<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('mar_mc_source')) {
            // This migration creates the table; remove only an incomplete table
            // left behind if the first migration attempt failed before recording it.
            Schema::dropIfExists('mar_mc_source');
        }

        Schema::create('mar_mc_source', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('mar_clearance_type_id')->nullable();
            $table->unsignedBigInteger('mar_clearance_subcategory_id')->nullable();
            $table->string('source_key', 80);
            $table->unsignedBigInteger('source_category_id')->nullable();
            $table->json('source_config')->nullable();
            $table->date('effective_from')->nullable();
            $table->date('effective_until')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->foreign('mar_clearance_type_id', 'mcsm_type_fk')
                ->references('id')
                ->on('mar_clearance_types')
                ->cascadeOnDelete();
            $table->foreign('mar_clearance_subcategory_id', 'mcsm_subcat_fk')
                ->references('id')
                ->on('mar_clearance_subcategories')
                ->cascadeOnDelete();
            $table->foreign('source_category_id', 'mcsm_category_fk')
                ->references('id')
                ->on('health_form_categories')
                ->nullOnDelete();

            $table->index(['mar_clearance_type_id', 'is_active'], 'mcsm_type_active_idx');
            $table->index(['mar_clearance_subcategory_id', 'is_active'], 'mcsm_subcat_active_idx');
            $table->index(['source_key', 'source_category_id', 'is_active'], 'mcsm_source_active_idx');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('mar_mc_source');
    }
};
