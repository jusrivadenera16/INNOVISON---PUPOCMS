const assert = require('node:assert/strict');
const { execFileSync } = require('node:child_process');
const fs = require('node:fs/promises');
const http = require('node:http');
const path = require('node:path');
const test = require('node:test');
const { chromium } = require(process.env.CSP_PLAYWRIGHT_MODULE || 'playwright');

const root = path.resolve(__dirname, '../..');

test('accessibility controls remain usable under the strict middleware policy on desktop and mobile', { timeout: 90000 }, async () => {
    const { nonce, policy } = JSON.parse(execFileSync(process.env.PHP_BINARY || 'php', [path.join(root, 'tests/fixtures/csp-response.php')], { encoding: 'utf8' }));
    assert.ok(nonce && policy, 'The real middleware must provide the fixture policy.');
    const server = http.createServer(async (request, response) => {
        if (request.url === '/js/sienna.js') {
            response.writeHead(200, { 'Content-Type': 'text/javascript' });
            response.end(await fs.readFile(path.join(root, 'public/js/sienna-accessibility-custom.umd.js')));
            return;
        }
        response.writeHead(200, { 'Content-Type': 'text/html', 'Content-Security-Policy': policy });
        response.end(`<!doctype html><html lang="en"><head><meta name="viewport" content="width=device-width, initial-scale=1">
            <script nonce="${nonce}" src="/js/sienna.js" defer></script></head>
            <body><h1>Admin Accessibility Fixture</h1><p>Clinic services and readable content.</p>
            <a href="#content">Sample link</a><div id="content">Sample clinic content</div>
            <script nonce="${nonce}">window.fixtureReady = true;</script></body></html>`);
    });
    let browser;
    try {
        await new Promise(resolve => server.listen(0, '127.0.0.1', resolve));
        browser = await chromium.launch({ headless: true, ...(process.env.CSP_BROWSER_EXECUTABLE ? { executablePath: process.env.CSP_BROWSER_EXECUTABLE } : {}) });
        const page = await browser.newPage({ viewport: { width: 1366, height: 768 } });
        const errors = [];
        page.on('pageerror', error => errors.push(error.message));
        await page.addInitScript(() => {
            window.cspViolations = [];
            document.addEventListener('securitypolicyviolation', event => {
                window.cspViolations.push({ directive: event.effectiveDirective, blocked: event.blockedURI });
            });
        });
        await page.goto(`http://127.0.0.1:${server.address().port}`);
        await page.locator('.asw-menu-btn').click();
        assert.equal(await page.evaluate(() => window.fixtureReady), true);
        for (const key of ['highlight-title', 'highlight-links', 'readable-font', 'letter-spacing', 'line-height', 'font-weight', 'big-cursor', 'readable-guide', 'super-focus', 'dark-contrast', 'light-contrast', 'high-contrast', 'high-saturation', 'low-saturation', 'monochrome', 'image-tooltips', 'hide-images', 'stop-animations']) {
            await page.locator(`.asw-btn[data-key="${key}"]`).click();
            await page.locator('.asw-menu-reset').first().click();
        }
        await page.locator('.asw-btn[data-key="page-structure"]').click();
        assert.equal(await page.locator('.asw-structure-modal').isVisible(), true);
        await page.locator('.asw-structure-close').click();
        for (const key of ['seizure-safe', 'visually-impaired', 'adhd-friendly', 'cognitive-learning', 'motor-impaired']) {
            await page.locator(`.asw-profile-btn[data-key="${key}"]`).click();
            await page.locator('.asw-menu-reset').first().click();
        }
        await page.setViewportSize({ width: 390, height: 844 });
        const bounds = await page.locator('.asw-menu').boundingBox();
        assert.ok(bounds.x >= 0 && bounds.x + bounds.width <= 390, 'The mobile accessibility panel must fit the viewport.');
        await page.locator('.asw-btn[data-key="readable-guide"]').click();
        assert.equal(await page.locator('.asw-rg-bottom').evaluate(element => getComputedStyle(element).bottom), '0px');
        assert.deepEqual(await page.evaluate(() => window.cspViolations), []);
        assert.deepEqual(errors, []);
    } finally {
        if (browser) await browser.close();
        await new Promise(resolve => server.close(resolve));
    }
});
