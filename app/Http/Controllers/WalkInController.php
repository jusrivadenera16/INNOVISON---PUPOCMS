<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use App\Models\Appointment;
use App\Models\HealthProfile;
use App\Models\InventoryMovement;
use App\Models\Item;
use App\Models\ActivityLog;
use App\Models\Consultation;
use App\Services\PuptasWebhookService;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class WalkInController extends Controller
{
    private function normalizeConsultationSource(?string $source): string
    {
        $source = strtolower(trim((string) $source));

        return in_array($source, ['online', 'walkin', 'assisted'], true)
            ? $source
            : 'walkin';
    }

    private function consultationStartSessionKey($staffId, $studentId, string $source): string
    {
        $identity = implode('|', [
            (string) ($staffId ?: 'guest'),
            (string) $studentId,
            $this->normalizeConsultationSource($source),
        ]);

        return 'consultation_started_at.' . hash('sha256', $identity);
    }

    private function normalizeLookupName(?string $value): string
    {
        $value = Str::upper((string) $value);
        $value = preg_replace('/[^A-Z\s]/', ' ', $value) ?? '';
        $value = preg_replace('/\s+/', ' ', $value) ?? '';

        return trim($value);
    }

    private function logReferenceLookup(
        Request $request,
        string $referenceNumber,
        bool $found = false,
        ?string $applicantName = null,
        ?string $errorMessage = null,
        ?array $metadata = null
    ): void
    {
        $user = auth()->user();
        $lookupStatus = $found ? 'found' : ($errorMessage ? 'error' : 'not_found');
        $description = match ($lookupStatus) {
            'found' => "Reference lookup successful for: {$referenceNumber}" . ($applicantName ? " ({$applicantName})" : ''),
            'error' => "Reference lookup error for {$referenceNumber}: {$errorMessage}",
            'not_found' => "Reference lookup failed - no applicant found for: {$referenceNumber}",
        };

        $activityMetadata = $metadata ?? [];
        $activityMetadata['reference_number'] = $referenceNumber;
        $activityMetadata['lookup_status'] = $lookupStatus;
        if ($applicantName) {
            $activityMetadata['applicant_name'] = $applicantName;
        }
        if ($errorMessage) {
            $activityMetadata['error'] = $errorMessage;
        }

        ActivityLog::create([
            'user_id' => $user?->id,
            'user_name' => $user?->name ?? $user?->email ?? 'System',
            'user_role' => $user ? strtolower((string) ($user->user_role ?? '')) : null,
            'action' => 'Reference Lookup',
            'module' => 'Patient Intake',
            'event_type' => 'reference_lookup',
            'description' => $description,
            'route_name' => optional($request->route())->getName(),
            'http_method' => strtoupper((string) $request->method()),
            'request_path' => '/' . ltrim((string) $request->path(), '/'),
            'status_code' => $errorMessage ? 422 : 200,
            'subject_type' => 'applicant',
            'subject_id' => $referenceNumber,
            'metadata' => $activityMetadata,
            'ip_address' => $request->ip(),
            'user_agent' => substr((string) $request->userAgent(), 0, 255),
        ]);
    }

    private function namesRoughlyMatch(?string $extractedName, User $student): bool
    {
        $needle = $this->normalizeLookupName($extractedName);
        if ($needle === '') {
            return true;
        }

        $candidates = array_filter([
            $student->name ?? '',
            trim(implode(' ', array_filter([
                $student->first_name ?? '',
                $student->middle_name ?? '',
                $student->last_name ?? '',
            ]))),
            trim(implode(' ', array_filter([
                $student->first_name ?? '',
                $student->last_name ?? '',
            ]))),
        ]);

        foreach ($candidates as $candidate) {
            $normalizedCandidate = $this->normalizeLookupName($candidate);
            if ($normalizedCandidate === '') {
                continue;
            }

            if ($normalizedCandidate === $needle) {
                return true;
            }

            if (Str::contains($normalizedCandidate, $needle) || Str::contains($needle, $normalizedCandidate)) {
                return true;
            }

            $needleParts = array_values(array_filter(explode(' ', $needle)));
            $candidateParts = array_values(array_filter(explode(' ', $normalizedCandidate)));

            if (count($needleParts) >= 2 && count($candidateParts) >= 2) {
                $firstMatches = ($needleParts[0] ?? null) === ($candidateParts[0] ?? null);
                $lastMatches = ($needleParts[count($needleParts) - 1] ?? null) === ($candidateParts[count($candidateParts) - 1] ?? null);

                if ($firstMatches && $lastMatches) {
                    return true;
                }
            }
        }

        return false;
    }

    private function findUserByIdentifier(string $identifier): ?User
    {
        $identifier = trim($identifier);
        if ($identifier === '') {
            return null;
        }

        return User::with('healthProfile')
            ->where(function ($query) use ($identifier) {
                if (\Schema::hasColumn('users', 'student_number')) {
                    $query->orWhere('student_number', $identifier);
                }

                if (\Schema::hasColumn('users', 'reference_number')) {
                    $query->orWhere('reference_number', $identifier);
                }

                $query->orWhere('barcode', $identifier)
                    ->orWhere('student_id', $identifier);
            })
            ->first();
    }

    private function findHealthProfileByReference(string $referenceNumber): ?HealthProfile
    {
        $referenceNumber = trim($referenceNumber);
        if ($referenceNumber === '' || !\Schema::hasTable('health_profiles')) {
            return null;
        }

        return HealthProfile::query()
            ->with('user')
            ->where('reference_number', $referenceNumber)
            ->orWhere('student_number', $referenceNumber)
            ->orWhere('student_id', $referenceNumber)
            ->latest()
            ->first();
    }

    private function ensureLocalUserFromHealthProfile(HealthProfile $profile, string $referenceNumber): ?User
    {
        $profile->loadMissing('user');
        $user = $profile->user;

        if (!$user && trim((string) $profile->user_id) !== '') {
            $user = User::find($profile->user_id);
        }

        if (!$user) {
            return null;
        }

        $needsSave = false;
        if (\Schema::hasColumn('users', 'reference_number') && trim((string) ($user->reference_number ?? '')) === '') {
            $user->reference_number = $referenceNumber;
            $needsSave = true;
        }
        if (\Schema::hasColumn('users', 'student_number') && trim((string) ($user->student_number ?? '')) === '') {
            $user->student_number = trim((string) ($profile->student_number ?: $referenceNumber));
            $needsSave = true;
        }
        if (trim((string) ($user->student_id ?? '')) === '' && trim((string) ($profile->student_id ?? '')) !== '') {
            $user->student_id = (string) $profile->student_id;
            $needsSave = true;
        }

        if ($needsSave) {
            $user->save();
        }

        if (trim((string) ($profile->reference_number ?? '')) === '') {
            $profile->reference_number = $referenceNumber;
            $profile->save();
        }

        return $user->fresh('healthProfile');
    }

    private function looksLikeUuid(?string $value): bool
    {
        return (bool) preg_match(
            '/^[0-9a-f]{8}-[0-9a-f]{4}-[1-5][0-9a-f]{3}-[89ab][0-9a-f]{3}-[0-9a-f]{12}$/i',
            trim((string) $value)
        );
    }

    private function findUniqueUserByName(string $name): ?User
    {
        $name = $this->normalizeLookupName($name);
        if ($name === '') {
            return null;
        }

        $parts = array_values(array_filter(explode(' ', $name)));
        $query = User::with('healthProfile');

        foreach (array_slice($parts, 0, 3) as $part) {
            $query->where(function ($inner) use ($part) {
                $inner->orWhere('name', 'like', '%' . $part . '%')
                    ->orWhere('first_name', 'like', '%' . $part . '%')
                    ->orWhere('last_name', 'like', '%' . $part . '%');
            });
        }

        $matches = $query->limit(12)->get()->filter(function (User $student) use ($name) {
            return $this->namesRoughlyMatch($name, $student);
        })->values();

        return $matches->count() === 1 ? $matches->first() : null;
    }

    private function resolveUniqueStudentId(string $seed): string
    {
        $candidate = trim($seed) !== '' ? trim($seed) : ('idp-' . Str::lower(Str::random(12)));
        $base = $candidate;
        $counter = 1;

        while (User::where('student_id', $candidate)->exists()) {
            $candidate = $base . '-' . $counter;
            $counter++;
        }

        return $candidate;
    }

    private function resolveLocalUserFromApplicant(array $applicant, bool $persist = true, ?string $referenceNumber = null): User
    {
        \Log::debug('PUPTAS applicant data', ['applicant' => $applicant, 'referenceNumber' => $referenceNumber]);

        $studentNumber = trim((string) (
            data_get($applicant, 'user.student_number')
            ?: data_get($applicant, 'student_number')
        ));
        $resolvedReferenceNumber = trim((string) (
            data_get($applicant, 'user.reference_number')
            ?: data_get($applicant, 'reference_number')
            ?: data_get($applicant, 'application.reference_number')
            ?: $referenceNumber
        ));
        $idpUserId = trim((string) (
            data_get($applicant, 'user.id')
            ?: data_get($applicant, 'idp_user_id')
        ));
        $email = trim((string) (
            data_get($applicant, 'user.email')
            ?: data_get($applicant, 'email')
        ));

        \Log::debug('Extracted fields', [
            'studentNumber' => $studentNumber,
            'referenceNumber' => $resolvedReferenceNumber,
            'idpUserId' => $idpUserId,
            'email' => $email,
        ]);

        $user = User::query()
            ->when($idpUserId !== '', fn ($query) => $query->orWhere('student_id', $idpUserId))
            ->when($studentNumber !== '' && \Schema::hasColumn('users', 'student_number'), fn ($query) => $query->orWhere('student_number', $studentNumber))
            ->when($resolvedReferenceNumber !== '' && \Schema::hasColumn('users', 'reference_number'), fn ($query) => $query->orWhere('reference_number', $resolvedReferenceNumber))
            ->when($email !== '', fn ($query) => $query->orWhere('email', $email))
            ->first();

        // Try multiple field name variations for first and last name
        $firstName = trim((string) data_get($applicant, 'user.firstname'));
        if ($firstName === '') {
            $firstName = trim((string) data_get($applicant, 'user.first_name'));
        }
        if ($firstName === '') {
            $firstName = trim((string) data_get($applicant, 'first_name'));
        }
        if ($firstName === '') {
            $firstName = trim((string) data_get($applicant, 'firstname'));
        }
        if ($firstName === '') {
            $firstName = trim((string) data_get($applicant, 'given_name'));
        }

        $lastName = trim((string) data_get($applicant, 'user.lastname'));
        if ($lastName === '') {
            $lastName = trim((string) data_get($applicant, 'user.last_name'));
        }
        if ($lastName === '') {
            $lastName = trim((string) data_get($applicant, 'last_name'));
        }
        if ($lastName === '') {
            $lastName = trim((string) data_get($applicant, 'lastname'));
        }
        if ($lastName === '') {
            $lastName = trim((string) data_get($applicant, 'family_name'));
        }
        if ($lastName === '') {
            $lastName = trim((string) data_get($applicant, 'surname'));
        }

        $middleName = trim((string) (
            data_get($applicant, 'user.middlename')
            ?: data_get($applicant, 'user.middle_name')
            ?: data_get($applicant, 'middle_name')
            ?: data_get($applicant, 'middlename')
        ));

        // Prefer the complete structured name. Some PUPTAS responses expose a
        // shortened full_name while still providing all individual name fields.
        $structuredFullName = trim(implode(' ', array_filter([
            $firstName,
            $middleName,
            $lastName,
        ])));
        $fullName = $structuredFullName !== ''
            ? $structuredFullName
            : trim((string) (data_get($applicant, 'full_name') ?: data_get($applicant, 'name')));

        \Log::debug('Name extraction results', [
            'firstName' => $firstName,
            'middleName' => $middleName,
            'lastName' => $lastName,
            'fullName' => $fullName,
            'applicantAllKeys' => array_keys($applicant),
        ]);

        $fallbackFirstName = $firstName !== '' ? $firstName : 'Applicant';
        $fallbackLastName = $lastName !== '' ? $lastName : 'User';
        $fallbackFullName = $fullName !== '' ? $fullName : trim($fallbackFirstName . ' ' . $fallbackLastName);

        if (!$user) {
            $user = new User();
            $user->student_id = $this->resolveUniqueStudentId($idpUserId !== '' ? $idpUserId : $studentNumber);
            $user->password = Hash::make(Str::random(40));
            $user->user_role = User::ROLE_STUDENT;
            $user->status = \Schema::hasColumn('users', 'status') ? 'active' : null;
        }

        if ($studentNumber !== '' && \Schema::hasColumn('users', 'student_number')) {
            $user->student_number = $studentNumber;
        }

        if ($resolvedReferenceNumber !== '' && \Schema::hasColumn('users', 'reference_number')) {
            $user->reference_number = $resolvedReferenceNumber;
        }

        if ($idpUserId !== '' && trim((string) $user->student_id) === '') {
            $user->student_id = $this->resolveUniqueStudentId($idpUserId);
        }

        \Log::debug('User fields set', [
            'student_id' => $user->student_id,
            'student_number' => $user->student_number,
            'reference_number' => $user->reference_number,
        ]);

        if ($firstName !== '') {
            $user->first_name = $firstName;
        } elseif (trim((string) $user->first_name) === '') {
            $user->first_name = $fallbackFirstName;
        }

        if (\Schema::hasColumn('users', 'middle_name') && ($firstName !== '' || $lastName !== '' || $fullName !== '')) {
            $user->middle_name = $middleName !== '' ? $middleName : null;
        }

        if ($lastName !== '') {
            $user->last_name = $lastName;
        } elseif (trim((string) $user->last_name) === '') {
            $user->last_name = $fallbackLastName;
        }

        if ($fullName !== '') {
            $user->name = $fullName;
        } elseif (trim((string) $user->name) === '') {
            $user->name = $fallbackFullName;
        }

        if ($email !== '') {
            $user->email = $email;
        } elseif (!$user->exists || trim((string) $user->email) === '') {
            $seed = $studentNumber !== '' ? $studentNumber : ($idpUserId !== '' ? $idpUserId : Str::lower(Str::random(8)));
            $user->email = Str::slug($seed, '.') . '@idp.local';
        }

        if ($persist) {
            $user->save();

            // Link any pending medical assessments for this applicant
            $this->linkPendingMedicalAssessments($user, $email);
        }

        return $user;
    }

    private function puptasApplicantFullName(array $applicant): string
    {
        $firstName = trim((string) (
            data_get($applicant, 'user.firstname')
            ?: data_get($applicant, 'user.first_name')
            ?: data_get($applicant, 'firstname')
            ?: data_get($applicant, 'first_name')
            ?: data_get($applicant, 'given_name')
        ));
        $middleName = trim((string) (
            data_get($applicant, 'user.middlename')
            ?: data_get($applicant, 'user.middle_name')
            ?: data_get($applicant, 'middlename')
            ?: data_get($applicant, 'middle_name')
        ));
        $lastName = trim((string) (
            data_get($applicant, 'user.lastname')
            ?: data_get($applicant, 'user.last_name')
            ?: data_get($applicant, 'lastname')
            ?: data_get($applicant, 'last_name')
            ?: data_get($applicant, 'family_name')
            ?: data_get($applicant, 'surname')
        ));
        $structuredName = trim(implode(' ', array_filter([$firstName, $middleName, $lastName])));

        return $structuredName !== ''
            ? $structuredName
            : trim((string) (
                data_get($applicant, 'user.full_name')
                ?: data_get($applicant, 'full_name')
                ?: data_get($applicant, 'name')
            ));
    }

    private function resolveAssistedEmail(string $referenceId): string
    {
        $base = Str::slug($referenceId, '.');
        $base = $base !== '' ? $base : ('assisted.' . Str::lower(Str::random(8)));

        $email = $base . '@assisted.local';
        $counter = 1;

        while (User::where('email', $email)->exists()) {
            $email = $base . '.' . $counter . '@assisted.local';
            $counter++;
        }

        return $email;
    }

    private function inAssistantWorkspace(Request $request): bool
    {
        return $request->is('assistant/*');
    }

    private function walkinRouteName(Request $request, string $suffix): string
    {
        if ($this->inAssistantWorkspace($request)) {
            return 'assistant.walkin.' . $suffix;
        }

        return 'walkin.' . $suffix;
    }

    private function adminBasePrefix(Request $request): string
    {
        return $this->inAssistantWorkspace($request) ? '/assistant' : '/admin';
    }

    private function healthProfileDocuments(Request $request, ?HealthProfile $profile): array
    {
        if (!$profile) {
            return [];
        }

        $documents = collect([
            ['key' => 'medical_certificate', 'label' => 'Medical Certificate', 'path' => $profile->medical_certificate],
            ['key' => 'chest_xray_result', 'label' => 'Chest X-Ray Result', 'path' => $profile->chest_xray_result],
            ['key' => 'student_photo', 'label' => '2x2 Photo', 'path' => $profile->student_photo],
            ['key' => 'pwd_id_proof', 'label' => 'PWD ID Proof', 'path' => $profile->pwd_id_proof],
            ['key' => 'medical_assessment_upload', 'label' => 'Medical Assessment Copy', 'path' => $profile->medical_assessment_upload],
        ])->filter(fn (array $document) => filled($document['path']))
            ->map(function (array $document) use ($request, $profile) {
                $extension = strtolower(pathinfo((string) $document['path'], PATHINFO_EXTENSION));

                return [
                    'key' => $document['key'],
                    'label' => $document['label'],
                    'url' => route($this->walkinRouteName($request, 'document'), [
                        'healthProfile' => $profile->id,
                        'document' => $document['key'],
                    ]),
                    'type' => in_array($extension, ['jpg', 'jpeg', 'png', 'webp'], true) ? 'image' : 'file',
                ];
            })
            ->values()
            ->all();

        $documents[] = [
            'key' => 'health_form',
            'label' => 'Health Information Form',
            'url' => route($this->walkinRouteName($request, 'healthForm'), ['healthProfile' => $profile->id]),
            'type' => 'form',
        ];

        return $documents;
    }

    private function healthProfileAssessmentReview(?HealthProfile $profile): array
    {
        if (!$profile) {
            return [];
        }

        $pendingReason = trim((string) $profile->pending_reason);
        $medicalRemarks = trim((string) $profile->medical_condition_remarks);
        $assessmentRemarks = trim((string) $profile->assessment_remarks);
        $clearanceStatus = trim((string) $profile->clearance_status);
        $hasSavedReview = trim((string) $profile->blood_pressure) !== ''
            || $profile->respiratory_rate !== null
            || $profile->temperature !== null
            || $pendingReason !== ''
            || in_array($clearanceStatus, ['Pending/Conditional', 'Fully Cleared', 'Issued'], true)
            || $profile->verified_at !== null;

        if (!$hasSavedReview) {
            return [];
        }

        $findingsStatus = trim((string) $profile->med_cert_findings);
        if (!in_array($findingsStatus, ['No Findings / Normal', 'With Findings'], true)) {
            $findingsStatus = $clearanceStatus === 'Pending/Conditional'
                ? 'With Findings'
                : 'No Findings / Normal';
        }

        [$pendingReasonLine, $pendingRemarks] = array_pad(
            preg_split('/\R/', $pendingReason, 2) ?: [],
            2,
            ''
        );
        [$medicalCondition, $medicalConditionRemarks] = array_pad(
            preg_split('/\R/', $medicalRemarks, 2) ?: [],
            2,
            ''
        );

        $hasMedicalCondition = $medicalRemarks !== ''
            || stripos($pendingReasonLine, 'Medical Condition:') !== false
            || stripos($pendingReasonLine, 'With Medical Condition') !== false;
        if ($medicalCondition === '' && preg_match('/Medical Condition:\s*([^;]+)/i', $pendingReasonLine, $matches)) {
            $medicalCondition = trim((string) $matches[1]);
        }

        $hasIncompleteRequirements = stripos($pendingReasonLine, 'Incomplete Requirements') !== false;
        $needsPhysicianEvaluation = stripos($pendingReasonLine, 'For Physician Evaluation') !== false;
        $otherPendingReason = '';
        if (preg_match('/(?:^|;\s*)Others:\s*(.+)$/i', $pendingReasonLine, $matches)) {
            $otherPendingReason = trim((string) $matches[1]);
        }

        if (
            $findingsStatus === 'With Findings'
            && !$hasMedicalCondition
            && !$hasIncompleteRequirements
            && !$needsPhysicianEvaluation
            && $otherPendingReason === ''
            && $pendingReasonLine !== ''
        ) {
            $otherPendingReason = $pendingReasonLine;
        }

        return [
            'findings_status' => $findingsStatus,
            'has_medical_condition' => $hasMedicalCondition,
            'medical_condition' => $medicalCondition,
            'incomplete_requirements' => $hasIncompleteRequirements,
            'needs_physician_evaluation' => $needsPhysicianEvaluation,
            'other_pending_reason' => $otherPendingReason,
            'condition_remarks' => $findingsStatus === 'With Findings'
                ? trim($pendingRemarks !== '' ? $pendingRemarks : $medicalConditionRemarks)
                : '',
            'normal_remarks' => $findingsStatus === 'No Findings / Normal' ? $assessmentRemarks : '',
            'blood_pressure' => trim((string) $profile->blood_pressure),
            'pulse_rate' => $profile->pulse_rate,
            'respiratory_rate' => $profile->respiratory_rate,
            'temperature' => $profile->temperature,
            'covid_positive' => trim((string) $profile->covid_positive),
            'covid_positive_date' => optional($profile->covid_positive_date)->format('Y-m-d'),
        ];
    }

    private function formatMedicalAssessmentDate($value): string
    {
        if (blank($value)) {
            return '';
        }

        try {
            return \Carbon\Carbon::parse($value)->format('M d, Y');
        } catch (\Throwable $e) {
            return trim((string) $value);
        }
    }

    private function normalizeMedicalAssessmentXrayResult(?string $value): string
    {
        $value = trim((string) $value);

        return match ($value) {
            'With Findings' => 'With Findings',
            'Normal', 'No Findings / Normal' => 'Normal / Without Findings',
            default => $value !== '' ? $value : 'Normal / Without Findings',
        };
    }

    private function generateMedicalAssessmentCopy(HealthProfile $profile): string
    {
        $profile->refresh();

        $data = [
            'assessmentDate' => $this->formatMedicalAssessmentDate($profile->assessment_date ?: now()),
            'birthday' => $this->formatMedicalAssessmentDate($profile->birthday),
            'height' => trim((string) $profile->height),
            'weight' => trim((string) $profile->weight),
            'bloodPressure' => trim((string) $profile->blood_pressure),
            'pulseRate' => trim((string) $profile->pulse_rate),
            'respiratoryRate' => trim((string) $profile->respiratory_rate),
            'temperature' => trim((string) $profile->temperature),
            'covidPositive' => trim((string) $profile->covid_positive),
            'covidPositiveDate' => $profile->covid_positive === 'Yes'
                ? $this->formatMedicalAssessmentDate($profile->covid_positive_date)
                : '',
            'doctorName' => trim((string) ($profile->medical_certificate_issued_by ?: $profile->doctor_name)),
            'medicalCertificateDate' => $this->formatMedicalAssessmentDate($profile->medical_certificate_issued_at ?: $profile->med_cert_date),
            'xrayResult' => $this->normalizeMedicalAssessmentXrayResult($profile->chest_xray_result_text ?: $profile->xray_findings),
            'xrayDate' => $this->formatMedicalAssessmentDate($profile->chest_xray_date ?: $profile->xray_date),
        ];

        $pdf = Pdf::loadView('admin.medical_assessment_copy', $data)->setPaper('letter');
        $path = 'health_profiles/medical_assessments/medical-assessment-' . $profile->id . '-' . now()->format('YmdHis') . '.pdf';

        $previousPath = ltrim((string) $profile->medical_assessment_upload, '/');
        if (Str::startsWith($previousPath, 'health_profiles/medical_assessments/') && Storage::disk('public')->exists($previousPath)) {
            Storage::disk('public')->delete($previousPath);
        }

        Storage::disk('public')->put($path, $pdf->output());

        return $path;
    }

    public function showApplicantDocument(HealthProfile $healthProfile, string $document)
    {
        $allowedDocuments = [
            'medical_certificate',
            'chest_xray_result',
            'student_photo',
            'pwd_id_proof',
            'medical_assessment_upload',
        ];

        abort_unless(in_array($document, $allowedDocuments, true), 404);

        $path = ltrim((string) $healthProfile->{$document}, '/');
        $path = preg_replace('#^(?:public/)?storage/#', '', $path) ?? $path;

        abort_if($path === '' || !Storage::disk('public')->exists($path), 404, 'Uploaded document not found.');

        $disk = Storage::disk('public');
        $mimeType = $disk->mimeType($path) ?: 'application/octet-stream';
        $filename = basename($path);

        return response()->file($disk->path($path), [
            'Content-Type' => $mimeType,
            'Content-Disposition' => 'inline; filename="' . str_replace('"', '', $filename) . '"',
            'X-Content-Type-Options' => 'nosniff',
            'Cache-Control' => 'private, max-age=300',
        ]);
    }

    private function formatQuantityNumber(float $value): string
    {
        $rounded = round($value, 2);
        if (abs($rounded - round($rounded)) < 0.00001) {
            return (string) (int) round($rounded);
        }

        return rtrim(rtrim(number_format($rounded, 2, '.', ''), '0'), '.');
    }

    private function extractOpenAiOutputText(array $payload): string
    {
        $outputText = trim((string) data_get($payload, 'output_text', ''));
        if ($outputText !== '') {
            return $outputText;
        }

        $parts = [];
        foreach ((array) data_get($payload, 'output', []) as $output) {
            foreach ((array) data_get($output, 'content', []) as $content) {
                $text = trim((string) data_get($content, 'text', ''));
                if ($text !== '') {
                    $parts[] = $text;
                }
            }
        }

        return trim(implode("\n", $parts));
    }

    private function decodeAiVerificationText(string $text): ?array
    {
        $text = trim($text);
        if ($text === '') {
            return null;
        }

        $text = preg_replace('/^```json\s*/i', '', $text) ?? $text;
        $text = preg_replace('/^```\s*/', '', $text) ?? $text;
        $text = preg_replace('/\s*```$/', '', $text) ?? $text;

        $decoded = json_decode($text, true);
        if (!is_array($decoded)) {
            return null;
        }

        return [
            'student_number' => trim((string) ($decoded['student_number'] ?? '')),
            'first_name' => trim((string) ($decoded['first_name'] ?? '')),
            'surname' => trim((string) ($decoded['surname'] ?? '')),
            'full_name' => trim((string) ($decoded['full_name'] ?? '')),
            'confidence_note' => trim((string) ($decoded['confidence_note'] ?? '')),
        ];
    }

    // 1. INDEX PAGE
    public function index(Request $request)
{

    $mode = $request->query('mode', '');
    $walkins = Appointment::latest()->take(10)->get();
    
    return view('admin.walkin', compact('walkins', 'mode'));
}

    // 2. SHOW WALKIN FORM
    public function showWalkinForm(Request $request, $student_id)
    {
        $student = $this->findUserByIdentifier((string) $student_id);
        abort_if(!$student, 404);
        $user_source = $this->normalizeConsultationSource($request->query('source', 'walkin'));
        $consultationSessionKey = $this->consultationStartSessionKey(
            auth()->id(),
            $student->id,
            $user_source
        );
        $consultationStartedAt = (string) $request->session()->get($consultationSessionKey, '');

        if (!preg_match('/^\d{2}:\d{2}:\d{2}$/', $consultationStartedAt)) {
            $consultationStartedAt = now()->format('H:i:s');
            $request->session()->put($consultationSessionKey, $consultationStartedAt);
        }

        $latestAppointment = null;

        // Kukuha lang tayo ng data kung ang source link ay 'online'
        if ($user_source === 'online') {
            $latestAppointment = Appointment::query()
                ->where('status', 'Approved')
                ->where(function ($query) use ($student) {
                    $query->where('student_id', $student->student_id);

                    if (!empty($student->student_number)) {
                        $query->orWhere('student_number', $student->student_number);
                    }
                })
                ->latest()
                ->first();

            if ($latestAppointment) {
                $appointmentDate = \Carbon\Carbon::parse($latestAppointment->date)->startOfDay();
                $today = \Carbon\Carbon::today();

                if ($appointmentDate->gt($today)) {
                    return redirect()->back()->with('error', 'Consultation denied. This appointment is scheduled for ' . $appointmentDate->format('F d, Y') . '.');
                }
            }
        }

        $items = \App\Models\Item::where('category', 'Medicine')
                                 ->where('quantity', '>', 0)
                                 ->orderBy('name')
                                 ->get();

        $conditions = \App\Models\MedicalConditions::with('category')->get();
        $studentDocuments = $this->healthProfileDocuments($request, $student->healthProfile);
        $studentTreatments = Consultation::query()
            ->with(['medicalCondition.category', 'medicineItem', 'attendingStaff'])
            ->where('user_id', $student->id)
            ->latest('consultation_date')
            ->latest('time_out')
            ->limit(20)
            ->get();

        $consultationDob = (string) ($student->healthProfile->birthday ?? $student->DOB ?? '');
        if ($consultationDob !== '') {
            try {
                $consultationDob = \Carbon\Carbon::parse($consultationDob)->format('Y-m-d');
            } catch (\Throwable $exception) {
                $consultationDob = '';
            }
        }

        $consultationHeight = $student->healthProfile->height ?? $student->height ?? null;
        $consultationWeight = $student->healthProfile->weight ?? $student->weight ?? null;

        return view('admin.consult-form', compact(
            'student',
            'items',
            'conditions',
            'latestAppointment',
            'user_source',
            'consultationDob',
            'consultationHeight',
            'consultationWeight',
            'studentDocuments',
            'studentTreatments',
            'consultationStartedAt'
        ));
    }

    // 3. GET STUDENT INFO
    public function getStudent(Request $request, PuptasWebhookService $puptasWebhookService)
    {
        $lookup = trim((string) $request->student_id);
        $lookupName = trim((string) $request->student_name);
        $previewOnly = $request->boolean('preview_only');
        $intakeTarget = strtolower(trim((string) $request->query('intake_target', 'consultation')));
        $lookupScope = strtolower(trim((string) $request->query('lookup_scope', 'default')));
        if ($lookup !== '' && $lookupScope !== 'clinic_local' && Str::startsWith(strtoupper($lookup), 'CLN-')) {
            return response()->json([
                'status' => 'not_found',
                'message' => 'This is a Clinic Reference, not an Applicant Reference. Please use the Clinic Reference lookup.',
            ], 422);
        }

        $student = $this->findUserByIdentifier($lookup);
        $lookupMessage = $lookupScope === 'clinic_local'
            ? 'No clinic record matched that reference number in local records.'
            : 'No patient matched that student number in local records or PUPTAS.';
        $lookupStatus = null;

        if ($lookupScope === 'clinic_local' && $lookup !== '') {
            if (!$student) {
                $localProfile = $this->findHealthProfileByReference($lookup);
                if ($localProfile) {
                    $student = $this->ensureLocalUserFromHealthProfile($localProfile, $lookup);
                    $lookupStatus = 'local_clinic_reference';
                    $lookupMessage = 'Clinic reference found in local records.';
                }
            } else {
                $lookupStatus = 'local_clinic_reference';
                $lookupMessage = 'Clinic reference found in local records.';
            }
        } elseif (
            $lookup !== ''
            && (
                !$student
                || ($previewOnly && !$this->looksLikeUuid($lookup))
            )
        ) {
            $lookupResult = $puptasWebhookService->fetchApplicantByReferenceNumberDetailed($lookup);
            $lookupStatus = $lookupResult['status'] ?? null;
            $lookupMessage = trim((string) ($lookupResult['message'] ?? '')) ?: $lookupMessage;
            $applicant = $lookupResult['data'] ?? null;

            if (is_array($applicant)) {
                // Keep a synchronized local identity snapshot after a confirmed
                // PUPTAS response. The medical endpoint may stop listing an
                // applicant after their workflow status changes.
                $student = $this->resolveLocalUserFromApplicant($applicant, true, $lookup);
            } elseif (!$student) {
                $localProfile = $this->findHealthProfileByReference($lookup);
                if ($localProfile) {
                    $student = $this->ensureLocalUserFromHealthProfile($localProfile, $lookup);
                    $lookupStatus = 'local_health_profile';
                    $lookupMessage = 'Local health profile found. PUPTAS sync will still depend on a valid Admission reference.';
                }
            } elseif ($lookupStatus && (int) $lookupStatus !== 200) {
                $localProfile = $this->findHealthProfileByReference($lookup);
                if ($localProfile) {
                    $lookupStatus = 'local_health_profile';
                    $lookupMessage = 'Local health profile found. PUPTAS sync will still depend on a valid Admission reference.';
                }
            }

            // Log the reference lookup attempt
            if (is_array($applicant) && $student) {
                // Successful lookup
                $applicantName = $applicant['full_name'] ?? $applicant['name'] ?? '';
                if (!$applicantName && isset($applicant['first_name'])) {
                    $applicantName = $applicant['first_name'];
                    if (isset($applicant['last_name'])) {
                        $applicantName .= ' ' . $applicant['last_name'];
                    }
                }
                $this->logReferenceLookup($request, $lookup, true, $applicantName, null, [
                    'local_user_id' => $student->id,
                    'local_email' => $student->email,
                    'source' => 'puptas',
                ]);
            } elseif ($student && $lookupStatus === 'local_health_profile') {
                $this->logReferenceLookup($request, $lookup, true, $student->name, null, [
                    'local_user_id' => $student->id,
                    'local_email' => $student->email,
                    'source' => 'local_health_profile',
                    'puptas_lookup_status' => $lookupResult['status'] ?? null,
                ]);
            } elseif (!is_array($applicant) && $lookupResult['success'] === false) {
                // Failed lookup with error
                $this->logReferenceLookup($request, $lookup, false, null, $lookupMessage);
            }
        }

        if (!$student && $lookup === '' && $lookupName !== '') {
            $student = $this->findUniqueUserByName($lookupName);
            if (!$student) {
                $lookupMessage = 'No unique patient matched that name in local records yet.';
            }
        }

        if ($student) {
            $resolvedName = trim((string) ($student->name ?: trim(($student->first_name ?? '') . ' ' . ($student->last_name ?? ''))));
            $healthProfile = HealthProfile::where('user_id', $student->id)->first();
            $resolvedReferenceNumber = trim((string) (
                ($lookup !== '' && !$this->looksLikeUuid($lookup) ? $lookup : null)
                ?: $student->reference_number
                ?: optional($healthProfile)->reference_number
            ));
            $resolvedCourse = trim((string) ($student->course ?? $student->course_college ?? ''));
            $resolvedYear = trim((string) ($student->year ?? ''));
            $resolvedSection = trim((string) ($student->section ?? ''));
            $resolvedDob = !empty($student->DOB) ? (string) $student->DOB : '';
            $resolvedEmail = trim((string) ($student->email ?? ''));
            $resolvedBirthday = trim((string) (optional($healthProfile)->birthday ?: $resolvedDob));
            $resolvedAge = optional($healthProfile)->age;
            if (($resolvedAge === null || $resolvedAge === '') && $resolvedBirthday !== '') {
                try {
                    $resolvedAge = \Carbon\Carbon::parse($resolvedBirthday)->age;
                } catch (\Throwable $exception) {
                    $resolvedAge = null;
                }
            }
            $resolvedHeight = trim((string) (optional($healthProfile)->height ?: ($student->height ?? '')));
            $resolvedWeight = trim((string) (optional($healthProfile)->weight ?: ($student->weight ?? '')));
            $resolvedSex = trim((string) (optional($healthProfile)->sex ?: ($student->gender ?? '')));
            $resolvedCivilStatus = trim((string) optional($healthProfile)->civil_status);
            $resolvedContactNumber = trim((string) (
                optional($healthProfile)->cellphone
                ?: optional($healthProfile)->landline
                ?: ($student->contact_no ?? '')
            ));
            $walkinLookupIdentifier = (string) (
                $student->student_number
                ?: $resolvedReferenceNumber
                ?: $student->student_id
                ?: $student->id
            );
            $walkinRoutePrefix = Str::startsWith((string) optional($request->route())->getName(), 'assistant.')
                ? '/assistant'
                : '/admin';
            $rawClearanceStatus = trim((string) optional($healthProfile)->clearance_status);
            $medicalCertificateResult = trim((string) optional($healthProfile)->med_cert_findings);
            $medicalCertificateDetails = \Schema::hasColumn('health_profiles', 'med_cert_findings_details')
                ? trim((string) optional($healthProfile)->med_cert_findings_details)
                : '';
            $xrayResult = trim((string) optional($healthProfile)->xray_findings);
            $xrayDetails = \Schema::hasColumn('health_profiles', 'xray_findings_details')
                ? trim((string) optional($healthProfile)->xray_findings_details)
                : '';
            $resolvedClinicStatus = match (true) {
                in_array($rawClearanceStatus, ['Issued', 'Fully Cleared'], true) => 'Fully Cleared',
                $rawClearanceStatus === 'Pending/Conditional' => 'Pending Compliance / Conditional',
                in_array($rawClearanceStatus, ['Pending', 'For Verification'], true) => 'Pending Verification',
                $rawClearanceStatus !== '' => $rawClearanceStatus,
                default => 'Awaiting Uploads',
            };

            if ($previewOnly) {
                return response()->json([
                    'status' => 'preview',
                    'reference_number' => $resolvedReferenceNumber,
                    'student_number' => $student->student_number ?: '',
                    'student_id' => $student->student_id ?: '',
                    'student_name' => $resolvedName,
                    'course' => $resolvedCourse,
                    'year' => $resolvedYear,
                    'section' => $resolvedSection,
                    'dob' => $resolvedDob,
                    'email' => $resolvedEmail,
                    'age' => $resolvedAge,
                    'height' => $resolvedHeight,
                    'weight' => $resolvedWeight,
                    'sex' => $resolvedSex,
                    'civil_status' => $resolvedCivilStatus,
                    'contact_number' => $resolvedContactNumber,
                    'clinic_status' => $resolvedClinicStatus,
                    'clearance_status' => $rawClearanceStatus,
                    'approved' => in_array($resolvedClinicStatus, ['Fully Cleared'], true),
                    'health_profile_id' => optional($healthProfile)->id,
                    'medical_assessment_upload' => optional($healthProfile)->medical_assessment_upload,
                    'medical_certificate_result' => $medicalCertificateResult,
                    'medical_certificate_findings_details' => $medicalCertificateDetails,
                    'xray_result' => $xrayResult,
                    'xray_findings_details' => $xrayDetails,
                    'assessment_review' => $this->healthProfileAssessmentReview($healthProfile),
                    'documents' => $this->healthProfileDocuments($request, $healthProfile),
                    'name_matches' => $lookupName !== '' ? $this->namesRoughlyMatch($lookupName, $student) : null,
                    'lookup_status' => $lookupStatus,
                    'lookup_source' => in_array($lookupStatus, ['local_health_profile', 'local_clinic_reference'], true)
                        ? $lookupStatus
                        : 'puptas_or_local_user',
                    'sync_warning' => $lookupStatus === 'local_health_profile'
                        ? 'Local health profile found. PUPTAS sync will only succeed if this saved reference matches the Admission System.'
                        : null,
                    'redirect_url' => url($walkinRoutePrefix . '/walkin/form/' . rawurlencode($walkinLookupIdentifier) . '?source=walkin'),
                ]);
            }

            if (!$this->namesRoughlyMatch($lookupName, $student)) {
                return response()->json([
                    'status' => 'name_mismatch',
                    'lookup_status' => $lookupStatus,
                    'message' => 'The student number matched a record, but the extracted name does not match our saved name yet.',
                    'candidate' => [
                        'student_number' => $student->student_number ?: $student->student_id,
                        'name' => $resolvedName,
                    ],
                ]);
            }

            return response()->json([
                'status' => 'found',
                'reference_number' => $resolvedReferenceNumber,
                'student_number' => $student->student_number ?: '',
                'student_id' => $student->student_id ?: '',
                'student_name' => $resolvedName,
                'course' => $resolvedCourse,
                'year' => $resolvedYear,
                'section' => $resolvedSection,
                'dob' => $resolvedDob,
                'email' => $resolvedEmail,
                'age' => $resolvedAge,
                'height' => $resolvedHeight,
                'weight' => $resolvedWeight,
                'sex' => $resolvedSex,
                'civil_status' => $resolvedCivilStatus,
                'contact_number' => $resolvedContactNumber,
                'clinic_status' => $resolvedClinicStatus,
                'clearance_status' => $rawClearanceStatus,
                'approved' => in_array($resolvedClinicStatus, ['Fully Cleared'], true),
                'health_profile_id' => optional($healthProfile)->id,
                'medical_assessment_upload' => optional($healthProfile)->medical_assessment_upload,
                'medical_certificate_result' => $medicalCertificateResult,
                'medical_certificate_findings_details' => $medicalCertificateDetails,
                'xray_result' => $xrayResult,
                'xray_findings_details' => $xrayDetails,
                'assessment_review' => $this->healthProfileAssessmentReview($healthProfile),
                'documents' => $this->healthProfileDocuments($request, $healthProfile),
                'lookup_status' => $lookupStatus,
                'lookup_source' => in_array($lookupStatus, ['local_health_profile', 'local_clinic_reference'], true)
                    ? $lookupStatus
                    : 'puptas_or_local_user',
                'sync_warning' => $lookupStatus === 'local_health_profile'
                    ? 'Local health profile found. PUPTAS sync will only succeed if this saved reference matches the Admission System.'
                    : null,
                'redirect_url' => $intakeTarget === 'assessment'
                    ? (function () use ($student) {
                        $profile = HealthProfile::firstOrCreate(
                            ['user_id' => $student->id],
                            [
                                'student_id' => (string) ($student->student_id ?? ''),
                                'student_number' => (string) ($student->student_number ?? ''),
                                'reference_number' => (string) ($student->reference_number ?? ''),
                                'course_college' => (string) ($student->course ?? ''),
                                'birthday' => (string) ($student->DOB ?? ''),
                                'sex' => (string) ($student->gender ?? ''),
                                'clearance_status' => 'Pending',
                            ]
                        );

                        $profileNeedsSave = false;
                        if (empty($profile->student_number) && !empty($student->student_number)) {
                            $profile->student_number = (string) $student->student_number;
                            $profileNeedsSave = true;
                        }
                        if (empty($profile->reference_number) && !empty($student->reference_number)) {
                            $profile->reference_number = (string) $student->reference_number;
                            $profileNeedsSave = true;
                        }
                        if (empty($profile->student_id) && !empty($student->student_id)) {
                            $profile->student_id = (string) $student->student_id;
                            $profileNeedsSave = true;
                        }
                        if ($profileNeedsSave) {
                            $profile->save();
                        }

                        return route('admin.show_health', $profile->id);
                    })()
                    : route($this->walkinRouteName($request, 'form'), [
                        'student_id' => $walkinLookupIdentifier,
                        'source' => 'walkin'
                    ])
            ]);
        }

        return response()->json([
            'status' => 'not_found',
            'scanned_barcode' => $lookup,
            'lookup_status' => $lookupStatus,
            'message' => $lookupMessage,
        ]);
    }

    public function showApplicantHealthForm(Request $request, HealthProfile $healthProfile)
    {
        $healthProfile->loadMissing('user');
        abort_unless($healthProfile->user, 404);

        return view('student.print_health_form', [
            'profile' => $healthProfile,
            'adminViewer' => true,
        ]);
    }

    public function verifyStudentIdWithAi(Request $request)
    {
        $request->validate([
            'image_data' => 'required|string',
        ]);

        $apiKey = trim((string) config('services.openai.api_key'));
        if ($apiKey === '') {
            return response()->json([
                'status' => 'unavailable',
                'message' => 'AI verification is not configured yet. Please add OPENAI_API_KEY first.',
            ], 422);
        }

        $imageData = trim((string) $request->input('image_data'));
        if (!Str::startsWith($imageData, 'data:image/')) {
            return response()->json([
                'status' => 'invalid_image',
                'message' => 'The captured ID image is invalid. Please capture the card again.',
            ], 422);
        }

        $model = trim((string) config('services.openai.model', ''));
        if ($model === '') {
            $model = 'gpt-4.1-mini';
        }

        $prompt = <<<'PROMPT'
You are reading a school ID card for clinic intake.
Your top priority is extracting the student number correctly.
Return strict JSON with these keys:
student_number, first_name, surname, full_name, confidence_note

Rules:
- Focus on the student number first. It is the most important field.
- Student number format may look like: 2025-00523-TG-0
- Preserve hyphens in the student number.
- If the student number is readable but the name is unclear, return the student number and leave the name fields empty.
- Only fill first_name, surname, and full_name when they are clearly readable from the card.
- confidence_note should be a short plain-English note focused on how reliable the student number extraction is.
- Return JSON only. No markdown fence. No explanation.
PROMPT;

        try {
            $response = Http::withToken($apiKey)
                ->acceptJson()
                ->timeout(30)
                ->post('https://api.openai.com/v1/responses', [
                    'model' => $model,
                    'input' => [[
                        'role' => 'user',
                        'content' => [
                            ['type' => 'input_text', 'text' => $prompt],
                            ['type' => 'input_image', 'image_url' => $imageData, 'detail' => 'high'],
                        ],
                    ]],
                ]);

            if (!$response->successful()) {
                return response()->json([
                    'status' => 'api_error',
                    'message' => 'AI verification could not finish right now.',
                    'details' => $response->json() ?: $response->body(),
                ], $response->status());
            }

            $payload = $response->json() ?: [];
            $text = $this->extractOpenAiOutputText($payload);
            $decoded = $this->decodeAiVerificationText($text);

            if (!$decoded) {
                Log::warning('AI ID verification returned non-JSON text.', ['text' => $text]);

                return response()->json([
                    'status' => 'parse_error',
                    'message' => 'AI verification returned an unreadable response. Please try again.',
                ], 422);
            }

            return response()->json([
                'status' => 'success',
                'student_number' => $decoded['student_number'],
                'student_name' => trim($decoded['full_name'] !== '' ? $decoded['full_name'] : trim($decoded['first_name'] . ' ' . $decoded['surname'])),
                'first_name' => $decoded['first_name'],
                'surname' => $decoded['surname'],
                'confidence_note' => $decoded['confidence_note'],
            ]);
        } catch (\Throwable $exception) {
            Log::error('AI ID verification failed.', [
                'message' => $exception->getMessage(),
            ]);

            return response()->json([
                'status' => 'server_error',
                'message' => 'AI verification failed. Please try again or use manual confirmation.',
            ], 500);
        }
    }

    // 4. REGISTER STUDENT
    public function registerStudent(Request $request)
    {
        $request->validate([
            'student_number' => 'required',
            'first_name' => 'required',
            'middle_name' => 'nullable|string|max:255',
            'last_name'  => 'required',
            'email'      => 'required|email',
            'password'   => 'nullable|min:6',
            'user_role'  => 'required',
            'dob'        => 'nullable|date',
            'gender'     => 'nullable|string|max:50',
            'contact_no' => 'nullable|string|max:20',
        ]);

        $email = trim((string) $request->email);
        $password = trim((string) $request->password);

        $studentNumber = trim((string) $request->student_number);

        $existingUser = User::query()
                            ->where(function ($query) use ($studentNumber) {
                                if (\Schema::hasColumn('users', 'student_number')) {
                                    $query->orWhere('student_number', $studentNumber);
                                }

                                $query->orWhere('student_id', $studentNumber);
                            })
                            ->when($email !== '', function ($query) use ($email) {
                                $query->orWhere('email', $email);
                            })
                            ->first();

        if ($existingUser) {
            return response()->json([
                'success' => false,
                'message' => 'This account already exists.',
                'redirect_url' => route($this->walkinRouteName($request, 'form'), ['student_id' => $existingUser->student_number ?: $existingUser->student_id])
            ], 409);
        }

        if ($password === '') {
            $password = Str::random(12);
        }

        $user = User::create([
            'student_id' => $this->resolveUniqueStudentId('assisted-' . Str::slug($studentNumber, '-')),
            'student_number' => $studentNumber,
            'first_name' => $request->first_name,
            'middle_name' => $request->input('middle_name'),
            'last_name'  => $request->last_name,
            'name'       => trim(implode(' ', array_filter([
                $request->first_name,
                $request->input('middle_name'),
                $request->last_name,
            ]))),
            'email'      => $email,
            'password'   => Hash::make($password),
            'user_role'  => $request->user_role, 
            'barcode'    => $request->barcode,
            'DOB'        => $request->input('dob'),
            'gender'     => $request->input('gender'),
            'contact_no' => $request->input('contact_no'),
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Student registered successfully!',
            'redirect_url' => route($this->walkinRouteName($request, 'form'), ['student_id' => $user->student_number ?: $user->student_id])
        ]);
    }

    // 5. FINAL STORE
    public function store(Request $request)
    {
        $request->validate([
            'student_number' => 'required',
            'service'      => 'required',
            'remarks'      => 'required',
            'condition_id' => 'required|exists:medical_conditions,id',
            'dob'          => 'nullable|date',
            'height'       => 'nullable|numeric|min:0|max:400',
            'weight'       => 'nullable|numeric|min:0|max:1000',
            'temp'         => 'nullable|numeric|min:30|max:45',
            'bp'           => 'nullable|string|max:20',
            'pulse_rate'   => 'nullable|integer|min:0|max:300',
            'respiratory_rate' => 'nullable|integer|min:0|max:120',
            'covid_status' => 'required|in:Yes,No',
            'reason_for_visit' => 'nullable|string|max:255',
            'certificate_type' => 'nullable|in:none,excused_letter,coc_ijt,coc_ladderized',
            'item_id' => 'nullable|exists:items,id',
            'issued_quantity' => 'nullable|numeric|min:0.01',
            'consultation_started_at' => 'nullable|date_format:H:i:s',
        ]);

        $student = $this->findUserByIdentifier((string) $request->student_number);

        if (!$student) {
            return redirect()->back()->with('error', 'Student not found.');
        }

        $requestedSource = $this->normalizeConsultationSource($request->input('user_type', 'walkin'));
        $consultationSessionKey = $this->consultationStartSessionKey(
            auth()->id(),
            $student->id,
            $requestedSource
        );
        $consultationStartedAt = (string) $request->session()->get($consultationSessionKey, '');

        if (!preg_match('/^\d{2}:\d{2}:\d{2}$/', $consultationStartedAt)) {
            $submittedStartedAt = (string) $request->input('consultation_started_at', '');
            $consultationStartedAt = preg_match('/^\d{2}:\d{2}:\d{2}$/', $submittedStartedAt)
                ? $submittedStartedAt
                : now()->format('H:i:s');
            $request->session()->put($consultationSessionKey, $consultationStartedAt);
        }

        if ($request->filled('dob')) {
            $student->DOB = $request->input('dob');
        }

        if ($request->filled('height')) {
            $student->height = $request->input('height');
        }

        if ($request->filled('weight')) {
            $student->weight = $request->input('weight');
        }

        $student->save();

        if ($student->healthProfile) {
            if ($request->filled('dob')) {
                $student->healthProfile->birthday = $request->input('dob');
            }

            if ($request->filled('height')) {
                $student->healthProfile->height = (string) $request->input('height');
            }

            if ($request->filled('weight')) {
                $student->healthProfile->weight = (string) $request->input('weight');
            }

            $student->healthProfile->save();
        }

        $issuedQuantity = (float) $request->input('issued_quantity', 0);
        $dispensedItem = null;

        if ($request->filled('item_id')) {
            $dispensedItem = Item::find($request->input('item_id'));

            if (!$dispensedItem) {
                return redirect()->back()->withInput()->with('error', 'Selected medicine was not found in inventory.');
            }

            if ($issuedQuantity <= 0) {
                return redirect()->back()->withInput()->with('error', 'Enter the quantity to issue for the selected medicine.');
            }

            if ($dispensedItem->requiresDispensingConversion() && !$dispensedItem->hasDispensingConversion()) {
                return redirect()->back()->withInput()->with(
                    'error',
                    'This medicine uses a stock unit like box or bottle. Please edit the inventory item first and set the dispensing unit plus units per stock unit.'
                );
            }

            $availableDispensingQuantity = $dispensedItem->availableDispensingQuantity();
            if ($issuedQuantity - $availableDispensingQuantity > 0.00001) {
                $availableUnitLabel = $dispensedItem->hasDispensingConversion()
                    ? ($dispensedItem->dispensing_unit ?: $dispensedItem->unit)
                    : $dispensedItem->unit;

                return redirect()->back()->withInput()->with(
                    'error',
                    'Only ' . $this->formatQuantityNumber($availableDispensingQuantity) . ' ' . $availableUnitLabel . ' of ' . $dispensedItem->name . ' are currently available.'
                );
            }
        } elseif ($issuedQuantity > 0) {
            return redirect()->back()->withInput()->with('error', 'Select a medicine before entering a quantity to issue.');
        }

        DB::transaction(function () use ($request, $student, $dispensedItem, $issuedQuantity, $requestedSource, $consultationStartedAt) {
            $isOnlineSource = $requestedSource === 'online';
            $finalSource = 'walkin';
            $patientType = Appointment::normalizeUserType($student->user_role ?? $student->user_type);

            if ($isOnlineSource) {
                $existingAppt = Appointment::where('student_id', $student->student_id)
                    ->where('status', 'Approved')
                    ->whereDate('date', now()->format('Y-m-d'))
                    ->first();

                if (!$existingAppt && !empty($student->student_number)) {
                    $existingAppt = Appointment::where('student_number', $student->student_number)
                        ->where('status', 'Approved')
                        ->whereDate('date', now()->format('Y-m-d'))
                        ->first();
                }

                if ($existingAppt) {
                    $existingAppt->status = 'Completed';
                    $existingAppt->service = $request->service;
                    $existingAppt->save();
                    $finalSource = 'online';
                }
            }

            if ($finalSource !== 'online') {
                $appointment = new Appointment();
                $appointment->user_id    = $student->id;
                $appointment->student_id = $student->student_id;
                $appointment->student_number = $student->student_number ?? null;
                $appointment->name       = $student->name;
                $appointment->email      = $student->email; 
                $appointment->service    = $request->service;
                $appointment->remarks    = $request->input('reason_for_visit') ?: $request->remarks;
                $appointment->status     = 'Completed';
                $appointment->date       = now()->format('Y-m-d');
                $appointment->time       = now()->format('H:i:s'); 
                $appointment->type       = 'walkin';
                $appointment->user_type  = $patientType;
                $appointment->save();
            }

            // --- MEDICINE LOGIC ---
            $medicineName = 'None';
            if ($dispensedItem) {
                $item = Item::query()->lockForUpdate()->find($dispensedItem->id);
                $medicineName = $item ? $item->name : 'None';

                if ($item && $issuedQuantity > 0) {
                    $availableDispensingQuantity = $item->availableDispensingQuantity();
                    if ($issuedQuantity - $availableDispensingQuantity > 0.00001) {
                        throw \Illuminate\Validation\ValidationException::withMessages([
                            'issued_quantity' => ['The selected medicine no longer has enough stock for that quantity.'],
                        ]);
                    }

                    $stockDeduction = $item->convertDispensingQuantityToStockQuantity($issuedQuantity);
                    $stockBefore = (float) $item->quantity;
                    $item->decrement('quantity', $stockDeduction);
                    $item->refresh();

                    InventoryMovement::create([
                        'item_id' => $item->id,
                        'user_id' => auth()->id(),
                        'type' => 'consumed',
                        'quantity' => -1 * $stockDeduction,
                        'stock_before' => $stockBefore,
                        'stock_after' => (float) $item->quantity,
                        'unit' => $item->unit ?: 'pcs',
                        'batch_number' => $item->batch_number,
                        'supplier_source' => $item->supplier_source,
                        'notes' => 'Issued during consultation.',
                    ]);
                }
            }

            // --- SAVE TO CONSULTATIONS TABLE ---
            \App\Models\Consultation::create([
                'user_id'              => $student->id,
                'attending_staff_id'   => auth()->id(),
                'attending_staff_name' => auth()->user()?->name ?? auth()->user()?->email ?? 'Clinic Staff',
                'name'                 => $student->name,
                'consultation_date'    => now()->format('Y-m-d'),
                'time_in'              => $consultationStartedAt,
                'time_out'             => now()->format('H:i:s'),
                'user_role'            => $patientType,
                'user_type'            => $patientType,
                'consultation_source'  => $finalSource,
                'service'              => $request->service,
                'medical_condition_id' => $request->condition_id,
                'temperature'          => $request->temp,
                'blood_pressure'       => $request->bp,
                'pulse_rate'           => $request->input('pulse_rate'),
                'respiratory_rate'     => $request->input('respiratory_rate'),
                'covid_status'         => $request->input('covid_status'),
                'reason_for_visit'     => $request->input('reason_for_visit'),
                'certificate_type'     => $request->input('certificate_type') ?: 'none',
                'medicine'             => $medicineName,
                'item_id'              => $dispensedItem?->id,
                'medicine_quantity'    => $issuedQuantity > 0 ? $issuedQuantity : 0,
                'comments'             => $request->remarks,
            ]);
        });

        $request->session()->forget($consultationSessionKey);

        // Redirect logic
        if ($requestedSource === 'online') {
            return redirect($this->adminBasePrefix($request) . '/appointments')
                ->with('success', 'Online consultation completed!');
        }

        return redirect()->route($this->walkinRouteName($request, 'index'))->with('consultation_done', true);
    }

    public function approveApplicant(Request $request, PuptasWebhookService $webhookService)
    {
        try {
            $validated = $request->validate([
                'reference_number' => ['required', 'string', 'max:120'],
                'lookup_scope' => ['nullable', 'string', 'max:40'],
                'findings_status' => ['required', 'string', 'in:No Findings / Normal,With Findings'],
                'has_medical_condition' => ['nullable', 'boolean'],
                'incomplete_requirements' => ['nullable', 'boolean'],
                'needs_physician_evaluation' => ['nullable', 'boolean'],
                'other_pending_reason' => ['nullable', 'string', 'max:1000'],
                'medical_condition' => ['required_if:has_medical_condition,true', 'nullable', 'string', 'max:1000'],
                'condition_remarks' => ['nullable', 'string', 'max:2000'],
                'blood_pressure' => ['required', 'string', 'max:20', 'regex:/^\d{2,3}\s*\/\s*\d{2,3}$/'],
                'pulse_rate' => ['required', 'integer', 'min:1', 'max:300'],
                'respiratory_rate' => ['required', 'integer', 'min:1', 'max:120'],
                'temperature' => ['required', 'numeric', 'min:30', 'max:45'],
                'covid_positive' => ['required', 'string', 'in:Yes,No'],
                'covid_positive_date' => ['required_if:covid_positive,Yes', 'nullable', 'date'],
            ]);
            $referenceNumber = trim((string) $validated['reference_number']);
            $lookupScope = strtolower(trim((string) ($validated['lookup_scope'] ?? 'default')));
            $forceLocalClinicApproval = $lookupScope === 'clinic_local';
            $findingsStatus = (string) $validated['findings_status'];
            $hasMedicalCondition = $request->boolean('has_medical_condition');
            $hasIncompleteRequirements = $request->boolean('incomplete_requirements');
            $needsPhysicianEvaluation = $request->boolean('needs_physician_evaluation');
            $medicalCondition = trim((string) $request->input('medical_condition', ''));
            $otherPendingReason = trim((string) $request->input('other_pending_reason', ''));
            $conditionRemarks = trim((string) $request->input('condition_remarks', ''));
            $bloodPressure = preg_replace('/\s+/', '', (string) $validated['blood_pressure']);
            $pulseRate = (int) $validated['pulse_rate'];
            $respiratoryRate = (int) $validated['respiratory_rate'];
            $temperature = (float) $validated['temperature'];
            $covidPositive = (string) $validated['covid_positive'];
            $covidPositiveDate = $covidPositive === 'Yes'
                ? $validated['covid_positive_date']
                : null;

            $pendingReasons = [];
            if ($hasMedicalCondition) {
                $pendingReasons[] = $medicalCondition !== '' ? 'Medical Condition: ' . $medicalCondition : 'With Medical Condition';
            }
            if ($hasIncompleteRequirements) {
                $pendingReasons[] = 'Incomplete Requirements';
            }
            if ($needsPhysicianEvaluation) {
                $pendingReasons[] = 'For Physician Evaluation';
            }
            if ($otherPendingReason !== '') {
                $pendingReasons[] = 'Others: ' . $otherPendingReason;
            }
            $hasPendingFinding = $findingsStatus === 'With Findings';

            if ($hasPendingFinding && empty($pendingReasons)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Please select at least one pending reason.',
                ], 422);
            }

            // Fetch applicant details to get student ID
            $applicantData = $forceLocalClinicApproval
                ? null
                : $webhookService->fetchApplicantByStudentNumber($referenceNumber);
            $localOnlyProfile = null;
            $isLocalOnlyApproval = $forceLocalClinicApproval;

            if (!$applicantData) {
                $localOnlyProfile = $this->findHealthProfileByReference($referenceNumber);
                if (!$localOnlyProfile) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Applicant not found in PUPTAS or local submitted health profiles.'
                    ], 404);
                }

                $student = $this->ensureLocalUserFromHealthProfile($localOnlyProfile, $referenceNumber);
                if (!$student) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Local health profile was found, but no linked student account is available.'
                    ], 404);
                }

                $isLocalOnlyApproval = true;
                $applicantData = null;
            }

            if (!$isLocalOnlyApproval) {
                $idpStudentId = trim((string) ($applicantData['idp_user_id'] ?? ''));
                $studentId = $idpStudentId !== '' ? $idpStudentId : $referenceNumber;
                $student = $this->resolveLocalUserFromApplicant($applicantData, true, $referenceNumber);
            } else {
                $idpStudentId = trim((string) ($student->student_id ?? $localOnlyProfile?->student_id ?? ''));
                $studentId = $idpStudentId !== '' ? $idpStudentId : $referenceNumber;
            }
            $clearanceStatus = $hasPendingFinding ? 'Pending/Conditional' : 'Fully Cleared';

            // Conditional applicants remain uncleared in PUPTAS until compliance is resolved.
            $webhookResult = $isLocalOnlyApproval
                ? [
                    'success' => false,
                    'skipped' => true,
                    'message' => 'Local clinic reference decision saved without PUPTAS sync.',
                ]
                : $webhookService->sendMedicalClearance(
                    $referenceNumber,
                    $idpStudentId,
                    !$hasPendingFinding
                );

            $profile = DB::transaction(function () use (
                $student,
                $studentId,
                $referenceNumber,
                $clearanceStatus,
                $hasMedicalCondition,
                $hasPendingFinding,
                $pendingReasons,
                $medicalCondition,
                $conditionRemarks,
                $findingsStatus,
                $bloodPressure,
                $pulseRate,
                $respiratoryRate,
                $temperature,
                $covidPositive,
                $covidPositiveDate,
                $webhookResult,
                $isLocalOnlyApproval
            ) {
                $pendingAssessment = \Schema::hasTable('pending_medical_assessments')
                    ? \App\Models\PendingMedicalAssessment::query()
                        ->where('reference_number', $referenceNumber)
                        ->latest()
                        ->first()
                    : null;

                if ($pendingAssessment && !$pendingAssessment->user_id) {
                    $pendingAssessment->user_id = $student->id;
                    $pendingAssessment->save();
                }

                $profile = HealthProfile::firstOrNew(['user_id' => $student->id]);
                $profile->student_id = (string) ($student->student_id ?: $studentId);
                $profile->student_number = (string) ($student->student_number ?: $referenceNumber);
                $profile->reference_number = $referenceNumber;
                $profile->course_college = (string) ($profile->course_college ?: $student->course);
                $profile->birthday = $profile->birthday ?: $student->DOB;
                $profile->sex = (string) ($profile->sex ?: $student->gender);
                $profile->med_cert_findings = $findingsStatus;
                $profile->xray_findings = trim((string) $profile->xray_findings) !== ''
                    ? $profile->xray_findings
                    : ($findingsStatus === 'No Findings / Normal' ? 'Normal' : 'With Findings');
                $profile->blood_pressure = $bloodPressure;
                $profile->pulse_rate = $pulseRate;
                $profile->respiratory_rate = $respiratoryRate;
                $profile->temperature = $temperature;
                $profile->covid_positive = $covidPositive;
                $profile->covid_positive_date = $covidPositiveDate;
                $profile->medical_certificate_issued_by = $profile->doctor_name;
                $profile->medical_certificate_issued_at = $profile->med_cert_date;
                $profile->chest_xray_result_text = $this->normalizeMedicalAssessmentXrayResult($profile->xray_findings);
                $profile->chest_xray_date = $profile->xray_date;
                $profile->assessment_date = $hasPendingFinding ? $profile->assessment_date : now()->toDateString();
                $profile->clearance_status = $clearanceStatus;
                $profile->pending_reason = $hasPendingFinding
                    ? trim(implode('; ', $pendingReasons)
                        . ($conditionRemarks !== '' ? "\n" . $conditionRemarks : ''))
                    : null;
                $profile->medical_condition_remarks = $hasMedicalCondition
                    ? trim(($medicalCondition !== '' ? $medicalCondition : 'With findings')
                        . ($conditionRemarks !== '' ? "\n" . $conditionRemarks : ''))
                    : null;
                $profile->assessment_remarks = !$hasPendingFinding && $conditionRemarks !== ''
                    ? $conditionRemarks
                    : $profile->assessment_remarks;
                $profile->has_illness = $hasMedicalCondition ? 'Yes' : ($profile->has_illness ?: 'No');
                $profile->other_illness = $hasMedicalCondition ? $medicalCondition : $profile->other_illness;
                $profile->physical_assessment_status = $hasPendingFinding
                    ? 'Not Yet Conducted'
                    : 'Completed / Passed';
                $profile->documents_valid = !$hasPendingFinding;
                $profile->verified_at = $hasPendingFinding ? null : now();
                $profile->puptas_sync_status = ($webhookResult['skipped'] ?? false)
                    ? null
                    : (($webhookResult['success'] ?? false) ? 'synced' : 'failed');
                $profile->puptas_synced_at = ($webhookResult['success'] ?? false)
                    && !($webhookResult['skipped'] ?? false)
                        ? now()
                        : null;
                $profile->puptas_sync_message = $webhookResult['message'] ?? null;
                if ($isLocalOnlyApproval && !($webhookResult['success'] ?? false) && !($webhookResult['skipped'] ?? false)) {
                    $profile->puptas_sync_message = trim((string) ($profile->puptas_sync_message ?? ''))
                        ?: 'Local approval saved. PUPTAS sync still needs a matching Admission reference.';
                }

                if (!$profile->medical_assessment_upload && $pendingAssessment) {
                    $profile->medical_assessment_upload = $pendingAssessment->file_path;
                }

                $profile->save();

                if (!$hasPendingFinding) {
                    $profile->medical_assessment_upload = $this->generateMedicalAssessmentCopy($profile);
                    $profile->save();
                }

                $student->is_health_profile_completed = $hasPendingFinding ? 0 : 1;
                $student->save();

                return $profile;
            });

            Log::info('Applicant reference decision saved', [
                'reference_number' => $referenceNumber,
                'student_id' => $studentId,
                'health_profile_id' => $profile->id,
                'clearance_status' => $clearanceStatus,
                'webhook_success' => (bool) ($webhookResult['success'] ?? false),
                'user_id' => auth()->id(),
            ]);

            ActivityLog::create([
                'user_id' => auth()->id(),
                'user_name' => auth()->user()?->name ?? auth()->user()?->email ?? 'System',
                'user_role' => strtolower((string) (auth()->user()?->user_role ?? '')),
                'action' => $hasPendingFinding ? 'Applicant Pending Compliance' : 'Applicant Approval',
                'module' => 'Patient Intake',
                'event_type' => $hasPendingFinding ? 'applicant_pending_compliance' : 'applicant_approval',
                'description' => $hasPendingFinding
                    ? "Applicant set to pending compliance: {$referenceNumber} (Student ID: {$studentId})"
                    : "Applicant approved: {$referenceNumber} (Student ID: {$studentId})",
                'route_name' => optional($request->route())->getName(),
                'http_method' => 'POST',
                'request_path' => '/' . ltrim((string) $request->path(), '/'),
                'status_code' => 200,
                'subject_type' => HealthProfile::class,
                'subject_id' => (string) $profile->id,
                'metadata' => [
                    'reference_number' => $referenceNumber,
                    'student_id' => $studentId,
                    'health_profile_id' => $profile->id,
                    'clearance_status' => $clearanceStatus,
                    'findings_status' => $findingsStatus,
                    'pending_reasons' => $pendingReasons,
                    'webhook_status' => ($webhookResult['success'] ?? false) ? 'success' : 'failed',
                    'webhook_message' => $webhookResult['message'] ?? null,
                    'lookup_source' => $isLocalOnlyApproval ? 'local_clinic_reference' : 'puptas',
                ],
                'ip_address' => $request->ip(),
                'user_agent' => substr((string) $request->userAgent(), 0, 255),
            ]);

            $redirectUrl = route('admin.health_records')
                . ($hasPendingFinding
                    ? '?tab=pending_conditional&highlight_health=' . $profile->id
                    : '?highlight_health=' . $profile->id);

            return response()->json([
                'success' => true,
                'status' => $clearanceStatus,
                'message' => $hasPendingFinding
                    ? 'Applicant saved under Pending Compliance.'
                    : (($webhookResult['success'] ?? false)
                        ? 'Applicant approved and synced to PUPTAS.'
                        : 'Applicant approved locally. PUPTAS sync still needs attention.'),
                'redirect_url' => $redirectUrl,
                'webhook_synced' => (bool) ($webhookResult['success'] ?? false),
                'lookup_source' => $isLocalOnlyApproval ? 'local_clinic_reference' : 'puptas',
            ]);
        } catch (\Exception $e) {
            Log::error('Applicant approval exception', [
                'error' => $e->getMessage(),
                'reference_number' => $request->input('reference_number'),
                'user_id' => auth()->id(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'An error occurred during approval: ' . $e->getMessage()
            ], 500);
        }
    }

    private function linkPendingMedicalAssessments(User $user, string $email): void
    {
        if (!class_exists('App\Models\PendingMedicalAssessment')) {
            return;
        }

        try {
            $pendingAssessments = \App\Models\PendingMedicalAssessment::where('email', $email)
                ->whereNull('user_id')
                ->get();

            foreach ($pendingAssessments as $assessment) {
                $assessment->update(['user_id' => $user->id]);

                \Log::info('Linked pending medical assessment to user', [
                    'user_id' => $user->id,
                    'assessment_id' => $assessment->id,
                    'reference_number' => $assessment->reference_number,
                    'email' => $email,
                ]);
            }
        } catch (\Exception $e) {
            \Log::warning('Failed to link pending medical assessments', [
                'user_id' => $user->id,
                'email' => $email,
                'error' => $e->getMessage(),
            ]);
        }
    }
}
