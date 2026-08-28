<?php

namespace Tests\Unit;

use App\Services\PuptasWebhookService;
use Illuminate\Http\Client\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Tests\TestCase;

class PuptasWebhookServiceTest extends TestCase
{
    protected function tearDown(): void
    {
        Cache::flush();

        parent::tearDown();
    }

    public function test_it_sends_the_documented_payload_and_raw_body_signature_to_puptas(): void
    {
        Config::set('services.puptas.api_url', 'https://puptas.example/api/v1/webhooks/medical-result');
        Config::set('services.puptas.client_id', 'client-id');
        Config::set('services.puptas.client_secret', 'client-secret');
        Config::set('services.puptas.webhook_secret', 'webhook-secret');
        Config::set('services.puptas.signature_header', 'X-Medical-Signature');
        Config::set('services.puptas.token_url', 'https://puptas.example/oauth/token');
        Config::set('services.puptas.timeout', 20);
        Cache::forget('puptas.oauth_token');

        Http::fake([
            'https://puptas.example/oauth/token' => Http::response([
                'access_token' => 'fake-token',
                'expires_in' => 3600,
            ], 200),
            'https://puptas.example/api/v1/webhooks/medical-result' => Http::response([
                'message' => 'ok',
            ], 200),
        ]);

        $result = app(PuptasWebhookService::class)
            ->sendMedicalClearance('2026-000-057', 'ade67dc4-50f0-4e32-bd80-84308c0f4e10', true);

        $this->assertTrue($result['success']);

        Http::assertSent(function (Request $request) {
            if ($request->url() !== 'https://puptas.example/api/v1/webhooks/medical-result') {
                return false;
            }

            $payload = json_decode($request->body(), true);

            $this->assertSame('ade67dc4-50f0-4e32-bd80-84308c0f4e10', $payload['student_id']);
            $this->assertSame('2026-000-057', $payload['reference_number']);
            $this->assertSame(1, $payload['is_health_profile_completed']);
            $this->assertNotEmpty($payload['timestamp']);
            $this->assertNotEmpty($payload['nonce']);
            $this->assertSame(
                [hash_hmac('sha256', $request->body(), 'webhook-secret')],
                $request->header('X-Medical-Signature')
            );
            $this->assertSame(['Bearer fake-token'], $request->header('Authorization'));
            $this->assertEmpty($request->header('X-Medical-Timestamp'));
            $this->assertEmpty($request->header('X-Medical-Nonce'));
            $this->assertEmpty($request->header('X-HMAC-Signature'));

            return true;
        });
    }

    public function test_it_extracts_the_error_message_from_puptas_failures(): void
    {
        Config::set('services.puptas.api_url', 'https://puptas.example/api/v1/webhooks/medical-result');
        Config::set('services.puptas.client_id', 'client-id');
        Config::set('services.puptas.client_secret', 'client-secret');
        Config::set('services.puptas.webhook_secret', 'webhook-secret');
        Config::set('services.puptas.signature_header', 'X-Medical-Signature');
        Config::set('services.puptas.token_url', 'https://puptas.example/oauth/token');
        Cache::forget('puptas.oauth_token');

        Http::fake([
            'https://puptas.example/oauth/token' => Http::response([
                'access_token' => 'fake-token',
                'expires_in' => 3600,
            ], 200),
            'https://puptas.example/api/v1/webhooks/medical-result' => Http::response([
                'message' => 'Applicant not found, already passed, or missing prerequisite stages.',
            ], 404),
        ]);

        $result = app(PuptasWebhookService::class)
            ->sendMedicalClearance('2026-000-057', null, true);

        $this->assertFalse($result['success']);
        $this->assertSame(
            'Applicant not found, already passed, or missing prerequisite stages.',
            $result['message']
        );
    }

    public function test_it_does_not_send_a_webhook_for_an_uncleared_student(): void
    {
        Http::fake();

        $result = app(PuptasWebhookService::class)
            ->sendMedicalClearance('2026-000-057', 'idp-user-123', false);

        $this->assertTrue($result['success']);
        $this->assertTrue($result['skipped']);
        Http::assertNothingSent();
    }

    public function test_webhook_retry_stops_after_an_oauth_rate_limit_response(): void
    {
        Config::set('services.puptas.api_url', 'https://puptas.example/api/v1/webhooks/medical-result');
        Config::set('services.puptas.client_id', 'client-id');
        Config::set('services.puptas.client_secret', 'client-secret');
        Config::set('services.puptas.webhook_secret', 'webhook-secret');
        Config::set('services.puptas.token_url', 'https://puptas.example/oauth/token');
        Config::set('services.puptas.timeout', 20);

        $tokenRequests = 0;
        $webhookRequests = 0;

        Http::fake(function (Request $request) use (&$tokenRequests, &$webhookRequests) {
            if ($request->url() === 'https://puptas.example/oauth/token') {
                $tokenRequests++;

                return Http::response(
                    ['message' => 'Rate limited.'],
                    429,
                    ['Retry-After' => '120']
                );
            }

            $webhookRequests++;

            return Http::response([], 500);
        });

        $result = app(PuptasWebhookService::class)
            ->sendWithRetry('2026-0207-8804', 'idp-user-rate-limited', true);

        $this->assertFalse($result['success']);
        $this->assertSame(429, $result['status']);
        $this->assertSame('rate_limited', $result['error_type']);
        $this->assertSame(1, $tokenRequests);
        $this->assertSame(0, $webhookRequests);

        $secondResult = app(PuptasWebhookService::class)
            ->sendMedicalClearance('2026-0207-8804', 'idp-user-rate-limited', true);

        $this->assertFalse($secondResult['success']);
        $this->assertSame(429, $secondResult['status']);
        $this->assertSame(1, $tokenRequests);
        $this->assertSame(0, $webhookRequests);
    }

    public function test_lookup_and_webhook_share_one_cached_oauth_token(): void
    {
        Config::set('services.puptas.api_url', 'https://puptas.example/api/v1/webhooks/medical-result');
        Config::set('services.puptas.client_id', 'shared-client-id');
        Config::set('services.puptas.client_secret', 'shared-client-secret');
        Config::set('services.puptas.webhook_secret', 'webhook-secret');
        Config::set('services.puptas.token_url', 'https://puptas.example/oauth/token');
        Config::set('services.puptas.scope', 'medical-read medical-write');

        $tokenRequests = 0;
        $lookupRequests = 0;
        $webhookRequests = 0;

        Http::fake(function (Request $request) use (&$tokenRequests, &$lookupRequests, &$webhookRequests) {
            if ($request->url() === 'https://puptas.example/oauth/token') {
                $tokenRequests++;

                return Http::response([
                    'access_token' => 'shared-token',
                    'expires_in' => 3600,
                ]);
            }

            if ($request->url() === 'https://puptas.example/api/v1/medical/applicants/2026-0207-8804') {
                $lookupRequests++;

                return Http::response([
                    'data' => [
                        'idp_user_id' => 'idp-user-123',
                        'reference_number' => '2026-0207-8804',
                    ],
                ]);
            }

            if ($request->url() === 'https://puptas.example/api/v1/webhooks/medical-result') {
                $webhookRequests++;

                return Http::response(['message' => 'ok']);
            }

            return Http::response([], 404);
        });

        $lookup = app(PuptasWebhookService::class)
            ->fetchApplicantByReferenceNumberDetailed('2026-0207-8804');
        $webhook = app(PuptasWebhookService::class)
            ->sendMedicalClearance('2026-0207-8804', 'idp-user-123', true);

        $this->assertTrue($lookup['success']);
        $this->assertTrue($webhook['success']);
        $this->assertSame(1, $tokenRequests);
        $this->assertSame(1, $lookupRequests);
        $this->assertSame(1, $webhookRequests);
    }

    public function test_token_cache_diagnostics_do_not_expose_credentials_or_tokens(): void
    {
        Config::set('services.puptas.api_url', 'https://puptas.example/api/v1/webhooks/medical-result');
        Config::set('services.puptas.client_id', 'diagnostic-client-id');
        Config::set('services.puptas.client_secret', 'diagnostic-client-secret');
        Config::set('services.puptas.webhook_secret', 'webhook-secret');
        Config::set('services.puptas.token_url', 'https://puptas.example/oauth/token?private=value');
        Config::set('services.puptas.scope', 'medical-read medical-write');

        Log::spy();

        Http::fake([
            'https://puptas.example/oauth/token?private=value' => Http::response([
                'access_token' => 'diagnostic-access-token',
                'expires_in' => 3600,
            ]),
            'https://puptas.example/api/v1/webhooks/medical-result' => Http::response([
                'message' => 'ok',
            ]),
        ]);

        $service = app(PuptasWebhookService::class);
        $firstResult = $service->sendMedicalClearance('2026-0207-8804', 'idp-user-123', true);
        $secondResult = $service->sendMedicalClearance('2026-0207-8804', 'idp-user-123', true);

        $this->assertTrue($firstResult['success']);
        $this->assertTrue($secondResult['success']);

        Log::shouldHaveReceived('info')
            ->with('PUPTAS OAuth access token cached successfully', \Mockery::on(function (array $context): bool {
                $encoded = json_encode($context);

                return $context['token_endpoint'] === 'https://puptas.example/oauth/token'
                    && $context['reported_expires_in_seconds'] === 3600
                    && $context['cache_ttl_seconds'] === 3540
                    && $context['cache_write_verified'] === true
                    && !str_contains($encoded, 'diagnostic-client-id')
                    && !str_contains($encoded, 'diagnostic-client-secret')
                    && !str_contains($encoded, 'diagnostic-access-token')
                    && !str_contains($encoded, 'private=value');
            }))
            ->once();

        Log::shouldHaveReceived('info')
            ->with('PUPTAS OAuth access token cache hit', \Mockery::type('array'))
            ->once();
    }

    public function test_rate_limit_diagnostics_record_upstream_headers_and_cached_cooldown(): void
    {
        Config::set('services.puptas.api_url', 'https://puptas.example/api/v1/webhooks/medical-result');
        Config::set('services.puptas.client_id', 'rate-limited-client-id');
        Config::set('services.puptas.client_secret', 'rate-limited-client-secret');
        Config::set('services.puptas.webhook_secret', 'webhook-secret');
        Config::set('services.puptas.token_url', 'https://puptas.example/oauth/token');

        Log::spy();

        Http::fake([
            'https://puptas.example/oauth/token' => Http::response(
                ['message' => 'Rate limited.'],
                429,
                [
                    'Retry-After' => '42',
                    'X-RateLimit-Limit' => '200',
                    'X-RateLimit-Remaining' => '0',
                ]
            ),
        ]);

        $firstResult = app(PuptasWebhookService::class)
            ->sendMedicalClearance('2026-0207-8804', 'idp-user-123', true);
        $secondResult = app(PuptasWebhookService::class)
            ->sendMedicalClearance('2026-0207-8804', 'idp-user-123', true);

        $this->assertFalse($firstResult['success']);
        $this->assertFalse($secondResult['success']);
        $this->assertSame(429, $firstResult['status']);
        $this->assertSame(429, $secondResult['status']);

        Log::shouldHaveReceived('warning')
            ->with('PUPTAS OAuth token endpoint returned a rate limit response', \Mockery::on(function (array $context): bool {
                return $context['status'] === 429
                    && $context['retry_after_seconds'] === 42
                    && $context['retry_after_source'] === 'upstream_header'
                    && $context['retry_after_header'] === '42'
                    && $context['x_rate_limit_limit'] === '200'
                    && $context['x_rate_limit_remaining'] === '0'
                    && $context['cooldown_cache_write_verified'] === true;
            }))
            ->once();

        Log::shouldHaveReceived('info')
            ->with('PUPTAS OAuth access token request blocked by cached cooldown', \Mockery::on(function (array $context): bool {
                return $context['retry_after_seconds'] >= 1
                    && $context['retry_after_seconds'] <= 42;
            }))
            ->once();
    }

    public function test_it_does_not_request_another_token_while_refresh_is_locked(): void
    {
        Config::set('services.puptas.api_url', 'https://puptas.example/api/v1/webhooks/medical-result');
        Config::set('services.puptas.client_id', 'locked-client-id');
        Config::set('services.puptas.client_secret', 'locked-client-secret');
        Config::set('services.puptas.webhook_secret', 'webhook-secret');
        Config::set('services.puptas.token_url', 'https://puptas.example/oauth/token');
        Config::set('services.puptas.scope', 'medical-read medical-write');
        Config::set('services.puptas.token_lock_seconds', 30);
        Config::set('services.puptas.token_lock_wait_seconds', 0);

        $tokenCacheKey = 'puptas.oauth_token.' . hash('sha256', implode('|', [
            'https://puptas.example/oauth/token',
            'locked-client-id',
            'medical-read medical-write',
        ]));
        $lock = Cache::lock($tokenCacheKey . '.refresh_lock', 30);

        $this->assertTrue($lock->get());
        Http::fake();

        try {
            $result = app(PuptasWebhookService::class)
                ->sendMedicalClearance('2026-0207-8804', 'idp-user-locked', true);
        } finally {
            $lock->release();
        }

        $this->assertFalse($result['success']);
        $this->assertSame(503, $result['status']);
        $this->assertStringContainsString('already in progress', $result['message']);
        Http::assertNothingSent();
    }
}
