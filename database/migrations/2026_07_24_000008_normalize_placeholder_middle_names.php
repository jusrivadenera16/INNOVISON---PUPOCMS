<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        foreach (['users', 'health_profile_staffs'] as $table) {
            if (!Schema::hasTable($table) || !Schema::hasColumn($table, 'middle_name')) {
                continue;
            }

            DB::table($table)
                ->whereIn(DB::raw('UPPER(TRIM(middle_name))'), ['N/A', 'NA', 'NONE'])
                ->update(['middle_name' => null]);

            if (!Schema::hasColumn($table, 'name')) {
                continue;
            }

            DB::table($table)
                ->select(['id', 'first_name', 'middle_name', 'last_name', 'name'])
                ->where('name', 'like', '%N/A%')
                ->orderBy('id')
                ->chunkById(100, function ($records) use ($table) {
                    foreach ($records as $record) {
                        $name = trim(preg_replace('/\s+/', ' ', implode(' ', array_filter([
                            trim((string) ($record->first_name ?? '')),
                            trim((string) ($record->middle_name ?? '')),
                            trim((string) ($record->last_name ?? '')),
                        ]))));

                        if ($name === '') {
                            $name = trim(preg_replace('/\s+/', ' ', str_ireplace([' N/A ', ' N/A', 'N/A '], ' ', (string) $record->name)));
                        }

                        DB::table($table)
                            ->where('id', $record->id)
                            ->update(['name' => $name]);
                    }
                });
        }
    }

    public function down(): void
    {
        // Placeholder cleanup is intentionally not reversible.
    }
};
