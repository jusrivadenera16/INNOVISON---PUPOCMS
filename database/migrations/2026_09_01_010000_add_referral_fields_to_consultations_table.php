<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasColumn('consultations', 'referral_type')) {
            Schema::table('consultations', function (Blueprint $table) {
                $table->string('referral_type', 60)->nullable()->after('certificate_type');
            });
        }

        if (!Schema::hasColumn('consultations', 'referral_details')) {
            Schema::table('consultations', function (Blueprint $table) {
                $table->string('referral_details', 500)->nullable()->after('referral_type');
            });
        }
    }

    public function down(): void
    {
        $columns = [];

        if (Schema::hasColumn('consultations', 'referral_details')) {
            $columns[] = 'referral_details';
        }

        if (Schema::hasColumn('consultations', 'referral_type')) {
            $columns[] = 'referral_type';
        }

        if ($columns) {
            Schema::table('consultations', function (Blueprint $table) use ($columns) {
                $table->dropColumn($columns);
            });
        }
    }
};
