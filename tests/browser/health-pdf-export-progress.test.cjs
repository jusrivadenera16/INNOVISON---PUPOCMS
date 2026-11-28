const assert = require('node:assert/strict');
const fs = require('node:fs');
const path = require('node:path');
const test = require('node:test');

test('PDF progress uses the real browser fetch flow and strict CSP in both report workspaces', {
    skip: !process.env.CSP_PLAYWRIGHT_MODULE, timeout: 30000,
}, async () => {
    const { chromium } = require(process.env.CSP_PLAYWRIGHT_MODULE);
    const root = path.resolve(__dirname, '../..');
    const source = fs.readFileSync(path.join(root, 'resources/views/admin/reports/health-forms-pdf-progress.blade.php'), 'utf8');
    const script = fs.readFileSync(path.join(root, 'public/js/health-pdf-export-progress.js'), 'utf8');
    const browser = await chromium.launch({ headless: true, executablePath: process.env.CSP_BROWSER_EXECUTABLE });
    try {
        for (const role of ['admin', 'assistant']) {
            const page = await browser.newPage();
            await page.clock.install();
            const errors = []; page.on('pageerror', error => errors.push(error.message));
            const base = `http://127.0.0.1/${role}/reports/health-forms/pdf`;
            const id = '8bd93dde-9ea4-45f4-bc90-38af1a5921de';
            const states = [{ status: 'queued' }, { status: 'preparing', scanned: 100, source_total: 100, total: 75 },
                { status: 'rendering', rendered: 25, total: 75 }, { status: 'merging' }, { status: 'ready', total: 75, pages: 13 }];
            let posts = 0;
            await page.route(base + '**', route => {
                if (route.request().method() === 'POST') {
                    posts++;
                    assert.equal(route.request().headers()['x-csrf-token'], 'test-token');
                    assert.equal(route.request().postDataJSON().date_from, '2026-01-01');
                    return route.fulfill({ json: { id }, status: 202 });
                }
                return route.fulfill({ json: states.shift() });
            });
            const content = source.match(/@section\('content'\)([\s\S]*?)@endsection/)[1]
                .replace('{{ $startUrl }}', base).replace('{{ json_encode($filters) }}', '{&quot;date_from&quot;:&quot;2026-01-01&quot;,&quot;date_to&quot;:&quot;2026-11-28&quot;}')
                .replace("{{ $exportId ?? '' }}", '').replaceAll("{{ $filters['date_from'] }}", '2026-01-01')
                .replaceAll("{{ $filters['date_to'] }}", '2026-11-28').replace('{{ $backUrl }}', '/reports');
            await page.route('http://127.0.0.1/progress', route => route.fulfill({ contentType: 'text/html', body: `<html><head>
                <meta name="csrf-token" content="test-token"><meta http-equiv="Content-Security-Policy" content="default-src 'none'; script-src 'nonce-test'; connect-src 'self'; script-src-attr 'none'; style-src-attr 'none'">
                </head><body>${content}<script nonce="test">window.violations=[];addEventListener('securitypolicyviolation', e=>violations.push(e.effectiveDirective));${script}</script></body></html>` }));
            await page.goto('http://127.0.0.1/progress');
            await page.locator('#healthPdfPhase').filter({ hasText: 'Waiting for the PDF worker' }).waitFor();
            for (const phase of ['Checking and sorting records', 'Generating PDF batches', 'Combining your PDF', 'Your PDF is ready']) {
                await page.clock.runFor(3001);
                await page.locator('#healthPdfPhase').filter({ hasText: phase }).waitFor();
            }
            assert.equal(await page.locator('#healthPdfMeter').evaluate(node => node.value), 100);
            assert.equal(await page.locator('#healthPdfDownload').getAttribute('href'), base + '/' + id + '/download');
            assert.equal(posts, 1);
            assert.equal(states.length, 0);
            assert.deepEqual(await page.evaluate(() => violations), []);
            assert.deepEqual(errors, []);
            await page.close();
        }
    } finally { await browser.close(); }
});
