<?php

namespace App\Services;

use App\Models\EmployeeHealthProfile;
use App\Models\HealthFormSubmission;
use App\Models\HealthProfileCorrectionRequest;

class EmployeeHealthFormHistoryService
{
    public const SCHEMA_VERSION = 1;

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
        return HealthFormSubmission::query()
            ->where('employee_health_profile_id', $profile->id)
            ->where('status', HealthFormSubmission::STATUS_APPROVED)
            ->latest('approved_at')
            ->latest('submitted_at')
            ->latest('id')
            ->first();
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
        ]);
    }

    public function createSubmittedSnapshot(
        EmployeeHealthProfile $profile,
        ?HealthProfileCorrectionRequest $request = null
    ): HealthFormSubmission {
        $profile->loadMissing(['user', 'approvedBy']);

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

    public function finalizeLatestSubmission(EmployeeHealthProfile $profile): ?HealthFormSubmission
    {
        $submission = HealthFormSubmission::query()
            ->where('employee_health_profile_id', $profile->id)
            ->whereIn('status', [
                HealthFormSubmission::STATUS_SUBMITTED,
                HealthFormSubmission::STATUS_NEEDS_CORRECTION,
            ])
            ->latest('submitted_at')
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
            'profile_snapshot' => $this->capture($profile),
            'snapshot_captured_at' => now(),
            'approved_at' => $profile->verified_at ?: now(),
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
