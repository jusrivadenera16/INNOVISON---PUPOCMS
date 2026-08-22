<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('announcements') || Schema::hasColumn('announcements', 'image_paths')) {
            return;
        }

        Schema::table('announcements', function (Blueprint $table) {
            $table->json('image_paths')->nullable()->after('message');
        });
    }

    public function down(): void
    {
        if (! Schema::hasTable('announcements') || ! Schema::hasColumn('announcements', 'image_paths')) {
            return;
        }

        Schema::table('announcements', function (Blueprint $table) {
            $table->dropColumn('image_paths');
        });
    }
};
