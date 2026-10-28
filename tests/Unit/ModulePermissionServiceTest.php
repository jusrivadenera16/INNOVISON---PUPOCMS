<?php

namespace Tests\Unit;

use App\Models\User;
use App\Services\ModulePermissionService;
use PHPUnit\Framework\TestCase;

class ModulePermissionServiceTest extends TestCase
{
    public function test_action_permissions_require_their_parent_module(): void
    {
        $service = new ModulePermissionService();
        $user = new User();
        $user->user_role = User::ROLE_ADMIN;
        $user->module_permissions = ['reports.mar'];

        $this->assertFalse($service->can($user, 'reports.mar'));
    }

    public function test_saved_module_and_action_permissions_are_granted(): void
    {
        $service = new ModulePermissionService();
        $user = new User();
        $user->user_role = User::ROLE_ADMIN;
        $user->module_permissions = ['appointments.view', 'appointments.approve'];

        $this->assertTrue($service->can($user, 'appointments.view'));
        $this->assertTrue($service->can($user, 'appointments.approve'));
        $this->assertFalse($service->can($user, 'appointments.reject'));
    }

    public function test_super_admin_has_full_module_access(): void
    {
        $service = new ModulePermissionService();
        $user = new User();
        $user->user_role = User::ROLE_SUPERADMIN;

        $this->assertTrue($service->can($user, 'settings.faqs'));
        $this->assertTrue($service->can($user, 'inventory.manage'));
        $this->assertTrue($service->can($user, 'reports.export_reports'));
    }

    public function test_medical_information_edit_requires_assignment_for_admin_and_student_assistant(): void
    {
        $service = new ModulePermissionService();

        foreach ([User::ROLE_ADMIN, 'student_assistant'] as $role) {
            $user = new User();
            $user->user_role = $role;
            $user->module_permissions = ['health_records.view'];

            $this->assertFalse($service->can($user, 'health_records.update_assessment'));

            $user->module_permissions = ['health_records.view', 'health_records.update_assessment'];
            $this->assertTrue($service->can($user, 'health_records.update_assessment'));
        }

        $superadmin = new User();
        $superadmin->user_role = User::ROLE_SUPERADMIN;
        $this->assertTrue($service->can($superadmin, 'health_records.update_assessment'));
    }

    public function test_final_walkin_approval_is_reserved_for_super_admin(): void
    {
        $service = new ModulePermissionService();
        $user = new User();
        $user->user_role = User::ROLE_ADMIN;
        $user->module_permissions = ['walkin.view', 'walkin.final_review'];

        $this->assertFalse($service->can($user, 'walkin.final_review'));
    }

    public function test_report_exports_are_reserved_for_super_admin(): void
    {
        $service = new ModulePermissionService();
        $user = new User();
        $user->user_role = User::ROLE_ADMIN;
        $user->module_permissions = ['reports.view', 'reports.export_reports'];

        $this->assertFalse($service->can($user, 'reports.export_reports'));
    }

    public function test_legacy_feedback_report_permission_is_no_longer_assignable(): void
    {
        $service = new ModulePermissionService();
        $user = new User();
        $user->user_role = User::ROLE_ADMIN;
        $user->module_permissions = ['reports.view', 'reports.feedbacks'];

        $this->assertFalse($service->can($user, 'reports.feedbacks'));
        $this->assertFalse($service->isKnown('reports.feedbacks'));
        $this->assertSame(['reports.view'], $service->normalize(['reports.view', 'reports.feedbacks']));
    }

    public function test_employee_id_lookup_requires_employee_record_view_access(): void
    {
        $service = new ModulePermissionService();

        $this->assertSame(
            ['walkin.view', 'walkin.employee_view', 'walkin.employee_lookup'],
            $service->normalize(['walkin.view', 'walkin.employee_view', 'walkin.employee_lookup'])
        );
        $this->assertSame(
            ['walkin.view'],
            $service->normalize(['walkin.view', 'walkin.employee_lookup'])
        );
    }

    public function test_reference_lookup_edit_and_new_health_form_actions_require_their_parents(): void
    {
        $service = new ModulePermissionService();

        $this->assertSame(
            ['walkin.view', 'walkin.reference_lookup', 'walkin.edit_information'],
            $service->normalize(['walkin.view', 'walkin.reference_lookup', 'walkin.edit_information'])
        );
        $this->assertSame(
            ['health_records.view', 'health_records.request_health_form'],
            $service->normalize(['health_records.view', 'health_records.request_health_form'])
        );
        $this->assertSame(
            ['walkin.view'],
            $service->normalize(['walkin.view', 'walkin.edit_information'])
        );
    }
}
