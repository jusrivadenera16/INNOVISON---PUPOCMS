<?php

namespace Tests\Feature;

use App\Http\Controllers\AdminController;
use App\Models\{HealthFormCategory, HealthFormSubmission, HealthProfile, HealthProfileCorrectionRequest, User};
use App\Services\{HealthProfileSnapshotService, StudentNotificationMailer};
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\ValidationException;
use Tests\TestCase;

class ClinicStartedStudentHealthFormTest extends TestCase
{
    use DatabaseTransactions;

    private function student(bool $withApproved = true): array
    {
        $user = User::factory()->create(['user_role' => User::ROLE_STUDENT, 'user_type' => 'student', 'student_number' => '2099-' . random_int(10000, 99999) . '-TG-1']);
        $profile = HealthProfile::create(['user_id' => $user->id, 'student_number' => $user->student_number, 'clearance_status' => 'Issued', 'verified_at' => now()->subDay(), 'home_address' => 'Saved address']);
        $approved = $withApproved ? HealthFormSubmission::create([
            'user_id' => $user->id, 'health_profile_id' => $profile->id, 'status' => 'approved',
            'approved_at' => $profile->verified_at, 'category' => 'Previous Form',
            'profile_snapshot' => app(HealthProfileSnapshotService::class)->capture($profile),
        ]) : null;
        $purpose = HealthFormCategory::create(['name' => 'Clinic OJT ' . $user->id, 'available_for' => ['student'], 'is_active' => true]);
        return [$profile, $approved, $purpose];
    }

    private function clinic(): void
    {
        $this->actingAs(User::factory()->create(['user_role' => User::ROLE_SUPERADMIN]), 'admin');
        Storage::fake('health_private');
        $pdf = \Mockery::mock(\Barryvdh\DomPDF\PDF::class);
        $pdf->shouldReceive('setPaper');
        $pdf->shouldReceive('output')->andReturn('%PDF-1.4 test');
        Pdf::shouldReceive('loadView')->andReturn($pdf);
    }

    public function test_individual_start_submits_saved_information_and_preserves_approved_form_without_request_email(): void
    {
        $this->clinic();
        [$profile, $approved, $purpose] = $this->student();
        $before = $approved->fresh()->getAttributes();
        $this->mock(StudentNotificationMailer::class)->shouldNotReceive('sendHealthRecordNotice');
        $request = Request::create('/health-profile/' . $profile->id . '/request-health-form', 'POST', ['category' => $purpose->name, 'clinic_action' => 'start']);
        $response = app(AdminController::class)->requestNewHealthForm($request, $profile->id);
        $this->assertSame(route('admin.health_records', ['tab' => 'pending_approval', 'user_type' => 'student']), $response->getTargetUrl());
        $this->assertSame($profile->id, $response->getSession()->get('open_student_health_form'));
        $pending = HealthFormSubmission::where('health_profile_id', $profile->id)->where('status', 'submitted')->sole();
        $this->assertSame($purpose->name, $pending->category);
        $this->assertSame('Saved address', data_get($pending->profile_snapshot, 'profile.home_address'));
        $this->assertNotNull($pending->pdf_path);
        $this->assertSame('Issued', $profile->fresh()->clearance_status);
        $this->assertSame($before, $approved->fresh()->getAttributes());
        $correction = HealthProfileCorrectionRequest::where('health_form_submission_id', $pending->id)->sole();
        $this->assertSame('submitted', $correction->status);
        $this->assertFalse(data_get($correction->metadata, 'student_login_required'));
        $stats = app(AdminController::class)->healthRecordStats(Request::create('/health-records/stats', 'GET', ['q' => $profile->user->email]))->getData(true)['stats'];
        $this->assertSame(1, $stats['pending_approval']);
        app(AdminController::class)->requestNewHealthForm($request, $profile->id);
        $this->assertSame(2, HealthFormSubmission::where('health_profile_id', $profile->id)->count());
    }

    public function test_send_request_keeps_old_student_submission_and_email_flow(): void
    {
        $this->clinic();
        [$profile, $approved, $purpose] = $this->student();
        $this->mock(StudentNotificationMailer::class)->shouldReceive('sendHealthRecordNotice')->once()->andReturn(['status' => 'skipped']);
        $response = app(AdminController::class)->requestNewHealthForm(Request::create('/health-profile/request', 'POST', ['category' => $purpose->name, 'clinic_action' => 'send']), $profile->id);
        $this->assertSame(route('admin.show_health', $profile->id), $response->getTargetUrl());
        $this->assertSame(1, HealthFormSubmission::where('health_profile_id', $profile->id)->where('status', 'requested')->count());
        $this->assertSame('Issued', $profile->fresh()->clearance_status);
    }

    public function test_legacy_approved_profile_is_preserved_before_creating_the_new_cycle(): void
    {
        $this->clinic();
        [$profile, , $purpose] = $this->student(false);
        $this->mock(StudentNotificationMailer::class)->shouldNotReceive('sendHealthRecordNotice');
        $request = Request::create('/health-profile/request', 'POST', ['category' => $purpose->name, 'clinic_action' => 'start']);
        app(AdminController::class)->requestNewHealthForm($request, $profile->id);
        $submissions = HealthFormSubmission::where('health_profile_id', $profile->id)->orderBy('id')->get();
        $this->assertCount(2, $submissions);
        $this->assertSame('approved', $submissions[0]->status);
        $this->assertSame('submitted', $submissions[1]->status);
        $this->assertSame($profile->verified_at->toDateTimeString(), $submissions[0]->approved_at->toDateTimeString());
        app(AdminController::class)->requestNewHealthForm($request, $profile->id);
        $this->assertSame(2, HealthFormSubmission::where('health_profile_id', $profile->id)->count());
    }

    public function test_bulk_send_request_still_waits_for_student_submission(): void
    {
        $this->clinic();
        [$first, , $purpose] = $this->student();
        [$second] = $this->student();
        $this->mock(StudentNotificationMailer::class)->shouldReceive('sendHealthRecordNotice')->twice()->andReturn(['status' => 'skipped']);
        $response = app(AdminController::class)->requestBulkEmployeeHealthForms(Request::create('/health-records/bulk-request-health-form', 'POST', [
            'user_type' => 'student', 'category' => $purpose->name, 'health_profile_ids' => [$first->id, $second->id],
        ]));
        $this->assertStringContainsString('tab=approved', $response->getTargetUrl());
        foreach ([$first, $second] as $profile) {
            $this->assertSame(1, HealthFormSubmission::where('health_profile_id', $profile->id)->where('status', 'requested')->count());
            $this->assertSame(0, HealthFormSubmission::where('health_profile_id', $profile->id)->where('status', 'submitted')->count());
        }
    }

    public function test_bulk_start_submits_each_student_and_skips_active_requests(): void
    {
        $this->clinic();
        [$first, , $purpose] = $this->student();
        [$second] = $this->student();
        [$busy] = $this->student();
        HealthFormSubmission::create(['user_id' => $busy->user_id, 'health_profile_id' => $busy->id, 'status' => 'requested', 'requested_at' => now()]);
        $this->mock(StudentNotificationMailer::class)->shouldNotReceive('sendHealthRecordNotice');
        $response = app(AdminController::class)->requestBulkEmployeeHealthForms(Request::create('/health-records/bulk-request-health-form', 'POST', [
            'user_type' => 'student', 'category' => $purpose->name, 'clinic_action' => 'start', 'health_profile_ids' => [$first->id, $second->id, $busy->id],
        ]));
        $this->assertStringContainsString('pending_approval', $response->getTargetUrl());
        $this->assertNull($response->getSession()->get('open_student_health_form'));
        foreach ([$first, $second] as $profile) {
            $this->assertSame(1, HealthFormSubmission::where('health_profile_id', $profile->id)->where('status', 'submitted')->count());
            $this->assertSame('Issued', $profile->fresh()->clearance_status);
        }
        $this->assertSame(0, HealthFormSubmission::where('health_profile_id', $busy->id)->where('status', 'submitted')->count());
    }

    public function test_invalid_clinic_action_is_rejected(): void
    {
        $this->clinic();
        [$profile, , $purpose] = $this->student();
        $this->expectException(ValidationException::class);
        app(AdminController::class)->requestNewHealthForm(Request::create('/health-profile/request', 'POST', ['category' => $purpose->name, 'clinic_action' => 'invalid']), $profile->id);
    }
}
