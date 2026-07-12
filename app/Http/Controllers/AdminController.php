<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use App\Models\Appointment;
use App\Models\ActivityLog;
use App\Models\Consultation;
use App\Models\InventoryMovement;
use App\Models\Item;
use App\Models\MedicineType;
use App\Models\Setting;
use App\Models\Admin;
use App\Services\FacultySyncService;
use App\Services\GuisisApiService;
use App\Services\InventoryImportAnalyzer;
use App\Services\InventoryDataNormalizer;
use App\Services\PuptasWebhookService;
use Illuminate\Http\Client\RequestException;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Response; 
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;
use App\Models\HealthProfile;
use Carbon\Carbon;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\Schema;
use Illuminate\Validation\Rule;
use App\Models\IntegrationClient;

class AdminController extends Controller
{
    private function formatInventoryQuantity(float $value): string
    {
        $rounded = round($value, 2);
        if (abs($rounded - round($rounded)) < 0.00001) {
            return (string) (int) round($rounded);
        }

        return rtrim(rtrim(number_format($rounded, 2, '.', ''), '0'), '.');
    }

    private function recordInventoryMovement(Item $item, string $type, float $quantity, float $stockBefore, float $stockAfter, ?string $notes = null, ?string $movementDate = null, ?string $reason = null): void
    {
        $movementData = [
            'item_id' => $item->id,
            'user_id' => auth()->id(),
            'type' => $type,
            'quantity' => $quantity,
            'stock_before' => $stockBefore,
            'stock_after' => $stockAfter,
            'unit' => $item->unit ?: 'pcs',
            'batch_number' => $item->batch_number,
            'supplier_source' => $item->supplier_source,
            'notes' => $notes,
        ];

        if (Schema::hasColumn('inventory_movements', 'movement_date')) {
            $movementData['movement_date'] = $movementDate ?: now()->toDateString();
        }

        if (Schema::hasColumn('inventory_movements', 'reason')) {
            $movementData['reason'] = $reason;
        }

        InventoryMovement::create($movementData);
    }

    private function consumedStockQuantityForItem(Item $item, float $consumedTotal): float
    {
        return $item->convertDispensingQuantityToStockQuantity($consumedTotal);
    }

    private function inventoryReportCategoryLabel(Item $item): string
    {
        if ($item->category === 'Medicine') {
            if (!empty($item->medicine_type)) {
                return 'Medicine (' . $item->medicine_type . ')';
            }
        }

        return (string) $item->category;
    }

    private function isStudentAssistantAccount(User $user): bool
    {
        if (User::normalizeRole((string) $user->user_role) !== User::ROLE_ADMIN) {
            return false;
        }

        $userType = strtolower(trim((string) ($user->user_type ?? '')));
        return in_array($userType, ['assistant', 'student assistant', 'student_assistant'], true);
    }

    private function isSuperadminAccount(User $user): bool
    {
        return User::normalizeRole($user->user_role) === User::ROLE_SUPERADMIN;
    }

    private function canAccessApiTesting(User $user): bool
    {
        $email = strtolower(trim((string) ($user->email ?? '')));

        return $email === 'pupocms2027@gmail.com';
    }

    private function findLinkedAdminProfile(User $user): ?Admin
    {
        return $this->findLinkedAdminProfileByEmails([
            trim((string) ($user->email ?? '')),
        ]);
    }

    private function findLinkedAdminProfileByEmails(array $emails): ?Admin
    {
        if (!Schema::hasTable('admins')) {
            return null;
        }

        $emails = array_values(array_filter(array_unique(array_map(static function ($value) {
            return trim((string) $value);
        }, $emails))));

        if ($emails === []) {
            return null;
        }

        $query = Admin::query();

        $query->where(function ($builder) use ($emails) {
            if (Admin::hasColumn('email')) {
                $builder->orWhereIn('email', $emails);
            }

            if (Admin::hasColumn('email_address')) {
                $builder->orWhereIn('email_address', $emails);
            }
        });

        return $query->first();
    }

    private function splitDisplayName(string $name): array
    {
        $name = trim($name);
        if ($name === '') {
            return ['', '', '', ''];
        }

        $suffixes = ['jr', 'jr.', 'sr', 'sr.', 'ii', 'iii', 'iv', 'v'];
        $parts = preg_split('/\s+/', $name) ?: [$name];
        $suffix = '';

        if (count($parts) > 1) {
            $lastPart = strtolower((string) end($parts));
            if (in_array($lastPart, $suffixes, true)) {
                $suffix = (string) array_pop($parts);
            }
        }

        $parts = array_values($parts);
        $firstName = $parts[0] ?? '';
        $middleName = count($parts) > 2 ? implode(' ', array_slice($parts, 1, -1)) : '';
        $lastName = count($parts) > 1 ? ($parts[count($parts) - 1] ?? '') : '';

        return [$firstName, $middleName, $lastName, $suffix];
    }

    private function buildCmsAdminProfile(User $user): array
    {
        $isStudentAssistant = $this->isStudentAssistantAccount($user);
        $isSuperadmin = $this->isSuperadminAccount($user);
        $linkedAdmin = $isSuperadmin ? $this->findLinkedAdminProfile($user) : null;

        $birthday = $linkedAdmin?->birthday;
        $age = null;
        if ($birthday) {
            try {
                $age = Carbon::parse($birthday)->age;
            } catch (\Throwable $exception) {
                $age = null;
            }
        }

        $resolvedRole = $linkedAdmin?->access_level
            ?? ($isStudentAssistant ? 'student_assistant' : User::normalizeRole($user->user_role));

        $resolvedStatus = $linkedAdmin?->status ?? ($isStudentAssistant ? null : 'active');
        $resolvedAddress = $linkedAdmin?->address;
        $resolvedContactNumber = $linkedAdmin?->contact_no ?? $linkedAdmin?->emergency_contact_no;
        $resolvedFirstName = $linkedAdmin?->first_name ?: ($user->first_name ?? '');
        $resolvedMiddleName = $linkedAdmin?->middle_name;
        $resolvedLastName = $linkedAdmin?->last_name ?: ($user->last_name ?? '');
        $resolvedSuffixName = $linkedAdmin?->suffix_name;
        $resolvedName = trim(implode(' ', array_filter([
            $resolvedFirstName,
            $resolvedMiddleName,
            $resolvedLastName,
            $resolvedSuffixName,
        ])));

        return [
            'admin_id' => $linkedAdmin?->admin_id,
            'name' => $resolvedName !== '' ? $resolvedName : ($linkedAdmin?->name ?: ($user->name ?? '')),
            'first_name' => $resolvedFirstName,
            'last_name' => $resolvedLastName,
            'email' => $linkedAdmin?->email ?: ($linkedAdmin?->email_address ?: ($user->email ?? '')),
            'middle_name' => $linkedAdmin?->middle_name,
            'suffix_name' => $linkedAdmin?->suffix_name,
            'birthday' => $birthday,
            'age' => $age,
            'address' => $resolvedAddress,
            'contact_number' => $resolvedContactNumber,
            'emergency_contact_person' => $linkedAdmin?->emergency_contact_person,
            'emergency_contact_no' => $linkedAdmin?->emergency_contact_no,
            'office' => $linkedAdmin?->office,
            'gender' => $linkedAdmin?->gender,
            'civil_status' => $linkedAdmin?->civil_status,
            'role' => $resolvedRole,
            'status' => $resolvedStatus,
            'source' => $isSuperadmin ? 'admins' : ($isStudentAssistant ? 'external_pending' : 'display_only'),
            'is_student_assistant' => $isStudentAssistant,
            'is_superadmin' => $isSuperadmin,
            'has_local_admin_profile' => (bool) $linkedAdmin,
        ];
    }

    private function canSignHealthClearance(): bool
    {
        $role = User::normalizeRole(optional(Auth::user())->user_role ?? '');
        return $role === User::ROLE_SUPERADMIN;
    }

    private function looksLikeIdpIdentifier(?string $value): bool
    {
        $value = trim((string) $value);
        if ($value === '') {
            return false;
        }

        if (str_starts_with(strtolower($value), 'idp-')) {
            return true;
        }

        return (bool) preg_match(
            '/^[0-9a-f]{8}-[0-9a-f]{4}-[1-5][0-9a-f]{3}-[89ab][0-9a-f]{3}-[0-9a-f]{12}$/i',
            $value
        );
    }

    private function resolvePuptasReferenceNumber(HealthProfile $record): string
    {
        $user = $record->user;
        $candidateNumbers = [
            trim((string) ($record->reference_number ?? '')),
            trim((string) $record->student_number),
            trim((string) optional($user)->student_number),
        ];

        $knownIdpIdentifiers = array_filter([
            trim((string) optional($user)->student_id),
            trim((string) $record->student_id),
        ]);

        foreach ($candidateNumbers as $candidate) {
            if ($candidate === '') {
                continue;
            }

            if (in_array($candidate, $knownIdpIdentifiers, true) || $this->looksLikeIdpIdentifier($candidate)) {
                continue;
            }

            return $candidate;
        }

        $idpUserId = trim((string) optional($user)->student_id);
        if ($idpUserId === '') {
            $idpUserId = trim((string) $record->student_id);
        }

        if ($idpUserId === '') {
            return '';
        }

        $applicant = app(PuptasWebhookService::class)->fetchApplicantByIdpUserId($idpUserId);
        $referenceNumber = trim((string) data_get($applicant, 'reference_number'));
        if ($referenceNumber === '') {
            $referenceNumber = trim((string) data_get($applicant, 'student_number'));
        }

        if ($referenceNumber === '' || $referenceNumber === $idpUserId || $this->looksLikeIdpIdentifier($referenceNumber)) {
            return '';
        }

        if ($user && trim((string) $user->student_number) === '') {
            $user->student_number = $referenceNumber;
            $user->save();
        }

        $recordNeedsSave = false;
        if (Schema::hasColumn('health_profiles', 'reference_number') && trim((string) ($record->reference_number ?? '')) === '') {
            $record->reference_number = $referenceNumber;
            $recordNeedsSave = true;
        }
        if (trim((string) $record->student_number) === '' || $record->student_number === $idpUserId) {
            $record->student_number = $referenceNumber;
            $recordNeedsSave = true;
        }
        if ($recordNeedsSave) {
            $record->save();
        }

        return $referenceNumber;
    }

    private function resolvePuptasIdpStudentId(HealthProfile $record): string
    {
        $user = $record->user;

        return trim((string) (optional($user)->student_id ?: $record->student_id));
    }

    private function updatePuptasSyncState(HealthProfile $record, ?string $status, ?string $message = null, bool $markSyncedAt = false): void
    {
        if (!Schema::hasColumn('health_profiles', 'puptas_sync_status')) {
            return;
        }

        $updates = [
            'puptas_sync_status' => $status,
        ];

        if (Schema::hasColumn('health_profiles', 'puptas_sync_message')) {
            $updates['puptas_sync_message'] = $message ? trim($message) : null;
        }

        if (Schema::hasColumn('health_profiles', 'puptas_synced_at')) {
            $updates['puptas_synced_at'] = $markSyncedAt ? now() : null;
        }

        $record->forceFill($updates)->save();
    }

    private function logActivity(string $action, string $description, ?string $module = null, ?string $eventType = null): void
    {
        $user = Auth::user();
        if (!$user) {
            return;
        }

        ActivityLog::create([
            'user_id' => $user->id,
            'user_name' => $user->name ?? $user->email ?? 'Unknown User',
            'user_role' => strtolower((string) ($user->user_role ?? '')),
            'action' => $action,
            'module' => $module,
            'event_type' => $eventType,
            'description' => $description,
            'route_name' => optional(request()->route())->getName(),
            'http_method' => strtoupper((string) request()->method()),
            'request_path' => '/' . ltrim((string) request()->path(), '/'),
            'status_code' => 200,
            'ip_address' => request()->ip(),
            'user_agent' => substr((string) request()->userAgent(), 0, 255),
        ]);
    }

    // ==========================================
    //  PART 1: VIEW METHODS (Loading the Pages)
    // ==========================================

    public function dashboard()
    {
        Appointment::expireOverduePending();

        $total = Appointment::count();
        $pending = Appointment::where('status', 'Pending')->count();
        $upcoming = Appointment::where('status', 'Approved')
            ->whereDate('date', today())
            ->count();
        $completed = Appointment::where('status', 'Completed')->count();
        $cancelled = Appointment::where('status', 'Cancelled')->count();

        $inventoryTotal = Item::count();
        $inventoryInStock = Item::where('quantity', '>', 0)
            ->whereColumn('quantity', '>', 'minimum_stock')
            ->count();
        $inventoryLowStock = Item::where('quantity', '>', 0)
            ->whereColumn('quantity', '<=', 'minimum_stock')
            ->count();
        $inventoryOutOfStock = Item::where('quantity', '<=', 0)->count();

        $appointmentChartStats = [
            ['label' => 'Pending', 'value' => $pending, 'class' => 'warning'],
            ['label' => 'Scheduled Today', 'value' => $upcoming, 'class' => 'success'],
            ['label' => 'Completed', 'value' => $completed, 'class' => 'info'],
            ['label' => 'Cancelled', 'value' => $cancelled, 'class' => 'danger'],
        ];

        $inventoryChartStats = [
            ['label' => 'In Stock', 'value' => $inventoryInStock, 'class' => 'success'],
            ['label' => 'Low Stock', 'value' => $inventoryLowStock, 'class' => 'warning'],
            ['label' => 'Out', 'value' => $inventoryOutOfStock, 'class' => 'danger'],
        ];

        $recentAppointments = Appointment::latest()->take(5)->get();

        return view('admin.dashboard', compact(
            'total',
            'pending',
            'upcoming',
            'completed',
            'cancelled',
            'inventoryTotal',
            'appointmentChartStats',
            'inventoryChartStats',
            'recentAppointments'
        ));
    }

    public function developerTools()
    {
        $user = Auth::user();
        abort_unless($user instanceof User && $this->canAccessApiTesting($user), 403);

        return view('admin.developer_tools');
    }

    public function apiTesting(Request $request, FacultySyncService $facultySyncService, GuisisApiService $guisisApiService)
    {
        $user = Auth::user();
        abort_unless($user instanceof User && $this->canAccessApiTesting($user), 403);

        $search = trim((string) $request->query('search', ''));
        $source = trim((string) $request->query('source', 'faculty'));
        $dbTable = trim((string) $request->query('db_table', 'users'));
        $availableSystems = $this->externalApiTestingSystems();
        $selectedSystem = trim((string) $request->query('system', ($availableSystems[0] ?? '')));
        $results = [];
        $databaseInfo = [];
        $apiResponseMeta = null;
        $errorMessage = null;
        $errorDetails = null;

        $canRunWithoutSearch = in_array($source, ['admin_api', 'admin_options', 'database_info', 'guisis_profiles'], true);

        if ($search !== '' || $canRunWithoutSearch) {
            $facultyEndpoint = trim((string) config('services.pupt_flss.faculty_profiles_url', ''));
            $internalAdminEndpoint = url('/api/external/admins');
            $internalAdminOptionsEndpoint = url('/api/external/admins/options');
            $configuredTempEndpoint = trim((string) config('services.temp_api_testing.url', ''));
            $guisisBaseUrl = $guisisApiService->configuredBaseUrl();

            if ($source === 'database_info') {
                $endpoint = 'local-database://' . $dbTable;
            } elseif ($source === 'admin_api') {
                $endpoint = $internalAdminEndpoint;
            } elseif ($source === 'admin_options') {
                $endpoint = $internalAdminOptionsEndpoint;
            } elseif ($source === 'guisis_profile') {
                $endpoint = $guisisBaseUrl . '/integrations/students/profile?email={email}';
            } elseif ($source === 'guisis_profiles') {
                $endpoint = $guisisBaseUrl . '/integrations/students/profiles';
            } elseif ($source === 'guisis_student') {
                $endpoint = $guisisBaseUrl . '/integrations/students/{studentNumber}';
            } elseif ($source === 'guisis_addresses') {
                $endpoint = $guisisBaseUrl . '/integrations/students/{studentNumber}/addresses';
            } elseif ($source === 'guisis_personal_info') {
                $endpoint = $guisisBaseUrl . '/integrations/students/{studentNumber}/personalInfo';
            } elseif ($source === 'puptas_applicant') {
                $endpoint = 'PUPTAS /api/v1/medical/applicants/{studentNumber}';
            } elseif ($source === 'puptas_applicant_idp') {
                $endpoint = 'PUPTAS /api/v1/medical/applicants/idp/{idpUserId}';
            } elseif ($source === 'custom' && $configuredTempEndpoint !== '') {
                $endpoint = $configuredTempEndpoint;
            } else {
                $source = 'faculty';
                $endpoint = $configuredTempEndpoint !== '' ? $configuredTempEndpoint : $facultyEndpoint;
            }

            if ($endpoint === '') {
                $errorMessage = 'Temporary API testing URL is not configured yet.';
            } else {
                try {
                    if ($source === 'admin_api') {
                        [$systemIsValid, $systemMeta, $systemError] = $this->resolveExternalApiTestingSystemMeta($selectedSystem);
                        if (!$systemIsValid) {
                            $errorMessage = $systemError;
                        } else {
                        $results = $this->searchLocalAdminsForApiTesting($search);
                        $apiResponseMeta = [
                            'status' => 200,
                            'ok' => true,
                            'endpoint' => $internalAdminEndpoint,
                            'result_count' => count($results),
                            'auth_mode' => $systemMeta['auth_mode'],
                            'source' => $source,
                            'system' => $systemMeta['system'],
                            'header_name' => $systemMeta['header_name'],
                            'system_header_name' => $systemMeta['system_header_name'],
                            'api_key_preview' => $systemMeta['api_key_preview'] ?? null,
                            'auth_note' => $systemMeta['auth_note'] ?? null,
                        ];

                        if (empty($results)) {
                            $errorMessage = 'No matching records were found for the current search.';
                        }
                        }

                        return view('admin.api-testing', [
                            'search' => $search,
                            'source' => $source,
                            'selectedSystem' => $selectedSystem,
                            'availableSystems' => $availableSystems,
                            'results' => $results,
                            'apiResponseMeta' => $apiResponseMeta,
                            'errorMessage' => $errorMessage,
                            'errorDetails' => $errorDetails,
                        ]);
                    }

                    if ($source === 'admin_options') {
                        [$systemIsValid, $systemMeta, $systemError] = $this->resolveExternalApiTestingSystemMeta($selectedSystem);
                        if (!$systemIsValid) {
                            $errorMessage = $systemError;
                        } else {
                        $results = $this->searchLocalAdminOptionsForApiTesting($search);
                        $apiResponseMeta = [
                            'status' => 200,
                            'ok' => true,
                            'endpoint' => $internalAdminOptionsEndpoint,
                            'result_count' => count($results),
                            'auth_mode' => $systemMeta['auth_mode'],
                            'source' => $source,
                            'system' => $systemMeta['system'],
                            'header_name' => $systemMeta['header_name'],
                            'system_header_name' => $systemMeta['system_header_name'],
                            'api_key_preview' => $systemMeta['api_key_preview'] ?? null,
                            'auth_note' => $systemMeta['auth_note'] ?? null,
                        ];

                        if (empty($results)) {
                            $errorMessage = 'No matching records were found for the current search.';
                        }
                        }

                        return view('admin.api-testing', [
                            'search' => $search,
                            'source' => $source,
                            'selectedSystem' => $selectedSystem,
                            'availableSystems' => $availableSystems,
                            'results' => $results,
                            'apiResponseMeta' => $apiResponseMeta,
                            'errorMessage' => $errorMessage,
                            'errorDetails' => $errorDetails,
                        ]);
                    }

                    if ($source === 'faculty') {
                        $faculties = $facultySyncService->fetchFaculties($search);
                        $payload = ['faculties' => $faculties];
                        $results = $this->normalizeApiTestingResults($payload, $search);
                        $apiResponseMeta = [
                            'status' => 200,
                            'ok' => true,
                            'endpoint' => $facultyEndpoint,
                            'result_count' => count($results),
                            'auth_mode' => 'faculty-hmac',
                            'source' => $source,
                        ];

                        if (empty($results)) {
                            $errorMessage = 'No matching records were found for the current search.';
                        }
                    } elseif ($source === 'guisis_profile') {
                        $lookupResult = $guisisApiService->getStudentByEmailDetailed($search);
                        $payload = $lookupResult['data'] ?? null;
                        $results = $payload ? $this->normalizeApiTestingResults($payload, $search) : [];
                        $apiResponseMeta = [
                            'status' => $lookupResult['status'] ?? ($results ? 200 : 404),
                            'ok' => !empty($results),
                            'endpoint' => $endpoint,
                            'result_count' => count($results),
                            'auth_mode' => 'guisis-m2m-bearer',
                            'source' => $source,
                            'auth_status' => data_get($lookupResult, 'auth.status'),
                            'auth_token_source' => data_get($lookupResult, 'auth.source'),
                            'auth_endpoint' => data_get($lookupResult, 'auth.endpoint'),
                        ];

                        if (empty($results)) {
                            $errorMessage = trim((string) ($lookupResult['message'] ?? '')) ?: 'No GuiSIS student record matched the provided email.';
                            $errorDetails = trim((string) ($lookupResult['body'] ?? ''));
                        }
                    } elseif ($source === 'guisis_profiles') {
                        $lookupResult = $guisisApiService->listStudentsDetailed([
                            'search' => $search !== '' ? $search : null,
                            'page' => 1,
                            'page_size' => 10,
                        ]);
                        $payload = $lookupResult['data'] ?? null;
                        $results = $this->normalizeGuisisStudentResults($payload, $search);
                        $apiResponseMeta = [
                            'status' => $lookupResult['status'] ?? 200,
                            'ok' => ($lookupResult['ok'] ?? false) && !empty($results),
                            'endpoint' => $endpoint,
                            'result_count' => count($results),
                            'auth_mode' => 'guisis-m2m-bearer',
                            'source' => $source,
                            'auth_status' => data_get($lookupResult, 'auth.status'),
                            'auth_token_source' => data_get($lookupResult, 'auth.source'),
                            'auth_endpoint' => data_get($lookupResult, 'auth.endpoint'),
                        ];

                        if (!$lookupResult['ok']) {
                            $errorMessage = trim((string) ($lookupResult['message'] ?? '')) ?: 'GuiSIS list-students request failed.';
                            $errorDetails = trim((string) ($lookupResult['body'] ?? ''));
                        } elseif (empty($results)) {
                            $errorMessage = 'No GuiSIS student records matched the current search.';
                        }
                    } elseif ($source === 'guisis_student') {
                        $lookupResult = $guisisApiService->getStudentByStudentNumberDetailed($search);
                        $payload = $lookupResult['data'] ?? null;
                        $results = $payload ? $this->normalizeApiTestingResults($payload, $search) : [];
                        $apiResponseMeta = [
                            'status' => $lookupResult['status'] ?? ($results ? 200 : 404),
                            'ok' => !empty($results),
                            'endpoint' => $endpoint,
                            'result_count' => count($results),
                            'auth_mode' => 'guisis-m2m-bearer',
                            'source' => $source,
                            'auth_status' => data_get($lookupResult, 'auth.status'),
                            'auth_token_source' => data_get($lookupResult, 'auth.source'),
                            'auth_endpoint' => data_get($lookupResult, 'auth.endpoint'),
                        ];

                        if (empty($results)) {
                            $errorMessage = trim((string) ($lookupResult['message'] ?? '')) ?: 'No GuiSIS student record matched the provided student number.';
                            $errorDetails = trim((string) ($lookupResult['body'] ?? ''));
                        }
                    } elseif ($source === 'guisis_addresses') {
                        $lookupResult = $guisisApiService->getStudentAddressesDetailed($search);
                        $payload = $lookupResult['data'] ?? null;
                        $results = is_array($payload) ? [$payload] : [];
                        $apiResponseMeta = [
                            'status' => $lookupResult['status'] ?? ($results ? 200 : 404),
                            'ok' => !empty($results),
                            'endpoint' => $endpoint,
                            'result_count' => count($results),
                            'auth_mode' => 'guisis-m2m-bearer',
                            'source' => $source,
                            'auth_status' => data_get($lookupResult, 'auth.status'),
                            'auth_token_source' => data_get($lookupResult, 'auth.source'),
                            'auth_endpoint' => data_get($lookupResult, 'auth.endpoint'),
                        ];

                        if (empty($results)) {
                            $errorMessage = trim((string) ($lookupResult['message'] ?? '')) ?: 'No GuiSIS address record matched the provided student number.';
                            $errorDetails = trim((string) ($lookupResult['body'] ?? ''));
                        }
                    } elseif ($source === 'guisis_personal_info') {
                        $lookupResult = $guisisApiService->getStudentPersonalInfoDetailed($search);
                        $payload = $lookupResult['data'] ?? null;
                        $results = is_array($payload) ? [$payload] : [];
                        $apiResponseMeta = [
                            'status' => $lookupResult['status'] ?? ($results ? 200 : 404),
                            'ok' => !empty($results),
                            'endpoint' => $endpoint,
                            'result_count' => count($results),
                            'auth_mode' => 'guisis-m2m-bearer',
                            'source' => $source,
                            'auth_status' => data_get($lookupResult, 'auth.status'),
                            'auth_token_source' => data_get($lookupResult, 'auth.source'),
                            'auth_endpoint' => data_get($lookupResult, 'auth.endpoint'),
                        ];

                        if (empty($results)) {
                            $errorMessage = trim((string) ($lookupResult['message'] ?? '')) ?: 'No GuiSIS personal-info record matched the provided student number.';
                            $errorDetails = trim((string) ($lookupResult['body'] ?? ''));
                        }
                    } elseif ($source === 'puptas_applicant') {
                        $lookupResult = app(PuptasWebhookService::class)->fetchApplicantByStudentNumberDetailed($search);
                        $applicant = $lookupResult['data'] ?? null;
                        $results = $applicant ? [$applicant] : [];
                        $apiResponseMeta = [
                            'status' => $lookupResult['status'] ?? ($applicant ? 200 : 404),
                            'ok' => !empty($results),
                            'endpoint' => $endpoint,
                            'result_count' => count($results),
                            'auth_mode' => 'oauth-client-credentials',
                            'source' => $source,
                        ];

                        if (empty($results)) {
                            $errorMessage = trim((string) ($lookupResult['message'] ?? '')) ?: 'No PUPTAS applicant record matched the provided student number.';
                            $errorDetails = trim((string) ($lookupResult['body'] ?? ''));
                        }
                    } elseif ($source === 'puptas_applicant_idp') {
                        $lookupResult = app(PuptasWebhookService::class)->fetchApplicantByIdpUserIdDetailed($search);
                        $applicant = $lookupResult['data'] ?? null;
                        $results = $applicant ? [$applicant] : [];
                        $apiResponseMeta = [
                            'status' => $lookupResult['status'] ?? ($applicant ? 200 : 404),
                            'ok' => !empty($results),
                            'endpoint' => $endpoint,
                            'result_count' => count($results),
                            'auth_mode' => 'oauth-client-credentials',
                            'source' => $source,
                        ];

                        if (empty($results)) {
                            $errorMessage = trim((string) ($lookupResult['message'] ?? '')) ?: 'No PUPTAS applicant record matched the provided IDP user ID.';
                            $errorDetails = trim((string) ($lookupResult['body'] ?? ''));
                        }
                    } elseif ($source === 'database_info') {
                        $dbTable = in_array($dbTable, ['users', 'admins'], true) ? $dbTable : 'users';
                        $databaseInfo = $this->searchDatabaseInfoRecords($dbTable, $search);
                        $apiResponseMeta = [
                            'status' => 200,
                            'ok' => true,
                            'endpoint' => $endpoint,
                            'result_count' => count($databaseInfo),
                            'auth_mode' => 'superadmin-local',
                            'source' => $source,
                        ];

                        if (empty($databaseInfo)) {
                            $errorMessage = 'No database records matched the current search.';
                        }
                    } else {
                        $queryParams = [
                            'search' => $search,
                            'query'  => $search,
                            'q'      => $search,
                        ];
                        $fullUrlWithQuery = $endpoint . (str_contains($endpoint, '?') ? '&' : '?') . http_build_query($queryParams);

                        $client = Http::timeout((int) config('services.temp_api_testing.timeout', 20))
                            ->acceptJson();

                        $apiKey = trim((string) config('services.temp_api_testing.api_key', ''));
                        $apiHeader = trim((string) config('services.temp_api_testing.header', 'X-External-Api-Key'));
                        $authMode = 'none';

                        if ($apiKey !== '') {
                            $client = $client->withHeaders([$apiHeader => $apiKey]);
                            $authMode = 'custom-header';
                        }

                        $response = $client->get($fullUrlWithQuery);

                        $payload = $response->json();
                        $results = $this->normalizeApiTestingResults($payload, $search);
                        $apiResponseMeta = [
                            'status' => $response->status(),
                            'ok' => $response->successful(),
                            'endpoint' => $endpoint,
                            'result_count' => count($results),
                            'auth_mode' => $authMode,
                            'source' => $source,
                        ];

                        if (!$response->successful()) {
                            $errorMessage = 'The API request returned an error response.';
                            $errorDetails = trim((string) $response->body());
                        } elseif (empty($results)) {
                            $errorMessage = 'No matching records were found for the current search.';
                        }
                    }
                } catch (RequestException $exception) {
                    $response = $exception->response;
                    $status = $response?->status() ?? 500;
                    $body = trim((string) ($response?->body() ?? ''));

                    if ($source === 'faculty') {
                        $errorMessage = "FLSS returned an error response (HTTP {$status}).";
                        $apiResponseMeta = [
                            'status' => $status,
                            'ok' => false,
                            'endpoint' => $facultyEndpoint ?: $endpoint,
                            'result_count' => 0,
                            'auth_mode' => 'faculty-hmac',
                            'source' => $source,
                        ];
                    } else {
                        $errorMessage = "The external API returned an error response (HTTP {$status}).";
                    }

                    $errorDetails = $body !== '' ? $body : $exception->getMessage();
                } catch (\Throwable $exception) {
                    $errorMessage = 'Unable to reach the external API right now: ' . $exception->getMessage();
                    $errorDetails = $exception->getMessage();
                }
            }
        }

        return view('admin.api-testing', [
            'search' => $search,
            'source' => $source,
            'dbTable' => $dbTable,
            'selectedSystem' => $selectedSystem,
            'availableSystems' => $availableSystems,
            'results' => $results,
            'databaseInfo' => $databaseInfo,
            'apiResponseMeta' => $apiResponseMeta,
            'errorMessage' => $errorMessage,
            'errorDetails' => $errorDetails,
        ]);
    }

    public function updateApiTestingDatabaseRecord(Request $request, string $table, int $id)
    {
        $user = Auth::user();
        abort_unless($user instanceof User && $this->canAccessApiTesting($user), 403);

        if ($table === 'users') {
            $request->validate([
                'first_name' => 'nullable|string|max:255',
                'last_name' => 'nullable|string|max:255',
                'email' => ['required', 'email', 'max:255', Rule::unique('users', 'email')->ignore($id)],
                'student_id' => 'nullable|string|max:255',
                'student_number' => 'nullable|string|max:255',
                'gender' => 'nullable|string|max:255',
                'user_role' => ['required', Rule::in(['student', 'student_assistant', 'admin', 'superadmin', 'super_admin'])],
                'status' => ['nullable', Rule::in(['active', 'inactive'])],
            ]);

            $user = User::findOrFail($id);
            $user->first_name = $request->input('first_name');
            $user->last_name = $request->input('last_name');
            $user->name = trim(implode(' ', array_filter([$request->input('first_name'), $request->input('last_name')]))) ?: $user->name;
            $user->email = $request->input('email');
            $user->student_id = $request->input('student_id');
            if (Schema::hasColumn('users', 'student_number')) {
                $user->student_number = $request->input('student_number');
            }
            if (Schema::hasColumn('users', 'gender')) {
                $user->gender = $request->input('gender');
            }
            $user->user_role = User::normalizeRole($request->input('user_role'));
            if (Schema::hasColumn('users', 'status')) {
                $user->status = $request->input('status', 'active');
            }
            $user->save();

            return redirect()->route('admin.api-testing', ['source' => 'database_info', 'db_table' => 'users'])->with('success', 'User record updated.');
        }

        abort_unless($table === 'admins', 404);

        $request->validate([
            'first_name' => 'nullable|string|max:255',
            'last_name' => 'nullable|string|max:255',
            'email' => 'nullable|email|max:255',
            'office' => 'nullable|string|max:255',
            'access_level' => 'nullable|string|max:255',
            'status' => ['nullable', Rule::in(['active', 'inactive'])],
        ]);

        $admin = Admin::findOrFail($id);
        if (Admin::hasColumn('first_name')) {
            $admin->first_name = $request->input('first_name');
        }
        if (Admin::hasColumn('last_name')) {
            $admin->last_name = $request->input('last_name');
        }
        if (Admin::hasColumn('name')) {
            $admin->name = trim(implode(' ', array_filter([$request->input('first_name'), $request->input('last_name')]))) ?: $admin->name;
        }
        if (Admin::hasColumn('email')) {
            $admin->email = $request->input('email');
        }
        if (Admin::hasColumn('email_address')) {
            $admin->email_address = $request->input('email');
        }
        if (Admin::hasColumn('office')) {
            $admin->office = $request->input('office');
        }
        if (Admin::hasColumn('access_level')) {
            $admin->access_level = $request->input('access_level');
        }
        if (Admin::hasColumn('status')) {
            $admin->status = $request->input('status', 'active');
        }
        $admin->save();

        return redirect()->route('admin.api-testing', ['source' => 'database_info', 'db_table' => 'admins'])->with('success', 'Admin record updated.');
    }

    public function deleteApiTestingDatabaseRecord(string $table, int $id)
    {
        $user = Auth::user();
        abort_unless($user instanceof User && $this->canAccessApiTesting($user), 403);

        if ($table === 'users') {
            User::findOrFail($id)->delete();
            return redirect()->route('admin.api-testing', ['source' => 'database_info', 'db_table' => 'users'])->with('success', 'User record deleted.');
        }

        abort_unless($table === 'admins', 404);
        Admin::findOrFail($id)->delete();
        return redirect()->route('admin.api-testing', ['source' => 'database_info', 'db_table' => 'admins'])->with('success', 'Admin record deleted.');
    }

    private function externalApiTestingSystems(): array
    {
        $databaseSystems = collect();

        if (Schema::hasTable('integration_clients')) {
            $databaseSystems = IntegrationClient::query()
                ->where('is_active', true)
                ->orderBy('system_name')
                ->pluck('system_key');
        }

        $legacySystems = collect(
            config('services.external_admin_profile.system_keys', [])
        )->keys();

        return $databaseSystems
            ->merge($legacySystems)
            ->map(fn ($system) => strtolower(trim((string) $system)))
            ->filter()
            ->unique()
            ->values()
            ->all();
    }

    private function resolveExternalApiTestingSystemMeta(string $selectedSystem): array
    {
        $system = strtolower(trim($selectedSystem));
        $client = null;

        if (Schema::hasTable('integration_clients')) {
            $client = IntegrationClient::query()
                ->whereRaw('LOWER(system_key) = ?', [$system])
                ->where('is_active', true)
                ->first();
        }

        $systemKeys = collect(config('services.external_admin_profile.system_keys', []))
            ->mapWithKeys(fn ($value, $key) => [strtolower(trim((string) $key)) => trim((string) $value)]);

        if ($system === '') {
            return [false, null, 'Choose an external system first to test the API key configuration.'];
        }

        if ($client) {
            return [true, [
                'system' => $system,
                'header_name' => 'Authorization',
                'system_header_name' => trim((string) config('services.external_admin_profile.system_header', 'X-External-System')),
                'auth_mode' => 'sanctum-bearer-token',
                'api_key_preview' => 'Bearer token stored hashed in database',
                'auth_note' => 'Use the issued TOKEN_ID|RANDOM_SECRET as the Bearer token. The plaintext token cannot be viewed again after issuing.',
            ], null];
        }

        $apiKey = (string) $systemKeys->get($system, '');
        if ($apiKey === '') {
            return [false, null, 'No active integration client or legacy API key is configured for the selected external system.'];
        }

        return [true, [
            'system' => $system,
            'header_name' => trim((string) config('services.external_admin_profile.header', 'X-External-Api-Key')),
            'system_header_name' => trim((string) config('services.external_admin_profile.system_header', 'X-External-System')),
            'auth_mode' => 'legacy-static-key',
            'api_key_preview' => substr($apiKey, 0, 8) . '...' . substr($apiKey, -6),
        ], null];
    }

    private function searchDatabaseInfoRecords(string $table, string $search): array
    {
        if ($table === 'admins') {
            $query = Admin::query();

            if ($search !== '') {
                $query->where(function ($builder) use ($search) {
                    foreach (['admin_id', 'name', 'first_name', 'last_name', 'email', 'email_address', 'office', 'access_level', 'status'] as $column) {
                        if (Admin::hasColumn($column)) {
                            $builder->orWhere($column, 'like', '%' . $search . '%');
                        }
                    }
                });
            }

            return $query->orderByDesc('admin_id')
                ->limit(100)
                ->get()
                ->map(function (Admin $admin) {
                    return [
                        'id' => $admin->admin_id,
                        'name' => $admin->name ?: trim(implode(' ', array_filter([$admin->first_name, $admin->last_name]))),
                        'email' => $admin->email_address ?: $admin->email,
                        'status' => $admin->status ?? 'N/A',
                        'primary' => [
                            'Admin ID' => $admin->admin_id,
                            'First Name' => $admin->first_name ?? 'N/A',
                            'Last Name' => $admin->last_name ?? 'N/A',
                            'Email' => $admin->email_address ?: ($admin->email ?? 'N/A'),
                            'Office' => $admin->office ?? 'N/A',
                            'Access Level' => $admin->access_level ?? 'N/A',
                            'Status' => $admin->status ?? 'N/A',
                            'Updated At' => optional($admin->updated_at)->toIso8601String() ?? 'N/A',
                        ],
                        'raw' => $admin->toArray(),
                    ];
                })
                ->values()
                ->all();
        }

        $query = User::query();

        if ($search !== '') {
            $query->where(function ($builder) use ($search) {
                foreach (['id', 'student_id', 'name', 'first_name', 'last_name', 'email', 'user_role', 'status'] as $column) {
                    if (Schema::hasColumn('users', $column)) {
                        $builder->orWhere($column, 'like', '%' . $search . '%');
                    }
                }
            });
        }

        return $query->orderByDesc('id')
            ->limit(100)
            ->get()
            ->map(function (User $user) {
                return [
                    'id' => $user->id,
                    'name' => $user->name ?: trim(implode(' ', array_filter([$user->first_name, $user->last_name]))),
                    'email' => $user->email,
                    'status' => $user->status ?? 'N/A',
                    'primary' => [
                        'User ID' => $user->id,
                        'Student ID' => $user->student_id ?? 'N/A',
                        'First Name' => $user->first_name ?? 'N/A',
                        'Last Name' => $user->last_name ?? 'N/A',
                        'Email' => $user->email ?? 'N/A',
                        'Role' => $user->user_role ?? 'N/A',
                        'Status' => $user->status ?? 'N/A',
                        'Updated At' => optional($user->updated_at)->toIso8601String() ?? 'N/A',
                    ],
                    'raw' => $user->toArray(),
                ];
            })
            ->values()
            ->all();
    }

    private function searchLocalAdminsForApiTesting(string $search): array
    {
        if (!Schema::hasTable('admins')) {
            return [];
        }

        $query = Admin::query();

        if ($search !== '') {
            $query->where(function ($builder) use ($search) {
                foreach (['admin_id', 'name', 'first_name', 'middle_name', 'last_name', 'email', 'email_address', 'office', 'access_level', 'role'] as $column) {
                    if (!Admin::hasColumn($column)) {
                        continue;
                    }

                    $builder->orWhere($column, 'like', '%' . $search . '%');
                }
            });
        }

        $orderColumn = 'admin_id';
        foreach (['name', 'first_name', 'admin_id'] as $candidateColumn) {
            if (Admin::hasColumn($candidateColumn)) {
                $orderColumn = $candidateColumn;
                break;
            }
        }

        $records = $query->orderBy($orderColumn)->limit(20)->get()->map(function (Admin $admin) {
            $fields = $admin->toArray();
            $name = trim((string) ($fields['name'] ?? trim(($fields['first_name'] ?? '') . ' ' . ($fields['middle_name'] ?? '') . ' ' . ($fields['last_name'] ?? '') . ' ' . ($fields['suffix_name'] ?? ''))));
            $resolvedStatus = $this->resolveLocalAdminApiTestingStatus($fields);

            return [
                'identifier' => (string) ($fields['admin_id'] ?? 'N/A'),
                'admin_id' => (string) ($fields['admin_id'] ?? 'N/A'),
                'name' => $name !== '' ? $name : 'N/A',
                'first_name' => (string) ($fields['first_name'] ?? 'N/A'),
                'middle_name' => (string) ($fields['middle_name'] ?? 'N/A'),
                'last_name' => (string) ($fields['last_name'] ?? 'N/A'),
                'suffix_name' => (string) ($fields['suffix_name'] ?? 'N/A'),
                'email' => (string) ($fields['email'] ?? $fields['email_address'] ?? 'N/A'),
                'birthday' => (string) ($fields['birthday'] ?? 'N/A'),
                'age' => (string) ($fields['age'] ?? 'N/A'),
                'gender' => (string) ($fields['gender'] ?? 'N/A'),
                'civil_status' => (string) ($fields['civil_status'] ?? 'N/A'),
                'role' => (string) ($fields['access_level'] ?? $fields['role'] ?? 'N/A'),
                'access_level' => (string) ($fields['access_level'] ?? $fields['role'] ?? 'N/A'),
                'office' => (string) ($fields['office'] ?? 'N/A'),
                'contact_number' => (string) ($fields['contact_no'] ?? $fields['emergency_contact_no'] ?? 'N/A'),
                'address' => (string) ($fields['address'] ?? 'N/A'),
                'status' => $resolvedStatus,
                'emergency_contact_person' => (string) ($fields['emergency_contact_person'] ?? 'N/A'),
                'emergency_contact_no' => (string) ($fields['emergency_contact_no'] ?? 'N/A'),
                'last_updated' => (string) ($fields['updated_at'] ?? 'N/A'),
                'fields' => $fields,
            ];
        })->values()->all();

        return $records;
    }

    private function searchLocalAdminOptionsForApiTesting(string $search): array
    {
        if (!Schema::hasTable('admins')) {
            return [];
        }

        $query = Admin::query();

        if ($search !== '') {
            $query->where(function ($builder) use ($search) {
                foreach (['admin_id', 'first_name', 'last_name', 'suffix_name', 'email', 'email_address'] as $column) {
                    if (!Admin::hasColumn($column)) {
                        continue;
                    }

                    $builder->orWhere($column, 'like', '%' . $search . '%');
                }
            });
        }

        return $query->orderBy($this->resolveAdminApiTestingOrderColumn())
            ->limit(50)
            ->get()
            ->map(function (Admin $admin) {
                $fields = $admin->toArray();
                $firstName = (string) ($fields['first_name'] ?? 'N/A');
                $lastName = (string) ($fields['last_name'] ?? 'N/A');
                $suffixName = $fields['suffix_name'] ?? null;

                return [
                    'identifier' => (string) ($fields['admin_id'] ?? 'N/A'),
                    'admin_id' => (string) ($fields['admin_id'] ?? 'N/A'),
                    'name' => trim(implode(' ', array_filter([$firstName, $lastName, $suffixName]))) ?: 'N/A',
                    'first_name' => $firstName,
                    'middle_name' => 'N/A',
                    'last_name' => $lastName,
                    'suffix_name' => $suffixName ?: 'N/A',
                    'email' => (string) ($fields['email'] ?? $fields['email_address'] ?? 'N/A'),
                    'birthday' => 'N/A',
                    'age' => 'N/A',
                    'gender' => 'N/A',
                    'civil_status' => 'N/A',
                    'role' => 'N/A',
                    'access_level' => 'N/A',
                    'office' => 'N/A',
                    'contact_number' => 'N/A',
                    'address' => 'N/A',
                    'status' => $this->resolveLocalAdminApiTestingStatus($fields),
                    'emergency_contact_person' => 'N/A',
                    'emergency_contact_no' => 'N/A',
                    'last_updated' => (string) ($fields['updated_at'] ?? 'N/A'),
                    'fields' => [
                        'id' => (string) ($fields['admin_id'] ?? 'N/A'),
                        'first_name' => $firstName,
                        'last_name' => $lastName,
                        'suffix_name' => $suffixName,
                        'email' => (string) ($fields['email'] ?? $fields['email_address'] ?? 'N/A'),
                        'status' => $this->resolveLocalAdminApiTestingStatus($fields),
                    ],
                ];
            })
            ->values()
            ->all();
    }

    private function resolveAdminApiTestingOrderColumn(): string
    {
        foreach (['first_name', 'name', 'admin_id'] as $candidateColumn) {
            if (Admin::hasColumn($candidateColumn)) {
                return $candidateColumn;
            }
        }

        return 'admin_id';
    }

    private function resolveLocalAdminApiTestingStatus(array $fields): string
    {
        $rawStatus = trim((string) ($fields['status'] ?? ''));
        if ($rawStatus !== '') {
            return $rawStatus;
        }

        if (!Schema::hasTable('users')) {
            return 'N/A';
        }

        $emails = array_values(array_filter(array_unique(array_map(static function ($value) {
            return trim((string) $value);
        }, [
            $fields['email'] ?? null,
            $fields['email_address'] ?? null,
        ]))));

        if ($emails === []) {
            return 'N/A';
        }

        $linkedUser = User::query()->whereIn('email', $emails)->first();
        if (!$linkedUser) {
            return 'inactive';
        }

        foreach (['status', 'account_status'] as $column) {
            if (Schema::hasColumn('users', $column)) {
                $value = trim((string) $linkedUser->getAttribute($column));
                if ($value !== '') {
                    return strtolower($value);
                }
            }
        }

        if (Schema::hasColumn('users', 'is_active')) {
            return (bool) $linkedUser->getAttribute('is_active') ? 'active' : 'inactive';
        }

        return 'active';
    }

    private function normalizeApiTestingResults($payload, string $search): array
    {
        if (!is_array($payload)) {
            return [];
        }

        $records = $payload['data'] ?? $payload['results'] ?? $payload['records'] ?? $payload;
        if (!is_array($records)) {
            return [];
        }

        if (is_array($records) && isset($records['faculties']) && is_array($records['faculties'])) {
            $items = $records['faculties'];
        } elseif (array_is_list($records)) {
            $items = $records;
        } else {
            $items = [$records];
        }
        $needle = strtolower($search);
        $normalized = [];

        foreach ($items as $item) {
            if (!is_array($item)) {
                continue;
            }

            $profile = isset($item['profile']) && is_array($item['profile'])
                ? $item['profile']
                : [];
            $profileAddress = isset($profile['address']) && is_array($profile['address'])
                ? $profile['address']
                : [];
            $name = trim((string) ($item['name'] ?? trim(($item['first_name'] ?? '') . ' ' . ($item['middle_name'] ?? '') . ' ' . ($item['last_name'] ?? '') . ' ' . ($item['suffix_name'] ?? ''))));
            $email = trim((string) ($item['email'] ?? $item['email_address'] ?? ''));
            $identifier = trim((string) ($item['faculty_code'] ?? $item['faculty_id'] ?? $item['id'] ?? $item['admin_id'] ?? $item['student_number'] ?? $item['student_id'] ?? $item['employee_id'] ?? ''));
            $birthday = trim((string) ($item['birthday'] ?? $profile['birthday'] ?? $item['dob'] ?? $item['date_of_birth'] ?? ''));
            $role = trim((string) ($item['faculty_type'] ?? $item['role'] ?? $item['access_level'] ?? $item['designation'] ?? ''));
            $office = trim((string) ($item['office'] ?? $item['offices'] ?? $item['department'] ?? ''));
            $contactNumber = trim((string) ($item['contact_no'] ?? $item['contact_number'] ?? $item['phone'] ?? $item['mobile'] ?? ''));
            $address = trim((string) ($item['address'] ?? $item['home_address'] ?? $this->formatApiTestingAddress($profileAddress)));
            $status = trim((string) ($item['status'] ?? ($item['is_active'] ?? '')));

            $haystack = strtolower(implode(' ', array_filter([
                $name,
                $email,
                $identifier,
                json_encode($item),
            ])));

            if ($needle !== '' && !str_contains($haystack, $needle)) {
                continue;
            }

            $normalized[] = [
                'identifier' => $identifier !== '' ? $identifier : 'N/A',
                'admin_id' => trim((string) ($item['admin_id'] ?? $item['id'] ?? '')) ?: 'N/A',
                'name' => $name !== '' ? $name : 'N/A',
                'first_name' => trim((string) ($item['first_name'] ?? '')) ?: 'N/A',
                'middle_name' => trim((string) ($item['middle_name'] ?? '')) ?: 'N/A',
                'last_name' => trim((string) ($item['last_name'] ?? '')) ?: 'N/A',
                'suffix_name' => trim((string) ($item['suffix_name'] ?? '')) ?: 'N/A',
                'email' => $email !== '' ? $email : 'N/A',
                'birthday' => $birthday !== '' ? $birthday : 'N/A',
                'age' => trim((string) ($item['age'] ?? '')) ?: 'N/A',
                'gender' => trim((string) ($item['gender'] ?? $profile['gender'] ?? '')) ?: 'N/A',
                'civil_status' => trim((string) ($item['civil_status'] ?? '')) ?: 'N/A',
                'role' => $role !== '' ? $role : 'N/A',
                'access_level' => trim((string) ($item['access_level'] ?? $item['role'] ?? '')) ?: ($role !== '' ? $role : 'N/A'),
                'office' => $office !== '' ? $office : 'N/A',
                'contact_number' => $contactNumber !== '' ? $contactNumber : 'N/A',
                'address' => $address !== '' ? $address : 'N/A',
                'status' => $this->normalizeApiTestingStatusValue($status),
                'emergency_contact_person' => trim((string) ($item['emergency_contact_person'] ?? '')) ?: 'N/A',
                'emergency_contact_no' => trim((string) ($item['emergency_contact_no'] ?? '')) ?: 'N/A',
                'last_updated' => trim((string) ($item['last_updated'] ?? $item['updated_at'] ?? '')) ?: 'N/A',
                'fields' => $item,
            ];
        }

        return array_slice($normalized, 0, 20);
    }

    private function normalizeGuisisStudentResults($payload, string $search): array
    {
        if (!is_array($payload)) {
            return [];
        }

        $students = $payload['students'] ?? $payload['data']['students'] ?? $payload['data'] ?? [];
        if (!is_array($students)) {
            return [];
        }

        if (!array_is_list($students)) {
            $students = [$students];
        }

        return $this->normalizeApiTestingResults($students, $search);
    }

    private function formatApiTestingAddress(array $address): string
    {
        $parts = array_values(array_filter(array_map(static function ($value) {
            return trim((string) $value);
        }, [
            $address['house_num'] ?? null,
            $address['street'] ?? null,
            $address['barangay'] ?? null,
            $address['city'] ?? null,
            $address['province'] ?? null,
            $address['country'] ?? null,
            $address['zipcode'] ?? null,
        ])));

        return implode(', ', $parts);
    }

    private function normalizeApiTestingStatusValue($status): string
    {
        if (is_bool($status)) {
            return $status ? 'active' : 'inactive';
        }

        $normalized = strtolower(trim((string) $status));
        if ($normalized === '') {
            return 'N/A';
        }

        return match ($normalized) {
            '1', 'true', 'active', 'enabled' => 'active',
            '0', 'false', 'inactive', 'disabled' => 'inactive',
            default => $normalized,
        };
    }

    public function viewHealth(Request $request)
    {
        $search = trim((string) $request->query('q', ''));
        $courseFilter = trim((string) $request->query('course', ''));
        $monthFilter = trim((string) $request->query('month', ''));
        $yearFilter = trim((string) $request->query('year', ''));
        $perPageInput = trim((string) $request->query('per_page', '20'));
        $allowedPerPage = ['20', '40', '80', '100', 'all'];
        $issuedPerPage = in_array($perPageInput, $allowedPerPage, true) ? $perPageInput : '20';

        $query = HealthProfile::with('user')->latest();

        if ($search !== '') {
            $query->where(function ($builder) use ($search) {
                $like = '%' . $search . '%';

                $builder->where('reference_number', 'like', $like)
                    ->orWhere('student_number', 'like', $like)
                    ->orWhere('student_id', 'like', $like)
                    ->orWhere('course_college', 'like', $like)
                    ->orWhere('course_code', 'like', $like)
                    ->orWhere('sex', 'like', $like)
                    ->orWhere('clearance_status', 'like', $like)
                    ->orWhereHas('user', function ($userQuery) use ($like) {
                        $userQuery->where('name', 'like', $like)
                            ->orWhere('first_name', 'like', $like)
                            ->orWhere('middle_name', 'like', $like)
                            ->orWhere('last_name', 'like', $like)
                            ->orWhere('email', 'like', $like)
                            ->orWhere('student_number', 'like', $like)
                            ->orWhere('student_id', 'like', $like)
                            ->orWhere('course', 'like', $like);
                    });
            });
        }

        if ($courseFilter !== '') {
            $query->where(function ($builder) use ($courseFilter) {
                $builder->where('course_college', $courseFilter)
                    ->orWhere(function ($innerBuilder) use ($courseFilter) {
                        $innerBuilder->where(function ($profileBuilder) {
                            $profileBuilder->whereNull('course_college')
                                ->orWhere('course_college', '');
                        })
                            ->whereHas('user', function ($userQuery) use ($courseFilter) {
                                $userQuery->where('course', $courseFilter);
                            });
                    });
            });
        }

        if ($monthFilter !== '') {
            try {
                $monthDate = Carbon::parse($monthFilter . '-01');
                $query->whereYear('created_at', $monthDate->year)
                    ->whereMonth('created_at', $monthDate->month);
            } catch (\Throwable $e) {
                // Ignore invalid month input and keep the query usable.
            }
        }

        if ($yearFilter !== '') {
            $yearAliases = [
                '1st Year' => ['1st year', '1st', '1', 'first year'],
                '2nd Year' => ['2nd year', '2nd', '2', 'second year'],
                '3rd Year' => ['3rd year', '3rd', '3', 'third year'],
                '4th Year' => ['4th year', '4th', '4', 'fourth year'],
            ];

            $acceptedYearValues = $yearAliases[$yearFilter] ?? [Str::lower($yearFilter)];

            $query->whereHas('user', function ($userQuery) use ($acceptedYearValues) {
                $userQuery->where(function ($builder) use ($acceptedYearValues) {
                    foreach ($acceptedYearValues as $index => $acceptedYearValue) {
                        $method = $index === 0 ? 'whereRaw' : 'orWhereRaw';
                        $builder->{$method}('LOWER(year) = ?', [$acceptedYearValue]);
                    }
                });
            });
        }

        $issuedQuery = (clone $query)
            ->whereIn('clearance_status', ['Issued', 'Fully Cleared']);

        $healthProfileSummaryRecords = $issuedQuery
            ->paginate($issuedPerPage === 'all' ? max(1, (clone $issuedQuery)->count()) : (int) $issuedPerPage, ['*'], 'issued_page')
            ->withQueryString();

        $records = $query->get();

        $courseOptions = HealthProfile::query()
            ->with('user:id,course')
            ->get()
            ->map(function (HealthProfile $profile) {
                return trim((string) ($profile->course_college ?: optional($profile->user)->course ?: ''));
            })
            ->filter(fn ($course) => $course !== '')
            ->unique()
            ->sort()
            ->values();

        $yearOptions = collect(['1st Year', '2nd Year', '3rd Year', '4th Year']);

        return view('admin.health_records', compact(
            'records',
            'healthProfileSummaryRecords',
            'search',
            'courseFilter',
            'monthFilter',
            'yearFilter',
            'courseOptions',
            'yearOptions',
            'issuedPerPage'
        ));
    }

    public function healthRecordStats(Request $request)
    {
        $search = trim((string) $request->query('q', ''));
        $courseFilter = trim((string) $request->query('course', ''));
        $monthFilter = trim((string) $request->query('month', ''));
        $yearFilter = trim((string) $request->query('year', ''));

        $query = HealthProfile::with('user')->latest();

        if ($search !== '') {
            $query->where(function ($builder) use ($search) {
                $like = '%' . $search . '%';

                $builder->where('reference_number', 'like', $like)
                    ->orWhere('student_number', 'like', $like)
                    ->orWhere('student_id', 'like', $like)
                    ->orWhere('course_college', 'like', $like)
                    ->orWhere('course_code', 'like', $like)
                    ->orWhere('sex', 'like', $like)
                    ->orWhere('clearance_status', 'like', $like)
                    ->orWhereHas('user', function ($userQuery) use ($like) {
                        $userQuery->where('name', 'like', $like)
                            ->orWhere('first_name', 'like', $like)
                            ->orWhere('middle_name', 'like', $like)
                            ->orWhere('last_name', 'like', $like)
                            ->orWhere('email', 'like', $like)
                            ->orWhere('student_number', 'like', $like)
                            ->orWhere('student_id', 'like', $like)
                            ->orWhere('course', 'like', $like);
                    });
            });
        }

        if ($courseFilter !== '') {
            $query->where(function ($builder) use ($courseFilter) {
                $builder->where('course_college', $courseFilter)
                    ->orWhere(function ($innerBuilder) use ($courseFilter) {
                        $innerBuilder->where(function ($profileBuilder) {
                            $profileBuilder->whereNull('course_college')
                                ->orWhere('course_college', '');
                        })
                            ->whereHas('user', function ($userQuery) use ($courseFilter) {
                                $userQuery->where('course', $courseFilter);
                            });
                    });
            });
        }

        if ($monthFilter !== '') {
            try {
                $monthDate = Carbon::parse($monthFilter . '-01');
                $query->whereYear('created_at', $monthDate->year)
                    ->whereMonth('created_at', $monthDate->month);
            } catch (\Throwable $e) {
                // Ignore invalid month input and keep the stats endpoint usable.
            }
        }

        if ($yearFilter !== '') {
            $yearAliases = [
                '1st Year' => ['1st year', '1st', '1', 'first year'],
                '2nd Year' => ['2nd year', '2nd', '2', 'second year'],
                '3rd Year' => ['3rd year', '3rd', '3', 'third year'],
                '4th Year' => ['4th year', '4th', '4', 'fourth year'],
            ];

            $acceptedYearValues = $yearAliases[$yearFilter] ?? [Str::lower($yearFilter)];

            $query->whereHas('user', function ($userQuery) use ($acceptedYearValues) {
                $userQuery->where(function ($builder) use ($acceptedYearValues) {
                    foreach ($acceptedYearValues as $index => $acceptedYearValue) {
                        $method = $index === 0 ? 'whereRaw' : 'orWhereRaw';
                        $builder->{$method}('LOWER(year) = ?', [$acceptedYearValue]);
                    }
                });
            });
        }

        $issuedQuery = (clone $query)
            ->whereIn('clearance_status', ['Issued', 'Fully Cleared']);

        $records = $query->get();
        $stats = [
            'total_approved' => (clone $issuedQuery)->count(),
            'with_conditions' => 0,
            'pending_approval' => 0,
            'pending_conditional' => 0,
        ];

        foreach ($records as $summaryRecord) {
            $summaryHasRequirements = filled($summaryRecord->medical_certificate)
                && filled($summaryRecord->chest_xray_result)
                && filled($summaryRecord->student_photo);
            $summaryStatus = trim((string) ($summaryRecord->clearance_status ?? ''));
            $summaryIsApproved = in_array($summaryStatus, ['Issued', 'Fully Cleared'], true);
            $summaryIsConditional = !$summaryIsApproved && (
                in_array($summaryStatus, ['Pending/Conditional', 'Pending Resubmission', 'Rejected'], true)
                || trim((string) ($summaryRecord->pending_reason ?? '')) !== ''
                || trim((string) ($summaryRecord->medical_condition_remarks ?? '')) !== ''
            );

            if ($summaryIsApproved && $summaryRecord->hasMedicalCondition()) {
                $stats['with_conditions']++;
            }

            if ($summaryHasRequirements && !$summaryIsConditional && in_array($summaryStatus, ['Pending', 'For Verification', ''], true)) {
                $stats['pending_approval']++;
            }

            if ($summaryIsConditional) {
                $stats['pending_conditional']++;
            }
        }

        return response()->json(['stats' => $stats]);
    }

    public function showHealth($id)
    {

        $profile = HealthProfile::with('user')->findOrFail($id);


        $calculatedAge = Carbon::parse($profile->user->DOB)->age;

        return view('admin.show_health', compact('profile', 'calculatedAge'));
    }

    public function resyncPuptasHealthProfile($id, PuptasWebhookService $puptasService)
    {
        $profile = HealthProfile::with('user')->findOrFail($id);

        if (!in_array((string) $profile->clearance_status, ['Issued', 'Fully Cleared'], true)) {
            return back()->with('error', 'Only issued health records can be synced to PUPTAS.');
        }

        $referenceNumber = $this->resolvePuptasReferenceNumber($profile);
        $idpStudentId = $this->resolvePuptasIdpStudentId($profile);
        $upperReference = strtoupper($referenceNumber);

        if ($referenceNumber === '') {
            $this->updatePuptasSyncState($profile, 'missing_reference_number', 'PUPTAS resync skipped because the reference number is missing.');
            $this->logActivity('PUPTAS Resync Skipped', "PUPTAS resync skipped for health profile #{$profile->id}: missing reference number.", 'Health Records', 'ACTION');

            return back()->with('error', 'PUPTAS resync skipped because the reference number is missing.');
        }

        if (
            str_starts_with($upperReference, 'CLN-')
            || str_starts_with($upperReference, 'LOC-')
            || str_starts_with($upperReference, 'TEST-LOCAL')
        ) {
            $this->updatePuptasSyncState($profile, 'not_applicable', 'PUPTAS resync skipped because this is a local clinic reference.');
            $this->logActivity('PUPTAS Resync Skipped', "PUPTAS resync skipped for {$referenceNumber}: local clinic reference.", 'Health Records', 'ACTION');

            return back()->with('info', 'PUPTAS resync is not applicable for local clinic references.');
        }

        $this->updatePuptasSyncState($profile, 'syncing', 'Manual PUPTAS resync is in progress.');
        $this->logActivity('PUPTAS Resync Attempted', "Manual PUPTAS resync attempted for {$referenceNumber}.", 'Health Records', 'ACTION');

        $syncResult = $puptasService->sendWithRetry($referenceNumber, $idpStudentId, true);

        if ($syncResult['success'] ?? false) {
            $this->updatePuptasSyncState($profile, 'synced', 'Approved health clearance synced to PUPTAS.', true);
            $this->logActivity('PUPTAS Resync Successful', "Manual PUPTAS resync succeeded for {$referenceNumber}.", 'Health Records', 'ACTION');

            return back()->with('success', 'PUPTAS resync completed successfully.');
        }

        $message = $syncResult['message'] ?? 'The PUPTAS resync attempt failed.';
        $this->updatePuptasSyncState($profile, 'failed', $message);
        $this->logActivity('PUPTAS Resync Failed', "Manual PUPTAS resync failed for {$referenceNumber}: {$message}", 'Health Records', 'ERROR');

        return back()->with('error', 'PUPTAS resync failed: ' . $message);
    }

    public function exportHealthPdf($id)
    {
        $profile = HealthProfile::with('user')->findOrFail($id);
        $calculatedAge = !empty($profile->user->DOB)
            ? Carbon::parse($profile->user->DOB)->age
            : null;

        $pdf = Pdf::loadView('admin.show_health_pdf', compact('profile', 'calculatedAge'));
        $pdf->setPaper([0, 0, 612, 936]);

        $studentNumber = trim((string) ($profile->user->student_number ?: $profile->user->student_id ?: $profile->id));
        $fileName = 'health-form-' . preg_replace('/[^A-Za-z0-9\\-_]+/', '-', $studentNumber) . '.pdf';

        return $pdf->download($fileName);
    }

    public function showHealthPlain($id)
    {
        $profile = HealthProfile::with('user')->findOrFail($id);
        $calculatedAge = !empty($profile->user->DOB)
            ? Carbon::parse($profile->user->DOB)->age
            : null;

        return view('admin.show_health_pdf', compact('profile', 'calculatedAge'));
    }

// 1. Para lumabas 'yung page (GET)
public function showSignPage($id)
{
    if (!$this->canSignHealthClearance()) {
        return redirect()->route('admin.health_records')
            ->with('error', 'Only authorized clinic officers can verify and approve health records.');
    }

    // Ginaya ko ang variable name na $record para tugma sa blade na binigay ko kanina
    $record = HealthProfile::with('user')->findOrFail($id);
    return view('admin.sign_clearance', compact('record'));
}

// 2. Para sa pag-save ng pinirmahan (PUT)
public function updateClearance(Request $request, $id)
{
    if (!$this->canSignHealthClearance()) {
        return redirect()->route('admin.health_records')
            ->with('error', 'Only authorized clinic officers can verify and approve health records.');
    }

    $request->validate([
        'clearance_status' => ['required', Rule::in(['Fully Cleared', 'Issued', 'For Verification', 'Pending/Conditional', 'Pending Resubmission', 'Rejected'])],
        'pending_reason'   => ['nullable', 'string'],
        'verified_at'      => ['nullable', 'date'],
        'medical_condition_remarks' => ['nullable', 'string'],
        'physical_assessment_status' => ['required', Rule::in(['Not Yet Conducted', 'Completed / Passed'])],
        'documents_valid' => ['nullable', 'accepted'],
        'resubmission_required_documents' => ['nullable', 'array'],
        'resubmission_required_documents.*' => ['string', Rule::in(['student_photo', 'health_declaration', 'medical_certificate', 'chest_xray_result', 'pwd_id_proof'])],
    ]);

    $record = HealthProfile::findOrFail($id);
    $previousStatus = (string) $record->clearance_status;
    $requestedStatus = (string) $request->input('clearance_status');
    $isApproval = in_array($requestedStatus, ['Issued', 'Fully Cleared'], true);
    $documentsValid = $request->boolean('documents_valid');

    if (in_array($requestedStatus, ['Pending/Conditional', 'Pending Resubmission'], true) && trim((string) $request->input('pending_reason')) === '') {
        return back()->withInput()->with('error', 'Nurse remarks are required when setting a student as pending or requesting resubmission.');
    }

    if ($requestedStatus === 'Pending Resubmission' && empty($request->input('resubmission_required_documents', []))) {
        return back()->withInput()->with('error', 'Select at least one document that needs resubmission.');
    }

    if ($isApproval && (!$documentsValid || $request->input('physical_assessment_status') !== 'Completed / Passed')) {
        return back()->withInput()->with('error', 'Medical clearance can only be issued after documents are marked valid and physical assessment is Completed / Passed.');
    }

    // Update Status
    $record->clearance_status = $requestedStatus;
    $record->pending_reason   = $isApproval ? null : $request->pending_reason;
    $record->medical_condition_remarks = $request->input('medical_condition_remarks');
    $record->physical_assessment_status = $request->input('physical_assessment_status');
    $record->documents_valid = $requestedStatus === 'Pending Resubmission' ? false : $documentsValid;
    $approvalDate = $isApproval ? ($request->verified_at ?? now()) : null;
    $record->verified_at = $approvalDate;
    $record->approved_by_user_id = $isApproval ? auth()->id() : null;
    $record->resubmission_required_documents = $requestedStatus === 'Pending Resubmission'
        ? array_values(array_unique((array) $request->input('resubmission_required_documents', [])))
        : null;
    $record->resubmission_requested_at = $requestedStatus === 'Pending Resubmission' ? now() : null;
    $record->resubmitted_at = $requestedStatus === 'Pending Resubmission' ? null : $record->resubmitted_at;

    if ($record->save()) {
        if ($record->user) {
            $record->user->is_health_profile_completed = $isApproval ? 1 : 0;
            $record->user->save();

            if ($isApproval) {
                try {
                    $puptasService = app(PuptasWebhookService::class);
                    $referenceNumber = $this->resolvePuptasReferenceNumber($record);
                    $idpStudentId = $this->resolvePuptasIdpStudentId($record);

                    if ($referenceNumber === '') {
                        $this->updatePuptasSyncState($record, 'missing_reference_number', 'PUPTAS sync skipped because the reference number is still missing.');
                        \Log::warning("PUPTAS Sync Skipped for User {$record->user->id}: missing reference_number.");
                        return redirect()->route('admin.health_records')
                            ->with('success', 'Medical clearance updated, but PUPTAS sync was skipped because reference number is missing.');
                    }

                    $this->updatePuptasSyncState($record, 'syncing', 'Preparing the approved health clearance for PUPTAS.');
                    $syncResult = $puptasService->sendWithRetry($referenceNumber, $idpStudentId, true);

                    if (!$syncResult['success']) {
                        $this->updatePuptasSyncState(
                            $record,
                            'failed',
                            $syncResult['message'] ?? 'The PUPTAS sync attempt failed.',
                        );
                        \Log::error("PUPTAS Sync Failed for reference {$referenceNumber} / student_id {$idpStudentId}: " . ($syncResult['message'] ?? 'Unknown error'));
                    } else {
                        $this->updatePuptasSyncState($record, 'synced', 'Approved health clearance synced to PUPTAS.', true);
                    }
                } catch (\Exception $e) {
                    $referenceNumber = trim((string) (($record->reference_number ?? '') ?: ($record->student_number ?? '') ?: optional($record->user)->student_number));
                    $idpStudentId = trim((string) (optional($record->user)->student_id ?: $record->student_id));
                    $this->updatePuptasSyncState($record, 'failed', $e->getMessage());
                    \Log::error("PUPTAS Sync Failed for reference {$referenceNumber} / student_id {$idpStudentId}: " . $e->getMessage());
                }
            } else {
                $this->updatePuptasSyncState($record, null, null);
            }
        }

        return redirect()->route('admin.health_records')
                         ->with('success', 'Health Clearance status updated successfully.');
    }

    return back()->with('error', 'Failed to save to database.');
}

    public function requestHealthProfileResubmission(Request $request, $id)
    {
        $validated = $request->validate([
            'pending_reason' => ['required', 'string', 'max:2000'],
            'resubmission_required_documents' => ['required', 'array', 'min:1'],
            'resubmission_required_documents.*' => ['string', Rule::in([
                'student_photo',
                'health_declaration',
                'medical_certificate',
                'chest_xray_result',
                'pwd_id_proof',
            ])],
            'clear_uploaded_documents' => ['nullable', 'boolean'],
            'return_to' => ['nullable', 'string', Rule::in(['health_records', 'show_health'])],
        ]);

        $record = HealthProfile::with('user')->findOrFail($id);
        $wasAlreadyIssued = in_array($record->clearance_status, ['Issued', 'Fully Cleared'], true)
            || !empty($record->verified_at);
        $requestedDocuments = array_values(array_unique((array) $validated['resubmission_required_documents']));
        $documentColumns = [
            'student_photo' => 'student_photo',
            'health_declaration' => 'health_declaration',
            'medical_certificate' => 'medical_certificate',
            'chest_xray_result' => 'chest_xray_result',
            'pwd_id_proof' => 'pwd_id_proof',
        ];

        if (!$wasAlreadyIssued) {
            $record->clearance_status = 'Pending Resubmission';
        }
        $record->pending_reason = trim((string) $validated['pending_reason']);
        $record->documents_valid = false;
        $record->resubmission_required_documents = $requestedDocuments;
        $record->resubmission_requested_at = now();
        $record->resubmitted_at = null;

        if ($request->boolean('clear_uploaded_documents')) {
            foreach ($requestedDocuments as $documentKey) {
                $column = $documentColumns[$documentKey] ?? null;
                if ($column) {
                    $record->{$column} = null;
                }
            }
        }

        $record->save();

        if ($record->user && !$wasAlreadyIssued) {
            $record->user->is_health_profile_completed = 0;
            $record->user->save();
        }

        ActivityLog::create([
            'user_id' => auth()->id(),
            'user_name' => auth()->user()?->name ?? auth()->user()?->email ?? 'System',
            'user_role' => strtolower((string) (auth()->user()?->user_role ?? '')),
            'action' => 'Health Profile Resubmission Requested',
            'module' => 'Health Records',
            'event_type' => 'health_profile_resubmission_requested',
            'description' => 'Requested replacement file/s for approved health profile #' . $record->id . ': ' . implode(', ', $requestedDocuments),
            'route_name' => optional(request()->route())->getName(),
            'request_method' => request()->method(),
            'request_path' => request()->path(),
            'response_status' => 200,
            'ip_address' => request()->ip(),
        ]);

        $message = $request->boolean('clear_uploaded_documents')
            ? 'Replacement file request sent. Selected uploaded document references were removed from the record.'
            : 'Replacement file request sent. The student will see the reupload prompt in Health Records.';

        $redirect = ($validated['return_to'] ?? 'show_health') === 'health_records'
            ? redirect()->route('admin.health_records', ['tab' => $wasAlreadyIssued ? 'approved' : 'pending_compliance'])
            : redirect()->route('admin.show_health', $record->id);

        return $redirect->with('success', $message);
    }

    public function markHealthProfileForFinalReview(Request $request, $id)
    {
        $record = HealthProfile::with('user')->findOrFail($id);

        $record->clearance_status = 'For Final Review';
        $record->physical_assessment_status = 'Encoded / For Final Review';
        $record->verified_at = null;
        $record->approved_by_user_id = null;
        $record->save();

        ActivityLog::create([
            'user_id' => auth()->id(),
            'user_name' => auth()->user()?->name ?? auth()->user()?->email ?? 'System',
            'user_role' => strtolower((string) (auth()->user()?->user_role ?? '')),
            'action' => 'Health Profile Moved to Final Review',
            'module' => 'Health Records',
            'event_type' => 'health_profile_for_final_review',
            'description' => 'Pending compliance record was moved to Final Review without triggering PUPTAS sync.',
            'route_name' => optional($request->route())->getName(),
            'http_method' => 'POST',
            'request_path' => '/' . ltrim((string) $request->path(), '/'),
            'status_code' => 200,
            'subject_type' => HealthProfile::class,
            'subject_id' => (string) $record->id,
            'metadata' => [
                'health_profile_id' => $record->id,
                'reference_number' => $record->reference_number,
                'student_id' => $record->student_id,
                'previous_pending_reason' => $record->pending_reason,
            ],
            'ip_address' => $request->ip(),
            'user_agent' => substr((string) $request->userAgent(), 0, 255),
        ]);

        return redirect()
            ->route('admin.health_records')
            ->with('success', 'Record moved to Final Review. It will appear in the Final Review applicant list.');
    }

    public function markHealthProfileForApproval(Request $request, $id)
    {
        $record = HealthProfile::with('user')->findOrFail($id);

        $previousPendingReason = $record->pending_reason;
        $previousTrackingRemarks = $record->medical_condition_remarks;
        $previousDocuments = $record->resubmission_required_documents;

        $record->clearance_status = 'For Verification';
        $record->physical_assessment_status = 'Not Yet Conducted';
        $record->pending_reason = null;
        $record->medical_condition_remarks = null;
        $record->resubmission_required_documents = null;
        $record->resubmission_requested_at = null;
        $record->resubmitted_at = null;
        $record->verified_at = null;
        $record->approved_by_user_id = null;
        $record->save();

        ActivityLog::create([
            'user_id' => auth()->id(),
            'user_name' => auth()->user()?->name ?? auth()->user()?->email ?? 'System',
            'user_role' => strtolower((string) (auth()->user()?->user_role ?? '')),
            'action' => 'Health Profile Moved to Pending Approval',
            'module' => 'Health Records',
            'event_type' => 'health_profile_for_approval',
            'description' => 'Pending compliance record was moved back to Pending Approval without triggering PUPTAS sync.',
            'route_name' => optional($request->route())->getName(),
            'http_method' => 'POST',
            'request_path' => '/' . ltrim((string) $request->path(), '/'),
            'status_code' => 200,
            'subject_type' => HealthProfile::class,
            'subject_id' => (string) $record->id,
            'metadata' => [
                'health_profile_id' => $record->id,
                'reference_number' => $record->reference_number,
                'student_id' => $record->student_id,
                'previous_pending_reason' => $previousPendingReason,
                'previous_tracking_remarks' => $previousTrackingRemarks,
                'previous_resubmission_required_documents' => $previousDocuments,
            ],
            'ip_address' => $request->ip(),
            'user_agent' => substr((string) $request->userAgent(), 0, 255),
        ]);

        return redirect()
            ->route('admin.health_records', ['tab' => 'pending_approval'])
            ->with('success', 'Record moved to Pending Approval.');
    }

    public function uploadMedicalAssessmentCopy(Request $request)
    {
        $validated = $request->validate([
            'reference_number' => ['required', 'string', 'max:120'],
            'medical_assessment_copy' => ['required', 'file', 'mimes:pdf,jpg,jpeg,png', 'max:10240'],
        ]);

        $referenceNumber = trim((string) $validated['reference_number']);
        $file = $request->file('medical_assessment_copy');

        // Try to find existing user first
        $user = User::query()
            ->where('student_number', $referenceNumber)
            ->orWhere('student_id', $referenceNumber)
            ->first();

        // If user exists, save to health profile
        if ($user) {
            $profile = HealthProfile::firstOrCreate(
                ['user_id' => $user->id],
                [
                    'student_id' => (string) ($user->student_id ?? ''),
                    'student_number' => (string) ($user->student_number ?? ''),
                    'reference_number' => $referenceNumber,
                    'course_college' => (string) ($user->course ?? ''),
                    'birthday' => (string) ($user->DOB ?? ''),
                    'sex' => (string) ($user->gender ?? ''),
                ]
            );

            $profile->student_id = $profile->student_id ?: (string) ($user->student_id ?? '');
            $profile->student_number = $profile->student_number ?: (string) ($user->student_number ?? '');
            $profile->reference_number = $referenceNumber;
            $profile->course_college = $profile->course_college ?: (string) ($user->course ?? '');
            $profile->birthday = $profile->birthday ?: (string) ($user->DOB ?? '');
            $profile->sex = $profile->sex ?: (string) ($user->gender ?? '');

            if (!empty($profile->medical_assessment_upload) && Storage::disk('public')->exists($profile->medical_assessment_upload)) {
                Storage::disk('public')->delete($profile->medical_assessment_upload);
            }

            $profile->medical_assessment_upload = $file->store('health_profiles/medical_assessment_uploads', 'public');
            $profile->save();
        } else {
            // Applicant not yet in system - save to pending assessments table
            // Will be linked when they register/login later
            $filePath = $file->store('pending_medical_assessments', 'public');

            \App\Models\PendingMedicalAssessment::updateOrCreate(
                [
                    'reference_number' => $referenceNumber,
                    // Email will need to come from applicant data in the form
                ],
                [
                    'email' => trim((string) $request->input('email', '')),
                    'file_path' => $filePath,
                    'file_name' => $file->getClientOriginalName(),
                    'file_type' => $file->getMimeType(),
                    'file_size' => $file->getSize(),
                ]
            );
        }

        $message = 'Medical assessment copy uploaded successfully.';

        if ($request->expectsJson()) {
            return response()->json([
                'status' => 'ok',
                'message' => $message,
            ]);
        }

        return back()->with('success', $message);
    }

    public function appointments()
    {
        Appointment::expireOverduePending();

        $appointments = Appointment::with('user')
            ->orderByRaw("CASE LOWER(status)
                WHEN 'pending' THEN 0
                WHEN 'approved' THEN 1
                WHEN 'scheduled' THEN 2
                WHEN 'completed' THEN 3
                WHEN 'missed' THEN 4
                WHEN 'expired' THEN 5
                WHEN 'cancelled' THEN 6
                WHEN 'rejected' THEN 7
                ELSE 8
            END")
            ->orderBy('date')
            ->orderBy('time')
            ->latest()
            ->get();
        $consultationComments = Consultation::query()
            ->whereIn('user_id', $appointments->pluck('user_id')->filter()->unique())
            ->whereIn('consultation_date', $appointments->pluck('date')->filter()->unique())
            ->latest('consultation_date')
            ->latest('time_in')
            ->get()
            ->groupBy(fn ($consultation) => $consultation->user_id . '|' . optional($consultation->consultation_date)->format('Y-m-d'));

        $appointments->each(function ($appointment) use ($consultationComments) {
            $key = $appointment->user_id . '|' . Carbon::parse($appointment->date)->format('Y-m-d');
            $appointment->clinical_findings_comment = trim((string) optional($consultationComments->get($key)?->first())->comments);
        });

        return view('admin.appointments', compact('appointments'));
    }

    public function inventory()
    {
        $items = Item::query()
            ->with(['medicineType', 'movements.user'])
            ->orderBy('name')
            ->get();
        $medicineTypes = MedicineType::query()
            ->orderBy('name')
            ->get();
        if ($medicineTypes->isEmpty()) {
            $defaultMedicineTypes = [
                'ANALGESIC',
                'MUSCLE RELAXANT',
                'ANTIPYRETIC',
                'MUCOLYTIC',
                'DECONGESTANT',
                'ANTITUSSIVE',
                'ANTI-HYPERTENSION',
                'CORONARY DILATOR',
                'ANTIVERTIGO',
                'ANTIBIOTIC',
                'ANTISPASMODIC',
                'GASTROKINETIC/ANTIEMETIC',
                'ANTIMOTILITY',
                'ELECTROLYTE ORAL',
                'ANTACID/ANTIFLATULENT',
                'PROTON PUMP INHIBITOR',
                'ANTIHISTAMINE',
                'ANTI-ASTHMA',
                'IV SET',
                'TOPICAL OINTMENT/GEL/LOTION',
                'EYE / EAR DROPS',
            ];

            $medicineTypes = collect($defaultMedicineTypes)->map(function ($name) {
                return (object) [
                    'id' => $name,
                    'name' => $name,
                ];
            });
        }
        $reportMonth = now()->format('Y-m');

        return view('admin.inventory', compact('items', 'medicineTypes', 'reportMonth'));
    }

    public function reports()
{
    Appointment::expireOverduePending();

   
    $appointments = Appointment::all();
    $total = Appointment::count();
    $approved = Appointment::where('status', 'Approved')->count();
    $cancelled = Appointment::where('status', 'Cancelled')->count();
    

    $lowStockCount = Item::whereColumn('quantity', '<=', 'minimum_stock')->where('quantity', '>', 0)->count(); 
    
   
    $appointmentsToday = Appointment::where('status', 'Approved')
                                    ->whereDate('date', \Carbon\Carbon::today())
                                    ->count();


    $totalConsultations = Appointment::where('status', 'Approved')
                                     ->whereMonth('date', \Carbon\Carbon::now()->month)
                                     ->count();

    $items = Item::all();

    return view('admin.reports', compact(
        'appointments', 'total', 'approved', 'cancelled', 
        'lowStockCount', 'appointmentsToday', 'totalConsultations', 'items'
    ));
}

    public function settings()
    {
        $admin = Auth::user();
        $settings = Setting::first();
        if(!$settings) { $settings = new Setting(); }
        $cmsProfile = $admin ? $this->buildCmsAdminProfile($admin) : [];

        return view('admin.settings', compact('admin', 'settings', 'cmsProfile'));
    }

    public function settingsPersonal()
    {
        $admin = Auth::user();
        $settings = Setting::first();
        if(!$settings) { $settings = new Setting(); }
        $cmsProfile = $admin ? $this->buildCmsAdminProfile($admin) : [];

        return view('admin.settings-personal', compact('admin', 'settings', 'cmsProfile'));
    }

    public function settingsClinic()
    {
        $admin = Auth::user();
        $settings = Setting::first();
        if(!$settings) { $settings = new Setting(); }
        $cmsProfile = $admin ? $this->buildCmsAdminProfile($admin) : [];

        return view('admin.settings-clinic', compact('admin', 'settings', 'cmsProfile'));
    }

    public function settingsPreferences()
    {
        $admin = Auth::user();
        $settings = Setting::first();
        if(!$settings) { $settings = new Setting(); }
        $cmsProfile = $admin ? $this->buildCmsAdminProfile($admin) : [];

        return view('admin.settings-preferences', compact('admin', 'settings', 'cmsProfile'));
    }

    public function settingsMedicalConfiguration()
    {
        $admin = Auth::user();
        $settings = Setting::first();
        if(!$settings) { $settings = new Setting(); }
        $cmsProfile = $admin ? $this->buildCmsAdminProfile($admin) : [];

        return view('admin.settings-medical-configuration', compact('admin', 'settings', 'cmsProfile'));
    }

    public function notificationsFeed(Request $request)
    {
        /** @var \App\Models\User|null $user */
        $user = Auth::user();
        if (!$user) {
            return response()->json([
                'count' => 0,
                'notifications' => [],
            ], 401);
        }

        $currentRole = User::normalizeRole((string) ($user->user_role ?? ''));
        $isStudentAssistant = $currentRole === User::ROLE_ADMIN;

        $appointmentsUrl = $isStudentAssistant ? url('/assistant/appointments') : url('/admin/appointments');
        $healthRecordsUrl = route('admin.health_records') . '?tab=pending_approval';
        $readMap = is_array($user->notification_read_map) ? $user->notification_read_map : [];

        $recentPendingAppointments = Appointment::query()
            ->where('status', 'Pending')
            ->orderByDesc('created_at')
            ->limit(4)
            ->get();

        $recentHealthFormSubmissions = $isStudentAssistant
            ? collect()
            : HealthProfile::query()
                ->with('user')
                ->where(function ($query) {
                    $query->whereIn('clearance_status', ['Pending', 'For Verification'])
                        ->orWhereNull('clearance_status')
                        ->orWhere('clearance_status', '');
                })
                ->latest('created_at')
                ->limit(3)
                ->get();

        $notifications = collect();

        foreach ($recentPendingAppointments as $appointment) {
            $notifications->push([
                'id' => 'appointment-pending:' . $appointment->id . ':' . optional($appointment->updated_at)->timestamp,
                'kind' => 'appointment',
                'title' => 'New appointment request',
                'message' => 'A new appointment request is waiting for review.',
                'link' => $appointmentsUrl . '?highlight_appointment=' . $appointment->id,
            ]);
        }

        foreach ($recentHealthFormSubmissions as $healthProfile) {
            $notifications->push([
                'id' => 'health-form:' . $healthProfile->id . ':' . optional($healthProfile->created_at)->timestamp,
                'kind' => 'health',
                'title' => 'New health form submission',
                'message' => 'A student submitted a health record for verification.',
                'link' => $healthRecordsUrl . '&highlight_health=' . $healthProfile->id,
            ]);
        }

        $unread = $notifications
            ->filter(function (array $notification) use ($readMap) {
                return !isset($readMap[$notification['id']]);
            })
            ->values();

        return response()->json([
            'count' => $unread->count(),
            'notifications' => $unread,
        ]);
    }

    public function markAllAdminNotificationsRead(Request $request)
    {
        $user = Auth::user();

        if (!$user) {
            return back()->with('error', 'Unable to mark notifications as read.');
        }

        $notificationIds = collect((array) $request->input('notification_ids', []))
            ->map(fn ($id) => trim((string) $id))
            ->filter()
            ->unique()
            ->values();

        if ($notificationIds->isEmpty()) {
            return back()->with('success', 'No unread notifications to update.');
        }

        $readMap = is_array($user->notification_read_map) ? $user->notification_read_map : [];
        $timestamp = now()->toIso8601String();

        foreach ($notificationIds as $notificationId) {
            $readMap[$notificationId] = $timestamp;
        }

        $user->notification_read_map = $readMap;
        $user->save();

        $redirectTo = trim((string) $request->input('redirect_to', ''));
        if ($redirectTo !== '' && (str_starts_with($redirectTo, '/') || str_starts_with($redirectTo, url('/')))) {
            return redirect()->to($redirectTo)->with('success', 'All notifications marked as read.');
        }

        return back()->with('success', 'All notifications marked as read.');
    }

    // ==========================================
    //  PART 2: ACTION METHODS (The Real Logic)
    // ==========================================

    // --- 1. APPOINTMENT ACTIONS ---
    public function updateStatus($id, $status)
    {
        $appointment = Appointment::find($id);
        if ($appointment) {
            $appointment->status = $status;
            if ($status === 'Approved') {
                if (Schema::hasColumn('appointments', 'approval_message')) {
                    $appointment->approval_message = trim((string) request()->query('message', '')) ?: null;
                }

                if (Schema::hasColumn('appointments', 'approval_reminders')) {
                    $appointment->approval_reminders = collect((array) request()->query('reminders', []))
                        ->map(fn ($reminder) => trim((string) $reminder))
                        ->filter()
                        ->values()
                        ->all();
                }
            }
            $appointment->save();
            $actionMessage = trim((string) request()->query('message', ''));

            \App\Models\ActivityLog::create([
            'user_id'     => auth()->id(), 
            'user_name'   => auth()->user()->name,
            'action'      => 'Status Updated',
            'description' => "Updated Appointment #$id status to $status" . ($actionMessage !== '' ? ". Message: {$actionMessage}" : ''),
            'ip_address'  => request()->ip(),
            'user_agent'  => request()->userAgent(),
        ]);

            return redirect()->back()->with('success', "Appointment marked as $status.");
        }
        return redirect()->back()->with('error', "Appointment not found.");
    }

    public function reschedule($id, Request $request)
    {
        $request->validate([
            'date' => ['required', 'date'],
            'time' => ['required'],
            'reschedule_reason' => ['nullable', 'string', 'max:500'],
        ]);

        $appointment = Appointment::find($id);
        if ($appointment) {
            $appointment->date = $request->date;
            $appointment->time = $request->time;
            $appointment->status = 'Approved';
            $appointment->save();

            // LOGS CODES
             \App\Models\ActivityLog::create([
            'user_id'     => auth()->id(),
            'user_name'   => auth()->user()->name,
            'action'      => 'Appointment Rescheduled', 
            'description' => "Rescheduled Appointment #$id to $request->date at $request->time. Status set to Approved." . ($request->filled('reschedule_reason') ? " Reason: {$request->reschedule_reason}" : ''), 
            'ip_address'  => request()->ip(),
            'user_agent'  => request()->userAgent(),
        ]);

            return redirect()->back()->with('success', "Appointment rescheduled successfully.");
        }
        return redirect()->back()->with('error', "Error rescheduling.");
    }

    // --- 2. INVENTORY ACTIONS ---
public function storeItem(Request $request)
{
    $request->validate([
        'name' => ['required', 'string', 'max:255'],
        'category' => ['required', 'string', 'max:255'],
        'stock_number' => ['nullable', 'string', 'max:50'],
        'starting_stock' => ['required', 'numeric', 'min:0'],
        'consumed' => ['nullable', 'numeric', 'min:0'],
        'quantity' => ['required', 'numeric', 'min:0'],
        'unit' => ['required', 'string', 'max:50'],
        'minimum_stock' => ['nullable', 'numeric', 'min:0'],
        'dispensing_unit' => ['nullable', 'string', 'max:50'],
        'units_per_stock_unit' => ['nullable', 'integer', 'min:1'],
        'date_added' => ['required', 'string'],
        'medicine_type_id' => ['nullable', 'string', 'max:255'],
        'medicine_type_custom' => ['nullable', 'string', 'max:255'],
        'expiration_date' => ['nullable', 'string'],
    ]);

    // 1. Prepare data and sanitize medicine-specific fields
    $normalizer = new InventoryDataNormalizer();
    $data = $request->all();
    $data['date_added'] = $normalizer->normalizeDate($request->input('date_added')) ?: now()->toDateString();
    $data['expiration_date'] = $normalizer->normalizeDate($request->input('expiration_date', ''));
    $data['unit'] = trim((string) $request->input('unit', 'pcs')) ?: 'pcs';
    if (Schema::hasColumn('items', 'stock_number')) {
        $data['stock_number'] = trim((string) $request->input('stock_number', '')) ?: null;
    }
    $data['starting_stock'] = (float) $request->input('starting_stock', 0);
    $data['consumed'] = max(0, (float) $request->input('consumed', 0));
    $data['quantity'] = (float) $request->input('quantity', 0);
    $data['minimum_stock'] = $request->filled('minimum_stock') ? (float) $request->input('minimum_stock') : 10;
    $data['dispensing_unit'] = trim((string) $request->input('dispensing_unit', '')) ?: null;
    $data['units_per_stock_unit'] = $request->filled('units_per_stock_unit')
        ? max(1, (int) $request->input('units_per_stock_unit'))
        : null;
    $selectedMedicineType = null;
    $medicineTypeValue = trim((string) $request->input('medicine_type_id', ''));
    $medicineTypeCustom = trim((string) $request->input('medicine_type_custom', ''));
    if ($medicineTypeValue === '__custom__' || $medicineTypeCustom !== '') {
        $medicineTypeName = $medicineTypeCustom !== '' ? $medicineTypeCustom : trim((string) $request->input('medicine_type_id', ''));
        if ($medicineTypeName !== '' && $medicineTypeName !== '__custom__') {
            $selectedMedicineType = MedicineType::firstOrCreate(['name' => $medicineTypeName]);
        }
    } elseif ($medicineTypeValue !== '') {
        $selectedMedicineType = ctype_digit($medicineTypeValue)
            ? MedicineType::find((int) $medicineTypeValue)
            : MedicineType::firstOrCreate(['name' => $medicineTypeValue]);
    }
    $data['medicine_type_id'] = $selectedMedicineType?->id;
    $data['medicine_type'] = $selectedMedicineType?->name;
    if ($request->category !== 'Medicine') {
        $data['medicine_type_id'] = null;
        $data['medicine_type'] = null;
        $data['expiration_date'] = null;
        $data['dispensing_unit'] = null;
        $data['units_per_stock_unit'] = null;
    }

    $item = Item::create($data);
    $this->recordInventoryMovement(
        $item,
        'created',
        (float) $item->starting_stock,
        0,
        (float) $item->starting_stock,
        'Initial stock encoded.'
    );
    if ((float) $item->consumed > 0) {
        $this->recordInventoryMovement(
            $item,
            'consumed',
            (float) $item->consumed,
            (float) $item->starting_stock,
            (float) $item->quantity,
            'Initial consumed quantity encoded.'
        );
    }

    // 2. LOGS CODES
    $typeInfo = $item->medicine_type ? " ({$item->medicine_type})" : "";
    $expInfo = $item->expiration_date ? " | Exp: " . $item->expiration_date->format('M d, Y') : "";
    $conversionInfo = $item->hasDispensingConversion()
        ? " | Dispense as: {$item->dispensing_unit} ({$item->units_per_stock_unit} per {$item->unit})"
        : "";

    \App\Models\ActivityLog::create([
        'user_id'     => auth()->id(),
        'user_name'   => auth()->user()->name,
        'action'      => 'Inventory Update', 
        'description' => "Added new item: " . $item->name . $typeInfo . " (Qty: " . $this->formatInventoryQuantity((float) $item->quantity) . " {$item->unit})" . $conversionInfo . $expInfo,
        'ip_address'  => request()->ip(),
        'user_agent'  => request()->userAgent(),
    ]);

    return redirect()->back()->with('success', 'New item added to inventory.');
}

public function updateItem($id, Request $request)
{
    $item = Item::find($id);
    
    if ($item) {
        $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'category' => ['required', 'string', 'max:255'],
            'stock_number' => ['nullable', 'string', 'max:50'],
            'starting_stock' => ['required', 'numeric', 'min:0'],
            'consumed' => ['nullable', 'numeric', 'min:0'],
            'quantity' => ['required', 'numeric', 'min:0'],
            'unit' => ['required', 'string', 'max:50'],
            'minimum_stock' => ['nullable', 'numeric', 'min:0'],
            'dispensing_unit' => ['nullable', 'string', 'max:50'],
            'units_per_stock_unit' => ['nullable', 'integer', 'min:1'],
            'date_added' => ['required', 'date'],
            'medicine_type_id' => ['nullable', 'string', 'max:255'],
            'medicine_type_custom' => ['nullable', 'string', 'max:255'],
            'expiration_date' => ['nullable', 'date'],
        ]);

        $oldName = $item->name; // Using 'name' as per your blade file
        $oldQuantity = (float) $item->quantity;
        
        // 1. Prepare and sanitize data for update
        $data = $request->all();
        $data['unit'] = trim((string) $request->input('unit', 'pcs')) ?: 'pcs';
        if (Schema::hasColumn('items', 'stock_number')) {
            $data['stock_number'] = trim((string) $request->input('stock_number', '')) ?: null;
        }
        $data['starting_stock'] = (float) $request->input('starting_stock', 0);
        $data['consumed'] = max(0, (float) $request->input('consumed', 0));
        $data['quantity'] = (float) $request->input('quantity', 0);
        $data['minimum_stock'] = $request->filled('minimum_stock') ? (float) $request->input('minimum_stock') : (float) ($item->minimum_stock ?: 10);
        $data['dispensing_unit'] = trim((string) $request->input('dispensing_unit', '')) ?: null;
        $data['units_per_stock_unit'] = $request->filled('units_per_stock_unit')
            ? max(1, (int) $request->input('units_per_stock_unit'))
            : null;
        $selectedMedicineType = null;
        $medicineTypeValue = trim((string) $request->input('medicine_type_id', ''));
        $medicineTypeCustom = trim((string) $request->input('medicine_type_custom', ''));
        if ($medicineTypeValue === '__custom__' || $medicineTypeCustom !== '') {
            $medicineTypeName = $medicineTypeCustom !== '' ? $medicineTypeCustom : trim((string) $request->input('medicine_type_id', ''));
            if ($medicineTypeName !== '' && $medicineTypeName !== '__custom__') {
                $selectedMedicineType = MedicineType::firstOrCreate(['name' => $medicineTypeName]);
            }
        } elseif ($medicineTypeValue !== '') {
            $selectedMedicineType = ctype_digit($medicineTypeValue)
                ? MedicineType::find((int) $medicineTypeValue)
                : MedicineType::firstOrCreate(['name' => $medicineTypeValue]);
        }
        $data['medicine_type_id'] = $selectedMedicineType?->id;
        $data['medicine_type'] = $selectedMedicineType?->name;
        if ($request->category !== 'Medicine') {
            $data['medicine_type_id'] = null;
            $data['medicine_type'] = null;
            $data['expiration_date'] = null;
            $data['dispensing_unit'] = null;
            $data['units_per_stock_unit'] = null;
        }

        $item->update($data);
        $newQuantity = (float) $item->quantity;
        if (abs($newQuantity - $oldQuantity) > 0.00001) {
            $this->recordInventoryMovement(
                $item,
                'adjusted',
                $newQuantity - $oldQuantity,
                $oldQuantity,
                $newQuantity,
                'Quantity adjusted through edit item.'
            );
        } else {
            $this->recordInventoryMovement($item, 'edited', 0, $oldQuantity, $newQuantity, 'Item details updated.');
        }
        $conversionInfo = $item->hasDispensingConversion()
            ? " | Dispense as: {$item->dispensing_unit} ({$item->units_per_stock_unit} per {$item->unit})"
            : "";

        // 2. LOGS CODES
        \App\Models\ActivityLog::create([
            'user_id'     => auth()->id(),
            'user_name'   => auth()->user()->name,
            'action'      => 'Inventory Edited', 
            'description' => "Updated Item: $oldName (ID: #$id). New Qty: " . $this->formatInventoryQuantity((float) $item->quantity) . " {$item->unit}" . $conversionInfo . ($item->expiration_date ? " | New Exp: " . $item->expiration_date->format('M d, Y') : ""),
            'ip_address'  => request()->ip(),
            'user_agent'  => request()->userAgent(),
        ]);

        return redirect()->back()->with('success', 'Item updated successfully.');
    }
    
    return redirect()->back()->with('error', 'Item not found.');
}

public function restockItem($id, Request $request)
{
    $item = Item::find($id);
    if (!$item) {
        return redirect()->back()->with('error', 'Item not found.');
    }

    $validated = $request->validate([
        'restock_quantity' => ['required', 'numeric', 'min:0.01'],
        'restock_date' => ['nullable', 'date'],
        'batch_number' => ['nullable', 'string', 'max:120'],
        'supplier_source' => ['nullable', 'string', 'max:255'],
        'restock_notes' => ['nullable', 'string', 'max:1000'],
    ]);

    $stockBefore = (float) $item->quantity;
    $restockQuantity = (float) $validated['restock_quantity'];
    $item->quantity = $stockBefore + $restockQuantity;

    if (trim((string) ($validated['batch_number'] ?? '')) !== '') {
        $item->batch_number = trim((string) $validated['batch_number']);
    }

    if (trim((string) ($validated['supplier_source'] ?? '')) !== '') {
        $item->supplier_source = trim((string) $validated['supplier_source']);
    }

    if (!empty($validated['restock_date'])) {
        $item->date_added = $validated['restock_date'];
    }

    $item->save();

    $this->recordInventoryMovement(
        $item,
        'restocked',
        $restockQuantity,
        $stockBefore,
        (float) $item->quantity,
        $validated['restock_notes'] ?? 'Stock restocked.'
    );

    ActivityLog::create([
        'user_id' => auth()->id(),
        'user_name' => auth()->user()->name,
        'action' => 'Inventory Restocked',
        'description' => "Restocked {$item->name}: +" . $this->formatInventoryQuantity($restockQuantity) . " {$item->unit}. Current stock: " . $this->formatInventoryQuantity((float) $item->quantity) . " {$item->unit}.",
        'ip_address' => request()->ip(),
        'user_agent' => request()->userAgent(),
    ]);

    return redirect()->back()->with('success', 'Item restocked successfully.');
}

public function issueStock($id, Request $request)
{
    $item = Item::find($id);
    if (!$item) {
        return redirect()->back()->with('error', 'Item not found.');
    }

    $validated = $request->validate([
        'issue_quantity' => ['required', 'numeric', 'min:0.01'],
        'date_consumed' => ['required', 'date'],
        'issue_reason' => ['required', Rule::in(['Dispensed to Patient', 'Clinic Usage', 'Damaged/Expired', 'Other'])],
        'issue_remarks' => ['nullable', 'string', 'max:1000'],
    ]);

    $issueQuantity = (float) $validated['issue_quantity'];
    $stockBefore = (float) $item->quantity;

    if ($issueQuantity > $stockBefore) {
        return redirect()->back()
            ->withInput()
            ->with('error', 'Quantity to issue cannot exceed the current available stock.');
    }

    DB::transaction(function () use ($item, $validated, $issueQuantity, $stockBefore) {
        $item->quantity = max(0, $stockBefore - $issueQuantity);
        $item->consumed = max(0, (float) ($item->consumed ?? 0)) + $issueQuantity;
        $item->save();

        $this->recordInventoryMovement(
            $item,
            'issued',
            -1 * $issueQuantity,
            $stockBefore,
            (float) $item->quantity,
            $validated['issue_remarks'] ?? null,
            $validated['date_consumed'],
            $validated['issue_reason']
        );

        if (Schema::hasTable('inventory_logs')) {
            $legacyLog = [];
            $legacyMap = [
                'item_id' => $item->id,
                'user_id' => auth()->id(),
                'type' => 'issued',
                'quantity' => $issueQuantity,
                'stock_before' => $stockBefore,
                'stock_after' => (float) $item->quantity,
                'date_consumed' => $validated['date_consumed'],
                'movement_date' => $validated['date_consumed'],
                'reason' => $validated['issue_reason'],
                'purpose' => $validated['issue_reason'],
                'remarks' => $validated['issue_remarks'] ?? null,
                'notes' => $validated['issue_remarks'] ?? null,
                'created_at' => now(),
                'updated_at' => now(),
            ];

            foreach ($legacyMap as $column => $value) {
                if (Schema::hasColumn('inventory_logs', $column)) {
                    $legacyLog[$column] = $value;
                }
            }

            if ($legacyLog !== []) {
                DB::table('inventory_logs')->insert($legacyLog);
            }
        }
    });

    ActivityLog::create([
        'user_id' => auth()->id(),
        'user_name' => auth()->user()->name,
        'action' => 'Inventory Stock Issued',
        'description' => "Issued " . $this->formatInventoryQuantity($issueQuantity) . " {$item->unit} of {$item->name}. Reason: {$validated['issue_reason']}.",
        'ip_address' => request()->ip(),
        'user_agent' => request()->userAgent(),
    ]);

    return redirect()->back()->with('success', 'Stock issued successfully.');
}

public function analyzeInventoryImport(Request $request, InventoryImportAnalyzer $analyzer)
{
    $validated = $request->validate([
        'inventory_import_file' => ['required', 'file', 'max:10240'],
    ]);

    $file = $request->file('inventory_import_file');
    $extension = strtolower((string) $file->getClientOriginalExtension());
    $allowedExtensions = ['jpg', 'jpeg', 'png', 'webp', 'csv', 'tsv', 'txt', 'json'];

    if (!in_array($extension, $allowedExtensions, true)) {
        return redirect()->back()
            ->with('inventory_import_feedback', [
                'status' => 'error',
                'title' => 'Unsupported inventory file',
                'message' => 'Upload a clear inventory image or a CSV, TSV, TXT, or JSON file.',
            ])
            ->with('error', 'Upload a clear inventory image or a CSV, TSV, TXT, or JSON file.');
    }

    $analysis = $analyzer->analyze($file);
    if (!($analysis['ok'] ?? false)) {
        return redirect()->back()
            ->with('inventory_import_feedback', [
                'status' => 'error',
                'title' => 'Inventory scan needs attention',
                'message' => (string) ($analysis['message'] ?? 'Inventory import analysis failed.'),
            ])
            ->with('error', (string) ($analysis['message'] ?? 'Inventory import analysis failed.'));
    }

    $preview = $this->buildInventoryImportPreview($analysis);
    if (empty($preview['rows'])) {
        return redirect()->back()
            ->with('inventory_import_feedback', [
                'status' => 'error',
                'title' => 'No inventory rows found',
                'message' => 'No usable inventory rows were found in the uploaded file. Try a clearer image or a CSV/TSV export.',
            ])
            ->with('error', 'No usable inventory rows were found in the uploaded file.');
    }

    $request->session()->put('inventory_import_preview', $preview);

    ActivityLog::create([
        'user_id' => auth()->id(),
        'user_name' => auth()->user()->name,
        'action' => 'Inventory Import Analyzed',
        'description' => "Analyzed uploaded inventory file {$preview['source_name']} and prepared " . count($preview['rows']) . ' rows for review.',
        'ip_address' => $request->ip(),
        'user_agent' => $request->userAgent(),
    ]);

    return redirect()->route('admin.inventory')
        ->with('inventory_import_feedback', [
            'status' => 'success',
            'title' => 'Inventory scan ready',
            'message' => 'Review the extracted rows below, edit anything that looks wrong, then import the selected rows.',
        ])
        ->with('success', 'Inventory file analyzed. Review every row before importing.');
}

public function commitInventoryImport(Request $request)
{
    $rows = (array) $request->input('import_items', []);
    $selectedRows = collect($rows)
        ->filter(function ($row) {
            return is_array($row) && !empty($row['selected']);
        })
        ->values();

    if ($selectedRows->isEmpty()) {
        return redirect()->route('admin.inventory')
            ->with('error', 'Select at least one analyzed inventory row to import.');
    }

    $created = 0;
    $updated = 0;
    $skipped = 0;
    $errors = [];

    DB::transaction(function () use ($selectedRows, &$created, &$updated, &$skipped, &$errors) {
        foreach ($selectedRows as $index => $row) {
            $action = strtolower(trim((string) ($row['action'] ?? '')));
            if ($action === 'skip') {
                $skipped++;
                continue;
            }

            $data = $this->sanitizeInventoryImportRow($row);
            if (($data['name'] ?? '') === '') {
                $errors[] = 'Row ' . ($index + 1) . ' was skipped because the item name is missing.';
                continue;
            }

            $existingItem = null;
            $matchedItemId = (int) ($row['matched_item_id'] ?? 0);
            if ($matchedItemId > 0) {
                $existingItem = Item::find($matchedItemId);
            }
            if (!$existingItem) {
                $existingItem = $this->findInventoryImportMatch($data);
            }

            if ($action === 'update' && $existingItem) {
                $oldQuantity = (float) $existingItem->quantity;
                $existingItem->fill($data);
                $existingItem->save();
                $newQuantity = (float) $existingItem->quantity;

                $this->recordInventoryMovement(
                    $existingItem,
                    abs($newQuantity - $oldQuantity) > 0.00001 ? 'adjusted' : 'edited',
                    $newQuantity - $oldQuantity,
                    $oldQuantity,
                    $newQuantity,
                    'Updated from reviewed inventory import.',
                    $data['date_added'] ?? null
                );

                $updated++;
                continue;
            }

            $item = Item::create($data);
            $this->recordInventoryMovement(
                $item,
                'created',
                (float) $item->quantity,
                0,
                (float) $item->quantity,
                'Created from reviewed inventory import.',
                $data['date_added'] ?? null
            );

            $created++;
        }
    });

    $request->session()->forget('inventory_import_preview');

    ActivityLog::create([
        'user_id' => auth()->id(),
        'user_name' => auth()->user()->name,
        'action' => 'Inventory Import Committed',
        'description' => "Imported inventory rows. Created: {$created}, Updated: {$updated}, Skipped: {$skipped}.",
        'ip_address' => $request->ip(),
        'user_agent' => $request->userAgent(),
    ]);

    $message = "Inventory import complete. Created: {$created}. Updated: {$updated}. Skipped: {$skipped}.";
    if ($errors !== []) {
        $message .= ' ' . implode(' ', array_slice($errors, 0, 3));
    }

    return redirect()->route('admin.inventory')->with('success', $message);
}

public function clearInventoryImportPreview(Request $request)
{
    $request->session()->forget('inventory_import_preview');

    return redirect()->route('admin.inventory')
        ->with('success', 'Inventory import preview cleared.');
}

private function buildInventoryImportPreview(array $analysis): array
{
    $rows = [];
    $normalizer = new InventoryDataNormalizer();

    foreach ((array) ($analysis['rows'] ?? []) as $index => $row) {
        if (!is_array($row)) {
            continue;
        }

        $data = $this->sanitizeInventoryImportRow($row, false);

        $match = $this->findInventoryImportMatch($data);
        $rowIssues = [];
        if ($data['name'] === '') {
            $rowIssues[] = 'Missing item name';
        }

        $dataIssues = $normalizer->getDataIssues($row);
        foreach ($dataIssues as $issueKey => $isPresent) {
            if (!$isPresent) {
                continue;
            }

            if ($issueKey === 'date_unparseable') {
                $rowIssues[] = 'Date format not recognized';
            } elseif ($issueKey === 'missing_stock_number') {
                $rowIssues[] = 'Stock number missing';
            } elseif ($issueKey === 'non_standard_unit') {
                $rowIssues[] = 'Non-standard unit (will be standardized)';
            }
        }

        $rows[] = array_merge($data, [
            'row_key' => $index,
            'action' => $data['name'] === '' ? 'skip' : ($match ? 'update' : 'create'),
            'matched_item_id' => $match ? $match->id : null,
            'matched_item_name' => $match ? $match->name : null,
            'match_status' => $data['name'] === '' ? 'Needs review' : ($match ? 'Existing item' : 'New item'),
            'ready_to_import' => $data['name'] !== '',
            'issues' => $rowIssues,
            'confidence' => (int) ($row['confidence'] ?? 100),
            'notes' => trim((string) ($row['notes'] ?? '')),
        ]);
    }

    return [
        'source_name' => (string) ($analysis['source_name'] ?? 'inventory upload'),
        'source_type' => (string) ($analysis['source_type'] ?? 'upload'),
        'quality' => (array) ($analysis['quality'] ?? []),
        'rows' => $rows,
    ];
}

private function sanitizeInventoryImportRow(array $row, bool $persistMedicineType = true): array
{
    $normalizer = new InventoryDataNormalizer();

    $text = static function ($value, int $maxLength): string {
        $value = trim((string) $value);

        if (function_exists('mb_substr')) {
            return mb_substr($value, 0, $maxLength);
        }

        return substr($value, 0, $maxLength);
    };

    $category = trim((string) ($row['category'] ?? 'Medicine'));
    if (!in_array($category, ['Medicine', 'Supplies', 'Equipment'], true)) {
        $category = 'Medicine';
    }

    $quantity = max(0, (float) ($row['quantity'] ?? 0));
    $consumed = max(0, (float) ($row['consumed'] ?? 0));
    $startingStock = max(0, (float) ($row['starting_stock'] ?? 0));
    if ($startingStock <= 0) {
        $startingStock = $quantity + $consumed;
    }

    $medicineTypeName = $text($row['medicine_type'] ?? '', 255);
    $medicineType = null;
    if ($persistMedicineType && $category === 'Medicine' && $medicineTypeName !== '') {
        $medicineType = MedicineType::firstOrCreate(['name' => $medicineTypeName]);
    }

    $normalizedUnit = $normalizer->normalizeUnit($row['unit'] ?? 'pcs');
    $normalizedDate = $normalizer->normalizeDate($row['date_added'] ?? '');
    $normalizedExpiryDate = $normalizer->normalizeDate($row['expiration_date'] ?? '');

    return [
        'name' => $text($row['name'] ?? '', 255),
        'category' => $category,
        'stock_number' => $normalizer->normalizeStockNumber($row['stock_number'] ?? '') ?: null,
        'medicine_type_id' => $category === 'Medicine' && $medicineType ? $medicineType->id : null,
        'medicine_type' => $category === 'Medicine' ? ($medicineType ? $medicineType->name : ($medicineTypeName ?: null)) : null,
        'quantity' => $quantity,
        'starting_stock' => $startingStock,
        'consumed' => $consumed,
        'minimum_stock' => max(0, (float) ($row['minimum_stock'] ?? 10)),
        'unit' => $text($normalizedUnit, 50),
        'dispensing_unit' => null,
        'units_per_stock_unit' => null,
        'date_added' => $normalizedDate ?: now()->toDateString(),
        'expiration_date' => $category === 'Medicine' ? $normalizedExpiryDate : null,
        'description' => 'Imported from reviewed inventory upload.',
    ];
}

private function findInventoryImportMatch(array $row): ?Item
{
    $stockNumber = trim((string) ($row['stock_number'] ?? ''));
    if ($stockNumber !== '') {
        $match = Item::query()
            ->where('stock_number', $stockNumber)
            ->first();

        if ($match) {
            return $match;
        }
    }

    $name = trim((string) ($row['name'] ?? ''));
    if ($name === '') {
        return null;
    }

    return Item::query()
        ->whereRaw('LOWER(name) = ?', [strtolower($name)])
        ->where('category', $row['category'] ?? 'Medicine')
        ->first();
}

public function deleteItem($id)
{
    $item = Item::find($id);

    if ($item) {
        $itemName = $item->name; 
        $stockBefore = (float) $item->quantity;
        $this->recordInventoryMovement($item, 'deleted', 0, $stockBefore, 0, 'Item deleted from inventory.');

        $item->delete();

        // LOGS CODES
        \App\Models\ActivityLog::create([
            'user_id'     => auth()->id(),
            'user_name'   => auth()->user()->name,
            'action'      => 'Inventory Deleted',
            'description' => "Permanently removed item: $itemName (ID: #$id) from inventory.",
            'ip_address'  => request()->ip(),
            'user_agent'  => request()->userAgent(),
        ]);

        return redirect()->back()->with('success', 'Item removed.');
    }

    return redirect()->back()->with('error', 'Item not found.');
}
    // --- 3. SETTINGS & PROFILE ---
    public function updateSettings(Request $request)
    {
        if ($request->boolean('preferences_form')) {
            $closureRequired = Rule::requiredIf($request->boolean('clinic_closure_enabled'));
            $request->validate([
                'student_assistant_open_time' => ['required', 'date_format:H:i'],
                'student_assistant_close_time' => ['required', 'date_format:H:i'],
                'appointment_reminder_hours' => ['required', 'integer', 'in:0,1,3,24,48'],
                'clinic_closure_starts_at' => [$closureRequired, 'nullable', 'date'],
                'clinic_closure_ends_at' => [$closureRequired, 'nullable', 'date', 'after:clinic_closure_starts_at'],
                'clinic_closure_reason' => ['nullable', 'string', 'max:100'],
                'clinic_closure_message' => ['nullable', 'string', 'max:500'],
            ]);
        }

        $settings = Setting::first();
        if(!$settings) { $settings = new Setting(); }

        $settings->clinic_name = $request->input('clinic_name', $settings->clinic_name ?: 'PUP Taguig Clinic');
        $settings->clinic_location = $request->input('clinic_location', $settings->clinic_location ?: 'Santos Ave, Lower Bicutan, Taguig');
        $settings->open_time = $request->input('open_time', $settings->open_time ?: '08:00');
        $settings->close_time = $request->input('close_time', $settings->close_time ?: '17:00');

        if ($request->boolean('preferences_form')) {
            $settings->admin_live_notifications = $request->boolean('admin_live_notifications');
            $settings->auto_approve = $request->boolean('auto_approve');
            $settings->student_assistant_open_time = $request->input('student_assistant_open_time', '08:00');
            $settings->student_assistant_close_time = $request->input('student_assistant_close_time', '20:00');
            $settings->appointment_reminder_hours = (int) $request->input('appointment_reminder_hours', 24);
            $settings->clinic_closure_enabled = $request->boolean('clinic_closure_enabled');
            $settings->clinic_closure_starts_at = $request->input('clinic_closure_starts_at') ?: null;
            $settings->clinic_closure_ends_at = $request->input('clinic_closure_ends_at') ?: null;
            $settings->clinic_closure_reason = $request->input('clinic_closure_reason') ?: null;
            $settings->clinic_closure_message = $request->input('clinic_closure_message') ?: null;
        }

        $cancelledForClosure = DB::transaction(function () use ($settings, $request) {
            $settings->save();

            if (!$request->boolean('preferences_form') || !$settings->clinic_closure_enabled) {
                return 0;
            }

            return $this->cancelAppointmentsDuringClinicClosure($settings);
        });

        $logParts = [];
        if ($request->filled('clinic_name') || $request->filled('clinic_location') || $request->filled('open_time') || $request->filled('close_time')) {
            $logParts[] = "clinic configuration (Name: {$request->clinic_name}, Hours: {$request->open_time} - {$request->close_time})";
        }
        if ($request->boolean('preferences_form')) {
            $logParts[] = 'system preferences';
            if ($cancelledForClosure > 0) {
                $logParts[] = "cancelled {$cancelledForClosure} appointment(s) within the clinic closure window";
            }
        }
        $logDescription = $logParts !== []
            ? 'Modified ' . implode(', ', $logParts)
            : 'Updated system settings';

        // --- LOGS CODES ---
    \App\Models\ActivityLog::create([
        'user_id'     => auth()->id(),
        'user_name'   => auth()->user()->name,
        'action'      => 'System Settings Updated',
        'description' => $logDescription,
        'ip_address'  => request()->ip(),
        'user_agent'  => request()->userAgent(),
    ]);

        $successMessage = 'System settings saved.';
        if ($cancelledForClosure > 0) {
            $successMessage .= " {$cancelledForClosure} affected appointment(s) were cancelled and students were asked to rebook.";
        }

        return redirect()->back()->with('success', $successMessage);
    }

    private function cancelAppointmentsDuringClinicClosure(Setting $settings): int
    {
        if (!$settings->clinic_closure_starts_at || !$settings->clinic_closure_ends_at) {
            return 0;
        }

        $timezone = config('app.timezone');
        $startsAt = Carbon::parse($settings->clinic_closure_starts_at, $timezone);
        $endsAt = Carbon::parse($settings->clinic_closure_ends_at, $timezone);
        $reason = trim((string) ($settings->clinic_closure_reason ?: 'Temporary Clinic Closure'));
        $closureNote = sprintf(
            '[Clinic Closure] %s. Clinic unavailable from %s until %s. Please book a new appointment after reopening.',
            $reason,
            $startsAt->format('M d, Y g:i A'),
            $endsAt->format('M d, Y g:i A')
        );

        $appointments = Appointment::query()
            ->whereIn('status', ['Pending', 'Approved'])
            ->whereBetween('date', [$startsAt->toDateString(), $endsAt->toDateString()])
            ->lockForUpdate()
            ->get();

        $cancelled = 0;
        foreach ($appointments as $appointment) {
            if (!$appointment->date || !$appointment->time) {
                continue;
            }

            $appointmentAt = Carbon::parse($appointment->date . ' ' . $appointment->time, $timezone);
            if (!$this->appointmentFallsWithinClinicClosure($appointmentAt, $startsAt, $endsAt)) {
                continue;
            }

            $existingNotes = trim((string) $appointment->notes);
            $appointment->status = 'Cancelled';
            $appointment->notes = $existingNotes === ''
                ? $closureNote
                : $existingNotes . PHP_EOL . PHP_EOL . $closureNote;
            $appointment->save();
            $cancelled++;
        }

        return $cancelled;
    }

    private function appointmentFallsWithinClinicClosure(Carbon $appointmentAt, Carbon $startsAt, Carbon $endsAt): bool
    {
        return $appointmentAt->gte($startsAt) && $appointmentAt->lt($endsAt);
    }

    public function updateProfile(Request $request)
{
    $request->validate([
        'first_name' => 'required|string|max:255',
        'last_name' => 'required|string|max:255',
        'email' => ['required', 'email', 'max:255', Rule::unique('users', 'email')->ignore(Auth::id())],
        'middle_name' => 'nullable|string|max:255',
        'suffix_name' => 'nullable|string|max:50',
        'birthday' => 'nullable|date',
        'address' => 'nullable|string|max:255',
        'contact_number' => 'nullable|string|max:30',
        'gender' => 'nullable|string|max:255',
        'civil_status' => 'nullable|string|max:255',
        'emergency_contact_person' => 'nullable|string|max:255',
        'emergency_contact_no' => 'nullable|string|max:255',
        'office' => 'nullable|string|max:255',
        'role' => 'nullable|string|max:255',
        'status' => 'nullable|in:active,inactive',
        'password' => 'nullable|string|min:6|confirmed',
    ]);
    
    /** @var \App\Models\User $user */
    $user = Auth::user();
    $isStudentAssistant = $this->isStudentAssistantAccount($user);
    $originalEmail = (string) $user->email;
    

    $passwordChanged = $request->filled('password') ? ' (Password was also updated)' : '';
    $user->first_name = $request->first_name;
    if (Schema::hasColumn('users', 'middle_name')) {
        $user->middle_name = $request->middle_name;
    }
    $user->last_name = $request->last_name;
    $user->name = trim(implode(' ', array_filter([
        $request->first_name,
        $request->middle_name,
        $request->last_name,
        $request->suffix_name,
    ])));
    $user->email = $request->email;

    if (!$isStudentAssistant && $request->filled('role')) {
        $user->user_role = User::normalizeRole($request->role);
    }

    if (Schema::hasColumn('users', 'status') && $request->filled('status')) {
        $user->status = $request->status;
    }

    if ($request->filled('password')) {
        $user->password = bcrypt($request->password);
    }
    
    $user->save();

    $profileMessageSuffix = '';

    if ($this->isSuperadminAccount($user) && Schema::hasTable('admins')) {
        $linkedAdminProfile = $this->findLinkedAdminProfileByEmails([
            $originalEmail,
            $request->email,
        ]);

        if (!$linkedAdminProfile) {
            $linkedAdminProfile = new Admin();
        }

        if (Admin::hasColumn('first_name')) {
            $linkedAdminProfile->first_name = $request->first_name;
        }

        if (Admin::hasColumn('middle_name')) {
            $linkedAdminProfile->middle_name = $request->middle_name;
        }

        if (Admin::hasColumn('last_name')) {
            $linkedAdminProfile->last_name = $request->last_name;
        }

        if (Admin::hasColumn('suffix_name')) {
            $linkedAdminProfile->suffix_name = $request->suffix_name;
        }

        if (Admin::hasColumn('name')) {
            $linkedAdminProfile->name = $user->name;
        }

        if (Admin::hasColumn('email')) {
            $linkedAdminProfile->email = $request->email;
        }

        if (Admin::hasColumn('email_address')) {
            $linkedAdminProfile->email_address = $request->email;
        }

        if (Admin::hasColumn('birthday')) {
            $linkedAdminProfile->birthday = $request->birthday;
        }

        if (Admin::hasColumn('age')) {
            $linkedAdminProfile->age = $request->filled('birthday')
                ? Carbon::parse($request->birthday)->age
                : null;
        }

        if (Admin::hasColumn('address')) {
            $linkedAdminProfile->address = $request->address;
        }

        if (Admin::hasColumn('gender')) {
            $linkedAdminProfile->gender = $request->gender;
        }

        if (Admin::hasColumn('civil_status')) {
            $linkedAdminProfile->civil_status = $request->civil_status;
        }

        if (Admin::hasColumn('emergency_contact_person')) {
            $linkedAdminProfile->emergency_contact_person = $request->emergency_contact_person;
        }

        if (Admin::hasColumn('emergency_contact_no')) {
            $linkedAdminProfile->emergency_contact_no = $request->emergency_contact_no ?: $request->contact_number;
        } elseif (Admin::hasColumn('contact_no')) {
            $linkedAdminProfile->contact_no = $request->contact_number;
        }

        if (Admin::hasColumn('office')) {
            $linkedAdminProfile->office = $request->office;
        }

        $normalizedRole = User::normalizeRole((string) ($user->user_role ?? ''));
        if (Admin::hasColumn('access_level')) {
            if ($request->filled('role')) {
                $requestRole = User::normalizeRole($request->role);
                $linkedAdminProfile->access_level = match ($requestRole) {
                    User::ROLE_SUPERADMIN => 'superadmin',
                    User::ROLE_ADMIN => in_array(strtolower((string) ($linkedAdminProfile->access_level ?? '')), ['clinic_staff', 'designee'], true)
                        ? strtolower((string) $linkedAdminProfile->access_level)
                        : 'clinic_staff',
                    default => null,
                };
            } else {
                $linkedAdminProfile->access_level = match ($normalizedRole) {
                    User::ROLE_SUPERADMIN => 'superadmin',
                    User::ROLE_ADMIN => in_array(strtolower((string) ($linkedAdminProfile->access_level ?? '')), ['clinic_staff', 'designee'], true)
                        ? strtolower((string) $linkedAdminProfile->access_level)
                        : 'clinic_staff',
                    default => null,
                };
            }
        } elseif (Admin::hasColumn('role')) {
            $linkedAdminProfile->role = $request->role ?: ($normalizedRole === User::ROLE_SUPERADMIN ? 'superadmin' : ($normalizedRole === User::ROLE_ADMIN ? 'clinic_staff' : null));
        }

        if (Admin::hasColumn('status')) {
            $linkedAdminProfile->status = $request->status;
        }

        $linkedAdminProfile->save();
        $profileMessageSuffix = ' CMS admin profile saved locally for the superadmin account.';
    } elseif ($isStudentAssistant) {
        $profileMessageSuffix = ' Student assistant profile sync is pending external API integration, so extra profile fields remain temporary.';
    } else {
        $profileMessageSuffix = ' Extra CMS profile fields are display-only for admin accounts right now and were not saved.';
    }

    // --- LOGS CODES ---
    \App\Models\ActivityLog::create([
        'user_id'     => $user->id,
        'user_name'   => $user->name,
        'action'      => 'Security Update', 
        'description' => "User updated admin profile info: Name/Email{$passwordChanged}. Source email before update: {$originalEmail}.",
        'ip_address'  => $request->ip(),
        'user_agent'  => $request->userAgent(),
    ]);

    return redirect()->back()->with('success', 'Profile updated successfully.' . $profileMessageSuffix);
}

    // --- 4. EXPORTS (CSV) ---
    public function exportReports()
{
    $appointments = Appointment::all();
    $filename = "appointments_" . date('Y-m-d_H-i-s') . ".csv";
    $headers = ["Content-Type" => "text/csv", "Content-Disposition" => "attachment; filename={$filename}"];
    $columns = ['ID','Name','Email','Student ID','Service','Date','Time','Status','Notes'];

    // --- LOGS CODES ---
    \App\Models\ActivityLog::create([
        'user_id'     => auth()->id(),
        'user_name'   => auth()->user()->name,
        'action'      => 'Report Exported',
        'description' => "Downloaded appointment reports as CSV ($filename). Total records: " . $appointments->count(),
        'ip_address'  => request()->ip(),
        'user_agent'  => request()->userAgent(),
    ]);

    $callback = function() use ($appointments, $columns) {
        $file = fopen('php://output', 'w');
        fputcsv($file, $columns);
        foreach ($appointments as $appt) {
            fputcsv($file, [
                $appt->id,
                $appt->name,
                $appt->email,
                $appt->student_id,
                $appt->service,
                $appt->date,
                $appt->time,
                $appt->status,
                $appt->notes
            ]);
        }
        fclose($file);
    };

    return Response::stream($callback, 200, $headers);
}

public function exportInventory()
{
    $monthFilter = request()->query('month', now()->format('Y-m'));
    $monthStart = Carbon::parse($monthFilter . '-01')->startOfMonth();
    $monthEnd = (clone $monthStart)->endOfMonth();

    $consumedByItem = InventoryMovement::query()
        ->select('item_id', DB::raw('SUM(ABS(quantity)) as consumed_total'))
        ->where('type', 'consumed')
        ->whereBetween('created_at', [$monthStart, $monthEnd])
        ->groupBy('item_id')
        ->pluck('consumed_total', 'item_id');

      $items = Item::query()
          ->orderBy('name')
          ->get()
          ->map(function ($item) use ($consumedByItem) {
              $consumedInStockUnit = (float) ($consumedByItem[$item->id] ?? 0);
              $item->unit = $item->unit ?: 'pcs';
              $item->starting_stock = (float) $item->quantity + $consumedInStockUnit;
              $item->consumed = $consumedInStockUnit;
              $item->consumed_display = $item->hasDispensingConversion()
                  ? $consumedInStockUnit * $item->unitsPerStockUnit()
                  : $consumedInStockUnit;
              $item->current_balance = (float) $item->quantity;
              $item->report_category = $this->inventoryReportCategoryLabel($item);
              return $item;
          });

      $filename = "inventory_" . date('Y-m-d_H-i-s') . ".csv";
      $headers = ["Content-Type" => "text/csv", "Content-Disposition" => "attachment; filename={$filename}"];
      $columns = ['Medicine Name','Category','Unit','Starting Stock','Consumed','Current Balance'];

    // --- LOGS CODES ---
    \App\Models\ActivityLog::create([
        'user_id'     => auth()->id(),
        'user_name'   => auth()->user()->name,
        'action'      => 'Inventory Exported',
        'description' => "Exported full inventory list to CSV ($filename). Total items logged: " . $items->count(),
        'ip_address'  => request()->ip(),
        'user_agent'  => request()->userAgent(),
    ]);

    $callback = function() use ($items, $columns) {
        $file = fopen('php://output', 'w');
        fputcsv($file, $columns);
          foreach ($items as $item) {
              fputcsv($file, [
                  $item->name,
                  $item->report_category,
                  $item->unit,
                  $this->formatInventoryQuantity((float) $item->starting_stock),
                  $this->formatInventoryQuantity((float) $item->consumed),
                  $this->formatInventoryQuantity((float) $item->current_balance)
              ]);
          }
        fclose($file);
    };

    return Response::stream($callback, 200, $headers);
}

    // --- 5. COMPLETE APPOINTMENT & DEDUCT INVENTORY ---
    public function completeWithMedicine(Request $request, $id)
{
    $appointment = Appointment::find($id);
    if(!$appointment) return redirect()->back()->with('error', 'Appointment not found.');

    $appointment->status = 'Completed';
    $appointment->save();

    $logDescription = "Completed Appointment #$id for {$appointment->name}.";

      if ($request->filled('item_id')) {
        $item = Item::find($request->item_id);
        if ($item && (float) $item->quantity > 0) {
            $item->decrement('quantity', 1);
            $logDescription .= " Deducted 1 {$item->unit} of {$item->name} from inventory."; 
            
            $this->logActivity('Appointment & Inventory', $logDescription); 
            return redirect()->back()->with('success', "Appointment completed and 1 {$item->unit} of {$item->name} deducted.");
        } 
    }

    $this->logActivity('Appointment Completed', $logDescription);
    return redirect()->back()->with('success', 'Appointment completed (No medicine deducted).');
}

    // 6. FOR INVENTORY SUMMARY
public function inventorySummary()
{
    $monthFilter = request()->query('month', now()->format('Y-m'));
    $monthStart = Carbon::parse($monthFilter . '-01')->startOfMonth();
    $monthEnd = (clone $monthStart)->endOfMonth();

    $consumedByItem = InventoryMovement::query()
        ->select('item_id', DB::raw('SUM(ABS(quantity)) as consumed_total'))
        ->where('type', 'consumed')
        ->whereBetween('created_at', [$monthStart, $monthEnd])
        ->groupBy('item_id')
        ->pluck('consumed_total', 'item_id');

    $itemPerformance = Item::query()
        ->orderBy('name')
        ->get()
        ->map(function ($item) use ($consumedByItem) {
            $consumedInStockUnit = (float) ($consumedByItem[$item->id] ?? 0);
            $item->unit = $item->unit ?: 'pcs';
            $item->consumed = $consumedInStockUnit;
            $item->consumed_display = $item->hasDispensingConversion()
                ? $consumedInStockUnit * $item->unitsPerStockUnit()
                : $consumedInStockUnit;
            $item->current_balance = (float) $item->quantity;
            $item->starting_stock = $item->current_balance + $consumedInStockUnit;
            $item->report_category = $this->inventoryReportCategoryLabel($item);
            return $item;
        });

    $totalItems = $itemPerformance->count();
    $totalStock = $itemPerformance->sum('current_balance');
    $totalConsumed = $itemPerformance->sum('consumed');
    $outOfStock = $itemPerformance->where('current_balance', 0)->count();
    $lowStockItems = $itemPerformance
        ->filter(fn($item) => $item->current_balance > 0 && $item->current_balance <= (float) ($item->minimum_stock ?: 10))
        ->values();
    $lowStockCount = $lowStockItems->count();
    
    $categorySummary = $itemPerformance
        ->groupBy('report_category')
        ->map(function ($items, $category) {
            return (object) [
                'category' => $category,
                'count' => $items->count(),
                'starting_qty' => $items->sum('starting_stock'),
                'consumed_qty' => $items->sum('consumed'),
                'total_qty' => $items->sum('current_balance'),
            ];
        })
        ->values();

    // LOGS CODES
    \App\Models\ActivityLog::create([
        'user_id'     => auth()->id(),
        'user_name'   => auth()->user()->name,
        'action'      => 'Viewed Inventory Report',
        'description' => "Accessed Inventory Summary. System detected $outOfStock out-of-stock items.",
        'ip_address'  => request()->ip(),
        'user_agent'  => request()->userAgent(),
    ]);

    return view('admin.reports.inventory-summary', compact(
        'totalItems', 'totalStock', 'totalConsumed', 'outOfStock', 'lowStockItems', 'lowStockCount', 'categorySummary', 'itemPerformance', 'monthFilter'
    ));
}

// 7. AUDIT TRAIL CONTROLLER
    public function indexLogs(Request $request)
    {
        $currentRole = User::normalizeRole(optional(Auth::user())->user_role ?? '');
        if ($currentRole !== User::ROLE_SUPERADMIN) {
            abort(403, 'Unauthorized');
        }

        $query = ActivityLog::query();

        $search = trim((string) $request->input('q', ''));
        if ($search !== '') {
            $query->where(function ($builder) use ($search) {
                $builder->where('user_name', 'like', "%{$search}%")
                    ->orWhere('action', 'like', "%{$search}%")
                    ->orWhere('description', 'like', "%{$search}%")
                    ->orWhere('module', 'like', "%{$search}%")
                    ->orWhere('route_name', 'like', "%{$search}%")
                    ->orWhere('request_path', 'like', "%{$search}%")
                    ->orWhere('ip_address', 'like', "%{$search}%");
            });
        }

        $actorRole = trim((string) $request->input('actor_role', ''));
        if ($actorRole !== '') {
            $query->where('user_role', strtolower($actorRole));
        }

        $eventType = trim((string) $request->input('event_type', ''));
        if ($eventType !== '') {
            $query->where('event_type', strtolower($eventType));
        }

        $module = trim((string) $request->input('module', ''));
        if ($module !== '') {
            $query->where('module', $module);
        }

        $httpMethod = strtoupper(trim((string) $request->input('http_method', '')));
        if ($httpMethod !== '') {
            $query->where('http_method', $httpMethod);
        }

        $statusClass = trim((string) $request->input('status_class', ''));
        if ($statusClass === 'success') {
            $query->where(function ($builder) {
                $builder->whereNull('status_code')
                    ->orWhere('status_code', '<', 400);
            });
        } elseif ($statusClass === 'error') {
            $query->where('status_code', '>=', 400);
        }

        $dateFrom = trim((string) $request->input('date_from', ''));
        if ($dateFrom !== '') {
            $query->whereDate('created_at', '>=', $dateFrom);
        }

        $dateTo = trim((string) $request->input('date_to', ''));
        if ($dateTo !== '') {
            $query->whereDate('created_at', '<=', $dateTo);
        }

        $perPageInput = (string) $request->input('per_page', '25');
        if (!in_array($perPageInput, ['25', '50', '100', 'all'], true)) {
            $perPageInput = '25';
        }
        $perPage = $perPageInput === 'all'
            ? max(1, (clone $query)->count())
            : (int) $perPageInput;

        $logs = (clone $query)
            ->orderByDesc('created_at')
            ->paginate($perPage)
            ->withQueryString();

        $totalEvents = (clone $query)->count();
        $todayEvents = (clone $query)->whereDate('created_at', Carbon::today())->count();
        $uniqueActors = (clone $query)
            ->whereNotNull('user_id')
            ->distinct('user_id')
            ->count('user_id');
        $failedEvents = (clone $query)->where('status_code', '>=', 400)->count();
        $emergencyEvents = (clone $query)
            ->where(function ($builder) {
                $builder->where('action', 'like', '%Emergency Login%')
                    ->orWhere('description', 'like', '%Emergency login%');
            })
            ->count();

        $roleBreakdown = (clone $query)
            ->selectRaw("COALESCE(NULLIF(user_role, ''), 'unknown') as role, COUNT(*) as total")
            ->groupBy('role')
            ->orderByDesc('total')
            ->get();

        $moduleBreakdown = (clone $query)
            ->selectRaw("COALESCE(NULLIF(module, ''), 'Uncategorized') as module_name, COUNT(*) as total")
            ->groupBy('module_name')
            ->orderByDesc('total')
            ->limit(6)
            ->get();

        $roleOptions = ActivityLog::query()
            ->whereNotNull('user_role')
            ->where('user_role', '!=', '')
            ->distinct()
            ->orderBy('user_role')
            ->pluck('user_role');

        $eventTypeOptions = ActivityLog::query()
            ->whereNotNull('event_type')
            ->where('event_type', '!=', '')
            ->distinct()
            ->orderBy('event_type')
            ->pluck('event_type');

        $moduleOptions = ActivityLog::query()
            ->whereNotNull('module')
            ->where('module', '!=', '')
            ->distinct()
            ->orderBy('module')
            ->pluck('module');

        return view('admin.activity_logs', compact(
            'logs',
            'totalEvents',
            'todayEvents',
            'uniqueActors',
            'failedEvents',
            'emergencyEvents',
            'roleBreakdown',
            'moduleBreakdown',
            'roleOptions',
            'eventTypeOptions',
            'moduleOptions',
            'perPageInput'
        ));
    }

    public function apiHealthMonitor()
    {
        $user = Auth::user();
        abort_unless($user instanceof User && $this->canAccessApiTesting($user), 403);

        $healthStatus = \App\Services\ApiHealthMonitor::checkAllSystems();

        return response()->json($healthStatus);
    }

    public function apiErrorLogs(Request $request)
    {
        $user = Auth::user();
        abort_unless($user instanceof User && $this->canAccessApiTesting($user), 403);

        $hours = $request->query('hours', 24);
        $system = $request->query('system');
        $limit = $request->query('limit', 100);

        $query = \App\Models\ApiErrorLog::query()
            ->where('created_at', '>=', now()->subHours($hours))
            ->orderByDesc('created_at')
            ->limit($limit);

        if ($system) {
            $query->where('system_name', $system);
        }

        $errors = $query->get();
        $stats = \App\Models\ApiErrorLog::getErrorStats($hours);
        $affectedPuptasAccounts = $errors
            ->where('system_name', 'puptas')
            ->map(function ($error) {
                $payload = json_decode((string) $error->request_payload, true);

                return is_array($payload) ? ($payload['user_id'] ?? null) : null;
            })
            ->filter()
            ->unique()
            ->values();

        return response()->json([
            'errors' => $errors,
            'stats' => $stats,
            'puptas_summary' => [
                'failure_count' => $errors->where('system_name', 'puptas')->count(),
                'affected_account_count' => $affectedPuptasAccounts->count(),
                'affected_account_ids' => $affectedPuptasAccounts,
            ],
            'hours' => $hours,
            'system' => $system,
        ]);
    }

    public function apiSystemStatus(Request $request)
    {
        $user = Auth::user();
        abort_unless($user instanceof User && $this->canAccessApiTesting($user), 403);

        $externalAdminSystemKeys = config('services.external_admin_profile.system_keys', []);
        if (is_string($externalAdminSystemKeys)) {
            $externalAdminSystemKeys = json_decode($externalAdminSystemKeys, true) ?: [];
        }
        if (!is_array($externalAdminSystemKeys)) {
            $externalAdminSystemKeys = [];
        }

        $systems = [
            'pupt' => [
                'name' => 'PUPT (Faculty)',
                'configured' => !!config('services.pupt_flss.faculty_profiles_url'),
                'endpoint' => config('services.pupt_flss.faculty_profiles_url'),
                'timeout' => config('services.pupt_flss.timeout'),
                'system_id' => config('services.pupt_flss.system_id'),
            ],
            'guisis' => [
                'name' => 'GuiSIS',
                'configured' => !!config('services.guisis.base_url'),
                'endpoint' => config('services.guisis.base_url'),
                'timeout' => config('services.guisis.timeout'),
                'client_id' => config('services.guisis.client_id'),
            ],
            'puptas' => [
                'name' => 'PUPTAS',
                'configured' => !!config('services.puptas.api_url'),
                'endpoint' => config('services.puptas.api_url'),
                'timeout' => config('services.puptas.timeout'),
                'client_id' => config('services.puptas.client_id'),
            ],
            'one_portal' => [
                'name' => 'One Portal (IdP)',
                'configured' => !!config('services.idp.url'),
                'endpoint' => config('services.idp.url'),
                'auth_method' => config('services.idp.token_auth_method'),
            ],
            'external_admin' => [
                'name' => 'External Admin APIs',
                'configured' => !!config('services.external_admin_profile.system_keys'),
                'systems' => array_keys($externalAdminSystemKeys),
                'header' => config('services.external_admin_profile.header'),
                'system_header' => config('services.external_admin_profile.system_header'),
            ],
        ];

        return response()->json($systems);
    }

    public function integrationTokens()
    {
        $user = Auth::user();
        abort_unless($user instanceof User && $this->canAccessApiTesting($user), 403);

        $integrationClients = IntegrationClient::with('tokens')->get();

        return view('admin.integration-tokens', compact('integrationClients'));
    }

    public function generateIntegrationToken(Request $request)
    {
        $user = Auth::user();
        abort_unless($user instanceof User && $this->canAccessApiTesting($user), 403);

        $allowedAbilities = [
            'external-admin:read',
            'external-admin:update',
            'medical-status:read',
        ];

        $validated = $request->validate([
            'client_id' => 'required|exists:integration_clients,id',
            'token_name' => 'nullable|string|max:100',
            'abilities' => 'nullable|array',
            'abilities.*' => 'string|in:' . implode(',', $allowedAbilities),
        ]);

        try {
            $client = IntegrationClient::findOrFail($validated['client_id']);

            if (!$client->is_active) {
                return response()->json([
                    'success' => false,
                    'message' => 'This integration client is inactive.',
                ], 422);
            }

            $abilities = $validated['abilities'] ?? $allowedAbilities;
            $tokenName = trim((string) ($validated['token_name'] ?? ''));

            if ($tokenName === '') {
                $tokenName = 'web-rotation-' . now()->format('Ymd-His');
            }

            $newToken = $client->createToken($tokenName, $abilities);

            return response()->json([
                'success' => true,
                'message' => 'Token generated successfully',
                'token' => $newToken->plainTextToken,
                'token_id' => $newToken->accessToken->id,
                'abilities' => $abilities,
                'created_date' => optional($newToken->accessToken->created_at)->format('M d, Y'),
                'created_time' => optional($newToken->accessToken->created_at)->format('h:i A'),
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to generate token: ' . $e->getMessage()
            ], 500);
        }
    }

    public function revokeIntegrationToken(Request $request)
    {
        $user = Auth::user();
        abort_unless($user instanceof User && $this->canAccessApiTesting($user), 403);

        $validated = $request->validate([
            'client_id' => 'required|exists:integration_clients,id',
            'token_id' => 'nullable|integer',
        ]);

        try {
            $client = IntegrationClient::findOrFail($validated['client_id']);

            if (!empty($validated['token_id'])) {
                $deleted = $client->tokens()
                    ->whereKey((int) $validated['token_id'])
                    ->delete();

                if ($deleted === 0) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Token not found for this integration client.',
                    ], 404);
                }
            } else {
                $client->tokens()->delete();
            }

            return response()->json([
                'success' => true,
                'message' => 'Token revoked successfully'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to revoke tokens: ' . $e->getMessage()
            ], 500);
        }
    }

    public function createIntegrationClient(Request $request)
    {
        $user = Auth::user();
        abort_unless($user instanceof User && $this->canAccessApiTesting($user), 403);

        $validated = $request->validate([
            'system_key' => 'required|string|unique:integration_clients,system_key|regex:/^[a-z0-9_]+$/',
            'system_name' => 'required|string|max:255'
        ]);

        try {
            $client = IntegrationClient::create([
                'system_key' => strtolower($validated['system_key']),
                'system_name' => $validated['system_name'],
                'is_active' => true
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Integration client created successfully',
                'client' => [
                    'id' => $client->id,
                    'system_key' => $client->system_key,
                    'system_name' => $client->system_name,
                    'is_active' => (bool) $client->is_active,
                ],
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to create client: ' . $e->getMessage()
            ], 500);
        }
    }

    public function integrationTokensDocs()
    {
        $user = Auth::user();
        abort_unless($user instanceof User && $this->canAccessApiTesting($user), 403);

        return view('admin.integration-tokens-docs');
    }

    public function integrationTokensActivity()
    {
        $user = Auth::user();
        abort_unless($user instanceof User && $this->canAccessApiTesting($user), 403);

        $allTokens = \DB::table('personal_access_tokens')
            ->where('tokenable_type', IntegrationClient::class)
            ->join('integration_clients', 'personal_access_tokens.tokenable_id', '=', 'integration_clients.id')
            ->select(
                'personal_access_tokens.*',
                'integration_clients.system_name',
                'integration_clients.system_key'
            )
            ->orderBy('personal_access_tokens.created_at', 'desc')
            ->paginate(50);

        return view('admin.integration-tokens-activity', compact('allTokens'));
    }

}
