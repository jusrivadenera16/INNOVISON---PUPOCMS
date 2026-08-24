<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class InventoryMovement extends Model
{
    use HasFactory;

    protected $fillable = [
        'item_id',
        'user_id',
        'type',
        'movement_date',
        'reason',
        'quantity',
        'stock_before',
        'stock_after',
        'unit',
        'batch_number',
        'supplier_source',
        'notes',
    ];

    protected $casts = [
        'quantity' => 'decimal:6',
        'stock_before' => 'decimal:6',
        'stock_after' => 'decimal:6',
        'movement_date' => 'date',
    ];

    public function item(): BelongsTo
    {
        return $this->belongsTo(Item::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
