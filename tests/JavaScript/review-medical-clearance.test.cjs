const { test } = require('node:test');
const assert = require('node:assert/strict');
const fs = require('node:fs');
const path = require('node:path');
const vm = require('node:vm');
const source = fs.readFileSync(path.join(__dirname, '../../public/js/review-medical-clearance.js'), 'utf8');
function element() {
    const handlers = {};
    return { hidden: false, checked: false, disabled: false, required: false, value: '', textContent: '', children: [],
        addEventListener(name, handler) { handlers[name] = handler; }, emit(name, event = {}) { return handlers[name]?.(event); },
        replaceChildren() { this.children = []; }, append(child) { this.children.push(child); }, reportValidity() { return !this.required || !!this.value; },
    };
}
const info = { today: '2026-11-11', purposes: ['On the Job Training'], default_purpose: 'On the Job Training',
    identity: { name: 'Juan Dela Cruz', age: 22, sex: 'M', birth_date: '2004-01-15' } };
function harness(fetcher = () => ({ ok: true, json: async () => info })) {
    const nodes = Object.fromEntries(['enable', 'date', 'purpose', 'other', 'fields', 'other-label', 'patient', 'status'].map(key => [key, element()]));
    const root = element(); root.dataset = { optionsBase: '/admin/medical-clearances' };
    root.querySelector = selector => nodes[selector.slice('[data-review-clearance-'.length, -1)];
    const document = element(); let decision = 'approve';
    document.querySelector = selector => selector === '[data-review-clearance]' ? root : { value: decision };
    document.createElement = () => element();
    const calls = [], window = {};
    vm.runInNewContext(source, { window, document, crypto: { randomUUID: () => 'review-key' },
        fetch: async (...args) => { calls.push(args); return fetcher(...args); } });
    return { root, nodes, document, calls, api: window.ReviewMedicalClearance, setDecision(value) { decision = value; document.emit('change', { target: { name: 'applicant_clearance_decision' } }); } };
}
test('only pending student and employee reviews can show certificate options; lookups never issue', async () => {
    const h = harness();
    for (const type of ['applicant', 'dependent', 'guest']) { await h.api.configure(type, 1, true); assert.equal(h.root.hidden, true); }
    await h.api.configure('student', 1, false); assert.equal(h.root.hidden, true);
    assert.equal(h.calls.length, 0);
    await h.api.configure('student', 1, true);
    assert.equal(h.root.hidden, false);
    assert.equal(h.nodes.patient.value, 'Juan Dela Cruz  22 / M');
    assert.equal(h.nodes.enable.checked, false);
    assert.equal(h.calls.length, 1);
    assert.equal(h.calls[0][1].method, undefined);
    assert.equal(h.api.collect(true).issue_medical_clearance, false);
});
test('checked option is sent with approval and keeps one retry key', async () => {
    const h = harness(); await h.api.configure('student', 1, true);
    h.nodes.enable.checked = true; h.nodes.enable.emit('change');
    const data = h.api.collect(true);
    assert.equal(data.issue_medical_clearance, true);
    assert.equal(data.medical_clearance.purpose, 'On the Job Training');
    assert.equal(data.medical_clearance.issued_on, '2026-11-11');
    assert.equal(data.medical_clearance.request_key, h.api.collect(true).medical_clearance.request_key);
    assert.equal(data.medical_clearance.name, undefined);
});
test('pending never issues even when checked; Others requires a purpose', async () => {
    const h = harness(); await h.api.configure('employee', 1, true);
    h.nodes.enable.checked = true; h.nodes.enable.emit('change');
    h.setDecision('pending');
    assert.equal(h.api.collect(false).issue_medical_clearance, false);
    assert.equal(h.nodes.date.disabled, true);
    h.setDecision('approve');
    h.nodes.purpose.value = '__other__'; h.nodes.purpose.emit('change');
    assert.equal(h.nodes['other-label'].hidden, false);
    assert.throws(() => h.api.collect(true), /Complete/);
    h.nodes.other.value = 'Return to Work';
    assert.equal(h.api.collect(true).medical_clearance.other_purpose, 'Return to Work');
});
test('changing patients clears optional issuance and ignores stale option responses', async () => {
    let finish;
    const pending = new Promise(resolve => { finish = resolve; });
    const h = harness(() => pending);
    const loading = h.api.configure('student', 1, true);
    h.api.reset();
    finish({ ok: true, json: async () => info }); await loading;
    assert.equal(h.root.hidden, true);
    assert.equal(h.api.collect(true).issue_medical_clearance, false);
});
test('date changes update the displayed age without trusting editable patient details', async () => {
    const h = harness(); await h.api.configure('student', 1, true);
    h.nodes.date.value = '2026-01-14'; h.nodes.date.emit('change');
    assert.equal(h.nodes.patient.value, 'Juan Dela Cruz  21 / M');
});
