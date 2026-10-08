<?php

namespace App\Http\Middleware;

use App\Services\IdpSessionService;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class EnsureStudentSessionIdle
{
    private const LAST_ACTIVITY_SESSION_KEY = 'student_last_activity_at';

    public function __construct(private IdpSessionService $idpSession)
    {
    }

    public function handle(Request $request, Closure $next)
    {
        if (!$request->hasSession() || !Auth::guard('student')->check()) {
            return $next($request);
        }

        $now = now()->timestamp;
        $lastActivity = (int) $request->session()->get(self::LAST_ACTIVITY_SESSION_KEY, $now);
        $timeoutMinutes = max(1, (int) config('session.student_idle_timeout', 15));
        $timeoutSeconds = $timeoutMinutes * 60;

        if ($lastActivity > 0 && ($now - $lastActivity) >= $timeoutSeconds) {
            $this->expireSession($request);

            return $this->idpSession->clearCookies(
                redirect('/login?session_expired=1')->withErrors([
                    'session' => "Your student session expired after {$timeoutMinutes} minutes of inactivity. Please sign in again.",
                ])
            );
        }

        $request->session()->put(self::LAST_ACTIVITY_SESSION_KEY, $now);

        return $next($request);
    }

    private function expireSession(Request $request): void
    {
        $user = Auth::guard('student')->user();
        $accessCookieName = trim((string) config('services.idp.access_cookie_name', 'access_token'));
        $accessToken = $accessCookieName !== '' ? $request->cookie($accessCookieName) : null;

        $this->idpSession->logout($accessToken);

        if ($user) {
            Log::info('Student session expired after inactivity.', [
                'user_id' => $user->getAuthIdentifier(),
                'path' => $request->path(),
            ]);
        }

        Auth::guard('admin')->logout();
        Auth::guard('student')->logout();
        Auth::guard('web')->logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();
    }
}
