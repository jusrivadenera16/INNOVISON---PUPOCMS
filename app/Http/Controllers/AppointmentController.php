<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Admin;
use App\Models\Appointment;
use App\Models\AppointmentFeedback;
use App\Models\Consultation;
use App\Models\HealthProfile;
use App\Models\SystemSetting;
use App\Models\User;
use App\Services\GuisisApiService;
use App\Services\PuptasWebhookService;
use App\Services\ClinicWorkflowService;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\ValidationException;
use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\Carbon;

class AppointmentController extends Controller
{
    private function promoteDesigneeAdminToStudentGuard(): ?User
    {
        /** @var \App\Models\User|null $studentUser */
        $studentUser = Auth::guard('student')->user();
        if ($studentUser) {
            return $studentUser;
        }

        /** @var \App\Models\User|null $adminUser */
        $adminUser = Auth::guard('admin')->user();
        if (!$adminUser instanceof User || User::normalizeRole((string) ($adminUser->user_role ?? '')) !== User::ROLE_ADMIN) {
            return null;
        }

        $linkedAdmin = null;
        if ($adminUser->relationLoaded('adminProfile')) {
            $linkedAdmin = $adminUser->adminProfile;
        }
        if (!$linkedAdmin) {
            $linkedAdmin = Admin::query()
                ->where(function ($builder) use ($adminUser) {
                    if (Admin::hasColumn('user_id')) {
                        $builder->orWhere('user_id', $adminUser->id);
                    }

                    $email = trim((string) ($adminUser->email ?? ''));
                    if ($email !== '') {
                        if (Admin::hasColumn('email')) {
                            $builder->orWhere('email', $email);
                        }
                        if (Admin::hasColumn('email_address')) {
                            $builder->orWhere('email_address', $email);
                        }
                    }
                })
                ->first();
        }

        $resolvedRole = strtolower(trim((string) (
            $linkedAdmin?->access_level
            ?? $linkedAdmin?->admin_hub_role
            ?? ''
        )));

        if (!in_array($resolvedRole, ['designee', 'admin_designee'], true)) {
            return null;
        }

        Auth::guard('student')->login($adminUser);
        Auth::shouldUse('student');
        Auth::guard('admin')->logout();

        return $adminUser;
    }

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
                $approvalExtras = [];
                $approvalMessage = trim((string) ($appt->approval_message ?? ''));
                $approvalReminders = collect((array) ($appt->approval_reminders ?? []))
                    ->map(fn ($reminder) => trim((string) $reminder))
                    ->filter()
                    ->values();

                if ($approvalMessage !== '') {
                    $approvalExtras[] = 'Message: ' . $approvalMessage;
                }

                if ($approvalReminders->isNotEmpty()) {
                    $approvalExtras[] = 'Reminder: ' . $approvalReminders->implode(', ');
                }

                $notifications[] = [
                    'id' => $this->buildNotificationId('appointment-approved', [$appt->id, $appt->status, optional($appt->updated_at)->timestamp]),
                    'type' => 'success',
                    'icon' => 'OK',
                    'message' => "Your {$appt->service} on {$dateStr} has been approved." . ($approvalExtras ? ' ' . implode(' ', $approvalExtras) : ''),
                    'time' => $timeAgo,
                    'link' => url('/student/history'),
                ];
            } elseif ($appt->status === 'Cancelled') {
                $cancelledForClosure = str_contains((string) $appt->notes, '[Clinic Closure]');
                $notifications[] = [
                    'id' => $this->buildNotificationId('appointment-cancelled', [$appt->id, $appt->status, optional($appt->updated_at)->timestamp]),
                    'type' => 'danger',
                    'icon' => 'X',
                    'message' => $cancelledForClosure
                        ? "Your {$appt->service} on {$dateStr} was cancelled because the clinic is unavailable during that schedule. Please book a new appointment after reopening."
                        : "Your {$appt->service} on {$dateStr} was cancelled.",
                    'time' => $timeAgo,
                    'link' => $cancelledForClosure ? url('/student/booking') : url('/student/history'),
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
        $isHealthProfileIssued = in_array($healthProfileStatus, ['Issued', 'Fully Cleared'], true);
        $isHealthProfileResubmission = $healthProfileStatus === 'Pending Resubmission';

        if ($healthProfile) {
            $notifications[] = [
                'id' => $this->buildNotificationId('health-record', [$healthProfile->id, $healthProfileStatus, optional($healthProfile->updated_at)->timestamp]),
                'type' => $isHealthProfileIssued ? 'success' : 'warning',
                'icon' => $isHealthProfileIssued ? 'OK' : '...',
                'message' => $isHealthProfileIssued
                    ? 'Your health profile has been approved by the clinic.'
                    : ($isHealthProfileResubmission
                        ? 'The clinic requested replacement files for your health profile.'
                        : 'Your health profile was submitted and is awaiting medical review.'),
                'time' => 'Health profile status',
                'link' => url('/student/account?view=health-record'),
            ];

            if ($isHealthProfileIssued && $puptasSyncStatus !== null) {
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

    private function fetchPuptasApplicantLookupForUser(User $user): array
    {
        $puptasService = app(PuptasWebhookService::class);
        $lookupResults = [];

        $referenceNumber = trim((string) ($user->reference_number ?? ''));
        if (
            $referenceNumber !== ''
            && !$this->looksLikeIdpIdentifier($referenceNumber, $user)
            && !$this->isClinicReference($referenceNumber)
        ) {
            $lookup = $puptasService->fetchApplicantByReferenceNumberDetailed($referenceNumber);
            Log::info('PUPTAS applicant reference lookup finished.', [
                'user_id' => $user->id,
                'lookup_type' => 'reference',
                'outcome' => $lookup['outcome'] ?? null,
                'status' => $lookup['status'] ?? null,
                'has_data' => is_array($lookup['data'] ?? null),
                'has_reference_number' => trim((string) data_get($lookup, 'data.reference_number', data_get($lookup, 'data.user.reference_number', ''))) !== '',
            ]);
            $lookupResults[] = $lookup;
            if (($lookup['outcome'] ?? '') === 'found' && is_array($lookup['data'] ?? null)) {
                return $lookup;
            }
        }

        $idpUserId = trim((string) ($user->student_id ?? ''));
        if ($idpUserId !== '') {
            $lookup = $puptasService->fetchApplicantByIdpUserIdDetailed($idpUserId);
            Log::info('PUPTAS applicant IDP lookup finished.', [
                'user_id' => $user->id,
                'lookup_type' => 'idp',
                'outcome' => $lookup['outcome'] ?? null,
                'status' => $lookup['status'] ?? null,
                'has_data' => is_array($lookup['data'] ?? null),
                'has_reference_number' => trim((string) data_get($lookup, 'data.reference_number', data_get($lookup, 'data.user.reference_number', ''))) !== '',
            ]);
            $lookupResults[] = $lookup;
            if (($lookup['outcome'] ?? '') === 'found' && is_array($lookup['data'] ?? null)) {
                return $lookup;
            }
        }

        $unavailable = collect($lookupResults)->first(
            fn ($lookup) => ($lookup['outcome'] ?? '') === 'unavailable'
        );
        if (is_array($unavailable)) {
            return $unavailable;
        }

        $notFound = collect($lookupResults)->first(
            fn ($lookup) => ($lookup['outcome'] ?? '') === 'not_found'
        );
        if (is_array($notFound)) {
            return $notFound;
        }

        return [
            'success' => false,
            'outcome' => 'not_found',
            'status' => null,
            'message' => 'No PUPTAS applicant identifiers are available for this account.',
            'data' => null,
            'attempts' => 0,
        ];
    }

    private function fetchPuptasApplicantForUser(User $user): ?array
    {
        $lookup = $this->fetchPuptasApplicantLookupForUser($user);

        return ($lookup['outcome'] ?? '') === 'found' && is_array($lookup['data'] ?? null)
            ? $lookup['data']
            : null;
    }

    private function isClinicReference(?string $referenceNumber): bool
    {
        return str_starts_with(strtoupper(trim((string) $referenceNumber)), 'CLN');
    }

    private function looksLikeReferenceIdentifier(?string $value): bool
    {
        $value = strtoupper(trim((string) $value));

        return $value === ''
            || str_starts_with($value, 'CLN')
            || str_starts_with($value, 'TEST-')
            || str_starts_with($value, 'TESTLOCAL')
            || str_starts_with($value, 'LOC-')
            || (bool) preg_match('/^\d{4}-\d{4}-\d{4}/', $value)
            || (bool) preg_match('/^\d{4}-[A-Z]+-\d+/', $value);
    }

    private function formatDisplayNameParts(?string $firstName, ?string $middleName, ?string $lastName, ?string $suffixName = ''): string
    {
        return trim(preg_replace('/\s+/', ' ', implode(' ', array_filter([
            trim((string) $firstName),
            trim((string) $middleName),
            trim((string) $lastName),
            trim((string) $suffixName),
        ])))) ?: '';
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
            'full_name' => $this->formatDisplayNameParts($firstName, $middleName, $lastName),
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
        if (
            Schema::hasTable('system_settings')
            && SystemSetting::booleanValue('maintenance_mode_enabled', false)
        ) {
            return redirect()->route('maintenance');
        }

        $this->promoteDesigneeAdminToStudentGuard();
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
            if ($candidate === '' || $this->looksLikeIdpIdentifier($candidate, $user) || $this->looksLikeReferenceIdentifier($candidate)) {
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
            if (
                $candidate !== ''
                && !$this->looksLikeIdpIdentifier($candidate, $user)
                && !$this->isClinicReference($candidate)
            ) {
                return $candidate;
            }
        }

        return '';
    }

    private function persistResolvedStudentNumber(User $user, ?HealthProfile $healthProfile, ?string $studentNumber): void
    {
        $studentNumber = trim((string) $studentNumber);
        if ($studentNumber === '' || $this->looksLikeIdpIdentifier($studentNumber, $user) || $this->looksLikeReferenceIdentifier($studentNumber)) {
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

        $currentUserReference = trim((string) ($user->reference_number ?? ''));
        $userHasAdmissionReference = $currentUserReference !== ''
            && !$this->looksLikeIdpIdentifier($currentUserReference, $user)
            && !$this->isClinicReference($currentUserReference);
        $incomingIsClinicReference = $this->isClinicReference($referenceNumber);

        if (!$userHasAdmissionReference || !$incomingIsClinicReference) {
            if ($currentUserReference !== $referenceNumber) {
                $user->reference_number = $referenceNumber;
                $user->save();
            }
        }

        $currentHealthReference = trim((string) optional($healthProfile)->reference_number);
        $healthProfileHasAdmissionReference = $currentHealthReference !== ''
            && !$this->looksLikeIdpIdentifier($currentHealthReference, $user)
            && !$this->isClinicReference($currentHealthReference);

        if (
            $healthProfile
            && (!$healthProfileHasAdmissionReference || !$incomingIsClinicReference)
            && $currentHealthReference !== $referenceNumber
        ) {
            $healthProfile->reference_number = $referenceNumber;
            $healthProfile->save();
        }
    }

    private function persistResolvedUserProfileFields(User $user, array $prefill): void
    {
        $resolvedStudentNumber = trim((string) ($prefill['student_number'] ?? ''));
        $resolvedGender = trim((string) ($prefill['sex'] ?? ''));

        $shouldSave = false;

        if (
            $resolvedStudentNumber !== ''
            && !$this->looksLikeIdpIdentifier($resolvedStudentNumber, $user)
            && !$this->looksLikeReferenceIdentifier($resolvedStudentNumber)
            && trim((string) $user->student_number) === ''
        ) {
            $user->student_number = $resolvedStudentNumber;
            $shouldSave = true;
        }

        if ($resolvedGender !== '' && trim((string) $user->gender) === '') {
            $user->gender = $resolvedGender;
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

    private function sanitizeAcademicCourse(?string $value): string
    {
        $course = trim((string) $value);
        $normalized = strtolower(preg_replace('/\s+/', ' ', $course));
        $roleLabels = [
            'admin', 'admin - designee', 'admin designee', 'designee',
            'admin - clinic staff', 'clinic staff', 'student assistant',
            'faculty', 'faculty / staff', 'faculty/staff', 'guest', 'applicant',
            'regular', 'superadmin', 'super admin',
        ];

        return $course !== '' && !in_array($normalized, $roleLabels, true) ? $course : '';
    }

    private function healthFormCourseOptions(): array
    {
        $courses = [
            'BSBA-HRM' => 'Bachelor of Science in Business Administration major in Human Resource Management',
            'BSBA-MM' => 'Bachelor of Science in Business Administration major in Marketing Management',
            'BSECE' => 'Bachelor of Science in Electronics Engineering',
            'BSIT' => 'Bachelor of Science in Information Technology',
            'BSME' => 'Bachelor of Science in Mechanical Engineering',
            'BSOA' => 'Bachelor of Science in Office Administration',
            'BSPSY' => 'Bachelor of Science in Psychology',
            'BSED-ENGLISH' => 'Bachelor of Secondary Education major in English',
            'BSED-MATH' => 'Bachelor of Secondary Education major in Mathematics',
            'DIT' => 'Diploma in Information Technology',
            'DOMT' => 'Diploma in Office Management Technology',
        ];

        return collect($courses)
            ->map(fn (string $name, string $code) => [
                'code' => $code,
                'name' => $name,
                'label' => $code . ' - ' . $name,
            ])
            ->values()
            ->all();
    }

    private function healthFormCourseMap(): array
    {
        return collect($this->healthFormCourseOptions())
            ->keyBy('code')
            ->all();
    }

    private function normalizeCourseCode(?string $value): string
    {
        return strtoupper(trim((string) $value));
    }

    private function isHealthCourseApplicable(User $user): bool
    {
        $idpRole = strtolower(trim((string) ($user->idp_role ?? '')));
        $userType = strtolower(trim((string) ($user->user_type ?? '')));
        $userRole = User::normalizeRole((string) ($user->user_role ?? ''));

        if (in_array($userType, ['faculty', 'guest', 'regular', 'assistant', 'student assistant'], true)) {
            return false;
        }

        if (in_array($idpRole, ['faculty', 'guest', 'admin', 'superadmin', 'super_admin'], true)) {
            return false;
        }

        return in_array($userType, ['student', 'applicant'], true)
            || in_array($idpRole, ['student', 'applicant'], true)
            || ($userType === '' && $userRole === User::ROLE_STUDENT);
    }

    private function isApplicantAccount(User $user): bool
    {
        return strtolower(trim((string) ($user->idp_role ?? ''))) === 'applicant'
            || strtolower(trim((string) ($user->user_type ?? ''))) === 'applicant';
    }

    private function isStudentAccount(User $user): bool
    {
        if ($this->isApplicantAccount($user)) {
            return false;
        }

        $idpRole = strtolower(trim((string) ($user->idp_role ?? '')));
        $userType = strtolower(trim((string) ($user->user_type ?? '')));
        $userRole = User::normalizeRole((string) ($user->user_role ?? ''));

        return $idpRole === 'student'
            || $userType === 'student'
            || ($userType === '' && $userRole === User::ROLE_STUDENT);
    }

    private function enrolledStudentReferenceNumber(User $user, ?HealthProfile $healthProfile = null, ?array $guisisAccountData = null): string
    {
        if (!$this->isStudentAccount($user)) {
            return '';
        }

        $candidates = [
            trim((string) data_get($guisisAccountData, 'student_number')),
            trim((string) optional($healthProfile)->student_number),
            trim((string) ($user->student_number ?? '')),
        ];

        foreach ($candidates as $candidate) {
            if ($candidate === '' || $this->looksLikeIdpIdentifier($candidate, $user) || $this->looksLikeReferenceIdentifier($candidate)) {
                continue;
            }

            return $candidate;
        }

        return '';
    }

    private function findHealthFormCourse(?string $code, ?string $name = null): array
    {
        $map = $this->healthFormCourseMap();
        $normalizedCode = $this->normalizeCourseCode($code);

        if ($normalizedCode !== '' && isset($map[$normalizedCode])) {
            return $map[$normalizedCode];
        }

        $normalizedName = strtolower(preg_replace('/\s+/', ' ', trim((string) $name)));
        if ($normalizedName !== '') {
            foreach ($map as $option) {
                $candidateName = strtolower(preg_replace('/\s+/', ' ', $option['name']));
                $candidateLabel = strtolower(preg_replace('/\s+/', ' ', $option['label']));
                if ($normalizedName === $candidateName || $normalizedName === $candidateLabel) {
                    return $option;
                }
            }
        }

        return ['code' => '', 'name' => '', 'label' => ''];
    }

    private function resolveHealthFormCourse(User $user, ?HealthProfile $healthProfile = null, ?array $applicantData = null, ?Request $request = null): array
    {
        if (!$this->isHealthCourseApplicable($user)) {
            return ['code' => '', 'name' => '', 'label' => ''];
        }

        $requestedCode = $request ? $this->normalizeCourseCode($request->input('course_code')) : '';
        if ($requestedCode !== '') {
            return $this->findHealthFormCourse($requestedCode);
        }

        $existingCode = $this->normalizeCourseCode(optional($healthProfile)->course_code);
        $existingName = $this->sanitizeAcademicCourse(optional($healthProfile)->course_college);
        $existingCourse = $this->findHealthFormCourse($existingCode, $existingName);
        if ($existingCourse['code'] !== '') {
            return $existingCourse;
        }

        $puptasCode = $this->normalizeCourseCode(
            data_get($applicantData, 'program.code')
            ?: data_get($applicantData, 'program_code')
            ?: data_get($applicantData, 'programCode')
            ?: data_get($applicantData, 'course.code')
            ?: data_get($applicantData, 'course_code')
        );
        $puptasName = trim((string) (
            data_get($applicantData, 'program.name')
            ?: data_get($applicantData, 'program_name')
            ?: data_get($applicantData, 'programName')
            ?: data_get($applicantData, 'course.name')
            ?: data_get($applicantData, 'course_name')
            ?: data_get($applicantData, 'program')
            ?: data_get($applicantData, 'course')
        ));

        $puptasCourse = $this->findHealthFormCourse($puptasCode, $puptasName);
        if ($puptasCourse['code'] !== '') {
            return $puptasCourse;
        }

        $userCourse = $this->sanitizeAcademicCourse($user->course ?? null);
        return $this->findHealthFormCourse(null, $userCourse);
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

    private function resolveHealthReferenceMode(
        User $user,
        ?Admin $linkedAdminProfile = null,
        ?array $applicantData = null,
        ?string $lookupOutcome = null
    ): string
    {
        $linkedAdminProfile = $linkedAdminProfile ?: $this->resolveLinkedAdminProfile($user);
        if ($this->enrolledStudentReferenceNumber($user) !== '') {
            return 'student_number';
        }

        if (!is_array($applicantData) && $lookupOutcome === null) {
            $lookup = $this->fetchPuptasApplicantLookupForUser($user);
            $applicantData = is_array($lookup['data'] ?? null) ? $lookup['data'] : null;
            $lookupOutcome = (string) ($lookup['outcome'] ?? '');
        }

        $existingReference = trim((string) ($user->reference_number ?? ''));
        if (
            $existingReference !== ''
            && !$this->looksLikeIdpIdentifier($existingReference, $user)
            && !$this->isClinicReference($existingReference)
        ) {
            return 'admission';
        }

        $applicantIdentity = $this->normalizePuptasApplicantIdentity($applicantData);
        $normalizedIdpRole = strtolower(trim((string) ($user->idp_role ?? '')));
        $isApplicantAccount = $normalizedIdpRole === 'applicant'
            || strtolower(trim((string) ($user->user_type ?? ''))) === 'applicant';

        if ($isApplicantAccount) {
            return $lookupOutcome === 'unavailable'
                ? 'verification_unavailable'
                : 'admission';
        }

        if ($lookupOutcome === 'found' || ($applicantIdentity['available'] ?? false) === true) {
            return 'admission';
        }

        if (
            $normalizedIdpRole === 'student'
            || strtolower(trim((string) ($user->user_type ?? ''))) === 'student'
        ) {
            return $lookupOutcome === 'unavailable'
                ? 'verification_unavailable'
                : 'admission';
        }

        if (in_array($normalizedIdpRole, ['student', 'guest', 'faculty', 'admin', 'superadmin', 'super_admin'], true)) {
            return 'clinic';
        }

        $hasLinkedDirectoryProfile = $linkedAdminProfile instanceof Admin;
        $isAdminLikeUser = User::normalizeRole((string) ($user->user_role ?? '')) === User::ROLE_ADMIN
            || User::normalizeRole((string) ($user->user_role ?? '')) === User::ROLE_SUPERADMIN;

        if ($hasLinkedDirectoryProfile || $isAdminLikeUser) {
            return 'clinic';
        }

        return 'guest';
    }

    private function generateClinicReferenceNumber(User $user): string
    {
        $timestamp = now()->setTimezone('Asia/Taipei');
        $baseReference = sprintf(
            'CLN-%s-%sR',
            $timestamp->format('mdy'),
            $timestamp->format('Hi')
        );

        $existingReferences = collect();

        if (\Schema::hasTable('health_profiles') && \Schema::hasColumn('health_profiles', 'reference_number')) {
            $existingReferences = $existingReferences->merge(
                HealthProfile::query()
                    ->where('reference_number', 'like', $baseReference . '%')
                    ->pluck('reference_number')
            );
        }

        if (\Schema::hasTable('users') && \Schema::hasColumn('users', 'reference_number')) {
            $existingReferences = $existingReferences->merge(
                User::query()
                    ->where('reference_number', 'like', $baseReference . '%')
                    ->pluck('reference_number')
            );
        }

        $highestSequence = $existingReferences
            ->map(function ($reference) use ($baseReference) {
                $reference = strtoupper(trim((string) $reference));
                if (!preg_match('/^' . preg_quote($baseReference, '/') . '(\d+)$/', $reference, $matches)) {
                    return 0;
                }

                return (int) $matches[1];
            })
            ->max() ?: 0;

        return $baseReference . ($highestSequence + 1);
    }

    private function resolveClinicReferenceNumber(User $user, ?HealthProfile $healthProfile = null): string
    {
        $candidates = array_filter([
            trim((string) (optional($healthProfile)->reference_number ?? '')),
            trim((string) ($user->reference_number ?? '')),
        ], fn ($value) => $value !== '');

        foreach ($candidates as $candidate) {
            if (!$this->looksLikeIdpIdentifier($candidate)) {
                return strtoupper($candidate);
            }
        }

        return $this->generateClinicReferenceNumber($user);
    }

    private function buildHealthFormPrefill(User $user, ?Admin $linkedAdminProfile = null, ?HealthProfile $healthProfile = null): array
    {
        $linkedAdminProfile = $linkedAdminProfile ?: $this->resolveLinkedAdminProfile($user);
        $guisisAccountData = $this->isStudentAccount($user) ? $this->buildGuisisAccountData($user) : ['available' => false];
        $studentNumberReference = $this->enrolledStudentReferenceNumber($user, $healthProfile, $guisisAccountData);
        if ($studentNumberReference !== '') {
            $applicantLookup = [
                'success' => false,
                'outcome' => 'skipped_student_number',
                'message' => 'Student number from GUISIS is used for enrolled student health profiles.',
                'data' => null,
            ];
            $applicantData = null;
            $lookupOutcome = 'skipped_student_number';
            $referenceMode = 'student_number';
        } else {
            $applicantLookup = $this->fetchPuptasApplicantLookupForUser($user);
            $applicantData = is_array($applicantLookup['data'] ?? null) ? $applicantLookup['data'] : null;
            $lookupOutcome = (string) ($applicantLookup['outcome'] ?? 'not_found');
            $referenceMode = $this->resolveHealthReferenceMode($user, $linkedAdminProfile, $applicantData, $lookupOutcome);
        }
        $applicantIdentity = $this->normalizePuptasApplicantIdentity($applicantData);
        $this->persistPuptasApplicantIdentity($user, $applicantIdentity);
        $usePuptasApplicantPrefill = $referenceMode === 'admission' && is_array($applicantData) && !empty($applicantData);

        $calculatedAge = null;
        if (!empty($user->DOB)) {
            try {
                $calculatedAge = \Carbon\Carbon::parse($user->DOB)->age;
            } catch (\Throwable $exception) {
                $calculatedAge = null;
            }
        }

        $resolvedSex = $this->normalizeSexValue(
            $usePuptasApplicantPrefill
                ? (data_get($applicantData, 'sex') ?: optional($healthProfile)->sex)
                : (optional($healthProfile)->sex ?? $user->gender ?? optional($linkedAdminProfile)->gender ?? '')
        );

        $resolvedCivilStatus = trim((string) (optional($healthProfile)->civil_status ?? optional($linkedAdminProfile)->civil_status ?? ''));
        $resolvedCivilStatus = in_array($resolvedCivilStatus, ['Single', 'Married'], true) ? $resolvedCivilStatus : 'Single';

        $resolvedBirthday = (string) (
            optional($healthProfile)->birthday
            ?: ($usePuptasApplicantPrefill ? data_get($applicantData, 'birthday') : null)
            ?: $user->DOB
            ?: optional($linkedAdminProfile)->birthday
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

        $resolvedAddress = $usePuptasApplicantPrefill ? trim(implode(', ', array_filter([
            data_get($applicantData, 'street_address'),
            data_get($applicantData, 'barangay'),
            data_get($applicantData, 'city'),
            data_get($applicantData, 'province'),
        ]))) : '';
        $resolvedCourse = $this->resolveHealthFormCourse($user, $healthProfile, $applicantData);

        $applicantFirstName = trim((string) $applicantIdentity['first_name']);
        $applicantMiddleName = trim((string) $applicantIdentity['middle_name']);
        $applicantLastName = trim((string) $applicantIdentity['last_name']);
        $applicantStructuredName = trim((string) $applicantIdentity['full_name']);
        $hasOfficialApplicantIdentity = (bool) $applicantIdentity['available'];
        $resolvedReferenceNumber = match ($referenceMode) {
            'admission' => $this->resolveReferenceNumber($user, $healthProfile, $applicantData),
            'student_number' => $studentNumberReference,
            'verification_unavailable' => '',
            default => $this->resolveClinicReferenceNumber($user, $healthProfile),
        };

        return [
            'reference_mode' => $referenceMode,
            'reference_requires_validation' => $referenceMode === 'admission',
            'reference_label' => $referenceMode === 'admission'
                ? 'Admission Reference Number'
                : ($referenceMode === 'student_number'
                    ? 'Student Number'
                    : ($referenceMode === 'verification_unavailable' ? 'PUPTAS Verification' : 'Clinic Reference Number')),
            'step_1_title' => $referenceMode === 'admission'
                ? 'Admission Reference'
                : ($referenceMode === 'student_number'
                    ? 'Student Number'
                    : ($referenceMode === 'verification_unavailable' ? 'PUPTAS Verification' : 'Clinic Reference')),
            'step_1_description' => match ($referenceMode) {
                'admission' => 'Confirm your admission reference, complete your health information, then upload the required clinic documents.',
                'student_number' => 'Review your official student number, complete your health information, then upload any available clinic documents.',
                'verification_unavailable' => 'PUPTAS verification is temporarily unavailable. The form remains locked to prevent an incorrect clinic reference from being generated.',
                default => 'Review your clinic reference, complete your health information, then upload the required clinic documents.',
            },
            'puptas_verification_status' => $lookupOutcome,
            'puptas_verification_message' => trim((string) ($applicantLookup['message'] ?? '')),
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
            'reference_number' => $resolvedReferenceNumber,
            'student_number' => $this->resolveStudentNumber($user, $healthProfile, $applicantData),
            'school_year_from_puptas' => trim((string) $applicantIdentity['school_year']) !== '',
            'email' => (string) (
                $applicantIdentity['email']
                ?: optional(optional($healthProfile)->user)->email
                ?: ($user->email ?? optional($linkedAdminProfile)->email ?? '')
            ),
            'course_options' => $this->healthFormCourseOptions(),
            'course_applicable' => $this->isHealthCourseApplicable($user),
            'course_code' => $resolvedCourse['code'],
            'course_college' => $resolvedCourse['name'],
            'home_address' => trim((string) (
                optional($healthProfile)->home_address
                ?: ($resolvedAddress !== '' ? $resolvedAddress : trim((string) (optional($linkedAdminProfile)->address ?? '')))
            )),
            'zipcode' => trim((string) (
                optional($healthProfile)->zipcode
                ?: ($usePuptasApplicantPrefill ? data_get($applicantData, 'postal_code') : '')
            )),
            'school_year' => (string) (optional($healthProfile)->school_year ?? $this->resolveSchoolYear($applicantData, $user)),
            'height' => (string) ($this->extractMeasurementNumber(optional($healthProfile)->height ?? $user->height ?? '') ?? ''),
            'weight' => (string) ($this->extractMeasurementNumber(optional($healthProfile)->weight ?? $user->weight ?? '') ?? ''),
            'birthday' => $resolvedBirthday,
            'age' => $resolvedAge,
            'sex' => $resolvedSex,
            'civil_status' => $resolvedCivilStatus,
            'blood_type' => (string) (optional($healthProfile)->blood_type ?? 'Not Known'),
            'contact_number' => trim((string) (
                ($usePuptasApplicantPrefill ? data_get($applicantData, 'contactnumber') : null)
                ?: $user->contact_no
                ?: ''
            )),
            'guardian_name' => trim((string) (optional($healthProfile)->guardian_name ?? optional($linkedAdminProfile)->emergency_contact_person ?? '')),
            'cellphone' => trim((string) (
                optional($healthProfile)->cellphone
                ?: (optional($linkedAdminProfile)->emergency_contact_no ?? '')
            )),
            'landline' => (string) (optional($healthProfile)->landline ?? ''),
            'office' => trim((string) (optional($linkedAdminProfile)->office ?? '')),
            'access_level' => trim((string) (optional($linkedAdminProfile)->access_level ?? '')),
            'health_form_correction_mode' => $this->healthProfileNeedsFormCorrection($healthProfile),
            'health_form_correction_notes' => trim((string) (optional($healthProfile)->pending_reason ?? '')),
            'resubmission_required_documents' => $this->requestedHealthProfileDocuments($healthProfile),
            'existing_documents' => [
                'student_photo' => trim((string) (optional($healthProfile)->student_photo ?? '')),
                'health_declaration' => trim((string) (optional($healthProfile)->health_declaration ?? '')),
                'medical_certificate' => trim((string) (optional($healthProfile)->medical_certificate ?? '')),
                'chest_xray_result' => trim((string) (optional($healthProfile)->chest_xray_result ?? '')),
                'pwd_id_proof' => trim((string) (optional($healthProfile)->pwd_id_proof ?? '')),
            ],
            'has_illness' => (string) (optional($healthProfile)->has_illness ?? 'No'),
            'medical_history' => is_array(optional($healthProfile)->medical_history) ? $healthProfile->medical_history : [],
            'other_illness' => (string) (optional($healthProfile)->other_illness ?? ''),
            'has_disability' => (string) (optional($healthProfile)->has_disability ?? 'No'),
            'disability_type' => (string) (optional($healthProfile)->disability_type ?? ''),
            'food_allergies' => (string) (optional($healthProfile)->food_allergies ?? ''),
            'no_allergies' => (bool) (optional($healthProfile)->no_allergies ?? false),
            'medicine_allergies' => is_array(optional($healthProfile)->medicine_allergies) ? $healthProfile->medicine_allergies : [],
            'other_med_allergies' => (string) (optional($healthProfile)->other_med_allergies ?? ''),
            'is_smoker' => (string) (optional($healthProfile)->is_smoker ?? 'No'),
            'is_drinker' => (string) (optional($healthProfile)->is_drinker ?? 'No'),
            'covid_vaccinated' => (string) (optional($healthProfile)->covid_vaccinated ?? ''),
            'vaccine_history' => is_array(optional($healthProfile)->vaccine_history) ? $healthProfile->vaccine_history : [],
            'doctor_name' => (string) (optional($healthProfile)->doctor_name ?? ''),
            'med_cert_date' => (string) (optional($healthProfile)->med_cert_date ?? ''),
            'med_cert_findings' => (string) (optional($healthProfile)->med_cert_findings ?? ''),
            'med_cert_findings_details' => (string) (optional($healthProfile)->med_cert_findings_details ?? ''),
            'xray_date' => (string) (optional($healthProfile)->xray_date ?? ''),
            'xray_findings' => (string) (optional($healthProfile)->xray_findings ?? ''),
            'xray_findings_details' => (string) (optional($healthProfile)->xray_findings_details ?? ''),
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
            $structuredFullName = $this->formatDisplayNameParts($firstName, $middleName, $lastName, $suffixName);

            $fullName = $structuredFullName !== '' ? $structuredFullName : $fullName;

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

    private function healthProfileNeedsFormCorrection(?HealthProfile $healthProfile): bool
    {
        if (!$healthProfile) {
            return false;
        }

        $reason = strtolower(trim((string) $healthProfile->pending_reason));
        if ($reason === '') {
            return false;
        }

        if (str_contains($reason, 'health form correction')) {
            return true;
        }

        $status = strtolower(trim((string) $healthProfile->clearance_status));
        if (!str_contains($status, 'pending') && !str_contains($status, 'conditional')) {
            return false;
        }

        foreach ([
            'health information form',
            'health form',
            'correct address',
            'home address',
            'correct information',
            'correct details',
        ] as $needle) {
            if (str_contains($reason, $needle)) {
                return true;
            }
        }

        return false;
    }

    private function requestedHealthProfileDocuments(?HealthProfile $healthProfile): array
    {
        if (!$healthProfile) {
            return [];
        }

        return collect($healthProfile->resubmission_required_documents ?? [])
            ->filter()
            ->intersect(['student_photo', 'health_declaration', 'medical_certificate', 'chest_xray_result', 'pwd_id_proof'])
            ->values()
            ->all();
    }

    private function healthProfileFileRule(
        bool $isCorrectionMode,
        array $requestedDocuments,
        string $document,
        array $rules,
        bool $requiredOnFirstSubmission = true
    ): array {
        $presenceRule = $isCorrectionMode
            ? (in_array($document, $requestedDocuments, true) ? 'required' : 'nullable')
            : ($requiredOnFirstSubmission ? 'required' : 'nullable');

        return array_merge([$presenceRule], $rules);
    }

    private function storeHealthProfileFileOrKeep(
        Request $request,
        ?HealthProfile $existingHealthProfile,
        string $document,
        string $folder,
        array &$oldPaths
    ): ?string {
        $existingPath = trim((string) (optional($existingHealthProfile)->{$document} ?? ''));

        if (!$request->hasFile($document)) {
            return $existingPath !== '' ? $existingPath : null;
        }

        if ($existingPath !== '') {
            $oldPaths[$document] = ltrim($existingPath, '/');
        }

        return $request->file($document)->store($folder, 'public');
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
        $user = $this->promoteDesigneeAdminToStudentGuard() ?? Auth::guard('student')->user();
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

        $scheduledClosure = $workflow->activeClosure($selectedDateTime);
        if ($scheduledClosure) {
            $reopening = $scheduledClosure['ends_at']
                ? ' Reopening is expected on ' . $scheduledClosure['ends_at']->format('M d, Y g:i A') . '.'
                : '';

            return redirect()->back()->withInput()->with(
                'error',
                'That appointment time falls within a temporary clinic closure.' . $reopening . ' Please choose another schedule.'
            );
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
        $appointment->apt_id = Appointment::generateAppointmentNumber(now(), 'online');
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
                'apt_id' => $appointment->apt_id,
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
        $closureBlockedCount = 0;
        $workflow = app(ClinicWorkflowService::class);

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
                $isClosureBlocked = $workflow->activeClosure($slotDateTime) !== null;
                $isAvailable = !$isTaken && !$isPastTime && !$isClosureBlocked;

                if ($isClosureBlocked) {
                    $closureBlockedCount++;
                }

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
            'reason' => $dailyCount > 0 ? null : ($closureBlockedCount > 0 ? 'clinic_closure' : 'fully_booked'),
            'message' => $dailyCount > 0
                ? null
                : ($closureBlockedCount > 0
                    ? 'No appointments are available during the temporary clinic closure. Please choose a schedule after reopening.'
                    : 'No available time slots for this date.'),
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

    $hasLocalProfileData = collect([
        $accountProfileData['birthday'] ?? '',
        $accountProfileData['sex'] ?? '',
        $accountProfileData['civil_status'] ?? '',
        $accountProfileData['home_address'] ?? '',
        $accountProfileData['guardian_name'] ?? '',
        $accountProfileData['cellphone'] ?? '',
        $accountProfileData['reference_number'] ?? '',
        optional($linkedAdminProfile)->office ?? '',
    ])->contains(fn ($value) => trim((string) $value) !== '');

    $isEnrolled = (bool) ($guisisAccountData['available'] ?? false)
        || trim((string) ($accountProfileData['student_number'] ?? '')) !== ''
        || (bool) $user->is_health_profile_completed
        || $hasLocalProfileData;
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

    public function showStudentHealthRecordDocument(string $document)
    {
        /** @var \App\Models\User|null $user */
        $user = Auth::user();
        abort_unless($user, 403);

        $healthProfile = HealthProfile::query()->where('user_id', $user->id)->firstOrFail();

        $allowedDocuments = [
            'medical_certificate',
            'chest_xray_result',
            'student_photo',
            'health_declaration',
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

    public function resubmitHealthRecordRequirements(Request $request)
    {
        /** @var \App\Models\User|null $user */
        $user = Auth::user();
        if (!$user) {
            return redirect('/login-as-student')->with('error', 'Please login first.');
        }

        $healthProfile = HealthProfile::query()->where('user_id', $user->id)->first();
        if (!$healthProfile) {
            return redirect('/student/account?view=health-record')
                ->with('error', 'Submit your health profile before uploading replacement requirements.');
        }

        $requiredDocuments = collect($healthProfile->resubmission_required_documents ?? [])
            ->filter()
            ->intersect(['student_photo', 'health_declaration', 'medical_certificate', 'chest_xray_result', 'pwd_id_proof'])
            ->values();

        $statusNormalized = strtolower(trim((string) $healthProfile->clearance_status));
        $allowsResubmission = $statusNormalized === 'pending resubmission'
            || ($requiredDocuments->isNotEmpty() && (
                str_contains($statusNormalized, 'pending')
                || str_contains($statusNormalized, 'conditional')
                || in_array($statusNormalized, ['issued', 'fully cleared'], true)
            ));

        if (!$allowsResubmission || $requiredDocuments->isEmpty()) {
            return redirect('/student/account?view=health-record')
                ->with('error', 'There are no replacement requirements requested for this record.');
        }

        $preserveApprovalHistory = !empty($healthProfile->verified_at)
            || !empty($healthProfile->approved_by_user_id)
            || in_array(strtolower(trim((string) $healthProfile->clearance_status)), ['issued', 'fully cleared'], true);
        $pendingReasonBeforeUpload = trim((string) $healthProfile->pending_reason);
        $pendingReasonSearch = strtolower($pendingReasonBeforeUpload);
        $requiresManualComplianceAfterUpload = str_contains($pendingReasonSearch, 'others:')
            || str_contains($pendingReasonSearch, 'health information form')
            || str_contains($pendingReasonSearch, 'health form correction');

        $documentRules = [
            'student_photo' => ['required', 'image', 'mimes:jpg,jpeg,png', 'max:1024'],
            'health_declaration' => ['required', 'file', 'mimes:pdf,jpg,jpeg,png', 'max:1024'],
            'medical_certificate' => ['required', 'file', 'mimes:pdf,jpg,jpeg,png', 'max:2048'],
            'chest_xray_result' => ['required', 'file', 'mimes:pdf,jpg,jpeg,png', 'max:2048'],
            'pwd_id_proof' => ['required', 'file', 'mimes:pdf', 'max:2048'],
        ];

        $attributeNames = [
            'student_photo' => '2x2 Student Photo',
            'health_declaration' => 'Health Declaration',
            'medical_certificate' => 'Medical Certificate',
            'chest_xray_result' => 'Chest X-ray Result',
            'pwd_id_proof' => 'PWD ID Proof',
        ];

        $rules = $requiredDocuments
            ->mapWithKeys(fn ($document) => [$document => $documentRules[$document]])
            ->all();

        $request->validate($rules, [], $attributeNames);

        $storageFolders = [
            'student_photo' => 'health_profiles/photos',
            'health_declaration' => 'health_profiles/health_declarations',
            'medical_certificate' => 'health_profiles/medical_certificates',
            'chest_xray_result' => 'health_profiles/chest_xray_results',
            'pwd_id_proof' => 'health_profiles/pwd_id_proofs',
        ];

        $storedPaths = [];
        $oldPaths = [];

        try {
            foreach ($requiredDocuments as $document) {
                if (!$request->hasFile($document)) {
                    continue;
                }

                $oldPaths[$document] = ltrim((string) $healthProfile->{$document}, '/');
                $storedPaths[$document] = $request->file($document)->store($storageFolders[$document], 'public');
            }

            foreach ($storedPaths as $document => $path) {
                $healthProfile->{$document} = $path;
            }

            $healthProfile->clearance_status = $requiresManualComplianceAfterUpload ? 'Pending/Conditional' : 'For Verification';
            $healthProfile->pending_reason = $requiresManualComplianceAfterUpload ? $pendingReasonBeforeUpload : null;
            $healthProfile->documents_valid = false;
            if (!$preserveApprovalHistory) {
                $healthProfile->verified_at = null;
                $healthProfile->approved_by_user_id = null;
            }
            $healthProfile->resubmission_required_documents = null;
            $healthProfile->resubmission_requested_at = null;
            $healthProfile->resubmitted_at = now();
            $healthProfile->save();

            $user->is_health_profile_completed = 0;
            $user->save();

            foreach ($oldPaths as $oldPath) {
                $oldPath = preg_replace('#^(?:public/)?storage/#', '', $oldPath) ?? $oldPath;
                if ($oldPath !== '' && Storage::disk('public')->exists($oldPath)) {
                    Storage::disk('public')->delete($oldPath);
                }
            }

            \App\Models\ActivityLog::create([
                'user_id' => $user->id,
                'user_name' => $user->name,
                'action' => 'Health Requirements Resubmitted',
                'description' => 'Student uploaded replacement health profile requirement files.',
                'ip_address' => $request->ip(),
                'user_agent' => $request->userAgent(),
            ]);

            $successMessage = $requiresManualComplianceAfterUpload
                ? 'Replacement files uploaded successfully. Your record remains pending compliance until the remaining correction is reviewed.'
                : 'Replacement requirement files uploaded successfully. Your record is back for clinic review.';

            return redirect('/student/account?view=health-record')
                ->with('success', $successMessage);
        } catch (\Throwable $e) {
            foreach ($storedPaths as $path) {
                if ($path && Storage::disk('public')->exists($path)) {
                    Storage::disk('public')->delete($path);
                }
            }

            Log::error('Health requirement resubmission failed', [
                'user_id' => $user->id,
                'health_profile_id' => $healthProfile->id,
                'message' => $e->getMessage(),
            ]);

            return back()->withInput()
                ->with('error', 'Unable to upload replacement files right now. Please try again.');
        }
    }

    public function uploadHealthDeclaration(Request $request)
    {
        /** @var \App\Models\User|null $user */
        $user = Auth::user();
        if (!$user) {
            return redirect('/login-as-student')->with('error', 'Please login first.');
        }

        $healthProfile = HealthProfile::query()->where('user_id', $user->id)->first();
        if (!$healthProfile) {
            return redirect('/student/account?view=health-record')
                ->with('error', 'Submit your health profile before uploading your Health Declaration.');
        }

        $request->validate([
            'health_declaration' => ['required', 'file', 'mimes:pdf,jpg,jpeg,png', 'max:1024'],
        ], [], [
            'health_declaration' => 'Health Declaration',
        ]);

        $oldPath = ltrim((string) $healthProfile->health_declaration, '/');

        try {
            $newPath = $request->file('health_declaration')->store('health_profiles/health_declarations', 'public');
            $healthProfile->health_declaration = $newPath;
            $healthProfile->save();

            $oldPath = preg_replace('#^(?:public/)?storage/#', '', $oldPath) ?? $oldPath;
            if ($oldPath !== '' && Storage::disk('public')->exists($oldPath)) {
                Storage::disk('public')->delete($oldPath);
            }

            \App\Models\ActivityLog::create([
                'user_id' => $user->id,
                'user_name' => $user->name,
                'action' => 'Health Declaration Uploaded',
                'description' => 'Student uploaded a Health Declaration document.',
                'ip_address' => $request->ip(),
                'user_agent' => $request->userAgent(),
            ]);

            return redirect('/student/account?view=health-record')
                ->with('success', 'Health Declaration uploaded successfully.');
        } catch (\Throwable $e) {
            Log::error('Health Declaration upload failed', [
                'user_id' => $user->id,
                'health_profile_id' => $healthProfile->id,
                'message' => $e->getMessage(),
            ]);

            return back()->withInput()
                ->with('error', 'Unable to upload the Health Declaration right now. Please try again.');
        }
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
        $consultation = Consultation::query()
            ->where('user_id', $appointment->user_id)
            ->whereDate('consultation_date', $appointment->date)
            ->orderByDesc('time_in')
            ->first();

        return view('student.feedback', [
            'appointment' => $appointment,
            'existingFeedback' => $appointment->feedback,
            'consultation' => $consultation,
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
        $user = $this->promoteDesigneeAdminToStudentGuard() ?? Auth::guard('student')->user();
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
        'height'     => ['nullable', 'string', 'max:20', 'regex:/^\s*\d+(\.\d+)?(\s*ft)?\s*$/i'],
        'weight'     => ['nullable', 'string', 'max:20', 'regex:/^\s*\d+(\.\d+)?(\s*lbs?)?\s*$/i'],
    ], [
        'height.regex' => 'Height must be a valid number (optional unit: ft).',
        'weight.regex' => 'Weight must be a valid number (optional unit: lbs).',
    ]);

    $heightNumeric = $this->extractMeasurementNumber($validated['height'] ?? null);
    $weightNumeric = $this->extractMeasurementNumber($validated['weight'] ?? null);

    // 3. Save only clinic-controlled measurements.
    $user->height = $heightNumeric ?? $user->height;
    $user->weight = $weightNumeric ?? $user->weight;
    $user->save();

    $healthProfile = $user->healthProfile()->first();
    if ($healthProfile) {
        if ($heightNumeric !== null) {
            $healthProfile->height = $heightNumeric;
        }
        if ($weightNumeric !== null) {
            $healthProfile->weight = $weightNumeric;
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

    $existingHealthProfile = $user
        ? HealthProfile::query()->where('user_id', $user->id)->first()
        : null;
    $isHealthFormCorrectionMode = $this->healthProfileNeedsFormCorrection($existingHealthProfile);

    if ($this->hasSubmittedHealthProfile($user) && !$isHealthFormCorrectionMode) {
        return redirect('/student/account?view=health-record')
            ->with('info', 'You have already submitted your health profile.');
    }

    // Resolve the linked admin profile (Required by your view to avoid Undefined Variable error)
    $linkedAdminProfile = $this->resolveLinkedAdminProfile($user);
    $healthFormPrefill = $this->buildHealthFormPrefill($user, $linkedAdminProfile, $existingHealthProfile);
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

public function validateHealthFormReference(Request $request)
{
    /** @var \App\Models\User|null $user */
    $user = Auth::user();
    $linkedAdminProfile = $this->resolveLinkedAdminProfile($user);
    $existingHealthProfile = $user
        ? HealthProfile::query()->where('user_id', $user->id)->first()
        : null;
    $studentNumberReference = $user ? $this->enrolledStudentReferenceNumber($user, $existingHealthProfile) : '';

    if ($studentNumberReference !== '') {
        $referenceMode = 'student_number';
        $accountApplicantData = null;
        $accountLookupOutcome = 'skipped_student_number';
    } else {
        $accountLookup = $this->fetchPuptasApplicantLookupForUser($user);
        $accountApplicantData = is_array($accountLookup['data'] ?? null) ? $accountLookup['data'] : null;
        $accountLookupOutcome = (string) ($accountLookup['outcome'] ?? 'not_found');
        $referenceMode = $this->resolveHealthReferenceMode(
            $user,
            $linkedAdminProfile,
            $accountApplicantData,
            $accountLookupOutcome
        );
    }

    if ($referenceMode === 'verification_unavailable') {
        return response()->json([
            'success' => false,
            'service_unavailable' => true,
            'message' => 'PUPTAS verification is temporarily unavailable. Please try again later or contact Admissions or clinic staff.',
        ], 503);
    }

    if ($referenceMode !== 'admission') {
        $clinicReference = $referenceMode === 'student_number'
            ? $studentNumberReference
            : $this->resolveClinicReferenceNumber($user, $existingHealthProfile);
        $this->persistResolvedReferenceNumber($user, $clinicReference);

        return response()->json([
            'success' => true,
            'reference_number' => $clinicReference,
            'message' => $referenceMode === 'student_number'
                ? 'Student number is ready for this account.'
                : 'Clinic reference is ready for this account.',
        ]);
    }

    $validated = $request->validate([
        'reference_number' => ['required', 'string', 'max:120', 'regex:/^[A-Za-z0-9]+(?:-[A-Za-z0-9]+)+$/'],
    ]);

    $referenceNumber = strtoupper(trim((string) $validated['reference_number']));
    $currentReference = strtoupper(trim((string) ($user->reference_number ?? '')));

    if (
        $currentReference !== ''
        && !$this->isClinicReference($currentReference)
        && $referenceNumber === $currentReference
    ) {
        RateLimiter::clear($this->healthReferenceAttemptKey($request, $user));
        return response()->json([
            'success' => true,
            'reference_number' => $currentReference,
            'message' => 'Reference already verified.',
        ]);
    }

    if ($currentReference !== '' && !$this->isClinicReference($currentReference)) {
        return response()->json([
            'success' => false,
            'message' => 'A verified Admission Reference is already linked to this account and cannot be replaced.',
        ], 409);
    }

    $attemptKey = $this->healthReferenceAttemptKey($request, $user);

    if ($this->isLocalHealthFormTestReference($referenceNumber)) {
        $existingHealthProfile = HealthProfile::query()->where('user_id', $user->id)->first();
        $this->persistResolvedReferenceNumber($user, $referenceNumber, $existingHealthProfile);
        RateLimiter::clear($attemptKey);

        return response()->json([
            'success' => true,
            'reference_number' => $referenceNumber,
            'message' => 'Local test reference verified. This is only allowed in local testing.',
        ]);
    }

    if (RateLimiter::tooManyAttempts($attemptKey, 3)) {
        $retryAfter = RateLimiter::availableIn($attemptKey);

        return response()->json([
            'success' => false,
            'rate_limited' => true,
            'attempts_remaining' => 0,
            'retry_after_seconds' => $retryAfter,
            'retry_at' => now()->addSeconds($retryAfter)->format('g:i A'),
            'message' => 'Too many failed verification attempts. Try again at '
                . now()->addSeconds($retryAfter)->format('g:i A')
                . ' or contact Admissions or clinic staff.',
        ], 429);
    }

    $lookup = app(PuptasWebhookService::class)->fetchApplicantByReferenceNumberDetailed($referenceNumber);
    if (empty($lookup['success']) || !is_array($lookup['data'] ?? null)) {
        if (($lookup['outcome'] ?? '') === 'unavailable') {
            return response()->json([
                'success' => false,
                'service_unavailable' => true,
                'message' => 'PUPTAS verification is temporarily unavailable. Please try again later or contact Admissions or clinic staff.',
            ], 503);
        }

        RateLimiter::hit($attemptKey, 3600);
        $attemptsRemaining = max(0, 3 - RateLimiter::attempts($attemptKey));

        return response()->json([
            'success' => false,
            'attempts_remaining' => $attemptsRemaining,
            'message' => 'The reference number could not be verified for this account. '
                . ($attemptsRemaining > 0
                    ? "You have {$attemptsRemaining} attempt(s) remaining this hour."
                    : 'Manual verification is locked for one hour.'),
        ], 422);
    }

    $applicantData = $lookup['data'];
    $applicantIdpUserId = trim((string) (
        data_get($applicantData, 'idp_user_id')
        ?: data_get($applicantData, 'user.idp_user_id')
        ?: data_get($applicantData, 'user_id')
        ?: data_get($applicantData, 'user.id')
    ));
    $applicantEmail = strtolower(trim((string) (
        data_get($applicantData, 'email')
        ?: data_get($applicantData, 'user.email')
    )));

    $currentIdpUserId = trim((string) ($user->student_id ?? ''));
    $currentEmail = strtolower(trim((string) ($user->email ?? '')));

    $matchesCurrentAccount = false;
    if ($currentIdpUserId !== '' && $applicantIdpUserId !== '' && strcasecmp($currentIdpUserId, $applicantIdpUserId) === 0) {
        $matchesCurrentAccount = true;
    } elseif ($currentEmail !== '' && $applicantEmail !== '' && $currentEmail === $applicantEmail) {
        $matchesCurrentAccount = true;
    }

    if (!$matchesCurrentAccount) {
        RateLimiter::hit($attemptKey, 3600);
        $attemptsRemaining = max(0, 3 - RateLimiter::attempts($attemptKey));

        return response()->json([
            'success' => false,
            'attempts_remaining' => $attemptsRemaining,
            'message' => 'The reference number could not be verified for this account. '
                . ($attemptsRemaining > 0
                    ? "You have {$attemptsRemaining} attempt(s) remaining this hour."
                    : 'Manual verification is locked for one hour.'),
        ], 422);
    }

    $identity = $this->normalizePuptasApplicantIdentity($applicantData);
    $this->persistPuptasApplicantIdentity($user, $identity);
    $existingHealthProfile = HealthProfile::query()->where('user_id', $user->id)->first();
    $this->persistResolvedReferenceNumber($user, $referenceNumber, $existingHealthProfile);
    RateLimiter::clear($attemptKey);

    return response()->json([
        'success' => true,
        'reference_number' => $referenceNumber,
        'message' => 'Reference number verified successfully.',
    ]);
}

private function healthReferenceAttemptKey(Request $request, User $user): string
{
    return 'health-reference-verification:' . $user->id . ':' . sha1((string) $request->ip());
}

private function allowsLocalHealthFormTestReferences(): bool
{
    return app()->environment('local')
        && (bool) config('health_form.allow_local_test_references', false);
}

private function isLocalHealthFormTestReference(string $referenceNumber): bool
{
    if (!$this->allowsLocalHealthFormTestReferences()) {
        return false;
    }

    return in_array(
        strtoupper(trim($referenceNumber)),
        (array) config('health_form.local_test_references', []),
        true
    );
}

public function storeHealthForm(Request $request)
{
    /** @var \App\Models\User|null $user */
    $user = Auth::user();
    $existingHealthProfile = $user?->relationLoaded('healthProfile') && $user?->healthProfile
        ? $user->healthProfile
        : \App\Models\HealthProfile::where('user_id', $user?->id)->first();
    $isHealthFormCorrectionMode = $this->healthProfileNeedsFormCorrection($existingHealthProfile);
    $requestedCorrectionDocuments = $this->requestedHealthProfileDocuments($existingHealthProfile);

    if ($this->hasSubmittedHealthProfile($user) && !$isHealthFormCorrectionMode) {
        return redirect('/student/account?view=health-record')
            ->with('info', 'Your health profile is already submitted.');
    }

    $linkedAdminProfile = $this->resolveLinkedAdminProfile($user);
    $studentNumberReference = $user ? $this->enrolledStudentReferenceNumber($user, $existingHealthProfile) : '';
    if ($studentNumberReference !== '') {
        $accountApplicantData = null;
        $referenceMode = 'student_number';
    } else {
        $accountLookup = $this->fetchPuptasApplicantLookupForUser($user);
        $accountApplicantData = is_array($accountLookup['data'] ?? null) ? $accountLookup['data'] : null;
        $referenceMode = $this->resolveHealthReferenceMode(
            $user,
            $linkedAdminProfile,
            $accountApplicantData,
            (string) ($accountLookup['outcome'] ?? 'not_found')
        );
    }
    if ($referenceMode === 'verification_unavailable') {
        throw ValidationException::withMessages([
            'reference_number' => 'PUPTAS verification is temporarily unavailable. Please try again later or contact Admissions or clinic staff.',
        ]);
    }
    $resolvedCourse = $user
        ? $this->resolveHealthFormCourse($user, $existingHealthProfile, $accountApplicantData, $request)
        : ['code' => '', 'name' => '', 'label' => ''];
    $resolvedCourseCode = $resolvedCourse['code'];
    $resolvedCourseCollege = $resolvedCourse['name'];

    $resolvedSchoolYear = trim((string) $request->input('school_year'));
    if ($resolvedSchoolYear === '') {
        $resolvedSchoolYear = trim((string) (
            optional($existingHealthProfile)->school_year
            ?: ($user ? $this->resolveSchoolYear(null, $user) : '')
        ));
    }

    $request->merge([
        'course_code' => $resolvedCourseCode,
        'course_college' => $resolvedCourseCollege,
        'school_year' => $resolvedSchoolYear,
    ]);

    $isStudentNumberReferenceMode = $referenceMode === 'student_number';
    $applicantDocumentsRequired = $referenceMode === 'admission';
    $referenceNumberRules = $isStudentNumberReferenceMode
        ? ['required', 'string', 'max:120', 'regex:/^[A-Za-z0-9\-_]+$/']
        : ['required', 'string', 'max:120', 'regex:/^[A-Za-z0-9]+(?:-[A-Za-z0-9]+)+$/'];

    $request->validate([
        'student_id'        => 'nullable|string|max:255',
        'reference_number'  => $referenceNumberRules,
        'school_year'       => ['required', 'string', 'regex:/^\d{4}-\d{4}$/'],
        'home_address'      => 'required|string|max:255',
        'zipcode'           => 'required|string|max:20',
        'birthday'          => 'required|date',
        'student_photo'     => $this->healthProfileFileRule($isHealthFormCorrectionMode, $requestedCorrectionDocuments, 'student_photo', ['image', 'mimes:jpeg,png,jpg', 'max:1024']),
        'health_declaration' => $this->healthProfileFileRule($isHealthFormCorrectionMode, $requestedCorrectionDocuments, 'health_declaration', ['file', 'mimes:pdf,jpg,jpeg,png', 'max:1024'], $applicantDocumentsRequired),
        'age'               => 'required|numeric|min:15|max:100',
        'sex'               => 'required|string',
        'civil_status'      => 'required|string',
        'course_code'       => 'nullable|string|max:30',
        'course_college'    => 'nullable|string|max:255',
        'blood_type'        => 'required|string|max:20',
        'contact_no'        => ['required', 'string', 'max:20', 'regex:/^\d{11,20}$/'],
        'guardian_name'     => 'required|string|max:255',
        'landline'          => 'nullable|string|max:20',
        'cellphone'         => ['required', 'string', 'max:20', 'regex:/^\d{11,20}$/'],
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
        'vaccine_history.first_dose.date' => 'nullable|required_with:vaccine_history.first_dose.brand|date|after_or_equal:2021-03-01|before_or_equal:today',
        'vaccine_history.first_dose.brand' => 'nullable|required_with:vaccine_history.first_dose.date|string|max:100',
        'vaccine_history.second_dose.date' => 'nullable|required_with:vaccine_history.second_dose.brand|date|after_or_equal:2021-03-01|before_or_equal:today',
        'vaccine_history.second_dose.brand' => 'nullable|required_with:vaccine_history.second_dose.date|string|max:100',
        'vaccine_history.booster_1.date' => 'nullable|required_with:vaccine_history.booster_1.brand|date|after_or_equal:2021-03-01|before_or_equal:today',
        'vaccine_history.booster_1.brand' => 'nullable|required_with:vaccine_history.booster_1.date|string|max:100',
        'vaccine_history.booster_2.date' => 'nullable|required_with:vaccine_history.booster_2.brand|date|after_or_equal:2021-03-01|before_or_equal:today',
        'vaccine_history.booster_2.brand' => 'nullable|required_with:vaccine_history.booster_2.date|string|max:100',

        'chest_xray_result' => $this->healthProfileFileRule($isHealthFormCorrectionMode, $requestedCorrectionDocuments, 'chest_xray_result', ['file', 'mimes:pdf,jpg,jpeg,png', 'max:2048'], $applicantDocumentsRequired),
        'xray_date'         => $applicantDocumentsRequired ? 'required|date' : 'nullable|date',
        'xray_findings'     => $applicantDocumentsRequired ? 'required|string|in:Normal,With Findings,Not Sure / For Clinic Review' : 'nullable|string|in:Normal,With Findings,Not Sure / For Clinic Review',
        'xray_findings_details' => 'required_if:xray_findings,With Findings|nullable|string|max:1000',
        'has_disability'    => 'required|string',
        'disability_type'   => 'required_if:has_disability,Yes|nullable|string|max:255',
        'pwd_id_proof'      => $isHealthFormCorrectionMode
            ? $this->healthProfileFileRule(true, $requestedCorrectionDocuments, 'pwd_id_proof', ['file', 'mimes:pdf', 'max:2048'], false)
            : ['required_if:has_disability,Yes', 'file', 'mimes:pdf', 'max:2048'],
        'medical_certificate' => $this->healthProfileFileRule($isHealthFormCorrectionMode, $requestedCorrectionDocuments, 'medical_certificate', ['file', 'mimes:pdf,jpg,jpeg,png', 'max:2048'], $applicantDocumentsRequired),
        'doctor_name'       => $applicantDocumentsRequired ? 'required|string|max:255' : 'nullable|string|max:255',
        'med_cert_date'     => $applicantDocumentsRequired ? 'required|date' : 'nullable|date',
        'med_cert_findings' => $applicantDocumentsRequired ? 'required|string|in:No Findings / Normal,With Findings,Not Sure / For Clinic Review' : 'nullable|string|in:No Findings / Normal,With Findings,Not Sure / For Clinic Review',
        'med_cert_findings_details' => 'required_if:med_cert_findings,With Findings|nullable|string|max:1000',
        'health_profile_certified' => 'accepted',
    ]);

    if ($user && $this->isHealthCourseApplicable($user) && $resolvedCourseCode === '') {
        throw ValidationException::withMessages([
            'course_code' => 'Please select the correct course/program.',
        ]);
    }

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
        $booster1Date = trim((string) $request->input('vaccine_history.booster_1.date'));
        $booster2Date = trim((string) $request->input('vaccine_history.booster_2.date'));

        if ($firstDoseDate !== '' && $secondDoseDate !== '') {
            $minimumSecondDoseDate = Carbon::parse($firstDoseDate)->addWeeks(2)->startOfDay();
            $selectedSecondDoseDate = Carbon::parse($secondDoseDate)->startOfDay();

            if ($selectedSecondDoseDate->lt($minimumSecondDoseDate)) {
                $dateErrors['vaccine_history.second_dose.date'] =
                    'The 2nd Dose must be at least 2 weeks after the 1st Dose.';
            }
        }

        if ($secondDoseDate !== '' && $booster1Date !== '') {
            $minimumBooster1Date = Carbon::parse($secondDoseDate)->addWeeks(2)->startOfDay();
            $selectedBooster1Date = Carbon::parse($booster1Date)->startOfDay();

            if ($selectedBooster1Date->lt($minimumBooster1Date)) {
                $dateErrors['vaccine_history.booster_1.date'] =
                    'Booster 1 must be at least 2 weeks after the 2nd Dose.';
            }
        }

        if ($booster1Date !== '' && $booster2Date !== '') {
            $minimumBooster2Date = Carbon::parse($booster1Date)->addWeeks(2)->startOfDay();
            $selectedBooster2Date = Carbon::parse($booster2Date)->startOfDay();

            if ($selectedBooster2Date->lt($minimumBooster2Date)) {
                $dateErrors['vaccine_history.booster_2.date'] =
                    'Booster 2 must be at least 2 weeks after Booster 1.';
            }
        }

        if ($dateErrors !== []) {
            throw ValidationException::withMessages($dateErrors);
        }
    }

    $submittedVaccineHistory = [];
    if ($request->input('covid_vaccinated') === 'Yes') {
        foreach (['first_dose', 'second_dose', 'booster_1', 'booster_2'] as $doseKey) {
            $date = trim((string) $request->input("vaccine_history.{$doseKey}.date"));
            $brand = trim((string) $request->input("vaccine_history.{$doseKey}.brand"));

            if ($date !== '' || $brand !== '') {
                $submittedVaccineHistory[$doseKey] = [
                    'date' => $date,
                    'brand' => $brand,
                ];
            }
        }
    }

    if ($referenceMode === 'student_number') {
        $officialReference = $studentNumberReference;
        if ($officialReference === '' || $submittedReference !== strtoupper($officialReference)) {
            throw ValidationException::withMessages([
                'reference_number' => 'Student Number must come from your official GUISIS account before submitting the Health Profile.',
            ]);
        }
    } elseif ($referenceMode === 'admission') {
        $officialReference = strtoupper(trim((string) ($user->reference_number ?? '')));
        if ($this->isClinicReference($officialReference)) {
            $officialReference = '';
        }
        if ($officialReference === '' && $existingHealthProfile) {
            $officialReference = strtoupper(trim((string) ($existingHealthProfile->reference_number ?? '')));
            if ($this->isClinicReference($officialReference)) {
                $officialReference = '';
            }
        }

        if ($this->isLocalHealthFormTestReference($submittedReference)) {
            $officialReference = $submittedReference;
        }

        if ($officialReference === '' || $submittedReference !== $officialReference) {
            throw ValidationException::withMessages([
                'reference_number' => 'Admission Reference must come from the Admission System before submitting the Health Profile.',
            ]);
        }
    } else {
        $officialReference = $this->resolveClinicReferenceNumber($user, $existingHealthProfile);
    }

    $user->DOB = $request->input('birthday');
    $user->contact_no = $request->input('contact_no');
    $resolvedGender = trim((string) $request->input('sex'));
    if ($resolvedGender !== '') {
        $user->gender = $resolvedGender;
    }
    $user->reference_number = $officialReference;
    $user->save();

    $oldPaths = [];

    try {
        $photoPath = $this->storeHealthProfileFileOrKeep($request, $existingHealthProfile, 'student_photo', 'health_profiles/photos', $oldPaths);
        $chestXrayPath = $this->storeHealthProfileFileOrKeep($request, $existingHealthProfile, 'chest_xray_result', 'health_profiles/chest_xray_results', $oldPaths);
        $pwdIdProofPath = $this->storeHealthProfileFileOrKeep($request, $existingHealthProfile, 'pwd_id_proof', 'health_profiles/pwd_id_proofs', $oldPaths);
        $medicalCertificatePath = $this->storeHealthProfileFileOrKeep($request, $existingHealthProfile, 'medical_certificate', 'health_profiles/medical_certificates', $oldPaths);
        $healthDeclarationPath = $this->storeHealthProfileFileOrKeep($request, $existingHealthProfile, 'health_declaration', 'health_profiles/health_declarations', $oldPaths);

        $healthProfile = \App\Models\HealthProfile::updateOrCreate(
            ['user_id' => $user->id],
            [
                'student_id'         => $request->student_id,
                'reference_number'   => $officialReference,
                'school_year'        => $request->school_year,
                'home_address'       => $request->home_address,
                'zipcode'            => $request->zipcode,
                'birthday'           => $request->input('birthday'),
                'student_photo'      => $photoPath,
                'health_declaration' => $healthDeclarationPath,
                'age'                => $request->age,
                'sex'                => $request->sex,
                'civil_status'       => $request->civil_status,
                'course_college'     => $resolvedCourseCollege !== '' ? $resolvedCourseCollege : null,
                'course_code'        => \Schema::hasColumn('health_profiles', 'course_code') && $resolvedCourseCode !== '' ? $resolvedCourseCode : null,
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
                'vaccine_history'    => $submittedVaccineHistory,
                'chest_xray_result'  => $chestXrayPath,
                'xray_date'          => $request->input('xray_date'),
                'xray_findings'      => $request->input('xray_findings'),
                'xray_findings_details' => \Schema::hasColumn('health_profiles', 'xray_findings_details')
                    ? ($request->input('xray_findings') === 'With Findings'
                        ? trim((string) $request->input('xray_findings_details'))
                        : null)
                    : null,
                'has_disability'     => $request->has_disability,
                'disability_type'    => $request->disability_type,
                'pwd_id_proof'       => $pwdIdProofPath,
                'medical_certificate' => $medicalCertificatePath,
                'doctor_name'        => $request->input('doctor_name'),
                'med_cert_date'      => $request->input('med_cert_date'),
                'med_cert_findings'  => $request->input('med_cert_findings'),
                'med_cert_findings_details' => \Schema::hasColumn('health_profiles', 'med_cert_findings_details')
                    ? ($request->input('med_cert_findings') === 'With Findings'
                        ? trim((string) $request->input('med_cert_findings_details'))
                        : null)
                    : null,
                'clearance_status'   => 'For Verification',
                'pending_reason'     => null,
                'resubmission_required_documents' => null,
                'resubmission_requested_at' => null,
                'resubmitted_at'      => $isHealthFormCorrectionMode ? now() : optional($existingHealthProfile)->resubmitted_at,
                'verified_at'        => null,
            ]
        );

        foreach ($oldPaths as $oldPath) {
            $oldPath = preg_replace('#^(?:public/)?storage/#', '', $oldPath) ?? $oldPath;
            if ($oldPath !== '' && Storage::disk('public')->exists($oldPath)) {
                Storage::disk('public')->delete($oldPath);
            }
        }

        $user->is_health_profile_completed = 0;
        $user->save();

        \App\Models\ActivityLog::create([
            'user_id'     => $user->id,
            'user_name'   => $user->name,
            'action'      => $isHealthFormCorrectionMode ? 'Health Profile Correction Submitted' : 'Health Profile Completed',
            'description' => $isHealthFormCorrectionMode
                ? 'Student submitted requested Health Form corrections.'
                : 'Student completed the Health Profile requirements.',
            'ip_address'  => $request->ip(),
            'user_agent'  => $request->userAgent(),
        ]);

        if (!$isHealthFormCorrectionMode && $referenceMode === 'admission') {
            $puptasService = new \App\Services\PuptasWebhookService();
            $webhookResult = $puptasService->sendMedicalClearance(
                $officialReference,
                $request->input('student_id'),
                false
            );

            if (!$webhookResult['success']) {
                \Log::warning('PUPTAS webhook sync failed after health form submission', [
                    'user_id' => $user->id,
                    'reference_number' => $officialReference,
                    'webhook_message' => $webhookResult['message'] ?? 'Unknown error',
                ]);
            }
        }

        $successMessage = $isHealthFormCorrectionMode
            ? 'Health Form corrections submitted successfully. Your record is back for clinic review.'
            : 'Health Profile saved successfully.';

        return redirect('/student/account?view=health-record')
            ->with('success', $successMessage)
            ->with('health_profile_submitted', true)
            ->with('show_health_print_reminder', !$isHealthFormCorrectionMode);

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
            'height' => $user->height ?: '5.5 ft',
            'weight' => $user->weight ?: '132 lbs',
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
