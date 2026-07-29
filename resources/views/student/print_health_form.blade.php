<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Health Information Form</title>
    <style>
        @page { size: 8.5in 13in; margin: 0; }
        * { box-sizing: border-box; }
        body { margin: 0; padding: 0; background: #fff; color: #000; font-family: Arial, Helvetica, sans-serif; }
        .print-container { width: auto; margin: 0; padding: 0.28in 0.42in 0.2in; line-height: 1.18; }
        .print-page { position: relative; }
        .document-code { position: absolute; top: -9px; right: 0; z-index: 2; width: 125px; font-family: Arial, sans-serif; font-size: 10px; line-height: 1.15; text-align: left; }
        .official-header-table { width: 100%; height: 112px; border-collapse: collapse; table-layout: fixed; }
        .official-header-table td { padding: 0; border: 0; vertical-align: top; }
        .official-logo-cell { width: 20%; padding: 4px 0 0 15px !important; text-align: center; }
        .official-heading-cell { width: 60%; padding-top: 15px !important; }
        .official-photo-cell { position: relative; width: 20%; padding: 0 2px 0 8px !important; text-align: right; vertical-align: top !important; }
        .logo { width: 88px; height: 88px; object-fit: contain; }
        .official-heading-cell { font-family: "Times New Roman", Times, serif; }
        .official-heading-cell p { margin: 0; font-size: 13px; line-height: 1.17; }
        .official-heading-cell .univ-name { font-size: 16px; font-weight: normal; }
        .official-heading-cell .dept-name { margin-top: 2px; font-size: 21px; font-weight: normal; }
        .photo-box { position: absolute; top: 29px; right: 2px; box-sizing: border-box; width: 130px; height: 120px; padding: 24px 8px 0; overflow: hidden; border: 1px solid #000; font-size: 13px; line-height: 1.25; text-align: center; white-space: normal; word-wrap: break-word; }
        .official-title-table { position: relative; width: 60%; margin-top: -5px; margin-left: 23.5%; border-collapse: collapse; table-layout: fixed; }
        .official-title-table::before { position: absolute; top: 0; left: -33.333%; width: 120%; border-top: 2px solid #000; content: ""; }
        .official-title-table td { padding: 9px 0 5px; border: 0; font-family: Arial, Helvetica, sans-serif; font-size: 17px; font-weight: 700; font-style: italic; text-align: center; }
        .section-header { width: 80%; margin-top: 8px; padding: 3px 0 4px; border: 0; font-family: Arial, Helvetica, sans-serif; font-size: 14px; font-weight: bold; font-style: italic; text-transform: uppercase; }
        .official-title-table + .section-header { margin-top: 11px; }
        .student-information-table { width: 100%; border-collapse: separate; border-spacing: 0 5px; table-layout: fixed; }
        .student-information-table td { padding: 0; border: 0; vertical-align: bottom; font-size: 13px; }
        .student-name-cell { width: 62% !important; padding-right: 8px !important; }
        .student-number-label { width: 14% !important; padding-right: 4px !important; font-weight: bold; white-space: nowrap; }
        .student-number-value { width: 24% !important; padding-left: 4px !important; border-bottom: 1px solid #000 !important; font-weight: bold; }
        .compound-information-table { width: 100%; border-collapse: collapse; table-layout: fixed; }
        .compound-information-table td { padding: 0 3px 1px 0; font-size: 12px; vertical-align: bottom; }
        .blood-email-table { width: 100%; border-collapse: collapse; table-layout: fixed; }
        .blood-email-table td { padding-top: 0; padding-bottom: 1px; font-size: 12px; vertical-align: bottom; }
        .blood-type-label { width: 15%; }
        .blood-type-value { width: 28%; }
        .blood-email-table .email-label { position: relative; left: -10px; width: 14%; padding-left: 0 !important; }
        .email-value { position: relative; left: -10px; width: 43%; overflow: hidden; font-size: 11px !important; white-space: nowrap; text-overflow: clip; }
        .student-full-name { display: inline-block; width: 86%; vertical-align: bottom; }
        .home-address-label-cell { width: 15% !important; }
        .home-address-value-cell { width: 47% !important; }
        .line-label { padding-right: 4px !important; font-weight: 700; white-space: nowrap; }
        .line-value { padding-left: 4px !important; border-bottom: 1px solid #000 !important; font-weight: bold; overflow-wrap: break-word; word-wrap: break-word; }
        .school-year-label,
        .email-label,
        .cellphone-label { padding-left: 10px !important; }
        .guardian-row { padding-top: 1px !important; white-space: nowrap; }
        .guardian-value { display: inline-block; width: 68.4%; white-space: nowrap; vertical-align: bottom; }
        .contact-information-row { width: 100%; white-space: nowrap; }
        .contact-information-row .contact-label,
        .contact-information-row .contact-value { display: inline-block; font-size: 13px; vertical-align: bottom; }
        .contact-information-row .contact-label { padding: 0 3px 0 0 !important; font-weight: 700; }
        .contact-information-row .landline-label { width: 72px; }
        .contact-information-row .landline-value { width: 42%; padding: 0 3px 0 2px !important; border-bottom: 1px solid #000; font-weight: 700; }
        .contact-information-row .cellphone-label { width: 92px; margin-left: 48px; }
        .contact-information-row .cellphone-value { width: 26.8%; padding: 0 3px !important; border-bottom: 1px solid #000; font-weight: 700; }
        .compound-row { padding: 0 !important; }
        .age-label { width: 5%; }
        .age-value { width: 8%; }
        .sex-label { width: 5%; padding-left: 5px !important; }
        .sex-value { width: 11%; }
        .civil-label { width: 10%; padding-left: 5px !important; }
        .civil-value { width: 12%; }
        .course-label { width: 15%; padding-left: 5px !important; }
        .course-value { width: 34%; padding-bottom: 2px !important; font-size: 10px !important; line-height: 1.02; white-space: normal; overflow-wrap: break-word; word-wrap: break-word; }
        .row { display: block; width: 100%; margin: 5px 0; }
        .field { display: inline-block; min-height: 13px; min-width: 90px; padding: 0 4px; border-bottom: 1px solid #000; color: #000; font-size: 13px; font-weight: bold; vertical-align: bottom; }
        .label, .labels { white-space: nowrap; font-size: 13px; }
        .label { font-weight: 700; }
        .print-page .row,
        .print-page .field,
        .print-page .label,
        .print-page .labels,
        .print-page .check-item,
        .print-page .medical-history-instruction,
        .print-page .medical-subsection-title,
        .print-page .cert-text,
        .print-page .sig-line,
        .print-page .physician-section {
            font-size: 13px !important;
        }
        .medical-subsection-title { font-weight: 700; line-height: 1.18; }
        .medical-subsection-heading { margin: 5px 0 3px; font-weight: 700; text-transform: none; }
        .disability-row .check-item { font-weight: 700; }
        .allergy-declaration { margin: 2px 0 3px 18px; font-size: 13px; line-height: 1.18; }
        .medicine-allergy-table { width: 100%; margin: 5px 0; border-collapse: collapse; table-layout: fixed; }
        .medicine-allergy-table td { padding: 0 3px 0 0; border: 0; font-size: 13px; line-height: 15px; vertical-align: middle; white-space: nowrap; }
        .medicine-allergy-table .medicine-label-cell { width: 10%; padding-right: 6px !important; font-weight: 700; }
        .medicine-allergy-table .medicine-item-cell { width: 12%; }
        .medicine-allergy-table .medicine-item-cell-wide { width: 15%; }
        .medicine-allergy-table td:nth-child(5) { width: 18%; }
        .medicine-allergy-table .medicine-other-cell { width: 22%; }
        .medicine-allergy-table .box-ui { margin-right: 5px; vertical-align: middle; }
        .medicine-other-field { display: inline-block; width: 62%; min-width: 0; padding: 0; }
        .medical-history-instruction { margin: 2px 0 3px 24px; font-size: 12px; font-style: italic; }
        .medical-check-table { width: 96%; margin: 3px 0 5px 4%; border-collapse: collapse; table-layout: fixed; }
        .medical-check-table td { width: 25%; padding: 2px 3px; border: 0; font-size: 12px; vertical-align: middle; }
        .write-line { display: inline-block; width: 72%; min-height: 9px; border-bottom: 1px solid #000; font-weight: bold; }
        .check-item { display: inline-block; margin-right: 10px; font-size: 12px; white-space: nowrap; vertical-align: middle; }
        .box-ui { display: inline-block; width: 13px; height: 13px; border: 1px solid #000; font-family: Arial, sans-serif; font-weight: bold; line-height: 12px; text-align: center; vertical-align: middle; }
        .social-history-table { width: 31%; margin: 2px 0 2px 2%; border-collapse: collapse; table-layout: fixed; }
        .social-history-table td { padding: 1px 2px; border: 0; font-size: 12px; vertical-align: middle; }
        .social-history-table .social-label { width: 58%; }
        .social-history-table td:nth-child(2),
        .social-history-table td:nth-child(3) { width: 21%; white-space: nowrap; }
        .covid-layout-table { width: 82%; margin: 4px 0 0 3%; border-collapse: collapse; table-layout: fixed; }
        .covid-layout-table td { padding: 0; border: 0; vertical-align: top; }
        .covid-label-cell { width: 38%; padding-right: 10px !important; }
        .covid-label-cell > .medical-subsection-title { position: relative; left: -22px; white-space: nowrap; }
        .vaccinated-choice { margin: 3px 0 0 12px; }
        .vax-table { position: relative; top: -14px; left: 100px; width: 100%; margin-top: 0; border-collapse: collapse; table-layout: fixed; }
        .vax-table th, .vax-table td { height: 18px; padding: 2px 4px 0; border: 1px solid #000; font-size: 10px; line-height: 1; text-align: center; white-space: nowrap; vertical-align: middle; }
        .vax-table th:first-child,
        .vax-table td:first-child { width: 40%; text-align: left; }
        .vax-table td:first-child { font-weight: 700; }
        .vax-table th:nth-child(2),
        .vax-table td:nth-child(2) { width: 30%; }
        .vax-table th:nth-child(3),
        .vax-table td:nth-child(3) { width: 30%; }
        .cert-text { margin-top: 8px; font-size: 11px; font-style: italic; line-height: 1.22; text-align: justify; }
        .cert-text-first { margin-top: 16px; }
        .signature-table { width: 92%; margin: 25px auto 0; border-collapse: separate; border-spacing: 20px 0; table-layout: fixed; page-break-inside: avoid; }
        .signature-table td { padding: 0; border: 0; vertical-align: bottom; text-align: center; }
        .sig-image { display: block; width: 190px; max-width: 100%; height: auto; max-height: 64px; margin: 0 auto -10px; }
        .signature-space { height: 18px; }
        .signature-date-space { display: flex; align-items: flex-end; justify-content: center; padding-bottom: 3px; font-weight: bold; }
        .sig-line { min-height: 11px; margin-bottom: 2px; border-bottom: 1px solid #000; font-size: 11px; font-weight: bold; text-transform: uppercase; }
        .student-signature-name { overflow: hidden; line-height: 11px; white-space: nowrap; }
        .signature-caption { position: relative; top: -2px; min-height: 17px; font-size: 12.5px; font-weight: 700; line-height: 1.1; text-align: center; }
        .physician-section { width: 100%; clear: both; margin-top: 14px !important; padding: 12px 24px 0 !important; border: 0 !important; border-top: 1px dotted #000 !important; break-inside: avoid; page-break-before: avoid; page-break-inside: avoid; }
        .signature-physician-block { margin-top: 10px; }
        .physician-section,
        .physician-section * { font-family: Arial, Helvetica, sans-serif; }
        .physician-section > p { margin: 0 0 1px !important; font-weight: 700 !important; }
        .physician-check-instruction { margin-bottom: 10px !important; font-size: 12.5px; font-style: italic; font-weight: 700; text-align: center; }
        .physician-clearance-table,
        .physician-signature-table { width: 94%; margin-left: auto; margin-right: auto; border-collapse: collapse; table-layout: fixed; }
        .physician-clearance-table td,
        .physician-signature-table td { padding: 1px 3px; border: 0; font-size: 12px; font-weight: 700; vertical-align: middle; }
        .physician-clearance-label { width: 20%; font-weight: bold; }
        .physician-clearance-table td:nth-child(2) { width: 13%; white-space: nowrap; }
        .physician-pending-cell { width: 67%; white-space: nowrap; }
        .physician-box { width: 9px !important; height: 9px !important; line-height: 8px !important; margin-right: 3px; }
        .physician-reason-line { display: inline-block; width: 55%; min-height: 9px; border-bottom: 1px solid #000; font-style: italic; vertical-align: bottom; }
        .physician-signature-table { margin-top: 12px; }
        .physician-signature-table td:first-child { width: 34%; padding-right: 12px; }
        .physician-signature-table td:last-child { width: 66%; padding-left: 12px; }
        .physician-signature-line { min-height: 15px; border-bottom: 1px solid #000; font-size: 12px; font-weight: bold; text-align: center; }
        .physician-signature-label { margin-top: 1px; font-size: 12px; font-weight: 700; text-align: center; }
        .print-action-bar { display: flex; justify-content: flex-end; gap: 8px; width: min(8.5in, 100%); margin: 0 auto; padding: 10px 12px 0; }
        .print-action-button { display: inline-flex; align-items: center; justify-content: center; padding: 9px 18px; border: 1px solid #800000; border-radius: 6px; background: #fff; color: #800000; font-size: 13px; font-weight: bold; text-decoration: none; }
        .print-action-button.is-download { background: #800000; color: #fff; }
        .print-action-button:hover { background: #facc15; color: #111827; }
        @media print { .no-print { display: none !important; } }
    </style>
</head>
<body>
    @unless($pdfMode ?? false)
        <div class="print-action-bar no-print">
            @if($adminViewer ?? false)
                <button class="print-action-button is-download" type="button" onclick="window.print()">Print</button>
                <button class="print-action-button" type="button" onclick="window.close()">Close View</button>
            @else
                <a class="print-action-button is-download" href="{{ route('student.health_form.download') }}">Download PDF</a>
                <a class="print-action-button" href="{{ url('/student/account?view=health-record') }}">Close</a>
            @endif
        </div>
    @endunless
    @php
        $studentPrintCopy = !($adminViewer ?? false);
        $healthFormLogo = ($pdfMode ?? false)
            ? public_path('images/pup_logo_print.jpg')
            : asset('images/pup_logo_print.jpg');
    @endphp
    @include('admin.partials.health_form_body')
    @if(!($pdfMode ?? false) && !($adminViewer ?? false))
        <script>
            window.addEventListener('load', function () {
                window.setTimeout(function () {
                    window.print();
                }, 250);
            });
        </script>
    @endif
</body>
</html>
