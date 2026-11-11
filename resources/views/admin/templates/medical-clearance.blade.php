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
        @page { size: letter portrait; margin: 0.55in 0.65in; }
        * { box-sizing: border-box; }
        body { margin: 0; color: #000; background: #fff; font-family: Arial, Helvetica, sans-serif; font-size: 11pt; line-height: 1.65; }
        .control-number { text-align: right; font-size: 9pt; margin-bottom: 7pt; }
        .control-line { display: inline-block; min-width: 82pt; border-bottom: 0.6pt solid #000; line-height: 1.2; text-align: center; }
        .letterhead { position: relative; width: 100%; min-height: 64pt; }
        .logo { position: absolute; left: 4pt; top: 0; width: 64pt; height: 64pt; }
        .heading { margin: 0 70pt; text-align: center; font-family: 'Times New Roman', Times, serif; font-size: 10pt; line-height: 1.15; padding-top: 3pt; }
        .university { font-size: 10.5pt; }
        .campus { text-decoration: underline; }
        h1 { font-family: 'Times New Roman', Times, serif; font-size: 16pt; text-align: center; margin: 19pt 0 13pt; line-height: 1.2; }
        .date-row { text-align: right; margin-bottom: 27pt; }
        .date-line { display: inline-block; min-width: 135pt; text-align: center; border-bottom: 0.6pt solid #000; line-height: 1.3; }
        .salutation { margin: 0 0 19pt; }
        .certification { margin: 0; text-align: justify; text-indent: 35pt; line-height: 2; }
        .patient { display: inline; border-bottom: 0.6pt solid #000; font-weight: bold; overflow-wrap: break-word; }
        .blank-patient { display: inline-block; width: 270pt; border-bottom: 0.6pt solid #000; line-height: 1.3; }
        .purpose-paragraph { margin: 27pt 0 0; text-indent: 35pt; line-height: 2; text-align: justify; }
        .purpose { border-bottom: 0.6pt solid #000; font-weight: bold; }
        .blank-purpose { display: inline-block; width: 215pt; border-bottom: 0.6pt solid #000; line-height: 1.3; }
        .signature-table { width: 100%; border-collapse: collapse; margin-top: 65pt; table-layout: fixed; page-break-inside: avoid; }
        .signature-spacer { width: 55%; }
        .signature { width: 45%; text-align: center; font-size: 10pt; }
        .physician-line { border-bottom: 0.6pt solid #000; min-height: 17pt; line-height: 1.4; }
        .physician-line span { display: inline-block; min-width: 140pt; }
        .license { margin-top: 5pt; text-align: left; }
        .license-line { display: inline-block; min-width: 145pt; border-bottom: 0.6pt solid #000; line-height: 1.3; text-align: center; }
        .preview-note { margin-top: 52pt; color: #777; text-align: center; font-size: 8pt; letter-spacing: 0.7pt; }
    </style>
</head>
<body>
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
                <div class="physician-line"><span>{{ $value('physician_name') ?: "\u{00A0}" }}</span> M.D.</div>
                <div class="license">Lic No. <span class="license-line">{{ $value('license_number') ?: "\u{00A0}" }}</span></div>
            </td>
        </tr>
    </table>
    @if($templatePreview)<div class="preview-note">TEMPLATE PREVIEW ONLY - NOT AN ISSUED MEDICAL CLEARANCE</div>@endif
</body>
</html>
