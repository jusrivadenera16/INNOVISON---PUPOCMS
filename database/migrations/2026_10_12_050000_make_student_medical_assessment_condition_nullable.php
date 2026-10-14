<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('student_medical_assessments')
            || !Schema::hasColumn('student_medical_assessments', 'condition_or_illness')) {
            return;
        }

        Schema::table('student_medical_assessments', function (Blueprint $table): void {
            $table->text('condition_or_illness')->nullable()->change();
        });
    }

    public function down(): void
    {
        if (!Schema::hasTable('student_medical_assessments')
            || !Schema::hasColumn('student_medical_assessments', 'condition_or_illness')) {
            return;
        }

        if (DB::table('student_medical_assessments')->whereNull('condition_or_illness')->exists()) {
            throw new RuntimeException('Cannot make condition_or_illness required while assessment entries have no condition recorded.');
        }

        Schema::table('student_medical_assessments', function (Blueprint $table): void {
            $table->text('condition_or_illness')->nullable(false)->change();
        });
    }
};
