const assert = require('node:assert/strict');
const fs = require('node:fs');
const path = require('node:path');
const test = require('node:test');
const { chromium } = require(process.env.CSP_PLAYWRIGHT_MODULE || 'playwright');

const root = path.resolve(__dirname, '../..');
// Generate private captures with CSP_CAPTURE_STUDENT_PAGES=1 php tests/fixtures/student-runtime-csp.php.
// Then point CSP_STUDENT_RUNTIME_DIR at storage/app/csp-student-runtime; never commit captured HTML.
const directory = process.env.CSP_STUDENT_RUNTIME_DIR;

test('captured student, applicant, employee, dependent, and guest screens work under strict CSP', {
    skip: !directory,
    timeout: 480000,
}, async t => {
    const manifest = JSON.parse(fs.readFileSync(path.join(directory, 'manifest.json'), 'utf8'));
    const origin = 'http://127.0.0.1:8000';
    const mime = { '.js': 'text/javascript', '.css': 'text/css', '.svg': 'image/svg+xml', '.png': 'image/png', '.jpg': 'image/jpeg', '.webp': 'image/webp', '.woff2': 'font/woff2' };
    const browser = await chromium.launch({ headless: true, ...(process.env.CSP_BROWSER_EXECUTABLE ? { executablePath: process.env.CSP_BROWSER_EXECUTABLE } : {}) });
    const failures = [];
    let checks = 0;
    let current;
    try {
        for (const width of [1366, 390]) {
            const context = await browser.newContext({ viewport: { width, height: 844 }, reducedMotion: 'reduce' });
            await context.addInitScript(() => {
                window.cspViolations = [];
                addEventListener('securitypolicyviolation', event => cspViolations.push({ directive: event.effectiveDirective, source: event.sourceFile.split('?')[0], line: event.lineNumber }));
            });
            await context.route('**/*', async route => {
                const request = route.request();
                const url = new URL(request.url());
                if (url.origin === origin) {
                    if (request.isNavigationRequest() && request.frame() === request.frame().page().mainFrame()
                        && url.pathname + url.search === current.path) {
                        return route.fulfill({ status: current.status, contentType: 'text/html',
                            headers: { 'Content-Security-Policy': current.policy },
                            body: fs.readFileSync(path.join(directory, current.file), 'utf8') });
                    }
                    const file = path.resolve(root, 'public', '.' + decodeURIComponent(url.pathname));
                    if (file.startsWith(path.join(root, 'public') + path.sep) && fs.existsSync(file) && fs.statSync(file).isFile()) {
                        return route.fulfill({ contentType: mime[path.extname(file)] || 'application/octet-stream', body: fs.readFileSync(file) });
                    }
                    return route.fulfill({ contentType: 'application/json', body: JSON.stringify({ success: true, data: [], records: [], notifications: [], unread_count: 0, slots: [] }) });
                }
                if (url.hostname === 'cdn.botpress.cloud' && process.env.CSP_BOTPRESS_INJECT_SCRIPT) {
                    return route.fulfill({ contentType: 'text/javascript', body: fs.readFileSync(process.env.CSP_BOTPRESS_INJECT_SCRIPT) });
                }
                if (url.hostname === 'files.bpcontent.cloud' && url.pathname.endsWith('.js') && process.env.CSP_BOTPRESS_CONFIG_SCRIPT) {
                    return route.fulfill({ contentType: 'text/javascript', body: fs.readFileSync(process.env.CSP_BOTPRESS_CONFIG_SCRIPT) });
                }
                if (url.href === 'https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css' && process.env.CSP_BOOTSTRAP_CSS) {
                    return route.fulfill({ contentType: 'text/css', body: fs.readFileSync(process.env.CSP_BOOTSTRAP_CSS) });
                }
                if (request.resourceType() === 'stylesheet') return route.fulfill({ contentType: 'text/css', body: '' });
                return route.abort();
            });
            const page = await context.newPage();
            const errors = [];
            page.on('pageerror', error => errors.push(error.message));
            page.on('dialog', dialog => dialog.dismiss());
            for (const record of manifest) {
                current = record;
                errors.length = 0;
                assert.equal(record.status, 200, record.name + ': the application must render the page');
                assert.ok(record.policy?.includes("style-src-attr 'none'"), record.name + ': use the strict global policy');
                await page.goto(origin + record.path, { waitUntil: 'load' });
                await page.waitForTimeout(120);
                const actions = await page.evaluate(() => {
                    let count = 0;
                    const check = (condition, message) => { if (!condition) throw new Error(message); count++; };
                    const click = selector => document.querySelector(selector)?.click();
                    const visible = selector => {
                        const node = document.querySelector(selector);
                        return Boolean(node && getComputedStyle(node).display !== 'none');
                    };
                    document.querySelectorAll('.terms-gate-overlay').forEach(node => node.remove());
                    document.body.classList.remove('terms-gate-lock');
                    const dismiss = document.querySelector('[data-student-action="dismiss-health-form"]');
                    if (dismiss) {
                        dismiss.click();
                        check(!visible('#healthFormModal'), 'Health-form reminder dismisses despite its important display rule');
                    }
                    // Mandatory first-setup prompts are not acknowledged or saved by these UI-only checks.
                    document.getElementById('healthFormModal')?.remove();
                    if (document.querySelector('#themeToggleBtn')) {
                        const previous = document.documentElement.dataset.theme;
                        click('#themeToggleBtn');
                        check(document.documentElement.dataset.theme !== previous, 'Theme toggle changes the theme');
                        click('#themeToggleBtn');
                    }
                    const nav = document.querySelector('.nav-toggle');
                    if (nav && getComputedStyle(nav).display !== 'none') {
                        nav.click(); check(nav.getAttribute('aria-expanded') === 'true', 'Mobile navigation opens');
                        nav.click(); check(nav.getAttribute('aria-expanded') === 'false', 'Mobile navigation closes');
                    }
                    for (const [open, close, id] of [
                        ['open-health-record', 'close-health-record', 'healthRecordModal'],
                        ['open-signature', 'close-signature', 'missingESignModal'],
                    ]) {
                        if (!document.querySelector(`[data-student-action="${open}"]`)) continue;
                        click(`[data-student-action="${open}"]`);
                        check(document.getElementById(id).classList.contains('is-open'), 'Account dialog opens: ' + open);
                        click(`#${id} [data-student-action="${close}"]`);
                        check(!document.getElementById(id).classList.contains('is-open'), 'Account dialog closes: ' + close);
                    }
                    if (document.querySelector('[data-profile-edit-allowed="true"]')) {
                        click('[data-student-action="edit-profile"]');
                        check(document.getElementById('profileEditModal').classList.contains('is-open'), 'Profile editor opens');
                        click('#profileEditModal [data-profile-modal-close]');
                        check(!document.getElementById('profileEditModal').classList.contains('is-open'), 'Profile editor closes');
                    }
                    if (document.querySelector('[data-student-action="request-health-form"]')) {
                        click('[data-student-action="request-health-form"]');
                        check(document.getElementById('studentNewCycleDialog').open, 'New-cycle request dialog opens');
                        click('#studentNewCycleDialog [data-student-action="close-dialog"]');
                        check(!document.getElementById('studentNewCycleDialog').open, 'New-cycle request dialog closes');
                    }
                    if (document.getElementById('preferredDateToggle')) {
                        click('#preferredDateToggle'); check(!document.getElementById('datePickerPanel').hidden, 'Booking date picker opens');
                        click('#preferredDateToggle'); check(document.getElementById('datePickerPanel').hidden, 'Booking date picker closes');
                        click('#serviceTypeDisplay');
                        check(document.getElementById('serviceTypeDisplay').getAttribute('aria-expanded') === 'true', 'Service menu opens');
                        click('.service-select-option');
                        check(document.getElementById('serviceTypeDisplay').getAttribute('aria-expanded') === 'false', 'Service selection closes the menu');
                        const form = document.getElementById('bookingForm');
                        check(!form.dispatchEvent(new Event('submit', { bubbles: true, cancelable: true })), 'Incomplete booking cannot submit');
                    }
                    const progress = document.getElementById('stepProgressFill');
                    if (progress) {
                        check(parseFloat(getComputedStyle(progress).width) > 0, 'Health-form initial progress renders');
                        const visiblePanel = document.querySelector('.step-panel:not(.is-hidden)');
                        check(Boolean(visiblePanel), 'Health form has an active step');
                        const form = visiblePanel.closest('form');
                        check(!form.dispatchEvent(new Event('submit', { bubbles: true, cancelable: true })), 'Incomplete health form cannot submit');
                    }
                    const survey = document.getElementById('clientSatisfactionSurvey');
                    if (survey) {
                        const privacy = document.querySelector('[data-survey-step="0"]');
                        privacy?.querySelectorAll('[required]').forEach(input => { input.checked = true; input.dispatchEvent(new Event('change', { bubbles: true })); });
                        click('[data-survey-step="0"] [data-survey-next]');
                        check(document.querySelector('[data-survey-step="1"]').classList.contains('is-active'), 'Survey moves to client details');
                        click('[data-survey-step="1"] [data-survey-prev]');
                        check(privacy.classList.contains('is-active'), 'Survey returns to privacy step');
                        check(!survey.dispatchEvent(new Event('submit', { bubbles: true, cancelable: true })), 'Preview survey cannot submit');
                    }
                    const question = document.querySelector('.faq-question');
                    if (question) {
                        question.querySelector('summary').click(); check(question.open, 'FAQ expands');
                        question.querySelector('summary').click(); check(!question.open, 'FAQ collapses');
                    }
                    return count;
                });
                checks += actions;
                if (await page.locator('#studentAccessibilityLaunch').count()) {
                    await page.locator('#studentAccessibilityLaunch').evaluate(node => node.click());
                    await page.locator('.asw-menu').waitFor({ state: 'visible', timeout: 3000 });
                    const bounds = await page.locator('.asw-menu').boundingBox();
                    assert.ok(bounds.x >= 0 && bounds.x + bounds.width <= width + 1, record.name + ': accessibility panel fits');
                    await page.locator('.asw-menu-close').evaluate(node => node.click());
                    await page.locator('.asw-menu').waitFor({ state: 'hidden', timeout: 3000 });
                    checks++;
                }
                const violations = await page.evaluate(() => cspViolations);
                if (errors.length || violations.length) failures.push({ name: record.name, width, errors: [...errors], violations });
                if (process.env.CSP_STUDENT_SCREENSHOTS && ['student-home', 'student-profile', 'student-health-form', 'student-evaluation'].includes(record.name)) {
                    fs.mkdirSync(process.env.CSP_STUDENT_SCREENSHOTS, { recursive: true });
                    await page.screenshot({ path: path.join(process.env.CSP_STUDENT_SCREENSHOTS, `${record.name}-${width}.png`) });
                }
            }
            await context.close();
        }
        t.diagnostic(JSON.stringify({ routes: manifest.length, pages: manifest.length * 2, checks, failures }));
        assert.deepEqual(failures, [], 'Student screens must have no CSP violations or JavaScript exceptions.');
    } finally {
        await browser.close();
    }
});
