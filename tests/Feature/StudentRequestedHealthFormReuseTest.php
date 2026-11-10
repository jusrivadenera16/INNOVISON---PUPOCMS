<?php

namespace Tests\Feature;

use App\Http\Controllers\AppointmentController;
use App\Http\Controllers\AdminController;
use App\Models\HealthFormSubmission;
use App\Models\HealthProfile;
use App\Models\HealthProfileCorrectionRequest;
use App\Models\User;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Tests\TestCase;

class StudentRequestedHealthFormReuseTest extends TestCase
{
    use DatabaseTransactions;

    private function pendingCycle(): array
    {
        $student = User::factory()->create(['user_role' => User::ROLE_STUDENT, 'user_type' => 'student']);
        $profile = HealthProfile::create(['user_id' => $student->id, 'clearance_status' => 'Issued', 'home_address' => 'Saved address']);
        $pending = HealthFormSubmission::create([
            'user_id' => $student->id, 'health_profile_id' => $profile->id,
            'status' => 'requested', 'category' => 'OJT', 'requested_at' => now(),
        ]);
        $correction = HealthProfileCorrectionRequest::create([
            'user_id' => $student->id, 'health_profile_id' => $profile->id,
            'health_form_submission_id' => $pending->id, 'type' => 'new_health_form',
            'status' => 'pending', 'requested_at' => now(),
        ]);
        return [$student, $profile, $pending, $correction];
    }

    public function test_existing_information_submits_the_requested_cycle_and_preserves_the_issued_profile(): void
    {
        Storage::fake('health_private');
        $pdf = \Mockery::mock(\Barryvdh\DomPDF\PDF::class);
        $pdf->shouldReceive('setPaper')->once();
        $pdf->shouldReceive('output')->once()->andReturn('%PDF-1.4 test');
        Pdf::shouldReceive('loadView')->once()->andReturn($pdf);
        [$student, $profile, $pending, $correction] = $this->pendingCycle();
        $approved = HealthFormSubmission::create([
            'user_id' => $student->id, 'health_profile_id' => $profile->id,
            'status' => 'approved', 'category' => 'Previous',
            'approved_at' => now()->subYear(), 'submitted_at' => now()->subYear(),
        ]);
        $this->actingAs($student, 'student');
        $response = app(AppointmentController::class)->submitExistingHealthForm(
            Request::create('/student/health-form/use-existing', 'POST', ['submission_id' => $pending->id])
        );
        $this->assertStringContainsString('view=health-record', $response->getTargetUrl());
        $this->assertSame('submitted', $pending->fresh()->status);
        $this->assertNotNull($pending->fresh()->submitted_at);
        $this->assertNull($pending->fresh()->approved_at);
        $this->assertSame('Saved address', data_get($pending->fresh()->profile_snapshot, 'profile.home_address'));
        $this->assertSame('For Verification', data_get($pending->fresh()->profile_snapshot, 'profile.clearance_status'));
        $this->assertSame('Issued', $profile->fresh()->clearance_status);
        $this->assertSame('submitted', $correction->fresh()->status);
        $this->assertSame(2, HealthFormSubmission::where('user_id', $student->id)->count());
        $this->assertSame('approved', $approved->fresh()->status);
        $stats = app(AdminController::class)->healthRecordStats(
            Request::create('/admin/health-records/stats', 'GET', ['q' => $student->email])
        )->getData(true)['stats'];
        $this->assertSame(1, $stats['pending_approval']);
        $this->assertSame(1, $stats['total_approved']);

        $this->expectException(HttpException::class);
        $this->expectExceptionMessage('no longer awaiting submission');
        app(AppointmentController::class)->submitExistingHealthForm(
            Request::create('/student/health-form/use-existing', 'POST', ['submission_id' => $pending->id])
        );
    }

    public function test_another_users_request_cannot_be_submitted(): void
    {
        [$student, $profile, $pending] = $this->pendingCycle();
        $other = User::factory()->create(['user_role' => User::ROLE_STUDENT, 'user_type' => 'student']);
        $this->actingAs($other, 'student');
        try {
            app(AppointmentController::class)->submitExistingHealthForm(
                Request::create('/student/health-form/use-existing', 'POST', ['submission_id' => $pending->id])
            );
            $this->fail('A foreign request must be rejected.');
        } catch (HttpException $exception) {
            $this->assertSame(409, $exception->getStatusCode());
            $this->assertSame('requested', $pending->fresh()->status);
        }
    }
}
