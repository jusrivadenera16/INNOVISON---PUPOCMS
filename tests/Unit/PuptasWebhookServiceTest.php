<?php

namespace Tests\Unit;

use App\Services\PuptasWebhookService;
use Illuminate\Http\Client\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Http;
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
    }
}
