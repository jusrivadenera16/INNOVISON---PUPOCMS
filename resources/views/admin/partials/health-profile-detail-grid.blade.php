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

@if(in_array($healthFormAudience ?? null, ['student', 'applicant'], true))
    <div class="health-overview">
        <section class="health-overview-card" aria-label="Medical Status">
            <div class="health-overview-heading"><span class="health-overview-heading-icon" aria-hidden="true"><x-outline-icon name="clipboard-document-list" /></span><div class="health-overview-heading-copy"><h4>Medical Status</h4><p>Summary of the latest medical evaluation.</p></div></div>
            <div class="health-overview-metrics is-three">
                <div class="health-overview-metric"><span class="health-overview-metric-icon" aria-hidden="true"><x-outline-icon name="heart-pulse" /></span><span class="health-overview-metric-copy"><span class="health-overview-metric-label">Medical Condition</span><span class="health-overview-metric-value">{{ $historyMedicalCondition }}</span></span></div>
                <div class="health-overview-metric"><span class="health-overview-metric-icon" aria-hidden="true"><x-outline-icon name="document-check" /></span><span class="health-overview-metric-copy"><span class="health-overview-metric-label">Physical Assessment</span><span class="health-overview-metric-value">{{ $healthValue('physical_assessment_status') }}</span></span></div>
                <div class="health-overview-metric"><span class="health-overview-metric-icon {{ !empty($healthData['documents_valid']) ? 'is-positive' : '' }}" aria-hidden="true"><x-outline-icon name="shield-check" /></span><span class="health-overview-metric-copy"><span class="health-overview-metric-label">Documents</span><span class="health-overview-metric-value">{{ !empty($healthData['documents_valid']) ? 'Validated' : 'Not Validated' }}</span></span></div>
            </div>
        </section>

        <section class="health-overview-card" aria-label="Body Measurements">
            <div class="health-overview-heading"><span class="health-overview-heading-icon" aria-hidden="true"><x-outline-icon name="chart-bar" /></span><div class="health-overview-heading-copy"><h4>Body Measurements</h4><p>Physical measurement details.</p></div></div>
            <div class="health-overview-metrics is-two">
                <div class="health-overview-metric"><span class="health-overview-metric-icon" aria-hidden="true"><x-outline-icon name="user-circle" /></span><span class="health-overview-metric-copy"><span class="health-overview-metric-label">Height</span><span class="health-overview-metric-value">{{ $healthValue('height') }}</span></span></div>
                <div class="health-overview-metric"><span class="health-overview-metric-icon" aria-hidden="true"><x-outline-icon name="chart-bar" /></span><span class="health-overview-metric-copy"><span class="health-overview-metric-label">Weight</span><span class="health-overview-metric-value">{{ $healthValue('weight') }}</span></span></div>
            </div>
        </section>

        <section class="health-overview-card" aria-label="Vital Signs">
            <div class="health-overview-heading"><span class="health-overview-heading-icon" aria-hidden="true"><x-outline-icon name="heart-pulse" /></span><div class="health-overview-heading-copy"><h4>Vital Signs</h4><p>Latest recorded vital signs.</p></div></div>
            <div class="health-overview-metrics is-four">
                <div class="health-overview-metric"><span class="health-overview-metric-icon" aria-hidden="true"><x-outline-icon name="heart" /></span><span class="health-overview-metric-copy"><span class="health-overview-metric-label">Blood Pressure</span><span class="health-overview-metric-value">{{ $healthValue('blood_pressure') }}</span></span></div>
                <div class="health-overview-metric"><span class="health-overview-metric-icon" aria-hidden="true"><x-outline-icon name="heart-pulse" /></span><span class="health-overview-metric-copy"><span class="health-overview-metric-label">Pulse Rate</span><span class="health-overview-metric-value">{{ $healthValue('pulse_rate') }}</span></span></div>
                <div class="health-overview-metric"><span class="health-overview-metric-icon" aria-hidden="true"><x-outline-icon name="heart-pulse" /></span><span class="health-overview-metric-copy"><span class="health-overview-metric-label">Respiratory Rate</span><span class="health-overview-metric-value">{{ $healthValue('respiratory_rate') }}</span></span></div>
                <div class="health-overview-metric"><span class="health-overview-metric-icon" aria-hidden="true"><x-outline-icon name="heart-pulse" /></span><span class="health-overview-metric-copy"><span class="health-overview-metric-label">Temperature</span><span class="health-overview-metric-value">{{ $healthValue('temperature') }}</span></span></div>
            </div>
        </section>

        <section class="health-overview-card" aria-label="Medical History">
            <div class="health-overview-heading"><span class="health-overview-heading-icon" aria-hidden="true"><x-outline-icon name="clipboard-document-list" /></span><div class="health-overview-heading-copy"><h4>Medical History</h4><p>Details of past and existing medical conditions.</p></div></div>
            <div class="health-overview-metrics is-seven">
                <div class="health-overview-metric is-stacked"><span class="health-overview-metric-icon" aria-hidden="true"><x-outline-icon name="clipboard-document-list" /></span><span class="health-overview-metric-copy"><span class="health-overview-metric-label">Known Medical Illness</span><span class="health-overview-metric-value">{{ $healthValue('has_illness') }}</span></span></div>
                <div class="health-overview-metric is-stacked"><span class="health-overview-metric-icon" aria-hidden="true"><x-outline-icon name="document-text" /></span><span class="health-overview-metric-copy"><span class="health-overview-metric-label">Medical History</span><span class="health-overview-metric-value">{{ $historyMedicalHistory }}</span></span></div>
                <div class="health-overview-metric is-stacked"><span class="health-overview-metric-icon" aria-hidden="true"><x-outline-icon name="document-text" /></span><span class="health-overview-metric-copy"><span class="health-overview-metric-label">Other Illness / Notes</span><span class="health-overview-metric-value">{{ $healthValue('other_illness') }}</span></span></div>
                <div class="health-overview-metric is-stacked"><span class="health-overview-metric-icon" aria-hidden="true"><x-outline-icon name="user-circle" /></span><span class="health-overview-metric-copy"><span class="health-overview-metric-label">Has Disability</span><span class="health-overview-metric-value">{{ $healthValue('has_disability') }}</span></span></div>
                <div class="health-overview-metric is-stacked"><span class="health-overview-metric-icon" aria-hidden="true"><x-outline-icon name="document-text" /></span><span class="health-overview-metric-copy"><span class="health-overview-metric-label">Disability Type</span><span class="health-overview-metric-value">{{ $healthValue('disability_type') }}</span></span></div>
                <div class="health-overview-metric is-stacked"><span class="health-overview-metric-icon" aria-hidden="true"><x-outline-icon name="heart-pulse" /></span><span class="health-overview-metric-copy"><span class="health-overview-metric-label">COVID Positive</span><span class="health-overview-metric-value">{{ $healthValue('covid_positive') }}</span></span></div>
                <div class="health-overview-metric is-stacked"><span class="health-overview-metric-icon" aria-hidden="true"><x-outline-icon name="calendar-days" /></span><span class="health-overview-metric-copy"><span class="health-overview-metric-label">COVID Positive Date</span><span class="health-overview-metric-value">{{ $formatProfileDate($healthData['covid_positive_date'] ?? null) }}</span></span></div>
            </div>
        </section>

        <section class="health-overview-card" aria-label="Medications, Allergies and Lifestyle">
            <div class="health-overview-heading"><span class="health-overview-heading-icon" aria-hidden="true"><x-outline-icon name="heart-pulse" /></span><div class="health-overview-heading-copy"><h4>Medications, Allergies &amp; Lifestyle</h4><p>Information about allergies and lifestyle habits.</p></div></div>
            <div class="health-overview-metrics is-eight">
                <div class="health-overview-metric is-stacked"><span class="health-overview-metric-icon {{ !empty($healthData['no_allergies']) ? 'is-positive' : '' }}" aria-hidden="true"><x-outline-icon name="shield-check" /></span><span class="health-overview-metric-copy"><span class="health-overview-metric-label">No Known Allergies</span><span class="health-overview-metric-value">{{ $healthBoolean('no_allergies') }}</span></span></div>
                <div class="health-overview-metric is-stacked"><span class="health-overview-metric-icon" aria-hidden="true"><x-outline-icon name="exclamation-triangle" /></span><span class="health-overview-metric-copy"><span class="health-overview-metric-label">Food Allergies</span><span class="health-overview-metric-value">{{ $healthValue('food_allergies') }}</span></span></div>
                <div class="health-overview-metric is-stacked"><span class="health-overview-metric-icon" aria-hidden="true"><x-outline-icon name="exclamation-triangle" /></span><span class="health-overview-metric-copy"><span class="health-overview-metric-label">Medicine Allergies</span><span class="health-overview-metric-value">{{ $historyMedicineAllergies }}</span></span></div>
                <div class="health-overview-metric is-stacked"><span class="health-overview-metric-icon" aria-hidden="true"><x-outline-icon name="document-text" /></span><span class="health-overview-metric-copy"><span class="health-overview-metric-label">Other Medicine Allergies</span><span class="health-overview-metric-value">{{ $healthValue('other_med_allergies') }}</span></span></div>
                <div class="health-overview-metric is-stacked"><span class="health-overview-metric-icon" aria-hidden="true"><x-outline-icon name="exclamation-circle" /></span><span class="health-overview-metric-copy"><span class="health-overview-metric-label">Cigarette Smoking</span><span class="health-overview-metric-value">{{ $healthValue('is_smoker') }}</span></span></div>
                <div class="health-overview-metric is-stacked"><span class="health-overview-metric-icon" aria-hidden="true"><x-outline-icon name="exclamation-circle" /></span><span class="health-overview-metric-copy"><span class="health-overview-metric-label">Alcohol Drinking</span><span class="health-overview-metric-value">{{ $healthValue('is_drinker') }}</span></span></div>
                <div class="health-overview-metric is-stacked"><span class="health-overview-metric-icon" aria-hidden="true"><x-outline-icon name="shield-check" /></span><span class="health-overview-metric-copy"><span class="health-overview-metric-label">COVID Vaccinated</span><span class="health-overview-metric-value">{{ $healthValue('covid_vaccinated') }}</span></span></div>
                <div class="health-overview-metric is-stacked"><span class="health-overview-metric-icon" aria-hidden="true"><x-outline-icon name="document-text" /></span><span class="health-overview-metric-copy"><span class="health-overview-metric-label">Vaccination History</span><span class="health-overview-metric-value">{{ $historyVaccineHistory }}</span></span></div>
            </div>
        </section>

        <section class="health-overview-card" aria-label="Medical Examination and Assessment">
            <div class="health-overview-heading"><span class="health-overview-heading-icon" aria-hidden="true"><x-outline-icon name="heart-pulse" /></span><div class="health-overview-heading-copy"><h4>Medical Examination &amp; Assessment</h4><p>Results and notes from the recorded medical examination.</p></div></div>
            <div class="health-overview-metrics is-six">
                <div class="health-overview-metric is-stacked"><span class="health-overview-metric-icon" aria-hidden="true"><x-outline-icon name="user-circle" /></span><span class="health-overview-metric-copy"><span class="health-overview-metric-label">Medical Certificate Doctor</span><span class="health-overview-metric-value">{{ $firstHealthValue(['doctor_name', 'medical_certificate_issued_by']) }}</span></span></div>
                <div class="health-overview-metric is-stacked"><span class="health-overview-metric-icon" aria-hidden="true"><x-outline-icon name="calendar-days" /></span><span class="health-overview-metric-copy"><span class="health-overview-metric-label">Medical Certificate Date</span><span class="health-overview-metric-value">{{ $formatProfileDate($firstHealthValue(['med_cert_date', 'medical_certificate_issued_at'], null)) }}</span></span></div>
                <div class="health-overview-metric is-stacked"><span class="health-overview-metric-icon" aria-hidden="true"><x-outline-icon name="document-check" /></span><span class="health-overview-metric-copy"><span class="health-overview-metric-label">Medical Certificate Result</span><span class="health-overview-metric-value">{{ $healthValue('med_cert_findings') }}</span></span></div>
                <div class="health-overview-metric is-stacked"><span class="health-overview-metric-icon" aria-hidden="true"><x-outline-icon name="calendar-days" /></span><span class="health-overview-metric-copy"><span class="health-overview-metric-label">Chest X-ray Date</span><span class="health-overview-metric-value">{{ $formatProfileDate($firstHealthValue(['xray_date', 'chest_xray_date'], null)) }}</span></span></div>
                <div class="health-overview-metric is-stacked"><span class="health-overview-metric-icon" aria-hidden="true"><x-outline-icon name="document-check" /></span><span class="health-overview-metric-copy"><span class="health-overview-metric-label">Chest X-ray Result</span><span class="health-overview-metric-value">{{ $firstHealthValue(['xray_findings', 'chest_xray_result_text']) }}</span></span></div>
                <div class="health-overview-metric is-stacked"><span class="health-overview-metric-icon" aria-hidden="true"><x-outline-icon name="document-text" /></span><span class="health-overview-metric-copy"><span class="health-overview-metric-label">Pending Reason</span><span class="health-overview-metric-value">{{ $healthValue('pending_reason') }}</span></span></div>
            </div>
            <div class="health-overview-findings"><span class="health-overview-metric-icon" aria-hidden="true"><x-outline-icon name="document-text" /></span><span><span class="health-overview-metric-label">Student-Declared Medical Findings</span><span class="health-overview-metric-value">{{ $healthValue('med_cert_findings_details') }}</span></span></div>
            <div class="health-overview-findings"><span class="health-overview-metric-icon" aria-hidden="true"><x-outline-icon name="document-text" /></span><span><span class="health-overview-metric-label">Student-Declared X-ray Findings</span><span class="health-overview-metric-value">{{ $healthValue('xray_findings_details') }}</span></span></div>
            <div class="health-overview-findings"><span class="health-overview-metric-icon" aria-hidden="true"><x-outline-icon name="document-text" /></span><span><span class="health-overview-metric-label">Assessment Remarks</span><span class="health-overview-metric-value">{{ $firstHealthValue(['assessment_remarks', 'med_assessment_remarks']) }}</span></span></div>
        </section>

        <section class="health-overview-card" aria-label="Certification and Verification">
            <div class="health-overview-heading"><span class="health-overview-heading-icon" aria-hidden="true"><x-outline-icon name="shield-check" /></span><div class="health-overview-heading-copy"><h4>Certification &amp; Verification</h4><p>Record of medical assessment and verification.</p></div></div>
            <div class="health-overview-metrics is-two">
                <div class="health-overview-metric is-stacked"><span class="health-overview-metric-icon" aria-hidden="true"><x-outline-icon name="calendar-days" /></span><span class="health-overview-metric-copy"><span class="health-overview-metric-label">Assessment Date</span><span class="health-overview-metric-value">{{ $formatProfileDate($healthData['assessment_date'] ?? null) }}</span></span></div>
                <div class="health-overview-metric is-stacked"><span class="health-overview-metric-icon" aria-hidden="true"><x-outline-icon name="shield-check" /></span><span class="health-overview-metric-copy"><span class="health-overview-metric-label">Verified At</span><span class="health-overview-metric-value">{{ $formatProfileDate($healthData['verified_at'] ?? null) }}</span></span></div>
            </div>
        </section>
    </div>
@else
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
@endif
