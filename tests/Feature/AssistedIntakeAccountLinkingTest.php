<?php

namespace Tests\Feature;

use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\WalkInController;
use App\Models\User;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use ReflectionMethod;
use Tests\TestCase;

class AssistedIntakeAccountLinkingTest extends TestCase
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
            $table->string('first_name')->nullable();
            $table->string('middle_name')->nullable();
            $table->string('last_name')->nullable();
            $table->string('name');
            $table->string('email')->unique();
            $table->string('user_role')->default('student');
            $table->string('idp_role')->nullable();
            $table->string('user_type')->nullable();
            $table->string('student_type')->nullable();
            $table->string('status')->default('active');
            $table->string('password');
            $table->rememberToken();
            $table->timestamps();
        });

        Schema::create('appointments', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id');
            $table->string('student_id')->nullable();
            $table->string('student_number')->nullable();
            $table->string('name')->nullable();
            $table->string('email')->nullable();
        });

        Schema::create('consultations', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id');
            $table->string('name')->nullable();
        });

        Schema::create('health_profiles', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id');
        });
    }

    public function test_idp_login_links_assisted_appointments_to_the_matched_user_without_creating_a_health_profile(): void
    {
        $assistedUserId = DB::table('users')->insertGetId([
            'student_id' => 'assisted-2026-00001-tg-0',
            'student_number' => '2026-00001-TG-0',
            'first_name' => 'Temporary',
            'last_name' => 'Patient',
            'name' => 'Temporary Patient',
            'email' => 'patient@example.test',
            'user_role' => 'student',
            'user_type' => 'Student',
            'status' => 'active',
            'password' => 'temporary-hash',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        DB::table('appointments')->insert([
            'user_id' => $assistedUserId,
            'student_id' => 'assisted-2026-00001-tg-0',
            'student_number' => '2026-00001-TG-0',
            'name' => 'Temporary Patient',
            'email' => 'patient@example.test',
        ]);
        DB::table('consultations')->insert([
            'user_id' => $assistedUserId,
            'name' => 'Temporary Patient',
        ]);

        $method = new ReflectionMethod(LoginController::class, 'upsertLocalUserFromIdpProfile');
        $method->setAccessible(true);
        $account = $method->invoke(new LoginController(), [
            'id' => 'idp-student-001',
            'email' => 'patient@example.test',
            'student_number' => '2026-00001-TG-0',
            'first_name' => 'Jamie',
            'last_name' => 'Patient',
            'roles' => 'student',
        ]);

        $this->assertSame($assistedUserId, $account->id);
        $this->assertSame('idp-student-001', $account->student_id);
        $this->assertDatabaseHas('appointments', [
            'user_id' => $account->id,
            'student_id' => 'idp-student-001',
            'student_number' => '2026-00001-TG-0',
            'name' => 'Jamie Patient',
            'email' => 'patient@example.test',
        ]);
        $this->assertDatabaseHas('consultations', [
            'user_id' => $account->id,
            'name' => 'Jamie Patient',
        ]);
        $this->assertDatabaseCount('health_profiles', 0);
    }

    public function test_external_patient_lookup_keeps_an_assisted_identity_temporary_until_idp_login(): void
    {
        $assistedUserId = DB::table('users')->insertGetId([
            'student_id' => 'assisted-2026-00002-tg-0',
            'student_number' => '2026-00002-TG-0',
            'first_name' => 'Temporary',
            'last_name' => 'Patient',
            'name' => 'Temporary Patient',
            'email' => 'second-patient@example.test',
            'user_role' => 'student',
            'user_type' => 'Student',
            'status' => 'active',
            'password' => 'temporary-hash',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $method = new ReflectionMethod(WalkInController::class, 'resolveLocalUserFromApplicant');
        $method->setAccessible(true);
        $resolvedUser = $method->invoke(new WalkInController(), [
            'idp_user_id' => 'idp-student-002',
            'student_number' => '2026-00002-TG-0',
            'email' => 'second-patient@example.test',
            'first_name' => 'Jamie',
            'last_name' => 'Patient',
        ], true, '2026-2222-2222');

        $this->assertSame($assistedUserId, $resolvedUser->id);
        $this->assertSame('assisted-2026-00002-tg-0', $resolvedUser->student_id);
        $this->assertDatabaseCount('health_profiles', 0);
    }
}
