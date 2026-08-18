<?php

namespace App\Services;

use App\Mail\EmergencyLoginOtpMail;
use App\Models\SystemSetting;
use BaconQrCode\Renderer\Image\Svg;
use BaconQrCode\Writer;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use PragmaRX\Google2FA\Google2FA;

class EmergencyTwoFactorService
{
    private const EMAIL_OTP_CACHE_PREFIX = 'emergency-email-otp:';
    private const EMAIL_OTP_RESEND_CACHE_PREFIX = 'emergency-email-otp-resend:';
    private const BACKUP_CODES_SETTING_PREFIX = 'emergency_login_backup_codes:';
    private const SETTING_PREFIX = 'emergency_login_totp:';
    private const USED_TOTP_CACHE_PREFIX = 'emergency-totp-used:';

    public function hasAuthenticator(string $email): bool
    {
        return $this->authenticatorSecret($email) !== '';
    }

    public function generateSecret(): string
    {
        return (new Google2FA())->generateSecretKey();
    }

    public function provisioningUri(string $email, string $secret): string
    {
        return (new Google2FA())->getQRCodeUrl(
            trim((string) config('services.emergency.totp_issuer', 'PUP Taguig Clinic')),
            strtolower(trim($email)),
            $secret
        );
    }

    public function qrCodeSvg(string $provisioningUri): string
    {
        $renderer = new Svg();
        $renderer->setHeight(300);
        $renderer->setWidth(300);
        $renderer->setMargin(4);
        $renderer->setRoundDimensions(true);

        return (new Writer($renderer))->writeString($provisioningUri);
    }

    public function verifyEnrollmentCode(string $secret, string $code): bool
    {
        return $this->validates($secret, $code);
    }

    public function saveAuthenticator(string $email, string $secret): void
    {
        SystemSetting::putValue($this->settingKey($email), $secret, true);
    }

    /**
     * Backup codes are shown only once. Their hashes are encrypted at rest.
     */
    public function generateBackupCodes(int $count = 10): array
    {
        $count = max(1, $count);
        $codes = [];

        while (count($codes) < $count) {
            $rawCode = $this->randomBackupCharacters(8);
            $code = substr($rawCode, 0, 4) . '-' . substr($rawCode, 4, 4);
            $codes[$code] = $code;
        }

        return array_values($codes);
    }

    public function hasBackupCodes(string $email): bool
    {
        return $this->backupCodeHashes($email) !== [];
    }

    public function saveEnrollment(string $email, string $secret, array $backupCodes): void
    {
        $hashes = array_values(array_filter(array_map(
            fn ($code) => is_string($code) && $this->normalizeBackupCode($code) !== ''
                ? Hash::make($this->normalizeBackupCode($code))
                : null,
            $backupCodes
        )));

        if (count($hashes) < 10) {
            throw new \InvalidArgumentException('Ten backup codes are required for emergency authenticator enrollment.');
        }

        DB::transaction(function () use ($email, $secret, $hashes) {
            $this->saveAuthenticator($email, $secret);
            SystemSetting::putValue($this->backupCodesSettingKey($email), json_encode($hashes, JSON_THROW_ON_ERROR), true);
        });
    }

    public function verifyBackupCode(string $email, string $code): bool
    {
        $normalizedCode = $this->normalizeBackupCode($code);
        if ($normalizedCode === '') {
            return false;
        }

        $hashes = $this->backupCodeHashes($email);
        foreach ($hashes as $index => $hash) {
            if (!Hash::check($normalizedCode, $hash)) {
                continue;
            }

            unset($hashes[$index]);
            SystemSetting::putValue(
                $this->backupCodesSettingKey($email),
                json_encode(array_values($hashes), JSON_THROW_ON_ERROR),
                true
            );

            return true;
        }

        return false;
    }

    public function verifyAuthenticator(string $email, string $code): bool
    {
        $secret = $this->authenticatorSecret($email);
        if (!$this->validates($secret, $code)) {
            return false;
        }

        return Cache::add(
            self::USED_TOTP_CACHE_PREFIX . hash('sha256', $secret . '|' . trim($code)),
            true,
            now()->addMinutes(2)
        );
    }

    public function sendEmailOtp(string $email): array
    {
        $email = strtolower(trim($email));
        $resendKey = self::EMAIL_OTP_RESEND_CACHE_PREFIX . hash('sha256', $email);
        if (Cache::has($resendKey)) {
            return ['sent' => false, 'reason' => 'cooldown', 'recipient' => $this->maskedRecipient($this->emailOtpRecipient($email))];
        }

        $code = (string) random_int(100000, 999999);
        $cacheKey = self::EMAIL_OTP_CACHE_PREFIX . hash('sha256', $email);
        $recipient = $this->emailOtpRecipient($email);

        try {
            Mail::to($recipient)->send(new EmergencyLoginOtpMail($code));
        } catch (\Throwable $exception) {
            Log::warning('Emergency Email OTP delivery failed.', [
                'mailer' => config('mail.default'),
                'smtp_host' => config('mail.mailers.smtp.host'),
                'recipient' => $this->maskedRecipient($recipient),
                'exception' => $exception::class,
                'message' => $exception->getMessage(),
            ]);

            return ['sent' => false, 'reason' => 'delivery_failed', 'recipient' => $this->maskedRecipient($recipient)];
        }

        Cache::put($cacheKey, Hash::make($code), now()->addMinutes(5));
        Cache::put($resendKey, true, now()->addSeconds(45));

        return ['sent' => true, 'reason' => null, 'recipient' => $this->maskedRecipient($recipient)];
    }

    public function verifyEmailOtp(string $email, string $code): bool
    {
        $code = trim($code);
        if (!preg_match('/^\d{6}$/', $code)) {
            return false;
        }

        $email = strtolower(trim($email));
        $cacheKey = self::EMAIL_OTP_CACHE_PREFIX . hash('sha256', $email);
        $hash = Cache::get($cacheKey);

        if (!is_string($hash) || !Hash::check($code, $hash)) {
            return false;
        }

        Cache::forget($cacheKey);
        Cache::forget(self::EMAIL_OTP_RESEND_CACHE_PREFIX . hash('sha256', $email));

        return true;
    }

    public function emailOtpRecipient(string $email): string
    {
        $configuredRecipient = strtolower(trim((string) config('services.emergency.email_otp_recipient', '')));

        return $configuredRecipient !== '' ? $configuredRecipient : strtolower(trim($email));
    }

    public function maskedRecipient(string $email): string
    {
        [$local, $domain] = array_pad(explode('@', $email, 2), 2, '');
        if ($domain === '') {
            return 'configured recovery email';
        }

        return substr($local, 0, 1) . str_repeat('*', max(2, strlen($local) - 1)) . '@' . $domain;
    }

    private function authenticatorSecret(string $email): string
    {
        try {
            return trim((string) SystemSetting::getValue($this->settingKey($email), ''));
        } catch (\Throwable) {
            return '';
        }
    }

    private function settingKey(string $email): string
    {
        return self::SETTING_PREFIX . hash('sha256', strtolower(trim($email)));
    }

    private function backupCodeHashes(string $email): array
    {
        try {
            $decoded = json_decode((string) SystemSetting::getValue($this->backupCodesSettingKey($email), '[]'), true, 512, JSON_THROW_ON_ERROR);

            return is_array($decoded)
                ? array_values(array_filter($decoded, fn ($hash) => is_string($hash) && $hash !== ''))
                : [];
        } catch (\Throwable) {
            return [];
        }
    }

    private function backupCodesSettingKey(string $email): string
    {
        return self::BACKUP_CODES_SETTING_PREFIX . hash('sha256', strtolower(trim($email)));
    }

    private function normalizeBackupCode(string $code): string
    {
        $normalized = strtoupper((string) preg_replace('/[^A-Z2-9]/i', '', trim($code)));

        return strlen($normalized) === 8 ? $normalized : '';
    }

    private function randomBackupCharacters(int $length): string
    {
        $alphabet = 'ABCDEFGHJKLMNPQRSTUVWXYZ23456789';
        $characters = '';

        for ($index = 0; $index < $length; $index++) {
            $characters .= $alphabet[random_int(0, strlen($alphabet) - 1)];
        }

        return $characters;
    }

    private function validates(string $secret, string $code): bool
    {
        $code = trim($code);
        if ($secret === '' || !preg_match('/^\d{6}$/', $code)) {
            return false;
        }

        try {
            return (new Google2FA())->verifyKey($secret, $code, 1) === true;
        } catch (\Throwable) {
            return false;
        }
    }
}
