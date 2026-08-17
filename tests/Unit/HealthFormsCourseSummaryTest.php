<?php

namespace Tests\Unit;

use App\Http\Controllers\ReportsController;
use App\Models\HealthProfile;
use App\Models\User;
use PHPUnit\Framework\TestCase;
use ReflectionMethod;

class HealthFormsCourseSummaryTest extends TestCase
{
    public function test_it_summarizes_issued_and_for_approval_records_by_course(): void
    {
        $issuedWithCondition = $this->profile('Bachelor of Science in Information Technology', [
            'clearance_status' => 'Issued',
            'has_illness' => 'Yes',
        ]);

        $issuedNoCondition = $this->profile('Bachelor of Science in Information Technology', [
            'clearance_status' => 'Fully Cleared',
        ]);

        $pendingOnlyCourse = $this->profile('Bachelor of Science in Psychology', [
            'clearance_status' => 'For Verification',
        ]);

        $rows = $this->summaryRows(
            collect([$issuedWithCondition, $issuedNoCondition]),
            collect([$pendingOnlyCourse])
        );

        $this->assertCount(2, $rows);

        $it = $rows->firstWhere('course', 'Bachelor of Science in Information Technology');
        $this->assertSame(2, $it->issued_count);
        $this->assertSame(1, $it->with_condition_count);
        $this->assertSame(1, $it->no_condition_count);
        $this->assertSame(0, $it->for_approval_count);

        $psychology = $rows->firstWhere('course', 'Bachelor of Science in Psychology');
        $this->assertSame(0, $psychology->issued_count);
        $this->assertSame(0, $psychology->with_condition_count);
        $this->assertSame(0, $psychology->no_condition_count);
        $this->assertSame(1, $psychology->for_approval_count);
    }

    public function test_it_sorts_health_form_records_by_patient_last_name(): void
    {
        $records = $this->sortByLastName(collect([
            $this->profile('Bachelor of Science in Information Technology', [
                'reference_number' => '2026-0208-1884',
                'user' => [
                    'first_name' => 'Hana Catig',
                    'last_name' => 'Valle',
                    'name' => 'Hana Catig Valle',
                ],
            ]),
            $this->profile('Bachelor of Science in Information Technology', [
                'reference_number' => '2026-0205-0412',
                'user' => [
                    'first_name' => 'Joege Catayen',
                    'last_name' => 'Nono',
                    'name' => 'Joege Catayen Nono',
                ],
            ]),
            $this->profile('Bachelor of Science in Information Technology', [
                'reference_number' => '2026-0210-3745',
                'user' => [
                    'first_name' => 'Eunice Allison Abarquez',
                    'last_name' => 'Abdon',
                    'name' => 'Eunice Allison Abarquez Abdon',
                ],
            ]),
        ]));

        $this->assertSame([
            'Eunice Allison Abarquez Abdon',
            'Joege Catayen Nono',
            'Hana Catig Valle',
        ], $records->map(fn (HealthProfile $record) => $record->user->name)->all());
    }

    private function profile(string $course, array $attributes = []): HealthProfile
    {
        $userAttributes = $attributes['user'] ?? ['course' => $course];
        unset($attributes['user']);

        $profile = new HealthProfile(array_merge([
            'course_college' => $course,
            'clearance_status' => 'Issued',
        ], $attributes));

        $profile->setRelation('user', new User(array_merge(['course' => $course], $userAttributes)));

        return $profile;
    }

    private function summaryRows($issuedRecords, $pendingRecords)
    {
        $controller = new ReportsController();
        $method = new ReflectionMethod($controller, 'healthFormsCourseSummaryRows');
        $method->setAccessible(true);

        return $method->invoke($controller, $issuedRecords, $pendingRecords);
    }

    private function sortByLastName($records)
    {
        $controller = new ReportsController();
        $method = new ReflectionMethod($controller, 'sortHealthFormsByPatientLastName');
        $method->setAccessible(true);

        return $method->invoke($controller, $records);
    }
}
