<?php

namespace App\Http\Middleware;

use App\Models\IntegrationClient;
use Closure;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Laravel\Sanctum\PersonalAccessToken;

class AuthenticateExternalApiRequest
{
    public function handle(Request $request, Closure $next, ...$abilities)
    {
        $headerName = trim((string) config(
            'services.external_admin_profile.header',
            'X-External-Api-Key'
        ));

        $systemHeaderName = trim((string) config(
            'services.external_admin_profile.system_header',
            'X-External-System'
        ));

        $providedCredential = trim((string) $request->bearerToken());

        if ($providedCredential === '') {
            $providedCredential = trim((string) $request->header($headerName, ''));
        }

        if ($providedCredential === '') {
            return new JsonResponse([
                'message' => 'Authentication credentials were not provided.',
            ], 401);
        }

        $accessToken = null;
        if (Schema::hasTable('personal_access_tokens') && Schema::hasTable('integration_clients')) {
            $accessToken = PersonalAccessToken::findToken($providedCredential);
        }

        if ($accessToken) {
            $client = $accessToken->tokenable;

            if (!$client instanceof IntegrationClient || !$client->is_active) {
                return new JsonResponse([
                    'message' => 'Forbidden.',
                ], 403);
            }

            $requestedSystem = strtolower(trim((string) $request->header(
                $systemHeaderName,
                $request->query('system', '')
            )));

            if (
                $requestedSystem !== ''
                && $requestedSystem !== strtolower($client->system_key)
            ) {
                return new JsonResponse([
                    'message' => 'The token does not belong to the requested system.',
                ], 403);
            }

            foreach ($abilities as $ability) {
                if ($ability !== '' && $accessToken->cant($ability)) {
                    return new JsonResponse([
                        'message' => 'The token does not have the required ability.',
                    ], 403);
                }
            }

            $accessToken->forceFill([
                'last_used_at' => now(),
            ])->save();

            $request->attributes->set(
                'external_api_system',
                $client->system_key
            );

            $request->attributes->set(
                'external_api_auth_method',
                'sanctum'
            );

            $startedAt = microtime(true);
            $response = $next($request);
            $this->logIntegrationRequest($request, $response, [
                'integration_client_id' => $client->id,
                'token_id' => $accessToken->id,
                'system_key' => $client->system_key,
                'system_name' => $client->system_name,
                'auth_method' => 'sanctum',
                'started_at' => $startedAt,
            ]);

            return $response;
        }

    
        $expectedKey = trim((string) config(
            'services.external_admin_profile.api_key',
            ''
        ));

        $systemKeys = collect(
            config('services.external_admin_profile.system_keys', [])
        )
            ->mapWithKeys(fn ($value, $key) => [
                strtolower(trim((string) $key)) => trim((string) $value),
            ])
            ->filter(fn ($value, $key) => $key !== '' && $value !== '');

        if ($expectedKey === '' && $systemKeys->isEmpty()) {
            return new JsonResponse([
                'message' => 'External API authentication is not configured.',
            ], 403);
        }

        if ($systemKeys->isNotEmpty()) {
            $requestedSystem = strtolower(trim((string) $request->header(
                $systemHeaderName,
                $request->query('system', '')
            )));

            if ($requestedSystem !== '') {
                $expectedSystemKey = $systemKeys->get($requestedSystem);

                if (
                    $expectedSystemKey === null
                    || !hash_equals($expectedSystemKey, $providedCredential)
                ) {
                    return new JsonResponse([
                        'message' => 'Forbidden.',
                    ], 403);
                }

                $request->attributes->set(
                    'external_api_system',
                    $requestedSystem
                );

                $request->attributes->set(
                    'external_api_auth_method',
                    'legacy-static-key'
                );

                $startedAt = microtime(true);
                $response = $next($request);
                $this->logIntegrationRequest($request, $response, [
                    'system_key' => $requestedSystem,
                    'system_name' => $requestedSystem,
                    'auth_method' => 'legacy-static-key',
                    'started_at' => $startedAt,
                ]);

                return $response;
            }

            $matchedSystem = $systemKeys
                ->keys()
                ->first(fn ($system) => hash_equals(
                    (string) $systemKeys->get($system),
                    $providedCredential
                ));

            if ($matchedSystem === null) {
                return new JsonResponse([
                    'message' => 'Forbidden.',
                ], 403);
            }

            $request->attributes->set(
                'external_api_system',
                $matchedSystem
            );

            $request->attributes->set(
                'external_api_auth_method',
                'legacy-static-key'
            );

            $startedAt = microtime(true);
            $response = $next($request);
            $this->logIntegrationRequest($request, $response, [
                'system_key' => $matchedSystem,
                'system_name' => $matchedSystem,
                'auth_method' => 'legacy-static-key',
                'started_at' => $startedAt,
            ]);

            return $response;
        }

        if (!hash_equals($expectedKey, $providedCredential)) {
            return new JsonResponse([
                'message' => 'Forbidden.',
            ], 403);
        }

        $startedAt = microtime(true);
        $response = $next($request);
        $this->logIntegrationRequest($request, $response, [
            'system_key' => 'legacy',
            'system_name' => 'Legacy API Key',
            'auth_method' => 'legacy-static-key',
            'started_at' => $startedAt,
        ]);

        return $response;
    }

    private function logIntegrationRequest(Request $request, $response, array $context): void
    {
        try {
            if (!Schema::hasTable('integration_request_logs')) {
                return;
            }

            $statusCode = method_exists($response, 'getStatusCode')
                ? (int) $response->getStatusCode()
                : null;

            $errorMessage = null;
            if ($statusCode !== null && $statusCode >= 400) {
                $content = method_exists($response, 'getContent') ? (string) $response->getContent() : '';
                $decoded = json_decode($content, true);
                $errorMessage = is_array($decoded)
                    ? (string) ($decoded['message'] ?? $decoded['error'] ?? mb_substr($content, 0, 500))
                    : mb_substr($content, 0, 500);
            }

            DB::table('integration_request_logs')->insert([
                'integration_client_id' => $context['integration_client_id'] ?? null,
                'token_id' => $context['token_id'] ?? null,
                'system_key' => (string) ($context['system_key'] ?? 'unknown'),
                'system_name' => $context['system_name'] ?? null,
                'auth_method' => $context['auth_method'] ?? null,
                'http_method' => $request->method(),
                'endpoint' => '/' . ltrim($request->path(), '/'),
                'status_code' => $statusCode,
                'response_time_ms' => isset($context['started_at'])
                    ? (int) round((microtime(true) - (float) $context['started_at']) * 1000)
                    : null,
                'error_message' => $errorMessage,
                'ip_address' => $request->ip(),
                'user_agent' => mb_substr((string) $request->userAgent(), 0, 255),
                'created_at' => now(),
            ]);
        } catch (\Throwable $exception) {
            report($exception);
        }
    }
}
