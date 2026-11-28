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
    health_form_categories: ['On the Job Training', 'Regular'], purposes: ['On the Job Training', 'Regular'],
    health_form_category_catalog: [{id:4,name:'On the Job Training'},{id:5,name:'Regular'}],
    purpose_options:[{value:'mapping:1',label:'On the Job Training'},{value:'mapping:2',label:'Regular'}],
    purpose_categories:[{value:'mapping:1',category:'On the Job Training'},{value:'mapping:2',category:'Regular'}] };
const response = (data, ok = true) => ({ ok, json: async () => data });
function harness(fetcher = () => response(data), configurationControls = true) {
    const root = element(), result = element(), document = element(), title = element();
    const keys = ['bubble', 'form', 'open', 'prompt', 'category', 'category-trigger', 'category-menu', 'continue', 'bubble-status', 'close', 'patient', 'selected', 'date', 'findings', 'decision', 'purpose', 'other-label', 'other', 'remarks', 'status', 'back', 'issue', 'document', 'type', 'editor', 'editor-name', 'editor-label', 'editor-help', 'editor-save', 'editor-cancel', 'add-type', 'add-category'];
    const nodes = Object.fromEntries(keys.map(key => [key, element()]));
    for (const key of ['type-trigger','type-menu','editor-category','editor-category-trigger','editor-category-menu','category-editor','category-name','category-save','category-cancel']) nodes[key]=element();
    root.dataset.base = '/admin/medical-clearances';
    root.dataset.typeStore = '/admin/mar-clearance-types'; root.dataset.categoryStore = '/admin/health-form-categories';
    nodes['category-trigger'].querySelector = () => nodes['category-trigger'].label ||= element();
    for (const key of ['type-trigger','editor-category-trigger']) nodes[key].querySelector=()=>nodes[key].label ||= element();
    root.querySelector = selector => {
        const key=selector.slice('[data-approved-clearance-'.length,-1);
        return !configurationControls && /^(editor|category-|add-)/.test(key) ? null : nodes[key];
    }; root.closest = () => result;
    nodes.form.reportValidity = () => true; nodes.form.reset = () => { for (const key of ['other', 'remarks', 'decision']) nodes[key].value = ''; nodes.findings.value = 'No Findings / Normal'; };
    const submitted = [];
    document.body = element();
    document.querySelector = selector => selector.startsWith('meta') ? { content: 'csrf' } : root; document.getElementById = () => title;
    document.createElement = tag => { const item = element(); if (tag === 'form') item.submit = () => submitted.push({method:item.method,action:item.action,fields:Object.fromEntries(item.children.map(input=>[input.name,input.value]))}); return item; };
    const calls = [], opened = [], window = { ClinicExistingFinalReview: { open: context => { opened.push(context); return true; } }, innerWidth: 1000, innerHeight: 800, addEventListener() {} }; let uuid = 0;
    const context = vm.createContext({ window, document, crypto: { randomUUID: () => `uuid-${++uuid}` }, fetch: async (url, options) => { calls.push([url, options]); return fetcher(url, options); } });
    vm.runInContext(dropdownSource, context); vm.runInContext(source, context);
    return { root, nodes, calls, opened, submitted, title, configure: window.ApprovedClearanceReview.configure,
        async enter(type = 'student') { this.configure(type, 5); await nodes.open.emit('click'); nodes.type.value = 'mapping:1'; nodes.continue.emit('click'); },
        submit() { return nodes.form.emit('submit'); } };
}
test('clearance action accepts only canonical student or employee form identifiers', async () => {
    const h = harness();
    for (const type of ['applicant','dependent','guest',null]) { h.configure(type,5); assert.equal(h.root.hidden,true); await h.nodes.open.emit('click'); }
    assert.equal(h.calls.length,0);
    h.configure('student',5); assert.equal(h.root.hidden,false);
});
test('Continue saves a pending request once and asks to open Final Review', async () => {
    const h = harness(); await h.enter();
    assert.equal(h.opened.length,0); assert.equal(h.submitted.length,1);
    assert.equal(h.submitted[0].method,'POST'); assert.equal(h.submitted[0].action,'/health-profile/5/request-health-form');
    assert.deepEqual(h.submitted[0].fields,{_token:'csrf',category:'On the Job Training',review_after_start:'1',clinic_action:'start',clearance_purpose:'mapping:1'});
    h.nodes.continue.emit('click'); assert.equal(h.submitted.length,1); assert.equal(h.nodes.continue.disabled,true);
    assert.equal(h.calls.length,1);
    assert.equal(h.calls[0][1].method,undefined);
    assert.doesNotMatch(source,/approved-clearance-form|review\(true\)/);
});
test('employee selection posts to its existing assessment endpoint without a student action', async () => {
    const h = harness(()=>response({...data,start_review_url:'/employee-health-profile/5/request-health-form'})); h.configure('employee',5); await h.nodes.open.emit('click');
    h.nodes['type-trigger'].emit('click'); h.nodes['type-menu'].children[1].emit('click');
    h.nodes.continue.emit('click'); assert.equal(h.submitted[0].fields.category,'On the Job Training');
    assert.equal(h.submitted[0].action,'/employee-health-profile/5/request-health-form'); assert.equal(h.submitted[0].fields.clinic_action,undefined);
    assert.equal(h.submitted[0].fields.review_after_start,'1');
});
test('unavailable records cannot continue and stale category responses are ignored', async () => {
    const h=harness(()=>response({...data,can_issue:false,can_start_review:false})); h.configure('student',5); await h.nodes.open.emit('click');
    h.nodes.type.value='mapping:1'; h.nodes.continue.emit('click'); assert.equal(h.submitted.length,0);
    let finish; const stale=harness(()=>new Promise(resolve=>{finish=resolve;}));
    stale.configure('student',5); const pending=stale.nodes.open.emit('click'); stale.configure('employee',8);
    finish(response(data)); await pending; assert.equal(stale.nodes.bubble.hidden,true);
});

test('OJT display label submits the original configured category name',async()=>{
    const h=harness(()=>response({...data,health_form_category_options:[{value:'OJT',label:'On the Job Training'}],purpose_categories:[{value:'mapping:1',category:'OJT'}]}));
    h.configure('student',5);await h.nodes.open.emit('click');
    h.nodes['type-menu'].children[1].emit('click');h.nodes.continue.emit('click');
    assert.equal(h.submitted[0].fields.category,'OJT');
});

test('category bubble stays centered in the lookup modal when its dropdown opens',async()=>{
    const h=harness();
    h.nodes.open.closest=()=>({getBoundingClientRect:()=>({left:100,top:80,width:700,height:600})});
    h.configure('student',5);await h.nodes.open.emit('click');
    assert.equal(h.nodes.bubble.style.left,'275px');assert.equal(h.nodes.bubble.style.top,'255px');
    h.nodes['type-trigger'].emit('click');
    assert.equal(h.nodes.bubble.style.left,'275px');assert.equal(h.nodes.bubble.style.top,'255px');
});

test('a new category is added inside the type editor without losing the type name', async () => {
    let saved = false;
    const original = {...data, can_manage_configuration: true, category_audience: 'student'};
    const h = harness((url, options) => {
        if (options.method === 'POST') { saved = true; return response({message: 'Category added.', category: {id: 9, name: 'Practice Teaching'}}); }
        return response(saved ? {...original, health_form_categories: [...data.health_form_categories, 'Practice Teaching'],
            health_form_category_catalog:[...data.health_form_category_catalog,{id:9,name:'Practice Teaching'}]} : original);
    });
    h.configure('student', 5); await h.nodes.open.emit('click');
    h.nodes['add-type'].emit('click'); h.nodes['editor-name'].value = 'Teaching Certificate';
    h.nodes['add-category'].emit('click'); h.nodes['category-name'].value = 'Practice Teaching';
    await h.nodes['category-save'].emit('click');
    assert.equal(h.calls[1][0], '/admin/health-form-categories');
    assert.deepEqual(JSON.parse(h.calls[1][1].body), {name: 'Practice Teaching', audiences: ['student']});
    assert.equal(h.nodes['editor-category'].value, 'Practice Teaching'); assert.equal(h.nodes['category-editor'].hidden, true);
    assert.equal(h.nodes.editor.hidden,false); assert.equal(h.nodes['editor-name'].value,'Teaching Certificate');
    assert.equal(h.nodes.continue.disabled,true);
    assert.equal(h.submitted.length, 0);
});

test('quick add type links the selected category and carries its selection to final review', async () => {
    let saved = false;
    const original = {...data, can_manage_configuration: true, category_audience: 'student', health_form_category_catalog: [{id: 4, name: 'On the Job Training'}]};
    const h = harness((url, options) => {
        if (options.method === 'POST') { saved = true; return response({message: 'Type added.', purpose_value: 'mapping:30'}); }
        return response(saved ? {...original, purpose_options: [{value: 'mapping:30', label: 'Practice Teaching'}],
            purpose_categories:[{value:'mapping:30',category:'On the Job Training'}]} : original);
    });
    h.configure('student', 5); await h.nodes.open.emit('click');
    h.nodes['type-menu'].children[1].emit('click'); h.nodes['add-type'].emit('click');
    h.nodes['editor-name'].value = 'Practice Teaching'; await h.nodes['editor-save'].emit('click');
    assert.equal(h.calls[1][0], '/admin/mar-clearance-types');
    assert.equal(JSON.parse(h.calls[1][1].body).source_category_id, 4);
    assert.equal(JSON.parse(h.calls[1][1].body).source_key, 'student_health_form_category');
    assert.equal(h.nodes.type.value, 'mapping:30');
    h.nodes.continue.emit('click'); assert.equal(h.submitted[0].fields.clearance_purpose, 'mapping:30');
});

test('the selected type determines the category and configuration errors keep the editor open', async () => {
    const original = {...data, can_manage_configuration: true, category_audience: 'student',
        purpose_options: [{value: 'mapping:1', label: 'OJT'}], purpose_categories: [{value: 'mapping:1', category: 'On the Job Training'}]};
    const h = harness((url, options) => options.method === 'POST' ? response({errors: {name: ['Already exists.']}}, false) : response(original));
    h.configure('student', 5); await h.nodes.open.emit('click');
    h.nodes['type-menu'].children[1].emit('click'); assert.equal(h.nodes.type.value, 'mapping:1');
    h.nodes['add-type'].emit('click'); h.nodes['editor-name'].value = 'Duplicate';
    await h.nodes['editor-save'].emit('click');
    assert.equal(h.nodes['bubble-status'].textContent, 'Already exists.'); assert.equal(h.nodes.editor.hidden, false);
    assert.equal(h.nodes['editor-save'].disabled, false);
});

test('a clearance type cannot be created without an eligible category or while category creation is unfinished',async()=>{
    const h=harness(()=>response({...data,can_manage_configuration:true}));
    h.configure('student',5); await h.nodes.open.emit('click'); h.nodes['add-type'].emit('click');
    h.nodes['editor-name'].value='New Type'; await h.nodes['editor-save'].emit('click');
    assert.match(h.nodes['bubble-status'].textContent,/Select or add/); assert.equal(h.calls.length,1);
    h.nodes['editor-category'].value='On the Job Training'; h.nodes['add-category'].emit('click');
    await h.nodes['editor-save'].emit('click'); assert.match(h.nodes['bubble-status'].textContent,/Save or cancel/);
    assert.equal(h.calls.length,1);
});

test('Continue is unavailable while editing and is restored when the editor is cancelled',async()=>{
    const h=harness(()=>response({...data,can_manage_configuration:true})); h.configure('student',5); await h.nodes.open.emit('click');
    h.nodes['type-menu'].children[1].emit('click'); assert.equal(h.nodes.continue.disabled,false);
    h.nodes['add-type'].emit('click'); assert.equal(h.nodes.continue.disabled,true);
    h.nodes.continue.emit('click'); assert.equal(h.submitted.length,0);
    h.nodes['editor-cancel'].emit('click'); assert.equal(h.nodes.continue.disabled,false);
});

test('an unmapped type cannot start a form and users without configuration controls can continue',async()=>{
    const h=harness(()=>response({...data,purpose_options:[...data.purpose_options,{value:'mapping:999',label:'Unmapped'}]}),false);
    h.configure('student',5); await h.nodes.open.emit('click');
    assert.equal(h.nodes['type-menu'].children.length,3);
    h.nodes.type.value='mapping:999'; h.nodes.continue.emit('click'); assert.equal(h.submitted.length,0);
    h.nodes.type.value='mapping:1'; h.nodes.continue.emit('click'); assert.equal(h.submitted.length,1);
});

test('duplicate saves and a late save response cannot alter the next selected record',async()=>{
    let finish;
    const h=harness((_url,options)=>options.method==='POST'?new Promise(resolve=>{finish=resolve;}):response({...data,can_manage_configuration:true}));
    h.configure('student',5); await h.nodes.open.emit('click'); h.nodes['add-type'].emit('click');
    h.nodes['editor-name'].value='New Type'; h.nodes['editor-category'].value='On the Job Training';
    const pending=h.nodes['editor-save'].emit('click'); await h.nodes['editor-save'].emit('click');
    assert.equal(h.calls.length,2);
    h.configure('employee',19);
    finish(response({purpose_value:'mapping:30'})); await pending;
    assert.equal(h.calls.length,2); assert.equal(h.nodes.bubble.hidden,true); assert.equal(h.nodes.editor.hidden,true);
});

test('configuration permission is required for the quick-add editor', async () => {
    const h = harness(); h.configure('employee', 5); await h.nodes.open.emit('click');
    h.nodes['add-type'].emit('click'); h.nodes['add-category'].emit('click');
    assert.equal(h.nodes.editor.hidden, true); assert.equal(h.calls.length, 1);
});
