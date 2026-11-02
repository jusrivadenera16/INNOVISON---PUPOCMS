const assert = require('node:assert/strict');
const fs = require('node:fs');
const path = require('node:path');
const test = require('node:test');
const vm = require('node:vm');

const appointments = fs.readFileSync(path.join(__dirname, '../../resources/views/admin/appointments.blade.php'), 'utf8');
const layout = fs.readFileSync(path.join(__dirname, '../../resources/views/layouts/admin.blade.php'), 'utf8');
function section(source, startMarker, endMarker) {
    const start = source.indexOf(startMarker);
    const end = source.indexOf(endMarker, start + startMarker.length);
    assert.ok(start >= 0 && end > start, `Actual application source must contain ${startMarker}.`);
    return source.slice(start, end);
}
const safeTextSource = section(appointments, '    function safeText(value)', '    function initializeAppointmentReasonDropdown(');
const helpersSource = section(appointments, '    function getNameInitials(value)', '    function openInfoModal(');
const infoSource = section(appointments, '    function openInfoModal(', '    function closeInfoModal(');
const statusSource = section(appointments, '    function openStatusActionModal(', '    function closeStatusActionModal(');
const resolverSource = section(layout, '        const resolveActionCopy = function', '        const resolveSkeletonKind = function');

function harness() {
    const nodes = new Map();
    let document;
    class Element extends EventTarget {
        constructor(id = '') {
            super();
            this.id = id;
            this.dataset = {};
            this.style = {};
            this.attributes = new Map();
            this.value = '';
            this.hidden = false;
            this.children = [];
            this.textContent = '';
            const classes = new Set();
            this.classList = {
                add: name => classes.add(name),
                remove: name => classes.delete(name),
                toggle(name, active) { if (active) classes.add(name); else classes.delete(name); },
            };
        }
        set innerText(value) { this.textContent = value; }
        get innerText() { return this.textContent; }
        set innerHTML(value) { this.textContent = value.replace(/<[^>]*>/g, ''); }
        getAttribute(name) { return this.attributes.get(name) ?? this[name] ?? null; }
        setAttribute(name, value) { this.attributes.set(name, String(value)); }
        closest() { return null; }
        querySelector() { return null; }
        replaceChildren(...children) { this.children = children; }
        appendChild(child) { this.children.push(child); }
        append(...children) { this.children.push(...children); }
        focus() { document.activeElement = this; }
    }
    class HTMLAnchorElement extends Element {}
    class HTMLFormElement extends Element {}
    const anchorIds = new Set(['statusActionConfirm', 'mApproveAction', 'mRejectAction', 'mConsultAction']);
    document = {
        getElementById(id) {
            if (!nodes.has(id)) nodes.set(id, anchorIds.has(id) ? new HTMLAnchorElement(id) : new Element(id));
            return nodes.get(id);
        },
        createElement: () => new Element(),
    };
    const context = vm.createContext({
        document,
        HTMLAnchorElement,
        HTMLFormElement,
        URL,
        URLSearchParams,
        appointmentsBaseUrl: 'http://localhost/admin/appointments',
        window: { location: { href: 'http://localhost/admin/appointments' } },
    });
    vm.runInContext([safeTextSource, helpersSource, infoSource, statusSource, resolverSource].join('\n')
        + '\nglobalThis.copyFor = resolveActionCopy;', context);

    function status(target) {
        const trigger = new HTMLAnchorElement();
        trigger.href = 'http://localhost/admin/appointments/42/' + target;
        trigger.dataset = { id: '42', statusTarget: target, name: 'Patient', service: 'General Consultation' };
        context.openStatusActionModal(trigger);
        return document.getElementById('statusActionConfirm');
    }
    function info(status, remarks = 'Saved clinic remarks') {
        const trigger = new Element();
        trigger.dataset = { appointmentId: '42', status, name: 'Patient', service: 'General Consultation', remarks };
        context.openInfoModal(trigger);
    }
    return { context, document, status, info, HTMLFormElement, HTMLAnchorElement };
}

for (const [status, title] of [
    ['Approved', 'Approving Appointment'],
    ['Rejected', 'Rejecting Appointment'],
    ['Missed', 'Updating Missed Status'],
]) {
    test(`${status} confirmation resolves its action-specific loader copy`, () => {
        const h = harness();
        const confirm = h.status(status);
        const copy = h.context.copyFor(confirm);
        assert.equal(copy.title, title);
        assert.match(copy.detail, /Saving/);
        assert.doesNotMatch(copy.title, /Opening/);
        assert.equal(new URL(confirm.href).pathname, '/admin/appointments/42/' + status);
    });
}

test('Reusing the confirmation resets loader copy and button text for every action', () => {
    const h = harness();
    for (const [status, title, buttonText] of [
        ['Approved', 'Approving Appointment', 'Confirm Approval'],
        ['Rejected', 'Rejecting Appointment', 'Confirm Rejection'],
        ['Missed', 'Updating Missed Status', 'Confirm Missed Status'],
        ['Approved', 'Approving Appointment', 'Confirm Approval'],
    ]) {
        const confirm = h.status(status);
        assert.equal(h.context.copyFor(confirm).title, title);
        assert.equal(confirm.textContent, buttonText);
    }
});

test('Rejection validation still prevents navigation until its required reason is supplied', () => {
    const h = harness();
    const confirm = h.status('Rejected');
    const missingReason = new Event('click', { cancelable: true });
    assert.equal(confirm.onclick(missingReason), false);
    assert.equal(missingReason.defaultPrevented, true);
    assert.equal(h.document.getElementById('statusReasonError').hidden, false);

    h.document.getElementById('sApprovalReason').value = 'Duplicate appointment';
    const valid = new Event('click', { cancelable: true });
    assert.equal(confirm.onclick(valid), true);
    assert.equal(valid.defaultPrevented, false);
    assert.equal(new URL(confirm.href).searchParams.get('message'), 'Duplicate appointment');
    assert.equal(h.context.copyFor(confirm).title, 'Rejecting Appointment');
});

test('Reschedule form attributes resolve the correct submit loader through its submit button', () => {
    const h = harness();
    const markup = appointments.match(/<form\b[^>]*\bid="rescheduleForm"[^>]*>/)?.[0];
    assert.ok(markup);
    const form = new h.HTMLFormElement();
    for (const [attribute, property] of [['data-loader-message', 'loaderMessage'], ['data-loader-detail', 'loaderDetail']]) {
        form.dataset[property] = markup.match(new RegExp(attribute + '="([^"]+)"'))?.[1] || '';
    }
    const submitter = { textContent: 'Confirm New Schedule', form };
    const copy = h.context.copyFor(submitter, 'submit');
    assert.equal(copy.title, 'Rescheduling Appointment');
    assert.equal(copy.detail, 'Saving the new appointment date, time, and rescheduling reason.');
});

test('Pending, approved and scheduled appointments hide generic consultation Remarks', () => {
    const h = harness();
    for (const status of ['Pending', 'Approved', 'Scheduled']) {
        h.info(status);
        assert.equal(h.document.getElementById('appointmentRemarksBlock').hidden, true, status);
        assert.equal(h.document.getElementById('mRemarksLabel').innerText, 'Remarks');
    }
});

test('Completed appointments show Remarks and reusing the modal for approved hides them again', () => {
    const h = harness();
    h.info('Completed', 'Completed consultation remarks');
    assert.equal(h.document.getElementById('appointmentRemarksBlock').hidden, false);
    assert.equal(h.document.getElementById('mRemarksLabel').innerText, 'Remarks');
    assert.equal(h.document.getElementById('mRemarks').innerText, 'Completed consultation remarks');

    h.info('Approved');
    assert.equal(h.document.getElementById('appointmentRemarksBlock').hidden, true);
});

test('Rejection, cancellation, missed and rescheduled outcomes retain their specifically labeled reasons', () => {
    const h = harness();
    for (const [status, label] of [
        ['Rejected', 'Reason for Rejection'],
        ['Cancelled', 'Cancellation Details'],
        ['Canceled', 'Cancellation Details'],
        ['Missed', 'Missed Appointment Reason'],
        ['Rescheduled', 'Rescheduling Reason'],
    ]) {
        h.info(status, 'Saved outcome reason');
        assert.equal(h.document.getElementById('appointmentRemarksBlock').hidden, false, status);
        assert.equal(h.document.getElementById('mRemarksLabel').innerText, label);
        assert.equal(h.document.getElementById('mRemarks').innerText, 'Saved outcome reason');
    }
});

test('Health-profile links open Record while Health Records and inventory navigation keep their own titles', () => {
    const h = harness();
    for (const route of [
        '/health-profile/42?record_type=applicant',
        '/health-profile/42?record_type=transferee&source=walkin',
        '/employee-health-profile/24',
    ]) {
        const link = new h.HTMLAnchorElement();
        link.href = 'http://localhost' + route;
        link.textContent = 'View';
        assert.equal(h.context.copyFor(link).title, 'Opening Record', route);
    }
    for (const [route, label, title] of [
        ['/health-records', 'Health Records', 'Opening Health Records'],
        ['/admin/inventory', 'Inventory', 'Opening Clinic Inventory'],
    ]) {
        const link = new h.HTMLAnchorElement();
        link.href = 'http://localhost' + route;
        link.textContent = label;
        assert.equal(h.context.copyFor(link).title, title);
    }
});
