<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class HealthProfile extends Model
{
    protected $fillable = [
        'user_id', 
        'student_id', 'student_number', 'reference_number',
        'school_year', 'home_address', 'zipcode', 'birthday', 'student_photo', 'health_declaration',
        'digital_signature',
        'height', 'weight',
        'age', 'sex', 'civil_status', 'course_college', 'course_code',
        'blood_type', 'guardian_name', 'landline', 'cellphone',
        'chest_xray_result',
        'xray_date',
        'xray_findings',
        'xray_findings_details',
        'has_disability', 'disability_type',
        'has_illness', 'medical_history', 'other_illness',
        'food_allergies', 'no_allergies', 'medicine_allergies', 'other_med_allergies',
        'is_smoker', 'is_drinker', 'covid_vaccinated', 'vaccine_history',
        'pwd_id_proof',
        'medical_certificate', 'doctor_name', 'med_cert_date', 'med_cert_findings', 'med_cert_findings_details', 'medical_assessment_upload', 'clearance_status',
        'assessment_date',
        'blood_pressure',
        'pulse_rate',
        'respiratory_rate',
        'temperature',
        'covid_positive',
        'covid_positive_date',
        'medical_certificate_issued_by',
        'medical_certificate_issued_at',
        'chest_xray_result_text',
        'chest_xray_date',
        'assessment_remarks',
        'med_assessment_remarks',
        'pending_reason',
        'resubmission_required_documents',
        'resubmission_requested_at',
        'resubmitted_at',
        'review_started_at',
        'review_started_by_user_id',
        'medical_condition_remarks',
        'physical_assessment_status',
        'encode_remarks',
        'documents_valid',
        'verified_at',
        'approved_by_user_id',
        'puptas_sync_status',
        'puptas_synced_at',
        'puptas_sync_message',
        'final_review_draft_data',

    ];

    protected $casts = [
        'puptas_synced_at' => 'datetime',
        'documents_valid' => 'boolean',
        'no_allergies' => 'boolean',
        'medical_history' => 'array',
        'medicine_allergies' => 'array',
        'vaccine_history' => 'array',
        'assessment_date' => 'date',
        'covid_positive_date' => 'date',
        'xray_date' => 'date',
        'med_cert_date' => 'date',
        'medical_certificate_issued_at' => 'date',
        'chest_xray_date' => 'date',
        'resubmission_required_documents' => 'array',
        'resubmission_requested_at' => 'datetime',
        'resubmitted_at' => 'datetime',
        'review_started_at' => 'datetime',
        'verified_at' => 'datetime',
        'final_review_draft_data' => 'array',

    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function approvedBy()
    {
        return $this->belongsTo(User::class, 'approved_by_user_id');
    }

    public function reviewStartedBy()
    {
        return $this->belongsTo(User::class, 'review_started_by_user_id');
    }

    public function healthFormSubmissions()
    {
        return $this->hasMany(HealthFormSubmission::class);
    }

    public function latestHealthFormSubmission()
    {
        return $this->hasOne(HealthFormSubmission::class)->latestOfMany('submitted_at');
    }

    public function hasMedicalCondition(): bool
    {
        return $this->has_disability === 'Yes'
            || $this->has_illness === 'Yes'
            || $this->filledProfileValue($this->medical_history)
            || $this->filledProfileValue($this->other_illness)
            || $this->filledProfileValue($this->food_allergies)
            || $this->filledProfileValue($this->medicine_allergies)
            || $this->filledProfileValue($this->other_med_allergies)
            || $this->filledProfileValue($this->medical_condition_remarks);
    }

    public function scopeWithMedicalCondition($query)
    {
        return $query->where(function ($builder) {
            $builder->where('has_disability', 'Yes')
                ->orWhere('has_illness', 'Yes')
                ->orWhere(function ($q) {
                    $this->whereFilledProfileColumn($q, 'medical_history');
                })
                ->orWhere(function ($q) {
                    $this->whereFilledProfileColumn($q, 'other_illness');
                })
                ->orWhere(function ($q) {
                    $this->whereFilledProfileColumn($q, 'food_allergies');
                })
                ->orWhere(function ($q) {
                    $this->whereFilledProfileColumn($q, 'medicine_allergies');
                })
                ->orWhere(function ($q) {
                    $this->whereFilledProfileColumn($q, 'other_med_allergies');
                })
                ->orWhere(function ($q) {
                    $this->whereFilledProfileColumn($q, 'medical_condition_remarks');
                });
        });
    }

    public function scopeWithoutMedicalCondition($query)
    {
        return $query->where(function ($builder) {
            $builder->where(function ($q) {
                    $q->whereNull('has_disability')->orWhere('has_disability', '!=', 'Yes');
                })
                ->where(function ($q) {
                    $q->whereNull('has_illness')->orWhere('has_illness', '!=', 'Yes');
                })
                ->where(function ($q) {
                    $this->whereBlankProfileColumn($q, 'medical_history');
                })
                ->where(function ($q) {
                    $this->whereBlankProfileColumn($q, 'other_illness');
                })
                ->where(function ($q) {
                    $this->whereBlankProfileColumn($q, 'food_allergies');
                })
                ->where(function ($q) {
                    $this->whereBlankProfileColumn($q, 'medicine_allergies');
                })
                ->where(function ($q) {
                    $this->whereBlankProfileColumn($q, 'other_med_allergies');
                })
                ->where(function ($q) {
                    $this->whereBlankProfileColumn($q, 'medical_condition_remarks');
                });
        });
    }

    private function filledProfileValue($value): bool
    {
        if (is_array($value)) {
            return collect($value)->filter(fn ($item) => trim((string) $item) !== '')->isNotEmpty();
        }

        $normalized = trim((string) $value);

        return $normalized !== '' && $normalized !== '[]';
    }

    private function whereFilledProfileColumn($query, string $column): void
    {
        $query->whereNotNull($column)
            ->where($column, '!=', '')
            ->where($column, '!=', '[]');
    }

    private function whereBlankProfileColumn($query, string $column): void
    {
        $query->whereNull($column)
            ->orWhere($column, '')
            ->orWhere($column, '[]');
    }
}
