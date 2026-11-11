<?php

namespace Tests\Feature;

use App\Http\Controllers\WalkInController;
use App\Models\HealthProfile;
use App\Models\HealthFormSubmission;
use App\Models\EmployeeHealthProfile;
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
            foreach (['name', 'first_name', 'middle_name', 'last_name', 'email', 'user_type', 'user_role', 'idp_role', 'student_number', 'student_type'] as $column) {
                $table->string($column)->nullable();
            }
            $table->timestamps();
        });
        Schema::create('health_profiles', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id')->nullable();
            foreach (['reference_number', 'student_number', 'student_id', 'health_form_category', 'clearance_status', 'student_photo'] as $column) {
                $table->string($column)->nullable();
            }
            $table->json('final_review_draft_data')->nullable();
            $table->timestamps();
        });
        Schema::create('health_profile_emp', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id')->nullable();
            $table->json('draft_data')->nullable();
            foreach (['employee_number', 'name', 'health_form_category', 'clearance_status', 'student_photo'] as $column) $table->string($column)->nullable();
            $table->softDeletes();
            $table->timestamps();
        });
        Schema::create('health_form_submissions', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id')->nullable();
            $table->unsignedBigInteger('health_profile_id')->nullable();
            $table->string('category')->nullable();
            $table->string('status');
            $table->json('profile_snapshot')->nullable();
            $table->timestamp('submitted_at')->nullable();
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

    public function test_student_draft_labels_follow_the_current_form_and_photo_urls_are_not_empty(): void
    {
        foreach (['OJT' => 'On the Job Training', 'Ladderized Program' => 'Ladderized', 'Regular' => 'Regular'] as $category => $label) {
            $user = User::forceCreate(['name' => $category . ' Student', 'user_type' => 'Student', 'student_type' => 'regular']);
            $profile = HealthProfile::forceCreate([
                'user_id' => $user->id, 'student_number' => 'STUDENT-' . $user->id,
                'health_form_category' => 'Student', 'student_photo' => 'health_profiles/photos/' . $user->id . '.jpg',
                'final_review_draft_data' => ['lookup_scope' => 'student_local'],
            ]);
            HealthFormSubmission::create([
                'user_id' => $user->id, 'health_profile_id' => $profile->id,
                'category' => $category, 'status' => 'submitted', 'submitted_at' => now(),
            ]);
            // A cancelled newer form must not override the active form's purpose.
            HealthFormSubmission::create([
                'user_id' => $user->id, 'health_profile_id' => $profile->id,
                'category' => 'Wrong purpose', 'status' => 'cancelled', 'submitted_at' => now()->addMinute(),
            ]);
            $items = app(WalkInController::class)->employeeDrafts(Request::create('/admin/walkin/employee-drafts'))->getData(true)['drafts'];
            $item = collect($items)->firstWhere('id', $profile->id);
            $this->assertSame($label, $item['health_form_category']);
            $this->assertSame(route('walkin.student-draft-photo', $profile), $item['photo_url']);
        }
    }

    public function test_generic_student_category_falls_back_to_account_type_and_snapshot_photo(): void
    {
        $user = User::forceCreate(['name' => 'OJT Student', 'user_type' => 'Student', 'student_type' => 'ojt']);
        $profile = HealthProfile::forceCreate([
            'user_id' => $user->id, 'health_form_category' => 'Student', 'student_number' => '2026-00001-TG-1',
            'final_review_draft_data' => ['lookup_scope' => 'student_local'],
        ]);
        HealthFormSubmission::create([
            'user_id' => $user->id, 'health_profile_id' => $profile->id,
            'category' => 'Student', 'status' => 'submitted', 'submitted_at' => now(),
            'profile_snapshot' => ['profile' => ['student_photo' => 'health_profiles/photos/snapshot.jpg']],
        ]);
        $item = app(WalkInController::class)->employeeDrafts(Request::create('/'))->getData(true)['drafts'][0];
        $this->assertSame('On the Job Training', $item['health_form_category']);
        $this->assertNotSame('', $item['photo_url']);
        $controller = \Mockery::mock(WalkInController::class)->makePartial();
        $controller->shouldReceive('showApplicantDocument')->once()
            ->with(\Mockery::on(fn ($photoProfile) => $photoProfile->student_photo === 'health_profiles/photos/snapshot.jpg'), 'student_photo')
            ->andReturn(response('student photo'));
        $this->assertSame('student photo', $controller->showStudentDraftPhoto($profile)->getContent());
    }

    public function test_employee_category_is_not_replaced_by_student_type_labels(): void
    {
        $user = User::forceCreate(['name' => 'Admin Employee', 'user_type' => 'Admin']);
        EmployeeHealthProfile::forceCreate([
            'user_id' => $user->id, 'name' => 'Admin Employee', 'employee_number' => '93060',
            'health_form_category' => 'Annual Medical Clearance', 'draft_data' => ['remarks' => 'Unfinished'],
        ]);
        $item = app(WalkInController::class)->employeeDrafts(Request::create('/'))->getData(true)['drafts'][0];
        $this->assertSame('Annual Medical Clearance', $item['health_form_category']);
    }

    public function test_student_photo_route_requires_the_same_permission_as_the_draft_list(): void
    {
        foreach (['walkin.student-draft-photo', 'assistant.walkin.student-draft-photo'] as $name) {
            $route = app('router')->getRoutes()->getByName($name);
            $this->assertContains('module.permission:walkin.employee_lookup', $route->gatherMiddleware());
        }
    }
}
