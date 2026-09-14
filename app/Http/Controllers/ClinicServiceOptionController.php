<?php

namespace App\Http\Controllers;

use App\Models\Appointment;
use App\Models\ClinicServiceOption;
use App\Models\Consultation;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class ClinicServiceOptionController extends Controller
{
    private const GROUP_LABELS = [
        ClinicServiceOption::GROUP_REFERRAL => 'Referral Services',
        ClinicServiceOption::GROUP_OTHER_SERVICE => 'Other Services',
        ClinicServiceOption::GROUP_ONLINE_CONSULTATION => 'Online Consultation',
    ];

    public function referrals()
    {
        return $this->index(ClinicServiceOption::GROUP_REFERRAL, [
            'title' => 'Manage Referral Services',
            'description' => 'Manage the referral choices available when recording a clinic consultation.',
            'addLabel' => 'Add Referral',
            'icon' => 'arrow-long-right',
        ]);
    }

    public function otherServices()
    {
        return $this->index(ClinicServiceOption::GROUP_OTHER_SERVICE, [
            'title' => 'Manage Other Services',
            'description' => 'Manage additional clinic services that can be selected for appointments and consultations.',
            'addLabel' => 'Add Service',
            'icon' => 'plus-circle',
        ]);
    }

    public function onlineConsultations()
    {
        return $this->index(ClinicServiceOption::GROUP_ONLINE_CONSULTATION, [
            'title' => 'Manage Online Consultation',
            'description' => 'Manage the doctors or providers shown for online consultation appointments.',
            'addLabel' => 'Add Provider',
            'icon' => 'globe-alt',
        ]);
    }

    private function index(string $group, array $page): \Illuminate\Contracts\View\View
    {
        $options = ClinicServiceOption::query()
            ->forGroup($group)
            ->orderBy('sort_order')
            ->orderBy('name')
            ->get();

        $view = match ($group) {
            ClinicServiceOption::GROUP_REFERRAL => 'admin.reports.manage-referral-services',
            ClinicServiceOption::GROUP_OTHER_SERVICE => 'admin.reports.manage-other-services',
            default => 'admin.reports.manage-online-consultations',
        };

        return view($view, [
            'options' => $options,
            'optionGroup' => $group,
            'pageTitle' => $page['title'],
            'pageDescription' => $page['description'],
            'addLabel' => $page['addLabel'],
            'pageIcon' => $page['icon'],
            'groupLabel' => self::GROUP_LABELS[$group],
        ]);
    }

    public function store(Request $request, string $optionGroup)
    {
        $group = $this->validateGroup($optionGroup);
        $validated = $this->validatedOption($request, $group);

        ClinicServiceOption::create([
            'option_group' => $group,
            'code' => $this->uniqueCode($group, $validated['name']),
            'name' => $validated['name'],
            'sort_order' => $validated['sort_order'],
            'is_active' => $validated['is_active'],
        ]);

        return back()->with('status', self::GROUP_LABELS[$group] . ' added.');
    }

    public function update(Request $request, string $optionGroup, ClinicServiceOption $option)
    {
        $group = $this->validateGroup($optionGroup);
        abort_unless($option->option_group === $group, 404);

        $validated = $this->validatedOption($request, $group, $option);
        if ($validated['name'] !== $option->name && $this->hasLinkedRecords($option)) {
            return back()->withInput()->withErrors([
                'name' => 'This option is already used by saved records. Archive it and add a new option instead of renaming it.',
            ]);
        }

        $option->update([
            'name' => $validated['name'],
            'sort_order' => $validated['sort_order'],
            'is_active' => $validated['is_active'],
        ]);

        return back()->with('status', self::GROUP_LABELS[$group] . ' updated.');
    }

    public function destroy(string $optionGroup, ClinicServiceOption $option)
    {
        $group = $this->validateGroup($optionGroup);
        abort_unless($option->option_group === $group, 404);

        if ($this->hasLinkedRecords($option)) {
            $option->update(['is_active' => false]);

            return back()->with('status', self::GROUP_LABELS[$group] . ' archived because it is used by saved records.');
        }

        $option->delete();

        return back()->with('status', self::GROUP_LABELS[$group] . ' removed.');
    }

    private function validateGroup(string $group): string
    {
        abort_unless(array_key_exists($group, self::GROUP_LABELS), 404);

        return $group;
    }

    private function validatedOption(Request $request, string $group, ?ClinicServiceOption $option = null): array
    {
        $validated = $request->validate([
            'name' => [
                'required',
                'string',
                'max:160',
                Rule::unique('clinic_service_options', 'name')
                    ->where(fn ($query) => $query->where('option_group', $group))
                    ->ignore($option?->id),
            ],
            'sort_order' => ['nullable', 'integer', 'min:0', 'max:9999'],
            'is_active' => ['nullable', 'boolean'],
        ]);

        $validated['name'] = trim($validated['name']);
        $validated['sort_order'] = (int) ($validated['sort_order'] ?? 0);
        $validated['is_active'] = $request->boolean('is_active', true);

        return $validated;
    }

    private function uniqueCode(string $group, string $name): string
    {
        $base = Str::slug($name, '_') ?: 'option';
        $code = $base;
        $counter = 2;

        while (ClinicServiceOption::query()->forGroup($group)->where('code', $code)->exists()) {
            $code = $base . '_' . $counter++;
        }

        return $code;
    }

    private function hasLinkedRecords(ClinicServiceOption $option): bool
    {
        if ($option->option_group === ClinicServiceOption::GROUP_REFERRAL) {
            return Consultation::query()->where('referral_type', $option->code)->exists();
        }

        return Appointment::query()->where('service', $option->serviceLabel())->exists()
            || Consultation::query()->where('service', $option->serviceLabel())->exists();
    }
}
