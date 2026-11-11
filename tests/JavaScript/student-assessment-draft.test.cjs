const { test } = require('node:test');
const assert = require('node:assert/strict');
const fs = require('node:fs');
const vm = require('node:vm');
const path = require('node:path');

const blade = fs.readFileSync(path.join(__dirname, '../../resources/views/admin/walkin.blade.php'), 'utf8');
const start = blade.indexOf('        function populateStudentAssessmentDraft(');
const end = blade.indexOf('        function renderPendingHistory(', start);
const restoreSource = blade.slice(start, end);

test('student drafts restore only in clinic Record Management, never applicant Final Review', () => {
    const restored = [];
    const context = vm.createContext({
        currentRecordType: 'student',
        isClinicLookupMode: () => true,
        isFinalReviewWorkflow: () => false,
        applyReviewDraftFields: data => restored.push(data),
    });
    vm.runInContext(restoreSource, context);
    const draft = { lookup_scope: 'student_local', applicant_condition_remarks: 'Resume me' };
    context.populateStudentAssessmentDraft(draft);
    assert.deepEqual(restored, [draft]);
    context.populateStudentAssessmentDraft({ applicant_condition_remarks: 'Applicant draft' });
    context.isClinicLookupMode = () => false;
    context.populateStudentAssessmentDraft(draft);
    context.populateApplicantFinalReviewDraft(draft);
    assert.equal(restored.length, 1);
});

test('the draft button sends student clinic records to their own saving handler', () => {
    const start = blade.indexOf("        reviewDraftBtn?.addEventListener('click', function () {");
    const end = blade.indexOf("        document.querySelectorAll('#applicantRefModal [name]')", start);
    let handler;
    const calls = [];
    const context = vm.createContext({
        reviewDraftBtn: { addEventListener: (event, callback) => { handler = callback; } },
        isClinicLookupMode: () => true,
        currentRecordType: 'student',
        isFinalReviewWorkflow: () => false,
        saveStudentAssessmentDraft: () => calls.push('student'),
        saveEmployeeDraft: () => calls.push('employee'),
        saveApplicantFinalReviewDraft: () => calls.push('applicant'),
    });
    vm.runInContext(blade.slice(start, end), context);
    handler();
    context.currentRecordType = 'employee';
    handler();
    context.currentRecordType = 'applicant';
    context.isClinicLookupMode = () => false;
    context.isFinalReviewWorkflow = () => true;
    handler();
    assert.deepEqual(calls, ['student', 'employee', 'applicant']);
});
