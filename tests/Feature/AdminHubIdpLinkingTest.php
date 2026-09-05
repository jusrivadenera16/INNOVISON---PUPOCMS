<?php

namespace Tests\Feature;

use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\AdminUserController;
use App\Http\Controllers\AppointmentController;
use App\Http\Middleware\RoleMiddleware;
use App\Models\Admin;
use App\Models\AdminHub;
use App\Models\User;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Schema;
use ReflectionMethod;
use Tests\TestCase;

class AdminHubIdpLinkingTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        Config::set('database.default', 'sqlite');
        Config::set('database.connections.sqlite.database', ':memory:');
        DB::purge('sqlite');
        DB::reconnect('sqlite');

        Schema::create('users', function (Blueprint $table) {
            $table->id();
            $table->string('student_id')->unique();
            $table->string('student_number')->nullable()->unique();
            $table->string('employee_number')->nullable();
            $table->string('reference_number')->nullable();
            $table->string('first_name');
            $table->string('middle_name')->nullable();
            $table->string('last_name');
            $table->string('name');
            $table->string('email')->unique();
            $table->string('user_role')->default('student');
            $table->string('idp_role')->nullable();
            $table->string('user_type')->nullable();
            $table->string('status')->default('active');
            $table->string('password');
            $table->rememberToken();
            $table->timestamps();
        });

        Schema::create('admins', function (Blueprint $table) {
            $table->id('admin_id');
            $table->unsignedBigInteger('user_id')->nullable();
            $table->string('first_name')->nullable();
            $table->string('middle_name')->nullable();
            $table->string('last_name')->nullable();
            $table->string('name')->nullable();
            $table->string('email')->nullable();
            $table->string('employee_number')->nullable();
            $table->string('email_address')->nullable();
            $table->string('access_level')->nullable();
            $table->string('status')->default('active');
            $table->timestamps();
        });

        Schema::create('admin_hub', function (Blueprint $table) {
            $table->id();
            $table->string('admin_uuid')->nullable()->unique();
            $table->string('employee_number')->nullable()->index();
            $table->unsignedBigInteger('user_id')->nullable();
            $table->string('first_name')->nullable();
            $table->string('middle_name')->nullable();
            $table->string('last_name')->nullable();
            $table->string('suffix_name')->nullable();
            $table->string('name')->nullable();
            $table->string('email')->nullable()->index();
            $table->date('birthday')->nullable();
            $table->unsignedSmallInteger('age')->nullable();
            $table->string('gender')->nullable();
            $table->string('civil_status')->nullable();
            $table->text('address')->nullable();
            $table->string('emergency_contact_person')->nullable();
            $table->string('emergency_contact_no')->nullable();
            $table->string('office')->nullable();
            $table->string('access_level')->nullable();
            $table->string('role')->default('admin_designee');
            $table->string('status')->default('active');
            $table->timestamps();
        });
    }

    protected function tearDown(): void
    {
        Schema::dropIfExists('admin_hub');
        Schema::dropIfExists('admins');
        Schema::dropIfExists('users');

        parent::tearDown();
    }

    public function test_idp_uuid_links_a_designee_even_when_the_email_changed(): void
    {
        $hub = AdminHub::create([
            'admin_uuid' => '4df68c44-d455-42ea-8580-5ae77173aa11',
            'name' => 'Original Admin',
            'email' => 'old-address@example.test',
            'role' => 'admin_designee',
            'status' => 'active',
        ]);

        $user = $this->upsertFromIdp([
            'sub' => '4df68c44-d455-42ea-8580-5ae77173aa11',
            'email' => 'new-address@example.test',
            'firstname' => 'Updated',
            'lastname' => 'Admin',
            'role' => 'faculty',
        ]);

        $this->assertSame(User::ROLE_ADMIN, User::normalizeRole($user->user_role));
        $this->assertSame('4df68c44-d455-42ea-8580-5ae77173aa11', $user->student_id);
        $this->assertDatabaseHas('admin_hub', [
            'id' => $hub->id,
            'admin_uuid' => '4df68c44-d455-42ea-8580-5ae77173aa11',
            'user_id' => $user->id,
            'email' => 'new-address@example.test',
        ]);
        $this->assertDatabaseCount('admins', 0);
    }

    public function test_first_idp_login_backfills_uuid_on_an_email_only_hub_profile(): void
    {
        $hub = AdminHub::create([
            'email' => 'legacy@example.test',
            'role' => 'admin_designee',
            'status' => 'active',
        ]);

        $user = $this->upsertFromIdp([
            'sub' => 'e5b894ba-799f-44e5-8b43-4088f3eed44b',
            'email' => 'legacy@example.test',
            'firstname' => 'Legacy',
            'lastname' => 'Designee',
            'role' => 'faculty',
        ]);

        $this->assertSame(User::ROLE_ADMIN, User::normalizeRole($user->user_role));
        $this->assertDatabaseHas('admin_hub', [
            'id' => $hub->id,
            'admin_uuid' => 'e5b894ba-799f-44e5-8b43-4088f3eed44b',
            'user_id' => $user->id,
        ]);
        $this->assertDatabaseCount('admins', 0);
    }

    public function test_matching_email_cannot_override_a_different_stored_uuid(): void
    {
        $hub = AdminHub::create([
            'admin_uuid' => '8ac50a30-b6c8-4f8d-b008-f654012ad16a',
            'email' => 'shared@example.test',
            'role' => 'admin_designee',
            'status' => 'active',
        ]);

        $user = $this->upsertFromIdp([
            'sub' => 'f415515b-5930-4ad6-a49b-7c9b0523805f',
            'email' => 'shared@example.test',
            'firstname' => 'Different',
            'lastname' => 'Identity',
            'role' => 'faculty',
        ]);

        $this->assertSame(User::ROLE_STUDENT, User::normalizeRole($user->user_role));
        $this->assertDatabaseHas('admin_hub', [
            'id' => $hub->id,
            'admin_uuid' => '8ac50a30-b6c8-4f8d-b008-f654012ad16a',
            'user_id' => null,
            'email' => 'shared@example.test',
        ]);
        $this->assertDatabaseCount('admins', 0);
    }

    public function test_first_idp_login_moves_a_legacy_employee_code_out_of_admin_uuid(): void
    {
        $hub = AdminHub::create([
            'admin_uuid' => 'FA001TG2023',
            'email' => 'faculty@example.test',
            'role' => 'admin_designee',
            'status' => 'active',
        ]);

        $user = $this->upsertFromIdp([
            'sub' => '6104038d-6449-4211-a248-318e1bbb452b',
            'faculty_code' => 'FA001TG2023',
            'email' => 'faculty@example.test',
            'firstname' => 'Faculty',
            'lastname' => 'Designee',
            'role' => 'faculty',
        ]);

        $this->assertSame(User::ROLE_ADMIN, User::normalizeRole($user->user_role));
        $this->assertSame('FA001TG2023', $user->employee_number);
        $this->assertDatabaseHas('admin_hub', [
            'id' => $hub->id,
            'admin_uuid' => '6104038d-6449-4211-a248-318e1bbb452b',
            'employee_number' => 'FA001TG2023',
            'user_id' => $user->id,
        ]);
    }

    public function test_account_access_superadmin_role_wins_over_admin_hub_designee_membership(): void
    {
        $user = User::create([
            'student_id' => '922bf3cd-bde8-4fd2-a229-885e996ab577',
            'first_name' => 'Clinic',
            'last_name' => 'Owner',
            'name' => 'Clinic Owner',
            'email' => 'owner@example.test',
            'user_role' => User::ROLE_SUPERADMIN,
            'idp_role' => 'faculty',
            'user_type' => 'Regular',
            'status' => 'active',
            'password' => bcrypt('secret'),
        ]);

        Admin::create([
            'user_id' => $user->id,
            'name' => 'Clinic Owner',
            'email' => 'owner@example.test',
            'access_level' => 'superadmin',
            'status' => 'active',
        ]);

        AdminHub::create([
            'admin_uuid' => '922bf3cd-bde8-4fd2-a229-885e996ab577',
            'user_id' => $user->id,
            'name' => 'Clinic Owner',
            'email' => 'owner@example.test',
            'role' => 'admin_designee',
            'access_level' => 'designee',
            'status' => 'active',
        ]);

        $syncedUser = $this->upsertFromIdp([
            'sub' => '922bf3cd-bde8-4fd2-a229-885e996ab577',
            'email' => 'owner@example.test',
            'firstname' => 'Clinic',
            'lastname' => 'Owner',
            'role' => 'faculty',
        ]);

        $this->assertSame(User::ROLE_SUPERADMIN, User::normalizeRole($syncedUser->user_role));
        $this->assertDatabaseHas('admins', [
            'user_id' => $user->id,
            'access_level' => 'superadmin',
        ]);
        $this->assertDatabaseHas('admin_hub', [
            'user_id' => $user->id,
            'role' => 'admin_designee',
            'access_level' => 'designee',
        ]);
    }

    public function test_activating_admin_hub_membership_does_not_demote_account_access_superadmin(): void
    {
        $user = User::create([
            'student_id' => '7122a999-1377-48df-99ba-5d1b69b8555f',
            'first_name' => 'Existing',
            'last_name' => 'Superadmin',
            'name' => 'Existing Superadmin',
            'email' => 'existing-superadmin@example.test',
            'user_role' => User::ROLE_SUPERADMIN,
            'user_type' => 'Regular',
            'status' => 'inactive',
            'password' => bcrypt('secret'),
        ]);

        Admin::create([
            'user_id' => $user->id,
            'name' => 'Existing Superadmin',
            'email' => 'existing-superadmin@example.test',
            'access_level' => 'superadmin',
            'status' => 'active',
        ]);

        $controller = new AdminUserController();
        $method = new ReflectionMethod($controller, 'activateUserForAdminHub');
        $method->setAccessible(true);
        $method->invoke($controller, $user);

        $this->assertSame(User::ROLE_SUPERADMIN, User::normalizeRole($user->fresh()->user_role));
        $this->assertSame('inactive', $user->fresh()->status);
    }

    public function test_faculty_id_is_not_used_as_an_employee_number(): void
    {
        $user = $this->upsertFromIdp([
            'sub' => 'd7ce50da-5c84-4817-975d-c1bf3b605340',
            'faculty_id' => '12345',
            'email' => 'faculty-without-number@example.test',
            'firstname' => 'No',
            'lastname' => 'Employee Number',
            'role' => 'faculty',
        ]);

        $this->assertNull($user->employee_number);
    }

    public function test_me_faculty_roles_correct_a_previously_student_account(): void
    {
        $identity = [
            'id' => '45e0d8c6-a201-418b-901b-072f1c96225f',
            'email' => 'faculty-role@example.test',
            'first_name' => 'Faculty',
            'last_name' => 'Test',
        ];
        $oldUser = $this->upsertFromIdp($identity + ['role' => 'student']);
        Config::set('services.idp.base_url', 'https://idp.example.test');
        Config::set('services.idp.profile_paths', ['/me']);
        Http::fake(['*' => Http::response($identity + ['roles' => 'faculty'])]);

        $controller = new LoginController();
        $fetch = new ReflectionMethod($controller, 'fetchProfileFromIdp');
        $merge = new ReflectionMethod($controller, 'mergeIdpProfilePayloads');
        $profile = $merge->invoke($controller, $identity + ['role' => 'student'], $fetch->invoke($controller, 'test-token'));
        $user = $this->upsertFromIdp($profile)->fresh();

        $this->assertSame($oldUser->id, $user->id);
        $this->assertSame('faculty', $user->idp_role);
        $this->assertSame('Faculty', $user->user_type);
        $this->assertSame(User::ROLE_STUDENT, $user->user_role);
        $this->assertSame('employee', $user->idpHealthFormAudience());
        $this->assertDatabaseCount('users', 1);
        $this->assertDatabaseCount('admins', 0);

        Http::assertSent(fn ($request) => str_ends_with($request->url(), '/me'));
        Http::assertSentCount(1);
        $enrich = new ReflectionMethod($controller, 'enrichUserWithGuisisData');
        $enrich->invoke($controller, $user);
        Http::assertSentCount(1);
    }

    public function test_idp_classifications_save_without_creating_account_access(): void
    {
        $cases = [
            ['student', 'Student', 'student'],
            ['applicant', 'Applicant', 'applicant'],
            ['faculty', 'Faculty', 'employee'],
            ['admin', 'Admin', 'employee'],
            ['non-teaching staff', 'Admin', 'employee'],
            ['non_teaching_staff', 'Admin', 'employee'],
            ['non teaching staff', 'Admin', 'employee'],
            ['guest', 'Guest', 'dependent'],
            ['dependent', 'Dependent', 'dependent'],
            ['superadmin', 'Dependent', 'dependent'],
            ['super_admin', 'Dependent', 'dependent'],
            ['student_assistant', 'Dependent', 'dependent'],
            ['custom_admin_role', 'Dependent', 'dependent'],
            ['unknown', 'Dependent', 'dependent'],
        ];
        $controller = new AppointmentController();
        $employee = new ReflectionMethod($controller, 'shouldUseEmployeeHealthForm');
        $dependent = new ReflectionMethod($controller, 'isDependentProfileUser');
        $login = new LoginController();
        $prompt = new ReflectionMethod($login, 'isDependentProfileUser');
        $redirect = new ReflectionMethod($login, 'resolveRedirectPathForUser');

        foreach ($cases as $index => [$role, $type, $audience]) {
            $user = $this->upsertFromIdp([
                'id' => 'idp-role-' . $index,
                'email' => 'role-' . $index . '@example.test',
                'first_name' => 'Role',
                'last_name' => 'Test',
                'roles' => [$role],
                'role' => 'student',
            ])->fresh();

            $this->assertSame($role, $user->idp_role);
            $this->assertSame($type, $user->user_type, $role);
            $this->assertSame(User::ROLE_STUDENT, $user->user_role, $role);
            $this->assertSame($audience, $user->idpHealthFormAudience(), $role);
            $this->assertSame($audience === 'employee', $employee->invoke($controller, $user), $role);
            $this->assertSame($audience === 'dependent', $dependent->invoke($controller, $user), $role);
            $this->assertSame($audience === 'dependent', $prompt->invoke($login, $user), $role);
            $this->assertSame('/student/home', $redirect->invoke($login, $user), $role);
            $this->assertFalse($this->canAccessAdminRoutes($user), $role);
        }

        $this->assertDatabaseCount('admins', 0);
        $this->assertDatabaseCount('admin_hub', 0);
    }

    public function test_missing_roles_default_to_dependent_and_email_shortcuts_do_not_grant_access(): void
    {
        Config::set('services.idp.local_superadmin_identifiers', ['pupocms']);
        $user = $this->upsertFromIdp([
            'id' => 'missing-role-id',
            'email' => 'pupocms@example.test',
            'first_name' => 'No',
            'last_name' => 'Role',
        ]);

        $this->assertSame('dependent', $user->idp_role);
        $this->assertSame('Dependent', $user->user_type);
        $this->assertSame(User::ROLE_STUDENT, $user->user_role);
        $this->assertFalse($this->canAccessAdminRoutes($user));
        $redirect = new ReflectionMethod(LoginController::class, 'resolveRedirectPathForUser');
        $this->assertSame('/student/home', $redirect->invoke(new LoginController(), $user));
    }

    public function test_faculty_lookup_cannot_reclassify_other_idp_roles(): void
    {
        $this->mock(\App\Services\FacultySyncService::class)
            ->shouldNotReceive('fetchFaculties');
        $controller = new LoginController();
        $enrich = new ReflectionMethod($controller, 'enrichUserWithFlssFacultyData');

        foreach (['student', 'applicant', 'guest', 'superadmin', 'non-teaching staff'] as $role) {
            $user = $this->upsertFromIdp([
                'id' => 'faculty-lookup-' . $role,
                'email' => str_replace(' ', '-', $role) . '@example.test',
                'roles' => $role,
            ]);
            $userType = $user->user_type;
            $enrich->invoke($controller, $user);
            $this->assertSame($userType, $user->fresh()->user_type);
        }
    }

    public function test_stale_superadmin_role_without_account_access_is_blocked_and_corrected_on_login(): void
    {
        $identity = ['id' => 'stale-superadmin-id', 'email' => 'stale@example.test'];
        $user = $this->upsertFromIdp($identity + ['roles' => 'superadmin']);
        $user->user_role = User::ROLE_SUPERADMIN;
        $user->save();

        $this->assertFalse($this->canAccessAdminRoutes($user));
        $updated = $this->upsertFromIdp($identity + ['roles' => 'superadmin'])->fresh();
        $this->assertSame(User::ROLE_STUDENT, $updated->user_role);
        $this->assertSame('dependent', $updated->idpHealthFormAudience());
        $this->assertDatabaseCount('admins', 0);
    }

    public function test_admin_routes_require_active_local_account_access(): void
    {
        foreach (['superadmin', 'clinic_staff'] as $accessLevel) {
            $identity = ['id' => 'local-' . $accessLevel, 'email' => $accessLevel . '@example.test'];
            $user = $this->upsertFromIdp($identity + ['roles' => 'guest']);
            $admin = Admin::create([
                'user_id' => $user->id,
                'email' => $user->email,
                'access_level' => $accessLevel,
                'status' => 'active',
            ]);

            $user = $this->upsertFromIdp($identity + ['roles' => 'guest']);
            $this->assertSame($accessLevel === 'superadmin' ? User::ROLE_SUPERADMIN : User::ROLE_ADMIN, $user->user_role);
            $this->assertTrue($this->canAccessAdminRoutes($user));

            $admin->update(['status' => 'inactive']);
            $this->assertFalse($this->canAccessAdminRoutes($user));
            $user = $this->upsertFromIdp($identity + ['roles' => 'guest']);
            $this->assertSame(User::ROLE_STUDENT, $user->user_role);
            $this->assertFalse($this->canAccessAdminRoutes($user));
        }
    }

    private function canAccessAdminRoutes(User $user): bool
    {
        $middleware = new RoleMiddleware();
        $access = new ReflectionMethod($middleware, 'hasRoleAccess');

        return $access->invoke($middleware, $user, User::normalizeRole($user->user_role), [User::ROLE_SUPERADMIN, User::ROLE_ADMIN]);
    }

    private function upsertFromIdp(array $profile): User
    {
        $controller = new LoginController();
        $method = new ReflectionMethod($controller, 'upsertLocalUserFromIdpProfile');
        $method->setAccessible(true);

        return $method->invoke($controller, $profile);
    }
}
