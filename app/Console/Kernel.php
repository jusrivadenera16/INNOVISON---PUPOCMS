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
        // Dedicated database connection/queue; existing notifications keep their current behavior.
        $schedule->command('queue:work health_pdf --queue=health-pdf --stop-when-empty --max-time=50 --memory=384 --timeout=120 --tries=3')
            ->everyMinute()->withoutOverlapping(10)->runInBackground()
            ->when(fn () => \Illuminate\Support\Facades\Schema::hasTable('health_pdf_jobs'));
        $schedule->command('health-pdf:cleanup')->hourly()->withoutOverlapping();

        $schedule->command('health-notifications:send-appointment-reminders')
            ->everyMinute()
            ->withoutOverlapping();

        $schedule->command('health-notifications:send-pending-compliance-reminders')
            ->dailyAt('08:00')
            ->withoutOverlapping();

        $schedule->command('consultation-drafts:cleanup')
            ->hourly()
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
