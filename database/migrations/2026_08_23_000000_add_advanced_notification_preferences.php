<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('settings', function (Blueprint $table): void {
            $table->unsignedSmallInteger('pending_compliance_reminder_max_count')->default(3)->after('pending_compliance_reminder_days');
            $table->boolean('notification_quiet_hours_enabled')->default(false)->after('pending_compliance_reminder_max_count');
            $table->time('notification_quiet_hours_start')->default('20:00')->after('notification_quiet_hours_enabled');
            $table->time('notification_quiet_hours_end')->default('07:00')->after('notification_quiet_hours_start');
            $table->timestamp('workflow_preferences_saved_at')->nullable()->after('notification_quiet_hours_end');
            $table->string('workflow_preferences_saved_by')->nullable()->after('workflow_preferences_saved_at');
        });

        Schema::table('health_profiles', function (Blueprint $table): void {
            $table->unsignedSmallInteger('pending_compliance_reminder_count')->default(0)->after('pending_compliance_reminder_sent_at');
        });

        Schema::table('health_profile_emp', function (Blueprint $table): void {
            $table->unsignedSmallInteger('pending_compliance_reminder_count')->default(0)->after('pending_compliance_reminder_sent_at');
        });
    }

    public function down(): void
    {
        Schema::table('health_profile_emp', function (Blueprint $table): void {
            $table->dropColumn('pending_compliance_reminder_count');
        });

        Schema::table('health_profiles', function (Blueprint $table): void {
            $table->dropColumn('pending_compliance_reminder_count');
        });

        Schema::table('settings', function (Blueprint $table): void {
            $table->dropColumn([
                'pending_compliance_reminder_max_count',
                'notification_quiet_hours_enabled',
                'notification_quiet_hours_start',
                'notification_quiet_hours_end',
                'workflow_preferences_saved_at',
                'workflow_preferences_saved_by',
            ]);
        });
    }
};
