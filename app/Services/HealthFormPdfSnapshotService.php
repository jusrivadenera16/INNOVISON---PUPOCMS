<?php

namespace App\Services;

use App\Models\HealthFormSubmission;
use App\Models\EmployeeHealthProfile;
use App\Models\HealthProfile;
use App\Models\User;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\ValidationException;

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

    public function refreshLatestDeclarationAfterApplicantToStudent(User $user): bool
    {
        return $this->refreshLatestDeclarationAfterAccountTypeCorrection(
            $user,
            'applicant',
            'student',
            null,
            $user->student_type,
            'new',
            null
        );
    }

    /**
     * Refresh the latest submitted or approved Health Form after a Developer Tools account-type correction.
     * Uploaded declarations are left alone and prior PDFs remain available as history.
     */
    public function refreshLatestDeclarationAfterAccountTypeCorrection(
        User $user,
        string $previousAccountType,
        string $targetAccountType,
        ?string $previousStudentType,
        ?string $targetStudentType,
        ?string $previousApplicantType,
        ?string $targetApplicantType
    ): bool
    {
        $newFiles = [];

        try {
            return DB::transaction(function () use ($user, $previousAccountType, $targetAccountType, $previousStudentType, $targetStudentType, $previousApplicantType, $targetApplicantType, &$newFiles) {
                $submission = HealthFormSubmission::query()
                    ->where('user_id', $user->id)
                    ->where(function ($query) {
                        $query->whereNotNull('health_profile_id')
                            ->orWhereNotNull('employee_health_profile_id');
                    })
                    ->where(function ($query) {
                        $query->whereIn('status', [
                            HealthFormSubmission::STATUS_SUBMITTED,
                            HealthFormSubmission::STATUS_NEEDS_CORRECTION,
                            'under_review',
                            HealthFormSubmission::STATUS_APPROVED,
                            'Approved',
                        ])->orWhereNotNull('approved_at');
                    })
                    ->latest('id')
                    ->lockForUpdate()
                    ->first();

                if (!$submission) {
                    return false;
                }

                $submissionStatus = strtolower(trim((string) $submission->status));
                $isApproved = $submission->approved_at !== null || $submissionStatus === 'approved';
                $isPending = !$isApproved
                    && !$submission->approved_by_user_id
                    && in_array($submissionStatus, [
                        HealthFormSubmission::STATUS_SUBMITTED,
                        HealthFormSubmission::STATUS_NEEDS_CORRECTION,
                        'under_review',
                    ], true);
                if (!$isApproved && !$isPending) {
                    return false;
                }

                $employeeProfile = null;
                if ($submission->employee_health_profile_id) {
                    $employeeProfile = EmployeeHealthProfile::withTrashed()
                        ->whereKey($submission->employee_health_profile_id)
                        ->lockForUpdate()
                        ->first();
                    if (!$employeeProfile) {
                        return false;
                    }
                    $employeeProfile->load('user');
                    if (!$employeeProfile->user) {
                        return false;
                    }

                    return $this->refreshEmployeeDeclarationSnapshot(
                        $employeeProfile,
                        $submission,
                        $user,
                        $previousAccountType,
                        $targetAccountType,
                        $previousStudentType,
                        $targetStudentType,
                        $previousApplicantType,
                        $targetApplicantType,
                        $newFiles
                    );
                }

                $profile = HealthProfile::query()
                    ->whereKey($submission->health_profile_id)
                    ->lockForUpdate()
                    ->first();
                if (!$profile) {
                    return false;
                }
                $profile->load('user');
                if (!$profile->user) {
                    return false;
                }

                $snapshotProfile = $profile->fresh('user') ?: $profile;
                $snapshotUser = $snapshotProfile->user;
                if (!$snapshotUser) {
                    return false;
                }
                $snapshotProfile->setRelation('user', clone $snapshotUser);
                $snapshotUser = $snapshotProfile->user;

                if ($submission->snapshotProfile() !== []) {
                    $this->profileSnapshots->applySubmissionSnapshot($snapshotProfile, $submission, $snapshotUser);
                }

                $declarationPath = $this->healthFiles->normalizePath((string) $snapshotProfile->health_declaration);
                $generatedDeclarationPattern = '#^health_profiles/health_declarations/declaration-'
                    . preg_quote((string) $snapshotUser->id, '#') . '-[A-Za-z0-9-]+\\.pdf$#i';
                if ($declarationPath === '' || !preg_match($generatedDeclarationPattern, $declarationPath)) {
                    return false;
                }

                if (app(StoredImageDataUri::class)->fromStorage($snapshotProfile->digital_signature) === '') {
                    throw ValidationException::withMessages([
                        'clinic_account_type' => 'The saved signature could not be loaded, so the Health Declaration and Health Form PDF were not regenerated. Please re-upload the signature and try again.',
                    ]);
                }

                $category = trim((string) ($submission->category ?: $snapshotProfile->health_form_category)) ?: 'General';
                $submittedAt = $submission->submitted_at
                    ?: $submission->approved_at
                    ?: $profile->resubmitted_at
                    ?: $profile->created_at
                    ?: now();
                $newDeclarationPath = app(StudentHealthDeclarationService::class)->generateForCorrectedAccountType(
                    $snapshotProfile,
                    $snapshotUser,
                    $targetAccountType,
                    $targetStudentType,
                    $targetApplicantType,
                    $submittedAt,
                    $submission->remarks
                );
                $newFiles[] = $newDeclarationPath;
                $snapshotProfile->health_declaration = $newDeclarationPath;

                $newPdfPath = $this->buildSnapshotPath($profile, $snapshotUser, $category, now());
                $newFiles[] = $newPdfPath;
                $pdf = Pdf::loadView('student.print_health_form', [
                    'profile' => $snapshotProfile,
                    'pdfMode' => true,
                    'healthFormIdentity' => [],
                    'healthFormSubmittedAt' => $submittedAt,
                ]);
                $pdf->setPaper([0, 0, 612, 936]);
                if (!$this->healthFiles->put($newPdfPath, $pdf->output())) {
                    throw new \RuntimeException('Unable to save the updated pending Health Form PDF.');
                }

                $snapshot = is_array($submission->profile_snapshot) ? $submission->profile_snapshot : [];
                if ($submission->snapshotProfile() === []) {
                    $snapshot = $this->profileSnapshots->capture($snapshotProfile);
                }
                if (array_key_exists('profile', $snapshot) && is_array($snapshot['profile'])) {
                    $snapshot['profile']['health_declaration'] = $newDeclarationPath;
                    $snapshot['captured_at'] = now()->toIso8601String();
                } else {
                    $snapshot['health_declaration'] = $newDeclarationPath;
                }
                if (isset($snapshot['user']) && is_array($snapshot['user'])) {
                    $snapshot['user']['user_type'] = $snapshotUser->user_type;
                    $snapshot['user']['student_type'] = $snapshotUser->student_type;
                    $snapshot['user']['applicant_type'] = $snapshotUser->applicant_type;
                }
                $correctionHistory = $snapshot['account_type_correction_history'] ?? [];
                if (!is_array($correctionHistory)) {
                    $correctionHistory = [];
                }
                $correctionHistory[] = [
                    'from' => $previousAccountType,
                    'to' => $targetAccountType,
                    'previous_student_type' => $previousStudentType,
                    'student_type' => $targetStudentType,
                    'previous_applicant_type' => $previousApplicantType,
                    'applicant_type' => $targetApplicantType,
                    'record_status' => $isApproved ? 'approved' : $submissionStatus,
                    'corrected_at' => now()->toIso8601String(),
                    'previous_health_declaration_path' => $declarationPath,
                    'previous_health_form_pdf_path' => $submission->pdf_path,
                ];
                $snapshot['account_type_correction_history'] = $correctionHistory;
                $submission->forceFill([
                    'pdf_path' => $newPdfPath,
                    'profile_snapshot' => $submission->preserveReviewTiming($snapshot),
                    'snapshot_captured_at' => now(),
                ])->save();

                $hasApprovedHistory = HealthFormSubmission::query()
                    ->where('health_profile_id', $profile->id)
                    ->where('id', '!=', $submission->id)
                    ->where(function ($query) {
                        $query->whereNotNull('approved_at')->orWhereIn('status', [HealthFormSubmission::STATUS_APPROVED, 'Approved']);
                    })
                    ->exists();
                if ($isApproved || !$hasApprovedHistory) {
                    $profile->forceFill(['health_declaration' => $newDeclarationPath])->save();
                }

                return true;
            });
        } catch (\Throwable $exception) {
            foreach ($newFiles as $path) {
                $this->healthFiles->delete($path);
            }

            throw $exception;
        }
    }

    private function refreshEmployeeDeclarationSnapshot(
        EmployeeHealthProfile $profile,
        HealthFormSubmission $submission,
        User $user,
        string $previousAccountType,
        string $targetAccountType,
        ?string $previousStudentType,
        ?string $targetStudentType,
        ?string $previousApplicantType,
        ?string $targetApplicantType,
        array &$newFiles
    ): bool {
        $snapshotProfile = clone $profile;
        $snapshotUser = clone $user;
        if ($submission->snapshotProfile() !== []) {
            $snapshotProfile->forceFill($submission->snapshotProfile());
            $snapshotUser->setRawAttributes(array_merge($snapshotUser->getAttributes(), $submission->snapshotUser()), true);
        }
        $snapshotUser->forceFill([
            'user_type' => $user->user_type,
            'student_type' => $user->student_type,
            'applicant_type' => $user->applicant_type,
        ]);
        $snapshotProfile->setRelation('user', $snapshotUser);

        $declarationPath = $this->healthFiles->normalizePath((string) $snapshotProfile->health_declaration);
        if ($declarationPath === ''
            || !preg_match('#^health_profile_employees/health_declarations/declaration-[A-Za-z0-9_-]+-[0-9]{8}-[0-9]{6}(?:-[A-Za-z0-9-]+)?\.pdf$#i', $declarationPath)) {
            return false;
        }

        $signatureValue = trim((string) ($snapshotProfile->uploaded_signature_path ?: $snapshotProfile->staff_signature));
        $signatureSource = str_starts_with($signatureValue, 'data:image/')
            ? $signatureValue
            : app(StoredImageDataUri::class)->fromStorage($signatureValue);
        if ($signatureSource === '') {
            throw ValidationException::withMessages([
                'clinic_account_type' => 'The saved signature could not be loaded, so the Health Declaration and Health Form PDF were not regenerated. Please re-upload the signature and try again.',
            ]);
        }

        $submittedAt = $submission->submitted_at
            ?: $submission->approved_at
            ?: $profile->certified_at
            ?: $profile->created_at
            ?: now();
        $purpose = app(StudentHealthDeclarationService::class)->purposeForAccountType(
            $targetAccountType,
            $targetStudentType,
            $targetApplicantType
        );
        $category = match ($targetAccountType) {
            'student' => 'Student',
            'applicant' => 'Applicant',
            'faculty' => 'Faculty',
            'non_teaching_staff' => 'Employee',
            'dependent' => 'Dependent',
            default => trim((string) $snapshotProfile->health_form_category) ?: 'Employee',
        };
        $fullName = trim((string) $snapshotProfile->name);
        if ($fullName === '') {
            $fullName = trim(implode(' ', array_filter([
                $snapshotProfile->first_name ?: $snapshotUser->first_name,
                $snapshotProfile->middle_name ?: $snapshotUser->middle_name,
                $snapshotProfile->last_name ?: $snapshotUser->last_name,
                $snapshotProfile->suffix_name ?: $snapshotUser->suffix_name,
            ]))) ?: $snapshotUser->name;
        }
        $declarationPdf = Pdf::loadView('student.print_declaration_form', [
            'user' => $snapshotUser,
            'studentFullName' => $fullName,
            'categorySelected' => $category,
            'purposeUnderline1' => $purpose['purpose'],
            'purposeUnderline2' => $purpose['endorsement'],
            'studentSignatureSrc' => $signatureSource,
            'signatureDate' => $submittedAt->format('m/d/Y'),
            'remarks' => '',
            'isMinor' => false,
            'guardianName' => '',
            'guardianSignatureSrc' => null,
            'hideGuardianBlock' => true,
            'signatureAlt' => $targetAccountType === 'dependent'
                ? 'Dependent Signature'
                : (in_array($targetAccountType, ['student', 'applicant'], true) ? 'Student Signature' : 'Employee Signature'),
            'signatureCaption' => $targetAccountType === 'dependent'
                ? "Dependent's Signature Over Printed Name/ Date"
                : ($targetAccountType === 'applicant'
                    ? "Applicant's Signature Over Printed Name/ Date"
                    : ($targetAccountType === 'student'
                        ? "Student's Signature Over Printed Name/ Date"
                        : "Employee's Signature Over Printed Name/ Date")),
            'fallbackSignerName' => match ($targetAccountType) {
                'dependent' => 'DEPENDENT NAME',
                'applicant' => 'APPLICANT NAME',
                'student' => 'STUDENT NAME',
                default => 'EMPLOYEE NAME',
            },
            'postRemarksNote' => 'Both the record owner and clinic staff will affix their signature if required by the Medical Clinic.',
        ])->setPaper('letter', 'portrait');

        $identifier = preg_replace('/[^A-Za-z0-9_-]+/', '-', (string) (
            $snapshotProfile->employee_number ?: $snapshotUser->employee_number ?: $snapshotProfile->id
        )) ?: 'employee-' . $snapshotProfile->id;
        $newDeclarationPath = 'health_profile_employees/health_declarations/declaration-'
            . $identifier . '-' . now()->format('Ymd-His') . '-' . Str::uuid() . '.pdf';
        if (!$this->healthFiles->put($newDeclarationPath, $declarationPdf->output())) {
            throw new \RuntimeException('Unable to save the corrected Health Declaration.');
        }
        $newFiles[] = $newDeclarationPath;
        $snapshotProfile->health_declaration = $newDeclarationPath;

        $identifierForPdf = preg_replace('/[^A-Za-z0-9_-]+/', '-', (string) (
            $snapshotProfile->employee_number ?: $snapshotUser->employee_number ?: $snapshotProfile->id
        )) ?: 'employee-' . $snapshotProfile->id;
        $newPdfPath = 'health_profile_employees/health_forms/health-form-'
            . $identifierForPdf . '-' . now()->format('Ymd-His') . '-' . Str::uuid() . '.pdf';
        $snapshotProfile->staff_health_form_pdf_path = $newPdfPath;
        $pdf = Pdf::loadView('student.print_employee_health_form', [
            'user' => $snapshotUser,
            'employeeProfile' => $snapshotProfile,
            'adminViewer' => true,
            'pdfMode' => true,
        ])->setPaper([0, 0, 612, 936]);
        if (!$this->healthFiles->put($newPdfPath, $pdf->output())) {
            throw new \RuntimeException('Unable to save the corrected Employee Health Form PDF.');
        }
        $newFiles[] = $newPdfPath;

        $snapshot = is_array($submission->profile_snapshot) ? $submission->profile_snapshot : [];
        if ($submission->snapshotProfile() === []) {
            $snapshot = app(EmployeeHealthFormHistoryService::class)->capture($snapshotProfile);
        }
        if (isset($snapshot['profile']) && is_array($snapshot['profile'])) {
            $snapshot['profile']['health_declaration'] = $newDeclarationPath;
            $snapshot['profile']['staff_health_form_pdf_path'] = $newPdfPath;
            $snapshot['captured_at'] = now()->toIso8601String();
        } else {
            $snapshot['health_declaration'] = $newDeclarationPath;
            $snapshot['staff_health_form_pdf_path'] = $newPdfPath;
        }
        if (isset($snapshot['user']) && is_array($snapshot['user'])) {
            $snapshot['user']['user_type'] = $snapshotUser->user_type;
            $snapshot['user']['student_type'] = $snapshotUser->student_type;
            $snapshot['user']['applicant_type'] = $snapshotUser->applicant_type;
        }
        $correctionHistory = $snapshot['account_type_correction_history'] ?? [];
        if (!is_array($correctionHistory)) {
            $correctionHistory = [];
        }
        $correctionHistory[] = [
            'from' => $previousAccountType,
            'to' => $targetAccountType,
            'previous_student_type' => $previousStudentType,
            'student_type' => $targetStudentType,
            'previous_applicant_type' => $previousApplicantType,
            'applicant_type' => $targetApplicantType,
            'record_status' => $submission->approved_at || strtolower((string) $submission->status) === 'approved'
                ? 'approved'
                : strtolower((string) $submission->status),
            'corrected_at' => now()->toIso8601String(),
            'previous_health_declaration_path' => $declarationPath,
            'previous_health_form_pdf_path' => $submission->pdf_path,
        ];
        $snapshot['account_type_correction_history'] = $correctionHistory;
        $submission->forceFill([
            'pdf_path' => $newPdfPath,
            'profile_snapshot' => $submission->preserveReviewTiming($snapshot),
            'snapshot_captured_at' => now(),
        ])->save();

        $hasApprovedHistory = HealthFormSubmission::query()
            ->where('employee_health_profile_id', $profile->id)
            ->where('id', '!=', $submission->id)
            ->where(function ($query) {
                $query->whereNotNull('approved_at')->orWhereIn('status', [HealthFormSubmission::STATUS_APPROVED, 'Approved']);
            })
            ->exists();
        if ($submission->approved_at || strtolower((string) $submission->status) === 'approved' || !$hasApprovedHistory) {
            $profile->forceFill(['health_declaration' => $newDeclarationPath])->save();
        }
        $profile->forceFill(['staff_health_form_pdf_path' => $newPdfPath])->save();

        return true;
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
