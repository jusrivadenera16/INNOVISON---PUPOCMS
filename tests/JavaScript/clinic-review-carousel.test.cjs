const { test } = require('node:test');
const assert = require('node:assert/strict');
const fs = require('node:fs');
const path = require('node:path');
const vm = require('node:vm');
const source = fs.readFileSync(path.join(__dirname, '../../public/js/clinic-review-carousel.js'), 'utf8');

function element() {
    const handlers = {};
    return {
        handlers, attributes: {}, classes: new Set(), dataset: {}, hidden: false,
        classList: { toggle(name, value) { value ? this.owner.classes.add(name) : this.owner.classes.delete(name); } },
        addEventListener(name, handler) { handlers[name] = handler; },
        setAttribute(name, value) { this.attributes[name] = value; },
        focus() { this.focused = true; },
        emit(name, event = {}) { handlers[name]?.(event); },
    };
}

function harness({ count = 3, reduced = false } = {}) {
    const animations = [];
    const pages = Array.from({ length: count }, (_, index) => ({ hidden: index > 0, animate() {
        const animation = { cancel() { this.cancelled = true; } };
        animations.push(animation);
        return animation;
    } }));
    const dots = Array.from({ length: 3 }, () => element());
    dots.forEach(dot => { dot.classList.owner = dot; });
    const stage = element();
    const status = {};
    const root = element();
    root.querySelectorAll = selector => selector === '[data-review-page]' ? pages : dots;
    root.querySelector = selector => selector === '.feedback-stage' ? stage : status;
    let ready;
    const context = {
        window: { matchMedia: () => ({ matches: reduced }) },
        document: { readyState: 'loading', addEventListener: (event, fn) => { ready = fn; }, querySelectorAll: () => [root] },
    };
    vm.runInNewContext(source, context);
    ready();
    return { pages, dots, stage, status, animations, ready };
}

test('dots show exactly one page, update state, and cancel the previous transition', () => {
    const h = harness();
    h.dots[1].emit('click');
    assert.deepEqual(h.pages.map(page => page.hidden), [true, false, true]);
    assert.equal(h.status.textContent, 'Page 2 of 3');
    assert.equal(h.dots[1].attributes['aria-pressed'], 'true');
    h.dots[2].emit('click');
    assert.deepEqual(h.pages.map(page => page.hidden), [true, true, false]);
    assert.equal(h.animations[0].cancelled, true);
});

test('keyboard navigation wraps and respects reduced motion', () => {
    const h = harness({ reduced: true });
    h.dots[0].emit('keydown', { key: 'ArrowLeft', preventDefault() {} });
    assert.equal(h.pages[2].hidden, false);
    assert.equal(h.dots[2].focused, true);
    h.dots[2].emit('keydown', { key: 'Home', preventDefault() {} });
    assert.equal(h.pages[0].hidden, false);
    assert.equal(h.animations.length, 0);
});

test('horizontal touch swipe changes pages but vertical gestures and missing pages do not', () => {
    const h = harness({ count: 2 });
    const start = { pointerType: 'touch', isPrimary: true, pointerId: 1, clientX: 200, clientY: 100 };
    h.stage.emit('pointerdown', start);
    h.stage.emit('pointerup', { pointerId: 1, clientX: 120, clientY: 105 });
    assert.equal(h.pages[1].hidden, false);
    h.dots[2].emit('click');
    assert.equal(h.pages[1].hidden, false);
    h.stage.emit('pointerdown', start);
    h.stage.emit('pointerup', { pointerId: 1, clientX: 280, clientY: 250 });
    assert.equal(h.pages[1].hidden, false);
});
