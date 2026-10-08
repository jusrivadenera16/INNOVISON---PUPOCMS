<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        foreach ((array) config('health_data_encryption.fields', []) as $tableName => $fieldMap) {
            if (!Schema::hasTable($tableName)) {
                continue;
            }

            $columns = array_values(array_filter(
                $fieldMap,
                static fn (string $encryptedColumn, string $sourceColumn): bool => Schema::hasColumn($tableName, $sourceColumn)
                    && !Schema::hasColumn($tableName, $encryptedColumn),
                ARRAY_FILTER_USE_BOTH
            ));

            if ($columns === []) {
                continue;
            }

            Schema::table($tableName, function (Blueprint $table) use ($columns): void {
                foreach ($columns as $column) {
                    $table->text($column)->nullable();
                }
            });
        }
    }

    public function down(): void
    {
        foreach ((array) config('health_data_encryption.fields', []) as $tableName => $fieldMap) {
            if (!Schema::hasTable($tableName)) {
                continue;
            }

            $columns = array_values(array_filter(
                $fieldMap,
                static fn (string $encryptedColumn): bool => Schema::hasColumn($tableName, $encryptedColumn)
            ));

            if ($columns === []) {
                continue;
            }

            Schema::table($tableName, function (Blueprint $table) use ($columns): void {
                $table->dropColumn($columns);
            });
        }
    }
};
