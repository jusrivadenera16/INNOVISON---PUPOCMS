const assert = require('node:assert/strict');
const fs = require('node:fs');
const path = require('node:path');
const test = require('node:test');
const vm = require('node:vm');

const source = fs.readFileSync(path.join(__dirname, '../../resources/views/landing.blade.php'), 'utf8');
const navigationStart = source.indexOf('function initializeLandingMobileNavigation()');
const navigationEnd = source.indexOf('initializeLandingMobileNavigation();', navigationStart);
const navigationSource = source.slice(navigationStart, navigationEnd) + 'initializeLandingMobileNavigation();';
const chestStart = source.indexOf('function initializeLandingChestpiece()');
const chestEnd = source.indexOf('function initializeLandingStickers()', chestStart);
const chestSource = source.slice(chestStart, chestEnd) + 'initializeLandingChestpiece();';
assert.ok(navigationStart >= 0 && navigationEnd > navigationStart && chestStart >= 0 && chestEnd > chestStart);

class Element {
    constructor(id, parent = null) {
        this.id = id;
        this.parent = parent;
        this.children = [];
        parent?.children.push(this);
        this.attributes = new Map();
        this.listeners = new Map();
        this.dataset = {};
        this.inert = false;
        this.style = { setProperty(name, value) { this[name] = value; } };
        const classes = new Set();
        this.classList = {
            add: name => classes.add(name),
            remove: name => classes.delete(name),
            contains: name => classes.has(name),
            toggle(name, force = !classes.has(name)) { force ? classes.add(name) : classes.delete(name); },
        };
    }
    addEventListener(type, callback, capture = false) {
        const listeners = this.listeners.get(type) ?? [];
        listeners.push({ callback, capture: capture === true });
        this.listeners.set(type, listeners);
    }
    emit(type, extra = {}) {
        const event = { target: this, defaultPrevented: false, preventDefault() { this.defaultPrevented = true; }, ...extra };
        const ancestors = [];
        for (let parent = this.parent; parent; parent = parent.parent) ancestors.push(parent);
        const call = (node, capture) => (node.listeners.get(type) ?? []).forEach(listener => {
            if (capture === null || listener.capture === capture) listener.callback(event);
        });
        [...ancestors].reverse().forEach(parent => call(parent, true));
        call(this, null);
        ancestors.forEach(parent => call(parent, false));
        return event;
    }
    setAttribute(name, value) { this.attributes.set(name, String(value)); }
    getAttribute(name) { return this.attributes.get(name) ?? null; }
    removeAttribute(name) { this.attributes.delete(name); }
    contains(target) {
        for (let node = target; node; node = node.parent) if (node === this) return true;
        return false;
    }
    closest(selector) {
        for (let node = this; node; node = node.parent) if (node.classList.contains(selector.slice(1))) return node;
        return null;
    }
    focus() {
        this.ownerDocument.activeElement = this;
        this.emit('focusin');
    }
}

function navigationHarness(mobile = true) {
    const document = new Element('document');
    const make = (id, parent = document) => {
        const element = new Element(id, parent);
        element.ownerDocument = document;
        return element;
    };
    const toggle = make('landingNavToggle');
    const menu = make('landingNavMenu');
    const outside = make('outside');
    document.activeElement = outside;
    const items = ['announcementBtn', 'landingFaqButton', 'bp-toggle-chat'].map(id => {
        const item = make(id, menu);
        item.classList.add('landing-nav-item');
        return item;
    });
    const viewport = new Element('viewport');
    viewport.matches = mobile;
    document.getElementById = id => ({ landingNavToggle: toggle, landingNavMenu: menu }[id] ?? null);
    menu.querySelectorAll = () => items;
    vm.runInNewContext(navigationSource, { document, window: { matchMedia: () => viewport } });
    return {
        document, toggle, menu, items, outside,
        resize(matches) { viewport.matches = matches; viewport.emit('change'); },
    };
}

test('mobile menu starts closed and toggles its expanded state', () => {
    const h = navigationHarness();
    assert.equal(h.menu.inert, true);
    assert.equal(h.menu.getAttribute('aria-hidden'), 'true');
    h.toggle.emit('click');
    assert.equal(h.toggle.getAttribute('aria-expanded'), 'true');
    assert.equal(h.toggle.getAttribute('aria-label'), 'Close navigation');
    assert.equal(h.menu.classList.contains('is-open'), true);
    assert.equal(h.menu.inert, false);
    h.toggle.emit('click');
    assert.equal(h.toggle.getAttribute('aria-expanded'), 'false');
    assert.equal(h.menu.inert, true);
});

test('all three remaining actions still run when the dropdown closes', () => {
    const h = navigationHarness();
    for (const item of h.items) {
        let actions = 0;
        item.addEventListener('click', () => actions++);
        h.toggle.emit('click');
        item.focus();
        item.emit('click');
        assert.equal(actions, 1);
        assert.equal(h.toggle.getAttribute('aria-expanded'), 'false');
        assert.equal(h.document.activeElement, h.toggle);
    }
});

test('outside clicks and outside focus close the menu, internal clicks do not', () => {
    const h = navigationHarness();
    h.toggle.emit('click');
    h.menu.emit('pointerdown');
    assert.equal(h.menu.inert, false);
    h.outside.emit('pointerdown');
    assert.equal(h.menu.inert, true);
    h.toggle.emit('click');
    h.outside.focus();
    assert.equal(h.menu.inert, true);
});

test('keyboard opening, arrow navigation and Escape preserve focus', () => {
    const h = navigationHarness();
    const last = h.items[h.items.length - 1];
    h.toggle.emit('keydown', { key: 'ArrowUp' });
    assert.equal(h.document.activeElement, last);
    last.emit('keydown', { key: 'ArrowDown' });
    assert.equal(h.document.activeElement, h.items[0]);
    h.items[0].emit('keydown', { key: 'End' });
    assert.equal(h.document.activeElement, last);
    last.emit('keydown', { key: 'Home' });
    assert.equal(h.document.activeElement, h.items[0]);
    const event = h.items[0].emit('keydown', { key: 'Escape' });
    assert.equal(event.defaultPrevented, true);
    assert.equal(h.menu.inert, true);
    assert.equal(h.document.activeElement, h.toggle);
});

test('desktop navigation stays accessible across viewport changes', () => {
    const h = navigationHarness();
    h.toggle.focus();
    h.toggle.emit('click');
    h.resize(false);
    assert.equal(h.menu.inert, false);
    assert.equal(h.menu.getAttribute('aria-hidden'), null);
    assert.equal(h.menu.classList.contains('is-open'), false);
    assert.equal(h.document.activeElement, h.items[0]);
    h.resize(true);
    assert.equal(h.menu.inert, true);
    assert.equal(h.document.activeElement, h.toggle);

    const desktop = navigationHarness(false);
    desktop.toggle.emit('click');
    assert.equal(desktop.menu.inert, false);
    assert.equal(desktop.toggle.getAttribute('aria-expanded'), 'false');
});

function chestHarness(width) {
    const compact = width <= 900;
    const imageWidth = width <= 640 ? 465 : compact ? 523 : 620;
    const cropWidth = width <= 640 ? 140 : compact ? 158 : 187;
    const imageHeight = imageWidth * 949 / 1658;
    const stage = new Element('stage');
    stage.classList.add('landing-announcement-stethoscope');
    let faqHeight = 264;
    const stageTop = 500 - 420;
    stage.getBoundingClientRect = () => ({ left: 0, top: stageTop, width, height: faqHeight + 420 });
    const chest = new Element('chest', stage);
    const image = new Element('image', chest);
    const canvas = new Element('canvas', stage);
    const captured = new Set();
    chest.setPointerCapture = id => captured.add(id);
    chest.hasPointerCapture = id => captured.has(id);
    chest.releasePointerCapture = id => captured.delete(id);
    const offset = name => parseFloat(chest.style[name] ?? '0');
    chest.getBoundingClientRect = () => {
        const left = width / 2 + (width <= 640 ? 80 : compact ? 225 : 320) - cropWidth / 2 + offset('--steth-chest-drag-x');
        const top = 500 + faqHeight - (width <= 640 ? 18 : compact ? 22 : 36) - imageHeight + offset('--steth-chest-drag-y');
        return { left, right: left + cropWidth, top, width: cropWidth, height: imageHeight };
    };
    image.getBoundingClientRect = () => ({ ...chest.getBoundingClientRect(), width: imageWidth, height: imageHeight });
    chest.querySelector = () => image;
    const context = {
        strokes: [],
        setTransform(...args) { this.transform = args; },
        clearRect(...args) { this.clear = args; this.strokes = []; },
        beginPath() { this.currentPath = {}; },
        moveTo(x, y) { this.currentPath.start = [x, y]; },
        bezierCurveTo(...args) { this.currentPath.curve = args; },
        arc(...args) { this.currentPath.arc = args; },
        stroke() { this.strokes.push({ ...this.currentPath, width: this.lineWidth }); },
        fill() {},
    };
    canvas.getContext = () => context;
    const window = new Element('window');
    window.matchMedia = () => ({ matches: compact });
    window.devicePixelRatio = 2;
    let nextFrame = 0;
    const frames = new Map();
    window.requestAnimationFrame = callback => { frames.set(++nextFrame, callback); return nextFrame; };
    window.cancelAnimationFrame = id => frames.delete(id);
    const document = {
        querySelector(selector) {
            if (selector === '.landing-stethoscope-chestpiece') return chest;
            if (selector === '.landing-stethoscope-hose-canvas') return canvas;
            throw new Error('Chestpiece code touched an unrelated element: ' + selector);
        },
    };
    vm.runInNewContext(chestSource, { document, window, performance: { now: () => 0 } });
    return {
        stage, chest, image, canvas, context, window, compact,
        resize(height) { faqHeight = height; window.emit('resize'); },
        settle() {
            const pending = [...frames.values()];
            frames.clear();
            pending.forEach(callback => callback(1000));
        },
    };
}

for (const width of [360, 768, 1366]) {
    test(`hose stays attached without top clipping or scroll space below the footer at ${width}px`, () => {
        const h = chestHarness(width);
        assert.equal(h.canvas.style.top, '0px');
        assert.equal(h.canvas.height, (264 + 420) * 2);
        assert.equal(h.stage.getBoundingClientRect().top + parseFloat(h.canvas.style.height), 500 + 264);
        h.chest.emit('pointerdown', { pointerId: 1, button: 0, clientX: 0, clientY: 0 });
        for (const deltaY of [-500, 500]) {
            h.chest.emit('pointermove', { pointerId: 1, clientX: 500, clientY: deltaY });
            const hose = h.context.strokes[0];
            const image = h.image.getBoundingClientRect();
            const chest = h.chest.getBoundingClientRect();
            const expectedY = image.top - h.stage.getBoundingClientRect().top + 674 * image.width / 1658;
            assert.ok(Math.abs(hose.start[0] - (chest.right - 1)) < 0.001);
            assert.ok(Math.abs(hose.start[1] - expectedY) < 0.001);
            assert.equal(hose.curve[1], hose.start[1]);
            assert.ok(hose.start[1] - hose.width / 2 >= 0);
            assert.equal(h.context.clear[1], 0);
            assert.equal(h.context.clear[3], 264 + 420);
            assert.equal(h.canvas.style.height, `${264 + 420}px`);
        }
        h.chest.emit('pointerup', { pointerId: 1 });
        h.settle();
        assert.equal(parseFloat(h.chest.style['--steth-chest-drag-y']), 0);
        assert.equal(h.chest.classList.contains('is-dragging'), false);
        h.resize(180);
        assert.equal(h.canvas.height, (180 + 420) * 2);
        h.image.emit('load');
        assert.ok(h.context.strokes[0].start.every(Number.isFinite));
    });
}
