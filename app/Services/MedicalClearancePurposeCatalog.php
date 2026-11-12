<?php

namespace App\Services;

use App\Models\{EmployeeHealthProfile, HealthProfile, MarClearanceSourceMapping, MarClearanceSubcategorySource};
use Carbon\Carbon;
use Illuminate\Support\Collection;

class MedicalClearancePurposeCatalog
{
    public const SOURCES = ['student_health_form_category', 'employee_health_form_category'];

    public function options(string $type, HealthProfile|EmployeeHealthProfile $profile, ?string $date = null): Collection
    {
        $audience = $type === 'student' ? 'student' : ($profile->user?->clinicAccountTypeKey() === 'faculty' ? 'faculty' : 'admin');
        $workflow = $type === 'student' ? MarClearanceSubcategorySource::STUDENT_NURSE_REVIEW : MarClearanceSubcategorySource::EMPLOYEE_NURSE_REVIEW;
        $day = Carbon::parse($date ?: now()->toDateString())->startOfDay();

        return MarClearanceSourceMapping::query()->where('is_active', true)
            ->where('source_key', $type === 'student' ? self::SOURCES[0] : self::SOURCES[1])
            ->with(['sourceCategory', 'clearanceType.sources', 'subcategory.clearanceType.sources', 'subcategory.sources'])
            ->orderBy('id')->get()->filter(function ($mapping) use ($audience, $workflow, $day, $type) {
                $parent = $mapping->subcategory?->clearanceType ?: $mapping->clearanceType;
                $category = $mapping->sourceCategory;
                $sources = $mapping->subcategory ? $mapping->subcategory->sources : $parent?->sources;
                if (!$parent?->is_active || (!$mapping->subcategory && !$parent->allow_direct_use)
                    || !$category?->is_active || !in_array($audience, $category->available_for ?: [], true)
                    || !$sources?->contains('source', $workflow)) return false;
                if ($mapping->effective_from && $day->lt($mapping->effective_from->startOfDay())) return false;
                if ($mapping->effective_until && $day->gt($mapping->effective_until->endOfDay())) return false;
                $name = $mapping->subcategory?->name ?: $parent->name;
                return $type !== 'employee' || !preg_match('/\b(ojt|on[ -]the[ -]job)\b/i', $name . ' ' . $category->name);
            })->map(function ($mapping) use ($workflow) {
                $parent = $mapping->subcategory?->clearanceType ?: $mapping->clearanceType;
                $name = $mapping->subcategory?->name ?: $parent->name;
                return ['value' => 'mapping:' . $mapping->id, 'label' => $name, 'name' => $name,
                    'clearanceType' => $parent, 'subcategory' => $mapping->subcategory, 'workflow' => $workflow];
            })->values()->pipe(function ($options) {
                return $options->map(function ($option) use ($options) {
                    if ($options->where('name', $option['name'])->count() > 1) {
                        $option['label'] .= ' - ' . $option['clearanceType']->name;
                    }
                    return $option;
                });
            });
    }

    public function resolve(string $type, HealthProfile|EmployeeHealthProfile $profile, string $value): ?array
    {
        $options = $this->options($type, $profile);
        if (str_starts_with($value, 'mapping:')) return $options->firstWhere('value', $value);
        // Accept older open review forms only when their label identifies exactly one target.
        $matches = $options->filter(fn ($option) => mb_strtolower(trim($option['name'])) === mb_strtolower(trim($value)));
        return $matches->count() === 1 ? $matches->first() : null;
    }
}
