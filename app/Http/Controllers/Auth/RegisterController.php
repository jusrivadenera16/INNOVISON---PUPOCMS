<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\Admin;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

class RegisterController extends Controller
{
    public function register(Request $request) {
    abort_unless(app()->environment('local'), 404);

    if ((bool) config('services.idp.enabled', false)) {
        abort(403, 'Local registration is disabled while centralized login is enabled.');
    }

    $request->validate([
        'first_name' => 'required|string|max:255',
        'middle_name' => 'nullable|string|max:255',
        'last_name'  => 'required|string|max:255',
        'email'      => 'required|email|unique:users',
        'clinic_role' => 'required|string|in:student,faculty,guest,admin_clinic_staff,admin_designee,student_assistant,super_admin',
        'password'   => 'required|min:6|confirmed',
    ]);

    $selectedRole = (string) $request->input('clinic_role');
    $isStudentSideUser = in_array($selectedRole, ['student', 'faculty', 'guest'], true);
    $isClinicAdmin = $selectedRole === 'admin_clinic_staff';
    $isAdminDesignee = $selectedRole === 'admin_designee';
    $isStudentAssistant = $selectedRole === 'student_assistant';
    $userRole = match (true) {
        $selectedRole === 'super_admin' => User::ROLE_SUPERADMIN,
        $isStudentSideUser => User::ROLE_STUDENT,
        default => User::ROLE_ADMIN,
    };

    $studentId = 'LOC-' . strtoupper(Str::random(10));
    while (User::where('student_id', $studentId)->exists()) {
        $studentId = 'LOC-' . strtoupper(Str::random(10));
    }

    $payload = [
        'first_name' => $request->first_name,
        'middle_name' => $request->input('middle_name'),
        'last_name'  => $request->last_name,
        'name'       => trim(implode(' ', array_filter([
            $request->first_name,
            $request->input('middle_name'),
            $request->last_name,
        ]))),
        'student_id' => $studentId,
        'email'      => $request->email,
        'DOB'        => null,
        'course'     => null,
        'year'       => null,
        'section'    => null,
        'user_role'  => $userRole,
        'status'     => 'active',
        'password'   => Hash::make($request->password),
    ];

    if (Schema::hasColumn('users', 'user_type')) {
        $payload['user_type'] = match (true) {
            $selectedRole === 'student' => 'Student',
            $selectedRole === 'faculty' => 'Faculty',
            $selectedRole === 'guest' => 'Guest',
            $isStudentAssistant => 'Assistant',
            default => 'Regular',
        };
    }

    $user = User::create($payload);

    if (($isClinicAdmin || $isAdminDesignee || $selectedRole === 'super_admin') && Schema::hasTable('admins')) {
        $adminPayload = [
            'first_name' => $user->first_name,
            'middle_name' => $user->middle_name,
            'last_name' => $user->last_name,
            'name' => $user->name,
            'email' => $user->email,
            'access_level' => match (true) {
                $selectedRole === 'super_admin' => 'superadmin',
                $isAdminDesignee => 'designee',
                default => 'clinic_staff',
            },
        ];

        if (Admin::hasColumn('user_id')) {
            $adminPayload['user_id'] = $user->id;
        }

        if (Admin::hasColumn('email_address')) {
            $adminPayload['email_address'] = $user->email;
        }

        if (Admin::hasColumn('admin_hub_role')) {
            $adminPayload['admin_hub_role'] = match (true) {
                $selectedRole === 'super_admin' => 'super_admin',
                $isAdminDesignee => 'admin_designee',
                default => 'admin_clinic_staff',
            };
        }

        if (Admin::hasColumn('admin_hub_enabled')) {
            $adminPayload['admin_hub_enabled'] = $selectedRole === 'super_admin';
        }

        Admin::create($adminPayload);
    }

    $guard = $isStudentSideUser ? 'student' : 'admin';
    Auth::shouldUse($guard);
    Auth::guard($guard)->login($user);
    $request->session()->regenerate();

    if ($isStudentSideUser || $isAdminDesignee) {
        return redirect('/student/home');
    }

    if ($userRole === User::ROLE_SUPERADMIN) {
        return redirect('/admin/dashboard');
    }

    if ($isStudentAssistant) {
        return redirect('/assistant/choose-portal');
    }

    return redirect('/assistant/dashboard');
}
}
