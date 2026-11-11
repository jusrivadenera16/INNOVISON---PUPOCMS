<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class IssuedMedicalClearance extends Model
{
    protected $guarded = ['id'];

    protected $casts = [
        'patient_name' => 'encrypted',
        'purpose' => 'encrypted',
        'doctor_review' => 'encrypted:array',
        'patient_age' => 'integer',
        'issued_on' => 'date',
    ];
}
