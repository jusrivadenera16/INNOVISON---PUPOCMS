@php
    $pupLogo = public_path('images/pup_logo_print.jpg');
    $governmentLogo = public_path('images/bagong_pilipinas_logo_print.jpg');
    $footerImage = public_path('images/footer_bg_print.jpg');
    $hasPupLogo = file_exists($pupLogo);
    $hasGovernmentLogo = file_exists($governmentLogo);
    $hasFooterImage = file_exists($footerImage);
    $periodLabel = $dateFrom->format('M d, Y') . ' - ' . $dateTo->format('M d, Y');
@endphp
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Health Forms Export</title>
    <style>
        @page {
            size: A4 portrait;
            margin: 150px 18px 88px;
        }

        * {
            box-sizing: border-box;
        }

        body {
            margin: 0;
            color: #000;
            font-family: Arial, Helvetica, sans-serif;
            font-size: 12px;
            line-height: 1.28;
        }

        .official-header {
            position: fixed;
            top: -122px;
            right: 0;
            left: 0;
            height: 96px;
            padding: 4px 136px 6px;
            text-align: center;
        }

        .official-logo {
            position: absolute;
            top: 2px;
            left: 28px;
            width: 96px;
            height: 96px;
            object-fit: contain;
        }

        .government-logo {
            position: absolute;
            top: 2px;
            right: 28px;
            width: 96px;
            height: 96px;
            object-fit: contain;
        }

        .logo-fallback {
            display: inline-block;
            padding-top: 22px;
            border: 1px solid #800000;
            border-radius: 50%;
            color: #800000;
            font-size: 9px;
            font-weight: 700;
            text-align: center;
        }

        .official-university {
            width: auto;
            margin: 16px 0 0;
            font-family: "Times New Roman", Times, serif;
            font-size: 16px;
            font-weight: 400;
            line-height: 1.05;
            text-align: center;
        }

        .official-office {
            width: auto;
            margin: 0;
            font-family: "Times New Roman", Times, serif;
            font-size: 16px;
            font-weight: 700;
            font-style: italic;
            line-height: 1.05;
            text-align: center;
        }

        .official-campus {
            width: auto;
            margin: 0;
            font-size: 14px;
            font-weight: 400;
            line-height: 1.05;
            text-align: center;
        }

        .official-footer {
            position: fixed;
            right: 0;
            bottom: -74px;
            left: 0;
            font-family: Arial, Helvetica, sans-serif;
            color: #000;
        }

        .footer-layout {
            width: 100%;
            border-collapse: collapse;
            table-layout: fixed;
        }

        .footer-layout td {
            border: 0;
            padding: 0;
            vertical-align: top;
        }

        .footer-copy-cell {
            width: 40%;
            padding-left: 24px !important;
        }

        .footer-image-cell {
            width: 60%;
            text-align: left;
        }

        .footer-contact {
            margin: 0;
            font-size: 10px;
            line-height: 1.28;
        }

        .footer-motto {
            margin: 4px 0 0;
            font-family: "Times New Roman", Times, serif;
            font-size: 12px;
            font-weight: 700;
            line-height: 1.08;
            letter-spacing: .35px;
            text-transform: uppercase;
        }

        .footer-image {
            width: 410px;
            max-height: 60px;
            object-fit: contain;
        }

        .footer-note {
            margin: 3px 0 0;
            color: #7f1d1d;
            font-size: 7.5px;
            line-height: 1.15;
            text-align: center;
            white-space: nowrap;
        }

        .report-heading {
            margin: 0 0 10px;
            text-align: center;
        }

        .report-heading h1 {
            margin: 0;
            font-size: 14px;
            font-weight: 700;
            line-height: 1.15;
        }

        .report-heading .report-level {
            margin: 4px 0 0;
            font-size: 11px;
            font-weight: 700;
        }

        .report-heading .report-period {
            margin: 3px 0 0;
            font-size: 8px;
            font-weight: 400;
        }

        .health-export-table {
            width: 100%;
            border-collapse: collapse;
            table-layout: fixed;
        }

        .health-export-table thead {
            display: table-header-group;
        }

        .health-export-table tr {
            page-break-inside: avoid;
        }

        .health-export-table th,
        .health-export-table td {
            border: 1px solid #000;
            padding: 5px 6px;
            font-size: 12px;
            line-height: 1.28;
            text-align: center;
            vertical-align: middle;
            overflow-wrap: anywhere;
            word-wrap: break-word;
        }

        .health-export-table th {
            height: 42px;
            font-size: 11px;
            font-weight: 700;
        }

        .health-export-table .is-left {
            text-align: left;
        }

        .health-export-table .dose-line {
            display: block;
            margin-top: 2px;
        }

        .health-export-table .empty-row td {
            padding: 18px 6px;
            color: #4b5563;
            font-style: italic;
            text-align: center;
        }
    </style>
</head>
<body>
    <header class="official-header">
        @if($hasPupLogo)
            <img class="official-logo" src="{{ $pupLogo }}" alt="PUP Logo">
        @else
            <span class="official-logo logo-fallback">PUP</span>
        @endif

        @if($hasGovernmentLogo)
            <img class="government-logo" src="{{ $governmentLogo }}" alt="Bagong Pilipinas Logo">
        @else
            <span class="government-logo logo-fallback">BP</span>
        @endif

        <p class="official-university">Polytechnic University of the Philippines</p>
        <p class="official-office">Medical Services Department</p>
        <p class="official-campus">Taguig Campus</p>
    </header>

    <footer class="official-footer">
        <table class="footer-layout">
            <tr>
                <td class="footer-copy-cell">
                    <p class="footer-contact">
                        Gen. Santos Avenue, Lower Bicutan, Taguig City 1632<br>
                        Direct Line: (02) 8837 5858 to 60 | Email: taguig@pup.edu.ph<br>
                        Website: www.pup.edu.ph | Inquiries: https://bit.ly/PUPSINTA
                    </p>
                    <p class="footer-motto">A Leading Comprehensive<br>Polytechnic University in Asia</p>
                </td>
                <td class="footer-image-cell">
                    @if($hasFooterImage)
                        <img class="footer-image" src="{{ $footerImage }}" alt="PUP accreditation marks">
                    @endif
                </td>
            </tr>
            <tr>
                <td colspan="2">
                    <p class="footer-note">
                        This is system-generated, signature is not required. This document contains personal-identifiable information that is subject to Data Privacy. Please keep this document protected and in a safe place.
                    </p>
                </td>
            </tr>
        </table>
    </footer>

    <main>
        <div class="report-heading">
            <h1>{{ $reportCourse }}</h1>
            <p class="report-level">{{ $reportLevel !== '' ? $reportLevel . ' ' : '' }}SY {{ $schoolYear }}</p>
            <p class="report-period">Health Forms Report | {{ $periodLabel }} | Generated {{ $generatedAt->format('M d, Y g:i A') }}</p>
        </div>

        <table class="health-export-table">
            <colgroup>
                <col style="width: 16%;">
                <col style="width: 15%;">
                <col style="width: 10%;">
                <col style="width: 17%;">
                <col style="width: 17%;">
                <col style="width: 14%;">
                <col style="width: 11%;">
            </colgroup>
            <thead>
                <tr>
                    <th>SURNAME, FIRST NAME,<br>MIDDLE NAME</th>
                    <th>Present Address</th>
                    <th>Contact Number<br>(Student)</th>
                    <th>2 Names of Parents / Guardian with Contact Number (Required)</th>
                    <th>COVID Vaccination - Fully Vaccinated (First and Second Dose)</th>
                    <th>Pre-existing Illness</th>
                    <th>Living with Parent - P<br>Guardian - G<br>Boarding House - BH</th>
                </tr>
            </thead>
            <tbody>
                @forelse($rows as $row)
                    <tr>
                        <td class="is-left">{{ $row['name'] }}</td>
                        <td>{{ $row['address'] }}</td>
                        <td>{{ $row['contact'] }}</td>
                        <td>{{ $row['guardian'] }}</td>
                        <td>
                            {{ $row['vaccination_status'] }}
                            @foreach($row['vaccination_doses'] as $dose)
                                <span class="dose-line">{{ $dose }}</span>
                            @endforeach
                        </td>
                        <td>{{ $row['illness'] }}</td>
                        <td>{{ $row['living_with'] }}</td>
                    </tr>
                @empty
                    <tr class="empty-row">
                        <td colspan="7">No health form records matched the selected filters.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </main>
</body>
</html>
