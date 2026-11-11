const { test } = require('node:test');
const assert = require('node:assert/strict');
const fs = require('node:fs');
const path = require('node:path');
const vm = require('node:vm');

const blade = fs.readFileSync(path.join(__dirname, '../../resources/views/admin/walkin.blade.php'), 'utf8');
const start = blade.indexOf("                    const evaluationPrompt = isApprovedDecision && data.evaluation_source_type === 'student_approval'");
const end = blade.indexOf('                } else {', start);
assert.ok(start > 0 && end > start);
const source = blade.slice(start, end);

function prompt(approved, data) {
    let options;
    vm.runInNewContext(source, {
        isApprovedDecision: approved,
        isDependentRecord: false,
        data,
        studentApprovalEvaluationUrl: '/admin/walkin/student-approval-evaluation',
        approvalOverlay: {},
        showClinicSuccessOverlay: (overlay, value) => { options = value; },
    });
    return options;
}

test('approved student sees a persistent Send Evaluation / Skip prompt', () => {
    const options = prompt(true, { evaluation_source_type: 'student_approval', evaluation_source_id: 42 });
    assert.equal(options.persistent, true);
    assert.equal(options.evaluationPrompt.sourceType, 'student_approval');
    assert.equal(options.evaluationPrompt.sourceId, 42);
    assert.equal(options.evaluationPrompt.url, '/admin/walkin/student-approval-evaluation');
    assert.match(options.message, /send a service evaluation form/);
});

test('pending assessment and non-student responses do not prompt for student evaluation', () => {
    assert.equal(prompt(false, {}).evaluationPrompt, null);
    assert.equal(prompt(false, {}).persistent, false);
    assert.equal(prompt(true, { evaluation_source_type: 'employee_approval', evaluation_source_id: 42 }).evaluationPrompt, null);
});
