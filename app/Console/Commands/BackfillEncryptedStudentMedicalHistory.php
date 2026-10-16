<?php

namespace App\Console\Commands;

use App\Models\DependentsProfile;
use App\Models\EmployeeHealthProfile;
use App\Models\HealthFormSubmission;
use App\Models\HealthProfile;
use App\Models\StudentMedicalAssessment;
use App\Services\HealthDataEncrypter;
use Illuminate\Console\Command;
use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Support\Facades\Schema;

class BackfillEncryptedStudentMedicalHistory extends Command
{
    protected $signature = 'health:encrypt-student-medical-history
        {--apply : Write encrypted mirror values to the database}
        {--force : Refresh existing encrypted mirror values, including legacy APP_KEY ciphertext}
        {--chunk=100 : Number of records processed per batch}';

    protected $description = 'Backfill encrypted mirror values for health profiles, medical assessments, and historical submissions.';

    private const PROFILE_MODELS = [
        'student' => [HealthProfile::class, 'health_profiles'],
        'employee' => [EmployeeHealthProfile::class, 'health_profile_emp'],
        'dependent' => [DependentsProfile::class, 'dependents_profiles'],
        'medical assessment' => [StudentMedicalAssessment::class, 'student_medical_assessments'],
        'submission' => [HealthFormSubmission::class, 'health_form_submissions'],
    ];

    public function handle(): int
    {
        $apply = (bool) $this->option('apply');
        $force = (bool) $this->option('force');
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

            $recordLabel = match ($label) {
                'submission' => 'historical submission',
                'medical assessment' => 'medical assessment',
                default => "{$label} health profile",
            };
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
                        $profile->refresh();

                        foreach ($encryptedValues as $encryptedColumn => $expectedValue) {
                            $sourceColumn = array_search($encryptedColumn, $fieldMap, true);
                            $ciphertext = $profile->getRawOriginal($encryptedColumn);
                            if ($sourceColumn === false || !is_string($ciphertext)) {
                                throw new \RuntimeException('An encrypted health field could not be verified after writing.');
                            }

                            app(HealthDataEncrypter::class)->decryptWithDedicatedKey($ciphertext);
                            if (!$this->valuesMatch($expectedValue, $profile->getAttribute($sourceColumn))) {
                                throw new \RuntimeException('An encrypted health field did not round-trip after writing.');
                            }
                        }
                    }

                    $written++;
                }
            });

            $labelText = $apply ? 'Written and verified' : ($force ? 'Would refresh' : 'Would write');
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

        $label = $apply ? 'Written and verified' : ($force ? 'Would refresh' : 'Would write');
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

    private function valuesMatch($left, $right): bool
    {
        if ($left instanceof \DateTimeInterface) {
            $left = $left->format('Y-m-d\TH:i:s.uP');
        }

        if ($right instanceof \DateTimeInterface) {
            $right = $right->format('Y-m-d\TH:i:s.uP');
        }

        if ($left instanceof Arrayable) {
            $left = $left->toArray();
        } elseif (is_object($left)) {
            $left = get_object_vars($left);
        }

        if ($right instanceof Arrayable) {
            $right = $right->toArray();
        } elseif (is_object($right)) {
            $right = get_object_vars($right);
        }

        if (is_array($left) && is_array($right)) {
            foreach ($left as $key => $value) {
                if (!array_key_exists($key, $right) || !$this->valuesMatch($value, $right[$key])) {
                    return false;
                }
            }

            return count($left) === count($right);
        }

        return $left === $right || (is_numeric($left) && is_numeric($right) && (string) $left === (string) $right);
    }
}
