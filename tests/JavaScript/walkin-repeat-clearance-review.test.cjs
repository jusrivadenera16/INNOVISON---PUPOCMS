const {test}=require('node:test');
const assert=require('node:assert/strict');
const fs=require('node:fs');
const vm=require('node:vm');
const blade=fs.readFileSync(require('node:path').join(__dirname,'../../resources/views/admin/walkin.blade.php'),'utf8');
const source=blade.slice(blade.indexOf('        function openExistingFinalReview(context) {'),blade.indexOf('        function renderApplicantReviewSource('));
function element(value='') {return {value,checked:false,disabled:false,textContent:'',hidden:true,style:{},classList:{add(){},remove(){}},addEventListener(){},removeEventListener(){}};}
function harness(type='student',fetcher=()=>({ok:true,json:async()=>({document:{url:'/saved.pdf'}})})) {
    const decision=element('approve'),findings=element('No Findings / Normal'),button=element(),link=element(); findings.checked=true;
    const fields={applicantHeight:element("5'6\""),applicantWeight:element('120'),applicantNormalRemarks:element('Reviewed')};
    const requests=[],messages=[],opened=[], confirmations=[],draftSaves=[],draftVisibility=[],physicalLocks=[],restored=[];
    const data={record_type:type,health_form_information:{profile_id:7}};
    const context=vm.createContext({
        window:{ReviewMedicalClearance:{configureApproved:(...args)=>opened.push(args),collect:()=>({medical_clearance:{request_key:'same-retry-key',purpose:'On the Job Training'}})}},
        document:{querySelector:selector=>selector.includes('applicant_clearance_decision')?(decision.checked?decision:null):selector.includes('applicant_findings_status')?findings:selector.includes('csrf-token')?{content:'csrf'}:selector==='[data-review-clearance-document]'?link:null,
            querySelectorAll:()=>[decision],getElementById:id=>fields[id]||null},
        currentClinicLookupData:data,currentLookupRef:'STUDENT-1',currentLookupName:'Sample Patient',repeatClearanceReview:null,isApprovalMode:false,
        isClinicLookupMode:()=>true,resetLookupButtonToFind(){},applyLookupMode(){},showLookupDetails(){},
        foundName:element(),foundCard:element(),defaultPane:element(),medicalConditionSection:element(),findBtn:button,
        modalShell:element(),lookupModalTitle:element(),lookupModalSubtitle:element(),reviewDraftBtn:element(),canApproveFinalReview:true,
        setFinalReviewPhysicalReadonly:value=>physicalLocks.push(value),setReviewDraftButtonVisible:value=>draftVisibility.push(value),syncFindingsReviewFields(){},
        applyReviewDraftFields:value=>restored.push(value),
        openFinalReviewConfirmModal:(...args)=>confirmations.push(args),setFinalReviewConfirmLoading(){},closeFinalReviewConfirmModal(){},
        saveStudentAssessmentDraft:()=>draftSaves.push('student'),saveEmployeeDraft:()=>draftSaves.push('employee'),
        doLookup(){},doApprove(){},saveStudentAssessment(){},setStatus:(...args)=>messages.push(args),
        fetch:async(url,options)=>{requests.push([url,options]);return fetcher(url,options);}
    });
    vm.runInContext(source,context);
    const input={type,id:7,url:`/admin/medical-clearances/${type}/7`,category:'On the Job Training',options:{can_issue:true,submission_id:19}};
    return {context,input,decision,button,link,requests,messages,opened,confirmations,draftSaves,draftVisibility,physicalLocks,restored,
        open:()=>context.window.ClinicExistingFinalReview.open(input),submit:()=>context.submitRepeatMedicalClearance(),confirm:()=>context.submitRepeatMedicalClearance(true)};
}
test('student and employee Continue open the existing shared review and require a fresh decision',()=>{
    for(const type of ['student','employee']) {const h=harness(type);assert.equal(h.open(),true);assert.equal(h.opened.length,1);assert.equal(h.button.textContent,'Approve');assert.equal(h.decision.checked,false);assert.equal(h.requests.length,0);
        assert.equal(h.draftVisibility.at(-1),true);assert.equal(h.physicalLocks.at(-1),true);assert.equal(h.context.isApprovalMode,true);}
    const applicant=harness('applicant');assert.equal(applicant.open(),false);
    const stale=harness();stale.input.id=8;assert.equal(stale.open(),false);
});
test('pending or missing decision never submits or calls an approval endpoint',async()=>{
    const h=harness();h.open();await h.submit();assert.equal(h.requests.length,0);
    h.decision.checked=true;h.decision.value='pending';await h.submit();assert.equal(h.requests.length,0);
    assert.equal(h.confirmations.at(-1)[1],true); await h.confirm(); assert.deepEqual(h.draftSaves,['student']);
});
test('shared review issues a certificate only, saves its assessment, and prevents duplicate clicks',async()=>{
    let finish;const h=harness('student',()=>new Promise(resolve=>{finish=resolve;}));h.open();h.decision.checked=true;
    await h.submit(); assert.equal(h.requests.length,0); assert.equal(h.confirmations.at(-1)[1],false);
    const pending=h.confirm();await h.confirm();assert.equal(h.requests.length,1);
    const [url,options]=h.requests[0],body=JSON.parse(options.body);
    assert.match(url,/medical-clearances\/student\/7$/);assert.equal(body.submission_id,19);assert.equal(body.doctor_review.assessment.height,"5'6\"");
    assert.equal(body.doctor_review.remarks,'Reviewed');assert.equal(body.issued_on,undefined);assert.equal(body.patient_name,undefined);
    finish({ok:true,json:async()=>({document:{url:'/saved.pdf'}})});await pending;await h.submit();
    assert.equal(h.requests.length,1);assert.equal(h.link.hidden,false);assert.equal(h.button.disabled,true);
});
test('failed issuance permits retry without a different request identity',async()=>{
    const h=harness('student',()=>({ok:false,json:async()=>({message:'Try again'})}));h.open();h.decision.checked=true;
    await h.confirm();await h.confirm();assert.equal(h.button.disabled,false);
    assert.equal(JSON.parse(h.requests[0][1].body).request_key,JSON.parse(h.requests[1][1].body).request_key);
});

test('saved review resumes its existing assessment, purpose and request identity in the same UI',()=>{
    const h=harness(); h.input.draft={applicant_height:"5'8\"",medical_clearance:{request_key:'saved-key',purpose:'mapping:12'},
        medical_clearance_review:{submission_id:19,category:'On the Job Training'}};
    h.open(); assert.equal(h.restored[0],h.input.draft);
    assert.equal(h.opened[0][2],h.input.draft.medical_clearance);
    assert.equal(h.button.textContent,'Approve'); assert.equal(h.draftVisibility.at(-1),true);
});

test('shared Draft collector includes purpose and repeat-review context without creating a new submission',()=>{
    const h=harness(); h.open();
    h.context.window.ReviewMedicalClearance.exportDraft=()=>({request_key:'draft-key',purpose:'mapping:12',other_purpose:''});
    h.context.document.querySelectorAll=()=>[];
    const collector=blade.slice(blade.indexOf('        function collectReviewDraftData('),blade.indexOf('        function collectEmployeeDraftData('));
    vm.runInContext(collector,h.context);
    const draft=JSON.parse(JSON.stringify(h.context.collectReviewDraftData('student_local')));
    assert.deepEqual(draft.medical_clearance_review,{submission_id:19,category:'On the Job Training'});
    assert.equal(draft.medical_clearance.purpose,'mapping:12');assert.equal(draft.medical_clearance.request_key,'draft-key');
    assert.equal(h.requests.length,0);
});

test('the existing confirmation dispatches repeat approval only to certificate issuance',async()=>{
    const h=harness();h.open();h.decision.checked=true;
    const dispatcher=blade.slice(blade.indexOf('        function submitFinalReviewApproval('),blade.indexOf('        function collectReviewDraftData('));
    vm.runInContext(dispatcher,h.context);
    await h.context.submitFinalReviewApproval({repeat_clearance_review:true},false);
    assert.equal(h.requests.length,1);assert.match(h.requests[0][0],/medical-clearances\/student\/7$/);
    assert.equal(h.button.textContent,'Approved');
});
