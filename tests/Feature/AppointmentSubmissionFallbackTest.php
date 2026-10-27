<?php

namespace Tests\Feature;

use Tests\TestCase;

class AppointmentSubmissionFallbackTest extends TestCase
{
    public function test_getting_the_appointment_submission_url_redirects_to_booking_safely(): void
    {
        $this->withoutMiddleware();

        $this->get('/student/appointments/store')
            ->assertRedirect(route('student.booking'))
            ->assertSessionHas('booking_retry_notice');
    }
}
