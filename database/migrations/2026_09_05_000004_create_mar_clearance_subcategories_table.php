<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('mar_clearance_subcategories')) {
            return;
        }

        Schema::create('mar_clearance_subcategories', function (Blueprint $table) {
            $table->id();
            $table->foreignId('mar_clearance_type_id')
                ->constrained('mar_clearance_types')
                ->cascadeOnDelete();
            $table->string('code', 120)->unique();
            $table->string('name', 160);
            $table->unsignedInteger('sort_order')->default(1);
            $table->timestamps();

            $table->unique(['mar_clearance_type_id', 'name'], 'mar_clearance_subcategory_name_unique');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('mar_clearance_subcategories');
    }
};
