const {test}=require('node:test');
const assert=require('node:assert/strict');
const fs=require('node:fs');
const path=require('node:path');
const vm=require('node:vm');
const source=fs.readFileSync(path.join(__dirname,'../../public/js/medical-clearance-print-prompt.js'),'utf8');
function harness() {
    const document={handlers:{},activeElement:null,getElementById:()=>root,addEventListener(name,fn){this.handlers[name]=fn;}};
    function element(){return {isConnected:true,handlers:{},attributes:{},addEventListener(name,fn){this.handlers[name]=fn;},click(){this.handlers.click?.();},focus(){document.activeElement=this;},setAttribute(name,value){this.attributes[name]=value;}};}
    const root=element(),later=element(),view=element(),previous=element(),opened=[];
    const classes=new Set();root.classList={add:value=>classes.add(value),remove:value=>classes.delete(value)};
    root.querySelector=selector=>selector.includes('later')?later:view;
    document.activeElement=previous;
    const window={location:{href:'http://localhost/admin/walkin',origin:'http://localhost'},open:(...args)=>opened.push(args)};
    vm.runInNewContext(source,{URL,window,document});
    return {api:window.MedicalClearancePrintPrompt,root,later,view,previous,document,opened,classes};
}
test('prompt contains exactly View / Print and Not Now, matching confirmation modal UI',()=>{
    const html=fs.readFileSync(path.join(__dirname,'../../resources/views/admin/partials/medical-clearance-print-prompt.blade.php'),'utf8');
    assert.equal((html.match(/<button\b/g)||[]).length,2);
    assert.match(html,/>Not Now<\/span>/);assert.match(html,/>View \/ Print<\/span>/);
    assert.match(html,/clinic-confirm-card/);assert.doesNotMatch(html,/data-success-close/);
});
test('Not Now closes only the print prompt, without printing or any request',()=>{
    const h=harness();let next=0;assert.equal(h.api.show('/saved.pdf',()=>next++),true);
    assert.equal(h.document.activeElement,h.later);assert.equal(h.opened.length,0);
    h.later.click();h.view.click();assert.equal(next,1);assert.equal(h.opened.length,0);assert.equal(h.classes.has('is-open'),false);
    assert.equal(h.document.activeElement,h.previous);
});
test('View / Print opens the saved PDF once and then continues the existing workflow',()=>{
    const h=harness();let next=0;h.api.show('/saved.pdf',()=>next++);h.view.click();h.view.click();
    assert.deepEqual(h.opened,[['http://localhost/saved.pdf','_blank','noopener']]);assert.equal(next,1);
});
test('Escape means Not Now, focus stays inside two actions, and external/empty URLs are refused',()=>{
    const h=harness();assert.equal(h.api.show('https://external.example/test.pdf'),false);assert.equal(h.api.show(''),false);
    let next=0;h.api.show('/saved.pdf',()=>next++);
    const event={key:'Tab',shiftKey:true,preventDefault(){}};h.document.handlers.keydown(event);assert.equal(h.document.activeElement,h.view);
    event.shiftKey=false;h.document.handlers.keydown(event);assert.equal(h.document.activeElement,h.later);
    h.document.handlers.keydown({key:'Escape',preventDefault(){},stopImmediatePropagation(){}});assert.equal(next,1);assert.equal(h.opened.length,0);
});
test('print choice comes before Send Evaluation and does not skip that decision',()=>{
    const blade=fs.readFileSync(path.join(__dirname,'../../resources/views/admin/walkin.blade.php'),'utf8');
    const start=blade.indexOf('        function showClinicSuccessOverlay('),end=blade.indexOf('        function closeFinalReviewErrorModal',start);
    assert.ok(start>0&&end>start);
    let printDone,finish=0,evaluations=[];
    const overlay={classList:{add(){}},setAttribute(){}};
    const context=vm.createContext({window:{MedicalClearancePrintPrompt:{show:(url,next)=>{printDone=next;return true;}}},
        approvalOverlay:overlay,approvalOverlayTitle:{},approvalOverlayMessage:{},pendingApprovalEvaluation:null,approvalEvaluationInProgress:false,approvalEvaluationActions:null,
        setApprovalEvaluationPromptVisible:value=>evaluations.push(value),activeSuccessAction:null,activeSuccessTimer:null,setTimeout(){},clearTimeout(){}});
    vm.runInContext(blade.slice(start,end),context);
    context.showClinicSuccessOverlay(overlay,{medicalClearanceUrl:'/saved.pdf',evaluationPrompt:{sourceId:5},persistent:true,onDone:()=>finish++});
    assert.equal(evaluations.length,0);assert.equal(finish,0);printDone();assert.deepEqual(evaluations,[true]);assert.equal(finish,0);
    context.showClinicSuccessOverlay(overlay,{medicalClearanceUrl:'/saved.pdf',onDone:()=>finish++});printDone();assert.equal(finish,1);
});
