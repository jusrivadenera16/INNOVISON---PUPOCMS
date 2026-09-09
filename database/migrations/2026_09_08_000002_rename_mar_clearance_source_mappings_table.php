<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('mar_clearance_source_mappings') && !Schema::hasTable('mar_mc_source')) {
            Schema::rename('mar_clearance_source_mappings', 'mar_mc_source');
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('mar_mc_source') && !Schema::hasTable('mar_clearance_source_mappings')) {
            Schema::rename('mar_mc_source', 'mar_clearance_source_mappings');
        }
    }
};
