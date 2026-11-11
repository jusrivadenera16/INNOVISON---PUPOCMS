<?php

namespace App\Http\Controllers;

use App\Models\IssuedMedicalClearance;
use App\Services\{HealthFileEncryption, HealthFileStorage, MedicalClearanceService, ModulePermissionService};
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class MedicalClearanceController extends Controller
{
    public function reviewOptions(string $type, int $profile, MedicalClearanceService $service)
    {
        $record = $service->profile($type, $profile);
        $foreignKey = $type === 'student' ? 'health_profile_id' : 'employee_health_profile_id';
        $submission = \App\Models\HealthFormSubmission::where($foreignKey, $profile)->latest('id')->first();
        abort_unless($submission && $submission->submitted_at && !$submission->approved_at
            && in_array(strtolower((string) $submission->status), ['submitted', 'under_review'], true), 422, 'No new submitted Health Form is awaiting review.');
        $purposes = $service->purposes($type, $record, $submission->category);
        $default = app(\App\Services\HealthRecordCategoryFilter::class)->label($submission->category ?: $record->health_form_category);
        return response()->json([
            'identity' => $service->identity($record, now()->toDateString()), 'today' => now()->toDateString(),
            'purposes' => $purposes, 'default_purpose' => in_array($default, $purposes, true) ? $default : '',
        ])->withHeaders(HealthFileStorage::NO_STORE_HEADERS);
    }

    public function index(Request $request, string $type, int $profile, MedicalClearanceService $service)
    {
        $record = $service->profile($type, $profile);
        $submission = $service->approvedSubmission($record);
        $purposes = $service->purposes($type, $record);
        $defaultPurpose = trim((string) ($submission?->category ?: $record->health_form_category));
        if ($defaultPurpose !== '') $defaultPurpose = app(\App\Services\HealthRecordCategoryFilter::class)->label($defaultPurpose);

        return response()->json([
            'can_issue' => (bool) $submission && app(ModulePermissionService::class)->can($request->user(), 'health_records.update_assessment'),
            'identity' => $service->identity($record, now()->toDateString()), 'today' => now()->toDateString(),
            'purposes' => $purposes, 'default_purpose' => in_array($defaultPurpose, $purposes, true) ? $defaultPurpose : '',
            'health_form_categories' => $service->healthFormCategories($type, $record),
            'submission_id' => $submission?->id,
            'documents' => IssuedMedicalClearance::where('record_type', $type)->where('profile_id', $profile)->latest('id')->get()->map(fn ($document) => $this->documentPayload($document)),
        ])->withHeaders(HealthFileStorage::NO_STORE_HEADERS);
    }

    public function store(Request $request, string $type, int $profile, MedicalClearanceService $service)
    {
        $input = $request->validate([
            'request_key' => ['required', 'uuid'], 'issued_on' => ['required', 'date_format:Y-m-d'],
            'purpose' => ['required', 'string', 'max:180'],
            'other_purpose' => ['required_if:purpose,__other__', 'nullable', 'string', 'max:180'],
            'submission_id' => ['required_with:doctor_review', 'integer', 'min:1'],
            'doctor_review' => ['sometimes', 'array:category,findings,remarks,decision'],
            'doctor_review.category' => ['required_with:doctor_review', 'string', 'max:180'],
            'doctor_review.findings' => ['required_with:doctor_review', 'in:No Findings / Normal,With Findings'],
            'doctor_review.decision' => ['required_with:doctor_review', 'in:issue'],
            'doctor_review.remarks' => ['nullable', 'string', 'max:4000'],
        ]);
        $document = $service->issue($type, $profile, $input, $request->user());

        return response()->json(['message' => 'Medical Clearance issued and saved.', 'document' => $this->documentPayload($document)])
            ->withHeaders(HealthFileStorage::NO_STORE_HEADERS);
    }

    public function show(string $type, int $profile, IssuedMedicalClearance $document, MedicalClearanceService $service)
    {
        $service->profile($type, $profile);
        abort_unless($document->record_type === $type && (int) $document->profile_id === $profile, 404);
        $disk = Storage::disk('health_private');
        abort_unless($document->pdf_path && $disk->exists($document->pdf_path), 404, 'The saved clearance PDF was not found.');
        $bytes = app(HealthFileEncryption::class)->decrypt($disk->get($document->pdf_path));

        return response($bytes, 200, array_merge(HealthFileStorage::NO_STORE_HEADERS, [
            'Content-Type' => 'application/pdf', 'Content-Disposition' => 'inline; filename="' . $document->control_number . '.pdf"',
            'X-Content-Type-Options' => 'nosniff',
        ]));
    }

    private function documentPayload(IssuedMedicalClearance $document): array
    {
        return ['id' => $document->id, 'control_number' => $document->control_number,
            'issued_on' => $document->issued_on->format('M d, Y'), 'purpose' => $document->purpose,
            'url' => route('admin.medical_clearances.show', [$document->record_type, $document->profile_id, $document]),
        ];
    }
}
