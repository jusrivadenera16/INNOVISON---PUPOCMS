<?php

namespace Tests\Unit;

use App\Models\HealthProfile;
use App\Services\MarClearanceIssuanceService;
use ReflectionMethod;
use Tests\TestCase;

class MarClearanceIssuanceServiceTest extends TestCase
{
    public function test_mar_medical_condition_filter_uses_final_review_with_findings(): void
    {
        $profile = new HealthProfile();
        $profile->final_review_findings_status = 'With Findings';

        $this->assertTrue($this->matchesMedicalCondition($profile, 'with_condition'));
        $this->assertFalse($this->matchesMedicalCondition($profile, 'without_condition'));
    }

    public function test_final_review_no_findings_overrides_old_condition_remarks(): void
    {
        $profile = new HealthProfile();
        $profile->final_review_findings_status = 'No Findings / Normal';
        $profile->medical_condition_remarks = 'Previous assessment note';

        $this->assertFalse($this->matchesMedicalCondition($profile, 'with_condition'));
        $this->assertTrue($this->matchesMedicalCondition($profile, 'without_condition'));
    }

    public function test_legacy_record_falls_back_to_condition_remarks_without_final_review(): void
    {
        $profile = new HealthProfile();
        $profile->medical_condition_remarks = 'Asthma';

        $this->assertTrue($this->matchesMedicalCondition($profile, 'with_condition'));
    }

    private function matchesMedicalCondition(HealthProfile $profile, string $filterValue): bool
    {
        $service = new MarClearanceIssuanceService();
        $method = new ReflectionMethod($service, 'matchesApplicantFilters');
        $method->setAccessible(true);

        return $method->invoke($service, $profile, null, [
            'filters' => [
                'medical_condition' => $filterValue,
            ],
        ]);
    }
}
