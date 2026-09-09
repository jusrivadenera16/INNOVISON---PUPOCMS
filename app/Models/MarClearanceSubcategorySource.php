<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class MarClearanceSubcategorySource extends Model
{
    public const APPLICANT_FINAL_REVIEW = 'applicant_final_review';
    public const STUDENT_NURSE_REVIEW = 'student_nurse_review';
    public const EMPLOYEE_NURSE_REVIEW = 'employee_nurse_review';
    public const PATIENT_INTAKE = 'patient_intake';
    public const CONSULTATION = 'consultation';

    protected $fillable = ['mar_clearance_subcategory_id', 'source'];

    public function subcategory(): BelongsTo
    {
        return $this->belongsTo(MarClearanceSubcategory::class, 'mar_clearance_subcategory_id');
    }
}
