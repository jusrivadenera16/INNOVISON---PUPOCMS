<?php

namespace Tests\Feature;

use App\Http\Controllers\Auth\EmergencyAuthController;
use App\Models\ActivityLog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use ReflectionMethod;
use Tests\TestCase;

class EmergencyAuditLogTest extends TestCase
{
    public function test_it_logs_pending_emergency_mfa_attempts_with_a_non_null_actor_name(): void
    {
        DB::beginTransaction();

        try {
            $session = app('session.store');
            $session->start();
            $session->put('emergency_mfa_pending_account', [
                'email' => 'audit-test@example.test',
            ]);

            $request = Request::create('/system-admin/emergency-login/verify', 'POST');
            $request->setLaravelSession($session);

            $controller = app(EmergencyAuthController::class);
            $method = new ReflectionMethod($controller, 'logAttempt');
            $method->setAccessible(true);
            $method->invoke($controller, $request, null, 'Emergency audit verification', 202);

            $entry = ActivityLog::query()
                ->where('description', 'Emergency audit verification')
                ->latest('id')
                ->first();

            $this->assertNotNull($entry);
            $this->assertSame('audit-test@example.test', $entry->user_name);
            $this->assertSame('audit-test@example.test', $entry->metadata['email']);
            $this->assertSame(202, $entry->status_code);
        } finally {
            DB::rollBack();
        }
    }
}
