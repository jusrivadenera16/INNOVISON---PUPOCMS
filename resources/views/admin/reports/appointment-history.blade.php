@extends('layouts.admin')

@section('title', 'Appointment History')

@push('styles')
<style>
    .appt-history-shell {
        max-width: 1480px;
        margin: 0 auto;
        padding: 22px;
    }
    .appt-history-head {
        display: flex;
        justify-content: space-between;
        align-items: flex-start;
        gap: 18px;
        margin-bottom: 24px;
    }
    .appt-history-title {
        margin: 0;
        font-size: 30px;
        font-weight: 900;
        color: #ffffff;
        letter-spacing: -0.03em;
    }
    .appt-history-copy {
        margin: 8px 0 0;
        color: rgba(255,255,255,0.78);
        font-size: 12px;
        line-height: 1.6;
        max-width: 600px;
    }
    .appt-history-back {
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
    .appt-history-back:hover {
        background: #ffffff;
        border-color: rgba(112, 19, 27, 0.48);
    }
    .appt-search-wrap {
        background: #ffffff;
        border-radius: 20px;
        padding: 24px;
        box-shadow: 0 14px 30px rgba(15, 23, 42, 0.12);
        margin-bottom: 24px;
    }
    .appt-search-input {
        width: 100%;
        height: 48px;
        border: 1px solid #cbd5e1;
        border-radius: 12px;
        padding: 0 16px;
        font-size: 14px;
        color: #111827;
        margin-bottom: 16px;
    }
    .appt-search-input::placeholder {
        color: #94a3b8;
    }
    .appt-users-list {
        display: grid;
        gap: 10px;
    }
    .appt-user-card {
        display: flex;
        justify-content: space-between;
        align-items: center;
        padding: 16px 18px;
        border: 1.5px solid #e2e8f0;
        border-radius: 14px;
        background: linear-gradient(135deg, #ffffff 0%, #f9fafb 100%);
        transition: all .2s ease;
        box-shadow: 0 2px 8px rgba(15, 23, 42, 0.04);
    }
    .appt-user-card:hover {
        background: linear-gradient(135deg, #ffffff 0%, #f8fafc 100%);
        border-color: #7f1d2d;
        box-shadow: 0 8px 20px rgba(127, 29, 45, 0.12);
        transform: translateY(-2px);
    }
    .appt-user-info {
        flex: 1;
    }
    .appt-user-name {
        display: block;
        font-weight: 800;
        color: #111827;
        margin-bottom: 6px;
        font-size: 14px;
    }
    .appt-user-meta {
        display: flex;
        gap: 20px;
        font-size: 12px;
        color: #64748b;
        flex-wrap: wrap;
    }
    .appt-user-meta span {
        display: inline-flex;
        align-items: center;
        gap: 4px;
    }
    .appt-view-btn {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        padding: 10px 18px;
        border: none;
        border-radius: 12px;
        background: #7f1d2d;
        color: #ffffff;
        font-size: 13px;
        font-weight: 800;
        text-transform: uppercase;
        cursor: pointer;
        transition: all .18s ease;
    }
    .appt-view-btn:hover {
        background: #5f1520;
    }
    .appt-table-wrap {
        background: #ffffff;
        border-radius: 20px;
        padding: 0;
        box-shadow: 0 14px 30px rgba(15, 23, 42, 0.12);
        overflow: hidden;
        overflow-x: auto;
    }
    .appt-table {
        width: 100%;
        border-collapse: collapse;
    }
    .appt-table th {
        padding: 16px 14px;
        border-bottom: 2px solid #7f1d2d;
        font-size: 11px;
        font-weight: 900;
        text-transform: uppercase;
        letter-spacing: 0.06em;
        color: #ffffff;
        background: linear-gradient(135deg, #7f1d2d 0%, #5f1520 100%);
        text-align: left;
        vertical-align: middle;
    }
    .appt-table tbody tr {
        border-bottom: 1px solid #e2e8f0;
        transition: all .18s ease;
    }
    .appt-table tbody tr:hover {
        background: #f8fafc;
        box-shadow: inset 0 0 8px rgba(127, 29, 45, 0.08);
    }
    .appt-table td {
        padding: 14px;
        font-size: 13px;
        text-align: left;
        color: #111827;
        vertical-align: middle;
        white-space: nowrap;
    }
    .appt-table td:nth-child(5),
    .appt-table td:nth-child(6) {
        white-space: normal;
        max-width: 200px;
    }
    .appt-table tbody tr:nth-child(even) {
        background: #f9fafb;
    }
    .appt-table tbody tr:last-child {
        border-bottom: none;
    }
    .appt-empty {
        padding: 60px 24px;
        text-align: center;
        color: #64748b;
        font-weight: 700;
        background: #f8fafc;
    }
    .vitals-badge {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        padding: 8px 12px;
        border-radius: 8px;
        background: linear-gradient(135deg, #7f1d2d15 0%, #7f1d2d08 100%);
        border: 1px solid #7f1d2d30;
        font-size: 12px;
        font-weight: 700;
        color: #7f1d2d;
        margin-right: 6px;
        white-space: nowrap;
    }
    .appt-table th:first-child {
        border-top-left-radius: 20px 20px;
    }
    .appt-table th:last-child {
        border-top-right-radius: 20px 20px;
    }
</style>
@endpush

@section('content')
@php
    $role = \App\Models\User::normalizeRole(optional(auth()->user())->user_role ?? '');
    $reportsUrl = $role === \App\Models\User::ROLE_ADMIN ? url('/assistant/reports') : url('/admin/reports');
@endphp

<div class="appt-history-shell">
    <div class="appt-history-head">
        <div>
            <h1 class="appt-history-title">Appointment History</h1>
            <p class="appt-history-copy">Search for a user and view their complete consultation history including vitals, treatment, and medical details.</p>
        </div>
        <a href="{{ $reportsUrl }}" class="appt-history-back">&larr; Back to Reports</a>
    </div>

    <div class="appt-search-wrap">
        <form method="GET">
            <input type="text" name="q" class="appt-search-input" placeholder="Search by user name..." value="{{ $search }}" autofocus>
        </form>

        @if($users->count() > 0)
            <div class="appt-users-list">
                <p style="margin: 0 0 12px; font-size: 12px; font-weight: 700; text-transform: uppercase; color: #64748b; letter-spacing: 0.04em;">
                    Found {{ $users->count() }} user(s)
                </p>
                @foreach($users as $user)
                    <div class="appt-user-card">
                        <div class="appt-user-info">
                            <span class="appt-user-name">{{ $user->name }}</span>
                            <div class="appt-user-meta">
                                <span>📧 {{ $user->email }}</span>
                                <span>📚 {{ $user->course ?? 'N/A' }}</span>
                                <span>👤 {{ $user->user_type ?? 'N/A' }}</span>
                            </div>
                        </div>
                        <a href="{{ route('reports.appointment-history') }}?q={{ $search }}&user_id={{ $user->id }}" class="appt-view-btn">
                            View
                        </a>
                    </div>
                @endforeach
            </div>
        @endif
    </div>

    @if($consultations->count() > 0)
        <div style="margin-bottom: 24px; display: flex; justify-content: space-between; align-items: center;">
            <h2 style="margin: 0; font-size: 20px; font-weight: 700; color: #111827;">
                📋 Consultation History
            </h2>
            <a href="{{ route('reports.appointment-history-print', ['user_id' => request('user_id')]) }}" target="_blank" style="display: inline-flex; align-items: center; gap: 8px; padding: 10px 16px; border: 1.5px solid #7f1d2d; border-radius: 12px; background: #ffffff; color: #7f1d2d; font-size: 13px; font-weight: 800; text-transform: uppercase; cursor: pointer; transition: all .18s ease; text-decoration: none;" onmouseover="this.style.background='#7f1d2d'; this.style.color='#ffffff';" onmouseout="this.style.background='#ffffff'; this.style.color='#7f1d2d';">
                🖨️ Print
            </a>
        </div>

        <div class="appt-table-wrap">
            <table class="appt-table">
                <thead>
                    <tr>
                        <th>Day</th>
                        <th>Time In</th>
                        <th>Time Out</th>
                        <th>Service</th>
                        <th>Treatment/Medicines</th>
                        <th>Qty</th>
                        <th>PR</th>
                        <th>RR</th>
                        <th>Temp</th>
                        <th>BP</th>
                        <th>Attending Staff</th>
                        <th>Complaints/Impression</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($consultations as $record)
                        @php
                            $appt = $record->appointment;
                            $cons = $record->consultation;
                        @endphp
                        <tr>
                            <td>{{ \Carbon\Carbon::parse($appt->date)->format('M d, Y') }}</td>
                            <td>{{ $appt->time ?? '-' }}</td>
                            <td>{{ $cons ? ($cons->time_out ?? '-') : '-' }}</td>
                            <td>{{ $appt->service ?? '-' }}</td>
                            <td>{{ $cons && $cons->medicine ? $cons->medicine : ($appt->notes ?? $appt->remarks ?? '-') }}</td>
                            <td>{{ $cons && $cons->medicine_quantity ? $cons->medicine_quantity : '-' }}</td>
                            <td>
                                @if($cons && $cons->pulse_rate)
                                    <span class="vitals-badge">{{ $cons->pulse_rate }} bpm</span>
                                @else
                                    -
                                @endif
                            </td>
                            <td>
                                @if($cons && $cons->respiratory_rate)
                                    <span class="vitals-badge">{{ $cons->respiratory_rate }} /min</span>
                                @else
                                    -
                                @endif
                            </td>
                            <td>
                                @if($cons && $cons->temperature)
                                    <span class="vitals-badge">{{ $cons->temperature }}°C</span>
                                @else
                                    -
                                @endif
                            </td>
                            <td>
                                @if($cons && $cons->blood_pressure)
                                    <span class="vitals-badge">{{ $cons->blood_pressure }}</span>
                                @else
                                    -
                                @endif
                            </td>
                            <td>{{ $cons ? (optional($cons->attendingStaff)->name ?? optional($cons->user)->name ?? '-') : (optional($appt->user)->name ?? '-') }}</td>
                            <td style="max-width: 250px; word-wrap: break-word;">{{ $appt->problem ?? ($cons && $cons->reason_for_visit ? $cons->reason_for_visit : '-') }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    @endif

    @if(!empty($search) && $users->count() === 0)
        <div style="background: #ffffff; border-radius: 20px; padding: 40px; box-shadow: 0 14px 30px rgba(15, 23, 42, 0.12); text-align: center; color: #64748b; margin-top: 24px;">
            <p style="margin: 0; font-weight: 700;">❌ No users found matching "{{ $search }}"</p>
        </div>
    @endif
</div>

@endsection
