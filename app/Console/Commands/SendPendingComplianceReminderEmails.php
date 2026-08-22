<?php

namespace App\Console\Commands;

use App\Models\EmployeeHealthProfile;
use App\Models\HealthProfile;
use App\Services\ClinicWorkflowService;
use App\Services\StudentNotificationMailer;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Database\Eloquent\Builder;

class SendPendingComplianceReminderEmails extends Command
{
    protected $signature = 'health-notifications:send-pending-compliance-reminders';

    protected $description = 'Remind users about unresolved health record compliance requirements.';

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

        $intervalDays = max(0, (int) ($workflow->settings()->pending_compliance_reminder_days ?? 0));
        if ($intervalDays === 0) {
            $this->info('Pending compliance reminders are disabled.');

            return self::SUCCESS;
        }

        $dueBefore = now()->subDays($intervalDays);
        [$studentSent, $studentNotSent] = $this->sendHealthProfileReminders($mailer, $dueBefore);
        [$employeeSent, $employeeNotSent] = $this->sendEmployeeProfileReminders($mailer, $dueBefore);

        $this->info(sprintf(
            'Pending compliance reminders sent: %d. Not sent: %d.',
            $studentSent + $employeeSent,
            $studentNotSent + $employeeNotSent
        ));

        return self::SUCCESS;
    }

    private function sendHealthProfileReminders(StudentNotificationMailer $mailer, Carbon $dueBefore): array
    {
        $profiles = HealthProfile::query()
            ->with('user')
            ->whereHas('user')
            ->where(function (Builder $query): void {
                $query->whereIn('clearance_status', ['Pending/Conditional', 'Pending Resubmission'])
                    ->orWhere(function (Builder $resubmission): void {
                        $resubmission->whereNotNull('resubmission_requested_at')
                            ->whereNull('resubmitted_at');
                    });
            })
            ->where(function (Builder $query) use ($dueBefore): void {
                $query->where('resubmission_requested_at', '<=', $dueBefore)
                    ->orWhere(function (Builder $fallback) use ($dueBefore): void {
                        $fallback->whereNull('resubmission_requested_at')
                            ->where('updated_at', '<=', $dueBefore);
                    });
            })
            ->where(function (Builder $query) use ($dueBefore): void {
                $query->whereNull('pending_compliance_reminder_sent_at')
                    ->orWhere('pending_compliance_reminder_sent_at', '<=', $dueBefore);
            })
            ->orderBy('id')
            ->get();

        return $this->sendReminders($profiles, HealthProfile::class, $mailer, $dueBefore);
    }

    private function sendEmployeeProfileReminders(StudentNotificationMailer $mailer, Carbon $dueBefore): array
    {
        $profiles = EmployeeHealthProfile::query()
            ->with('user')
            ->whereHas('user')
            ->where(function (Builder $query): void {
                $query->whereIn('clearance_status', ['Pending/Conditional', 'Pending Resubmission'])
                    ->orWhere(function (Builder $resubmission): void {
                        $resubmission->whereNotNull('resubmission_requested_at')
                            ->whereNull('resubmitted_at');
                    });
            })
            ->where(function (Builder $query) use ($dueBefore): void {
                $query->where('resubmission_requested_at', '<=', $dueBefore)
                    ->orWhere(function (Builder $fallback) use ($dueBefore): void {
                        $fallback->whereNull('resubmission_requested_at')
                            ->where('updated_at', '<=', $dueBefore);
                    });
            })
            ->where(function (Builder $query) use ($dueBefore): void {
                $query->whereNull('pending_compliance_reminder_sent_at')
                    ->orWhere('pending_compliance_reminder_sent_at', '<=', $dueBefore);
            })
            ->orderBy('id')
            ->get();

        return $this->sendReminders($profiles, EmployeeHealthProfile::class, $mailer, $dueBefore);
    }

    private function sendReminders($profiles, string $modelClass, StudentNotificationMailer $mailer, Carbon $dueBefore): array
    {
        $sent = 0;
        $notSent = 0;

        foreach ($profiles as $profile) {
            $previousSentAt = $profile->pending_compliance_reminder_sent_at;
            $claimedAt = now();
            $claimed = $modelClass::query()
                ->whereKey($profile->id)
                ->where(function (Builder $query) use ($dueBefore): void {
                    $query->whereNull('pending_compliance_reminder_sent_at')
                        ->orWhere('pending_compliance_reminder_sent_at', '<=', $dueBefore);
                })
                ->toBase()
                ->update(['pending_compliance_reminder_sent_at' => $claimedAt]);

            if ($claimed !== 1) {
                continue;
            }

            $result = $mailer->sendHealthRecordNotice($profile->user, 'pending_compliance_reminder');
            if ($result['status'] === 'sent') {
                $sent++;
                continue;
            }

            $modelClass::query()
                ->whereKey($profile->id)
                ->where('pending_compliance_reminder_sent_at', $claimedAt)
                ->toBase()
                ->update(['pending_compliance_reminder_sent_at' => $previousSentAt]);
            $notSent++;
        }

        return [$sent, $notSent];
    }
}
