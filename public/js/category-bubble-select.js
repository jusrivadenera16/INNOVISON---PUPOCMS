(() => {
    'use strict';
    window.CategoryBubbleSelect = { create({ select, trigger, menu, onToggle = () => {} }) {
        if (!select || !trigger || !menu) return null;
        select.hidden = true; trigger.hidden = false;
        const label = trigger.querySelector('span');
        const buttons = () => [...menu.children];
        function close(focus = false) {
            menu.hidden = true; trigger.setAttribute('aria-expanded', 'false'); onToggle();
            if (focus) trigger.focus();
        }
        function refresh() {
            close(); menu.replaceChildren();
            trigger.disabled = select.disabled;
            let selectedLabel = 'Select a category';
            for (const option of select.children) {
                if (option.value === select.value) selectedLabel = option.textContent;
                const button = document.createElement('button');
                button.type = 'button'; button.className = 'category-bubble-option';
                button.textContent = option.textContent; button.dataset.value = option.value; button.disabled = option.disabled;
                button.setAttribute('role', 'option'); button.setAttribute('aria-selected', String(option.value === select.value));
                button.addEventListener('click', () => {
                    select.value = option.value;
                    label.textContent = option.textContent;
                    for (const item of buttons()) item.setAttribute('aria-selected', String(item === button));
                    close(true);
                });
                menu.append(button);
            }
            label.textContent = selectedLabel;
        }
        function open(last = false) {
            if (trigger.disabled || !buttons().length) return;
            menu.hidden = false; trigger.setAttribute('aria-expanded', 'true'); onToggle();
            const available = buttons().filter(button => !button.disabled);
            (last ? available.at(-1) : available.find(button => button.dataset.value === select.value) || available[0])?.focus();
        }
        trigger.addEventListener('click', () => menu.hidden ? open() : close());
        trigger.addEventListener('keydown', event => {
            if (['ArrowDown', 'ArrowUp'].includes(event.key)) { event.preventDefault(); open(event.key === 'ArrowUp'); }
        });
        menu.addEventListener('keydown', event => {
            const available = buttons().filter(button => !button.disabled);
            const index = available.indexOf(event.target);
            if (['ArrowDown', 'ArrowUp', 'Home', 'End'].includes(event.key)) {
                event.preventDefault();
                const next = event.key === 'Home' ? 0 : event.key === 'End' ? available.length - 1 : (index + (event.key === 'ArrowDown' ? 1 : -1) + available.length) % available.length;
                available[next]?.focus();
            }
            if (event.key === 'Tab') close();
        });
        document.addEventListener('click', event => { if (!menu.hidden && !menu.contains(event.target) && !trigger.contains(event.target)) close(); });
        // Capture Escape before the containing bubble or status modal handles it.
        document.addEventListener('keydown', event => {
            if (event.key === 'Escape' && !menu.hidden) { event.preventDefault(); event.stopImmediatePropagation(); close(true); }
        }, true);
        refresh();
        return { refresh, close, focus: () => trigger.focus() };
    } };
})();
