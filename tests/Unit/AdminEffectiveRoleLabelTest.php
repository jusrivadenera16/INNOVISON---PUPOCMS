<?php

namespace Tests\Unit;

use App\Http\Controllers\AdminUserController;
use App\Models\Admin;
use App\Models\User;
use ReflectionMethod;
use PHPUnit\Framework\TestCase;

class AdminEffectiveRoleLabelTest extends TestCase
{
    public function test_inactive_default_designee_profile_displays_as_admin_regular(): void
    {
        $this->assertSame('Admin - Regular', $this->roleLabel(''));
    }

    public function test_active_admin_hub_and_clinic_access_keep_their_specific_labels(): void
    {
        $this->assertSame('Admin - Designee', $this->roleLabel('designee'));
        $this->assertSame('Admin - Clinic Staff', $this->roleLabel('clinic_staff'));
    }

    public function test_idp_uuid_is_not_used_as_a_human_facing_identifier(): void
    {
        $this->assertSame('', $this->displayIdentifier('', '23bc49da-b339-4bc1-ad6d-5e8da90014f3'));
        $this->assertSame('2025-00523-TG-1', $this->displayIdentifier('2025-00523-TG-1', '23bc49da-b339-4bc1-ad6d-5e8da90014f3'));
        $this->assertSame('FAC-001', $this->displayIdentifier('', 'FAC-001'));
    }

    public function test_original_identity_is_restored_from_the_idp_role(): void
    {
        $this->assertSame('Student', $this->defaultUserType('student'));
        $this->assertSame('Faculty', $this->defaultUserType('faculty'));
        $this->assertSame('Guest', $this->defaultUserType('guest'));
        $this->assertSame('Regular', $this->defaultUserType('admin'));
    }

    public function test_account_access_identifiers_follow_the_original_identity(): void
    {
        $admin = new Admin(['admin_id' => 42]);

        $adminUser = new User(['idp_role' => 'admin', 'student_id' => '23bc49da-b339-4bc1-ad6d-5e8da90014f3']);
        $this->assertSame('42', $this->userIdentifier($adminUser, $admin));

        $assistant = new User(['idp_role' => 'student', 'user_type' => 'Assistant', 'student_number' => '2025-00123-TG-0']);
        $this->assertSame('2025-00123-TG-0', $this->userIdentifier($assistant));

        $faculty = new User(['idp_role' => 'faculty', 'student_id' => 'FAC-009']);
        $this->assertSame('FAC-009', $this->userIdentifier($faculty));
    }

    private function roleLabel(string $accessLevel): string
    {
        $controller = new AdminUserController();
        $method = new ReflectionMethod($controller, 'adminRoleLabelForAccessLevel');
        $method->setAccessible(true);

        return $method->invoke($controller, $accessLevel);
    }

    private function displayIdentifier(string $studentNumber, string $studentId): string
    {
        $controller = new AdminUserController();
        $method = new ReflectionMethod($controller, 'resolveDisplayIdentifier');
        $method->setAccessible(true);

        return $method->invoke($controller, $studentNumber, $studentId);
    }

    private function defaultUserType(string $idpRole): string
    {
        $controller = new AdminUserController();
        $method = new ReflectionMethod($controller, 'defaultUserTypeForIdpRole');
        $method->setAccessible(true);

        return $method->invoke($controller, $idpRole);
    }

    private function userIdentifier(User $user, ?Admin $admin = null): string
    {
        $controller = new AdminUserController();
        $method = new ReflectionMethod($controller, 'resolveUserDisplayIdentifier');
        $method->setAccessible(true);

        return $method->invoke($controller, $user, $admin);
    }
}
