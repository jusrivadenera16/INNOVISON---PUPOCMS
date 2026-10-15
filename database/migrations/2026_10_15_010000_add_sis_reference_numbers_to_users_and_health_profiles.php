<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasColumn('users', 'sis_reference_number')) {
            Schema::table('users', function (Blueprint $table) {
                $table->string('sis_reference_number', 20)->nullable()->after('applicant_type');
                $table->unique('sis_reference_number', 'users_sis_reference_number_unique');
            });
        }

        if (Schema::hasTable('health_profiles') && !Schema::hasColumn('health_profiles', 'sis_reference_number')) {
            Schema::table('health_profiles', function (Blueprint $table) {
                $table->string('sis_reference_number', 20)->nullable()->after('reference_number');
                $table->unique('sis_reference_number', 'health_profiles_sis_reference_number_unique');
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('health_profiles') && Schema::hasColumn('health_profiles', 'sis_reference_number')) {
            Schema::table('health_profiles', function (Blueprint $table) {
                $table->dropUnique('health_profiles_sis_reference_number_unique');
                $table->dropColumn('sis_reference_number');
            });
        }

        if (Schema::hasColumn('users', 'sis_reference_number')) {
            Schema::table('users', function (Blueprint $table) {
                $table->dropUnique('users_sis_reference_number_unique');
                $table->dropColumn('sis_reference_number');
            });
        }
    }
};
