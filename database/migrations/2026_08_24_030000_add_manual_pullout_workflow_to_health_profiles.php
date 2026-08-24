<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('health_profiles', function (Blueprint $table) {
            $table->string('pullout_status', 30)->nullable()->index();
            $table->string('pullout_reason')->nullable();
            $table->text('pullout_request_remarks')->nullable();
            $table->unsignedBigInteger('pullout_requested_by_user_id')->nullable();
            $table->timestamp('pullout_requested_at')->nullable();

            $table->string('pullout_reference', 120)->nullable();
            $table->text('pullout_completion_remarks')->nullable();
            $table->unsignedBigInteger('pullout_completed_by_user_id')->nullable();
            $table->timestamp('pullout_completed_at')->nullable();

            $table->text('pullout_restore_reason')->nullable();
            $table->unsignedBigInteger('pullout_restored_by_user_id')->nullable();
            $table->timestamp('pullout_restored_at')->nullable();
        });
    }

    public function down(): void
    {
        Schema::table('health_profiles', function (Blueprint $table) {
            $table->dropIndex(['pullout_status']);
            $table->dropColumn([
                'pullout_status',
                'pullout_reason',
                'pullout_request_remarks',
                'pullout_requested_by_user_id',
                'pullout_requested_at',
                'pullout_reference',
                'pullout_completion_remarks',
                'pullout_completed_by_user_id',
                'pullout_completed_at',
                'pullout_restore_reason',
                'pullout_restored_by_user_id',
                'pullout_restored_at',
            ]);
        });
    }
};
