<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('health_profile_staffs', function (Blueprint $table) {
            if (!Schema::hasColumn('health_profile_staffs', 'office')) {
                $table->string('office')->nullable()->after('form_date');
            }
        });

        Schema::table('health_profile_staffs', function (Blueprint $table) {
            $columns = array_filter([
                Schema::hasColumn('health_profile_staffs', 'address') ? 'address' : null,
                Schema::hasColumn('health_profile_staffs', 'college_department') ? 'college_department' : null,
                Schema::hasColumn('health_profile_staffs', 'course_school_year') ? 'course_school_year' : null,
            ]);

            if ($columns !== []) {
                $table->dropColumn($columns);
            }
        });
    }

    public function down(): void
    {
        Schema::table('health_profile_staffs', function (Blueprint $table) {
            if (!Schema::hasColumn('health_profile_staffs', 'address')) {
                $table->text('address')->nullable()->after('name');
            }

            if (!Schema::hasColumn('health_profile_staffs', 'college_department')) {
                $table->string('college_department')->nullable()->after('form_date');
            }

            if (!Schema::hasColumn('health_profile_staffs', 'course_school_year')) {
                $table->string('course_school_year')->nullable()->after('college_department');
            }
        });

        Schema::table('health_profile_staffs', function (Blueprint $table) {
            if (Schema::hasColumn('health_profile_staffs', 'office')) {
                $table->dropColumn('office');
            }
        });
    }
};
