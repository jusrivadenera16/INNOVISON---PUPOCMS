<?php

namespace App\Services;

use App\Models\HealthFormSubmission;
use App\Models\HealthProfile;
use App\Models\User;
use Barryvdh\DomPDF\Facade\Pdf;

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
        ?HealthProfile $snapshotProfile = null
    ): HealthFormSubmission
    {
        $profile->loadMissing('user');
        $submission = $this->submissionForUpdate($profile, $user, $createNewSubmission);
        $resolvedCategory = trim((string) ($submission->category ?: $category)) ?: 'General';
        $timestamp = now();
        $filePath = $this->buildSnapshotPath($profile, $user, $resolvedCategory, $timestamp);
        $snapshotProfile = $snapshotProfile ?: ($profile->fresh('user') ?: $profile);

        $pdf = Pdf::loadView('student.print_health_form', [
            'profile' => $snapshotProfile,
            'pdfMode' => true,
            'healthFormIdentity' => [],
            'healthFormSubmittedAt' => $timestamp,
        ]);
        $pdf->setPaper([0, 0, 612, 936]);
        $this->healthFiles->put($filePath, $pdf->output());

        $oldPath = $this->normalizeStoragePath((string) $submission->pdf_path);

        $submission->fill([
            'user_id' => $user->id,
            'health_profile_id' => $profile->id,
            'category' => $resolvedCategory,
            'school_year' => trim((string) ($snapshotProfile->school_year ?? $profile->school_year ?? '')) ?: $submission->school_year,
            'status' => HealthFormSubmission::STATUS_SUBMITTED,
            'pdf_path' => $filePath,
            'profile_snapshot' => $this->profileSnapshots->capture($snapshotProfile),
            'snapshot_captured_at' => $timestamp,
            'submitted_at' => $timestamp,
            'approved_at' => null,
            'remarks' => trim((string) ($submission->remarks ?? $remarks)) ?: null,
        ]);
        $submission->save();

        if ($oldPath !== '' && $oldPath !== $filePath && $this->healthFiles->exists($oldPath)) {
            $this->healthFiles->delete($oldPath);
        }

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
        $profile->loadMissing('user');
        $user = $user ?: $profile->user;

        if (!$user instanceof User) {
            throw new \RuntimeException('Cannot save Health Form PDF snapshot without a linked user.');
        }

        $submission = $targetSubmission ?: $this->submissionForUpdate($profile, $user, false, false);
        $resolvedCategory = trim((string) ($submission->category ?: $category)) ?: 'General';
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
        $this->healthFiles->put($filePath, $pdf->output());

        $oldPath = $this->normalizeStoragePath((string) $submission->pdf_path);

        $submission->fill([
            'user_id' => $user->id,
            'health_profile_id' => $profile->id,
            'category' => $resolvedCategory,
            'school_year' => trim((string) ($profile->school_year ?? '')) ?: $submission->school_year,
            'status' => HealthFormSubmission::STATUS_APPROVED,
            'pdf_path' => $filePath,
            'profile_snapshot' => $this->profileSnapshots->capture($snapshotProfile),
            'snapshot_captured_at' => $timestamp,
            'submitted_at' => $submittedAt,
            'approved_at' => $timestamp,
            'remarks' => trim((string) ($submission->remarks ?? $remarks)) ?: null,
        ]);
        $submission->save();

        if ($oldPath !== '' && $oldPath !== $filePath && $this->healthFiles->exists($oldPath)) {
            $this->healthFiles->delete($oldPath);
        }

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
            ->where(function ($query) use ($profile, $user) {
                $query->where('health_profile_id', $profile->id)
                    ->orWhere('user_id', $user->id);
            })
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

    public function refreshExistingSnapshot(HealthProfile $profile): ?HealthFormSubmission
    {
        $profile->loadMissing('user');
        $user = $profile->user;

        if (!$user instanceof User) {
            throw new \RuntimeException('Cannot refresh a Health Form PDF snapshot without a linked user.');
        }

        $submission = HealthFormSubmission::query()
            ->where(function ($query) use ($profile, $user) {
                $query->where('health_profile_id', $profile->id)
                    ->orWhere('user_id', $user->id);
            })
            ->whereNotNull('pdf_path')
            ->whereIn('status', [
                HealthFormSubmission::STATUS_SUBMITTED,
                HealthFormSubmission::STATUS_APPROVED,
                HealthFormSubmission::STATUS_NEEDS_CORRECTION,
            ])
            ->latest('submitted_at')
            ->latest('approved_at')
            ->latest('id')
            ->first();

        if (!$submission) {
            return null;
        }

        $timestamp = now();
        $resolvedCategory = trim((string) $submission->category) ?: 'General';
        $filePath = $this->buildSnapshotPath($profile, $user, $resolvedCategory, $timestamp);
        $oldPath = $this->normalizeStoragePath((string) $submission->pdf_path);

        $pdf = Pdf::loadView('student.print_health_form', [
            'profile' => $profile->fresh('user') ?: $profile,
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
            $submission->save();
        } catch (\Throwable $exception) {
            if ($filePath !== $oldPath) {
                $this->healthFiles->delete($filePath);
            }

            throw $exception;
        }

        if ($oldPath !== '' && $oldPath !== $filePath && $this->healthFiles->exists($oldPath)) {
            $this->healthFiles->delete($oldPath);
        }

        return $submission->fresh();
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
            ->where(function ($query) use ($profile, $user) {
                $query->where('health_profile_id', $profile->id)
                    ->orWhere('user_id', $user->id);
            })
            ->whereIn('status', [
                HealthFormSubmission::STATUS_SUBMITTED,
                HealthFormSubmission::STATUS_APPROVED,
                HealthFormSubmission::STATUS_NEEDS_CORRECTION,
            ])
            ->latest('submitted_at')
            ->latest('approved_at')
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

        return 'health_form_submissions/' . $user->id . '/health-form-' . $safeIdentifier . '-' . $safeCategory . '-' . $timestamp->format('Ymd-His') . '.pdf';
    }

    private function normalizeStoragePath(string $path): string
    {
        $path = ltrim($path, '/');
        return preg_replace('#^(?:public/)?storage/#', '', $path) ?? $path;
    }
}
