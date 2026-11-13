const {test} = require('node:test');
const assert = require('node:assert/strict');
const fs = require('node:fs');
const path = require('node:path');
const vm = require('node:vm');
const blade = fs.readFileSync(path.join(__dirname, '../../resources/views/login.blade.php'), 'utf8');
const start = blade.indexOf('      (function initLocalLoginPasswordToggle() {');
const end = blade.indexOf('      (function () {', start);
const source = blade.slice(start, end);
function element() {
    return {hidden: false, attrs: {}, handlers: {}, addEventListener(name, fn) {this.handlers[name] = fn;},
        setAttribute(name, value) {this.attrs[name] = value;}};
}
function harness(present = true) {
    const toggle = element(), showIcon = element(), hideIcon = element(), form = element(), submit = element(), window = element();
    const password = {type: 'password', value: 'unchanged-test-value', form};
    toggle.querySelector = selector => selector === '[data-password-show]' ? showIcon : hideIcon;
    const nodes = {localLoginPassword: password, localLoginPasswordToggle: toggle, loginSubmitBtn: submit};
    vm.runInNewContext(source, {document: {getElementById: id => present ? nodes[id] : null}, window});
    return {toggle, password, showIcon, hideIcon, form, submit, window};
}
test('eye button shows and hides the password without changing its value or submitting', () => {
    const h = harness();
    assert.equal(h.password.type, 'password');
    assert.equal(h.toggle.attrs['aria-label'], 'Show password');
    assert.equal(h.toggle.hidden, false);
    h.toggle.handlers.click();
    assert.equal(h.password.type, 'text');
    assert.equal(h.toggle.attrs['aria-pressed'], 'true');
    assert.equal(h.toggle.attrs['aria-label'], 'Hide password');
    assert.equal(h.showIcon.hidden, true);
    assert.equal(h.hideIcon.hidden, false);
    h.toggle.handlers.click();
    assert.equal(h.password.type, 'password');
    assert.equal(h.password.value, 'unchanged-test-value');
    assert.equal(h.toggle.attrs['aria-pressed'], 'false');
    assert.doesNotMatch(source, /fetch\(|\.submit\(|localStorage|sessionStorage|console\./);
});
test('login submission and returning to the page mask a previously visible password', () => {
    const h = harness();
    for (const handler of [h.form.handlers.submit, h.submit.handlers.click, h.window.handlers.pageshow]) {
        h.toggle.handlers.click(); assert.equal(h.password.type, 'text');
        handler(); assert.equal(h.password.type, 'password');
        assert.equal(h.password.value, 'unchanged-test-value');
    }
});
test('both local login branches reuse an accessible non-submit button; IdP-only pages are safe', () => {
    assert.doesNotThrow(() => harness(false));
    assert.equal(blade.split("@include('partials.local-login-password')").length - 1, 2);
    const partial = fs.readFileSync(path.join(__dirname, '../../resources/views/partials/local-login-password.blade.php'), 'utf8');
    assert.match(partial, /type="button"/);
    assert.match(partial, /aria-controls="localLoginPassword"/);
    assert.match(partial, /autocomplete="current-password"/);
    assert.doesNotMatch(partial, /value=|old\('password'\)/);
});
