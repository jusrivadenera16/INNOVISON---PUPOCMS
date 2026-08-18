<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasColumn('users', 'notification_system_enabled')) {
            Schema::table('users', function (Blueprint $table) {
                $table->boolean('notification_system_enabled')
                    ->default(true)
                    ->after('notification_email_enabled');
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasColumn('users', 'notification_system_enabled')) {
            Schema::table('users', function (Blueprint $table) {
                $table->dropColumn('notification_system_enabled');
            });
        }
    }
};
