<?php

namespace App\Http\Controllers;

use App\Models\Admin;
use App\Models\AdminHub;
use App\Models\Appointment;
use App\Models\HealthProfile;
use App\Models\User;
use App\Services\FacultySyncService;
use App\Services\ModulePermissionService;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Validation\Rule;

class AdminUserController extends Controller
{
    private function redirectToManagementView(Request $request, string $messageType, string $message)
    {
        $managementView = trim((string) $request->input('management_view', $request->query('management_view', '')));

        $route = match ($managementView) {
            'account-access' => 'admin.user-management.account-access',
            'admin-hub' => 'admin.user-management.admin-hub',
            default => 'admin.user-management',
        };

        return redirect()->route($route)->with($messageType, $message);
    }

    private function buildManagementData(Request $request, FacultySyncService $facultySyncService, string $forcedManagementView = ''): array
    {
        $lookupSearch = trim((string) $request->query('lookup_search', ''));
        $managementView = $forcedManagementView !== ''
            ? $forcedManagementView
            : ($request->query('entry') === 'menu'
                ? ''
                : trim((string) $request->query('management_view', '')));

        if (!in_array($managementView, ['account-access', 'admin-hub'], true)) {
            $managementView = '';
        }

        $currentUserId = Auth::id();

        $allLocalUsers = $this->collectLocalUsers('');
        // Account Access only renders local managed users. Avoid blocking that
        // page on the external faculty directory before a lookup is requested.
        $allFacultyUsers = $managementView === 'account-access'
            ? []
            : $this->collectFacultyUsers($facultySyncService, '');

        $localRecords = collect($allLocalUsers)
            ->map(function (array $record) use ($currentUserId) {
                $record['can_edit'] = $this->canManageRecord($record, $currentUserId);

                return $record;
            })
            ->filter(function (array $record) {
                $source = $record['source'] ?? 'student';
                $accessLevel = strtolower(trim((string) ($record['meta']['access_level'] ?? '')));

                if ($source === 'superadmin' || $source === 'student_assistant') {
                    return true;
                }

                if ($source !== 'admin') {
                    return false;
                }

                return in_array($accessLevel, ['clinic_staff', 'clinic staff', 'staff', 'superadmin'], true);
            })
            ->sortBy(fn (array $record) => sprintf(
                '%02d-%s',
                $this->recordSortWeight($record['source'] ?? 'student'),
                strtolower((string) ($record['name'] ?? ''))
            ))
            ->values()
            ->all();

        $facultyDirectory = collect($allFacultyUsers)
            ->filter(fn (array $record) => ($record['source'] ?? '') === 'faculty')
            ->values()
            ->all();

        $adminHubRecords = $managementView === 'account-access'
            ? []
            : $this->collectAdminHubProfiles($lookupSearch, $facultyDirectory);

        $lookupRecords = [];

        if ($lookupSearch !== '') {
            $localLookupRecords = collect($this->collectLocalUsers($lookupSearch));
            $localEmails = $localLookupRecords
                ->pluck('email')
                ->filter()
                ->map(fn ($email) => strtolower(trim((string) $email)));
            $linkedAdminProfileIds = $localLookupRecords
                ->pluck('meta.admin_profile_id')
                ->filter()
                ->map(fn ($id) => (string) $id);

            $standaloneAdminRecords = collect($this->collectStandaloneAdminLookupRecords($lookupSearch))
                ->reject(function (array $record) use ($localEmails, $linkedAdminProfileIds) {
                    $email = strtolower(trim((string) ($record['email'] ?? '')));
                    $adminProfileId = (string) ($record['meta']['admin_profile_id'] ?? '');

                    return ($email !== '' && $localEmails->contains($email))
                        || ($adminProfileId !== '' && $linkedAdminProfileIds->contains($adminProfileId));
                });

            $lookupRecords = $localLookupRecords->merge($standaloneAdminRecords);

            // A local match is enough for Account Access. Only fall back to
            // FLSS when no local user/admin profile matched, and keep that
            // interactive lookup bounded if the remote service is unavailable.
            if ($managementView !== 'account-access' || $lookupRecords->isEmpty()) {
                $lookupRecords = $lookupRecords->merge(
                    $this->collectFacultyUsers(
                        $facultySyncService,
                        $lookupSearch,
                        $managementView === 'account-access' ? 5 : null
                    )
                );
            }

            $lookupRecords = $lookupRecords
                ->map(function (array $record) use ($managementView) {
                    if ($managementView === 'admin-hub' && ($record['source'] ?? '') !== 'faculty') {
                        $record['can_edit'] = true;
                    }

                    return $record;
                })
                ->sortBy(fn (array $record) => sprintf(
                    '%02d-%s',
                    $this->recordSortWeight($record['source'] ?? 'student'),
                    strtolower((string) ($record['name'] ?? ''))
                ))
                ->values()
                ->all();
        }

        $stats = [
            'students' => collect($allLocalUsers)->where('source', 'student')->count(),
            'admins' => collect($allLocalUsers)->whereIn('source', ['admin', 'superadmin', 'student_assistant'])->count(),
            'faculty' => collect($allFacultyUsers)->count(),
            'active' => collect($allLocalUsers)->where('status', 'active')->count(),
            'inactive' => collect($allLocalUsers)->where('status', 'inactive')->count(),
            'local_total' => count($localRecords),
        ];

        return [
            'lookupSearch' => $lookupSearch,
            'managementView' => $managementView,
            'localRecords' => $localRecords,
            'adminHubRecords' => $adminHubRecords,
            'lookupRecords' => $lookupRecords,
            'stats' => $stats,
        ];
    }

    public function index(Request $request, FacultySyncService $facultySyncService)
    {
        return view('admin.user_management', $this->buildManagementData($request, $facultySyncService));
    }

    public function accountAccess(Request $request, FacultySyncService $facultySyncService)
    {
        return view('admin.user_management_account_access', $this->buildManagementData($request, $facultySyncService, 'account-access'));
    }

    public function adminHub(Request $request, FacultySyncService $facultySyncService)
    {
        return view('admin.user_management_admin_hub', $this->buildManagementData($request, $facultySyncService, 'admin-hub'));
    }

    public function update(Request $request, User $user)
    {
        $this->ensureCanManageUsers();
        $managementView = trim((string) $request->input('management_view', 'account-access'));
        $allowedRoles = $managementView === 'admin-hub'
            ? ['admin_designee']
            : ['admin_clinic_staff', 'student_assistant', 'super_admin'];

        $request->validate([
            'management_view' => ['nullable', Rule::in(['account-access', 'admin-hub'])],
            'user_role' => ['required', Rule::in($allowedRoles)],
            'status' => ['required', Rule::in(['active', 'inactive'])],
            'email' => ['required', 'email', 'max:255', Rule::unique('users', 'email')->ignore($user->id)],
            'admin_email' => ['nullable', 'email', 'max:255'],
            'admin_uuid' => ['nullable', 'uuid', 'max:255'],
            'employee_number' => ['nullable', 'string', 'max:255'],
            'access_level' => ['nullable', Rule::in(['clinic_staff', 'designee'])],
            'office' => ['nullable', 'string', 'max:255'],
            'module_permissions' => ['nullable', 'array'],
            'module_permissions.*' => ['string', Rule::in(app(ModulePermissionService::class)->all())],
        ]);

        if ($managementView === 'admin-hub') {
            $linkedAdminHub = $this->findLinkedAdminHubProfile($user) ?? new AdminHub();
            $requestedStatus = strtolower(trim((string) $request->status));
            $adminUuid = trim((string) $request->input('admin_uuid', ''));

            if (Schema::hasColumn('users', 'employee_number') && $request->filled('employee_number')) {
                $user->employee_number = trim((string) $request->input('employee_number'));
                $user->save();
            }

            if ($adminUuid !== '' && AdminHub::hasColumn('admin_uuid')) {
                $uuidConflict = AdminHub::query()
                    ->where('admin_uuid', $adminUuid)
                    ->when(
                        $linkedAdminHub->exists,
                        fn ($query) => $query->where($linkedAdminHub->getKeyName(), '!=', $linkedAdminHub->getKey())
                    )
                    ->exists();

                if ($uuidConflict) {
                    return $this->redirectToManagementView($request, 'error', 'That IDP Admin UUID is already assigned to another Admin Hub profile.');
                }

                $linkedAdminHub->admin_uuid = $adminUuid;
            }

            if (AdminHub::hasColumn('user_id')) {
                $linkedAdminHub->user_id = $user->id;
            }
            if (AdminHub::hasColumn('first_name')) {
                $linkedAdminHub->first_name = $user->first_name;
            }
            if (AdminHub::hasColumn('middle_name')) {
                $linkedAdminHub->middle_name = $user->middle_name;
            }
            if (AdminHub::hasColumn('last_name')) {
                $linkedAdminHub->last_name = $user->last_name;
            }
            if (AdminHub::hasColumn('name')) {
                $linkedAdminHub->name = $user->name;
            }
            if (AdminHub::hasColumn('email')) {
                $linkedAdminHub->email = trim((string) $request->email);
            }
            if (AdminHub::hasColumn('office')) {
                $linkedAdminHub->office = $request->input('office');
            }
            $this->fillAdminHubProfileDetails($linkedAdminHub, $user, $this->findLinkedAdminProfile($user), $request->all());
            if (AdminHub::hasColumn('role')) {
                $linkedAdminHub->role = $request->user_role;
            }
            if (AdminHub::hasColumn('status')) {
                $linkedAdminHub->status = $request->status;
            }
            $linkedAdminHub->save();

            if ($requestedStatus === 'inactive') {
                $this->reconcileUserAfterAdminHubDeactivation($user);
            } else {
                $this->activateUserForAdminHub($user);
            }

            $this->logUserManagementAction(
                $requestedStatus === 'inactive' ? 'Deactivated admin hub account' : 'Added local account to Admin Hub',
                sprintf(
                    $requestedStatus === 'inactive'
                        ? 'Marked %s (%s) inactive in Admin Hub and restored the appropriate clinic or IDP access.'
                        : 'Added %s (%s) to the centralized Admin Hub as %s without changing clinic permissions.',
                    $user->name ?? $user->email,
                    $user->email,
                    $request->user_role
                )
            );

            return $this->redirectToManagementView(
                $request,
                'success',
                $requestedStatus === 'inactive'
                    ? 'Admin Hub access deactivated. Clinic access was preserved when separately assigned.'
                    : 'The profile was added to the Admin Hub. Clinic permissions were not changed.'
            );
        }

        if ($this->isProtectedUser($user)) {
            return redirect()->back()->with('error', 'This account is protected and cannot be modified here.');
        }

        $originalRole = $user->user_role;
        $originalStatus = $user->status ?? 'active';
        $requestedRoleRaw = strtolower(trim((string) $request->user_role));
        $roleAssignment = $this->resolveClinicRoleAssignment($requestedRoleRaw);
        $usesSeparateAdminEmail = $roleAssignment['user_type'] === 'Assistant';
        $normalizedRequestedRole = $roleAssignment['user_role'];

        if (
            Schema::hasColumn('users', 'idp_role')
            && trim((string) $user->idp_role) === ''
            && !in_array(User::normalizeRole($originalRole), [User::ROLE_ADMIN, User::ROLE_SUPERADMIN], true)
        ) {
            $user->idp_role = $this->baseRoleTokenForUser($user);
        }
        $user->user_role = $normalizedRequestedRole;
        if (Schema::hasColumn('users', 'user_type')) {
            $user->user_type = $roleAssignment['user_type'];
        }
        $user->email = trim((string) $request->email);
        $adminLoginEmail = trim((string) $request->admin_email);

        if (Schema::hasColumn('users', 'status')) {
            $user->status = $request->status;
        }
        if (Schema::hasColumn('users', 'module_permissions')) {
            $user->module_permissions = $normalizedRequestedRole === User::ROLE_SUPERADMIN
                ? null
                : app(ModulePermissionService::class)->normalize($request->input('module_permissions', []));
        }
        if (Schema::hasColumn('users', 'employee_number') && $request->filled('employee_number')) {
            $user->employee_number = trim((string) $request->input('employee_number'));
        }

        $user->save();

        $linkedAdmin = $this->findLinkedAdminProfile($user);
        if (in_array($normalizedRequestedRole, [User::ROLE_ADMIN, User::ROLE_SUPERADMIN], true)) {
            if (!$linkedAdmin) {
                $linkedAdmin = new Admin();
            }

            if (Admin::hasColumn('user_id')) {
                $linkedAdmin->user_id = $user->id;
            }

            if (Admin::hasColumn('first_name')) {
                $linkedAdmin->first_name = $user->first_name;
            }
            if (Admin::hasColumn('last_name')) {
                $linkedAdmin->last_name = $user->last_name;
            }
            if (Admin::hasColumn('email')) {
                $linkedAdmin->email = $usesSeparateAdminEmail && $adminLoginEmail !== '' ? $adminLoginEmail : $user->email;
            }
            if (Admin::hasColumn('email_address')) {
                $linkedAdmin->email_address = $usesSeparateAdminEmail && $adminLoginEmail !== '' ? $adminLoginEmail : $user->email;
            }
            if (Admin::hasColumn('name')) {
                $linkedAdmin->name = $user->name;
            }
            if (Admin::hasColumn('access_level')) {
                $linkedAdmin->access_level = $roleAssignment['access_level'];
            }
            if (Admin::hasColumn('status')) {
                $linkedAdmin->status = $request->status;
            }
            if (Admin::hasColumn('office')) {
                $linkedAdmin->office = $request->input('office');
            }
            if (Admin::hasColumn('employee_number')) {
                $linkedAdmin->employee_number = trim((string) (
                    $request->input('employee_number')
                    ?: $user->employee_number
                    ?: $user->employeeHealthProfile?->employee_number
                )) ?: null;
            }

            $linkedAdmin->save();
        } elseif ($linkedAdmin) {
            if (Admin::hasColumn('access_level')) {
                $linkedAdmin->access_level = null;
            }
            if (Admin::hasColumn('email_address')) {
                $linkedAdmin->email_address = null;
            }
            if (Admin::hasColumn('status')) {
                $linkedAdmin->status = $request->status;
            }
            if (Admin::hasColumn('office') && $request->filled('office')) {
                $linkedAdmin->office = $request->input('office');
            }
            $linkedAdmin->save();
        }

        if (strtolower(trim((string) $request->status)) === 'inactive') {
            $this->deactivateUserAccess($user, $linkedAdmin);
        }

        $this->logUserManagementAction(
            'Updated user account',
            sprintf(
                'Updated %s (%s) role from %s to %s and status from %s to %s.',
                $user->name ?? $user->email,
                $user->email,
                $originalRole,
                $user->user_role,
                $originalStatus,
                $user->status ?? 'active'
            )
        );

        return $this->redirectToManagementView($request, 'success', 'User account updated successfully.');
    }

    public function storeFromLookup(Request $request)
    {
        $this->ensureCanManageUsers();
        $managementView = trim((string) $request->input('management_view', 'account-access'));
        $allowedRoles = $managementView === 'admin-hub'
            ? ['admin_designee']
            : ['admin_clinic_staff', 'student_assistant', 'super_admin'];

        $request->validate([
            'lookup_source' => ['required', Rule::in(['faculty', 'admin_profile'])],
            'management_view' => ['nullable', Rule::in(['account-access', 'admin-hub'])],
            'user_role' => ['required', Rule::in($allowedRoles)],
            'status' => ['required', Rule::in(['active', 'inactive'])],
            'email' => ['required', 'email', 'max:255'],
            'admin_email' => ['nullable', 'email', 'max:255'],
            'access_level' => ['nullable', Rule::in(['clinic_staff', 'designee'])],
            'office' => ['nullable', 'string', 'max:255'],
            'module_permissions' => ['nullable', 'array'],
            'module_permissions.*' => ['string', Rule::in(app(ModulePermissionService::class)->all())],
            'first_name' => ['nullable', 'string', 'max:255'],
            'middle_name' => ['nullable', 'string', 'max:255'],
            'last_name' => ['nullable', 'string', 'max:255'],
            'full_name' => ['nullable', 'string', 'max:255'],
            'admin_uuid' => ['nullable', 'uuid', 'max:255'],
            'employee_number' => ['nullable', 'string', 'max:255'],
            'birthday' => ['nullable', 'date'],
            'age' => ['nullable', 'integer', 'min:0', 'max:150'],
            'gender' => ['nullable', 'string', 'max:50'],
            'civil_status' => ['nullable', 'string', 'max:50'],
            'address' => ['nullable', 'string'],
            'emergency_contact_person' => ['nullable', 'string', 'max:255'],
            'emergency_contact_no' => ['nullable', 'string', 'max:50'],
        ]);

        $requestedRoleRaw = strtolower(trim((string) $request->user_role));
        $roleAssignment = $this->resolveClinicRoleAssignment($requestedRoleRaw);
        $normalizedRequestedRole = $roleAssignment['user_role'];
        $usesSeparateAdminEmail = $roleAssignment['user_type'] === 'Assistant';
        $baseEmail = trim((string) $request->email);

        $firstName = trim((string) $request->input('first_name', ''));
        $middleName = trim((string) $request->input('middle_name', ''));
        $lastName = trim((string) $request->input('last_name', ''));
        $fullName = trim((string) $request->input('full_name', ''));
        if ($fullName === '') {
            $fullName = trim(implode(' ', array_filter([$firstName, $middleName, $lastName])));
        }

        if ($managementView === 'admin-hub') {
            $adminEmail = $baseEmail;
            $adminUuid = trim((string) $request->input('admin_uuid', ''));
            $linkedAdminHub = $this->findAdminHubProfileByEmailOrUuid($adminEmail, $adminUuid) ?? new AdminHub();

            if (AdminHub::hasColumn('admin_uuid') && $adminUuid !== '') {
                $linkedAdminHub->admin_uuid = $adminUuid;
            }
            if (AdminHub::hasColumn('first_name')) {
                $linkedAdminHub->first_name = $firstName !== '' ? $firstName : 'Faculty';
            }
            if (AdminHub::hasColumn('middle_name')) {
                $linkedAdminHub->middle_name = $middleName !== '' ? $middleName : null;
            }
            if (AdminHub::hasColumn('last_name')) {
                $linkedAdminHub->last_name = $lastName !== '' ? $lastName : 'User';
            }
            if (AdminHub::hasColumn('name')) {
                $linkedAdminHub->name = $fullName !== '' ? $fullName : trim(($linkedAdminHub->first_name ?? 'Faculty') . ' ' . ($linkedAdminHub->last_name ?? 'User'));
            }
            if (AdminHub::hasColumn('email')) {
                $linkedAdminHub->email = $adminEmail;
            }
            if (AdminHub::hasColumn('office')) {
                $linkedAdminHub->office = $request->input('office');
            }
            $this->fillAdminHubProfileDetails($linkedAdminHub, null, null, $request->all());
            if (AdminHub::hasColumn('role')) {
                $linkedAdminHub->role = $requestedRoleRaw;
            }
            if (AdminHub::hasColumn('status')) {
                $linkedAdminHub->status = $request->status;
            }
            $linkedAdminHub->save();

            $linkedUser = $this->resolveLinkedUserForAdminHubRecord($linkedAdminHub);
            if ($linkedUser) {
                if (AdminHub::hasColumn('user_id') && !$linkedAdminHub->user_id) {
                    $linkedAdminHub->user_id = $linkedUser->id;
                    $linkedAdminHub->save();
                }

                if (strtolower(trim((string) $request->status)) === 'inactive') {
                    $this->reconcileUserAfterAdminHubDeactivation($linkedUser);
                } else {
                    $this->activateUserForAdminHub($linkedUser);
                }
            }

            $this->logUserManagementAction(
                'Added admin hub profile from lookup',
                sprintf(
                    'Added %s (%s) from %s lookup into the centralized Admin Hub as %s.',
                    $linkedAdminHub->name ?? $adminEmail,
                    $adminEmail,
                    $request->lookup_source,
                    $requestedRoleRaw
                )
            );

            return $this->redirectToManagementView($request, 'success', 'Lookup user added to the Admin Hub successfully.');
        }

        $user = User::query()->where('email', $baseEmail)->first();

        if (!$user) {
            $lookupSource = trim((string) $request->input('lookup_source', 'faculty'));
            $studentIdSeed = trim((string) $request->input('employee_number', ''));
            if ($studentIdSeed === '') {
                $studentIdSeed = ($lookupSource === 'admin_profile' ? 'admin-' : 'faculty-') . strtolower(substr(md5($baseEmail), 0, 10));
            }

            $user = new User();
            $user->student_id = $this->resolveUniqueLocalIdentifier($studentIdSeed);
            $user->first_name = $firstName !== '' ? $firstName : ($lookupSource === 'admin_profile' ? 'Admin' : 'Faculty');
            $user->middle_name = $middleName !== '' ? $middleName : null;
            $user->last_name = $lastName !== '' ? $lastName : 'User';
            $user->name = $fullName !== '' ? $fullName : trim($user->first_name . ' ' . $user->last_name);
            $user->email = $baseEmail;
            if (Schema::hasColumn('users', 'employee_number')) {
                $user->employee_number = trim((string) $request->input('employee_number', '')) ?: null;
            }
            $user->password = bcrypt(\Illuminate\Support\Str::random(40));
            if (Schema::hasColumn('users', 'idp_role')) {
                $user->idp_role = $lookupSource === 'admin_profile' ? 'admin' : 'faculty';
            }
        } elseif (
            Schema::hasColumn('users', 'idp_role')
            && trim((string) $user->idp_role) === ''
            && !in_array(User::normalizeRole((string) $user->user_role), [User::ROLE_ADMIN, User::ROLE_SUPERADMIN], true)
        ) {
            $user->idp_role = $this->baseRoleTokenForUser($user);
        }

        if (Schema::hasColumn('users', 'employee_number') && $request->filled('employee_number')) {
            $user->employee_number = trim((string) $request->input('employee_number'));
        }

        $user->user_role = $normalizedRequestedRole;
        if (Schema::hasColumn('users', 'user_type')) {
            $user->user_type = $roleAssignment['user_type'];
        }
        if (Schema::hasColumn('users', 'status')) {
            $user->status = $request->status;
        }
        if (Schema::hasColumn('users', 'module_permissions')) {
            $user->module_permissions = $normalizedRequestedRole === User::ROLE_SUPERADMIN
                ? null
                : app(ModulePermissionService::class)->normalize($request->input('module_permissions', []));
        }
        $user->save();

        $linkedAdmin = $this->findLinkedAdminProfile($user) ?? new Admin();

        if (in_array($normalizedRequestedRole, [User::ROLE_ADMIN, User::ROLE_SUPERADMIN], true)) {
            if (Admin::hasColumn('user_id')) {
                $linkedAdmin->user_id = $user->id;
            }
            if (Admin::hasColumn('first_name')) {
                $linkedAdmin->first_name = $user->first_name;
            }
            if (Admin::hasColumn('middle_name')) {
                $linkedAdmin->middle_name = $user->middle_name;
            }
            if (Admin::hasColumn('last_name')) {
                $linkedAdmin->last_name = $user->last_name;
            }
            if (Admin::hasColumn('name')) {
                $linkedAdmin->name = $user->name;
            }
            if (Admin::hasColumn('email')) {
                $linkedAdmin->email = $usesSeparateAdminEmail && trim((string) $request->input('admin_email', '')) !== ''
                    ? trim((string) $request->input('admin_email', ''))
                    : $user->email;
            }
            if (Admin::hasColumn('email_address')) {
                $linkedAdmin->email_address = $usesSeparateAdminEmail && trim((string) $request->input('admin_email', '')) !== ''
                    ? trim((string) $request->input('admin_email', ''))
                    : $user->email;
            }
            if (Admin::hasColumn('access_level')) {
                $linkedAdmin->access_level = $roleAssignment['access_level'];
            }
            if (Admin::hasColumn('status')) {
                $linkedAdmin->status = $request->status;
            }
            if (Admin::hasColumn('office')) {
                $linkedAdmin->office = $request->input('office');
            }
            if (Admin::hasColumn('employee_number')) {
                $linkedAdmin->employee_number = trim((string) (
                    $request->input('employee_number')
                    ?: $user->employee_number
                )) ?: null;
            }
            $linkedAdmin->save();
        }

        $this->logUserManagementAction(
            'Added user from lookup',
            sprintf(
                'Added %s (%s) from %s lookup into clinic access as %s.',
                $user->name ?? $user->email,
                $user->email,
                $request->lookup_source,
                $user->user_role
            )
        );

        return $this->redirectToManagementView($request, 'success', 'Lookup user added to the clinic system successfully.');
    }

    public function updateAdminHub(Request $request, AdminHub $admin)
    {
        $this->ensureCanManageUsers();

        $request->validate([
            'user_role' => ['required', Rule::in(['admin_designee'])],
            'status' => ['required', Rule::in(['active', 'inactive'])],
            'email' => ['required', 'email', 'max:255'],
            'admin_uuid' => [
                'nullable',
                'uuid',
                'max:255',
                Rule::unique('admin_hub', 'admin_uuid')->ignore($admin->id),
            ],
            'employee_number' => ['nullable', 'string', 'max:255'],
            'birthday' => ['nullable', 'date'],
            'age' => ['nullable', 'integer', 'min:0', 'max:150'],
            'gender' => ['nullable', 'string', 'max:50'],
            'civil_status' => ['nullable', 'string', 'max:50'],
            'address' => ['nullable', 'string'],
            'emergency_contact_person' => ['nullable', 'string', 'max:255'],
            'emergency_contact_no' => ['nullable', 'string', 'max:50'],
            'office' => ['nullable', 'string', 'max:255'],
            'first_name' => ['nullable', 'string', 'max:255'],
            'last_name' => ['nullable', 'string', 'max:255'],
            'full_name' => ['nullable', 'string', 'max:255'],
        ]);

        if (AdminHub::hasColumn('first_name')) {
            $admin->first_name = trim((string) $request->input('first_name', ''));
        }
        if (AdminHub::hasColumn('last_name')) {
            $admin->last_name = trim((string) $request->input('last_name', ''));
        }
        if (AdminHub::hasColumn('name')) {
            $fullName = trim((string) $request->input('full_name', ''));
            $admin->name = $fullName !== '' ? $fullName : trim(implode(' ', array_filter([$admin->first_name, $admin->last_name])));
        }
        if (AdminHub::hasColumn('email')) {
            $admin->email = trim((string) $request->input('email'));
        }
        if (AdminHub::hasColumn('admin_uuid')) {
            $incomingUuid = trim((string) $request->input('admin_uuid', ''));
            if ($incomingUuid !== '') {
                $admin->admin_uuid = $incomingUuid;
            }
        }
        if (AdminHub::hasColumn('status')) {
            $admin->status = $request->status;
        }
        if (AdminHub::hasColumn('office')) {
            $admin->office = $request->input('office');
        }
        $this->fillAdminHubProfileDetails(
            $admin,
            $this->resolveLinkedUserForAdminHubRecord($admin),
            null,
            $request->all()
        );
        if (AdminHub::hasColumn('role')) {
            $admin->role = $request->input('user_role');
        }
        $admin->save();

        $requestedStatus = strtolower(trim((string) $request->status));
        $linkedUser = $this->resolveLinkedUserForAdminHubRecord($admin);
        if ($linkedUser && $requestedStatus === 'inactive') {
            $this->reconcileUserAfterAdminHubDeactivation($linkedUser);
        } elseif ($linkedUser) {
            $this->activateUserForAdminHub($linkedUser);
        }

        $this->logUserManagementAction(
            $requestedStatus === 'inactive' ? 'Deactivated admin hub profile' : 'Updated admin hub profile',
            sprintf(
                $requestedStatus === 'inactive'
                    ? 'Marked admin hub record #%s (%s) inactive and restored the linked account to its remaining access.'
                    : 'Updated admin hub record #%s (%s).',
                $admin->id,
                $admin->name ?? ($admin->email ?? 'Unknown Admin')
            )
        );

        return $this->redirectToManagementView(
            $request,
            'success',
            $requestedStatus === 'inactive'
                ? 'Admin Hub profile deactivated. Separate clinic access was preserved when available.'
                : 'Admin Hub profile updated successfully.'
        );
    }

    public function destroyAdminHub(Request $request, AdminHub $admin)
    {
        $this->ensureCanManageUsers();

        $linkedUser = $this->resolveLinkedUserForAdminHubRecord($admin);

        if (AdminHub::hasColumn('status')) {
            $admin->status = 'inactive';
        }
        $admin->save();

        if ($linkedUser) {
            $this->reconcileUserAfterAdminHubDeactivation($linkedUser);
        }

        $this->logUserManagementAction(
            'Removed admin hub access',
            sprintf(
                'Removed centralized Admin Hub membership for record #%s (%s).',
                $admin->id,
                $admin->name ?? ($admin->email ?? 'Unknown Admin')
            )
        );

        return $this->redirectToManagementView(
            $request,
            'success',
            $linkedUser && !$this->hasClinicAccountAccess($linkedUser, $this->findLinkedAdminProfile($linkedUser))
                ? 'Admin Hub membership removed. The linked account returned to its base role.'
                : 'Admin Hub membership removed. Clinic account access was not changed.'
        );
    }

    public function deleteAdminHubRecord(Request $request, AdminHub $admin)
    {
        $this->ensureCanManageUsers();

        $adminName = $admin->name ?? ($admin->email ?? 'Unknown Admin');
        $adminId = $admin->id;
        $linkedUser = $this->resolveLinkedUserForAdminHubRecord($admin);
        $hasLinkedClinicAccount = $linkedUser && $this->hasClinicAccountAccess($linkedUser, $this->findLinkedAdminProfile($linkedUser));
        $admin->delete();

        $this->logUserManagementAction(
            'Deleted admin hub record',
            sprintf(
                $hasLinkedClinicAccount
                    ? 'Removed record #%s (%s) from the Admin Hub directory while preserving clinic access.'
                    : 'Deleted standalone admin hub record #%s (%s) from the admin_hub table.',
                $adminId,
                $adminName
            )
        );

        return $this->redirectToManagementView(
            $request,
            'success',
            $hasLinkedClinicAccount
                ? 'The directory entry was removed. Clinic account access was preserved.'
                : 'The standalone Admin Hub record was deleted successfully.'
        );
    }

    public function destroy(Request $request, User $user)
    {
        $this->ensureCanManageUsers();

        if ($this->isProtectedUser($user) || $user->id === Auth::id()) {
            return $this->redirectToManagementView($request, 'error', 'This account access cannot be removed.');
        }

        $originalRole = $user->user_role;
        $originalStatus = $user->status ?? 'active';
        $adminProfileId = trim((string) request()->input('admin_profile_id', ''));

        $linkedAdmin = null;
        if ($adminProfileId !== '' && Schema::hasTable('admins')) {
            $linkedAdmin = Admin::query()
                ->when(Admin::hasColumn('admin_id'), fn ($query) => $query->where('admin_id', $adminProfileId))
                ->first();
        }

        if (!$linkedAdmin) {
            $linkedAdmin = $this->findLinkedAdminProfile($user);
        }

        $linkedAdminHub = $this->findLinkedAdminHubProfile($user);
        $isAdminHubMember = $linkedAdminHub
            && strtolower(trim((string) ($linkedAdminHub->status ?? 'active'))) !== 'inactive'
            && in_array(
                strtolower(trim((string) ($linkedAdminHub->role ?? ''))),
                ['admin_designee', 'designee'],
                true
            );

        if ($isAdminHubMember) {
            $user->user_role = User::ROLE_ADMIN;
            if (Schema::hasColumn('users', 'user_type')) {
                $user->user_type = $this->defaultUserTypeForIdpRole(
                    trim((string) ($user->idp_role ?? '')) ?: $this->baseRoleTokenForUser($user)
                );
            }
        } else {
            $this->applyBaseRoleToUser($user);
        }

        if (Schema::hasColumn('users', 'status')) {
            $user->status = 'active';
        }
        $user->save();

        if ($linkedAdmin) {
            if (Admin::hasColumn('access_level')) {
                $linkedAdmin->access_level = null;
            }
            if (Admin::hasColumn('status')) {
                $linkedAdmin->status = 'active';
            }
            if (Admin::hasColumn('email_address') && !$isAdminHubMember) {
                $linkedAdmin->email_address = null;
            }
            $linkedAdmin->save();
        }

        $this->logUserManagementAction(
            'Removed user access',
            sprintf(
                'Removed elevated access for %s (%s) and restored role from %s to %s.',
                $user->name ?? $user->email,
                $user->email,
                $originalRole,
                $user->user_role
            )
        );

        return $this->redirectToManagementView($request, 'success', 'User access removed successfully. The original IDP role has been restored.');
    }

    public function deleteAccount(Request $request, User $user)
    {
        $this->ensureCanManageUsers();

        if ($user->id === Auth::id()) {
            return $this->redirectToManagementView($request, 'error', 'You cannot delete the account you are currently using.');
        }

        $deletedUserName = $user->name ?? $user->email ?? 'Unknown User';
        $deletedUserEmail = (string) ($user->email ?? '');
        $deletedUserId = $user->id;
        $linkedAdminCount = 0;

        try {
            DB::transaction(function () use ($user, &$linkedAdminCount) {
                if (method_exists($user, 'tokens')) {
                    $user->tokens()->delete();
                }

                $linkedAdminProfiles = $this->linkedAdminProfilesForUser($user);
                $linkedAdminCount = $linkedAdminProfiles->count();

                $linkedAdminProfiles->each(function (Admin $adminProfile) {
                    $adminProfile->delete();
                });

                $user->delete();
            });
        } catch (\Throwable $exception) {
            return $this->redirectToManagementView(
                $request,
                'error',
                'The account could not be deleted because related records are protected. Deactivate the account or remove linked records first.'
            );
        }

        $this->logUserManagementAction(
            'Deleted user account',
            sprintf(
                'Deleted user account #%s for %s (%s) and removed %s linked admin profile(s).',
                $deletedUserId,
                $deletedUserName,
                $deletedUserEmail !== '' ? $deletedUserEmail : 'no email',
                $linkedAdminCount
            )
        );

        return $this->redirectToManagementView($request, 'success', 'User account deleted successfully.');
    }

    private function collectLocalUsers(string $search): array
    {
        $query = User::query()->with(['healthProfile', 'adminProfile']);

        if ($search !== '') {
            $query->where(function ($builder) use ($search) {
                foreach ([
                    'student_id',
                    'student_number',
                    'first_name',
                    'middle_name',
                    'last_name',
                    'name',
                    'email',
                    'course',
                    'year',
                    'section',
                ] as $column) {
                    if (Schema::hasColumn('users', $column)) {
                        $builder->orWhere($column, 'like', '%' . $search . '%');
                    }
                }

                if (Schema::hasColumn('users', 'first_name') && Schema::hasColumn('users', 'last_name')) {
                    $userNameParts = ["COALESCE(first_name, '')"];
                    if (Schema::hasColumn('users', 'middle_name')) {
                        $userNameParts[] = "COALESCE(middle_name, '')";
                    }
                    $userNameParts[] = "COALESCE(last_name, '')";
                    $builder->orWhereRaw('CONCAT_WS(" ", ' . implode(', ', $userNameParts) . ') LIKE ?', ['%' . $search . '%']);
                }

                if (Schema::hasTable('admins')) {
                    $builder->orWhereHas('adminProfile', function ($adminQuery) use ($search) {
                        $adminQuery->where(function ($adminSearchQuery) use ($search) {
                            foreach (['admin_id', 'employee_number', 'name', 'first_name', 'middle_name', 'last_name', 'email', 'email_address'] as $column) {
                                if (Admin::hasColumn($column)) {
                                    $adminSearchQuery->orWhere($column, 'like', '%' . $search . '%');
                                }
                            }

                            if (Admin::hasColumn('first_name') && Admin::hasColumn('last_name')) {
                                $adminNameParts = ["COALESCE(first_name, '')"];
                                if (Admin::hasColumn('middle_name')) {
                                    $adminNameParts[] = "COALESCE(middle_name, '')";
                                }
                                $adminNameParts[] = "COALESCE(last_name, '')";
                                $adminSearchQuery->orWhereRaw('CONCAT_WS(" ", ' . implode(', ', $adminNameParts) . ') LIKE ?', ['%' . $search . '%']);
                            }
                        });
                    });

                    $builder->orWhereExists(function ($adminQuery) use ($search) {
                        $adminQuery->select(DB::raw(1))
                            ->from('admins')
                            ->where(function ($linkQuery) {
                                $hasLink = false;

                                if (Admin::hasColumn('user_id')) {
                                    $linkQuery->orWhereColumn('admins.user_id', 'users.id');
                                    $hasLink = true;
                                }

                                if (Admin::hasColumn('email')) {
                                    $linkQuery->orWhereColumn('admins.email', 'users.email');
                                    $hasLink = true;
                                }

                                if (Admin::hasColumn('email_address')) {
                                    $linkQuery->orWhereColumn('admins.email_address', 'users.email');
                                    $hasLink = true;
                                }

                                if (!$hasLink) {
                                    $linkQuery->whereRaw('1 = 0');
                                }
                            })
                            ->where(function ($adminSearchQuery) use ($search) {
                                foreach (['admin_id', 'employee_number', 'name', 'first_name', 'middle_name', 'last_name', 'email', 'email_address', 'access_level'] as $column) {
                                    if (Admin::hasColumn($column)) {
                                        $adminSearchQuery->orWhere('admins.' . $column, 'like', '%' . $search . '%');
                                    }
                                }

                                if (Admin::hasColumn('first_name') && Admin::hasColumn('last_name')) {
                                    $adminNameParts = ["COALESCE(admins.first_name, '')"];
                                    if (Admin::hasColumn('middle_name')) {
                                        $adminNameParts[] = "COALESCE(admins.middle_name, '')";
                                    }
                                    $adminNameParts[] = "COALESCE(admins.last_name, '')";
                                    $adminSearchQuery->orWhereRaw('CONCAT_WS(" ", ' . implode(', ', $adminNameParts) . ') LIKE ?', ['%' . $search . '%']);
                                }
                            });
                    });
                }
            });
        }

        $query->orderBy('first_name');

        if ($search !== '') {
            $query->limit(100);
        }

        return $query->get()
            ->map(function (User $user) {
                $linkedAdmin = $this->findLinkedAdminProfile($user);
                $linkedAdminHub = $this->findLinkedAdminHubProfile($user);
                $resolvedAccessLevel = $this->resolveEffectiveAdminAccessLevel($user, $linkedAdmin);
                $rawRole = strtolower(trim((string) ($user->user_role ?? 'student')));
                $role = User::normalizeRole($rawRole);
                $source = $this->resolveUserSource($user, $linkedAdmin);
                $status = strtolower(trim((string) ($user->status ?? 'active')));
                if ($status === '') {
                    $status = 'active';
                }

                $displayName = trim((string) $user->name);
                if ($displayName === '') {
                    $displayName = trim(implode(' ', array_filter([
                        $user->first_name ?? '',
                        $user->last_name ?? '',
                    ])));
                }

                $studentPhotoProfile = $user->healthProfile;
                $studentPhoto = $studentPhotoProfile?->student_photo;
                $studentNumber = trim((string) ($user->student_number ?? ''));
                $studentId = trim((string) ($user->student_id ?? ''));
                $resolvedIdentifier = $this->resolveUserDisplayIdentifier($user, $linkedAdmin);
                $adminUuid = trim((string) ($linkedAdminHub?->admin_uuid ?? ''));
                if ($adminUuid === '' && $this->isUuid($studentId)) {
                    $adminUuid = $studentId;
                }

                return [
                    'id' => (string) $user->id,
                    'record_id' => (string) $user->id,
                    'source' => $source,
                    'source_label' => $this->resolveUserSourceLabel($user, $linkedAdmin),
                    'name' => $displayName !== '' ? $displayName : ($user->email ?? 'Unknown User'),
                    'first_name' => (string) ($user->first_name ?? ''),
                    'last_name' => (string) ($user->last_name ?? ''),
                    'student_id' => $resolvedIdentifier,
                    'email' => (string) ($user->email ?? ''),
                    'role' => $this->resolveRoleLabel($user, $linkedAdmin),
                    'raw_role' => $rawRole,
                    'normalized_role' => $role,
                    'status' => $status === 'inactive' ? 'inactive' : 'active',
                    'avatar_url' => $studentPhoto && $studentPhotoProfile
                        ? route('walkin.document', [
                            'healthProfile' => $studentPhotoProfile->id,
                            'document' => 'student_photo',
                        ])
                        : null,
                    'avatar_letter' => strtoupper(substr($displayName !== '' ? $displayName : ($user->email ?? 'U'), 0, 1)),
                    'can_edit' => true,
                    'is_external' => false,
                    'delete_admin_hub_url' => $linkedAdminHub
                        ? route('admin.user-management.admin-hub.delete-record', $linkedAdminHub->id)
                        : '',
                    'meta' => [
                        'email' => (string) ($user->email ?? ''),
                        'course' => (string) ($user->course ?? ''),
                        'year' => (string) ($user->year ?? ''),
                        'section' => (string) ($user->section ?? ''),
                        'student_number' => $studentNumber,
                        'DOB' => (string) ($user->DOB ?? ''),
                        'gender' => (string) ($user->gender ?? ''),
                        'contact_no' => (string) ($user->contact_no ?? ''),
                        'address' => (string) ($user->healthProfile?->home_address ?? ''),
                        'is_health_profile_completed' => (bool) ($user->is_health_profile_completed ?? false),
                        'access_level' => $resolvedAccessLevel,
                        'idp_role' => (string) ($user->idp_role ?? ''),
                        'user_type' => (string) ($user->user_type ?? ''),
                        'module_permissions' => Schema::hasColumn('users', 'module_permissions')
                            ? app(ModulePermissionService::class)->assigned($user)
                            : app(ModulePermissionService::class)->defaults(),
                        'admin_login_email' => (string) ($linkedAdmin?->email_address ?? $linkedAdmin?->email ?? ''),
                        'admin_profile_id' => $linkedAdmin?->admin_id,
                        'admin_profile_name' => (string) ($linkedAdmin?->name ?? ''),
                        'admin_hub_profile_id' => $linkedAdminHub?->id,
                        'admin_hub_profile_name' => (string) ($linkedAdminHub?->name ?? ''),
                        'admin_uuid' => $adminUuid,
                        'employee_number' => (string) (
                            $linkedAdminHub?->employee_number
                            ?? $linkedAdmin?->employee_number
                            ?? $user->employee_number
                            ?? ''
                        ),
                        'office' => (string) ($linkedAdmin?->office ?? ''),
                        'updated_at' => optional($user->updated_at)->toIso8601String(),
                    ],
                ];
            })
            ->values()
            ->all();
    }

    private function collectStandaloneAdminLookupRecords(string $search): array
    {
        if ($search === '' || !Schema::hasTable('admins')) {
            return [];
        }

        $query = Admin::query()
            ->where(function ($builder) use ($search) {
                foreach (['admin_id', 'employee_number', 'name', 'first_name', 'middle_name', 'last_name', 'email', 'email_address', 'office', 'status', 'access_level'] as $column) {
                    if (Admin::hasColumn($column)) {
                        $builder->orWhere($column, 'like', '%' . $search . '%');
                    }
                }

                if (Admin::hasColumn('first_name') && Admin::hasColumn('last_name')) {
                    $adminNameParts = ["COALESCE(first_name, '')"];
                    if (Admin::hasColumn('middle_name')) {
                        $adminNameParts[] = "COALESCE(middle_name, '')";
                    }
                    $adminNameParts[] = "COALESCE(last_name, '')";
                    $builder->orWhereRaw('CONCAT_WS(" ", ' . implode(', ', $adminNameParts) . ') LIKE ?', ['%' . $search . '%']);
                }
            })
            ->limit(100);

        return $query->get()
            ->map(function (Admin $admin) {
                $displayName = trim((string) ($admin->name ?? ''));
                if ($displayName === '') {
                    $displayName = trim(implode(' ', array_filter([
                        $admin->first_name ?? '',
                        $admin->last_name ?? '',
                    ])));
                }

                $email = trim((string) ($admin->email_address ?? $admin->email ?? ''));
                $status = strtolower(trim((string) ($admin->status ?? 'active')));
                if (!in_array($status, ['active', 'inactive'], true)) {
                    $status = 'active';
                }

                $adminId = trim((string) ($admin->admin_id ?? ''));
                $employeeNumber = trim((string) ($admin->employee_number ?? ''));
                $identifier = $adminId !== '' ? $adminId : ($employeeNumber !== '' ? $employeeNumber : $email);

                return [
                    'id' => $identifier,
                    'record_id' => $identifier,
                    'source' => 'admin_profile',
                    'source_label' => 'Admin Profile',
                    'name' => $displayName !== '' ? $displayName : ($email !== '' ? $email : 'Admin Profile'),
                    'first_name' => (string) ($admin->first_name ?? ''),
                    'last_name' => (string) ($admin->last_name ?? ''),
                    'student_id' => $identifier,
                    'email' => $email,
                    'role' => 'Admin - Regular',
                    'raw_role' => 'admin',
                    'status' => $status,
                    'avatar_url' => null,
                    'avatar_letter' => strtoupper(substr($displayName !== '' ? $displayName : ($email ?: 'A'), 0, 1)),
                    'can_edit' => false,
                    'can_onboard' => true,
                    'is_external' => false,
                    'meta' => [
                        'email' => $email,
                        'idp_role' => 'admin',
                        'user_type' => 'Regular',
                        'access_level' => trim((string) ($admin->access_level ?? '')),
                        'admin_login_email' => $email,
                        'admin_profile_id' => $adminId,
                        'admin_profile_name' => $displayName,
                        'admin_uuid' => '',
                        'employee_number' => $employeeNumber,
                        'DOB' => (string) ($admin->birthday ?? ''),
                        'birthday' => (string) ($admin->birthday ?? ''),
                        'age' => (string) ($admin->age ?? ''),
                        'gender' => (string) ($admin->gender ?? ''),
                        'civil_status' => (string) ($admin->civil_status ?? ''),
                        'contact_no' => (string) ($admin->emergency_contact_no ?? ''),
                        'address' => (string) ($admin->address ?? ''),
                        'emergency_contact_person' => (string) ($admin->emergency_contact_person ?? ''),
                        'emergency_contact_no' => (string) ($admin->emergency_contact_no ?? ''),
                        'office' => (string) ($admin->office ?? ''),
                        'lookup_source' => 'admin_profile',
                        'updated_at' => optional($admin->updated_at)->toIso8601String(),
                    ],
                ];
            })
            ->values()
            ->all();
    }

    private function collectFacultyUsers(
        FacultySyncService $facultySyncService,
        string $search,
        ?int $timeout = null
    ): array
    {
        try {
            $faculties = $facultySyncService->fetchFaculties($search, $timeout);
        } catch (\Throwable $exception) {
            return [];
        }

        return collect($faculties)
            ->filter(fn ($faculty) => is_array($faculty))
            ->map(function (array $faculty) {
                $profile = is_array($faculty['profile'] ?? null) ? $faculty['profile'] : [];
                $name = trim((string) ($faculty['name'] ?? trim(implode(' ', array_filter([
                    $faculty['first_name'] ?? '',
                    $faculty['middle_name'] ?? '',
                    $faculty['last_name'] ?? '',
                    $faculty['suffix_name'] ?? '',
                ])))));
                $email = trim((string) ($faculty['email'] ?? ''));
                $role = trim((string) ($faculty['faculty_type'] ?? $faculty['role'] ?? $faculty['access_level'] ?? 'Faculty'));
                $status = strtolower(trim((string) ($faculty['status'] ?? 'active')));
                $facultyCode = trim((string) ($faculty['faculty_code'] ?? $faculty['employee_number'] ?? $faculty['employee_no'] ?? ''));
                $facultyNumericId = trim((string) ($faculty['faculty_id'] ?? $faculty['id'] ?? ''));
                $employeeNumber = $facultyCode;
                $adminUuid = $this->firstFilledValue([
                    $faculty['admin_uuid'] ?? null,
                    $faculty['idp_user_id'] ?? null,
                    $faculty['user_uuid'] ?? null,
                    $faculty['uuid'] ?? null,
                    $faculty['student_id'] ?? null,
                    data_get($profile, 'admin_uuid'),
                    data_get($profile, 'idp_user_id'),
                    data_get($profile, 'user_uuid'),
                    data_get($profile, 'uuid'),
                    data_get($profile, 'student_id'),
                    $facultyNumericId,
                ], fn ($value) => $this->isUuid((string) $value));
                $recordId = $employeeNumber !== '' ? $employeeNumber : ($adminUuid ?: ($email !== '' ? $email : 'faculty'));

                if (in_array($status, ['1', 'true', 'active', 'enabled'], true)) {
                    $status = 'active';
                } elseif (in_array($status, ['0', 'false', 'inactive', 'disabled'], true)) {
                    $status = 'inactive';
                } else {
                    $status = $status !== '' ? $status : 'active';
                }

                return [
                    'id' => $recordId,
                    'record_id' => $recordId,
                    'source' => 'faculty',
                    'source_label' => 'Faculty',
                    'name' => $name !== '' ? $name : ($email !== '' ? $email : 'Faculty'),
                    'first_name' => (string) ($faculty['first_name'] ?? ''),
                    'last_name' => (string) ($faculty['last_name'] ?? ''),
                    'student_id' => $employeeNumber,
                    'email' => $email,
                    'role' => $role !== '' ? $role : 'Faculty',
                    'raw_role' => $role,
                    'status' => $status,
                    'avatar_url' => null,
                    'avatar_letter' => strtoupper(substr($name !== '' ? $name : ($email ?: 'F'), 0, 1)),
                    'can_edit' => false,
                    'can_onboard' => true,
                    'is_external' => true,
                    'meta' => [
                        'faculty_id' => $faculty['faculty_id'] ?? null,
                        'faculty_code' => $employeeNumber !== '' ? $employeeNumber : null,
                        'employee_number' => $employeeNumber !== '' ? $employeeNumber : null,
                        'admin_uuid' => $adminUuid,
                        'faculty_type' => $faculty['faculty_type'] ?? null,
                        'department' => $faculty['department'] ?? null,
                        'birthday' => $this->firstFilledValue([$faculty['birthday'] ?? null, $faculty['date_of_birth'] ?? null, data_get($profile, 'birthday'), data_get($profile, 'date_of_birth')]),
                        'age' => $this->firstFilledValue([$faculty['age'] ?? null, data_get($profile, 'age')]),
                        'gender' => $this->firstFilledValue([$faculty['gender'] ?? null, $faculty['sex'] ?? null, data_get($profile, 'gender'), data_get($profile, 'sex')]),
                        'civil_status' => $this->firstFilledValue([$faculty['civil_status'] ?? null, data_get($profile, 'civil_status')]),
                        'address' => $this->firstFilledValue([$faculty['address'] ?? null, $faculty['home_address'] ?? null, data_get($profile, 'address'), data_get($profile, 'home_address')]),
                        'emergency_contact_person' => $this->firstFilledValue([$faculty['emergency_contact_person'] ?? null, data_get($profile, 'emergency_contact_person')]),
                        'emergency_contact_no' => $this->firstFilledValue([$faculty['emergency_contact_no'] ?? null, data_get($profile, 'emergency_contact_no')]),
                        'access_level' => 'designee',
                        'profile' => $profile,
                        'lookup_source' => 'faculty',
                        'updated_at' => $faculty['last_updated'] ?? null,
                    ],
                ];
            })
            ->values()
            ->all();
    }

    private function collectAdminHubProfiles(string $search = '', array $facultyDirectory = []): array
    {
        if (!Schema::hasTable('admin_hub')) {
            return [];
        }

        $facultyByEmail = collect($facultyDirectory)
            ->filter(fn (array $record) => trim(strtolower((string) ($record['email'] ?? ''))) !== '')
            ->keyBy(fn (array $record) => trim(strtolower((string) ($record['email'] ?? ''))))
            ->all();

        $facultyByName = collect($facultyDirectory)
            ->filter(fn (array $record) => trim(strtolower((string) ($record['name'] ?? ''))) !== '')
            ->keyBy(fn (array $record) => trim(strtolower((string) ($record['name'] ?? ''))))
            ->all();
        $facultyByIdentifier = [];
        foreach ($facultyDirectory as $facultyRecord) {
            foreach ([
                $facultyRecord['student_id'] ?? '',
                data_get($facultyRecord, 'meta.faculty_id'),
                data_get($facultyRecord, 'meta.faculty_code'),
            ] as $candidateIdentifier) {
                $candidateIdentifier = trim(strtolower((string) $candidateIdentifier));
                if ($candidateIdentifier !== '') {
                    $facultyByIdentifier[$candidateIdentifier] = $facultyRecord;
                }
            }
        }

        $query = AdminHub::query();

        if ($search !== '') {
            $query->where(function ($builder) use ($search) {
                foreach (['admin_uuid', 'employee_number', 'name', 'first_name', 'last_name', 'email', 'office', 'status'] as $column) {
                    if (AdminHub::hasColumn($column)) {
                        $builder->orWhere($column, 'like', '%' . $search . '%');
                    }
                }
            });
        }

        return $query->orderBy('name')
            ->limit(100)
            ->get()
            ->map(function (AdminHub $admin) use ($facultyByEmail, $facultyByName, $facultyByIdentifier) {
                $linkedUser = AdminHub::hasColumn('user_id') && $admin->user_id ? User::find($admin->user_id) : null;
                $adminUuid = trim((string) ($admin->admin_uuid ?? ''));
                $displayName = trim((string) ($admin->name ?? ''));
                if ($displayName === '') {
                    $displayName = trim(implode(' ', array_filter([
                        $admin->first_name ?? '',
                        $admin->last_name ?? '',
                    ])));
                }
                $email = trim((string) ($admin->email ?? ''));
                $normalizedEmail = trim(strtolower($email));
                $normalizedName = trim(strtolower($displayName));
                $matchedFaculty = $normalizedEmail !== '' && isset($facultyByEmail[$normalizedEmail])
                    ? $facultyByEmail[$normalizedEmail]
                    : ($normalizedName !== '' && isset($facultyByName[$normalizedName])
                        ? $facultyByName[$normalizedName]
                        : null);
                $employeeNumber = trim((string) ($admin->employee_number ?? ''));
                if (!$matchedFaculty && $employeeNumber !== '') {
                    $matchedFaculty = $facultyByIdentifier[strtolower($employeeNumber)] ?? null;
                }
                $facultyIdentifier = trim((string) (
                    data_get($matchedFaculty, 'meta.faculty_code')
                    ?: ($matchedFaculty['student_id'] ?? '')
                ));
                $status = strtolower(trim((string) ($admin->status ?? 'active')));
                $hubRole = strtolower(trim((string) ($admin->role ?? 'admin_designee')));
                if (!in_array($hubRole, ['admin_designee', 'designee'], true)) {
                    $hubRole = 'admin_designee';
                }
                if ($status === '') {
                    $status = 'active';
                }

                $resolvedIdentifier = $employeeNumber !== ''
                    ? $employeeNumber
                    : ($facultyIdentifier !== ''
                    ? $facultyIdentifier
                    : ($linkedUser
                        ? $this->resolveDisplayIdentifier(
                            trim((string) ($linkedUser->student_number ?? '')),
                            trim((string) ($linkedUser->student_id ?? ''))
                        )
                        : ''));

                return [
                    'id' => (string) $admin->id,
                    'record_id' => (string) $admin->id,
                    'source' => 'admin',
                    'source_label' => 'Admin Hub',
                    'name' => $displayName !== '' ? $displayName : ($email !== '' ? $email : 'Admin Hub Record'),
                    'first_name' => (string) ($admin->first_name ?? ''),
                    'last_name' => (string) ($admin->last_name ?? ''),
                    'student_id' => $resolvedIdentifier,
                    'email' => $email,
                    'role' => 'Admin - Designee',
                    'raw_role' => $hubRole,
                    'normalized_role' => $linkedUser
                        ? User::normalizeRole((string) $linkedUser->user_role)
                        : User::ROLE_ADMIN,
                    'status' => $status === 'inactive' ? 'inactive' : 'active',
                    'avatar_url' => null,
                    'avatar_letter' => strtoupper(substr($displayName !== '' ? $displayName : ($email ?: 'A'), 0, 1)),
                    'can_edit' => true,
                    'is_external' => false,
                    'update_url' => route('admin.user-management.admin-hub.update', $admin->id),
                    'delete_url' => route('admin.user-management.admin-hub.destroy', $admin->id),
                    'delete_admin_hub_url' => route('admin.user-management.admin-hub.delete-record', $admin->id),
                    'meta' => [
                        'email' => $email,
                        'access_level' => 'designee',
                        'hub_role' => $hubRole,
                        'admin_login_email' => $email,
                        'admin_profile_id' => $admin->id,
                        'admin_profile_name' => $displayName,
                        'admin_hub_profile_id' => $admin->id,
                        'admin_hub_profile_name' => $displayName,
                        'admin_uuid' => $adminUuid,
                        'employee_number' => $employeeNumber,
                        'faculty_identifier' => $facultyIdentifier,
                        'DOB' => (string) ($admin->birthday ?? $linkedUser?->DOB ?? ''),
                        'birthday' => (string) ($admin->birthday ?? $linkedUser?->DOB ?? ''),
                        'age' => (string) ($admin->age ?? ''),
                        'gender' => (string) ($admin->gender ?? $linkedUser?->gender ?? ''),
                        'civil_status' => (string) ($admin->civil_status ?? ''),
                        'contact_no' => (string) ($admin->emergency_contact_no ?? $linkedUser?->contact_no ?? ''),
                        'address' => (string) ($admin->address ?? $linkedUser?->employeeHealthProfile?->home_address ?? ''),
                        'emergency_contact_person' => (string) ($admin->emergency_contact_person ?? ''),
                        'emergency_contact_no' => (string) ($admin->emergency_contact_no ?? ''),
                        'office' => (string) ($admin->office ?? ''),
                        'lookup_source' => 'admin-hub',
                        'updated_at' => optional($admin->updated_at)->toIso8601String(),
                        'linked_user_id' => $admin->user_id ?? null,
                    ],
                ];
            })
            ->values()
            ->all();
    }

    private function resolveUserSource(User $user, ?Admin $linkedAdmin = null): string
    {
        $rawRole = strtolower(trim((string) ($user->user_role ?? 'student')));
        $normalizedRole = User::normalizeRole($rawRole);
        $userType = strtolower(trim((string) ($user->user_type ?? '')));
        $idpRole = strtolower(trim((string) ($user->idp_role ?? '')));
        $accessLevel = $normalizedRole === User::ROLE_ADMIN
            ? $this->resolveEffectiveAdminAccessLevel($user, $linkedAdmin)
            : '';

        if ($normalizedRole === User::ROLE_SUPERADMIN || $accessLevel === 'superadmin') {
            return 'superadmin';
        }

        if (
            in_array($rawRole, ['student_assistant', 'studentassistant', 'assistant'], true)
            || (
                $normalizedRole === User::ROLE_ADMIN
                && in_array($userType, ['assistant', 'student assistant', 'student_assistant'], true)
            )
        ) {
            return 'student_assistant';
        }

        if ($normalizedRole === User::ROLE_STUDENT && ($idpRole === 'faculty' || $userType === 'faculty')) {
            return 'faculty';
        }

        return match ($normalizedRole) {
            User::ROLE_SUPERADMIN => 'superadmin',
            User::ROLE_ADMIN => 'admin',
            default => 'student',
        };
    }

    private function resolveUserSourceLabel(User $user, ?Admin $linkedAdmin = null): string
    {
        return match ($this->resolveUserSource($user, $linkedAdmin)) {
            'superadmin' => 'Super Admin',
            'admin' => 'Admin',
            'student_assistant' => 'Student Assistant',
            'faculty' => 'Faculty',
            default => 'Student',
        };
    }

    private function resolveRoleLabel(User $user, ?Admin $linkedAdmin = null): string
    {
        $rawRole = strtolower(trim((string) ($user->user_role ?? 'student')));
        $normalizedRole = User::normalizeRole($rawRole);
        $userType = strtolower(trim((string) ($user->user_type ?? '')));
        $idpRole = strtolower(trim((string) ($user->idp_role ?? '')));

        if ($normalizedRole === User::ROLE_SUPERADMIN) {
            return 'Super Admin';
        }

        if (
            in_array($rawRole, ['student_assistant', 'studentassistant', 'assistant'], true)
            || (
                $normalizedRole === User::ROLE_ADMIN
                && in_array($userType, ['assistant', 'student assistant', 'student_assistant'], true)
            )
        ) {
            return 'Admin - Student Assistant';
        }

        if ($normalizedRole === User::ROLE_ADMIN) {
            $accessLevel = $this->resolveEffectiveAdminAccessLevel($user, $linkedAdmin);

            return $this->adminRoleLabelForAccessLevel($accessLevel);
        }

        if ($normalizedRole === User::ROLE_STUDENT && ($idpRole === 'faculty' || $userType === 'faculty')) {
            return 'Faculty';
        }

        return match ($normalizedRole) {
            User::ROLE_SUPERADMIN => 'Super Admin',
            User::ROLE_ADMIN => 'Admin - Regular',
            default => 'Student',
        };
    }

    private function adminRoleLabelForAccessLevel(string $accessLevel): string
    {
        return match (strtolower(trim($accessLevel))) {
            'superadmin' => 'Super Admin',
            'designee' => 'Admin - Designee',
            'clinic_staff', 'clinic staff', 'staff' => 'Admin - Clinic Staff',
            default => 'Admin - Regular',
        };
    }

    private function linkedAdminProfilesForUser(User $user)
    {
        if (!Schema::hasTable('admins')) {
            return collect();
        }

        $query = Admin::query()->where(function ($builder) use ($user) {
            $matched = false;

            if (Admin::hasColumn('user_id')) {
                $builder->orWhere('user_id', $user->id);
                $matched = true;
            }

            $email = trim((string) ($user->email ?? ''));
            if ($email !== '') {
                if (Admin::hasColumn('email')) {
                    $builder->orWhere('email', $email);
                    $matched = true;
                }

                if (Admin::hasColumn('email_address')) {
                    $builder->orWhere('email_address', $email);
                    $matched = true;
                }
            }

            if (!$matched) {
                $builder->whereRaw('1 = 0');
            }
        });

        return $query->get()->unique(fn (Admin $admin) => (string) ($admin->admin_id ?? spl_object_id($admin)))->values();
    }

    private function resolveEffectiveAdminProfile(User $user, ?Admin $linkedAdmin = null): ?Admin
    {
        $profiles = $this->linkedAdminProfilesForUser($user);

        if ($linkedAdmin) {
            $profiles = $profiles->prepend($linkedAdmin)
                ->unique(fn (Admin $admin) => (string) ($admin->admin_id ?? spl_object_id($admin)))
                ->values();
        }

        if ($profiles->isEmpty()) {
            return null;
        }

        $scoreProfile = function (Admin $admin): int {
            $accessLevel = strtolower(trim((string) ($admin->access_level ?? '')));

            return match ($accessLevel) {
                'superadmin' => 40,
                'clinic_staff', 'clinic staff', 'staff' => 30,
                'designee' => 5,
                default => 10,
            };
        };

        return $profiles->sortByDesc($scoreProfile)->first();
    }

    private function resolveEffectiveAdminAccessLevel(User $user, ?Admin $linkedAdmin = null): string
    {
        $profile = $this->resolveEffectiveAdminProfile($user, $linkedAdmin);
        $accessLevel = strtolower(trim((string) ($profile?->access_level ?? '')));

        if ($accessLevel === 'designee') {
            return '';
        }

        return $accessLevel;
    }

    private function resolveDisplayIdentifier(string $studentNumber, string $studentId): string
    {
        if ($studentNumber !== '') {
            return $studentNumber;
        }

        return $studentId !== '' && !$this->isUuid($studentId) ? $studentId : '';
    }

    private function isUuid(string $value): bool
    {
        return preg_match(
            '/^[0-9a-f]{8}-[0-9a-f]{4}-[1-5][0-9a-f]{3}-[89ab][0-9a-f]{3}-[0-9a-f]{12}$/i',
            trim($value)
        ) === 1;
    }

    private function resolveUserDisplayIdentifier(User $user, ?Admin $linkedAdmin): string
    {
        $idpRole = strtolower(trim((string) ($user->idp_role ?? '')));
        $userType = strtolower(trim((string) ($user->user_type ?? '')));
        $studentNumber = trim((string) ($user->student_number ?? ''));
        $studentId = trim((string) ($user->student_id ?? ''));

        if (in_array($userType, ['assistant', 'student assistant', 'student_assistant'], true)) {
            return $studentNumber;
        }

        if ($idpRole === 'faculty') {
            $facultyCode = trim((string) ($linkedAdmin?->employee_number ?? ''));

            return $this->resolveDisplayIdentifier('', $facultyCode !== '' ? $facultyCode : $studentId);
        }

        if ($idpRole === 'admin') {
            return trim((string) ($linkedAdmin?->admin_id ?? ''));
        }

        if (in_array($idpRole, ['student', 'applicant', 'guest'], true)) {
            return $studentNumber;
        }

        return $this->resolveDisplayIdentifier($studentNumber, $studentId);
    }

    private function findLinkedAdminProfile(User $user): ?Admin
    {
        $linkedAdmin = $this->resolveEffectiveAdminProfile($user);

        if ($linkedAdmin && Admin::hasColumn('user_id') && !$linkedAdmin->user_id) {
            $linkedAdmin->user_id = $user->id;
            $linkedAdmin->save();
        }

        return $linkedAdmin;
    }

    private function findLinkedAdminHubProfile(User $user): ?AdminHub
    {
        if (!Schema::hasTable('admin_hub')) {
            return null;
        }

        if (AdminHub::hasColumn('user_id')) {
            $linkedByUserId = AdminHub::query()
                ->where('user_id', $user->id)
                ->first();

            if ($linkedByUserId) {
                return $linkedByUserId;
            }
        }

        return $this->findAdminHubProfileByEmailOrUuid((string) ($user->email ?? ''), '');
    }

    private function findAdminHubProfileByEmailOrUuid(string $email, string $adminUuid): ?AdminHub
    {
        if (!Schema::hasTable('admin_hub')) {
            return null;
        }

        $email = trim($email);
        $adminUuid = trim($adminUuid);

        if ($adminUuid !== '' && AdminHub::hasColumn('admin_uuid')) {
            $adminHub = AdminHub::query()->where('admin_uuid', $adminUuid)->first();
            if ($adminHub) {
                return $adminHub;
            }
        }

        if ($email !== '' && AdminHub::hasColumn('email')) {
            return AdminHub::query()->where('email', $email)->first();
        }

        return null;
    }

    private function fillAdminHubProfileDetails(
        AdminHub $adminHub,
        ?User $user = null,
        ?Admin $admin = null,
        array $input = []
    ): void {
        $employeeProfile = $user?->employeeHealthProfile;
        $birthday = $this->firstFilledValue([
            $input['birthday'] ?? null,
            $input['DOB'] ?? null,
            $adminHub->birthday ?? null,
            $admin?->birthday,
            $user?->DOB,
            $employeeProfile?->birthday,
        ]);
        $age = $this->firstFilledValue([
            $input['age'] ?? null,
            $adminHub->age ?? null,
            $admin?->age,
            $employeeProfile?->age,
        ]);

        if (($age === null || $age === '') && $birthday) {
            try {
                $age = Carbon::parse($birthday)->age;
            } catch (\Throwable) {
                $age = null;
            }
        }

        $values = [
            'employee_number' => $this->firstFilledValue([
                $input['employee_number'] ?? null,
                $adminHub->employee_number ?? null,
                $admin?->employee_number,
                $user?->employee_number,
                $employeeProfile?->employee_number,
            ]),
            'birthday' => $birthday,
            'age' => $age,
            'gender' => $this->firstFilledValue([
                $input['gender'] ?? null,
                $adminHub->gender ?? null,
                $admin?->gender,
                $user?->gender,
                $employeeProfile?->sex,
            ]),
            'civil_status' => $this->firstFilledValue([
                $input['civil_status'] ?? null,
                $adminHub->civil_status ?? null,
                $admin?->civil_status,
                $employeeProfile?->civil_status,
            ]),
            'address' => $this->firstFilledValue([
                $input['address'] ?? null,
                $adminHub->address ?? null,
                $admin?->address,
                $employeeProfile?->home_address,
            ]),
            'emergency_contact_person' => $this->firstFilledValue([
                $input['emergency_contact_person'] ?? null,
                $adminHub->emergency_contact_person ?? null,
                $admin?->emergency_contact_person,
                $employeeProfile?->emergency_contact_person,
            ]),
            'emergency_contact_no' => $this->firstFilledValue([
                $input['emergency_contact_no'] ?? null,
                $adminHub->emergency_contact_no ?? null,
                $admin?->emergency_contact_no,
                $employeeProfile?->emergency_contact_no,
            ]),
            'access_level' => 'designee',
        ];

        foreach ($values as $column => $value) {
            if (AdminHub::hasColumn($column)) {
                $adminHub->setAttribute($column, $value !== '' ? $value : null);
            }
        }
    }

    private function firstFilledValue(array $values, ?callable $accept = null)
    {
        foreach ($values as $value) {
            if ($value === null || (is_string($value) && trim($value) === '')) {
                continue;
            }

            if ($accept === null || $accept($value)) {
                return is_string($value) ? trim($value) : $value;
            }
        }

        return null;
    }

    private function recordSortWeight(string $source): int
    {
        return match ($source) {
            'superadmin' => 0,
            'admin' => 1,
            'admin_profile' => 1,
            'student_assistant' => 2,
            'student' => 3,
            'faculty' => 4,
            default => 9,
        };
    }

    private function isProtectedUser(User $user): bool
    {
        $currentUserId = Auth::id();

        return $user->id === $currentUserId;
    }

    private function canManageRecord(array $record, ?int $currentUserId = null): bool
    {
        $recordId = (string) ($record['id'] ?? $record['record_id'] ?? '');

        if ($recordId !== '' && $currentUserId !== null && $recordId === (string) $currentUserId) {
            return false;
        }

        return true;
    }

    private function ensureCanManageUsers(): void
    {
        $current = Auth::user();
        abort_unless($current && User::normalizeRole((string) ($current->user_role ?? '')) === User::ROLE_SUPERADMIN, 403);
    }

    private function resolveClinicRoleAssignment(string $requestedRole): array
    {
        return match (strtolower(trim($requestedRole))) {
            'student_assistant' => [
                'user_role' => User::ROLE_ADMIN,
                'user_type' => 'Assistant',
                'access_level' => 'clinic_staff',
            ],
            'admin_designee' => [
                'user_role' => User::ROLE_ADMIN,
                'user_type' => 'Regular',
                'access_level' => 'designee',
            ],
            'super_admin', 'superadmin' => [
                'user_role' => User::ROLE_SUPERADMIN,
                'user_type' => 'Regular',
                'access_level' => 'superadmin',
            ],
            default => [
                'user_role' => User::ROLE_ADMIN,
                'user_type' => 'Regular',
                'access_level' => 'clinic_staff',
            ],
        };
    }

    private function defaultUserTypeForIdpRole(string $idpRole): string
    {
        return match (strtolower(trim($idpRole))) {
            'faculty' => 'Faculty',
            'guest' => 'Guest',
            'student' => 'Student',
            'student_assistant', 'studentassistant', 'assistant' => 'Assistant',
            default => 'Regular',
        };
    }

    private function baseRoleTokenForUser(User $user): string
    {
        $userType = strtolower(trim((string) ($user->user_type ?? '')));
        if ($userType === 'faculty') {
            return 'faculty';
        }
        if ($userType === 'guest') {
            return 'guest';
        }

        $normalizedRole = User::normalizeRole((string) ($user->user_role ?? ''));
        if ($normalizedRole === User::ROLE_STUDENT) {
            return User::ROLE_STUDENT;
        }

        return trim((string) ($user->idp_role ?? '')) ?: User::ROLE_STUDENT;
    }

    private function applyBaseRoleToUser(User $user): void
    {
        $restoredRole = trim((string) ($user->idp_role ?? ''));
        if ($restoredRole === '') {
            $restoredRole = $this->baseRoleTokenForUser($user);
        }

        $normalizedRestoredRole = strtolower(trim($restoredRole));
        if (in_array($normalizedRestoredRole, ['student', 'faculty', 'guest'], true)) {
            $user->user_role = User::ROLE_STUDENT;
        } else {
            $user->user_role = User::normalizeRole($restoredRole);
        }

        if (Schema::hasColumn('users', 'user_type')) {
            $user->user_type = $this->defaultUserTypeForIdpRole($restoredRole);
        }
    }

    private function resolveLinkedUserForAdminRecord(Admin $admin): ?User
    {
        if (Admin::hasColumn('user_id') && !empty($admin->user_id)) {
            $linkedUser = User::find($admin->user_id);
            if ($linkedUser) {
                return $linkedUser;
            }
        }

        $emails = array_filter([
            trim((string) ($admin->email ?? '')),
            trim((string) ($admin->email_address ?? '')),
        ]);

        foreach ($emails as $email) {
            $linkedUser = User::query()->where('email', $email)->first();
            if ($linkedUser) {
                return $linkedUser;
            }
        }

        return null;
    }

    private function resolveLinkedUserForAdminHubRecord(AdminHub $admin): ?User
    {
        if (AdminHub::hasColumn('user_id') && !empty($admin->user_id)) {
            $linkedUser = User::find($admin->user_id);
            if ($linkedUser) {
                return $linkedUser;
            }
        }

        $adminUuid = trim((string) ($admin->admin_uuid ?? ''));
        if ($adminUuid !== '') {
            $linkedUser = User::query()->where('student_id', $adminUuid)->first();
            if ($linkedUser) {
                return $linkedUser;
            }
        }

        $email = trim((string) ($admin->email ?? ''));
        if ($email !== '') {
            return User::query()->where('email', $email)->first();
        }

        return null;
    }

    private function hasClinicAccountAccess(User $user, ?Admin $linkedAdmin = null): bool
    {
        if (User::normalizeRole((string) ($user->user_role ?? '')) === User::ROLE_SUPERADMIN) {
            return true;
        }

        if ($user->isStudentAssistant()) {
            return true;
        }

        $accessLevel = strtolower(trim((string) (
            $linkedAdmin?->access_level
            ?? ''
        )));

        return in_array($accessLevel, ['clinic_staff', 'clinic staff', 'staff', 'superadmin'], true);
    }

    private function restoreUserToBaseRole(User $user): void
    {
        $this->applyBaseRoleToUser($user);
        if (Schema::hasColumn('users', 'status')) {
            $user->status = 'active';
        }
        $user->save();
    }

    private function activateUserForAdminHub(User $user): void
    {
        $linkedAdmin = $this->findLinkedAdminProfile($user);
        if ($this->hasClinicAccountAccess($user, $linkedAdmin)) {
            return;
        }

        $user->user_role = User::ROLE_ADMIN;

        if (Schema::hasColumn('users', 'status')) {
            $user->status = 'active';
        }

        $user->save();
    }

    private function reconcileUserAfterAdminHubDeactivation(User $user): void
    {
        $linkedAdmin = $this->findLinkedAdminProfile($user);
        if ($this->hasClinicAccountAccess($user, $linkedAdmin)) {
            return;
        }

        $this->restoreUserToBaseRole($user);
    }

    private function deactivateUserAccess(User $user, ?Admin $linkedAdmin = null): void
    {
        $this->applyBaseRoleToUser($user);

        if (Schema::hasColumn('users', 'status')) {
            $user->status = 'inactive';
        }

        if (Schema::hasColumn('users', 'remember_token')) {
            $user->remember_token = null;
        }

        $user->save();

        if (method_exists($user, 'tokens')) {
            $user->tokens()->delete();
        }

        $adminProfiles = $this->linkedAdminProfilesForUser($user);
        if ($linkedAdmin) {
            $adminProfiles = $adminProfiles
                ->prepend($linkedAdmin)
                ->unique(fn (Admin $admin) => (string) ($admin->admin_id ?? spl_object_id($admin)))
                ->values();
        }

        foreach ($adminProfiles as $adminProfile) {
            if (Admin::hasColumn('status')) {
                $adminProfile->status = 'inactive';
            }
            if (Admin::hasColumn('access_level')) {
                $adminProfile->access_level = null;
            }
            $adminProfile->save();
        }
    }

    private function logUserManagementAction(string $action, string $description): void
    {
        $user = Auth::user();
        if (!$user) {
            return;
        }

        \App\Models\ActivityLog::create([
            'user_id' => $user->id,
            'user_name' => $user->name ?? $user->email ?? 'Unknown User',
            'user_role' => strtolower((string) ($user->user_role ?? '')),
            'action' => $action,
            'module' => 'user_management',
            'event_type' => 'administrative_action',
            'description' => $description,
            'route_name' => optional(request()->route())->getName(),
            'http_method' => strtoupper((string) request()->method()),
            'request_path' => '/' . ltrim((string) request()->path(), '/'),
            'status_code' => 200,
            'ip_address' => request()->ip(),
            'user_agent' => substr((string) request()->userAgent(), 0, 255),
        ]);
    }

    private function resolveUniqueLocalIdentifier(string $seed): string
    {
        $base = trim($seed) !== '' ? trim($seed) : 'lookup-user';
        $candidate = $base;
        $counter = 1;

        while (User::query()->where('student_id', $candidate)->exists()) {
            $candidate = $base . '-' . $counter;
            $counter++;
        }

        return $candidate;
    }
}
