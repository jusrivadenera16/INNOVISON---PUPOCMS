<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddModulePermissionsToUsersTable extends Migration
{
    public function up()
    {
        if (!Schema::hasTable('users') || Schema::hasColumn('users', 'module_permissions')) {
            return;
        }

        Schema::table('users', function (Blueprint $table) {
            $table->json('module_permissions')->nullable();
        });
    }

    public function down()
    {
        if (!Schema::hasTable('users') || !Schema::hasColumn('users', 'module_permissions')) {
            return;
        }

        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn('module_permissions');
        });
    }
}
