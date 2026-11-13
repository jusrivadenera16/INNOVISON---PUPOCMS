<?php

namespace App\Services;

use App\Models\ClinicServiceOption;
use Illuminate\Support\Collection;

class ConsultationServiceOptions
{
    public function labels(?Collection $options = null): array
    {
        $options ??= ClinicServiceOption::query()->forGroup(ClinicServiceOption::GROUP_OTHER_SERVICE)
            ->active()->orderBy('sort_order')->orderBy('name')->get();
        $labels = $options->map(fn ($option) => trim($option->serviceLabel()))->filter()->values();
        $defaults = ['General Consultation', 'Clinic Document Services'];
        if (!$labels->contains(fn ($label) => strtolower($label) === 'bp monitoring')) $defaults[] = 'BP Monitoring';

        return collect($defaults)->concat($labels)->unique()->values()->all();
    }
}
