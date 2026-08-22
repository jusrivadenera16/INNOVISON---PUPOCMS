<?php

namespace Tests\Feature;

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class SyncAdminHubFacultyUuidsTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        Config::set('database.default', 'sqlite');
        Config::set('database.connections.sqlite.database', ':memory:');
        DB::purge('sqlite');
        DB::reconnect('sqlite');

        Schema::create('admin_hub', function (Blueprint $table) {
            $table->id();
            $table->string('admin_uuid')->nullable()->unique();
            $table->string('employee_number')->nullable()->index();
            $table->string('name')->nullable();
            $table->string('email')->nullable()->index();
            $table->string('role', 50)->default('admin_designee');
            $table->string('status', 30)->default('active');
            $table->timestamps();
        });

        Config::set('services.pupt_flss.faculty_profiles_url', 'https://faculty.example.test/api/faculties');
        Config::set('services.pupt_flss.secret_key', 'faculty-test-secret');
        Config::set('services.pupt_flss.timeout', 3);
    }

    protected function tearDown(): void
    {
        Schema::dropIfExists('admin_hub');

        parent::tearDown();
    }

    public function test_it_backfills_a_missing_admin_hub_uuid_from_faculty_uuid_without_changing_profile_access(): void
    {
        $adminHubId = DB::table('admin_hub')->insertGetId([
            'employee_number' => 'FA-0001',
            'name' => 'Faculty Designee',
            'email' => 'faculty.designee@example.test',
            'role' => 'admin_designee',
            'status' => 'active',
            'created_at' => now(),
            'updated_at' => now(),
        ]);
        $facultyUuid = '59fc14e5-0e5e-4c5f-8672-6b82491f89c1';

        Http::fake([
            'https://faculty.example.test/api/faculties' => Http::response([
                'faculties' => [[
                    'faculty_uuid' => $facultyUuid,
                    'faculty_code' => 'FA-0001',
                    'email' => 'faculty.designee@example.test',
                ]],
            ]),
        ]);

        $this->artisan('admin-hub:sync-faculty-uuids --dry-run')
            ->assertExitCode(0);

        $this->assertDatabaseHas('admin_hub', [
            'id' => $adminHubId,
            'admin_uuid' => null,
            'role' => 'admin_designee',
            'status' => 'active',
        ]);

        $this->artisan('admin-hub:sync-faculty-uuids')
            ->assertExitCode(0);

        $this->assertDatabaseHas('admin_hub', [
            'id' => $adminHubId,
            'admin_uuid' => $facultyUuid,
            'employee_number' => 'FA-0001',
            'role' => 'admin_designee',
            'status' => 'active',
        ]);
    }
}
