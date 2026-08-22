<?php

namespace Tests\Unit;

use App\Mail\StudentPortalNotificationMail;
use App\Models\Appointment;
use App\Models\User;
use App\Services\StudentNotificationMailer;
use Illuminate\Support\Facades\Mail;
use Tests\TestCase;

class StudentNotificationMailerTest extends TestCase
{
    public function test_notification_sends_to_the_student_recipient_outside_local_development(): void
    {
        config()->set('services.student_notifications.enabled', true);
        config()->set('services.student_notifications.local_test_recipient', 'pupocms2027@gmail.com');
        Mail::fake();

        $student = new User([
            'name' => 'Test Student',
            'email' => 'student@example.test',
        ]);
        $student->id = 99;

        $result = app(StudentNotificationMailer::class)->send(
            $student,
            'Action needed: Update your PUP Taguig Clinic health record',
            'Health record update requested',
            'The clinic requested replacement files for your health profile.',
            'Open Health Record',
            url('/student/account?view=health-record')
        );

        $this->assertSame('sent', $result['status']);
        Mail::assertSent(StudentPortalNotificationMail::class, function (StudentPortalNotificationMail $mail) {
            return $mail->hasTo('student@example.test');
        });
    }

    public function test_notification_email_keeps_request_details_out_of_the_message(): void
    {
        $mail = new StudentPortalNotificationMail(
            'Action needed: Update your PUP Taguig Clinic health record',
            'Test Student',
            'Health record update requested',
            'The clinic requested replacement files for your health profile.',
            'Open Health Record',
            url('/student/account?view=health-record')
        );

        $rendered = $mail->render();

        $this->assertStringContainsString('Health record update requested', $rendered);
        $this->assertStringContainsString('For your privacy, request details are available only after you sign in', $rendered);
    }

    public function test_notification_email_is_skipped_when_the_user_selects_in_system_only(): void
    {
        config()->set('services.student_notifications.enabled', true);
        Mail::fake();

        $student = new User([
            'name' => 'Test Student',
            'email' => 'student@example.test',
            'notification_email_enabled' => false,
        ]);

        $result = app(StudentNotificationMailer::class)->send(
            $student,
            'Appointment update - PUP Taguig Clinic',
            'Your appointment was not approved',
            'Please sign in to view the appointment update.',
            'View Appointment',
            url('/student/history')
        );

        $this->assertSame('skipped', $result['status']);
        Mail::assertNothingSent();
    }

    public function test_notification_email_is_skipped_when_global_email_notifications_are_disabled(): void
    {
        config()->set('services.student_notifications.enabled', true);
        Mail::fake();

        $mailer = new class extends StudentNotificationMailer {
            protected function globalEmailNotificationsEnabled(): bool
            {
                return false;
            }
        };

        $student = new User([
            'name' => 'Test Student',
            'email' => 'student@example.test',
        ]);

        $result = $mailer->send(
            $student,
            'Appointment update - PUP Taguig Clinic',
            'Appointment update',
            'Please sign in to view the appointment update.',
            'View Appointment',
            url('/student/history')
        );

        $this->assertSame('skipped', $result['status']);
        Mail::assertNothingSent();
    }

    public function test_health_clearance_approval_uses_the_portal_health_record_message(): void
    {
        config()->set('services.student_notifications.enabled', true);
        Mail::fake();

        $student = new User([
            'name' => 'Test Student',
            'email' => 'student@example.test',
        ]);

        $result = app(StudentNotificationMailer::class)->sendHealthRecordNotice($student, 'approved');

        $this->assertSame('sent', $result['status']);
        Mail::assertSent(StudentPortalNotificationMail::class, function (StudentPortalNotificationMail $mail) {
            return $mail->title === 'Your health clearance is approved!'
                && $mail->actionLabel === 'Open Health Record'
                && $mail->statusCard['tone'] === 'success';
        });
    }

    public function test_pending_compliance_reminder_links_back_to_the_health_record(): void
    {
        config()->set('services.student_notifications.enabled', true);
        Mail::fake();

        $student = new User([
            'name' => 'Test Student',
            'email' => 'student@example.test',
        ]);

        $result = app(StudentNotificationMailer::class)
            ->sendHealthRecordNotice($student, 'pending_compliance_reminder');

        $this->assertSame('sent', $result['status']);
        Mail::assertSent(StudentPortalNotificationMail::class, function (StudentPortalNotificationMail $mail) {
            return $mail->title === 'Your health record still needs attention'
                && $mail->actionLabel === 'Open Health Record';
        });
    }

    public function test_appointment_reminder_uses_the_scheduled_appointment_details(): void
    {
        config()->set('services.student_notifications.enabled', true);
        Mail::fake();

        $student = new User([
            'name' => 'Test Student',
            'email' => 'student@example.test',
        ]);
        $appointment = new Appointment([
            'id' => 10,
            'service' => 'Consultation',
            'date' => '2026-08-19',
            'time' => '10:15:00',
            'status' => 'Approved',
        ]);
        $appointment->id = 10;

        $result = app(StudentNotificationMailer::class)->sendAppointmentNotice($student, $appointment, 'reminder');

        $this->assertSame('sent', $result['status']);
        Mail::assertSent(StudentPortalNotificationMail::class, function (StudentPortalNotificationMail $mail) {
            return $mail->title === 'Your appointment is coming up'
                && str_contains($mail->messageText, 'Aug 19, 2026 10:15 AM')
                && ($mail->statusCard['template'] ?? null) === 'appointment_reminder';
        });
    }

    public function test_appointment_reminder_renders_its_dedicated_email_layout(): void
    {
        $mail = new StudentPortalNotificationMail(
            'Appointment reminder - PUP Taguig Clinic',
            'Test Student',
            'Your appointment is in about 15 minutes',
            'Reminder: Your General Consultation appointment is scheduled for Aug 19, 2026 10:15 AM.',
            'View Appointment Details',
            url('/student/history'),
            [
                'template' => 'appointment_reminder',
                'details' => [
                    'date' => 'August 19, 2026',
                    'time' => '10:15 AM',
                    'service' => 'General Consultation',
                ],
            ]
        );

        $rendered = $mail->render();

        $this->assertStringContainsString('UNTIL YOUR APPOINTMENT', $rendered);
        $this->assertStringContainsString('Before your appointment:', $rendered);
        $this->assertStringContainsString('puptclinic@gmail.com', $rendered);
    }
}
