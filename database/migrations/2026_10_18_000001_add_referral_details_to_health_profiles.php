<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('health_profiles') || Schema::hasColumn('health_profiles', 'referral_details')) {
            return;
        }

        Schema::table('health_profiles', function (Blueprint $table): void {
            $table->text('referral_details')->nullable();
        });
    }

    public function down(): void
    {
        if (!Schema::hasTable('health_profiles') || !Schema::hasColumn('health_profiles', 'referral_details')) {
            return;
        }

        Schema::table('health_profiles', function (Blueprint $table): void {
            $table->dropColumn('referral_details');
        });
    }
};
