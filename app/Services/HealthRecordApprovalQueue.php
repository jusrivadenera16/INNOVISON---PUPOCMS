<?php

namespace App\Services;

use App\Models\{EmployeeHealthProfile, HealthFormSubmission, HealthProfile};
use Illuminate\Support\Collection;

class HealthRecordApprovalQueue
{
    public function markEligibility(Collection $records): void
    {
        if ($records->isEmpty()) return;
        $healthIds = $records->reject(fn ($record) => $record instanceof EmployeeHealthProfile)->pluck('id');
        $employeeIds = $records->filter(fn ($record) => $record instanceof EmployeeHealthProfile)->pluck('id');
        // Metadata only. The newest request, including cancellation/draft states,
        // must not be replaced by an older submitted version for this profile.
        $submissions = HealthFormSubmission::query()
            ->where(fn ($query) => $query->whereIn('health_profile_id', $healthIds)->orWhereIn('employee_health_profile_id', $employeeIds))
            ->orderByDesc('id')->get(['id', 'user_id', 'health_profile_id', 'employee_health_profile_id', 'status', 'submitted_at', 'approved_at']);
        $healthSubmissions = $submissions->whereNotNull('health_profile_id')->unique('health_profile_id')->keyBy('health_profile_id');
        $employeeSubmissions = $submissions->whereNotNull('employee_health_profile_id')->unique('employee_health_profile_id')->keyBy('employee_health_profile_id');
        $candidates = collect();

        foreach ($records as $record) {
            $record->setAttribute('pending_approval_eligible', false);
            if (!in_array(trim((string) $record->clearance_status), ['', 'Pending', 'For Verification'], true)) continue;
            $employee = $record instanceof EmployeeHealthProfile;
            $submission = ($employee ? $employeeSubmissions : $healthSubmissions)->get($record->id);
            $record->setAttribute('pending_approval_submission_id', $submission?->id);
            if ($submission) {
                $activeSubmission = in_array($submission->status, ['submitted', 'under_review', 'needs_correction'], true)
                    && $submission->submitted_at && !$submission->approved_at;
                // Explicit clearance revocation already starts a revalidation
                // workflow. Do not hide it merely because its saved form was approved.
                $revokedApproval = $record instanceof HealthProfile && filled($record->clearance_revoked_at)
                    && in_array($submission->status, ['approved', 'Approved'], true) && $submission->approved_at;
                if (($submission->user_id !== null && (int) $submission->user_id !== (int) $record->user_id)
                    || (!$activeSubmission && !$revokedApproval)) continue;
                // Never collapse different users or independent request IDs.
                $key = $record->user_id . ':submission:' . $submission->id;
            } else {
                // Legacy profiles have no version metadata. Preserve records with
                // saved uploads, but exclude empty lookup-created placeholders.
                if (!$this->hasLegacyUpload($record)) continue;
                $key = $record->user_id . ':legacy:' . ($employee ? 'employee:' : 'health:') . $record->id;
            }
            $candidates->push(['key' => $key, 'record' => $record, 'employee' => $employee]);
        }

        foreach ($candidates->groupBy('key') as $matches) {
            // A submission with both foreign keys may be returned by both sources.
            // Prefer the account's canonical source only for that SAME request.
            $winner = $matches->sortBy(function ($candidate) {
                $account = $candidate['record']->user?->clinicAccountTypeKey();
                $preferEmployee = in_array($account, ['faculty', 'non_teaching_staff', 'dependent'], true);
                return $candidate['employee'] === $preferEmployee ? 0 : 1;
            })->first();
            $winner['record']->setAttribute('pending_approval_eligible', true);
        }
    }

    private function hasLegacyUpload($record): bool
    {
        foreach (['health_declaration', 'student_photo', 'employee_photo', 'medical_certificate', 'chest_xray_result',
            'chest_xray_document', 'medical_assessment_upload', 'pwd_id_proof', 'staff_health_form_pdf_path'] as $field) {
            if (filled($record->getAttribute($field))) return true;
        }
        return false;
    }
}
