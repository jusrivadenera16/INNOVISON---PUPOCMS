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

        $columns = array_values(array_filter(
            ['admin_hub_enabled', 'admin_hub_role'],
            fn (string $column): bool => Schema::hasColumn('admins', $column)
        ));

        if ($columns === []) {
            return;
        }

        Schema::table('admins', function (Blueprint $table) use ($columns) {
            $table->dropColumn($columns);
        });
    }

    public function down(): void
    {
        if (!Schema::hasTable('admins')) {
            return;
        }

        Schema::table('admins', function (Blueprint $table) {
            if (!Schema::hasColumn('admins', 'admin_hub_enabled')) {
                $table->boolean('admin_hub_enabled')->default(false)->after('access_level');
            }

            if (!Schema::hasColumn('admins', 'admin_hub_role')) {
                $table->string('admin_hub_role', 50)->nullable()->after('admin_hub_enabled');
            }
        });
    }
};
