<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasColumn('settings', 'operating_days')) {
            Schema::table('settings', function (Blueprint $table) {
                $table->json('operating_days')->nullable()->after('close_time');
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasColumn('settings', 'operating_days')) {
            Schema::table('settings', function (Blueprint $table) {
                $table->dropColumn('operating_days');
            });
        }
    }
};
