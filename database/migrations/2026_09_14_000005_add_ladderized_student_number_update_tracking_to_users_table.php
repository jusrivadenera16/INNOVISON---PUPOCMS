<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasColumn('users', 'ladderized_student_number_updated_at')) {
            Schema::table('users', function (Blueprint $table) {
                $table->timestamp('ladderized_student_number_updated_at')
                    ->nullable()
                    ->after('student_type');
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasColumn('users', 'ladderized_student_number_updated_at')) {
            Schema::table('users', function (Blueprint $table) {
                $table->dropColumn('ladderized_student_number_updated_at');
            });
        }
    }
};
