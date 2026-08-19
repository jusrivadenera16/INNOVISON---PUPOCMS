<?php

namespace Tests\Feature;

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\URL;
use Tests\TestCase;

class AdminProfileApiTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        Config::set('database.default', 'sqlite');
        Config::set('database.connections.sqlite.database', ':memory:');
        Config::set('app.url', 'http://localhost');
        URL::forceRootUrl('http://localhost');
        DB::purge('sqlite');
        DB::reconnect('sqlite');

        Schema::dropIfExists('admin_hub');
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
            $table->string('role', 50)->default('admin_designee');
            $table->string('status', 30)->default('active');
            $table->timestamps();
        });

        Config::set('services.external_admin_profile.api_key', 'shared-secret');
        Config::set('services.external_admin_profile.header', 'X-External-Api-Key');
        Config::set('services.external_admin_profile.system_keys', []);
    }

    protected function tearDown(): void
    {
        Schema::dropIfExists('admin_hub');
        parent::tearDown();
    }

    public function test_it_requires_the_external_api_key(): void
    {
        $response = $this->getJson('/api/external/admin-profile?admin_uuid=idp-admin-001');

        $response->assertUnauthorized();
    }

    public function test_it_returns_the_admin_hub_profile_by_admin_uuid(): void
    {
        DB::table('admin_hub')->insert([
            'admin_uuid' => 'idp-admin-001',
            'employee_number' => 'FA001TG2023',
            'first_name' => 'Ada',
            'middle_name' => 'Byron',
            'last_name' => 'Lovelace',
            'name' => 'Ada Lovelace',
            'email' => 'admin@example.com',
            'office' => 'Clinic Office',
            'role' => 'admin_designee',
            'status' => 'active',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $response = $this
            ->withHeader('X-External-Api-Key', 'shared-secret')
            ->getJson('/api/external/admin-profile?admin_uuid=idp-admin-001');

        $response
            ->assertOk()
            ->assertJson([
                'data' => [
                    'admin_uuid' => 'idp-admin-001',
                    'employee_number' => 'FA001TG2023',
                    'email' => 'admin@example.com',
                    'first_name' => 'Ada',
                    'middle_name' => 'Byron',
                    'last_name' => 'Lovelace',
                    'name' => 'Ada Lovelace',
                    'office' => 'Clinic Office',
                    'role' => 'admin_designee',
                    'status' => 'active',
                ],
            ]);

        $this->assertArrayNotHasKey('id', $response->json('data'));
        $this->assertArrayNotHasKey('admin_id', $response->json('data'));
        $this->assertArrayNotHasKey('user_id', $response->json('data'));
    }

    public function test_it_lists_only_admin_hub_profiles(): void
    {
        DB::table('admin_hub')->insert([
            [
                'admin_uuid' => 'idp-admin-002',
                'name' => 'Grace Hopper',
                'email' => 'grace@example.com',
                'role' => 'admin_designee',
                'status' => 'active',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'admin_uuid' => 'idp-admin-003',
                'name' => 'Katherine Johnson',
                'email' => 'katherine@example.com',
                'role' => 'admin_designee',
                'status' => 'inactive',
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);

        $this->withHeader('X-External-Api-Key', 'shared-secret')
            ->getJson('/api/external/admins?status=active')
            ->assertOk()
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.admin_uuid', 'idp-admin-002');
    }

    public function test_it_updates_an_admin_hub_profile_by_admin_uuid(): void
    {
        DB::table('admin_hub')->insert([
            'admin_uuid' => 'idp-admin-004',
            'name' => 'Old Name',
            'email' => 'old@example.com',
            'role' => 'admin_designee',
            'status' => 'active',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $this->withHeader('X-External-Api-Key', 'shared-secret')
            ->putJson('/api/external/admins/idp-admin-004', [
                'first_name' => 'Updated',
                'last_name' => 'Admin',
                'email' => 'updated@example.com',
                'office' => 'Medical Services',
                'employee_number' => 'FA004TG2019',
                'birthday' => '1987-03-14',
                'age' => 39,
                'gender' => 'Female',
                'civil_status' => 'Married',
                'address' => 'Taguig City',
                'emergency_contact_person' => 'Grace Admin',
                'emergency_contact_no' => '09123456789',
                'access_level' => 'designee',
                'role' => 'designee',
                'status' => 'inactive',
            ])
            ->assertOk()
            ->assertJsonPath('data.name', 'Updated Admin')
            ->assertJsonPath('data.employee_number', 'FA004TG2019')
            ->assertJsonPath('data.access_level', 'designee')
            ->assertJsonPath('data.role', 'admin_designee')
            ->assertJsonPath('data.status', 'inactive');

        $this->assertDatabaseHas('admin_hub', [
            'admin_uuid' => 'idp-admin-004',
            'email' => 'updated@example.com',
            'employee_number' => 'FA004TG2019',
            'office' => 'Medical Services',
            'status' => 'inactive',
        ]);
    }

    public function test_it_rejects_arbitrary_admin_hub_roles(): void
    {
        DB::table('admin_hub')->insert([
            'admin_uuid' => 'idp-admin-005',
            'name' => 'Role Test',
            'email' => 'role@example.com',
            'role' => 'admin_designee',
            'status' => 'active',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $this->withHeader('X-External-Api-Key', 'shared-secret')
            ->putJson('/api/external/admins/idp-admin-005', [
                'role' => 'superadmin',
            ])
            ->assertUnprocessable();
    }
}
