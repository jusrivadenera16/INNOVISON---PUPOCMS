const assert = require('node:assert/strict');
const fs = require('node:fs');
const path = require('node:path');
const test = require('node:test');
const { chromium } = require(process.env.CSP_PLAYWRIGHT_MODULE || 'playwright');

const root = path.resolve(__dirname, '../..');
const directory = process.env.CSP_ADMIN_RUNTIME_DIR;

// Captured HTML stays local; capture it with read-only application requests, never commit patient data.
test('captured authenticated admin screens initialize and shared controls work under enforced strict CSP', {
    skip: !directory,
    timeout: 480000,
}, async t => {
    const manifest = JSON.parse(fs.readFileSync(path.join(directory, 'manifest.json'), 'utf8'));
    const origin = 'http://127.0.0.1:8000';
    const rebase = html => html.replaceAll('http://127.0.0.1:43819', origin)
        .replaceAll('http:\\/\\/127.0.0.1:43819', 'http:\\/\\/127.0.0.1:8000');
    const pages = new Map(manifest.map(record => [record.path, record]));
    const assets = process.env.CSP_SCANNER_ASSET_DIR;
    const remoteAssets = new Map([
        ['https://code.jquery.com/jquery-3.6.0.min.js', 'jquery-3.6.0.min.js'],
        ['https://cdn.jsdelivr.net/npm/tesseract.js@5.1.1/dist/tesseract.min.js', 'tesseract.min.js'],
        ['https://unpkg.com/html5-qrcode@2.3.8/html5-qrcode.min.js', 'html5-qrcode.min.js'],
    ]);
    const mime = { '.js': 'text/javascript', '.css': 'text/css', '.svg': 'image/svg+xml', '.png': 'image/png', '.jpg': 'image/jpeg', '.webp': 'image/webp', '.ico': 'image/x-icon', '.woff2': 'font/woff2' };
    const browser = await chromium.launch({ headless: true, ...(process.env.CSP_BROWSER_EXECUTABLE ? { executablePath: process.env.CSP_BROWSER_EXECUTABLE } : {}) });
    const failures = [];
    const checked = [];
    try {
        for (const width of [1366, 390]) {
            const context = await browser.newContext({ viewport: { width, height: 844 }, reducedMotion: 'reduce' });
            await context.addInitScript(() => {
                window.cspViolations = [];
                addEventListener('securitypolicyviolation', event => cspViolations.push({
                    directive: event.effectiveDirective,
                    source: event.sourceFile.split('?')[0],
                    line: event.lineNumber,
                    blocked: event.blockedURI.split('?')[0],
                }));
            });
            await context.route('**/*', async route => {
                const request = route.request();
                const url = new URL(request.url());
                if (url.origin === origin) {
                    const record = pages.get(url.pathname + url.search) || pages.get(url.pathname);
                    if (request.isNavigationRequest() && record) {
                        return route.fulfill({ status: record.status, contentType: 'text/html',
                            headers: { 'Content-Security-Policy': record.policy },
                            body: rebase(fs.readFileSync(path.join(directory, record.file), 'utf8')) });
                    }
                    const file = path.resolve(root, 'public', '.' + decodeURIComponent(url.pathname));
                    if (file.startsWith(path.join(root, 'public') + path.sep) && fs.existsSync(file) && fs.statSync(file).isFile()) {
                        return route.fulfill({ contentType: mime[path.extname(file)] || 'application/octet-stream', body: fs.readFileSync(file) });
                    }
                    // Network-dependent feeds are isolated from the live clinic database during UI checks.
                    return route.fulfill({ contentType: 'application/json', body: JSON.stringify({ data: [], items: [], notifications: [], results: [], unread_count: 0, success: true }) });
                }
                const cached = remoteAssets.get(url.origin + url.pathname);
                if (cached && assets) return route.fulfill({ contentType: 'text/javascript', body: fs.readFileSync(path.join(assets, cached)) });
                if (request.resourceType() === 'stylesheet') return route.fulfill({ contentType: 'text/css', body: '' });
                return route.abort();
            });
            const page = await context.newPage();
            const errors = [];
            page.on('pageerror', error => errors.push(error.message));
            for (const record of manifest) {
                if (record.status === 404 && record.route === 'admin.developer-tools.test-accounts') continue;
                assert.equal(record.status, 200, record.route + ' must render successfully');
                assert.ok(record.policy, record.route + ' must receive the global strict policy');
                errors.length = 0;
                await page.goto(origin + record.path, { waitUntil: 'load' });
                await page.waitForTimeout(150);
                // Exercise the gate locally without persisting an acknowledgement or issuing a POST.
                await page.evaluate(() => {
                    document.querySelectorAll('.terms-gate-overlay, .school-year-reminder-overlay').forEach(element => element.remove());
                    document.body.classList.remove('terms-gate-lock');
                });
                const toggle = page.locator('#adminSidebarToggle');
                if (await toggle.isVisible()) {
                    await toggle.evaluate(element => element.click());
                    await toggle.evaluate(element => element.click());
                }
                const quick = page.locator('#headerQuickActions .quick-actions-toggle');
                if (await quick.isVisible()) await quick.evaluate(element => element.click());
                const theme = page.locator('#adminThemeToggle');
                if (await theme.count()) {
                    const before = await page.locator('html').getAttribute('data-theme');
                    await theme.evaluate(element => element.click());
                    if (await page.locator('html').getAttribute('data-theme') === before) errors.push('Theme toggle did not change the theme.');
                    await theme.evaluate(element => element.click());
                }
                await page.evaluate(() => {
                    const click = selector => document.querySelector(selector)?.click();
                    const visible = selector => {
                        const element = document.querySelector(selector);
                        return element && getComputedStyle(element).display !== 'none';
                    };
                    const check = (condition, message) => { if (!condition) throw new Error(message); };
                    if (document.getElementById('adminProfileMenuToggle')) {
                        click('#adminProfileMenuToggle');
                        check(document.getElementById('profileDropdown').classList.contains('is-open'), 'Profile menu opens');
                        click('#adminProfileMenuToggle');
                        check(!document.getElementById('profileDropdown').classList.contains('is-open'), 'Profile menu closes');
                    }
                    if (document.querySelector('[data-appointment-open-queue]')) {
                        for (const button of document.querySelectorAll('[data-appointment-open-queue]')) {
                            button.click();
                            const id = button.dataset.appointmentOpenQueue;
                            check(visible('#' + id), 'Appointment queue opens');
                            click('#' + id + ' [data-appointment-close-queue]');
                            check(!visible('#' + id), 'Appointment queue closes');
                        }
                        if (document.querySelector('[data-appointment-open-info]')) {
                            click('[data-appointment-open-info]');
                            check(visible('#infoModal'), 'Appointment details open');
                            click('#infoModal [data-appointment-close-info]');
                            check(!visible('#infoModal'), 'Appointment details close');
                        }
                    }
                    if (document.querySelector('[data-inventory-action="open-item"]')) {
                        for (const [open, close, id] of [
                            ['open-item', 'close-item', 'itemModal'],
                            ['open-import', 'close-import', 'inventoryImportModal'],
                            ['view-item', 'close-view-item', 'viewItemModal'],
                            ['restock-item', 'close-restock', 'restockModal'],
                            ['issue-item', 'close-issue', 'issueModal'],
                            ['edit-item', 'close-item', 'itemModal'],
                        ]) {
                            if (!document.querySelector(`[data-inventory-action="${open}"]`)) continue;
                            click(`[data-inventory-action="${open}"]`);
                            check(visible('#' + id), 'Inventory dialog opens: ' + open);
                            click(`[data-inventory-action="${close}"]`);
                            check(!visible('#' + id), 'Inventory dialog closes: ' + close);
                        }
                    }
                    if (document.querySelector('[data-integration-action="open-create-client"]')) {
                        click('[data-integration-action="open-create-client"]');
                        check(visible('#createClientModal'), 'Integration client dialog opens');
                        click('[data-integration-action="close-create-client"]');
                        check(!visible('#createClientModal'), 'Integration client dialog closes');
                    }
                });
                const accessibility = page.locator('#adminAccessibilityLaunch');
                if (await accessibility.count()) {
                    assert.equal(await page.locator('.asw-menu-btn').isVisible(), false, record.route + ': original accessibility launcher stays hidden');
                    await accessibility.evaluate(element => element.click());
                    await page.locator('.asw-menu').waitFor({ state: 'visible', timeout: 3000 });
                    await page.locator('.asw-menu-close').evaluate(element => element.click());
                    await page.locator('.asw-menu').waitFor({ state: 'hidden', timeout: 3000 });
                }
                const violations = await page.evaluate(() => window.cspViolations);
                if (errors.length || violations.length) failures.push({ route: record.route, width, errors: [...errors], violations });
                checked.push({ route: record.route, width });
            }
            await context.close();
        }
        t.diagnostic(JSON.stringify({ renderedRoutes: manifest.length, browserPages: checked.length, failures }));
        assert.deepEqual(failures, [], 'All captured admin pages must initialize without CSP violations or JavaScript exceptions.');
    } finally {
        await browser.close();
    }
});
