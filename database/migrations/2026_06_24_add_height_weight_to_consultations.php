<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('consultations', function (Blueprint $table) {
            if (!Schema::hasColumn('consultations', 'height')) {
                $table->decimal('height', 5, 2)->nullable()->after('medical_condition_id');
            }
            if (!Schema::hasColumn('consultations', 'weight')) {
                $table->decimal('weight', 5, 2)->nullable()->after('height');
            }
        });
    }

    public function down(): void
    {
        Schema::table('consultations', function (Blueprint $table) {
            if (Schema::hasColumn('consultations', 'height')) {
                $table->dropColumn('height');
            }
            if (Schema::hasColumn('consultations', 'weight')) {
                $table->dropColumn('weight');
            }
        });
    }
};
