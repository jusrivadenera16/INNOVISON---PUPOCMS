<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('health_profiles', function (Blueprint $table) {
            if (!Schema::hasColumn('health_profiles', 'review_started_at')) {
                $table->timestamp('review_started_at')->nullable()->after('resubmitted_at');
            }

            if (!Schema::hasColumn('health_profiles', 'review_started_by_user_id')) {
                $table->foreignId('review_started_by_user_id')
                    ->nullable()
                    ->after('review_started_at')
                    ->constrained('users')
                    ->nullOnDelete();
            }
        });
    }

    public function down(): void
    {
        Schema::table('health_profiles', function (Blueprint $table) {
            if (Schema::hasColumn('health_profiles', 'review_started_by_user_id')) {
                $table->dropConstrainedForeignId('review_started_by_user_id');
            }

            if (Schema::hasColumn('health_profiles', 'review_started_at')) {
                $table->dropColumn('review_started_at');
            }
        });
    }
};
