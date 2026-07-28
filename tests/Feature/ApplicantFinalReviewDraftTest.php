<?php

namespace Tests\Feature;

use App\Http\Controllers\WalkInController;
use App\Models\HealthProfile;
use App\Models\User;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class ApplicantFinalReviewDraftTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        config()->set('database.default', 'sqlite');
        config()->set('database.connections.sqlite', [
            'driver' => 'sqlite',
            'database' => ':memory:',
            'prefix' => '',
            'foreign_key_constraints' => false,
        ]);
        DB::purge('sqlite');
        DB::setDefaultConnection('sqlite');

        Schema::create('users', function (Blueprint $table) {
            $table->id();
            $table->string('name')->nullable();
            $table->string('first_name')->nullable();
            $table->string('middle_name')->nullable();
            $table->string('last_name')->nullable();
            $table->string('email')->nullable();
            $table->timestamps();
        });

        Schema::create('health_profiles', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id')->nullable();
            $table->string('student_id')->nullable();
            $table->string('student_number')->nullable();
            $table->string('reference_number')->nullable();
            $table->string('clearance_status')->nullable();
            $table->timestamps();
        });

        $migration = require database_path(
            'migrations/2026_07_28_000000_add_final_review_draft_data_to_health_profiles.php'
        );
        $migration->up();
    }

    public function test_it_saves_an_applicant_final_review_draft_without_changing_queue_status(): void
    {
        $user = User::forceCreate(['name' => 'Draft Applicant']);
        $profile = HealthProfile::forceCreate([
            'user_id' => $user->id,
            'reference_number' => '2026-0000-1001',
            'clearance_status' => 'For Final Review',
        ]);

        $request = Request::create('/admin/walkin/applicant-final-review-draft', 'POST', [
            'reference_number' => '2026-0000-1001',
            'lookup_scope' => 'final_review',
            'applicant_findings_status' => 'With Findings',
            'applicant_clearance_decision' => 'pending',
            'applicant_condition_remarks' => 'Return for physician review.',
            'resubmission_required_documents' => ['medical_certificate'],
        ]);

        $response = app(WalkInController::class)->saveApplicantFinalReviewDraft($request);

        $this->assertSame(200, $response->getStatusCode());
        $profile->refresh();
        $this->assertSame('For Final Review', $profile->clearance_status);
        $this->assertSame('With Findings', $profile->final_review_draft_data['applicant_findings_status']);
        $this->assertSame('pending', $profile->final_review_draft_data['applicant_clearance_decision']);
        $this->assertSame(
            ['medical_certificate'],
            $profile->final_review_draft_data['resubmission_required_documents']
        );
    }
}
