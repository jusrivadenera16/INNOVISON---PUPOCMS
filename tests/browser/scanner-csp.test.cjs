const assert = require('node:assert/strict');
const { execFileSync } = require('node:child_process');
const fs = require('node:fs/promises');
const http = require('node:http');
const path = require('node:path');
const test = require('node:test');
const { chromium } = require(process.env.CSP_PLAYWRIGHT_MODULE || 'playwright');

const root = path.resolve(__dirname, '../..');

test('cold-start OCR and camera scanning work with the strict middleware CSP', { timeout: 90000 }, async () => {
    const { policy } = JSON.parse(execFileSync(process.env.PHP_BINARY || 'php', [path.join(root, 'tests/fixtures/csp-response.php')], { encoding: 'utf8' }));
    const source = await fs.readFile(path.join(root, 'resources/views/admin/walkin.blade.php'), 'utf8');
    const start = source.indexOf('        async function getOcrWorker()');
    const end = source.indexOf('        function capturePreparedIdCanvas(', start);
    assert.ok(start >= 0 && end > start);
    const workerSource = 'let ocrWorkerPromise = null;' + source.slice(start, end);
    const server = http.createServer((request, response) => {
        response.writeHead(200, { 'Content-Type': 'text/html', 'Content-Security-Policy': policy });
        response.end('<!doctype html><html><body><h1>Scanner Fixture</h1><div id="qr-reader"></div></body></html>');
    });
    let browser;
    try {
        await new Promise(resolve => server.listen(0, '127.0.0.1', resolve));
        browser = await chromium.launch({ headless: true, ...(process.env.CSP_BROWSER_EXECUTABLE ? { executablePath: process.env.CSP_BROWSER_EXECUTABLE } : {}), args: ['--use-fake-ui-for-media-stream', '--use-fake-device-for-media-stream'] });
        const context = await browser.newContext({ serviceWorkers: 'block', permissions: ['camera'] });
        if (process.env.CSP_SCANNER_ASSET_DIR) {
            await context.route(/https:\/\/(?:cdn\.jsdelivr\.net|unpkg\.com)\//, async route => {
                const name = path.basename(new URL(route.request().url()).pathname);
                assert.ok(['tesseract.min.js', 'worker.min.js', 'tesseract-core-simd-lstm.wasm.js', 'tesseract-core-simd-lstm.wasm', 'eng.traineddata.gz', 'html5-qrcode.min.js'].includes(name), 'Unexpected scanner dependency: ' + name);
                await route.fulfill({ status: 200, headers: { 'Access-Control-Allow-Origin': '*', 'Content-Type': name.endsWith('.wasm') ? 'application/wasm' : name.endsWith('.js') ? 'text/javascript' : 'application/octet-stream' }, body: await fs.readFile(path.join(process.env.CSP_SCANNER_ASSET_DIR, name)) });
            });
        }
        const page = await context.newPage();
        const errors = [];
        page.on('pageerror', error => errors.push(error.message));
        await page.addInitScript(() => {
            window.cspViolations = [];
            document.addEventListener('securitypolicyviolation', event => window.cspViolations.push({ directive: event.effectiveDirective, blocked: event.blockedURI }));
        });
        await page.goto(`http://127.0.0.1:${server.address().port}`);
        const tesseractUrl = source.match(/<script src="([^"]*tesseract\.js[^\"]+)"/)[1];
        await page.addScriptTag({ url: tesseractUrl });
        const text = await page.evaluate(workerSource + `;(async () => {
            const worker = await getOcrWorker();
            const canvas = document.createElement('canvas'); canvas.width = 1200; canvas.height = 180;
            const ctx = canvas.getContext('2d'); ctx.fillStyle = 'white'; ctx.fillRect(0, 0, 1200, 180);
            ctx.fillStyle = 'black'; ctx.font = '64px Arial'; ctx.fillText('2026-12345-TG-0 TEST STUDENT', 24, 110);
            const result = await worker.recognize(canvas); await worker.terminate(); return result.data.text;
        })()`);
        assert.match(text, /2026-12345-TG-0 TEST STUDENT/);
        const qrUrl = source.match(/<script src="([^"]*html5-qrcode[^\"]+)"/)[1];
        await page.addScriptTag({ url: qrUrl });
        await page.evaluate(async () => {
            window.fixtureScanner = new Html5Qrcode('qr-reader');
            await window.fixtureScanner.start({ facingMode: 'environment' }, { fps: 20, qrbox: { width: 400, height: 160 }, aspectRatio: 1.777778 }, () => {});
        });
        await page.locator('#qr-reader canvas').waitFor({ state: 'attached' });
        await page.waitForFunction(() => document.querySelector('#qr-reader video')?.readyState >= 2);
        await page.evaluate(async () => { await window.fixtureScanner.stop(); window.fixtureScanner.clear(); });
        assert.deepEqual(await page.evaluate(() => window.cspViolations), []);
        assert.deepEqual(errors, []);
    } finally {
        if (browser) await browser.close();
        await new Promise(resolve => server.close(resolve));
    }
});
