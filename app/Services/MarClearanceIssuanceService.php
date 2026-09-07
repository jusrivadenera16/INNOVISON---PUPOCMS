<?php

namespace App\Services;

use App\Models\MarClearanceIssuance;
use App\Models\MarClearanceSubcategory;
use App\Models\MarClearanceSubcategorySource;
use App\Models\MarClearanceType;
use App\Models\User;
use Carbon\CarbonInterface;
use InvalidArgumentException;
use Illuminate\Support\Collection;

class MarClearanceIssuanceService
{
    public function resolveSubcategoryForWorkflow(
        ?string $category,
        string $sourceWorkflow,
        array $additionalAliases = []
    ): ?MarClearanceSubcategory {
        $categoryAliases = $this->buildCategoryAliases($category, $additionalAliases);

        if ($categoryAliases->isEmpty()) {
            return null;
        }

        $subcategories = MarClearanceSubcategory::query()
            ->whereHas('clearanceType', fn ($query) => $query->where('is_active', true))
            ->whereHas('sources', fn ($query) => $query->where('source', $sourceWorkflow))
            ->with('clearanceType')
            ->get();

        return $this->findBestClearanceItem(
            $subcategories,
            $categoryAliases,
            fn (MarClearanceSubcategory $subcategory) => [$subcategory->code, $subcategory->name]
        );
    }

    /**
     * Resolve one report target. A subcategory always wins; a parent can only
     * be used directly when the nurse explicitly enabled it in configuration.
     */
    public function resolveClearanceTargetForWorkflow(
        ?string $category,
        string $sourceWorkflow,
        array $additionalAliases = []
    ): ?array {
        $subcategory = $this->resolveSubcategoryForWorkflow($category, $sourceWorkflow, $additionalAliases);
        if ($subcategory) {
            $subcategory->loadMissing('clearanceType');

            return [
                'clearanceType' => $subcategory->clearanceType,
                'subcategory' => $subcategory,
            ];
        }

        $categoryAliases = $this->buildCategoryAliases($category, $additionalAliases);
        if ($categoryAliases->isEmpty()) {
            return null;
        }

        $clearanceType = MarClearanceType::query()
            ->where('is_active', true)
            ->where('allow_direct_use', true)
            ->whereHas('sources', fn ($query) => $query->where('source', $sourceWorkflow))
            ->get()
            ->pipe(fn (Collection $types) => $this->findBestClearanceItem(
                $types,
                $categoryAliases,
                fn (MarClearanceType $type) => [$type->code, $type->name]
            ));

        return $clearanceType ? [
            'clearanceType' => $clearanceType,
            'subcategory' => null,
        ] : null;
    }

    public function resolveClearanceTargetByCodeForWorkflow(
        ?string $code,
        string $sourceWorkflow
    ): ?array {
        $code = trim((string) $code);
        if ($code === '') {
            return null;
        }

        $subcategory = MarClearanceSubcategory::query()
            ->where('code', $code)
            ->whereHas('clearanceType', fn ($query) => $query->where('is_active', true))
            ->whereHas('sources', fn ($query) => $query->where('source', $sourceWorkflow))
            ->with('clearanceType')
            ->first();
        if ($subcategory) {
            return [
                'clearanceType' => $subcategory->clearanceType,
                'subcategory' => $subcategory,
            ];
        }

        $clearanceType = MarClearanceType::query()
            ->where('code', $code)
            ->where('is_active', true)
            ->where('allow_direct_use', true)
            ->whereHas('sources', fn ($query) => $query->where('source', $sourceWorkflow))
            ->first();

        return $clearanceType ? [
            'clearanceType' => $clearanceType,
            'subcategory' => null,
        ] : null;
    }

    public function syncApprovedHealthProfile(\App\Models\HealthProfile $profile, bool $isApproved): void
    {
        $profile->loadMissing('user');
        $workflows = [
            MarClearanceSubcategorySource::APPLICANT_FINAL_REVIEW,
            MarClearanceSubcategorySource::STUDENT_NURSE_REVIEW,
            MarClearanceSubcategorySource::EMPLOYEE_NURSE_REVIEW,
            MarClearanceSubcategorySource::PATIENT_INTAKE,
        ];

        if (!$isApproved) {
            foreach ($workflows as $workflow) {
                $this->removeForSourceRecord($workflow, (string) $profile->id);
            }

            return;
        }

        $workflow = $this->resolveHealthProfileWorkflow($profile);
        foreach ($workflows as $candidateWorkflow) {
            if ($candidateWorkflow !== $workflow) {
                $this->removeForSourceRecord($candidateWorkflow, (string) $profile->id);
            }
        }

        if ($workflow === null) {
            return;
        }

        $patientType = app(MarPatientTypeNormalizer::class)->normalize(
            $profile->user?->user_type ?: $profile->user?->user_role
        );
        $aliases = $patientType === MarPatientTypeNormalizer::DEPENDENT
            ? ['guest', 'dependent']
            : [];
        $target = $this->resolveClearanceTargetForWorkflow(
            $profile->health_form_category,
            $workflow,
            $aliases
        );

        if (!$target || !$profile->user) {
            return;
        }

        $this->recordApprovedClearanceTarget(
            $profile->user,
            $target['clearanceType'],
            $target['subcategory'],
            $workflow,
            (string) $profile->id,
            $profile->verified_at ?: now()
        );
    }

    public function recordApprovedClearance(
        User $user,
        MarClearanceSubcategory $subcategory,
        string $sourceWorkflow,
        string|int $sourceRecordId,
        CarbonInterface|string|null $approvedAt = null
    ): MarClearanceIssuance {
        return $this->recordApprovedClearanceSnapshot(
            $user,
            $subcategory,
            $sourceWorkflow,
            $sourceRecordId,
            $approvedAt,
            $user->user_type ?: $user->user_role,
            $user->name ?: $user->email
        );
    }

    public function recordApprovedClearanceTarget(
        User $user,
        MarClearanceType $clearanceType,
        ?MarClearanceSubcategory $subcategory,
        string $sourceWorkflow,
        string|int $sourceRecordId,
        CarbonInterface|string|null $approvedAt = null
    ): MarClearanceIssuance {
        return $this->recordApprovedClearanceTargetSnapshot(
            $user,
            $clearanceType,
            $subcategory,
            $sourceWorkflow,
            $sourceRecordId,
            $approvedAt,
            $user->user_type ?: $user->user_role,
            $user->name ?: $user->email
        );
    }

    public function recordApprovedClearanceSnapshot(
        ?User $user,
        MarClearanceSubcategory $subcategory,
        string $sourceWorkflow,
        string|int $sourceRecordId,
        CarbonInterface|string|null $approvedAt = null,
        ?string $userType = null,
        ?string $userName = null
    ): MarClearanceIssuance {
        $subcategory->loadMissing('clearanceType');

        return $this->recordApprovedClearanceTargetSnapshot(
            $user,
            $subcategory->clearanceType,
            $subcategory,
            $sourceWorkflow,
            $sourceRecordId,
            $approvedAt,
            $userType,
            $userName
        );
    }

    public function recordApprovedClearanceTargetSnapshot(
        ?User $user,
        MarClearanceType $clearanceType,
        ?MarClearanceSubcategory $subcategory,
        string $sourceWorkflow,
        string|int $sourceRecordId,
        CarbonInterface|string|null $approvedAt = null,
        ?string $userType = null,
        ?string $userName = null
    ): MarClearanceIssuance {
        $allowedSources = [
            MarClearanceSubcategorySource::APPLICANT_FINAL_REVIEW,
            MarClearanceSubcategorySource::STUDENT_NURSE_REVIEW,
            MarClearanceSubcategorySource::EMPLOYEE_NURSE_REVIEW,
            MarClearanceSubcategorySource::PATIENT_INTAKE,
            MarClearanceSubcategorySource::CONSULTATION,
        ];

        if (!in_array($sourceWorkflow, $allowedSources, true)) {
            throw new InvalidArgumentException('Unsupported MAR clearance source workflow.');
        }

        if ($subcategory && (int) $subcategory->mar_clearance_type_id !== (int) $clearanceType->id) {
            throw new InvalidArgumentException('The clearance subcategory does not belong to the selected clearance type.');
        }

        $sourceQuery = $subcategory
            ? $subcategory->sources()
            : $clearanceType->sources();
        if (!$sourceQuery->where('source', $sourceWorkflow)->exists()) {
            throw new InvalidArgumentException('The clearance target is not enabled for this workflow.');
        }

        return MarClearanceIssuance::updateOrCreate(
            [
                'source_workflow' => $sourceWorkflow,
                'source_record_id' => (string) $sourceRecordId,
            ],
            [
                'user_id' => $user?->id,
                'clearance_type_id' => $clearanceType->id,
                'clearance_subcategory_id' => $subcategory?->id,
                'user_type' => $this->snapshotUserType($userType ?: $user?->user_type ?: $user?->user_role),
                'user_name_snapshot' => trim((string) ($userName ?: $user?->name ?: $user?->email)) ?: null,
                'clearance_name_snapshot' => $subcategory?->name ?: $clearanceType->name,
                'approved_at' => $approvedAt ?: now(),
            ]
        );
    }

    public function removeForSourceRecord(string $sourceWorkflow, string|int $sourceRecordId): void
    {
        MarClearanceIssuance::query()
            ->where('source_workflow', $sourceWorkflow)
            ->where('source_record_id', (string) $sourceRecordId)
            ->delete();
    }

    private function resolveHealthProfileWorkflow(\App\Models\HealthProfile $profile): ?string
    {
        $userType = $this->normalizeClearanceValue(
            $profile->user?->user_type ?: $profile->user?->user_role
        );

        if (str_contains($userType, 'applicant')) {
            return MarClearanceSubcategorySource::APPLICANT_FINAL_REVIEW;
        }

        if (str_contains($userType, 'dependent') || str_contains($userType, 'guest')) {
            return MarClearanceSubcategorySource::PATIENT_INTAKE;
        }

        if (str_contains($userType, 'student') || $userType === 'ojt') {
            return MarClearanceSubcategorySource::STUDENT_NURSE_REVIEW;
        }

        if (str_contains($userType, 'faculty') || str_contains($userType, 'admin') || str_contains($userType, 'staff')) {
            return MarClearanceSubcategorySource::EMPLOYEE_NURSE_REVIEW;
        }

        return $this->hasOfficialStudentNumber($profile)
            ? MarClearanceSubcategorySource::STUDENT_NURSE_REVIEW
            : ($this->hasAdmissionReference($profile)
                ? MarClearanceSubcategorySource::APPLICANT_FINAL_REVIEW
                : $this->resolveWorkflowFromCategory($profile));
    }

    private function resolveWorkflowFromCategory(\App\Models\HealthProfile $profile): ?string
    {
        $category = $this->normalizeClearanceValue($profile->health_form_category);

        return str_contains($category, 'student') || str_contains($category, 'ojt')
            ? MarClearanceSubcategorySource::STUDENT_NURSE_REVIEW
            : null;
    }

    private function hasOfficialStudentNumber(\App\Models\HealthProfile $profile): bool
    {
        $values = [$profile->student_number, $profile->user?->student_number];

        return collect($values)->contains(function ($value) {
            $value = strtoupper(trim((string) $value));

            return $value !== '' && (bool) preg_match('/^[0-9]{4}-[0-9]{5}-[A-Z]{2}-[0-9]+$/', $value);
        });
    }

    private function hasAdmissionReference(\App\Models\HealthProfile $profile): bool
    {
        $values = [$profile->reference_number, $profile->user?->reference_number];

        return collect($values)->contains(function ($value) {
            $value = strtoupper(trim((string) $value));

            return $value !== ''
                && !str_starts_with($value, 'CLN-')
                && !str_starts_with($value, 'LOC-')
                && !str_starts_with($value, 'TEST-LOCAL')
                && !in_array($value, ['N/A', 'NA', 'NULL', 'NONE', 'UNKNOWN'], true)
                && !preg_match('/^[0-9A-F]{8}-[0-9A-F]{4}-[1-5][0-9A-F]{3}-[89AB][0-9A-F]{3}-[0-9A-F]{12}$/i', $value)
                && !preg_match('/^[0-9]{4}-[0-9]{5}-[A-Z]{2}-[0-9]+$/', $value);
        });
    }

    private function snapshotUserType(?string $value): ?string
    {
        $value = trim((string) $value);
        $normalized = app(MarPatientTypeNormalizer::class)->normalize($value);

        return $normalized ?: ($value !== '' ? $value : null);
    }

    private function normalizeClearanceValue(?string $value): string
    {
        $normalized = strtolower(trim((string) $value));
        $normalized = preg_replace('/[^a-z0-9]+/', ' ', $normalized) ?? $normalized;

        return trim(preg_replace('/\s+/', ' ', $normalized) ?? $normalized);
    }

    private function buildCategoryAliases(?string $category, array $additionalAliases = []): Collection
    {
        return collect([$category, ...$additionalAliases])
            ->map(fn ($alias) => $this->normalizeClearanceValue((string) $alias))
            ->filter(fn (string $alias) => $alias !== '' && $alias !== 'general')
            ->unique()
            ->values();
    }

    private function findBestClearanceItem(
        Collection $items,
        Collection $categoryAliases,
        callable $valuesResolver
    ): ?object {
        $matches = $items->map(function ($item) use ($categoryAliases, $valuesResolver) {
            $score = collect($valuesResolver($item))->sum(function ($value) use ($categoryAliases) {
                $normalizedValue = $this->normalizeClearanceValue((string) $value);
                $valueTokens = collect(explode(' ', $normalizedValue))->filter(fn ($token) => strlen($token) > 2);

                return $categoryAliases->sum(function (string $alias) use ($normalizedValue, $valueTokens) {
                    if ($normalizedValue === $alias) {
                        return 100;
                    }

                    if (str_contains($normalizedValue, $alias) || str_contains($alias, $normalizedValue)) {
                        return 20;
                    }

                    $aliasTokens = collect(explode(' ', $alias))->filter(fn ($token) => strlen($token) > 2);

                    return $aliasTokens->intersect($valueTokens)->count() * 5;
                });
            });

            return ['item' => $item, 'score' => $score];
        })->filter(fn (array $match) => $match['score'] > 0)->sortByDesc('score')->values();

        if ($matches->isEmpty()) {
            return null;
        }

        $topScore = $matches->first()['score'];
        $topMatches = $matches->where('score', $topScore);

        return $topMatches->count() === 1 ? $topMatches->first()['item'] : null;
    }
}
