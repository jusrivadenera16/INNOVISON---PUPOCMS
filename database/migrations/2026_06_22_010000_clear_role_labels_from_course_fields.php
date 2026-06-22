<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        $invalidCourseValues = [
            'admin',
            'admin - designee',
            'admin designee',
            'designee',
            'admin - clinic staff',
            'clinic staff',
            'student assistant',
            'faculty',
            'faculty / staff',
            'faculty/staff',
            'guest',
            'applicant',
            'regular',
            'superadmin',
            'super admin',
        ];

        if (Schema::hasTable('users') && Schema::hasColumn('users', 'course')) {
            DB::table('users')
                ->whereIn(DB::raw('LOWER(TRIM(course))'), $invalidCourseValues)
                ->update(['course' => null]);
        }

        if (Schema::hasTable('health_profiles') && Schema::hasColumn('health_profiles', 'course_college')) {
            DB::table('health_profiles')
                ->whereIn(DB::raw('LOWER(TRIM(course_college))'), $invalidCourseValues)
                ->update(['course_college' => null]);
        }
    }

    public function down(): void
    {
        // Invalid role labels intentionally remain cleared.
    }
};
