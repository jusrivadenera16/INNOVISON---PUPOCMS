const { test } = require('node:test');
const assert = require('node:assert/strict');
const fs = require('node:fs');
const path = require('node:path');
const vm = require('node:vm');

const source = fs.readFileSync(path.join(__dirname, '../../resources/views/admin/partials/record-consult-modal.blade.php'), 'utf8');
const script = source.match(/<script>([\s\S]*?)<\/script>/)[1];

function setup() {
    const element = () => ({
        listeners: {}, attrs: {}, classes: new Set(['is-open']), focused: false,
        addEventListener(name, listener) { this.listeners[name] = listener; },
        setAttribute(name, value) { this.attrs[name] = value; },
        focus() { this.focused = true; },
        classList: { remove(name) { this.owner.classes.delete(name); } },
    });
    const trigger = element();
    const select = element();
    const picker = element();
    const form = element();
    const radio = element();
    radio.value = 'Clinic Document Services';
    const selected = element();
    const cancel = element();
    const close = element();
    const dialog = element();
    dialog.open = false;
    dialog.showModal = () => { dialog.open = true; };
    dialog.close = () => { dialog.open = false; dialog.listeners.close(); };
    dialog.querySelector = selector => selector === 'form' ? form : selector === 'details' ? picker : selector === '#recordConsultSelected' ? selected : select;
    dialog.querySelectorAll = selector => selector === 'input[name="service"]' ? [radio] : [close, cancel];
    dialog.getBoundingClientRect = () => ({ left: 10, top: 10, right: 200, bottom: 200 });
    const nodes = Object.fromEntries(['profileActionsMenu', 'profileActionsToggle', 'employeeProfileActions',
        'employeeProfileActionsToggle', 'employeeProfileActionsMenu'].map(id => [id, element()]));
    for (const node of Object.values(nodes)) node.classList.owner = node;
    nodes.recordConsultDialog = dialog;
    vm.runInNewContext(script, { document: {
        getElementById: id => nodes[id], querySelector: () => trigger,
    } });
    return { dialog, trigger, select, cancel, close, nodes, picker, radio, selected, form };
}

test('Consult closes the action menu and focuses the required service picker', () => {
    const { trigger, dialog, select, nodes } = setup();
    trigger.listeners.click();
    assert.equal(dialog.open, true);
    assert.equal(select.focused, true);
    assert.equal(nodes.profileActionsMenu.classes.has('is-open'), false);
    assert.equal(nodes.profileActionsToggle.attrs['aria-expanded'], 'false');
    assert.equal(nodes.employeeProfileActionsMenu.attrs['aria-hidden'], 'true');
});

test('Cancel and close dismiss without navigation and return focus to Consult', () => {
    for (const action of ['cancel', 'close']) {
        const context = setup();
        context.trigger.listeners.click();
        context[action].listeners.click();
        assert.equal(context.dialog.open, false);
        assert.equal(context.trigger.focused, true);
    }
});

test('Backdrop dismisses, but clicks inside the modal do not', () => {
    const { trigger, dialog } = setup();
    trigger.listeners.click();
    dialog.listeners.click({ target: dialog, clientX: 50, clientY: 50 });
    assert.equal(dialog.open, true);
    dialog.listeners.click({ target: dialog, clientX: 250, clientY: 250 });
    assert.equal(dialog.open, false);
});

test('Service selection updates the styled picker and invalid empty choices reopen it', () => {
    const { picker, radio, selected, select } = setup();
    radio.listeners.invalid({ preventDefault() {} });
    assert.equal(picker.open, true);
    radio.listeners.change();
    assert.equal(picker.open, false);
    assert.equal(selected.textContent, 'Clinic Document Services');
    assert.equal(select.focused, true);
});

test('Valid Continue closes the top-layer dialog before navigation without changing the selected service', () => {
    const { trigger, dialog, form, radio } = setup();
    trigger.listeners.click();
    radio.listeners.change();
    form.listeners.submit();
    assert.equal(dialog.open, false);
    assert.equal(radio.value, 'Clinic Document Services');
});

test('Invalid service selection keeps the dialog open for correction', () => {
    const { trigger, dialog, radio, picker } = setup();
    trigger.listeners.click();
    radio.listeners.invalid({ preventDefault() {} });
    assert.equal(dialog.open, true);
    assert.equal(picker.open, true);
});
