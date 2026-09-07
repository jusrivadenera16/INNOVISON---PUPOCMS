<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class MarClearanceType extends Model
{
    protected $fillable = ['code', 'name', 'sort_order', 'is_active', 'allow_direct_use'];

    protected $casts = [
        'sort_order' => 'integer',
        'is_active' => 'boolean',
        'allow_direct_use' => 'boolean',
    ];

    public function subcategories(): HasMany
    {
        return $this->hasMany(MarClearanceSubcategory::class)->orderBy('sort_order')->orderBy('name');
    }

    public function sources(): HasMany
    {
        return $this->hasMany(MarClearanceTypeSource::class, 'mar_clearance_type_id');
    }

    public function issuances(): HasMany
    {
        return $this->hasMany(MarClearanceIssuance::class, 'clearance_type_id');
    }
}
