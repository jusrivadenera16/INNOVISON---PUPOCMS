<?php

use App\Http\Controllers\AdminAssistantController;
use App\Http\Controllers\AdminController;
use App\Http\Controllers\AdminUserController;
use App\Http\Controllers\AppointmentController;
use App\Http\Controllers\Auth\EmergencyAuthController;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Auth\RegisterController;
use App\Http\Controllers\MedicalConditionController;
use App\Http\Controllers\MedicineTypeController;
use App\Http\Controllers\ReportsController;
use App\Http\Controllers\StudentAssistantController;
use App\Http\Controllers\WalkInController;
use App\Models\Admin;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Route;

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
            $linkedAdmin = null;
            $email = trim((string) ($user->email ?? ''));

            if ($email !== '' && \Illuminate\Support\Facades\Schema::hasTable('admins')) {
                $linkedAdmin = Admin::query()
                    ->where(function ($query) use ($email) {
                        if (Admin::hasColumn('email')) {
                            $query->orWhere('email', $email);
                        }
                        if (Admin::hasColumn('email_address')) {
                            $query->orWhere('email_address', $email);
                        }
                    })
                    ->first();
            }

            $linkedRole = strtolower(trim((string) (
                $linkedAdmin?->access_level
                ?? $linkedAdmin?->admin_hub_role
                ?? ''
            )));

            if (in_array($linkedRole, ['designee', 'admin_designee'], true)) {
                return '/student/home';
            }

            return '/student/home';
        }

        return '/student/home';
    }
}

// --- PUBLIC ROUTES (No login required) ---
Route::get('/', function () {
    $user = Auth::guard('admin')->user() ?? Auth::guard('student')->user();
    if ($user instanceof User) {
        return redirect(resolveWorkspaceRedirectForUser($user));
    }

    return view('landing');
})->name('landing');
Route::get('/login', [LoginController::class, 'showLoginForm'])->name('login');
Route::get('/login/portal', function () {
    $existingUser = Auth::guard('admin')->user() ?? Auth::guard('student')->user();
    if ($existingUser instanceof User) {
        return redirect(resolveWorkspaceRedirectForUser($existingUser));
    }

    $clientId = trim((string) config('services.idp.client_id', ''));
    $redirectUri = trim((string) config('services.idp.redirect_uri', ''));
    $responseType = trim((string) config('services.idp.authorize_response_type', 'code')) ?: 'code';
    $authorizePath = trim((string) config('services.idp.authorize_path', '/api/v1/auth/authorize'));
    $baseUrl = trim((string) config('services.idp.base_url', ''));

    if ($clientId === '' || $redirectUri === '' || $baseUrl === '') {
        Log::warning('Portal login route blocked because IDP configuration is incomplete.', [
            'has_client_id' => $clientId !== '',
            'has_redirect_uri' => $redirectUri !== '',
            'has_base_url' => $baseUrl !== '',
        ]);

        return redirect()->route('landing')->withErrors([
            'idp' => 'Identity provider login is not configured.',
        ]);
    }

    $authorizeUrl = rtrim($baseUrl, '/') . '/' . ltrim($authorizePath, '/');
    $query = [
        'client_id' => $clientId,
        'redirect_uri' => $redirectUri,
        'response_type' => $responseType,
    ];

    $scope = trim((string) config('services.idp.authorize_scope', ''));
    if ($scope !== '') {
        $query['scope'] = $scope;
    }

    return redirect()->away($authorizeUrl . '?' . http_build_query($query));
})->name('login.portal');
Route::get('/auth/callback', [LoginController::class, 'handleIdpCallback'])->name('auth.callback');
Route::post('/login-action', [LoginController::class, 'login']);
Route::get('/system-admin/emergency-login', [EmergencyAuthController::class, 'showLoginForm'])->name('system-admin.emergency-login');
Route::post('/system-admin/emergency-login', [EmergencyAuthController::class, 'login'])
    ->middleware('throttle:10,1')
    ->name('system-admin.emergency-login.submit');
Route::post('/register-action', [RegisterController::class, 'register']);
Route::post('/logout', [LoginController::class, 'logout'])->name('logout');

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
Route::middleware(['auth:student', 'audit'])->group(function () {
    Route::middleware('role:student')->group(function () {
        Route::post('/student/skip-barcode', function () {
            session(['barcode_skipped' => true]);
            return response()->json(['status' => 'success']);
        });

        Route::get('/student/feedbacks', [AppointmentController::class, 'feedbackIndex'])->name('student.feedback.index');

        // 1. Route para ipakita ang blankong form
        Route::get('/student/health-form', [AppointmentController::class, 'showHealthForm'])->name('health.form');
        Route::post('/student/health-form', [AppointmentController::class, 'storeHealthForm'])
            ->name('store.health.form.fallback');
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
        Route::get('/student/health-record/document/{document}', [AppointmentController::class, 'showStudentHealthRecordDocument'])
            ->name('student.health_record.document');
        Route::post('/student/health-record/resubmit', [AppointmentController::class, 'resubmitHealthRecordRequirements'])
            ->name('student.health_record.resubmit');
        Route::post('/student/health-record/health-declaration', [AppointmentController::class, 'uploadHealthDeclaration'])
            ->name('student.health_record.health_declaration');

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

Route::middleware(['auth:admin', 'audit'])->group(function () {
    Route::middleware('role:admin')->group(function () {
        Route::get('/assistant/choose-portal', [LoginController::class, 'showStudentAssistantPortalChooser'])->name('assistant.choose-portal');
        Route::get('/assistant/enter-student', [LoginController::class, 'enterStudentPortal'])->name('assistant.enter-student');
        Route::get('/assistant/enter-admin', [LoginController::class, 'enterAdminPortal'])->name('assistant.enter-admin');
    });

    Route::get('/health-records', [AdminController::class, 'viewHealth'])
        ->middleware('role:superadmin,admin')
        ->name('admin.health_records');
    Route::get('/health-profile/{id}', [AdminController::class, 'showHealth'])
        ->middleware('role:superadmin,admin')
        ->name('admin.show_health');
    Route::get('/health-profile/{id}/plain', [AdminController::class, 'showHealthPlain'])
        ->middleware('role:superadmin,admin')
        ->name('admin.show_health_plain');
    Route::get('/health-profile/{id}/pdf', [AdminController::class, 'exportHealthPdf'])
        ->middleware('role:superadmin,admin')
        ->name('admin.health_pdf');
    Route::post('/health-profile/medical-assessment-upload', [AdminController::class, 'uploadMedicalAssessmentCopy'])
        ->middleware('role:superadmin,admin,nurse')
        ->name('admin.medical_assessment_upload');
    Route::get('/health-profile/{id}/sign', [AdminController::class, 'showSignPage'])
        ->middleware('role:superadmin')
        ->name('admin.sign_page');
    Route::put('/health-profile/{id}/update', [AdminController::class, 'updateClearance'])
        ->middleware('role:superadmin')
        ->name('admin.update_clearance');

    Route::middleware('role:superadmin,admin')->group(function () {
        Route::post('/admin/assistant/intent', [AdminAssistantController::class, 'handle'])->name('admin.assistant.intent');
        Route::post('/assistant/intent', [AdminAssistantController::class, 'handle'])->name('assistant.intent');

        Route::get('/admin/dashboard', [AdminController::class, 'dashboard'])->name('admin.dashboard');
        Route::get('/admin/appointments', [AdminController::class, 'appointments'])->name('admin.appointments');
        Route::get('/admin/appointments/{id}/{status}', [AdminController::class, 'updateStatus'])->name('admin.appointments.status');
        Route::post('/admin/appointments/{id}/reschedule', [AdminController::class, 'reschedule'])->name('admin.appointments.reschedule');

        Route::get('/admin/inventory', [AdminController::class, 'inventory'])->name('admin.inventory');

        Route::get('/admin/walkin', [WalkInController::class, 'index'])->name('walkin.index');
        Route::get('/admin/walkin/get-student', [WalkInController::class, 'getStudent'])->name('walkin.getStudent');
        Route::get('/admin/walkin/final-review-applicants', [WalkInController::class, 'finalReviewApplicants'])->name('walkin.final-review-applicants');
        Route::post('/admin/walkin/verify-id-ai', [WalkInController::class, 'verifyStudentIdWithAi'])->name('walkin.verify-id-ai');
        Route::post('/admin/walkin/register', [WalkInController::class, 'registerStudent'])->name('walkin.registerStudent');
        Route::get('/admin/walkin/form/{student_id}', [WalkInController::class, 'showWalkinForm'])->name('walkin.form');
        Route::get('/admin/walkin/health-form/{healthProfile}', [WalkInController::class, 'showApplicantHealthForm'])->name('walkin.healthForm');
        Route::get('/admin/walkin/document/{healthProfile}/{document}', [WalkInController::class, 'showApplicantDocument'])->name('walkin.document');
        Route::post('/admin/walkin/store', [WalkInController::class, 'store'])->name('walkin.store');
        Route::post('/admin/walkin/applicant-encoding', [WalkInController::class, 'saveApplicantEncoding'])->name('admin.walkin.applicant_encoding');
        Route::post('/admin/walkin/approve-applicant', [WalkInController::class, 'approveApplicant'])->name('admin.walkin.approve_applicant');

        Route::get('/admin/reports', [AdminController::class, 'reports'])->name('admin.reports');
        Route::get('/admin/reports/mar', [ReportsController::class, 'marReport'])->name('reports.mar');
        Route::get('/admin/reports/inventory-summary', [AdminController::class, 'inventorySummary'])->name('reports.inventory-summary');
        Route::get('/admin/reports/daily-treatment-record', [ReportsController::class, 'dailyTreatmentRecord'])->name('reports.daily-treatment-record');
        Route::get('/admin/reports/appointment-statistics', [ReportsController::class, 'appointmentStatistics'])->name('reports.appointment-statistics');
        Route::get('/admin/reports/appointment-history', [ReportsController::class, 'appointmentHistory'])->name('reports.appointment-history');
        Route::get('/admin/reports/appointment-history/print', [ReportsController::class, 'printAppointmentHistory'])->name('reports.appointment-history-print');
        Route::get('/admin/reports/health-forms', [ReportsController::class, 'healthFormsReport'])->name('reports.health-forms');
        Route::get('/admin/reports/health-forms-logbook', [ReportsController::class, 'healthFormsLogbook'])->name('reports.health-forms-logbook');
        Route::get('/admin/reports/health-forms-logbook/export', [ReportsController::class, 'exportHealthFormsLogbook'])->name('reports.health-forms-logbook.export');
        Route::get('/admin/reports/feedbacks', [ReportsController::class, 'feedbackReport'])->name('reports.feedbacks');
        Route::get('/admin/reports/export-hub', [ReportsController::class, 'exportHub'])->name('reports.exportHub');
        Route::get('/admin/reports/print-reports', [ReportsController::class, 'printReport'])->name('reports.print');
        Route::get('/admin/notifications/feed', [AdminController::class, 'notificationsFeed'])->name('admin.notifications.feed');
        Route::post('/admin/notifications/mark-all-read', [AdminController::class, 'markAllAdminNotificationsRead'])->name('admin.notifications.read_all');
        Route::get('/admin/user-management', [AdminUserController::class, 'index'])->name('admin.user-management');
        Route::get('/admin/user-management/account-access', [AdminUserController::class, 'accountAccess'])->name('admin.user-management.account-access');
        Route::get('/admin/user-management/admin-hub', [AdminUserController::class, 'adminHub'])->name('admin.user-management.admin-hub');
        Route::post('/admin/user-management/from-lookup', [AdminUserController::class, 'storeFromLookup'])->name('admin.user-management.store-from-lookup');
        Route::put('/admin/user-management/{user}', [AdminUserController::class, 'update'])->name('admin.user-management.update');
        Route::delete('/admin/user-management/{user}', [AdminUserController::class, 'destroy'])->name('admin.user-management.destroy');
        Route::put('/admin/user-management/admin-hub/{admin}', [AdminUserController::class, 'updateAdminHub'])->name('admin.user-management.admin-hub.update');
        Route::delete('/admin/user-management/admin-hub/{admin}', [AdminUserController::class, 'destroyAdminHub'])->name('admin.user-management.admin-hub.destroy');
        Route::delete('/admin/user-management/admin-hub/{admin}/delete-record', [AdminUserController::class, 'deleteAdminHubRecord'])->name('admin.user-management.admin-hub.delete-record');
        Route::get('/admin/developer-tools', [AdminController::class, 'developerTools'])->name('admin.developer-tools');
        Route::get('/admin/api-testing', [AdminController::class, 'apiTesting'])->name('admin.api-testing');
        Route::get('/admin/api/health-monitor', [AdminController::class, 'apiHealthMonitor'])->name('admin.api.health-monitor');
        Route::get('/admin/api/error-logs', [AdminController::class, 'apiErrorLogs'])->name('admin.api.error-logs');
        Route::get('/admin/api/system-status', [AdminController::class, 'apiSystemStatus'])->name('admin.api.system-status');
        Route::get('/admin/activity-logs', [AdminController::class, 'indexLogs'])
            ->middleware('role:superadmin')
            ->name('admin.logs');
    });

    // Super Admin-only routes
    Route::middleware('role:superadmin')->group(function () {
        Route::get('/admin/settings', [AdminController::class, 'settings'])->name('admin.settings');
        Route::put('/admin/settings/update', [AdminController::class, 'updateSettings'])->name('admin.settings.update');
        Route::put('/admin/profile/update', [AdminController::class, 'updateProfile'])->name('admin.profile.update');
        Route::put('/admin/api-testing/database/{table}/{id}', [AdminController::class, 'updateApiTestingDatabaseRecord'])->name('admin.api-testing.database.update');
        Route::delete('/admin/api-testing/database/{table}/{id}', [AdminController::class, 'deleteApiTestingDatabaseRecord'])->name('admin.api-testing.database.delete');

        Route::post('/admin/inventory/store', [AdminController::class, 'storeItem'])->name('admin.inventory.store');
        Route::post('/admin/inventory/import/analyze', [AdminController::class, 'analyzeInventoryImport'])->name('admin.inventory.import.analyze');
        Route::post('/admin/inventory/import/commit', [AdminController::class, 'commitInventoryImport'])->name('admin.inventory.import.commit');
        Route::post('/admin/inventory/import/clear', [AdminController::class, 'clearInventoryImportPreview'])->name('admin.inventory.import.clear');
        Route::post('/admin/inventory/{id}/restock', [AdminController::class, 'restockItem'])->name('admin.inventory.restock');
        Route::post('/admin/inventory/{id}/issue', [AdminController::class, 'issueStock'])->name('admin.inventory.issue');
        Route::put('/admin/inventory/{id}', [AdminController::class, 'updateItem'])->name('admin.inventory.update');
        Route::delete('/admin/inventory/{id}', [AdminController::class, 'deleteItem'])->name('admin.inventory.delete');

        Route::get('/admin/reports/manage-mar', [ReportsController::class, 'manageMar'])->name('admin.reports.manage-mar');
        Route::get('/admin/reports/manage-medicine-types', [MedicineTypeController::class, 'index'])->name('admin.reports.manage-medicine-types');
        Route::put('/admin/conditions/{id}', [ReportsController::class, 'update'])->name('conditions.update');
        Route::post('/admin/medical-conditions', [MedicalConditionController::class, 'store'])->name('conditions.store');
        Route::delete('/admin/medical-conditions/{id}', [MedicalConditionController::class, 'destroy'])->name('conditions.destroy');
        Route::post('/admin/medicine-types', [MedicineTypeController::class, 'store'])->name('medicine-types.store');
        Route::delete('/admin/medicine-types/{id}', [MedicineTypeController::class, 'destroy'])->name('medicine-types.destroy');

        Route::get('/admin/student-assistants', [StudentAssistantController::class, 'index'])->name('admin.student-assistants.index');
        Route::post('/admin/student-assistants', [StudentAssistantController::class, 'store'])->name('admin.student-assistants.store');
        Route::put('/admin/student-assistants/{assistant}', [StudentAssistantController::class, 'update'])->name('admin.student-assistants.update');
        Route::delete('/admin/student-assistants/{assistant}', [StudentAssistantController::class, 'destroy'])->name('admin.student-assistants.destroy');
    });

    // Admin prefixed entry points (same modules, different UI context)
    Route::middleware('role:admin')->prefix('assistant')->name('assistant.')->group(function () {
        Route::get('/dashboard', [AdminController::class, 'dashboard'])->name('dashboard');
        Route::get('/appointments', [AdminController::class, 'appointments'])->name('appointments');
        Route::get('/appointments/{id}/{status}', [AdminController::class, 'updateStatus'])->name('appointments.status');
        Route::post('/appointments/{id}/reschedule', [AdminController::class, 'reschedule'])->name('appointments.reschedule');
        Route::get('/inventory', [AdminController::class, 'inventory'])->name('inventory');

        Route::get('/walkin', [WalkInController::class, 'index'])->name('walkin.index');
        Route::get('/walkin/get-student', [WalkInController::class, 'getStudent'])->name('walkin.getStudent');
        Route::get('/walkin/final-review-applicants', [WalkInController::class, 'finalReviewApplicants'])->name('walkin.final-review-applicants');
        Route::post('/walkin/verify-id-ai', [WalkInController::class, 'verifyStudentIdWithAi'])->name('walkin.verify-id-ai');
        Route::post('/walkin/register', [WalkInController::class, 'registerStudent'])->name('walkin.registerStudent');
        Route::get('/walkin/form/{student_id}', [WalkInController::class, 'showWalkinForm'])->name('walkin.form');
        Route::get('/walkin/health-form/{healthProfile}', [WalkInController::class, 'showApplicantHealthForm'])->name('walkin.healthForm');
        Route::get('/walkin/document/{healthProfile}/{document}', [WalkInController::class, 'showApplicantDocument'])->name('walkin.document');
        Route::post('/walkin/store', [WalkInController::class, 'store'])->name('walkin.store');
        Route::post('/walkin/applicant-encoding', [WalkInController::class, 'saveApplicantEncoding'])->name('walkin.applicant_encoding');
        Route::post('/walkin/approve-applicant', [WalkInController::class, 'approveApplicant'])->name('walkin.approve_applicant');

        Route::get('/reports', [AdminController::class, 'reports'])->name('reports');
        Route::get('/reports/mar', [ReportsController::class, 'marReport'])->name('reports.mar');
        Route::get('/reports/inventory-summary', [AdminController::class, 'inventorySummary'])->name('reports.inventory-summary');
        Route::get('/reports/daily-treatment-record', [ReportsController::class, 'dailyTreatmentRecord'])->name('reports.daily-treatment-record');
        Route::get('/reports/appointment-statistics', [ReportsController::class, 'appointmentStatistics'])->name('reports.appointment-statistics');
        Route::get('/reports/appointment-history', [ReportsController::class, 'appointmentHistory'])->name('reports.appointment-history');
        Route::get('/reports/health-forms', [ReportsController::class, 'healthFormsReport'])->name('reports.health-forms');
        Route::get('/reports/health-forms-logbook', [ReportsController::class, 'healthFormsLogbook'])->name('reports.health-forms-logbook');
        Route::get('/reports/health-forms-logbook/export', [ReportsController::class, 'exportHealthFormsLogbook'])->name('reports.health-forms-logbook.export');
        Route::get('/reports/feedbacks', [ReportsController::class, 'feedbackReport'])->name('reports.feedbacks');
        Route::get('/reports/export-hub', [ReportsController::class, 'exportHub'])->name('reports.exportHub');
        Route::get('/reports/print-reports', [ReportsController::class, 'printReport'])->name('reports.print');
        Route::get('/notifications/feed', [AdminController::class, 'notificationsFeed'])->name('notifications.feed');
        Route::post('/notifications/mark-all-read', [AdminController::class, 'markAllAdminNotificationsRead'])->name('notifications.read_all');
        Route::get('/developer-tools', [AdminController::class, 'developerTools'])->name('developer-tools');
        Route::get('/api-testing', [AdminController::class, 'apiTesting'])->name('api-testing');
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
