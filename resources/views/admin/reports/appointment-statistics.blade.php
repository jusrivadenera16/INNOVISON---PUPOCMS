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
        font-size: 14px;
        line-height: 1.6;
        font-weight: 600;
    }

    .appointment-stats-header-actions {
        display: flex;
        align-items: center;
        justify-content: flex-end;
        gap: 12px;
        flex-wrap: wrap;
    }

    .appointment-stats-back,
    .appointment-stats-filter-toggle,
    .appointment-stats-filter-button {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        gap: 8px;
        min-height: 42px;
        padding: 0 16px;
        border-radius: 999px;
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
    }

    .appointment-stats-filter-toggle {
        min-height: 50px;
        min-width: 132px;
        border-radius: 14px;
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
        color: #111827;
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
        gap: 14px;
        margin-bottom: 18px;
    }

    .appointment-stat-card,
    .appointment-chart-card {
        border-radius: 14px;
        border: 1px solid rgba(112, 19, 27, 0.10);
        background: #ffffff;
        box-shadow: 0 14px 32px rgba(15, 23, 42, 0.07);
    }

    .appointment-stat-card {
        padding: 18px;
        border-left: 5px solid #70131B;
    }

    .appointment-stat-label {
        margin: 0;
        color: #64748b;
        font-size: 12px;
        font-weight: 900;
        text-transform: uppercase;
    }

    .appointment-stat-value {
        margin: 8px 0 2px;
        color: #111827;
        font-size: 28px;
        font-weight: 950;
    }

    .appointment-stat-hint {
        margin: 0;
        color: #64748b;
        font-size: 13px;
        font-weight: 650;
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
    html[data-theme="dark"] .appointment-chart-title,
    html[data-theme="dark"] .appointment-chart-label,
    html[data-theme="dark"] .appointment-chart-value {
        color: #f8fafc;
    }

    html[data-theme="dark"] .appointment-stats-subtitle,
    html[data-theme="dark"] .appointment-stat-label,
    html[data-theme="dark"] .appointment-stat-hint,
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
    $rangeLabel = $monthStart->format('F Y') === $monthEnd->format('F Y')
        ? $monthStart->format('F Y')
        : $monthStart->format('F Y') . ' to ' . $monthEnd->format('F Y');
    $barClasses = ['gold', 'green', 'blue', 'red', ''];
@endphp

<div class="appointment-stats-shell">
    <header class="appointment-stats-header">
        <div>
            <h1 class="appointment-stats-title">Appointment Statistics</h1>
            <p class="appointment-stats-subtitle">Clinic activity analytics for online appointments and walk-in consultations, filtered by date range, patient type, status, service, and source.</p>
        </div>
        <div class="appointment-stats-header-actions">
            <a href="{{ $reportsHomeUrl }}" class="appointment-stats-back">
                <x-outline-icon name="arrow-long-right" />
                Back to Reports
            </a>
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
        </div>
    </header>

    <section class="appointment-stats-summary" aria-label="Appointment summary cards">
        @foreach($summaryCards as $card)
            <article class="appointment-stat-card">
                <p class="appointment-stat-label">{{ $card['label'] }}</p>
                <div class="appointment-stat-value">{{ is_numeric($card['value']) ? number_format((float) $card['value'], is_float($card['value']) ? 1 : 0) : $card['value'] }}</div>
                <p class="appointment-stat-hint">{{ $card['hint'] }}</p>
            </article>
        @endforeach
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
