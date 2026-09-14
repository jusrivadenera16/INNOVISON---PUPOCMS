<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::table('clinic_service_options')
            ->where('option_group', 'other_service')
            ->whereIn('code', ['blood_pressure_monitoring', 'bp_monitoring'])
            ->delete();
    }

    public function down(): void
    {
        DB::table('clinic_service_options')->insert([
            'option_group' => 'other_service',
            'code' => 'blood_pressure_monitoring',
            'name' => 'Blood Pressure Monitoring',
            'sort_order' => 10,
            'is_active' => true,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }
};
