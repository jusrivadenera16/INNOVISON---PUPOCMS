<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Health Examination Record</title>
    <style>
        @page { size: 8.5in 13in; margin: 0; }
        * { box-sizing: border-box; }
        body { margin: 0; background: #fff; color: #000; font-family: Arial, Helvetica, sans-serif; }
        .print-action-bar { display: flex; justify-content: flex-end; gap: 8px; width: min(8.5in, 100%); margin: 0 auto; padding: 10px 12px 0; }
        .print-action-button { display: inline-flex; align-items: center; justify-content: center; padding: 9px 18px; border: 1px solid #800000; border-radius: 6px; background: #fff; color: #800000; font-size: 13px; font-weight: bold; text-decoration: none; }
        .print-action-button.is-download { background: #800000; color: #fff; }
        .print-container { width: auto; margin: 0; padding: 0.22in 0.38in 0.14in; line-height: 1.06; }
        .print-page { position: relative; page-break-after: avoid; page-break-inside: avoid; }
        .document-code { position: absolute; top: -2px; right: 7px; z-index: 2; width: 118px; font-size: 8px; line-height: 1.12; text-align: left; }
        .official-header-table { width: 100%; height: 88px; border-collapse: collapse; table-layout: fixed; }
        .official-header-table td { padding: 0; border: 0; vertical-align: top; }
        .official-logo-cell { width: 18%; padding: 3px 0 0 18px !important; text-align: center; }
        .official-heading-cell { width: 64%; padding-top: 7px !important; font-family: "Times New Roman", Times, serif; }
        .official-spacer-cell { width: 18%; }
        .logo { width: 72px; height: 72px; object-fit: contain; }
        .official-heading-cell p { margin: 0; font-size: 14.5px; line-height: 1.08; }
        .official-heading-cell .univ-name { font-size: 17.3px; }
        .official-heading-cell .dept-name { margin-top: 1px; font-size: 20.5px; }
        .official-heading-cell .campus-name { text-align: center; font-weight: bold; }
        .title-rule { width: 100%; border-top: 2px solid #000; margin: 0 0 12px; }
        .form-title { margin: 0; font-family: Arial, Helvetica, sans-serif; font-size: 16.5px; font-weight: bold; font-style: italic; text-align: center; text-transform: uppercase; }
        .form-subtitle { margin: 2px 0 13px; font-family: Arial, Helvetica, sans-serif; font-size: 15.5px; font-weight: bold; font-style: italic; text-align: center; text-transform: uppercase; }
        .record-grid { width: 100%; height: 720.3pt; border-collapse: collapse; table-layout: fixed; border: 1px solid #000; page-break-inside: avoid; font-family: Arial, Helvetica, sans-serif; }
        .record-grid tr { height: 14.7pt; max-height: 14.7pt; }
        .record-grid td { height: 14.7pt; max-height: 14.7pt; border: 1px solid #000; padding: 0 3px; font-size: 12px; line-height: .96; vertical-align: middle; overflow: hidden; white-space: nowrap; text-decoration: none !important; }
        .record-grid span,
        .record-grid strong { text-decoration: none !important; }
        .record-grid .left-col,
        .record-grid .right-col { width: 50%; }
        .record-grid .top-info .label { font-weight: 400; }
        .section-cell { font-family: Arial, Helvetica, sans-serif; font-size: 14.6px; font-weight: bold; font-style: italic; text-transform: uppercase; background: #fff; }
        .top-cell { vertical-align: top !important; }
        .signature-merge { position: relative; height: 29.4pt !important; max-height: 29.4pt !important; padding: 0 3px !important; text-align: left; vertical-align: top !important; }
        .signature-merge .signature-label { position: absolute; left: 3px; top: 2px; font-weight: 700; }
        .employee-sign-inline { position: absolute; left: 112px; top: 2px; width: 150px; text-align: center; }
        .student-signature-merge { height: 29.4pt !important; max-height: 29.4pt !important; padding: 0 3px !important; line-height: .95 !important; text-align: center; vertical-align: middle !important; }
        .student-signature-merge .signature-name { border: 0; font-size: 10px; font-weight: 700; line-height: 1; }
        .consent-merge { height: 58.8pt !important; max-height: 58.8pt !important; padding: 1px 5px !important; border: 1.5px solid #000 !important; font-size: 12px !important; font-style: italic; line-height: .98 !important; text-align: left; vertical-align: top !important; white-space: normal !important; }
        .section { padding: 2px 3px; border-bottom: 0; font-family: Arial, Helvetica, sans-serif; font-size: 14.6px; font-weight: bold; font-style: italic; text-transform: uppercase; background: #fff; }
        .section.no-bottom { border-bottom: 0; }
        .ruled { width: 100%; border-collapse: collapse; table-layout: fixed; }
        .ruled td { height: 0.154in; padding: 1px 3px; border: 0; border-bottom: 0; font-size: 12.1px; line-height: 1.02; vertical-align: middle; }
        .ruled tr:last-child td { border-bottom: 0; }
        .label { font-weight: 700; }
        .line { display: inline-block; min-width: 44px; border: 0 none !important; border-bottom: 0 none !important; text-decoration: none !important; font-weight: 700; line-height: .95; vertical-align: bottom; }
        .line.wide { min-width: 130px; }
        .line.mid { min-width: 82px; }
        .line.short { min-width: 34px; }
        .item { display: inline-block; margin-right: 5px; white-space: nowrap; }
        .choice-indent { display: inline-block; margin-left: 122px; }
        .heart-choice-indent { display: inline-block; margin-left: 45px; }
        .throat-choice-indent { display: inline-block; margin-left: 58px; }
        .referral-center { display: inline-block; margin-left: 92px; }
        .box { display: inline-block; min-width: 18px; margin-left: 4px; border: 0; line-height: 1; text-align: center; font-size: 14px; font-weight: 900; vertical-align: middle; }
        .field-row { margin: 0; min-height: 0.154in; padding: 1px 3px; border-bottom: 0; }
        .field-row:last-child { border-bottom: 0; }
        .block { border-bottom: 0; }
        .block:last-child { border-bottom: 0; }
        .pmh-left { height: 2.12in; }
        .right-medical { height: 2.12in; }
        .family-left { height: .58in; }
        .personal-left { height: .58in; }
        .physical-left { height: 4.78in; }
        .impression-right { height: 5.94in; position: relative; }
        .fit-lines .field-row { min-height: .205in; }
        .sig-img { max-width: 118px; max-height: 30px; display: block; margin: 0 auto 0; }
        .signature-area { position: absolute; left: 0; right: 0; bottom: 0; padding: 0 8px 8px; text-align: center; }
        .signature-line { min-height: 14px; border: 0 none !important; border-bottom: 0 none !important; text-decoration: none !important; font-weight: 700; text-align: center; }
        .consent { margin-top: 8px; font-size: 15.6px; font-style: italic; line-height: 1.18; text-align: left; }
        @media print { .no-print { display: none !important; } }
    </style>
</head>
<body>
@unless($pdfMode ?? false)
    <div class="print-action-bar no-print">
        <button class="print-action-button is-download" type="button" onclick="window.print()">Print</button>
        <button class="print-action-button" type="button" onclick="window.close()">Close View</button>
    </div>
@endunless
@php
    $profile = $employeeProfile;
    $user = $profile->user;
    $logo = ($pdfMode ?? false) ? public_path('images/pup_logo_print.jpg') : asset('images/pup_logo_print.jpg');
    $name = trim((string) ($profile->name ?: implode(' ', array_filter([$profile->first_name, $profile->middle_name, $profile->last_name])) ?: optional($user)->name));
    $email = trim((string) optional($user)->email);
    $formatDate = function ($value): string {
        $raw = trim((string) $value);
        if ($raw === '') {
            return '';
        }

        try {
            return $value instanceof \Carbon\CarbonInterface
                ? $value->format('Y-m-d')
                : \Carbon\Carbon::parse($raw)->format('Y-m-d');
        } catch (\Throwable $exception) {
            return $raw;
        }
    };
    $birthday = $formatDate($profile->birthday);
    $lmp = $formatDate($profile->genito_urinary_date_lmp);
    $followUp = $formatDate($profile->follow_up_on);
    $normalizeChoice = function ($value): string {
        return preg_replace('/[^a-z0-9]+/', ' ', strtolower(trim((string) $value))) ?? '';
    };
    $arrayHas = function ($value, array $needles) use ($normalizeChoice): bool {
        $values = is_array($value) ? $value : preg_split('/[,|]/', (string) $value);
        $normalized = array_map(fn ($item) => $normalizeChoice($item), $values ?: []);
        foreach ($needles as $needle) {
            if (in_array($normalizeChoice($needle), $normalized, true)) {
                return true;
            }
        }
        return false;
    };
    $valueIs = function ($value, array $needles) use ($normalizeChoice): bool {
        $normalized = $normalizeChoice($value);
        foreach ($needles as $needle) {
            if ($normalized === $normalizeChoice($needle)) {
                return true;
            }
        }
        return false;
    };
    $mark = fn ($condition) => $condition ? '(/)' : '( )';
    $signatureStoragePath = function ($path) {
        $path = ltrim((string) $path, '/');
        return preg_replace('#^(?:public/)?storage/#', '', $path) ?? $path;
    };
    $signatureSourceFromPath = function ($path) use ($pdfMode, $signatureStoragePath) {
        $path = $signatureStoragePath($path);
        if ($path === '') {
            return '';
        }

        return ($pdfMode ?? false)
            ? \Illuminate\Support\Facades\Storage::disk('public')->path($path)
            : asset('storage/' . $path);
    };
    $signatureSrc = '';
    if ($profile->uploaded_signature_path) {
        $signatureSrc = $signatureSourceFromPath($profile->uploaded_signature_path);
    } elseif ($profile->staff_signature) {
        if (str_starts_with((string) $profile->staff_signature, 'data:image/')) {
            $signatureSrc = $profile->staff_signature;
        } else {
            $signatureSrc = $signatureSourceFromPath($profile->staff_signature);
        }
    }
@endphp
<div class="print-container">
    <div class="print-page">
        <div class="document-code">
            <strong>PUP-HER-6-MEDS-028</strong><br>
            Rev. 1<br>
            October 31, 2019
        </div>
        <table class="official-header-table">
            <tr>
                <td class="official-logo-cell"><img src="{{ $logo }}" class="logo" alt=""></td>
                <td class="official-heading-cell">
                    <p>Republic of the Philippines</p>
                    <p class="univ-name">POLYTECHNIC UNIVERSITY OF THE PHILIPPINES</p>
                    <p>Office of the Vice President for Administration</p>
                    <p class="dept-name">MEDICAL SERVICES DEPARTMENT</p>
                    <p class="campus-name">Taguig Campus</p>
                </td>
                <td class="official-spacer-cell"></td>
            </tr>
        </table>
        <div class="title-rule"></div>
        <h1 class="form-title">Health Examination Record</h1>
        <div class="form-subtitle">Faculty, Administrative Employee and Student</div>

        <table class="record-grid">
            <tr class="top-info"><td class="left-col"><span class="label">Name:</span> <span class="line wide">{{ $name }}</span></td><td class="right-col"><span class="label">Date:</span> <span class="line mid">{{ $formatDate($profile->form_date) }}</span></td></tr>
            <tr class="top-info"><td><span class="label">Address:</span> <span class="line wide">{{ $profile->home_address }}</span></td><td><span class="label">College / Department:</span> <span class="line wide">{{ $profile->office ?: $profile->course_college }}</span></td></tr>
            <tr class="top-info"><td><span class="label">Contact No.:</span> <span class="line mid">{{ $profile->contact_no }}</span></td><td><span class="label">Course / School Year:</span> <span class="line mid">{{ $profile->course_college }}</span> / <span class="line short">{{ $profile->school_year }}</span></td></tr>
            <tr class="top-info"><td><span class="label">Contact Person in Case of Emergency:</span> <span class="line mid">{{ $profile->emergency_contact_person }}</span></td><td><span class="label">Contact No.:</span> <span class="line mid">{{ $profile->emergency_contact_no }}</span></td></tr>
            <tr><td>&nbsp;</td><td>&nbsp;</td></tr>
            <tr class="top-info"><td><span class="label">Age:</span> <span class="line short">{{ $profile->age }}</span> <span class="label">Sex:</span> <span class="line short">{{ $profile->sex }}</span> <span class="label">Civil Status:</span> <span class="line mid">{{ $profile->civil_status }}</span></td><td><span class="label">Date of Birth:</span> <span class="line mid">{{ $birthday }}</span></td></tr>
            <tr><td>&nbsp;</td><td>&nbsp;</td></tr>
            <tr><td class="section-cell">I. Past Medical History</td><td><span class="label">Chest X-Ray Result:</span> <span class="box">{{ $mark($valueIs($profile->chest_xray_result, ['normal'])) }}</span> Normal</td></tr>
            <tr><td><span class="box">{{ $mark($arrayHas($profile->past_medical_history, ['childhood illness'])) }}</span> Childhood Illness</td><td><span class="choice-indent"><span class="box">{{ $mark($valueIs($profile->chest_xray_result, ['with findings'])) }}</span> With findings</span></td></tr>
            <tr><td><span class="box">{{ $mark($arrayHas($profile->past_medical_history, ['asthma'])) }}</span> Asthma <span class="box">{{ $mark($arrayHas($profile->past_medical_history, ['heart disease'])) }}</span> Heart Disease <span class="box">{{ $mark($arrayHas($profile->past_medical_history, ['seizure disorder'])) }}</span> Seizure Disorder</td><td>&nbsp;</td></tr>
            <tr><td><span class="box">{{ $mark($arrayHas($profile->past_medical_history, ['chicken pox'])) }}</span> Chicken Pox <span class="box">{{ $mark($arrayHas($profile->past_medical_history, ['measles'])) }}</span> Measles</td><td><span class="label">Breast:</span> <span class="box">{{ $mark($valueIs($profile->breast_findings, ['normal'])) }}</span> Normal</td></tr>
            <tr><td><span class="box">{{ $mark($arrayHas($profile->past_medical_history, ['diabetes'])) }}</span> Diabetes <span class="box">{{ $mark($arrayHas($profile->past_medical_history, ['hypertension'])) }}</span> Hypertension</td><td>&nbsp;</td></tr>
            <tr><td>Others: <span class="line wide">{{ $profile->past_medical_history_others }}</span></td><td><span class="label">Heart:</span> Murmur <span class="box">{{ $mark($valueIs($profile->heart_murmur, ['present'])) }}</span> Present <span class="box">{{ $mark($valueIs($profile->heart_murmur, ['absent'])) }}</span> Absent</td></tr>
            <tr><td><span class="label">Previous Hospitalization:</span> <span class="box">{{ $mark(!$profile->previous_hospitalization) }}</span> No <span class="box">{{ $mark($profile->previous_hospitalization) }}</span> Yes</td><td><span class="heart-choice-indent">Rhythm <span class="box">{{ $mark($valueIs($profile->heart_rhythm, ['regular'])) }}</span> Regular <span class="box">{{ $mark($valueIs($profile->heart_rhythm, ['irregular'])) }}</span> Irregular</span></td></tr>
            <tr><td><span class="label">Operation/Surgery:</span> <span class="box">{{ $mark(!$profile->operation_surgery) }}</span> No <span class="box">{{ $mark($profile->operation_surgery) }}</span> Yes</td><td>&nbsp;</td></tr>
            <tr><td>&nbsp;</td><td><span class="label">Abdomen:</span> <span class="box">{{ $mark($valueIs($profile->abdomen_findings, ['normal'])) }}</span> Normal</td></tr>
            <tr><td><span class="label">Current Medications:</span> <span class="line wide">{{ $profile->current_medications }}</span></td><td>&nbsp;</td></tr>
            <tr><td><span class="label">Allergies:</span> <span class="line wide">{{ $profile->allergies }}</span></td><td><span class="label">Genito-Urinary -</span> 1st day of last Menstruation: <span class="line mid">{{ $lmp }}</span></td></tr>
            <tr><td>&nbsp;</td><td>&nbsp;</td></tr>
            <tr><td class="section-cell">II. Family History</td><td><span class="label">Extremities:</span> <span class="box">{{ $mark($valueIs($profile->extremities_findings, ['no deformities'])) }}</span> No Deformities</td></tr>
            <tr><td><span class="box">{{ $mark($arrayHas($profile->family_history, ['diabetes'])) }}</span> Diabetes <span class="box">{{ $mark($arrayHas($profile->family_history, ['ptb'])) }}</span> PTB</td><td>&nbsp;</td></tr>
            <tr><td><span class="box">{{ $mark($arrayHas($profile->family_history, ['hypertension'])) }}</span> Hypertension <span class="box">{{ $mark($arrayHas($profile->family_history, ['cancer'])) }}</span> Cancer</td><td><span class="label">Vertebral Column:</span> <span class="box">{{ $mark($valueIs($profile->vertebral_column_findings, ['normal'])) }}</span> Normal</td></tr>
            <tr><td><span class="label">Others:</span> <span class="line wide">{{ $profile->family_history_others }}</span></td><td><span class="choice-indent"><span class="box">{{ $mark($valueIs($profile->vertebral_column_findings, ['with deformity'])) }}</span> With Deformity</span></td></tr>
            <tr><td>&nbsp;</td><td>&nbsp;</td></tr>
            <tr><td class="section-cell">III. Personal History</td><td><span class="label">Skin:</span> <span class="box">{{ $mark($arrayHas($profile->skin_findings, ['pallor'])) }}</span> Pallor <span class="box">{{ $mark($arrayHas($profile->skin_findings, ['rashes'])) }}</span> Rashes <span class="box">{{ $mark($arrayHas($profile->skin_findings, ['lesions'])) }}</span> Lesions</td></tr>
            <tr><td>Cigarette Smoking: <span class="box">{{ $mark(!$profile->cigarette_smoking) }}</span> No <span class="box">{{ $mark($profile->cigarette_smoking) }}</span> Yes</td><td>Scars: <span class="box">{{ $mark($valueIs($profile->scars_findings ?? '', ['absent'])) }}</span> Absent <span class="box">{{ $mark($valueIs($profile->scars_findings ?? '', ['present'])) }}</span> Present</td></tr>
            <tr><td>Alcohol Drinking: <span class="box">{{ $mark(!$profile->alcohol_drinking) }}</span> No <span class="box">{{ $mark($profile->alcohol_drinking) }}</span> Yes</td><td>&nbsp;</td></tr>
            <tr><td>Traveled Abroad: <span class="box">{{ $mark(!$profile->traveled_abroad) }}</span> No <span class="box">{{ $mark($profile->traveled_abroad) }}</span> Yes</td><td class="section-cell">Working Impression:</td></tr>
            <tr><td>&nbsp;</td><td>&nbsp;</td></tr>
            <tr><td class="section-cell">IV. Physical Examination</td><td><span class="label">Fit:</span> <span class="line wide">{{ $profile->fit_status }}</span></td></tr>
            <tr><td><span class="label">Vital Signs:</span> <span class="box">{{ $mark($valueIs($profile->vital_signs_distress_status, ['not in distress'])) }}</span> Not in Distress <span class="box">{{ $mark($valueIs($profile->vital_signs_distress_status, ['in distress'])) }}</span> In Distress</td><td>&nbsp;</td></tr>
            <tr><td>Ht: <span class="line short">{{ $profile->height }}</span> ft. Wt: <span class="line short">{{ $profile->weight }}</span> lbs. BMI: <span class="line short">{{ $profile->bmi }}</span></td><td><span class="label">For Work-Up:</span> <span class="line wide">{{ $profile->for_work_up }}</span></td></tr>
            <tr><td>BP: <span class="line short">{{ $profile->bp }}</span> HR: <span class="line short">{{ $profile->hr }}</span> /min</td><td>&nbsp;</td></tr>
            <tr><td>RR: <span class="line short">{{ $profile->rr }}</span> /min Temp: <span class="line short">{{ $profile->temperature }}</span></td><td><span class="label">Referred to:</span></td></tr>
            <tr><td>&nbsp;</td><td><span class="box">{{ $mark($arrayHas($profile->referred_to, ['cardio'])) }}</span> Cardio <span class="referral-center"><span class="box">{{ $mark($arrayHas($profile->referred_to, ['pulmo'])) }}</span> Pulmo</span></td></tr>
            <tr><td><span class="label">Head:</span> <span class="box">{{ $mark($arrayHas($profile->head_findings, ['wound'])) }}</span> Wound <span class="box">{{ $mark($arrayHas($profile->head_findings, ['mass'])) }}</span> Mass <span class="box">{{ $mark($arrayHas($profile->head_findings, ['alopecia'])) }}</span> Alopecia</td><td><span class="box">{{ $mark($arrayHas($profile->referred_to, ['derma'])) }}</span> Derma <span class="referral-center"><span class="box">{{ $mark($arrayHas($profile->referred_to, ['others'])) }}</span> Others: <span class="line mid">{{ $profile->referred_to_others }}</span></span></td></tr>
            <tr><td>&nbsp;</td><td><span class="box">{{ $mark($arrayHas($profile->referred_to, ['ent'])) }}</span> ENT</td></tr>
            <tr><td><span class="label">Eyes:</span> <span class="box">{{ $mark($arrayHas($profile->eyes_findings, ['w/o glasses'])) }}</span> w/o Glasses <span class="box">{{ $mark($arrayHas($profile->eyes_findings, ['w/ glasses'])) }}</span> w/ Glasses</td><td><span class="box">{{ $mark($arrayHas($profile->referred_to, ['optha'])) }}</span> Optha</td></tr>
            <tr><td><span class="box">{{ $mark($arrayHas($profile->eyes_findings, ['anicteric sclera'])) }}</span> Anicteric Sclera <span class="box">{{ $mark($arrayHas($profile->eyes_findings, ['pink palpebral conjunctiva'])) }}</span> Pink Palpebral Conjunctiva</td><td>&nbsp;</td></tr>
            <tr><td>&nbsp;</td><td><span class="label">Follow up on:</span> <span class="line mid">{{ $followUp }}</span></td></tr>
            <tr><td>&nbsp;</td><td>&nbsp;</td></tr>
            <tr><td><span class="label">Ears:</span> <span class="box">{{ $mark($arrayHas($profile->ears_findings, ['no gross deformity'])) }}</span> No Gross Deformity <span class="box">{{ $mark($arrayHas($profile->ears_findings, ['no discharge'])) }}</span> No Discharge</td><td rowspan="2" class="signature-merge"><span class="signature-label">Physician's Signature:</span><span class="employee-sign-inline">@if($signatureSrc)<img src="{{ $signatureSrc }}" class="sig-img" alt="">@endif</span></td></tr>
            <tr><td>&nbsp;</td></tr>
            <tr><td>&nbsp;</td><td rowspan="2" class="student-signature-merge"><div class="signature-name">{{ strtoupper($name) }}</div></td></tr>
            <tr><td><span class="label">Throat:</span> <span class="box">{{ $mark($arrayHas($profile->throat_findings, ['no tpc'])) }}</span> No TPC <span class="box">{{ $mark($arrayHas($profile->throat_findings, ['no mass'])) }}</span> No Mass</td></tr>
            <tr><td><span class="throat-choice-indent"><span class="box">{{ $mark($arrayHas($profile->throat_findings, ['no lymphadenopathy'])) }}</span> No lymphadenopathy</span></td><td rowspan="4" class="consent-merge"><strong>By affixing my signature, I am agreeing to the PUP Data Privacy Policy and giving my consent in the collection and processing of my Personal Information in accordance thereto.</strong></td></tr>
            <tr><td>&nbsp;</td></tr>
            <tr><td><span class="label">Chest/Lungs:</span> <span class="box">{{ $mark($arrayHas($profile->chest_lungs_findings, ['normal'])) }}</span> Normal <span class="box">{{ $mark($arrayHas($profile->chest_lungs_findings, ['wheeze'])) }}</span> Wheeze <span class="box">{{ $mark($arrayHas($profile->chest_lungs_findings, ['rales'])) }}</span> Rales</td></tr>
            <tr><td>&nbsp;</td></tr>
        </table>
    </div>
</div>
</body>
</html>
