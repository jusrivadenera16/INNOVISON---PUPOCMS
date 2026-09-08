<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('health_profiles', function (Blueprint $table) {
            if (!Schema::hasColumn('health_profiles', 'final_review_findings_status')) {
                $table->string('final_review_findings_status', 40)
                    ->nullable()
                    ->after('final_review_draft_data');
            }
        });
    }

    public function down(): void
    {
        Schema::table('health_profiles', function (Blueprint $table) {
            if (Schema::hasColumn('health_profiles', 'final_review_findings_status')) {
                $table->dropColumn('final_review_findings_status');
            }
        });
    }
};
