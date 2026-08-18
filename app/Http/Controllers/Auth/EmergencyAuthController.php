<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\ActivityLog;
use App\Models\User;
use App\Services\EmergencyTwoFactorService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Facades\Schema;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class EmergencyAuthController extends Controller
{
    private const ENROLLMENT_BACKUP_CODES_SESSION_KEY = 'emergency_mfa_enrollment_backup_codes';
    private const ENROLLMENT_SECRET_SESSION_KEY = 'emergency_mfa_enrollment_secret';
    private const MFA_METHOD_SESSION_KEY = 'emergency_mfa_method';
    private const MFA_PENDING_SESSION_KEY = 'emergency_mfa_pending_account';
    private const MFA_PENDING_TTL_SECONDS = 300;
    private const LOGIN_MAX_FAILED_ATTEMPTS = 5;
    private const LOGIN_RATE_LIMIT_DECAY_SECONDS = 60;

    public function showLoginForm(): RedirectResponse|View
    {
        if (Auth::guard('admin')->check()) {
            return redirect()->route('admin.dashboard');
        }

        return view('auth.emergency-login', ['step' => 'credentials']);
    }

    public function login(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'email' => ['required', 'email', 'max:255'],
            'password' => ['required', 'string', 'max:255'],
        ]);

        $email = strtolower(trim((string) $validated['email']));
        $rateLimitKey = $this->loginRateLimitKey($request, $email);
        if (RateLimiter::tooManyAttempts($rateLimitKey, self::LOGIN_MAX_FAILED_ATTEMPTS)) {
            $this->logAttempt($request, null, 'Emergency login was rate limited after repeated failed credentials.', 429);

            throw ValidationException::withMessages([
                'email' => 'Too many unsuccessful sign-in attempts. Wait one minute and try again.',
            ]);
        }

        $account = $this->matchingAccount($email, (string) $validated['password']);

        if (!$account) {
            RateLimiter::hit($rateLimitKey, self::LOGIN_RATE_LIMIT_DECAY_SECONDS);
            $this->logAttempt($request, null, 'Emergency login failed because the bootstrap credentials did not match.', 401);

            throw ValidationException::withMessages([
                'email' => 'Invalid emergency credentials.',
            ]);
        }

        RateLimiter::clear($rateLimitKey);
        $request->session()->regenerate();
        $this->clearMfaState($request);
        $request->session()->put(self::MFA_PENDING_SESSION_KEY, [
            'email' => $email,
            'verified_at' => now()->timestamp,
        ]);

        $this->logAttempt($request, null, 'Emergency password accepted. Multi-factor authentication is required before access is granted.', 202);

        return $this->twoFactor()->hasAuthenticator($email)
            ? redirect()->route('system-admin.emergency-login.method')
            : redirect()->route('system-admin.emergency-login.enroll');
    }

    public function showEnrollment(Request $request): RedirectResponse|View
    {
        $account = $this->pendingAccount($request);
        if (!$account) {
            return $this->restartMfaFlow($request);
        }

        $email = (string) $account['email'];
        if ($this->twoFactor()->hasAuthenticator($email)) {
            return redirect()->route('system-admin.emergency-login.method');
        }

        $secret = (string) $request->session()->get(self::ENROLLMENT_SECRET_SESSION_KEY, '');
        if ($secret === '') {
            $secret = $this->twoFactor()->generateSecret();
            $request->session()->put(self::ENROLLMENT_SECRET_SESSION_KEY, $secret);
        }

        return view('auth.emergency-login', [
            'step' => 'enroll',
            'email' => $email,
            'qrCodeSvg' => $this->twoFactor()->qrCodeSvg($this->twoFactor()->provisioningUri($email, $secret)),
        ]);
    }

    public function continueEnrollment(Request $request): RedirectResponse
    {
        $account = $this->pendingAccount($request);
        if (!$account) {
            return $this->restartMfaFlow($request);
        }

        if ($this->twoFactor()->hasAuthenticator((string) $account['email'])) {
            return redirect()->route('system-admin.emergency-login.method');
        }

        if ((string) $request->session()->get(self::ENROLLMENT_SECRET_SESSION_KEY, '') === '') {
            return redirect()
                ->route('system-admin.emergency-login.enroll')
                ->withErrors(['totp_code' => 'Start the authenticator app enrollment again.']);
        }

        return redirect()->route('system-admin.emergency-login.enroll.backup-codes');
    }

    public function showBackupCodes(Request $request): RedirectResponse|View
    {
        $account = $this->pendingAccount($request);
        if (!$account) {
            return $this->restartMfaFlow($request);
        }

        if ($this->twoFactor()->hasAuthenticator((string) $account['email'])) {
            return redirect()->route('system-admin.emergency-login.method');
        }

        if ((string) $request->session()->get(self::ENROLLMENT_SECRET_SESSION_KEY, '') === '') {
            return redirect()->route('system-admin.emergency-login.enroll');
        }

        $backupCodes = $request->session()->get(self::ENROLLMENT_BACKUP_CODES_SESSION_KEY, []);
        if (!is_array($backupCodes) || count($backupCodes) !== 10) {
            $backupCodes = $this->twoFactor()->generateBackupCodes();
            $request->session()->put(self::ENROLLMENT_BACKUP_CODES_SESSION_KEY, $backupCodes);
        }

        return view('auth.emergency-login', [
            'step' => 'backup-codes',
            'backupCodes' => $backupCodes,
        ]);
    }

    public function confirmBackupCodes(Request $request): RedirectResponse
    {
        $account = $this->pendingAccount($request);
        if (!$account) {
            return $this->restartMfaFlow($request);
        }

        if ($this->twoFactor()->hasAuthenticator((string) $account['email'])) {
            return redirect()->route('system-admin.emergency-login.method');
        }

        $backupCodes = $request->session()->get(self::ENROLLMENT_BACKUP_CODES_SESSION_KEY, []);
        if (!is_array($backupCodes) || count($backupCodes) !== 10) {
            return redirect()
                ->route('system-admin.emergency-login.enroll.backup-codes')
                ->withErrors(['backup_codes' => 'Open the backup codes page again and copy the new codes before continuing.']);
        }

        return redirect()->route('system-admin.emergency-login.enroll.verify');
    }

    public function showEnrollmentVerification(Request $request): RedirectResponse|View
    {
        $account = $this->pendingAccount($request);
        if (!$account) {
            return $this->restartMfaFlow($request);
        }

        if ($this->twoFactor()->hasAuthenticator((string) $account['email'])) {
            return redirect()->route('system-admin.emergency-login.method');
        }

        $secret = (string) $request->session()->get(self::ENROLLMENT_SECRET_SESSION_KEY, '');
        $backupCodes = $request->session()->get(self::ENROLLMENT_BACKUP_CODES_SESSION_KEY, []);
        if ($secret === '' || !is_array($backupCodes) || count($backupCodes) !== 10) {
            return redirect()->route('system-admin.emergency-login.enroll');
        }

        return view('auth.emergency-login', ['step' => 'enroll-verify']);
    }

    public function confirmEnrollment(Request $request): RedirectResponse
    {
        $account = $this->pendingAccount($request);
        if (!$account) {
            return $this->restartMfaFlow($request);
        }

        $email = (string) $account['email'];
        if ($this->twoFactor()->hasAuthenticator($email)) {
            return redirect()->route('system-admin.emergency-login.method');
        }

        $secret = (string) $request->session()->get(self::ENROLLMENT_SECRET_SESSION_KEY, '');
        $backupCodes = $request->session()->get(self::ENROLLMENT_BACKUP_CODES_SESSION_KEY, []);
        if ($secret === '' || !is_array($backupCodes) || count($backupCodes) !== 10) {
            return redirect()
                ->route('system-admin.emergency-login.enroll')
                ->withErrors(['totp_code' => 'Start the authenticator app enrollment again.']);
        }

        $validated = $request->validate([
            'totp_code' => ['required', 'digits:6'],
        ]);

        if (!$this->twoFactor()->verifyEnrollmentCode($secret, (string) $validated['totp_code'])) {
            $this->logAttempt($request, null, 'Emergency authenticator app enrollment failed because the code was invalid.', 401);

            return back()->withErrors([
                'totp_code' => 'The authenticator code is invalid. Wait for a new code and try again.',
            ]);
        }

        try {
            $this->twoFactor()->saveEnrollment($email, $secret, $backupCodes);
        } catch (\Throwable) {
            Log::error('Emergency authenticator app enrollment could not be saved.', ['email' => $email]);

            return back()->withErrors([
                'totp_code' => 'The authenticator setup could not be saved. Please try again.',
            ]);
        }

        $request->session()->forget(self::ENROLLMENT_SECRET_SESSION_KEY);
        $request->session()->forget(self::ENROLLMENT_BACKUP_CODES_SESSION_KEY);
        $this->logAttempt($request, null, 'Emergency authenticator app enrollment completed with one-time backup codes.', 200);

        return $this->completeLogin($request, $account);
    }

    public function showMethodChoice(Request $request): RedirectResponse|View
    {
        $account = $this->pendingAccount($request);
        if (!$account) {
            return $this->restartMfaFlow($request);
        }

        $email = (string) $account['email'];
        if (!$this->twoFactor()->hasAuthenticator($email)) {
            return redirect()->route('system-admin.emergency-login.enroll');
        }

        return view('auth.emergency-login', [
            'step' => 'method',
            'email' => $email,
            'emailOtpRecipient' => $this->twoFactor()->maskedRecipient($this->twoFactor()->emailOtpRecipient($email)),
            'hasBackupCodes' => $this->twoFactor()->hasBackupCodes($email),
        ]);
    }

    public function chooseMethod(Request $request): RedirectResponse
    {
        $account = $this->pendingAccount($request);
        if (!$account) {
            return $this->restartMfaFlow($request);
        }

        $email = (string) $account['email'];
        if (!$this->twoFactor()->hasAuthenticator($email)) {
            return redirect()->route('system-admin.emergency-login.enroll');
        }

        $validated = $request->validate([
            'mfa_method' => ['required', 'in:totp,email,backup'],
        ]);

        $method = (string) $validated['mfa_method'];
        if ($method === 'backup' && !$this->twoFactor()->hasBackupCodes($email)) {
            return back()->withErrors(['mfa_method' => 'No unused backup codes are available for this emergency account.']);
        }

        if ($method === 'email') {
            $result = $this->twoFactor()->sendEmailOtp($email);
            if (!$result['sent']) {
                $message = $result['reason'] === 'cooldown'
                    ? 'A code was recently sent. Wait a moment before requesting another one.'
                    : 'The email code could not be delivered. Choose an authenticator app or check the mail configuration.';

                return back()->withErrors(['mfa_method' => $message]);
            }

            $request->session()->put('emergency_mfa_email_recipient', $result['recipient']);
            $request->session()->put('emergency_mfa_email_otp_sent_at', now()->timestamp);
            $this->logAttempt($request, null, 'Emergency Email OTP was sent for multi-factor verification.', 200);
        }

        $request->session()->put(self::MFA_METHOD_SESSION_KEY, $method);

        return redirect()->route('system-admin.emergency-login.verify');
    }

    public function showVerification(Request $request): RedirectResponse|View
    {
        $account = $this->pendingAccount($request);
        if (!$account) {
            return $this->restartMfaFlow($request);
        }

        $email = (string) $account['email'];
        if (!$this->twoFactor()->hasAuthenticator($email)) {
            return redirect()->route('system-admin.emergency-login.enroll');
        }

        $method = (string) $request->session()->get(self::MFA_METHOD_SESSION_KEY, '');
        if (!in_array($method, ['totp', 'email', 'backup'], true)) {
            return redirect()->route('system-admin.emergency-login.method');
        }

        if ($method === 'backup' && !$this->twoFactor()->hasBackupCodes($email)) {
            return redirect()->route('system-admin.emergency-login.method');
        }

        return view('auth.emergency-login', [
            'step' => 'verify',
            'email' => $email,
            'mfaMethod' => $method,
            'emailOtpRecipient' => (string) $request->session()->get('emergency_mfa_email_recipient', $this->twoFactor()->maskedRecipient($this->twoFactor()->emailOtpRecipient($email))),
            'emailOtpResendSeconds' => $method === 'email'
                ? max(0, 45 - (now()->timestamp - (int) $request->session()->get('emergency_mfa_email_otp_sent_at', 0)))
                : 0,
            'hasBackupCodes' => $this->twoFactor()->hasBackupCodes($email),
        ]);
    }

    public function verify(Request $request): RedirectResponse
    {
        $account = $this->pendingAccount($request);
        if (!$account) {
            return $this->restartMfaFlow($request);
        }

        $email = (string) $account['email'];
        $method = (string) $request->session()->get(self::MFA_METHOD_SESSION_KEY, '');
        if (!in_array($method, ['totp', 'email', 'backup'], true)) {
            return redirect()->route('system-admin.emergency-login.method');
        }

        if ($method === 'backup') {
            $validated = $request->validate([
                'verification_code' => ['required', 'string', 'max:16'],
            ]);
        } else {
            $validated = $request->validate([
                'verification_code' => ['required', 'digits:6'],
            ]);
        }

        $valid = match ($method) {
            'totp' => $this->twoFactor()->verifyAuthenticator($email, (string) $validated['verification_code']),
            'email' => $this->twoFactor()->verifyEmailOtp($email, (string) $validated['verification_code']),
            'backup' => $this->twoFactor()->verifyBackupCode($email, (string) $validated['verification_code']),
        };

        if (!$valid) {
            $this->logAttempt($request, null, 'Emergency login failed because the selected multi-factor code was invalid or expired.', 401);

            return back()->withErrors([
                'verification_code' => 'The verification code is invalid, expired, or already used.',
            ]);
        }

        return $this->completeLogin($request, $account);
    }

    private function completeLogin(Request $request, array $account): RedirectResponse
    {
        $bootstrapEmail = strtolower(trim((string) ($account['email'] ?? '')));
        $bootstrapPassword = (string) ($account['password'] ?? '');
        $bootstrapPasswordHash = trim((string) ($account['password_hash'] ?? ''));
        $bootstrapRole = User::normalizeRole((string) ($account['role'] ?? User::ROLE_ADMIN));

        if (!in_array($bootstrapRole, [User::ROLE_ADMIN, User::ROLE_SUPERADMIN], true)) {
            $this->logAttempt($request, null, 'Emergency login blocked because the configured account role is not allowed.', 403);
            $this->clearMfaState($request);

            return redirect()
                ->route('system-admin.emergency-login')
                ->withErrors(['email' => 'This emergency account is not allowed to access the clinic workspace.']);
        }

        $user = User::query()->where('email', $bootstrapEmail)->first();
        $newUser = false;
        if (!$user) {
            $user = new User();
            $newUser = true;
        }

        if (strtolower(trim((string) ($user->status ?? 'active'))) === 'inactive') {
            $this->clearMfaState($request);
            $this->logAttempt($request, $user, 'Emergency login blocked because the account is inactive.', 423);

            return redirect()
                ->route('system-admin.emergency-login')
                ->withErrors(['email' => 'This emergency account is inactive.']);
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
        $this->clearMfaState($request);
        Auth::shouldUse('admin');
        Auth::guard('admin')->login($user, false);

        $this->logAttempt(
            $request,
            $user,
            $newUser
                ? 'Emergency login succeeded and bootstrap account was created after multi-factor verification.'
                : 'Emergency login succeeded after multi-factor verification.',
            200
        );

        return redirect()
            ->route($bootstrapRole === User::ROLE_SUPERADMIN ? 'admin.dashboard' : 'assistant.dashboard')
            ->with('success', 'Emergency backup login successful.');
    }

    private function matchingAccount(string $email, string $password): ?array
    {
        $bootstrapAccounts = $this->emergencyConfig()['accounts'] ?? [];
        if (empty($bootstrapAccounts)) {
            return null;
        }

        foreach ($bootstrapAccounts as $account) {
            $accountEmail = strtolower(trim((string) ($account['email'] ?? '')));
            $accountPassword = (string) ($account['password'] ?? '');
            $accountPasswordHash = trim((string) ($account['password_hash'] ?? ''));
            $passwordMatches = $accountPasswordHash !== ''
                ? Hash::check($password, $accountPasswordHash)
                : ($accountPassword !== '' && hash_equals($accountPassword, $password));

            if ($email === $accountEmail && $passwordMatches) {
                return $account;
            }
        }

        return null;
    }

    private function pendingAccount(Request $request): ?array
    {
        $pending = $request->session()->get(self::MFA_PENDING_SESSION_KEY, []);
        $email = strtolower(trim((string) ($pending['email'] ?? '')));
        $verifiedAt = (int) ($pending['verified_at'] ?? 0);

        if ($email === '' || $verifiedAt <= 0 || now()->timestamp - $verifiedAt > self::MFA_PENDING_TTL_SECONDS) {
            $this->clearMfaState($request);
            return null;
        }

        foreach ($this->emergencyConfig()['accounts'] ?? [] as $account) {
            if (strtolower(trim((string) ($account['email'] ?? ''))) === $email) {
                return $account;
            }
        }

        $this->clearMfaState($request);

        return null;
    }

    private function restartMfaFlow(Request $request): RedirectResponse
    {
        $this->clearMfaState($request);

        return redirect()
            ->route('system-admin.emergency-login')
            ->withErrors(['email' => 'Your emergency verification session expired. Sign in again to continue.']);
    }

    private function loginRateLimitKey(Request $request, string $email): string
    {
        return 'emergency-login-failures:' . hash('sha256', strtolower(trim($email)) . '|' . $request->ip());
    }

    private function clearMfaState(Request $request): void
    {
        $request->session()->forget([
            self::ENROLLMENT_SECRET_SESSION_KEY,
            self::ENROLLMENT_BACKUP_CODES_SESSION_KEY,
            self::MFA_METHOD_SESSION_KEY,
            self::MFA_PENDING_SESSION_KEY,
            'emergency_mfa_email_recipient',
            'emergency_mfa_email_otp_sent_at',
        ]);
    }

    private function twoFactor(): EmergencyTwoFactorService
    {
        return app(EmergencyTwoFactorService::class);
    }

    private function logAttempt(Request $request, ?User $user, string $description, int $statusCode): void
    {
        $pendingEmail = (string) $request->session()->get(self::MFA_PENDING_SESSION_KEY . '.email', '');
        $actorEmail = strtolower(trim((string) ($user?->email ?: $pendingEmail ?: $request->input('email', ''))));
        $actorName = trim((string) ($user?->name ?? ''));
        if ($actorName === '') {
            $actorName = $actorEmail !== '' ? $actorEmail : 'Emergency Account';
        }

        try {
            ActivityLog::create([
                'user_id' => $user?->id,
                'user_name' => $actorName,
                'user_role' => $user ? strtolower((string) ($user->user_role ?? '')) : null,
                'action' => 'Emergency Login',
                'module' => 'Authentication',
                'event_type' => $statusCode === 200 ? 'auth' : 'error',
                'description' => $description,
                'route_name' => optional($request->route())->getName(),
                'http_method' => strtoupper($request->method()),
                'request_path' => '/' . ltrim($request->path(), '/'),
                'status_code' => $statusCode,
                'subject_type' => 'user',
                'subject_id' => $user?->id ? (string) $user->id : null,
                'metadata' => [
                    'email' => $actorEmail !== '' ? $actorEmail : null,
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
