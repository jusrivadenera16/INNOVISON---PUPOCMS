<?php

namespace Tests\Feature;

use App\Models\EmployeeHealthProfile;
use App\Models\HealthFormSubmission;
use App\Models\HealthProfile;
use App\Models\User;
use App\Services\EmployeeHealthFormPdfService;
use App\Services\HealthFormPdfSnapshotService;
use App\Services\HealthProfileSnapshotService;
use App\Services\StoredImageDataUri;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Http\Request;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Storage;
use Mockery;
use Tests\TestCase;

class HealthFormPdfRefreshTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        config()->set('database.default', 'sqlite');
        config()->set('database.connections.sqlite', [
            'driver' => 'sqlite',
            'database' => ':memory:',
            'prefix' => '',
            'foreign_key_constraints' => false,
        ]);
        DB::purge('sqlite');
        DB::setDefaultConnection('sqlite');

        Schema::create('users', function (Blueprint $table) {
            $table->id();
            $table->string('name')->nullable();
            $table->string('first_name')->nullable();
            $table->string('middle_name')->nullable();
            $table->string('last_name')->nullable();
            $table->string('email')->nullable();
            $table->string('user_type')->nullable();
            $table->string('user_role')->nullable();
            $table->string('student_id')->nullable();
            $table->string('student_number')->nullable();
            $table->string('employee_number')->nullable();
            $table->string('reference_number')->nullable();
            $table->string('sis_reference_number')->nullable();
            $table->date('DOB')->nullable();
            $table->string('contact_no')->nullable();
            $table->string('gender')->nullable();
            $table->string('suffix_name')->nullable();
            $table->string('course')->nullable();
            $table->string('year')->nullable();
            $table->string('section')->nullable();
            $table->boolean('is_health_profile_completed')->default(false);
            $table->timestamps();
        });

        Schema::create('health_profiles', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id')->nullable();
            $table->string('student_id')->nullable();
            $table->string('student_number')->nullable();
            $table->string('reference_number')->nullable();
            $table->string('school_year')->nullable();
            $table->string('health_form_category')->nullable();
            $table->string('home_address')->nullable();
            $table->string('digital_signature')->nullable();
            $table->string('student_photo')->nullable();
            $table->string('medical_condition_remarks')->nullable();
            $table->boolean('documents_valid')->default(false);
            $table->string('medical_certificate')->nullable();
            $table->string('clearance_status')->nullable();
            $table->string('pending_reason')->nullable();
            $table->string('physical_assessment_status')->nullable();
            $table->json('resubmission_required_documents')->nullable();
            $table->timestamp('resubmission_requested_at')->nullable();
            $table->timestamp('resubmitted_at')->nullable();
            $table->timestamp('clearance_revoked_at')->nullable();
            $table->timestamp('verified_at')->nullable();
            $table->unsignedBigInteger('approved_by_user_id')->nullable();
            $table->string('pullout_status')->nullable();
            $table->date('birthday')->nullable();
            $table->timestamps();
        });

        Schema::create('health_form_submissions', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id')->nullable();
            $table->unsignedBigInteger('health_profile_id')->nullable();
            $table->unsignedBigInteger('employee_health_profile_id')->nullable();
            $table->string('category')->nullable();
            $table->string('school_year')->nullable();
            $table->string('status');
            $table->string('pdf_path')->nullable();
            $table->json('profile_snapshot')->nullable();
            $table->timestamp('snapshot_captured_at')->nullable();
            $table->unsignedBigInteger('requested_by_user_id')->nullable();
            $table->timestamp('requested_at')->nullable();
            $table->timestamp('submitted_at')->nullable();
            $table->timestamp('approved_at')->nullable();
            $table->unsignedBigInteger('approved_by_user_id')->nullable();
            $table->unsignedBigInteger('status_changed_by_user_id')->nullable();
            $table->text('remarks')->nullable();
            $table->timestamps();
        });

        Schema::create('health_profile_emp', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id')->nullable();
            $table->string('employee_number')->nullable();
            $table->string('uploaded_signature_path')->nullable();
            $table->longText('staff_signature')->nullable();
            $table->string('staff_health_form_pdf_path')->nullable();
            $table->string('student_photo')->nullable();
            $table->string('clearance_status')->nullable();
            $table->string('submission_status')->nullable();
            $table->string('health_form_category')->nullable();
            $table->string('school_year')->nullable();
            $table->timestamp('verified_at')->nullable();
            $table->timestamp('certified_at')->nullable();
            $table->unsignedBigInteger('approved_by_user_id')->nullable();
            $table->softDeletes();
            $table->timestamps();
        });

        Schema::create('health_profile_correction_requests', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id')->nullable();
            $table->unsignedBigInteger('health_profile_id')->nullable();
            $table->string('status');
            $table->string('type');
            $table->timestamp('requested_at')->nullable();
            $table->timestamps();
        });
        Schema::create('activity_logs', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id')->nullable();
            foreach (['user_name', 'action', 'description', 'ip_address', 'user_agent'] as $field) $table->text($field)->nullable();
            $table->timestamps();
        });

        Storage::fake('health_private');
        Storage::fake('public');
        config([
            'health_files.write_disk' => 'health_private',
            'health_files.legacy_disk' => 'public',
            'health_files.legacy_fallback' => true,
            'health_files.mirror_to_legacy' => false,
            'health_files.delete_legacy_on_replace' => false,
        ]);
    }

    public function test_student_snapshot_refresh_preserves_review_state(): void
    {
        Carbon::setTestNow('2026-07-28 15:30:00');

        $user = User::forceCreate([
            'name' => 'Test Student',
            'student_number' => '2026-0001',
        ]);
        $profile = HealthProfile::forceCreate([
            'user_id' => $user->id,
            'student_number' => '2026-0001',
            'digital_signature' => 'health_profiles/signatures/test.png',
        ]);
        $oldPath = 'health_form_submissions/' . $user->id . '/old.pdf';
        $submittedAt = Carbon::parse('2026-07-20 09:00:00');
        $submission = HealthFormSubmission::create([
            'user_id' => $user->id,
            'health_profile_id' => $profile->id,
            'category' => 'Enrollment',
            'status' => HealthFormSubmission::STATUS_NEEDS_CORRECTION,
            'pdf_path' => $oldPath,
            'submitted_at' => $submittedAt,
            'remarks' => 'Keep this review note.',
        ]);
        Storage::disk('health_private')->put($oldPath, 'old-pdf');

        $dompdf = Mockery::mock(\Barryvdh\DomPDF\PDF::class);
        $dompdf->shouldReceive('setPaper')->once()->with([0, 0, 612, 936])->andReturnSelf();
        $dompdf->shouldReceive('output')->once()->andReturn('new-pdf');
        Pdf::shouldReceive('loadView')
            ->once()
            ->with('student.print_health_form', Mockery::on(function (array $data) use ($profile, $submittedAt) {
                return (int) $data['profile']->id === (int) $profile->id
                    && $data['pdfMode'] === true
                    && $submittedAt->equalTo($data['healthFormSubmittedAt']);
            }))
            ->andReturn($dompdf);

        $refreshed = app(HealthFormPdfSnapshotService::class)->refreshExistingSnapshot($profile);

        $this->assertNotNull($refreshed);
        $this->assertSame($submission->id, $refreshed->id);
        $this->assertSame(HealthFormSubmission::STATUS_NEEDS_CORRECTION, $refreshed->status);
        $this->assertSame('Keep this review note.', $refreshed->remarks);
        $this->assertTrue($submittedAt->equalTo($refreshed->submitted_at));
        $this->assertNotSame($oldPath, $refreshed->pdf_path);
        Storage::disk('health_private')->assertMissing($oldPath);
        $this->assertSame('new-pdf', Storage::disk('health_private')->get($refreshed->pdf_path));
    }

    public function test_corrected_photo_refreshes_pending_pdf_and_snapshot_then_freezes_on_approval(): void
    {
        foreach (['Student', 'Applicant'] as $type) {
            $user = User::forceCreate(['name' => 'Corrected Patient', 'user_type' => $type]);
            $profile = HealthProfile::forceCreate([
                'user_id' => $user->id, 'student_photo' => 'new-photo.png', 'home_address' => 'Current address',
            ]);
            $oldPath = 'health_form_submissions/' . $user->id . '/pending.pdf';
            Storage::disk('health_private')->put($oldPath, 'old PDF with wrong photo');
            $submission = HealthFormSubmission::create([
                'user_id' => $user->id, 'health_profile_id' => $profile->id,
                'status' => HealthFormSubmission::STATUS_NEEDS_CORRECTION,
                'pdf_path' => $oldPath, 'submitted_at' => now(),
                'profile_snapshot' => ['profile' => ['student_photo' => 'wrong-photo.png', 'home_address' => 'Staged address']],
            ]);
            $pdf = Mockery::mock(\Barryvdh\DomPDF\PDF::class);
            $pdf->shouldReceive('setPaper')->twice()->andReturnSelf();
            $pdf->shouldReceive('output')->twice()->andReturn('corrected PDF', 'final approved PDF');
            Pdf::shouldReceive('loadView')->twice()->with('student.print_health_form', Mockery::on(
                fn (array $data) => $data['profile']->student_photo === 'new-photo.png'
            ))->andReturn($pdf);
            $service = app(HealthFormPdfSnapshotService::class);
            $pending = $service->refreshAfterDocumentResubmission($profile, ['student_photo']);
            $this->assertSame($submission->id, $pending->id);
            $this->assertSame(HealthFormSubmission::STATUS_NEEDS_CORRECTION, $pending->status);
            $this->assertSame('new-photo.png', data_get($pending->profile_snapshot, 'profile.student_photo'));
            $this->assertSame('Staged address', data_get($pending->profile_snapshot, 'profile.home_address'));
            $pendingPath = $pending->pdf_path;
            $approved = $service->saveApprovedSnapshot($profile, $user, null, null, $pending);
            $frozenSnapshot = $approved->profile_snapshot;
            $frozenPath = $approved->pdf_path;
            $this->assertNotSame($pendingPath, $frozenPath);
            $profile->student_photo = 'later-edit.png';
            $profile->save();
            $this->assertNull($service->refreshExistingSnapshot($profile));
            $this->assertSame($frozenPath, $service->saveApprovedSnapshot($profile, $user, null, null, $approved)->pdf_path);
            $this->assertSame($frozenSnapshot, $approved->fresh()->profile_snapshot);
            $this->assertSame('final approved PDF', Storage::disk('health_private')->get($frozenPath));
        }
    }

    public function test_resubmission_after_approval_creates_a_new_review_copy_and_prints_it_without_overwriting_history(): void
    {
        $user = User::forceCreate(['name' => 'Revision Patient']);
        $profile = HealthProfile::forceCreate(['user_id' => $user->id, 'student_photo' => 'correct-photo.png']);
        $approvedPath = 'health_form_submissions/' . $user->id . '/approved.pdf';
        Storage::disk('health_private')->put($approvedPath, 'frozen approved PDF');
        $approved = HealthFormSubmission::create([
            'user_id' => $user->id, 'health_profile_id' => $profile->id,
            'status' => HealthFormSubmission::STATUS_APPROVED, 'pdf_path' => $approvedPath,
            'submitted_at' => now()->subMonth(), 'approved_at' => now()->subMonth(),
            'profile_snapshot' => ['profile' => ['student_photo' => 'original-approved-photo.png']],
        ]);
        $pdf = Mockery::mock(\Barryvdh\DomPDF\PDF::class);
        $pdf->shouldReceive('setPaper')->once()->andReturnSelf();
        $pdf->shouldReceive('output')->once()->andReturn('new review PDF');
        Pdf::shouldReceive('loadView')->once()->andReturn($pdf);
        $pending = app(HealthFormPdfSnapshotService::class)->refreshAfterDocumentResubmission($profile, ['student_photo']);
        $this->assertNotSame($approved->id, $pending->id);
        $this->assertSame(HealthFormSubmission::STATUS_SUBMITTED, $pending->status);
        $this->assertSame('correct-photo.png', data_get($pending->profile_snapshot, 'profile.student_photo'));
        $this->assertSame($approvedPath, $approved->fresh()->pdf_path);
        $this->assertSame('original-approved-photo.png', data_get($approved->fresh()->profile_snapshot, 'profile.student_photo'));
        $this->assertSame('frozen approved PDF', Storage::disk('health_private')->get($approvedPath));
        $method = new \ReflectionMethod(\App\Http\Controllers\AppointmentController::class, 'latestHealthFormSubmissionForProfile');
        $this->assertSame($pending->id, $method->invoke(app(\App\Http\Controllers\AppointmentController::class), $profile)->id);
    }

    public function test_employee_pdf_is_frozen_after_approval_and_corrections_use_a_separate_review_copy(): void
    {
        $user = User::forceCreate(['name' => 'Employee Patient']);
        $approvedAt = now()->subMonth();
        $approvedPath = 'health_profile_employees/health_forms/approved.pdf';
        Storage::disk('health_private')->put($approvedPath, 'frozen employee PDF');
        $profile = EmployeeHealthProfile::forceCreate([
            'user_id' => $user->id, 'clearance_status' => 'Fully Cleared', 'submission_status' => 'approved',
            'verified_at' => $approvedAt, 'staff_health_form_pdf_path' => $approvedPath, 'student_photo' => 'changed-photo.png',
        ]);
        $approved = HealthFormSubmission::create([
            'user_id' => $user->id, 'employee_health_profile_id' => $profile->id, 'status' => HealthFormSubmission::STATUS_APPROVED,
            'approved_at' => $approvedAt, 'submitted_at' => $approvedAt, 'pdf_path' => $approvedPath,
            'profile_snapshot' => ['profile' => ['student_photo' => 'original-photo.png']],
        ]);
        $service = app(EmployeeHealthFormPdfService::class);
        $this->assertSame($approvedPath, $service->generate($profile));
        $profile->clearance_status = 'For Verification';
        $profile->save();
        $pdf = Mockery::mock(\Barryvdh\DomPDF\PDF::class);
        $pdf->shouldReceive('setPaper')->once()->andReturnSelf();
        $pdf->shouldReceive('output')->once()->andReturn('corrected employee review');
        Pdf::shouldReceive('loadView')->once()->with('student.print_employee_health_form', Mockery::on(
            fn (array $data) => $data['employeeProfile']->student_photo === 'changed-photo.png'
        ))->andReturn($pdf);
        $path = $service->generate($profile);
        $pending = app(\App\Services\EmployeeHealthFormHistoryService::class)->refreshSubmittedSnapshot($profile->fresh());
        $this->assertNotSame($approvedPath, $path);
        $this->assertSame($path, $pending->pdf_path);
        $this->assertSame(HealthFormSubmission::STATUS_SUBMITTED, $pending->status);
        $this->assertSame('changed-photo.png', data_get($pending->profile_snapshot, 'profile.student_photo'));
        $this->assertSame('original-photo.png', data_get($approved->fresh()->profile_snapshot, 'profile.student_photo'));
        $this->assertSame('frozen employee PDF', Storage::disk('health_private')->get($approvedPath));
    }

    public function test_photo_resubmission_controller_refreshes_the_pending_pdf(): void
    {
        $user = User::forceCreate(['name' => 'Photo Student', 'user_type' => 'Student']);
        $user->setRelation('adminProfile', null)->setRelation('adminHubProfile', null);
        $this->actingAs($user);
        $profile = HealthProfile::forceCreate([
            'user_id' => $user->id, 'clearance_status' => 'Pending Resubmission',
            'student_photo' => 'old-photo.png', 'resubmission_required_documents' => ['student_photo'],
        ]);
        $submission = HealthFormSubmission::create([
            'user_id' => $user->id, 'health_profile_id' => $profile->id, 'submitted_at' => now(),
            'status' => HealthFormSubmission::STATUS_NEEDS_CORRECTION, 'pdf_path' => 'old-form.pdf',
        ]);
        Storage::disk('health_private')->put('old-form.pdf', 'old PDF');
        $pdf = Mockery::mock(\Barryvdh\DomPDF\PDF::class);
        $pdf->shouldReceive('setPaper')->once()->andReturnSelf();
        $pdf->shouldReceive('output')->once()->andReturn('corrected resubmitted PDF');
        Pdf::shouldReceive('loadView')->once()->with('student.print_health_form', Mockery::on(
            fn (array $data) => $data['profile']->student_photo !== 'old-photo.png'
                && str_starts_with($data['profile']->student_photo, 'health_profiles/photos/')
        ))->andReturn($pdf);
        $request = Request::create('/student/health-record/resubmit', 'POST', [], [], [
            'student_photo' => UploadedFile::fake()->image('correct.jpg', 100, 100),
        ]);
        $response = app(\App\Http\Controllers\AppointmentController::class)->resubmitHealthRecordRequirements($request);
        $this->assertSame(302, $response->getStatusCode());
        $this->assertFalse(session()->has('error'), (string) session('error'));
        $this->assertSame('For Verification', $profile->fresh()->clearance_status);
        $this->assertNotSame('old-photo.png', $profile->fresh()->student_photo);
        $this->assertSame($profile->fresh()->student_photo, data_get($submission->fresh()->profile_snapshot, 'profile.student_photo'));
        $this->assertSame('corrected resubmitted PDF', Storage::disk('health_private')->get($submission->fresh()->pdf_path));
    }

    public function test_failed_resubmission_pdf_rolls_back_the_photo_and_review_state(): void
    {
        $user = User::forceCreate(['name' => 'Retry Student', 'user_type' => 'Student']);
        $user->setRelation('adminProfile', null)->setRelation('adminHubProfile', null);
        $this->actingAs($user);
        $profile = HealthProfile::forceCreate([
            'user_id' => $user->id, 'clearance_status' => 'Pending Resubmission',
            'student_photo' => 'original-photo.png', 'resubmission_required_documents' => ['student_photo'],
        ]);
        $submission = HealthFormSubmission::create([
            'user_id' => $user->id, 'health_profile_id' => $profile->id, 'submitted_at' => now(),
            'status' => HealthFormSubmission::STATUS_NEEDS_CORRECTION, 'pdf_path' => 'original-form.pdf',
        ]);
        Storage::disk('health_private')->put('original-form.pdf', 'original PDF');
        Pdf::shouldReceive('loadView')->once()->andThrow(new \RuntimeException('PDF rendering failed'));
        $request = Request::create('/student/health-record/resubmit', 'POST', [], [], [
            'student_photo' => UploadedFile::fake()->image('replacement.jpg', 100, 100),
        ]);
        app(\App\Http\Controllers\AppointmentController::class)->resubmitHealthRecordRequirements($request);
        $this->assertTrue(session()->has('error'));
        $this->assertSame('original-photo.png', $profile->fresh()->student_photo);
        $this->assertSame('Pending Resubmission', $profile->fresh()->clearance_status);
        $this->assertSame('original-form.pdf', $submission->fresh()->pdf_path);
        $this->assertSame('original PDF', Storage::disk('health_private')->get('original-form.pdf'));
    }

    public function test_admin_approval_is_not_saved_when_final_pdf_generation_fails(): void
    {
        $owner = User::forceCreate(['name' => 'Approval Student', 'user_type' => 'Student']);
        $admin = User::forceCreate(['name' => 'Approving Admin', 'user_role' => User::ROLE_SUPERADMIN]);
        $this->actingAs($admin);
        $profile = HealthProfile::forceCreate(['user_id' => $owner->id, 'clearance_status' => 'For Verification']);
        $this->mock(HealthFormPdfSnapshotService::class)->shouldReceive('saveApprovedSnapshot')->once()
            ->andThrow(new \RuntimeException('Final PDF failed'));
        $request = Request::create('/health-profile/' . $profile->id . '/clearance', 'PUT', [
            'clearance_status' => 'Fully Cleared', 'documents_valid' => 'yes',
            'physical_assessment_status' => 'Completed / Passed',
        ]);
        app(\App\Http\Controllers\AdminController::class)->updateClearance($request, $profile->id);
        $this->assertStringContainsString('Approval was not saved', (string) session('error'));
        $this->assertSame('For Verification', $profile->fresh()->clearance_status);
        $this->assertNull($profile->fresh()->verified_at);
        $this->assertSame(0, HealthFormSubmission::count());
    }

    public function test_missing_approved_employee_pdf_rebuilds_from_frozen_photo_not_later_edits(): void
    {
        $user = User::forceCreate(['name' => 'Frozen Employee']);
        $approvedAt = now()->subMonth();
        $profile = EmployeeHealthProfile::forceCreate([
            'user_id' => $user->id, 'clearance_status' => 'Fully Cleared', 'verified_at' => $approvedAt,
            'student_photo' => 'later-edit.png', 'staff_health_form_pdf_path' => 'missing.pdf',
        ]);
        $approved = HealthFormSubmission::create([
            'user_id' => $user->id, 'employee_health_profile_id' => $profile->id,
            'status' => HealthFormSubmission::STATUS_APPROVED, 'approved_at' => $approvedAt,
            'pdf_path' => 'missing.pdf', 'profile_snapshot' => ['profile' => ['student_photo' => 'approved-photo.png']],
        ]);
        $snapshot = $approved->profile_snapshot;
        $pdf = Mockery::mock(\Barryvdh\DomPDF\PDF::class);
        $pdf->shouldReceive('setPaper')->once()->andReturnSelf();
        $pdf->shouldReceive('output')->once()->andReturn('rebuilt frozen PDF');
        Pdf::shouldReceive('loadView')->once()->with('student.print_employee_health_form', Mockery::on(
            fn (array $data) => $data['employeeProfile']->student_photo === 'approved-photo.png'
        ))->andReturn($pdf);
        $path = app(EmployeeHealthFormPdfService::class)->generate($profile);
        $this->assertSame('rebuilt frozen PDF', Storage::disk('health_private')->get($path));
        $this->assertSame($path, $approved->fresh()->pdf_path);
        $this->assertSame($snapshot, $approved->fresh()->profile_snapshot);
        $this->assertSame('later-edit.png', $profile->fresh()->student_photo);
    }

    public function test_student_submission_saves_a_frozen_health_profile_snapshot(): void
    {
        Carbon::setTestNow('2026-08-23 10:15:00');

        $user = User::forceCreate([
            'name' => 'History Student',
            'email' => 'history@example.com',
            'student_number' => '2026-0100',
        ]);
        $profile = HealthProfile::forceCreate([
            'user_id' => $user->id,
            'student_number' => '2026-0100',
            'medical_condition_remarks' => 'Asthma',
            'documents_valid' => true,
            'medical_certificate' => 'health_profiles/medical_certificates/history.pdf',
        ]);

        $dompdf = Mockery::mock(\Barryvdh\DomPDF\PDF::class);
        $dompdf->shouldReceive('setPaper')->once()->with([0, 0, 612, 936])->andReturnSelf();
        $dompdf->shouldReceive('output')->once()->andReturn('snapshot-pdf');
        Pdf::shouldReceive('loadView')->once()->andReturn($dompdf);

        $submission = app(HealthFormPdfSnapshotService::class)->recordSubmittedWithoutPdf(
            $profile,
            $user,
            'Enrollment'
        );

        $this->assertSame('Asthma', data_get($submission->profile_snapshot, 'profile.medical_condition_remarks'));
        $this->assertTrue((bool) data_get($submission->profile_snapshot, 'profile.documents_valid'));
        $this->assertSame(
            'health_profiles/medical_certificates/history.pdf',
            data_get($submission->profile_snapshot, 'profile.medical_certificate')
        );
        $this->assertSame('History Student', data_get($submission->profile_snapshot, 'user.name'));
        $this->assertNotNull($submission->snapshot_captured_at);
    }

    public function test_new_form_request_preserves_the_previous_current_profile_before_overwrite(): void
    {
        Carbon::setTestNow('2026-08-23 11:00:00');

        $user = User::forceCreate([
            'name' => 'Versioned Student',
            'student_number' => '2026-0200',
        ]);
        $profile = HealthProfile::forceCreate([
            'user_id' => $user->id,
            'student_number' => '2026-0200',
            'medical_condition_remarks' => 'Original assessment',
            'documents_valid' => true,
        ]);
        $current = HealthFormSubmission::create([
            'user_id' => $user->id,
            'health_profile_id' => $profile->id,
            'category' => 'Initial Submission',
            'status' => HealthFormSubmission::STATUS_APPROVED,
            'submitted_at' => now()->subMonth(),
            'approved_at' => now()->subMonth(),
        ]);
        $request = HealthFormSubmission::create([
            'user_id' => $user->id,
            'health_profile_id' => $profile->id,
            'category' => 'Internship',
            'status' => HealthFormSubmission::STATUS_REQUESTED,
            'requested_at' => now(),
        ]);

        app(\App\Services\HealthProfileSnapshotService::class)
            ->preserveCurrentBeforeNewSubmission($profile, $request);

        $this->assertSame(
            'Original assessment',
            data_get($current->fresh()->profile_snapshot, 'profile.medical_condition_remarks')
        );
        $this->assertNull($request->fresh()->profile_snapshot);
    }

    public function test_approved_snapshot_fallback_does_not_take_over_a_newer_submitted_form(): void
    {
        $user = User::forceCreate([
            'name' => 'Versioned Student',
            'email' => 'versioned@example.com',
            'student_number' => '2026-0300',
        ]);
        $profile = HealthProfile::forceCreate([
            'user_id' => $user->id,
            'student_number' => '2026-0300',
            'clearance_status' => 'Fully Cleared',
        ]);
        $approvedPath = 'health_form_submissions/' . $user->id . '/approved.pdf';
        $pendingPath = 'health_form_submissions/' . $user->id . '/pending.pdf';

        $approved = HealthFormSubmission::create([
            'user_id' => $user->id,
            'health_profile_id' => $profile->id,
            'status' => HealthFormSubmission::STATUS_APPROVED,
            'pdf_path' => $approvedPath,
            'profile_snapshot' => [
                'profile' => ['home_address' => 'Original approved address'],
                'user' => ['name' => 'Versioned Student'],
            ],
            'submitted_at' => now()->subMonth(),
            'approved_at' => now()->subMonth(),
        ]);
        $pending = HealthFormSubmission::create([
            'user_id' => $user->id,
            'health_profile_id' => $profile->id,
            'status' => HealthFormSubmission::STATUS_SUBMITTED,
            'pdf_path' => $pendingPath,
            'submitted_at' => now(),
        ]);

        $dompdf = Mockery::mock(\Barryvdh\DomPDF\PDF::class);
        $dompdf->shouldReceive('setPaper')->once()->with([0, 0, 612, 936])->andReturnSelf();
        $dompdf->shouldReceive('output')->once()->andReturn('rebuilt-approved-pdf');
        Pdf::shouldReceive('loadView')
            ->once()
            ->with('student.print_health_form', Mockery::on(function (array $data) {
                return $data['profile']->home_address === 'Original approved address';
            }))
            ->andReturn($dompdf);

        $result = app(HealthFormPdfSnapshotService::class)->ensureApprovedSnapshot($profile);

        $this->assertSame($approved->id, $result->id);
        $this->assertSame(HealthFormSubmission::STATUS_APPROVED, $approved->fresh()->status);
        $this->assertSame('Original approved address', data_get($approved->fresh()->profile_snapshot, 'profile.home_address'));
        $this->assertSame(HealthFormSubmission::STATUS_SUBMITTED, $pending->fresh()->status);
        $this->assertSame($pendingPath, $pending->fresh()->pdf_path);
        $this->assertSame('rebuilt-approved-pdf', Storage::disk('health_private')->get($result->pdf_path));
    }

    public function test_new_health_form_stays_staged_until_approval_then_promotes_over_the_old_version(): void
    {
        Carbon::setTestNow('2026-08-23 11:30:00');

        $user = User::forceCreate([
            'name' => 'Versioned Student',
            'student_number' => '2026-0201',
            'DOB' => '2005-01-10',
            'contact_no' => '09111111111',
            'is_health_profile_completed' => true,
        ]);
        $profile = HealthProfile::forceCreate([
            'user_id' => $user->id,
            'student_number' => '2026-0201',
            'school_year' => '2025-2026',
            'health_form_category' => 'Initial Submission',
            'home_address' => 'Old address',
            'medical_condition_remarks' => 'Original assessment',
            'clearance_status' => 'Fully Cleared',
            'documents_valid' => true,
        ]);
        $previousVersion = HealthFormSubmission::create([
            'user_id' => $user->id,
            'health_profile_id' => $profile->id,
            'category' => 'Initial Submission',
            'status' => HealthFormSubmission::STATUS_APPROVED,
            'submitted_at' => now()->subMonth(),
            'approved_at' => now()->subMonth(),
        ]);
        $request = HealthFormSubmission::create([
            'user_id' => $user->id,
            'health_profile_id' => $profile->id,
            'category' => 'Academic Year 2026-2027',
            'status' => HealthFormSubmission::STATUS_REQUESTED,
            'requested_at' => now(),
        ]);

        app(HealthProfileSnapshotService::class)
            ->preserveCurrentBeforeNewSubmission($profile->fresh('user'), $request);

        $stagedUser = clone $user;
        $stagedUser->DOB = '2005-02-12';
        $stagedUser->contact_no = '09222222222';
        $stagedProfile = clone $profile;
        $stagedProfile->forceFill([
            'school_year' => '2026-2027',
            'health_form_category' => 'Academic Year 2026-2027',
            'home_address' => 'New address',
            'medical_condition_remarks' => 'Updated assessment',
            'clearance_status' => 'For Verification',
        ]);
        $stagedProfile->setRelation('user', $stagedUser);

        $dompdf = Mockery::mock(\Barryvdh\DomPDF\PDF::class);
        $dompdf->shouldReceive('setPaper')->once()->with([0, 0, 612, 936])->andReturnSelf();
        $dompdf->shouldReceive('output')->once()->andReturn('staged-pdf');
        Pdf::shouldReceive('loadView')
            ->once()
            ->with('student.print_health_form', Mockery::on(function (array $data) {
                return $data['profile']->home_address === 'New address'
                    && $data['profile']->user->contact_no === '09222222222';
            }))
            ->andReturn($dompdf);

        $submission = app(HealthFormPdfSnapshotService::class)->recordSubmittedWithoutPdf(
            $profile,
            $stagedUser,
            'Academic Year 2026-2027',
            null,
            false,
            $stagedProfile
        );

        $this->assertSame(HealthFormSubmission::STATUS_SUBMITTED, $submission->status);
        $this->assertSame('New address', data_get($submission->fresh()->profile_snapshot, 'profile.home_address'));
        $this->assertSame('09222222222', data_get($submission->fresh()->profile_snapshot, 'user.contact_no'));
        $this->assertSame('Old address', $profile->fresh()->home_address);
        $this->assertSame('Fully Cleared', $profile->fresh()->clearance_status);
        $this->assertSame('09111111111', $user->fresh()->contact_no);
        $this->assertSame(HealthFormSubmission::STATUS_APPROVED, $previousVersion->fresh()->status);
        $this->assertNotEmpty($previousVersion->fresh()->profile_snapshot);

        $admin = User::forceCreate(['name' => 'Clinic Admin']);
        $submission->forceFill([
            'status' => HealthFormSubmission::STATUS_APPROVED,
            'approved_at' => now(),
        ])->save();

        app(HealthProfileSnapshotService::class)->promoteApprovedSubmission(
            $profile->fresh(),
            $submission->fresh(),
            $user->fresh(),
            $admin->id
        );

        $this->assertSame('New address', $profile->fresh()->home_address);
        $this->assertSame('Original assessment', $profile->fresh()->medical_condition_remarks);
        $this->assertSame('Fully Cleared', $profile->fresh()->clearance_status);
        $this->assertSame('09222222222', $user->fresh()->contact_no);
        $this->assertSame('2005-02-12', $user->fresh()->DOB);
        $this->assertTrue((bool) $user->fresh()->is_health_profile_completed);
        $this->assertSame($admin->id, $profile->fresh()->approved_by_user_id);
        $this->assertSame(HealthFormSubmission::STATUS_APPROVED, $previousVersion->fresh()->status);
    }

    public function test_employee_snapshot_refresh_persists_new_path_and_removes_old_pdf(): void
    {
        Carbon::setTestNow('2026-07-28 15:45:00');

        $user = User::forceCreate([
            'name' => 'Test Employee',
            'employee_number' => 'EMP-100',
        ]);
        $oldPath = 'health_profile_employees/health_forms/old.pdf';
        $profile = EmployeeHealthProfile::forceCreate([
            'user_id' => $user->id,
            'employee_number' => 'EMP-100',
            'uploaded_signature_path' => 'health_profile_employees/signatures/test.png',
            'staff_health_form_pdf_path' => $oldPath,
        ]);
        Storage::disk('health_private')->put($oldPath, 'old-pdf');

        $dompdf = Mockery::mock(\Barryvdh\DomPDF\PDF::class);
        $dompdf->shouldReceive('setPaper')->once()->with([0, 0, 612, 936])->andReturnSelf();
        $dompdf->shouldReceive('output')->once()->andReturn('new-employee-pdf');
        Pdf::shouldReceive('loadView')
            ->once()
            ->with('student.print_employee_health_form', Mockery::on(function (array $data) use ($profile) {
                return (int) $data['employeeProfile']->id === (int) $profile->id
                    && $data['pdfMode'] === true;
            }))
            ->andReturn($dompdf);

        $newPath = app(EmployeeHealthFormPdfService::class)->generate($profile);
        $profile->refresh();

        $this->assertSame($newPath, $profile->staff_health_form_pdf_path);
        $this->assertNotSame($oldPath, $newPath);
        Storage::disk('health_private')->assertMissing($oldPath);
        $this->assertSame('new-employee-pdf', Storage::disk('health_private')->get($newPath));
    }

    public function test_stored_signature_is_converted_to_an_embedded_image_data_uri(): void
    {
        $path = 'health_profiles/signatures/test.png';
        $contents = base64_decode(
            'iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAQAAAC1HAwCAAAAC0lEQVR42mNk+A8AAQUBAScY42YAAAAASUVORK5CYII=',
            true
        );
        Storage::disk('public')->put($path, $contents);

        $source = app(StoredImageDataUri::class)->fromPublicDisk('storage/' . $path);

        $this->assertSame('data:image/png;base64,' . base64_encode($contents), $source);
    }

    public function test_student_form_prints_the_name_and_submission_date_without_a_signature(): void
    {
        $user = User::forceCreate([
            'name' => 'Marcella Mae Igcasenza Pada',
            'student_number' => '2026-0001',
        ]);
        $profile = HealthProfile::forceCreate([
            'user_id' => $user->id,
            'student_number' => '2026-0001',
        ])->load('user');

        $html = view('student.print_health_form', [
            'profile' => $profile,
            'pdfMode' => true,
            'healthFormIdentity' => [],
            'healthFormSubmittedAt' => Carbon::parse('2026-07-20 09:00:00'),
        ])->render();

        $this->assertStringContainsString('MARCELLA MAE IGCASENZA PADA', $html);
        $this->assertStringContainsString('07/20/2026', $html);
        $this->assertStringContainsString('white-space: nowrap', $html);
        $this->assertStringContainsString('width: 190px', $html);
        $this->assertStringContainsString('font-size: 11px !important', $html);
    }

    public function test_home_address_value_starts_directly_after_its_label(): void
    {
        $user = (new User())->forceFill(['name' => 'Address Layout Student']);
        $profile = (new HealthProfile())->forceFill([
            'home_address' => '123 Sample Street, Taguig City',
        ]);
        $profile->setRelation('user', $user);

        $html = view('student.print_health_form', [
            'profile' => $profile,
            'pdfMode' => true,
            'healthFormIdentity' => [],
        ])->render();

        $this->assertMatchesRegularExpression(
            '/<span class="line-label home-address-label">Home Address:<\/span><span class="line-value home-address-value">123 Sample Street, Taguig City<\/span>/',
            $html
        );
        $this->assertStringContainsString('.home-address-label { display: inline-block; width: 72pt;', $html);
        $this->assertStringContainsString('.home-address-value { display: inline-block; width: 240pt;', $html);
    }

    public function test_long_student_signature_uses_middle_initial_and_wraps_without_clipping(): void
    {
        $user = User::forceCreate([
            'name' => 'Alexandria Cassandra Maximiliana De Los Santos Evangelista',
            'first_name' => 'Alexandria',
            'middle_name' => 'Cassandra',
            'last_name' => 'Maximiliana De Los Santos Evangelista',
            'suffix_name' => 'Jr.',
            'student_number' => '2026-0002',
        ]);
        $profile = HealthProfile::forceCreate([
            'user_id' => $user->id,
            'student_number' => '2026-0002',
        ])->load('user');

        $html = view('student.print_health_form', [
            'profile' => $profile,
            'pdfMode' => true,
            'healthFormIdentity' => [],
            'healthFormSubmittedAt' => Carbon::parse('2026-07-20 09:00:00'),
        ])->render();

        $this->assertStringContainsString(
            'student-signature-name" style="font-size: 7px !important;">ALEXANDRIA C. MAXIMILIANA DE LOS SANTOS EVANGELISTA, JR.',
            $html
        );
        $this->assertStringContainsString('Alexandria Cassandra Maximiliana De Los Santos Evangelista', $html);
        $this->assertStringContainsString('white-space: normal', $html);
        $this->assertStringContainsString('overflow-wrap: break-word', $html);
    }
}
