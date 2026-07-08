<?php

namespace App\Http\Middleware;

use App\Models\User;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class EnsureIdpSessionIsActive
{
    private const VALIDATED_AT_SESSION_KEY = 'idp_last_validated_at';

    public function handle(Request $request, Closure $next)
    {
        if (!(bool) config('services.idp.enabled', false)) {
            return $next($request);
        }

        $user = $this->authenticatedUser();
        if (!$user instanceof User || $this->isEmergencyUser($user)) {
            return $next($request);
        }

        if ($this->recentlyValidated($request)) {
            return $next($request);
        }

        $cookieName = trim((string) config('services.idp.access_cookie_name', 'access_token'));
        $accessToken = $cookieName !== '' ? trim((string) $request->cookie($cookieName, '')) : '';

        if ($accessToken !== '' && $this->idpTokenStillValid($accessToken)) {
            $this->markValidated($request);
            return $next($request);
        }

        Log::info('Local clinic session cleared because the IDP session is no longer active.', [
            'user_id' => $user->id,
            'email' => $user->email,
            'path' => $request->path(),
            'has_access_cookie' => $accessToken !== '',
        ]);

        Auth::guard('admin')->logout();
        Auth::guard('student')->logout();
        Auth::guard('web')->logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        $response = redirect('/login?idp_error=1')->withErrors([
            'idp' => 'Your One Portal session has ended. Please sign in again.',
        ]);

        foreach (['access_cookie_name', 'refresh_cookie_name'] as $cookieKey) {
            $name = trim((string) config('services.idp.' . $cookieKey, ''));
            if ($name === '') {
                continue;
            }

            $response->cookie(
                $name,
                '',
                -60,
                '/',
                null,
                (bool) config('services.idp.cookie_secure', true),
                true,
                false,
                $this->normalizeSameSite((string) config('services.idp.cookie_same_site', 'Lax'))
            );
        }

        return $response;
    }

    private function recentlyValidated(Request $request): bool
    {
        $ttl = max(0, (int) config('services.idp.session_validation_cache_seconds', 300));
        if ($ttl === 0 || !$request->hasSession()) {
            return false;
        }

        $validatedAt = (int) $request->session()->get(self::VALIDATED_AT_SESSION_KEY, 0);
        if ($validatedAt <= 0) {
            return false;
        }

        return now()->timestamp - $validatedAt <= $ttl;
    }

    private function markValidated(Request $request): void
    {
        if ($request->hasSession()) {
            $request->session()->put(self::VALIDATED_AT_SESSION_KEY, now()->timestamp);
        }
    }

    private function authenticatedUser(): ?User
    {
        foreach (['admin', 'student', 'web'] as $guard) {
            $user = Auth::guard($guard)->user();
            if ($user instanceof User) {
                return $user;
            }
        }

        return null;
    }

    private function isEmergencyUser(User $user): bool
    {
        $emergencyEmail = strtolower(trim((string) config('services.emergency.email', '')));
        $email = strtolower(trim((string) $user->email));
        $studentId = strtolower(trim((string) $user->student_id));

        return ($emergencyEmail !== '' && $email === $emergencyEmail)
            || $studentId === 'emergency-admin';
    }

    private function idpTokenStillValid(string $accessToken): bool
    {
        $profilePaths = (array) config('services.idp.profile_paths', []);
        foreach ($profilePaths as $path) {
            $url = $this->idpUrl((string) $path);
            if ($url === null) {
                continue;
            }

            try {
                $response = Http::acceptJson()->timeout(10)->withToken($accessToken)->get($url);
                if ($response->successful() && $this->hasIdentityPayload($response->json())) {
                    return true;
                }
            } catch (\Throwable $e) {
                Log::warning('IDP session profile validation failed.', [
                    'endpoint' => $url,
                    'message' => $e->getMessage(),
                ]);
            }
        }

        $validatePath = trim((string) config('services.idp.validate_token_path', ''));
        $validateUrl = $this->idpUrl($validatePath);
        if ($validateUrl === null) {
            return false;
        }

        try {
            $response = Http::acceptJson()->timeout(10)->post($validateUrl, [
                'token' => $accessToken,
            ]);

            return $response->successful() && $this->hasIdentityPayload($response->json());
        } catch (\Throwable $e) {
            Log::warning('IDP session token validation failed.', [
                'endpoint' => $validateUrl,
                'message' => $e->getMessage(),
            ]);

            return false;
        }
    }

    private function idpUrl(string $path): ?string
    {
        $path = trim($path);
        $baseUrl = rtrim((string) config('services.idp.base_url', ''), '/');

        if ($path === '' || $baseUrl === '') {
            return null;
        }

        return $baseUrl . '/' . ltrim($path, '/');
    }

    private function hasIdentityPayload(mixed $payload): bool
    {
        if (!is_array($payload)) {
            return false;
        }

        foreach (['id', 'email', 'first_name', 'last_name', 'data.id', 'data.email', 'user.id', 'user.email'] as $key) {
            $value = data_get($payload, $key);
            if (is_scalar($value) && trim((string) $value) !== '') {
                return true;
            }
        }

        return false;
    }

    private function normalizeSameSite(string $sameSite): ?string
    {
        $sameSite = strtolower(trim($sameSite));

        return match ($sameSite) {
            'none' => 'None',
            'strict' => 'Strict',
            'lax' => 'Lax',
            default => null,
        };
    }
}
