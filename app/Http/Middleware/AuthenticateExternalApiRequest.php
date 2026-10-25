<?php

namespace App\Http\Middleware;

use App\Models\IntegrationClient;
use App\Services\HealthFileStorage;
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
        $requestedSystem = strtolower(trim((string) $request->header(
            $systemHeaderName,
            $request->query('system', '')
        )));

        $providedCredential = trim((string) $request->bearerToken());

        if ($providedCredential === '') {
            $providedCredential = trim((string) $request->header($headerName, ''));
        }

        if ($providedCredential === '') {
            return $this->rejectAccess($request, 401, 'Authentication credentials were not provided.', 'authentication-missing');
        }

        $accessToken = null;
        if (Schema::hasTable('personal_access_tokens') && Schema::hasTable('integration_clients')) {
            $accessToken = PersonalAccessToken::findToken($providedCredential);
        }

        if ($accessToken) {
            $client = $accessToken->tokenable;

            if (!$client instanceof IntegrationClient || !$client->is_active) {
                return $this->rejectAccess(
                    $request,
                    403,
                    'Forbidden.',
                    'integration-client-inactive',
                    $client instanceof IntegrationClient ? $client : null
                );
            }

            if (
                $requestedSystem !== ''
                && $requestedSystem !== strtolower($client->system_key)
            ) {
                return $this->rejectAccess($request, 403, 'The token does not belong to the requested system.', 'token-system-mismatch', $client);
            }

            foreach ($abilities as $ability) {
                if ($ability !== '' && $accessToken->cant($ability)) {
                    return $this->rejectAccess($request, 403, 'The token does not have the required ability.', 'token-ability-denied', $client);
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
            $response = $this->preventResponseCaching($next($request));
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
            return $this->rejectAccess($request, 403, 'External API authentication is not configured.', 'authentication-not-configured');
        }

        if ($systemKeys->isNotEmpty()) {
            if ($requestedSystem !== '') {
                $expectedSystemKey = $systemKeys->get($requestedSystem);

                if (
                    $expectedSystemKey === null
                    || !hash_equals($expectedSystemKey, $providedCredential)
                ) {
                    return $this->rejectAccess($request, 403, 'Forbidden.', 'credential-rejected');
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
                $response = $this->preventResponseCaching($next($request));
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
                return $this->rejectAccess($request, 403, 'Forbidden.', 'credential-rejected');
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
            $response = $this->preventResponseCaching($next($request));
            $this->logIntegrationRequest($request, $response, [
                'system_key' => $matchedSystem,
                'system_name' => $matchedSystem,
                'auth_method' => 'legacy-static-key',
                'started_at' => $startedAt,
            ]);

            return $response;
        }

        if (!hash_equals($expectedKey, $providedCredential)) {
            return $this->rejectAccess($request, 403, 'Forbidden.', 'credential-rejected');
        }

        $startedAt = microtime(true);
        $response = $this->preventResponseCaching($next($request));
        $this->logIntegrationRequest($request, $response, [
            'system_key' => 'legacy',
            'system_name' => 'Legacy API Key',
            'auth_method' => 'legacy-static-key',
            'started_at' => $startedAt,
        ]);

        return $response;
    }

    private function rejectAccess(
        Request $request,
        int $status,
        string $message,
        string $authMethod,
        ?IntegrationClient $client = null
    ): JsonResponse {
        $systemHeaderName = trim((string) config(
            'services.external_admin_profile.system_header',
            'X-External-System'
        ));
        $requestedSystem = strtolower(trim((string) $request->header(
            $systemHeaderName,
            $request->query('system', '')
        )));

        if (!$client && $requestedSystem !== '' && Schema::hasTable('integration_clients')) {
            try {
                $client = IntegrationClient::query()
                    ->whereRaw('LOWER(system_key) = ?', [$requestedSystem])
                    ->first();
            } catch (\Throwable $exception) {
                report($exception);
            }
        }

        $systemKey = $client?->system_key ?: ($requestedSystem !== '' ? $requestedSystem : 'unknown');
        $systemName = $client?->system_name ?: ($requestedSystem !== '' ? $requestedSystem : 'Unknown system');
        $response = new JsonResponse(['message' => $message], $status);

        $this->logIntegrationRequest($request, $response, [
            'integration_client_id' => $client?->id,
            'system_key' => mb_substr((string) $systemKey, 0, 255),
            'system_name' => mb_substr((string) $systemName, 0, 255),
            'auth_method' => $authMethod,
            'error_message' => $message,
            'started_at' => microtime(true),
        ]);

        return $response;
    }

    private function preventResponseCaching($response)
    {
        if (isset($response->headers)) {
            $response->headers->add(HealthFileStorage::NO_STORE_HEADERS);
        }

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

            $errorMessage = $context['error_message'] ?? ($statusCode !== null && $statusCode >= 400
                ? 'External endpoint returned HTTP ' . $statusCode . '.'
                : null);
            $endpoint = $request->route()?->uri() ?? '[unmatched]';

            DB::table('integration_request_logs')->insert([
                'integration_client_id' => $context['integration_client_id'] ?? null,
                'token_id' => $context['token_id'] ?? null,
                'system_key' => (string) ($context['system_key'] ?? 'unknown'),
                'system_name' => $context['system_name'] ?? null,
                'auth_method' => $context['auth_method'] ?? null,
                'http_method' => $request->method(),
                'endpoint' => '/' . ltrim($endpoint, '/'),
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
