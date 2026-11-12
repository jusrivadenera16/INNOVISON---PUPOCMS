const { test } = require('node:test');
const assert = require('node:assert/strict');
const fs = require('node:fs');
const path = require('node:path');
const vm = require('node:vm');
const source = fs.readFileSync(path.join(__dirname, '../../public/js/review-medical-clearance.js'), 'utf8');
const selectSource = fs.readFileSync(path.join(__dirname, '../../public/js/category-bubble-select.js'), 'utf8');
function element() {
    const handlers = {};
    return { hidden: false, checked: false, disabled: false, required: false, value: '', textContent: '', children: [], dataset: {}, attrs: {},
        setAttribute(name, value) { this.attrs[name] = value; }, focus() { this.focused = true; },
        contains(target) { return target === this || this.children.includes(target); },
        addEventListener(name, handler) { handlers[name] = handler; }, emit(name, event = {}) { return handlers[name]?.(event); },
        replaceChildren() { this.children = []; }, append(child) { this.children.push(child); }, reportValidity() { return !this.required || !!this.value; },
    };
}
const info = { today: '2026-11-11', purposes: ['On the Job Training'], default_purpose: 'On the Job Training',
    identity: { name: 'Juan Dela Cruz', age: 22, sex: 'M', birth_date: '2004-01-15' } };
function harness(fetcher = () => ({ ok: true, json: async () => info })) {
    const nodes = Object.fromEntries(['enable', 'date', 'purpose', 'purpose-trigger', 'purpose-menu', 'other', 'fields', 'other-label', 'patient', 'status', 'document'].map(key => [key, element()]));
    nodes['purpose-trigger'].querySelector = () => nodes.purposeValue;
    nodes.purposeValue = element();
    const root = element(); root.dataset = { optionsBase: '/admin/medical-clearances' };
    root.querySelector = selector => nodes[selector.slice('[data-review-clearance-'.length, -1)];
    const document = element(); let decision = 'approve';
    document.querySelector = selector => selector === '[data-review-clearance]' ? root : { value: decision };
    document.createElement = () => element();
    const calls = [], window = {};
    vm.runInNewContext(selectSource, { window, document });
    vm.runInNewContext(source, { window, document, crypto: { randomUUID: () => 'review-key' },
        fetch: async (...args) => { calls.push(args); return fetcher(...args); } });
    return { root, nodes, document, calls, api: window.ReviewMedicalClearance, setDecision(value) { decision = value; document.emit('change', { target: { name: 'applicant_clearance_decision' } }); } };
}
test('purpose is required for pending student and employee approval only', async () => {
    const h=harness();
    for(const type of ['applicant','dependent','guest']) {await h.api.configure(type,1,true); assert.equal(h.root.hidden,true);}
    await h.api.configure('student',1,false); assert.equal(h.calls.length,0);
    await h.api.configure('student',1,true); assert.equal(h.nodes.purpose.required,true);
    const input=h.api.collect(true);
    assert.equal(input.issue_medical_clearance,true);
    assert.equal(input.medical_clearance.purpose,'On the Job Training');
    assert.equal(input.medical_clearance.issued_on,undefined);
    assert.equal(input.medical_clearance.name,undefined);
    assert.doesNotMatch(source,/node\('enable'\)|node\('patient'\)|node\('date'\)/);
});
test('empty purpose blocks approval; Pending never issues', async () => {
    const h=harness(); await h.api.configure('employee',1,true);
    h.nodes.purpose.value=''; assert.throws(()=>h.api.collect(true),/required/);
    h.setDecision('pending'); assert.equal(h.api.collect(false).issue_medical_clearance,false);
    assert.equal(h.nodes.purpose.disabled,true);
    h.setDecision('approve'); h.nodes.purpose.value='__other__'; h.nodes.purpose.emit('change');
    assert.throws(()=>h.api.collect(true),/required/); h.nodes.other.value='Return to Work';
    assert.equal(h.api.collect(true).medical_clearance.other_purpose,'Return to Work');
});
test('another clearance reuses the same purpose section and retry key without a pending-form GET', () => {
    const h=harness(); h.api.configureApproved(info,'On the Job Training');
    assert.equal(h.calls.length,0); assert.equal(h.nodes.purpose.value,'On the Job Training');
    assert.equal(h.api.collect(true).medical_clearance.request_key,h.api.collect(true).medical_clearance.request_key);
});
test('changing records ignores stale option responses', async () => {
    let finish; const h=harness(()=>new Promise(resolve=>{finish=resolve;}));
    const loading=h.api.configure('student',1,true); h.api.reset();
    finish({ok:true,json:async()=>info}); await loading;
    assert.equal(h.root.hidden,true); assert.equal(h.api.collect(true).issue_medical_clearance,false);
});

test('custom purpose menu selects Others, enforces details and disables for Pending', async () => {
    const h = harness(); await h.api.configure('student', 1, true);
    assert.equal(h.nodes.purpose.hidden, true);
    assert.equal(h.nodes['purpose-trigger'].hidden, false);
    assert.equal(h.nodes.purposeValue.textContent, 'On the Job Training');
    h.nodes['purpose-trigger'].emit('click');
    assert.equal(h.nodes['purpose-menu'].hidden, false);
    h.nodes['purpose-menu'].children.find(button => button.dataset.value === '__other__').emit('click');
    assert.equal(h.nodes.purpose.value, '__other__');
    assert.equal(h.nodes['other-label'].hidden, false);
    assert.equal(h.nodes.other.required, true);
    assert.equal(h.nodes['purpose-menu'].hidden, true);
    assert.throws(() => h.api.collect(true), /required/);
    h.nodes.other.value = 'School requirements';
    assert.equal(h.api.collect(true).medical_clearance.other_purpose, 'School requirements');
    h.setDecision('pending');
    assert.equal(h.nodes['purpose-trigger'].disabled, true);
    h.setDecision('approve');
    assert.equal(h.nodes['purpose-trigger'].disabled, false);
    h.nodes['purpose-menu'].children.find(button => button.dataset.value === '').emit('click');
    assert.throws(() => h.api.collect(true), /required/);
    assert.equal(h.nodes['purpose-trigger'].focused, true);
    h.nodes['purpose-trigger'].emit('click'); h.api.reset();
    assert.equal(h.nodes['purpose-menu'].hidden, true);
});
