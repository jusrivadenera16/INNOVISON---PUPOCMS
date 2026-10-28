<?php

namespace Tests\Feature;

use App\Http\Controllers\AdminController;
use App\Http\Controllers\AppointmentController;
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
use Illuminate\Support\Facades\Crypt;
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
            $table->string('gender')->nullable();
            $table->string('course')->nullable();
            $table->string('year')->nullable();
            $table->string('section')->nullable();
            $table->string('contact_no')->nullable();
            $table->string('reference_number')->nullable();
            $table->string('status')->default('active');
            $table->boolean('is_health_profile_completed')->default(false);
            $table->rememberToken();
            $table->timestamps();
        });

        Schema::create('health_profiles', function (Blueprint $table): void {
            $table->id();
            $table->unsignedBigInteger('user_id')->nullable();
            $table->date('birthday')->nullable();
            $table->string('sex')->nullable();
            $table->string('course_college')->nullable();
            $table->string('course_code')->nullable();
            $table->string('cellphone')->nullable();
            $table->string('guardian_name')->nullable();
            $table->string('landline')->nullable();
            $table->string('home_address')->nullable();
            $table->string('street')->nullable();
            $table->string('barangay')->nullable();
            $table->string('municipality')->nullable();
            $table->string('province')->nullable();
            $table->string('zipcode')->nullable();
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
            $table->text('weight')->nullable();
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
            $table->date('birthday')->nullable();
            $table->string('sex')->nullable();
            $table->string('office')->nullable();
            $table->string('course_college')->nullable();
            $table->string('contact_no')->nullable();
            $table->string('home_address')->nullable();
            $table->string('street')->nullable();
            $table->string('barangay')->nullable();
            $table->string('municipality')->nullable();
            $table->string('province')->nullable();
            $table->string('emergency_contact_person')->nullable();
            $table->string('emergency_contact_no')->nullable();
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

    public function test_student_personal_information_saves_split_health_form_address_and_user_fields_with_exact_history(): void
    {
        $student = $this->makeStudent();
        $staff = $this->makeStaff();
        $profile = $this->makeProfileFor($student, 'Issued');
        $this->actingAs($staff, 'admin');

        $response = app(AdminController::class)->updateStudentMedicalInformation(
            Request::create('/health-profile/' . $profile->id . '/medical-information', 'PUT', [
                'medical_information' => [
                    'student_number' => '2026-00002-TG-0',
                    'sex' => 'Female',
                    'course_code' => 'BSIT',
                    'user_year' => '3',
                    'user_section' => 'B',
                    'user_contact_no' => '09123456789',
                    'guardian_name' => 'Alex Student',
                    'cellphone' => '09987654321',
                    'landline' => '02-1234567',
                    'street' => '123 Mabini St',
                    'barangay' => 'Poblacion',
                    'municipality' => 'Boliney',
                    'province' => 'Abra',
                    'zipcode' => '2815',
                ],
            ]),
            $profile->id
        );

        $this->assertSame(302, $response->getStatusCode());
        $profile->refresh();
        $student->refresh();
        $this->assertSame('123 Mabini St, Poblacion, Boliney, Abra', $profile->home_address);
        $this->assertSame('123 Mabini St', $profile->street);
        $this->assertSame('Poblacion', $profile->barangay);
        $this->assertSame('Boliney', $profile->municipality);
        $this->assertSame('Abra', $profile->province);
        $this->assertSame('2815', $profile->zipcode);
        $this->assertSame('Alex Student', $profile->guardian_name);
        $this->assertSame('02-1234567', $profile->landline);
        $this->assertSame('09987654321', $profile->cellphone);
        $this->assertSame('2026-00002-TG-0', $student->student_number);
        $this->assertSame('Female', $student->gender);
        $this->assertSame('Bachelor of Science in Information Technology', $student->course);
        $this->assertSame('BSIT', $profile->course_code);
        $this->assertSame('3', $student->year);
        $this->assertSame('B', $student->section);
        $this->assertSame('09123456789', $student->contact_no);

        $log = ActivityLog::query()->where('event_type', 'student_medical_information_updated')->firstOrFail();
        $updatedFields = $log->metadata['updated_fields'];
        $this->assertEqualsCanonicalizing([
            'student_number', 'sex', 'course_code', 'user_year', 'user_section', 'user_contact_no',
            'guardian_name', 'cellphone', 'landline', 'street', 'barangay', 'municipality', 'province', 'zipcode',
        ], $updatedFields);
        $this->assertNotContains('home_address', $updatedFields);
        $this->assertStringContainsString('House No. / Street', implode(', ', $log->metadata['updated_field_labels']));
        $this->assertStringContainsString('Emergency contact person', implode(', ', $log->metadata['updated_field_labels']));
    }

    public function test_student_medical_information_supports_dropdowns_dose_details_and_validated_vitals(): void
    {
        $student = $this->makeStudent();
        $staff = $this->makeStaff();
        $profile = $this->makeProfileFor($student, 'Issued');
        $this->actingAs($staff, 'admin');

        $response = app(AdminController::class)->updateStudentMedicalInformation(
            Request::create('/health-profile/' . $profile->id . '/medical-information', 'PUT', [
                'medical_information' => [
                    'blood_type' => 'AB+',
                    'has_disability' => 'Yes',
                    'disability_type' => 'Others',
                    'other_disability_type' => 'Cognitive disability',
                    'covid_vaccinated' => 'Yes',
                    'vaccine_history' => json_encode([
                        'first_dose' => ['brand' => 'Pfizer', 'date' => '2021-03-01'],
                        'second_dose' => ['brand' => 'Moderna', 'date' => '2021-03-15'],
                    ]),
                    'height' => '5.6',
                    'weight' => '144',
                    'blood_pressure' => '120/80',
                    'pulse_rate' => '98',
                    'respiratory_rate' => '32',
                    'temperature' => '36.5',
                ],
            ]),
            $profile->id
        );

        $this->assertSame(302, $response->getStatusCode());
        $profile->refresh();
        $this->assertSame('AB+', $profile->blood_type);
        $this->assertSame('Cognitive disability', $profile->disability_type);
        $this->assertSame('Pfizer', $profile->vaccine_history['first_dose']['brand']);
        $this->assertSame('2021-03-15', $profile->vaccine_history['second_dose']['date']);
        $this->assertSame('5.6', (string) $profile->height);
        $this->assertSame('120/80', $profile->blood_pressure);
        $this->assertSame(98, (int) $profile->pulse_rate);
        $this->assertSame('36.5', (string) $profile->temperature);

        try {
            app(AdminController::class)->updateStudentMedicalInformation(
                Request::create('/health-profile/' . $profile->id . '/medical-information', 'PUT', [
                    'medical_information' => ['blood_type' => 'Type Z'],
                ]),
                $profile->id
            );
            $this->fail('Unsupported blood types must be rejected.');
        } catch (ValidationException $exception) {
            $this->assertArrayHasKey('medical_information.blood_type', $exception->errors());
        }

        try {
            app(AdminController::class)->updateStudentMedicalInformation(
                Request::create('/health-profile/' . $profile->id . '/medical-information', 'PUT', [
                    'medical_information' => ['height' => '9'],
                ]),
                $profile->id
            );
            $this->fail('An out-of-range height must be rejected.');
        } catch (ValidationException $exception) {
            $this->assertArrayHasKey('medical_information.height', $exception->errors());
        }
    }

    public function test_student_health_form_changes_are_serialized_as_only_changed_history_fields(): void
    {
        $student = $this->makeStudent();
        $before = (new HealthProfile())->forceFill([
            'height' => '5.5',
            'weight' => 144,
        ]);
        $after = (new HealthProfile())->forceFill([
            'height' => '5.6',
            'weight' => '144',
        ]);
        $metadataMethod = new \ReflectionMethod(AppointmentController::class, 'studentHealthInformationChangeMetadata');
        $metadataMethod->setAccessible(true);
        $metadata = $metadataMethod->invoke(app(AppointmentController::class), $before, $after);

        $this->assertSame(['height'], $metadata['updated_fields']);
        $this->assertSame(['Height'], $metadata['updated_field_labels']);
        $this->assertSame(
            ['height' => '5.5'],
            json_decode(Crypt::decryptString($metadata['previous_values_encrypted']), true)
        );
        $this->assertSame(
            ['height' => '5.6'],
            json_decode(Crypt::decryptString($metadata['updated_values_encrypted']), true)
        );

        ActivityLog::create([
            'user_id' => $student->id,
            'user_name' => $student->name,
            'user_role' => 'student',
            'action' => 'Health Profile Correction Submitted',
            'module' => 'Health Records',
            'event_type' => 'student_medical_information_updated',
            'subject_type' => HealthProfile::class,
            'subject_id' => '987',
            'metadata' => $metadata,
        ]);
        $historyMethod = new \ReflectionMethod(AdminController::class, 'medicalInformationChangeHistory');
        $historyMethod->setAccessible(true);
        $history = $historyMethod->invoke(app(AdminController::class), HealthProfile::class, 987);

        $this->assertSame(['Height'], array_column($history->first()['fields'], 'label'));
        $this->assertSame('5.5 ft', $history->first()['fields'][0]['previous_value']);
        $this->assertSame('5.6 ft', $history->first()['fields'][0]['value']);
        $this->assertSame('Test Student (Student)', $history->first()['fields'][0]['account']);
    }

    public function test_known_allergies_update_maps_to_the_inverse_database_flag(): void
    {
        $student = $this->makeStudent();
        $staff = $this->makeStaff();
        $profile = $this->makeProfileFor($student, 'Issued');
        $this->actingAs($staff, 'admin');

        foreach ([['Yes', false], ['No', true]] as [$knownAllergies, $noAllergiesFlag]) {
            $response = app(AdminController::class)->updateStudentMedicalInformation(
                Request::create('/health-profile/' . $profile->id . '/medical-information', 'PUT', [
                    'medical_information' => ['known_allergies' => $knownAllergies],
                ]),
                $profile->id
            );

            $this->assertSame(302, $response->getStatusCode());
            $this->assertSame($noAllergiesFlag, $profile->fresh()->no_allergies);
        }
    }

    public function test_student_personal_information_updates_are_recorded_in_history_and_audit_trail(): void
    {
        $student = $this->makeStudent();
        $staff = $this->makeStaff();
        $profile = $this->makeProfileFor($student, 'Issued');
        $this->actingAs($staff, 'admin');

        $response = app(AdminController::class)->updateStudentMedicalInformation(
            Request::create('/health-profile/' . $profile->id . '/medical-information', 'PUT', [
                'medical_information' => [
                    'birthday' => '2002-04-05',
                    'sex' => 'Female',
                    'course_college' => 'Information Technology',
                    'cellphone' => '09123456789',
                ],
            ]),
            $profile->id
        );

        $this->assertSame(302, $response->getStatusCode());
        $profile->refresh();
        $this->assertNull($profile->birthday);
        $this->assertSame('Female', $profile->sex);
        $this->assertSame('Information Technology', $profile->course_college);
        $this->assertSame('09123456789', $profile->cellphone);

        $log = ActivityLog::query()->firstOrFail();
        $this->assertSame('Clinic Staff', $log->user_name);
        $this->assertSame(
            ['Sex', 'Program', 'Emergency contact number'],
            $log->metadata['updated_field_labels']
        );
        $this->assertStringContainsString('Sex, Program, Emergency contact number', $log->description);
        $this->assertStringNotContainsString('Information Technology', $log->metadata['updated_values_encrypted']);

        $secondResponse = app(AdminController::class)->updateStudentMedicalInformation(
            Request::create('/health-profile/' . $profile->id . '/medical-information', 'PUT', [
                'medical_information' => ['height' => '5.6'],
            ]),
            $profile->id
        );
        $this->assertSame(302, $secondResponse->getStatusCode());

        $historyMethod = new \ReflectionMethod(AdminController::class, 'medicalInformationChangeHistory');
        $historyMethod->setAccessible(true);
        $history = $historyMethod->invoke(app(AdminController::class), HealthProfile::class, (int) $profile->id);
        $this->assertCount(1, $history);
        $this->assertCount(2, $history->first()['events']);
        $this->assertSame('Clinic Staff (Admin)', $history->first()['events'][0]['account']);
        $this->assertSame(
            ['Height', 'Sex', 'Program', 'Emergency contact number'],
            array_column($history->first()['fields'], 'label')
        );
        $this->assertSame('5.6 ft', $history->first()['fields'][0]['value']);
        $this->assertSame('Female', $history->first()['fields'][1]['value']);

        $log->created_at = now()->subDay();
        $log->save();
        $history = $historyMethod->invoke(app(AdminController::class), HealthProfile::class, (int) $profile->id);
        $this->assertCount(2, $history);
        $this->assertCount(1, $history[0]['events']);
        $this->assertSame(['Height'], array_column($history[0]['fields'], 'label'));
        $this->assertSame($log->created_at->toDateString(), $history[1]['created_at']->toDateString());
    }

    public function test_history_and_audit_include_only_changed_values_even_when_fields_are_encrypted(): void
    {
        config()->set('health_data_encryption.enabled', true);
        config()->set('health_data_encryption.key', 'base64:' . base64_encode(str_repeat('h', 32)));
        config()->set('health_data_encryption.fields.health_profiles', [
            'height' => 'height_encrypted',
            'weight' => 'weight_encrypted',
        ]);

        $student = $this->makeStudent();
        $staff = $this->makeStaff();
        $profile = HealthProfile::forceCreate([
            'user_id' => $student->id,
            'student_id' => $student->student_number,
            'student_number' => $student->student_number,
            'clearance_status' => 'Issued',
            'height' => '5.5',
            'weight' => '64',
        ]);
        $this->actingAs($staff, 'admin');

        $response = app(AdminController::class)->updateStudentMedicalInformation(
            Request::create('/health-profile/' . $profile->id . '/medical-information', 'PUT', [
                'medical_information' => [
                    'height' => '5.6',
                    'weight' => '64',
                ],
            ]),
            $profile->id
        );

        $this->assertSame(302, $response->getStatusCode());
        $log = ActivityLog::query()->firstOrFail();
        $this->assertSame(['height'], $log->metadata['updated_fields']);
        $this->assertSame(['Height'], $log->metadata['updated_field_labels']);

        $historyMethod = new \ReflectionMethod(AdminController::class, 'medicalInformationChangeHistory');
        $historyMethod->setAccessible(true);
        $history = $historyMethod->invoke(app(AdminController::class), HealthProfile::class, (int) $profile->id);
        $this->assertSame(['Height'], array_column($history->first()['fields'], 'label'));
        $this->assertSame('5.6 ft', $history->first()['fields'][0]['value']);
        $this->assertSame('5.5 ft', $history->first()['fields'][0]['previous_value']);
        $this->assertSame(['height' => '5.5'], json_decode(Crypt::decryptString($log->metadata['previous_values_encrypted']), true));
    }

    public function test_employee_history_and_audit_include_only_changed_encrypted_values(): void
    {
        config()->set('health_data_encryption.enabled', true);
        config()->set('health_data_encryption.key', 'base64:' . base64_encode(str_repeat('e', 32)));
        config()->set('health_data_encryption.fields.health_profile_emp', [
            'contact_no' => 'contact_no_encrypted',
            'current_medications' => 'current_medications_encrypted',
        ]);

        $staff = $this->makeStaff();
        $faculty = $this->makeStudent([
            'name' => 'Faculty Member',
            'user_type' => 'Faculty',
            'idp_role' => 'faculty',
            'student_number' => null,
            'reference_number' => 'FAC-1001',
        ]);
        $profile = EmployeeHealthProfile::forceCreate([
            'user_id' => $faculty->id,
            'employee_number' => $faculty->reference_number,
            'contact_no' => '09111111111',
            'current_medications' => 'Inhaler',
        ]);
        $this->actingAs($staff, 'admin');

        $response = app(AdminController::class)->updateEmployeeMedicalInformation(
            Request::create('/employee-health-profile/' . $profile->id . '/medical-information', 'PUT', [
                'medical_information' => [
                    'contact_no' => '09222222222',
                    'current_medications' => 'Inhaler',
                ],
            ]),
            $profile
        );

        $this->assertSame(302, $response->getStatusCode());
        $log = ActivityLog::query()->firstOrFail();
        $this->assertSame('faculty_medical_information_updated', $log->event_type);
        $this->assertSame(['contact_no'], $log->metadata['updated_fields']);
        $this->assertSame(['Contact number'], $log->metadata['updated_field_labels']);

        $historyMethod = new \ReflectionMethod(AdminController::class, 'medicalInformationChangeHistory');
        $historyMethod->setAccessible(true);
        $history = $historyMethod->invoke(app(AdminController::class), EmployeeHealthProfile::class, (int) $profile->id);
        $this->assertSame(['Contact number'], array_column($history->first()['fields'], 'label'));
        $this->assertSame('09222222222', $history->first()['fields'][0]['value']);
    }

    public function test_employee_health_information_saves_address_and_emergency_contact_fields(): void
    {
        $staff = $this->makeStaff();
        $faculty = $this->makeStudent([
            'name' => 'Faculty Member',
            'user_type' => 'Faculty',
            'idp_role' => 'faculty',
            'student_number' => null,
            'reference_number' => 'FAC-1001',
        ]);
        $profile = EmployeeHealthProfile::forceCreate([
            'user_id' => $faculty->id,
            'employee_number' => $faculty->reference_number,
        ]);
        $this->actingAs($staff, 'admin');

        $response = app(AdminController::class)->updateEmployeeMedicalInformation(
            Request::create('/employee-health-profile/' . $profile->id . '/medical-information', 'PUT', [
                'medical_information' => [
                    'street' => '12 Sample Street',
                    'barangay' => 'Barangay One',
                    'municipality' => 'Manila',
                    'province' => 'Metro Manila',
                    'emergency_contact_person' => 'Alex Faculty',
                    'emergency_contact_no' => '09987654321',
                ],
            ]),
            $profile
        );

        $this->assertSame(302, $response->getStatusCode());
        $profile->refresh();
        $this->assertSame('12 Sample Street, Barangay One, Manila, Metro Manila', $profile->home_address);
        $this->assertSame('12 Sample Street', $profile->street);
        $this->assertSame('Alex Faculty', $profile->emergency_contact_person);
        $this->assertSame('09987654321', $profile->emergency_contact_no);

        $log = ActivityLog::query()->firstOrFail();
        $this->assertEqualsCanonicalizing([
            'street', 'barangay', 'municipality', 'province',
            'emergency_contact_person', 'emergency_contact_no',
        ], $log->metadata['updated_fields']);
        $this->assertNotContains('home_address', $log->metadata['updated_fields']);
    }

    public function test_history_hides_legacy_fields_without_captured_values(): void
    {
        $profile = $this->makeProfileFor($this->makeStudent(), 'Issued');
        $baseLog = [
            'user_name' => 'Clinic Staff',
            'user_role' => 'admin',
            'action' => 'Student Medical Information Updated',
            'module' => 'Health Records',
            'event_type' => 'student_medical_information_updated',
            'subject_type' => HealthProfile::class,
            'subject_id' => (string) $profile->id,
        ];

        ActivityLog::create(array_merge($baseLog, [
            'metadata' => [
                'updated_fields' => ['height', 'weight', 'blood_pressure'],
                'updated_field_labels' => ['Height', 'Weight', 'Blood pressure'],
                'updated_values_encrypted' => Crypt::encryptString(json_encode(['height' => '5.6'])),
            ],
        ]));
        ActivityLog::create(array_merge($baseLog, [
            'metadata' => [
                'updated_fields' => ['pulse_rate', 'temperature'],
                'updated_field_labels' => ['Pulse rate', 'Temperature'],
            ],
        ]));

        $historyMethod = new \ReflectionMethod(AdminController::class, 'medicalInformationChangeHistory');
        $historyMethod->setAccessible(true);
        $history = $historyMethod->invoke(app(AdminController::class), HealthProfile::class, (int) $profile->id);

        $this->assertCount(1, $history);
        $this->assertSame(['Height'], array_column($history->first()['fields'], 'label'));
        $this->assertSame('5.6 ft', $history->first()['fields'][0]['value']);
        $this->assertSame('Value not captured for this earlier update', $history->first()['fields'][0]['previous_value']);
        $this->assertCount(1, $history->first()['events']);
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
                        'birthday' => '1984-07-29',
                        'sex' => 'Female',
                        'office' => 'Medical Clinic',
                        'contact_no' => '09987654321',
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
            $this->assertSame('1984-07-29', $profile->birthday->format('Y-m-d'));
            $this->assertSame('Female', $profile->sex);
            $this->assertSame('Medical Clinic', $profile->office);
            $this->assertSame('09987654321', $profile->contact_no);
            $this->assertSame('Issued', $profile->clearance_status);
            $this->assertSame('medical-certificates/employee-original.pdf', $profile->medical_certificate);
            $this->assertSame($eventType, ActivityLog::query()->latest('id')->value('event_type'));
            $latestLog = ActivityLog::query()->latest('id')->firstOrFail();
            $this->assertEqualsCanonicalizing(
                ['Date of birth', 'Sex', 'Office', 'Contact number', 'Past medical history', 'Current medications', 'Disability', 'Weight'],
                $latestLog->metadata['updated_field_labels']
            );
        }

        $this->assertSame(0, StudentMedicalAssessment::query()->count());
    }

    public function test_legacy_admin_user_type_is_recognized_for_employee_health_information_updates(): void
    {
        $staff = $this->makeStaff();
        $this->actingAs($staff, 'admin');
        $employee = $this->makeStudent([
            'name' => 'Admin Tester One',
            'user_type' => 'Regular',
            'idp_role' => null,
            'user_role' => User::ROLE_ADMIN,
            'student_number' => null,
            'reference_number' => '00001',
        ]);
        $profile = EmployeeHealthProfile::forceCreate([
            'user_id' => $employee->id,
            'employee_number' => $employee->reference_number,
            'current_medications' => 'Original medication',
        ]);

        $this->assertNull($employee->clinicAccountTypeKey());

        $response = app(AdminController::class)->updateEmployeeMedicalInformation(
            Request::create('/employee-health-profile/' . $profile->id . '/medical-information', 'PUT', [
                'medical_information' => ['current_medications' => 'Reviewed medication'],
            ]),
            $profile
        );

        $this->assertSame(302, $response->getStatusCode());
        $this->assertSame('Reviewed medication', $profile->fresh()->current_medications);
        $this->assertSame('admin_medical_information_updated', ActivityLog::query()->value('event_type'));

        $profileTypeMethod = new \ReflectionMethod(AdminController::class, 'employeeMedicalInformationProfileType');
        $profileTypeMethod->setAccessible(true);
        $this->assertSame('Admin', $profileTypeMethod->invoke(app(AdminController::class), $employee));
    }

    public function test_employee_modal_renders_employee_fields_and_medical_information_save_route(): void
    {
        $this->withSession([
            '_old_input' => [
                'medical_information' => [
                    'street' => '',
                    'province' => '',
                    'municipality' => '',
                    'barangay' => '',
                ],
            ],
        ]);

        $html = view('admin.partials.medical-assessment-modal', [
            'canUpdateMedicalAssessment' => true,
            'medicalAssessmentProfileTypeLabel' => 'Faculty',
            'medicalAssessmentSnapshot' => [
                'Personal Information' => [
                    'Date of birth' => 'Mar 12, 1985',
                    'Sex' => 'Female',
                    'Contact number' => '09123456789',
                ],
                'Address' => ['Address' => '12 Sample Street, Barangay One, Manila, Metro Manila'],
                'Emergency Contact' => [
                    'Emergency contact person' => 'Alex Faculty',
                    'Emergency contact number' => '09987654321',
                ],
                'Employment Information' => [
                    'Employee number' => 'FAC-1001',
                    'Office' => 'Medical Clinic',
                ],
                'Medical & Family History' => [
                    'Past medical history' => 'Not recorded',
                    'Other past medical history' => 'Not recorded',
                    'Family history' => 'Not recorded',
                    'Other family history' => 'Not recorded',
                    'Disability' => 'No',
                    'Disability type' => 'Not recorded',
                ],
            ],
            'medicalAssessmentEditableValues' => [
                'birthday' => '1985-03-12',
                'sex' => 'Female',
                'office' => 'Medical Clinic',
                'contact_no' => '09123456789',
                'street' => '12 Sample Street',
                'barangay' => 'Barangay One',
                'municipality' => 'Manila',
                'province' => 'Metro Manila',
                'emergency_contact_person' => 'Alex Faculty',
                'emergency_contact_no' => '09987654321',
            ],
            'medicalAssessmentHistory' => collect(),
            'medicalInformationChangeHistory' => collect([[
                'type' => 'information',
                'title' => 'Health information updated',
                'date_label' => 'Oct 28, 2026',
                'created_at' => now(),
                'fields' => [
                    ['label' => 'Height', 'value' => '5.6'],
                    ['label' => 'Weight', 'value' => '64'],
                ],
                'events' => [[
                    'created_at' => now(),
                    'account' => 'Clinic Staff (Admin)',
                ]],
            ]]),
            'medicalAssessmentUpdateUrl' => route('admin.employee_health_profile.medical_information.update', 77),
            'employeeDocuments' => [],
            'errors' => new \Illuminate\Support\ViewErrorBag(),
        ])->render();

        $this->assertStringContainsString('id="medicalInformationModal"', $html);
        $this->assertStringContainsString('/employee-health-profile/77/medical-information', $html);
        $this->assertStringContainsString('data-medical-label="History"', $html);
        $this->assertStringContainsString('View History', $html);
        $this->assertStringNotContainsString('Assessment History', $html);
        $this->assertStringContainsString('Personal Information', $html);
        $this->assertStringContainsString('name="medical_information[birthday]"', $html);
        $this->assertStringContainsString('name="medical_information[sex]"', $html);
        $this->assertStringContainsString('name="medical_information[office]"', $html);
        $this->assertStringContainsString('name="medical_information[contact_no]"', $html);
        $this->assertStringContainsString('Employment Information', $html);
        $this->assertStringContainsString('Emergency Contact', $html);
        $this->assertStringContainsString('name="medical_information[emergency_contact_person]"', $html);
        $this->assertStringContainsString('data-medical-address-location="province"', $html);
        $this->assertStringContainsString('value="Metro Manila" selected', $html);
        $this->assertStringContainsString('value="Manila" selected', $html);
        $this->assertStringContainsString('value="Barangay One" selected', $html);
        $this->assertStringContainsString('Update to', $html);
        $this->assertStringContainsString('Height', $html);
        $this->assertStringContainsString('5.6', $html);
        $this->assertStringContainsString('Updated by Clinic Staff (Admin)', $html);
    }

    public function test_employee_medical_address_edit_values_merge_legacy_locations_with_existing_street(): void
    {
        $profile = new EmployeeHealthProfile([
            'street' => '123 Mabini St.',
            'home_address' => '123 Mabini St., Amti, Boliney, Abra',
            'province' => '',
            'municipality' => '',
            'barangay' => '',
        ]);
        $method = new \ReflectionMethod(AdminController::class, 'employeeMedicalAddressParts');
        $method->setAccessible(true);

        $this->assertSame([
            'street' => '123 Mabini St.',
            'barangay' => 'Amti',
            'municipality' => 'Boliney',
            'province' => 'Abra',
        ], $method->invoke(app(AdminController::class), $profile));
    }

    public function test_employee_medical_address_edit_values_use_the_approved_snapshot_address(): void
    {
        $profile = new EmployeeHealthProfile([
            'street' => '123 Mabini St.',
            'home_address' => '123 Mabini St.',
        ]);
        $displayProfile = new EmployeeHealthProfile([
            'home_address' => '123 Mabini St., Abilan, Buenavista, Agusan Del Norte',
        ]);
        $method = new \ReflectionMethod(AdminController::class, 'employeeMedicalAddressEditValues');
        $method->setAccessible(true);

        $this->assertSame([
            'street' => '123 Mabini St.',
            'barangay' => 'Abilan',
            'municipality' => 'Buenavista',
            'province' => 'Agusan Del Norte',
        ], $method->invoke(app(AdminController::class), $profile, $displayProfile));
    }

    public function test_employee_medical_edit_uses_walkin_choices_conditional_details_and_vital_units(): void
    {
        $html = view('admin.partials.medical-assessment-modal', [
            'canUpdateMedicalAssessment' => true,
            'medicalAssessmentProfileTypeLabel' => 'Admin',
            'medicalAssessmentSnapshot' => [
                'Medical & Family History' => [
                    'Past medical history' => 'Not recorded',
                    'Other past medical history' => 'Not recorded',
                    'Family history' => 'Asthma',
                    'Other family history' => 'Not recorded',
                    'Disability' => 'No',
                    'Disability type' => 'Not recorded',
                ],
                'Hospitalization & Surgery' => [
                    'Previous hospitalization' => 'No',
                    'Hospitalization details' => 'Not recorded',
                    'Operation / surgery' => 'No',
                    'Operation / surgery details' => 'Not recorded',
                ],
                'Vital Signs' => [
                    'Height' => '5.6',
                    'Weight' => '144',
                    'Temperature' => '36',
                ],
                'Physical Examination Findings' => [
                    'Head' => 'Wound', 'Eyes' => 'Not recorded', 'Ears' => 'Not recorded',
                    'Throat' => 'Not recorded', 'Chest & lungs' => 'Normal', 'Chest X-ray' => 'Normal',
                    'Breast' => 'Normal', 'Heart murmur' => 'Absent', 'Heart rhythm' => 'Regular',
                    'Abdomen' => 'Normal', 'Last menstruation' => 'Not recorded', 'Extremities' => 'Not recorded',
                    'Vertebral column' => 'Normal', 'Skin' => 'Not recorded', 'Scars' => 'Absent',
                ],
                'Assessment & Follow-up' => [
                    'Working impression' => 'Stable', 'Fit status' => 'Others: Limited duty',
                    'For work-up' => 'Not recorded', 'Referred to' => 'Cardio',
                    'Other referral' => 'Not recorded', 'Follow-up date' => 'Not recorded',
                ],
            ],
            'medicalAssessmentEditableValues' => [
                'past_medical_history' => [],
                'family_history' => ['Asthma'],
                'has_disability' => false,
                'previous_hospitalization' => false,
                'previous_hospitalization_details' => 'Old admission details',
                'operation_surgery' => false,
                'operation_surgery_details' => 'Old surgery details',
                'height' => '5.6', 'weight' => '144', 'temperature' => '36',
                'head_findings' => ['Wound'], 'eyes_findings' => [], 'ears_findings' => [],
                'throat_findings' => [], 'chest_lungs_findings' => ['Normal'],
                'chest_xray_result' => 'Normal', 'breast_findings' => 'Normal',
                'heart_murmur' => 'Absent', 'heart_rhythm' => 'Regular', 'abdomen_findings' => 'Normal',
                'extremities_findings' => null, 'vertebral_column_findings' => 'Normal',
                'skin_findings' => [], 'scars_findings' => 'Absent',
                'working_impression' => 'Stable', 'fit_status' => 'Others: Limited duty',
                'for_work_up' => null, 'referred_to' => ['Cardio'], 'referred_to_others' => null,
            ],
            'medicalAssessmentHistory' => collect(),
            'medicalInformationChangeHistory' => collect(),
            'medicalAssessmentUpdateUrl' => '/employee-health-profile/77/medical-information',
            'employeeDocuments' => [],
            'errors' => new \Illuminate\Support\ViewErrorBag(),
        ])->render();

        $this->assertMatchesRegularExpression('/<textarea[^>]*name="medical_information\[past_medical_history\]"[^>]*>\s*<\/textarea>/', $html);
        $this->assertMatchesRegularExpression('/data-medical-depends-on-key="previous_hospitalization"[^>]*hidden/', $html);
        $this->assertMatchesRegularExpression('/data-medical-depends-on-key="operation_surgery"[^>]*hidden/', $html);
        $this->assertStringContainsString('5.6 ft', $html);
        $this->assertStringContainsString('144 lbs', $html);
        $this->assertStringContainsString('36 °C', $html);
        $this->assertMatchesRegularExpression('/value="Wound"[^>]*checked/', $html);
        $this->assertMatchesRegularExpression('/value="Normal"[^>]*checked/', $html);
        $this->assertStringContainsString('data-medical-choice-format="json"', $html);
        $this->assertStringContainsString('Select fit status', $html);
        $this->assertStringContainsString('Fit for Clinic Duty', $html);
        $this->assertStringContainsString('Other fit status', $html);
        $this->assertStringContainsString('Others: Limited duty', $html);
        $this->assertStringContainsString('Cardio', $html);
        $this->assertStringNotContainsString('<textarea name="medical_information[head_findings]">[]</textarea>', $html);
    }

    public function test_employee_medical_update_persists_walkin_physical_exam_and_followup_choices(): void
    {
        $staff = $this->makeStaff();
        $this->actingAs($staff, 'admin');
        $employee = $this->makeStudent([
            'name' => 'Faculty Member',
            'user_type' => 'Faculty',
            'idp_role' => 'faculty',
            'student_number' => null,
            'reference_number' => 'FAC-1001',
        ]);
        $profile = EmployeeHealthProfile::forceCreate([
            'user_id' => $employee->id,
            'employee_number' => $employee->reference_number,
            'previous_hospitalization' => false,
            'previous_hospitalization_details' => 'Existing admission note',
            'operation_surgery' => false,
            'operation_surgery_details' => 'Existing surgery note',
        ]);

        $response = app(AdminController::class)->updateEmployeeMedicalInformation(
            Request::create('/employee-health-profile/' . $profile->id . '/medical-information', 'PUT', [
                'medical_information' => [
                    'head_findings' => '["Wound", "Mass"]',
                    'chest_lungs_findings' => '["Normal"]',
                    'chest_xray_result' => 'With Findings',
                    'heart_murmur' => 'Absent',
                    'fit_status' => 'Others: Limited duty',
                    'referred_to' => '["Cardio", "ENT"]',
                    'follow_up_on' => '2026-11-10',
                    'previous_hospitalization' => 'No',
                    'operation_surgery' => 'No',
                ],
            ]),
            $profile
        );

        $profile->refresh();
        $this->assertSame(302, $response->getStatusCode());
        $this->assertSame(['Wound', 'Mass'], $profile->head_findings);
        $this->assertSame(['Normal'], $profile->chest_lungs_findings);
        $this->assertSame('With Findings', $profile->chest_xray_result);
        $this->assertSame('Absent', $profile->heart_murmur);
        $this->assertSame('Others: Limited duty', $profile->fit_status);
        $this->assertSame(['Cardio', 'ENT'], $profile->referred_to);
        $this->assertSame('2026-11-10', $profile->follow_up_on);
        $this->assertSame('Existing admission note', $profile->previous_hospitalization_details);
        $this->assertSame('Existing surgery note', $profile->operation_surgery_details);
    }

    public function test_student_personal_information_keeps_read_only_details_visible_and_uses_dropdowns(): void
    {
        $html = view('admin.partials.medical-assessment-modal', [
            'canUpdateMedicalAssessment' => true,
            'medicalAssessmentProfileTypeLabel' => 'Student',
            'profile' => (object) [
                'id' => 77,
                'has_disability' => 'No',
                'student_photo' => null,
                'health_declaration' => null,
                'medical_certificate' => null,
                'chest_xray_result' => null,
                'pwd_id_proof' => null,
            ],
            'medicalAssessmentSnapshot' => [
                'Personal Information' => [
                    'Full name' => 'Jamie Student',
                    'Student number' => '2025-00001-TG-1',
                    'Email address' => 'jamie@example.test',
                    'Date of birth' => 'Oct 10, 2000',
                    'Sex' => 'Female',
                    'Contact number' => '09123456789',
                ],
                'Address' => [
                    'Address' => '12 Sample Street, Barangay One, Manila, Metro Manila',
                    'ZIP code' => '1008',
                ],
                'Emergency Contact' => [
                    'Emergency contact person' => 'Alex Student',
                    'Emergency contact number' => '09987654321',
                    'Landline' => '02-1234567',
                ],
                'Academic Information' => [
                    'Course Code' => 'BSIT',
                    'Year' => '2',
                    'Section' => '1',
                ],
                'Medical History' => [
                    'Known illness' => 'Not recorded',
                    'Medical conditions' => 'Not recorded',
                    'Other illness' => 'Not recorded',
                    'Blood type' => 'Not recorded',
                    'Disability' => 'Not recorded',
                    'Disability type' => 'Not recorded',
                ],
                'Medical Certificate & Imaging' => [
                    'Medical certificate' => 'Uploaded',
                    'Chest X-ray report' => 'Uploaded',
                    'Health declaration' => 'Uploaded',
                    'Certificate doctor' => 'Dr. Example',
                    'Certificate date' => 'Oct 12, 2026',
                ],
                'Referral' => [
                    'Referral details' => 'Not recorded',
                    'Encoded findings' => 'Not recorded',
                ],
            ],
            'medicalAssessmentEditableValues' => [
                'sex' => 'Female',
                'course_code' => 'BSIT',
                'user_year' => '2',
                'user_section' => '1',
                'cellphone' => '09987654321',
                'guardian_name' => 'Alex Student',
                'landline' => '02-1234567',
                'user_contact_no' => '09123456789',
                'referral_details' => [],
                'street' => '12 Sample Street',
                'barangay' => 'Barangay One',
                'municipality' => 'Manila City',
                'province' => 'Metro Manila',
                'zipcode' => '1008',
            ],
            'medicalAssessmentHistory' => collect(),
            'medicalInformationChangeHistory' => collect(),
            'healthFormSubmissions' => collect(),
            'medicalAssessmentUpdateUrl' => '/health-profile/77/medical-information',
            'errors' => new \Illuminate\Support\ViewErrorBag(),
        ])->render();

        $this->assertStringContainsString('Jamie Student', $html);
        $this->assertStringContainsString('data-medical-view-title>Summary</h3>', $html);
        $this->assertStringNotContainsString('Health Overview', $html);
        $this->assertStringContainsString('jamie@example.test', $html);
        $this->assertStringContainsString('Date of birth', $html);
        $this->assertStringContainsString('Oct 10, 2000', $html);
        $this->assertStringContainsString('Year', $html);
        $this->assertStringContainsString('Section', $html);
        $this->assertStringContainsString('Academic Information', $html);
        $this->assertStringContainsString('Course Code', $html);
        $this->assertStringContainsString('Address', $html);
        $this->assertStringContainsString('Emergency Contact', $html);
        $this->assertStringContainsString('Emergency contact person', $html);
        $this->assertStringContainsString('Emergency contact number', $html);
        $this->assertStringContainsString('Landline', $html);
        $this->assertStringContainsString('Address', $html);
        $this->assertStringContainsString('ZIP code', $html);
        $this->assertStringContainsString('No information provided', $html);
        $this->assertStringContainsString('>Select an answer</option>', $html);
        $this->assertStringContainsString('>Select an option</option>', $html);
        $this->assertMatchesRegularExpression('/<option value="" disabled[^>]*>Select an option<\/option>/', $html);
        $this->assertMatchesRegularExpression('/<option value="" disabled[^>]*>Select an answer<\/option>/', $html);
        $this->assertStringNotContainsString('Not recorded', $html);
        $this->assertStringNotContainsString('Encoded findings', $html);
        $this->assertStringContainsString('Medical Certificate Information', $html);
        $this->assertStringNotContainsString('class="medical-document-info-head">Certificate Information</div>', $html);
        $this->assertMatchesRegularExpression('/<textarea[^>]*name="medical_information\[referral_details\]"[^>]*>\s*<\/textarea>/', $html);

        $documentListStart = strpos($html, '<div class="medical-document-file-list"');
        $documentLabels = [
            '<span>Medical Certificate</span>',
            '<span>Chest X-ray Report</span>',
            '<span>Health Form</span>',
            '<span>Health Declaration</span>',
            '<span>2x2 Photo</span>',
        ];
        $documentPositions = array_map(
            fn ($label) => strpos($html, $label, $documentListStart),
            $documentLabels
        );
        $this->assertNotContains(false, $documentPositions);
        $sortedDocumentPositions = $documentPositions;
        sort($sortedDocumentPositions);
        $this->assertSame($documentPositions, $sortedDocumentPositions);
        $this->assertStringContainsString('medical-information-row-readonly', $html);
        $this->assertMatchesRegularExpression('/<select[^>]+name="medical_information\[sex\]"/', $html);
        $this->assertMatchesRegularExpression('/<select[^>]+name="medical_information\[course_code\]"/', $html);
        $this->assertMatchesRegularExpression('/<select[^>]+name="medical_information\[user_year\]"/', $html);
        $this->assertMatchesRegularExpression('/<select[^>]+name="medical_information\[user_section\]"/', $html);
        $this->assertStringContainsString('name="medical_information[cellphone]"', $html);
        $this->assertStringNotContainsString('name="medical_information[birthday]"', $html);
        $this->assertStringContainsString('data-medical-address-location="province"', $html);
        $this->assertStringContainsString('data-medical-address-location="municipality"', $html);
        $this->assertStringContainsString('data-medical-address-location="barangay"', $html);
        $this->assertStringContainsString('medical-information-row-address-zip', $html);
        $this->assertSame(1, substr_count($html, 'name="medical_information[zipcode]"'));
        $this->assertStringNotContainsString('name="medical_information[email]"', $html);
        $this->assertStringNotContainsString('name="medical_information[full_name]"', $html);
    }

    public function test_student_history_renders_field_initial_and_before_after_values(): void
    {
        $html = view('admin.partials.medical-assessment-modal', [
            'canUpdateMedicalAssessment' => true,
            'medicalAssessmentProfileTypeLabel' => 'Student',
            'profile' => (object) [
                'id' => 77,
                'has_disability' => 'Yes',
                'student_photo' => null,
                'health_declaration' => null,
                'medical_certificate' => null,
                'chest_xray_result' => null,
                'pwd_id_proof' => null,
            ],
            'medicalAssessmentSnapshot' => [
                'Medical History' => [
                    'Known illness' => 'No',
                    'Blood type' => 'Unknown',
                    'Disability' => 'Yes',
                    'Disability type' => 'Visual impairment',
                ],
                'Lifestyle & Immunization' => [
                    'COVID-19 vaccination' => 'Yes',
                    'Vaccine history' => 'Not recorded',
                ],
                'Vitals & Assessment' => [
                    'Height' => '5.6',
                    'Weight' => '144',
                    'Temperature' => '36.5',
                ],
            ],
            'medicalAssessmentEditableValues' => [
                'has_illness' => 'No',
                'blood_type' => 'Unknown',
                'has_disability' => 'Yes',
                'disability_type' => 'Visual impairment',
                'covid_vaccinated' => 'Yes',
                'vaccine_history' => [
                    'first_dose' => ['brand' => 'Pfizer', 'date' => '2021-03-01'],
                ],
                'height' => '5.6',
                'weight' => '144',
                'temperature' => '36.5',
            ],
            'medicalAssessmentHistory' => collect(),
            'medicalInformationChangeHistory' => collect([[
                'type' => 'information',
                'title' => 'Health information updated',
                'date_label' => 'Oct 28, 2026',
                'created_at' => now(),
                'fields' => [[
                    'label' => 'Height',
                    'value' => '5.6',
                    'previous_value' => '5.5',
                    'created_at' => now(),
                    'account' => 'PUP OCMS Admin (Superadmin)',
                ]],
                'events' => [],
            ]]),
            'healthFormSubmissions' => collect(),
            'medicalAssessmentUpdateUrl' => '/health-profile/77/medical-information',
            'errors' => new \Illuminate\Support\ViewErrorBag(),
        ])->render();

        $this->assertStringContainsString('class="medical-information-history-initial" aria-hidden="true">H</span>', $html);
        $this->assertStringContainsString('Height Updated', $html);
        $this->assertStringContainsString('Previous value', $html);
        $this->assertStringContainsString('5.5', $html);
        $this->assertStringContainsString('Updated value', $html);
        $this->assertStringContainsString('5.6', $html);
        $this->assertStringContainsString('PUP OCMS Admin (Superadmin)', $html);
        $this->assertStringNotContainsString('medical-information-change-table', $html);
        $this->assertStringContainsString('name="medical_information[blood_type]"', $html);
        $this->assertStringContainsString('name="medical_information[disability_type]"', $html);
        $this->assertStringContainsString('name="medical_information[other_disability_type]"', $html);
        $this->assertStringContainsString('data-medical-vaccine-dose="first_dose"', $html);
        $this->assertStringContainsString('data-medical-vaccine-dose="booster_2"', $html);
        $this->assertStringContainsString(' ft', $html);
        $this->assertStringContainsString(' lbs', $html);
        $this->assertStringContainsString('°C', $html);
        $this->assertStringNotContainsString('PWD ID Proof', $html);
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
