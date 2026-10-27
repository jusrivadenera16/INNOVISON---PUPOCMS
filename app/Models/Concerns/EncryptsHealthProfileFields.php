<?php

namespace App\Models\Concerns;

use App\Casts\EncryptedHealthData;
use DateTimeInterface;
use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Support\Facades\Schema;

trait EncryptsHealthProfileFields
{
    private ?array $healthEncryptionColumnListing = null;

    public function getCasts()
    {
        $casts = parent::getCasts();
        $columns = $this->healthEncryptionColumnListing();

        foreach ($this->encryptedHealthFieldMap() as $sourceColumn => $encryptedColumn) {
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

        $encryptedValue = parent::getAttribute($encryptedColumn);
        if ($encryptedValue === null) {
            return parent::getAttribute($key);
        }

        $sourceCast = strtolower((string) (parent::getCasts()[$key] ?? ''));
        if (in_array($sourceCast, ['array', 'json', 'object', 'collection'], true)
            && !is_string($encryptedValue)) {
            return $encryptedValue;
        }

        return $sourceCast !== '' ? $this->castAttribute($key, $encryptedValue) : $encryptedValue;
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

    private function healthEncryptionColumnListing(): array
    {
        if ($this->healthEncryptionColumnListing !== null) {
            return $this->healthEncryptionColumnListing;
        }

        $table = $this->getTable();
        if (!Schema::hasTable($table)) {
            return $this->healthEncryptionColumnListing = [];
        }

        return $this->healthEncryptionColumnListing = Schema::getColumnListing($table);
    }
}
