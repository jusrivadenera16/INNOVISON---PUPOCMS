<?php

namespace Tests\Unit;

use App\Services\MarPatientTypeNormalizer;
use PHPUnit\Framework\TestCase;

class MarPatientTypeNormalizerTest extends TestCase
{
    public function test_report_columns_use_shared_patient_type_mapping(): void
    {
        $normalizer = new MarPatientTypeNormalizer();

        $this->assertSame('student', $normalizer->normalize('Applicant'));
        $this->assertSame('student', $normalizer->normalize('Student / OJT'));
        $this->assertSame('faculty', $normalizer->normalize('Faculty member'));
        $this->assertSame('admin', $normalizer->normalize('Non-teaching Staff / Admins'));
        $this->assertSame('dependent', $normalizer->normalize('Guest / Dependent'));
        $this->assertNull($normalizer->normalize('System Owner'));
    }
}
