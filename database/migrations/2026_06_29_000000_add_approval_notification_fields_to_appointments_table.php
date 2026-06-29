<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('appointments', function (Blueprint $table) {
            if (!Schema::hasColumn('appointments', 'approval_message')) {
                $table->text('approval_message')->nullable()->after('notes');
            }

            if (!Schema::hasColumn('appointments', 'approval_reminders')) {
                $table->json('approval_reminders')->nullable()->after('approval_message');
            }
        });
    }

    public function down(): void
    {
        Schema::table('appointments', function (Blueprint $table) {
            if (Schema::hasColumn('appointments', 'approval_reminders')) {
                $table->dropColumn('approval_reminders');
            }

            if (Schema::hasColumn('appointments', 'approval_message')) {
                $table->dropColumn('approval_message');
            }
        });
    }
};
