@extends('layouts.admin')

@section('title', 'Daily Treatment Record')

@push('styles')
<style>
    .treatment-record-shell {
        max-width: 1600px;
        margin: 0 auto;
        padding: 22px;
    }
    .treatment-record-header {
        display: flex;
        align-items: flex-start;
        justify-content: space-between;
        gap: 20px;
        margin-bottom: 22px;
    }
    .treatment-record-title {
        margin: 0;
        color: #111827;
        font-size: 30px;
        font-weight: 900;
    }
    .treatment-record-subtitle {
        max-width: 780px;
        margin: 7px 0 0;
        color: #64748b;
        font-size: 14px;
        line-height: 1.6;
    }
    .treatment-record-back {
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

    .treatment-record-back::after {
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

    .treatment-record-back:hover::after {
        left: 125%;
    }

    .treatment-record-back:hover,
    .treatment-record-back:focus {
        color: #70131B;
        border-color: rgba(112, 19, 27, 0.48);
        background: #ffffff;
        box-shadow: 0 0 0 2px rgba(112, 19, 27, 0.12), 0 12px 28px rgba(15, 23, 42, 0.12);
        outline: none;
    }

    .treatment-record-actions {
        display: flex;
        align-items: center;
        gap: 12px;
    }

    .treatment-record-back svg {
        width: 18px;
        height: 18px;
        transform: rotate(180deg);
        position: relative;
        z-index: 1;
    }
    .treatment-filter {
        display: flex;
        align-items: flex-end;
        gap: 10px;
        margin-bottom: 20px;
        padding: 16px;
        border: 1px solid #e2e8f0;
        border-radius: 8px;
        background: #fff;
    }
    .treatment-field {
        min-width: 230px;
    }
    .treatment-field label,
    .logbook-search label {
        display: block;
        margin-bottom: 6px;
        color: #475569;
        font-size: 11px;
        font-weight: 900;
        text-transform: uppercase;
    }
    .treatment-control {
        width: 100%;
        min-height: 43px;
        padding: 9px 12px;
        border: 1px solid #94a3b8;
        border-radius: 6px;
        background: #fff;
        color: #111827;
        font-size: 14px;
    }
    .treatment-control:focus {
        border-color: #70131b;
        outline: 3px solid rgba(112, 19, 27, .12);
    }
    .treatment-filter-button {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        gap: 7px;
        min-height: 43px;
        padding: 9px 17px;
        border: 1px solid #70131b;
        border-radius: 6px;
        background: #70131b;
        color: #fff;
        font-size: 13px;
        font-weight: 800;
        cursor: pointer;
        transition: background-color .18s ease, color .18s ease;
    }
    .treatment-filter-button:hover {
        border-color: #facc15;
        background: #facc15;
        color: #111827;
    }
    .treatment-filter-button svg {
        width: 17px;
        height: 17px;
    }
    .treatment-filter-modal {
        position: fixed;
        z-index: 2100;
        inset: 0;
        display: none;
        align-items: center;
        justify-content: center;
        padding: 18px;
        background: rgba(15, 23, 42, .58);
        backdrop-filter: blur(4px);
    }
    .treatment-filter-modal.show {
        display: flex;
    }
    .treatment-filter-dialog {
        width: min(620px, 100%);
        overflow: hidden;
        border: 1px solid rgba(112, 19, 27, .16);
        border-radius: 18px;
        background: #fff;
        box-shadow: 0 26px 70px rgba(15, 23, 42, .28);
    }
    .treatment-filter-head {
        display: flex;
        align-items: center;
        gap: 14px;
        padding: 20px 22px;
        border-bottom: 1px solid rgba(255, 255, 255, .16);
        background: linear-gradient(135deg, #70131b 0%, #8f2230 100%);
    }
    .treatment-filter-badge {
        display: grid;
        place-items: center;
        width: 44px;
        height: 44px;
        flex: 0 0 44px;
        border-radius: 12px;
        border: 1px solid rgba(250, 204, 21, .38);
        background: rgba(255, 255, 255, .1);
        color: #facc15;
    }
    .treatment-filter-badge svg {
        width: 23px;
        height: 23px;
    }
    .treatment-filter-head-copy {
        min-width: 0;
        flex: 1;
    }
    .treatment-filter-head .treatment-filter-head-copy h2 {
        margin: 0;
        color: #ffffff !important;
        font-size: 19px;
        font-weight: 900;
    }
    .treatment-filter-head .treatment-filter-head-copy p {
        margin: 4px 0 0;
        color: #ffffff !important;
        font-size: 12px;
        line-height: 1.5;
    }
    .treatment-filter-close {
        position: relative;
        z-index: 0;
        overflow: hidden;
        display: grid;
        place-items: center;
        width: 40px;
        height: 40px;
        flex: 0 0 40px;
        padding: 0;
        border: 1px solid rgba(250, 204, 21, .58);
        border-radius: 999px;
        background: linear-gradient(90deg, #8f2230 0 50%, #70131b 50% 100%);
        background-size: 205% 100%;
        background-position: 100% 0;
        color: #ffffff;
        font-size: 23px;
        cursor: pointer;
        transition: background-position .32s ease, border-color .18s ease, box-shadow .18s ease;
    }
    .treatment-filter-close:hover,
    .treatment-filter-close:focus {
        border-color: #facc15;
        background-position: 0 0;
        color: #ffffff;
        box-shadow: 0 8px 18px rgba(15, 23, 42, .18);
        outline: none;
    }
    .treatment-filter-form {
        padding: 22px;
    }
    .treatment-filter-grid {
        display: grid;
        grid-template-columns: repeat(2, minmax(0, 1fr));
        gap: 14px;
    }
    .treatment-filter-card {
        padding: 13px 14px;
        border: 1px solid rgba(112, 19, 27, .15);
        border-radius: 13px;
        background: linear-gradient(180deg, #fff 0%, #fffaf7 100%);
    }
    .treatment-filter-card label {
        display: block;
        margin-bottom: 7px;
        color: #6b7280;
        font-size: 11px;
        font-weight: 900;
        text-transform: uppercase;
    }
    .treatment-month-input {
        width: 100%;
        min-height: 48px;
        padding: 10px 12px;
        border: 1px solid rgba(112, 19, 27, .28);
        border-radius: 11px;
        background: #fff;
        color: #111827;
        font: inherit;
        font-size: 14px;
        font-weight: 750;
    }
    .treatment-month-input:focus {
        border-color: #70131b;
        outline: 3px solid rgba(112, 19, 27, .09);
    }
    .treatment-filter-actions {
        display: flex;
        justify-content: flex-end;
        gap: 10px;
        margin-top: 18px;
    }
    .treatment-filter-cancel {
        min-height: 43px;
        padding: 9px 16px;
        border: 1px solid #cbd5e1;
        border-radius: 8px;
        background: #fff;
        color: #475569;
        font: inherit;
        font-size: 13px;
        font-weight: 800;
        cursor: pointer;
    }
    .treatment-metrics {
        display: grid;
        grid-template-columns: repeat(3, minmax(0, 1fr));
        gap: 14px;
        margin-bottom: 22px;
    }
    .treatment-metric {
        display: grid;
        grid-template-columns: 46px minmax(0, 1fr);
        gap: 13px;
        align-items: center;
        min-height: 112px;
        padding: 18px;
        border: 1px solid #e2e8f0;
        border-radius: 8px;
        background: #fff;
        box-shadow: 0 6px 18px rgba(15, 23, 42, .06);
    }
    .treatment-metric-icon {
        display: grid;
        place-items: center;
        width: 46px;
        height: 46px;
        border-radius: 7px;
        background: #70131b;
        color: #fff;
    }
    .treatment-metric-icon.yellow {
        background: #facc15;
        color: #111827;
    }
    .treatment-metric-icon.green {
        background: #166534;
    }
    .treatment-metric-icon svg {
        width: 23px;
        height: 23px;
    }
    .treatment-metric-label {
        display: block;
        margin-bottom: 5px;
        color: #64748b;
        font-size: 11px;
        font-weight: 900;
        text-transform: uppercase;
    }
    .treatment-metric-value {
        display: block;
        overflow-wrap: anywhere;
        color: #111827;
        font-size: 25px;
        font-weight: 900;
        line-height: 1.15;
    }
    .treatment-metric-value.text-value {
        font-size: 18px;
    }
    .form-b-panel {
        overflow: hidden;
        border: 1px solid #cbd5e1;
        border-radius: 8px;
        background: #fff;
        box-shadow: 0 8px 22px rgba(15, 23, 42, .07);
    }
    .form-b-heading {
        padding: 18px;
        border-bottom: 1px solid #cbd5e1;
        background: #f8fafc;
    }
    .form-b-title-row {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 20px;
    }
    .form-b-title-copy {
        min-width: 0;
    }
    .form-b-kicker {
        margin: 0 0 3px;
        color: #70131b;
        font-size: 11px;
        font-weight: 900;
        text-transform: uppercase;
    }
    .form-b-title {
        margin: 0;
        color: #111827;
        font-size: 20px;
        font-weight: 900;
    }
    .form-b-month {
        margin: 4px 0 0;
        color: #64748b;
        font-size: 12px;
        font-weight: 700;
    }
    .logbook-search {
        flex: 0 1 330px;
        width: min(100%, 330px);
    }
    .logbook-search-wrap {
        position: relative;
    }
    .logbook-search-wrap svg {
        position: absolute;
        top: 50%;
        left: 11px;
        width: 17px;
        height: 17px;
        color: #64748b;
        transform: translateY(-50%);
        pointer-events: none;
    }
    .logbook-search .treatment-control {
        padding-left: 36px;
    }
    .form-b-table-wrap {
        overflow-x: auto;
    }
    .form-b-table {
        width: 100%;
        min-width: 1450px;
        border-collapse: collapse;
        table-layout: fixed;
    }
    .form-b-table th,
    .form-b-table td {
        border-right: 1px solid #cbd5e1;
        border-bottom: 1px solid #cbd5e1;
        vertical-align: top;
    }
    .form-b-table th:last-child,
    .form-b-table td:last-child {
        border-right: 0;
    }
    .form-b-table th {
        padding: 10px 8px;
        background: #70131b;
        color: #fff;
        font-size: 11px;
        font-weight: 900;
        line-height: 1.35;
        text-align: center;
        text-transform: uppercase;
    }
    .form-b-table td {
        padding: 10px 9px;
        color: #1f2937;
        font-size: 12px;
        line-height: 1.45;
    }
    .form-b-table tbody tr:nth-child(even) {
        background: #f8fafc;
    }
    .form-b-table tbody tr:hover {
        background: #fffbea;
    }
    .form-b-table .col-date { width: 92px; }
    .form-b-table .col-time { width: 76px; }
    .form-b-table .col-patient { width: 175px; }
    .form-b-table .col-course { width: 165px; }
    .form-b-table .col-complaint { width: 260px; }
    .form-b-table .col-treatment { width: 190px; }
    .form-b-table .col-qty { width: 65px; }
    .form-b-table .col-staff { width: 175px; }
    .patient-name {
        display: block;
        color: #111827;
        font-weight: 800;
    }
    .patient-number,
    .cell-secondary {
        display: block;
        margin-top: 3px;
        color: #64748b;
        font-size: 10px;
    }
    .complaint-impression-entry {
        display: block;
        margin-bottom: 6px;
    }
    .complaint-impression-entry:last-child {
        margin-bottom: 0;
    }
    .complaint-impression-label {
        display: block;
        margin-bottom: 2px;
        color: #70131b;
        font-size: 9px;
        font-weight: 900;
        text-transform: uppercase;
    }
    .complaint-impression-value {
        display: block;
    }
    .quantity-cell,
    .time-cell,
    .date-cell {
        text-align: center;
        white-space: nowrap;
    }
    .form-b-empty {
        padding: 38px 20px !important;
        color: #64748b !important;
        text-align: center;
    }
    .form-b-no-results {
        display: none;
        padding: 24px;
        color: #64748b;
        font-size: 13px;
        text-align: center;
    }
    .form-b-footer {
        display: flex;
        justify-content: space-between;
        gap: 14px;
        padding: 12px 18px;
        color: #64748b;
        font-size: 11px;
    }
    html[data-theme="dark"] .treatment-filter,
    html[data-theme="dark"] .treatment-metric,
    html[data-theme="dark"] .form-b-panel {
        border-color: #374151;
        background: #111827;
    }
    html[data-theme="dark"] .treatment-filter-dialog,
    html[data-theme="dark"] .treatment-filter-head,
    html[data-theme="dark"] .treatment-filter-card,
    html[data-theme="dark"] .treatment-month-input,
    html[data-theme="dark"] .treatment-filter-close,
    html[data-theme="dark"] .treatment-filter-cancel {
        border-color: #374151;
        background: #111827;
        color: #f8fafc;
    }
    html[data-theme="dark"] .treatment-filter-head .treatment-filter-head-copy h2 {
        color: #ffffff !important;
    }
    html[data-theme="dark"] .treatment-filter-head {
        border-color: rgba(250, 204, 21, .2);
        background: linear-gradient(135deg, #70131b 0%, #8f2230 100%);
    }
    html[data-theme="dark"] .treatment-filter-head .treatment-filter-head-copy p {
        color: #ffffff !important;
    }
    html[data-theme="dark"] .treatment-filter-close {
        border-color: rgba(250, 204, 21, .58);
        background: linear-gradient(90deg, #8f2230 0 50%, #70131b 50% 100%);
        background-size: 205% 100%;
        background-position: 100% 0;
        color: #ffffff;
    }
    html[data-theme="dark"] .treatment-filter-close:hover,
    html[data-theme="dark"] .treatment-filter-close:focus {
        border-color: #facc15;
        background-position: 0 0;
        color: #ffffff;
    }
    html[data-theme="dark"] .treatment-record-back {
        border-color: #70131b;
        background: #e5e7eb;
        color: #70131b;
    }
    html[data-theme="dark"] .form-b-heading,
    html[data-theme="dark"] .form-b-table tbody tr:nth-child(even) {
        background: #1f2937;
    }
    html[data-theme="dark"] .treatment-record-title,
    html[data-theme="dark"] .treatment-metric-value,
    html[data-theme="dark"] .form-b-title,
    html[data-theme="dark"] .patient-name {
        color: #f8fafc;
    }
    html[data-theme="dark"] .treatment-control {
        border-color: #4b5563;
        background: #0f172a;
        color: #f8fafc;
    }
    html[data-theme="dark"] .form-b-table td {
        border-color: #374151;
        color: #e5e7eb;
    }
    html[data-theme="dark"] .complaint-impression-label {
        color: #facc15;
    }
    html[data-theme="dark"] .form-b-table tbody tr:hover {
        background: #332d19;
    }
    @media (max-width: 900px) {
        .treatment-metrics {
            grid-template-columns: 1fr;
        }
        .form-b-title-row {
            align-items: stretch;
            flex-direction: column;
        }
        .logbook-search {
            width: 100%;
            flex-basis: auto;
        }
    }
    @media (max-width: 620px) {
        .treatment-record-shell {
            padding: 14px;
        }
        .treatment-record-header,
        .treatment-filter {
            align-items: stretch;
            flex-direction: column;
        }
        .treatment-record-actions {
            align-items: stretch;
            flex-direction: column;
        }
        .treatment-filter-grid {
            grid-template-columns: 1fr;
        }
        .treatment-field,
        .treatment-filter-button {
            width: 100%;
        }
        .treatment-record-title {
            font-size: 25px;
        }
        .form-b-footer {
            flex-direction: column;
        }
    }
</style>
@endpush

@section('content')
@php
    $role = \App\Models\User::normalizeRole(optional(auth()->user())->user_role ?? '');
    $reportsHomeUrl = $role === \App\Models\User::ROLE_ADMIN ? url('/assistant/reports') : url('/admin/reports');
    $rangeStartLabel = $dateFrom->format('d M Y');
    $rangeEndLabel = $dateTo->format('d M Y');
    $selectedMonthLabel = $dateFrom->isSameDay($dateTo)
        ? $rangeStartLabel
        : $rangeStartLabel . ' to ' . $rangeEndLabel;
@endphp

<div class="treatment-record-shell">
    <header class="treatment-record-header">
        <div>
            <h1 class="treatment-record-title">Daily Treatment Record</h1>
            <p class="treatment-record-subtitle">Official digital Form B logbook for clinic consultations, treatment provided, medicines dispensed, and attending personnel.</p>
        </div>
        <div class="treatment-record-actions">
            <button type="button" class="treatment-filter-button" id="openTreatmentFilter">
                <x-outline-icon name="calendar-days" />
                Filter
            </button>
            <a href="{{ $reportsHomeUrl }}" class="treatment-record-back">
                <x-outline-icon name="arrow-long-right" />
                Back to Reports
            </a>
        </div>
    </header>

    <section class="form-b-panel">
        <div class="form-b-heading">
            <div>
                <p class="form-b-kicker">PUP Taguig Medical Clinic · Form B</p>
                <div class="form-b-title-row">
                    <div class="form-b-title-copy">
                        <h2 class="form-b-title">Digital Treatment Logbook</h2>
                        <p class="form-b-month">{{ $selectedMonthLabel }}</p>
                    </div>
                    <div class="logbook-search">
                        <label for="treatmentRecordSearch">Search Patient</label>
                        <div class="logbook-search-wrap">
                            <x-outline-icon name="magnifying-glass" />
                            <input id="treatmentRecordSearch" class="treatment-control" type="search" placeholder="Name or student number" autocomplete="off">
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="form-b-table-wrap">
            <table class="form-b-table">
                <thead>
                    <tr>
                        <th class="col-date">Date</th>
                        <th class="col-time">Time In</th>
                        <th class="col-time">Time Out</th>
                        <th class="col-patient">Patient Name</th>
                        <th class="col-course">Course-Yr &amp; Sec / Dept</th>
                        <th class="col-complaint">Complaints / Impression</th>
                        <th class="col-treatment">Treatment / Medicines</th>
                        <th class="col-qty">Qty</th>
                        <th class="col-staff">Physician / Attending Staff</th>
                    </tr>
                </thead>
                <tbody id="treatmentRecordBody">
                    @forelse($consultations as $consultation)
                        @php
                            $patient = $consultation->user;
                            $patientName = trim((string) ($patient?->name ?: $consultation->name)) ?: 'Unnamed Patient';
                            $studentNumber = trim((string) ($patient?->student_number ?: $patient?->student_id));
                            $course = trim((string) ($patient?->course ?: optional($patient?->healthProfile)->course_college));
                            $yearSection = trim(implode(' - ', array_filter([
                                trim((string) $patient?->year),
                                trim((string) $patient?->section),
                            ])));
                            $courseDepartment = trim(implode(' / ', array_filter([$course, $yearSection])));
                            $complaint = trim((string) $consultation->reason_for_visit);
                            $impression = trim((string) $consultation->comments);
                            $medicineName = trim((string) (optional($consultation->medicineItem)->name ?: $consultation->medicine));
                            $medicineQuantity = (float) $consultation->medicine_quantity;
                            $staffName = trim((string) ($consultation->attending_staff_name ?: optional($consultation->attendingStaff)->name));
                            $timeIn = $consultation->time_in ?: optional($consultation->created_at)->format('H:i:s');
                            $timeOut = $consultation->time_out ?: optional($consultation->updated_at)->format('H:i:s');
                        @endphp
                        <tr
                            class="treatment-record-row"
                            data-patient-name="{{ \Illuminate\Support\Str::lower($patientName) }}"
                            data-student-number="{{ \Illuminate\Support\Str::lower($studentNumber) }}"
                        >
                            <td class="date-cell">{{ optional($consultation->consultation_date)->format('m/d/Y') ?: '-' }}</td>
                            <td class="time-cell">{{ $timeIn ? \Carbon\Carbon::parse($timeIn)->format('g:i A') : '-' }}</td>
                            <td class="time-cell">{{ $timeOut ? \Carbon\Carbon::parse($timeOut)->format('g:i A') : '-' }}</td>
                            <td>
                                <span class="patient-name">{{ $patientName }}</span>
                                <span class="patient-number">{{ $studentNumber ?: 'No student number' }}</span>
                            </td>
                            <td>{{ $courseDepartment ?: ($consultation->user_role ?: '-') }}</td>
                            <td>
                                <span class="complaint-impression-entry">
                                    <span class="complaint-impression-label">Complaint</span>
                                    <span class="complaint-impression-value">{{ $complaint ?: 'No complaint recorded' }}</span>
                                </span>
                                <span class="complaint-impression-entry">
                                    <span class="complaint-impression-label">Impression</span>
                                    <span class="complaint-impression-value">{{ $impression ?: 'No assessment recorded' }}</span>
                                </span>
                            </td>
                            <td>
                                {{ $medicineName !== '' && strtolower($medicineName) !== 'none' ? $medicineName : 'No medicine issued' }}
                                @if($consultation->service)
                                    <span class="cell-secondary">{{ $consultation->service }}</span>
                                @endif
                            </td>
                            <td class="quantity-cell">
                                {{ $medicineQuantity > 0 ? rtrim(rtrim(number_format($medicineQuantity, 2, '.', ''), '0'), '.') : '-' }}
                            </td>
                            <td>{{ $staffName ?: 'Clinic Staff' }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="9" class="form-b-empty">No treatment records were logged from {{ $selectedMonthLabel }}.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="form-b-no-results" id="treatmentNoResults">No patient matched your search.</div>
        <footer class="form-b-footer">
            <span id="treatmentVisibleCount">{{ $consultations->count() }} record{{ $consultations->count() === 1 ? '' : 's' }}</span>
            <span>Generated from finalized clinic consultations</span>
        </footer>
    </section>
</div>

<div class="treatment-filter-modal" id="treatmentFilterModal" aria-hidden="true">
    <div class="treatment-filter-dialog" role="dialog" aria-modal="true" aria-labelledby="treatmentFilterTitle">
        <header class="treatment-filter-head">
            <span class="treatment-filter-badge">
                <x-outline-icon name="calendar-days" />
            </span>
            <div class="treatment-filter-head-copy">
                <h2 id="treatmentFilterTitle">Treatment Record Date Range</h2>
                <p>Select the starting and ending dates to display in the Form B logbook.</p>
            </div>
            <button type="button" class="treatment-filter-close" id="closeTreatmentFilter" aria-label="Close date filter">&times;</button>
        </header>
        <form method="GET" class="treatment-filter-form">
            <div class="treatment-filter-grid">
                <div class="treatment-filter-card">
                    <label for="treatmentDateFrom">From</label>
                    <input id="treatmentDateFrom" class="treatment-month-input" type="text" name="date_from" value="{{ $dateFrom->format('d/m/Y') }}" placeholder="DD/MM/YYYY" inputmode="numeric" pattern="\d{2}/\d{2}/\d{4}" required>
                </div>
                <div class="treatment-filter-card">
                    <label for="treatmentDateTo">To</label>
                    <input id="treatmentDateTo" class="treatment-month-input" type="text" name="date_to" value="{{ $dateTo->format('d/m/Y') }}" placeholder="DD/MM/YYYY" inputmode="numeric" pattern="\d{2}/\d{2}/\d{4}" required>
                </div>
            </div>
            <div class="treatment-filter-actions">
                <button type="button" class="treatment-filter-cancel" id="cancelTreatmentFilter">Cancel</button>
                <button type="submit" class="treatment-filter-button">
                    <x-outline-icon name="check" />
                    Apply Filter
                </button>
            </div>
        </form>
    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function () {
        const searchInput = document.getElementById('treatmentRecordSearch');
        const rows = Array.from(document.querySelectorAll('.treatment-record-row'));
        const noResults = document.getElementById('treatmentNoResults');
        const visibleCount = document.getElementById('treatmentVisibleCount');
        const filterModal = document.getElementById('treatmentFilterModal');
        const openFilter = document.getElementById('openTreatmentFilter');
        const closeFilter = document.getElementById('closeTreatmentFilter');
        const cancelFilter = document.getElementById('cancelTreatmentFilter');
        const dateFrom = document.getElementById('treatmentDateFrom');
        const dateTo = document.getElementById('treatmentDateTo');

        const updateSearch = function () {
            const query = searchInput.value.trim().toLowerCase();
            let shown = 0;

            rows.forEach(function (row) {
                const patientName = row.dataset.patientName || '';
                const studentNumber = row.dataset.studentNumber || '';
                const matches = query === '' || patientName.includes(query) || studentNumber.includes(query);
                row.hidden = !matches;
                if (matches) {
                    shown += 1;
                }
            });

            noResults.style.display = rows.length > 0 && shown === 0 ? 'block' : 'none';
            visibleCount.textContent = shown + ' record' + (shown === 1 ? '' : 's');
        };

        if (searchInput && noResults && visibleCount) {
            searchInput.addEventListener('input', updateSearch);
        }

        const setFilterModal = function (isOpen) {
            if (!filterModal) return;
            filterModal.classList.toggle('show', isOpen);
            filterModal.setAttribute('aria-hidden', isOpen ? 'false' : 'true');
            document.body.style.overflow = isOpen ? 'hidden' : '';
            if (isOpen) {
                window.setTimeout(function () {
                    dateFrom?.focus();
                }, 0);
            }
        };

        openFilter?.addEventListener('click', function () {
            setFilterModal(true);
        });
        closeFilter?.addEventListener('click', function () {
            setFilterModal(false);
        });
        cancelFilter?.addEventListener('click', function () {
            setFilterModal(false);
        });
        filterModal?.addEventListener('click', function (event) {
            if (event.target === filterModal) {
                setFilterModal(false);
            }
        });
        document.addEventListener('keydown', function (event) {
            if (event.key === 'Escape' && filterModal?.classList.contains('show')) {
                setFilterModal(false);
            }
        });
    });
</script>
@endsection
