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
    if ((bool) config('services.idp.enabled', false)) {
        abort(403, 'Local registration is disabled while centralized login is enabled.');
    }

    $request->validate([
        'first_name' => 'required|string|max:255',
        'middle_name' => 'nullable|string|max:255',
        'last_name'  => 'required|string|max:255',
        'suffix_name' => 'nullable|string|max:50',
        'email'      => 'required|email|unique:users',
        'clinic_role' => 'required|string|in:applicant,student,faculty,admin,guest',
        'password'   => 'required|min:6|confirmed',
    ]);

    $selectedRole = (string) $request->input('clinic_role');
    $isStudentSideUser = in_array($selectedRole, ['applicant', 'student', 'faculty', 'guest'], true);
    $isClinicAdmin = $selectedRole === 'admin';
    $userRole = $isStudentSideUser ? User::ROLE_STUDENT : User::ROLE_ADMIN;

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
            $request->input('suffix_name'),
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

    if (Schema::hasColumn('users', 'suffix_name')) {
        $payload['suffix_name'] = $request->input('suffix_name');
    }

    if (Schema::hasColumn('users', 'user_type')) {
        $payload['user_type'] = match (true) {
            $selectedRole === 'applicant' => 'Applicant',
            $selectedRole === 'student' => 'Student',
            $selectedRole === 'faculty' => 'Faculty',
            $selectedRole === 'guest' => 'Guest',
            default => 'Admin',
        };
    }

    $user = User::create($payload);

    if ($isClinicAdmin && Schema::hasTable('admins')) {
        $adminPayload = [
            'first_name' => $user->first_name,
            'middle_name' => $user->middle_name,
            'last_name' => $user->last_name,
            'name' => $user->name,
            'email' => $user->email,
            'access_level' => 'clinic_staff',
        ];

        if (Admin::hasColumn('suffix_name')) {
            $adminPayload['suffix_name'] = $user->suffix_name;
        }

        if (Admin::hasColumn('user_id')) {
            $adminPayload['user_id'] = $user->id;
        }

        if (Admin::hasColumn('email_address')) {
            $adminPayload['email_address'] = $user->email;
        }

        Admin::create($adminPayload);
    }

    $guard = $isStudentSideUser ? 'student' : 'admin';
    Auth::shouldUse($guard);
    Auth::guard($guard)->login($user);
    $request->session()->regenerate();

    if ($isStudentSideUser) {
        return redirect('/student/home');
    }

    return redirect('/assistant/dashboard');
}
}
