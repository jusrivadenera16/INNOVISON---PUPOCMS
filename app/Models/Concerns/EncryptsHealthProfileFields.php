<?php

namespace App\Models\Concerns;

trait EncryptsHealthProfileFields
{
    public function getCasts()
    {
        $casts = parent::getCasts();

        foreach ($this->encryptedHealthFieldMap() as $sourceColumn => $encryptedColumn) {
            if (array_key_exists($encryptedColumn, $casts)) {
                continue;
            }

            $sourceCast = strtolower((string) ($casts[$sourceColumn] ?? ''));
            $casts[$encryptedColumn] = in_array($sourceCast, [
                'array',
                'json',
                'object',
                'collection',
            ], true) ? 'encrypted:' . $sourceCast : 'encrypted';
        }

        return $casts;
    }

    public function getAttribute($key)
    {
        $value = parent::getAttribute($key);

        if (!config('health_data_encryption.enabled', false) || !is_string($key)) {
            return $value;
        }

        $encryptedColumn = $this->encryptedHealthFieldMap()[$key] ?? null;
        if ($encryptedColumn === null) {
            return $value;
        }

        $encryptedValue = parent::getAttribute($encryptedColumn);
        if ($encryptedValue === null) {
            return $value;
        }

        $sourceCast = strtolower((string) (parent::getCasts()[$key] ?? ''));
        if (in_array($sourceCast, ['array', 'json', 'object', 'collection'], true)
            && !is_string($encryptedValue)) {
            return $encryptedValue;
        }

        return $sourceCast !== '' ? $this->castAttribute($key, $encryptedValue) : $encryptedValue;
    }

    public function setAttribute($key, $value)
    {
        $result = parent::setAttribute($key, $value);

        if (config('health_data_encryption.enabled', false) && is_string($key)) {
            $encryptedColumn = $this->encryptedHealthFieldMap()[$key] ?? null;
            if ($encryptedColumn !== null) {
                parent::setAttribute($encryptedColumn, parent::getAttribute($key));
            }
        }

        return $result;
    }

    protected function encryptedHealthFieldMap(): array
    {
        return (array) config('health_data_encryption.fields.' . $this->getTable(), []);
    }
}
