<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\Admin;
use App\Models\AdminHub;
use App\Models\ActivityLog;
use App\Models\HealthFormSubmission;
use App\Models\User;
use App\Services\ClinicWorkflowService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

class LoginController extends Controller
{
    private function studentGuardName(): string
    {
        return 'student';
    }

    private function adminGuardName(): string
    {
        return 'admin';
    }

    private function guardForUser(User $user): string
    {
        $redirectPath = $this->resolveRedirectPathForUser($user);

        if (str_starts_with($redirectPath, '/admin/') || str_starts_with($redirectPath, '/assistant/')) {
            return $this->adminGuardName();
        }

        return $this->studentGuardName();
    }

    private function authenticatedUser(): ?User
    {
        foreach ([$this->adminGuardName(), $this->studentGuardName(), 'web'] as $guard) {
            $user = Auth::guard($guard)->user();
            if ($user instanceof User) {
                return $user;
            }
        }

        return null;
    }

    private function activeGuard(?Request $request = null): ?string
    {
        $requestedGuard = trim((string) ($request?->input('portal_guard') ?? ''));
        if (in_array($requestedGuard, [$this->adminGuardName(), $this->studentGuardName()], true)) {
            return $requestedGuard;
        }

        foreach ([$this->adminGuardName(), $this->studentGuardName(), 'web'] as $guard) {
            if (Auth::guard($guard)->check()) {
                return $guard;
            }
        }

        return null;
    }

    private function usersTableHasUserTypeColumn(): bool
    {
        static $hasColumn;

        if ($hasColumn === null) {
            $hasColumn = Schema::hasColumn('users', 'user_type');
        }

        return $hasColumn;
    }

    private function studentRoleValue(): string
    {
        if (defined(User::class . '::ROLE_STUDENT')) {
            return (string) constant(User::class . '::ROLE_STUDENT');
        }

        return 'student';
    }

    private function adminRoleValue(): string
    {
        if (defined(User::class . '::ROLE_ADMIN')) {
            return (string) constant(User::class . '::ROLE_ADMIN');
        }

        if (defined(User::class . '::ROLE_STUDENT_ASSISTANT')) {
            return (string) constant(User::class . '::ROLE_STUDENT_ASSISTANT');
        }

        return 'admin';
    }

    private function superAdminRoleValue(): string
    {
        if (defined(User::class . '::ROLE_SUPERADMIN')) {
            return (string) constant(User::class . '::ROLE_SUPERADMIN');
        }

        if (defined(User::class . '::ROLE_SUPER_ADMIN')) {
            return (string) constant(User::class . '::ROLE_SUPER_ADMIN');
        }

        return 'superadmin';
    }

    private function recordAuthEvent(
        Request $request,
        string $action,
        string $description,
        ?User $actor = null,
        int $statusCode = 200,
        string $eventType = 'auth'
    ): void
    {
        $user = $actor ?? $this->authenticatedUser();
        $email = trim((string) $request->input('email', ''));

        if (!$user && $email === '') {
            return;
        }

        ActivityLog::create([
            'user_id' => $user?->id,
            'user_name' => $user?->name ?? $user?->email ?? $email,
            'user_role' => $user ? strtolower((string) ($user->user_role ?? '')) : null,
            'action' => $action,
            'module' => 'Authentication',
            'event_type' => $eventType,
            'description' => $description,
            'route_name' => optional($request->route())->getName(),
            'http_method' => strtoupper((string) $request->method()),
            'request_path' => '/' . ltrim((string) $request->path(), '/'),
            'status_code' => $statusCode,
            'subject_type' => 'user',
            'subject_id' => $user?->id ? (string) $user->id : null,
            'metadata' => [
                'session_id' => $request->session()->getId(),
                'email' => $email !== '' ? $email : null,
            ],
            'ip_address' => $request->ip(),
            'user_agent' => substr((string) $request->userAgent(), 0, 255),
        ]);
    }

    private function useIdpAuth(): bool
    {
        return (bool) config('services.idp.enabled', false);
    }

    private function localLoginEnabled(?Request $request = null): bool
    {
        $host = strtolower(trim((string) optional($request)->getHost()));
        $allowedHosts = array_map(
            fn ($allowedHost) => strtolower(trim((string) $allowedHost)),
            (array) config('services.local_login.allowed_hosts', [])
        );

        return $host !== '' && in_array($host, $allowedHosts, true);
    }

    private function redirectPathByRole(string $role): string
    {
        $normalizedRole = User::normalizeRole($role);

        if ($normalizedRole === User::normalizeRole($this->superAdminRoleValue())) {
            return '/admin/dashboard';
        }

        if ($normalizedRole === User::normalizeRole($this->adminRoleValue())) {
            return '/student/home';
        }

        return '/student/home';
    }

    private function queueHealthProfilePrompt(Request $request, User $user, string $redirectPath): void
    {
        if (!str_starts_with($redirectPath, '/student/')) {
            return;
        }

        $healthProfile = $user->healthProfile()->first();
        if (!$healthProfile) {
            $request->session()->flash('show_health_profile_prompt', true);
            return;
        }

        $pendingReason = strtolower(trim((string) $healthProfile->pending_reason));
        $hasHealthFormCorrection = str_contains($pendingReason, 'health form correction');
        $hasNewHealthFormRequest = HealthFormSubmission::query()
            ->where('user_id', $user->id)
            ->where('status', HealthFormSubmission::STATUS_REQUESTED)
            ->exists();

        if ($hasHealthFormCorrection || $hasNewHealthFormRequest) {
            $request->session()->flash('show_health_form_action_prompt', true);
        }
    }

    private function findLinkedAdminProfile(User $user): ?Admin
    {
        if (!Schema::hasTable('admins')) {
            return null;
        }

        if (Admin::hasColumn('user_id')) {
            $linkedByUserId = Admin::query()
                ->where('user_id', $user->id)
                ->first();

            if ($linkedByUserId) {
                return $linkedByUserId;
            }
        }

        $email = trim((string) ($user->email ?? ''));
        if ($email === '') {
            return null;
        }

        $linkedAdmin = Admin::query()
            ->where(function ($builder) use ($email) {
                if (Admin::hasColumn('email')) {
                    $builder->orWhere('email', $email);
                }

                if (Admin::hasColumn('email_address')) {
                    $builder->orWhere('email_address', $email);
                }
            })
            ->first();

        if ($linkedAdmin && Admin::hasColumn('user_id') && !$linkedAdmin->user_id) {
            $linkedAdmin->user_id = $user->id;
            $linkedAdmin->save();
        }

        return $linkedAdmin;
    }

    private function ensureDefaultClinicAdminProfile(User $user, string $role): void
    {
        if (!Schema::hasTable('admins') || User::normalizeRole($role) !== User::ROLE_ADMIN) {
            return;
        }

        $linkedAdmin = $this->findLinkedAdminProfile($user);
        $linkedAdminHub = $this->findLinkedAdminHubProfile($user);
        $adminHubRole = strtolower(trim((string) ($linkedAdminHub?->role ?? '')));
        $adminHubStatus = strtolower(trim((string) ($linkedAdminHub?->status ?? 'active')));
        $clinicAccessLevel = strtolower(trim((string) ($linkedAdmin?->access_level ?? '')));

        if (
            $linkedAdminHub
            && $adminHubStatus !== 'inactive'
            && in_array($adminHubRole, ['designee', 'admin_designee'], true)
            && !in_array($clinicAccessLevel, ['clinic_staff', 'clinic staff', 'staff', 'superadmin'], true)
        ) {
            return;
        }

        if (!$linkedAdmin) {
            $linkedAdmin = new Admin();
        }

        if (Admin::hasColumn('user_id')) {
            $linkedAdmin->user_id = $user->id;
        }

        if (Admin::hasColumn('first_name') && trim((string) $linkedAdmin->first_name) === '') {
            $linkedAdmin->first_name = $user->first_name;
        }

        if (Admin::hasColumn('last_name') && trim((string) $linkedAdmin->last_name) === '') {
            $linkedAdmin->last_name = $user->last_name;
        }

        if (Admin::hasColumn('name') && trim((string) $linkedAdmin->name) === '') {
            $linkedAdmin->name = trim((string) ($user->name ?? ''));
        }

        if (Admin::hasColumn('email') && trim((string) $linkedAdmin->email) === '') {
            $linkedAdmin->email = $user->email;
        }

        if (Admin::hasColumn('email_address') && trim((string) $linkedAdmin->email_address) === '') {
            $linkedAdmin->email_address = $user->email;
        }

        if (Admin::hasColumn('status') && trim((string) ($linkedAdmin->status ?? '')) === '') {
            $linkedAdmin->status = 'active';
        }

        $linkedAdmin->save();
    }

    private function isStudentAssistantAccount(User $user): bool
    {
        if (User::normalizeRole((string) $user->user_role) !== User::ROLE_ADMIN) {
            return false;
        }

        $userType = strtolower(trim((string) ($user->user_type ?? '')));

        return in_array($userType, ['assistant', 'student assistant', 'student_assistant'], true);
    }

    private function isAdminDesigneeAccount(User $user): bool
    {
        if (User::normalizeRole((string) $user->user_role) !== User::ROLE_ADMIN) {
            return false;
        }

        $linkedAdmin = $this->findLinkedAdminProfile($user);
        $accessLevel = strtolower(trim((string) ($linkedAdmin?->access_level ?? '')));
        $linkedAdminHub = $this->findLinkedAdminHubProfile($user);
        $adminHubRole = strtolower(trim((string) ($linkedAdminHub?->role ?? '')));
        $adminHubStatus = strtolower(trim((string) ($linkedAdminHub?->status ?? 'active')));
        if (in_array($accessLevel, ['clinic_staff', 'clinic staff', 'staff', 'superadmin'], true)) {
            return false;
        }

        if ($linkedAdminHub && in_array($adminHubRole, ['designee', 'admin_designee'], true)) {
            return $adminHubStatus !== 'inactive';
        }

        return false;
    }

    private function resolveLocalRoleFromAdminHub(string $email, string $adminUuid = ''): ?string
    {
        $email = trim(strtolower($email));
        $adminUuid = trim($adminUuid);
        if ($email === '' && $adminUuid === '') {
            return null;
        }

        if (Schema::hasTable('admins') && $email !== '') {
            $linkedAdmin = Admin::query()
                ->where(function ($builder) use ($email) {
                    if (Admin::hasColumn('email')) {
                        $builder->orWhere('email', $email);
                    }

                    if (Admin::hasColumn('email_address')) {
                        $builder->orWhere('email_address', $email);
                    }
                })
                ->first();

            $accountAccessLevel = strtolower(trim((string) ($linkedAdmin?->access_level ?? '')));
            if (in_array($accountAccessLevel, ['superadmin', 'super_admin'], true)) {
                return $this->superAdminRoleValue();
            }

            if (in_array($accountAccessLevel, ['clinic_staff', 'clinic staff', 'staff'], true)) {
                return $this->adminRoleValue();
            }
        }

        $linkedAdminHub = $this->findAdminHubProfileByIdentity($email, $adminUuid);
        if ($linkedAdminHub) {
            $hubStatus = strtolower(trim((string) ($linkedAdminHub->status ?? 'active')));
            if ($hubStatus === 'inactive') {
                return null;
            }

            $hubRole = strtolower(trim((string) ($linkedAdminHub->role ?? '')));
            if (in_array($hubRole, ['designee', 'admin_designee'], true)) {
                return $this->adminRoleValue();
            }
        }

        return null;
    }

    private function resolveExistingAccountAccessRole(User $user): ?string
    {
        $linkedAdmin = Schema::hasTable('admins') ? $this->findLinkedAdminProfile($user) : null;
        $accessLevel = strtolower(trim((string) ($linkedAdmin?->access_level ?? '')));

        if (in_array($accessLevel, ['superadmin', 'super_admin'], true)) {
            return $this->superAdminRoleValue();
        }

        if (in_array($accessLevel, ['clinic_staff', 'clinic staff', 'staff'], true)) {
            return $this->adminRoleValue();
        }

        return User::normalizeRole((string) ($user->user_role ?? '')) === User::ROLE_SUPERADMIN
            ? $this->superAdminRoleValue()
            : null;
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

        return $this->findAdminHubProfileByIdentity(
            (string) ($user->email ?? ''),
            (string) ($user->student_id ?? '')
        );
    }

    private function findAdminHubProfileByIdentity(string $email, string $adminUuid = ''): ?AdminHub
    {
        if (!Schema::hasTable('admin_hub')) {
            return null;
        }

        $adminUuid = trim($adminUuid);
        if (!$this->isUuid($adminUuid)) {
            $adminUuid = '';
        }
        if ($adminUuid !== '' && AdminHub::hasColumn('admin_uuid')) {
            $matchedByUuid = AdminHub::query()->where('admin_uuid', $adminUuid)->first();
            if ($matchedByUuid) {
                return $matchedByUuid;
            }
        }

        $email = trim(strtolower($email));
        if ($email === '' || !AdminHub::hasColumn('email')) {
            return null;
        }

        $matchedByEmail = AdminHub::query()->whereRaw('LOWER(email) = ?', [$email])->first();
        if (!$matchedByEmail || $adminUuid === '' || !AdminHub::hasColumn('admin_uuid')) {
            return $matchedByEmail;
        }

        $storedUuid = trim((string) ($matchedByEmail->admin_uuid ?? ''));

        return $storedUuid === '' || !$this->isUuid($storedUuid) || hash_equals($storedUuid, $adminUuid)
            ? $matchedByEmail
            : null;
    }

    private function syncAdminHubProfileFromIdp(User $user, string $adminUuid): ?AdminHub
    {
        $adminUuid = trim($adminUuid);
        if (!$this->isUuid($adminUuid)) {
            $adminUuid = '';
        }
        $adminHub = $this->findAdminHubProfileByIdentity((string) ($user->email ?? ''), $adminUuid);
        if (!$adminHub) {
            return null;
        }

        $storedIdentifier = trim((string) ($adminHub->admin_uuid ?? ''));
        if (
            AdminHub::hasColumn('employee_number')
            && !$this->isUuid($storedIdentifier)
            && $storedIdentifier !== ''
            && trim((string) ($adminHub->employee_number ?? '')) === ''
        ) {
            $adminHub->employee_number = $storedIdentifier;
        }
        if (AdminHub::hasColumn('admin_uuid') && $adminUuid !== '') {
            $adminHub->admin_uuid = $adminUuid;
        }
        if (AdminHub::hasColumn('user_id')) {
            $adminHub->user_id = $user->id;
        }
        if (AdminHub::hasColumn('first_name') && trim((string) ($user->first_name ?? '')) !== '') {
            $adminHub->first_name = $user->first_name;
        }
        if (AdminHub::hasColumn('middle_name')) {
            $adminHub->middle_name = trim((string) ($user->middle_name ?? '')) ?: null;
        }
        if (AdminHub::hasColumn('last_name') && trim((string) ($user->last_name ?? '')) !== '') {
            $adminHub->last_name = $user->last_name;
        }
        if (AdminHub::hasColumn('email') && trim((string) ($user->email ?? '')) !== '') {
            $adminHub->email = strtolower(trim((string) $user->email));
        }

        $employeeProfile = Schema::hasTable('health_profile_emp') ? $user->employeeHealthProfile : null;
        $linkedAdmin = Schema::hasTable('admins') ? $this->findLinkedAdminProfile($user) : null;
        $profileValues = [
            'employee_number' => [
                $adminHub->employee_number ?? null,
                $user->employee_number ?? null,
                $linkedAdmin?->employee_number,
                $employeeProfile?->employee_number,
            ],
            'birthday' => [$adminHub->birthday ?? null, $linkedAdmin?->birthday, $user->DOB ?? null, $employeeProfile?->birthday],
            'age' => [$adminHub->age ?? null, $linkedAdmin?->age, $employeeProfile?->age],
            'gender' => [$adminHub->gender ?? null, $linkedAdmin?->gender, $user->gender ?? null, $employeeProfile?->sex],
            'civil_status' => [$adminHub->civil_status ?? null, $linkedAdmin?->civil_status, $employeeProfile?->civil_status],
            'address' => [$adminHub->address ?? null, $linkedAdmin?->address, $employeeProfile?->home_address],
            'emergency_contact_person' => [$adminHub->emergency_contact_person ?? null, $linkedAdmin?->emergency_contact_person, $employeeProfile?->emergency_contact_person],
            'emergency_contact_no' => [$adminHub->emergency_contact_no ?? null, $linkedAdmin?->emergency_contact_no, $employeeProfile?->emergency_contact_no],
            'access_level' => ['designee'],
        ];

        foreach ($profileValues as $column => $values) {
            if (!AdminHub::hasColumn($column)) {
                continue;
            }

            foreach ($values as $value) {
                if ($value !== null && trim((string) $value) !== '') {
                    $adminHub->setAttribute($column, $value);
                    break;
                }
            }
        }

        $adminHub->save();

        return $adminHub;
    }

    private function resolveForcedLocalRole(string $email): ?string
    {
        $email = trim(strtolower($email));
        if ($email === '') {
            return null;
        }

        $localPart = Str::before($email, '@');
        $identifiers = array_map(
            static fn ($value) => trim(strtolower((string) $value)),
            (array) config('services.idp.local_superadmin_identifiers', [])
        );

        foreach ($identifiers as $identifier) {
            if ($identifier === '') {
                continue;
            }

            if ($identifier === $email || $identifier === $localPart) {
                return $this->superAdminRoleValue();
            }
        }

        return null;
    }

    private function resolveRedirectPathForUser(User $user): string
    {
        $forcedRole = $this->resolveForcedLocalRole((string) ($user->email ?? ''));
        if ($forcedRole !== null && User::normalizeRole($forcedRole) === User::ROLE_SUPERADMIN) {
            return '/admin/dashboard';
        }

        $normalizedRole = User::normalizeRole((string) ($user->user_role ?? ''));
        if ($normalizedRole === User::ROLE_SUPERADMIN) {
            return '/admin/dashboard';
        }

        if ($normalizedRole === User::ROLE_ADMIN) {
            if ($this->isStudentAssistantAccount($user)) {
                return '/assistant/choose-portal';
            }

            if ($this->isAdminDesigneeAccount($user)) {
                return '/student/home';
            }

            return '/student/home';
        }

        return '/student/home';
    }

    public function showStudentAssistantPortalChooser()
    {
        $user = $this->authenticatedUser();
        abort_unless($user instanceof User && $this->isStudentAssistantAccount($user), 403);

        return view('auth.student-assistant-portal', [
            'user' => $user,
            'adminWorkspaceAvailable' => $this->studentAssistantAdminWorkspaceAvailable(),
            'adminWorkspaceHoursLabel' => app(ClinicWorkflowService::class)->studentAssistantHoursLabel(),
        ]);
    }

    public function enterStudentPortal(Request $request)
    {
        $user = $this->authenticatedUser();
        abort_unless($user instanceof User && $this->isStudentAssistantAccount($user), 403);

        Auth::guard($this->studentGuardName())->login($user);
        Auth::shouldUse($this->studentGuardName());
        $this->queueHealthProfilePrompt($request, $user, '/student/home');

        return redirect('/student/home');
    }

    public function enterAdminPortal()
    {
        $user = $this->authenticatedUser();
        abort_unless($user instanceof User && $this->isStudentAssistantAccount($user), 403);

        if (!$this->studentAssistantAdminWorkspaceAvailable()) {
            $hours = app(ClinicWorkflowService::class)->studentAssistantHoursLabel();
            return redirect()
                ->route('assistant.choose-portal')
                ->withErrors([
                    'workspace' => "Admin Workspace is available {$hours}.",
                ]);
        }

        Auth::guard($this->adminGuardName())->login($user);
        Auth::shouldUse($this->adminGuardName());

        return redirect('/assistant/dashboard');
    }

    private function studentAssistantAdminWorkspaceAvailable(): bool
    {
        return app(ClinicWorkflowService::class)->studentAssistantWorkspaceAvailable();
    }

    private function idpUrl(string $path): ?string
    {
        $baseUrl = trim((string) config('services.idp.base_url', ''));
        if ($baseUrl === '') {
            return null;
        }

        if (Str::startsWith($path, ['http://', 'https://'])) {
            return $path;
        }

        return rtrim($baseUrl, '/') . '/' . ltrim($path, '/');
    }

    private function buildAuthorizeUrl(): ?string
    {
        $clientId = trim((string) config('services.idp.client_id', ''));
        if ($clientId === '') {
            return null;
        }

        $authorizePath = trim((string) config('services.idp.authorize_path', '/auth/authorize'));
        $authorizeUrl = $this->idpUrl($authorizePath);
        if ($authorizeUrl === null) {
            return null;
        }

        $query = [
            'client_id' => $clientId,
        ];

        $redirectUri = trim((string) config('services.idp.redirect_uri', ''));
        $includeRedirectUri = (bool) config('services.idp.authorize_include_redirect_uri', false);
        if ($includeRedirectUri && $redirectUri !== '') {
            $query['redirect_uri'] = $redirectUri;
        }

        $responseType = trim((string) config('services.idp.authorize_response_type', 'code'));
        if ($responseType !== '') {
            $query['response_type'] = $responseType;
        }

        $scope = trim((string) config('services.idp.authorize_scope', ''));
        if ($scope !== '') {
            $query['scope'] = $scope;
        }

        return $authorizeUrl . '?' . http_build_query($query);
    }

    private function useIdpPkce(): bool
    {
        return (bool) config('services.idp.use_pkce', true);
    }

    private function idpPkceChallengeMethod(): string
    {
        $method = strtoupper(trim((string) config('services.idp.pkce_challenge_method', 'S256')));
        if (!in_array($method, ['S256', 'PLAIN'], true)) {
            return 'S256';
        }

        return $method;
    }

    private function buildPkceVerifier(): string
    {
        return rtrim(strtr(base64_encode(random_bytes(64)), '+/', '-_'), '=');
    }

    private function buildPkceChallenge(string $verifier): string
    {
        if ($this->idpPkceChallengeMethod() === 'PLAIN') {
            return $verifier;
        }

        return rtrim(strtr(base64_encode(hash('sha256', $verifier, true)), '+/', '-_'), '=');
    }

    private function normalizeCookieSameSite(): string
    {
        $sameSite = strtolower(trim((string) config('services.idp.cookie_same_site', 'lax')));
        if (!in_array($sameSite, ['lax', 'strict', 'none'], true)) {
            return 'lax';
        }

        return $sameSite;
    }

    private function attachIdpCookies(RedirectResponse $response, ?string $accessToken, ?string $refreshToken): RedirectResponse
    {
        $secure = (bool) config('services.idp.cookie_secure', true);
        $sameSite = $this->normalizeCookieSameSite();

        $accessCookieName = trim((string) config('services.idp.access_cookie_name', 'access_token'));
        if ($accessCookieName !== '' && $accessToken !== null && $accessToken !== '') {
            $response->cookie(
                $accessCookieName,
                $accessToken,
                (int) config('services.idp.access_cookie_minutes', 60),
                '/',
                null,
                $secure,
                true,
                false,
                $sameSite
            );
        }

        $refreshCookieName = trim((string) config('services.idp.refresh_cookie_name', 'refresh_token'));
        if ($refreshCookieName !== '' && $refreshToken !== null && $refreshToken !== '') {
            $response->cookie(
                $refreshCookieName,
                $refreshToken,
                (int) config('services.idp.refresh_cookie_minutes', 10080),
                '/',
                null,
                $secure,
                true,
                false,
                $sameSite
            );
        }

        return $response;
    }

    private function clearIdpCookies(RedirectResponse $response): RedirectResponse
    {
        $secure = (bool) config('services.idp.cookie_secure', true);
        $sameSite = $this->normalizeCookieSameSite();

        foreach (['access_cookie_name', 'refresh_cookie_name'] as $cookieKey) {
            $cookieName = trim((string) config('services.idp.' . $cookieKey, ''));
            if ($cookieName === '') {
                continue;
            }

            $response->cookie(
                $cookieName,
                '',
                -60,
                '/',
                null,
                $secure,
                true,
                false,
                $sameSite
            );
        }

        return $response;
    }

    private function buildLogoutRedirectUrl(): string
    {
        $configuredUrl = trim((string) config('services.idp.logout_url', ''));
        if ($configuredUrl !== '') {
            return $configuredUrl;
        }

        $baseUrl = rtrim((string) config('services.idp.base_url', ''), '/');
        $logoutPath = '/' . ltrim((string) config('services.idp.logout_path', '/logout'), '/');

        return $baseUrl !== '' ? $baseUrl . $logoutPath : '/';
    }

    private function exchangeCodeForTokens(string $code): ?array
    {
        $tokenPath = trim((string) config('services.idp.token_path', '/auth/token'));
        $tokenUrl = $this->idpUrl($tokenPath);
        if ($tokenUrl === null) {
            return null;
        }

        $clientId = trim((string) config('services.idp.client_id', ''));
        $clientSecret = trim((string) config('services.idp.client_secret', ''));
        $payload = [
            'client_id' => $clientId,
            'client_secret' => $clientSecret,
            'code' => $code,
        ];

        $redirectUri = trim((string) config('services.idp.redirect_uri', ''));
        $includeRedirectUri = (bool) config('services.idp.token_include_redirect_uri', false);
        if ($includeRedirectUri && $redirectUri !== '') {
            $payload['redirect_uri'] = $redirectUri;
        }

        $pkceVerifier = session('idp_pkce_verifier');
        if ($this->useIdpPkce() && is_string($pkceVerifier) && trim($pkceVerifier) !== '') {
            $payload['code_verifier'] = trim($pkceVerifier);
        }

        $grantType = trim((string) config('services.idp.token_grant_type', 'authorization_code'));
        if ($grantType === '') {
            $grantType = 'authorization_code';
        }
        $payload['grant_type'] = $grantType;

        if ($clientId === '' || $clientSecret === '') {
            return null;
        }

        $configuredMethod = $this->idpTokenAuthMethod();
        $authMethods = array_values(array_unique(array_filter([
            $configuredMethod,
            'client_secret_basic',
            'client_secret_post',
            'json',
        ])));

        $payloadVariants = [$payload];
        if (isset($payload['redirect_uri']) && $payload['redirect_uri'] !== '') {
            $withoutRedirectUri = $payload;
            unset($withoutRedirectUri['redirect_uri']);
            $payloadVariants[] = $withoutRedirectUri;
        }

        foreach ($authMethods as $authMethod) {
            foreach ($payloadVariants as $attemptPayload) {
                $response = $this->performTokenExchangeRequest($tokenUrl, $attemptPayload, $clientId, $clientSecret, $authMethod);
                if ($response->successful() && is_array($response->json())) {
                    return $response->json();
                }

                Log::warning('IDP token exchange failed.', [
                    'token_url' => $tokenUrl,
                    'auth_method' => $authMethod,
                    'status' => $response->status(),
                    'body' => Str::limit((string) $response->body(), 1200),
                    'payload_flags' => [
                        'has_code' => isset($attemptPayload['code']) && $attemptPayload['code'] !== '',
                        'has_grant_type' => isset($attemptPayload['grant_type']) && $attemptPayload['grant_type'] !== '',
                        'has_redirect_uri' => isset($attemptPayload['redirect_uri']) && $attemptPayload['redirect_uri'] !== '',
                        'has_code_verifier' => isset($attemptPayload['code_verifier']) && $attemptPayload['code_verifier'] !== '',
                    ],
                ]);
            }
        }

        return null;
    }

    private function idpTokenAuthMethod(): string
    {
        $method = strtolower(trim((string) config('services.idp.token_auth_method', 'client_secret_post')));

        if (!in_array($method, ['client_secret_post', 'client_secret_basic', 'json'], true)) {
            return 'client_secret_post';
        }

        return $method;
    }

    private function performTokenExchangeRequest(
        string $tokenUrl,
        array $payload,
        string $clientId,
        string $clientSecret,
        string $authMethod
    ) {
        $request = Http::acceptJson()->timeout(20);

        if ($authMethod === 'client_secret_basic') {
            $basicPayload = $payload;
            unset($basicPayload['client_id']);
            unset($basicPayload['client_secret']);

            return $request
                ->withBasicAuth($clientId, $clientSecret)
                ->asForm()
                ->post($tokenUrl, $basicPayload);
        }

        if ($authMethod === 'json') {
            return $request->post($tokenUrl, $payload);
        }

        return $request
            ->asForm()
            ->post($tokenUrl, $payload);
    }

    private function hasIdentityFields(array $payload): bool
    {
        foreach ([
            'email',
            'role',
            'roles',
            'user_role',
            'student_number',
            'student_id',
            'reference_number',
            'reference_no',
            'referenceNo',
            'name',
            'full_name',
            'first_name',
            'firstname',
            'middle_name',
            'middlename',
            'last_name',
            'lastname',
            'user_id',
            'sub',
            'id',
            'user.email',
            'user.id',
            'user.sub',
            'user.reference_number',
            'user.firstname',
            'user.first_name',
            'data.user.email',
            'data.user.id',
            'data.user.sub',
            'data.user.reference_number',
            'application.reference_number',
            'admission.reference_number',
        ] as $key) {
            $value = data_get($payload, $key);
            if (is_string($value) && trim($value) !== '') {
                return true;
            }

            if (is_numeric($value)) {
                return true;
            }

            if (is_array($value) && !empty($value)) {
                return true;
            }
        }

        return false;
    }

    private function extractProfilePayload(array $payload): ?array
    {
        $candidates = [
            $payload,
            data_get($payload, 'data'),
            data_get($payload, 'user'),
            data_get($payload, 'profile'),
            data_get($payload, 'data.user'),
            data_get($payload, 'data.profile'),
        ];

        foreach ($candidates as $candidate) {
            if (!is_array($candidate)) {
                continue;
            }

            if ($this->hasIdentityFields($candidate)) {
                return $candidate;
            }
        }

        return null;
    }

    private function mergeIdpProfilePayloads(?array $tokenProfile, ?array $userInfoProfile): ?array
    {
        if ($tokenProfile === null) {
            return $userInfoProfile;
        }

        if ($userInfoProfile === null) {
            return $tokenProfile;
        }

        // The token callback can contain admission fields such as the reference
        // number while /me contains the fresher name and email. Keep both.
        return array_replace_recursive($tokenProfile, $userInfoProfile);
    }

    private function extractJwtClaims(?string $token): ?array
    {
        $token = trim((string) $token);
        $segments = explode('.', $token);

        if (count($segments) !== 3 || trim($segments[1]) === '') {
            return null;
        }

        $payload = strtr($segments[1], '-_', '+/');
        $payload .= str_repeat('=', (4 - strlen($payload) % 4) % 4);
        $decodedPayload = base64_decode($payload, true);

        if ($decodedPayload === false) {
            return null;
        }

        $claims = json_decode($decodedPayload, true);

        return is_array($claims) && $this->hasIdentityFields($claims)
            ? $claims
            : null;
    }

    private function fetchProfileFromIdp(string $accessToken): ?array
    {
        $profilePaths = (array) config('services.idp.profile_paths', []);
        $validateTokenPath = trim((string) config('services.idp.validate_token_path', ''));

        foreach ($profilePaths as $path) {
            $path = trim((string) $path);
            if ($path === '') {
                continue;
            }

            $url = $this->idpUrl($path);
            if ($url === null) {
                continue;
            }

            $response = Http::acceptJson()->timeout(20)->withToken($accessToken)->get($url);
            if (!$response->successful() || !is_array($response->json())) {
                continue;
            }

            $profile = $this->extractProfilePayload($response->json());
            if ($profile !== null) {
                return $profile;
            }
        }

        if ($validateTokenPath !== '') {
            $validateUrl = $this->idpUrl($validateTokenPath);
            if ($validateUrl !== null) {
                $response = Http::acceptJson()->timeout(20)->post($validateUrl, [
                    'token' => $accessToken,
                ]);

                if ($response->successful() && is_array($response->json())) {
                    $profile = $this->extractProfilePayload($response->json());
                    if ($profile !== null) {
                        return $profile;
                    }
                }
            }
        }

        return null;
    }

    private function extractRawRoles(array $profile): array
    {
        $sources = [
            data_get($profile, 'role'),
            data_get($profile, 'user_role'),
            data_get($profile, 'roles'),
            data_get($profile, 'authorities'),
            data_get($profile, 'data.role'),
            data_get($profile, 'data.roles'),
            data_get($profile, 'data.authorities'),
        ];

        $roles = [];
        foreach ($sources as $source) {
            if (is_string($source)) {
                foreach (explode(',', $source) as $role) {
                    $trimmed = trim($role);
                    if ($trimmed !== '') {
                        $roles[] = $trimmed;
                    }
                }
                continue;
            }

            if (is_array($source)) {
                foreach ($source as $role) {
                    if (is_string($role) && trim($role) !== '') {
                        $roles[] = trim($role);
                    }
                }
            }
        }

        return array_values(array_unique($roles));
    }

    private function configuredIdpRolePrefix(): string
    {
        $rolePrefix = strtolower(trim((string) config('services.idp.role_prefix', '')));
        if ($rolePrefix !== '' && !Str::endsWith($rolePrefix, ':')) {
            $rolePrefix .= ':';
        }

        return $rolePrefix;
    }

    private function parseIdpRoleToken(string $role): array
    {
        $normalized = strtolower(trim((string) $role));
        if ($normalized === '') {
            return ['', false];
        }

        $isScoped = false;
        $rolePrefix = $this->configuredIdpRolePrefix();
        if ($rolePrefix !== '' && Str::startsWith($normalized, $rolePrefix)) {
            $normalized = substr($normalized, strlen($rolePrefix));
            $isScoped = true;
        } elseif (Str::startsWith($normalized, 'cms:')) {
            $normalized = substr($normalized, 4);
            $isScoped = true;
        } elseif (Str::startsWith($normalized, 'ocms:')) {
            $normalized = substr($normalized, 5);
            $isScoped = true;
        }

        return [trim($normalized), $isScoped];
    }

    private function normalizeIdpRoleToken(string $role): string
    {
        [$normalized] = $this->parseIdpRoleToken($role);
        return $normalized;
    }

    private function isAssistantIdpRole(?string $role): bool
    {
        $normalized = $this->normalizeIdpRoleToken((string) $role);

        return in_array($normalized, ['student_assistant', 'assistant', 'studentassistant'], true);
    }

    private function defaultUserTypeForIdpRole(?string $idpRole, ?string $localRole = null): string
    {
        $normalizedLocalRole = User::normalizeRole((string) $localRole);
        $normalizedIdpRole = $this->normalizeIdpRoleToken((string) $idpRole);

        if ($normalizedLocalRole === User::ROLE_SUPERADMIN) {
            return 'Regular';
        }

        if ($normalizedLocalRole === User::ROLE_STUDENT) {
            return match ($normalizedIdpRole) {
                'faculty' => 'Faculty',
                'guest' => 'Guest',
                'applicant' => 'Applicant',
                'student' => 'Student',
                default => 'Regular',
            };
        }

        return $this->isAssistantIdpRole($idpRole) ? 'Assistant' : 'Regular';
    }

    private function mapSingleIdpRoleToLocal(string $role): ?string
    {
        $normalized = $this->normalizeIdpRoleToken($role);
        if ($normalized === '') {
            return null;
        }

        if (in_array($normalized, ['superadmin', 'super_admin'], true)) {
            return $this->superAdminRoleValue();
        }

        if (in_array($normalized, ['admin', 'student_assistant', 'assistant', 'studentassistant'], true)) {
            return $this->adminRoleValue();
        }

        if (in_array($normalized, ['student', 'faculty', 'guest', 'applicant'], true)) {
            return $this->studentRoleValue();
        }

        return null;
    }

    private function resolveLocalRoleFromTokens(array $normalizedRoles): ?string
    {
        $normalizedRoles = array_values(array_unique(array_filter(array_map('trim', $normalizedRoles))));
        if (empty($normalizedRoles)) {
            return null;
        }

        if (in_array('superadmin', $normalizedRoles, true) || in_array('super_admin', $normalizedRoles, true)) {
            return $this->superAdminRoleValue();
        }

        $hasAdmin = count(array_intersect($normalizedRoles, ['admin', 'student_assistant', 'assistant', 'studentassistant'])) > 0;
        if ($hasAdmin) {
            return $this->adminRoleValue();
        }

        if (count(array_intersect($normalizedRoles, ['student', 'faculty', 'guest', 'applicant'])) > 0) {
            return $this->studentRoleValue();
        }

        return null;
    }

    private function mapIdpRolesToLocal(array $roles, ?string $preferredRole = null): string
    {
        $normalizedRoles = [];

        foreach ($roles as $role) {
            [$normalized] = $this->parseIdpRoleToken((string) $role);
            if ($normalized === '') {
                continue;
            }

            $normalizedRoles[] = $normalized;
        }

        $mappedFromRoles = $this->resolveLocalRoleFromTokens($normalizedRoles);
        if ($mappedFromRoles !== null) {
            return $mappedFromRoles;
        }

        if ($preferredRole !== null) {
            $preferredMappedRole = $this->mapSingleIdpRoleToLocal($preferredRole);
            if ($preferredMappedRole !== null) {
                return $preferredMappedRole;
            }
        }

        return $this->studentRoleValue();
    }

    private function firstNonEmptyScalar(array $payload, array $keys): ?string
    {
        foreach ($keys as $key) {
            $value = data_get($payload, $key);
            if (is_string($value) && trim($value) !== '') {
                return trim($value);
            }

            if (is_numeric($value)) {
                return (string) $value;
            }
        }

        return null;
    }

    private function isUuid(?string $value): bool
    {
        return preg_match(
            '/^[0-9a-f]{8}-[0-9a-f]{4}-[1-5][0-9a-f]{3}-[89ab][0-9a-f]{3}-[0-9a-f]{12}$/i',
            trim((string) $value)
        ) === 1;
    }

    private function splitName(string $fullName): array
    {
        $clean = trim(preg_replace('/\s+/', ' ', $fullName));
        if ($clean === '') {
            return ['IDP', 'User', 'IDP User'];
        }

        $parts = explode(' ', $clean);
        if (count($parts) === 1) {
            return [$parts[0], 'User', trim($parts[0] . ' User')];
        }

        $firstName = array_shift($parts);
        $lastName = implode(' ', $parts);

        return [$firstName, $lastName, trim($firstName . ' ' . $lastName)];
    }

    private function normalizeStudentIdSeed(?string $seed): string
    {
        $value = trim((string) $seed);
        if ($value === '') {
            return 'idp-' . Str::lower(Str::random(10));
        }

        $value = preg_replace('/\s+/', '-', $value);
        $value = preg_replace('/[^A-Za-z0-9\-_]/', '', $value);

        if ($value === '') {
            return 'idp-' . Str::lower(Str::random(10));
        }

        return $value;
    }

    private function resolveUniqueStudentId(string $seed, ?int $ignoreUserId = null): string
    {
        $base = $this->normalizeStudentIdSeed($seed);
        $candidate = $base;
        $counter = 1;

        while (User::query()
            ->where('student_id', $candidate)
            ->when($ignoreUserId !== null, fn ($query) => $query->where('id', '!=', $ignoreUserId))
            ->exists()) {
            $candidate = $base . '-' . $counter;
            $counter++;
        }

        return $candidate;
    }

    private function resolveUniqueEmail(string $seed, ?int $ignoreUserId = null): string
    {
        $email = strtolower(trim($seed));
        if ($email === '' || !str_contains($email, '@')) {
            $email = 'idp-' . Str::lower(Str::random(12)) . '@idp.local';
        }

        $localPart = Str::before($email, '@');
        $domain = Str::after($email, '@');
        if ($domain === '') {
            $domain = 'idp.local';
        }

        $candidate = $localPart . '@' . $domain;
        $counter = 1;

        while (User::query()
            ->where('email', $candidate)
            ->when($ignoreUserId !== null, fn ($query) => $query->where('id', '!=', $ignoreUserId))
            ->exists()) {
            $candidate = $localPart . '+' . $counter . '@' . $domain;
            $counter++;
        }

        return $candidate;
    }

    private function upsertLocalUserFromIdpProfile(array $profile): User
    {
        $emailSeed = $this->firstNonEmptyScalar($profile, [
            'email',
            'mail',
            'username',
            'user.email',
            'data.user.email',
        ]) ?? '';
        $studentNumberSeed = $this->firstNonEmptyScalar($profile, [
            'student_number',
            'studentNo',
            'user.student_number',
            'user.studentNo',
            'data.user.student_number',
        ]) ?? '';
        $employeeNumberSeed = $this->firstNonEmptyScalar($profile, [
            'employee_number',
            'employee_no',
            'faculty_code',
            'user.employee_number',
            'user.employee_no',
            'user.faculty_code',
            'data.employee_number',
            'data.user.employee_number',
            'profile.employee_number',
            'profile.faculty_code',
        ]) ?? '';
        $referenceNumberSeed = $this->firstNonEmptyScalar($profile, [
            'reference_number',
            'reference_no',
            'referenceNo',
            'applicant_reference_number',
            'admission_reference_number',
            'user.reference_number',
            'user.reference_no',
            'user.referenceNo',
            'data.user.reference_number',
            'data.user.reference_no',
            'application.reference_number',
            'application.reference_no',
            'admission.reference_number',
            'admission.reference_no',
            'data.application.reference_number',
            'data.admission.reference_number',
        ]) ?? '';
        $studentIdSeed = $this->firstNonEmptyScalar($profile, [
            'student_id',
            'idp_user_id',
            'user_id',
            'sub',
            'id',
            'user.student_id',
            'user.idp_user_id',
            'user.sub',
            'user.id',
            'data.sub',
            'data.idp_user_id',
            'data.user.sub',
            'data.user.id',
        ]) ?? '';
        $idpSubjectSeed = $this->firstNonEmptyScalar($profile, [
            'idp_user_id',
            'sub',
            'id',
            'user.idp_user_id',
            'user.sub',
            'user.id',
            'data.idp_user_id',
            'data.sub',
            'data.user.idp_user_id',
            'data.user.sub',
            'data.user.id',
        ]) ?? '';
        $firstName = $this->firstNonEmptyScalar($profile, [
            'first_name',
            'firstname',
            'given_name',
            'user.first_name',
            'user.firstname',
            'data.user.first_name',
            'data.user.firstname',
        ]) ?? '';
        $middleName = $this->firstNonEmptyScalar($profile, [
            'middle_name',
            'middlename',
            'user.middle_name',
            'user.middlename',
            'data.user.middle_name',
            'data.user.middlename',
        ]) ?? '';
        $lastName = $this->firstNonEmptyScalar($profile, [
            'last_name',
            'lastname',
            'family_name',
            'user.last_name',
            'user.lastname',
            'data.user.last_name',
            'data.user.lastname',
        ]) ?? '';
        $displayName = $this->firstNonEmptyScalar($profile, [
            'name',
            'full_name',
            'display_name',
            'user.name',
            'user.full_name',
            'data.user.name',
            'data.user.full_name',
        ]) ?? '';

        if ($displayName === '' && ($firstName !== '' || $middleName !== '' || $lastName !== '')) {
            $displayName = trim(implode(' ', array_filter([$firstName, $middleName, $lastName])));
        }

        if ($displayName === '' && $emailSeed !== '' && str_contains($emailSeed, '@')) {
            $displayName = Str::title(str_replace(['.', '_', '-'], ' ', (string) Str::before($emailSeed, '@')));
        }

        [$splitFirstName, $splitLastName, $fullName] = $this->splitName($displayName);
        $firstName = $firstName !== '' ? $firstName : $splitFirstName;
        $lastName = $lastName !== '' ? $lastName : $splitLastName;

        $preferredRole = $this->firstNonEmptyScalar($profile, [
            'role',
            'user_role',
            'primary_role',
            'data.role',
            'user.role',
            'data.user.role',
        ]);
        $rawIdpRoles = $this->extractRawRoles($profile);
        $rawIdpRole = $preferredRole ?: ($rawIdpRoles[0] ?? 'student');
        $idpRole = $this->normalizeIdpRoleToken((string) $rawIdpRole);
        $idpRole = $idpRole !== '' ? $idpRole : 'student';
        $role = $this->mapIdpRolesToLocal($rawIdpRoles, $preferredRole);
        $forcedRole = $this->resolveForcedLocalRole($emailSeed);
        if ($forcedRole !== null) {
            $role = $forcedRole;
        }

        $adminHubRole = $this->resolveLocalRoleFromAdminHub($emailSeed, $idpSubjectSeed);
        if ($forcedRole === null && $adminHubRole !== null) {
            $role = $adminHubRole;
        }

        $normalizedEmailSeed = trim(strtolower($emailSeed));
        $existingUser = null;

        if ($normalizedEmailSeed !== '' && str_contains($normalizedEmailSeed, '@')) {
            $existingUser = User::query()->where('email', $normalizedEmailSeed)->first();
        }

        if (!$existingUser && $studentNumberSeed !== '') {
            $existingUser = User::query()->where('student_number', $studentNumberSeed)->first();
        }

        if (!$existingUser && $referenceNumberSeed !== '') {
            $existingUser = User::query()->where('reference_number', $referenceNumberSeed)->first();
        }

        if (!$existingUser && $studentIdSeed !== '') {
            $existingUser = User::query()->where('student_id', $studentIdSeed)->first();
        }

        if ($existingUser) {
            $existingAccountAccessRole = $this->resolveExistingAccountAccessRole($existingUser);
            if ($forcedRole === null && $existingAccountAccessRole !== null) {
                $role = $existingAccountAccessRole;
            }

            $existingUser->email = $this->resolveUniqueEmail(
                $normalizedEmailSeed !== '' ? $normalizedEmailSeed : (string) $existingUser->email,
                (int) $existingUser->id
            );
            $existingUser->first_name = $firstName !== '' ? $firstName : ($existingUser->first_name ?: 'IDP');
            $existingUser->middle_name = $middleName !== '' ? $middleName : null;
            $existingUser->last_name = $lastName !== '' ? $lastName : ($existingUser->last_name ?: 'User');
            $existingUser->name = trim(implode(' ', array_filter([
                $existingUser->first_name,
                $existingUser->middle_name,
                $existingUser->last_name,
            ])));
            $existingUser->user_role = $role;
            if (Schema::hasColumn('users', 'idp_role')) {
                $existingUser->idp_role = $idpRole;
            }
            if (Schema::hasColumn('users', 'employee_number') && $employeeNumberSeed !== '') {
                $existingUser->employee_number = $employeeNumberSeed;
            }

            if (
                $studentIdSeed !== ''
                && trim((string) $existingUser->student_id) !== $studentIdSeed
            ) {
                $existingUser->student_id = $this->resolveUniqueStudentId($studentIdSeed, (int) $existingUser->id);
            } elseif (trim((string) $existingUser->student_id) === '') {
                $existingUser->student_id = $this->resolveUniqueStudentId('idp-' . $existingUser->id, (int) $existingUser->id);
            }

            if ($studentNumberSeed !== '' && trim((string) $existingUser->student_number) === '') {
                $existingUser->student_number = $studentNumberSeed;
            }

            if ($referenceNumberSeed !== '') {
                $existingUser->reference_number = $referenceNumberSeed;
            }

            if (trim((string) $existingUser->password) === '') {
                $existingUser->password = Hash::make(Str::random(40));
            }

            if ($this->usersTableHasUserTypeColumn()) {
                $normalizedLocalRole = User::normalizeRole($role);
                $linkedAdmin = $this->findLinkedAdminProfile($existingUser);
                $currentUserType = strtolower(trim((string) ($existingUser->user_type ?? '')));
                $resolvedUserType = $this->defaultUserTypeForIdpRole($idpRole, $role);

                if ($normalizedLocalRole === User::ROLE_SUPERADMIN) {
                    $existingUser->user_type = 'Regular';
                } elseif (
                    $normalizedLocalRole === User::ROLE_STUDENT
                    && in_array($resolvedUserType, ['Faculty', 'Guest', 'Student', 'Applicant'], true)
                    && $existingUser->user_type !== $resolvedUserType
                ) {
                    $existingUser->user_type = $resolvedUserType;
                } elseif (empty($existingUser->user_type)) {
                    $existingUser->user_type = $resolvedUserType;
                } elseif (
                    in_array($currentUserType, ['assistant', 'student assistant', 'student_assistant'], true)
                    && !$this->isAssistantIdpRole($idpRole)
                    && !$linkedAdmin
                ) {
                    $existingUser->user_type = 'Regular';
                }
            }

            $existingUser->save();
            $this->syncAdminHubProfileFromIdp($existingUser, $idpSubjectSeed);
            $this->ensureDefaultClinicAdminProfile($existingUser, $role);
            return $existingUser;
        }

        $studentId = $this->resolveUniqueStudentId($studentIdSeed !== '' ? $studentIdSeed : ('idp-' . Str::lower(Str::random(10))));
        $email = $this->resolveUniqueEmail(
            $normalizedEmailSeed !== '' ? $normalizedEmailSeed : ($studentId . '@idp.local')
        );

        $newUserAttributes = [
            'student_id' => $studentId,
            'student_number' => $studentNumberSeed !== '' ? $studentNumberSeed : null,
            'reference_number' => $referenceNumberSeed !== '' ? $referenceNumberSeed : null,
            'first_name' => $firstName !== '' ? $firstName : 'IDP',
            'middle_name' => $middleName !== '' ? $middleName : null,
            'last_name' => $lastName !== '' ? $lastName : 'User',
            'name' => trim(implode(' ', array_filter([
                $firstName ?: 'IDP',
                $middleName,
                $lastName ?: 'User',
            ]))),
            'email' => $email,
            'user_role' => $role,
            'password' => Hash::make(Str::random(40)),
        ];
        if (Schema::hasColumn('users', 'idp_role')) {
            $newUserAttributes['idp_role'] = $idpRole;
        }
        if (Schema::hasColumn('users', 'employee_number')) {
            $newUserAttributes['employee_number'] = $employeeNumberSeed !== '' ? $employeeNumberSeed : null;
        }
        $user = User::create($newUserAttributes);

        if ($this->usersTableHasUserTypeColumn() && empty($user->user_type)) {
            $user->user_type = $this->defaultUserTypeForIdpRole($idpRole, $role);
            $user->save();
        }

        $this->syncAdminHubProfileFromIdp($user, $idpSubjectSeed);
        $this->ensureDefaultClinicAdminProfile($user, $role);

        return $user;
    }

    private function enrichUserWithPuptasData(User $user): void
    {
        try {
            $idpRole = strtolower(trim((string) ($user->idp_role ?? '')));
            $userType = strtolower(trim((string) ($user->user_type ?? '')));
            $isStudentRole = $idpRole === 'student' || ($idpRole === '' && $userType === 'student');

            // Only process students
            if (!$isStudentRole) {
                return;
            }

            // Skip if already enriched from GUISIS
            if ($user->is_student === true) {
                Log::info('PUPTAS enrichment skipped - already enriched from GUISIS', [
                    'user_id' => $user->id,
                ]);
                return;
            }

            Log::info('PUPTAS enrichment started', ['user_id' => $user->id]);

            $puptasService = app(\App\Services\PuptasWebhookService::class);
            $applicantData = null;
            $lookupResults = [];

            // Try lookup by student_id first
            $studentId = trim((string) ($user->student_id ?? ''));
            if ($studentId !== '') {
                $lookup = $puptasService->fetchApplicantByIdpUserIdDetailed($studentId);
                $lookupResults[] = $lookup;
                if (($lookup['outcome'] ?? '') === 'found' && is_array($lookup['data'] ?? null)) {
                    $applicantData = $lookup['data'];
                }
            }

            // Fallback: try lookup by reference_number if student_id lookup fails
            if (!is_array($applicantData) || empty($applicantData)) {
                $referenceNumber = trim((string) ($user->reference_number ?? ''));
                if ($referenceNumber !== '') {
                    $lookup = $puptasService->fetchApplicantByReferenceNumberDetailed($referenceNumber);
                    $lookupResults[] = $lookup;
                    if (($lookup['outcome'] ?? '') === 'found' && is_array($lookup['data'] ?? null)) {
                        $applicantData = $lookup['data'];
                    }
                }
            }

            if (!is_array($applicantData)) {
                $failedLookup = collect($lookupResults)->first(
                    fn ($result) => ($result['outcome'] ?? '') === 'unavailable'
                ) ?: (end($lookupResults) ?: []);

                Log::info('PUPTAS enrichment lookup finished without applicant data', [
                    'user_id' => $user->id,
                    'outcome' => $failedLookup['outcome'] ?? 'not_found',
                    'status' => $failedLookup['status'] ?? null,
                    'attempts' => $failedLookup['attempts'] ?? 0,
                    'message' => $failedLookup['message'] ?? 'No PUPTAS applicant data was returned.',
                ]);
                return;
            }

            $referenceNumber = trim((string) (
                data_get($applicantData, 'reference_number')
                ?: data_get($applicantData, 'reference_no')
                ?: data_get($applicantData, 'referenceNo')
                ?: data_get($applicantData, 'user.reference_number')
                ?: data_get($applicantData, 'application.reference_number')
                ?: data_get($applicantData, 'admission.reference_number')
            ));
            $firstName = trim((string) (
                data_get($applicantData, 'first_name')
                ?: data_get($applicantData, 'firstname')
                ?: data_get($applicantData, 'user.first_name')
                ?: data_get($applicantData, 'user.firstname')
            ));
            $lastName = trim((string) (
                data_get($applicantData, 'last_name')
                ?: data_get($applicantData, 'lastname')
                ?: data_get($applicantData, 'user.last_name')
                ?: data_get($applicantData, 'user.lastname')
            ));

            $shouldUpdate = false;
            if ($referenceNumber !== '' && trim((string) $user->reference_number) === '') {
                $user->reference_number = $referenceNumber;
                $shouldUpdate = true;
            }

            if ($firstName !== '' && trim((string) $user->first_name) === '') {
                $user->first_name = $firstName;
                $shouldUpdate = true;
            }

            if ($lastName !== '' && trim((string) $user->last_name) === '') {
                $user->last_name = $lastName;
                $shouldUpdate = true;
            }

            if ($user->is_student !== false) {
                $user->is_student = false;
                $shouldUpdate = true;
            }

            if ($shouldUpdate) {
                $user->save();

                // Get medical status from health_profile if exists
                $healthProfile = $user->healthProfile;
                $medicalStatus = $healthProfile?->puptas_sync_status ?? null;
                $syncedAt = $healthProfile?->puptas_synced_at ?? null;

                Log::info('User enriched with PUPTAS data during login', [
                    'user_id' => $user->id,
                    'reference_number' => $referenceNumber,
                    'first_name' => $firstName,
                    'last_name' => $lastName,
                    'is_student' => false,
                    'medical_status' => $medicalStatus,
                    'synced_at' => $syncedAt,
                ]);
            }
        } catch (\Throwable $exception) {
            Log::warning('Failed to enrich user with PUPTAS data', [
                'user_id' => $user->id,
                'error' => $exception->getMessage(),
            ]);
        }
    }

    private function enrichUserWithExternalStudentData(User $user): void
    {
        // These integrations are independent. A missing PUPTAS applicant must
        // not prevent GUISIS from supplying the student's academic record.
        $this->enrichUserWithPuptasData($user);
        $this->enrichUserWithFlssFacultyData($user);
        $this->enrichUserWithGuisisData($user);
    }

    private function enrichUserWithFlssFacultyData(User $user): void
    {
        if (User::normalizeRole((string) ($user->user_role ?? '')) !== User::ROLE_STUDENT) {
            return;
        }

        try {
            $searchTerms = array_values(array_unique(array_filter(array_map('trim', [
                (string) ($user->email ?? ''),
                (string) ($user->student_id ?? ''),
                (string) ($user->employee_number ?? ''),
            ]))));

            if ($searchTerms === []) {
                return;
            }

            $facultySyncService = app(\App\Services\FacultySyncService::class);
            $matchedFaculty = null;

            foreach ($searchTerms as $searchTerm) {
                $faculties = $facultySyncService->fetchFaculties($searchTerm);
                $matchedFaculty = collect($faculties)
                    ->filter(fn ($faculty) => is_array($faculty))
                    ->first(function (array $faculty) use ($user) {
                        $facultyEmail = strtolower(trim((string) ($faculty['email'] ?? '')));
                        $userEmail = strtolower(trim((string) ($user->email ?? '')));
                        $facultyIdentifier = strtolower(trim((string) (
                            $faculty['faculty_code']
                            ?? $faculty['faculty_id']
                            ?? $faculty['id']
                            ?? ''
                        )));
                        $userIdentifiers = array_filter(array_map(
                            fn ($value) => strtolower(trim((string) $value)),
                            [$user->student_id ?? '', $user->employee_number ?? '']
                        ));

                        return ($facultyEmail !== '' && $facultyEmail === $userEmail)
                            || ($facultyIdentifier !== '' && in_array($facultyIdentifier, $userIdentifiers, true));
                    });

                if (is_array($matchedFaculty)) {
                    break;
                }
            }

            if (!is_array($matchedFaculty)) {
                return;
            }

            $status = strtolower(trim((string) ($matchedFaculty['status'] ?? 'active')));
            if (in_array($status, ['0', 'false', 'inactive', 'disabled'], true)) {
                return;
            }

            $shouldUpdate = false;
            if ($this->usersTableHasUserTypeColumn() && trim((string) ($user->user_type ?? '')) !== 'Faculty') {
                $user->user_type = 'Faculty';
                $shouldUpdate = true;
            }

            $facultyIdentifier = trim((string) (
                $matchedFaculty['faculty_code']
                ?? $matchedFaculty['employee_number']
                ?? $matchedFaculty['employee_no']
                ?? ''
            ));
            if (
                Schema::hasColumn('users', 'employee_number')
                && $facultyIdentifier !== ''
                && trim((string) ($user->employee_number ?? '')) === ''
            ) {
                $user->employee_number = $facultyIdentifier;
                $shouldUpdate = true;
            }

            if ($shouldUpdate) {
                $user->save();
                Log::info('User identified as Faculty through PUPT-FLSS during login', [
                    'user_id' => $user->id,
                    'faculty_identifier' => $facultyIdentifier,
                ]);
            }
        } catch (\Throwable $exception) {
            Log::warning('Failed to enrich user with PUPT-FLSS faculty data', [
                'user_id' => $user->id,
                'error' => $exception->getMessage(),
            ]);
        }
    }

    private function enrichUserWithGuisisData(User $user): void
    {
        try {
            $idpRole = strtolower(trim((string) ($user->idp_role ?? '')));
            $userType = strtolower(trim((string) ($user->user_type ?? '')));
            if ($idpRole !== 'student' && !($idpRole === '' && $userType === 'student')) {
                return;
            }

            $email = trim((string) ($user->email ?? ''));
            if ($email === '') {
                return;
            }

            Log::info('GUISIS enrichment started', ['user_id' => $user->id, 'email' => $email]);

            $guisisService = app(\App\Services\GuisisApiService::class);
            $guisisResult = $guisisService->getStudentByEmailDetailed($email);

            if (!($guisisResult['ok'] ?? false) || !is_array($guisisResult['data'] ?? null)) {
                Log::info('GUISIS lookup not found', [
                    'user_id' => $user->id,
                    'status' => $guisisResult['status'] ?? 'unknown',
                ]);
                return;
            }

            $guisisPayload = $guisisResult['data'];
            $guisisData = is_array(data_get($guisisPayload, 'data'))
                ? data_get($guisisPayload, 'data')
                : $guisisPayload;

            $studentNumber = $this->firstNonEmptyScalar($guisisPayload, [
                'data.studentNumber',
                'data.student_number',
                'data.studentNo',
                'data.student_no',
                'studentNumber',
                'student_number',
                'studentNo',
                'student_no',
            ]) ?? '';

            $courseCode = $this->firstNonEmptyScalar($guisisPayload, [
                'data.course.code',
                'data.program.code',
                'data.course_code',
                'data.courseCode',
                'course.code',
                'program.code',
                'course_code',
                'courseCode',
            ]) ?? '';

            $courseName = $this->firstNonEmptyScalar($guisisPayload, [
                'data.course.name',
                'data.program.name',
                'data.course_name',
                'data.courseName',
                'course.name',
                'program.name',
                'course_name',
                'courseName',
            ]) ?? '';

            $course = trim(implode(' - ', array_unique(array_filter([$courseCode, $courseName]))));

            $year = $this->firstNonEmptyScalar($guisisPayload, [
                'data.yearLevel',
                'data.year_level',
                'data.year',
                'yearLevel',
                'year_level',
                'year',
            ]) ?? '';

            $shouldUpdate = false;
            if ($studentNumber !== '' && trim((string) $user->student_number) === '') {
                $user->student_number = $studentNumber;
                $shouldUpdate = true;
            }

            if ($course !== '' && trim((string) $user->course) !== $course) {
                $user->course = $course;
                $shouldUpdate = true;
            }

            if ($year !== '' && trim((string) $user->year) === '') {
                $user->year = $year;
                $shouldUpdate = true;
            }

            if (trim((string) $user->is_student) !== '1') {
                $user->is_student = true;
                $shouldUpdate = true;
            }

            if ($shouldUpdate) {
                $user->save();
                Log::info('User enriched with GUISIS data during login', [
                    'user_id' => $user->id,
                    'student_number' => $studentNumber,
                    'course' => $course,
                    'year' => $year,
                    'is_student' => true,
                ]);
            } else {
                Log::info('GUISIS enrichment completed without local changes', [
                    'user_id' => $user->id,
                    'student_number_found' => $studentNumber !== '',
                    'course_found' => $course !== '',
                    'year_found' => $year !== '',
                    'payload_keys' => array_keys($guisisData),
                ]);
            }
        } catch (\Throwable $exception) {
            Log::warning('Failed to enrich user with GUISIS data', [
                'user_id' => $user->id,
                'error' => $exception->getMessage(),
            ]);
        }
    }

    public function checkSession(Request $request)
    {
        // Already have a valid local session — send straight to the right dashboard.
        $authenticatedUser = $this->authenticatedUser();
        if ($authenticatedUser) {
            return redirect($this->resolveRedirectPathForUser($authenticatedUser));
        }

        // IDP auth disabled or client not configured — render landing page directly.
        if (!$this->useIdpAuth()) {
            return view('landing');
        }

        $authorizeUrl = $this->buildAuthorizeUrl();
        if ($authorizeUrl === null) {
            return view('landing');
        }

        // Collect all extra query parameters so we append them in a single pass.
        $extraParams = ['prompt' => 'none'];

        if ($this->useIdpPkce()) {
            $pkceVerifier = $this->buildPkceVerifier();
            $request->session()->put('idp_pkce_verifier', $pkceVerifier);
            $extraParams['code_challenge']        = $this->buildPkceChallenge($pkceVerifier);
            $extraParams['code_challenge_method'] = $this->idpPkceChallengeMethod();
        } else {
            $request->session()->forget('idp_pkce_verifier');
        }

        $separator   = str_contains($authorizeUrl, '?') ? '&' : '?';
        $authorizeUrl .= $separator . http_build_query($extraParams);

        Log::info('Initiating silent authentication via IdP.', [
            'authorize_url_base' => strtok($authorizeUrl, '?'),
        ]);

        return redirect()->away($authorizeUrl);
    }

    public function login(Request $request)
    {
        if (! $this->localLoginEnabled($request)) {
            return redirect('/login')->withErrors([
                'login' => 'Local login is not available on this site.',
            ]);
        }

        $request->validate([
            'email' => 'required|email',
            'password' => 'required',
        ]);

        $user = User::where('email', $request->email)->first();

        if (!$user) {
            $this->recordAuthEvent(
                $request,
                'Login Failed',
                'Login attempt failed because email is not registered.',
                null,
                404,
                'error'
            );

            return back()->withErrors([
                'email' => 'This email is not registered in our system.',
            ])->withInput();
        }

        $guard = $this->guardForUser($user);

        if (Auth::guard($guard)->attempt(['email' => $request->email, 'password' => $request->password])) {
            Auth::shouldUse($guard);
            $request->session()->regenerate();
            $request->session()->put('show_terms_modal', true);
            $request->session()->save();

            /** @var \App\Models\User $authenticatedUser */
            $authenticatedUser = Auth::guard($guard)->user();
            if (strtolower(trim((string) ($authenticatedUser->status ?? 'active'))) === 'inactive') {
                Auth::guard($guard)->logout();
                $request->session()->regenerateToken();

                $this->recordAuthEvent(
                    $request,
                    'Login Blocked',
                    'Login attempt blocked because the account is inactive.',
                    $authenticatedUser,
                    423,
                    'error'
                );

                return back()->withErrors([
                    'email' => 'This account is inactive. Please contact the clinic administrator.',
                ])->withInput();
            }

            $normalizedRole = User::normalizeRole($authenticatedUser->user_role);

            if ($normalizedRole !== strtolower((string) ($authenticatedUser->user_role ?? ''))) {
                $authenticatedUser->user_role = $normalizedRole;
                $authenticatedUser->save();
            }

            $this->enrichUserWithExternalStudentData($authenticatedUser);

            $this->recordAuthEvent($request, 'Login', 'User logged in successfully.', $authenticatedUser);

            $redirectPath = $this->resolveRedirectPathForUser($authenticatedUser);
            $this->queueHealthProfilePrompt($request, $authenticatedUser, $redirectPath);

            return redirect($redirectPath);
        }

        $this->recordAuthEvent(
            $request,
            'Login Failed',
            'Login attempt failed because password is incorrect.',
            $user,
            401,
            'error'
        );

        return back()->withErrors([
            'password' => 'Incorrect password. Please try again.',
        ])->withInput();
    }

    public function handleIdpCallback(Request $request): RedirectResponse
    {
        Log::info('IDP callback reached.', [
            'has_code' => $request->query->has('code'),
            'query_keys' => array_keys($request->query()),
            'path' => $request->path(),
        ]);

        if (!$this->useIdpAuth()) {
            Log::warning('IDP callback aborted because IDP auth is disabled.');
            return redirect('/login');
        }

        // Handle IdP error responses before checking for the auth code.
        // Soft errors from a prompt=none silent-auth attempt mean the IdP has no active
        // session for this user — gracefully fall back to the landing page so they can
        // log in manually. Hard errors are unexpected and go to the login error page.
        $idpError = trim((string) $request->query('error', ''));
        if ($idpError !== '') {
            $silentAuthErrors = [
                'login_required',
                'interaction_required',
                'consent_required',
                'account_selection_required',
            ];

            if (in_array($idpError, $silentAuthErrors, true)) {
                Log::info('Silent authentication found no active IdP session.', [
                    'error'             => $idpError,
                    'error_description' => $request->query('error_description'),
                ]);

                return redirect()->route('landing');
            }

            $errorDescription = trim((string) $request->query('error_description', ''));
            Log::warning('IDP callback returned an unexpected error.', [
                'error'             => $idpError,
                'error_description' => $errorDescription,
            ]);

            return redirect('/login?idp_error=1')->withErrors([
                'idp' => $errorDescription !== ''
                    ? "Sign-in failed: {$errorDescription}"
                    : 'The identity provider returned an error. Please try again.',
            ]);
        }

        $code = trim((string) $request->query('code', ''));
        if ($code === '') {
            Log::warning('IDP callback missing authorization code.');
            return redirect('/login?idp_error=1')->withErrors([
                'idp' => 'Missing authorization code from the identity provider.',
            ]);
        }

        Log::info('IDP callback received authorization code.', [
            'code_length' => strlen($code),
        ]);

        $tokenPayload = $this->exchangeCodeForTokens($code);
        $request->session()->forget('idp_pkce_verifier');
        if ($tokenPayload === null) {
            Log::warning('IDP callback token exchange returned no payload.');
            return redirect('/login?idp_error=1')->withErrors([
                'idp' => 'Token exchange failed. Please try signing in again.',
            ]);
        }

        $accessToken = trim((string) ($tokenPayload['access_token'] ?? ''));
        $refreshToken = trim((string) ($tokenPayload['refresh_token'] ?? ''));
        if ($accessToken === '') {
            Log::warning('IDP callback token payload did not contain an access token.', [
                'payload_keys' => array_keys($tokenPayload),
            ]);
            return redirect('/login?idp_error=1')->withErrors([
                'idp' => 'Identity provider did not return an access token.',
            ]);
        }

        Log::info('IDP callback token exchange succeeded.', [
            'has_refresh_token' => $refreshToken !== '',
            'payload_keys' => array_keys($tokenPayload),
        ]);

        $tokenProfile = $this->extractProfilePayload($tokenPayload);
        $jwtProfile = $this->mergeIdpProfilePayloads(
            $this->extractJwtClaims($tokenPayload['id_token'] ?? null),
            $this->extractJwtClaims($accessToken)
        );
        $userInfoProfile = $this->fetchProfileFromIdp($accessToken);
        $profile = $this->mergeIdpProfilePayloads(
            $this->mergeIdpProfilePayloads($tokenProfile, $jwtProfile),
            $userInfoProfile
        );

        if ($userInfoProfile === null && $tokenProfile !== null) {
            Log::warning('IDP profile fetch returned null; using token payload identity.');
        }

        if ($profile === null) {
            Log::warning('IDP callback could not resolve a profile payload.');
            return redirect('/login?idp_error=1')->withErrors([
                'idp' => 'Unable to retrieve your profile from the identity provider.',
            ]);
        }

        Log::info('IDP callback resolved profile payload.', [
            'profile_keys' => array_keys($profile),
            'has_reference_number' => $this->firstNonEmptyScalar($profile, [
                'reference_number',
                'user.reference_number',
                'application.reference_number',
                'admission.reference_number',
            ]) !== null,
            'has_idp_subject' => $this->firstNonEmptyScalar($profile, [
                'sub',
                'user.sub',
                'idp_user_id',
                'user.idp_user_id',
                'id',
                'user.id',
                'data.idp_user_id',
                'data.user.id',
            ]) !== null,
        ]);

        $user = $this->upsertLocalUserFromIdpProfile($profile);
        $user->user_role = User::normalizeRole($user->user_role);
        $user->save();

        $this->enrichUserWithExternalStudentData($user);

        if (strtolower(trim((string) ($user->status ?? 'active'))) === 'inactive') {
            Log::warning('IDP callback blocked inactive account.', [
                'user_id' => $user->id,
                'email' => $user->email,
            ]);

            return redirect('/login?idp_error=1')->withErrors([
                'idp' => 'This account is inactive. Please contact the clinic administrator.',
            ]);
        }

        Log::info('IDP callback upserted local user.', [
            'user_id' => $user->id,
            'user_role' => $user->user_role,
            'email' => $user->email,
        ]);

        $guard = $this->guardForUser($user);
        Auth::guard($guard)->login($user);
        Auth::shouldUse($guard);
        $request->session()->regenerate();
        $request->session()->put('idp_last_validated_at', now()->timestamp);
        $request->session()->put('show_terms_modal', true);
        // CRITICAL: Explicitly save the session BEFORE redirect to ensure the
        // laravel_session cookie is included in the response headers. Without this,
        // the browser never receives the session cookie during the redirect.
        $request->session()->save();
        $this->recordAuthEvent($request, 'Login', 'User logged in successfully via IDP authorization code flow.', $user);

        $redirectPath = $this->resolveRedirectPathForUser($user);
        $this->queueHealthProfilePrompt($request, $user, $redirectPath);
        $redirectResponse = redirect($redirectPath);
        return $this->attachIdpCookies($redirectResponse, $accessToken, $refreshToken);
    }

    public function acknowledgePostLoginTerms(Request $request)
    {
        $request->session()->forget('show_terms_modal');

        return response()->json(['status' => 'ok']);
    }

    public function logout(Request $request)
    {
        $guard = $this->activeGuard($request);
        $user = $guard ? Auth::guard($guard)->user() : $this->authenticatedUser();

        $clientId = config('services.idp.client_id');
        $idpLogoutUrl = config('services.idp.logout_url');
        $accessTokenCookie = config('services.idp.access_cookie_name', 'access_token');
        $token = $request->cookie($accessTokenCookie);

        try {
            if ($idpLogoutUrl && $token) {
                Http::withToken($token)->post($idpLogoutUrl, [
                    'client_id' => $clientId,
                ]);
            }
        } catch (\Exception $e) {
            Log::error('IDP Logout API call failed: ' . $e->getMessage());
        }

        if ($user instanceof User) {
            $this->recordAuthEvent($request, 'Logout', 'User logged out from the system.', $user);
        }

        Auth::guard($this->adminGuardName())->logout();
        Auth::guard($this->studentGuardName())->logout();

        if ($guard === 'web') {
            Auth::guard('web')->logout();
        }

        $request->session()->regenerateToken();

        $hasRemainingPortalSession = Auth::guard($this->adminGuardName())->check()
            || Auth::guard($this->studentGuardName())->check();

        $response = redirect('/')->with('status', 'Logged out successfully');

        if ($hasRemainingPortalSession) {
            return $response;
        }

        Auth::guard('web')->logout();
        return $this->clearIdpCookies($response);
    }

    public function handleIdpLogout(Request $request)
    {
        $user = $this->authenticatedUser();

        if ($user instanceof User) {
            $this->recordAuthEvent(
                $request,
                'Logout',
                'User logged out through the identity provider logout callback.',
                $user
            );
        }

        Auth::guard($this->adminGuardName())->logout();
        Auth::guard($this->studentGuardName())->logout();
        Auth::guard('web')->logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return $this->clearIdpCookies(
            redirect()->route('landing')->with('status', 'Logged out successfully')
        );
    }

    public function showLoginForm(Request $request)
    {
        $authenticatedUser = $this->authenticatedUser();
        if ($authenticatedUser) {
            return redirect($this->resolveRedirectPathForUser($authenticatedUser));
        }

        return view('login', [
            'localLoginEnabled' => $this->localLoginEnabled($request),
            'idpUnavailable' => $request->boolean('idp_error'),
        ]);
    }

    public function redirectToIdpPortal(Request $request): RedirectResponse
    {
        try {
            $authenticatedUser = $this->authenticatedUser();
            if ($authenticatedUser) {
                return redirect($this->resolveRedirectPathForUser($authenticatedUser));
            }
        } catch (\Throwable $exception) {
            Log::warning('Portal login route could not inspect the existing clinic session; continuing to IDP redirect.', [
                'message' => $exception->getMessage(),
            ]);
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

            return redirect('/login?idp_error=1')->withErrors([
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
    }

    public function handleWorkspaceGateway(Request $request)
    {
        Log::info('[WORKSPACE GATEWAY] Handling workspace gateway request');

        // STEP 1: Check standard Laravel session guards (full server-side access)
        $user = null;
        $guard = null;

        if (Auth::guard($this->adminGuardName())->check()) {
            $user = Auth::guard($this->adminGuardName())->user();
            $guard = $this->adminGuardName();
            Log::info('[WORKSPACE GATEWAY] Found user in admin guard');
        } elseif (Auth::guard($this->studentGuardName())->check()) {
            $user = Auth::guard($this->studentGuardName())->user();
            $guard = $this->studentGuardName();
            Log::info('[WORKSPACE GATEWAY] Found user in student guard');
        } elseif (Auth::guard('web')->check()) {
            $user = Auth::guard('web')->user();
            $guard = 'web';
            Log::info('[WORKSPACE GATEWAY] Found user in web guard');
        }

        // STEP 2: FALLBACK - If no session detected, check for IDP access token
        if (!$user instanceof User && (bool) config('services.idp.enabled', false)) {
            Log::info('[WORKSPACE GATEWAY] No session found in guards, checking IDP token cookie');
            $accessTokenCookieName = trim((string) config('services.idp.access_cookie_name', 'access_token'));
            $accessToken = $request->cookie($accessTokenCookieName);

            if ($accessToken && $accessToken !== '') {
                Log::info('[WORKSPACE GATEWAY] Found IDP access token in cookie');
                // Validate token by fetching user profile from IDP
                $idpProfile = $this->fetchProfileFromIdp($accessToken);

                if ($idpProfile !== null) {
                    $email = trim((string) ($idpProfile['email'] ?? ''));

                    // Look up user in local database by email
                    if ($email !== '') {
                        $user = User::query()->where('email', $email)->first();

                        if ($user instanceof User) {
                            // Determine which guard based on user role
                            $guard = $this->guardForUser($user);
                            Log::info('[WORKSPACE GATEWAY] Found user via IDP token', [
                                'user_id' => $user->id,
                                'guard' => $guard,
                            ]);
                        }
                    }
                }
            }
        }

        // STEP 3: If still no user found, redirect to landing page with auth_error flag
        if (!$user instanceof User) {
            Log::warning('[WORKSPACE GATEWAY] No authenticated user found - redirecting to landing with auth_error');
            return redirect('/?auth_error=true');
        }

        // STEP 4: Route based on user role
        $normalizedRole = User::normalizeRole((string) ($user->user_role ?? ''));
        $isStudentAssistant = $this->isStudentAssistantAccount($user);

        Log::info('[WORKSPACE GATEWAY] Routing user', [
            'user_id' => $user->id,
            'role' => $normalizedRole,
            'is_sa' => $isStudentAssistant,
        ]);

        if ($normalizedRole === User::ROLE_SUPERADMIN) {
            Log::info('[WORKSPACE GATEWAY] Redirecting Superadmin to /admin/dashboard');
            return redirect('/admin/dashboard');
        }

        if ($normalizedRole === User::ROLE_ADMIN) {
            if ($isStudentAssistant) {
                Log::info('[WORKSPACE GATEWAY] Redirecting Student Assistant to landing with workspace selector');
                return redirect('/?workspace=sa');
            }

            Log::info('[WORKSPACE GATEWAY] Redirecting Admin Regular or unassigned Admin to student-side landing');
            return redirect('/?workspace=student');
        }

        // Student - redirect to student side
        if ($normalizedRole === User::ROLE_STUDENT) {
            Log::info('[WORKSPACE GATEWAY] Redirecting Student to landing with student indicator');
            return redirect('/?workspace=student');
        }

        Log::info('[WORKSPACE GATEWAY] Unknown role - redirecting to student-side landing');
        return redirect('/?workspace=student');
    }

    public function apiCheckSession(Request $request)
    {
        $user = null;

        // STEP 1: Check standard Laravel session guards
        // These work when laravel_session cookie is present
        if (Auth::guard($this->adminGuardName())->check()) {
            $user = Auth::guard($this->adminGuardName())->user();
        } elseif (Auth::guard($this->studentGuardName())->check()) {
            $user = Auth::guard($this->studentGuardName())->user();
        } elseif (Auth::guard('web')->check()) {
            $user = Auth::guard('web')->user();
        }

        // STEP 2: FALLBACK - If no session detected, check for IDP access token
        // This handles cases where the laravel_session cookie isn't sent (browser cookie isolation)
        if (!$user instanceof User && (bool) config('services.idp.enabled', false)) {
            $accessTokenCookieName = trim((string) config('services.idp.access_cookie_name', 'access_token'));
            $accessToken = $request->cookie($accessTokenCookieName);

            if ($accessToken && $accessToken !== '') {
                // Validate token by fetching user profile from IDP
                $idpProfile = $this->fetchProfileFromIdp($accessToken);

                if ($idpProfile !== null) {
                    // Extract user email from profile
                    $email = trim((string) ($idpProfile['email'] ?? ''));

                    // Look up user in local database by email
                    if ($email !== '') {
                        $user = User::query()->where('email', $email)->first();
                    }

                    // If user found, log them into the proper guard on-the-fly
                    if ($user instanceof User) {
                        // Determine which guard based on user role
                        $guard = $this->guardForUser($user);
                        Auth::guard($guard)->login($user);
                        Auth::shouldUse($guard);
                    }
                }
            }
        }

        // STEP 3: Return response
        if (!$user instanceof User) {
            return response()->json([
                'authenticated' => false,
                'role' => null,
                'isStudentAssistant' => false,
            ]);
        }

        // User is authenticated - determine role and assistant status
        $userRole = User::normalizeRole((string) ($user->user_role ?? ''));
        $isStudentAssistant = $this->isStudentAssistantAccount($user);

        return response()->json([
            'authenticated' => true,
            'role' => $userRole,
            'isStudentAssistant' => $isStudentAssistant,
        ]);
    }

    public function apiGetRedirectPath(Request $request)
    {
        $user = null;
        $activeGuard = null;

        // STEP 1: Check all authentication guards (local session)
        if (Auth::guard($this->adminGuardName())->check()) {
            $user = Auth::guard($this->adminGuardName())->user();
            $activeGuard = $this->adminGuardName();
        } elseif (Auth::guard($this->studentGuardName())->check()) {
            $user = Auth::guard($this->studentGuardName())->user();
            $activeGuard = $this->studentGuardName();
        } elseif (Auth::guard('web')->check()) {
            $user = Auth::guard('web')->user();
            $activeGuard = 'web';
        }

        // STEP 2: If no local session, check for IDP access token cookie
        if (!$user instanceof User && (bool) config('services.idp.enabled', false)) {
            $accessTokenCookieName = trim((string) config('services.idp.access_cookie_name', 'access_token'));
            $accessToken = $request->cookie($accessTokenCookieName);

            if ($accessToken && $accessToken !== '') {
                // Try to validate/fetch profile using the IDP access token
                $idpProfile = $this->fetchProfileFromIdp($accessToken);

                if ($idpProfile !== null) {
                    // We have a valid IDP token with profile info
                    $email = trim((string) ($idpProfile['email'] ?? ''));
                    $idpUserId = trim((string) ($idpProfile['id'] ?? $idpProfile['user_id'] ?? ''));

                    if ($email !== '') {
                        $user = User::query()->where('email', $email)->first();
                    } elseif ($idpUserId !== '') {
                        $user = User::query()
                            ->where('student_id', $idpUserId)
                            ->orWhere('email', 'like', '%' . $idpUserId . '%')
                            ->first();
                    }

                    if ($user instanceof User) {
                        $activeGuard = $this->guardForUser($user);
                    }
                }
            }
        }

        // STEP 3: Return response
        if (!$user instanceof User) {
            return response()->json([
                'redirectPath' => null,
                'error' => 'Not authenticated',
                'message' => 'No active session found (local or IDP)',
            ], 401);
        }

        $redirectPath = $this->resolveRedirectPathForUser($user);

        return response()->json([
            'redirectPath' => $redirectPath,
            'userRole' => $user->user_role,
            'activeGuard' => $activeGuard,
            'isStudentAssistant' => $this->isStudentAssistantAccount($user),
        ]);
    }
}
