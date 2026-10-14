<?php

namespace Tests\Feature;

use App\Http\Controllers\AdminController;
use App\Models\HealthProfile;
use App\Support\HealthRecordIdentityResolver;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use ReflectionMethod;
use Tests\TestCase;

class HealthRecordEmployeeIdentityTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        config(['database.connections.health_record_identity_test' => array_merge(
            config('database.connections.sqlite'),
            ['database' => ':memory:']
        )]);
        config(['database.default' => 'health_record_identity_test']);
        DB::purge('health_record_identity_test');
        DB::reconnect('health_record_identity_test');
        DB::connection()->getPdo()->sqliteCreateFunction(
            'regexp',
            fn ($pattern, $value) => @preg_match('/' . $pattern . '/i', (string) $value) ?: 0,
            2
        );

        Schema::create('users', function (Blueprint $table) {
            $table->id();
            $table->string('email')->nullable();
            $table->string('user_type')->nullable();
            $table->string('user_role')->nullable();
            $table->string('idp_role')->nullable();
            $table->string('student_number')->nullable();
            $table->string('status')->default('active');
        });
        Schema::create('admin_hub', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id')->nullable();
            $table->string('email')->nullable();
            $table->string('employee_number')->nullable();
            $table->string('role')->nullable();
            $table->string('status')->default('active');
        });
        Schema::create('health_profiles', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id')->nullable();
            $table->string('student_number')->nullable();
            $table->string('reference_number')->nullable();
            $table->string('clearance_status')->nullable();
        });
    }

    protected function tearDown(): void
    {
        Schema::dropIfExists('health_profiles');
        Schema::dropIfExists('admin_hub');
        Schema::dropIfExists('users');

        parent::tearDown();
    }

    public function test_email_linked_admin_hub_profile_controls_health_record_type_and_employee_number(): void
    {
        $userId = DB::table('users')->insertGetId([
            'email' => 'admin@example.test',
            'user_type' => 'Applicant',
            'user_role' => 'student',
            'idp_role' => 'applicant',
            'status' => 'active',
        ]);
        DB::table('admin_hub')->insert([
            'email' => 'admin@example.test',
            'employee_number' => '06002',
            'role' => 'admin_designee',
            'status' => 'active',
        ]);
        $profileId = DB::table('health_profiles')->insertGetId([
            'user_id' => $userId,
            'reference_number' => '15041',
        ]);

        $record = HealthProfile::with('user.adminHubProfile')->findOrFail($profileId);
        $record->user->setRelation('employeeHealthProfile', null);
        $record->user->setRelation('adminProfile', null);
        $controller = new AdminController();
        $attach = new ReflectionMethod($controller, 'attachAdminHubProfilesByEmail');
        $attach->setAccessible(true);
        $attach->invoke($controller, collect([$record]));

        $this->assertSame('Admin', HealthRecordIdentityResolver::userType($record));
        $this->assertSame('06002', HealthRecordIdentityResolver::employeeNumber($record));
    }

    public function test_health_record_filters_exclude_staff_from_applicants_and_include_email_linked_admins(): void
    {
        $adminUserId = DB::table('users')->insertGetId([
            'email' => 'hub-admin@example.test',
            'user_type' => 'Applicant',
            'user_role' => 'student',
            'idp_role' => 'applicant',
            'status' => 'active',
        ]);
        DB::table('admin_hub')->insert([
            'email' => 'hub-admin@example.test',
            'employee_number' => '15041',
            'role' => 'admin_designee',
            'status' => 'active',
        ]);
        $adminProfileId = DB::table('health_profiles')->insertGetId([
            'user_id' => $adminUserId,
            'reference_number' => '15041',
        ]);

        $facultyUserId = DB::table('users')->insertGetId([
            'email' => 'faculty@example.test',
            'user_type' => 'Applicant',
            'user_role' => 'student',
            'idp_role' => 'faculty',
            'status' => 'active',
        ]);
        $facultyProfileId = DB::table('health_profiles')->insertGetId([
            'user_id' => $facultyUserId,
            'reference_number' => '15042',
        ]);

        $controller = new AdminController();
        $filter = new ReflectionMethod($controller, 'applyHealthProfileUserTypeFilter');
        $filter->setAccessible(true);

        $adminQuery = HealthProfile::query();
        $filter->invoke($controller, $adminQuery, 'admin');
        $this->assertSame([$adminProfileId], $adminQuery->pluck('id')->all());

        $facultyQuery = HealthProfile::query();
        $filter->invoke($controller, $facultyQuery, 'faculty');
        $this->assertSame([$facultyProfileId], $facultyQuery->pluck('id')->all());

        $applicantQuery = HealthProfile::query();
        $filter->invoke($controller, $applicantQuery, 'applicant');
        $this->assertSame([], $applicantQuery->pluck('id')->all());
    }
}
