<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;

class HealthFormCategory extends Model
{
    public const AUDIENCE_LABELS = [
        'applicant' => 'Applicants',
        'student' => 'Students',
        'faculty' => 'Faculty',
        'admin' => 'Admin',
        'dependent' => 'Dependents',
    ];

    public const STUDENT_TYPE_LABELS = [
        'regular' => 'Regular',
        'ojt' => 'OJT',
        'ladderized' => 'Ladderized',
        'transferee' => 'Transferee',
        'returnee' => 'Returnee',
        'shiftee' => 'Shiftee',
    ];

    public const CONFIGURABLE_STUDENT_TYPES = [
        'ojt',
        'ladderized',
        'transferee',
        'returnee',
        'shiftee',
    ];

    protected $fillable = [
        'name',
        'is_active',
        'available_for',
        'student_types',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'available_for' => 'array',
        'student_types' => 'array',
    ];

    public function scopeAvailableFor(Builder $query, string $audience): Builder
    {
        return $query->whereJsonContains('available_for', $audience);
    }

    public function submissions()
    {
        return $this->hasMany(HealthFormSubmission::class, 'category', 'name');
    }

    public function employeeProfiles()
    {
        return $this->hasMany(EmployeeHealthProfile::class, 'health_form_category', 'name');
    }

    public function marSourceMappings()
    {
        return $this->hasMany(MarClearanceSourceMapping::class, 'source_category_id');
    }
}
