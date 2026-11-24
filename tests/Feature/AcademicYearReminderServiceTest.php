<?php

namespace Tests\Feature;

use App\Models\SystemSetting;
use App\Services\AcademicYearReminderService;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class AcademicYearReminderServiceTest extends TestCase
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

    public function test_default_reminder_is_ten_months_after_school_year_was_saved(): void
    {
        Carbon::setTestNow('2026-11-24 10:00:00');
        SystemSetting::putValue('academic_year_from', '2026');
        SystemSetting::putValue('academic_year_to', '2027');

        $configuration = app(AcademicYearReminderService::class)->initializeDefaults();

        $this->assertTrue($configuration['enabled']);
        $this->assertSame('2027-09-24', $configuration['reminder_date']);
        $this->assertFalse(app(AcademicYearReminderService::class)->shouldPrompt());
    }

    public function test_reminder_can_be_snoozed_and_then_confirmed_for_ten_months(): void
    {
        Carbon::setTestNow('2026-11-24 10:00:00');
        $service = app(AcademicYearReminderService::class);
        $service->savePreferences(true, '2026-11-24');

        $this->assertTrue($service->shouldPrompt());
        $this->assertSame('2026-12-01', $service->snooze()['snoozed_until']);
        $this->assertFalse($service->shouldPrompt());

        Carbon::setTestNow('2026-12-01 10:00:00');
        $this->assertTrue($service->shouldPrompt());

        $configuration = $service->confirmCurrentYear();
        $this->assertSame('2027-10-01', $configuration['reminder_date']);
        $this->assertFalse($service->shouldPrompt());
    }

    public function test_invalid_stored_reminder_date_falls_back_to_a_valid_default(): void
    {
        Carbon::setTestNow('2026-11-24 10:00:00');
        SystemSetting::putValue('academic_year_from', '2026');
        SystemSetting::putValue('academic_year_to', '2027');
        SystemSetting::putValue(AcademicYearReminderService::DATE_KEY, '2026-02-31');

        $this->assertSame(
            '2027-09-24',
            app(AcademicYearReminderService::class)->configuration()['reminder_date']
        );
    }
}
