@extends('layouts.admin')

@section('title', 'Dashboard')

@push('styles')
<style>
    /* --- DASHBOARD CONTAINER --- */
    .dashboard-container {
        max-width: 1400px;
        margin: 0 auto;
    }

    /* --- 1. STATS ROW (The "MedTrackr" Style) --- */
    .stats-grid {
        display: grid;
        grid-template-columns: repeat(4, 1fr);
        gap: 20px;
        margin-bottom: 30px;
    }

    .stat-card {
        background: #70131B; /* Dark Navy/Slate background like reference */
        color: #fff;
        border-radius: 16px;
        padding: 24px 20px;
        position: relative;
        overflow: hidden;
        display: flex;
        flex-direction: column;
        justify-content: space-between;
        min-height: 140px;
        box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.1);
        transition: transform 0.2s ease, box-shadow 0.2s ease;
        z-index: 0;
    }

    .stat-card::before {
        content: "";
        position: absolute;
        right: -28px;
        bottom: -34px;
        width: 170px;
        height: 110px;
        background: url("data:image/svg+xml,%3Csvg viewBox='0 0 170 110' xmlns='http://www.w3.org/2000/svg'%3E%3Cpath d='M0 62C24 28 50 88 76 54C103 20 126 72 158 34L170 110H0Z' fill='%23facc15'/%3E%3C/svg%3E") center / 100% 100% no-repeat;
        opacity: .72;
        transition: background .22s ease, opacity .22s ease, transform .22s ease;
        z-index: 0;
    }

    .stat-card::after {
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
        z-index: 0;
    }

    .stat-card:hover {
        transform: translateY(-5px);
        box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.2);
    }

    .stat-card:hover::before {
        background: url("data:image/svg+xml,%3Csvg viewBox='0 0 170 110' xmlns='http://www.w3.org/2000/svg'%3E%3Cpath d='M0 62C24 28 50 88 76 54C103 20 126 72 158 34L170 110H0Z' fill='%237f1d2d'/%3E%3C/svg%3E") center / 100% 100% no-repeat;
        opacity: .86;
        transform: translateY(-4px);
    }

    .stat-card:hover::after {
        transform: translateX(135%);
    }

    .stat-card > * {
        position: relative;
        z-index: 1;
    }

    .stat-card-top {
        display: flex;
        justify-content: space-between;
        align-items: flex-start;
        gap: 12px;
    }

    .stat-percent {
        flex: 0 0 auto;
        padding: 5px 9px;
        border-radius: 999px;
        background: rgba(250, 204, 21, .16);
        border: 1px solid rgba(250, 204, 21, .32);
        color: #facc15;
        font-size: 11px;
        font-weight: 900;
        line-height: 1;
        white-space: nowrap;
    }

    /* Distinct Colors for active states if needed, 
       but keeping them uniform looks more professional like the reference */
    
    .stat-label {
        position: relative;
        z-index: 1;
        font-size: 13px;
        font-weight: 500;
        color: #94a3b8; /* Muted gray text */
        text-transform: uppercase;
        letter-spacing: 0.5px;
        margin-bottom: 10px;
    }

    .stat-value {
        position: relative;
        z-index: 1;
        font-size: 38px;
        font-weight: 700;
        color: #fff;
        margin-bottom: 12px;
        line-height: 1;
    }

    /* The "Pill" at the bottom (e.g. "Last 7 days") */
    .stat-badge {
        position: relative;
        z-index: 1;
        display: inline-block;
        font-size: 11px;
        font-weight: 600;
        padding: 4px 10px;
        border-radius: 6px;
        width: fit-content;
    }

    /* Badge Colors */
    .badge-neutral { background: rgba(255,255,255,0.1); color: #cbd5e1; }
    .badge-warning { background: rgba(245, 158, 11, 0.2); color: #fbbf24; } /* Orange tint */
    .badge-success { background: rgba(16, 185, 129, 0.2); color: #34d399; } /* Green tint */
    .badge-info    { background: rgba(59, 130, 246, 0.2); color: #60a5fa; } /* Blue tint */
    .badge-danger  { background: rgba(239, 68, 68, 0.2);  color: #f87171; } /* Red tint */


    /* --- 2. RECENT ACTIVITY PANEL --- */
    .panel {
        background: #fff; /* Keep white for readability of table */
        border-radius: 16px;
        padding: 24px;
        box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.05);
        border: 1px solid #e2e8f0;
    }

    .panel-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 24px;
    }

    .panel-title {
        font-size: 18px;
        font-weight: 700;
        color: #0f172a;
        margin: 0;
    }

    .btn-view-all {
        font-size: 13px;
        font-weight: 600;
        color: #8B0000;
        text-decoration: none;
        padding: 6px 12px;
        background: #fff1f2;
        border-radius: 6px;
        transition: 0.2s;
    }
    .btn-view-all:hover { background: #ffe4e6; }
    html[data-theme="dark"] .btn-view-all {
        color: #0f172a;
        background: #f8fafc;
    }
    html[data-theme="dark"] .btn-view-all:hover {
        background: #ffffff;
    }

    /* Table Styles */
    table { width: 100%; border-collapse: separate; border-spacing: 0; }
    th { 
        text-align: left; 
        padding: 12px 16px; 
        color: #64748b; 
        font-size: 12px; 
        font-weight: 600; 
        text-transform: uppercase; 
        border-bottom: 1px solid #f1f5f9;
    }
    td { 
        padding: 16px; 
        border-bottom: 1px solid #f8fafc; 
        font-size: 14px; 
        color: #334155; 
    }
    tr:last-child td { border-bottom: none; }

    /* Status Pills in Table */
    .status-pill { padding: 4px 10px; border-radius: 99px; font-size: 11px; font-weight: 700; text-transform: uppercase; }
    .st-approved { background: #dcfce7; color: #15803d; }
    .st-pending { background: #fffbeb; color: #b45309; }
    .st-completed { background: #dbeafe; color: #1e40af; }
    .st-cancelled { background: #fee2e2; color: #b91c1c; }

    .dashboard-chart-grid {
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 16px;
        margin: -12px 0 22px;
    }

    .dashboard-chart-card {
        background: rgba(255, 255, 255, 0.96);
        border: 1px solid rgba(112, 19, 27, 0.12);
        border-radius: 16px;
        padding: 16px;
        box-shadow: 0 12px 28px rgba(15, 23, 42, 0.08);
    }

    .dashboard-chart-head {
        display: flex;
        align-items: flex-start;
        justify-content: space-between;
        gap: 12px;
        margin-bottom: 12px;
    }

    .dashboard-chart-title {
        margin: 0;
        color: #0f172a;
        font-size: 15px;
        font-weight: 800;
    }

    .dashboard-chart-copy {
        margin: 3px 0 0;
        color: #64748b;
        font-size: 12px;
        line-height: 1.4;
    }

    .dashboard-chart-total {
        min-width: 54px;
        text-align: right;
        color: #8B0000;
        font-size: 22px;
        font-weight: 900;
        line-height: 1;
    }

    .dashboard-chart-total span {
        display: block;
        margin-top: 3px;
        color: #94a3b8;
        font-size: 10px;
        font-weight: 800;
        letter-spacing: 0.06em;
        text-transform: uppercase;
    }

    .dashboard-chart-bars {
        display: grid;
        gap: 9px;
    }

    .dashboard-chart-row {
        display: grid;
        grid-template-columns: 92px 1fr 42px;
        align-items: center;
        gap: 10px;
    }

    .dashboard-chart-label {
        color: #334155;
        font-size: 12px;
        font-weight: 800;
        white-space: nowrap;
    }

    .dashboard-chart-track {
        height: 9px;
        overflow: hidden;
        border-radius: 999px;
        background: #f1f5f9;
    }

    .dashboard-chart-fill {
        display: block;
        height: 100%;
        min-width: 4px;
        border-radius: inherit;
        background: #94a3b8;
    }

    .dashboard-chart-fill.success { background: #22c55e; }
    .dashboard-chart-fill.warning { background: #f59e0b; }
    .dashboard-chart-fill.info { background: #3b82f6; }
    .dashboard-chart-fill.danger { background: #ef4444; }

    .dashboard-chart-value {
        color: #0f172a;
        font-size: 12px;
        font-weight: 900;
        text-align: right;
    }

    html[data-theme="dark"] .dashboard-chart-card {
        background: rgba(15, 23, 42, 0.78);
        border-color: rgba(250, 204, 21, 0.16);
    }

    html[data-theme="dark"] .dashboard-chart-title,
    html[data-theme="dark"] .dashboard-chart-value,
    html[data-theme="dark"] .dashboard-chart-label {
        color: #f8fafc;
    }

    html[data-theme="dark"] .dashboard-chart-copy,
    html[data-theme="dark"] .dashboard-chart-total span {
        color: #cbd5e1;
    }

    html[data-theme="dark"] .dashboard-chart-total {
        color: #fde68a;
    }

    html[data-theme="dark"] .dashboard-chart-track {
        background: rgba(255, 255, 255, 0.12);
    }

    /* Responsive */
    @media (max-width: 1200px) {
        .stats-grid { grid-template-columns: repeat(2, 1fr); }
        .dashboard-chart-grid { grid-template-columns: 1fr; }
    }
    @media (max-width: 768px) {
        .stats-grid { grid-template-columns: repeat(2, 1fr); }
        .dashboard-chart-row { grid-template-columns: 84px 1fr 36px; }
    }
    @media (max-width: 500px) { .stats-grid { grid-template-columns: 1fr; } }
</style>
@endpush

@section('content')
@php
    $role = \App\Models\User::normalizeRole(optional(auth()->user())->user_role ?? '');
    $appointmentsUrl = $role === \App\Models\User::ROLE_ADMIN ? url('/assistant/appointments') : url('/admin/appointments');
    $dashboardPercent = function ($value, $base) {
        $base = max(0, (int) $base);
        return $base > 0 ? (int) round(((int) $value / $base) * 100) : 0;
    };
    $inventoryInStockRow = collect($inventoryChartStats)->firstWhere('label', 'In Stock');
    $inventoryInStock = (int) ($inventoryInStockRow['value'] ?? 0);
    $completionRate = $dashboardPercent($completed, $total);
    $pendingRate = $dashboardPercent($pending, $total);
    $todayRate = $dashboardPercent($upcoming, $total);
    $stockHealthRate = $dashboardPercent($inventoryInStock, $inventoryTotal);
@endphp
<div class="dashboard-container">
    <div class="stats-grid">
        
        <div class="stat-card">
            <div class="stat-card-top">
                <div class="stat-label">Total Appointments</div>
                <span class="stat-percent">{{ $completionRate }}%</span>
            </div>
            <div>
                <div class="stat-value">{{ $total }}</div>
            </div>
            <div class="stat-badge badge-neutral">Completion Rate</div>
        </div>

        <div class="stat-card">
            <div class="stat-card-top">
                <div class="stat-label">Pending Requests</div>
                <span class="stat-percent">{{ $pendingRate }}%</span>
            </div>
            <div>
                <div class="stat-value">{{ $pending }}</div>
            </div>
            <div class="stat-badge badge-warning">Action Needed</div>
        </div>

        <div class="stat-card">
            <div class="stat-card-top">
                <div class="stat-label">Scheduled Today</div>
                <span class="stat-percent">{{ $todayRate }}%</span>
            </div>
            <div>
                <div class="stat-value">{{ $upcoming }}</div>
            </div>
            <div class="stat-badge badge-success">Scheduled</div>
        </div>

        <div class="stat-card">
            <div class="stat-card-top">
                <div class="stat-label">Inventory Items</div>
                <span class="stat-percent">{{ $stockHealthRate }}%</span>
            </div>
            <div>
                <div class="stat-value">{{ $inventoryTotal }}</div>
            </div>
            <div class="stat-badge badge-info">Stock Health</div>
        </div>

    </div>

    @php
        $appointmentChartMax = max(1, collect($appointmentChartStats)->max('value') ?? 0);
        $inventoryChartMax = max(1, collect($inventoryChartStats)->max('value') ?? 0);
    @endphp

    <div class="dashboard-chart-grid" aria-label="Dashboard statistics charts">
        <section class="dashboard-chart-card">
            <div class="dashboard-chart-head">
                <div>
                    <h3 class="dashboard-chart-title">Appointment Status</h3>
                    <p class="dashboard-chart-copy">Quick count of current request outcomes.</p>
                </div>
                <div class="dashboard-chart-total">{{ $total }}<span>Total</span></div>
            </div>
            <div class="dashboard-chart-bars">
                @foreach($appointmentChartStats as $chartItem)
                    @php $width = max(4, round(((int) $chartItem['value'] / $appointmentChartMax) * 100)); @endphp
                    <div class="dashboard-chart-row">
                        <div class="dashboard-chart-label">{{ $chartItem['label'] }}</div>
                        <div class="dashboard-chart-track" aria-hidden="true">
                            <span class="dashboard-chart-fill {{ $chartItem['class'] }}" style="width: {{ $width }}%;"></span>
                        </div>
                        <div class="dashboard-chart-value">{{ number_format($chartItem['value']) }}</div>
                    </div>
                @endforeach
            </div>
        </section>

        <section class="dashboard-chart-card">
            <div class="dashboard-chart-head">
                <div>
                    <h3 class="dashboard-chart-title">Inventory Health</h3>
                    <p class="dashboard-chart-copy">Stock condition across encoded items.</p>
                </div>
                <div class="dashboard-chart-total">{{ $inventoryTotal }}<span>Items</span></div>
            </div>
            <div class="dashboard-chart-bars">
                @foreach($inventoryChartStats as $chartItem)
                    @php $width = max(4, round(((int) $chartItem['value'] / $inventoryChartMax) * 100)); @endphp
                    <div class="dashboard-chart-row">
                        <div class="dashboard-chart-label">{{ $chartItem['label'] }}</div>
                        <div class="dashboard-chart-track" aria-hidden="true">
                            <span class="dashboard-chart-fill {{ $chartItem['class'] }}" style="width: {{ $width }}%;"></span>
                        </div>
                        <div class="dashboard-chart-value">{{ number_format($chartItem['value']) }}</div>
                    </div>
                @endforeach
            </div>
        </section>
    </div>

    <div class="panel">
        <div class="panel-header">
            <h3 class="panel-title">Recent Activity</h3>
            <a href="{{ $appointmentsUrl }}" class="btn-view-all">View All</a>
        </div>

        <table>
            <thead>
                <tr>
                    <th>Student Name</th>
                    <th>Service</th>
                    <th>Date</th>
                    <th>Status</th>
                </tr>
            </thead>
            <tbody>
                @forelse($recentAppointments as $appt)
                    <tr>
                        <td style="font-weight: 600;">
                            {{ $appt->name }}<br>
                            <span style="font-size:11px; color:#94a3b8; font-weight:400;">{{ $appt->student_number ?: $appt->student_id }}</span>
                        </td>
                        <td>{{ $appt->service }}</td>
                        <td>
                            {{ \Carbon\Carbon::parse($appt->date)->format('M d') }}
                            <span style="color:#cbd5e1; margin:0 4px;">&bull;</span>
                            <span style="color:#64748b; font-size:12px;">{{ \Carbon\Carbon::parse($appt->time)->format('g:i A') }}</span>
                        </td>
                        <td>
                            @if($appt->status == 'Approved')
                                <span class="status-pill st-approved">Approved</span>
                            @elseif($appt->status == 'Pending')
                                <span class="status-pill st-pending">Pending</span>
                            @elseif($appt->status == 'Completed')
                                <span class="status-pill st-completed">Completed</span>
                            @elseif($appt->status == 'Missed')
                                <span class="status-pill st-cancelled">Missed</span>
                            @elseif($appt->status == 'Expired')
                                <span class="status-pill st-cancelled">Expired</span>
                            @else
                                <span class="status-pill st-cancelled">Cancelled</span>
                            @endif
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="4" style="text-align: center; padding: 30px; color: #94a3b8;">No recent activity.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>

</div>
@endsection
