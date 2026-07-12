@extends('layouts.admin')

@section('title', 'Digital Logbook')

@push('styles')
<style>
    .digital-logbook-shell {
        width: min(1120px, calc(100% - 32px));
        margin: 28px auto;
        color: #0f172a;
    }

    .digital-logbook-frame {
        position: relative;
        overflow: hidden;
        border-radius: 18px;
        border: 1px solid rgba(127, 29, 45, 0.18);
        background: #ffffff;
        padding: 24px;
        box-shadow: 0 18px 42px rgba(15, 23, 42, 0.08);
    }

    .digital-logbook-frame::before {
        content: "";
        position: absolute;
        top: 0;
        left: 18px;
        right: 18px;
        height: 5px;
        border-radius: 0 0 999px 999px;
        background: #70131B;
        pointer-events: none;
    }

    .digital-logbook-header {
        display: flex;
        align-items: flex-start;
        justify-content: space-between;
        gap: 18px;
        padding-bottom: 22px;
        border-bottom: 1px solid rgba(148, 163, 184, 0.22);
        margin-bottom: 24px;
    }

    .digital-logbook-title {
        margin: 0 0 10px;
        color: #0f172a;
        font-size: 22px;
        font-weight: 800;
        line-height: 1.25;
    }

    .digital-logbook-copy {
        margin: 0;
        color: #334155;
        font-size: 16px;
        font-weight: 600;
        line-height: 1.45;
        max-width: 760px;
    }

    .digital-logbook-back {
        min-height: 42px;
        border-radius: 12px;
        border: 1px solid rgba(112, 19, 27, 0.24);
        background: #ffffff;
        color: #70131B;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        padding: 0 16px;
        font-size: 13px;
        font-weight: 900;
        text-decoration: none;
        white-space: nowrap;
        box-shadow: 0 10px 24px rgba(112, 19, 27, 0.10);
        transition: transform .18s ease, border-color .18s ease, background .18s ease, color .18s ease;
    }

    .digital-logbook-back:hover {
        transform: translateY(-1px);
        border-color: #facc15;
        background: #facc15;
        color: #70131B;
    }

    .digital-logbook-grid {
        display: grid;
        grid-template-columns: repeat(2, minmax(0, 1fr));
        gap: 20px;
    }

    .digital-logbook-card {
        position: relative;
        overflow: hidden;
        min-height: 250px;
        border-radius: 16px;
        border: 1px solid rgba(112, 19, 27, 0.22);
        background: #70131B;
        color: #ffffff;
        padding: 20px;
        text-decoration: none;
        display: flex;
        flex-direction: column;
        justify-content: space-between;
        align-items: flex-start;
        gap: 20px;
        box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.10);
        transition: transform .24s ease, box-shadow .24s ease, background .24s ease, border-color .24s ease;
    }

    .digital-logbook-card > * {
        position: relative;
        z-index: 2;
    }

    .digital-logbook-card::after {
        content: "";
        position: absolute;
        top: -45%;
        bottom: -45%;
        left: -95%;
        width: 55%;
        opacity: 0;
        background: linear-gradient(105deg, rgba(255,255,255,0) 0%, rgba(255,248,196,.42) 48%, rgba(255,255,255,0) 100%);
        transform: translateX(0) skewX(-18deg);
        pointer-events: none;
        z-index: 1;
    }

    .digital-logbook-card:hover {
        background: #facc15;
        color: #70131B;
        transform: translateY(-6px);
        border-color: #facc15;
        box-shadow:
            0 0 0 3px rgba(250, 204, 21, 0.12),
            0 20px 30px rgba(139, 0, 0, 0.22);
    }

    .digital-logbook-card:hover::after {
        animation: digitalLogbookSweep .92s ease both;
    }

    @keyframes digitalLogbookSweep {
        0% { opacity: 0; transform: translateX(0) skewX(-18deg); }
        18%, 72% { opacity: .7; }
        100% { opacity: 0; transform: translateX(520%) skewX(-18deg); }
    }

    .digital-logbook-card-label {
        margin: 0 0 8px;
        color: rgba(255, 255, 255, .80);
        font-size: 12px;
        font-weight: 900;
        text-transform: uppercase;
        letter-spacing: .04em;
        transition: color .2s ease;
    }

    .digital-logbook-card-title {
        margin: 0;
        color: #ffffff;
        font-size: 19px;
        font-weight: 900;
        line-height: 1.15;
        transition: color .2s ease;
    }

    .digital-logbook-card-copy {
        margin: 10px 0 0;
        color: rgba(255, 255, 255, .92);
        font-size: 14px;
        line-height: 1.48;
        max-width: 420px;
        font-weight: 700;
        transition: color .2s ease;
    }

    .digital-logbook-card-footer {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 12px;
    }

    .digital-logbook-chip {
        display: none;
        align-items: center;
        justify-content: center;
        border-radius: 8px;
        background: rgba(255, 255, 255, 0.12);
        color: #ffffff;
        padding: 6px 12px;
        font-size: 11px;
        font-weight: 800;
        line-height: 1;
    }

    .digital-logbook-icon {
        width: 58px;
        height: 58px;
        border-radius: 14px;
        color: #facc15;
        background: rgba(255, 255, 255, 0.12);
        border: 1px solid rgba(255, 255, 255, 0.18);
        margin-bottom: 18px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        transition: background .2s ease, color .2s ease, transform .2s ease;
    }

    .digital-logbook-icon svg {
        width: 30px;
        height: 30px;
    }

    .digital-logbook-arrow {
        position: absolute;
        top: 20px;
        right: 18px;
        width: 30px;
        height: 30px;
        border-radius: 999px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        color: #ffffff;
        background: rgba(255, 255, 255, .10);
        border: 1px solid rgba(255, 255, 255, .28);
        transition: background .2s ease, color .2s ease, border-color .2s ease, transform .2s ease;
    }

    .digital-logbook-arrow svg {
        width: 18px;
        height: 18px;
    }

    .digital-logbook-card:hover .digital-logbook-card-label,
    .digital-logbook-card:hover .digital-logbook-card-title,
    .digital-logbook-card:hover .digital-logbook-card-copy {
        color: #70131B;
    }

    .digital-logbook-card:hover .digital-logbook-icon {
        background: rgba(112, 19, 27, 0.10);
        border-color: rgba(112, 19, 27, 0.14);
    }

    .digital-logbook-card:hover .digital-logbook-icon {
        color: #facc15;
    }

    .digital-logbook-card:hover .digital-logbook-arrow {
        color: #70131B;
        background: rgba(112, 19, 27, .10);
        border-color: rgba(112, 19, 27, .22);
        transform: translateX(4px);
    }

    html[data-theme="dark"] .digital-logbook-frame {
        background: linear-gradient(135deg, rgba(112, 19, 27, 0.68) 0%, rgba(86, 16, 26, 0.64) 48%, rgba(44, 14, 18, 0.72) 100%);
        border-color: rgba(255, 255, 255, 0.12);
        box-shadow: 0 20px 44px rgba(0, 0, 0, 0.34);
    }

    html[data-theme="dark"] .digital-logbook-title,
    html[data-theme="dark"] .digital-logbook-copy {
        color: #ffffff;
    }

    html[data-theme="dark"] .digital-logbook-header {
        border-bottom-color: rgba(255, 255, 255, 0.12);
    }

    html[data-theme="dark"] .digital-logbook-back {
        background: rgba(255, 255, 255, 0.08);
        border-color: rgba(255, 255, 255, 0.22);
        color: #ffffff;
    }

    html[data-theme="dark"] .digital-logbook-back:hover {
        background: #facc15;
        border-color: #facc15;
        color: #70131B;
    }

    html[data-theme="dark"] .digital-logbook-card {
        background: linear-gradient(135deg, #70131B, #8f2230);
        border-color: rgba(250, 204, 21, 0.44);
    }

    html[data-theme="dark"] .digital-logbook-card:hover {
        background: #facc15;
    }

    @media (max-width: 820px) {
        .digital-logbook-header {
            flex-direction: column;
        }

        .digital-logbook-back {
            width: 100%;
        }

        .digital-logbook-grid {
            grid-template-columns: 1fr;
        }
    }
</style>
@endpush

@section('content')
@php
    $role = \App\Models\User::normalizeRole(optional(auth()->user())->user_role ?? '');
    $reportsHomeUrl = $role === \App\Models\User::ROLE_ADMIN ? url('/assistant/reports') : url('/admin/reports');
    $dailyTreatmentRecordUrl = $role === \App\Models\User::ROLE_ADMIN ? url('/assistant/reports/daily-treatment-record') : url('/admin/reports/daily-treatment-record');
    $healthFormsLogbookUrl = $role === \App\Models\User::ROLE_ADMIN ? url('/assistant/reports/health-forms-logbook') : url('/admin/reports/health-forms-logbook');
@endphp

<div class="digital-logbook-shell">
    <section class="digital-logbook-frame">
        <header class="digital-logbook-header">
            <div>
                <h1 class="digital-logbook-title">Digital Logbook</h1>
                <p class="digital-logbook-copy">Open the clinic logbooks used to monitor consultations, treatment records, and health form review visits.</p>
            </div>
            <a href="{{ $reportsHomeUrl }}" class="digital-logbook-back">&larr; Back to Reports</a>
        </header>

        <div class="digital-logbook-grid">
            <a href="{{ $dailyTreatmentRecordUrl }}" class="digital-logbook-card">
                <div>
                    <span class="digital-logbook-icon"><x-outline-icon name="clipboard-document-list" /></span>
                    <div class="digital-logbook-card-label">Form B</div>
                    <h2 class="digital-logbook-card-title">Daily Treatment Record</h2>
                    <p class="digital-logbook-card-copy">Official treatment logbook for consultations, medicines dispensed, vitals, complaints, and attending staff.</p>
                </div>
                <div class="digital-logbook-card-footer">
                    <span class="digital-logbook-chip">DTR</span>
                    <span class="digital-logbook-arrow"><x-outline-icon name="chevron-right" /></span>
                </div>
            </a>

            <a href="{{ $healthFormsLogbookUrl }}" class="digital-logbook-card">
                <div>
                    <span class="digital-logbook-icon"><x-outline-icon name="document-text" /></span>
                    <div class="digital-logbook-card-label">Applicant Review</div>
                    <h2 class="digital-logbook-card-title">Health Form Logbook</h2>
                    <p class="digital-logbook-card-copy">Monitor approved health forms, final review visits, approver, condition, and sync status.</p>
                </div>
                <div class="digital-logbook-card-footer">
                    <span class="digital-logbook-chip">HRL</span>
                    <span class="digital-logbook-arrow"><x-outline-icon name="chevron-right" /></span>
                </div>
            </a>
        </div>
    </section>
</div>
@endsection
