<?php

namespace App\Listeners;

use App\Models\Admin;
use App\Models\SystemSetting;
use App\Models\User;
use Illuminate\Auth\Events\Login;
use Illuminate\Support\Facades\Schema;

class MarkDoctorAvailableOnLogin
{
    public function handle(Login $event): void
    {
        $user = $event->user;
        if (!$user instanceof User) {
            return;
        }

        if (!in_array(User::normalizeRole((string) $user->user_role), [User::ROLE_ADMIN, User::ROLE_SUPERADMIN], true)
            || $user->isStudentAssistant()) {
            return;
        }

        $userStatus = strtolower(trim((string) ($user->status ?? 'active')));
        if (!in_array($userStatus, ['', 'active'], true)) {
            return;
        }

        if (!Schema::hasTable('admins')
            || !Schema::hasColumn('admins', 'report_position')
            || !Schema::hasTable('system_settings')) {
            return;
        }

        $doctorProfile = null;
        if (Schema::hasColumn('admins', 'user_id')) {
            $doctorProfile = $user->adminProfile()
                ->whereRaw('LOWER(TRIM(report_position)) = ?', ['doctor'])
                ->first();
        }

        if (!$doctorProfile && trim((string) $user->email) !== '') {
            $emailColumns = array_values(array_filter(
                ['email', 'email_address'],
                fn (string $column): bool => Schema::hasColumn('admins', $column)
            ));

            if ($emailColumns !== []) {
                $doctorProfile = Admin::query()
                    ->whereRaw('LOWER(TRIM(report_position)) = ?', ['doctor'])
                    ->where(function ($query) use ($emailColumns, $user) {
                        foreach ($emailColumns as $column) {
                            $query->orWhereRaw('LOWER(TRIM(' . $column . ')) = ?', [strtolower(trim((string) $user->email))]);
                        }
                    })
                    ->first();
            }
        }

        if (!$doctorProfile) {
            return;
        }

        if (Schema::hasColumn('admins', 'status')) {
            $profileStatus = strtolower(trim((string) ($doctorProfile->status ?? '')));
            if ($profileStatus !== '' && $profileStatus !== 'active') {
                return;
            }
        }

        SystemSetting::putValue('clinic_doctor_available', '1');
    }
}
