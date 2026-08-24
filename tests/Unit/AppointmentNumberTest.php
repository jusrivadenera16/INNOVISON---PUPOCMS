<?php

namespace Tests\Unit;

use App\Models\Appointment;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class AppointmentNumberTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        config([
            'database.default' => 'sqlite',
            'database.connections.sqlite.database' => ':memory:',
        ]);

        DB::purge('sqlite');

        Schema::create('appointments', function (Blueprint $table): void {
            $table->id();
            $table->string('apt_id')->nullable()->unique();
        });
    }

    public function test_it_generates_the_expected_online_appointment_number(): void
    {
        $number = Appointment::generateAppointmentNumber(
            Carbon::create(2026, 8, 5, 22, 34),
            'online'
        );

        $this->assertSame('OAPT-050826-223401', $number);
    }

    public function test_malformed_historical_ids_do_not_expand_new_ids(): void
    {
        DB::table('appointments')->insert([
            ['apt_id' => 'OAPT-050826-211501'],
            ['apt_id' => 'OAPT-050826-2234211502'],
        ]);

        $number = Appointment::generateAppointmentNumber(
            Carbon::create(2026, 8, 5, 22, 40),
            'online'
        );

        $this->assertSame('OAPT-050826-224003', $number);
    }

    public function test_online_and_walk_in_sequences_are_counted_separately(): void
    {
        DB::table('appointments')->insert([
            ['apt_id' => 'OAPT-050826-090001'],
            ['apt_id' => 'WAPT-050826-091501'],
        ]);

        $number = Appointment::generateAppointmentNumber(
            Carbon::create(2026, 8, 5, 10, 30),
            'walkin'
        );

        $this->assertSame('WAPT-050826-103002', $number);
    }
}
