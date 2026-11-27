<?php

namespace Tests\Feature;

use App\Models\IntegrationClient;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\URL;
use Tests\TestCase;

class AdminProfileApiTest extends TestCase
{
    private IntegrationClient $integrationClient;
    private string $apiToken;

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

        Schema::create('integration_clients', function (Blueprint $table): void {
            $table->id();
            $table->string('system_key')->unique();
            $table->string('system_name');
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });
        Schema::create('personal_access_tokens', function (Blueprint $table): void {
            $table->id();
            $table->morphs('tokenable');
            $table->string('name');
            $table->string('token', 64)->unique();
            $table->text('abilities')->nullable();
            $table->timestamp('last_used_at')->nullable();
            $table->timestamp('expires_at')->nullable();
            $table->timestamps();
        });
        Schema::create('users', function (Blueprint $table): void {
            $table->id();
            $table->string('student_id')->unique();
            $table->string('student_number')->nullable();
            $table->boolean('is_health_profile_completed')->default(false);
            $table->timestamps();
        });
        $this->integrationClient = IntegrationClient::create([
            'system_key' => 'ims', 'system_name' => 'IMS', 'is_active' => true,
        ]);
        $this->apiToken = $this->integrationClient
            ->createToken('existing-integration', ['external-admin:read', 'external-admin:update'])
            ->plainTextToken;
    }

    protected function tearDown(): void
    {
        Schema::dropIfExists('admin_hub');
        parent::tearDown();
    }

    public function test_it_requires_an_integration_token(): void
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
            ->withToken($this->apiToken)
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

        $this->assertStringContainsString('no-store', (string) $response->headers->get('Cache-Control'));
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

        $this->withToken($this->apiToken)
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

        $this->withToken($this->apiToken)
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

        $this->withToken($this->apiToken)
            ->putJson('/api/external/admins/idp-admin-005', [
                'role' => 'superadmin',
            ])
            ->assertUnprocessable();
    }

    public function test_every_admin_hub_endpoint_rejects_global_and_per_system_static_keys_in_both_headers(): void
    {
        foreach ([false, true] as $perSystem) {
            config()->set('services.external_admin_profile.system_keys', $perSystem ? ['ims' => 'system-secret'] : []);
            $key = $perSystem ? 'system-secret' : 'shared-secret';
            foreach (['Authorization', 'X-External-Api-Key'] as $header) {
                $this->withHeaders([
                    'Authorization' => '', 'X-External-Api-Key' => '', 'X-External-System' => 'ims',
                    $header => $header === 'Authorization' ? 'Bearer '.$key : $key,
                ]);
                foreach ([
                    '/api/external/admins',
                    '/api/external/admins/options',
                    '/api/external/admins/idp-admin-test',
                    '/api/external/admin-profile?email=admin@example.test',
                ] as $endpoint) {
                    $this->getJson($endpoint)->assertForbidden();
                }
                $this->putJson('/api/external/admins/idp-admin-test', ['first_name' => 'Must not change'])
                    ->assertForbidden();
            }
        }
        $this->assertDatabaseCount('personal_access_tokens', 1);
        $this->assertDatabaseCount('admin_hub', 0);
    }

    public function test_existing_tokens_work_for_show_options_and_email_lookup_without_rotation(): void
    {
        DB::table('admin_hub')->insert([
            'admin_uuid' => 'idp-admin-test', 'name' => 'Test Admin', 'email' => 'admin@example.test',
            'role' => 'admin_designee', 'status' => 'active', 'created_at' => now(), 'updated_at' => now(),
        ]);
        $this->withToken($this->apiToken);
        foreach ([
            '/api/external/admins/idp-admin-test',
            '/api/external/admins/options',
            '/api/external/admin-profile?email=admin@example.test',
        ] as $endpoint) {
            $this->getJson($endpoint)->assertOk();
        }
        $this->assertDatabaseCount('personal_access_tokens', 1);
        $this->assertNotNull($this->integrationClient->tokens()->first()->last_used_at);
    }

    public function test_generated_tokens_still_work_in_the_compatible_custom_header(): void
    {
        $this->withHeader('X-External-Api-Key', $this->apiToken)
            ->getJson('/api/external/admins')->assertOk();
    }

    public function test_admin_hub_rejects_invalid_revoked_inactive_and_wrong_system_tokens(): void
    {
        $this->withToken('invalid-token')->getJson('/api/external/admins')->assertForbidden();
        $this->withToken($this->apiToken)->withHeader('X-External-System', 'different-system')
            ->getJson('/api/external/admins')->assertForbidden();
        $this->withHeader('X-External-System', 'ims');
        $this->integrationClient->update(['is_active' => false]);
        $this->getJson('/api/external/admins')->assertForbidden();
        $this->integrationClient->update(['is_active' => true]);
        $this->integrationClient->tokens()->delete();
        $this->getJson('/api/external/admins')->assertForbidden();
    }

    public function test_admin_hub_enforces_read_and_update_abilities(): void
    {
        $readToken = $this->integrationClient->createToken('read-only', ['external-admin:read'])->plainTextToken;
        $this->withToken($readToken)->getJson('/api/external/admins')->assertOk();
        $this->putJson('/api/external/admins/idp-admin-test', [])->assertForbidden();
        $updateToken = $this->integrationClient->createToken('update-only', ['external-admin:update'])->plainTextToken;
        $this->withToken($updateToken)->getJson('/api/external/admins')->assertForbidden();
        $medicalToken = $this->integrationClient->createToken('medical-only', ['medical-status:read'])->plainTextToken;
        $this->withToken($medicalToken)->getJson('/api/external/admins')->assertForbidden();
    }

    public function test_medical_status_keeps_global_and_per_system_static_key_authentication(): void
    {
        $this->seedMedicalStatusStudent();
        foreach ([false, true] as $perSystem) {
            config()->set('services.external_admin_profile.system_keys', $perSystem ? ['ims' => 'system-secret'] : []);
            $key = $perSystem ? 'system-secret' : 'shared-secret';
            foreach (['Authorization', 'X-External-Api-Key'] as $header) {
                foreach (['', 'ims'] as $system) {
                    $this->withHeaders([
                        'Authorization' => '', 'X-External-Api-Key' => '', 'X-External-System' => $system,
                        $header => $header === 'Authorization' ? 'Bearer '.$key : $key,
                    ])->getJson('/api/external/students/student-test/medical-status')
                        ->assertOk()->assertJsonPath('data.is_health_profile_completed', 1);
                }
            }
        }
    }

    public function test_medical_status_keeps_generated_token_permissions_and_rejections(): void
    {
        $this->seedMedicalStatusStudent();
        $medicalToken = $this->integrationClient->createToken('medical', ['medical-status:read'])->plainTextToken;
        $endpoint = '/api/external/students/student-test/medical-status';
        $this->getJson($endpoint)->assertUnauthorized();
        $this->withToken($medicalToken)->getJson($endpoint)->assertOk();
        $this->withToken($this->apiToken)->getJson($endpoint)->assertForbidden();
        $this->withToken('invalid')->getJson($endpoint)->assertForbidden();
        $this->integrationClient->tokens()->delete();
        $this->withToken($medicalToken)->getJson($endpoint)->assertForbidden();
    }

    public function test_medical_status_route_uses_its_original_middleware(): void
    {
        $routes = app('router')->getRoutes();
        $medical = $routes->match(Request::create('/api/external/students/test/medical-status'));
        $this->assertContains('external.api:medical-status:read', $medical->gatherMiddleware());
        $this->assertNotContains('adminhub.api:medical-status:read', $medical->gatherMiddleware());
        $admin = $routes->match(Request::create('/api/external/admins'));
        $this->assertContains('adminhub.api:external-admin:read', $admin->gatherMiddleware());
    }

    private function seedMedicalStatusStudent(): void
    {
        DB::table('users')->insert([
            'student_id' => 'student-test', 'student_number' => '2026-00001-TG-0',
            'is_health_profile_completed' => true, 'created_at' => now(), 'updated_at' => now(),
        ]);
    }
}
