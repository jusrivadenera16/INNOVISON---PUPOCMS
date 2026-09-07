<?php

namespace App\Http\Controllers;

use App\Models\Consultation;
use App\Models\MarClearanceIssuance;
use App\Models\MarClearanceSubcategory;
use App\Models\MarClearanceSubcategorySource;
use App\Models\MarClearanceType;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class MarClearanceTypeController extends Controller
{
    private const SUBCATEGORY_SOURCES = [
        MarClearanceSubcategorySource::APPLICANT_FINAL_REVIEW,
        MarClearanceSubcategorySource::STUDENT_NURSE_REVIEW,
        MarClearanceSubcategorySource::EMPLOYEE_NURSE_REVIEW,
        MarClearanceSubcategorySource::PATIENT_INTAKE,
        MarClearanceSubcategorySource::CONSULTATION,
    ];

    public function subcategorySources(): array
    {
        return [
            MarClearanceSubcategorySource::APPLICANT_FINAL_REVIEW => 'Applicant Final Review',
            MarClearanceSubcategorySource::STUDENT_NURSE_REVIEW => 'Student Nurse Review',
            MarClearanceSubcategorySource::EMPLOYEE_NURSE_REVIEW => 'Employee Nurse Review',
            MarClearanceSubcategorySource::PATIENT_INTAKE => 'Patient Intake',
            MarClearanceSubcategorySource::CONSULTATION => 'Consultation',
        ];
    }

    public function index()
    {
        $clearanceTypes = MarClearanceType::query()
            ->where('is_active', true)
            ->with(['sources', 'subcategories.sources'])
            ->orderBy('sort_order')
            ->orderBy('name')
            ->get();

        $sourceLabels = $this->subcategorySources();

        return view('admin.reports.manage-clearance-types', compact('clearanceTypes', 'sourceLabels'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:160'],
            'allow_direct_use' => ['nullable', 'boolean'],
            'sources' => ['nullable', 'array'],
            'sources.*' => ['string', 'in:' . implode(',', self::SUBCATEGORY_SOURCES)],
        ]);

        $name = trim($validated['name']);
        $existingType = MarClearanceType::query()
            ->whereRaw('LOWER(TRIM(name)) = ?', [mb_strtolower($name)])
            ->first();
        if ($existingType?->is_active) {
            return back()->withInput()->withErrors(['name' => 'This clearance type already exists.']);
        }

        $allowDirectUse = $request->boolean('allow_direct_use');
        $sources = collect($validated['sources'] ?? [])->unique()->values()->all();
        if ($allowDirectUse && $sources === []) {
            return back()->withInput()->withErrors(['sources' => 'Select at least one workflow for direct parent use.']);
        }

        if ($existingType) {
            DB::transaction(function () use ($existingType, $name, $allowDirectUse, $sources) {
                $existingType->update([
                    'name' => $name,
                    'sort_order' => (int) MarClearanceType::max('sort_order') + 1,
                    'is_active' => true,
                    'allow_direct_use' => $allowDirectUse,
                ]);

                $this->syncTypeSources($existingType, $allowDirectUse ? $sources : []);
            });

            return back()->with('success', 'Archived clearance type restored.');
        }

        $baseCode = Str::snake(Str::limit($name, 45, '')) ?: 'clearance_type';
        $code = $baseCode;
        $suffix = 2;
        while (MarClearanceType::query()->where('code', $code)->exists()) {
            $code = $baseCode . '_' . $suffix++;
        }

        DB::transaction(function () use ($code, $name, $allowDirectUse, $sources) {
            $clearanceType = MarClearanceType::create([
                'code' => $code,
                'name' => $name,
                'sort_order' => (int) MarClearanceType::max('sort_order') + 1,
                'is_active' => true,
                'allow_direct_use' => $allowDirectUse,
            ]);

            $this->syncTypeSources($clearanceType, $sources);
        });

        return back()->with('success', 'Clearance type added.');
    }

    public function update(Request $request, MarClearanceType $marClearanceType)
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:160'],
            'sort_order' => ['nullable', 'integer', 'min:1', 'max:999'],
            'allow_direct_use' => ['nullable', 'boolean'],
            'sources' => ['nullable', 'array'],
            'sources.*' => ['string', 'in:' . implode(',', self::SUBCATEGORY_SOURCES)],
        ]);

        $name = trim($validated['name']);
        $exists = MarClearanceType::query()
            ->where('id', '!=', $marClearanceType->id)
            ->whereRaw('LOWER(TRIM(name)) = ?', [mb_strtolower($name)])
            ->exists();
        if ($exists) {
            return back()->withInput()->withErrors(['name' => 'This clearance type already exists.']);
        }

        $allowDirectUse = $request->boolean('allow_direct_use');
        $sources = collect($validated['sources'] ?? [])->unique()->values()->all();
        if ($allowDirectUse && $sources === []) {
            return back()->withInput()->withErrors(['sources' => 'Select at least one workflow for direct parent use.']);
        }

        DB::transaction(function () use ($marClearanceType, $name, $validated, $allowDirectUse, $sources) {
            $marClearanceType->update([
                'name' => $name,
                'sort_order' => $validated['sort_order'] ?? $marClearanceType->sort_order,
                'allow_direct_use' => $allowDirectUse,
            ]);
            $this->syncTypeSources($marClearanceType, $allowDirectUse ? $sources : []);
        });

        return back()->with('success', 'Clearance type updated.');
    }

    public function destroy(MarClearanceType $marClearanceType)
    {
        $subcategoryIds = $marClearanceType->subcategories()->pluck('id');
        $linkedCodes = $marClearanceType->subcategories()->pluck('code')->push($marClearanceType->code);
        if ($marClearanceType->code === 'ojt') {
            $linkedCodes->push('coc_ijt');
        }

        $hasLinkedIssuances = MarClearanceIssuance::query()
            ->where(function ($query) use ($marClearanceType, $subcategoryIds) {
                $query->where('clearance_type_id', $marClearanceType->id)
                    ->orWhereIn('clearance_subcategory_id', $subcategoryIds);
            })
            ->exists();

        if ($hasLinkedIssuances || Consultation::query()->whereIn('certificate_type', $linkedCodes)->exists()) {
            return back()->with('error', 'This clearance type cannot be deleted because existing records are linked to it.');
        }

        $marClearanceType->delete();
        return back()->with('success', 'Clearance type removed.');
    }

    public function storeSubcategory(Request $request, MarClearanceType $marClearanceType)
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:160'],
            'sources' => ['required', 'array', 'min:1'],
            'sources.*' => ['string', 'in:' . implode(',', self::SUBCATEGORY_SOURCES)],
        ]);

        $name = trim($validated['name']);
        $exists = $marClearanceType->subcategories()
            ->whereRaw('LOWER(TRIM(name)) = ?', [mb_strtolower($name)])
            ->exists();

        if ($exists) {
            return back()->withInput()->withErrors(['name' => 'This subcategory already exists under the selected clearance type.']);
        }

        $baseCode = Str::snake(Str::limit($marClearanceType->code . '_' . $name, 90, '')) ?: 'clearance_subcategory';
        $code = $baseCode;
        $suffix = 2;
        while (MarClearanceSubcategory::query()->where('code', $code)->exists()) {
            $code = $baseCode . '_' . $suffix++;
        }

        DB::transaction(function () use ($marClearanceType, $code, $name, $validated) {
            $subcategory = $marClearanceType->subcategories()->create([
                'code' => $code,
                'name' => $name,
                'sort_order' => (int) $marClearanceType->subcategories()->max('sort_order') + 1,
            ]);

            $this->syncSubcategorySources($subcategory, $validated['sources']);
        });

        return back()->with('success', 'Clearance subcategory added.');
    }

    public function updateSubcategory(Request $request, MarClearanceSubcategory $marClearanceSubcategory)
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:160'],
            'sources' => ['required', 'array', 'min:1'],
            'sources.*' => ['string', 'in:' . implode(',', self::SUBCATEGORY_SOURCES)],
        ]);

        $name = trim($validated['name']);
        $exists = MarClearanceSubcategory::query()
            ->where('mar_clearance_type_id', $marClearanceSubcategory->mar_clearance_type_id)
            ->where('id', '!=', $marClearanceSubcategory->id)
            ->whereRaw('LOWER(TRIM(name)) = ?', [mb_strtolower($name)])
            ->exists();

        if ($exists) {
            return back()->withInput()->withErrors(['name' => 'This subcategory already exists under the selected clearance type.']);
        }

        DB::transaction(function () use ($marClearanceSubcategory, $name, $validated) {
            $marClearanceSubcategory->update(['name' => $name]);
            $this->syncSubcategorySources($marClearanceSubcategory, $validated['sources']);
        });

        return back()->with('success', 'Clearance subcategory updated.');
    }

    public function destroySubcategory(MarClearanceSubcategory $marClearanceSubcategory)
    {
        $legacyCertificateTypes = $this->legacyCertificateTypesFor($marClearanceSubcategory);

        if (MarClearanceIssuance::query()->where('clearance_subcategory_id', $marClearanceSubcategory->id)->exists()
            || Consultation::query()->whereIn('certificate_type', $legacyCertificateTypes)->exists()) {
            return back()->with('error', 'This subcategory cannot be removed because it has linked records.');
        }

        $marClearanceSubcategory->delete();

        return back()->with('success', 'Clearance subcategory removed.');
    }

    private function legacyCertificateTypesFor(MarClearanceSubcategory $subcategory): array
    {
        $normalized = strtolower(trim((string) $subcategory->code . ' ' . (string) $subcategory->name));
        $normalized = trim(preg_replace('/[^a-z0-9]+/', ' ', $normalized) ?? $normalized);
        $codes = [$subcategory->code];

        if (str_contains($normalized, 'ojt')
            || str_contains($normalized, 'ijt')
            || str_contains($normalized, 'on the job')) {
            $codes[] = 'coc_ijt';
        }

        if (str_contains($normalized, 'ladderized')) {
            $codes[] = 'coc_ladderized';
        }

        return array_values(array_unique($codes));
    }

    private function syncSubcategorySources(MarClearanceSubcategory $subcategory, array $sources): void
    {
        $subcategory->sources()->delete();
        $subcategory->sources()->createMany(
            collect($sources)->unique()->map(fn (string $source) => ['source' => $source])->all()
        );
    }

    private function syncTypeSources(MarClearanceType $clearanceType, array $sources): void
    {
        $clearanceType->sources()->delete();
        $clearanceType->sources()->createMany(
            collect($sources)->unique()->map(fn (string $source) => ['source' => $source])->all()
        );
    }
}
