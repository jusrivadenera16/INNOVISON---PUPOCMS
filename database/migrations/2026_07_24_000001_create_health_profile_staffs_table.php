<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('health_profile_staffs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->unique()->constrained()->cascadeOnDelete();
            $table->string('reference_number')->nullable()->index();
            $table->string('employee_number')->nullable()->index();
            $table->string('name')->nullable();
            $table->text('address')->nullable();
            $table->string('contact_no')->nullable();
            $table->string('emergency_contact_person')->nullable();
            $table->string('emergency_contact_no')->nullable();
            $table->date('form_date')->nullable();
            $table->string('college_department')->nullable();
            $table->string('course_school_year')->nullable();
            $table->unsignedTinyInteger('age')->nullable();
            $table->string('civil_status')->nullable();
            $table->date('date_of_birth')->nullable();

            $table->json('past_medical_history')->nullable();
            $table->boolean('previous_hospitalization')->default(false);
            $table->text('previous_hospitalization_details')->nullable();
            $table->boolean('operation_surgery')->default(false);
            $table->text('operation_surgery_details')->nullable();
            $table->text('current_medications')->nullable();
            $table->text('allergies')->nullable();

            $table->json('family_history')->nullable();
            $table->string('family_history_others')->nullable();

            $table->boolean('cigarette_smoking')->default(false);
            $table->boolean('alcohol_drinking')->default(false);
            $table->boolean('traveled_abroad')->default(false);
            $table->boolean('has_disability')->default(false);
            $table->string('disability_type')->nullable();
            $table->string('student_photo')->nullable();
            $table->string('health_declaration')->nullable();
            $table->string('medical_certificate')->nullable();
            $table->string('chest_xray_document')->nullable();
            $table->string('pwd_id_proof')->nullable();

            $table->string('vital_signs_distress_status')->nullable();
            $table->string('height')->nullable();
            $table->string('weight')->nullable();
            $table->string('bmi')->nullable();
            $table->string('bp')->nullable();
            $table->string('hr')->nullable();
            $table->string('rr')->nullable();
            $table->string('temperature')->nullable();
            $table->json('head_findings')->nullable();
            $table->json('eyes_findings')->nullable();
            $table->json('ears_findings')->nullable();
            $table->json('throat_findings')->nullable();
            $table->json('chest_lungs_findings')->nullable();

            $table->string('chest_xray_result')->nullable();
            $table->string('breast_findings')->nullable();
            $table->string('heart_murmur')->nullable();
            $table->string('heart_rhythm')->nullable();
            $table->string('abdomen_findings')->nullable();
            $table->date('genito_urinary_date_lmp')->nullable();
            $table->string('extremities_findings')->nullable();
            $table->string('vertebral_column_findings')->nullable();
            $table->json('skin_findings')->nullable();

            $table->text('working_impression')->nullable();
            $table->string('fit_status')->nullable();
            $table->text('for_work_up')->nullable();
            $table->json('referred_to')->nullable();
            $table->string('referred_to_others')->nullable();
            $table->date('follow_up_on')->nullable();
            $table->string('physician_signature')->nullable();

            $table->longText('staff_signature')->nullable();
            $table->string('uploaded_signature_path')->nullable();
            $table->string('signature_type')->nullable();
            $table->timestamp('certified_at')->nullable();

            $table->string('submission_status')->default('submitted')->index();
            $table->string('clearance_status')->default('For Verification')->index();
            $table->text('pending_reason')->nullable();
            $table->boolean('documents_valid')->nullable();
            $table->timestamp('verified_at')->nullable();
            $table->foreignId('approved_by_user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->json('resubmission_required_fields')->nullable();
            $table->timestamp('resubmission_requested_at')->nullable();
            $table->timestamp('resubmitted_at')->nullable();
            $table->string('staff_health_form_pdf_path')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('health_profile_staffs');
    }
};
