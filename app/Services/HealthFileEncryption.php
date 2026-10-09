<?php

namespace App\Services;

use RuntimeException;

class HealthFileEncryption
{
    private const CIPHER = 'aes-256-gcm';
    private const MAGIC = 'OCMS-HEALTH-AES1';
    private const NONCE_LENGTH = 12;
    private const TAG_LENGTH = 16;

    public function enabled(): bool
    {
        return (bool) config('health_files.encryption_enabled', false);
    }

    public function encrypt(string $contents): string
    {
        if (!$this->enabled() || $this->isEncrypted($contents)) {
            return $contents;
        }

        return $this->encryptWithKey($contents, $this->key());
    }

    public function encryptWithKey(string $contents, string $configuredKey): string
    {
        $nonce = random_bytes(self::NONCE_LENGTH);
        $tag = '';
        $ciphertext = openssl_encrypt(
            $contents,
            self::CIPHER,
            $this->normalizeKey($configuredKey),
            OPENSSL_RAW_DATA,
            $nonce,
            $tag,
            '',
            self::TAG_LENGTH
        );

        if ($ciphertext === false || strlen($tag) !== self::TAG_LENGTH) {
            throw new RuntimeException('Unable to encrypt the private health file.');
        }

        return self::MAGIC . $nonce . $tag . $ciphertext;
    }

    public function decrypt(string $contents): string
    {
        if (!$this->isEncrypted($contents)) {
            return $contents;
        }

        return $this->decryptWithKey($contents, $this->key());
    }

    public function decryptWithKey(string $contents, string $configuredKey): string
    {
        if (!$this->isEncrypted($contents)) {
            return $contents;
        }

        $headerLength = strlen(self::MAGIC) + self::NONCE_LENGTH + self::TAG_LENGTH;
        if (strlen($contents) < $headerLength) {
            throw new RuntimeException('The private health file has an invalid encryption envelope.');
        }

        $offset = strlen(self::MAGIC);
        $nonce = substr($contents, $offset, self::NONCE_LENGTH);
        $offset += self::NONCE_LENGTH;
        $tag = substr($contents, $offset, self::TAG_LENGTH);
        $offset += self::TAG_LENGTH;
        $ciphertext = substr($contents, $offset);
        $plaintext = openssl_decrypt(
            $ciphertext,
            self::CIPHER,
            $this->normalizeKey($configuredKey),
            OPENSSL_RAW_DATA,
            $nonce,
            $tag,
            ''
        );

        if ($plaintext === false) {
            throw new RuntimeException('Unable to decrypt the private health file.');
        }

        return $plaintext;
    }

    public function reEncrypt(string $contents, string $fromKey, string $toKey): string
    {
        $plaintext = $this->isEncrypted($contents)
            ? $this->decryptWithKey($contents, $fromKey)
            : $contents;

        return $this->encryptWithKey($plaintext, $toKey);
    }

    public function isEncrypted(string $contents): bool
    {
        return strncmp($contents, self::MAGIC, strlen(self::MAGIC)) === 0;
    }

    private function key(): string
    {
        $configuredKey = trim((string) config('health_files.encryption_key', ''));
        if ($configuredKey === '') {
            throw new RuntimeException('HEALTH_FILES_ENCRYPTION_KEY or APP_KEY is required for private health-file encryption.');
        }

        return $this->normalizeKey($configuredKey);
    }

    private function normalizeKey(string $configuredKey): string
    {
        $configuredKey = trim($configuredKey);
        if ($configuredKey === '') {
            throw new RuntimeException('A non-empty encryption key is required.');
        }

        if (str_starts_with($configuredKey, 'base64:')) {
            $decoded = base64_decode(substr($configuredKey, 7), true);
            if ($decoded !== false) {
                $configuredKey = $decoded;
            }
        }

        return strlen($configuredKey) === 32
            ? $configuredKey
            : hash('sha256', $configuredKey, true);
    }
}
