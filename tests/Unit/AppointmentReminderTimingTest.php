<?php

namespace Tests\Unit;

use App\Models\Appointment;
use Illuminate\Support\Carbon;
use Tests\TestCase;

class AppointmentReminderTimingTest extends TestCase
{
    public function test_reminder_becomes_due_at_the_configured_lead_time(): void
    {
        $appointment = $this->appointment(
            'Approved',
            '2026-10-26 09:00:00',
            '2026-10-24 08:00:00'
        );

        $this->assertFalse($appointment->isReminderDue(24, Carbon::parse('2026-10-25 08:59:00')));
        $this->assertTrue($appointment->isReminderDue(24, Carbon::parse('2026-10-25 09:01:00')));
    }

    public function test_approval_after_the_reminder_cutoff_does_not_trigger_a_late_reminder(): void
    {
        $appointment = $this->appointment(
            'Approved',
            '2026-10-26 09:00:00',
            '2026-10-25 09:05:00'
        );

        $this->assertFalse($appointment->isReminderDue(24, Carbon::parse('2026-10-25 09:10:00')));
    }

    public function test_reminder_is_not_due_when_disabled_or_appointment_is_not_approved(): void
    {
        $appointment = $this->appointment(
            'Pending',
            '2026-10-26 09:00:00',
            '2026-10-24 08:00:00'
        );

        $this->assertFalse($appointment->isReminderDue(24, Carbon::parse('2026-10-25 09:01:00')));
        $this->assertFalse($appointment->isReminderDue(0, Carbon::parse('2026-10-25 09:01:00')));
    }

    private function appointment(string $status, string $appointmentAt, string $approvedAt): Appointment
    {
        $scheduledAt = Carbon::parse($appointmentAt);
        $approvedAt = Carbon::parse($approvedAt);

        $appointment = new Appointment([
            'status' => $status,
            'date' => $scheduledAt->toDateString(),
            'time' => $scheduledAt->format('H:i:s'),
        ]);
        $appointment->created_at = $approvedAt;
        $appointment->updated_at = $approvedAt;

        return $appointment;
    }
}
