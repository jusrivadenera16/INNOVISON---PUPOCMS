@php
    $healthSubmissionItems = collect($healthFormSubmissions ?? [])->values();
    $latestHealthSubmission = $healthSubmissionItems->first();
    $oldestHealthSubmission = $healthSubmissionItems->sortBy('id')->first();
    $historyHealthSubmissionItems = $healthSubmissionItems->isNotEmpty()
        ? $healthSubmissionItems
        : ($healthFormSubmitted ? collect([null]) : collect());

    $healthRecordDate = function ($value, string $fallback = '-'): string {
        if (!$value) {
            return $fallback;
        }

        try {
            return \Carbon\Carbon::parse($value)->format('M d, Y g:i A');
        } catch (\Throwable $exception) {
            return $fallback;
        }
    };
    $healthFormDisplayId = function ($submission) use ($healthProfileRecord): string {
        $date = optional($submission)->submitted_at
            ?: optional($submission)->requested_at
            ?: optional($submission)->created_at
            ?: optional($healthProfileRecord)->created_at
            ?: now();
        try {
            $year = \Carbon\Carbon::parse($date)->format('Y');
        } catch (\Throwable $exception) {
            $year = now()->format('Y');
        }
        $numericId = (int) (optional($submission)->id ?: optional($healthProfileRecord)->id ?: 0);

        return 'HF-' . $year . '-' . str_pad((string) $numericId, 6, '0', STR_PAD_LEFT);
    };
    $healthSubmissionStatus = function ($submission) use ($healthFormSubmitted, $isIssuedStatus): array {
        $statusKey = strtolower(trim((string) optional($submission)->status));
        if ($statusKey === '' || ($isIssuedStatus && $statusKey === 'submitted')) {
            $statusKey = !$healthFormSubmitted ? 'requested' : ($isIssuedStatus ? 'approved' : 'submitted');
        }

        return match ($statusKey) {
            'requested' => ['Requested', 'is-requested'],
            'approved' => ['Approved', 'is-approved'],
            'needs_correction' => ['Needs Correction', 'is-correction'],
            default => ['Under Review', 'is-review'],
        };
    };
    $healthSubmissionSnapshotProfile = function ($submission): array {
        if (!$submission) {
            return [];
        }

        try {
            $profile = $submission->snapshotProfile();
            return is_array($profile) ? $profile : [];
        } catch (\Throwable $exception) {
            return [];
        }
    };

    $currentUserRoles = strtolower(trim(implode(' ', array_filter([
        (string) ($user->user_role ?? ''),
        (string) ($user->user_type ?? ''),
        (string) ($user->idp_role ?? ''),
    ]))));
    $resolvedStudentNumber = trim((string) $recordStudentNumber);
    $resolvedAdmissionReference = trim((string) $recordReferenceNumber);
    if ($resolvedAdmissionReference === '-') {
        $resolvedAdmissionReference = '';
    }
    $resolvedEmployeeNumber = trim((string) (
        optional($healthProfileRecord)->employee_number
        ?: ($accountProfileData['employee_number'] ?? '')
        ?: ($user->employee_number ?? '')
    ));
    $isEmployeeHealthRecord = (bool) $usesEmployeeHealthForm;
    $isStudentHealthRecord = !$isEmployeeHealthRecord && $resolvedStudentNumber !== '';
    $isApplicantHealthRecord = !$isEmployeeHealthRecord
        && !$isStudentHealthRecord
        && ($resolvedAdmissionReference !== '' || str_contains($currentUserRoles, 'applicant'));
    $isApplicantHealthWorkflow = $isApplicantHealthRecord;
    $resolvedHealthFormType = match (true) {
        $isEmployeeHealthRecord => 'Employee Health Form',
        $isStudentHealthRecord => 'Student Health Form',
        default => 'Applicant Health Form',
    };
    $healthSubmissionFormType = function ($submission) use ($healthSubmissionSnapshotProfile, $resolvedHealthFormType, $isEmployeeHealthRecord, $isApplicantHealthRecord): string {
        if ($isEmployeeHealthRecord) {
            return 'Employee Health Form';
        }

        $snapshot = $healthSubmissionSnapshotProfile($submission);
        $category = strtolower(trim((string) (optional($submission)->category ?: ($snapshot['health_form_category'] ?? ''))));
        $referenceNumber = strtoupper(trim((string) ($snapshot['reference_number'] ?? '')));
        $studentNumber = strtoupper(trim((string) ($snapshot['student_number'] ?? '')));
        $looksLikeApplicantReference = (bool) preg_match('/^\d{4}-\d{4}-\d{4}/', $referenceNumber)
            || (bool) preg_match('/^\d{4}-[A-Z]+-\d+/', $referenceNumber);

        if (str_contains($category, 'ojt') || str_contains($category, 'on-the-job') || str_contains($category, 'student')) {
            return 'Student Health Form';
        }

        if (
            str_contains($category, 'applicant')
            || $looksLikeApplicantReference
            || ($category === 'general' && ($studentNumber === '' || $studentNumber === $referenceNumber))
            || (!$submission && $isApplicantHealthRecord)
        ) {
            return 'Applicant Health Form';
        }

        return $resolvedHealthFormType;
    };
    $latestStatus = $healthSubmissionStatus($latestHealthSubmission);
    $latestStatusKey = strtolower(trim((string) optional($latestHealthSubmission)->status));
    if ($latestStatusKey === '' || ($isIssuedStatus && $latestStatusKey === 'submitted')) {
        $latestStatusKey = $isIssuedStatus ? 'approved' : ($healthFormSubmitted ? 'submitted' : 'requested');
    }
    $latestIsRequested = $latestStatusKey === 'requested';
    $latestIsApproved = $latestStatusKey === 'approved' || $isIssuedStatus;
    $latestIsSubmitted = in_array($latestStatusKey, ['submitted', 'approved', 'needs_correction'], true)
        || (!$latestHealthSubmission && $healthFormSubmitted);
    $latestSubmittedSource = optional($latestHealthSubmission)->submitted_at
        ?: (!$latestIsRequested ? $recordSubmittedSource : null);
    $latestApprovedSource = optional($latestHealthSubmission)->approved_at
        ?: ($latestIsApproved ? $recordApprovedSource : null);
    $latestUpdatedSource = optional($latestHealthSubmission)->updated_at
        ?: optional($latestHealthSubmission)->requested_at
        ?: $latestSubmittedSource
        ?: optional($healthProfileRecord)->updated_at;
    $latestFormId = $healthFormDisplayId($latestHealthSubmission);
    $latestCategory = trim((string) optional($latestHealthSubmission)->category);
    $latestSchoolYear = trim((string) (optional($latestHealthSubmission)->school_year ?: $recordAcademicYear));
    $latestFormType = $healthSubmissionFormType($latestHealthSubmission);
    if (!$isEmployeeHealthRecord && $latestSchoolYear !== '') {
        $latestFormType .= ' (' . $latestSchoolYear . ')';
    }
    $latestIdentifierLabel = match (true) {
        $isEmployeeHealthRecord => 'Employee Number',
        $isStudentHealthRecord => 'Student Number',
        default => 'Admission Reference',
    };
    $latestIdentifierValue = match (true) {
        $isEmployeeHealthRecord => $resolvedEmployeeNumber,
        $isStudentHealthRecord => $resolvedStudentNumber,
        default => $resolvedAdmissionReference,
    };
    if ($latestIdentifierValue === '') {
        $latestIdentifierValue = '-';
    }
    $latestPdfAvailable = filled(optional($latestHealthSubmission)->pdf_path)
        || (!$latestHealthSubmission && $healthFormSubmitted);
    $latestPdfUrl = filled(optional($latestHealthSubmission)->pdf_path)
        ? route('student.health_form.submission', $latestHealthSubmission->id)
        : route('student.health_record.document', ['document' => 'health_form']);
    $newStudentHealthFormRoute = $isEmployeeHealthRecord
        ? $healthFormRoute
        : route('health.form.student');

    $encodeAssessmentComplete = $isApplicantHealthWorkflow && $healthProfileRecord && (
        filled(optional($healthProfileRecord)->assessment_date)
        || filled(optional($healthProfileRecord)->physical_assessment_status)
        || filled(optional($healthProfileRecord)->review_started_at)
    );
    $finalReviewComplete = $latestIsApproved;
    $finalReviewActive = $latestIsSubmitted
        && !$finalReviewComplete
        && (!$isApplicantHealthWorkflow || $encodeAssessmentComplete);

    $healthRecordDocuments = [
        [
            'key' => 'student_photo',
            'title' => $usesEmployeeHealthForm ? 'Employee Photo' : '2x2 Student Photo',
            'meta' => 'Image Upload',
            'path' => optional($healthProfileRecord)->student_photo,
            'is_image' => true,
        ],
        [
            'key' => 'health_declaration',
            'title' => 'Health Declaration and Consent Form',
            'meta' => 'PDF or Image Upload',
            'path' => optional($healthProfileRecord)->health_declaration,
            'is_image' => false,
        ],
        [
            'key' => 'medical_certificate',
            'title' => 'Medical Certificate',
            'meta' => 'PDF or Image Upload',
            'path' => optional($healthProfileRecord)->medical_certificate,
            'is_image' => false,
        ],
        [
            'key' => 'chest_xray_result',
            'title' => 'Chest X-ray Result',
            'meta' => 'PDF or Image Upload',
            'path' => $usesEmployeeHealthForm
                ? optional($healthProfileRecord)->chest_xray_document
                : optional($healthProfileRecord)->chest_xray_result,
            'is_image' => false,
        ],
        [
            'key' => 'pwd_id_proof',
            'title' => 'PWD ID Proof',
            'meta' => 'PDF Upload',
            'path' => optional($healthProfileRecord)->pwd_id_proof,
            'is_image' => false,
        ],
    ];
    $visibleHealthRecordDocuments = collect($healthRecordDocuments)
        ->filter(fn ($document) => filled($document['path']))
        ->map(function ($document) {
            $extension = strtolower(pathinfo((string) $document['path'], PATHINFO_EXTENSION));
            $document['is_image'] = $document['is_image'] || in_array($extension, ['jpg', 'jpeg', 'png', 'webp'], true);

            return $document;
        })
        ->values();
    $healthSubmissionDocumentBlueprints = collect($healthRecordDocuments)->map(fn ($document) => [
        'key' => $document['key'],
        'title' => $document['title'],
        'meta' => $document['meta'],
        'is_image' => $document['is_image'],
    ])->values();
    $healthSubmissionDocumentGroups = $historyHealthSubmissionItems
        ->filter(function ($submission) use ($healthFormSubmitted) {
            $statusKey = strtolower(trim((string) optional($submission)->status));
            return !$submission || ($healthFormSubmitted && $statusKey !== 'requested');
        })
        ->values()
        ->map(function ($submission, $index) use ($healthSubmissionSnapshotProfile, $healthSubmissionDocumentBlueprints, $healthFormDisplayId, $healthRecordDate, $healthSubmissionFormType, $healthProfileRecord, $latestHealthSubmission, $healthRecordDocuments) {
            $snapshot = $healthSubmissionSnapshotProfile($submission);
            $useCurrentProfileFallback = !$submission
                || ($snapshot === [] && optional($submission)->id === optional($latestHealthSubmission)->id);
            $documents = $healthSubmissionDocumentBlueprints
                ->map(function ($document) use ($submission, $snapshot, $useCurrentProfileFallback, $healthRecordDocuments) {
                    $path = trim((string) ($snapshot[$document['key']] ?? ''));
                    if ($path === '' && $document['key'] === 'chest_xray_result') {
                        $path = trim((string) ($snapshot['chest_xray_document'] ?? ''));
                    }
                    if ($useCurrentProfileFallback && $path === '') {
                        $currentDocument = collect($healthRecordDocuments)->firstWhere('key', $document['key']);
                        $path = trim((string) ($currentDocument['path'] ?? ''));
                    }
                    if ($path === '') {
                        return null;
                    }

                    $extension = strtolower(pathinfo($path, PATHINFO_EXTENSION));
                    $document['path'] = $path;
                    $document['is_image'] = $document['is_image'] || in_array($extension, ['jpg', 'jpeg', 'png', 'webp'], true);
                    $document['url'] = $submission
                        ? route('student.health_form.submission.document', ['submission' => $submission->id, 'document' => $document['key']])
                        : route('student.health_record.document', ['document' => $document['key']]);

                    return $document;
                })
                ->filter()
                ->values();

            return [
                'id' => $healthFormDisplayId($submission),
                'type' => $healthSubmissionFormType($submission),
                'date' => $healthRecordDate(optional($submission)->submitted_at ?: (!$submission ? optional($healthProfileRecord)->created_at : null), 'Not submitted'),
                'is_latest' => $index === 0,
                'documents' => $documents,
            ];
        })
        ->filter(fn ($group) => $group['documents']->isNotEmpty())
        ->values();
    $healthSubmissionDocumentCount = $healthSubmissionDocumentGroups->sum(fn ($group) => $group['documents']->count());
@endphp

<div class="health-doc-layout">
    <div class="health-doc-main">
        <section class="health-doc-card health-doc-latest" aria-labelledby="latestHealthFormTitle">
            <div class="health-doc-ribbon">Latest Health Form</div>
            @if($healthFormSubmitted || $latestHealthSubmission)
                <div class="health-doc-latest-grid">
                    <div class="health-doc-identity">
                        <span class="health-doc-primary-icon" aria-hidden="true"><x-outline-icon name="clipboard-document-list" /></span>
                        <span>
                            <span class="health-doc-label">Health Form ID</span>
                            <strong class="health-doc-form-id" id="latestHealthFormTitle">{{ $latestFormId }}</strong>
                            <span class="health-doc-status {{ $latestStatus[1] }}">
                                {{ $latestStatus[0] }}
                            </span>
                        </span>
                    </div>

                    <div class="health-doc-meta-item">
                        <span class="health-doc-meta-icon"><x-outline-icon name="calendar-days" /></span>
                        <span><span class="health-doc-label">Submitted At</span><strong>{{ $healthRecordDate($latestSubmittedSource, $latestIsRequested ? 'Not yet submitted' : '-') }}</strong></span>
                    </div>
                    <div class="health-doc-meta-item">
                        <span class="health-doc-meta-icon"><x-outline-icon name="clock" /></span>
                        <span><span class="health-doc-label">Last Updated</span><strong>{{ $healthRecordDate($latestUpdatedSource) }}</strong></span>
                    </div>
                    <div class="health-doc-type">
                        <span class="health-doc-label">Form Type</span>
                        <strong>{{ $latestFormType }}</strong>
                    </div>
                    <div class="health-doc-meta-item">
                        <span class="health-doc-meta-icon"><x-outline-icon name="check" /></span>
                        <span><span class="health-doc-label">Approved At</span><strong>{{ $healthRecordDate($latestApprovedSource, 'Pending') }}</strong></span>
                    </div>
                    <div class="health-doc-meta-item">
                        <span class="health-doc-meta-icon"><x-outline-icon name="identification" /></span>
                        <span>
                            <span class="health-doc-label">{{ $latestIdentifierLabel }}</span>
                            <strong>{{ $latestIdentifierValue }}</strong>
                        </span>
                    </div>
                </div>

                <div class="health-doc-actions">
                    @if($healthProfileRecord)
                        <button type="button" class="health-doc-action is-primary" onclick="openHealthRecordModal()">
                            <x-outline-icon name="eye" />
                            View Files
                        </button>
                    @endif
                    @if($latestPdfAvailable && !$latestIsRequested)
                        <a href="{{ $latestPdfUrl }}" class="health-doc-action" download>
                            <x-outline-icon name="arrow-down-tray" />
                            Download Health Form (PDF)
                        </a>
                    @endif
                    @if($latestIsRequested)
                        <a href="{{ $newStudentHealthFormRoute }}" class="health-doc-action is-edit">
                            <x-outline-icon name="document-text" />
                            Fill Up New Health Form
                        </a>
                    @elseif($requiresHealthFormCorrection)
                        <a href="{{ $healthFormRoute }}" class="health-doc-action is-edit">
                            <x-outline-icon name="pencil-square" />
                            Edit Health Form
                        </a>
                    @endif
                </div>

                @if($healthRecordMissingRequirements->isNotEmpty())
                    <div class="health-doc-requirement">
                        <span><x-outline-icon name="exclamation-triangle" /> {{ $healthRecordMissingTitle }} ({{ $healthRecordMissingRequirements->count() }})</span>
                        @if($documentUploadKeysForModal->isNotEmpty())
                            <button
                                type="button"
                                class="health-doc-action is-primary health-doc-upload-button"
                                data-missing-document-open
                                data-action="{{ $documentModalUsesClinicReplacement ? route('student.health_record.resubmit') : route('student.health_record.documents') }}"
                            ><x-outline-icon name="document-text" /> Upload Files</button>
                        @elseif($requiresMissingESign)
                            <button type="button" class="health-doc-action is-primary health-doc-upload-button" onclick="openMissingESignModal()"><x-outline-icon name="pencil-square" /> Attach E-sign</button>
                        @endif
                    </div>
                @endif

                <div class="health-doc-note {{ $latestIsRequested ? 'is-requested' : '' }}">
                    <x-outline-icon name="information-circle" />
                    <span>
                        @if($latestIsRequested)
                            The clinic requested a new {{ $latestCategory !== '' ? $latestCategory : 'Health Form' }}. Complete it to begin the new review.
                        @elseif($requiresHealthFormCorrection)
                            Health Form correction requested. You may edit the form while the correction request is active.
                        @elseif($latestIsApproved)
                            This is your latest approved Health Form. A new clinic request will appear here as a separate record.
                        @else
                            Your latest Health Form is under clinic review.
                        @endif
                    </span>
                </div>
            @else
                <div class="health-doc-empty">
                    <span><x-outline-icon name="document-text" /></span>
                    <strong>No Health Form submitted yet</strong>
                    <p>Complete your Health Information Form to begin clinic review.</p>
                    <a href="{{ $healthFormRoute }}" class="health-doc-action is-edit">Complete Health Form</a>
                </div>
            @endif
        </section>

        <section class="health-doc-card health-doc-history" aria-labelledby="healthFormHistoryTitle">
            <div class="health-doc-section-head">
                <span class="health-doc-section-icon"><x-outline-icon name="clipboard-document-list" /></span>
                <span>
                    <h2 id="healthFormHistoryTitle">Health Form History</h2>
                    <p>All Health Forms submitted or requested for your clinic record.</p>
                </span>
            </div>

            <div class="health-doc-history-table">
                <div class="health-doc-history-head" aria-hidden="true">
                    <span>Form ID</span>
                    <span>Form Type / Purpose</span>
                    <span>Submitted At</span>
                    <span>Approved At</span>
                    <span>Status</span>
                    <span>Actions</span>
                </div>
                <div class="health-doc-history-body">
                    @forelse($historyHealthSubmissionItems as $historyIndex => $submission)
                        @php
                            $historyStatus = $healthSubmissionStatus($submission);
                            $historyStatusKey = strtolower(trim((string) optional($submission)->status));
                            if ($historyStatusKey === '' || ($isIssuedStatus && $historyStatusKey === 'submitted')) {
                                $historyStatusKey = $isIssuedStatus ? 'approved' : 'submitted';
                            }
                            $historyIsLatest = $historyIndex === 0;
                            $historyIsInitial = !$submission || !$oldestHealthSubmission || optional($submission)->id === optional($oldestHealthSubmission)->id;
                            $historyYear = trim((string) (optional($submission)->school_year ?: $recordAcademicYear));
                            $historyType = $healthSubmissionFormType($submission);
                            $historyPurpose = trim((string) optional($submission)->remarks);
                            if ($historyPurpose === '') {
                                $historyPurpose = $historyIsInitial && $isApplicantHealthRecord
                                    ? 'First clinic Health Form submitted as applicant.'
                                    : ($historyStatusKey === 'requested' ? 'New Health Form requested by the clinic.' : 'Health Form record.');
                            }
                            $historyHasPdf = filled(optional($submission)->pdf_path) || (!$submission && $healthFormSubmitted);
                            $historyPdfUrl = filled(optional($submission)->pdf_path)
                                ? route('student.health_form.submission', $submission->id)
                                : route('student.health_record.document', ['document' => 'health_form']);
                        @endphp
                        <article class="health-doc-history-row {{ $historyIsLatest ? 'is-latest' : '' }}">
                            <div class="health-doc-history-id" data-label="Form ID">
                                <span class="health-doc-row-icon"><x-outline-icon name="document-text" /></span>
                                <span>
                                    <strong>{{ $healthFormDisplayId($submission) }}</strong>
                                    @if($historyIsLatest)<small>Latest</small>@endif
                                </span>
                            </div>
                            <div class="health-doc-history-type" data-label="Form Type / Purpose">
                                <strong>{{ $historyType }}{{ !$isEmployeeHealthRecord && $historyYear !== '' ? ' (' . $historyYear . ')' : '' }}</strong>
                                <small>{{ $historyPurpose }}</small>
                            </div>
                            <div data-label="Submitted At"><strong>{{ $healthRecordDate(optional($submission)->submitted_at ?: (!$submission ? $recordSubmittedSource : null), 'Not submitted') }}</strong></div>
                            <div data-label="Approved At"><strong>{{ $healthRecordDate(optional($submission)->approved_at ?: ($historyStatusKey === 'approved' ? $recordApprovedSource : null), 'Pending') }}</strong></div>
                            <div data-label="Status"><span class="health-doc-status {{ $historyStatus[1] }}">{{ $historyStatus[0] }}</span></div>
                            <div class="health-doc-history-actions" data-label="Actions">
                                @if($historyHasPdf && $historyStatusKey !== 'requested')
                                    <a href="{{ $historyPdfUrl }}" target="_blank" rel="noopener" aria-label="View Health Form" title="View Health Form"><x-outline-icon name="eye" /></a>
                                    <a href="{{ $historyPdfUrl }}" download aria-label="Download Health Form" title="Download Health Form"><x-outline-icon name="arrow-down-tray" /></a>
                                @elseif($historyIsLatest && $historyStatusKey === 'requested')
                                    <a href="{{ $newStudentHealthFormRoute }}"><x-outline-icon name="document-text" /></a>
                                @endif
                            </div>
                        </article>
                    @empty
                        <div class="health-doc-history-empty">No Health Form history is available yet.</div>
                    @endforelse
                </div>
            </div>

            @if(!$latestIsRequested)
                <div class="health-doc-history-request">
                    <span><x-outline-icon name="information-circle" /> Need to submit a new Health Form?</span>
                    <small>Please visit the Medical Clinic or contact the clinic staff.</small>
                </div>
            @endif
        </section>
    </div>

    <aside class="health-doc-sidebar" aria-label="Health Form status and reminders">
        <section class="health-doc-side-card">
            <h2><span><x-outline-icon name="clipboard-document-list" /></span> Record Status</h2>
            <ol class="health-doc-progress">
                <li class="{{ $latestIsSubmitted ? 'is-complete' : ($latestIsRequested ? 'is-active' : '') }}">
                    <i></i>
                    <span><strong>Submitted</strong><small>{{ $healthRecordDate($latestSubmittedSource, $latestIsRequested ? 'Waiting for submission' : '-') }}</small><em>{{ $latestIsSubmitted ? 'Health Form submitted successfully.' : 'Complete the requested Health Form.' }}</em></span>
                    @if($latestIsSubmitted)<b><x-outline-icon name="check" /></b>@endif
                </li>
                @if($isApplicantHealthWorkflow)
                    <li class="{{ $encodeAssessmentComplete ? 'is-complete' : ($latestIsSubmitted ? 'is-active' : '') }}">
                        <i></i>
                        <span><strong>Encode Assessment</strong><small>{{ $encodeAssessmentComplete ? ($recordReviewAt ?: 'Assessment encoded') : 'Pending' }}</small><em>{{ $encodeAssessmentComplete ? 'Initial medical assessment recorded.' : 'Waiting for clinic assessment.' }}</em></span>
                        @if($encodeAssessmentComplete)<b><x-outline-icon name="check" /></b>@endif
                    </li>
                @endif
                <li class="{{ $finalReviewComplete ? 'is-complete' : ($finalReviewActive ? 'is-active' : '') }}">
                    <i></i>
                    <span><strong>Final Review</strong><small>{{ $finalReviewComplete ? ($recordReviewAt ?: 'Completed') : ($finalReviewActive ? 'In progress' : 'Pending') }}</small><em>{{ $finalReviewComplete ? 'Clinic review completed.' : 'For final review by the clinic.' }}</em></span>
                    @if($finalReviewComplete)<b><x-outline-icon name="check" /></b>@endif
                </li>
                <li class="{{ $latestIsApproved ? 'is-complete' : '' }}">
                    <i></i>
                    <span><strong>Approved</strong><small>{{ $healthRecordDate($latestApprovedSource, 'Pending') }}</small><em>{{ $latestIsApproved ? 'Health Form approved.' : 'Waiting for final approval.' }}</em></span>
                    @if($latestIsApproved)<b><x-outline-icon name="check" /></b>@endif
                </li>
            </ol>
        </section>

        <section class="health-doc-side-card is-reminder">
            <h2><span><x-outline-icon name="bell" /></span> Important Reminder</h2>
            <div class="health-doc-schedule"><x-outline-icon name="calendar-days" /><span><strong>Clinic Schedule</strong><small>{{ $clinicScheduleLabel }}</small></span></div>
            <p>Please make sure your selected clinic visit or follow-up time falls within this schedule.</p>
        </section>

        <section class="health-doc-side-card is-help">
            <h2><span><x-outline-icon name="information-circle" /></span> Need Help?</h2>
            <p>For questions or concerns about your Health Form, contact the Medical Clinic.</p>
            <a href="tel:0288375858"><x-outline-icon name="phone" /> (02) 8837-5858</a>
            <a href="mailto:puptclinic@gmail.com"><x-outline-icon name="envelope" /> puptclinic@gmail.com</a>
        </section>
    </aside>
</div>

@if($healthProfileRecord)
    <div class="record-modal-overlay" id="healthRecordModal" aria-hidden="true">
        <div class="record-modal health-record-details-modal health-doc-files-modal" role="dialog" aria-modal="true" aria-labelledby="healthRecordModalTitle">
            <div class="record-modal-head">
                <span class="health-record-details-head-icon" aria-hidden="true"><x-outline-icon name="document-text" /></span>
                <button type="button" class="record-modal-close" aria-label="Close uploaded files" onclick="closeHealthRecordModal()"><x-outline-icon name="x-mark" /></button>
                <div class="record-modal-head-main">
                    <h2 class="record-modal-title" id="healthRecordModalTitle">Uploaded Documents</h2>
                    <p class="record-modal-subtitle">View uploaded files for each Health Form record, newest first.</p>
                </div>
            </div>
            <div class="record-modal-body" id="healthRecordModalBody">
                <div class="health-doc-modal-summary">
                    <span><x-outline-icon name="clipboard-document-list" /></span>
                    <span>
                        <strong>{{ $healthSubmissionDocumentGroups->count() }} {{ \Illuminate\Support\Str::plural('Health Form version', $healthSubmissionDocumentGroups->count()) }}</strong>
                        <small>{{ $healthSubmissionDocumentCount }} {{ \Illuminate\Support\Str::plural('uploaded file', $healthSubmissionDocumentCount) }}</small>
                    </span>
                </div>
                <div class="health-doc-version-list">
                    @forelse($healthSubmissionDocumentGroups as $group)
                        <details class="health-doc-version-group" {{ $loop->first ? 'open' : '' }}>
                            <summary class="health-doc-version-toggle">
                                <span class="health-doc-version-icon"><x-outline-icon name="clipboard-document-list" /></span>
                                <span class="health-doc-version-copy">
                                    <span>
                                        <strong>{{ $group['id'] }}</strong>
                                        @if($group['is_latest'])
                                            <b>Latest</b>
                                        @endif
                                    </span>
                                    <small>{{ $group['type'] }} | {{ $group['date'] }} | {{ $group['documents']->count() }} {{ \Illuminate\Support\Str::plural('file', $group['documents']->count()) }}</small>
                                </span>
                                <span class="health-doc-version-chevron"><x-outline-icon name="chevron-down" /></span>
                            </summary>
                            <div class="health-doc-modal-files health-doc-version-files">
                                @foreach($group['documents'] as $document)
                                    <article class="health-doc-file-card">
                                        <div class="health-doc-file-preview">
                                            @if($document['is_image'])
                                                <img src="{{ $document['url'] }}" alt="{{ $document['title'] }} preview">
                                            @else
                                                <x-outline-icon name="document-text" />
                                            @endif
                                        </div>
                                        <div class="health-doc-file-copy">
                                            <strong>{{ $document['title'] }}</strong>
                                            <small>{{ $document['meta'] }}</small>
                                        </div>
                                        <a href="{{ $document['url'] }}" target="_blank" rel="noopener noreferrer">View File <x-outline-icon name="chevron-right" /></a>
                                    </article>
                                @endforeach
                            </div>
                        </details>
                    @empty
                        <div class="health-doc-modal-empty">
                            <span><x-outline-icon name="document-text" /></span>
                            <strong>No uploaded documents</strong>
                            <p>No supporting files are attached to your Health Form records yet.</p>
                        </div>
                    @endforelse
                </div>
            </div>
        </div>
    </div>
@endif
