<?php

namespace Tests\Feature;

use App\Models\HealthProfile;
use App\Services\HealthFormReportFindings;
use Tests\TestCase;

class HealthFormReportFindingsTest extends TestCase
{
    private function text(array $attributes): string
    {
        return app(HealthFormReportFindings::class)->text(new HealthProfile($attributes));
    }

    public function test_report_excludes_the_reported_legacy_notes_without_changing_them(): void
    {
        $notes = '- No subjective complaints. - PE: unremarkable. - Past Medical History/ Surgical history: unremarkable - MC and CXR noted. - MC issued. dr. arellano';
        $profile = new HealthProfile(['has_illness' => 'No', 'physical_assessment_status' => 'Not Yet Conducted', 'assessment_remarks' => $notes, 'med_assessment_remarks' => $notes]);
        $this->assertSame('No condition or abnormal findings reported.', app(HealthFormReportFindings::class)->text($profile));
        $this->assertSame($notes, $profile->assessment_remarks);
    }

    public function test_it_retains_declared_conditions_and_relevant_findings_and_deduplicates(): void
    {
        $this->assertSame('Asthma; Hypertension; CXR: abnormal opacity', $this->text([
            'has_illness' => 'Yes', 'medical_history' => ['Asthma'],
            'medical_condition_remarks' => 'Hypertension', 'med_cert_findings' => 'With Findings',
            'med_assessment_remarks' => 'No subjective complaints. PE: unremarkable. Asthma. Hypertension MC issued dr. Arellano',
            'xray_findings' => 'With Findings', 'xray_findings_details' => 'CXR: abnormal opacity',
        ]));
    }

    public function test_unknown_missing_details_and_normal_results_are_not_confused(): void
    {
        $this->assertSame('Not specified', $this->text([]));
        $this->assertSame('With findings (details not specified)', $this->text(['has_illness' => 'No', 'med_cert_findings' => 'With Findings', 'assessment_remarks' => 'PE: unremarkable. MC issued. Dr. Arellano']));
        $this->assertSame('No condition or abnormal findings reported.', $this->text(['med_cert_findings' => 'No Findings / Normal']));
        $this->assertSame('Reported illness (not specified)', $this->text(['has_illness' => 'Yes']));
    }

    public function test_disability_allergies_and_negative_findings_are_preserved_when_relevant(): void
    {
        $text = $this->text(['has_disability' => 'Yes', 'disability_type' => 'Hearing impairment', 'food_allergies' => 'Peanuts',
            'medicine_allergies' => ['Penicillin'], 'medical_condition_remarks' => 'Asthma without acute exacerbation']);
        $this->assertStringContainsString('Disability: Hearing impairment', $text);
        $this->assertStringContainsString('Food allergy: Peanuts', $text);
        $this->assertStringContainsString('Medicine allergy: Penicillin', $text);
        $this->assertStringContainsString('Asthma without acute exacerbation', $text);
    }

    public function test_actual_pdf_export_uses_cleaned_findings_and_the_new_column_heading(): void
    {
        $profile = new HealthProfile(['has_illness' => 'Yes', 'medical_history' => ['Asthma'],
            'med_cert_findings' => 'With Findings', 'medical_condition_remarks' => 'Hypertension',
            'med_assessment_remarks' => 'Not Yet Conducted | No subjective complaints. PE: unremarkable. MC issued. Dr. Arellano',
            'home_address' => 'Sample Address', 'guardian_name' => 'Sample Guardian']);
        $profile->setRelation('user', new \App\Models\User(['name' => 'Sample Patient', 'first_name' => 'Sample', 'last_name' => 'Patient']));
        $pdf = app('dompdf.wrapper');
        \Barryvdh\DomPDF\Facade\Pdf::shouldReceive('loadView')->once()->andReturnUsing(function ($view, $data) use ($pdf) {
            $this->assertSame('Asthma; Hypertension', $data['rows'][0]['illness']);
            $html = view($view, $data)->render();
            $this->assertStringContainsString('Medical Condition / Findings', $html);
            $this->assertStringNotContainsString('Not Yet Conducted', $html);
            $this->assertStringNotContainsString('MC issued', $html);
            $this->assertStringNotContainsString('Health Forms Report |', $html);
            $this->assertStringNotContainsString('report-period', $html);
            $this->assertStringContainsString('font-size: 11px;', $html);
            return $pdf->loadHTML($html);
        });
        $response = (new \ReflectionMethod(\App\Http\Controllers\ReportsController::class, 'streamHealthFormsPdf'))->invoke(
            app(\App\Http\Controllers\ReportsController::class), collect([$profile]), \Carbon\Carbon::parse('2026-11-01'), \Carbon\Carbon::parse('2026-11-12'), '');
        $this->assertStringStartsWith('%PDF-', $response->getContent());
        $this->assertSame(1, $pdf->getDomPDF()->getCanvas()->get_page_count());
    }
}
