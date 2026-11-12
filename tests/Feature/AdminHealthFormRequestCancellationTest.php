<?php

namespace Tests\Feature;

use App\Http\Controllers\AdminController;
use App\Models\ActivityLog;
use App\Models\HealthFormSubmission;
use App\Models\HealthProfile;
use App\Models\HealthProfileCorrectionRequest;
use App\Models\User;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\{DB, Schema};
use Tests\TestCase;

class AdminHealthFormRequestCancellationTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        config(['database.default' => 'sqlite', 'database.connections.sqlite.database' => ':memory:', 'health_data_encryption.enabled' => false]);
        DB::purge('sqlite');
        Schema::create('users', function (Blueprint $table) {
            $table->id();
            foreach (['name', 'first_name', 'middle_name', 'last_name', 'email', 'student_id', 'student_number', 'user_role', 'user_type', 'password', 'remember_token'] as $column) $table->string($column)->nullable();
            $table->timestamps();
        });
        Schema::create('health_profiles', function (Blueprint $table) {
            $table->id(); $table->unsignedBigInteger('user_id');
            $table->string('clearance_status')->nullable(); $table->timestamp('verified_at')->nullable();
            $table->softDeletes(); $table->timestamps();
        });
        Schema::create('health_form_submissions', function (Blueprint $table) {
            $table->id(); $table->unsignedBigInteger('user_id'); $table->unsignedBigInteger('health_profile_id');
            $table->string('status'); $table->unsignedBigInteger('status_changed_by_user_id')->nullable();
            foreach (['requested_at', 'submitted_at', 'approved_at'] as $column) $table->timestamp($column)->nullable();
            $table->timestamps();
        });
        Schema::create('health_profile_correction_requests', function (Blueprint $table) {
            $table->id();
            foreach (['user_id', 'health_profile_id', 'health_form_submission_id', 'reviewed_by_user_id'] as $column) $table->unsignedBigInteger($column)->nullable();
            $table->string('status'); $table->string('type');
            foreach (['requested_at', 'submitted_at', 'reviewed_at'] as $column) $table->timestamp($column)->nullable();
            $table->timestamps();
        });
        Schema::create('activity_logs', function (Blueprint $table) {
            $table->id(); $table->unsignedBigInteger('user_id')->nullable();
            foreach (['user_name', 'user_role', 'action', 'module', 'event_type', 'description', 'route_name', 'http_method', 'request_path', 'status_code', 'subject_type', 'subject_id', 'ip_address', 'user_agent'] as $column) $table->text($column)->nullable();
            $table->timestamps();
        });
    }

    public function test_only_clinic_route_exposes_new_health_form_cancellation(): void
    {
        $this->assertTrue(Route::has('admin.health_profile.health_form_request.cancel'));
        $this->assertFalse(Route::has('student.health_form.request.cancel'));
    }

    public function test_clinic_can_cancel_only_the_unsubmitted_request_without_changing_approved_record(): void
    {
        $admin = User::factory()->create(['user_role' => User::ROLE_SUPERADMIN]);
        $student = User::factory()->create([
            'user_role' => User::ROLE_STUDENT,
            'user_type' => 'student',
            'student_number' => '2099-99998-TG-1',
        ]);
        $profile = HealthProfile::query()->create([
            'user_id' => $student->id,
            'clearance_status' => 'Issued',
            'verified_at' => now()->subDay(),
        ]);
        $approved = HealthFormSubmission::query()->create([
            'user_id' => $student->id,
            'health_profile_id' => $profile->id,
            'status' => HealthFormSubmission::STATUS_APPROVED,
            'approved_at' => now()->subDay(),
        ]);
        $requested = HealthFormSubmission::query()->create([
            'user_id' => $student->id,
            'health_profile_id' => $profile->id,
            'status' => HealthFormSubmission::STATUS_REQUESTED,
            'requested_at' => now(),
        ]);
        $correction = HealthProfileCorrectionRequest::query()->create([
            'user_id' => $student->id,
            'health_profile_id' => $profile->id,
            'health_form_submission_id' => $requested->id,
            'type' => HealthProfileCorrectionRequest::TYPE_NEW_HEALTH_FORM,
            'status' => HealthProfileCorrectionRequest::STATUS_PENDING,
            'requested_at' => now(),
        ]);
        Auth::guard('admin')->setUser($admin);

        $response = app(AdminController::class)->cancelNewHealthFormRequest(
            Request::create('/health-profile/' . $profile->id . '/health-form-request/' . $requested->id . '/cancel', 'POST'),
            $profile->id,
            $requested->id
        );

        $this->assertSame(302, $response->getStatusCode());
        $this->assertSame(HealthFormSubmission::STATUS_CANCELLED, $requested->fresh()->status);
        $this->assertSame(HealthProfileCorrectionRequest::STATUS_CANCELLED, $correction->fresh()->status);
        $this->assertNotNull($correction->fresh()->reviewed_at);
        $this->assertSame(HealthFormSubmission::STATUS_APPROVED, $approved->fresh()->status);
        $this->assertSame('Issued', $profile->fresh()->clearance_status);
        $this->assertNotNull($profile->fresh()->verified_at);
        $this->assertTrue(ActivityLog::query()
            ->where('action', 'Health Form Request Cancelled')
            ->where('subject_id', (string) $requested->id)
            ->exists());
    }

    public function test_clinic_can_cancel_a_submitted_request_and_close_both_records(): void
    {
        $admin = User::factory()->create(['user_role' => User::ROLE_SUPERADMIN]);
        $student = User::factory()->create([
            'user_role' => User::ROLE_STUDENT,
            'user_type' => 'student',
            'student_number' => '2099-99997-TG-1',
        ]);
        $profile = HealthProfile::query()->create(['user_id' => $student->id, 'clearance_status' => 'Issued']);
        $submission = HealthFormSubmission::query()->create([
            'user_id' => $student->id,
            'health_profile_id' => $profile->id,
            'status' => HealthFormSubmission::STATUS_SUBMITTED,
            'submitted_at' => now(),
        ]);
        $correction = HealthProfileCorrectionRequest::query()->create([
            'user_id' => $student->id,
            'health_profile_id' => $profile->id,
            'health_form_submission_id' => $submission->id,
            'type' => HealthProfileCorrectionRequest::TYPE_NEW_HEALTH_FORM,
            'status' => HealthProfileCorrectionRequest::STATUS_SUBMITTED,
            'submitted_at' => now(),
        ]);
        Auth::guard('admin')->setUser($admin);

        $response = app(AdminController::class)->cancelNewHealthFormRequest(
            Request::create('/health-profile/' . $profile->id . '/health-form-request/' . $submission->id . '/cancel', 'POST'),
            $profile->id,
            $submission->id
        );

        $this->assertSame(302, $response->getStatusCode());
        $this->assertSame(HealthFormSubmission::STATUS_CANCELLED, $submission->fresh()->status);
        $this->assertSame(HealthProfileCorrectionRequest::STATUS_CANCELLED, $correction->fresh()->status);
        $this->assertSame('Issued', $profile->fresh()->clearance_status);
        // Retrying cancellation cannot alter the closed request or add another audit entry.
        app(AdminController::class)->cancelNewHealthFormRequest(Request::create('/cancel', 'POST'), $profile->id, $submission->id);
        $this->assertSame(1, ActivityLog::where('subject_id', (string) $submission->id)->count());
        $submission->update(['status' => 'approved', 'approved_at' => now()]);
        $correction->update(['status' => 'approved']);
        app(AdminController::class)->cancelNewHealthFormRequest(Request::create('/cancel', 'POST'), $profile->id, $submission->id);
        $this->assertSame('approved', $submission->fresh()->status);
        $this->assertSame('approved', $correction->fresh()->status);
    }
}
