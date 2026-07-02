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
    .hf-logbook-actions {
        display: flex;
        gap: 10px;
        flex-wrap: wrap;
        justify-content: flex-end;
    }
    .hf-logbook-btn {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        gap: 8px;
        min-height: 42px;
        padding: 10px 16px;
        border-radius: 999px;
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
        color: #facc15;
        border-color: #70131b;
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
        width: 100%;
        border: 0;
        outline: 0;
        background: transparent;
        color: #111827;
        font-size: 13px;
        font-weight: 700;
    }
    .hf-form-b-table-wrap {
        overflow-x: auto;
    }
    .hf-form-b-table {
        width: 100%;
        border-collapse: collapse;
        min-width: 1080px;
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
    .hf-filter-modal {
        position: fixed;
        inset: 0;
        display: none;
        align-items: center;
        justify-content: center;
        padding: 20px;
        background: rgba(15, 23, 42, 0.55);
        z-index: 1000;
    }
    .hf-filter-modal.is-open {
        display: flex;
    }
    .hf-filter-card {
        width: min(520px, 100%);
        border-radius: 18px;
        background: #ffffff;
        box-shadow: 0 28px 70px rgba(15, 23, 42, 0.28);
        overflow: hidden;
    }
    .hf-filter-head {
        display: flex;
        justify-content: space-between;
        gap: 12px;
        padding: 18px 20px;
        background: #70131b;
        color: #ffffff;
    }
    .hf-filter-head h2 {
        margin: 0;
        font-size: 18px;
        font-weight: 900;
    }
    .hf-filter-close {
        border: 0;
        background: rgba(255,255,255,0.14);
        color: #ffffff;
        border-radius: 999px;
        width: 36px;
        height: 36px;
        cursor: pointer;
        font-size: 22px;
    }
    .hf-filter-form {
        display: grid;
        gap: 14px;
        padding: 20px;
    }
    .hf-filter-field label {
        display: block;
        margin-bottom: 7px;
        color: #475569;
        font-size: 12px;
        font-weight: 900;
        text-transform: uppercase;
    }
    .hf-filter-field input {
        width: 100%;
        height: 44px;
        border: 1px solid #cbd5e1;
        border-radius: 12px;
        padding: 0 13px;
        color: #111827;
        font-weight: 800;
    }
    .hf-filter-actions {
        display: flex;
        justify-content: flex-end;
        gap: 10px;
        padding-top: 4px;
    }
    @media (max-width: 780px) {
        .hf-logbook-header,
        .hf-form-b-heading {
            flex-direction: column;
            align-items: stretch;
        }
        .hf-logbook-btn {
            width: 100%;
        }
    }
</style>
@endpush

@section('content')
@php
    $role = \App\Models\User::normalizeRole(optional(auth()->user())->user_role ?? '');
    $reportsHomeUrl = $role === \App\Models\User::ROLE_ADMIN ? url('/assistant/reports/digital-logbook') : url('/admin/reports/digital-logbook');
    $rangeStartLabel = $dateFrom->format('d M Y');
    $rangeEndLabel = $dateTo->format('d M Y');
    $selectedRangeLabel = $dateFrom->isSameDay($dateTo)
        ? $rangeStartLabel
        : $rangeStartLabel . ' to ' . $rangeEndLabel;
@endphp

<div class="hf-logbook-shell">
    <header class="hf-logbook-header">
        <div>
            <h1 class="hf-logbook-title">Health Forms Logbook</h1>
            <p class="hf-logbook-subtitle">Approved health form clinic visit logbook using Final Review time-in and approval time-out.</p>
        </div>
        <div class="hf-logbook-actions">
            <button type="button" class="hf-logbook-btn primary" id="openHealthFormsLogbookFilter">
                <x-outline-icon name="calendar-days" />
                Filter
            </button>
            <a href="{{ $reportsHomeUrl }}" class="hf-logbook-btn">&larr; Back</a>
        </div>
    </header>

    <section class="hf-form-b-panel">
        <div class="hf-form-b-heading">
            <div>
                <p class="hf-form-b-kicker">PUP Taguig Medical Clinic · Health Form</p>
                <h2 class="hf-form-b-title">Digital Health Form Logbook</h2>
                <p class="hf-form-b-month">{{ $selectedRangeLabel }}</p>
            </div>
            <div class="hf-logbook-search">
                <label for="healthFormsLogbookSearch">Search Patient</label>
                <div class="hf-logbook-search-wrap">
                    <x-outline-icon name="magnifying-glass" />
                    <input id="healthFormsLogbookSearch" type="search" placeholder="Name or reference number" autocomplete="off">
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
                        <th>Patient Name</th>
                        <th>Course-Yr &amp; Sec / Dept</th>
                        <th>Complaints / Impression</th>
                        <th>Treatment / Medicines</th>
                        <th>Qty</th>
                        <th>Physician / Attending Staff</th>
                    </tr>
                </thead>
                <tbody id="healthFormsLogbookBody">
                    @forelse($records as $record)
                        @php
                            $patient = $record->user;
                            $patientName = trim((string) ($patient?->name ?: 'Unnamed Patient'));
                            $reference = trim((string) ($record->reference_number ?: $record->student_number ?: $patient?->student_number));
                            $course = trim((string) ($record->course_college ?: $patient?->course));
                            $yearSection = trim(implode(' - ', array_filter([
                                trim((string) $patient?->year),
                                trim((string) $patient?->section),
                            ])));
                            $courseDepartment = trim(implode(' / ', array_filter([$course, $yearSection])));
                            $dateValue = $record->verified_at ?: $record->created_at;
                            $timeIn = $record->review_started_at ?: $record->created_at;
                            $timeOut = $record->verified_at ?: $record->updated_at;
                            $staffName = trim((string) (optional($record->approvedBy)->name ?: optional($record->reviewStartedBy)->name));
                            $conditionText = $record->hasMedicalCondition() ? 'With medical condition' : 'No medical condition';
                            $remarks = trim((string) ($record->med_assessment_remarks ?: $record->medical_condition_remarks ?: $record->assessment_remarks));
                        @endphp
                        <tr
                            class="hf-logbook-row"
                            data-patient-name="{{ \Illuminate\Support\Str::lower($patientName) }}"
                            data-reference="{{ \Illuminate\Support\Str::lower($reference) }}"
                        >
                            <td>{{ optional($dateValue)->format('m/d/Y') ?: '-' }}</td>
                            <td>{{ optional($timeIn)->format('g:i A') ?: '-' }}</td>
                            <td>{{ optional($timeOut)->format('g:i A') ?: '-' }}</td>
                            <td>
                                <span class="hf-patient-name">{{ $patientName }}</span>
                                <span class="hf-patient-number">{{ $reference ?: 'No reference number' }}</span>
                            </td>
                            <td>{{ $courseDepartment ?: ($patient?->user_type ?: '-') }}</td>
                            <td>
                                <span class="hf-entry">
                                    <span class="hf-entry-label">Complaint</span>
                                    <span class="hf-entry-value">Health form review</span>
                                </span>
                                <span class="hf-entry">
                                    <span class="hf-entry-label">Impression</span>
                                    <span class="hf-entry-value">{{ $conditionText }}{{ $remarks ? ' - ' . $remarks : '' }}</span>
                                </span>
                            </td>
                            <td>
                                Medical clearance
                                <span class="hf-cell-secondary">{{ $record->clearance_status ?: 'Approved' }}</span>
                            </td>
                            <td>-</td>
                            <td>{{ $staffName ?: 'Clinic Staff' }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="9" class="hf-empty">No approved health form records were logged from {{ $selectedRangeLabel }}.</td>
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

<div class="hf-filter-modal" id="healthFormsLogbookFilterModal" aria-hidden="true">
    <div class="hf-filter-card" role="dialog" aria-modal="true" aria-labelledby="healthFormsLogbookFilterTitle">
        <header class="hf-filter-head">
            <h2 id="healthFormsLogbookFilterTitle">Health Forms Logbook Date Range</h2>
            <button type="button" class="hf-filter-close" id="closeHealthFormsLogbookFilter" aria-label="Close filter">&times;</button>
        </header>
        <form method="GET" class="hf-filter-form">
            <div class="hf-filter-field">
                <label for="healthFormsLogbookDateFrom">From</label>
                <input id="healthFormsLogbookDateFrom" type="text" name="date_from" value="{{ $dateFrom->format('d/m/Y') }}" placeholder="DD/MM/YYYY" inputmode="numeric" pattern="\d{2}/\d{2}/\d{4}" required>
            </div>
            <div class="hf-filter-field">
                <label for="healthFormsLogbookDateTo">To</label>
                <input id="healthFormsLogbookDateTo" type="text" name="date_to" value="{{ $dateTo->format('d/m/Y') }}" placeholder="DD/MM/YYYY" inputmode="numeric" pattern="\d{2}/\d{2}/\d{4}" required>
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

<script>
const healthFormsLogbookSearch = document.getElementById('healthFormsLogbookSearch');
const healthFormsLogbookRows = Array.from(document.querySelectorAll('.hf-logbook-row'));
const healthFormsLogbookVisibleCount = document.getElementById('healthFormsLogbookVisibleCount');
const healthFormsLogbookFilterModal = document.getElementById('healthFormsLogbookFilterModal');

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

document.getElementById('openHealthFormsLogbookFilter')?.addEventListener('click', function () {
    healthFormsLogbookFilterModal?.classList.add('is-open');
    healthFormsLogbookFilterModal?.setAttribute('aria-hidden', 'false');
});

function closeHealthFormsLogbookFilter() {
    healthFormsLogbookFilterModal?.classList.remove('is-open');
    healthFormsLogbookFilterModal?.setAttribute('aria-hidden', 'true');
}

document.getElementById('closeHealthFormsLogbookFilter')?.addEventListener('click', closeHealthFormsLogbookFilter);
document.getElementById('cancelHealthFormsLogbookFilter')?.addEventListener('click', closeHealthFormsLogbookFilter);
healthFormsLogbookFilterModal?.addEventListener('click', function (event) {
    if (event.target === healthFormsLogbookFilterModal) closeHealthFormsLogbookFilter();
});
</script>
@endsection
