<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class MarClearanceSubcategory extends Model
{
    protected $fillable = ['mar_clearance_type_id', 'code', 'name', 'sort_order'];

    protected $casts = [
        'sort_order' => 'integer',
    ];

    public function clearanceType(): BelongsTo
    {
        return $this->belongsTo(MarClearanceType::class, 'mar_clearance_type_id');
    }

    public function sources(): HasMany
    {
        return $this->hasMany(MarClearanceSubcategorySource::class, 'mar_clearance_subcategory_id');
    }

    public function sourceMappings(): HasMany
    {
        return $this->hasMany(MarClearanceSourceMapping::class, 'mar_clearance_subcategory_id');
    }

    public function issuances(): HasMany
    {
        return $this->hasMany(MarClearanceIssuance::class, 'clearance_subcategory_id');
    }
}
