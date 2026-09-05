<?php

namespace Tests\Unit;

use App\Http\Controllers\AppointmentController;
use App\Models\Admin;
use App\Models\HealthProfile;
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

    public function test_it_reads_the_documented_guisis_personal_info_fields(): void
    {
        $controller = new AppointmentController();
        $unwrap = new ReflectionMethod($controller, 'unwrapGuisisPayload');
        $unwrap->setAccessible(true);
        $firstValue = new ReflectionMethod($controller, 'firstGuisisValue');
        $firstValue->setAccessible(true);

        $personalInfo = $unwrap->invoke($controller, [
            'data' => [
                'dateOfBirth' => '2001-10-16',
                'studentNumber' => '2026-002-067',
                'gender' => [
                    'id' => 1,
                    'name' => 'Male',
                ],
            ],
            'status' => 'success',
        ]);

        $this->assertSame(
            '2026-002-067',
            $firstValue->invoke($controller, [$personalInfo], ['studentNumber', 'student_number'])
        );
        $this->assertSame(
            '2001-10-16',
            $firstValue->invoke($controller, [$personalInfo], ['dateOfBirth', 'date_of_birth'])
        );
        $this->assertSame(
            'Male',
            $firstValue->invoke($controller, [$personalInfo], ['gender.name', 'gender'])
        );
    }

    public function test_it_reads_the_documented_guisis_student_profile_fields(): void
    {
        $controller = new AppointmentController();
        $unwrap = new ReflectionMethod($controller, 'unwrapGuisisPayload');
        $unwrap->setAccessible(true);
        $firstValue = new ReflectionMethod($controller, 'firstGuisisValue');
        $firstValue->setAccessible(true);

        $profile = $unwrap->invoke($controller, [
            'data' => [
                'course' => [
                    'id' => 10,
                    'code' => 'BSIT',
                    'name' => 'Bachelor of Science in Information Technology',
                ],
                'firstName' => 'Juan',
                'middleName' => [
                    'string' => 'Santos',
                    'valid' => true,
                ],
                'lastName' => 'Dela Cruz',
                'mobileNumber' => '09123456789',
                'section' => '1-1',
                'studentNumber' => '2026-002-067',
                'yearLevel' => 1,
            ],
            'status' => 'success',
        ]);

        $this->assertSame('BSIT', $firstValue->invoke($controller, [$profile], ['course.code']));
        $this->assertSame(
            'Bachelor of Science in Information Technology',
            $firstValue->invoke($controller, [$profile], ['course.name'])
        );
        $this->assertSame('1', $firstValue->invoke($controller, [$profile], ['yearLevel']));
        $this->assertSame('1-1', $firstValue->invoke($controller, [$profile], ['section']));
        $this->assertSame('Santos', $firstValue->invoke($controller, [$profile], ['middleName.string']));
    }

    public function test_admission_reference_resolution_never_uses_a_clinic_reference(): void
    {
        $controller = new AppointmentController();
        $method = new ReflectionMethod($controller, 'resolveReferenceNumber');
        $method->setAccessible(true);

        $user = new User();
        $user->reference_number = 'CLN-061926-1203R1';
        $profile = new HealthProfile();
        $profile->reference_number = 'CLN-061926-1203R1';

        $this->assertSame('', $method->invoke($controller, $user, $profile, []));
        $this->assertSame(
            '2026-1111-1111',
            $method->invoke($controller, $user, $profile, ['reference_number' => '2026-1111-1111'])
        );
    }

    public function test_known_local_directory_accounts_keep_clinic_mode_during_puptas_outages(): void
    {
        $controller = new AppointmentController();
        $method = new ReflectionMethod($controller, 'resolveHealthReferenceMode');
        $method->setAccessible(true);

        $user = new User();
        $user->user_role = User::ROLE_ADMIN;

        $this->assertSame(
            'clinic',
            $method->invoke($controller, $user, new Admin(), [], 'unavailable')
        );
    }

    public function test_applicant_role_stays_in_admission_mode_when_idp_lookup_returns_not_found(): void
    {
        $controller = new AppointmentController();
        $method = new ReflectionMethod($controller, 'resolveHealthReferenceMode');
        $method->setAccessible(true);

        $user = new User();
        $user->user_role = User::ROLE_STUDENT;
        $user->idp_role = 'applicant';
        $user->user_type = 'Applicant';
        $user->setRelation('healthProfile', null);

        $this->assertSame(
            'admission',
            $method->invoke($controller, $user, new Admin(), [], 'not_found')
        );
        $this->assertSame(
            'verification_unavailable',
            $method->invoke($controller, $user, new Admin(), [], 'unavailable')
        );
    }

    public function test_admission_applicants_cannot_use_manual_student_number_mode(): void
    {
        $controller = new AppointmentController();
        $method = new ReflectionMethod($controller, 'canUseManualStudentNumberMode');
        $method->setAccessible(true);

        $applicant = new User();
        $applicant->user_role = User::ROLE_STUDENT;
        $applicant->idp_role = 'applicant';
        $applicant->user_type = 'Applicant';
        $applicant->setRelation('healthProfile', null);

        $this->assertFalse($method->invoke($controller, $applicant, null, null, 'not_found'));

    }

    public function test_current_students_and_ojt_accounts_can_use_manual_student_number_mode(): void
    {
        $controller = new AppointmentController();
        $method = new ReflectionMethod($controller, 'canUseManualStudentNumberMode');
        $method->setAccessible(true);

        $student = new User();
        $student->user_role = User::ROLE_STUDENT;
        $student->idp_role = 'student';
        $student->user_type = 'Student';
        $student->setRelation('healthProfile', null);

        $this->assertTrue($method->invoke($controller, $student, null, null, 'not_found'));
    }

    public function test_local_student_choice_uses_student_number_mode_even_when_puptas_has_data(): void
    {
        $controller = new AppointmentController();
        $method = new ReflectionMethod($controller, 'resolveHealthReferenceMode');
        $method->setAccessible(true);

        $user = new User();
        $user->idp_role = 'student';
        $user->user_role = User::ROLE_STUDENT;
        $user->user_type = 'Student';
        $user->setRelation('healthProfile', null);

        $this->assertSame(
            'student_number',
            $method->invoke($controller, $user, new Admin(), ['reference_number' => 'STALE-PUPTAS-REFERENCE'], 'found')
        );
    }
}
