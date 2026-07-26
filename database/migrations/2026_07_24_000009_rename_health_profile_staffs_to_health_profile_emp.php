<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('health_profile_staffs') && !Schema::hasTable('health_profile_emp')) {
            Schema::rename('health_profile_staffs', 'health_profile_emp');
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('health_profile_emp') && !Schema::hasTable('health_profile_staffs')) {
            Schema::rename('health_profile_emp', 'health_profile_staffs');
        }
    }
};
