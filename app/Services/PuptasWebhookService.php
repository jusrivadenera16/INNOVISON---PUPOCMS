<?php

namespace App\Services;

use App\Models\ApiErrorLog;
use Illuminate\Contracts\Cache\LockTimeoutException;
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
    private int $tokenLockSeconds;
    private int $tokenLockWaitSeconds;
    private string $userAgent;
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
        $this->tokenLockSeconds = max(
            5,
            $this->timeout + 5,
            (int) config('services.puptas.token_lock_seconds', 30)
        );
        $this->tokenLockWaitSeconds = max(0, (int) config('services.puptas.token_lock_wait_seconds', 25));
        $this->userAgent = trim((string) config('services.puptas.user_agent', 'PUPOCMS/1.0 Laravel')) ?: 'PUPOCMS/1.0 Laravel';
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
            $this->logTokenCacheStateOnce('hit', 1800);

            return $cachedToken;
        }

        $this->logTokenCacheStateOnce('miss', 300);

        $this->ensureAccessTokenRequestIsAvailable();

        if ($this->tokenUrl === '' || $this->clientId === '' || $this->clientSecret === '') {
            throw new \RuntimeException('PUPTAS OAuth configuration is incomplete.');
        }

        $lock = Cache::lock($this->accessTokenLockCacheKey(), $this->tokenLockSeconds);

        try {
            return $lock->block($this->tokenLockWaitSeconds, function () use ($cacheKey): string {
                // Another request may have refreshed the token while this request waited.
                $cachedToken = trim((string) Cache::get($cacheKey, ''));
                if ($cachedToken !== '') {
                    Log::info('PUPTAS OAuth access token cache filled while waiting for refresh lock',
                        $this->tokenDiagnosticContext()
                    );

                    return $cachedToken;
                }

                $this->ensureAccessTokenRequestIsAvailable();

                Log::info('PUPTAS OAuth access token refresh lock acquired',
                    $this->tokenDiagnosticContext([
                        'lock_seconds' => $this->tokenLockSeconds,
                        'lock_wait_seconds' => $this->tokenLockWaitSeconds,
                    ])
                );

                return $this->requestAndCacheAccessToken($cacheKey);
            });
        } catch (LockTimeoutException $exception) {
            // Check once more before reporting contention; the lock owner may have
            // completed between the final lock attempt and this exception.
            $cachedToken = trim((string) Cache::get($cacheKey, ''));
            if ($cachedToken !== '') {
                Log::info('PUPTAS OAuth access token became available after refresh lock timeout',
                    $this->tokenDiagnosticContext()
                );

                return $cachedToken;
            }

            $this->ensureAccessTokenRequestIsAvailable();

            Log::warning('PUPTAS OAuth access token refresh lock timed out',
                $this->tokenDiagnosticContext([
                    'lock_seconds' => $this->tokenLockSeconds,
                    'lock_wait_seconds' => $this->tokenLockWaitSeconds,
                ])
            );

            throw new \RuntimeException(
                'PUPTAS access token refresh is already in progress. Please try again shortly.',
                503,
                $exception
            );
        }
    }

    private function ensureAccessTokenRequestIsAvailable(): void
    {
        $rateLimitedUntil = (int) Cache::get($this->accessTokenRateLimitCacheKey(), 0);
        if ($rateLimitedUntil > now()->timestamp) {
            $retryAfter = max(1, $rateLimitedUntil - now()->timestamp);
            $this->logTokenCooldownOnce($retryAfter, $rateLimitedUntil);

            throw new \RuntimeException(
                "PUPTAS access token request is rate limited. Retry after {$retryAfter} seconds.",
                429
            );
        }

        if ($rateLimitedUntil > 0) {
            Cache::forget($this->accessTokenRateLimitCacheKey());
            Cache::forget($this->tokenDiagnosticMarkerKey('cooldown'));
        }
    }

    private function requestAndCacheAccessToken(string $cacheKey): string
    {
        $requestStartedAt = microtime(true);

        Log::info('PUPTAS OAuth access token request started',
            $this->tokenDiagnosticContext()
        );

        try {
            $response = Http::asForm()
                ->acceptJson()
                ->withUserAgent($this->userAgent)
                ->timeout($this->timeout)
                ->post($this->tokenUrl, [
                    'grant_type' => 'client_credentials',
                    'client_id' => $this->clientId,
                    'client_secret' => $this->clientSecret,
                    'scope' => $this->scope,
                ]);
        } catch (\Throwable $exception) {
            Log::warning('PUPTAS OAuth access token request failed before receiving a response',
                $this->tokenDiagnosticContext([
                    'duration_ms' => $this->elapsedMilliseconds($requestStartedAt),
                    'exception_type' => get_class($exception),
                ])
            );

            throw $exception;
        }

        if (!$response->successful()) {
            $status = $response->status();
            $responseMessage = $this->responseFailureMessage($response->body());

            if ($status === 429) {
                $retryAfterHeader = trim((string) $response->header('Retry-After'));
                $retryAfter = $this->retryAfterSeconds($retryAfterHeader);
                $retryAt = now()->addSeconds($retryAfter);
                Cache::put(
                    $this->accessTokenRateLimitCacheKey(),
                    $retryAt->timestamp,
                    $retryAt
                );

                Log::warning('PUPTAS OAuth token endpoint returned a rate limit response',
                    $this->tokenDiagnosticContext(array_merge([
                        'status' => $status,
                        'duration_ms' => $this->elapsedMilliseconds($requestStartedAt),
                        'retry_after_seconds' => $retryAfter,
                        'retry_after_source' => $retryAfterHeader !== '' ? 'upstream_header' : 'local_default',
                        'cooldown_cache_write_verified' => Cache::has($this->accessTokenRateLimitCacheKey()),
                    ], $this->tokenResponseHeaders($response)))
                );

                throw new \RuntimeException(
                    "Unable to fetch PUPTAS access token: rate limited. Retry after {$retryAfter} seconds.",
                    429
                );
            }

            Log::warning('PUPTAS OAuth token endpoint rejected the access token request',
                $this->tokenDiagnosticContext(array_merge([
                    'status' => $status,
                    'duration_ms' => $this->elapsedMilliseconds($requestStartedAt),
                ], $this->tokenResponseHeaders($response)))
            );

            throw new \RuntimeException(
                'Unable to fetch PUPTAS access token'
                    . ($status > 0 ? " (HTTP {$status})" : '')
                    . ($responseMessage !== '' ? ': ' . $responseMessage : '.'),
                $status
            );
        }

        $token = trim((string) $response->json('access_token'));
        if ($token === '') {
            Log::warning('PUPTAS OAuth token response did not include an access token',
                $this->tokenDiagnosticContext([
                    'status' => $response->status(),
                    'duration_ms' => $this->elapsedMilliseconds($requestStartedAt),
                ])
            );

            throw new \RuntimeException('PUPTAS access token response did not include access_token.');
        }

        $reportedExpiresIn = (int) $response->json('expires_in', 3600);
        if ($reportedExpiresIn <= 0) {
            $reportedExpiresIn = 3600;
        }

        $safetyWindow = min(60, max(5, (int) ceil($reportedExpiresIn * 0.1)));
        $cacheTtl = max(1, $reportedExpiresIn - $safetyWindow);
        Cache::put($cacheKey, $token, now()->addSeconds($cacheTtl));
        Cache::forget($this->accessTokenRateLimitCacheKey());
        Cache::forget($this->tokenDiagnosticMarkerKey('miss'));
        Cache::forget($this->tokenDiagnosticMarkerKey('hit'));
        Cache::forget($this->tokenDiagnosticMarkerKey('cooldown'));

        Log::info('PUPTAS OAuth access token cached successfully',
            $this->tokenDiagnosticContext([
                'status' => $response->status(),
                'duration_ms' => $this->elapsedMilliseconds($requestStartedAt),
                'reported_expires_in_seconds' => $reportedExpiresIn,
                'cache_ttl_seconds' => $cacheTtl,
                'cache_write_verified' => Cache::has($cacheKey),
            ])
        );

        return $token;
    }

    private function logTokenCacheStateOnce(string $state, int $markerTtlSeconds): void
    {
        try {
            $shouldLog = Cache::add(
                $this->tokenDiagnosticMarkerKey($state),
                true,
                now()->addSeconds($markerTtlSeconds)
            );
        } catch (\Throwable $exception) {
            Log::debug('PUPTAS OAuth cache diagnostic marker could not be stored',
                $this->tokenDiagnosticContext([
                    'state' => $state,
                    'exception_type' => get_class($exception),
                ])
            );

            return;
        }

        if ($shouldLog) {
            Log::info("PUPTAS OAuth access token cache {$state}",
                $this->tokenDiagnosticContext()
            );
        }
    }

    private function logTokenCooldownOnce(int $retryAfter, int $rateLimitedUntil): void
    {
        try {
            $shouldLog = Cache::add(
                $this->tokenDiagnosticMarkerKey('cooldown'),
                true,
                now()->addSeconds($retryAfter)
            );
        } catch (\Throwable $exception) {
            return;
        }

        if ($shouldLog) {
            Log::info('PUPTAS OAuth access token request blocked by cached cooldown',
                $this->tokenDiagnosticContext([
                    'retry_after_seconds' => $retryAfter,
                    'rate_limited_until' => date(DATE_ATOM, $rateLimitedUntil),
                ])
            );
        }
    }

    private function tokenDiagnosticContext(array $additional = []): array
    {
        return array_merge([
            'cache_store' => (string) config('cache.default', 'unknown'),
            'token_endpoint' => $this->safeTokenEndpoint(),
            'client_fingerprint' => $this->safeFingerprint($this->clientId),
            'scope_fingerprint' => $this->safeFingerprint($this->scope),
            'cache_key_fingerprint' => substr(hash('sha256', $this->accessTokenCacheKey()), 0, 12),
        ], $additional);
    }

    private function safeTokenEndpoint(): string
    {
        $parts = parse_url($this->tokenUrl);
        if (!is_array($parts) || empty($parts['scheme']) || empty($parts['host'])) {
            return $this->tokenUrl === '' ? 'not_configured' : 'invalid_url';
        }

        $endpoint = $parts['scheme'] . '://' . $parts['host'];
        if (!empty($parts['port'])) {
            $endpoint .= ':' . $parts['port'];
        }

        return $endpoint . ($parts['path'] ?? '');
    }

    private function safeFingerprint(string $value): string
    {
        $value = trim($value);

        return $value === '' ? 'missing' : substr(hash('sha256', $value), 0, 12);
    }

    private function tokenDiagnosticMarkerKey(string $state): string
    {
        return $this->accessTokenCacheKey() . '.diagnostics.' . $state;
    }

    private function elapsedMilliseconds(float $startedAt): int
    {
        return max(0, (int) round((microtime(true) - $startedAt) * 1000));
    }

    private function tokenResponseHeaders($response): array
    {
        $context = [
            'response_headers' => [],
        ];
        $headers = [
            'retry_after_header' => 'Retry-After',
            'rate_limit_limit' => 'RateLimit-Limit',
            'rate_limit_remaining' => 'RateLimit-Remaining',
            'rate_limit_reset' => 'RateLimit-Reset',
            'x_rate_limit_limit' => 'X-RateLimit-Limit',
            'x_rate_limit_remaining' => 'X-RateLimit-Remaining',
            'x_rate_limit_reset' => 'X-RateLimit-Reset',
        ];

        foreach ($headers as $contextKey => $headerName) {
            $value = trim((string) $response->header($headerName));
            if ($value !== '') {
                $context[$contextKey] = mb_substr($value, 0, 100);
            }
        }

        foreach ($response->headers() as $headerName => $values) {
            $normalizedName = strtolower((string) $headerName);
            if (in_array($normalizedName, ['authorization', 'proxy-authorization', 'set-cookie'], true)) {
                continue;
            }

            $context['response_headers'][$normalizedName] = mb_substr(
                implode(', ', array_map('strval', (array) $values)),
                0,
                500
            );
        }

        return $context;
    }

    private function accessTokenCacheKey(): string
    {
        return 'puptas.oauth_token.' . hash('sha256', implode('|', [
            $this->tokenUrl,
            $this->clientId,
            $this->scope,
        ]));
    }

    private function accessTokenRateLimitCacheKey(): string
    {
        return $this->accessTokenCacheKey() . '.rate_limited_until';
    }

    private function accessTokenLockCacheKey(): string
    {
        return $this->accessTokenCacheKey() . '.refresh_lock';
    }

    private function retryAfterSeconds(?string $retryAfter, int $default = 60): int
    {
        $retryAfter = trim((string) $retryAfter);
        if ($retryAfter !== '' && ctype_digit($retryAfter)) {
            return max(1, min(3600, (int) $retryAfter));
        }

        if ($retryAfter !== '') {
            $retryAt = strtotime($retryAfter);
            if ($retryAt !== false) {
                return max(1, min(3600, $retryAt - time()));
            }
        }

        return $default;
    }

    private function responseFailureMessage(string $responseBody): string
    {
        $responseBody = trim($responseBody);
        if ($responseBody === '') {
            return '';
        }

        $decoded = json_decode($responseBody, true);
        if (json_last_error() === JSON_ERROR_NONE && is_array($decoded)) {
            foreach (['message', 'error', 'detail'] as $key) {
                $message = trim((string) ($decoded[$key] ?? ''));
                if ($message !== '') {
                    return mb_substr($message, 0, 1000);
                }
            }
        }

        return mb_substr($responseBody, 0, 1000);
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
                    ->withUserAgent($this->userAgent)
                    ->withToken($this->getAccessToken())
                    ->acceptJson()
                    ->get($url);

                if ($response->status() === 401) {
                    $this->forgetAccessToken();
                    $response = Http::timeout($this->timeout)
                        ->withUserAgent($this->userAgent)
                        ->withToken($this->getAccessToken())
                        ->acceptJson()
                        ->get($url);
                }

                // Retrying a 429 immediately only extends the upstream throttle.
                if ($response->status() === 429) {
                    return $response;
                }

                $isTemporaryFailure = in_array($response->status(), [408, 425], true)
                    || $response->status() >= 500;

                if (!$isTemporaryFailure || $attempt === $maxAttempts) {
                    return $response;
                }
            } catch (\Throwable $exception) {
                $lastException = $exception;
                $status = (int) $exception->getCode();
                $isNonRetryableHttpFailure = $status >= 400 && $status < 500;
                if ($isNonRetryableHttpFailure || $attempt === $maxAttempts) {
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

    private function exceptionHttpStatus(\Throwable $exception): ?int
    {
        $status = (int) $exception->getCode();

        return $status >= 400 && $status <= 599 ? $status : null;
    }

    private function safeLookupExceptionMessage(\Throwable $exception, string $identifier): string
    {
        $message = trim($exception->getMessage());
        if ($message === '') {
            return 'PUPTAS lookup failed with an unknown exception.';
        }

        $identifier = trim($identifier);
        if ($identifier !== '') {
            $message = str_replace(
                [$identifier, rawurlencode($identifier)],
                $this->maskedLookupIdentifier($identifier),
                $message
            );
        }

        return mb_substr($message, 0, 2000);
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
            'message' => $message,
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
                        $response->status() === 429 ? 'rate_limited' : 'http_error'
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
            $status = $this->exceptionHttpStatus($exception);
            $exceptionMessage = $this->safeLookupExceptionMessage($exception, $referenceNumber);
            $this->logApplicantLookupFailure(
                'reference',
                $referenceNumber,
                $status,
                $exceptionMessage,
                $status === 429 ? 'rate_limited' : 'exception'
            );
            return [
                'success' => false,
                'outcome' => 'unavailable',
                'status' => $status,
                'message' => $status === 429
                    ? 'PUPTAS verification is temporarily rate limited. Please try again later.'
                    : 'PUPTAS verification is temporarily unavailable. Please try again later.',
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
                        $response->status() === 429 ? 'rate_limited' : 'http_error'
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
            $status = $this->exceptionHttpStatus($exception);
            $exceptionMessage = $this->safeLookupExceptionMessage($exception, $idpUserId);
            $this->logApplicantLookupFailure(
                'idp',
                $idpUserId,
                $status,
                $exceptionMessage,
                $status === 429 ? 'rate_limited' : 'exception'
            );
            return [
                'success' => false,
                'outcome' => 'unavailable',
                'status' => $status,
                'message' => $status === 429
                    ? 'PUPTAS verification is temporarily rate limited. Please try again later.'
                    : 'PUPTAS verification is temporarily unavailable. Please try again later.',
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
                ->withUserAgent($this->userAgent)
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
            return [
                'success' => false,
                'message' => $errorMessage,
                'status' => $response->status(),
                'error_type' => $response->status() === 429 ? 'rate_limited' : 'http_error',
            ];
        } catch (\Exception $e) {
            $status = $this->exceptionHttpStatus($e);
            Log::error('PUPTAS webhook exception', [
                'status' => $status,
                'error_type' => $status === 429 ? 'rate_limited' : 'exception',
                'error' => $e->getMessage(),
            ]);
            return [
                'success' => false,
                'message' => $e->getMessage(),
                'status' => $status,
                'error_type' => $status === 429 ? 'rate_limited' : 'exception',
            ];
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

            $status = (int) ($result['status'] ?? 0);
            if ($status >= 400 && $status < 500) {
                return $result;
            }

            $attempt++;
            if ($attempt < $maxRetries) {
                sleep(2);
            }
        }

        return array_merge($lastResult, [
            'success' => false,
            'message' => trim((string) ($lastResult['message'] ?? '')) !== ''
                ? $lastResult['message']
                : ('Failed after ' . $maxRetries . ' attempts'),
        ]);
    }
}
