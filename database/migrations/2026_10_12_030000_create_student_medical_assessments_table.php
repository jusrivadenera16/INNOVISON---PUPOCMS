<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('health_profiles')
            && !Schema::hasColumn('health_profiles', 'clearance_revoked_at')) {
            Schema::table('health_profiles', function (Blueprint $table): void {
                $table->timestamp('clearance_revoked_at')->nullable()->after('clearance_status');
            });
        }

        if (!Schema::hasTable('student_medical_assessments')) {
            Schema::create('student_medical_assessments', function (Blueprint $table): void {
                $table->id();
                $table->unsignedBigInteger('health_profile_id')->index();
                $table->unsignedBigInteger('recorded_by_user_id')->nullable()->index();
                $table->text('condition_or_illness');
                $table->text('condition_or_illness_encrypted')->nullable();
                $table->text('remarks')->nullable();
                $table->text('remarks_encrypted')->nullable();
                $table->text('reason')->nullable();
                $table->text('reason_encrypted')->nullable();
                $table->timestamps();
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('student_medical_assessments');

        if (Schema::hasTable('health_profiles')
            && Schema::hasColumn('health_profiles', 'clearance_revoked_at')) {
            Schema::table('health_profiles', function (Blueprint $table): void {
                $table->dropColumn('clearance_revoked_at');
            });
        }
    }
};
