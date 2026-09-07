<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('mar_clearance_subcategory_sources', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('mar_clearance_subcategory_id');
            $table->foreign('mar_clearance_subcategory_id', 'mcss_subcat_fk')
                ->references('id')
                ->on('mar_clearance_subcategories')
                ->cascadeOnDelete();
            $table->string('source', 40);
            $table->timestamps();

            $table->unique(['mar_clearance_subcategory_id', 'source'], 'mcss_subcat_source_unique');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('mar_clearance_subcategory_sources');
    }
};
