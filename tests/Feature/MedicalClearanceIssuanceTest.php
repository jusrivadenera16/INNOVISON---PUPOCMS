<?php

namespace Tests\Feature;

use App\Http\Controllers\MedicalClearanceController;
use App\Models\{EmployeeHealthProfile, HealthFormCategory, HealthFormSubmission, HealthProfile, IssuedMedicalClearance, User, MarClearanceType, MarClearanceSubcategory, MarClearanceSourceMapping, MarClearanceIssuance};
use App\Services\{HealthFileEncryption, MedicalClearanceService};
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\{DB, Schema, Storage};
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Tests\TestCase;

class MedicalClearanceIssuanceTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        config(['database.default' => 'sqlite', 'database.connections.sqlite.database' => ':memory:',
            'health_data_encryption.enabled' => false, 'health_files.encryption_enabled' => true]);
        DB::purge('sqlite');
        Storage::fake('health_private');
        Schema::create('users', function (Blueprint $table) {
            $table->id();
            foreach (['name', 'first_name', 'middle_name', 'last_name', 'email', 'user_type', 'user_role', 'idp_role', 'student_number', 'gender', 'DOB'] as $column) $table->string($column)->nullable();
            $table->timestamps();
        });
        foreach (['health_profiles', 'health_profile_emp'] as $name) {
            Schema::create($name, function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('user_id');
                foreach (['name', 'clearance_status', 'health_form_category', 'pullout_status', 'birthday', 'age', 'sex', 'student_number'] as $column) $table->string($column)->nullable();
                $table->softDeletes();
                $table->timestamps();
            });
        }
        Schema::create('health_form_submissions', function (Blueprint $table) {
            $table->id();
            foreach (['user_id', 'health_profile_id', 'employee_health_profile_id'] as $column) $table->unsignedBigInteger($column)->nullable();
            $table->string('category')->nullable();
            $table->string('status');
            $table->timestamp('submitted_at')->nullable();
            $table->timestamp('approved_at')->nullable();
            $table->timestamps();
        });
        Schema::create('health_form_categories', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->boolean('is_active')->default(true);
            $table->json('available_for');
            $table->json('student_types')->nullable();
            $table->timestamps();
        });
        Schema::create('activity_logs', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id')->nullable();
            foreach (['user_name', 'user_role', 'action', 'module', 'event_type', 'description', 'subject_type', 'subject_id', 'metadata'] as $column) $table->text($column)->nullable();
            $table->timestamps();
        });
        (require base_path('database/migrations/2026_11_11_190000_create_issued_medical_clearances_table.php'))->up();
        (require base_path('database/migrations/2026_11_11_210000_add_doctor_review_to_issued_medical_clearances.php'))->up();
        Schema::create('mar_clearance_types', function (Blueprint $table) {
            $table->id(); $table->string('code'); $table->string('name');
            $table->boolean('is_active')->default(true); $table->boolean('allow_direct_use')->default(true);
            $table->integer('sort_order')->default(0); $table->timestamps();
        });
        Schema::create('mar_clearance_subcategories', function (Blueprint $table) {
            $table->id(); $table->unsignedBigInteger('mar_clearance_type_id'); $table->string('code');
            $table->string('name'); $table->integer('sort_order')->default(0); $table->timestamps();
        });
        foreach (['mar_clearance_type_sources' => 'mar_clearance_type_id', 'mar_clearance_subcategory_sources' => 'mar_clearance_subcategory_id'] as $tableName => $key) {
            Schema::create($tableName, function (Blueprint $table) use ($key) {
                $table->id(); $table->unsignedBigInteger($key); $table->string('source'); $table->timestamps();
            });
        }
        Schema::create('mar_mc_source', function (Blueprint $table) {
            $table->id();
            foreach (['mar_clearance_type_id', 'mar_clearance_subcategory_id', 'source_category_id'] as $key) $table->unsignedBigInteger($key)->nullable();
            $table->string('source_key'); $table->json('source_config')->nullable();
            $table->date('effective_from')->nullable(); $table->date('effective_until')->nullable();
            $table->boolean('is_active')->default(true); $table->timestamps();
        });
        Schema::create('mar_clearance_issuances', function (Blueprint $table) {
            $table->id();
            foreach (['user_id', 'clearance_type_id', 'clearance_subcategory_id'] as $key) $table->unsignedBigInteger($key)->nullable();
            foreach (['source_workflow', 'source_record_id', 'user_type', 'user_name_snapshot', 'clearance_name_snapshot'] as $key) $table->string($key)->nullable();
            $table->dateTime('approved_at'); $table->timestamps();
            $table->unique(['source_workflow', 'source_record_id']);
        });
    }

    protected function configurePurpose(string $name, string $type = 'student', string $categoryName = 'On the Job Training', ?MarClearanceType $parent = null): array
    {
        $category = HealthFormCategory::firstOrCreate(['name' => $categoryName], ['is_active' => true,
            'available_for' => $type === 'student' ? ['student'] : ['faculty', 'admin']]);
        $workflow = $type === 'student' ? 'student_nurse_review' : 'employee_nurse_review';
        $subcategory = null;
        if ($parent) {
            $subcategory = $parent->subcategories()->create(['name' => $name, 'code' => (string) Str::uuid()]);
            $subcategory->sources()->create(['source' => $workflow]);
        } else {
            $parent = MarClearanceType::firstOrCreate(['name' => $name], ['code' => (string) Str::uuid(), 'is_active' => true, 'allow_direct_use' => true]);
            $parent->sources()->firstOrCreate(['source' => $workflow]);
        }
        $mapping = MarClearanceSourceMapping::firstOrCreate(['mar_clearance_type_id' => $subcategory ? null : $parent->id,
            'mar_clearance_subcategory_id' => $subcategory?->id, 'source_key' => $type === 'student' ? 'student_health_form_category' : 'employee_health_form_category'],
            ['source_category_id' => $category->id, 'is_active' => true]);
        return compact('parent', 'subcategory', 'mapping');
    }

    private function prepareReportSchema(): void
    {
        foreach (['health_profiles', 'health_profile_emp'] as $name) {
            Schema::table($name, function (Blueprint $table) {
                foreach (['verified_at', 'certified_at', 'form_date'] as $column) $table->dateTime($column)->nullable();
                $table->string('submission_status')->nullable();
            });
        }
        Schema::create('consultations', function (Blueprint $table) {
            $table->id(); $table->unsignedBigInteger('user_id')->nullable();
            $table->string('certificate_type')->nullable(); $table->string('service')->nullable();
            $table->date('consultation_date')->nullable(); $table->softDeletes(); $table->timestamps();
        });
        Schema::create('appointments', function (Blueprint $table) {
            $table->id(); $table->unsignedBigInteger('user_id');
            $table->date('date'); $table->string('status')->nullable(); $table->string('service')->nullable();
        });
    }

    private function prepareRepeatDraftColumns(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('student_id')->nullable(); $table->string('barcode')->nullable();
        });
        foreach (['health_profiles', 'health_profile_emp'] as $name) {
            Schema::table($name, function (Blueprint $table) {
                $table->string('reference_number')->nullable(); $table->string('student_id')->nullable();
                $table->string('employee_number')->nullable(); $table->string('submission_status')->nullable();
                $table->json('final_review_draft_data')->nullable(); $table->json('draft_data')->nullable();
            });
        }
    }

    public function test_existing_draft_endpoints_save_repeat_review_without_changing_approval_and_clear_it_on_issue(): void
    {
        $this->prepareRepeatDraftColumns();
        foreach (['student', 'employee'] as $type) {
            $category = $type === 'student' ? 'On the Job Training' : 'Annual Medical Clearance';
            $profile = $this->patient($type, $category); $actor = $this->actor();
            $reference = $type === 'student' ? '2026-00001-TG-1' : 'EMP-1';
            $profile->forceFill(['student_number' => $reference, 'employee_number' => $reference])->save();
            $submission = $this->service()->approvedSubmission($profile);
            $original = $submission->getRawOriginal();
            $clearance = $this->input(['purpose' => $this->service()->purposeOptions($type, $profile)[0]['value']]);
            $draft = ['reference_number' => $reference, 'lookup_scope' => $type === 'student' ? 'student_local' : 'employee_local',
                'applicant_findings_status' => 'With Findings', 'applicant_height' => "5'8\"", 'medical_clearance' => $clearance,
                'medical_clearance_review' => ['submission_id' => $submission->id, 'category' => $category]];
            $request = Request::create('/', 'POST', $draft); $request->setUserResolver(fn () => $actor);
            $controller = app(\App\Http\Controllers\WalkInController::class);
            $response = $type === 'student' ? $controller->saveStudentAssessmentDraft($request) : $controller->saveEmployeeDraft($request);
            $this->assertSame(200, $response->getStatusCode(), $response->getContent());
            $saved = $profile->fresh();
            $draftField = $type === 'student' ? 'final_review_draft_data' : 'draft_data';
            $this->assertSame($clearance['request_key'], data_get($saved->{$draftField}, 'medical_clearance.request_key'));
            $this->assertSame('Fully Cleared', $saved->clearance_status);
            $this->assertNull($saved->submission_status);
            $this->assertSame($original, $submission->fresh()->getRawOriginal());
            $this->assertSame($type === 'student' ? 0 : 1, IssuedMedicalClearance::count());
            $this->service()->issue($type, $profile->id, $clearance, $actor);
            $this->assertNull($profile->fresh()->{$draftField});
            $this->assertSame($original, $submission->fresh()->getRawOriginal());
        }
        $this->assertSame(2, HealthFormSubmission::count());
        $this->assertSame(2, MarClearanceIssuance::count());
    }

    public function test_stale_or_already_issued_repeat_draft_is_rejected_without_changing_approved_form(): void
    {
        $this->prepareRepeatDraftColumns();
        $profile = $this->patient(); $actor = $this->actor();
        $clearance = $this->input();
        $request = Request::create('/', 'POST', ['medical_clearance' => $clearance,
            'medical_clearance_review' => ['submission_id' => 9999, 'category' => 'On the Job Training']]);
        $request->setUserResolver(fn () => $actor);
        try { $this->service()->validateReviewDraft($request, $profile); $this->fail('Stale submission must not save a review draft.'); }
        catch (ValidationException $exception) { $this->assertArrayHasKey('record', $exception->errors()); }
        $document = $this->service()->issue('student', $profile->id, $clearance, $actor);
        $request->merge(['medical_clearance_review' => ['submission_id' => $document->health_form_submission_id, 'category' => 'On the Job Training']]);
        try { $this->service()->validateReviewDraft($request, $profile); $this->fail('Already-issued request must not be saved again as a draft.'); }
        catch (ValidationException $exception) { $this->assertArrayHasKey('record', $exception->errors()); }
        $this->assertSame('Fully Cleared', $profile->fresh()->clearance_status);
        $this->assertSame(1, HealthFormSubmission::count());
    }

    public function test_same_ojt_form_has_separate_configured_parent_and_subcategory_purpose_counts(): void
    {
        $profile = $this->patient(); $actor = $this->actor();
        $parent = MarClearanceType::create(['name' => 'Teaching Certificates', 'code' => 'teaching', 'allow_direct_use' => false, 'is_active' => true]);
        $practice = $this->configurePurpose('Practice Teaching / Internship', 'student', 'On the Job Training', $parent);
        HealthFormCategory::create(['name' => 'Unconfigured Health Form', 'available_for' => ['student'], 'is_active' => true]);
        $request = Request::create('/'); $request->setUserResolver(fn () => $actor);
        $options = app(MedicalClearanceController::class)->index($request, 'student', $profile->id, $this->service())->getData(true);
        $this->assertContains('Practice Teaching / Internship', $options['purposes']);
        $this->assertNotContains('Teaching Certificates', $options['purposes']);
        $this->assertNotContains('Unconfigured Health Form', $options['purposes']);
        $value = 'mapping:' . $practice['mapping']->id;
        $this->assertContains(['value' => $value, 'label' => 'Practice Teaching / Internship'], $options['purpose_options']);
        $input = $this->input(['purpose' => $value]);
        $document = $this->service()->issue('student', $profile->id, $input, $actor);
        $this->assertSame('Practice Teaching / Internship', $document->purpose);
        $snapshot = MarClearanceIssuance::first();
        $this->assertSame($parent->id, $snapshot->clearance_type_id);
        $this->assertSame($practice['subcategory']->id, $snapshot->clearance_subcategory_id);
        $this->assertSame('medical-clearance:' . $document->id, $snapshot->source_record_id);
        $this->service()->issue('student', $profile->id, $input, $actor);
        app(MedicalClearanceController::class)->show('student', $profile->id, $document, $this->service());
        $this->assertSame(1, MarClearanceIssuance::count());
        $this->service()->issue('student', $profile->id, $this->input(), $actor);
        $this->assertSame(2, MarClearanceIssuance::count());
        $this->assertSame(1, HealthFormSubmission::count());
        $this->prepareReportSchema();
        $report = app(\App\Services\MarClearanceIssuanceService::class)->issuancesForReportPeriod('2026-11-01', '2026-11-30');
        $this->assertCount(2, $report);
        $this->assertSame(['On the Job Training', 'Practice Teaching / Internship'], $report->pluck('clearance_name_snapshot')->sort()->values()->all());
    }

    public function test_inactive_or_changed_mapping_is_rejected_without_issuance_or_count(): void
    {
        $profile = $this->patient();
        $option = $this->service()->purposeOptions('student', $profile)[0];
        MarClearanceSourceMapping::query()->update(['is_active' => false]);
        $this->assertSame([], $this->service()->purposeOptions('student', $profile));
        try {
            $this->service()->issue('student', $profile->id, $this->input(['purpose' => $option['value']]), $this->actor());
            $this->fail('A stale mapping must not issue.');
        } catch (ValidationException $exception) { $this->assertArrayHasKey('purpose', $exception->errors()); }
        $this->assertSame(0, IssuedMedicalClearance::count());
        $this->assertSame(0, MarClearanceIssuance::count());
    }

    public function test_employee_cannot_forge_a_student_purpose_mapping_and_only_sees_employee_configuration(): void
    {
        $this->patient();
        $studentMapping = MarClearanceSourceMapping::first();
        $profile = $this->patient('employee', 'Annual Medical Clearance');
        $this->assertSame(['Annual Medical Clearance'], $this->service()->purposes('employee', $profile));
        try {
            $this->service()->issue('employee', $profile->id, $this->input(['purpose' => 'mapping:' . $studentMapping->id]), $this->actor());
            $this->fail('An employee must not use a student mapping.');
        } catch (ValidationException $exception) { $this->assertArrayHasKey('purpose', $exception->errors()); }
        $this->assertSame(0, MarClearanceIssuance::count());
    }

    public function test_counting_failure_rolls_back_pdf_certificate_and_audit_together(): void
    {
        $profile = $this->patient();
        $this->mock(\App\Services\MarClearanceIssuanceService::class)
            ->shouldReceive('recordApprovedClearanceTarget')->once()->andThrow(new \RuntimeException('Count save failed'));
        try {
            $this->service()->issue('student', $profile->id, $this->input(), $this->actor());
            $this->fail('A certificate must not succeed without its count snapshot.');
        } catch (\RuntimeException $exception) { $this->assertSame('Count save failed', $exception->getMessage()); }
        $this->assertSame(0, IssuedMedicalClearance::count());
        $this->assertSame(0, MarClearanceIssuance::count());
        $this->assertSame(0, DB::table('activity_logs')->count());
        $this->assertSame([], Storage::disk('health_private')->allFiles());
    }

    public function test_saved_purpose_count_keeps_its_target_and_label_after_configuration_is_renamed(): void
    {
        $profile = $this->patient();
        $document = $this->service()->issue('student', $profile->id, $this->input(), $this->actor());
        $snapshot = MarClearanceIssuance::first();
        MarClearanceType::first()->update(['name' => 'Renamed OJT Purpose']);
        $this->prepareReportSchema();
        $report = app(\App\Services\MarClearanceIssuanceService::class)->issuancesForReportPeriod('2026-11-01', '2026-11-30');
        $this->assertCount(1, $report);
        $this->assertSame($snapshot->clearance_type_id, $report->first()->clearance_type_id);
        $this->assertSame('On the Job Training', $report->first()->clearance_name_snapshot);
        $this->assertSame('On the Job Training', $document->fresh()->purpose);
        $this->assertSame(['Renamed OJT Purpose'], $this->service()->purposes('student', $profile));
    }

    public function test_report_uses_certificates_not_old_form_approval_rows_and_legacy_pdf_is_counted_once(): void
    {
        $profile = $this->patient(); $actor = $this->actor();
        $this->prepareReportSchema();
        $service = app(\App\Services\MarClearanceIssuanceService::class);
        $target = MarClearanceType::first();
        $submission = HealthFormSubmission::first();
        $service->recordApprovedClearanceTarget($profile->user, $target, null, 'student_nurse_review', 'health_form_submission:' . $submission->id, '2026-11-11');
        $this->assertCount(0, $service->issuancesForReportPeriod('2026-11-01', '2026-11-30'));
        $document = $this->service()->issue('student', $profile->id, $this->input(), $actor);
        $this->assertCount(1, $service->issuancesForReportPeriod('2026-11-01', '2026-11-30'));
        // Simulate an existing certificate from before purpose-count snapshots were introduced.
        DB::table('mar_clearance_issuances')->where('source_record_id', 'medical-clearance:' . $document->id)->delete();
        $before = MarClearanceIssuance::count();
        $this->assertCount(1, $service->issuancesForReportPeriod('2026-11-01', '2026-11-30'));
        $this->assertCount(0, $service->issuancesForReportPeriod('2026-12-01', '2026-12-31'));
        $this->assertSame($before, MarClearanceIssuance::count());
    }

    public function test_another_clearance_for_student_and_employee_saves_review_without_mutating_approved_form(): void
    {
        HealthFormCategory::create(['name' => 'On the Job Training', 'is_active' => true, 'available_for' => ['student']]);
        HealthFormCategory::create(['name' => 'Annual Medical Clearance', 'is_active' => true, 'available_for' => ['faculty', 'admin']]);
        HealthFormCategory::create(['name' => 'Disabled Category', 'is_active' => false, 'available_for' => ['student', 'faculty']]);
        foreach (['student' => 'On the Job Training', 'employee' => 'Annual Medical Clearance'] as $type => $category) {
            $profile = $this->patient($type, $category);
            $originalProfile = $profile->fresh()->getRawOriginal();
            $submission = $this->service()->approvedSubmission($profile);
            $originalSubmission = $submission->getRawOriginal();
            $actor = $this->actor();
            $request = Request::create('/'); $request->setUserResolver(fn () => $actor);
            $options = app(MedicalClearanceController::class)->index($request, $type, $profile->id, $this->service())->getData(true);
            $this->assertSame([$category], $options['health_form_categories']);
            $count = HealthFormSubmission::count();
            $input = $this->input(['purpose' => $category, 'submission_id' => $submission->id,
                'doctor_review' => ['category' => $category, 'findings' => 'No Findings / Normal', 'decision' => 'issue', 'remarks' => 'Reviewed for another clearance.', 'assessment' => ['height' => "5'6\"", 'weight' => '120']]]);
            $request = Request::create('/', 'POST', $input); $request->setUserResolver(fn () => $actor);
            $response = app(MedicalClearanceController::class)->store($request, $type, $profile->id, $this->service());
            $this->assertSame(200, $response->getStatusCode());
            $document = IssuedMedicalClearance::latest('id')->first();
            $this->assertSame($category, $document->doctor_review['category']);
            $this->assertSame($actor->id, $document->doctor_review['reviewed_by_user_id']);
            $this->assertSame("5'6\"", $document->doctor_review['assessment']['height']);
            $this->assertStringNotContainsString('Reviewed for another clearance.', DB::table('issued_medical_clearances')->where('id', $document->id)->value('doctor_review'));
            $this->assertSame($originalProfile, $profile->fresh()->getRawOriginal());
            $this->assertSame($originalSubmission, $submission->fresh()->getRawOriginal());
            $this->assertSame($count, HealthFormSubmission::count());
            $this->assertSame($submission->id, $document->health_form_submission_id);
            $this->service()->issue($type, $profile->id, $input, $actor);
            $this->assertSame($type === 'student' ? 1 : 2, IssuedMedicalClearance::count());
        }
    }

    public function test_repeat_review_rejects_stale_submission_wrong_audience_and_not_cleared_decision(): void
    {
        $profile = $this->patient('employee', 'Annual Medical Clearance'); $actor = $this->actor();
        HealthFormCategory::create(['name' => 'On the Job Training', 'is_active' => true, 'available_for' => ['student']]);
        HealthFormCategory::create(['name' => 'Annual Medical Clearance', 'is_active' => true, 'available_for' => ['faculty']]);
        $id = $this->service()->approvedSubmission($profile)->id;
        foreach ([[999, 'Annual Medical Clearance', 'issue'], [$id, 'On the Job Training', 'issue'], [$id, 'Annual Medical Clearance', 'pending']] as [$submissionId, $category, $decision]) {
            try {
                $this->service()->issue('employee', $profile->id, $this->input(['purpose' => 'Annual Medical Clearance', 'submission_id' => $submissionId,
                    'doctor_review' => ['category' => $category, 'findings' => 'No Findings / Normal', 'decision' => $decision]]), $actor);
                $this->fail('Invalid repeat review must not issue a certificate.');
            } catch (ValidationException $exception) { $this->assertNotEmpty($exception->errors()); }
        }
        $this->assertSame(0, IssuedMedicalClearance::count()); $this->assertSame(1, HealthFormSubmission::count());
    }

    public function test_cancelled_or_rejected_renewal_keeps_prior_approval_eligible_but_active_or_revoked_form_does_not(): void
    {
        foreach (['student', 'employee'] as $type) {
            $profile = $this->patient($type, $type === 'student' ? 'On the Job Training' : 'Annual Medical Clearance');
            $approved = $this->service()->approvedSubmission($profile);
            $key = $type === 'student' ? 'health_profile_id' : 'employee_health_profile_id';
            foreach (['cancelled', 'canceled', 'rejected'] as $status) {
                HealthFormSubmission::create(['user_id' => $profile->user_id, $key => $profile->id, 'status' => $status, 'submitted_at' => now()]);
                $this->assertSame($approved->id, $this->service()->approvedSubmission($profile)?->id);
            }
            $actor = $this->actor();
            $document = $this->service()->issue($type, $profile->id, $this->input(['purpose' => $approved->category]), $actor);
            $this->assertSame($approved->id, $document->health_form_submission_id);
            foreach (['requested', 'submitted', 'under_review', 'needs_correction', 'withdrawn'] as $status) {
                $latest = HealthFormSubmission::create(['user_id' => $profile->user_id, $key => $profile->id, 'status' => $status, 'submitted_at' => now()]);
                $this->assertNull($this->service()->approvedSubmission($profile)); $latest->delete();
            }
            $revoked = HealthFormSubmission::create(['user_id' => $profile->user_id, $key => $profile->id, 'status' => 'cancelled', 'submitted_at' => now(), 'approved_at' => now()]);
            $this->assertNull($this->service()->approvedSubmission($profile)); $revoked->delete();
            $profile->forceFill(['pullout_status' => 'pulled_out'])->save();
            $this->assertNull($this->service()->approvedSubmission($profile));
        }
    }

    private function service(): MedicalClearanceService { return app(MedicalClearanceService::class); }
    private function actor(): User { return User::forceCreate(['name' => 'Clinic Staff', 'user_role' => 'superadmin']); }
    private function patient(string $type = 'student', string $category = 'On the Job Training')
    {
        $this->configurePurpose($category, $type, $category);
        $user = User::forceCreate(['name' => 'Juan Dela Cruz', 'user_type' => $type === 'student' ? 'Student' : 'Faculty', 'gender' => 'Male', 'DOB' => '2004-01-15']);
        $class = $type === 'student' ? HealthProfile::class : EmployeeHealthProfile::class;
        $profile = $class::forceCreate(['user_id' => $user->id, 'clearance_status' => 'Fully Cleared', 'health_form_category' => $category]);
        HealthFormSubmission::create(['user_id' => $user->id, $type === 'student' ? 'health_profile_id' : 'employee_health_profile_id' => $profile->id,
            'category' => $category, 'status' => 'approved', 'submitted_at' => now()->subDay(), 'approved_at' => now()]);
        return $profile;
    }
    private function input(array $overrides = []): array
    {
        return array_merge(['request_key' => (string) Str::uuid(), 'issued_on' => '2026-11-11', 'purpose' => 'On the Job Training'], $overrides);
    }

    public function test_approved_student_issuance_saves_identity_private_pdf_and_audit_and_retry_is_idempotent(): void
    {
        $profile = $this->patient();
        $actor = $this->actor();
        $input = $this->input(['name' => 'Forged Name', 'age' => 99, 'sex' => 'F']);
        $document = $this->service()->issue('student', $profile->id, $input, $actor);
        $this->assertSame('Juan Dela Cruz', $document->patient_name);
        $this->assertSame(22, $document->patient_age);
        $this->assertSame('M', $document->patient_sex);
        $this->assertSame('On the Job Training', $document->purpose);
        $this->assertSame($actor->id, $document->issued_by_user_id);
        $this->assertNull($document->control_number);
        $this->assertNotSame('Juan Dela Cruz', DB::table('issued_medical_clearances')->value('patient_name'));
        $stored = Storage::disk('health_private')->get($document->pdf_path);
        $this->assertStringNotContainsString('%PDF-', $stored);
        $this->assertStringStartsWith('%PDF-', app(HealthFileEncryption::class)->decrypt($stored));
        $retry = $this->service()->issue('student', $profile->id, $input, $actor);
        $this->assertSame($document->id, $retry->id);
        $this->assertSame(1, IssuedMedicalClearance::count());
        $this->assertSame(1, DB::table('activity_logs')->count());
        $this->assertSame(1, MarClearanceIssuance::count());
    }

    public function test_reprint_refreshes_template_using_saved_details_without_reissuing_or_overwriting_archive(): void
    {
        $profile = $this->patient();
        $actor = $this->actor();
        $request = Request::create('/');
        $request->setUserResolver(fn () => $actor);
        $controller = app(MedicalClearanceController::class);
        $before = $controller->index($request, 'student', $profile->id, $this->service())->getData(true);
        $this->assertTrue($before['can_issue']);
        $this->assertSame([], $before['documents']);
        $document = $this->service()->issue('student', $profile->id, $this->input(), $actor);
        $document->forceFill(['control_number' => 'MC-2026-000087'])->save();
        $snapshot = $document->fresh()->getRawOriginal();
        $archived = app(HealthFileEncryption::class)->encrypt('%PDF-legacy-full-page');
        Storage::disk('health_private')->put($document->pdf_path, $archived);
        $profile->user->forceFill(['name' => 'Later Name', 'DOB' => '2000-01-01'])->save();
        $profile->forceFill(['clearance_status' => 'Pending/Conditional'])->save();
        $rendered = \Mockery::mock(\Barryvdh\DomPDF\PDF::class);
        $rendered->shouldReceive('setPaper')->with('letter')->once()->andReturnSelf();
        $rendered->shouldReceive('output')->once()->andReturn('%PDF-refreshed-half-page');
        Pdf::shouldReceive('loadView')->once()->with('admin.templates.medical-clearance', [
            'templatePreview' => false, 'clearance' => ['date' => 'Nov 11, 2026', 'name' => 'Juan Dela Cruz',
                'age' => 22, 'sex' => 'M', 'purpose' => 'On the Job Training'],
        ])->andReturn($rendered);
        $response = $controller->show('student', $profile->id, $document->fresh(), $this->service());
        $this->assertSame('%PDF-refreshed-half-page', $response->getContent());
        $this->assertSame($archived, Storage::disk('health_private')->get($document->pdf_path));
        $this->assertSame($snapshot, $document->fresh()->getRawOriginal());
        $this->assertSame(1, IssuedMedicalClearance::count());
        $this->assertSame(1, DB::table('activity_logs')->count());
        $this->assertStringContainsString('no-store', $response->headers->get('Cache-Control'));
        $this->assertSame('inline; filename="medical-clearance.pdf"', $response->headers->get('Content-Disposition'));
        $this->assertSame('Juan Dela Cruz', $document->fresh()->patient_name);
        $after = $controller->index($request, 'student', $profile->id, $this->service())->getData(true);
        $this->assertFalse($after['can_issue']);
        $this->assertCount(1, $after['documents']);
        $this->assertStringContainsString('/student/'.$profile->id.'/'.$document->id, $after['documents'][0]['url']);
    }

    public function test_employee_uses_form_category_and_other_purpose_does_not_add_a_category(): void
    {
        $profile = $this->patient('employee', 'Annual Medical Clearance');
        $category = HealthFormCategory::create(['name' => 'Employment', 'is_active' => true, 'available_for' => ['faculty']]);
        $this->assertNotContains($category->name, $this->service()->purposes('employee', $profile));
        $this->configurePurpose('Employment', 'employee', 'Employment');
        $this->configurePurpose('Others', 'employee', 'Annual Medical Clearance');
        $categoryCount = HealthFormCategory::count();
        $purposes = $this->service()->purposes('employee', $profile);
        $this->assertContains('Annual Medical Clearance', $purposes);
        $this->assertContains($category->name, $purposes);
        $this->assertNotContains('On the Job Training', $purposes);
        $document = $this->service()->issue('employee', $profile->id, $this->input(['purpose' => '__other__', 'other_purpose' => 'Return to Work']), $this->actor());
        $this->assertSame('Return to Work', $document->purpose);
        $this->assertSame($categoryCount, HealthFormCategory::count());
        $this->assertSame('Others', MarClearanceIssuance::first()->clearance_name_snapshot);
    }

    public function test_employee_cannot_receive_ojt_even_through_others(): void
    {
        $profile = $this->patient('employee', 'Annual');
        $this->expectException(ValidationException::class);
        $this->service()->issue('employee', $profile->id, $this->input(['purpose' => '__other__', 'other_purpose' => 'OJT']), $this->actor());
    }

    public function test_new_pending_submission_blocks_issuance_despite_old_approved_profile(): void
    {
        $profile = $this->patient();
        HealthFormSubmission::create(['health_profile_id' => $profile->id, 'status' => 'submitted', 'submitted_at' => now()]);
        $this->assertNull($this->service()->approvedSubmission($profile));
        try {
            $this->service()->issue('student', $profile->id, $this->input(), $this->actor());
            $this->fail('Pending record should not issue a clearance.');
        } catch (ValidationException $exception) {
            $this->assertSame(0, IssuedMedicalClearance::count());
            $this->assertSame([], Storage::disk('health_private')->allFiles());
        }
    }

    public function test_missing_identity_is_blocked_and_female_age_uses_the_selected_issue_date(): void
    {
        $profile = $this->patient();
        $profile->user->forceFill(['gender' => 'Female', 'DOB' => '2004-11-12'])->save();
        $identity = $this->service()->identity($profile->fresh('user'), '2026-11-11');
        $this->assertSame(21, $identity['age']);
        $this->assertSame('F', $identity['sex']);
        $profile->user->forceFill(['gender' => null])->save();
        $this->expectException(ValidationException::class);
        $this->service()->issue('student', $profile->id, $this->input(), $this->actor());
    }

    public function test_failed_pdf_generation_rolls_back_document_and_audit(): void
    {
        $profile = $this->patient();
        Pdf::shouldReceive('loadView')->once()->andThrow(new \RuntimeException('PDF failure'));
        try {
            $this->service()->issue('student', $profile->id, $this->input(), $this->actor());
            $this->fail('Expected rendering failure');
        } catch (\RuntimeException $exception) {
            $this->assertSame('PDF failure', $exception->getMessage());
            $this->assertSame(0, IssuedMedicalClearance::count());
            $this->assertSame(0, DB::table('activity_logs')->count());
            $this->assertSame(0, MarClearanceIssuance::count());
            $this->assertSame([], Storage::disk('health_private')->allFiles());
        }
    }

    public function test_applicants_dependents_and_cross_record_document_access_are_rejected(): void
    {
        $profile = $this->patient();
        $document = $this->service()->issue('student', $profile->id, $this->input(), $this->actor());
        foreach (['Applicant', 'Dependent'] as $type) {
            $user = User::forceCreate(['user_type' => $type]);
            $other = HealthProfile::forceCreate(['user_id' => $user->id, 'clearance_status' => 'Fully Cleared']);
            try { $this->service()->profile('student', $other->id); $this->fail('Wrong account type must be rejected'); }
            catch (HttpException $exception) { $this->assertSame(404, $exception->getStatusCode()); }
        }
        $other = $this->patient();
        $this->expectException(HttpException::class);
        app(MedicalClearanceController::class)->show('student', $other->id, $document, $this->service());
    }

    public function test_routes_are_admin_only_and_issue_requires_assessment_permission(): void
    {
        foreach (['index', 'store', 'show'] as $action) {
            $middleware = app('router')->getRoutes()->getByName('admin.medical_clearances.'.$action)->gatherMiddleware();
            $this->assertContains('admin.guard', $middleware);
            $this->assertContains('role:superadmin,admin', $middleware);
            $this->assertContains('module.permission:health_records.'.($action === 'store' ? 'update_assessment' : 'view'), $middleware);
        }
        $student = User::forceCreate(['user_role' => 'student']);
        $request = Request::create('/');
        $request->setUserResolver(fn () => $student);
        $this->expectException(HttpException::class);
        app(\App\Http\Middleware\EnsureModulePermission::class)->handle($request, fn () => response('should not run'), 'health_records.view');
    }

    public function test_review_options_are_available_before_approval_and_use_the_pending_form_category(): void
    {
        $profile = $this->patient();
        $profile->forceFill(['clearance_status' => 'For Verification', 'health_form_category' => 'Regular'])->save();
        HealthFormSubmission::create(['user_id' => $profile->user_id, 'health_profile_id' => $profile->id,
            'category' => 'On the Job Training', 'status' => 'under_review', 'submitted_at' => now()]);
        $options = app(MedicalClearanceController::class)->reviewOptions('student', $profile->id, $this->service())->getData(true);
        $this->assertSame('On the Job Training', $options['default_purpose']);
        $this->assertSame('Juan Dela Cruz', $options['identity']['name']);
        $this->assertSame(0, IssuedMedicalClearance::count());
        $middleware = app('router')->getRoutes()->getByName('admin.medical_clearances.review_options')->gatherMiddleware();
        $this->assertContains('module.permission:health_records.update_assessment', $middleware);
    }

    public function test_pending_does_not_issue_but_student_employee_approval_requires_purpose(): void
    {
        $request = Request::create('/', 'POST', ['issue_medical_clearance' => true]);
        $this->assertNull($this->service()->approvalInput($request, false, true));
        $request = Request::create('/', 'POST');
        $actor = $this->actor(); $request->setUserResolver(fn () => $actor);
        $this->expectException(ValidationException::class);
        $this->service()->approvalInput($request, true, true);
        $this->assertSame(0, IssuedMedicalClearance::count());
    }

    public function test_applicants_and_dependents_cannot_request_a_certificate_with_approval(): void
    {
        $this->expectException(ValidationException::class);
        $this->service()->approvalInput(Request::create('/', 'POST', ['issue_medical_clearance' => true]), true, false);
    }

    public function test_review_uses_server_issue_date_and_does_not_require_a_checkbox_patient_or_date_input(): void
    {
        $actor = $this->actor();
        $request = Request::create('/', 'POST', ['medical_clearance' => ['request_key' => (string) Str::uuid(), 'purpose' => 'On the Job Training', 'issued_on' => '2000-01-01', 'name' => 'Forged name']]);
        $request->setUserResolver(fn () => $actor);
        $input = $this->service()->approvalInput($request, true, true);
        $this->assertSame('On the Job Training', $input['purpose']);
        $this->assertSame(now()->toDateString(), $input['issued_on']);
        $this->assertArrayNotHasKey('name', $input);
        $this->assertSame(0, IssuedMedicalClearance::count());
    }

    private function prepareStudentReview(): array
    {
        Schema::table('users', function (Blueprint $table) { $table->boolean('is_health_profile_completed')->default(false); });
        $columns = ['student_id', 'reference_number', 'course_college', 'clearance_revoked_at', 'documents_valid', 'verified_at', 'review_closed_at',
            'approved_by_user_id', 'pending_reason', 'assessment_date', 'physical_assessment_status', 'review_started_at', 'review_started_by_user_id', 'final_review_draft_data'];
        Schema::table('health_profiles', function (Blueprint $table) use ($columns) { foreach ($columns as $column) $table->string($column)->nullable(); });
        Schema::table('health_form_submissions', function (Blueprint $table) {
            foreach (['pdf_path', 'profile_snapshot', 'snapshot_captured_at', 'school_year', 'remarks', 'approved_by_user_id', 'status_changed_by_user_id'] as $column) $table->text($column)->nullable();
        });
        Schema::table('activity_logs', function (Blueprint $table) {
            foreach (['route_name', 'http_method', 'request_path', 'status_code', 'ip_address', 'user_agent'] as $column) $table->text($column)->nullable();
        });
        Schema::create('dependents_profiles', function (Blueprint $table) { $table->id(); $table->unsignedBigInteger('user_id'); });
        $profile = $this->patient();
        $profile->user->forceFill(['student_number' => '2026-00001-TG-1'])->save();
        $profile->forceFill(['student_number' => '2026-00001-TG-1', 'clearance_status' => 'For Verification'])->save();
        $submission = HealthFormSubmission::where('health_profile_id', $profile->id)->first();
        $submission->forceFill(['status' => 'submitted', 'approved_at' => null])->save();
        $actor = $this->actor();
        $this->actingAs($actor, 'admin');
        return [$profile, $submission, $actor];
    }

    public function test_student_doctor_review_approval_issues_and_saves_the_selected_certificate_in_one_request(): void
    {
        [$profile, $submission, $actor] = $this->prepareStudentReview();
        $request = Request::create('/', 'POST', ['reference_number' => '2026-00001-TG-1', 'clearance_decision' => 'approve',
            'issue_medical_clearance' => true, 'medical_clearance' => $this->input()]);
        $request->setUserResolver(fn () => $actor);
        $response = app(\App\Http\Controllers\WalkInController::class)->saveStudentAssessment($request);
        $this->assertSame(200, $response->getStatusCode(), $response->getContent());
        $this->assertSame('Fully Cleared', $profile->fresh()->clearance_status);
        $this->assertSame('approved', $submission->fresh()->status);
        $this->assertSame(1, IssuedMedicalClearance::count());
        $document = IssuedMedicalClearance::first();
        $this->assertSame($submission->id, $document->health_form_submission_id);
        $this->assertSame('On the Job Training', $document->purpose);
        $this->assertStringContainsString('/student/'.$profile->id.'/', $response->getData(true)['medical_clearance_url']);
    }

    public function test_certificate_failure_during_student_review_rolls_back_the_approval_and_submission(): void
    {
        [$profile, $submission, $actor] = $this->prepareStudentReview();
        $formPdf = \Mockery::mock(\Barryvdh\DomPDF\PDF::class);
        $formPdf->shouldReceive('setPaper')->once()->andReturnSelf();
        $formPdf->shouldReceive('output')->once()->andReturn('final Health Form PDF');
        Pdf::shouldReceive('loadView')->with('student.print_health_form', \Mockery::any())->once()->andReturn($formPdf);
        Pdf::shouldReceive('loadView')->with('admin.templates.medical-clearance', \Mockery::any())->once()->andThrow(new \RuntimeException('Certificate render failed'));
        $request = Request::create('/', 'POST', ['reference_number' => '2026-00001-TG-1', 'clearance_decision' => 'approve',
            'issue_medical_clearance' => true, 'medical_clearance' => $this->input()]);
        $request->setUserResolver(fn () => $actor);
        $response = app(\App\Http\Controllers\WalkInController::class)->saveStudentAssessment($request);
        $this->assertSame(500, $response->getStatusCode());
        $this->assertSame('For Verification', $profile->fresh()->clearance_status);
        $this->assertSame('submitted', $submission->fresh()->status);
        $this->assertNull($submission->fresh()->approved_at);
        $this->assertSame(0, IssuedMedicalClearance::count());
        $this->assertFalse((bool) $profile->user->fresh()->is_health_profile_completed);
    }

    public function test_employee_doctor_review_approval_saves_its_certificate_with_the_employee_category(): void
    {
        [, , $actor] = $this->prepareStudentReview();
        $columns = array_merge(\App\Services\EmployeeHealthFormHistoryService::ASSESSMENT_FIELDS,
            ['employee_number', 'submission_status', 'documents_valid', 'pending_reason', 'resubmission_required_fields', 'resubmission_requested_at',
                'verified_at', 'certified_at', 'approved_by_user_id', 'staff_health_form_pdf_path', 'school_year']);
        Schema::table('health_profile_emp', function (Blueprint $table) use ($columns) { foreach ($columns as $column) $table->text($column)->nullable(); });
        Schema::create('health_profile_correction_requests', function (Blueprint $table) {
            $table->id(); $table->unsignedBigInteger('user_id')->nullable(); $table->unsignedBigInteger('employee_health_profile_id'); $table->string('type'); $table->string('status');
            $table->timestamp('reviewed_at')->nullable(); $table->unsignedBigInteger('reviewed_by_user_id')->nullable(); $table->timestamps();
        });
        $profile = $this->patient('employee', 'Annual Medical Clearance');
        $profile->forceFill(['employee_number' => 'EMP-1', 'clearance_status' => 'For Verification'])->save();
        $submission = HealthFormSubmission::where('employee_health_profile_id', $profile->id)->first();
        $submission->forceFill(['status' => 'submitted', 'approved_at' => null])->save();
        $this->mock(\App\Services\EmployeeHealthFormPdfService::class)->shouldReceive('generate')->once()->andReturnUsing(function ($record) {
            $record->forceFill(['staff_health_form_pdf_path' => 'employee-final-form.pdf'])->save(); return 'employee-final-form.pdf';
        });
        $this->mock(\App\Services\MarClearanceIssuanceService::class)->makePartial()->shouldReceive('syncApprovedEmployeeHealthProfile')->once();
        $this->mock(\App\Services\StudentNotificationMailer::class)->shouldReceive('sendHealthRecordNotice')->once()->andReturn(['status' => 'skipped']);
        $request = Request::create('/', 'POST', ['issue_medical_clearance' => true, 'medical_clearance' => $this->input(['purpose' => 'Annual Medical Clearance'])]);
        $request->setUserResolver(fn () => $actor);
        $response = (new \ReflectionMethod(\App\Http\Controllers\WalkInController::class, 'approveEmployeeHealthProfile'))->invoke(
            app(\App\Http\Controllers\WalkInController::class), $request, [], 'EMP-1', 'Fully Cleared', false, false, [], [], 'No Findings / Normal', '', '', '');
        $this->assertSame(200, $response->getStatusCode());
        $this->assertSame('approved', $submission->fresh()->status);
        $this->assertSame('Fully Cleared', $profile->fresh()->clearance_status);
        $this->assertSame(1, IssuedMedicalClearance::count());
        $document = IssuedMedicalClearance::first();
        $this->assertSame('employee', $document->record_type);
        $this->assertSame('Annual Medical Clearance', $document->purpose);
        $this->assertSame($submission->id, $document->health_form_submission_id);
        $this->assertStringContainsString('/employee/'.$profile->id.'/', $response->getData(true)['medical_clearance_url']);
    }
}
