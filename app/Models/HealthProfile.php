<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class HealthProfile extends Model
{
    public const PULLOUT_PENDING = 'pending';
    public const PULLOUT_COMPLETED = 'pulled_out';
    public const PULLOUT_RESTORED = 'restored';

    protected $fillable = [
        'user_id', 
        'student_id', 'student_number', 'reference_number', 'health_form_category',
        'suffix_name',
        'school_year', 'home_address', 'street', 'barangay', 'municipality', 'province', 'zipcode', 'birthday', 'student_photo', 'health_declaration',
        'digital_signature', 'guardian_signature',
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
        'pending_compliance_reminder_sent_at',
        'pending_compliance_reminder_count',
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
        'pullout_status',
        'pullout_reason',
        'pullout_request_remarks',
        'pullout_requested_by_user_id',
        'pullout_requested_at',
        'pullout_reference',
        'pullout_completion_remarks',
        'pullout_completed_by_user_id',
        'pullout_completed_at',
        'pullout_previous_user_status',
        'pullout_restore_reason',
        'pullout_restored_by_user_id',
        'pullout_restored_at',

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
        'pending_compliance_reminder_sent_at' => 'datetime',
        'pending_compliance_reminder_count' => 'integer',
        'resubmitted_at' => 'datetime',
        'review_started_at' => 'datetime',
        'verified_at' => 'datetime',
        'final_review_draft_data' => 'array',
        'pullout_requested_at' => 'datetime',
        'pullout_completed_at' => 'datetime',
        'pullout_restored_at' => 'datetime',

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

    public function pulloutRequestedBy()
    {
        return $this->belongsTo(User::class, 'pullout_requested_by_user_id');
    }

    public function pulloutCompletedBy()
    {
        return $this->belongsTo(User::class, 'pullout_completed_by_user_id');
    }

    public function pulloutRestoredBy()
    {
        return $this->belongsTo(User::class, 'pullout_restored_by_user_id');
    }

    public function healthFormSubmissions()
    {
        return $this->hasMany(HealthFormSubmission::class);
    }

    public function correctionRequests()
    {
        return $this->hasMany(HealthProfileCorrectionRequest::class);
    }

    public function activeCorrectionRequest()
    {
        return $this->hasOne(HealthProfileCorrectionRequest::class)
            ->whereIn('status', [
                HealthProfileCorrectionRequest::STATUS_PENDING,
                HealthProfileCorrectionRequest::STATUS_UNDER_REVIEW,
            ])
            ->latestOfMany();
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

    public function scopeNotPulledOut($query)
    {
        return $query->where(function ($builder) {
            $builder->whereNull('pullout_status')
                ->orWhere('pullout_status', '!=', self::PULLOUT_COMPLETED);
        });
    }

    public function scopePulledOut($query)
    {
        return $query->where('pullout_status', self::PULLOUT_COMPLETED);
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
