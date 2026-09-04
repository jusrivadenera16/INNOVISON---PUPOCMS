<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        $this->addAddressColumns('health_profiles', 'home_address');
        $this->addAddressColumns('health_profile_emp', 'home_address');
    }

    public function down(): void
    {
        $this->dropAddressColumns('health_profiles');
        $this->dropAddressColumns('health_profile_emp');
    }

    private function addAddressColumns(string $tableName, string $afterColumn): void
    {
        if (!Schema::hasTable($tableName)) {
            return;
        }

        Schema::table($tableName, function (Blueprint $table) use ($tableName, $afterColumn) {
            if (!Schema::hasColumn($tableName, 'street')) {
                $table->string('street')->nullable()->after($afterColumn);
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
