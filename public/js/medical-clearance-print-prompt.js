(() => {
    'use strict';
    const root = document.getElementById('medicalClearancePrintPrompt');
    if (!root) return;
    const later = root.querySelector('[data-clearance-print-later]');
    const view = root.querySelector('[data-clearance-print-view]');
    let active = null;
    function choose(print) {
        if (!active) return;
        const choice = active;
        active = null;
        if (print) window.open(choice.url, '_blank', 'noopener');
        root.classList.remove('is-open'); root.setAttribute('aria-hidden', 'true');
        if (choice.previous?.isConnected) choice.previous.focus();
        choice.onDone?.();
    }
    window.MedicalClearancePrintPrompt = {show(url, onDone) {
        if (active || typeof url !== 'string' || !url.trim()) return false;
        let target;
        try { target = new URL(url, window.location.href); } catch { return false; }
        if (target.origin !== window.location.origin || !['http:', 'https:'].includes(target.protocol)) return false;
        active = {url: target.href, onDone, previous: document.activeElement};
        root.classList.add('is-open'); root.setAttribute('aria-hidden', 'false');
        later.focus();
        return true;
    }};
    later.addEventListener('click', () => choose(false));
    view.addEventListener('click', () => choose(true));
    document.addEventListener('keydown', event => {
        if (!active) return;
        if (event.key === 'Escape') { event.preventDefault(); event.stopImmediatePropagation(); choose(false); }
        else if (event.key === 'Tab' && ((event.shiftKey && document.activeElement === later) || (!event.shiftKey && document.activeElement === view))) {
            event.preventDefault(); (event.shiftKey ? view : later).focus();
        }
    }, true);
})();
