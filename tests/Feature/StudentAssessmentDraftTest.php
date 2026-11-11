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

class StudentAssessmentDraftTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        config()->set('database.default', 'sqlite');
        config()->set('database.connections.sqlite.database', ':memory:');
        config()->set('health_data_encryption.enabled', false);
        DB::purge('sqlite');
        Schema::create('users', function (Blueprint $table) {
            $table->id();
            foreach (['name', 'first_name', 'middle_name', 'last_name', 'email', 'user_type', 'user_role', 'idp_role', 'student_number'] as $column) {
                $table->string($column)->nullable();
            }
            $table->timestamps();
        });
        Schema::create('health_profiles', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id')->nullable();
            foreach (['reference_number', 'student_number', 'student_id', 'health_form_category', 'clearance_status'] as $column) {
                $table->string($column)->nullable();
            }
            $table->json('final_review_draft_data')->nullable();
            $table->timestamps();
        });
        Schema::create('health_profile_emp', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id')->nullable();
            $table->json('draft_data')->nullable();
            $table->softDeletes();
            $table->timestamps();
        });
    }

    public function test_all_student_categories_save_without_changing_approval_status_and_resume_from_clinic_drafts(): void
    {
        foreach (['Student', 'OJT', 'Ladderized', 'Annual / School Year Update'] as $index => $category) {
            $identifier = '2026-0000' . $index . '-TG-2';
            $user = User::forceCreate(['name' => 'Student ' . $index, 'user_type' => 'Student', 'student_number' => $identifier]);
            $profile = HealthProfile::forceCreate([
                'user_id' => $user->id, 'student_number' => $identifier,
                'health_form_category' => $category, 'clearance_status' => 'Fully Cleared',
            ]);
            $request = Request::create('/admin/walkin/student-assessment-draft', 'POST', [
                'reference_number' => $identifier, 'lookup_scope' => 'student_local',
                'applicant_condition_remarks' => 'Unfinished review', 'applicant_clearance_decision' => 'pending',
            ]);
            $response = app(WalkInController::class)->saveStudentAssessmentDraft($request);
            $this->assertSame(200, $response->getStatusCode());
            $profile->refresh();
            $this->assertSame('Fully Cleared', $profile->clearance_status);
            $this->assertSame('student_local', $profile->final_review_draft_data['lookup_scope']);
            $this->assertSame('Unfinished review', $profile->final_review_draft_data['applicant_condition_remarks']);
        }

        $applicant = User::forceCreate(['name' => 'Applicant', 'user_type' => 'Applicant']);
        HealthProfile::forceCreate([
            'user_id' => $applicant->id, 'reference_number' => 'APPLICANT-001', 'clearance_status' => 'For Final Review',
            'final_review_draft_data' => ['applicant_condition_remarks' => 'Applicant draft'],
        ]);
        $list = app(WalkInController::class)->employeeDrafts(Request::create('/admin/walkin/employee-drafts'))->getData(true);
        $this->assertSame(4, $list['total']);
        $this->assertSame(['Student'], array_values(array_unique(array_column($list['drafts'], 'record_type'))));
        $this->assertNotContains('APPLICANT-001', array_column($list['drafts'], 'employee_number'));
    }

    public function test_applicants_cannot_save_through_the_student_draft_endpoint(): void
    {
        $user = User::forceCreate(['name' => 'Applicant', 'user_type' => 'Applicant']);
        $profile = HealthProfile::forceCreate([
            'user_id' => $user->id, 'reference_number' => 'APPLICANT-001', 'clearance_status' => 'For Final Review',
        ]);
        $response = app(WalkInController::class)->saveStudentAssessmentDraft(Request::create('/', 'POST', [
            'reference_number' => 'APPLICANT-001', 'lookup_scope' => 'student_local',
        ]));
        $this->assertSame(404, $response->getStatusCode());
        $this->assertNull($profile->fresh()->final_review_draft_data);
    }
}
