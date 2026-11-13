const {test} = require('node:test');
const assert = require('node:assert/strict');
const fs = require('node:fs');
const vm = require('node:vm');
const path = require('node:path');
const source = fs.readFileSync(path.join(__dirname, '../../resources/views/admin/walkin.blade.php'), 'utf8');

test('employee draft Resume identifies the saved record and requests direct Final Review', () => {
    const start = source.indexOf('        function openEmployeeDraftReference(');
    const end = source.indexOf('        function showFinalReviewList(', start);
    const calls = [];
    const context = vm.createContext({String, refInput: {value: ''}, resetLookupState() {}, applyLookupMode() {}, setEntryMode() {}, doLookup(...args) {calls.push(args);}});
    vm.runInContext(source.slice(start, end), context);
    context.openEmployeeDraftReference(' 93060 ');
    assert.equal(context.refInput.value, '93060');
    assert.equal(context.currentLookupMode, 'clinic');
    assert.deepEqual(calls, [[null, true]]);
    context.openEmployeeDraftReference('');
    assert.equal(calls.length, 1);
});

test('only explicit, unapproved, submitted reviews bypass the ID Lookup preview', () => {
    const start = source.indexOf('                    // Resume and explicit review links');
    const end = source.indexOf("                    if (currentApplicantWorkflow === 'reference_lookup')", start);
    const body = source.slice(start, end);
    for (const item of [
        {explicit: true, completed: true, approved: false, direct: true},
        {explicit: false, completed: true, approved: false, direct: false},
        {explicit: true, completed: false, approved: false, direct: false},
        {explicit: true, completed: true, approved: true, direct: false},
    ]) {
        let entered = 0, preview = 0;
        const context = vm.createContext({cachedLookupData: null, data: {has_reviewable_health_form: true, saved_draft: {remarks: 'Saved note'}},
            openSubmittedReview: item.explicit, hasCompletedHealthForm: item.completed, isAlreadyApproved: item.approved,
            isClinicLookupMode: () => true, enterClinicFinalReview() {entered++;}, showApplicantReferenceVerification() {preview++;},
            setReviewDraftButtonVisible() {}, ref: '93060', applicantName: 'Employee'});
        vm.runInContext(`(function () { ${body} })()`, context);
        assert.equal(entered, item.direct ? 1 : 0);
        assert.equal(preview, item.direct ? 0 : 1);
        if (item.direct) assert.equal(context.cachedLookupData, context.data);
    }
    assert.ok(source.includes('populateEmployeeDraft(data.employee_draft_data || {})'));
    assert.ok(source.includes('populateStudentAssessmentDraft(data.student_assessment_draft_data || {})'));
});

test('direct draft opening uses the same clinic Final Review shell as Continue', () => {
    const start = source.indexOf('        function enterClinicFinalReview()');
    const end = source.indexOf('        referenceVerificationCancel?', start);
    const removed = [], added = [];
    const context = vm.createContext({pendingReferenceLookup: {}, referenceVerification: {hidden: false},
        entryPane: {classList: {remove(...names) {removed.push(...names);}}}, hideAlreadyApprovedResult() {},
        lookupModalTitle: {}, lookupModalSubtitle: {}, modalShell: {classList: {remove(...names) {removed.push(...names);}, add(name) {added.push(name);}}}});
    vm.runInContext(source.slice(start, end), context);
    context.enterClinicFinalReview();
    assert.equal(context.pendingReferenceLookup, null);
    assert.equal(context.referenceVerification.hidden, true);
    assert.equal(context.lookupModalTitle.textContent, 'Final Review');
    assert.deepEqual(added, ['is-clinic-final-review']);
    assert.ok(removed.includes('is-reference-lookup'));
});
