<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            if (!Schema::hasColumn('users', 'api_pin_page_enabled')) {
                $table->boolean('api_pin_page_enabled')->default(false)->after('api_pin_enabled');
            }

            if (!Schema::hasColumn('users', 'api_pin_token_action_enabled')) {
                $table->boolean('api_pin_token_action_enabled')->default(false)->after('api_pin_page_enabled');
            }
        });

        if (
            Schema::hasColumn('users', 'api_pin_enabled')
            && Schema::hasColumn('users', 'api_pin_page_enabled')
            && Schema::hasColumn('users', 'api_pin_token_action_enabled')
        ) {
            DB::table('users')->update([
                'api_pin_page_enabled' => DB::raw('api_pin_enabled'),
                'api_pin_token_action_enabled' => DB::raw('api_pin_enabled'),
            ]);
        }
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            if (Schema::hasColumn('users', 'api_pin_token_action_enabled')) {
                $table->dropColumn('api_pin_token_action_enabled');
            }

            if (Schema::hasColumn('users', 'api_pin_page_enabled')) {
                $table->dropColumn('api_pin_page_enabled');
            }
        });
    }
};
