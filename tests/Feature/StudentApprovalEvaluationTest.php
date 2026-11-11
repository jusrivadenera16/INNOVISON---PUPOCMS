<?php

namespace Tests\Feature;

use App\Http\Controllers\WalkInController;
use App\Models\HealthProfile;
use App\Models\EmployeeHealthProfile;
use App\Models\User;
use App\Services\StudentNotificationMailer;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Validation\ValidationException;
use Tests\TestCase;

class StudentApprovalEvaluationTest extends TestCase
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
            foreach (['name', 'first_name', 'middle_name', 'last_name', 'email', 'user_type', 'student_number'] as $field) {
                $table->string($field)->nullable();
            }
            $table->timestamps();
        });
        Schema::create('health_profiles', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id');
            $table->string('clearance_status');
            $table->string('health_form_category')->nullable();
            $table->string('pullout_status')->nullable();
            $table->timestamps();
        });
        Schema::create('health_profile_emp', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id');
            $table->string('clearance_status');
            $table->string('submission_status');
            $table->string('health_form_category')->nullable();
            $table->softDeletes();
            $table->timestamps();
        });
        Schema::create('eval_form', function (Blueprint $table) {
            $table->id();
            $table->string('source_type');
            $table->unsignedBigInteger('source_id');
            $table->unsignedBigInteger('consultation_id')->nullable();
            $table->unsignedBigInteger('user_id');
            $table->unsignedBigInteger('requested_by_user_id')->nullable();
            $table->string('status');
            $table->timestamp('sent_at')->nullable();
            $table->timestamp('submitted_at')->nullable();
            $table->timestamps();
        });
    }

    private function profile(string $status = 'Fully Cleared', string $type = 'Student'): HealthProfile
    {
        $user = User::forceCreate(['name' => 'Test Patient', 'user_type' => $type, 'email' => 'patient@example.com']);

        return HealthProfile::forceCreate(['user_id' => $user->id, 'clearance_status' => $status]);
    }

    private function choose(HealthProfile $profile, string $decision)
    {
        return app(WalkInController::class)->sendStudentApprovalEvaluation(Request::create('/', 'POST', [
            'source_type' => 'student_approval', 'source_id' => $profile->id, 'decision' => $decision,
        ]));
    }

    public function test_approved_student_can_send_an_evaluation_without_duplicate_email(): void
    {
        $profile = $this->profile();
        $this->mock(StudentNotificationMailer::class)->shouldReceive('sendServiceEvaluationNotice')->once()
            ->andReturn(['status' => 'sent']);
        $this->assertTrue($this->choose($profile, 'send')->getData(true)['success']);
        $this->assertTrue($this->choose($profile, 'send')->getData(true)['success']);
        $this->assertSame(1, DB::table('eval_form')->count());
        $this->assertSame('student_approval', DB::table('eval_form')->value('source_type'));
        $this->assertSame('sent', DB::table('eval_form')->value('status'));
    }

    public function test_skip_does_not_create_an_evaluation(): void
    {
        $this->mock(StudentNotificationMailer::class)->shouldNotReceive('sendServiceEvaluationNotice');
        $this->assertTrue($this->choose($this->profile(), 'skip')->getData(true)['success']);
        $this->assertSame(0, DB::table('eval_form')->count());
    }

    public function test_pending_student_and_applicant_are_rejected(): void
    {
        $this->assertSame(409, $this->choose($this->profile('Pending/Conditional'), 'send')->getStatusCode());
        $this->assertSame(404, $this->choose($this->profile('Fully Cleared', 'Applicant'), 'send')->getStatusCode());
        $this->assertSame(0, DB::table('eval_form')->count());
    }

    public function test_student_endpoint_cannot_be_used_to_send_applicant_evaluations(): void
    {
        $this->expectException(ValidationException::class);
        app(WalkInController::class)->sendStudentApprovalEvaluation(Request::create('/', 'POST', [
            'source_type' => 'applicant_approval', 'source_id' => 1, 'decision' => 'send',
        ]));
    }

    public function test_record_menu_resends_the_same_unanswered_student_evaluation_and_ignores_posted_source(): void
    {
        $profile = $this->profile();
        $this->mock(StudentNotificationMailer::class)->shouldReceive('sendServiceEvaluationNotice')->twice()
            ->andReturn(['status' => 'sent']);
        $this->choose($profile, 'send');
        $originalId = DB::table('eval_form')->value('id');
        $response = app(WalkInController::class)->sendStudentRecordEvaluation(Request::create('/', 'POST', [
            'source_id' => 999, 'source_type' => 'employee_approval', 'decision' => 'skip',
        ]), $profile);
        $this->assertTrue($response->getData(true)['success']);
        $this->assertSame(1, DB::table('eval_form')->count());
        $this->assertSame($originalId, DB::table('eval_form')->value('id'));
        $this->assertSame('student_approval', DB::table('eval_form')->value('source_type'));
        $this->assertSame($profile->id, DB::table('eval_form')->value('source_id'));
    }

    public function test_record_menu_does_not_resend_submitted_evaluations_or_send_for_ineligible_students(): void
    {
        $profile = $this->profile();
        DB::table('eval_form')->insert([
            'source_type' => 'student_approval', 'source_id' => $profile->id,
            'user_id' => $profile->user_id, 'status' => 'submitted', 'submitted_at' => now(),
        ]);
        $this->mock(StudentNotificationMailer::class)->shouldNotReceive('sendServiceEvaluationNotice');
        $controller = app(WalkInController::class);
        $result = $controller->sendStudentRecordEvaluation(Request::create('/', 'POST'), $profile)->getData(true);
        $this->assertTrue($result['success']);
        $this->assertStringContainsString('already been submitted', $result['message']);
        $this->assertSame(409, $controller->sendStudentRecordEvaluation(Request::create('/', 'POST'), $this->profile('Pending/Conditional'))->getStatusCode());
        $this->assertSame(404, $controller->sendStudentRecordEvaluation(Request::create('/', 'POST'), $this->profile('Fully Cleared', 'Applicant'))->getStatusCode());
        $profile->pullout_status = HealthProfile::PULLOUT_COMPLETED;
        $profile->save();
        $this->assertSame(409, $controller->sendStudentRecordEvaluation(Request::create('/', 'POST'), $profile)->getStatusCode());
    }

    public function test_employee_record_menu_sends_and_resends_without_creating_duplicate_evaluations(): void
    {
        $user = User::forceCreate(['name' => 'Employee Test', 'user_type' => 'Faculty', 'email' => 'employee@example.com']);
        $profile = EmployeeHealthProfile::forceCreate([
            'user_id' => $user->id, 'submission_status' => 'approved', 'clearance_status' => 'Fully Cleared',
        ]);
        $this->mock(StudentNotificationMailer::class)->shouldReceive('sendServiceEvaluationNotice')->twice()->andReturn(['status' => 'sent']);
        $controller = app(WalkInController::class);
        $this->assertTrue($controller->sendEmployeeRecordEvaluation(Request::create('/', 'POST'), $profile)->getData(true)['success']);
        $this->assertTrue($controller->sendEmployeeRecordEvaluation(Request::create('/', 'POST'), $profile)->getData(true)['success']);
        $this->assertSame(1, DB::table('eval_form')->count());
        $this->assertSame('employee_approval', DB::table('eval_form')->value('source_type'));
        $profile->submission_status = 'pending';
        $profile->save();
        $this->assertSame(409, $controller->sendEmployeeRecordEvaluation(Request::create('/', 'POST'), $profile)->getStatusCode());
    }

    public function test_manual_send_routes_require_record_management_permission(): void
    {
        foreach (['admin.health_profile.send_evaluation', 'admin.employee_health_profile.send_evaluation'] as $name) {
            $route = app('router')->getRoutes()->getByName($name);
            $this->assertContains('module.permission:health_records.update_assessment', $route->gatherMiddleware());
            $this->assertContains('role:superadmin,admin', $route->gatherMiddleware());
        }
    }
}
