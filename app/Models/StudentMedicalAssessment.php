<?php

namespace App\Models;

use App\Models\Concerns\EncryptsHealthProfileFields;
use Illuminate\Database\Eloquent\Model;

class StudentMedicalAssessment extends Model
{
    use EncryptsHealthProfileFields;

    protected $fillable = [
        'health_profile_id',
        'recorded_by_user_id',
        'condition_or_illness',
        'findings',
        'remarks',
        'reason',
    ];

    public function healthProfile()
    {
        return $this->belongsTo(HealthProfile::class);
    }

    public function recordedBy()
    {
        return $this->belongsTo(User::class, 'recorded_by_user_id');
    }
}
