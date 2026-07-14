<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Crypt;

class SystemSetting extends Model
{
    protected $fillable = [
        'key',
        'value',
        'encrypted',
    ];

    protected $casts = [
        'encrypted' => 'boolean',
    ];

    public static function getValue(string $key, mixed $default = null): mixed
    {
        $setting = static::query()->where('key', $key)->first();

        if (!$setting) {
            return $default;
        }

        if (!$setting->encrypted) {
            return $setting->value ?? $default;
        }

        if ($setting->value === null || $setting->value === '') {
            return $default;
        }

        try {
            return Crypt::decryptString($setting->value);
        } catch (\Throwable) {
            return $default;
        }
    }

    public static function putValue(string $key, mixed $value, bool $encrypted = false): void
    {
        static::query()->updateOrCreate(
            ['key' => $key],
            [
                'value' => $encrypted && $value !== null ? Crypt::encryptString((string) $value) : $value,
                'encrypted' => $encrypted,
            ]
        );
    }

    public static function booleanValue(string $key, bool $default = false): bool
    {
        return filter_var(static::getValue($key, $default ? '1' : '0'), FILTER_VALIDATE_BOOL);
    }
}
