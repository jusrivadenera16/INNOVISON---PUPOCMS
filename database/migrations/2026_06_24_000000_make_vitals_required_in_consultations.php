<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Add covid_positive_date column if it doesn't exist
        if (!Schema::hasColumn('consultations', 'covid_positive_date')) {
            Schema::table('consultations', function (Blueprint $table) {
                $table->date('covid_positive_date')->nullable()->after('covid_status');
            });
        }

        // Make vitals NOT NULL using raw SQL
        if (Schema::hasColumn('consultations', 'temperature')) {
            DB::statement('ALTER TABLE consultations MODIFY temperature DECIMAL(4, 2) NOT NULL');
        }

        if (Schema::hasColumn('consultations', 'blood_pressure')) {
            DB::statement('ALTER TABLE consultations MODIFY blood_pressure VARCHAR(20) NOT NULL');
        }

        if (Schema::hasColumn('consultations', 'pulse_rate')) {
            DB::statement('ALTER TABLE consultations MODIFY pulse_rate INT UNSIGNED NOT NULL');
        }

        if (Schema::hasColumn('consultations', 'respiratory_rate')) {
            DB::statement('ALTER TABLE consultations MODIFY respiratory_rate INT UNSIGNED NOT NULL');
        }
    }

    public function down(): void
    {
        // Revert vitals to nullable using raw SQL
        if (Schema::hasColumn('consultations', 'temperature')) {
            DB::statement('ALTER TABLE consultations MODIFY temperature DECIMAL(4, 2) NULL');
        }

        if (Schema::hasColumn('consultations', 'blood_pressure')) {
            DB::statement('ALTER TABLE consultations MODIFY blood_pressure VARCHAR(20) NULL');
        }

        if (Schema::hasColumn('consultations', 'pulse_rate')) {
            DB::statement('ALTER TABLE consultations MODIFY pulse_rate INT UNSIGNED NULL');
        }

        if (Schema::hasColumn('consultations', 'respiratory_rate')) {
            DB::statement('ALTER TABLE consultations MODIFY respiratory_rate INT UNSIGNED NULL');
        }

        // Drop covid_positive_date if it exists
        if (Schema::hasColumn('consultations', 'covid_positive_date')) {
            Schema::table('consultations', function (Blueprint $table) {
                $table->dropColumn('covid_positive_date');
            });
        }
    }
};
