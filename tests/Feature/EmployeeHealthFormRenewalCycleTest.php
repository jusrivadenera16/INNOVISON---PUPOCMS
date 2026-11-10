<?php

namespace Tests\Feature;

use App\Http\Controllers\AdminController;
use App\Models\EmployeeHealthProfile;
use App\Models\HealthFormCategory;
use App\Models\HealthFormSubmission;
use App\Models\HealthProfileCorrectionRequest;
use App\Models\ActivityLog;
use App\Models\User;
use App\Services\HealthRecordHistoryService;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Http\Request;
use ReflectionMethod;
use Tests\TestCase;

class EmployeeHealthFormRenewalCycleTest extends TestCase
{
    use DatabaseTransactions;

    private function approvedEmployee(string $type): EmployeeHealthProfile
    {
        $employee = User::factory()->create([
            'user_role' => User::ROLE_STUDENT, 'user_type' => $type,
        ]);
        $profile = new EmployeeHealthProfile();
        $profile->forceFill([
            'user_id' => $employee->id, 'name' => 'Saved Employee ' . $employee->id,
            'health_form_category' => 'Previous Category', 'clearance_status' => 'Issued',
            'submission_status' => 'approved', 'verified_at' => now()->subDay(),
            'form_date' => now()->subMonth()->toDateString(),
            'height' => '170', 'fit_status' => 'Fit',
        ])->save();
        return $profile;
    }

    private function startAssessment(EmployeeHealthProfile $profile, User $actor, bool $bulk = false): HealthProfileCorrectionRequest
    {
        $method = new ReflectionMethod(AdminController::class, 'queueEmployeeHealthFormRenewal');
        $method->setAccessible(true);
        return $method->invoke(app(AdminController::class), Request::create('/admin/test-assessment', 'POST'),
            $profile, $actor, 'Annual Assessment', '', 'existing', $profile->user->user_type, $bulk);
    }

    public function test_individual_and_bulk_assessments_can_be_cancelled_and_restore_only_the_previous_cycle(): void
    {
        $actor = User::factory()->create(['user_role' => User::ROLE_SUPERADMIN]);
        $this->actingAs($actor, 'admin');
        foreach (['faculty' => false, 'admin' => true] as $type => $bulk) {
            $profile = $this->approvedEmployee($type);
            $oldVerifiedAt = $profile->verified_at->toDateTimeString();
            $oldFormDate = $profile->form_date->toDateString();
            $assessment = $this->startAssessment($profile, $actor, $bulk);
            $approved = HealthFormSubmission::findOrFail(data_get($assessment->metadata, 'previous_approved_submission_id'));
            $originalSnapshot = $approved->profile_snapshot;
            $response = app(AdminController::class)->cancelEmployeeHealthAssessment(
                Request::create('/admin/test-assessment/cancel', 'POST'), $profile, $assessment->health_form_submission_id);
            $this->assertNotNull($response->getSession()->get('success'));
            $restored = $profile->fresh();
            $this->assertSame('Issued', $restored->clearance_status);
            $this->assertSame('approved', $restored->submission_status);
            $this->assertSame('Previous Category', $restored->health_form_category);
            $this->assertSame('Fit', $restored->fit_status);
            $this->assertEquals(170, $restored->height);
            $this->assertSame($oldVerifiedAt, $restored->verified_at->toDateTimeString());
            $this->assertSame($oldFormDate, $restored->form_date->toDateString());
            $this->assertSame($originalSnapshot, $approved->fresh()->profile_snapshot);
            $this->assertSame('approved', $approved->fresh()->status);
            $this->assertSame('cancelled', $assessment->fresh()->status);
            $this->assertSame('cancelled', $assessment->healthFormSubmission->fresh()->status);
            $this->assertSame($actor->id, $assessment->fresh()->reviewed_by_user_id);
            $this->assertSame(2, HealthFormSubmission::where('employee_health_profile_id', $profile->id)->count());
            $this->assertTrue(ActivityLog::where('action', 'Employee Health Assessment Cancelled')->where('subject_id', (string) $assessment->health_form_submission_id)->exists());
            // A repeated cancellation cannot change the restored approved record.
            $duplicate = app(AdminController::class)->cancelEmployeeHealthAssessment(Request::create('/admin/test-assessment/cancel', 'POST'), $profile, $assessment->health_form_submission_id);
            $this->assertNotNull($duplicate->getSession()->get('error'));
            $this->assertSame('Issued', $profile->fresh()->clearance_status);
        }
    }

    public function test_cancellation_rejects_changed_reviewed_approved_and_foreign_assessments(): void
    {
        $actor = User::factory()->create(['user_role' => User::ROLE_SUPERADMIN]);
        $this->actingAs($actor, 'admin');
        foreach (['changed', 'reviewed', 'approved', 'foreign', 'missing_snapshot', 'draft'] as $scenario) {
            $profile = $this->approvedEmployee('faculty');
            $assessment = $this->startAssessment($profile, $actor);
            $submission = $assessment->healthFormSubmission;
            if ($scenario === 'changed') $profile->fresh()->update(['height' => '180']);
            if ($scenario === 'reviewed') $assessment->update(['status' => 'under_review']);
            if ($scenario === 'approved') $submission->update(['status' => 'approved', 'approved_at' => now()]);
            if ($scenario === 'missing_snapshot') HealthFormSubmission::findOrFail(data_get($assessment->metadata, 'previous_approved_submission_id'))->update(['profile_snapshot' => null]);
            if ($scenario === 'draft') $profile->fresh()->update(['draft_data' => ['bp' => '120/80']]);
            $target = $scenario === 'foreign' ? $this->approvedEmployee('admin') : $profile;
            $before = $target->fresh()->getAttributes();
            $response = app(AdminController::class)->cancelEmployeeHealthAssessment(Request::create('/admin/test-assessment/cancel', 'POST'), $target, $submission->id);
            $this->assertNotNull($response->getSession()->get('error'), $scenario);
            $this->assertSame($before, $target->fresh()->getAttributes(), $scenario);
            $this->assertNotSame('cancelled', $assessment->fresh()->status, $scenario);
            $this->assertNotSame('cancelled', $submission->fresh()->status, $scenario);
        }
    }

    public function test_employee_assessment_cancellation_route_is_clinic_permission_protected(): void
    {
        $route = app('router')->getRoutes()->getByName('admin.employee_health_profile.assessment.cancel');
        $this->assertNotNull($route);
        $this->assertContains('role:superadmin,admin', $route->gatherMiddleware());
        $this->assertContains('module.permission:health_records.request_health_form', $route->gatherMiddleware());
        $this->assertContains('admin.guard', $route->gatherMiddleware());
    }

    public function test_individual_faculty_and_admin_assessments_start_without_employee_login_and_reject_duplicates(): void
    {
        $admin = User::factory()->create(['user_role' => User::ROLE_SUPERADMIN]);
        $this->actingAs($admin, 'admin');
        $category = HealthFormCategory::create([
            'name' => 'Assessment ' . $admin->id, 'available_for' => ['faculty', 'admin'], 'is_active' => true,
        ]);
        foreach (['faculty', 'admin'] as $type) {
            $profile = $this->approvedEmployee($type);
            $request = Request::create('/admin/employee-health-profile/' . $profile->id . '/request-health-form', 'POST', ['category' => $category->name]);
            $response = app(AdminController::class)->requestNewEmployeeHealthForm($request, $profile);
            $this->assertStringContainsString('new health assessment was started', $response->getSession()->get('success'));
            $this->assertSame(route('admin.health_records', ['tab' => 'pending_approval', 'user_type' => $type]), $response->getTargetUrl());
            $this->assertSame($profile->id, $response->getSession()->get('open_employee_assessment'));
            $this->assertSame('For Verification', $profile->fresh()->clearance_status);
            $this->assertSame('submitted', $profile->fresh()->submission_status);
            $submissions = HealthFormSubmission::where('employee_health_profile_id', $profile->id)->orderBy('id')->get();
            $this->assertCount(2, $submissions);
            $this->assertSame('approved', $submissions[0]->status);
            $this->assertSame('submitted', $submissions[1]->status);
            $this->assertSame('Saved Employee ' . $profile->user_id, data_get($submissions[1]->profile_snapshot, 'profile.name'));
            $this->assertSame('Fit', data_get($submissions[0]->profile_snapshot, 'profile.fit_status'));
            $this->assertNull(data_get($submissions[1]->profile_snapshot, 'profile.fit_status'));
            $duplicate = app(AdminController::class)->requestNewEmployeeHealthForm($request, $profile->fresh());
            $this->assertStringContainsString('already active', $duplicate->getSession()->get('info'));
            $this->assertSame(2, HealthFormSubmission::where('employee_health_profile_id', $profile->id)->count());
        }
    }

    public function test_bulk_endpoint_starts_employee_assessments_in_pending_approval(): void
    {
        $admin = User::factory()->create(['user_role' => User::ROLE_SUPERADMIN]);
        $this->actingAs($admin, 'admin');
        $category = HealthFormCategory::create([
            'name' => 'Bulk Assessment ' . $admin->id, 'available_for' => ['faculty', 'admin'], 'is_active' => true,
        ]);
        foreach (['faculty', 'admin'] as $type) {
            $profile = $this->approvedEmployee($type);
            $response = app(AdminController::class)->requestBulkEmployeeHealthForms(Request::create('/admin/health-records/bulk-request-health-form', 'POST', [
                'user_type' => $type, 'category' => $category->name, 'employee_profile_ids' => [$profile->id],
            ]));
            $this->assertStringContainsString('1 employee health assessment started', $response->getSession()->get('success'));
            $this->assertStringContainsString('pending_approval', $response->getTargetUrl());
            $this->assertSame($profile->id, $response->getSession()->get('open_employee_assessment'));
            $this->assertSame('For Verification', $profile->fresh()->clearance_status);
            $this->assertSame(1, HealthFormSubmission::where('employee_health_profile_id', $profile->id)->where('status', 'submitted')->count());
        }
    }

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
        $cycleEvent = $history->firstWhere('title', 'Health Assessment Started');
        $this->assertNotNull($cycleEvent);
        $this->assertSame($admin->name, $cycleEvent['actor']);
        $this->assertSame('Awaiting Assessment', $cycleEvent['status']);
    }
}
