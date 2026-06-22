<?php

namespace Tests\Unit;

use App\Http\Controllers\AppointmentController;
use PHPUnit\Framework\TestCase;
use ReflectionMethod;

class AcademicCourseSourceTest extends TestCase
{
    public function test_role_labels_are_not_accepted_as_academic_courses(): void
    {
        $this->assertSame('', $this->sanitize('designee'));
        $this->assertSame('', $this->sanitize('Faculty / Staff'));
        $this->assertSame('', $this->sanitize('Admin - Clinic Staff'));
    }

    public function test_a_real_guisis_program_name_is_preserved(): void
    {
        $this->assertSame(
            'BSBA-HRM - Bachelor of Science in Business Administration',
            $this->sanitize('BSBA-HRM - Bachelor of Science in Business Administration')
        );
    }

    private function sanitize(?string $course): string
    {
        $controller = new AppointmentController();
        $method = new ReflectionMethod($controller, 'sanitizeAcademicCourse');
        $method->setAccessible(true);

        return $method->invoke($controller, $course);
    }
}
