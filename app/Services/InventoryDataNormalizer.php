<?php

namespace App\Services;

use Carbon\Carbon;

class InventoryDataNormalizer
{
    private const UNIT_MAPPINGS = [
        'pc' => 'pcs',
        'pcs' => 'pcs',
        'piece' => 'pcs',
        'pieces' => 'pcs',
        'box' => 'box',
        'boxes' => 'box',
        'bottle' => 'bottle',
        'bottles' => 'bottle',
        'gallon' => 'gallon',
        'gallons' => 'gallon',
        'liter' => 'liter',
        'liters' => 'liter',
        'roll' => 'roll',
        'rolls' => 'roll',
        'pack' => 'pack',
        'packs' => 'pack',
        'tube' => 'tube',
        'tubes' => 'tube',
        'vial' => 'vial',
        'vials' => 'vial',
        'strip' => 'strip',
        'strips' => 'strip',
        'ampule' => 'ampule',
        'ampules' => 'ampule',
        'tablet' => 'tablet',
        'tablets' => 'tablet',
        'capsule' => 'capsule',
        'capsules' => 'capsule',
        'ml' => 'ml',
        'milliliter' => 'ml',
        'milliliters' => 'ml',
        'mg' => 'mg',
        'milligram' => 'mg',
        'milligrams' => 'mg',
        'kg' => 'kg',
        'kilogram' => 'kg',
        'kilograms' => 'kg',
        'g' => 'g',
        'gram' => 'g',
        'grams' => 'g',
        'meter' => 'meter',
        'meters' => 'meter',
        'cm' => 'cm',
        'centimeter' => 'cm',
        'centimeters' => 'cm',
        'inch' => 'inch',
        'inches' => 'inch',
        'yard' => 'yard',
        'yards' => 'yard',
        'dozen' => 'dozen',
        'dozens' => 'dozen',
        'pair' => 'pair',
        'pairs' => 'pair',
        'set' => 'set',
        'sets' => 'set',
        'unit' => 'unit',
        'units' => 'unit',
    ];

    public function normalizeDate(string $value): ?string
    {
        $value = trim($value);
        if ($value === '') {
            return null;
        }

        // Try common formats first with direct parsing
        $commonFormats = [
            'Y-m-d', 'm/d/Y', 'd/m/Y', 'm-d-Y', 'd-m-Y',
            'Y-m', 'm/Y', 'd-M-y', 'j-M-y', 'j-M-Y',
            'M Y', 'M d, Y', 'M d Y', 'F Y',
            'd M Y', 'd M y',
        ];

        foreach ($commonFormats as $format) {
            try {
                $parsed = Carbon::createFromFormat($format, $value);
                return $parsed->format('Y-m-d');
            } catch (\Throwable) {
                // Try next format
            }
        }

        // Handle year-only format (e.g., "2030", "2028")
        if (preg_match('/^\d{4}$/', $value)) {
            try {
                $year = (int) $value;
                if ($year >= 1900 && $year <= 2100) {
                    return Carbon::create($year, 1, 1)->format('Y-m-d');
                }
            } catch (\Throwable) {
                // Invalid year
            }
        }

        // Handle partial dates like "May 2028" or "May" or "Oct 2030"
        if (preg_match('/^(jan|feb|mar|apr|may|jun|jul|aug|sep|oct|nov|dec|january|february|march|april|june|july|august|september|october|november|december)\s*(\d{4})?$/i', $value)) {
            try {
                $dateStr = $value;
                if (!preg_match('/\d{4}/', $value)) {
                    $dateStr = $value . ' ' . Carbon::now()->year;
                }
                $parsed = Carbon::createFromFormat('F Y', $dateStr);
                return $parsed->format('Y-m-d');
            } catch (\Throwable) {
                // Try alternative parsing
                try {
                    $parsed = Carbon::parse($value);
                    return $parsed->format('Y-m-d');
                } catch (\Throwable) {
                    // Give up on this date
                }
            }
        }

        // Fallback to Carbon::parse which handles many formats
        try {
            return Carbon::parse($value)->format('Y-m-d');
        } catch (\Throwable) {
            return null;
        }
    }

    public function normalizeExpirationDate(string $value): ?string
    {
        $value = trim($value);
        if ($value === '') {
            return null;
        }

        if (preg_match('/^(\d{4})-(\d{2})$/', $value, $matches)) {
            $year = (int) $matches[1];
            $month = (int) $matches[2];
            if ($year >= 1900 && $year <= 2100 && $month >= 1 && $month <= 12) {
                return Carbon::create($year, $month, 1)->endOfMonth()->toDateString();
            }

            return null;
        }

        if (preg_match('/^(\d{1,2})\/(\d{4})$/', $value, $matches)) {
            $month = (int) $matches[1];
            $year = (int) $matches[2];
            if ($year >= 1900 && $year <= 2100 && $month >= 1 && $month <= 12) {
                return Carbon::create($year, $month, 1)->endOfMonth()->toDateString();
            }

            return null;
        }

        if (preg_match('/^(\d{4})$/', $value, $matches)) {
            $year = (int) $matches[1];
            return $year >= 1900 && $year <= 2100
                ? Carbon::create($year, 12, 31)->toDateString()
                : null;
        }

        if (preg_match('/^([A-Za-z]+)\s+(\d{4})$/', $value, $matches)) {
            $year = (int) $matches[2];
            if ($year < 1900 || $year > 2100) {
                return null;
            }

            foreach (['!F Y', '!M Y'] as $format) {
                try {
                    return Carbon::createFromFormat($format, $value)->endOfMonth()->toDateString();
                } catch (\Throwable) {
                    // Try the other month-name format.
                }
            }

            return null;
        }

        return $this->normalizeDate($value);
    }

    public function normalizeUnit(string $value): string
    {
        if ($value === '') {
            return 'pcs';
        }

        $normalized = strtolower(trim($value));
        $normalized = preg_replace('/[^a-z0-9]/', '', $normalized) ?? $normalized;

        return self::UNIT_MAPPINGS[$normalized] ?? trim($value) ?: 'pcs';
    }

    public function normalizeStockNumber(string $value): string
    {
        return trim($value);
    }

    public function normalizeItemName(string $value): string
    {
        return trim($value);
    }

    public function getDataIssues(array $row): array
    {
        $issues = [];

        // Check for unparseable date
        $dateValue = $row['date_added'] ?? '';
        if ($dateValue !== '' && $this->normalizeDate($dateValue) === null) {
            $issues['date_unparseable'] = true;
        }

        // Check for missing stock number
        if (empty($row['stock_number'])) {
            $issues['missing_stock_number'] = true;
        }

        // Check for non-standard unit
        $unit = $row['unit'] ?? '';
        if ($unit !== '' && !isset(self::UNIT_MAPPINGS[strtolower(preg_replace('/[^a-z0-9]/', '', $unit) ?? '')])) {
            $issues['non_standard_unit'] = true;
        }

        // Check for missing item name
        if (empty($row['name'])) {
            $issues['missing_item_name'] = true;
        }

        return $issues;
    }

    public function formatDataIssues(array $issues): string
    {
        $messages = [];

        if ($issues['date_unparseable'] ?? false) {
            $messages[] = 'Date could not be parsed';
        }
        if ($issues['missing_stock_number'] ?? false) {
            $messages[] = 'Missing stock number';
        }
        if ($issues['non_standard_unit'] ?? false) {
            $messages[] = 'Non-standard unit';
        }
        if ($issues['missing_item_name'] ?? false) {
            $messages[] = 'Missing item name';
        }

        return implode(', ', $messages);
    }
}
