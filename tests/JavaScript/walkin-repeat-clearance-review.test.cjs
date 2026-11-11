const {test}=require('node:test');
const assert=require('node:assert/strict');
const fs=require('node:fs');
const vm=require('node:vm');
const blade=fs.readFileSync(require('node:path').join(__dirname,'../../resources/views/admin/walkin.blade.php'),'utf8');
const source=blade.slice(blade.indexOf('        function openExistingFinalReview(context) {'),blade.indexOf('        function renderApplicantReviewSource('));
function element(value='') {return {value,checked:false,disabled:false,textContent:'',hidden:true,style:{},classList:{add(){}},addEventListener(){},removeEventListener(){}};}
function harness(type='student',fetcher=()=>({ok:true,json:async()=>({document:{url:'/saved.pdf'}})})) {
    const decision=element('approve'),findings=element('No Findings / Normal'),button=element(),link=element(); findings.checked=true;
    const fields={applicantHeight:element("5'6\""),applicantWeight:element('120'),applicantNormalRemarks:element('Reviewed')};
    const requests=[],messages=[],opened=[];
    const data={record_type:type,health_form_information:{profile_id:7}};
    const context=vm.createContext({
        window:{ReviewMedicalClearance:{configureApproved:(...args)=>opened.push(args),collect:()=>({medical_clearance:{request_key:'same-retry-key',purpose:'On the Job Training'}})}},
        document:{querySelector:selector=>selector.includes('applicant_clearance_decision')?(decision.checked?decision:null):selector.includes('applicant_findings_status')?findings:selector.includes('csrf-token')?{content:'csrf'}:selector==='[data-review-clearance-document]'?link:null,
            querySelectorAll:()=>[decision],getElementById:id=>fields[id]||null},
        currentClinicLookupData:data,currentLookupRef:'STUDENT-1',currentLookupName:'Sample Patient',repeatClearanceReview:null,isApprovalMode:false,
        isClinicLookupMode:()=>true,resetLookupButtonToFind(){},applyLookupMode(){},showLookupDetails(){},
        foundName:element(),foundCard:element(),defaultPane:element(),medicalConditionSection:element(),findBtn:button,
        setFinalReviewPhysicalReadonly(){},setReviewDraftButtonVisible(){},syncFindingsReviewFields(){},
        doLookup(){},doApprove(){},saveStudentAssessment(){},setStatus:(...args)=>messages.push(args),
        fetch:async(url,options)=>{requests.push([url,options]);return fetcher(url,options);}
    });
    vm.runInContext(source,context);
    const input={type,id:7,url:`/admin/medical-clearances/${type}/7`,category:'On the Job Training',options:{can_issue:true,submission_id:19}};
    return {context,input,decision,button,link,requests,messages,opened,open:()=>context.window.ClinicExistingFinalReview.open(input),submit:()=>context.submitRepeatMedicalClearance()};
}
test('student and employee Continue open the existing shared review and require a fresh decision',()=>{
    for(const type of ['student','employee']) {const h=harness(type);assert.equal(h.open(),true);assert.equal(h.opened.length,1);assert.equal(h.button.textContent,'Issue Medical Clearance');assert.equal(h.decision.checked,false);assert.equal(h.requests.length,0);}
    const applicant=harness('applicant');assert.equal(applicant.open(),false);
    const stale=harness();stale.input.id=8;assert.equal(stale.open(),false);
});
test('pending or missing decision never submits or calls an approval endpoint',async()=>{
    const h=harness();h.open();await h.submit();assert.equal(h.requests.length,0);
    h.decision.checked=true;h.decision.value='pending';await h.submit();assert.equal(h.requests.length,0);
});
test('shared review issues a certificate only, saves its assessment, and prevents duplicate clicks',async()=>{
    let finish;const h=harness('student',()=>new Promise(resolve=>{finish=resolve;}));h.open();h.decision.checked=true;
    const pending=h.submit();await h.submit();assert.equal(h.requests.length,1);
    const [url,options]=h.requests[0],body=JSON.parse(options.body);
    assert.match(url,/medical-clearances\/student\/7$/);assert.equal(body.submission_id,19);assert.equal(body.doctor_review.assessment.height,"5'6\"");
    assert.equal(body.doctor_review.remarks,'Reviewed');assert.equal(body.issued_on,undefined);assert.equal(body.patient_name,undefined);
    finish({ok:true,json:async()=>({document:{url:'/saved.pdf'}})});await pending;await h.submit();
    assert.equal(h.requests.length,1);assert.equal(h.link.hidden,false);assert.equal(h.button.disabled,true);
});
test('failed issuance permits retry without a different request identity',async()=>{
    const h=harness('student',()=>({ok:false,json:async()=>({message:'Try again'})}));h.open();h.decision.checked=true;
    await h.submit();await h.submit();assert.equal(h.button.disabled,false);
    assert.equal(JSON.parse(h.requests[0][1].body).request_key,JSON.parse(h.requests[1][1].body).request_key);
});
