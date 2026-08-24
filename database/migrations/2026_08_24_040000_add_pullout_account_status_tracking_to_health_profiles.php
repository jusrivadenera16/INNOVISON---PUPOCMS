<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasColumn('health_profiles', 'pullout_previous_user_status')) {
            Schema::table('health_profiles', function (Blueprint $table) {
                $table->string('pullout_previous_user_status', 20)
                    ->nullable()
                    ->after('pullout_completed_at');
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasColumn('health_profiles', 'pullout_previous_user_status')) {
            Schema::table('health_profiles', function (Blueprint $table) {
                $table->dropColumn('pullout_previous_user_status');
            });
        }
    }
};
