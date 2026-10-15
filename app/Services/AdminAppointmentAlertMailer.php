<?php

namespace App\Services;

use App\Mail\AdminAppointmentAlertMail;
use App\Models\Appointment;
use App\Models\User;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class AdminAppointmentAlertMailer
{
    public function sendNewAppointmentAlert(Appointment $appointment): void
    {
        $admins = User::query()
            ->where('admin_appointment_email_alerts', true)
            ->whereNotNull('email')
            ->get()
            ->filter(function (User $user): bool {
                return in_array(User::normalizeRole((string) $user->user_role), [User::ROLE_ADMIN, User::ROLE_SUPERADMIN], true)
                    && in_array(strtolower(trim((string) $user->status)), ['', 'active'], true)
                    && $user->isVisibleForAdminHubRecords()
                    && $user->canAccessPermission('appointments.view')
                    && filter_var(trim((string) $user->email), FILTER_VALIDATE_EMAIL) !== false;
            });

        foreach ($admins as $admin) {
            $appointmentsUrl = $admin->isStudentAssistant()
                ? url('/assistant/appointments')
                : route('admin.appointments');

            try {
                Mail::to($admin->email)->send(new AdminAppointmentAlertMail($appointment, $appointmentsUrl));
            } catch (\Throwable $exception) {
                Log::warning('Admin appointment alert email could not be sent.', [
                    'admin_id' => $admin->id,
                    'appointment_id' => $appointment->getKey(),
                    'exception' => $exception::class,
                ]);
            }
        }
    }
}
