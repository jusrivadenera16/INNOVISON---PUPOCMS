<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ConsultationDraft extends Model
{
    protected $fillable = [
        'patient_user_id',
        'saved_by_user_id',
        'consultation_source',
        'appointment_number',
        'started_at',
        'payload',
    ];

    protected $casts = [
        'payload' => 'array',
    ];

    public function patient(): BelongsTo
    {
        return $this->belongsTo(User::class, 'patient_user_id');
    }

    public function savedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'saved_by_user_id');
    }
}
