<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('health_profiles', function (Blueprint $table) {
            if (!Schema::hasColumn('health_profiles', 'digital_signature')) {
                $table->string('digital_signature')->nullable()->after('health_declaration');
            }
        });
    }

    public function down(): void
    {
        Schema::table('health_profiles', function (Blueprint $table) {
            if (Schema::hasColumn('health_profiles', 'digital_signature')) {
                $table->dropColumn('digital_signature');
            }
        });
    }
};
