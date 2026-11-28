const assert = require('node:assert/strict');
const fs = require('node:fs');
const path = require('node:path');
const vm = require('node:vm');
const test = require('node:test');
const script = fs.readFileSync(path.resolve(__dirname, '../../public/js/health-pdf-export-progress.js'), 'utf8');

function fixture(responses, existingId = '') {
    const elements = {};
    for (const id of ['healthPdfProgress', 'healthPdfPhase', 'healthPdfMessage', 'healthPdfMeter', 'healthPdfRetry', 'healthPdfDownload']) {
        elements[id] = { textContent: '', hidden: true, value: 0, addEventListener(name, callback) { this[name] = callback; } };
    }
    elements.healthPdfProgress.dataset = { startUrl: 'https://clinic.test/admin/reports/health-forms/pdf', exportId: existingId,
        filters: JSON.stringify({ date_from: '2026-01-01', date_to: '2026-11-28', condition_keyword: 'sample' }) };
    const calls = [], timers = [], events = {}, history = [];
    vm.runInNewContext(script, {
        URL, encodeURIComponent, Error, JSON, Number,
        document: { getElementById: id => elements[id], querySelector: () => ({ content: 'csrf-token' }) },
        window: { location: { href: 'https://clinic.test/admin/reports/health-forms/export?output=pdf' },
            history: { replaceState: (...args) => history.push(args) }, addEventListener: (name, callback) => { events[name] = callback; } },
        fetch: async (url, options) => { calls.push({ url, options }); const result = responses.shift();
            if (result instanceof Error) throw result;
            assert.ok(result, 'No unexpected extra request');
            return { ok: true, status: 200, redirected: false, json: async () => result.body ?? result, ...result.response }; },
        setTimeout: (callback, delay) => { timers.push({ callback, delay }); return timers.length; }, clearTimeout: () => {},
    });
    return { elements, calls, timers, events, history };
}
const flush = () => new Promise(resolve => setImmediate(resolve));

test('starts once with CSRF, polls every phase, and exposes exactly one final PDF link', async () => {
    const h = fixture([{ id: 'export-1' }, { status: 'queued' }, { status: 'preparing', scanned: 100, source_total: 200, total: 70 },
        { status: 'rendering', rendered: 25, total: 70 }, { status: 'merging' }, { status: 'ready', total: 70, pages: 12 }]);
    await flush();
    assert.equal(h.calls[0].options.method, 'POST');
    assert.equal(h.calls[0].options.headers['X-CSRF-TOKEN'], 'csrf-token');
    assert.equal(h.calls[0].options.credentials, 'same-origin');
    assert.equal(JSON.parse(h.calls[0].options.body).date_from, '2026-01-01');
    assert.equal(h.history.length, 1);
    assert.match(String(h.history[0][2]), /pdf_export=export-1/);
    for (let phase = 0; phase < 4; phase++) {
        const timer = h.timers.shift(); assert.equal(timer.delay, 3000); await timer.callback(); await flush();
    }
    assert.equal(h.elements.healthPdfPhase.textContent, 'Your PDF is ready');
    assert.equal(h.elements.healthPdfMeter.value, 100);
    assert.equal(h.elements.healthPdfDownload.hidden, false);
    assert.match(h.elements.healthPdfDownload.href, /export-1\/download$/);
    assert.equal(h.timers.length, 0, 'Stop polling when ready.');
    assert.equal(h.calls.filter(call => call.options.method === 'POST').length, 1);
});

test('reload resumes an existing export without creating a duplicate', async () => {
    const h = fixture([{ status: 'ready', total: 0, pages: 1 }], 'existing-export');
    await flush();
    assert.equal(h.calls.length, 1);
    assert.ok(h.calls[0].url.endsWith('/existing-export'));
    assert.equal(h.calls[0].options.method, undefined);
    assert.equal(h.elements.healthPdfDownload.hidden, false);
});

test('failed job stops polling and retry creates a fresh export', async () => {
    const h = fixture([{ status: 'failed' }, { id: 'new-export' }, { status: 'ready', total: 1, pages: 1 }], 'failed-export');
    await flush();
    assert.equal(h.elements.healthPdfRetry.hidden, false);
    assert.equal(h.timers.length, 0);
    await h.elements.healthPdfRetry.click(); await flush();
    assert.equal(h.calls[1].options.method, 'POST');
    assert.ok(h.elements.healthPdfDownload.href.endsWith('/new-export/download'));
});

test('session expiration and HTTP errors stop safely without exposing a download', async () => {
    for (const status of [401, 419, 403, 410, 503]) {
        const h = fixture([{ body: { message: 'Export unavailable' }, response: { ok: false, status } }], 'existing');
        await flush();
        assert.equal(h.elements.healthPdfRetry.hidden, false);
        assert.equal(h.elements.healthPdfDownload.hidden, true);
        assert.equal(h.timers.length, 0);
        assert.match(h.elements.healthPdfMessage.textContent, [401, 419].includes(status) ? /session has expired/ : /Export unavailable/);
    }
});
