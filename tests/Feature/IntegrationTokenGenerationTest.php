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
            ->assertJsonPath('success', true)
            ->assertHeader('Cache-Control', 'no-store, private');

        $this->assertNotSame(
            $generated->json('token'),
            DB::table('personal_access_tokens')->where('id', $generated->json('token_id'))->value('token')
        );

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
            ->assertJsonPath('message', 'Token rotated successfully')
            ->assertHeader('Cache-Control', 'no-store, private');

        $newTokenId = $rotated->json('token_id');
        $this->assertNotSame($oldTokenId, $newTokenId);
        $this->assertDatabaseMissing('personal_access_tokens', ['id' => $oldTokenId]);
        $this->assertDatabaseHas('personal_access_tokens', ['id' => $newTokenId, 'tokenable_id' => $client->id]);
        $this->assertDatabaseCount('personal_access_tokens', 1);
    }

    public function test_admin_hub_dashboard_does_not_accept_or_list_legacy_static_key_systems(): void
    {
        config()->set('services.external_admin_profile.system_keys', [
            'legacy' => 'short-key',
        ]);

        $method = new \ReflectionMethod(\App\Http\Controllers\AdminController::class, 'resolveExternalApiTestingSystemMeta');
        [$isValid, $meta, $error] = $method->invoke(new \App\Http\Controllers\AdminController(), 'legacy');

        $this->assertFalse($isValid);
        $this->assertNull($meta);
        $this->assertStringContainsString('static keys are not accepted', $error);
        $systemsMethod = new \ReflectionMethod(\App\Http\Controllers\AdminController::class, 'externalApiTestingSystems');
        $this->assertSame([], $systemsMethod->invoke(new \App\Http\Controllers\AdminController()));
    }

    public function test_a_token_generated_in_developer_tools_authenticates_admin_hub_and_revocation_removes_access(): void
    {
        $client = IntegrationClient::create([
            'system_key' => 'ims', 'system_name' => 'IMS', 'is_active' => true,
        ]);
        $generated = $this->postJson(route('admin.integration-tokens.generate'), [
            'client_id' => $client->id, 'abilities' => ['external-admin:read', 'external-admin:update'],
        ])->assertOk();
        $middleware = new \App\Http\Middleware\AuthenticateAdminHubApiRequest();
        foreach (['external-admin:read', 'external-admin:update'] as $ability) {
            $request = Request::create('/api/external/admins', 'GET');
            $request->headers->set('Authorization', 'Bearer '.$generated->json('token'));
            $response = $middleware->handle($request, fn () => response()->json(['ok' => true]), $ability);
            $this->assertSame(200, $response->getStatusCode());
            $this->assertSame('sanctum', $request->attributes->get('external_api_auth_method'));
        }
        $this->assertDatabaseHas('integration_request_logs', [
            'integration_client_id' => $client->id, 'token_id' => $generated->json('token_id'),
            'auth_method' => 'sanctum', 'status_code' => 200,
        ]);
        $client->tokens()->delete();
        $request = Request::create('/api/external/admins', 'GET');
        $request->headers->set('Authorization', 'Bearer '.$generated->json('token'));
        $response = $middleware->handle($request, fn () => response()->json(['ok' => true]), 'external-admin:read');
        $this->assertSame(403, $response->getStatusCode());
        $this->assertDatabaseHas('integration_request_logs', [
            'auth_method' => 'credential-rejected', 'status_code' => 403,
        ]);
    }

    public function test_admin_hub_dashboard_uses_generated_integration_tokens_not_static_key_configuration(): void
    {
        config()->set('services.external_admin_profile.system_keys', ['legacy' => 'short-key']);
        $client = IntegrationClient::create([
            'system_key' => 'ims', 'system_name' => 'IMS', 'is_active' => true,
        ]);
        $controller = new \App\Http\Controllers\AdminController();
        $method = new \ReflectionMethod($controller, 'resolveExternalApiTestingSystemMeta');
        [$valid, $meta] = $method->invoke($controller, 'ims');
        $this->assertTrue($valid);
        $this->assertSame('Authorization', $meta['header_name']);
        $this->assertSame('sanctum-bearer-token', $meta['auth_mode']);
        $this->assertFalse($controller->apiSystemStatus(Request::create('/'))->getData(true)['external_admin']['configured']);
        $client->createToken('admin-hub', ['external-admin:read']);
        $status = $controller->apiSystemStatus(Request::create('/'))->getData(true)['external_admin'];
        $this->assertTrue($status['configured']);
        $this->assertSame(['ims'], $status['systems']);
        $this->assertSame('Authorization', $status['header']);
        $this->assertSame('sanctum-bearer-token', $status['auth_mode']);
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

    public function test_delete_client_removes_only_its_tokens_and_preserves_records_and_history(): void
    {
        $client = IntegrationClient::create(['system_key' => 'ims', 'system_name' => 'IMS', 'is_active' => true]);
        $other = IntegrationClient::create(['system_key' => 'ris', 'system_name' => 'RIS', 'is_active' => true]);
        $token = $client->createToken('first', ['external-admin:read'])->plainTextToken;
        $client->createToken('second');
        $otherToken = $other->createToken('other')->accessToken;
        config()->set('services.external_admin_profile.system_keys', ['medical' => 'unchanged-static-key']);
        Schema::create('admin_hub', function (Blueprint $table): void {
            $table->id();
            $table->string('name');
        });
        DB::table('admin_hub')->insert(['name' => 'Keep this admin record']);
        $logId = DB::table('integration_request_logs')->insertGetId([
            'integration_client_id' => $client->id, 'system_key' => 'ims', 'system_name' => 'IMS',
            'http_method' => 'GET', 'endpoint' => '/api/external/admins', 'status_code' => 200, 'created_at' => now(),
        ]);
        DB::table('api_error_logs')->insert(['system_name' => 'IMS', 'error_message' => 'Keep history', 'created_at' => now()]);

        $this->deleteJson(route('admin.integration-clients.destroy', $client->id), ['system_key' => 'ims'])
            ->assertOk()->assertJsonPath('success', true);

        $this->assertDatabaseMissing('integration_clients', ['id' => $client->id]);
        $this->assertSame(0, $client->tokens()->count());
        $this->assertDatabaseHas('integration_clients', ['id' => $other->id]);
        $this->assertDatabaseHas('personal_access_tokens', ['id' => $otherToken->id]);
        $this->assertDatabaseHas('admin_hub', ['name' => 'Keep this admin record']);
        $this->assertDatabaseHas('integration_request_logs', [
            'id' => $logId, 'integration_client_id' => null, 'system_key' => 'ims', 'status_code' => 200,
        ]);
        $this->assertDatabaseCount('api_error_logs', 1);
        $this->assertSame(['medical' => 'unchanged-static-key'], config('services.external_admin_profile.system_keys'));
        $request = Request::create('/api/external/admins');
        $request->headers->set('Authorization', 'Bearer '.$token);
        $response = (new \App\Http\Middleware\AuthenticateAdminHubApiRequest())
            ->handle($request, fn () => response()->json([]), 'external-admin:read');
        $this->assertSame(403, $response->getStatusCode());
    }

    public function test_client_without_tokens_can_be_deleted(): void
    {
        $client = IntegrationClient::create(['system_key' => 'unused', 'system_name' => 'Unused', 'is_active' => false]);
        $this->deleteJson(route('admin.integration-clients.destroy', $client->id), ['system_key' => 'unused'])
            ->assertOk()->assertJsonPath('success', true);
        $this->assertDatabaseMissing('integration_clients', ['id' => $client->id]);
    }

    public function test_delete_client_requires_matching_identity_and_handles_an_already_deleted_client(): void
    {
        $client = IntegrationClient::create(['system_key' => 'ims', 'system_name' => 'IMS', 'is_active' => true]);
        $client->createToken('keep');
        $url = route('admin.integration-clients.destroy', $client->id);
        $this->deleteJson($url, [])->assertUnprocessable();
        $this->deleteJson($url, ['system_key' => 'different-client'])->assertUnprocessable();
        $this->assertDatabaseHas('integration_clients', ['id' => $client->id]);
        $this->assertSame(1, $client->tokens()->count());
        $this->deleteJson(route('admin.integration-clients.destroy', 99999), ['system_key' => 'missing'])
            ->assertNotFound();
    }

    public function test_non_developer_cannot_delete_an_integration_client(): void
    {
        $client = IntegrationClient::create(['system_key' => 'ims', 'system_name' => 'IMS', 'is_active' => true]);
        $client->createToken('keep');
        $this->actingAs(User::forceCreate([
            'name' => 'Other Admin', 'email' => 'other@example.test', 'user_role' => User::ROLE_SUPERADMIN, 'status' => 'active',
        ]), 'admin');
        $this->deleteJson(route('admin.integration-clients.destroy', $client->id), ['system_key' => 'ims'])
            ->assertForbidden();
        $this->assertDatabaseHas('integration_clients', ['id' => $client->id]);
        $this->assertSame(1, $client->tokens()->count());
    }

    public function test_delete_client_rolls_back_token_deletion_if_client_deletion_fails(): void
    {
        $client = IntegrationClient::create(['system_key' => 'ims', 'system_name' => 'IMS', 'is_active' => true]);
        $client->createToken('keep');
        DB::unprepared("CREATE TRIGGER fail_client_delete BEFORE DELETE ON integration_clients BEGIN SELECT RAISE(ABORT, 'test deletion failure'); END");
        $this->deleteJson(route('admin.integration-clients.destroy', $client->id), ['system_key' => 'ims'])
            ->assertStatus(500)->assertJsonPath('success', false);
        $this->assertDatabaseHas('integration_clients', ['id' => $client->id]);
        $this->assertSame(1, $client->tokens()->count());
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

    public function test_activity_query_hides_entries_older_than_eight_days_without_deleting_them(): void
    {
        $client = IntegrationClient::create([
            'system_key' => 'ims',
            'system_name' => 'IMS',
            'is_active' => true,
        ]);
        $client->createToken('recent-token');
        $oldToken = $client->createToken('old-token')->accessToken;
        $oldTimestamp = now()->subDays(9);
        DB::table('personal_access_tokens')->where('id', $oldToken->id)->update([
            'created_at' => $oldTimestamp,
        ]);

        foreach ([now(), $oldTimestamp] as $createdAt) {
            DB::table('integration_request_logs')->insert([
                'integration_client_id' => $client->id,
                'system_key' => 'ims',
                'system_name' => 'IMS',
                'http_method' => 'GET',
                'endpoint' => '/api/external/admin-profile',
                'status_code' => 200,
                'created_at' => $createdAt,
            ]);
            DB::table('api_error_logs')->insert([
                'system_name' => 'IMS',
                'endpoint' => '/profiles',
                'error_message' => 'Remote service unavailable.',
                'http_status' => '503',
                'created_at' => $createdAt,
            ]);
        }

        $method = new \ReflectionMethod(\App\Http\Controllers\AdminController::class, 'integrationActivityQuery');
        $method->setAccessible(true);
        $activities = $method->invoke(new \App\Http\Controllers\AdminController())->get();

        $this->assertCount(3, $activities);
        $this->assertSame(2, DB::table('personal_access_tokens')->count());
        $this->assertSame(2, DB::table('integration_request_logs')->count());
        $this->assertSame(2, DB::table('api_error_logs')->count());
    }
}
