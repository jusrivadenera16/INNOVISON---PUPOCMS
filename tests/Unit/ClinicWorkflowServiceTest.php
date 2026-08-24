<?php

namespace Tests\Unit;

use App\Models\Setting;
use App\Services\ClinicWorkflowService;
use Carbon\Carbon;
use PHPUnit\Framework\TestCase;
use ReflectionProperty;

class ClinicWorkflowServiceTest extends TestCase
{
    public function test_it_opens_only_on_selected_operating_days(): void
    {
        $service = $this->serviceWithSchedule([1, 3, 5], '08:00', '17:00');

        $wednesday = $service->clinicHoursStatus(Carbon::parse('2026-08-05 09:00:00', 'Asia/Manila'));
        $tuesday = $service->clinicHoursStatus(Carbon::parse('2026-08-04 09:00:00', 'Asia/Manila'));

        $this->assertTrue($wednesday['is_open']);
        $this->assertTrue($service->studentAssistantWorkspaceAvailable(Carbon::parse('2026-08-05 09:00:00', 'Asia/Manila')));
        $this->assertFalse($tuesday['is_open']);
        $this->assertSame('Clinic Closed Today', $tuesday['label']);
    }

    public function test_next_opening_skips_days_that_are_not_selected(): void
    {
        $service = $this->serviceWithSchedule([1, 3, 5], '08:00', '17:00');

        $status = $service->clinicHoursStatus(Carbon::parse('2026-08-07 18:00:00', 'Asia/Manila'));

        $this->assertFalse($status['is_open']);
        $this->assertNotNull($status['next_open_at']);
        $this->assertTrue(
            Carbon::parse($status['next_open_at'])->equalTo(Carbon::parse('2026-08-10 08:00:00', 'Asia/Manila'))
        );
    }

    public function test_schedule_label_includes_selected_days_and_clinic_hours(): void
    {
        $service = $this->serviceWithSchedule([1, 3, 5], '08:00', '17:00');

        $this->assertSame('Mon, Wed, Fri, 8:00 AM - 5:00 PM', $service->clinicScheduleLabel());
    }

    public function test_notification_quiet_hours_support_an_overnight_window(): void
    {
        $service = $this->serviceWithSchedule([1, 2, 3, 4, 5], '08:00', '17:00');
        $settings = $service->settings();
        $settings->notification_quiet_hours_enabled = true;
        $settings->notification_quiet_hours_start = '20:00';
        $settings->notification_quiet_hours_end = '07:00';

        $this->assertTrue($service->notificationsAreQuiet(Carbon::parse('2026-08-05 22:30:00', 'Asia/Manila')));
        $this->assertTrue($service->notificationsAreQuiet(Carbon::parse('2026-08-06 06:59:00', 'Asia/Manila')));
        $this->assertFalse($service->notificationsAreQuiet(Carbon::parse('2026-08-06 07:00:00', 'Asia/Manila')));
        $this->assertFalse($service->notificationsAreQuiet(Carbon::parse('2026-08-06 12:00:00', 'Asia/Manila')));
    }

    public function test_disabled_notification_quiet_hours_never_pause_delivery(): void
    {
        $service = $this->serviceWithSchedule([1, 2, 3, 4, 5], '08:00', '17:00');
        $service->settings()->notification_quiet_hours_enabled = false;

        $this->assertFalse($service->notificationsAreQuiet(Carbon::parse('2026-08-05 23:30:00', 'Asia/Manila')));
    }

    private function serviceWithSchedule(array $days, string $openTime, string $closeTime): ClinicWorkflowService
    {
        $settings = new Setting();
        $settings->open_time = $openTime;
        $settings->close_time = $closeTime;
        $settings->operating_days = $days;

        $service = new ClinicWorkflowService();
        $property = new ReflectionProperty($service, 'settings');
        $property->setAccessible(true);
        $property->setValue($service, $settings);

        return $service;
    }
}
