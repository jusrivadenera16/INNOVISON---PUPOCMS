<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Setting extends Model
{
    use HasFactory;

    protected $fillable = [
        'clinic_name',
        'clinic_location',
        'open_time',
        'close_time',
        'operating_days',
        'email_notifications',
        'admin_live_notifications',
        'auto_approve',
        'student_assistant_open_time',
        'student_assistant_close_time',
        'appointment_reminder_hours',
        'clinic_closure_enabled',
        'clinic_closure_starts_at',
        'clinic_closure_ends_at',
        'clinic_closure_reason',
        'clinic_closure_message',
    ];

    protected $casts = [
        'email_notifications' => 'boolean',
        'admin_live_notifications' => 'boolean',
        'auto_approve' => 'boolean',
        'appointment_reminder_hours' => 'integer',
        'clinic_closure_enabled' => 'boolean',
        'clinic_closure_starts_at' => 'datetime',
        'clinic_closure_ends_at' => 'datetime',
        'operating_days' => 'array',
    ];
}
