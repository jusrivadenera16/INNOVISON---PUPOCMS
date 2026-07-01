<?php

namespace App\Services;

use App\Models\ApiErrorLog;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Schema;

class PuptasWebhookService
{
    private string $apiUrl;
    private string $clientId;
    private string $clientSecret;
    private string $webhookSecret;
    private string $signatureHeader;
    private string $timestampHeader;
    private string $nonceHeader;
    private int $timeout;
    private string $scope;
    private string $tokenUrl;
    private int $lastLookupAttempts = 0;

    public function __construct()
    {
        $this->apiUrl = (string) config('services.puptas.api_url', '');
        $this->clientId = (string) config('services.puptas.client_id', '');
        $this->clientSecret = (string) config('services.puptas.client_secret', '');
        $this->webhookSecret = (string) config('services.puptas.webhook_secret', '');
        $this->signatureHeader = (string) config('services.puptas.signature_header', 'X-Medical-Signature');
        $this->timestampHeader = (string) config('services.puptas.timestamp_header', 'X-Timestamp');
        $this->nonceHeader = (string) config('services.puptas.nonce_header', 'X-Nonce');
        $this->timeout = (int) config('services.puptas.timeout', 20);
        $this->scope = (string) config('services.puptas.scope', 'medical-read medical-write');
        $this->tokenUrl = $this->resolveTokenUrl((string) config('services.puptas.token_url', ''));
    }

    private function buildHmacSignature(string $payload): string
    {
        return hash_hmac('sha256', $payload, $this->webhookSecret);
    }

    private function extractWebhookFailureMessage(string $responseBody): string
    {
        $responseBody = trim($responseBody);
        if ($responseBody === '') {
            return 'PUPTAS rejected the webhook request.';
        }

        $decoded = json_decode($responseBody, true);
        if (json_last_error() === JSON_ERROR_NONE && is_array($decoded)) {
            $message = trim((string) ($decoded['message'] ?? ''));
            if ($message !== '') {
                return $message;
            }
        }

        return $responseBody;
    }

    private function resolveTokenUrl(string $configuredTokenUrl): string
    {
        $configuredTokenUrl = trim($configuredTokenUrl);
        if ($configuredTokenUrl !== '') {
            return $configuredTokenUrl;
        }

        $apiUrl = trim($this->apiUrl);
        if ($apiUrl === '') {
            return '';
        }

        $parts = parse_url($apiUrl);
        if (!is_array($parts) || empty($parts['scheme']) || empty($parts['host'])) {
            return '';
        }

        $base = $parts['scheme'] . '://' . $parts['host'];
        if (!empty($parts['port'])) {
            $base .= ':' . $parts['port'];
        }

        return $base . '/oauth/token';
    }

    private function getAccessToken(): string
    {
        $cacheKey = $this->accessTokenCacheKey();
        $cachedToken = trim((string) Cache::get($cacheKey, ''));
        if ($cachedToken !== '') {
            return $cachedToken;
        }

        if ($this->tokenUrl === '' || $this->clientId === '' || $this->clientSecret === '') {
            throw new \RuntimeException('PUPTAS OAuth configuration is incomplete.');
        }

        $response = Http::asForm()
            ->acceptJson()
            ->timeout($this->timeout)
            ->post($this->tokenUrl, [
                'grant_type' => 'client_credentials',
                'client_id' => $this->clientId,
                'client_secret' => $this->clientSecret,
                'scope' => $this->scope,
            ]);

        if (!$response->successful()) {
            throw new \RuntimeException('Unable to fetch PUPTAS access token: ' . $response->body());
        }

        $token = trim((string) $response->json('access_token'));
        if ($token === '') {
            throw new \RuntimeException('PUPTAS access token response did not include access_token.');
        }

        $expiresIn = max(60, ((int) $response->json('expires_in', 3600)) - 60);
        Cache::put($cacheKey, $token, now()->addSeconds($expiresIn));

        return $token;
    }

    private function accessTokenCacheKey(): string
    {
        return 'puptas.oauth_token.' . hash('sha256', implode('|', [
            $this->tokenUrl,
            $this->clientId,
            $this->scope,
        ]));
    }

    private function forgetAccessToken(): void
    {
        Cache::forget($this->accessTokenCacheKey());
        Cache::forget('puptas.oauth_token');
    }

    private function sendApplicantGetRequest(string $url)
    {
        $maxAttempts = 3;
        $lastException = null;

        for ($attempt = 1; $attempt <= $maxAttempts; $attempt++) {
            $this->lastLookupAttempts = $attempt;

            try {
                $response = Http::timeout($this->timeout)
                    ->withToken($this->getAccessToken())
                    ->acceptJson()
                    ->get($url);

                if ($response->status() === 401) {
                    $this->forgetAccessToken();
                    $response = Http::timeout($this->timeout)
                        ->withToken($this->getAccessToken())
                        ->acceptJson()
                        ->get($url);
                }

                $isTemporaryFailure = in_array($response->status(), [408, 425, 429], true)
                    || $response->status() >= 500;

                if (!$isTemporaryFailure || $attempt === $maxAttempts) {
                    return $response;
                }
            } catch (\Throwable $exception) {
                $lastException = $exception;
                if ($attempt === $maxAttempts) {
                    throw $exception;
                }
            }

            usleep(250000 * $attempt);
        }

        throw $lastException ?: new \RuntimeException('PUPTAS lookup failed after retrying.');
    }

    private function maskedLookupIdentifier(string $value): string
    {
        $value = trim($value);
        if ($value === '') {
            return '';
        }

        return strlen($value) <= 4
            ? str_repeat('*', strlen($value))
            : str_repeat('*', max(4, strlen($value) - 4)) . substr($value, -4);
    }

    private function logApplicantLookupFailure(
        string $lookupType,
        string $identifier,
        ?int $httpStatus,
        string $message,
        string $errorType
    ): void {
        Log::warning('PUPTAS applicant lookup unavailable', [
            'lookup_type' => $lookupType,
            'identifier' => $this->maskedLookupIdentifier($identifier),
            'status' => $httpStatus,
            'attempts' => $this->lastLookupAttempts,
            'user_id' => auth()->id(),
            'error_type' => $errorType,
        ]);

        try {
            if (!Schema::hasTable('api_error_logs')) {
                return;
            }

            ApiErrorLog::logError('puptas', [
                'endpoint' => $lookupType === 'idp'
                    ? '/api/v1/medical/applicants/idp/{idpUserId}'
                    : '/api/v1/medical/applicants/{referenceNumber}',
                'error_code' => $httpStatus ? 'HTTP_' . $httpStatus : 'LOOKUP_EXCEPTION',
                'error_message' => mb_substr($message, 0, 2000),
                'request_payload' => json_encode([
                    'user_id' => auth()->id(),
                    'lookup_type' => $lookupType,
                    'identifier' => $this->maskedLookupIdentifier($identifier),
                    'attempts' => $this->lastLookupAttempts,
                ], JSON_UNESCAPED_SLASHES),
                'response_payload' => null,
                'http_status' => $httpStatus !== null ? (string) $httpStatus : null,
                'error_type' => $errorType,
                'ip_address' => request()?->ip(),
                'user_agent' => mb_substr((string) request()?->userAgent(), 0, 255),
            ]);
        } catch (\Throwable $exception) {
            Log::debug('Unable to persist PUPTAS API error log', [
                'error' => $exception->getMessage(),
            ]);
        }
    }

    private function resolveApplicantsBaseUrl(): string
    {
        $apiUrl = trim($this->apiUrl);
        if ($apiUrl === '') {
            return '';
        }

        $parts = parse_url($apiUrl);
        if (!is_array($parts) || empty($parts['scheme']) || empty($parts['host'])) {
            return '';
        }

        $base = $parts['scheme'] . '://' . $parts['host'];
        if (!empty($parts['port'])) {
            $base .= ':' . $parts['port'];
        }

        return $base . '/api/v1/medical/applicants';
    }

    public function fetchApplicantByReferenceNumber(string $referenceNumber): ?array
    {
        $result = $this->fetchApplicantByReferenceNumberDetailed($referenceNumber);

        return $result['success'] ? ($result['data'] ?? null) : null;
    }

    public function fetchApplicantByReferenceNumberDetailed(string $referenceNumber): array
    {
        try {
            $referenceNumber = trim($referenceNumber);
            if ($referenceNumber === '') {
                return [
                    'success' => false,
                    'outcome' => 'invalid',
                    'status' => null,
                    'message' => 'Reference number is required.',
                    'data' => null,
                    'attempts' => 0,
                ];
            }

            $applicantsBaseUrl = $this->resolveApplicantsBaseUrl();
            if ($applicantsBaseUrl === '') {
                $this->lastLookupAttempts = 0;
                $this->logApplicantLookupFailure(
                    'reference',
                    $referenceNumber,
                    null,
                    'PUPTAS applicants endpoint is not configured.',
                    'configuration'
                );
                return [
                    'success' => false,
                    'outcome' => 'unavailable',
                    'status' => null,
                    'message' => 'PUPTAS applicants endpoint is not configured.',
                    'data' => null,
                    'attempts' => 0,
                ];
            }

            $response = $this->sendApplicantGetRequest(
                rtrim($applicantsBaseUrl, '/') . '/' . rawurlencode($referenceNumber)
            );

            if (!$response->successful()) {
                $responseMessage = trim((string) $response->json('message'));
                $isNotFound = $response->status() === 404;
                if (!$isNotFound) {
                    $this->logApplicantLookupFailure(
                        'reference',
                        $referenceNumber,
                        $response->status(),
                        $responseMessage !== '' ? $responseMessage : 'PUPTAS lookup could not be completed.',
                        'http_error'
                    );
                }
                return [
                    'success' => false,
                    'outcome' => $isNotFound ? 'not_found' : 'unavailable',
                    'status' => $response->status(),
                    'message' => $responseMessage !== ''
                        ? $responseMessage
                        : ($isNotFound
                            ? 'No eligible PUPTAS applicant was found for that reference number.'
                            : 'PUPTAS lookup could not be completed. Please try again.'),
                    'body' => $response->body(),
                    'data' => null,
                    'attempts' => $this->lastLookupAttempts,
                ];
            }

            $data = $response->json('data');
            if (!is_array($data)) {
                $this->logApplicantLookupFailure(
                    'reference',
                    $referenceNumber,
                    $response->status(),
                    'PUPTAS response did not include applicant data.',
                    'invalid_response'
                );
            }
            return [
                'success' => is_array($data),
                'outcome' => is_array($data) ? 'found' : 'unavailable',
                'status' => $response->status(),
                'message' => is_array($data) ? 'Applicant found.' : 'PUPTAS response did not include applicant data.',
                'body' => $response->body(),
                'data' => is_array($data) ? $data : null,
                'attempts' => $this->lastLookupAttempts,
            ];
        } catch (\Throwable $exception) {
            $this->logApplicantLookupFailure(
                'reference',
                $referenceNumber,
                null,
                'PUPTAS lookup could not be completed after retrying.',
                'exception'
            );
            return [
                'success' => false,
                'outcome' => 'unavailable',
                'status' => null,
                'message' => 'PUPTAS verification is temporarily unavailable. Please try again later.',
                'data' => null,
                'attempts' => $this->lastLookupAttempts,
            ];
        }
    }

    /**
     * Backward-compatible aliases for callers that still use the former,
     * misleading student-number method names.
     */
    public function fetchApplicantByStudentNumber(string $studentNumber): ?array
    {
        return $this->fetchApplicantByReferenceNumber($studentNumber);
    }

    public function fetchApplicantByStudentNumberDetailed(string $studentNumber): array
    {
        return $this->fetchApplicantByReferenceNumberDetailed($studentNumber);
    }

    public function fetchApplicantByIdpUserId(string $idpUserId): ?array
    {
        $result = $this->fetchApplicantByIdpUserIdDetailed($idpUserId);

        return $result['success'] ? ($result['data'] ?? null) : null;
    }

    public function fetchApplicantByIdpUserIdDetailed(string $idpUserId): array
    {
        try {
            $idpUserId = trim($idpUserId);
            if ($idpUserId === '') {
                return [
                    'success' => false,
                    'outcome' => 'invalid',
                    'status' => null,
                    'message' => 'IDP user ID is required.',
                    'data' => null,
                    'attempts' => 0,
                ];
            }

            $applicantsBaseUrl = $this->resolveApplicantsBaseUrl();
            if ($applicantsBaseUrl === '') {
                $this->lastLookupAttempts = 0;
                $this->logApplicantLookupFailure(
                    'idp',
                    $idpUserId,
                    null,
                    'PUPTAS applicants endpoint is not configured.',
                    'configuration'
                );
                return [
                    'success' => false,
                    'outcome' => 'unavailable',
                    'status' => null,
                    'message' => 'PUPTAS applicants endpoint is not configured.',
                    'data' => null,
                    'attempts' => 0,
                ];
            }

            $response = $this->sendApplicantGetRequest(
                rtrim($applicantsBaseUrl, '/') . '/idp/' . rawurlencode($idpUserId)
            );

            if (!$response->successful()) {
                $responseMessage = trim((string) $response->json('message'));
                $isNotFound = $response->status() === 404;
                if (!$isNotFound) {
                    $this->logApplicantLookupFailure(
                        'idp',
                        $idpUserId,
                        $response->status(),
                        $responseMessage !== '' ? $responseMessage : 'PUPTAS lookup could not be completed.',
                        'http_error'
                    );
                }
                return [
                    'success' => false,
                    'outcome' => $isNotFound ? 'not_found' : 'unavailable',
                    'status' => $response->status(),
                    'message' => $responseMessage !== ''
                        ? $responseMessage
                        : ($isNotFound
                            ? 'No eligible PUPTAS applicant was found for this account.'
                            : 'PUPTAS lookup could not be completed. Please try again.'),
                    'body' => $response->body(),
                    'data' => null,
                    'attempts' => $this->lastLookupAttempts,
                ];
            }

            $data = $response->json('data');
            if (!is_array($data)) {
                $this->logApplicantLookupFailure(
                    'idp',
                    $idpUserId,
                    $response->status(),
                    'PUPTAS response did not include applicant data.',
                    'invalid_response'
                );
            }
            return [
                'success' => is_array($data),
                'outcome' => is_array($data) ? 'found' : 'unavailable',
                'status' => $response->status(),
                'message' => is_array($data) ? 'Applicant found.' : 'PUPTAS response did not include applicant data.',
                'body' => $response->body(),
                'data' => is_array($data) ? $data : null,
                'attempts' => $this->lastLookupAttempts,
            ];
        } catch (\Throwable $exception) {
            $this->logApplicantLookupFailure(
                'idp',
                $idpUserId,
                null,
                'PUPTAS lookup could not be completed after retrying.',
                'exception'
            );
            return [
                'success' => false,
                'outcome' => 'unavailable',
                'status' => null,
                'message' => 'PUPTAS verification is temporarily unavailable. Please try again later.',
                'data' => null,
                'attempts' => $this->lastLookupAttempts,
            ];
        }
    }

    public function sendMedicalClearance(
        string $referenceNumber,
        ?string $studentId = null,
        bool $isCleared = true
    ): array
    {
        try {
            $referenceNumber = trim($referenceNumber);
            $studentId = trim((string) $studentId);

            if (!$isCleared) {
                return [
                    'success' => true,
                    'skipped' => true,
                    'message' => 'PUPTAS sync skipped because the student is not yet medically cleared.',
                ];
            }

            if ($referenceNumber === '' && $studentId === '') {
                return [
                    'success' => false,
                    'message' => 'A reference number or IDP student ID is required.',
                ];
            }

            if ($this->apiUrl === '' || $this->webhookSecret === '') {
                throw new \RuntimeException('PUPTAS webhook configuration is incomplete.');
            }

            $timestamp = now()->utc()->toIso8601String();
            $nonce = (string) \Illuminate\Support\Str::uuid();
            $payloadData = [];
            if ($studentId !== '') {
                $payloadData['student_id'] = $studentId;
            }
            if ($referenceNumber !== '') {
                $payloadData['reference_number'] = $referenceNumber;
            }
            $payloadData['is_health_profile_completed'] = 1;
            $payloadData['timestamp'] = $timestamp;
            $payloadData['nonce'] = $nonce;

            $payload = json_encode($payloadData, JSON_UNESCAPED_SLASHES);

            if ($payload === false) {
                throw new \RuntimeException('Failed to encode PUPTAS payload.');
            }

            $signature = $this->buildHmacSignature($payload);
            $accessToken = $this->getAccessToken();

            $headers = [
                'Content-Type' => 'application/json',
                'Accept' => 'application/json',
                $this->signatureHeader => $signature,
                $this->timestampHeader => $timestamp,
                $this->nonceHeader => $nonce,
            ];

            if ($this->signatureHeader !== 'X-Medical-Signature') {
                $headers['X-Medical-Signature'] = $signature;
            }
            if (strtolower($this->timestampHeader) !== 'timestamp') {
                $headers['timestamp'] = $timestamp;
            }
            if (strtolower($this->timestampHeader) !== 'x-timestamp') {
                $headers['X-Timestamp'] = $timestamp;
            }
            if (strtolower($this->nonceHeader) !== 'nonce') {
                $headers['nonce'] = $nonce;
            }
            if (strtolower($this->nonceHeader) !== 'x-nonce') {
                $headers['X-Nonce'] = $nonce;
            }

            $response = Http::timeout($this->timeout)
                ->withToken($accessToken)
                ->withHeaders($headers)
                ->withBody($payload, 'application/json')
                ->post($this->apiUrl);

            if ($response->successful()) {
                Log::info('PUPTAS webhook sent successfully', [
                    'reference_number' => $referenceNumber,
                    'student_id' => $studentId,
                    'timestamp' => $timestamp,
                    'nonce' => $nonce,
                ]);
                return ['success' => true, 'message' => 'Synced successfully'];
            }

            $errorMessage = $this->extractWebhookFailureMessage($response->body());
            Log::error('PUPTAS webhook failed', [
                'status' => $response->status(),
                'reference_number' => $referenceNumber,
                'student_id' => $studentId,
                'timestamp' => $timestamp,
                'nonce' => $nonce,
                'error' => $response->body(),
            ]);
            return ['success' => false, 'message' => $errorMessage];
        } catch (\Exception $e) {
            Log::error('PUPTAS webhook exception', ['error' => $e->getMessage()]);
            return ['success' => false, 'message' => $e->getMessage()];
        }
    }

    public function sendWithRetry(
        string $referenceNumber,
        ?string $studentId = null,
        bool $isCleared = true,
        int $maxRetries = 3
    ): array
    {
        $attempt = 0;
        $lastResult = ['success' => false, 'message' => 'No webhook attempts were made.'];

        while ($attempt < $maxRetries) {
            $result = $this->sendMedicalClearance($referenceNumber, $studentId, $isCleared);
            $lastResult = $result;

            if ($result['success']) {
                return $result;
            }

            $attempt++;
            if ($attempt < $maxRetries) {
                sleep(2);
            }
        }

        return [
            'success' => false,
            'message' => trim((string) ($lastResult['message'] ?? '')) !== ''
                ? $lastResult['message']
                : ('Failed after ' . $maxRetries . ' attempts'),
        ];
    }
}
