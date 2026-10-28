@if($canUpdateMedicalAssessment)
    @php
        $medicalAssessmentPersonType = $medicalAssessmentProfileTypeLabel ?? 'Student';
        $medicalInformationSections = collect($medicalAssessmentSnapshot ?? [])->map(function ($items, $name) {
            $name = in_array($name, ['Medical Certificate & Imaging', 'Medical Certificates & Imaging', 'Medical Documents'], true)
                ? 'Documents'
                : (string) $name;
            $normalizedName = strtolower((string) $name);
            $items = is_array($items) ? $items : [];
            $icon = match (true) {
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
                $overviewItems = collect($items)->only(['Medical certificate', 'Chest X-ray report', 'Other medical assessment file']);
                if ($overviewItems->isEmpty()) {
                    $overviewItems = collect($items)->only(['Medical certificate', 'Chest X-ray document', 'Health declaration', 'PWD ID proof']);
                }
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
        $medicalInformationEditMap = $medicalAssessmentIsEmployee ? [
            'Medical & Family History' => [
                'Past medical history' => ['key' => 'past_medical_history', 'type' => 'json'],
                'Other past medical history' => ['key' => 'past_medical_history_others', 'type' => 'textarea'],
                'Family history' => ['key' => 'family_history', 'type' => 'json'],
                'Other family history' => ['key' => 'family_history_others', 'type' => 'textarea'],
                'Disability' => ['key' => 'has_disability', 'type' => 'boolean'],
                'Disability type' => ['key' => 'disability_type', 'type' => 'text'],
            ],
            'Hospitalization & Surgery' => [
                'Previous hospitalization' => ['key' => 'previous_hospitalization', 'type' => 'boolean'],
                'Hospitalization details' => ['key' => 'previous_hospitalization_details', 'type' => 'textarea'],
                'Operation / surgery' => ['key' => 'operation_surgery', 'type' => 'boolean'],
                'Operation / surgery details' => ['key' => 'operation_surgery_details', 'type' => 'textarea'],
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
                'Height' => ['key' => 'height', 'type' => 'text'],
                'Weight' => ['key' => 'weight', 'type' => 'number'],
                'BMI' => ['key' => 'bmi', 'type' => 'number'],
                'Blood pressure' => ['key' => 'bp', 'type' => 'text'],
                'Heart rate' => ['key' => 'hr', 'type' => 'number'],
                'Respiratory rate' => ['key' => 'rr', 'type' => 'number'],
                'Temperature' => ['key' => 'temperature', 'type' => 'number'],
            ],
            'Physical Examination Findings' => [
                'Head' => ['key' => 'head_findings', 'type' => 'json'],
                'Eyes' => ['key' => 'eyes_findings', 'type' => 'json'],
                'Ears' => ['key' => 'ears_findings', 'type' => 'json'],
                'Throat' => ['key' => 'throat_findings', 'type' => 'json'],
                'Chest & lungs' => ['key' => 'chest_lungs_findings', 'type' => 'json'],
                'Chest X-ray' => ['key' => 'chest_xray_result', 'type' => 'textarea'],
                'Breast' => ['key' => 'breast_findings', 'type' => 'textarea'],
                'Heart murmur' => ['key' => 'heart_murmur', 'type' => 'text'],
                'Heart rhythm' => ['key' => 'heart_rhythm', 'type' => 'text'],
                'Abdomen' => ['key' => 'abdomen_findings', 'type' => 'textarea'],
                'Last menstruation' => ['key' => 'genito_urinary_date_lmp', 'type' => 'date'],
                'Extremities' => ['key' => 'extremities_findings', 'type' => 'textarea'],
                'Vertebral column' => ['key' => 'vertebral_column_findings', 'type' => 'textarea'],
                'Skin' => ['key' => 'skin_findings', 'type' => 'json'],
                'Scars' => ['key' => 'scars_findings', 'type' => 'textarea'],
            ],
            'Assessment & Follow-up' => [
                'Working impression' => ['key' => 'working_impression', 'type' => 'textarea'],
                'Fit status' => ['key' => 'fit_status', 'type' => 'text'],
                'For work-up' => ['key' => 'for_work_up', 'type' => 'textarea'],
                'Referred to' => ['key' => 'referred_to', 'type' => 'json'],
                'Other referral' => ['key' => 'referred_to_others', 'type' => 'textarea'],
                'Follow-up date' => ['key' => 'follow_up_on', 'type' => 'date'],
            ],
        ] : [
            'Medical History' => [
                'Known illness' => ['key' => 'has_illness', 'type' => 'yes-no'],
                'Medical conditions' => ['key' => 'medical_history', 'type' => 'json'],
                'Other illness' => ['key' => 'other_illness', 'type' => 'textarea'],
                'Blood type' => ['key' => 'blood_type', 'type' => 'text'],
                'Disability' => ['key' => 'has_disability', 'type' => 'yes-no'],
                'Disability type' => ['key' => 'disability_type', 'type' => 'text'],
            ],
            'Allergies' => [
                'No known allergies' => ['key' => 'no_allergies', 'type' => 'boolean'],
                'Food allergies' => ['key' => 'food_allergies', 'type' => 'textarea'],
                'Medicine allergies' => ['key' => 'medicine_allergies', 'type' => 'json'],
                'Other medicine allergies' => ['key' => 'other_med_allergies', 'type' => 'textarea'],
            ],
            'Lifestyle & Immunization' => [
                'Smoking' => ['key' => 'is_smoker', 'type' => 'yes-no'],
                'Alcohol use' => ['key' => 'is_drinker', 'type' => 'yes-no'],
                'COVID-19 vaccination' => ['key' => 'covid_vaccinated', 'type' => 'yes-no'],
                'Vaccine history' => ['key' => 'vaccine_history', 'type' => 'json'],
                'COVID-19 positive' => ['key' => 'covid_positive', 'type' => 'yes-no'],
                'COVID-19 positive date' => ['key' => 'covid_positive_date', 'type' => 'date'],
            ],
            'Vitals & Assessment' => [
                'Height' => ['key' => 'height', 'type' => 'text'],
                'Weight' => ['key' => 'weight', 'type' => 'number'],
                'Blood pressure' => ['key' => 'blood_pressure', 'type' => 'text'],
                'Pulse rate' => ['key' => 'pulse_rate', 'type' => 'number'],
                'Respiratory rate' => ['key' => 'respiratory_rate', 'type' => 'number'],
                'Temperature' => ['key' => 'temperature', 'type' => 'number'],
                'Assessment date' => ['key' => 'assessment_date', 'type' => 'date'],
                'Assessment remarks' => ['key' => 'med_assessment_remarks', 'type' => 'textarea'],
                'Medical condition remarks' => ['key' => 'medical_condition_remarks', 'type' => 'textarea'],
            ],
            'Documents' => [
                'Certificate doctor' => ['key' => 'doctor_name', 'type' => 'text'],
                'Certificate issued by' => ['key' => 'medical_certificate_issued_by', 'type' => 'text'],
                'Certificate date' => ['key' => 'med_cert_date', 'type' => 'date'],
                'Certificate issued date' => ['key' => 'medical_certificate_issued_at', 'type' => 'date'],
                'Certificate findings' => ['key' => 'med_cert_findings', 'type' => 'text'],
                'Certificate findings details' => ['key' => 'med_cert_findings_details', 'type' => 'textarea'],
                'Chest X-ray result' => ['key' => 'chest_xray_result_text', 'type' => 'textarea'],
                'Chest X-ray date' => ['key' => 'chest_xray_date', 'type' => 'date'],
                'Chest X-ray findings' => ['key' => 'xray_findings', 'type' => 'text'],
                'Chest X-ray findings details' => ['key' => 'xray_findings_details', 'type' => 'textarea'],
            ],
            'Referral' => [
                'Referral details' => ['key' => 'referral_details', 'type' => 'json'],
                'Encoded findings' => ['key' => 'encode_remarks', 'type' => 'textarea'],
            ],
        ];
        $formatMedicalEditValue = static function ($value, string $type): string {
            if (in_array($type, ['boolean', 'yes-no'], true)) {
                return is_bool($value) ? ($value ? 'Yes' : 'No') : trim((string) ($value ?? ''));
            }
            if ($value instanceof \DateTimeInterface) {
                return $value->format('Y-m-d');
            }
            if ($type === 'json') {
                return json_encode($value ?? [], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) ?: '[]';
            }

            return trim((string) ($value ?? ''));
        };
        if ($medicalAssessmentIsEmployee) {
            $medicalAssessmentDocuments = collect($employeeDocuments ?? [])
                ->filter(fn (array $document) => in_array($document['key'] ?? '', ['medical_certificate', 'chest_xray_document'], true))
                ->map(fn (array $document) => [
                    'label' => ($document['key'] ?? '') === 'medical_certificate' ? 'Medical Certificate' : 'Chest X-ray Report',
                    'name' => $document['name'] ?? 'Missing',
                    'uploaded' => (bool) ($document['uploaded'] ?? false),
                    'url' => $document['view_url'] ?? null,
                ])
                ->values();
        } else {
            $documentRouteName = $documentRouteName ?? (request()->routeIs('assistant.*') ? 'assistant.walkin.document' : 'walkin.document');
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
                    'label' => 'Other Medical Assessment File',
                    'path' => $profile->medical_assessment_upload ?? null,
                    'document' => 'medical_assessment_upload',
                ],
            ])->map(function (array $document) use ($profile, $documentRouteName): array {
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
            });
        }
        $medicalInformationErrorMessages = collect($errors->getMessages())
            ->filter(fn ($messages, $key) => \Illuminate\Support\Str::startsWith($key, 'medical_information.'))
            ->flatten()
            ->values();
        $medicalInformationSections = $medicalInformationSections->map(function (array $section) use ($medicalHistorySectionNames) {
            $isMedicalHistory = in_array($section['name'], $medicalHistorySectionNames, true);

            return array_merge($section, [
                'display_name' => $section['name'] === 'Medical History' ? 'General Medical History' : $section['name'],
                'view_id' => $isMedicalHistory ? 'medical-history' : $section['id'],
                'view_name' => $isMedicalHistory ? 'Medical History' : $section['name'],
                'view_icon' => $isMedicalHistory ? 'heart-pulse' : $section['icon'],
            ]);
        });
        $medicalHistorySections = $medicalInformationSections
            ->filter(fn (array $section) => in_array($section['name'], $medicalHistorySectionNames, true))
            ->values();
        $medicalInformationViews = collect();
        if ($medicalHistorySections->isNotEmpty()) {
            $medicalInformationViews->push([
                'id' => 'medical-history',
                'name' => 'Medical History',
                'icon' => 'heart-pulse',
                'sections' => $medicalHistorySections,
            ]);
        }
        $medicalInformationSections
            ->reject(fn (array $section) => in_array($section['name'], $medicalHistorySectionNames, true))
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
                #medicalInformationModal .medical-information-heading h2 { margin: 0; color: #fff; font-size: 19px; font-weight: 850; line-height: 1.2; }
                #medicalInformationModal .medical-information-heading p { margin: 3px 0 0; color: rgba(255,255,255,.92); font-size: 12px; line-height: 1.35; }
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
                    font-size: 12px;
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
                #medicalInformationModal .medical-information-content-head h3 { margin: 0; color: #f3f7fc; font-size: 16px; font-weight: 850; line-height: 1.2; }
                #medicalInformationModal .medical-information-content-head p { margin: 3px 0 0; color: #9aadc2; font-size: 12px; line-height: 1.3; }
                #medicalInformationModal .medical-information-edit-toggle { display: inline-flex; min-height: 32px; flex: 0 0 auto; align-items: center; gap: 7px; padding: 6px 10px; border: 1px solid #71839a; border-radius: 6px; background: transparent; color: #f1f5f9; font: inherit; font-size: 11px; font-weight: 750; cursor: pointer; }
                #medicalInformationModal .medical-information-edit-toggle svg { width: 15px; height: 15px; }
                #medicalInformationModal .medical-information-edit-toggle:hover,
                #medicalInformationModal .medical-information-edit-toggle:focus-visible { border-color: #facc15; color: #facc15; outline: none; }
                #medicalInformationModal .medical-information-edit-control { display: none; width: 100%; min-width: 0; padding: 6px 8px; border: 1px solid #334b64; border-radius: 5px; background: #091625; color: #f3f7fc; font: inherit; font-size: 11px; line-height: 1.35; }
                #medicalInformationModal textarea.medical-information-edit-control { min-height: 68px; resize: vertical; white-space: pre; }
                #medicalInformationModal select.medical-information-edit-control { min-height: 30px; }
                #medicalInformationModal .medical-information-edit-control:focus { border-color: #d1a72b; outline: 2px solid rgba(250,204,21,.13); }
                #medicalInformationModal.is-editing .medical-information-read-value { display: none; }
                #medicalInformationModal.is-editing .medical-information-edit-control { display: block; }
                #medicalInformationModal .medical-information-field-error { display: block; margin-top: 4px; color: #fca5a5; font-size: 10px; font-weight: 700; }
                #medicalInformationModal .medical-information-errors { margin-bottom: 8px; padding: 8px 10px; border: 1px solid #a33a47; border-radius: 6px; background: #321922; color: #fecaca; font-size: 11px; }
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
                #medicalInformationModal .medical-document-file-list,
                #medicalInformationModal .medical-document-information { display: grid; min-width: 0; align-content: start; gap: 9px; }
                #medicalInformationModal .medical-document-file,
                #medicalInformationModal .medical-document-info-card { min-width: 0; overflow: hidden; border: 1px solid #1c3349; border-radius: 8px; background: linear-gradient(145deg, #0e1d2d, #0b1928); }
                #medicalInformationModal .medical-document-file-head,
                #medicalInformationModal .medical-document-info-head { display: flex; min-height: 38px; align-items: center; gap: 8px; padding: 7px 10px; border-bottom: 1px solid #1c3349; background: #14283b; color: #f4f7fb; font-size: 12px; font-weight: 800; }
                #medicalInformationModal .medical-document-file-head svg { width: 18px; height: 18px; flex: 0 0 18px; }
                #medicalInformationModal .medical-document-preview { display: grid; min-height: 130px; place-items: center; overflow: hidden; margin: 9px; border: 1px solid #263c53; border-radius: 6px; background: #081321; color: #9aadc2; }
                #medicalInformationModal .medical-document-preview img { display: block; width: 100%; max-height: 190px; object-fit: contain; }
                #medicalInformationModal .medical-document-preview iframe { display: block; width: 100%; height: 190px; border: 0; background: #fff; }
                #medicalInformationModal .medical-document-preview a { padding: 12px; color: #facc15; font-size: 11px; font-weight: 750; text-align: center; }
                #medicalInformationModal .medical-document-file-name { display: flex; min-width: 0; align-items: center; justify-content: space-between; gap: 8px; padding: 0 10px 10px; color: #b5c4d5; font-size: 10px; }
                #medicalInformationModal .medical-document-file-name span { min-width: 0; overflow-wrap: anywhere; }
                #medicalInformationModal .medical-document-file-name a { flex: 0 0 auto; color: #facc15; font-weight: 750; }
                #medicalInformationModal .medical-document-info-card .medical-information-rows { grid-template-columns: minmax(0, 1fr); }
                #medicalInformationModal .medical-document-info-card .medical-information-row { display: grid; grid-template-columns: minmax(100px, .8fr) minmax(0, 1.2fr); }
                #medicalInformationModal .medical-document-info-empty { padding: 12px; color: #9aadc2; font-size: 11px; line-height: 1.45; }
                #medicalInformationModal .medical-information-section-head { display: flex; min-height: 40px; align-items: center; gap: 9px; padding: 7px 11px; border-bottom: 1px solid #1c3349; background: #14283b; }
                #medicalInformationModal .medical-information-section-head h4 { min-width: 0; flex: 1 1 auto; margin: 0; color: #f4f7fb; font-size: 13px; font-weight: 800; line-height: 1.25; }
                #medicalInformationModal .medical-information-category-card[hidden],
                #medicalInformationModal [data-medical-panel][hidden] { display: none !important; }
                #medicalInformationModal .medical-information-category-head { display: flex; min-height: 40px; align-items: center; gap: 9px; padding: 6px 11px; background: #14283b; }
                #medicalInformationModal .medical-information-category-icon { display: grid; width: 26px; height: 26px; flex: 0 0 26px; place-items: center; color: #f5f8fc; }
                #medicalInformationModal .medical-information-category-icon svg { width: 22px; height: 22px; stroke-width: 2.4; }
                #medicalInformationModal .medical-information-category-head h4 { min-width: 0; flex: 1 1 auto; margin: 0; color: #f4f7fb; font-size: 13px; font-weight: 800; line-height: 1.25; }
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
                    font-size: 10px;
                    font-weight: 650;
                    cursor: pointer;
                }
                #medicalInformationModal .medical-information-view-details:hover,
                #medicalInformationModal .medical-information-view-details:focus-visible { border-color: #e8b923; color: #facc15; outline: none; }
                #medicalInformationModal .medical-information-view-details svg { width: 13px; height: 13px; }
                #medicalInformationModal .medical-information-rows { display: grid; min-width: 0; grid-template-columns: repeat(2, minmax(0, 1fr)); column-gap: 22px; margin: 0; padding: 3px 11px 7px; }
                #medicalInformationModal .medical-information-row-column { display: grid; min-width: 0; align-content: start; margin: 0; }
                #medicalInformationModal .medical-information-row { display: flex; min-width: 0; align-items: baseline; justify-content: space-between; gap: 12px; padding: 4px 0; border-bottom: 1px solid #1b2e42; }
                #medicalInformationModal .medical-information-row:last-child { border-bottom: 0; }
                #medicalInformationModal .medical-information-row dt { flex: 1 1 55%; min-width: 0; margin: 0; padding: 0; color: #9eb3ca; font-size: 11px; line-height: 1.3; overflow-wrap: anywhere; }
                #medicalInformationModal .medical-information-row dd { flex: 0 1 45%; min-width: 0; margin: 0; padding: 0; color: #e7eef6; font-size: 11px; line-height: 1.3; text-align: right; overflow-wrap: anywhere; white-space: pre-wrap; }
                #medicalInformationModal .medical-information-history-panel { padding: 11px 12px; border: 1px solid #1c3349; border-radius: 8px; background: #0d1b2b; }
                #medicalInformationModal .medical-information-history-list { display: grid; gap: 8px; }
                #medicalInformationModal .medical-information-history-entry { padding: 10px 11px; border: 1px solid #1d344a; border-radius: 7px; background: #102033; }
                #medicalInformationModal .medical-information-entry-head { display: flex; flex-wrap: wrap; align-items: baseline; justify-content: space-between; gap: 5px 12px; }
                #medicalInformationModal .medical-information-entry-head strong { color: #f7fafc; font-size: 13px; }
                #medicalInformationModal .medical-information-entry-head time,
                #medicalInformationModal .medical-information-history-entry small { color: #9aadc2; font-size: 10px; }
                #medicalInformationModal .medical-information-history-entry p { display: grid; gap: 2px; margin: 8px 0 0; color: #e2eaf3; font-size: 12px; line-height: 1.45; white-space: pre-wrap; overflow-wrap: anywhere; }
                #medicalInformationModal .medical-information-history-entry p span { color: #a6b9ce; font-size: 10px; font-weight: 850; text-transform: uppercase; }
                #medicalInformationModal .medical-information-history-entry small { display: block; margin-top: 8px; }
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
                html[data-theme="light"] #medicalInformationModal { background: rgba(15, 23, 42, .58); }
                html[data-theme="light"] #medicalInformationModal .medical-information-card {
                    border-color: #d5dee8;
                    background: #fff;
                    color: #172033;
                    box-shadow: 0 24px 60px rgba(15, 23, 42, .28);
                }
                html[data-theme="light"] #medicalInformationModal .medical-information-sidebar,
                html[data-theme="light"] #medicalInformationModal .medical-information-footer { border-color: #e0e6ed; background: #f7f9fc; }
                html[data-theme="light"] #medicalInformationModal .medical-information-nav button { border-color: #dce3eb; background: #fff; }
                html[data-theme="light"] #medicalInformationModal .medical-information-nav button { color: #243247; }
                html[data-theme="light"] #medicalInformationModal .medical-information-nav button:hover,
                html[data-theme="light"] #medicalInformationModal .medical-information-nav button:focus-visible { border-color: #b9c7d6; background: #edf2f7; }
                html[data-theme="light"] #medicalInformationModal .medical-information-nav button.is-active { border-color: #8c1220; border-left-color: #ed1b2f; background: linear-gradient(100deg, #64111e, #391220); color: #fff; }
                html[data-theme="light"] #medicalInformationModal .medical-information-main { background: #fff; }
                html[data-theme="light"] #medicalInformationModal .medical-information-content-head h3,
                html[data-theme="light"] #medicalInformationModal .medical-information-category-head h4,
                html[data-theme="light"] #medicalInformationModal .medical-information-section-head h4 { color: #1f2937; }
                html[data-theme="light"] #medicalInformationModal .medical-information-content-head p { color: #64748b; }
                html[data-theme="light"] #medicalInformationModal .medical-information-edit-toggle { border-color: #b8c4d1; color: #334155; }
                html[data-theme="light"] #medicalInformationModal .medical-information-edit-control { border-color: #cbd5e1; background: #fff; color: #172033; }
                html[data-theme="light"] #medicalInformationModal .medical-information-errors { border-color: #f1a6ad; background: #fff1f2; color: #9f1239; }
                html[data-theme="light"] #medicalInformationModal .medical-information-category-card,
                html[data-theme="light"] #medicalInformationModal .medical-information-history-panel { border-color: #dbe3ec; background: #fff; }
                html[data-theme="light"] #medicalInformationModal .medical-document-file,
                html[data-theme="light"] #medicalInformationModal .medical-document-info-card { border-color: #dbe3ec; background: #fff; }
                html[data-theme="light"] #medicalInformationModal .medical-document-file-head,
                html[data-theme="light"] #medicalInformationModal .medical-document-info-head { border-color: #e5eaf0; background: #f1f5f9; color: #1f2937; }
                html[data-theme="light"] #medicalInformationModal .medical-document-preview { border-color: #dbe3ec; background: #f8fafc; color: #64748b; }
                html[data-theme="light"] #medicalInformationModal .medical-document-file-name,
                html[data-theme="light"] #medicalInformationModal .medical-document-info-empty { color: #64748b; }
                html[data-theme="light"] #medicalInformationModal .medical-document-file-name a,
                html[data-theme="light"] #medicalInformationModal .medical-document-preview a { color: #8c1220; }
                html[data-theme="light"] #medicalInformationModal .medical-information-category-detail { border-color: #dbe3ec; }
                html[data-theme="light"] #medicalInformationModal .medical-information-category-head,
                html[data-theme="light"] #medicalInformationModal .medical-information-section-head { border-color: #e5eaf0; background: #f1f5f9; }
                html[data-theme="light"] #medicalInformationModal .medical-information-category-icon { color: #26364a; }
                html[data-theme="light"] #medicalInformationModal .medical-information-view-details { border-color: #d7e0e9; background: #eef2f6; color: #334155; }
                html[data-theme="light"] #medicalInformationModal .medical-information-view-details:hover,
                html[data-theme="light"] #medicalInformationModal .medical-information-view-details:focus-visible { border-color: #d6ad27; color: #7f1d1d; }
                html[data-theme="light"] #medicalInformationModal .medical-information-row { border-color: #e5eaf0; }
                html[data-theme="light"] #medicalInformationModal .medical-information-row dt { color: #617187; }
                html[data-theme="light"] #medicalInformationModal .medical-information-row dd { color: #1f2937; }
                html[data-theme="light"] #medicalInformationModal .medical-information-history-entry,
                html[data-theme="light"] #medicalInformationModal .medical-information-empty { border-color: #dfe6ee; background: #f8fafc; }
                html[data-theme="light"] #medicalInformationModal .medical-information-entry-head strong { color: #253247; }
                html[data-theme="light"] #medicalInformationModal .medical-information-entry-head time,
                html[data-theme="light"] #medicalInformationModal .medical-information-history-entry small,
                html[data-theme="light"] #medicalInformationModal .medical-information-history-entry p span { color: #64748b; }
                html[data-theme="light"] #medicalInformationModal .medical-information-history-entry p { color: #334155; }
                html[data-theme="light"] #medicalInformationModal .medical-information-empty { color: #526176; }
                html[data-theme="light"] #medicalInformationModal .medical-information-footer button:not(.medical-information-update) { border-color: #cbd5e1; color: #334155; }
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
                    #medicalInformationModal .medical-information-heading h2 { font-size: 16px; }
                    #medicalInformationModal .medical-information-heading p { max-width: 55vw; font-size: 10px; }
                    #medicalInformationModal .medical-information-close { width: 32px; height: 32px; flex-basis: 32px; }
                    #medicalInformationModal .medical-information-body { grid-template-columns: minmax(0, 1fr); grid-template-rows: auto minmax(0, 1fr); }
                    #medicalInformationModal .medical-information-sidebar { gap: 7px; padding: 7px 8px 6px; border-right: 0; border-bottom: 1px solid #1a2c41; }
                    #medicalInformationModal .medical-information-nav { display: flex; gap: 5px; overflow-x: auto; overflow-y: hidden; padding-bottom: 2px; }
                    #medicalInformationModal .medical-information-nav button { min-height: 32px; flex: 0 0 auto; padding: 5px 8px; font-size: 10px; }
                    #medicalInformationModal .medical-information-nav .medical-information-nav-label { white-space: nowrap; }
                    #medicalInformationModal .medical-information-nav button svg { width: 15px; height: 15px; flex-basis: 15px; }
                    #medicalInformationModal .medical-information-main { padding: 9px 9px 7px; }
                    #medicalInformationModal .medical-information-category-grid { grid-template-columns: minmax(0, 1fr); gap: 7px; }
                    #medicalInformationModal .medical-information-rows { grid-template-columns: minmax(0, 1fr); }
                    #medicalInformationModal .medical-information-documents-layout { grid-template-columns: minmax(0, 1fr); }
                    #medicalInformationModal .medical-information-category-head h4 { font-size: 12px; }
                    #medicalInformationModal .medical-information-footer { justify-content: space-between; padding: 6px 8px; }
                    #medicalInformationModal .medical-information-footer button { min-height: 35px; padding: 7px 9px; font-size: 11px; }
                }
                @media (max-width: 420px) {
                    #medicalInformationModal .medical-information-footer { display: grid; grid-template-columns: minmax(0, 1fr); }
                    #medicalInformationModal .medical-information-footer button { width: 100%; }
                }
                @media (prefers-reduced-motion: reduce) {
                    #medicalInformationModal *, #medicalInformationModal *::before, #medicalInformationModal *::after { scroll-behavior: auto !important; transition-duration: .01ms !important; }
                }
            </style>
        @endpush
    @endonce

    <div class="medical-information-overlay" id="medicalInformationModal" aria-hidden="true" data-auto-open="{{ $medicalInformationErrorMessages->isNotEmpty() ? 'true' : 'false' }}">
        <div class="correction-card medical-information-card" role="dialog" aria-modal="true" aria-labelledby="medicalInformationTitle">
            <header class="medical-information-header">
                <div class="medical-information-heading">
                    <span class="medical-information-heading-icon"><x-outline-icon name="medical-clearance-issued" /></span>
                    <div>
                        <h2 id="medicalInformationTitle">Medical Information</h2>
                        <p>Review and update the {{ strtolower($medicalAssessmentPersonType) }}'s medical record.</p>
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
                        <button type="button" class="is-active" role="tab" aria-selected="true" data-medical-view="overview" data-medical-label="Health Overview">
                            <x-outline-icon name="squares-2x2" /><span class="medical-information-nav-label">Overview</span>
                        </button>
                        @foreach($medicalInformationViews as $medicalView)
                            <button type="button" role="tab" aria-selected="false" data-medical-view="{{ $medicalView['id'] }}" data-medical-label="{{ $medicalView['name'] }}">
                                <x-outline-icon name="{{ $medicalView['icon'] }}" /><span class="medical-information-nav-label">{{ $medicalView['name'] }}</span>
                            </button>
                        @endforeach
                        <button type="button" role="tab" aria-selected="false" data-medical-view="assessment-history" data-medical-label="Assessment History">
                            <x-outline-icon name="clipboard-document-list" /><span class="medical-information-nav-label">Assessment History</span>
                        </button>
                    </nav>
                </aside>

                <main class="medical-information-main">
                    <div class="medical-information-content-head">
                        <div class="medical-information-content-copy">
                            <h3 data-medical-view-title>Health Overview</h3>
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
                                                    <div class="medical-information-row"><dt>{{ $medicalLabel }}</dt><dd>{{ $medicalValue }}</dd></div>
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
                                                'Certificate Information' => [
                                                    'Certificate doctor',
                                                    'Certificate issued by',
                                                    'Certificate date',
                                                    'Certificate issued date',
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
                                        <div class="medical-information-documents-layout" aria-label="Uploaded medical documents and information">
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
                                                        @if($medicalDocument['uploaded'] && filled($medicalDocument['url']))
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
                                                        @else
                                                            <div class="medical-document-preview">No file uploaded</div>
                                                        @endif
                                                        <div class="medical-document-file-name">
                                                            <span>{{ $medicalDocument['name'] }}</span>
                                                            @if($medicalDocument['uploaded'] && filled($medicalDocument['url']))
                                                                <a href="{{ $medicalDocument['url'] }}" target="_blank" rel="noopener">Open</a>
                                                            @endif
                                                        </div>
                                                    </article>
                                                @endforeach
                                            </div>
                                            <div class="medical-document-information" aria-label="Medical document information">
                                                @if($medicalAssessmentIsEmployee)
                                                    <div class="medical-document-info-card">
                                                        <div class="medical-document-info-head">Document Information</div>
                                                        <div class="medical-document-info-empty">Certificate and X-ray details are not part of this employee health profile.</div>
                                                    </div>
                                                @else
                                                    @foreach($medicalDocumentInfoGroups as $medicalInfoTitle => $medicalInfoLabels)
                                                        <article class="medical-document-info-card">
                                                            <div class="medical-document-info-head">{{ $medicalInfoTitle }}</div>
                                                            <div class="medical-information-rows">
                                                                @foreach($medicalInfoLabels as $medicalLabel)
                                                                    @php
                                                                        $medicalValue = $medicalDocumentItems->get($medicalLabel, 'Not recorded');
                                                                        $medicalEditField = $medicalInformationEditMap['Documents'][$medicalLabel] ?? null;
                                                                        $medicalEditKey = $medicalEditField['key'] ?? null;
                                                                        $medicalEditRawValue = $medicalEditKey ? data_get($medicalAssessmentEditableValues ?? [], $medicalEditKey) : null;
                                                                        $medicalEditValue = $medicalEditField ? $formatMedicalEditValue($medicalEditRawValue, $medicalEditField['type']) : '';
                                                                        $medicalOldValue = $medicalEditKey ? old('medical_information.' . $medicalEditKey, $medicalEditValue) : '';
                                                                    @endphp
                                                                    <div class="medical-information-row">
                                                                        <dt>{{ $medicalLabel }}</dt>
                                                                        <dd>
                                                                            <span class="medical-information-read-value">{{ $medicalValue }}</span>
                                                                            @if($medicalEditField)
                                                                                @if(in_array($medicalEditField['type'], ['boolean', 'yes-no'], true))
                                                                                    <select class="medical-information-edit-control" data-medical-edit-control name="medical_information[{{ $medicalEditKey }}]" aria-label="Edit {{ $medicalLabel }}" disabled>
                                                                                        <option value="" {{ $medicalOldValue === '' ? 'selected' : '' }}>Not recorded</option>
                                                                                        <option value="Yes" {{ (string) $medicalOldValue === 'Yes' ? 'selected' : '' }}>Yes</option>
                                                                                        <option value="No" {{ (string) $medicalOldValue === 'No' ? 'selected' : '' }}>No</option>
                                                                                    </select>
                                                                                @elseif(in_array($medicalEditField['type'], ['json', 'textarea'], true))
                                                                                    <textarea class="medical-information-edit-control" data-medical-edit-control name="medical_information[{{ $medicalEditKey }}]" aria-label="Edit {{ $medicalLabel }}" disabled>{{ $medicalOldValue }}</textarea>
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
                                                @endif
                                            </div>
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
                                                        @endphp
                                                        <div class="medical-information-row">
                                                            <dt>{{ $medicalLabel }}</dt>
                                                            <dd>
                                                                <span class="medical-information-read-value">{{ $medicalValue }}</span>
                                                                @if($medicalEditField)
                                                                    @if(in_array($medicalEditField['type'], ['boolean', 'yes-no'], true))
                                                                        <select class="medical-information-edit-control" data-medical-edit-control name="medical_information[{{ $medicalEditKey }}]" aria-label="Edit {{ $medicalLabel }}" disabled>
                                                                            <option value="" {{ $medicalOldValue === '' ? 'selected' : '' }}>Not recorded</option>
                                                                            <option value="Yes" {{ (string) $medicalOldValue === 'Yes' ? 'selected' : '' }}>Yes</option>
                                                                            <option value="No" {{ (string) $medicalOldValue === 'No' ? 'selected' : '' }}>No</option>
                                                                        </select>
                                                                    @elseif($medicalEditField['type'] === 'json' || $medicalEditField['type'] === 'textarea')
                                                                        <textarea class="medical-information-edit-control" data-medical-edit-control name="medical_information[{{ $medicalEditKey }}]" aria-label="Edit {{ $medicalLabel }}" disabled>{{ $medicalOldValue }}</textarea>
                                                                    @else
                                                                        <input class="medical-information-edit-control" data-medical-edit-control type="{{ $medicalEditField['type'] }}" name="medical_information[{{ $medicalEditKey }}]" value="{{ $medicalOldValue }}" aria-label="Edit {{ $medicalLabel }}" disabled>
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

                        <section class="medical-information-history-panel" data-medical-panel="assessment-history" aria-label="Assessment history" hidden>
                            <div class="medical-information-history-list">
                                @forelse($medicalAssessmentHistory as $assessmentEntry)
                                    <article class="medical-information-history-entry">
                                        <div class="medical-information-entry-head">
                                            <strong>{{ $assessmentEntry->condition_or_illness ?: 'Assessment update' }}</strong>
                                            <time>{{ optional($assessmentEntry->created_at)->format('M d, Y h:i A') }}</time>
                                        </div>
                                        @if(filled($assessmentEntry->findings))<p><span>Findings</span>{{ $assessmentEntry->findings }}</p>@endif
                                        @if(filled($assessmentEntry->remarks))<p><span>Remarks</span>{{ $assessmentEntry->remarks }}</p>@endif
                                        @if(filled($assessmentEntry->reason))<p><span>Reason for update</span>{{ $assessmentEntry->reason }}</p>@endif
                                        <small>Recorded by {{ $assessmentEntry->recordedBy?->name ?? 'Clinic staff' }}</small>
                                    </article>
                                @empty
                                    <div class="medical-information-empty">No medical assessment updates have been added.</div>
                                @endforelse
                            </div>
                        </section>

                    </div>
                </main>
            </div>

            <footer class="medical-information-footer">
                <button type="button" data-medical-history-button data-medical-view="assessment-history" data-medical-label="Assessment History">
                    <x-outline-icon name="arrow-path" /><span>View Assessment History</span>
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

                    const setEditing = (editing) => {
                        isEditing = editing;
                        modal.classList.toggle('is-editing', editing);
                        editControls.forEach((control) => { control.disabled = !editing; });
                        if (editToggle) editToggle.hidden = editing || !editControlsForView(activeView).length;
                        if (editCancel) editCancel.hidden = !editing;
                        if (editSubmit) editSubmit.hidden = !editing;
                        if (historyButton) historyButton.hidden = editing;
                    };

                    const editControlsForView = (view) => {
                        if (view === 'overview' || view === 'assessment-history') return [];
                        return [...modal.querySelectorAll(`[data-medical-panel="${CSS.escape(view)}"] [data-medical-edit-control]`)];
                    };

                    const setView = (view, label = '') => {
                        activeView = view;
                        const isOverview = view === 'overview';
                        const isHistory = view === 'assessment-history';

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
                        const currentLabel = label || selectedButton?.dataset.medicalLabel || 'Health Overview';
                        if (viewTitle) viewTitle.textContent = currentLabel;
                        if (viewInput) viewInput.value = view;
                        if (viewDescription) {
                            viewDescription.textContent = isOverview
                                ? @json($medicalAssessmentOverviewDescription)
                                : isHistory
                                    ? 'Saved assessment history for this medical record.'
                                    : `Full details recorded under ${currentLabel}.`;
                        }
                        if (editToggle) editToggle.hidden = isEditing || editControlsForView(view).length === 0;
                    };

                    const setModalOpen = (open) => {
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

                    document.querySelector('[data-medical-assessment-trigger]')?.addEventListener('click', function () {
                        document.getElementById('profileActionsMenu')?.classList.remove('is-open');
                        document.getElementById('profileActionsToggle')?.setAttribute('aria-expanded', 'false');
                        document.getElementById('employeeProfileActions')?.classList.remove('is-open');
                        document.getElementById('employeeProfileActionsToggle')?.setAttribute('aria-expanded', 'false');
                        setEditing(false);
                        setView('overview');
                        setModalOpen(true);
                    });

                    modal.querySelector('[data-medical-assessment-close]')?.addEventListener('click', () => setModalOpen(false));
                    editToggle?.addEventListener('click', () => {
                        setEditing(true);
                        requestAnimationFrame(() => {
                            editControlsForView(activeView).find((control) => !control.disabled)?.focus({ preventScroll: true });
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
                        if (event.key === 'Escape' && modal.classList.contains('is-open')) setModalOpen(false);
                    });

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
