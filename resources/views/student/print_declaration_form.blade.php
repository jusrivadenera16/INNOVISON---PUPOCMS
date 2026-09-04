@php
    $pupLogo = public_path('images/pup_logo_print.jpg');
    $bpLogo = public_path('images/bagong_pilipinas_logo_print.jpg');
    $footerImg = public_path('images/footer_bg_print.jpg');

    $hasPupLogo = file_exists($pupLogo);
    $hasBpLogo = file_exists($bpLogo);
    $hasFooterImg = file_exists($footerImg);

    $purpose1 = $purposeUnderline1 ?? 'enrolled student';
    $purpose2 = $purposeUnderline2 ?? 'enrollment as a student';
    $fullName = trim((string) ($studentFullName ?? ''));
    $sigDate = $signatureDate ?? now()->format('m/d/Y');
    $guardian = trim((string) ($guardianName ?? ''));
    $isMinorStudent = isset($isMinor) ? (bool) $isMinor : ((int) ($age ?? 0) < 18);
    $signatureAlt = $signatureAlt ?? 'Student Signature';
    $signatureCaption = $signatureCaption ?? "Student's Signature Over Printed Name/ Date";
    $fallbackSignerName = $fallbackSignerName ?? 'STUDENT NAME';
    $showGuardian = empty($hideGuardianBlock ?? false);
    $postRemarksNote = trim((string) ($postRemarksNote ?? ''));
@endphp

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">

    <title>
        Declaration of Medical Information and Data Subject Consent Form
    </title>

    <style>
        @page {
            size: 8.5in 11in;
            margin: 0.55in 0.42in 0.90in 0.42in;
        }

        * {
            box-sizing: border-box;
        }

        html,
        body {
            margin: 0;
            padding: 0;
        }

        body {
            color: #000000;
            font-family: Arial, Helvetica, sans-serif;
            font-size: 10.5pt;
            line-height: 1.38;
        }


        /* =========================================================
           HEADER
        ========================================================= */

        .header-table {
            width: 92%;
            margin-left: 50px;
            margin-top:30px;
            border-collapse: collapse;
            table-layout: fixed;
        }

        .header-table td {
            border: 0;
            padding: 0;
            vertical-align: middle;
        }

        .header-main {
            width: 82%;
        }

        .header-brand-table {
            width: auto;
            margin: 0;
            border-collapse: collapse;
            table-layout: auto;
        }

        .header-brand-table td {
            border: 0;
            padding: 0;
            vertical-align: middle;
        }

        .header-logo-left {
            width: 94px;
            padding: 0 !important;
            text-align: left;
            vertical-align: middle;
        }

        .header-logo-left img {
            display: block;
            width: 95px;
            height: 95px;
            margin: 0;
            object-fit: contain;
        }

        .header-center {
            width: 365px;
            padding-left: 9px !important;
            text-align: left;
            vertical-align: middle;
            line-height: 1.12;
        }

        .header-center p {
            padding: 0;
        }

        .header-center .gov-title {
            margin: 0;
            font-family: "Times New Roman", Times, serif;
            font-size: 10pt;
            font-weight: normal;
            color: #000000;
        }

        .header-center .univ-title {
            margin: 1px 0;
            font-family: "Times New Roman", Times, serif;
            font-size: 10pt;
            font-weight: bold;
            line-height: 1.05;
            color: #000000;
            text-transform: uppercase;
        }

        .header-center .office-title {
            margin: 0;
            font-family: "Times New Roman", Times, serif;
            font-size: 10pt;
            font-weight: normal;
            color: #000000;
        }

        .header-center .dept-title {
            margin: 1px 0 0;
            font-family: "Times New Roman", Times, serif;
            font-size: 12pt;
            font-weight: bold;
            color: #000000;
        }

        .header-logo-right {
            width: 18%;
            padding: 0 !important;
            text-align: right;
            vertical-align: middle;
        }

        .header-logo-right img {
            display: inline-block;
            width: 105px;
            height: 95px;
            margin-right: 50px;
            object-fit: contain;
        }

        .header-campus-tag {
           
         
            margin-left: 250px;
            font-family: Arial, Helvetica, sans-serif;
            font-size: 10.5pt;
            font-weight: normal;
            line-height: 1.15;
            text-align: center;
            color: #000000;
        }

        .header-divider {
            width: 92%;
            margin: 2px auto 13px;
            border: 0;
            border-top: 1px solid #b8bec7;
        }


        /* =========================================================
           FORM TITLE
        ========================================================= */

        .form-title {
            margin: 12px 0 14px;
            font-family: Arial, Helvetica, sans-serif;
            font-size: 13.5pt;
            font-weight: bold;
            line-height: 1.15;
            text-align: center;
            color: #000000;
        }


        /* =========================================================
           DECLARATION BODY
        ========================================================= */

        .declaration-body {
            width: 76%;
            margin: 0 auto;
            padding: 0;
        }

        .declaration-p {
            margin: 0 0 10px;
            font-family: Arial, Helvetica, sans-serif;
            font-size: 12pt;
            line-height: 1.38;
            text-align: justify;
            text-justify: inter-word;
            color: #000000;
            text-indent: 34px;
        }

        .declaration-p u {
            font-weight: normal;
            text-decoration: underline;
            text-underline-offset: 2px;
        }


        /* =========================================================
           SIGNATURE SECTION
        ========================================================= */

        .signature-block-table {
            width: 76%;
            margin: 20px auto 0;
            border-collapse: collapse;
            table-layout: fixed;
        }

        .signature-block-table td {
            border: 0;
            padding: 0;
            vertical-align: top;
        }

        .sig-col-left {
            width: 42%;
        }

        .sig-col-right {
            width: 58%;
            padding-left: 10px !important;
        }

        .sig-box {
            position: relative;
            margin-bottom: 9px;
            text-align: center;
        }

        .student-signature-preview {
            position: relative;
            display: block;
            max-width: 165px;
            max-height: 40px;
            margin: 0 auto -16px;
            object-fit: contain;
        }

        .sig-underline {
            position: relative;
            min-height: 16px;
            padding-bottom: 1px;
            border-bottom: 1px solid #000000;
            font-family: Arial, Helvetica, sans-serif;
            font-size: 12pt;
            font-weight: normal;
            text-transform: uppercase;
        }

        .sig-caption {
            margin-top: 2px;
            font-family: Arial, Helvetica, sans-serif;
            font-size: 12pt;
            line-height: 1.12;
            color: #000000;
        }


        /* =========================================================
           REMARKS
        ========================================================= */

        .remarks-line {
            display: table;
            width: 100%;
            margin: 35px 0 8px;
            font-family: Arial, Helvetica, sans-serif;
            font-size: 12pt;
            line-height: 1.15;
            text-align: left;
            color: #000000;
        }

        .remarks-label,
        .remarks-fill {
            display: table-cell;
            vertical-align: bottom;
        }

        .remarks-label {
            width: 70px;
            white-space: nowrap;
        }

        .remarks-fill {
            height: 18px;
            border-bottom: 1px solid #000000;
        }


        /* =========================================================
           GUARDIAN / MINOR
        ========================================================= */

        .guardian-signature-box {
            margin-top: 12px !important;
            margin-bottom: 0 !important;
        }

        .minor-note {
            margin-top: 13px;
            font-family: Arial, Helvetica, sans-serif;
            font-size: 12pt;
            line-height: 1.15;
            text-align: justify;
            color: #000000;
        }


        /* =========================================================
           FOOTER
        ========================================================= */

        .footer-table {
            position: fixed;
            left: 100px;
            right: 100px;
            bottom: 100px;
            width: 76%;
            border-collapse: collapse;
            table-layout: fixed;
        }

        .footer-table td {
            border: 0;
            padding: 0;
            vertical-align: bottom;
        }

        .footer-left {
            width: 80%;
            font-family: Arial, Helvetica, sans-serif;
            font-size: 7.5pt;
            line-height: 1.17;
            color: #374151;
        }

        .footer-left a {
            color: #1d4ed8;
            text-decoration: underline;
        }

        .footer-motto {
            margin-top: 3px;
            font-size: 12pt;
            font-family:'Times New Roman';
            line-height: 1.08;
            color: #111827;
            letter-spacing: 0.01em;
            text-transform: uppercase;
        }
        .footer-motto2 {
            margin-top: 5px;
            font-family:'Times New Roman';
            font-size: 12pt;
            line-height: 1.08;
            color: #111827;
            letter-spacing: 0.01em;
            text-transform: uppercase;
        }

        .footer-right {
            width: 42%;
            text-align: right;
            vertical-align: bottom;
        }

        .footer-badge-img {
            display: inline-block;
            width: 270px;
            height: 80px;
            object-fit: contain;
        }


        /* =========================================================
           PDF / DOMPDF SAFETY
        ========================================================= */

        table {
            border-spacing: 0;
        }

        tr,
        td,
        .sig-box {
            page-break-inside: avoid;
        }

        .header-table,
        .header-brand-table,
        .signature-block-table {
            page-break-inside: avoid;
        }

        .form-title {
            page-break-after: avoid;
        }

        .declaration-p {
            page-break-inside: avoid;
        }
    </style>

</head>


<body>


    {{-- =========================================================
         FIXED FOOTER
         IMPORTANT:
         Keep this near the beginning of BODY for DomPDF.
    ========================================================= --}}

    <table class="footer-table">

        <tr>

            <td class="footer-left">

                Gen. Santos Avenue, Lower Bicutan, Taguig City 1632

                <br>

                Direct Line: (02) 8837 5858 to 60 |

                Email:

                <a href="mailto:taguig@pup.edu.ph">
                    taguig@pup.edu.ph
                </a>

                <br>

                Website:

                <a href="https://www.pup.edu.ph">
                    www.pup.edu.ph
                </a>

                |

                Inquiries:

                <a href="https://bit.ly/PUPSINTA">
                    https://bit.ly/PUPSINTA
                </a>

                <div class="footer-motto">
                    A Leading Comprehensive
                </div>

                <div class="footer-motto2">
                     Polytechnic University in Asia
                </div>

            </td>


            <td class="footer-right">

                @if($hasFooterImg)

                    <img
                        src="{{ $footerImg }}"
                        alt="Certifications"
                        class="footer-badge-img"
                    >

                @endif

            </td>

        </tr>

    </table>



    {{-- =========================================================
         HEADER
    ========================================================= --}}

    <table class="header-table">

        <tr>


            {{-- PUP LOGO + UNIVERSITY INFORMATION --}}

            <td class="header-main">

                <table class="header-brand-table">

                    <tr>


                        <td class="header-logo-left">

                            @if($hasPupLogo)

                                <img
                                    src="{{ $pupLogo }}"
                                    alt="PUP Logo"
                                >

                            @endif

                        </td>


                        <td class="header-center">

                            <p class="gov-title">
                                Republic of the Philippines
                            </p>


                            <p class="univ-title">
                                POLYTECHNIC UNIVERSITY OF THE PHILIPPINES
                            </p>


                            <p class="office-title">
                                Office of the Vice President for Administration
                            </p>


                            <p class="dept-title">
                                Medical Services Department
                            </p>

                        </td>


                    </tr>

                </table>

            </td>


            {{-- BAGONG PILIPINAS --}}

            <td class="header-logo-right">
                @if($hasBpLogo)
                    <img
                        src="{{ $bpLogo }}"
                        alt="Bagong Pilipinas Logo"
                    >

                @endif
            </td>
        </tr>
    </table>



    <div class="header-campus-tag">
        Taguig Campus
    </div>


    <hr class="header-divider">



    {{-- =========================================================
         FORM TITLE
    ========================================================= --}}

    <div class="form-title">
        Declaration of Medical Information and Data Subject Consent Form
    </div>



    {{-- =========================================================
         DECLARATION
    ========================================================= --}}

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



    {{-- =========================================================
         SIGNATURE BLOCK
    ========================================================= --}}

    <table class="signature-block-table">

        <tr>


            <td class="sig-col-left"></td>


            <td class="sig-col-right">


                {{-- STUDENT SIGNATURE --}}

                <div class="sig-box">

                    @if(!empty($studentSignatureSrc))

                        <img
                            src="{{ $studentSignatureSrc }}"
                                alt="{{ $signatureAlt }}"
                                class="student-signature-preview"
                        >

                    @endif


                    <div class="sig-underline">

                        {{ $fullName !== ''
                            ? $fullName
                            : $fallbackSignerName
                        }}

                        /

                        {{ $sigDate }}

                    </div>


                    <div class="sig-caption">
                        {{ $signatureCaption }}
                    </div>


                </div>



                {{-- REMARKS --}}

                <div class="remarks-line">

                    <span class="remarks-label">Remarks:</span>

                    <span class="remarks-fill">{{ $remarks ?? '' }}</span>

                </div>



                {{-- GUARDIAN SIGNATURE --}}

                @if($showGuardian)
                    <div class="sig-box guardian-signature-box">

                        @if($isMinorStudent && !empty($guardianSignatureSrc))

                            <img
                                src="{{ $guardianSignatureSrc }}"
                                alt="Guardian Signature"
                                class="student-signature-preview"
                            >

                        @endif


                        <div class="sig-underline">

                            {{ $isMinorStudent && $guardian !== ''
                                ? $guardian . ' / ' . $sigDate
                                : ''
                            }}

                        </div>


                        <div class="sig-caption">
                            Guardian's Signature Over Printed Name/ Date
                        </div>


                        <div class="minor-note">
                            Both student and guardian will affix their signature if the student is aged below 18 years old.
                        </div>


                    </div>
                @elseif($postRemarksNote !== '')
                    <div class="minor-note">
                        {{ $postRemarksNote }}
                    </div>
                @endif


            </td>


        </tr>

    </table>


    {{-- IMPORTANT:
         Footer is intentionally NOT repeated here.
         It is fixed at the beginning of BODY.
    --}}


</body>

</html>
