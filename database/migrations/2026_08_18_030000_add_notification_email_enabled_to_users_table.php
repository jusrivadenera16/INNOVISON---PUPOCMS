<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasColumn('users', 'notification_email_enabled')) {
            Schema::table('users', function (Blueprint $table) {
                $table->boolean('notification_email_enabled')
                    ->default(true)
                    ->after('notification_read_map');
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasColumn('users', 'notification_email_enabled')) {
            Schema::table('users', function (Blueprint $table) {
                $table->dropColumn('notification_email_enabled');
            });
        }
    }
};
