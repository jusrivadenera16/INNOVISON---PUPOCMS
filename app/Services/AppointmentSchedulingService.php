<?php

namespace App\Services;

use App\Models\Appointment;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use LogicException;
use Throwable;

class AppointmentSchedulingService
{
    public function lockSchedule(): void
    {
        $lock = DB::table('appointment_booking_locks')
            ->where('id', 1)
            ->lockForUpdate()
            ->first();

        if (!$lock) {
            throw new LogicException('The appointment booking lock is missing. Run the database migrations.');
        }
    }

    public function bookableTimeSlots(): array
    {
        $slots = [];
        for ($hour = 8; $hour <= 19; $hour++) {
            foreach ([0, 30] as $minute) {
                if ($hour === 19 && $minute > 0) {
                    continue;
                }

                $slots[] = sprintf('%02d:%02d', $hour, $minute);
            }
        }

        return $slots;
    }

    public function hasReservedSlot(string $date, string $time, ?int $exceptAppointmentId = null): bool
    {
        $query = Appointment::query()
            ->whereDate('date', $date)
            ->where('status', '!=', 'Cancelled');

        if ($exceptAppointmentId !== null) {
            $query->where('id', '!=', $exceptAppointmentId);
        }

        return $query->pluck('time')->contains(function ($reservedTime) use ($time): bool {
            try {
                return Carbon::parse((string) $reservedTime)->format('H:i') === $time;
            } catch (Throwable) {
                return false;
            }
        });
    }

    public function dailyAppointmentCount(string $date, ?int $exceptAppointmentId = null): int
    {
        $query = Appointment::query()
            ->whereDate('date', $date)
            ->where('status', '!=', 'Cancelled');

        if ($exceptAppointmentId !== null) {
            $query->where('id', '!=', $exceptAppointmentId);
        }

        return $query->count();
    }
}
