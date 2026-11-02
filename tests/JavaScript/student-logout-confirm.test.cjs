const assert = require('node:assert/strict');
const fs = require('node:fs');
const path = require('node:path');
const test = require('node:test');
const vm = require('node:vm');

const partial = fs.readFileSync(path.join(__dirname, '../../resources/views/partials/student-logout-confirm.blade.php'), 'utf8');
const layout = fs.readFileSync(path.join(__dirname, '../../resources/views/layouts/student.blade.php'), 'utf8');
const confirmationSource = partial.slice(partial.indexOf('<script>') + '<script>'.length, partial.indexOf('</script>'));
const loaderMarker = layout.indexOf("const loader = document.getElementById('studentLogoutLoader');");
const loaderStart = layout.lastIndexOf('(function () {', loaderMarker);
const loaderSource = layout.slice(loaderStart, layout.indexOf('</script>', loaderMarker)).trim();
assert.ok(confirmationSource.includes('function initStudentLogoutConfirm()'));
assert.ok(loaderMarker >= 0 && loaderStart >= 0);

function harness() {
    let document;
    let clock = 0;
    let nextTimer = 0;
    const timers = new Map();
    const frames = [];
    const submissions = [];

    class HTMLElement extends EventTarget {
        constructor(id, initialClasses = []) {
            super();
            this.id = id;
            this.attributes = new Map();
            this.style = {};
            this.visible = true;
            this.disabled = false;
            const classes = new Set(initialClasses);
            this.classList = {
                add: (...names) => names.forEach(name => classes.add(name)),
                remove: (...names) => names.forEach(name => classes.delete(name)),
                contains: name => classes.has(name),
            };
        }
        setAttribute(name, value) { this.attributes.set(name, String(value)); }
        getAttribute(name) { return this.attributes.get(name) ?? null; }
        removeAttribute(name) { this.attributes.delete(name); }
        focus() { document.activeElement = this; }
        getClientRects() { return this.visible ? [{}] : []; }
        click() { if (!this.disabled) this.dispatchEvent(new Event('click', { cancelable: true })); }
    }

    class HTMLFormElement extends HTMLElement {
        requestCount = 0;
        requestSubmit() {
            this.requestCount++;
            const event = new Event('submit', { cancelable: true });
            if (this.dispatchEvent(event)) this.submit();
        }
        submit() { submissions.push(this.id); }
    }

    const modal = new HTMLElement('studentLogoutConfirm');
    const confirm = new HTMLElement('studentLogoutConfirmSubmit');
    const cancel = new HTMLElement('studentLogoutConfirmCancel');
    const loader = new HTMLElement('studentLogoutLoader', ['hidden']);
    const normalForm = new HTMLFormElement('logout-form');
    const termsForm = new HTMLFormElement('termsGateLogoutForm');
    const desktop = new HTMLElement('desktopLogout');
    const mobile = new HTMLElement('mobileLogout');
    const navToggle = new HTMLElement('navToggle');
    const elements = new Map([modal, confirm, cancel, loader, normalForm, termsForm].map(element => [element.id, element]));
    document = new EventTarget();
    document.body = new HTMLElement('body');
    document.activeElement = document.body;
    document.getElementById = id => elements.get(id) ?? null;
    document.querySelectorAll = selector => {
        if (selector === '[data-student-logout-trigger]' || selector === '.desktop-account-logout, .student-logout-btn') return [desktop, mobile];
        if (selector === '.nav-toggle, .nav-dropdown-toggle') return [navToggle];
        return [];
    };

    const window = {
        setTimeout(callback, delay) {
            const id = ++nextTimer;
            timers.set(id, { callback, time: clock + delay });
            return id;
        },
        clearTimeout: id => timers.delete(id),
        requestAnimationFrame: callback => frames.push(callback),
    };
    vm.runInNewContext(confirmationSource + '\n' + loaderSource, { document, window, HTMLElement, HTMLFormElement });

    function advance(milliseconds) {
        clock += milliseconds;
        while (true) {
            const ready = [...timers].filter(([, item]) => item.time <= clock).sort((a, b) => a[1].time - b[1].time);
            if (!ready.length) return;
            const [id, item] = ready[0];
            timers.delete(id);
            item.callback();
        }
    }
    function frame() { frames.splice(0).forEach(callback => callback()); }
    function key(key, shiftKey = false) {
        const event = new Event('keydown', { cancelable: true });
        Object.defineProperties(event, { key: { value: key }, shiftKey: { value: shiftKey } });
        document.dispatchEvent(event);
        return event;
    }
    function open(trigger = desktop) {
        trigger.focus();
        trigger.click();
        advance(40);
    }
    return { document, modal, confirm, cancel, loader, normalForm, termsForm, desktop, mobile, navToggle, submissions, advance, frame, key, open };
}

for (const trigger of ['desktop', 'mobile']) {
    test(`${trigger} logout opens confirmation without submitting or starting the loader`, () => {
        const h = harness();
        h.open(h[trigger]);
        assert.equal(h.modal.classList.contains('is-open'), true);
        assert.equal(h.modal.getAttribute('aria-hidden'), 'false');
        assert.equal(h.document.activeElement, h.confirm);
        assert.equal(h.normalForm.requestCount, 0);
        assert.equal(h.loader.classList.contains('hidden'), true);
        assert.deepEqual(h.submissions, []);
    });
}

test('Cancel, Escape and backdrop close safely and allow reopening', () => {
    const h = harness();
    for (const dismiss of [() => h.cancel.click(), () => h.key('Escape'), () => h.modal.click()]) {
        h.open();
        dismiss();
        assert.equal(h.modal.getAttribute('aria-hidden'), 'true');
        h.advance(180);
        assert.equal(h.modal.classList.contains('is-open'), false);
        assert.equal(h.document.activeElement, h.desktop);
        assert.equal(h.normalForm.requestCount, 0);
        assert.equal(h.loader.classList.contains('hidden'), true);
        assert.deepEqual(h.submissions, []);
    }
    h.open(h.mobile);
    assert.equal(h.modal.classList.contains('is-open'), true);
});

test('Confirm uses the existing loader and submits once after two animation frames', () => {
    const h = harness();
    h.open();
    h.confirm.click();
    assert.equal(h.normalForm.requestCount, 1);
    assert.equal(h.modal.classList.contains('is-open'), false);
    assert.equal(h.loader.classList.contains('hidden'), false);
    assert.equal(h.loader.getAttribute('aria-hidden'), 'false');
    assert.equal(h.document.body.getAttribute('aria-busy'), 'true');
    assert.equal(h.desktop.getAttribute('aria-disabled'), 'true');
    assert.equal(h.mobile.getAttribute('aria-disabled'), 'true');
    assert.equal(h.confirm.disabled, true);
    assert.equal(h.cancel.disabled, true);
    assert.deepEqual(h.submissions, []);

    // Even a directly dispatched duplicate bypassing native disabled-click filtering is ignored.
    h.confirm.dispatchEvent(new Event('click'));
    h.normalForm.requestSubmit();
    h.desktop.click();
    h.frame();
    assert.deepEqual(h.submissions, []);
    h.frame();
    assert.deepEqual(h.submissions, ['logout-form']);
    h.frame();
    assert.deepEqual(h.submissions, ['logout-form']);
});

test('Terms-gate cancellation still directly invokes its own logout form and loader', () => {
    const h = harness();
    h.termsForm.requestSubmit();
    assert.equal(h.modal.classList.contains('is-open'), false);
    assert.equal(h.loader.classList.contains('hidden'), false);
    assert.equal(h.normalForm.requestCount, 0);
    h.frame();
    h.frame();
    assert.deepEqual(h.submissions, ['termsGateLogoutForm']);
});

test('Tab and Shift+Tab keep focus on confirmation actions', () => {
    const h = harness();
    h.open();
    assert.equal(h.key('Tab', true).defaultPrevented, true);
    assert.equal(h.document.activeElement, h.cancel);
    assert.equal(h.key('Tab').defaultPrevented, true);
    assert.equal(h.document.activeElement, h.confirm);
    assert.equal(h.normalForm.requestCount, 0);
});

test('Closing restores focus to visible navigation when the original trigger becomes hidden', () => {
    const h = harness();
    h.open();
    h.desktop.visible = false;
    h.cancel.click();
    h.advance(180);
    assert.equal(h.document.activeElement, h.navToggle);
    assert.equal(h.normalForm.requestCount, 0);
});

const accessibilityStart = layout.indexOf('                function isStudentAccessibilityLauncher(element)');
const accessibilityEnd = layout.indexOf('                function themeSiennaMenu()', accessibilityStart);
assert.ok(accessibilityStart >= 0 && accessibilityEnd > accessibilityStart);
const accessibilitySource = layout.slice(accessibilityStart, accessibilityEnd);

function accessibilityElement(id, textContent) {
    const attributes = new Map([['aria-hidden', 'true']]);
    return {
        id,
        textContent,
        classList: { contains: () => false },
        style: { removeProperty(name) { delete this[name]; } },
        getAttribute: name => attributes.get(name) ?? null,
        removeAttribute: name => attributes.delete(name),
    };
}

function showAccessibilityTrigger(candidates, nativeTrigger = null) {
    const document = {
        querySelector: selector => selector === '.asw-menu-btn' ? nativeTrigger : null,
        querySelectorAll: () => candidates,
    };
    const window = { getComputedStyle: () => ({ position: 'fixed' }) };
    vm.runInNewContext(accessibilitySource + '\nshowSiennaTrigger();', { document, window });
}

test('Accessibility fallback does not select or reposition the logout overlay', () => {
    const modal = accessibilityElement('studentLogoutConfirm',
        'Logout Are you sure you want to logout? You will need to sign in again to access your account. Logout Cancel');
    showAccessibilityTrigger([modal]);
    for (const property of ['position', 'left', 'right', 'top', 'bottom']) {
        assert.equal(modal.style[property], undefined, `The logout overlay must not receive accessibility ${property}.`);
    }
    assert.equal(modal.getAttribute('aria-hidden'), 'true');
});

test('Accessibility detection preserves native widget priority and its genuine floating fallback', () => {
    const modal = accessibilityElement('studentLogoutConfirm', 'You will need to sign in again to access your account.');
    const native = accessibilityElement('nativeWidget', 'Accessibility Tools');
    const fallback = accessibilityElement('fallbackWidget', 'Accessibility Tools');

    showAccessibilityTrigger([modal, fallback], native);
    assert.equal(native.style.right, '24px');
    assert.equal(native.style.bottom, '12px');
    assert.equal(fallback.style.right, undefined);
    assert.equal(modal.style.right, undefined);

    showAccessibilityTrigger([modal, fallback]);
    assert.equal(fallback.style.right, '24px');
    assert.equal(fallback.style.bottom, '12px');
    assert.equal(modal.style.right, undefined);
    assert.equal(modal.getAttribute('aria-hidden'), 'true');
});
