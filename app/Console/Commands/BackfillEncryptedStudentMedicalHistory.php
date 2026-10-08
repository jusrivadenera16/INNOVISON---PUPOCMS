<?php

namespace App\Console\Commands;

use App\Models\DependentsProfile;
use App\Models\EmployeeHealthProfile;
use App\Models\HealthFormSubmission;
use App\Models\HealthProfile;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Schema;

class BackfillEncryptedStudentMedicalHistory extends Command
{
    protected $signature = 'health:encrypt-student-medical-history
        {--apply : Write encrypted mirror values to the database}
        {--force : Refresh existing encrypted mirror values; requires --apply}
        {--chunk=100 : Number of records processed per batch}';

    protected $description = 'Backfill encrypted mirror values for health profiles and historical submissions.';

    private const PROFILE_MODELS = [
        'student' => [HealthProfile::class, 'health_profiles'],
        'employee' => [EmployeeHealthProfile::class, 'health_profile_emp'],
        'dependent' => [DependentsProfile::class, 'dependents_profiles'],
        'submission' => [HealthFormSubmission::class, 'health_form_submissions'],
    ];

    public function handle(): int
    {
        $apply = (bool) $this->option('apply');
        $force = (bool) $this->option('force');
        if ($force && !$apply) {
            $this->error('--force requires --apply because it overwrites encrypted mirror values.');

            return self::FAILURE;
        }

        $chunkSize = max(1, (int) $this->option('chunk'));
        $totalWritten = 0;
        $totalSkipped = 0;
        $foundTable = false;

        foreach (self::PROFILE_MODELS as $label => [$modelClass, $tableName]) {
            if (!Schema::hasTable($tableName)) {
                continue;
            }

            $foundTable = true;
            $fieldMap = $this->availableFieldMap($tableName);
            if ($fieldMap === []) {
                $this->line("{$label}: no matching source/mirror columns found.");
                continue;
            }

            $query = $modelClass::query()
                ->withoutGlobalScopes()
                ->orderBy('id');
            $total = (clone $query)->count();
            $written = 0;
            $skipped = 0;

            $recordLabel = $label === 'submission'
                ? 'historical submission'
                : "{$label} health profile";
            $this->info(($apply ? 'Apply mode: ' : 'Dry run: ') . "Found {$total} {$recordLabel}(s).");

            $query->chunkById($chunkSize, function ($profiles) use ($apply, $force, $fieldMap, &$written, &$skipped): void {
                foreach ($profiles as $profile) {
                    $encryptedValues = [];

                    foreach ($fieldMap as $sourceColumn => $encryptedColumn) {
                        $existingEncryptedValue = $profile->getRawOriginal($encryptedColumn);
                        if (!$force && $existingEncryptedValue !== null) {
                            continue;
                        }

                        $sourceValue = $profile->getAttribute($sourceColumn);
                        if ($sourceValue === null) {
                            continue;
                        }

                        $encryptedValues[$encryptedColumn] = $sourceValue;
                    }

                    if ($encryptedValues === []) {
                        $skipped++;
                        continue;
                    }

                    if ($apply) {
                        $profile->forceFill($encryptedValues)->saveQuietly();
                    }

                    $written++;
                }
            });

            $labelText = $apply ? 'Written' : 'Would write';
            $this->info("{$label}: {$labelText} {$written} profile(s); skipped {$skipped} profile(s).");
            $totalWritten += $written;
            $totalSkipped += $skipped;
        }

        if (!$foundTable) {
            $this->error('No supported health profile tables exist.');

            return self::FAILURE;
        }

        if (!$apply) {
            $this->warn('No data was changed. Re-run with --apply only after reviewing the count.');
        }

        $label = $apply ? 'Written' : 'Would write';
        $this->info("Total: {$label} {$totalWritten} profile(s); skipped {$totalSkipped} profile(s).");

        return self::SUCCESS;
    }

    private function availableFieldMap(string $tableName): array
    {
        $fieldMap = (array) config('health_data_encryption.fields.' . $tableName, []);

        return array_filter(
            $fieldMap,
            static fn (string $encryptedColumn, string $sourceColumn): bool => Schema::hasColumn($tableName, $sourceColumn)
                && Schema::hasColumn($tableName, $encryptedColumn),
            ARRAY_FILTER_USE_BOTH
        );
    }
}
