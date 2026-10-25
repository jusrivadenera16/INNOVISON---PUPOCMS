<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class EmergencyPasswordResetOtpMail extends Mailable
{
    use Queueable;
    use SerializesModels;

    public string $code;
    public int $expiresMinutes;

    public function __construct(string $code, int $expiresMinutes = 5)
    {
        $this->code = $code;
        $this->expiresMinutes = $expiresMinutes;
    }

    public function build(): self
    {
        return $this
            ->subject('Emergency password reset verification code')
            ->view('emails.emergency-password-reset-otp')
            ->text('emails.emergency-password-reset-otp-text');
    }
}
