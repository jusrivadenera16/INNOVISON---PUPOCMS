<?php

namespace App\Console\Commands;

use App\Models\Consultation;
use App\Models\EmployeeHealthProfile;
use App\Models\HealthProfile;
use App\Models\MarClearanceIssuance;
use App\Models\MarClearanceSubcategory;
use App\Models\MarClearanceSubcategorySource;
use App\Models\User;
use App\Services\MarClearanceIssuanceService;
use App\Services\MarPatientTypeNormalizer;
use Illuminate\Console\Command;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class BackfillMarClearanceIssuances extends Command
{
    protected $signature = 'mar:backfill-clearance-issuances
        {--apply : Persist matched records. Without this flag the command only reports a dry run.}
        {--source=all : Limit the scan to all, applicants, students, employees, or consultations.}';

    protected $description = 'Reconcile approved historical records with the central MAR clearance issuance table.';

    private const APPROVED_STATUSES = ['issued', 'fully cleared', 'approved', 'cleared'];

    public function handle(MarClearanceIssuanceService $issuanceService): int
    {
        $source = strtolower(trim((string) $this->option('source')));
        $sourceAliases = [
            'all' => null,
            'applicant' => MarClearanceSubcategorySource::APPLICANT_FINAL_REVIEW,
            'applicants' => MarClearanceSubcategorySource::APPLICANT_FINAL_REVIEW,
            'student' => MarClearanceSubcategorySource::STUDENT_NURSE_REVIEW,
            'students' => MarClearanceSubcategorySource::STUDENT_NURSE_REVIEW,
            'employee' => MarClearanceSubcategorySource::EMPLOYEE_NURSE_REVIEW,
            'employees' => MarClearanceSubcategorySource::EMPLOYEE_NURSE_REVIEW,
            'consultation' => MarClearanceSubcategorySource::CONSULTATION,
            'consultations' => MarClearanceSubcategorySource::CONSULTATION,
            'patient_intake' => MarClearanceSubcategorySource::PATIENT_INTAKE,
            'patients' => MarClearanceSubcategorySource::PATIENT_INTAKE,
        ];

        if (!array_key_exists($source, $sourceAliases)) {
            $this->error('Invalid --source. Use all, applicants, students, employees, patient_intake, or consultations.');

            return self::FAILURE;
        }

        $apply = (bool) $this->option('apply');
        $this->info($apply
            ? 'Applying matched MAR clearance issuance backfill.'
            : 'Dry run: no MAR clearance issuance records will be changed.');

        $summary = [];
        $workflow = $sourceAliases[$source];

        if ($workflow === null || $workflow === MarClearanceSubcategorySource::APPLICANT_FINAL_REVIEW
            || $workflow === MarClearanceSubcategorySource::STUDENT_NURSE_REVIEW
            || $workflow === MarClearanceSubcategorySource::PATIENT_INTAKE) {
            $this->backfillHealthProfiles($issuanceService, $apply, $summary, $workflow);
        }

        if ($workflow === null || $workflow === MarClearanceSubcategorySource::EMPLOYEE_NURSE_REVIEW) {
            $this->backfillEmployeeProfiles($issuanceService, $apply, $summary);
        }

        if ($workflow === null || $workflow === MarClearanceSubcategorySource::CONSULTATION) {
            $this->backfillConsultations($issuanceService, $apply, $summary);
        }

        $this->renderSummary($summary, $apply);

        return collect($summary)->sum('failed') > 0 ? self::FAILURE : self::SUCCESS;
    }

    private function backfillHealthProfiles(
        MarClearanceIssuanceService $issuanceService,
        bool $apply,
        array &$summary,
        ?string $onlyWorkflow
    ): void {
        $query = HealthProfile::query()
            ->with('user')
            ->whereIn(DB::raw('LOWER(TRIM(clearance_status))'), self::APPROVED_STATUSES)
            ->orderBy('id');

        $query->chunkById(100, function (Collection $profiles) use (
            $issuanceService,
            $apply,
            &$summary,
            $onlyWorkflow
        ): void {
            foreach ($profiles as $profile) {
                ['workflow' => $workflow, 'reason' => $classificationReason] = $this->resolveHealthProfileWorkflow($profile);

                if ($workflow === null || ($onlyWorkflow !== null && $workflow !== $onlyWorkflow)) {
                    if ($workflow === null && $onlyWorkflow === null) {
                        $unclassifiedWorkflow = 'health_profile_unclassified';
                        $this->ensureSummary($summary, $unclassifiedWorkflow);
                        $summary[$unclassifiedWorkflow]['scanned']++;
                        $summary[$unclassifiedWorkflow]['unmapped']++;
                        $this->warn(sprintf(
                            'Unclassified [health_profiles] record #%s: %s',
                            $profile->id,
                            $classificationReason
                        ));
                    }

                    continue;
                }

                $this->ensureSummary($summary, $workflow);
                $this->processProfile(
                    $issuanceService,
                    $apply,
                    $summary[$workflow],
                    $profile,
                    $workflow
                );
            }
        });
    }

    private function resolveHealthProfileWorkflow(HealthProfile $profile): array
    {
        $userType = $this->normalize(
            (string) (optional($profile->user)->user_type ?: optional($profile->user)->user_role)
        );
        if (str_contains($userType, 'applicant')) {
            return [
                'workflow' => MarClearanceSubcategorySource::APPLICANT_FINAL_REVIEW,
                'reason' => null,
            ];
        }

        if (str_contains($userType, 'student') || $userType === 'ojt') {
            $studentNumber = collect([
                $profile->student_number,
                optional($profile->user)->student_number,
            ])->first(fn ($value) => $this->isOfficialStudentNumber((string) $value));

            if ($studentNumber !== null) {
                return [
                    'workflow' => MarClearanceSubcategorySource::STUDENT_NURSE_REVIEW,
                    'reason' => null,
                ];
            }
        }

        if (str_contains($userType, 'dependent') || str_contains($userType, 'guest')) {
            return [
                'workflow' => MarClearanceSubcategorySource::PATIENT_INTAKE,
                'reason' => null,
            ];
        }

        if (str_contains($userType, 'student') || $userType === 'ojt') {
            return [
                'workflow' => MarClearanceSubcategorySource::STUDENT_NURSE_REVIEW,
                'reason' => null,
            ];
        }

        $studentNumber = collect([
            $profile->student_number,
            optional($profile->user)->student_number,
        ])->first(fn ($value) => $this->isOfficialStudentNumber((string) $value));

        if ($studentNumber !== null) {
            return [
                'workflow' => MarClearanceSubcategorySource::STUDENT_NURSE_REVIEW,
                'reason' => null,
            ];
        }

        $referenceNumber = collect([
            $profile->reference_number,
            optional($profile->user)->reference_number,
        ])->first(fn ($value) => $this->isAdmissionReference((string) $value));

        if ($referenceNumber !== null) {
            return [
                'workflow' => MarClearanceSubcategorySource::APPLICANT_FINAL_REVIEW,
                'reason' => null,
            ];
        }

        $category = $this->normalize((string) $profile->health_form_category);
        if (str_contains($category, 'guest') || str_contains($category, 'dependent')) {
            return [
                'workflow' => MarClearanceSubcategorySource::PATIENT_INTAKE,
                'reason' => null,
            ];
        }
        if (str_contains($category, 'student') || str_contains($category, 'ojt')) {
            return [
                'workflow' => MarClearanceSubcategorySource::STUDENT_NURSE_REVIEW,
                'reason' => null,
            ];
        }

        return [
            'workflow' => null,
            'reason' => 'missing local user type and usable student/admission identifier',
        ];
    }

    private function isOfficialStudentNumber(string $value): bool
    {
        $value = strtoupper(trim($value));

        return $value !== ''
            && !str_starts_with($value, 'CLN-')
            && !str_starts_with($value, 'LOC-')
            && !str_starts_with($value, 'TEST-LOCAL')
            && !in_array($value, ['N/A', 'NA', 'NULL', 'NONE', 'UNKNOWN'], true)
            && !preg_match('/^[0-9A-F]{8}-[0-9A-F]{4}-[1-5][0-9A-F]{3}-[89AB][0-9A-F]{3}-[0-9A-F]{12}$/i', $value)
            && (bool) preg_match('/^[0-9]{4}-[0-9]{5}-[A-Z]{2}-[0-9]+$/', $value);
    }

    private function isAdmissionReference(string $value): bool
    {
        $value = strtoupper(trim($value));

        return $value !== ''
            && !str_starts_with($value, 'CLN-')
            && !str_starts_with($value, 'LOC-')
            && !str_starts_with($value, 'TEST-LOCAL')
            && !in_array($value, ['N/A', 'NA', 'NULL', 'NONE', 'UNKNOWN'], true)
            && !preg_match('/^[0-9A-F]{8}-[0-9A-F]{4}-[1-5][0-9A-F]{3}-[89AB][0-9A-F]{3}-[0-9A-F]{12}$/i', $value)
            && !$this->isOfficialStudentNumber($value);
    }

    private function backfillEmployeeProfiles(
        MarClearanceIssuanceService $issuanceService,
        bool $apply,
        array &$summary
    ): void {
        $workflow = MarClearanceSubcategorySource::EMPLOYEE_NURSE_REVIEW;
        $this->ensureSummary($summary, $workflow);
        EmployeeHealthProfile::query()
            ->with('user')
            ->whereIn(DB::raw('LOWER(TRIM(clearance_status))'), self::APPROVED_STATUSES)
            ->orderBy('id')
            ->chunkById(100, function (Collection $profiles) use (
                $issuanceService,
                $apply,
                &$summary,
                $workflow
            ): void {
                foreach ($profiles as $profile) {
                    $category = trim((string) ($profile->health_form_category ?: optional($profile->user)->user_type));
                    $aliases = $this->employeeCategoryAliases($category);
                    $match = $this->resolveClearanceTarget($issuanceService, $category, $workflow, $aliases);

                    $this->processMatch(
                        $issuanceService,
                        $apply,
                        $summary[$workflow],
                        $match,
                        $workflow,
                        $profile->id,
                        $profile->user,
                        $profile->user?->user_type ?: $category,
                        $profile->name ?: $profile->user?->name,
                        $profile->verified_at ?: $profile->certified_at ?: $profile->updated_at ?: $profile->created_at,
                        $category
                    );
                }
            });
    }

    private function backfillConsultations(
        MarClearanceIssuanceService $issuanceService,
        bool $apply,
        array &$summary
    ): void {
        $workflow = MarClearanceSubcategorySource::CONSULTATION;
        $this->ensureSummary($summary, $workflow);
        Consultation::query()
            ->with('user')
            ->whereNotNull('certificate_type')
            ->orderBy('id')
            ->chunkById(100, function (Collection $consultations) use (
                $issuanceService,
                $apply,
                &$summary,
                $workflow
            ): void {
                foreach ($consultations as $consultation) {
                    $certificateType = $this->normalize((string) $consultation->certificate_type);
                    if ($certificateType === '' || $certificateType === 'none') {
                        continue;
                    }

                    $aliases = match ($certificateType) {
                        'coc ijt', 'coc_ijt' => ['ojt', 'on the job', 'coc ijt'],
                        'coc ladderized', 'coc_ladderized' => ['ladderized', 'coc ladderized'],
                        default => [],
                    };

                    $user = $consultation->user;
                    $this->processMatch(
                        $issuanceService,
                        $apply,
                        $summary[$workflow],
                        $this->resolveClearanceTarget($issuanceService, $certificateType, $workflow, $aliases),
                        $workflow,
                        $consultation->id,
                        $user,
                        $consultation->user_type ?: $consultation->user_role ?: $user?->user_type ?: $user?->user_role,
                        $consultation->name ?: $user?->name,
                        $consultation->consultation_date ?: $consultation->created_at,
                        $consultation->certificate_type
                    );
                }
            });
    }

    private function processProfile(
        MarClearanceIssuanceService $issuanceService,
        bool $apply,
        array &$summary,
        HealthProfile $profile,
        string $workflow
    ): void {
        $category = trim((string) $profile->health_form_category);
        $patientType = app(MarPatientTypeNormalizer::class)->normalize(
            $profile->user?->user_type ?: $profile->user?->user_role
        );
        $aliases = $patientType === MarPatientTypeNormalizer::DEPENDENT
            ? ['guest', 'dependent']
            : [];
        $this->processMatch(
            $issuanceService,
            $apply,
            $summary,
            $this->resolveClearanceTarget($issuanceService, $category, $workflow, $aliases),
            $workflow,
            $profile->id,
            $profile->user,
            $profile->user?->user_type ?: $profile->user?->user_role,
            $profile->user?->name,
            $profile->verified_at ?: $profile->updated_at ?: $profile->created_at,
            $category
        );
    }

    private function processMatch(
        MarClearanceIssuanceService $issuanceService,
        bool $apply,
        array &$summary,
        array $match,
        string $workflow,
        int|string $sourceRecordId,
        ?User $user,
        ?string $userType,
        ?string $userName,
        $approvedAt,
        string $rawValue
    ): void {
        $summary['scanned']++;

        if (!$match['target']) {
            $summary['unmapped']++;
            $this->warn(sprintf(
                'Unmapped [%s] record #%s: "%s" (%s)',
                $workflow,
                $sourceRecordId,
                trim($rawValue) !== '' ? trim($rawValue) : '[empty]',
                $match['reason']
            ));

            return;
        }

        $summary['matched']++;
        $existing = MarClearanceIssuance::query()
            ->where('source_workflow', $workflow)
            ->where('source_record_id', (string) $sourceRecordId)
            ->exists();

        if (!$apply) {
            $summary['would_write']++;
            $this->line(sprintf(
                '[dry-run] %s record #%s -> %s%s',
                $workflow,
                $sourceRecordId,
                $match['target']['subcategory']?->name ?: $match['target']['clearanceType']->name,
                $existing ? ' (already linked)' : ''
            ));

            return;
        }

        try {
            $issuanceService->recordApprovedClearanceTargetSnapshot(
                $user,
                $match['target']['clearanceType'],
                $match['target']['subcategory'],
                $workflow,
                (string) $sourceRecordId,
                $approvedAt,
                $userType,
                $userName
            );
            $summary['written']++;
            $summary[$existing ? 'updated' : 'created']++;
        } catch (\Throwable $exception) {
            $summary['failed']++;
            $this->error(sprintf(
                'Failed [%s] record #%s: %s',
                $workflow,
                $sourceRecordId,
                $exception->getMessage()
            ));
        }
    }

    private function resolveClearanceTarget(
        MarClearanceIssuanceService $issuanceService,
        string $value,
        string $workflow,
        array $aliases = []
    ): array {
        if ($this->normalize($value) === '') {
            return ['target' => null, 'reason' => 'empty source value'];
        }

        $target = $issuanceService->resolveClearanceTargetForWorkflow($value, $workflow, $aliases);

        return [
            'target' => $target,
            'reason' => $target ? null : 'no active unique source mapping',
        ];
    }

    private function subcategoriesByWorkflow(array $workflows): array
    {
        $subcategories = MarClearanceSubcategory::query()
            ->whereHas('clearanceType', fn ($query) => $query->where('is_active', true))
            ->whereHas('sources', fn ($query) => $query->whereIn('source', $workflows))
            ->with(['clearanceType', 'sources'])
            ->orderBy('sort_order')
            ->orderBy('name')
            ->get();

        return collect($workflows)->mapWithKeys(function (string $workflow) use ($subcategories) {
            return [$workflow => $subcategories->filter(
                fn (MarClearanceSubcategory $subcategory) => $subcategory->sources->contains('source', $workflow)
            )->values()];
        })->all();
    }

    private function resolveSubcategory(Collection $subcategories, string $value, array $aliases = []): array
    {
        $normalizedValue = $this->normalize($value);
        if ($normalizedValue === '') {
            return ['subcategory' => null, 'reason' => 'empty source value'];
        }

        $candidateAliases = collect([$normalizedValue, ...$aliases])
            ->map(fn (string $alias) => $this->normalize($alias))
            ->filter()
            ->unique()
            ->values();

        $matches = $subcategories->map(function (MarClearanceSubcategory $subcategory) use ($candidateAliases) {
            $score = collect([$subcategory->code, $subcategory->name])->sum(function ($value) use ($candidateAliases) {
                $normalizedCandidate = $this->normalize((string) $value);
                $candidateTokens = collect(explode(' ', $normalizedCandidate))->filter(fn ($token) => strlen($token) > 2);

                return $candidateAliases->sum(function (string $alias) use ($normalizedCandidate, $candidateTokens) {
                    if ($normalizedCandidate === $alias) {
                        return 100;
                    }

                    if (str_contains($normalizedCandidate, $alias) || str_contains($alias, $normalizedCandidate)) {
                        return 20;
                    }

                    $aliasTokens = collect(explode(' ', $alias))->filter(fn ($token) => strlen($token) > 2);

                    return $aliasTokens->intersect($candidateTokens)->count() * 5;
                });
            });

            return ['subcategory' => $subcategory, 'score' => $score];
        })->filter(fn (array $match) => $match['score'] > 0)->sortByDesc('score')->values();

        if ($matches->isEmpty()) {
            return ['subcategory' => null, 'reason' => 'no active unique source mapping'];
        }

        $topScore = $matches->first()['score'];
        $topMatches = $matches->where('score', $topScore);
        if ($topMatches->count() !== 1) {
            return ['subcategory' => null, 'reason' => 'ambiguous source mapping'];
        }

        return ['subcategory' => $topMatches->first()['subcategory'], 'reason' => null];
    }

    private function employeeCategoryAliases(string $category): array
    {
        $normalized = $this->normalize($category);

        if (str_contains($normalized, 'faculty')) {
            return ['faculty', 'staff', 'annual medical'];
        }

        if (str_contains($normalized, 'administrative') || str_contains($normalized, 'admin') || str_contains($normalized, 'non teaching')) {
            return ['administrative', 'admin', 'staff', 'annual medical'];
        }

        return [];
    }

    private function normalize(string $value): string
    {
        $normalized = strtolower(trim($value));
        $normalized = preg_replace('/[^a-z0-9]+/', ' ', $normalized) ?? $normalized;

        return trim(preg_replace('/\s+/', ' ', $normalized) ?? $normalized);
    }

    private function ensureSummary(array &$summary, string $workflow): void
    {
        $summary[$workflow] ??= [
            'scanned' => 0,
            'matched' => 0,
            'would_write' => 0,
            'written' => 0,
            'created' => 0,
            'updated' => 0,
            'unmapped' => 0,
            'failed' => 0,
        ];
    }

    private function renderSummary(array $summary, bool $apply): void
    {
        if ($summary === []) {
            $this->warn('No eligible records were found.');

            return;
        }

        $rows = [];
        foreach ($summary as $workflow => $values) {
            $rows[] = [
                $workflow,
                $values['scanned'],
                $values['matched'],
                $apply ? $values['written'] : $values['would_write'],
                $values['unmapped'],
                $values['failed'],
            ];
        }

        $this->table(
            ['Workflow', 'Scanned', 'Matched', $apply ? 'Written' : 'Would write', 'Unmapped', 'Failed'],
            $rows
        );

        if ($apply) {
            $this->info('Backfill finished. Existing source rows were updated idempotently; unresolved rows were left unchanged.');
        } else {
            $this->info('Dry run finished. Run the same command with --apply after reviewing the mappings.');
        }
    }
}
