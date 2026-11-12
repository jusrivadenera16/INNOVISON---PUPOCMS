const { test } = require('node:test');
const assert = require('node:assert/strict');
const fs = require('node:fs');
const path = require('node:path');
const vm = require('node:vm');
const source = fs.readFileSync(path.join(__dirname, '../../public/js/medical-clearance.js'), 'utf8');

function node() {
    const handlers = {};
    return { dataset: {}, hidden: false, disabled: false, required: false, value: '', children: [], textContent: '',
        addEventListener(name, handler) { handlers[name] = handler; },
        emit(name, event = {}) { return handlers[name]?.(event); },
        append(...children) { this.children.push(...children); },
        replaceChildren(...children) { this.children = children; },
    };
}
const info = overrides => ({ can_issue: true, identity: { name: 'Juan Dela Cruz', age: 22, sex: 'M', birth_date: '2004-01-15' },
    today: '2026-11-11', purposes: ['On the Job Training'], default_purpose: 'On the Job Training', documents: [], ...overrides });
const response = (data, ok = true) => ({ ok, json: async () => data });
function harness(fetcher) {
    const modal = node(), panel = node(), form = node(), trigger = node(), close = node(), submit = node();
    const status = node(), panelStatus = node(), docs = node(), list = node(), patient = node(), other = node();
    const fields = { issued_on: node(), purpose: node(), other_purpose: node() };
    form.elements = { namedItem: name => fields[name] };
    form.reset = () => Object.values(fields).forEach(field => { field.value = ''; });
    form.reportValidity = () => true;
    modal.dataset.clearanceBase = '/admin/medical-clearances';
    modal.showModal = () => { modal.open = true; };
    modal.close = () => { modal.open = false; };
    modal.querySelector = selector => ({ '[data-clearance-form]': form, '[data-clearance-status]': status,
        '[data-clearance-other]': other, '[data-clearance-patient]': patient })[selector];
    modal.querySelectorAll = selector => selector === 'button' ? [submit, close] : [close];
    panel.querySelector = selector => ({ '[data-clearance-open]': trigger, '[data-clearance-list]': list,
        '[data-clearance-documents]': docs, '[data-clearance-panel-status]': panelStatus })[selector];
    trigger.closest = () => panel;
    const document = node();
    document.getElementById = () => modal;
    document.querySelectorAll = () => [];
    document.querySelector = () => ({ content: 'csrf-token' });
    document.createElement = () => node();
    const calls = [];
    const window = {};
    vm.runInNewContext(source, { window, document, crypto: { randomUUID: () => 'c5968c2a-b64c-4ff4-81c7-10ccfb832f59' },
        fetch: async (url, options) => { calls.push([url, options]); return fetcher(url, options); } });
    return { modal, panel, form, trigger, close, submit, status, panelStatus, docs, list, patient, other, fields, document, calls,
        load: url => window.ClinicMedicalClearance.load(panel, url),
        open: () => document.emit('click', { target: { closest: () => trigger } }),
        send: () => form.emit('submit', { preventDefault() {} }),
    };
}

test('lookup/open/cancel do not issue a document; issued section is absent until an actual issuance', async () => {
    const h = harness(() => response(info()));
    await h.load('/admin/medical-clearances/student/12');
    assert.equal(h.trigger.hidden, false);
    assert.equal(h.docs.hidden, true);
    h.open();
    assert.equal(h.modal.open, true);
    assert.equal(h.patient.value, 'Juan Dela Cruz  22 / M');
    assert.equal(h.fields.purpose.value, 'On the Job Training');
    h.close.emit('click');
    assert.equal(h.modal.open, false);
    assert.equal(h.calls.length, 1);
    assert.equal(h.calls[0][1].method, undefined);
});

test('approved status documents-only panel stays hidden without documents and shows saved documents only', async () => {
    let documents = [];
    const h = harness(() => response(info({ documents })));
    h.panel.dataset.clearanceDocumentsOnly = 'true';
    const loading = h.load('/record');
    assert.equal(h.panel.hidden, true);
    await loading;
    assert.equal(h.panel.hidden, true);
    assert.equal(h.trigger.hidden, true);
    documents = [{ control_number: 'MC-1', purpose: 'OJT', issued_on: 'Nov 11, 2026', url: '/saved.pdf' }];
    await h.load('/record');
    assert.equal(h.panel.hidden, false);
    assert.equal(h.docs.hidden, false);
    assert.equal(h.trigger.hidden, true);
    assert.equal(h.list.children.length, 1);
});

test('Others requires custom text and changing the date updates the displayed age', async () => {
    const h = harness(() => response(info()));
    await h.load('/record');
    h.open();
    h.fields.purpose.value = '__other__';
    h.fields.purpose.emit('change');
    assert.equal(h.other.hidden, false);
    assert.equal(h.fields.other_purpose.required, true);
    assert.equal(h.fields.other_purpose.disabled, false);
    h.fields.issued_on.value = '2026-01-14';
    h.fields.issued_on.emit('change');
    assert.equal(h.patient.value, 'Juan Dela Cruz  21 / M');
});

test('unnumbered certificates show only purpose and date in Documents', async () => {
    const h = harness(() => response(info({ documents: [
        { control_number: null, purpose: 'OJT', issued_on: 'Nov 11, 2026', url: '/saved.pdf' },
    ] })));
    await h.load('/record');
    assert.equal(h.list.children[0].children[0].textContent, 'OJT - Nov 11, 2026');
});

test('Generate saves only once while busy and then shows the saved View/Reprint link', async () => {
    let finish;
    const pending = new Promise(resolve => { finish = resolve; });
    const h = harness((url, options) => options.method === 'POST' ? pending : response(info()));
    await h.load('/admin/medical-clearances/student/12');
    h.open();
    const sending = h.send();
    await h.send();
    assert.equal(h.calls.length, 2);
    assert.equal(h.submit.disabled, true);
    h.close.emit('click');
    assert.equal(h.modal.open, true);
    let prevented = false;
    h.modal.emit('cancel', { preventDefault() { prevented = true; } });
    assert.equal(prevented, true);
    const payload = JSON.parse(h.calls[1][1].body);
    assert.equal(payload.purpose, 'On the Job Training');
    assert.equal(payload.name, undefined);
    assert.equal(payload.age, undefined);
    assert.equal(h.calls[1][1].headers['X-CSRF-TOKEN'], 'csrf-token');
    finish(response({ message: 'Medical Clearance issued and saved.', document: { id: 1, control_number: 'MC-2026-000001', purpose: 'OJT', issued_on: 'Nov 11, 2026', url: '/saved-pdf' } }));
    await sending;
    assert.equal(h.modal.open, false);
    assert.equal(h.docs.hidden, false);
    assert.equal(h.list.children.length, 1);
    assert.equal(h.list.children[0].children[1].href, '/saved-pdf');
    assert.equal(h.panelStatus.dataset.success, 'true');
});

test('failed issuance stays open and retries the same key instead of creating a duplicate', async () => {
    const h = harness((url, options) => options.method === 'POST' ? response({ errors: { purpose: ['Select a purpose.'] } }, false) : response(info()));
    await h.load('/record');
    h.open();
    await h.send();
    assert.equal(h.modal.open, true);
    assert.equal(h.status.textContent, 'Select a purpose.');
    assert.equal(h.submit.disabled, false);
    await h.send();
    assert.equal(JSON.parse(h.calls[1][1].body).request_key, JSON.parse(h.calls[2][1].body).request_key);
    assert.equal(h.docs.hidden, true);
});

test('record without issue permission can still reprint already issued documents', async () => {
    const h = harness(() => response(info({ can_issue: false, documents: [{ id: 1, control_number: 'MC-1', purpose: 'Annual', issued_on: 'Nov 11', url: '/saved' }] })));
    await h.load('/record');
    assert.equal(h.trigger.hidden, true);
    assert.equal(h.docs.hidden, false);
    h.open();
    assert.notEqual(h.modal.open, true);
});

test('late responses for the previous selected user cannot show their documents on the next record', async () => {
    let finish;
    const first = new Promise(resolve => { finish = resolve; });
    const h = harness(url => url === '/first' ? first : response(info({ can_issue: false })));
    const loadingFirst = h.load('/first');
    await h.load('/second');
    finish(response(info({ documents: [{ id: 1, control_number: 'OLD', purpose: 'Old user', issued_on: 'Nov 11', url: '/old' }] })));
    await loadingFirst;
    assert.equal(h.docs.hidden, true);
    assert.equal(h.trigger.hidden, true);
    assert.equal(h.list.children.length, 0);
});
