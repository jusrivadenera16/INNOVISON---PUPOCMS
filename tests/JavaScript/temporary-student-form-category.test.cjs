const { test } = require('node:test');
const assert = require('node:assert/strict');
const fs = require('node:fs');
const vm = require('node:vm');
const source = fs.readFileSync(require('node:path').join(__dirname, '../../public/js/temporary-student-form-category.js'), 'utf8');
const dropdownSource = fs.readFileSync(require('node:path').join(__dirname, '../../public/js/category-bubble-select.js'), 'utf8');
function element() {
    const handlers = {};
    return { hidden: true, disabled: false, value: '', textContent: '', children: [], attributes: {}, dataset: {}, style: {}, offsetHeight: 230, offsetWidth: 350,
        addEventListener(name, handler) { handlers[name] = handler; }, emit(name, event = {}) { return handlers[name]?.(event); },
        setAttribute(name, value) { this.attributes[name] = value; }, toggleAttribute() {},
        append(child) { this.children.push(child); }, replaceChildren() { this.children = []; }, focus() {}, contains(target) { return target === this; },
        getBoundingClientRect() { return { top: 500, bottom: 540, left: 200 }; }, showPopover() { this.open = true; }, hidePopover() { this.open = false; },
    };
}
const info = { submission_id: 42, category: 'Regular', categories: ['Regular', 'On the Job Training'] };
const response = (data, ok = true) => ({ ok, json: async () => data });
function harness(fetcher) {
    const bubble = element(), wrap = element(), trigger = element(), document = element();
    const nodes = Object.fromEntries(['select', 'category-trigger', 'category-menu', 'status', 'current', 'form', 'apply', 'close'].map(key => [key, element()]));
    nodes['category-trigger'].querySelector = () => nodes['category-trigger'].label ||= element();
    nodes.form.reportValidity = () => true;
    bubble.querySelector = selector => nodes[selector.slice('[data-temp-category-'.length, -1)];
    wrap.querySelector = () => trigger; document.getElementById = () => bubble;
    document.querySelector = selector => selector.startsWith('meta') ? { content: 'csrf-test' } : wrap;
    document.createElement = () => element();
    const redirects = [], calls = [], window = { innerWidth: 1200, innerHeight: 800, addEventListener() {}, location: { href: 'http://localhost/health-records?form_category=Regular', assign: url => redirects.push(url) } };
    const context = vm.createContext({ URL, window, document, fetch: async (url, options) => { calls.push([url, options]); return fetcher(url, options); } });
    vm.runInContext(dropdownSource, context); vm.runInContext(source, context);
    return { bubble, wrap, nodes, trigger, calls, redirects, document, configure: window.TemporaryStudentFormCategory.configure,
        apply() { nodes.select.value = 'On the Job Training'; return nodes.form.emit('submit', { preventDefault() {} }); } };
}
test('unavailable action is hidden and never fetches', async () => {
    const h = harness(() => { throw new Error('No fetch'); }); h.configure(''); await h.trigger.emit('click');
    assert.equal(h.wrap.hidden, true); assert.equal(h.calls.length, 0);
});
test('opening bubble and dismissing do not save or require another modal', async () => {
    const h = harness(() => response(info)); h.configure('/record'); await h.trigger.emit('click');
    assert.equal(h.bubble.open, true); assert.equal(h.nodes.select.value, 'Regular');
    h.nodes.close.emit('click'); assert.equal(h.bubble.hidden, true); assert.equal(h.calls.length, 1);
});
test('Apply sends once directly and clears old category filter after save', async () => {
    let finish; const pending = new Promise(resolve => { finish = resolve; });
    const h = harness((url, opts) => opts.method === 'POST' ? pending : response(info));
    h.configure('/record'); await h.trigger.emit('click'); const saving = h.apply(); await h.apply(); h.nodes.close.emit('click');
    assert.equal(h.bubble.hidden, false); assert.equal(h.calls.length, 2); assert.equal(h.nodes.apply.disabled, true);
    assert.equal(h.calls[1][1].headers['X-CSRF-TOKEN'], 'csrf-test');
    assert.deepEqual(JSON.parse(h.calls[1][1].body), { submission_id: 42, expected_category: 'Regular', category: 'On the Job Training' });
    finish(response({})); await saving; assert.match(h.redirects[0], /tab=pending_approval/); assert.doesNotMatch(h.redirects[0], /form_category/);
});
test('save failure stays in bubble and allows retry', async () => {
    const h = harness((url, opts) => opts.method === 'POST' ? response({ errors: { record: ['Already approved.'] } }, false) : response(info));
    h.configure('/record'); await h.trigger.emit('click'); await h.apply();
    assert.equal(h.nodes.status.textContent, 'Already approved.'); assert.equal(h.nodes.apply.disabled, false); assert.equal(h.bubble.hidden, false); assert.equal(h.redirects.length, 0);
});
test('custom dropdown preserves the current category and applies the selected option', async () => {
    const h = harness(() => response(info)); h.configure('/record'); await h.trigger.emit('click');
    assert.equal(h.nodes.select.hidden, true); assert.equal(h.nodes['category-trigger'].label.textContent, 'Regular');
    h.nodes['category-trigger'].emit('click'); h.nodes['category-menu'].children[1].emit('click');
    assert.equal(h.nodes.select.value, 'On the Job Training'); assert.equal(h.nodes['category-menu'].hidden, true);
    await h.nodes.form.emit('submit', { preventDefault() {} });
    assert.equal(JSON.parse(h.calls[1][1].body).category, 'On the Job Training');
});
test('unchanged category is not saved and stale GET cannot reopen bubble', async () => {
    const h = harness(() => response(info)); h.configure('/record'); await h.trigger.emit('click');
    await h.nodes.form.emit('submit', { preventDefault() {} }); assert.equal(h.calls.length, 1); assert.match(h.nodes.status.textContent, /different/);
    let finish; const stale = harness(() => new Promise(resolve => { finish = resolve; }));
    stale.configure('/old'); const opening = stale.trigger.emit('click'); stale.configure('/new'); finish(response(info)); await opening;
    assert.equal(stale.bubble.hidden, true); assert.equal(stale.nodes.select.children.length, 0);
});
