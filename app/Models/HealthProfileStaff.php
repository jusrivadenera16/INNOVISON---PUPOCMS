<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class HealthProfileStaff extends Model
{
    use SoftDeletes;

    protected $table = 'health_profile_staffs';

    protected $fillable = [
        'user_id',
        'employee_number',
        'first_name',
        'middle_name',
        'last_name',
        'name',
        'home_address',
        'contact_no',
        'emergency_contact_person',
        'emergency_contact_no',
        'form_date',
        'office',
        'course_college',
        'school_year',
        'age',
        'sex',
        'civil_status',
        'birthday',
        'past_medical_history',
        'past_medical_history_others',
        'previous_hospitalization',
        'previous_hospitalization_details',
        'operation_surgery',
        'operation_surgery_details',
        'current_medications',
        'allergies',
        'family_history',
        'family_history_others',
        'cigarette_smoking',
        'alcohol_drinking',
        'traveled_abroad',
        'has_disability',
        'disability_type',
        'student_photo',
        'health_declaration',
        'medical_certificate',
        'chest_xray_document',
        'pwd_id_proof',
        'vital_signs_distress_status',
        'height',
        'weight',
        'bmi',
        'bp',
        'hr',
        'rr',
        'temperature',
        'head_findings',
        'eyes_findings',
        'ears_findings',
        'throat_findings',
        'chest_lungs_findings',
        'chest_xray_result',
        'breast_findings',
        'heart_murmur',
        'heart_rhythm',
        'abdomen_findings',
        'genito_urinary_date_lmp',
        'extremities_findings',
        'vertebral_column_findings',
        'skin_findings',
        'working_impression',
        'fit_status',
        'for_work_up',
        'referred_to',
        'referred_to_others',
        'follow_up_on',
        'physician_signature',
        'staff_signature',
        'uploaded_signature_path',
        'signature_type',
        'certified_at',
        'submission_status',
        'clearance_status',
        'pending_reason',
        'documents_valid',
        'verified_at',
        'approved_by_user_id',
        'resubmission_required_fields',
        'resubmission_requested_at',
        'resubmitted_at',
        'staff_health_form_pdf_path',
    ];

    protected $casts = [
        'form_date' => 'date',
        'birthday' => 'date',
        'past_medical_history' => 'array',
        'previous_hospitalization' => 'boolean',
        'operation_surgery' => 'boolean',
        'family_history' => 'array',
        'cigarette_smoking' => 'boolean',
        'alcohol_drinking' => 'boolean',
        'traveled_abroad' => 'boolean',
        'has_disability' => 'boolean',
        'head_findings' => 'array',
        'eyes_findings' => 'array',
        'ears_findings' => 'array',
        'throat_findings' => 'array',
        'chest_lungs_findings' => 'array',
        'skin_findings' => 'array',
        'referred_to' => 'array',
        'certified_at' => 'datetime',
        'documents_valid' => 'boolean',
        'verified_at' => 'datetime',
        'resubmission_required_fields' => 'array',
        'resubmission_requested_at' => 'datetime',
        'resubmitted_at' => 'datetime',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function approvedBy()
    {
        return $this->belongsTo(User::class, 'approved_by_user_id');
    }
}
