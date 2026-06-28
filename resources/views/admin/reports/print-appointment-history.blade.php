<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Appointment History - {{ $user->name }}</title>
    <style>
        html, body {
            height: 100%;
        }
        body {
            font-family: Arial, sans-serif;
            margin: 20px;
            color: #333;
            display: flex;
            flex-direction: column;
            min-height: 100vh;
        }
        .content {
            flex: 1;
        }
        .report-title {
            text-align: center;
            margin-bottom: 30px;
            font-size: 28px;
            font-weight: bold;
            color: #000000;
        }
        .user-info {
            margin-bottom: 20px;
            padding: 0;
            display: table;
            width: 100%;
        }
        .user-info-left,
        .user-info-right {
            display: table-cell;
            width: 50%;
            padding-right: 20px;
            vertical-align: top;
        }
        .user-info-right {
            padding-left: 20px;
            padding-right: 0;
        }
        .user-info p {
            margin: 5px 0;
            font-size: 13px;
        }
        .user-info strong {
            color: #7f1d2d;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
        }
        table th {
            background: #ffffff;
            color: #111827;
            padding: 12px;
            text-align: left;
            font-weight: bold;
            font-size: 12px;
            border: 1px solid #ddd;
            border-bottom: 2px solid #111827;
        }
        table td {
            padding: 10px 12px;
            border: 1px solid #ddd;
            font-size: 12px;
        }
        table tbody tr:nth-child(even) {
            background: #f9fafb;
        }
        table tbody tr:hover {
            background: #f1f5f9;
        }
        .footer {
            margin-top: 30px;
            padding-top: 15px;
            border-top: 1px solid #ddd;
            text-align: center;
            font-size: 11px;
            color: #999;
        }
        .no-data {
            text-align: center;
            padding: 40px;
            color: #999;
        }
        @media print {
            body {
                margin: 0;
            }
            table {
                page-break-inside: avoid;
            }
            .footer {
                page-break-before: avoid;
            }
        }
</style>
</head>
<body>
    @php
        $formatDate = function ($value) {
            if (!$value) return '-';
            try { return \Carbon\Carbon::parse($value)->format('M d, Y'); } catch (\Throwable $e) { return '-'; }
        };
        $formatTime = function ($value) {
            if (!$value) return '-';
            try { return \Carbon\Carbon::parse($value)->format('g:i A'); } catch (\Throwable $e) { return '-'; }
        };
        $validTimeOut = function ($timeIn, $timeOut) {
            if (!$timeOut) return '-';
            if (!$timeIn) return $timeOut;
            try {
                $in = \Carbon\Carbon::parse($timeIn);
                $out = \Carbon\Carbon::parse($timeOut);
                return $out->lt($in) ? 'Needs review' : $out->format('g:i A');
            } catch (\Throwable $e) {
                return '-';
            }
        };
    @endphp
    <div class="content">
        <div class="report-title">
            History Report
        </div>

        <div class="user-info">
            <div class="user-info-left">
                <p><strong>Patient Name:</strong> {{ $user->name }}</p>
                <p><strong>Email:</strong> {{ $user->email }}</p>
            </div>
            <div class="user-info-right">
                <p><strong>Course:</strong> {{ $user->course ?? 'N/A' }}</p>
                <p><strong>User Type:</strong> {{ $user->user_type ?? 'N/A' }}</p>
            </div>
        </div>

        @if($consultations->count() > 0)
            <table>
                <thead>
                    <tr>
                        <th>Appointment Number</th>
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
                            $visitDate = $cons?->consultation_date ?: $appt?->date;
                            $timeIn = $cons?->time_in ?: $appt?->time;
                            $complaint = trim((string) ($appt?->problem ?: $cons?->reason_for_visit));
                            $impression = trim((string) ($cons?->comments ?? ''));
                        @endphp
                        <tr>
                            <td>{{ $appt?->apt_id ?: 'N/A' }}</td>
                            <td>{{ $formatDate($visitDate) }}</td>
                            <td>{{ $formatTime($timeIn) }}</td>
                            <td>{{ $validTimeOut($timeIn, $cons?->time_out) }}</td>
                            <td>{{ $cons?->service ?: $appt?->service ?: '-' }}</td>
                            <td>{{ $cons && $cons->medicine ? $cons->medicine : ($appt?->notes ?? $appt?->remarks ?? '-') }}</td>
                            <td>{{ $cons && $cons->medicine_quantity ? $cons->medicine_quantity : '-' }}</td>
                            <td>{{ $cons && $cons->pulse_rate ? $cons->pulse_rate . ' bpm' : '-' }}</td>
                            <td>{{ $cons && $cons->respiratory_rate ? $cons->respiratory_rate . ' /min' : '-' }}</td>
                            <td>{{ $cons && $cons->temperature ? $cons->temperature . '°C' : '-' }}</td>
                            <td>{{ $cons && $cons->blood_pressure ? $cons->blood_pressure : '-' }}</td>
                            <td>{{ $cons ? (optional($cons->attendingStaff)->name ?? $cons->attending_staff_name ?? '-') : (optional($appt?->user)->name ?? '-') }}</td>
                            <td>
                                <strong>Complaint:</strong> {{ $complaint !== '' ? $complaint : '-' }}<br>
                                <strong>Impression:</strong> {{ $impression !== '' ? $impression : '-' }}
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        @else
            <div class="no-data">
                <p>No consultation records found for this patient.</p>
            </div>
        @endif
    </div>

    <div class="footer">
        <p>This document contains personal-identifiable information that is subject to Data Privacy. Please keep this document protected and safe.</p>
    </div>
</body>
</html>
