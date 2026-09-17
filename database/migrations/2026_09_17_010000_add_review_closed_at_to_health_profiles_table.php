<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasColumn('health_profiles', 'review_closed_at')) {
            Schema::table('health_profiles', function (Blueprint $table): void {
                $table->timestamp('review_closed_at')->nullable()->after('review_started_at');
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasColumn('health_profiles', 'review_closed_at')) {
            Schema::table('health_profiles', function (Blueprint $table): void {
                $table->dropColumn('review_closed_at');
            });
        }
    }
};
