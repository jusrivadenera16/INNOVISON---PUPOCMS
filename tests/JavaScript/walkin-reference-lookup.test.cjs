const assert = require('node:assert/strict');
const fs = require('node:fs');
const path = require('node:path');
const test = require('node:test');
const vm = require('node:vm');

const blade = fs.readFileSync(path.join(__dirname, '../../resources/views/admin/walkin.blade.php'), 'utf8');
const lookupStart = blade.indexOf('        function doLookup(cachedLookupData = null) {');
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
