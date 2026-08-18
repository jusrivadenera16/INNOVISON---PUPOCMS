<?php

namespace App\Services;

use App\Mail\StudentPortalNotificationMail;
use App\Models\Appointment;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class StudentNotificationMailer
{
    /**
     * Sends a transactional email for a student-facing portal notification.
     * Local testing may safely redirect delivery to one configured mailbox.
     *
     * @return array{status: 'sent'|'skipped'|'failed'}
     */
    public function send(
        User $student,
        string $subject,
        string $title,
        string $message,
        string $actionLabel,
        string $actionUrl,
        array $statusCard = []
    ): array {
        if (!config('services.student_notifications.enabled', false)) {
            return ['status' => 'skipped'];
        }

        if ($student->getAttribute('notification_email_enabled') === false) {
            return ['status' => 'skipped'];
        }

        $recipient = $this->resolveRecipient($student);
        if ($recipient === null) {
            return ['status' => 'skipped'];
        }

        try {
            Mail::to($recipient)->send(new StudentPortalNotificationMail(
                $subject,
                (string) $student->name,
                $title,
                $message,
                $actionLabel,
                $actionUrl,
                $statusCard
            ));

            return ['status' => 'sent'];
        } catch (\Throwable $exception) {
            Log::warning('Student portal notification email could not be sent.', [
                'student_id' => $student->id,
                'notification_title' => $title,
                'exception' => $exception::class,
            ]);

            return ['status' => 'failed'];
        }
    }

    /**
     * Sends one of the privacy-safe Health Record notifications available in the portal.
     *
     * @return array{status: 'sent'|'skipped'|'failed'}
     */
    public function sendHealthRecordNotice(User $recipient, string $event, array $details = []): array
    {
        $notice = match ($event) {
            'approved' => [
                'subject' => 'Health clearance approved - PUP Taguig Clinic',
                'title' => 'Your health clearance is approved!',
                'message' => 'Your health profile has been reviewed and approved by the PUP Taguig Clinic.',
                'action_label' => 'Open Health Record',
                'action_url' => url('/student/account?view=health-record'),
                'status_card' => $this->healthApprovalStatusCard($details),
            ],
            'new_form' => [
                'subject' => 'Action needed: Complete your Health Information Form',
                'title' => 'New Health Information Form requested',
                'message' => 'The clinic requested a new Health Information Form. You may now fill it out and submit it.',
                'action_label' => 'Open Health Information Form',
                'action_url' => route('health.form'),
            ],
            'health_form_correction' => [
                'subject' => 'Action needed: Correct your Health Information Form',
                'title' => 'Health Information Form correction requested',
                'message' => 'The clinic requested corrections to your Health Information Form. You may now edit and resubmit it.',
                'action_label' => 'Open Health Information Form',
                'action_url' => route('health.form'),
            ],
            default => [
                'subject' => 'Action needed: Update your PUP Taguig Clinic health record',
                'title' => 'Health record update requested',
                'message' => 'The clinic requested replacement files for your health profile.',
                'action_label' => 'Open Health Record',
                'action_url' => url('/student/account?view=health-record'),
            ],
        };

        return $this->send(
            $recipient,
            $notice['subject'],
            $notice['title'],
            $notice['message'],
            $notice['action_label'],
            $notice['action_url'],
            $notice['status_card'] ?? []
        );
    }

    /**
     * Sends an appointment notification to the patient account, regardless of role.
     *
     * @return array{status: 'sent'|'skipped'|'failed'}
     */
    public function sendAppointmentNotice(User $recipient, Appointment $appointment, string $event): array
    {
        $service = trim((string) $appointment->service) ?: 'clinic';
        $schedule = $this->formatAppointmentSchedule($appointment);

        $notice = match ($event) {
            'approved' => [
                'subject' => 'Appointment approved - PUP Taguig Clinic',
                'title' => 'Your appointment is approved!',
                'message' => "Your {$service} appointment on {$schedule} has been approved.",
                'action_label' => 'View Appointment',
                'action_url' => url('/student/history'),
                'status_card' => $this->appointmentStatusCard($appointment, 'approved', $schedule, $service),
            ],
            'rejected' => [
                'subject' => 'Appointment update - PUP Taguig Clinic',
                'title' => 'Your appointment was not approved',
                'message' => "Your {$service} appointment scheduled for {$schedule} was not approved. You may book a new appointment if you still need clinic assistance.",
                'action_label' => 'Book an Appointment',
                'action_url' => url('/student/booking'),
                'status_card' => $this->appointmentStatusCard($appointment, 'rejected', $schedule, $service),
            ],
            'rescheduled' => [
                'subject' => 'Appointment rescheduled - PUP Taguig Clinic',
                'title' => 'Your appointment was rescheduled',
                'message' => "Your {$service} appointment has been rescheduled to {$schedule}.",
                'action_label' => 'View Appointment',
                'action_url' => url('/student/history'),
                'status_card' => $this->appointmentStatusCard($appointment, 'rescheduled', $schedule, $service),
            ],
            'feedback' => [
                'subject' => 'Share feedback about your clinic visit',
                'title' => 'Your consultation is complete',
                'message' => "Your consultation for {$service} has been completed. Please share your feedback with the clinic.",
                'action_label' => 'Share Feedback',
                'action_url' => route('student.feedback.show', ['appointment' => $appointment->id]),
            ],
            'reminder' => [
                'subject' => 'Appointment reminder - PUP Taguig Clinic',
                'title' => 'Your appointment is in about 15 minutes',
                'message' => "Reminder: Your {$service} appointment is scheduled for {$schedule}.",
                'action_label' => 'View Appointment Details',
                'action_url' => url('/student/history'),
                'status_card' => $this->appointmentReminderCard($appointment, $service),
            ],
            default => [
                'subject' => 'Appointment request received - PUP Taguig Clinic',
                'title' => $appointment->status === 'Approved'
                    ? 'Your appointment was booked and approved'
                    : 'Your appointment request was received',
                'message' => $appointment->status === 'Approved'
                    ? "Your {$service} appointment on {$schedule} was booked and approved automatically."
                    : "Your {$service} appointment request for {$schedule} was received and is awaiting clinic review.",
                'action_label' => 'View Appointment',
                'action_url' => url('/student/history'),
                'status_card' => $appointment->status === 'Approved'
                    ? $this->appointmentStatusCard($appointment, 'approved', $schedule, $service)
                    : [],
            ],
        };

        return $this->send(
            $recipient,
            $notice['subject'],
            $notice['title'],
            $notice['message'],
            $notice['action_label'],
            $notice['action_url'],
            $notice['status_card'] ?? []
        );
    }

    private function resolveRecipient(User $student): ?string
    {
        $recipient = strtolower(trim((string) $student->email));

        if (app()->environment('local')) {
            $localTestRecipient = strtolower(trim((string) config('services.student_notifications.local_test_recipient', '')));
            if ($this->isValidEmail($localTestRecipient)) {
                $recipient = $localTestRecipient;
            }
        }

        return $this->isValidEmail($recipient) ? $recipient : null;
    }

    private function isValidEmail(string $email): bool
    {
        return filter_var($email, FILTER_VALIDATE_EMAIL) !== false;
    }

    private function formatAppointmentSchedule(Appointment $appointment): string
    {
        try {
            return Carbon::parse($appointment->date . ' ' . $appointment->time)
                ->format('M d, Y g:i A');
        } catch (\Throwable) {
            return 'the scheduled time';
        }
    }

    private function healthApprovalStatusCard(array $details): array
    {
        $approvedAt = trim((string) ($details['approved_at'] ?? '')) ?: now()->format('F j, Y');
        $referenceNumber = trim((string) ($details['reference_number'] ?? '')) ?: 'Available in the Student Portal';
        $campus = trim((string) ($details['campus'] ?? '')) ?: 'PUP Taguig';

        return [
            'tone' => 'success',
            'icon' => 'check',
            'status_icon' => $this->statusIconSvg('approved'),
            'status_label' => 'Status',
            'status_value' => 'APPROVED',
            'details' => [
                ['icon' => '&#128197;', 'label' => 'Date Approved', 'value' => $approvedAt],
                ['icon' => '#', 'label' => 'Reference Number', 'value' => $referenceNumber],
                ['icon' => '&#9679;', 'label' => 'Campus', 'value' => $campus],
            ],
        ];
    }

    private function appointmentStatusCard(Appointment $appointment, string $event, string $schedule, string $service): array
    {
        $isWarning = in_array($event, ['rejected', 'rescheduled'], true);
        $appointmentNumber = trim((string) $appointment->apt_id) ?: ('#' . ($appointment->id ?: 'N/A'));
        $scheduleLabel = match ($event) {
            'rejected' => 'Requested Schedule',
            'rescheduled' => 'New Schedule',
            default => 'Appointment Schedule',
        };
        $statusValue = match ($event) {
            'rejected' => 'NOT APPROVED',
            'rescheduled' => 'RESCHEDULED',
            default => 'APPROVED',
        };

        return [
            'tone' => $isWarning ? 'warning' : 'success',
            'icon' => $isWarning ? 'x' : 'check',
            'status_icon' => $this->statusIconSvg($event),
            'status_label' => 'Status',
            'status_value' => $statusValue,
            'details' => [
                ['icon' => '&#128197;', 'label' => $scheduleLabel, 'value' => $schedule],
                ['icon' => '#', 'label' => 'Appointment Number', 'value' => $appointmentNumber],
                ['icon' => '&#128203;', 'label' => 'Clinic Service', 'value' => $service],
            ],
        ];
    }

    private function appointmentReminderCard(Appointment $appointment, string $service): array
    {
        try {
            $appointmentAt = Carbon::parse($appointment->date . ' ' . $appointment->time);
            $date = $appointmentAt->format('F j, Y');
            $time = $appointmentAt->format('g:i A');
        } catch (\Throwable) {
            $date = 'Your appointment date';
            $time = 'Your appointment time';
        }

        return [
            'template' => 'appointment_reminder',
            'details' => [
                'date' => $date,
                'time' => $time,
                'service' => $service,
            ],
        ];
    }

    private function statusIconSvg(string $event): string
    {
        $path = match ($event) {
            'rejected' => '<path stroke-linecap="round" stroke-linejoin="round" d="m9.75 9.75 4.5 4.5m0-4.5-4.5 4.5M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z" />',
            'rescheduled' => '<path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m9-.75a9 9 0 1 1-18 0 9 9 0 0 1 18 0Zm-9 3.75h.008v.008H12v-.008Z" />',
            default => '<path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75 11.25 15 15 9.75M21 12c0 1.268-.63 2.39-1.593 3.068a3.745 3.745 0 0 1-1.043 3.296 3.745 3.745 0 0 1-3.296 1.043A3.745 3.745 0 0 1 12 21c-1.268 0-2.39-.63-3.068-1.593a3.746 3.746 0 0 1-3.296-1.043 3.745 3.745 0 0 1-1.043-3.296A3.745 3.745 0 0 1 3 12c0-1.268.63-2.39 1.593-3.068a3.745 3.745 0 0 1 1.043-3.296 3.746 3.746 0 0 1 3.296-1.043A3.746 3.746 0 0 1 12 3c1.268 0 2.39.63 3.068 1.593a3.746 3.746 0 0 1 3.296 1.043 3.746 3.746 0 0 1 1.043 3.296A3.745 3.745 0 0 1 21 12Z" />',
        };

        return '<svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" width="28" height="28" stroke-width="1.5" stroke="currentColor" style="display:inline-block; width:28px; height:28px; vertical-align:middle;">' . $path . '</svg>';
    }
}
