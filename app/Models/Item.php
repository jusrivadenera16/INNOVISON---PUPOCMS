<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;

class Item extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'category',
        'stock_number',
        'medicine_type_id',
        'medicine_type',
        'illness_category_id',
        'quantity',
        'starting_stock',
        'consumed',
        'minimum_stock',
        'unit',
        'dispensing_unit',
        'units_per_stock_unit',
        'batch_number',
        'supplier_source',
        'date_added',      
        'expiration_date',
        'description'
    ];

    protected $casts = [
        'quantity' => 'decimal:6',
        'starting_stock' => 'decimal:6',
        'consumed' => 'decimal:6',
        'minimum_stock' => 'decimal:6',
        'units_per_stock_unit' => 'integer',
        'date_added' => 'date',
        'expiration_date' => 'date',
    ];

    public function illnessCategory(): BelongsTo
    {
        return $this->belongsTo(InventoryIllnessCategory::class, 'illness_category_id');
    }

    public function medicineType(): BelongsTo
    {
        return $this->belongsTo(MedicineType::class, 'medicine_type_id');
    }

    public function movements(): HasMany
    {
        return $this->hasMany(InventoryMovement::class)->latest();
    }

    public function scopeAvailableMedicinesFefo(Builder $query): Builder
    {
        return $query
            ->where('category', 'Medicine')
            ->where('quantity', '>', 0)
            ->where(function (Builder $query): void {
                $query
                    ->whereNull('expiration_date')
                    ->orWhereDate('expiration_date', '>=', now()->toDateString());
            })
            ->orderByRaw('CASE WHEN expiration_date IS NULL THEN 1 ELSE 0 END')
            ->orderBy('expiration_date')
            ->orderBy('name');
    }

    public static function fefoSortKey(?string $expirationDate): int
    {
        return trim((string) $expirationDate) === '' ? 1 : 0;
    }

    public function normalizedUnit(): string
    {
        return Str::lower(trim((string) ($this->unit ?: 'pcs')));
    }

    public function normalizedDispensingUnit(): string
    {
        return Str::lower(trim((string) ($this->dispensing_unit ?: '')));
    }

    public function unitsPerStockUnit(): int
    {
        return max(1, (int) ($this->units_per_stock_unit ?: 1));
    }

    public function hasDispensingConversion(): bool
    {
        return $this->normalizedDispensingUnit() !== ''
            && $this->unitsPerStockUnit() > 1;
    }

    public function requiresDispensingConversion(): bool
    {
        return in_array($this->normalizedUnit(), [
            'box',
            'boxes',
            'pack',
            'packs',
            'bottle',
            'vial',
            'ampule',
            'ampoule',
            'tube',
            'sachet',
        ], true);
    }

    public function availableDispensingQuantity(): float
    {
        $stockQuantity = (float) $this->quantity;

        if ($this->hasDispensingConversion()) {
            return round($stockQuantity * $this->unitsPerStockUnit(), 6);
        }

        return round($stockQuantity, 6);
    }

    public function convertDispensingQuantityToStockQuantity(float $dispensingQuantity): float
    {
        if ($this->hasDispensingConversion()) {
            return round($dispensingQuantity / $this->unitsPerStockUnit(), 6);
        }

        return round($dispensingQuantity, 6);
    }
}
