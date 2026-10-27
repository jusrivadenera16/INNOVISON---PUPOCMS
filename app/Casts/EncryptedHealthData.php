<?php

namespace App\Casts;

use App\Services\HealthDataEncrypter;
use Illuminate\Contracts\Database\Eloquent\CastsAttributes;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use DateTimeInterface;

class EncryptedHealthData implements CastsAttributes
{
    private string $format;
    private ?int $precision;

    public function __construct(string $format = 'scalar', ?int $precision = null)
    {
        $this->format = $format;
        $this->precision = $precision;
    }

    public function get($model, string $key, $value, array $attributes)
    {
        if ($value === null) {
            return null;
        }

        $plaintext = app(HealthDataEncrypter::class)->decrypt((string) $value);

        return match ($this->format) {
            'array', 'json' => json_decode($plaintext, true),
            'object' => json_decode($plaintext),
            'collection' => new Collection(json_decode($plaintext, true) ?: []),
            'date', 'datetime' => Carbon::parse($plaintext),
            'immutable_date', 'immutable_datetime' => Carbon::parse($plaintext)->toDateTimeImmutable(),
            'integer', 'int' => (int) $plaintext,
            'boolean', 'bool' => in_array(strtolower($plaintext), ['1', 'true', 'on', 'yes'], true),
            'float', 'double', 'real' => (float) $plaintext,
            'decimal' => number_format((float) $plaintext, $this->precision ?? 2, '.', ''),
            default => $plaintext,
        };
    }

    public function set($model, string $key, $value, array $attributes)
    {
        if ($value === null) {
            return null;
        }

        $plaintext = match ($this->format) {
            'array', 'json', 'object', 'collection' => json_encode($value, JSON_THROW_ON_ERROR),
            'date', 'immutable_date' => $this->formatDate($value, 'Y-m-d'),
            'datetime', 'immutable_datetime' => $this->formatDate($value, 'Y-m-d H:i:s'),
            'integer', 'int' => (string) ((int) $value),
            'boolean', 'bool' => $value ? '1' : '0',
            'float', 'double', 'real', 'decimal' => (string) $value,
            default => $value instanceof DateTimeInterface ? $value->format('Y-m-d H:i:s') : (string) $value,
        };

        return app(HealthDataEncrypter::class)->encrypt($plaintext);
    }

    private function formatDate($value, string $format): string
    {
        return $value instanceof DateTimeInterface
            ? $value->format($format)
            : Carbon::parse($value)->format($format);
    }
}
