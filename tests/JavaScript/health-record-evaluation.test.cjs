const { test } = require('node:test');
const assert = require('node:assert/strict');
const fs = require('node:fs');
const path = require('node:path');
const vm = require('node:vm');
const source = fs.readFileSync(path.join(__dirname, '../../public/js/health-record-evaluation.js'), 'utf8');

function element() {
    const handlers = {};
    return { dataset: {}, handlers, disabled: false, hidden: true,
        addEventListener: (name, callback) => { handlers[name] = callback; },
        emit: (name, event = {}) => handlers[name]?.(event),
        focus() {},
    };
}

function harness(fetcher) {
    const modal = element();
    const send = element();
    const cancel = element();
    const status = element();
    const trigger = element();
    const calls = [];
    const toasts = [];
    modal.dataset.recordEvaluationUrl = '/health-profile/42/send-evaluation';
    modal.open = false;
    modal.showModal = () => { modal.open = true; };
    modal.close = () => { modal.open = false; modal.emit('close'); };
    modal.querySelector = selector => ({
        '[data-record-evaluation-send]': send,
        '[data-record-evaluation-cancel]': cancel,
        '[data-record-evaluation-status]': status,
    })[selector];
    vm.runInNewContext(source, {
        document: {
            readyState: 'complete',
            getElementById: id => id === 'recordEvaluationModal' ? modal : null,
            querySelectorAll: () => [trigger],
            querySelector: selector => selector.startsWith('meta') ? { content: 'csrf-test' } : null,
        },
        window: { showAdminToast: (...args) => toasts.push(args) },
        fetch: async (...args) => { calls.push(args); return fetcher(...args); },
    });
    return { modal, send, cancel, status, trigger, calls, toasts };
}

test('opening and cancelling the confirmation never sends an email', () => {
    const h = harness(() => { throw new Error('Unexpected request'); });
    h.trigger.emit('click');
    assert.equal(h.modal.open, true);
    assert.equal(h.calls.length, 0);
    h.cancel.emit('click');
    assert.equal(h.modal.open, false);
    assert.equal(h.calls.length, 0);
});

test('Send makes one request and prevents duplicate clicks or closing while sending', async () => {
    let complete;
    const result = new Promise(resolve => { complete = resolve; });
    const h = harness(() => result);
    h.trigger.emit('click');
    const pending = h.send.emit('click');
    await h.send.emit('click');
    assert.equal(h.calls.length, 1);
    assert.equal(h.send.textContent, 'Sending...');
    assert.equal(h.send.disabled, true);
    h.cancel.emit('click');
    assert.equal(h.modal.open, true);
    let prevented = false;
    h.modal.emit('cancel', { preventDefault() { prevented = true; } });
    assert.equal(prevented, true);
    complete({ ok: true, json: async () => ({ success: true, message: 'Link sent.' }) });
    await pending;
    assert.equal(h.calls[0][1].method, 'POST');
    assert.equal(h.calls[0][1].headers['X-CSRF-TOKEN'], 'csrf-test');
    assert.equal(h.modal.open, false);
    assert.equal(h.send.disabled, false);
    assert.equal(h.send.textContent, 'Send');
    assert.deepEqual(h.toasts, [['success', 'Link sent.']]);
});

test('delivery failures stay in the modal and allow retry', async () => {
    const h = harness(async () => ({ ok: false, json: async () => ({ success: false, message: 'Email could not be sent.' }) }));
    h.trigger.emit('click');
    await h.send.emit('click');
    assert.equal(h.modal.open, true);
    assert.equal(h.status.hidden, false);
    assert.equal(h.status.textContent, 'Email could not be sent.');
    assert.equal(h.send.disabled, false);
});
