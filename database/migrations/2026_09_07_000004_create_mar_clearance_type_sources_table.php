<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('mar_clearance_type_sources')) {
            return;
        }

        Schema::create('mar_clearance_type_sources', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('mar_clearance_type_id');
            $table->string('source', 40);
            $table->timestamps();

            $table->foreign('mar_clearance_type_id', 'mcts_type_fk')
                ->references('id')
                ->on('mar_clearance_types')
                ->cascadeOnDelete();
            $table->unique(['mar_clearance_type_id', 'source'], 'mcts_type_source_unique');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('mar_clearance_type_sources');
    }
};
