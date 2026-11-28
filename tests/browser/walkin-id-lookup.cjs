'use strict';

// Real verification markup/CSS and production JS against synthetic API responses.
// This is UI regression coverage, not an authenticated production smoke test.
const assert = require('node:assert/strict');
const fs = require('node:fs');
const path = require('node:path');
const {execFileSync} = require('node:child_process');
const root = path.resolve(__dirname, '../..');
const read = file => fs.readFileSync(path.join(root, file), 'utf8');
const php = process.env.CSP_PHP_EXECUTABLE || 'C:/xampp/php/php.exe';
const fixture = JSON.parse(execFileSync(php, ['tests/fixtures/walkin-id-lookup.php'], {cwd:root, encoding:'utf8'}));
const csp = JSON.parse(execFileSync(php, ['tests/fixtures/csp-response.php'], {cwd:root, encoding:'utf8'}));
const {chromium} = require(process.env.CSP_PLAYWRIGHT_MODULE || 'playwright');
const source = read('resources/views/admin/walkin.blade.php');
const between = (start, end) => {
    const a = source.indexOf(start), b = source.indexOf(end, a);
    assert.ok(a >= 0 && b > a, start);
    return source.slice(a,b);
};
const bindings = between('        const referenceVerification =', '        const reviewDraftBtn');
const helpers = between('        function hideAlreadyApprovedResult()', '        function showAlreadyApprovedResult(');
const verification = between('        function showApplicantReferenceVerification(', '        function returnToApplicantReferenceSearch(');
const enterReview = between('        function enterClinicFinalReview()', '        referenceVerificationCancel?');
const continueHandler = between("        referenceVerificationContinue?.addEventListener('click'", "        refInput?.addEventListener('input'");
const setup = `
${bindings}
const entryPane=document.getElementById('applicantRefEntry');
const modalShell=document.querySelector('.applicant-modal-shell');
const lookupModalTitle=document.getElementById('lookupModalTitle');
const lookupModalSubtitle=document.getElementById('lookupModalSubtitle');
const alreadyApprovedResult=document.getElementById('applicantAlreadyApprovedResult');
const alreadyApprovedMessage=document.getElementById('applicantAlreadyApprovedMessage');
const alreadyApprovedMeta=document.getElementById('applicantAlreadyApprovedMeta');
const alreadyApprovedCloseRow=null;
const refInput={value:''};
let pendingReferenceLookup=null;
const currentApplicantWorkflow='clinic';
function isClinicLookupMode(){return true;}
function getApplicantInitials(){return 'SP';}
function setStatus(){}
${helpers}
${verification}
window.showRecord=(type,state='approved')=>showApplicantReferenceVerification({
    record_type:type,health_profile_id:7,review_timing_profile_id:type==='dependent'?19:7,
    approved:state==='approved',has_reviewable_health_form:state==='submitted',email:'synthetic.patient@example.test',
    health_form_preview:{status:state,submitted:state!=='requested',category:'Annual Checkup',approved_at:'Nov 28, 2026, 10:00 AM'}
},'SYNTHETIC-123','Synthetic Patient With A Long Name',state!=='requested');
window.showCertificateRequest=type=>showApplicantReferenceVerification({
    record_type:type,health_profile_id:7,review_timing_profile_id:type==='dependent'?19:7,
    has_reviewable_health_form:true,health_form_preview:{status:'submitted',submitted:true,category:'Ladderized',
        certificate_request:{type:'Practice Teaching / Internship',previous_approved_at:'Nov 12, 2026, 10:00 AM'}}
},'SYNTHETIC-123','Synthetic Patient With A Long Name',true);
window.continued=[];
function doLookup(data){window.continued.push(data);}
${enterReview}
${continueHandler}
window.submissions=[];
HTMLFormElement.prototype.submit=function(){window.submissions.push({url:this.action,fields:Object.fromEntries(new FormData(this))});};
referenceVerificationCancel.addEventListener('click',()=>{hideAlreadyApprovedResult();referenceVerification.hidden=true;});
`;
const scripts = ['category-bubble-select','approved-clearance-review','medical-clearance'].map(name => read(`public/js/${name}.js`));
const css = fixture.css + read('public/css/temporary-student-form-category.css') + read('public/css/medical-clearance.css');
const html = theme => `<!doctype html><html data-theme="${theme}"><head><meta name="viewport" content="width=device-width,initial-scale=1"><meta name="csrf-token" content="fixture-token"><style nonce="${csp.nonce}">*{box-sizing:border-box}body{margin:0;font-family:Arial,sans-serif}${css}</style></head><body>
<div id="applicantRefModal" class="applicant-modal-backdrop show"><div class="applicant-modal-shell is-employee-lookup">
<header class="applicant-modal-head"><div><h2 id="lookupModalTitle">ID Lookup</h2><p id="lookupModalSubtitle"></p></div></header>
<div class="applicant-modal-body"><div class="applicant-ref-panel is-visible" id="applicantRefEntry">${fixture.markup}</div></div></div></div>
${[...scripts,setup].map(js=>`<script nonce="${csp.nonce}">${js}</script>`).join('')}</body></html>`;

(async () => {
    const browser = await chromium.launch({headless:true,executablePath:process.env.CSP_BROWSER_EXECUTABLE || undefined});
    const results=[];
    try {
        for (const theme of ['light','dark']) for (const [width,height] of [[1366,900],[390,844],[320,640]]) {
            const page=await browser.newPage({viewport:{width,height}}),errors=[],saved=[];
            let addedCategory=false,addedType=false,emptyHistory=false;
            page.on('pageerror',error=>errors.push(error.message));
            page.on('console',message=>{if(message.type()==='error')errors.push(message.text());});
            await page.route('http://localhost/**',route=>{
                const url=new URL(route.request().url());
                if(url.pathname==='/fixture') return route.fulfill({contentType:'text/html',headers:{'Content-Security-Policy':csp.policy},body:html(theme)});
                if(route.request().method()==='POST') {
                    saved.push({path:url.pathname,body:route.request().postDataJSON()});
                    if(url.pathname.endsWith('/health-form-categories')) {
                        addedCategory=true;
                        return route.fulfill({json:{message:'Category added.',category:{id:9,name:'New School Category'}}});
                    }
                    addedType=true;
                    return route.fulfill({json:{message:'Clearance Type added.',purpose_value:'mapping:30'}});
                }
                const type=url.pathname.includes('/student/')?'student':'employee';
                return route.fulfill({json:{can_start_review:true,can_manage_configuration:true,category_audience:url.pathname.endsWith('/19')?'dependent':type==='student'?'student':'faculty',
                    health_form_categories:['Annual Checkup',...(addedCategory?['New School Category']:[])],
                    health_form_category_options:[{value:'Annual Checkup',label:'Annual Checkup'},...(addedCategory?[{value:'New School Category',label:'New School Category'}]:[])],
                    health_form_category_catalog:[{id:4,name:'Annual Checkup'},...(addedCategory?[{id:9,name:'New School Category'}]:[])],default_purpose_value:'mapping:1',
                    purpose_options:[{value:'mapping:1',label:'Annual Medical Certificate'},...(addedType?[{value:'mapping:30',label:'New School Certificate'}]:[])],
                    purpose_categories:[{value:'mapping:1',category:'Annual Checkup'},...(addedType?[{value:'mapping:30',category:'New School Category'}]:[])],
                    documents:emptyHistory?[]:[{id:101,purpose:'Annual Checkup',issued_on:'Nov 28, 2026',url:'/synthetic-certificate.pdf'}],
                    start_review_url:`/start/${type}/${url.pathname.endsWith('/19')?19:7}`}});
            });
            await page.goto('http://localhost/fixture');
            for(const type of ['student','employee','dependent']) {
                await page.evaluate(type=>window.showRecord(type),type);
                const prompt=page.locator('[data-approved-clearance-open]');
                await prompt.waitFor({state:'visible'});
                await page.locator('[data-clearance-documents]').waitFor({state:'visible'});
                assert.equal(await page.locator('[data-clearance-documents] h3').textContent(),'Medical Certificate History');
                assert.equal(await page.locator('.medical-clearance-hint').first().evaluate(e=>getComputedStyle(e).fontSize),'12px');
                assert.equal(await page.locator('[data-clearance-list] a').getAttribute('href'),'/synthetic-certificate.pdf');
                assert.equal(await page.locator('#btnContinueApplicantVerification').isVisible(),false);
                assert.equal(await page.locator('#applicantReferenceVerificationWarning').isVisible(),false);
                const appearance=await page.evaluate(()=>{
                    const card=document.getElementById('applicantReferenceVerification');
                    const name=document.getElementById('applicantReferenceVerificationName');
                    const approved=document.getElementById('applicantAlreadyApprovedResult');
                    return {background:getComputedStyle(card).backgroundColor,name:getComputedStyle(name).color,
                        approvedBackground:getComputedStyle(approved).backgroundImage,approvedShadow:getComputedStyle(approved).boxShadow,
                        overflow:document.documentElement.scrollWidth>innerWidth,
                        clipped:[...card.querySelectorAll('strong,small')].filter(e=>e.getBoundingClientRect().width>0&&e.scrollWidth>e.clientWidth+1).map(e=>e.id||e.textContent)};
                });
                assert.equal(appearance.background,theme==='dark'?'rgb(17, 24, 39)':'rgb(255, 255, 255)');
                assert.equal(appearance.name,theme==='dark'?'rgb(248, 250, 252)':'rgb(23, 32, 51)');
                assert.equal(appearance.approvedBackground,'none'); assert.equal(appearance.approvedShadow,'none');
                assert.equal(appearance.overflow,false); assert.deepEqual(appearance.clipped,[]);
                await page.screenshot({path:path.join(root,`storage/app/walkin-id-lookup-${theme}-${width}-${type}.png`),fullPage:true});
                await prompt.click();
                const next=page.locator('[data-approved-clearance-continue]');
                await next.waitFor({state:'visible'});
                await page.waitForFunction(()=>!document.querySelector('[data-approved-clearance-continue]').disabled).catch(error=>{
                    throw new Error(`${theme}/${width}/${type}: ${error.message}; console: ${errors.join('; ')}`);
                });
                assert.equal(await page.locator('[data-approved-clearance-bubble]').evaluate(e=>e.matches(':popover-open')),true);
                assert.equal(await page.locator('[data-approved-clearance-bubble] > label').textContent(),'Select Clearance Type');
                assert.equal(await page.locator('[data-approved-clearance-editor]').isVisible(),false);
                assert.equal(await page.locator('[data-approved-clearance-add-category]').isVisible(),false);
                assert.equal(await page.locator('[data-approved-clearance-bubble] > select').count(),1);
                await page.locator('[data-approved-clearance-type-trigger]').click();
                const typeOption=page.locator('[data-approved-clearance-type-menu] button').nth(1);
                assert.equal(await typeOption.evaluate(e=>getComputedStyle(e).backgroundColor),'rgb(112, 19, 27)');
                await typeOption.click();
                await page.screenshot({path:path.join(root,`storage/app/walkin-clearance-types-${theme}-${width}-${type}.png`)});
                if(type==='dependent') {
                    await page.locator('[data-approved-clearance-add-type]').click();
                    await page.locator('[data-approved-clearance-editor-name]').fill('New School Certificate');
                    assert.equal(await next.isDisabled(),true);
                    await page.locator('[data-approved-clearance-add-category]').click();
                    await page.locator('[data-approved-clearance-category-name]').fill('New School Category');
                    await page.locator('[data-approved-clearance-category-save]').click();
                    await page.waitForFunction(()=>document.querySelector('[data-approved-clearance-category-editor]').hidden);
                    assert.equal(await page.locator('[data-approved-clearance-editor-name]').inputValue(),'New School Certificate');
                    assert.equal(await page.locator('[data-approved-clearance-editor-category]').inputValue(),'New School Category');
                    await page.locator('[data-approved-clearance-editor-category-trigger]').click();
                    await page.screenshot({path:path.join(root,`storage/app/walkin-clearance-editor-${theme}-${width}.png`)});
                    await page.locator('[data-approved-clearance-editor-category-menu] button').last().click();
                    await page.locator('[data-approved-clearance-editor-save]').click();
                    await page.waitForFunction(()=>document.querySelector('[data-approved-clearance-editor]').hidden);
                    assert.equal(await page.locator('[data-approved-clearance-type]').inputValue(),'mapping:30');
                    assert.equal(await page.locator('[data-approved-clearance-type-trigger] span').textContent(),'New School Certificate');
                    assert.equal(saved[0].body.audiences[0],'dependent');
                    assert.equal(saved[1].body.source_category_id,9);
                    assert.equal(saved[1].body.source_key,'employee_health_form_category');
                    await page.waitForFunction(()=>!document.querySelector('[data-approved-clearance-continue]').disabled);
                }
                await next.click();
                const posted=await page.evaluate(()=>window.submissions.at(-1));
                assert.equal(posted.url,`http://localhost/start/${type==='student'?'student':'employee'}/${type==='dependent'?19:7}`);
                assert.equal(posted.fields.category,type==='dependent'?'New School Category':'Annual Checkup');
                assert.equal(posted.fields.clearance_purpose,type==='dependent'?'mapping:30':'mapping:1');
                assert.equal(posted.fields._token,'fixture-token');
                assert.equal(posted.fields.review_after_start,'1');
                assert.equal(posted.fields.clinic_action,type==='student'?'start':undefined);
                await page.keyboard.press('Escape');
                await page.evaluate(type=>window.showRecord(type,'requested'),type);
                assert.equal(await prompt.isVisible(),false);
                assert.equal(await page.locator('#btnContinueApplicantVerification').isDisabled(),true);
                await page.evaluate(type=>window.showRecord(type,'submitted'),type);
                assert.equal(await prompt.isVisible(),false);
                assert.equal(await page.locator('#btnContinueApplicantVerification').isEnabled(),true);
                await page.evaluate(type=>window.showRecord(type),type);
                assert.equal(await prompt.isVisible(),true);
                await page.locator('#btnCancelApplicantVerification').click();
                assert.equal(await prompt.isVisible(),false);
                await page.evaluate(type=>window.showCertificateRequest(type),type);
                await page.locator('[data-clearance-documents]').waitFor({state:'visible'});
                await page.waitForFunction(()=>document.querySelector('[data-clearance-panel-status]').hidden);
                assert.equal(await page.locator('#lookupModalTitle').textContent(),'New Medical Certificate Request');
                assert.equal(await page.locator('#certificateRequestType').textContent(),'Practice Teaching / Internship');
                assert.equal(await page.locator('#certificateRequestCategory').textContent(),'Ladderized');
                assert.equal(await page.locator('#certificateRequestIdentity').textContent(),'SYNTHETIC-123');
                assert.match(await page.locator('#certificateRequestSummary').textContent(),/Previous Record[\s\S]*Approved[\s\S]*New Request[\s\S]*Pending Review/);
                assert.equal(await page.locator('#applicantReferenceVerificationEmail').isVisible(),false);
                assert.equal(await page.locator('.applicant-reference-record-found').isVisible(),false);
                assert.equal(await prompt.isVisible(),false);
                assert.equal(await page.locator('[data-clearance-documents]').isVisible(),true);
                assert.equal(await page.locator('#btnCancelApplicantVerification').textContent(),'Cancel');
                assert.equal((await page.locator('#btnContinueApplicantVerification').textContent()).trim(),'Continue');
                assert.equal(await page.locator('#btnContinueApplicantVerification').isEnabled(),true);
                const geometry=await page.locator('#certificateRequestSummary').evaluate(e=>({
                    overflow:document.documentElement.scrollWidth>innerWidth,
                    clipped:[...e.querySelectorAll('strong,small')].some(n=>n.scrollWidth>n.clientWidth+1),
                }));
                assert.deepEqual(geometry,{overflow:false,clipped:false});
                await page.screenshot({path:path.join(root,`storage/app/certificate-request-${theme}-${width}-${type}.png`)});
                await page.locator('#btnContinueApplicantVerification').click();
                assert.equal(await page.locator('#lookupModalTitle').textContent(),'Final Review');
                assert.equal(await page.evaluate(()=>window.continued.at(-1).health_form_preview.certificate_request.type),'Practice Teaching / Internship');
                if(type==='dependent') {
                    emptyHistory=true;
                    await page.evaluate(type=>window.showCertificateRequest(type),type);
                    await page.locator('[data-clearance-empty]').waitFor({state:'visible'});
                    assert.equal(await page.locator('[data-clearance-documents] h3').isVisible(),true);
                    emptyHistory=false;
                }
                await page.evaluate(type=>window.showCertificateRequest(type),type);
                await page.locator('#btnCancelApplicantVerification').click();
                assert.equal(await page.locator('#applicantReferenceVerification').isVisible(),false);
                results.push({theme,width,type,passed:true});
            }
            assert.deepEqual(errors,[]); await page.close();
        }
        console.log(JSON.stringify({passed:results.length,results},null,2));
    } finally {await browser.close();}
})().catch(error=>{console.error(error);process.exitCode=1;});
