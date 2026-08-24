@extends('layouts.admin')

@section('title', 'Pulled Out Record')

@push('styles')
<style>
    .pulled-record-page {
        --pr-maroon: #8f1422;
        --pr-yellow: #facc15;
        --pr-border: #ead8dc;
        --pr-muted: #64748b;
        display: grid;
        gap: 16px;
        padding: 10px;
    }
    .pulled-record-card {
        border: 1px solid var(--pr-border);
        border-radius: 8px;
        background: #fff;
        box-shadow: 0 14px 34px rgba(72,16,27,.08);
    }
    .pulled-record-head {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 16px;
        padding: 20px 22px;
        color: #fff;
        background: var(--pr-maroon);
        border-radius: 7px 7px 0 0;
    }
    .pulled-record-title { display: flex; align-items: center; gap: 14px; min-width: 0; }
    .pulled-record-title > span {
        display: grid; place-items: center; width: 46px; height: 46px; flex: 0 0 auto;
        border: 1px solid rgba(255,255,255,.28); border-radius: 8px; background: rgba(255,255,255,.1);
    }
    .pulled-record-title svg { width: 24px; height: 24px; }
    .pulled-record-title h1 { margin: 0; font-size: 23px; }
    .pulled-record-title p { margin: 4px 0 0; font-size: 13px; color: rgba(255,255,255,.84); }
    .pulled-record-back {
        display: inline-flex; align-items: center; justify-content: center; min-height: 40px; padding: 0 14px;
        border: 1px solid rgba(255,255,255,.35); border-radius: 7px; color: #fff; text-decoration: none; font-weight: 800;
    }
    .pulled-record-body { padding: 20px 22px; }
    .pulled-record-alert {
        display: flex; align-items: center; justify-content: space-between; gap: 16px; margin-bottom: 18px; padding: 14px 16px;
        border: 1px solid #efb9c1; border-radius: 8px; color: #74101c; background: #fff1f3;
    }
    .pulled-record-alert strong { display: block; font-size: 14px; }
    .pulled-record-alert span { display: block; margin-top: 3px; font-size: 12px; }
    .pulled-record-grid { display: grid; grid-template-columns: repeat(4, minmax(0,1fr)); gap: 12px; }
    .pulled-record-field { padding: 13px; border: 1px solid #eadde0; border-radius: 8px; background: #fffafa; }
    .pulled-record-field dt { margin-bottom: 6px; color: var(--pr-muted); font-size: 10px; font-weight: 800; text-transform: uppercase; }
    .pulled-record-field dd { margin: 0; color: #172033; font-size: 13px; font-weight: 750; overflow-wrap: anywhere; }
    .pulled-record-section-title { margin: 22px 0 12px; color: #7c111e; font-size: 15px; }
    .pulled-record-docs { display: grid; grid-template-columns: repeat(3,minmax(0,1fr)); gap: 10px; }
    .pulled-record-doc {
        display: flex; align-items: center; justify-content: space-between; gap: 10px; min-height: 58px; padding: 12px;
        border: 1px solid #eadde0; border-radius: 8px; color: #172033; background: #fff; font-size: 12px; font-weight: 750;
    }
    .pulled-record-doc a { color: var(--pr-maroon); font-weight: 850; }
    .pulled-record-restore { margin-top: 20px; padding: 18px; border: 1px solid #eadde0; border-radius: 8px; background: #fffafa; }
    .pulled-record-restore label { display: block; margin-bottom: 8px; color: #7c111e; font-size: 12px; font-weight: 850; }
    .pulled-record-restore textarea {
        width: 100%; min-height: 96px; resize: vertical; padding: 12px; border: 1px solid #dcc8cd; border-radius: 7px;
        color: #172033; background: #fff; font: inherit; font-size: 13px; outline: none;
    }
    .pulled-record-restore textarea:focus { border-color: var(--pr-maroon); box-shadow: 0 0 0 3px rgba(143,20,34,.1); }
    .pulled-record-actions { display: flex; justify-content: flex-end; gap: 10px; margin-top: 12px; }
    .pulled-record-restore-btn {
        min-height: 42px; padding: 0 18px; border: 1px solid var(--pr-maroon); border-radius: 7px; color: #fff;
        background: var(--pr-maroon); font-weight: 850; cursor: pointer; box-shadow: 0 9px 20px rgba(111,15,25,.18);
        transition: background .2s ease, color .2s ease, transform .2s ease;
    }
    .pulled-record-restore-btn:hover { color: var(--pr-maroon); background: var(--pr-yellow); transform: translateY(-1px); }
    .pullout-error { margin-bottom: 12px; padding: 11px 13px; border: 1px solid #ef9ba7; border-radius: 7px; color: #8b1020; background: #fff0f2; font-size: 12px; }
    [data-theme="dark"] .pulled-record-page { --pr-border: #344157; --pr-muted: #aeb9cc; }
    [data-theme="dark"] .pulled-record-card { background: #111a2d; box-shadow: 0 16px 38px rgba(0,0,0,.38); }
    [data-theme="dark"] .pulled-record-field,
    [data-theme="dark"] .pulled-record-doc,
    [data-theme="dark"] .pulled-record-restore { border-color: #3b4960; background: #192338; }
    [data-theme="dark"] .pulled-record-field dd,
    [data-theme="dark"] .pulled-record-doc { color: #f7f9fd; }
    [data-theme="dark"] .pulled-record-restore textarea { color: #f7f9fd; background: #111a2d; border-color: #46516a; }
    [data-theme="dark"] .pulled-record-alert { color: #ffd6dc; background: #3a1720; border-color: #7d3946; }
    @media (max-width: 1000px) { .pulled-record-grid { grid-template-columns: repeat(2,minmax(0,1fr)); } }
    @media (max-width: 680px) {
        .pulled-record-page { padding: 4px; }
        .pulled-record-head { align-items: flex-start; flex-direction: column; }
        .pulled-record-grid, .pulled-record-docs { grid-template-columns: 1fr; }
        .pulled-record-alert { align-items: flex-start; flex-direction: column; }
        .pulled-record-actions, .pulled-record-restore-btn { width: 100%; }
    }
</style>
@endpush

@section('content')
@php
    $recordUser = $profile->user;
    $documentLabels = [
        'student_photo' => '2x2 Photo',
        'health_declaration' => 'Health Declaration',
        'medical_certificate' => 'Medical Certificate',
        'chest_xray_result' => 'Chest X-ray Result',
        'pwd_id_proof' => 'PWD Proof',
        'medical_assessment_upload' => 'Medical Assessment',
    ];
@endphp
<div class="pulled-record-page">
    <section class="pulled-record-card">
        <header class="pulled-record-head">
            <div class="pulled-record-title">
                <span><x-outline-icon name="document-check" /></span>
                <div>
                    <h1>Pulled Out Health Record</h1>
                    <p>Archived record retained for audit, review, and authorized restoration.</p>
                </div>
            </div>
            <a class="pulled-record-back" href="{{ route('reports.pulled-out-records') }}">Back to Pulled Out Logbook</a>
        </header>
        <div class="pulled-record-body">
            @if(session('success'))
                <div class="pulled-record-alert"><div><strong>{{ session('success') }}</strong></div></div>
            @endif
            <div class="pulled-record-alert">
                <div>
                    <strong>Clinic system access is blocked</strong>
                    <span>This profile is excluded from active Health Records. Its medical data and files remain stored.</span>
                </div>
                <strong>PULLED OUT</strong>
            </div>

            <dl class="pulled-record-grid">
                <div class="pulled-record-field"><dt>Name</dt><dd>{{ $recordUser?->name ?: 'Unknown User' }}</dd></div>
                <div class="pulled-record-field"><dt>Email</dt><dd>{{ $recordUser?->email ?: 'N/A' }}</dd></div>
                <div class="pulled-record-field"><dt>Reference Number</dt><dd>{{ $profile->reference_number ?: $profile->student_number ?: 'N/A' }}</dd></div>
                <div class="pulled-record-field"><dt>User Type</dt><dd>{{ \Illuminate\Support\Str::headline($recordUser?->user_type ?: $recordUser?->idp_role ?: $recordUser?->user_role ?: 'User') }}</dd></div>
                <div class="pulled-record-field"><dt>Previous Clearance</dt><dd>{{ $profile->clearance_status ?: 'N/A' }}</dd></div>
                <div class="pulled-record-field"><dt>Pullout Reason</dt><dd>{{ $profile->pullout_reason ?: 'N/A' }}</dd></div>
                <div class="pulled-record-field"><dt>Pulled Out By</dt><dd>{{ $profile->pulloutCompletedBy?->name ?: $profile->pulloutCompletedBy?->email ?: 'N/A' }}</dd></div>
                <div class="pulled-record-field"><dt>Pulled Out At</dt><dd>{{ $profile->pullout_completed_at?->format('M d, Y h:i A') ?: 'N/A' }}</dd></div>
                <div class="pulled-record-field"><dt>Medical Condition</dt><dd>{{ $profile->medical_condition_remarks ?: ($profile->hasMedicalCondition() ? 'With Condition' : 'No Medical Condition Recorded') }}</dd></div>
                <div class="pulled-record-field"><dt>Physical Assessment</dt><dd>{{ $profile->physical_assessment_status ?: 'N/A' }}</dd></div>
                <div class="pulled-record-field"><dt>Verified At</dt><dd>{{ $profile->verified_at?->format('M d, Y h:i A') ?: 'N/A' }}</dd></div>
                <div class="pulled-record-field"><dt>Previous Account Status</dt><dd>{{ \Illuminate\Support\Str::headline($profile->pullout_previous_user_status ?: 'Active') }}</dd></div>
            </dl>

            @if($profile->pullout_request_remarks)
                <h2 class="pulled-record-section-title">Pullout Remarks</h2>
                <div class="pulled-record-field"><dd>{{ $profile->pullout_request_remarks }}</dd></div>
            @endif

            <h2 class="pulled-record-section-title">Archived Documents</h2>
            <div class="pulled-record-docs">
                @foreach($documentLabels as $field => $label)
                    @php $hasDocument = filled($profile->getAttribute($field)); @endphp
                    <div class="pulled-record-doc">
                        <span>{{ $label }}</span>
                        @if($hasDocument)
                            <a href="{{ route('walkin.document', ['healthProfile' => $profile->id, 'document' => $field]) }}" target="_blank" rel="noopener">Open</a>
                        @else
                            <span>N/A</span>
                        @endif
                    </div>
                @endforeach
            </div>

            <form class="pulled-record-restore" method="POST" action="{{ route('admin.health_profile.pullout.restore', $profile->id) }}">
                @csrf
                @error('pullout_restore_reason')<div class="pullout-error">{{ $message }}</div>@enderror
                <label for="pulloutRestoreReason">Restore Reason</label>
                <textarea id="pulloutRestoreReason" name="pullout_restore_reason" required placeholder="Explain why the health record and clinic system access should be restored.">{{ old('pullout_restore_reason') }}</textarea>
                <div class="pulled-record-actions">
                    <button class="pulled-record-restore-btn" type="submit">Restore Record &amp; Access</button>
                </div>
            </form>
        </div>
    </section>
</div>
@endsection
