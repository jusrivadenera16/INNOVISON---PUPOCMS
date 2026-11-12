<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class IssuedMedicalClearance extends Model
{
    public static function documentVersions(string $recordType, int $profileId): \Illuminate\Support\Collection
    {
        if (!\Illuminate\Support\Facades\Schema::hasTable('issued_medical_clearances')) return collect();

        return static::query()->where('record_type', $recordType)->where('profile_id', $profileId)
            ->latest('id')->get()->groupBy('health_form_submission_id');
    }

    protected $guarded = ['id'];

    protected $casts = [
        'patient_name' => 'encrypted',
        'purpose' => 'encrypted',
        'doctor_review' => 'encrypted:array',
        'patient_age' => 'integer',
        'issued_on' => 'date',
    ];
}
