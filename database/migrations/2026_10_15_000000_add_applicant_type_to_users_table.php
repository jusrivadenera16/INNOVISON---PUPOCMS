<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasColumn('users', 'applicant_type')) {
            Schema::table('users', function (Blueprint $table) {
                $table->string('applicant_type', 30)->nullable()->after('student_type');
            });
        }

        if (Schema::hasColumn('users', 'student_type') && Schema::hasColumn('users', 'user_type')) {
            DB::table('users')
                ->whereRaw('LOWER(TRIM(user_type)) = ?', ['applicant'])
                ->where('student_type', 'transferee')
                ->whereNull('applicant_type')
                ->update(['applicant_type' => 'transferee', 'student_type' => null]);
        }
    }

    public function down(): void
    {
        if (!Schema::hasColumn('users', 'applicant_type')) {
            return;
        }

        if (Schema::hasColumn('users', 'student_type') && Schema::hasColumn('users', 'user_type')) {
            DB::table('users')
                ->whereRaw('LOWER(TRIM(user_type)) = ?', ['applicant'])
                ->where('applicant_type', 'transferee')
                ->whereNull('student_type')
                ->update(['student_type' => 'transferee']);
        }

        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn('applicant_type');
        });
    }
};
