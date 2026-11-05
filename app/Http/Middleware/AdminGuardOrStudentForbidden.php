<?php

namespace App\Http\Middleware;

use App\Models\User;
use Closure;
use Illuminate\Auth\Middleware\Authenticate;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AdminGuardOrStudentForbidden
{
    public function handle(Request $request, Closure $next)
    {
        if (Auth::guard('admin')->check()) {
            Auth::shouldUse('admin');

            return $next($request);
        }

        $studentUser = Auth::guard('student')->user();
        if ($studentUser && User::normalizeRole((string) $studentUser->user_role) === User::ROLE_STUDENT) {
            return response()->view('errors.403', [], 403);
        }

        return app(Authenticate::class)->handle($request, $next, 'admin');
    }
}
