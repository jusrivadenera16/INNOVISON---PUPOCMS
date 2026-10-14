<?php

namespace Tests\Feature;

use App\Http\Controllers\AdminController;
use App\Models\ActivityLog;
use App\Models\HealthFormSubmission;
use App\Models\HealthProfile;
use App\Models\MarClearanceSubcategorySource;
use App\Models\StudentMedicalAssessment;
use App\Models\User;
use App\Services\MarClearanceIssuanceService;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Illuminate\Validation\ValidationException;
use Tests\TestCase;

class StudentMedicalAssessmentWorkflowTest extends TestCase
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
        config()->set('health_data_encryption.enabled', false);
        DB::purge('sqlite');
        DB::setDefaultConnection('sqlite');

        Schema::create('users', function (Blueprint $table): void {
            $table->id();
            $table->string('name')->nullable();
            $table->string('first_name')->nullable();
            $table->string('middle_name')->nullable();
            $table->string('last_name')->nullable();
            $table->string('suffix_name')->nullable();
            $table->string('email')->nullable();
            $table->string('user_role')->nullable();
            $table->string('user_type')->nullable();
            $table->string('idp_role')->nullable();
            $table->string('student_number')->nullable();
            $table->string('reference_number')->nullable();
            $table->string('status')->default('active');
            $table->boolean('is_health_profile_completed')->default(false);
            $table->rememberToken();
            $table->timestamps();
        });

        Schema::create('health_profiles', function (Blueprint $table): void {
            $table->id();
            $table->unsignedBigInteger('user_id')->nullable();
            $table->string('student_id')->nullable();
            $table->string('student_number')->nullable();
            $table->string('reference_number')->nullable();
            $table->string('clearance_status')->nullable();
            $table->string('physical_assessment_status')->nullable();
            $table->text('pending_reason')->nullable();
            $table->text('medical_condition_remarks')->nullable();
            $table->timestamp('verified_at')->nullable();
            $table->unsignedBigInteger('approved_by_user_id')->nullable();
            $table->string('puptas_sync_status')->nullable();
            $table->timestamp('puptas_synced_at')->nullable();
            $table->text('puptas_sync_message')->nullable();
            $table->string('pullout_status')->nullable();
            $table->timestamps();
        });

        Schema::create('health_form_submissions', function (Blueprint $table): void {
            $table->id();
            $table->unsignedBigInteger('user_id')->nullable();
            $table->unsignedBigInteger('health_profile_id')->nullable();
            $table->string('status')->nullable();
            $table->timestamp('submitted_at')->nullable();
            $table->timestamp('approved_at')->nullable();
            $table->timestamps();
        });

        Schema::create('activity_logs', function (Blueprint $table): void {
            $table->id();
            $table->unsignedBigInteger('user_id')->nullable();
            $table->string('user_name')->nullable();
            $table->string('user_role')->nullable();
            $table->string('action');
            $table->string('module')->nullable();
            $table->string('event_type')->nullable();
            $table->text('description')->nullable();
            $table->string('route_name')->nullable();
            $table->string('http_method')->nullable();
            $table->text('request_path')->nullable();
            $table->unsignedSmallInteger('status_code')->nullable();
            $table->string('subject_type')->nullable();
            $table->string('subject_id')->nullable();
            $table->json('metadata')->nullable();
            $table->string('ip_address')->nullable();
            $table->text('user_agent')->nullable();
            $table->timestamps();
        });

        Schema::create('mar_clearance_issuances', function (Blueprint $table): void {
            $table->id();
            $table->unsignedBigInteger('user_id')->nullable();
            $table->string('source_workflow', 40);
            $table->string('source_record_id', 120);
        });

        $migration = require database_path(
            'migrations/2026_10_12_030000_create_student_medical_assessments_table.php'
        );
        $migration->up();

        $assessmentMigration = require database_path(
            'migrations/2026_10_12_040000_add_findings_to_student_medical_assessments_table.php'
        );
        $assessmentMigration->up();

        $nullableConditionMigration = require database_path(
            'migrations/2026_10_12_050000_make_student_medical_assessment_condition_nullable.php'
        );
        $nullableConditionMigration->up();

        Auth::shouldUse('admin');
    }

    public function test_revoke_clearance_returns_student_to_pending_and_removes_only_student_mar_source(): void
    {
        $student = $this->makeStudent();
        $staff = $this->makeStaff();
        $profile = HealthProfile::forceCreate([
            'user_id' => $student->id,
            'student_id' => $student->student_number,
            'student_number' => $student->student_number,
            'reference_number' => $student->student_number,
            'clearance_status' => 'Fully Cleared',
            'physical_assessment_status' => 'Completed / Passed',
            'verified_at' => now()->subDay(),
            'approved_by_user_id' => $staff->id,
            'puptas_sync_status' => 'synced',
            'puptas_synced_at' => now()->subDay(),
        ]);
        $submission = HealthFormSubmission::create([
            'user_id' => $student->id,
            'health_profile_id' => $profile->id,
            'status' => HealthFormSubmission::STATUS_APPROVED,
            'submitted_at' => now()->subDays(2),
            'approved_at' => now()->subDay(),
        ]);
        $this->actingAs($staff, 'admin');

        $issuanceService = \Mockery::mock(MarClearanceIssuanceService::class);
        $issuanceService->shouldReceive('syncApprovedHealthProfileForWorkflow')
            ->once()
            ->with(
                \Mockery::type(HealthProfile::class),
                MarClearanceSubcategorySource::STUDENT_NURSE_REVIEW,
                false
            );
        $this->app->instance(MarClearanceIssuanceService::class, $issuanceService);

        $response = app(AdminController::class)->revokeHealthProfileClearance(
            Request::create('/health-profile/' . $profile->id . '/revoke-clearance', 'POST', [
                'revoke_reason' => 'The student requires a fresh clinic assessment.',
            ]),
            $profile->id
        );

        $this->assertSame(302, $response->getStatusCode());
        $profile->refresh();
        $this->assertSame('For Verification', $profile->clearance_status);
        $this->assertNotNull($profile->clearance_revoked_at);
        $this->assertSame('Not Yet Conducted', $profile->physical_assessment_status);
        $this->assertSame('The student requires a fresh clinic assessment.', $profile->pending_reason);
        $this->assertNull($profile->verified_at);
        $this->assertNull($profile->approved_by_user_id);
        $this->assertSame('synced', $profile->puptas_sync_status);
        $this->assertFalse((bool) $student->fresh()->is_health_profile_completed);
        $this->assertSame(HealthFormSubmission::STATUS_SUBMITTED, $submission->fresh()->status);
        $this->assertSame('student_health_clearance_revoked', ActivityLog::query()->value('event_type'));
    }

    public function test_medical_assessment_appends_history_without_changing_clearance_or_student_form_data(): void
    {
        $student = $this->makeStudent();
        $staff = $this->makeStaff();
        $profile = HealthProfile::forceCreate([
            'user_id' => $student->id,
            'student_id' => $student->student_number,
            'student_number' => $student->student_number,
            'reference_number' => $student->student_number,
            'clearance_status' => 'Issued',
            'pending_reason' => null,
        ]);
        $this->actingAs($staff, 'admin');
        config()->set('app.key', 'base64:' . base64_encode(random_bytes(32)));
        config()->set('health_data_encryption.enabled', true);

        $response = app(AdminController::class)->storeStudentMedicalAssessment(
            Request::create('/health-profile/' . $profile->id . '/medical-assessments', 'POST', [
                'condition_or_illness' => 'Seasonal asthma',
                'findings' => 'Mild wheezing on exertion; no acute distress.',
                'remarks' => 'No acute symptoms observed today.',
                'reason' => 'Follow-up assessment after clinic visit.',
            ]),
            $profile->id
        );

        $this->assertSame(302, $response->getStatusCode());
        $this->assertSame('Issued', $profile->fresh()->clearance_status);
        $this->assertNull($profile->fresh()->medical_condition_remarks);
        $assessment = StudentMedicalAssessment::query()->sole();
        $this->assertSame($profile->id, $assessment->health_profile_id);
        $this->assertSame($staff->id, $assessment->recorded_by_user_id);
        $this->assertSame('Seasonal asthma', $assessment->condition_or_illness);
        $this->assertSame('Mild wheezing on exertion; no acute distress.', $assessment->findings);
        $this->assertSame('No acute symptoms observed today.', $assessment->remarks);
        $this->assertSame('Follow-up assessment after clinic visit.', $assessment->reason);
        $this->assertNotSame(
            'Seasonal asthma',
            DB::table('student_medical_assessments')->where('id', $assessment->id)->value('condition_or_illness_encrypted')
        );
        $this->assertNotSame(
            'Mild wheezing on exertion; no acute distress.',
            DB::table('student_medical_assessments')->where('id', $assessment->id)->value('findings_encrypted')
        );
        $this->assertSame('student_medical_assessment_added', ActivityLog::query()->value('event_type'));
    }

    public function test_medical_assessment_fields_are_optional_when_at_least_one_clinical_detail_is_provided(): void
    {
        $student = $this->makeStudent();
        $staff = $this->makeStaff();
        $profile = HealthProfile::forceCreate([
            'user_id' => $student->id,
            'student_id' => $student->student_number,
            'student_number' => $student->student_number,
            'reference_number' => $student->student_number,
            'clearance_status' => 'Issued',
            'pending_reason' => null,
        ]);
        $this->actingAs($staff, 'admin');

        $response = app(AdminController::class)->storeStudentMedicalAssessment(
            Request::create('/health-profile/' . $profile->id . '/medical-assessments', 'POST', [
                'findings' => 'No acute findings.',
            ]),
            $profile->id
        );

        $this->assertSame(302, $response->getStatusCode());
        $assessment = StudentMedicalAssessment::query()->sole();
        $this->assertNull($assessment->condition_or_illness);
        $this->assertSame('No acute findings.', $assessment->findings);
        $this->assertNull($assessment->remarks);
        $this->assertNull($assessment->reason);
    }

    public function test_medical_assessment_rejects_entry_without_any_clinical_detail(): void
    {
        $student = $this->makeStudent();
        $staff = $this->makeStaff();
        $profile = HealthProfile::forceCreate([
            'user_id' => $student->id,
            'student_id' => $student->student_number,
            'student_number' => $student->student_number,
            'reference_number' => $student->student_number,
            'clearance_status' => 'Issued',
            'pending_reason' => null,
        ]);
        $this->actingAs($staff, 'admin');

        try {
            app(AdminController::class)->storeStudentMedicalAssessment(
                Request::create('/health-profile/' . $profile->id . '/medical-assessments', 'POST'),
                $profile->id
            );
            $this->fail('A blank medical assessment update must not be saved.');
        } catch (ValidationException $exception) {
            $this->assertArrayHasKey('condition_or_illness', $exception->errors());
        }

        $this->assertSame(0, StudentMedicalAssessment::query()->count());
    }

    public function test_student_revoke_cleanup_does_not_remove_applicant_final_review_mar_entries(): void
    {
        $student = $this->makeStudent();
        $profile = $this->makeProfileFor($student, 'Fully Cleared');
        DB::table('mar_clearance_issuances')->insert([
            [
                'source_workflow' => MarClearanceSubcategorySource::STUDENT_NURSE_REVIEW,
                'source_record_id' => (string) $profile->id,
            ],
            [
                'source_workflow' => MarClearanceSubcategorySource::APPLICANT_FINAL_REVIEW,
                'source_record_id' => 'other-applicant-profile',
            ],
        ]);

        app(MarClearanceIssuanceService::class)->syncApprovedHealthProfileForWorkflow(
            $profile,
            MarClearanceSubcategorySource::STUDENT_NURSE_REVIEW,
            false
        );

        $this->assertDatabaseMissing('mar_clearance_issuances', [
            'source_workflow' => MarClearanceSubcategorySource::STUDENT_NURSE_REVIEW,
            'source_record_id' => (string) $profile->id,
        ]);
        $this->assertDatabaseHas('mar_clearance_issuances', [
            'source_workflow' => MarClearanceSubcategorySource::APPLICANT_FINAL_REVIEW,
            'source_record_id' => 'other-applicant-profile',
        ]);
    }

    public function test_applicant_and_employee_accounts_cannot_use_student_assessment_actions(): void
    {
        $staff = $this->makeStaff();
        $this->actingAs($staff, 'admin');
        $applicant = $this->makeStudent([
            'user_type' => 'Applicant',
            'idp_role' => 'applicant',
            'student_number' => null,
            'reference_number' => '2026-0123-4567',
        ]);
        $employee = $this->makeStudent([
            'user_type' => 'Faculty',
            'idp_role' => 'faculty',
            'student_number' => null,
            'reference_number' => 'EMP-1001',
        ]);
        $applicantProfile = $this->makeProfileFor($applicant, 'Fully Cleared');
        $employeeProfile = $this->makeProfileFor($employee, 'Issued');

        foreach ([[$applicantProfile, 'revoke'], [$employeeProfile, 'assessment']] as [$profile, $action]) {
            try {
                if ($action === 'revoke') {
                    app(AdminController::class)->revokeHealthProfileClearance(
                        Request::create('/health-profile/' . $profile->id . '/revoke-clearance', 'POST', [
                            'revoke_reason' => 'Not a student record.',
                        ]),
                        $profile->id
                    );
                } else {
                    app(AdminController::class)->storeStudentMedicalAssessment(
                        Request::create('/health-profile/' . $profile->id . '/medical-assessments', 'POST', [
                            'condition_or_illness' => 'Test condition',
                            'reason' => 'Test reason',
                        ]),
                        $profile->id
                    );
                }
                $this->fail('Non-student health records must be rejected.');
            } catch (HttpException $exception) {
                $this->assertSame(404, $exception->getStatusCode());
            }
        }

        $this->assertSame('Fully Cleared', $applicantProfile->fresh()->clearance_status);
        $this->assertSame('Issued', $employeeProfile->fresh()->clearance_status);
        $this->assertSame(0, StudentMedicalAssessment::query()->count());
    }

    private function makeStudent(array $overrides = []): User
    {
        return User::forceCreate(array_merge([
            'name' => 'Test Student',
            'email' => uniqid('student-', true) . '@example.test',
            'user_role' => User::ROLE_STUDENT,
            'user_type' => 'Student',
            'idp_role' => 'student',
            'student_number' => '2026-00001-TG-0',
            'status' => 'active',
            'is_health_profile_completed' => true,
        ], $overrides));
    }

    private function makeStaff(): User
    {
        return User::forceCreate([
            'name' => 'Clinic Staff',
            'email' => 'clinic-staff@example.test',
            'user_role' => User::ROLE_ADMIN,
            'user_type' => 'Admin',
            'idp_role' => 'admin',
            'status' => 'active',
        ]);
    }

    private function makeProfileFor(User $user, string $status): HealthProfile
    {
        return HealthProfile::forceCreate([
            'user_id' => $user->id,
            'student_id' => $user->student_number ?: $user->reference_number,
            'student_number' => $user->student_number,
            'reference_number' => $user->reference_number,
            'clearance_status' => $status,
        ]);
    }
}
