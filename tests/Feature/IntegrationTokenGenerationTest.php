<?php

namespace Tests\Feature;

use App\Models\IntegrationClient;
use App\Models\User;
use App\Http\Middleware\AuthenticateExternalApiRequest;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class IntegrationTokenGenerationTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        config()->set('database.default', 'sqlite');
        config()->set('database.connections.sqlite', [
            'driver' => 'sqlite',
            'database' => ':memory:',
            'prefix' => '',
            'foreign_key_constraints' => false,
        ]);
        DB::purge('sqlite');
        DB::setDefaultConnection('sqlite');
        config()->set('app.system_developer_email', 'developer@example.test');
        Auth::shouldUse('admin');

        Schema::create('users', function (Blueprint $table): void {
            $table->id();
            $table->string('name')->nullable();
            $table->string('first_name')->nullable();
            $table->string('middle_name')->nullable();
            $table->string('last_name')->nullable();
            $table->string('email')->nullable();
            $table->string('user_role')->nullable();
            $table->string('status')->default('active');
            $table->rememberToken();
            $table->timestamps();
        });

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
            $table->timestamps();
        });

        Schema::create('integration_request_logs', function (Blueprint $table): void {
            $table->id();
            $table->unsignedBigInteger('integration_client_id')->nullable();
            $table->unsignedBigInteger('token_id')->nullable();
            $table->string('system_key');
            $table->string('system_name')->nullable();
            $table->string('auth_method')->nullable();
            $table->string('http_method', 12);
            $table->string('endpoint');
            $table->unsignedSmallInteger('status_code')->nullable();
            $table->integer('response_time_ms')->nullable();
            $table->text('error_message')->nullable();
            $table->string('ip_address')->nullable();
            $table->string('user_agent')->nullable();
            $table->timestamp('created_at')->useCurrent();
        });

        Schema::create('api_error_logs', function (Blueprint $table): void {
            $table->id();
            $table->string('system_name');
            $table->string('endpoint')->nullable();
            $table->text('error_message');
            $table->string('http_status')->nullable();
            $table->string('error_type')->nullable();
            $table->integer('response_time_ms')->nullable();
            $table->string('ip_address')->nullable();
            $table->string('user_agent')->nullable();
            $table->timestamp('created_at')->useCurrent();
        });

        $migration = require database_path('migrations/2026_10_23_130000_add_expires_at_to_personal_access_tokens_table.php');
        $migration->up();

        $this->actingAs(User::forceCreate([
            'name' => 'System Developer',
            'email' => 'developer@example.test',
            'user_role' => User::ROLE_SUPERADMIN,
            'status' => 'active',
        ]), 'admin')->withoutMiddleware();
    }

    public function test_generation_works_with_migrated_sanctum_schema_and_rotation_replaces_the_old_token(): void
    {
        $client = IntegrationClient::create([
            'system_key' => 'ims',
            'system_name' => 'IMS',
            'is_active' => true,
        ]);

        $this->assertTrue(Schema::hasColumn('personal_access_tokens', 'expires_at'));

        $generated = $this->postJson(route('admin.integration-tokens.generate'), [
            'client_id' => $client->id,
        ])->assertOk()
            ->assertJsonPath('success', true);

        $oldTokenId = $generated->json('token_id');
        $this->assertDatabaseHas('personal_access_tokens', [
            'id' => $oldTokenId,
            'tokenable_id' => $client->id,
            'expires_at' => null,
        ]);

        $this->postJson(route('admin.integration-tokens.generate'), [
            'client_id' => $client->id,
        ])->assertStatus(409)
            ->assertJsonPath('success', false);

        $rotated = $this->postJson(route('admin.integration-tokens.generate'), [
            'client_id' => $client->id,
            'rotate' => true,
        ])->assertOk()
            ->assertJsonPath('success', true)
            ->assertJsonPath('message', 'Token rotated successfully');

        $newTokenId = $rotated->json('token_id');
        $this->assertNotSame($oldTokenId, $newTokenId);
        $this->assertDatabaseMissing('personal_access_tokens', ['id' => $oldTokenId]);
        $this->assertDatabaseHas('personal_access_tokens', ['id' => $newTokenId, 'tokenable_id' => $client->id]);
        $this->assertDatabaseCount('personal_access_tokens', 1);
    }

    public function test_client_is_connected_only_after_its_token_has_been_used(): void
    {
        $unusedToken = (object) ['last_used_at' => null];
        $client = new IntegrationClient(['is_active' => true]);
        $client->setRelation('tokens', collect([$unusedToken]));

        $this->assertFalse($client->hasUsedToken());

        $unusedToken->last_used_at = now();
        $this->assertTrue($client->hasUsedToken());

        $client->is_active = false;
        $this->assertFalse($client->hasUsedToken());
    }

    public function test_missing_and_invalid_api_credentials_are_logged_against_the_requested_client(): void
    {
        $client = IntegrationClient::create([
            'system_key' => 'ims',
            'system_name' => 'IMS',
            'is_active' => true,
        ]);
        config()->set('services.external_admin_profile.system_keys', ['ims' => 'valid-secret']);
        config()->set('services.external_admin_profile.api_key', '');

        $middleware = new AuthenticateExternalApiRequest();
        $invalidRequest = Request::create('/api/external/admin-profile', 'GET');
        $invalidRequest->headers->set('X-External-System', 'ims');
        $invalidRequest->headers->set('X-External-Api-Key', 'wrong-secret');
        $invalidResponse = $middleware->handle($invalidRequest, fn () => response()->json([]), 'external-admin:read');

        $this->assertSame(403, $invalidResponse->getStatusCode());

        $missingRequest = Request::create('/api/external/admin-profile', 'GET');
        $missingRequest->headers->set('X-External-System', 'ims');
        $missingResponse = $middleware->handle($missingRequest, fn () => response()->json([]), 'external-admin:read');

        $this->assertSame(401, $missingResponse->getStatusCode());
        $this->assertDatabaseCount('integration_request_logs', 2);
        $this->assertDatabaseHas('integration_request_logs', [
            'integration_client_id' => $client->id,
            'system_key' => 'ims',
            'status_code' => 403,
            'auth_method' => 'credential-rejected',
            'error_message' => 'Forbidden.',
        ]);
        $this->assertDatabaseHas('integration_request_logs', [
            'integration_client_id' => $client->id,
            'system_key' => 'ims',
            'status_code' => 401,
            'auth_method' => 'authentication-missing',
        ]);
    }

    public function test_activity_query_combines_tokens_api_requests_and_external_errors(): void
    {
        $client = IntegrationClient::create([
            'system_key' => 'ims',
            'system_name' => 'IMS',
            'is_active' => true,
        ]);
        $client->createToken('integration-test');

        DB::table('integration_request_logs')->insert([
            'integration_client_id' => $client->id,
            'system_key' => 'ims',
            'system_name' => 'IMS',
            'auth_method' => 'credential-rejected',
            'http_method' => 'GET',
            'endpoint' => '/api/external/admin-profile',
            'status_code' => 403,
            'error_message' => 'Forbidden.',
            'created_at' => now(),
        ]);
        DB::table('api_error_logs')->insert([
            'system_name' => 'IMS',
            'endpoint' => '/profiles',
            'error_message' => 'Remote service unavailable.',
            'http_status' => '503',
            'error_type' => 'connection',
            'created_at' => now(),
        ]);

        $method = new \ReflectionMethod(\App\Http\Controllers\AdminController::class, 'integrationActivityQuery');
        $method->setAccessible(true);
        $activities = $method->invoke(new \App\Http\Controllers\AdminController())->get();

        $this->assertCount(3, $activities);
        $this->assertEqualsCanonicalizing(['error', 'error', 'token'], $activities->pluck('event_type')->all());
        $this->assertEqualsCanonicalizing(
            ['External API error', 'API access denied', 'Token generated'],
            $activities->pluck('title')->all()
        );
    }
}
