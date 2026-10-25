<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('student_medical_assessments')) {
            return;
        }

        if (Schema::hasColumn('student_medical_assessments', 'health_profile_id')) {
            Schema::table('student_medical_assessments', function (Blueprint $table): void {
                $table->unsignedBigInteger('health_profile_id')->nullable()->change();
            });
        }

        if (!Schema::hasColumn('student_medical_assessments', 'employee_health_profile_id')) {
            Schema::table('student_medical_assessments', function (Blueprint $table): void {
                $table->unsignedBigInteger('employee_health_profile_id')->nullable()->after('health_profile_id');
                $table->index('employee_health_profile_id', 'student_medical_assessments_employee_profile_idx');
            });
        }
    }

    public function down(): void
    {
        if (!Schema::hasTable('student_medical_assessments')
            || !Schema::hasColumn('student_medical_assessments', 'employee_health_profile_id')) {
            return;
        }

        if (DB::table('student_medical_assessments')->whereNotNull('employee_health_profile_id')->exists()) {
            throw new RuntimeException('Employee medical assessment history exists; migrate or remove those entries before rolling this migration back.');
        }

        if (DB::table('student_medical_assessments')->whereNull('health_profile_id')->exists()) {
            throw new RuntimeException('Medical assessment entries have no health profile; restore their profile IDs before rolling this migration back.');
        }

        Schema::table('student_medical_assessments', function (Blueprint $table): void {
            $table->dropIndex('student_medical_assessments_employee_profile_idx');
            $table->dropColumn('employee_health_profile_id');
            $table->unsignedBigInteger('health_profile_id')->nullable(false)->change();
        });
    }
};
