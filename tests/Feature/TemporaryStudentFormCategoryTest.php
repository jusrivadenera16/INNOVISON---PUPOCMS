<?php

namespace Tests\Feature;

use App\Models\{ActivityLog, HealthFormCategory, HealthFormSubmission, HealthProfile, HealthProfileCorrectionRequest, User};
use App\Services\{HealthRecordHistoryService, TemporaryStudentFormCategoryService};
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\{DB, Schema, Storage};
use Illuminate\Validation\ValidationException;
use Mockery;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Tests\TestCase;

class TemporaryStudentFormCategoryTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        config(['database.default' => 'sqlite', 'database.connections.sqlite.database' => ':memory:',
            'health_data_encryption.enabled' => false, 'health_files.encryption_enabled' => false,
            'health_files.write_disk' => 'health_private', 'health_files.mirror_to_legacy' => false,
            'health_files.legacy_fallback' => false, 'health_form_features.temporary_student_category_change' => true]);
        DB::purge('sqlite');
        Storage::fake('health_private');
        Schema::create('users', function (Blueprint $table) {
            $table->id();
            foreach (['name', 'first_name', 'middle_name', 'last_name', 'email', 'user_type', 'student_type', 'user_role', 'idp_role', 'student_number', 'course'] as $field) $table->string($field)->nullable();
            $table->timestamps();
        });
        Schema::create('health_profiles', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id');
            foreach (['student_number', 'health_form_category', 'clearance_status', 'pullout_status', 'health_declaration', 'student_photo', 'medical_certificate', 'chest_xray_result', 'digital_signature', 'home_address', 'age', 'sex'] as $field) $table->string($field)->nullable();
            $table->timestamps();
        });
        Schema::create('health_form_submissions', function (Blueprint $table) {
            $table->id();
            foreach (['user_id', 'health_profile_id', 'employee_health_profile_id', 'approved_by_user_id', 'requested_by_user_id', 'status_changed_by_user_id'] as $field) $table->unsignedBigInteger($field)->nullable();
            foreach (['category', 'status', 'pdf_path', 'remarks'] as $field) $table->string($field)->nullable();
            $table->json('profile_snapshot')->nullable();
            foreach (['submitted_at', 'approved_at', 'requested_at', 'snapshot_captured_at'] as $field) $table->timestamp($field)->nullable();
            $table->timestamps();
        });
        Schema::create('health_form_categories', function (Blueprint $table) {
            $table->id(); $table->string('name'); $table->boolean('is_active')->default(true);
            $table->json('available_for'); $table->timestamps();
        });
        Schema::create('health_profile_correction_requests', function (Blueprint $table) {
            $table->id();
            foreach (['user_id', 'health_profile_id', 'health_form_submission_id', 'requested_by_user_id', 'reviewed_by_user_id'] as $field) $table->unsignedBigInteger($field)->nullable();
            $table->string('type'); $table->string('status'); $table->json('metadata')->nullable();
            $table->timestamps();
        });
        Schema::create('student_medical_assessments', function (Blueprint $table) {
            $table->id(); $table->unsignedBigInteger('health_profile_id'); $table->timestamps();
        });
        Schema::create('activity_logs', function (Blueprint $table) {
            $table->id(); $table->unsignedBigInteger('user_id');
            foreach (['user_name', 'user_role', 'action', 'module', 'event_type', 'description', 'subject_type', 'subject_id', 'metadata'] as $field) $table->text($field)->nullable();
            $table->timestamps();
        });
        foreach (['Regular', 'On the Job Training', 'Ladderized'] as $category) HealthFormCategory::create(['name' => $category, 'available_for' => ['student']]);
        HealthFormCategory::create(['name' => 'Annual', 'available_for' => ['faculty']]);
    }

    private function service(): TemporaryStudentFormCategoryService { return app(TemporaryStudentFormCategoryService::class); }
    private function actor(): User { return User::forceCreate(['name' => 'Clinic Admin', 'user_role' => 'superadmin']); }
    private function student(string $studentType = 'regular'): array
    {
        $user = User::forceCreate(['name' => 'Student Patient', 'user_type' => 'Student', 'student_type' => $studentType]);
        $profile = HealthProfile::forceCreate(['user_id' => $user->id, 'health_form_category' => 'Regular', 'clearance_status' => 'For Verification',
            'student_photo' => 'original-photo.jpg', 'health_declaration' => 'original-declaration.pdf',
            'medical_certificate' => 'original-medcert.pdf', 'chest_xray_result' => 'original-xray.pdf', 'digital_signature' => 'original-signature.png']);
        $submission = HealthFormSubmission::create(['user_id' => $user->id, 'health_profile_id' => $profile->id, 'category' => 'Regular',
            'status' => 'under_review', 'submitted_at' => now()->subDays(2), 'pdf_path' => 'original-form.pdf',
            'profile_snapshot' => ['profile' => $profile->attributesToArray(), 'user' => ['course' => 'Saved course']]]);
        foreach (['original-form.pdf', 'original-declaration.pdf', 'original-photo.jpg', 'original-medcert.pdf', 'original-xray.pdf', 'original-signature.png'] as $path) Storage::disk('health_private')->put($path, 'original bytes');
        return [$profile, $submission];
    }
    private function input(HealthFormSubmission $submission, string $category = 'On the Job Training'): array
    {
        return ['submission_id' => $submission->id, 'expected_category' => $submission->category, 'category' => $category];
    }
    private function expectPdfs(string $category, bool $failForm = false): void
    {
        $declaration = Mockery::mock(\Barryvdh\DomPDF\PDF::class);
        $declaration->shouldReceive('setPaper')->once()->andReturnSelf();
        $declaration->shouldReceive('output')->once()->andReturn('updated declaration PDF');
        Pdf::shouldReceive('loadView')->once()->with('student.print_declaration_form', Mockery::on(fn ($data) => $data['categorySelected'] === $category))->andReturn($declaration);
        $form = Mockery::mock(\Barryvdh\DomPDF\PDF::class);
        $form->shouldReceive('setPaper')->once()->andReturnSelf();
        if ($failForm) $form->shouldReceive('output')->once()->andThrow(new \RuntimeException('PDF render failed'));
        else $form->shouldReceive('output')->once()->andReturn('updated Health Form PDF');
        Pdf::shouldReceive('loadView')->once()->with('student.print_health_form', Mockery::on(fn ($data) =>
            $data['profile']->health_form_category === $category && $data['profile']->student_photo === 'original-photo.jpg'
            && $data['profile']->medical_certificate === 'original-medcert.pdf' && $data['profile']->chest_xray_result === 'original-xray.pdf'
        ))->andReturn($form);
    }

    public function test_first_pending_form_changes_in_place_preserving_uploads_dates_status_and_account_type(): void
    {
        [$profile, $submission] = $this->student();
        $this->expectPdfs('On the Job Training');
        $actor = $this->actor();
        $input = $this->input($submission);
        $changed = $this->service()->change($profile->id, $input, $actor);
        $this->assertSame($submission->id, $changed->id);
        $this->assertSame(1, HealthFormSubmission::count());
        $this->assertSame('under_review', $changed->status);
        $this->assertTrue($submission->submitted_at->equalTo($changed->submitted_at));
        $this->assertNull($changed->approved_at);
        $this->assertSame('On the Job Training', $profile->fresh()->health_form_category);
        $this->assertSame('regular', $profile->user->fresh()->student_type);
        $this->assertSame('For Verification', $profile->fresh()->clearance_status);
        $this->assertSame('original-photo.jpg', data_get($changed->profile_snapshot, 'profile.student_photo'));
        $this->assertSame('original-signature.png', data_get($changed->profile_snapshot, 'profile.digital_signature'));
        $this->assertSame($submission->snapshotUser(), $changed->snapshotUser());
        $this->assertSame($submission->snapshotProfile()['clearance_status'], $changed->snapshotProfile()['clearance_status']);
        $this->assertNotSame('original-declaration.pdf', data_get($changed->profile_snapshot, 'profile.health_declaration'));
        $this->assertSame('updated Health Form PDF', Storage::disk('health_private')->get($changed->pdf_path));
        Storage::disk('health_private')->assertExists(['original-form.pdf', 'original-declaration.pdf', 'original-medcert.pdf']);
        $this->assertSame($changed->id, $this->service()->change($profile->id, $input, $actor)->id);
        $this->assertSame(1, ActivityLog::count());
        $this->assertSame('Regular', ActivityLog::first()->metadata['previous_category']);
        $history = app(HealthRecordHistoryService::class)->forStudentProfile($profile->fresh('user'));
        $event = $history->firstWhere('title', 'Health Form Category Changed');
        $this->assertNotNull($event);
        $this->assertSame('Clinic Admin', $event['actor']);
    }

    public function test_renewal_changes_only_pending_copy_not_the_previous_approved_profile_or_pdf(): void
    {
        [$profile, $pending] = $this->student('ojt');
        $pending->delete();
        $approved = HealthFormSubmission::create(['user_id' => $profile->user_id, 'health_profile_id' => $profile->id, 'category' => 'Regular',
            'status' => 'approved', 'submitted_at' => now()->subMonth(), 'approved_at' => now()->subMonth(), 'pdf_path' => 'original-form.pdf',
            'profile_snapshot' => ['profile' => ['health_form_category' => 'Regular', 'health_declaration' => 'original-declaration.pdf']]]);
        $pending = HealthFormSubmission::create(['user_id' => $profile->user_id, 'health_profile_id' => $profile->id, 'category' => 'Regular',
            'status' => 'submitted', 'submitted_at' => now(), 'profile_snapshot' => $pending->profile_snapshot]);
        $request = HealthProfileCorrectionRequest::create(['health_profile_id' => $profile->id, 'health_form_submission_id' => $pending->id,
            'type' => 'new_health_form', 'status' => 'submitted', 'metadata' => ['category' => 'Regular', 'information_handling' => 'existing']]);
        $this->expectPdfs('Ladderized');
        $changed = $this->service()->change($profile->id, $this->input($pending, 'Ladderized'), $this->actor());
        $this->assertSame($pending->id, $changed->id);
        $this->assertSame('Regular', $profile->fresh()->health_form_category);
        $this->assertSame('original-declaration.pdf', $profile->fresh()->health_declaration);
        $this->assertSame('original-form.pdf', $approved->fresh()->pdf_path);
        $this->assertSame('Regular', $approved->fresh()->category);
        $this->assertSame('Ladderized', $request->fresh()->metadata['category']);
        $this->assertSame('submitted', $request->fresh()->status);
        $this->assertSame(2, HealthFormSubmission::count());
    }

    public function test_failed_regeneration_rolls_back_everything_and_removes_only_new_files(): void
    {
        [$profile, $submission] = $this->student();
        $this->expectPdfs('On the Job Training', true);
        $originalFiles = Storage::disk('health_private')->allFiles();
        try {
            $this->service()->change($profile->id, $this->input($submission), $this->actor());
            $this->fail('Expected PDF failure');
        } catch (\RuntimeException $exception) {
            $this->assertSame('PDF render failed', $exception->getMessage());
            $this->assertSame('Regular', $submission->fresh()->category);
            $this->assertSame('original-form.pdf', $submission->fresh()->pdf_path);
            $this->assertSame('original-declaration.pdf', $profile->fresh()->health_declaration);
            $this->assertSame($originalFiles, Storage::disk('health_private')->allFiles());
            $this->assertSame(0, ActivityLog::count());
        }
    }

    public function test_approved_requested_cancelled_or_wrong_submission_cannot_be_changed(): void
    {
        [$profile, $submission] = $this->student();
        Pdf::shouldReceive('loadView')->never();
        foreach (['approved', 'requested', 'cancelled', 'needs_correction'] as $status) {
            $submission->forceFill(['status' => $status])->save();
            try { $this->service()->change($profile->id, $this->input($submission), $this->actor()); $this->fail('Should reject '.$status); }
            catch (ValidationException $exception) { $this->assertSame('Regular', $submission->fresh()->category); }
        }
        $submission->forceFill(['status' => 'submitted', 'approved_at' => now()])->save();
        try { $this->service()->change($profile->id, $this->input($submission), $this->actor()); $this->fail('Should reject previously approved'); }
        catch (ValidationException $exception) { $this->assertSame('Regular', $submission->fresh()->category); }
        $submission->forceFill(['approved_at' => null])->save();
        $this->expectException(ValidationException::class);
        $this->service()->change($profile->id, array_merge($this->input($submission), ['submission_id' => $submission->id + 1]), $this->actor());
    }

    public function test_employee_applicant_and_dependent_accounts_are_rejected(): void
    {
        [$profile, $submission] = $this->student();
        foreach (['Faculty', 'Admin', 'Applicant', 'Dependent'] as $type) {
            $profile->user->forceFill(['user_type' => $type])->save();
            try { $this->service()->change($profile->id, $this->input($submission), $this->actor()); $this->fail('Should reject '.$type); }
            catch (HttpException $exception) { $this->assertSame(404, $exception->getStatusCode()); }
        }
        $this->assertSame(0, ActivityLog::count());
    }

    public function test_invalid_or_staff_category_and_stale_category_are_rejected(): void
    {
        [$profile, $submission] = $this->student('ladderized');
        foreach (['Annual', 'Unknown category'] as $category) {
            try { $this->service()->change($profile->id, $this->input($submission, $category), $this->actor()); $this->fail('Should reject category'); }
            catch (ValidationException $exception) { $this->assertSame('Regular', $submission->fresh()->category); }
        }
        $this->expectException(ValidationException::class);
        $this->service()->change($profile->id, array_merge($this->input($submission), ['expected_category' => 'Changed elsewhere']), $this->actor());
    }

    public function test_feature_flag_can_disable_the_temporary_action_and_routes_are_permission_protected(): void
    {
        foreach (['admin.health_profile.pending_category', 'admin.health_profile.pending_category.update'] as $name) {
            $middleware = app('router')->getRoutes()->getByName($name)->gatherMiddleware();
            $this->assertContains('admin.guard', $middleware);
            $this->assertContains('role:superadmin,admin', $middleware);
            $this->assertContains('module.permission:health_records.update_assessment', $middleware);
        }
        [$profile] = $this->student();
        config(['health_form_features.temporary_student_category_change' => false]);
        $this->expectException(HttpException::class);
        $this->service()->pending($profile);
    }

    public function test_real_pending_category_change_generates_both_printable_pdfs(): void
    {
        [$profile, $submission] = $this->student();
        $profile->forceFill(['age' => '22', 'sex' => 'Male'])->save();
        $snapshot = $submission->profile_snapshot;
        $snapshot['profile']['age'] = '22';
        $snapshot['profile']['sex'] = 'Male';
        $submission->profile_snapshot = $snapshot;
        $submission->save();
        $changed = $this->service()->change($profile->id, $this->input($submission), $this->actor());
        $this->assertStringStartsWith('%PDF-', Storage::disk('health_private')->get($changed->pdf_path));
        $this->assertStringStartsWith('%PDF-', Storage::disk('health_private')->get(data_get($changed->profile_snapshot, 'profile.health_declaration')));
        $this->assertSame('under_review', $changed->status);
        $this->assertNull($changed->approved_at);
    }
}
