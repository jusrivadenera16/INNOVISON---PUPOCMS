@php
    $healthData = is_array($healthData ?? null) ? $healthData : [];
    $healthValue = function (string $key, $fallback = 'N/A') use ($healthData) {
        $value = $healthData[$key] ?? null;

        return filled($value) ? $value : $fallback;
    };
    $healthBoolean = function (string $key) use ($healthData) {
        return !empty($healthData[$key]) ? 'Yes' : 'No';
    };
    $firstHealthValue = function (array $keys, $fallback = 'N/A') use ($healthData) {
        foreach ($keys as $key) {
            $value = $healthData[$key] ?? null;

            if (filled($value)) {
                return $value;
            }
        }

        return $fallback;
    };
    $historyMedicineAllergies = $formatProfileList($healthData['medicine_allergies'] ?? null);
    $historyMedicalHistory = $formatProfileList($healthData['medical_history'] ?? null);
    $historyVaccineHistory = collect($healthData['vaccine_history'] ?? [])
        ->map(function ($dose, $key) use ($formatProfileDate) {
            if (!is_array($dose)) {
                return filled($dose) ? (string) $dose : null;
            }

            $label = \Illuminate\Support\Str::of((string) $key)->replace('_', ' ')->title();
            $date = $formatProfileDate($dose['date'] ?? null);
            $brand = trim((string) ($dose['brand'] ?? ''));
            $details = collect([$date !== 'N/A' ? $date : null, $brand !== '' ? $brand : null])
                ->filter()
                ->implode(' - ');

            return $details !== '' ? "{$label}: {$details}" : null;
        })
        ->filter()
        ->values()
        ->implode('; ');
    $historyVaccineHistory = $historyVaccineHistory !== '' ? $historyVaccineHistory : 'N/A';

    $historyMedicalCondition = trim((string) ($healthData['medical_condition_remarks'] ?? ''));
    if ($historyMedicalCondition === '') {
        $hasCondition = ($healthData['has_disability'] ?? 'No') === 'Yes'
            || ($healthData['has_illness'] ?? 'No') === 'Yes'
            || collect([
                $healthData['medical_history'] ?? null,
                $healthData['other_illness'] ?? null,
                $healthData['food_allergies'] ?? null,
                $healthData['medicine_allergies'] ?? null,
                $healthData['other_med_allergies'] ?? null,
            ])->contains(function ($value) {
                return is_array($value)
                    ? collect($value)->filter(fn ($item) => filled($item))->isNotEmpty()
                    : filled($value);
            });
        $historyMedicalCondition = $hasCondition ? 'With Condition' : 'No Medical Condition Recorded';
    }
@endphp

<div class="profile-grid">
    <div class="profile-meta"><div class="profile-meta-k">Medical Condition</div><div class="profile-meta-v">{{ $historyMedicalCondition }}</div></div>
    <div class="profile-meta"><div class="profile-meta-k">Physical Assessment</div><div class="profile-meta-v">{{ $healthValue('physical_assessment_status') }}</div></div>
    <div class="profile-meta"><div class="profile-meta-k">Documents Valid</div><div class="profile-meta-v">{{ $healthBoolean('documents_valid') }}</div></div>

    <div class="profile-meta"><div class="profile-meta-k">Assessment Date</div><div class="profile-meta-v">{{ $formatProfileDate($healthData['assessment_date'] ?? null) }}</div></div>
    <div class="profile-meta"><div class="profile-meta-k">Verified At</div><div class="profile-meta-v">{{ $formatProfileDate($healthData['verified_at'] ?? null) }}</div></div>
    <div class="profile-meta"><div class="profile-meta-k">Pending Reason</div><div class="profile-meta-v">{{ $healthValue('pending_reason') }}</div></div>

    <div class="profile-meta"><div class="profile-meta-k">Blood Pressure</div><div class="profile-meta-v">{{ $healthValue('blood_pressure') }}</div></div>
    <div class="profile-meta"><div class="profile-meta-k">Pulse Rate</div><div class="profile-meta-v">{{ $healthValue('pulse_rate') }}</div></div>
    <div class="profile-meta"><div class="profile-meta-k">Respiratory Rate</div><div class="profile-meta-v">{{ $healthValue('respiratory_rate') }}</div></div>
    <div class="profile-meta"><div class="profile-meta-k">Temperature</div><div class="profile-meta-v">{{ $healthValue('temperature') }}</div></div>
    <div class="profile-meta"><div class="profile-meta-k">COVID Positive</div><div class="profile-meta-v">{{ $healthValue('covid_positive') }}</div></div>
    <div class="profile-meta"><div class="profile-meta-k">COVID Positive Date</div><div class="profile-meta-v">{{ $formatProfileDate($healthData['covid_positive_date'] ?? null) }}</div></div>

    <div class="profile-meta"><div class="profile-meta-k">Known Medical Illness</div><div class="profile-meta-v">{{ $healthValue('has_illness') }}</div></div>
    <div class="profile-meta is-wide"><div class="profile-meta-k">Medical History</div><div class="profile-meta-v">{{ $historyMedicalHistory }}</div></div>
    <div class="profile-meta is-full"><div class="profile-meta-k">Other Illness / Medical Notes</div><div class="profile-meta-v">{{ $healthValue('other_illness') }}</div></div>

    <div class="profile-meta"><div class="profile-meta-k">Has Disability</div><div class="profile-meta-v">{{ $healthValue('has_disability') }}</div></div>
    <div class="profile-meta is-wide"><div class="profile-meta-k">Disability Type</div><div class="profile-meta-v">{{ $healthValue('disability_type') }}</div></div>

    <div class="profile-meta"><div class="profile-meta-k">No Known Allergies</div><div class="profile-meta-v">{{ $healthBoolean('no_allergies') }}</div></div>
    <div class="profile-meta"><div class="profile-meta-k">Food Allergies</div><div class="profile-meta-v">{{ $healthValue('food_allergies') }}</div></div>
    <div class="profile-meta"><div class="profile-meta-k">Medicine Allergies</div><div class="profile-meta-v">{{ $historyMedicineAllergies }}</div></div>
    <div class="profile-meta is-full"><div class="profile-meta-k">Other Medicine Allergies</div><div class="profile-meta-v">{{ $healthValue('other_med_allergies') }}</div></div>

    <div class="profile-meta"><div class="profile-meta-k">Smoker</div><div class="profile-meta-v">{{ $healthValue('is_smoker') }}</div></div>
    <div class="profile-meta"><div class="profile-meta-k">Alcohol Drinker</div><div class="profile-meta-v">{{ $healthValue('is_drinker') }}</div></div>
    <div class="profile-meta"><div class="profile-meta-k">COVID Vaccinated</div><div class="profile-meta-v">{{ $healthValue('covid_vaccinated') }}</div></div>
    <div class="profile-meta is-full"><div class="profile-meta-k">Vaccination History</div><div class="profile-meta-v">{{ $historyVaccineHistory }}</div></div>

    <div class="profile-meta"><div class="profile-meta-k">Medical Certificate Doctor</div><div class="profile-meta-v">{{ $firstHealthValue(['doctor_name', 'medical_certificate_issued_by']) }}</div></div>
    <div class="profile-meta"><div class="profile-meta-k">Medical Certificate Date</div><div class="profile-meta-v">{{ $formatProfileDate($firstHealthValue(['med_cert_date', 'medical_certificate_issued_at'], null)) }}</div></div>
    <div class="profile-meta"><div class="profile-meta-k">Medical Certificate Result</div><div class="profile-meta-v">{{ $healthValue('med_cert_findings') }}</div></div>
    <div class="profile-meta is-full"><div class="profile-meta-k">Student Declared Medical Findings</div><div class="profile-meta-v">{{ $healthValue('med_cert_findings_details') }}</div></div>

    <div class="profile-meta"><div class="profile-meta-k">Chest X-ray Date</div><div class="profile-meta-v">{{ $formatProfileDate($firstHealthValue(['xray_date', 'chest_xray_date'], null)) }}</div></div>
    <div class="profile-meta is-wide"><div class="profile-meta-k">Chest X-ray Result</div><div class="profile-meta-v">{{ $firstHealthValue(['xray_findings', 'chest_xray_result_text']) }}</div></div>
    <div class="profile-meta is-full"><div class="profile-meta-k">Student Declared X-ray Findings</div><div class="profile-meta-v">{{ $healthValue('xray_findings_details') }}</div></div>

    <div class="profile-meta is-full"><div class="profile-meta-k">Assessment Remarks</div><div class="profile-meta-v">{{ $healthValue('assessment_remarks') }}</div></div>
</div>
