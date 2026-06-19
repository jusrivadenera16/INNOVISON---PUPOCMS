<?php

namespace Tests\Unit;

use App\Http\Controllers\WalkInController;
use App\Models\HealthProfile;
use PHPUnit\Framework\TestCase;
use ReflectionMethod;

class WalkInHealthProfileAssessmentReviewTest extends TestCase
{
    public function test_it_restores_saved_pending_findings_and_vital_signs(): void
    {
        $profile = new HealthProfile();
        $profile->clearance_status = 'Pending/Conditional';
        $profile->med_cert_findings = 'With Findings';
        $profile->pending_reason = "Medical Condition: Asthma; Incomplete Requirements; Others: Bring updated certificate\nFollow up with the clinic.";
        $profile->medical_condition_remarks = "Asthma\nCondition note.";
        $profile->blood_pressure = '120/80';
        $profile->respiratory_rate = 18;
        $profile->temperature = 36.7;

        $review = $this->assessmentReview($profile);

        $this->assertSame('With Findings', $review['findings_status']);
        $this->assertTrue($review['has_medical_condition']);
        $this->assertSame('Asthma', $review['medical_condition']);
        $this->assertTrue($review['incomplete_requirements']);
        $this->assertSame('Bring updated certificate', $review['other_pending_reason']);
        $this->assertSame('Follow up with the clinic.', $review['condition_remarks']);
        $this->assertSame('120/80', $review['blood_pressure']);
        $this->assertSame(18, $review['respiratory_rate']);
        $this->assertSame(36.7, $review['temperature']);
    }

    public function test_it_does_not_treat_unreviewed_health_form_findings_as_a_nurse_review(): void
    {
        $profile = new HealthProfile();
        $profile->clearance_status = 'For Verification';
        $profile->med_cert_findings = 'With Findings';

        $this->assertSame([], $this->assessmentReview($profile));
    }

    private function assessmentReview(HealthProfile $profile): array
    {
        $controller = new WalkInController();
        $method = new ReflectionMethod($controller, 'healthProfileAssessmentReview');
        $method->setAccessible(true);

        return $method->invoke($controller, $profile);
    }
}
