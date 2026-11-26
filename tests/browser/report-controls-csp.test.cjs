const path = require('node:path');
const vm = require('node:vm');
const { execFileSync } = require('node:child_process');
const test = require('node:test');
const { chromium } = require(process.env.CSP_PLAYWRIGHT_MODULE || 'playwright');

const result = JSON.parse(execFileSync(process.env.PHP_BINARY || 'php', [path.join(__dirname, '../fixtures/admin-report-csp.php'), '--browser-fixtures'], {
    encoding: 'utf8', maxBuffer: 16 * 1024 * 1024, windowsHide: true,
}));
for (const [name, script] of Object.entries(result.scripts)) new vm.Script(script, { filename: name });
const nonce = 'cmVwb3J0cy1jc3AtY2hlY2s=';
test('report filters, modal actions, confirmations, and escaped data work under strict CSP', { timeout: 180000 }, async t => {
    const browser = await chromium.launch({ headless: true, ...(process.env.CSP_BROWSER_EXECUTABLE ? { executablePath: process.env.CSP_BROWSER_EXECUTABLE } : {}) });
    const browserPage = await browser.newPage();
    const exceptions = [];
    browserPage.on('pageerror', error => exceptions.push(error.message));
    try {
    const script = (file) => result.scripts[file + ':0'];
    const styles = (file) => [...result.sources[file].matchAll(/<style\b[^\n]*>[\s\S]*?<\/style>/g)]
        .map(([tag]) => tag.replace(/nonce="[^"]*"/, `nonce="${nonce}"`)).join('\n');
    const checkResults = [];
    async function evaluate(expression) {
        return browserPage.evaluate(expression);
    }
    async function page(name, html, js, css, assertions) {
        await browserPage.goto('about:blank');
        exceptions.length = 0;
        const policy = `default-src 'none'; script-src 'nonce-${nonce}'; script-src-attr 'none'; style-src 'nonce-${nonce}'; style-src-attr 'none'`;
        const content = `<!doctype html><html><head><meta charset="utf-8"><meta http-equiv="Content-Security-Policy" content="${policy}">
            <script nonce="${nonce}">globalThis.cspViolations = []; addEventListener('securitypolicyviolation', (e) => cspViolations.push(e.violatedDirective));</script>
            ${css || ''}</head><body>${html}<script nonce="${nonce}">${js || ''}</script></body></html>`;
        await browserPage.setContent(content);
        await browserPage.evaluate(() => new Promise(resolve => requestAnimationFrame(resolve)));
        if (exceptions.length) throw new Error(name + ': ' + JSON.stringify(exceptions));
        const checked = await evaluate(`(() => {
            let checks = 0;
            const assert = (condition, message) => { if (!condition) throw new Error(${JSON.stringify(name)} + ': ' + message); checks++; };
            ${assertions}
            assert(cspViolations.length === 0, 'CSP violations: ' + cspViolations);
            return checks;
        })()`);
        checkResults.push({ name, assertions: checked });
    }

    for (const file of ['health-forms-logbook.blade.php', 'health-forms-applicants-list.blade.php']) {
        const prefix = file.startsWith('health-forms-applicants') ? 'applicant-logbook' : 'health-logbook';
        await page(file, `<button data-open-logbook-filter>Open</button>
            <div id="filterModal" class="modal-overlay" data-logbook-filter-backdrop><div class="modal-content" data-logbook-filter-content>
            <button data-close-logbook-filter><span>Close</span></button></div></div>
            <form id="filterForm"><select data-logbook-page-size><option>10</option></select></form>
            <input id="searchInput"><div class="logbook-total-card"><span>1</span></div>
            <table><tbody><tr data-logbook-row data-search="sample"><td>Sample</td></tr>
            <tr id="empty-row" data-logbook-empty-row class="${prefix}-empty-hidden"><td>Empty</td></tr></tbody></table>`,
        script(file), styles(file) + `<style nonce="${nonce}">#empty-row { display: table-row; }</style>`, `
            const empty = document.getElementById('empty-row');
            const input = document.getElementById('searchInput');
            assert(getComputedStyle(empty).display === 'none', 'initial hiding must outrank an ID rule');
            input.value = 'missing'; input.dispatchEvent(new Event('input', {bubbles:true}));
            assert(getComputedStyle(empty).display === 'table-row', 'no-results row must show');
            assert(getComputedStyle(document.querySelector('[data-logbook-row]')).display === 'none', 'non-match row must hide');
            input.value = 'sample'; input.dispatchEvent(new Event('input', {bubbles:true}));
            assert(getComputedStyle(empty).display === 'none', 'no-results row must hide again');
            document.querySelector('[data-open-logbook-filter]').click();
            assert(document.getElementById('filterModal').classList.contains('active'), 'filter opens');
            document.querySelector('[data-logbook-filter-content]').click();
            assert(document.getElementById('filterModal').classList.contains('active'), 'content click keeps filter open');
            document.querySelector('[data-close-logbook-filter] span').click();
            assert(!document.getElementById('filterModal').classList.contains('active'), 'nested close click closes filter');
            document.querySelector('[data-open-logbook-filter]').click(); document.getElementById('filterModal').click();
            assert(!document.getElementById('filterModal').classList.contains('active'), 'backdrop closes');
            ${file.startsWith('health-forms-applicants') ? `let submissions=0; document.getElementById('filterForm').submit=()=>submissions++;
            document.querySelector('[data-logbook-page-size]').dispatchEvent(new Event('change'));
            assert(submissions === 1, 'page size change submits form');` : ''}
        `);
    }

    for (const [file, open, close, backdrop, id, state] of [
        ['health-forms.blade.php', 'data-open-health-forms-filter', 'data-close-health-forms-filter', 'data-health-forms-filter-backdrop', 'healthFormsFilterModal', 'is-open'],
        ['partials/export-report-page.blade.php', 'data-open-export-filter', 'data-close-export-filter', 'data-export-filter-backdrop', 'exportFilterModal', 'is-open'],
    ]) {
        await page(file, `<button ${open}>Open</button><div id="${id}" ${backdrop}><button ${close}><span>Close</span></button><span id="inside">Content</span></div>`, script(file), styles(file), `
            const modal=document.getElementById('${id}'); document.querySelector('[${open}]').click();
            assert(modal.classList.contains('${state}'), 'open');
            document.getElementById('inside').click(); assert(modal.classList.contains('${state}'), 'content does not close');
            document.querySelector('[${close}] span').click(); assert(!modal.classList.contains('${state}'), 'close');
            document.querySelector('[${open}]').click(); modal.click(); assert(!modal.classList.contains('${state}'), 'backdrop');
        `);
    }

    const specialName = JSON.stringify(result.specialName);
    await page('illness categories', result.fixtures['data-view-illness-category'] + `<button data-close-illness-category>Close</button>
        <form data-delete-illness-category></form><div id="viewModal" class="modal-overlay"><p id="categoryDisplayName"></p>
        <ul id="linkedMedicineList"></ul><p id="linkedMedicineEmpty" class="linked-medicine-empty illness-categories-empty-hidden">Empty</p></div>`,
    script('manage-inventory-illness-categories.blade.php'), styles('manage-inventory-illness-categories.blade.php') + `<style nonce="${nonce}">#linkedMedicineEmpty {display:block;}</style>`, `
        const empty=document.getElementById('linkedMedicineEmpty');
        assert(getComputedStyle(empty).display === 'none', 'initial hidden class beats ID');
        document.querySelector('[data-view-illness-category]').click();
        assert(document.getElementById('categoryDisplayName').textContent === 'Category: ' + ${specialName}, 'escaped category name round-trips');
        assert(document.querySelector('#linkedMedicineList li').textContent === ${specialName}, 'JSON item name round-trips');
        document.querySelector('[data-close-illness-category]').click(); assert(getComputedStyle(document.getElementById('viewModal')).display==='none','close');
        openViewModal({name:'Empty',items:[]}); assert(getComputedStyle(empty).display==='block','CSSOM block overrides hidden class under strict CSP');
        assert(getComputedStyle(document.getElementById('linkedMedicineList')).display==='none','empty list hides');
        openViewModal({name:'Populated',items:['Medicine']}); assert(getComputedStyle(empty).display==='none','empty state hides again');
        globalThis.confirm=()=>false; assert(!document.querySelector('form').dispatchEvent(new Event('submit',{cancelable:true})), 'delete cancel');
        globalThis.confirm=()=>true; assert(document.querySelector('form').dispatchEvent(new Event('submit',{cancelable:true})), 'delete accept');
    `);

    for (const [file, add, closeAdd, modal, closeEdit, editModal, deletes] of [
        ['manage-mar.blade.php','data-add-condition','data-close-add-condition','addConditionModal','data-close-edit-condition','changeModal',['data-delete-condition']],
        ['manage-medicine-types.blade.php','data-add-medicine-type','data-close-add-medicine-type','addMedicineTypeModal','data-close-edit-medicine','editMedicineModal',['data-delete-medicine-type','data-delete-medicine']],
        ['manage-health-form-categories.blade.php','data-add-health-category','data-close-add-health-category','addHealthCategoryModal','data-close-edit-health-category','editHealthCategoryModal',['data-delete-health-category']],
    ]) {
        await page(file, `<button ${add}>Add</button><button ${closeAdd}>Close add</button><button ${closeEdit}>Close edit</button>
            <div id="${modal}" aria-hidden="true"></div><div id="${editModal}" class="is-open" aria-hidden="false"></div>
            ${deletes.map((attr)=>`<form ${attr}></form>`).join('')}
            ${file==='manage-mar.blade.php' ? result.fixtures['data-edit-condition'] : ''}`, script(file), styles(file), `
            document.querySelector('[${add}]').click(); assert(document.getElementById('${modal}').classList.contains('is-open'),'add opens');
            document.querySelector('[${closeAdd}]').click(); assert(!document.getElementById('${modal}').classList.contains('is-open'),'add closes');
            document.querySelector('[${closeEdit}]').click(); assert(!document.getElementById('${editModal}').classList.contains('is-open'),'edit closes');
            document.querySelectorAll('form').forEach(form=>{
                globalThis.confirm=()=>false; assert(!form.dispatchEvent(new Event('submit',{cancelable:true})),'delete cancel');
                globalThis.confirm=()=>true; assert(form.dispatchEvent(new Event('submit',{cancelable:true})),'delete accept');
            });
            ${file==='manage-mar.blade.php' ? `let payload; openChangeModal=(...args)=>payload=args;document.querySelector('[data-edit-condition]').click();
                assert(JSON.stringify(payload)===JSON.stringify(['8','9',${specialName}]),'condition parameters');` : ''}
        `);
    }

    await page('clearance types', `<button data-add-clearance>Add</button>
        ${['addClearanceModal','editClearanceModal','addSubcategoryModal','editSubcategoryModal'].map(id=>`<div id="${id}" class="clearance-modal-overlay"></div>`).join('')}
        ${['data-close-add-clearance','data-close-edit-clearance','data-close-add-clearance-subcategory','data-close-edit-clearance-subcategory'].map(attr=>`<button ${attr}>Close</button>`).join('')}
        <form data-delete-clearance></form><form data-delete-clearance-subcategory></form>
        ${result.fixtures['data-add-clearance-subcategory']}${result.fixtures['data-edit-clearance']}${result.fixtures['data-edit-clearance-subcategory']}`,
    script('manage-clearance-types.blade.php'), styles('manage-clearance-types.blade.php'), `
        document.querySelector('[data-add-clearance]').click();assert(document.getElementById('addClearanceModal').classList.contains('is-open'),'add opens');
        document.querySelector('[data-close-add-clearance]').click();assert(!document.getElementById('addClearanceModal').classList.contains('is-open'),'add closes');
        let payload; openAddSubcategoryModal=(...args)=>payload=args; document.querySelector('[data-add-clearance-subcategory]').click();
        assert(JSON.stringify(payload)===JSON.stringify(['42',${specialName}]),'add subcategory parameters');
        openEditClearanceModal=(...args)=>payload=args; document.querySelector('[data-edit-clearance]').click();
        assert(payload[0]==='42'&&payload[1]===${specialName}&&payload[2]==='subcategories'&&payload[3].filters.name===${specialName},'edit mapping JSON and name');
        openEditSubcategoryModal=(...args)=>payload=args; document.querySelector('[data-edit-clearance-subcategory]').click();
        assert(JSON.stringify(payload)===JSON.stringify(['73',${specialName},${specialName},null]),'edit subcategory null mapping');
        for (const [id,attr] of [['editClearanceModal','data-close-edit-clearance'],['addSubcategoryModal','data-close-add-clearance-subcategory'],['editSubcategoryModal','data-close-edit-clearance-subcategory']]){
            document.getElementById(id).classList.add('is-open');document.querySelector('['+attr+']').click();assert(!document.getElementById(id).classList.contains('is-open'),'modal closes '+id);
        }
        document.querySelectorAll('form').forEach(form=>{globalThis.confirm=()=>false;assert(!form.dispatchEvent(new Event('submit',{cancelable:true})),'cancel delete');globalThis.confirm=()=>true;assert(form.dispatchEvent(new Event('submit',{cancelable:true})),'accept delete');});
    `);

    await page('clinic option confirmation', '<form data-delete-clinic-option></form>', script('manage-clinic-service-options.blade.php'), '', `
        const form=document.querySelector('form');globalThis.confirm=()=>false;
        assert(!form.dispatchEvent(new Event('submit',{cancelable:true})),'cancel delete');
        globalThis.confirm=()=>true;assert(form.dispatchEvent(new Event('submit',{cancelable:true})),'accept delete');
    `);
    await page('print actions', '<button data-print-report>Print</button><button data-close-report>Close</button>', script('print-reports.blade.php'), '', `
        let printed=0,closed=0;window.print=()=>printed++;window.close=()=>closed++;
        document.querySelector('[data-print-report]').click();document.querySelector('[data-close-report]').click();
        assert(printed===1&&closed===1,'print and close');
    `);
    await page('chart widths', result.fixtures.bars, '', `<style nonce="${nonce}">.appointment-chart-track{width:200px}.appointment-chart-fill{display:block;height:8px}#fill{width:1px}</style>`, `
        const fills=[...document.querySelectorAll('.appointment-chart-fill')];const expected=[100,4,0,0,0,0,0,0,50,25];
        fills[0].id='fill';fills.forEach((fill,i)=>assert(Math.abs(parseFloat(getComputedStyle(fill).width)-expected[i]*2)<0.1,'bar '+i));
    `);
    t.diagnostic(JSON.stringify({ views: Object.keys(result.sources).length, scriptSyntaxChecks: Object.keys(result.scripts).length, pdfVariants: Object.keys(result.pdf).length, browser: checkResults }));
    } finally {
        await browser.close();
    }
});
