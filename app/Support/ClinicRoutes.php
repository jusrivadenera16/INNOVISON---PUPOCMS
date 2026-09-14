<?php

namespace App\Support;

use App\Models\SystemSetting;
use App\Models\User;
use Illuminate\Support\Facades\Schema;

class ClinicRoutes
{
    public static function workspaceRedirectForUser(User $user): string
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

        if ($normalizedRole === User::ROLE_ADMIN) {
            return '/student/home';
        }

        return '/student/home';
    }

    public static function maintenanceModeEnabled(): bool
    {
        return Schema::hasTable('system_settings')
            && SystemSetting::booleanValue('maintenance_mode_enabled', false);
    }
}
