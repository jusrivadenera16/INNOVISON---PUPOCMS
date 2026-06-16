<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Admin;
use App\Models\Appointment;
use App\Models\AppointmentFeedback;
use App\Models\HealthProfile;
use App\Models\User;
use App\Services\GuisisApiService;
use App\Services\PuptasWebhookService;
use App\Services\ClinicWorkflowService;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\ValidationException;
use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\Carbon;

class AppointmentController extends Controller
{
    private function formatFeedbackDisplayName(?User $user, ?Appointment $appointment = null): string
    {
        $firstName = trim((string) ($user?->first_name ?? ''));
        $lastName = trim((string) ($user?->last_name ?? ''));

        if ($firstName === '' && $lastName === '' && $appointment) {
            $nameParts = preg_split('/\s+/', trim((string) $appointment->name)) ?: [];
            $firstName = $nameParts[0] ?? '';
            $lastName = count($nameParts) > 1 ? ($nameParts[count($nameParts) - 1] ?? '') : '';
        }

        if ($firstName === '' && $lastName === '') {
            return 'Clinic User';
        }

        $lastInitial = $lastName !== '' ? strtoupper(substr($lastName, 0, 1)) . '.' : '';

        return trim($firstName . ' ' . $lastInitial);
    }

    private function getNotificationReadMap(User $user): array
    {
        $readMap = $user->notification_read_map ?? [];
        return is_array($readMap) ? $readMap : [];
    }

    private function markNotificationAsRead(User $user, string $notificationId): void
    {
        $readMap = $this->getNotificationReadMap($user);
        $readMap[$notificationId] = now()->toIso8601String();
        $user->notification_read_map = $readMap;
        $user->save();
    }

    private function buildNotificationId(string $prefix, array $parts = []): string
    {
        $normalizedParts = array_map(static fn ($value) => trim((string) $value), $parts);
        return $prefix . '-' . substr(sha1(implode('|', $normalizedParts)), 0, 16);
    }

    public function getStudentNotifications(User $user): array
    {
        Appointment::expireOverduePending();
        $workflow = app(ClinicWorkflowService::class);
        $settings = $workflow->settings();

        $user->loadMissing('healthProfile');

        $appointments = Appointment::where('user_id', $user->id)
            ->with('feedback')
            ->orderBy('updated_at', 'desc')
            ->get();

        $notifications = [];
        foreach ($appointments as $appt) {
            $timeAgo = $appt->updated_at ? $appt->updated_at->diffForHumans() : 'Just now';
            $dateStr = $appt->date ? date('M d', strtotime($appt->date)) : 'N/A';

            if ($appt->status === 'Approved') {
                $notifications[] = [
                    'id' => $this->buildNotificationId('appointment-approved', [$appt->id, $appt->status, optional($appt->updated_at)->timestamp]),
                    'type' => 'success',
                    'icon' => 'OK',
                    'message' => "Your {$appt->service} on {$dateStr} has been approved.",
                    'time' => $timeAgo,
                    'link' => url('/student/history'),
                ];
            } elseif ($appt->status === 'Cancelled') {
                $notifications[] = [
                    'id' => $this->buildNotificationId('appointment-cancelled', [$appt->id, $appt->status, optional($appt->updated_at)->timestamp]),
                    'type' => 'danger',
                    'icon' => 'X',
                    'message' => "Your {$appt->service} on {$dateStr} was cancelled.",
                    'time' => $timeAgo,
                    'link' => url('/student/history'),
                ];
            } elseif ($appt->status === 'Expired') {
                $timeLabel = $appt->time ? date('g:i A', strtotime((string) $appt->time)) : 'N/A';
                $notifications[] = [
                    'id' => $this->buildNotificationId('appointment-expired', [$appt->id, $appt->status, optional($appt->updated_at)->timestamp]),
                    'type' => 'warning',
                    'icon' => '!',
                    'message' => "Your {$appt->service} on {$dateStr} at {$timeLabel} expired because it was not approved in time. Tap to book again.",
                    'time' => $timeAgo,
                    'link' => url('/student/booking'),
                ];
            } elseif ($appt->status === 'Completed') {
                $notifications[] = [
                    'id' => $this->buildNotificationId('appointment-feedback', [$appt->id, $appt->status, optional($appt->updated_at)->timestamp]),
                    'type' => 'info',
                    'icon' => '!',
                    'message' => $appt->feedback
    ? "You already submitted feedback for {$appt->service} on {$dateStr}."
    : "Your consultation for {$appt->service} on {$dateStr} has been completed. Please share your feedback.",
                    'time' => $timeAgo,
                    'link' => route('student.feedback.show', ['appointment' => $appt->id]),
                ];
            }

            $reminderHours = (int) ($settings->appointment_reminder_hours ?? 0);
            if ($appt->status === 'Approved' && $reminderHours > 0 && $appt->date && $appt->time) {
                $appointmentAt = Carbon::parse($appt->date . ' ' . $appt->time);
                $hoursUntilAppointment = now()->diffInMinutes($appointmentAt, false) / 60;

                if ($hoursUntilAppointment > 0 && $hoursUntilAppointment <= $reminderHours) {
                    $notifications[] = [
                        'id' => $this->buildNotificationId('appointment-reminder', [$appt->id, $reminderHours, $appt->date, $appt->time]),
                        'type' => 'info',
                        'icon' => '!',
                        'message' => "Reminder: Your {$appt->service} appointment is on {$dateStr} at " . Carbon::parse($appt->time)->format('g:i A') . '.',
                        'time' => 'Upcoming appointment',
                        'link' => url('/student/history'),
                    ];
                }
            }
        }

        $healthProfile = $user->healthProfile;
        $healthProfileStatus = optional($healthProfile)->clearance_status;
        $puptasSyncStatus = optional($healthProfile)->puptas_sync_status;

        if ($healthProfile) {
            $notifications[] = [
                'id' => $this->buildNotificationId('health-record', [$healthProfile->id, $healthProfileStatus, optional($healthProfile->updated_at)->timestamp]),
                'type' => $healthProfileStatus === 'Issued' ? 'success' : 'warning',
                'icon' => $healthProfileStatus === 'Issued' ? 'OK' : '...',
                'message' => $healthProfileStatus === 'Issued'
                    ? 'Your health profile has been approved by the clinic.'
                    : 'Your health profile was submitted and is awaiting medical review.',
                'time' => 'Health profile status',
                'link' => url('/student/account?view=health-record'),
            ];

            if ($healthProfileStatus === 'Issued' && $puptasSyncStatus !== null) {
                $notifications[] = [
                    'id' => $this->buildNotificationId('puptas-sync', [$healthProfile->id, $puptasSyncStatus, optional($healthProfile->puptas_synced_at)->timestamp, optional($healthProfile->updated_at)->timestamp]),
                    'type' => $puptasSyncStatus === 'synced' ? 'success' : ($puptasSyncStatus === 'syncing' ? 'info' : 'warning'),
                    'icon' => $puptasSyncStatus === 'synced' ? 'OK' : ($puptasSyncStatus === 'syncing' ? '...' : '!'),
                    'message' => match ($puptasSyncStatus) {
                        'synced' => 'Your approved health clearance was synced to PUPTAS.',
                        'syncing' => 'Your approved health clearance is being prepared for PUPTAS sync.',
                        'missing_reference_number', 'missing_student_number' => 'Your clearance is approved, but PUPTAS sync is waiting for a valid reference number.',
                        'missing_student_id' => 'Your clearance is approved, but PUPTAS sync is waiting for your IdP student ID.',
                        'failed' => 'Your clearance is approved, but the PUPTAS sync still needs attention.',
                        default => 'Your clearance approval is being checked for PUPTAS sync.',
                    },
                    'time' => 'PUPTAS sync status',
                    'link' => url('/student/account?view=health-record'),
                ];
            }
        }

        $closure = $workflow->activeClosure();
        if ($closure) {
            $notifications[] = [
                'id' => $this->buildNotificationId('clinic-closure', [
                    $closure['reason'],
                    optional($closure['updated_at'])->timestamp,
                    optional($closure['ends_at'])->timestamp,
                ]),
                'type' => 'warning',
                'icon' => '!',
                'message' => $closure['reason'] . ': ' . $closure['message'],
                'time' => $closure['ends_at']
                    ? 'Expected reopening ' . $closure['ends_at']->format('M d, g:i A')
                    : 'Clinic advisory',
                'link' => url('/student/booking'),
            ];
        }

        $readMap = $this->getNotificationReadMap($user);

        return array_map(function (array $notification) use ($readMap) {
            $notification['is_unread'] = !isset($readMap[$notification['id']]);
            return $notification;
        }, $notifications);
    }

    private function fetchPuptasApplicantForUser(User $user): ?array
    {
        $puptasService = app(PuptasWebhookService::class);

        $referenceNumber = trim((string) ($user->reference_number ?? ''));
        if ($referenceNumber !== '' && !$this->looksLikeIdpIdentifier($referenceNumber, $user)) {
            $applicantByReference = $puptasService->fetchApplicantByStudentNumber($referenceNumber);
            if (is_array($applicantByReference) && !empty($applicantByReference)) {
                return $applicantByReference;
            }
        }

        $idpUserId = trim((string) ($user->student_id ?? ''));
        if ($idpUserId !== '') {
            $applicantByIdpUserId = $puptasService->fetchApplicantByIdpUserId($idpUserId);
            if (is_array($applicantByIdpUserId) && !empty($applicantByIdpUserId)) {
                return $applicantByIdpUserId;
            }
        }

        return null;
    }

    private function normalizePuptasApplicantIdentity(?array $applicantData): array
    {
        if (!is_array($applicantData) || empty($applicantData)) {
            return [
                'available' => false,
                'first_name' => '',
                'middle_name' => '',
                'last_name' => '',
                'full_name' => '',
                'reference_number' => '',
                'email' => '',
                'school_year' => '',
            ];
        }

        $firstName = trim((string) (
            data_get($applicantData, 'user.firstname')
            ?: data_get($applicantData, 'user.first_name')
            ?: data_get($applicantData, 'firstname')
            ?: data_get($applicantData, 'first_name')
            ?: data_get($applicantData, 'given_name')
        ));
        $middleName = trim((string) (
            data_get($applicantData, 'user.middlename')
            ?: data_get($applicantData, 'user.middle_name')
            ?: data_get($applicantData, 'middlename')
            ?: data_get($applicantData, 'middle_name')
        ));
        $lastName = trim((string) (
            data_get($applicantData, 'user.lastname')
            ?: data_get($applicantData, 'user.last_name')
            ?: data_get($applicantData, 'lastname')
            ?: data_get($applicantData, 'last_name')
            ?: data_get($applicantData, 'family_name')
            ?: data_get($applicantData, 'surname')
        ));
        $referenceNumber = trim((string) (
            data_get($applicantData, 'user.reference_number')
            ?: data_get($applicantData, 'reference_number')
            ?: data_get($applicantData, 'application.reference_number')
            ?: data_get($applicantData, 'admission.reference_number')
        ));
        $email = trim((string) (
            data_get($applicantData, 'user.email')
            ?: data_get($applicantData, 'email')
        ));
        $schoolYear = trim((string) (
            data_get($applicantData, 'user.school_year')
            ?: data_get($applicantData, 'user.schoolyear')
            ?: data_get($applicantData, 'school_year')
            ?: data_get($applicantData, 'schoolyear')
            ?: data_get($applicantData, 'academic_year')
        ));

        return [
            'available' => $firstName !== '' || $lastName !== '' || $referenceNumber !== '',
            'first_name' => $firstName,
            'middle_name' => $middleName,
            'last_name' => $lastName,
            'full_name' => trim(implode(' ', array_filter([$firstName, $middleName, $lastName]))),
            'reference_number' => $referenceNumber,
            'email' => $email,
            'school_year' => $schoolYear,
        ];
    }

    private function persistPuptasApplicantIdentity(User $user, array $identity): void
    {
        if (empty($identity['available'])) {
            return;
        }

        $firstName = trim((string) ($identity['first_name'] ?? ''));
        $middleName = trim((string) ($identity['middle_name'] ?? ''));
        $lastName = trim((string) ($identity['last_name'] ?? ''));
        $referenceNumber = trim((string) ($identity['reference_number'] ?? ''));
        $shouldSave = false;

        if ($firstName !== '' && trim((string) $user->first_name) !== $firstName) {
            $user->first_name = $firstName;
            $shouldSave = true;
        }

        // PUPTAS does not always provide a middle-name key. In that case the
        // health form must show N/A instead of reconstructing one from the IDP name.
        $resolvedMiddleName = $middleName !== '' ? $middleName : null;
        if (($user->middle_name ?: null) !== $resolvedMiddleName) {
            $user->middle_name = $resolvedMiddleName;
            $shouldSave = true;
        }

        if ($lastName !== '' && trim((string) $user->last_name) !== $lastName) {
            $user->last_name = $lastName;
            $shouldSave = true;
        }

        if (
            $referenceNumber !== ''
            && !$this->looksLikeIdpIdentifier($referenceNumber, $user)
            && \Schema::hasColumn('users', 'reference_number')
            && trim((string) ($user->reference_number ?? '')) !== $referenceNumber
        ) {
            $user->reference_number = $referenceNumber;
            $shouldSave = true;
        }

        if ($shouldSave) {
            $user->save();
        }
    }

    private function buildRecentFeedbackCollection()
    {
        return AppointmentFeedback::query()
            ->with(['user', 'appointment'])
            ->whereNotNull('submitted_at')
            ->latest('submitted_at')
            ->get()
            ->map(function (AppointmentFeedback $feedback) {
                $appointment = $feedback->appointment;
                $user = $feedback->user;

                return [
                    'name' => $this->formatFeedbackDisplayName($user, $appointment),
                    'role' => trim((string) ($appointment?->user_type ?? $user?->user_role ?? 'Student')),
                    'time' => optional($feedback->submitted_at)->diffForHumans() ?? 'Recently',
                    'message' => trim((string) $feedback->feedback) !== '' ? trim((string) $feedback->feedback) : 'Shared positive feedback about the clinic experience.',
                    'service' => trim((string) ($appointment?->service ?? 'Clinic Service')),
                ];
            });
    }

    public function home()
    {
        $allFeedback = $this->buildRecentFeedbackCollection();
        $feedbackCount = $allFeedback->count();
        $recentFeedback = $allFeedback->take(3);

        return view('student.home', compact('recentFeedback', 'feedbackCount'));
    }

    public function feedbackIndex()
    {
        $allFeedback = $this->buildRecentFeedbackCollection();

        return view('student.feedback-index', [
            'allFeedback' => $allFeedback,
            'feedbackCount' => $allFeedback->count(),
        ]);
    }

    private function looksLikeIdpIdentifier(?string $value, ?User $user = null): bool
    {
        $value = trim((string) $value);
        if ($value === '') {
            return false;
        }

        $userStudentId = trim((string) optional($user)->student_id);
        if ($userStudentId !== '' && strcasecmp($value, $userStudentId) === 0) {
            return true;
        }

        return (bool) preg_match(
            '/^[0-9a-f]{8}-[0-9a-f]{4}-[1-5][0-9a-f]{3}-[89ab][0-9a-f]{3}-[0-9a-f]{12}$/i',
            $value
        );
    }

    private function resolveStudentNumber(User $user, ?HealthProfile $healthProfile = null, ?array $applicantData = null): string
    {
        $candidates = [
            trim((string) data_get($applicantData, 'student_number')),
            trim((string) optional($healthProfile)->student_number),
            trim((string) ($user->student_number ?? '')),
        ];

        foreach ($candidates as $candidate) {
            if ($candidate === '' || $this->looksLikeIdpIdentifier($candidate, $user)) {
                continue;
            }

            return $candidate;
        }

        return '';
    }

    private function resolveReferenceNumber(User $user, ?HealthProfile $healthProfile = null, ?array $applicantData = null): string
    {
        $candidates = [
            trim((string) data_get($applicantData, 'user.reference_number')),
            trim((string) data_get($applicantData, 'reference_number')),
            trim((string) data_get($applicantData, 'application.reference_number')),
            trim((string) data_get($applicantData, 'admission.reference_number')),
            trim((string) optional($healthProfile)->reference_number),
            trim((string) ($user->reference_number ?? '')),
        ];

        foreach ($candidates as $candidate) {
            if ($candidate !== '' && !$this->looksLikeIdpIdentifier($candidate, $user)) {
                return $candidate;
            }
        }

        return '';
    }

    private function persistResolvedStudentNumber(User $user, ?HealthProfile $healthProfile, ?string $studentNumber): void
    {
        $studentNumber = trim((string) $studentNumber);
        if ($studentNumber === '' || $this->looksLikeIdpIdentifier($studentNumber, $user)) {
            return;
        }

        if (trim((string) $user->student_number) === '') {
            $user->student_number = $studentNumber;
            $user->save();
        }

        if ($healthProfile && trim((string) $healthProfile->student_number) === '') {
            $healthProfile->student_number = $studentNumber;
            $healthProfile->save();
        }
    }

    private function persistResolvedReferenceNumber(User $user, ?string $referenceNumber, ?HealthProfile $healthProfile = null): void
    {
        $referenceNumber = trim((string) $referenceNumber);
        if (
            $referenceNumber === ''
            || $this->looksLikeIdpIdentifier($referenceNumber, $user)
            || !\Schema::hasColumn('users', 'reference_number')
        ) {
            return;
        }

        if (trim((string) ($user->reference_number ?? '')) !== $referenceNumber) {
            $user->reference_number = $referenceNumber;
            $user->save();
        }

        if ($healthProfile && trim((string) ($healthProfile->reference_number ?? '')) !== $referenceNumber) {
            $healthProfile->reference_number = $referenceNumber;
            $healthProfile->save();
        }
    }

    private function persistResolvedUserProfileFields(User $user, array $prefill): void
    {
        $resolvedStudentNumber = trim((string) ($prefill['student_number'] ?? ''));
        $resolvedGender = trim((string) ($prefill['sex'] ?? ''));
        $resolvedCourse = trim((string) ($prefill['course_college'] ?? ''));

        $shouldSave = false;

        if ($resolvedStudentNumber !== '' && !$this->looksLikeIdpIdentifier($resolvedStudentNumber, $user) && trim((string) $user->student_number) === '') {
            $user->student_number = $resolvedStudentNumber;
            $shouldSave = true;
        }

        if ($resolvedGender !== '' && trim((string) $user->gender) === '') {
            $user->gender = $resolvedGender;
            $shouldSave = true;
        }

        if ($resolvedCourse !== '' && trim((string) $user->course) === '') {
            $user->course = $resolvedCourse;
            $shouldSave = true;
        }

        if ($shouldSave) {
            $user->save();
        }
    }

    private function normalizeSexValue(?string $value): string
    {
        $value = strtolower(trim((string) $value));

        return match ($value) {
            'male' => 'Male',
            'female' => 'Female',
            default => '',
        };
    }

    private function isPuptasApplicant(array $applicantData): bool
    {
        if (empty($applicantData)) {
            return false;
        }

        if (is_array(data_get($applicantData, 'application'))) {
            return true;
        }

        if (trim((string) data_get($applicantData, 'medical_process_status')) !== '') {
            return true;
        }

        return trim((string) data_get($applicantData, 'student_number')) === '';
    }

    private function resolveSchoolYear(?array $applicantData, User $user): string
    {
        $providedSchoolYear = trim((string) (
            data_get($applicantData, 'user.school_year')
            ?: data_get($applicantData, 'user.schoolyear')
            ?: data_get($applicantData, 'school_year')
            ?: data_get($applicantData, 'schoolyear')
            ?: data_get($applicantData, 'academic_year')
        ));

        if ($providedSchoolYear !== '') {
            return $providedSchoolYear;
        }

        $now = now();
        $calendarYear = (int) $now->format('Y');
        $academicStartMonth = 5;

        $startYear = ((int) $now->format('n')) >= $academicStartMonth
            ? $calendarYear
            : ($calendarYear - 1);

        return $startYear . '-' . ($startYear + 1);
    }

    private function normalizeMeasurement(?string $value, string $unit): ?string
    {
        $value = trim((string) $value);
        if ($value === '') {
            return null;
        }

        $normalizedUnit = strtolower(trim($unit));
        $normalizedValue = preg_replace('/\s+/', ' ', $value) ?? $value;

        if (!str_contains(strtolower($normalizedValue), $normalizedUnit)) {
            $normalizedValue .= ' ' . $normalizedUnit;
        }

        return $normalizedValue;
    }

    private function extractMeasurementNumber($value): ?string
    {
        $value = trim((string) $value);
        if ($value === '') {
            return null;
        }

        if (!preg_match('/\d+(?:\.\d+)?/', $value, $matches)) {
            return null;
        }

        $number = $matches[0];
        if (str_contains($number, '.')) {
            $number = rtrim(rtrim($number, '0'), '.');
        }

        return $number === '' ? null : $number;
    }

    private function normalizeDoctorName(?string $value): string
    {
        $value = trim((string) $value);
        if ($value === '') {
            return '';
        }

        $value = preg_replace('/^(dr\.?\s*)+/i', '', $value) ?? $value;

        return 'Dr. ' . trim($value);
    }

    private function buildHealthFormPrefill(User $user, ?Admin $linkedAdminProfile = null, ?HealthProfile $healthProfile = null): array
    {
        $linkedAdminProfile = $linkedAdminProfile ?: $this->resolveLinkedAdminProfile($user);
        $applicantData = $this->fetchPuptasApplicantForUser($user);
        $applicantIdentity = $this->normalizePuptasApplicantIdentity($applicantData);
        $this->persistPuptasApplicantIdentity($user, $applicantIdentity);

        $calculatedAge = null;
        if (!empty($user->DOB)) {
            try {
                $calculatedAge = \Carbon\Carbon::parse($user->DOB)->age;
            } catch (\Throwable $exception) {
                $calculatedAge = null;
            }
        }

        $resolvedSex = $this->normalizeSexValue(
            data_get($applicantData, 'sex')
            ?: (optional($healthProfile)->sex ?? $user->gender ?? optional($linkedAdminProfile)->gender ?? '')
        );

        $resolvedCivilStatus = trim((string) (optional($healthProfile)->civil_status ?? optional($linkedAdminProfile)->civil_status ?? ''));
        $resolvedCivilStatus = in_array($resolvedCivilStatus, ['Single', 'Married'], true) ? $resolvedCivilStatus : 'Single';

        $resolvedBirthday = (string) (
            optional($healthProfile)->birthday
            ?: $user->DOB
            ?: optional($linkedAdminProfile)->birthday
            ?: data_get($applicantData, 'birthday')
            ?: ''
        );

        if ($resolvedBirthday !== '') {
            try {
                $resolvedBirthday = \Carbon\Carbon::parse($resolvedBirthday)->format('Y-m-d');
            } catch (\Throwable $exception) {
                $resolvedBirthday = '';
            }
        }

        $resolvedAge = optional($healthProfile)->age ?? $calculatedAge;
        if ($resolvedBirthday !== '') {
            try {
                $resolvedAge = \Carbon\Carbon::parse($resolvedBirthday)->age;
            } catch (\Throwable $exception) {
                // keep existing resolved age
            }
        }

        $resolvedAddress = trim(implode(', ', array_filter([
            data_get($applicantData, 'street_address'),
            data_get($applicantData, 'barangay'),
            data_get($applicantData, 'city'),
            data_get($applicantData, 'province'),
        ])));

        $applicantFirstName = trim((string) $applicantIdentity['first_name']);
        $applicantMiddleName = trim((string) $applicantIdentity['middle_name']);
        $applicantLastName = trim((string) $applicantIdentity['last_name']);
        $applicantStructuredName = trim((string) $applicantIdentity['full_name']);
        $hasOfficialApplicantIdentity = (bool) $applicantIdentity['available'];

        return [
            'identity_from_puptas' => $hasOfficialApplicantIdentity,
            'puptas_full_name' => $applicantStructuredName,
            'puptas_first_name' => $applicantFirstName,
            'puptas_middle_name' => $applicantMiddleName,
            'puptas_last_name' => $applicantLastName,
            'full_name' => $applicantStructuredName
                ?: trim((string) (data_get($applicantData, 'full_name') ?: data_get($applicantData, 'name') ?: $user->name)),
            'first_name' => $applicantFirstName
                ?: trim((string) (optional($linkedAdminProfile)->first_name ?? $user->first_name ?? '')),
            'middle_name' => $hasOfficialApplicantIdentity
                ? $applicantMiddleName
                : trim((string) ($user->middle_name ?? optional($linkedAdminProfile)->middle_name ?? '')),
            'last_name' => $applicantLastName
                ?: trim((string) (optional($linkedAdminProfile)->last_name ?? $user->last_name ?? '')),
            'suffix_name' => trim((string) (optional($linkedAdminProfile)->suffix_name ?? '')),
            'student_id' => (string) (optional($healthProfile)->student_id ?? $user->student_id ?? ''),
            'reference_number' => $this->resolveReferenceNumber($user, $healthProfile, $applicantData),
            'student_number' => $this->resolveStudentNumber($user, $healthProfile, $applicantData),
            'school_year_from_puptas' => trim((string) $applicantIdentity['school_year']) !== '',
            'email' => (string) (
                $applicantIdentity['email']
                ?: optional(optional($healthProfile)->user)->email
                ?: ($user->email ?? optional($linkedAdminProfile)->email ?? '')
            ),
            'course_college' => trim((string) (
                optional($healthProfile)->course_college
                ?? trim(implode(' - ', array_filter([
                    data_get($applicantData, 'program.code'),
                    data_get($applicantData, 'program.name'),
                ])))
                ?: trim(implode(' - ', array_filter([
                    data_get($applicantData, 'strand'),
                    data_get($applicantData, 'track'),
                ])))
                ?: ($user->course ?? '')
            )),
            'home_address' => trim((string) (
                optional($healthProfile)->home_address
                ?: ($resolvedAddress !== '' ? $resolvedAddress : trim((string) (optional($linkedAdminProfile)->address ?? '')))
            )),
            'zipcode' => trim((string) (optional($healthProfile)->zipcode ?? data_get($applicantData, 'postal_code') ?? '')),
            'school_year' => (string) (optional($healthProfile)->school_year ?? $this->resolveSchoolYear($applicantData, $user)),
            'height' => (string) (optional($healthProfile)->height ?? $user->height ?? ''),
            'weight' => (string) (optional($healthProfile)->weight ?? $user->weight ?? ''),
            'birthday' => $resolvedBirthday,
            'age' => $resolvedAge,
            'sex' => $resolvedSex,
            'civil_status' => $resolvedCivilStatus,
            'blood_type' => (string) (optional($healthProfile)->blood_type ?? 'Not Known'),
            'contact_number' => trim((string) ($user->contact_no ?? data_get($applicantData, 'contactnumber') ?? '')),
            'guardian_name' => trim((string) (optional($healthProfile)->guardian_name ?? optional($linkedAdminProfile)->emergency_contact_person ?? '')),
            'cellphone' => trim((string) (
                optional($healthProfile)->cellphone
                ?: (optional($linkedAdminProfile)->emergency_contact_no ?? '')
            )),
            'landline' => (string) (optional($healthProfile)->landline ?? ''),
            'office' => trim((string) (optional($linkedAdminProfile)->office ?? '')),
            'access_level' => trim((string) (optional($linkedAdminProfile)->access_level ?? '')),
        ];
    }

    private function unwrapGuisisPayload($payload): array
    {
        if (!is_array($payload)) {
            return [];
        }

        foreach (['data.student', 'data.profile', 'data.personalInfo', 'data.personal_info', 'data', 'student', 'profile'] as $path) {
            $candidate = data_get($payload, $path);
            if (is_array($candidate) && !array_is_list($candidate)) {
                return $candidate;
            }
        }

        return array_is_list($payload) ? [] : $payload;
    }

    private function firstGuisisValue(array $sources, array $paths): string
    {
        foreach ($sources as $source) {
            if (!is_array($source)) {
                continue;
            }

            foreach ($paths as $path) {
                $value = data_get($source, $path);
                if (is_scalar($value) && trim((string) $value) !== '') {
                    return trim((string) $value);
                }
            }
        }

        return '';
    }

    private function buildGuisisAddress(array $sources): string
    {
        $directAddress = $this->firstGuisisValue($sources, [
            'address',
            'home_address',
            'homeAddress',
            'current_address',
            'currentAddress',
            'permanent_address',
            'permanentAddress',
            'full_address',
            'fullAddress',
        ]);

        if ($directAddress !== '') {
            return $directAddress;
        }

        $parts = [];
        foreach ([
            ['house_number', 'houseNumber', 'street_address', 'streetAddress', 'street'],
            ['barangay', 'brgy'],
            ['city', 'municipality'],
            ['province'],
            ['postal_code', 'postalCode', 'zip_code', 'zipCode'],
        ] as $aliases) {
            $part = $this->firstGuisisValue($sources, $aliases);
            if ($part !== '') {
                $parts[] = $part;
            }
        }

        return implode(', ', array_unique($parts));
    }

    private function buildGuisisAccountData(User $user): array
    {
        $email = trim((string) $user->email);
        if ($email === '') {
            return ['available' => false, 'status' => 'missing_email'];
        }

        try {
            $service = app(GuisisApiService::class);
            $emailResult = $service->getStudentByEmailDetailed($email);
            if (!($emailResult['ok'] ?? false)) {
                Log::warning('GUISIS My Account email lookup failed', [
                    'user_id' => $user->id,
                    'status' => $emailResult['status'] ?? null,
                    'message' => $emailResult['message'] ?? null,
                ]);

                return [
                    'available' => false,
                    'status' => 'email_lookup_failed',
                    'message' => $emailResult['message'] ?? 'GUISIS record is currently unavailable.',
                ];
            }

            $emailProfile = $this->unwrapGuisisPayload($emailResult['data'] ?? []);
            $studentNumber = $this->firstGuisisValue([$emailProfile], [
                'studentNumber',
                'student_number',
                'student_no',
                'studentNo',
                'student_id',
                'studentId',
            ]);

            $studentProfile = [];
            $personalInfo = [];
            if ($studentNumber !== '') {
                $studentResult = $service->getStudentByStudentNumberDetailed($studentNumber);
                if ($studentResult['ok'] ?? false) {
                    $studentProfile = $this->unwrapGuisisPayload($studentResult['data'] ?? []);
                }

                $personalResult = $service->getStudentPersonalInfoDetailed($studentNumber);
                if ($personalResult['ok'] ?? false) {
                    $personalInfo = $this->unwrapGuisisPayload($personalResult['data'] ?? []);
                }
            }

            $sources = [$personalInfo, $studentProfile, $emailProfile];
            $firstName = $this->firstGuisisValue($sources, ['first_name', 'firstName', 'firstname', 'given_name', 'givenName']);
            $middleName = $this->firstGuisisValue($sources, [
                'middleName.string',
                'middle_name',
                'middleName',
                'middlename',
            ]);
            $lastName = $this->firstGuisisValue($sources, ['last_name', 'lastName', 'lastname', 'surname']);
            $suffixName = $this->firstGuisisValue($sources, ['suffix', 'suffix_name', 'suffixName', 'name_suffix']);
            $fullName = $this->firstGuisisValue($sources, ['full_name', 'fullName', 'name']);

            if ($fullName === '') {
                $fullName = trim(implode(' ', array_filter([$firstName, $middleName, $lastName, $suffixName])));
            }

            $courseCode = $this->firstGuisisValue($sources, [
                'course.code',
                'program.code',
                'program_code',
                'programCode',
                'course_code',
                'courseCode',
            ]);
            $courseName = $this->firstGuisisValue($sources, [
                'course.name',
                'program.name',
                'program_name',
                'programName',
                'course_name',
                'courseName',
                'program',
                'course',
            ]);
            $courseCollege = trim(implode(' - ', array_unique(array_filter([$courseCode, $courseName]))));

            $birthday = $this->firstGuisisValue($sources, [
                'dateOfBirth',
                'date_of_birth',
                'birthDate',
                'birth_date',
                'birthday',
                'dob',
            ]);
            if ($birthday !== '') {
                try {
                    $birthday = Carbon::parse($birthday)->format('Y-m-d');
                } catch (\Throwable $exception) {
                    // Keep the source value when GUISIS uses a non-standard date format.
                }
            }
            $age = '';
            if ($birthday !== '') {
                try {
                    $age = (string) Carbon::parse($birthday)->age;
                } catch (\Throwable $exception) {
                    // Leave age blank when the source birthday cannot be parsed.
                }
            }

            $resolvedStudentNumber = $studentNumber !== ''
                ? $studentNumber
                : $this->firstGuisisValue($sources, ['studentNumber', 'student_number', 'studentNo', 'student_no']);

            $data = [
                'available' => true,
                'status' => 'synced',
                'student_number' => $resolvedStudentNumber,
                'first_name' => $firstName,
                'middle_name' => $middleName,
                'last_name' => $lastName,
                'suffix_name' => $suffixName,
                'full_name' => $fullName,
                'email' => $this->firstGuisisValue($sources, ['email', 'email_address', 'emailAddress']) ?: $email,
                'course_college' => $courseCollege,
                'year' => $this->firstGuisisValue($sources, ['yearLevel', 'year_level', 'year', 'level']),
                'section' => $this->firstGuisisValue($sources, ['section', 'section_name', 'sectionName']),
                'sex' => $this->normalizeSexValue($this->firstGuisisValue($sources, [
                    'gender.name',
                    'genderName',
                    'sex',
                    'gender',
                ])),
                'birthday' => $birthday,
                'age' => $age,
                'civil_status' => $this->firstGuisisValue($sources, ['civil_status', 'civilStatus', 'marital_status', 'maritalStatus']),
                'contact_number' => $this->firstGuisisValue($sources, [
                    'contact_number',
                    'contactNumber',
                    'mobile_number',
                    'mobileNumber',
                    'phone_number',
                    'phoneNumber',
                    'cellphone',
                ]),
                'home_address' => $this->buildGuisisAddress($sources),
                'guardian_name' => $this->firstGuisisValue($sources, [
                    'guardian_name',
                    'guardianName',
                    'emergency_contact_name',
                    'emergencyContactName',
                    'parent_guardian_name',
                    'parentGuardianName',
                ]),
                'cellphone' => $this->firstGuisisValue($sources, [
                    'guardian_contact_number',
                    'guardianContactNumber',
                    'emergency_contact_number',
                    'emergencyContactNumber',
                ]),
            ];

            $shouldSave = false;
            foreach ([
                'student_number' => 'student_number',
                'course_college' => 'course',
                'year' => 'year',
                'section' => 'section',
                'sex' => 'gender',
                'birthday' => 'DOB',
                'contact_number' => 'contact_no',
            ] as $sourceKey => $userColumn) {
                if (($data[$sourceKey] ?? '') !== '' && trim((string) ($user->{$userColumn} ?? '')) === '') {
                    $user->{$userColumn} = $data[$sourceKey];
                    $shouldSave = true;
                }
            }

            if ($shouldSave) {
                $user->save();
            }

            return $data;
        } catch (\Throwable $exception) {
            Log::warning('GUISIS My Account synchronization failed', [
                'user_id' => $user->id,
                'error' => $exception->getMessage(),
            ]);

            return [
                'available' => false,
                'status' => 'request_failed',
                'message' => 'GUISIS record is currently unavailable.',
            ];
        }
    }

    private function hasSubmittedHealthProfile(?User $user): bool
    {
        if (!$user) {
            return false;
        }

        if ($user->relationLoaded('healthProfile')) {
            return $user->healthProfile !== null;
        }

        return HealthProfile::query()->where('user_id', $user->id)->exists();
    }

    private function resolveStudentContext(?User $user): array
    {
        if (!$user) {
            return [
                'student_id' => '',
                'student_number' => '',
            ];
        }

        $user->loadMissing('healthProfile');
        $linkedAdminProfile = $this->resolveLinkedAdminProfile($user);
        $prefill = $this->buildHealthFormPrefill($user, $linkedAdminProfile, $user->healthProfile);

        return [
            'student_id' => trim((string) ($prefill['student_id'] ?? $user->student_id ?? '')),
            'student_number' => trim((string) ($prefill['student_number'] ?? $user->student_number ?? '')),
        ];
    }

    private function resolveLinkedAdminProfile(?User $user): ?Admin
    {
        if (!$user || !Admin::hasColumn('email')) {
            return null;
        }

        $email = trim(strtolower((string) $user->email));
        if ($email === '') {
            return null;
        }

        return Admin::query()
            ->whereRaw('LOWER(email) = ?', [$email])
            ->first();
    }

    private function normalizeBarcodeValue(?string $value): string
    {
        return strtoupper((string) preg_replace('/[^A-Za-z0-9]/', '', $value ?? ''));
    }

    private function getBarcodeMismatchMessage(User $user, string $barcode): ?string
    {
        $studentId = trim((string) $user->student_id);
        if ($studentId === '') {
            return null;
        }

        $normalizedStudentId = $this->normalizeBarcodeValue($studentId);
        $normalizedBarcode = $this->normalizeBarcodeValue($barcode);

        if ($normalizedStudentId !== '' && $normalizedBarcode !== '' && $normalizedStudentId !== $normalizedBarcode) {
            return 'The scanned barcode does not match your Student ID.';
        }

        return null;
    }

    // -------------------------------
    // 1. STUDENT DASHBOARD
    // -------------------------------
    public function index()
    {
        Appointment::expireOverduePending();

        $user = Auth::user() ?? User::where('email', 'guest@pup.edu.ph')->first();

        if (!$user) {
            $user = User::create([
                'first_name' => 'Guest',
                'last_name' => 'Student',
                'name' => 'Guest Student',
                'email' => 'guest@pup.edu.ph',
                'password' => bcrypt('password'),
                'is_admin' => 0,
            ]);
        }

        $appointments = Appointment::where('user_id', $user->id)->get();
        $upcoming = $appointments->where('status', 'Approved');
        $pending = $appointments->where('status', 'Pending');
        $history = $appointments->whereIn('status', ['Completed', 'Cancelled', 'Expired']);

        return view('student.home', compact('upcoming', 'pending', 'history'));
    }

    // -------------------------------
    // 2. BOOKING FORM
    // -------------------------------
    public function create()
    {
        Appointment::expireOverduePending();

        /** @var \App\Models\User|null $user */
        $user = Auth::guard('student')->user();
        if (!$user) {
            return view('student.booking-public');
        }

        $appointments = Appointment::where('user_id', $user->id)
                                   ->whereIn('status', ['Pending', 'Approved'])
                                   ->orderBy('date', 'asc')
                                   ->get();

        $studentContext = $this->resolveStudentContext($user);

        $clinicClosure = app(ClinicWorkflowService::class)->activeClosure();

        return view('student.booking', compact('user', 'appointments', 'studentContext', 'clinicClosure'));
    }

    // -------------------------------
    // 3. STORE APPOINTMENT
    // -------------------------------
    public function store(Request $request)
    {
        $workflow = app(ClinicWorkflowService::class);
        $closure = $workflow->activeClosure();
        if ($closure) {
            return redirect()->back()->withInput()->with(
                'error',
                'New appointment booking is temporarily unavailable. ' . $closure['message']
            );
        }

        $request->validate([
            'date' => 'required|date',
            'time' => 'required',
            'service' => 'required',
            'remarks' => 'nullable|string',
        ]);

        $selectedDate = Carbon::parse($request->date)->startOfDay();
        $today = Carbon::today();
        if ($selectedDate->lt($today)) {
            return redirect()->back()->withInput()->with('error', 'Past dates are not available.');
        }

        if ($selectedDate->isWeekend()) {
            return redirect()->back()->withInput()->with('error', 'Appointments are available from Monday to Friday only.');
        }

        $selectedDateTime = Carbon::parse($request->date . ' ' . $request->time);
        if ($selectedDateTime->lt(Carbon::now())) {
            return redirect()->back()->withInput()->with('error', 'Please choose a future appointment time.');
        }

        // Prevent overlapping appointments
        $exists = Appointment::where('date', $request->date)
                             ->where('time', $request->time)
                             ->where('status', '!=', 'Cancelled')
                             ->exists();

        if ($exists) {
            return redirect()->back()->withInput()->with('error', 'That time slot is already taken.');
        }

        // Max 10 appointments per day
        $dailyCount = Appointment::where('date', $request->date)
                                 ->where('status', '!=', 'Cancelled')
                                 ->count();
        if ($dailyCount >= 10) {
            return redirect()->back()->withInput()->with('error', 'Fully booked for this date.');
        }

        // Get or create user
        $user = Auth::user() ?? User::firstOrCreate(
            ['email' => 'guest@pup.edu.ph'],
            [
                'first_name' => 'Guest',
                'last_name' => 'Student',
                'name' => 'Guest Student',
                'password' => bcrypt('password'),
                'is_admin' => 0
            ]
        );

        // Create appointment for student side
  

        $studentContext = $this->resolveStudentContext($user);

        $appointment = new Appointment();
        $appointment->user_id = $user->id;
        $appointment->student_id = $request->input('student_id', $studentContext['student_id'] ?: '2025-0000-TG-0');
        $appointment->student_number = $request->input('student_number', $studentContext['student_number']);
        $appointment->name = $user->name;
        $appointment->email = $user->email;
        $appointment->date = $request->date;
        $appointment->time = $request->time;
        $appointment->service = $request->service;
        $appointment->status = $workflow->settings()->auto_approve ? 'Approved' : 'Pending';
        $appointment->remarks = $request->remarks; 
        $appointment->type = 'online';
        $appointment->user_type = Appointment::normalizeUserType($user->user_role);
        $appointment->save(); // Dito lang dapat magtatapos ang command.

        $successMessage = $appointment->status === 'Approved'
            ? 'Appointment booked and approved automatically.'
            : 'Appointment request submitted! Please wait for admin approval.';

        return redirect()->back()
            ->with('success', $successMessage)
            ->with('appointment_confirmation', [
                'service' => $appointment->service,
                'date' => Carbon::parse($appointment->date)->format('M d, Y'),
                'time' => Carbon::parse($appointment->time)->format('g:i A'),
                'status' => $appointment->status,
            ]);
    }

    public function availability(Request $request)
    {
        $closure = app(ClinicWorkflowService::class)->activeClosure();
        if ($closure) {
            return response()->json([
                'date' => (string) $request->query('date', ''),
                'available' => false,
                'reason' => 'clinic_temporarily_closed',
                'message' => $closure['message'],
                'slots' => [],
            ], 423);
        }

        $request->validate([
            'date' => 'required|date_format:Y-m-d',
        ]);

        $selectedDate = Carbon::parse($request->date)->startOfDay();
        $today = Carbon::today();

        if ($selectedDate->lt($today)) {
            return response()->json([
                'date' => $selectedDate->toDateString(),
                'available' => false,
                'reason' => 'past_date',
                'message' => 'Past dates are not available.',
                'slots' => [],
            ]);
        }

        if ($selectedDate->isWeekend()) {
            return response()->json([
                'date' => $selectedDate->toDateString(),
                'available' => false,
                'reason' => 'weekend',
                'message' => 'Appointments are available from Monday to Friday only.',
                'slots' => [],
            ]);
        }

        $takenTimes = Appointment::whereDate('date', $selectedDate->toDateString())
            ->where('status', '!=', 'Cancelled')
            ->pluck('time')
            ->map(function ($time) {
                return Carbon::parse($time)->format('H:i');
            })
            ->values()
            ->all();

        $dailyBookedCount = Appointment::whereDate('date', $selectedDate->toDateString())
            ->where('status', '!=', 'Cancelled')
            ->count();

        if ($dailyBookedCount >= 10) {
            return response()->json([
                'date' => $selectedDate->toDateString(),
                'available' => false,
                'reason' => 'fully_booked',
                'message' => 'This date is fully booked.',
                'slots' => [],
            ]);
        }

        $takenLookup = array_fill_keys($takenTimes, true);
        $slots = [];
        $now = Carbon::now();
        $dailyCount = 0;

        for ($hour = 8; $hour <= 19; $hour++) {
            foreach ([0, 30] as $minute) {
                if ($hour === 19 && $minute > 0) {
                    continue;
                }

                $slotTime = Carbon::createFromTime($hour, $minute, 0);
                $slotValue = $slotTime->format('H:i');
                $slotDateTime = Carbon::parse($selectedDate->toDateString() . ' ' . $slotValue);
                $isTaken = isset($takenLookup[$slotValue]);
                $isPastTime = $slotDateTime->lt($now);
                $isAvailable = !$isTaken && !$isPastTime;

                if ($isAvailable) {
                    $dailyCount++;
                }

                $slots[] = [
                    'value' => $slotValue,
                    'label' => $slotTime->format('g:i A'),
                    'available' => $isAvailable,
                ];
            }
        }

        return response()->json([
            'date' => $selectedDate->toDateString(),
            'available' => $dailyCount > 0,
            'reason' => $dailyCount > 0 ? null : 'fully_booked',
            'message' => $dailyCount > 0 ? null : 'No available time slots for this date.',
            'slots' => $slots,
        ]);
    }
    // -------------------------------
    // 4. STUDENT ACCOUNT
    // -------------------------------
public function account(Request $request)
{
    Appointment::expireOverduePending();

    // 1. Kunin ang logged-in user. Kung walang session, redirect sa login page.
    $user = Auth::user();

    if (!$user) {
        // Imbis na gumawa ng guest, force login natin para stable ang testing
        return redirect('/login-as-student')->with('error', 'Please login first.');
    }

    $user->load('healthProfile');

    // 2. Kunin ang appointments ng SPECIFIC user na naka-login
    $appointments = Appointment::where('user_id', $user->id)
                                ->orderBy('updated_at', 'desc')
                                ->get();

    // 3. Stats calculation
    $pendingCount   = $appointments->where('status', 'Pending')->count();
    $approvedCount  = $appointments->where('status', 'Approved')->count();
    $completedCount = $appointments->where('status', 'Completed')->count();
    $cancelledCount = $appointments->where('status', 'Cancelled')->count();

    // 4. Notification Logic
    $notifications = collect($this->getStudentNotifications($user));
    $hasSubmittedHealthProfile = $this->hasSubmittedHealthProfile($user);

    // 5. Return view user
    $linkedAdminProfile = $this->resolveLinkedAdminProfile($user);
    $accountProfileData = $this->buildHealthFormPrefill($user, $linkedAdminProfile, $user->healthProfile);

    // These fields belong to the submitted clinic Health Profile for now.
    $profileBirthday = trim((string) (optional($user->healthProfile)->birthday ?: $user->DOB));
    $profileAge = optional($user->healthProfile)->age;
    if ($profileAge === null && $profileBirthday !== '') {
        try {
            $profileAge = Carbon::parse($profileBirthday)->age;
        } catch (\Throwable $exception) {
            $profileAge = null;
        }
    }
    $accountProfileData['birthday'] = $profileBirthday;
    $accountProfileData['age'] = $profileAge;
    $accountProfileData['sex'] = trim((string) (optional($user->healthProfile)->sex ?: $user->gender));
    $accountProfileData['civil_status'] = trim((string) optional($user->healthProfile)->civil_status);
    $accountProfileData['home_address'] = trim((string) optional($user->healthProfile)->home_address);
    $accountProfileData['guardian_name'] = trim((string) optional($user->healthProfile)->guardian_name);
    $accountProfileData['cellphone'] = trim((string) optional($user->healthProfile)->cellphone);

    $guisisAccountData = $this->buildGuisisAccountData($user);
    if ($guisisAccountData['available'] ?? false) {
        foreach ([
            'student_number',
            'first_name',
            'middle_name',
            'last_name',
            'suffix_name',
            'full_name',
            'email',
            'course_college',
            'year',
            'section',
            'contact_number',
        ] as $key) {
            // GUISIS is authoritative for account information. Assign blank
            // values too, so stale clinic-form data is not shown as GUISIS data.
            $accountProfileData[$key] = trim((string) ($guisisAccountData[$key] ?? ''));
        }
    }

    $isEnrolled = (bool) ($guisisAccountData['available'] ?? false)
        || trim((string) ($accountProfileData['student_number'] ?? '')) !== ''
        || (bool) $user->is_health_profile_completed;
    $accountView = in_array((string) $request->query('view', 'profile'), ['profile', 'health-record', 'notifications'], true)
        ? (string) $request->query('view', 'profile')
        : 'profile';

    return view('student.account', compact(
        'user', 
        'appointments', 
        'pendingCount', 
        'approvedCount', 
        'completedCount', 
        'cancelledCount', 
        'notifications',
        'linkedAdminProfile',
        'hasSubmittedHealthProfile',
        'accountProfileData',
        'guisisAccountData',
        'isEnrolled',
        'accountView'
    ));
}

    public function openNotification(string $notificationId)
    {
        /** @var \App\Models\User|null $user */
        $user = Auth::user();
        if (!$user) {
            return redirect('/login-as-student')->with('error', 'Please login first.');
        }

        $notification = collect($this->getStudentNotifications($user))
            ->firstWhere('id', $notificationId);

        if (!$notification) {
            return redirect('/student/account')->with('error', 'Notification not found.');
        }

        $this->markNotificationAsRead($user, $notificationId);

        return redirect($notification['link'] ?? '/student/account');
    }

    public function markAllNotificationsRead()
    {
        /** @var \App\Models\User|null $user */
        $user = Auth::user();
        if (!$user) {
            return redirect('/login-as-student')->with('error', 'Please login first.');
        }

        $readMap = $this->getNotificationReadMap($user);
        foreach ($this->getStudentNotifications($user) as $notification) {
            $readMap[$notification['id']] = now()->toIso8601String();
        }

        $user->notification_read_map = $readMap;
        $user->save();

        return back()->with('success', 'All notifications marked as read.');
    }

    public function showFeedbackForm(Appointment $appointment)
    {
        /** @var \App\Models\User|null $user */
        $user = Auth::user();
        if (!$user || $appointment->user_id !== $user->id) {
            return redirect('/student/history')->with('error', 'Feedback form not available for this appointment.');
        }

        if ($appointment->status !== 'Completed') {
            return redirect('/student/history')->with('error', 'You can only send feedback for completed appointments.');
        }

        $appointment->load('feedback');

        return view('student.feedback', [
            'appointment' => $appointment,
            'existingFeedback' => $appointment->feedback,
        ]);
    }

    public function storeFeedback(Request $request, Appointment $appointment)
    {
        /** @var \App\Models\User|null $user */
        $user = Auth::user();
        if (!$user || $appointment->user_id !== $user->id) {
            return redirect('/student/history')->with('error', 'Feedback form not available for this appointment.');
        }

        if ($appointment->status !== 'Completed') {
            return redirect('/student/history')->with('error', 'You can only send feedback for completed appointments.');
        }

        if ($appointment->feedback && $appointment->feedback->submitted_at) {
            return redirect()
                ->route('student.feedback.show', ['appointment' => $appointment->id])
                ->with('success', 'Your feedback has already been submitted and is now view-only.');
        }

        $validated = $request->validate([
            'rating' => ['required', 'integer', 'between:1,5'],
            'feedback' => ['nullable', 'string', 'max:2000'],
        ]);

        AppointmentFeedback::create([
            'appointment_id' => $appointment->id,
            'user_id' => $user->id,
            'rating' => $validated['rating'],
            'feedback' => trim((string) ($validated['feedback'] ?? '')),
            'submitted_at' => now(),
        ]);

        \App\Models\ActivityLog::create([
            'user_id'     => $user->id,
            'user_name'   => $user->name,
            'action'      => 'Appointment Feedback Submitted',
            'description' => "Submitted feedback for Appointment #{$appointment->id}.",
            'ip_address'  => $request->ip(),
            'user_agent'  => $request->userAgent(),
        ]);

        return redirect('/student/account?view=notifications')->with('success', 'Thank you for sharing your feedback.');
    }


    // -------------------------------
    // 5. CANCEL APPOINTMENT
    // -------------------------------
    public function cancel($id)
    {
        $user = Auth::user() ?? User::where('email', 'guest@pup.edu.ph')->first();
        $appointment = Appointment::where('id', $id)->where('user_id', $user->id)->first();

        if ($appointment) {
            $appointment->status = 'Cancelled';
            $appointment->save();
            return redirect()->back()->with('success', 'Appointment cancelled.');
        }

        return redirect()->back()->with('error', 'Appointment not found.');
    }

    // -------------------------------
    // 6. FAQ PAGE
    // -------------------------------
    public function faq()
    {
        Appointment::expireOverduePending();

        /** @var \App\Models\User|null $user */
        $user = Auth::guard('student')->user();
        $pendingCount = 0;
        $upcomingCount = 0;
        $completedCount = 0;
        $cancelledCount = 0;

        if ($user) {
            $appointments = Appointment::where('user_id', $user->id)->get();
            $pendingCount = $appointments->where('status', 'Pending')->count();
            $upcomingCount = $appointments->where('status', 'Approved')->count();
            $completedCount = $appointments->where('status', 'Completed')->count();
            $cancelledCount = $appointments->where('status', 'Cancelled')->count();
        }

        return view('student.faq', compact('user', 'pendingCount', 'upcomingCount', 'completedCount', 'cancelledCount'));
    }

    //-------------------------------
    // 7. UPDATE CONTACT
    //-------------------------------
public function updateContact(Request $request)
{
    // 1. Kunin ang user
    $user = Auth::user() ?? User::where('email', 'guest@pup.edu.ph')->first();

    if (!$user) {
        return redirect()->back()->with('error', 'User session not found.');
    }

    // 2. GUISIS owns student profile details. Clinic profile edits only allow height and weight.
    $validated = $request->validate([
        'height'     => ['nullable', 'string', 'max:20', 'regex:/^\s*\d+(\.\d+)?(\s*cm)?\s*$/i'],
        'weight'     => ['nullable', 'string', 'max:20', 'regex:/^\s*\d+(\.\d+)?(\s*kg)?\s*$/i'],
    ], [
        'height.regex' => 'Height must be a valid number (optional unit: cm).',
        'weight.regex' => 'Weight must be a valid number (optional unit: kg).',
    ]);

    $heightNumeric = $this->extractMeasurementNumber($validated['height'] ?? null);
    $weightNumeric = $this->extractMeasurementNumber($validated['weight'] ?? null);
    $normalizedHeight = $heightNumeric !== null ? $this->normalizeMeasurement($heightNumeric, 'cm') : null;
    $normalizedWeight = $weightNumeric !== null ? $this->normalizeMeasurement($weightNumeric, 'kg') : null;

    // 3. Save only clinic-controlled measurements.
    $user->height = $heightNumeric ?? $user->height;
    $user->weight = $weightNumeric ?? $user->weight;
    $user->save();

    $healthProfile = $user->healthProfile()->first();
    if ($healthProfile) {
        if ($normalizedHeight !== null) {
            $healthProfile->height = $normalizedHeight;
        }
        if ($normalizedWeight !== null) {
            $healthProfile->weight = $normalizedWeight;
        }
        $healthProfile->save();
    }

    // 5. SYSTEM LOG ---
    \App\Models\ActivityLog::create([
        'user_id'     => $user->id,
        'user_name'   => $user->name,
        'action'      => 'Profile Update',
        'description' => 'Updated clinic profile measurements: height and weight.',
        'ip_address'  => $request->ip(),
        'user_agent'  => $request->userAgent(),
    ]);

    return redirect()->back()->with('success', 'Profile details updated successfully.');
}

    // -------------------------------
    // 8. APPOINTMENT HISTORY
    // -------------------------------
    public function history()
    {
        Appointment::expireOverduePending();

        $user = Auth::user() ?? User::where('email', 'guest@pup.edu.ph')->first();

        if (!$user) {
            $user = User::create([
                'first_name' => 'Guest',
                'last_name' => 'Student',
                'name' => 'Guest Student',
                'email' => 'guest@pup.edu.ph',
                'password' => bcrypt('password'),
                'is_admin' => 0,
            ]);
        }

        $appointments = Appointment::where('user_id', $user->id)
                                   ->orderBy('date', 'desc')
                                   ->orderBy('time', 'desc')
                                   ->get();

        $studentContext = $this->resolveStudentContext($user);

        return view('student.history', compact('appointments', 'studentContext'));
    }

    // -------------------------------
    // 9. BARCODE REGISTER PAGE
    // -------------------------------
    public function barcodeRegister()
    {
        $user = Auth::user() ?? User::where('email', 'guest@pup.edu.ph')->first();
        $studentContext = $this->resolveStudentContext($user);

        return view('student.barcode-register', compact('user', 'studentContext'));
    }

    // -------------------------------
    // SAVE BARCODE
    // -------------------------------
    public function storeBarcode(Request $request)
    {
        $user = Auth::user() ?? User::where('email', 'guest@pup.edu.ph')->first();

        if (!$user) {
            return redirect()->back()->with('error', 'User not found!');
        }

        $request->validate([
            'barcode' => 'required|string|max:255'
        ]);

        $barcode = trim((string) $request->barcode);
        $mismatchMessage = $this->getBarcodeMismatchMessage($user, $barcode);
        if ($mismatchMessage) {
            return redirect()->back()->withInput()->withErrors([
                'barcode' => $mismatchMessage,
            ]);
        }

        $request->merge(['barcode' => $barcode]);
        $request->validate([
            'barcode' => 'required|string|max:255|unique:users,barcode,' . $user->id
        ]);

        $user->barcode = $barcode;
        $user->save();

        return redirect()->back()->with('success', 'Barcode registered successfully!');
    }

    public function validateBarcodeScan(Request $request)
    {
        $user = Auth::user() ?? User::where('email', 'guest@pup.edu.ph')->first();

        if (!$user) {
            return response()->json([
                'valid' => false,
                'message' => 'User session not found. Please login again.',
            ], 401);
        }

        $request->validate([
            'barcode' => 'required|string|max:255',
        ]);

        $barcode = trim((string) $request->barcode);
        $mismatchMessage = $this->getBarcodeMismatchMessage($user, $barcode);
        if ($mismatchMessage) {
            return response()->json([
                'valid' => false,
                'message' => $mismatchMessage,
            ], 422);
        }

        $barcodeInUse = User::where('barcode', $barcode)
            ->where('id', '!=', $user->id)
            ->exists();

        if ($barcodeInUse) {
            return response()->json([
                'valid' => false,
                'message' => 'This barcode is already linked to another account.',
            ], 422);
        }

        return response()->json([
            'valid' => true,
            'barcode' => $barcode,
            'message' => 'Barcode validated. You can submit registration.',
        ]);
    }

    // -------------------------------
    // FETCH USER USING STUDENT ID (Bridge for Walk-in)
    // -------------------------------
    public function fetchUser($student_id)
    {
        $user = User::where('student_id', $student_id)->first();

        if ($user) {
            return response()->json([
                'success' => true,
                'name' => $user->name,
                'student_id' => $user->student_id,
                'student_number' => $user->student_number,
                'barcode' => $user->barcode
            ]);
        }

        return response()->json(['success' => false]);
    }


    // ---------------------------------------------------------
// HEALTH FORM FUNCTIONS
// ---------------------------------------------------------

public function showHealthForm()
{
    /** @var \App\Models\User $user */
    $user = Auth::user();

    // Refresh user from database to ensure all fields are populated
    if ($user) {
        $user = User::find($user->id);
    }

    if ($this->hasSubmittedHealthProfile($user)) {
        return redirect('/student/account?view=health-record')
            ->with('info', 'You have already submitted your health profile.');
    }

    // Resolve the linked admin profile (Required by your view to avoid Undefined Variable error)
    $linkedAdminProfile = $this->resolveLinkedAdminProfile($user);
    $healthFormPrefill = $this->buildHealthFormPrefill($user, $linkedAdminProfile);
    $this->persistResolvedUserProfileFields($user, $healthFormPrefill);
    $this->persistResolvedReferenceNumber($user, $healthFormPrefill['reference_number'] ?? '');
    $calculatedAge = $healthFormPrefill['age'] ?? null;

    // Return the view with all required variables
    $displayFirstName = $healthFormPrefill['first_name'] ?? '';
    $displayMiddleName = $healthFormPrefill['middle_name'] ?? '';
    $displayLastName = $healthFormPrefill['last_name'] ?? '';
    $displayReferenceNumber = $healthFormPrefill['reference_number'] ?? '';
    $prefill = $healthFormPrefill;

    return view('student.health_form', compact('user', 'calculatedAge', 'linkedAdminProfile', 'healthFormPrefill', 'displayFirstName', 'displayMiddleName', 'displayLastName', 'displayReferenceNumber', 'prefill'));
}

public function storeHealthForm(Request $request)
{
    /** @var \App\Models\User|null $user */
    $user = Auth::user();
    if ($this->hasSubmittedHealthProfile($user)) {
        return redirect('/student/account?view=health-record')
            ->with('info', 'Your health profile is already submitted.');
    }

    $request->validate([
        'student_id'        => 'nullable|string|max:255',
        'reference_number'  => ['required', 'string', 'max:20', 'regex:/^[A-Za-z0-9]+(?:-[A-Za-z0-9]+)+$/'],
        'school_year'       => ['required', 'string', 'regex:/^\d{4}-\d{4}$/'],
        'home_address'      => 'required|string|max:255',
        'zipcode'           => 'required|string|max:20',
        'birthday'          => 'required|date',
        'student_photo'     => 'required|image|mimes:jpeg,png,jpg|max:1024',
        'height'            => 'required|numeric|min:0',
        'weight'            => 'required|numeric|min:0',
        'age'               => 'required|numeric|min:15|max:100',
        'sex'               => 'required|string',
        'civil_status'      => 'required|string',
        'course_college'    => 'required|string',
        'blood_type'        => 'required|string|max:20',
        'contact_no'        => 'required|string|max:20',
        'guardian_name'     => 'required|string|max:255',
        'landline'          => 'nullable|string|max:20',
        'cellphone'         => 'required|string|max:20',
        'has_illness'       => 'required|string|in:Yes,No',
        'medical_history'   => 'nullable|array',
        'medical_history.*' => 'string|max:100',
        'other_illness'     => 'nullable|string|max:1000',
        'food_allergies'    => 'nullable|string|max:255',
        'no_allergies'      => 'nullable|boolean',
        'medicine_allergies' => 'nullable|array',
        'medicine_allergies.*' => 'string|max:100',
        'other_med_allergies' => 'nullable|string|max:255',
        'is_smoker'         => 'required|string|in:Yes,No',
        'is_drinker'        => 'required|string|in:Yes,No',
        'covid_vaccinated'  => 'required|string|in:Yes,No',
        'vaccine_history'   => 'nullable|array',
        'vaccine_history.first_dose.date' => 'required_if:covid_vaccinated,Yes|nullable|date|after_or_equal:2021-03-01|before_or_equal:2025-12-31',
        'vaccine_history.first_dose.brand' => 'required_if:covid_vaccinated,Yes|nullable|string|max:100',
        'vaccine_history.second_dose.date' => 'required_if:covid_vaccinated,Yes|nullable|date|after_or_equal:2021-03-01|before_or_equal:2025-12-31',
        'vaccine_history.second_dose.brand' => 'required_if:covid_vaccinated,Yes|nullable|string|max:100',
        'vaccine_history.booster_1.date' => 'nullable|required_with:vaccine_history.booster_1.brand|date|after_or_equal:2021-03-01|before_or_equal:2025-12-31',
        'vaccine_history.booster_1.brand' => 'nullable|required_with:vaccine_history.booster_1.date|string|max:100',
        'vaccine_history.booster_2.date' => 'nullable|required_with:vaccine_history.booster_2.brand|date|after_or_equal:2021-03-01|before_or_equal:2025-12-31',
        'vaccine_history.booster_2.brand' => 'nullable|required_with:vaccine_history.booster_2.date|string|max:100',

        'chest_xray_result' => 'required|file|mimes:pdf,jpg,jpeg,png|max:2048',
        'xray_date'         => 'required|date',
        'xray_findings'     => 'required|string|in:Normal,With Findings,Not Sure / For Clinic Review',
        'has_disability'    => 'required|string',
        'disability_type'   => 'required_if:has_disability,Yes|nullable|string|max:255',
        'pwd_id_proof'      => 'required_if:has_disability,Yes|file|mimes:pdf|max:2048',
        'medical_certificate' => 'required|file|mimes:pdf,jpg,jpeg,png|max:2048',
        'doctor_name'       => 'required|string|max:255',
        'med_cert_date'     => 'required|date',
        'med_cert_findings' => 'required|string|in:No Findings / Normal,With Findings,Not Sure / For Clinic Review',
        'health_profile_certified' => 'accepted',
    ]);

    $submittedReference = strtoupper(trim((string) $request->input('reference_number')));

    if ($request->input('covid_vaccinated') === 'Yes') {
        $doseDateFields = [
            'first_dose' => '1st Dose',
            'second_dose' => '2nd Dose',
            'booster_1' => 'Booster 1',
            'booster_2' => 'Booster 2',
        ];
        $usedDates = [];
        $dateErrors = [];

        foreach ($doseDateFields as $doseKey => $doseLabel) {
            $date = trim((string) $request->input("vaccine_history.{$doseKey}.date"));
            if ($date === '') {
                continue;
            }

            if (isset($usedDates[$date])) {
                $dateErrors["vaccine_history.{$doseKey}.date"] =
                    "{$doseLabel} cannot use the same date as {$usedDates[$date]}.";
            } else {
                $usedDates[$date] = $doseLabel;
            }
        }

        $firstDoseDate = trim((string) $request->input('vaccine_history.first_dose.date'));
        $secondDoseDate = trim((string) $request->input('vaccine_history.second_dose.date'));

        if ($firstDoseDate !== '' && $secondDoseDate !== '') {
            $minimumSecondDoseDate = Carbon::parse($firstDoseDate)->addWeeks(4)->startOfDay();
            $selectedSecondDoseDate = Carbon::parse($secondDoseDate)->startOfDay();

            if ($selectedSecondDoseDate->lt($minimumSecondDoseDate)) {
                $dateErrors['vaccine_history.second_dose.date'] =
                    'The 2nd Dose must be at least 4 weeks after the 1st Dose.';
            }
        }

        if ($dateErrors !== []) {
            throw ValidationException::withMessages($dateErrors);
        }
    }

    /** @var \App\Models\User $user */
    $user = Auth::user();
    $normalizedHeight = $this->normalizeMeasurement($request->input('height'), 'cm');
    $normalizedWeight = $this->normalizeMeasurement($request->input('weight'), 'kg');
    $user->DOB = $request->input('birthday');
    $user->contact_no = $request->input('contact_no');
    $resolvedGender = trim((string) $request->input('sex'));
    if ($resolvedGender !== '') {
        $user->gender = $resolvedGender;
    }
    $resolvedCourse = trim((string) $request->input('course_college'));
    if ($resolvedCourse !== '') {
        $user->course = $resolvedCourse;
    }
    $user->reference_number = $submittedReference;
    $user->save();

    try {
        $photoPath = $request->file('student_photo')->store('health_profiles/photos', 'public');
        $chestXrayPath = $request->hasFile('chest_xray_result')
            ? $request->file('chest_xray_result')->store('health_profiles/chest_xray_results', 'public')
            : null;
        $pwdIdProofPath = $request->hasFile('pwd_id_proof')
            ? $request->file('pwd_id_proof')->store('health_profiles/pwd_id_proofs', 'public')
            : null;
        $medicalCertificatePath = $request->hasFile('medical_certificate')
            ? $request->file('medical_certificate')->store('health_profiles/medical_certificates', 'public')
            : null;
        \App\Models\HealthProfile::updateOrCreate(
            ['user_id' => $user->id],
            [
                'student_id'         => $request->student_id,
                'reference_number'   => $submittedReference,
                'school_year'        => $request->school_year,
                'home_address'       => $request->home_address,
                'zipcode'            => $request->zipcode,
                'birthday'           => $request->input('birthday'),
                'student_photo'      => $photoPath,
                'height'             => $normalizedHeight,
                'weight'             => $normalizedWeight,
                'age'                => $request->age,
                'sex'                => $request->sex,
                'civil_status'       => $request->civil_status,
                'course_college'     => $request->course_college,
                'blood_type'         => $request->blood_type,
                'guardian_name'      => $request->guardian_name,
                'landline'           => $request->landline,
                'cellphone'          => $request->cellphone,
                'has_illness'        => $request->input('has_illness'),
                'medical_history'    => $request->input('has_illness') === 'Yes'
                    ? array_values($request->input('medical_history', []))
                    : [],
                'other_illness'      => $request->input('has_illness') === 'Yes'
                    ? $request->input('other_illness')
                    : null,
                'food_allergies'     => $request->boolean('no_allergies') ? null : $request->input('food_allergies'),
                'no_allergies'       => $request->boolean('no_allergies'),
                'medicine_allergies' => $request->boolean('no_allergies') ? [] : array_values($request->input('medicine_allergies', [])),
                'other_med_allergies' => $request->boolean('no_allergies') ? null : $request->input('other_med_allergies'),
                'is_smoker'          => $request->input('is_smoker'),
                'is_drinker'         => $request->input('is_drinker'),
                'covid_vaccinated'   => $request->input('covid_vaccinated'),
                'vaccine_history'    => $request->input('covid_vaccinated') === 'Yes'
                    ? $request->input('vaccine_history', [])
                    : [],
                'chest_xray_result'  => $chestXrayPath,
                'xray_date'          => $request->input('xray_date'),
                'xray_findings'      => $request->input('xray_findings'),
                'has_disability'     => $request->has_disability,
                'disability_type'    => $request->disability_type,
                'pwd_id_proof'       => $pwdIdProofPath,
                'medical_certificate' => $medicalCertificatePath,
                'doctor_name'        => $request->input('doctor_name'),
                'med_cert_date'      => $request->input('med_cert_date'),
                'med_cert_findings'  => $request->input('med_cert_findings'),
                'clearance_status'   => 'For Verification',
                'pending_reason'     => null,
                'verified_at'        => null,
            ]
        );

        $user->is_health_profile_completed = 0;
        $user->save();

        \App\Models\ActivityLog::create([
            'user_id'     => $user->id,
            'user_name'   => $user->name,
            'action'      => 'Health Profile Completed',
            'description' => 'Student completed the Health Profile requirements.',
            'ip_address'  => $request->ip(),
            'user_agent'  => $request->userAgent(),
        ]);

        return redirect('/student/account?view=health-record')
            ->with('success', 'Health Profile saved successfully.')
            ->with('health_profile_submitted', true)
            ->with('show_health_print_reminder', true);

    } catch (\Exception $e) {
   
        return back()->withInput()->with('error', 'Something went wrong: ' . $e->getMessage());
    }
}

public function printHealthForm()
{
    /** @var \App\Models\User|null $user */
    $user = Auth::guard('student')->user();
    if (!$user) {
        return redirect('/login-as-student')->with('error', 'Please login first.');
    }

    $profile = HealthProfile::query()
        ->with('user')
        ->where('user_id', $user->id)
        ->first();

    if (!$profile) {
        return redirect('/student/account')
            ->with('error', 'Submit your health profile before printing the form.');
    }

    $pdf = $this->buildStudentHealthFormPdf($profile);
    $fileName = $this->studentHealthFormFileName($profile, $user);

    return $pdf->stream($fileName, [
        'Cache-Control' => 'no-store, no-cache, must-revalidate, max-age=0',
        'Pragma' => 'no-cache',
        'Expires' => '0',
    ]);
}

public function downloadHealthForm()
{
    /** @var \App\Models\User|null $user */
    $user = Auth::guard('student')->user();
    if (!$user) {
        return redirect('/login-as-student')->with('error', 'Please login first.');
    }

    $profile = HealthProfile::query()
        ->with('user')
        ->where('user_id', $user->id)
        ->first();

    if (!$profile) {
        return redirect('/student/account')
            ->with('error', 'Submit your health profile before downloading the form.');
    }

    $pdf = $this->buildStudentHealthFormPdf($profile);
    $fileName = $this->studentHealthFormFileName($profile, $user);

    return $pdf->download($fileName);
}

private function buildStudentHealthFormPdf(HealthProfile $profile)
{
    $healthFormIdentity = $this->buildHealthFormPrefill(
        $profile->user,
        $this->resolveLinkedAdminProfile($profile->user),
        $profile
    );
    $this->persistResolvedReferenceNumber(
        $profile->user,
        $healthFormIdentity['reference_number'] ?? '',
        $profile
    );
    $profile->load('user');

    $pdf = Pdf::loadView('student.print_health_form', [
        'profile' => $profile,
        'pdfMode' => true,
        'healthFormIdentity' => $healthFormIdentity,
    ]);
    $pdf->setPaper([0, 0, 612, 936]);

    return $pdf;
}

private function studentHealthFormFileName(HealthProfile $profile, User $user): string
{
    $identifier = trim((string) (
        $profile->reference_number
        ?: $user->reference_number
        ?: $profile->student_number
        ?: $user->student_number
        ?: $user->student_id
        ?: $profile->id
    ));

    return 'health-form-' . preg_replace('/[^A-Za-z0-9\-_]+/', '-', $identifier) . '.pdf';
}

public function testingSkipHealthForm()
{
    abort_unless(app()->environment('local'), 404);

    /** @var \App\Models\User|null $user */
    $user = Auth::guard('student')->user();
    if (!$user) {
        return redirect('/login-as-student')->with('error', 'Please login first.');
    }

    $birthday = $user->DOB ?: '2000-01-01';
    try {
        $age = \Carbon\Carbon::parse($birthday)->age;
    } catch (\Throwable $exception) {
        $birthday = '2000-01-01';
        $age = 25;
    }

    HealthProfile::query()->firstOrCreate(
        ['user_id' => $user->id],
        [
            'student_id' => $user->student_id,
            'student_number' => $user->student_number ?: $user->student_id ?: 'TEST-STUDENT',
            'reference_number' => $user->student_number ?: $user->student_id ?: 'TEST-REFERENCE',
            'school_year' => '2025-2026',
            'home_address' => 'Testing Address',
            'zipcode' => '0000',
            'birthday' => $birthday,
            'height' => $user->height ?: '165 cm',
            'weight' => $user->weight ?: '60 kg',
            'age' => $age,
            'sex' => $user->gender ?: 'Male',
            'civil_status' => 'Single',
            'course_college' => $user->course ?: 'Testing Course',
            'blood_type' => 'Unknown',
            'guardian_name' => 'Testing Guardian',
            'landline' => null,
            'cellphone' => $user->contact_no ?: '09000000000',
            'has_illness' => 'No',
            'medical_history' => [],
            'has_disability' => 'No',
            'no_allergies' => true,
            'medicine_allergies' => [],
            'is_smoker' => 'No',
            'is_drinker' => 'No',
            'covid_vaccinated' => 'Yes',
            'vaccine_history' => [
                'first_dose' => ['date' => '2021-06-01', 'brand' => 'Testing Brand'],
                'second_dose' => ['date' => '2021-07-01', 'brand' => 'Testing Brand'],
                'booster_1' => ['date' => '2022-03-01', 'brand' => 'Testing Brand'],
                'booster_2' => ['date' => '2023-03-01', 'brand' => 'Testing Brand'],
            ],
            'clearance_status' => 'For Verification',
        ]
    );

    return redirect()->route('student.health_form.print');
}

    // -------------------------------
    // RESET BARCODE (for testing)
    // -------------------------------
    public function resetBarcode()
    {
        $user = Auth::user() ?? User::where('email', 'guest@pup.edu.ph')->first();

        if (!$user) {
            return redirect()->back()->with('error', 'User not found.');
        }

        $user->barcode = null;
        $user->save();

        return redirect()->back()->with('success', 'Barcode reset successfully! You can scan again.');
    }
} 
