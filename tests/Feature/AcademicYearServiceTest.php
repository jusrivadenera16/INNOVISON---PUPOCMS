<?php

namespace Tests\Feature;

use App\Models\SystemSetting;
use App\Services\AcademicYearService;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class AcademicYearServiceTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        config()->set('database.default', 'sqlite');
        config()->set('database.connections.sqlite', [
            'driver' => 'sqlite',
            'database' => ':memory:',
            'prefix' => '',
            'foreign_key_constraints' => false,
        ]);
        DB::purge('sqlite');
        DB::setDefaultConnection('sqlite');

        Schema::create('system_settings', function (Blueprint $table) {
            $table->id();
            $table->string('key')->unique();
            $table->text('value')->nullable();
            $table->boolean('encrypted')->default(false);
            $table->timestamps();
        });
    }

    protected function tearDown(): void
    {
        Carbon::setTestNow();
        parent::tearDown();
    }

    public function test_saved_year_range_is_used_for_health_forms(): void
    {
        SystemSetting::putValue('academic_year_from', '2026');
        SystemSetting::putValue('academic_year_to', '2027');
        SystemSetting::putValue('academic_year_start_month', '8');
        Carbon::setTestNow('2028-02-10 12:00:00');

        $this->assertSame('2026-2027', app(AcademicYearService::class)->label());
    }

    public function test_legacy_start_month_provides_a_compatible_initial_range(): void
    {
        SystemSetting::putValue('academic_year_start_month', '8');
        $service = app(AcademicYearService::class);

        Carbon::setTestNow('2027-02-10 12:00:00');
        $this->assertSame(['from' => '2026', 'to' => '2027'], $service->configuredRange());

        Carbon::setTestNow('2027-09-10 12:00:00');
        $this->assertSame(['from' => '2027', 'to' => '2028'], $service->configuredRange());
    }
}
