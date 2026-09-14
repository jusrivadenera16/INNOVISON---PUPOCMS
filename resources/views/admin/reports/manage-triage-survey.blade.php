@extends('layouts.admin')

@section('title', 'Triage Survey')

@push('styles')
<style>
    .triage-survey-page {
        --clinic-maroon: #8f1024;
        --clinic-deep: #6d0718;
        --clinic-border: #ead4d7;
        --clinic-muted: #64748b;
        color: #201016;
    }

    .triage-survey-header,
    .triage-survey-title,
    .triage-survey-status,
    .triage-survey-item {
        display: flex;
        align-items: center;
    }

    .triage-survey-header {
        gap: 14px;
        margin-bottom: 26px;
    }

    .triage-survey-icon {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        width: 46px;
        height: 46px;
        border-radius: 10px;
        color: #facc15;
        background: linear-gradient(135deg, var(--clinic-maroon), var(--clinic-deep));
    }

    .triage-survey-icon svg,
    .triage-survey-item svg {
        width: 20px;
        height: 20px;
        fill: none;
        stroke: currentColor;
        stroke-width: 2;
        stroke-linecap: round;
        stroke-linejoin: round;
    }

    .triage-survey-title {
        gap: 14px;
    }

    .triage-survey-title h1 {
        margin: 0;
        color: var(--clinic-deep);
        font-size: 1.35rem;
        font-weight: 900;
    }

    .triage-survey-title p {
        margin: 4px 0 0;
        color: var(--clinic-muted);
        font-size: .88rem;
        font-weight: 600;
    }

    .triage-survey-status {
        justify-content: space-between;
        gap: 14px;
        margin-bottom: 14px;
        padding: 15px 17px;
        border: 1px solid var(--clinic-border);
        border-radius: 9px;
        background: #fff;
    }

    .triage-survey-status strong {
        color: #2c1820;
        font-size: .9rem;
    }

    .triage-survey-status span {
        min-height: 30px;
        padding: 7px 11px;
        border-radius: 999px;
        color: #92400e;
        background: #fef3c7;
        font-size: .72rem;
        font-weight: 900;
    }

    .triage-survey-list {
        display: grid;
        gap: 10px;
        max-height: clamp(320px, calc(100vh - 360px), 620px);
        overflow-y: auto;
        padding-right: 4px;
    }

    .triage-survey-item {
        gap: 14px;
        min-height: 66px;
        padding: 13px 15px;
        border: 1px solid var(--clinic-border);
        border-radius: 9px;
        background: #fff;
    }

    .triage-survey-item-icon {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        width: 38px;
        height: 38px;
        flex: 0 0 auto;
        border-radius: 9px;
        color: var(--clinic-maroon);
        background: #fff8f5;
    }

    .triage-survey-item strong {
        display: block;
        color: #2c1820;
        font-size: .9rem;
        font-weight: 900;
    }

    .triage-survey-item small {
        display: block;
        margin-top: 3px;
        color: var(--clinic-muted);
        font-size: .78rem;
        font-weight: 600;
    }

    html[data-theme="dark"] .triage-survey-page {
        --clinic-border: rgba(250, 204, 21, .24);
        --clinic-muted: #cbd5e1;
        color: #f8fafc;
    }

    html[data-theme="dark"] .triage-survey-title h1,
    html[data-theme="dark"] .triage-survey-status strong,
    html[data-theme="dark"] .triage-survey-item strong {
        color: #f8fafc;
    }

    html[data-theme="dark"] .triage-survey-status,
    html[data-theme="dark"] .triage-survey-item {
        border-color: rgba(250, 204, 21, .24);
        background: #111827;
    }

    html[data-theme="dark"] .triage-survey-item-icon {
        color: #facc15;
        background: #1e293b;
    }

    @media (max-width: 640px) {
        .triage-survey-status {
            align-items: flex-start;
            flex-direction: column;
        }
    }
</style>
@endpush

@section('content')
<div class="triage-survey-page">
    @include('admin.partials.report-breadcrumb', ['items' => [
        ['label' => 'Settings', 'url' => route('admin.settings')],
        ['label' => 'Medical Configuration', 'url' => route('admin.settings.medical')],
        ['label' => 'Triage Survey'],
    ], 'class' => 'report-breadcrumb--settings-medical'])

    <div class="triage-survey-header">
        <span class="triage-survey-icon" aria-hidden="true"><x-outline-icon name="clipboard-document-list" /></span>
        <div class="triage-survey-title">
            <div>
                <h1>Triage Survey</h1>
                <p>Review the fixed online triage survey structure before response collection is connected.</p>
            </div>
        </div>
    </div>

    <div class="triage-survey-status">
        <strong>Online Triage Survey</strong>
        <span>Static Preview</span>
    </div>

    <div class="triage-survey-list">
        <div class="triage-survey-item">
            <span class="triage-survey-item-icon"><x-outline-icon name="question-mark-circle" /></span>
            <div><strong>Primary concern</strong><small>Identify the patient's main reason for seeking help.</small></div>
        </div>
        <div class="triage-survey-item">
            <span class="triage-survey-item-icon"><x-outline-icon name="heart-pulse" /></span>
            <div><strong>Current symptoms</strong><small>Capture the symptoms that need initial assessment.</small></div>
        </div>
        <div class="triage-survey-item">
            <span class="triage-survey-item-icon"><x-outline-icon name="exclamation-circle" /></span>
            <div><strong>Urgency indicator</strong><small>Record whether the concern needs prompt clinic attention.</small></div>
        </div>
        <div class="triage-survey-item">
            <span class="triage-survey-item-icon"><x-outline-icon name="document-text" /></span>
            <div><strong>Additional notes</strong><small>Provide context for the nurse or doctor before consultation.</small></div>
        </div>
    </div>
</div>
@endsection
