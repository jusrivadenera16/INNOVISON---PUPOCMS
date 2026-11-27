const assert = require('node:assert/strict');
const fs = require('node:fs');
const path = require('node:path');
const test = require('node:test');
const vm = require('node:vm');

const source = fs.readFileSync(path.join(__dirname, '../../resources/views/admin/integration-tokens.blade.php'), 'utf8');
const start = source.indexOf('async function deleteIntegrationClient(button)');
const end = source.indexOf('async function copyGeneratedToken()', start);
assert.ok(start >= 0 && end > start);

function setup({ confirmed = true, ok = true, success = true, networkError = false } = {}) {
    const calls = [];
    const alerts = [];
    const card = { dataset: { systemName: 'IMS', systemKey: 'ims' } };
    const button = { disabled: false, dataset: { deleteUrl: '/admin/integration-clients/12' }, closest: () => card };
    const state = { cleared: false, reloaded: false, confirmation: '' };
    const context = vm.createContext({
        integrationCsrfToken: 'test-csrf',
        confirm: message => { state.confirmation = message; return confirmed; },
        fetch: async (url, options) => { calls.push({ url, options }); if (networkError) throw new Error('offline'); return { ok }; },
        readJsonResponse: async () => ({ success, message: success ? 'Client deleted' : 'Rejected' }),
        clearGeneratedToken: () => { state.cleared = true; },
        showAlert: (message, error) => alerts.push({ message, error }),
        location: { reload: () => { state.reloaded = true; } },
        setTimeout: fn => fn(),
    });
    vm.runInContext(source.slice(start, end), context);
    return { context, button, calls, alerts, state };
}

test('delete is in the three-dot menu even without a token and has maroon/white styling', () => {
    assert.ok(source.includes('<details class="token-action-menu">'));
    assert.match(source, /data-integration-action="delete-client"[^>]*data-delete-url=/);
    assert.match(source, /case 'delete-client': deleteIntegrationClient\(button\)/);
    const style = source.slice(source.indexOf('.token-action-dropdown .delete-client-action,'));
    assert.match(style, /background: #7f1d2d;\s+color: #fff;/);
});

test('cancelling confirmation sends no deletion request', async () => {
    const h = setup({ confirmed: false });
    await h.context.deleteIntegrationClient(h.button);
    assert.equal(h.calls.length, 0);
    assert.equal(h.button.disabled, false);
    assert.match(h.state.confirmation, /IMS.*ims/);
    assert.match(h.state.confirmation, /tokens will be revoked/);
    assert.match(h.state.confirmation, /records and historical request logs will not be deleted/);
});

test('confirmed deletion sends only the selected identity with CSRF and reloads on success', async () => {
    const h = setup();
    await h.context.deleteIntegrationClient(h.button);
    assert.equal(h.calls.length, 1);
    assert.equal(h.calls[0].url, '/admin/integration-clients/12');
    assert.equal(h.calls[0].options.method, 'DELETE');
    assert.equal(h.calls[0].options.headers['X-CSRF-TOKEN'], 'test-csrf');
    assert.deepEqual(JSON.parse(h.calls[0].options.body), { system_key: 'ims' });
    assert.equal(h.state.cleared, true);
    assert.equal(h.state.reloaded, true);
    assert.equal(h.button.disabled, true);
});

for (const failure of [{ ok: false, success: false }, { ok: false }, { success: false }, { networkError: true }]) {
    test('failed deletion keeps the page and allows retry: ' + JSON.stringify(failure), async () => {
        const h = setup(failure);
        await h.context.deleteIntegrationClient(h.button);
        assert.equal(h.state.reloaded, false);
        assert.equal(h.state.cleared, false);
        assert.equal(h.button.disabled, false);
        assert.equal(h.alerts.at(-1).error, true);
    });
}

test('disabled delete action cannot send a duplicate request', async () => {
    const h = setup();
    h.button.disabled = true;
    await h.context.deleteIntegrationClient(h.button);
    assert.equal(h.calls.length, 0);
});
