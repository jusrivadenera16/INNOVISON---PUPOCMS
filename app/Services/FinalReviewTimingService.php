<?php

namespace App\Services;

use App\Models\{EmployeeHealthProfile, HealthFormSubmission, HealthProfile, User};
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class FinalReviewTimingService
{
    public function open(HealthProfile|EmployeeHealthProfile $profile, ?int $actorId): string
    {
        if ($profile instanceof HealthProfile) {
            return $this->openStudentHealthProfile($profile, $actorId);
        }

        return DB::transaction(function () use ($profile, $actorId) {
            User::query()->whereKey($profile->user_id)->lockForUpdate()->firstOrFail();
            $profile = $profile->newQuery()->whereKey($profile->id)->lockForUpdate()->firstOrFail();
            $key = $profile instanceof EmployeeHealthProfile ? 'employee_health_profile_id' : 'health_profile_id';
            $submission = HealthFormSubmission::query()->where($key, $profile->id)->where('user_id', $profile->user_id)
                ->latest('id')->lockForUpdate()->first();
            if ($submission) {
                abort_unless(!$submission->approved_at && $submission->submitted_at && in_array($submission->status,
                    ['submitted', 'under_review', 'needs_correction'], true), 422, 'No active submitted Health Form is awaiting Final Review.');
            } else {
                // Older records may predate submission history. Never reopen an approved record.
                abort_if(in_array(strtolower((string) $profile->clearance_status), ['approved', 'issued', 'fully cleared', 'cleared', 'withdrawn'], true), 422,
                    'Start a new Health Form before opening another Final Review.');
                $snapshot = $profile instanceof EmployeeHealthProfile
                    ? app(EmployeeHealthFormHistoryService::class)->capture($profile)
                    : app(HealthProfileSnapshotService::class)->capture($profile);
                $submission = HealthFormSubmission::create([
                    'user_id' => $profile->user_id, $key => $profile->id, 'status' => 'submitted',
                    'category' => $profile->health_form_category, 'submitted_at' => $profile->resubmitted_at ?: $profile->created_at ?: now(),
                    'profile_snapshot' => $snapshot,
                ]);
            }
            $openedAt = now()->toIso8601String();
            $snapshot = $submission->profile_snapshot ?: ['profile' => [], 'user' => []];
            $snapshot['review_timing'] = ['opened_at' => $openedAt, 'opened_by_user_id' => $actorId];
            $submission->profile_snapshot = $snapshot;
            $submission->save();

            return $openedAt;
        });
    }

    private function openStudentHealthProfile(HealthProfile $profile, ?int $actorId): string
    {
        $submissionId = null;
        $openedAt = now()->toIso8601String();

        DB::transaction(function () use ($profile, $actorId, &$submissionId, $openedAt): void {
            User::query()->whereKey($profile->user_id)->lockForUpdate()->firstOrFail();
            $profile = $profile->newQuery()->whereKey($profile->id)->lockForUpdate()->firstOrFail();
            $submission = HealthFormSubmission::query()
                ->where('health_profile_id', $profile->id)
                ->where('user_id', $profile->user_id)
                ->latest('id')
                ->lockForUpdate()
                ->first();

            if ($submission) {
                abort_unless(!$submission->approved_at && $submission->submitted_at && in_array(
                    strtolower((string) $submission->status),
                    ['submitted', 'under_review', 'needs_correction'],
                    true
                ), 422, 'No active submitted Health Form is awaiting Final Review.');
                $submissionId = (int) $submission->id;
            } else {
                // Legacy applicant records can be in the Final Review queue without
                // a history row. Save Time In to the profile first; legacy history
                // is retained below as a best-effort follow-up.
                abort_if(in_array(strtolower((string) $profile->clearance_status), [
                    'approved', 'issued', 'fully cleared', 'cleared', 'withdrawn',
                ], true), 422, 'Start a new Health Form before opening another Final Review.');
            }

            if (!Schema::hasColumn('health_profiles', 'review_started_at')) {
                throw new \RuntimeException('The health_profiles.review_started_at column is missing.');
            }

            $profile->review_started_at = $openedAt;
            if (Schema::hasColumn('health_profiles', 'review_started_by_user_id')) {
                $profile->review_started_by_user_id = $actorId;
            }
            $profile->save();
        });

        // The profile timestamp is the durable Time In source. Keep the per-form
        // snapshot too when available, but a snapshot/encryption issue must not
        // undo a successfully recorded Time In.
        try {
            DB::transaction(function () use ($profile, &$submissionId, $openedAt, $actorId): void {
                $submission = $submissionId
                    ? HealthFormSubmission::query()->lockForUpdate()->find($submissionId)
                    : HealthFormSubmission::query()
                        ->where('health_profile_id', $profile->id)
                        ->where('user_id', $profile->user_id)
                        ->latest('id')
                        ->lockForUpdate()
                        ->first();

                if (!$submission && !$submissionId) {
                    $freshProfile = HealthProfile::query()->lockForUpdate()->find($profile->id);
                    if (!$freshProfile) {
                        return;
                    }

                    $snapshot = app(HealthProfileSnapshotService::class)->capture($freshProfile);
                    $submission = HealthFormSubmission::create([
                        'user_id' => $freshProfile->user_id,
                        'health_profile_id' => $freshProfile->id,
                        'status' => 'submitted',
                        'category' => $freshProfile->health_form_category,
                        'submitted_at' => $freshProfile->resubmitted_at ?: $freshProfile->created_at ?: now(),
                        'profile_snapshot' => $snapshot,
                    ]);
                }

                if (!$submission || $submission->approved_at || !$submission->submitted_at
                    || !in_array(strtolower((string) $submission->status), ['submitted', 'under_review', 'needs_correction'], true)) {
                    return;
                }

                $submissionId = (int) $submission->id;
                $snapshot = $submission->profile_snapshot ?: ['profile' => [], 'user' => []];
                $snapshot['review_timing'] = [
                    'opened_at' => $openedAt,
                    'opened_by_user_id' => $actorId,
                ];
                $submission->profile_snapshot = $snapshot;
                $submission->save();
            });
        } catch (\Throwable $exception) {
            \Log::warning('Final Review Time In history snapshot update failed; profile timestamp was saved.', [
                'health_form_submission_id' => $submissionId,
                'user_id' => $profile->user_id,
                ...\App\Support\SensitiveLogRedactor::exceptionContext($exception),
            ]);
        }

        return $openedAt;
    }
}
