<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;

class Appointment extends Model
{
    use HasFactory;

    /**
     * Mass assignable fields
     */
    protected $fillable = [
        'apt_id',       // Public appointment number
        'user_id',      // Link to User table
        'student_id',   // Student ID
        'student_number', // Student number
        'name',         // Name of student
        'email',        // Email
        'problem',      // Issue / reason for consultation
        'service',      // Optional service type
        'date',         // Appointment date
        'time',         // Appointment time
        'status',       // Pending / Completed / Cancelled
        'type',         // Source: online or walkin
        'user_type',    // Role enum: Student/Faculty/Admin/Dependent
        'notes',        // Stored DB field for notes
        'approval_message',
        'approval_reminders',
        'remarks',      // Backward-compatible virtual alias to notes
    ];

    protected $casts = [
        'approval_reminders' => 'array',
    ];

    /**
     * Normalize role value to appointments.user_type enum.
     */
    public static function normalizeUserType(?string $value): string
    {
        $key = strtolower(trim((string) $value));

        $map = [
            'student' => 'Student',
            'faculty' => 'Faculty',
            'admin' => 'Admin',
            'dependent' => 'Dependent',
            'dependents' => 'Dependent',
        ];

        return $map[$key] ?? 'Student';
    }

    /**
     * Generate the public appointment number:
     * OAPT/WAPT-ddmmyy-HHmmNN, where NN increments per source per day.
     */
    public static function generateAppointmentNumber($baseAt = null, ?string $source = 'online'): string
    {
        if ($baseAt instanceof Carbon) {
            $baseTime = $baseAt->copy();
        } elseif ($baseAt) {
            $baseTime = Carbon::parse($baseAt);
        } else {
            $baseTime = Carbon::now();
        }

        $sourceKey = strtolower(trim((string) $source));
        $sourcePrefix = in_array($sourceKey, ['walkin', 'walk-in', 'walk_in'], true) ? 'WAPT' : 'OAPT';
        $dayPrefix = $sourcePrefix . '-' . $baseTime->format('dmy') . '-';
        $timePrefix = $dayPrefix . $baseTime->format('Hi');

        $highestSequence = static::query()
            ->where('apt_id', 'like', $dayPrefix . '%')
            ->pluck('apt_id')
            ->map(function ($appointmentNumber) {
                return preg_match('/(\d{2,})$/', (string) $appointmentNumber, $matches)
                    ? (int) $matches[1]
                    : 0;
            })
            ->max() ?? 0;

        $nextSequence = $highestSequence + 1;

        return $timePrefix . str_pad((string) $nextSequence, 2, '0', STR_PAD_LEFT);
    }

    /**
     * Backward compatibility:
     * old code reads $appointment->remarks, but DB column is `notes`.
     */
    public function getRemarksAttribute()
    {
        return $this->attributes['notes'] ?? null;
    }

    /**
     * Backward compatibility:
     * old code writes $appointment->remarks = '...'; save into `notes`.
     */
    public function setRemarksAttribute($value)
    {
        $this->attributes['notes'] = $value;
    }

    /**
     * Relationship: Appointment belongs to User
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function feedback()
    {
        return $this->hasOne(AppointmentFeedback::class);
    }

    /**
     * Scope: Only walk-in appointments
     */
    public function scopeWalkIn($query)
    {
        return $query->whereIn('type', ['walkin', 'walk-in']);
    }

    /**
     * Scope: Only online appointments
     */
    public function scopeOnline($query)
    {
        return $query->where('type', 'online');
    }

    /**
     * Helper: Check if appointment is completed
     */
    public function isCompleted()
    {
        return $this->status === 'Completed';
    }

    /**
     * Sync overdue appointment statuses based on the scheduled date/time.
     * Pending appointments become Expired once their scheduled time passes.
     * Approved appointments become Missed once 1 hour has passed with no consult.
     */
    public static function expireOverduePending(): int
    {
        $now = Carbon::now();
        $nowStamp = $now->format('Y-m-d H:i:s');
        $missedCutoff = $now->copy()->subHour()->format('Y-m-d H:i:s');

        $expiredPending = static::query()
            ->where('status', 'Pending')
            ->whereRaw('TIMESTAMP(`date`, `time`) <= ?', [$nowStamp])
            ->update([
                'status' => 'Expired',
                'updated_at' => $now,
            ]);

        $missedApproved = static::query()
            ->where('status', 'Approved')
            ->whereRaw('TIMESTAMP(`date`, `time`) <= ?', [$missedCutoff])
            ->update([
                'status' => 'Missed',
                'updated_at' => $now,
            ]);

        return $expiredPending + $missedApproved;
    }
}
