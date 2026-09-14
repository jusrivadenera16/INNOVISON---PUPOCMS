<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\Concerns\ExcludesInactiveUserRecords;

class PendingMedicalAssessment extends Model
{
    use HasFactory, ExcludesInactiveUserRecords;

    protected $fillable = [
        'reference_number',
        'email',
        'file_path',
        'file_name',
        'file_type',
        'file_size',
        'user_id',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public static function findByReferenceAndEmail(string $reference, string $email)
    {
        return static::where('reference_number', $reference)
            ->where('email', $email)
            ->first();
    }
}
