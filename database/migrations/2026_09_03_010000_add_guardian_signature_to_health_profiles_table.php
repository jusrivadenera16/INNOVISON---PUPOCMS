<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddGuardianSignatureToHealthProfilesTable extends Migration
{
    public function up()
    {
        Schema::table('health_profiles', function (Blueprint $table) {
            if (!Schema::hasColumn('health_profiles', 'guardian_signature')) {
                $table->string('guardian_signature')->nullable()->after('digital_signature');
            }
        });
    }

    public function down()
    {
        Schema::table('health_profiles', function (Blueprint $table) {
            if (Schema::hasColumn('health_profiles', 'guardian_signature')) {
                $table->dropColumn('guardian_signature');
            }
        });
    }
}
