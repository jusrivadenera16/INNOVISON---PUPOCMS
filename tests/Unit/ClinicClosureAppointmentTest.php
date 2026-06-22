<?php

namespace Tests\Unit;

use App\Http\Controllers\AdminController;
use Carbon\Carbon;
use PHPUnit\Framework\TestCase;
use ReflectionMethod;

class ClinicClosureAppointmentTest extends TestCase
{
    public function test_appointments_inside_the_closure_window_are_affected(): void
    {
        $startsAt = Carbon::parse('2026-06-22 10:00:00');
        $endsAt = Carbon::parse('2026-06-22 15:00:00');

        $this->assertTrue($this->fallsWithin('2026-06-22 10:00:00', $startsAt, $endsAt));
        $this->assertTrue($this->fallsWithin('2026-06-22 12:30:00', $startsAt, $endsAt));
    }

    public function test_reopening_time_is_not_part_of_the_closure_window(): void
    {
        $startsAt = Carbon::parse('2026-06-22 10:00:00');
        $endsAt = Carbon::parse('2026-06-22 15:00:00');

        $this->assertFalse($this->fallsWithin('2026-06-22 09:30:00', $startsAt, $endsAt));
        $this->assertFalse($this->fallsWithin('2026-06-22 15:00:00', $startsAt, $endsAt));
    }

    private function fallsWithin(string $appointmentAt, Carbon $startsAt, Carbon $endsAt): bool
    {
        $controller = new AdminController();
        $method = new ReflectionMethod($controller, 'appointmentFallsWithinClinicClosure');
        $method->setAccessible(true);

        return $method->invoke($controller, Carbon::parse($appointmentAt), $startsAt, $endsAt);
    }
}
