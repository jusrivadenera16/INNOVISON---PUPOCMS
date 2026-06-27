<?php

namespace App\Http\Middleware;

use App\Models\User;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class RedirectIfAuthenticated
{
    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next, ...$guards)
    {
        $guards = empty($guards) ? [null] : $guards;

        foreach ($guards as $guard) {
            $user = Auth::guard($guard)->user();
            if ($user instanceof User) {
                return redirect($this->getDashboardRoute($user));
            }
        }

        return $next($request);
    }

    private function getDashboardRoute(User $user): string
    {
        $normalizedRole = User::normalizeRole((string) ($user->user_role ?? ''));

        if ($normalizedRole === User::ROLE_SUPERADMIN) {
            return '/admin/dashboard';
        }

        $rawRole = strtolower(trim((string) ($user->user_role ?? '')));
        $userType = strtolower(trim((string) ($user->user_type ?? '')));
        $isStudentAssistant = in_array($userType, ['assistant', 'student assistant', 'student_assistant'], true)
            || in_array($rawRole, ['student_assistant', 'studentassistant', 'assistant'], true);

        if ($normalizedRole === User::ROLE_ADMIN && $isStudentAssistant) {
            return '/assistant/choose-portal';
        }

        return match ($normalizedRole) {
            User::ROLE_ADMIN => '/student/home',
            default => '/student/home',
        };
    }
}
