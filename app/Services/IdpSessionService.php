<?php

namespace App\Services;

use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class IdpSessionService
{
    public function logout(?string $accessToken): void
    {
        $logoutUrl = trim((string) config('services.idp.logout_url', ''));
        $token = trim((string) $accessToken);

        if ($logoutUrl === '' || $token === '') {
            return;
        }

        try {
            Http::withToken($token)->post($logoutUrl, [
                'client_id' => config('services.idp.client_id'),
            ]);
        } catch (\Throwable $exception) {
            Log::error('IDP Logout API call failed: ' . $exception->getMessage());
        }
    }

    public function clearCookies(RedirectResponse $response): RedirectResponse
    {
        $secure = (bool) config('services.idp.cookie_secure', true);
        $sameSite = $this->normalizeSameSite();

        foreach (['access_cookie_name', 'refresh_cookie_name'] as $cookieKey) {
            $cookieName = trim((string) config('services.idp.' . $cookieKey, ''));
            if ($cookieName === '') {
                continue;
            }

            $response->cookie(
                $cookieName,
                '',
                -60,
                '/',
                null,
                $secure,
                true,
                false,
                $sameSite
            );
        }

        return $response;
    }

    private function normalizeSameSite(): string
    {
        $sameSite = strtolower(trim((string) config('services.idp.cookie_same_site', 'lax')));

        return in_array($sameSite, ['lax', 'strict', 'none'], true) ? $sameSite : 'lax';
    }
}
