<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!$this->supportsPreciseAlterStatements()) {
            return;
        }

        if (Schema::hasTable('items')) {
            $this->alterDecimalColumns('items', [
                'quantity',
                'starting_stock',
                'consumed',
                'minimum_stock',
            ], 14, 6);
        }

        if (Schema::hasTable('inventory_movements')) {
            $this->alterDecimalColumns('inventory_movements', [
                'quantity',
                'stock_before',
                'stock_after',
            ], 14, 6);
        }
    }

    public function down(): void
    {
        if (!$this->supportsPreciseAlterStatements()) {
            return;
        }

        if (Schema::hasTable('items')) {
            $this->alterDecimalColumns('items', [
                'quantity',
                'starting_stock',
                'consumed',
                'minimum_stock',
            ], 10, 2);
        }

        if (Schema::hasTable('inventory_movements')) {
            $this->alterDecimalColumns('inventory_movements', [
                'quantity',
                'stock_before',
                'stock_after',
            ], 10, 2);
        }
    }

    private function supportsPreciseAlterStatements(): bool
    {
        return in_array(DB::getDriverName(), ['mysql', 'mariadb'], true);
    }

    private function alterDecimalColumns(string $table, array $columns, int $precision, int $scale): void
    {
        foreach ($columns as $column) {
            if (!Schema::hasColumn($table, $column)) {
                continue;
            }

            DB::statement(sprintf(
                'ALTER TABLE `%s` MODIFY `%s` DECIMAL(%d,%d) NOT NULL DEFAULT 0',
                $table,
                $column,
                $precision,
                $scale
            ));
        }
    }
};
