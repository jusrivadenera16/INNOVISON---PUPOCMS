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

    private function profile(string $course, array $attributes = []): HealthProfile
    {
        $profile = new HealthProfile(array_merge([
            'course_college' => $course,
            'clearance_status' => 'Issued',
        ], $attributes));

        $profile->setRelation('user', new User(['course' => $course]));

        return $profile;
    }

    private function summaryRows($issuedRecords, $pendingRecords)
    {
        $controller = new ReportsController();
        $method = new ReflectionMethod($controller, 'healthFormsCourseSummaryRows');
        $method->setAccessible(true);

        return $method->invoke($controller, $issuedRecords, $pendingRecords);
    }
}
