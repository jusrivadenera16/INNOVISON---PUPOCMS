<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class HealthPdfExport extends Model
{
    public $incrementing = false;
    protected $keyType = 'string';
    protected $guarded = [];
    protected $casts = [
        'filters' => 'encrypted:array', 'context' => 'encrypted:array', 'expires_at' => 'datetime',
        'source_max_id' => 'integer', 'source_total' => 'integer', 'cursor_id' => 'integer',
        'scanned' => 'integer', 'total' => 'integer', 'rendered' => 'integer',
        'parts' => 'integer', 'manifest_offset' => 'integer',
    ];

    public function terminal(): bool
    {
        return in_array($this->status, ['ready', 'failed'], true);
    }
}
