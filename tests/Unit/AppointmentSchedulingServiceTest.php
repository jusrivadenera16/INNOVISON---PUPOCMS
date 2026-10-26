<?php

namespace Tests\Unit;

use App\Services\AppointmentSchedulingService;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class AppointmentSchedulingServiceTest extends TestCase
{
    private AppointmentSchedulingService $scheduling;

    protected function setUp(): void
    {
        parent::setUp();

        config([
            'database.default' => 'sqlite',
            'database.connections.sqlite.database' => ':memory:',
        ]);
        DB::purge('sqlite');

        Schema::create('users', function (Blueprint $table): void {
            $table->id();
        });
        Schema::create('appointments', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('user_id')->nullable();
            $table->date('date');
            $table->time('time');
            $table->string('status');
        });
        Schema::create('appointment_booking_locks', function (Blueprint $table): void {
            $table->unsignedTinyInteger('id')->primary();
        });
        DB::table('appointment_booking_locks')->insert(['id' => 1]);

        $this->scheduling = new AppointmentSchedulingService();
    }

    public function test_bookable_slots_match_the_student_booking_schedule(): void
    {
        $slots = $this->scheduling->bookableTimeSlots();

        $this->assertCount(23, $slots);
        $this->assertSame('08:00', $slots[0]);
        $this->assertSame('08:30', $slots[1]);
        $this->assertSame('19:00', $slots[22]);
        $this->assertNotContains('19:30', $slots);
    }

    public function test_it_detects_reserved_slots_and_releases_cancelled_ones(): void
    {
        $appointmentId = DB::table('appointments')->insertGetId([
            'date' => '2026-11-02',
            'time' => '09:00:00',
            'status' => 'Pending',
        ]);

        $this->assertTrue($this->scheduling->hasReservedSlot('2026-11-02', '09:00'));
        $this->assertFalse($this->scheduling->hasReservedSlot('2026-11-02', '09:30'));
        $this->assertFalse($this->scheduling->hasReservedSlot('2026-11-02', '09:00', $appointmentId));
        $this->assertSame(1, $this->scheduling->dailyAppointmentCount('2026-11-02'));

        DB::table('appointments')->where('id', $appointmentId)->update(['status' => 'Cancelled']);

        $this->assertFalse($this->scheduling->hasReservedSlot('2026-11-02', '09:00'));
        $this->assertSame(0, $this->scheduling->dailyAppointmentCount('2026-11-02'));
    }

    public function test_booking_mutex_can_be_locked_inside_a_transaction(): void
    {
        DB::transaction(fn () => $this->scheduling->lockSchedule());

        $this->assertDatabaseHas('appointment_booking_locks', ['id' => 1]);
    }
}
