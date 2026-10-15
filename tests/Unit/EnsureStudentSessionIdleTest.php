<?php

namespace Tests\Unit;

use App\Http\Middleware\EnsureStudentSessionIdle;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Config;
use Tests\TestCase;

class EnsureStudentSessionIdleTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        Config::set('session.student_idle_timeout', 30);
        Config::set('services.idp.logout_url', '');
        Config::set('services.idp.cookie_secure', false);
        Auth::guard('student')->setUser($this->makeUser());
    }

    protected function tearDown(): void
    {
        Auth::guard('admin')->logout();
        Auth::guard('student')->logout();
        Auth::guard('web')->logout();
        Carbon::setTestNow();

        parent::tearDown();
    }

    public function test_active_student_request_refreshes_the_idle_marker(): void
    {
        Carbon::setTestNow('2026-09-30 10:00:00');
        $request = $this->requestWithSession();
        $request->session()->put('student_last_activity_at', now()->subMinutes(29)->timestamp);

        $response = app(EnsureStudentSessionIdle::class)->handle(
            $request,
            fn () => response('allowed')
        );

        $this->assertSame(200, $response->getStatusCode());
        $this->assertSame(now()->timestamp, $request->session()->get('student_last_activity_at'));
        $this->assertAuthenticated('student');
    }

    public function test_notification_poll_does_not_refresh_the_idle_marker(): void
    {
        Carbon::setTestNow('2026-09-30 10:00:00');
        $request = $this->requestWithSession('/student/notifications/feed');
        $lastActivity = now()->subMinutes(29)->timestamp;
        $request->session()->put('student_last_activity_at', $lastActivity);

        $response = app(EnsureStudentSessionIdle::class)->handle(
            $request,
            fn () => response('allowed')
        );

        $this->assertSame(200, $response->getStatusCode());
        $this->assertSame($lastActivity, $request->session()->get('student_last_activity_at'));
        $this->assertAuthenticated('student');
    }

    public function test_student_is_logged_out_after_thirty_minutes_of_inactivity(): void
    {
        Carbon::setTestNow('2026-09-30 10:30:00');
        $request = $this->requestWithSession('/student/health-record/document/health_form');
        $request->session()->put('student_last_activity_at', now()->subMinutes(30)->timestamp);

        $response = app(EnsureStudentSessionIdle::class)->handle(
            $request,
            fn () => response('should not run')
        );

        $this->assertSame(302, $response->getStatusCode());
        $this->assertSame(url('/login?session_expired=1'), $response->getTargetUrl());
        $this->assertGuest('student');
        $this->assertFalse($request->session()->has('student_last_activity_at'));
    }

    public function test_without_a_student_session_the_request_is_untouched(): void
    {
        Carbon::setTestNow('2026-09-30 10:30:00');
        Auth::guard('student')->logout();
        $request = $this->requestWithSession();
        $request->session()->put('student_last_activity_at', now()->subHours(2)->timestamp);

        $response = app(EnsureStudentSessionIdle::class)->handle(
            $request,
            fn () => response('allowed')
        );

        $this->assertSame(200, $response->getStatusCode());
        $this->assertSame(now()->subHours(2)->timestamp, $request->session()->get('student_last_activity_at'));
    }

    private function requestWithSession(string $path = '/student/account'): Request
    {
        $request = Request::create($path, 'GET');
        $request->setLaravelSession(app('session.store'));

        if (!app('session.store')->isStarted()) {
            app('session.store')->start();
        }

        return $request;
    }

    private function makeUser(): User
    {
        $user = new User();
        $user->id = 123;
        $user->email = 'student@example.test';

        return $user;
    }
}
