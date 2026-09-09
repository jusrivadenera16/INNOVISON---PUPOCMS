<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasColumn('mar_clearance_types', 'allow_direct_use')) {
            Schema::table('mar_clearance_types', function (Blueprint $table) {
                $table->boolean('allow_direct_use')->default(false)->after('is_active');
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasColumn('mar_clearance_types', 'allow_direct_use')) {
            Schema::table('mar_clearance_types', function (Blueprint $table) {
                $table->dropColumn('allow_direct_use');
            });
        }
    }
};
