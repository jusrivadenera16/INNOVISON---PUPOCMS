<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            if (!Schema::hasColumn('users', 'api_pin')) {
                $table->string('api_pin')->nullable()->after('password');
            }

            if (!Schema::hasColumn('users', 'api_pin_enabled')) {
                $table->boolean('api_pin_enabled')->default(false)->after('api_pin');
            }

            if (!Schema::hasColumn('users', 'api_pin_disabled')) {
                $table->boolean('api_pin_disabled')->default(false)->after('api_pin_enabled');
            }
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            if (Schema::hasColumn('users', 'api_pin_disabled')) {
                $table->dropColumn('api_pin_disabled');
            }

            if (Schema::hasColumn('users', 'api_pin_enabled')) {
                $table->dropColumn('api_pin_enabled');
            }

            if (Schema::hasColumn('users', 'api_pin')) {
                $table->dropColumn('api_pin');
            }
        });
    }
};
