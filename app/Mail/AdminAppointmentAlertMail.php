<?php

namespace App\Mail;

use App\Models\Appointment;
use Carbon\Carbon;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class AdminAppointmentAlertMail extends Mailable
{
    use Queueable;
    use SerializesModels;

    public string $appointmentNumber;
    public string $patientName;
    public string $service;
    public string $schedule;
    public string $status;
    public string $appointmentsUrl;

    public function __construct(Appointment $appointment, string $appointmentsUrl)
    {
        $this->appointmentNumber = trim((string) $appointment->apt_id) ?: (string) $appointment->getKey();
        $this->patientName = trim((string) $appointment->name) ?: 'Patient';
        $this->service = trim((string) $appointment->service) ?: 'Clinic service';
        $this->status = trim((string) $appointment->status) ?: 'Pending';
        $this->appointmentsUrl = $appointmentsUrl;

        try {
            $this->schedule = Carbon::parse($appointment->date . ' ' . $appointment->time)->format('M d, Y g:i A');
        } catch (\Throwable) {
            $this->schedule = 'Schedule unavailable';
        }
    }

    public function build(): self
    {
        return $this
            ->subject('New clinic appointment submitted')
            ->view('emails.admin-appointment-alert')
            ->text('emails.admin-appointment-alert-text');
    }
}
