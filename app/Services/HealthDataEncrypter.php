<?php

namespace App\Services;

use Illuminate\Encryption\Encrypter;
use RuntimeException;

class HealthDataEncrypter
{
    public function encrypt($value, bool $serialize = false): string
    {
        return $this->dedicatedEncrypter()->encrypt($value, $serialize);
    }

    public function decrypt(string $payload, bool $unserialize = false)
    {
        return $this->dedicatedEncrypter()->decrypt($payload, $unserialize);
    }

    public function decryptWithDedicatedKey(string $payload, bool $unserialize = false)
    {
        return $this->dedicatedEncrypter()->decrypt($payload, $unserialize);
    }

    private function dedicatedEncrypter(): Encrypter
    {
        $configuredKey = trim((string) config('health_data_encryption.key', ''));
        if ($configuredKey === '') {
            throw new RuntimeException('HEALTH_DATA_ENCRYPTION_KEY is required while health-data encryption is enabled.');
        }

        if (str_starts_with($configuredKey, 'base64:')) {
            $decodedKey = base64_decode(substr($configuredKey, 7), true);
            if ($decodedKey === false) {
                throw new RuntimeException('HEALTH_DATA_ENCRYPTION_KEY must be valid base64 when using the base64: prefix.');
            }

            $configuredKey = $decodedKey;
        }

        if (!app()->environment('testing') && hash_equals($this->decodeKey((string) config('app.key', '')), $configuredKey)) {
            throw new RuntimeException('HEALTH_DATA_ENCRYPTION_KEY must be different from APP_KEY.');
        }

        return new Encrypter($configuredKey, config('health_data_encryption.cipher', 'AES-256-CBC'));
    }

    private function decodeKey(string $configuredKey): string
    {
        if (str_starts_with($configuredKey, 'base64:')) {
            $decodedKey = base64_decode(substr($configuredKey, 7), true);
            if ($decodedKey !== false) {
                return $decodedKey;
            }
        }

        return $configuredKey;
    }
}
