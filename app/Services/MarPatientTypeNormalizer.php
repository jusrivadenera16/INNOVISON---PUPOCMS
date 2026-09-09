<?php

namespace App\Services;

class MarPatientTypeNormalizer
{
    public const STUDENT = 'student';
    public const FACULTY = 'faculty';
    public const ADMIN = 'admin';
    public const DEPENDENT = 'dependent';

    public function normalize(?string $value): ?string
    {
        $normalized = strtolower(trim((string) $value));
        $normalized = preg_replace('/[^a-z0-9]+/', ' ', $normalized) ?? $normalized;
        $normalized = trim(preg_replace('/\s+/', ' ', $normalized) ?? $normalized);

        if ($normalized === '') {
            return null;
        }

        return match (true) {
            str_contains($normalized, 'applicant'),
            str_contains($normalized, 'student'),
            $normalized === 'ojt' => self::STUDENT,
            str_contains($normalized, 'faculty') => self::FACULTY,
            str_contains($normalized, 'admin'),
            str_contains($normalized, 'staff'),
            str_contains($normalized, 'employee'),
            str_contains($normalized, 'designee') => self::ADMIN,
            str_contains($normalized, 'dependent'),
            str_contains($normalized, 'guest') => self::DEPENDENT,
            default => null,
        };
    }
}
