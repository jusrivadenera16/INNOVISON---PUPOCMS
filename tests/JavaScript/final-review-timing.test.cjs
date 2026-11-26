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
            fetch:async(_url,input)=>{body=JSON.parse(input.body);return {ok:true,json:async()=>({success:true})};}});
        vm.runInContext(blade.slice(start,end),context);
        assert.equal((await context.markFinalReviewTimeIn('REF-123',{record_type:type,health_form_information:{profile_id:7}})).ok,true);
        assert.equal(body.profile_id,7);
        assert.equal(body.record_type,['employee','dependent'].includes(type)?'employee':'student');
        if(type==='dependent') {
            await context.markFinalReviewTimeIn('DEP-1',{record_type:'dependent',health_form_information:{profile_id:2},review_timing_record_type:'employee',review_timing_profile_id:19});
            assert.equal(body.profile_id,19);
            assert.equal(body.record_type,'employee');
        }
        context.fetch=async()=>({ok:false,status:500,json:async()=>({success:false})});context.console={warn(){}};
        const failed=await context.markFinalReviewTimeIn('REF-123');
        assert.equal(failed.ok,false);
        assert.match(failed.message,/server could not save/i);
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
        assert.ok(body.includes('const timeInResult = await openingRequest;'));
        assert.ok(body.includes('if (!timeInResult?.ok)'));
    }
});

test('time-in rejects unconfirmed saves, expired sessions, validation failures, and network errors',async()=>{
    for(const [response,expected] of [
        [{ok:true,status:200,json:async()=>({success:false})},/did not confirm/],
        [{ok:true,status:200,json:async()=>{throw new SyntaxError('Invalid JSON');}},/did not confirm/],
        [{ok:false,status:403,json:async()=>({})},/do not have permission/],
        [{ok:false,status:419,json:async()=>({})},/session may have expired/],
        [{ok:false,status:404,json:async()=>({})},/could not be found/],
        [{ok:false,status:422,json:async()=>({errors:{reference_number:['Not ready for review.']}})},/Not ready for review/],
        [{ok:false,status:500,json:async()=>({})},/server could not save/],
    ]) {
        const statuses=[];
        const context=vm.createContext({Promise,canApproveFinalReview:true,canLookupEmployeeRecords:false,canReviewFinalSubmission:false,
            finalReviewTimeInUrl:'/time-in',document:{querySelector:()=>({content:'csrf'})},console:{warn(){}},
            setStatus:(...args)=>statuses.push(args),fetch:async()=>response});
        vm.runInContext(blade.slice(start,end),context);
        const result=await context.markFinalReviewTimeIn('REF-123');
        assert.equal(result.ok,false);
        assert.match(result.message,expected);
        assert.deepEqual(statuses,[['error',result.message]]);
    }
    const context=vm.createContext({Promise,TypeError,canApproveFinalReview:true,canLookupEmployeeRecords:false,canReviewFinalSubmission:false,
        finalReviewTimeInUrl:'/time-in',document:{querySelector:()=>({content:'csrf'})},console:{warn(){}},setStatus(){},
        fetch:async()=>{throw new TypeError('Failed to fetch');}});
    vm.runInContext(blade.slice(start,end),context);
    const result=await context.markFinalReviewTimeIn('REF-123');
    assert.equal(result.ok,false);
    assert.match(result.message,/Could not reach the server/);
});

test('missing permissions, reference, or endpoint cannot issue a time-in request',async()=>{
    for(const fixture of [
        {canApproveFinalReview:false,reference:'REF-123',url:'/time-in'},
        {canApproveFinalReview:true,reference:'',url:'/time-in'},
        {canApproveFinalReview:true,reference:'REF-123',url:''},
    ]) {
        let calls=0;
        const context=vm.createContext({Promise,canApproveFinalReview:fixture.canApproveFinalReview,canLookupEmployeeRecords:false,canReviewFinalSubmission:false,
            finalReviewTimeInUrl:fixture.url,setStatus(){},fetch:async()=>{calls++;}});
        vm.runInContext(blade.slice(start,end),context);
        assert.equal((await context.markFinalReviewTimeIn(fixture.reference)).ok,false);
        assert.equal(calls,0);
    }
});
