<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class StudentPortalNotificationMail extends Mailable
{
    use Queueable;
    use SerializesModels;

    public string $studentName;
    public string $title;
    public string $messageText;
    public string $actionLabel;
    public string $actionUrl;
    public array $statusCard;

    public function __construct(
        public string $subjectLine,
        string $studentName,
        string $title,
        string $messageText,
        string $actionLabel,
        string $actionUrl,
        array $statusCard = []
    ) {
        $this->studentName = trim($studentName) ?: 'Student';
        $this->title = trim($title);
        $this->messageText = trim($messageText);
        $this->actionLabel = trim($actionLabel);
        $this->actionUrl = trim($actionUrl);
        $this->statusCard = $statusCard;
    }

    public function build(): self
    {
        $view = match (true) {
            ($this->statusCard['template'] ?? null) === 'appointment_reminder' => 'emails.appointment-reminder',
            $this->statusCard !== [] => 'emails.student-portal-status-notification',
            default => 'emails.student-portal-notification',
        };

        return $this
            ->subject($this->subjectLine)
            ->view($view, [
                'logoUrl' => asset('images/clinic_logo_transparent.png'),
            ])
            ->text('emails.student-portal-notification-text');
    }
}
