<?php

namespace App\Http\Middleware;

use App\Models\User;
use Closure;
use Illuminate\Http\Request;

class EnsureClinicAccountType
{
    public function handle(Request $request, Closure $next)
    {
        $user = $request->user('student');
        if (!$user instanceof User || User::normalizeRole($user->user_role) !== User::ROLE_STUDENT) {
            return $next($request);
        }

        if ($user->needsClinicAccountTypeSelection()) {
            if ($request->expectsJson()) {
                return response()->json(['message' => 'Please select your clinic account type.', 'redirect' => route('student.home')], 409);
            }

            return redirect()->route('student.home')->with('show_health_profile_prompt', true);
        }

        $audience = $user->clinicHealthFormAudience();
        $expected = match ($request->route()->getName()) {
            'health.form', 'store.health.form', 'store.health.form.fallback' => 'applicant',
            'health.form.student', 'store.health.form.student' => 'student',
            'health.form.employee', 'store.health.form.employee', 'health.form.staff', 'store.health.form.staff' => 'employee',
            'dependent.profile.form', 'dependent.profile.store' => 'dependent',
            default => null,
        };
        if ($expected !== null && $audience !== $expected) {
            return redirect()->route($user->clinicHealthFormRoute());
        }

        return $next($request);
    }
}
