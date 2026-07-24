<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddPastMedicalHistoryOthersToHealthProfileStaffsTable extends Migration
{
    public function up()
    {
        if (!Schema::hasTable('health_profile_staffs')) {
            return;
        }

        Schema::table('health_profile_staffs', function (Blueprint $table) {
            if (!Schema::hasColumn('health_profile_staffs', 'past_medical_history_others')) {
                $table->string('past_medical_history_others')->nullable()->after('past_medical_history');
            }
        });
    }

    public function down()
    {
        if (!Schema::hasTable('health_profile_staffs')) {
            return;
        }

        Schema::table('health_profile_staffs', function (Blueprint $table) {
            if (Schema::hasColumn('health_profile_staffs', 'past_medical_history_others')) {
                $table->dropColumn('past_medical_history_others');
            }
        });
    }
}
