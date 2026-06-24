<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            if (!Schema::hasColumn('users', 'is_student')) {
                $table->boolean('is_student')->nullable()->after('user_type')
                    ->comment('true=GUISIS student, false=PUPTAS applicant, null=neither');
            }
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            if (Schema::hasColumn('users', 'is_student')) {
                $table->dropColumn('is_student');
            }
        });
    }
};
