<?php

namespace App\Services;

use App\Models\SystemSetting;
use Carbon\Carbon;

class AcademicYearReminderService
{
    public const ENABLED_KEY = 'academic_year_reminder_enabled';
    public const DATE_KEY = 'academic_year_reminder_date';
    public const SNOOZED_UNTIL_KEY = 'academic_year_reminder_snoozed_until';

    public function configuration(): array
    {
        $enabledValue = SystemSetting::getValue(self::ENABLED_KEY);
        $reminderDate = trim((string) SystemSetting::getValue(self::DATE_KEY, ''));
        $snoozedUntil = trim((string) SystemSetting::getValue(self::SNOOZED_UNTIL_KEY, ''));

        if (!$this->isDate($reminderDate)) {
            $reminderDate = $this->defaultReminderDate();
        }

        if (!$this->isDate($snoozedUntil)) {
            $snoozedUntil = '';
        }

        return [
            'enabled' => $enabledValue === null
                ? true
                : filter_var($enabledValue, FILTER_VALIDATE_BOOL),
            'reminder_date' => $reminderDate,
            'snoozed_until' => $snoozedUntil,
        ];
    }

    public function initializeDefaults(): array
    {
        if (SystemSetting::getValue(self::ENABLED_KEY) === null) {
            SystemSetting::putValue(self::ENABLED_KEY, '1');
        }

        if (!$this->isDate(trim((string) SystemSetting::getValue(self::DATE_KEY, '')))) {
            SystemSetting::putValue(self::DATE_KEY, $this->defaultReminderDate());
        }

        return $this->configuration();
    }

    public function shouldPrompt(): bool
    {
        $configuration = $this->configuration();
        $today = now()->toDateString();

        return $configuration['enabled']
            && $configuration['reminder_date'] <= $today
            && ($configuration['snoozed_until'] === '' || $configuration['snoozed_until'] <= $today);
    }

    public function savePreferences(bool $enabled, ?string $reminderDate): array
    {
        SystemSetting::putValue(self::ENABLED_KEY, $enabled ? '1' : '0');

        if ($enabled && $this->isDate($reminderDate)) {
            SystemSetting::putValue(self::DATE_KEY, $reminderDate);
        } elseif ($this->isDate($reminderDate)) {
            SystemSetting::putValue(self::DATE_KEY, $reminderDate);
        }

        $this->clearSnooze();

        return $this->configuration();
    }

    public function snooze(): array
    {
        SystemSetting::putValue(self::SNOOZED_UNTIL_KEY, now()->addDays(7)->toDateString());

        return $this->configuration();
    }

    public function confirmCurrentYear(): array
    {
        SystemSetting::putValue(self::ENABLED_KEY, '1');
        SystemSetting::putValue(self::DATE_KEY, $this->nextReminderDate());
        $this->clearSnooze();

        return $this->configuration();
    }

    public function scheduleAfterYearUpdate(): array
    {
        SystemSetting::putValue(self::ENABLED_KEY, '1');
        SystemSetting::putValue(self::DATE_KEY, $this->nextReminderDate());
        $this->clearSnooze();

        return $this->configuration();
    }

    public function nextReminderDate(): string
    {
        return now()->addMonthsNoOverflow(10)->toDateString();
    }

    private function defaultReminderDate(): string
    {
        $academicYearSetting = SystemSetting::query()->where('key', 'academic_year_to')->first()
            ?: SystemSetting::query()->where('key', 'academic_year_from')->first();

        $anchor = $academicYearSetting?->updated_at
            ? Carbon::parse($academicYearSetting->updated_at)
            : now();

        return $anchor->addMonthsNoOverflow(10)->toDateString();
    }

    private function clearSnooze(): void
    {
        SystemSetting::query()->where('key', self::SNOOZED_UNTIL_KEY)->delete();
    }

    private function isDate(?string $value): bool
    {
        if (!is_string($value) || !preg_match('/^\d{4}-\d{2}-\d{2}$/', $value)) {
            return false;
        }

        try {
            $date = Carbon::createFromFormat('!Y-m-d', $value);

            return $date instanceof Carbon
                && $date->format('Y-m-d') === $value;
        } catch (\Throwable) {
            return false;
        }
    }
}
