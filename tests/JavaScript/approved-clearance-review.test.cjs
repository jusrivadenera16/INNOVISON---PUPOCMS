const { test } = require('node:test');
const assert = require('node:assert/strict');
const fs = require('node:fs');
const vm = require('node:vm');
const source = fs.readFileSync(require('node:path').join(__dirname, '../../public/js/approved-clearance-review.js'), 'utf8');
const dropdownSource = fs.readFileSync(require('node:path').join(__dirname, '../../public/js/category-bubble-select.js'), 'utf8');
function element() {
    const handlers = {};
    return { hidden: true, disabled: false, value: '', textContent: '', children: [], attributes: {}, style: {}, dataset: {}, offsetHeight: 250, offsetWidth: 350,
        classList: { toggle() {} }, addEventListener(name, fn) { handlers[name] = fn; }, emit(name, event = { preventDefault() {} }) { return handlers[name]?.(event); },
        setAttribute(name, value) { this.attributes[name] = value; }, toggleAttribute() {},
        append(child) { this.children.push(child); }, replaceChildren() { this.children = []; }, focus() {}, contains(target) { return target === this; },
        getBoundingClientRect() { return { top: 400, bottom: 430, left: 160 }; }, showPopover() { this.open = true; }, hidePopover() { this.open = false; },
    };
}
const data = { can_issue: true, can_start_review: true, start_review_url: '/health-profile/5/request-health-form', submission_id: 11, identity: { name: 'Juan Dela Cruz', age: 22, sex: 'M', birth_date: '2004-01-15' }, today: '2026-11-11',
    health_form_categories: ['On the Job Training', 'Regular'], purposes: ['On the Job Training', 'Regular'] };
const response = (data, ok = true) => ({ ok, json: async () => data });
function harness(fetcher = () => response(data)) {
    const root = element(), result = element(), document = element(), title = element();
    const keys = ['bubble', 'form', 'open', 'prompt', 'category', 'category-trigger', 'category-menu', 'continue', 'bubble-status', 'close', 'patient', 'selected', 'date', 'findings', 'decision', 'purpose', 'other-label', 'other', 'remarks', 'status', 'back', 'issue', 'document'];
    const nodes = Object.fromEntries(keys.map(key => [key, element()]));
    root.dataset.base = '/admin/medical-clearances';
    nodes['category-trigger'].querySelector = () => nodes['category-trigger'].label ||= element();
    root.querySelector = selector => nodes[selector.slice('[data-approved-clearance-'.length, -1)]; root.closest = () => result;
    nodes.form.reportValidity = () => true; nodes.form.reset = () => { for (const key of ['other', 'remarks', 'decision']) nodes[key].value = ''; nodes.findings.value = 'No Findings / Normal'; };
    const submitted = [];
    document.body = element();
    document.querySelector = selector => selector.startsWith('meta') ? { content: 'csrf' } : root; document.getElementById = () => title;
    document.createElement = tag => { const item = element(); if (tag === 'form') item.submit = () => submitted.push({method:item.method,action:item.action,fields:Object.fromEntries(item.children.map(input=>[input.name,input.value]))}); return item; };
    const calls = [], opened = [], window = { ClinicExistingFinalReview: { open: context => { opened.push(context); return true; } }, innerWidth: 1000, innerHeight: 800, addEventListener() {} }; let uuid = 0;
    const context = vm.createContext({ window, document, crypto: { randomUUID: () => `uuid-${++uuid}` }, fetch: async (url, options) => { calls.push([url, options]); return fetcher(url, options); } });
    vm.runInContext(dropdownSource, context); vm.runInContext(source, context);
    return { root, nodes, calls, opened, submitted, title, configure: window.ApprovedClearanceReview.configure,
        async enter(type = 'student') { this.configure(type, 5); await nodes.open.emit('click'); nodes.category.value = 'On the Job Training'; nodes.continue.emit('click'); },
        submit() { return nodes.form.emit('submit'); } };
}
test('only students and employees expose the clearance action', async () => {
    const h = harness();
    for (const type of ['applicant','dependent','guest',null]) { h.configure(type,5); assert.equal(h.root.hidden,true); await h.nodes.open.emit('click'); }
    assert.equal(h.calls.length,0);
    h.configure('student',5); assert.equal(h.root.hidden,false);
});
test('Continue posts once to the existing clinic-start endpoint before opening Pending Approval', async () => {
    const h = harness(); await h.enter();
    assert.equal(h.opened.length,0); assert.equal(h.submitted.length,1);
    assert.equal(h.submitted[0].method,'POST'); assert.equal(h.submitted[0].action,'/health-profile/5/request-health-form');
    assert.deepEqual(h.submitted[0].fields,{_token:'csrf',category:'On the Job Training',clinic_action:'start'});
    h.nodes.continue.emit('click'); assert.equal(h.submitted.length,1); assert.equal(h.nodes.continue.disabled,true);
    assert.equal(h.calls.length,1);
    assert.equal(h.calls[0][1].method,undefined);
    assert.doesNotMatch(source,/approved-clearance-form|review\(true\)/);
});
test('employee selection posts to its existing assessment endpoint without a student action', async () => {
    const h = harness(()=>response({...data,start_review_url:'/employee-health-profile/5/request-health-form'})); h.configure('employee',5); await h.nodes.open.emit('click');
    h.nodes['category-trigger'].emit('click'); h.nodes['category-menu'].children[1].emit('click');
    h.nodes.continue.emit('click'); assert.equal(h.submitted[0].fields.category,'On the Job Training');
    assert.equal(h.submitted[0].action,'/employee-health-profile/5/request-health-form'); assert.equal(h.submitted[0].fields.clinic_action,undefined);
});
test('unavailable records cannot continue and stale category responses are ignored', async () => {
    const h=harness(()=>response({...data,can_issue:false,can_start_review:false})); h.configure('student',5); await h.nodes.open.emit('click');
    h.nodes.category.value='On the Job Training'; h.nodes.continue.emit('click'); assert.equal(h.opened.length,0);
    let finish; const stale=harness(()=>new Promise(resolve=>{finish=resolve;}));
    stale.configure('student',5); const pending=stale.nodes.open.emit('click'); stale.configure('employee',8);
    finish(response(data)); await pending; assert.equal(stale.nodes.bubble.hidden,true);
});

test('OJT display label submits the original configured category name',async()=>{
    const h=harness(()=>response({...data,health_form_category_options:[{value:'OJT',label:'On the Job Training'}]}));
    h.configure('student',5);await h.nodes.open.emit('click');
    h.nodes['category-menu'].children[1].emit('click');h.nodes.continue.emit('click');
    assert.equal(h.submitted[0].fields.category,'OJT');
});
