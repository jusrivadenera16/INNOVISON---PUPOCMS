<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        foreach (['mar_clearance_types', 'mar_clearance_subcategories'] as $name) {
            Schema::table($name, function (Blueprint $table) {
                $table->softDeletes();
                $table->string('report_name', 160)->nullable();
            });
        }
    }

    public function down(): void
    {
        foreach (['mar_clearance_subcategories', 'mar_clearance_types'] as $name) {
            Schema::table($name, function (Blueprint $table) {
                $table->dropSoftDeletes();
                $table->dropColumn('report_name');
            });
        }
    }
};
