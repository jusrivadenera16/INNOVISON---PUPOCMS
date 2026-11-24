<?php

namespace Tests\Feature;

use App\Http\Controllers\ReportsController;
use App\Models\{ClinicServiceOption, Consultation, User};
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\{DB, Schema};
use Tests\TestCase;

class MarClinicDocumentServicesTest extends TestCase
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
            $table->unsignedInteger('sort_order')->default(0);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });
    }

    public function test_clinic_document_services_has_its_own_mar_count_without_medical_configuration(): void
    {
        ClinicServiceOption::create([
            'option_group' => ClinicServiceOption::GROUP_OTHER_SERVICE,
            'code' => 'bp-monitoring',
            'name' => 'BP Monitoring',
        ]);

        $consultations = new Collection([
            $this->consultation('Clinic Document Services', 'student'),
            $this->consultation('clinic document services', 'faculty'),
            $this->consultation('BP Monitoring', 'student'),
        ]);
        $method = new \ReflectionMethod(ReportsController::class, 'buildMarServiceSummary');

        $summaryWithoutConfiguredDocumentService = $method->invoke(
            app(ReportsController::class),
            now()->startOfMonth(),
            now()->endOfMonth(),
            $consultations
        );

        $this->assertSame(['student' => 1, 'faculty' => 1, 'admin' => 0, 'dependent' => 0], $summaryWithoutConfiguredDocumentService['clinic_document_services']);
        $this->assertSame(['student' => 1, 'faculty' => 0, 'admin' => 0, 'dependent' => 0], $summaryWithoutConfiguredDocumentService['other_services']);

        ClinicServiceOption::create([
            'option_group' => ClinicServiceOption::GROUP_OTHER_SERVICE,
            'code' => 'clinic-document-services',
            'name' => 'Clinic Document Services',
        ]);
        $summaryWithConfiguredDocumentService = $method->invoke(
            app(ReportsController::class),
            now()->startOfMonth(),
            now()->endOfMonth(),
            $consultations
        );

        $this->assertSame($summaryWithoutConfiguredDocumentService['clinic_document_services'], $summaryWithConfiguredDocumentService['clinic_document_services']);
        $this->assertSame($summaryWithoutConfiguredDocumentService['other_services'], $summaryWithConfiguredDocumentService['other_services']);
    }

    private function consultation(string $service, string $patientType): Consultation
    {
        $consultation = new Consultation(['service' => $service]);
        $consultation->setRelation('user', new User(['user_type' => $patientType]));

        return $consultation;
    }
}
