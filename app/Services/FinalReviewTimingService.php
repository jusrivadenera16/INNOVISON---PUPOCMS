<?php

namespace App\Services;

use App\Models\{EmployeeHealthProfile, HealthFormSubmission, HealthProfile, User};
use Illuminate\Support\Facades\DB;

class FinalReviewTimingService
{
    public function open(HealthProfile|EmployeeHealthProfile $profile, ?int $actorId): string
    {
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
}
