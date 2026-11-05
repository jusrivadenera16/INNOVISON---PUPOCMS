<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('consultation_supplies', function (Blueprint $table) {
            $table->id();
            $table->foreignId('consultation_id')->constrained('consultations')->cascadeOnDelete();
            $table->foreignId('item_id')->nullable()->constrained('items')->nullOnDelete();
            $table->string('supply');
            $table->decimal('quantity', 12, 4);
            $table->string('unit', 50);
            $table->timestamps();

            $table->index(['consultation_id', 'item_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('consultation_supplies');
    }
};
