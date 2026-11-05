<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('health_form_submissions', function (Blueprint $table) {
            $table->foreignId('approved_by_user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('status_changed_by_user_id')->nullable()->constrained('users')->nullOnDelete();
        });

        Schema::table('health_profile_correction_requests', function (Blueprint $table) {
            $table->foreignId('reviewed_by_user_id')->nullable()->constrained('users')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('health_profile_correction_requests', function (Blueprint $table) {
            $table->dropConstrainedForeignId('reviewed_by_user_id');
        });

        Schema::table('health_form_submissions', function (Blueprint $table) {
            $table->dropConstrainedForeignId('status_changed_by_user_id');
            $table->dropConstrainedForeignId('approved_by_user_id');
        });
    }
};
