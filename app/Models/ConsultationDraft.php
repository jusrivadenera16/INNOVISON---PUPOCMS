<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Crypt;
use App\Models\Concerns\ExcludesInactiveUserRecords;

class ConsultationDraft extends Model
{
    use ExcludesInactiveUserRecords;

    protected $fillable = [
        'patient_user_id',
        'saved_by_user_id',
        'consultation_source',
        'appointment_number',
        'started_at',
        'payload',
    ];

    protected $hidden = [
        'payload',
        'payload_encrypted',
    ];

    public function getPayloadAttribute($value): ?array
    {
        $encryptedPayload = $this->attributes['payload_encrypted'] ?? null;
        if (is_string($encryptedPayload) && $encryptedPayload !== '') {
            $payload = Crypt::decrypt($encryptedPayload);

            return is_array($payload) ? $payload : null;
        }

        if (is_array($value)) {
            return $value;
        }

        if (!is_string($value) || trim($value) === '') {
            return null;
        }

        $payload = json_decode($value, true);

        return is_array($payload) ? $payload : null;
    }

    public function setPayloadAttribute($value): void
    {
        $payload = is_array($value) ? $value : [];
        $this->attributes['payload_encrypted'] = Crypt::encrypt($payload);
        $this->attributes['payload'] = json_encode([], JSON_THROW_ON_ERROR);
    }

    public function patient(): BelongsTo
    {
        return $this->belongsTo(User::class, 'patient_user_id');
    }

    public function getInactiveUserForeignKey(): string
    {
        return 'patient_user_id';
    }

    public function getInactiveUserRelation(): string
    {
        return 'patient';
    }

    public function savedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'saved_by_user_id');
    }
}
