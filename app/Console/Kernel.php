<?php

namespace App\Console; // This MUST match the folder path app/Console

use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;

class Kernel extends ConsoleKernel
{
    /**
     * Define the application's command schedule.
     */
    protected function schedule(Schedule $schedule): void
    {
        $schedule->command('health-notifications:send-appointment-reminders')
            ->everyMinute()
            ->withoutOverlapping();

        $schedule->command('health-notifications:send-pending-compliance-reminders')
            ->dailyAt('08:00')
            ->withoutOverlapping();
    }

    /**
     * Register the commands for the application.
     */
    protected function commands(): void
    {
        $this->load(__DIR__.'/Commands');

        require base_path('routes/console.php');
    }
}
