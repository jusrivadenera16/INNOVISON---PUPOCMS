<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\ActivityLog;
use App\Models\SystemSetting;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;
use Illuminate\Support\Str;

class EmergencyAuthController extends Controller
{
    public function showLoginForm(): RedirectResponse|View
    {
        if (Auth::guard('admin')->check()) {
            return redirect()->route('admin.dashboard');
        }

        return view('auth.emergency-login');
    }

    public function login(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'email' => ['required', 'email', 'max:255'],
            'password' => ['required', 'string', 'max:255'],
        ]);

        $email = strtolower(trim((string) $validated['email']));
        $password = (string) $validated['password'];
        $guard = Auth::guard('admin');
        $emergencyConfig = $this->emergencyConfig();
        $bootstrapAccounts = $emergencyConfig['accounts'] ?? [];

        if (empty($bootstrapAccounts)) {
            $this->logAttempt($request, null, 'Emergency login failed because bootstrap credentials are not configured.', 503);

            throw ValidationException::withMessages([
                'email' => 'Emergency login is not configured on this server.',
            ]);
        }

        $matchedAccount = null;
        foreach ($bootstrapAccounts as $account) {
            $accountEmail = strtolower(trim((string) ($account['email'] ?? '')));
            $accountPassword = (string) ($account['password'] ?? '');
            $accountPasswordHash = trim((string) ($account['password_hash'] ?? ''));
            $passwordMatches = $accountPasswordHash !== ''
                ? Hash::check($password, $accountPasswordHash)
                : ($accountPassword !== '' && hash_equals($accountPassword, $password));

            if ($email === $accountEmail && $passwordMatches) {
                $matchedAccount = $account;
                break;
            }
        }

        if (!$matchedAccount) {
            $this->logAttempt($request, null, 'Emergency login failed because the bootstrap credentials did not match.', 401);

            throw ValidationException::withMessages([
                'email' => 'Invalid emergency credentials.',
            ]);
        }

        $bootstrapEmail = strtolower(trim((string) ($matchedAccount['email'] ?? '')));
        $bootstrapPassword = (string) ($matchedAccount['password'] ?? '');
        $bootstrapPasswordHash = trim((string) ($matchedAccount['password_hash'] ?? ''));
        $bootstrapRole = User::normalizeRole((string) ($matchedAccount['role'] ?? User::ROLE_ADMIN));

        $user = User::query()->where('email', $bootstrapEmail)->first();
        $newUser = false;
        if (!$user) {
            $user = new User();
            $newUser = true;
        }

        $user->email = $bootstrapEmail;
        $user->first_name = $user->first_name ?: 'Emergency';
        $user->last_name = $user->last_name ?: 'Admin';
        $user->name = trim(($user->first_name ?? 'Emergency') . ' ' . ($user->last_name ?? 'Admin'));
        $user->user_role = $bootstrapRole;
        $user->status = 'active';
        $user->password = $bootstrapPasswordHash !== ''
            ? $bootstrapPasswordHash
            : Hash::make($bootstrapPassword);

        if (Schema::hasColumn('users', 'student_id') && empty($user->student_id)) {
            $user->student_id = 'emergency-admin';
        }
        if (Schema::hasColumn('users', 'student_number') && empty($user->student_number)) {
            $user->student_number = 'emergency-admin';
        }
        if (Schema::hasColumn('users', 'user_type')) {
            $user->user_type = $bootstrapRole === User::ROLE_ADMIN ? 'Assistant' : 'Regular';
        }

        $user->save();

        $request->session()->regenerate();
        Auth::shouldUse('admin');
        $guard->login($user, false);

        $normalizedRole = User::normalizeRole((string) ($user->user_role ?? ''));
        if (!in_array($normalizedRole, [User::ROLE_ADMIN, User::ROLE_SUPERADMIN], true)) {
            $guard->logout();
            $request->session()->invalidate();
            $request->session()->regenerateToken();

            $this->logAttempt($request, $user, 'Emergency login blocked because the account is not an admin or nurse account.', 403);

            throw ValidationException::withMessages([
                'email' => 'This backdoor is limited to clinic admin and nurse accounts.',
            ]);
        }

        if (strtolower(trim((string) ($user->status ?? 'active'))) === 'inactive') {
            $guard->logout();
            $request->session()->invalidate();
            $request->session()->regenerateToken();

            $this->logAttempt($request, $user, 'Emergency login blocked because the account is inactive.', 423);

            throw ValidationException::withMessages([
                'email' => 'This account is inactive.',
            ]);
        }

        $this->logAttempt(
            $request,
            $user,
            $newUser
                ? 'Emergency login succeeded and bootstrap account was created.'
                : 'Emergency login succeeded.',
            200
        );

        return redirect()
            ->route($normalizedRole === User::ROLE_SUPERADMIN ? 'admin.dashboard' : 'assistant.dashboard')
            ->with('success', 'Emergency backup login successful.');
    }

    private function logAttempt(Request $request, ?User $user, string $description, int $statusCode): void
    {
        try {
            ActivityLog::create([
                'user_id' => $user?->id,
                'user_name' => $user?->name ?? $request->input('email'),
                'user_role' => $user ? strtolower((string) ($user->user_role ?? '')) : null,
                'action' => 'Emergency Login',
                'module' => 'Authentication',
                'event_type' => $statusCode === 200 ? 'auth' : 'error',
                'description' => $description,
                'route_name' => 'system-admin.emergency-login',
                'http_method' => strtoupper($request->method()),
                'request_path' => '/' . ltrim($request->path(), '/'),
                'status_code' => $statusCode,
                'subject_type' => 'user',
                'subject_id' => $user?->id ? (string) $user->id : null,
                'metadata' => [
                    'email' => $request->input('email'),
                    'emergency_login' => true,
                ],
                'ip_address' => $request->ip(),
                'user_agent' => substr((string) $request->userAgent(), 0, 255),
            ]);
        } catch (\Throwable $exception) {
            Log::warning('Emergency login audit log could not be written.', [
                'message' => $exception->getMessage(),
            ]);
        }
    }

    private function emergencyConfig(): array
    {
        $configEnabled = true;
        $configEmail = (string) config('services.emergency.email', '');
        $configPassword = (string) config('services.emergency.password', '');
        $configPasswordHash = trim((string) config('services.emergency.password_hash', ''));
        $configRole = (string) config('services.emergency.role', User::ROLE_ADMIN);
        $accounts = [];
        if ($configEmail !== '' && ($configPassword !== '' || $configPasswordHash !== '')) {
            $accounts[] = [
                'email' => $configEmail,
                'password' => $configPassword,
                'password_hash' => $configPasswordHash,
                'role' => $configRole,
            ];
        }

        return [
            'enabled' => $configEnabled,
            'accounts' => array_merge($accounts, $this->emergencyAdditionalAccounts()),
        ];
    }

    private function emergencyAdditionalAccounts(): array
    {
        $encoded = trim((string) env('EMERGENCY_ADMIN_ADDITIONAL_ACCOUNTS', ''));
        if ($encoded === '') {
            return [];
        }

        $decoded = base64_decode($encoded, true);
        if ($decoded === false) {
            return [];
        }

        $accounts = json_decode($decoded, true);
        return is_array($accounts) ? array_values(array_filter($accounts, fn ($account) => is_array($account))) : [];
    }
}
