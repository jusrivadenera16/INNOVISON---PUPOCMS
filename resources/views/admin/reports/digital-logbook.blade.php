@extends('layouts.admin')

@section('title', 'Digital Logbook')

@push('styles')
<style>
    .digital-logbook-shell {
        width: min(1120px, calc(100% - 32px));
        margin: 28px auto;
        display: flex;
        flex-direction: column;
        gap: 18px;
    }

    .digital-logbook-head {
        border-radius: 20px;
        border: 1px solid rgba(127, 29, 45, 0.16);
        border-bottom: 3px solid #8B0000;
        background:
            radial-gradient(circle at 96% 4%, rgba(250, 204, 21, .18), transparent 30%),
            linear-gradient(180deg, #ffffff 0%, #fffaf2 100%);
        padding: 24px;
        box-shadow: 0 18px 42px rgba(15, 23, 42, 0.08);
        display: flex;
        justify-content: space-between;
        gap: 18px;
        align-items: center;
    }

    .digital-logbook-kicker {
        margin: 0 0 8px;
        color: #8B0000;
        font-size: 12px;
        font-weight: 900;
        letter-spacing: 0.08em;
        text-transform: uppercase;
    }

    .digital-logbook-title {
        margin: 0;
        color: #0f172a;
        font-size: clamp(1.85rem, 4vw, 2.6rem);
        font-weight: 900;
    }

    .digital-logbook-copy {
        margin: 8px 0 0;
        color: #334155;
        font-size: 14px;
        line-height: 1.6;
        max-width: 680px;
    }

    .digital-logbook-back {
        min-height: 44px;
        border-radius: 12px;
        border: 1px solid rgba(139, 0, 0, 0.26);
        background: #ffffff;
        color: #8B0000;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        padding: 0 18px;
        font-size: 13px;
        font-weight: 900;
        text-decoration: none;
        white-space: nowrap;
        box-shadow: 0 10px 24px rgba(0, 0, 0, 0.14);
    }

    .digital-logbook-grid {
        display: grid;
        grid-template-columns: repeat(2, minmax(0, 1fr));
        gap: 18px;
    }

    .digital-logbook-card {
        position: relative;
        overflow: hidden;
        min-height: 220px;
        border-radius: 18px;
        border: 1px solid rgba(127, 29, 45, 0.18);
        border-top: 5px solid #8B0000;
        background:
            radial-gradient(circle at 92% 86%, rgba(250, 204, 21, .18), transparent 26%),
            linear-gradient(180deg, #ffffff 0%, #fffafa 100%);
        padding: 24px;
        text-decoration: none;
        color: #0f172a;
        box-shadow: 0 18px 36px rgba(15, 23, 42, 0.08);
        display: flex;
        flex-direction: column;
        justify-content: space-between;
        gap: 28px;
        transition: transform .18s ease, box-shadow .18s ease, border-color .18s ease, background .18s ease;
    }

    .digital-logbook-card::after {
        content: "";
        position: absolute;
        top: -45%;
        left: -82%;
        width: 42%;
        height: 176%;
        background: linear-gradient(120deg, transparent 0%, rgba(255, 244, 180, .16) 34%, rgba(255, 244, 180, .44) 50%, rgba(255, 244, 180, .16) 66%, transparent 100%);
        transform: skewX(-18deg);
        transition: left .28s ease;
        pointer-events: none;
    }

    .digital-logbook-card:hover {
        transform: translateY(-2px);
        border-color: rgba(139, 0, 0, 0.30);
        box-shadow: 0 24px 44px rgba(127, 29, 45, 0.16);
    }

    .digital-logbook-card:hover::after {
        left: 130%;
    }

    .digital-logbook-card-label {
        color: #8B0000;
        font-size: 12px;
        font-weight: 900;
        text-transform: uppercase;
        letter-spacing: 0.08em;
        margin-bottom: 10px;
    }

    .digital-logbook-card-title {
        font-size: 1.45rem;
        font-weight: 900;
        color: #0f172a;
        margin-bottom: 8px;
    }

    .digital-logbook-card-copy {
        color: #334155;
        font-size: 14px;
        line-height: 1.65;
        max-width: 480px;
    }

    .digital-logbook-card-foot {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 14px;
    }

    .digital-logbook-chip {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        border-radius: 999px;
        background: #fff7ed;
        border: 1px solid rgba(139, 0, 0, 0.20);
        color: #8B0000;
        padding: 8px 12px;
        font-size: 12px;
        font-weight: 900;
        text-transform: uppercase;
        letter-spacing: 0.04em;
    }

    .digital-logbook-icon {
        width: 46px;
        height: 46px;
        border-radius: 14px;
        background: #8B0000;
        color: #ffffff;
        display: inline-flex;
        align-items: center;
        justify-content: center;
    }

    .digital-logbook-icon svg {
        width: 22px;
        height: 22px;
    }

    @media (max-width: 820px) {
        .digital-logbook-head {
            align-items: flex-start;
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
    <header class="digital-logbook-head">
        <div>
            <p class="digital-logbook-kicker">Clinic Monitoring</p>
            <h1 class="digital-logbook-title">Digital Logbook</h1>
            <p class="digital-logbook-copy">Open the clinic logbooks used to monitor consultations, treatment records, and health form review visits.</p>
        </div>
        <a href="{{ $reportsHomeUrl }}" class="digital-logbook-back">&larr; Back to Reports</a>
    </header>

    <section class="digital-logbook-grid">
        <a href="{{ $dailyTreatmentRecordUrl }}" class="digital-logbook-card">
            <div>
                <div class="digital-logbook-card-label">Form B</div>
                <div class="digital-logbook-card-title">Daily Treatment Record</div>
                <p class="digital-logbook-card-copy">Official treatment logbook for consultations, medicines dispensed, vitals, complaints, and attending staff.</p>
            </div>
            <div class="digital-logbook-card-foot">
                <span class="digital-logbook-chip">Treatment Log</span>
                <span class="digital-logbook-icon"><x-outline-icon name="clipboard-document-list" /></span>
            </div>
        </a>

        <a href="{{ $healthFormsLogbookUrl }}" class="digital-logbook-card">
            <div>
                <div class="digital-logbook-card-label">Applicant Review</div>
                <div class="digital-logbook-card-title">Health Form Logbook</div>
                <p class="digital-logbook-card-copy">Monitor submitted health forms, Final Review time-in, approval time-out, reviewer, approver, condition, and status.</p>
            </div>
            <div class="digital-logbook-card-foot">
                <span class="digital-logbook-chip">Clinic Visit Log</span>
                <span class="digital-logbook-icon"><x-outline-icon name="document-text" /></span>
            </div>
        </a>
    </section>
</div>
@endsection
