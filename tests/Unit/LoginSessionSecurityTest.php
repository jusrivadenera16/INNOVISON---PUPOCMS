<?php

namespace Tests\Unit;

use App\Http\Controllers\Auth\LoginController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\URL;
use ReflectionMethod;
use Tests\TestCase;

class LoginSessionSecurityTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        Config::set('app.url', 'http://localhost');
        Config::set('services.idp.access_cookie_name', 'access_token');
        Config::set('services.idp.refresh_cookie_name', 'refresh_token');
        Config::set('services.idp.cookie_secure', false);
        Config::set('services.idp.cookie_same_site', 'Lax');
        Config::set('services.idp.logout_url', '');
        URL::forceRootUrl('http://localhost');
    }

    protected function tearDown(): void
    {
        Auth::guard('admin')->logout();
        Auth::guard('student')->logout();
        Auth::guard('web')->logout();

        parent::tearDown();
    }

    public function test_student_idp_cookies_are_session_cookies_when_close_behavior_is_enabled(): void
    {
        $response = redirect('/student/home');
        $method = new ReflectionMethod(LoginController::class, 'attachIdpCookies');
        $method->setAccessible(true);

        $method->invoke(new LoginController(), $response, 'access-token', 'refresh-token', true);

        foreach ($response->headers->getCookies() as $cookie) {
            $this->assertSame(0, $cookie->getExpiresTime());
            $this->assertTrue($cookie->isHttpOnly());
            $this->assertSame('lax', $cookie->getSameSite());
        }
    }

    public function test_explicit_logout_invalidates_the_local_session(): void
    {
        $request = Request::create('/logout', 'POST');
        $request->setLaravelSession(app('session.store'));

        if (!app('session.store')->isStarted()) {
            app('session.store')->start();
        }

        $request->session()->put('logout-test-sentinel', 'present');

        $response = (new LoginController())->logout($request);

        $this->assertSame(302, $response->getStatusCode());
        $this->assertFalse($request->session()->has('logout-test-sentinel'));
        $this->assertGuest('admin');
        $this->assertGuest('student');
    }
}
