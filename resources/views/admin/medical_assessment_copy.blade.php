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
        .row {
            margin-bottom: 13px;
            white-space: nowrap;
        }
        .label {
            display: inline-block;
            min-width: 110px;
            font-weight: 700;
        }
        .short-label {
            min-width: 34px;
        }
        .line {
            display: inline-block;
            min-height: 16px;
            padding: 0 6px 1px;
            border-bottom: 1px solid #111;
            font-weight: 700;
            vertical-align: bottom;
        }
        .line-date { width: 180px; }
        .line-birth { width: 210px; }
        .line-small { width: 86px; }
        .line-medium { width: 150px; }
        .line-doctor { width: 270px; }
        .line-xray { width: 300px; }
        .suffix {
            display: inline-block;
            min-width: 36px;
            margin-right: 54px;
            font-weight: 700;
        }
        .vitals .line {
            margin-right: 18px;
        }
        .spacer {
            display: inline-block;
            width: 26px;
        }
    </style>
</head>
<body>
    <div class="sheet">
        <h1 class="title">Medical Assessment Copy</h1>

        <div class="row">
            <span class="label">Date:</span>
            <span class="line line-date">{{ $assessmentDate }}</span>
        </div>

        <div class="row">
            <span class="label">Date of birth:</span>
            <span class="line line-birth">{{ $birthday }}</span>
        </div>

        <div class="row">
            <span class="label">Height:</span>
            <span class="line line-small">{{ $height }}</span>
            <span class="suffix">cm</span>
            <span class="label short-label">Weight:</span>
            <span class="line line-small">{{ $weight }}</span>
            <span class="suffix">kg</span>
        </div>

        <div class="row vitals">
            <span class="label short-label">BP:</span>
            <span class="line line-small">{{ $bloodPressure }}</span>
            <span class="label short-label">PR:</span>
            <span class="line line-small">{{ $pulseRate }}</span>
            <span class="label short-label">RR:</span>
            <span class="line line-small">{{ $respiratoryRate }}</span>
            <span class="label short-label">Temp:</span>
            <span class="line line-small">{{ $temperature }}</span>
        </div>

        <div class="row">
            <span class="label">COVID Positive:</span>
            <span class="line line-small">{{ $covidPositive }}</span>
            <span class="spacer"></span>
            <span class="label short-label">Date</span>
            <span class="line line-medium">{{ $covidPositiveDate }}</span>
        </div>

        <div class="row">
            <span class="label">Medical certificate issued by: Dr.</span>
            <span class="line line-doctor">{{ $doctorName }}</span>
            <span class="spacer"></span>
            <span class="label short-label">Date:</span>
            <span class="line line-medium">{{ $medicalCertificateDate }}</span>
        </div>

        <div class="row">
            <span class="label">Chest x-ray result:</span>
            <span class="line line-xray">{{ $xrayResult }}</span>
            <span class="spacer"></span>
            <span class="label short-label">Date:</span>
            <span class="line line-medium">{{ $xrayDate }}</span>
        </div>
    </div>
</body>
</html>
