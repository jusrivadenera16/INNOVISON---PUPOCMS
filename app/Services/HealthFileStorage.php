<?php

namespace App\Services;

use Illuminate\Filesystem\FilesystemAdapter;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use RuntimeException;

class HealthFileStorage
{
    public function __construct(private HealthFileEncryption $encryption)
    {
    }

    public function normalizePath(?string $value): string
    {
        $path = trim((string) $value);
        if ($path === '' || str_starts_with($path, 'data:') || filter_var($path, FILTER_VALIDATE_URL)) {
            return '';
        }

        $path = str_replace('\\', '/', $path);
        $path = preg_replace('#^(?:public/)?storage/#i', '', ltrim($path, '/')) ?? $path;
        $segments = explode('/', $path);

        if (
            $path === ''
            || str_contains($path, "\0")
            || str_contains($path, ':')
            || in_array('..', $segments, true)
            || in_array('.', $segments, true)
        ) {
            return '';
        }

        return implode('/', array_filter($segments, fn ($segment) => $segment !== ''));
    }

    public function exists(?string $value): bool
    {
        return $this->resolve($value) !== null;
    }

    public function get(?string $value): string
    {
        [$disk, $path] = $this->resolvedOrFail($value);

        return $this->encryption->decrypt((string) $disk->get($path));
    }

    public function path(?string $value): string
    {
        [$disk, $path] = $this->resolvedOrFail($value);

        $rawContents = (string) $disk->get($path);
        if (!$this->encryption->isEncrypted($rawContents)) {
            return $disk->path($path);
        }

        $temporaryPath = tempnam(sys_get_temp_dir(), 'ocms-health-');
        if ($temporaryPath === false || file_put_contents($temporaryPath, $this->encryption->decrypt($rawContents)) === false) {
            throw new RuntimeException('Unable to materialize the encrypted private health file.');
        }

        register_shutdown_function(static function () use ($temporaryPath): void {
            @unlink($temporaryPath);
        });

        return $temporaryPath;
    }

    public function mimeType(?string $value): ?string
    {
        [$disk, $path] = $this->resolvedOrFail($value);
        $contents = $this->encryption->decrypt((string) $disk->get($path));
        $mimeType = function_exists('finfo_open')
            ? (new \finfo(FILEINFO_MIME_TYPE))->buffer($contents)
            : null;
        if (!is_string($mimeType) || $mimeType === '') {
            $mimeType = $disk->mimeType($path);
        }

        return is_string($mimeType) && $mimeType !== '' ? $mimeType : null;
    }

    public function lastModified(?string $value): ?int
    {
        $resolved = $this->resolve($value);
        if (!$resolved) {
            return null;
        }
        [$disk, $path] = $resolved;

        return $disk->lastModified($path) ?: null;
    }

    public function put(string $path, $contents, array $options = []): bool
    {
        $path = $this->normalizePath($path);
        if ($path === '') {
            throw new RuntimeException('Invalid private health-file path.');
        }

        $written = $this->writeDisk()->put($path, $this->encryption->encrypt((string) $contents), $options);
        if (!$written) {
            return false;
        }

        $this->mirrorToLegacyIfEnabled($path);

        return true;
    }

    public function store(UploadedFile $file, string $directory): string
    {
        $directory = $this->normalizePath($directory);
        if ($directory === '') {
            throw new RuntimeException('Invalid private health-file directory.');
        }

        $path = $file->hashName($directory);
        if (!$this->put($path, (string) $file->get())) {
            throw new RuntimeException('Unable to store the private health file.');
        }

        $this->mirrorToLegacyIfEnabled($path);

        return $path;
    }

    public function delete(?string $value): bool
    {
        $path = $this->normalizePath($value);
        if ($path === '') {
            return false;
        }

        $deleted = false;
        $writeDisk = $this->writeDisk();
        if ($writeDisk->exists($path)) {
            $deleted = $writeDisk->delete($path) || $deleted;
        }

        if (
            $this->writeDiskName() === $this->legacyDiskName()
            || (bool) config('health_files.delete_legacy_on_replace', false)
        ) {
            $legacyDisk = $this->legacyDisk();
            if ($legacyDisk->exists($path)) {
                $deleted = $legacyDisk->delete($path) || $deleted;
            }
        }

        return $deleted;
    }

    public function files(string $directory = ''): array
    {
        $directory = $this->normalizePath($directory);
        $files = $this->writeDisk()->files($directory);
        if (empty($files) && $this->writeDiskName() !== $this->legacyDiskName()) {
            $files = $this->legacyDisk()->files($directory);
        }

        return $files ?: [];
    }

    public function download(?string $value, ?string $name = null, array $headers = [])
    {
        $path = $this->normalizePath($value);
        $contents = $this->get($path);

        return response()->streamDownload(
            static function () use ($contents): void {
                echo $contents;
            },
            $name ?: basename($path),
            $this->responseHeaders($contents, $headers)
        );
    }

    public function fileResponse(?string $value, array $headers = [])
    {
        $path = $this->normalizePath($value);
        $contents = $this->get($path);

        return response()->stream(
            static function () use ($contents): void {
                echo $contents;
            },
            200,
            $this->responseHeaders($contents, $headers)
        );
    }

    public function writeDiskName(): string
    {
        return trim((string) config('health_files.write_disk', 'health_private')) ?: 'health_private';
    }

    public function legacyDiskName(): string
    {
        return trim((string) config('health_files.legacy_disk', 'public')) ?: 'public';
    }

    public function writeDisk(): FilesystemAdapter
    {
        return Storage::disk($this->writeDiskName());
    }

    public function legacyDisk(): FilesystemAdapter
    {
        return Storage::disk($this->legacyDiskName());
    }

    private function resolve(?string $value): ?array
    {
        $path = $this->normalizePath($value);
        if ($path === '') {
            return null;
        }

        $writeDisk = $this->writeDisk();
        if ($writeDisk->exists($path)) {
            return [$writeDisk, $path];
        }

        if (
            (bool) config('health_files.legacy_fallback', true)
            && $this->legacyDiskName() !== $this->writeDiskName()
        ) {
            $legacyDisk = $this->legacyDisk();
            if ($legacyDisk->exists($path)) {
                return [$legacyDisk, $path];
            }
        }

        return null;
    }

    private function resolvedOrFail(?string $value): array
    {
        $resolved = $this->resolve($value);
        if ($resolved === null) {
            throw new RuntimeException('Private health file not found.');
        }

        return $resolved;
    }

    private function responseHeaders(string $contents, array $headers): array
    {
        if (!array_key_exists('Content-Length', $headers)) {
            $headers['Content-Length'] = (string) strlen($contents);
        }

        return $headers;
    }

    private function mirrorToLegacyIfEnabled(string $path): void
    {
        if (
            !(bool) config('health_files.mirror_to_legacy', false)
            || $this->legacyDiskName() === $this->writeDiskName()
        ) {
            return;
        }

        $contents = $this->writeDisk()->get($path);
        if (!$this->legacyDisk()->put($path, $contents)) {
            $this->writeDisk()->delete($path);
            throw new RuntimeException('Unable to mirror the health file to legacy storage.');
        }
    }
}
