<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('health_profile_staffs', function (Blueprint $table) {
            if (!Schema::hasColumn('health_profile_staffs', 'birthday')) {
                $table->date('birthday')->nullable()->after('civil_status');
            }
        });

        if (
            Schema::hasColumn('health_profile_staffs', 'birthday')
            && Schema::hasColumn('health_profile_staffs', 'date_of_birth')
        ) {
            DB::table('health_profile_staffs')
                ->whereNull('birthday')
                ->update(['birthday' => DB::raw('date_of_birth')]);
        }

        Schema::table('health_profile_staffs', function (Blueprint $table) {
            $columns = array_filter([
                Schema::hasColumn('health_profile_staffs', 'reference_number') ? 'reference_number' : null,
                Schema::hasColumn('health_profile_staffs', 'date_of_birth') ? 'date_of_birth' : null,
            ]);

            if ($columns !== []) {
                $table->dropColumn($columns);
            }
        });
    }

    public function down(): void
    {
        Schema::table('health_profile_staffs', function (Blueprint $table) {
            if (!Schema::hasColumn('health_profile_staffs', 'reference_number')) {
                $table->string('reference_number')->nullable()->index()->after('user_id');
            }

            if (!Schema::hasColumn('health_profile_staffs', 'date_of_birth')) {
                $table->date('date_of_birth')->nullable()->after('civil_status');
            }
        });

        if (
            Schema::hasColumn('health_profile_staffs', 'date_of_birth')
            && Schema::hasColumn('health_profile_staffs', 'birthday')
        ) {
            DB::table('health_profile_staffs')
                ->whereNull('date_of_birth')
                ->update(['date_of_birth' => DB::raw('birthday')]);
        }

        Schema::table('health_profile_staffs', function (Blueprint $table) {
            if (Schema::hasColumn('health_profile_staffs', 'birthday')) {
                $table->dropColumn('birthday');
            }
        });
    }
};
