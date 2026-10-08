<?php

namespace App\Console\Commands;

use App\Services\HealthFileEncryption;
use App\Services\HealthFileStorage;
use Illuminate\Console\Command;

class EncryptHealthFiles extends Command
{
    protected $signature = 'health-files:encrypt-private
        {--apply : Encrypt existing private health files in place}
        {--directory= : Limit the operation to one private-storage directory}';

    protected $description = 'Encrypt existing private health files with AES-256-GCM.';

    public function handle(HealthFileStorage $healthFiles, HealthFileEncryption $encryption): int
    {
        if (!$encryption->enabled()) {
            $this->error('Enable HEALTH_FILES_ENCRYPTION_ENABLED before encrypting private health files.');

            return self::FAILURE;
        }

        $directory = $healthFiles->normalizePath((string) $this->option('directory'));
        $files = collect($healthFiles->writeDisk()->allFiles($directory))
            ->map(fn ($path) => $healthFiles->normalizePath((string) $path))
            ->filter()
            ->unique()
            ->values();
        $apply = (bool) $this->option('apply');
        $encrypted = 0;
        $skipped = 0;
        $failed = 0;

        foreach ($files as $path) {
            try {
                $rawContents = (string) $healthFiles->writeDisk()->get($path);
                if ($encryption->isEncrypted($rawContents)) {
                    $skipped++;
                    continue;
                }

                if (!$apply) {
                    $encrypted++;
                    continue;
                }

                if (!$healthFiles->put($path, $rawContents)) {
                    $failed++;
                    continue;
                }

                $encryptedContents = (string) $healthFiles->writeDisk()->get($path);
                if (!$encryption->isEncrypted($encryptedContents)
                    || $healthFiles->get($path) !== $rawContents) {
                    $failed++;
                    continue;
                }

                $encrypted++;
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
                [$apply ? 'Files encrypted and verified' : 'Files that would be encrypted', $encrypted],
                ['Files already encrypted', $skipped],
                ['Failures', $failed],
            ]
        );

        if (!$apply) {
            $this->info('Dry run complete. No files were changed. Re-run with --apply to encrypt them.');
        }

        return $failed > 0 ? self::FAILURE : self::SUCCESS;
    }
}
