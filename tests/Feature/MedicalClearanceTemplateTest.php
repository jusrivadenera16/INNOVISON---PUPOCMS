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
        $this->assertStringContainsString('Control No.', $html);
        $this->assertStringNotContainsString('MC-', $html);
        $this->assertStringContainsString('<span class="physician-suffix">M.D.</span>', $html);
        $this->assertStringContainsString('<span class="license-label">Lic No.</span>', $html);
    }

    public function test_existing_control_number_is_preserved_when_provided(): void
    {
        $html = view('admin.templates.medical-clearance', ['templatePreview' => false,
            'clearance' => ['control_number' => 'MC-2026-000087']])->render();
        $this->assertStringContainsString('Control No.', $html);
        $this->assertStringContainsString('MC-2026-000087', $html);
    }

    public function test_blank_and_long_value_templates_fit_on_one_letter_page(): void
    {
        foreach ([[], ['name' => 'SAMPLE PATIENT WITH A LONG COMPOUND FIRST NAME AND SURNAME', 'age' => '22', 'sex' => 'F', 'purpose' => 'Practice Teaching / Internship', 'physician_name' => 'Sample Physician', 'license_number' => 'SAMPLE ONLY']] as $clearance) {
            $pdf = Pdf::loadView('admin.templates.medical-clearance', compact('clearance'))->setPaper('letter');
            $this->assertStringStartsWith('%PDF-', $pdf->output());
            $this->assertSame(1, $pdf->getDomPDF()->getCanvas()->get_page_count());
        }
    }

    public function test_certificate_and_signature_fit_above_the_letter_page_midpoint(): void
    {
        foreach ([[], ['name' => 'Juan Dela Cruz', 'age' => 22, 'sex' => 'M', 'purpose' => 'Practice Teaching / Internship'],
            ['name' => str_repeat('Sample Patient ', 8), 'age' => 22, 'sex' => 'F', 'purpose' => str_repeat('School requirements ', 9)]] as $clearance) {
            $bottom = null;
            $pdf = Pdf::loadView('admin.templates.medical-clearance', ['clearance' => $clearance, 'templatePreview' => false])->setPaper('letter');
            $pdf->getDomPDF()->setCallbacks([['event' => 'end_frame', 'f' => function ($frame) use (&$bottom) {
                if ($frame->get_node()->nodeName === 'table') {
                    $bottom = $frame->get_position()['y'] + $frame->get_margin_height();
                }
            }]]);
            $pdf->output();
            $this->assertNotNull($bottom);
            $this->assertLessThan(396, $bottom, 'Keep the signature above the 5.5-inch midpoint without clipping content.');
            $this->assertSame(1, $pdf->getDomPDF()->getCanvas()->get_page_count());
        }
    }
}
