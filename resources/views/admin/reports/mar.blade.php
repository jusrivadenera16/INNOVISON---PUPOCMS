@extends('layouts.admin')

@section('title', 'MAR Report')

@push('styles')
<style>
    .card { background: #fff; border-radius: 12px; padding: 24px; box-shadow: 0 4px 12px rgba(0,0,0,0.05); border: 1px solid #f0f0f0; margin-bottom: 24px; }
    h2 { color: #8B0000; font-size: 22px; margin-bottom: 20px; border-bottom: 2px solid #8B0000; padding-bottom: 10px; }
    h3 { color: #334155; font-size: 18px; margin-top: 30px; }
    .mar-header-bar {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 16px;
        margin-bottom: 18px;
        flex-wrap: wrap;
    }
    .mar-header-bar h2 {
        margin: 0;
        padding-bottom: 0;
        border-bottom: none;
    }
    .mar-filter-bar {
        display: flex;
        align-items: flex-end;
        justify-content: space-between;
        gap: 14px;
        flex-wrap: wrap;
        padding: 16px 18px;
        border-radius: 16px;
        background: linear-gradient(180deg, #fffaf5 0%, #ffffff 100%);
        border: 1px solid #f3e8d1;
        box-shadow: inset 0 1px 0 rgba(255,255,255,0.7);
        margin-bottom: 20px;
    }
    .mar-filter-copy {
        min-width: 0;
        flex: 1 1 220px;
    }
    .mar-filter-title {
        margin: 0 0 4px;
        font-size: 14px;
        font-weight: 800;
        color: #70131B;
        text-transform: uppercase;
        letter-spacing: 0.04em;
    }
    .mar-filter-subtitle {
        margin: 0;
        font-size: 13px;
        color: #64748b;
    }
    .mar-filter-controls {
        display: flex;
        align-items: center;
        gap: 10px;
        flex-wrap: wrap;
    }
    .mar-date-input {
        min-width: 170px;
        padding: 10px 12px;
        border-radius: 10px;
        border: 1px solid #d5c4a1;
        background: #ffffff;
        color: #334155;
        font-size: 14px;
        font-weight: 700;
    }
    .mar-date-field {
        display: grid;
        gap: 5px;
    }
    .mar-date-field label {
        font-size: 11px;
        font-weight: 800;
        color: #70131B;
        text-transform: uppercase;
        letter-spacing: 0.05em;
    }
    .mar-generate-btn {
        background: linear-gradient(135deg, #70131B, #8f2230);
        color: #ffffff;
        padding: 10px 16px;
        border-radius: 10px;
        border: 1px solid #70131B;
        font-size: 14px;
        font-weight: 800;
        cursor: pointer;
        box-shadow: 0 10px 20px rgba(112, 19, 27, 0.14);
        transition: .18s ease;
    }
    .mar-generate-btn:hover {
        transform: translateY(-1px);
        box-shadow: 0 14px 24px rgba(112, 19, 27, 0.18);
    }

    .mar-top-actions {
        display: flex;
        justify-content: flex-end;
    }
    .mar-manage-btn {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        gap: 10px;
        position: relative;
        overflow: hidden;
        white-space: nowrap;
        background: linear-gradient(135deg, #70131B, #8f2230);
        color: #ffffff;
        padding: 11px 18px;
        border-radius: 999px;
        text-decoration: none;
        font-size: 14px;
        font-weight: 800;
        border: 1px solid #8f2230;
        box-shadow:
            0 0 0 3px rgba(112, 19, 27, 0.12),
            0 10px 22px rgba(112, 19, 27, 0.20);
        transition: color .08s linear, transform .18s ease, box-shadow .18s ease, background .18s ease;
        z-index: 0;
    }
    .mar-manage-btn::after {
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
        z-index: -1;
    }
    .mar-manage-btn::before {
        content: "MC";
        width: 28px;
        height: 28px;
        border-radius: 999px;
        background: #ffefb5;
        color: #70131B;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        font-size: 11px;
        font-weight: 900;
        letter-spacing: 0.04em;
        flex: 0 0 auto;
        position: relative;
        z-index: 1;
    }
    .mar-manage-btn-label {
        position: relative;
        z-index: 1;
        transition: color .08s linear;
        color: #ffffff;
    }
    .mar-manage-btn:hover {
        transform: translateY(-2px);
        border-color: #facc15;
        box-shadow:
            0 0 0 3px rgba(250, 204, 21, 0.12),
            0 18px 30px rgba(139, 0, 0, 0.22);
        color: #111111;
        background: #facc15;
    }
    .mar-manage-btn:hover .mar-manage-btn-label {
        color: #111111;
    }
    .mar-manage-btn:hover::before {
        background: #111111;
        color: #facc15;
    }
    .mar-manage-btn:hover::after {
        transform: translateX(135%);
    }

    /* Table Styling */
    .mar-table { width: 100%; border-collapse: collapse; margin-top: 15px; }
    .mar-table th { background: #f8fafc; color: #64748b; text-align: left; padding: 12px; border-bottom: 2px solid #eee; font-size: 13px; }
    .mar-table td { padding: 12px; border-bottom: 1px solid #f1f5f9; font-size: 14px; color: #334155; }
    .category-row { background: #70131B; font-weight: 700; color: #ffffff; }
    .section-row td { background: linear-gradient(135deg, #70131B, #8f2230); color: #ffffff !important; font-weight: 800; }
    .subsection-row td { background: #8f2230; color: #ffffff !important; font-weight: 800; }
    .detail-row td { background: #e5e7eb; color: #334155 !important; font-weight: 800; }
    .nested-row td:first-child { padding-left: 30px; }
    .deep-nested-row td:first-child { padding-left: 48px; }
    .gad-table { width: 100%; border-collapse: collapse; margin-top: 26px; }
    .gad-table th { background: #f8fafc; color: #64748b; text-align: left; padding: 12px; border-bottom: 2px solid #eee; font-size: 13px; }
    .gad-table td { padding: 12px; border-bottom: 1px solid #f1f5f9; font-size: 14px; color: #334155; }
    .gad-section-row td { background: linear-gradient(135deg, #70131B, #8f2230); color: #ffffff !important; font-weight: 800; }
    .report-switcher { display: flex; flex-wrap: wrap; gap: 10px; margin: 18px 0 8px; }
    .report-switch-btn {
        position: relative;
        overflow: hidden;
        border: 1px solid #8f2230;
        background: linear-gradient(135deg, #70131B, #8f2230);
        color: #ffffff;
        border-radius: 999px;
        padding: 10px 14px;
        font-size: 13px;
        font-weight: 800;
        cursor: pointer;
        transition: color .08s linear, transform .18s ease, box-shadow .18s ease, background .18s ease, border-color .18s ease;
        box-shadow:
            0 0 0 3px rgba(112, 19, 27, 0.12),
            0 10px 22px rgba(112, 19, 27, 0.20);
        z-index: 0;
    }
    .report-switch-btn::after {
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
        z-index: -1;
    }
    .report-switch-btn:hover {
        background: #facc15;
        color: #111111;
        transform: translateY(-2px);
        border-color: #facc15;
        box-shadow:
            0 0 0 3px rgba(250, 204, 21, 0.12),
            0 18px 30px rgba(139, 0, 0, 0.22);
    }
    .report-switch-btn:hover::after {
        transform: translateX(135%);
    }
    .report-switch-btn.is-active {
        background: #facc15;
        color: #111111;
        border-color: #facc15;
        box-shadow:
            0 0 0 3px rgba(250, 204, 21, 0.12),
            0 18px 30px rgba(139, 0, 0, 0.22);
    }
    .report-panel {
        display: none;
        animation: marPanelFade .18s ease;
    }
    .report-panel.is-active {
        display: block;
    }
    .table-panel-title {
        margin: 18px 0 8px;
        display: inline-flex;
        align-items: center;
        padding: 8px 14px;
        border-radius: 999px;
        background: #70131B;
        color: #fff;
        font-size: 13px;
        font-weight: 800;
        letter-spacing: 0.02em;
        text-transform: uppercase;
    }
    @keyframes marPanelFade {
        from { opacity: 0; transform: translateY(6px); }
        to { opacity: 1; transform: translateY(0); }
    }
    
    /* CRUD Form */
    .manage-section { background: #fdfdfd; border: 1px dashed #cbd5e1; padding: 20px; border-radius: 10px; margin-top: 40px; }
    .form-group { margin-bottom: 15px; }
    .btn-save { background: #70131B; color: white; border: none; padding: 8px 15px; border-radius: 5px; cursor: pointer; }
    .btn-delete { color: #ef4444; text-decoration: none; font-size: 12px; margin-left: 10px; }

    /* MOBILE RESPONSIVE */
    @media (max-width: 768px) {
        /* Form - Stack on Mobile */
        .form-group {
            margin-bottom: 12px !important;
        }

        .form-group input,
        .form-group textarea,
        .form-group select {
            width: 100% !important;
            font-size: 16px !important;
            padding: 10px !important;
        }

        .btn-save,
        .btn-delete {
            padding: 10px 16px !important;
            font-size: 14px !important;
            min-height: 40px !important;
        }

        /* Table - Horizontal Scroll */
        .table-container,
        .table-wrapper {
            overflow-x: auto !important;
            -webkit-overflow-scrolling: touch;
        }

        table {
            min-width: 100% !important;
            font-size: 13px !important;
        }
    }

    /* DARK MODE FIXES */
    html[data-theme="dark"] .manage-section {
        background: rgba(35, 17, 25, 0.96) !important;
        border-color: rgba(255, 255, 255, 0.12) !important;
    }

    html[data-theme="dark"] .form-group label {
        color: #f8fafc !important;
    }

    html[data-theme="dark"] .form-group input,
    html[data-theme="dark"] .form-group textarea,
    html[data-theme="dark"] .form-group select {
        background: rgba(18, 18, 18, 0.55) !important;
        color: #000000 !important;
        border-color: rgba(255, 255, 255, 0.12) !important;
    }

    html[data-theme="dark"] input[type="date"],
    html[data-theme="dark"] input[type="datetime-local"] {
        color: #000000 !important;
    }

    html[data-theme="dark"] .form-group input::placeholder,
    html[data-theme="dark"] .form-group textarea::placeholder {
        color: rgba(248, 250, 252, 0.5) !important;
    }

    html[data-theme="dark"] .btn-save {
        background: #70131B !important;
        color: #f8fafc !important;
    }

    html[data-theme="dark"] .btn-save:hover {
        background: #8f2230 !important;
    }

    html[data-theme="dark"] table {
        background: rgba(18, 18, 18, 0.4) !important;
        color: #f8fafc !important;
    }

    html[data-theme="dark"] table th {
        background: rgba(18, 18, 18, 0.55) !important;
        color: #f8fafc !important;
        border-color: rgba(255, 255, 255, 0.08) !important;
    }

    html[data-theme="dark"] table td {
        border-color: rgba(255, 255, 255, 0.08) !important;
        color: #f8fafc !important;
    }

    html[data-theme="dark"] table tbody tr:hover {
        background: rgba(59, 24, 33, 0.5) !important;
    }

    html[data-theme="dark"] table tbody tr[style*="background-color: #f8f9fa"] {
        background-color: rgba(35, 17, 25, 0.96) !important;
        color: #f3d6da !important;
    }

    html[data-theme="dark"] table tbody tr[style*="background-color: #f8f9fa"] td {
        color: #f3d6da !important;
    }

    html[data-theme="dark"] table tbody tr[style*="background-color: #f8f9fa"]:hover {
        background-color: rgba(45, 22, 32, 0.98) !important;
    }

    /* Back to Reports Button */
    .mar-back-button {
        display: inline-block;
        padding: 0;
        border: none;
        background: none;
        color: #000000;
        font-size: 13px;
        font-weight: 600;
        text-decoration: none;
        white-space: nowrap;
        cursor: pointer;
        transition: color .2s ease;
    }

    .mar-back-button:hover,
    .mar-back-button:focus {
        color: #333333;
        outline: none;
        text-decoration: underline;
    }
</style>
@endpush

@section('content')
@php
    $role = \App\Models\User::normalizeRole(optional(auth()->user())->user_role ?? '');
    $reportsHomeUrl = $role === \App\Models\User::ROLE_ADMIN ? url('/assistant/reports') : url('/admin/reports');
@endphp

@php
    $consultationGad = $gadTables['consultation'] ?? [];
    $certificateGad = $gadTables['certificate'] ?? [];
    $triageOnlineGad = $gadTables['triage_online'] ?? [];
    $combinedGad = $gadTables['combined'] ?? [];
@endphp



 <a href="{{ $reportsHomeUrl }}" class="mar-back-button">&larr; Back to Reports</a>



<div class="card">
    <div class="mar-header-bar">
        <h2>Medical Accomplishment Report</h2>
    </div>

    <form method="GET" class="mar-filter-bar">
        <div class="mar-filter-copy">
            <p class="mar-filter-title">Report Filter</p>
            <p class="mar-filter-subtitle">Set the date range you want to review, then regenerate the MAR tables below.</p>
        </div>
        <div class="mar-filter-controls">
            <div class="mar-date-field">
                <label for="marDateFrom">From Date</label>
                <input type="date" id="marDateFrom" name="date_from" class="mar-date-input" value="{{ $dateFrom ?? '' }}">
            </div>
            <div class="mar-date-field">
                <label for="marDateTo">To Date</label>
                <input type="date" id="marDateTo" name="date_to" class="mar-date-input" value="{{ $dateTo ?? '' }}">
            </div>
            <button class="mar-generate-btn" type="submit">Generate</button>
        </div>
    </form>
    
    <div class="report-switcher" id="marReportSwitcher">
        <button type="button" class="report-switch-btn is-active" data-target="mar-main-table">Consultation & Services</button>
        <button type="button" class="report-switch-btn" data-target="mar-gad-consultation">GAD Consultation</button>
        <button type="button" class="report-switch-btn" data-target="mar-gad-certificate">GAD Certificate</button>
        <button type="button" class="report-switch-btn" data-target="mar-gad-triage">GAD Triage Online</button>
        <button type="button" class="report-switch-btn" data-target="mar-gad-combined">GAD Combined</button>
    </div>

    

    @php
        $resolveConsultationPatientType = function ($consultation) {
            $value = strtolower(trim((string) ($consultation->user_role ?: $consultation->user_type ?: '')));

            return match ($value) {
                'student' => 'student',
                'faculty' => 'faculty',
                'admin', 'staff' => 'admin',
                'dependent', 'dependents' => 'dependent',
                default => null,
            };
        };

        $countByPatientType = function ($consultations, string $type) use ($resolveConsultationPatientType) {
            return $consultations->filter(function ($consultation) use ($resolveConsultationPatientType, $type) {
                return $resolveConsultationPatientType($consultation) === $type;
            })->count();
        };

        $countCertificateByType = function ($consultations, string $certificateType, string $patientType) use ($countByPatientType) {
            $filtered = $consultations->filter(function ($consultation) use ($certificateType) {
                return trim((string) ($consultation->certificate_type ?? 'none')) === $certificateType;
            });

            return $countByPatientType($filtered, $patientType);
        };

        $consultationTotals = ['student' => 0, 'faculty' => 0, 'admin' => 0, 'dependent' => 0];
        foreach ($categories as $cat) {
            foreach ($cat->medicalConditions as $condition) {
                $consultationTotals['student'] += $countByPatientType($condition->consultations, 'student');
                $consultationTotals['faculty'] += $countByPatientType($condition->consultations, 'faculty');
                $consultationTotals['admin'] += $countByPatientType($condition->consultations, 'admin');
                $consultationTotals['dependent'] += $countByPatientType($condition->consultations, 'dependent');
            }
        }
        $consultationGrandTotal = array_sum($consultationTotals);

        $excusedLetterTotals = ['student' => 0, 'faculty' => 0, 'admin' => 0, 'dependent' => 0];
        $cocIjtTotals = ['student' => 0, 'faculty' => 0, 'admin' => 0, 'dependent' => 0];
        $cocLadderizedTotals = ['student' => 0, 'faculty' => 0, 'admin' => 0, 'dependent' => 0];
        $excusedLetterCategoryRows = collect();
        $onlineTotals = [
            'consultation' => ['student' => 0, 'faculty' => 0, 'admin' => 0, 'dependent' => 0],
            'medical_clearance' => ['student' => 0, 'faculty' => 0, 'admin' => 0, 'dependent' => 0],
            'others' => ['student' => 0, 'faculty' => 0, 'admin' => 0, 'dependent' => 0],
        ];

        foreach ($categories as $cat) {
            $categoryConsultations = $cat->medicalConditions->flatMap->consultations;

            $categoryExcused = [
                'student' => $countCertificateByType($categoryConsultations, 'excused_letter', 'student'),
                'faculty' => $countCertificateByType($categoryConsultations, 'excused_letter', 'faculty'),
                'admin' => $countCertificateByType($categoryConsultations, 'excused_letter', 'admin'),
                'dependent' => $countCertificateByType($categoryConsultations, 'excused_letter', 'dependent'),
            ];

            if (array_sum($categoryExcused) > 0) {
                $excusedLetterCategoryRows->push([
                    'label' => 'Category ' . $cat->code . ' - ' . $cat->name,
                    'student' => $categoryExcused['student'],
                    'faculty' => $categoryExcused['faculty'],
                    'admin' => $categoryExcused['admin'],
                    'dependent' => $categoryExcused['dependent'],
                    'total' => array_sum($categoryExcused),
                ]);
            }

            foreach (['student', 'faculty', 'admin', 'dependent'] as $type) {
                $excusedLetterTotals[$type] += $categoryExcused[$type];
                $cocIjtTotals[$type] += $countCertificateByType($categoryConsultations, 'coc_ijt', $type);
                $cocLadderizedTotals[$type] += $countCertificateByType($categoryConsultations, 'coc_ladderized', $type);
            }

            $onlineConsultations = $categoryConsultations->filter(function ($consultation) {
                return trim((string) ($consultation->consultation_source ?? '')) === 'online';
            });

            $onlineBuckets = [
                'consultation' => $onlineConsultations->filter(function ($consultation) {
                    $service = strtolower(trim((string) ($consultation->service ?? '')));
                    return in_array($service, ['general consultation', 'consultation'], true);
                }),
                'medical_clearance' => $onlineConsultations->filter(function ($consultation) {
                    $service = strtolower(trim((string) ($consultation->service ?? '')));
                    return str_contains($service, 'clearance');
                }),
            ];
            $onlineBuckets['others'] = $onlineConsultations->reject(function ($consultation) {
                $service = strtolower(trim((string) ($consultation->service ?? '')));
                return in_array($service, ['general consultation', 'consultation'], true) || str_contains($service, 'clearance');
            });

            foreach ($onlineBuckets as $bucket => $consultations) {
                foreach (['student', 'faculty', 'admin', 'dependent'] as $type) {
                    $onlineTotals[$bucket][$type] += $countByPatientType($consultations, $type);
                }
            }
        }
    @endphp

    <div class="report-panel is-active" id="mar-main-table">
    <div class="table-panel-title">Consultation & Services</div>
    <table class="mar-table">
        <thead>
            <tr>
                <th>Service Category / Conditions</th>
                <th style="text-align: center;">Student</th>
                <th style="text-align: center;">Faculty</th>
                <th style="text-align: center;">Admin</th>
                <th style="text-align: center;">Dependent</th>
                <th style="text-align: center;">Total</th>
            </tr>
        </thead>
        <tbody>
            <tr class="section-row">
                <td colspan="6">i. Consultation / Treatment</td>
            </tr>
            @foreach($categories as $cat)
    <tr style="background-color: #f8f9fa; font-weight: bold;">
        <td colspan="6">Category {{ $cat->code }} - {{ $cat->name }}</td>
    </tr>

        @foreach($cat->medicalConditions as $condition)
        @php
            $stu = $countByPatientType($condition->consultations, 'student');
            $fac = $countByPatientType($condition->consultations, 'faculty');
            $adm = $countByPatientType($condition->consultations, 'admin');
            $dep = $countByPatientType($condition->consultations, 'dependent');
            $total = $stu + $fac + $adm + $dep;
        @endphp
        <tr>
            <td style="padding-left: 30px;">{{ $condition->name }}</td>
            <td class="text-center">{{ $stu }}</td>
            <td class="text-center">{{ $fac }}</td>
            <td class="text-center">{{ $adm }}</td>
            <td class="text-center">{{ $dep }}</td>
            <td class="text-center">{{ $total }}</td>
        </tr>
    @endforeach
@endforeach
            <tr class="subsection-row">
                <td>Total Consultation</td>
                <td class="text-center">{{ $consultationTotals['student'] }}</td>
                <td class="text-center">{{ $consultationTotals['faculty'] }}</td>
                <td class="text-center">{{ $consultationTotals['admin'] }}</td>
                <td class="text-center">{{ $consultationTotals['dependent'] }}</td>
                <td class="text-center">{{ $consultationGrandTotal }}</td>
            </tr>

            <tr class="section-row"><td colspan="6">ii. Medical Certificate / Clearance - Certificate of Compliance</td></tr>
            <tr class="detail-row nested-row">
                <td>A. Excused Letter</td>
                <td class="text-center">{{ $excusedLetterTotals['student'] }}</td>
                <td class="text-center">{{ $excusedLetterTotals['faculty'] }}</td>
                <td class="text-center">{{ $excusedLetterTotals['admin'] }}</td>
                <td class="text-center">{{ $excusedLetterTotals['dependent'] }}</td>
                <td class="text-center">{{ array_sum($excusedLetterTotals) }}</td>
            </tr>
            @forelse($excusedLetterCategoryRows as $categoryRow)
                <tr class="nested-row deep-nested-row">
                    <td>{{ $categoryRow['label'] }}</td>
                    <td class="text-center">{{ $categoryRow['student'] }}</td>
                    <td class="text-center">{{ $categoryRow['faculty'] }}</td>
                    <td class="text-center">{{ $categoryRow['admin'] }}</td>
                    <td class="text-center">{{ $categoryRow['dependent'] }}</td>
                    <td class="text-center">{{ $categoryRow['total'] }}</td>
                </tr>
            @empty
                <tr class="nested-row deep-nested-row"><td>No excused letter category recorded yet.</td><td class="text-center">0</td><td class="text-center">0</td><td class="text-center">0</td><td class="text-center">0</td><td class="text-center">0</td></tr>
            @endforelse
            <tr class="detail-row nested-row">
                <td>B. COC for IJT</td>
                <td class="text-center">{{ $cocIjtTotals['student'] }}</td>
                <td class="text-center">{{ $cocIjtTotals['faculty'] }}</td>
                <td class="text-center">{{ $cocIjtTotals['admin'] }}</td>
                <td class="text-center">{{ $cocIjtTotals['dependent'] }}</td>
                <td class="text-center">{{ array_sum($cocIjtTotals) }}</td>
            </tr>
            <tr class="detail-row nested-row">
                <td>C. COC for Ladderized</td>
                <td class="text-center">{{ $cocLadderizedTotals['student'] }}</td>
                <td class="text-center">{{ $cocLadderizedTotals['faculty'] }}</td>
                <td class="text-center">{{ $cocLadderizedTotals['admin'] }}</td>
                <td class="text-center">{{ $cocLadderizedTotals['dependent'] }}</td>
                <td class="text-center">{{ array_sum($cocLadderizedTotals) }}</td>
            </tr>

            <tr class="section-row"><td colspan="6">iii. Injections</td></tr>
            <tr><td class="nested-row">Injection Services</td><td class="text-center">0</td><td class="text-center">0</td><td class="text-center">0</td><td class="text-center">0</td><td class="text-center">0</td></tr>

            <tr class="section-row"><td colspan="6">iv. Referrals</td></tr>
            <tr class="detail-row nested-row"><td>A. Ref. to Hospital without nurse</td><td class="text-center">0</td><td class="text-center">0</td><td class="text-center">0</td><td class="text-center">0</td><td class="text-center">0</td></tr>
            <tr class="detail-row nested-row"><td>B. Ref. to Hospital with nurse</td><td class="text-center">0</td><td class="text-center">0</td><td class="text-center">0</td><td class="text-center">0</td><td class="text-center">0</td></tr>
            <tr class="detail-row nested-row"><td>C. Referral</td><td class="text-center">0</td><td class="text-center">0</td><td class="text-center">0</td><td class="text-center">0</td><td class="text-center">0</td></tr>

            <tr class="section-row"><td colspan="6">v. Others</td></tr>
            <tr><td class="nested-row">Other Services</td><td class="text-center">0</td><td class="text-center">0</td><td class="text-center">0</td><td class="text-center">0</td><td class="text-center">0</td></tr>

            <tr class="section-row"><td colspan="6">vi. On-line Consultation</td></tr>
            <tr class="detail-row nested-row"><td>A. Consultation</td><td class="text-center">{{ $onlineTotals['consultation']['student'] }}</td><td class="text-center">{{ $onlineTotals['consultation']['faculty'] }}</td><td class="text-center">{{ $onlineTotals['consultation']['admin'] }}</td><td class="text-center">{{ $onlineTotals['consultation']['dependent'] }}</td><td class="text-center">{{ array_sum($onlineTotals['consultation']) }}</td></tr>
            <tr class="detail-row nested-row"><td>B. Medical Clearance</td><td class="text-center">{{ $onlineTotals['medical_clearance']['student'] }}</td><td class="text-center">{{ $onlineTotals['medical_clearance']['faculty'] }}</td><td class="text-center">{{ $onlineTotals['medical_clearance']['admin'] }}</td><td class="text-center">{{ $onlineTotals['medical_clearance']['dependent'] }}</td><td class="text-center">{{ array_sum($onlineTotals['medical_clearance']) }}</td></tr>
            <tr class="detail-row nested-row"><td>C. Others</td><td class="text-center">{{ $onlineTotals['others']['student'] }}</td><td class="text-center">{{ $onlineTotals['others']['faculty'] }}</td><td class="text-center">{{ $onlineTotals['others']['admin'] }}</td><td class="text-center">{{ $onlineTotals['others']['dependent'] }}</td><td class="text-center">{{ array_sum($onlineTotals['others']) }}</td></tr>

            <tr class="section-row"><td colspan="6">vii. Triage Survey</td></tr>
            <tr class="detail-row nested-row"><td>A. Online</td><td class="text-center">0</td><td class="text-center">0</td><td class="text-center">0</td><td class="text-center">0</td><td class="text-center">0</td></tr>

            <tr class="section-row"><td colspan="6">viii. Bulletin Updates</td></tr>
            <tr><td class="nested-row">Bulletin Updates</td><td class="text-center">0</td><td class="text-center">0</td><td class="text-center">0</td><td class="text-center">0</td><td class="text-center">0</td></tr>
        </tbody>
    </table>
    </div>

    <div class="report-panel" id="mar-gad-consultation">
    <div class="table-panel-title">GAD Consultation</div>
    <table class="gad-table">
        <thead>
            <tr>
                <th>GAD (Consultation)</th>
                <th style="text-align: center;">Students</th>
                <th style="text-align: center;">Faculty</th>
                <th style="text-align: center;">Admin</th>
                <th style="text-align: center;">Dependent</th>
                <th style="text-align: center;">Total</th>
            </tr>
        </thead>
        <tbody>
            <tr class="gad-section-row"><td colspan="6">GAD Summary</td></tr>
            <tr><td>Female</td><td class="text-center">{{ $consultationGad['female']['student'] ?? 0 }}</td><td class="text-center">{{ $consultationGad['female']['faculty'] ?? 0 }}</td><td class="text-center">{{ $consultationGad['female']['admin'] ?? 0 }}</td><td class="text-center">{{ $consultationGad['female']['dependent'] ?? 0 }}</td><td class="text-center">{{ $consultationGad['female']['total'] ?? 0 }}</td></tr>
            <tr><td>Male</td><td class="text-center">{{ $consultationGad['male']['student'] ?? 0 }}</td><td class="text-center">{{ $consultationGad['male']['faculty'] ?? 0 }}</td><td class="text-center">{{ $consultationGad['male']['admin'] ?? 0 }}</td><td class="text-center">{{ $consultationGad['male']['dependent'] ?? 0 }}</td><td class="text-center">{{ $consultationGad['male']['total'] ?? 0 }}</td></tr>
            <tr class="subsection-row"><td colspan="6">PWD</td></tr>
            <tr><td style="padding-left: 30px;">Male</td><td class="text-center">{{ $consultationGad['pwd_male']['student'] ?? 0 }}</td><td class="text-center">{{ $consultationGad['pwd_male']['faculty'] ?? 0 }}</td><td class="text-center">{{ $consultationGad['pwd_male']['admin'] ?? 0 }}</td><td class="text-center">{{ $consultationGad['pwd_male']['dependent'] ?? 0 }}</td><td class="text-center">{{ $consultationGad['pwd_male']['total'] ?? 0 }}</td></tr>
            <tr><td style="padding-left: 30px;">Female</td><td class="text-center">{{ $consultationGad['pwd_female']['student'] ?? 0 }}</td><td class="text-center">{{ $consultationGad['pwd_female']['faculty'] ?? 0 }}</td><td class="text-center">{{ $consultationGad['pwd_female']['admin'] ?? 0 }}</td><td class="text-center">{{ $consultationGad['pwd_female']['dependent'] ?? 0 }}</td><td class="text-center">{{ $consultationGad['pwd_female']['total'] ?? 0 }}</td></tr>
            <tr class="subsection-row"><td colspan="6">Senior</td></tr>
            <tr><td style="padding-left: 30px;">Male</td><td class="text-center">{{ $consultationGad['senior_male']['student'] ?? 0 }}</td><td class="text-center">{{ $consultationGad['senior_male']['faculty'] ?? 0 }}</td><td class="text-center">{{ $consultationGad['senior_male']['admin'] ?? 0 }}</td><td class="text-center">{{ $consultationGad['senior_male']['dependent'] ?? 0 }}</td><td class="text-center">{{ $consultationGad['senior_male']['total'] ?? 0 }}</td></tr>
            <tr><td style="padding-left: 30px;">Female</td><td class="text-center">{{ $consultationGad['senior_female']['student'] ?? 0 }}</td><td class="text-center">{{ $consultationGad['senior_female']['faculty'] ?? 0 }}</td><td class="text-center">{{ $consultationGad['senior_female']['admin'] ?? 0 }}</td><td class="text-center">{{ $consultationGad['senior_female']['dependent'] ?? 0 }}</td><td class="text-center">{{ $consultationGad['senior_female']['total'] ?? 0 }}</td></tr>
            <tr class="subsection-row"><td>Total</td><td class="text-center">{{ $consultationGad['total']['student'] ?? 0 }}</td><td class="text-center">{{ $consultationGad['total']['faculty'] ?? 0 }}</td><td class="text-center">{{ $consultationGad['total']['admin'] ?? 0 }}</td><td class="text-center">{{ $consultationGad['total']['dependent'] ?? 0 }}</td><td class="text-center">{{ $consultationGad['total']['total'] ?? 0 }}</td></tr>
        </tbody>
    </table>
    </div>

    <div class="report-panel" id="mar-gad-certificate">
    <div class="table-panel-title">GAD Certificate</div>
    <table class="gad-table">
        <thead>
            <tr>
                <th>GAD (OJT/Medical Compliance/Medical for Promotion/Excused Letter)</th>
                <th style="text-align: center;">Students</th>
                <th style="text-align: center;">Faculty</th>
                <th style="text-align: center;">Admin</th>
                <th style="text-align: center;">Dependent</th>
                <th style="text-align: center;">Total</th>
            </tr>
        </thead>
        <tbody>
            <tr class="gad-section-row"><td colspan="6">GAD Summary</td></tr>
            <tr><td>Female</td><td class="text-center">{{ $certificateGad['female']['student'] ?? 0 }}</td><td class="text-center">{{ $certificateGad['female']['faculty'] ?? 0 }}</td><td class="text-center">{{ $certificateGad['female']['admin'] ?? 0 }}</td><td class="text-center">{{ $certificateGad['female']['dependent'] ?? 0 }}</td><td class="text-center">{{ $certificateGad['female']['total'] ?? 0 }}</td></tr>
            <tr><td>Male</td><td class="text-center">{{ $certificateGad['male']['student'] ?? 0 }}</td><td class="text-center">{{ $certificateGad['male']['faculty'] ?? 0 }}</td><td class="text-center">{{ $certificateGad['male']['admin'] ?? 0 }}</td><td class="text-center">{{ $certificateGad['male']['dependent'] ?? 0 }}</td><td class="text-center">{{ $certificateGad['male']['total'] ?? 0 }}</td></tr>
            <tr class="subsection-row"><td colspan="6">PWD</td></tr>
            <tr><td style="padding-left: 30px;">Male</td><td class="text-center">{{ $certificateGad['pwd_male']['student'] ?? 0 }}</td><td class="text-center">{{ $certificateGad['pwd_male']['faculty'] ?? 0 }}</td><td class="text-center">{{ $certificateGad['pwd_male']['admin'] ?? 0 }}</td><td class="text-center">{{ $certificateGad['pwd_male']['dependent'] ?? 0 }}</td><td class="text-center">{{ $certificateGad['pwd_male']['total'] ?? 0 }}</td></tr>
            <tr><td style="padding-left: 30px;">Female</td><td class="text-center">{{ $certificateGad['pwd_female']['student'] ?? 0 }}</td><td class="text-center">{{ $certificateGad['pwd_female']['faculty'] ?? 0 }}</td><td class="text-center">{{ $certificateGad['pwd_female']['admin'] ?? 0 }}</td><td class="text-center">{{ $certificateGad['pwd_female']['dependent'] ?? 0 }}</td><td class="text-center">{{ $certificateGad['pwd_female']['total'] ?? 0 }}</td></tr>
            <tr class="subsection-row"><td colspan="6">Senior</td></tr>
            <tr><td style="padding-left: 30px;">Male</td><td class="text-center">{{ $certificateGad['senior_male']['student'] ?? 0 }}</td><td class="text-center">{{ $certificateGad['senior_male']['faculty'] ?? 0 }}</td><td class="text-center">{{ $certificateGad['senior_male']['admin'] ?? 0 }}</td><td class="text-center">{{ $certificateGad['senior_male']['dependent'] ?? 0 }}</td><td class="text-center">{{ $certificateGad['senior_male']['total'] ?? 0 }}</td></tr>
            <tr><td style="padding-left: 30px;">Female</td><td class="text-center">{{ $certificateGad['senior_female']['student'] ?? 0 }}</td><td class="text-center">{{ $certificateGad['senior_female']['faculty'] ?? 0 }}</td><td class="text-center">{{ $certificateGad['senior_female']['admin'] ?? 0 }}</td><td class="text-center">{{ $certificateGad['senior_female']['dependent'] ?? 0 }}</td><td class="text-center">{{ $certificateGad['senior_female']['total'] ?? 0 }}</td></tr>
            <tr class="subsection-row"><td>Total</td><td class="text-center">{{ $certificateGad['total']['student'] ?? 0 }}</td><td class="text-center">{{ $certificateGad['total']['faculty'] ?? 0 }}</td><td class="text-center">{{ $certificateGad['total']['admin'] ?? 0 }}</td><td class="text-center">{{ $certificateGad['total']['dependent'] ?? 0 }}</td><td class="text-center">{{ $certificateGad['total']['total'] ?? 0 }}</td></tr>
        </tbody>
    </table>
    </div>

    <div class="report-panel" id="mar-gad-triage">
    <div class="table-panel-title">GAD Triage Online</div>
    <table class="gad-table">
        <thead>
            <tr>
                <th>GAD (Triage Online)</th>
                <th style="text-align: center;">Students</th>
                <th style="text-align: center;">Faculty</th>
                <th style="text-align: center;">Admin</th>
                <th style="text-align: center;">Dependent</th>
                <th style="text-align: center;">Total</th>
            </tr>
        </thead>
        <tbody>
            <tr class="gad-section-row"><td colspan="6">GAD Summary</td></tr>
            <tr><td>Female</td><td class="text-center">{{ $triageOnlineGad['female']['student'] ?? 0 }}</td><td class="text-center">{{ $triageOnlineGad['female']['faculty'] ?? 0 }}</td><td class="text-center">{{ $triageOnlineGad['female']['admin'] ?? 0 }}</td><td class="text-center">{{ $triageOnlineGad['female']['dependent'] ?? 0 }}</td><td class="text-center">{{ $triageOnlineGad['female']['total'] ?? 0 }}</td></tr>
            <tr><td>Male</td><td class="text-center">{{ $triageOnlineGad['male']['student'] ?? 0 }}</td><td class="text-center">{{ $triageOnlineGad['male']['faculty'] ?? 0 }}</td><td class="text-center">{{ $triageOnlineGad['male']['admin'] ?? 0 }}</td><td class="text-center">{{ $triageOnlineGad['male']['dependent'] ?? 0 }}</td><td class="text-center">{{ $triageOnlineGad['male']['total'] ?? 0 }}</td></tr>
            <tr class="subsection-row"><td colspan="6">PWD</td></tr>
            <tr><td style="padding-left: 30px;">Male</td><td class="text-center">{{ $triageOnlineGad['pwd_male']['student'] ?? 0 }}</td><td class="text-center">{{ $triageOnlineGad['pwd_male']['faculty'] ?? 0 }}</td><td class="text-center">{{ $triageOnlineGad['pwd_male']['admin'] ?? 0 }}</td><td class="text-center">{{ $triageOnlineGad['pwd_male']['dependent'] ?? 0 }}</td><td class="text-center">{{ $triageOnlineGad['pwd_male']['total'] ?? 0 }}</td></tr>
            <tr><td style="padding-left: 30px;">Female</td><td class="text-center">{{ $triageOnlineGad['pwd_female']['student'] ?? 0 }}</td><td class="text-center">{{ $triageOnlineGad['pwd_female']['faculty'] ?? 0 }}</td><td class="text-center">{{ $triageOnlineGad['pwd_female']['admin'] ?? 0 }}</td><td class="text-center">{{ $triageOnlineGad['pwd_female']['dependent'] ?? 0 }}</td><td class="text-center">{{ $triageOnlineGad['pwd_female']['total'] ?? 0 }}</td></tr>
            <tr class="subsection-row"><td colspan="6">Senior</td></tr>
            <tr><td style="padding-left: 30px;">Male</td><td class="text-center">{{ $triageOnlineGad['senior_male']['student'] ?? 0 }}</td><td class="text-center">{{ $triageOnlineGad['senior_male']['faculty'] ?? 0 }}</td><td class="text-center">{{ $triageOnlineGad['senior_male']['admin'] ?? 0 }}</td><td class="text-center">{{ $triageOnlineGad['senior_male']['dependent'] ?? 0 }}</td><td class="text-center">{{ $triageOnlineGad['senior_male']['total'] ?? 0 }}</td></tr>
            <tr><td style="padding-left: 30px;">Female</td><td class="text-center">{{ $triageOnlineGad['senior_female']['student'] ?? 0 }}</td><td class="text-center">{{ $triageOnlineGad['senior_female']['faculty'] ?? 0 }}</td><td class="text-center">{{ $triageOnlineGad['senior_female']['admin'] ?? 0 }}</td><td class="text-center">{{ $triageOnlineGad['senior_female']['dependent'] ?? 0 }}</td><td class="text-center">{{ $triageOnlineGad['senior_female']['total'] ?? 0 }}</td></tr>
            <tr class="subsection-row"><td>Total</td><td class="text-center">{{ $triageOnlineGad['total']['student'] ?? 0 }}</td><td class="text-center">{{ $triageOnlineGad['total']['faculty'] ?? 0 }}</td><td class="text-center">{{ $triageOnlineGad['total']['admin'] ?? 0 }}</td><td class="text-center">{{ $triageOnlineGad['total']['dependent'] ?? 0 }}</td><td class="text-center">{{ $triageOnlineGad['total']['total'] ?? 0 }}</td></tr>
        </tbody>
    </table>
    </div>

    <div class="report-panel" id="mar-gad-combined">
    <div class="table-panel-title">GAD Combined</div>
    <table class="gad-table">
        <thead>
            <tr>
                <th>GAD (Consultation + OJT/Medical Compliance/Medical for Promotion + Triage)</th>
                <th style="text-align: center;">Students</th>
                <th style="text-align: center;">Faculty</th>
                <th style="text-align: center;">Admin</th>
                <th style="text-align: center;">Dependent</th>
                <th style="text-align: center;">Total</th>
            </tr>
        </thead>
        <tbody>
            <tr class="gad-section-row"><td colspan="6">GAD Summary</td></tr>
            <tr><td>Female</td><td class="text-center">{{ $combinedGad['female']['student'] ?? 0 }}</td><td class="text-center">{{ $combinedGad['female']['faculty'] ?? 0 }}</td><td class="text-center">{{ $combinedGad['female']['admin'] ?? 0 }}</td><td class="text-center">{{ $combinedGad['female']['dependent'] ?? 0 }}</td><td class="text-center">{{ $combinedGad['female']['total'] ?? 0 }}</td></tr>
            <tr><td>Male</td><td class="text-center">{{ $combinedGad['male']['student'] ?? 0 }}</td><td class="text-center">{{ $combinedGad['male']['faculty'] ?? 0 }}</td><td class="text-center">{{ $combinedGad['male']['admin'] ?? 0 }}</td><td class="text-center">{{ $combinedGad['male']['dependent'] ?? 0 }}</td><td class="text-center">{{ $combinedGad['male']['total'] ?? 0 }}</td></tr>
            <tr class="subsection-row"><td colspan="6">PWD</td></tr>
            <tr><td style="padding-left: 30px;">Male</td><td class="text-center">{{ $combinedGad['pwd_male']['student'] ?? 0 }}</td><td class="text-center">{{ $combinedGad['pwd_male']['faculty'] ?? 0 }}</td><td class="text-center">{{ $combinedGad['pwd_male']['admin'] ?? 0 }}</td><td class="text-center">{{ $combinedGad['pwd_male']['dependent'] ?? 0 }}</td><td class="text-center">{{ $combinedGad['pwd_male']['total'] ?? 0 }}</td></tr>
            <tr><td style="padding-left: 30px;">Female</td><td class="text-center">{{ $combinedGad['pwd_female']['student'] ?? 0 }}</td><td class="text-center">{{ $combinedGad['pwd_female']['faculty'] ?? 0 }}</td><td class="text-center">{{ $combinedGad['pwd_female']['admin'] ?? 0 }}</td><td class="text-center">{{ $combinedGad['pwd_female']['dependent'] ?? 0 }}</td><td class="text-center">{{ $combinedGad['pwd_female']['total'] ?? 0 }}</td></tr>
            <tr class="subsection-row"><td colspan="6">Senior</td></tr>
            <tr><td style="padding-left: 30px;">Male</td><td class="text-center">{{ $combinedGad['senior_male']['student'] ?? 0 }}</td><td class="text-center">{{ $combinedGad['senior_male']['faculty'] ?? 0 }}</td><td class="text-center">{{ $combinedGad['senior_male']['admin'] ?? 0 }}</td><td class="text-center">{{ $combinedGad['senior_male']['dependent'] ?? 0 }}</td><td class="text-center">{{ $combinedGad['senior_male']['total'] ?? 0 }}</td></tr>
            <tr><td style="padding-left: 30px;">Female</td><td class="text-center">{{ $combinedGad['senior_female']['student'] ?? 0 }}</td><td class="text-center">{{ $combinedGad['senior_female']['faculty'] ?? 0 }}</td><td class="text-center">{{ $combinedGad['senior_female']['admin'] ?? 0 }}</td><td class="text-center">{{ $combinedGad['senior_female']['dependent'] ?? 0 }}</td><td class="text-center">{{ $combinedGad['senior_female']['total'] ?? 0 }}</td></tr>
            <tr class="subsection-row"><td>Total</td><td class="text-center">{{ $combinedGad['total']['student'] ?? 0 }}</td><td class="text-center">{{ $combinedGad['total']['faculty'] ?? 0 }}</td><td class="text-center">{{ $combinedGad['total']['admin'] ?? 0 }}</td><td class="text-center">{{ $combinedGad['total']['dependent'] ?? 0 }}</td><td class="text-center">{{ $combinedGad['total']['total'] ?? 0 }}</td></tr>
        </tbody>
    </table>
    </div>
</div>



@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
    const switcher = document.getElementById('marReportSwitcher');
    if (!switcher) return;

    const buttons = Array.from(switcher.querySelectorAll('.report-switch-btn'));
    const panels = Array.from(document.querySelectorAll('.report-panel'));

    const activatePanel = function (targetId) {
        buttons.forEach(function (button) {
            button.classList.toggle('is-active', button.dataset.target === targetId);
        });

        panels.forEach(function (panel) {
            panel.classList.toggle('is-active', panel.id === targetId);
        });
    };

    buttons.forEach(function (button) {
        button.addEventListener('click', function () {
            activatePanel(button.dataset.target);
        });
    });
});
</script>
@endpush
