<?php

namespace Tests\Unit;

use App\Http\Controllers\AppointmentController;
use App\Models\User;
use Carbon\Carbon;
use PHPUnit\Framework\TestCase;
use ReflectionMethod;

class AppointmentControllerPuptasIdentityTest extends TestCase
{
    public function test_it_maps_the_confirmed_nested_puptas_user_payload(): void
    {
        $controller = new AppointmentController();
        $method = new ReflectionMethod($controller, 'normalizePuptasApplicantIdentity');
        $method->setAccessible(true);

        $identity = $method->invoke($controller, [
            'user' => [
                'id' => '1702',
                'reference_number' => '2026-1111-1111',
                'firstname' => 'The',
                'lastname' => 'Tester',
                'email' => 'dummyjm15@gmail.com',
                'school_year' => '2026-2027',
            ],
        ]);

        $this->assertTrue($identity['available']);
        $this->assertSame('The', $identity['first_name']);
        $this->assertSame('', $identity['middle_name']);
        $this->assertSame('Tester', $identity['last_name']);
        $this->assertSame('The Tester', $identity['full_name']);
        $this->assertSame('2026-1111-1111', $identity['reference_number']);
        $this->assertSame('dummyjm15@gmail.com', $identity['email']);
        $this->assertSame('2026-2027', $identity['school_year']);
    }

    public function test_school_year_rolls_over_in_may_when_puptas_does_not_supply_it(): void
    {
        $controller = new AppointmentController();
        $method = new ReflectionMethod($controller, 'resolveSchoolYear');
        $method->setAccessible(true);
        $user = new User();

        Carbon::setTestNow('2026-04-30 12:00:00');
        $this->assertSame('2025-2026', $method->invoke($controller, null, $user));

        Carbon::setTestNow('2026-05-01 12:00:00');
        $this->assertSame('2026-2027', $method->invoke($controller, null, $user));

        Carbon::setTestNow();
    }

    public function test_it_unwraps_common_guisis_response_envelopes(): void
    {
        $controller = new AppointmentController();
        $method = new ReflectionMethod($controller, 'unwrapGuisisPayload');
        $method->setAccessible(true);

        $profile = $method->invoke($controller, [
            'data' => [
                'student' => [
                    'studentNumber' => '2026-12345',
                    'firstName' => 'Juan',
                ],
            ],
        ]);

        $this->assertSame('2026-12345', $profile['studentNumber']);
        $this->assertSame('Juan', $profile['firstName']);
    }

    public function test_it_builds_a_guisis_address_from_separate_fields(): void
    {
        $controller = new AppointmentController();
        $method = new ReflectionMethod($controller, 'buildGuisisAddress');
        $method->setAccessible(true);

        $address = $method->invoke($controller, [[
            'streetAddress' => '123 Main Street',
            'barangay' => 'Lower Bicutan',
            'city' => 'Taguig',
            'postalCode' => '1632',
        ]]);

        $this->assertSame('123 Main Street, Lower Bicutan, Taguig, 1632', $address);
    }
}
