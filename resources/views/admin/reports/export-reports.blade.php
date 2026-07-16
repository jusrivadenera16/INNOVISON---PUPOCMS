@extends('layouts.admin')

@section('title', 'Export Reports Hub')

@push('styles')
<style>
    .export-hub-shell {
        width: min(1180px, calc(100% - 32px));
        margin: 24px auto;
        color: #0f172a;
    }

    .export-hub-frame {
        position: relative;
        overflow: hidden;
        border-radius: 18px;
        border: 1px solid rgba(127, 29, 45, .18);
        background: #ffffff;
        padding: 24px;
        box-shadow: 0 18px 42px rgba(15, 23, 42, .08);
    }

    .export-hub-frame::before {
        content: "";
        position: absolute;
        top: 0;
        left: 18px;
        right: 18px;
        height: 5px;
        border-radius: 0 0 999px 999px;
        background: #70131B;
    }

    .export-hub-header {
        display: flex;
        align-items: flex-start;
        justify-content: space-between;
        gap: 18px;
        margin-bottom: 22px;
    }

    .export-hub-kicker {
        margin: 0 0 8px;
        color: #70131B;
        font-size: 12px;
        font-weight: 950;
        letter-spacing: .08em;
        text-transform: uppercase;
    }

    .export-hub-title {
        margin: 0;
        color: #0f172a;
        font-size: 26px;
        line-height: 1.1;
        font-weight: 950;
    }

    .export-hub-copy {
        margin: 8px 0 0;
        max-width: 760px;
        color: #334155;
        font-size: 14px;
        font-weight: 650;
        line-height: 1.5;
    }

    .export-hub-back {
        min-height: 42px;
        padding: 0 16px;
        border-radius: 10px;
        border: 1px solid #70131B;
        background: #70131B;
        color: #ffffff;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        font-size: 13px;
        font-weight: 900;
        text-decoration: none;
        white-space: nowrap;
        transition: background .18s ease, color .18s ease, border-color .18s ease, transform .18s ease;
    }

    .export-hub-back,
    .export-hub-back:link,
    .export-hub-back:visited,
    .export-hub-back:active {
        color: #ffffff !important;
    }

    .export-hub-back:hover,
    .export-hub-back:focus-visible {
        background: #facc15;
        border-color: #facc15;
        color: #70131B !important;
        transform: translateY(-1px);
        outline: none;
    }

    .export-hub-grid {
        display: grid;
        grid-template-columns: 1fr;
        gap: 16px;
    }

    .export-hub-card {
        position: relative;
        min-height: 100px;
        display: flex;
        align-items: center;
        justify-content: flex-start;
        gap: 18px;
        overflow: hidden;
        padding: 20px 64px 20px 22px;
        border-radius: 10px;
        border: 1px solid rgba(112, 19, 27, .16);
        background: #ffffff;
        color: #0f172a;
        text-decoration: none;
        box-shadow: 0 12px 26px rgba(15, 23, 42, .06);
        transition: transform .22s ease, border-color .22s ease, box-shadow .22s ease, background .22s ease, color .22s ease;
    }

    .export-hub-card > * {
        position: relative;
        z-index: 1;
    }

    .export-hub-card::after {
        content: "";
        position: absolute;
        top: -42%;
        bottom: -42%;
        left: -125%;
        width: 42%;
        opacity: 0;
        background: linear-gradient(105deg, rgba(255,255,255,0) 0%, rgba(250,204,21,.18) 48%, rgba(255,255,255,0) 100%);
        transform: translateX(0) skewX(-18deg);
        pointer-events: none;
    }

    .export-hub-card:hover,
    .export-hub-card:focus-visible {
        background: #facc15;
        color: #70131B;
        transform: translateY(-2px);
        border-color: #facc15;
        box-shadow: 0 18px 34px rgba(112, 19, 27, .14);
        outline: none;
    }

    .export-hub-card:hover::after,
    .export-hub-card:focus-visible::after {
        animation: exportHubSweep .92s ease both;
    }

    @keyframes exportHubSweep {
        0% { opacity: 0; transform: translateX(0) skewX(-18deg); }
        18%, 72% { opacity: .72; }
        100% { opacity: 0; transform: translateX(720%) skewX(-18deg); }
    }

    .export-hub-icon {
        width: 58px;
        height: 58px;
        flex: 0 0 auto;
        display: grid;
        place-items: center;
        border-radius: 999px;
        color: #70131B;
        background: linear-gradient(135deg, rgba(127, 29, 45, .08), rgba(250, 204, 21, .22));
        border: 1px solid rgba(112, 19, 27, .08);
        transition: background .22s ease, border-color .22s ease, color .22s ease;
    }

    .export-hub-icon svg {
        width: 30px;
        height: 30px;
    }

    .export-hub-card:hover .export-hub-icon,
    .export-hub-card:focus-visible .export-hub-icon {
        color: #70131B;
        background: rgba(112, 19, 27, .16);
        border-color: rgba(112, 19, 27, .24);
    }

    .export-hub-card h2 {
        margin: 0 0 6px;
        color: #0f172a;
        font-size: 17px;
        line-height: 1.15;
        font-weight: 950;
        transition: color .22s ease;
    }

    .export-hub-card p {
        margin: 0;
        max-width: 760px;
        color: #0f172a;
        font-size: 13px;
        line-height: 1.45;
        font-weight: 700;
        transition: color .22s ease;
    }

    .export-hub-card:hover h2,
    .export-hub-card:hover p,
    .export-hub-card:focus-visible h2,
    .export-hub-card:focus-visible p {
        color: #70131B;
    }

    .export-hub-arrow {
        position: absolute;
        top: 50%;
        right: 18px;
        width: 30px;
        height: 30px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        border-radius: 999px;
        color: #70131B;
        background: transparent;
        border: 0;
        transform: translateY(-50%);
        transition: color .2s ease, background .2s ease, border-color .2s ease, transform .2s ease;
    }

    .export-hub-arrow svg {
        width: 18px;
        height: 18px;
    }

    .export-hub-card:hover .export-hub-arrow,
    .export-hub-card:focus-visible .export-hub-arrow {
        color: #70131B;
        background: transparent;
        border-color: transparent;
        transform: translateY(-50%) translateX(4px);
    }

    html[data-theme="dark"] .export-hub-frame {
        background: rgba(15, 23, 42, .94);
        border-color: rgba(250, 204, 21, .18);
    }

    html[data-theme="dark"] .export-hub-title {
        color: #ffffff;
    }

    html[data-theme="dark"] .export-hub-copy {
        color: #cbd5e1;
    }

    html[data-theme="dark"] .export-hub-card {
        background: rgba(15, 23, 42, .92);
        border-color: rgba(250, 204, 21, .18);
    }

    html[data-theme="dark"] .export-hub-card h2,
    html[data-theme="dark"] .export-hub-card p {
        color: #ffffff;
    }

    html[data-theme="dark"] .export-hub-icon,
    html[data-theme="dark"] .export-hub-arrow {
        color: #facc15;
    }

    html[data-theme="dark"] .export-hub-icon {
        background: rgba(250, 204, 21, .10);
        border-color: rgba(250, 204, 21, .22);
    }

    html[data-theme="dark"] .export-hub-card:hover,
    html[data-theme="dark"] .export-hub-card:focus-visible {
        background: #facc15;
    }

    html[data-theme="dark"] .export-hub-card:hover .export-hub-icon,
    html[data-theme="dark"] .export-hub-card:focus-visible .export-hub-icon,
    html[data-theme="dark"] .export-hub-card:hover .export-hub-arrow,
    html[data-theme="dark"] .export-hub-card:focus-visible .export-hub-arrow {
        color: #70131B;
    }

    html[data-theme="dark"] .export-hub-card:hover h2,
    html[data-theme="dark"] .export-hub-card:hover p,
    html[data-theme="dark"] .export-hub-card:focus-visible h2,
    html[data-theme="dark"] .export-hub-card:focus-visible p {
        color: #70131B;
    }

    @media (max-width: 820px) {
        .export-hub-header {
            flex-direction: column;
        }
        .export-hub-back {
            width: 100%;
        }
    }
</style>
@endpush

@section('content')
@php
    $role = \App\Models\User::normalizeRole(optional(auth()->user())->user_role ?? '');
    $reportsHomeUrl = $role === \App\Models\User::ROLE_ADMIN ? url('/assistant/reports') : url('/admin/reports');
    $hubBaseUrl = $role === \App\Models\User::ROLE_ADMIN ? url('/assistant/reports/export-hub') : url('/admin/reports/export-hub');
    $cards = [
        [
            'title' => 'Monthly Accomplishment Report',
            'copy' => 'Preview medical accomplishment categories before opening the existing MAR export template.',
            'icon' => 'clipboard-document-list',
            'url' => $hubBaseUrl . '/mar',
        ],
        [
            'title' => 'Inventory Stock',
            'copy' => 'Preview inventory balances, then export the existing Inventory of Medicines or Inventory of Supplies template.',
            'icon' => 'cube',
            'url' => $hubBaseUrl . '/inventory',
        ],
         [
            'title' => 'Health Forms',
            'copy' => 'Preview health form records before exporting the report.',
            'icon' => 'document-text',
            'url' => $hubBaseUrl . '/health-forms',
        ],
        [
            'title' => 'Appointments',
            'copy' => 'Preview appointment records and consultation activity for the selected period.',
            'icon' => 'calendar-days',
            'url' => $hubBaseUrl . '/appointments',
        ],
        [
            'title' => 'Audit Trail',
            'copy' => 'Preview user activity logs, system changes, and administrative actions.',
            'icon' => 'clock',
            'url' => $hubBaseUrl . '/audit-trail',
        ],
       
    ];
@endphp

<div class="export-hub-shell">
    <section class="export-hub-frame">
        <header class="export-hub-header">
            <div>
                <p class="export-hub-kicker">Export Reports</p>
                <h1 class="export-hub-title">Choose the report export workspace you want to open.</h1>
                <p class="export-hub-copy">Each export now opens a dedicated preview page with date filtering and the existing generated report output.</p>
            </div>
            <a href="{{ $reportsHomeUrl }}" class="export-hub-back">&larr; Back to Reports</a>
        </header>

        <div class="export-hub-grid">
            @foreach($cards as $card)
                <a href="{{ $card['url'] }}" class="export-hub-card">
                    <span class="export-hub-arrow"><x-outline-icon name="chevron-right" /></span>
                    <span class="export-hub-icon"><x-outline-icon :name="$card['icon']" /></span>
                    <span>
                        <h2>{{ $card['title'] }}</h2>
                        <p>{{ $card['copy'] }}</p>
                    </span>
                </a>
            @endforeach
        </div>
    </section>
</div>
@endsection
