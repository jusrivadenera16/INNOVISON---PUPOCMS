<?php

namespace Tests\Feature;

use App\Http\Controllers\ReportsController;
use App\Models\{HealthProfile, User};
use Carbon\Carbon;
use Tests\TestCase;

class HealthFormsExcelExportTest extends TestCase
{
    private function invoke(string $method, ...$arguments)
    {
        return (new \ReflectionMethod(ReportsController::class, $method))->invoke(app(ReportsController::class), ...$arguments);
    }

    public function test_formatted_xlsx_keeps_identifiers_as_text_and_numbers_and_dates_typed(): void
    {
        $xml = $this->invoke('xlsxWorksheetXml', ['Reference Number', 'Full Name', 'Course', 'Gender', 'Status', 'Medical Condition', 'BMI', 'BMI Category', 'Medical Condition / Findings', 'Approval Date and Time'],
            [['2023-00001-TG-0', '=Not a formula', 'Sample Course', 'Female', 'Approved', 'Yes', 23.4, 'Normal', 'Asthma; Hypertension', Carbon::parse('2026-11-12 00:16:00')]]);
        $document = new \SimpleXMLElement($xml);
        $document->registerXPathNamespace('s', 'http://schemas.openxmlformats.org/spreadsheetml/2006/main');
        $this->assertCount(10, $document->xpath('//s:cols/s:col'));
        $this->assertStringContainsString('xSplit="2" ySplit="1"', $xml);
        $this->assertStringContainsString('autoFilter ref="A1:J2"', $xml);
        $this->assertStringContainsString('r="G2" s="2" t="n"><v>23.4</v>', $xml);
        $this->assertStringContainsString('r="J2" s="3" t="n"', $xml);
        $this->assertStringContainsString('r="A2" t="inlineStr"', $xml);
        $this->assertStringContainsString('=Not a formula', $xml);
        $this->assertStringNotContainsString('<f>', $xml);
        $serial = (float) $document->xpath('//s:c[@r="J2"]/s:v')[0];
        $this->assertEqualsWithDelta((new \DateTimeImmutable('2026-11-12 00:16:00', new \DateTimeZone('UTC')))->getTimestamp() / 86400 + 25569, $serial, 0.000001);
    }

    public function test_normal_excel_export_includes_students_not_just_applicants_and_downloads_xlsx(): void
    {
        $profile = new HealthProfile(['student_number' => '2023-00001-TG-0', 'clearance_status' => 'Fully Cleared', 'height' => 160, 'weight' => 60,
            'has_illness' => 'Yes', 'medical_history' => ['Asthma'], 'verified_at' => '2026-11-12 00:16:00']);
        $profile->setRelation('user', new User(['name' => 'Sample Student', 'first_name' => 'Sample', 'last_name' => 'Student', 'user_type' => 'Student']));
        $response = $this->invoke('streamHealthFormsXlsx', collect([$profile]), Carbon::parse('2026-11-01'), Carbon::parse('2026-11-12'));
        $bytes = $response->getContent();
        $this->assertSame('application/vnd.openxmlformats-officedocument.spreadsheetml.sheet', $response->headers->get('Content-Type'));
        $this->assertStringContainsString('.xlsx', $response->headers->get('Content-Disposition'));
        $this->assertStringStartsWith("PK\x03\x04", $bytes);
        $this->assertStringContainsString('STUDENT, Sample', $bytes);
        $this->assertStringContainsString('Asthma', $bytes);
        $this->assertStringContainsString('width="55"', $bytes);
        $this->assertStringContainsString('formatCode="0.0"', $bytes);
        $this->assertStringContainsString('FF70131B', $bytes);
    }

    public function test_existing_course_sheet_names_and_separate_tabs_are_preserved(): void
    {
        $bytes = $this->invoke('healthFormsCourseSheetsXlsx', collect(['Course A' => collect(), 'Course B' => collect()]), ['Reference Number']);
        $this->assertStringContainsString('name="Course A"', $bytes);
        $this->assertStringContainsString('name="Course B"', $bytes);
        $this->assertStringContainsString('xl/worksheets/sheet2.xml', $bytes);
    }
}
