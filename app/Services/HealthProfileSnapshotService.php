<?php

namespace App\Services;

use App\Models\HealthFormSubmission;
use App\Models\HealthProfile;

class HealthProfileSnapshotService
{
    public const SCHEMA_VERSION = 1;

    public const DOCUMENT_FIELDS = [
        'student_photo',
        'health_declaration',
        'medical_certificate',
        'medical_assessment_upload',
        'chest_xray_result',
        'pwd_id_proof',
        'digital_signature',
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
                'course' => $profile->user?->course,
                'year' => $profile->user?->year,
                'section' => $profile->user?->section,
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
}
