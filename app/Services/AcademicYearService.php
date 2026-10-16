<?php

namespace App\Services;

use App\Models\SystemSetting;

class AcademicYearService
{
    public function configuredRange(): array
    {
        $from = trim((string) SystemSetting::getValue('academic_year_from', ''));
        $to = trim((string) SystemSetting::getValue('academic_year_to', ''));

        if (preg_match('/^\d{4}$/', $from) && preg_match('/^\d{4}$/', $to)) {
            $fromYear = (int) $from;
            $toYear = (int) $to;

            if ($fromYear >= 1950 && $fromYear <= 2099 && $toYear === $fromYear + 1) {
                return ['from' => (string) $fromYear, 'to' => (string) $toYear];
            }
        }

        $startMonth = (int) SystemSetting::getValue('academic_year_start_month', '5');
        if ($startMonth < 1 || $startMonth > 12) {
            $startMonth = 5;
        }

        $now = now();
        $fromYear = $now->month >= $startMonth ? $now->year : $now->year - 1;

        return ['from' => (string) $fromYear, 'to' => (string) ($fromYear + 1)];
    }

    public function label(): string
    {
        $range = $this->configuredRange();

        return $range['from'] . '-' . $range['to'];
    }
}
