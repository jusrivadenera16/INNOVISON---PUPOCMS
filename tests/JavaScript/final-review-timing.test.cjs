const {test}=require('node:test');
const assert=require('node:assert/strict');
const fs=require('node:fs');
const vm=require('node:vm');
const blade=fs.readFileSync(require('node:path').join(__dirname,'../../resources/views/admin/walkin.blade.php'),'utf8');
const start=blade.indexOf('        function markFinalReviewTimeIn(');
const end=blade.indexOf('        function buildFinalReviewCard(',start);
test('time-in identifies every patient type and only returns success after the server saves it',async()=>{
    for(const type of ['student','applicant','transferee','employee','dependent']) {
        let body;
        const context=vm.createContext({Promise,canApproveFinalReview:true,canLookupEmployeeRecords:true,canReviewFinalSubmission:true,
            finalReviewTimeInUrl:'/time-in',document:{querySelector:()=>({content:'csrf'})},console,setStatus(){},
            fetch:async(_url,input)=>{body=JSON.parse(input.body);return {ok:true};}});
        vm.runInContext(blade.slice(start,end),context);
        assert.equal(await context.markFinalReviewTimeIn('REF-123',{record_type:type,health_form_information:{profile_id:7}}),true);
        assert.equal(body.profile_id,7);
        assert.equal(body.record_type,['employee','dependent'].includes(type)?'employee':'student');
        if(type==='dependent') {
            await context.markFinalReviewTimeIn('DEP-1',{record_type:'dependent',health_form_information:{profile_id:2},review_timing_record_type:'employee',review_timing_profile_id:19});
            assert.equal(body.profile_id,19);
            assert.equal(body.record_type,'employee');
        }
        context.fetch=async()=>({ok:false});context.console={warn(){}};
        assert.equal(await context.markFinalReviewTimeIn('REF-123'),false);
    }
});
test('Final Review opening is the only time-in trigger; Back and X cannot time-out a record',()=>{
    assert.ok(!blade.includes('markFinalReviewTimeOut('));
    assert.ok(!blade.includes('finalReviewTimeOutUrl'));
    assert.equal((blade.match(/markFinalReviewTimeIn\(referenceNumber, data\)/g)||[]).length,1);
    for(const name of ['doApprove','saveStudentAssessment']) {
        const body=blade.slice(blade.indexOf(`async function ${name}()`),blade.indexOf(`async function ${name}()`)+700);
        assert.ok(body.includes('await openingRequest'));
        assert.ok(body.includes('openingRequest !== finalReviewTimeInRequest || openingReference !== currentLookupRef'));
        assert.ok(body.includes('if (timeInSaved === false)'));
    }
});
