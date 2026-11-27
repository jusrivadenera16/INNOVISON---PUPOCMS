<?php

namespace App\Services;

use Illuminate\Support\Collection;

class MarClearanceReportRows
{
    public function build(iterable $types, Collection $issuances, array $details = []): Collection
    {
        $normalizer = app(MarPatientTypeNormalizer::class);
        $row = function ($records, string $key) use ($normalizer, $details): array {
            $counts = array_fill_keys(['student', 'faculty', 'admin', 'dependent'], 0);
            foreach ($records as $record) {
                $type = $normalizer->normalize($record->user_type ?? '');
                if (array_key_exists($type, $counts)) $counts[$type]++;
            }
            return $counts + ['details' => $details[$key] ?? []];
        };
        return collect($types)->map(function ($type) use ($issuances, $row) {
            $parent = $row($issuances->filter(fn ($item) => (int) $item->clearance_type_id === (int) $type->id && $item->clearance_subcategory_id === null), 'type:' . $type->id);
            $children = $type->subcategories->map(function ($child) use ($issuances, $row) {
                return ['name' => $child->report_name ?: $child->name] + $row(
                    $issuances->filter(fn ($item) => (int) $item->clearance_subcategory_id === (int) $child->id),
                    'subcategory:' . $child->id
                );
            })->filter(fn ($item) => $this->total($item) > 0)->values();
            return ['label' => $type->report_name ?: $type->name, 'allow_direct_use' => (bool) $type->allow_direct_use, 'parent' => $parent, 'rows' => $children];
        })->filter(fn ($group) => $this->total($group['parent']) > 0 || $group['rows']->isNotEmpty())
            ->values()->map(function ($group, $index) {
                $group['label'] = $this->letter($index) . '. ' . $group['label'];
                return $group;
            });
    }

    private function total(array $row): int
    {
        return array_sum(array_intersect_key($row, array_flip(['student', 'faculty', 'admin', 'dependent'])));
    }

    private function letter(int $index): string
    {
        $label = '';
        for ($value = $index + 1; $value > 0; $value = intdiv($value, 26)) {
            $value--;
            $label = chr(65 + $value % 26) . $label;
        }
        return $label;
    }
}
