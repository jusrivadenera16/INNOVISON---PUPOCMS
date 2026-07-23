<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('health_form_submissions')) {
            return;
        }

        Schema::create('health_form_submissions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('health_profile_id')->nullable()->constrained('health_profiles')->nullOnDelete();
            $table->string('category')->default('General');
            $table->string('school_year')->nullable();
            $table->string('status')->default('submitted');
            $table->string('pdf_path')->nullable();
            $table->foreignId('requested_by_user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('requested_at')->nullable();
            $table->timestamp('submitted_at')->nullable();
            $table->timestamp('approved_at')->nullable();
            $table->text('remarks')->nullable();
            $table->timestamps();

            $table->index(['user_id', 'status']);
            $table->index(['health_profile_id', 'submitted_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('health_form_submissions');
    }
};
