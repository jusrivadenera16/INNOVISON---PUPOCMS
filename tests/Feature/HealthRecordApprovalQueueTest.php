<?php

namespace Tests\Feature;

use App\Models\{EmployeeHealthProfile, HealthFormSubmission, HealthProfile, User};
use App\Services\HealthRecordApprovalQueue;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\{DB, Schema};
use Tests\TestCase;

class HealthRecordApprovalQueueTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        config(['database.default' => 'sqlite', 'database.connections.sqlite.database' => ':memory:', 'health_data_encryption.enabled' => false]);
        DB::purge('sqlite');
        Schema::create('users', function (Blueprint $table) { $table->id(); $table->string('user_type'); });
        Schema::create('health_form_submissions', function (Blueprint $table) {
            $table->id();
            foreach (['user_id', 'health_profile_id', 'employee_health_profile_id'] as $key) $table->unsignedBigInteger($key)->nullable();
            $table->string('status'); $table->timestamp('submitted_at')->nullable(); $table->timestamp('approved_at')->nullable(); $table->timestamps();
        });
    }

    private function record(string $source, int $id, int $userId, string $account, array $values = [])
    {
        DB::table('users')->insertOrIgnore(['id' => $userId, 'user_type' => $account]);
        $user = (new User)->forceFill(['id' => $userId, 'user_type' => $account]);
        $user->setRelation('healthProfile', null);
        $record = $source === 'employee' ? new EmployeeHealthProfile : new HealthProfile;
        $record->forceFill(array_merge(['id' => $id, 'user_id' => $userId, 'clearance_status' => 'For Verification',
            'record_source' => $source, 'record_key' => $source . ':' . $id], $values));
        $record->setRelation('user', $user);
        return $record;
    }

    public function test_blank_shadow_profile_is_excluded_and_real_employee_submission_is_preserved(): void
    {
        $shadow = $this->record('health', 7, 1, 'faculty');
        $real = $this->record('employee', 7, 1, 'faculty', ['health_declaration' => 'private/declaration.pdf', 'past_medical_history' => ['Asthma']]);
        HealthFormSubmission::create(['user_id' => 1, 'employee_health_profile_id' => 7, 'status' => 'submitted', 'submitted_at' => now()]);
        app(HealthRecordApprovalQueue::class)->markEligibility(collect([$shadow, $real]));
        $this->assertFalse($shadow->pending_approval_eligible);
        $this->assertTrue($real->pending_approval_eligible);
        $this->assertSame('private/declaration.pdf', $real->health_declaration);
        $this->assertSame(['Asthma'], $real->past_medical_history);
        $this->assertDatabaseCount('health_form_submissions', 1);
    }

    public function test_one_request_linked_to_both_sources_uses_the_account_source_regardless_of_order(): void
    {
        foreach (['faculty', 'employee', 'dependent', 'student', 'applicant'] as $index => $account) {
            $health = $this->record('health', 100 + $index, $index + 1, $account);
            $employee = $this->record('employee', 200 + $index, $index + 1, $account);
            HealthFormSubmission::create(['user_id' => $index + 1, 'health_profile_id' => $health->id, 'employee_health_profile_id' => $employee->id, 'status' => 'submitted', 'submitted_at' => now()]);
            foreach ([[$employee, $health], [$health, $employee]] as $order) {
                app(HealthRecordApprovalQueue::class)->markEligibility(collect($order));
                $this->assertSame(in_array($account, ['student', 'applicant']), $health->pending_approval_eligible);
                $this->assertSame(!in_array($account, ['student', 'applicant']), $employee->pending_approval_eligible);
            }
        }
    }

    public function test_different_requests_and_different_users_are_not_collapsed_by_identity_text(): void
    {
        $first = $this->record('health', 1, 1, 'faculty');
        $second = $this->record('employee', 1, 1, 'faculty');
        $third = $this->record('employee', 2, 2, 'faculty');
        foreach ([$first, $second, $third] as $record) {
            HealthFormSubmission::create(['user_id' => $record->user_id,
                $record instanceof EmployeeHealthProfile ? 'employee_health_profile_id' : 'health_profile_id' => $record->id,
                'status' => 'submitted', 'submitted_at' => now()]);
        }
        $records = collect([$first, $second, $third]);
        app(HealthRecordApprovalQueue::class)->markEligibility($records);
        $this->assertCount(3, $records->where('pending_approval_eligible', true));
    }

    public function test_modern_closed_requested_or_unsubmitted_records_do_not_fall_back_to_old_uploads(): void
    {
        foreach (['requested', 'cancelled', 'approved', 'rejected', 'draft', 'submitted'] as $index => $status) {
            $record = $this->record('employee', $index + 1, 1, 'faculty', ['health_declaration' => 'old/declaration.pdf']);
            HealthFormSubmission::create(['user_id' => 1, 'employee_health_profile_id' => $record->id, 'status' => $status,
                'submitted_at' => $status === 'submitted' ? null : now()]);
            app(HealthRecordApprovalQueue::class)->markEligibility(collect([$record]));
            $this->assertFalse($record->pending_approval_eligible, $status);
        }
        $record = $this->record('health', 77, 1, 'student');
        HealthFormSubmission::create(['user_id' => 1, 'health_profile_id' => 77, 'status' => 'submitted', 'submitted_at' => now()]);
        HealthFormSubmission::create(['user_id' => 1, 'health_profile_id' => 77, 'status' => 'cancelled']);
        app(HealthRecordApprovalQueue::class)->markEligibility(collect([$record]));
        $this->assertFalse($record->pending_approval_eligible);
    }

    public function test_legacy_uploads_are_preserved_for_all_patient_types_but_empty_placeholders_are_not(): void
    {
        foreach ([['health', 'student'], ['health', 'applicant'], ['employee', 'faculty'], ['employee', 'dependent']] as $index => [$source, $account]) {
            $record = $this->record($source, $index + 1, $index + 1, $account);
            app(HealthRecordApprovalQueue::class)->markEligibility(collect([$record]));
            $this->assertFalse($record->pending_approval_eligible);
            $record->setAttribute('student_photo', 'legacy/photo.jpg');
            app(HealthRecordApprovalQueue::class)->markEligibility(collect([$record]));
            $this->assertTrue($record->pending_approval_eligible);
        }
    }

    public function test_active_under_review_is_allowed_and_mismatched_submission_owner_is_not(): void
    {
        $record = $this->record('employee', 4, 1, 'faculty');
        DB::table('users')->insert(['id' => 2, 'user_type' => 'faculty']);
        $submission = HealthFormSubmission::create(['user_id' => 1, 'employee_health_profile_id' => 4, 'status' => 'under_review', 'submitted_at' => now()]);
        app(HealthRecordApprovalQueue::class)->markEligibility(collect([$record]));
        $this->assertTrue($record->pending_approval_eligible);
        $submission->update(['user_id' => 2]);
        app(HealthRecordApprovalQueue::class)->markEligibility(collect([$record]));
        $this->assertFalse($record->pending_approval_eligible);
    }

    public function test_explicit_clearance_revocation_preserves_the_existing_revalidation_queue(): void
    {
        $record = $this->record('health', 80, 1, 'student', ['clearance_status' => 'Pending', 'clearance_revoked_at' => now()]);
        HealthFormSubmission::create(['user_id' => 1, 'health_profile_id' => 80, 'status' => 'approved', 'submitted_at' => now()->subDay(), 'approved_at' => now()->subDay()]);
        app(HealthRecordApprovalQueue::class)->markEligibility(collect([$record]));
        $this->assertTrue($record->pending_approval_eligible);
        $record->setAttribute('clearance_revoked_at', null);
        app(HealthRecordApprovalQueue::class)->markEligibility(collect([$record]));
        $this->assertFalse($record->pending_approval_eligible);
    }
}
