@php
    $pupLogo = public_path('images/pup_logo_print.jpg');
    $bpLogo = public_path('images/bagong_pilipinas_logo_print.jpg');
    $footerImg = public_path('images/footer_bg_print.jpg');

    $hasPupLogo = file_exists($pupLogo);
    $hasBpLogo = file_exists($bpLogo);
    $hasFooterImg = file_exists($footerImg);

    $purpose1 = $purposeUnderline1 ?? 'enrolled student';
    $purpose2 = $purposeUnderline2 ?? 'enrolment as a student';
    $fullName = trim((string) ($studentFullName ?? ''));
    $sigDate = $signatureDate ?? now()->format('m/d/Y');
    $guardian = trim((string) ($guardianName ?? ''));
    $showGuardian = !empty($isMinor) || $guardian !== '';
@endphp
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Declaration of Medical Information and Data Subject Consent Form</title>
    <style>
        @page {
            size: 8.5in 11in;
            margin: 0.5in 0.6in 0.5in 0.6in;
        }

        * {
            box-sizing: border-box;
        }

        body {
            margin: 0;
            padding: 0;
            color: #111827;
            font-family: Arial, Helvetica, sans-serif;
            font-size: 12.5px;
            line-height: 1.45;
        }

        .header-table {
            width: 100%;
            border-collapse: collapse;
            table-layout: fixed;
        }

        .header-table td {
            border: 0;
            padding: 0;
            vertical-align: middle;
        }

        .header-logo-left {
            width: 78px;
            text-align: left;
        }

        .header-logo-left img {
            width: 72px;
            height: 72px;
            object-fit: contain;
        }

        .header-center {
            text-align: left;
            padding-left: 8px !important;
            line-height: 1.18;
        }

        .header-center .gov-title {
            font-size: 10px;
            color: #374151;
            margin: 0;
        }

        .header-center .univ-title {
            font-family: "Times New Roman", Times, serif;
            font-size: 13.5px;
            font-weight: bold;
            color: #111827;
            text-transform: uppercase;
            letter-spacing: 0.02em;
            margin: 1px 0;
        }

        .header-center .office-title {
            font-size: 10.5px;
            color: #374151;
            margin: 0;
        }

        .header-center .dept-title {
            font-size: 11.5px;
            font-weight: bold;
            color: #111827;
            margin: 1px 0 0;
        }

        .header-campus-tag {
            text-align: center;
            font-size: 12px;
            font-weight: normal;
            color: #111827;
            margin: 2px 0 0;
        }

        .header-logo-right {
            width: 82px;
            text-align: right;
        }

        .header-logo-right img {
            width: 76px;
            height: auto;
            object-fit: contain;
        }

        .header-divider {
            border: 0;
            border-top: 1px solid #cbd5e1;
            margin: 8px 0 16px;
        }

        .form-title {
            margin: 16px 0 20px;
            font-size: 15px;
            font-weight: bold;
            text-align: center;
            color: #000000;
            letter-spacing: 0.01em;
        }

        .declaration-body {
            margin: 0;
            padding: 0 4px;
        }

        .declaration-p {
            margin: 0 0 16px;
            font-size: 12.5px;
            line-height: 1.55;
            text-align: justify;
            text-justify: inter-word;
            color: #111827;
            text-indent: 32px;
        }

        .declaration-p u {
            font-weight: bold;
            text-decoration: underline;
            text-underline-offset: 2px;
        }

        .signature-block-table {
            width: 100%;
            margin-top: 28px;
            border-collapse: collapse;
            table-layout: fixed;
        }

        .signature-block-table td {
            border: 0;
            padding: 0;
            vertical-align: top;
        }

        .sig-col-left {
            width: 44%;
        }

        .sig-col-right {
            width: 56%;
            padding-left: 14px !important;
        }

        .sig-box {
            position: relative;
            text-align: center;
            margin-bottom: 22px;
        }

        .student-signature-preview {
            display: block;
            margin: 0 auto -18px;
            max-height: 48px;
            max-width: 170px;
            object-fit: contain;
            position: relative;
            z-index: 2;
        }

        .sig-underline {
            border-bottom: 1px solid #000000;
            min-height: 18px;
            font-size: 12px;
            font-weight: bold;
            text-transform: uppercase;
            padding-bottom: 2px;
            position: relative;
            z-index: 1;
        }

        .sig-caption {
            margin-top: 3px;
            font-size: 11px;
            color: #111827;
        }

        .remarks-line {
            margin: 14px 0 20px;
            font-size: 12px;
            text-align: left;
        }

        .minor-note {
            margin-top: 6px;
            font-size: 11px;
            line-height: 1.35;
            color: #111827;
            text-align: justify;
        }

        .footer-table {
            width: 100%;
            margin-top: 45px;
            border-collapse: collapse;
            table-layout: fixed;
        }

        .footer-table td {
            border: 0;
            padding: 0;
            vertical-align: bottom;
        }

        .footer-left {
            width: 56%;
            font-size: 8.5px;
            line-height: 1.25;
            color: #374151;
        }

        .footer-left a {
            color: #1d4ed8;
            text-decoration: underline;
        }

        .footer-motto {
            margin-top: 5px;
            font-size: 9.5px;
            font-weight: bold;
            color: #111827;
            letter-spacing: 0.02em;
            text-transform: uppercase;
        }

        .footer-right {
            width: 44%;
            text-align: right;
        }

        .footer-badge-img {
            max-width: 100%;
            height: 38px;
            object-fit: contain;
        }
    </style>
</head>
<body>

    <table class="header-table">
        <tr>
            <td class="header-logo-left">
                @if($hasPupLogo)
                    <img src="{{ $pupLogo }}" alt="PUP Logo">
                @endif
            </td>
            <td class="header-center">
                <p class="gov-title">Republic of the Philippines</p>
                <p class="univ-title">POLYTECHNIC UNIVERSITY OF THE PHILIPPINES</p>
                <p class="office-title">Office of the Vice President for Administration</p>
                <p class="dept-title">Medical Services Department</p>
                <div class="header-campus-tag">Taguig Campus</div>
            </td>
            <td class="header-logo-right">
                @if($hasBpLogo)
                    <img src="{{ $bpLogo }}" alt="Bagong Pilipinas Logo">
                @endif
            </td>
        </tr>
    </table>

    <hr class="header-divider">

    <div class="form-title">
        Declaration of Medical Information and Data Subject Consent Form
    </div>

    <div class="declaration-body">
        <p class="declaration-p">
            I hereby certify that the medical health information given to the physician and nurses of Polytechnic University of the Philippines (PUP) during my on-site consultation for the issuance of medical clearance for <u>{{ $purpose1 }}</u> are true, correct and complete to the best of my knowledge. I have fully disclosed all the medical condition that may affect in the assessment to endorse my <u>{{ $purpose2 }}</u> of PUP Taguig Campus
        </p>

        <p class="declaration-p">
            I also understand that the PUP Medical Services and University will not be liable for any untoward incident that may arise due to my failure to disclose accurate information or intentionally providing false and deceptive information.
        </p>

        <p class="declaration-p">
            In compliance with the Data Privacy Act of 2012 and its implementing Rules and Regulations, I voluntarily consent to the collection, processing and storage of my personal and health information for the purpose/s of health assessment, treatment/ or research (following research ethics guidelines) for the improvement of healthcare services.
        </p>
    </div>

    <table class="signature-block-table">
        <tr>
            <td class="sig-col-left"></td>
            <td class="sig-col-right">
                <div class="sig-box">
                    @if(!empty($studentSignatureSrc))
                        <img src="{{ $studentSignatureSrc }}" alt="Student Signature" class="student-signature-preview">
                    @endif
                    <div class="sig-underline">
                        {{ $fullName !== '' ? $fullName : 'STUDENT NAME' }} / {{ $sigDate }}
                    </div>
                    <div class="sig-caption">
                        Student's Signature Over Printed Name/ Date
                    </div>
                </div>

                <div class="remarks-line">
                    <strong>Remarks:</strong> {{ $remarks ?? '' }}
                </div>

                <div class="sig-box" style="margin-top: 24px; margin-bottom: 0;">
                    <div class="sig-underline">
                        {{ $guardian !== '' ? $guardian . ' / ' . $sigDate : '' }}
                    </div>
                    <div class="sig-caption">
                        Guardian's Signature Over Printed Name/ Date
                    </div>
                    <div class="minor-note">
                        Both student and guardian will affix their signature if the student is aged below 18 years old.
                    </div>
                </div>
            </td>
        </tr>
    </table>

    <table class="footer-table">
        <tr>
            <td class="footer-left">
                Gen. Santos Avenue, Lower Bicutan, Taguig City 1632<br>
                Direct Line: (02) 8837 5858 to 60 | Email: <a href="mailto:taguig@pup.edu.ph">taguig@pup.edu.ph</a><br>
                Website: <a href="https://www.pup.edu.ph">www.pup.edu.ph</a> | Inquiries: <a href="https://bit.ly/PUPSINTA">https://bit.ly/PUPSINTA</a>
                <div class="footer-motto">
                    THE COUNTRY'S 1st POLYTECHNIC U
                </div>
            </td>
            <td class="footer-right">
                @if($hasFooterImg)
                    <img src="{{ $footerImg }}" alt="Certifications" class="footer-badge-img">
                @endif
            </td>
        </tr>
    </table>

</body>
</html>

