<?php

namespace App\Listeners;

use App\Models\User;
use App\Services\ClinicWorkflowService;
use Illuminate\Auth\Events\Logout;

class MarkDoctorOutOnLogout
{
    public function handle(Logout $event): void
    {
        $user = $event->user;
        if (!$user instanceof User) {
            return;
        }

        $workflow = app(ClinicWorkflowService::class);
        if ($workflow->userIsAssignedDoctor($user)) {
            $workflow->setDoctorAvailability(false);
        }
    }
}
