<?php

namespace App\Services;

use App\Models\ActivityLog;
use App\Models\EmployeeHealthProfile;
use App\Models\HealthFormSubmission;
use App\Models\HealthProfile;
use App\Models\HealthProfileCorrectionRequest;
use App\Models\StudentMedicalAssessment;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Str;

class HealthRecordHistoryService
{
    public function forEmployeeProfile(EmployeeHealthProfile $profile): Collection
    {
        $events = collect();

        $corrections = HealthProfileCorrectionRequest::query()
            ->withoutGlobalScope('activeLinkedUser')
            ->with(['requestedBy', 'reviewedBy', 'user'])
            ->where('employee_health_profile_id', $profile->id)
            ->get();

        foreach ($corrections as $correction) {
            $metadata = is_array($correction->metadata) ? $correction->metadata : [];
            $isNewForm = $correction->type === HealthProfileCorrectionRequest::TYPE_NEW_HEALTH_FORM
                || ($metadata['request_kind'] ?? null) === 'new_health_form'
                || ($metadata['request_kind'] ?? null) === 'bulk_new_health_form';
            $kind = $isNewForm ? 'New Health Form' : ($correction->type === HealthProfileCorrectionRequest::TYPE_FILE_CORRECTION ? 'File Correction' : 'Health Form Correction');
            $actor = $this->actorName($correction->requestedBy);
            $documents = collect($correction->required_documents ?? [])->map(fn ($item) => Str::headline((string) $item))->implode(', ');
            $details = [
                'Form Category' => $isNewForm ? ($metadata['category'] ?? null) : null,
                'Reason' => $correction->admin_note,
                'Files' => $documents,
            ];
            if ($correction->requested_at ?: $correction->created_at) {
                $events->push($this->event($correction->requested_at ?: $correction->created_at, $kind . ' Requested', 'Requested', $actor, $details, [], 'Requests'));
            }
            if ($correction->submitted_at) {
                $events->push($this->event($correction->submitted_at, $kind . ' Submitted', 'Submitted', $this->actorName($correction->user), [], [], 'Requests'));
            }
            if ($correction->status === HealthProfileCorrectionRequest::STATUS_UNDER_REVIEW && $correction->updated_at) {
                $events->push($this->event($correction->updated_at, $kind . ' Under Review', 'Under Review', $this->actorName($correction->reviewedBy), [], [], 'Requests'));
            }
            if (in_array($correction->status, [HealthProfileCorrectionRequest::STATUS_APPROVED, HealthProfileCorrectionRequest::STATUS_CANCELLED], true)) {
                $closedAt = $correction->reviewed_at ?: $correction->updated_at;
                if ($closedAt) {
                    $completed = $correction->status === HealthProfileCorrectionRequest::STATUS_APPROVED;
                    $events->push($this->event($closedAt, $kind . ($completed ? ' Approved' : ' Cancelled'), $completed ? 'Approved' : 'Cancelled', $this->actorName($correction->reviewedBy), [], [], 'Requests'));
                }
            }
        }

        $submissions = HealthFormSubmission::query()
            ->withoutGlobalScope('activeLinkedUser')
            ->with(['requestedBy', 'approvedBy', 'statusChangedBy', 'user'])
            ->where('employee_health_profile_id', $profile->id)
            ->get();

        foreach ($submissions as $submission) {
            $details = ['Form Category' => trim((string) $submission->category) ?: 'General Health Form'];
            if ($submission->requested_at && !$corrections->contains(fn ($correction) =>
                $correction->requested_at && abs($correction->requested_at->getTimestamp() - $submission->requested_at->getTimestamp()) < 60
            )) {
                $actor = $this->actorName($submission->requestedBy);
                $events->push($this->event($submission->requested_at, 'New Health Form Requested', 'Requested', $actor, $details, [], 'Requests'));
            }
            if ($submission->submitted_at) {
                $events->push($this->event($submission->submitted_at, 'Health Form Submitted', 'Submitted', $this->actorName($submission->user), $details, [], 'Forms'));
            }
            if ($submission->approved_at) {
                $events->push($this->event($submission->approved_at, 'Health Form Approved', 'Approved', $this->approvalActor($submission, $profile), $details, [], 'Forms'));
            }
            if ($submission->status === HealthFormSubmission::STATUS_CANCELLED && $submission->updated_at) {
                $events->push($this->event($submission->updated_at, 'Health Form Request Cancelled', 'Cancelled', $this->actorName($submission->statusChangedBy), $details, [], 'Requests'));
            }
            if ($submission->status === HealthFormSubmission::STATUS_NEEDS_CORRECTION && $submission->updated_at) {
                $events->push($this->event($submission->updated_at, 'Health Form Needs Correction', 'Needs Correction', $this->actorName($submission->statusChangedBy), $details, [], 'Forms'));
            }
        }

        $assessments = StudentMedicalAssessment::query()
            ->with('recordedBy')
            ->where('employee_health_profile_id', $profile->id)
            ->get();
        foreach ($assessments as $assessment) {
            if (!$assessment->created_at) continue;
            $events->push($this->event(
                $assessment->created_at,
                'Medical Assessment Updated',
                'Updated',
                $this->actorName($assessment->recordedBy),
                ['Medical Condition' => $assessment->condition_or_illness, 'Findings' => $assessment->findings, 'Remarks' => $assessment->remarks, 'Reason' => $assessment->reason],
                [],
                'Medical Information'
            ));
        }

        $logs = ActivityLog::query()
            ->where('subject_type', EmployeeHealthProfile::class)
            ->where('subject_id', (string) $profile->id)
            ->whereIn('event_type', ['faculty_medical_information_updated', 'admin_medical_information_updated'])
            ->get();
        foreach ($logs as $log) {
            if ($log->created_at) {
                $events->push($this->event($log->created_at, 'Medical Information Updated', 'Completed', $log->user_name ?: 'Not recorded', [], $this->medicalChanges($log), 'Medical Information'));
            }
        }

        return $events->sortByDesc(fn (array $event) => $event['at']->getTimestamp())->values();
    }

    public function forStudentProfile(HealthProfile $profile): Collection
    {
        $events = collect();

        $submissions = HealthFormSubmission::query()
            ->withoutGlobalScope('activeLinkedUser')
            ->with(['requestedBy', 'approvedBy', 'statusChangedBy', 'user'])
            ->where('user_id', $profile->user_id)
            ->where(function ($query) use ($profile) {
                $query->where('health_profile_id', $profile->id)
                    ->orWhereNull('health_profile_id');
            })
            ->get();

        foreach ($submissions as $submission) {
            $category = trim((string) $submission->category) ?: 'General Health Form';
            $actor = $this->actorName($submission->requestedBy);
            if ($submission->requested_at) {
                $events->push($this->event($submission->requested_at, 'New Health Form Requested', 'Requested', $actor, ['Form Category' => $category, 'Reason' => $submission->remarks], [], 'Requests'));
            }
            if ($submission->submitted_at) {
                $events->push($this->event($submission->submitted_at, 'Health Form Submitted', 'Submitted', $this->actorName($submission->user), ['Form Category' => $category], [], 'Forms'));
            }
            if ($submission->approved_at) {
                $events->push($this->event($submission->approved_at, 'Health Form Approved', 'Approved', $this->approvalActor($submission, $profile), ['Form Category' => $category], [], 'Forms'));
            }
            if ($submission->status === HealthFormSubmission::STATUS_CANCELLED && $submission->updated_at) {
                $events->push($this->event($submission->updated_at, 'Health Form Request Cancelled', 'Cancelled', $this->actorName($submission->statusChangedBy), ['Form Category' => $category], [], 'Requests'));
            }
            if ($submission->status === HealthFormSubmission::STATUS_NEEDS_CORRECTION && $submission->updated_at) {
                $events->push($this->event($submission->updated_at, 'Health Form Needs Correction', 'Needs Correction', $this->actorName($submission->statusChangedBy), ['Form Category' => $category], [], 'Forms'));
            }
        }

        $corrections = HealthProfileCorrectionRequest::query()
            ->withoutGlobalScope('activeLinkedUser')
            ->with(['requestedBy', 'reviewedBy', 'user'])
            ->where('user_id', $profile->user_id)
            ->where('health_profile_id', $profile->id)
            ->whereIn('type', [
                HealthProfileCorrectionRequest::TYPE_FILE_CORRECTION,
                HealthProfileCorrectionRequest::TYPE_HEALTH_FORM_CORRECTION,
            ])
            ->get();

        foreach ($corrections as $correction) {
            $kind = $correction->type === HealthProfileCorrectionRequest::TYPE_HEALTH_FORM_CORRECTION
                ? 'Health form correction'
                : 'File correction';
            $documents = collect($correction->required_documents ?? [])->map(fn ($item) => Str::headline((string) $item))->implode(', ');
            $details = ['Reason' => $correction->admin_note, 'Files' => $documents];
            $actor = $this->actorName($correction->requestedBy);
            $requestedAt = $correction->requested_at ?: $correction->created_at;
            if ($requestedAt) {
                $events->push($this->event($requestedAt, Str::title($kind) . ' Requested', 'Requested', $actor, $details, [], 'Requests'));
            }
            if ($correction->submitted_at) {
                $events->push($this->event($correction->submitted_at, Str::title($kind) . ' Submitted', 'Submitted', $this->actorName($correction->user), [], [], 'Requests'));
            }
            if ($correction->status === HealthProfileCorrectionRequest::STATUS_UNDER_REVIEW && $correction->updated_at) {
                $events->push($this->event($correction->updated_at, Str::title($kind) . ' Under Review', 'Under Review', $this->actorName($correction->reviewedBy), [], [], 'Requests'));
            }
            if (in_array($correction->status, [
                HealthProfileCorrectionRequest::STATUS_APPROVED,
                HealthProfileCorrectionRequest::STATUS_CANCELLED,
            ], true)) {
                $closedAt = $correction->reviewed_at ?: $correction->updated_at;
                if ($closedAt) {
                    $events->push($this->event(
                        $closedAt,
                        Str::title($kind) . ($correction->status === HealthProfileCorrectionRequest::STATUS_APPROVED ? ' Approved' : ' Cancelled'),
                        Str::headline($correction->status),
                        $this->actorName($correction->reviewedBy),
                        [],
                        [],
                        'Requests'
                    ));
                }
            }
        }

        // Older records may have only the profile timestamps, before request rows existed.
        if ($profile->resubmission_requested_at && !$corrections->contains(fn ($item) =>
            $item->requested_at && abs($item->requested_at->getTimestamp() - $profile->resubmission_requested_at->getTimestamp()) < 60
        )) {
            $events->push($this->event($profile->resubmission_requested_at, 'File Correction Requested', 'Requested', 'Not recorded', [], [], 'Requests'));
        }
        if ($profile->resubmitted_at && !$corrections->contains(fn ($item) =>
            $item->submitted_at && abs($item->submitted_at->getTimestamp() - $profile->resubmitted_at->getTimestamp()) < 60
        )) {
            $events->push($this->event($profile->resubmitted_at, 'File Correction Submitted', 'Submitted', $this->actorName($profile->user), [], [], 'Requests'));
        }

        $assessments = StudentMedicalAssessment::query()
            ->with('recordedBy')
            ->where('health_profile_id', $profile->id)
            ->get();
        foreach ($assessments as $assessment) {
            if (!$assessment->created_at) continue;
            $events->push($this->event(
                $assessment->created_at,
                'Medical Assessment Updated',
                'Updated',
                $this->actorName($assessment->recordedBy),
                [
                    'Medical Condition' => $assessment->condition_or_illness,
                    'Findings' => $assessment->findings,
                    'Remarks' => $assessment->remarks,
                    'Reason' => $assessment->reason,
                ],
                [],
                'Medical Information'
            ));
        }

        $logs = ActivityLog::query()
            ->where('subject_type', HealthProfile::class)
            ->where('subject_id', (string) $profile->id)
            ->whereIn('event_type', [
                'student_medical_information_updated',
                'applicant_medical_information_updated',
                'health_profile_pulled_out',
                'health_profile_pullout_restored',
                'student_health_clearance_revoked',
                'health_profile_for_final_review',
                'health_profile_for_approval',
            ])
            ->get();

        foreach ($logs as $log) {
            if (!$log->created_at) continue;
            $actor = $log->user_name ?: 'Not recorded';
            if (str_ends_with((string) $log->event_type, '_medical_information_updated')) {
                $events->push($this->event($log->created_at, 'Medical Information Updated', 'Completed', $actor, [], $this->medicalChanges($log), 'Medical Information'));
                continue;
            }

            if ($log->event_type === 'student_health_clearance_revoked') {
                $metadata = is_array($log->metadata) ? $log->metadata : [];
                $sameCurrentAction = $profile->clearance_revoked_at
                    && abs($profile->clearance_revoked_at->getTimestamp() - $log->created_at->getTimestamp()) < 60;
                $events->push($this->event(
                    $log->created_at,
                    'Clearance Revoked',
                    'Revoked',
                    $actor,
                    ['Previous Status' => $metadata['previous_status'] ?? null, 'Reason' => $this->decryptText($metadata['reason_encrypted'] ?? null) ?: ($sameCurrentAction ? $profile->pending_reason : null)],
                    [],
                    'Status Changes'
                ));
                continue;
            }

            if (in_array($log->event_type, ['health_profile_for_final_review', 'health_profile_for_approval'], true)) {
                $isFinalReview = $log->event_type === 'health_profile_for_final_review';
                $events->push($this->event(
                    $log->created_at,
                    $isFinalReview ? 'Moved to Final Review' : 'Moved to Pending Approval',
                    $isFinalReview ? 'Final Review' : 'Pending Approval',
                    $actor,
                    [],
                    [],
                    'Status Changes'
                ));
                continue;
            }

            $isPulledOut = $log->event_type === 'health_profile_pulled_out';
            $metadata = is_array($log->metadata) ? $log->metadata : [];
            $currentDate = $isPulledOut ? $profile->pullout_completed_at : $profile->pullout_restored_at;
            $sameCurrentAction = $currentDate && abs($currentDate->getTimestamp() - $log->created_at->getTimestamp()) < 60;
            $details = [
                'Reason' => $this->decryptText($metadata['reason_encrypted'] ?? null)
                    ?: ($sameCurrentAction ? ($isPulledOut ? $profile->pullout_reason : $profile->pullout_restore_reason) : null),
                'Remarks' => $isPulledOut
                    ? ($this->decryptText($metadata['remarks_encrypted'] ?? null)
                        ?: ($sameCurrentAction ? $profile->pullout_request_remarks : null))
                    : null,
            ];
            $events->push($this->event(
                $log->created_at,
                $isPulledOut ? 'Health Record Pulled Out' : 'Health Record Restored',
                $isPulledOut ? 'Pulled Out' : 'Restored',
                $actor,
                $details,
                [],
                'Status Changes'
            ));
        }

        foreach ([
            ['at' => $profile->pullout_completed_at, 'type' => 'health_profile_pulled_out', 'title' => 'Health Record Pulled Out', 'status' => 'Pulled Out', 'details' => ['Reason' => $profile->pullout_reason, 'Remarks' => $profile->pullout_request_remarks]],
            ['at' => $profile->pullout_restored_at, 'type' => 'health_profile_pullout_restored', 'title' => 'Health Record Restored', 'status' => 'Restored', 'details' => ['Reason' => $profile->pullout_restore_reason]],
            ['at' => $profile->clearance_revoked_at, 'type' => 'student_health_clearance_revoked', 'title' => 'Clearance Revoked', 'status' => 'Revoked', 'details' => ['Reason' => $profile->pending_reason]],
        ] as $legacyStatus) {
            if (!$legacyStatus['at'] || $logs->contains(fn ($log) =>
                $log->event_type === $legacyStatus['type']
                && $log->created_at
                && abs($log->created_at->getTimestamp() - $legacyStatus['at']->getTimestamp()) < 60
            )) continue;

            $events->push($this->event(
                $legacyStatus['at'],
                $legacyStatus['title'],
                $legacyStatus['status'],
                'Not recorded',
                $legacyStatus['details'],
                [],
                'Status Changes'
            ));
        }

        return $events
            ->sortByDesc(fn (array $event) => $event['at']->getTimestamp())
            ->values();
    }

    private function event($at, string $title, string $status, string $actor, array $details, array $changes = [], string $category = 'Status Changes'): array
    {
        return [
            'at' => $at,
            'title' => $title,
            'status' => $status,
            'actor' => $actor,
            'category' => $category,
            'details' => collect($details)->filter(fn ($detail) => filled($detail))->map(fn ($detail, $label) => [
                'label' => (string) $label,
                'value' => (string) $detail,
            ])->values()->all(),
            'changes' => $changes,
        ];
    }

    private function actorName($user): string
    {
        return trim((string) ($user?->name ?: $user?->email ?: '')) ?: 'Not recorded';
    }

    private function approvalActor(HealthFormSubmission $submission, HealthProfile|EmployeeHealthProfile $profile): string
    {
        if ($submission->approvedBy) {
            return $this->actorName($submission->approvedBy);
        }

        $snapshotApprover = data_get($submission->profile_snapshot, 'review.approved_by');
        if (filled($snapshotApprover)) {
            return (string) $snapshotApprover;
        }

        // Older submissions predate per-version approver tracking. Match the
        // current profile approval time before using its recorded approver.
        if ($profile->verified_at && $submission->approved_at
            && abs($profile->verified_at->getTimestamp() - $submission->approved_at->getTimestamp()) < 60) {
            return $this->actorName($profile->approvedBy);
        }

        return 'Not recorded';
    }

    private function medicalChanges(ActivityLog $log): array
    {
        $metadata = is_array($log->metadata) ? $log->metadata : [];
        $fields = is_array($metadata['updated_fields'] ?? null) ? $metadata['updated_fields'] : [];
        $labels = is_array($metadata['updated_field_labels'] ?? null) ? $metadata['updated_field_labels'] : [];
        $before = $this->decryptValues($metadata['previous_values_encrypted'] ?? null);
        $after = $this->decryptValues($metadata['updated_values_encrypted'] ?? null);

        return collect($fields)->map(function ($field, $index) use ($labels, $before, $after) {
            $field = (string) $field;
            return [
                'label' => (string) ($labels[$index] ?? Str::headline($field)),
                'before' => array_key_exists($field, $before) ? $this->displayValue($before[$field]) : 'Earlier value unavailable',
                'after' => array_key_exists($field, $after) ? $this->displayValue($after[$field]) : 'Updated value unavailable',
            ];
        })->all();
    }

    private function decryptValues($encrypted): array
    {
        if (!is_string($encrypted) || $encrypted === '') return [];
        try {
            $decoded = json_decode(Crypt::decryptString($encrypted), true);
            return is_array($decoded) ? $decoded : [];
        } catch (\Throwable) {
            return [];
        }
    }

    private function decryptText($encrypted): ?string
    {
        if (!is_string($encrypted) || $encrypted === '') return null;
        try {
            return Crypt::decryptString($encrypted);
        } catch (\Throwable) {
            return null;
        }
    }

    private function displayValue($value): string
    {
        if ($value === null || $value === '') return 'Not recorded';
        if (is_bool($value)) return $value ? 'Yes' : 'No';
        if (is_array($value)) return json_encode($value, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) ?: 'Not recorded';
        return (string) $value;
    }
}
