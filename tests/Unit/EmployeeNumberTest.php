<?php

namespace Tests\Unit;

use App\Support\EmployeeNumber;
use PHPUnit\Framework\TestCase;

class EmployeeNumberTest extends TestCase
{
    public function test_it_preserves_leading_zeroes_and_pads_numeric_api_values(): void
    {
        $this->assertSame('06002', EmployeeNumber::normalize('06002'));
        $this->assertSame('06002', EmployeeNumber::normalize(6002));
        $this->assertSame('93060', EmployeeNumber::normalize('93060'));
    }

    public function test_it_rejects_legacy_alphanumeric_identifiers(): void
    {
        $this->assertNull(EmployeeNumber::normalize('FA001TG2023'));
        $this->assertNull(EmployeeNumber::normalize('EMP-100'));
        $this->assertNull(EmployeeNumber::normalize('123456'));
    }

    public function test_faculty_employee_number_takes_precedence_over_legacy_faculty_code(): void
    {
        $this->assertSame('06002', EmployeeNumber::fromFaculty([
            'faculty_code' => 'FA001TG2023',
            'employee_number' => '06002',
        ]));
    }

    public function test_it_accepts_a_five_digit_numeric_legacy_alias_when_no_canonical_field_exists(): void
    {
        $this->assertSame('93060', EmployeeNumber::fromFaculty([
            'faculty_code' => '93060',
        ]));
    }
}
