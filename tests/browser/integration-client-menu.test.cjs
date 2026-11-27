const assert = require('node:assert/strict');
const fs = require('node:fs');
const path = require('node:path');
const test = require('node:test');

test('client delete menu is visible with or without tokens and maroon/white in both themes', {
    skip: !process.env.CSP_PLAYWRIGHT_MODULE,
    timeout: 30000,
}, async () => {
    const { chromium } = require(process.env.CSP_PLAYWRIGHT_MODULE);
    const source = fs.readFileSync(path.resolve(__dirname, '../../resources/views/admin/integration-tokens.blade.php'), 'utf8');
    const css = source.match(/<style[^>]*>([\s\S]*?)<\/style>/)[1];
    const menu = source.match(/<div class="token-action-control">[\s\S]*?<\/details>/)[0];
    const browser = await chromium.launch({ headless: true, executablePath: process.env.CSP_BROWSER_EXECUTABLE });
    try {
        for (const width of [1366, 390]) {
            for (const theme of ['light', 'dark']) {
                for (const hasToken of [false, true]) {
                    const page = await browser.newPage({ viewport: { width, height: 844 } });
                    await page.route('**/*', route => route.abort());
                    const markup = menu.replace(/\{\{ \$hasToken \? '' : 'hidden' \}\}/g, hasToken ? '' : 'hidden')
                        .replace(/\{\{[\s\S]*?\}\}/g, '');
                    await page.setContent(`<html data-theme="${theme}"><head><style>${css}</style></head><body><div style="display:flex;justify-content:flex-end;padding:20px">${markup}</div></div></body></html>`);
                    await page.locator('.token-action-menu summary').click();
                    const button = page.locator('[data-integration-action="delete-client"]');
                    assert.equal(await button.isVisible(), true);
                    assert.equal(await page.locator('[data-integration-action="rotate-token"]').isVisible(), hasToken);
                    const colors = await button.evaluate(node => ({ background: getComputedStyle(node).backgroundColor, color: getComputedStyle(node).color }));
                    assert.equal(colors.background, 'rgb(127, 29, 45)');
                    assert.equal(colors.color, 'rgb(255, 255, 255)');
                    const bounds = await button.boundingBox();
                    assert.ok(bounds.x >= 0 && bounds.x + bounds.width <= width);
                    await page.close();
                }
            }
        }
    } finally {
        await browser.close();
    }
});
