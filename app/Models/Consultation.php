<?php

// app/Models/Consultation.php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class Consultation extends Model
{
    use SoftDeletes;

    protected $fillable = [
    'user_id', 'attending_staff_id', 'attending_staff_name', 'name', 'consultation_date',
    'time_in', 'time_out', 'user_type', 'user_role', 'consultation_source', 'service',
    'medical_condition_id', 'height', 'weight', 'temperature', 'blood_pressure', 'pulse_rate',
    'respiratory_rate', 'covid_status', 'covid_positive_date', 'reason_for_visit', 'certificate_type', 'medicine', 'item_id',
    'medicine_quantity', 'comments'
];

    protected $casts = [
        'consultation_date' => 'date',
        'covid_positive_date' => 'date',
        'medicine_quantity' => 'decimal:2',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function attendingStaff(): BelongsTo
    {
        return $this->belongsTo(User::class, 'attending_staff_id');
    }

    public function medicineItem(): BelongsTo
    {
        return $this->belongsTo(Item::class, 'item_id');
    }

    public function medicalCondition(): BelongsTo
    {
        return $this->belongsTo(MedicalConditions::class, 'medical_condition_id');
    }
}
