<?php

namespace App\Services;

use App\Models\HealthFormSubmission;
use App\Models\HealthProfile;
use App\Models\User;
use Illuminate\Support\Facades\Schema;

class HealthProfileSnapshotService
{
    public const SCHEMA_VERSION = 1;

    public const HEALTH_FORM_PROFILE_FIELDS = [
        'student_id', 'student_number', 'reference_number', 'sis_reference_number',
        'health_form_category', 'suffix_name', 'school_year', 'home_address',
        'street', 'barangay', 'municipality', 'province', 'zipcode', 'birthday',
        'student_photo', 'health_declaration', 'digital_signature', 'guardian_signature',
        'age', 'sex', 'civil_status', 'course_college', 'course_code', 'blood_type',
        'guardian_name', 'landline', 'cellphone', 'chest_xray_result', 'xray_date',
        'xray_findings', 'xray_findings_details', 'has_disability', 'disability_type',
        'has_illness', 'medical_history', 'other_illness', 'food_allergies',
        'no_allergies', 'medicine_allergies', 'other_med_allergies', 'is_smoker',
        'is_drinker', 'covid_vaccinated', 'vaccine_history', 'pwd_id_proof',
        'medical_certificate', 'doctor_name', 'med_cert_date', 'med_cert_findings',
        'med_cert_findings_details', 'privacy_consent_version', 'privacy_consent_at',
    ];

    public const HEALTH_FORM_USER_FIELDS = [
        'student_number', 'reference_number', 'sis_reference_number', 'course',
        'year', 'section', 'DOB', 'contact_no', 'gender', 'suffix_name',
    ];

    public const DOCUMENT_FIELDS = [
        'student_photo',
        'health_declaration',
        'medical_certificate',
        'medical_assessment_upload',
        'chest_xray_result',
        'pwd_id_proof',
        'digital_signature',
        'guardian_signature',
    ];

    public function capture(HealthProfile $profile): array
    {
        $profile->loadMissing('user', 'approvedBy', 'reviewStartedBy');

        $profileData = $profile->attributesToArray();
        unset($profileData['final_review_draft_data']);

        return [
            'schema_version' => self::SCHEMA_VERSION,
            'captured_at' => now()->toIso8601String(),
            'profile' => $profileData,
            'user' => [
                'id' => $profile->user?->id,
                'name' => $profile->user?->name,
                'email' => $profile->user?->email,
                'student_id' => $profile->user?->student_id,
                'student_number' => $profile->user?->student_number,
                'reference_number' => $profile->user?->reference_number,
                'user_type' => $profile->user?->user_type,
                'user_role' => $profile->user?->user_role,
                'idp_role' => $profile->user?->idp_role,
                'student_type' => $profile->user?->student_type,
                'course' => $profile->user?->course,
                'year' => $profile->user?->year,
                'section' => $profile->user?->section,
                'DOB' => $profile->user?->DOB,
                'contact_no' => $profile->user?->contact_no,
                'gender' => $profile->user?->gender,
                'suffix_name' => $profile->user?->suffix_name,
                'sis_reference_number' => $profile->user?->sis_reference_number,
            ],
            'review' => [
                'approved_by' => $profile->approvedBy?->name,
                'review_started_by' => $profile->reviewStartedBy?->name,
            ],
        ];
    }

    public function preserveCurrentBeforeNewSubmission(
        HealthProfile $profile,
        HealthFormSubmission $pendingRequest
    ): ?HealthFormSubmission {
        $currentSubmission = HealthFormSubmission::query()
            ->where('user_id', $profile->user_id)
            ->where('id', '!=', $pendingRequest->id)
            ->whereIn('status', [
                HealthFormSubmission::STATUS_SUBMITTED,
                HealthFormSubmission::STATUS_APPROVED,
                HealthFormSubmission::STATUS_NEEDS_CORRECTION,
            ])
            ->latest('submitted_at')
            ->latest('approved_at')
            ->latest('id')
            ->first();

        if (!$currentSubmission || !empty($currentSubmission->profile_snapshot)) {
            return $currentSubmission;
        }

        $currentSubmission->forceFill([
            'profile_snapshot' => $this->capture($profile),
            'snapshot_captured_at' => now(),
        ])->save();

        return $currentSubmission->fresh();
    }

    public function promoteApprovedSubmission(
        HealthProfile $profile,
        HealthFormSubmission $submission,
        User $user,
        ?int $approvedByUserId = null
    ): HealthProfile {
        if ($submission->snapshotProfile() === []) {
            throw new \RuntimeException('Cannot promote a Health Form without its saved profile snapshot.');
        }

        $this->applySubmissionSnapshot($profile, $submission, $user);

        $clearanceWasWithdrawn = strcasecmp(trim((string) $profile->clearance_status), 'Withdrawn') === 0;
        $clearanceIsProtected = $profile->clearance_revoked_at
            || in_array($profile->pullout_status, [HealthProfile::PULLOUT_PENDING, HealthProfile::PULLOUT_COMPLETED], true)
            || $clearanceWasWithdrawn;
        if (!$clearanceIsProtected) {
            $profile->clearance_status = 'Fully Cleared';
            $profile->verified_at = $submission->approved_at ?: now();
            if ($approvedByUserId && Schema::hasColumn('health_profiles', 'approved_by_user_id')) {
                $profile->approved_by_user_id = $approvedByUserId;
            }
        }
        $profile->save();

        if (Schema::hasColumn('users', 'is_health_profile_completed')) {
            $user->is_health_profile_completed = 1;
        }
        $user->save();

        return $profile;
    }

    public function applySubmissionSnapshot(
        HealthProfile $profile,
        HealthFormSubmission $submission,
        ?User $user = null
    ): HealthProfile {
        $snapshotProfile = $submission->snapshotProfile();
        if ($snapshotProfile === []) {
            return $profile;
        }

        $profileData = [];
        foreach (self::HEALTH_FORM_PROFILE_FIELDS as $field) {
            if (array_key_exists($field, $snapshotProfile) && Schema::hasColumn('health_profiles', $field)) {
                $profileData[$field] = $snapshotProfile[$field];
            }
        }
        $profile->forceFill($profileData);

        if ($user) {
            $this->applySubmissionUserSnapshot($user, $submission);
        }

        return $profile;
    }

    public function applySubmissionUserSnapshot(User $user, HealthFormSubmission $submission): User
    {
        $snapshotUser = $submission->snapshotUser();
        $userData = [];
        foreach (self::HEALTH_FORM_USER_FIELDS as $field) {
            if (array_key_exists($field, $snapshotUser) && Schema::hasColumn('users', $field)) {
                $userData[$field] = $snapshotUser[$field];
            }
        }
        $user->forceFill($userData);

        return $user;
    }
}
