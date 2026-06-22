@extends('layouts.admin')

@section('title', 'Health Forms Report')

@push('styles')
<style>
    .health-forms-shell {
        max-width: 1380px;
        margin: 0 auto;
        padding: 22px;
    }
    .health-forms-head {
        display: flex;
        justify-content: space-between;
        align-items: flex-start;
        gap: 18px;
        margin-bottom: 24px;
    }
    .health-forms-title {
        margin: 0;
        font-size: 30px;
        font-weight: 900;
        color: #ffffff;
        letter-spacing: -0.03em;
    }
    .health-forms-copy {
        margin: 8px 0 0;
        color: rgba(255,255,255,0.78);
        font-size: 14px;
        line-height: 1.6;
        max-width: 720px;
    }
    .health-forms-back {
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

    .health-forms-back::after {
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

    .health-forms-back:hover::after {
        left: 125%;
    }

    .health-forms-back:hover,
    .health-forms-back:focus {
        color: #70131B;
        border-color: rgba(112, 19, 27, 0.48);
        background: #ffffff;
        box-shadow: 0 0 0 2px rgba(112, 19, 27, 0.12), 0 12px 28px rgba(15, 23, 42, 0.12);
        outline: none;
    }
    .health-forms-stat-grid {
        display: grid;
        grid-template-columns: repeat(3, minmax(0, 1fr));
        gap: 18px;
        margin-bottom: 24px;
    }
    .health-forms-stat-card {
        background: #ffffff;
        border-radius: 18px;
        padding: 20px 22px;
        box-shadow: 0 14px 30px rgba(15, 23, 42, 0.12);
        border-top: 5px solid #7f1d2d;
    }
    .health-forms-stat-card span {
        display: block;
        font-size: 12px;
        font-weight: 800;
        letter-spacing: 0.06em;
        text-transform: uppercase;
        color: #64748b;
    }
    .health-forms-stat-card strong {
        display: block;
        margin-top: 8px;
        font-size: 28px;
        line-height: 1.1;
        font-weight: 900;
        color: #111827;
    }
    .health-forms-layout {
        display: grid;
        grid-template-columns: 320px minmax(0, 1fr);
        gap: 22px;
        align-items: start;
    }
    .health-forms-panel {
        background: #ffffff;
        border-radius: 20px;
        padding: 22px;
        box-shadow: 0 14px 30px rgba(15, 23, 42, 0.12);
    }
    .health-forms-panel h3 {
        margin: 0 0 14px;
        font-size: 17px;
        font-weight: 900;
        color: #7f1d2d;
    }
    .health-forms-filter-form {
        display: grid;
        gap: 14px;
    }
    .health-forms-field label {
        display: block;
        margin-bottom: 7px;
        font-size: 12px;
        font-weight: 800;
        letter-spacing: 0.04em;
        text-transform: uppercase;
        color: #475569;
    }
    .health-forms-field input,
    .health-forms-field select {
        width: 100%;
        height: 46px;
        border: 1px solid #cbd5e1;
        border-radius: 12px;
        padding: 0 14px;
        font-size: 14px;
        color: #111827;
        background: #ffffff;
    }
    .health-forms-filter-actions {
        display: flex;
        gap: 10px;
    }
    .health-forms-btn {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        min-height: 44px;
        border-radius: 12px;
        padding: 0 16px;
        text-decoration: none;
        font-weight: 800;
        cursor: pointer;
        border: none;
    }
    .health-forms-btn.primary {
        background: #7f1d2d;
        color: #ffffff;
    }
    .health-forms-btn.secondary {
        background: #eef2f7;
        color: #334155;
    }
    .health-forms-table-wrap {
        background: #ffffff;
        border-radius: 20px;
        padding: 12px 12px 4px;
        box-shadow: 0 14px 30px rgba(15, 23, 42, 0.12);
        overflow-x: auto;
    }
    .health-forms-table {
        width: 100%;
        border-collapse: collapse;
    }
    .health-forms-table th,
    .health-forms-table td {
        padding: 14px 12px;
        border-bottom: 1px solid #e2e8f0;
        font-size: 14px;
        text-align: left;
        color: #111827;
        vertical-align: middle;
    }
    .health-forms-table th {
        font-size: 12px;
        font-weight: 900;
        text-transform: uppercase;
        letter-spacing: 0.05em;
        color: #64748b;
    }
    .health-status-badge {
        display: inline-flex;
        align-items: center;
        padding: 6px 10px;
        border-radius: 999px;
        background: #dcfce7;
        color: #166534;
        font-size: 11px;
        font-weight: 800;
        text-transform: uppercase;
    }
    .health-condition-badge {
        display: inline-flex;
        align-items: center;
        padding: 6px 10px;
        border-radius: 999px;
        background: #fef2f2;
        color: #991b1b;
        font-size: 11px;
        font-weight: 800;
        text-transform: uppercase;
    }
    .health-condition-badge.none {
        background: #eff6ff;
        color: #1d4ed8;
    }
    .health-forms-empty {
        padding: 44px 24px;
        text-align: center;
        color: #64748b;
        font-weight: 700;
    }
    .health-forms-pagination {
        margin-top: 18px;
    }
    .health-forms-pagination .pagination {
        justify-content: center;
    }
</style>
@endpush

@section('content')
@php
    $role = \App\Models\User::normalizeRole(optional(auth()->user())->user_role ?? '');
    $reportsUrl = $role === \App\Models\User::ROLE_ADMIN ? url('/assistant/reports') : url('/admin/reports');
@endphp
<div class="health-forms-shell">
    <div class="health-forms-head">
        <div>
            <h1 class="health-forms-title">Health Forms</h1>
            <p class="health-forms-copy">Issued health forms summarized by course for the selected date range.</p>
        </div>
        <a href="{{ $reportsUrl }}" class="health-forms-back">&larr; Back to Reports</a>
    </div>

    <div class="health-forms-stat-grid">
        <div class="health-forms-stat-card">
            <span>Total Issued</span>
            <strong>{{ $totalIssued }}</strong>
        </div>
        <div class="health-forms-stat-card">
            <span>Courses Covered</span>
            <strong>{{ $totalCourses }}</strong>
        </div>
        <div class="health-forms-stat-card">
            <span>Top Course</span>
            <strong style="font-size: 20px; line-height: 1.3;">{{ $topCourse }}</strong>
        </div>
    </div>

    <div class="health-forms-layout">
        <aside class="health-forms-panel">
            <h3>Filter Records</h3>
            <form class="health-forms-filter-form" method="GET">
                <div class="health-forms-field">
                    <label for="healthFormsDateFrom">From</label>
                    <input id="healthFormsDateFrom" type="text" name="date_from" value="{{ $dateFrom->format('d/m/Y') }}" placeholder="DD/MM/YYYY" inputmode="numeric" pattern="\d{2}/\d{2}/\d{4}" required>
                </div>
                <div class="health-forms-field">
                    <label for="healthFormsDateTo">To</label>
                    <input id="healthFormsDateTo" type="text" name="date_to" value="{{ $dateTo->format('d/m/Y') }}" placeholder="DD/MM/YYYY" inputmode="numeric" pattern="\d{2}/\d{2}/\d{4}" required>
                </div>
                <div class="health-forms-field">
                    <label for="healthFormsSearch">Search</label>
                    <input id="healthFormsSearch" type="text" name="q" value="{{ $search }}" placeholder="Course name">
                </div>
                <div class="health-forms-filter-actions">
                    <button type="submit" class="health-forms-btn primary">Apply</button>
                    <a href="{{ request()->url() }}" class="health-forms-btn secondary">Reset</a>
                </div>
            </form>
        </aside>

        <div>
            <div class="health-forms-table-wrap">
                <table class="health-forms-table">
                    <thead>
                        <tr>
                            <th>Course</th>
                            <th>Issued Forms</th>
                            <th>With Condition</th>
                            <th>No Condition</th>
                            <th>Last Issued</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($issuedForms as $form)
                            <tr>
                                <td>{{ $form->course }}</td>
                                <td><span class="health-status-badge">{{ $form->issued_count }}</span></td>
                                <td><span class="health-condition-badge">{{ $form->with_condition_count }}</span></td>
                                <td><span class="health-condition-badge none">{{ $form->no_condition_count }}</span></td>
                                <td>{{ optional($form->last_issued_at)->format('M d, Y') }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="health-forms-empty">No issued health forms found for this filter.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <div class="health-forms-pagination">
                {{ $issuedForms->withQueryString()->links() }}
            </div>
        </div>
    </div>
</div>
@endsection
