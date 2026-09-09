<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('consultation_drafts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('patient_user_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('saved_by_user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('consultation_source', 40)->default('walkin');
            $table->string('appointment_number', 80)->nullable();
            $table->time('started_at')->nullable();
            $table->json('payload');
            $table->timestamps();

            $table->unique(['patient_user_id', 'consultation_source']);
            $table->index(['patient_user_id', 'updated_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('consultation_drafts');
    }
};
