<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('health_profile_emp', function (Blueprint $table) {
            if (!Schema::hasColumn('health_profile_emp', 'scars_findings')) {
                $table->string('scars_findings')->nullable()->after('skin_findings');
            }
        });
    }

    public function down(): void
    {
        Schema::table('health_profile_emp', function (Blueprint $table) {
            if (Schema::hasColumn('health_profile_emp', 'scars_findings')) {
                $table->dropColumn('scars_findings');
            }
        });
    }
};
