<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('health_profiles', function (Blueprint $table) {
            if (!Schema::hasColumn('health_profiles', 'med_cert_findings_details')) {
                $table->text('med_cert_findings_details')->nullable()->after('med_cert_findings');
            }
        });
    }

    public function down(): void
    {
        Schema::table('health_profiles', function (Blueprint $table) {
            if (Schema::hasColumn('health_profiles', 'med_cert_findings_details')) {
                $table->dropColumn('med_cert_findings_details');
            }
        });
    }
};
