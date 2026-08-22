<?php

namespace App\Console\Commands;

use App\Models\AdminHub;
use App\Services\FacultySyncService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Schema;
use Throwable;

class SyncAdminHubFacultyUuids extends Command
{
    protected $signature = 'admin-hub:sync-faculty-uuids
                            {--dry-run : Preview UUID matches without changing Admin Hub records}';

    protected $description = 'Backfill missing Admin Hub UUIDs from the Faculty API using email or employee number matches.';

    public function handle(FacultySyncService $facultySyncService): int
    {
        if (!Schema::hasTable('admin_hub') || !AdminHub::hasColumn('admin_uuid')) {
            $this->error('Admin Hub UUID storage is unavailable. Run the Admin Hub migrations first.');

            return self::FAILURE;
        }

        try {
            $faculties = $facultySyncService->fetchFaculties();
        } catch (Throwable $exception) {
            $this->error('Faculty API fetch failed: ' . $exception->getMessage());

            return self::FAILURE;
        }

        $facultyIndexes = $this->buildFacultyIndexes($faculties, $facultySyncService);
        $dryRun = (bool) $this->option('dry-run');
        $uuidOwners = AdminHub::query()
            ->whereNotNull('admin_uuid')
            ->where('admin_uuid', '<>', '')
            ->pluck('id', 'admin_uuid')
            ->mapWithKeys(fn ($id, $uuid) => [strtolower(trim((string) $uuid)) => (int) $id])
            ->all();
        $metrics = [
            'Admin Hub records' => 0,
            'Already has UUID' => 0,
            'Matched by email' => 0,
            'Matched by employee number' => 0,
            $dryRun ? 'Would be updated' : 'Updated' => 0,
            'No Faculty API match' => 0,
            'Ambiguous Faculty API match' => 0,
            'UUID already belongs to another record' => 0,
        ];

        AdminHub::query()->orderBy('id')->cursor()->each(function (AdminHub $admin) use (&$metrics, &$uuidOwners, $facultyIndexes, $dryRun) {
            $metrics['Admin Hub records']++;

            if (trim((string) $admin->admin_uuid) !== '') {
                $metrics['Already has UUID']++;

                return;
            }

            $email = $this->normalize($admin->email);
            $employeeNumber = $this->normalize($admin->employee_number);
            $emailMatches = $email === '' ? [] : ($facultyIndexes['email'][$email] ?? []);
            $employeeMatches = $employeeNumber === '' ? [] : ($facultyIndexes['employee_number'][$employeeNumber] ?? []);
            $matches = array_values(array_unique(array_merge($emailMatches, $employeeMatches)));

            if ($matches === []) {
                $metrics['No Faculty API match']++;

                return;
            }

            if (count($matches) !== 1) {
                $metrics['Ambiguous Faculty API match']++;

                return;
            }

            $uuid = $matches[0];
            $ownerId = $uuidOwners[$uuid] ?? null;
            if ($ownerId !== null && $ownerId !== (int) $admin->id) {
                $metrics['UUID already belongs to another record']++;

                return;
            }

            if ($emailMatches !== []) {
                $metrics['Matched by email']++;
            } else {
                $metrics['Matched by employee number']++;
            }

            if (!$dryRun) {
                $admin->admin_uuid = $uuid;
                $admin->save();
                $uuidOwners[$uuid] = (int) $admin->id;
            }

            $metrics[$dryRun ? 'Would be updated' : 'Updated']++;
        });

        $this->table(
            ['Metric', $dryRun ? 'Dry-run result' : 'Result'],
            collect($metrics)->map(fn ($value, $label) => [$label, $value])->all()
        );
        $this->line($dryRun
            ? 'Dry run complete. No Admin Hub records were changed.'
            : 'Faculty UUID sync complete. Existing roles and profile details were preserved.');

        return self::SUCCESS;
    }

    private function buildFacultyIndexes(array $faculties, FacultySyncService $facultySyncService): array
    {
        $indexes = [
            'email' => [],
            'employee_number' => [],
        ];

        foreach ($faculties as $faculty) {
            if (!is_array($faculty)) {
                continue;
            }

            $uuid = $facultySyncService->resolveFacultyUuid($faculty);
            if ($uuid === null) {
                continue;
            }

            $profile = is_array($faculty['profile'] ?? null) ? $faculty['profile'] : [];
            foreach ($this->uniqueNormalizedValues([
                $faculty['email'] ?? null,
                $profile['email'] ?? null,
            ]) as $email) {
                $this->addIndexCandidate($indexes['email'], $email, $uuid);
            }

            foreach ($this->uniqueNormalizedValues([
                $faculty['faculty_code'] ?? null,
                $faculty['employee_number'] ?? null,
                $faculty['employee_no'] ?? null,
                $profile['faculty_code'] ?? null,
                $profile['employee_number'] ?? null,
                $profile['employee_no'] ?? null,
            ]) as $employeeNumber) {
                $this->addIndexCandidate($indexes['employee_number'], $employeeNumber, $uuid);
            }
        }

        return $indexes;
    }

    private function addIndexCandidate(array &$index, string $key, string $uuid): void
    {
        if (!isset($index[$key])) {
            $index[$key] = [];
        }

        if (!in_array($uuid, $index[$key], true)) {
            $index[$key][] = $uuid;
        }
    }

    private function uniqueNormalizedValues(array $values): array
    {
        return array_values(array_unique(array_filter(array_map(
            fn ($value) => $this->normalize($value),
            $values
        ))));
    }

    private function normalize($value): string
    {
        return strtolower(trim((string) $value));
    }
}
