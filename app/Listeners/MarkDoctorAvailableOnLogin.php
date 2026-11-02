<?php

namespace App\Listeners;

use App\Models\User;
use Illuminate\Auth\Events\Login;

class MarkDoctorAvailableOnLogin
{
    public function handle(Login $event): void
    {
        $user = $event->user;
        if (!$user instanceof User) {
            return;
        }

        if (!in_array(
            User::normalizeRole((string) $user->user_role),
            [User::ROLE_ADMIN, User::ROLE_SUPERADMIN],
            true
        )) {
            return;
        }

        $workflow = app(\App\Services\ClinicWorkflowService::class);
        if (!$workflow->activeDoctorProfile()) {
            return;
        }

        if ($workflow->clinicIsClosed()) {
            $workflow->setDoctorAvailability(false);
            return;
        }

        if ($workflow->userIsAssignedDoctor($user)) {
            $workflow->setDoctorAvailability(true);
        }
    }
}
