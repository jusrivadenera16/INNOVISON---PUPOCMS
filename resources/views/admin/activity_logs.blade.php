@extends('layouts.admin')

@section('title', 'Audit Trail')

@push('styles')
<style>
    .audit-wrap {
        display: grid;
        gap: 18px;
    }

    .audit-card {
        position: relative;
        background: linear-gradient(180deg, rgba(255,255,255,0.98), rgba(248,250,252,0.98));
        border: 1px solid rgba(148, 163, 184, 0.18);
        border-radius: 20px;
        box-shadow: 0 20px 50px rgba(15, 23, 42, 0.08);
        padding: 20px;
        overflow: hidden;
    }

    .audit-card::before {
        content: "";
        position: absolute;
        inset: 0 auto 0 0;
        width: 4px;
        background: linear-gradient(180deg, #facc15, #8B0000 70%, #5e0000);
        opacity: 0.9;
    }

    .audit-card,
    .audit-card *:not(.audit-role-badge):not(.audit-event-badge):not(.audit-status-badge):not(.audit-btn-primary):not(.audit-stat-value) {
        color: #111827;
    }

    .audit-head {
        display: flex;
        justify-content: space-between;
        align-items: flex-start;
        gap: 16px;
        flex-wrap: wrap;
    }

    .audit-head-main {
        display: grid;
        gap: 8px;
        max-width: 760px;
    }

    .audit-kicker {
        display: inline-flex;
        align-items: center;
        gap: 8px;
        width: fit-content;
        padding: 7px 11px;
        border-radius: 999px;
        background: rgba(139, 0, 0, 0.08);
        border: 1px solid rgba(139, 0, 0, 0.14);
        color: #8B0000;
        font-size: 11px;
        font-weight: 900;
        letter-spacing: 0.08em;
        text-transform: uppercase;
    }

    .audit-kicker span {
        width: 7px;
        height: 7px;
        border-radius: 50%;
        background: #facc15;
        box-shadow: 0 0 0 4px rgba(250, 204, 21, 0.14);
    }

    .audit-title {
        margin: 0;
        color: #0f172a;
        font-size: 26px;
        font-weight: 900;
        line-height: 1.08;
    }

    .audit-subtitle {
        margin: 0;
        color: #475569;
        font-size: 14px;
        line-height: 1.7;
        max-width: 72ch;
    }

    .audit-head-aside {
        display: grid;
        gap: 8px;
        justify-items: end;
        min-width: 220px;
    }

    .audit-head-note {
        color: #64748b;
        font-size: 12px;
        line-height: 1.5;
        text-align: right;
    }

    .audit-chip-row {
        display: flex;
        flex-wrap: wrap;
        justify-content: flex-end;
        gap: 8px;
    }

    .audit-chip {
        padding: 7px 10px;
        border-radius: 999px;
        background: #f8fafc;
        border: 1px solid #e2e8f0;
        color: #334155;
        font-size: 11px;
        font-weight: 700;
    }

    .audit-card-link {
        display: block;
        text-decoration: none;
    }

    .audit-stats {
        display: grid;
        grid-template-columns: repeat(5, minmax(0, 1fr));
        gap: 12px;
    }

    .audit-stat {
        position: relative;
        overflow: hidden;
        border: 1px solid rgba(148, 163, 184, 0.18);
        border-radius: 16px;
        padding: 15px 16px;
        background: linear-gradient(180deg, #ffffff, #f8fafc);
        box-shadow: inset 0 1px 0 rgba(255,255,255,0.88);
    }

    .audit-stat::after {
        content: "";
        position: absolute;
        inset: auto 0 0 0;
        height: 3px;
        background: linear-gradient(90deg, #facc15, #8B0000, #5e0000);
        opacity: 0.85;
    }

    .audit-stat-label {
        color: #475569;
        font-size: 11px;
        text-transform: uppercase;
        letter-spacing: 0.08em;
        font-weight: 800;
        margin-bottom: 8px;
    }

    .audit-stat-value {
        color: #0f172a;
        font-size: 28px;
        font-weight: 900;
        line-height: 1;
    }

    .audit-filters {
        display: grid;
        grid-template-columns: repeat(4, minmax(0, 1fr));
        gap: 12px;
    }

    .audit-filter-group {
        display: grid;
        gap: 6px;
    }

    .audit-filter-label {
        color: #475569;
        font-size: 11px;
        text-transform: uppercase;
        letter-spacing: 0.08em;
        font-weight: 800;
    }

    .audit-input,
    .audit-select {
        width: 100%;
        border: 1px solid #cbd5e1;
        border-radius: 12px;
        padding: 10px 12px;
        font-size: 13px;
        color: #0f172a;
        background: #fff;
        transition: border-color .18s ease, box-shadow .18s ease, transform .18s ease;
    }

    .audit-input:focus,
    .audit-select:focus {
        outline: none;
        border-color: #8B0000;
        box-shadow: 0 0 0 4px rgba(139, 0, 0, 0.08);
        transform: translateY(-1px);
    }

    .audit-filter-actions {
        display: flex;
        justify-content: flex-end;
        gap: 8px;
        margin-top: 6px;
    }

    .audit-btn {
        border: 1px solid transparent;
        border-radius: 10px;
        font-size: 12px;
        font-weight: 800;
        padding: 9px 13px;
        text-decoration: none;
        cursor: pointer;
        transition: transform .18s ease, background .18s ease, border-color .18s ease, color .18s ease, box-shadow .18s ease;
    }

    .audit-btn:hover {
        transform: translateY(-1px);
    }

    .audit-btn-primary {
        background: linear-gradient(135deg, #5e0000, #8B0000 60%, #a61b1b);
        color: #fff;
        box-shadow: 0 12px 22px rgba(91,0,0,0.18);
    }

    .audit-btn-primary:hover {
        background: #facc15;
        color: #8B0000;
    }

    .audit-btn-light {
        background: #fff;
        border-color: #cbd5e1;
        color: #334155;
    }

    .audit-btn-light:hover {
        border-color: #94a3b8;
        background: #f8fafc;
    }

    .audit-breakdowns {
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 12px;
    }

    .audit-collapsible {
        padding: 0;
    }

    .audit-collapsible details {
        position: relative;
        z-index: 1;
    }

    .audit-collapse-summary {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 12px;
        padding: 16px 20px;
        cursor: pointer;
        user-select: none;
        color: #0f172a;
        font-size: 14px;
        font-weight: 900;
        list-style: none;
    }

    .audit-collapse-summary::-webkit-details-marker {
        display: none;
    }

    .audit-collapse-summary::after {
        content: "";
        width: 10px;
        height: 10px;
        border-right: 2px solid currentColor;
        border-bottom: 2px solid currentColor;
        transform: rotate(45deg);
        transition: transform .18s ease;
        flex: 0 0 auto;
    }

    .audit-collapsible details[open] .audit-collapse-summary::after {
        transform: rotate(225deg) translate(-2px, -2px);
    }

    .audit-collapse-summary:hover {
        color: #8B0000;
    }

    .audit-collapse-summary-sub {
        color: #64748b;
        font-size: 11px;
        font-weight: 800;
        letter-spacing: 0.04em;
        text-transform: uppercase;
    }

    .audit-collapse-body {
        padding: 0 20px 20px;
    }

    .audit-list-title {
        margin: 0;
        color: #0f172a;
        font-size: 14px;
        font-weight: 900;
    }

    .audit-mini-list {
        margin: 0;
        padding: 0;
        list-style: none;
        display: grid;
        gap: 8px;
    }

    .audit-mini-item {
        display: flex;
        justify-content: space-between;
        align-items: center;
        background: #f8fafc;
        border: 1px solid #e2e8f0;
        border-radius: 12px;
        padding: 10px 12px;
        gap: 8px;
    }

    .audit-mini-label {
        color: #111827;
        font-size: 12px;
        font-weight: 700;
    }

    .audit-mini-count {
        color: #8B0000;
        font-size: 12px;
        font-weight: 900;
    }

    .audit-table-wrap {
        overflow-x: auto;
        border-radius: 16px;
    }

    .audit-table {
        width: 100%;
        border-collapse: separate;
        border-spacing: 0 10px;
        min-width: 1100px;
    }

    .audit-table thead th {
        text-align: left;
        font-size: 11px;
        text-transform: uppercase;
        color: #64748b;
        font-weight: 900;
        letter-spacing: 0.08em;
        padding: 0 12px 10px;
    }

    .audit-table tbody tr {
        background: linear-gradient(180deg, #ffffff, #f8fafc);
        box-shadow: 0 10px 24px rgba(15, 23, 42, 0.05);
        transition: transform .18s ease, box-shadow .18s ease;
    }

    .audit-table tbody tr:hover {
        transform: translateY(-2px);
        box-shadow: 0 18px 30px rgba(15, 23, 42, 0.09);
    }

    .audit-table td {
        border-top: 1px solid #edf2f7;
        border-bottom: 1px solid #edf2f7;
        padding: 12px;
        vertical-align: top;
        font-size: 13px;
        color: #1e293b;
        background: transparent;
    }

    .audit-table td:first-child {
        border-left: 1px solid #edf2f7;
        border-top-left-radius: 14px;
        border-bottom-left-radius: 14px;
    }

    .audit-table td:last-child {
        border-right: 1px solid #edf2f7;
        border-top-right-radius: 14px;
        border-bottom-right-radius: 14px;
    }

    .audit-avatar {
        width: 34px;
        height: 34px;
        border-radius: 50%;
        background: linear-gradient(135deg, #fee2e2, #fef3c7);
        color: #8B0000;
        font-size: 12px;
        font-weight: 900;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        flex-shrink: 0;
    }

    .audit-user {
        display: flex;
        align-items: center;
        gap: 10px;
    }

    .audit-role-badge,
    .audit-event-badge,
    .audit-status-badge {
        display: inline-flex;
        align-items: center;
        border-radius: 999px;
        padding: 3px 10px;
        font-size: 11px;
        font-weight: 800;
        text-transform: uppercase;
        letter-spacing: 0.04em;
    }

    .audit-role-admin { background: #dbeafe; color: #1e40af; }
    .audit-role-superadmin { background: #ede9fe; color: #5b21b6; }
    .audit-role-super_admin { background: #ede9fe; color: #5b21b6; }
    .audit-role-student_assistant { background: #dbeafe; color: #1e40af; }
    .audit-role-student { background: #dcfce7; color: #166534; }
    .audit-role-unknown { background: #e2e8f0; color: #334155; }

    .audit-event-view { background: #e0f2fe; color: #0369a1; }
    .audit-event-create { background: #dcfce7; color: #166534; }
    .audit-event-update { background: #fef3c7; color: #92400e; }
    .audit-event-delete { background: #fee2e2; color: #991b1b; }
    .audit-event-auth { background: #ede9fe; color: #5b21b6; }
    .audit-event-error { background: #fecaca; color: #7f1d1d; }
    .audit-event-action { background: #e2e8f0; color: #334155; }

    .audit-status-ok { background: #dcfce7; color: #166534; }
    .audit-status-failed { background: #fee2e2; color: #991b1b; }
    .audit-status-unknown { background: #e2e8f0; color: #334155; }

    .audit-mono {
        font-family: Consolas, "Courier New", monospace;
        font-size: 12px;
        color: #334155;
    }

    .audit-muted {
        color: #64748b;
    }

    .audit-empty {
        text-align: center;
        color: #64748b;
        font-size: 13px;
        padding: 28px 12px;
    }

    .audit-pagination {
        margin-top: 18px;
        padding: 14px 18px;
        display: flex;
        justify-content: center;
        border: 1px solid #edf2f7;
        border-radius: 12px;
        background: #ffffff;
        box-shadow: 0 10px 24px rgba(15, 23, 42, 0.04);
    }
    .audit-pagination .pagination {
        display: flex;
        align-items: center;
        justify-content: center;
        gap: 10px;
        flex-wrap: wrap;
        margin: 0;
        padding: 0;
        list-style: none;
    }
    .audit-pagination .pagination li {
        display: inline-flex;
    }
    .audit-pagination .pagination a,
    .audit-pagination .pagination span {
        min-width: 38px;
        height: 38px;
        padding: 0 14px;
        border-radius: 8px;
        border: 1px solid #e2e8f0;
        background: #ffffff;
        color: #334155;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        font-size: 12px;
        font-weight: 900;
        text-decoration: none;
        line-height: 1;
        transition: transform .18s ease, background .18s ease, color .18s ease, border-color .18s ease, box-shadow .18s ease;
    }
    .audit-pagination .pagination a:hover {
        transform: translateY(-1px);
        background: #fff7ed;
        border-color: #f8cfd4;
        color: #70131B;
        box-shadow: 0 10px 20px rgba(112, 19, 27, .10);
    }
    .audit-pagination .pagination .active span {
        background: #7f0010;
        border-color: #7f0010;
        color: #ffffff;
    }
    .audit-pagination .pagination .disabled span {
        background: #f8fafc;
        color: #94a3b8;
        cursor: not-allowed;
    }
    .audit-pagination .pagination svg {
        width: 14px;
        height: 14px;
    }

    html[data-theme="dark"] .audit-card {
        background: linear-gradient(180deg, rgba(17,24,39,0.98), rgba(15,23,42,0.96));
        border-color: rgba(148,163,184,0.18);
        box-shadow: 0 24px 54px rgba(0,0,0,0.26);
        color: #f8fafc !important;
    }

    html[data-theme="dark"] .audit-card::before {
        background: linear-gradient(180deg, #facc15, #8B0000 70%, #5e0000);
    }

    html[data-theme="dark"] .audit-title,
    html[data-theme="dark"] .audit-list-title,
    html[data-theme="dark"] .audit-collapse-summary,
    html[data-theme="dark"] .audit-stat-value,
    html[data-theme="dark"] .audit-mini-label,
    html[data-theme="dark"] .audit-table td {
        color: #f8fafc !important;
    }

    html[data-theme="dark"] .audit-subtitle,
    html[data-theme="dark"] .audit-head-note,
    html[data-theme="dark"] .audit-filter-label,
    html[data-theme="dark"] .audit-collapse-summary-sub,
    html[data-theme="dark"] .audit-mono,
    html[data-theme="dark"] .audit-muted,
    html[data-theme="dark"] .audit-empty {
        color: #cbd5e1 !important;
    }

    html[data-theme="dark"] .audit-chip,
    html[data-theme="dark"] .audit-stat,
    html[data-theme="dark"] .audit-mini-item,
    html[data-theme="dark"] .audit-input,
    html[data-theme="dark"] .audit-select,
    html[data-theme="dark"] .audit-btn-light {
        background: rgba(17,24,39,0.92);
        border-color: rgba(148,163,184,0.2);
        color: #f8fafc !important;
    }

    html[data-theme="dark"] .audit-input::placeholder {
        color: #cbd5e1;
        opacity: 0.78;
    }

    html[data-theme="dark"] .audit-mini-count {
        color: #fde68a !important;
    }

    html[data-theme="dark"] .audit-table tbody tr {
        background: linear-gradient(180deg, rgba(17,24,39,0.98), rgba(15,23,42,0.96));
        box-shadow: 0 10px 24px rgba(0, 0, 0, 0.18);
        color: #f8fafc !important;
    }

    html[data-theme="dark"] .audit-table thead th {
        color: #94a3b8;
    }

    html[data-theme="dark"] .audit-table td,
    html[data-theme="dark"] .audit-table td:first-child,
    html[data-theme="dark"] .audit-table td:last-child {
        border-color: rgba(148,163,184,0.14);
    }

    html[data-theme="dark"] .audit-table td *:not(.audit-role-badge):not(.audit-event-badge):not(.audit-status-badge):not(.audit-muted):not(.audit-mono) {
        color: #f8fafc !important;
    }

    html[data-theme="dark"] .audit-kicker {
        background: rgba(250, 204, 21, 0.12);
        border-color: rgba(250, 204, 21, 0.22);
        color: #fde68a !important;
    }

    html[data-theme="dark"] .audit-btn-light:hover,
    html[data-theme="dark"] .audit-btn-primary:hover {
        color: #111827;
    }

    @media (max-width: 1200px) {
        .audit-stats,
        .audit-filters {
            grid-template-columns: repeat(2, minmax(0, 1fr));
        }
    }

    @media (max-width: 760px) {
        .audit-stats,
        .audit-filters,
        .audit-breakdowns {
            grid-template-columns: 1fr;
        }

        .audit-head-aside {
            justify-items: start;
        }

        .audit-chip-row {
            justify-content: flex-start;
        }
    }
</style>
@endpush

@section('content')
@php
    $roleLabelMap = [
        'superadmin' => 'Super Admin',
        'admin' => 'Admin',
        'super_admin' => 'Super Admin (Legacy)',
        'student_assistant' => 'Admin (Legacy)',
        'student' => 'Student',
        'unknown' => 'Unknown',
    ];
@endphp

<div class="audit-wrap">
    <section class="audit-card">
        <div class="audit-head">
            <div class="audit-head-main">
                <div class="audit-kicker"><span></span> Security Console</div>
                <h1 class="audit-title">Professional Audit Trail</h1>
                <p class="audit-subtitle">Monitor all user activities across students, student assistants, and clinic administrators with a cleaner, high-signal view.</p>
            </div>
            <div class="audit-head-aside">
                <div class="audit-head-note">Records shown: {{ $logs->count() }} of {{ number_format($logs->total()) }}</div>
                <div class="audit-chip-row">
                    <span class="audit-chip">Live feed</span>
                    <span class="audit-chip">Secure history</span>
                    <span class="audit-chip">Clinic console</span>
                </div>
            </div>
        </div>
    </section>

    <section class="audit-stats">
        <article class="audit-stat">
            <div class="audit-stat-label">Total Events</div>
            <div class="audit-stat-value">{{ number_format($totalEvents) }}</div>
        </article>
        <article class="audit-stat">
            <div class="audit-stat-label">Events Today</div>
            <div class="audit-stat-value">{{ number_format($todayEvents) }}</div>
        </article>
        <article class="audit-stat">
            <div class="audit-stat-label">Unique Actors</div>
            <div class="audit-stat-value">{{ number_format($uniqueActors) }}</div>
        </article>
        <article class="audit-stat">
            <div class="audit-stat-label">Failed Events</div>
            <div class="audit-stat-value">{{ number_format($failedEvents) }}</div>
        </article>
        <article class="audit-stat">
            <div class="audit-stat-label">Emergency Events</div>
            <div class="audit-stat-value">{{ number_format($emergencyEvents) }}</div>
        </article>
    </section>

    <section class="audit-card audit-collapsible">
        <details open>
            <summary class="audit-collapse-summary">
                <span>Audit Filters</span>
                <span class="audit-collapse-summary-sub">Search and refine records</span>
            </summary>
            <form method="GET" action="{{ route('admin.logs') }}" class="audit-collapse-body">
                <div class="audit-filters">
                    <div class="audit-filter-group">
                        <label class="audit-filter-label" for="audit_q">Search</label>
                        <input id="audit_q" name="q" type="text" class="audit-input" value="{{ request('q') }}" placeholder="Name, action, module, route, IP...">
                    </div>

                    <div class="audit-filter-group">
                        <label class="audit-filter-label" for="audit_actor_role">Actor Role</label>
                        <select id="audit_actor_role" name="actor_role" class="audit-select">
                            <option value="">All Roles</option>
                            @foreach($roleOptions as $roleOption)
                                <option value="{{ $roleOption }}" @selected(request('actor_role') === $roleOption)>
                                    {{ $roleLabelMap[$roleOption] ?? ucwords(str_replace('_', ' ', $roleOption)) }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div class="audit-filter-group">
                        <label class="audit-filter-label" for="audit_event_type">Event Type</label>
                        <select id="audit_event_type" name="event_type" class="audit-select">
                            <option value="">All Types</option>
                            @foreach($eventTypeOptions as $eventTypeOption)
                                <option value="{{ $eventTypeOption }}" @selected(request('event_type') === $eventTypeOption)>
                                    {{ strtoupper($eventTypeOption) }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div class="audit-filter-group">
                        <label class="audit-filter-label" for="audit_module">Module</label>
                        <select id="audit_module" name="module" class="audit-select">
                            <option value="">All Modules</option>
                            @foreach($moduleOptions as $moduleOption)
                                <option value="{{ $moduleOption }}" @selected(request('module') === $moduleOption)>
                                    {{ $moduleOption }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div class="audit-filter-group">
                        <label class="audit-filter-label" for="audit_http_method">HTTP Method</label>
                        <select id="audit_http_method" name="http_method" class="audit-select">
                            <option value="">All Methods</option>
                            @foreach(['GET', 'POST', 'PUT', 'PATCH', 'DELETE'] as $methodOption)
                                <option value="{{ $methodOption }}" @selected(request('http_method') === $methodOption)>
                                    {{ $methodOption }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div class="audit-filter-group">
                        <label class="audit-filter-label" for="audit_status_class">Status Class</label>
                        <select id="audit_status_class" name="status_class" class="audit-select">
                            <option value="">All Statuses</option>
                            <option value="success" @selected(request('status_class') === 'success')>Success (&lt; 400)</option>
                            <option value="error" @selected(request('status_class') === 'error')>Error (400+)</option>
                        </select>
                    </div>

                    <div class="audit-filter-group">
                        <label class="audit-filter-label" for="audit_date_from">Date From</label>
                        <input id="audit_date_from" name="date_from" type="date" class="audit-input" value="{{ request('date_from') }}">
                    </div>

                    <div class="audit-filter-group">
                        <label class="audit-filter-label" for="audit_date_to">Date To</label>
                        <input id="audit_date_to" name="date_to" type="date" class="audit-input" value="{{ request('date_to') }}">
                    </div>

                    <div class="audit-filter-group">
                        <label class="audit-filter-label" for="audit_per_page">Rows</label>
                        <select id="audit_per_page" name="per_page" class="audit-select">
                            @foreach(['25' => '25 per page', '50' => '50 per page', '100' => '100 per page', 'all' => 'Show all'] as $rowsOption => $rowsLabel)
                                <option value="{{ $rowsOption }}" @selected(($perPageInput ?? '25') === $rowsOption)>
                                    {{ $rowsLabel }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                </div>

                <div class="audit-filter-actions">
                    <a href="{{ route('admin.logs') }}" class="audit-btn audit-btn-light">Reset</a>
                    <button type="submit" class="audit-btn audit-btn-primary">Apply Filters</button>
                </div>
            </form>
        </details>
    </section>

    <section class="audit-breakdowns">
        <article class="audit-card audit-collapsible">
            <details open>
                <summary class="audit-collapse-summary">
                    <span class="audit-list-title">Activity by Role</span>
                    <span class="audit-collapse-summary-sub">Actor totals</span>
                </summary>
                <div class="audit-collapse-body">
                    <ul class="audit-mini-list">
                        @forelse($roleBreakdown as $row)
                            <li class="audit-mini-item">
                                <span class="audit-mini-label">{{ $roleLabelMap[$row->role] ?? ucwords(str_replace('_', ' ', $row->role)) }}</span>
                                <span class="audit-mini-count">{{ number_format($row->total) }}</span>
                            </li>
                        @empty
                            <li class="audit-empty">No role activity yet.</li>
                        @endforelse
                    </ul>
                </div>
            </details>
        </article>
        <article class="audit-card audit-collapsible">
            <details open>
                <summary class="audit-collapse-summary">
                    <span class="audit-list-title">Top Modules</span>
                    <span class="audit-collapse-summary-sub">Most active areas</span>
                </summary>
                <div class="audit-collapse-body">
                    <ul class="audit-mini-list">
                        @forelse($moduleBreakdown as $row)
                            <li class="audit-mini-item">
                                <span class="audit-mini-label">{{ $row->module_name }}</span>
                                <span class="audit-mini-count">{{ number_format($row->total) }}</span>
                            </li>
                        @empty
                            <li class="audit-empty">No module activity yet.</li>
                        @endforelse
                    </ul>
                </div>
            </details>
        </article>
    </section>

    <section class="audit-card">
        <div class="audit-table-wrap">
            <table class="audit-table">
                <thead>
                    <tr>
                        <th>Timestamp</th>
                        <th>Actor</th>
                        <th>Role</th>
                        <th>Module</th>
                        <th>Event</th>
                        <th>Action</th>
                        <th>Subject</th>
                        <th>HTTP</th>
                        <th>Status</th>
                        <th>IP</th>
                        <th>Description</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($logs as $log)
                        @php
                            $roleKey = strtolower((string) ($log->user_role ?? 'unknown'));
                            $eventKey = strtolower((string) ($log->event_type ?? 'action'));
                            $statusCode = $log->status_code;
                            $statusClass = is_null($statusCode) ? 'unknown' : ((int) $statusCode >= 400 ? 'failed' : 'ok');
                            $subjectText = trim((string) (($log->subject_type ?? '-') . ((string) ($log->subject_id ?? '') !== '' ? ' #' . $log->subject_id : '')));
                        @endphp
                        <tr>
                            <td>
                                <div>{{ optional($log->created_at)->format('M d, Y') }}</div>
                                <div class="audit-muted" style="font-size:12px;">{{ optional($log->created_at)->format('g:i:s A') }}</div>
                            </td>
                            <td>
                                <div class="audit-user">
                                    <span class="audit-avatar">{{ strtoupper(substr((string) ($log->user_name ?? 'U'), 0, 1)) }}</span>
                                    <div>
                                        <div style="font-weight:700;">{{ $log->user_name ?? 'Unknown User' }}</div>
                                        <div class="audit-muted" style="font-size:11px;">UID: {{ $log->user_id ?? 'N/A' }}</div>
                                    </div>
                                </div>
                            </td>
                            <td>
                                <span class="audit-role-badge audit-role-{{ $roleKey }}">
                                    {{ $roleLabelMap[$roleKey] ?? ucwords(str_replace('_', ' ', $roleKey)) }}
                                </span>
                            </td>
                            <td>{{ $log->module ?? 'Uncategorized' }}</td>
                            <td>
                                <span class="audit-event-badge audit-event-{{ $eventKey }}">
                                    {{ strtoupper($eventKey) }}
                                </span>
                            </td>
                            <td>{{ $log->action }}</td>
                            <td>{{ $subjectText !== '' ? $subjectText : '-' }}</td>
                            <td>
                                <div class="audit-mono">{{ $log->http_method ?? '-' }}</div>
                                <div class="audit-mono audit-muted">{{ $log->request_path ?? '-' }}</div>
                            </td>
                            <td>
                                <span class="audit-status-badge audit-status-{{ $statusClass }}">
                                    {{ is_null($statusCode) ? 'N/A' : $statusCode }}
                                </span>
                            </td>
                            <td class="audit-mono">{{ $log->ip_address ?? '-' }}</td>
                            <td style="min-width:250px;">
                                <div>{{ $log->description }}</div>
                                @if($log->route_name)
                                    <div class="audit-muted" style="margin-top:4px; font-size:11px;">Route: {{ $log->route_name }}</div>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="11" class="audit-empty">No audit records found for the selected filters.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($logs->hasPages())
            <div class="audit-pagination">
                {{ $logs->links() }}
            </div>
        @endif
    </section>
</div>
@endsection
