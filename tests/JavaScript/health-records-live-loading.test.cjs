const {test}=require('node:test');
const assert=require('node:assert/strict');
const fs=require('node:fs');
const vm=require('node:vm');
const blade=fs.readFileSync(require('node:path').join(__dirname,'../../resources/views/admin/health_records.blade.php'),'utf8');
const statsStart=blade.indexOf('    (function initHealthRecordStatsLiveSync() {');
const feedStart=blade.indexOf('    (function initHealthSummaryLiveSync() {',statsStart);
const feedEnd=blade.indexOf('    (function initPremiumSelects() {',feedStart);
const statsSource=blade.slice(statsStart,feedStart).replace("@json(route('admin.health_records.stats'))", "'/stats'");
const feedSource=blade.slice(feedStart,feedEnd);
const settle=()=>new Promise(resolve=>setImmediate(resolve));
function harness(source) {
    const intervals=[],listeners={},events={},calls=[];
    let finish;
    const document={hidden:false,getElementById:()=>({textContent:'"/feed"'}),querySelectorAll:()=>[{textContent:'0',classList:{add(){},remove(){}}}],
        addEventListener:(name,handler)=>{events[name]=handler;}};
    const context=vm.createContext({document,URLSearchParams,Intl,Set,JSON,
        window:{location:{search:'?user_type=student&issued_page=2',reload(){}},setInterval:(fn,ms)=>{intervals.push({fn,ms});return intervals.length;},clearInterval(){},addEventListener:(name,fn)=>{listeners[name]=fn;}},
        fetch:(url)=>{calls.push(url);return new Promise(resolve=>{finish=resolve;});}});
    vm.runInContext(source,context);
    return {calls,intervals,events,document,finish:()=>finish?.({ok:true,json:async()=>({stats:{},notifications:[]})})};
}
test('stats reuse server-rendered counts, poll at 30 seconds and never overlap requests',async()=>{
    const h=harness(statsSource);
    assert.equal(h.calls.length,0);
    assert.equal(h.intervals[0].ms,30000);
    h.intervals[0].fn();h.intervals[0].fn();assert.equal(h.calls.length,1);
    assert.equal(h.calls[0],'/stats?user_type=student');
    h.finish();await settle();
    h.document.hidden=true;h.intervals[0].fn();assert.equal(h.calls.length,1);
    h.document.hidden=false;h.events.visibilitychange();assert.equal(h.calls.length,2);
});
test('live feed still checks for changes but does not overlap or poll hidden pages',async()=>{
    const h=harness(feedSource);
    assert.equal(h.calls.length,1);
    assert.equal(h.intervals[0].ms,15000);
    h.intervals[0].fn();assert.equal(h.calls.length,1);
    h.finish();await settle();
    h.document.hidden=true;h.intervals[0].fn();assert.equal(h.calls.length,1);
    h.document.hidden=false;h.intervals[0].fn();assert.equal(h.calls.length,2);
});
