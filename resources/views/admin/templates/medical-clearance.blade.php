@php
    /**
     * Print-only view: receives frozen issuance values; no record lookup or signature wiring.
     * Optional $clearance keys: control_number, date, name, age, sex, purpose,
     * physician_name, license_number. All values are escaped and blank by default.
     */
    $certificate = is_array($clearance ?? null) ? $clearance : [];
    $value = static fn (string $key): string => trim((string) ($certificate[$key] ?? ''));
    $logoPath = public_path('images/pup_logo_print.jpg');
    $logoData = is_file($logoPath) ? 'data:image/jpeg;base64,' . base64_encode(file_get_contents($logoPath)) : '';
    $templatePreview = $templatePreview ?? true;
@endphp
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Medical Clearance{{ $templatePreview ? ' - Template' : '' }}</title>
    <style>
        @page { size: letter portrait; margin: 0.35in 0.95in; }
        * { box-sizing: border-box; }
        body { margin: 0; color: #000; background: #fff; font-family: Arial, Helvetica, sans-serif; font-size: 11.5pt; line-height: 1.4; }
        .control-number { text-align: right; font-size: 9.5pt; margin-bottom: 7pt; }
        .control-line { display: inline-block; min-width: 82pt; border-bottom: 0.6pt solid #000; line-height: 1.2; text-align: center; }
        .letterhead { position: relative; width: 100%; min-height: 58pt; }
        .logo { position: absolute; left: 40pt; top: -3pt; width: 64.5pt; height: 64.5pt; }
        .heading { margin: 0 70pt; text-align: center; font-family: 'Times New Roman', Times, serif; font-size: 10.5pt; line-height: 1.15; padding-top: 3pt; }
        .university { font-size: 11pt; }
        .campus { text-decoration: underline; }
        h1 { font-family: 'Times New Roman', Times, serif; font-size: 16.5pt; text-align: center; margin: 13pt 0 9pt; line-height: 1.2; }
        .date-row { text-align: right; margin-bottom: 16pt; }
        .date-line { display: inline-block; min-width: 135pt; text-align: center; border-bottom: 0.6pt solid #000; line-height: 1.3; }
        .salutation { margin: 0 0 12pt; }
        .certification { margin: 0; text-align: justify; text-indent: 35pt; line-height: 1.65; }
        .patient { display: inline; border-bottom: 0.6pt solid #000; font-weight: bold; overflow-wrap: break-word; }
        .blank-patient { display: inline-block; width: 270pt; border-bottom: 0.6pt solid #000; line-height: 1.3; }
        .purpose-paragraph { margin: 16pt 0 0; text-indent: 35pt; line-height: 1.65; text-align: justify; }
        .purpose { border-bottom: 0.6pt solid #000; font-weight: bold; }
        .blank-purpose { display: inline-block; width: 215pt; border-bottom: 0.6pt solid #000; line-height: 1.3; }
        .signature-table { width: 100%; border-collapse: collapse; margin-top: 22pt; table-layout: fixed; page-break-inside: avoid; }
        .signature-spacer { width: 55%; }
        .signature { width: 45%; text-align: center; font-size: 10.5pt; }
        .physician-line { display: table; width: 100%; min-height: 17pt; line-height: 1.4; }
        .physician-name { display: table-cell; border-bottom: 0.6pt solid #000; vertical-align: bottom; }
        .physician-suffix { display: table-cell; width: 28pt; padding-left: 4pt; white-space: nowrap; vertical-align: bottom; text-align: right; }
        .license { position: relative; margin-top: 5pt; text-align: left; }
        .license-label { position: absolute; top: 0; left: 0; }
        .license-line { display: block; margin-left: 35pt; border-bottom: 0.6pt solid #000; line-height: 1.3; text-align: center; }
        .preview-note { margin-top: 12pt; color: #777; text-align: center; font-size: 8.5pt; letter-spacing: 0.7pt; }
        /* Preserve the half-page boundary for long values without shrinking the requested text sizes. */
        .extended-content .purpose-paragraph { margin-top: 12pt; }
        .extended-content .signature-table { margin-top: 16pt; }
    </style>
</head>
<body @if(mb_strlen($value('name') . $value('purpose')) > 180)class="extended-content"@endif>
    <div class="control-number">Control No. <span class="control-line">{{ $value('control_number') ?: "\u{00A0}" }}</span></div>
    <div class="letterhead">
        @if($logoData)<img class="logo" src="{{ $logoData }}" alt="PUP seal">@endif
        <div class="heading">
                <div>Republic of the Philippines</div>
                <div class="university">POLYTECHNIC UNIVERSITY OF THE PHILIPPINES</div>
                <div>MEDICAL SERVICES DEPARTMENT</div>
                <div>Sta. Mesa, Manila</div>
                <div class="campus">Taguig Campus</div>
        </div>
    </div>

    <h1>MEDICAL CLEARANCE</h1>
    <div class="date-row">Date <span class="date-line">{{ $value('date') ?: "\u{00A0}" }}</span></div>
    <p class="salutation">To Whom It May Concern:</p>
    <p class="certification">
        This is to certify that
        @if($value('name'))
            <span class="patient">{{ $value('name') }}@if($value('age') !== '' || $value('sex') !== '')&nbsp;&nbsp; {{ $value('age') }} / {{ $value('sex') }}@endif</span>
        @else
            <span class="blank-patient">&nbsp;</span>
        @endif
        has been examined by the undersigned and found to be physically fit at the time of examination.
    </p>
    <p class="purpose-paragraph">
        This certification is issued upon the request for
        @if($value('purpose'))<span class="purpose">{{ $value('purpose') }}</span>@else<span class="blank-purpose">&nbsp;</span>@endif
        purpose.
    </p>
    <table class="signature-table" role="presentation">
        <tr>
            <td class="signature-spacer"></td>
            <td class="signature">
                <div class="physician-line"><span class="physician-name">{{ $value('physician_name') ?: "\u{00A0}" }}</span><span class="physician-suffix">M.D.</span></div>
                <div class="license"><span class="license-label">Lic No.</span><span class="license-line">{{ $value('license_number') ?: "\u{00A0}" }}</span></div>
            </td>
        </tr>
    </table>
    @if($templatePreview)<div class="preview-note">TEMPLATE PREVIEW ONLY - NOT AN ISSUED MEDICAL CLEARANCE</div>@endif
</body>
</html>
