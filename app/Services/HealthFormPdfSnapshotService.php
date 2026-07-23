<?php

namespace App\Services;

use App\Models\HealthFormSubmission;
use App\Models\HealthProfile;
use App\Models\User;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\Storage;

class HealthFormPdfSnapshotService
{
    public function recordSubmittedWithoutPdf(HealthProfile $profile, User $user, ?string $category = null, ?string $remarks = null): HealthFormSubmission
    {
        $submission = $this->submissionForUpdate($profile, $user);

        $submission->fill([
            'user_id' => $user->id,
            'health_profile_id' => $profile->id,
            'category' => $submission->category ?: (trim((string) $category) ?: 'General'),
            'school_year' => trim((string) ($profile->school_year ?? '')) ?: $submission->school_year,
            'status' => HealthFormSubmission::STATUS_SUBMITTED,
            'submitted_at' => now(),
            'approved_at' => null,
            'remarks' => trim((string) ($submission->remarks ?? $remarks)) ?: null,
        ]);
        $submission->save();

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
        $filePath = $this->buildSnapshotPath($profile, $user, $resolvedCategory, $timestamp);

        $pdf = Pdf::loadView('student.print_health_form', [
            'profile' => $profile->fresh('user') ?: $profile,
            'pdfMode' => true,
            'healthFormIdentity' => [],
        ]);
        $pdf->setPaper([0, 0, 612, 936]);
        Storage::disk('public')->put($filePath, $pdf->output());

        $oldPath = $this->normalizeStoragePath((string) $submission->pdf_path);

        $submission->fill([
            'user_id' => $user->id,
            'health_profile_id' => $profile->id,
            'category' => $resolvedCategory,
            'school_year' => trim((string) ($profile->school_year ?? '')) ?: $submission->school_year,
            'status' => HealthFormSubmission::STATUS_APPROVED,
            'pdf_path' => $filePath,
            'submitted_at' => $submission->submitted_at ?: $timestamp,
            'approved_at' => $timestamp,
            'remarks' => trim((string) ($submission->remarks ?? $remarks)) ?: null,
        ]);
        $submission->save();

        if ($oldPath !== '' && $oldPath !== $filePath && Storage::disk('public')->exists($oldPath)) {
            Storage::disk('public')->delete($oldPath);
        }

        return $submission;
    }

    private function submissionForUpdate(HealthProfile $profile, User $user): HealthFormSubmission
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
