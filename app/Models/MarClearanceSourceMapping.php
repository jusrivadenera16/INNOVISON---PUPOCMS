<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class MarClearanceSourceMapping extends Model
{
    protected $table = 'mar_mc_source';

    protected $fillable = [
        'mar_clearance_type_id',
        'mar_clearance_subcategory_id',
        'source_key',
        'source_category_id',
        'source_config',
        'effective_from',
        'effective_until',
        'is_active',
    ];

    protected $casts = [
        'source_config' => 'array',
        'effective_from' => 'date',
        'effective_until' => 'date',
        'is_active' => 'boolean',
    ];

    public function clearanceType(): BelongsTo
    {
        return $this->belongsTo(MarClearanceType::class, 'mar_clearance_type_id');
    }

    public function subcategory(): BelongsTo
    {
        return $this->belongsTo(MarClearanceSubcategory::class, 'mar_clearance_subcategory_id');
    }

    public function sourceCategory(): BelongsTo
    {
        return $this->belongsTo(HealthFormCategory::class, 'source_category_id');
    }
}
