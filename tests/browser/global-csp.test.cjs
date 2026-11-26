const assert = require('node:assert/strict');
const { execFileSync } = require('node:child_process');
const http = require('node:http');
const path = require('node:path');
const test = require('node:test');
const { chromium } = require(process.env.CSP_PLAYWRIGHT_MODULE || 'playwright');

test('the enforced middleware policy permits nonced controls and rejects inline injection', { timeout: 60000 }, async () => {
    const root = path.resolve(__dirname, '../..');
    const { nonce, policy } = JSON.parse(execFileSync(process.env.PHP_BINARY || 'php', [path.join(root, 'tests/fixtures/csp-response.php')], { encoding: 'utf8' }));
    const server = http.createServer((request, response) => {
        response.writeHead(200, { 'Content-Type': 'text/html', 'Content-Security-Policy': policy });
        response.end(`<!doctype html><html><head>
            <style nonce="${nonce}">#approved { color: rgb(20, 80, 40); }</style>
            <style id="unapproved-style" nonce="wrong-nonce">body { color: lime; }</style>
            </head><body>
            <button id="approved">Approved control</button>
            <button id="inline-event" onclick="window.unapprovedEvent = true">Inline event</button>
            <p id="inline-style" style="color: lime">Inline style</p>
            <script nonce="wrong-nonce">window.unapprovedScript = true;</script>
            <script nonce="${nonce}">
                window.approvedScript = true;
                document.getElementById('approved').addEventListener('click', function () {
                    this.style.width = '180px';
                    window.approvedClick = true;
                });
            </script></body></html>`);
    });
    let browser;
    try {
        await new Promise(resolve => server.listen(0, '127.0.0.1', resolve));
        browser = await chromium.launch({ headless: true, ...(process.env.CSP_BROWSER_EXECUTABLE ? { executablePath: process.env.CSP_BROWSER_EXECUTABLE } : {}) });
        const page = await browser.newPage();
        await page.addInitScript(() => {
            window.cspViolations = [];
            addEventListener('securitypolicyviolation', event => cspViolations.push(event.effectiveDirective));
        });
        await page.goto(`http://127.0.0.1:${server.address().port}`, { waitUntil: 'load' });
        await page.locator('#approved').click();
        await page.locator('#inline-event').click();
        await page.waitForFunction(() => cspViolations.includes('script-src-attr'));
        const result = await page.evaluate(() => ({
            approvedScript: window.approvedScript,
            approvedClick: window.approvedClick,
            approvedColor: getComputedStyle(document.getElementById('approved')).color,
            approvedWidth: document.getElementById('approved').style.width,
            unapprovedScript: Boolean(window.unapprovedScript),
            unapprovedEvent: Boolean(window.unapprovedEvent),
            unapprovedStyle: Boolean(document.getElementById('unapproved-style').sheet),
            violations: [...new Set(cspViolations)].sort(),
        }));
        assert.deepEqual(result, {
            approvedScript: true, approvedClick: true,
            approvedColor: 'rgb(20, 80, 40)', approvedWidth: '180px',
            unapprovedScript: false, unapprovedEvent: false, unapprovedStyle: false,
            violations: ['script-src-attr', 'script-src-elem', 'style-src-attr', 'style-src-elem'],
        });
    } finally {
        if (browser) await browser.close();
        await new Promise(resolve => server.close(resolve));
    }
});
