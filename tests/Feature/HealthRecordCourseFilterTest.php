<?php

namespace Tests\Feature;

use App\Models\EmployeeHealthProfile;
use App\Models\HealthProfile;
use App\Services\HealthRecordCourseFilter;
use App\Services\StudentHealthFormRequestService;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class HealthRecordCourseFilterTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        config(['database.default' => 'sqlite', 'database.connections.sqlite.database' => ':memory:', 'health_data_encryption.enabled' => false]);
        DB::purge('sqlite');
        Schema::create('users', function (Blueprint $table): void {
            $table->id(); $table->string('course')->nullable();
        });
        foreach (['health_profiles', 'health_profile_emp'] as $name) {
            Schema::create($name, function (Blueprint $table): void {
                $table->id(); $table->unsignedBigInteger('user_id');
                $table->string('course_college')->nullable(); $table->string('office')->nullable();
                $table->string('pullout_status')->nullable(); $table->softDeletes();
            });
        }
    }

    private function record(string $table, ?string $course, ?string $userCourse = null, ?string $office = null): int
    {
        $user = DB::table('users')->insertGetId(['course' => $userCourse]);

        return DB::table($table)->insertGetId(['user_id' => $user, 'course_college' => $course, 'office' => $office]);
    }

    public function test_dropdown_deduplicates_courses_but_keeps_distinct_majors_and_departments(): void
    {
        foreach (['BSBA-MM', 'BSBAMM', 'Bachelor of Science in Business Administration - Marketing Management', StudentHealthFormRequestService::COURSES['BSBA-MM']] as $course) {
            $this->record('health_profiles', $course);
        }
        $this->record('health_profiles', null, 'BSBA-HRM - Bachelor of Science in Business Administration');
        $this->record('health_profile_emp', null, null, 'Admin');
        $this->record('health_profile_emp', null, null, 'Office of Student Affairs');
        $filter = new HealthRecordCourseFilter();
        $this->assertEqualsCanonicalizing([
            StudentHealthFormRequestService::COURSES['BSBA-MM'],
            StudentHealthFormRequestService::COURSES['BSBA-HRM'], 'Admin', 'Office of Student Affairs',
        ], $filter->options()->all());
    }

    public function test_student_filter_matches_all_saved_aliases_without_overriding_the_profile_course(): void
    {
        $expected = [];
        foreach (['BSBA-MM', ' BSBAMM ', 'Bachelor of Science in Business Administration - Marketing Management'] as $value) {
            $expected[] = $this->record('health_profiles', $value);
        }
        $expected[] = $this->record('health_profiles', null, 'bsba-mm');
        $this->record('health_profiles', 'BSBA-HRM', 'BSBA-MM');
        $before = DB::table('health_profiles')->get()->toJson();
        foreach (['BSBA-MM', StudentHealthFormRequestService::COURSES['BSBA-MM'], 'Bachelor of Science in Business Administration - Marketing Management'] as $selected) {
            $query = HealthProfile::query();
            (new HealthRecordCourseFilter())->apply($query, $selected);
            $this->assertEqualsCanonicalizing($expected, $query->pluck('id')->all());
        }
        $this->assertSame($before, DB::table('health_profiles')->get()->toJson());
    }

    public function test_employee_filter_keeps_profile_office_and_user_fallback_sources(): void
    {
        $ids = [
            $this->record('health_profile_emp', 'BSBA-MM'),
            $this->record('health_profile_emp', null, null, 'BSBAMM'),
            $this->record('health_profile_emp', null, 'Bachelor of Science in Business Administration - Marketing Management'),
        ];
        $admin = $this->record('health_profile_emp', null, null, 'Admin');
        $filter = new HealthRecordCourseFilter();
        $query = EmployeeHealthProfile::query();
        $filter->apply($query, StudentHealthFormRequestService::COURSES['BSBA-MM'], true);
        $this->assertEqualsCanonicalizing($ids, $query->pluck('id')->all());
        $query = EmployeeHealthProfile::query();
        $filter->apply($query, 'Admin', true);
        $this->assertSame([$admin], $query->pluck('id')->all());
    }

    public function test_unknown_values_are_preserved_and_filter_input_is_bound_not_interpolated(): void
    {
        $first = $this->record('health_profiles', 'Other Program');
        $second = $this->record('health_profiles', ' other   program ');
        $this->record('health_profiles', 'BSIT');
        $filter = new HealthRecordCourseFilter();
        $query = HealthProfile::query();
        $filter->apply($query, 'Other Program');
        $this->assertEqualsCanonicalizing([$first, $second], $query->pluck('id')->all());
        $query = HealthProfile::query();
        $filter->apply($query, "' OR 1=1 --");
        $this->assertSame(0, $query->count());
    }
}
