const assert = require('node:assert/strict');
const fs = require('node:fs');
const path = require('node:path');
const test = require('node:test');

test('native export filter submissions show the existing screen loader under strict CSP', {
    skip: !process.env.CSP_PLAYWRIGHT_MODULE, timeout: 30000,
}, async () => {
    const { chromium } = require(process.env.CSP_PLAYWRIGHT_MODULE);
    const views = path.resolve(__dirname, '../../resources/views');
    const layout = fs.readFileSync(path.join(views, 'layouts/admin.blade.php'), 'utf8');
    const partial = fs.readFileSync(path.join(views, 'admin/reports/partials/export-report-page.blade.php'), 'utf8');
    const start = layout.indexOf('const resolveActionCopy = function');
    const end = layout.indexOf('const persistNavigationHandoff', start);
    const submitStart = layout.indexOf("document.addEventListener('submit', function (event) {", layout.indexOf('window.AdminLoading ='));
    const submitEnd = layout.indexOf("window.addEventListener('resize'", submitStart);
    const browser = await chromium.launch({ headless: true, executablePath: process.env.CSP_BROWSER_EXECUTABLE });
    try {
        const page = await browser.newPage();
        const errors = []; page.on('pageerror', error => errors.push(error.message));
        for (const role of ['admin', 'assistant']) {
            for (const type of ['mar', 'inventory', 'appointments', 'audit-trail', 'health-forms']) {
                const origin = `http://127.0.0.1/${role}/reports/export-hub/${type}`;
                const form = partial.match(/<form\b[^>]*class="export-filter-form"[\s\S]*?>/)[0]
                    .replace('{{ $filterActionUrl }}', origin).replace('{{ $exportBreadcrumbLabel }}', type);
                const html = `<html><head><meta http-equiv="Content-Security-Policy" content="default-src 'none'; script-src 'nonce-test'; form-action 'self'; script-src-attr 'none'; style-src-attr 'none'"></head><body>
                    <main id="main"></main><div id="content-loader"></div><div id="action-loader" aria-hidden="true"><span data-admin-loader-message-text></span><span data-admin-loader-detail></span></div>
                    ${form}<input name="date_from" type="date" required><button type="submit">Apply Filter</button></form>
                    <script nonce="test">window.violations=[];addEventListener('securitypolicyviolation', e=>violations.push(e.effectiveDirective));
                    const content=document.getElementById('main'), contentLoader=document.getElementById('content-loader'), actionLoader=document.getElementById('action-loader');
                    ${layout.slice(start, end)}${layout.slice(submitStart, submitEnd)}
                    window.addEventListener('submit', e=>e.preventDefault());</script></body></html>`;
                await page.route(origin, route => route.fulfill({ contentType: 'text/html', body: html }));
                await page.goto(origin);
                await page.locator('button').click();
                assert.equal(await page.locator('#action-loader').getAttribute('aria-hidden'), 'true', 'Invalid inputs must not show loading.');
                await page.locator('input').fill('2026-11-28');
                await page.locator('button').click();
                assert.equal(await page.locator('#action-loader').getAttribute('aria-hidden'), 'false');
                const skeleton = { inventory: 'inventory', appointments: 'appointments' }[type] || 'reports';
                assert.equal(await page.locator('#action-loader').getAttribute('data-skeleton-kind'), skeleton);
                assert.equal(await page.locator('[data-admin-loader-message-text]').textContent(), 'Applying report filters');
                assert.deepEqual(await page.evaluate(() => violations), []);
                assert.deepEqual(errors, []);
                await page.unroute(origin);
            }
        }
    } finally { await browser.close(); }
});
