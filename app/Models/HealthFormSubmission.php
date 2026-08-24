<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class HealthFormSubmission extends Model
{
    public const STATUS_REQUESTED = 'requested';
    public const STATUS_SUBMITTED = 'submitted';
    public const STATUS_APPROVED = 'approved';
    public const STATUS_NEEDS_CORRECTION = 'needs_correction';

    protected $fillable = [
        'user_id',
        'health_profile_id',
        'category',
        'school_year',
        'status',
        'pdf_path',
        'profile_snapshot',
        'snapshot_captured_at',
        'requested_by_user_id',
        'requested_at',
        'submitted_at',
        'approved_at',
        'remarks',
    ];

    protected $casts = [
        'profile_snapshot' => 'array',
        'snapshot_captured_at' => 'datetime',
        'requested_at' => 'datetime',
        'submitted_at' => 'datetime',
        'approved_at' => 'datetime',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function healthProfile()
    {
        return $this->belongsTo(HealthProfile::class);
    }

    public function requestedBy()
    {
        return $this->belongsTo(User::class, 'requested_by_user_id');
    }

    public function snapshotProfile(): array
    {
        $snapshot = is_array($this->profile_snapshot) ? $this->profile_snapshot : [];
        $profile = $snapshot['profile'] ?? $snapshot;

        return is_array($profile) ? $profile : [];
    }

    public function snapshotUser(): array
    {
        $snapshot = is_array($this->profile_snapshot) ? $this->profile_snapshot : [];
        $user = $snapshot['user'] ?? [];

        return is_array($user) ? $user : [];
    }
}
