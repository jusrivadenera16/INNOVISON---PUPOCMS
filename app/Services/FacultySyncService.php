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
        $fields = is_array($faculty['fields'] ?? null) ? $faculty['fields'] : [];

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
            $fields['idp_user_id'] ?? null,
            $fields['faculty_uuid'] ?? null,
            $fields['admin_uuid'] ?? null,
            $fields['user_uuid'] ?? null,
            $fields['uuid'] ?? null,
            $fields['sub'] ?? null,
        ] as $candidate) {
            $candidate = trim((string) $candidate);

            if ($this->isUuid($candidate)) {
                return strtolower($candidate);
            }
        }

        return null;
    }

    public function resolveFacultyUuidByIdentity(
        string $email = '',
        string $employeeNumber = '',
        ?int $timeoutOverride = null
    ): ?string {
        $email = $this->normalize($email);
        $employeeNumber = $this->normalize($employeeNumber);
        $searchTerms = array_values(array_unique(array_filter([
            $employeeNumber,
            $email,
        ])));

        foreach ($searchTerms as $searchTerm) {
            $matches = [];

            foreach ($this->fetchFaculties($searchTerm, $timeoutOverride) as $faculty) {
                if (!is_array($faculty) || !$this->facultyMatchesIdentity($faculty, $email, $employeeNumber)) {
                    continue;
                }

                $uuid = $this->resolveFacultyUuid($faculty);
                if ($uuid !== null) {
                    $matches[$uuid] = true;
                }
            }

            $uuids = array_keys($matches);
            if (count($uuids) === 1) {
                return $uuids[0];
            }

            if (count($uuids) > 1) {
                return null;
            }
        }

        return null;
    }

    private function facultyMatchesIdentity(array $faculty, string $email, string $employeeNumber): bool
    {
        $profile = is_array($faculty['profile'] ?? null) ? $faculty['profile'] : [];
        $fields = is_array($faculty['fields'] ?? null) ? $faculty['fields'] : [];

        if ($email !== '' && in_array($email, $this->uniqueNormalizedValues([
            $faculty['email'] ?? null,
            $faculty['email_address'] ?? null,
            $fields['email'] ?? null,
            $fields['email_address'] ?? null,
            $profile['email'] ?? null,
            $profile['email_address'] ?? null,
        ]), true)) {
            return true;
        }

        return $employeeNumber !== '' && in_array($employeeNumber, $this->uniqueNormalizedValues([
            $faculty['faculty_code'] ?? null,
            $faculty['identifier'] ?? null,
            $faculty['employee_number'] ?? null,
            $faculty['employee_no'] ?? null,
            $fields['faculty_code'] ?? null,
            $fields['identifier'] ?? null,
            $fields['employee_number'] ?? null,
            $fields['employee_no'] ?? null,
            $profile['faculty_code'] ?? null,
            $profile['identifier'] ?? null,
            $profile['employee_number'] ?? null,
            $profile['employee_no'] ?? null,
        ]), true);
    }

    private function uniqueNormalizedValues(array $values): array
    {
        return array_values(array_unique(array_filter(array_map(
            fn ($value) => $this->normalize($value),
            $values
        ))));
    }

    private function normalize($value): string
    {
        return strtolower(trim((string) $value));
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
