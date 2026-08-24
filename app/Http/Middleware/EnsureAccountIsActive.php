<?php

namespace App\Http\Middleware;

use App\Models\HealthProfile;
use App\Models\User;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class EnsureAccountIsActive
{
    public function handle(Request $request, Closure $next)
    {
        $user = $this->authenticatedUser();

        if (!$user || !$this->isBlocked($user)) {
            return $next($request);
        }

        foreach (['admin', 'student', 'web'] as $guard) {
            Auth::guard($guard)->logout();
        }

        if ($request->hasSession()) {
            $request->session()->invalidate();
            $request->session()->regenerateToken();
        }

        return redirect('/login?account_inactive=1')->withErrors([
            'account' => 'Your clinic system access is inactive. Please contact the clinic administrator.',
        ]);
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

    private function isBlocked(User $user): bool
    {
        if (strtolower(trim((string) ($user->status ?? 'active'))) === 'inactive') {
            return true;
        }

        return $user->healthProfile()
            ->where('pullout_status', HealthProfile::PULLOUT_COMPLETED)
            ->exists();
    }
}
