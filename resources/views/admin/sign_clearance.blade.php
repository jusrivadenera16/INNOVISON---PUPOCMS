@extends('layouts.admin')

@section('title', 'Verify Health Uploads')

@push('styles')
<style>
    .verify-wrap { max-width: 1120px; margin: 0 auto; display: grid; gap: 18px; }
    .verify-card { background: #fff; border: 1px solid #e2e8f0; border-radius: 14px; padding: 18px; box-shadow: 0 8px 24px rgba(15, 23, 42, 0.06); }
    .verify-head h2 { margin: 0; font-size: 20px; color: #0f172a; font-weight: 800; }
    .verify-head p { margin: 6px 0 0; font-size: 13px; color: #64748b; }
    .verify-top { display: flex; justify-content: space-between; align-items: flex-start; gap: 12px; flex-wrap: wrap; }
    .verify-badge { padding: 6px 12px; border-radius: 999px; font-size: 12px; font-weight: 800; }
    .verify-badge-ok { background: #ecfdf3; color: #047857; }
    .verify-badge-alert { background: #fef2f2; color: #b91c1c; }
    .verify-meta { margin-top: 14px; display: grid; grid-template-columns: repeat(4, minmax(0, 1fr)); gap: 10px; }
    .verify-meta-item { border: 1px solid #e2e8f0; border-radius: 10px; background: #f8fafc; padding: 10px 12px; }
    .verify-meta-k { font-size: 11px; text-transform: uppercase; color: #64748b; font-weight: 700; margin-bottom: 4px; }
    .verify-meta-v { font-size: 14px; color: #0f172a; font-weight: 700; word-break: break-word; }
    .verify-grid { display: grid; grid-template-columns: repeat(2, minmax(0, 1fr)); gap: 14px; }
    .verify-doc { border: 1px solid #e2e8f0; border-radius: 12px; padding: 12px; background: #fff; }
    .verify-doc h4 { margin: 0 0 10px; font-size: 14px; font-weight: 800; color: #1e293b; }
    .verify-doc-preview { width: 100%; height: 250px; border: 1px solid #e2e8f0; border-radius: 8px; background: #f8fafc; overflow: hidden; }
    .verify-doc-preview iframe, .verify-doc-preview img { width: 100%; height: 100%; border: 0; object-fit: contain; background: #fff; }
    .verify-missing { border: 1px dashed #cbd5e1; border-radius: 8px; padding: 12px; font-size: 13px; color: #64748b; font-weight: 600; background: #f8fafc; }
    .verify-link { display: inline-flex; align-items: center; gap: 6px; text-decoration: none; border: 1px solid #cbd5e1; border-radius: 8px; padding: 7px 10px; font-size: 12px; font-weight: 700; color: #1e293b; margin-bottom: 10px; }
    .verify-form-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 14px; }
    .verify-label { display: block; margin-bottom: 6px; font-size: 13px; font-weight: 700; color: #334155; }
    .verify-control { width: 100%; border: 1px solid #cbd5e1; border-radius: 10px; padding: 10px 12px; font-size: 14px; background: #fff; color: #0f172a; }
    .verify-actions { display: flex; justify-content: flex-end; gap: 10px; margin-top: 16px; }
    .verify-btn { border: 1px solid transparent; border-radius: 10px; padding: 10px 15px; font-weight: 700; font-size: 13px; text-decoration: none; cursor: pointer; }
    .verify-btn-cancel { background: #e2e8f0; color: #1e293b; }
    .verify-btn-save { background: #70131b; color: #fff; }

    [data-theme="dark"] .verify-card, [data-theme="dark"] .verify-doc { background: #0f172a; border-color: #334155; box-shadow: none; }
    [data-theme="dark"] .verify-head h2, [data-theme="dark"] .verify-meta-v, [data-theme="dark"] .verify-doc h4, [data-theme="dark"] .verify-label { color: #f8fafc; }
    [data-theme="dark"] .verify-head p, [data-theme="dark"] .verify-meta-k, [data-theme="dark"] .verify-missing { color: #cbd5e1; }
    [data-theme="dark"] .verify-meta-item { background: #111827; border-color: #334155; }
    [data-theme="dark"] .verify-link { background: #111827; border-color: #475569; color: #f8fafc; }
    [data-theme="dark"] .verify-control { background: #111827; border-color: #475569; color: #f8fafc; }
    [data-theme="dark"] .verify-btn-cancel { background: #1e293b; color: #f8fafc; }

    @media (max-width: 1024px) { .verify-meta { grid-template-columns: repeat(2, minmax(0, 1fr)); } }
    @media (max-width: 768px) {
        .verify-grid, .verify-form-grid, .verify-meta { grid-template-columns: 1fr; }
    }
</style>
@endpush

@section('content')
@php
    $documentRouteName = request()->routeIs('assistant.*') ? 'assistant.walkin.document' : 'walkin.document';
@endphp
<div style="margin-bottom: 14px;">
    <a href="{{ route('admin.health_records') }}" style="text-decoration: none; color: #64748b; font-size: 14px;">&larr; Back to Health Records</a>
</div>

<div class="verify-wrap">
    <div class="verify-card">
        <div class="verify-top">
            <div class="verify-head">
                <h2>Verify Student Health Uploads</h2>
                <p>Review the uploaded files and set the final verification status. Nurse digital e-sign is not required in this workflow.</p>
            </div>
            <span class="verify-badge {{ ($record->has_disability ?? 'No') === 'Yes' ? 'verify-badge-alert' : 'verify-badge-ok' }}">
                {{ ($record->has_disability ?? 'No') === 'Yes' ? 'With Condition' : 'No Condition' }}
            </span>
        </div>

        <div class="verify-meta">
            <div class="verify-meta-item">
                <div class="verify-meta-k">Student Name</div>
                <div class="verify-meta-v">{{ $record->user->name ?? 'N/A' }}</div>
            </div>
            <div class="verify-meta-item">
                <div class="verify-meta-k">Student Number</div>
                <div class="verify-meta-v">{{ $record->user->student_number ?: ($record->user->student_id ?? 'N/A') }}</div>
            </div>
            <div class="verify-meta-item">
                <div class="verify-meta-k">Course</div>
                <div class="verify-meta-v">{{ $record->course_college ?: ($record->user->course ?? 'N/A') }}</div>
            </div>
            <div class="verify-meta-item">
                <div class="verify-meta-k">Submitted</div>
                <div class="verify-meta-v">{{ optional($record->created_at)->format('M d, Y h:i A') }}</div>
            </div>
        </div>
    </div>

    <div class="verify-card">
        <div class="verify-grid">
            <div class="verify-doc">
                <h4>Medical Certificate (PDF)</h4>
                @if(!empty($record->medical_certificate))
                    @php($medicalCertificateUrl = route($documentRouteName, ['healthProfile' => $record->id, 'document' => 'medical_certificate']))
                    <a class="verify-link" href="{{ $medicalCertificateUrl }}" target="_blank" rel="noopener">
                        <x-outline-icon name="document-text" /> Open
                    </a>
                    <div class="verify-doc-preview">
                        <iframe src="{{ $medicalCertificateUrl }}"></iframe>
                    </div>
                @else
                    <div class="verify-missing">No medical certificate uploaded.</div>
                @endif
            </div>

            <div class="verify-doc">
                <h4>Medical Assessment Copy</h4>
                @if(!empty($record->medical_assessment_upload))
                    @php($medicalAssessmentUrl = route($documentRouteName, ['healthProfile' => $record->id, 'document' => 'medical_assessment_upload']))
                    <a class="verify-link" href="{{ $medicalAssessmentUrl }}" target="_blank" rel="noopener">
                        <x-outline-icon name="document-text" /> Open
                    </a>
                    <div class="verify-doc-preview">
                        <iframe src="{{ $medicalAssessmentUrl }}"></iframe>
                    </div>
                @else
                    <div class="verify-missing">No medical assessment copy uploaded.</div>
                @endif
            </div>

            <div class="verify-doc">
                <h4>Chest X-ray Result (PDF)</h4>
                @if(!empty($record->chest_xray_result))
                    @php($chestXrayUrl = route($documentRouteName, ['healthProfile' => $record->id, 'document' => 'chest_xray_result']))
                    <a class="verify-link" href="{{ $chestXrayUrl }}" target="_blank" rel="noopener">
                        <x-outline-icon name="document-text" /> Open
                    </a>
                    <div class="verify-doc-preview">
                        <iframe src="{{ $chestXrayUrl }}"></iframe>
                    </div>
                @else
                    <div class="verify-missing">No chest X-ray result uploaded.</div>
                @endif
            </div>

            <div class="verify-doc">
                <h4>PWD ID Proof (PDF)</h4>
                @if(($record->has_disability ?? 'No') !== 'Yes')
                    <div class="verify-missing">Not required (PWD is set to No).</div>
                @elseif(!empty($record->pwd_id_proof))
                    @php($pwdIdProofUrl = route($documentRouteName, ['healthProfile' => $record->id, 'document' => 'pwd_id_proof']))
                    <a class="verify-link" href="{{ $pwdIdProofUrl }}" target="_blank" rel="noopener">
                        <x-outline-icon name="document-text" /> Open
                    </a>
                    <div class="verify-doc-preview">
                        <iframe src="{{ $pwdIdProofUrl }}"></iframe>
                    </div>
                @else
                    <div class="verify-missing">PWD is Yes but no proof uploaded.</div>
                @endif
            </div>

            <div class="verify-doc">
                <h4>2x2 Student Photo</h4>
                @if(!empty($record->student_photo))
                    @php($studentPhotoUrl = route($documentRouteName, ['healthProfile' => $record->id, 'document' => 'student_photo']))
                    <a class="verify-link" href="{{ $studentPhotoUrl }}" target="_blank" rel="noopener">
                        <x-outline-icon name="eye" /> Open
                    </a>
                    <div class="verify-doc-preview">
                        <img src="{{ $studentPhotoUrl }}" alt="2x2 Student Photo">
                    </div>
                @else
                    <div class="verify-missing">No 2x2 photo uploaded.</div>
                @endif
            </div>
        </div>
    </div>

    <div class="verify-card">
        <form action="{{ route('admin.update_clearance', $record->id) }}" method="POST">
            @csrf
            @method('PUT')

            <div style="margin-bottom: 14px;">
                <label class="verify-label" for="pending_reason">Verification Notes / Reason</label>
                <textarea id="pending_reason" name="pending_reason" class="verify-control" rows="3" placeholder="Add notes if still for verification or rejected...">{{ $record->pending_reason }}</textarea>
            </div>

            <div class="verify-form-grid">
                <div>
                    <label class="verify-label" for="clearance_status">Verification Status</label>
                    <select id="clearance_status" name="clearance_status" class="verify-control" required>
                        <option value="Issued" {{ $record->clearance_status == 'Issued' ? 'selected' : '' }}>Approved (Webhook Ready)</option>
                        <option value="For Verification" {{ in_array($record->clearance_status, ['Pending', 'For Verification'], true) ? 'selected' : '' }}>For Verification</option>
                        <option value="Rejected" {{ $record->clearance_status == 'Rejected' ? 'selected' : '' }}>Rejected</option>
                    </select>
                </div>
                <div>
                    <label class="verify-label" for="verified_at">Date Verified</label>
                    <input id="verified_at" type="date" name="verified_at" class="verify-control" value="{{ $record->verified_at ?? date('Y-m-d') }}" readonly>
                </div>
            </div>

            <div class="verify-actions">
                <a href="{{ route('admin.health_records') }}" class="verify-btn verify-btn-cancel">Cancel</a>
                <button type="submit" class="verify-btn verify-btn-save">Save Verification</button>
            </div>
        </form>
    </div>
</div>
@endsection
