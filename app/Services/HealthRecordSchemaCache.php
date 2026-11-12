<?php

namespace App\Services;

use Illuminate\Support\Facades\Schema;

/** Schema metadata only, owned by one read-only Health Records HTTP request. */
class HealthRecordSchemaCache
{
    public const REQUEST_ATTRIBUTE = 'health_record_schema_cache';
    private array $columns = [];

    public function columns(?string $connection, string $table): array
    {
        $key = ($connection ?? config('database.default')) . ':' . $table;
        return $this->columns[$key] ??= Schema::connection($connection)->getColumnListing($table);
    }
}
