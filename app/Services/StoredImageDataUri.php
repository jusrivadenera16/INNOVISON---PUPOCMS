<?php

namespace App\Services;

class StoredImageDataUri
{
    private const ALLOWED_MIME_TYPES = [
        'image/gif',
        'image/jpeg',
        'image/png',
        'image/webp',
    ];

    public function __construct(private HealthFileStorage $healthFiles)
    {
    }

    public function fromStorage(?string $value): string
    {
        $value = trim((string) $value);
        if ($value === '') {
            return '';
        }

        if (str_starts_with($value, 'data:image/')) {
            return preg_match('#^data:image/(?:gif|jpe?g|png|webp);base64,#i', $value)
                ? $value
                : '';
        }

        $path = $this->healthFiles->normalizePath($value);
        if ($path === '' || !$this->healthFiles->exists($path)) {
            return '';
        }

        $contents = $this->healthFiles->get($path);
        if ($contents === '') {
            return '';
        }

        $imageInfo = @getimagesizefromstring($contents);
        $mimeType = strtolower((string) ($imageInfo['mime'] ?? ''));
        if (!in_array($mimeType, self::ALLOWED_MIME_TYPES, true)) {
            return '';
        }

        return 'data:' . $mimeType . ';base64,' . base64_encode($contents);
    }

    public function fromPublicDisk(?string $value): string
    {
        return $this->fromStorage($value);
    }
}
