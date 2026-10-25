<?php

namespace Tests\Feature;

use App\Http\Controllers\AdminController;
use App\Models\ActivityLog;
use App\Models\EmployeeHealthProfile;
use App\Models\HealthFormSubmission;
use App\Models\HealthProfile;
use App\Models\MarClearanceSubcategorySource;
use App\Models\StudentMedicalAssessment;
use App\Models\User;
use App\Services\HealthFormPdfSnapshotService;
use App\Services\MarClearanceIssuanceService;
use App\Services\StudentNotificationMailer;
use App\Http\Controllers\WalkInController;
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
            $table->string('student_type')->nullable();
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
            $table->boolean('documents_valid')->default(false);
            $table->timestamp('clearance_revoked_at')->nullable();
            $table->text('pending_reason')->nullable();
            $table->text('medical_condition_remarks')->nullable();
            $table->json('resubmission_required_documents')->nullable();
            $table->timestamp('resubmission_requested_at')->nullable();
            $table->timestamp('pending_compliance_reminder_sent_at')->nullable();
            $table->unsignedInteger('pending_compliance_reminder_count')->default(0);
            $table->timestamp('resubmitted_at')->nullable();
            $table->timestamp('verified_at')->nullable();
            $table->unsignedBigInteger('approved_by_user_id')->nullable();
            $table->string('puptas_sync_status')->nullable();
            $table->timestamp('puptas_synced_at')->nullable();
            $table->text('puptas_sync_message')->nullable();
            $table->string('pullout_status')->nullable();
            $table->string('has_illness')->nullable();
            $table->json('medical_history')->nullable();
            $table->text('other_illness')->nullable();
            $table->string('blood_type')->nullable();
            $table->string('has_disability')->nullable();
            $table->string('disability_type')->nullable();
            $table->boolean('no_allergies')->nullable();
            $table->text('food_allergies')->nullable();
            $table->json('medicine_allergies')->nullable();
            $table->text('other_med_allergies')->nullable();
            $table->string('is_smoker')->nullable();
            $table->string('is_drinker')->nullable();
            $table->string('covid_vaccinated')->nullable();
            $table->json('vaccine_history')->nullable();
            $table->string('covid_positive')->nullable();
            $table->date('covid_positive_date')->nullable();
            $table->string('height')->nullable();
            $table->decimal('weight', 8, 2)->nullable();
            $table->string('blood_pressure')->nullable();
            $table->unsignedInteger('pulse_rate')->nullable();
            $table->unsignedInteger('respiratory_rate')->nullable();
            $table->decimal('temperature', 5, 2)->nullable();
            $table->date('assessment_date')->nullable();
            $table->text('med_assessment_remarks')->nullable();
            $table->text('medical_certificate')->nullable();
            $table->string('doctor_name')->nullable();
            $table->string('medical_certificate_issued_by')->nullable();
            $table->date('med_cert_date')->nullable();
            $table->date('medical_certificate_issued_at')->nullable();
            $table->string('med_cert_findings')->nullable();
            $table->text('med_cert_findings_details')->nullable();
            $table->text('chest_xray_result')->nullable();
            $table->text('chest_xray_result_text')->nullable();
            $table->date('chest_xray_date')->nullable();
            $table->date('xray_date')->nullable();
            $table->string('xray_findings')->nullable();
            $table->text('xray_findings_details')->nullable();
            $table->string('medical_assessment_upload')->nullable();
            $table->text('referral_details')->nullable();
            $table->text('encode_remarks')->nullable();
            $table->timestamps();
        });

        Schema::create('health_profile_emp', function (Blueprint $table): void {
            $table->id();
            $table->unsignedBigInteger('user_id')->nullable();
            $table->string('employee_number')->nullable();
            $table->json('past_medical_history')->nullable();
            $table->text('past_medical_history_others')->nullable();
            $table->json('family_history')->nullable();
            $table->text('family_history_others')->nullable();
            $table->boolean('has_disability')->nullable();
            $table->string('disability_type')->nullable();
            $table->boolean('previous_hospitalization')->nullable();
            $table->text('previous_hospitalization_details')->nullable();
            $table->boolean('operation_surgery')->nullable();
            $table->text('operation_surgery_details')->nullable();
            $table->text('current_medications')->nullable();
            $table->text('allergies')->nullable();
            $table->boolean('cigarette_smoking')->nullable();
            $table->boolean('alcohol_drinking')->nullable();
            $table->boolean('traveled_abroad')->nullable();
            $table->string('vital_signs_distress_status')->nullable();
            $table->string('height')->nullable();
            $table->decimal('weight', 8, 2)->nullable();
            $table->decimal('bmi', 8, 2)->nullable();
            $table->string('bp')->nullable();
            $table->unsignedInteger('hr')->nullable();
            $table->unsignedInteger('rr')->nullable();
            $table->decimal('temperature', 5, 2)->nullable();
            $table->json('head_findings')->nullable();
            $table->json('eyes_findings')->nullable();
            $table->json('ears_findings')->nullable();
            $table->json('throat_findings')->nullable();
            $table->json('chest_lungs_findings')->nullable();
            $table->text('chest_xray_result')->nullable();
            $table->text('breast_findings')->nullable();
            $table->string('heart_murmur')->nullable();
            $table->string('heart_rhythm')->nullable();
            $table->text('abdomen_findings')->nullable();
            $table->date('genito_urinary_date_lmp')->nullable();
            $table->text('extremities_findings')->nullable();
            $table->text('vertebral_column_findings')->nullable();
            $table->json('skin_findings')->nullable();
            $table->text('scars_findings')->nullable();
            $table->text('working_impression')->nullable();
            $table->string('fit_status')->nullable();
            $table->text('for_work_up')->nullable();
            $table->json('referred_to')->nullable();
            $table->text('referred_to_others')->nullable();
            $table->date('follow_up_on')->nullable();
            $table->string('medical_certificate')->nullable();
            $table->string('chest_xray_document')->nullable();
            $table->string('clearance_status')->nullable();
            $table->softDeletes();
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

        Schema::create('health_profile_correction_requests', function (Blueprint $table): void {
            $table->id();
            $table->unsignedBigInteger('user_id')->nullable();
            $table->unsignedBigInteger('health_profile_id')->nullable();
            $table->string('status')->nullable();
            $table->timestamp('reviewed_at')->nullable();
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

        $employeeAssessmentMigration = require database_path(
            'migrations/2026_10_23_140000_expand_medical_assessments_to_employee_profiles.php'
        );
        $employeeAssessmentMigration->up();

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

    public function test_student_medical_information_updates_profile_without_changing_clearance_or_uploaded_files(): void
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
            'medical_certificate' => 'medical-certificates/original.pdf',
            'blood_type' => 'Unknown',
        ]);
        $this->actingAs($staff, 'admin');

        $response = app(AdminController::class)->updateStudentMedicalInformation(
            Request::create('/health-profile/' . $profile->id . '/medical-information', 'PUT', [
                'medical_information' => [
                    'has_illness' => 'Yes',
                    'medical_history' => '["Asthma"]',
                    'blood_type' => 'O+',
                    'medical_certificate_issued_by' => 'PUP Medical Clinic',
                    'med_cert_findings_details' => 'Cleared for regular activity.',
                    'referral_details' => '{"referred_to":"Pulmonology"}',
                    'medical_certificate' => 'medical-certificates/replaced.pdf',
                    'clearance_status' => 'Rejected',
                ],
            ]),
            $profile->id
        );

        $this->assertSame(302, $response->getStatusCode());
        $this->assertSame('Issued', $profile->fresh()->clearance_status);
        $this->assertSame('O+', $profile->fresh()->blood_type);
        $this->assertSame(['Asthma'], $profile->fresh()->medical_history);
        $this->assertSame('medical-certificates/original.pdf', $profile->fresh()->medical_certificate);
        $this->assertSame('PUP Medical Clinic', $profile->fresh()->medical_certificate_issued_by);
        $this->assertSame(['referred_to' => 'Pulmonology'], $profile->fresh()->referral_details);
        $this->assertSame(0, StudentMedicalAssessment::query()->count());
        $this->assertSame('student_medical_information_updated', ActivityLog::query()->value('event_type'));
        $this->assertSame(
            ['has_illness', 'medical_history', 'blood_type', 'medical_certificate_issued_by', 'med_cert_findings_details', 'referral_details'],
            ActivityLog::query()->first()->metadata['updated_fields']
        );
    }

    public function test_regular_student_can_be_approved_without_a_physical_assessment(): void
    {
        $student = $this->makeStudent(['student_type' => 'regular']);
        $superadmin = $this->makeStaff();
        $superadmin->user_role = User::ROLE_SUPERADMIN;
        $superadmin->save();
        $profile = $this->makeProfileFor($student, 'For Verification');
        $this->actingAs($superadmin, 'admin');

        $issuanceService = \Mockery::mock(MarClearanceIssuanceService::class);
        $issuanceService->shouldReceive('syncApprovedHealthProfile')
            ->once()
            ->with(\Mockery::type(HealthProfile::class), true);
        $this->app->instance(MarClearanceIssuanceService::class, $issuanceService);

        $mailer = \Mockery::mock(StudentNotificationMailer::class);
        $mailer->shouldReceive('sendHealthRecordNotice')
            ->once()
            ->with(\Mockery::type(User::class), 'approved', \Mockery::type('array'));
        $this->app->instance(StudentNotificationMailer::class, $mailer);

        $snapshotService = \Mockery::mock(HealthFormPdfSnapshotService::class);
        $snapshotService->shouldReceive('saveApprovedSnapshot')
            ->once()
            ->with(\Mockery::type(HealthProfile::class));
        $this->app->instance(HealthFormPdfSnapshotService::class, $snapshotService);

        $response = app(AdminController::class)->updateClearance(
            Request::create('/health-profile/' . $profile->id . '/update', 'PUT', [
                'clearance_status' => 'Fully Cleared',
                'physical_assessment_status' => 'Not Yet Conducted',
                'documents_valid' => 'on',
            ]),
            $profile->id
        );

        $this->assertSame(302, $response->getStatusCode());
        $this->assertSame('Fully Cleared', $profile->fresh()->clearance_status);
        $this->assertSame('Not Yet Conducted', $profile->fresh()->physical_assessment_status);
        $this->assertTrue((bool) $profile->fresh()->documents_valid);
        $this->assertTrue((bool) $student->fresh()->is_health_profile_completed);
    }

    public function test_ladderized_student_still_requires_a_completed_physical_assessment_for_approval(): void
    {
        $student = $this->makeStudent(['student_type' => 'ladderized']);
        $superadmin = $this->makeStaff();
        $superadmin->user_role = User::ROLE_SUPERADMIN;
        $superadmin->save();
        $profile = $this->makeProfileFor($student, 'For Verification');
        $this->actingAs($superadmin, 'admin');

        $response = app(AdminController::class)->updateClearance(
            Request::create('/health-profile/' . $profile->id . '/update', 'PUT', [
                'clearance_status' => 'Fully Cleared',
                'physical_assessment_status' => 'Not Yet Conducted',
                'documents_valid' => '1',
            ]),
            $profile->id
        );

        $this->assertSame(302, $response->getStatusCode());
        $this->assertSame('For Verification', $profile->fresh()->clearance_status);
        $this->assertNull($profile->fresh()->physical_assessment_status);
    }

    public function test_regular_student_cannot_be_moved_to_applicants_final_review(): void
    {
        $student = $this->makeStudent(['student_type' => 'regular']);
        $profile = $this->makeProfileFor($student, 'Pending/Conditional');

        $response = app(AdminController::class)->markHealthProfileForFinalReview(
            Request::create('/health-profile/' . $profile->id . '/for-final-review', 'POST'),
            $profile->id
        );

        $this->assertSame(302, $response->getStatusCode());
        $this->assertSame('Pending/Conditional', $profile->fresh()->clearance_status);
        $this->assertSame(
            route('admin.health_records', ['tab' => 'pending_compliance']),
            $response->getTargetUrl()
        );
    }

    public function test_regular_student_with_a_legacy_final_review_status_is_hidden_from_applicant_queue(): void
    {
        $student = $this->makeStudent(['student_type' => 'regular']);
        $this->makeProfileFor($student, 'For Final Review');

        $response = app(WalkInController::class)->finalReviewApplicants(
            Request::create('/walkin/final-review-applicants', 'GET')
        );

        $this->assertSame([], $response->getData(true)['applicants']);
    }

    public function test_regular_student_record_can_be_rejected_without_a_physical_assessment(): void
    {
        $student = $this->makeStudent(['student_type' => 'regular']);
        $superadmin = $this->makeStaff();
        $superadmin->user_role = User::ROLE_SUPERADMIN;
        $superadmin->save();
        $profile = $this->makeProfileFor($student, 'For Verification');
        $this->actingAs($superadmin, 'admin');

        $issuanceService = \Mockery::mock(MarClearanceIssuanceService::class);
        $issuanceService->shouldReceive('syncApprovedHealthProfile')
            ->once()
            ->with(\Mockery::type(HealthProfile::class), false);
        $this->app->instance(MarClearanceIssuanceService::class, $issuanceService);

        $response = app(AdminController::class)->updateClearance(
            Request::create('/health-profile/' . $profile->id . '/update', 'PUT', [
                'clearance_status' => 'Rejected',
                'physical_assessment_status' => 'Not Yet Conducted',
                'documents_valid' => '0',
            ]),
            $profile->id
        );

        $this->assertSame(302, $response->getStatusCode());
        $this->assertSame('Rejected', $profile->fresh()->clearance_status);
        $this->assertFalse((bool) $student->fresh()->is_health_profile_completed);
    }

    public function test_student_medical_information_rejects_invalid_json_values(): void
    {
        $student = $this->makeStudent();
        $staff = $this->makeStaff();
        $profile = $this->makeProfileFor($student, 'Issued');
        $this->actingAs($staff, 'admin');

        try {
            app(AdminController::class)->updateStudentMedicalInformation(
                Request::create('/health-profile/' . $profile->id . '/medical-information', 'PUT', [
                    'medical_information' => ['medical_history' => 'not valid json'],
                ]),
                $profile->id
            );
            $this->fail('Malformed medical history must not be saved.');
        } catch (ValidationException $exception) {
            $this->assertArrayHasKey('medical_information.medical_history', $exception->errors());
        }

        $this->assertNull($profile->fresh()->medical_history);
        $this->assertSame(0, StudentMedicalAssessment::query()->count());
    }

    public function test_student_medical_information_requires_a_payload(): void
    {
        $student = $this->makeStudent();
        $staff = $this->makeStaff();
        $profile = $this->makeProfileFor($student, 'Issued');
        $this->actingAs($staff, 'admin');

        try {
            app(AdminController::class)->updateStudentMedicalInformation(
                Request::create('/health-profile/' . $profile->id . '/medical-information', 'PUT'),
                $profile->id
            );
            $this->fail('A medical information payload is required.');
        } catch (ValidationException $exception) {
            $this->assertArrayHasKey('medical_information', $exception->errors());
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

    public function test_applicant_can_update_medical_information_but_employee_cannot_use_student_update(): void
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

        try {
            app(AdminController::class)->revokeHealthProfileClearance(
                Request::create('/health-profile/' . $applicantProfile->id . '/revoke-clearance', 'POST', [
                    'revoke_reason' => 'Not a student record.',
                ]),
                $applicantProfile->id
            );
            $this->fail('Applicants must not use the student clearance-revocation action.');
        } catch (HttpException $exception) {
            $this->assertSame(404, $exception->getStatusCode());
        }

        try {
            app(AdminController::class)->updateStudentMedicalInformation(
                Request::create('/health-profile/' . $employeeProfile->id . '/medical-information', 'PUT', [
                    'medical_information' => ['other_illness' => 'Test condition'],
                ]),
                $employeeProfile->id
            );
            $this->fail('Employee health profiles must not use the student/applicant assessment action.');
        } catch (HttpException $exception) {
            $this->assertSame(404, $exception->getStatusCode());
        }

        $this->assertSame('Fully Cleared', $applicantProfile->fresh()->clearance_status);
        $this->assertSame('Issued', $employeeProfile->fresh()->clearance_status);
        $this->assertSame(0, StudentMedicalAssessment::query()->count());
    }

    public function test_applicant_medical_information_is_saved_to_the_applicant_health_profile(): void
    {
        $staff = $this->makeStaff();
        $this->actingAs($staff, 'admin');
        $applicant = $this->makeStudent([
            'user_type' => 'Applicant',
            'idp_role' => 'applicant',
            'student_number' => null,
            'reference_number' => '2026-0123-4567',
        ]);
        $profile = $this->makeProfileFor($applicant, 'For Verification');

        $response = app(AdminController::class)->updateStudentMedicalInformation(
            Request::create('/health-profile/' . $profile->id . '/medical-information', 'PUT', [
                'medical_information' => [
                    'medical_condition_remarks' => 'Applicant medical record reviewed.',
                ],
            ]),
            $profile->id
        );

        $this->assertSame(302, $response->getStatusCode());
        $this->assertSame('Applicant medical record reviewed.', $profile->fresh()->medical_condition_remarks);
        $this->assertSame('applicant_medical_information_updated', ActivityLog::query()->value('event_type'));
    }

    public function test_faculty_and_admin_medical_information_updates_employee_profiles(): void
    {
        $staff = $this->makeStaff();
        $this->actingAs($staff, 'admin');
        foreach ([
            ['Faculty', 'faculty', 'faculty_medical_information_updated'],
            ['Admin', 'admin', 'admin_medical_information_updated'],
        ] as [$type, $role, $eventType]) {
            $employee = $this->makeStudent([
                'name' => $type . ' Member',
                'user_type' => $type,
                'idp_role' => $role,
                'student_number' => null,
                'reference_number' => strtoupper($type) . '-1001',
            ]);
            $profile = EmployeeHealthProfile::forceCreate([
                'user_id' => $employee->id,
                'employee_number' => $employee->reference_number,
                'clearance_status' => 'Issued',
                'medical_certificate' => 'medical-certificates/employee-original.pdf',
            ]);

            $response = app(AdminController::class)->updateEmployeeMedicalInformation(
                Request::create('/employee-health-profile/' . $profile->id . '/medical-information', 'PUT', [
                    'medical_information' => [
                        'past_medical_history' => '["Asthma"]',
                        'current_medications' => 'Inhaler',
                        'has_disability' => 'No',
                        'weight' => '68.5',
                    ],
                ]),
                $profile
            );

            $profile->refresh();
            $this->assertSame(302, $response->getStatusCode());
            $this->assertSame(['Asthma'], $profile->past_medical_history);
            $this->assertSame('Inhaler', $profile->current_medications);
            $this->assertFalse($profile->has_disability);
            $this->assertSame('68.5', (string) $profile->weight);
            $this->assertSame('Issued', $profile->clearance_status);
            $this->assertSame('medical-certificates/employee-original.pdf', $profile->medical_certificate);
            $this->assertSame($eventType, ActivityLog::query()->latest('id')->value('event_type'));
        }

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
