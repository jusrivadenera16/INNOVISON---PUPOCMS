<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class HealthProfileCorrectionRequest extends Model
{
    public const TYPE_FILE_CORRECTION = 'file_correction';
    public const TYPE_HEALTH_FORM_CORRECTION = 'health_form_correction';
    public const TYPE_NEW_HEALTH_FORM = 'new_health_form';

    public const STATUS_PENDING = 'pending';
    public const STATUS_SUBMITTED = 'submitted';
    public const STATUS_UNDER_REVIEW = 'under_review';
    public const STATUS_APPROVED = 'approved';
    public const STATUS_CANCELLED = 'cancelled';

    protected $fillable = [
        'user_id',
        'health_profile_id',
        'employee_health_profile_id',
        'health_form_submission_id',
        'profile_kind',
        'type',
        'status',
        'required_documents',
        'admin_note',
        'requested_by_user_id',
        'requested_at',
        'submitted_at',
        'reviewed_at',
        'metadata',
    ];

    protected $casts = [
        'required_documents' => 'array',
        'requested_at' => 'datetime',
        'submitted_at' => 'datetime',
        'reviewed_at' => 'datetime',
        'metadata' => 'array',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function healthProfile()
    {
        return $this->belongsTo(HealthProfile::class);
    }

    public function employeeHealthProfile()
    {
        return $this->belongsTo(EmployeeHealthProfile::class, 'employee_health_profile_id');
    }

    public function healthFormSubmission()
    {
        return $this->belongsTo(HealthFormSubmission::class);
    }

    public function requestedBy()
    {
        return $this->belongsTo(User::class, 'requested_by_user_id');
    }

    public function scopeActive($query)
    {
        return $query->whereIn('status', [
            self::STATUS_PENDING,
            self::STATUS_UNDER_REVIEW,
        ]);
    }

    public function markSubmitted(): void
    {
        $this->forceFill([
            'status' => self::STATUS_SUBMITTED,
            'submitted_at' => now(),
        ])->save();
    }
}
