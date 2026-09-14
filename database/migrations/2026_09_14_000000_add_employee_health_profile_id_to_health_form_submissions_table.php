<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('health_form_submissions')
            || Schema::hasColumn('health_form_submissions', 'employee_health_profile_id')) {
            return;
        }

        Schema::table('health_form_submissions', function (Blueprint $table) {
            $table->unsignedBigInteger('employee_health_profile_id')
                ->nullable()
                ->after('health_profile_id');
            $table->index('employee_health_profile_id', 'health_form_submissions_employee_profile_index');
        });
    }

    public function down(): void
    {
        if (!Schema::hasTable('health_form_submissions')
            || !Schema::hasColumn('health_form_submissions', 'employee_health_profile_id')) {
            return;
        }

        Schema::table('health_form_submissions', function (Blueprint $table) {
            $table->dropIndex('health_form_submissions_employee_profile_index');
            $table->dropColumn('employee_health_profile_id');
        });
    }
};
