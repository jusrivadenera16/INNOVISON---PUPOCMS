<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasColumn('health_profiles', 'final_review_draft_data')) {
            Schema::table('health_profiles', function (Blueprint $table) {
                $table->json('final_review_draft_data')->nullable()->after('puptas_sync_message');
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasColumn('health_profiles', 'final_review_draft_data')) {
            Schema::table('health_profiles', function (Blueprint $table) {
                $table->dropColumn('final_review_draft_data');
            });
        }
    }
};
