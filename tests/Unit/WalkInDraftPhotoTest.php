<?php

namespace Tests\Unit;

use App\Http\Controllers\WalkInController;
use App\Models\EmployeeHealthProfile;
use App\Models\HealthProfile;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Mockery;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Tests\TestCase;

class WalkInDraftPhotoTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        config()->set('database.default', 'sqlite');
        config()->set('database.connections.sqlite.database', ':memory:');
        config()->set('health_data_encryption.enabled', false);
        DB::purge('sqlite');
    }

    private function record(?string $employeePhoto, ?string $studentPhoto, string $type = 'Student'): EmployeeHealthProfile
    {
        $student = new HealthProfile;
        $student->setRawAttributes(['student_photo' => $studentPhoto]);
        $user = new User;
        $user->setRawAttributes(['user_type' => $type]);
        $user->setRelation('healthProfile', $student);
        $profile = new EmployeeHealthProfile;
        $profile->setRawAttributes(['student_photo' => $employeePhoto]);
        $profile->setRelation('user', $user);

        return $profile;
    }

    public function test_student_draft_uses_the_linked_student_photo_when_employee_photo_is_empty(): void
    {
        $profile = $this->record(null, 'health_profiles/photo.jpg');
        $controller = Mockery::mock(WalkInController::class)->makePartial();
        $controller->shouldReceive('showApplicantDocument')->once()
            ->with($profile->user->healthProfile, 'student_photo')->andReturn(response('student photo'));
        $controller->shouldNotReceive('showEmployeeDocument');

        $this->assertSame('student photo', $controller->showEmployeeDraftPhoto($profile)->getContent());
    }

    public function test_existing_employee_photo_is_preserved(): void
    {
        $profile = $this->record('employee/photo.jpg', 'student/photo.jpg');
        $controller = Mockery::mock(WalkInController::class)->makePartial();
        $controller->shouldReceive('showEmployeeDocument')->once()
            ->with($profile, 'student_photo')->andReturn(response('employee photo'));
        $controller->shouldNotReceive('showApplicantDocument');

        $this->assertSame('employee photo', $controller->showEmployeeDraftPhoto($profile)->getContent());
    }

    public function test_absent_photo_returns_not_found_for_the_initials_fallback(): void
    {
        try {
            app(WalkInController::class)->showEmployeeDraftPhoto($this->record(null, null));
            $this->fail('A missing photo must return 404.');
        } catch (HttpException $exception) {
            $this->assertSame(404, $exception->getStatusCode());
        }
    }
}
