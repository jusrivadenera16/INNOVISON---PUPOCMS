<script>
    (function () {
        const handoffKey = 'pup_student_navigation_loader_handoff';

        try {
            const rawHandoff = sessionStorage.getItem(handoffKey);
            if (!rawHandoff) return;

            const handoff = JSON.parse(rawHandoff);
            const startedAt = Number(handoff?.startedAt || 0);
            const isRecent = startedAt > 0 && Date.now() - startedAt >= 0 && Date.now() - startedAt <= 90000;
            const isStudentShellPath = (path) => /^\/(student(?:\/|$)|account(?:\/|$)|health-form(?:\/|$))/.test(String(path || ''));

            if (!isRecent || !isStudentShellPath(handoff?.destinationPath) || !isStudentShellPath(window.location.pathname)) {
                sessionStorage.removeItem(handoffKey);
                return;
            }

            document.documentElement.style.setProperty(
                '--student-pending-loader-title',
                JSON.stringify(String(handoff.title || 'Opening Portal').trim())
            );
            document.documentElement.style.setProperty(
                '--student-pending-loader-detail',
                JSON.stringify(String(handoff.detail || 'Loading your portal information.').trim())
            );
            document.documentElement.setAttribute('data-student-navigation-pending', 'true');
        } catch (error) {
            document.documentElement.removeAttribute('data-student-navigation-pending');
        }
    })();
</script>

<style>
    @keyframes studentNavigationHeartbeat {
        to { stroke-dashoffset: 0; }
    }

    .student-navigation-loader {
        position: fixed;
        inset: 0;
        z-index: 2147483000;
        display: none;
        align-items: center;
        justify-content: center;
        overflow: hidden;
        background: #f8f9fc;
        color: #7f1d2d;
        backdrop-filter: blur(4px);
        -webkit-backdrop-filter: blur(4px);
    }

    html[data-theme="dark"] .student-navigation-loader {
        background: #0f172a;
        color: #facc15;
    }

    .student-navigation-loader.is-active,
    html[data-student-navigation-pending="true"] .student-navigation-loader {
        display: flex;
    }

    .student-loader-copy {
        position: relative;
        z-index: 2;
        display: none;
        max-width: min(88vw, 470px);
        padding: 14px;
        text-align: center;
    }

    .student-navigation-loader.is-active .student-loader-copy {
        display: block;
    }

    .student-loader-heartbeat {
        width: min(66vw, 210px);
        height: 60px;
        margin: 0 auto 8px;
    }

    .student-loader-heartbeat svg {
        display: block;
        width: 100%;
        height: 100%;
        overflow: visible;
    }

    .student-loader-heartbeat polyline {
        fill: none;
        stroke-width: 2.8;
        stroke-linecap: round;
        stroke-linejoin: round;
        stroke-miterlimit: 10;
    }

    .student-loader-heartbeat .student-loader-trace { stroke: rgba(190, 18, 60, .22); }
    .student-loader-heartbeat .student-loader-pulse {
        stroke: #be123c;
        stroke-dasharray: 65 435;
        stroke-dashoffset: 500;
        animation: studentNavigationHeartbeat 2.2s linear infinite;
    }

    html[data-theme="dark"] .student-loader-heartbeat .student-loader-trace { stroke: rgba(250, 204, 21, .28); }
    html[data-theme="dark"] .student-loader-heartbeat .student-loader-pulse { stroke: #facc15; }

    .student-loader-message {
        display: inline-flex;
        justify-content: center;
        align-items: center;
        color: #7f1d2d;
        font-size: clamp(18px, 2.5vw, 23px);
        font-weight: 850;
        line-height: 1.25;
        letter-spacing: -.02em;
    }

    html[data-theme="dark"] .student-loader-message { color: #facc15; }

    .student-loader-dots { display: inline-flex; margin-left: 1px; }

    .student-loader-dots span {
        opacity: .18;
        animation: studentLoaderDotReveal 1.2s infinite ease-in-out;
    }

    .student-loader-dots span:nth-child(2) { animation-delay: .16s; }
    .student-loader-dots span:nth-child(3) { animation-delay: .32s; }

    .student-loader-detail {
        margin: 6px 0 0;
        color: #5b6472;
        font-size: 13px;
        font-weight: 500;
        line-height: 1.45;
    }

    html[data-theme="dark"] .student-loader-detail { color: #cbd5e1; }

    @keyframes studentLoaderDotReveal {
        0%, 60%, 100% { opacity: .18; }
        30% { opacity: 1; }
    }

    html[data-student-navigation-pending="true"] .student-loader-copy { display: block; }

    html[data-student-navigation-pending="true"] .student-loader-message [data-student-loader-title],
    html[data-student-navigation-pending="true"] [data-student-loader-detail] {
        font-size: 0;
    }

    html[data-student-navigation-pending="true"] .student-loader-message [data-student-loader-title]::before {
        content: var(--student-pending-loader-title, "Opening Portal");
        font-size: clamp(18px, 2.5vw, 23px);
    }

    html[data-student-navigation-pending="true"] [data-student-loader-detail]::before {
        display: block;
        content: var(--student-pending-loader-detail, "Loading your portal information.");
        font-size: 13px;
    }

    @media (max-width: 700px) {
        .student-loader-copy { padding: 12px; }
    }

    @media (prefers-reduced-motion: reduce) {
        .student-loader-heartbeat .student-loader-pulse,
        .student-loader-dots span,
    }
</style>
