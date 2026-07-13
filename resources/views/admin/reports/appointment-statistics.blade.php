@extends('layouts.admin')

@section('title', 'Appointment Statistics')

@push('styles')
<style>
    .appointment-stats-shell {
        max-width: 1500px;
        margin: 0 auto;
        padding: 22px;
    }

    .appointment-stats-header {
        display: flex;
        align-items: flex-start;
        justify-content: space-between;
        gap: 18px;
        margin-bottom: 20px;
    }

    .appointment-stats-title {
        margin: 0;
        color: #111827;
        font-size: 30px;
        line-height: 1.1;
        font-weight: 900;
        letter-spacing: 0;
    }

    .appointment-stats-subtitle {
        margin: 8px 0 0;
        max-width: 780px;
        color: #64748b;
        font-size: 12px;
        line-height: 1.5;
        font-weight: 600;
    }

    .appointment-stats-header-actions {
        display: flex;
        align-items: center;
        justify-content: flex-end;
        gap: 12px;
        flex-wrap: nowrap;
    }

    .appointment-stats-header-actions > a[style*="background"] {
        display: none !important;
    }

    .appointment-stats-header-actions .appointment-stats-filter-shell {
        order: 1;
    }

    .appointment-stats-header-actions > .appointment-stats-back {
        order: 2;
    }

    .appointment-stats-back,
    .appointment-stats-filter-toggle,
    .appointment-stats-filter-button {
        display: inline-flex;
        position: relative;
        overflow: hidden;
        align-items: center;
    }

    .appointment-stats-back::after,
    .appointment-stats-filter-toggle::after,
    .appointment-stats-filter-button::after,
    .appointment-stat-action::after {
        content: "";
        position: absolute;
        top: -40%;
        left: -130%;
        width: 120%;
        height: 180%;
        background: linear-gradient(115deg, rgba(250, 204, 21, 0) 0%, rgba(250, 204, 21, 0.46) 45%, rgba(250, 204, 21, 0) 100%);
        transform: skewX(-20deg);
        transition: left 1.5s ease;
        pointer-events: none;
        z-index: 0;
    }

    .appointment-stats-back:hover::after,
    .appointment-stats-filter-toggle:hover::after,
    .appointment-stats-filter-button:hover::after,
    .appointment-stat-action:hover::after {
        left: 125%;
    }

    .appointment-stats-back {
        z-index: 1;
        justify-content: center;
        gap: 8px;
        min-height: 50px;
        min-width: 132px;
        padding: 0 18px;
        border-radius: 10px;
        border: 1px solid #70131B;
        background: #70131B;
        color: #ffffff;
        font-family: inherit;
        font-size: 13px;
        font-weight: 900;
        text-decoration: none;
        cursor: pointer;
        box-shadow: 0 10px 22px rgba(15, 23, 42, 0.08);
        transition: all .18s ease;
    }

    .appointment-stats-back:hover,
    .appointment-stats-back:focus-visible {
        background: #facc15 !important;
        border-color: #facc15 !important;
        color: #70131B !important;
        outline: none;
    }

    .appointment-stats-back,
    .appointment-stats-back:link,
    .appointment-stats-back:visited,
    .appointment-stats-back:active {
        color: #ffffff !important;
    }

    .appointment-stats-filter-toggle,
    .appointment-stats-filter-button {
        justify-content: center;
        gap: 8px;
        min-height: 42px;
        padding: 0 16px;
        border-radius: 10px;
        border: 1px solid rgba(112, 19, 27, 0.22);
        background: #ffffff;
        color: #70131B;
        font-family: inherit;
        font-size: 13px;
        font-weight: 900;
        text-decoration: none;
        cursor: pointer;
        box-shadow: 0 10px 22px rgba(15, 23, 42, 0.08);
    }

    .appointment-stats-back svg,
    .appointment-stats-filter-toggle svg,
    .appointment-stats-filter-button svg {
        width: 18px;
        height: 18px;
    }

    .appointment-stats-filter-shell {
        position: relative;
        display: inline-flex;
        align-items: center;
        flex: 0 0 auto;
    }

    .appointment-stats-filter-toggle {
        min-height: 50px;
        min-width: 132px;
        position: relative;
        overflow: hidden;
        border-radius: 10px;
        background: linear-gradient(135deg, #70131B, #8f2230);
        border-color: #8f2230;
        color: #ffffff;
        box-shadow:
            0 0 0 3px rgba(112, 19, 27, 0.12),
            0 10px 22px rgba(112, 19, 27, 0.20);
    }

    .appointment-stats-filter-toggle:hover,
    .appointment-stats-filter-toggle:focus {
        background: #facc15;
        border-color: #facc15;
        color: #70131B;
        outline: none;
    }

    .appointment-stats-filter-panel {
        position: absolute;
        right: 0;
        top: calc(100% + 10px);
        z-index: 40;
        width: min(560px, 92vw);
        padding: 14px;
        border-radius: 18px;
        border: 1px solid rgba(112, 19, 27, 0.12);
        background: rgba(255, 255, 255, 0.96);
        box-shadow: 0 24px 38px rgba(15, 23, 42, 0.12);
        display: none;
    }

    .appointment-stats-filter-shell.is-open .appointment-stats-filter-panel {
        display: block;
    }

    .appointment-stats-filter-title {
        margin: 0 0 10px;
        color: #70131B;
        font-size: 12px;
        font-weight: 800;
        letter-spacing: 0.04em;
        text-transform: uppercase;
    }

    .appointment-stats-filter {
        display: grid;
        grid-template-columns: repeat(2, minmax(0, 1fr));
        gap: 12px;
    }

    .appointment-stats-field {
        display: grid;
        gap: 6px;
    }

    .appointment-stats-field label {
        color: #475569;
        font-size: 12px;
        font-weight: 900;
        text-transform: uppercase;
    }

    .appointment-stats-control {
        width: 100%;
        min-height: 46px;
        border-radius: 16px;
        border: 1px solid rgba(127, 29, 29, 0.22);
        padding: 0 13px;
        color: #111827;
        background:
            radial-gradient(circle at top right, rgba(250, 204, 21, 0.10), transparent 36%),
            linear-gradient(180deg, #ffffff 0%, #fff8f6 100%);
        box-shadow:
            0 12px 22px rgba(15, 23, 42, 0.08),
            inset 0 1px 0 rgba(255,255,255,0.86);
        font-family: inherit;
        font-size: 13px;
        font-weight: 700;
    }

    .appointment-stats-control:focus {
        border-color: #8B0000;
        box-shadow:
            0 0 0 4px rgba(139, 0, 0, 0.06),
            0 14px 24px rgba(139, 0, 0, 0.10),
            inset 0 1px 0 rgba(255,255,255,0.88);
        outline: none;
    }

    .appointment-stats-filter-actions {
        display: flex;
        gap: 8px;
        margin-top: 12px;
        grid-column: 1 / -1;
    }

    .appointment-stats-filter-reset,
    .appointment-stats-filter-close {
        flex: 1 1 0;
        min-height: 42px;
        border-radius: 12px;
        border: 1px solid rgba(112, 19, 27, 0.12);
        background: #f8fafc;
        color: #475569;
        font-weight: 800;
        text-decoration: none;
        cursor: pointer;
        display: inline-flex;
        align-items: center;
        justify-content: center;
    }

    .appointment-stats-filter-button {
        flex: 1.2 1 0;
        border-radius: 12px;
        background: #70131B;
        color: #ffffff;
    }

    html[data-theme="dark"] .appointment-stats-filter-reset,
    html[data-theme="dark"] .appointment-stats-filter-close {
        background: rgba(18, 18, 18, 0.55);
        color: #f8fafc;
        border-color: rgba(255, 255, 255, 0.08);
    }

    .appointment-stats-summary {
        display: grid;
        grid-template-columns: repeat(4, minmax(0, 1fr));
        gap: 12px;
        margin-bottom: 18px;
    }

    .appointment-chart-card {
        border-radius: 14px;
        border: 1px solid rgba(112, 19, 27, 0.10);
        background: #ffffff;
        box-shadow: 0 14px 32px rgba(15, 23, 42, 0.07);
    }

    .appointment-stat-card {
        position: relative;
        display: grid;
        grid-template-columns: 44px minmax(0, 1fr);
        grid-template-rows: auto auto auto;
        column-gap: 14px;
        min-height: 112px;
        padding: 14px 16px;
        overflow: hidden;
        border-radius: 14px;
        border: 1px solid rgba(112, 19, 27, 0.10);
        background: #ffffff;
        box-shadow: 0 14px 32px rgba(15, 23, 42, 0.07);
        transition: transform .18s ease, box-shadow .18s ease, border-color .18s ease;
    }

    .appointment-stat-card:hover {
        transform: translateY(-3px);
        border-color: rgba(112, 19, 27, 0.20);
        box-shadow: 0 18px 38px rgba(15, 23, 42, 0.11);
    }

    .appointment-stat-card::before {
        content: "";
        width: 44px;
        height: 44px;
        border-radius: 999px;
        grid-column: 1;
        grid-row: 1 / span 3;
        align-self: center;
        background: #fff1f2;
        border: 1px solid rgba(127, 29, 45, .08);
    }

    .appointment-stat-card::after {
        content: "";
        position: absolute;
        left: 31px;
        top: 50%;
        width: 18px;
        height: 18px;
        transform: translateY(-50%);
        background: #7f1d2d;
        -webkit-mask: var(--appointment-stat-icon) center / contain no-repeat;
        mask: var(--appointment-stat-icon) center / contain no-repeat;
    }

    .appointment-stat-card:nth-child(1) {
        border-color: rgba(34, 197, 94, .32);
    }

    .appointment-stat-card:nth-child(1)::before {
        background: #dcfce7;
    }

    .appointment-stat-card:nth-child(1)::after {
        background: #22c55e;
        --appointment-stat-icon: url("data:image/svg+xml,%3Csvg viewBox='0 0 24 24' fill='none' xmlns='http://www.w3.org/2000/svg'%3E%3Cpath d='M8 7h8M8 12h8M8 17h5M5 3h14a2 2 0 0 1 2 2v14a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2Z' stroke='black' stroke-width='2' stroke-linecap='round'/%3E%3C/svg%3E");
    }

    .appointment-stat-card:nth-child(2) {
        border-color: rgba(59, 130, 246, .30);
    }

    .appointment-stat-card:nth-child(2)::before {
        background: #dbeafe;
    }

    .appointment-stat-card:nth-child(2)::after {
        background: #2563eb;
        --appointment-stat-icon: url("data:image/svg+xml,%3Csvg viewBox='0 0 24 24' fill='none' xmlns='http://www.w3.org/2000/svg'%3E%3Cpath d='M8 6h8M8 18h8M12 4v16M4 12h16' stroke='black' stroke-width='2' stroke-linecap='round'/%3E%3C/svg%3E");
    }

    .appointment-stat-card:nth-child(3)::after {
        background: #f59e0b;
        --appointment-stat-icon: url("data:image/svg+xml,%3Csvg viewBox='0 0 24 24' fill='none' xmlns='http://www.w3.org/2000/svg'%3E%3Cpath d='M12 6v6l4 2M12 22a10 10 0 1 0 0-20 10 10 0 0 0 0 20Z' stroke='black' stroke-width='2' stroke-linecap='round' stroke-linejoin='round'/%3E%3C/svg%3E");
    }

    .appointment-stat-card:nth-child(4)::after {
        background: #7f1d2d;
        --appointment-stat-icon: url("data:image/svg+xml,%3Csvg viewBox='0 0 24 24' fill='none' xmlns='http://www.w3.org/2000/svg'%3E%3Cpath d='M8 2v4M16 2v4M3 10h18M6 5h12a3 3 0 0 1 3 3v11a3 3 0 0 1-3 3H6a3 3 0 0 1-3-3V8a3 3 0 0 1 3-3Z' stroke='black' stroke-width='2' stroke-linecap='round'/%3E%3C/svg%3E");
    }

    .appointment-stat-label {
        grid-column: 2;
        margin: 0;
        color: #0f172a;
        font-size: 12px;
        font-weight: 900;
        text-transform: uppercase;
        letter-spacing: .04em;
    }

    .appointment-stat-value {
        grid-column: 2;
        margin: 4px 0 0;
        color: #0f172a;
        font-size: 22px;
        font-weight: 950;
        line-height: 1.05;
    }

    .appointment-stat-hint {
        grid-column: 2;
        margin: 0;
        color: #64748b;
        font-size: 12px;
        line-height: 1.35;
        font-weight: 650;
    }

    .appointment-stat-split {
        grid-column: 2;
        display: grid;
        grid-template-columns: 1fr 1px 1fr;
        gap: 12px;
        align-items: stretch;
        margin-top: 6px;
    }

    .appointment-stat-split-line {
        width: 1px;
        min-height: 44px;
        background: rgba(112, 19, 27, .18);
    }

    .appointment-stat-mini-label {
        display: block;
        color: #64748b;
        font-size: 11px;
        font-weight: 900;
        text-transform: uppercase;
        letter-spacing: .04em;
    }

    .appointment-stat-mini-value {
        display: block;
        margin-top: 4px;
        color: #0f172a;
        font-size: 22px;
        line-height: 1;
        font-weight: 950;
    }

    .appointment-stat-mini-hint {
        display: block;
        margin-top: 5px;
        color: #64748b;
        font-size: 11px;
        line-height: 1.25;
        font-weight: 700;
    }

    .appointment-stat-action {
        position: relative;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        gap: 8px;
        grid-column: 2;
        min-height: 34px;
        width: fit-content;
        margin-top: 8px;
        padding: 0 13px;
        overflow: hidden;
        border-radius: 10px;
        border: 1px solid #70131B;
        background: #70131B;
        color: #ffffff;
        font-size: 12px;
        font-weight: 900;
        text-decoration: none;
        transition: all .18s ease;
    }

    .appointment-stat-action:hover,
    .appointment-stat-action:focus-visible {
        background: #facc15;
        border-color: #facc15;
        color: #70131B;
        outline: none;
    }

    .appointment-stat-card.is-action-card {
        grid-template-columns: 44px minmax(0, 1fr) 44px;
        align-items: center;
        background: #8f1827;
        border-color: rgba(250, 204, 21, .75);
        color: #ffffff;
        box-shadow: 0 16px 30px rgba(112, 19, 27, .16);
    }

    .appointment-stat-card.is-action-card::before {
        background: rgba(255, 255, 255, .14);
        border-color: rgba(255, 255, 255, .14);
    }

    .appointment-stat-card.is-action-card::after {
        background: #ffffff;
    }

    .appointment-stat-card.is-action-card .appointment-stat-label,
    .appointment-stat-card.is-action-card .appointment-stat-value,
    .appointment-stat-card.is-action-card .appointment-stat-hint {
        color: #ffffff;
    }

    .appointment-stat-card.is-action-card .appointment-stat-value {
        font-size: 20px;
    }

    .appointment-stat-card.is-action-card .appointment-stat-action {
        grid-column: 3;
        grid-row: 1 / span 3;
        width: 38px;
        height: 38px;
        min-height: 38px;
        padding: 0;
        margin: 0;
        border-radius: 10px;
        border-color: #facc15;
        background: rgba(255, 255, 255, .08);
        color: #ffffff;
        font-size: 0;
    }

    .appointment-stat-card.is-action-card .appointment-stat-action::before {
        content: "→";
        font-size: 18px;
        line-height: 1;
        position: relative;
        z-index: 1;
    }

    .appointment-stat-card.is-action-card:hover {
        background: #facc15;
        border-color: #facc15;
    }

    .appointment-stat-card.is-action-card:hover .appointment-stat-label,
    .appointment-stat-card.is-action-card:hover .appointment-stat-value,
    .appointment-stat-card.is-action-card:hover .appointment-stat-hint {
        color: #70131B;
    }

    .appointment-stat-card.is-action-card:hover::before {
        background: rgba(112, 19, 27, .14);
        border-color: rgba(112, 19, 27, .20);
    }

    .appointment-stat-card.is-action-card:hover::after {
        background: #70131B;
    }

    .appointment-stat-card.is-action-card:hover .appointment-stat-action,
    .appointment-stat-card.is-action-card .appointment-stat-action:focus-visible {
        background: rgba(112, 19, 27, .10);
        border-color: #70131B;
        color: #70131B;
    }

    .appointment-stats-grid {
        display: grid;
        grid-template-columns: repeat(2, minmax(0, 1fr));
        gap: 16px;
    }

    .appointment-chart-card {
        padding: 18px;
    }

    .appointment-chart-card.is-wide {
        grid-column: 1 / -1;
    }

    .appointment-chart-head {
        display: flex;
        align-items: flex-start;
        justify-content: space-between;
        gap: 14px;
        margin-bottom: 16px;
    }

    .appointment-chart-title {
        margin: 0;
        color: #111827;
        font-size: 17px;
        font-weight: 900;
    }

    .appointment-chart-copy {
        margin: 5px 0 0;
        color: #64748b;
        font-size: 13px;
        line-height: 1.5;
        font-weight: 600;
    }

    .appointment-chart-total {
        color: #70131B;
        font-size: 24px;
        font-weight: 950;
        text-align: right;
    }

    .appointment-chart-total span {
        display: block;
        color: #64748b;
        font-size: 11px;
        font-weight: 900;
        text-transform: uppercase;
    }

    .appointment-chart-bars {
        display: grid;
        gap: 11px;
    }

    .appointment-chart-row {
        display: grid;
        grid-template-columns: minmax(110px, 0.42fr) minmax(0, 1fr) 52px;
        align-items: center;
        gap: 10px;
    }

    .appointment-chart-label,
    .appointment-chart-value {
        color: #1f2937;
        font-size: 13px;
        font-weight: 850;
    }

    .appointment-chart-track {
        height: 10px;
        overflow: hidden;
        border-radius: 999px;
        background: #eef2f7;
    }

    .appointment-chart-fill {
        display: block;
        height: 100%;
        min-width: 4px;
        border-radius: inherit;
        background: #70131B;
    }

    .appointment-chart-fill.gold { background: #facc15; }
    .appointment-chart-fill.green { background: #22c55e; }
    .appointment-chart-fill.blue { background: #3b82f6; }
    .appointment-chart-fill.red { background: #ef4444; }

    .appointment-chart-row.is-muted {
        opacity: 0.62;
    }

    .appointment-chart-row.is-muted .appointment-chart-label,
    .appointment-chart-row.is-muted .appointment-chart-value {
        font-size: 12px;
        font-weight: 750;
    }

    .appointment-chart-row.is-muted .appointment-chart-track {
        height: 7px;
    }

    .appointment-chart-row.is-current {
        padding: 3px 0;
    }

    .appointment-chart-row.is-current .appointment-chart-label,
    .appointment-chart-row.is-current .appointment-chart-value {
        color: #70131B;
        font-size: 15px;
        font-weight: 950;
    }

    .appointment-chart-row.is-current .appointment-chart-track {
        height: 14px;
        background: rgba(112, 19, 27, 0.10);
    }

    html[data-theme="dark"] .appointment-stats-title,
    html[data-theme="dark"] .appointment-stat-value,
    html[data-theme="dark"] .appointment-stat-label,
    html[data-theme="dark"] .appointment-stat-mini-value,
    html[data-theme="dark"] .appointment-chart-title,
    html[data-theme="dark"] .appointment-chart-label,
    html[data-theme="dark"] .appointment-chart-value {
        color: #f8fafc;
    }

    html[data-theme="dark"] .appointment-stats-subtitle,
    html[data-theme="dark"] .appointment-stat-hint,
    html[data-theme="dark"] .appointment-stat-mini-label,
    html[data-theme="dark"] .appointment-stat-mini-hint,
    html[data-theme="dark"] .appointment-chart-copy,
    html[data-theme="dark"] .appointment-chart-total span {
        color: #cbd5e1;
    }

    html[data-theme="dark"] .appointment-stat-card,
    html[data-theme="dark"] .appointment-chart-card,
    html[data-theme="dark"] .appointment-stats-filter-panel {
        border-color: rgba(250, 204, 21, 0.16);
        background: rgba(15, 23, 42, 0.92);
    }

    html[data-theme="dark"] .appointment-stat-split-line {
        background: rgba(250, 204, 21, .20);
    }

    html[data-theme="dark"] .appointment-stats-filter-title {
        color: #f3d6da;
    }

    html[data-theme="dark"] .appointment-stats-control {
        background: rgba(18, 18, 18, 0.55);
        color: #f8fafc;
        border-color: rgba(255, 255, 255, 0.08);
    }

    html[data-theme="dark"] .appointment-chart-track {
        background: rgba(148, 163, 184, 0.22);
    }

    @media (max-width: 1100px) {
        .appointment-stats-summary {
            grid-template-columns: repeat(2, minmax(0, 1fr));
        }

        .appointment-stats-grid {
            grid-template-columns: 1fr;
        }
    }

    @media (max-width: 720px) {
        .appointment-stats-shell {
            padding: 16px;
        }

        .appointment-stats-header {
            flex-direction: column;
        }

        .appointment-stats-header-actions,
        .appointment-stats-filter-shell,
        .appointment-stats-filter-toggle,
        .appointment-stats-back {
            width: 100%;
        }

        .appointment-stats-filter-panel {
            position: fixed;
            right: 16px;
            left: 16px;
            top: auto;
            bottom: auto;
            width: auto;
            max-height: 70vh;
            overflow-y: auto;
        }

        .appointment-stats-filter,
        .appointment-stats-summary {
            grid-template-columns: 1fr;
        }

        .appointment-chart-row {
            grid-template-columns: 96px 1fr 42px;
        }
    }
</style>
@endpush

@section('content')
@php
    $role = \App\Models\User::normalizeRole(optional(auth()->user())->user_role ?? '');
    $reportsHomeUrl = $role === \App\Models\User::ROLE_ADMIN ? url('/assistant/reports') : url('/admin/reports');
    $filterAction = $role === \App\Models\User::ROLE_ADMIN ? url('/assistant/reports/appointment-statistics') : url('/admin/reports/appointment-statistics');
    $historyUrl = $role === \App\Models\User::ROLE_ADMIN ? url('/assistant/reports/appointment-history') : url('/admin/reports/appointment-history');
    $rangeLabel = $monthStart->format('F Y') === $monthEnd->format('F Y')
        ? $monthStart->format('F Y')
        : $monthStart->format('F Y') . ' to ' . $monthEnd->format('F Y');
    $barClasses = ['gold', 'green', 'blue', 'red', ''];
    $totalCard = $summaryCards[0] ?? ['label' => 'Total Records', 'value' => 0, 'hint' => 'Filtered activity'];
    $onlineCard = $summaryCards[1] ?? ['label' => 'Online', 'value' => 0, 'hint' => 'Online appointment requests'];
    $walkInCard = $summaryCards[2] ?? ['label' => 'Walk-in', 'value' => 0, 'hint' => 'Same-day clinic visits'];
    $averageCard = $summaryCards[3] ?? ['label' => 'Average Appointments / Day', 'value' => 0, 'hint' => 'Across selected range'];
    $formatSummaryValue = function ($value) {
        if (!is_numeric($value)) {
            return $value;
        }

        $numeric = (float) $value;
        return number_format($numeric, floor($numeric) === $numeric ? 0 : 1);
    };
@endphp

<div class="appointment-stats-shell">
    <header class="appointment-stats-header">
        <div>
            <h1 class="appointment-stats-title">Appointment Statistics</h1>
            <p class="appointment-stats-subtitle">Clinic activity analytics for online appointments and walk-in consultations, filtered by date range, patient type, status, service, and source.</p>
        </div>
        <div class="appointment-stats-header-actions">
            {{-- <a href="{{ route('reports.appointment-history') }}" class="appointment-stats-back" style="background: #7f1d2d; color: #ffffff; border-color: #7f1d2d;">
                📋 Appointment History
            </a> --}}
            <div class="appointment-stats-filter-shell" id="appointmentStatsFilterShell">
                <button type="button" class="appointment-stats-filter-toggle" id="appointmentStatsFilterToggle" aria-label="Open appointment statistics filters" aria-expanded="false" aria-controls="appointmentStatsFilterPanel">
                    <x-outline-icon name="funnel" />
                    Filter
                </button>
                <div class="appointment-stats-filter-panel" id="appointmentStatsFilterPanel" aria-hidden="true">
                    <div class="appointment-stats-filter-title">Report Filter</div>
                    <form class="appointment-stats-filter" method="GET" action="{{ $filterAction }}">
                        <div class="appointment-stats-field">
                            <label for="month_from">From</label>
                            <input class="appointment-stats-control" id="month_from" type="month" name="month_from" value="{{ $filters['month_from'] }}">
                        </div>
                        <div class="appointment-stats-field">
                            <label for="month_to">To</label>
                            <input class="appointment-stats-control" id="month_to" type="month" name="month_to" value="{{ $filters['month_to'] }}">
                        </div>
                        <div class="appointment-stats-field">
                            <label for="patient_type">Patient Type</label>
                            <select class="appointment-stats-control" id="patient_type" name="patient_type">
                                <option value="">All Types</option>
                                @foreach(['student' => 'Student', 'faculty' => 'Faculty', 'admin' => 'Admin', 'dependent' => 'Dependent'] as $value => $label)
                                    <option value="{{ $value }}" {{ $filters['patient_type'] === $value ? 'selected' : '' }}>{{ $label }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="appointment-stats-field">
                            <label for="status">Status</label>
                            <select class="appointment-stats-control" id="status" name="status">
                                <option value="">All Statuses</option>
                                @foreach(['pending' => 'Pending', 'approved' => 'Approved', 'completed' => 'Completed', 'cancelled' => 'Cancelled', 'expired' => 'Expired', 'missed' => 'Missed'] as $value => $label)
                                    <option value="{{ $value }}" {{ $filters['status'] === $value ? 'selected' : '' }}>{{ $label }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="appointment-stats-field">
                            <label for="service">Service</label>
                            <select class="appointment-stats-control" id="service" name="service">
                                <option value="">All Services</option>
                                @foreach(['general_consultation' => 'General Consultation', 'blood_pressure_monitoring' => 'Blood Pressure Monitoring'] as $value => $label)
                                    <option value="{{ $value }}" {{ $filters['service'] === $value ? 'selected' : '' }}>{{ $label }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="appointment-stats-field">
                            <label for="source">Source</label>
                            <select class="appointment-stats-control" id="source" name="source">
                                <option value="">Online + Walk-in</option>
                                <option value="online" {{ $filters['source'] === 'online' ? 'selected' : '' }}>Online</option>
                                <option value="walk-in" {{ $filters['source'] === 'walk-in' ? 'selected' : '' }}>Walk-in</option>
                            </select>
                        </div>
                        <div class="appointment-stats-filter-actions">
                            <a class="appointment-stats-filter-reset" href="{{ $filterAction }}">Reset</a>
                            <button type="button" class="appointment-stats-filter-close" id="appointmentStatsFilterClose">Close</button>
                            <button class="appointment-stats-filter-button" type="submit">
                                <x-outline-icon name="calendar-days" />
                                Apply
                            </button>
                        </div>
                    </form>
                </div>
            </div>
            <a href="{{ $reportsHomeUrl }}" class="appointment-stats-back">
                &larr; Back to Reports
            </a>
        </div>
    </header>

    <section class="appointment-stats-summary" aria-label="Appointment summary cards">
        <article class="appointment-stat-card">
            <p class="appointment-stat-label">{{ $totalCard['label'] }}</p>
            <div class="appointment-stat-value">{{ $formatSummaryValue($totalCard['value']) }}</div>
            <p class="appointment-stat-hint">{{ $totalCard['hint'] }}</p>
        </article>
        <article class="appointment-stat-card">
            <p class="appointment-stat-label">Online / Walk-in</p>
            <div class="appointment-stat-split">
                <div>
                    <span class="appointment-stat-mini-label">{{ $onlineCard['label'] }}</span>
                    <span class="appointment-stat-mini-value">{{ $formatSummaryValue($onlineCard['value']) }}</span>
                    <span class="appointment-stat-mini-hint">Requests</span>
                </div>
                <span class="appointment-stat-split-line" aria-hidden="true"></span>
                <div>
                    <span class="appointment-stat-mini-label">{{ $walkInCard['label'] }}</span>
                    <span class="appointment-stat-mini-value">{{ $formatSummaryValue($walkInCard['value']) }}</span>
                    <span class="appointment-stat-mini-hint">Clinic visits</span>
                </div>
            </div>
        </article>
        <article class="appointment-stat-card">
            <p class="appointment-stat-label">{{ $averageCard['label'] }}</p>
            <div class="appointment-stat-value">{{ $formatSummaryValue($averageCard['value']) }}</div>
            <p class="appointment-stat-hint">{{ $averageCard['hint'] }}</p>
        </article>
        <article class="appointment-stat-card is-action-card">
            <p class="appointment-stat-label">Appointment History</p>
            <div class="appointment-stat-value">Open</div>
            <p class="appointment-stat-hint">Review patient visits and clinic records.</p>
            <a href="{{ $historyUrl }}" class="appointment-stat-action" aria-label="Open appointment history">Open History &rarr;</a>
        </article>
    </section>

    <div class="appointment-stats-grid">
        <section class="appointment-chart-card is-wide">
            <div class="appointment-chart-head">
                <div>
                    <h2 class="appointment-chart-title">Appointments by {{ $monthStart->diffInDays($monthEnd) > 62 ? 'Month' : 'Day' }}</h2>
                    <p class="appointment-chart-copy">{{ $rangeLabel }}</p>
                </div>
                <div class="appointment-chart-total">{{ number_format($trendTotal) }}<span>Total</span></div>
            </div>
            @include('admin.reports.partials.appointment-stat-bars', ['items' => $trendRows, 'classes' => $barClasses])
        </section>

        <section class="appointment-chart-card">
            <div class="appointment-chart-head">
                <div>
                    <h2 class="appointment-chart-title">Appointment Status</h2>
                    <p class="appointment-chart-copy">Current outcome mix for the filtered range.</p>
                </div>
            </div>
            @include('admin.reports.partials.appointment-stat-bars', ['items' => $statusBreakdown, 'classes' => $barClasses])
        </section>

        <section class="appointment-chart-card">
            <div class="appointment-chart-head">
                <div>
                    <h2 class="appointment-chart-title">Patient Type</h2>
                    <p class="appointment-chart-copy">Student, faculty, admin, and dependent visits.</p>
                </div>
            </div>
            @include('admin.reports.partials.appointment-stat-bars', ['items' => $patientTypeBreakdown, 'classes' => $barClasses])
        </section>

        <section class="appointment-chart-card">
            <div class="appointment-chart-head">
                <div>
                    <h2 class="appointment-chart-title">Peak Hours</h2>
                    <p class="appointment-chart-copy">Busiest logged appointment or consultation hours.</p>
                </div>
            </div>
            @include('admin.reports.partials.appointment-stat-bars', ['items' => $peakHours, 'classes' => $barClasses])
        </section>

        <section class="appointment-chart-card">
            <div class="appointment-chart-head">
                <div>
                    <h2 class="appointment-chart-title">Online vs Walk-in</h2>
                    <p class="appointment-chart-copy">Source distribution for clinic activity.</p>
                </div>
            </div>
            @include('admin.reports.partials.appointment-stat-bars', ['items' => $sourceBreakdown, 'classes' => $barClasses])
        </section>

        <section class="appointment-chart-card">
            <div class="appointment-chart-head">
                <div>
                    <h2 class="appointment-chart-title">Top Reasons / Complaints</h2>
                    <p class="appointment-chart-copy">Most common patient concerns recorded.</p>
                </div>
            </div>
            @include('admin.reports.partials.appointment-stat-bars', ['items' => $topReasons, 'classes' => $barClasses])
        </section>

        <section class="appointment-chart-card">
            <div class="appointment-chart-head">
                <div>
                    <h2 class="appointment-chart-title">Most Appointment Type</h2>
                    <p class="appointment-chart-copy">Most frequent service or consultation type.</p>
                </div>
            </div>
            @include('admin.reports.partials.appointment-stat-bars', ['items' => $serviceBreakdown, 'classes' => $barClasses])
        </section>
    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function () {
        const filterShell = document.getElementById('appointmentStatsFilterShell');
        const filterToggle = document.getElementById('appointmentStatsFilterToggle');
        const filterPanel = document.getElementById('appointmentStatsFilterPanel');
        const filterClose = document.getElementById('appointmentStatsFilterClose');

        const setFilterOpenState = function (isOpen) {
            if (!filterShell || !filterToggle || !filterPanel) {
                return;
            }

            filterShell.classList.toggle('is-open', isOpen);
            filterToggle.setAttribute('aria-expanded', isOpen ? 'true' : 'false');
            filterPanel.setAttribute('aria-hidden', isOpen ? 'false' : 'true');
        };

        filterToggle?.addEventListener('click', function () {
            setFilterOpenState(!filterShell?.classList.contains('is-open'));
        });

        filterClose?.addEventListener('click', function () {
            setFilterOpenState(false);
        });

        document.addEventListener('click', function (event) {
            if (filterShell && !filterShell.contains(event.target)) {
                setFilterOpenState(false);
            }
        });

        document.addEventListener('keydown', function (event) {
            if (event.key === 'Escape') {
                setFilterOpenState(false);
            }
        });
    });
</script>
@endsection
