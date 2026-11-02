<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('admins') || Schema::hasColumn('admins', 'report_credentials')) {
            return;
        }

        Schema::table('admins', function (Blueprint $table) {
            if (Schema::hasColumn('admins', 'report_position')) {
                $table->string('report_credentials')->nullable()->after('report_position');
            } else {
                $table->string('report_credentials')->nullable();
            }
        });
    }

    public function down(): void
    {
        if (Schema::hasTable('admins') && Schema::hasColumn('admins', 'report_credentials')) {
            Schema::table('admins', function (Blueprint $table) {
                $table->dropColumn('report_credentials');
            });
        }
    }
};
