<?php

namespace App\Http\Controllers;

use App\Models\Consultation;
use App\Models\HealthFormCategory;
use App\Models\MarClearanceIssuance;
use App\Models\MarClearanceSubcategory;
use App\Models\MarClearanceSubcategorySource;
use App\Models\MarClearanceType;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class MarClearanceTypeController extends Controller
{
    private const DATA_SOURCE_KEYS = [
        'freshmen_applicants',
        'health_form_category',
        'consultation',
        'patient_intake',
    ];

    private const APPLICANT_FILTER_OPTIONS = [
        'final_review_result' => [
            'label' => 'Final Review Result',
            'values' => [
                'with_findings' => 'With Findings',
                'no_findings' => 'No Findings / Normal',
            ],
        ],
        'medical_condition' => [
            'label' => 'Medical Condition',
            'values' => [
                'with_condition' => 'With Medical Condition',
                'without_condition' => 'No Medical Condition',
            ],
        ],
        'pwd_status' => [
            'label' => 'PWD Status',
            'values' => [
                'pwd' => 'PWD',
                'not_pwd' => 'Not PWD',
            ],
        ],
        'pwd_document' => [
            'label' => 'PWD Document',
            'values' => [
                'submitted' => 'PWD Document Submitted',
                'not_submitted' => 'PWD Document Not Submitted',
            ],
        ],
        'covid_status' => [
            'label' => 'COVID-19 Status',
            'values' => [
                'positive' => 'COVID Positive',
                'negative' => 'COVID Negative',
            ],
        ],
        'medical_certificate_result' => [
            'label' => 'Medical Certificate Result',
            'values' => [
                'with_findings' => 'With Findings',
                'no_findings' => 'No Findings / Normal',
                'not_sure' => 'Not Sure / For Clinic Review',
            ],
        ],
        'chest_xray_result' => [
            'label' => 'Chest X-ray Result',
            'values' => [
                'with_findings' => 'With Findings',
                'normal' => 'Normal',
                'not_sure' => 'Not Sure / For Clinic Review',
            ],
        ],
    ];

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

    public function dataSourceLabels(): array
    {
        return [
            'freshmen_applicants' => 'Freshmen Applicants',
            'health_form_category' => 'Health Form Categories',
            'consultation' => 'Consultation Records',
            'patient_intake' => 'Patient Intake Records',
        ];
    }

    public function index()
    {
        $clearanceTypes = MarClearanceType::query()
            ->where('is_active', true)
            ->with([
                'sources',
                'sourceMappings.sourceCategory',
                'subcategories.sources',
                'subcategories.sourceMappings.sourceCategory',
            ])
            ->orderBy('sort_order')
            ->orderBy('name')
            ->get();

        $sourceLabels = $this->subcategorySources();
        $dataSourceLabels = $this->dataSourceLabels();
        $healthFormCategories = HealthFormCategory::query()
            ->where('is_active', true)
            ->orderBy('name')
            ->get();
        $applicantFilterOptions = self::APPLICANT_FILTER_OPTIONS;

        return view('admin.reports.manage-clearance-types', compact(
            'clearanceTypes',
            'sourceLabels',
            'dataSourceLabels',
            'healthFormCategories',
            'applicantFilterOptions'
        ));
    }

    public function store(Request $request)
    {
        $validated = $request->validate(array_merge([
            'name' => ['required', 'string', 'max:160'],
            'count_placement' => ['required', Rule::in(['parent', 'subcategories'])],
        ], $this->dataSourceRules($request->input('count_placement') === 'parent')));

        $name = trim($validated['name']);
        $existingType = MarClearanceType::query()
            ->whereRaw('LOWER(TRIM(name)) = ?', [mb_strtolower($name)])
            ->first();
        if ($existingType?->is_active) {
            return back()->withInput()->withErrors(['name' => 'This clearance type already exists.']);
        }

        $parentReceivesCount = $validated['count_placement'] === 'parent';
        $sourceKey = trim((string) ($validated['source_key'] ?? ''));
        $sources = $this->workflowSourcesForDataSource($sourceKey);

        if ($existingType) {
            DB::transaction(function () use ($existingType, $name, $parentReceivesCount, $sources, $validated) {
                $existingType->update([
                    'name' => $name,
                    'sort_order' => (int) MarClearanceType::max('sort_order') + 1,
                    'is_active' => true,
                    'allow_direct_use' => $parentReceivesCount,
                ]);

                $this->syncTypeSources($existingType, $parentReceivesCount ? $sources : []);
                $this->syncTypeSourceMapping($existingType, $parentReceivesCount ? $validated : null);
            });

            return back()->with('success', 'Archived clearance type restored.');
        }

        $baseCode = Str::snake(Str::limit($name, 45, '')) ?: 'clearance_type';
        $code = $baseCode;
        $suffix = 2;
        while (MarClearanceType::query()->where('code', $code)->exists()) {
            $code = $baseCode . '_' . $suffix++;
        }

        DB::transaction(function () use ($code, $name, $parentReceivesCount, $sources, $validated) {
            $clearanceType = MarClearanceType::create([
                'code' => $code,
                'name' => $name,
                'sort_order' => (int) MarClearanceType::max('sort_order') + 1,
                'is_active' => true,
                'allow_direct_use' => $parentReceivesCount,
            ]);

            $this->syncTypeSources($clearanceType, $parentReceivesCount ? $sources : []);
            $this->syncTypeSourceMapping($clearanceType, $parentReceivesCount ? $validated : null);
        });

        return back()->with('success', 'Clearance type added.');
    }

    public function update(Request $request, MarClearanceType $marClearanceType)
    {
        $validated = $request->validate(array_merge([
            'name' => ['required', 'string', 'max:160'],
            'sort_order' => ['nullable', 'integer', 'min:1', 'max:999'],
            'count_placement' => ['required', Rule::in(['parent', 'subcategories'])],
        ], $this->dataSourceRules($request->input('count_placement') === 'parent')));

        $name = trim($validated['name']);
        $exists = MarClearanceType::query()
            ->where('id', '!=', $marClearanceType->id)
            ->whereRaw('LOWER(TRIM(name)) = ?', [mb_strtolower($name)])
            ->exists();
        if ($exists) {
            return back()->withInput()->withErrors(['name' => 'This clearance type already exists.']);
        }

        $parentReceivesCount = $validated['count_placement'] === 'parent';
        $sourceKey = trim((string) ($validated['source_key'] ?? ''));
        $sources = $this->workflowSourcesForDataSource($sourceKey);

        DB::transaction(function () use ($marClearanceType, $name, $validated, $parentReceivesCount, $sources) {
            $marClearanceType->update([
                'name' => $name,
                'sort_order' => $validated['sort_order'] ?? $marClearanceType->sort_order,
                'allow_direct_use' => $parentReceivesCount,
            ]);
            $this->syncTypeSources($marClearanceType, $parentReceivesCount ? $sources : []);
            $this->syncTypeSourceMapping($marClearanceType, $parentReceivesCount ? $validated : null);
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
        $validated = $request->validate(array_merge([
            'name' => ['required', 'string', 'max:160'],
        ], $this->dataSourceRules(true)));

        $name = trim($validated['name']);
        $exists = $marClearanceType->subcategories()
            ->whereRaw('LOWER(TRIM(name)) = ?', [mb_strtolower($name)])
            ->exists();

        if ($exists) {
            return back()->withInput()->withErrors(['name' => 'This subcategory already exists under the selected clearance type.']);
        }

        $this->createSubcategory($marClearanceType, $validated);

        return back()->with('success', 'Clearance subcategory added.');
    }

    public function storeConsultationSubcategory(Request $request, MarClearanceType $marClearanceType)
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:160'],
        ]);

        $name = trim($validated['name']);
        $exists = $marClearanceType->subcategories()
            ->whereRaw('LOWER(TRIM(name)) = ?', [mb_strtolower($name)])
            ->exists();

        if ($exists) {
            return response()->json([
                'message' => 'This subcategory already exists under the selected clearance type.',
                'errors' => ['name' => ['This subcategory already exists under the selected clearance type.']],
            ], 422);
        }

        $validated['source_key'] = 'consultation';
        $subcategory = $this->createSubcategory($marClearanceType, $validated);

        return response()->json([
            'message' => 'Consultation subcategory added.',
            'subcategory' => [
                'id' => $subcategory->id,
                'code' => $subcategory->code,
                'name' => $subcategory->name,
                'label' => $marClearanceType->name . ' - ' . $subcategory->name,
                'clearance_type_id' => $marClearanceType->id,
                'clearance_type_name' => $marClearanceType->name,
            ],
        ], 201);
    }

    private function createSubcategory(MarClearanceType $marClearanceType, array $validated): MarClearanceSubcategory
    {
        $name = trim($validated['name']);
        $baseCode = Str::snake(Str::limit($marClearanceType->code . '_' . $name, 90, '')) ?: 'clearance_subcategory';
        $code = $baseCode;
        $suffix = 2;
        while (MarClearanceSubcategory::query()->where('code', $code)->exists()) {
            $code = $baseCode . '_' . $suffix++;
        }

        return DB::transaction(function () use ($marClearanceType, $code, $name, $validated): MarClearanceSubcategory {
            $subcategory = $marClearanceType->subcategories()->create([
                'code' => $code,
                'name' => $name,
                'sort_order' => (int) $marClearanceType->subcategories()->max('sort_order') + 1,
            ]);

            $this->syncSubcategorySources(
                $subcategory,
                $this->workflowSourcesForDataSource((string) $validated['source_key'])
            );
            $this->syncSubcategorySourceMapping($subcategory, $validated);

            return $subcategory;
        });
    }

    public function updateSubcategory(Request $request, MarClearanceSubcategory $marClearanceSubcategory)
    {
        $validated = $request->validate(array_merge([
            'name' => ['required', 'string', 'max:160'],
        ], $this->dataSourceRules(true)));

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
            $this->syncSubcategorySources(
                $marClearanceSubcategory,
                $this->workflowSourcesForDataSource((string) $validated['source_key'])
            );
            $this->syncSubcategorySourceMapping($marClearanceSubcategory, $validated);
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

    private function workflowSourcesForDataSource(?string $sourceKey): array
    {
        return match (trim((string) $sourceKey)) {
            'freshmen_applicants' => [MarClearanceSubcategorySource::APPLICANT_FINAL_REVIEW],
            'health_form_category' => [
                MarClearanceSubcategorySource::STUDENT_NURSE_REVIEW,
                MarClearanceSubcategorySource::EMPLOYEE_NURSE_REVIEW,
            ],
            'consultation' => [MarClearanceSubcategorySource::CONSULTATION],
            'patient_intake' => [MarClearanceSubcategorySource::PATIENT_INTAKE],
            default => [],
        };
    }

    private function dataSourceRules(bool $required): array
    {
        return array_merge([
            'source_key' => [
                $required ? 'required' : 'nullable',
                'string',
                Rule::in(self::DATA_SOURCE_KEYS),
            ],
            'source_category_id' => [
                'nullable',
                'integer',
                'required_if:source_key,health_form_category',
                Rule::exists('health_form_categories', 'id')
                    ->where(fn ($query) => $query->where('is_active', true)),
            ],
        ], $this->applicantFilterRules());
    }

    private function applicantFilterRules(): array
    {
        $rules = [
            'applicant_filters' => ['nullable', 'array'],
        ];

        foreach (self::APPLICANT_FILTER_OPTIONS as $filterKey => $filter) {
            $rules['applicant_filters.' . $filterKey] = [
                'nullable',
                Rule::in(array_keys($filter['values'])),
            ];
        }

        return $rules;
    }

    private function sourceMappingAttributes(array $validated): array
    {
        $sourceKey = trim((string) ($validated['source_key'] ?? ''));

        return [
            'source_key' => $sourceKey,
            'source_category_id' => $sourceKey === 'health_form_category'
                ? ($validated['source_category_id'] ?? null)
                : null,
            'source_config' => $sourceKey === 'freshmen_applicants'
                ? $this->applicantSourceConfig($validated['applicant_filters'] ?? [])
                : null,
            'is_active' => true,
        ];
    }

    private function applicantSourceConfig(array $filters): ?array
    {
        $filters = collect($filters)
            ->filter(fn ($value, $key) => isset(self::APPLICANT_FILTER_OPTIONS[$key]) && trim((string) $value) !== '')
            ->map(fn ($value) => trim((string) $value))
            ->all();

        return $filters === [] ? null : ['filters' => $filters];
    }

    private function syncTypeSourceMapping(MarClearanceType $clearanceType, ?array $validated): void
    {
        $clearanceType->sourceMappings()->delete();

        if (!$validated || trim((string) ($validated['source_key'] ?? '')) === '') {
            return;
        }

        $clearanceType->sourceMappings()->create($this->sourceMappingAttributes($validated));
    }

    private function syncSubcategorySourceMapping(MarClearanceSubcategory $subcategory, array $validated): void
    {
        $subcategory->sourceMappings()->delete();
        $subcategory->sourceMappings()->create($this->sourceMappingAttributes($validated));
    }
}
