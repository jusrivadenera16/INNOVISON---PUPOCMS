<?php

namespace Tests\Unit;

use App\Mail\EmergencyLoginOtpMail;
use App\Services\EmergencyTwoFactorService;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;
use PragmaRX\Google2FA\Google2FA;
use Tests\TestCase;

class EmergencyTwoFactorServiceTest extends TestCase
{
    private const SECRET = 'JBSWY3DPEHPK3PXPJBSWY3DPEHPK3PXP';

    protected function tearDown(): void
    {
        config([
            'services.emergency.email_otp_recipient' => null,
            'services.emergency.totp_issuer' => 'PUP Taguig Clinic',
            'mail.default' => env('MAIL_MAILER', 'log'),
        ]);

        parent::tearDown();
    }

    public function test_it_generates_a_google_authenticator_secret_and_verifies_enrollment(): void
    {
        $service = app(EmergencyTwoFactorService::class);
        $secret = $service->generateSecret();
        $code = (new Google2FA())->getCurrentOtp($secret);

        $this->assertSame(32, strlen($secret));
        $this->assertTrue($service->verifyEnrollmentCode($secret, $code));
        $this->assertFalse($service->verifyEnrollmentCode($secret, '123456'));
    }

    public function test_it_builds_a_provisioning_uri_for_the_specific_emergency_account(): void
    {
        config(['services.emergency.totp_issuer' => 'PUP Taguig Clinic']);
        $service = app(EmergencyTwoFactorService::class);
        $uri = $service->provisioningUri('emergency@example.test', self::SECRET);

        $this->assertStringStartsWith('otpauth://totp/', $uri);
        $this->assertStringContainsString('PUP%20Taguig%20Clinic', $uri);
        $this->assertStringContainsString('emergency%40example.test', $uri);

        parse_str((string) parse_url($uri, PHP_URL_QUERY), $parameters);
        $this->assertSame(self::SECRET, $parameters['secret']);
        $this->assertSame('PUP Taguig Clinic', $parameters['issuer']);
    }

    public function test_it_renders_a_scannable_setup_qr_code(): void
    {
        $service = app(EmergencyTwoFactorService::class);
        $svg = $service->qrCodeSvg('otpauth://totp/PUP:emergency?secret=' . self::SECRET);

        $this->assertStringContainsString('<svg', $svg);
        $this->assertStringContainsString('<rect', $svg);
    }

    public function test_it_generates_ten_short_unique_backup_codes(): void
    {
        $codes = app(EmergencyTwoFactorService::class)->generateBackupCodes();

        $this->assertCount(10, $codes);
        $this->assertCount(10, array_unique($codes));
        foreach ($codes as $code) {
            $this->assertMatchesRegularExpression('/^[A-Z2-9]{4}-[A-Z2-9]{4}$/', $code);
        }
    }

    public function test_it_masks_the_email_otp_recipient_without_changing_the_configured_address(): void
    {
        config(['services.emergency.email_otp_recipient' => null]);
        $service = app(EmergencyTwoFactorService::class);

        $this->assertSame('e********@example.test', $service->maskedRecipient('emergency@example.test'));
        $this->assertSame('emergency@example.test', $service->emailOtpRecipient('emergency@example.test'));

        config(['services.emergency.email_otp_recipient' => 'recovery@example.test']);

        $this->assertSame('recovery@example.test', $service->emailOtpRecipient('emergency@example.test'));
    }

    public function test_it_sends_an_email_otp_and_applies_a_resend_cooldown(): void
    {
        config([
            'mail.default' => 'array',
            'services.emergency.email_otp_recipient' => null,
        ]);
        Mail::fake();
        $service = app(EmergencyTwoFactorService::class);
        $email = 'emergency-' . Str::uuid() . '@example.test';

        $firstAttempt = $service->sendEmailOtp($email);
        $secondAttempt = $service->sendEmailOtp($email);

        $this->assertTrue($firstAttempt['sent']);
        $this->assertStringContainsString('@example.test', $firstAttempt['recipient']);
        Mail::assertSent(EmergencyLoginOtpMail::class);
        $this->assertFalse($secondAttempt['sent']);
        $this->assertSame('cooldown', $secondAttempt['reason']);
    }
}
