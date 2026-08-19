<?php

namespace Tests\Feature;

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class AdminHubIdentifierMigrationTest extends TestCase
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
            $table->uuid('student_id')->nullable();
            $table->string('employee_number')->nullable();
            $table->string('email')->nullable();
            $table->date('DOB')->nullable();
            $table->string('gender')->nullable();
        });

        Schema::create('admins', function (Blueprint $table) {
            $table->id('admin_id');
            $table->unsignedBigInteger('user_id')->nullable();
            $table->string('email')->nullable();
            $table->string('external_identifier')->nullable();
            $table->date('birthday')->nullable();
            $table->unsignedInteger('age')->nullable();
            $table->string('gender')->nullable();
            $table->string('civil_status')->nullable();
            $table->text('address')->nullable();
            $table->string('emergency_contact_person')->nullable();
            $table->string('emergency_contact_no')->nullable();
            $table->string('office')->nullable();
            $table->string('access_level')->nullable();
            $table->timestamps();
        });

        Schema::create('health_profile_emp', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id')->nullable();
            $table->string('employee_number')->nullable();
            $table->string('home_address')->nullable();
            $table->string('emergency_contact_person')->nullable();
            $table->string('emergency_contact_no')->nullable();
            $table->date('birthday')->nullable();
            $table->unsignedInteger('age')->nullable();
            $table->string('sex')->nullable();
            $table->string('civil_status')->nullable();
        });

        Schema::create('admin_hub', function (Blueprint $table) {
            $table->id();
            $table->string('admin_uuid')->nullable()->unique();
            $table->unsignedBigInteger('user_id')->nullable();
            $table->string('name')->nullable();
            $table->string('email')->nullable();
            $table->string('office')->nullable();
            $table->string('role')->default('admin_designee');
            $table->string('status')->default('active');
            $table->timestamps();
        });
    }

    public function test_it_separates_idp_uuid_and_employee_number_without_losing_profile_data(): void
    {
        $userId = DB::table('users')->insertGetId([
            'student_id' => '6104038d-6449-4211-a248-318e1bbb452b',
            'email' => 'designee@example.test',
            'DOB' => '1990-05-20',
            'gender' => 'Female',
        ]);

        DB::table('admins')->insert([
            'user_id' => $userId,
            'email' => 'designee@example.test',
            'external_identifier' => 'FA001TG2023',
            'civil_status' => 'Married',
            'address' => 'Taguig City',
            'emergency_contact_person' => 'Emergency Person',
            'emergency_contact_no' => '09123456789',
            'access_level' => 'designee',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $hubId = DB::table('admin_hub')->insertGetId([
            'admin_uuid' => 'FA001TG2023',
            'user_id' => $userId,
            'name' => 'Faculty Designee',
            'email' => 'designee@example.test',
            'role' => 'admin_designee',
            'status' => 'active',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $migration = require database_path('migrations/2026_08_19_000000_expand_admin_hub_and_normalize_employee_identifiers.php');
        $migration->up();

        $this->assertDatabaseHas('admin_hub', [
            'id' => $hubId,
            'admin_uuid' => '6104038d-6449-4211-a248-318e1bbb452b',
            'employee_number' => 'FA001TG2023',
            'birthday' => '1990-05-20',
            'gender' => 'Female',
            'civil_status' => 'Married',
            'address' => 'Taguig City',
            'emergency_contact_person' => 'Emergency Person',
            'emergency_contact_no' => '09123456789',
            'access_level' => 'designee',
        ]);
        $this->assertDatabaseHas('admins', [
            'user_id' => $userId,
            'employee_number' => 'FA001TG2023',
        ]);
        $this->assertTrue(Schema::hasColumn('admins', 'employee_number'));
        $this->assertFalse(Schema::hasColumn('admins', 'external_identifier'));
    }
}
