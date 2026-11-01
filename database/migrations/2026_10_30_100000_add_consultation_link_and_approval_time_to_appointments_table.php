<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('appointments', function (Blueprint $table) {
            if (!Schema::hasColumn('appointments', 'consultation_id')) {
                $table->foreignId('consultation_id')
                    ->nullable()
                    ->unique()
                    ->constrained('consultations')
                    ->nullOnDelete();
            }

            if (!Schema::hasColumn('appointments', 'approved_at')) {
                $table->timestamp('approved_at')->nullable();
            }
        });
    }

    public function down(): void
    {
        Schema::table('appointments', function (Blueprint $table) {
            if (Schema::hasColumn('appointments', 'consultation_id')) {
                $table->dropForeign(['consultation_id']);
                $table->dropUnique(['consultation_id']);
                $table->dropColumn('consultation_id');
            }

            if (Schema::hasColumn('appointments', 'approved_at')) {
                $table->dropColumn('approved_at');
            }
        });
    }
};
