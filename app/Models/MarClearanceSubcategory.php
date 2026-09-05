<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

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
}
