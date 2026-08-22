<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddNotificationWorkflowSettings extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('settings') && !Schema::hasColumn('settings', 'pending_compliance_reminder_days')) {
            Schema::table('settings', function (Blueprint $table): void {
                $table->unsignedSmallInteger('pending_compliance_reminder_days')
                    ->default(7)
                    ->after('appointment_reminder_hours');
            });
        }

        if (Schema::hasTable('health_profiles') && !Schema::hasColumn('health_profiles', 'pending_compliance_reminder_sent_at')) {
            Schema::table('health_profiles', function (Blueprint $table): void {
                $table->timestamp('pending_compliance_reminder_sent_at')
                    ->nullable()
                    ->after('resubmission_requested_at');
            });
        }

        if (Schema::hasTable('health_profile_emp') && !Schema::hasColumn('health_profile_emp', 'pending_compliance_reminder_sent_at')) {
            Schema::table('health_profile_emp', function (Blueprint $table): void {
                $table->timestamp('pending_compliance_reminder_sent_at')
                    ->nullable()
                    ->after('resubmission_requested_at');
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('health_profile_emp') && Schema::hasColumn('health_profile_emp', 'pending_compliance_reminder_sent_at')) {
            Schema::table('health_profile_emp', function (Blueprint $table): void {
                $table->dropColumn('pending_compliance_reminder_sent_at');
            });
        }

        if (Schema::hasTable('health_profiles') && Schema::hasColumn('health_profiles', 'pending_compliance_reminder_sent_at')) {
            Schema::table('health_profiles', function (Blueprint $table): void {
                $table->dropColumn('pending_compliance_reminder_sent_at');
            });
        }

        if (Schema::hasTable('settings') && Schema::hasColumn('settings', 'pending_compliance_reminder_days')) {
            Schema::table('settings', function (Blueprint $table): void {
                $table->dropColumn('pending_compliance_reminder_days');
            });
        }
    }
}
