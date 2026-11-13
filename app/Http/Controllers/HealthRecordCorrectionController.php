<?php

namespace App\Http\Controllers;

use App\Services\{HealthRecordCorrectionService, StudentNotificationMailer};
use Illuminate\Http\Request;

class HealthRecordCorrectionController extends Controller
{
    public function store(Request $request, string $type, int $profile, HealthRecordCorrectionService $service)
    {
        $data = $request->validate(['submission_id' => ['required', 'integer', 'min:1']]);
        $correction = $service->request($type, $profile, (int) $data['submission_id'], '', $request->user());
        if ($correction->wasRecentlyCreated) {
            try {
                $formRoute = $type === 'employee' ? 'health.form.employee'
                    : ($correction->user->clinicAccountTypeKey() === 'student' ? 'health.form.student'
                        : ($correction->user->applicant_type === 'transferee' ? 'health.form.transferee' : 'health.form'));
                app(StudentNotificationMailer::class)->sendHealthRecordNotice($correction->user, 'health_form_correction', ['action_url' => route($formRoute)]);
            } catch (\Throwable $exception) {
                // The correction remains visible in the portal even if email is unavailable.
                \Log::warning('Correction request saved; notification could not be sent.', ['correction_request_id' => $correction->id]);
            }
        }
        return redirect()->route('admin.health_records', ['tab' => 'pending_approval'])
            ->with('success', 'Health Form returned for correction. The record owner can edit and resubmit the same form.');
    }
}
