<?php

namespace App\Casts;

use App\Services\HealthDataEncrypter;
use Illuminate\Contracts\Database\Eloquent\CastsAttributes;
use Illuminate\Support\Collection;

class EncryptedHealthData implements CastsAttributes
{
    private string $format;

    public function __construct(string $format = 'scalar')
    {
        $this->format = $format;
    }

    public function get($model, string $key, $value, array $attributes)
    {
        if ($value === null) {
            return null;
        }

        $plaintext = app(HealthDataEncrypter::class)->decrypt((string) $value);

        if ($this->format === 'scalar') {
            return $plaintext;
        }

        $decoded = json_decode($plaintext, $this->format !== 'object');

        return $this->format === 'collection' ? new Collection($decoded ?: []) : $decoded;
    }

    public function set($model, string $key, $value, array $attributes)
    {
        if ($value === null) {
            return null;
        }

        $plaintext = in_array($this->format, ['array', 'json', 'object', 'collection'], true)
            ? json_encode($value, JSON_THROW_ON_ERROR)
            : (string) $value;

        return app(HealthDataEncrypter::class)->encrypt($plaintext);
    }
}
