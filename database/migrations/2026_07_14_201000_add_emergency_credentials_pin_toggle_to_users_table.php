<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('users')) {
            return;
        }

        Schema::table('users', function (Blueprint $table) {
            if (!Schema::hasColumn('users', 'api_pin_emergency_credentials_enabled')) {
                $table->boolean('api_pin_emergency_credentials_enabled')
                    ->default(false)
                    ->after('api_pin_token_action_enabled');
            }
        });
    }

    public function down(): void
    {
        if (!Schema::hasTable('users')) {
            return;
        }

        Schema::table('users', function (Blueprint $table) {
            if (Schema::hasColumn('users', 'api_pin_emergency_credentials_enabled')) {
                $table->dropColumn('api_pin_emergency_credentials_enabled');
            }
        });
    }
};
