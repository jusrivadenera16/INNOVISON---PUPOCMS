<?php

namespace App\Services;

use App\Models\{ActivityLog, HealthFormCategory, HealthFormSubmission, HealthProfile, HealthProfileCorrectionRequest, User};
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

/** Temporary pending-student correction; deliberately separate from approval/new-form workflows. */
class TemporaryStudentFormCategoryService
{
    public function options(): array
    {
        return HealthFormCategory::query()->where('is_active', true)->availableFor('student')->orderBy('name')->pluck('name')->all();
    }

    public function pending(HealthProfile $profile): HealthFormSubmission
    {
        abort_unless(config('health_form_features.temporary_student_category_change'), 404);
        abort_unless($profile->user?->clinicAccountTypeKey() === 'student', 404);
        if (in_array(strtolower((string) $profile->clearance_status), ['withdrawn', 'cancelled'], true)
            || in_array($profile->pullout_status, [HealthProfile::PULLOUT_PENDING, HealthProfile::PULLOUT_COMPLETED], true)) {
            throw ValidationException::withMessages(['record' => 'This student record is not available for a pending category change.']);
        }
        $submission = HealthFormSubmission::query()->where('health_profile_id', $profile->id)->latest('id')->first();
        if (!$submission || !$submission->submitted_at || $submission->approved_at || $submission->approved_by_user_id
            || !in_array(strtolower((string) $submission->status), ['submitted', 'under_review'], true)) {
            throw ValidationException::withMessages(['record' => 'Only the latest submitted, unapproved student Health Form can change category.']);
        }

        return $submission;
    }

    public function change(int $profileId, array $input, User $actor): HealthFormSubmission
    {
        $newFiles = [];
        try {
            return DB::transaction(function () use ($profileId, $input, $actor, &$newFiles) {
                $profile = HealthProfile::query()->lockForUpdate()->with('user')->findOrFail($profileId);
                $submission = $this->pending($profile);
                if ((int) $submission->id !== (int) $input['submission_id']) {
                    throw ValidationException::withMessages(['record' => 'The current submission changed. Reopen the record before continuing.']);
                }
                $category = trim($input['category']);
                if (!in_array($category, $this->options(), true)) {
                    throw ValidationException::withMessages(['category' => 'Select an active student Health Form category.']);
                }
                $previousCategory = trim((string) $submission->category);
                if ($category === $previousCategory) return $submission; // Safe retry; never creates another form/PDF.
                if ($previousCategory !== $input['expected_category']) {
                    throw ValidationException::withMessages(['record' => 'The form category was changed by another action. Reopen the record and try again.']);
                }

                $snapshots = app(HealthProfileSnapshotService::class);
                $staged = clone $profile;
                $staged->setRelation('user', clone $profile->user);
                $snapshots->applySubmissionSnapshot($staged, $submission, $staged->user);
                $staged->health_form_category = $category;
                $previousDeclaration = (string) $staged->health_declaration;
                // Preserve the original submission/signature date, not a new consent/approval date.
                $staged->health_declaration = app(StudentHealthDeclarationService::class)->generate(
                    $staged, $staged->user, $category, $submission->submitted_at, $submission->remarks
                );
                $newFiles[] = $staged->health_declaration;
                $newPdf = 'health_form_submissions/' . $profile->user_id . '/category-change-' . $submission->id . '-' . Str::uuid() . '.pdf';
                $newFiles[] = $newPdf;
                $pdf = Pdf::loadView('student.print_health_form', [
                    'profile' => $staged, 'pdfMode' => true, 'healthFormIdentity' => [], 'healthFormSubmittedAt' => $submission->submitted_at,
                ])->setPaper([0, 0, 612, 936]);
                if (!app(HealthFileStorage::class)->put($newPdf, $pdf->output())) {
                    throw new \RuntimeException('Unable to save the updated Health Form PDF.');
                }
                $previousPdf = $submission->pdf_path;
                // Preserve the exact pending data/review envelope; only these two fields changed.
                $snapshot = is_array($submission->profile_snapshot) ? $submission->profile_snapshot : [];
                $changedFields = ['health_form_category' => $category, 'health_declaration' => $staged->health_declaration];
                if ($submission->snapshotProfile() === []) {
                    $snapshot = $snapshots->capture($staged);
                } elseif (array_key_exists('profile', $snapshot)) {
                    $snapshot['profile'] = array_merge($snapshot['profile'], $changedFields);
                    $snapshot['captured_at'] = now()->toIso8601String();
                } else {
                    $snapshot = array_merge($snapshot, $changedFields);
                }
                $submission->forceFill([
                    'category' => $category, 'pdf_path' => $newPdf,
                    'profile_snapshot' => $snapshot, 'snapshot_captured_at' => now(),
                ])->save();

                $hasApprovedHistory = HealthFormSubmission::query()->where('health_profile_id', $profile->id)
                    ->where('id', '!=', $submission->id)->where(function ($query) {
                        $query->whereNotNull('approved_at')->orWhereIn('status', ['approved', 'Approved']);
                    })->exists();
                if (!$hasApprovedHistory) {
                    $profile->forceFill(['health_form_category' => $category, 'health_declaration' => $staged->health_declaration])->save();
                }
                // Keep a linked renewal's purpose in sync without altering its status or creating a request.
                $corrections = HealthProfileCorrectionRequest::query()->where('health_profile_id', $profile->id)
                    ->where('health_form_submission_id', $submission->id)->where('type', HealthProfileCorrectionRequest::TYPE_NEW_HEALTH_FORM)
                    ->whereIn('status', ['submitted', 'under_review'])->get();
                foreach ($corrections as $correction) {
                    $correction->metadata = array_merge($correction->metadata ?? [], ['category' => $category]);
                    $correction->save();
                }
                ActivityLog::create([
                    'user_id' => $actor->id, 'user_name' => $actor->name ?: $actor->email, 'user_role' => $actor->user_role,
                    'action' => 'Student Health Form Category Changed', 'module' => 'Health Records',
                    'event_type' => 'student_health_form_category_changed', 'description' => 'Corrected the category of a pending student Health Form.',
                    'subject_type' => HealthProfile::class, 'subject_id' => (string) $profile->id,
                    'metadata' => ['health_form_submission_id' => $submission->id, 'previous_category' => $previousCategory,
                        'category' => $category, 'previous_pdf_path' => $previousPdf, 'previous_declaration_path' => $previousDeclaration],
                ]);

                return $submission->fresh();
            });
        } catch (\Throwable $exception) {
            foreach ($newFiles as $path) app(HealthFileStorage::class)->delete($path);
            throw $exception;
        }
    }
}
