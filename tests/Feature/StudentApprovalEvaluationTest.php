<?php

namespace Tests\Feature;

use App\Http\Controllers\WalkInController;
use App\Models\HealthProfile;
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
}
