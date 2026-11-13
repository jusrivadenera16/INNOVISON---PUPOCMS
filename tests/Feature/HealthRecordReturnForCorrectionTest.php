<?php

namespace Tests\Feature;

use App\Models\{ActivityLog, EmployeeHealthProfile, HealthFormSubmission, HealthProfile, HealthProfileCorrectionRequest, User};
use App\Services\{EmployeeHealthFormHistoryService, HealthFormPdfSnapshotService, HealthRecordCorrectionService};
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\{DB, Schema, Storage};
use Illuminate\Validation\ValidationException;
use Tests\TestCase;

class HealthRecordReturnForCorrectionTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        config(['database.default' => 'sqlite', 'database.connections.sqlite.database' => ':memory:',
            'health_data_encryption.enabled' => false, 'health_files.encryption_enabled' => false]);
        DB::purge('sqlite');
        Storage::fake('health_private');
        Storage::fake('public');
        Schema::create('users', function (Blueprint $table) {
            $table->id();
            foreach (['name', 'first_name', 'middle_name', 'last_name', 'email', 'user_type', 'user_role', 'idp_role', 'student_number'] as $field) $table->string($field)->nullable();
            $table->boolean('is_health_profile_completed')->default(true); $table->timestamps();
        });
        foreach (['health_profiles', 'health_profile_emp'] as $name) {
            Schema::create($name, function (Blueprint $table) {
                $table->id(); $table->unsignedBigInteger('user_id');
                foreach (['clearance_status', 'submission_status', 'health_form_category', 'pending_reason', 'pullout_status',
                    'student_photo', 'health_declaration', 'medical_certificate', 'home_address', 'school_year', 'staff_health_form_pdf_path'] as $field) $table->text($field)->nullable();
                $table->boolean('documents_valid')->nullable()->default(false);
                foreach (['resubmission_requested_at', 'resubmitted_at', 'verified_at'] as $field) $table->timestamp($field)->nullable();
                foreach (['approved_by_user_id', 'review_started_by_user_id'] as $field) $table->unsignedBigInteger($field)->nullable();
                $table->softDeletes(); $table->timestamps();
            });
        }
        Schema::create('health_form_submissions', function (Blueprint $table) {
            $table->id();
            foreach (['user_id', 'health_profile_id', 'employee_health_profile_id', 'approved_by_user_id', 'status_changed_by_user_id', 'requested_by_user_id'] as $field) $table->unsignedBigInteger($field)->nullable();
            foreach (['status', 'category', 'school_year', 'pdf_path', 'remarks', 'profile_snapshot'] as $field) $table->text($field)->nullable();
            foreach (['submitted_at', 'approved_at', 'requested_at', 'snapshot_captured_at'] as $field) $table->timestamp($field)->nullable();
            $table->timestamps();
        });
        Schema::create('health_profile_correction_requests', function (Blueprint $table) {
            $table->id();
            foreach (['user_id', 'health_profile_id', 'employee_health_profile_id', 'health_form_submission_id', 'requested_by_user_id', 'reviewed_by_user_id'] as $field) $table->unsignedBigInteger($field)->nullable();
            foreach (['profile_kind', 'type', 'status', 'admin_note', 'required_documents', 'metadata'] as $field) $table->text($field)->nullable();
            foreach (['requested_at', 'submitted_at', 'reviewed_at'] as $field) $table->timestamp($field)->nullable();
            $table->timestamps();
        });
        Schema::create('activity_logs', function (Blueprint $table) {
            $table->id(); $table->unsignedBigInteger('user_id')->nullable();
            foreach (['user_name', 'user_role', 'action', 'module', 'event_type', 'subject_type', 'subject_id', 'description', 'metadata'] as $field) $table->text($field)->nullable();
            $table->timestamps();
        });
    }

    private function fixture(string $kind, string $account): array
    {
        $actor = User::forceCreate(['name' => 'Reviewer', 'user_role' => 'superadmin']);
        $user = User::forceCreate(['name' => 'Patient', 'user_role' => 'student', 'user_type' => $account]);
        $class = $kind === 'employee' ? EmployeeHealthProfile::class : HealthProfile::class;
        $profile = $class::forceCreate(['user_id' => $user->id, 'clearance_status' => 'For Verification',
            'health_form_category' => $account === 'Dependent' ? 'Dependent' : 'General', 'student_photo' => 'saved/photo.jpg',
            'health_declaration' => 'saved/declaration.pdf', 'home_address' => 'Current profile address']);
        $key = $kind === 'employee' ? 'employee_health_profile_id' : 'health_profile_id';
        $old = HealthFormSubmission::create(['user_id' => $user->id, $key => $profile->id,
            'status' => 'approved', 'submitted_at' => now()->subDays(3), 'approved_at' => now()->subDays(2), 'pdf_path' => 'old-approved.pdf']);
        $latest = HealthFormSubmission::create(['user_id' => $user->id, $key => $profile->id,
            'status' => 'submitted', 'submitted_at' => now()->subDay(), 'category' => 'General', 'pdf_path' => 'returned-original.pdf',
            'profile_snapshot' => ['profile' => ['home_address' => 'Address in submitted form', 'student_photo' => 'saved/photo.jpg']]]);
        return [$profile, $old, $latest, $actor];
    }

    public function test_all_user_types_return_only_latest_form_and_keep_history_files_and_reason(): void
    {
        foreach (['student' => ['Student', 'Applicant'], 'employee' => ['Faculty', 'Admin', 'Dependent']] as $kind => $accounts) {
            foreach ($accounts as $account) {
                [$profile, $old, $latest, $actor] = $this->fixture($kind, $account);
                $count = HealthFormSubmission::count();
                $service = app(HealthRecordCorrectionService::class);
                $correction = $service->request($kind, $profile->id, $latest->id, 'Correct the address.', $actor);
                $this->assertSame($latest->id, $correction->health_form_submission_id);
                $this->assertSame('needs_correction', $latest->fresh()->status);
                $this->assertSame('approved', $old->fresh()->status);
                $this->assertNotNull($old->fresh()->approved_at);
                $this->assertSame('old-approved.pdf', $old->fresh()->pdf_path);
                $this->assertSame('saved/photo.jpg', $profile->fresh()->student_photo);
                $this->assertSame('saved/declaration.pdf', $profile->fresh()->health_declaration);
                $this->assertSame('Correct the address.', $correction->admin_note);
                $this->assertSame($count, HealthFormSubmission::count());
                $this->assertSame('Pending Resubmission', $profile->fresh()->clearance_status);
                $this->assertSame($correction->id, $service->request($kind, $profile->id, $latest->id, 'Retry', $actor)->id);
                $prefill = $service->prefill($profile->fresh(), $correction);
                $this->assertSame('Address in submitted form', $prefill->home_address);
                $this->assertSame('Current profile address', $profile->fresh()->home_address);
            }
        }
    }

    public function test_controller_accepts_confirmation_without_a_reason(): void
    {
        [$profile, $old, $latest, $actor] = $this->fixture('student', 'Student');
        $correction = app(HealthRecordCorrectionService::class)->request('student', $profile->id, $latest->id, '', $actor);
        $this->assertSame('', $correction->admin_note);
        $this->assertSame('Health Form Correction', $profile->fresh()->pending_reason);
        $request = \Illuminate\Http\Request::create('/return-for-correction', 'POST', ['submission_id' => $latest->id]);
        $request->setUserResolver(fn () => $actor);
        $response = app(\App\Http\Controllers\HealthRecordCorrectionController::class)
            ->store($request, 'student', $profile->id, app(HealthRecordCorrectionService::class));
        $this->assertSame(302, $response->getStatusCode());
        $this->assertSame(1, HealthProfileCorrectionRequest::count());
    }

    public function test_old_or_other_profile_submission_cannot_be_returned(): void
    {
        [$profile, $old, $latest, $actor] = $this->fixture('student', 'Student');
        try {
            app(HealthRecordCorrectionService::class)->request('student', $profile->id, $old->id, 'Old form', $actor);
            $this->fail('An older form must be rejected.');
        } catch (ValidationException $exception) {
            $this->assertArrayHasKey('submission_id', $exception->errors());
        }
        $this->assertSame('submitted', $latest->fresh()->status);
        $this->assertSame(0, HealthProfileCorrectionRequest::count());
        $this->assertSame(0, ActivityLog::count());
    }

    public function test_employee_and_dependent_resubmit_same_form_instead_of_creating_another(): void
    {
        foreach (['Faculty', 'Admin', 'Dependent'] as $account) {
            [$profile, $old, $latest, $actor] = $this->fixture('employee', $account);
            $request = app(HealthRecordCorrectionService::class)->request('employee', $profile->id, $latest->id, 'Fix address', $actor);
            $count = HealthFormSubmission::count();
            $profile->forceFill(['home_address' => 'Corrected address', 'staff_health_form_pdf_path' => 'corrected.pdf'])->save();
            $submission = app(EmployeeHealthFormHistoryService::class)->createSubmittedSnapshot($profile, $request);
            $request->markSubmitted();
            $this->assertSame($latest->id, $submission->id);
            $this->assertSame('submitted', $submission->status);
            $this->assertSame('Corrected address', data_get($submission->profile_snapshot, 'profile.home_address'));
            $this->assertSame($count, HealthFormSubmission::count());
            $this->assertSame('approved', $old->fresh()->status);
            $this->assertSame('submitted', $request->fresh()->status);
        }
    }

    public function test_student_and_applicant_resubmit_same_form_and_keep_original_pdf(): void
    {
        $pdf = \Mockery::mock(\Barryvdh\DomPDF\PDF::class);
        $pdf->shouldReceive('setPaper')->andReturnSelf(); $pdf->shouldReceive('output')->andReturn('corrected PDF');
        Pdf::shouldReceive('loadView')->andReturn($pdf);
        foreach (['Student', 'Applicant'] as $account) {
            [$profile, $old, $latest, $actor] = $this->fixture('student', $account);
            Storage::disk('public')->put('returned-original.pdf', 'original PDF');
            $service = app(HealthRecordCorrectionService::class);
            $request = $service->request('student', $profile->id, $latest->id, 'Fix address', $actor);
            $count = HealthFormSubmission::count();
            $target = $service->resubmissionTarget($profile, $request);
            $profile->forceFill(['home_address' => 'Corrected address'])->save();
            $submission = app(HealthFormPdfSnapshotService::class)->recordSubmittedWithoutPdf($profile, $profile->user, 'General', null, false, null, false, $target);
            $this->assertSame($latest->id, $submission->id);
            $this->assertSame('submitted', $submission->status);
            $this->assertSame($count, HealthFormSubmission::count());
            $this->assertSame('approved', $old->fresh()->status);
            Storage::disk('public')->assertExists('returned-original.pdf');
        }
    }

    public function test_button_is_next_to_withdraw_and_route_retains_record_management_permissions(): void
    {
        $view = file_get_contents(resource_path('views/admin/health_records.blade.php'));
        $this->assertMatchesRegularExpression('/id="verifyWithdrawHealthRecordBtn".*?@if\(optional\(auth\(\)->user\(\)\)->canAccessPermission\(\'health_records.request_resubmission\'\)\)\s*<button[^>]*id="verifyReturnForCorrectionBtn"/s', $view);
        $route = app('router')->getRoutes()->getByName('admin.health_records.return_for_correction');
        $this->assertContains('module.permission:health_records.request_resubmission', $route->gatherMiddleware());
        $this->assertContains('role:superadmin,admin', $route->gatherMiddleware());
    }

    private function employeeControllerSchema(): void
    {
        foreach (['admins' => 'access_level', 'admin_hub' => 'role'] as $name => $field) {
            Schema::create($name, function (Blueprint $table) use ($field) { $table->id(); $table->unsignedBigInteger('user_id'); $table->string($field)->nullable(); });
        }
        $existing = Schema::getColumnListing('health_profile_emp');
        $fields = array_diff((new EmployeeHealthProfile())->getFillable(), $existing);
        Schema::table('health_profile_emp', function (Blueprint $table) use ($fields) {
            foreach ($fields as $field) $table->text($field)->nullable();
        });
        Schema::table('users', function (Blueprint $table) {
            foreach (['DOB', 'gender', 'contact_no', 'employee_number', 'suffix_name', 'student_id', 'reference_number', 'sis_reference_number'] as $field) $table->string($field)->nullable();
        });
        Schema::table('activity_logs', function (Blueprint $table) { $table->text('ip_address')->nullable(); $table->text('user_agent')->nullable(); });
        $this->mock(\App\Services\AcademicYearService::class)->shouldReceive('label')->andReturn('2026-2027');
    }

    private function employeeCorrectionPayload(EmployeeHealthProfile $profile, string $account): \Illuminate\Http\Request
    {
        $data = ['first_name' => 'Patient', 'middle_name' => '', 'last_name' => 'Corrected', 'suffix_name' => '',
            'street_address' => 'Corrected Street', 'barangay' => 'Central', 'city_municipality' => 'Taguig', 'province' => 'Metro Manila',
            'contact_no' => '09123456789', 'emergency_contact_person' => 'Guardian', 'emergency_contact_no' => '09123456789',
            'form_date' => now()->toDateString(), 'office' => 'Office', 'age' => 26, 'sex' => 'Male', 'civil_status' => 'Single',
            'birthday' => '2000-01-01', 'employee_signature_method' => 'draw',
            'employee_signature' => 'data:image/png;base64,' . base64_encode('new signature'),
            'employee_consent_acknowledged' => 1, 'employee_health_profile_certified' => 1];
        if ($account === 'Dependent') $data['id_number'] = 'DEP-' . $profile->id;
        else $data['employee_number'] = (string) (10000 + $profile->id);
        return \Illuminate\Http\Request::create('/', 'POST', $data);
    }

    public function test_actual_employee_and_dependent_controller_resubmits_without_duplicate_or_photo_reupload(): void
    {
        $this->employeeControllerSchema();
        $this->mock(\App\Services\EmployeeHealthFormPdfService::class)->shouldReceive('generate')->times(3)->andReturnUsing(function ($profile) {
            $profile->staff_health_form_pdf_path = 'corrected-form.pdf'; $profile->save(); return 'corrected-form.pdf';
        });
        foreach (['Faculty', 'Admin', 'Dependent'] as $account) {
            [$profile, $old, $latest, $actor] = $this->fixture('employee', $account);
            if ($account !== 'Dependent') $profile->forceFill(['employee_number' => (string) (10000 + $profile->id)])->save();
            $correction = app(HealthRecordCorrectionService::class)->request('employee', $profile->id, $latest->id, 'Correct street.', $actor);
            $this->actingAs($profile->user, 'student');
            $count = HealthFormSubmission::count();
            $response = app(\App\Http\Controllers\AppointmentController::class)->storeEmployeeHealthForm($this->employeeCorrectionPayload($profile, $account));
            $this->assertSame(302, $response->getStatusCode());
            $this->assertFalse(session()->has('error'), (string) session('error'));
            $this->assertSame($count, HealthFormSubmission::count());
            $this->assertSame('submitted', $latest->fresh()->status);
            $this->assertSame('For Verification', $profile->fresh()->clearance_status);
            $this->assertSame('submitted', $correction->fresh()->status);
            $this->assertSame($latest->id, $correction->fresh()->health_form_submission_id);
            $this->assertSame('saved/photo.jpg', $profile->fresh()->student_photo);
            $this->assertSame('saved/declaration.pdf', $profile->fresh()->health_declaration);
            $this->assertSame('approved', $old->fresh()->status);
            $queueProfile = $profile->fresh('user');
            app(\App\Services\HealthRecordApprovalQueue::class)->markEligibility(collect([$queueProfile]));
            $this->assertTrue($queueProfile->pending_approval_eligible);
            $this->assertSame($latest->id, $queueProfile->pending_approval_submission_id);
            app(\App\Http\Controllers\AppointmentController::class)->storeEmployeeHealthForm($this->employeeCorrectionPayload($profile, $account));
            $this->assertSame($count, HealthFormSubmission::count(), 'Double submission must not create another form.');
        }
    }

    public function test_actual_employee_controller_rolls_back_failed_resubmission(): void
    {
        $this->employeeControllerSchema();
        [$profile, $old, $latest, $actor] = $this->fixture('employee', 'Faculty');
        app(HealthRecordCorrectionService::class)->request('employee', $profile->id, $latest->id, 'Fix address.', $actor);
        $this->actingAs($profile->user, 'student');
        $this->mock(\App\Services\EmployeeHealthFormPdfService::class)->shouldReceive('generate')->once()->andThrow(new \RuntimeException('PDF failed'));
        $response = app(\App\Http\Controllers\AppointmentController::class)->storeEmployeeHealthForm($this->employeeCorrectionPayload($profile, 'Faculty'));
        $this->assertSame(302, $response->getStatusCode());
        $this->assertTrue(session()->has('error'));
        $this->assertSame('Pending Resubmission', $profile->fresh()->clearance_status);
        $this->assertSame('Current profile address', $profile->fresh()->home_address);
        $this->assertSame('needs_correction', $latest->fresh()->status);
        $this->assertSame('approved', $old->fresh()->status);
    }

    public function test_returned_profile_and_resubmission_cannot_target_another_profile_or_newer_form(): void
    {
        [$profile, $old, $latest, $actor] = $this->fixture('student', 'Student');
        $service = app(HealthRecordCorrectionService::class);
        $correction = $service->request('student', $profile->id, $latest->id, 'Correct address.', $actor);
        $this->assertSame($profile->id, $service->returnedProfile($profile->user, 'student')->id);
        [$other] = $this->fixture('student', 'Student');
        $this->assertNull($service->returnedProfile($other->user, 'student'));
        try {
            $service->resubmissionTarget($other, $correction);
            $this->fail('A foreign correction must be rejected.');
        } catch (ValidationException $exception) {
            $this->assertArrayHasKey('health_form', $exception->errors());
        }
        HealthFormSubmission::create(['health_profile_id' => $profile->id, 'user_id' => $profile->user_id, 'status' => 'submitted', 'submitted_at' => now()]);
        try {
            $service->resubmissionTarget($profile, $correction);
            $this->fail('A stale correction must not overwrite a newer form.');
        } catch (ValidationException $exception) {
            $this->assertArrayHasKey('health_form', $exception->errors());
        }
        $this->assertSame('needs_correction', $latest->fresh()->status);
    }

    public function test_actual_student_controller_updates_existing_submission_without_reuploading_documents(): void
    {
        $this->employeeControllerSchema();
        $existing = Schema::getColumnListing('health_profiles');
        $fields = array_diff((new HealthProfile())->getFillable(), $existing);
        Schema::table('health_profiles', function (Blueprint $table) use ($fields) {
            foreach ($fields as $field) $table->text($field)->nullable();
        });
        Schema::table('users', function (Blueprint $table) {
            foreach (['year', 'section', 'course', 'student_type'] as $field) $table->string($field)->nullable();
        });
        $existing = Schema::getColumnListing('activity_logs');
        $fields = array_diff((new ActivityLog())->getFillable(), $existing);
        Schema::table('activity_logs', function (Blueprint $table) use ($fields) {
            foreach ($fields as $field) $table->text($field)->nullable();
        });
        [$profile, $old, $latest, $actor] = $this->fixture('student', 'Student');
        $profile->user->forceFill(['student_number' => '2026-12345-TG-0', 'student_type' => 'regular'])->save();
        $profile->forceFill(['student_number' => '2026-12345-TG-0', 'reference_number' => '2026-12345-TG-0', 'health_form_category' => 'Student'])->save();
        $latest->update(['category' => 'Student']);
        $correction = app(HealthRecordCorrectionService::class)->request('student', $profile->id, $latest->id, 'Fix street.', $actor);
        $this->actingAs($profile->user, 'student');
        \Illuminate\Support\Facades\Http::preventStrayRequests();
        $pdf = \Mockery::mock(\Barryvdh\DomPDF\PDF::class);
        $pdf->shouldReceive('setPaper')->andReturnSelf(); $pdf->shouldReceive('output')->andReturn('corrected PDF');
        Pdf::shouldReceive('loadView')->andReturn($pdf);
        $request = \Illuminate\Http\Request::create('/student/form', 'POST', [
            'reference_number' => '2026-12345-TG-0', 'year' => '2nd Year', 'section' => '1', 'course_code' => 'BSIT',
            'home_address' => 'Corrected address', 'home_address_street' => 'Corrected Street', 'home_address_barangay' => 'Central',
            'home_address_city_municipality' => 'Taguig', 'home_address_province' => 'Metro Manila', 'zipcode' => '1630',
            'birthday' => '2000-01-01', 'age' => 26, 'sex' => 'Male', 'civil_status' => 'Single', 'blood_type' => 'O+',
            'contact_no' => '09123456789', 'guardian_name' => 'Guardian', 'landline' => 'N/A', 'cellphone' => '09123456789',
            'has_illness' => 'No', 'is_smoker' => 'No', 'is_drinker' => 'No', 'covid_vaccinated' => 'No', 'has_disability' => 'No',
            'signature_method' => 'draw', 'digital_signature_data' => 'data:image/png;base64,' . base64_encode(str_repeat('a', 250)),
            'consent_acknowledged' => 1, 'health_profile_certified' => 1,
        ]);
        $route = new \Illuminate\Routing\Route('POST', '/student/form', fn () => null);
        $route->name('store.health.form.student'); $route->bind($request); $request->setRouteResolver(fn () => $route);
        $response = app(\App\Http\Controllers\AppointmentController::class)->storeHealthForm($request);
        $this->assertSame(302, $response->getStatusCode());
        $this->assertFalse(session()->has('error'), (string) session('error'));
        $this->assertSame(2, HealthFormSubmission::count());
        $this->assertSame('submitted', $latest->fresh()->status);
        $this->assertSame('submitted', $correction->fresh()->status);
        $this->assertSame($latest->id, $correction->fresh()->health_form_submission_id);
        $this->assertSame('saved/photo.jpg', $profile->fresh()->student_photo);
        $this->assertSame('approved', $old->fresh()->status);
    }
}
