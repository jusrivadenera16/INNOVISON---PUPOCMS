<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('announcements') || Schema::hasColumn('announcements', 'show_on_landing')) {
            return;
        }

        Schema::table('announcements', function (Blueprint $table): void {
            $table->boolean('show_on_landing')->default(true)->after('target_audience');
        });
    }

    public function down(): void
    {
        if (! Schema::hasTable('announcements') || ! Schema::hasColumn('announcements', 'show_on_landing')) {
            return;
        }

        Schema::table('announcements', function (Blueprint $table): void {
            $table->dropColumn('show_on_landing');
        });
    }
};
