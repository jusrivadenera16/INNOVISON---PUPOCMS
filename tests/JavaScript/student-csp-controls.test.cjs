const assert = require('node:assert/strict');
const fs = require('node:fs');
const path = require('node:path');
const test = require('node:test');
const vm = require('node:vm');

const source = fs.readFileSync(path.join(__dirname, '../../public/js/student-csp-controls.js'), 'utf8');

function harness(confirmed = true) {
    const listeners = new Map();
    const calls = [];
    class HTMLFormElement {
        constructor(attributes = {}) { this.attributes = attributes; this.dataset = { studentConfirmSubmit: attributes['data-student-confirm-submit'] }; }
        hasAttribute(name) { return Object.hasOwn(this.attributes, name); }
    }
    const window = { confirm: message => { calls.push(['confirm', message]); return confirmed; },
        location: { reload: () => calls.push(['reload']) }, print: () => calls.push(['print']), close: () => calls.push(['close-window']) };
    for (const name of ['openProfileEditModal', 'openHealthRecordModal', 'closeHealthRecordModal', 'openMissingESignModal', 'closeMissingESignModal', 'closeMissingDocumentModal', 'closeNotificationSettingsModal', 'closeGlobalResubmissionModal']) {
        window[name] = (...args) => calls.push([name, ...args]);
    }
    const document = { addEventListener: (name, listener, capture) => listeners.set(name, { listener, capture }),
        getElementById: id => ({ showModal: () => calls.push(['showModal', id]), style: { setProperty: (...args) => calls.push(['style', id, ...args]) } }) };
    vm.runInNewContext(source, { document, window, HTMLFormElement });
    function dispatch(name, target) {
        const event = { target, defaultPrevented: false, stopped: false,
            preventDefault() { this.defaultPrevented = true; }, stopImmediatePropagation() { this.stopped = true; } };
        listeners.get(name).listener(event);
        return event;
    }
    function click(action, extra = {}) {
        const button = { dataset: { studentAction: action, ...extra }, closest: () => ({ close: () => calls.push(['close-dialog']) }) };
        return dispatch('click', { closest: () => button });
    }
    return { calls, listeners, HTMLFormElement, dispatch, click };
}

test('student dialog commands still work from nested icons without executable attributes', () => {
    const h = harness();
    for (const action of ['open-health-record', 'close-health-record', 'open-signature', 'close-signature', 'close-document', 'close-notification-settings', 'close-resubmission']) h.click(action);
    assert.deepEqual(h.calls.map(call => call[0]), ['openHealthRecordModal', 'closeHealthRecordModal', 'openMissingESignModal', 'closeMissingESignModal', 'closeMissingDocumentModal', 'closeNotificationSettingsModal', 'closeGlobalResubmissionModal']);
});

test('profile permissions remain a boolean argument and signature replacement closes the previous dialog first', () => {
    const h = harness();
    h.click('edit-profile', { profileEditAllowed: 'true' });
    h.click('edit-profile', { profileEditAllowed: 'false' });
    h.click('replace-signature');
    assert.deepEqual(h.calls, [['openProfileEditModal', true], ['openProfileEditModal', false], ['closeMissingDocumentModal'], ['openMissingESignModal']]);
});

test('native dialogs, health prompt dismissal, print, close, and reload preserve their actions', () => {
    const h = harness();
    for (const action of ['request-health-form', 'close-dialog', 'dismiss-health-form', 'print', 'close-window', 'reload']) h.click(action);
    assert.deepEqual(h.calls, [['showModal', 'studentNewCycleDialog'], ['close-dialog'], ['style', 'healthFormModal', 'display', 'none', 'important'], ['print'], ['close-window'], ['reload']]);
});

test('cancelling a correction request prevents submission before navigation loaders run', () => {
    const h = harness(false);
    const event = h.dispatch('submit', new h.HTMLFormElement({ 'data-student-confirm-submit': 'Cancel this request?' }));
    assert.equal(h.listeners.get('submit').capture, true);
    assert.equal(event.defaultPrevented, true);
    assert.equal(event.stopped, true);
    assert.deepEqual(h.calls, [['confirm', 'Cancel this request?']]);
});

test('accepting confirmation and normal form submission remain available, while preview submission is blocked', () => {
    const h = harness();
    assert.equal(h.dispatch('submit', new h.HTMLFormElement({ 'data-student-confirm-submit': 'Continue?' })).defaultPrevented, false);
    assert.equal(h.dispatch('submit', new h.HTMLFormElement()).defaultPrevented, false);
    const preview = h.dispatch('submit', new h.HTMLFormElement({ 'data-student-prevent-submit': '' }));
    assert.equal(preview.defaultPrevented, true);
    assert.equal(preview.stopped, false);
    h.dispatch('click', { closest: () => null });
    assert.deepEqual(h.calls, [['confirm', 'Continue?']]);
});
