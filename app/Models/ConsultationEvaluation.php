<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ConsultationEvaluation extends Model
{
    protected $table = 'eval_form';

    protected $fillable = [
        'consultation_id',
        'user_id',
        'requested_by_user_id',
        'status',
        'consent_at',
        'client_type',
        'sex',
        'age_group',
        'cc1',
        'cc2',
        'cc3',
        'sqd_answers',
        'rating',
        'feedback',
        'suggestions',
        'sent_at',
        'opened_at',
        'submitted_at',
    ];

    protected $casts = [
        'consent_at' => 'datetime',
        'sqd_answers' => 'array',
        'sent_at' => 'datetime',
        'opened_at' => 'datetime',
        'submitted_at' => 'datetime',
    ];

    public function consultation(): BelongsTo
    {
        return $this->belongsTo(Consultation::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function requestedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'requested_by_user_id');
    }
}
