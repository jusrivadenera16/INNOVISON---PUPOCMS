@extends('layouts.admin')

@section('title', 'Inventory Summary')

@push('styles')
<style>
    .summary-container {
        padding: 10px 4px;
    }

    .summary-header {
        display: flex;
        justify-content: space-between;
        align-items: flex-end;
        gap: 18px;
        flex-wrap: wrap;
        margin-bottom: 20px;
    }

    .summary-title {
        margin: 0;
        color: #4b0f17;
        font-size: 26px;
        font-weight: 800;
        letter-spacing: -0.01em;
    }

    .summary-subtitle {
        margin: 6px 0 0;
        color: #6b7280;
        font-size: 14px;
        line-height: 1.5;
    }

    .summary-filter {
        display: flex;
        align-items: end;
        gap: 10px;
        flex-wrap: wrap;
    }

    .summary-filter label {
        display: block;
        margin-bottom: 6px;
        color: #6b7280;
        font-size: 11px;
        font-weight: 700;
        letter-spacing: 0.08em;
        text-transform: uppercase;
    }

    .summary-filter input[type="month"] {
        min-width: 180px;
        padding: 10px 12px;
        border-radius: 10px;
        border: 1px solid #d8c7cd;
        color: #111827;
        background: #fff;
    }

    .summary-filter button {
        padding: 10px 16px;
        border: none;
        border-radius: 10px;
        background: #70131B;
        color: #fff;
        font-weight: 700;
        cursor: pointer;
    }

    .summary-grid {
        display: grid;
        grid-template-columns: repeat(4, minmax(0, 1fr));
        gap: 16px;
        margin-bottom: 20px;
    }

    .summary-card {
        background: #fff;
        border-radius: 14px;
        border: 1px solid #eadde1;
        border-left: 4px solid #70131B;
        padding: 18px 18px 16px;
        box-shadow: 0 8px 24px rgba(112, 19, 27, 0.06);
    }

    .summary-label {
        font-size: 11px;
        font-weight: 700;
        color: #6b7280;
        text-transform: uppercase;
        letter-spacing: 0.08em;
    }

    .summary-value {
        margin: 6px 0 0;
        font-size: 30px;
        line-height: 1;
        color: #4b0f17;
        font-weight: 800;
    }

    .summary-meta {
        margin-top: 8px;
        color: #64748b;
        font-size: 12px;
    }

    .summary-panels {
        display: grid;
        grid-template-columns: 1.15fr 0.85fr;
        gap: 18px;
        margin-bottom: 18px;
    }

    .summary-panel,
    .summary-table-card {
        background: #fff;
        border-radius: 14px;
        border: 1px solid #eadde1;
        padding: 18px;
        box-shadow: 0 8px 24px rgba(112, 19, 27, 0.06);
    }

    .summary-table-card {
        margin-bottom: 18px;
    }

    .summary-table-title {
        margin: 0 0 10px;
        font-size: 17px;
        color: #4b0f17;
        font-weight: 800;
    }

    .summary-table-subtitle {
        margin: -2px 0 12px;
        color: #6b7280;
        font-size: 13px;
    }

    .summary-table {
        width: 100%;
        border-collapse: collapse;
    }

    .summary-table thead th {
        padding: 11px 12px;
        text-align: left;
        border-bottom: 1px solid #eddde2;
        color: #6b7280;
        font-size: 12px;
        text-transform: uppercase;
        letter-spacing: 0.05em;
    }

    .summary-table td {
        padding: 12px;
        border-bottom: 1px solid #f4ebee;
        color: #334155;
        font-size: 14px;
        vertical-align: top;
    }

    .summary-table tbody tr:last-child td {
        border-bottom: none;
    }

    .summary-chip {
        display: inline-flex;
        align-items: center;
        padding: 4px 10px;
        border-radius: 999px;
        font-size: 11px;
        font-weight: 700;
    }

    .summary-chip.low {
        background: #fff7ed;
        color: #c2410c;
    }

    .summary-chip.out {
        background: #fee2e2;
        color: #b91c1c;
    }

    .summary-chip.ok {
        background: #dcfce7;
        color: #15803d;
    }

    .summary-empty {
        padding: 18px;
        border: 1px dashed #d8c7cd;
        border-radius: 12px;
        text-align: center;
        color: #6b7280;
        font-size: 14px;
    }

    .summary-back {
        min-width: 132px;
        width: auto !important;
        flex: 0 0 auto;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        text-align: center;
        position: relative;
        overflow: hidden;
        gap: 7px;
        padding: 10px 16px;
        border: 1px solid rgba(112, 19, 27, 0.3);
        border-radius: 999px;
        background: rgba(255, 255, 255, 0.96);
        color: #70131B;
        font-size: 13px;
        font-weight: 800;
        text-decoration: none;
        white-space: nowrap;
        box-shadow: 0 0 0 2px rgba(112, 19, 27, 0.09), 0 10px 20px rgba(15, 23, 42, 0.08);
        transition: color .08s ease, border-color .18s ease, background .18s ease, box-shadow .18s ease;
    }

    .summary-back::after {
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

    .summary-back:hover::after {
        left: 125%;
    }

    .summary-back:hover,
    .summary-back:focus {
        color: #70131B;
        border-color: rgba(112, 19, 27, 0.48);
        background: #ffffff;
        box-shadow: 0 0 0 2px rgba(112, 19, 27, 0.12), 0 12px 28px rgba(15, 23, 42, 0.12);
        outline: none;
    }
    .summary-top-actions {
        display: flex;
        justify-content: flex-end;
        margin-bottom: 14px;
    }
    .mar-manage-btn {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        gap: 10px;
        position: relative;
        overflow: hidden;
        white-space: nowrap;
        background: linear-gradient(135deg, #70131B, #8f2230);
        color: #ffffff;
        padding: 11px 18px;
        border-radius: 999px;
        text-decoration: none;
        font-size: 14px;
        font-weight: 800;
        border: 1px solid #8f2230;
        box-shadow: 0 0 0 3px rgba(112, 19, 27, 0.12), 0 10px 22px rgba(112, 19, 27, 0.20);
        transition: color .08s linear, transform .18s ease, box-shadow .18s ease, background .18s ease;
        z-index: 0;
    }
    .mar-manage-btn::after {
        content: "";
        position: absolute;
        inset: 0;
        background: linear-gradient(120deg, rgba(255, 248, 196, 0) 0%, rgba(255, 239, 181, 0.14) 22%, rgba(255, 239, 181, 0.52) 48%, rgba(255, 239, 181, 0.14) 72%, rgba(255, 248, 196, 0) 100%);
        transform: translateX(-135%);
        transition: transform 1.5s ease;
        z-index: -1;
    }
    .mar-manage-btn::before {
        content: "MT";
        width: 28px;
        height: 28px;
        border-radius: 999px;
        background: #ffefb5;
        color: #70131B;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        font-size: 11px;
        font-weight: 900;
        letter-spacing: 0.04em;
        flex: 0 0 auto;
        position: relative;
        z-index: 1;
    }
    .mar-manage-btn-label {
        position: relative;
        z-index: 1;
        color: #ffffff;
    }
    .mar-manage-btn:hover {
        transform: translateY(-1px);
        border-color: #facc15;
        box-shadow: 0 0 0 3px rgba(250, 204, 21, 0.18), 0 14px 24px rgba(112, 19, 27, 0.16);
        color: #ffffff;
    }
    .mar-manage-btn:hover::after {
        transform: translateX(135%);
    }

    @media (max-width: 1100px) {
        .summary-grid {
            grid-template-columns: repeat(2, minmax(0, 1fr));
        }

        .summary-panels {
            grid-template-columns: 1fr;
        }
    }

    @media (max-width: 700px) {
        .summary-grid {
            grid-template-columns: 1fr;
        }
    }

    /* DARK MODE FIXES - Inventory Summary Cards */
    html[data-theme="dark"] .inventory-frame,
    html[data-theme="dark"] .summary-card,
    html[data-theme="dark"] .total-card {
        background: rgba(35, 17, 25, 0.96) !important;
        border: 1px solid rgba(255, 255, 255, 0.08) !important;
        color: #f8fafc !important;
    }

    html[data-theme="dark"] .inventory-frame::before {
        background: #facc15 !important;
    }

    html[data-theme="dark"] .inventory-frame-title,
    html[data-theme="dark"] .summary-card h3,
    html[data-theme="dark"] .total-card h3 {
        color: #f3d6da !important;
    }

    html[data-theme="dark"] .inventory-frame-value,
    html[data-theme="dark"] .summary-card .value,
    html[data-theme="dark"] .total-card .value {
        color: #f8fafc !important;
        font-weight: bold !important;
    }

    html[data-theme="dark"] .inventory-frame-label,
    html[data-theme="dark"] .summary-card .label,
    html[data-theme="dark"] .total-card .label {
        color: rgba(248, 250, 252, 0.7) !important;
    }

    html[data-theme="dark"] .inventory-frame-change,
    html[data-theme="dark"] .summary-card .change {
        color: rgba(248, 250, 252, 0.6) !important;
    }

    html[data-theme="dark"] .summary-grid {
        gap: 16px !important;
    }

    .summary-filter {
        position: relative;
        align-items: center !important;
    }

    .summary-filter-toggle {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        gap: 8px;
        min-height: 50px;
        min-width: 120px;
        padding: 0 18px;
        border: 1px solid #7f1d2d;
        border-radius: 14px;
        background: #7f1d2d;
        color: #ffffff;
        font-weight: 900;
        cursor: pointer;
        position: relative;
        overflow: hidden;
        box-shadow: 0 10px 20px rgba(112, 19, 27, .16);
    }

    .summary-filter-toggle::after {
        content: "";
        position: absolute;
        top: -40%;
        left: -130%;
        width: 120%;
        height: 180%;
        background: linear-gradient(115deg, rgba(250,204,21,0) 0%, rgba(255,247,181,.58) 45%, rgba(250,204,21,0) 100%);
        transform: skewX(-20deg);
        transition: left 1.5s ease;
        pointer-events: none;
    }

    .summary-filter-toggle > * {
        position: relative;
        z-index: 1;
    }

    .summary-filter-toggle svg {
        width: 18px;
        height: 18px;
        flex: 0 0 auto;
    }

    .summary-filter-toggle:hover,
    .summary-filter-toggle:focus-visible {
        background: #facc15;
        border-color: #facc15;
        color: #70131B;
        outline: none;
    }

    .summary-filter-toggle:hover::after,
    .summary-filter-toggle:focus-visible::after {
        left: 125%;
    }

    .summary-filter-panel {
        position: absolute;
        right: 0;
        top: calc(100% + 10px);
        z-index: 80;
        width: min(420px, 92vw);
        padding: 14px;
        border-radius: 18px;
        border: 1px solid rgba(112, 19, 27, .12);
        background: rgba(255, 255, 255, .98);
        box-shadow: 0 24px 38px rgba(15, 23, 42, .12);
        display: none;
    }

    .summary-filter.is-open .summary-filter-panel {
        display: block;
    }

    .summary-filter-panel-title {
        margin: 0 0 10px;
        font-size: 12px;
        font-weight: 900;
        letter-spacing: .04em;
        text-transform: uppercase;
        color: #70131B;
    }

    .summary-date-grid {
        display: grid;
        grid-template-columns: repeat(2, minmax(0, 1fr));
        gap: 10px;
    }

    .summary-date-field {
        display: grid;
        gap: 6px;
    }

    .summary-date-field label {
        margin: 0;
    }

    .summary-date-field input[type="date"] {
        width: 100%;
        min-height: 46px;
        padding: 12px 14px;
        border-radius: 16px;
        border: 1px solid rgba(127, 29, 29, .22);
        background: linear-gradient(180deg, #ffffff 0%, #fff8f6 100%);
        color: #111827;
        font-weight: 800;
        color-scheme: light;
        box-shadow: 0 12px 22px rgba(15, 23, 42, .08), inset 0 1px 0 rgba(255,255,255,.86);
    }

    .summary-filter-actions {
        display: flex;
        gap: 8px;
        margin-top: 12px;
    }

    .summary-filter-actions button,
    .summary-filter-actions a {
        flex: 1 1 0;
        min-height: 42px;
        border-radius: 12px;
        border: 1px solid rgba(112, 19, 27, .12);
        background: #f8fafc;
        color: #475569;
        font-weight: 900;
        text-decoration: none;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        cursor: pointer;
    }

    html[data-theme="dark"] .summary-filter-panel {
        background: rgba(15, 23, 42, .98) !important;
        border-color: rgba(250, 204, 21, .18) !important;
        box-shadow: 0 24px 38px rgba(0, 0, 0, .42) !important;
    }

    html[data-theme="dark"] .summary-filter-panel-title,
    html[data-theme="dark"] .summary-date-field label {
        color: #facc15 !important;
    }

    html[data-theme="dark"] .summary-date-field input[type="date"] {
        background: #111827 !important;
        border-color: rgba(250, 204, 21, .22) !important;
        color: #ffffff !important;
        color-scheme: dark;
        box-shadow: 0 12px 22px rgba(0, 0, 0, .28), inset 0 1px 0 rgba(255,255,255,.05) !important;
    }

    html[data-theme="dark"] .summary-filter-actions a {
        background: rgba(17, 24, 39, .94) !important;
        border-color: rgba(250, 204, 21, .18) !important;
        color: #ffffff !important;
    }

    .summary-filter-actions button:hover,
    .summary-filter-actions a:hover {
        background: #fff3f5;
        color: #70131B;
    }

    .summary-grid {
        gap: 10px !important;
        margin-bottom: 16px !important;
    }

    .summary-card {
        position: relative;
        min-height: 118px !important;
        display: grid;
        grid-template-columns: 44px minmax(0, 1fr);
        align-items: center;
        gap: 14px;
        padding: 14px !important;
        border-radius: 12px !important;
        border: 1px solid #e5e7eb !important;
        border-left: 0 !important;
        background: #ffffff !important;
        box-shadow: 0 12px 26px rgba(15, 23, 42, .06) !important;
        overflow: hidden;
        transition: transform .18s ease, box-shadow .18s ease, border-color .18s ease;
    }

    .summary-card:hover {
        transform: translateY(-3px);
        border-color: rgba(127, 29, 45, .36) !important;
        box-shadow: 0 16px 30px rgba(112, 19, 27, .12) !important;
    }

    .summary-card::before {
        content: "";
        width: 44px;
        height: 44px;
        border-radius: 999px;
        grid-column: 1;
        grid-row: 1 / span 3;
        background: #fff1f2;
        border: 1px solid rgba(127, 29, 45, .08);
    }

    .summary-card::after {
        content: "";
        position: absolute;
        left: 27px;
        top: 50%;
        width: 18px;
        height: 18px;
        transform: translateY(-50%);
        background: #7f1d2d;
        -webkit-mask: var(--summary-card-icon) center / contain no-repeat;
        mask: var(--summary-card-icon) center / contain no-repeat;
    }

    .summary-card:nth-child(1) { border-color: rgba(34, 197, 94, .32) !important; }
    .summary-card:nth-child(1)::before { background: #dcfce7; }
    .summary-card:nth-child(1)::after {
        background: #22c55e;
        --summary-card-icon: url("data:image/svg+xml,%3Csvg viewBox='0 0 24 24' fill='none' xmlns='http://www.w3.org/2000/svg'%3E%3Cpath d='m20 7-8-4-8 4 8 4 8-4Z M4 7v10l8 4 8-4V7' stroke='black' stroke-width='2' stroke-linejoin='round'/%3E%3C/svg%3E");
    }
    .summary-card:nth-child(2) { border-color: rgba(248, 113, 113, .36) !important; }
    .summary-card:nth-child(2)::before { background: #fee2e2; }
    .summary-card:nth-child(2)::after {
        background: #ef4444;
        --summary-card-icon: url("data:image/svg+xml,%3Csvg viewBox='0 0 24 24' fill='none' xmlns='http://www.w3.org/2000/svg'%3E%3Cpath d='M12 3v18M7 8h8.5a3.5 3.5 0 1 1 0 7H7' stroke='black' stroke-width='2.2' stroke-linecap='round'/%3E%3C/svg%3E");
    }
    .summary-card:nth-child(3)::after {
        --summary-card-icon: url("data:image/svg+xml,%3Csvg viewBox='0 0 24 24' fill='none' xmlns='http://www.w3.org/2000/svg'%3E%3Cpath d='M5 12h14M12 5l7 7-7 7' stroke='black' stroke-width='2.2' stroke-linecap='round' stroke-linejoin='round'/%3E%3C/svg%3E");
    }
    .summary-card:nth-child(4)::after {
        background: #f59e0b;
        --summary-card-icon: url("data:image/svg+xml,%3Csvg viewBox='0 0 24 24' fill='none' xmlns='http://www.w3.org/2000/svg'%3E%3Cpath d='M12 9v4m0 4h.01M10.3 4.3 2.7 18a2 2 0 0 0 1.7 3h15.2a2 2 0 0 0 1.7-3L13.7 4.3a2 2 0 0 0-3.4 0Z' stroke='black' stroke-width='2' stroke-linecap='round' stroke-linejoin='round'/%3E%3C/svg%3E");
    }

    .summary-label,
    .summary-value,
    .summary-meta {
        grid-column: 2;
    }

    .summary-label {
        color: #0f172a !important;
        font-size: 12px !important;
        line-height: 1.15;
        font-weight: 900 !important;
        letter-spacing: .04em !important;
    }

    .summary-value {
        margin-top: 6px !important;
        color: #0f172a !important;
        font-size: 24px !important;
    }

    .summary-meta {
        margin-top: 7px !important;
        color: #64748b !important;
        font-size: 12px !important;
        line-height: 1.35;
    }

    html[data-theme="dark"] .summary-filter-panel,
    html[data-theme="dark"] .summary-date-field input[type="date"] {
        background: rgba(15, 23, 42, .96) !important;
        border-color: rgba(250, 204, 21, .18) !important;
        color: #ffffff !important;
    }

    html[data-theme="dark"] .summary-card {
        background: rgba(15, 23, 42, .98) !important;
        border-color: rgba(250, 204, 21, .18) !important;
    }

    html[data-theme="dark"] .summary-label,
    html[data-theme="dark"] .summary-value {
        color: #ffffff !important;
    }

    html[data-theme="dark"] .summary-title,
    html[data-theme="dark"] .summary-table-title {
        color: #f8fafc !important;
    }

    html[data-theme="dark"] .summary-subtitle,
    html[data-theme="dark"] .summary-table-subtitle,
    html[data-theme="dark"] .summary-meta {
        color: #cbd5e1 !important;
    }

    html[data-theme="dark"] .summary-panel,
    html[data-theme="dark"] .summary-table-card,
    html[data-theme="dark"] .summary-empty {
        background: #111827 !important;
        border-color: rgba(250, 204, 21, .18) !important;
        color: #f8fafc !important;
        box-shadow: none !important;
    }

    html[data-theme="dark"] .summary-table {
        background: #111827 !important;
        color: #f8fafc !important;
    }

    html[data-theme="dark"] .summary-table thead th {
        background: #111827 !important;
        border-bottom-color: rgba(250, 204, 21, .18) !important;
        color: #f8fafc !important;
    }

    html[data-theme="dark"] .summary-table td {
        background: #111827 !important;
        border-bottom-color: rgba(148, 163, 184, .14) !important;
        color: #f8fafc !important;
    }

    html[data-theme="dark"] .summary-table td *,
    html[data-theme="dark"] .summary-empty {
        color: #f8fafc !important;
    }

    html[data-theme="dark"] .summary-chip.low {
        background: rgba(251, 146, 60, .16) !important;
        color: #fed7aa !important;
    }

    html[data-theme="dark"] .summary-chip.out {
        background: rgba(248, 113, 113, .18) !important;
        color: #fecaca !important;
    }

    html[data-theme="dark"] .summary-chip.ok {
        background: rgba(34, 197, 94, .16) !important;
        color: #bbf7d0 !important;
    }

    .summary-header .summary-filter {
        gap: 10px !important;
    }

    .summary-header .summary-filter-toggle,
    .summary-header .summary-back {
        min-height: 50px !important;
        min-width: 120px !important;
        padding: 0 18px !important;
        border: 1px solid #7f1d2d !important;
        border-radius: 10px !important;
        background: #7f1d2d !important;
        color: #ffffff !important;
        font-size: 13px !important;
        font-weight: 900 !important;
        box-shadow: 0 10px 20px rgba(112, 19, 27, .16) !important;
    }

    .summary-header .summary-back {
        display: inline-flex !important;
    }

    .summary-header .summary-filter-toggle:hover,
    .summary-header .summary-filter-toggle:focus-visible,
    .summary-header .summary-back:hover,
    .summary-header .summary-back:focus-visible {
        background: #facc15 !important;
        border-color: #facc15 !important;
        color: #70131B !important;
        outline: none;
    }

    .summary-header .summary-back:hover::after,
    .summary-header .summary-back:focus-visible::after {
        left: 125%;
    }
</style>
@endpush

@section('content')
@php
    $role = \App\Models\User::normalizeRole(optional(auth()->user())->user_role ?? '');
    $reportsHomeUrl = $role === \App\Models\User::ROLE_ADMIN ? url('/assistant/reports') : url('/admin/reports');
    $summaryUrl = $role === \App\Models\User::ROLE_ADMIN ? url('/assistant/reports/inventory-summary') : url('/admin/reports/inventory-summary');
    $rangeStartLabel = $dateFrom->format('M d, Y');
    $rangeEndLabel = $dateTo->format('M d, Y');
    $reportRangeLabel = $dateFrom->isSameDay($dateTo) ? $rangeStartLabel : $rangeStartLabel . ' to ' . $rangeEndLabel;
@endphp
<div class="summary-container">
    <div class="summary-header">
        <div>
            <h2 class="summary-title">Inventory Summary Report</h2>
            <p class="summary-subtitle">Monitor current stock, consumption, and starting balances for {{ $reportRangeLabel }}.</p>
        </div>

        <form action="{{ $summaryUrl }}" method="GET" class="summary-filter" id="summaryFilterShell">
            <button type="button" class="summary-filter-toggle" id="summaryFilterToggle" aria-expanded="false" aria-controls="summaryFilterPanel">
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" aria-hidden="true">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 3c2.755 0 5.455.232 8.083.678.533.09.917.556.917 1.096v1.044a2.25 2.25 0 0 1-.659 1.591l-5.432 5.432a2.25 2.25 0 0 0-.659 1.591v2.927a2.25 2.25 0 0 1-1.244 2.013L9.75 21v-6.568a2.25 2.25 0 0 0-.659-1.591L3.659 7.409A2.25 2.25 0 0 1 3 5.818V4.774c0-.54.384-1.006.917-1.096A48.32 48.32 0 0 1 12 3Z" />
                </svg>
                <span>Filter</span>
            </button>
            <a href="{{ $reportsHomeUrl }}" class="summary-back">&larr; Back to Reports</a>
            <div class="summary-filter-panel" id="summaryFilterPanel" aria-hidden="true">
                <div class="summary-filter-panel-title">Calendar Filter</div>
                <div class="summary-date-grid">
                    <div class="summary-date-field">
                        <label for="summaryDateFrom">From</label>
                        <input id="summaryDateFrom" type="date" name="date_from" value="{{ $dateFrom->format('Y-m-d') }}" aria-label="Report start date">
                    </div>
                    <div class="summary-date-field">
                        <label for="summaryDateTo">To</label>
                        <input id="summaryDateTo" type="date" name="date_to" value="{{ $dateTo->format('Y-m-d') }}" aria-label="Report end date">
                    </div>
                </div>
                <div class="summary-filter-actions">
                    <a href="{{ $summaryUrl }}">Reset</a>
                    <button type="submit">Apply</button>
                </div>
            </div>
        </form>
    </div>

    <div class="summary-grid">
        <div class="summary-card">
            <span class="summary-label">Total Unique Items</span>
            <h3 class="summary-value">{{ $totalItems }}</h3>
            <div class="summary-meta">Tracked inventory entries in the clinic list.</div>
        </div>
        <div class="summary-card">
            <span class="summary-label">Current Stock Quantity</span>
            <h3 class="summary-value">{{ $totalStock }}</h3>
            <div class="summary-meta">Combined remaining quantity across all items.</div>
        </div>
        <div class="summary-card">
            <span class="summary-label">Consumed This Month</span>
            <h3 class="summary-value">{{ $totalConsumed }}</h3>
            <div class="summary-meta">Based on inventory movement records for the selected month.</div>
        </div>
        <div class="summary-card">
            <span class="summary-label">Stock Alerts</span>
            <h3 class="summary-value">{{ $outOfStock + $lowStockCount }}</h3>
            <div class="summary-meta">{{ $outOfStock }} out of stock, {{ $lowStockCount }} running low.</div>
        </div>
    </div>

    <div class="summary-panels">
        <div class="summary-panel">
            <h4 class="summary-table-title">Inventory Performance by Item</h4>
            <p class="summary-table-subtitle">Starting stock is derived from current balance plus movement-recorded consumption for the selected month.</p>
            <table class="summary-table">
                <thead>
                    <tr>
                        <th>Item</th>
                        <th>Unit</th>
                        <th>Starting</th>
                        <th>Consumed</th>
                        <th>Current</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($itemPerformance as $item)
                        <tr>
                            <td>
                                <div style="font-weight: 700;">{{ $item->name }}</div>
                                <div style="font-size: 12px; color: #6b7280;">{{ $item->report_category }}</div>
                            </td>
                            <td>
                                {{ $item->unit }}
                                @if($item->hasDispensingConversion())
                                    <div style="font-size: 12px; color: #6b7280;">{{ $item->dispensing_unit }} ({{ $item->units_per_stock_unit }} per {{ $item->unit }})</div>
                                @endif
                            </td>
                            <td>{{ rtrim(rtrim(number_format((float) $item->starting_stock, 2, '.', ''), '0'), '.') }}</td>
                            <td>
                                {{ rtrim(rtrim(number_format((float) $item->consumed, 2, '.', ''), '0'), '.') }}
                                @if($item->hasDispensingConversion())
                                    <div style="font-size: 12px; color: #6b7280;">{{ rtrim(rtrim(number_format((float) $item->consumed_display, 2, '.', ''), '0'), '.') }} {{ $item->dispensing_unit }} dispensed</div>
                                @endif
                            </td>
                            <td style="font-weight: 800;">{{ rtrim(rtrim(number_format((float) $item->current_balance, 2, '.', ''), '0'), '.') }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5">
                                <div class="summary-empty">No inventory records available.</div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="summary-panel">
            <h4 class="summary-table-title">Low Stock Watchlist</h4>
            <p class="summary-table-subtitle">Items that need attention before they run out.</p>

            @if($lowStockItems->isEmpty())
                <div class="summary-empty">No low-stock items for this report period.</div>
            @else
                <table class="summary-table">
                    <thead>
                        <tr>
                            <th>Item</th>
                            <th>Balance</th>
                            <th>Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($lowStockItems as $item)
                            <tr>
                                <td style="font-weight: 700;">{{ $item->name }}</td>
                                <td>{{ rtrim(rtrim(number_format((float) $item->current_balance, 2, '.', ''), '0'), '.') }} {{ $item->unit }}</td>
                                <td><span class="summary-chip low">Low Stock</span></td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            @endif
        </div>
    </div>

    <div class="summary-table-card">
        <h4 class="summary-table-title">Stock by Category</h4>
        <p class="summary-table-subtitle">Category-level view of starting stock, monthly usage, and current balance.</p>
        <table class="summary-table">
            <thead>
                <tr>
                    <th>Category</th>
                    <th>Item Count</th>
                    <th>Starting Stock</th>
                    <th>Consumed</th>
                    <th>Current Balance</th>
                </tr>
            </thead>
            <tbody>
                @forelse($categorySummary as $cat)
                <tr>
                    <td style="font-weight: 700;">{{ $cat->category }}</td>
                    <td>{{ $cat->count }}</td>
                    <td>{{ $cat->starting_qty }}</td>
                    <td>{{ rtrim(rtrim(number_format((float) $cat->consumed_qty, 2, '.', ''), '0'), '.') }}</td>
                    <td>{{ $cat->total_qty }}</td>
                </tr>
                @empty
                <tr>
                    <td colspan="5">
                        <div class="summary-empty">No category summary available.</div>
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>

</div>

<script>
document.addEventListener('DOMContentLoaded', function () {
    const shell = document.getElementById('summaryFilterShell');
    const toggle = document.getElementById('summaryFilterToggle');
    const panel = document.getElementById('summaryFilterPanel');

    function setOpen(isOpen) {
        if (!shell || !toggle || !panel) return;
        shell.classList.toggle('is-open', isOpen);
        toggle.setAttribute('aria-expanded', isOpen ? 'true' : 'false');
        panel.setAttribute('aria-hidden', isOpen ? 'false' : 'true');
    }

    toggle?.addEventListener('click', function (event) {
        event.preventDefault();
        setOpen(!shell.classList.contains('is-open'));
    });

    document.addEventListener('click', function (event) {
        if (!shell || shell.contains(event.target)) return;
        setOpen(false);
    });

    document.addEventListener('keydown', function (event) {
        if (event.key === 'Escape') setOpen(false);
    });
});
</script>
@endsection
