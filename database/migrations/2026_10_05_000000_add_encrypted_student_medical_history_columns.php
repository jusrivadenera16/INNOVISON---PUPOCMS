<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    private array $columns = [
        'medical_history_encrypted',
        'other_illness_encrypted',
        'food_allergies_encrypted',
        'medicine_allergies_encrypted',
        'other_med_allergies_encrypted',
        'vaccine_history_encrypted',
    ];

    public function up(): void
    {
        if (!Schema::hasTable('health_profiles')) {
            return;
        }

        $missingColumns = array_values(array_filter(
            $this->columns,
            static fn (string $column): bool => !Schema::hasColumn('health_profiles', $column)
        ));

        if ($missingColumns === []) {
            return;
        }

        Schema::table('health_profiles', function (Blueprint $table) use ($missingColumns): void {
            foreach ($missingColumns as $column) {
                $table->text($column)->nullable();
            }
        });
    }

    public function down(): void
    {
        if (!Schema::hasTable('health_profiles')) {
            return;
        }

        $existingColumns = array_values(array_filter(
            $this->columns,
            static fn (string $column): bool => Schema::hasColumn('health_profiles', $column)
        ));

        if ($existingColumns === []) {
            return;
        }

        Schema::table('health_profiles', function (Blueprint $table) use ($existingColumns): void {
            $table->dropColumn($existingColumns);
        });
    }
};
