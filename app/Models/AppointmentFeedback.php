<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\Concerns\ExcludesInactiveUserRecords;

class AppointmentFeedback extends Model
{
    use HasFactory, ExcludesInactiveUserRecords;

    protected $fillable = [
        'appointment_id',
        'user_id',
        'rating',
        'feedback',
        'submitted_at',
    ];

    protected $casts = [
        'submitted_at' => 'datetime',
    ];

    public function appointment()
    {
        return $this->belongsTo(Appointment::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
