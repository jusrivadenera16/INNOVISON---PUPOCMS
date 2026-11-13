const { test } = require('node:test');
const assert = require('node:assert/strict');
const fs = require('node:fs');
const vm = require('node:vm');
const path = require('node:path');
const script = fs.readFileSync(path.join(__dirname, '../../public/js/health-record-correction.js'), 'utf8');
function setup() {
    const node = () => ({ listeners: {}, attrs: {}, value: '',
        addEventListener(event, fn) { this.listeners[event] = fn; },
        setAttribute(key, val) { this.attrs[key] = val; }, getAttribute(key) { return this.attrs[key]; },
        focus() { this.focused = true; }, setCustomValidity(text) { this.validity = text; }, reportValidity() {},
    });
    const button = node(), dialog = node(), form = node(), submission = node(), cancel = node();
    dialog.open = false; dialog.showModal = () => { dialog.open = true; }; dialog.close = () => { dialog.open = false; };
    dialog.querySelector = selector => selector === 'form' ? form : cancel;
    form.querySelector = selector => selector === '[name="submission_id"]' ? submission : null;
    form.reset = () => { submission.value = ''; };
    const window = {};
    vm.runInNewContext(script, { window, document: {
        getElementById: id => id === 'verifyReturnForCorrectionBtn' ? button : dialog,
        querySelectorAll: () => [dialog],
    } });
    return { button, dialog, form, submission, cancel, configure: window.configureHealthRecordCorrection };
}
test('correction confirmation targets the selected submission and focuses Cancel', () => {
    const state = setup();
    state.configure({ correction_url: '/health-records/employee/28/return-for-correction', correction_submission_id: 178 });
    assert.equal(state.button.disabled, false);
    state.button.listeners.click();
    assert.equal(state.dialog.open, true);
    assert.equal(state.cancel.focused, true);
    state.configure({ correction_url: '/health-records/student/9/return-for-correction', correction_submission_id: 200 });
    assert.equal(state.submission.value, '200');
    assert.equal(state.form.attrs.action, '/health-records/student/9/return-for-correction');
});
test('missing route or submission keeps correction disabled', () => {
    for (const payload of [{}, { correction_url: '/test' }, { correction_url: '/test', correction_submission_id: -1 }]) {
        const state = setup(); state.configure(payload); state.button.listeners.click();
        assert.equal(state.button.disabled, true); assert.equal(state.dialog.open, false);
    }
});
test('confirmation submits without a reason and closes the dialog before loading', () => {
    const state = setup();
    state.configure({ correction_url: '/test', correction_submission_id: 1 }); state.button.listeners.click();
    state.form.listeners.submit({ preventDefault() { throw new Error('Valid submission must proceed'); } });
    assert.equal(state.dialog.open, false);
});
test('Cancel closes confirmation without submitting', () => {
    const state = setup();
    state.configure({ correction_url: '/test', correction_submission_id: 1 });
    state.button.listeners.click(); state.cancel.listeners.click();
    assert.equal(state.dialog.open, false);
});
test('withdrawal needs modal confirmation, Cancel does not submit, confirm closes before navigation', () => {
    const blade = fs.readFileSync(path.join(__dirname, '../../resources/views/admin/health_records.blade.php'), 'utf8').replace(/\r\n/g, '\n');
    const start = blade.indexOf('    if (verifyWithdrawHealthRecordForm) {\n        const withdrawDialog');
    const end = blade.indexOf('    if (verifyRegularStudentDecisionForm)', start);
    assert.ok(start > 0 && end > start);
    const nodes = {};
    for (const key of ['cancel', 'confirm']) nodes[key] = { addEventListener(_, fn) { this.click = fn; }, focus() {} };
    const dialog = { open: false, showModal() { this.open = true; }, close() { this.open = false; },
        querySelector(selector) { return selector === '[data-withdraw-cancel]' ? nodes.cancel : nodes.confirm; } };
    const button = { disabled: false };
    let navigations = 0;
    const form = { getAttribute() { return '/withdraw/student/1'; }, addEventListener(_, fn) { this.submit = fn; },
        requestSubmit() { let prevented = false; this.submit({ preventDefault() { prevented = true; } });
            if (!prevented) { assert.equal(dialog.open, false); navigations++; } } };
    vm.runInNewContext(blade.slice(start, end), { verifyWithdrawHealthRecordForm: form,
        verifyWithdrawHealthRecordBtn: button, document: { getElementById: () => dialog, querySelectorAll: () => [dialog] } });
    form.requestSubmit(); assert.equal(dialog.open, true); assert.equal(navigations, 0);
    nodes.cancel.click(); assert.equal(dialog.open, false); assert.equal(navigations, 0);
    form.requestSubmit(); nodes.confirm.click(); assert.equal(navigations, 1); assert.equal(button.disabled, true);
});
