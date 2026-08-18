<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasColumn('appointments', 'appointment_reminder_email_sent_at')) {
            Schema::table('appointments', function (Blueprint $table) {
                $table->timestamp('appointment_reminder_email_sent_at')
                    ->nullable()
                    ->after('approval_reminders');
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasColumn('appointments', 'appointment_reminder_email_sent_at')) {
            Schema::table('appointments', function (Blueprint $table) {
                $table->dropColumn('appointment_reminder_email_sent_at');
            });
        }
    }
};
