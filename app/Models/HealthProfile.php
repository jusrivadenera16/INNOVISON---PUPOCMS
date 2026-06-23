<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class HealthProfile extends Model
{
    protected $fillable = [
        'user_id', 
        'student_id', 'student_number', 'reference_number',
        'school_year', 'home_address', 'zipcode', 'birthday', 'student_photo', 
        'height', 'weight',
        'age', 'sex', 'civil_status', 'course_college', 'course_code',
        'blood_type', 'guardian_name', 'landline', 'cellphone',
        'chest_xray_result',
        'xray_date',
        'xray_findings',
        'has_disability', 'disability_type',
        'has_illness', 'medical_history', 'other_illness',
        'food_allergies', 'no_allergies', 'medicine_allergies', 'other_med_allergies',
        'is_smoker', 'is_drinker', 'covid_vaccinated', 'vaccine_history',
        'pwd_id_proof',
        'medical_certificate', 'doctor_name', 'med_cert_date', 'med_cert_findings', 'medical_assessment_upload', 'clearance_status',
        'assessment_date',
        'blood_pressure',
        'respiratory_rate',
        'temperature',
        'covid_positive',
        'medical_certificate_issued_by',
        'medical_certificate_issued_at',
        'chest_xray_result_text',
        'chest_xray_date',
        'assessment_remarks',
        'pending_reason',
        'medical_condition_remarks',
        'physical_assessment_status',
        'documents_valid',
        'verified_at',
        'puptas_sync_status',
        'puptas_synced_at',
        'puptas_sync_message',
        
    ];

    protected $casts = [
        'puptas_synced_at' => 'datetime',
        'documents_valid' => 'boolean',
        'no_allergies' => 'boolean',
        'medical_history' => 'array',
        'medicine_allergies' => 'array',
        'vaccine_history' => 'array',
        'assessment_date' => 'date',
        'xray_date' => 'date',
        'med_cert_date' => 'date',
        'medical_certificate_issued_at' => 'date',
        'chest_xray_date' => 'date',

    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
