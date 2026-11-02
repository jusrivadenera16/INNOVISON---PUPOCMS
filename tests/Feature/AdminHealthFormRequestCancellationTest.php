<?php

namespace Tests\Feature;

use App\Http\Controllers\AdminController;
use App\Models\ActivityLog;
use App\Models\HealthFormSubmission;
use App\Models\HealthProfile;
use App\Models\HealthProfileCorrectionRequest;
use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;
use Tests\TestCase;

class AdminHealthFormRequestCancellationTest extends TestCase
{
    use DatabaseTransactions;

    public function test_only_clinic_route_exposes_new_health_form_cancellation(): void
    {
        $this->assertTrue(Route::has('admin.health_profile.health_form_request.cancel'));
        $this->assertFalse(Route::has('student.health_form.request.cancel'));
    }

    public function test_clinic_can_cancel_only_the_unsubmitted_request_without_changing_approved_record(): void
    {
        $admin = User::factory()->create(['user_role' => User::ROLE_SUPERADMIN]);
        $student = User::factory()->create([
            'user_role' => User::ROLE_STUDENT,
            'user_type' => 'student',
            'student_number' => '2099-99998-TG-1',
        ]);
        $profile = HealthProfile::query()->create([
            'user_id' => $student->id,
            'clearance_status' => 'Issued',
            'verified_at' => now()->subDay(),
        ]);
        $approved = HealthFormSubmission::query()->create([
            'user_id' => $student->id,
            'health_profile_id' => $profile->id,
            'status' => HealthFormSubmission::STATUS_APPROVED,
            'approved_at' => now()->subDay(),
        ]);
        $requested = HealthFormSubmission::query()->create([
            'user_id' => $student->id,
            'health_profile_id' => $profile->id,
            'status' => HealthFormSubmission::STATUS_REQUESTED,
            'requested_at' => now(),
        ]);
        $correction = HealthProfileCorrectionRequest::query()->create([
            'user_id' => $student->id,
            'health_profile_id' => $profile->id,
            'health_form_submission_id' => $requested->id,
            'type' => HealthProfileCorrectionRequest::TYPE_NEW_HEALTH_FORM,
            'status' => HealthProfileCorrectionRequest::STATUS_PENDING,
            'requested_at' => now(),
        ]);
        Auth::guard('admin')->setUser($admin);

        $response = app(AdminController::class)->cancelNewHealthFormRequest(
            Request::create('/health-profile/' . $profile->id . '/health-form-request/' . $requested->id . '/cancel', 'POST'),
            $profile->id,
            $requested->id
        );

        $this->assertSame(302, $response->getStatusCode());
        $this->assertSame(HealthFormSubmission::STATUS_CANCELLED, $requested->fresh()->status);
        $this->assertSame(HealthProfileCorrectionRequest::STATUS_CANCELLED, $correction->fresh()->status);
        $this->assertNotNull($correction->fresh()->reviewed_at);
        $this->assertSame(HealthFormSubmission::STATUS_APPROVED, $approved->fresh()->status);
        $this->assertSame('Issued', $profile->fresh()->clearance_status);
        $this->assertNotNull($profile->fresh()->verified_at);
        $this->assertTrue(ActivityLog::query()
            ->where('action', 'Health Form Request Cancelled')
            ->where('subject_id', (string) $requested->id)
            ->exists());
    }

    public function test_clinic_cannot_cancel_a_submitted_request(): void
    {
        $admin = User::factory()->create(['user_role' => User::ROLE_SUPERADMIN]);
        $student = User::factory()->create([
            'user_role' => User::ROLE_STUDENT,
            'user_type' => 'student',
            'student_number' => '2099-99997-TG-1',
        ]);
        $profile = HealthProfile::query()->create(['user_id' => $student->id, 'clearance_status' => 'Issued']);
        $submission = HealthFormSubmission::query()->create([
            'user_id' => $student->id,
            'health_profile_id' => $profile->id,
            'status' => HealthFormSubmission::STATUS_SUBMITTED,
            'submitted_at' => now(),
        ]);
        $correction = HealthProfileCorrectionRequest::query()->create([
            'user_id' => $student->id,
            'health_profile_id' => $profile->id,
            'health_form_submission_id' => $submission->id,
            'type' => HealthProfileCorrectionRequest::TYPE_NEW_HEALTH_FORM,
            'status' => HealthProfileCorrectionRequest::STATUS_SUBMITTED,
            'submitted_at' => now(),
        ]);
        Auth::guard('admin')->setUser($admin);

        $response = app(AdminController::class)->cancelNewHealthFormRequest(
            Request::create('/health-profile/' . $profile->id . '/health-form-request/' . $submission->id . '/cancel', 'POST'),
            $profile->id,
            $submission->id
        );

        $this->assertSame(302, $response->getStatusCode());
        $this->assertSame(HealthFormSubmission::STATUS_SUBMITTED, $submission->fresh()->status);
        $this->assertSame(HealthProfileCorrectionRequest::STATUS_SUBMITTED, $correction->fresh()->status);
    }
}
