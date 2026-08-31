<?php

namespace Tests\Unit;

use App\Http\Controllers\AdminController;
use PHPUnit\Framework\TestCase;
use ReflectionMethod;

class GuisisStudentNormalizationTest extends TestCase
{
    public function test_direct_guisis_student_payload_is_normalized_for_the_dashboard(): void
    {
        $controller = new AdminController();
        $method = new ReflectionMethod($controller, 'normalizeGuisisStudentResults');
        $method->setAccessible(true);

        $result = $method->invoke($controller, [
            'idpUuid' => '4784198f-a082-4244-9a7d-ecfaef346b93',
            'studentNumber' => '2026-00316-TG-1',
            'firstName' => 'Faith Youri',
            'middleName' => 'Atanante',
            'lastName' => 'Sacramento',
            'email' => 'sacramentofaith975@gmail.com',
            'program' => [
                'code' => 'BSPSYCH',
                'name' => 'Bachelor of Science in Psychology',
            ],
            'yearLevel' => 1,
            'section' => '1',
        ], 'sacramentofaith975@gmail.com');

        $this->assertCount(1, $result);
        $this->assertSame('2026-00316-TG-1', $result[0]['student_number']);
        $this->assertSame('4784198f-a082-4244-9a7d-ecfaef346b93', $result[0]['idp_uuid']);
        $this->assertSame('Faith Youri', $result[0]['first_name']);
        $this->assertSame('BSPSYCH', $result[0]['course_code']);
        $this->assertSame('1', $result[0]['year_level']);
        $this->assertSame('1', $result[0]['section']);
    }

    public function test_list_response_envelope_is_normalized_for_sync_matching(): void
    {
        $controller = new AdminController();
        $method = new ReflectionMethod($controller, 'normalizeGuisisStudentResults');
        $method->setAccessible(true);

        $result = $method->invoke($controller, [
            'data' => [
                'items' => [[
                    'idpUuid' => '4784198f-a082-4244-9a7d-ecfaef346b93',
                    'studentNumber' => '2026-00316-TG-1',
                    'email' => 'sacramentofaith975@gmail.com',
                ]],
            ],
        ], '4784198f-a082-4244-9a7d-ecfaef346b93');

        $this->assertCount(1, $result);
        $this->assertSame('2026-00316-TG-1', $result[0]['student_number']);
        $this->assertSame('4784198f-a082-4244-9a7d-ecfaef346b93', $result[0]['idp_uuid']);
    }
}
