const {test}=require('node:test');
const assert=require('node:assert/strict');
const fs=require('node:fs');
const vm=require('node:vm');
const path=require('node:path');
const blade=fs.readFileSync(path.join(__dirname,'../../resources/views/admin/walkin.blade.php'),'utf8');
const start=blade.indexOf('        function renderDocuments(documents) {');
const end=blade.indexOf('        function setEntryMode(',start);
const source=blade.slice(start,end);
function element(tag='div') {return {tag,children:[],textContent:'',className:'',classList:{add(){}},setAttribute(){},append(...items){this.children.push(...items);},appendChild(item){this.children.push(item);},replaceChildren(){this.children=[];}};}
test('all document types use labels instead of private file previews and only Open links carry file URLs',()=>{
    const grid=element(),created=[];
    const context=vm.createContext({currentDocuments:[],documentsCount:element(),documentsGrid:grid,closeDocumentPreview(){},syncDocumentsModalCopy(){return 'No uploaded documents';},
        document:{createElement:tag=>{const node=element(tag);created.push(node);return node;}}});
    vm.runInContext(source,context);
    const documents=[{type:'image',label:'2x2 Photo',url:'/private/photo.jpg'}, {type:'pdf',label:'Medical Certificate',url:'/private/certificate.pdf'},
        {type:'form',label:'Health Information Form',url:'/private/form'}, {type:'pdf',label:'Health Declaration',url:'/private/declaration'},
        {type:'image',label:'Chest X-ray Result',missing:true}];
    context.renderDocuments(documents);
    assert.equal(grid.children.length,5);
    assert.equal(created.some(node=>['img','iframe'].includes(node.tag)),false);
    assert.equal(created.filter(node=>node.className==='private-document-thumbnail').length,5);
    assert.deepEqual(created.filter(node=>node.tag==='a').map(node=>node.href),documents.filter(doc=>doc.url).map(doc=>doc.url));
    for(const link of created.filter(node=>node.tag==='a')) {assert.equal(link.textContent,'Open');assert.equal(link.target,'_blank');}
    assert.ok(created.some(node=>node.textContent==='Missing'));
    assert.doesNotMatch(source,/fetch\(|previewDocument\(|\.src\s*=/);
    assert.ok(!blade.includes('firstPreviewableIndex'));
});
