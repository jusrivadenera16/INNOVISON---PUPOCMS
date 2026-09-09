<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('mar_mc_source')) {
            return;
        }

        $now = now();

        DB::table('mar_mc_source')
            ->where('source_key', 'employee_health_form_category')
            ->where('is_active', true)
            ->update([
                'is_active' => false,
                'updated_at' => $now,
            ]);

        $mappings = DB::table('mar_mc_source')
            ->where('source_key', 'health_form_category')
            ->where('is_active', true)
            ->get([
                'mar_clearance_type_id',
                'mar_clearance_subcategory_id',
            ]);

        foreach ($mappings as $mapping) {
            if ($mapping->mar_clearance_subcategory_id) {
                if (Schema::hasTable('mar_clearance_subcategory_sources')) {
                    DB::table('mar_clearance_subcategory_sources')->insertOrIgnore([
                        'mar_clearance_subcategory_id' => $mapping->mar_clearance_subcategory_id,
                        'source' => 'employee_nurse_review',
                        'created_at' => $now,
                        'updated_at' => $now,
                    ]);
                }

                continue;
            }

            if ($mapping->mar_clearance_type_id
                && Schema::hasTable('mar_clearance_type_sources')) {
                DB::table('mar_clearance_type_sources')->insertOrIgnore([
                    'mar_clearance_type_id' => $mapping->mar_clearance_type_id,
                    'source' => 'employee_nurse_review',
                    'created_at' => $now,
                    'updated_at' => $now,
                ]);
            }
        }
    }

    public function down(): void
    {
        if (!Schema::hasTable('mar_mc_source')) {
            return;
        }

        DB::table('mar_mc_source')
            ->where('source_key', 'employee_health_form_category')
            ->update([
                'is_active' => true,
                'updated_at' => now(),
            ]);
    }
};
