<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('consultations', function (Blueprint $table) {
            // Make temperature NOT NULL (required)
            if (Schema::hasColumn('consultations', 'temperature')) {
                $table->decimal('temperature', 4, 2)->nullable(false)->change();
            }

            // Make blood_pressure NOT NULL (required)
            if (Schema::hasColumn('consultations', 'blood_pressure')) {
                $table->string('blood_pressure')->nullable(false)->change();
            }

            // Make pulse_rate NOT NULL (required)
            if (Schema::hasColumn('consultations', 'pulse_rate')) {
                $table->unsignedInteger('pulse_rate')->nullable(false)->change();
            }

            // Make respiratory_rate NOT NULL (required)
            if (Schema::hasColumn('consultations', 'respiratory_rate')) {
                $table->unsignedInteger('respiratory_rate')->nullable(false)->change();
            }

            // Add covid_positive_date column if it doesn't exist
            if (!Schema::hasColumn('consultations', 'covid_positive_date')) {
                $table->date('covid_positive_date')->nullable()->after('covid_status');
            }
        });
    }

    public function down(): void
    {
        Schema::table('consultations', function (Blueprint $table) {
            // Revert vitals to nullable
            if (Schema::hasColumn('consultations', 'temperature')) {
                $table->decimal('temperature', 4, 2)->nullable()->change();
            }
            if (Schema::hasColumn('consultations', 'blood_pressure')) {
                $table->string('blood_pressure')->nullable()->change();
            }
            if (Schema::hasColumn('consultations', 'pulse_rate')) {
                $table->unsignedInteger('pulse_rate')->nullable()->change();
            }
            if (Schema::hasColumn('consultations', 'respiratory_rate')) {
                $table->unsignedInteger('respiratory_rate')->nullable()->change();
            }

            // Drop covid_positive_date if it exists
            if (Schema::hasColumn('consultations', 'covid_positive_date')) {
                $table->dropColumn('covid_positive_date');
            }
        });
    }
};
