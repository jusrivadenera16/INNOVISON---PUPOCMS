<?php

namespace App\Support;

final class EmployeeNumber
{
    public static function normalize($value): ?string
    {
        if (!is_scalar($value) || is_bool($value)) {
            return null;
        }

        if ($value === 0 || $value === 0.0) {
            return null;
        }

        $value = trim((string) $value);
        if ($value === '' || preg_match('/^\d{1,5}$/', $value) !== 1) {
            return null;
        }

        return str_pad($value, 5, '0', STR_PAD_LEFT);
    }

    public static function fromFaculty(array $faculty): ?string
    {
        $groups = [
            $faculty,
            is_array($faculty['fields'] ?? null) ? $faculty['fields'] : [],
            is_array($faculty['profile'] ?? null) ? $faculty['profile'] : [],
        ];

        foreach (['employee_number', 'employee_no', 'employee_id'] as $field) {
            foreach ($groups as $group) {
                $number = self::normalize($group[$field] ?? null);
                if ($number !== null) {
                    return $number;
                }
            }
        }

        foreach (['faculty_code', 'identifier'] as $field) {
            foreach ($groups as $group) {
                $number = self::normalize($group[$field] ?? null);
                if ($number !== null) {
                    return $number;
                }
            }
        }

        return null;
    }
}
