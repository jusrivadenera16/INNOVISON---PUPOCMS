<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class MarClearanceTypeSource extends Model
{
    protected $fillable = ['mar_clearance_type_id', 'source'];

    public function clearanceType(): BelongsTo
    {
        return $this->belongsTo(MarClearanceType::class, 'mar_clearance_type_id');
    }
}
