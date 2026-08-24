<?php

namespace Tests\Unit;

use App\Models\Item;
use PHPUnit\Framework\TestCase;

class ItemInventoryConversionTest extends TestCase
{
    public function test_item_without_conversion_uses_its_stock_quantity(): void
    {
        $item = new Item([
            'quantity' => 12,
            'unit' => 'pcs',
        ]);

        $this->assertFalse($item->hasDispensingConversion());
        $this->assertSame(12.0, $item->availableDispensingQuantity());
        $this->assertSame(3.0, $item->convertDispensingQuantityToStockQuantity(3));
    }

    public function test_item_with_conversion_exposes_and_converts_dispensing_quantity(): void
    {
        $item = new Item([
            'quantity' => 2,
            'unit' => 'box',
            'dispensing_unit' => 'tablet',
            'units_per_stock_unit' => 100,
        ]);

        $this->assertTrue($item->hasDispensingConversion());
        $this->assertSame(200.0, $item->availableDispensingQuantity());
        $this->assertSame(0.25, $item->convertDispensingQuantityToStockQuantity(25));
    }

    public function test_packaged_item_requires_conversion_configuration(): void
    {
        $item = new Item([
            'quantity' => 5,
            'unit' => 'bottle',
        ]);

        $this->assertTrue($item->requiresDispensingConversion());
        $this->assertFalse($item->hasDispensingConversion());
    }

    public function test_conversion_preserves_fractional_stock_precision(): void
    {
        $item = new Item([
            'quantity' => 1,
            'unit' => 'box',
            'dispensing_unit' => 'tablet',
            'units_per_stock_unit' => 30,
        ]);

        $this->assertSame(0.033333, $item->convertDispensingQuantityToStockQuantity(1));
    }
}
