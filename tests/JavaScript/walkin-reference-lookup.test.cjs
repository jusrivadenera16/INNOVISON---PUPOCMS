const assert = require('node:assert/strict');
const fs = require('node:fs');
const path = require('node:path');
const test = require('node:test');
const vm = require('node:vm');

const blade = fs.readFileSync(path.join(__dirname, '../../resources/views/admin/walkin.blade.php'), 'utf8');
const lookupStart = blade.indexOf('        function doLookup(cachedLookupData = null, openSubmittedReview = false) {');
const lookupEnd = blade.indexOf('        referenceVerificationCancel?.addEventListener', lookupStart);
assert.ok(lookupStart >= 0 && lookupEnd > lookupStart, 'The actual lookup function must be present.');
const lookupSource = blade.slice(lookupStart, lookupEnd);
const bindingStart = blade.indexOf("        if (findBtn) findBtn.addEventListener('click', doLookup);");
const bindingEnd = blade.indexOf('        if (initialWalkinReviewReference', bindingStart);
assert.ok(bindingStart >= 0 && bindingEnd > bindingStart, 'The actual Search and Enter handlers must be present.');
const bindingSource = blade.slice(bindingStart, bindingEnd);

class Button extends EventTarget {
    disabled = false;
    click() { this.dispatchEvent(new Event('click')); }
}

function harness(response, functionSource = lookupSource) {
    const calls = { requests: [], verification: [], details: [], errors: [] };
    const findBtn = new Button();
    const refInput = new EventTarget();
    refInput.value = '2026-2121-1212';
    const classList = { add() {}, remove() {} };
    const context = vm.createContext({
        URL,
        Promise,
        findBtn,
        refInput,
        foundCard: { style: {}, dataset: {} },
        documentsButton: { classList },
        savedAssessmentButton: { classList },
        entryPane: { classList },
        referenceVerification: { hidden: false },
        document: { querySelector: () => null },
        window: { location: { origin: 'http://localhost' } },
        getStudentUrl: '/admin/walkin/get-student',
        currentApplicantWorkflow: 'reference_lookup',
        pendingReferenceLookup: {},
        isClinicLookupMode: () => false,
        isFinalReviewWorkflow: () => false,
        formatApplicantReferenceInput: value => value,
        setStatus() {},
        setReferenceLookupMessage: (type, message) => calls.errors.push({ type, message }),
        showApplicantReferenceVerification: (...args) => calls.verification.push(args),
        showLookupDetails: (...args) => calls.details.push(args),
        showAlreadyApprovedResult() {},
        populateEmployeeDraft() {},
        populateStudentAssessmentDraft() {},
        populateApplicantFinalReviewDraft() {},
        setReviewDraftButtonVisible() {},
        fetch: async (url, options) => {
            calls.requests.push({ url, options });
            return { json: async () => response };
        },
    });
    vm.runInContext(functionSource + '\n' + bindingSource, context);
    return { calls, context, findBtn, refInput };
}

// Flush the fetch/json/lookup promise chain after DOM events have been dispatched.
const settle = () => new Promise(resolve => setImmediate(resolve));

test('clinic Continue stays in clinic review and the applicants total is applicant-only', () => {
    const start = blade.indexOf("        referenceVerificationContinue?.addEventListener('click', () => {");
    const end = blade.indexOf("        refInput?.addEventListener('input'", start);
    let click;
    const classes = [];
    const activeClasses = new Set(['is-employee-lookup', 'is-final-review-workflow', 'is-final-review-toolbar-stuck']);
    const data = {status:'preview',record_type:'employee'};
    const continued = [];
    const context = vm.createContext({
        referenceVerificationContinue:{addEventListener:(_event,handler)=>{click=handler;}},
        pendingReferenceLookup:{hasCompletedHealthForm:true,referenceNumber:'EMP-123',data},
        refInput:{value:''},referenceVerification:{hidden:false},entryPane:{classList:{remove(){}}},
        isClinicLookupMode:()=>true,hideAlreadyApprovedResult(){},
        lookupModalTitle:{textContent:''},lookupModalSubtitle:{textContent:''},
        modalShell:{classList:{add:value=>{classes.push(value);activeClasses.add(value);},remove:(...values)=>values.forEach(value=>activeClasses.delete(value))}},
        setApplicantWorkflow(){throw new Error('Must not switch clinic record into applicant review');},
        doLookup:payload=>continued.push(payload),
    });
    const helperStart = blade.indexOf('        function enterClinicFinalReview()');
    const helperEnd = blade.indexOf('        referenceVerificationCancel?', helperStart);
    vm.runInContext(blade.slice(helperStart,helperEnd) + blade.slice(start,end),context);click();
    assert.equal(continued[0],data);
    assert.equal(context.lookupModalTitle.textContent,'Final Review');
    assert.deepEqual(classes,['is-clinic-final-review']);
    assert.equal(activeClasses.has('is-employee-lookup'),true);
    assert.equal(activeClasses.has('is-final-review-workflow'),false);
    assert.equal(activeClasses.has('is-final-review-toolbar-stuck'),false);
    assert.match(blade,/\.is-final-review-workflow\.is-applicant-lookup #applicantFinalReviewTotalBadge\s*\{\s*display: inline-flex;/);
    assert.doesNotMatch(blade,/\.is-final-review-workflow \.applicant-final-review-total-badge\s*\{\s*display: inline-flex;/);
    assert.ok(blade.includes("setStatus('info', 'Looking up record...');"));
    assert.ok(!blade.includes('Looking up employee/student record...'));
});

test('Search click fetches the entered reference and opens successful verification', async () => {
    const response = { status: 'preview', student_name: 'Applicant', reference_number: '2026-2121-1212' };
    const { calls, findBtn } = harness(response);
    findBtn.click();
    await settle();

    assert.equal(calls.requests.length, 1);
    const request = new URL(calls.requests[0].url);
    assert.equal(request.pathname, '/admin/walkin/get-student');
    assert.equal(request.searchParams.get('student_id'), '2026-2121-1212');
    assert.equal(request.searchParams.get('preview_only'), 'true');
    assert.equal(request.searchParams.get('lookup_scope'), null);
    assert.equal(calls.verification.length, 1);
    assert.equal(calls.verification[0][0], response);
    assert.equal(calls.errors.length, 0);
});

test('Enter uses the actual keyboard handler and performs the same Search lookup', async () => {
    const response = { status: 'preview', student_name: 'Applicant' };
    const { calls, refInput } = harness(response);
    const enter = new Event('keydown', { cancelable: true });
    Object.defineProperty(enter, 'key', { value: 'Enter' });
    refInput.dispatchEvent(enter);
    await settle();

    assert.equal(enter.defaultPrevented, true);
    assert.equal(calls.requests.length, 1);
    assert.equal(calls.verification.length, 1);
    assert.equal(calls.errors.length, 0);
});

test('Search accepts a local SIS transferee preview without reporting no applicant', async () => {
    const response = {
        status: 'preview',
        student_name: 'Local Transferee',
        reference_number: '2026-2121-1212',
        applicant_type: 'transferee',
        record_type: 'applicant',
        lookup_source: 'local_sis_reference',
        has_completed_health_form: true,
    };
    const { calls, findBtn } = harness(response);
    findBtn.click();
    await settle();

    assert.equal(calls.requests.length, 1);
    assert.equal(calls.verification.length, 1);
    assert.equal(calls.verification[0][0].applicant_type, 'transferee');
    assert.equal(calls.verification[0][3], true);
    assert.equal(calls.errors.length, 0);
});

test('An intentional cached successful response continues to details without fetching again', async () => {
    const response = {
        status: 'preview',
        student_name: 'Approved Applicant',
        has_completed_health_form: true,
        is_health_profile_completed: true,
    };
    const { calls, context } = harness(response);
    context.cachedResponse = response;
    vm.runInContext('doLookup(cachedResponse);', context);
    await settle();

    assert.equal(calls.requests.length, 0);
    assert.equal(calls.verification.length, 0);
    assert.equal(calls.details.length, 1);
    assert.equal(calls.details[0][0], response);
    assert.equal(context.pendingReferenceLookup, null);
    assert.equal(calls.errors.length, 0);
});

test('clinic ID lookup without a submission shows verification only even if the record says approved', async () => {
    for (const recordType of ['student', 'employee', 'dependent']) {
        const response = { status: 'preview', record_type: recordType, student_name: 'Record Found', has_completed_health_form: false, approved: true };
        const { calls, context, findBtn } = harness(response);
        context.isClinicLookupMode = () => true;
        context.currentApplicantWorkflow = 'review';
        findBtn.click();
        await settle();
        assert.equal(calls.verification.length, 1);
        assert.equal(calls.verification[0][3], false);
        assert.equal(calls.details.length, 0);
        assert.equal(context.isApprovalMode, false);
        assert.equal(calls.errors.length, 0);
    }
});

test('clinic ID lookup with an approved form stops at preview instead of opening review', async () => {
    const response = { status: 'preview', record_type: 'employee', student_name: 'Employee', has_completed_health_form: true, approved: true };
    const { calls, context, findBtn } = harness(response);
    context.isClinicLookupMode = () => true;
    context.currentApplicantWorkflow = 'review';
    findBtn.click();
    await settle();
    assert.equal(calls.verification.length, 1);
    assert.equal(calls.details.length, 0);
    assert.equal(calls.errors.length, 0);
});

test('explicit clinic review links continue automatically only for a reviewable submission', async () => {
    const gateStart = blade.indexOf('                    // Resume and explicit review links');
    const gateEnd = blade.indexOf("                    if (currentApplicantWorkflow === 'reference_lookup')", gateStart);
    for (const reviewable of [true, false]) {
        const response = {status:'preview', record_type:'student', has_completed_health_form:true, has_reviewable_health_form:reviewable};
        let continued = 0;
        let previewed = 0;
        const context = vm.createContext({
            data:response, cachedLookupData:null, openSubmittedReview:true, hasCompletedHealthForm:true, isAlreadyApproved:false,
            isClinicLookupMode:()=>true, currentApplicantWorkflow:'review',
            ref:'STUDENT-1', applicantName:'Student', setReviewDraftButtonVisible(){},
            enterClinicFinalReview:()=>continued++,
            showApplicantReferenceVerification:()=>previewed++,
        });
        vm.runInContext(`(function () { ${blade.slice(gateStart,gateEnd)} })()`,context);
        assert.equal(continued,reviewable?1:0);
        assert.equal(previewed,reviewable?0:1);
    }
    assert.match(blade,/setEntryMode\(true\);\s*doLookup\(null, true\);/);
});

test('approved students and dependents use the same view-only branch as approved employees', async () => {
    for (const recordType of ['student', 'dependent', 'employee']) {
        const response = { status: 'preview', record_type: recordType, student_name: 'Approved Patient', has_completed_health_form: true, has_reviewable_health_form: false, approved: true };
        const { calls, context, findBtn } = harness(response);
        context.isClinicLookupMode = () => true;
        context.currentApplicantWorkflow = 'review';
        let approvedView = 0;
        context.showAlreadyApprovedResult = () => approvedView++;
        findBtn.click();
        await settle();
        assert.equal(approvedView, 0);
        assert.equal(calls.verification.length, 1);
        assert.equal(calls.details.length, 0);
        assert.equal(context.isApprovalMode, false);
        assert.equal(calls.errors.length, 0);
    }
});

test('a genuinely new submitted form can still be reviewed after a previous approval', async () => {
    const response = { status: 'preview', record_type: 'student', student_name: 'OJT Student', has_completed_health_form: true, has_reviewable_health_form: true, approved: true };
    const { calls, context, findBtn } = harness(response);
    findBtn.style = {};
    Object.assign(context, {
        isClinicLookupMode: () => true, currentApplicantWorkflow: 'review',
        foundName: { textContent: '' }, getApplicantInitials: () => 'OS',
        isEncodeWorkflow: () => false, canApproveFinalReview: true,
        currentRecordType: 'student', canLookupEmployeeRecords: true,
        defaultPane: null, syncFindingsReviewFields() {},
        saveStudentAssessment() {}, doApprove() {}, saveApplicantEncoding() {}, enterSavedReviewEditMode() {},
    });
    let approvedView = 0;
    context.showAlreadyApprovedResult = () => approvedView++;
    findBtn.click();
    await settle();
    assert.equal(calls.verification.length,1);
    assert.equal(calls.details.length,0);
    context.doLookup(response); // Continue reuses the successful lookup payload.
    await settle();
    assert.equal(approvedView, 0);
    assert.equal(calls.details.length, 1);
    assert.equal(context.isApprovalMode, true);
    assert.equal(findBtn.disabled, false);
    assert.equal(calls.errors.length, 0);
});

test('the shared applicant verification card disables Continue and shows Not Yet Submitted for clinic users too', () => {
    const start = blade.indexOf('        function showApplicantReferenceVerification(');
    const end = blade.indexOf('        function returnToApplicantReferenceSearch(', start);
    for (const type of ['applicant', 'employee', 'student']) {
        const node = () => ({ hidden: true, disabled: false, textContent: '',
            classList: { toggle() {}, add() {} }, setAttribute() {}, removeAttribute() {}, focus() {},
        });
        const context = {
            URL, setTimeout: callback => callback(),
            window: { location: { origin: 'http://localhost', href: 'http://localhost/admin/walkin' } },
            getApplicantInitials: () => 'RP', setStatus() {}, hideAlreadyApprovedResult() {},
            lookupModalTitle: node(),lookupModalSubtitle: node(),referenceVerificationCourseLabel:node(),
            referenceVerificationApprovedAt:node(),referenceFormDocumentIcon:node(),referenceFormApprovedIcon:node(),
            isClinicLookupMode: () => type !== 'applicant',
            currentApplicantWorkflow: type === 'applicant' ? 'reference_lookup' : 'review',
            pendingReferenceLookup: null,
            entryPane: node(),
        };
        for (const name of ['Initials', 'Photo', 'Name', 'ApplicantStatus', 'ApplicantType', 'Ref', 'Course', 'Email', 'FormStatus', 'Warning', 'Continue', 'Cancel']) {
            context['referenceVerification' + name] = node();
        }
        context.referenceVerification = node();
        vm.createContext(context);
        vm.runInContext(blade.slice(start, end), context);
        context.showApplicantReferenceVerification({ record_type: type }, 'ID-123', 'Record Patient', false);
        assert.equal(context.referenceVerification.hidden, false);
        assert.equal(context.referenceVerificationFormStatus.textContent, 'Not Yet Submitted');
        assert.equal(context.referenceVerificationContinue.disabled, true);
        assert.equal(context.referenceVerificationWarning.hidden, false);
        assert.equal(context.referenceVerificationApplicantType.textContent, type[0].toUpperCase() + type.slice(1));
        if(type!=='applicant') {
            assert.equal(context.lookupModalTitle.textContent,'ID Lookup');
            assert.equal(context.referenceVerificationCourse.textContent,'No submitted request');
            context.showApplicantReferenceVerification({record_type:type,has_reviewable_health_form:true,
                health_form_preview:{status:'submitted',submitted:true,category:'On the Job Training'}},'ID-123','Record Patient',true);
            assert.equal(context.referenceVerificationContinue.disabled,false);
            assert.equal(context.referenceVerificationCourse.textContent,'On the Job Training');
            assert.equal(context.referenceVerificationApplicantStatus.textContent,'Pending Approval');
            const actions=[];
            context.alreadyApprovedResult={...node(),parentElement:{},querySelector:()=>null};
            context.alreadyApprovedMessage=node();context.alreadyApprovedMeta=node();
            context.clinicLookupApprovedSlot={append:card=>actions.push(card)};
            context.approvedResultHome=null;context.approvedResultNext=null;
            context.window.ApprovedClearanceReview={configure:(...args)=>actions.push(args)};
            context.showApplicantReferenceVerification({record_type:type,health_profile_id:7,approved:true,
                health_form_preview:{status:'approved',submitted:true,category:'Annual',approved_at:'Nov 12, 2026'}},'ID-123','Record Patient',true);
            assert.equal(context.referenceVerificationContinue.hidden,true);
            assert.equal(context.referenceVerificationApplicantStatus.textContent,'Approved');
            assert.equal(context.referenceVerificationCourse.textContent,'Annual');
            assert.match(context.referenceVerificationApprovedAt.textContent,/Nov 12, 2026/);
            assert.equal(context.referenceVerificationApprovedAt.hidden,false);
            assert.equal(context.referenceFormApprovedIcon.hidden,false);
            assert.equal(context.referenceFormDocumentIcon.hidden,true);
            assert.equal(actions[0],context.alreadyApprovedResult);
            assert.deepEqual(actions[1],[type,7]);
        }
    }
});
