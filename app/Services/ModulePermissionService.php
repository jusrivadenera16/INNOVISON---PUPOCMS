<?php

namespace App\Services;

use App\Models\User;

class ModulePermissionService
{
    public const DEFAULT_PERMISSIONS = [
        'appointments.view',
        'walkin.view',
        'health_records.view',
        'inventory.view',
    ];

    private const PERMISSIONS = [
        'appointments.view',
        'appointments.approve',
        'appointments.reject',
        'appointments.reschedule',
        'walkin.view',
        'walkin.scan_id',
        'walkin.register_patient',
        'walkin.encode_assessment',
        'walkin.review_submission',
        'walkin.employee_view',
        'walkin.employee_lookup',
        'walkin.final_review',
        'health_records.view',
        'health_records.review_documents',
        'health_records.request_resubmission',
        'health_records.update_assessment',
        'inventory.view',
        'inventory.import',
        'inventory.add_stock',
        'inventory.manage',
        'reports.view',
        'reports.mar',
        'reports.inventory_summary',
        'reports.health_forms',
        'reports.appointment_statistics',
        'reports.digital_logbook',
        'reports.feedbacks',
        'reports.export_reports',
        'announcements.view',
        'announcements.publish',
        'announcements.archive',
        'settings.view',
        'settings.personal',
        'settings.clinic',
        'settings.preferences',
        'settings.medical',
        'settings.faqs',
    ];

    private const PARENT_PERMISSIONS = [
        'appointments.approve' => 'appointments.view',
        'appointments.reject' => 'appointments.view',
        'appointments.reschedule' => 'appointments.view',
        'walkin.scan_id' => 'walkin.view',
        'walkin.register_patient' => 'walkin.view',
        'walkin.encode_assessment' => 'walkin.view',
        'walkin.review_submission' => 'walkin.view',
        'walkin.employee_view' => 'walkin.view',
        'walkin.employee_lookup' => 'walkin.employee_view',
        'walkin.final_review' => 'walkin.view',
        'health_records.review_documents' => 'health_records.view',
        'health_records.request_resubmission' => 'health_records.view',
        'health_records.update_assessment' => 'health_records.view',
        'inventory.import' => 'inventory.view',
        'inventory.add_stock' => 'inventory.view',
        'inventory.manage' => 'inventory.view',
        'reports.mar' => 'reports.view',
        'reports.inventory_summary' => 'reports.view',
        'reports.health_forms' => 'reports.view',
        'reports.appointment_statistics' => 'reports.view',
        'reports.digital_logbook' => 'reports.view',
        'reports.feedbacks' => 'reports.view',
        'reports.export_reports' => 'reports.view',
        'announcements.publish' => 'announcements.view',
        'announcements.archive' => 'announcements.view',
        'settings.personal' => 'settings.view',
        'settings.clinic' => 'settings.view',
        'settings.preferences' => 'settings.view',
        'settings.medical' => 'settings.view',
        'settings.faqs' => 'settings.view',
    ];

    public function all(): array
    {
        return self::PERMISSIONS;
    }

    public function defaults(): array
    {
        return self::DEFAULT_PERMISSIONS;
    }

    public function assigned(User $user): array
    {
        $stored = $user->module_permissions;

        if (!is_array($stored)) {
            return self::DEFAULT_PERMISSIONS;
        }

        return $this->normalize($stored);
    }

    public function normalize(array $permissions): array
    {
        $normalized = collect($permissions)
            ->map(fn ($permission) => strtolower(trim((string) $permission)))
            ->filter(fn ($permission) => in_array($permission, self::PERMISSIONS, true))
            ->unique()
            ->values()
            ->all();

        return array_values(array_filter($normalized, function (string $permission) use ($normalized) {
            $parent = self::PARENT_PERMISSIONS[$permission] ?? null;

            return $parent === null || in_array($parent, $normalized, true);
        }));
    }

    public function can(?User $user, string $permission): bool
    {
        if (!$user || !in_array($permission, self::PERMISSIONS, true)) {
            return false;
        }

        if (User::normalizeRole((string) $user->user_role) === User::ROLE_SUPERADMIN) {
            return true;
        }

        if (User::normalizeRole((string) $user->user_role) !== User::ROLE_ADMIN) {
            return false;
        }

        if (in_array($permission, ['walkin.final_review', 'reports.export_reports'], true)) {
            return false;
        }

        return in_array($permission, $this->assigned($user), true);
    }

    public function canAny(?User $user, array $permissions): bool
    {
        foreach ($permissions as $permission) {
            if ($this->can($user, $permission)) {
                return true;
            }
        }

        return false;
    }

    public function isKnown(string $permission): bool
    {
        return in_array($permission, self::PERMISSIONS, true);
    }
}
