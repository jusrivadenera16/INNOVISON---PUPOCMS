<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('issued_medical_clearances', function (Blueprint $table) {
            $table->text('doctor_review')->nullable();
        });
    }

    public function down(): void
    {
        Schema::table('issued_medical_clearances', fn (Blueprint $table) => $table->dropColumn('doctor_review'));
    }
};
