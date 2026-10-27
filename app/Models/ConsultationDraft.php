<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Contracts\Encryption\DecryptException;
use Illuminate\Support\Facades\Schema;
use App\Services\HealthDataEncrypter;
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
        $encryptedPayload = $this->hasEncryptedPayloadColumn()
            ? ($this->attributes['payload_encrypted'] ?? null)
            : null;
        if (is_string($encryptedPayload) && $encryptedPayload !== '') {
            $payload = app(HealthDataEncrypter::class)->decrypt($encryptedPayload, true);

            return is_array($payload) ? $payload : null;
        }

        if (!$this->hasEncryptedPayloadColumn() && is_string($value) && trim($value) !== '') {
            try {
                $payload = app(HealthDataEncrypter::class)->decrypt($value, true);

                return is_array($payload) ? $payload : null;
            } catch (DecryptException) {
                // A migration-incomplete database may still contain legacy JSON here.
            }
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
        $ciphertext = app(HealthDataEncrypter::class)->encrypt($payload, true);

        if ($this->hasEncryptedPayloadColumn()) {
            $this->attributes['payload_encrypted'] = $ciphertext;
            $this->attributes['payload'] = json_encode([], JSON_THROW_ON_ERROR);

            return;
        }

        $this->attributes['payload'] = $ciphertext;
    }

    private function hasEncryptedPayloadColumn(): bool
    {
        return Schema::hasTable($this->getTable())
            && Schema::hasColumn($this->getTable(), 'payload_encrypted');
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
