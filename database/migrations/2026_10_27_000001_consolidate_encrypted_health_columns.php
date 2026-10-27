<?php

use App\Services\HealthDataEncrypter;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Contracts\Encryption\DecryptException;

return new class extends Migration
{
    public function up(): void
    {
        $pairs = $this->fieldPairs();
        if ($pairs === []) {
            return;
        }

        $this->validateHealthFieldData($pairs);
        $this->validateDraftPayloads($pairs);
        $indexesToDrop = $this->indexesToDrop($pairs);

        foreach ($indexesToDrop as $table => $indexes) {
            foreach ($indexes as $index) {
                Schema::table($table, static function (Blueprint $blueprint) use ($index): void {
                    $blueprint->dropIndex($index);
                });
            }
        }

        $this->widenOriginalColumns($pairs);
        $this->copyHealthFieldCiphertexts($pairs);
        $this->copyDraftPayloads($pairs);
        $this->verifyCopiedFields($pairs);
        $this->dropMirrorColumns($pairs);
    }

    public function down(): void
    {
        $pairs = $this->fieldPairs();
        if ($pairs === []) {
            return;
        }

        $this->validateSingleColumnCiphertexts($pairs);
        $this->addMirrorColumns($pairs);
        $this->restoreMirrorCiphertexts($pairs);
        $this->restoreDependentIndexes();
    }

    private function fieldPairs(): array
    {
        $mappings = (array) config('health_data_encryption.fields', []);
        $mappings['consultation_drafts'] = ['payload' => 'payload_encrypted'];
        $pairs = [];

        foreach ($mappings as $table => $fieldMap) {
            if (!Schema::hasTable($table)) {
                continue;
            }

            $columns = Schema::getColumnListing($table);
            foreach ($fieldMap as $source => $mirror) {
                if (!in_array($source, $columns, true)) {
                    if (in_array($mirror, $columns, true)) {
                        throw new RuntimeException("Encrypted column {$table}.{$mirror} has no matching original column.");
                    }

                    continue;
                }

                $pairs[$table][] = [
                    'source' => $source,
                    'mirror' => $mirror,
                    'has_mirror' => in_array($mirror, $columns, true),
                ];
            }
        }

        return $pairs;
    }

    private function validateHealthFieldData(array $pairs): void
    {
        $encrypter = app(HealthDataEncrypter::class);

        foreach ($pairs as $table => $fields) {
            if ($table === 'consultation_drafts') {
                continue;
            }

            DB::table($table)->orderBy('id')->chunkById(100, function ($records) use ($fields, $table, $encrypter): void {
                foreach ($records as $record) {
                    foreach ($fields as $field) {
                        $source = $record->{$field['source']} ?? null;
                        $mirror = $field['has_mirror'] ? ($record->{$field['mirror']} ?? null) : null;

                        if ($mirror !== null) {
                            if (!is_string($mirror)) {
                                throw new RuntimeException("Encrypted value {$table}.{$field['mirror']} is not text.");
                            }

                            $encrypter->decryptWithDedicatedKey($mirror);
                            if ($source !== null && !hash_equals($mirror, (string) $source)) {
                                throw new RuntimeException("Original column {$table}.{$field['source']} is occupied; migration stopped without changing data.");
                            }

                            continue;
                        }

                        if ($source !== null) {
                            if ($field['has_mirror']) {
                                throw new RuntimeException("Original column {$table}.{$field['source']} is occupied without an encrypted mirror; migration stopped without changing data.");
                            }

                            $encrypter->decryptWithDedicatedKey((string) $source);
                        }
                    }
                }
            });
        }
    }

    private function validateDraftPayloads(array $pairs): void
    {
        if (!isset($pairs['consultation_drafts'])) {
            return;
        }

        $encrypter = app(HealthDataEncrypter::class);
        $hasMirror = $pairs['consultation_drafts'][0]['has_mirror'];

        DB::table('consultation_drafts')->orderBy('id')->chunkById(100, function ($drafts) use ($encrypter, $hasMirror): void {
            foreach ($drafts as $draft) {
                $source = (string) ($draft->payload ?? '[]');
                $sourceCipher = $this->decryptDraftCiphertext($source, $encrypter);
                $legacyPayload = $sourceCipher ?? json_decode($source, true);
                if (!is_array($legacyPayload)) {
                    throw new RuntimeException('A consultation draft payload is neither valid encrypted data nor valid JSON.');
                }

                $mirror = $hasMirror ? ($draft->payload_encrypted ?? null) : null;
                if ($mirror === null) {
                    continue;
                }

                $encryptedPayload = $encrypter->decryptWithDedicatedKey((string) $mirror, true);
                if (!is_array($encryptedPayload)) {
                    throw new RuntimeException('An encrypted consultation draft payload could not be verified.');
                }

                if ($legacyPayload !== [] && $legacyPayload !== $encryptedPayload) {
                    throw new RuntimeException('Consultation draft plaintext and encrypted payloads disagree; migration stopped without changing data.');
                }
            }
        });
    }

    private function indexesToDrop(array $pairs): array
    {
        $drop = [];

        foreach ($pairs as $table => $fields) {
            $mappedColumns = [];
            $sourceColumns = array_column($fields, 'source');
            $mirrorColumns = [];
            foreach ($fields as $field) {
                $mappedColumns[] = $field['source'];
                if ($field['has_mirror']) {
                    $mappedColumns[] = $field['mirror'];
                    $mirrorColumns[] = $field['mirror'];
                }
            }

            foreach (Schema::getIndexes($table) as $index) {
                $indexedMappedColumns = array_values(array_intersect($index['columns'], $mappedColumns));
                if ($indexedMappedColumns === []) {
                    continue;
                }

                if ($index['unique'] || $index['primary'] || count($index['columns']) > 1) {
                    throw new RuntimeException("Index {$index['name']} on {$table} needs manual review before encrypted columns can be consolidated.");
                }

                if (in_array($index['columns'][0], $mirrorColumns, true)) {
                    throw new RuntimeException("Index {$index['name']} on encrypted mirror {$table}.{$index['columns'][0]} needs manual review before consolidation.");
                }

                if (in_array($index['columns'][0], $sourceColumns, true)) {
                    $drop[$table][] = $index['name'];
                }
            }
        }

        return $drop;
    }

    private function widenOriginalColumns(array $pairs): void
    {
        if (!in_array(DB::getDriverName(), ['mysql', 'mariadb'], true)) {
            return;
        }

        $grammar = DB::connection()->getQueryGrammar();
        foreach ($pairs as $table => $fields) {
            $columns = array_values(array_unique(array_column($fields, 'source')));
            if ($columns === []) {
                continue;
            }

            $modifications = array_map(
                static fn (string $column): string => 'MODIFY COLUMN ' . $grammar->wrap($column) . ' LONGTEXT NULL',
                $columns
            );

            DB::statement('ALTER TABLE ' . $grammar->wrapTable($table) . ' ' . implode(', ', $modifications));
        }
    }

    private function copyHealthFieldCiphertexts(array $pairs): void
    {
        foreach ($pairs as $table => $fields) {
            if ($table === 'consultation_drafts') {
                continue;
            }

            foreach ($fields as $field) {
                if (!$field['has_mirror']) {
                    continue;
                }

                DB::table($table)
                    ->whereNull($field['source'])
                    ->whereNotNull($field['mirror'])
                    ->update([$field['source'] => DB::raw(DB::connection()->getQueryGrammar()->wrap($field['mirror']))]);
            }
        }
    }

    private function copyDraftPayloads(array $pairs): void
    {
        if (!isset($pairs['consultation_drafts'])) {
            return;
        }

        $encrypter = app(HealthDataEncrypter::class);
        $hasMirror = $pairs['consultation_drafts'][0]['has_mirror'];

        DB::table('consultation_drafts')->orderBy('id')->chunkById(100, function ($drafts) use ($encrypter, $hasMirror): void {
            foreach ($drafts as $draft) {
                $source = (string) ($draft->payload ?? '[]');
                $sourceCipher = $this->decryptDraftCiphertext($source, $encrypter);
                $mirror = $hasMirror ? ($draft->payload_encrypted ?? null) : null;

                if ($mirror !== null) {
                    $ciphertext = (string) $mirror;
                } elseif ($sourceCipher !== null) {
                    $ciphertext = $source;
                } else {
                    $payload = json_decode($source, true);
                    if (!is_array($payload)) {
                        throw new RuntimeException('A consultation draft payload could not be encrypted.');
                    }

                    $ciphertext = $encrypter->encrypt($payload, true);
                }

                DB::table('consultation_drafts')->where('id', $draft->id)->update(['payload' => $ciphertext]);
            }
        });
    }

    private function verifyCopiedFields(array $pairs): void
    {
        $encrypter = app(HealthDataEncrypter::class);

        foreach ($pairs as $table => $fields) {
            if ($table === 'consultation_drafts') {
                DB::table($table)->orderBy('id')->chunkById(100, function ($drafts) use ($encrypter): void {
                    foreach ($drafts as $draft) {
                        if (!is_array($encrypter->decryptWithDedicatedKey((string) $draft->payload, true))) {
                            throw new RuntimeException('A consolidated consultation draft payload failed verification.');
                        }
                    }
                });

                continue;
            }

            DB::table($table)->orderBy('id')->chunkById(100, function ($records) use ($fields, $table, $encrypter): void {
                foreach ($records as $record) {
                    foreach ($fields as $field) {
                        $source = $record->{$field['source']} ?? null;
                        $mirror = $field['has_mirror'] ? ($record->{$field['mirror']} ?? null) : null;

                        if ($mirror !== null && ($source === null || !hash_equals((string) $mirror, (string) $source))) {
                            throw new RuntimeException("Ciphertext copy verification failed for {$table}.{$field['source']}.");
                        }

                        if (!$field['has_mirror'] && $source !== null) {
                            $encrypter->decryptWithDedicatedKey((string) $source);
                        }
                    }
                }
            });
        }
    }

    private function dropMirrorColumns(array $pairs): void
    {
        foreach ($pairs as $table => $fields) {
            $columns = array_values(array_filter(
                array_column($fields, 'mirror'),
                static fn (string $column, int $index): bool => $fields[$index]['has_mirror'],
                ARRAY_FILTER_USE_BOTH
            ));

            if ($columns !== []) {
                Schema::table($table, static function (Blueprint $blueprint) use ($columns): void {
                    $blueprint->dropColumn($columns);
                });
            }
        }
    }

    private function validateSingleColumnCiphertexts(array $pairs): void
    {
        $encrypter = app(HealthDataEncrypter::class);

        foreach ($pairs as $table => $fields) {
            if ($table === 'consultation_drafts') {
                DB::table($table)->orderBy('id')->chunkById(100, function ($drafts) use ($encrypter): void {
                    foreach ($drafts as $draft) {
                        $ciphertext = $draft->payload_encrypted ?? $draft->payload ?? null;
                        if (is_string($ciphertext) && $ciphertext !== '') {
                            $this->decryptDraftCiphertext($ciphertext, $encrypter)
                                ?? throw new RuntimeException('A consultation draft could not be restored to mirror storage.');
                        }
                    }
                });

                continue;
            }

            DB::table($table)->orderBy('id')->chunkById(100, function ($records) use ($fields, $encrypter): void {
                foreach ($records as $record) {
                    foreach ($fields as $field) {
                        $mirror = $field['has_mirror'] ? ($record->{$field['mirror']} ?? null) : null;
                        $source = $record->{$field['source']} ?? null;
                        $ciphertext = $mirror ?? $source;
                        if (is_string($ciphertext) && $ciphertext !== '') {
                            $encrypter->decryptWithDedicatedKey($ciphertext);
                        }
                    }
                }
            });
        }
    }

    private function addMirrorColumns(array $pairs): void
    {
        foreach ($pairs as $table => $fields) {
            $columns = array_values(array_filter(
                array_column($fields, 'mirror'),
                static fn (string $column, int $index): bool => !$fields[$index]['has_mirror'],
                ARRAY_FILTER_USE_BOTH
            ));

            if ($columns === []) {
                continue;
            }

            Schema::table($table, static function (Blueprint $blueprint) use ($columns, $table): void {
                foreach ($columns as $column) {
                    if ($table === 'consultation_drafts' && $column === 'payload_encrypted') {
                        $blueprint->longText($column)->nullable();
                    } else {
                        $blueprint->text($column)->nullable();
                    }
                }
            });
        }
    }

    private function restoreMirrorCiphertexts(array $pairs): void
    {
        $encrypter = app(HealthDataEncrypter::class);

        foreach ($pairs as $table => $fields) {
            if ($table === 'consultation_drafts') {
                $hasMirror = $pairs[$table][0]['has_mirror'];
                DB::table($table)->orderBy('id')->chunkById(100, function ($drafts) use ($encrypter, $hasMirror): void {
                    foreach ($drafts as $draft) {
                        $source = (string) ($draft->payload ?? '[]');
                        $sourceCipher = $this->decryptDraftCiphertext($source, $encrypter);
                        $ciphertext = $hasMirror && ($draft->payload_encrypted ?? null) !== null
                            ? (string) $draft->payload_encrypted
                            : ($sourceCipher !== null ? $source : $encrypter->encrypt(json_decode($source, true) ?: [], true));

                        DB::table('consultation_drafts')->where('id', $draft->id)->update([
                            'payload_encrypted' => $ciphertext,
                            'payload' => json_encode([], JSON_THROW_ON_ERROR),
                        ]);
                    }
                });

                continue;
            }

            foreach ($fields as $field) {
                DB::table($table)
                    ->whereNotNull($field['source'])
                    ->update([$field['mirror'] => DB::raw(DB::connection()->getQueryGrammar()->wrap($field['source']))]);
                DB::table($table)->whereNotNull($field['source'])->update([$field['source'] => null]);
            }
        }
    }

    private function restoreDependentIndexes(): void
    {
        $table = 'dependents_profiles';
        if (!Schema::hasTable($table)) {
            return;
        }

        $existing = Schema::getIndexListing($table);
        foreach ([
            'idp_user_id' => 'dependents_profiles_idp_user_id_index',
            'id_number' => 'dependents_profiles_id_number_index',
        ] as $column => $name) {
            if (!Schema::hasColumn($table, $column) || in_array($name, $existing, true)) {
                continue;
            }

            if (in_array(DB::getDriverName(), ['mysql', 'mariadb'], true)) {
                $grammar = DB::connection()->getQueryGrammar();
                DB::statement(sprintf(
                    'CREATE INDEX %s ON %s (%s(191))',
                    $grammar->wrap($name),
                    $grammar->wrapTable($table),
                    $grammar->wrap($column)
                ));
            } else {
                Schema::table($table, static function (Blueprint $blueprint) use ($column, $name): void {
                    $blueprint->index($column, $name);
                });
            }
        }
    }

    private function decryptDraftCiphertext(string $value, HealthDataEncrypter $encrypter): ?array
    {
        try {
            $payload = $encrypter->decryptWithDedicatedKey($value, true);

            return is_array($payload) ? $payload : null;
        } catch (DecryptException) {
            return null;
        }
    }
};
