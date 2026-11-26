const test = require('node:test');
const assert = require('node:assert/strict');
const fs = require('node:fs');
const path = require('node:path');
const vm = require('node:vm');

function executableInlineScripts(source) {
    const scripts = /<script(?=[\t\n\f\r >])((?:[^"'<>]|"[^"]*"|'[^']*')*)>([\s\S]*?)<\/script\s*>/gi;
    return [...source.matchAll(scripts)].filter(([, attributes]) => {
        const attributePattern = /(?:^|[\t\n\f\r ])([^\s"'<>/=]+)(?:\s*=\s*(?:"([^"]*)"|'([^']*)'|([^\s"'=<>`]+)))?/g;
        const parsed = new Map([...attributes.matchAll(attributePattern)].map(([, name, doubleQuoted, singleQuoted, unquoted]) => [
            name.toLowerCase(), doubleQuoted ?? singleQuoted ?? unquoted ?? '',
        ]));
        const type = (parsed.get('type') || '').trim().toLowerCase();
        return !parsed.has('src') && (!type || /^(?:module|(?:text|application)\/(?:java|ecma)script)$/.test(type));
    }).map(([, , script]) => script);
}

test('script extraction accepts nonce attributes and ignores external and data scripts', () => {
    const source = `<script src="/external.js">external();</script>
        <script src>emptySource();</script><script src="">blankSource();</script>
        <script type="application/json">{"data":true}</script>
        <script type=application/ld+json>{"data":true}</script>
        <script data-src="/not-external.js" title="src='/ignored.js' type='application/json'"
            nonce="{{ request()->attributes->get('csp_nonce') }}">classic();</script>
        <SCRIPT nonce='nonce>value' TYPE=" Text/JavaScript ">javascript();</SCRIPT>
        <script type="module">export const value = 1;</script>`;
    assert.deepEqual(executableInlineScripts(source), ['classic();', 'javascript();', 'export const value = 1;']);
});

test('generated token is copied only on request and cleared after copy or close', async () => {
    const view = fs.readFileSync(
        path.resolve(__dirname, '../../resources/views/admin/integration-tokens.blade.php'),
        'utf8'
    );
    const script = executableInlineScripts(view)[0]
        ?.replace('@json($clientLogPayload)', '{}')
        .replace(/\{\{ route\('[^']+'\) \}\}/g, '/test');
    assert.ok(script, 'Integration token script should exist');

    const elements = new Map();
    const element = (id) => {
        if (!elements.has(id)) {
            const classes = new Set();
            elements.set(id, {
                textContent: '',
                style: {},
                disabled: false,
                addEventListener: () => {},
                classList: {
                    add: (name) => classes.add(name),
                    remove: (name) => classes.delete(name),
                    toggle: (name, enabled) => enabled ? classes.add(name) : classes.delete(name),
                    contains: (name) => classes.has(name),
                },
            });
        }
        return elements.get(id);
    };
    const copied = [];
    const context = vm.createContext({
        document: {
            querySelector: (selector) => selector === 'meta[name="csrf-token"]' ? {content: 'test'} : null,
            querySelectorAll: () => [],
            getElementById: element,
            addEventListener: () => {},
        },
        navigator: {clipboard: {writeText: async (value) => { copied.push(value); }}},
        fetch: async () => ({
            json: async () => ({
                success: true,
                token: '123|test-secret',
                token_id: 123,
                abilities: ['medical-status:read'],
            }),
        }),
        confirm: () => true,
        setTimeout: () => {},
    });
    vm.runInContext(script, context);
    const flush = () => new Promise(setImmediate);

    await context.generateToken('1', 'Test system');
    await flush();
    assert.equal(copied.length, 0, 'Generate must not copy automatically');
    assert.equal(element('generatedTokenValue').textContent, '*'.repeat(80));

    context.toggleTokenVisibility();
    assert.equal(element('generatedTokenValue').textContent, '123|test-secret');
    await context.copyGeneratedToken();
    await flush();
    assert.deepEqual(copied, ['123|test-secret']);
    assert.equal(vm.runInContext('latestGeneratedToken', context), '');
    assert.equal(element('generatedTokenValue').textContent, 'Token no longer available in this session.');
    assert.equal(element('copyButton').disabled, true);
    assert.equal(element('toggleTokenVisibilityButton').disabled, true);

    context.closeGeneratedTokenModal();
    assert.equal(element('generatedTokenModal').classList.contains('show'), false);

    await context.generateToken('1', 'Test system', true);
    await flush();
    context.toggleTokenVisibility();
    context.closeGeneratedTokenModal();
    assert.equal(vm.runInContext('latestGeneratedToken', context), '');
    assert.equal(element('generatedTokenValue').textContent, 'Token no longer available in this session.');
});
