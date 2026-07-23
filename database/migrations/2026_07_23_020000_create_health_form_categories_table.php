<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('health_form_categories')) {
            return;
        }

        Schema::create('health_form_categories', function (Blueprint $table) {
            $table->id();
            $table->string('name')->unique();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        $now = now();
        foreach ([
            'Annual / School Year Update',
            'OJT',
            'Internship',
            'Practicum',
            'Medical Clearance',
            'Return to School',
            'Event / Sports Participation',
            'Other',
        ] as $name) {
            DB::table('health_form_categories')->insert([
                'name' => $name,
                'is_active' => true,
                'created_at' => $now,
                'updated_at' => $now,
            ]);
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('health_form_categories');
    }
};
