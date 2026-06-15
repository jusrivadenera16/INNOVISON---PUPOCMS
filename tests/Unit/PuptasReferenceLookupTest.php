<?php

namespace Tests\Unit;

use App\Services\PuptasWebhookService;
use Illuminate\Http\Client\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class PuptasReferenceLookupTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        config([
            'services.puptas.api_url' => 'https://puptas.example.test/api/v1/webhooks/medical-result',
            'services.puptas.token_url' => 'https://puptas.example.test/oauth/token',
            'services.puptas.client_id' => 'clinic-client',
            'services.puptas.client_secret' => 'clinic-secret',
            'services.puptas.scope' => 'medical-read medical-write',
            'services.puptas.timeout' => 20,
        ]);

        Cache::flush();
    }

    public function test_it_looks_up_an_applicant_using_the_reference_number_endpoint(): void
    {
        Http::fake([
            'https://puptas.example.test/oauth/token' => Http::response([
                'access_token' => 'test-token',
                'expires_in' => 3600,
            ]),
            'https://puptas.example.test/api/v1/medical/applicants/2026-1111-1111' => Http::response([
                'data' => [
                    'idp_user_id' => '5c26bd95-eaee-4931-9706-039931efecd5',
                    'reference_number' => '2026-1111-1111',
                    'firstname' => 'The',
                    'lastname' => 'Tester',
                    'email' => 'dummyjm15@gmail.com',
                ],
            ]),
        ]);

        $result = app(PuptasWebhookService::class)
            ->fetchApplicantByReferenceNumberDetailed('2026-1111-1111');

        $this->assertTrue($result['success']);
        $this->assertSame('2026-1111-1111', $result['data']['reference_number']);

        Http::assertSent(fn ($request) =>
            $request->method() === 'GET'
            && $request->url() === 'https://puptas.example.test/api/v1/medical/applicants/2026-1111-1111'
            && $request->hasHeader('Authorization', 'Bearer test-token')
        );
    }

    public function test_the_old_method_name_remains_a_compatible_alias(): void
    {
        Http::fake([
            'https://puptas.example.test/oauth/token' => Http::response([
                'access_token' => 'test-token',
                'expires_in' => 3600,
            ]),
            'https://puptas.example.test/api/v1/medical/applicants/2026-1111-1111' => Http::response([
                'data' => ['reference_number' => '2026-1111-1111'],
            ]),
        ]);

        $result = app(PuptasWebhookService::class)
            ->fetchApplicantByStudentNumberDetailed('2026-1111-1111');

        $this->assertTrue($result['success']);
        $this->assertSame('2026-1111-1111', $result['data']['reference_number']);
    }

    public function test_it_refreshes_the_oauth_token_after_an_unauthorized_lookup(): void
    {
        $tokenRequests = 0;
        $lookupRequests = 0;

        Http::fake(function (Request $request) use (&$tokenRequests, &$lookupRequests) {
            if ($request->url() === 'https://puptas.example.test/oauth/token') {
                $tokenRequests++;

                return Http::response([
                    'access_token' => $tokenRequests === 1 ? 'expired-token' : 'fresh-token',
                    'expires_in' => 3600,
                ]);
            }

            if ($request->url() === 'https://puptas.example.test/api/v1/medical/applicants/2026-8889-8828') {
                $lookupRequests++;

                if ($request->hasHeader('Authorization', 'Bearer expired-token')) {
                    return Http::response(['message' => 'Unauthenticated.'], 401);
                }

                return Http::response([
                    'data' => [
                        'idp_user_id' => 'idp-user-123',
                        'reference_number' => '2026-8889-8828',
                        'first_name' => 'Lofi',
                        'last_name' => 'Nuko',
                    ],
                ]);
            }

            return Http::response([], 404);
        });

        $result = app(PuptasWebhookService::class)
            ->fetchApplicantByReferenceNumberDetailed('2026-8889-8828');

        $this->assertTrue($result['success']);
        $this->assertSame('2026-8889-8828', $result['data']['reference_number']);
        $this->assertSame(2, $tokenRequests);
        $this->assertSame(2, $lookupRequests);
    }
}
