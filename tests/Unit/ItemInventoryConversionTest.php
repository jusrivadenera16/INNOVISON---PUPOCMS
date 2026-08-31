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

    public function test_fefo_sort_key_places_medicines_without_expiry_last(): void
    {
        $this->assertSame(0, Item::fefoSortKey('2026-09-15'));
        $this->assertSame(1, Item::fefoSortKey(null));
        $this->assertSame(1, Item::fefoSortKey(''));
    }

    public function test_fefo_rule_prioritizes_earliest_expiration_then_name(): void
    {
        $medicines = [
            ['name' => 'Vitamin C', 'expiration_date' => null],
            ['name' => 'Cetirizine', 'expiration_date' => '2026-10-10'],
            ['name' => 'Biogesic', 'expiration_date' => '2026-09-01'],
            ['name' => 'Amoxicillin', 'expiration_date' => '2026-09-01'],
        ];

        usort($medicines, function (array $first, array $second): int {
            return [
                Item::fefoSortKey($first['expiration_date']),
                $first['expiration_date'] ?: '9999-12-31',
                $first['name'],
            ] <=> [
                Item::fefoSortKey($second['expiration_date']),
                $second['expiration_date'] ?: '9999-12-31',
                $second['name'],
            ];
        });

        $this->assertSame(
            ['Amoxicillin', 'Biogesic', 'Cetirizine', 'Vitamin C'],
            array_column($medicines, 'name')
        );
    }
}
