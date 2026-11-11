const { test } = require('node:test');
const assert = require('node:assert/strict');
const fs = require('node:fs');
const vm = require('node:vm');
const source = fs.readFileSync(require('node:path').join(__dirname, '../../public/js/approved-clearance-review.js'), 'utf8');
const dropdownSource = fs.readFileSync(require('node:path').join(__dirname, '../../public/js/category-bubble-select.js'), 'utf8');
function element() {
    const handlers = {};
    return { hidden: true, disabled: false, value: '', textContent: '', children: [], attributes: {}, style: {}, dataset: {}, offsetHeight: 250, offsetWidth: 350,
        classList: { toggle() {} }, addEventListener(name, fn) { handlers[name] = fn; }, emit(name, event = { preventDefault() {} }) { return handlers[name]?.(event); },
        setAttribute(name, value) { this.attributes[name] = value; }, toggleAttribute() {},
        append(child) { this.children.push(child); }, replaceChildren() { this.children = []; }, focus() {}, contains(target) { return target === this; },
        getBoundingClientRect() { return { top: 400, bottom: 430, left: 160 }; }, showPopover() { this.open = true; }, hidePopover() { this.open = false; },
    };
}
const data = { can_issue: true, submission_id: 11, identity: { name: 'Juan Dela Cruz', age: 22, sex: 'M', birth_date: '2004-01-15' }, today: '2026-11-11',
    health_form_categories: ['On the Job Training', 'Regular'], purposes: ['On the Job Training', 'Regular'] };
const response = (data, ok = true) => ({ ok, json: async () => data });
function harness(fetcher = () => response(data)) {
    const root = element(), result = element(), document = element(), title = element();
    const keys = ['bubble', 'form', 'open', 'prompt', 'category', 'category-trigger', 'category-menu', 'continue', 'bubble-status', 'close', 'patient', 'selected', 'date', 'findings', 'decision', 'purpose', 'other-label', 'other', 'remarks', 'status', 'back', 'issue', 'document'];
    const nodes = Object.fromEntries(keys.map(key => [key, element()]));
    root.dataset.base = '/admin/medical-clearances';
    nodes['category-trigger'].querySelector = () => nodes['category-trigger'].label ||= element();
    root.querySelector = selector => nodes[selector.slice('[data-approved-clearance-'.length, -1)]; root.closest = () => result;
    nodes.form.reportValidity = () => true; nodes.form.reset = () => { for (const key of ['other', 'remarks', 'decision']) nodes[key].value = ''; nodes.findings.value = 'No Findings / Normal'; };
    document.querySelector = selector => selector.startsWith('meta') ? { content: 'csrf' } : root; document.getElementById = () => title; document.createElement = () => element();
    const calls = [], window = { innerWidth: 1000, innerHeight: 800, addEventListener() {} }; let uuid = 0;
    const context = vm.createContext({ window, document, crypto: { randomUUID: () => `uuid-${++uuid}` }, fetch: async (url, options) => { calls.push([url, options]); return fetcher(url, options); } });
    vm.runInContext(dropdownSource, context); vm.runInContext(source, context);
    return { root, nodes, calls, title, configure: window.ApprovedClearanceReview.configure,
        async enter(type = 'student') { this.configure(type, 5); await nodes.open.emit('click'); nodes.category.value = 'On the Job Training'; nodes.continue.emit('click'); },
        submit() { return nodes.form.emit('submit'); } };
}
test('only student and employee approved records expose the action', async () => {
    const h = harness();
    for (const type of ['applicant', 'dependent', 'guest', null]) { h.configure(type, 5); assert.equal(h.root.hidden, true); await h.nodes.open.emit('click'); }
    assert.equal(h.calls.length, 0);
    for (const type of ['student', 'employee']) { h.configure(type, 5); assert.equal(h.root.hidden, false); }
});
test('bubble lists health form categories, and Continue enters Doctor Review without writing', async () => {
    const h = harness(); await h.enter();
    assert.equal(h.calls.length, 1); assert.equal(h.calls[0][1].method, undefined);
    assert.equal(h.nodes.category.children[1].textContent, 'On the Job Training'); assert.equal(h.nodes.bubble.hidden, true);
    assert.equal(h.nodes.form.hidden, false); assert.equal(h.title.textContent, 'Doctor Review');
    assert.equal(h.nodes.patient.value, 'Juan Dela Cruz  22 / M'); assert.equal(h.nodes.purpose.value, 'On the Job Training');
});
test('not cleared never issues and no approval endpoint is called', async () => {
    const h = harness(); await h.enter(); h.nodes.decision.value = 'pending'; await h.submit();
    assert.equal(h.calls.length, 1); assert.match(h.nodes.status.textContent, /No clearance issued/);
});
test('custom dropdown selects category into Doctor Review and closes its menu', async () => {
    const h = harness(); h.configure('student', 5); await h.nodes.open.emit('click');
    assert.equal(h.nodes.category.hidden, true); assert.equal(h.nodes['category-trigger'].hidden, false);
    h.nodes['category-trigger'].emit('click'); assert.equal(h.nodes['category-menu'].hidden, false);
    h.nodes['category-menu'].children[1].emit('click');
    assert.equal(h.nodes.category.value, 'On the Job Training'); assert.equal(h.nodes['category-menu'].hidden, true);
    h.nodes.continue.emit('click'); assert.equal(h.nodes.selected.value, 'On the Job Training');
});
test('cleared review issues only a certificate, disables duplicate submit and shows reprint', async () => {
    let finish; const saving = new Promise(resolve => { finish = resolve; });
    const h = harness((url, options) => options.method === 'POST' ? saving : response(data));
    await h.enter(); h.nodes.decision.value = 'issue'; h.nodes.remarks.value = 'Reviewed';
    const pending = h.submit(); await h.submit();
    assert.equal(h.calls.length, 2); assert.equal(h.nodes.issue.disabled, true); assert.match(h.calls[1][0], /medical-clearances\/student\/5$/);
    const body = JSON.parse(h.calls[1][1].body);
    assert.equal(body.submission_id, 11); assert.equal(body.doctor_review.category, 'On the Job Training'); assert.equal(body.doctor_review.remarks, 'Reviewed');
    assert.equal(h.calls[1][1].headers['X-CSRF-TOKEN'], 'csrf');
    finish(response({ document: { url: '/saved.pdf' } })); await pending; await h.submit();
    assert.equal(h.calls.length, 2); assert.equal(h.nodes.document.hidden, false); assert.equal(h.nodes.document.href, '/saved.pdf');
});
test('failed issue retains the retry key; stale options cannot populate another patient', async () => {
    const h = harness((url, options) => options.method === 'POST' ? response({ message: 'Try again' }, false) : response(data));
    await h.enter(); h.nodes.decision.value = 'issue'; await h.submit(); await h.submit();
    assert.equal(h.nodes.issue.disabled, false); assert.equal(JSON.parse(h.calls[1][1].body).request_key, JSON.parse(h.calls[2][1].body).request_key);
    let finish; const stale = harness(() => new Promise(resolve => { finish = resolve; }));
    stale.configure('student', 5); const loading = stale.nodes.open.emit('click'); stale.configure('employee', 8); finish(response(data)); await loading;
    assert.equal(stale.nodes.bubble.hidden, true); assert.equal(stale.nodes.category.children.length, 0);
});
test('unavailable approved record cannot proceed and Others toggles its input', async () => {
    const h = harness(() => response({ ...data, can_issue: false })); h.configure('student', 5); await h.nodes.open.emit('click');
    assert.equal(h.nodes.continue.disabled, true); assert.match(h.nodes['bubble-status'].textContent, /No eligible approved/);
    assert.equal(h.nodes.category.children[1].textContent, 'On the Job Training');
    h.nodes.category.value = 'On the Job Training'; h.nodes.continue.emit('click'); assert.equal(h.nodes.form.hidden, true);
    const active = harness(); await active.enter(); active.nodes.purpose.value = '__other__'; active.nodes.purpose.emit('change');
    assert.equal(active.nodes.other.required, true); assert.equal(active.nodes.other.disabled, false);
});
