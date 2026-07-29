<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('users') && !Schema::hasColumn('users', 'suffix_name')) {
            Schema::table('users', function (Blueprint $table) {
                $table->string('suffix_name')->nullable()->after('last_name');
            });
        }

        if (Schema::hasTable('health_profiles') && !Schema::hasColumn('health_profiles', 'suffix_name')) {
            Schema::table('health_profiles', function (Blueprint $table) {
                $table->string('suffix_name')->nullable()->after('student_number');
            });
        }

        if (Schema::hasTable('health_profile_emp') && !Schema::hasColumn('health_profile_emp', 'suffix_name')) {
            Schema::table('health_profile_emp', function (Blueprint $table) {
                $table->string('suffix_name')->nullable()->after('last_name');
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('health_profile_emp') && Schema::hasColumn('health_profile_emp', 'suffix_name')) {
            Schema::table('health_profile_emp', function (Blueprint $table) {
                $table->dropColumn('suffix_name');
            });
        }

        if (Schema::hasTable('health_profiles') && Schema::hasColumn('health_profiles', 'suffix_name')) {
            Schema::table('health_profiles', function (Blueprint $table) {
                $table->dropColumn('suffix_name');
            });
        }

        if (Schema::hasTable('users') && Schema::hasColumn('users', 'suffix_name')) {
            Schema::table('users', function (Blueprint $table) {
                $table->dropColumn('suffix_name');
            });
        }
    }
};
