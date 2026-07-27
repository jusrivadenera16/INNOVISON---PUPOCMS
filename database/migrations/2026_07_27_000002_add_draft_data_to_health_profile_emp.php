<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('health_profile_emp', function (Blueprint $table) {
            $table->json('draft_data')->nullable()->after('staff_health_form_pdf_path');
        });
    }

    public function down(): void
    {
        Schema::table('health_profile_emp', function (Blueprint $table) {
            $table->dropColumn('draft_data');
        });
    }
};
