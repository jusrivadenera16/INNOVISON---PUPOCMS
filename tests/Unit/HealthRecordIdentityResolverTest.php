<?php

namespace Tests\Unit;

use App\Support\HealthRecordIdentityResolver;
use PHPUnit\Framework\TestCase;

class HealthRecordIdentityResolverTest extends TestCase
{
    public function test_admin_hub_link_overrides_an_applicant_label(): void
    {
        $record = (object) [
            'record_source' => 'health',
            'user' => (object) [
                'user_type' => 'Applicant',
                'idp_role' => 'applicant',
                'user_role' => 'student',
                'adminHubProfile' => (object) ['employee_number' => '06002'],
            ],
        ];

        $this->assertSame('Admin', HealthRecordIdentityResolver::userType($record));
    }

    public function test_faculty_idp_identity_is_not_overridden_by_applicant_user_type(): void
    {
        $record = (object) [
            'user' => (object) [
                'user_type' => 'Applicant',
                'idp_role' => 'Faculty',
            ],
        ];

        $this->assertSame('Faculty', HealthRecordIdentityResolver::userType($record));
    }

    public function test_official_student_identity_remains_a_student(): void
    {
        $record = (object) [
            'student_number' => '2026-12345-TG-1',
            'user' => (object) [
                'user_type' => 'Student',
                'idp_role' => 'student',
            ],
        ];

        $this->assertSame('Student', HealthRecordIdentityResolver::userType($record));
    }

    public function test_employee_number_uses_canonical_sources_and_never_reference_number(): void
    {
        $record = (object) [
            'reference_number' => '15041',
            'user' => (object) [
                'employee_number' => 'FA001TG2023',
                'adminHubProfile' => (object) ['employee_number' => '06002'],
            ],
        ];

        $this->assertSame('06002', HealthRecordIdentityResolver::employeeNumber($record));
    }

    public function test_legacy_reference_number_is_not_relabelled_as_employee_number(): void
    {
        $record = (object) [
            'reference_number' => '15041',
            'user' => (object) [],
        ];

        $this->assertNull(HealthRecordIdentityResolver::employeeNumber($record));
    }
}
