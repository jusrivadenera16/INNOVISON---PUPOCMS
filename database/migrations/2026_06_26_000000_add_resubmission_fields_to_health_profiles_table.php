<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('health_profiles', function (Blueprint $table) {
            if (!Schema::hasColumn('health_profiles', 'resubmission_required_documents')) {
                $table->json('resubmission_required_documents')->nullable()->after('pending_reason');
            }

            if (!Schema::hasColumn('health_profiles', 'resubmission_requested_at')) {
                $table->timestamp('resubmission_requested_at')->nullable()->after('resubmission_required_documents');
            }

            if (!Schema::hasColumn('health_profiles', 'resubmitted_at')) {
                $table->timestamp('resubmitted_at')->nullable()->after('resubmission_requested_at');
            }
        });
    }

    public function down(): void
    {
        Schema::table('health_profiles', function (Blueprint $table) {
            foreach ([
                'resubmitted_at',
                'resubmission_requested_at',
                'resubmission_required_documents',
            ] as $column) {
                if (Schema::hasColumn('health_profiles', $column)) {
                    $table->dropColumn($column);
                }
            }
        });
    }
};
