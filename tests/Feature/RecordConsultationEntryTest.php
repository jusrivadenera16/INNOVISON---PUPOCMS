<?php

namespace Tests\Feature;

use App\Models\ClinicServiceOption;
use App\Services\ConsultationServiceOptions;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\{DB, Schema};
use Tests\TestCase;

class RecordConsultationEntryTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        config(['database.default' => 'sqlite', 'database.connections.sqlite.database' => ':memory:']);
        DB::purge('sqlite');
        Schema::create('clinic_service_options', function (Blueprint $table) {
            $table->id();
            $table->string('option_group');
            $table->string('code');
            $table->string('name');
            $table->integer('sort_order')->default(0);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });
    }

    public function test_service_choices_include_defaults_and_only_active_configured_other_services(): void
    {
        ClinicServiceOption::create(['option_group' => 'other_service', 'code' => 'wound', 'name' => 'Wound Care', 'is_active' => true]);
        ClinicServiceOption::create(['option_group' => 'other_service', 'code' => 'old', 'name' => 'Old Service', 'is_active' => false]);
        ClinicServiceOption::create(['option_group' => 'referral', 'code' => 'ref', 'name' => 'Referral Only', 'is_active' => true]);
        $this->assertSame(['General Consultation', 'Clinic Document Services', 'BP Monitoring', 'Wound Care'], app(ConsultationServiceOptions::class)->labels());
        ClinicServiceOption::create(['option_group' => 'other_service', 'code' => 'bp', 'name' => 'BP Monitoring', 'is_active' => true]);
        $this->assertSame(1, count(array_keys(app(ConsultationServiceOptions::class)->labels(), 'BP Monitoring', true)));
    }

    public function test_selection_modal_opens_existing_patient_form_and_starts_without_a_selected_service(): void
    {
        ClinicServiceOption::create(['option_group' => 'other_service', 'code' => 'care', 'name' => 'Care <test>', 'is_active' => true]);
        $html = view('admin.partials.record-consult-modal', ['consultPatientIdentifier' => 'patient-uuid'])->render();
        $this->assertStringContainsString('action="' . route('walkin.form', ['student_id' => 'patient-uuid']) . '"', $html);
        $this->assertStringContainsString('method="GET"', $html);
        $this->assertStringContainsString('<span id="recordConsultSelected">Select a service</span>', $html);
        $this->assertStringContainsString('type="radio" name="service"', $html);
        $this->assertStringNotContainsString(' checked', $html);
        $this->assertStringNotContainsString('record-consult-head', $html);
        $this->assertStringContainsString('value="Clinic Document Services" required', $html);
        $this->assertStringContainsString('<button type="submit">Continue</button>', $html);
        $this->assertStringNotContainsString('Continue to Consultation', $html);
        $this->assertStringContainsString('Care &lt;test&gt;', $html);
    }

    public function test_both_health_record_menus_place_consult_immediately_before_history_and_gate_permissions(): void
    {
        foreach (['show_health', 'show_employee_health'] as $name) {
            $source = file_get_contents(resource_path('views/admin/' . $name . '.blade.php'));
            $this->assertMatchesRegularExpression('/@if\(\$can(?:Start|View)Consultation\).*?data-record-consult-open.*?@endif\s*<button[^>]*data-health-record-history-open/s', $source);
            $this->assertStringContainsString("canAccessPermission('walkin.encode_assessment')", $source);
        }
        $controller = file_get_contents(app_path('Http/Controllers/WalkInController.php'));
        $this->assertStringContainsString('ConsultationServiceOptions::class)->labels($otherServiceOptions)', $controller);
        $view = file_get_contents(resource_path('views/admin/consult-form.blade.php'));
        $this->assertStringContainsString('!empty($selectedConsultationService)', $view);
        $this->assertStringContainsString("(\$user_source ?? '') !== 'online'", $view);
    }

    public function test_employee_menu_shows_evaluation_but_disables_it_until_current_record_is_approved(): void
    {
        $source = file_get_contents(resource_path('views/admin/show_employee_health.blade.php'));
        $start = strpos($source, '                        @if($canViewRecordEvaluation)');
        $end = strpos($source, '                        <button type="button" data-health-record-history-open', $start);
        $fragment = substr($source, $start, $end - $start);
        $data = ['canViewRecordEvaluation' => true, 'canSendRecordEvaluation' => false,
            'canViewConsultation' => true, 'canStartConsultation' => true];
        $html = \Illuminate\Support\Facades\Blade::render($fragment, $data);
        $this->assertMatchesRegularExpression('/data-record-evaluation-open[^>]*disabled/', $html);
        $this->assertStringContainsString('Consult', $html);
        $data['canSendRecordEvaluation'] = true;
        $html = \Illuminate\Support\Facades\Blade::render($fragment, $data);
        $this->assertStringNotContainsString('disabled', $html);
        $this->assertTrue(strpos($html, 'Send Evaluation') < strpos($html, 'Consult'));
        $data['canViewRecordEvaluation'] = $data['canViewConsultation'] = false;
        $html = \Illuminate\Support\Facades\Blade::render($fragment, $data);
        $this->assertStringNotContainsString('data-record-evaluation-open', $html);
        $this->assertStringNotContainsString('data-record-consult-open', $html);
    }

    public function test_issued_employee_evaluation_button_is_enabled_without_submission_status_check(): void
    {
        $source = file_get_contents(resource_path('views/admin/show_employee_health.blade.php'));
        preg_match('/\$canSendRecordEvaluation = .*?;/s', $source, $match);
        $this->assertStringNotContainsString('submission_status', $match[0]);
        foreach (['Issued' => 'enabled', 'Fully Cleared' => 'enabled', 'For Verification' => 'disabled'] as $status => $expected) {
            $html = \Illuminate\Support\Facades\Blade::render("@php\n" . $match[0] . "\n@endphp\n" . '{{ $canSendRecordEvaluation ? "enabled" : "disabled" }}', [
                'canViewRecordEvaluation' => true, 'status' => $status,
            ]);
            $this->assertSame($expected, trim($html));
        }
    }

    public function test_service_selected_in_modal_is_locked_and_submitted_while_direct_walkin_remains_editable(): void
    {
        $source = file_get_contents(resource_path('views/admin/consult-form.blade.php'));
        $start = strpos($source, '    $consultServiceLocked =');
        $end = strpos($source, '    $hasConfiguredClinicDocumentService', $start);
        $state = substr($source, $start, $end - $start);
        $start = strpos($source, '<select id="consultService"');
        $end = strpos($source, '</div>', $start);
        $field = substr($source, $start, $end - $start);
        foreach ([['walkin', 'Clinic Document Services', true], ['walkin', '', false], ['online', '', true]] as [$origin, $service, $locked]) {
            $html = \Illuminate\Support\Facades\Blade::render("@php\n" . $state . "\n@endphp\n" . $field, [
                'user_source' => $origin, 'selectedConsultationService' => $service,
                'draftValue' => static fn ($key, $default = '') => 'BP Monitoring',
                'latestAppointment' => null, 'otherServiceOptions' => [],
                'hasConfiguredClinicDocumentService' => false, 'hasConfiguredBpMonitoring' => false,
            ]);
            preg_match('/<select[^>]*>/', $html, $select);
            if ($locked) {
                $this->assertStringContainsString('disabled', $select[0]);
                $this->assertStringNotContainsString('name="service"', $select[0]);
                $this->assertStringContainsString('<input type="hidden" name="service" value="' . ($service ?: 'BP Monitoring') . '">', $html);
            } else {
                $this->assertStringNotContainsString('disabled', $select[0]);
                $this->assertStringContainsString('name="service"', $select[0]);
                $this->assertStringNotContainsString('type="hidden" name="service"', $html);
            }
        }
    }
}
