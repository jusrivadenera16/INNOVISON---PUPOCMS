<?php

namespace Tests\Unit;

use App\Services\HealthRecordCourseFilter;
use App\Services\StudentHealthFormRequestService;
use PHPUnit\Framework\TestCase;

class HealthRecordCourseNamesTest extends TestCase
{
    public function test_catalog_names_codes_and_legacy_labels_share_one_canonical_name(): void
    {
        $filter = new HealthRecordCourseFilter();
        foreach (StudentHealthFormRequestService::COURSES as $code => $name) {
            foreach ([$name, $code, str_replace('-', '', $code), $code.' - '.$name, strtolower($name)] as $variant) {
                self::assertSame($code, HealthRecordCourseFilter::courseCode($variant), $variant);
                self::assertSame($name, $filter->canonicalValue($variant), $variant);
            }
        }
        self::assertSame('BSBA-MM', HealthRecordCourseFilter::courseCode(
            'Bachelor of Science in Business Administration - Major in Marketing Management'
        ));
        self::assertSame('BSBA-MM', HealthRecordCourseFilter::courseCode(
            'Bachelor of Science in Business Administration - Marketing Management'
        ));
        self::assertSame('BSBA-HRM', HealthRecordCourseFilter::courseCode(
            'BSBA-HRM - Bachelor of Science in Business Administration'
        ));
    }

    public function test_distinct_majors_and_ambiguous_names_are_not_merged(): void
    {
        $filter = new HealthRecordCourseFilter();
        self::assertNotSame($filter->canonicalValue('BSBA-MM'), $filter->canonicalValue('BSBA-HRM'));
        self::assertSame('', HealthRecordCourseFilter::courseCode('Bachelor of Science in Business Administration'));
        self::assertSame('', HealthRecordCourseFilter::courseCode('Information Technology Department'));
        self::assertSame('Admin', $filter->canonicalValue(' Admin '));
        self::assertSame('Office of Student Affairs', $filter->canonicalValue('Office of Student Affairs'));
    }
}
