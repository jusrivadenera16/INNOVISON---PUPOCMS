<?php

namespace Tests\Feature;

use App\Http\Controllers\AdminController;
use App\Models\Admin;
use App\Models\HealthProfile;
use App\Models\SystemSetting;
use App\Models\User;
use App\Services\GuisisApiService;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\Config;
use Mockery;
use ReflectionMethod;
use Tests\TestCase;

class GuisisApplicantSyncTest extends TestCase
{
    use DatabaseTransactions;

    protected function setUp(): void
    {
        parent::setUp();
        Config::set('app.system_developer_email', 'guisis-sync-developer@example.test');
    }

    public function test_candidate_query_only_includes_pending_applicants_with_a_reference_number(): void
    {
        $applicant = $this->pendingApplicant('applicant-to-sync@example.test', 'APP-2026-12345');
        $enrolled = $this->pendingApplicant('already-enrolled@example.test', 'APP-2026-23456', '2023-13121-MN-0');
        $withoutReference = $this->pendingApplicant('no-reference@example.test', null);

        $queryMethod = new ReflectionMethod(AdminController::class, 'studentNumberSyncCandidateQuery');
        $candidateIds = $queryMethod->invoke(app(AdminController::class))
            ->pluck('users.id')
            ->map(static fn ($id): int => (int) $id)
            ->all();

        $this->assertContains((int) $applicant->id, $candidateIds);
        $this->assertNotContains((int) $enrolled->id, $candidateIds);
        $this->assertNotContains((int) $withoutReference->id, $candidateIds);
    }

    public function test_single_record_sync_updates_only_the_selected_applicant_and_preserves_batch_cursor(): void
    {
        $admin = User::factory()->create([
            'email' => 'guisis-sync-developer@example.test',
            'user_role' => User::ROLE_SUPERADMIN,
            'status' => 'active',
        ]);
        Admin::query()->create([
            'user_id' => $admin->id,
            'email' => $admin->email,
            'access_level' => 'superadmin',
        ]);
        $applicant = $this->pendingApplicant('selected-applicant@example.test', 'APP-2026-34567');
        $otherApplicant = $this->pendingApplicant('other-applicant@example.test', 'APP-2026-45678');
        $studentUuid = (string) $applicant->student_id;
        $studentNumber = '2026-00123-TG-0';

        SystemSetting::putValue('guisis_student_number_sync_cursor', '712');

        $remoteStudent = [
            'idpUuid' => $studentUuid,
            'email' => $applicant->email,
            'studentNumber' => $studentNumber,
            'yearLevel' => '1',
            'section' => '1',
        ];
        $guisis = Mockery::mock(GuisisApiService::class);
        $guisis->shouldReceive('listStudentsDetailed')
            ->once()
            ->with(['search' => $studentUuid, 'page' => 1, 'page_size' => 25])
            ->andReturn(['ok' => true, 'data' => ['data' => [$remoteStudent]]]);
        $guisis->shouldReceive('getStudentByEmailDetailed')
            ->once()
            ->with($applicant->email)
            ->andReturn(['ok' => true, 'data' => ['data' => [$remoteStudent]]]);
        $this->instance(GuisisApiService::class, $guisis);

        $response = $this->actingAs($admin, 'admin')->post(route('admin.api-testing.sync-student-numbers'), [
            'mode' => 'apply',
            'user_id' => $applicant->id,
        ]);

        $response->assertRedirect(route('admin.api-testing'));
        $response->assertSessionHas('student_number_sync_summary.synced', 1);
        $this->assertSame($studentNumber, $applicant->fresh()->student_number);
        $this->assertSame($studentNumber, $applicant->healthProfile->fresh()->student_number);
        $this->assertSame($studentUuid, $applicant->healthProfile->fresh()->student_id);
        $this->assertSame('student', $applicant->fresh()->clinicAccountTypeKey());
        $this->assertNull($otherApplicant->fresh()->student_number);
        $this->assertSame('712', SystemSetting::getValue('guisis_student_number_sync_cursor'));

        $queryMethod = new ReflectionMethod(AdminController::class, 'studentNumberSyncCandidateQuery');
        $candidateIds = $queryMethod->invoke(app(AdminController::class))
            ->pluck('users.id')
            ->map(static fn ($id): int => (int) $id)
            ->all();
        $this->assertNotContains((int) $applicant->id, $candidateIds);
        $this->assertContains((int) $otherApplicant->id, $candidateIds);
    }

    private function pendingApplicant(string $email, ?string $referenceNumber, ?string $studentNumber = null): User
    {
        $user = User::factory()->create([
            'email' => $email,
            'user_role' => User::ROLE_STUDENT,
            'user_type' => $studentNumber === null ? 'Applicant' : 'Student',
            'idp_role' => 'student',
            'applicant_type' => $studentNumber === null ? 'new' : null,
            'reference_number' => $referenceNumber,
            'student_number' => $studentNumber,
            'year' => null,
            'section' => null,
            'status' => 'active',
        ]);

        HealthProfile::create([
            'user_id' => $user->id,
            'reference_number' => $referenceNumber,
            'student_number' => $studentNumber,
        ]);

        return $user->fresh('healthProfile');
    }
}
