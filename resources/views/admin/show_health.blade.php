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
    .profile-sync-panel {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 14px;
        flex-wrap: wrap;
        border: 1px solid #e2e8f0;
        border-radius: 14px;
        padding: 14px 16px;
        background: #f8fafc;
    }
    .profile-sync-main {
        display: grid;
        gap: 6px;
    }
    .profile-sync-title {
        margin: 0;
        font-size: 13px;
        font-weight: 900;
        color: #64748b;
        letter-spacing: 0.04em;
        text-transform: uppercase;
    }
    .profile-sync-row {
        display: flex;
        align-items: center;
        gap: 10px;
        flex-wrap: wrap;
    }
    .profile-sync-message {
        margin: 0;
        color: #475569;
        font-size: 13px;
        font-weight: 600;
    }
    .profile-sync-button {
        border: 1px solid #8f2230;
        border-radius: 999px;
        background: #70131B;
        color: #facc15;
        min-height: 42px;
        padding: 10px 16px;
        font-size: 13px;
        font-weight: 900;
        cursor: pointer;
        box-shadow: 0 10px 22px rgba(112, 19, 27, 0.18);
        transition: transform .18s ease, background .18s ease, color .18s ease;
    }
    .profile-sync-button:hover,
    .profile-sync-button:focus {
        transform: translateY(-1px);
        background: #facc15;
        color: #70131B;
    }
    .profile-correction-panel {
        border: 1px solid #fecaca;
        border-radius: 14px;
        padding: 16px;
        background: linear-gradient(135deg, #fff7f7, #ffffff);
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 14px;
        flex-wrap: wrap;
    }
    .profile-correction-title {
        margin: 0;
        color: #7f1d2d;
        font-size: 14px;
        font-weight: 900;
        text-transform: uppercase;
        letter-spacing: 0.05em;
    }
    .profile-correction-copy {
        margin: 5px 0 0;
        color: #64748b;
        font-size: 13px;
        line-height: 1.55;
        max-width: 680px;
    }
    .profile-correction-button,
    .correction-submit {
        border: 1px solid #8f2230;
        border-radius: 999px;
        background: #70131B;
        color: #facc15;
        min-height: 42px;
        padding: 10px 16px;
        font-size: 13px;
        font-weight: 900;
        cursor: pointer;
        box-shadow: 0 10px 22px rgba(112, 19, 27, 0.18);
        transition: transform .18s ease, background .18s ease, color .18s ease;
    }
    .profile-correction-button:hover,
    .profile-correction-button:focus,
    .correction-submit:hover,
    .correction-submit:focus {
        transform: translateY(-1px);
        background: #facc15;
        color: #70131B;
    }
    .correction-modal {
        display: none;
        position: fixed;
        inset: 0;
        z-index: 2147482500;
        background: rgba(15, 23, 42, 0.62);
        align-items: center;
        justify-content: center;
        padding: 20px;
    }
    .correction-modal.is-open {
        display: flex;
    }
    .correction-card {
        width: min(720px, 100%);
        border-radius: 18px;
        background: #ffffff;
        border: 1px solid #fecaca;
        border-bottom: 4px solid #facc15;
        overflow: hidden;
        box-shadow: 0 24px 60px rgba(15, 23, 42, 0.28);
    }
    .correction-head {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 14px;
        padding: 18px 20px;
        background: linear-gradient(135deg, #7f1d1d, #b91c1c);
        color: #ffffff;
    }
    .correction-head h3 {
        margin: 0;
        color: #ffffff;
        font-size: 18px;
        font-weight: 900;
    }
    .correction-head p {
        margin: 4px 0 0;
        color: rgba(255, 255, 255, 0.88);
        font-size: 13px;
    }
    .correction-close {
        width: 38px;
        height: 38px;
        border-radius: 999px;
        border: 1px solid rgba(255,255,255,0.24);
        background: rgba(112, 19, 27, 0.45);
        color: #ffffff;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        cursor: pointer;
    }
    .correction-close svg {
        width: 18px;
        height: 18px;
    }
    .correction-body {
        padding: 20px;
        display: grid;
        gap: 16px;
    }
    .correction-note {
        border: 1px solid #fed7aa;
        border-radius: 12px;
        background: #fffbeb;
        color: #7c2d12;
        padding: 12px 14px;
        font-size: 13px;
        font-weight: 700;
        line-height: 1.55;
    }
    .correction-doc-grid {
        display: grid;
        grid-template-columns: repeat(2, minmax(0, 1fr));
        gap: 10px;
    }
    .correction-doc-option {
        display: flex;
        align-items: center;
        gap: 10px;
        border: 1px solid #fecaca;
        border-radius: 12px;
        padding: 12px;
        color: #111827;
        font-size: 13px;
        font-weight: 800;
        background: #fff7f7;
    }
    .correction-doc-option input {
        width: 18px;
        height: 18px;
        accent-color: #7f1d2d;
    }
    .correction-field label {
        display: block;
        margin-bottom: 7px;
        color: #334155;
        font-size: 12px;
        font-weight: 900;
        text-transform: uppercase;
        letter-spacing: 0.04em;
    }
    .correction-field textarea {
        width: 100%;
        min-height: 120px;
        border: 1px solid #cbd5e1;
        border-radius: 12px;
        padding: 12px;
        color: #111827;
        font-size: 14px;
        resize: vertical;
    }
    .correction-actions {
        display: flex;
        justify-content: flex-end;
        gap: 10px;
        flex-wrap: wrap;
    }
    .correction-cancel {
        border: 1px solid #cbd5e1;
        border-radius: 999px;
        background: #ffffff;
        color: #334155;
        min-height: 42px;
        padding: 10px 16px;
        font-size: 13px;
        font-weight: 900;
        cursor: pointer;
    }
    .profile-panel { display: none; }
    .profile-panel.is-active { display: block; }

    .profile-grid { display: grid; grid-template-columns: repeat(3, minmax(0, 1fr)); gap: 12px; }
    .profile-meta { background: #f8fafc; border: 1px solid #e2e8f0; border-radius: 10px; padding: 10px 12px; }
    .profile-meta-k { font-size: 12px; color: #64748b; text-transform: uppercase; font-weight: 700; margin-bottom: 4px; }
    .profile-meta-v { font-size: 15px; color: #0f172a; font-weight: 700; word-break: break-word; }
    .profile-meta.is-wide { grid-column: span 2; }
    .profile-meta.is-full { grid-column: 1 / -1; }

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
    [data-theme="dark"] .profile-sync-panel {
        background: #111827;
        border-color: #334155;
    }
    [data-theme="dark"] .profile-correction-panel,
    [data-theme="dark"] .correction-card {
        background: #111827;
        border-color: rgba(248, 113, 113, 0.45);
    }
    [data-theme="dark"] .correction-doc-option {
        background: #0f172a;
        border-color: rgba(248, 113, 113, 0.36);
        color: #f8fafc;
    }
    [data-theme="dark"] .correction-field label,
    [data-theme="dark"] .profile-correction-copy {
        color: #cbd5e1;
    }
    [data-theme="dark"] .correction-field textarea {
        background: #0f172a;
        border-color: #334155;
        color: #f8fafc;
    }
    [data-theme="dark"] .profile-sync-title,
    [data-theme="dark"] .profile-sync-message {
        color: #cbd5e1;
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
        .profile-meta.is-wide,
        .profile-meta.is-full { grid-column: auto; }
    }
</style>
@endpush

@section('content')
@php
    $formatProfileDate = function ($value) {
        if (blank($value)) {
            return 'N/A';
        }

        try {
            return \Carbon\Carbon::parse($value)->format('M d, Y');
        } catch (\Throwable $exception) {
            return (string) $value;
        }
    };

    $formatProfileList = function ($value) {
        if (blank($value)) {
            return 'N/A';
        }

        if (is_array($value)) {
            $items = collect($value)
                ->filter(fn ($item) => filled($item))
                ->values();

            return $items->isNotEmpty() ? $items->implode(', ') : 'N/A';
        }

        return (string) $value;
    };

    $medicineAllergies = $formatProfileList($profile->medicine_allergies);
    $medicalHistory = $formatProfileList($profile->medical_history);
    $vaccineHistory = collect($profile->vaccine_history ?? [])
        ->map(function ($dose, $key) use ($formatProfileDate) {
            if (!is_array($dose)) {
                return filled($dose) ? (string) $dose : null;
            }

            $label = \Illuminate\Support\Str::of((string) $key)->replace('_', ' ')->title();
            $date = $formatProfileDate($dose['date'] ?? null);
            $brand = trim((string) ($dose['brand'] ?? ''));
            $details = collect([$date !== 'N/A' ? $date : null, $brand !== '' ? $brand : null])
                ->filter()
                ->implode(' - ');

            return $details !== '' ? "{$label}: {$details}" : null;
        })
        ->filter()
        ->values()
        ->implode('; ');
    $vaccineHistory = $vaccineHistory !== '' ? $vaccineHistory : 'N/A';

    $medicalConditionValue = trim((string) ($profile->medical_condition_remarks ?? ''));
    if ($medicalConditionValue === '') {
        $medicalConditionValue = $profile->hasMedicalCondition()
            ? 'With Condition'
            : 'No Medical Condition Recorded';
    }

    $profileStatusRaw = trim((string) ($profile->clearance_status ?? ''));
    $profileStatusNormalized = in_array($profileStatusRaw, ['Pending', 'For Verification'], true) ? 'Pending' : $profileStatusRaw;
    $profileStatusClass = match ($profileStatusNormalized) {
        'Issued', 'Fully Cleared' => 'profile-status-issued',
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
    $puptasSyncRaw = strtolower(trim((string) ($profile->puptas_sync_status ?? '')));
    $puptasReference = strtoupper(trim((string) ($profile->reference_number ?: $profile->student_number ?: optional($profile->user)->student_number)));
    $isLocalPuptasReference = $puptasReference === ''
        || \Illuminate\Support\Str::startsWith($puptasReference, ['CLN-', 'LOC-', 'TEST-LOCAL']);
    if ($puptasSyncRaw === '' && $isLocalPuptasReference) {
        $puptasSyncRaw = 'not_applicable';
    }
    $puptasSyncLabel = match ($puptasSyncRaw) {
        'synced' => 'Synced to PUPTAS',
        'failed' => 'Sync Failed',
        'syncing' => 'Syncing',
        'pending' => 'Pending Sync',
        'not_applicable' => 'Not Applicable',
        'missing_reference_number' => 'Missing Reference',
        default => 'Not Synced',
    };
    $puptasSyncClass = match ($puptasSyncRaw) {
        'synced' => 'profile-status-issued',
        'failed', 'missing_reference_number' => 'profile-status-rejected',
        'syncing', 'pending' => 'profile-status-pending',
        'not_applicable' => 'profile-status-default',
        default => 'profile-status-pending',
    };
    $canResyncPuptas = in_array($profileStatusNormalized, ['Issued', 'Fully Cleared'], true)
        && !in_array($puptasSyncRaw, ['synced', 'not_applicable'], true);
    $canRequestFileCorrection = in_array($profileStatusNormalized, ['Issued', 'Fully Cleared'], true);
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
        <div class="profile-sync-panel">
            <div class="profile-sync-main">
                <p class="profile-sync-title">PUPTAS Sync Status</p>
                <div class="profile-sync-row">
                    <span class="profile-status-badge {{ $puptasSyncClass }}">
                        @if($puptasSyncRaw === 'synced')
                            <x-outline-icon name="check" />
                        @elseif(in_array($puptasSyncRaw, ['failed', 'missing_reference_number'], true))
                            <x-outline-icon name="exclamation-triangle" />
                        @elseif(in_array($puptasSyncRaw, ['syncing', 'pending'], true))
                            <x-outline-icon name="clock" />
                        @else
                            <x-outline-icon name="information-circle" />
                        @endif
                        {{ $puptasSyncLabel }}
                    </span>
                    @if($profile->puptas_synced_at)
                        <span class="profile-sync-message">Last synced: {{ $profile->puptas_synced_at->format('M d, Y h:i A') }}</span>
                    @endif
                </div>
                @if(filled($profile->puptas_sync_message))
                    <p class="profile-sync-message">{{ $profile->puptas_sync_message }}</p>
                @endif
            </div>

            @if($canResyncPuptas)
                <form method="POST" action="{{ route('admin.health_profile.resync_puptas', $profile->id) }}">
                    @csrf
                    <button type="submit" class="profile-sync-button">Resync to PUPTAS</button>
                </form>
            @endif
        </div>
    </div>

    @if($canRequestFileCorrection)
        <div class="profile-card">
            <div class="profile-correction-panel">
                <div>
                    <p class="profile-correction-title">File Correction</p>
                    <p class="profile-correction-copy">Request replacement of a specific uploaded requirement without deleting approval history or PUPTAS sync records.</p>
                </div>
                <button type="button" class="profile-correction-button" id="openCorrectionModal">
                    Request File Correction
                </button>
            </div>
        </div>
    @endif

    <div class="profile-card">
        <div class="profile-switch-head">
            <div class="profile-switch" role="tablist" aria-label="Health profile sections">
                <button type="button" class="profile-tab is-active" data-profile-tab-target="summaryPanel">Personal Information</button>
                <button type="button" class="profile-tab" data-profile-tab-target="healthPanel">Health Profile</button>
                <button type="button" class="profile-tab" data-profile-tab-target="docsPanel">Uploaded Documents</button>
            </div>
            <span class="profile-status-badge {{ $profileStatusClass }}">
                @if(in_array($profileStatusNormalized, ['Issued', 'Fully Cleared'], true))
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

    <div class="profile-card profile-panel" id="healthPanel">
        <div class="profile-grid">
            <div class="profile-meta"><div class="profile-meta-k">Medical Condition</div><div class="profile-meta-v">{{ $medicalConditionValue }}</div></div>
            <div class="profile-meta"><div class="profile-meta-k">Physical Assessment</div><div class="profile-meta-v">{{ $profile->physical_assessment_status ?: 'N/A' }}</div></div>
            <div class="profile-meta"><div class="profile-meta-k">Documents Valid</div><div class="profile-meta-v">{{ $profile->documents_valid ? 'Yes' : 'No' }}</div></div>

            <div class="profile-meta"><div class="profile-meta-k">Assessment Date</div><div class="profile-meta-v">{{ $formatProfileDate($profile->assessment_date) }}</div></div>
            <div class="profile-meta"><div class="profile-meta-k">Verified At</div><div class="profile-meta-v">{{ $formatProfileDate($profile->verified_at) }}</div></div>
            <div class="profile-meta"><div class="profile-meta-k">Pending Reason</div><div class="profile-meta-v">{{ $profile->pending_reason ?: 'N/A' }}</div></div>

            <div class="profile-meta"><div class="profile-meta-k">Blood Pressure</div><div class="profile-meta-v">{{ $profile->blood_pressure ?: 'N/A' }}</div></div>
            <div class="profile-meta"><div class="profile-meta-k">Pulse Rate</div><div class="profile-meta-v">{{ $profile->pulse_rate ?: 'N/A' }}</div></div>
            <div class="profile-meta"><div class="profile-meta-k">Respiratory Rate</div><div class="profile-meta-v">{{ $profile->respiratory_rate ?: 'N/A' }}</div></div>
            <div class="profile-meta"><div class="profile-meta-k">Temperature</div><div class="profile-meta-v">{{ $profile->temperature ?: 'N/A' }}</div></div>
            <div class="profile-meta"><div class="profile-meta-k">COVID Positive</div><div class="profile-meta-v">{{ $profile->covid_positive ?: 'N/A' }}</div></div>
            <div class="profile-meta"><div class="profile-meta-k">COVID Positive Date</div><div class="profile-meta-v">{{ $formatProfileDate($profile->covid_positive_date) }}</div></div>

            <div class="profile-meta"><div class="profile-meta-k">Known Medical Illness</div><div class="profile-meta-v">{{ $profile->has_illness ?: 'N/A' }}</div></div>
            <div class="profile-meta is-wide"><div class="profile-meta-k">Medical History</div><div class="profile-meta-v">{{ $medicalHistory }}</div></div>
            <div class="profile-meta is-full"><div class="profile-meta-k">Other Illness / Medical Notes</div><div class="profile-meta-v">{{ $profile->other_illness ?: 'N/A' }}</div></div>

            <div class="profile-meta"><div class="profile-meta-k">Has Disability</div><div class="profile-meta-v">{{ $profile->has_disability ?: 'N/A' }}</div></div>
            <div class="profile-meta is-wide"><div class="profile-meta-k">Disability Type</div><div class="profile-meta-v">{{ $profile->disability_type ?: 'N/A' }}</div></div>

            <div class="profile-meta"><div class="profile-meta-k">No Known Allergies</div><div class="profile-meta-v">{{ $profile->no_allergies ? 'Yes' : 'No' }}</div></div>
            <div class="profile-meta"><div class="profile-meta-k">Food Allergies</div><div class="profile-meta-v">{{ $profile->food_allergies ?: 'N/A' }}</div></div>
            <div class="profile-meta"><div class="profile-meta-k">Medicine Allergies</div><div class="profile-meta-v">{{ $medicineAllergies }}</div></div>
            <div class="profile-meta is-full"><div class="profile-meta-k">Other Medicine Allergies</div><div class="profile-meta-v">{{ $profile->other_med_allergies ?: 'N/A' }}</div></div>

            <div class="profile-meta"><div class="profile-meta-k">Smoker</div><div class="profile-meta-v">{{ $profile->is_smoker ?: 'N/A' }}</div></div>
            <div class="profile-meta"><div class="profile-meta-k">Alcohol Drinker</div><div class="profile-meta-v">{{ $profile->is_drinker ?: 'N/A' }}</div></div>
            <div class="profile-meta"><div class="profile-meta-k">COVID Vaccinated</div><div class="profile-meta-v">{{ $profile->covid_vaccinated ?: 'N/A' }}</div></div>
            <div class="profile-meta is-full"><div class="profile-meta-k">Vaccination History</div><div class="profile-meta-v">{{ $vaccineHistory }}</div></div>

            <div class="profile-meta"><div class="profile-meta-k">Medical Certificate Doctor</div><div class="profile-meta-v">{{ $profile->doctor_name ?: ($profile->medical_certificate_issued_by ?: 'N/A') }}</div></div>
            <div class="profile-meta"><div class="profile-meta-k">Medical Certificate Date</div><div class="profile-meta-v">{{ $formatProfileDate($profile->med_cert_date ?: $profile->medical_certificate_issued_at) }}</div></div>
            <div class="profile-meta"><div class="profile-meta-k">Medical Certificate Result</div><div class="profile-meta-v">{{ $profile->med_cert_findings ?: 'N/A' }}</div></div>
            <div class="profile-meta is-full"><div class="profile-meta-k">Student Declared Medical Findings</div><div class="profile-meta-v">{{ $profile->med_cert_findings_details ?: 'N/A' }}</div></div>

            <div class="profile-meta"><div class="profile-meta-k">Chest X-ray Date</div><div class="profile-meta-v">{{ $formatProfileDate($profile->xray_date ?: $profile->chest_xray_date) }}</div></div>
            <div class="profile-meta is-wide"><div class="profile-meta-k">Chest X-ray Result</div><div class="profile-meta-v">{{ $profile->xray_findings ?: ($profile->chest_xray_result_text ?: 'N/A') }}</div></div>
            <div class="profile-meta is-full"><div class="profile-meta-k">Student Declared X-ray Findings</div><div class="profile-meta-v">{{ $profile->xray_findings_details ?: 'N/A' }}</div></div>

            <div class="profile-meta is-full"><div class="profile-meta-k">Assessment Remarks</div><div class="profile-meta-v">{{ $profile->assessment_remarks ?: 'N/A' }}</div></div>
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
                <h4>Health Declaration</h4>
                @if(!empty($profile->health_declaration))
                    @php($healthDeclarationUrl = route($documentRouteName, ['healthProfile' => $profile->id, 'document' => 'health_declaration']))
                    <div class="doc-actions">
                        <a class="doc-link" href="{{ $healthDeclarationUrl }}" target="_blank" rel="noopener">
                            <x-outline-icon name="document-text" /> Open
                        </a>
                    </div>
                    <div class="doc-preview"><iframe src="{{ $healthDeclarationUrl }}"></iframe></div>
                @else
                    <div class="doc-missing">No health declaration uploaded.</div>
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

@if($canRequestFileCorrection)
    <div class="correction-modal" id="correctionModal" aria-hidden="true">
        <div class="correction-card">
            <div class="correction-head">
                <div>
                    <h3>Request File Correction</h3>
                    <p>Select only the file/s that need replacement. The student will see a reupload prompt.</p>
                </div>
                <button type="button" class="correction-close" id="closeCorrectionModal" aria-label="Close correction modal">
                    <x-outline-icon name="x-mark" />
                </button>
            </div>
            <form method="POST" action="{{ route('admin.health_profile.request_resubmission', $profile->id) }}" class="correction-body">
                @csrf
                <div class="correction-note">
                    This will move the record to Pending Resubmission while keeping the original approval date, approver, and PUPTAS sync history.
                </div>
                <div class="correction-doc-grid">
                    <label class="correction-doc-option">
                        <input type="checkbox" name="resubmission_required_documents[]" value="student_photo">
                        <span>2x2 Student Photo</span>
                    </label>
                    <label class="correction-doc-option">
                        <input type="checkbox" name="resubmission_required_documents[]" value="health_declaration">
                        <span>Declaration Form</span>
                    </label>
                    <label class="correction-doc-option">
                        <input type="checkbox" name="resubmission_required_documents[]" value="medical_certificate">
                        <span>Medical Certificate</span>
                    </label>
                    <label class="correction-doc-option">
                        <input type="checkbox" name="resubmission_required_documents[]" value="chest_xray_result">
                        <span>Chest X-ray Result</span>
                    </label>
                    <label class="correction-doc-option">
                        <input type="checkbox" name="resubmission_required_documents[]" value="pwd_id_proof">
                        <span>PWD ID Proof</span>
                    </label>
                </div>
                <div class="correction-field">
                    <label for="correctionReason">Reason</label>
                    <textarea id="correctionReason" name="pending_reason" required placeholder="Example: Medical certificate has no signature and must be replaced."></textarea>
                </div>
                <div class="correction-actions">
                    <button type="button" class="correction-cancel" id="cancelCorrectionModal">Cancel</button>
                    <button type="submit" class="correction-submit">Send Correction Request</button>
                </div>
            </form>
        </div>
    </div>
@endif
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

    const correctionModal = document.getElementById('correctionModal');
    const openCorrectionModal = document.getElementById('openCorrectionModal');
    const closeCorrectionModal = document.getElementById('closeCorrectionModal');
    const cancelCorrectionModal = document.getElementById('cancelCorrectionModal');

    function setCorrectionModal(open) {
        if (!correctionModal) return;
        correctionModal.classList.toggle('is-open', open);
        correctionModal.setAttribute('aria-hidden', open ? 'false' : 'true');
    }

    openCorrectionModal?.addEventListener('click', function () {
        setCorrectionModal(true);
    });

    closeCorrectionModal?.addEventListener('click', function () {
        setCorrectionModal(false);
    });

    cancelCorrectionModal?.addEventListener('click', function () {
        setCorrectionModal(false);
    });

    correctionModal?.addEventListener('click', function (event) {
        if (event.target === correctionModal) {
            setCorrectionModal(false);
        }
    });
</script>
@endpush
