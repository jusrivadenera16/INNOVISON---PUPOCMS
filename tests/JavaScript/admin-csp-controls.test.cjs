const assert = require('node:assert/strict');
const fs = require('node:fs');
const path = require('node:path');
const test = require('node:test');
const vm = require('node:vm');

const source = fs.readFileSync(path.join(__dirname, '../../public/js/admin-csp-controls.js'), 'utf8');

function harness(confirmed = true) {
    const listeners = new Map();
    const calls = { confirms: [], submissions: 0, reloads: 0, copies: [] };
    class HTMLFormElement {
        constructor(message) { this.dataset = { confirmSubmit: message }; }
        hasAttribute(name) { return name === 'data-confirm-submit' && this.dataset.confirmSubmit !== undefined; }
        requestSubmit() { calls.submissions++; }
    }
    const context = {
        HTMLFormElement,
        document: {
            addEventListener(name, listener, capture) {
                if (!listeners.has(name)) listeners.set(name, []);
                listeners.get(name).push({ listener, capture });
            },
            querySelectorAll: () => [],
            getElementById: () => ({ value: 'Safe source value' }),
        },
        window: {
            confirm(message) { calls.confirms.push(message); return confirmed; },
            location: { reload() { calls.reloads++; } },
        },
        navigator: { clipboard: { writeText(value) { calls.copies.push(value); } } },
    };
    vm.runInNewContext(source, context);
    function dispatch(name, target) {
        const event = {
            target,
            defaultPrevented: false,
            propagationStopped: false,
            preventDefault() { this.defaultPrevented = true; },
            stopImmediatePropagation() { this.propagationStopped = true; },
        };
        for (const { listener } of listeners.get(name) || []) {
            listener(event);
            if (event.propagationStopped) break;
        }
        return event;
    }
    return { calls, HTMLFormElement, dispatch, listeners };
}

test('cancelling a submit blocks deletion before page handlers or navigation loaders run', () => {
    const h = harness(false);
    let pageHandlerRan = false;
    h.listeners.get('submit').push({ listener() { pageHandlerRan = true; } });
    const event = h.dispatch('submit', new h.HTMLFormElement('Delete this record?'));
    assert.equal(h.listeners.get('submit')[0].capture, true);
    assert.equal(event.defaultPrevented, true);
    assert.equal(pageHandlerRan, false);
    assert.deepEqual(h.calls.confirms, ['Delete this record?']);
});

test('accepting confirmation preserves submission, including keyboard submission', () => {
    const h = harness();
    const event = h.dispatch('submit', new h.HTMLFormElement('Restore this record?'));
    assert.equal(event.defaultPrevented, false);
    assert.equal(event.propagationStopped, false);
    assert.deepEqual(h.calls.confirms, ['Restore this record?']);
});

test('forms without a confirmation continue without a dialog', () => {
    const h = harness(false);
    assert.equal(h.dispatch('submit', new h.HTMLFormElement()).defaultPrevented, false);
    assert.deepEqual(h.calls.confirms, []);
});

test('page size changes use requestSubmit so form handlers and validation still run', () => {
    const h = harness();
    const control = { form: new h.HTMLFormElement(), closest() { return this; } };
    h.dispatch('change', control);
    assert.equal(h.calls.submissions, 1);
});

test('reload and copy controls still work from nested icon clicks', () => {
    const h = harness();
    for (const action of ['data-admin-reload', 'data-admin-copy-source']) {
        const button = { hasAttribute(name) { return name === action; } };
        h.dispatch('click', { closest() { return button; } });
    }
    assert.equal(h.calls.reloads, 1);
    assert.deepEqual(h.calls.copies, ['Safe source value']);
});
