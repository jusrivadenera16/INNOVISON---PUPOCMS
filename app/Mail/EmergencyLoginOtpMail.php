<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class EmergencyLoginOtpMail extends Mailable
{
    use Queueable;
    use SerializesModels;

    public $code;
    public $expiresMinutes;

    public function __construct($code, $expiresMinutes = 5)
    {
        $this->code = (string) $code;
        $this->expiresMinutes = (int) $expiresMinutes;
    }

    public function build()
    {
        return $this
            ->subject('Your PUP Taguig Clinic verification code')
            ->view('emails.emergency-login-otp', [
                'logoUrl' => asset('images/clinic_logo_transparent.png'),
            ])
            ->text('emails.emergency-login-otp-text');
    }
}
