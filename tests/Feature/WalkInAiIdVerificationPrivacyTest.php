<?php

namespace Tests\Feature;

use App\Http\Controllers\WalkInController;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Route;
use Tests\TestCase;

class WalkInAiIdVerificationPrivacyTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        Config::set('services.ai.external_processing_enabled', true);
        Route::post('/_test-walkin-ai-id-verification', [WalkInController::class, 'verifyStudentIdWithAi']);
    }

    public function test_ai_id_verification_rejects_oversized_image_data_before_external_request(): void
    {
        Http::fake();

        $this->postJson('/_test-walkin-ai-id-verification', [
            'image_data' => 'data:image/jpeg;base64,' . str_repeat('A', 6000001),
        ])->assertUnprocessable();

        Http::assertNothingSent();
    }

    public function test_ai_id_verification_rejects_non_raster_data_uris(): void
    {
        Http::fake();

        $this->postJson('/_test-walkin-ai-id-verification', [
            'image_data' => 'data:image/svg+xml;base64,PHN2Zz48L3N2Zz4=',
        ])->assertUnprocessable()
            ->assertJsonPath('status', 'invalid_image');

        Http::assertNothingSent();
    }

    public function test_ai_id_verification_accepts_a_small_valid_png(): void
    {
        Http::fake();
        Config::set('services.ai.external_processing_enabled', true);
        Config::set('services.openai.api_key', '');

        $this->postJson('/_test-walkin-ai-id-verification', [
            'image_data' => 'data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAQAAAC1HAwCAAAAC0lEQVR42mP8/x8AAwMCAO+jf2sAAAAASUVORK5CYII=',
        ])->assertUnprocessable()
            ->assertJsonPath('status', 'unavailable');

        Http::assertNothingSent();
    }

    public function test_ai_id_verification_is_disabled_without_external_processing_approval(): void
    {
        Http::fake();
        Config::set('services.ai.external_processing_enabled', false);

        $this->postJson('/_test-walkin-ai-id-verification', [
            'image_data' => 'data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAQAAAC1HAwCAAAAC0lEQVR42mP8/x8AAwMCAO+jf2sAAAAASUVORK5CYII=',
        ])->assertStatus(503)
            ->assertJsonPath('status', 'external_processing_disabled');

        Http::assertNothingSent();
    }

    public function test_ai_id_verification_route_is_rate_limited(): void
    {
        foreach (['walkin.verify-id-ai', 'assistant.walkin.verify-id-ai'] as $routeName) {
            $route = Route::getRoutes()->getByName($routeName);

            $this->assertNotNull($route);
            $this->assertContains('throttle:10,1', $route->gatherMiddleware());
        }
    }
}
