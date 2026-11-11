<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('issued_medical_clearances', function (Blueprint $table) {
            $table->id();
            $table->string('record_type', 16);
            $table->unsignedBigInteger('profile_id');
            $table->unsignedBigInteger('user_id')->index();
            $table->unsignedBigInteger('health_form_submission_id');
            $table->uuid('request_key')->unique();
            $table->string('control_number')->nullable()->unique();
            $table->text('patient_name');
            $table->unsignedTinyInteger('patient_age');
            $table->string('patient_sex', 1);
            $table->date('issued_on');
            $table->text('purpose');
            $table->unsignedBigInteger('issued_by_user_id');
            $table->string('pdf_path')->nullable();
            $table->timestamps();
            $table->index(['record_type', 'profile_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('issued_medical_clearances');
    }
};
