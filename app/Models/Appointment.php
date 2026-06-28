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
        'remarks',      // Backward-compatible virtual alias to notes
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
     * APT-ddmmyy-HHmmNN, where NN increments per appointment minute.
     */
    public static function generateAppointmentNumber($scheduledAt = null): string
    {
        if ($scheduledAt instanceof Carbon) {
            $baseTime = $scheduledAt->copy();
        } elseif ($scheduledAt) {
            $baseTime = Carbon::parse($scheduledAt);
        } else {
            $baseTime = Carbon::now();
        }

        $prefix = 'APT-' . $baseTime->format('dmy-Hi');
        $lastNumber = static::query()
            ->where('apt_id', 'like', $prefix . '%')
            ->orderByDesc('apt_id')
            ->value('apt_id');

        $nextSequence = 1;
        if ($lastNumber && preg_match('/(\d{2})$/', (string) $lastNumber, $matches)) {
            $nextSequence = ((int) $matches[1]) + 1;
        }

        return $prefix . str_pad((string) $nextSequence, 2, '0', STR_PAD_LEFT);
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
