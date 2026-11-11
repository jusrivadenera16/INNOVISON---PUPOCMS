<?php

namespace Tests\Feature;

use App\Http\Controllers\WalkInController;
use App\Models\{EmployeeHealthProfile, HealthFormSubmission, HealthProfile, User};
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\{DB, Schema};
use ReflectionMethod;
use Tests\TestCase;

class WalkInSubmissionGuardTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        config(['database.default' => 'sqlite', 'database.connections.sqlite.database' => ':memory:', 'health_data_encryption.enabled' => false]);
        DB::purge('sqlite');
        Schema::create('users', function (Blueprint $table) {
            $table->id();
            foreach (['name', 'first_name', 'middle_name', 'last_name', 'email', 'user_type', 'student_type', 'user_role', 'idp_role', 'student_number', 'employee_number', 'student_id', 'reference_number', 'barcode'] as $field) $table->string($field)->nullable();
            $table->timestamps();
        });
        Schema::create('health_profiles', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id')->nullable();
            foreach (['reference_number', 'student_number', 'student_id', 'clearance_status', 'health_form_category'] as $field) $table->string($field)->nullable();
            $table->timestamps();
        });
        Schema::create('health_profile_emp', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id')->nullable();
            $table->string('employee_number')->nullable();
            $table->string('clearance_status')->nullable();
            $table->string('health_form_category')->nullable();
            $table->softDeletes();
            $table->timestamps();
        });
        Schema::create('dependents_profiles', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id')->nullable();
        });
        Schema::create('health_form_submissions', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id')->nullable();
            $table->unsignedBigInteger('health_profile_id')->nullable();
            $table->unsignedBigInteger('employee_health_profile_id')->nullable();
            $table->string('status');
            $table->timestamp('submitted_at')->nullable();
            $table->timestamp('approved_at')->nullable();
            $table->timestamps();
        });
    }

    private function eligible(HealthProfile|EmployeeHealthProfile|null $profile): bool
    {
        return (new ReflectionMethod(WalkInController::class, 'hasSubmittedClinicHealthForm'))->invoke(app(WalkInController::class), $profile);
    }

    public function test_id_or_approved_record_without_submission_is_not_eligible(): void
    {
        $profile = HealthProfile::forceCreate(['clearance_status' => 'Fully Cleared']);
        $this->assertFalse($this->eligible($profile));
        $this->assertFalse($this->eligible(null));
        HealthFormSubmission::create(['health_profile_id' => $profile->id, 'status' => 'requested']);
        $this->assertFalse($this->eligible($profile));
        HealthFormSubmission::create(['health_profile_id' => $profile->id, 'status' => 'submitted']);
        $this->assertFalse($this->eligible($profile));
    }

    public function test_submitted_clinic_forms_are_eligible_but_new_unsubmitted_cycle_blocks_old_approval(): void
    {
        $profile = HealthProfile::forceCreate([]);
        foreach (['submitted', 'under_review', 'needs_correction', 'approved'] as $status) {
            HealthFormSubmission::create(['health_profile_id' => $profile->id, 'status' => $status, 'submitted_at' => now()]);
            $this->assertTrue($this->eligible($profile));
        }
        HealthFormSubmission::create(['health_profile_id' => $profile->id, 'status' => 'requested']);
        $this->assertFalse($this->eligible($profile));
    }

    public function test_employee_form_must_match_the_employee_profile_not_a_student_form_with_the_same_id(): void
    {
        $profile = EmployeeHealthProfile::forceCreate(['employee_number' => 'EMP-1']);
        HealthFormSubmission::create(['health_profile_id' => $profile->id, 'status' => 'submitted', 'submitted_at' => now()]);
        $this->assertFalse($this->eligible($profile));
        HealthFormSubmission::create(['employee_health_profile_id' => $profile->id, 'status' => 'submitted', 'submitted_at' => now()]);
        $this->assertTrue($this->eligible($profile));
    }

    public function test_student_approval_endpoint_rejects_missing_form_without_changing_the_record(): void
    {
        $user = User::forceCreate(['name' => 'Student Patient', 'user_type' => 'Student', 'student_number' => '2026-00001-TG-1']);
        $profile = HealthProfile::forceCreate(['user_id' => $user->id, 'student_number' => $user->student_number, 'clearance_status' => 'For Verification']);
        $response = app(WalkInController::class)->saveStudentAssessment(Request::create('/', 'POST', [
            'reference_number' => $user->student_number, 'clearance_decision' => 'approve',
        ]));
        $this->assertSame(422, $response->getStatusCode());
        $this->assertSame('health_form_not_completed', $response->getData(true)['code']);
        $this->assertSame('For Verification', $profile->fresh()->clearance_status);
    }

    public function test_employee_approval_endpoint_rejects_missing_form_without_changing_approval(): void
    {
        $user = User::forceCreate(['name' => 'Employee Patient', 'user_type' => 'Faculty', 'employee_number' => 'EMP-1']);
        $profile = EmployeeHealthProfile::forceCreate(['user_id' => $user->id, 'employee_number' => 'EMP-1', 'clearance_status' => 'Fully Cleared']);
        $method = new ReflectionMethod(WalkInController::class, 'approveEmployeeHealthProfile');
        $response = $method->invoke(app(WalkInController::class), Request::create('/', 'POST'), [], 'EMP-1', 'Fully Cleared', false, false, [], [], 'No Findings / Normal', '', '', '');
        $this->assertSame(422, $response->getStatusCode());
        $this->assertSame('health_form_not_completed', $response->getData(true)['code']);
        $this->assertSame('Fully Cleared', $profile->fresh()->clearance_status);
    }

    public function test_approved_student_cannot_be_approved_again_or_create_another_form(): void
    {
        $user = User::forceCreate(['name' => 'OJT Student', 'user_type' => 'Student', 'student_number' => '2026-00001-TG-1']);
        $profile = HealthProfile::forceCreate(['user_id' => $user->id, 'student_number' => $user->student_number, 'clearance_status' => 'Fully Cleared']);
        $submission = HealthFormSubmission::create(['user_id' => $user->id, 'health_profile_id' => $profile->id, 'status' => 'approved', 'submitted_at' => now()->subDay(), 'approved_at' => now()]);
        $response = app(WalkInController::class)->saveStudentAssessment(Request::create('/', 'POST', [
            'reference_number' => $user->student_number, 'clearance_decision' => 'approve',
        ]));
        $this->assertSame(422, $response->getStatusCode());
        $this->assertStringContainsString('view-only', $response->getData(true)['message']);
        $this->assertSame(1, HealthFormSubmission::count());
        $this->assertTrue($submission->approved_at->equalTo($submission->fresh()->approved_at));
        $this->assertSame('Fully Cleared', $profile->fresh()->clearance_status);
    }

    public function test_approved_employee_cannot_be_approved_again(): void
    {
        $user = User::forceCreate(['name' => 'Employee', 'user_type' => 'Faculty', 'employee_number' => 'EMP-1']);
        $profile = EmployeeHealthProfile::forceCreate(['user_id' => $user->id, 'employee_number' => 'EMP-1', 'clearance_status' => 'Fully Cleared']);
        HealthFormSubmission::create(['employee_health_profile_id' => $profile->id, 'status' => 'approved', 'submitted_at' => now(), 'approved_at' => now()]);
        $this->expectException(\Illuminate\Validation\ValidationException::class);
        (new ReflectionMethod(WalkInController::class, 'approveEmployeeHealthProfile'))->invoke(app(WalkInController::class), Request::create('/', 'POST'), [], 'EMP-1', 'Fully Cleared', false, false, [], [], 'No Findings / Normal', '', '', '');
    }

    public function test_only_the_latest_submitted_cycle_is_reviewable_even_when_profile_was_already_cleared(): void
    {
        $profile = HealthProfile::forceCreate(['clearance_status' => 'Fully Cleared']);
        $method = new ReflectionMethod(WalkInController::class, 'reviewableClinicSubmission');
        $controller = app(WalkInController::class);
        HealthFormSubmission::create(['health_profile_id' => $profile->id, 'status' => 'approved', 'submitted_at' => now(), 'approved_at' => now()]);
        $this->assertNull($method->invoke($controller, $profile));
        $pending = HealthFormSubmission::create(['health_profile_id' => $profile->id, 'status' => 'under_review', 'submitted_at' => now()]);
        $this->assertSame($pending->id, $method->invoke($controller, $profile)->id);
        HealthFormSubmission::create(['health_profile_id' => $profile->id, 'status' => 'requested']);
        $this->assertNull($method->invoke($controller, $profile));
    }
}
