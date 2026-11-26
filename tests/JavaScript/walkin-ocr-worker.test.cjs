const assert = require('node:assert/strict');
const fs = require('node:fs');
const path = require('node:path');
const test = require('node:test');
const vm = require('node:vm');

const source = fs.readFileSync(path.join(__dirname, '../../resources/views/admin/walkin.blade.php'), 'utf8');
const start = source.indexOf('        async function getOcrWorker()');
const end = source.indexOf('        function capturePreparedIdCanvas(', start);
assert.ok(start >= 0 && end > start);
const script = 'let ocrWorkerPromise = null;' + source.slice(start, end);

test('concurrent OCR requests share the same initialized worker and pinned resources', async () => {
    const calls = [];
    const parameters = [];
    const worker = { async setParameters(value) { parameters.push(value); } };
    const context = vm.createContext({ Tesseract: { async createWorker(...args) { calls.push(args); return worker; } } });
    vm.runInContext(script, context);
    const result = await Promise.all([context.getOcrWorker(), context.getOcrWorker()]);
    assert.equal(calls.length, 1);
    assert.equal(result[0], worker);
    assert.equal(result[1], worker);
    assert.equal(calls[0][0], 'eng');
    assert.equal(calls[0][1], 1);
    assert.equal(calls[0][2].workerPath, 'https://cdn.jsdelivr.net/npm/tesseract.js@5.1.1/dist/worker.min.js');
    assert.equal(calls[0][2].corePath, 'https://cdn.jsdelivr.net/npm/tesseract.js-core@5.1.1');
    assert.equal(calls[0][2].langPath, 'https://cdn.jsdelivr.net/npm/@tesseract.js-data/eng/4.0.0_best_int');
    assert.equal(parameters[0].preserve_interword_spaces, '1');
});

test('a temporary OCR initialization failure allows the next scan to retry', async () => {
    let attempts = 0;
    const worker = {};
    const context = vm.createContext({ Tesseract: { async createWorker() {
        if (++attempts === 1) throw new Error('Temporary worker download failure');
        return worker;
    } } });
    vm.runInContext(script, context);
    await assert.rejects(context.getOcrWorker(), /Temporary worker download failure/);
    assert.equal(await context.getOcrWorker(), worker);
    assert.equal(attempts, 2);
});
