<?php

namespace App\Services;

use App\Models\EmployeeHealthProfile;
use App\Models\HealthFormSubmission;
use App\Models\HealthProfileCorrectionRequest;
use Illuminate\Support\Carbon;

class EmployeeHealthFormHistoryService
{
    public const SCHEMA_VERSION = 1;
    public const ASSESSMENT_FIELDS = [
        'vital_signs_distress_status', 'height', 'weight', 'bmi', 'bp', 'hr', 'rr', 'temperature',
        'head_findings', 'eyes_findings', 'ears_findings', 'throat_findings', 'chest_lungs_findings',
        'chest_xray_result', 'breast_findings', 'heart_murmur', 'heart_rhythm', 'abdomen_findings',
        'genito_urinary_date_lmp', 'extremities_findings', 'vertebral_column_findings', 'skin_findings',
        'scars_findings', 'working_impression', 'fit_status', 'for_work_up', 'referred_to',
        'referred_to_others', 'follow_up_on',
    ];

    public function canCancelAssessment(EmployeeHealthProfile $profile, HealthProfileCorrectionRequest $request, ?HealthFormSubmission $submission): bool
    {
        if (!$submission || (int) $request->employee_health_profile_id !== (int) $profile->id
            || (int) $request->user_id !== (int) $profile->user_id
            || (int) $submission->employee_health_profile_id !== (int) $profile->id
            || (int) $submission->user_id !== (int) $profile->user_id
            || (int) $request->health_form_submission_id !== (int) $submission->id
            || data_get($request->metadata, 'employee_login_required') !== false
            || $request->status !== HealthProfileCorrectionRequest::STATUS_SUBMITTED || $request->reviewed_at
            || $submission->status !== HealthFormSubmission::STATUS_SUBMITTED || $submission->approved_at
            || $submission->status_changed_by_user_id
            || $profile->clearance_status !== 'For Verification' || $profile->submission_status !== 'submitted'
            || filled($profile->draft_data)) return false;

        $current = $this->capture($profile)['profile'];
        $initial = $submission->snapshotProfile();
        unset($current['created_at'], $current['updated_at'], $initial['created_at'], $initial['updated_at']);
        return $initial !== [] && $current == $initial;
    }

    public function approvedBeforeAssessment(EmployeeHealthProfile $profile, HealthProfileCorrectionRequest $request): ?HealthFormSubmission
    {
        $approvedId = data_get($request->metadata, 'previous_approved_submission_id');
        return HealthFormSubmission::query()->where('employee_health_profile_id', $profile->id)
            ->where('user_id', $profile->user_id)->where('status', HealthFormSubmission::STATUS_APPROVED)
            ->when($approvedId, fn ($query) => $query->whereKey($approvedId))
            ->where('id', '<', $request->health_form_submission_id)
            ->latest('approved_at')->latest('id')->first();
    }

    public function restoreBeforeAssessment(EmployeeHealthProfile $profile, HealthFormSubmission $approved): void
    {
        // Restore only fields reset when starting the cycle, not identity, documents or contact data.
        $fields = array_merge(self::ASSESSMENT_FIELDS, [
            'health_form_category', 'form_date', 'clearance_status', 'submission_status', 'pending_reason',
            'resubmission_required_fields', 'resubmission_requested_at', 'pending_compliance_reminder_sent_at',
            'pending_compliance_reminder_count', 'resubmitted_at', 'verified_at', 'approved_by_user_id',
        ]);
        $values = array_intersect_key($approved->snapshotProfile(), array_flip($fields));
        foreach ($values as $field => $value) {
            if ($value !== null && in_array($profile->getCasts()[$field] ?? null, ['date', 'datetime'], true)) {
                // Snapshot dates are serialized in UTC; preserve their original app-local instant.
                $values[$field] = Carbon::parse($value)->setTimezone(config('app.timezone'));
            }
        }
        $profile->forceFill($values)->save();
    }

    public function capture(EmployeeHealthProfile $profile): array
    {
        $profile->loadMissing(['user', 'approvedBy']);

        $profileData = $profile->attributesToArray();
        unset($profileData['draft_data']);

        return [
            'schema_version' => self::SCHEMA_VERSION,
            'captured_at' => now()->toIso8601String(),
            'profile' => $profileData,
            'user' => [
                'id' => $profile->user?->id,
                'name' => $profile->user?->name,
                'email' => $profile->user?->email,
                'first_name' => $profile->user?->first_name,
                'middle_name' => $profile->user?->middle_name,
                'last_name' => $profile->user?->last_name,
                'suffix_name' => $profile->user?->suffix_name,
                'user_type' => $profile->user?->user_type,
                'user_role' => $profile->user?->user_role,
                'idp_role' => $profile->user?->idp_role,
                'student_type' => $profile->user?->student_type,
                'employee_number' => $profile->user?->employee_number,
                'contact_no' => $profile->user?->contact_no,
                'course' => $profile->user?->course,
                'year' => $profile->user?->year,
                'section' => $profile->user?->section,
            ],
            'review' => [
                'approved_by' => $profile->approvedBy?->name,
            ],
        ];
    }

    public function latestApproved(EmployeeHealthProfile $profile): ?HealthFormSubmission
    {
        if ($profile->relationLoaded('healthRecordListApprovedSubmission')) {
            return $profile->getRelation('healthRecordListApprovedSubmission');
        }
        return HealthFormSubmission::query()
            ->where('employee_health_profile_id', $profile->id)
            ->where('status', HealthFormSubmission::STATUS_APPROVED)
            ->latest('approved_at')
            ->latest('submitted_at')
            ->latest('id')
            ->first();
    }

    public function preloadLatestApproved(\Illuminate\Support\Collection $profiles): void
    {
        if ($profiles->isEmpty()) return;
        // First choose version IDs using small metadata rows, then hydrate only those snapshots.
        $ids = HealthFormSubmission::query()->whereIn('employee_health_profile_id', $profiles->pluck('id'))
            ->where('status', HealthFormSubmission::STATUS_APPROVED)
            ->latest('approved_at')->latest('submitted_at')->latest('id')
            ->get(['id', 'employee_health_profile_id', 'approved_at', 'submitted_at'])
            ->unique('employee_health_profile_id')->pluck('id');
        $submissions = $ids->isEmpty() ? collect() : HealthFormSubmission::whereIn('id', $ids)->get()->keyBy('employee_health_profile_id');
        foreach ($profiles as $profile) {
            $profile->setRelation('healthRecordListApprovedSubmission', $submissions->get($profile->id));
        }
    }

    public function ensureApprovedSnapshot(EmployeeHealthProfile $profile): ?HealthFormSubmission
    {
        $profile->loadMissing(['user', 'approvedBy']);
        if (!$profile->user) {
            return null;
        }

        $latest = $this->latestApproved($profile);
        if ($latest && $this->snapshotMatchesCurrentProfile($latest, $profile)) {
            return $latest;
        }

        $approvedAt = $profile->verified_at ?: $profile->certified_at ?: now();

        return HealthFormSubmission::query()->create([
            'user_id' => $profile->user_id,
            'employee_health_profile_id' => $profile->id,
            'category' => trim((string) $profile->health_form_category) ?: null,
            'school_year' => trim((string) $profile->school_year) ?: null,
            'status' => HealthFormSubmission::STATUS_APPROVED,
            'pdf_path' => $profile->staff_health_form_pdf_path,
            'profile_snapshot' => $this->capture($profile),
            'snapshot_captured_at' => now(),
            'submitted_at' => $profile->certified_at ?: $profile->updated_at ?: now(),
            'approved_at' => $approvedAt,
            'approved_by_user_id' => $profile->approved_by_user_id,
        ]);
    }

    public function createSubmittedSnapshot(
        EmployeeHealthProfile $profile,
        ?HealthProfileCorrectionRequest $request = null
    ): HealthFormSubmission {
        $profile->loadMissing(['user', 'approvedBy']);

        if (data_get($request?->metadata, 'return_for_correction')) {
            return \Illuminate\Support\Facades\DB::transaction(function () use ($profile, $request) {
                EmployeeHealthProfile::query()->whereKey($profile->id)->lockForUpdate()->firstOrFail();
                $submission = app(HealthRecordCorrectionService::class)->resubmissionTarget($profile, $request);
                $submission->forceFill([
                    'status' => HealthFormSubmission::STATUS_SUBMITTED, 'submitted_at' => now(),
                    'pdf_path' => $profile->staff_health_form_pdf_path,
                    'profile_snapshot' => $submission->preserveReviewTiming($this->capture($profile)),
                    'snapshot_captured_at' => now(), 'approved_at' => null,
                ])->save();
                return $submission->fresh();
            });
        }

        return HealthFormSubmission::query()->create([
            'user_id' => $profile->user_id,
            'employee_health_profile_id' => $profile->id,
            'category' => trim((string) $profile->health_form_category) ?: null,
            'school_year' => trim((string) $profile->school_year) ?: null,
            'status' => HealthFormSubmission::STATUS_SUBMITTED,
            'pdf_path' => $profile->staff_health_form_pdf_path,
            'profile_snapshot' => $this->capture($profile),
            'snapshot_captured_at' => now(),
            'requested_by_user_id' => $request?->requested_by_user_id,
            'requested_at' => $request?->requested_at,
            'submitted_at' => now(),
            'remarks' => $request?->admin_note,
        ]);
    }

    public function refreshSubmittedSnapshot(EmployeeHealthProfile $profile, ?HealthProfileCorrectionRequest $request = null): HealthFormSubmission
    {
        $submission = HealthFormSubmission::query()
            ->where('employee_health_profile_id', $profile->id)
            ->whereNull('approved_at')
            ->whereIn('status', [HealthFormSubmission::STATUS_SUBMITTED, HealthFormSubmission::STATUS_NEEDS_CORRECTION, 'under_review'])
            ->latest('submitted_at')->latest('id')->first();
        if (!$submission) {
            return $this->createSubmittedSnapshot($profile, $request);
        }
        $submission->forceFill([
            'pdf_path' => $profile->staff_health_form_pdf_path,
            'profile_snapshot' => $submission->preserveReviewTiming($this->capture($profile)),
            'snapshot_captured_at' => now(),
        ])->save();

        return $submission->fresh();
    }

    public function finalizeLatestSubmission(EmployeeHealthProfile $profile): ?HealthFormSubmission
    {
        $submission = HealthFormSubmission::query()
            ->where('employee_health_profile_id', $profile->id)
            ->whereNull('approved_at')
            ->whereIn('status', [
                HealthFormSubmission::STATUS_SUBMITTED,
                HealthFormSubmission::STATUS_NEEDS_CORRECTION,
                'under_review',
            ])
            ->latest('id')
            ->first();

        if (!$submission) {
            return $this->ensureApprovedSnapshot($profile);
        }

        $submission->forceFill([
            'user_id' => $profile->user_id,
            'category' => trim((string) $profile->health_form_category) ?: null,
            'school_year' => trim((string) $profile->school_year) ?: null,
            'status' => HealthFormSubmission::STATUS_APPROVED,
            'pdf_path' => $profile->staff_health_form_pdf_path,
            'profile_snapshot' => $submission->preserveReviewTiming($this->capture($profile)),
            'snapshot_captured_at' => now(),
            'approved_at' => $profile->verified_at ?: now(),
            'approved_by_user_id' => $profile->approved_by_user_id,
            'status_changed_by_user_id' => $profile->approved_by_user_id,
        ])->save();

        return $submission->fresh();
    }

    private function snapshotMatchesCurrentProfile(
        HealthFormSubmission $submission,
        EmployeeHealthProfile $profile
    ): bool {
        if (empty($submission->profile_snapshot)) {
            return false;
        }

        if ($profile->verified_at && $submission->approved_at) {
            return $profile->verified_at->equalTo($submission->approved_at);
        }

        $snapshotProfile = $submission->snapshotProfile();
        $currentFormDate = (string) ($profile->form_date?->format('Y-m-d') ?? $profile->form_date ?? '');
        $snapshotFormDate = (string) ($snapshotProfile['form_date'] ?? '');

        return $currentFormDate !== ''
            && $currentFormDate === $snapshotFormDate
            && (string) ($snapshotProfile['staff_health_form_pdf_path'] ?? '')
                === (string) ($profile->staff_health_form_pdf_path ?? '');
    }
}
