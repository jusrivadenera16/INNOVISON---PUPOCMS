const {test} = require('node:test');
const assert = require('node:assert/strict');
const fs = require('node:fs');
const path = require('node:path');
const vm = require('node:vm');
const source = fs.readFileSync(path.join(__dirname, '../../public/js/pending-approval-document-preview.js'), 'utf8');
const blade = fs.readFileSync(path.join(__dirname, '../../resources/views/admin/health_records.blade.php'), 'utf8');
function harness() {
    const window = {location: {href: 'https://clinic.example/health-records', origin: 'https://clinic.example'}};
    vm.runInNewContext(source, {window, URL});
    return window;
}
test('pending images and PDFs preview using the existing authorized same-origin URLs', () => {
    const render = harness().PendingApprovalDocumentPreview.render;
    const image = render({url: '/private/photo', title: '2x2 Photo', preview_type: 'image'}, true);
    assert.match(image, /<img src="https:\/\/clinic.example\/private\/photo"/);
    assert.match(image, /loading="lazy"/);
    const pdf = render({url: '/health-form/3?fresh=1', title: 'Health Information Form', preview_type: 'pdf'}, true);
    assert.match(pdf, /<iframe/);
    assert.match(pdf, /https:\/\/clinic.example\/health-form\/3\?fresh=1#toolbar=0&amp;navpanes=0&amp;view=FitH/);
});
test('non-pending cards, missing files, unsupported files and external URLs keep their placeholder', () => {
    const render = harness().PendingApprovalDocumentPreview.render;
    assert.equal(render({url: '/private/photo', preview_type: 'image'}), '');
    for (const doc of [{preview_type: 'pdf'}, {url: '/private/svg', preview_type: 'file'},
        {url: 'https://external.example/private.pdf', preview_type: 'pdf'}, {url: 'javascript:alert(1)', preview_type: 'image'}]) {
        assert.equal(render(doc, true), '');
    }
    assert.equal(render({url: '/private/photo', preview_type: 'image'}, false), '');
});
test('document titles cannot inject markup and preview code does not fetch files on initialization', () => {
    const html = harness().PendingApprovalDocumentPreview.render({url: '/private/photo', title: '"><script>alert(1)</script>', preview_type: 'image'}, true);
    assert.ok(!html.includes('<script>'));
    assert.match(html, /&lt;script&gt;/);
    assert.doesNotMatch(source, /fetch\(|XMLHttpRequest|createElement\(|innerHTML\s*=/);
});
test('actual modal renderer enables previews only for a pending payload and retains missing-upload warnings', () => {
    const window = harness(), verificationDocsGrid = {innerHTML: ''};
    const start = blade.indexOf('    function renderVerificationDocuments(');
    const end = blade.indexOf('    function syncApprovalButtonState()', start);
    const context = vm.createContext({window, verificationDocsGrid, escapeHtml: value => String(value).replace(/&/g, '&amp;').replace(/</g, '&lt;').replace(/"/g, '&quot;'), updateVerificationDocumentCount() {}});
    vm.runInContext(blade.slice(start, end), context);
    const docs = [{url: '/private/photo', title: 'Photo', preview_type: 'image'}, {title: 'X-ray'}];
    context.renderVerificationDocuments(docs);
    assert.ok(!verificationDocsGrid.innerHTML.includes('<img'));
    context.renderVerificationDocuments(docs, true);
    assert.match(verificationDocsGrid.innerHTML, /<img/);
    assert.match(verificationDocsGrid.innerHTML, /Missing upload/);
    assert.match(verificationDocsGrid.innerHTML, /href="\/private\/photo"/);
    assert.equal(blade.split("'preview_documents' => true").length - 1, 1);
    assert.match(blade, /renderVerificationDocuments\(payload.documents \|\| \[\], payload.preview_documents === true\)/);
});
