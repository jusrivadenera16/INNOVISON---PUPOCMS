<?php

namespace App\Console\Commands;

use App\Models\Appointment;
use App\Services\StudentNotificationMailer;
use Illuminate\Console\Command;

class SendAppointmentReminderEmails extends Command
{
    protected $signature = 'health-notifications:send-appointment-reminders';

    protected $description = 'Send one appointment reminder email about 15 minutes before an approved consultation.';

    public function handle(StudentNotificationMailer $mailer): int
    {
        if (!config('services.student_notifications.enabled', false)) {
            $this->info('Student notification emails are disabled.');

            return self::SUCCESS;
        }

        $reminderMinutes = max(1, (int) config('services.student_notifications.appointment_reminder_minutes', 15));
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
