<?php

namespace Tests\Unit;

use App\Models\MarClearanceIssuance;
use App\Models\MarClearanceSubcategory;
use App\Models\MarClearanceType;
use App\Services\MarClearanceIssuanceService;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class MarClearanceExcusedLetterBreakdownTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        config([
            'database.default' => 'sqlite',
            'database.connections.sqlite.database' => ':memory:',
        ]);
        DB::purge('sqlite');

        Schema::create('consultations', function (Blueprint $table): void {
            $table->id();
            $table->string('certificate_details')->nullable();
            $table->softDeletes();
        });

        DB::table('consultations')->insert([
            ['id' => 1, 'certificate_details' => 'Schedule for check up'],
            ['id' => 2, 'certificate_details' => 'schedule   for check up'],
            ['id' => 3, 'certificate_details' => 'MC Issued for senior'],
            ['id' => 4, 'certificate_details' => 'Do not include this'],
        ]);
    }

    public function test_excused_letter_details_are_grouped_and_counted_by_patient_type(): void
    {
        $excusedLetter = new MarClearanceSubcategory(['name' => 'Excused Letter']);
        $otherClearance = new MarClearanceSubcategory(['name' => 'Medical Certificate']);
        $issuances = collect([
            $this->consultationIssuance(1, 'Regular Student', $excusedLetter),
            $this->consultationIssuance(2, 'Faculty', $excusedLetter),
            $this->consultationIssuance(3, 'Admin', $excusedLetter),
            $this->consultationIssuance(4, 'Student', $otherClearance),
            $this->consultationIssuance(5, 'Student', $excusedLetter, 'student_nurse_review'),
        ]);

        $breakdown = (new MarClearanceIssuanceService())->excusedLetterBreakdown($issuances);

        $this->assertSame([
            [
                'name' => 'MC Issued for senior',
                'student' => 0,
                'faculty' => 0,
                'admin' => 1,
                'dependent' => 0,
            ],
            [
                'name' => 'Schedule for check up',
                'student' => 1,
                'faculty' => 1,
                'admin' => 0,
                'dependent' => 0,
            ],
        ], $breakdown['subcategory:9']);
    }

    private function consultationIssuance(
        int $consultationId,
        string $patientType,
        MarClearanceSubcategory $subcategory,
        string $workflow = 'consultation'
    ): MarClearanceIssuance {
        $issuance = new MarClearanceIssuance([
            'source_workflow' => $workflow,
            'source_record_id' => (string) $consultationId,
            'clearance_type_id' => 2,
            'clearance_subcategory_id' => 9,
            'user_type' => $patientType,
        ]);
        $issuance->setRelation('subcategory', $subcategory);
        $issuance->setRelation('clearanceType', new MarClearanceType(['name' => 'Medical Certificate / Clearance']));

        return $issuance;
    }
}
