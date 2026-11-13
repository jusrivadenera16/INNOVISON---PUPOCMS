<?php

namespace App\Services;

use App\Models\HealthFormSubmission;
use App\Models\HealthProfile;
use App\Models\User;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class HealthFormPdfSnapshotService
{
    public function __construct(
        private HealthFileStorage $healthFiles,
        private HealthProfileSnapshotService $profileSnapshots
    ) {
    }

    public function recordSubmittedWithoutPdf(
        HealthProfile $profile,
        User $user,
        ?string $category = null,
        ?string $remarks = null,
        bool $createNewSubmission = false,
        ?HealthProfile $snapshotProfile = null,
        bool $refreshDeclaration = false,
        ?HealthFormSubmission $targetSubmission = null
    ): HealthFormSubmission
    {
        return DB::transaction(function () use ($profile, $user, $category, $remarks, $createNewSubmission, $snapshotProfile, $refreshDeclaration, $targetSubmission) {
            HealthProfile::query()->whereKey($profile->id)->lockForUpdate()->firstOrFail();
            return $this->writeSubmittedSnapshot($profile, $user, $category, $remarks, $createNewSubmission, $snapshotProfile, $refreshDeclaration, $targetSubmission);
        });
    }

    private function writeSubmittedSnapshot(
        HealthProfile $profile, User $user, ?string $category, ?string $remarks,
        bool $createNewSubmission, ?HealthProfile $snapshotProfile, bool $refreshDeclaration, ?HealthFormSubmission $targetSubmission = null
    ): HealthFormSubmission {
        $profile->loadMissing('user');
        if ($targetSubmission && ((int) $targetSubmission->health_profile_id !== (int) $profile->id
            || (int) $targetSubmission->user_id !== (int) $user->id || $targetSubmission->approved_at)) {
            throw new \RuntimeException('The correction submission does not belong to this pending Health Form.');
        }
        $submission = $targetSubmission ?: $this->submissionForUpdate($profile, $user, $createNewSubmission);
        $resolvedCategory = trim((string) ($submission->category ?: $category)) ?: 'General';
        $timestamp = now();
        $filePath = $this->buildSnapshotPath($profile, $user, $resolvedCategory, $timestamp);
        $snapshotProfile = $snapshotProfile ?: ($profile->fresh('user') ?: $profile);
        if ($refreshDeclaration) {
            $snapshotProfile = clone $snapshotProfile;
            $snapshotProfile->health_declaration = app(StudentHealthDeclarationService::class)->generate(
                $snapshotProfile, $user, $resolvedCategory, $timestamp, $remarks
            );
        }

        $pdf = Pdf::loadView('student.print_health_form', [
            'profile' => $snapshotProfile,
            'pdfMode' => true,
            'healthFormIdentity' => [],
            'healthFormSubmittedAt' => $timestamp,
        ]);
        $pdf->setPaper([0, 0, 612, 936]);
        if (!$this->healthFiles->put($filePath, $pdf->output())) {
            throw new \RuntimeException('Unable to write the submitted Health Form PDF.');
        }

        $oldPath = $this->normalizeStoragePath((string) $submission->pdf_path);

        $submission->fill([
            'user_id' => $user->id,
            'health_profile_id' => $profile->id,
            'category' => $resolvedCategory,
            'school_year' => trim((string) ($snapshotProfile->school_year ?? $profile->school_year ?? '')) ?: $submission->school_year,
            'status' => HealthFormSubmission::STATUS_SUBMITTED,
            'pdf_path' => $filePath,
            'profile_snapshot' => $submission->preserveReviewTiming($this->profileSnapshots->capture($snapshotProfile)),
            'snapshot_captured_at' => $timestamp,
            'submitted_at' => $timestamp,
            'approved_at' => null,
            'remarks' => trim((string) ($submission->remarks ?? $remarks)) ?: null,
        ]);
        $submission->save();

        if (!$targetSubmission) $this->deleteReplacedDraftPdf($oldPath, $filePath);

        return $submission;
    }

    public function saveApprovedSnapshot(
        HealthProfile $profile,
        ?User $user = null,
        ?string $category = null,
        ?string $remarks = null,
        ?HealthFormSubmission $targetSubmission = null
    ): HealthFormSubmission
    {
        return DB::transaction(function () use ($profile, $user, $category, $remarks, $targetSubmission) {
            HealthProfile::query()->whereKey($profile->id)->lockForUpdate()->firstOrFail();
            return $this->writeApprovedSnapshot($profile, $user, $category, $remarks, $targetSubmission);
        });
    }

    private function writeApprovedSnapshot(
        HealthProfile $profile, ?User $user, ?string $category, ?string $remarks, ?HealthFormSubmission $targetSubmission
    ): HealthFormSubmission {
        $profile->loadMissing('user');
        $user = $user ?: $profile->user;

        if (!$user instanceof User) {
            throw new \RuntimeException('Cannot save Health Form PDF snapshot without a linked user.');
        }

        $submission = $targetSubmission ?: $this->submissionForUpdate($profile, $user, false, false);
        // Repeated approval without a new submission must not invent another form.
        if (!$submission->exists) {
            $approved = HealthFormSubmission::query()
                ->where('health_profile_id', $profile->id)
                ->where(function ($query) {
                    $query->whereNotNull('approved_at')->orWhereIn('status', ['approved', 'Approved']);
                })
                ->latest('id')->first();
            if ($approved) return $approved;
        }
        $persistedSubmission = $submission->exists ? $submission->fresh() : null;
        if ($persistedSubmission && ($persistedSubmission->approved_at
            || strcasecmp((string) $persistedSubmission->status, HealthFormSubmission::STATUS_APPROVED) === 0)) {
            return $persistedSubmission;
        }
        $resolvedCategory = trim((string) ($submission->category ?: $category ?: $profile->health_form_category)) ?: 'General';
        $timestamp = now();
        $submittedAt = $submission->submitted_at ?: $profile->resubmitted_at ?: $profile->created_at ?: $timestamp;
        $filePath = $this->buildSnapshotPath($profile, $user, $resolvedCategory, $timestamp);
        $snapshotProfile = $profile->fresh('user') ?: $profile;

        $pdf = Pdf::loadView('student.print_health_form', [
            'profile' => $snapshotProfile,
            'pdfMode' => true,
            'healthFormIdentity' => [],
            'healthFormSubmittedAt' => $submittedAt,
        ]);
        $pdf->setPaper([0, 0, 612, 936]);
        if (!$this->healthFiles->put($filePath, $pdf->output())) {
            throw new \RuntimeException('Unable to write the approved Health Form PDF.');
        }

        $oldPath = $this->normalizeStoragePath((string) $submission->pdf_path);

        $submission->fill([
            'user_id' => $user->id,
            'health_profile_id' => $profile->id,
            'category' => $resolvedCategory,
            'school_year' => trim((string) ($profile->school_year ?? '')) ?: $submission->school_year,
            'status' => HealthFormSubmission::STATUS_APPROVED,
            'pdf_path' => $filePath,
            'profile_snapshot' => $submission->preserveReviewTiming($this->profileSnapshots->capture($snapshotProfile)),
            'snapshot_captured_at' => $timestamp,
            'submitted_at' => $submittedAt,
            'approved_at' => $timestamp,
            'remarks' => trim((string) ($submission->remarks ?? $remarks)) ?: null,
        ]);
        $submission->save();

        $this->deleteReplacedDraftPdf($oldPath, $filePath);

        return $submission;
    }

    public function ensureApprovedSnapshot(HealthProfile $profile, ?User $user = null): HealthFormSubmission
    {
        $profile->loadMissing('user');
        $user = $user ?: $profile->user;

        if (!$user instanceof User) {
            throw new \RuntimeException('Cannot save Health Form PDF snapshot without a linked user.');
        }

        $approved = HealthFormSubmission::query()
            ->where('health_profile_id', $profile->id)
            ->whereIn('status', [HealthFormSubmission::STATUS_APPROVED, 'Approved'])
            ->orderByDesc('approved_at')
            ->orderByDesc('id')
            ->first();

        if (!$approved) {
            return $this->saveApprovedSnapshot(
                $profile,
                $user,
                null,
                null,
                new HealthFormSubmission([
                    'user_id' => $user->id,
                    'health_profile_id' => $profile->id,
                ])
            );
        }

        $oldPath = $this->normalizeStoragePath((string) $approved->pdf_path);
        if ($oldPath !== '' && $this->healthFiles->exists($oldPath)) {
            return $approved;
        }

        $snapshotProfile = $profile->fresh('user') ?: $profile;
        $snapshotUser = $snapshotProfile->user ?: $user;
        if ($approved->snapshotProfile() !== []) {
            $this->profileSnapshots->applySubmissionSnapshot($snapshotProfile, $approved, $snapshotUser);
        } else {
            $approved->profile_snapshot = $this->profileSnapshots->capture($snapshotProfile);
            $approved->snapshot_captured_at = now();
        }

        $timestamp = now();
        $category = trim((string) $approved->category) ?: 'General';
        $filePath = $this->buildSnapshotPath($profile, $user, $category, $timestamp);
        $pdf = Pdf::loadView('student.print_health_form', [
            'profile' => $snapshotProfile,
            'pdfMode' => true,
            'healthFormIdentity' => [],
            'healthFormSubmittedAt' => $approved->submitted_at ?: $approved->approved_at ?: $timestamp,
        ]);
        $pdf->setPaper([0, 0, 612, 936]);

        if (!$this->healthFiles->put($filePath, $pdf->output())) {
            throw new \RuntimeException('Unable to write the approved Health Form PDF snapshot.');
        }

        $approved->pdf_path = $filePath;
        $approved->save();

        return $approved->fresh();
    }

    public function refreshExistingSnapshot(HealthProfile $profile, ?array $updatedFields = null): ?HealthFormSubmission
    {
        return DB::transaction(function () use ($profile, $updatedFields) {
            HealthProfile::query()->whereKey($profile->id)->lockForUpdate()->firstOrFail();
            return $this->refreshPendingSnapshot($profile, $updatedFields);
        });
    }

    private function refreshPendingSnapshot(HealthProfile $profile, ?array $updatedFields): ?HealthFormSubmission
    {
        $profile->loadMissing('user');
        $user = $profile->user;

        if (!$user instanceof User) {
            throw new \RuntimeException('Cannot refresh a Health Form PDF snapshot without a linked user.');
        }

        $submission = HealthFormSubmission::query()
            ->where('health_profile_id', $profile->id)
            ->whereNull('approved_at')
            ->whereIn('status', [
                HealthFormSubmission::STATUS_SUBMITTED,
                HealthFormSubmission::STATUS_NEEDS_CORRECTION,
                'under_review',
            ])
            ->latest('id')
            ->first();

        if (!$submission) {
            return null;
        }

        $timestamp = now();
        $resolvedCategory = trim((string) $submission->category) ?: 'General';
        $filePath = $this->buildSnapshotPath($profile, $user, $resolvedCategory, $timestamp);
        $oldPath = $this->normalizeStoragePath((string) $submission->pdf_path);
        $snapshotProfile = $profile->fresh('user') ?: $profile;
        if ($updatedFields !== null && $submission->snapshotProfile() !== []) {
            $liveProfile = clone $snapshotProfile;
            $snapshotProfile = clone $snapshotProfile;
            $snapshotProfile->setRelation('user', clone $snapshotProfile->user);
            $this->profileSnapshots->applySubmissionSnapshot($snapshotProfile, $submission, $snapshotProfile->user);
            foreach (array_intersect($updatedFields, HealthProfileSnapshotService::DOCUMENT_FIELDS) as $field) {
                $snapshotProfile->{$field} = $liveProfile->{$field};
            }
        }

        $pdf = Pdf::loadView('student.print_health_form', [
            'profile' => $snapshotProfile,
            'pdfMode' => true,
            'healthFormIdentity' => [],
            'healthFormSubmittedAt' => $submission->submitted_at ?: $profile->resubmitted_at ?: $profile->created_at,
        ]);
        $pdf->setPaper([0, 0, 612, 936]);

        if (!$this->healthFiles->put($filePath, $pdf->output())) {
            throw new \RuntimeException('Unable to write the refreshed Health Form PDF snapshot.');
        }

        try {
            $submission->pdf_path = $filePath;
            $submission->profile_snapshot = $submission->preserveReviewTiming($this->profileSnapshots->capture($snapshotProfile));
            $submission->snapshot_captured_at = $timestamp;
            $submission->save();
        } catch (\Throwable $exception) {
            if ($filePath !== $oldPath) {
                $this->healthFiles->delete($filePath);
            }

            throw $exception;
        }

        $this->deleteReplacedDraftPdf($oldPath, $filePath);

        return $submission->fresh();
    }

    public function refreshAfterDocumentResubmission(HealthProfile $profile, array $updatedFields): HealthFormSubmission
    {
        $profile->loadMissing('user');
        $refreshed = $this->refreshExistingSnapshot($profile, $updatedFields);
        if ($refreshed) {
            return $refreshed;
        }

        // A correction after approval starts a new review copy; the approved PDF stays untouched.
        return $this->recordSubmittedWithoutPdf($profile, $profile->user, $profile->health_form_category, null, true);
    }

    private function submissionForUpdate(
        HealthProfile $profile,
        User $user,
        bool $createNewSubmission = false,
        bool $includePendingRequest = true
    ): HealthFormSubmission
    {
        if ($includePendingRequest) {
            $pendingRequest = HealthFormSubmission::query()
                ->where('user_id', $user->id)
                ->where('status', HealthFormSubmission::STATUS_REQUESTED)
                ->whereNull('approved_at')
                ->latest('requested_at')
                ->latest('id')
                ->first();

            if ($pendingRequest) {
                return $pendingRequest;
            }
        }

        if ($createNewSubmission) {
            return new HealthFormSubmission([
                'user_id' => $user->id,
                'health_profile_id' => $profile->id,
            ]);
        }

        $existing = HealthFormSubmission::query()
            ->where('health_profile_id', $profile->id)
            ->whereNull('approved_at')
            ->whereIn('status', [
                HealthFormSubmission::STATUS_SUBMITTED,
                HealthFormSubmission::STATUS_NEEDS_CORRECTION,
                'under_review',
            ])
            ->latest('id')
            ->first();

        return $existing ?: new HealthFormSubmission([
            'user_id' => $user->id,
            'health_profile_id' => $profile->id,
        ]);
    }

    private function buildSnapshotPath(HealthProfile $profile, User $user, string $category, $timestamp): string
    {
        $baseIdentifier = trim((string) (
            $profile->reference_number
            ?: $user->reference_number
            ?: $profile->student_number
            ?: $user->student_number
            ?: $user->student_id
            ?: $profile->id
        ));
        $safeIdentifier = preg_replace('/[^A-Za-z0-9\-_]+/', '-', $baseIdentifier) ?: (string) $profile->id;
        $safeCategory = preg_replace('/[^A-Za-z0-9\-_]+/', '-', strtolower($category)) ?: 'general';

        return 'health_form_submissions/' . $user->id . '/health-form-' . $safeIdentifier . '-' . $safeCategory . '-' . $timestamp->format('Ymd-His') . '-' . Str::uuid() . '.pdf';
    }

    private function normalizeStoragePath(string $path): string
    {
        $path = ltrim($path, '/');
        return preg_replace('#^(?:public/)?storage/#', '', $path) ?? $path;
    }

    private function deleteReplacedDraftPdf(string $oldPath, string $newPath): void
    {
        if ($oldPath === '' || $oldPath === $newPath) return;
        $cleanup = function () use ($oldPath): void {
            try {
                if (!HealthFormSubmission::query()->where('pdf_path', $oldPath)->exists() && $this->healthFiles->exists($oldPath)) {
                    $this->healthFiles->delete($oldPath);
                }
            } catch (\Throwable $exception) {
                Log::warning('Unable to clean up a replaced draft Health Form PDF.', ['exception_type' => get_class($exception)]);
            }
        };
        if (DB::transactionLevel() > 0) DB::afterCommit($cleanup);
        else $cleanup();
    }
}
