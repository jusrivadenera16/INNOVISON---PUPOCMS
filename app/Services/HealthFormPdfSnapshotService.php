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
        bool $createNewSubmission = false
    ): HealthFormSubmission
    {
        $profile->loadMissing('user');
        $submission = $this->submissionForUpdate($profile, $user, $createNewSubmission);
        $resolvedCategory = trim((string) ($submission->category ?: $category)) ?: 'General';
        $timestamp = now();
        $filePath = $this->buildSnapshotPath($profile, $user, $resolvedCategory, $timestamp);
        $snapshotProfile = $profile->fresh('user') ?: $profile;

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
            'school_year' => trim((string) ($profile->school_year ?? '')) ?: $submission->school_year,
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

    public function saveApprovedSnapshot(HealthProfile $profile, ?User $user = null, ?string $category = null, ?string $remarks = null): HealthFormSubmission
    {
        $profile->loadMissing('user');
        $user = $user ?: $profile->user;

        if (!$user instanceof User) {
            throw new \RuntimeException('Cannot save Health Form PDF snapshot without a linked user.');
        }

        $submission = $this->submissionForUpdate($profile, $user);
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

    private function submissionForUpdate(HealthProfile $profile, User $user, bool $createNewSubmission = false): HealthFormSubmission
    {
        $pendingRequest = HealthFormSubmission::query()
            ->where('user_id', $user->id)
            ->where('status', HealthFormSubmission::STATUS_REQUESTED)
            ->latest('requested_at')
            ->latest('id')
            ->first();

        if ($pendingRequest) {
            return $pendingRequest;
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
