<?php

namespace Tests\Unit;

use App\Http\Controllers\AppointmentController;
use PHPUnit\Framework\TestCase;
use ReflectionMethod;

class StudentNotificationPrivacyCopyTest extends TestCase
{
    public function test_appointment_notifications_hide_service_and_schedule_details(): void
    {
        $notification = $this->decorate([
            'id' => 'appointment-expired-abc123',
            'title' => 'Appointment Expired',
            'message' => 'Your BP Monitoring on Sep 18 at 9:00 AM expired because it was not approved in time.',
            'link' => '/student/history',
        ]);

        $this->assertSame('Appointment Request Expired', $notification['title']);
        $this->assertSame('Your appointment request expired before approval.', $notification['message']);
        $this->assertSame('Book Again', $notification['action_label']);
        $this->assertSame('/student/history', $notification['link']);
        $this->assertStringNotContainsString('BP Monitoring', $notification['message']);
        $this->assertStringNotContainsString('Sep 18', $notification['message']);
        $this->assertStringNotContainsString('9:00 AM', $notification['message']);
    }

    public function test_health_record_notifications_hide_record_details(): void
    {
        $notification = $this->decorate([
            'id' => 'health-record-pullout-abc123',
            'title' => 'Health Record Pulled Out',
            'message' => 'Your record for asthma has been pulled out and archived.',
            'link' => '/student/account?view=health-record',
        ]);

        $this->assertSame('Health Record Update', $notification['title']);
        $this->assertSame('Your Health Record has a status update. Open your account to review it.', $notification['message']);
        $this->assertSame('View Health Record', $notification['action_label']);
        $this->assertSame('/student/account?view=health-record', $notification['link']);
        $this->assertStringNotContainsString('asthma', $notification['message']);
    }

    public function test_appointment_approval_and_rejection_are_clear_without_visit_details(): void
    {
        $approved = $this->decorate([
            'id' => 'appointment-approved-abc123',
            'message' => 'Your BP Monitoring on Sep 18 has been approved.',
        ]);
        $rejected = $this->decorate([
            'id' => 'appointment-rejected-abc123',
            'message' => 'Your appointment request was not approved.',
        ]);

        $this->assertSame('Appointment Approved', $approved['title']);
        $this->assertSame('Your appointment has been approved.', $approved['message']);
        $this->assertSame('Appointment Rejected', $rejected['title']);
        $this->assertSame('Your appointment request was not approved.', $rejected['message']);
        $this->assertStringNotContainsString('BP Monitoring', $approved['message']);
        $this->assertStringNotContainsString('Sep 18', $approved['message']);
    }

    public function test_health_form_status_copy_does_not_expose_medical_details(): void
    {
        $notification = $this->decorate([
            'id' => 'health-form-correction-abc123',
            'message' => 'The clinic requested corrections to the form due to asthma-related information.',
        ]);

        $this->assertSame('Health Form Changes Requested', $notification['title']);
        $this->assertSame('The clinic requested updates to your Health Form.', $notification['message']);
        $this->assertStringNotContainsString('asthma', strtolower($notification['message']));
    }

    public function test_consultation_evaluation_notifications_do_not_name_the_service(): void
    {
        $notification = $this->decorate([
            'id' => 'consultation-evaluation-abc123',
            'message' => 'The clinic invited you to evaluate your recent General Consultation (Sep 18).',
        ]);

        $this->assertSame('Clinic Update', $notification['title']);
        $this->assertSame('There is a new update in your clinic account.', $notification['message']);
        $this->assertSame('Open Update', $notification['action_label']);
    }

    public function test_public_announcement_content_is_preserved(): void
    {
        $notification = $this->decorate([
            'id' => 'announcement-abc123',
            'title' => 'Clinic Schedule',
            'message' => 'The clinic will close at 3 PM on Friday.',
        ]);

        $this->assertSame('Clinic Schedule', $notification['title']);
        $this->assertSame('The clinic will close at 3 PM on Friday.', $notification['message']);
    }

    private function decorate(array $notification): array
    {
        $controller = new AppointmentController();
        $method = new ReflectionMethod($controller, 'decorateStudentNotification');
        $method->setAccessible(true);

        return $method->invoke($controller, $notification);
    }
}
