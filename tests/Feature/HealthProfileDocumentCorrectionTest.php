<?php

namespace Tests\Feature;

use App\Http\Controllers\AdminController;
use App\Http\Controllers\AppointmentController;
use App\Models\EmployeeHealthProfile;
use App\Models\HealthProfile;
use App\Models\HealthProfileCorrectionRequest;
use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Http\UploadedFile;
use Tests\TestCase;

class HealthProfileDocumentCorrectionTest extends TestCase
{
    use DatabaseTransactions;

    public function test_it_keeps_an_approved_record_approved_when_document_correction_is_requested(): void
    {
        $user = User::factory()->create([
            'first_name' => 'Student',
            'last_name' => 'User',
            'user_role' => User::ROLE_STUDENT,
        ]);

        $profile = HealthProfile::query()->create([
            'user_id' => $user->id,
            'clearance_status' => 'Issued',
            'verified_at' => now()->subDays(7),
            'documents_valid' => true,
            'pending_reason' => null,
            'resubmission_required_documents' => null,
            'resubmission_requested_at' => null,
            'puptas_synced_at' => now()->subDays(6),
            'puptas_sync_status' => 'synced',
        ]);

        $request = Request::create('/health-profile/' . $profile->id . '/request-resubmission', 'POST', [
            'pending_reason' => 'Please upload the updated medical certificate.',
            'resubmission_required_documents' => ['medical_certificate'],
            'needs_health_form_correction' => false,
        ]);

        $controller = app(AdminController::class);

        $response = $controller->requestHealthProfileResubmission($request, $profile->id);

        $profile->refresh();

        $this->assertEquals('Issued', $profile->clearance_status);
        $this->assertNotNull($profile->verified_at);
        $this->assertNotNull($profile->puptas_synced_at);
        $this->assertSame(['medical_certificate'], $profile->resubmission_required_documents);
        $this->assertTrue((bool) $profile->documents_valid);
        $this->assertNotNull($profile->resubmission_requested_at);
        $this->assertEquals(302, $response->getStatusCode());
    }

    public function test_it_marks_a_pending_health_profile_withdrawn_and_cancels_open_correction_requests(): void
    {
        $user = User::factory()->create([
            'first_name' => 'Student',
            'last_name' => 'Withdrawing',
            'user_role' => User::ROLE_STUDENT,
        ]);

        $profile = HealthProfile::query()->create([
            'user_id' => $user->id,
            'clearance_status' => 'Pending Resubmission',
            'verified_at' => now()->subMonth(),
            'pending_reason' => 'Please replace the uploaded document.',
            'resubmission_required_documents' => ['medical_certificate'],
            'resubmission_requested_at' => now()->subDay(),
            'pending_compliance_reminder_count' => 2,
            'medical_condition_remarks' => 'Existing health information remains saved.',
        ]);

        $requests = collect([
            HealthProfileCorrectionRequest::STATUS_PENDING,
            HealthProfileCorrectionRequest::STATUS_UNDER_REVIEW,
            HealthProfileCorrectionRequest::STATUS_SUBMITTED,
        ])->map(fn (string $status) => HealthProfileCorrectionRequest::query()->create([
            'user_id' => $user->id,
            'health_profile_id' => $profile->id,
            'type' => HealthProfileCorrectionRequest::TYPE_FILE_CORRECTION,
            'status' => $status,
        ]));

        $response = app(AdminController::class)->withdrawHealthProfile($profile->id);

        $profile->refresh();

        $this->assertSame(302, $response->getStatusCode());
        $this->assertSame('Withdrawn', $profile->clearance_status);
        $this->assertNull($profile->resubmission_requested_at);
        $this->assertNull($profile->resubmission_required_documents);
        $this->assertSame('Existing health information remains saved.', $profile->medical_condition_remarks);
        $this->assertSame('Please replace the uploaded document.', $profile->pending_reason);
        $this->assertSame(
            [HealthProfileCorrectionRequest::STATUS_CANCELLED],
            $requests->map(fn (HealthProfileCorrectionRequest $request) => $request->fresh()->status)->unique()->values()->all()
        );
    }

    public function test_it_does_not_allow_an_approved_health_profile_to_be_withdrawn(): void
    {
        $user = User::factory()->create([
            'user_role' => User::ROLE_STUDENT,
        ]);
        $profile = HealthProfile::query()->create([
            'user_id' => $user->id,
            'clearance_status' => ' issued ',
            'verified_at' => now()->subDay(),
        ]);

        $response = app(AdminController::class)->withdrawHealthProfile($profile->id);

        $this->assertSame(302, $response->getStatusCode());
        $this->assertSame(' issued ', $profile->fresh()->clearance_status);
    }

    public function test_student_replacement_upload_returns_the_record_to_verification(): void
    {
        $user = User::factory()->create([
            'user_role' => User::ROLE_STUDENT,
            'user_type' => 'student',
        ]);
        $profile = HealthProfile::query()->create([
            'user_id' => $user->id,
            'clearance_status' => 'Pending Resubmission',
            'pending_reason' => 'Please upload the updated medical certificate.',
            'resubmission_required_documents' => ['medical_certificate'],
            'resubmission_requested_at' => now()->subMinute(),
        ]);
        $correction = HealthProfileCorrectionRequest::query()->create([
            'user_id' => $user->id,
            'health_profile_id' => $profile->id,
            'profile_kind' => 'student',
            'type' => HealthProfileCorrectionRequest::TYPE_FILE_CORRECTION,
            'status' => HealthProfileCorrectionRequest::STATUS_PENDING,
            'required_documents' => ['medical_certificate'],
            'requested_at' => now()->subMinute(),
        ]);

        Storage::fake('health_private');
        Storage::fake('public');
        config([
            'health_files.write_disk' => 'health_private',
            'health_files.legacy_disk' => 'public',
            'health_files.legacy_fallback' => true,
            'health_files.mirror_to_legacy' => false,
        ]);
        $this->actingAs($user);
        $request = Request::create('/student/health-record/resubmit', 'POST', [], [], [
            'medical_certificate' => UploadedFile::fake()->createWithContent('medical-certificate.pdf', "%PDF-1.4\nreplacement"),
        ]);

        $response = app(AppointmentController::class)->resubmitHealthRecordRequirements($request);

        $profile->refresh();
        $correction->refresh();
        $this->assertSame(302, $response->getStatusCode());
        $this->assertSame('For Verification', $profile->clearance_status);
        $this->assertNull($profile->pending_reason);
        $this->assertNull($profile->resubmission_required_documents);
        $this->assertNotNull($profile->resubmitted_at);
        $this->assertSame(HealthProfileCorrectionRequest::STATUS_SUBMITTED, $correction->status);
        Storage::disk('health_private')->assertExists($profile->medical_certificate);
    }

    public function test_employee_replacement_upload_uses_employee_profile_and_returns_to_verification(): void
    {
        $user = User::factory()->create([
            'user_role' => User::ROLE_ADMIN,
            'user_type' => 'faculty',
        ]);
        $profile = EmployeeHealthProfile::query()->create([
            'user_id' => $user->id,
            'employee_number' => '12345',
            'clearance_status' => 'Approved',
            'verified_at' => now()->subDay(),
            'documents_valid' => true,
            'pending_reason' => 'Please upload the updated medical certificate.',
            'resubmission_required_fields' => ['medical_certificate'],
            'resubmission_requested_at' => now()->subMinute(),
        ]);
        $correction = HealthProfileCorrectionRequest::query()->create([
            'user_id' => $user->id,
            'employee_health_profile_id' => $profile->id,
            'profile_kind' => 'employee',
            'type' => HealthProfileCorrectionRequest::TYPE_FILE_CORRECTION,
            'status' => HealthProfileCorrectionRequest::STATUS_PENDING,
            'required_documents' => ['medical_certificate'],
            'requested_at' => now()->subMinute(),
        ]);

        Storage::fake('health_private');
        Storage::fake('public');
        config([
            'health_files.write_disk' => 'health_private',
            'health_files.legacy_disk' => 'public',
            'health_files.legacy_fallback' => true,
            'health_files.mirror_to_legacy' => false,
        ]);
        $this->actingAs($user);
        $request = Request::create('/student/health-record/resubmit', 'POST', [], [], [
            'medical_certificate' => UploadedFile::fake()->createWithContent('medical-certificate.pdf', "%PDF-1.4\nreplacement"),
        ]);

        $response = app(AppointmentController::class)->resubmitHealthRecordRequirements($request);

        $profile->refresh();
        $correction->refresh();
        $this->assertSame(302, $response->getStatusCode());
        $this->assertSame('For Verification', $profile->clearance_status);
        $this->assertNull($profile->pending_reason);
        $this->assertNull($profile->resubmission_required_fields);
        $this->assertNotNull($profile->resubmitted_at);
        $this->assertNotNull($profile->verified_at);
        $this->assertSame(HealthProfileCorrectionRequest::STATUS_SUBMITTED, $correction->status);
        $this->assertStringStartsWith('health_profile_employees/medical_certificates/', $profile->medical_certificate);
        Storage::disk('health_private')->assertExists($profile->medical_certificate);
    }
}
