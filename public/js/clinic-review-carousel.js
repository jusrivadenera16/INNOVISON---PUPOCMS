(() => {
    function initReviewCarousels() {
        document.querySelectorAll('[data-review-carousel]').forEach((root) => {
            if (root.dataset.reviewReady) return;
            root.dataset.reviewReady = 'true';
            const pages = Array.from(root.querySelectorAll('[data-review-page]'));
            const dots = Array.from(root.querySelectorAll('[data-review-dot]'));
            const status = root.querySelector('[data-review-status]');
            const reducedMotion = window.matchMedia('(prefers-reduced-motion: reduce)');
            let current = 0;
            let gesture = null;
            let animation = null;

            function showPage(index, focusDot = false) {
                if (index < 0 || index >= pages.length || index === current) return;
                const direction = index > current ? 1 : -1;
                animation?.cancel();
                current = index;
                pages.forEach((page, position) => { page.hidden = position !== current; });
                dots.forEach((dot, position) => {
                    dot.classList.toggle('is-active', position === current);
                    dot.setAttribute('aria-pressed', String(position === current));
                });
                if (status) status.textContent = `Page ${current + 1} of ${pages.length}`;
                if (!reducedMotion.matches && typeof pages[current].animate === 'function') {
                    animation = pages[current].animate([
                        { opacity: 0, transform: `translateX(${direction * 22}px)` },
                        { opacity: 1, transform: 'translateX(0)' }
                    ], { duration: 380, easing: 'cubic-bezier(.22,1,.36,1)' });
                }
                if (focusDot) dots[current]?.focus();
            }

            dots.forEach((dot, index) => {
                dot.addEventListener('click', () => showPage(index));
                dot.addEventListener('keydown', (event) => {
                    let next;
                    if (event.key === 'ArrowRight') next = (current + 1) % pages.length;
                    else if (event.key === 'ArrowLeft') next = (current - 1 + pages.length) % pages.length;
                    else if (event.key === 'Home') next = 0;
                    else if (event.key === 'End') next = pages.length - 1;
                    else return;
                    event.preventDefault();
                    showPage(next, true);
                });
            });
            const stage = root.querySelector('.feedback-stage');
            stage?.addEventListener('pointerdown', (event) => {
                if (event.pointerType !== 'touch' || !event.isPrimary) return;
                gesture = { x: event.clientX, y: event.clientY, id: event.pointerId };
            });
            stage?.addEventListener('pointerup', (event) => {
                if (!gesture || gesture.id !== event.pointerId) return;
                const dx = event.clientX - gesture.x;
                const dy = event.clientY - gesture.y;
                gesture = null;
                if (Math.abs(dx) > 50 && Math.abs(dx) > Math.abs(dy) * 1.5) {
                    showPage(current + (dx < 0 ? 1 : -1));
                }
            });
            stage?.addEventListener('pointercancel', () => { gesture = null; });
        });
    }
    if (document.readyState === 'loading') document.addEventListener('DOMContentLoaded', initReviewCarousels);
    else initReviewCarousels();
})();
