<?php

namespace Tests\Unit;

use App\Models\Consultation;
use App\Services\MarClearanceIssuanceService;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use ReflectionMethod;
use Tests\TestCase;

class MarClearanceConsultationCompletionTest extends TestCase
{
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
            $table->foreignId('user_id');
            $table->date('date');
            $table->string('service')->nullable();
            $table->string('status')->nullable();
        });

        DB::table('users')->insert(['id' => 7]);
    }

    public function test_consultation_source_requires_a_matching_completed_appointment(): void
    {
        $consultation = new Consultation([
            'user_id' => 7,
            'consultation_date' => '2026-10-25',
            'service' => 'Medical Consultation',
        ]);
        $service = new MarClearanceIssuanceService();
        $method = new ReflectionMethod($service, 'hasCompletedAppointmentForConsultation');
        $method->setAccessible(true);

        DB::table('appointments')->insert([
            'user_id' => 7,
            'date' => '2026-10-25',
            'service' => 'Medical Consultation',
            'status' => 'Pending',
        ]);
        $this->assertFalse($method->invoke($service, $consultation));

        DB::table('appointments')->update(['status' => 'Completed']);
        $this->assertTrue($method->invoke($service, $consultation));
    }
}
