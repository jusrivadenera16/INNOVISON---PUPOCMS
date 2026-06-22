<?php

namespace App\Http\Controllers;

use App\Models\Admin;
use App\Models\Appointment;
use App\Models\HealthProfile;
use App\Models\User;
use App\Services\FacultySyncService;
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
        $allFacultyUsers = $this->collectFacultyUsers($facultySyncService, '');

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

        $adminHubRecords = $this->collectAdminHubProfiles($lookupSearch, $facultyDirectory);

        $lookupRecords = $lookupSearch !== ''
            ? collect($this->collectLocalUsers($lookupSearch))
                ->merge($this->collectFacultyUsers($facultySyncService, $lookupSearch))
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
                ->all()
            : [];

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
            'access_level' => ['nullable', Rule::in(['clinic_staff', 'designee'])],
            'office' => ['nullable', 'string', 'max:255'],
        ]);

        if ($managementView === 'admin-hub') {
            $linkedAdmin = $this->findLinkedAdminProfile($user) ?? new Admin();

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
                $linkedAdmin->email = trim((string) $request->email);
            }
            if (Admin::hasColumn('email_address')) {
                $linkedAdmin->email_address = trim((string) $request->email);
            }
            if (Admin::hasColumn('admin_hub_enabled')) {
                $linkedAdmin->admin_hub_enabled = true;
            }
            if (Admin::hasColumn('admin_hub_role')) {
                $linkedAdmin->admin_hub_role = $request->user_role;
            }
            if (Admin::hasColumn('status')) {
                $linkedAdmin->status = $request->status;
            }
            if (Admin::hasColumn('office')) {
                $linkedAdmin->office = $request->input('office');
            }
            $linkedAdmin->save();

            $this->logUserManagementAction(
                'Added local account to Admin Hub',
                sprintf(
                    'Added %s (%s) to the centralized Admin Hub as %s without changing clinic access.',
                    $user->name ?? $user->email,
                    $user->email,
                    $request->user_role
                )
            );

            return $this->redirectToManagementView(
                $request,
                'success',
                'The profile was added to the Admin Hub. Clinic account permissions were not changed.'
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
            'lookup_source' => ['required', Rule::in(['faculty'])],
            'management_view' => ['nullable', Rule::in(['account-access', 'admin-hub'])],
            'user_role' => ['required', Rule::in($allowedRoles)],
            'status' => ['required', Rule::in(['active', 'inactive'])],
            'email' => ['required', 'email', 'max:255'],
            'admin_email' => ['nullable', 'email', 'max:255'],
            'access_level' => ['nullable', Rule::in(['clinic_staff', 'designee'])],
            'office' => ['nullable', 'string', 'max:255'],
            'first_name' => ['nullable', 'string', 'max:255'],
            'last_name' => ['nullable', 'string', 'max:255'],
            'full_name' => ['nullable', 'string', 'max:255'],
            'external_identifier' => ['nullable', 'string', 'max:255'],
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
            $externalIdentifier = trim((string) $request->input('external_identifier', ''));
            $linkedAdmin = Admin::query()
                ->where(function ($query) use ($adminEmail, $baseEmail) {
                    if (Admin::hasColumn('email')) {
                        $query->orWhere('email', $adminEmail)->orWhere('email', $baseEmail);
                    }
                    if (Admin::hasColumn('email_address')) {
                        $query->orWhere('email_address', $adminEmail)->orWhere('email_address', $baseEmail);
                    }
                })
                ->first() ?? new Admin();

            if (Admin::hasColumn('first_name')) {
                $linkedAdmin->first_name = $firstName !== '' ? $firstName : 'Faculty';
            }
            if (Admin::hasColumn('last_name')) {
                $linkedAdmin->last_name = $lastName !== '' ? $lastName : 'User';
            }
            if (Admin::hasColumn('name')) {
                $linkedAdmin->name = $fullName !== '' ? $fullName : trim(($linkedAdmin->first_name ?? 'Faculty') . ' ' . ($linkedAdmin->last_name ?? 'User'));
            }
            if (Admin::hasColumn('email')) {
                $linkedAdmin->email = $adminEmail;
            }
            if (Admin::hasColumn('email_address')) {
                $linkedAdmin->email_address = $adminEmail;
            }
            if (Admin::hasColumn('external_identifier')) {
                $linkedAdmin->external_identifier = $externalIdentifier !== '' ? $externalIdentifier : null;
            }
            if (Admin::hasColumn('admin_hub_enabled')) {
                $linkedAdmin->admin_hub_enabled = true;
            }
            if (Admin::hasColumn('admin_hub_role')) {
                $linkedAdmin->admin_hub_role = $requestedRoleRaw;
            }
            if (Admin::hasColumn('status')) {
                $linkedAdmin->status = $request->status;
            }
            if (Admin::hasColumn('office')) {
                $linkedAdmin->office = $request->input('office');
            }
            $linkedAdmin->save();

            $this->logUserManagementAction(
                'Added admin hub profile from lookup',
                sprintf(
                    'Added %s (%s) from %s lookup into the centralized Admin Hub as %s.',
                    $linkedAdmin->name ?? $adminEmail,
                    $adminEmail,
                    $request->lookup_source,
                    $requestedRoleRaw
                )
            );

            return $this->redirectToManagementView($request, 'success', 'Lookup user added to the Admin Hub successfully.');
        }

        $user = User::query()->where('email', $baseEmail)->first();

        if (!$user) {
            $studentIdSeed = trim((string) $request->input('external_identifier', ''));
            if ($studentIdSeed === '') {
                $studentIdSeed = 'faculty-' . strtolower(substr(md5($baseEmail), 0, 10));
            }

            $user = new User();
            $user->student_id = $this->resolveUniqueLocalIdentifier($studentIdSeed);
            $user->first_name = $firstName !== '' ? $firstName : 'Faculty';
            $user->middle_name = $middleName !== '' ? $middleName : null;
            $user->last_name = $lastName !== '' ? $lastName : 'User';
            $user->name = $fullName !== '' ? $fullName : trim($user->first_name . ' ' . $user->last_name);
            $user->email = $baseEmail;
            $user->password = bcrypt(\Illuminate\Support\Str::random(40));
            if (Schema::hasColumn('users', 'idp_role')) {
                $user->idp_role = 'faculty';
            }
        } elseif (
            Schema::hasColumn('users', 'idp_role')
            && trim((string) $user->idp_role) === ''
            && !in_array(User::normalizeRole((string) $user->user_role), [User::ROLE_ADMIN, User::ROLE_SUPERADMIN], true)
        ) {
            $user->idp_role = $this->baseRoleTokenForUser($user);
        }

        $user->user_role = $normalizedRequestedRole;
        if (Schema::hasColumn('users', 'user_type')) {
            $user->user_type = $roleAssignment['user_type'];
        }
        if (Schema::hasColumn('users', 'status')) {
            $user->status = $request->status;
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

    public function updateAdminHub(Request $request, Admin $admin)
    {
        $this->ensureCanManageUsers();

        $request->validate([
            'user_role' => ['required', Rule::in(['admin_designee'])],
            'status' => ['required', Rule::in(['active', 'inactive'])],
            'email' => ['required', 'email', 'max:255'],
            'office' => ['nullable', 'string', 'max:255'],
            'first_name' => ['nullable', 'string', 'max:255'],
            'last_name' => ['nullable', 'string', 'max:255'],
            'full_name' => ['nullable', 'string', 'max:255'],
        ]);

        if (Admin::hasColumn('first_name')) {
            $admin->first_name = trim((string) $request->input('first_name', ''));
        }
        if (Admin::hasColumn('last_name')) {
            $admin->last_name = trim((string) $request->input('last_name', ''));
        }
        if (Admin::hasColumn('name')) {
            $fullName = trim((string) $request->input('full_name', ''));
            $admin->name = $fullName !== '' ? $fullName : trim(implode(' ', array_filter([$admin->first_name, $admin->last_name])));
        }
        if (Admin::hasColumn('email')) {
            $admin->email = trim((string) $request->input('email'));
        }
        if (Admin::hasColumn('email_address')) {
            $admin->email_address = trim((string) $request->input('email'));
        }
        if (Admin::hasColumn('external_identifier')) {
            $incomingIdentifier = trim((string) $request->input('external_identifier', ''));
            if ($incomingIdentifier !== '') {
                $admin->external_identifier = $incomingIdentifier;
            }
        }
        if (Admin::hasColumn('status')) {
            $admin->status = $request->status;
        }
        if (Admin::hasColumn('office')) {
            $admin->office = $request->input('office');
        }
        if (Admin::hasColumn('admin_hub_enabled')) {
            $admin->admin_hub_enabled = true;
        }
        if (Admin::hasColumn('admin_hub_role')) {
            $admin->admin_hub_role = $request->input('user_role');
        }
        $admin->save();

        $this->logUserManagementAction(
            'Updated admin hub profile',
            sprintf(
                'Updated admin hub record #%s (%s).',
                $admin->admin_id,
                $admin->name ?? ($admin->email ?? 'Unknown Admin')
            )
        );

        return $this->redirectToManagementView($request, 'success', 'Admin Hub profile updated successfully.');
    }

    public function destroyAdminHub(Request $request, Admin $admin)
    {
        $this->ensureCanManageUsers();

        $linkedUser = $this->resolveLinkedUserForAdminRecord($admin);

        if (Admin::hasColumn('admin_hub_enabled')) {
            $admin->admin_hub_enabled = false;
        }
        if (Admin::hasColumn('admin_hub_role')) {
            $admin->admin_hub_role = null;
        }
        $admin->save();

        if ($linkedUser && !$this->hasClinicAccountAccess($linkedUser, $admin)) {
            $this->restoreUserToBaseRole($linkedUser);
        }

        $this->logUserManagementAction(
            'Removed admin hub access',
            sprintf(
                'Removed centralized Admin Hub membership for record #%s (%s).',
                $admin->admin_id,
                $admin->name ?? ($admin->email ?? 'Unknown Admin')
            )
        );

        return $this->redirectToManagementView(
            $request,
            'success',
            $linkedUser && !$this->hasClinicAccountAccess($linkedUser, $admin)
                ? 'Admin Hub membership removed. The linked account returned to its base role.'
                : 'Admin Hub membership removed. Clinic account access was not changed.'
        );
    }

    public function deleteAdminHubRecord(Request $request, Admin $admin)
    {
        $this->ensureCanManageUsers();

        $adminName = $admin->name ?? ($admin->email ?? 'Unknown Admin');
        $adminId = $admin->admin_id;
        $linkedUser = $this->resolveLinkedUserForAdminRecord($admin);
        $hasLinkedClinicAccount = $linkedUser
            ? $this->hasClinicAccountAccess($linkedUser, $admin)
            : in_array(strtolower(trim((string) ($admin->access_level ?? ''))), ['clinic_staff', 'clinic staff', 'staff', 'superadmin'], true);

        if ($hasLinkedClinicAccount) {
            if (Admin::hasColumn('admin_hub_enabled')) {
                $admin->admin_hub_enabled = false;
            }
            if (Admin::hasColumn('admin_hub_role')) {
                $admin->admin_hub_role = null;
            }
            $admin->save();
        } else {
            $admin->delete();
        }

        $this->logUserManagementAction(
            $hasLinkedClinicAccount ? 'Removed linked Admin Hub record' : 'Deleted admin hub record',
            sprintf(
                $hasLinkedClinicAccount
                    ? 'Removed record #%s (%s) from the Admin Hub directory while preserving clinic access.'
                    : 'Deleted standalone admin hub record #%s (%s) from the admins table.',
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

        $isAdminHubMember = $linkedAdmin
            && Admin::hasColumn('admin_hub_enabled')
            && (bool) $linkedAdmin->admin_hub_enabled
            && in_array(strtolower(trim((string) ($linkedAdmin->admin_hub_role ?? ''))), ['admin_designee', 'designee'], true);

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

    private function collectLocalUsers(string $search): array
    {
        $query = User::query()->with(['healthProfile', 'adminProfile']);

        if ($search !== '') {
            $query->where(function ($builder) use ($search) {
                foreach ([
                    'student_id',
                    'student_number',
                    'first_name',
                    'last_name',
                    'name',
                    'email',
                    'course',
                    'year',
                    'section',
                    'user_role',
                    'idp_role',
                ] as $column) {
                    if (Schema::hasColumn('users', $column)) {
                        $builder->orWhere($column, 'like', '%' . $search . '%');
                    }
                }

                if (Schema::hasTable('admins')) {
                    $builder->orWhereHas('adminProfile', function ($adminQuery) use ($search) {
                        foreach (['admin_id', 'external_identifier', 'name', 'email', 'email_address'] as $column) {
                            if (Admin::hasColumn($column)) {
                                $adminQuery->orWhere($column, 'like', '%' . $search . '%');
                            }
                        }
                    });
                }
            });
        }

        return $query->orderBy('first_name')
            ->limit(100)
            ->get()
            ->map(function (User $user) {
                $linkedAdmin = $this->findLinkedAdminProfile($user);
                $resolvedAccessLevel = $this->resolveEffectiveAdminAccessLevel($user, $linkedAdmin);
                $rawRole = strtolower(trim((string) ($user->user_role ?? 'student')));
                $role = User::normalizeRole($rawRole);
                $source = $this->resolveUserSource($user);
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

                $studentPhoto = $user->healthProfile?->student_photo;
                $studentNumber = trim((string) ($user->student_number ?? ''));
                $studentId = trim((string) ($user->student_id ?? ''));
                $resolvedIdentifier = $this->resolveUserDisplayIdentifier($user, $linkedAdmin);

                return [
                    'id' => (string) $user->id,
                    'record_id' => (string) $user->id,
                    'source' => $source,
                    'source_label' => $this->resolveUserSourceLabel($user),
                    'name' => $displayName !== '' ? $displayName : ($user->email ?? 'Unknown User'),
                    'first_name' => (string) ($user->first_name ?? ''),
                    'last_name' => (string) ($user->last_name ?? ''),
                    'student_id' => $resolvedIdentifier,
                    'email' => (string) ($user->email ?? ''),
                    'role' => $this->resolveRoleLabel($user, $linkedAdmin),
                    'raw_role' => $rawRole,
                    'normalized_role' => $role,
                    'status' => $status === 'inactive' ? 'inactive' : 'active',
                    'avatar_url' => $studentPhoto ? asset('storage/' . $studentPhoto) : null,
                    'avatar_letter' => strtoupper(substr($displayName !== '' ? $displayName : ($user->email ?? 'U'), 0, 1)),
                    'can_edit' => true,
                    'is_external' => false,
                    'meta' => [
                        'email' => (string) ($user->email ?? ''),
                        'course' => (string) ($user->course ?? ''),
                        'year' => (string) ($user->year ?? ''),
                        'section' => (string) ($user->section ?? ''),
                        'student_number' => $studentNumber,
                        'DOB' => (string) ($user->DOB ?? ''),
                        'gender' => (string) ($user->gender ?? ''),
                        'contact_no' => (string) ($user->contact_no ?? ''),
                        'is_health_profile_completed' => (bool) ($user->is_health_profile_completed ?? false),
                        'access_level' => $resolvedAccessLevel,
                        'idp_role' => (string) ($user->idp_role ?? ''),
                        'user_type' => (string) ($user->user_type ?? ''),
                        'admin_login_email' => (string) ($linkedAdmin?->email_address ?? $linkedAdmin?->email ?? ''),
                        'admin_profile_id' => $linkedAdmin?->admin_id,
                        'admin_profile_name' => (string) ($linkedAdmin?->name ?? ''),
                        'office' => (string) ($linkedAdmin?->office ?? ''),
                        'updated_at' => optional($user->updated_at)->toIso8601String(),
                    ],
                ];
            })
            ->values()
            ->all();
    }

    private function collectFacultyUsers(FacultySyncService $facultySyncService, string $search): array
    {
        try {
            $faculties = $facultySyncService->fetchFaculties($search);
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
                $facultyCode = trim((string) ($faculty['faculty_code'] ?? ''));
                $facultyNumericId = trim((string) ($faculty['faculty_id'] ?? $faculty['id'] ?? ''));
                $facultyIdentifier = $facultyCode !== '' ? $facultyCode : $facultyNumericId;
                $recordId = $facultyIdentifier !== '' ? $facultyIdentifier : ($email !== '' ? $email : 'faculty');

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
                    'student_id' => $facultyIdentifier,
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
                        'faculty_code' => $facultyCode !== '' ? $facultyCode : null,
                        'faculty_type' => $faculty['faculty_type'] ?? null,
                        'department' => $faculty['department'] ?? null,
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
        if (!Schema::hasTable('admins')) {
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

        $query = Admin::query();

        if (Admin::hasColumn('admin_hub_enabled')) {
            $query->where('admin_hub_enabled', true);
        } else {
            // Backward compatibility before the Admin Hub membership migration runs.
            $query->where('access_level', 'designee');
        }

        if ($search !== '') {
            $query->where(function ($builder) use ($search) {
                foreach (['admin_id', 'name', 'first_name', 'last_name', 'email', 'email_address', 'office', 'status'] as $column) {
                    if (Admin::hasColumn($column)) {
                        $builder->orWhere($column, 'like', '%' . $search . '%');
                    }
                }
            });
        }

        return $query->orderBy('name')
            ->limit(100)
            ->get()
            ->map(function (Admin $admin) use ($facultyByEmail, $facultyByName, $facultyByIdentifier) {
                $linkedUser = Admin::hasColumn('user_id') && $admin->user_id ? User::find($admin->user_id) : null;
                $externalIdentifier = trim((string) ($admin->external_identifier ?? ''));
                $displayName = trim((string) ($admin->name ?? ''));
                if ($displayName === '') {
                    $displayName = trim(implode(' ', array_filter([
                        $admin->first_name ?? '',
                        $admin->last_name ?? '',
                    ])));
                }
                $email = trim((string) ($admin->email_address ?? $admin->email ?? ''));
                $normalizedEmail = trim(strtolower($email));
                $normalizedName = trim(strtolower($displayName));
                $matchedFaculty = $normalizedEmail !== '' && isset($facultyByEmail[$normalizedEmail])
                    ? $facultyByEmail[$normalizedEmail]
                    : ($normalizedName !== '' && isset($facultyByName[$normalizedName])
                        ? $facultyByName[$normalizedName]
                        : null);
                if (!$matchedFaculty && $externalIdentifier !== '') {
                    $matchedFaculty = $facultyByIdentifier[strtolower($externalIdentifier)] ?? null;
                }
                $facultyIdentifier = trim((string) (
                    data_get($matchedFaculty, 'meta.faculty_code')
                    ?: ($matchedFaculty['student_id'] ?? '')
                ));
                $status = strtolower(trim((string) ($admin->status ?? 'active')));
                $hubRole = strtolower(trim((string) ($admin->admin_hub_role ?? 'admin_designee')));
                if (!in_array($hubRole, ['admin_designee', 'super_admin'], true)) {
                    $hubRole = 'admin_designee';
                }
                $accessLevel = strtolower(trim((string) ($admin->access_level ?? '')));
                if ($status === '') {
                    $status = 'active';
                }

                $resolvedIdentifier = $facultyIdentifier !== ''
                    ? $facultyIdentifier
                    : ($externalIdentifier !== '' ? $externalIdentifier : (string) ($linkedUser?->student_id ?? ''));

                return [
                    'id' => (string) $admin->admin_id,
                    'record_id' => (string) $admin->admin_id,
                    'source' => 'admin',
                    'source_label' => 'Admin Hub',
                    'name' => $displayName !== '' ? $displayName : ($email !== '' ? $email : 'Admin Hub Record'),
                    'first_name' => (string) ($admin->first_name ?? ''),
                    'last_name' => (string) ($admin->last_name ?? ''),
                    'student_id' => $resolvedIdentifier,
                    'email' => $email,
                    'role' => $hubRole === 'super_admin' ? 'Super Admin' : 'Admin - Designee',
                    'raw_role' => $hubRole,
                    'normalized_role' => $linkedUser
                        ? User::normalizeRole((string) $linkedUser->user_role)
                        : User::ROLE_ADMIN,
                    'status' => $status === 'inactive' ? 'inactive' : 'active',
                    'avatar_url' => null,
                    'avatar_letter' => strtoupper(substr($displayName !== '' ? $displayName : ($email ?: 'A'), 0, 1)),
                    'can_edit' => true,
                    'is_external' => false,
                    'update_url' => route('admin.user-management.admin-hub.update', $admin->admin_id),
                    'delete_url' => route('admin.user-management.admin-hub.destroy', $admin->admin_id),
                    'delete_admin_hub_url' => route('admin.user-management.admin-hub.delete-record', $admin->admin_id),
                    'meta' => [
                        'email' => $email,
                        'access_level' => $accessLevel,
                        'admin_hub_role' => $hubRole,
                        'admin_login_email' => $email,
                        'admin_profile_id' => $admin->admin_id,
                        'admin_profile_name' => $displayName,
                        'external_identifier' => $resolvedIdentifier,
                        'faculty_identifier' => $facultyIdentifier,
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

    private function resolveUserSource(User $user): string
    {
        $rawRole = strtolower(trim((string) ($user->user_role ?? 'student')));
        $normalizedRole = User::normalizeRole($rawRole);
        $userType = strtolower(trim((string) ($user->user_type ?? '')));

        if ($normalizedRole === User::ROLE_SUPERADMIN) {
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

        return match ($normalizedRole) {
            User::ROLE_SUPERADMIN => 'superadmin',
            User::ROLE_ADMIN => 'admin',
            default => 'student',
        };
    }

    private function resolveUserSourceLabel(User $user): string
    {
        return match ($this->resolveUserSource($user)) {
            'superadmin' => 'Super Admin',
            'admin' => 'Admin',
            'student_assistant' => 'Student Assistant',
            default => 'Student',
        };
    }

    private function resolveRoleLabel(User $user, ?Admin $linkedAdmin = null): string
    {
        $rawRole = strtolower(trim((string) ($user->user_role ?? 'student')));
        $normalizedRole = User::normalizeRole($rawRole);
        $userType = strtolower(trim((string) ($user->user_type ?? '')));

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

        return match ($normalizedRole) {
            User::ROLE_SUPERADMIN => 'Super Admin',
            User::ROLE_ADMIN => 'Admin - Regular',
            default => 'Student',
        };
    }

    private function adminRoleLabelForAccessLevel(string $accessLevel): string
    {
        return match (strtolower(trim($accessLevel))) {
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
            $activeAdminHubMembership = !Admin::hasColumn('admin_hub_enabled')
                || (bool) ($admin->admin_hub_enabled ?? false);

            return match ($accessLevel) {
                'superadmin' => 40,
                'clinic_staff', 'clinic staff', 'staff' => 30,
                'designee' => $activeAdminHubMembership ? 20 : 5,
                default => 10,
            };
        };

        return $profiles->sortByDesc($scoreProfile)->first();
    }

    private function resolveEffectiveAdminAccessLevel(User $user, ?Admin $linkedAdmin = null): string
    {
        $profile = $this->resolveEffectiveAdminProfile($user, $linkedAdmin);
        $accessLevel = strtolower(trim((string) ($profile?->access_level ?? '')));

        if (
            $accessLevel === 'designee'
            && Admin::hasColumn('admin_hub_enabled')
            && !(bool) ($profile?->admin_hub_enabled ?? false)
        ) {
            return '';
        }

        if (
            $accessLevel === ''
            && $profile
            && (!Admin::hasColumn('admin_hub_enabled') || (bool) ($profile->admin_hub_enabled ?? false))
            && in_array(strtolower(trim((string) ($profile->admin_hub_role ?? ''))), ['designee', 'admin_designee'], true)
        ) {
            return 'designee';
        }

        return $accessLevel;
    }

    private function resolveDisplayIdentifier(string $studentNumber, string $studentId): string
    {
        if ($studentNumber !== '') {
            return $studentNumber;
        }

        $isUuid = preg_match(
            '/^[0-9a-f]{8}-[0-9a-f]{4}-[1-5][0-9a-f]{3}-[89ab][0-9a-f]{3}-[0-9a-f]{12}$/i',
            $studentId
        ) === 1;

        return $studentId !== '' && !$isUuid ? $studentId : '';
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
            $facultyCode = trim((string) ($linkedAdmin?->external_identifier ?? ''));

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

    private function recordSortWeight(string $source): int
    {
        return match ($source) {
            'superadmin' => 0,
            'admin' => 1,
            'student_assistant' => 2,
            'student' => 3,
            'faculty' => 4,
            default => 9,
        };
    }

    private function isProtectedUser(User $user): bool
    {
        $currentUserId = Auth::id();
        $currentUser = Auth::user();
        $currentUserRole = User::normalizeRole((string) ($currentUser?->user_role ?? ''));
        $targetRole = User::normalizeRole((string) ($user->user_role ?? ''));

        if ($user->id === $currentUserId) {
            return true;
        }

        return $targetRole === User::ROLE_SUPERADMIN && $currentUserRole === User::ROLE_SUPERADMIN;
    }

    private function canManageRecord(array $record, ?int $currentUserId = null): bool
    {
        $currentUser = Auth::user();
        $currentUserRole = User::normalizeRole((string) ($currentUser?->user_role ?? ''));
        $recordRole = strtolower(trim((string) ($record['raw_role'] ?? $record['normalized_role'] ?? $record['source'] ?? 'student')));
        $recordId = (string) ($record['id'] ?? $record['record_id'] ?? '');

        if ($recordId !== '' && $currentUserId !== null && $recordId === (string) $currentUserId) {
            return false;
        }

        if (in_array($recordRole, ['superadmin', 'super_admin'], true) && $currentUserRole === User::ROLE_SUPERADMIN) {
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
