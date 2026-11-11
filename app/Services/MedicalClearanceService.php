<?php

namespace App\Services;

use App\Models\{ActivityLog, EmployeeHealthProfile, HealthFormCategory, HealthFormSubmission, HealthProfile, IssuedMedicalClearance, User};
use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\Carbon;
use Illuminate\Support\Facades\{DB, Storage};
use Illuminate\Validation\ValidationException;
use Illuminate\Http\Request;

class MedicalClearanceService
{
    public function profile(string $type, int $id, bool $lock = false): HealthProfile|EmployeeHealthProfile
    {
        abort_unless(in_array($type, ['student', 'employee'], true), 404);
        $profile = ($type === 'student' ? HealthProfile::query() : EmployeeHealthProfile::query())
            ->when($lock, fn ($query) => $query->lockForUpdate())->with('user')->findOrFail($id);
        $accountType = $profile->user?->clinicAccountTypeKey();
        abort_unless($type === 'student' ? $accountType === 'student' : in_array($accountType, ['faculty', 'non_teaching_staff'], true), 404);

        return $profile;
    }

    public function approvedSubmission(HealthProfile|EmployeeHealthProfile $profile): ?HealthFormSubmission
    {
        if (!in_array(strtolower(trim((string) $profile->clearance_status)), ['fully cleared', 'issued', 'approved'], true)
            || in_array(strtolower((string) $profile->pullout_status), ['pending', 'pulled_out', 'withdrawn', 'active'], true)) return null;
        $key = $profile instanceof EmployeeHealthProfile ? 'employee_health_profile_id' : 'health_profile_id';
        // A cancelled/rejected renewal does not invalidate the previous approval.
        // Keep active requests and revoked approvals in this check so neither is bypassed.
        $submission = HealthFormSubmission::query()->where($key, $profile->id)
            ->where(function ($query) {
                $query->whereNotIn(DB::raw('LOWER(TRIM(status))'), ['cancelled', 'canceled', 'rejected'])
                    ->orWhereNotNull('approved_at');
            })->latest('id')->first();

        return $submission && strtolower((string) $submission->status) === 'approved' && $submission->approved_at
            ? $submission : null;
    }

    public function identity(HealthProfile|EmployeeHealthProfile $profile, string $date): array
    {
        $user = $profile->user;
        $name = trim((string) ($user?->name ?: $profile->name));
        if ($name === '') $name = trim(implode(' ', array_filter([$user?->first_name, $user?->middle_name, $user?->last_name])));
        $sex = match (strtolower(trim((string) ($profile->sex ?: $user?->gender)))) { 'male', 'm' => 'M', 'female', 'f' => 'F', default => '' };
        $birthday = $profile->birthday ?: $user?->DOB;
        $age = null;
        $birthDateValue = null;
        if ($birthday) {
            try {
                $birthDate = Carbon::parse($birthday)->startOfDay();
                $birthDateValue = $birthDate->toDateString();
                $issueDate = Carbon::parse($date)->startOfDay();
                if ($birthDate->lte($issueDate)) $age = (int) $birthDate->diffInYears($issueDate);
            } catch (\Throwable $exception) { /* Leave incomplete identity blocked. */ }
        } elseif (is_numeric($profile->age)) {
            $age = (int) $profile->age;
        }

        return ['name' => $name, 'age' => $age, 'sex' => $sex, 'birth_date' => $birthDateValue];
    }

    public function healthFormCategories(string $type, HealthProfile|EmployeeHealthProfile $profile): array
    {
        $audience = $type === 'student' ? 'student' : ($profile->user?->clinicAccountTypeKey() === 'faculty' ? 'faculty' : 'admin');
        $categories = HealthFormCategory::query()->where('is_active', true)->availableFor($audience)->orderBy('name')->pluck('name')->all();
        if ($type === 'employee') $categories = array_filter($categories, fn ($category) => !preg_match('/\b(ojt|on[ -]the[ -]job)\b/i', $category));
        return array_values(array_unique(array_map(fn ($category) => app(HealthRecordCategoryFilter::class)->label($category), $categories)));
    }

    public function purposes(string $type, HealthProfile|EmployeeHealthProfile $profile, ?string $formCategory = null): array
    {
        $audience = $type === 'student' ? 'student' : ($profile->user?->clinicAccountTypeKey() === 'faculty' ? 'faculty' : 'admin');
        $purposes = HealthFormCategory::query()->where('is_active', true)->availableFor($audience)->orderBy('name')->pluck('name')->all();
        $category = trim((string) ($formCategory ?? ($this->approvedSubmission($profile)?->category ?: $profile->health_form_category)));
        if ($type === 'student') {
            $purposes = array_merge(['On the Job Training', 'Practice Teaching / Internship'], $purposes);
        }
        if ($category !== '' && ($type === 'student' || !preg_match('/\b(ojt|on[ -]the[ -]job)\b/i', $category))) $purposes[] = $category;
        // OJT is a student purpose, never an employee option.
        if ($type === 'employee') $purposes = array_filter($purposes, fn ($purpose) => !preg_match('/\b(ojt|on[ -]the[ -]job)\b/i', $purpose));

        $purposes = array_filter(array_map('trim', $purposes), fn ($purpose) => $purpose !== '' && strtolower($purpose) !== 'others');
        return array_values(array_unique(array_map(fn ($purpose) => app(HealthRecordCategoryFilter::class)->label($purpose), $purposes)));
    }

    public function issue(string $type, int $id, array $input, User $actor): IssuedMedicalClearance
    {
        $writtenPath = null;
        try {
            return DB::transaction(function () use ($type, $id, $input, $actor, &$writtenPath) {
                $profile = $this->profile($type, $id, true);
                $existing = IssuedMedicalClearance::where('request_key', $input['request_key'])->first();
                if ($existing) {
                    abort_unless($existing->record_type === $type && (int) $existing->profile_id === $id, 409);
                    return $existing;
                }
                $submission = $this->approvedSubmission($profile);
                if (!$submission) throw ValidationException::withMessages(['record' => 'Only a currently approved student or employee Health Form can receive a Medical Clearance.']);
                if (isset($input['doctor_review'])) {
                    if ((int) ($input['submission_id'] ?? 0) !== (int) $submission->id) {
                        throw ValidationException::withMessages(['record' => 'The current Health Form has changed. Look up the record again.']);
                    }
                    if (!in_array($input['doctor_review']['category'] ?? '', $this->healthFormCategories($type, $profile), true)) {
                        throw ValidationException::withMessages(['doctor_review.category' => 'Select an active Health Form category for this patient type.']);
                    }
                    if (($input['doctor_review']['decision'] ?? '') !== 'issue') {
                        throw ValidationException::withMessages(['doctor_review.decision' => 'A Medical Clearance can only be issued after a cleared Final Review.']);
                    }
                }
                $identity = $this->identity($profile, $input['issued_on']);
                if ($identity['name'] === '' || $identity['sex'] === '' || $identity['age'] === null || $identity['age'] > 120) {
                    throw ValidationException::withMessages(['record' => 'Complete the patient name, birthday/age, and sex in the health record before issuing the clearance.']);
                }
                $purpose = $input['purpose'] === '__other__' ? trim((string) ($input['other_purpose'] ?? '')) : $input['purpose'];
                if ($purpose === '' || ($input['purpose'] !== '__other__' && !in_array($purpose, $this->purposes($type, $profile), true))) {
                    throw ValidationException::withMessages(['purpose' => 'Select an available purpose or enter a purpose under Others.']);
                }
                if ($type === 'employee' && preg_match('/\b(ojt|on[ -]the[ -]job)\b/i', $purpose)) {
                    throw ValidationException::withMessages(['purpose' => 'On the Job Training is available for students only.']);
                }
                $document = IssuedMedicalClearance::create([
                    'record_type' => $type, 'profile_id' => $id, 'user_id' => $profile->user_id,
                    'health_form_submission_id' => $submission->id, 'request_key' => $input['request_key'],
                    'patient_name' => $identity['name'], 'patient_age' => $identity['age'], 'patient_sex' => $identity['sex'],
                    'issued_on' => $input['issued_on'], 'purpose' => $purpose, 'issued_by_user_id' => $actor->id,
                    'doctor_review' => isset($input['doctor_review']) ? array_merge($input['doctor_review'], ['reviewed_at' => now()->toIso8601String(), 'reviewed_by_user_id' => $actor->id]) : null,
                ]);
                $document->control_number = 'MC-' . now()->format('Y') . '-' . str_pad((string) $document->id, 6, '0', STR_PAD_LEFT);
                $document->pdf_path = 'issued_medical_clearances/' . $profile->user_id . '/' . $input['request_key'] . '.pdf';
                $pdf = Pdf::loadView('admin.templates.medical-clearance', ['templatePreview' => false, 'clearance' => [
                    'control_number' => $document->control_number, 'date' => $document->issued_on->format('M d, Y'),
                    'name' => $document->patient_name, 'age' => $document->patient_age, 'sex' => $document->patient_sex,
                    'purpose' => $document->purpose,
                ]])->setPaper('letter');
                $writtenPath = $document->pdf_path;
                if (!Storage::disk('health_private')->put($writtenPath, app(HealthFileEncryption::class)->encrypt($pdf->output()))) {
                    throw new \RuntimeException('Unable to save the Medical Clearance PDF.');
                }
                $document->save();
                ActivityLog::create([
                    'user_id' => $actor->id, 'user_name' => $actor->name ?: $actor->email,
                    'user_role' => $actor->user_role, 'action' => 'Medical Clearance Issued', 'module' => 'Patient Intake',
                    'event_type' => 'medical_clearance_issued', 'description' => 'Medical Clearance generated and saved.',
                    'subject_type' => IssuedMedicalClearance::class, 'subject_id' => (string) $document->id,
                    'metadata' => ['control_number' => $document->control_number, 'record_type' => $type, 'profile_id' => $id],
                ]);

                return $document;
            });
        } catch (\Throwable $exception) {
            if ($writtenPath) Storage::disk('health_private')->delete($writtenPath);
            throw $exception;
        }
    }

    public function approvalInput(Request $request, bool $approved, bool $allowed): ?array
    {
        if (!$approved) return null;
        if (!$allowed) {
            if ($request->boolean('issue_medical_clearance')) throw ValidationException::withMessages(['issue_medical_clearance' => 'Medical Clearances are available for student and employee reviews only.']);
            return null;
        }
        abort_unless(app(ModulePermissionService::class)->can($request->user(), 'health_records.update_assessment'), 403);
        $validated = $request->validate([
            'medical_clearance.request_key' => ['required', 'uuid'],
            'medical_clearance.purpose' => ['required', 'string', 'max:180'],
            'medical_clearance.other_purpose' => ['required_if:medical_clearance.purpose,__other__', 'nullable', 'string', 'max:180'],
        ]);

        return array_merge($validated['medical_clearance'], ['issued_on' => now()->toDateString()]);
    }
}
