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
use Illuminate\Validation\ValidationException;
use ReflectionMethod;
use Tests\TestCase;

class AdminHubIdpLinkingTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        Config::set('database.default', 'sqlite');
        Config::set('database.connections.sqlite.database', ':memory:');
        Config::set('app.url', 'http://localhost');
        \Illuminate\Support\Facades\URL::forceRootUrl('http://localhost');
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
            $table->string('clinic_account_type')->nullable();
            $table->string('status')->default('active');
            $table->string('password');
            $table->rememberToken();
            $table->timestamps();
        });

        Schema::create('health_profiles', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id');
            $table->string('reference_number')->nullable();
            $table->string('clearance_status')->nullable();
            $table->string('pullout_status')->nullable();
            $table->timestamps();
        });
        Schema::create('activity_logs', function (Blueprint $table) {
            $table->id();
            foreach (['user_id', 'user_name', 'user_role', 'action', 'module', 'event_type', 'description', 'route_name', 'http_method', 'request_path', 'status_code', 'subject_type', 'subject_id', 'metadata', 'ip_address', 'user_agent'] as $column) {
                $table->text($column)->nullable();
            }
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
        Schema::dropIfExists('activity_logs');
        Schema::dropIfExists('health_profiles');
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

    public function test_idp_roles_are_metadata_and_never_choose_a_form_or_grant_access(): void
    {
        foreach (['student', 'faculty', 'admin', 'superadmin', 'guest', 'unknown', ''] as $index => $role) {
            $user = $this->upsertFromIdp([
                'id' => 'metadata-' . $index,
                'email' => 'metadata-' . $index . '@example.test',
                'roles' => $role,
                'account_type' => 'Faculty',
            ])->fresh();

            $this->assertSame($role ?: null, $user->idp_role);
            $this->assertSame(User::ROLE_STUDENT, $user->user_role);
            $this->assertNull($user->user_type);
            $this->assertTrue($user->needsClinicAccountTypeSelection());
            $this->assertSame('unselected', $user->clinicHealthFormAudience());
            $this->assertFalse($this->canAccessAdminRoutes($user));
        }
        $this->assertDatabaseCount('admins', 0);
    }

    public function test_all_local_choices_save_the_right_form_without_changing_idp_or_access(): void
    {
        $cases = [
            ['applicant', 'Applicant', 'health.form'],
            ['student', 'Student', 'health.form.student'],
            ['faculty', 'Faculty', 'health.form.employee'],
            ['non_teaching_staff', 'Admin', 'health.form.employee'],
            ['dependent', 'Dependent', 'dependent.profile.form'],
        ];
        foreach ($cases as $index => [$type, $label, $route]) {
            $user = $this->upsertFromIdp(['id' => 'choice-' . $index, 'email' => 'choice-' . $index . '@example.test', 'roles' => 'superadmin']);
            $response = $this->saveClinicType($user, $type, ['user_role' => 'superadmin', 'idp_role' => 'admin']);
            $user->refresh();

            $this->assertSame(route($route), $response->getData(true)['redirect']);
            $this->assertSame($type, $user->clinic_account_type);
            $this->assertSame($label, $user->user_type);
            $this->assertSame('superadmin', $user->idp_role);
            $this->assertSame(User::ROLE_STUDENT, $user->user_role);
            $this->assertFalse($this->canAccessAdminRoutes($user));
            $this->assertFalse($user->needsClinicAccountTypeSelection());
        }
        $this->assertDatabaseCount('admins', 0);
    }

    public function test_local_choice_survives_blank_and_changed_idp_roles_on_later_logins(): void
    {
        $identity = ['id' => 'returning-faculty', 'email' => 'returning-faculty@example.test'];
        $user = $this->upsertFromIdp($identity + ['roles' => '']);
        $this->saveClinicType($user, 'faculty');
        foreach (['student', '', 'guest'] as $role) {
            $user = $this->upsertFromIdp($identity + ['roles' => $role, 'first_name' => 'Updated'])->fresh();
            $this->assertSame('faculty', $user->clinic_account_type);
            $this->assertSame('Faculty', $user->user_type);
            $this->assertSame('employee', $user->clinicHealthFormAudience());
            $this->assertSame('Updated', $user->first_name);
        }
    }

    public function test_account_type_http_endpoint_requires_login_and_saves_only_classification(): void
    {
        Config::set('services.idp.enabled', false);
        $this->postJson('/student/account-type', ['clinic_account_type' => 'faculty'])->assertUnauthorized();

        $user = $this->upsertFromIdp(['id' => 'http-choice', 'email' => 'http-choice@example.test', 'roles' => 'superadmin']);
        $this->actingAs($user, 'student')
            ->postJson('/student/account-type', [
                'clinic_account_type' => 'faculty', 'user_role' => 'superadmin', 'idp_role' => 'faculty',
            ])->assertOk()->assertJson(['redirect' => route('health.form.employee')]);
        $this->assertDatabaseHas('users', [
            'id' => $user->id, 'clinic_account_type' => 'faculty', 'user_role' => 'student', 'idp_role' => 'superadmin',
        ]);
        $this->postJson('/student/account-type', ['clinic_account_type' => 'student'])
            ->assertUnprocessable()->assertJsonValidationErrors('clinic_account_type');
        $this->actingAs($user->fresh(), 'student')->get('/student/health-form/student')
            ->assertRedirect(route('health.form.employee'));
        $this->assertDatabaseCount('admins', 0);
    }

    public function test_saved_choice_is_idempotent_but_cannot_be_switched_by_resubmitting(): void
    {
        $user = $this->upsertFromIdp(['id' => 'locked-choice', 'email' => 'locked-choice@example.test']);
        $this->saveClinicType($user, 'student');
        $this->saveClinicType($user, 'student');
        try {
            $this->saveClinicType($user, 'dependent');
            $this->fail('A saved choice must not be switched through the public selector.');
        } catch (ValidationException $exception) {
            $this->assertArrayHasKey('clinic_account_type', $exception->errors());
        }
        $this->assertSame('student', $user->fresh()->clinic_account_type);
    }

    public function test_unrecognized_and_privileged_local_choices_are_rejected(): void
    {
        $user = $this->upsertFromIdp(['id' => 'invalid-choice', 'email' => 'invalid-choice@example.test']);
        foreach (['superadmin', 'admin', '', 'unknown'] as $type) {
            try {
                $this->saveClinicType($user, $type);
                $this->fail('Unexpected classification was accepted.');
            } catch (ValidationException $exception) {
                $this->assertArrayHasKey('clinic_account_type', $exception->errors());
            }
            $this->assertNull($user->fresh()->clinic_account_type);
        }
    }

    public function test_pending_admission_reference_restricts_selection_and_direct_form_access(): void
    {
        $user = $this->upsertFromIdp([
            'id' => 'pending-applicant', 'email' => 'pending-applicant@example.test',
            'reference_number' => '2026-1234-5678', 'roles' => 'student',
        ]);
        foreach (['student', 'faculty', 'non_teaching_staff', 'dependent'] as $type) {
            try {
                $this->saveClinicType($user, $type);
                $this->fail('An applicant must not bypass admission requirements.');
            } catch (ValidationException $exception) {
                $this->assertArrayHasKey('clinic_account_type', $exception->errors());
            }
        }
        $this->saveClinicType($user, 'applicant');
        $user->refresh();
        $this->assertSame('applicant', $user->clinicHealthFormAudience());

        foreach (['health.form.student', 'store.health.form.student', 'health.form.employee', 'store.health.form.employee', 'dependent.profile.form', 'dependent.profile.store'] as $route) {
            $response = $this->checkFormRoute($user, $route);
            $this->assertSame(route('health.form'), $response->getTargetUrl());
        }
    }

    public function test_reference_appearing_after_selection_still_enforces_admission(): void
    {
        $user = $this->upsertFromIdp(['id' => 'late-reference', 'email' => 'late-reference@example.test']);
        $this->saveClinicType($user, 'student');
        $user->refresh();
        $user->reference_number = '2026-1010-2020';
        $user->save();
        $this->assertSame('applicant', $user->clinicHealthFormAudience());
        $this->assertSame(route('health.form'), $this->checkFormRoute($user, 'store.health.form.student')->getTargetUrl());
    }

    public function test_students_without_admission_references_are_not_forced_into_applicant_flow(): void
    {
        $user = $this->upsertFromIdp(['id' => 'enrolled-student', 'email' => 'enrolled-student@example.test', 'student_number' => '2020-20201-TG-0']);
        $this->saveClinicType($user, 'student');
        $user->refresh();
        $this->assertFalse($user->hasPendingAdmissionReference());
        $this->assertSame('student', $user->clinicHealthFormAudience());
        $this->assertSame(204, $this->checkFormRoute($user, 'store.health.form.student')->getStatusCode());
    }

    public function test_issued_applicant_clearance_allows_student_followup_forms(): void
    {
        $user = $this->upsertFromIdp(['id' => 'approved-applicant', 'email' => 'approved-applicant@example.test', 'reference_number' => '2026-1010-1010']);
        $this->saveClinicType($user, 'applicant');
        $profile = \App\Models\HealthProfile::create([
            'user_id' => $user->id, 'reference_number' => $user->reference_number, 'clearance_status' => 'Issued',
        ]);
        $user->refresh();
        $this->assertFalse($user->hasPendingAdmissionReference());
        $this->assertSame('student', $user->clinicHealthFormAudience());
        $this->assertSame(204, $this->checkFormRoute($user, 'store.health.form.student')->getStatusCode());
        $profile->update(['clearance_status' => 'Pending/Conditional']);
        $user->refresh();
        $this->assertSame('applicant', $user->clinicHealthFormAudience());
    }

    public function test_unselected_users_cannot_open_or_submit_any_health_form_directly(): void
    {
        $user = $this->upsertFromIdp(['id' => 'unselected', 'email' => 'unselected@example.test', 'roles' => 'faculty']);
        foreach (['health.form', 'store.health.form', 'health.form.student', 'store.health.form.student', 'health.form.employee', 'store.health.form.employee', 'dependent.profile.form', 'dependent.profile.store'] as $route) {
            $this->assertSame(route('student.home'), $this->checkFormRoute($user, $route)->getTargetUrl());
        }
    }

    public function test_callback_with_blank_roles_logs_in_and_prompts_for_local_selection(): void
    {
        Config::set('services.idp.enabled', true);
        Config::set('services.idp.base_url', 'https://idp.example.test');
        Config::set('services.idp.client_id', 'test-client');
        Config::set('services.idp.client_secret', 'test-secret');
        Config::set('services.idp.token_path', '/token');
        Config::set('services.idp.profile_paths', ['/me']);
        Http::fake([
            'https://idp.example.test/token' => Http::response(['access_token' => 'test-token']),
            'https://idp.example.test/me' => Http::response([
                'id' => 'blank-callback-id', 'email' => 'blank-callback@example.test',
                'first_name' => 'Student', 'last_name' => 'Test', 'roles' => '',
            ]),
            '*' => Http::response([], 500),
        ]);

        $request = \Illuminate\Http\Request::create('/auth/callback', 'GET', ['code' => 'test-code']);
        $request->setLaravelSession($this->app['session.store']);
        $response = (new LoginController())->handleIdpCallback($request);

        $this->assertSame(url('/student/home'), $response->getTargetUrl());
        $this->assertAuthenticated('student');
        $this->assertGuest('admin');
        $this->assertTrue(session('show_health_profile_prompt'));
        $this->assertDatabaseHas('users', ['email' => 'blank-callback@example.test', 'idp_role' => null, 'clinic_account_type' => null, 'user_type' => null]);
        Http::assertSentCount(2);
    }

    public function test_selector_renders_five_choices_and_loads_available_options(): void
    {
        $html = view('student.partials.clinic_account_type_selector', ['studentPendingAdmission' => false])->render();
        $this->assertSame(5, substr_count($html, 'name="clinic_account_type"'));
        $this->assertStringContainsString('Non-teaching Staff / Admin Designee', $html);
        $this->assertStringContainsString('Guest / Dependent', $html);
        $this->assertStringContainsString('Saving...', $html);
        $locked = view('student.partials.clinic_account_type_selector', ['studentPendingAdmission' => true])->render();
        $this->assertSame(5, preg_match_all('/<input[^>]+type="radio"[^>]+disabled[^>]*>/', $locked));
        $this->assertStringContainsString(route('student.account_type.options'), $html);
    }

    public function test_recognized_idp_roles_restrict_options_and_reject_tampered_choices(): void
    {
        foreach (['faculty' => 'faculty', 'student' => 'student', 'applicant' => 'applicant', 'guest' => 'dependent', 'dependent' => 'dependent', 'admin' => 'non_teaching_staff', 'non-teaching staff' => 'non_teaching_staff'] as $role => $expected) {
            $user = $this->upsertFromIdp(['id' => 'option-' . $role, 'email' => str_replace(' ', '-', $role) . '@example.test', 'roles' => $role]);
            $allowed = [$expected];
            $this->assertSame($allowed, $user->allowedClinicAccountTypes());
            foreach (array_diff(array_keys(User::CLINIC_ACCOUNT_TYPES), $allowed) as $type) {
                try {
                    $this->saveClinicType($user, $type);
                    $this->fail('A disabled account type was accepted.');
                } catch (ValidationException $exception) {
                    $this->assertArrayHasKey('clinic_account_type', $exception->errors());
                }
                $this->assertNull($user->fresh()->clinic_account_type);
            }
            $this->saveClinicType($user, $allowed[0]);
            $this->assertSame(User::ROLE_STUDENT, $user->fresh()->user_role);
        }
        $this->assertDatabaseCount('admins', 0);
    }

    public function test_blank_idp_role_opens_all_options_without_directory_requests(): void
    {
        $user = $this->upsertFromIdp(['id' => 'blank-options', 'email' => 'blank-options@example.test', 'roles' => '']);
        $this->mock(\App\Services\FacultySyncService::class)->shouldNotReceive('fetchFaculties');
        Config::set('services.idp.enabled', false);
        $this->actingAs($user, 'student')->getJson('/student/account-type/options')
            ->assertOk()->assertJson(['allowed_types' => array_keys(User::CLINIC_ACCOUNT_TYPES)]);
        $this->postJson('/student/account-type', ['clinic_account_type' => 'faculty'])
            ->assertOk()->assertJson(['redirect' => route('health.form.employee')]);
        $this->assertSame('faculty', $user->fresh()->clinic_account_type);
        $this->assertSame('student', $user->fresh()->user_role);
    }

    public function test_unknown_and_privileged_idp_roles_open_choices_but_never_grant_local_access(): void
    {
        foreach (['superadmin', 'unknown'] as $role) {
            $user = $this->upsertFromIdp(['id' => $role . '-options', 'email' => $role . '-options@example.test', 'roles' => $role]);
            $this->assertSame(array_keys(User::CLINIC_ACCOUNT_TYPES), $user->allowedClinicAccountTypes());
            $this->saveClinicType($user, 'non_teaching_staff');
            $this->assertSame('student', $user->fresh()->user_role);
            $this->assertFalse($this->canAccessAdminRoutes($user->fresh()));
        }
    }

    private function saveClinicType(User $user, string $type, array $extra = [])
    {
        $request = \Illuminate\Http\Request::create('/student/account-type', 'POST', ['clinic_account_type' => $type] + $extra);
        $request->headers->set('Accept', 'application/json');
        $request->setUserResolver(fn () => $user);
        return (new \App\Http\Controllers\ClinicAccountTypeController())->store($request);
    }

    private function checkFormRoute(User $user, string $route)
    {
        $request = \Illuminate\Http\Request::create(route($route), 'GET');
        $request->setUserResolver(fn () => $user);
        $request->setRouteResolver(fn () => \Illuminate\Support\Facades\Route::getRoutes()->getByName($route));
        return (new \App\Http\Middleware\EnsureClinicAccountType())->handle($request, fn () => response('', 204));
    }

    /** @dataProvider callbackConnectionFailureStages */
    public function test_callback_connection_failures_return_a_login_error_without_changing_the_account(string $stage): void
    {
        $identity = ['id' => 'faculty-timeout', 'email' => 'faculty-timeout@example.test'];
        $user = $this->upsertFromIdp($identity + ['roles' => 'faculty']);
        $original = $user->fresh()->getAttributes();
        Config::set('services.idp.enabled', true);
        Config::set('services.idp.base_url', 'https://idp.example.test');
        Config::set('services.idp.client_id', 'test-client');
        Config::set('services.idp.client_secret', 'test-secret');
        Config::set('services.idp.token_path', '/token');
        Config::set('services.idp.profile_paths', ['/me', '/userinfo']);
        $attempts = [];
        Http::fake(function ($request) use ($stage, $identity, &$attempts) {
            $attempts[] = $request->url();
            if ($stage === 'token_exchange' || $request->url() === 'https://idp.example.test/me') {
                throw new \Illuminate\Http\Client\ConnectionException('Simulated connection timeout');
            }

            return Http::response([
                'access_token' => 'test-token',
                'user' => $identity + ['roles' => 'faculty'],
            ]);
        });

        $request = \Illuminate\Http\Request::create('/auth/callback', 'GET', ['code' => 'test-code']);
        $request->setLaravelSession($this->app['session.store']);
        $request->session()->put('idp_pkce_verifier', 'test-verifier');
        $response = (new LoginController())->handleIdpCallback($request);

        $this->assertSame(302, $response->getStatusCode());
        $this->assertSame(url('/login?idp_error=1'), $response->getTargetUrl());
        $this->assertTrue(session('errors')->has('idp'));
        $this->assertFalse($request->session()->has('idp_pkce_verifier'));
        $this->assertGuest('student');
        $this->assertGuest('admin');
        $this->assertSame($original, $user->fresh()->getAttributes());
        $this->assertDatabaseCount('users', 1);
        $this->assertSame($stage === 'token_exchange'
            ? ['https://idp.example.test/token']
            : ['https://idp.example.test/token', 'https://idp.example.test/me'], $attempts);
    }

    public static function callbackConnectionFailureStages(): array
    {
        return [['token_exchange'], ['profile_fetch']];
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
        $this->assertSame('unselected', $updated->clinicHealthFormAudience());
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
