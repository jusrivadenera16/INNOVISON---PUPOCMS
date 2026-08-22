<?php

namespace App\Services;

use Illuminate\Http\Client\RequestException;
use Illuminate\Support\Facades\Http;
use RuntimeException;

class FacultySyncService
{
    public function generateHmacHeaders(string $method, string $url, string $body = '', string $nonce = ''): array
    {
        $secretKey = (string) config('services.pupt_flss.secret_key');
        $timestamp = (string) now()->timestamp;
        $normalizedUrl = trim($url);
        $normalizedBody = (string) $body;
        $normalizedNonce = (string) $nonce;

        if ($secretKey === '') {
            throw new RuntimeException('PUPT-FLSS HMAC credentials are not configured.');
        }

        $normalizedMethod = strtoupper(trim($method));
        $message = implode('|', [
            $normalizedMethod,
            $normalizedUrl,
            $normalizedBody,
            $timestamp,
            $normalizedNonce,
        ]);
        $signature = hash_hmac('sha256', $message, $secretKey);

        $headers = [
            // FLSS expects a hex-encoded HMAC SHA-256 signature.
            'X-HMAC-Signature' => $signature,
            'X-HMAC-Timestamp' => $timestamp,
        ];

        if ($normalizedNonce !== '') {
            $headers['X-HMAC-Nonce'] = $normalizedNonce;
        }

        return $headers;
    }

    public function fetchFaculties(?string $search = null, ?int $timeoutOverride = null): array
    {
        $rawConfig = config('services.pupt_flss.faculty_profiles_url');
        $baseUrl = trim((string) $rawConfig);

        if ($baseUrl === '') {
            throw new RuntimeException('PUPT-FLSS faculty profiles URL is not configured.');
        }

        $queryParams = [];
        $search = trim((string) $search);
        if ($search !== '') {
            $queryParams['search'] = $search;
        }

        $requestUrl = $queryParams === []
            ? $baseUrl
            : $baseUrl . (str_contains($baseUrl, '?') ? '&' : '?') . http_build_query($queryParams);

        $configuredTimeout = max(1, (int) config('services.pupt_flss.timeout', 30));
        $timeout = $timeoutOverride === null
            ? $configuredTimeout
            : max(1, min($configuredTimeout, $timeoutOverride));

        $response = Http::acceptJson()
            ->timeout($timeout)
            ->withOptions(['connect_timeout' => min(5, $timeout)])
            ->withHeaders($this->generateHmacHeaders('GET', $requestUrl))
            ->get($requestUrl);

        if (!$response->successful()) {
            throw new RequestException($response);
        }

        $payload = $response->json();

        return $this->extractFaculties($payload);
    }

    public function sync(): array
    {
        $faculties = $this->fetchFaculties();

        return [
            'fetched' => count($faculties),
            'synced' => 0,
        ];
    }

    public function resolveFacultyUuid(array $faculty): ?string
    {
        $profile = is_array($faculty['profile'] ?? null) ? $faculty['profile'] : [];

        foreach ([
            $faculty['faculty_uuid'] ?? null,
            $faculty['admin_uuid'] ?? null,
            $faculty['idp_user_id'] ?? null,
            $faculty['user_uuid'] ?? null,
            $faculty['uuid'] ?? null,
            $faculty['student_id'] ?? null,
            $profile['faculty_uuid'] ?? null,
            $profile['admin_uuid'] ?? null,
            $profile['idp_user_id'] ?? null,
            $profile['user_uuid'] ?? null,
            $profile['uuid'] ?? null,
            $profile['student_id'] ?? null,
        ] as $candidate) {
            $candidate = trim((string) $candidate);

            if ($this->isUuid($candidate)) {
                return strtolower($candidate);
            }
        }

        return null;
    }

    private function extractFaculties($payload): array
    {
        if (!is_array($payload)) {
            return [];
        }

        if (isset($payload['faculties']) && is_array($payload['faculties'])) {
            return $payload['faculties'];
        }

        if (isset($payload['data']['faculties']) && is_array($payload['data']['faculties'])) {
            return $payload['data']['faculties'];
        }

        if (isset($payload['data']) && is_array($payload['data']) && $this->isList($payload['data'])) {
            return $payload['data'];
        }

        if ($this->isList($payload)) {
            return $payload;
        }

        return [];
    }
    private function isList(array $value): bool
    {
        if (function_exists('array_is_list')) {
            return array_is_list($value);
        }

        return array_keys($value) === range(0, count($value) - 1);
    }

    private function isUuid(string $value): bool
    {
        return preg_match(
            '/^[0-9a-f]{8}-[0-9a-f]{4}-[1-5][0-9a-f]{3}-[89ab][0-9a-f]{3}-[0-9a-f]{12}$/i',
            $value
        ) === 1;
    }
}
