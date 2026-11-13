<?php

namespace App\Models\Concerns;

use App\Casts\EncryptedHealthData;
use DateTimeInterface;
use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Support\Facades\Schema;

trait EncryptsHealthProfileFields
{
    private ?array $healthEncryptionColumnListing = null;
    private ?array $healthRecordCastCache = null;
    private array $healthRecordReadCache = [];

    public function getCasts()
    {
        $casts = parent::getCasts();
        $columns = $this->healthEncryptionColumnListing();
        $fieldMap = $this->encryptedHealthFieldMap();
        $cacheKey = [$casts, $fieldMap, config('health_data_encryption.enabled', false), app()->bound('request') ? app('request')->attributes->get(\App\Services\HealthRecordSchemaCache::REQUEST_ATTRIBUTE) : null];
        $cacheReads = $this->usesHealthRecordReadCache();
        if ($cacheReads && ($this->healthRecordCastCache['key'] ?? null) === $cacheKey) {
            return $this->healthRecordCastCache['casts'];
        }

        foreach ($fieldMap as $sourceColumn => $encryptedColumn) {
            if (!in_array($sourceColumn, $columns, true)) {
                continue;
            }

            $sourceCast = strtolower((string) ($casts[$sourceColumn] ?? ''));

            if (in_array($encryptedColumn, $columns, true)) {
                if (array_key_exists($encryptedColumn, $casts)) {
                    continue;
                }

                $mirrorFormat = in_array($sourceCast, ['array', 'json', 'object', 'collection'], true)
                    ? $sourceCast
                    : 'scalar';
                $casts[$encryptedColumn] = EncryptedHealthData::class . ':' . $mirrorFormat;

                continue;
            }

            if (config('health_data_encryption.enabled', false)) {
                $casts[$sourceColumn] = EncryptedHealthData::class . ':' . ($sourceCast !== '' ? $sourceCast : 'scalar');
            }
        }

        if ($cacheReads) $this->healthRecordCastCache = ['key' => $cacheKey, 'casts' => $casts];
        return $casts;
    }

    public function getAttribute($key)
    {
        if (!config('health_data_encryption.enabled', false) || !is_string($key)) {
            return parent::getAttribute($key);
        }

        $encryptedColumn = $this->encryptedHealthFieldMap()[$key] ?? null;
        if ($encryptedColumn === null) {
            return parent::getAttribute($key);
        }

        if (!in_array($encryptedColumn, $this->healthEncryptionColumnListing(), true)) {
            return parent::getAttribute($key);
        }

        // Private, model-local read cache only during Health Records requests. Comparing
        // raw values also invalidates it after forceFill, refresh, or setRawAttributes.
        $raw = [$this->attributes[$encryptedColumn] ?? null, $this->attributes[$key] ?? null, parent::getCasts()[$key] ?? null,
            app()->bound('request') ? app('request')->attributes->get(\App\Services\HealthRecordSchemaCache::REQUEST_ATTRIBUTE) : null];
        $cacheReads = $this->usesHealthRecordReadCache();
        if ($cacheReads && ($this->healthRecordReadCache[$key]['raw'] ?? null) === $raw) {
            return $this->healthRecordReadCache[$key]['value'];
        }
        $encryptedValue = parent::getAttribute($encryptedColumn);
        if ($encryptedValue === null) {
            return parent::getAttribute($key);
        }

        $sourceCast = strtolower((string) (parent::getCasts()[$key] ?? ''));
        if (in_array($sourceCast, ['array', 'json', 'object', 'collection'], true)
            && !is_string($encryptedValue)) {
            if ($cacheReads && !is_object($encryptedValue)) $this->healthRecordReadCache[$key] = ['raw' => $raw, 'value' => $encryptedValue];
            return $encryptedValue;
        }

        $value = $sourceCast !== '' ? $this->castAttribute($key, $encryptedValue) : $encryptedValue;
        if ($cacheReads && !is_object($value)) $this->healthRecordReadCache[$key] = ['raw' => $raw, 'value' => $value];
        return $value;
    }

    public function attributesToArray()
    {
        $attributes = parent::attributesToArray();

        foreach ($this->encryptedHealthFieldMap() as $sourceColumn => $encryptedColumn) {
            unset($attributes[$encryptedColumn]);

            if (!config('health_data_encryption.enabled', false)
                || !array_key_exists($sourceColumn, $attributes)) {
                continue;
            }

            $value = $this->getAttribute($sourceColumn);
            if ($value instanceof DateTimeInterface) {
                $value = $this->serializeDate($value);
            } elseif ($value instanceof Arrayable) {
                $value = $value->toArray();
            }

            $attributes[$sourceColumn] = $value;
        }

        return $attributes;
    }

    protected function getArrayableAttributes()
    {
        $attributes = parent::getArrayableAttributes();

        foreach ($this->encryptedHealthFieldMap() as $encryptedColumn) {
            unset($attributes[$encryptedColumn]);
        }

        return $attributes;
    }

    public function setAttribute($key, $value)
    {
        if (config('health_data_encryption.enabled', false) && is_string($key)) {
            $encryptedColumn = $this->encryptedHealthFieldMap()[$key] ?? null;
            if ($encryptedColumn !== null
                && in_array($encryptedColumn, $this->healthEncryptionColumnListing(), true)) {
                parent::setAttribute($key, $value);
                parent::setAttribute($encryptedColumn, parent::getAttribute($key));
                parent::setAttribute($key, null);

                return $this;
            }
        }

        return parent::setAttribute($key, $value);
    }

    protected function encryptedHealthFieldMap(): array
    {
        return (array) config('health_data_encryption.fields.' . $this->getTable(), []);
    }

    private function usesHealthRecordReadCache(): bool
    {
        return app()->bound('request') && app('request')->attributes->get(\App\Services\HealthRecordSchemaCache::REQUEST_ATTRIBUTE)
            instanceof \App\Services\HealthRecordSchemaCache;
    }

    private function healthEncryptionColumnListing(): array
    {
        if ($this->healthEncryptionColumnListing !== null) {
            return $this->healthEncryptionColumnListing;
        }

        $table = $this->getTable();
        // Listing reads hydrate many models. Share schema metadata only within that request.
        $requestCache = app()->bound('request')
            ? app('request')->attributes->get(\App\Services\HealthRecordSchemaCache::REQUEST_ATTRIBUTE)
            : null;
        if ($requestCache instanceof \App\Services\HealthRecordSchemaCache) {
            return $this->healthEncryptionColumnListing = $requestCache->columns($this->getConnectionName(), $table);
        }
        if (!Schema::hasTable($table)) {
            return $this->healthEncryptionColumnListing = [];
        }

        return $this->healthEncryptionColumnListing = Schema::getColumnListing($table);
    }
}
