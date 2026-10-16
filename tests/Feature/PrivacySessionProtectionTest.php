<?php

namespace Tests\Feature;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use Tests\TestCase;

class PrivacySessionProtectionTest extends TestCase
{
    public function test_session_data_is_encrypted_by_default(): void
    {
        $this->assertTrue(config('session.encrypt'));
    }

    public function test_health_form_validation_does_not_flash_submitted_answers(): void
    {
        Route::post('/_test-health-form-validation', function (Request $request) {
            $request->validate(['health_answer' => ['required', 'in:allowed']]);

            return response()->noContent();
        })->name('store.health.form.privacy_test');

        $this->from('/')->post('/_test-health-form-validation', [
            'health_answer' => 'sensitive test value',
        ])
            ->assertRedirect('/')
            ->assertSessionHasErrors('health_answer')
            ->assertSessionMissing('_old_input');
    }
}
