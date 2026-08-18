<?php

namespace App\Http\Middleware;

use App\Models\Admin;
use App\Models\AdminHub;
use App\Models\User;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Schema;

class RoleMiddleware
{
    public function handle(Request $request, Closure $next, string ...$roles)
    {
        if (!Auth::check()) {
            abort(403, 'Unauthorized');
        }

        $user = Auth::user();
        $currentRole = User::normalizeRole(Auth::user()->user_role ?? '');
        $allowedRoles = array_map(function ($role) {
            return User::normalizeRole((string) $role);
        }, $roles);

        if ($this->hasRoleAccess($user, $currentRole, $allowedRoles)) {
            return $next($request);
        }

        abort(403, 'Unauthorized');
    }

    private function hasRoleAccess($user, string $currentRole, array $allowedRoles): bool
    {
        if (in_array($currentRole, $allowedRoles, true)) {
            if ($currentRole === User::ROLE_ADMIN && in_array(User::ROLE_ADMIN, $allowedRoles, true)) {
                return $this->isClinicStaffAdmin($user);
            }

            return true;
        }

        if (in_array(User::ROLE_STUDENT, $allowedRoles, true) && $this->isDesigneeAdmin($user)) {
            return true;
        }

        if (in_array(User::ROLE_STUDENT, $allowedRoles, true) && $this->isStudentAssistantPortalUser($user)) {
            return true;
        }

        if (in_array(User::ROLE_STUDENT, $allowedRoles, true) && $this->isAdminRegularStudentSideUser($user)) {
            return true;
        }

        return false;
    }

    private function isDesigneeAdmin($user): bool
    {
        if (!$user || User::normalizeRole((string) ($user->user_role ?? '')) !== User::ROLE_ADMIN) {
            return false;
        }

        $linkedAdmin = $this->findLinkedAdminProfile($user);
        $accessLevel = strtolower(trim((string) ($linkedAdmin?->access_level ?? '')));
        $linkedAdminHub = $this->findLinkedAdminHubProfile($user);
        $adminHubRole = strtolower(trim((string) ($linkedAdminHub?->role ?? '')));
        $adminHubStatus = strtolower(trim((string) ($linkedAdminHub?->status ?? 'active')));

        if (in_array($accessLevel, ['clinic_staff', 'clinic staff', 'staff', 'superadmin'], true)) {
            return false;
        }

        if ($linkedAdminHub && in_array($adminHubRole, ['designee', 'admin_designee'], true)) {
            return $adminHubStatus !== 'inactive';
        }

        return false;
    }

    private function isClinicStaffAdmin($user): bool
    {
        if (!$user || User::normalizeRole((string) ($user->user_role ?? '')) !== User::ROLE_ADMIN) {
            return false;
        }

        $linkedAdmin = $this->findLinkedAdminProfile($user);
        $accessLevel = strtolower(trim((string) ($linkedAdmin?->access_level ?? '')));

        return in_array($accessLevel, ['clinic_staff', 'clinic staff', 'staff'], true);
    }

    private function isStudentAssistantPortalUser($user): bool
    {
        if (!$user || User::normalizeRole((string) ($user->user_role ?? '')) !== User::ROLE_ADMIN) {
            return false;
        }

        $userType = strtolower(trim((string) ($user->user_type ?? '')));

        return in_array($userType, ['assistant', 'student assistant', 'student_assistant'], true);
    }

    private function isAdminRegularStudentSideUser($user): bool
    {
        if (!$user || User::normalizeRole((string) ($user->user_role ?? '')) !== User::ROLE_ADMIN) {
            return false;
        }

        $userType = strtolower(trim((string) ($user->user_type ?? '')));
        if (in_array($userType, ['assistant', 'student assistant', 'student_assistant'], true)) {
            return false;
        }

        $linkedAdmin = $this->findLinkedAdminProfile($user);
        $accessLevel = strtolower(trim((string) ($linkedAdmin?->access_level ?? '')));
        $linkedAdminHub = $this->findLinkedAdminHubProfile($user);
        $adminHubRole = strtolower(trim((string) ($linkedAdminHub?->role ?? '')));
        $adminHubStatus = strtolower(trim((string) ($linkedAdminHub?->status ?? 'active')));

        if (in_array($accessLevel, ['clinic_staff', 'clinic staff', 'staff', 'superadmin'], true)) {
            return false;
        }

        if ($linkedAdminHub && $adminHubStatus !== 'inactive' && in_array($adminHubRole, ['designee', 'admin_designee'], true)) {
            return false;
        }

        return true;
    }

    private function findLinkedAdminHubProfile($user): ?AdminHub
    {
        if (!$user || !Schema::hasTable('admin_hub')) {
            return null;
        }

        if (AdminHub::hasColumn('user_id')) {
            $linkedByUserId = AdminHub::query()
                ->where('user_id', $user->id)
                ->first();

            if ($linkedByUserId) {
                return $linkedByUserId;
            }
        }

        $adminUuid = trim((string) ($user->student_id ?? ''));
        if ($adminUuid !== '' && AdminHub::hasColumn('admin_uuid')) {
            $linkedByUuid = AdminHub::query()
                ->where('admin_uuid', $adminUuid)
                ->first();

            if ($linkedByUuid) {
                return $linkedByUuid;
            }
        }

        $email = trim((string) ($user->email ?? ''));
        if ($email === '' || !AdminHub::hasColumn('email')) {
            return null;
        }

        $linkedByEmail = AdminHub::query()
            ->whereRaw('LOWER(email) = ?', [strtolower($email)])
            ->first();

        if (!$linkedByEmail || $adminUuid === '' || !AdminHub::hasColumn('admin_uuid')) {
            return $linkedByEmail;
        }

        $storedUuid = trim((string) ($linkedByEmail->admin_uuid ?? ''));

        return $storedUuid === '' || hash_equals($storedUuid, $adminUuid)
            ? $linkedByEmail
            : null;
    }

    private function findLinkedAdminProfile($user): ?Admin
    {
        if (!Schema::hasTable('admins')) {
            return null;
        }

        if (Admin::hasColumn('user_id')) {
            $linkedByUserId = Admin::query()
                ->where('user_id', $user->id)
                ->first();

            if ($linkedByUserId) {
                return $linkedByUserId;
            }
        }

        $email = trim((string) ($user->email ?? ''));
        if ($email === '') {
            return null;
        }

        $linkedAdmin = Admin::query()
            ->where(function ($builder) use ($email) {
                if (Admin::hasColumn('email')) {
                    $builder->orWhere('email', $email);
                }

                if (Admin::hasColumn('email_address')) {
                    $builder->orWhere('email_address', $email);
                }
            })
            ->first();

        if ($linkedAdmin && Admin::hasColumn('user_id') && !$linkedAdmin->user_id) {
            $linkedAdmin->user_id = $user->id;
            $linkedAdmin->save();
        }

        return $linkedAdmin;
    }
}
