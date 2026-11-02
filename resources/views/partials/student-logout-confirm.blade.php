<style>
        .student-logout-confirm {
            position: fixed;
            inset: 0;
            z-index: 1000002;
            display: grid;
            place-items: center;
            padding: 24px;
            background: rgba(15, 23, 42, 0.38);
            backdrop-filter: blur(3px);
            -webkit-backdrop-filter: blur(3px);
            opacity: 0;
            visibility: hidden;
            pointer-events: none;
            transition: opacity 0.2s ease, visibility 0.2s ease;
        }

        .student-logout-confirm.is-open {
            opacity: 1;
            visibility: visible;
            pointer-events: auto;
        }

        .student-logout-confirm.is-closing {
            opacity: 0;
            pointer-events: none;
        }

        body.student-logout-confirm-open {
            overflow: hidden;
        }

        @keyframes studentLogoutModalIn {
            from {
                opacity: 0;
                transform: translateY(16px) scale(0.94);
            }
            to {
                opacity: 1;
                transform: translateY(0) scale(1);
            }
        }

        @keyframes studentLogoutModalOut {
            from {
                opacity: 1;
                transform: translateY(0) scale(1);
            }
            to {
                opacity: 0;
                transform: translateY(14px) scale(0.96);
            }
        }

        .student-logout-confirm-dialog {
            width: min(100%, 320px);
            padding: 26px 24px 28px;
            border-radius: 8px;
            border: 1px solid rgba(112, 19, 27, 0.10);
            background: #ffffff;
            color: #111827;
            text-align: center;
            box-shadow: 0 24px 60px rgba(15, 23, 42, 0.22);
            transform: translateY(10px) scale(0.96);
            transition: transform 0.22s cubic-bezier(0.22, 1, 0.36, 1);
        }

        .student-logout-confirm.is-open .student-logout-confirm-dialog {
            transform: translateY(0) scale(1);
            animation: studentLogoutModalIn 0.24s cubic-bezier(0.22, 1, 0.36, 1) both;
        }

        .student-logout-confirm.is-closing .student-logout-confirm-dialog {
            animation: studentLogoutModalOut 0.18s ease both;
        }

        .student-logout-confirm-icon {
            width: 48px;
            height: 48px;
            display: inline-grid;
            place-items: center;
            margin-bottom: 16px;
            border-radius: 999px;
            background: #fee2e2;
            color: #ef4444;
        }

        .student-logout-confirm-icon svg {
            width: 24px;
            height: 24px;
            stroke-width: 1.8;
        }

        .student-logout-confirm-title {
            margin: 0;
            color: #111827;
            font-size: 16px;
            font-weight: 900;
            line-height: 1.25;
        }

        .student-logout-confirm-copy {
            margin: 12px 0 18px;
            color: #64748b;
            font-size: 14px;
            line-height: 1.35;
        }

        .student-logout-confirm-copy-line {
            display: block;
        }

        .student-logout-confirm-copy-line.is-supporting {
            margin-top: 3px;
            font-size: 12.5px;
            line-height: 1.3;
        }

        .student-logout-confirm-actions {
            display: grid;
            gap: 12px;
        }

        .student-logout-confirm-btn {
            position: relative;
            overflow: hidden;
            min-height: 42px;
            width: 100%;
            border-radius: 5px;
            border: 1px solid transparent;
            font-family: inherit;
            font-size: 15px;
            font-weight: 900;
            cursor: pointer;
            transition: background 0.2s ease, border-color 0.2s ease, color 0.2s ease, transform 0.18s ease, box-shadow 0.2s ease;
        }

        .student-logout-confirm-btn::before {
            content: "";
            position: absolute;
            inset: -45% auto -45% -68%;
            width: 58%;
            background: linear-gradient(115deg, transparent 0%, rgba(255, 247, 194, 0.12) 35%, rgba(255, 247, 194, 0.76) 50%, rgba(255, 247, 194, 0.14) 65%, transparent 100%);
            transform: skewX(-18deg);
            transition: left 0.5s ease;
            pointer-events: none;
        }

        .student-logout-confirm-btn span {
            position: relative;
            z-index: 1;
        }

        .student-logout-confirm-btn.is-primary {
            background: #8B0000;
            border-color: #8B0000;
            color: #ffffff;
            box-shadow: 0 12px 24px rgba(139, 0, 0, 0.18);
        }

        .student-logout-confirm-btn.is-primary:hover,
        .student-logout-confirm-btn.is-primary:focus-visible {
            background: #facc15;
            border-color: #facc15;
            color: #8B0000;
            transform: translateY(-1px);
            box-shadow: 0 14px 28px rgba(250, 204, 21, 0.22);
        }

        .student-logout-confirm-btn.is-primary:hover::before,
        .student-logout-confirm-btn.is-primary:focus-visible::before {
            left: 112%;
        }

        .student-logout-confirm-btn.is-secondary {
            background: #ffffff;
            border-color: #d1d5db;
            color: #374151;
        }

        .student-logout-confirm-btn.is-secondary:hover,
        .student-logout-confirm-btn.is-secondary:focus-visible {
            border-color: #facc15;
            background: #facc15;
            color: #8B0000;
            transform: translateY(-1px);
            box-shadow: 0 12px 24px rgba(250, 204, 21, 0.18);
        }

        .student-logout-confirm-btn:focus-visible {
            outline: 2px solid rgba(250, 204, 21, 0.4);
            outline-offset: 2px;
        }

        html[data-theme="dark"] .student-logout-confirm {
            background: rgba(0, 0, 0, 0.56);
        }

        html[data-theme="dark"] .student-logout-confirm-dialog {
            border-color: rgba(250, 204, 21, 0.16);
            background: #111827;
            color: #f8fafc;
            box-shadow: 0 24px 60px rgba(0, 0, 0, 0.42);
        }

        html[data-theme="dark"] .student-logout-confirm-title {
            color: #f8fafc;
        }

        html[data-theme="dark"] .student-logout-confirm-copy {
            color: #cbd5e1;
        }

        html[data-theme="dark"] .student-logout-confirm-btn.is-secondary {
            border-color: rgba(255, 255, 255, 0.18);
            background: rgba(255, 255, 255, 0.06);
            color: #f8fafc;
        }

        html[data-theme="dark"] .student-logout-confirm-btn.is-secondary:hover,
        html[data-theme="dark"] .student-logout-confirm-btn.is-secondary:focus-visible {
            border-color: #facc15;
            background: #facc15;
            color: #8B0000;
        }
    .student-logout-confirm-dialog {
        box-sizing: border-box;
        max-height: calc(100dvh - 48px);
        overflow-y: auto;
    }

    .student-logout-confirm-btn:disabled {
        cursor: wait;
        opacity: .75;
    }

    @media (prefers-reduced-motion: reduce) {
        .student-logout-confirm,
        .student-logout-confirm-dialog,
        .student-logout-confirm-btn,
        .student-logout-confirm-btn::before {
            transition: none;
            animation: none !important;
        }
    }
</style>

<div class="student-logout-confirm" id="studentLogoutConfirm" aria-hidden="true">
    <section class="student-logout-confirm-dialog" role="dialog" aria-modal="true" aria-labelledby="studentLogoutConfirmTitle" aria-describedby="studentLogoutConfirmCopy">
        <span class="student-logout-confirm-icon" aria-hidden="true">
            <x-outline-icon name="exclamation-triangle" />
        </span>
        <h2 class="student-logout-confirm-title" id="studentLogoutConfirmTitle">Logout</h2>
        <p class="student-logout-confirm-copy" id="studentLogoutConfirmCopy">
            <span class="student-logout-confirm-copy-line">Are you sure you want to logout?</span>
            <span class="student-logout-confirm-copy-line is-supporting">You will need to sign in again to access your account.</span>
        </p>
        <div class="student-logout-confirm-actions">
            <button type="button" class="student-logout-confirm-btn is-primary" id="studentLogoutConfirmSubmit">
                <span>Logout</span>
            </button>
            <button type="button" class="student-logout-confirm-btn is-secondary" id="studentLogoutConfirmCancel">
                <span>Cancel</span>
            </button>
        </div>
    </section>
</div>

<script>
    (function initStudentLogoutConfirm() {
        const modal = document.getElementById('studentLogoutConfirm');
        const form = document.getElementById('logout-form');
        const confirmButton = document.getElementById('studentLogoutConfirmSubmit');
        const cancelButton = document.getElementById('studentLogoutConfirmCancel');
        const triggers = Array.from(document.querySelectorAll('[data-student-logout-trigger]'));

        if (!modal || !form || !confirmButton || !cancelButton || triggers.length === 0) return;

        let lastFocusedElement = null;
        let closeTimer = null;
        let focusTimer = null;
        let isSubmitting = false;

        const openModal = function () {
            if (isSubmitting) return;
            window.clearTimeout(closeTimer);
            window.clearTimeout(focusTimer);
            lastFocusedElement = document.activeElement instanceof HTMLElement ? document.activeElement : null;
            modal.classList.remove('is-closing');
            modal.classList.add('is-open');
            modal.setAttribute('aria-hidden', 'false');
            document.body.classList.add('student-logout-confirm-open');
            focusTimer = window.setTimeout(function () {
                confirmButton.focus({ preventScroll: true });
            }, 40);
        };

        const closeModal = function () {
            if (isSubmitting || !modal.classList.contains('is-open') || modal.classList.contains('is-closing')) return;
            window.clearTimeout(focusTimer);
            modal.classList.add('is-closing');
            modal.setAttribute('aria-hidden', 'true');
            document.body.classList.remove('student-logout-confirm-open');
            closeTimer = window.setTimeout(function () {
                modal.classList.remove('is-open', 'is-closing');
                const returnFocus = lastFocusedElement?.getClientRects().length
                    ? lastFocusedElement
                    : Array.from(document.querySelectorAll('.nav-toggle, .nav-dropdown-toggle'))
                        .find(function (element) { return element.getClientRects().length > 0; });
                returnFocus?.focus({ preventScroll: true });
                lastFocusedElement = null;
            }, 180);
        };

        triggers.forEach(function (trigger) {
            trigger.addEventListener('click', function (event) {
                event.preventDefault();
                openModal();
            });
        });

        cancelButton.addEventListener('click', closeModal);
        modal.addEventListener('click', function (event) {
            if (event.target === modal) closeModal();
        });
        document.addEventListener('keydown', function (event) {
            if (!modal.classList.contains('is-open') || isSubmitting) return;
            if (event.key === 'Escape') {
                closeModal();
            } else if (event.key === 'Tab' && !modal.classList.contains('is-closing')) {
                if (event.shiftKey && document.activeElement === confirmButton) {
                    event.preventDefault();
                    cancelButton.focus({ preventScroll: true });
                } else if (!event.shiftKey && document.activeElement === cancelButton) {
                    event.preventDefault();
                    confirmButton.focus({ preventScroll: true });
                }
            }
        });

        confirmButton.addEventListener('click', function () {
            if (isSubmitting || !modal.classList.contains('is-open') || modal.classList.contains('is-closing')) return;
            isSubmitting = true;
            window.clearTimeout(focusTimer);
            confirmButton.disabled = true;
            cancelButton.disabled = true;
            modal.classList.remove('is-open', 'is-closing');
            modal.setAttribute('aria-hidden', 'true');
            document.body.classList.remove('student-logout-confirm-open');
            form.requestSubmit();
        });
    })();
</script>

