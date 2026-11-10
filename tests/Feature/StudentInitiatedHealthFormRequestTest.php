<?php

namespace Tests\Feature;

use App\Http\Controllers\AdminController;
use App\Http\Controllers\AppointmentController;
use App\Models\HealthFormCategory;
use App\Models\HealthFormSubmission;
use App\Models\HealthProfile;
use App\Models\SystemSetting;
use App\Models\User;
use App\Services\StudentHealthFormRequestService;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\ValidationException;
use Tests\TestCase;

class StudentInitiatedHealthFormRequestTest extends TestCase
{
    use DatabaseTransactions;

    private function eligibleStudent(): array
    {
        $student = User::factory()->create(['user_role' => User::ROLE_STUDENT, 'user_type' => 'student', 'course' => 'BSIT', 'year' => '4th Year']);
        $profile = HealthProfile::create(['user_id' => $student->id, 'course_code' => 'BSIT', 'clearance_status' => 'Issued', 'home_address' => 'Saved address']);
        $purpose = HealthFormCategory::create(['name' => 'OJT Test ' . $student->id, 'available_for' => ['student'], 'is_active' => true]);
        $this->savePolicy(['purpose_ids' => [$purpose->id]]);
        return [$student, $profile, $purpose];
    }

    private function savePolicy(array $overrides = []): void
    {
        SystemSetting::putValue(StudentHealthFormRequestService::SETTING_KEY, json_encode(array_replace([
            'enabled' => true, 'purpose_ids' => [], 'eligible_years' => ['BSIT' => [4]], 'starts_on' => null, 'ends_on' => null,
        ], $overrides)));
    }

    public function test_eligibility_checks_toggle_course_year_dates_approval_and_active_cycle(): void
    {
        [$student, $profile, $purpose] = $this->eligibleStudent();
        $service = app(StudentHealthFormRequestService::class);
        $this->assertCount(1, $service->eligiblePurposes($student));
        foreach ([['enabled' => false], ['eligible_years' => ['BSIT' => [3]]], ['eligible_years' => ['BSME' => [4]]], ['starts_on' => now()->addDay()->toDateString()], ['ends_on' => now()->subDay()->toDateString()]] as $rule) {
            $this->savePolicy(array_merge(['purpose_ids' => [$purpose->id]], $rule));
            $this->assertCount(0, $service->eligiblePurposes($student));
        }
        $this->savePolicy(['purpose_ids' => [$purpose->id]]);
        $profile->update(['clearance_status' => 'For Verification']);
        $this->assertCount(0, $service->eligiblePurposes($student));
        $profile->update(['clearance_status' => 'Issued']);
        HealthFormSubmission::create(['user_id' => $student->id, 'health_profile_id' => $profile->id, 'status' => 'submitted']);
        $this->assertCount(0, $service->eligiblePurposes($student));
    }

    public function test_recent_approval_blocks_student_requests_until_exactly_24_hours(): void
    {
        $this->travelTo(now()->startOfSecond());
        try {
            [$student, $profile, $purpose] = $this->eligibleStudent();
            $approval = HealthFormSubmission::create([
                'user_id' => $student->id, 'health_profile_id' => $profile->id,
                'status' => HealthFormSubmission::STATUS_APPROVED, 'approved_at' => now(),
            ]);
            $service = app(StudentHealthFormRequestService::class);
            $this->assertCount(0, $service->eligiblePurposes($student));
            $this->actingAs($student, 'student');
            try {
                app(AppointmentController::class)->requestStudentHealthForm(Request::create('/student/health-form/request', 'POST', [
                    'purpose_id' => $purpose->id, 'information_handling' => 'update',
                ]));
                $this->fail('A direct POST must not bypass the approval cooldown.');
            } catch (ValidationException $exception) {
                $this->assertArrayHasKey('purpose_id', $exception->errors());
                $this->assertSame(1, HealthFormSubmission::where('user_id', $student->id)->count());
            }
            $this->travel(24)->hours();
            $this->assertCount(1, $service->eligiblePurposes($student));
            $approval->update(['approved_at' => now()->subHours(23)->subMinutes(59)]);
            $this->assertCount(0, $service->eligiblePurposes($student));
        } finally {
            $this->travelBack();
        }
    }

    public function test_legacy_profile_approval_and_active_cycles_hide_student_request_choices(): void
    {
        [$student, $profile] = $this->eligibleStudent();
        $service = app(StudentHealthFormRequestService::class);
        $profile->update(['verified_at' => now()->subHour()]);
        $this->assertCount(0, $service->eligiblePurposes($student));
        $profile->update(['verified_at' => now()->subHours(25)]);
        $this->assertCount(1, $service->eligiblePurposes($student));
        foreach (['requested', 'submitted', 'under_review', 'needs_correction'] as $status) {
            $submission = HealthFormSubmission::create([
                'user_id' => $student->id, 'health_profile_id' => $profile->id, 'status' => $status,
            ]);
            $this->assertCount(0, $service->eligiblePurposes($student));
            $submission->delete();
        }
    }

    public function test_update_creates_one_requested_cycle_and_opens_the_prefilled_form(): void
    {
        [$student, $profile, $purpose] = $this->eligibleStudent();
        $this->actingAs($student, 'student');
        $request = Request::create('/student/health-form/request', 'POST', ['purpose_id' => $purpose->id, 'information_handling' => 'update']);
        $response = app(AppointmentController::class)->requestStudentHealthForm($request);
        $this->assertSame(route('health.form.student'), $response->getTargetUrl());
        $submission = HealthFormSubmission::where('user_id', $student->id)->sole();
        $this->assertSame('requested', $submission->status);
        $this->assertSame($purpose->name, $submission->category);
        $this->assertSame('Issued', $profile->fresh()->clearance_status);
        try {
            app(AppointmentController::class)->requestStudentHealthForm($request);
            $this->fail('Duplicate cycles must be rejected.');
        } catch (ValidationException $exception) {
            $this->assertArrayHasKey('purpose_id', $exception->errors());
            $this->assertSame(1, HealthFormSubmission::where('user_id', $student->id)->count());
        }
    }

    public function test_existing_information_goes_directly_to_pending_approval(): void
    {
        Storage::fake('health_private');
        $pdf = \Mockery::mock(\Barryvdh\DomPDF\PDF::class);
        $pdf->shouldReceive('setPaper')->once();
        $pdf->shouldReceive('output')->once()->andReturn('%PDF-1.4 test');
        Pdf::shouldReceive('loadView')->once()->andReturn($pdf);
        [$student, $profile, $purpose] = $this->eligibleStudent();
        $this->actingAs($student, 'student');
        $response = app(AppointmentController::class)->requestStudentHealthForm(Request::create('/student/health-form/request', 'POST', ['purpose_id' => $purpose->id, 'information_handling' => 'existing']));
        $submission = HealthFormSubmission::where('user_id', $student->id)->sole();
        $this->assertSame('submitted', $submission->status);
        $this->assertNotNull($submission->submitted_at);
        $this->assertSame('Saved address', data_get($submission->profile_snapshot, 'profile.home_address'));
        $this->assertStringContainsString('view=health-record', $response->getTargetUrl());
        $stats = app(AdminController::class)->healthRecordStats(Request::create('/admin/health-records/stats', 'GET', ['q' => $student->email]))->getData(true)['stats'];
        $this->assertSame(1, $stats['pending_approval']);
    }

    public function test_server_rejects_an_ineligible_first_year_even_if_request_is_posted_directly(): void
    {
        [$student, $profile, $purpose] = $this->eligibleStudent();
        $student->update(['year' => '1st Year']);
        $this->actingAs($student, 'student');
        try {
            app(AppointmentController::class)->requestStudentHealthForm(Request::create('/student/health-form/request', 'POST', ['purpose_id' => $purpose->id, 'information_handling' => 'existing']));
            $this->fail('Ineligible students must be rejected.');
        } catch (ValidationException $exception) {
            $this->assertArrayHasKey('purpose_id', $exception->errors());
            $this->assertSame(0, HealthFormSubmission::where('user_id', $student->id)->count());
        }
    }

    public function test_request_settings_save_without_changing_other_preferences(): void
    {
        [$student, $profile, $purpose] = $this->eligibleStudent();
        $admin = User::factory()->create(['user_role' => User::ROLE_SUPERADMIN]);
        $request = Request::create('/admin/settings', 'PUT', [
            'student_health_request_settings_form' => '1', 'student_request_enabled' => '1',
            'student_request_purposes' => [$purpose->id], 'student_request_years' => ['BSIT' => [3, 4]],
            'student_request_starts_on' => now()->toDateString(), 'student_request_ends_on' => now()->addMonth()->toDateString(),
        ]);
        $request->setUserResolver(fn () => $admin);
        app(AdminController::class)->updateSettings($request);
        $saved = app(StudentHealthFormRequestService::class)->settings();
        $this->assertTrue($saved['enabled']);
        $this->assertSame(['BSIT' => [3, 4]], $saved['eligible_years']);
        $this->assertSame([$purpose->id], $saved['purpose_ids']);
    }

    public function test_unconfigured_purposes_are_rejected_even_for_an_eligible_student(): void
    {
        [$student] = $this->eligibleStudent();
        $otherPurpose = HealthFormCategory::create(['name' => 'Other ' . $student->id, 'available_for' => ['student'], 'is_active' => true]);
        $this->actingAs($student, 'student');
        $this->expectException(ValidationException::class);
        app(AppointmentController::class)->requestStudentHealthForm(Request::create('/student/health-form/request', 'POST', [
            'purpose_id' => $otherPurpose->id, 'information_handling' => 'update',
        ]));
    }

    public function test_request_settings_cannot_be_enabled_without_eligibility_rules(): void
    {
        $admin = User::factory()->create(['user_role' => User::ROLE_SUPERADMIN]);
        $request = Request::create('/admin/settings', 'PUT', [
            'student_health_request_settings_form' => '1', 'student_request_enabled' => '1',
        ]);
        $request->setUserResolver(fn () => $admin);
        $this->expectException(ValidationException::class);
        app(AdminController::class)->updateSettings($request);
    }
}
