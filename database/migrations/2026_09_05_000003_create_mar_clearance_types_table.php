<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('mar_clearance_types')) {
            return;
        }

        Schema::create('mar_clearance_types', function (Blueprint $table) {
            $table->id();
            $table->string('code', 80)->unique();
            $table->string('name', 160)->unique();
            $table->unsignedInteger('sort_order')->default(1);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        $now = now();
        foreach ([
            ['excused_letter', 'Excused Letter'],
            ['medical_certificate', 'Medical Certificate'],
            ['ojt', 'OJT - On-the-job training'],
            ['freshmen', 'Freshmen'],
            ['returnee', 'Returnee'],
            ['annual_medical_faculty_staff', 'Annual Medical for Faculty and Staff'],
        ] as $index => [$code, $name]) {
            DB::table('mar_clearance_types')->insert([
                'code' => $code,
                'name' => $name,
                'sort_order' => $index + 1,
                'is_active' => true,
                'created_at' => $now,
                'updated_at' => $now,
            ]);
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('mar_clearance_types');
    }
};
