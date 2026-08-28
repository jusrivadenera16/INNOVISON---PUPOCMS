<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('health_profile_correction_requests')) {
            return;
        }

        Schema::create('health_profile_correction_requests', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('health_profile_id')->nullable()->constrained('health_profiles')->nullOnDelete();
            $table->unsignedBigInteger('employee_health_profile_id')->nullable();
            $table->foreignId('health_form_submission_id')->nullable();
            $table->foreign('health_form_submission_id', 'hpcr_submission_fk')
                ->references('id')
                ->on('health_form_submissions')
                ->nullOnDelete();
            $table->string('profile_kind', 30)->default('student');
            $table->string('type', 40);
            $table->string('status', 40)->default('pending');
            $table->json('required_documents')->nullable();
            $table->text('admin_note')->nullable();
            $table->foreignId('requested_by_user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('requested_at')->nullable();
            $table->timestamp('submitted_at')->nullable();
            $table->timestamp('reviewed_at')->nullable();
            $table->json('metadata')->nullable();
            $table->timestamps();

            $table->index(['user_id', 'status']);
            $table->index(['health_profile_id', 'status']);
            $table->index(['employee_health_profile_id', 'status'], 'hpcr_employee_status_idx');
            $table->index(['type', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('health_profile_correction_requests');
    }
};
