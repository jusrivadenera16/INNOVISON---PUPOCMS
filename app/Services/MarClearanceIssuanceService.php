<?php

namespace App\Services;

use App\Models\Consultation;
use App\Models\ActivityLog;
use App\Models\EmployeeHealthProfile;
use App\Models\HealthFormSubmission;
use App\Models\HealthProfile;
use App\Models\MarClearanceSourceMapping;
use App\Models\MarClearanceIssuance;
use App\Models\MarClearanceSubcategory;
use App\Models\MarClearanceSubcategorySource;
use App\Models\MarClearanceType;
use App\Models\User;
use Carbon\Carbon;
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

        if (!$isApproved) {
            $this->removeAllHealthProfileIssuances($profile);

            return;
        }

        $workflow = $this->resolveHealthProfileWorkflow($profile);
        if ($workflow === null) {
            return;
        }

        $this->syncApprovedHealthProfileForWorkflow($profile, $workflow, $isApproved);
    }

    /**
     * Sync a profile approval while keeping each submitted Health Form request
     * as its own MAR source record. The profile-id fallback keeps older records
     * working when no submission snapshot exists.
     */
    public function syncApprovedHealthProfileForWorkflow(
        HealthProfile $profile,
        string $sourceWorkflow,
        bool $isApproved
    ): void {
        $profile->loadMissing('user');
        $submissions = HealthFormSubmission::query()
            ->where(function ($query) use ($profile) {
                $query->where('health_profile_id', $profile->id)
                    ->orWhere('user_id', $profile->user_id);
            })
            ->whereIn('status', [
                HealthFormSubmission::STATUS_SUBMITTED,
                HealthFormSubmission::STATUS_APPROVED,
                HealthFormSubmission::STATUS_NEEDS_CORRECTION,
            ])
            ->orderBy('id')
            ->get();

        if (!$isApproved) {
            $this->removeForSourceRecord($sourceWorkflow, (string) $profile->id);
            foreach ($submissions as $submission) {
                $this->removeForSourceRecord(
                    $sourceWorkflow,
                    $this->healthFormSourceRecordId($submission->id)
                );
            }

            return;
        }

        $approvedSubmissions = $submissions->whereIn('status', [
            HealthFormSubmission::STATUS_APPROVED,
            'Approved',
        ]);

        if ($approvedSubmissions->isNotEmpty()) {
            foreach ($approvedSubmissions as $submission) {
                $this->syncApprovedHealthFormSubmission(
                    $submission,
                    $sourceWorkflow,
                    true,
                    null
                );
            }

            // The approved submission is the source snapshot. Remove the
            // older profile-level fallback so the same approval is not counted twice.
            $this->removeForSourceRecord($sourceWorkflow, (string) $profile->id);

            return;
        }

        $pendingSubmissions = $submissions->whereIn('status', [
            HealthFormSubmission::STATUS_SUBMITTED,
            HealthFormSubmission::STATUS_NEEDS_CORRECTION,
        ]);

        if ($pendingSubmissions->isNotEmpty()) {
            foreach ($pendingSubmissions as $submission) {
                $currentApprovalDate = $profile->verified_at;

                $this->syncApprovedHealthFormSubmission(
                    $submission,
                    $sourceWorkflow,
                    true,
                    $currentApprovalDate
                );
            }

            // Remove only the pre-submission legacy snapshot for this profile.
            $this->removeForSourceRecord($sourceWorkflow, (string) $profile->id);

            return;
        }

        $target = $this->resolveConfiguredTargetForHealthProfile($profile, $sourceWorkflow);
        if (!$target || !$profile->user) {
            return;
        }

        $this->recordApprovedClearanceTarget(
            $profile->user,
            $target['clearanceType'],
            $target['subcategory'],
            $sourceWorkflow,
            (string) $profile->id,
            $profile->verified_at ?: now()
        );
    }

    /**
     * Record a submitted health form against the currently configured MAR
     * source. This is intentionally independent of the current profile
     * category so later requests keep their original category and date.
     */
    public function syncApprovedHealthFormSubmission(
        HealthFormSubmission $submission,
        string $sourceWorkflow,
        bool $isApproved = true,
        CarbonInterface|string|null $approvedAt = null
    ): ?MarClearanceIssuance {
        $submission->loadMissing(['user', 'healthProfile.user']);
        $sourceRecordId = $this->healthFormSourceRecordId($submission->id);

        if (!$isApproved) {
            $this->removeForSourceRecord($sourceWorkflow, $sourceRecordId);

            return null;
        }

        $target = $this->resolveConfiguredTargetForHealthFormSubmission($submission, $sourceWorkflow);
        if (!$target || !$submission->user) {
            return null;
        }

        return $this->recordApprovedClearanceTarget(
            $submission->user,
            $target['clearanceType'],
            $target['subcategory'],
            $sourceWorkflow,
            $sourceRecordId,
            $approvedAt ?: $submission->approved_at ?: $submission->submitted_at ?: now()
        );
    }

    public function syncApprovedEmployeeHealthProfile(
        EmployeeHealthProfile $profile,
        bool $isApproved
    ): ?MarClearanceIssuance {
        $profile->loadMissing('user');
        $sourceWorkflow = MarClearanceSubcategorySource::EMPLOYEE_NURSE_REVIEW;
        $sourceRecordId = (string) $profile->id;

        if (!$isApproved) {
            $this->removeForSourceRecord($sourceWorkflow, $sourceRecordId);

            return null;
        }

        $target = $this->resolveConfiguredTargetForEmployeeProfile($profile, $sourceWorkflow);
        if (!$target || !$profile->user) {
            return null;
        }

        return $this->recordApprovedClearanceTarget(
            $profile->user,
            $target['clearanceType'],
            $target['subcategory'],
            $sourceWorkflow,
            $sourceRecordId,
            $profile->verified_at ?: now()
        );
    }

    public function syncApprovedConsultation(
        Consultation $consultation,
        ?User $user = null
    ): ?MarClearanceIssuance {
        $user = $user ?: $consultation->user;
        $certificateType = trim((string) $consultation->certificate_type);
        if ($certificateType === '' || strtolower($certificateType) === 'none') {
            return null;
        }

        $sourceWorkflow = MarClearanceSubcategorySource::CONSULTATION;
        $target = $this->resolveConfiguredTargetForConsultation($consultation, $sourceWorkflow);

        // Keep the existing code-based consultation behavior when no new
        // source mapping has been configured for this certificate value.
        if (!$target) {
            $target = $this->resolveClearanceTargetByCodeForWorkflow($certificateType, $sourceWorkflow);
        }

        if (!$target || !$user) {
            return null;
        }

        return $this->recordApprovedClearanceTarget(
            $user,
            $target['clearanceType'],
            $target['subcategory'],
            $sourceWorkflow,
            (string) $consultation->id,
            $consultation->consultation_date ?: now()
        );
    }

    /**
     * Return the MAR issuance snapshots for a report period.
     *
     * Saved issuance rows remain the source of truth for approvals that were
     * already synced. The source tables are consulted only for older approved
     * records that do not have a snapshot yet, so adding a new source mapping
     * can immediately include historical records without a write-side backfill.
     */
    public function issuancesForReportPeriod(
        CarbonInterface|string $from,
        CarbonInterface|string $to
    ): Collection {
        $from = Carbon::parse($from)->startOfDay();
        $to = Carbon::parse($to)->endOfDay();

        $issuances = MarClearanceIssuance::query()
            ->with(['clearanceType', 'subcategory.clearanceType'])
            ->where(function ($query) {
                $query->whereHas('clearanceType', fn ($typeQuery) => $typeQuery->where('is_active', true))
                    ->orWhereHas('subcategory.clearanceType', fn ($typeQuery) => $typeQuery->where('is_active', true));
            })
            ->whereBetween('approved_at', [$from, $to])
            ->get();

        $knownSourceKeys = $issuances->mapWithKeys(function (MarClearanceIssuance $issuance): array {
            return [$this->reportSourceKey($issuance->source_workflow, $issuance->source_record_id) => true];
        });
        $historical = collect();
        $profileSnapshotKeysToReplace = collect();

        $submissions = HealthFormSubmission::query()
            ->with(['user', 'healthProfile.user'])
            ->whereIn('status', [HealthFormSubmission::STATUS_APPROVED, 'Approved'])
            ->where(function ($query) use ($from, $to) {
                $query->whereBetween('approved_at', [$from, $to])
                    ->orWhere(function ($fallback) use ($from, $to) {
                        $fallback->whereNull('approved_at')
                            ->whereBetween('submitted_at', [$from, $to]);
                    });
            })
            ->orderBy('id')
            ->get();

        foreach ($submissions as $submission) {
            if ($submission->healthProfile?->pullout_status === HealthProfile::PULLOUT_COMPLETED) {
                continue;
            }

            $approvedAt = $submission->approved_at ?: $submission->submitted_at;
            foreach ($this->reportWorkflowsForHealthFormSubmission($submission) as $sourceWorkflow) {
                $target = $this->resolveConfiguredTargetForHealthFormSubmission($submission, $sourceWorkflow);
                if (!$target) {
                    continue;
                }

                if ($submission->health_profile_id) {
                    $profileSnapshotKeysToReplace->put(
                        $this->reportSourceKey($sourceWorkflow, (string) $submission->health_profile_id),
                        true
                    );
                }

                $this->addReportIssuanceSnapshot(
                    $historical,
                    $knownSourceKeys,
                    $submission->user,
                    $target,
                    $sourceWorkflow,
                    $this->healthFormSourceRecordId($submission->id),
                    $approvedAt
                );
            }
        }

        if ($profileSnapshotKeysToReplace->isNotEmpty()) {
            $issuances = $issuances
                ->reject(fn (MarClearanceIssuance $issuance): bool => $profileSnapshotKeysToReplace->has(
                    $this->reportSourceKey($issuance->source_workflow, $issuance->source_record_id)
                ))
                ->values();
        }

        $submissionSourceKeys = $submissions
            ->flatMap(function (HealthFormSubmission $submission): array {
                return array_filter([
                    $submission->health_profile_id ? 'profile:' . $submission->health_profile_id : null,
                    $submission->user_id ? 'user:' . $submission->user_id : null,
                ]);
            })
            ->flip();

        $healthProfiles = HealthProfile::query()
            ->with('user')
            ->notPulledOut()
            ->whereIn('clearance_status', [
                'Approved', 'approved',
                'Issued', 'issued',
                'Fully Cleared', 'fully cleared',
                'Cleared', 'cleared',
            ])
            ->where(function ($query) use ($from, $to) {
                $query->whereBetween('verified_at', [$from, $to])
                    ->orWhere(function ($fallback) use ($from, $to) {
                        $fallback->whereNull('verified_at')
                            ->whereBetween('created_at', [$from, $to]);
                    });
            })
            ->orderBy('id')
            ->get();

        foreach ($healthProfiles as $profile) {
            if (!$profile->user || $submissionSourceKeys->has('profile:' . $profile->id)
                || $submissionSourceKeys->has('user:' . $profile->user_id)) {
                continue;
            }

            $sourceWorkflow = $this->reportWorkflowForHealthProfile($profile);
            if (!$sourceWorkflow) {
                continue;
            }

            $approvedAt = $profile->verified_at ?: $profile->created_at;
            if (!$approvedAt || !Carbon::parse($approvedAt)->betweenIncluded($from, $to)) {
                continue;
            }

            $target = $this->resolveConfiguredTargetForHealthProfile($profile, $sourceWorkflow);
            if (!$target) {
                continue;
            }

            $this->addReportIssuanceSnapshot(
                $historical,
                $knownSourceKeys,
                $profile->user,
                $target,
                $sourceWorkflow,
                (string) $profile->id,
                $approvedAt
            );
        }

        $employeeProfiles = EmployeeHealthProfile::query()
            ->with('user')
            ->where(function ($query) {
                $query->whereIn('clearance_status', [
                    'Approved', 'approved',
                    'Issued', 'issued',
                    'Fully Cleared', 'fully cleared',
                    'Cleared', 'cleared',
                ])->orWhereIn('submission_status', [
                    'Approved', 'approved',
                    'Issued', 'issued',
                    'Fully Cleared', 'fully cleared',
                    'Cleared', 'cleared',
                ]);
            })
            ->where(function ($query) use ($from, $to) {
                foreach (['verified_at', 'certified_at', 'form_date', 'created_at'] as $column) {
                    $query->orWhereBetween($column, [$from, $to]);
                }
            })
            ->orderBy('id')
            ->get();

        foreach ($employeeProfiles as $profile) {
            if (!$profile->user) {
                continue;
            }

            $approvedAt = $profile->verified_at
                ?: $profile->certified_at
                ?: $profile->form_date
                ?: $profile->created_at;
            if (!$approvedAt || !Carbon::parse($approvedAt)->betweenIncluded($from, $to)) {
                continue;
            }

            $sourceWorkflow = MarClearanceSubcategorySource::EMPLOYEE_NURSE_REVIEW;
            $target = $this->resolveConfiguredTargetForEmployeeProfile($profile, $sourceWorkflow);
            if (!$target) {
                continue;
            }

            $this->addReportIssuanceSnapshot(
                $historical,
                $knownSourceKeys,
                $profile->user,
                $target,
                $sourceWorkflow,
                (string) $profile->id,
                $approvedAt
            );
        }

        $consultations = Consultation::query()
            ->with('user')
            ->whereNotNull('certificate_type')
            ->whereRaw("LOWER(TRIM(certificate_type)) <> 'none'")
            ->whereBetween('consultation_date', [$from->toDateString(), $to->toDateString()])
            ->orderBy('id')
            ->get();

        foreach ($consultations as $consultation) {
            $sourceWorkflow = MarClearanceSubcategorySource::CONSULTATION;
            $target = $this->resolveConfiguredTargetForConsultation($consultation, $sourceWorkflow);
            if (!$target) {
                continue;
            }

            $this->addReportIssuanceSnapshot(
                $historical,
                $knownSourceKeys,
                $consultation->user,
                $target,
                $sourceWorkflow,
                (string) $consultation->id,
                $consultation->consultation_date,
                $consultation->user_role ?: $consultation->user_type,
                $consultation->name
            );
        }

        return $issuances
            ->concat($historical)
            ->sortBy('approved_at')
            ->values();
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

    private function resolveConfiguredTargetForHealthFormSubmission(
        HealthFormSubmission $submission,
        string $sourceWorkflow
    ): ?array {
        $approvedAt = $submission->approved_at ?: $submission->submitted_at ?: now();
        $mappings = $this->activeSourceMappingsForWorkflow($sourceWorkflow, $approvedAt);

        return $this->selectConfiguredTarget($mappings, function (MarClearanceSourceMapping $mapping) use ($submission, $sourceWorkflow): int {
            $sourceKey = trim((string) $mapping->source_key);

            if ($sourceKey === 'freshmen_applicants') {
                return $this->isFreshmenApplicantSubmission($submission)
                    && $this->matchesApplicantFilters(
                        $submission->healthProfile,
                        $submission,
                        $mapping->source_config
                    ) ? 1000 : 0;
            }

            if ($sourceKey === 'health_form_category') {
                $category = trim((string) ($submission->category ?: $submission->healthProfile?->health_form_category));

                return $this->matchesSourceCategory($category, $mapping->sourceCategory) ? 900 : 0;
            }

            if ($sourceKey === 'patient_intake' && $sourceWorkflow === MarClearanceSubcategorySource::PATIENT_INTAKE) {
                return 700;
            }

            return 0;
        });
    }

    private function resolveConfiguredTargetForHealthProfile(
        HealthProfile $profile,
        string $sourceWorkflow
    ): ?array {
        $mappings = $this->activeSourceMappingsForWorkflow($sourceWorkflow, $profile->verified_at ?: now());

        return $this->selectConfiguredTarget($mappings, function (MarClearanceSourceMapping $mapping) use ($profile, $sourceWorkflow): int {
            $sourceKey = trim((string) $mapping->source_key);

            if ($sourceKey === 'freshmen_applicants') {
                return $this->isFreshmenApplicantProfile($profile)
                    && $this->matchesApplicantFilters($profile, null, $mapping->source_config)
                    ? 1000 : 0;
            }

            if ($sourceKey === 'health_form_category') {
                return $this->matchesSourceCategory(
                    (string) $profile->health_form_category,
                    $mapping->sourceCategory
                ) ? 900 : 0;
            }

            if ($sourceKey === 'patient_intake' && $sourceWorkflow === MarClearanceSubcategorySource::PATIENT_INTAKE) {
                return 700;
            }

            return 0;
        });
    }

    private function resolveConfiguredTargetForEmployeeProfile(
        EmployeeHealthProfile $profile,
        string $sourceWorkflow
    ): ?array {
        $mappings = $this->activeSourceMappingsForWorkflow($sourceWorkflow, $profile->verified_at ?: now());
        $category = $this->normalizeSourceCategory($profile->health_form_category);

        return $this->selectConfiguredTarget($mappings, function (MarClearanceSourceMapping $mapping) use ($category, $profile): int {
            $sourceKey = trim((string) $mapping->source_key);

            if ($sourceKey === 'health_form_category') {
                return $this->matchesSourceCategory($profile->health_form_category, $mapping->sourceCategory) ? 800 : 0;
            }

            return 0;
        });
    }

    private function resolveConfiguredTargetForConsultation(
        Consultation $consultation,
        string $sourceWorkflow
    ): ?array {
        $mappings = $this->activeSourceMappingsForWorkflow($sourceWorkflow, $consultation->consultation_date ?: now());
        $certificateType = $this->normalizeClearanceValue($consultation->certificate_type);

        return $this->selectConfiguredTarget($mappings, function (MarClearanceSourceMapping $mapping) use ($certificateType): int {
            if (trim((string) $mapping->source_key) !== 'consultation' || $certificateType === '') {
                return 0;
            }

            $target = $this->configuredMappingTarget($mapping);
            if (!$target) {
                return 0;
            }

            $targetValues = collect([
                $target['subcategory']?->code,
                $target['subcategory']?->name,
                $target['clearanceType']->code,
                $target['clearanceType']->name,
            ])
                ->map(fn ($value) => $this->normalizeClearanceValue((string) $value))
                ->filter()
                ->unique();

            if ($targetValues->contains($certificateType)) {
                return 900;
            }

            return $targetValues->contains(function (string $targetValue) use ($certificateType): bool {
                return str_contains($targetValue, $certificateType)
                    || str_contains($certificateType, $targetValue);
            }) ? 700 : 0;
        });
    }

    private function activeSourceMappingsForWorkflow(
        string $sourceWorkflow,
        CarbonInterface|string|null $date = null
    ): Collection {
        $date = $date ? Carbon::parse($date) : now();

        return MarClearanceSourceMapping::query()
            ->where('is_active', true)
            ->with([
                'clearanceType',
                'subcategory.clearanceType',
                'sourceCategory',
            ])
            ->get()
            ->filter(function (MarClearanceSourceMapping $mapping) use ($sourceWorkflow, $date): bool {
                $target = $this->configuredMappingTarget($mapping);
                if (!$target || !$this->mappingIsEffective($mapping, $date)) {
                    return false;
                }

                $sourceQuery = $target['subcategory']
                    ? $target['subcategory']->sources()
                    : $target['clearanceType']->sources();

                return $sourceQuery->where('source', $sourceWorkflow)->exists();
            })
            ->values();
    }

    private function selectConfiguredTarget(Collection $mappings, callable $scoreResolver): ?array
    {
        $match = $mappings
            ->map(function (MarClearanceSourceMapping $mapping) use ($scoreResolver): array {
                return [
                    'mapping' => $mapping,
                    'score' => $scoreResolver($mapping),
                ];
            })
            ->filter(fn (array $match): bool => $match['score'] > 0)
            ->sort(function (array $left, array $right): int {
                if ($left['score'] !== $right['score']) {
                    return $right['score'] <=> $left['score'];
                }

                $leftIsSubcategory = $left['mapping']->mar_clearance_subcategory_id !== null;
                $rightIsSubcategory = $right['mapping']->mar_clearance_subcategory_id !== null;
                if ($leftIsSubcategory !== $rightIsSubcategory) {
                    return $leftIsSubcategory ? -1 : 1;
                }

                return (int) $left['mapping']->id <=> (int) $right['mapping']->id;
            })
            ->first();

        return $match ? $this->configuredMappingTarget($match['mapping']) : null;
    }

    private function configuredMappingTarget(MarClearanceSourceMapping $mapping): ?array
    {
        $subcategory = $mapping->subcategory;
        $clearanceType = $subcategory?->clearanceType ?: $mapping->clearanceType;

        if (!$clearanceType || !$clearanceType->is_active) {
            return null;
        }

        if (!$subcategory && !$clearanceType->allow_direct_use) {
            return null;
        }

        return [
            'clearanceType' => $clearanceType,
            'subcategory' => $subcategory,
        ];
    }

    private function addReportIssuanceSnapshot(
        Collection $issuances,
        Collection $knownSourceKeys,
        ?User $user,
        array $target,
        string $sourceWorkflow,
        string|int $sourceRecordId,
        CarbonInterface|string|null $approvedAt,
        ?string $userType = null,
        ?string $userName = null
    ): void {
        $sourceKey = $this->reportSourceKey($sourceWorkflow, $sourceRecordId);
        if ($knownSourceKeys->has($sourceKey)) {
            return;
        }

        $clearanceType = $target['clearanceType'];
        $subcategory = $target['subcategory'];
        $snapshot = new MarClearanceIssuance();
        $snapshot->forceFill([
            'user_id' => $user?->id,
            'clearance_type_id' => $clearanceType->id,
            'clearance_subcategory_id' => $subcategory?->id,
            'source_workflow' => $sourceWorkflow,
            'source_record_id' => (string) $sourceRecordId,
            'user_type' => $this->snapshotUserType(
                $userType ?: $user?->user_type ?: $user?->user_role
            ),
            'user_name_snapshot' => trim((string) (
                $userName ?: $user?->name ?: $user?->email
            )) ?: null,
            'clearance_name_snapshot' => $subcategory?->name ?: $clearanceType->name,
            'approved_at' => $approvedAt ?: now(),
        ]);
        $snapshot->setRelation('clearanceType', $clearanceType);
        if ($subcategory) {
            $snapshot->setRelation('subcategory', $subcategory);
        }

        $knownSourceKeys->put($sourceKey, true);
        $issuances->push($snapshot);
    }

    private function reportSourceKey(string $sourceWorkflow, string|int $sourceRecordId): string
    {
        return $sourceWorkflow . ':' . (string) $sourceRecordId;
    }

    private function reportWorkflowsForHealthFormSubmission(
        HealthFormSubmission $submission
    ): array {
        if ($this->isFreshmenApplicantSubmission($submission)) {
            return [MarClearanceSubcategorySource::APPLICANT_FINAL_REVIEW];
        }

        $userType = $this->normalizeClearanceValue(
            $submission->user?->user_type
                ?: $submission->user?->user_role
                ?: $submission->healthProfile?->user?->user_type
                ?: $submission->healthProfile?->user?->user_role
        );

        if (str_contains($userType, 'dependent') || str_contains($userType, 'guest')) {
            return [MarClearanceSubcategorySource::PATIENT_INTAKE];
        }

        if (str_contains($userType, 'faculty')
            || str_contains($userType, 'admin')
            || str_contains($userType, 'staff')
            || str_contains($userType, 'employee')) {
            return [MarClearanceSubcategorySource::EMPLOYEE_NURSE_REVIEW];
        }

        return [MarClearanceSubcategorySource::STUDENT_NURSE_REVIEW];
    }

    private function reportWorkflowForHealthProfile(HealthProfile $profile): ?string
    {
        if ($this->isFreshmenApplicantProfile($profile)) {
            return MarClearanceSubcategorySource::APPLICANT_FINAL_REVIEW;
        }

        $userType = $this->normalizeClearanceValue(
            $profile->user?->user_type ?: $profile->user?->user_role
        );

        if (str_contains($userType, 'applicant')) {
            return MarClearanceSubcategorySource::APPLICANT_FINAL_REVIEW;
        }

        if (str_contains($userType, 'dependent') || str_contains($userType, 'guest')) {
            return MarClearanceSubcategorySource::PATIENT_INTAKE;
        }

        if (str_contains($userType, 'faculty')
            || str_contains($userType, 'admin')
            || str_contains($userType, 'staff')
            || str_contains($userType, 'employee')) {
            return MarClearanceSubcategorySource::EMPLOYEE_NURSE_REVIEW;
        }

        return MarClearanceSubcategorySource::STUDENT_NURSE_REVIEW;
    }

    private function isFreshmenApplicantProfile(HealthProfile $profile): bool
    {
        if (!$this->hasAdmissionReference($profile)) {
            return false;
        }

        return !HealthFormSubmission::query()
            ->where(function ($query) use ($profile) {
                $query->where('health_profile_id', $profile->id)
                    ->orWhere('user_id', $profile->user_id);
            })
            ->whereNotNull('submitted_at')
            ->exists();
    }

    private function matchesApplicantFilters(
        ?HealthProfile $profile,
        ?HealthFormSubmission $submission,
        ?array $sourceConfig
    ): bool {
        $filters = data_get($sourceConfig, 'filters', []);
        if (!is_array($filters) || $filters === []) {
            return true;
        }

        $snapshot = $submission?->snapshotProfile() ?: [];
        $value = function (string $field) use ($profile, $snapshot): string {
            $snapshotValue = data_get($snapshot, $field);
            if (is_scalar($snapshotValue) && trim((string) $snapshotValue) !== '') {
                return trim((string) $snapshotValue);
            }

            return trim((string) data_get($profile, $field, ''));
        };

        foreach ($filters as $filterKey => $filterValue) {
            $filterValue = trim((string) $filterValue);
            if ($filterValue === '') {
                continue;
            }

            $matches = match ($filterKey) {
                'final_review_result' => $this->applicantFinalReviewResult($profile, $snapshot) === $filterValue,
                'medical_condition' => $this->applicantHasMedicalCondition($value) === ($filterValue === 'with_condition'),
                'pwd_status' => $this->applicantHasPwd($value) === ($filterValue === 'pwd'),
                'pwd_document' => $this->applicantHasPwdDocument($value) === ($filterValue === 'submitted'),
                'covid_status' => $this->applicantCovidStatus($value) === $filterValue,
                'medical_certificate_result' => $this->applicantMedicalCertificateResult($value) === $filterValue,
                'chest_xray_result' => $this->applicantChestXrayResult($value) === $filterValue,
                default => false,
            };

            if (!$matches) {
                return false;
            }
        }

        return true;
    }

    private function applicantFinalReviewResult(?HealthProfile $profile, array $snapshot): string
    {
        $result = trim((string) (
            data_get($snapshot, 'final_review_findings_status')
            ?: data_get($profile, 'final_review_findings_status')
            ?: data_get($profile, 'final_review_draft_data.applicant_findings_status')
        ));

        if ($result !== '') {
            return $result === 'With Findings' ? 'with_findings' : 'no_findings';
        }

        if ($profile?->id && \Schema::hasTable('activity_logs')) {
            $metadata = ActivityLog::query()
                ->where('subject_type', HealthProfile::class)
                ->where('subject_id', (string) $profile->id)
                ->whereIn('event_type', ['applicant_approval', 'applicant_pending_compliance'])
                ->latest('id')
                ->value('metadata');
            $metadata = is_array($metadata) ? $metadata : json_decode((string) $metadata, true);
            $result = trim((string) data_get($metadata, 'findings_status'));

            if ($result !== '') {
                return $result === 'With Findings' ? 'with_findings' : 'no_findings';
            }
        }

        return '';
    }

    private function applicantHasMedicalCondition(callable $value): bool
    {
        return trim((string) $value('medical_condition_remarks')) !== '';
    }

    private function applicantHasPwd(callable $value): bool
    {
        $hasDisability = strtolower(trim((string) $value('has_disability')));

        return in_array($hasDisability, ['yes', 'true', '1'], true)
            || $this->applicantHasPwdDocument($value);
    }

    private function applicantHasPwdDocument(callable $value): bool
    {
        return trim((string) $value('pwd_id_proof')) !== '';
    }

    private function applicantCovidStatus(callable $value): string
    {
        $status = strtolower(trim((string) $value('covid_positive')));

        return match ($status) {
            'yes', 'positive', 'covid positive' => 'positive',
            'no', 'negative', 'covid negative' => 'negative',
            default => '',
        };
    }

    private function applicantMedicalCertificateResult(callable $value): string
    {
        $result = strtolower(trim((string) $value('med_cert_findings')));

        return match ($result) {
            'with findings' => 'with_findings',
            'no findings / normal', 'normal', 'no findings' => 'no_findings',
            'not sure / for clinic review', 'not sure' => 'not_sure',
            default => '',
        };
    }

    private function applicantChestXrayResult(callable $value): string
    {
        $result = strtolower(trim((string) (
            $value('chest_xray_result_text') ?: $value('xray_findings')
        )));

        return match ($result) {
            'with findings' => 'with_findings',
            'normal', 'no findings / normal', 'no findings' => 'normal',
            'not sure / for clinic review', 'not sure' => 'not_sure',
            default => '',
        };
    }

    private function mappingIsEffective(MarClearanceSourceMapping $mapping, CarbonInterface $date): bool
    {
        if ($mapping->effective_from && $date->lt(Carbon::parse($mapping->effective_from)->startOfDay())) {
            return false;
        }

        return !$mapping->effective_until
            || !$date->gt(Carbon::parse($mapping->effective_until)->endOfDay());
    }

    private function isFreshmenApplicantSubmission(HealthFormSubmission $submission): bool
    {
        $profile = $submission->healthProfile;
        $referenceValues = [
            $profile?->reference_number,
            $profile?->user?->reference_number,
            data_get($submission->snapshotProfile(), 'reference_number'),
            data_get($submission->snapshotUser(), 'reference_number'),
        ];

        if (!collect($referenceValues)->contains(fn ($value) => $this->isAdmissionReference($value))) {
            return false;
        }

        $submissionsQuery = HealthFormSubmission::query()
            ->where(function ($query) use ($submission, $profile) {
                $query->where('user_id', $submission->user_id);
                if ($profile?->id) {
                    $query->orWhere('health_profile_id', $profile->id);
                }
            })
            ->whereNotNull('submitted_at')
            ->orderBy('submitted_at')
            ->orderBy('id');

        $firstSubmission = $submissionsQuery->first();

        if ($firstSubmission) {
            return (int) $firstSubmission->id === (int) $submission->id;
        }

        return !HealthFormSubmission::query()
            ->where('user_id', $submission->user_id)
            ->where('id', '<', $submission->id)
            ->exists();
    }

    private function matchesSourceCategory(?string $value, $sourceCategory): bool
    {
        $value = $this->normalizeSourceCategory($value);
        $sourceName = $this->normalizeSourceCategory($sourceCategory?->name);

        if ($value === '' || $sourceName === '') {
            return false;
        }

        return $value === $sourceName;
    }

    private function normalizeSourceCategory(?string $value): string
    {
        $normalized = $this->normalizeClearanceValue($value);
        $normalized = str_replace(['on the job training', 'on the job'], 'ojt', $normalized);
        $normalized = preg_replace('/\bfreshmen\b/', 'freshman', $normalized) ?? $normalized;

        return trim(preg_replace('/\s+/', ' ', $normalized) ?? $normalized);
    }

    private function isAdmissionReference($value): bool
    {
        $value = strtoupper(trim((string) $value));

        return $value !== ''
            && !str_starts_with($value, 'CLN-')
            && !str_starts_with($value, 'LOC-')
            && !str_starts_with($value, 'TEST-LOCAL')
            && !in_array($value, ['N/A', 'NA', 'NULL', 'NONE', 'UNKNOWN'], true)
            && !preg_match('/^[0-9A-F]{8}-[0-9A-F]{4}-[1-5][0-9A-F]{3}-[89AB][0-9A-F]{3}-[0-9A-F]{12}$/i', $value)
            && !preg_match('/^[0-9]{4}-[0-9]{5}-[A-Z]{2}-[0-9]+$/', $value);
    }

    private function healthFormSourceRecordId(int|string $submissionId): string
    {
        return 'health_form_submission:' . (string) $submissionId;
    }

    private function removeAllHealthProfileIssuances(HealthProfile $profile): void
    {
        $workflows = [
            MarClearanceSubcategorySource::APPLICANT_FINAL_REVIEW,
            MarClearanceSubcategorySource::STUDENT_NURSE_REVIEW,
            MarClearanceSubcategorySource::EMPLOYEE_NURSE_REVIEW,
            MarClearanceSubcategorySource::PATIENT_INTAKE,
        ];
        $submissionIds = HealthFormSubmission::query()
            ->where(function ($query) use ($profile) {
                $query->where('health_profile_id', $profile->id)
                    ->orWhere('user_id', $profile->user_id);
            })
            ->pluck('id');

        foreach ($workflows as $workflow) {
            $this->removeForSourceRecord($workflow, (string) $profile->id);
            foreach ($submissionIds as $submissionId) {
                $this->removeForSourceRecord(
                    $workflow,
                    $this->healthFormSourceRecordId($submissionId)
                );
            }
        }
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
