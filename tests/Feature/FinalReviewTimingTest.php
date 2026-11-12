<?php

namespace Tests\Feature;

use App\Http\Controllers\{ReportsController, WalkInController};
use App\Models\{EmployeeHealthProfile, HealthFormSubmission, HealthProfile, User};
use App\Services\FinalReviewTimingService;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\{DB, Schema};
use Symfony\Component\HttpKernel\Exception\HttpException;
use Tests\TestCase;

class FinalReviewTimingTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        config(['database.default' => 'sqlite', 'database.connections.sqlite.database' => ':memory:', 'health_data_encryption.enabled' => false]);
        DB::purge('sqlite');
        Schema::create('users', function (Blueprint $table) {
            $table->id();
            foreach (['name', 'first_name', 'middle_name', 'last_name', 'email', 'user_type', 'user_role'] as $column) $table->string($column)->nullable();
            $table->timestamps();
        });
        foreach (['health_profiles', 'health_profile_emp'] as $name) {
            Schema::create($name, function (Blueprint $table) {
                $table->id(); $table->unsignedBigInteger('user_id');
                foreach (['clearance_status', 'health_form_category', 'reference_number'] as $column) $table->string($column)->nullable();
                foreach (['verified_at', 'review_started_at', 'review_closed_at'] as $column) $table->timestamp($column)->nullable();
                $table->softDeletes(); $table->timestamps();
            });
        }
        Schema::create('health_form_submissions', function (Blueprint $table) {
            $table->id(); $table->unsignedBigInteger('user_id');
            foreach (['health_profile_id', 'employee_health_profile_id'] as $column) $table->unsignedBigInteger($column)->nullable();
            $table->string('status'); $table->string('category')->nullable(); $table->json('profile_snapshot')->nullable();
            foreach (['submitted_at', 'approved_at'] as $column) $table->timestamp($column)->nullable();
            $table->timestamps();
        });
    }

    public function test_each_open_replaces_time_in_for_every_patient_type_without_changing_approved_history(): void
    {
        foreach (['applicant', 'transferee', 'student', 'faculty', 'admin', 'employee', 'dependent'] as $type) {
            $employee = in_array($type, ['faculty', 'admin', 'employee', 'dependent'], true);
            $user = User::forceCreate(['name' => 'Timing Test', 'user_type' => $type]);
            $user->setRelation('dependentProfile', null);
            $class = $employee ? EmployeeHealthProfile::class : HealthProfile::class;
            $profile = $class::forceCreate(['user_id' => $user->id, 'clearance_status' => 'Issued', 'health_form_category' => 'OJT']);
            $profile->setRelation('user', $user)->setRelation('approvedBy', null);
            $key = $employee ? 'employee_health_profile_id' : 'health_profile_id';
            $old = HealthFormSubmission::create(['user_id' => $user->id, $key => $profile->id, 'status' => 'approved', 'approved_at' => '2026-11-01 09:00:00']);
            $oldAttributes = $old->fresh()->getAttributes();
            $pending = HealthFormSubmission::create(['user_id' => $user->id, $key => $profile->id, 'status' => 'submitted', 'submitted_at' => '2026-11-12 08:00:00', 'profile_snapshot' => ['profile' => []]]);
            $this->travelTo(now()->setDate(2026, 11, 12)->setTime(10, 0));
            app(FinalReviewTimingService::class)->open($profile, $user->id);
            $this->assertSame('10:00', \Carbon\Carbon::parse(data_get($pending->fresh()->profile_snapshot, 'review_timing.opened_at'))->format('H:i'));
            $this->travelTo(now()->setTime(10, 15));
            app(FinalReviewTimingService::class)->open($profile, $user->id);
            $pending->refresh();
            $this->assertSame('10:15', \Carbon\Carbon::parse(data_get($pending->profile_snapshot, 'review_timing.opened_at'))->format('H:i'));
            $this->assertSame('submitted', $pending->status);
            $this->assertNull($pending->approved_at);
            $this->assertSame($oldAttributes, $old->fresh()->getAttributes());
            $this->assertSame('Issued', $profile->fresh()->clearance_status);
            // Approval snapshot refresh must preserve the last actual opening instant.
            $pending->profile_snapshot = $pending->preserveReviewTiming(['profile' => ['review_closed_at' => '2026-11-12 10:20:00']]);
            $pending->status = 'approved'; $pending->approved_at = now()->setTime(11, 0); $pending->save();
            $pending->setRelation('user', $user);
            $entry = (new \ReflectionMethod(ReportsController::class, 'healthFormsLogbookEntry'))->invoke(app(ReportsController::class), $profile, $employee ? 'employee' : 'health', $pending);
            $this->assertSame('10:15', $entry['time_in']->format('H:i'));
            $this->assertSame('11:00', $entry['time_out']->format('H:i'));
            try { app(FinalReviewTimingService::class)->open($profile, $user->id); $this->fail('Approved versions must not be reopened in place.'); }
            catch (HttpException $error) { $this->assertSame(422, $error->getStatusCode()); }
        }
        $this->travelBack();
    }

    public function test_logbook_does_not_invent_time_in_from_submission_or_time_out_from_last_edit(): void
    {
        $user = new User(['name' => 'Legacy Student', 'user_type' => 'student']); $user->setRelation('dependentProfile', null);
        $profile = new HealthProfile(['clearance_status' => 'Issued']); $profile->setRelation('user', $user)->setRelation('approvedBy', null);
        $submission = new HealthFormSubmission(['status' => 'approved', 'submitted_at' => '2026-11-12 07:00:00', 'approved_at' => '2026-11-12 09:00:00', 'profile_snapshot' => ['profile' => ['review_closed_at' => '2026-11-12 10:00:00']]]);
        $submission->setRelation('user', $user);
        $entry = (new \ReflectionMethod(ReportsController::class, 'healthFormsLogbookEntry'))->invoke(app(ReportsController::class), $profile, 'health', $submission);
        $this->assertNull($entry['time_in']);
        $this->assertSame('09:00', $entry['time_out']->format('H:i'));
    }

    public function test_modal_close_endpoint_cannot_record_time_out_before_approval(): void
    {
        $user = User::forceCreate(['name' => 'Pending']);
        $profile = HealthProfile::forceCreate(['user_id' => $user->id, 'reference_number' => 'REF-1']);
        HealthFormSubmission::create(['user_id' => $user->id, 'health_profile_id' => $profile->id, 'status' => 'submitted', 'submitted_at' => now()]);
        try { app(WalkInController::class)->markFinalReviewTimeOut(Request::create('/', 'POST', ['reference_number' => 'REF-1'])); $this->fail('Pending requests have no Time Out.'); }
        catch (HttpException $error) { $this->assertSame(422, $error->getStatusCode()); }
        $this->assertNull($profile->fresh()->review_closed_at);
    }

    public function test_time_in_endpoint_uses_explicit_profile_type_when_student_and_employee_ids_collide(): void
    {
        $user = User::forceCreate(['name' => 'Dependent', 'user_type' => 'dependent']);
        $student = HealthProfile::forceCreate(['user_id' => $user->id, 'clearance_status' => 'For Verification']);
        $employee = EmployeeHealthProfile::forceCreate(['user_id' => $user->id, 'clearance_status' => 'For Verification']);
        $studentForm = HealthFormSubmission::create(['user_id' => $user->id, 'health_profile_id' => $student->id, 'status' => 'submitted', 'submitted_at' => now()]);
        $employeeForm = HealthFormSubmission::create(['user_id' => $user->id, 'employee_health_profile_id' => $employee->id, 'status' => 'submitted', 'submitted_at' => now()]);
        $response = app(WalkInController::class)->markFinalReviewTimeIn(Request::create('/', 'POST', ['record_type' => 'employee', 'profile_id' => $employee->id]));
        $this->assertTrue($response->getData(true)['success']);
        $this->assertNotNull(data_get($employeeForm->fresh()->profile_snapshot, 'review_timing.opened_at'));
        $this->assertNull(data_get($studentForm->fresh()->profile_snapshot, 'review_timing.opened_at'));
    }
}
