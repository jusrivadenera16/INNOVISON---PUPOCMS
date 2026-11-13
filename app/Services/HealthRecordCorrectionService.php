<?php

namespace App\Services;

use App\Models\{ActivityLog, EmployeeHealthProfile, HealthFormSubmission, HealthProfile, HealthProfileCorrectionRequest, User};
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class HealthRecordCorrectionService
{
    public function returnedProfile(User $user, string $type): HealthProfile|EmployeeHealthProfile|null
    {
        $employee = $type === 'employee';
        $key = $employee ? 'employee_health_profile_id' : 'health_profile_id';
        $request = HealthProfileCorrectionRequest::query()->where('user_id', $user->id)
            ->whereNotNull($key)->where('type', HealthProfileCorrectionRequest::TYPE_HEALTH_FORM_CORRECTION)
            ->active()->latest('id')->get()->first(fn ($request) => data_get($request->metadata, 'return_for_correction'));
        if (!$request) return null;
        return ($employee ? EmployeeHealthProfile::query() : HealthProfile::query())
            ->where('user_id', $user->id)->find($request->{$key});
    }

    public function resubmissionTarget(HealthProfile|EmployeeHealthProfile $profile, ?HealthProfileCorrectionRequest $request): ?HealthFormSubmission
    {
        if (!$request || !data_get($request->metadata, 'return_for_correction')) return null;
        $key = $profile instanceof EmployeeHealthProfile ? 'employee_health_profile_id' : 'health_profile_id';
        if ((int) $request->{$key} !== (int) $profile->id || (int) $request->user_id !== (int) $profile->user_id) {
            throw ValidationException::withMessages(['health_form' => 'This correction request does not belong to your Health Form.']);
        }
        $target = HealthFormSubmission::query()->where($key, $profile->id)->latest('id')->lockForUpdate()->first();
        if (!$target || $target->id !== (int) data_get($request->metadata, 'reuse_submission_id')
            || (int) $target->user_id !== (int) $profile->user_id || $target->approved_at
            || $target->status !== HealthFormSubmission::STATUS_NEEDS_CORRECTION) {
            throw ValidationException::withMessages(['health_form' => 'The returned Health Form has changed. Refresh your form before resubmitting.']);
        }
        return $target;
    }

    public function prefill(HealthProfile|EmployeeHealthProfile $profile, ?HealthProfileCorrectionRequest $request): HealthProfile|EmployeeHealthProfile
    {
        $target = $this->resubmissionTarget($profile, $request);
        if (!$target || !$target->profile_snapshot) return $profile;
        $prefill = clone $profile;
        $prefill->forceFill(\Illuminate\Support\Arr::except($target->snapshotProfile(), [
            'id', 'user_id', 'clearance_status', 'submission_status', 'pending_reason', 'resubmission_requested_at',
            'resubmitted_at', 'verified_at', 'approved_by_user_id', 'created_at', 'updated_at',
        ]));
        return $prefill;
    }

    public function request(string $type, int $id, int $submissionId, string $reason, User $actor): HealthProfileCorrectionRequest
    {
        abort_unless(in_array($type, ['student', 'employee'], true), 404);
        return DB::transaction(function () use ($type, $id, $submissionId, $reason, $actor) {
            $employee = $type === 'employee';
            $profile = ($employee ? EmployeeHealthProfile::query() : HealthProfile::query())
                ->with('user')->lockForUpdate()->findOrFail($id);
            $key = $employee ? 'employee_health_profile_id' : 'health_profile_id';
            $submission = HealthFormSubmission::query()->where($key, $id)->latest('id')->lockForUpdate()->first();
            if (!$profile->user || !$submission || $submission->id !== $submissionId
                || (int) $submission->user_id !== (int) $profile->user_id || !$submission->submitted_at
                || $submission->approved_at || !in_array($submission->status, ['submitted', 'under_review', 'needs_correction'], true)
                || in_array(strtolower(trim((string) $profile->pullout_status)), ['pending', 'pulled_out'], true)
                || strtolower(trim((string) $profile->clearance_status)) === 'withdrawn') {
                throw ValidationException::withMessages(['submission_id' => 'Only the latest submitted, unapproved Health Form can be returned for correction. Refresh the list and try again.']);
            }
            $existing = HealthProfileCorrectionRequest::query()->where($key, $id)
                ->where('health_form_submission_id', $submissionId)->where('type', HealthProfileCorrectionRequest::TYPE_HEALTH_FORM_CORRECTION)
                ->active()->latest('id')->first();
            if ($existing) return $existing;

            $reason = trim($reason);
            $correction = HealthProfileCorrectionRequest::create([
                'user_id' => $profile->user_id, $key => $id, 'profile_kind' => $type,
                'health_form_submission_id' => $submissionId,
                'type' => HealthProfileCorrectionRequest::TYPE_HEALTH_FORM_CORRECTION,
                'status' => HealthProfileCorrectionRequest::STATUS_PENDING, 'required_documents' => [],
                'admin_note' => $reason, 'requested_by_user_id' => $actor->id, 'requested_at' => now(),
                'metadata' => ['return_for_correction' => true, 'reuse_submission_id' => $submissionId,
                    'category' => $submission->category, 'previous_pdf_path' => $submission->pdf_path,
                    'original_submitted_at' => $submission->submitted_at?->toIso8601String()],
            ]);
            $submission->forceFill(['status' => HealthFormSubmission::STATUS_NEEDS_CORRECTION,
                'status_changed_by_user_id' => $actor->id])->save();
            $profile->forceFill(['clearance_status' => 'Pending Resubmission',
                'pending_reason' => trim("Health Form Correction\n" . $reason), 'documents_valid' => false,
                'resubmission_requested_at' => now(), 'resubmitted_at' => null,
                'verified_at' => null, 'approved_by_user_id' => null]);
            if ($employee) $profile->submission_status = 'needs_correction';
            $profile->save();
            $profile->user->forceFill(['is_health_profile_completed' => false])->save();
            ActivityLog::create([
                'user_id' => $actor->id, 'user_name' => $actor->name ?: $actor->email, 'user_role' => $actor->user_role,
                'action' => 'Health Form Returned for Correction', 'module' => 'Health Records',
                'event_type' => 'health_form_returned_for_correction', 'subject_type' => $profile::class,
                'subject_id' => (string) $id, 'description' => 'Latest submitted Health Form returned for correction.',
                'metadata' => ['submission_id' => $submissionId, 'correction_request_id' => $correction->id],
            ]);
            return $correction;
        });
    }
}
