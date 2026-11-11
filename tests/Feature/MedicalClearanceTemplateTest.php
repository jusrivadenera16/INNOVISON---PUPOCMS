<?php

namespace Tests\Feature;

use Barryvdh\DomPDF\Facade\Pdf;
use Tests\TestCase;

class MedicalClearanceTemplateTest extends TestCase
{
    public function test_blank_template_renders_without_a_patient_or_database_lookup(): void
    {
        $html = view('admin.templates.medical-clearance')->render();
        $this->assertStringContainsString('MEDICAL CLEARANCE', $html);
        $this->assertStringContainsString('TEMPLATE PREVIEW ONLY', $html);
        $this->assertStringContainsString('data:image/jpeg;base64,', $html);
        $this->assertStringNotContainsString('<button', $html);
        $this->assertStringNotContainsString('<form', $html);
    }

    public function test_optional_values_are_escaped_and_do_not_invent_a_signature_or_date(): void
    {
        $html = view('admin.templates.medical-clearance', ['clearance' => [
            'name' => '<script>alert(1)</script>', 'purpose' => 'Practice Teaching / Internship',
        ]])->render();
        $this->assertStringContainsString('&lt;script&gt;', $html);
        $this->assertStringNotContainsString('<script>', $html);
        $this->assertStringContainsString('Practice Teaching / Internship', $html);
        $this->assertStringNotContainsString(now()->format('M d, Y'), $html);
    }

    public function test_issued_patient_line_formats_name_age_and_sex_without_student_number(): void
    {
        $html = view('admin.templates.medical-clearance', ['templatePreview' => false, 'clearance' => [
            'name' => 'Juan Dela Cruz', 'age' => 22, 'sex' => 'M', 'purpose' => 'On the Job Training',
        ]])->render();
        $this->assertStringContainsString('Juan Dela Cruz&nbsp;&nbsp; 22 / M', $html);
        $this->assertStringNotContainsString('TEMPLATE PREVIEW ONLY', $html);
        $this->assertStringNotContainsString('Student No.', $html);
    }

    public function test_blank_and_long_value_templates_fit_on_one_letter_page(): void
    {
        foreach ([[], ['name' => 'SAMPLE PATIENT WITH A LONG COMPOUND FIRST NAME AND SURNAME', 'age' => '22', 'sex' => 'F', 'purpose' => 'Practice Teaching / Internship', 'physician_name' => 'Sample Physician', 'license_number' => 'SAMPLE ONLY']] as $clearance) {
            $pdf = Pdf::loadView('admin.templates.medical-clearance', compact('clearance'))->setPaper('letter');
            $this->assertStringStartsWith('%PDF-', $pdf->output());
            $this->assertSame(1, $pdf->getDomPDF()->getCanvas()->get_page_count());
        }
    }
}
