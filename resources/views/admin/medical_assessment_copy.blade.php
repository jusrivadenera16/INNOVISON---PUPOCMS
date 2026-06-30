<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <title>Medical Assessment Copy</title>
    <style>
        @page { size: 8.5in 11in; margin: 0.55in; }
        * { box-sizing: border-box; }
        body {
            margin: 0;
            color: #111;
            font-family: Arial, Helvetica, sans-serif;
            font-size: 13px;
            line-height: 1.45;
        }
        .sheet {
            width: 100%;
        }
        .title {
            margin: 0 0 18px;
            font-size: 18px;
            font-weight: 700;
            text-align: center;
            text-transform: uppercase;
        }
        .assessment-table {
            width: 100%;
            border-collapse: separate;
            border-spacing: 0 10px;
            table-layout: fixed;
        }
        .assessment-table td {
            padding: 0 5px;
            border: 0;
            vertical-align: bottom;
            white-space: nowrap;
        }
        .label {
            width: 19%;
            font-weight: 700;
        }
        .label-short {
            width: 7%;
            font-weight: 700;
        }
        .label-wide {
            width: 32%;
            font-weight: 700;
        }
        .value-line {
            border-bottom: 1px solid #111 !important;
            font-weight: 700;
            min-height: 16px;
        }
        .doctor-value {
            padding-left: 14px !important;
        }
        .value-small { width: 14%; }
        .value-medium { width: 24%; }
        .value-wide { width: 40%; }
        .value-xray { width: 46%; }
        .section-spacer td {
            height: 8px;
            padding: 0;
        }
        .block-label {
            padding-top: 4px !important;
            font-weight: 700;
            vertical-align: top !important;
        }
        .block-value {
            padding-top: 2px !important;
            white-space: normal !important;
            line-height: 1.45;
            font-weight: 700;
            vertical-align: top !important;
        }
        .block-value div {
            margin: 0 0 3px;
        }
    </style>
</head>
<body>
    <div class="sheet">
        <h1 class="title">Medical Assessment Copy</h1>

        <table class="assessment-table">
            <colgroup>
                <col style="width: 18%;">
                <col style="width: 16%;">
                <col style="width: 8%;">
                <col style="width: 15%;">
                <col style="width: 8%;">
                <col style="width: 15%;">
                <col style="width: 8%;">
                <col style="width: 12%;">
            </colgroup>
            <tr>
                <td class="label">Date:</td>
                <td class="value-line value-medium">{{ $assessmentDate }}</td>
                <td colspan="6"></td>
            </tr>
            <tr>
                <td class="label">Date of birth:</td>
                <td class="value-line value-medium">{{ $birthday }}</td>
                <td colspan="6"></td>
            </tr>
            <tr>
                <td class="label">Height:</td>
                <td class="value-line value-small">{{ $height }}</td>
                <td class="label-short">Weight:</td>
                <td class="value-line value-small">{{ $weight }}</td>
                <td colspan="4"></td>
            </tr>
            <tr>
                <td class="label-short">BP:</td>
                <td class="value-line value-small">{{ $bloodPressure }}</td>
                <td class="label-short">PR:</td>
                <td class="value-line value-small">{{ $pulseRate }}</td>
                <td class="label-short">RR:</td>
                <td class="value-line value-small">{{ $respiratoryRate }}</td>
                <td class="label-short">Temp:</td>
                <td class="value-line value-small">{{ $temperature }}</td>
            </tr>
            <tr>
                <td class="label">COVID Positive:</td>
                <td class="value-line value-small">{{ $covidPositive }}</td>
                <td class="label-short">Date:</td>
                <td class="value-line value-medium">{{ $covidPositiveDate }}</td>
                <td colspan="4"></td>
            </tr>
            <tr>
                <td class="label-wide">Medical certificate issued by: Dr.</td>
                <td colspan="3" class="value-line value-wide doctor-value">{{ $doctorName }}</td>
                <td class="label-short">Date:</td>
                <td colspan="3" class="value-line value-medium">{{ $medicalCertificateDate }}</td>
            </tr>
            <tr>
                <td class="label">Chest x-ray result:</td>
                <td colspan="4" class="value-line value-xray">{{ $xrayResult }}</td>
                <td class="label-short">Date:</td>
                <td colspan="2" class="value-line value-medium">{{ $xrayDate }}</td>
            </tr>
            @if(!empty($hasMedicalCondition) && !empty($medicalConditionLines))
                <tr class="section-spacer"><td colspan="8"></td></tr>
                <tr>
                    <td colspan="8" class="block-label">Medical Condition:</td>
                </tr>
                <tr>
                    <td colspan="8" class="block-value">
                        @foreach($medicalConditionLines as $conditionLine)
                            <div>{{ $conditionLine }}</div>
                        @endforeach
                    </td>
                </tr>
            @endif
            <tr class="section-spacer"><td colspan="8"></td></tr>
            <tr>
                <td colspan="8" class="block-label">Remarks:</td>
            </tr>
            <tr>
                <td colspan="8" class="block-value">{{ $medAssessmentRemarks }}</td>
            </tr>
        </table>
    </div>
</body>
</html>
