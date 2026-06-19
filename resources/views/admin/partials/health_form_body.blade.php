<div class="print-container">
    <div class="print-page">
    <div class="document-code">
        <strong>PUP-HIFS-6-MEDS-028</strong><br>
        Rev. 0<br>
        June 26, 2024
    </div>
    <table class="official-header-table">
        <tr>
            <td class="official-logo-cell">
                <img src="{{ $healthFormLogo ?? asset('images/pup_logo.png') }}" class="logo">
            </td>
            <td class="official-heading-cell">
                <p>Republic of the Philippines</p>
                <p class="univ-name">POLYTECHNIC UNIVERSITY OF THE PHILIPPINES</p>
                <p>Office of the Vice President for Administration</p>
                <p class="dept-name">MEDICAL SERVICES DEPARTMENT</p>
            </td>
            <td class="official-photo-cell">
                <div class="photo-box">2x2 or passport-sized<br>current colored ID photo</div>
            </td>
        </tr>
    </table>

    <table class="official-title-table">
        <tr>
            <td>HEALTH INFORMATION FORM</td>
        </tr>
    </table>

    <div class="section-header">PART I. STUDENT INFORMATION</div>
    @php
        $printedStudentName = trim((string) (
            ($healthFormIdentity['puptas_full_name'] ?? '')
            ?: ($healthFormIdentity['full_name'] ?? '')
            ?: ($profile->user->name ?? '')
            ?: trim(implode(' ', array_filter([
                $profile->user->first_name ?? '',
                $profile->user->middle_name ?? '',
                $profile->user->last_name ?? '',
            ])))
        )) ?: 'N/A';
        $printedStudentEmail = trim((string) ($healthFormIdentity['email'] ?? ''))
            ?: trim((string) ($profile->user->email ?? ''));
    @endphp
    <table class="student-information-table">
        <colgroup>
            <col style="width: 9%;">
            <col style="width: 53%;">
            <col style="width: 13%;">
            <col style="width: 25%;">
        </colgroup>
        <tr>
            <td class="student-name-cell" colspan="2">
                <span class="line-label">Name:</span>
                <span class="line-value student-full-name">{{ $printedStudentName }}</span>
            </td>
            <td class="student-number-label">PUP Student No.:</td>
            <td class="student-number-value">{{ $profile->student_number ?: $profile->user->student_number }}</td>
        </tr>
        <tr>
            <td class="line-label home-address-label-cell">Home Address:</td>
            <td class="line-value home-address-value-cell">{{ $profile->home_address ?? '' }}</td>
            <td class="line-label school-year-label">School Year:</td>
            <td class="line-value">{{ $profile->school_year ?? '2025-2026' }}</td>
        </tr>
        <tr>
            <td colspan="4" class="compound-row">
                <table class="compound-information-table">
                    <tr>
                        <td class="line-label age-label">Age:</td>
                        <td class="line-value age-value">{{ $profile->age ?? '' }}</td>
                        <td class="line-label sex-label">Sex:</td>
                        <td class="line-value sex-value">{{ $profile->sex ?? '' }}</td>
                        <td class="line-label civil-label">Civil Status:</td>
                        <td class="line-value civil-value">{{ $profile->civil_status ?? '' }}</td>
                        <td class="line-label course-label">Course / College:</td>
                        <td class="line-value course-value">{{ $profile->course_college ?? '' }}</td>
                    </tr>
                </table>
            </td>
        </tr>
        <tr>
            <td colspan="4" class="compound-row">
                <table class="blood-email-table">
                    <tr>
                        <td class="line-label blood-type-label">Blood Type:</td>
                        <td class="line-value blood-type-value">{{ $profile->blood_type ?? 'N/A' }}</td>
                        <td class="line-label email-label">Email Address:</td>
                        <td class="line-value email-value">{{ $printedStudentEmail }}</td>
                    </tr>
                </table>
            </td>
        </tr>
        <tr>
            <td class="guardian-row" colspan="4">
                <span class="line-label">Parent's Name / Guardian / Spouse:</span>
                <span class="line-value guardian-value">{{ $profile->guardian_name ?? '' }}</span>
            </td>
        </tr>
        <tr>
            <td colspan="4" class="compound-row">
                <div class="contact-information-row">
                    <span class="contact-label landline-label">Landline:</span><span class="contact-value landline-value">{{ $profile->landline ?? 'N/A' }}</span><span class="contact-label cellphone-label">Cellphone:</span><span class="contact-value cellphone-value">{{ $profile->cellphone ?? '' }}</span>
                </div>
            </td>
        </tr>
    </table>

    <div class="section-header">PART II. MEDICAL HISTORY</div>
    <div class="row medical-attention-row">
        <span class="medical-subsection-title">1. Do you need medical attention or has known medical illness?</span>
        <div class="check-item"><div class="box-ui">{{ $profile->has_illness == 'No' ? '/' : '' }}</div> No</div>
        <div class="check-item"><div class="box-ui">{{ $profile->has_illness == 'Yes' ? '/' : '' }}</div> Yes</div>
    </div>
    <div class="medical-history-instruction">(Please check the following that apply as needed)</div>
    
    <table class="medical-check-table">
        @php
            $illnesses = ['Asthma', 'Loss of Consciousness', 'Eye Disease / Defect', 'Accident Injuries', 'Diabetes', 'Heart Disease', 'Kidney Disease', 'Tuberculosis', 'Convulsion / Epilepsy', 'Migraine', 'Hyperventilation', 'High Blood Pressure', 'Hemophilia', 'Primary Complex'];
            $saved_history = is_array($profile->medical_history) ? $profile->medical_history : json_decode($profile->medical_history ?? '[]', true);
        @endphp
        @foreach(array_chunk($illnesses, 4) as $illnessRow)
            <tr>
                @foreach($illnessRow as $illness)
                    @php
                        $illnessChecked = in_array($illness, $saved_history, true)
                            || (
                                in_array($illness, ['Tuberculosis', 'Primary Complex'], true)
                                && in_array('Tuberculosis / Primary Complex', $saved_history, true)
                            );
                    @endphp
                    <td><span class="box-ui">{{ $illnessChecked ? '/' : '' }}</span> {{ $illness }}</td>
                @endforeach
                @for($emptyCell = count($illnessRow); $emptyCell < 4; $emptyCell++)
                    <td></td>
                @endfor
            </tr>
        @endforeach
        <tr>
            <td colspan="4"><strong>Others (Please indicate):</strong> <span class="write-line">{{ $profile->other_illness ?? '' }}</span></td>
        </tr>
    </table>

    <div class="row disability-row">
        <span class="medical-subsection-title">2. Do you have disability?</span>
        <div class="check-item"><div class="box-ui">{{ $profile->has_disability == 'No' ? '/' : '' }}</div> None</div>
        <div class="check-item"><div class="box-ui">{{ $profile->has_disability == 'Yes' ? '/' : '' }}</div> Yes: What type of disability?</div>
        <div class="field">{{ $profile->disability_type ?? '' }}</div>
    </div>

    <div class="medical-subsection-title medical-subsection-heading">
        3. Additional Information for Students and Medical Conditions:
    </div>
    <div class="allergy-declaration">
        As a Parent / Guardian, I would like to declare that my child has history of allergies to the following:
    </div>
    <div class="row">
        <span class="label">Food (Please specify):</span> <div class="field">{{ $profile->food_allergies ?? '' }}</div>
        <span class="label">No Known Allergies:</span> <div class="box-ui">{{ $profile->no_allergies ? '/' : '' }}</div>
    </div>
    @php
        $saved_meds = is_array($profile->medicine_allergies) ? $profile->medicine_allergies : json_decode($profile->medicine_allergies ?? '[]', true);
    @endphp
    <table class="medicine-allergy-table">
        <tr>
            <td class="medicine-label-cell">Medicines:</td>
            <td class="medicine-item-cell"><span class="box-ui">{{ in_array('Aspirin', $saved_meds) ? '/' : '' }}</span>Aspirin</td>
            <td class="medicine-item-cell"><span class="box-ui">{{ in_array('Ibuprofen', $saved_meds) ? '/' : '' }}</span>Ibuprofen</td>
            <td class="medicine-item-cell-wide"><span class="box-ui">{{ in_array('Amoxicillin', $saved_meds) ? '/' : '' }}</span>Amoxicillin</td>
            <td class="medicine-item-cell-wide"><span class="box-ui">{{ in_array('Mefenamic Acid', $saved_meds) ? '/' : '' }}</span>Mefenamic Acid</td>
            <td class="medicine-item-cell"><span class="box-ui">{{ in_array('Penicillin', $saved_meds) ? '/' : '' }}</span>Penicillin</td>
            <td class="medicine-other-cell">Others: <span class="field medicine-other-field">{{ $profile->other_med_allergies ?? '' }}</span></td>
        </tr>
    </table>

    <div class="section-header">PART III. PERSONAL SOCIAL HISTORY</div>
    <table class="social-history-table">
        <tr>
            <td class="social-label">Cigarette Smoking:</td>
            <td><span class="box-ui">{{ ($profile->is_smoker ?? '') == 'Yes' ? '/' : '' }}</span> Yes</td>
            <td><span class="box-ui">{{ ($profile->is_smoker ?? '') == 'No' ? '/' : '' }}</span> No</td>
        </tr>
        <tr>
            <td class="social-label">Alcohol Drinking:</td>
            <td><span class="box-ui">{{ ($profile->is_drinker ?? '') == 'Yes' ? '/' : '' }}</span> Yes</td>
            <td><span class="box-ui">{{ ($profile->is_drinker ?? '') == 'No' ? '/' : '' }}</span> No</td>
        </tr>
    </table>

    <table class="covid-layout-table">
        <tr>
            <td class="covid-label-cell">
                <span class="medical-subsection-title">4. COVID-19 Vaccination History:</span>
                <div class="vaccinated-choice">
                    <span class="label">Vaccinated:</span>
                    <span class="check-item"><span class="box-ui">{{ ($profile->covid_vaccinated ?? '') === 'Yes' ? '/' : '' }}</span> Yes</span>
                    <span class="check-item"><span class="box-ui">{{ ($profile->covid_vaccinated ?? '') !== 'Yes' ? '/' : '' }}</span> No</span>
                </div>
            </td>
            <td>
        <table class="vax-table">
            @php $vax = is_array($profile->vaccine_history) ? $profile->vaccine_history : json_decode($profile->vaccine_history ?? '[]', true); @endphp
            <thead><tr><th>If Yes (Vaccinated)</th><th>Date Received</th><th>Brand</th></tr></thead>
            <tbody>
                <tr><td>1st Dose</td><td>{{ $vax['first_dose']['date'] ?? '' }}</td><td>{{ $vax['first_dose']['brand'] ?? '' }}</td></tr>
                <tr><td>2nd Dose</td><td>{{ $vax['second_dose']['date'] ?? '' }}</td><td>{{ $vax['second_dose']['brand'] ?? '' }}</td></tr>
                <tr><td>Booster 1st Dose</td><td>{{ $vax['booster_1']['date'] ?? '' }}</td><td>{{ $vax['booster_1']['brand'] ?? '' }}</td></tr>
                <tr><td>Booster 2nd Dose</td><td>{{ $vax['booster_2']['date'] ?? '' }}</td><td>{{ $vax['booster_2']['brand'] ?? '' }}</td></tr>
            </tbody>
        </table>
            </td>
        </tr>
    </table>

    <div class="cert-text cert-text-first">
        I hereby certify that the medical health information given to PUP Medical Services are true, correct and fully disclosed to the best of
my knowledge and all the medical condition that may affect in the assessment for purpose of consultation/ issuance of medical
clearance/ certificate as a student of PUP.
    </div>
<div class="cert-text">
        I also understand that the PUP MSD and university will not be liable for any untoward incident that may arise due to any failure to
disclose accurate information or intentionally providing false and deceptive information.
    </div>
    <div class="cert-text">
        In compliance with the Data Privacy Act of 2012 and its IRR, I voluntarily consent to the collection, processing and storage of my
personal and health information for the purpose/s of health assessment/ treatment/ or research following research ethics guidelines
for the improvement of healthcare services.
    </div>

    <div class="signature-physician-block">
        <table class="signature-table">
            <tr>
                <td>
                    <div class="signature-space"></div>
                    <div class="sig-line"></div>
                    <div class="signature-caption">(Signature of parent/guardian for<br>students below 18 years old)</div>
                </td>
                <td>
                    @if(empty($studentPrintCopy) && $profile->digital_signature)
                        <img src="{{ asset('storage/' . $profile->digital_signature) }}" class="sig-image" style="height: 40px; width: auto;">
                    @else
                        <div class="signature-space"></div>
                    @endif
                    <div class="sig-line">{{ empty($studentPrintCopy) ? strtoupper($printedStudentName) : '' }}</div>
                    <div class="signature-caption">(Printed name and signature of student)</div>
                </td>
                <td>
                    <div class="signature-space signature-date-space">
                    </div>
                    <div class="sig-line"></div>
                    <div class="signature-caption">Date</div>
                </td>
            </tr>
        </table>

        <div class="physician-section">
            <p style="text-align: center; font-weight: bold; margin-bottom: 2px; font-size: 14px; text-transform: uppercase;">FOR PHYSICIAN ONLY</p>
            <p class="physician-check-instruction">Please Check</p>
            
            <table class="physician-clearance-table">
                <tr>
                    <td class="physician-clearance-label">Medical Clearance:</td>
                    <td>
                        <span class="box-ui physician-box">
                        {{ empty($studentPrintCopy) && in_array($profile->clearance_status, ['Issued', 'Fully Cleared'], true) ? '/' : '' }}
                        </span>
                        Issued
                    </td>
                    <td class="physician-pending-cell">
                        <span class="box-ui physician-box">
                        {{ empty($studentPrintCopy) && in_array($profile->clearance_status, ['Pending', 'For Verification'], true) ? '/' : '' }}
                        </span>
                        Pending, Reason:
                        <span class="physician-reason-line">
                            {{ empty($studentPrintCopy) && in_array($profile->clearance_status, ['Pending', 'For Verification'], true) ? $profile->pending_reason : '' }}
                        </span>
                    </td>
                </tr>
            </table>

            <table class="physician-signature-table">
                <tr>
                    <td>
                        <div class="physician-signature-line">
                            {{ empty($studentPrintCopy) && $profile->verified_at ? \Carbon\Carbon::parse($profile->verified_at)->format('m/d/Y') : '' }}
                        </div>
                        <div class="physician-signature-label">Date:</div>
                    </td>
                    <td>
                        <div class="physician-signature-line"></div>
                        <div class="physician-signature-label">Physician's Name and Signature</div>
                    </td>
                </tr>
            </table>
        </div>
    </div>

</div>
</div>
