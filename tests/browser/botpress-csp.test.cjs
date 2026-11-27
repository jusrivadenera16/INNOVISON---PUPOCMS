const assert = require('node:assert/strict');
const { execFileSync } = require('node:child_process');
const { createHash } = require('node:crypto');
const fs = require('node:fs');
const http = require('node:http');
const path = require('node:path');
const test = require('node:test');
const { chromium } = require(process.env.CSP_PLAYWRIGHT_MODULE || 'playwright');

const root = path.resolve(__dirname, '../..');
const injector = process.env.CSP_BOTPRESS_INJECT_SCRIPT;
const configuration = process.env.CSP_BOTPRESS_CONFIG_SCRIPT;

// Supply local copies of the landing page's two published CDN scripts; no live chat requests are sent.
test('the published landing Botpress styles and toggle work with enforced strict CSP on desktop and mobile', {
    skip: !injector || !configuration,
    timeout: 60000,
}, async t => {
    const landing = fs.readFileSync(path.join(root, 'resources/views/landing.blade.php'), 'utf8');
    const injectorUrl = landing.match(/<script\s+src="(https:\/\/cdn\.botpress\.cloud\/[^"\s]+\/inject\.js)"/)?.[1];
    const configurationUrl = landing.match(/<script\s+src="(https:\/\/files\.bpcontent\.cloud\/[^"\s]+\.js)"/)?.[1];
    assert.ok(injectorUrl && configurationUrl, 'Use the scripts actually referenced by the landing page.');
    const { nonce, policy } = JSON.parse(execFileSync(process.env.PHP_BINARY || 'php', [path.join(root, 'tests/fixtures/csp-response.php')], { encoding: 'utf8' }));
    const server = http.createServer((request, response) => {
        response.writeHead(200, { 'Content-Type': 'text/html', 'Content-Security-Policy': policy });
        response.end(`<!doctype html><html><head><meta name="viewport" content="width=device-width, initial-scale=1"></head>
            <body><button id="bp-toggle-chat">AI Assistant</button>
            <script nonce="${nonce}" src="${injectorUrl}"></script>
            <script nonce="${nonce}" src="${configurationUrl}" defer></script></body></html>`);
    });
    let browser;
    try {
        await new Promise(resolve => server.listen(0, '127.0.0.1', resolve));
        const origin = `http://127.0.0.1:${server.address().port}`;
        browser = await chromium.launch({ headless: true, ...(process.env.CSP_BROWSER_EXECUTABLE ? { executablePath: process.env.CSP_BROWSER_EXECUTABLE } : {}) });
        for (const width of [1366, 390]) {
            const context = await browser.newContext({ viewport: { width, height: 844 } });
            await context.route('**/*', route => {
                const url = route.request().url();
                if (url.startsWith(origin + '/')) return route.continue();
                if (url === injectorUrl) return route.fulfill({ contentType: 'text/javascript', body: fs.readFileSync(injector) });
                if (url === configurationUrl) return route.fulfill({ contentType: 'text/javascript', body: fs.readFileSync(configuration) });
                if (route.request().resourceType() === 'stylesheet') return route.fulfill({ contentType: 'text/css', body: '' });
                return route.abort();
            });
            const page = await context.newPage();
            const errors = [];
            page.on('pageerror', error => errors.push(error.message));
            await page.addInitScript(() => {
                window.cspViolations = [];
                addEventListener('securitypolicyviolation', event => cspViolations.push(event.effectiveDirective));
            });
            await page.goto(origin, { waitUntil: 'load' });
            await page.waitForFunction(() => document.getElementById('fab-root')?.shadowRoot?.querySelectorAll('style').length === 3);
            const styles = await page.evaluate(() => [...document.getElementById('fab-root').shadowRoot.querySelectorAll('style')]
                .map(node => ({ css: node.textContent, applied: Boolean(node.sheet) })));
            const hashes = styles.map(style => 'sha256-' + createHash('sha256').update(style.css).digest('base64'));
            assert.equal(new Set(hashes).size, 3, 'The three Botpress style blocks must be distinct.');
            for (const [index, style] of styles.entries()) {
                assert.ok(policy.includes(`'${hashes[index]}'`), `Unreviewed Botpress CSS at width ${width}: ${hashes[index]}`);
                assert.equal(style.applied, true, 'A matching hash must actually authorize the stylesheet.');
            }
            await page.locator('#bp-toggle-chat').click();
            await page.waitForFunction(() => window.botpress.state === 'opened');
            await page.locator('.bpContainer').waitFor({ state: 'visible' });
            await page.waitForFunction(viewportWidth => {
                const container = document.getElementById('webchat-root')?.shadowRoot?.querySelector('.bpContainer')
                    ?? document.getElementById('fab-root')?.shadowRoot?.querySelector('.bpContainer');
                const bounds = container?.getBoundingClientRect();
                return bounds && bounds.x >= -1 && bounds.right <= viewportWidth + 1;
            }, width);
            const bounds = await page.locator('.bpContainer').boundingBox();
            assert.ok(bounds.x >= -1 && bounds.x + bounds.width <= width + 1, 'The chat must fit the viewport: ' + JSON.stringify(bounds));
            await page.locator('#bp-toggle-chat').evaluate(element => element.click());
            await page.waitForFunction(() => window.botpress.state === 'closed');
            await page.waitForTimeout(150);
            assert.deepEqual(await page.evaluate(() => cspViolations), []);
            assert.deepEqual(errors, []);

            // Matching vendor hashes must not authorize arbitrary injected CSS.
            await page.evaluate(() => {
                const style = document.createElement('style');
                style.id = 'unapproved-style';
                style.textContent = 'body { outline: 9px solid lime; }';
                document.head.appendChild(style);
            });
            await page.waitForFunction(() => cspViolations.includes('style-src-elem'));
            assert.equal(await page.locator('#unapproved-style').evaluate(node => node.sheet === null), true);
            t.diagnostic(JSON.stringify({ width, approvedStyles: styles.length, chatToggle: 'passed', arbitraryStyles: 'blocked' }));
            await context.close();
        }
    } finally {
        if (browser) await browser.close();
        await new Promise(resolve => server.close(resolve));
    }
});
