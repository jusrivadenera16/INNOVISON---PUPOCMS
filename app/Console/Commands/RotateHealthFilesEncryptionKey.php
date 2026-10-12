<?php

namespace App\Console\Commands;

use App\Services\HealthFileEncryption;
use App\Services\HealthFileStorage;
use Illuminate\Console\Command;

class RotateHealthFilesEncryptionKey extends Command
{
    protected $signature = 'health-files:rotate-key
        {--apply : Re-encrypt private health files in place}
        {--directory= : Limit the operation to one private-storage directory}
        {--from-app-key : Use the current APP_KEY as the source key}';

    protected $description = 'Re-encrypt private health files with the dedicated health-file key.';

    public function handle(HealthFileStorage $healthFiles, HealthFileEncryption $encryption): int
    {
        if (!$encryption->enabled()) {
            $this->error('Enable HEALTH_FILES_ENCRYPTION_ENABLED before rotating private health files.');

            return self::FAILURE;
        }

        $fromKey = $this->sourceKey();
        $toKey = trim((string) config('health_files.encryption_key', ''));

        if ($fromKey === '') {
            $this->error('Provide the old key through --from-app-key or HEALTH_FILES_OLD_ENCRYPTION_KEY.');

            return self::FAILURE;
        }

        if ($toKey === '') {
            $this->error('HEALTH_FILES_ENCRYPTION_KEY must be configured before rotating private health files.');

            return self::FAILURE;
        }

        if ($this->keysMatch($fromKey, $toKey)) {
            $this->error('The source and target encryption keys must be different.');

            return self::FAILURE;
        }

        $directory = $healthFiles->normalizePath((string) $this->option('directory'));
        $files = collect($healthFiles->writeDisk()->allFiles($directory))
            ->map(fn ($path) => $healthFiles->normalizePath((string) $path))
            ->filter()
            ->unique()
            ->values();
        $apply = (bool) $this->option('apply');
        $rotated = 0;
        $skipped = 0;
        $failed = 0;

        foreach ($files as $path) {
            try {
                $rawContents = (string) $healthFiles->writeDisk()->get($path);
                $plaintext = $this->decryptSourceOrCurrent($rawContents, $fromKey, $toKey, $encryption, $skipped);

                if ($plaintext === null) {
                    continue;
                }

                if (!$apply) {
                    $rotated++;
                    continue;
                }

                if (!$healthFiles->put($path, $plaintext)) {
                    $failed++;
                    continue;
                }

                $rotatedContents = (string) $healthFiles->writeDisk()->get($path);
                if (!$encryption->isEncrypted($rotatedContents)
                    || $healthFiles->get($path) !== $plaintext) {
                    $failed++;
                    continue;
                }

                $rotated++;
            } catch (\Throwable $exception) {
                $failed++;
                if ($this->output->isVerbose()) {
                    $this->error('Failed: ' . $path . ' (' . $exception->getMessage() . ')');
                }
            }
        }

        $this->table(
            ['Metric', $apply ? 'Result' : 'Dry-run result'],
            [
                [$apply ? 'Files re-encrypted and verified' : 'Files that would be re-encrypted', $rotated],
                ['Files already using the target key', $skipped],
                ['Failures', $failed],
            ]
        );

        if (!$apply) {
            $this->info('Dry run complete. No files were changed. Re-run with --apply after reviewing the count.');
        }

        return $failed > 0 ? self::FAILURE : self::SUCCESS;
    }

    private function sourceKey(): string
    {
        if ($this->option('from-app-key')) {
            return trim((string) config('app.key', ''));
        }

        return trim((string) env('HEALTH_FILES_OLD_ENCRYPTION_KEY', ''));
    }

    private function keysMatch(string $fromKey, string $toKey): bool
    {
        return hash_equals(hash('sha256', $fromKey), hash('sha256', $toKey));
    }

    private function decryptSourceOrCurrent(
        string $rawContents,
        string $fromKey,
        string $toKey,
        HealthFileEncryption $encryption,
        int &$skipped
    ): ?string {
        if (!$encryption->isEncrypted($rawContents)) {
            return $rawContents;
        }

        try {
            return $encryption->decryptWithKey($rawContents, $fromKey);
        } catch (\Throwable $sourceException) {
            try {
                $encryption->decryptWithKey($rawContents, $toKey);
                $skipped++;

                return null;
            } catch (\Throwable $targetException) {
                throw $sourceException;
            }
        }
    }
}
