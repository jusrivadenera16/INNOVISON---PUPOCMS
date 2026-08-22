<?php

namespace App\Console\Commands;

use App\Models\Appointment;
use App\Services\ClinicWorkflowService;
use App\Services\StudentNotificationMailer;
use Illuminate\Console\Command;

class SendAppointmentReminderEmails extends Command
{
    protected $signature = 'health-notifications:send-appointment-reminders';

    protected $description = 'Send appointment reminder emails using the configured reminder timing.';

    public function handle(StudentNotificationMailer $mailer, ClinicWorkflowService $workflow): int
    {
        if (!config('services.student_notifications.enabled', false)) {
            $this->info('Student notification emails are disabled.');

            return self::SUCCESS;
        }

        if ($workflow->settings()->email_notifications === false) {
            $this->info('Email notifications are disabled in System Preferences.');

            return self::SUCCESS;
        }

        $reminderHours = max(0, (int) ($workflow->settings()->appointment_reminder_hours ?? 0));
        if ($reminderHours === 0) {
            $this->info('Appointment reminder emails are disabled.');

            return self::SUCCESS;
        }

        $reminderMinutes = $reminderHours * 60;
        $windowStart = now()->addMinutes($reminderMinutes)->startOfMinute();
        $windowEnd = $windowStart->copy()->addMinute()->endOfMinute();

        $appointments = Appointment::query()
            ->with('user')
            ->where('status', 'Approved')
            ->whereNotNull('date')
            ->whereNotNull('time')
            ->whereNull('appointment_reminder_email_sent_at')
            ->whereRaw('TIMESTAMP(`date`, `time`) >= ? AND TIMESTAMP(`date`, `time`) <= ?', [
                $windowStart->format('Y-m-d H:i:s'),
                $windowEnd->format('Y-m-d H:i:s'),
            ])
            ->orderBy('id')
            ->get();

        $sent = 0;
        $notSent = 0;

        foreach ($appointments as $appointment) {
            if (!$appointment->user) {
                continue;
            }

            $claimedAt = now();
            $claimed = Appointment::query()
                ->whereKey($appointment->id)
                ->whereNull('appointment_reminder_email_sent_at')
                ->update(['appointment_reminder_email_sent_at' => $claimedAt]);

            if ($claimed !== 1) {
                continue;
            }

            $result = $mailer->sendAppointmentNotice($appointment->user, $appointment, 'reminder');
            if ($result['status'] === 'sent') {
                $sent++;
                continue;
            }

            Appointment::query()
                ->whereKey($appointment->id)
                ->update(['appointment_reminder_email_sent_at' => null]);
            $notSent++;
        }

        $this->info("Appointment reminders sent: {$sent}. Not sent: {$notSent}.");

        return self::SUCCESS;
    }
}
