<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class MarClearanceIssuance extends Model
{
    protected $fillable = [
        'user_id',
        'clearance_type_id',
        'clearance_subcategory_id',
        'source_workflow',
        'source_record_id',
        'user_type',
        'user_name_snapshot',
        'clearance_name_snapshot',
        'approved_at',
    ];

    protected $casts = [
        'approved_at' => 'datetime',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function subcategory(): BelongsTo
    {
        return $this->belongsTo(MarClearanceSubcategory::class, 'clearance_subcategory_id');
    }

    public function clearanceType(): BelongsTo
    {
        return $this->belongsTo(MarClearanceType::class, 'clearance_type_id');
    }
}
