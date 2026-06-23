<?php

namespace App\Http\Middleware;

use App\Models\IntegrationClient;
use Closure;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
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

            return $next($request);
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

                return $next($request);
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

            return $next($request);
        }

        if (!hash_equals($expectedKey, $providedCredential)) {
            return new JsonResponse([
                'message' => 'Forbidden.',
            ], 403);
        }

        return $next($request);
    }
}
