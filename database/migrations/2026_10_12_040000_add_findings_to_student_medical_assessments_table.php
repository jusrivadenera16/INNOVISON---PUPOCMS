<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('student_medical_assessments')) {
            return;
        }

        Schema::table('student_medical_assessments', function (Blueprint $table): void {
            if (!Schema::hasColumn('student_medical_assessments', 'findings')) {
                $table->text('findings')->nullable()->after('condition_or_illness');
            }

            if (!Schema::hasColumn('student_medical_assessments', 'findings_encrypted')) {
                $table->text('findings_encrypted')->nullable()->after('findings');
            }
        });
    }

    public function down(): void
    {
        if (!Schema::hasTable('student_medical_assessments')) {
            return;
        }

        Schema::table('student_medical_assessments', function (Blueprint $table): void {
            $columns = [];
            if (Schema::hasColumn('student_medical_assessments', 'findings_encrypted')) {
                $columns[] = 'findings_encrypted';
            }
            if (Schema::hasColumn('student_medical_assessments', 'findings')) {
                $columns[] = 'findings';
            }

            if ($columns !== []) {
                $table->dropColumn($columns);
            }
        });
    }
};
