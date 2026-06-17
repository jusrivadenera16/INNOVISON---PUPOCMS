<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $title }} - {{ $monthFilter }}</title>

    <style>
    /* 1. Print & Base Styles */
    @page {
        margin: {{ in_array($type, ['inventory', 'mar'], true) ? '24px 24px 72px' : '115px 28px 85px' }};
    }

    @media print {
        .no-print { display: none !important; }
        body { margin: 0; padding: 0; }
        .page-header,
        .page-footer {
            position: fixed;
            left: 0;
            right: 0;
        }
        .page-header {
            top: -95px;
        }
        .page-footer {
            bottom: -65px;
        }
        .report-shell {
            margin: 0;
        }
    }
    
    body { 
        font-family: 'Arial', sans-serif; 
        color: #000; 
        line-height: 1.2; 
        margin: 40px; 
    }

    .pdf-mode .no-print {
        display: none !important;
    }

    .report-shell {
        position: relative;
    }

    .page-header {
        display: none;
        border-bottom: 2px solid #800000;
        padding: 0 28px 10px;
        background: #fff;
    }

    .page-header-inner {
        display: flex;
        justify-content: space-between;
        align-items: center;
        gap: 16px;
    }

    .page-header-left {
        display: flex;
        align-items: center;
        gap: 12px;
    }

    .page-header-left img {
        width: 44px;
        height: auto;
    }

    .page-header-copy {
        line-height: 1.15;
    }

    .page-header-copy strong {
        display: block;
        font-size: 14px;
        color: #800000;
        letter-spacing: 0.35px;
    }

    .page-header-copy span {
        display: block;
        font-size: 10px;
        color: #374151;
        text-transform: uppercase;
        font-weight: 700;
    }

    .page-header-meta {
        font-size: 10px;
        text-align: right;
        line-height: 1.3;
    }

    /* 2. Header & Logo Layout */
    .header-top {
        width: 100%;
        display: table;
        table-layout: fixed;
        margin-bottom: 18px;
    }

    .pup-logo-section {
        display: table-cell;
        width: 110px;
        vertical-align: middle;
        text-align: left;
    }

    .pup-logo-section img {
        width: 62px;
        height: auto;
        display: inline-block;
    }

    .report-header-center {
        display: table-cell;
        vertical-align: middle;
        text-align: center;
        line-height: 1.15;
    }

    .report-header-center .university {
        display: block;
        font-size: 12px;
        font-weight: 800;
        letter-spacing: 0.08em;
        text-transform: uppercase;
        color: #111827;
    }

    .report-header-center .department,
    .report-header-center .campus {
        display: block;
        font-size: 10px;
        font-weight: 700;
        color: #374151;
    }

    .logo-text-box {
        text-align: left;
        line-height: 1.1;
    }

    .logo-text-box .title {
        font-weight: bold;
        font-size: 18px;
        color: #800000;
        letter-spacing: 0.5px;
    }

    .logo-text-box .sub {
        font-size: 11px;
        color: #333;
        text-transform: uppercase;
        font-weight: 600;
    }

    .bp-logo {
        display: table-cell;
        width: 110px;
        vertical-align: middle;
        text-align: right;
    }

    .bp-logo img {
        width: 74px;
        height: auto;
        object-fit: contain;
        display: inline-block;
    }

    /* 3. Report Info & Titles */
    .report-main-title { 
        text-align: center; 
        margin: 25px 0; 
        font-weight: bold; 
        font-size: 18px; 
        text-transform: uppercase;
        text-decoration: underline;
    }
            
    .info-section { 
        display: flex; 
        justify-content: space-between; 
        margin-bottom: 25px; 
        font-size: 13px; 
    }

    .info-left, .info-right { 
        width: 48%; 
    }

    .info-row { 
        margin-bottom: 8px; 
        display: flex; 
        align-items: flex-end;
    }

    .label { 
        font-weight: bold; 
        width: 130px; 
    }

    .value { 
        border-bottom: 1px solid #000; 
        flex-grow: 1; 
        padding-left: 8px; 
        padding-bottom: 2px;
    }

    /* 4. Table Customization */
    table { 
        width: 100%; 
        table-layout: fixed;
        border-collapse: collapse; 
        margin-top: 15px; 
        background: #fff; 
    }

    th, td { 
        border: 1px solid #000; 
        padding: 10px 6px; 
        font-size: 11px; 
        text-align: left; 
        overflow-wrap: anywhere;
        word-break: break-word;
        vertical-align: top;
    }

    th { 
        background-color: #f2f2f2; 
        font-weight: bold; 
        text-transform: uppercase;
        text-align: center;
    }

    .text-left { text-align: left; }
    .mar-report-table col:first-child {
        width: 38%;
    }

    .mar-report-table col.metric-col {
        width: 12.4%;
    }

    .bg-category { 
        background-color: #f9f9f9; 
        font-weight: bold; 
        text-align: left; 
        padding-left: 10px;
    }

    .inventory-group-row td {
        background-color: #f9f9f9;
        font-weight: bold;
        text-transform: uppercase;
    }

    .inventory-group-label {
        color: #800000;
        letter-spacing: 0.04em;
        text-align: left;
    }

    .inventory-group-spacer {
        background-color: #f9f9f9;
    }

    /* 5. Signatures & Footer */
    .footer-signatures { 
        margin-top: 50px; 
        display: flex; 
        justify-content: space-between; 
    }

    .sig-box { 
        width: 250px; 
        text-align: center; 
    }

    .sig-line { 
        border-top: 1px solid #000; 
        margin-top: 45px; 
        font-weight: bold; 
        padding-top: 5px; 
        text-transform: uppercase; 
        font-size: 12px; 
    }

    .official-footer { 
        border-top: 2px solid #800000; 
        margin-top: 60px; 
        padding-top: 12px; 
        font-size: 10px; 
        color: #333; 
    }

    .footer-details p { 
        margin: 2px 0; 
    }

    .footer-motto { 
        text-align: center; 
        font-weight: bold; 
        font-size: 15px; 
        margin-top: 15px; 
        text-transform: uppercase;
        color: #000;
    }

    .generated-report-caption {
        border-top: 1px solid #9ca3af;
        margin-top: 14px;
        padding-top: 7px;
        font-size: 10px;
        line-height: 1.35;
    }

    .generated-report-caption .signature-note {
        text-align: right;
        color: #4b5563;
        font-weight: 700;
        margin-bottom: 5px;
    }

    .generated-report-caption .privacy-note {
        text-align: center;
        color: #9f4b5a;
        font-weight: 800;
    }

    .page-footer {
        display: none;
        padding: 7px 28px 0;
        background: #fff;
        border-top: 2px solid #800000;
        font-size: 9px;
        color: #374151;
    }

    .page-footer-inner {
        display: flex;
        justify-content: space-between;
        align-items: center;
        gap: 12px;
    }

    .page-footer-copy {
        line-height: 1.3;
    }

    .page-footer-motto {
        font-weight: 800;
        text-transform: uppercase;
        text-align: right;
        white-space: nowrap;
    }

    .page-footer-generated {
        border-top: 1px solid #9ca3af;
        margin-top: 6px;
        padding-top: 4px;
        font-size: 8.5px;
        line-height: 1.25;
    }

    .page-footer-generated .signature-note {
        text-align: right;
        color: #4b5563;
        font-weight: 700;
    }

    .page-footer-generated .privacy-note {
        text-align: center;
        color: #9f4b5a;
        font-weight: 800;
    }

    @media print {
        .page-header,
        .page-footer {
            display: block;
        }
    }

    /* 6. UI Components (No-Print) */
    .no-print-bar { 
        background: #1e293b; 
        color: white; 
        padding: 15px 25px; 
        display: flex; 
        justify-content: space-between; 
        align-items: center;
        border-radius: 8px; 
        margin-bottom: 25px; 
    }

    .btn-print { 
        background: #ef4444; 
        color: white; 
        border: none; 
        padding: 10px 20px; 
        cursor: pointer; 
        border-radius: 6px; 
        font-weight: bold; 
        transition: background 0.3s;
    }

    .btn-print:hover {
        background: #dc2626;
    }

    .pdf-warning {
        margin: 0 0 14px;
        padding: 12px 14px;
        border-radius: 10px;
        background: #fff4d6;
        border: 1px solid #facc15;
        color: #7c2d12;
        font-size: 13px;
        line-height: 1.45;
    }

    .official-form-report .page-header,
    .official-form-report .page-footer {
        display: none !important;
    }

    body.pdf-mode.official-form-report {
        margin: 0;
    }

    .official-inventory-report {
        font-family: Arial, Helvetica, sans-serif;
        color: #000;
    }

    .official-inventory-page-footer {
        position: fixed;
        right: 0;
        bottom: -58px;
        left: 0;
        border-top: 1.5px solid #000;
        padding-top: 5px;
        font-family: Arial, Helvetica, sans-serif;
        color: #000;
        text-align: center;
    }

    .official-inventory-footer-contact {
        margin: 0;
        font-size: 7px;
        line-height: 1.3;
    }

    .official-inventory-footer-motto {
        margin: 4px 0 0;
        font-size: 7px;
        font-weight: 700;
        letter-spacing: .35px;
        text-transform: uppercase;
    }

    .official-inventory-header {
        position: relative;
        min-height: 118px;
        padding: 4px 92px 12px 76px;
        border-bottom: 1.5px solid #000;
        text-align: center;
    }

    .official-inventory-logo {
        position: absolute;
        top: 2px;
        left: 4px;
        width: 58px;
        height: 58px;
        object-fit: contain;
    }

    .official-inventory-government-logo {
        position: absolute;
        top: 8px;
        right: 0;
        width: 86px;
        height: 58px;
        object-fit: contain;
    }

    .official-inventory-university {
        margin: 0;
        font-family: "Times New Roman", serif;
        font-size: 12px;
        font-weight: 700;
        letter-spacing: 0;
        text-transform: uppercase;
    }

    .official-inventory-office {
        margin: 2px 0 0;
        font-family: "Times New Roman", serif;
        font-size: 12px;
        font-weight: 700;
    }

    .official-inventory-campus {
        margin: 1px 0 8px;
        font-size: 11px;
        font-weight: 700;
    }

    .official-inventory-header-title {
        margin: 0;
        font-size: 13px;
        font-weight: 700;
        text-transform: uppercase;
    }

    .official-inventory-header-date {
        margin: 3px 0 0;
        font-size: 10px;
        font-weight: 700;
    }

    .official-inventory-form-code {
        position: absolute;
        right: 0;
        bottom: -13px;
        font-size: 6.5px;
        white-space: nowrap;
    }

    .official-inventory-meta {
        width: 100%;
        margin: 24px 0 10px;
        border-collapse: collapse;
        table-layout: fixed;
    }

    .official-inventory-meta td {
        border: 0;
        padding: 2px 4px;
        font-size: 9px;
        vertical-align: bottom;
    }

    .official-inventory-meta .meta-label {
        display: inline-block;
        min-width: 55px;
        font-weight: 700;
        text-transform: uppercase;
    }

    .official-inventory-meta .meta-value {
        display: inline-block;
        min-width: 150px;
        border-bottom: 1px solid #000;
        padding: 0 4px 1px;
        font-weight: 700;
        text-transform: uppercase;
    }

    .official-inventory-table {
        width: 100%;
        margin: 0;
        border-collapse: collapse;
        table-layout: fixed;
    }

    .official-inventory-table thead {
        display: table-header-group;
    }

    .official-inventory-table tr {
        page-break-inside: avoid;
    }

    .official-inventory-table th,
    .official-inventory-table td {
        border: 1px solid #000;
        padding: 3px 3px;
        font-size: 7.5px;
        line-height: 1.2;
        vertical-align: middle;
    }

    .official-inventory-table th {
        height: 31px;
        background: #fff;
        color: #000;
        font-size: 7px;
        font-weight: 700;
        text-align: center;
    }

    .official-inventory-table .inventory-category-row td {
        height: 17px;
        background: #fff;
        font-size: 8px;
        font-weight: 700;
        text-align: center;
        text-transform: uppercase;
    }

    .official-inventory-table .number-cell,
    .official-inventory-table .date-cell,
    .official-inventory-table .unit-cell {
        text-align: center;
    }

    .official-inventory-table .item-cell {
        text-align: left;
    }

    .official-inventory-signatures {
        width: 100%;
        margin-top: 25px;
        border-collapse: collapse;
        table-layout: fixed;
    }

    .official-inventory-signatures td {
        width: 50%;
        border: 0;
        padding: 0 28px;
        font-size: 8px;
        vertical-align: top;
    }

    .official-inventory-signature-space {
        height: 30px;
    }

    .official-inventory-signature-name {
        border-bottom: 1px solid #000;
        padding-bottom: 2px;
        font-size: 9px;
        font-weight: 700;
        text-align: center;
        text-transform: uppercase;
    }

    .official-inventory-signature-role {
        margin-top: 2px;
        text-align: center;
    }

    .mar-service-table tbody td:last-child:not(.bg-category) {
        color: transparent;
    }

    @media print {
        body.official-form-report {
            margin: 0;
        }

        .official-inventory-report {
            margin: 0;
        }
    }
</style>
</head>
<body class="{{ !empty($isPdf) ? 'pdf-mode' : '' }} {{ in_array($type, ['inventory', 'mar'], true) ? 'official-form-report' : '' }}">

    <div class="page-header" aria-hidden="true">
        <div class="page-header-inner">
            <div class="page-header-left">
                <img src="{{ asset('images/pup_logo.png') }}" alt="PUP Logo">
                <div class="page-header-copy">
                    <strong>PUP TAGUIG</strong>
                    <span>{{ strtoupper($title) }}</span>
                </div>
            </div>
            <div class="page-header-meta">
                <div>Medical Services Unit</div>
                <div>{{ date('F d, Y') }}</div>
            </div>
        </div>
    </div>

    <div class="page-footer" aria-hidden="true">
        <div class="page-footer-inner">
            <div class="page-footer-copy">
                General Santos Avenue, Lower Bicutan, Taguig City, Philippines 1632
                <br>Direct Line (02) 8837 5658 to 60 | Website: www.pup.edu.ph | Email: taguig@pup.edu.ph
            </div>
            <div class="page-footer-motto">The Country's First PolytechnicU</div>
        </div>
        <div class="page-footer-generated">
            <div class="signature-note">This is system-generated, signature is not required.</div>
            <div class="privacy-note">
                This document contains personal-identifiable information that is subject to Data Privacy.<br>
                Please keep this document protected and in a safe place.
            </div>
        </div>
    </div>

    <div class="report-shell">
    <div class="no-print no-print-bar">
        <span><strong>Preview Mode:</strong> {{ $title }}</span>
        <div>
            <button onclick="window.print()" class="btn-print">Print / Save as PDF</button>
            <button onclick="window.close()" style="padding: 8px 15px; cursor: pointer;">Close</button>
        </div>
    </div>

    @if(!empty($pdfUnavailable))
        <div class="no-print pdf-warning">
            PDF export is not available on this server yet, so this report opened in the HTML preview instead.
        </div>
    @endif

    @if(!in_array($type, ['inventory', 'mar'], true))
        <div class="header-top">
            <div class="pup-logo-section">
                <img src="{{ asset('images/pup_logo.png') }}" alt="PUP Logo">
            </div>
            <div class="report-header-center">
                <span class="university">POLYTECHNIC UNIVERSITY OF THE PHILIPPINES</span>
                <span class="department">Medical Services Department</span>
                <span class="campus">Taguig Campus</span>
            </div>
            <div class="bp-logo">
                <img src="{{ asset('images/Bagong_Pilipinas_logo.png') }}" alt="Bagong Pilipinas Logo">
            </div>
        </div>

        <div class="report-main-title">
        @if($type == 'mar')
            MEDICAL ACCOMPLISHMENT REPORT
        @elseif($type == 'appointment')
            APPOINTMENT REPORT
        @else
            ACCOMPLISHMENT REPORT as of {{ date('F d, Y') }}
        @endif
        </div>

        <div class="info-section">
            <div class="info-left">
                <div class="info-row"><span class="label">Name:</span> <span class="value">Nurse Joyce</span></div>
                <div class="info-row"><span class="label">Position:</span> <span class="value">Nurse</span></div>
            </div>
            <div class="info-right">
                <div class="info-row"><span class="label">Date of Submission:</span> <span class="value">{{ date('m/d/Y') }}</span></div>
                <div class="info-row"><span class="label">Unit/Department:</span> <span class="value">Medical Services Unit</span></div>
            </div>
        </div>
    @endif

    @if($type == 'mar')
        @php
            $consultationGad = $gadTables['consultation'] ?? [];
            $certificateGad = $gadTables['certificate'] ?? [];
            $triageOnlineGad = $gadTables['triage_online'] ?? [];
            $combinedGad = $gadTables['combined'] ?? [];
            $marMonthStart = \Carbon\Carbon::parse(($monthFilter ?? now()->format('Y-m')) . '-01')->startOfMonth();
            $marReportAsOf = $marMonthStart->isCurrentMonth() ? now() : $marMonthStart->endOfMonth();
            $marPreparedBy = auth('admin')->user() ?? auth()->user();
            $marPreparedByName = trim((string) optional($marPreparedBy)->name) ?: 'CLINIC STAFF';
            $marPreparedByPosition = \App\Models\User::normalizeRole(optional($marPreparedBy)->user_role) === \App\Models\User::ROLE_ADMIN
                ? 'Nurse / Clinic Staff'
                : 'Clinic Staff';
        @endphp

        <footer class="official-inventory-page-footer">
            <p class="official-inventory-footer-contact">
                Gen. Santos Avenue, Lower Bicutan, Taguig City 1632<br>
                Direct Line: (02) 8837 5858 to 60 | Email: taguig@pup.edu.ph<br>
                Website: www.pup.edu.ph | Inquiries: https://bit.ly/PUPSINTA
            </p>
            <p class="official-inventory-footer-motto">A Leading Comprehensive Polytechnic University in Asia</p>
        </footer>

        <section class="official-inventory-report">
            <header class="official-inventory-header">
                <img class="official-inventory-logo" src="{{ asset('images/pup_logo.png') }}" alt="PUP Logo">
                <img class="official-inventory-government-logo" src="{{ asset('images/Bagong_Pilipinas_logo.png') }}" alt="Bagong Pilipinas Logo">
                <p class="official-inventory-university">Polytechnic University of the Philippines</p>
                <p class="official-inventory-office">Medical Services Department</p>
                <p class="official-inventory-campus">Taguig Campus</p>
                <h1 class="official-inventory-header-title">Monthly Accomplishment Report</h1>
                <p class="official-inventory-header-date">As of {{ $marReportAsOf->format('F d, Y') }}</p>
                <span class="official-inventory-form-code">PUP-IRDM-6-MEDS-030 Rev.0 July 11, 2024</span>
            </header>

            <table class="official-inventory-meta">
                <tr>
                    <td>
                        <span class="meta-label">Name:</span>
                        <span class="meta-value">{{ $marPreparedByName }}</span>
                    </td>
                    <td>
                        <span class="meta-label">Date of Submission:</span>
                        <span class="meta-value">{{ $marReportAsOf->format('F d, Y') }}</span>
                    </td>
                </tr>
                <tr>
                    <td>
                        <span class="meta-label">Position:</span>
                        <span class="meta-value">{{ $marPreparedByPosition }}</span>
                    </td>
                    <td>
                        <span class="meta-label">Unit / Department:</span>
                        <span class="meta-value">Taguig Campus</span>
                    </td>
                </tr>
            </table>

        <table class="mar-report-table mar-service-table">
            <colgroup>
                <col>
                <col class="metric-col">
                <col class="metric-col">
                <col class="metric-col">
                <col class="metric-col">
                <col class="metric-col">
            </colgroup>
            <thead>
                <tr>
                    <th>MEDICAL SERVICE RENDERED</th>
                    <th>STUDENTS</th>
                    <th>FACULTY</th>
                    <th>ADMIN</th>
                    <th>DEPENDENTS</th>
                    <th>REMARKS</th>
                </tr>
            </thead>
            <tbody>
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
                    $excusedLetterTotals = ['student' => 0, 'faculty' => 0, 'admin' => 0, 'dependent' => 0];
                    $cocIjtTotals = ['student' => 0, 'faculty' => 0, 'admin' => 0, 'dependent' => 0];
                    $cocLadderizedTotals = ['student' => 0, 'faculty' => 0, 'admin' => 0, 'dependent' => 0];
                    $excusedLetterCategoryRows = collect();
                    $onlineTotals = [
                        'consultation' => ['student' => 0, 'faculty' => 0, 'admin' => 0, 'dependent' => 0],
                        'medical_clearance' => ['student' => 0, 'faculty' => 0, 'admin' => 0, 'dependent' => 0],
                        'others' => ['student' => 0, 'faculty' => 0, 'admin' => 0, 'dependent' => 0],
                    ];
                @endphp
                <tr>
                    <td colspan="6" class="bg-category">1. CONSULTATION / TREATMENT</td>
                </tr>
                @foreach($data as $catIndex => $cat)
                    @php
                        $categoryConsultations = $cat->medicalConditions->flatMap->consultations;
                        $categoryExcused = [
                            'student' => $countCertificateByType($categoryConsultations, 'excused_letter', 'student'),
                            'faculty' => $countCertificateByType($categoryConsultations, 'excused_letter', 'faculty'),
                            'admin' => $countCertificateByType($categoryConsultations, 'excused_letter', 'admin'),
                            'dependent' => $countCertificateByType($categoryConsultations, 'excused_letter', 'dependent'),
                        ];

                        if (array_sum($categoryExcused) > 0) {
                            $excusedLetterCategoryRows->push([
                                'label' => chr(65 + $catIndex) . '. ' . $cat->name,
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
                    @endphp
                    <tr class="bg-category">
                        <td colspan="6">{{ chr(65 + $catIndex) }}. {{ $cat->name }}</td>
                    </tr>
                    @foreach($cat->medicalConditions as $condition)
                        @php
                            $stu = $countByPatientType($condition->consultations, 'student');
                            $fac = $countByPatientType($condition->consultations, 'faculty');
                            $sta = $countByPatientType($condition->consultations, 'admin');
                            $dep = $countByPatientType($condition->consultations, 'dependent');
                            $rowTotal = $stu + $fac + $sta +$dep;
                            $consultationTotals['student'] += $stu;
                            $consultationTotals['faculty'] += $fac;
                            $consultationTotals['admin'] += $sta;
                            $consultationTotals['dependent'] += $dep;
                        @endphp
                        <tr>
                            <td class="text-left" style="padding-left: 15px;">{{ $condition->name }}</td>
                            <td>{{ $stu ?: '' }}</td>
                            <td>{{ $fac ?: '' }}</td>
                            <td>{{ $sta ?: '' }}</td>
                            <td>{{ $dep ?: '' }}</td>
                            <td></td>
                        </tr>
                    @endforeach
                @endforeach
                <tr class="bg-category">
                    <td>Total Consultation</td>
                    <td>{{ $consultationTotals['student'] }}</td>
                    <td>{{ $consultationTotals['faculty'] }}</td>
                    <td>{{ $consultationTotals['admin'] }}</td>
                    <td>{{ $consultationTotals['dependent'] }}</td>
                    <td></td>
                </tr>
                <tr class="bg-category"><td colspan="6">2. MEDICAL CERTIFICATE / CLEARANCE - CERTIFICATE OF COMPLIANCE</td></tr>
                <tr><td class="text-left" style="padding-left: 15px;">A. Excused Letter</td><td>{{ $excusedLetterTotals['student'] }}</td><td>{{ $excusedLetterTotals['faculty'] }}</td><td>{{ $excusedLetterTotals['admin'] }}</td><td>{{ $excusedLetterTotals['dependent'] }}</td><td></td></tr>
                @forelse($excusedLetterCategoryRows as $categoryRow)
                    <tr><td class="text-left" style="padding-left: 30px;">{{ $categoryRow['label'] }}</td><td>{{ $categoryRow['student'] }}</td><td>{{ $categoryRow['faculty'] }}</td><td>{{ $categoryRow['admin'] }}</td><td>{{ $categoryRow['dependent'] }}</td><td>{{ $categoryRow['total'] }}</td></tr>
                @empty
                    <tr><td class="text-left" style="padding-left: 30px;">No excused letter category recorded yet.</td><td>0</td><td>0</td><td>0</td><td>0</td><td>0</td></tr>
                @endforelse
                <tr><td class="text-left" style="padding-left: 15px;">B. COC for IJT</td><td>{{ $cocIjtTotals['student'] }}</td><td>{{ $cocIjtTotals['faculty'] }}</td><td>{{ $cocIjtTotals['admin'] }}</td><td>{{ $cocIjtTotals['dependent'] }}</td><td></td></tr>
                <tr><td class="text-left" style="padding-left: 15px;">C. COC for Ladderized</td><td>{{ $cocLadderizedTotals['student'] }}</td><td>{{ $cocLadderizedTotals['faculty'] }}</td><td>{{ $cocLadderizedTotals['admin'] }}</td><td>{{ $cocLadderizedTotals['dependent'] }}</td><td></td></tr>
                <tr class="bg-category"><td colspan="6">3. INJECTIONS</td></tr>
                <tr><td class="text-left" style="padding-left: 15px;">Injection Services</td><td>0</td><td>0</td><td>0</td><td>0</td><td>0</td></tr>
                <tr class="bg-category"><td colspan="6">4. REFERRALS</td></tr>
                <tr><td class="text-left" style="padding-left: 15px;">A. Ref. to Hospital without nurse</td><td>0</td><td>0</td><td>0</td><td>0</td><td>0</td></tr>
                <tr><td class="text-left" style="padding-left: 15px;">B. Ref. to Hospital with nurse</td><td>0</td><td>0</td><td>0</td><td>0</td><td>0</td></tr>
                <tr><td class="text-left" style="padding-left: 15px;">C. Referral</td><td>0</td><td>0</td><td>0</td><td>0</td><td>0</td></tr>
                <tr class="bg-category"><td colspan="6">5. OTHERS</td></tr>
                <tr><td class="text-left" style="padding-left: 15px;">Other Services</td><td>0</td><td>0</td><td>0</td><td>0</td><td>0</td></tr>
                <tr class="bg-category"><td colspan="6">6. ON-LINE CONSULTATION</td></tr>
                <tr><td class="text-left" style="padding-left: 15px;">A. Consultation</td><td>{{ $onlineTotals['consultation']['student'] }}</td><td>{{ $onlineTotals['consultation']['faculty'] }}</td><td>{{ $onlineTotals['consultation']['admin'] }}</td><td>{{ $onlineTotals['consultation']['dependent'] }}</td><td></td></tr>
                <tr><td class="text-left" style="padding-left: 15px;">B. Medical Clearance</td><td>{{ $onlineTotals['medical_clearance']['student'] }}</td><td>{{ $onlineTotals['medical_clearance']['faculty'] }}</td><td>{{ $onlineTotals['medical_clearance']['admin'] }}</td><td>{{ $onlineTotals['medical_clearance']['dependent'] }}</td><td></td></tr>
                <tr><td class="text-left" style="padding-left: 15px;">C. Others</td><td>{{ $onlineTotals['others']['student'] }}</td><td>{{ $onlineTotals['others']['faculty'] }}</td><td>{{ $onlineTotals['others']['admin'] }}</td><td>{{ $onlineTotals['others']['dependent'] }}</td><td></td></tr>
                <tr class="bg-category"><td colspan="6">7. TRIAGE SURVEY</td></tr>
                <tr><td class="text-left" style="padding-left: 15px;">A. Online</td><td>0</td><td>0</td><td>0</td><td>0</td><td>0</td></tr>
                <tr class="bg-category"><td colspan="6">8. BULLETIN UPDATES</td></tr>
                <tr><td class="text-left" style="padding-left: 15px;">Bulletin Updates</td><td>0</td><td>0</td><td>0</td><td>0</td><td>0</td></tr>
            </tbody>
        </table>

        <table class="mar-report-table" style="margin-top: 28px;">
            <colgroup>
                <col>
                <col class="metric-col">
                <col class="metric-col">
                <col class="metric-col">
                <col class="metric-col">
                <col class="metric-col">
            </colgroup>
            <thead>
                <tr>
                    <th>GAD (CONSULTATION)</th>
                    <th>STUDENTS</th>
                    <th>FACULTY</th>
                    <th>ADMIN</th>
                    <th>DEPENDENT</th>
                    <th>TOTAL</th>
                </tr>
            </thead>
            <tbody>
                <tr class="bg-category"><td colspan="6">GAD SUMMARY</td></tr>
                <tr><td class="text-left">Female</td><td>{{ $consultationGad['female']['student'] ?? 0 }}</td><td>{{ $consultationGad['female']['faculty'] ?? 0 }}</td><td>{{ $consultationGad['female']['admin'] ?? 0 }}</td><td>{{ $consultationGad['female']['dependent'] ?? 0 }}</td><td>{{ $consultationGad['female']['total'] ?? 0 }}</td></tr>
                <tr><td class="text-left">Male</td><td>{{ $consultationGad['male']['student'] ?? 0 }}</td><td>{{ $consultationGad['male']['faculty'] ?? 0 }}</td><td>{{ $consultationGad['male']['admin'] ?? 0 }}</td><td>{{ $consultationGad['male']['dependent'] ?? 0 }}</td><td>{{ $consultationGad['male']['total'] ?? 0 }}</td></tr>
                <tr class="bg-category"><td colspan="6">PWD</td></tr>
                <tr><td class="text-left" style="padding-left: 15px;">Male</td><td>{{ $consultationGad['pwd_male']['student'] ?? 0 }}</td><td>{{ $consultationGad['pwd_male']['faculty'] ?? 0 }}</td><td>{{ $consultationGad['pwd_male']['admin'] ?? 0 }}</td><td>{{ $consultationGad['pwd_male']['dependent'] ?? 0 }}</td><td>{{ $consultationGad['pwd_male']['total'] ?? 0 }}</td></tr>
                <tr><td class="text-left" style="padding-left: 15px;">Female</td><td>{{ $consultationGad['pwd_female']['student'] ?? 0 }}</td><td>{{ $consultationGad['pwd_female']['faculty'] ?? 0 }}</td><td>{{ $consultationGad['pwd_female']['admin'] ?? 0 }}</td><td>{{ $consultationGad['pwd_female']['dependent'] ?? 0 }}</td><td>{{ $consultationGad['pwd_female']['total'] ?? 0 }}</td></tr>
                <tr class="bg-category"><td colspan="6">Senior</td></tr>
                <tr><td class="text-left" style="padding-left: 15px;">Male</td><td>{{ $consultationGad['senior_male']['student'] ?? 0 }}</td><td>{{ $consultationGad['senior_male']['faculty'] ?? 0 }}</td><td>{{ $consultationGad['senior_male']['admin'] ?? 0 }}</td><td>{{ $consultationGad['senior_male']['dependent'] ?? 0 }}</td><td>{{ $consultationGad['senior_male']['total'] ?? 0 }}</td></tr>
                <tr><td class="text-left" style="padding-left: 15px;">Female</td><td>{{ $consultationGad['senior_female']['student'] ?? 0 }}</td><td>{{ $consultationGad['senior_female']['faculty'] ?? 0 }}</td><td>{{ $consultationGad['senior_female']['admin'] ?? 0 }}</td><td>{{ $consultationGad['senior_female']['dependent'] ?? 0 }}</td><td>{{ $consultationGad['senior_female']['total'] ?? 0 }}</td></tr>
                <tr class="bg-category"><td>Total</td><td>{{ $consultationGad['total']['student'] ?? 0 }}</td><td>{{ $consultationGad['total']['faculty'] ?? 0 }}</td><td>{{ $consultationGad['total']['admin'] ?? 0 }}</td><td>{{ $consultationGad['total']['dependent'] ?? 0 }}</td><td>{{ $consultationGad['total']['total'] ?? 0 }}</td></tr>
            </tbody>
        </table>

        <table class="mar-report-table" style="margin-top: 28px;">
            <colgroup>
                <col>
                <col class="metric-col">
                <col class="metric-col">
                <col class="metric-col">
                <col class="metric-col">
                <col class="metric-col">
            </colgroup>
            <thead>
                <tr>
                    <th>GAD (OJT/MEDICAL COMPLIANCE/MEDICAL FOR PROMOTION/EXCUSED LETTER)</th>
                    <th>STUDENTS</th>
                    <th>FACULTY</th>
                    <th>ADMIN</th>
                    <th>DEPENDENT</th>
                    <th>TOTAL</th>
                </tr>
            </thead>
            <tbody>
                <tr class="bg-category"><td colspan="6">GAD SUMMARY</td></tr>
                <tr><td class="text-left">Female</td><td>{{ $certificateGad['female']['student'] ?? 0 }}</td><td>{{ $certificateGad['female']['faculty'] ?? 0 }}</td><td>{{ $certificateGad['female']['admin'] ?? 0 }}</td><td>{{ $certificateGad['female']['dependent'] ?? 0 }}</td><td>{{ $certificateGad['female']['total'] ?? 0 }}</td></tr>
                <tr><td class="text-left">Male</td><td>{{ $certificateGad['male']['student'] ?? 0 }}</td><td>{{ $certificateGad['male']['faculty'] ?? 0 }}</td><td>{{ $certificateGad['male']['admin'] ?? 0 }}</td><td>{{ $certificateGad['male']['dependent'] ?? 0 }}</td><td>{{ $certificateGad['male']['total'] ?? 0 }}</td></tr>
                <tr class="bg-category"><td colspan="6">PWD</td></tr>
                <tr><td class="text-left" style="padding-left: 15px;">Male</td><td>{{ $certificateGad['pwd_male']['student'] ?? 0 }}</td><td>{{ $certificateGad['pwd_male']['faculty'] ?? 0 }}</td><td>{{ $certificateGad['pwd_male']['admin'] ?? 0 }}</td><td>{{ $certificateGad['pwd_male']['dependent'] ?? 0 }}</td><td>{{ $certificateGad['pwd_male']['total'] ?? 0 }}</td></tr>
                <tr><td class="text-left" style="padding-left: 15px;">Female</td><td>{{ $certificateGad['pwd_female']['student'] ?? 0 }}</td><td>{{ $certificateGad['pwd_female']['faculty'] ?? 0 }}</td><td>{{ $certificateGad['pwd_female']['admin'] ?? 0 }}</td><td>{{ $certificateGad['pwd_female']['dependent'] ?? 0 }}</td><td>{{ $certificateGad['pwd_female']['total'] ?? 0 }}</td></tr>
                <tr class="bg-category"><td colspan="6">Senior</td></tr>
                <tr><td class="text-left" style="padding-left: 15px;">Male</td><td>{{ $certificateGad['senior_male']['student'] ?? 0 }}</td><td>{{ $certificateGad['senior_male']['faculty'] ?? 0 }}</td><td>{{ $certificateGad['senior_male']['admin'] ?? 0 }}</td><td>{{ $certificateGad['senior_male']['dependent'] ?? 0 }}</td><td>{{ $certificateGad['senior_male']['total'] ?? 0 }}</td></tr>
                <tr><td class="text-left" style="padding-left: 15px;">Female</td><td>{{ $certificateGad['senior_female']['student'] ?? 0 }}</td><td>{{ $certificateGad['senior_female']['faculty'] ?? 0 }}</td><td>{{ $certificateGad['senior_female']['admin'] ?? 0 }}</td><td>{{ $certificateGad['senior_female']['dependent'] ?? 0 }}</td><td>{{ $certificateGad['senior_female']['total'] ?? 0 }}</td></tr>
                <tr class="bg-category"><td>Total</td><td>{{ $certificateGad['total']['student'] ?? 0 }}</td><td>{{ $certificateGad['total']['faculty'] ?? 0 }}</td><td>{{ $certificateGad['total']['admin'] ?? 0 }}</td><td>{{ $certificateGad['total']['dependent'] ?? 0 }}</td><td>{{ $certificateGad['total']['total'] ?? 0 }}</td></tr>
            </tbody>
        </table>

        <table class="mar-report-table" style="margin-top: 28px;">
            <colgroup>
                <col>
                <col class="metric-col">
                <col class="metric-col">
                <col class="metric-col">
                <col class="metric-col">
                <col class="metric-col">
            </colgroup>
            <thead>
                <tr>
                    <th>GAD (TRIAGE ONLINE)</th>
                    <th>STUDENTS</th>
                    <th>FACULTY</th>
                    <th>ADMIN</th>
                    <th>DEPENDENT</th>
                    <th>TOTAL</th>
                </tr>
            </thead>
            <tbody>
                <tr class="bg-category"><td colspan="6">GAD SUMMARY</td></tr>
                <tr><td class="text-left">Female</td><td>{{ $triageOnlineGad['female']['student'] ?? 0 }}</td><td>{{ $triageOnlineGad['female']['faculty'] ?? 0 }}</td><td>{{ $triageOnlineGad['female']['admin'] ?? 0 }}</td><td>{{ $triageOnlineGad['female']['dependent'] ?? 0 }}</td><td>{{ $triageOnlineGad['female']['total'] ?? 0 }}</td></tr>
                <tr><td class="text-left">Male</td><td>{{ $triageOnlineGad['male']['student'] ?? 0 }}</td><td>{{ $triageOnlineGad['male']['faculty'] ?? 0 }}</td><td>{{ $triageOnlineGad['male']['admin'] ?? 0 }}</td><td>{{ $triageOnlineGad['male']['dependent'] ?? 0 }}</td><td>{{ $triageOnlineGad['male']['total'] ?? 0 }}</td></tr>
                <tr class="bg-category"><td colspan="6">PWD</td></tr>
                <tr><td class="text-left" style="padding-left: 15px;">Male</td><td>{{ $triageOnlineGad['pwd_male']['student'] ?? 0 }}</td><td>{{ $triageOnlineGad['pwd_male']['faculty'] ?? 0 }}</td><td>{{ $triageOnlineGad['pwd_male']['admin'] ?? 0 }}</td><td>{{ $triageOnlineGad['pwd_male']['dependent'] ?? 0 }}</td><td>{{ $triageOnlineGad['pwd_male']['total'] ?? 0 }}</td></tr>
                <tr><td class="text-left" style="padding-left: 15px;">Female</td><td>{{ $triageOnlineGad['pwd_female']['student'] ?? 0 }}</td><td>{{ $triageOnlineGad['pwd_female']['faculty'] ?? 0 }}</td><td>{{ $triageOnlineGad['pwd_female']['admin'] ?? 0 }}</td><td>{{ $triageOnlineGad['pwd_female']['dependent'] ?? 0 }}</td><td>{{ $triageOnlineGad['pwd_female']['total'] ?? 0 }}</td></tr>
                <tr class="bg-category"><td colspan="6">Senior</td></tr>
                <tr><td class="text-left" style="padding-left: 15px;">Male</td><td>{{ $triageOnlineGad['senior_male']['student'] ?? 0 }}</td><td>{{ $triageOnlineGad['senior_male']['faculty'] ?? 0 }}</td><td>{{ $triageOnlineGad['senior_male']['admin'] ?? 0 }}</td><td>{{ $triageOnlineGad['senior_male']['dependent'] ?? 0 }}</td><td>{{ $triageOnlineGad['senior_male']['total'] ?? 0 }}</td></tr>
                <tr><td class="text-left" style="padding-left: 15px;">Female</td><td>{{ $triageOnlineGad['senior_female']['student'] ?? 0 }}</td><td>{{ $triageOnlineGad['senior_female']['faculty'] ?? 0 }}</td><td>{{ $triageOnlineGad['senior_female']['admin'] ?? 0 }}</td><td>{{ $triageOnlineGad['senior_female']['dependent'] ?? 0 }}</td><td>{{ $triageOnlineGad['senior_female']['total'] ?? 0 }}</td></tr>
                <tr class="bg-category"><td>Total</td><td>{{ $triageOnlineGad['total']['student'] ?? 0 }}</td><td>{{ $triageOnlineGad['total']['faculty'] ?? 0 }}</td><td>{{ $triageOnlineGad['total']['admin'] ?? 0 }}</td><td>{{ $triageOnlineGad['total']['dependent'] ?? 0 }}</td><td>{{ $triageOnlineGad['total']['total'] ?? 0 }}</td></tr>
            </tbody>
        </table>

        <table class="mar-report-table" style="margin-top: 28px;">
            <colgroup>
                <col>
                <col class="metric-col">
                <col class="metric-col">
                <col class="metric-col">
                <col class="metric-col">
                <col class="metric-col">
            </colgroup>
            <thead>
                <tr>
                    <th>GAD (CONSULTATION + OJT/MEDICAL COMPLIANCE/MEDICAL FOR PROMOTION + TRIAGE)</th>
                    <th>STUDENTS</th>
                    <th>FACULTY</th>
                    <th>ADMIN</th>
                    <th>DEPENDENT</th>
                    <th>TOTAL</th>
                </tr>
            </thead>
            <tbody>
                <tr class="bg-category"><td colspan="6">GAD SUMMARY</td></tr>
                <tr><td class="text-left">Female</td><td>{{ $combinedGad['female']['student'] ?? 0 }}</td><td>{{ $combinedGad['female']['faculty'] ?? 0 }}</td><td>{{ $combinedGad['female']['admin'] ?? 0 }}</td><td>{{ $combinedGad['female']['dependent'] ?? 0 }}</td><td>{{ $combinedGad['female']['total'] ?? 0 }}</td></tr>
                <tr><td class="text-left">Male</td><td>{{ $combinedGad['male']['student'] ?? 0 }}</td><td>{{ $combinedGad['male']['faculty'] ?? 0 }}</td><td>{{ $combinedGad['male']['admin'] ?? 0 }}</td><td>{{ $combinedGad['male']['dependent'] ?? 0 }}</td><td>{{ $combinedGad['male']['total'] ?? 0 }}</td></tr>
                <tr class="bg-category"><td colspan="6">PWD</td></tr>
                <tr><td class="text-left" style="padding-left: 15px;">Male</td><td>{{ $combinedGad['pwd_male']['student'] ?? 0 }}</td><td>{{ $combinedGad['pwd_male']['faculty'] ?? 0 }}</td><td>{{ $combinedGad['pwd_male']['admin'] ?? 0 }}</td><td>{{ $combinedGad['pwd_male']['dependent'] ?? 0 }}</td><td>{{ $combinedGad['pwd_male']['total'] ?? 0 }}</td></tr>
                <tr><td class="text-left" style="padding-left: 15px;">Female</td><td>{{ $combinedGad['pwd_female']['student'] ?? 0 }}</td><td>{{ $combinedGad['pwd_female']['faculty'] ?? 0 }}</td><td>{{ $combinedGad['pwd_female']['admin'] ?? 0 }}</td><td>{{ $combinedGad['pwd_female']['dependent'] ?? 0 }}</td><td>{{ $combinedGad['pwd_female']['total'] ?? 0 }}</td></tr>
                <tr class="bg-category"><td colspan="6">Senior</td></tr>
                <tr><td class="text-left" style="padding-left: 15px;">Male</td><td>{{ $combinedGad['senior_male']['student'] ?? 0 }}</td><td>{{ $combinedGad['senior_male']['faculty'] ?? 0 }}</td><td>{{ $combinedGad['senior_male']['admin'] ?? 0 }}</td><td>{{ $combinedGad['senior_male']['dependent'] ?? 0 }}</td><td>{{ $combinedGad['senior_male']['total'] ?? 0 }}</td></tr>
                <tr><td class="text-left" style="padding-left: 15px;">Female</td><td>{{ $combinedGad['senior_female']['student'] ?? 0 }}</td><td>{{ $combinedGad['senior_female']['faculty'] ?? 0 }}</td><td>{{ $combinedGad['senior_female']['admin'] ?? 0 }}</td><td>{{ $combinedGad['senior_female']['dependent'] ?? 0 }}</td><td>{{ $combinedGad['senior_female']['total'] ?? 0 }}</td></tr>
                <tr class="bg-category"><td>Total</td><td>{{ $combinedGad['total']['student'] ?? 0 }}</td><td>{{ $combinedGad['total']['faculty'] ?? 0 }}</td><td>{{ $combinedGad['total']['admin'] ?? 0 }}</td><td>{{ $combinedGad['total']['dependent'] ?? 0 }}</td><td>{{ $combinedGad['total']['total'] ?? 0 }}</td></tr>
            </tbody>
        </table>

            <table class="official-inventory-signatures">
                <tr>
                    <td>Prepared by:</td>
                    <td>Noted by:</td>
                </tr>
                <tr>
                    <td class="official-inventory-signature-space"></td>
                    <td class="official-inventory-signature-space"></td>
                </tr>
                <tr>
                    <td>
                        <div class="official-inventory-signature-name">{{ $marPreparedByName }}</div>
                        <div class="official-inventory-signature-role">{{ $marPreparedByPosition }}</div>
                    </td>
                    <td>
                        <div class="official-inventory-signature-name">Engr. Michael L. Zarco</div>
                        <div class="official-inventory-signature-role">Administrative Officer</div>
                    </td>
                </tr>
            </table>
        </section>


    @elseif($type == 'inventory')
        @php
            $inventoryScope = $inventoryScope ?? 'all';
            $reportAsOf = $inventoryReportAsOf ?? \Carbon\Carbon::parse(($monthFilter ?? now()->format('Y-m')) . '-01')->endOfMonth();
            $preparedBy = $inventoryPreparedBy ?? null;
            $preparedByName = trim((string) optional($preparedBy)->name) ?: 'CLINIC STAFF';
            $preparedByOffice = trim((string) optional(optional($preparedBy)->adminProfile)->office);
            $preparedByPosition = $preparedByOffice !== ''
                ? $preparedByOffice
                : (\App\Models\User::normalizeRole(optional($preparedBy)->user_role) === \App\Models\User::ROLE_ADMIN ? 'Nurse / Clinic Staff' : 'Clinic Staff');
            $inventoryTitle = $inventoryScope === 'medicines'
                ? 'Inventory of Medicines'
                : 'Inventory of Supplies';
            $inventoryFormCode = $inventoryScope === 'medicines'
                ? 'PUP-IRDM-6-MEDS-030 Rev.0 July 11, 2024'
                : 'PUP Medical Services Inventory Form';
            $inventoryGroups = collect($data)
                ->sortBy(function ($item) use ($inventoryScope) {
                    $group = $inventoryScope === 'medicines'
                        ? ($item->medicine_type ?: 'Uncategorized Medicine')
                        : 'Supplies';

                    return strtoupper($group . ' ' . $item->name);
                })
                ->groupBy(function ($item) use ($inventoryScope) {
                    return $inventoryScope === 'medicines'
                        ? ($item->medicine_type ?: 'Uncategorized Medicine')
                        : 'Supplies';
                });
            $formatInventoryQuantity = function ($value) {
                return rtrim(rtrim(number_format((float) $value, 2, '.', ''), '0'), '.');
            };
        @endphp

        <footer class="official-inventory-page-footer">
            <p class="official-inventory-footer-contact">
                Gen. Santos Avenue, Lower Bicutan, Taguig City 1632<br>
                Direct Line: (02) 8837 5858 to 60 | Email: taguig@pup.edu.ph<br>
                Website: www.pup.edu.ph | Inquiries: https://bit.ly/PUPSINTA
            </p>
            <p class="official-inventory-footer-motto">A Leading Comprehensive Polytechnic University in Asia</p>
        </footer>

        <section class="official-inventory-report">
            <header class="official-inventory-header">
                <img class="official-inventory-logo" src="{{ asset('images/pup_logo.png') }}" alt="PUP Logo">
                <img class="official-inventory-government-logo" src="{{ asset('images/Bagong_Pilipinas_logo.png') }}" alt="Bagong Pilipinas Logo">
                <p class="official-inventory-university">Polytechnic University of the Philippines</p>
                <p class="official-inventory-office">Medical Services Department</p>
                <p class="official-inventory-campus">Taguig Campus</p>
                <h1 class="official-inventory-header-title">{{ $inventoryTitle }}</h1>
                <p class="official-inventory-header-date">As of {{ $reportAsOf->format('F d, Y') }}</p>
                <span class="official-inventory-form-code">{{ $inventoryFormCode }}</span>
            </header>

            <table class="official-inventory-meta">
                <tr>
                    <td>
                        <span class="meta-label">Name:</span>
                        <span class="meta-value">{{ $preparedByName }}</span>
                    </td>
                    <td>
                        <span class="meta-label">Date of Submission:</span>
                        <span class="meta-value">{{ $reportAsOf->format('F d, Y') }}</span>
                    </td>
                </tr>
                <tr>
                    <td>
                        <span class="meta-label">Position:</span>
                        <span class="meta-value">{{ $preparedByPosition }}</span>
                    </td>
                    <td>
                        <span class="meta-label">Unit / Department:</span>
                        <span class="meta-value">Taguig Campus</span>
                    </td>
                </tr>
            </table>

            <table class="official-inventory-table">
                <colgroup>
                    <col style="width: 10%;">
                    <col style="width: 11%;">
                    <col style="width: 31%;">
                    <col style="width: 9%;">
                    <col style="width: 10%;">
                    <col style="width: 10%;">
                    <col style="width: 9%;">
                    <col style="width: 10%;">
                </colgroup>
                <thead>
                    <tr>
                        <th>Date</th>
                        <th>Stock Number</th>
                        <th>Medicines &amp; Materials</th>
                        <th>Units</th>
                        <th>Quantity</th>
                        <th>Consumed</th>
                        <th>Balance</th>
                        <th>Expiration Date</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($inventoryGroups as $groupName => $items)
                        <tr class="inventory-category-row">
                            <td colspan="8">{{ $groupName }}</td>
                        </tr>
                        @foreach($items as $item)
                            <tr>
                                <td class="date-cell">{{ optional($item->date_added)->format('d-M-y') ?: '-' }}</td>
                                <td class="number-cell">{{ $item->stock_number ?: '-' }}</td>
                                <td class="item-cell">{{ $item->name }}</td>
                                <td class="unit-cell">{{ $item->unit ?: 'Piece' }}</td>
                                <td class="number-cell">{{ $formatInventoryQuantity($item->starting_stock) }}</td>
                                <td class="number-cell">{{ $formatInventoryQuantity($item->consumed) }}</td>
                                <td class="number-cell">{{ $formatInventoryQuantity($item->current_balance) }}</td>
                                <td class="date-cell">{{ optional($item->expiration_date)->format('M Y') ?: '-' }}</td>
                            </tr>
                        @endforeach
                    @empty
                        <tr>
                            <td colspan="8" style="height: 38px; text-align: center;">
                                No {{ $inventoryScope === 'medicines' ? 'medicines' : 'supplies' }} found in the inventory.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>

            <table class="official-inventory-signatures">
                <tr>
                    <td>Prepared by:</td>
                    <td>Noted by:</td>
                </tr>
                <tr>
                    <td class="official-inventory-signature-space"></td>
                    <td class="official-inventory-signature-space"></td>
                </tr>
                <tr>
                    <td>
                        <div class="official-inventory-signature-name">{{ $preparedByName }}</div>
                        <div class="official-inventory-signature-role">{{ $preparedByPosition }}</div>
                    </td>
                    <td>
                        <div class="official-inventory-signature-name">Engr. Michael L. Zarco</div>
                        <div class="official-inventory-signature-role">Administrative Officer</div>
                    </td>
                </tr>
            </table>
        </section>




    @elseif($type == 'appointment')
        <table>
            <thead>
                <tr>
                    <th>DATE</th>
                    <th>PATIENT NAME</th>
                    <th>USER TYPE</th>
                    <th>PURPOSE / REASON</th>
                    <th>STATUS</th>
                </tr>
            </thead>
            <tbody>
                @forelse($data as $app)
                <tr>
                    <td>{{ date('M d, Y', strtotime($app->date)) }}</td>
                    <td class="text-left">{{ $app->name }}</td>
                    <td>{{ $app->user_type ?? 'N/A' }}</td>
                    <td>{{ $app->service }}</td>
                    <td>{{ $app->status }}</td>
                </tr>
                @empty
                <tr><td colspan="5">No recorded appointments.</td></tr>
                @endforelse
            </tbody>
        </table>
    @elseif($type == 'health_forms')
        <table>
            <thead>
                <tr>
                    <th>COURSE</th>
                    <th>ISSUED FORMS</th>
                    <th>WITH CONDITION</th>
                    <th>NO CONDITION</th>
                    <th>LAST ISSUED</th>
                </tr>
            </thead>
            <tbody>
                @forelse($data as $form)
                <tr>
                    <td class="text-left">{{ $form->course }}</td>
                    <td>{{ $form->issued_count }}</td>
                    <td>{{ $form->with_condition_count }}</td>
                    <td>{{ $form->no_condition_count }}</td>
                    <td>{{ optional($form->last_issued_at)->format('M d, Y') }}</td>
                </tr>
                @empty
                <tr><td colspan="5">No issued health forms found.</td></tr>
                @endforelse
            </tbody>
        </table>
    @endif

    @if(!in_array($type, ['inventory', 'mar'], true))
        <div class="footer-signatures" style="margin-top: 40px;">
            <div class="sig-box">
                <p>Prepared by:</p>
                <div class="sig-line">NURSE / MEDICAL STAFF</div>
            </div>
            <div class="sig-box">
                <p>Noted by:</p>
                <div class="sig-line">BRANCH DIRECTOR</div>
            </div>
        </div>

        <div class="official-footer">
            <div class="footer-details">
                <p>General Santos Avenue, Lower Bicutan Taguig City Philippines, 1632</p>
                <p>Direct Line (02) 8837 5658 to 60</p>
                <p>Website: www.pup.edu.ph | Email: taguig@pup.edu.ph</p>
            </div>
            <div class="footer-motto">
                THE COUNTRY'S FIRST POLYTECHNICU
            </div>
            <div class="generated-report-caption">
                <div class="signature-note">This is system-generated, signature is not required.</div>
                <div class="privacy-note">
                    This document contains personal-identifiable information that is subject to Data Privacy.<br>
                    Please keep this document protected and in a safe place.
                </div>
            </div>
        </div>
    @endif
    </div>

</body>
</html>
