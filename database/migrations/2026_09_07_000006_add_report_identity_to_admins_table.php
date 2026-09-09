<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('admins')) {
            return;
        }

        Schema::table('admins', function (Blueprint $table) {
            if (!Schema::hasColumn('admins', 'report_name')) {
                $table->string('report_name')->nullable()->after('name');
            }

            if (!Schema::hasColumn('admins', 'report_position')) {
                $table->string('report_position')->nullable()->after('report_name');
            }
        });
    }

    public function down(): void
    {
        if (!Schema::hasTable('admins')) {
            return;
        }

        Schema::table('admins', function (Blueprint $table) {
            $columns = [];
            if (Schema::hasColumn('admins', 'report_position')) {
                $columns[] = 'report_position';
            }
            if (Schema::hasColumn('admins', 'report_name')) {
                $columns[] = 'report_name';
            }

            if ($columns !== []) {
                $table->dropColumn($columns);
            }
        });
    }
};
