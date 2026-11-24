<?php

namespace App\Services;

use Illuminate\Support\Facades\Crypt;

class HealthFormSchoolYearSnapshotService
{
    public function issue(int $userId, string $schoolYear): string
    {
        return Crypt::encryptString(json_encode([
            'user_id' => $userId,
            'school_year' => $schoolYear,
        ], JSON_THROW_ON_ERROR));
    }

    public function resolve(?string $token, int $userId): ?string
    {
        if ($userId <= 0 || !is_string($token) || trim($token) === '') {
            return null;
        }

        try {
            $payload = json_decode(Crypt::decryptString($token), true, 512, JSON_THROW_ON_ERROR);
        } catch (\Throwable) {
            return null;
        }

        $schoolYear = trim((string) ($payload['school_year'] ?? ''));
        if ((int) ($payload['user_id'] ?? 0) !== $userId
            || !preg_match('/^(\d{4})-(\d{4})$/', $schoolYear, $matches)
            || (int) $matches[2] !== (int) $matches[1] + 1) {
            return null;
        }

        return $schoolYear;
    }
}
