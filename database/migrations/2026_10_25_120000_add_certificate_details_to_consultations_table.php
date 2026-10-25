<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasColumn('consultations', 'certificate_details')) {
            Schema::table('consultations', function (Blueprint $table) {
                $table->string('certificate_details', 255)->nullable()->after('certificate_type');
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasColumn('consultations', 'certificate_details')) {
            Schema::table('consultations', function (Blueprint $table) {
                $table->dropColumn('certificate_details');
            });
        }
    }
};
