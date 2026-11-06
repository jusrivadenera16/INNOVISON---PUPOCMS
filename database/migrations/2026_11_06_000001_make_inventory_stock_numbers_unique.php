<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('items') || !Schema::hasColumn('items', 'stock_number')) {
            return;
        }

        $rows = DB::table('items')
            ->select(['id', 'stock_number'])
            ->whereNotNull('stock_number')
            ->orderBy('id')
            ->get();
        $groups = [];

        foreach ($rows as $row) {
            $stockNumber = trim((string) $row->stock_number);
            if ($stockNumber === '') {
                DB::table('items')->where('id', $row->id)->update(['stock_number' => null]);
                continue;
            }

            if ($stockNumber !== $row->stock_number) {
                DB::table('items')->where('id', $row->id)->update(['stock_number' => $stockNumber]);
            }

            $groups[strtolower($stockNumber)][] = [
                'id' => $row->id,
                'stock_number' => $stockNumber,
            ];
        }

        foreach ($groups as $duplicates) {
            if (count($duplicates) < 2) {
                continue;
            }

            foreach (array_slice($duplicates, 1) as $duplicate) {
                $base = $duplicate['stock_number'];
                $suffix = 2;
                do {
                    $ending = '-' . $suffix++;
                    $candidate = substr($base, 0, 50 - strlen($ending)) . $ending;
                    $taken = DB::table('items')
                        ->where('id', '!=', $duplicate['id'])
                        ->whereRaw('LOWER(stock_number) = ?', [strtolower($candidate)])
                        ->exists();
                } while ($taken);

                DB::table('items')->where('id', $duplicate['id'])->update(['stock_number' => $candidate]);
            }
        }

        Schema::table('items', function (Blueprint $table): void {
            $table->unique('stock_number', 'items_stock_number_unique');
        });
    }

    public function down(): void
    {
        if (Schema::hasTable('items') && Schema::hasColumn('items', 'stock_number')) {
            Schema::table('items', function (Blueprint $table): void {
                $table->dropUnique('items_stock_number_unique');
            });
        }
    }
};
