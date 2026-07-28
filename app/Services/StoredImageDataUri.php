<?php

namespace App\Services;

use Illuminate\Support\Facades\Storage;

class StoredImageDataUri
{
    private const ALLOWED_MIME_TYPES = [
        'image/gif',
        'image/jpeg',
        'image/png',
        'image/webp',
    ];

    public function fromPublicDisk(?string $value): string
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

        $path = $this->normalizeStoragePath($value);
        $disk = Storage::disk('public');
        if ($path === '' || !$disk->exists($path)) {
            return '';
        }

        $contents = $disk->get($path);
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

    private function normalizeStoragePath(string $path): string
    {
        $path = ltrim($path, '/');

        return preg_replace('#^(?:public/)?storage/#', '', $path) ?? $path;
    }
}
