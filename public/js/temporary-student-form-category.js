(() => {
    'use strict';
    const bubble = document.getElementById('temporaryStudentCategoryBubble');
    const wrap = document.querySelector('[data-temp-category-menu-wrap]');
    if (!bubble || !wrap) return;
    const trigger = wrap.querySelector('[data-temp-category-open]');
    const node = key => bubble.querySelector(`[data-temp-category-${key}]`);
    const select = node('select'), apply = node('apply');
    let url = '', current = null, busy = false, token = null;
    const categoryDropdown = window.CategoryBubbleSelect?.create({ select, trigger: node('category-trigger'), menu: node('category-menu'), onToggle: position });
    const message = text => { node('status').textContent = text; node('status').hidden = !text; };
    function position() {
        if (bubble.hidden) return;
        const rect = trigger.getBoundingClientRect();
        const below = rect.top < bubble.offsetHeight + 22;
        bubble.toggleAttribute('data-below', below);
        bubble.style.left = `${Math.max(12, Math.min(rect.left, window.innerWidth - bubble.offsetWidth - 12))}px`;
        bubble.style.top = `${Math.max(12, Math.min(below ? rect.bottom + 12 : rect.top - bubble.offsetHeight - 12, window.innerHeight - bubble.offsetHeight - 12))}px`;
    }
    function close() {
        if (busy) return;
        token = null;
        categoryDropdown?.close();
        if (!bubble.hidden && bubble.hidePopover) bubble.hidePopover();
        bubble.hidden = true; trigger.setAttribute('aria-expanded', 'false');
    }
    async function json(target, options = {}) {
        const response = await fetch(target, { credentials: 'same-origin', ...options, headers: { Accept: 'application/json', ...options.headers } });
        const data = await response.json();
        if (!response.ok) throw new Error(Object.values(data.errors || {}).flat()[0] || data.message || 'Unable to change the category.');
        return data;
    }
    window.TemporaryStudentFormCategory = { configure(nextUrl) { close(); url = nextUrl || ''; wrap.hidden = !url; } };
    trigger.addEventListener('click', async () => {
        if (!url || busy) return;
        if (!bubble.hidden) { close(); return; }
        const loading = {}; token = loading; current = null;
        const target = url;
        select.disabled = true; apply.disabled = true; select.replaceChildren(); node('current').textContent = '';
        categoryDropdown?.refresh();
        message('Loading categories...');
        bubble.hidden = false; bubble.showPopover?.(); trigger.setAttribute('aria-expanded', 'true'); position();
        try {
            const data = await json(target);
            if (token !== loading) return;
            current = { ...data, url: target };
            node('current').textContent = `Current: ${data.category || 'Not specified'}`;
            for (const value of data.categories) {
                const option = document.createElement('option'); option.value = value; option.textContent = value; select.append(option);
            }
            select.value = data.category; select.disabled = false; apply.disabled = false; categoryDropdown?.refresh(); message(''); position();
            if (categoryDropdown) categoryDropdown.focus(); else select.focus();
        } catch (error) { if (token === loading) { message(error.message); position(); } }
    });
    node('close').addEventListener('click', close);
    document.addEventListener('click', event => { if (!bubble.hidden && !bubble.contains(event.target) && !trigger.contains(event.target)) close(); });
    document.addEventListener('keydown', event => { if (event.key === 'Escape' && !bubble.hidden) { event.preventDefault(); event.stopImmediatePropagation(); close(); trigger.focus(); } }, true);
    window.addEventListener('resize', position);
    window.addEventListener('scroll', position, true);
    node('form').addEventListener('submit', async event => {
        event.preventDefault();
        if (busy || !current || !node('form').reportValidity()) return;
        if (!select.value || select.value === current.category) { message('Choose a different student form category.'); return; }
        busy = true; apply.disabled = true; select.disabled = true; node('close').disabled = true;
        categoryDropdown?.refresh();
        message('Updating the category and PDFs...'); position();
        try {
            await json(current.url, { method: 'POST', headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content || '' },
                body: JSON.stringify({ submission_id: current.submission_id, expected_category: current.category, category: select.value }) });
            const destination = new URL(window.location.href); destination.searchParams.set('tab', 'pending_approval'); destination.searchParams.delete('form_category');
            window.location.assign(destination.toString());
        } catch (error) { message(error.message); busy = false; apply.disabled = false; select.disabled = false; node('close').disabled = false; categoryDropdown?.refresh(); position(); }
    });
})();
