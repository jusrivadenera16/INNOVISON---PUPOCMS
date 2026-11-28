const assert = require('node:assert/strict');
const fs = require('node:fs');
const path = require('node:path');
const test = require('node:test');
const vm = require('node:vm');

const views = path.resolve(__dirname, '../../resources/views');
const partial = fs.readFileSync(path.join(views, 'admin/reports/partials/export-report-page.blade.php'), 'utf8');
const layout = fs.readFileSync(path.join(views, 'layouts/admin.blade.php'), 'utf8');
const formTag = partial.match(/<form\b[^>]*class="export-filter-form"[\s\S]*?>/)[0];
const handlerStart = layout.indexOf("document.addEventListener('submit', function (event) {", layout.indexOf('window.AdminLoading ='));
const handlerEnd = layout.indexOf("window.addEventListener('resize'", handlerStart);
const handler = layout.slice(handlerStart, handlerEnd);

function harness(url, scope = 'screen') {
    class Form {
        constructor() { this.dataset = { loaderScope: scope }; this.target = ''; }
        matches() { return false; }
        getAttribute(name) { return name === 'method' ? 'GET' : url; }
    }
    const form = new Form();
    const calls = [];
    let onSubmit;
    vm.runInNewContext(handler, {
        URL, HTMLFormElement: Form,
        window: { location: { href: url, origin: 'https://clinic.example', pathname: new URL(url).pathname, search: new URL(url).search } },
        document: { addEventListener: (name, listener) => { onSubmit = listener; } },
        resolveActionCopy: () => ({ title: 'Applying report filters', detail: 'Updating export preview.' }),
        resolveSkeletonKind: () => 'reports',
        showActionLoading: (...args) => calls.push(args),
        showContentLoading: () => calls.push(['content']),
    });
    return { form, calls, submit: event => onSubmit({ target: form, defaultPrevented: false, ...event }) };
}

test('all five export workspaces share the opted-in GET filter form, without changing downloads', () => {
    assert.match(formTag, /method="GET"/);
    assert.match(formTag, /data-loader-scope="screen"/);
    assert.match(formTag, /data-loader-message="Applying report filters"/);
    for (const type of ['mar', 'inventory', 'appointments', 'audit-trail', 'health-forms']) {
        const page = fs.readFileSync(path.join(views, `admin/reports/export-reports-${type}.blade.php`), 'utf8');
        assert.ok(page.includes("@include('admin.reports.partials.export-report-page')"), type);
    }
    assert.equal((partial.match(/data-loader-scope="screen"/g) || []).length, 1);
});

test('Apply Filter and Enter show the shared screen loader even when the GET action matches the current URL', () => {
    for (const role of ['admin', 'assistant']) {
        for (const type of ['mar', 'inventory', 'appointments', 'audit-trail', 'health-forms']) {
            for (const query of ['', '?date_from=2026-11-01&date_to=2026-11-28']) {
                for (const enter of [false, true]) {
                    const h = harness(`https://clinic.example/${role}/reports/export-hub/${type}${query}`);
                    h.submit({ submitter: enter ? null : { dataset: {}, form: h.form } });
                    assert.deepEqual(h.calls, [['Applying report filters', 'Updating export preview.', 'reports']]);
                }
            }
        }
    }
});

test('prevented submissions, new-tab forms and unrelated same-page GET forms do not trigger this loader', () => {
    const url = 'https://clinic.example/admin/reports/export-hub/mar';
    const cancelled = harness(url); cancelled.submit({ defaultPrevented: true });
    assert.equal(cancelled.calls.length, 0);
    const newTab = harness(url); newTab.form.target = '_blank'; newTab.submit({});
    assert.equal(newTab.calls.length, 0);
    const unrelated = harness(url, ''); unrelated.submit({});
    assert.equal(unrelated.calls.length, 0);
});
