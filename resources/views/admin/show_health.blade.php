@extends('layouts.admin')

@section('title', 'Student Health Profile')

@push('styles')
<style>
    .health-profile-wrap {
        max-width: 1120px;
        margin: 0 auto;
        display: grid;
        gap: 16px;
        padding-right: 116px;
        padding-bottom: 124px;
        box-sizing: border-box;
    }
    #headerQuickActions,
    .quick-actions-wrap,
    .quick-actions-toggle,
    .quick-actions-panel,
    .medicine-alert-fab,
    .medicine-alert-panel {
        z-index: 2147483000 !important;
    }
    .profile-card { background: #ffffff; border: 1px solid #e2e8f0; border-radius: 14px; box-shadow: 0 8px 24px rgba(15, 23, 42, 0.06); padding: 18px; }
    .profile-head { display: flex; justify-content: space-between; align-items: center; gap: 12px; flex-wrap: wrap; }
    .profile-title { margin: 0; font-size: 21px; font-weight: 800; color: #0f172a; }
    .profile-sub { margin: 6px 0 0; font-size: 14px; color: #64748b; }
    .profile-top-btn {
        position: relative;
        overflow: hidden;
        display: inline-flex;
        align-items: center;
        gap: 8px;
        border-radius: 999px;
        min-height: 44px;
        padding: 11px 18px;
        font-size: 15px;
        font-weight: 800;
        color: #70131B;
        background: #ffffff;
        border: 1px solid rgba(112, 19, 27, 0.34);
        text-decoration: none;
        box-shadow:
            0 0 0 2px rgba(112, 19, 27, 0.08),
            0 10px 22px rgba(15, 23, 42, 0.10);
        transition: color .08s linear, transform .18s ease, box-shadow .18s ease, border-color .18s ease;
        z-index: 0;
    }
    .profile-top-btn::after {
        content: "";
        position: absolute;
        inset: 0;
        background:
            linear-gradient(120deg,
                rgba(255, 248, 196, 0) 0%,
                rgba(255, 239, 181, 0.14) 22%,
                rgba(255, 239, 181, 0.52) 48%,
                rgba(255, 239, 181, 0.14) 72%,
                rgba(255, 248, 196, 0) 100%);
        transform: translateX(-135%);
        transition: transform 1.5s ease;
        z-index: -1;
    }
    .profile-top-btn:hover {
        color: #70131B !important;
        text-decoration: none;
        transform: translateY(-1px);
        border-color: rgba(112, 19, 27, 0.58);
        background: #ffffff;
        box-shadow:
            0 0 0 3px rgba(250, 204, 21, 0.18),
            0 14px 24px rgba(112, 19, 27, 0.16);
    }
    .profile-top-btn,
    .profile-top-btn:visited,
    .profile-top-btn:active,
    .profile-top-btn:focus,
    .profile-top-btn:hover,
    .profile-top-btn span,
    .profile-top-btn svg {
        color: #70131B !important;
    }
    .profile-top-btn svg,
    .profile-top-btn svg * {
        stroke: #70131B !important;
    }
    .profile-top-btn:hover::after {
        transform: translateX(135%);
    }
    .profile-head-actions { display: inline-flex; align-items: center; gap: 10px; flex-wrap: wrap; }
    .profile-switch { display: flex; gap: 10px; flex-wrap: wrap; }
    .profile-switch-head {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 12px;
        flex-wrap: wrap;
    }
    .profile-tab {
        border: 1px solid #cbd5e1;
        background: #ffffff;
        color: #334155;
        border-radius: 999px;
        padding: 9px 14px;
        font-size: 13px;
        font-weight: 800;
        cursor: pointer;
        transition: all .18s ease;
    }
    .profile-tab.is-active {
        background: #70131B;
        border-color: #8f2230;
        color: #ffffff;
    }
    .profile-status-badge {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        min-height: 36px;
        padding: 8px 14px;
        border-radius: 999px;
        font-size: 12px;
        font-weight: 800;
        text-transform: uppercase;
        border: 1px solid transparent;
        letter-spacing: 0.02em;
    }
    .profile-status-badge svg {
        width: 14px;
        height: 14px;
        margin-right: 6px;
        stroke-width: 2.2;
        flex: 0 0 auto;
    }
    .profile-status-issued {
        background: #dcfce7;
        color: #166534;
        border-color: #86efac;
    }
    .profile-status-pending {
        background: #ffedd5;
        color: #9a3412;
        border-color: #fdba74;
    }
    .profile-status-rejected {
        background: #fee2e2;
        color: #991b1b;
        border-color: #fca5a5;
    }
    .profile-status-default {
        background: #e2e8f0;
        color: #334155;
        border-color: #cbd5e1;
    }
    .profile-panel { display: none; }
    .profile-panel.is-active { display: block; }

    .profile-grid { display: grid; grid-template-columns: repeat(3, minmax(0, 1fr)); gap: 12px; }
    .profile-meta { background: #f8fafc; border: 1px solid #e2e8f0; border-radius: 10px; padding: 10px 12px; }
    .profile-meta-k { font-size: 12px; color: #64748b; text-transform: uppercase; font-weight: 700; margin-bottom: 4px; }
    .profile-meta-v { font-size: 15px; color: #0f172a; font-weight: 700; word-break: break-word; }

    .doc-grid { display: grid; grid-template-columns: repeat(2, minmax(0, 1fr)); gap: 14px; }
    .doc-file { border: 1px solid #e2e8f0; border-radius: 12px; padding: 14px; background: #fff; }
    .doc-file h4 { margin: 0 0 10px; font-size: 15px; font-weight: 800; color: #1e293b; }
    .doc-actions { display: flex; gap: 8px; flex-wrap: wrap; margin-bottom: 10px; }
    .doc-link { display: inline-flex; align-items: center; gap: 6px; border: 1px solid #cbd5e1; border-radius: 8px; padding: 7px 10px; color: #1e293b; font-size: 13px; font-weight: 700; text-decoration: none; background: #fff; }
    .doc-preview { width: 100%; height: 300px; border: 1px solid #e2e8f0; border-radius: 8px; overflow: hidden; background: #f8fafc; }
    .doc-preview iframe, .doc-preview img { width: 100%; height: 100%; border: 0; object-fit: contain; background: #fff; }
    .doc-missing { border: 1px dashed #cbd5e1; color: #64748b; border-radius: 8px; padding: 14px; font-size: 14px; font-weight: 600; background: #f8fafc; }

    [data-theme="dark"] .profile-card,
    [data-theme="dark"] .doc-file { background: #0f172a; border-color: #334155; box-shadow: none; }
    [data-theme="dark"] .profile-title,
    [data-theme="dark"] .profile-meta-v,
    [data-theme="dark"] .doc-file h4 { color: #f8fafc; }
    [data-theme="dark"] .profile-sub,
    [data-theme="dark"] .profile-meta-k,
    [data-theme="dark"] .doc-missing { color: #cbd5e1; }
    [data-theme="dark"] .profile-meta { background: #111827; border-color: #334155; }
    [data-theme="dark"] .profile-top-btn {
        color: #ffffff !important;
        border-color: rgba(250, 204, 21, 0.30);
        box-shadow:
            0 0 0 3px rgba(112, 19, 27, 0.16),
            0 12px 22px rgba(0, 0, 0, 0.24);
    }
    [data-theme="dark"] .profile-tab { background: #111827; border-color: #475569; color: #f8fafc; }
    [data-theme="dark"] .profile-tab.is-active { background: #70131B; border-color: #8f2230; color: #fff; }
    [data-theme="dark"] .doc-link { background: #111827; border-color: #475569; color: #f8fafc; }
    [data-theme="dark"] .profile-status-issued {
        background: rgba(21, 128, 61, 0.25);
        color: #bbf7d0;
        border-color: rgba(74, 222, 128, 0.55);
    }
    [data-theme="dark"] .profile-status-pending {
        background: rgba(154, 52, 18, 0.25);
        color: #fed7aa;
        border-color: rgba(251, 146, 60, 0.55);
    }
    [data-theme="dark"] .profile-status-rejected {
        background: rgba(153, 27, 27, 0.25);
        color: #fecaca;
        border-color: rgba(248, 113, 113, 0.55);
    }
    [data-theme="dark"] .profile-status-default {
        background: rgba(51, 65, 85, 0.6);
        color: #e2e8f0;
        border-color: rgba(148, 163, 184, 0.35);
    }

    @media (max-width: 1024px) {
        .profile-grid { grid-template-columns: repeat(2, minmax(0, 1fr)); }
    }
    @media (max-width: 768px) {
        .health-profile-wrap {
            padding-right: 0;
            padding-bottom: 152px;
        }
        .profile-grid,
        .doc-grid { grid-template-columns: 1fr; }
    }
</style>
@endpush

@section('content')
@php
    $profileStatusRaw = trim((string) ($profile->clearance_status ?? ''));
    $profileStatusNormalized = in_array($profileStatusRaw, ['Pending', 'For Verification'], true) ? 'Pending' : $profileStatusRaw;
    $profileStatusClass = match ($profileStatusNormalized) {
        'Issued' => 'profile-status-issued',
        'Pending' => 'profile-status-pending',
        'Rejected' => 'profile-status-rejected',
        default => 'profile-status-default',
    };
    $profileStatusLabel = $profileStatusNormalized !== '' ? $profileStatusNormalized : 'Not Processed';
    $documentRouteName = request()->routeIs('assistant.*') ? 'assistant.walkin.document' : 'walkin.document';
    $displayStudentNumber = trim((string) optional($profile->user)->student_number);
    if ($displayStudentNumber === '' || \Illuminate\Support\Str::startsWith(\Illuminate\Support\Str::upper($displayStudentNumber), 'CLN-')) {
        $displayStudentNumber = 'N/A';
    }
@endphp
<div class="health-profile-wrap">
    <div class="profile-card">
        <div class="profile-head">
            <div>
                <h1 class="profile-title">Student Health Profile</h1>
                <p class="profile-sub">Issued health profile details and submitted documents.</p>
            </div>
            <div class="profile-head-actions">
                <a href="{{ route('admin.health_records') }}" class="profile-top-btn">
                    <span aria-hidden="true">&larr;</span>
                    Back
                </a>
            </div>
        </div>
    </div>

    <div class="profile-card">
        <div class="profile-switch-head">
            <div class="profile-switch" role="tablist" aria-label="Health profile sections">
                <button type="button" class="profile-tab is-active" data-profile-tab-target="summaryPanel">Personal Information</button>
                <button type="button" class="profile-tab" data-profile-tab-target="docsPanel">Uploaded Documents</button>
            </div>
            <span class="profile-status-badge {{ $profileStatusClass }}">
                @if($profileStatusNormalized === 'Issued')
                    <x-outline-icon name="check" />
                @elseif($profileStatusNormalized === 'Pending')
                    <x-outline-icon name="clock" />
                @elseif($profileStatusNormalized === 'Rejected')
                    <x-outline-icon name="exclamation-triangle" />
                @else
                    <x-outline-icon name="information-circle" />
                @endif
                Status: {{ $profileStatusLabel }}
            </span>
        </div>
    </div>

    <div class="profile-card profile-panel is-active" id="summaryPanel">
        <div class="profile-grid">
            <div class="profile-meta"><div class="profile-meta-k">Student Name</div><div class="profile-meta-v">{{ $profile->user->name ?? 'N/A' }}</div></div>
            <div class="profile-meta"><div class="profile-meta-k">Student Number</div><div class="profile-meta-v">{{ $displayStudentNumber }}</div></div>
            <div class="profile-meta"><div class="profile-meta-k">Course</div><div class="profile-meta-v">{{ $profile->course_college ?: ($profile->user->course ?? 'N/A') }}</div></div>

            <div class="profile-meta"><div class="profile-meta-k">Year / Section</div><div class="profile-meta-v">{{ trim(($profile->user->year ?? '') . '-' . ($profile->user->section ?? '')) ?: 'N/A' }}</div></div>
            <div class="profile-meta"><div class="profile-meta-k">Email</div><div class="profile-meta-v">{{ $profile->user->email ?? 'N/A' }}</div></div>
            <div class="profile-meta"><div class="profile-meta-k">Status</div><div class="profile-meta-v">{{ in_array($profile->clearance_status, ['Pending', 'For Verification'], true) ? 'For Verification' : ($profile->clearance_status ?: 'Not Processed') }}</div></div>

            <div class="profile-meta"><div class="profile-meta-k">Gender</div><div class="profile-meta-v">{{ $profile->sex ?: 'N/A' }}</div></div>
            <div class="profile-meta"><div class="profile-meta-k">Civil Status</div><div class="profile-meta-v">{{ $profile->civil_status ?: 'N/A' }}</div></div>
            <div class="profile-meta"><div class="profile-meta-k">Age</div><div class="profile-meta-v">{{ $profile->age ?: ($calculatedAge ?: 'N/A') }}</div></div>

            <div class="profile-meta"><div class="profile-meta-k">Blood Type</div><div class="profile-meta-v">{{ $profile->blood_type ?: 'N/A' }}</div></div>
            <div class="profile-meta"><div class="profile-meta-k">Height</div><div class="profile-meta-v">{{ $profile->height ?: 'N/A' }}</div></div>
            <div class="profile-meta"><div class="profile-meta-k">Weight</div><div class="profile-meta-v">{{ $profile->weight ?: 'N/A' }}</div></div>

            <div class="profile-meta"><div class="profile-meta-k">Guardian Name</div><div class="profile-meta-v">{{ $profile->guardian_name ?: 'N/A' }}</div></div>
            <div class="profile-meta"><div class="profile-meta-k">Guardian Contact</div><div class="profile-meta-v">{{ $profile->cellphone ?: ($profile->contact_no ?: 'N/A') }}</div></div>
            <div class="profile-meta"><div class="profile-meta-k">Submitted At</div><div class="profile-meta-v">{{ optional($profile->created_at)->format('M d, Y h:i A') ?: 'N/A' }}</div></div>
        </div>
    </div>

    <div class="profile-card profile-panel" id="docsPanel">
        <div class="doc-grid">
            <div class="doc-file">
                <h4>Medical Certificate (PDF)</h4>
                @if(!empty($profile->medical_certificate))
                    @php($medicalCertificateUrl = route($documentRouteName, ['healthProfile' => $profile->id, 'document' => 'medical_certificate']))
                    <div class="doc-actions">
                        <a class="doc-link" href="{{ $medicalCertificateUrl }}" target="_blank" rel="noopener">
                            <x-outline-icon name="document-text" /> Open
                        </a>
                    </div>
                    <div class="doc-preview"><iframe src="{{ $medicalCertificateUrl }}"></iframe></div>
                @else
                    <div class="doc-missing">No medical certificate uploaded.</div>
                @endif
            </div>

            <div class="doc-file">
                <h4>Medical Assessment Copy</h4>
                @if(!empty($profile->medical_assessment_upload))
                    @php($medicalAssessmentUrl = route($documentRouteName, ['healthProfile' => $profile->id, 'document' => 'medical_assessment_upload']))
                    <div class="doc-actions">
                        <a class="doc-link" href="{{ $medicalAssessmentUrl }}" target="_blank" rel="noopener">
                            <x-outline-icon name="document-text" /> Open
                        </a>
                    </div>
                    <div class="doc-preview"><iframe src="{{ $medicalAssessmentUrl }}"></iframe></div>
                @else
                    <div class="doc-missing">No medical assessment copy uploaded.</div>
                @endif
            </div>

            <div class="doc-file">
                <h4>Chest X-ray Result (PDF)</h4>
                @if(!empty($profile->chest_xray_result))
                    @php($chestXrayUrl = route($documentRouteName, ['healthProfile' => $profile->id, 'document' => 'chest_xray_result']))
                    <div class="doc-actions">
                        <a class="doc-link" href="{{ $chestXrayUrl }}" target="_blank" rel="noopener">
                            <x-outline-icon name="document-text" /> Open
                        </a>
                    </div>
                    <div class="doc-preview"><iframe src="{{ $chestXrayUrl }}"></iframe></div>
                @else
                    <div class="doc-missing">No chest X-ray result uploaded.</div>
                @endif
            </div>

            <div class="doc-file">
                <h4>PWD ID Proof (PDF)</h4>
                @if(($profile->has_disability ?? 'No') !== 'Yes')
                    <div class="doc-missing">Not required (PWD is set to No).</div>
                @elseif(!empty($profile->pwd_id_proof))
                    @php($pwdIdProofUrl = route($documentRouteName, ['healthProfile' => $profile->id, 'document' => 'pwd_id_proof']))
                    <div class="doc-actions">
                        <a class="doc-link" href="{{ $pwdIdProofUrl }}" target="_blank" rel="noopener">
                            <x-outline-icon name="document-text" /> Open
                        </a>
                    </div>
                    <div class="doc-preview"><iframe src="{{ $pwdIdProofUrl }}"></iframe></div>
                @else
                    <div class="doc-missing">PWD is Yes but no proof uploaded.</div>
                @endif
            </div>

            <div class="doc-file">
                <h4>2x2 Student Photo</h4>
                @if(!empty($profile->student_photo))
                    @php($studentPhotoUrl = route($documentRouteName, ['healthProfile' => $profile->id, 'document' => 'student_photo']))
                    <div class="doc-actions">
                        <a class="doc-link" href="{{ $studentPhotoUrl }}" target="_blank" rel="noopener">
                            <x-outline-icon name="eye" /> Open
                        </a>
                    </div>
                    <div class="doc-preview"><img src="{{ $studentPhotoUrl }}" alt="2x2 Student Photo"></div>
                @else
                    <div class="doc-missing">No 2x2 student photo uploaded.</div>
                @endif
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
    document.querySelectorAll('[data-profile-tab-target]').forEach(function (button) {
        button.addEventListener('click', function () {
            const targetId = button.getAttribute('data-profile-tab-target');
            if (!targetId) return;

            document.querySelectorAll('[data-profile-tab-target]').forEach(function (tabButton) {
                tabButton.classList.remove('is-active');
            });
            document.querySelectorAll('.profile-panel').forEach(function (panel) {
                panel.classList.remove('is-active');
            });

            button.classList.add('is-active');
            const targetPanel = document.getElementById(targetId);
            if (targetPanel) {
                targetPanel.classList.add('is-active');
            }
        });
    });
</script>
@endpush
