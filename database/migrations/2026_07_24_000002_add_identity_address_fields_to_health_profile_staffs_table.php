<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('health_profile_staffs', function (Blueprint $table) {
            $table->string('first_name')->nullable()->after('employee_number');
            $table->string('middle_name')->nullable()->after('first_name');
            $table->string('last_name')->nullable()->after('middle_name');
            $table->string('sex')->nullable()->after('age');
            $table->text('home_address')->nullable()->after('address');
        });
    }

    public function down(): void
    {
        Schema::table('health_profile_staffs', function (Blueprint $table) {
            $table->dropColumn([
                'first_name',
                'middle_name',
                'last_name',
                'sex',
                'home_address',
            ]);
        });
    }
};
