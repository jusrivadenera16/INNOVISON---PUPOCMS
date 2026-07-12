@extends('layouts.admin')

@section('title', 'Appointment History')

@push('styles')
<style>
    .appt-history-shell {
        max-width: 1480px;
        margin: 0 auto;
        padding: 22px;
    }
    .appt-history-head,
    .appt-panel-head,
    .appt-selected-head,
    .appt-table-head {
        display: flex;
        justify-content: space-between;
        align-items: flex-start;
        gap: 18px;
    }
    .appt-history-head {
        margin-bottom: 22px;
    }
    .appt-history-title {
        margin: 0;
        font-size: 30px;
        font-weight: 900;
        color: #ffffff;
    }
    .appt-history-copy {
        margin: 8px 0 0;
        color: rgba(255,255,255,0.78);
        font-size: 13px;
        line-height: 1.6;
        max-width: 680px;
    }
    .appt-history-back,
    .appt-print-btn,
    .appt-view-btn,
    .appt-search-btn {
        display: inline-flex;
        position: relative;
        overflow: hidden;
        align-items: center;
        justify-content: center;
        gap: 8px;
        border-radius: 10px;
        font-weight: 900;
        text-decoration: none;
        transition: all .18s ease;
        cursor: pointer;
    }
    .appt-history-back::after,
    .appt-search-btn::after {
        content: "";
        position: absolute;
        top: -40%;
        left: -130%;
        width: 120%;
        height: 180%;
        background: linear-gradient(115deg, rgba(255,255,255,0) 0%, rgba(255,255,255,.42) 45%, rgba(255,255,255,0) 100%);
        transform: skewX(-20deg);
        transition: left 1.5s ease;
        pointer-events: none;
    }
    .appt-history-back:hover::after,
    .appt-search-btn:hover::after {
        left: 125%;
    }
    .appt-history-back {
        min-width: 150px;
        min-height: 42px;
        padding: 0 16px;
        border: 1px solid #70131B;
        background: #70131B;
        color: #ffffff;
        font-size: 13px;
        box-shadow: 0 10px 22px rgba(112, 19, 27, 0.14);
    }
    .appt-history-back:hover,
    .appt-history-back:focus-visible {
        background: #facc15;
        border-color: #facc15;
        color: #70131B;
        outline: none;
    }
    .appt-print-btn:hover {
        transform: translateY(-1px);
        box-shadow: 0 12px 24px rgba(112, 19, 27, 0.14);
    }
    .appt-panel {
        background: #ffffff;
        border: 1px solid rgba(127, 29, 45, 0.14);
        border-radius: 20px;
        padding: 24px;
        box-shadow: 0 14px 30px rgba(15, 23, 42, 0.10);
        margin-bottom: 22px;
    }
    .appt-panel-title {
        margin: 0;
        font-size: 18px;
        font-weight: 900;
        color: #111827;
    }
    .appt-panel-sub {
        margin: 6px 0 0;
        color: #64748b;
        font-size: 13px;
    }
    .appt-search-form {
        display: flex;
        gap: 12px;
        margin-top: 18px;
    }
    .appt-search-input {
        width: 100%;
        height: 48px;
        border: 1px solid #d8e0ea;
        border-radius: 14px;
        padding: 0 16px;
        font-size: 14px;
        color: #111827;
        outline: none;
        transition: border-color .18s ease, box-shadow .18s ease;
    }
    .appt-search-input:focus {
        border-color: rgba(127, 29, 45, 0.42);
        box-shadow: 0 0 0 4px rgba(127, 29, 45, 0.08);
    }
    .appt-search-btn {
        min-width: 132px;
        border: 0;
        background: #7f1d2d;
        color: #ffffff;
        font-size: 13px;
        padding: 0 18px;
    }
    .appt-users-list {
        display: grid;
        gap: 10px;
        margin-top: 18px;
    }
    .appt-results-count {
        margin: 0;
        font-size: 12px;
        font-weight: 900;
        text-transform: uppercase;
        color: #64748b;
        letter-spacing: .05em;
    }
    .appt-user-card {
        display: grid;
        grid-template-columns: minmax(240px, 1fr) auto;
        gap: 16px;
        align-items: center;
        padding: 16px 18px;
        border: 1px solid #ead5d8;
        border-radius: 14px;
        background: #fffafa;
    }
    .appt-user-card.is-active {
        border-color: #7f1d2d;
        box-shadow: inset 4px 0 0 #7f1d2d;
    }
    .appt-user-name {
        display: block;
        font-weight: 900;
        color: #111827;
        margin-bottom: 8px;
        font-size: 15px;
    }
    .appt-user-meta {
        display: flex;
        gap: 10px;
        flex-wrap: wrap;
    }
    .appt-chip {
        display: inline-flex;
        align-items: center;
        min-height: 28px;
        padding: 6px 10px;
        border-radius: 999px;
        background: #ffffff;
        border: 1px solid #e5e7eb;
        color: #475569;
        font-size: 12px;
        font-weight: 800;
    }
    .appt-view-btn {
        min-width: 132px;
        padding: 11px 18px;
        border: 0;
        background: #7f1d2d;
        color: #ffffff !important;
        font-size: 13px;
    }
    .appt-view-btn:hover,
    .appt-view-btn:focus,
    .appt-search-btn:hover {
        background: #5f1520;
        color: #ffffff !important;
    }
    .appt-selected-card {
        background: linear-gradient(135deg, #7f1d2d 0%, #540e18 100%);
        border-radius: 20px;
        color: #ffffff !important;
        padding: 22px;
        margin-bottom: 22px;
        box-shadow: 0 16px 30px rgba(127, 29, 45, 0.22);
    }
    .appt-selected-head,
    .appt-selected-head * {
        color: #ffffff !important;
    }
    .appt-selected-name {
        margin: 0;
        font-size: 24px;
        font-weight: 900;
        color: #ffffff !important;
    }
    .appt-selected-meta {
        margin-top: 10px;
        display: flex;
        flex-wrap: wrap;
        gap: 10px;
    }
    .appt-selected-meta span {
        display: inline-flex;
        padding: 7px 10px;
        border-radius: 999px;
        background: rgba(255,255,255,0.16);
        color: #ffffff;
        font-size: 12px;
        font-weight: 800;
    }
    .appt-summary-grid {
        display: grid;
        grid-template-columns: repeat(4, minmax(0, 1fr));
        gap: 14px;
        margin-top: 18px;
    }
    .appt-summary-card {
        background: #ffffff;
        color: #111827;
        border-radius: 14px;
        padding: 16px;
        min-height: 92px;
    }
    .appt-summary-card span {
        display: block;
        color: #64748b;
        font-size: 11px;
        font-weight: 900;
        text-transform: uppercase;
        letter-spacing: .05em;
    }
    .appt-summary-card strong {
        display: block;
        margin-top: 8px;
        font-size: 20px;
        font-weight: 900;
        color: #7f1d2d;
    }
    .appt-condition-card {
        position: relative;
        cursor: default;
    }
    .appt-condition-card strong {
        display: inline-flex;
        align-items: center;
        gap: 8px;
    }
    .appt-condition-card.has-condition strong::after {
        content: "View";
        display: inline-flex;
        align-items: center;
        justify-content: center;
        min-height: 22px;
        padding: 0 8px;
        border-radius: 999px;
        background: #fff1f2;
        color: #9f1239;
        font-size: 10px;
        font-weight: 900;
        text-transform: uppercase;
    }
    .appt-condition-popover {
        position: absolute;
        right: 12px;
        bottom: calc(100% - 4px);
        z-index: 20;
        min-width: 260px;
        max-width: 340px;
        padding: 14px;
        border-radius: 12px;
        background: #111827;
        color: #ffffff;
        box-shadow: 0 18px 36px rgba(15, 23, 42, 0.24);
        opacity: 0;
        visibility: hidden;
        transform: translateY(8px);
        transition: opacity .18s ease, transform .18s ease, visibility .18s ease;
    }
    .appt-condition-card.has-condition:hover .appt-condition-popover,
    .appt-condition-card.has-condition:focus-within .appt-condition-popover {
        opacity: 1;
        visibility: visible;
        transform: translateY(0);
    }
    .appt-condition-popover h4 {
        margin: 0 0 8px;
        color: #ffffff;
        font-size: 12px;
        font-weight: 900;
        text-transform: uppercase;
        letter-spacing: .05em;
    }
    .appt-condition-popover p {
        margin: 5px 0;
        color: #e5e7eb;
        font-size: 12px;
        line-height: 1.45;
    }
    .appt-condition-popover b {
        color: #facc15;
    }
    .appt-print-btn {
        padding: 10px 16px;
        border: 1px solid rgba(127, 29, 45, 0.35);
        background: #ffffff;
        color: #7f1d2d;
        font-size: 13px;
    }
    .appt-table-wrap {
        overflow-x: auto;
        border: 1px solid #ead5d8;
        border-radius: 16px;
        background: #ffffff;
    }
    .appt-table {
        width: 100%;
        min-width: 1260px;
        border-collapse: collapse;
        table-layout: fixed;
    }
    .appt-table th {
        padding: 14px 14px;
        border-bottom: 1px solid #ead5d8;
        font-size: 11px;
        font-weight: 900;
        text-transform: uppercase;
        letter-spacing: 0.06em;
        color: #7f1d2d;
        background: #fff5f6;
        text-align: left;
    }
    .appt-table td {
        padding: 18px 14px;
        border-bottom: 1px solid #f1dfe2;
        font-size: 13px;
        color: #111827;
        vertical-align: top;
    }
    .appt-table th:nth-child(1) { width: 120px; }
    .appt-table th:nth-child(2) { width: 150px; }
    .appt-table th:nth-child(3) { width: 150px; }
    .appt-table th:nth-child(4) { width: 170px; }
    .appt-table th:nth-child(5) { width: 280px; }
    .appt-table th:nth-child(6) { width: 150px; }
    .appt-table th:nth-child(7) { width: 340px; }
    .appt-table tbody tr:hover {
        background: #fffafa;
    }
    .appt-table tbody tr:last-child td {
        border-bottom: 0;
    }
    .appt-date-main {
        font-weight: 900;
        color: #111827;
        white-space: nowrap;
    }
    .appt-date-sub {
        display: block;
        margin-top: 4px;
        color: #64748b;
        font-size: 12px;
        font-weight: 700;
    }
    .appt-time-stack {
        display: grid;
        gap: 8px;
        min-width: 126px;
    }
    .appt-time-line {
        display: grid;
        grid-template-columns: 34px 1fr;
        gap: 8px;
        align-items: center;
        white-space: nowrap;
    }
    .appt-time-line strong {
        color: #7f1d2d;
        font-size: 11px;
        text-transform: uppercase;
        letter-spacing: .04em;
    }
    .appt-time-line span {
        font-weight: 800;
        color: #111827;
    }
    .appt-vitals-grid {
        display: grid;
        grid-template-columns: repeat(4, minmax(0, 1fr));
        gap: 8px;
        min-width: 250px;
    }
    .vitals-badge {
        display: flex;
        flex-direction: column;
        justify-content: center;
        gap: 3px;
        min-height: 54px;
        padding: 8px 10px;
        border-radius: 10px;
        background: #fffaf0;
        border: 1px solid #f5d08c;
        color: #7f1d2d;
        text-align: center;
    }
    .vitals-badge span:first-child {
        font-size: 10px;
        font-weight: 900;
        text-transform: uppercase;
        letter-spacing: .04em;
        color: #9f1239;
    }
    .vitals-badge span:last-child {
        font-size: 12px;
        font-weight: 900;
        line-height: 1.2;
        color: #111827;
        word-break: keep-all;
    }
    .appt-notes {
        min-width: 240px;
        white-space: normal;
        line-height: 1.45;
    }
    .appt-empty {
        padding: 40px;
        text-align: center;
        color: #64748b;
        font-weight: 800;
    }
    @media (max-width: 900px) {
        .appt-history-head,
        .appt-panel-head,
        .appt-selected-head,
        .appt-table-head,
        .appt-search-form,
        .appt-user-card {
            grid-template-columns: 1fr;
            flex-direction: column;
            align-items: stretch;
        }
        .appt-summary-grid {
            grid-template-columns: repeat(2, minmax(0, 1fr));
        }
    }
    @media (max-width: 560px) {
        .appt-history-shell {
            padding: 14px;
        }
        .appt-summary-grid {
            grid-template-columns: 1fr;
        }
    }
</style>
@endpush

@section('content')
@php
    $role = \App\Models\User::normalizeRole(optional(auth()->user())->user_role ?? '');
    $reportsUrl = $role === \App\Models\User::ROLE_ADMIN ? url('/assistant/reports') : url('/admin/reports');

    $displayValue = fn ($value, $fallback = 'Not available') => trim((string) $value) !== '' ? $value : $fallback;
    $formatDate = function ($value) {
        if (!$value) return 'Not recorded';
        try { return \Carbon\Carbon::parse($value)->format('M d, Y'); } catch (\Throwable $e) { return 'Not recorded'; }
    };
    $formatTime = function ($value) {
        if (!$value) return 'Not recorded';
        try { return \Carbon\Carbon::parse($value)->format('g:i A'); } catch (\Throwable $e) { return 'Not recorded'; }
    };
    $validTimeOut = function ($timeIn, $timeOut) {
        if (!$timeOut) return 'Not recorded';
        if (!$timeIn) return $timeOut;
        try {
            $in = \Carbon\Carbon::parse($timeIn);
            $out = \Carbon\Carbon::parse($timeOut);
            return $out->lt($in) ? 'Needs review' : $out->format('g:i A');
        } catch (\Throwable $e) {
            return 'Not recorded';
        }
    };
    $selectedId = (int) request('user_id');
@endphp

<div class="appt-history-shell">
    <div class="appt-history-head">
        <div>
            <h1 class="appt-history-title">Appointment History</h1>
            <p class="appt-history-copy">Search for a patient and review consultation visits, vitals, treatment, medicines, and clinic notes in one organized record.</p>
        </div>
        <a href="{{ $reportsUrl }}" class="appt-history-back">&larr; Back to Reports</a>
    </div>

    <section class="appt-panel">
        <div class="appt-panel-head">
            <div>
                <h2 class="appt-panel-title">Find Patient</h2>
                <p class="appt-panel-sub">Search by name, email, student number, or local ID.</p>
            </div>
        </div>
        <form method="GET" class="appt-search-form">
            <input type="text" name="q" class="appt-search-input" placeholder="Search patient records..." value="{{ $search }}" autofocus>
            <button type="submit" class="appt-search-btn">Search</button>
        </form>

        @if($users->count() > 0 && !$selectedUser)
            <div class="appt-users-list">
                <p class="appt-results-count">Found {{ $users->count() }} user(s)</p>
                @foreach($users as $user)
                    @php
                        $courseLine = collect([$user->course, $user->year, $user->section])->filter(fn ($item) => trim((string) $item) !== '')->implode(' - ');
                        $idLine = $user->student_number ?: $user->student_id;
                    @endphp
                    <div class="appt-user-card {{ $selectedId === $user->id ? 'is-active' : '' }}">
                        <div class="appt-user-info">
                            <span class="appt-user-name">{{ $user->name }}</span>
                            <div class="appt-user-meta">
                                <span class="appt-chip">{{ $displayValue($user->email) }}</span>
                                <span class="appt-chip">{{ $displayValue($courseLine) }}</span>
                                <span class="appt-chip">{{ $displayValue($user->user_type ?: $user->user_role) }}</span>
                                <span class="appt-chip">ID: {{ $displayValue($idLine, 'N/A') }}</span>
                            </div>
                        </div>
                        <a href="{{ route('reports.appointment-history', ['q' => $search, 'user_id' => $user->id]) }}" class="appt-view-btn">
                            {{ $selectedId === $user->id ? 'Viewing' : 'View History' }}
                        </a>
                    </div>
                @endforeach
            </div>
        @elseif($selectedUser)
            <p class="appt-results-count" style="margin-top: 14px;">Showing selected patient record. Search again to view another patient.</p>
        @endif
    </section>

    @if($selectedUser)
        @php
            $selectedCourse = collect([$selectedUser->course, $selectedUser->year, $selectedUser->section])->filter(fn ($item) => trim((string) $item) !== '')->implode(' - ');
            $selectedIdentifier = $selectedUser->student_number ?: $selectedUser->student_id;
        @endphp
        <section class="appt-selected-card">
            <div class="appt-selected-head">
                <div>
                    <h2 class="appt-selected-name">{{ $selectedUser->name }}</h2>
                    <div class="appt-selected-meta">
                        <span>{{ $displayValue($selectedUser->email) }}</span>
                        <span>{{ $displayValue($selectedCourse) }}</span>
                        <span>{{ $displayValue($selectedUser->user_type ?: $selectedUser->user_role) }}</span>
                        <span>ID: {{ $displayValue($selectedIdentifier, 'N/A') }}</span>
                    </div>
                </div>
                @if($consultations->count() > 0)
                    <a href="{{ route('reports.appointment-history-print', ['user_id' => $selectedUser->id]) }}" target="_blank" class="appt-print-btn">
                        Print
                    </a>
                @endif
            </div>
            <div class="appt-summary-grid">
                <div class="appt-summary-card"><span>Total Consultations</span><strong>{{ $summary['total'] }}</strong></div>
                <div class="appt-summary-card"><span>Last Visit</span><strong>{{ $summary['last_visit'] ?: 'None' }}</strong></div>
                <div class="appt-summary-card"><span>Top Complaint</span><strong>{{ $summary['common_complaint'] ?: 'None' }}</strong></div>
                <div class="appt-summary-card appt-condition-card {{ $summary['has_condition'] ? 'has-condition' : '' }}" tabindex="{{ $summary['has_condition'] ? '0' : '-1' }}">
                    <span>Condition</span>
                    <strong>{{ $summary['has_condition'] ? 'Yes' : 'No' }}</strong>
                    @if($summary['has_condition'])
                        <div class="appt-condition-popover">
                            <h4>Medical Condition</h4>
                            @forelse($summary['condition_details'] as $label => $value)
                                <p><b>{{ $label }}:</b> {{ $value }}</p>
                            @empty
                                <p>With medical condition, but no details were recorded.</p>
                            @endforelse
                        </div>
                    @endif
                </div>
            </div>
        </section>
    @endif

    @if($selectedUser)
        <section class="appt-panel">
            <div class="appt-table-head">
                <div>
                    <h2 class="appt-panel-title">Consultation History</h2>
                    <p class="appt-panel-sub">Time-out values earlier than time-in are marked for review instead of being shown as valid.</p>
                </div>
            </div>

            <div class="appt-table-wrap" style="margin-top: 18px;">
                <table class="appt-table">
                    <thead>
                        <tr>
                            <th>Appointment Number</th>
                            <th>Visit</th>
                            <th>Time</th>
                            <th>Service</th>
                            <th>Treatment</th>
                            <th>Vitals</th>
                            <th>Attending Staff</th>
                            <th>Complaint / Impression</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($consultations as $record)
                            @php
                                $appt = $record->appointment;
                                $cons = $record->consultation;
                                $visitDate = $cons?->consultation_date ?: $appt?->date;
                                $timeIn = $cons?->time_in ?: $appt?->time;
                                $service = $cons?->service ?: $appt?->service;
                                $medicine = trim((string) ($cons?->medicine ?? ''));
                                $quantity = $cons?->medicine_quantity;
                                $complaint = trim((string) ($appt?->problem ?: $cons?->reason_for_visit));
                                $impression = trim((string) ($cons?->comments ?? ''));
                                $staff = $cons
                                    ? (optional($cons->attendingStaff)->name ?? $cons->attending_staff_name ?? '-')
                                    : '-';
                            @endphp
                            <tr>
                                <td>{{ $appt?->apt_id ?: 'N/A' }}</td>
                                <td>
                                    <span class="appt-date-main">{{ $formatDate($visitDate) }}</span>
                                    <span class="appt-date-sub">{{ $appt ? ucfirst((string) ($appt->type ?: 'Appointment')) : 'Consultation only' }}</span>
                                </td>
                                <td>
                                    <div class="appt-time-stack">
                                        <div class="appt-time-line"><strong>In</strong><span>{{ $formatTime($timeIn) }}</span></div>
                                        <div class="appt-time-line"><strong>Out</strong><span>{{ $validTimeOut($timeIn, $cons?->time_out) }}</span></div>
                                    </div>
                                </td>
                                <td>{{ $displayValue($service, '-') }}</td>
                                <td>
                                    <strong>{{ $medicine !== '' ? $medicine : 'No medicine recorded' }}</strong><br>
                                    <span class="appt-date-sub">Qty: {{ $quantity ?: '-' }}</span>
                                </td>
                                <td>
                                    <div class="appt-vitals-grid">
                                        <span class="vitals-badge"><span>PR</span><span>{{ $cons?->pulse_rate ? $cons->pulse_rate . ' bpm' : '-' }}</span></span>
                                        <span class="vitals-badge"><span>RR</span><span>{{ $cons?->respiratory_rate ? $cons->respiratory_rate . ' /min' : '-' }}</span></span>
                                        <span class="vitals-badge"><span>Temp</span><span>{{ $cons?->temperature ? $cons->temperature . ' C' : '-' }}</span></span>
                                        <span class="vitals-badge"><span>BP</span><span>{{ $cons?->blood_pressure ?: '-' }}</span></span>
                                    </div>
                                </td>
                                <td>{{ $displayValue($staff, '-') }}</td>
                                <td class="appt-notes">
                                    <strong>Complaint:</strong> {{ $complaint !== '' ? $complaint : 'No complaint recorded' }}<br>
                                    <strong>Impression:</strong> {{ $impression !== '' ? $impression : 'No assessment recorded' }}
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="8" class="appt-empty">No consultation history found for this patient.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </section>
    @endif

    @if(!empty($search) && $users->count() === 0)
        <section class="appt-panel">
            <div class="appt-empty">No users found matching "{{ $search }}".</div>
        </section>
    @endif
</div>
@endsection
