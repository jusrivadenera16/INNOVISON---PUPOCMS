<?php

namespace Tests\Feature;

use App\Http\Controllers\AdminController;
use App\Models\EmployeeHealthProfile;
use App\Models\HealthFormSubmission;
use App\Models\User;
use App\Services\HealthRecordHistoryService;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Http\Request;
use ReflectionMethod;
use Tests\TestCase;

class EmployeeHealthFormRenewalCycleTest extends TestCase
{
    use DatabaseTransactions;

    public function test_bulk_renewal_uses_saved_profile_and_queues_a_fresh_assessment_without_employee_login(): void
    {
        $admin = User::factory()->create(['user_role' => User::ROLE_SUPERADMIN]);
        $employee = User::factory()->create([
            'user_role' => User::ROLE_STUDENT,
            'user_type' => 'faculty',
            'employee_number' => '54321',
        ]);
        $profile = new EmployeeHealthProfile();
        $profile->forceFill([
            'user_id' => $employee->id,
            'employee_number' => '54321',
            'first_name' => 'Test',
            'last_name' => 'Faculty',
            'name' => 'Test Faculty',
            'health_form_category' => 'Previous Category',
            'clearance_status' => 'Issued',
            'submission_status' => 'approved',
            'verified_at' => now()->subYear(),
            'form_date' => now()->subYear()->toDateString(),
            'height' => '5.6',
            'weight' => '130',
            'fit_status' => 'Fit',
        ]);
        $profile->save();

        $method = new ReflectionMethod(AdminController::class, 'queueEmployeeHealthFormRenewal');
        $method->setAccessible(true);
        $correction = $method->invoke(
            app(AdminController::class),
            Request::create('/admin/health-records/bulk-request-health-form', 'POST'),
            $profile->fresh(['user']),
            $admin,
            'Annual Health Assessment',
            '',
            'existing',
            'faculty',
            true
        );

        $this->assertNotNull($correction);
        $this->assertSame('submitted', $correction->status);
        $this->assertSame('bulk_new_health_form', data_get($correction->metadata, 'request_kind'));
        $this->assertSame('existing', data_get($correction->metadata, 'information_handling'));
        $this->assertFalse(data_get($correction->metadata, 'employee_login_required'));

        $freshProfile = $profile->fresh();
        $this->assertSame('For Verification', $freshProfile->clearance_status);
        $this->assertSame('submitted', $freshProfile->submission_status);
        $this->assertSame('Annual Health Assessment', $freshProfile->health_form_category);
        $this->assertNull($freshProfile->height);
        $this->assertNull($freshProfile->weight);
        $this->assertNull($freshProfile->fit_status);
        $this->assertSame('Test Faculty', $freshProfile->name);

        $submissions = HealthFormSubmission::query()
            ->where('employee_health_profile_id', $profile->id)
            ->orderBy('id')
            ->get();
        $this->assertCount(2, $submissions);
        $this->assertSame(HealthFormSubmission::STATUS_APPROVED, $submissions[0]->status);
        $this->assertSame(HealthFormSubmission::STATUS_SUBMITTED, $submissions[1]->status);
        $this->assertSame($submissions[1]->id, $correction->health_form_submission_id);
        $this->assertSame('Test Faculty', data_get($submissions[1]->profile_snapshot, 'profile.name'));
        $this->assertNull(data_get($submissions[1]->profile_snapshot, 'profile.fit_status'));

        $history = app(HealthRecordHistoryService::class)->forEmployeeProfile($freshProfile);
        $cycleEvent = $history->firstWhere('title', 'New Health Form Cycle Created');
        $this->assertNotNull($cycleEvent);
        $this->assertSame($admin->name, $cycleEvent['actor']);
        $this->assertSame('Pending Approval', $cycleEvent['status']);
    }
}
