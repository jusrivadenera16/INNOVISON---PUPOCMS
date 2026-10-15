<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasColumn('users', 'admin_appointment_email_alerts')) {
            Schema::table('users', function (Blueprint $table) {
                $table->boolean('admin_appointment_email_alerts')
                    ->default(false)
                    ->after('notification_system_enabled');
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasColumn('users', 'admin_appointment_email_alerts')) {
            Schema::table('users', function (Blueprint $table) {
                $table->dropColumn('admin_appointment_email_alerts');
            });
        }
    }
};
