<?php

namespace App\Services;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class PuptasWebhookService
{
    private string $apiUrl;
    private string $clientId;
    private string $clientSecret;
    private string $webhookSecret;
    private string $signatureHeader;
    private int $timeout;
    private string $scope;
    private string $tokenUrl;

    public function __construct()
    {
        $this->apiUrl = (string) config('services.puptas.api_url', '');
        $this->clientId = (string) config('services.puptas.client_id', '');
        $this->clientSecret = (string) config('services.puptas.client_secret', '');
        $this->webhookSecret = (string) config('services.puptas.webhook_secret', '');
        $this->signatureHeader = (string) config('services.puptas.signature_header', 'X-Medical-Signature');
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

        return $response;
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
                    'status' => null,
                    'message' => 'Reference number is required.',
                    'data' => null,
                ];
            }

            $applicantsBaseUrl = $this->resolveApplicantsBaseUrl();
            if ($applicantsBaseUrl === '') {
                return [
                    'success' => false,
                    'status' => null,
                    'message' => 'PUPTAS applicants endpoint is not configured.',
                    'data' => null,
                ];
            }

            $response = $this->sendApplicantGetRequest(
                rtrim($applicantsBaseUrl, '/') . '/' . rawurlencode($referenceNumber)
            );

            if (!$response->successful()) {
                $responseMessage = trim((string) $response->json('message'));
                Log::warning('PUPTAS applicant lookup failed', [
                    'reference_number' => $referenceNumber,
                    'status' => $response->status(),
                    'body' => $response->body(),
                ]);
                return [
                    'success' => false,
                    'status' => $response->status(),
                    'message' => $responseMessage !== ''
                        ? $responseMessage
                        : ($response->status() === 404
                            ? 'No eligible PUPTAS applicant was found for that reference number.'
                            : 'PUPTAS lookup could not be completed. Please try again.'),
                    'body' => $response->body(),
                    'data' => null,
                ];
            }

            $data = $response->json('data');
            return [
                'success' => is_array($data),
                'status' => $response->status(),
                'message' => is_array($data) ? 'Applicant found.' : 'PUPTAS response did not include applicant data.',
                'body' => $response->body(),
                'data' => is_array($data) ? $data : null,
            ];
        } catch (\Throwable $exception) {
            Log::warning('PUPTAS applicant lookup exception', [
                'reference_number' => $referenceNumber,
                'error' => $exception->getMessage(),
            ]);
            return [
                'success' => false,
                'status' => null,
                'message' => $exception->getMessage(),
                'data' => null,
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
                    'status' => null,
                    'message' => 'IDP user ID is required.',
                    'data' => null,
                ];
            }

            $applicantsBaseUrl = $this->resolveApplicantsBaseUrl();
            if ($applicantsBaseUrl === '') {
                return [
                    'success' => false,
                    'status' => null,
                    'message' => 'PUPTAS applicants endpoint is not configured.',
                    'data' => null,
                ];
            }

            $response = $this->sendApplicantGetRequest(
                rtrim($applicantsBaseUrl, '/') . '/idp/' . rawurlencode($idpUserId)
            );

            if (!$response->successful()) {
                $responseMessage = trim((string) $response->json('message'));
                Log::warning('PUPTAS applicant IDP lookup failed', [
                    'idp_user_id' => $idpUserId,
                    'status' => $response->status(),
                    'body' => $response->body(),
                ]);
                return [
                    'success' => false,
                    'status' => $response->status(),
                    'message' => $responseMessage !== ''
                        ? $responseMessage
                        : ($response->status() === 404
                            ? 'No eligible PUPTAS applicant was found for this account.'
                            : 'PUPTAS lookup could not be completed. Please try again.'),
                    'body' => $response->body(),
                    'data' => null,
                ];
            }

            $data = $response->json('data');
            return [
                'success' => is_array($data),
                'status' => $response->status(),
                'message' => is_array($data) ? 'Applicant found.' : 'PUPTAS response did not include applicant data.',
                'body' => $response->body(),
                'data' => is_array($data) ? $data : null,
            ];
        } catch (\Throwable $exception) {
            Log::warning('PUPTAS applicant IDP lookup exception', [
                'idp_user_id' => $idpUserId,
                'error' => $exception->getMessage(),
            ]);
            return [
                'success' => false,
                'status' => null,
                'message' => $exception->getMessage(),
                'data' => null,
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

            $payloadData = [];
            if ($studentId !== '') {
                $payloadData['student_id'] = $studentId;
            }
            if ($referenceNumber !== '') {
                $payloadData['reference_number'] = $referenceNumber;
            }
            $payloadData['is_health_profile_completed'] = 1;

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
            ];

            if ($this->signatureHeader !== 'X-Medical-Signature') {
                $headers['X-Medical-Signature'] = $signature;
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
                ]);
                return ['success' => true, 'message' => 'Synced successfully'];
            }

            $errorMessage = $this->extractWebhookFailureMessage($response->body());
            Log::error('PUPTAS webhook failed', [
                'status' => $response->status(),
                'reference_number' => $referenceNumber,
                'student_id' => $studentId,
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
