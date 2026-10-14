<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    private const NULLABLE_COLUMNS = [
        'health_profiles' => ['no_allergies'],
        'health_profile_emp' => [
            'previous_hospitalization',
            'operation_surgery',
            'cigarette_smoking',
            'alcohol_drinking',
            'traveled_abroad',
            'has_disability',
        ],
    ];

    public function up(): void
    {
        foreach (self::NULLABLE_COLUMNS as $tableName => $columns) {
            $columns = $this->availableColumns($tableName, $columns);
            if ($columns === []) {
                continue;
            }

            Schema::table($tableName, function (Blueprint $table) use ($columns): void {
                foreach ($columns as $column) {
                    $table->boolean($column)->nullable()->default(null)->change();
                }
            });
        }
    }

    public function down(): void
    {
        foreach (self::NULLABLE_COLUMNS as $tableName => $columns) {
            $columns = $this->availableColumns($tableName, $columns);
            foreach ($columns as $column) {
                if (DB::table($tableName)->whereNull($column)->exists()) {
                    throw new RuntimeException(
                        "Cannot restore {$tableName}.{$column} to NOT NULL while encrypted-only records contain NULL source values."
                    );
                }
            }
        }

        foreach (self::NULLABLE_COLUMNS as $tableName => $columns) {
            $columns = $this->availableColumns($tableName, $columns);
            if ($columns === []) {
                continue;
            }

            Schema::table($tableName, function (Blueprint $table) use ($columns): void {
                foreach ($columns as $column) {
                    $table->boolean($column)->nullable(false)->default(false)->change();
                }
            });
        }
    }

    private function availableColumns(string $tableName, array $columns): array
    {
        if (!Schema::hasTable($tableName)) {
            return [];
        }

        return array_values(array_filter(
            $columns,
            static fn (string $column): bool => Schema::hasColumn($tableName, $column)
        ));
    }
};
