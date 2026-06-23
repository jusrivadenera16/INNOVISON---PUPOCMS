<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('health_profiles', function (Blueprint $table) {
            if (!Schema::hasColumn('health_profiles', 'pulse_rate')) {
                $table->unsignedInteger('pulse_rate')->nullable()->after('blood_pressure');
            }

            if (!Schema::hasColumn('health_profiles', 'covid_positive_date')) {
                $table->date('covid_positive_date')->nullable()->after('covid_positive');
            }
        });
    }

    public function down(): void
    {
        Schema::table('health_profiles', function (Blueprint $table) {
            if (Schema::hasColumn('health_profiles', 'pulse_rate')) {
                $table->dropColumn('pulse_rate');
            }

            if (Schema::hasColumn('health_profiles', 'covid_positive_date')) {
                $table->dropColumn('covid_positive_date');
            }
        });
    }
};
