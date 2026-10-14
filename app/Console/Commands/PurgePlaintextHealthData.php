<?php

namespace App\Console\Commands;

use App\Models\DependentsProfile;
use App\Models\EmployeeHealthProfile;
use App\Models\HealthFormSubmission;
use App\Models\HealthProfile;
use App\Models\StudentMedicalAssessment;
use DateTimeInterface;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Throwable;

class PurgePlaintextHealthData extends Command
{
    protected $signature = 'health:purge-plaintext-health-data
        {--apply : Clear source plaintext only after verifying encrypted mirrors}';

    protected $description = 'Verify encrypted health-data mirrors and clear matching plaintext source values.';

    private const PROFILE_MODELS = [
        'student' => [HealthProfile::class, 'health_profiles'],
        'employee' => [EmployeeHealthProfile::class, 'health_profile_emp'],
        'dependent' => [DependentsProfile::class, 'dependents_profiles'],
        'medical assessment' => [StudentMedicalAssessment::class, 'student_medical_assessments'],
        'submission' => [HealthFormSubmission::class, 'health_form_submissions'],
    ];

    public function handle(): int
    {
        if (!config('health_data_encryption.enabled', false)) {
            $this->error('Health data encryption must be enabled before plaintext can be purged.');

            return self::FAILURE;
        }

        $apply = (bool) $this->option('apply');
        $totalVerified = 0;
        $totalPurged = 0;
        $totalUnverified = 0;
        $foundTable = false;

        foreach (self::PROFILE_MODELS as $label => [$modelClass, $tableName]) {
            if (!Schema::hasTable($tableName)) {
                continue;
            }

            $fieldMap = $this->availableFieldMap($tableName);
            if ($fieldMap === []) {
                continue;
            }

            $foundTable = true;
            $tableVerified = 0;
            $tablePurged = 0;
            $tableUnverified = 0;

            $modelClass::query()
                ->withoutGlobalScopes()
                ->orderBy('id')
                ->chunkById(100, function ($profiles) use (
                    $tableName,
                    $fieldMap,
                    $apply,
                    &$tableVerified,
                    &$tablePurged,
                    &$tableUnverified
                ): void {
                    foreach ($profiles as $profile) {
                        $verifiedColumns = [];
                        $originalValues = [];

                        foreach ($fieldMap as $sourceColumn => $encryptedColumn) {
                            $sourceValue = $profile->getRawOriginal($sourceColumn);
                            if ($sourceValue === null) {
                                continue;
                            }

                            $encryptedValue = $profile->getRawOriginal($encryptedColumn);
                            if ($encryptedValue === null) {
                                $tableUnverified++;
                                continue;
                            }

                            try {
                                $plainValue = $this->plaintextSourceValue($profile, $sourceColumn, $encryptedColumn);
                                $decryptedValue = $profile->getAttribute($sourceColumn);
                            } catch (Throwable $exception) {
                                $tableUnverified++;
                                continue;
                            }

                            if (!$this->valuesMatch($plainValue, $decryptedValue)) {
                                $tableUnverified++;
                                continue;
                            }

                            $verifiedColumns[] = $sourceColumn;
                            $originalValues[$sourceColumn] = $sourceValue;
                            $tableVerified++;
                        }

                        if (!$apply || $verifiedColumns === []) {
                            continue;
                        }

                        try {
                            $updated = DB::transaction(function () use (
                                $tableName,
                                $profile,
                                $originalValues,
                                $verifiedColumns
                            ): int {
                                $row = DB::table($tableName)
                                    ->where('id', $profile->getKey())
                                    ->lockForUpdate()
                                    ->first();

                                if (!$row) {
                                    return 0;
                                }

                                foreach ($originalValues as $column => $value) {
                                    $currentValue = $row->{$column} ?? null;
                                    if ($currentValue === null || (string) $currentValue !== (string) $value) {
                                        return 0;
                                    }
                                }

                                return DB::table($tableName)
                                    ->where('id', $profile->getKey())
                                    ->update(array_fill_keys($verifiedColumns, null));
                            });
                        } catch (Throwable $exception) {
                            $tableUnverified += count($verifiedColumns);
                            continue;
                        }

                        if ($updated !== 1) {
                            $tableUnverified += count($verifiedColumns);
                            continue;
                        }

                        $tablePurged += count($verifiedColumns);
                    }
                });

            $totalVerified += $tableVerified;
            $totalPurged += $tablePurged;
            $totalUnverified += $tableUnverified;

            $this->line(sprintf(
                '%s: %d source field(s) verified; %d plaintext field(s) cleared; %d field(s) not verified.',
                ucfirst($label),
                $tableVerified,
                $tablePurged,
                $tableUnverified
            ));
        }

        if (!$foundTable) {
            $this->error('No supported health profile tables with mapped fields exist.');

            return self::FAILURE;
        }

        if (!$apply) {
            $this->warn('Dry run only. No data was changed. Use --apply after reviewing the verified-field count and confirming a backup.');
        }

        $this->info(sprintf(
            '%s: %d field(s) verified; %d plaintext field(s) cleared; %d field(s) not verified.',
            $apply ? 'Apply result' : 'Dry-run result',
            $totalVerified,
            $totalPurged,
            $totalUnverified
        ));

        return $totalUnverified === 0 ? self::SUCCESS : self::FAILURE;
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

    private function plaintextSourceValue($profile, string $sourceColumn, string $encryptedColumn)
    {
        $sourceModel = clone $profile;
        $attributes = $sourceModel->getAttributes();
        $attributes[$encryptedColumn] = null;
        $sourceModel->setRawAttributes($attributes, true);

        return $sourceModel->getAttribute($sourceColumn);
    }

    private function valuesMatch($left, $right): bool
    {
        return $this->normalizeValue($left) === $this->normalizeValue($right);
    }

    private function normalizeValue($value)
    {
        if ($value instanceof DateTimeInterface) {
            return $value->format('Y-m-d\TH:i:s.uP');
        }

        if (is_array($value)) {
            foreach ($value as $key => $item) {
                $value[$key] = $this->normalizeValue($item);
            }
        }

        return $value;
    }
}
