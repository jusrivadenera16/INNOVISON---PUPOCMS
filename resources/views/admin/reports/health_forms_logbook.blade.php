@extends('layouts.admin')

@section('title', 'Health Forms Logbook')

@push('styles')
<style>
    .hf-logbook-shell {
        max-width: 1380px;
        margin: 0 auto;
        padding: 22px;
    }
    .hf-logbook-header {
        display: flex;
        align-items: flex-start;
        justify-content: space-between;
        gap: 18px;
        margin-bottom: 20px;
    }
    .hf-logbook-title {
        margin: 0;
        color: #111827;
        font-size: 30px;
        font-weight: 900;
        letter-spacing: -0.03em;
    }
    .hf-logbook-subtitle {
        margin: 8px 0 0;
        color: #475569;
        font-size: 14px;
        line-height: 1.6;
    }
    .hf-logbook-btn {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        gap: 8px;
        min-height: 42px;
        padding: 10px 16px;
        border-radius: 10px;
        border: 1px solid rgba(112, 19, 27, 0.28);
        background: #ffffff;
        color: #70131b;
        font-size: 13px;
        font-weight: 900;
        text-decoration: none;
        cursor: pointer;
        box-shadow: 0 10px 22px rgba(15, 23, 42, 0.08);
    }
    .hf-logbook-btn.primary {
        background: #70131b;
        color: #ffffff;
        border-color: #70131b;
    }
    .hf-logbook-btn:hover,
    .hf-logbook-btn:focus-visible {
        background: #facc15;
        border-color: #facc15;
        color: #70131b;
        outline: none;
    }
    .hf-form-b-panel {
        background: #ffffff;
        border: 1px solid rgba(112, 19, 27, 0.12);
        border-radius: 18px;
        box-shadow: 0 18px 45px rgba(15, 23, 42, 0.1);
        overflow: hidden;
    }
    .hf-form-b-heading {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 18px;
        padding: 22px 24px;
        border-top: 5px solid #70131b;
        border-bottom: 1px solid #f1d7d7;
        background: linear-gradient(135deg, #ffffff, #fffaf0);
    }
    .hf-form-b-kicker {
        margin: 0 0 4px;
        color: #7f1d2d;
        font-size: 11px;
        font-weight: 900;
        letter-spacing: .08em;
        text-transform: uppercase;
    }
    .hf-form-b-title {
        margin: 0;
        color: #111827;
        font-size: 22px;
        font-weight: 900;
    }
    .hf-form-b-month {
        margin: 6px 0 0;
        color: #64748b;
        font-size: 13px;
        font-weight: 800;
    }
    .hf-logbook-search {
        min-width: min(360px, 100%);
    }
    .hf-logbook-search label {
        display: block;
        margin-bottom: 6px;
        color: #64748b;
        font-size: 11px;
        font-weight: 900;
        letter-spacing: .04em;
        text-transform: uppercase;
    }
    .hf-logbook-search-wrap {
        flex: 1;
        min-width: 0;
        display: flex;
        align-items: center;
        gap: 9px;
        min-height: 42px;
        padding: 0 12px;
        border: 1px solid #dbe3ef;
        border-radius: 12px;
        background: #ffffff;
    }
    .hf-logbook-search-wrap svg {
        width: 17px;
        height: 17px;
        color: #70131b;
    }
    .hf-logbook-search-wrap input {
        min-width: 0;
        width: 100%;
        border: 0;
        outline: 0;
        background: transparent;
        color: #111827;
        font-size: 13px;
        font-weight: 700;
    }
    .hf-logbook-search-actions {
        position: relative;
        display: flex;
        align-items: stretch;
        gap: 10px;
    }
    .hf-logbook-search-actions > .hf-logbook-btn {
        flex: 0 0 auto;
    }
    .hf-form-b-table-wrap {
        overflow-x: auto;
    }
    .hf-form-b-table {
        width: 100%;
        border-collapse: collapse;
        min-width: 1480px;
    }
    .hf-form-b-table th,
    .hf-form-b-table td {
        padding: 13px 12px;
        border-bottom: 1px solid #e5e7eb;
        text-align: left;
        vertical-align: top;
        color: #111827;
        font-size: 13px;
    }
    .hf-form-b-table th {
        background: #fff7f7;
        color: #7f1d2d;
        font-size: 11px;
        font-weight: 900;
        letter-spacing: .05em;
        text-transform: uppercase;
    }
    .hf-patient-name {
        display: block;
        font-weight: 900;
    }
    .hf-patient-number,
    .hf-cell-secondary {
        display: block;
        margin-top: 3px;
        color: #64748b;
        font-size: 11px;
        font-weight: 700;
    }
    .hf-entry {
        display: block;
        margin-bottom: 6px;
    }
    .hf-entry-label {
        display: block;
        color: #7f1d2d;
        font-size: 10px;
        font-weight: 900;
        text-transform: uppercase;
    }
    .hf-entry-value {
        display: block;
        margin-top: 2px;
        line-height: 1.45;
    }
    .hf-empty {
        padding: 42px 20px !important;
        text-align: center !important;
        color: #64748b !important;
        font-weight: 800;
    }
    .hf-form-b-footer {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 10px;
        padding: 14px 18px;
        background: #f8fafc;
        color: #64748b;
        font-size: 12px;
        font-weight: 800;
    }
    .hf-filter-popover {
        position: absolute;
        top: calc(100% + 10px);
        right: 0;
        display: none;
        width: min(310px, calc(100vw - 40px));
        border: 1px solid #ead1d1;
        border-radius: 14px;
        background: #ffffff;
        box-shadow: 0 18px 38px rgba(15, 23, 42, 0.2);
        overflow: hidden;
        z-index: 1000;
    }
    .hf-filter-popover.is-open {
        display: block;
    }
    .hf-filter-head {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 12px;
        padding: 14px;
        background: #ffffff;
        color: #70131b;
        border-bottom: 1px solid #f0dddd;
    }
    .hf-filter-head h2 {
        margin: 0;
        color: #70131b;
        font-size: 12px;
        font-weight: 900;
        letter-spacing: .05em;
        text-transform: uppercase;
    }
    .hf-filter-close {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        width: 28px;
        height: 28px;
        padding: 0;
        border: 1px solid #e5c6c6;
        border-radius: 8px;
        background: #ffffff;
        color: #70131b;
        cursor: pointer;
    }
    .hf-filter-close:hover,
    .hf-filter-close:focus-visible {
        background: #facc15;
        border-color: #facc15;
        color: #70131b;
        outline: none;
    }
    .hf-filter-close svg {
        width: 16px;
        height: 16px;
    }
    .hf-filter-form {
        display: grid;
        gap: 12px;
        padding: 14px;
        background: #ffffff;
    }
    .hf-filter-field label {
        display: block;
        margin-bottom: 6px;
        color: #70131b;
        font-size: 11px;
        font-weight: 900;
        letter-spacing: .04em;
        text-transform: uppercase;
    }
    .hf-filter-field input {
        width: 100%;
        height: 42px;
        border: 1px solid #e5c6c6;
        border-radius: 9px;
        padding: 0 11px;
        background: #ffffff;
        color: #111827;
        font-size: 13px;
        font-weight: 800;
        outline: none;
    }
    .hf-filter-field input:focus {
        border-color: #70131b;
        box-shadow: 0 0 0 3px rgba(112, 19, 27, 0.10);
    }
    .hf-filter-actions {
        display: flex;
        justify-content: flex-end;
        gap: 8px;
        padding-top: 2px;
    }
    .hf-filter-actions .hf-logbook-btn {
        min-height: 38px;
        padding: 8px 13px;
        font-size: 12px;
    }
    html[data-theme="dark"] .hf-filter-popover {
        border-color: rgba(250, 204, 21, 0.25);
        background: #111827;
        box-shadow: 0 20px 42px rgba(0, 0, 0, 0.42);
    }
    html[data-theme="dark"] .hf-filter-head,
    html[data-theme="dark"] .hf-filter-form {
        background: #111827;
        border-bottom-color: rgba(255, 255, 255, 0.10);
    }
    html[data-theme="dark"] .hf-filter-head h2,
    html[data-theme="dark"] .hf-filter-field label {
        color: #facc15;
    }
    html[data-theme="dark"] .hf-filter-close {
        border-color: rgba(250, 204, 21, 0.35);
        background: #1e293b;
        color: #facc15;
    }
    html[data-theme="dark"] .hf-filter-field input {
        border-color: rgba(250, 204, 21, 0.28);
        background: #1e293b;
        color: #ffffff;
        color-scheme: dark;
    }
    html[data-theme="dark"] .hf-filter-field input:focus {
        border-color: #facc15;
        box-shadow: 0 0 0 3px rgba(250, 204, 21, 0.12);
    }
    html[data-theme="dark"] .hf-filter-actions .hf-logbook-btn:not(.primary) {
        background: #1e293b;
        border-color: rgba(255, 255, 255, 0.18);
        color: #ffffff;
    }
    html[data-theme="dark"] .hf-logbook-title,
    html[data-theme="dark"] .hf-form-b-title,
    html[data-theme="dark"] .hf-form-b-table td {
        color: #ffffff;
    }
    html[data-theme="dark"] .hf-logbook-subtitle,
    html[data-theme="dark"] .hf-form-b-month,
    html[data-theme="dark"] .hf-logbook-search label,
    html[data-theme="dark"] .hf-patient-number,
    html[data-theme="dark"] .hf-empty {
        color: #cbd5e1 !important;
    }
    html[data-theme="dark"] .hf-form-b-panel {
        border-color: rgba(250, 204, 21, 0.22);
        background: #111827;
        box-shadow: 0 18px 45px rgba(0, 0, 0, 0.28);
    }
    html[data-theme="dark"] .hf-form-b-heading {
        border-bottom-color: rgba(255, 255, 255, 0.10);
        background: #111827;
    }
    html[data-theme="dark"] .hf-form-b-kicker,
    html[data-theme="dark"] .hf-form-b-table th,
    html[data-theme="dark"] .hf-entry-label {
        color: #facc15;
    }
    html[data-theme="dark"] .hf-logbook-search-wrap {
        border-color: rgba(250, 204, 21, 0.28);
        background: #1e293b;
    }
    html[data-theme="dark"] .hf-logbook-search-wrap svg {
        color: #facc15;
    }
    html[data-theme="dark"] .hf-logbook-search-wrap input {
        color: #ffffff;
        color-scheme: dark;
    }
    html[data-theme="dark"] .hf-form-b-table th {
        background: #1e293b;
    }
    html[data-theme="dark"] .hf-form-b-table td {
        border-bottom-color: rgba(255, 255, 255, 0.10);
    }
    html[data-theme="dark"] .hf-form-b-footer {
        background: #0f172a;
        color: #cbd5e1;
    }
    @media (max-width: 780px) {
        .hf-logbook-header,
        .hf-form-b-heading {
            flex-direction: column;
            align-items: stretch;
        }
        .hf-logbook-search-actions {
            flex-direction: column;
        }
        .hf-logbook-search-actions .hf-logbook-btn {
            width: 100%;
        }
        .hf-filter-popover {
            right: 0;
            left: 0;
            width: 100%;
        }
    }
</style>
@endpush

@section('content')
@php
    $role = \App\Models\User::normalizeRole(optional(auth()->user())->user_role ?? '');
    $reportsRootUrl = $role === \App\Models\User::ROLE_ADMIN ? url('/assistant/reports') : url('/admin/reports');
    $clinicRecordsUrl = $role === \App\Models\User::ROLE_ADMIN ? url('/assistant/reports/digital-logbook') : url('/admin/reports/digital-logbook');
    $rangeStartLabel = $dateFrom->format('d M Y');
    $rangeEndLabel = $dateTo->format('d M Y');
    $selectedRangeLabel = $dateFrom->isSameDay($dateTo)
        ? $rangeStartLabel
        : $rangeStartLabel . ' to ' . $rangeEndLabel;
@endphp
<div class="hf-logbook-shell">
    @include('admin.partials.report-breadcrumb', ['items' => [
        ['label' => 'Reports', 'url' => $reportsRootUrl],
        ['label' => 'Clinic Records', 'url' => $clinicRecordsUrl],
        ['label' => 'Logbook'],
    ]])
    <header class="hf-logbook-header">
        <div>
            <h1 class="hf-logbook-title">Health Forms Logbook</h1>
            <p class="hf-logbook-subtitle">Approved health form clinic visit logbook using Final Review time-in and approval time-out.</p>
        </div>
    </header>

    <section class="hf-form-b-panel">
        <div class="hf-form-b-heading">
            <div>
                <p class="hf-form-b-kicker">PUP Taguig Medical Clinic · Health Form</p>
                <h2 class="hf-form-b-title">Health Form Record Logs</h2>
                <p class="hf-form-b-month">{{ $selectedRangeLabel }}</p>
            </div>
            <div class="hf-logbook-search">
                <label for="healthFormsLogbookSearch">Search Patient</label>
                <div class="hf-logbook-search-actions">
                    <div class="hf-logbook-search-wrap">
                        <x-outline-icon name="magnifying-glass" />
                        <input id="healthFormsLogbookSearch" type="search" placeholder="Name or reference number" autocomplete="off">
                    </div>
                    <button type="button" class="hf-logbook-btn primary" id="openHealthFormsLogbookFilter" aria-controls="healthFormsLogbookFilterPopover" aria-expanded="false">
                        <x-outline-icon name="calendar-days" />
                        Filter
                    </button>
                    <div class="hf-filter-popover" id="healthFormsLogbookFilterPopover" role="dialog" aria-modal="false" aria-labelledby="healthFormsLogbookFilterTitle" aria-hidden="true">
                        <div class="hf-filter-head">
                            <h2 id="healthFormsLogbookFilterTitle">Date Filter</h2>
                            <button type="button" class="hf-filter-close" id="closeHealthFormsLogbookFilter" aria-label="Close date filter">
                                <x-outline-icon name="chevron-up" />
                            </button>
                        </div>
                        <form method="GET" class="hf-filter-form">
                            <div class="hf-filter-field">
                                <label for="healthFormsLogbookDateFrom">From</label>
                                <input id="healthFormsLogbookDateFrom" type="date" name="date_from" value="{{ $dateFrom->format('Y-m-d') }}" required>
                            </div>
                            <div class="hf-filter-field">
                                <label for="healthFormsLogbookDateTo">To</label>
                                <input id="healthFormsLogbookDateTo" type="date" name="date_to" value="{{ $dateTo->format('Y-m-d') }}" required>
                            </div>
                            <div class="hf-filter-actions">
                                <button type="button" class="hf-logbook-btn" id="cancelHealthFormsLogbookFilter">Cancel</button>
                                <button type="submit" class="hf-logbook-btn primary">
                                    <x-outline-icon name="check" />
                                    Apply Filter
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>

        <div class="hf-form-b-table-wrap">
            <table class="hf-form-b-table">
                <thead>
                    <tr>
                        <th>Date</th>
                        <th>Time In</th>
                        <th>Time Out</th>
                        <th>Full Name</th>
                        <th>Course-Yr &amp; Sec / Dept</th>
                        <th>Transaction</th>
                        <th>Pending Reason</th>
                        <th>Referral</th>
                        <th>Approval Date</th>
                        <th>Approved by</th>
                    </tr>
                </thead>
                <tbody id="healthFormsLogbookBody">
                    @forelse($records as $record)
                        <tr
                            class="hf-logbook-row"
                            data-patient-name="{{ \Illuminate\Support\Str::lower($record['patient_name']) }}"
                            data-reference="{{ \Illuminate\Support\Str::lower($record['reference']) }}"
                        >
                            <td>{{ optional($record['date'])->format('m/d/Y') ?: '-' }}</td>
                            <td>{{ optional($record['time_in'])->format('g:i A') ?: '-' }}</td>
                            <td>{{ optional($record['time_out'])->format('g:i A') ?: '-' }}</td>
                            <td>
                                <span class="hf-patient-name">{{ $record['patient_name'] }}</span>
                                <span class="hf-patient-number">{{ $record['reference'] ?: 'No reference number' }}</span>
                            </td>
                            <td>{{ $record['course_department'] ?: '-' }}</td>
                            <td>{{ $record['transaction'] ?: '-' }}</td>
                            <td>{{ $record['pending_reason'] ?: '-' }}</td>
                            <td>{{ $record['referral'] ?: '-' }}</td>
                            <td>{{ optional($record['approval_date'])->format('m/d/Y') ?: '-' }}</td>
                            <td>{{ $record['approved_by'] ?: '-' }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="10" class="hf-empty">No approved health form records were logged from {{ $selectedRangeLabel }}.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="hf-form-b-footer">
            <span id="healthFormsLogbookVisibleCount">{{ $records->count() }} record{{ $records->count() === 1 ? '' : 's' }}</span>
            <span>Generated from approved health forms</span>
        </div>
    </section>
</div>

<script>
const healthFormsLogbookSearch = document.getElementById('healthFormsLogbookSearch');
const healthFormsLogbookRows = Array.from(document.querySelectorAll('.hf-logbook-row'));
const healthFormsLogbookVisibleCount = document.getElementById('healthFormsLogbookVisibleCount');
const healthFormsLogbookFilterButton = document.getElementById('openHealthFormsLogbookFilter');
const healthFormsLogbookFilterPopover = document.getElementById('healthFormsLogbookFilterPopover');

healthFormsLogbookSearch?.addEventListener('input', function () {
    const value = this.value.trim().toLowerCase();
    let visible = 0;

    healthFormsLogbookRows.forEach(function (row) {
        const matched = !value
            || row.dataset.patientName.includes(value)
            || row.dataset.reference.includes(value);
        row.style.display = matched ? '' : 'none';
        if (matched) visible++;
    });

    if (healthFormsLogbookVisibleCount) {
        healthFormsLogbookVisibleCount.textContent = `${visible} record${visible === 1 ? '' : 's'}`;
    }
});

healthFormsLogbookFilterButton?.addEventListener('click', function () {
    const isOpen = healthFormsLogbookFilterPopover?.classList.toggle('is-open') ?? false;
    healthFormsLogbookFilterPopover?.setAttribute('aria-hidden', isOpen ? 'false' : 'true');
    this.setAttribute('aria-expanded', isOpen ? 'true' : 'false');
});

function closeHealthFormsLogbookFilter() {
    healthFormsLogbookFilterPopover?.classList.remove('is-open');
    healthFormsLogbookFilterPopover?.setAttribute('aria-hidden', 'true');
    healthFormsLogbookFilterButton?.setAttribute('aria-expanded', 'false');
}

document.getElementById('closeHealthFormsLogbookFilter')?.addEventListener('click', closeHealthFormsLogbookFilter);
document.getElementById('cancelHealthFormsLogbookFilter')?.addEventListener('click', closeHealthFormsLogbookFilter);
document.addEventListener('click', function (event) {
    if (!healthFormsLogbookFilterPopover?.classList.contains('is-open')) return;
    if (!event.target.closest('.hf-logbook-search-actions')) closeHealthFormsLogbookFilter();
});
document.addEventListener('keydown', function (event) {
    if (event.key === 'Escape') closeHealthFormsLogbookFilter();
});
</script>
@endsection
