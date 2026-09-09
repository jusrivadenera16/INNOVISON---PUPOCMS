<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

class ClinicAccountTypeController extends Controller
{
    public function options(Request $request)
    {
        $user = $request->user('student');
        abort_unless($user instanceof User && User::normalizeRole($user->user_role) === User::ROLE_STUDENT, 403);

        return response()->json(['allowed_types' => $user->allowedClinicAccountTypes()]);
    }

    public function store(Request $request)
    {
        $user = $request->user('student');
        abort_unless($user instanceof User && User::normalizeRole($user->user_role) === User::ROLE_STUDENT, 403);
        $validated = $request->validate([
            'clinic_account_type' => ['required', Rule::in(array_keys(User::CLINIC_ACCOUNT_TYPES))],
        ]);

        $user = DB::transaction(function () use ($user, $validated) {
            $user = User::query()->lockForUpdate()->findOrFail($user->id);
            abort_unless(User::normalizeRole($user->user_role) === User::ROLE_STUDENT, 403);
            $type = $validated['clinic_account_type'];
            if ($user->hasPendingAdmissionReference() && $type !== 'applicant') {
                throw ValidationException::withMessages([
                    'clinic_account_type' => 'Your admission reference is awaiting clearance. Please continue as an applicant.',
                ]);
            }
            if (!$user->needsClinicAccountTypeSelection() && $user->clinicAccountTypeKey() !== $type) {
                throw ValidationException::withMessages([
                    'clinic_account_type' => 'Your account type is already saved. Contact the clinic to request a correction.',
                ]);
            }
            if ($user->needsClinicAccountTypeSelection() && !in_array($type, $user->allowedClinicAccountTypes(), true)) {
                throw ValidationException::withMessages([
                    'clinic_account_type' => 'This account type is not available for your account.',
                ]);
            }

            $user->user_type = User::userTypeForClinicAccountType($type);
            $user->save();

            return $user;
        });

        return $request->expectsJson()
            ? response()->json(['redirect' => route($user->clinicHealthFormRoute())])
            : redirect()->route($user->clinicHealthFormRoute());
    }
}
