<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddLandlineToDependentsProfilesTable extends Migration
{
    public function up()
    {
        if (Schema::hasTable('dependents_profiles') && !Schema::hasColumn('dependents_profiles', 'landline')) {
            Schema::table('dependents_profiles', function (Blueprint $table) {
                $table->string('landline', 20)->nullable()->after('contact_no');
            });
        }
    }

    public function down()
    {
        if (Schema::hasTable('dependents_profiles') && Schema::hasColumn('dependents_profiles', 'landline')) {
            Schema::table('dependents_profiles', function (Blueprint $table) {
                $table->dropColumn('landline');
            });
        }
    }
}
