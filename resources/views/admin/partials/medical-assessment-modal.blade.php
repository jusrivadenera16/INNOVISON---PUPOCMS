@if($canUpdateMedicalAssessment)
    @php
        $medicalAssessmentPersonType = $medicalAssessmentProfileTypeLabel ?? 'Student';
        $medicalMissingValueLabel = 'No information provided';
        $medicalDisplayValue = static function ($value) use ($medicalMissingValueLabel): string {
            $value = is_scalar($value) ? trim((string) $value) : '';

            return $value === '' || strcasecmp($value, 'Not recorded') === 0
                ? $medicalMissingValueLabel
                : $value;
        };
        $medicalInformationSections = collect($medicalAssessmentSnapshot ?? [])->map(function ($items, $name) use ($medicalDisplayValue) {
            $name = in_array($name, ['Medical Certificate & Imaging', 'Medical Certificates & Imaging', 'Medical Documents'], true)
                ? 'Documents'
                : (string) $name;
            $normalizedName = strtolower((string) $name);
            $items = collect(is_array($items) ? $items : [])->map($medicalDisplayValue)->all();
            if ($name === 'Documents') {
                $items = collect($items)
                    ->reject(fn ($value, $label) => strtolower(trim((string) $label)) === 'other medical assessment file')
                    ->all();
            }
            if ($name === 'Vitals & Assessment') {
                $items = collect($items)->except(['Medical condition remarks', 'Nurse review remarks'])->all();
            }
            if ($name === 'Referral') {
                $items = collect($items)->except(['Encoded findings'])->all();
            }
            $icon = match (true) {
                str_contains($normalizedName, 'personal') => 'identification',
                str_contains($normalizedName, 'address') => 'map-pin',
                str_contains($normalizedName, 'emergency') => 'phone',
                str_contains($normalizedName, 'academic') => 'academic-cap',
                str_contains($normalizedName, 'employment') => 'briefcase',
                str_contains($normalizedName, 'allerg') => 'heart',
                str_contains($normalizedName, 'vital'), str_contains($normalizedName, 'assessment') => 'heart-pulse',
                str_contains($normalizedName, 'lifestyle'), str_contains($normalizedName, 'immun') => 'heart-pulse',
                str_contains($normalizedName, 'document'), str_contains($normalizedName, 'certificate'), str_contains($normalizedName, 'imaging') => 'document-text',
                str_contains($normalizedName, 'referral'), str_contains($normalizedName, 'follow-up') => 'arrow-path',
                str_contains($normalizedName, 'hospital'), str_contains($normalizedName, 'examination') => 'clipboard-document-list',
                default => 'heart-pulse',
            };
            $overviewItems = collect($items)->take(7);
            if ($name === 'Documents') {
                $overviewItems = collect($items)->only(['Medical certificate', 'Chest X-ray report']);
                if ($overviewItems->isEmpty()) {
                    $overviewItems = collect($items)->only(['Medical certificate', 'Chest X-ray document', 'Health declaration', 'PWD ID proof']);
                }
            }
            if ($name === 'Medical History' && strtolower(trim((string) ($items['Known illness'] ?? ''))) !== 'yes') {
                $overviewItems = $overviewItems->except(['Medical conditions', 'Other illness']);
            }
            if ($name === 'Allergies' && strtolower(trim((string) ($items['Known allergies'] ?? ''))) !== 'yes') {
                $overviewItems = $overviewItems->except(['Food allergies', 'Medicine allergies', 'Other medicine allergies']);
            }

            return [
                'id' => 'category-' . \Illuminate\Support\Str::slug((string) $name),
                'name' => (string) $name,
                'icon' => $icon,
                'items' => $items,
                'overview_items' => $overviewItems->all(),
            ];
        })->values();
        $medicalHistorySectionNames = [
            'Medical History',
            'Allergies',
            'Lifestyle & Immunization',
            'Medical & Family History',
            'Hospitalization & Surgery',
            'Medications, Allergies & Lifestyle',
        ];
        $medicalAssessmentIsEmployee = in_array(strtolower((string) ($medicalAssessmentProfileTypeLabel ?? '')), ['faculty', 'admin'], true);
        $medicalPersonalSectionNames = $medicalAssessmentIsEmployee
            ? ['Personal Information', 'Address', 'Emergency Contact', 'Employment Information']
            : ['Personal Information', 'Address', 'Emergency Contact', 'Academic Information'];
        $medicalAssessmentCourseCodes = [
            'BSBA-HRM', 'BSBA-MM', 'BSECE', 'BSIT', 'BSME', 'BSOA', 'BSPSY',
            'BSED-ENGLISH', 'BSED-MATH', 'DIT', 'DOMT',
        ];
        $medicalInformationEditMap = $medicalAssessmentIsEmployee ? [
            'Personal Information' => [
                'Date of birth' => ['key' => 'birthday', 'type' => 'date'],
                'Sex' => ['key' => 'sex', 'type' => 'text'],
                'Contact number' => ['key' => 'contact_no', 'type' => 'tel'],
            ],
            'Address' => [
                'Address' => ['key' => 'address_parts', 'type' => 'address'],
            ],
            'Emergency Contact' => [
                'Emergency contact person' => ['key' => 'emergency_contact_person', 'type' => 'text'],
                'Emergency contact number' => ['key' => 'emergency_contact_no', 'type' => 'tel'],
            ],
            'Employment Information' => [
                'Office' => ['key' => 'office', 'type' => 'text'],
            ],
            'Medical & Family History' => [
                'Past medical history' => ['key' => 'past_medical_history', 'type' => 'json-text'],
                'Other past medical history' => ['key' => 'past_medical_history_others', 'type' => 'textarea'],
                'Family history' => ['key' => 'family_history', 'type' => 'json-text'],
                'Other family history' => ['key' => 'family_history_others', 'type' => 'textarea'],
                'Disability' => ['key' => 'has_disability', 'type' => 'boolean'],
                'Disability type' => ['key' => 'disability_type', 'type' => 'text', 'depends_on' => ['key' => 'has_disability', 'type' => 'boolean', 'value' => 'Yes']],
            ],
            'Hospitalization & Surgery' => [
                'Previous hospitalization' => ['key' => 'previous_hospitalization', 'type' => 'boolean'],
                'Hospitalization details' => ['key' => 'previous_hospitalization_details', 'type' => 'textarea', 'depends_on' => ['key' => 'previous_hospitalization', 'type' => 'boolean', 'value' => 'Yes']],
                'Operation / surgery' => ['key' => 'operation_surgery', 'type' => 'boolean'],
                'Operation / surgery details' => ['key' => 'operation_surgery_details', 'type' => 'textarea', 'depends_on' => ['key' => 'operation_surgery', 'type' => 'boolean', 'value' => 'Yes']],
            ],
            'Medications, Allergies & Lifestyle' => [
                'Current medications' => ['key' => 'current_medications', 'type' => 'textarea'],
                'Allergies' => ['key' => 'allergies', 'type' => 'textarea'],
                'Cigarette smoking' => ['key' => 'cigarette_smoking', 'type' => 'boolean'],
                'Alcohol drinking' => ['key' => 'alcohol_drinking', 'type' => 'boolean'],
                'Traveled abroad' => ['key' => 'traveled_abroad', 'type' => 'boolean'],
            ],
            'Vital Signs' => [
                'Distress status' => ['key' => 'vital_signs_distress_status', 'type' => 'text'],
                'Height' => ['key' => 'height', 'type' => 'text', 'unit' => 'ft'],
                'Weight' => ['key' => 'weight', 'type' => 'number', 'unit' => 'lbs'],
                'BMI' => ['key' => 'bmi', 'type' => 'number'],
                'Blood pressure' => ['key' => 'bp', 'type' => 'text'],
                'Heart rate' => ['key' => 'hr', 'type' => 'number'],
                'Respiratory rate' => ['key' => 'rr', 'type' => 'number'],
                'Temperature' => ['key' => 'temperature', 'type' => 'number', 'unit' => '°C'],
            ],
            'Physical Examination Findings' => [
                'Head' => ['key' => 'head_findings', 'type' => 'choice-checkboxes', 'options' => ['Wound' => 'Wound', 'Mass' => 'Mass', 'Alopecia' => 'Alopecia']],
                'Eyes' => ['key' => 'eyes_findings', 'type' => 'choice-checkboxes', 'options' => ['w/o Glasses' => 'w/o Glasses', 'w/ Glasses' => 'w/ Glasses', 'Anicteric Sclera' => 'Anicteric Sclera', 'Pink Palpebral Conjunctiva' => 'Pink Palpebral Conjunctiva']],
                'Ears' => ['key' => 'ears_findings', 'type' => 'choice-checkboxes', 'options' => ['No Gross Deformity' => 'No Gross Deformity', 'No Discharge' => 'No Discharge']],
                'Throat' => ['key' => 'throat_findings', 'type' => 'choice-checkboxes', 'options' => ['No TPC' => 'No TPC', 'No Mass' => 'No Mass', 'No lymphadenopathy' => 'No lymphadenopathy']],
                'Chest & lungs' => ['key' => 'chest_lungs_findings', 'type' => 'choice-radios-json', 'options' => ['Normal' => 'Normal', 'Wheeze' => 'Wheeze', 'Rales' => 'Rales']],
                'Chest X-ray' => ['key' => 'chest_xray_result', 'type' => 'choice-radios', 'options' => ['Normal' => 'Normal', 'With Findings' => 'With Findings', 'N/A' => 'N/A']],
                'Breast' => ['key' => 'breast_findings', 'type' => 'choice-checkbox', 'options' => ['Normal' => 'Normal']],
                'Heart murmur' => ['key' => 'heart_murmur', 'type' => 'choice-radios', 'options' => ['Present' => 'Present', 'Absent' => 'Absent', 'N/A' => 'N/A']],
                'Heart rhythm' => ['key' => 'heart_rhythm', 'type' => 'choice-radios', 'options' => ['Regular' => 'Regular', 'Irregular' => 'Irregular', 'N/A' => 'N/A']],
                'Abdomen' => ['key' => 'abdomen_findings', 'type' => 'choice-checkbox', 'options' => ['Normal' => 'Normal']],
                'Last menstruation' => ['key' => 'genito_urinary_date_lmp', 'type' => 'date'],
                'Extremities' => ['key' => 'extremities_findings', 'type' => 'choice-checkbox', 'options' => ['No Deformities' => 'No Deformities']],
                'Vertebral column' => ['key' => 'vertebral_column_findings', 'type' => 'choice-radios', 'options' => ['Normal' => 'Normal', 'With Deformity' => 'With Deformity']],
                'Skin' => ['key' => 'skin_findings', 'type' => 'choice-checkboxes', 'options' => ['Pallor' => 'Pallor', 'Rashes' => 'Rashes', 'Lesions' => 'Lesions']],
                'Scars' => ['key' => 'scars_findings', 'type' => 'choice-radios', 'options' => ['Absent' => 'Absent', 'Present' => 'Present']],
            ],
            'Assessment & Follow-up' => [
                'Working impression' => ['key' => 'working_impression', 'type' => 'text'],
                'Fit status' => ['key' => 'fit_status', 'type' => 'select-other', 'options' => [
                    'Fit for Work' => 'Fit for Work',
                    'Fit for School' => 'Fit for School',
                    'Fit for Duty' => 'Fit for Duty',
                    'Fit for Internship' => 'Fit for Internship',
                    'Fit for Clinic Duty' => 'Fit for Clinic Duty',
                    'Others' => 'Others',
                ]],
                'For work-up' => ['key' => 'for_work_up', 'type' => 'text'],
                'Referred to' => ['key' => 'referred_to', 'type' => 'choice-checkboxes', 'options' => [
                    'Cardio' => 'Cardio', 'Derma' => 'Derma', 'ENT' => 'ENT', 'Optha' => 'Optha', 'Pulmo' => 'Pulmo',
                ]],
                'Other referral' => ['key' => 'referred_to_others', 'type' => 'text'],
                'Follow-up date' => ['key' => 'follow_up_on', 'type' => 'date'],
            ],
        ] : [
            'Personal Information' => [
                'Student number' => ['key' => 'student_number', 'type' => 'text'],
                'Sex' => ['key' => 'sex', 'type' => 'select', 'options' => [
                    'Male' => 'Male',
                    'Female' => 'Female',
                ]],
                'Contact number' => ['key' => 'user_contact_no', 'type' => 'tel'],
            ],
            'Address' => [
                'Address' => ['key' => 'address_parts', 'type' => 'address'],
                'ZIP code' => ['key' => 'zipcode', 'type' => 'text', 'pattern' => '[0-9]{4}', 'min_length' => '4', 'max_length' => '4', 'inputmode' => 'numeric', 'autocomplete' => 'postal-code', 'title' => 'Enter a 4-digit ZIP code.'],
            ],
            'Emergency Contact' => [
                'Emergency contact person' => ['key' => 'guardian_name', 'type' => 'text'],
                'Emergency contact number' => ['key' => 'cellphone', 'type' => 'tel'],
                'Landline' => ['key' => 'landline', 'type' => 'tel'],
            ],
            'Academic Information' => [
                'Course Code' => ['key' => 'course_code', 'type' => 'select', 'options' => array_combine($medicalAssessmentCourseCodes, $medicalAssessmentCourseCodes)],
                'Year' => ['key' => 'user_year', 'type' => 'select', 'options' => ['1' => '1st Year', '2' => '2nd Year', '3' => '3rd Year', '4' => '4th Year']],
                'Section' => ['key' => 'user_section', 'type' => 'select', 'options' => ['1' => '1', '2' => '2', '3' => '3', '4' => '4']],
            ],
            'Medical History' => [
                'Known illness' => ['key' => 'has_illness', 'type' => 'yes-no'],
                'Medical conditions' => ['key' => 'medical_history', 'type' => 'json-text', 'depends_on' => ['key' => 'has_illness', 'type' => 'yes-no', 'value' => 'Yes']],
                'Other illness' => ['key' => 'other_illness', 'type' => 'textarea', 'depends_on' => ['key' => 'has_illness', 'type' => 'yes-no', 'value' => 'Yes']],
                'Blood type' => ['key' => 'blood_type', 'type' => 'select', 'options' => [
                    'Unknown' => 'Unknown',
                    'A+' => 'A+', 'A-' => 'A-',
                    'B+' => 'B+', 'B-' => 'B-',
                    'AB+' => 'AB+', 'AB-' => 'AB-',
                    'O+' => 'O+', 'O-' => 'O-',
                    'Not Known' => 'Not Known',
                ]],
                'Disability' => ['key' => 'has_disability', 'type' => 'yes-no'],
                'Disability type' => ['key' => 'disability_type', 'type' => 'select', 'options' => [
                    'Physical' => 'Physical',
                    'Visual' => 'Visual',
                    'Hearing' => 'Hearing',
                    'Speech and Language' => 'Speech and Language',
                    'Intellectual' => 'Intellectual',
                    'Psychosocial' => 'Psychosocial',
                    'Learning' => 'Learning',
                    'Multiple Disabilities' => 'Multiple Disabilities',
                    'Others' => 'Others',
                ], 'depends_on' => ['key' => 'has_disability', 'type' => 'yes-no', 'value' => 'Yes']],
            ],
            'Allergies' => [
                'Known allergies' => ['key' => 'known_allergies', 'type' => 'yes-no'],
                'Food allergies' => ['key' => 'food_allergies', 'type' => 'textarea', 'depends_on' => ['key' => 'known_allergies', 'type' => 'yes-no', 'value' => 'Yes']],
                'Medicine allergies' => ['key' => 'medicine_allergies', 'type' => 'json-text', 'depends_on' => ['key' => 'known_allergies', 'type' => 'yes-no', 'value' => 'Yes']],
                'Other medicine allergies' => ['key' => 'other_med_allergies', 'type' => 'textarea', 'depends_on' => ['key' => 'known_allergies', 'type' => 'yes-no', 'value' => 'Yes']],
            ],
            'Lifestyle & Immunization' => [
                'Smoking' => ['key' => 'is_smoker', 'type' => 'yes-no'],
                'Alcohol use' => ['key' => 'is_drinker', 'type' => 'yes-no'],
                'COVID-19 vaccination' => ['key' => 'covid_vaccinated', 'type' => 'yes-no'],
                'Vaccine history' => ['key' => 'vaccine_history', 'type' => 'vaccine-history', 'depends_on' => ['key' => 'covid_vaccinated', 'type' => 'yes-no', 'value' => 'Yes']],
                'COVID-19 positive' => ['key' => 'covid_positive', 'type' => 'yes-no'],
                'COVID-19 positive date' => ['key' => 'covid_positive_date', 'type' => 'date', 'depends_on' => ['key' => 'covid_positive', 'type' => 'yes-no', 'value' => 'Yes']],
            ],
            'Vitals & Assessment' => [
                'Height' => ['key' => 'height', 'type' => 'number', 'unit' => 'ft', 'min' => '1', 'max' => '8', 'step' => '0.01'],
                'Weight' => ['key' => 'weight', 'type' => 'number', 'unit' => 'lbs', 'min' => '1', 'max' => '1000', 'step' => '0.1'],
                'Blood pressure' => ['key' => 'blood_pressure', 'type' => 'text', 'pattern' => '\d{2,3}/\d{2,3}', 'title' => 'Use systolic/diastolic format, for example 120/80.'],
                'Pulse rate' => ['key' => 'pulse_rate', 'type' => 'number', 'min' => '20', 'max' => '250', 'step' => '1'],
                'Respiratory rate' => ['key' => 'respiratory_rate', 'type' => 'number', 'min' => '5', 'max' => '80', 'step' => '1'],
                'Temperature' => ['key' => 'temperature', 'type' => 'number', 'unit' => '°C', 'min' => '30', 'max' => '45', 'step' => '0.1'],
                'Assessment date' => ['key' => 'assessment_date', 'type' => 'date', 'max' => now()->toDateString()],
                'Assessment remarks' => ['key' => 'med_assessment_remarks', 'type' => 'textarea'],
            ],
            'Documents' => [
                'Certificate doctor' => ['key' => 'doctor_name', 'type' => 'text'],
                'Certificate date' => ['key' => 'med_cert_date', 'type' => 'date'],
                'Certificate findings' => [
                    'key' => 'med_cert_findings',
                    'type' => 'select',
                    'options' => [
                        'No Findings / Normal' => 'No Findings / Normal',
                        'With Findings' => 'With Findings',
                        'Not Sure / For Clinic Review' => 'Not Sure / For Clinic Review',
                    ],
                ],
                'Certificate findings details' => [
                    'key' => 'med_cert_findings_details',
                    'type' => 'textarea',
                    'depends_on' => ['key' => 'med_cert_findings', 'type' => 'text', 'value' => 'With Findings'],
                ],
                'Chest X-ray result' => ['key' => 'chest_xray_result_text', 'type' => 'textarea'],
                'Chest X-ray date' => ['key' => 'chest_xray_date', 'type' => 'date'],
                'Chest X-ray findings' => [
                    'key' => 'xray_findings',
                    'type' => 'select',
                    'options' => [
                        'Normal' => 'Normal',
                        'With Findings' => 'With Findings',
                        'Not Sure / For Clinic Review' => 'Not Sure / For Clinic Review',
                    ],
                ],
                'Chest X-ray findings details' => [
                    'key' => 'xray_findings_details',
                    'type' => 'textarea',
                    'depends_on' => ['key' => 'xray_findings', 'type' => 'text', 'value' => 'With Findings'],
                ],
            ],
            'Referral' => [
                'Referral details' => ['key' => 'referral_details', 'type' => 'json-text'],
            ],
        ];
        $formatMedicalEditValue = static function ($value, string $type): string {
            if (is_string($value) && strcasecmp(trim($value), 'Not recorded') === 0) {
                return '';
            }
            if (in_array($type, ['boolean', 'yes-no'], true)) {
                return is_bool($value) ? ($value ? 'Yes' : 'No') : trim((string) ($value ?? ''));
            }
            if ($value instanceof \DateTimeInterface) {
                return $value->format('Y-m-d');
            }
            if ($type === 'json-text') {
                if (is_string($value)) {
                    $decoded = json_decode($value, true);
                    if (json_last_error() === JSON_ERROR_NONE && is_array($decoded)) {
                        $value = $decoded;
                    } elseif (in_array(trim($value), ['[]', '{}'], true)) {
                        return '';
                    } else {
                        return trim($value);
                    }
                }
                if (!is_array($value) || $value === []) {
                    return '';
                }
                if (array_is_list($value) && count(array_filter($value, 'is_array')) === 0) {
                    $values = array_map(static function ($item): string {
                        return is_bool($item) ? ($item ? 'Yes' : 'No') : (is_scalar($item) ? trim((string) $item) : '');
                    }, $value);

                    return implode("\n", array_values(array_filter($values, static fn ($item) => $item !== '')));
                }

                $lines = [];
                $flatten = static function (array $items, array $path = []) use (&$flatten, &$lines): void {
                    foreach ($items as $key => $item) {
                        $itemPath = [...$path, (string) $key];
                        if (is_array($item)) {
                            $flatten($item, $itemPath);
                        } elseif (is_bool($item)) {
                            $lines[] = implode('.', $itemPath) . ' = ' . ($item ? 'Yes' : 'No');
                        } elseif (is_scalar($item) && trim((string) $item) !== '') {
                            $lines[] = implode('.', $itemPath) . ' = ' . trim((string) $item);
                        }
                    }
                };
                $flatten($value);

                return implode("\n", $lines);
            }
            if (str_starts_with($type, 'choice-')) {
                return is_array($value)
                    ? (json_encode($value, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) ?: '')
                    : trim((string) ($value ?? ''));
            }
            if ($type === 'vaccine-history') {
                return '';
            }
            if ($type === 'json') {
                return json_encode($value ?? [], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) ?: '[]';
            }

            return trim((string) ($value ?? ''));
        };
        $normalizeMedicalChoiceValues = static function ($value): array {
            if (is_string($value)) {
                $decoded = json_decode($value, true);
                if (json_last_error() === JSON_ERROR_NONE && is_array($decoded)) {
                    $value = $decoded;
                } elseif (in_array(trim($value), ['', '[]', '{}'], true)) {
                    return [];
                } else {
                    $value = [$value];
                }
            }

            if (!is_array($value)) {
                $value = is_scalar($value) && trim((string) $value) !== '' ? [$value] : [];
            }

            return array_values(array_filter(array_map(
                static fn ($item) => is_scalar($item) ? trim((string) $item) : '',
                $value
            ), static fn ($item) => $item !== ''));
        };
        if ($medicalAssessmentIsEmployee) {
            $medicalDocumentOrder = [
                'Medical Certificate' => 0,
                'Chest X-ray Report' => 1,
                'Health Form' => 2,
                'Health Declaration' => 3,
                'PWD ID Proof' => 4,
                '2x2 Photo' => 5,
            ];
            $employeeDocumentLabels = [
                'health_form' => 'Health Form',
                'student_photo' => '2x2 Photo',
                'health_declaration' => 'Health Declaration',
                'medical_certificate' => 'Medical Certificate',
                'chest_xray_document' => 'Chest X-ray Report',
                'pwd_id_proof' => 'PWD ID Proof',
            ];
            $medicalAssessmentDocuments = collect($employeeDocuments ?? [])
                ->filter(fn (array $document) => array_key_exists($document['key'] ?? '', $employeeDocumentLabels)
                    && (($document['key'] ?? '') !== 'pwd_id_proof' || (bool) ($document['uploaded'] ?? false)))
                ->map(fn (array $document) => [
                    'label' => $employeeDocumentLabels[$document['key']] ?? 'Document',
                    'name' => $document['name'] ?? 'Missing',
                    'uploaded' => (bool) ($document['uploaded'] ?? false),
                    'url' => $document['view_url'] ?? null,
                ])
                ->sortBy(fn (array $document) => $medicalDocumentOrder[$document['label']] ?? 99)
                ->values();
        } else {
            $documentRouteName = $documentRouteName ?? (request()->routeIs('assistant.*') ? 'assistant.walkin.document' : 'walkin.document');
            $medicalAssessmentHealthFormSubmission = collect($healthFormSubmissions ?? [])
                ->first(fn ($submission) => filled($submission->pdf_path ?? null));
            $medicalAssessmentDocuments = collect([
                [
                    'label' => 'Medical Certificate',
                    'path' => $profile->medical_certificate ?? null,
                    'document' => 'medical_certificate',
                ],
                [
                    'label' => 'Chest X-ray Report',
                    'path' => $profile->chest_xray_result ?? null,
                    'document' => 'chest_xray_result',
                ],
                [
                    'label' => 'Health Form',
                    'name' => 'Health Form.pdf',
                    'url' => $medicalAssessmentHealthFormSubmission
                        ? route('admin.health_form_submissions.pdf', $medicalAssessmentHealthFormSubmission)
                        : route('admin.health_pdf', ['id' => $profile->id]),
                    'uploaded' => true,
                    'preview' => (bool) $medicalAssessmentHealthFormSubmission,
                ],
                [
                    'label' => 'Health Declaration',
                    'path' => $profile->health_declaration ?? null,
                    'document' => 'health_declaration',
                ],
            ]);
            if (strtolower(trim((string) $profile->has_disability)) === 'yes') {
                $medicalAssessmentDocuments->push([
                    'label' => 'PWD ID Proof',
                    'path' => $profile->pwd_id_proof ?? null,
                    'document' => 'pwd_id_proof',
                ]);
            }
            $medicalAssessmentDocuments->push([
                'label' => '2x2 Photo',
                'path' => $profile->student_photo ?? null,
                'document' => 'student_photo',
            ]);
            $medicalAssessmentDocuments = $medicalAssessmentDocuments->map(function (array $document) use ($profile, $documentRouteName): array {
                if (isset($document['url'])) {
                    return $document;
                }
                $path = trim((string) ($document['path'] ?? ''));
                $uploaded = $path !== '';

                return [
                    'label' => $document['label'],
                    'name' => $uploaded ? basename($path) : 'Not uploaded',
                    'uploaded' => $uploaded,
                    'url' => $uploaded ? route($documentRouteName, [
                        'healthProfile' => $profile->id,
                        'document' => $document['document'],
                    ]) : null,
                ];
            })->filter(fn (array $document) => $document['label'] !== 'PWD ID Proof' || $document['uploaded'])->values();
        }
        $medicalInformationErrorMessages = collect($errors->getMessages())
            ->filter(fn ($messages, $key) => \Illuminate\Support\Str::startsWith($key, 'medical_information.'))
            ->flatten()
            ->values();
        $medicalInformationSections = $medicalInformationSections->map(function (array $section) use ($medicalHistorySectionNames, $medicalPersonalSectionNames) {
            $isMedicalHistory = in_array($section['name'], $medicalHistorySectionNames, true);
            $isPersonalInformation = in_array($section['name'], $medicalPersonalSectionNames, true);

            return array_merge($section, [
                'display_name' => $section['name'] === 'Medical History' ? 'General Medical History' : $section['name'],
                'view_id' => $isMedicalHistory ? 'medical-history' : ($isPersonalInformation ? 'personal-information' : $section['id']),
                'view_name' => $isMedicalHistory ? 'Medical History' : ($isPersonalInformation ? 'Personal Information' : $section['name']),
                'view_icon' => $isMedicalHistory ? 'heart-pulse' : ($isPersonalInformation ? 'identification' : $section['icon']),
            ]);
        });
        $medicalHistorySections = $medicalInformationSections
            ->filter(fn (array $section) => in_array($section['name'], $medicalHistorySectionNames, true))
            ->values();
        $medicalInformationViews = collect();
        $medicalPersonalInformationSections = $medicalInformationSections
            ->filter(fn (array $section) => in_array($section['name'], $medicalPersonalSectionNames, true))
            ->values();
        if ($medicalPersonalInformationSections->isNotEmpty()) {
            $medicalInformationViews->push([
                'id' => 'personal-information',
                'name' => 'Personal Information',
                'icon' => 'identification',
                'sections' => $medicalPersonalInformationSections,
            ]);
        }
        if ($medicalHistorySections->isNotEmpty()) {
            $medicalInformationViews->push([
                'id' => 'medical-history',
                'name' => 'Medical History',
                'icon' => 'heart-pulse',
                'sections' => $medicalHistorySections,
            ]);
        }
        $medicalInformationSections
            ->reject(fn (array $section) => in_array($section['name'], $medicalPersonalSectionNames, true) || in_array($section['name'], $medicalHistorySectionNames, true))
            ->each(fn (array $section) => $medicalInformationViews->push([
                'id' => $section['id'],
                'name' => $section['name'],
                'icon' => $section['icon'],
                'sections' => collect([$section]),
            ]));
        $medicalAssessmentOverviewDescription = "Summary of the " . strtolower($medicalAssessmentPersonType) . "'s medical information.";
    @endphp

    @once
        @push('styles')
            <style>
                #medicalInformationModal {
                    --medical-edit-background: #162536;
                    --medical-edit-text: #f8fafc;
                    --medical-address-label: #cbd5e1;
                    --medical-address-error: #fda4af;
                    --medical-menu-background: #101c2d;
                    --medical-menu-border: #40516a;
                    --medical-option-background: #1d2c3e;
                    /* The admin layout applies its input colors through these variables. */
                    --admin-input-text: var(--medical-edit-text);
                    --admin-input-bg: var(--medical-edit-background);
                    --admin-input-border: var(--medical-menu-border);
                    --admin-input-placeholder: var(--medical-edit-text);
                    position: fixed;
                    inset: 0;
                    z-index: 2147482500;
                    display: none;
                    align-items: center;
                    justify-content: center;
                    padding: 6px 12px;
                    overflow: hidden;
                    background: rgba(2, 8, 18, .82);
                    overscroll-behavior: contain;
                }
                #medicalInformationModal.is-open { display: flex; }
                #medicalInformationModal .medical-information-card {
                    display: flex;
                    flex-direction: column;
                    width: min(1080px, calc(100vw - 96px));
                    height: min(620px, calc(100dvh - 12px));
                    min-height: 440px;
                    overflow: hidden;
                    border: 1px solid #263b52;
                    border-radius: 12px;
                    background: #081321;
                    color: #f3f7fc;
                    box-shadow: 0 24px 70px rgba(0, 0, 0, .58);
                }
                #medicalInformationModal .medical-information-header {
                    display: flex;
                    min-height: 78px;
                    flex: 0 0 auto;
                    align-items: center;
                    justify-content: space-between;
                    gap: 14px;
                    padding: 12px 18px;
                    border-radius: 11px 11px 0 0;
                    background: linear-gradient(105deg, #741521 0%, #951622 58%, #a91825 100%);
                    color: #fff;
                }
                #medicalInformationModal .medical-information-heading { display: flex; min-width: 0; align-items: center; gap: 13px; }
                #medicalInformationModal .medical-information-heading-icon {
                    display: grid;
                    width: 46px;
                    height: 46px;
                    flex: 0 0 46px;
                    place-items: center;
                    border: 1px solid rgba(255,255,255,.16);
                    border-radius: 12px;
                    background: #c71c2b;
                    color: #fff;
                    box-shadow: inset 0 1px 0 rgba(255,255,255,.15);
                }
                #medicalInformationModal .medical-information-heading-icon svg { width: 24px; height: 24px; }
                #medicalInformationModal .medical-information-heading h2 { margin: 0; color: #fff; font-size: 20px; font-weight: 850; line-height: 1.2; }
                #medicalInformationModal .medical-information-heading p { margin: 3px 0 0; color: rgba(255,255,255,.92); font-size: 13px; line-height: 1.35; }
                #medicalInformationModal .medical-information-close {
                    display: grid;
                    width: 36px;
                    height: 36px;
                    flex: 0 0 36px;
                    place-items: center;
                    border: 1px solid rgba(255,255,255,.17);
                    border-radius: 50%;
                    background: rgba(83, 0, 17, .24);
                    color: #fff;
                    cursor: pointer;
                    transition: background .15s ease, color .15s ease, border-color .15s ease;
                }
                #medicalInformationModal .medical-information-close:hover,
                #medicalInformationModal .medical-information-close:focus-visible { border-color: #facc15; background: #facc15; color: #70131b; outline: none; }
                #medicalInformationModal .medical-information-close svg { width: 18px; height: 18px; }
                #medicalInformationModal .medical-information-body {
                    display: grid;
                    min-height: 0;
                    flex: 1 1 auto;
                    grid-template-columns: 234px minmax(0, 1fr);
                }
                #medicalInformationModal .medical-information-sidebar {
                    display: flex;
                    min-height: 0;
                    flex-direction: column;
                    gap: 10px;
                    overflow: hidden;
                    padding: 11px 10px 10px;
                    border-right: 1px solid #1a2c41;
                    background: #091625;
                }
                #medicalInformationModal .medical-information-nav { display: grid; min-height: 0; gap: 5px; overflow-y: auto; padding: 0 1px 0 0; }
                #medicalInformationModal .medical-information-nav button {
                    display: flex;
                    min-height: 39px;
                    align-items: center;
                    gap: 11px;
                    padding: 7px 10px;
                    border: 1px solid #172a3f;
                    border-radius: 7px;
                    background: #0d1b2b;
                    color: #f1f5f9;
                    text-align: left;
                    font: inherit;
                    font-size: 13px;
                    font-weight: 650;
                    cursor: pointer;
                    transition: border-color .15s ease, background .15s ease, color .15s ease;
                }
                #medicalInformationModal .medical-information-nav button svg { width: 19px; height: 19px; flex: 0 0 19px; stroke-width: 2.2; }
                #medicalInformationModal .medical-information-nav button:hover,
                #medicalInformationModal .medical-information-nav button:focus-visible { border-color: #52677e; background: #14243a; outline: none; }
                #medicalInformationModal .medical-information-nav button.is-active { border-color: #8c1220; border-left: 3px solid #ed1b2f; background: linear-gradient(100deg, #64111e, #391220); color: #fff; }
                #medicalInformationModal .medical-information-nav button.is-active svg { color: #fff; }
                #medicalInformationModal .medical-information-nav .medical-information-nav-label { min-width: 0; overflow-wrap: anywhere; line-height: 1.2; }
                #medicalInformationModal .medical-information-main {
                    display: flex;
                    min-width: 0;
                    min-height: 0;
                    flex-direction: column;
                    overflow: hidden;
                    padding: 12px 14px 10px;
                    background: #081321;
                }
                #medicalInformationModal .medical-information-content-head { display: flex; flex: 0 0 auto; align-items: flex-start; justify-content: space-between; gap: 12px; margin: 0 0 7px; }
                #medicalInformationModal .medical-information-content-copy { min-width: 0; }
                #medicalInformationModal .medical-information-content-head h3 { margin: 0; color: #f3f7fc; font-size: 18px; font-weight: 850; line-height: 1.2; }
                #medicalInformationModal .medical-information-content-head p { margin: 3px 0 0; color: #cbd5e1; font-size: 13px; line-height: 1.3; }
                #medicalInformationModal .medical-information-edit-toggle { display: inline-flex; min-height: 32px; flex: 0 0 auto; align-items: center; gap: 7px; padding: 6px 10px; border: 1px solid #71839a; border-radius: 6px; background: transparent; color: #f1f5f9; font: inherit; font-size: 12px; font-weight: 750; cursor: pointer; }
                #medicalInformationModal .medical-information-edit-toggle svg { width: 15px; height: 15px; }
                #medicalInformationModal .medical-information-edit-toggle:hover,
                #medicalInformationModal .medical-information-edit-toggle:focus-visible { border-color: #facc15; color: #facc15; outline: none; }
                #medicalInformationModal .medical-information-edit-control { display: none; width: 100%; min-width: 0; padding: 6px 8px; border: 1px solid #40516a; border-radius: 5px; background: var(--medical-edit-background); color: var(--medical-edit-text); color-scheme: dark; caret-color: currentColor; font: inherit; font-size: 13px; line-height: 1.35; }
                #medicalInformationModal .medical-information-edit-control option { background: var(--medical-edit-background); color: var(--medical-edit-text); }
                #medicalInformationModal textarea.medical-information-edit-control { min-height: 68px; resize: vertical; white-space: pre; }
                #medicalInformationModal select.medical-information-edit-control { min-height: 30px; }
                #medicalInformationModal .medical-information-address-edit { display: none; grid-template-columns: repeat(2, minmax(0, 1fr)); gap: 7px 9px; }
                #medicalInformationModal.is-editing .medical-information-address-edit { display: grid; }
                #medicalInformationModal.is-editing .medical-information-category-detail .medical-information-category-card[aria-label="Address"] .medical-information-rows { grid-template-columns: minmax(0, 1fr); }
                #medicalInformationModal.is-editing .medical-information-row-address,
                #medicalInformationModal.is-editing .medical-information-row-address-zip { grid-template-columns: minmax(160px, 23%) minmax(0, 1fr); }
                #medicalInformationModal .medical-information-address-field { display: grid; min-width: 0; gap: 3px; color: var(--medical-address-label); font-size: 12px; font-weight: 700; }
                #medicalInformationModal .medical-information-address-status { grid-column: 1 / -1; color: var(--medical-address-label); font-size: 11px; line-height: 1.3; }
                #medicalInformationModal .medical-information-edit-control:focus { border-color: #d1a72b; outline: 2px solid rgba(250,204,21,.13); }
                #medicalInformationModal.is-editing .medical-information-row:not(.medical-information-row-readonly) .medical-information-read-value { display: none; }
                #medicalInformationModal.is-editing .medical-information-edit-control { display: block; }
                #medicalInformationModal .medical-information-choice-group { display: none; min-width: 0; }
                #medicalInformationModal.is-editing .medical-information-choice-group { display: block; }
                #medicalInformationModal .medical-information-choice-options { display: flex; flex-wrap: wrap; gap: 8px 12px; }
                #medicalInformationModal .medical-information-choice-options label { display: inline-flex; min-width: 0; align-items: flex-start; gap: 6px; color: var(--medical-edit-text); font-size: 12px; line-height: 1.35; cursor: pointer; }
                #medicalInformationModal .medical-information-choice-options input { width: 15px; height: 15px; flex: 0 0 15px; margin: 0; accent-color: #8b0000; }
                #medicalInformationModal .medical-information-unit-edit { display: none; min-width: 0; align-items: center; gap: 7px; }
                #medicalInformationModal.is-editing .medical-information-unit-edit { display: flex; }
                #medicalInformationModal .medical-information-unit-edit .medical-information-edit-control { flex: 1 1 auto; }
                #medicalInformationModal .medical-information-unit-edit > span { flex: 0 0 auto; color: #cbd5e1; font-size: 12px; font-weight: 700; }
                #medicalInformationModal .medical-information-conditional-input { display: none; gap: 4px; margin-top: 6px; }
                #medicalInformationModal.is-editing .medical-information-conditional-input { display: grid; }
                #medicalInformationModal .medical-information-conditional-input[hidden] { display: none !important; }
                #medicalInformationModal .medical-information-conditional-input label { color: #cbd5e1; font-size: 12px; font-weight: 700; }
                #medicalInformationModal .medical-information-vaccine-history { display: none; gap: 6px; }
                #medicalInformationModal.is-editing .medical-information-vaccine-history { display: grid; }
                #medicalInformationModal .medical-information-dose-row { display: grid; grid-template-columns: minmax(0, .7fr) minmax(0, 1.3fr); align-items: center; gap: 6px; }
                #medicalInformationModal .medical-information-dose-row strong { color: #cbd5e1; font-size: 12px; }
                #medicalInformationModal .medical-information-dose-row input { min-width: 0; width: 100%; min-height: 32px; padding: 5px 7px; border: 1px solid #40516a; border-radius: 5px; background: var(--medical-edit-background); color: var(--medical-edit-text); color-scheme: dark; font: inherit; font-size: 12px; }
                #medicalInformationModal .medical-information-dose-row input:focus { border-color: #d1a72b; outline: 2px solid rgba(250,204,21,.13); }
                #medicalInformationModal.is-editing .medical-information-row-vaccine-history { grid-template-columns: minmax(75px, .45fr) minmax(0, 1.55fr); }
                #medicalInformationModal .medical-information-dose-row strong { align-self: center; }
                #medicalInformationModal .medical-information-dose-row input[data-medical-vaccine-part="brand"] { grid-column: 1 / -1; }
                #medicalInformationModal .medical-information-select-source { display: none !important; }
                #medicalInformationModal .medical-information-select { display: none; min-width: 0; }
                #medicalInformationModal.is-editing .medical-information-select { display: block; }
                #medicalInformationModal .medical-information-select-trigger,
                #medicalInformationModal .medical-information-select-option { position: relative; display: flex; width: 100%; min-width: 0; min-height: 34px; align-items: center; justify-content: space-between; gap: 8px; overflow: hidden; padding: 6px 9px; border: 1px solid var(--medical-menu-border); border-radius: 6px; background: var(--medical-edit-background); color: var(--medical-edit-text); font: inherit; font-size: 13px; line-height: 1.35; text-align: left; cursor: pointer; transition: background .15s ease, color .15s ease, border-color .15s ease; }
                #medicalInformationModal .medical-information-select-trigger > span,
                #medicalInformationModal .medical-information-select-option > span { position: relative; z-index: 1; min-width: 0; color: inherit; overflow-wrap: anywhere; }
                #medicalInformationModal .medical-information-select-trigger svg { position: relative; z-index: 1; width: 16px; height: 16px; flex: 0 0 16px; color: inherit; transition: transform .15s ease; }
                #medicalInformationModal .medical-information-select-trigger[aria-expanded="true"] svg { transform: rotate(180deg); }
                #medicalInformationModal .medical-information-select-trigger[aria-expanded="true"],
                #medicalInformationModal .medical-information-select-option[aria-selected="true"] { border-color: #8b0000; background: #8b0000; color: #fff; }
                #medicalInformationModal .medical-information-select-menu { position: fixed; z-index: 2; display: grid; gap: 5px; max-height: 220px; overflow-y: auto; overscroll-behavior: contain; padding: 6px; border: 1px solid var(--medical-menu-border); border-radius: 8px; background: var(--medical-menu-background); color: var(--medical-edit-text); box-shadow: 0 14px 34px rgba(0,0,0,.3); }
                #medicalInformationModal .medical-information-select-menu[hidden] { display: none; }
                #medicalInformationModal .medical-information-select-option { background: var(--medical-option-background); }
                #medicalInformationModal .medical-information-select-trigger:hover,
                #medicalInformationModal .medical-information-select-option:hover,
                #medicalInformationModal .medical-information-select-option:focus-visible:not([aria-selected="true"]) { border-color: #facc15; background: #facc15; color: #8b0000; }
                #medicalInformationModal .medical-information-select-trigger:focus-visible,
                #medicalInformationModal .medical-information-select-option:focus-visible { outline: 2px solid #d1a72b; outline-offset: 1px; }
                #medicalInformationModal .medical-information-select-trigger:disabled,
                #medicalInformationModal .medical-information-select-option:disabled { opacity: .55; cursor: default; }
                #medicalInformationModal .medical-information-select-trigger::before,
                #medicalInformationModal .medical-information-select-option::before { content: ""; position: absolute; inset: -45% auto -45% -68%; width: 58%; background: linear-gradient(115deg, transparent 0%, rgba(255,247,194,.12) 35%, rgba(255,247,194,.78) 50%, rgba(255,247,194,.14) 65%, transparent 100%); transform: skewX(-18deg); opacity: 0; pointer-events: none; }
                #medicalInformationModal .medical-information-select-trigger:hover::before,
                #medicalInformationModal .medical-information-select-option:hover::before { animation: medical-information-select-sweep .5s ease forwards; }
                @keyframes medical-information-select-sweep { from { left: -68%; opacity: 1; } to { left: 112%; opacity: 1; } }
                #medicalInformationModal .medical-information-field-error { display: block; margin-top: 4px; color: #fca5a5; font-size: 12px; font-weight: 700; }
                #medicalInformationModal .medical-information-errors { margin-bottom: 8px; padding: 8px 10px; border: 1px solid #a33a47; border-radius: 6px; background: #321922; color: #fecaca; font-size: 13px; }
                #medicalInformationModal .medical-information-errors ul { margin: 0; padding-left: 17px; }
                #medicalInformationModal .medical-information-update-form { display: flex; min-height: 0; flex: 1 1 auto; flex-direction: column; overflow: hidden; }
                #medicalInformationModal .medical-information-scroll { min-height: 0; flex: 1 1 auto; overflow-y: auto; overscroll-behavior: contain; padding: 0 0 2px; }
                #medicalInformationModal .medical-information-category-grid { display: grid; grid-template-columns: minmax(0, 1fr); align-content: start; gap: 9px; }
                #medicalInformationModal .medical-information-category-card {
                    min-width: 0;
                    overflow: hidden;
                    border: 1px solid #1c3349;
                    border-radius: 8px;
                    background: linear-gradient(145deg, #0e1d2d, #0b1928);
                }
                #medicalInformationModal .medical-information-category-detail { display: grid; min-width: 0; gap: 9px; }
                #medicalInformationModal .medical-information-documents-layout { display: grid; min-width: 0; grid-template-columns: minmax(0, .95fr) minmax(0, 1.05fr); align-items: start; gap: 10px; }
                #medicalInformationModal .medical-information-documents-layout.is-employee { grid-template-columns: minmax(0, 1fr); }
                #medicalInformationModal .medical-document-file-list,
                #medicalInformationModal .medical-document-information { display: grid; min-width: 0; align-content: start; gap: 9px; }
                #medicalInformationModal .medical-document-file,
                #medicalInformationModal .medical-document-info-card { min-width: 0; overflow: hidden; border: 1px solid #1c3349; border-radius: 8px; background: linear-gradient(145deg, #0e1d2d, #0b1928); }
                #medicalInformationModal .medical-document-file-head,
                #medicalInformationModal .medical-document-info-head { display: flex; min-height: 38px; align-items: center; gap: 8px; padding: 7px 10px; border-bottom: 1px solid #1c3349; background: #14283b; color: #f4f7fb; font-size: 14px; font-weight: 800; }
                #medicalInformationModal .medical-document-file-head svg { width: 18px; height: 18px; flex: 0 0 18px; }
                #medicalInformationModal .medical-document-preview { display: grid; min-height: 130px; place-items: center; overflow: hidden; margin: 9px; border: 1px solid #263c53; border-radius: 6px; background: #081321; color: #9aadc2; }
                #medicalInformationModal .medical-document-preview img { display: block; width: 100%; max-height: 190px; object-fit: contain; }
                #medicalInformationModal .medical-document-preview iframe { display: block; width: 100%; height: 190px; border: 0; background: #fff; }
                #medicalInformationModal .medical-document-preview a { padding: 12px; color: #facc15; font-size: 13px; font-weight: 750; text-align: center; }
                #medicalInformationModal .medical-document-file-name { display: flex; min-width: 0; align-items: center; justify-content: space-between; gap: 8px; padding: 0 10px 10px; color: #cbd5e1; font-size: 12px; }
                #medicalInformationModal .medical-document-file-name span { min-width: 0; overflow-wrap: anywhere; }
                #medicalInformationModal .medical-document-file-name a { flex: 0 0 auto; color: #facc15; font-weight: 750; }
                #medicalInformationModal .medical-document-info-card .medical-information-rows { grid-template-columns: minmax(0, 1fr); }
                #medicalInformationModal .medical-document-info-card .medical-information-row { display: grid; grid-template-columns: minmax(100px, .8fr) minmax(0, 1.2fr); }
                #medicalInformationModal .medical-document-info-empty { padding: 12px; color: #cbd5e1; font-size: 13px; line-height: 1.45; }
                #medicalInformationModal .medical-information-section-head { display: flex; min-height: 40px; align-items: center; gap: 9px; padding: 7px 11px; border-bottom: 1px solid #1c3349; background: #14283b; }
                #medicalInformationModal .medical-information-section-head h4 { min-width: 0; flex: 1 1 auto; margin: 0; color: #f4f7fb; font-size: 15px; font-weight: 800; line-height: 1.25; }
                #medicalInformationModal .medical-information-category-card[hidden],
                #medicalInformationModal [data-medical-panel][hidden],
                #medicalInformationModal .medical-information-row[hidden] { display: none !important; }
                #medicalInformationModal .medical-information-category-head { display: flex; min-height: 40px; align-items: center; gap: 9px; padding: 6px 11px; background: #14283b; }
                #medicalInformationModal .medical-information-category-icon { display: grid; width: 26px; height: 26px; flex: 0 0 26px; place-items: center; color: #f5f8fc; }
                #medicalInformationModal .medical-information-category-icon svg { width: 22px; height: 22px; stroke-width: 2.4; }
                #medicalInformationModal .medical-information-category-head h4 { min-width: 0; flex: 1 1 auto; margin: 0; color: #f4f7fb; font-size: 15px; font-weight: 800; line-height: 1.25; }
                #medicalInformationModal .medical-information-view-details {
                    display: inline-flex;
                    min-height: 25px;
                    flex: 0 0 auto;
                    align-items: center;
                    gap: 4px;
                    padding: 4px 8px;
                    border: 1px solid #1c3046;
                    border-radius: 7px;
                    background: #14243a;
                    color: #e5edf6;
                    font: inherit;
                    font-size: 12px;
                    font-weight: 650;
                    cursor: pointer;
                }
                #medicalInformationModal .medical-information-view-details:hover,
                #medicalInformationModal .medical-information-view-details:focus-visible { border-color: #e8b923; color: #facc15; outline: none; }
                #medicalInformationModal .medical-information-view-details svg { width: 13px; height: 13px; }
                #medicalInformationModal .medical-information-rows { display: grid; min-width: 0; grid-template-columns: repeat(2, minmax(0, 1fr)); column-gap: 22px; margin: 0; padding: 3px 11px 7px; }
                #medicalInformationModal .medical-information-row-column { display: grid; min-width: 0; align-content: start; margin: 0; }
                #medicalInformationModal .medical-information-row { display: grid; min-width: 0; grid-template-columns: repeat(2, minmax(0, 1fr)); align-items: start; column-gap: 16px; padding: 6px 0; border-bottom: 1px solid #1b2e42; }
                #medicalInformationModal .medical-information-row:last-child { border-bottom: 0; }
                #medicalInformationModal .medical-information-row dt { min-width: 0; margin: 0; padding: 0; color: #cbd5e1; font-size: 12px; font-weight: 700; line-height: 1.35; overflow-wrap: anywhere; }
                #medicalInformationModal .medical-information-row dd { min-width: 0; margin: 0; padding: 0; color: #f3f7fc; font-size: 13px; line-height: 1.35; text-align: left; overflow-wrap: anywhere; white-space: normal; }
                #medicalInformationModal .medical-information-row dd > span { display: block; font-weight: 400; white-space: pre-wrap; }
                #medicalInformationModal .medical-information-history-panel { padding: 11px 12px; border: 1px solid #1c3349; border-radius: 8px; background: #0d1b2b; }
                #medicalInformationModal .medical-information-history-list { display: grid; gap: 8px; }
                #medicalInformationModal .medical-information-history-entry { padding: 10px 11px; border: 1px solid #1d344a; border-radius: 7px; background: #102033; }
                #medicalInformationModal .medical-information-entry-head { display: flex; flex-wrap: wrap; align-items: baseline; justify-content: space-between; gap: 5px 12px; }
                #medicalInformationModal .medical-information-entry-head strong { color: #f7fafc; font-size: 14px; }
                #medicalInformationModal .medical-information-entry-head time,
                #medicalInformationModal .medical-information-history-entry small { color: #cbd5e1; font-size: 12px; }
                #medicalInformationModal .medical-information-change-table { width: 100%; margin-top: 9px; border-collapse: collapse; table-layout: fixed; color: #f3f7fc; font-size: 13px; }
                #medicalInformationModal .medical-information-change-table th,
                #medicalInformationModal .medical-information-change-table td { padding: 7px 8px; border-top: 1px solid #263a50; text-align: left; vertical-align: top; overflow-wrap: anywhere; }
                #medicalInformationModal .medical-information-change-table th { color: #cbd5e1; font-size: 11px; font-weight: 800; text-transform: uppercase; }
                #medicalInformationModal .medical-information-change-table th:first-child,
                #medicalInformationModal .medical-information-change-table td:first-child { width: 38%; }
                #medicalInformationModal .medical-information-change-table td:last-child { white-space: pre-wrap; }
                #medicalInformationModal .medical-information-change-events { display: grid; gap: 3px; margin-top: 8px; }
                #medicalInformationModal .medical-information-history-entry p { display: grid; gap: 2px; margin: 8px 0 0; color: #e2eaf3; font-size: 13px; line-height: 1.45; white-space: pre-wrap; overflow-wrap: anywhere; }
                #medicalInformationModal .medical-information-history-entry p span { color: #cbd5e1; font-size: 12px; font-weight: 850; text-transform: uppercase; }
                #medicalInformationModal .medical-information-history-entry small { display: block; margin-top: 8px; }
                #medicalInformationModal .medical-information-history-day { display: grid; gap: 7px; }
                #medicalInformationModal .medical-information-history-day-head { display: flex; align-items: baseline; gap: 8px; padding: 1px 2px; color: #f7fafc; font-size: 12px; }
                #medicalInformationModal .medical-information-history-updates { display: grid; gap: 7px; }
                #medicalInformationModal .medical-information-history-update { display: grid; min-width: 0; grid-template-columns: 32px minmax(0, 1fr) minmax(125px, .38fr); align-items: center; gap: 9px; padding: 8px; border: 1px solid #1d344a; border-radius: 7px; background: #102033; }
                #medicalInformationModal .medical-information-history-initial { display: grid; width: 32px; height: 32px; place-items: center; border: 1px solid #334b64; border-radius: 6px; background: #1a2d40; color: #fff; font-size: 16px; font-weight: 800; }
                #medicalInformationModal .medical-information-history-update-main { display: grid; min-width: 0; gap: 6px; }
                #medicalInformationModal .medical-information-history-update-title { color: #f7fafc; font-size: 13px; font-weight: 800; }
                #medicalInformationModal .medical-information-history-values { display: flex; min-width: 0; flex-wrap: wrap; align-items: center; gap: 5px; }
                #medicalInformationModal .medical-information-history-value-group { display: inline-grid; min-width: 0; grid-template-columns: auto minmax(0, auto); align-items: center; gap: 5px; }
                #medicalInformationModal .medical-information-history-value-label { color: #cbd5e1; font-size: 12px; font-weight: 600; white-space: nowrap; }
                #medicalInformationModal .medical-information-history-value { display: block; min-width: 0; max-width: 230px; padding: 4px 7px; border: 1px solid #263d53; border-radius: 5px; background: #172b3e; color: #f3f7fc; font-size: 12px; line-height: 1.3; overflow-wrap: anywhere; white-space: pre-wrap; }
                #medicalInformationModal .medical-information-history-value.is-updated { border-color: #542735; background: #3a1d2a; color: #fff; }
                #medicalInformationModal .medical-information-history-arrow { flex: 0 0 auto; color: #e2eaf3; font-size: 16px; }
                #medicalInformationModal .medical-information-history-meta { display: grid; min-width: 0; justify-items: start; gap: 5px; color: #e2eaf3; font-size: 11px; line-height: 1.35; }
                #medicalInformationModal .medical-information-history-meta time,
                #medicalInformationModal .medical-information-history-meta span { min-width: 0; overflow-wrap: anywhere; }
                #medicalInformationModal .medical-information-history-reason { grid-column: 2 / -1; margin: 0; padding-top: 5px; border-top: 1px solid #263a50; color: #cbd5e1; font-size: 11px; line-height: 1.4; white-space: pre-wrap; overflow-wrap: anywhere; }
                #medicalInformationModal .medical-information-empty { padding: 15px; border: 1px solid #1d344a; border-radius: 7px; background: #102033; color: #b8c7d8; font-size: 13px; }
                #medicalInformationModal .medical-information-footer {
                    display: flex;
                    min-height: 46px;
                    flex: 0 0 auto;
                    align-items: center;
                    justify-content: flex-end;
                    gap: 8px;
                    padding: 7px 14px;
                    border-top: 1px solid #1b2e42;
                    background: #091625;
                }
                #medicalInformationModal .medical-information-footer button {
                    display: inline-flex;
                    min-height: 38px;
                    align-items: center;
                    justify-content: center;
                    gap: 8px;
                    padding: 8px 14px;
                    border: 1px solid #71839a;
                    border-radius: 6px;
                    background: transparent;
                    color: #f1f5f9;
                    font: inherit;
                    font-size: 12px;
                    font-weight: 750;
                    cursor: pointer;
                    transition: background .15s ease, border-color .15s ease;
                }
                #medicalInformationModal .medical-information-footer button svg { width: 17px; height: 17px; }
                #medicalInformationModal .medical-information-footer button:hover,
                #medicalInformationModal .medical-information-footer button:focus-visible { border-color: #facc15; color: #facc15; outline: none; }
                #medicalInformationModal .medical-information-footer button.medical-information-update { border-color: #b81626; background: #c7192a; color: #fff; }
                #medicalInformationModal .medical-information-footer button.medical-information-update:hover,
                #medicalInformationModal .medical-information-footer button.medical-information-update:focus-visible { border-color: #facc15; background: #9e1421; color: #fff; }
                #medicalInformationModal .medical-information-card [hidden] { display: none !important; }
                html[data-theme="light"] #medicalInformationModal { --medical-edit-background: #fff; --medical-edit-text: #000; --medical-menu-background: #fff; --medical-menu-border: #cbd5e1; --medical-option-background: #f8fafc; --medical-address-label: #334155; --medical-address-error: #9f1239; background: rgba(15, 23, 42, .58); }
                html[data-theme="light"] #medicalInformationModal .medical-information-card {
                    border-color: #d5dee8;
                    background: #fff;
                    color: #000;
                    box-shadow: 0 24px 60px rgba(15, 23, 42, .28);
                }
                html[data-theme="light"] #medicalInformationModal .medical-information-sidebar,
                html[data-theme="light"] #medicalInformationModal .medical-information-footer { border-color: #e0e6ed; background: #f7f9fc; }
                html[data-theme="light"] #medicalInformationModal .medical-information-nav button { border-color: #dce3eb; background: #fff; }
                html[data-theme="light"] #medicalInformationModal .medical-information-nav button { color: #000; }
                html[data-theme="light"] #medicalInformationModal .medical-information-nav button:hover,
                html[data-theme="light"] #medicalInformationModal .medical-information-nav button:focus-visible { border-color: #b9c7d6; background: #edf2f7; }
                html[data-theme="light"] #medicalInformationModal .medical-information-nav button.is-active { border-color: #8c1220; border-left-color: #ed1b2f; background: linear-gradient(100deg, #64111e, #391220); color: #fff; }
                html[data-theme="light"] #medicalInformationModal .medical-information-main { background: #fff; }
                html[data-theme="light"] #medicalInformationModal .medical-information-content-head h3,
                html[data-theme="light"] #medicalInformationModal .medical-information-category-head h4,
                html[data-theme="light"] #medicalInformationModal .medical-information-section-head h4 { color: #000; }
                html[data-theme="light"] #medicalInformationModal .medical-information-content-head p { color: #000; }
                html[data-theme="light"] #medicalInformationModal .medical-information-edit-toggle { border-color: #b8c4d1; color: #000; }
                html[data-theme="light"] #medicalInformationModal .medical-information-edit-control { border-color: #cbd5e1; background: #fff; color: #000; color-scheme: light; }
                html[data-theme="light"] #medicalInformationModal .medical-information-unit-edit > span,
                html[data-theme="light"] #medicalInformationModal .medical-information-conditional-input label,
                html[data-theme="light"] #medicalInformationModal .medical-information-dose-row strong { color: #334155; }
                html[data-theme="light"] #medicalInformationModal .medical-information-dose-row input { border-color: #cbd5e1; background: #fff; color: #000; color-scheme: light; }
                html[data-theme="light"] #medicalInformationModal .medical-information-errors { border-color: #f1a6ad; background: #fff1f2; color: #9f1239; }
                html[data-theme="light"] #medicalInformationModal .medical-information-category-card,
                html[data-theme="light"] #medicalInformationModal .medical-information-history-panel { border-color: #dbe3ec; background: #fff; }
                html[data-theme="light"] #medicalInformationModal .medical-document-file,
                html[data-theme="light"] #medicalInformationModal .medical-document-info-card { border-color: #dbe3ec; background: #fff; }
                html[data-theme="light"] #medicalInformationModal .medical-document-file-head,
                html[data-theme="light"] #medicalInformationModal .medical-document-info-head { border-color: #e5eaf0; background: #f1f5f9; color: #000; }
                html[data-theme="light"] #medicalInformationModal .medical-document-preview { border-color: #dbe3ec; background: #f8fafc; color: #000; }
                html[data-theme="light"] #medicalInformationModal .medical-document-file-name,
                html[data-theme="light"] #medicalInformationModal .medical-document-info-empty { color: #000; }
                html[data-theme="light"] #medicalInformationModal .medical-document-file-name a,
                html[data-theme="light"] #medicalInformationModal .medical-document-preview a { color: #000; }
                html[data-theme="light"] #medicalInformationModal .medical-information-category-detail { border-color: #dbe3ec; }
                html[data-theme="light"] #medicalInformationModal .medical-information-category-head,
                html[data-theme="light"] #medicalInformationModal .medical-information-section-head { border-color: #e5eaf0; background: #f1f5f9; }
                html[data-theme="light"] #medicalInformationModal .medical-information-category-icon { color: #000; }
                html[data-theme="light"] #medicalInformationModal .medical-information-view-details { border-color: #d7e0e9; background: #eef2f6; color: #000; }
                html[data-theme="light"] #medicalInformationModal .medical-information-view-details:hover,
                html[data-theme="light"] #medicalInformationModal .medical-information-view-details:focus-visible { border-color: #d6ad27; color: #7f1d1d; }
                html[data-theme="light"] #medicalInformationModal .medical-information-row { border-color: #e5eaf0; }
                html[data-theme="light"] #medicalInformationModal .medical-information-row dt,
                html[data-theme="light"] #medicalInformationModal .medical-information-row dd { color: #000; }
                html[data-theme="light"] #medicalInformationModal .medical-information-history-entry,
                html[data-theme="light"] #medicalInformationModal .medical-information-empty { border-color: #dfe6ee; background: #f8fafc; }
                html[data-theme="light"] #medicalInformationModal .medical-information-history-day-head,
                html[data-theme="light"] #medicalInformationModal .medical-information-history-update-title { color: #000; }
                html[data-theme="light"] #medicalInformationModal .medical-information-history-value-label,
                html[data-theme="light"] #medicalInformationModal .medical-information-history-reason { color: #334155; }
                html[data-theme="light"] #medicalInformationModal .medical-information-history-update { border-color: #dfe6ee; background: #f8fafc; }
                html[data-theme="light"] #medicalInformationModal .medical-information-history-initial { border-color: #d5dee8; background: #e8eef5; color: #000; }
                html[data-theme="light"] #medicalInformationModal .medical-information-history-value { border-color: #dbe3ec; background: #eef2f6; color: #000; }
                html[data-theme="light"] #medicalInformationModal .medical-information-history-value.is-updated { border-color: #e7bdc3; background: #f8e9eb; color: #54131d; }
                html[data-theme="light"] #medicalInformationModal .medical-information-history-arrow,
                html[data-theme="light"] #medicalInformationModal .medical-information-history-meta { color: #000; }
                html[data-theme="light"] #medicalInformationModal .medical-information-history-reason { border-color: #dbe3ec; }
                html[data-theme="light"] #medicalInformationModal .medical-information-entry-head strong { color: #000; }
                html[data-theme="light"] #medicalInformationModal .medical-information-entry-head time,
                html[data-theme="light"] #medicalInformationModal .medical-information-history-entry small,
                html[data-theme="light"] #medicalInformationModal .medical-information-history-entry p span { color: #000; }
                html[data-theme="light"] #medicalInformationModal .medical-information-change-table { color: #000; }
                html[data-theme="light"] #medicalInformationModal .medical-information-change-table th,
                html[data-theme="light"] #medicalInformationModal .medical-information-change-table td { border-color: #dbe3ec; }
                html[data-theme="light"] #medicalInformationModal .medical-information-change-table th { color: #334155; }
                html[data-theme="light"] #medicalInformationModal .medical-information-history-entry p { color: #000; }
                html[data-theme="light"] #medicalInformationModal .medical-information-empty { color: #000; }
                html[data-theme="light"] #medicalInformationModal .medical-information-footer button:not(.medical-information-update) { border-color: #cbd5e1; color: #000; }
                html[data-theme="light"] #medicalInformationModal .medical-information-footer button:not(.medical-information-update):hover,
                html[data-theme="light"] #medicalInformationModal .medical-information-footer button:not(.medical-information-update):focus-visible { border-color: #8c1220; color: #8c1220; }
                @media (min-width: 721px) and (max-width: 860px) {
                    #medicalInformationModal .medical-information-category-grid { grid-template-columns: minmax(0, 1fr); }
                }
                @media (max-width: 720px) {
                    #medicalInformationModal { padding: 6px; }
                    #medicalInformationModal .medical-information-card { width: calc(100vw - 12px); height: calc(100dvh - 12px); min-height: 0; }
                    #medicalInformationModal .medical-information-header { min-height: 68px; padding: 10px 10px; }
                    #medicalInformationModal .medical-information-heading { gap: 9px; }
                    #medicalInformationModal .medical-information-heading-icon { width: 40px; height: 40px; flex-basis: 40px; }
                    #medicalInformationModal .medical-information-heading h2 { font-size: 18px; }
                    #medicalInformationModal .medical-information-heading p { max-width: 55vw; font-size: 12px; }
                    #medicalInformationModal .medical-information-close { width: 32px; height: 32px; flex-basis: 32px; }
                    #medicalInformationModal .medical-information-body { grid-template-columns: minmax(0, 1fr); grid-template-rows: auto minmax(0, 1fr); }
                    #medicalInformationModal .medical-information-sidebar { gap: 7px; padding: 7px 8px 6px; border-right: 0; border-bottom: 1px solid #1a2c41; }
                    #medicalInformationModal .medical-information-nav { display: flex; gap: 5px; overflow-x: auto; overflow-y: hidden; padding-bottom: 2px; }
                    #medicalInformationModal .medical-information-nav button { min-height: 34px; flex: 0 0 auto; padding: 5px 8px; font-size: 12px; }
                    #medicalInformationModal .medical-information-nav .medical-information-nav-label { white-space: nowrap; }
                    #medicalInformationModal .medical-information-nav button svg { width: 15px; height: 15px; flex-basis: 15px; }
                    #medicalInformationModal .medical-information-main { padding: 9px 9px 7px; }
                    #medicalInformationModal .medical-information-category-grid { grid-template-columns: minmax(0, 1fr); gap: 7px; }
                    #medicalInformationModal .medical-information-rows { grid-template-columns: minmax(0, 1fr); }
                    #medicalInformationModal .medical-information-address-edit { grid-template-columns: minmax(0, 1fr); }
                    #medicalInformationModal.is-editing .medical-information-row-address,
                    #medicalInformationModal.is-editing .medical-information-row-address-zip { grid-template-columns: minmax(0, 1fr); }
                    #medicalInformationModal.is-editing .medical-information-row-vaccine-history { grid-template-columns: minmax(0, 1fr); }
                    #medicalInformationModal .medical-information-documents-layout { grid-template-columns: minmax(0, 1fr); }
                    #medicalInformationModal .medical-information-history-update { grid-template-columns: 30px minmax(0, 1fr); }
                    #medicalInformationModal .medical-information-history-meta { grid-column: 2; grid-template-columns: repeat(2, minmax(0, 1fr)); }
                    #medicalInformationModal .medical-information-history-reason { grid-column: 2; }
                    #medicalInformationModal .medical-information-category-head h4 { font-size: 14px; }
                    #medicalInformationModal .medical-information-footer { justify-content: space-between; padding: 6px 8px; }
                    #medicalInformationModal .medical-information-footer button { min-height: 35px; padding: 7px 9px; font-size: 11px; }
                }
                @media (max-width: 420px) {
                    #medicalInformationModal .medical-information-footer { display: grid; grid-template-columns: minmax(0, 1fr); }
                    #medicalInformationModal .medical-information-footer button { width: 100%; }
                }
                @media (prefers-reduced-motion: reduce) {
                    #medicalInformationModal *, #medicalInformationModal *::before, #medicalInformationModal *::after { scroll-behavior: auto !important; transition-duration: .01ms !important; }
                    #medicalInformationModal .medical-information-select-trigger::before,
                    #medicalInformationModal .medical-information-select-option::before { animation: none !important; }
                }
            </style>
        @endpush
    @endonce

    <div class="medical-information-overlay" id="medicalInformationModal" aria-hidden="true" data-auto-open="{{ $medicalInformationErrorMessages->isNotEmpty() ? 'true' : 'false' }}">
        <template data-medical-select-chevron><x-outline-icon name="chevron-down" /></template>
        <div class="correction-card medical-information-card" role="dialog" aria-modal="true" aria-labelledby="medicalInformationTitle">
            <header class="medical-information-header">
                <div class="medical-information-heading">
                    <span class="medical-information-heading-icon"><x-outline-icon name="medical-clearance-issued" /></span>
                    <div>
                        <h2 id="medicalInformationTitle">Health Information</h2>
                        <p>Review and update the {{ strtolower($medicalAssessmentPersonType) }}'s health record.</p>
                    </div>
                </div>
                <button type="button" class="medical-information-close" data-medical-assessment-close aria-label="Close medical information">
                    <x-outline-icon name="x-mark" />
                </button>
            </header>

            <form method="POST" action="{{ $medicalAssessmentUpdateUrl }}" id="medicalInformationUpdateForm" class="medical-information-update-form">
                @csrf
                @method('PUT')
                <input type="hidden" name="_medical_information_view" value="{{ old('_medical_information_view', 'overview') }}" data-medical-edit-view>
            <div class="medical-information-body">
                <aside class="medical-information-sidebar" aria-label="Medical information sections">
                    <nav class="medical-information-nav" role="tablist" aria-label="Medical record navigation">
                        <button type="button" class="is-active" role="tab" aria-selected="true" data-medical-view="overview" data-medical-label="Summary">
                            <x-outline-icon name="squares-2x2" /><span class="medical-information-nav-label">Overview</span>
                        </button>
                        @foreach($medicalInformationViews as $medicalView)
                            <button type="button" role="tab" aria-selected="false" data-medical-view="{{ $medicalView['id'] }}" data-medical-label="{{ $medicalView['name'] }}">
                                <x-outline-icon name="{{ $medicalView['icon'] }}" /><span class="medical-information-nav-label">{{ $medicalView['name'] }}</span>
                            </button>
                        @endforeach
                        <button type="button" role="tab" aria-selected="false" data-medical-view="assessment-history" data-medical-label="History">
                            <x-outline-icon name="clipboard-document-list" /><span class="medical-information-nav-label">History</span>
                        </button>
                    </nav>
                </aside>

                <main class="medical-information-main">
                    <div class="medical-information-content-head">
                        <div class="medical-information-content-copy">
                            <h3 data-medical-view-title>Summary</h3>
                            <p data-medical-view-description>{{ $medicalAssessmentOverviewDescription }}</p>
                        </div>
                        <button type="button" class="medical-information-edit-toggle" data-medical-edit-toggle hidden>
                            <x-outline-icon name="pencil-square" /><span>Edit Information</span>
                        </button>
                    </div>

                    <div class="medical-information-scroll" data-medical-content-scroll>
                        @if($medicalInformationErrorMessages->isNotEmpty())
                            <div class="medical-information-errors" role="alert">
                                <ul>@foreach($medicalInformationErrorMessages as $errorMessage)<li>{{ $errorMessage }}</li>@endforeach</ul>
                            </div>
                        @endif
                        <div class="medical-information-category-grid" data-medical-category-grid>
                            @foreach($medicalInformationSections as $medicalSection)
                                <section class="medical-information-category-card" aria-label="{{ $medicalSection['display_name'] }}">
                                    <div class="medical-information-category-head">
                                        <span class="medical-information-category-icon"><x-outline-icon name="{{ $medicalSection['icon'] }}" /></span>
                                        <h4>{{ $medicalSection['display_name'] }}</h4>
                                        <button type="button" class="medical-information-view-details" data-medical-view="{{ $medicalSection['view_id'] }}" data-medical-label="{{ $medicalSection['view_name'] }}">
                                            <span>View Details</span><x-outline-icon name="chevron-right" />
                                        </button>
                                    </div>
                                    <div class="medical-information-rows">
                                        @foreach(collect($medicalSection['overview_items'])->chunk(max(1, (int) ceil(count($medicalSection['overview_items']) / 2))) as $medicalColumn)
                                            <dl class="medical-information-row-column">
                                                @foreach($medicalColumn as $medicalLabel => $medicalValue)
                                                    <div class="medical-information-row"><dt>{{ $medicalLabel }}</dt><dd><span>{{ $medicalValue }}</span></dd></div>
                                                @endforeach
                                            </dl>
                                        @endforeach
                                    </div>
                                </section>
                            @endforeach
                        </div>

                        @foreach($medicalInformationViews as $medicalView)
                            <section class="medical-information-category-detail" data-medical-category-detail data-medical-panel="{{ $medicalView['id'] }}" aria-label="{{ $medicalView['name'] }}" hidden>
                                @foreach($medicalView['sections'] as $medicalSection)
                                    @if($medicalSection['name'] === 'Documents')
                                        @php
                                            $medicalDocumentItems = collect($medicalSection['items']);
                                            $medicalDocumentInfoGroups = [
                                                'Medical Certificate Information' => [
                                                    'Certificate doctor',
                                                    'Certificate date',
                                                    'Certificate findings',
                                                    'Certificate findings details',
                                                ],
                                                'Chest X-ray Information' => [
                                                    'Chest X-ray result',
                                                    'Chest X-ray date',
                                                    'Chest X-ray findings',
                                                    'Chest X-ray findings details',
                                                ],
                                            ];
                                        @endphp
                                        <div class="medical-information-documents-layout {{ $medicalAssessmentIsEmployee ? 'is-employee' : '' }}" aria-label="Uploaded medical documents and information">
                                            <div class="medical-document-file-list" aria-label="Uploaded files">
                                                @foreach($medicalAssessmentDocuments as $medicalDocument)
                                                    @php
                                                        $medicalDocumentExtension = strtolower(pathinfo((string) ($medicalDocument['name'] ?? ''), PATHINFO_EXTENSION));
                                                        $medicalDocumentIsImage = in_array($medicalDocumentExtension, ['jpg', 'jpeg', 'png', 'gif', 'webp', 'bmp'], true);
                                                    @endphp
                                                    <article class="medical-document-file">
                                                        <div class="medical-document-file-head">
                                                            <x-outline-icon name="document-text" /><span>{{ $medicalDocument['label'] }}</span>
                                                        </div>
                                                        @if($medicalDocument['uploaded'] && filled($medicalDocument['url']) && ($medicalDocument['preview'] ?? true))
                                                            <div class="medical-document-preview">
                                                                @if($medicalDocumentIsImage)
                                                                    <a href="{{ $medicalDocument['url'] }}" target="_blank" rel="noopener" aria-label="Open {{ $medicalDocument['label'] }}">
                                                                        <img src="{{ $medicalDocument['url'] }}" alt="{{ $medicalDocument['label'] }} preview" loading="lazy">
                                                                    </a>
                                                                @elseif($medicalDocumentExtension === 'pdf')
                                                                    <iframe src="{{ $medicalDocument['url'] }}" title="{{ $medicalDocument['label'] }} preview" loading="lazy"></iframe>
                                                                @else
                                                                    <a href="{{ $medicalDocument['url'] }}" target="_blank" rel="noopener">Open uploaded document</a>
                                                                @endif
                                                            </div>
                                                        @elseif(!$medicalDocument['uploaded'] || blank($medicalDocument['url']))
                                                            <div class="medical-document-preview">No file uploaded</div>
                                                        @endif
                                                        @if($medicalDocument['uploaded'] && filled($medicalDocument['url']))
                                                            <div class="medical-document-file-name">
                                                                <a href="{{ $medicalDocument['url'] }}" target="_blank" rel="noopener" aria-label="Open {{ $medicalDocument['label'] }}">Open</a>
                                                            </div>
                                                        @endif
                                                    </article>
                                                @endforeach
                                            </div>
                                            @unless($medicalAssessmentIsEmployee)
                                                <div class="medical-document-information" aria-label="Medical document information">
                                                    @foreach($medicalDocumentInfoGroups as $medicalInfoTitle => $medicalInfoLabels)
                                                        <article class="medical-document-info-card">
                                                            <div class="medical-document-info-head">{{ $medicalInfoTitle }}</div>
                                                            <div class="medical-information-rows">
                                                                @foreach($medicalInfoLabels as $medicalLabel)
                                                                    @php
                                                                        $medicalValue = $medicalDocumentItems->get($medicalLabel, $medicalMissingValueLabel);
                                                                        $medicalEditField = $medicalInformationEditMap['Documents'][$medicalLabel] ?? null;
                                                                        $medicalEditKey = $medicalEditField['key'] ?? null;
                                                                        $medicalEditRawValue = $medicalEditKey ? data_get($medicalAssessmentEditableValues ?? [], $medicalEditKey) : null;
                                                                        $medicalEditValue = $medicalEditField ? $formatMedicalEditValue($medicalEditRawValue, $medicalEditField['type']) : '';
                                                                        $medicalOldValue = $medicalEditKey ? old('medical_information.' . $medicalEditKey, $medicalEditValue) : '';
                                                                        if (is_string($medicalOldValue) && strcasecmp(trim($medicalOldValue), 'Not recorded') === 0) {
                                                                            $medicalOldValue = '';
                                                                        }
                                                                        $medicalDependsOn = $medicalEditField['depends_on'] ?? null;
                                                                        $medicalDependsOnValue = $medicalDependsOn
                                                                            ? $formatMedicalEditValue(old(
                                                                                'medical_information.' . $medicalDependsOn['key'],
                                                                                data_get($medicalAssessmentEditableValues ?? [], $medicalDependsOn['key'])
                                                                            ), $medicalDependsOn['type'])
                                                                            : null;
                                                                    @endphp
                                                                    <div class="medical-information-row @if(!$medicalEditField) medical-information-row-readonly @endif" @if($medicalDependsOn) data-medical-depends-on-key="{{ $medicalDependsOn['key'] }}" data-medical-depends-on-value="{{ $medicalDependsOn['value'] }}" @if($medicalDependsOnValue !== $medicalDependsOn['value']) hidden @endif @endif>
                                                                        <dt>{{ $medicalLabel }}</dt>
                                                                        <dd>
                                                                            <span class="medical-information-read-value">{{ $medicalValue }}</span>
                                                                            @if($medicalEditField)
                                                                                @if(in_array($medicalEditField['type'], ['boolean', 'yes-no'], true))
                                                                                    <select class="medical-information-edit-control" data-medical-edit-control name="medical_information[{{ $medicalEditKey }}]" aria-label="Edit {{ $medicalLabel }}" disabled>
                                                                                        <option value="" disabled {{ $medicalOldValue === '' ? 'selected' : '' }}>Select an answer</option>
                                                                                        <option value="Yes" {{ (string) $medicalOldValue === 'Yes' ? 'selected' : '' }}>Yes</option>
                                                                                        <option value="No" {{ (string) $medicalOldValue === 'No' ? 'selected' : '' }}>No</option>
                                                                                    </select>
                                                                                @elseif($medicalEditField['type'] === 'select')
                                                                                    <select class="medical-information-edit-control" data-medical-edit-control name="medical_information[{{ $medicalEditKey }}]" aria-label="Edit {{ $medicalLabel }}" disabled>
                                                                                        <option value="" disabled {{ (string) $medicalOldValue === '' ? 'selected' : '' }}>Select an option</option>
                                                                                        @foreach($medicalEditField['options'] ?? [] as $medicalOptionValue => $medicalOptionLabel)
                                                                                            <option value="{{ $medicalOptionValue }}" {{ (string) $medicalOldValue === (string) $medicalOptionValue ? 'selected' : '' }}>{{ $medicalOptionLabel }}</option>
                                                                                        @endforeach
                                                                                        @if((string) $medicalOldValue !== '' && !array_key_exists((string) $medicalOldValue, $medicalEditField['options'] ?? []))
                                                                                            <option value="{{ $medicalOldValue }}" selected>{{ $medicalOldValue }}</option>
                                                                                        @endif
                                                                                    </select>
                                                                                @elseif(in_array($medicalEditField['type'], ['json', 'json-text', 'textarea'], true))
                                                                                    <textarea class="medical-information-edit-control" data-medical-edit-control @if($medicalEditField['type'] === 'json-text') data-medical-json-text @endif name="medical_information[{{ $medicalEditKey }}]" aria-label="Edit {{ $medicalLabel }}" disabled>{{ $medicalOldValue }}</textarea>
                                                                                @else
                                                                                    <input class="medical-information-edit-control" data-medical-edit-control type="{{ $medicalEditField['type'] }}" name="medical_information[{{ $medicalEditKey }}]" value="{{ $medicalOldValue }}" aria-label="Edit {{ $medicalLabel }}" disabled>
                                                                                @endif
                                                                                @error('medical_information.' . $medicalEditKey)<small class="medical-information-field-error">{{ $message }}</small>@enderror
                                                                            @endif
                                                                        </dd>
                                                                    </div>
                                                                @endforeach
                                                            </div>
                                                        </article>
                                                    @endforeach
                                                </div>
                                            @endunless
                                        </div>
                                    @else
                                    <article class="medical-information-category-card" aria-label="{{ $medicalSection['display_name'] }}">
                                        <div class="medical-information-section-head">
                                            <span class="medical-information-category-icon"><x-outline-icon name="{{ $medicalSection['icon'] }}" /></span>
                                            <h4>{{ $medicalSection['display_name'] }}</h4>
                                        </div>
                                        <div class="medical-information-rows">
                                            @foreach(collect($medicalSection['items'])->chunk(max(1, (int) ceil(count($medicalSection['items']) / 2))) as $medicalColumn)
                                                <dl class="medical-information-row-column">
                                                    @foreach($medicalColumn as $medicalLabel => $medicalValue)
                                                        @php
                                                            $medicalEditField = $medicalInformationEditMap[$medicalSection['name']][$medicalLabel] ?? null;
                                                            $medicalEditKey = $medicalEditField['key'] ?? null;
                                                            $medicalEditRawValue = $medicalEditKey ? data_get($medicalAssessmentEditableValues ?? [], $medicalEditKey) : null;
                                                            $medicalEditValue = $medicalEditField ? $formatMedicalEditValue($medicalEditRawValue, $medicalEditField['type']) : '';
                                                            $medicalOldValue = $medicalEditKey ? old('medical_information.' . $medicalEditKey, $medicalEditValue) : '';
                                                            if (is_string($medicalOldValue) && strcasecmp(trim($medicalOldValue), 'Not recorded') === 0) {
                                                                $medicalOldValue = '';
                                                            }
                                                            $medicalSelectOptions = $medicalEditField['options'] ?? [];
                                                            $medicalFitStatusStoredValue = (string) $medicalOldValue;
                                                            $medicalFitOtherValue = '';
                                                            if (($medicalEditField['type'] ?? null) === 'select-other'
                                                                && preg_match('/^Others:\s*(.*)$/i', (string) $medicalOldValue, $medicalFitMatch)) {
                                                                $medicalFitOtherValue = trim($medicalFitMatch[1]);
                                                                $medicalOldValue = 'Others';
                                                            }
                                                            if ($medicalEditField && in_array($medicalEditField['type'], ['select', 'select-other'], true) && $medicalOldValue !== '' && !array_key_exists((string) $medicalOldValue, $medicalSelectOptions)) {
                                                                $medicalSelectOptions = [(string) $medicalOldValue => (string) $medicalOldValue] + $medicalSelectOptions;
                                                            }
                                                            $medicalChoiceSelectedValues = $medicalEditField && str_starts_with($medicalEditField['type'], 'choice-')
                                                                ? $normalizeMedicalChoiceValues(old('medical_information.' . $medicalEditKey, $medicalEditValue))
                                                                : [];
                                                            $medicalChoiceOptions = $medicalEditField['options'] ?? [];
                                                            foreach ($medicalChoiceSelectedValues as $medicalSelectedValue) {
                                                                $hasMedicalChoiceOption = collect($medicalChoiceOptions)->keys()->contains(
                                                                    fn ($optionValue) => strtolower((string) $optionValue) === strtolower($medicalSelectedValue)
                                                                );
                                                                if (!$hasMedicalChoiceOption) {
                                                                    $medicalChoiceOptions = [$medicalSelectedValue => $medicalSelectedValue] + $medicalChoiceOptions;
                                                                }
                                                            }
                                                            $medicalChoiceIsJson = $medicalEditField && in_array($medicalEditField['type'], ['choice-checkboxes', 'choice-radios-json'], true);
                                                            $medicalChoiceInputType = $medicalEditField && str_contains($medicalEditField['type'], 'radios') ? 'radio' : 'checkbox';
                                                            $medicalVaccineHistoryValue = $medicalEditKey === 'vaccine_history'
                                                                ? old('medical_information.vaccine_history', $medicalEditRawValue ?? [])
                                                                : [];
                                                            if (is_string($medicalVaccineHistoryValue)) {
                                                                $medicalDecodedVaccineHistory = json_decode($medicalVaccineHistoryValue, true);
                                                                $medicalVaccineHistoryValue = is_array($medicalDecodedVaccineHistory) ? $medicalDecodedVaccineHistory : [];
                                                            }
                                                            if ($medicalEditField && $medicalEditField['type'] === 'json-text') {
                                                                $medicalOldValue = $formatMedicalEditValue($medicalOldValue, 'json-text');
                                                            }
                                                            $medicalOtherDisabilityValue = '';
                                                            if ($medicalEditKey === 'disability_type') {
                                                                $medicalDisabilityOptions = $medicalEditField['options'] ?? [];
                                                                $medicalStoredDisabilityValue = trim((string) $medicalOldValue);
                                                                if (!array_key_exists($medicalStoredDisabilityValue, $medicalDisabilityOptions) && $medicalStoredDisabilityValue !== '') {
                                                                    $medicalOtherDisabilityValue = $medicalStoredDisabilityValue;
                                                                    $medicalOldValue = 'Others';
                                                                }
                                                                $medicalOtherDisabilityValue = old('medical_information.other_disability_type', $medicalOtherDisabilityValue);
                                                            }
                                                            if (isset($medicalEditField['unit']) && filled($medicalValue) && $medicalValue !== $medicalMissingValueLabel) {
                                                                $medicalValue = rtrim((string) $medicalValue) . ' ' . $medicalEditField['unit'];
                                                            }
                                                            $medicalDependsOn = $medicalEditField['depends_on'] ?? null;
                                                            $medicalDependsOnValue = $medicalDependsOn
                                                                ? $formatMedicalEditValue(old(
                                                                    'medical_information.' . $medicalDependsOn['key'],
                                                                    data_get($medicalAssessmentEditableValues ?? [], $medicalDependsOn['key'])
                                                                ), $medicalDependsOn['type'])
                                                                : null;
                                                            $medicalDependsOnValueMatches = $medicalDependsOn
                                                                && strtolower(trim((string) $medicalDependsOnValue)) === strtolower(trim((string) $medicalDependsOn['value']));
                                                        @endphp
                                                        <div class="medical-information-row @if(!$medicalEditField) medical-information-row-readonly @endif @if(($medicalEditField['type'] ?? null) === 'address') medical-information-row-address @endif @if($medicalSection['name'] === 'Address' && $medicalLabel === 'ZIP code') medical-information-row-address-zip @endif @if($medicalEditKey === 'vaccine_history') medical-information-row-vaccine-history @endif" @if($medicalDependsOn) data-medical-depends-on-key="{{ $medicalDependsOn['key'] }}" data-medical-depends-on-value="{{ $medicalDependsOn['value'] }}" @if(!$medicalDependsOnValueMatches) hidden @endif @endif>
                                                            <dt>{{ $medicalLabel }}</dt>
                                                            <dd>
                                                                <span class="medical-information-read-value">{{ $medicalValue }}</span>
                                                                @if($medicalEditField)
                                                                    @if(in_array($medicalEditField['type'], ['boolean', 'yes-no'], true))
                                                                        <select class="medical-information-edit-control" data-medical-edit-control name="medical_information[{{ $medicalEditKey }}]" aria-label="Edit {{ $medicalLabel }}" disabled>
                                                                            <option value="" disabled {{ $medicalOldValue === '' ? 'selected' : '' }}>Select an answer</option>
                                                                            <option value="Yes" {{ (string) $medicalOldValue === 'Yes' ? 'selected' : '' }}>Yes</option>
                                                                            <option value="No" {{ (string) $medicalOldValue === 'No' ? 'selected' : '' }}>No</option>
                                                                        </select>
                                                                    @elseif(in_array($medicalEditField['type'], ['select', 'select-other'], true))
                                                                        @if($medicalEditField['type'] === 'select-other')
                                                                            <div data-medical-fit-status>
                                                                                <select class="medical-information-edit-control" data-medical-edit-control data-medical-conditional-source="fit_status_choice" data-medical-fit-status-select aria-label="Edit {{ $medicalLabel }}" disabled>
                                                                                    <option value="" disabled {{ (string) $medicalOldValue === '' ? 'selected' : '' }}>Select fit status</option>
                                                                                    @foreach($medicalSelectOptions as $medicalOptionValue => $medicalOptionLabel)
                                                                                        <option value="{{ $medicalOptionValue }}" {{ (string) $medicalOldValue === (string) $medicalOptionValue ? 'selected' : '' }}>{{ $medicalOptionLabel }}</option>
                                                                                    @endforeach
                                                                                </select>
                                                                                <div class="medical-information-conditional-input" data-medical-depends-on-key="fit_status_choice" data-medical-depends-on-value="Others" @if((string) $medicalOldValue !== 'Others') hidden @endif>
                                                                                    <label>Other fit status
                                                                                        <input type="text" data-medical-edit-control data-medical-fit-status-other value="{{ $medicalFitOtherValue }}" maxlength="255" aria-label="Other fit status" disabled>
                                                                                    </label>
                                                                                </div>
                                                                                <input type="hidden" data-medical-edit-control data-medical-fit-status-value name="medical_information[{{ $medicalEditKey }}]" value="{{ $medicalFitStatusStoredValue }}" disabled>
                                                                            </div>
                                                                        @else
                                                                        <select class="medical-information-edit-control" data-medical-edit-control name="medical_information[{{ $medicalEditKey }}]" aria-label="Edit {{ $medicalLabel }}" disabled>
                                                                            <option value="" disabled {{ (string) $medicalOldValue === '' ? 'selected' : '' }}>Select an option</option>
                                                                            @foreach($medicalSelectOptions as $medicalOptionValue => $medicalOptionLabel)
                                                                                <option value="{{ $medicalOptionValue }}" {{ (string) $medicalOldValue === (string) $medicalOptionValue ? 'selected' : '' }}>{{ $medicalOptionLabel }}</option>
                                                                            @endforeach
                                                                        </select>
                                                                        @endif
                                                                        @if($medicalEditKey === 'disability_type')
                                                                            <div class="medical-information-conditional-input" data-medical-depends-on-key="disability_type" data-medical-depends-on-value="Others" @if((string) $medicalOldValue !== 'Others') hidden @endif>
                                                                                <label for="medicalOtherDisabilityType">Other disability type</label>
                                                                                <input id="medicalOtherDisabilityType" class="medical-information-edit-control" data-medical-edit-control name="medical_information[other_disability_type]" value="{{ $medicalOtherDisabilityValue }}" maxlength="255" aria-label="Other disability type" disabled>
                                                                                @error('medical_information.other_disability_type')<small class="medical-information-field-error">{{ $message }}</small>@enderror
                                                                            </div>
                                                                        @endif
                                                                    @elseif($medicalEditField['type'] === 'address')
                                                                        <div class="medical-information-address-edit" data-medical-address-editor>
                                                                            @php
                                                                                $medicalAddressStreet = old('medical_information.street');
                                                                                if (!filled($medicalAddressStreet)) {
                                                                                    $medicalAddressStreet = data_get($medicalAssessmentEditableValues ?? [], 'street');
                                                                                }
                                                                            @endphp
                                                                            <label class="medical-information-address-field">House No. / Street
                                                                                <input class="medical-information-edit-control" data-medical-edit-control data-medical-address-part="street" name="medical_information[street]" value="{{ $medicalAddressStreet }}" maxlength="255" aria-label="Edit house number or street" disabled>
                                                                            </label>
                                                                            @foreach([
                                                                                'province' => ['Province', 'Select province'],
                                                                                'municipality' => ['City / Municipality', 'Select city or municipality'],
                                                                                'barangay' => ['Barangay', 'Select barangay'],
                                                                            ] as $medicalAddressLevel => [$medicalAddressLabel, $medicalAddressPlaceholder])
                                                                                @php
                                                                                    $medicalAddressValue = old('medical_information.' . $medicalAddressLevel);
                                                                                    if (!filled($medicalAddressValue)) {
                                                                                        $medicalAddressValue = data_get($medicalAssessmentEditableValues ?? [], $medicalAddressLevel);
                                                                                    }
                                                                                @endphp
                                                                                <label class="medical-information-address-field">{{ $medicalAddressLabel }}
                                                                                    <select class="medical-information-edit-control" data-medical-edit-control data-medical-address-location="{{ $medicalAddressLevel }}" name="medical_information[{{ $medicalAddressLevel }}]" aria-label="Edit {{ $medicalAddressLabel }}" disabled>
                                                                                        <option value="">{{ $medicalAddressPlaceholder }}</option>
                                                                                        @if(filled($medicalAddressValue))
                                                                                            <option value="{{ $medicalAddressValue }}" selected>{{ $medicalAddressValue }}</option>
                                                                                        @endif
                                                                                    </select>
                                                                                </label>
                                                                            @endforeach
                                                                            <span class="medical-information-address-status" data-medical-address-status>Select a province, then a city/municipality and barangay.</span>
                                                                        </div>
                                                                    @elseif($medicalEditField['type'] === 'vaccine-history')
                                                                        <div class="medical-information-vaccine-history">
                                                                            <input type="hidden" class="medical-information-edit-control" data-medical-edit-control data-medical-vaccine-history name="medical_information[vaccine_history]" value="{{ json_encode($medicalVaccineHistoryValue, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) }}" disabled>
                                                                            @foreach(['first_dose' => '1st Dose', 'second_dose' => '2nd Dose', 'booster_1' => 'Booster 1', 'booster_2' => 'Booster 2'] as $medicalDoseKey => $medicalDoseLabel)
                                                                                <div class="medical-information-dose-row">
                                                                                    <strong>{{ $medicalDoseLabel }}</strong>
                                                                                    <input type="date" data-medical-edit-control data-medical-vaccine-dose="{{ $medicalDoseKey }}" data-medical-vaccine-part="date" value="{{ data_get($medicalVaccineHistoryValue, $medicalDoseKey . '.date') }}" min="2021-03-01" max="{{ now()->toDateString() }}" aria-label="{{ $medicalDoseLabel }} date" disabled>
                                                                                    <input type="text" data-medical-edit-control data-medical-vaccine-dose="{{ $medicalDoseKey }}" data-medical-vaccine-part="brand" value="{{ data_get($medicalVaccineHistoryValue, $medicalDoseKey . '.brand') }}" maxlength="100" placeholder="Vaccine brand" aria-label="{{ $medicalDoseLabel }} vaccine brand" disabled>
                                                                                </div>
                                                                            @endforeach
                                                                            @foreach($errors->getMessages() as $medicalErrorKey => $medicalErrorMessages)
                                                                                @if(\Illuminate\Support\Str::startsWith($medicalErrorKey, 'medical_information.vaccine_history'))
                                                                                    @foreach($medicalErrorMessages as $medicalErrorMessage)<small class="medical-information-field-error">{{ $medicalErrorMessage }}</small>@endforeach
                                                                                @endif
                                                                            @endforeach
                                                                        </div>
                                                                    @elseif(str_starts_with($medicalEditField['type'], 'choice-'))
                                                                        <div class="medical-information-choice-group" data-medical-choice-group data-medical-choice-format="{{ $medicalChoiceIsJson ? 'json' : 'scalar' }}">
                                                                            <input type="hidden" data-medical-edit-control data-medical-choice-value name="medical_information[{{ $medicalEditKey }}]" value="{{ $medicalChoiceIsJson ? (json_encode($medicalChoiceSelectedValues, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) ?: '[]') : ($medicalChoiceSelectedValues[0] ?? '') }}" disabled>
                                                                            <div class="medical-information-choice-options">
                                                                                @foreach($medicalChoiceOptions as $medicalOptionValue => $medicalOptionLabel)
                                                                                    @php
                                                                                        $medicalChoiceIsSelected = in_array(strtolower((string) $medicalOptionValue), array_map('strtolower', $medicalChoiceSelectedValues), true);
                                                                                    @endphp
                                                                                    <label><input type="{{ $medicalChoiceInputType }}" data-medical-edit-control data-medical-choice-option value="{{ $medicalOptionValue }}" @if($medicalChoiceInputType === 'radio') name="medical-information-choice-{{ $medicalEditKey }}" @endif @checked($medicalChoiceIsSelected) disabled><span>{{ $medicalOptionLabel }}</span></label>
                                                                                @endforeach
                                                                            </div>
                                                                        </div>
                                                                    @elseif(in_array($medicalEditField['type'], ['json', 'json-text', 'textarea'], true))
                                                                        <textarea class="medical-information-edit-control" data-medical-edit-control @if($medicalEditField['type'] === 'json-text') data-medical-json-text @endif name="medical_information[{{ $medicalEditKey }}]" aria-label="Edit {{ $medicalLabel }}" disabled>{{ $medicalOldValue }}</textarea>
                                                                    @elseif(isset($medicalEditField['unit']))
                                                                        <div class="medical-information-unit-edit">
                                                                            <input class="medical-information-edit-control" data-medical-edit-control type="{{ $medicalEditField['type'] }}" name="medical_information[{{ $medicalEditKey }}]" value="{{ $medicalOldValue }}" @foreach(['min', 'max', 'step'] as $medicalNumberAttribute) @if(isset($medicalEditField[$medicalNumberAttribute])) {{ $medicalNumberAttribute }}="{{ $medicalEditField[$medicalNumberAttribute] }}" @endif @endforeach aria-label="Edit {{ $medicalLabel }}" disabled>
                                                                            <span>{{ $medicalEditField['unit'] }}</span>
                                                                        </div>
                                                                    @else
                                                                        <input class="medical-information-edit-control" data-medical-edit-control type="{{ $medicalEditField['type'] }}" name="medical_information[{{ $medicalEditKey }}]" value="{{ $medicalOldValue }}" @if(isset($medicalEditField['min'])) min="{{ $medicalEditField['min'] }}" @endif @if(isset($medicalEditField['max'])) max="{{ $medicalEditField['max'] }}" @endif @if(isset($medicalEditField['step'])) step="{{ $medicalEditField['step'] }}" @endif @if(isset($medicalEditField['min_length'])) minlength="{{ $medicalEditField['min_length'] }}" @endif @if(isset($medicalEditField['max_length'])) maxlength="{{ $medicalEditField['max_length'] }}" @endif @if(isset($medicalEditField['inputmode'])) inputmode="{{ $medicalEditField['inputmode'] }}" @endif @if(isset($medicalEditField['autocomplete'])) autocomplete="{{ $medicalEditField['autocomplete'] }}" @endif @if(isset($medicalEditField['pattern'])) pattern="{{ $medicalEditField['pattern'] }}" title="{{ $medicalEditField['title'] ?? '' }}" @endif aria-label="Edit {{ $medicalLabel }}" disabled>
                                                                    @endif
                                                                    @error('medical_information.' . $medicalEditKey)<small class="medical-information-field-error">{{ $message }}</small>@enderror
                                                                @endif
                                                            </dd>
                                                        </div>
                                                    @endforeach
                                                </dl>
                                            @endforeach
                                        </div>
                                    </article>
                                    @endif
                                @endforeach
                            </section>
                        @endforeach

                        <section class="medical-information-history-panel" data-medical-panel="assessment-history" aria-label="History" hidden>
                            <div class="medical-information-history-list">
                                @php
                                    $medicalAssessmentHistoryItems = collect($medicalAssessmentHistory ?? [])->map(fn ($entry) => [
                                        'type' => 'assessment',
                                        'title' => $entry->condition_or_illness ?: 'Assessment update',
                                        'created_at' => $entry->created_at,
                                        'findings' => $entry->findings,
                                        'remarks' => $entry->remarks,
                                        'reason' => $entry->reason,
                                        'account' => $entry->recordedBy?->name ?? 'Clinic staff',
                                        'fields' => [],
                                    ])->concat(collect($medicalInformationChangeHistory ?? []))
                                        ->sortByDesc(fn ($entry) => data_get($entry, 'created_at')?->getTimestamp() ?? 0)
                                        ->values();
                                @endphp
                                @forelse($medicalAssessmentHistoryItems as $historyEntry)
                                    @if($historyEntry['type'] === 'information' && !$medicalAssessmentIsEmployee)
                                        <div class="medical-information-history-day">
                                            <div class="medical-information-history-day-head">
                                                <strong>Health information updated</strong>
                                            </div>
                                            <div class="medical-information-history-updates">
                                                @foreach($historyEntry['fields'] as $changedField)
                                                    @php
                                                        $changedFieldLabel = (string) ($changedField['label'] ?? 'Update');
                                                        $changedFieldInitial = strtoupper(substr($changedFieldLabel, 0, 1));
                                                        $changedFieldTime = $changedField['created_at'] ?? $historyEntry['created_at'];
                                                        $changedFieldAccount = $changedField['account'] ?? ($historyEntry['events'][0]['account'] ?? 'Clinic staff');
                                                    @endphp
                                                    <article class="medical-information-history-update">
                                                        <span class="medical-information-history-initial" aria-hidden="true">{{ $changedFieldInitial }}</span>
                                                        <div class="medical-information-history-update-main">
                                                            <strong class="medical-information-history-update-title">{{ $changedFieldLabel }} Updated</strong>
                                                            <div class="medical-information-history-values">
                                                                <span class="medical-information-history-value-group">
                                                                    <span class="medical-information-history-value-label">Previous value</span>
                                                                    <span class="medical-information-history-value">{{ $changedField['previous_value'] ?? 'Value not captured for this earlier update' }}</span>
                                                                </span>
                                                                <span class="medical-information-history-arrow" aria-hidden="true">&rarr;</span>
                                                                <span class="medical-information-history-value-group">
                                                                    <span class="medical-information-history-value-label">Updated value</span>
                                                                    <span class="medical-information-history-value is-updated">{{ $changedField['value'] }}</span>
                                                                </span>
                                                            </div>
                                                        </div>
                                                        <div class="medical-information-history-meta">
                                                            <time>{{ optional($changedFieldTime)->format('M d, Y - h:i A') }}</time>
                                                            <span>{{ $changedFieldAccount }}</span>
                                                        </div>
                                                        @if(filled($changedField['reason'] ?? null))
                                                            <p class="medical-information-history-reason"><strong>Reason for change</strong><br>{{ $changedField['reason'] }}</p>
                                                        @endif
                                                    </article>
                                                @endforeach
                                            </div>
                                        </div>
                                    @else
                                    <article class="medical-information-history-entry">
                                        <div class="medical-information-entry-head">
                                            <strong>{{ $historyEntry['title'] }}</strong>
                                        <time>{{ $historyEntry['type'] === 'information' ? $historyEntry['date_label'] : optional($historyEntry['created_at'])->format('M d, Y h:i A') }}</time>
                                        </div>
                                    @if($historyEntry['type'] === 'information')
                                        <table class="medical-information-change-table">
                                            <thead><tr><th scope="col">Information</th><th scope="col">Update to</th></tr></thead>
                                            <tbody>
                                                @foreach($historyEntry['fields'] as $changedField)
                                                    <tr><td>{{ $changedField['label'] }}</td><td>{{ $changedField['value'] }}</td></tr>
                                                @endforeach
                                            </tbody>
                                        </table>
                                        <div class="medical-information-change-events">
                                            @foreach($historyEntry['events'] as $historyEvent)
                                                <small>{{ optional($historyEvent['created_at'])->format('h:i A') }} · Updated by {{ $historyEvent['account'] }}</small>
                                            @endforeach
                                        </div>
                                    @else
                                        @if(filled($historyEntry['findings'] ?? null))<p><span>Findings</span>{{ $historyEntry['findings'] }}</p>@endif
                                        @if(filled($historyEntry['remarks'] ?? null))<p><span>Remarks</span>{{ $historyEntry['remarks'] }}</p>@endif
                                        @if(filled($historyEntry['reason'] ?? null))<p><span>Reason for update</span>{{ $historyEntry['reason'] }}</p>@endif
                                        <small>Recorded by {{ $historyEntry['account'] }}</small>
                                    @endif
                                    </article>
                                    @endif
                                @empty
                                    <div class="medical-information-empty">No history records have been added.</div>
                                @endforelse
                            </div>
                        </section>

                    </div>
                </main>
            </div>

            <footer class="medical-information-footer">
                <button type="button" data-medical-history-button data-medical-view="assessment-history" data-medical-label="History">
                    <x-outline-icon name="arrow-path" /><span>View History</span>
                </button>
                <button type="button" data-medical-edit-cancel hidden>
                    <x-outline-icon name="x-mark" /><span>Cancel</span>
                </button>
                <button type="submit" class="medical-information-update" data-medical-edit-submit hidden>
                    <x-outline-icon name="check" /><span>Update</span>
                </button>
            </footer>
            </form>
        </div>
    </div>

    @once
        @push('scripts')
            <script>
                document.addEventListener('DOMContentLoaded', function () {
                    const modal = document.getElementById('medicalInformationModal');
                    if (!modal) return;

                    const categoryGrid = modal.querySelector('[data-medical-category-grid]');
                    const categoryPanels = [...modal.querySelectorAll('[data-medical-category-detail]')];
                    const form = modal.querySelector('#medicalInformationUpdateForm');
                    const viewTitle = modal.querySelector('[data-medical-view-title]');
                    const viewDescription = modal.querySelector('[data-medical-view-description]');
                    const viewButtons = [...modal.querySelectorAll('[data-medical-view]')];
                    const navButtons = [...modal.querySelectorAll('.medical-information-nav [data-medical-view]')];
                    const editToggle = modal.querySelector('[data-medical-edit-toggle]');
                    const editCancel = modal.querySelector('[data-medical-edit-cancel]');
                    const editSubmit = modal.querySelector('[data-medical-edit-submit]');
                    const historyButton = modal.querySelector('[data-medical-history-button]');
                    const viewInput = modal.querySelector('[data-medical-edit-view]');
                    const editControls = [...modal.querySelectorAll('[data-medical-edit-control]')];
                    if (!categoryGrid || !form) return;

                    let activeView = 'overview';
                    let isEditing = false;
                    let openSelect = null;
                    const customSelects = new Map();
                    const addressEditor = modal.querySelector('[data-medical-address-editor]');
                    const addressSelects = [...modal.querySelectorAll('[data-medical-address-location]')];
                    let addressLookupReady = addressSelects.length === 0;
                    const addressSelect = (level) => addressEditor?.querySelector(`[data-medical-address-location="${level}"]`);
                    const addressStatus = modal.querySelector('[data-medical-address-status]');
                    const zipInput = modal.querySelector('[name="medical_information[zipcode]"]');
                    const normalizeAddressName = (value) => String(value || '').trim().toLowerCase()
                        .replace(/^(city|municipality)\s+of\s+/, '')
                        .replace(/^(city|municipality)\s+/, '')
                        .replace(/\s+(city|municipality)$/, '')
                        .replace(/^(brgy\.?|barangay)\s+/, '')
                        .replace(/[^a-z0-9]+/g, ' ').trim();
                    const formatAddressName = (value, level) => {
                        const label = String(value || '').trim();
                        if (level !== 'municipality') return label;
                        const city = label.match(/^City of\s+(.+)$/i);
                        if (city) return `${city[1].trim()} City`;
                        const municipality = label.match(/^Municipality of\s+(.+)$/i);
                        return municipality ? `${municipality[1].trim()} Municipality` : label;
                    };
                    const fetchAddressCollection = async (path) => {
                        const controller = new AbortController();
                        const timeout = window.setTimeout(() => controller.abort(), 10000);
                        try {
                            const response = await fetch(`https://psgc.gitlab.io/api${path}`, {
                                headers: { Accept: 'application/json' }, credentials: 'omit', signal: controller.signal,
                            });
                            if (!response.ok) throw new Error(`Address lookup failed (${response.status}).`);
                            const payload = await response.json();
                            return Array.isArray(payload) ? payload : (Array.isArray(payload.value) ? payload.value : []);
                        } finally {
                            window.clearTimeout(timeout);
                        }
                    };
                    const setAddressOptions = (select, placeholder, items, selectedValue, level) => {
                        if (!select) return null;
                        const selected = String(selectedValue || '').trim();
                        const sorted = [...items].sort((left, right) => formatAddressName(left.name, level).localeCompare(
                            formatAddressName(right.name, level), undefined, { sensitivity: 'base' }
                        ));
                        const match = sorted.find((item) => normalizeAddressName(item.name) === normalizeAddressName(selected));
                        select.replaceChildren(new Option(placeholder, ''));
                        sorted.forEach((item) => {
                            const label = formatAddressName(item.name, level);
                            if (!label) return;
                            const option = new Option(label, label);
                            if (item.code) option.dataset.locationCode = item.code;
                            select.add(option);
                        });
                        const resolved = match ? formatAddressName(match.name, level) : selected;
                        if (resolved && ![...select.options].some((option) => option.value === resolved)) {
                            select.add(new Option(`${resolved} (existing value)`, resolved));
                        }
                        select.value = resolved;
                        return match;
                    };
                    const setAddressStatus = (message, isError = false) => {
                        if (!addressStatus) return;
                        addressStatus.textContent = message;
                        addressStatus.style.color = isError ? 'var(--medical-address-error)' : '';
                    };
                    const addressCode = (level) => addressSelect(level)?.selectedOptions[0]?.dataset.locationCode || '';
                    const loadAddressBarangays = async (cityCode, selected = '') => {
                        const select = addressSelect('barangay');
                        if (!cityCode) {
                            setAddressOptions(select, 'Select barangay', [], selected, 'barangay');
                            return;
                        }
                        const items = await fetchAddressCollection(`/cities-municipalities/${encodeURIComponent(cityCode)}/barangays/`);
                        setAddressOptions(select, 'Select barangay', items, selected, 'barangay');
                    };
                    const loadAddressCities = async (provinceCode, selectedCity = '', selectedBarangay = '') => {
                        if (!provinceCode) {
                            setAddressOptions(addressSelect('municipality'), 'Select city or municipality', [], selectedCity, 'municipality');
                            setAddressOptions(addressSelect('barangay'), 'Select barangay', [], selectedBarangay, 'barangay');
                            return;
                        }
                        const endpoint = provinceCode === '130000000'
                            ? '/regions/130000000/cities-municipalities/'
                            : `/provinces/${encodeURIComponent(provinceCode)}/cities-municipalities/`;
                        const cities = await fetchAddressCollection(endpoint);
                        const selectedCityMatch = setAddressOptions(addressSelect('municipality'), 'Select city or municipality', cities, selectedCity, 'municipality');
                        await loadAddressBarangays(selectedCityMatch?.code || addressCode('municipality'), selectedBarangay);
                    };
                    const postalCodesPromise = addressSelects.length
                        ? Promise.all([
                            fetch('https://raw.githubusercontent.com/maikkoko/postal-ph/master/data/postal_codes/_all.json', { credentials: 'omit' }),
                            fetch('https://raw.githubusercontent.com/maikkoko/postal-ph/master/data/cities/cities_with_id.json', { credentials: 'omit' }),
                        ]).then(async ([postalResponse, cityResponse]) => {
                            if (!postalResponse.ok || !cityResponse.ok) return new Map();
                            const [groups, cities] = await Promise.all([postalResponse.json(), cityResponse.json()]);
                            const names = new Map((Array.isArray(cities) ? cities : []).map((city) => [String(city.id || ''), city.name || '']));
                            const index = new Map();
                            (Array.isArray(groups) ? groups : []).forEach((group) => (Array.isArray(group?.data) ? group.data : []).forEach((record) => {
                                const city = names.get(String(record?.citymun_id || '')) || '';
                                const zip = String(record?.zip_code ?? record?.zipcode ?? record?.postal_code ?? record?.postalCode ?? '').trim();
                                if (!city || !zip) return;
                                const cityKey = normalizeAddressName(city);
                                if (!index.has(cityKey)) index.set(cityKey, zip);
                                if (record?.location) index.set(`${cityKey}|${normalizeAddressName(record.location)}`, zip);
                            }));
                            return index;
                        }).catch(() => new Map())
                        : Promise.resolve(new Map());
                    const syncAddressZipcode = async () => {
                        if (!zipInput) return;
                        const index = await postalCodesPromise;
                        const city = normalizeAddressName(addressSelect('municipality')?.value);
                        const barangay = normalizeAddressName(addressSelect('barangay')?.value);
                        const zip = index.get(`${city}|${barangay}`) || index.get(city);
                        if (zip) zipInput.value = zip;
                    };
                    const loadStudentAddressLocations = async () => {
                        if (!addressEditor || addressSelects.length === 0) return;
                        const selectedProvince = addressSelect('province')?.value || '';
                        const selectedCity = addressSelect('municipality')?.value || '';
                        const selectedBarangay = addressSelect('barangay')?.value || '';
                        try {
                            setAddressStatus('Loading address options...');
                            const provinces = [{ code: '130000000', name: 'Metro Manila' }, ...(await fetchAddressCollection('/provinces/'))];
                            const provinceMatch = setAddressOptions(addressSelect('province'), 'Select province', provinces, selectedProvince, 'province');
                            await loadAddressCities(provinceMatch?.code || '', selectedCity, selectedBarangay);
                            void syncAddressZipcode();
                            setAddressStatus('Select a province, city/municipality, and barangay.');
                        } catch (error) {
                            setAddressStatus('Address options are unavailable. Existing values were preserved; refresh and try again.', true);
                        } finally {
                            addressLookupReady = true;
                            addressSelects.forEach((select) => { select.disabled = !isEditing; });
                        }
                    };
                    addressSelect('province')?.addEventListener('change', async () => {
                        if (zipInput) zipInput.value = '';
                        setAddressOptions(addressSelect('municipality'), 'Select city or municipality', [], '', 'municipality');
                        setAddressOptions(addressSelect('barangay'), 'Select barangay', [], '', 'barangay');
                        setAddressStatus('Loading cities and municipalities...');
                        try {
                            await loadAddressCities(addressCode('province'));
                            setAddressStatus('Select a city/municipality, then a barangay.');
                        } catch (error) {
                            setAddressStatus('City and municipality options are unavailable. Please refresh and try again.', true);
                        }
                    });
                    addressSelect('municipality')?.addEventListener('change', async () => {
                        if (zipInput) zipInput.value = '';
                        setAddressOptions(addressSelect('barangay'), 'Select barangay', [], '', 'barangay');
                        setAddressStatus('Loading barangays...');
                        try {
                            await loadAddressBarangays(addressCode('municipality'));
                            await syncAddressZipcode();
                            setAddressStatus('Address options loaded.');
                        } catch (error) {
                            setAddressStatus('Barangay options are unavailable. Please refresh and try again.', true);
                        }
                    });
                    addressSelect('barangay')?.addEventListener('change', syncAddressZipcode);

                    const closeMedicalSelect = (restoreFocus = false) => {
                        if (!openSelect) return;
                        const state = openSelect;
                        openSelect = null;
                        state.menu.hidden = true;
                        state.trigger.setAttribute('aria-expanded', 'false');
                        if (restoreFocus) state.trigger.focus({ preventScroll: true });
                    };

                    const syncMedicalSelect = (state) => {
                        state.label.textContent = state.source.selectedOptions[0]?.textContent.trim() || 'Select an option';
                        state.trigger.disabled = state.source.disabled;
                        state.options.forEach((button) => {
                            button.setAttribute('aria-selected', button.dataset.value === state.source.value ? 'true' : 'false');
                        });
                    };

                    const syncConditionalMedicalFields = () => {
                        modal.querySelectorAll('[data-medical-depends-on-key]').forEach((row) => {
                            const parentName = `medical_information[${row.dataset.medicalDependsOnKey}]`;
                            const parent = editControls.find((control) => control.name === parentName)
                                || modal.querySelector(`[data-medical-conditional-source="${CSS.escape(row.dataset.medicalDependsOnKey)}"]`);
                            const visible = parent?.value === row.dataset.medicalDependsOnValue;
                            row.hidden = !visible;
                            row.querySelectorAll('[data-medical-edit-control]').forEach((control) => {
                                control.disabled = !isEditing || !visible;
                            });
                        });
                    };

                    const parseMedicalJsonText = (text) => {
                        const lines = text.split(/\r?\n/).map((line) => line.trim()).filter(Boolean);
                        const values = [];
                        const structured = {};
                        let hasStructuredValues = false;

                        lines.forEach((line) => {
                            const match = line.match(/^(.+?)\s*=\s*(.*)$/);
                            const path = match?.[1].split('.').map((part) => part.trim()).filter(Boolean) || [];
                            if (!match || path.length === 0) {
                                values.push(line);
                                return;
                            }

                            hasStructuredValues = true;
                            let target = structured;
                            path.forEach((part, index) => {
                                if (index === path.length - 1) {
                                    target[part] = match[2];
                                    return;
                                }
                                if (!target[part] || typeof target[part] !== 'object') target[part] = {};
                                target = target[part];
                            });
                        });

                        return hasStructuredValues ? structured : values;
                    };

                    const openMedicalSelect = (state, focusLast = false) => {
                        if (state.source.disabled || !isEditing) return;
                        closeMedicalSelect();
                        syncMedicalSelect(state);
                        openSelect = state;
                        state.menu.hidden = false;
                        state.trigger.setAttribute('aria-expanded', 'true');

                        // Keep the menu outside the scrolling cards so options remain visible.
                        const anchor = state.trigger.getBoundingClientRect();
                        const width = Math.min(Math.max(anchor.width, 160), window.innerWidth - 16);
                        const below = window.innerHeight - anchor.bottom - 12;
                        const above = anchor.top - 12;
                        state.menu.style.width = `${width}px`;
                        state.menu.style.maxHeight = `${Math.min(220, Math.max(80, below, above))}px`;
                        const height = state.menu.getBoundingClientRect().height;
                        const dropUp = below < height && above > below;
                        state.menu.style.left = `${Math.max(8, Math.min(anchor.left, window.innerWidth - width - 8))}px`;
                        state.menu.style.top = `${Math.max(8, dropUp ? anchor.top - height - 6 : anchor.bottom + 6)}px`;
                        const selected = state.options.find((button) => button.getAttribute('aria-selected') === 'true' && !button.disabled);
                        const enabled = state.options.filter((button) => !button.disabled);
                        (selected || (focusLast ? enabled.at(-1) : enabled[0]))?.focus({ preventScroll: true });
                    };

                    editControls.filter((control) => control.tagName === 'SELECT' && !control.hasAttribute('data-medical-address-location')).forEach((source, index) => {
                        const wrapper = document.createElement('div');
                        const trigger = document.createElement('button');
                        const label = document.createElement('span');
                        const menu = document.createElement('div');
                        const menuId = `medical-information-select-${index}-options`;
                        wrapper.className = 'medical-information-select';
                        trigger.type = 'button';
                        trigger.className = 'medical-information-select-trigger';
                        trigger.setAttribute('aria-label', source.getAttribute('aria-label') || 'Choose an option');
                        trigger.setAttribute('aria-haspopup', 'listbox');
                        trigger.setAttribute('aria-expanded', 'false');
                        trigger.setAttribute('aria-controls', menuId);
                        trigger.append(label, modal.querySelector('[data-medical-select-chevron]').content.cloneNode(true));
                        menu.id = menuId;
                        menu.className = 'medical-information-select-menu';
                        menu.setAttribute('role', 'listbox');
                        menu.setAttribute('aria-label', source.getAttribute('aria-label') || 'Options');
                        menu.hidden = true;
                        const state = { source, trigger, label, menu, options: [] };

                        Array.from(source.options).forEach((option) => {
                            const button = document.createElement('button');
                            const text = document.createElement('span');
                            button.type = 'button';
                            button.className = 'medical-information-select-option';
                            button.dataset.value = option.value;
                            button.setAttribute('role', 'option');
                            button.tabIndex = -1;
                            button.disabled = option.disabled;
                            text.textContent = option.textContent.trim();
                            button.append(text);
                            button.addEventListener('click', () => {
                                source.value = option.value;
                                source.dispatchEvent(new Event('input', { bubbles: true }));
                                source.dispatchEvent(new Event('change', { bubbles: true }));
                                closeMedicalSelect(true);
                            });
                            state.options.push(button);
                            menu.append(button);
                        });

                        trigger.addEventListener('click', () => {
                            if (openSelect === state) closeMedicalSelect();
                            else openMedicalSelect(state);
                        });
                        trigger.addEventListener('keydown', (event) => {
                            if (['ArrowDown', 'ArrowUp', 'Enter', ' '].includes(event.key)) {
                                event.preventDefault();
                                openMedicalSelect(state, event.key === 'ArrowUp');
                            }
                        });
                        menu.addEventListener('keydown', (event) => {
                            if (event.key === 'Escape') {
                                event.preventDefault();
                                event.stopPropagation();
                                closeMedicalSelect(true);
                                return;
                            }
                            if (event.key === 'Tab') {
                                closeMedicalSelect(true);
                                return;
                            }
                            const enabled = state.options.filter((button) => !button.disabled);
                            const current = enabled.indexOf(document.activeElement);
                            let next;
                            if (event.key === 'ArrowDown') next = enabled[(current + 1) % enabled.length];
                            else if (event.key === 'ArrowUp') next = enabled[(current - 1 + enabled.length) % enabled.length];
                            else if (event.key === 'Home') next = enabled[0];
                            else if (event.key === 'End') next = enabled.at(-1);
                            else if (event.key.length === 1 && event.key !== ' ' && !event.ctrlKey && !event.metaKey && !event.altKey) {
                                const ordered = [...enabled.slice(current + 1), ...enabled.slice(0, current + 1)];
                                next = ordered.find((button) => button.textContent.toLowerCase().startsWith(event.key.toLowerCase()));
                            }
                            if (next) {
                                event.preventDefault();
                                next.focus({ preventScroll: true });
                                next.scrollIntoView({ block: 'nearest' });
                            }
                        });
                        source.addEventListener('change', () => {
                            syncMedicalSelect(state);
                            syncConditionalMedicalFields();
                        });
                        source.before(wrapper);
                        source.classList.add('medical-information-select-source');
                        source.tabIndex = -1;
                        wrapper.append(source, trigger);
                        modal.append(menu);
                        customSelects.set(source, state);
                        syncMedicalSelect(state);
                    });

                    document.addEventListener('pointerdown', (event) => {
                        if (openSelect && !openSelect.trigger.contains(event.target) && !openSelect.menu.contains(event.target)) closeMedicalSelect();
                    });
                    modal.addEventListener('scroll', (event) => {
                        if (openSelect && !openSelect.menu.contains(event.target)) closeMedicalSelect();
                    }, true);
                    window.addEventListener('resize', () => closeMedicalSelect());
                    form.addEventListener('reset', () => {
                        closeMedicalSelect();
                        queueMicrotask(() => customSelects.forEach(syncMedicalSelect));
                    });
                    form.addEventListener('submit', () => {
                        form.querySelectorAll('[data-medical-choice-group]').forEach((group) => {
                            const target = group.querySelector('[data-medical-choice-value]');
                            if (!target || target.disabled) return;
                            const selected = [...group.querySelectorAll('[data-medical-choice-option]:checked')].map((option) => option.value);
                            target.value = group.dataset.medicalChoiceFormat === 'json'
                                ? JSON.stringify(selected)
                                : (selected[0] || '');
                        });
                        form.querySelectorAll('[data-medical-fit-status]').forEach((group) => {
                            const select = group.querySelector('[data-medical-fit-status-select]');
                            const target = group.querySelector('[data-medical-fit-status-value]');
                            if (!select || !target || target.disabled) return;
                            const other = group.querySelector('[data-medical-fit-status-other]')?.value.trim() || '';
                            target.value = select.value === 'Others'
                                ? `Others${other ? `: ${other}` : ''}`
                                : select.value;
                        });
                        form.querySelectorAll('[data-medical-json-text]').forEach((control) => {
                            if (!control.disabled) control.value = JSON.stringify(parseMedicalJsonText(control.value));
                        });
                        form.querySelectorAll('[data-medical-vaccine-history]').forEach((historyControl) => {
                            if (historyControl.disabled) return;
                            const history = {};
                            const doseFields = form.querySelectorAll('[data-medical-vaccine-dose]');
                            doseFields.forEach((field) => {
                                const dose = field.dataset.medicalVaccineDose;
                                const part = field.dataset.medicalVaccinePart;
                                history[dose] ??= { brand: '', date: '' };
                                history[dose][part] = field.value.trim();
                            });
                            Object.keys(history).forEach((dose) => {
                                if (!history[dose].brand && !history[dose].date) delete history[dose];
                            });
                            historyControl.value = JSON.stringify(history);
                        });
                    });

                    const setEditing = (editing) => {
                        closeMedicalSelect();
                        isEditing = editing;
                        modal.classList.toggle('is-editing', editing);
                        editControls.forEach((control) => {
                            control.disabled = !editing || (control.hasAttribute('data-medical-address-location') && !addressLookupReady);
                        });
                        syncConditionalMedicalFields();
                        customSelects.forEach(syncMedicalSelect);
                        if (editToggle) editToggle.hidden = editing || !editControlsForView(activeView).length;
                        if (editCancel) editCancel.hidden = !editing;
                        if (editSubmit) editSubmit.hidden = !editing;
                        if (historyButton) historyButton.hidden = editing || activeView === 'assessment-history';
                    };

                    const editControlsForView = (view) => {
                        if (view === 'overview' || view === 'assessment-history') return [];
                        return [...modal.querySelectorAll(`[data-medical-panel="${CSS.escape(view)}"] [data-medical-edit-control]`)];
                    };

                    const setView = (view, label = '') => {
                        closeMedicalSelect();
                        activeView = view;
                        const isOverview = view === 'overview';
                        const isHistory = view === 'assessment-history';
                        if (historyButton) historyButton.hidden = isHistory || isEditing;

                        categoryGrid.hidden = !isOverview;
                        categoryPanels.forEach((panel) => {
                            panel.hidden = isOverview || panel.dataset.medicalPanel !== view;
                        });
                        const historyPanel = modal.querySelector('[data-medical-panel="assessment-history"]');
                        if (historyPanel) historyPanel.hidden = !isHistory;

                        navButtons.forEach((button) => {
                            const selected = button.dataset.medicalView === view;
                            button.classList.toggle('is-active', selected);
                            button.setAttribute('aria-selected', selected ? 'true' : 'false');
                        });

                        const selectedButton = viewButtons.find((button) => button.dataset.medicalView === view);
                        const currentLabel = label || selectedButton?.dataset.medicalLabel || 'Summary';
                        if (viewTitle) viewTitle.textContent = currentLabel;
                        if (viewInput) viewInput.value = view;
                        if (viewDescription) {
                            viewDescription.textContent = isOverview
                                ? @json($medicalAssessmentOverviewDescription)
                                : isHistory
                                    ? 'Saved history for this medical record.'
                                    : `Full details recorded under ${currentLabel}.`;
                        }
                        if (editToggle) editToggle.hidden = isEditing || editControlsForView(view).length === 0;
                    };

                    const setModalOpen = (open) => {
                        if (!open) closeMedicalSelect();
                        modal.classList.toggle('is-open', open);
                        modal.setAttribute('aria-hidden', open ? 'false' : 'true');
                        document.body.classList.toggle('medical-information-modal-open', open);
                    };

                    viewButtons.forEach((button) => {
                        button.addEventListener('click', () => {
                            const view = button.dataset.medicalView;
                            if (!view) return;
                            setView(view, button.dataset.medicalLabel || '');
                        });
                    });

                    document.querySelectorAll('[data-medical-assessment-trigger]').forEach((trigger) => {
                        trigger.addEventListener('click', function () {
                            document.getElementById('profileActionsMenu')?.classList.remove('is-open');
                            document.getElementById('profileActionsToggle')?.setAttribute('aria-expanded', 'false');
                            document.getElementById('employeeProfileActions')?.classList.remove('is-open');
                            document.getElementById('employeeProfileActionsToggle')?.setAttribute('aria-expanded', 'false');
                            document.getElementById('employeeProfileActionsMenu')?.setAttribute('aria-hidden', 'true');
                            setEditing(false);
                            setView('overview');
                            setModalOpen(true);
                        });
                    });

                    modal.querySelector('[data-medical-assessment-close]')?.addEventListener('click', () => setModalOpen(false));
                    editToggle?.addEventListener('click', () => {
                        setEditing(true);
                        requestAnimationFrame(() => {
                            const control = editControlsForView(activeView).find((item) => !item.disabled);
                            (customSelects.get(control)?.trigger || control)?.focus({ preventScroll: true });
                        });
                    });
                    editCancel?.addEventListener('click', () => {
                        form.reset();
                        setEditing(false);
                    });
                    modal.addEventListener('click', (event) => {
                        if (event.target === modal) setModalOpen(false);
                    });
                    document.addEventListener('keydown', (event) => {
                        if (event.key === 'Escape' && openSelect) {
                            event.preventDefault();
                            closeMedicalSelect(true);
                            return;
                        }
                        if (event.key === 'Escape' && modal.classList.contains('is-open')) setModalOpen(false);
                    });

                    loadStudentAddressLocations();
                    if (modal.dataset.autoOpen === 'true') {
                        setModalOpen(true);
                        const invalidView = viewInput?.value || 'overview';
                        setView(invalidView);
                        setEditing(true);
                    }
                });
            </script>
        @endpush
    @endonce
@endif
