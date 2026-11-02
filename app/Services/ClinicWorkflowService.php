<?php

namespace App\Services;

use App\Models\Setting;
use App\Models\Admin;
use App\Models\SystemSetting;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Facades\Schema;

class ClinicWorkflowService
{
    private ?Setting $settings = null;

    public function settings(): Setting
    {
        if ($this->settings) {
            return $this->settings;
        }

        $this->settings = Setting::query()->firstOrNew([], [
            'clinic_name' => 'PUP Taguig Clinic',
            'clinic_location' => 'Santos Ave, Lower Bicutan, Taguig',
            'open_time' => '08:00',
            'close_time' => '17:00',
            'operating_days' => [1, 2, 3, 4, 5],
            'student_assistant_open_time' => '08:00',
            'student_assistant_close_time' => '20:00',
            'appointment_reminder_hours' => 24,
            'pending_compliance_reminder_days' => 7,
            'pending_compliance_reminder_max_count' => 3,
            'notification_quiet_hours_enabled' => false,
            'notification_quiet_hours_start' => '20:00',
            'notification_quiet_hours_end' => '07:00',
        ]);

        return $this->settings;
    }

    public function doctorIsAvailable(?Carbon $at = null): bool
    {
        $at = ($at ?: now(config('app.timezone')))->copy();

        if (!$this->activeDoctorProfile()) {
            $this->setDoctorAvailability(false);
            return false;
        }

        if ($this->activeClosure($at) !== null) {
            $this->setDoctorAvailability(false);
            return false;
        }

        if (!SystemSetting::booleanValue('clinic_doctor_available', false)) {
            return false;
        }

        $availableOn = trim((string) SystemSetting::getValue('clinic_doctor_availability_date', ''));
        if ($availableOn !== $at->toDateString()) {
            $this->setDoctorAvailability(false);
            return false;
        }

        $settings = $this->settings();
        $closureEndsAt = $settings->clinic_closure_enabled && $settings->clinic_closure_ends_at
            ? Carbon::parse($settings->clinic_closure_ends_at, config('app.timezone'))
            : null;
        $availableAtValue = trim((string) SystemSetting::getValue('clinic_doctor_availability_at', ''));
        if ($closureEndsAt && $at->gte($closureEndsAt) && $availableAtValue !== '') {
            $availableAt = Carbon::parse($availableAtValue, config('app.timezone'));
            if ($availableAt->lte($closureEndsAt)) {
                $this->setDoctorAvailability(false);
                return false;
            }
        }

        return true;
    }

    public function setDoctorAvailability(bool $available, ?Carbon $at = null): void
    {
        SystemSetting::putValue('clinic_doctor_available', $available ? '1' : '0');
        SystemSetting::putValue(
            'clinic_doctor_availability_date',
            $available ? ($at ?: now(config('app.timezone')))->toDateString() : ''
        );
        SystemSetting::putValue(
            'clinic_doctor_availability_at',
            $available ? ($at ?: now(config('app.timezone')))->toIso8601String() : ''
        );
    }

    public function clinicIsClosed(?Carbon $at = null): bool
    {
        $at = ($at ?: now(config('app.timezone')))->copy();

        return !($this->clinicHoursStatus($at)['is_open'] ?? false)
            || $this->activeClosure($at) !== null;
    }

    public function doctorProfileForUser(?User $user): ?Admin
    {
        if (!$user || !$this->isEligibleClinicAdmin($user)
            || !Schema::hasTable('admins')
            || !Schema::hasColumn('admins', 'report_position')) {
            return null;
        }

        $profile = null;
        if (Schema::hasColumn('admins', 'user_id')) {
            $profile = Admin::query()
                ->where('user_id', $user->getKey())
                ->whereRaw('LOWER(TRIM(report_position)) = ?', ['doctor'])
                ->first();
        }

        if (!$profile && trim((string) $user->email) !== '') {
            $emailColumns = array_values(array_filter(
                ['email', 'email_address'],
                static fn (string $column): bool => Schema::hasColumn('admins', $column)
            ));

            if ($emailColumns !== []) {
                $profile = Admin::query()
                    ->whereRaw('LOWER(TRIM(report_position)) = ?', ['doctor'])
                    ->where(function ($query) use ($emailColumns, $user): void {
                        foreach ($emailColumns as $column) {
                            $query->orWhereRaw(
                                'LOWER(TRIM(' . $column . ')) = ?',
                                [strtolower(trim((string) $user->email))]
                            );
                        }
                    })
                    ->first();
            }
        }

        return $this->isActiveDoctorProfile($profile) ? $profile : null;
    }

    public function activeDoctorProfile(): ?Admin
    {
        if (!Schema::hasTable('admins') || !Schema::hasColumn('admins', 'report_position')) {
            return null;
        }

        $profiles = Admin::query()
            ->whereRaw('LOWER(TRIM(report_position)) = ?', ['doctor'])
            ->get();

        foreach ($profiles as $profile) {
            if (!$this->isActiveDoctorProfile($profile)) {
                continue;
            }

            $user = null;
            if (Schema::hasColumn('admins', 'user_id') && $profile->user_id) {
                $user = User::query()->find($profile->user_id);
            }

            $profileEmail = trim((string) ($profile->email ?? ''));
            if ($profileEmail === '') {
                $profileEmail = trim((string) ($profile->email_address ?? ''));
            }
            if (!$user && $profileEmail !== '') {
                $email = strtolower($profileEmail);
                $userEmailColumns = array_values(array_filter(
                    ['email', 'email_address'],
                    static fn (string $column): bool => Schema::hasColumn('users', $column)
                ));
                foreach ($userEmailColumns as $column) {
                    $user = User::query()
                        ->whereRaw('LOWER(TRIM(' . $column . ')) = ?', [$email])
                        ->first();
                    if ($user) {
                        break;
                    }
                }
            }

            if ($user && $this->doctorProfileForUser($user)?->getKey() === $profile->getKey()) {
                return $profile;
            }
        }

        return null;
    }

    public function userIsAssignedDoctor(?User $user): bool
    {
        return $this->doctorProfileForUser($user) !== null;
    }

    public function canManageDoctorAvailability(?User $user): bool
    {
        if (!$user || !$this->activeDoctorProfile() || $user->isStudentAssistant()) {
            return false;
        }

        return User::normalizeRole((string) $user->user_role) === User::ROLE_SUPERADMIN
            || $this->userIsAssignedDoctor($user);
    }

    private function isEligibleClinicAdmin(User $user): bool
    {
        if (!in_array(
            User::normalizeRole((string) $user->user_role),
            [User::ROLE_ADMIN, User::ROLE_SUPERADMIN],
            true
        ) || $user->isStudentAssistant()) {
            return false;
        }

        $status = strtolower(trim((string) ($user->status ?? 'active')));

        return in_array($status, ['', 'active'], true);
    }

    private function isActiveDoctorProfile(?Admin $profile): bool
    {
        if (!$profile) {
            return false;
        }

        if (Admin::hasColumn('status')) {
            $status = strtolower(trim((string) ($profile->status ?? '')));
            if ($status !== '' && $status !== 'active') {
                return false;
            }
        }

        return true;
    }

    public function notificationsAreQuiet(?Carbon $at = null): bool
    {
        $settings = $this->settings();
        if (!$settings->notification_quiet_hours_enabled) {
            return false;
        }

        $at = ($at ?: now(config('app.timezone')))->copy();
        $current = ((int) $at->format('H') * 60) + (int) $at->format('i');
        $start = $this->timeToMinutes(substr((string) ($settings->notification_quiet_hours_start ?: '20:00'), 0, 5));
        $end = $this->timeToMinutes(substr((string) ($settings->notification_quiet_hours_end ?: '07:00'), 0, 5));

        if ($start === $end) {
            return true;
        }

        return $start < $end
            ? $current >= $start && $current < $end
            : $current >= $start || $current < $end;
    }

    public function studentAssistantWorkspaceAvailable(?Carbon $at = null): bool
    {
        return (bool) $this->clinicHoursStatus($at)['is_open'];
    }

    public function studentAssistantHoursLabel(): string
    {
        return $this->clinicScheduleLabel();
    }

    public function operatingDays(): array
    {
        $days = $this->settings()->operating_days;
        if (is_string($days)) {
            $days = json_decode($days, true);
        }

        $days = array_values(array_unique(array_filter(
            array_map('intval', is_array($days) ? $days : []),
            static fn (int $day): bool => $day >= 1 && $day <= 7
        )));

        if ($days === []) {
            $days = [1, 2, 3, 4, 5];
        }

        sort($days);

        return $days;
    }

    public function operatingDaysLabel(): string
    {
        $days = $this->operatingDays();
        if ($days === [1, 2, 3, 4, 5, 6, 7]) {
            return 'Daily';
        }

        $labels = [1 => 'Mon', 2 => 'Tue', 3 => 'Wed', 4 => 'Thu', 5 => 'Fri', 6 => 'Sat', 7 => 'Sun'];
        $ranges = [];
        $rangeStart = $days[0];
        $previous = $days[0];

        foreach (array_slice($days, 1) as $day) {
            if ($day === $previous + 1) {
                $previous = $day;
                continue;
            }

            $ranges[] = $rangeStart === $previous
                ? $labels[$rangeStart]
                : $labels[$rangeStart] . '-' . $labels[$previous];
            $rangeStart = $previous = $day;
        }

        $ranges[] = $rangeStart === $previous
            ? $labels[$rangeStart]
            : $labels[$rangeStart] . '-' . $labels[$previous];

        return implode(', ', $ranges);
    }

    public function clinicScheduleLabel(): string
    {
        $settings = $this->settings();

        return $this->operatingDaysLabel()
            . ', '
            . $this->formatTime($settings->open_time ?: '08:00')
            . ' - '
            . $this->formatTime($settings->close_time ?: '17:00');
    }

    public function clinicHoursStatus(?Carbon $at = null): array
    {
        $settings = $this->settings();
        $at = ($at ?: now(config('app.timezone')))->copy();
        $openTime = substr((string) ($settings->open_time ?: '08:00'), 0, 5);
        $closeTime = substr((string) ($settings->close_time ?: '17:00'), 0, 5);
        $openMinutes = $this->timeToMinutes($openTime);
        $closeMinutes = $this->timeToMinutes($closeTime);
        $currentMinutes = ((int) $at->format('H') * 60) + (int) $at->format('i');
        $operatingDays = $this->operatingDays();
        $todayIsOperating = in_array($at->isoWeekday(), $operatingDays, true);

        if ($openMinutes === $closeMinutes) {
            $isOpen = $todayIsOperating;
        } elseif ($openMinutes < $closeMinutes) {
            $isOpen = $todayIsOperating
                && $currentMinutes >= $openMinutes
                && $currentMinutes < $closeMinutes;
        } else {
            $yesterdayWasOperating = in_array($at->copy()->subDay()->isoWeekday(), $operatingDays, true);
            $isOpen = ($todayIsOperating && $currentMinutes >= $openMinutes)
                || ($yesterdayWasOperating && $currentMinutes < $closeMinutes);
        }

        $nextOpenAt = $isOpen ? null : $this->nextClinicOpening($at, $operatingDays, $openTime);

        return [
            'is_open' => $isOpen,
            'is_operating_day' => $todayIsOperating,
            'label' => $isOpen
                ? 'Clinic Open Now'
                : ($todayIsOperating ? 'Clinic Closed Now' : 'Clinic Closed Today'),
            'hours' => $this->formatTime($openTime) . ' - ' . $this->formatTime($closeTime),
            'operating_days' => $operatingDays,
            'operating_days_label' => $this->operatingDaysLabel(),
            'next_open_at' => $nextOpenAt?->toIso8601String(),
        ];
    }

    public function activeClosure(?Carbon $at = null): ?array
    {
        $settings = $this->settings();
        if (!$settings->clinic_closure_enabled) {
            return null;
        }

        $at = ($at ?: now(config('app.timezone')))->copy();
        $startsAt = $settings->clinic_closure_starts_at
            ? Carbon::parse($settings->clinic_closure_starts_at, config('app.timezone'))
            : $at->copy()->startOfDay();
        $endsAt = $settings->clinic_closure_ends_at
            ? Carbon::parse($settings->clinic_closure_ends_at, config('app.timezone'))
            : null;

        if ($at->lt($startsAt) || ($endsAt && $at->gte($endsAt))) {
            return null;
        }

        $reason = trim((string) ($settings->clinic_closure_reason ?: 'Temporary Clinic Closure'));
        $message = trim((string) ($settings->clinic_closure_message ?: 'The clinic is temporarily unavailable for new appointment bookings.'));

        return [
            'reason' => $reason,
            'message' => $message,
            'starts_at' => $startsAt,
            'ends_at' => $endsAt,
            'updated_at' => $settings->updated_at,
        ];
    }

    private function nextClinicOpening(Carbon $at, array $operatingDays, string $openTime): ?Carbon
    {
        [$openHour, $openMinute] = array_map('intval', array_pad(explode(':', $openTime), 2, 0));

        for ($offset = 0; $offset <= 14; $offset++) {
            $candidate = $at->copy()->addDays($offset)->setTime($openHour, $openMinute, 0);
            if (in_array($candidate->isoWeekday(), $operatingDays, true) && $candidate->gt($at)) {
                return $candidate;
            }
        }

        return null;
    }

    private function timeToMinutes(string $time): int
    {
        [$hours, $minutes] = array_pad(explode(':', $time), 2, 0);

        return ((int) $hours * 60) + (int) $minutes;
    }

    private function formatTime(string $time): string
    {
        return Carbon::createFromFormat('H:i', substr($time, 0, 5))->format('g:i A');
    }
}
