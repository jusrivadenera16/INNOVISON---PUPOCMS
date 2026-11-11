(() => {
    'use strict';
    const root = document.querySelector('[data-approved-clearance]');
    if (!root) return;
    const node = key => root.querySelector(`[data-approved-clearance-${key}]`);
    const bubble = node('bubble'), trigger = node('open');
    let record = null, options = null, token = null;
    const categoryDropdown = window.CategoryBubbleSelect?.create({select: node('category'), trigger: node('category-trigger'), menu: node('category-menu'), onToggle: position});
    function message(text) { node('bubble-status').textContent = text; node('bubble-status').hidden = !text; }
    function position() {
        if (bubble.hidden) return;
        const rect = trigger.getBoundingClientRect(), below = rect.top < bubble.offsetHeight + 22;
        bubble.toggleAttribute('data-below', below);
        bubble.style.left = `${Math.max(12, Math.min(rect.left, window.innerWidth - bubble.offsetWidth - 12))}px`;
        bubble.style.top = `${Math.max(12, Math.min(below ? rect.bottom + 12 : rect.top - bubble.offsetHeight - 12, window.innerHeight - bubble.offsetHeight - 12))}px`;
    }
    function close() {
        token = null; categoryDropdown?.close();
        if (!bubble.hidden) bubble.hidePopover?.();
        bubble.hidden = true; trigger.setAttribute('aria-expanded', 'false');
    }
    window.ApprovedClearanceReview = {configure(type, id) {
        close(); options = null;
        record = ['student', 'employee'].includes(type) && Number(id) > 0
            ? {type, id: Number(id), url: `${root.dataset.base}/${type}/${Number(id)}`} : null;
        root.hidden = !record;
    }};
    trigger.addEventListener('click', async () => {
        if (!record) return;
        if (!bubble.hidden) { close(); return; }
        const loading = {}; token = loading; const target = record;
        node('category').replaceChildren(); node('category').disabled = true;
        node('continue').disabled = true; categoryDropdown?.refresh(); message('Loading Health Form categories...');
        bubble.hidden = false; bubble.showPopover?.(); trigger.setAttribute('aria-expanded', 'true'); position();
        try {
            const response = await fetch(target.url, {credentials:'same-origin', headers:{Accept:'application/json'}});
            const data = await response.json();
            if (token !== loading) return;
            if (!response.ok) throw new Error(data.message || 'Unable to load Health Form categories.');
            options = data;
            for (const value of ['', ...data.health_form_categories]) {
                const option = document.createElement('option'); option.value = value; option.textContent = value || 'Select a category'; node('category').append(option);
            }
            node('category').disabled = !data.can_issue || !data.health_form_categories.length;
            node('continue').disabled = node('category').disabled; categoryDropdown?.refresh();
            if (!data.can_issue) throw new Error('No eligible approved Health Form is available. Check for a pending form or withdrawn record.');
            message(data.health_form_categories.length ? '' : 'No active categories are available for this patient type.');
            position(); categoryDropdown ? categoryDropdown.focus() : node('category').focus();
        } catch (error) { if (token === loading) { message(error.message); position(); } }
    });
    node('continue').addEventListener('click', event => {
        event.preventDefault();
        if (!record || !options?.can_issue) return;
        const category = node('category').value;
        if (!options.health_form_categories.includes(category)) { message('Select a Health Form category first.'); return; }
        const context = {...record, options, category};
        if (!window.ClinicExistingFinalReview?.open(context)) {
            message('Unable to open the existing Final Review. Look up this record again.'); position(); return;
        }
        close();
    });
    node('close').addEventListener('click', close);
    document.addEventListener('click', event => { if (!bubble.hidden && !bubble.contains(event.target) && !trigger.contains(event.target)) close(); });
    document.addEventListener('keydown', event => { if (event.key === 'Escape' && !bubble.hidden) { event.preventDefault(); event.stopImmediatePropagation(); close(); trigger.focus(); } }, true);
    window.addEventListener('resize', position); window.addEventListener('scroll', position, true);
})();
