<?php

namespace App\Console\Commands;

use App\Services\HealthFileStorage;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class MigrateHealthFilesToPrivate extends Command
{
    protected $signature = 'health-files:migrate-private
        {--dry-run : Inventory and verify without copying files}';

    protected $description = 'Copy legacy public health files to private storage and verify their contents.';

    public function handle(HealthFileStorage $healthFiles): int
    {
        $dryRun = (bool) $this->option('dry-run');
        $sourceDiskName = $healthFiles->legacyDiskName();
        $targetDiskName = $healthFiles->writeDiskName();

        if ($sourceDiskName === $targetDiskName) {
            $this->error('The legacy and private write disks must be different.');

            return self::FAILURE;
        }

        $sourceDisk = $healthFiles->legacyDisk();
        $targetDisk = $healthFiles->writeDisk();
        $sourceFiles = collect($sourceDisk->allFiles())
            ->map(fn ($path) => $healthFiles->normalizePath((string) $path))
            ->filter()
            ->unique()
            ->values();
        $referencedPaths = $this->databaseReferencedPaths($healthFiles);

        $copied = 0;
        $verified = 0;
        $conflicts = 0;
        $failed = 0;

        foreach ($sourceFiles as $path) {
            try {
                $sourceContents = (string) $sourceDisk->get($path);
                $sourceHash = hash('sha256', $sourceContents);

                if ($targetDisk->exists($path)) {
                    $targetHash = hash('sha256', (string) $targetDisk->get($path));
                    if (hash_equals($sourceHash, $targetHash)) {
                        $verified++;
                        continue;
                    }

                    $conflicts++;
                    if ($this->output->isVerbose()) {
                        $this->warn('Conflict, not overwritten: ' . $path);
                    }
                    continue;
                }

                if ($dryRun) {
                    $copied++;
                    if ($this->output->isVerbose()) {
                        $this->line('[dry-run] Would copy: ' . $path);
                    }
                    continue;
                }

                if (!$targetDisk->put($path, $sourceContents)) {
                    $failed++;
                    continue;
                }

                $targetHash = hash('sha256', (string) $targetDisk->get($path));
                if (!hash_equals($sourceHash, $targetHash)) {
                    $targetDisk->delete($path);
                    $failed++;
                    continue;
                }

                $copied++;
                if ($this->output->isVerbose()) {
                    $this->line('Copied and verified: ' . $path);
                }
            } catch (\Throwable $exception) {
                $failed++;
                if ($this->output->isVerbose()) {
                    $this->error('Failed: ' . $path . ' (' . $exception->getMessage() . ')');
                }
            }
        }

        $availablePaths = $sourceFiles
            ->merge($targetDisk->allFiles())
            ->map(fn ($path) => $healthFiles->normalizePath((string) $path))
            ->filter()
            ->unique()
            ->flip();
        $missingReferences = collect(array_keys($referencedPaths))
            ->reject(fn ($path) => $availablePaths->has($path))
            ->count();
        $trackedSourceFiles = $sourceFiles->filter(fn ($path) => isset($referencedPaths[$path]))->count();
        $untrackedSourceFiles = $sourceFiles->count() - $trackedSourceFiles;

        $this->table(
            ['Metric', $dryRun ? 'Dry-run result' : 'Result'],
            [
                ['Legacy source files', $sourceFiles->count()],
                ['Known database-referenced source files', $trackedSourceFiles],
                ['Other/untracked source files', $untrackedSourceFiles],
                [$dryRun ? 'Files that would be copied' : 'Files copied and verified', $copied],
                ['Files already verified in private storage', $verified],
                ['Conflicting private files', $conflicts],
                ['Copy/verification failures', $failed],
                ['Missing known database references', $missingReferences],
            ]
        );

        if ($dryRun) {
            $this->info('Dry run complete. No files were changed.');
        } else {
            $this->info('Copy complete. Legacy public files were not deleted.');
        }

        return ($conflicts > 0 || $failed > 0 || $missingReferences > 0)
            ? self::FAILURE
            : self::SUCCESS;
    }

    private function databaseReferencedPaths(HealthFileStorage $healthFiles): array
    {
        $references = [];

        foreach ((array) config('health_files.reference_fields', []) as $table => $columns) {
            if (!Schema::hasTable($table)) {
                continue;
            }

            foreach ((array) $columns as $column) {
                if (!Schema::hasColumn($table, $column)) {
                    continue;
                }

                DB::table($table)
                    ->whereNotNull($column)
                    ->where($column, '<>', '')
                    ->orderBy($column)
                    ->pluck($column)
                    ->each(function ($value) use (&$references, $healthFiles) {
                        $path = $healthFiles->normalizePath((string) $value);
                        if ($path !== '') {
                            $references[$path] = true;
                        }
                    });
            }
        }

        return $references;
    }
}
