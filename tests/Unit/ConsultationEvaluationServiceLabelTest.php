<?php

namespace Tests\Unit;

use App\Models\Consultation;
use App\Models\ConsultationEvaluation;
use PHPUnit\Framework\TestCase;

class ConsultationEvaluationServiceLabelTest extends TestCase
{
    public function test_it_labels_applicant_and_employee_approval_evaluations(): void
    {
        $applicantEvaluation = new ConsultationEvaluation(['source_type' => 'applicant_approval']);
        $employeeEvaluation = new ConsultationEvaluation(['source_type' => 'employee_approval']);

        $this->assertSame('Applicant Health Form Review', $applicantEvaluation->serviceLabel());
        $this->assertSame('Employee Health Examination', $employeeEvaluation->serviceLabel());
    }

    public function test_it_uses_the_consultation_service_for_legacy_evaluations(): void
    {
        $evaluation = new ConsultationEvaluation(['source_type' => 'consultation']);
        $evaluation->setRelation('consultation', new Consultation(['service' => 'Medical Consultation']));

        $this->assertSame('Medical Consultation', $evaluation->serviceLabel());
    }
}
