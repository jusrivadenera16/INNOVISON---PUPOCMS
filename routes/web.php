<?php

use App\Http\Controllers\AdminAssistantController;
use App\Http\Controllers\AdminController;
use App\Http\Controllers\AdminUserController;
use App\Http\Controllers\AppointmentController;
use App\Http\Controllers\Auth\EmergencyAuthController;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Auth\RegisterController;
use App\Http\Controllers\HealthFormCategoryController;
use App\Http\Controllers\MedicalConditionController;
use App\Http\Controllers\MaintenanceController;
use App\Http\Controllers\MedicineTypeController;
use App\Http\Controllers\ReportsController;
use App\Http\Controllers\StudentAssistantController;
use App\Http\Controllers\WalkInController;
use App\Models\Announcement;
use App\Models\SystemSetting;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Schema;

if (!function_exists('resolveWorkspaceRedirectForUser')) {
    function resolveWorkspaceRedirectForUser(User $user): string
    {
        $normalizedRole = User::normalizeRole((string) ($user->user_role ?? ''));

        if ($normalizedRole === User::ROLE_SUPERADMIN) {
            return '/admin/dashboard';
        }

        $rawRole = strtolower(trim((string) ($user->user_role ?? '')));
        $userType = strtolower(trim((string) ($user->user_type ?? '')));
        $isStudentAssistant = in_array($userType, ['assistant', 'student assistant', 'student_assistant'], true)
            || in_array($rawRole, ['student_assistant', 'studentassistant', 'assistant'], true);

        if ($normalizedRole === User::ROLE_ADMIN && $isStudentAssistant) {
            return '/assistant/choose-portal';
        }

        if ($normalizedRole === User::ROLE_ADMIN) {
            return '/student/home';
        }

        return '/student/home';
    }
}

if (!function_exists('clinicMaintenanceModeEnabled')) {
    function clinicMaintenanceModeEnabled(): bool
    {
        return Schema::hasTable('system_settings')
            && SystemSetting::booleanValue('maintenance_mode_enabled', false);
    }
}

// --- PUBLIC ROUTES (No login required) ---
Route::get('/', function () {
    if (clinicMaintenanceModeEnabled()) {
        return redirect()->route('maintenance');
    }

    $user = Auth::guard('admin')->user() ?? Auth::guard('student')->user();
    if ($user instanceof User) {
        return redirect(resolveWorkspaceRedirectForUser($user));
    }

    $landingAnnouncements = collect();

    if (Schema::hasTable('announcements')) {
        $landingAnnouncements = Announcement::query()
            ->where('status', Announcement::STATUS_ACTIVE)
            ->where(function ($query) {
                $query->whereNull('expires_at')
                    ->orWhereDate('expires_at', '>=', now()->toDateString());
            })
            ->latest()
            ->take(6)
            ->get();
    }

    return view('landing', compact('landingAnnouncements'));
})->name('landing');
Route::get('/login', [LoginController::class, 'showLoginForm'])->name('login');
Route::get('/login/portal', [LoginController::class, 'redirectToIdpPortal'])->name('login.portal');
Route::get('/auth/callback', [LoginController::class, 'handleIdpCallback'])->name('auth.callback');
Route::post('/login-action', [LoginController::class, 'login']);
Route::post('/post-login-terms/acknowledge', [LoginController::class, 'acknowledgePostLoginTerms'])->name('post-login-terms.acknowledge');
Route::get('/system-admin/emergency-login', [EmergencyAuthController::class, 'showLoginForm'])->name('system-admin.emergency-login');
Route::post('/system-admin/emergency-login', [EmergencyAuthController::class, 'login'])
    ->name('system-admin.emergency-login.submit');
Route::get('/system-admin/emergency-login/enroll', [EmergencyAuthController::class, 'showEnrollment'])
    ->name('system-admin.emergency-login.enroll');
Route::post('/system-admin/emergency-login/enroll/continue', [EmergencyAuthController::class, 'continueEnrollment'])
    ->middleware('throttle:5,15')
    ->name('system-admin.emergency-login.enroll.continue');
Route::get('/system-admin/emergency-login/enroll/backup-codes', [EmergencyAuthController::class, 'showBackupCodes'])
    ->name('system-admin.emergency-login.enroll.backup-codes');
Route::post('/system-admin/emergency-login/enroll/backup-codes', [EmergencyAuthController::class, 'confirmBackupCodes'])
    ->name('system-admin.emergency-login.enroll.backup-codes.confirm');
Route::get('/system-admin/emergency-login/enroll/verify', [EmergencyAuthController::class, 'showEnrollmentVerification'])
    ->name('system-admin.emergency-login.enroll.verify');
Route::post('/system-admin/emergency-login/enroll', [EmergencyAuthController::class, 'confirmEnrollment'])
    ->middleware('throttle:10,1')
    ->name('system-admin.emergency-login.enroll.confirm');
Route::get('/system-admin/emergency-login/method', [EmergencyAuthController::class, 'showMethodChoice'])
    ->name('system-admin.emergency-login.method');
Route::post('/system-admin/emergency-login/method', [EmergencyAuthController::class, 'chooseMethod'])
    ->middleware('throttle:5,1')
    ->name('system-admin.emergency-login.method.select');
Route::get('/system-admin/emergency-login/verify', [EmergencyAuthController::class, 'showVerification'])
    ->name('system-admin.emergency-login.verify');
Route::post('/system-admin/emergency-login/verify', [EmergencyAuthController::class, 'verify'])
    ->middleware('throttle:5,15')
    ->name('system-admin.emergency-login.verify.submit');
Route::get('/maintenance', [MaintenanceController::class, 'show'])->name('maintenance');
Route::post('/register-action', [RegisterController::class, 'register']);
Route::post('/logout', [LoginController::class, 'logout'])->name('logout');
Route::get('/idp/logout', [LoginController::class, 'handleIdpLogout'])->name('idp.logout');

// --- WORKSPACE GATEWAY (Server-side session check) ---
// Direct page request that bypasses JavaScript cookie restrictions
Route::get('/clinic-workspace/gateway', [LoginController::class, 'handleWorkspaceGateway'])->name('workspace.gateway');

// --- API ROUTES (For AJAX/Frontend) ---
// These routes MUST have session middleware to access Auth guards
// Using explicit middleware stack to ensure StartSession runs
Route::middleware('web')->group(function () {
    Route::get('/api/check-session', [LoginController::class, 'apiCheckSession'])->withoutMiddleware(\App\Http\Middleware\VerifyCsrfToken::class);
    Route::get('/api/get-redirect-path', [LoginController::class, 'apiGetRedirectPath'])->withoutMiddleware(\App\Http\Middleware\VerifyCsrfToken::class);
});

// --- PUBLIC STUDENT PAGES (Guest Mode) ---
Route::get('/student/home', [AppointmentController::class, 'home'])->name('student.home');
Route::get('/student/faq', [AppointmentController::class, 'faq'])->name('student.faq');
Route::get('/student/booking', [AppointmentController::class, 'create'])->name('student.booking');

// --- PROTECTED ROUTES (Login required) ---
Route::middleware(['auth:student', 'idp.session', 'audit'])->group(function () {
    Route::middleware('role:student')->group(function () {
        Route::post('/student/skip-barcode', function () {
            session(['barcode_skipped' => true]);
            return response()->json(['status' => 'success']);
        });

        Route::get('/student/feedbacks', [AppointmentController::class, 'feedbackIndex'])->name('student.feedback.index');

        // 1. Route para ipakita ang blankong form
        Route::get('/student/health-form', [AppointmentController::class, 'showHealthForm'])->name('health.form');
        Route::get('/student/health-form/employee', [AppointmentController::class, 'showEmployeeHealthForm'])->name('health.form.employee');
        Route::get('/student/health-form/staff', [AppointmentController::class, 'showStaffHealthForm'])->name('health.form.staff');
        Route::post('/student/health-form', [AppointmentController::class, 'storeHealthForm'])
            ->name('store.health.form.fallback');
        Route::post('/student/health-form/employee', [AppointmentController::class, 'storeEmployeeHealthForm'])->name('store.health.form.employee');
        Route::post('/student/health-form/staff', [AppointmentController::class, 'storeStaffHealthForm'])->name('store.health.form.staff');
        Route::get('/student/health-form/reference/validate', [AppointmentController::class, 'validateHealthFormReference'])
            ->middleware('throttle:15,1')
            ->name('student.health_form.reference.validate');
        Route::get('/student/health-form-legacy', function () {
            return redirect()->route('health.form');
        })->name('student.health.form');

        // 2. Route para i-save ang data (Dito galing ang form submit)
        Route::post('/student/store-health-form', [AppointmentController::class, 'storeHealthForm'])->name('store.health.form');
        Route::get('/student/store-health-form', function () {
            return redirect()->route('health.form')
                ->with('error', 'Your previous submission was interrupted. Please review the form, re-upload your files, and submit again.');
        })->name('store.health.form.interrupted');
        if (app()->environment('local')) {
            Route::post('/student/health-form/testing-skip', [AppointmentController::class, 'testingSkipHealthForm'])
                ->name('student.health_form.testing_skip');
        }
        Route::get('/student/health-form/print', [AppointmentController::class, 'printHealthForm'])->name('student.health_form.print');
        Route::get('/student/health-form/download', [AppointmentController::class, 'downloadHealthForm'])->name('student.health_form.download');
        Route::get('/student/health-form/submissions/{submission}', [AppointmentController::class, 'showHealthFormSubmissionPdf'])->name('student.health_form.submission');
        Route::get('/student/health-record/document/{document}', [AppointmentController::class, 'showStudentHealthRecordDocument'])
            ->name('student.health_record.document');
        Route::get('/student/health-record/signature', [AppointmentController::class, 'showStudentHealthRecordSignature'])
            ->name('student.health_record.signature');
        Route::post('/student/health-record/resubmit', [AppointmentController::class, 'resubmitHealthRecordRequirements'])
            ->name('student.health_record.resubmit');
        Route::post('/student/health-record/documents', [AppointmentController::class, 'uploadHealthRecordDocuments'])
            ->name('student.health_record.documents');
        Route::post('/student/health-record/health-declaration', [AppointmentController::class, 'uploadHealthDeclaration'])
            ->name('student.health_record.health_declaration');
        Route::post('/student/health-record/e-sign', [AppointmentController::class, 'uploadHealthRecordSignature'])
            ->name('student.health_record.e_signature');
        Route::post('/student/health-record/e-sign/remove', [AppointmentController::class, 'removeHealthRecordSignature'])
            ->name('student.health_record.e_signature.remove');

        Route::get('/student/account', [AppointmentController::class, 'account']);
        Route::get('/student/history', [AppointmentController::class, 'history']);
        Route::post('/student/appointments/store', [AppointmentController::class, 'store']);
        Route::get('/student/appointments/availability', [AppointmentController::class, 'availability'])->name('student.appointments.availability');
        Route::post('/student/appointments/{id}/cancel', [AppointmentController::class, 'cancel']);
        Route::post('/student/update-contact', [AppointmentController::class, 'updateContact'])->name('student.updateContact');

        Route::get('/student/barcode-register', [AppointmentController::class, 'barcodeRegister'])->name('barcode.register');
        Route::post('/student/barcode-register', [AppointmentController::class, 'storeBarcode'])->name('barcode.store');
        Route::post('/student/barcode-validate', [AppointmentController::class, 'validateBarcodeScan'])->name('barcode.validate');
        Route::post('/student/reset-barcode', [AppointmentController::class, 'resetBarcode'])->name('barcode.reset');
        Route::get('/student/notifications/{notificationId}', [AppointmentController::class, 'openNotification'])->name('student.notifications.open');
        Route::post('/student/notifications/mark-all-read', [AppointmentController::class, 'markAllNotificationsRead'])->name('student.notifications.read_all');
        Route::post('/student/notifications/preferences', [AppointmentController::class, 'updateNotificationPreferences'])->name('student.notifications.preferences');
        Route::get('/student/appointments/{appointment}/feedback', [AppointmentController::class, 'showFeedbackForm'])->name('student.feedback.show');
        Route::post('/student/appointments/{appointment}/feedback', [AppointmentController::class, 'storeFeedback'])->name('student.feedback.store');
    });

    Route::get('/account', [AppointmentController::class, 'index'])->name('account');
    Route::get('/barcode-register', [AppointmentController::class, 'barcodeRegister'])->name('barcode.legacy.register');
    Route::post('/barcode-store', [AppointmentController::class, 'storeBarcode'])->name('barcode.legacy.store');
    Route::post('/barcode-validate', [AppointmentController::class, 'validateBarcodeScan'])->name('barcode.legacy.validate');
    Route::post('/barcode-reset', [AppointmentController::class, 'resetBarcode'])->name('barcode.legacy.reset');

    Route::get('/fetch-user/{student_id}', [AppointmentController::class, 'fetchUser']);
});

Route::middleware(['auth:admin', 'idp.session', 'audit'])->group(function () {
    Route::middleware('role:admin')->group(function () {
        Route::get('/assistant/choose-portal', [LoginController::class, 'showStudentAssistantPortalChooser'])->name('assistant.choose-portal');
        Route::get('/assistant/enter-student', [LoginController::class, 'enterStudentPortal'])->name('assistant.enter-student');
        Route::get('/assistant/enter-admin', [LoginController::class, 'enterAdminPortal'])->name('assistant.enter-admin');
    });

    Route::get('/health-records', [AdminController::class, 'viewHealth'])
        ->middleware(['role:superadmin,admin', 'module.permission:health_records.view'])
        ->name('admin.health_records');
    Route::get('/health-records/stats', [AdminController::class, 'healthRecordStats'])
        ->middleware(['role:superadmin,admin', 'module.permission:health_records.view'])
        ->name('admin.health_records.stats');
    Route::get('/health-profile/{id}', [AdminController::class, 'showHealth'])
        ->middleware(['role:superadmin,admin', 'module.permission:health_records.view'])
        ->name('admin.show_health');
    Route::get('/health-profile/{id}/plain', [AdminController::class, 'showHealthPlain'])
        ->middleware(['role:superadmin,admin', 'module.permission:health_records.view'])
        ->name('admin.show_health_plain');
    Route::get('/health-profile/{id}/pdf', [AdminController::class, 'exportHealthPdf'])
        ->middleware(['role:superadmin,admin', 'module.permission:health_records.view'])
        ->name('admin.health_pdf');
    Route::post('/health-profile/{id}/resync-puptas', [AdminController::class, 'resyncPuptasHealthProfile'])
        ->middleware(['role:superadmin,admin', 'module.permission:health_records.update_assessment'])
        ->name('admin.health_profile.resync_puptas');
    Route::post('/health-profile/{id}/request-resubmission', [AdminController::class, 'requestHealthProfileResubmission'])
        ->middleware(['role:superadmin,admin', 'module.permission:health_records.request_resubmission'])
        ->name('admin.health_profile.request_resubmission');
    Route::post('/health-profile/{id}/request-health-form', [AdminController::class, 'requestNewHealthForm'])
        ->middleware(['role:superadmin,admin', 'module.permission:health_records.request_resubmission'])
        ->name('admin.health_profile.request_health_form');
    Route::post('/health-profile/{id}/return-to-pending', [AdminController::class, 'returnHealthProfileToPending'])
        ->middleware(['role:superadmin,admin', 'module.permission:health_records.request_resubmission'])
        ->name('admin.health_profile.return_to_pending');
    Route::post('/health-form-submissions/{submission}/status', [AdminController::class, 'updateHealthFormSubmissionStatus'])
        ->middleware(['role:superadmin,admin', 'module.permission:health_records.review_documents'])
        ->name('admin.health_form_submissions.status');
    Route::get('/health-form-submissions/{submission}/pdf', [AdminController::class, 'showHealthFormSubmissionPdf'])
        ->middleware(['role:superadmin,admin', 'module.permission:health_records.view'])
        ->name('admin.health_form_submissions.pdf');
    Route::post('/health-profile/{id}/for-final-review', [AdminController::class, 'markHealthProfileForFinalReview'])
        ->middleware(['role:superadmin,admin', 'module.permission:health_records.review_documents'])
        ->name('admin.health_profile.for_final_review');
    Route::post('/health-profile/{id}/for-approval', [AdminController::class, 'markHealthProfileForApproval'])
        ->middleware(['role:superadmin,admin', 'module.permission:health_records.review_documents'])
        ->name('admin.health_profile.for_approval');
    Route::post('/health-profile/medical-assessment-upload', [AdminController::class, 'uploadMedicalAssessmentCopy'])
        ->middleware(['role:superadmin,admin,nurse', 'module.permission:health_records.update_assessment'])
        ->name('admin.medical_assessment_upload');
    Route::get('/health-profile/{id}/sign', [AdminController::class, 'showSignPage'])
        ->middleware('role:superadmin')
        ->name('admin.sign_page');
    Route::put('/health-profile/{id}/update', [AdminController::class, 'updateClearance'])
        ->middleware('role:superadmin')
        ->name('admin.update_clearance');

    Route::middleware('role:superadmin,admin')->group(function () {
        Route::post('/admin/assistant/intent', [AdminAssistantController::class, 'handle'])->name('admin.assistant.intent');
        Route::post('/assistant/intent', [AdminAssistantController::class, 'handle'])
            ->middleware('assistant.schedule')
            ->name('assistant.intent');

        Route::get('/admin/dashboard', [AdminController::class, 'dashboard'])->name('admin.dashboard');
        Route::get('/admin/appointments', [AdminController::class, 'appointments'])->middleware('module.permission:appointments.view')->name('admin.appointments');
        Route::get('/admin/appointments/{id}/{status}', [AdminController::class, 'updateStatus'])->middleware('module.permission:appointments.status')->name('admin.appointments.status');
        Route::post('/admin/appointments/{id}/reschedule', [AdminController::class, 'reschedule'])->middleware('module.permission:appointments.reschedule')->name('admin.appointments.reschedule');

        Route::get('/admin/inventory', [AdminController::class, 'inventory'])->middleware('module.permission:inventory.view')->name('admin.inventory');

        Route::get('/admin/walkin', [WalkInController::class, 'index'])->middleware('module.permission:walkin.view')->name('walkin.index');
        Route::get('/admin/walkin/get-student', [WalkInController::class, 'getStudent'])->middleware('module.permission:walkin.scan_id|walkin.register_patient|walkin.encode_assessment|walkin.review_submission|walkin.employee_lookup')->name('walkin.getStudent');
        Route::get('/admin/walkin/final-review-applicants', [WalkInController::class, 'finalReviewApplicants'])->middleware('module.permission:walkin.review_submission')->name('walkin.final-review-applicants');
        Route::post('/admin/walkin/verify-id-ai', [WalkInController::class, 'verifyStudentIdWithAi'])->middleware('module.permission:walkin.scan_id')->name('walkin.verify-id-ai');
        Route::post('/admin/walkin/register', [WalkInController::class, 'registerStudent'])->middleware('module.permission:walkin.register_patient')->name('walkin.registerStudent');
        Route::get('/admin/walkin/form/{student_id}', [WalkInController::class, 'showWalkinForm'])->middleware('module.permission:walkin.scan_id|walkin.register_patient')->name('walkin.form');
        Route::get('/admin/walkin/health-form/{healthProfile}', [WalkInController::class, 'showApplicantHealthForm'])->middleware('module.permission:walkin.encode_assessment|walkin.review_submission')->name('walkin.healthForm');
        Route::get('/admin/walkin/document/{healthProfile}/{document}', [WalkInController::class, 'showApplicantDocument'])->middleware('module.permission:walkin.encode_assessment|walkin.review_submission')->name('walkin.document');
        Route::get('/admin/walkin/employee-health-form/{employeeProfile}', [WalkInController::class, 'showEmployeeHealthForm'])->middleware('module.permission:walkin.employee_view')->name('walkin.employeeHealthForm');
        Route::get('/admin/walkin/employee-document/{employeeProfile}/{document}', [WalkInController::class, 'showEmployeeDocument'])->middleware('module.permission:walkin.employee_view')->name('walkin.employeeDocument');
        Route::get('/admin/walkin/staff-health-form/{staffProfile}', [WalkInController::class, 'showStaffHealthForm'])->middleware('module.permission:walkin.employee_view')->name('walkin.staffHealthForm');
        Route::get('/admin/walkin/staff-document/{staffProfile}/{document}', [WalkInController::class, 'showStaffDocument'])->middleware('module.permission:walkin.employee_view')->name('walkin.staffDocument');
        Route::post('/admin/walkin/health-profile-information/{healthProfile}', [WalkInController::class, 'updateHealthProfileInformation'])->middleware('module.permission:walkin.encode_assessment')->name('walkin.health-profile-information.update');
        Route::post('/admin/walkin/store', [WalkInController::class, 'store'])->middleware('module.permission:walkin.encode_assessment')->name('walkin.store');
        Route::post('/admin/walkin/applicant-encoding', [WalkInController::class, 'saveApplicantEncoding'])->middleware('module.permission:walkin.encode_assessment')->name('admin.walkin.applicant_encoding');
        Route::post('/admin/walkin/final-review/time-in', [WalkInController::class, 'markFinalReviewTimeIn'])->middleware(['module.permission:walkin.final_review', 'role:superadmin'])->name('admin.walkin.final_review.time_in');
        Route::post('/admin/walkin/approve-applicant', [WalkInController::class, 'approveApplicant'])->middleware(['module.permission:walkin.final_review', 'role:superadmin'])->name('admin.walkin.approve_applicant');
        Route::post('/admin/walkin/applicant-final-review-draft', [WalkInController::class, 'saveApplicantFinalReviewDraft'])->middleware(['module.permission:walkin.final_review', 'role:superadmin'])->name('admin.walkin.applicant_final_review_draft');
        Route::post('/admin/walkin/employee-draft', [WalkInController::class, 'saveEmployeeDraft'])->middleware(['module.permission:walkin.final_review', 'role:superadmin'])->name('admin.walkin.employee_draft');

        Route::get('/admin/reports', [AdminController::class, 'reports'])->middleware('module.permission:reports.view')->name('admin.reports');
        Route::get('/admin/reports/digital-logbook', [ReportsController::class, 'digitalLogbook'])->middleware('module.permission:reports.digital_logbook')->name('reports.digital-logbook');
        Route::get('/admin/reports/mar', [ReportsController::class, 'marReport'])->middleware('module.permission:reports.mar')->name('reports.mar');
        Route::get('/admin/reports/inventory-summary', [AdminController::class, 'inventorySummary'])->middleware('module.permission:reports.inventory_summary')->name('reports.inventory-summary');
        Route::get('/admin/reports/daily-treatment-record', [ReportsController::class, 'dailyTreatmentRecord'])->middleware('module.permission:reports.digital_logbook')->name('reports.daily-treatment-record');
        Route::get('/admin/reports/appointment-statistics', [ReportsController::class, 'appointmentStatistics'])->middleware('module.permission:reports.appointment_statistics')->name('reports.appointment-statistics');
        Route::get('/admin/reports/appointment-history', [ReportsController::class, 'appointmentHistory'])->middleware('module.permission:reports.appointment_statistics')->name('reports.appointment-history');
        Route::get('/admin/reports/appointment-history/print', [ReportsController::class, 'printAppointmentHistory'])
            ->middleware(['module.permission:reports.export_reports', 'superadmin.export'])
            ->name('reports.appointment-history-print');
        Route::get('/admin/reports/health-forms', [ReportsController::class, 'healthFormsReport'])->middleware('module.permission:reports.health_forms')->name('reports.health-forms');
        Route::get('/admin/reports/health-forms/applicants-list', [ReportsController::class, 'healthFormsApplicantsList'])->middleware('module.permission:reports.health_forms')->name('reports.health-forms.applicants-list');
        Route::get('/admin/reports/health-forms/export', [ReportsController::class, 'exportHealthForms'])
            ->middleware(['module.permission:reports.export_reports', 'superadmin.export'])
            ->name('reports.health-forms.export');
        Route::get('/admin/reports/health-forms-logbook', [ReportsController::class, 'healthFormsLogbook'])->middleware('module.permission:reports.health_forms')->name('reports.health-forms-logbook');
        Route::get('/admin/reports/health-forms-logbook/export', [ReportsController::class, 'exportHealthFormsLogbook'])
            ->middleware(['module.permission:reports.export_reports', 'superadmin.export'])
            ->name('reports.health-forms-logbook.export');
        Route::get('/admin/reports/feedbacks', [ReportsController::class, 'feedbackReport'])->middleware('module.permission:reports.feedbacks')->name('reports.feedbacks');
        Route::middleware(['module.permission:reports.export_reports', 'superadmin.export'])->group(function () {
            Route::get('/admin/reports/export-hub', [ReportsController::class, 'exportHub'])->name('reports.exportHub');
            Route::get('/admin/reports/export-hub/mar', [ReportsController::class, 'exportReportsMar'])->name('reports.exportHub.mar');
            Route::get('/admin/reports/export-hub/inventory', [ReportsController::class, 'exportReportsInventory'])->name('reports.exportHub.inventory');
            Route::get('/admin/reports/export-hub/appointments', [ReportsController::class, 'exportReportsAppointments'])->name('reports.exportHub.appointments');
            Route::get('/admin/reports/export-hub/audit-trail', [ReportsController::class, 'exportReportsAuditTrail'])->middleware('role:superadmin')->name('reports.exportHub.audit-trail');
            Route::get('/admin/reports/export-hub/health-forms', [ReportsController::class, 'exportReportsHealthForms'])->name('reports.exportHub.health-forms');
            Route::get('/admin/reports/print-reports', [ReportsController::class, 'printReport'])->name('reports.print');
        });
        Route::get('/admin/notifications/feed', [AdminController::class, 'notificationsFeed'])->name('admin.notifications.feed');
        Route::post('/admin/notifications/mark-all-read', [AdminController::class, 'markAllAdminNotificationsRead'])->name('admin.notifications.read_all');
        Route::get('/admin/announcements', [AdminController::class, 'announcements'])->middleware('module.permission:announcements.view')->name('admin.announcements');
        Route::post('/admin/announcements', [AdminController::class, 'storeAnnouncement'])->middleware('module.permission:announcements.publish')->name('admin.announcements.store');
        Route::patch('/admin/announcements/{announcement}/archive', [AdminController::class, 'archiveAnnouncement'])->middleware('module.permission:announcements.archive')->name('admin.announcements.archive');
        Route::delete('/admin/announcements/{announcement}', [AdminController::class, 'destroyAnnouncement'])->middleware('module.permission:announcements.archive')->name('admin.announcements.destroy');
        Route::middleware('role:superadmin')->group(function () {
            Route::get('/admin/user-management', [AdminUserController::class, 'index'])->name('admin.user-management');
            Route::get('/admin/user-management/account-access', [AdminUserController::class, 'accountAccess'])->name('admin.user-management.account-access');
            Route::get('/admin/user-management/admin-hub', [AdminUserController::class, 'adminHub'])->name('admin.user-management.admin-hub');
            Route::post('/admin/user-management/from-lookup', [AdminUserController::class, 'storeFromLookup'])->name('admin.user-management.store-from-lookup');
            Route::put('/admin/user-management/{user}', [AdminUserController::class, 'update'])->name('admin.user-management.update');
            Route::delete('/admin/user-management/{user}/account', [AdminUserController::class, 'deleteAccount'])->name('admin.user-management.delete-account');
            Route::delete('/admin/user-management/{user}', [AdminUserController::class, 'destroy'])->name('admin.user-management.destroy');
            Route::put('/admin/user-management/admin-hub/{admin}', [AdminUserController::class, 'updateAdminHub'])->name('admin.user-management.admin-hub.update');
            Route::delete('/admin/user-management/admin-hub/{admin}', [AdminUserController::class, 'destroyAdminHub'])->name('admin.user-management.admin-hub.destroy');
            Route::delete('/admin/user-management/admin-hub/{admin}/delete-record', [AdminUserController::class, 'deleteAdminHubRecord'])->name('admin.user-management.admin-hub.delete-record');
        });

        Route::middleware('role:superadmin')->group(function () {
            Route::get('/admin/developer-tools', [AdminController::class, 'developerTools'])->name('admin.developer-tools');
            Route::get('/admin/api-testing', [AdminController::class, 'apiTesting'])->name('admin.api-testing');
            Route::get('/admin/api/health-monitor', [AdminController::class, 'apiHealthMonitor'])->name('admin.api.health-monitor');
            Route::get('/admin/api/error-logs', [AdminController::class, 'apiErrorLogs'])->name('admin.api.error-logs');
            Route::get('/admin/api/system-status', [AdminController::class, 'apiSystemStatus'])->name('admin.api.system-status');
            Route::put('/admin/integration-pin/settings', [AdminController::class, 'updateIntegrationPinSettings'])->name('admin.integration-pin.update');
            Route::post('/admin/integration-pin/reset', [AdminController::class, 'resetIntegrationPin'])->name('admin.integration-pin.reset');
            Route::get('/admin/integration-pin/status', [AdminController::class, 'integrationPinStatus'])->name('admin.integration-pin.status');
            Route::post('/admin/integration-pin/verify', [AdminController::class, 'verifyIntegrationPin'])->name('admin.integration-pin.verify');
            Route::post('/admin/reset-key/verify', [AdminController::class, 'verifyResetKey'])->name('admin.reset-key.verify');
            Route::put('/admin/emergency-credentials', [AdminController::class, 'updateEmergencyCredentials'])->name('admin.emergency-credentials.update');
            Route::put('/admin/maintenance-policy', [AdminController::class, 'updateMaintenancePolicy'])->name('admin.maintenance-policy.update');
            Route::get('/admin/integration-tokens', [AdminController::class, 'integrationTokens'])->name('admin.integration-tokens');
            Route::get('/admin/integration-tokens/docs', [AdminController::class, 'integrationTokensDocs'])->name('admin.integration-tokens.docs');
            Route::get('/admin/integration-tokens/activity', [AdminController::class, 'integrationTokensActivity'])->name('admin.integration-tokens.activity');
            Route::post('/admin/integration-tokens/generate', [AdminController::class, 'generateIntegrationToken'])->name('admin.integration-tokens.generate');
            Route::post('/admin/integration-tokens/revoke', [AdminController::class, 'revokeIntegrationToken'])->name('admin.integration-tokens.revoke');
            Route::post('/admin/integration-clients/store', [AdminController::class, 'createIntegrationClient'])->name('admin.integration-clients.store');
        });
        Route::get('/admin/activity-logs', [AdminController::class, 'indexLogs'])
            ->middleware('role:superadmin')
            ->name('admin.logs');
    });

    // Super Admin-only routes
    Route::middleware('role:superadmin')->group(function () {
        Route::put('/admin/api-testing/database/{table}/{id}', [AdminController::class, 'updateApiTestingDatabaseRecord'])->name('admin.api-testing.database.update');
        Route::delete('/admin/api-testing/database/{table}/{id}', [AdminController::class, 'deleteApiTestingDatabaseRecord'])->name('admin.api-testing.database.delete');

        Route::get('/admin/student-assistants', [StudentAssistantController::class, 'index'])->name('admin.student-assistants.index');
        Route::post('/admin/student-assistants', [StudentAssistantController::class, 'store'])->name('admin.student-assistants.store');
        Route::put('/admin/student-assistants/{assistant}', [StudentAssistantController::class, 'update'])->name('admin.student-assistants.update');
        Route::delete('/admin/student-assistants/{assistant}', [StudentAssistantController::class, 'destroy'])->name('admin.student-assistants.destroy');
    });

    Route::middleware('module.permission:settings.view')->group(function () {
        Route::get('/admin/settings', [AdminController::class, 'settings'])->name('admin.settings');
        Route::get('/admin/settings/personal-information', [AdminController::class, 'settingsPersonal'])->middleware('module.permission:settings.personal')->name('admin.settings.personal');
        Route::get('/admin/settings/clinic-information', [AdminController::class, 'settingsClinic'])->middleware('module.permission:settings.clinic')->name('admin.settings.clinic');
        Route::get('/admin/settings/system-preferences', [AdminController::class, 'settingsPreferences'])->middleware('module.permission:settings.preferences')->name('admin.settings.preferences');
        Route::get('/admin/settings/medical-configuration', [AdminController::class, 'settingsMedicalConfiguration'])->middleware('module.permission:settings.medical')->name('admin.settings.medical');
        Route::get('/admin/settings/faqs', [AdminController::class, 'settingsFaqs'])->middleware('module.permission:settings.faqs')->name('admin.settings.faqs');
        Route::post('/admin/settings/faqs', [AdminController::class, 'storeFaq'])->middleware('module.permission:settings.faqs')->name('admin.settings.faqs.store');
        Route::post('/admin/settings/faqs/category/rename', [AdminController::class, 'renameFaqCategory'])->middleware('module.permission:settings.faqs')->name('admin.settings.faqs.category.rename');
        Route::put('/admin/settings/faqs/{faq}', [AdminController::class, 'updateFaq'])->middleware('module.permission:settings.faqs')->name('admin.settings.faqs.update');
        Route::delete('/admin/settings/faqs/{faq}', [AdminController::class, 'destroyFaq'])->middleware('module.permission:settings.faqs')->name('admin.settings.faqs.destroy');
        Route::put('/admin/settings/update', [AdminController::class, 'updateSettings'])->name('admin.settings.update');
        Route::put('/admin/profile/update', [AdminController::class, 'updateProfile'])->middleware('module.permission:settings.personal')->name('admin.profile.update');
    });

    Route::middleware('module.permission:inventory.add_stock')->group(function () {
        Route::post('/admin/inventory/{id}/restock', [AdminController::class, 'restockItem'])->name('admin.inventory.restock');
    });
    Route::middleware('module.permission:inventory.import')->group(function () {
        Route::post('/admin/inventory/import/analyze', [AdminController::class, 'analyzeInventoryImport'])->name('admin.inventory.import.analyze');
        Route::post('/admin/inventory/import/commit', [AdminController::class, 'commitInventoryImport'])->name('admin.inventory.import.commit');
        Route::post('/admin/inventory/import/clear', [AdminController::class, 'clearInventoryImportPreview'])->name('admin.inventory.import.clear');
    });
    Route::middleware('module.permission:inventory.manage')->group(function () {
        Route::post('/admin/inventory/store', [AdminController::class, 'storeItem'])->name('admin.inventory.store');
        Route::post('/admin/inventory/{id}/issue', [AdminController::class, 'issueStock'])->name('admin.inventory.issue');
        Route::put('/admin/inventory/{id}', [AdminController::class, 'updateItem'])->name('admin.inventory.update');
        Route::delete('/admin/inventory/{id}', [AdminController::class, 'deleteItem'])->name('admin.inventory.delete');
    });

    Route::middleware('module.permission:settings.medical')->group(function () {
        Route::get('/admin/reports/manage-mar', [ReportsController::class, 'manageMar'])->name('admin.reports.manage-mar');
        Route::get('/admin/reports/manage-medicine-types', [MedicineTypeController::class, 'index'])->name('admin.reports.manage-medicine-types');
        Route::get('/admin/reports/manage-health-form-categories', [HealthFormCategoryController::class, 'index'])->name('admin.reports.manage-health-form-categories');
        Route::put('/admin/conditions/{id}', [ReportsController::class, 'update'])->name('conditions.update');
        Route::post('/admin/medical-conditions', [MedicalConditionController::class, 'store'])->name('conditions.store');
        Route::delete('/admin/medical-conditions/{id}', [MedicalConditionController::class, 'destroy'])->name('conditions.destroy');
        Route::post('/admin/medicine-types', [MedicineTypeController::class, 'store'])->name('medicine-types.store');
        Route::delete('/admin/medicine-types/{id}', [MedicineTypeController::class, 'destroy'])->name('medicine-types.destroy');
        Route::post('/admin/health-form-categories', [HealthFormCategoryController::class, 'store'])->name('health-form-categories.store');
        Route::delete('/admin/health-form-categories/{id}', [HealthFormCategoryController::class, 'destroy'])->name('health-form-categories.destroy');
    });

    // Admin prefixed entry points (same modules, different UI context)
    Route::middleware(['role:admin', 'assistant.schedule'])->prefix('assistant')->name('assistant.')->group(function () {
        Route::get('/dashboard', [AdminController::class, 'dashboard'])->name('dashboard');
        Route::get('/appointments', [AdminController::class, 'appointments'])->middleware('module.permission:appointments.view')->name('appointments');
        Route::get('/appointments/{id}/{status}', [AdminController::class, 'updateStatus'])->middleware('module.permission:appointments.status')->name('appointments.status');
        Route::post('/appointments/{id}/reschedule', [AdminController::class, 'reschedule'])->middleware('module.permission:appointments.reschedule')->name('appointments.reschedule');
        Route::get('/inventory', [AdminController::class, 'inventory'])->middleware('module.permission:inventory.view')->name('inventory');

        Route::get('/walkin', [WalkInController::class, 'index'])->middleware('module.permission:walkin.view')->name('walkin.index');
        Route::get('/walkin/get-student', [WalkInController::class, 'getStudent'])->middleware('module.permission:walkin.scan_id|walkin.register_patient|walkin.encode_assessment|walkin.review_submission|walkin.employee_lookup')->name('walkin.getStudent');
        Route::get('/walkin/final-review-applicants', [WalkInController::class, 'finalReviewApplicants'])->middleware('module.permission:walkin.review_submission')->name('walkin.final-review-applicants');
        Route::post('/walkin/verify-id-ai', [WalkInController::class, 'verifyStudentIdWithAi'])->middleware('module.permission:walkin.scan_id')->name('walkin.verify-id-ai');
        Route::post('/walkin/register', [WalkInController::class, 'registerStudent'])->middleware('module.permission:walkin.register_patient')->name('walkin.registerStudent');
        Route::get('/walkin/form/{student_id}', [WalkInController::class, 'showWalkinForm'])->middleware('module.permission:walkin.scan_id|walkin.register_patient')->name('walkin.form');
        Route::get('/walkin/health-form/{healthProfile}', [WalkInController::class, 'showApplicantHealthForm'])->middleware('module.permission:walkin.encode_assessment|walkin.review_submission')->name('walkin.healthForm');
        Route::get('/walkin/document/{healthProfile}/{document}', [WalkInController::class, 'showApplicantDocument'])->middleware('module.permission:walkin.encode_assessment|walkin.review_submission')->name('walkin.document');
        Route::get('/walkin/employee-health-form/{employeeProfile}', [WalkInController::class, 'showEmployeeHealthForm'])->middleware('module.permission:walkin.employee_view')->name('walkin.employeeHealthForm');
        Route::get('/walkin/employee-document/{employeeProfile}/{document}', [WalkInController::class, 'showEmployeeDocument'])->middleware('module.permission:walkin.employee_view')->name('walkin.employeeDocument');
        Route::get('/walkin/staff-health-form/{staffProfile}', [WalkInController::class, 'showStaffHealthForm'])->middleware('module.permission:walkin.employee_view')->name('walkin.staffHealthForm');
        Route::get('/walkin/staff-document/{staffProfile}/{document}', [WalkInController::class, 'showStaffDocument'])->middleware('module.permission:walkin.employee_view')->name('walkin.staffDocument');
        Route::post('/walkin/health-profile-information/{healthProfile}', [WalkInController::class, 'updateHealthProfileInformation'])->middleware('module.permission:walkin.encode_assessment')->name('walkin.health-profile-information.update');
        Route::post('/walkin/store', [WalkInController::class, 'store'])->middleware('module.permission:walkin.encode_assessment')->name('walkin.store');
        Route::post('/walkin/applicant-encoding', [WalkInController::class, 'saveApplicantEncoding'])->middleware('module.permission:walkin.encode_assessment')->name('walkin.applicant_encoding');
        Route::post('/walkin/final-review/time-in', [WalkInController::class, 'markFinalReviewTimeIn'])->middleware(['module.permission:walkin.final_review', 'role:superadmin'])->name('walkin.final_review.time_in');
        Route::post('/walkin/approve-applicant', [WalkInController::class, 'approveApplicant'])->middleware(['module.permission:walkin.final_review', 'role:superadmin'])->name('walkin.approve_applicant');
        Route::post('/walkin/applicant-final-review-draft', [WalkInController::class, 'saveApplicantFinalReviewDraft'])->middleware(['module.permission:walkin.final_review', 'role:superadmin'])->name('walkin.applicant_final_review_draft');

        Route::get('/reports', [AdminController::class, 'reports'])->middleware('module.permission:reports.view')->name('reports');
        Route::get('/reports/digital-logbook', [ReportsController::class, 'digitalLogbook'])->middleware('module.permission:reports.digital_logbook')->name('reports.digital-logbook');
        Route::get('/reports/mar', [ReportsController::class, 'marReport'])->middleware('module.permission:reports.mar')->name('reports.mar');
        Route::get('/reports/inventory-summary', [AdminController::class, 'inventorySummary'])->middleware('module.permission:reports.inventory_summary')->name('reports.inventory-summary');
        Route::get('/reports/daily-treatment-record', [ReportsController::class, 'dailyTreatmentRecord'])->middleware('module.permission:reports.digital_logbook')->name('reports.daily-treatment-record');
        Route::get('/reports/appointment-statistics', [ReportsController::class, 'appointmentStatistics'])->middleware('module.permission:reports.appointment_statistics')->name('reports.appointment-statistics');
        Route::get('/reports/appointment-history', [ReportsController::class, 'appointmentHistory'])->middleware('module.permission:reports.appointment_statistics')->name('reports.appointment-history');
        Route::get('/reports/health-forms', [ReportsController::class, 'healthFormsReport'])->middleware('module.permission:reports.health_forms')->name('reports.health-forms');
        Route::get('/reports/health-forms/applicants-list', [ReportsController::class, 'healthFormsApplicantsList'])->middleware('module.permission:reports.health_forms')->name('reports.health-forms.applicants-list');
        Route::get('/reports/health-forms/export', [ReportsController::class, 'exportHealthForms'])
            ->middleware(['module.permission:reports.export_reports', 'superadmin.export'])
            ->name('reports.health-forms.export');
        Route::get('/reports/health-forms-logbook', [ReportsController::class, 'healthFormsLogbook'])->middleware('module.permission:reports.health_forms')->name('reports.health-forms-logbook');
        Route::get('/reports/health-forms-logbook/export', [ReportsController::class, 'exportHealthFormsLogbook'])
            ->middleware(['module.permission:reports.export_reports', 'superadmin.export'])
            ->name('reports.health-forms-logbook.export');
        Route::get('/reports/feedbacks', [ReportsController::class, 'feedbackReport'])->middleware('module.permission:reports.feedbacks')->name('reports.feedbacks');
        Route::middleware(['module.permission:reports.export_reports', 'superadmin.export'])->group(function () {
            Route::get('/reports/export-hub', [ReportsController::class, 'exportHub'])->name('reports.exportHub');
            Route::get('/reports/export-hub/mar', [ReportsController::class, 'exportReportsMar'])->name('reports.exportHub.mar');
            Route::get('/reports/export-hub/inventory', [ReportsController::class, 'exportReportsInventory'])->name('reports.exportHub.inventory');
            Route::get('/reports/export-hub/appointments', [ReportsController::class, 'exportReportsAppointments'])->name('reports.exportHub.appointments');
            Route::get('/reports/export-hub/audit-trail', [ReportsController::class, 'exportReportsAuditTrail'])->middleware('role:superadmin')->name('reports.exportHub.audit-trail');
            Route::get('/reports/export-hub/health-forms', [ReportsController::class, 'exportReportsHealthForms'])->name('reports.exportHub.health-forms');
            Route::get('/reports/print-reports', [ReportsController::class, 'printReport'])->name('reports.print');
        });
        Route::get('/notifications/feed', [AdminController::class, 'notificationsFeed'])->name('notifications.feed');
        Route::post('/notifications/mark-all-read', [AdminController::class, 'markAllAdminNotificationsRead'])->name('notifications.read_all');
        Route::get('/developer-tools', [AdminController::class, 'developerTools'])->middleware('role:superadmin')->name('developer-tools');
        Route::get('/api-testing', [AdminController::class, 'apiTesting'])->middleware('role:superadmin')->name('api-testing');
    });
});

// Temporary dev login helper (debug mode only)
Route::get('/dev-login/{id}', function ($id) {
    if (!config('app.debug')) {
        abort(404);
    }

    $user = User::find($id);
    if ($user) {
        $originalRole = strtolower((string) ($user->user_role ?? ''));
        $normalizedRole = User::normalizeRole($user->user_role);
        if ($normalizedRole !== $originalRole) {
            $user->user_role = $normalizedRole;
            $user->save();
        }

        if ($normalizedRole === User::ROLE_SUPERADMIN) {
            Auth::guard('admin')->login($user);
            return redirect('/admin/dashboard')->with('success', 'Logged in as ' . $user->name);
        }
        if ($normalizedRole === User::ROLE_ADMIN) {
            $redirectPath = resolveWorkspaceRedirectForUser($user);

            if ($redirectPath === '/student/home') {
                Auth::guard('student')->login($user);
                return redirect('/student/home')->with('success', 'Logged in as ' . $user->name);
            }

            Auth::guard('admin')->login($user);
            return redirect($redirectPath)->with('success', 'Logged in as ' . $user->name);
        }

        Auth::guard('student')->login($user);
        return redirect('/student/account')->with('success', 'Logged in as ' . $user->name);
    }

    return 'User not found!';
});
