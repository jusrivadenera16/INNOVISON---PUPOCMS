@extends('layouts.admin')

@section('title', 'Health Forms Approval Logbook')

@push('styles')
<style>
    .logbook-shell {
        max-width: 1480px;
        margin: 0 auto;
        padding: 22px;
    }
    .logbook-head {
        display: flex;
        justify-content: space-between;
        align-items: flex-start;
        gap: 18px;
        margin-bottom: 24px;
    }
    .logbook-title {
        margin: 0;
        font-size: 30px;
        font-weight: 900;
        color: #ffffff;
        letter-spacing: -0.03em;
    }
    .logbook-copy {
        margin: 8px 0 0;
        color: rgba(255,255,255,0.78);
        font-size: 14px;
        line-height: 1.6;
    }
    .logbook-back {
        min-width: 132px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        gap: 7px;
        padding: 10px 16px;
        border: 1px solid rgba(112, 19, 27, 0.3);
        border-radius: 999px;
        background: rgba(255, 255, 255, 0.96);
        color: #70131B;
        font-size: 13px;
        font-weight: 800;
        text-decoration: none;
        transition: all .18s ease;
    }
    .logbook-back:hover {
        background: #ffffff;
        border-color: rgba(112, 19, 27, 0.48);
    }
    .logbook-toolbar {
        display: flex;
        gap: 12px;
        align-items: center;
        margin-bottom: 24px;
    }
    .logbook-search-wrap {
        position: relative;
        flex: 1;
        min-width: 260px;
    }
    .logbook-search-wrap .voice-field-wrap {
        width: 100%;
    }
    .logbook-search-input {
        width: 100%;
        height: 44px;
        border: 1px solid #cbd5e1;
        border-radius: 12px;
        padding: 0 46px 0 14px;
        font-size: 14px;
        color: #111827;
        background: #ffffff;
        transition: border-color .18s ease, box-shadow .18s ease;
    }
    .logbook-search-input:focus {
        outline: none;
        border-color: #7f1d2d;
        box-shadow: 0 0 0 3px rgba(127, 29, 45, 0.1);
    }
    .logbook-search-wrap .voice-field-inline-mic {
        right: 10px;
    }
    .logbook-toolbar-actions {
        display: inline-flex;
        align-items: center;
        justify-content: flex-end;
        gap: 10px;
        flex: 0 0 auto;
    }
    .logbook-export-btn {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        gap: 8px;
        min-height: 44px;
        padding: 0 20px;
        border: 1px solid rgba(250, 204, 21, 0.95);
        border-radius: 12px;
        background: #facc15;
        color: #111827;
        font-size: 13px;
        font-weight: 900;
        text-transform: uppercase;
        letter-spacing: 0.04em;
        text-decoration: none;
        box-shadow: 0 14px 28px rgba(250, 204, 21, 0.22);
        transition: all .18s ease;
        white-space: nowrap;
    }
    .logbook-export-btn:hover {
        background: #eab308;
        border-color: #eab308;
        color: #111827;
        transform: translateY(-1px);
        box-shadow: 0 16px 30px rgba(234, 179, 8, 0.28);
    }
    .filter-btn-open {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        gap: 8px;
        padding: 12px 20px;
        border: none;
        border-radius: 12px;
        background: #7f1d2d;
        color: #ffffff;
        font-size: 13px;
        font-weight: 800;
        text-transform: uppercase;
        letter-spacing: 0.04em;
        cursor: pointer;
        transition: all .18s ease;
        white-space: nowrap;
    }
    .filter-btn-open:hover {
        background: #5f1520;
    }
    .modal-overlay {
        display: none;
        position: fixed;
        top: 0;
        left: 0;
        right: 0;
        bottom: 0;
        background: rgba(15, 23, 42, 0.6);
        z-index: 999;
    }
    .modal-overlay.active {
        display: flex;
        align-items: center;
        justify-content: center;
    }
    .modal-content {
        background: #ffffff;
        border-radius: 20px;
        padding: 32px;
        max-width: 600px;
        width: 90%;
        max-height: 90vh;
        overflow-y: auto;
        box-shadow: 0 20px 60px rgba(15, 23, 42, 0.2);
        animation: slideUp 0.3s ease;
    }
    @keyframes slideUp {
        from {
            transform: translateY(20px);
            opacity: 0;
        }
        to {
            transform: translateY(0);
            opacity: 1;
        }
    }
    .modal-header {
        margin-bottom: 24px;
    }
    .modal-header h2 {
        margin: 0;
        font-size: 24px;
        font-weight: 900;
        color: #111827;
    }
    .modal-close {
        position: absolute;
        top: 20px;
        right: 20px;
        background: none;
        border: none;
        font-size: 28px;
        color: #64748b;
        cursor: pointer;
        transition: color .18s ease;
    }
    .modal-close:hover {
        color: #111827;
    }
    .filters-grid {
        display: grid;
        grid-template-columns: 1fr;
        gap: 16px;
        margin-bottom: 24px;
    }
    .filter-group label {
        display: block;
        margin-bottom: 8px;
        font-size: 12px;
        font-weight: 800;
        text-transform: uppercase;
        color: #475569;
        letter-spacing: 0.04em;
    }
    .filter-group input,
    .filter-group select {
        width: 100%;
        height: 44px;
        border: 1px solid #cbd5e1;
        border-radius: 12px;
        padding: 0 14px;
        font-size: 14px;
        color: #111827;
    }
    .filter-group input:focus,
    .filter-group select:focus {
        outline: none;
        border-color: #7f1d2d;
        box-shadow: 0 0 0 3px rgba(127, 29, 45, 0.1);
    }
    .filter-actions {
        display: flex;
        gap: 10px;
    }
    .filter-btn {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        min-height: 44px;
        padding: 0 18px;
        border-radius: 12px;
        font-weight: 800;
        cursor: pointer;
        border: none;
        font-size: 13px;
        text-transform: uppercase;
        letter-spacing: 0.04em;
        transition: all .18s ease;
        flex: 1;
    }
    .filter-btn.primary {
        background: #7f1d2d;
        color: #ffffff;
    }
    .filter-btn.primary:hover {
        background: #5f1520;
    }
    .filter-btn.secondary {
        background: #eef2f7;
        color: #334155;
    }
    .filter-btn.secondary:hover {
        background: #e2e8f0;
    }
    .logbook-table-wrap {
        background: #ffffff;
        border-radius: 20px;
        padding: 12px;
        box-shadow: 0 14px 30px rgba(15, 23, 42, 0.12);
        overflow-x: auto;
    }
    .logbook-table {
        width: 100%;
        border-collapse: collapse;
    }
    .logbook-table th,
    .logbook-table td {
        padding: 14px 12px;
        border-bottom: 1px solid #e2e8f0;
        font-size: 13px;
        text-align: left;
        color: #111827;
        vertical-align: middle;
    }
    .logbook-table th {
        font-size: 11px;
        font-weight: 900;
        text-transform: uppercase;
        letter-spacing: 0.05em;
        color: #64748b;
        background: #f8fafc;
    }
    .status-badge {
        display: inline-flex;
        align-items: center;
        padding: 6px 12px;
        border-radius: 999px;
        font-size: 11px;
        font-weight: 800;
        text-transform: uppercase;
    }
    .status-approved {
        background: #dcfce7;
        color: #166534;
    }
    .status-pending {
        background: #fef3c7;
        color: #92400e;
    }
    .condition-yes {
        background: #fef2f2;
        color: #991b1b;
    }
    .condition-no {
        background: #eff6ff;
        color: #1d4ed8;
    }
    .condition-tooltip-wrap {
        position: relative;
        display: inline-flex;
        align-items: center;
    }
    .condition-tooltip-wrap .condition-yes {
        cursor: help;
        box-shadow: 0 8px 18px rgba(153, 27, 27, 0.08);
    }
    .condition-tooltip-bubble {
        position: absolute;
        right: 0;
        bottom: calc(100% + 10px);
        z-index: 30;
        width: min(320px, 78vw);
        padding: 14px 15px;
        border: 1px solid rgba(112, 19, 27, 0.16);
        border-radius: 14px;
        background: #ffffff;
        color: #111827;
        box-shadow: 0 20px 40px rgba(15, 23, 42, 0.18);
        opacity: 0;
        visibility: hidden;
        transform: translateY(6px);
        pointer-events: none;
        transition: opacity .18s ease, visibility .18s ease, transform .18s ease;
    }
    .condition-tooltip-bubble::after {
        content: '';
        position: absolute;
        right: 22px;
        bottom: -7px;
        width: 12px;
        height: 12px;
        background: #ffffff;
        border-right: 1px solid rgba(112, 19, 27, 0.16);
        border-bottom: 1px solid rgba(112, 19, 27, 0.16);
        transform: rotate(45deg);
    }
    .condition-tooltip-wrap:hover .condition-tooltip-bubble,
    .condition-tooltip-wrap:focus-within .condition-tooltip-bubble {
        opacity: 1;
        visibility: visible;
        transform: translateY(0);
    }
    .condition-tooltip-title {
        margin: 0 0 8px;
        color: #70131B;
        font-size: 12px;
        font-weight: 900;
        text-transform: uppercase;
        letter-spacing: .04em;
    }
    .condition-tooltip-list {
        display: grid;
        gap: 7px;
        margin: 0;
        padding: 0;
        list-style: none;
    }
    .condition-tooltip-list li {
        color: #334155;
        font-size: 12px;
        font-weight: 700;
        line-height: 1.45;
    }
    .condition-tooltip-list strong {
        color: #111827;
        font-weight: 900;
    }
    .logbook-empty {
        padding: 44px 24px;
        text-align: center;
        color: #64748b;
        font-weight: 700;
    }
    .logbook-pagination {
        margin-top: 20px;
        padding: 14px 16px;
        border: 1px solid rgba(112, 19, 27, 0.1);
        border-radius: 18px;
        background: rgba(255, 255, 255, 0.88);
        box-shadow: 0 18px 36px rgba(15, 23, 42, 0.08);
    }
    .logbook-pagination .pagination {
        display: flex;
        align-items: center;
        justify-content: center;
        gap: 10px;
        flex-wrap: wrap;
        margin: 0;
        padding: 0;
        list-style: none;
    }
    .logbook-pagination .pagination li {
        display: inline-flex;
    }
    .logbook-pagination .pagination a,
    .logbook-pagination .pagination span {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        min-width: 38px;
        height: 38px;
        padding: 0 14px;
        border-radius: 999px;
        border: 1px solid rgba(112, 19, 27, 0.14);
        background: #ffffff;
        color: #70131B;
        font-size: 13px;
        font-weight: 900;
        line-height: 1;
        text-decoration: none;
        box-shadow: 0 10px 20px rgba(112, 19, 27, 0.07);
        transition: transform .18s ease, border-color .18s ease, background-color .18s ease, color .18s ease, box-shadow .18s ease;
    }
    .logbook-pagination .pagination a:hover {
        background: #facc15;
        border-color: #facc15;
        color: #111827;
        transform: translateY(-1px);
        box-shadow: 0 14px 26px rgba(250, 204, 21, 0.24);
    }
    .logbook-pagination .pagination .active span {
        background: #7f1d2d;
        border-color: #7f1d2d;
        color: #ffffff;
        box-shadow: 0 12px 24px rgba(127, 29, 45, 0.22);
    }
    .logbook-pagination .pagination .disabled span {
        background: #f8fafc;
        color: #94a3b8;
        border-color: #e2e8f0;
        box-shadow: none;
        cursor: not-allowed;
    }
    .logbook-pagination .pagination svg {
        display: none !important;
        width: 0 !important;
        height: 0 !important;
        max-width: 0 !important;
        max-height: 0 !important;
    }
    @media (max-width: 720px) {
        .logbook-head,
        .logbook-toolbar {
            flex-direction: column;
            align-items: stretch;
        }
        .logbook-toolbar-actions,
        .logbook-back,
        .filter-btn-open,
        .logbook-export-btn {
            width: 100%;
        }
    }
</style>
@endpush

@section('content')
@php
    $role = \App\Models\User::normalizeRole(optional(auth()->user())->user_role ?? '');
    $reportsUrl = $role === \App\Models\User::ROLE_ADMIN ? url('/assistant/reports') : url('/admin/reports');
    $logbookRouteName = request()->routeIs('assistant.*') ? 'assistant.reports.health-forms-logbook' : 'reports.health-forms-logbook';
    $logbookExportRouteName = request()->routeIs('assistant.*') ? 'assistant.reports.health-forms-logbook.export' : 'reports.health-forms-logbook.export';
@endphp

<div class="logbook-shell">
    <div class="logbook-head">
        <div>
            <h1 class="logbook-title">Health Forms Approval Logbook</h1>
            <p class="logbook-copy">Track all health form submissions and approvals by applicants, students, and staff.</p>
        </div>
        <div style="display: flex; gap: 10px;">
            <button class="filter-btn-open" onclick="openFilterModal()">🔍 Filter</button>
            <a href="{{ $reportsUrl }}" class="logbook-back">&larr; Back</a>
        </div>
    </div>

    <div class="logbook-toolbar">
        <div class="logbook-search-wrap">
            <input type="text" id="searchInput" class="logbook-search-input" placeholder="Search by applicant or student name..." value="{{ $search }}" onkeyup="handleSearch()">
        </div>
        <div class="logbook-toolbar-actions">
            <a href="{{ route($logbookExportRouteName, request()->query()) }}" class="logbook-export-btn">Export</a>
        </div>
    </div>

    <div class="modal-overlay" id="filterModal" onclick="closeFilterModal(event)">
        <div class="modal-content" onclick="event.stopPropagation()">
            <button class="modal-close" onclick="closeFilterModal()">×</button>
            <div class="modal-header">
                <h2>Filter Records</h2>
            </div>
            <form method="GET" class="filters-form" id="filterForm">
                <div class="filters-grid">
                    <div class="filter-group">
                        <label>Course</label>
                        <select name="course">
                            <option value="">All Courses</option>
                            @foreach($courses as $course)
                                <option value="{{ $course }}" {{ $courseFilter === $course ? 'selected' : '' }}>
                                    {{ $course }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="filter-group">
                        <label>User Type</label>
                        <select name="type">
                            <option value="">All Types</option>
                            <option value="Applicant" {{ $userTypeFilter === 'Applicant' ? 'selected' : '' }}>Applicant</option>
                            <option value="Student" {{ $userTypeFilter === 'Student' ? 'selected' : '' }}>Student</option>
                            <option value="Faculty" {{ $userTypeFilter === 'Faculty' ? 'selected' : '' }}>Faculty</option>
                            <option value="Admin" {{ $userTypeFilter === 'Admin' ? 'selected' : '' }}>Admin</option>
                        </select>
                    </div>
                    <div class="filter-group">
                        <label>Gender</label>
                        <select name="gender">
                            <option value="">All Genders</option>
                            <option value="Male" {{ $genderFilter === 'Male' ? 'selected' : '' }}>Male</option>
                            <option value="Female" {{ $genderFilter === 'Female' ? 'selected' : '' }}>Female</option>
                            <option value="Non-binary" {{ $genderFilter === 'Non-binary' ? 'selected' : '' }}>Non-binary</option>
                        </select>
                    </div>
                    <div class="filter-group">
                        <label>Condition</label>
                        <select name="condition">
                            <option value="">All Records</option>
                            <option value="yes" {{ $conditionFilter === 'yes' ? 'selected' : '' }}>With Condition</option>
                            <option value="no" {{ $conditionFilter === 'no' ? 'selected' : '' }}>No Condition</option>
                        </select>
                    </div>
                    <div class="filter-group">
                        <label>Status</label>
                        <select name="status">
                            <option value="">All Status</option>
                            <option value="pending" {{ $statusFilter === 'pending' ? 'selected' : '' }}>Pending</option>
                            <option value="approved" {{ $statusFilter === 'approved' ? 'selected' : '' }}>Approved</option>
                        </select>
                    </div>
                </div>
                <div class="filter-actions">
                    <button type="submit" class="filter-btn primary">Apply Filters</button>
                    <a href="{{ route($logbookRouteName) }}" class="filter-btn secondary">Reset</a>
                </div>
            </form>
        </div>
    </div>

    <div class="logbook-table-wrap">
        <table class="logbook-table">
            <thead>
                <tr>
                    <th>Name</th>
                    <th>Gender</th>
                    <th>Course</th>
                    <th>Type</th>
                    <th>Submitted</th>
                    <th>Approved By</th>
                    <th>Approved Date</th>
                    <th>Status</th>
                    <th>Condition</th>
                </tr>
            </thead>
            <tbody>
                @forelse($logbookRecords as $record)
                    @php
                        $user = $record->user;
                        $approver = $record->approvedBy;
                        $isApproved = $record->clearance_status === 'Issued';
                        $hasCondition = $record->hasMedicalCondition();
                        $conditionDetails = collect();
                        $formatList = static function ($value): string {
                            if (is_array($value)) {
                                return collect($value)
                                    ->filter(fn ($item) => trim((string) $item) !== '')
                                    ->implode(', ');
                            }

                            return trim((string) $value);
                        };

                        if ($record->has_disability === 'Yes') {
                            $conditionDetails->push([
                                'label' => 'Disability',
                                'value' => trim((string) $record->disability_type) !== '' ? $record->disability_type : 'Yes',
                            ]);
                        }

                        if ($record->has_illness === 'Yes' || $formatList($record->medical_history) !== '') {
                            $conditionDetails->push([
                                'label' => 'Medical History',
                                'value' => $formatList($record->medical_history) !== '' ? $formatList($record->medical_history) : 'Yes',
                            ]);
                        }

                        foreach ([
                            'Other Illness' => $record->other_illness,
                            'Food Allergies' => $record->food_allergies,
                            'Medicine Allergies' => $record->medicine_allergies,
                            'Other Medicine Allergies' => $record->other_med_allergies,
                            'Nurse Remarks' => $record->medical_condition_remarks,
                        ] as $label => $value) {
                            $formattedValue = $formatList($value);
                            if ($formattedValue !== '' && $formattedValue !== '[]') {
                                $conditionDetails->push([
                                    'label' => $label,
                                    'value' => $formattedValue,
                                ]);
                            }
                        }
                    @endphp
                    <tr>
                        <td>
                            <strong>{{ $user->name ?? 'N/A' }}</strong>
                        </td>
                        <td>{{ $user->gender ?? 'N/A' }}</td>
                        <td>{{ $record->course_college ?? $user->course ?? 'N/A' }}</td>
                        <td>{{ $user->user_type ?? 'N/A' }}</td>
                        <td>{{ \Carbon\Carbon::parse($record->created_at)->format('M d, Y g:i A') }}</td>
                        <td>
                            @if($approver)
                                <strong>{{ $approver->name }}</strong>
                            @else
                                <span style="color: #94a3b8;">—</span>
                            @endif
                        </td>
                        <td>
                            @if($record->verified_at)
                                {{ \Carbon\Carbon::parse($record->verified_at)->format('M d, Y g:i A') }}
                            @else
                                <span style="color: #94a3b8;">—</span>
                            @endif
                        </td>
                        <td>
                            <span class="status-badge {{ $isApproved ? 'status-approved' : 'status-pending' }}">
                                {{ $isApproved ? 'Approved' : 'Pending' }}
                            </span>
                        </td>
                        <td>
                            @if($hasCondition)
                                <span class="condition-tooltip-wrap">
                                    <span class="status-badge condition-yes" tabindex="0">Yes</span>
                                    <span class="condition-tooltip-bubble" role="tooltip">
                                        <span class="condition-tooltip-title">Medical Condition Details</span>
                                        <ul class="condition-tooltip-list">
                                            @forelse($conditionDetails as $detail)
                                                <li><strong>{{ $detail['label'] }}:</strong> {{ $detail['value'] }}</li>
                                            @empty
                                                <li>Medical condition was flagged, but no specific details were provided.</li>
                                            @endforelse
                                        </ul>
                                    </span>
                                </span>
                            @else
                                <span class="status-badge condition-no">No</span>
                            @endif
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="9" class="logbook-empty">No health form records found matching your filters.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="logbook-pagination">
        {{ $logbookRecords->withQueryString()->links() }}
    </div>
</div>

<script>
function openFilterModal() {
    document.getElementById('filterModal').classList.add('active');
}

function closeFilterModal(event) {
    if (event && event.target.id !== 'filterModal') return;
    document.getElementById('filterModal').classList.remove('active');
}

function handleSearch() {
    const searchValue = document.getElementById('searchInput').value;
    const filterForm = document.getElementById('filterForm');

    let params = new URLSearchParams();
    params.append('q', searchValue);

    const courseValue = filterForm.querySelector('[name="course"]').value;
    if (courseValue) params.append('course', courseValue);

    const typeValue = filterForm.querySelector('[name="type"]').value;
    if (typeValue) params.append('type', typeValue);

    const genderValue = filterForm.querySelector('[name="gender"]').value;
    if (genderValue) params.append('gender', genderValue);

    const conditionValue = filterForm.querySelector('[name="condition"]').value;
    if (conditionValue) params.append('condition', conditionValue);

    const statusValue = filterForm.querySelector('[name="status"]').value;
    if (statusValue) params.append('status', statusValue);

    window.location.href = '{{ route($logbookRouteName) }}?' + params.toString();
}

document.getElementById('filterForm').addEventListener('submit', function(e) {
    e.preventDefault();
    const searchValue = document.getElementById('searchInput').value;

    let params = new URLSearchParams();
    params.append('q', searchValue);
    params.append('course', this.querySelector('[name="course"]').value);
    params.append('type', this.querySelector('[name="type"]').value);
    params.append('gender', this.querySelector('[name="gender"]').value);
    params.append('condition', this.querySelector('[name="condition"]').value);
    params.append('status', this.querySelector('[name="status"]').value);

    window.location.href = '{{ route($logbookRouteName) }}?' + params.toString();
});

document.addEventListener('keydown', function(e) {
    if (e.key === 'Escape') {
        closeFilterModal();
    }
});
</script>

@endsection
