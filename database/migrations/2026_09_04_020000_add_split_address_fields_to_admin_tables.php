<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        $this->addAddressColumns('admins');
        $this->addAddressColumns('admin_hub');
    }

    public function down(): void
    {
        $this->dropAddressColumns('admins');
        $this->dropAddressColumns('admin_hub');
    }

    private function addAddressColumns(string $tableName): void
    {
        if (!Schema::hasTable($tableName)) {
            return;
        }

        Schema::table($tableName, function (Blueprint $table) use ($tableName) {
            if (!Schema::hasColumn($tableName, 'street')) {
                $table->string('street')->nullable()->after('address');
            }

            if (!Schema::hasColumn($tableName, 'barangay')) {
                $table->string('barangay')->nullable()->after('street');
            }

            if (!Schema::hasColumn($tableName, 'municipality')) {
                $table->string('municipality')->nullable()->after('barangay');
            }

            if (!Schema::hasColumn($tableName, 'province')) {
                $table->string('province')->nullable()->after('municipality');
            }
        });
    }

    private function dropAddressColumns(string $tableName): void
    {
        if (!Schema::hasTable($tableName)) {
            return;
        }

        Schema::table($tableName, function (Blueprint $table) use ($tableName) {
            foreach (['province', 'municipality', 'barangay', 'street'] as $column) {
                if (Schema::hasColumn($tableName, $column)) {
                    $table->dropColumn($column);
                }
            }
        });
    }
};
