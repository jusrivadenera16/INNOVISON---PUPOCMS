<?php

namespace App\Services;

use Illuminate\Http\Client\RequestException;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;

class GuisisApiService
{
    private string $baseUrl;
    private string $clientId;
    private string $clientSecret;
    private int $timeout;

    public function __construct()
    {
        $this->baseUrl = rtrim((string) config('services.guisis.base_url', ''), '/');
        $this->clientId = trim((string) config('services.guisis.client_id', ''));
        $this->clientSecret = trim((string) config('services.guisis.client_secret', ''));
        $this->timeout = (int) config('services.guisis.timeout', 20);
    }

    public function getStudentByEmailDetailed(string $email): array
    {
        return $this->get('/integrations/students/profile', ['email' => trim($email)]);
    }

    public function listStudentsDetailed(array $query = []): array
    {
        return $this->get('/integrations/students/profiles', array_filter($query, static fn ($value) => $value !== null && $value !== ''));
    }

    public function getStudentByStudentNumberDetailed(string $studentNumber): array
    {
        return $this->get('/integrations/students/' . rawurlencode(trim($studentNumber)));
    }

    public function getStudentAddressesDetailed(string $studentNumber): array
    {
        return $this->get('/integrations/students/' . rawurlencode(trim($studentNumber)) . '/addresses');
    }

    public function getStudentPersonalInfoDetailed(string $studentNumber): array
    {
        $encodedStudentNumber = rawurlencode(trim($studentNumber));
        $paths = [
            "/integrations/students/{$encodedStudentNumber}/personalInfo",
            "/integrations/students/{$encodedStudentNumber}/personal-info",
            "/integrations/students/{$encodedStudentNumber}/personal_info",
            "/integrations/students/{$encodedStudentNumber}/personal-information",
            "/integrations/students/{$encodedStudentNumber}/personal",
        ];

        $lastResult = null;
        foreach ($paths as $path) {
            $result = $this->get($path);
            $result['attempted_paths'] = $paths;
            $result['attempted_urls'] = array_map(fn (string $attemptedPath): string => $this->baseUrl . $attemptedPath, $paths);

            if (($result['ok'] ?? false) || (int) ($result['status'] ?? 0) !== 404) {
                return $result;
            }

            $lastResult = $result;
        }

        return $lastResult ?? [
            'status' => 404,
            'ok' => false,
            'message' => 'GuiSIS personal information endpoint was not found.',
            'body' => '',
            'data' => null,
            'attempted_paths' => $paths,
            'attempted_urls' => array_map(fn (string $path): string => $this->baseUrl . $path, $paths),
        ];
    }

    public function configuredBaseUrl(): string
    {
        return $this->baseUrl;
    }

    private function get(string $path, array $query = []): array
    {
        if ($this->baseUrl === '') {
            return [
                'status' => 500,
                'ok' => false,
                'message' => 'GuiSIS base URL is not configured.',
                'body' => '',
                'data' => null,
                'auth' => [
                    'status' => 'not_configured',
                    'source' => 'none',
                    'endpoint' => '',
                ],
            ];
        }

        try {
            $tokenMeta = $this->accessToken();
            $url = $this->baseUrl . $path;
            $response = Http::timeout($this->timeout)
                ->acceptJson()
                ->withHeaders(['Content-Type' => 'application/json'])
                ->withToken($tokenMeta['token'])
                ->get($url, $query);

            $payload = $response->json();

            return [
                'status' => $response->status(),
                'ok' => $response->successful(),
                'message' => $response->successful()
                    ? 'GuiSIS request completed successfully.'
                    : $this->extractErrorMessage($payload, $response->body(), 'GuiSIS request returned an error response.'),
                'body' => $response->body(),
                'data' => $payload,
                'auth' => $tokenMeta['auth'],
                'endpoint' => $url,
            ];
        } catch (RequestException $exception) {
            $response = $exception->response;

            return [
                'status' => $response?->status() ?? 500,
                'ok' => false,
                'message' => $this->extractErrorMessage(
                    $response?->json(),
                    (string) ($response?->body() ?? ''),
                    'GuiSIS request failed.'
                ),
                'body' => (string) ($response?->body() ?? $exception->getMessage()),
                'data' => $response?->json(),
                'auth' => [
                    'status' => 'request_failed',
                    'source' => 'unknown',
                    'endpoint' => $this->baseUrl . '/auth/m2m/token',
                ],
            ];
        } catch (\Throwable $exception) {
            return [
                'status' => 500,
                'ok' => false,
                'message' => 'Unable to reach GuiSIS right now: ' . $exception->getMessage(),
                'body' => $exception->getMessage(),
                'data' => null,
                'auth' => [
                    'status' => str_contains(strtolower($exception->getMessage()), 'token') ? 'token_failed' : 'request_failed',
                    'source' => 'unknown',
                    'endpoint' => $this->baseUrl . '/auth/m2m/token',
                ],
            ];
        }
    }

    private function accessToken(): array
    {
        $cacheKey = 'guisis.m2m.' . md5($this->clientId . '|' . $this->baseUrl);
        $cachedToken = trim((string) Cache::get($cacheKey, ''));
        if ($cachedToken !== '') {
            return [
                'token' => $cachedToken,
                'auth' => [
                    'status' => 'ok',
                    'source' => 'cache',
                    'endpoint' => $this->baseUrl . '/auth/m2m/token',
                ],
            ];
        }

        if ($this->clientId === '' || $this->clientSecret === '') {
            throw new \RuntimeException('GuiSIS M2M credentials are incomplete.');
        }

        $response = Http::timeout($this->timeout)
            ->acceptJson()
            ->post($this->baseUrl . '/auth/m2m/token', [
                'clientId' => $this->clientId,
                'clientSecret' => $this->clientSecret,
            ]);

        if (!$response->successful()) {
            throw new \RuntimeException('Unable to fetch GuiSIS access token: ' . $response->body());
        }

        $payload = $response->json();
        $token = trim((string) (
            data_get($payload, 'accessToken')
            ?? data_get($payload, 'access_token')
            ?? data_get($payload, 'token')
            ?? data_get($payload, 'data.accessToken')
            ?? data_get($payload, 'data.access_token')
            ?? data_get($payload, 'data.token')
            ?? ''
        ));
        if ($token === '') {
            throw new \RuntimeException(
                'GuiSIS access token response did not include accessToken. Raw token response: ' . $response->body()
            );
        }

        $expiresIn = (int) (
            data_get($payload, 'expiresIn')
            ?? data_get($payload, 'expires_in')
            ?? data_get($payload, 'data.expiresIn')
            ?? data_get($payload, 'data.expires_in')
            ?? 3600
        );
        Cache::put($cacheKey, $token, now()->addSeconds(max(60, $expiresIn - 60)));

        return [
            'token' => $token,
            'auth' => [
                'status' => 'ok',
                'source' => 'fresh_m2m_token',
                'endpoint' => $this->baseUrl . '/auth/m2m/token',
            ],
        ];
    }

    private function extractErrorMessage($payload, string $body, string $fallback): string
    {
        if (is_array($payload)) {
            $message = trim((string) ($payload['message'] ?? $payload['error'] ?? ''));
            if ($message !== '') {
                return $message;
            }
        }

        $body = trim($body);
        return $body !== '' ? $body : $fallback;
    }
}
