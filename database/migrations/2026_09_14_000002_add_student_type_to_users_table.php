<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasColumn('users', 'student_type')) {
            Schema::table('users', function (Blueprint $table) {
                $table->string('student_type', 30)->nullable()->after('user_type');
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasColumn('users', 'student_type')) {
            Schema::table('users', function (Blueprint $table) {
                $table->dropColumn('student_type');
            });
        }
    }
};
