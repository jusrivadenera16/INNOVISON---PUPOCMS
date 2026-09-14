<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('clinic_service_options', function (Blueprint $table) {
            $table->id();
            $table->string('option_group', 40);
            $table->string('code', 100);
            $table->string('name', 160);
            $table->unsignedInteger('sort_order')->default(0);
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->unique(['option_group', 'code']);
            $table->index(['option_group', 'is_active']);
        });

        DB::table('clinic_service_options')->insert([
            [
                'option_group' => 'referral',
                'code' => 'hospital_without_nurse',
                'name' => 'Refer to Hospital (Without Nurse)',
                'sort_order' => 10,
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'option_group' => 'referral',
                'code' => 'hospital_with_nurse',
                'name' => 'Refer to Hospital (With Nurse)',
                'sort_order' => 20,
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'option_group' => 'referral',
                'code' => 'general',
                'name' => 'Referral (General)',
                'sort_order' => 30,
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'option_group' => 'referral',
                'code' => 'others',
                'name' => 'Other Referral',
                'sort_order' => 40,
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'option_group' => 'other_service',
                'code' => 'blood_pressure_monitoring',
                'name' => 'Blood Pressure Monitoring',
                'sort_order' => 10,
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);
    }

    public function down(): void
    {
        Schema::dropIfExists('clinic_service_options');
    }
};
