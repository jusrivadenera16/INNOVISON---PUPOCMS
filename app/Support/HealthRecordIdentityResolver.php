<?php

namespace App\Support;

final class HealthRecordIdentityResolver
{
    public static function userType($record): string
    {
        $user = $record->user ?? null;
        $userType = strtolower(trim((string) ($user->user_type ?? '')));
        $idpRole = strtolower(trim((string) ($user->idp_role ?? '')));
        $userRole = strtolower(trim((string) ($user->user_role ?? '')));
        $recordSource = strtolower(trim((string) ($record->record_source ?? 'health')));
        $hasStudentNumber = self::hasOfficialStudentNumber([
            $record->student_number ?? null,
            $user->student_number ?? null,
        ]);

        $normalizedIdpRole = str_replace(['-', ' '], '_', $idpRole);
        $normalizedUserType = str_replace(['-', ' '], '_', $userType);
        if ($hasStudentNumber
            && (in_array($normalizedIdpRole, ['student', 'ojt', 'student_ojt'], true)
                || in_array($normalizedUserType, ['student', 'ojt', 'student_ojt'], true)
                || $userType === 'faculty')) {
            return 'Student';
        }

        if (self::hasRole($idpRole, ['faculty'])) {
            return 'Faculty';
        }
        if (self::hasRole($idpRole, ['admin', 'superadmin', 'super_admin', 'clinic_staff', 'clinic staff', 'nurse', 'employee', 'staff'])) {
            return 'Admin';
        }
        if (self::hasRole($userType, ['faculty'])) {
            return 'Faculty';
        }
        if (self::hasRole($userType, ['admin', 'superadmin', 'super_admin', 'clinic_staff', 'clinic staff', 'nurse', 'employee', 'non_teaching', 'staff'])) {
            return 'Admin';
        }
        if (self::hasRole($userRole, ['faculty'])) {
            return 'Faculty';
        }
        if (self::hasRole($userRole, ['admin', 'superadmin', 'super_admin', 'clinic_staff', 'clinic staff', 'nurse', 'employee', 'staff'])) {
            return 'Admin';
        }

        if ($user?->adminHubProfile) {
            return 'Admin';
        }
        if (self::hasRole($userType . ' ' . $idpRole . ' ' . $userRole, ['dependent', 'guest'])) {
            return 'Guest';
        }
        if (in_array($recordSource, ['employee', 'staff'], true)) {
            return 'Faculty';
        }
        if (self::hasRole($userType . ' ' . $idpRole . ' ' . $userRole, ['applicant', 'student'])) {
            return $hasStudentNumber ? 'Student' : 'Applicant';
        }

        return $hasStudentNumber ? 'Student' : 'Applicant';
    }

    public static function employeeNumber($record): ?string
    {
        $user = $record->user ?? null;

        foreach ([
            $record->employee_number ?? null,
            data_get($user, 'employeeHealthProfile.employee_number'),
            $user->employee_number ?? null,
            data_get($user, 'adminHubProfile.employee_number'),
            data_get($user, 'adminProfile.employee_number'),
        ] as $candidate) {
            $number = EmployeeNumber::normalize($candidate);
            if ($number !== null) {
                return $number;
            }
        }

        return null;
    }

    private static function hasOfficialStudentNumber(array $values): bool
    {
        foreach ($values as $value) {
            $number = strtoupper(trim((string) $value));
            if ($number !== ''
                && !str_starts_with($number, 'CLN-')
                && !str_starts_with($number, 'LOC-')
                && !str_starts_with($number, 'TEST-LOCAL')
                && preg_match('/^\d{4}-\d{5}-[A-Z]{2}-\d+$/', $number) === 1) {
                return true;
            }
        }

        return false;
    }

    private static function hasRole(string $role, array $needles): bool
    {
        foreach ($needles as $needle) {
            if (str_contains($role, $needle)) {
                return true;
            }
        }

        return false;
    }
}
