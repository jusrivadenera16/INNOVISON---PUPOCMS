<?php

namespace Tests\Unit;

use App\Http\Controllers\AdminAssistantController;
use Illuminate\Http\Request;
use Tests\TestCase;

class AdminAssistantControllerScopeTest extends TestCase
{
    public function test_it_refuses_non_clinic_coding_requests(): void
    {
        $response = app(AdminAssistantController::class)->handle(
            Request::create('/admin/assistant/intent', 'POST', [
                'text' => 'create a basic python code',
            ])
        );

        $payload = $response->getData(true);

        $this->assertSame('answer', $payload['type']);
        $this->assertSame('scope_guard', $payload['source']);
        $this->assertStringContainsString('medical, health, and clinic-system requests', $payload['message']);
    }

    public function test_it_explains_its_clinic_scope_for_help_requests(): void
    {
        $response = app(AdminAssistantController::class)->handle(
            Request::create('/admin/assistant/intent', 'POST', [
                'text' => 'what can you do',
            ])
        );

        $payload = $response->getData(true);

        $this->assertSame('answer', $payload['type']);
        $this->assertSame('local', $payload['source']);
        $this->assertStringContainsString('clinic workflows', $payload['message']);
        $this->assertStringContainsString('basic symptom triage', $payload['message']);
    }
}
