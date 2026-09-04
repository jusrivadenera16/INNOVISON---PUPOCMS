<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>@yield('title') - PUP Taguig Clinic</title>
    <script
        src="{{ asset('js/sienna-accessibility-custom.umd.js') }}?v={{ filemtime(public_path('js/sienna-accessibility-custom.umd.js')) }}"
        data-asw-position="bottom-right"
        data-asw-offset="24,12"
        data-asw-size="small"
        defer
    ></script>
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const banner = document.getElementById('clinicAvailabilityBanner');
            const dismiss = document.getElementById('clinicAvailabilityBannerDismiss');
            if (!banner || !dismiss) {
                return;
            }

            const storageKey = 'pup-clinic-advisory:' + banner.dataset.dismissKey;
            try {
                if (sessionStorage.getItem(storageKey) === 'dismissed') {
                    banner.hidden = true;
                }
            } catch (error) {
                // Keep the advisory visible when browser storage is unavailable.
            }

            dismiss.addEventListener('click', function () {
                banner.hidden = true;
                try {
                    sessionStorage.setItem(storageKey, 'dismissed');
                } catch (error) {
                    // The current page can still dismiss the advisory.
                }
            });

            const logoutForm = document.getElementById('logout-form');
            if (logoutForm) {
                logoutForm.addEventListener('submit', function () {
                    try {
                        Object.keys(sessionStorage)
                            .filter(function (key) {
                                return key.indexOf('pup-clinic-advisory:') === 0;
                            })
                            .forEach(function (key) {
                                sessionStorage.removeItem(key);
                            });
                    } catch (error) {
                        // Logout continues even when browser storage is unavailable.
                    }
                });
            }
        });

        (function() {
            try {
                var savedTheme = localStorage.getItem('student_theme');
                var prefersDark = window.matchMedia && window.matchMedia('(prefers-color-scheme: dark)').matches;
                var theme = savedTheme || (prefersDark ? 'dark' : 'light');
                document.documentElement.setAttribute('data-theme', theme);
            } catch (error) {
                document.documentElement.setAttribute('data-theme', 'light');
            }
        })();
    </script>
    <link rel="stylesheet" href="{{ asset('css/booking.css') }}">
    
    <style>
        @keyframes accessibilityPulseRing {
            0% {
                transform: scale(1);
                opacity: 0.95;
            }
            70% {
                transform: scale(1.22);
                opacity: 0;
            }
            100% {
                transform: scale(1.22);
                opacity: 0;
            }
        }

        @keyframes accessibilityRingColorShift {
            0% {
                border-color: rgb(255, 0, 0);
                box-shadow: 0 0 0 2px rgba(255, 0, 0, 0.2);
            }
            33% {
                border-color: rgb(255, 215, 0);
                box-shadow: 0 0 0 2px rgba(255, 215, 0, 0.2);
            }
            66% {
                border-color: rgb(0, 191, 255);
                box-shadow: 0 0 0 2px rgba(0, 191, 255, 0.2);
            }
            100% {
                border-color: rgb(255, 0, 0);
                box-shadow: 0 0 0 2px rgba(255, 0, 0, 0.2);
            }
        }

        :where(.asw-menu-btn) {
            position: fixed;
            left: auto !important;
            right: 24px !important;
            top: auto !important;
            bottom: 12px !important;
            overflow: visible !important;
            background: #800000 !important;
            background-image: none !important;
            border: 2px solid #5f0012 !important;
            outline: none !important;
            box-shadow: 0 10px 24px rgba(128, 0, 0, 0.28) !important;
        }

        :where(.asw-menu-btn) {
            opacity: 0 !important;
            pointer-events: none !important;
            z-index: -1 !important;
        }

        :where(.asw-menu-btn)::after {
            content: "";
            position: absolute;
            inset: -6px;
            border: 3px solid rgb(255, 0, 0);
            border-radius: 999px;
            pointer-events: none;
            animation:
                accessibilityPulseRing 1.9s ease-out infinite,
                accessibilityRingColorShift 3.2s linear infinite;
            box-shadow: 0 0 0 2px rgba(255, 0, 0, 0.2);
        }

        :where(.asw-menu-btn:hover),
        :where(.asw-menu-btn:focus-visible) {
            background: #800000 !important;
            background-image: none !important;
            border-color: #5f0012 !important;
            outline: none !important;
        }

        :where(.asw-menu-btn svg) {
            fill: #ffffff !important;
            stroke: none !important;
            transform-origin: center;
        }

        :where(.asw-menu-btn svg path:not([fill="none"])) {
            fill: #ffffff !important;
            stroke: none !important;
        }

        :where(.asw-menu-btn svg path[fill="none"]) {
            stroke: none !important;
        }

        img,
        svg,
        video,
        canvas {
            max-width: 100%;
            height: auto;
        }

        main {
            width: 100%;
        }

        :where(
            [class*="sienna"][role="dialog"],
            [class*="sienna"][role="menu"],
            [id*="sienna"][role="dialog"],
            [id*="sienna"][role="menu"],
            [class*="sienna-menu"],
            [class*="sienna-panel"],
            [id*="sienna-menu"],
            [id*="sienna-panel"]
        ) {
            background: linear-gradient(180deg, #7f1d2d 0%, #4b5563 100%) !important;
            border: 1px solid rgba(255, 255, 255, 0.18) !important;
            color: #f8fafc !important;
            box-shadow: 0 18px 38px rgba(15, 23, 42, 0.35) !important;
        }

        :where(
            [class*="sienna-menu"],
            [class*="sienna-panel"],
            [id*="sienna-menu"],
            [id*="sienna-panel"]
        ) :is(header, [class*="header"], [class*="title"], [class*="top"]):first-child {
            background: linear-gradient(135deg, #8b0000 0%, #6b7280 100%) !important;
            color: #ffffff !important;
            border-bottom: 1px solid rgba(255, 255, 255, 0.16) !important;
        }

        :where(
            [class*="sienna-menu"],
            [class*="sienna-panel"],
            [id*="sienna-menu"],
            [id*="sienna-panel"]
        ) :is(button, [role="button"], input, select) {
            background: rgba(255, 255, 255, 0.12) !important;
            border-color: rgba(255, 255, 255, 0.22) !important;
            color: #f8fafc !important;
        }

        :where(
            [class*="sienna-menu"],
            [class*="sienna-panel"],
            [id*="sienna-menu"],
            [id*="sienna-panel"]
        ) :is(button, [role="button"]):hover {
            background: rgba(255, 255, 255, 0.2) !important;
        }

        /* --- 1. HEADER SPACING FIX --- */
        .site-header .header-inner {
            max-width: 100% !important; /* Force full width */
            width: 100%;
            padding-left: 50px;  /* Push Logo Left */
            padding-right: 50px; /* Push Menu Right */
        }

        /* --- 2. NAV HOVER EFFECTS --- */
        .nav-list {
            display: flex;
            gap: 30px; /* Increased gap between links */
            list-style: none;
            margin: 0;
            padding: 0;
            align-items: center;
        }

        .nav-list li a {
            color: rgba(255, 255, 255, 0.9);
            text-decoration: none;
            font-weight: 500;
            font-size: 15px;
            position: relative;
            transition: color 0.3s ease;
            padding: 5px 0;
            display: inline-flex;
            align-items: center;
            gap: 8px;
        }

        .nav-list li.nav-dropdown {
            position: relative;
        }

        .nav-list-divider {
            width: 1px;
            height: 24px;
            background: rgba(255, 255, 255, 0.28);
            margin: 0 6px;
            flex: 0 0 auto;
        }

        .nav-dropdown-toggle {
            background: none;
            border: none;
            color: rgba(255, 255, 255, 0.9);
            font-weight: 500;
            font-size: 15px;
            padding: 5px 0;
            cursor: pointer;
            display: inline-flex;
            align-items: center;
            gap: 8px;
            transition: color 0.3s ease;
            font-family: inherit;
        }

        .nav-link-content,
        .nav-dropdown-link-content {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            min-width: 0;
        }

        .nav-link-icon,
        .nav-dropdown-link-icon {
            width: 18px;
            height: 18px;
            flex: 0 0 auto;
            stroke-width: 1.8;
        }

        .nav-dropdown-toggle:hover,
        .nav-dropdown-toggle[aria-expanded="true"],
        .nav-dropdown-toggle.active {
            color: #ffc107;
        }

        .student-quick-actions-wrap {
            position: relative;
        }

        .student-quick-actions-fab-wrap {
            position: fixed;
            right: 24px;
            bottom: 18px;
            display: flex;
            align-items: center;
            z-index: 499997;
        }

        .student-quick-actions-toggle,
        .student-quick-action-btn,
        .student-quick-action-logo {
            width: 66px;
            height: 66px;
            border-radius: 999px;
            border: 2px solid #facc15;
            background: linear-gradient(145deg, #9b111e, #6e1220 55%, #4f0b15);
            color: #ffffff !important;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            flex: 0 0 auto;
            padding: 0;
            box-shadow:
                0 0 0 3px rgba(250, 204, 21, 0.12),
                0 0 18px rgba(250, 204, 21, 0.26),
                0 10px 22px rgba(95, 0, 18, 0.28);
            transition: background 0.2s ease, border-color 0.2s ease, transform 0.2s ease, box-shadow 0.2s ease;
        }

        .student-quick-actions-toggle,
        .student-quick-action-btn {
            cursor: pointer;
        }

        .student-quick-actions-toggle {
            animation: quickActionsGlow 2.2s ease-in-out infinite;
            position: relative;
            margin-left: 0;
        }

        .student-quick-actions-toggle:hover,
        .student-quick-action-btn:hover {
            background: linear-gradient(145deg, #b01826, #7f1d2d 55%, #5a0f16);
            border-color: #fde047;
            transform: translateY(-2px) scale(1.02);
            box-shadow:
                0 0 0 3px rgba(250, 204, 21, 0.18),
                0 0 22px rgba(250, 204, 21, 0.34),
                0 14px 28px rgba(95, 0, 18, 0.36);
        }

        .student-quick-action-item:hover .student-quick-action-btn,
        .student-quick-action-item:hover .student-quick-action-logo {
            box-shadow:
                0 0 0 3px rgba(250, 204, 21, 0.18),
                0 0 10px rgba(255, 0, 102, 0.34),
                0 0 18px rgba(0, 200, 255, 0.32),
                0 0 26px rgba(255, 221, 0, 0.3),
                0 14px 28px rgba(95, 0, 18, 0.36) !important;
        }

        .student-quick-action-item:hover .student-quick-action-btn svg,
        .student-quick-action-item:hover .student-quick-action-logo img {
            animation: quickActionShake 0.42s ease-in-out;
            filter:
                drop-shadow(0 0 6px rgba(255, 0, 102, 0.45))
                drop-shadow(0 0 10px rgba(0, 200, 255, 0.38))
                drop-shadow(0 0 14px rgba(255, 221, 0, 0.42));
        }

        .student-quick-action-item:hover .student-accessibility-launch svg {
            animation: quickActionShakeAccessibility 0.42s ease-in-out;
        }

        .student-quick-actions-toggle:focus-visible,
        .student-quick-action-btn:focus-visible,
        .student-quick-action-link:focus-visible {
            outline: 2px solid #facc15;
            outline-offset: 2px;
        }

        .student-quick-actions-toggle svg,
        .student-quick-action-btn svg,
        .student-quick-action-link svg {
            width: 24px;
            height: 24px;
            stroke: currentColor;
            fill: none;
            stroke-width: 2;
            stroke-linecap: round;
            stroke-linejoin: round;
            display: block;
            transition: transform 0.28s cubic-bezier(0.22, 1, 0.36, 1);
        }

        .student-quick-action-bell {
            position: relative;
        }

        .student-accessibility-launch svg {
            transform: none;
            transform-origin: center;
            overflow: visible;
        }

        .student-quick-actions-wrap.is-open .student-quick-actions-toggle svg {
            transform: rotate(135deg) scale(1.04);
        }

        .student-quick-actions-badge {
            position: absolute;
            top: -4px;
            right: -4px;
            min-width: 18px;
            height: 18px;
            padding: 0 5px;
            border-radius: 999px;
            background: #ffb81c;
            color: #5a0f16;
            border: 2px solid rgba(44, 14, 21, 0.96);
            font-size: 10px;
            font-weight: 900;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            line-height: 1;
        }

        .student-quick-actions-toggle > .student-quick-actions-badge {
            top: 1px;
            right: 1px;
            border-color: #7f1d2d;
            transition: opacity 0.16s ease, visibility 0.16s ease, transform 0.16s ease;
        }

        .student-quick-actions-wrap.is-open > .student-quick-actions-toggle > .student-quick-actions-badge {
            opacity: 0;
            visibility: hidden;
            transform: scale(0.76);
            pointer-events: none;
        }

        .student-quick-actions-wrap.is-open .student-quick-action-bell > .student-quick-actions-badge {
            opacity: 1 !important;
            visibility: visible !important;
            transform: none !important;
            z-index: 4;
        }

        .student-quick-actions-panel {
            position: absolute;
            left: 50%;
            right: auto;
            bottom: calc(100% + 16px);
            display: grid;
            grid-template-columns: 1fr;
            justify-items: center;
            align-items: center;
            gap: 0;
            width: 66px;
            min-width: 66px;
            padding: 0 0 6px;
            border-radius: 16px;
            border: none;
            background: transparent;
            box-shadow: none;
            backdrop-filter: none;
            opacity: 0;
            visibility: hidden;
            transform: translateX(-50%) translateY(6px) scale(0.96);
            transform-origin: bottom center;
            transition: opacity 0.18s ease, transform 0.18s ease, visibility 0.18s ease;
            z-index: 1002;
            overflow: visible;
        }

        .student-quick-actions-wrap.is-open .student-quick-actions-panel {
            opacity: 1;
            visibility: visible;
            transform: translateX(-50%) translateY(0) scale(1);
        }

        .student-quick-actions-panel::after {
            content: "";
            position: absolute;
            left: 50%;
            bottom: 2px;
            width: 54px;
            height: 16px;
            border-bottom: 3px solid #70131B;
            border-radius: 0 0 999px 999px;
            transform: translateX(-50%);
            filter: drop-shadow(0 0 8px rgba(250, 204, 21, 0.68));
            pointer-events: none;
            opacity: 0;
            visibility: hidden;
            z-index: 1;
        }

        .student-quick-actions-panel::before {
            content: "";
            position: absolute;
            left: 50%;
            bottom: -6px;
            width: 0;
            height: 0;
            border-left: 8px solid transparent;
            border-right: 8px solid transparent;
            border-top: 12px solid #70131B;
            transform: translateX(-50%);
            filter: drop-shadow(0 0 8px rgba(250, 204, 21, 0.68));
            pointer-events: none;
            opacity: 0;
            visibility: hidden;
            z-index: 1;
        }

        .student-quick-actions-wrap.is-open .student-quick-actions-panel::before,
        .student-quick-actions-wrap.is-open .student-quick-actions-panel::after {
            opacity: 1;
            visibility: visible;
        }

        .student-quick-action-item {
            position: relative;
            display: flex;
            width: 100%;
            justify-content: center;
            opacity: 0;
            transform: translateY(18px) scale(0.86);
            transition: opacity 0.22s ease, transform 0.28s cubic-bezier(0.22, 1, 0.36, 1);
            z-index: 2;
        }

        .student-quick-action-item.is-accessibility {
            grid-row: 1;
            margin-top: 0;
        }

        .student-quick-action-item.is-theme {
            grid-row: 2;
            margin-top: 4px;
        }

        .student-quick-action-item.is-notifications {
            grid-row: 3;
            margin-top: 4px;
        }

        .student-quick-actions-wrap.is-open .student-quick-action-item {
            opacity: 1;
            transform: translateY(0) scale(1);
        }

        .student-quick-actions-wrap.is-open .student-quick-action-item:nth-child(1) {
            transition-delay: 0.1s;
        }

        .student-quick-actions-wrap.is-open .student-quick-action-item:nth-child(2) {
            transition-delay: 0.07s;
        }

        .student-quick-actions-wrap.is-open .student-quick-action-item:nth-child(3) {
            transition-delay: 0.04s;
        }

        .student-quick-actions-divider {
            width: 100%;
            height: 1px;
            background: rgba(255, 255, 255, 0.12);
            margin: 2px 0;
        }

        .student-quick-action-link,
        .student-quick-action-btn {
            text-decoration: none;
            margin: 0 auto;
        }

        .student-quick-action-tooltip {
            position: absolute;
            top: 50%;
            right: calc(100% + 14px);
            transform: translateY(-50%) translateX(6px);
            padding: 8px 12px;
            border-radius: 12px;
            background: rgba(44, 14, 21, 0.96);
            border: 1px solid #facc15;
            color: #ffffff;
            font-size: 12px;
            font-weight: 700;
            line-height: 1;
            white-space: nowrap;
            opacity: 0;
            visibility: hidden;
            pointer-events: none;
            box-shadow: 0 10px 22px rgba(44, 14, 21, 0.24);
            transition: opacity 0.18s ease, transform 0.18s ease, visibility 0.18s ease;
        }

        .student-quick-action-tooltip::after {
            content: "";
            position: absolute;
            top: 50%;
            right: -6px;
            width: 10px;
            height: 10px;
            background: rgba(44, 14, 21, 0.96);
            border-right: 1px solid #facc15;
            border-top: 1px solid #facc15;
            transform: translateY(-50%) rotate(45deg);
        }

        .student-quick-action-item:hover .student-quick-action-tooltip,
        .student-quick-action-item:focus-within .student-quick-action-tooltip {
            opacity: 1;
            visibility: visible;
            transform: translateY(-50%) translateX(0);
        }

        .student-notif-panel {
            position: absolute;
            right: calc(100% + 14px);
            bottom: -10px;
            width: min(340px, calc(100vw - 32px));
            border-radius: 18px;
            background: rgba(255, 248, 249, 0.74);
            border: 1px solid rgba(250, 204, 21, 0.48);
            box-shadow: 0 22px 48px rgba(15, 23, 42, 0.2);
            backdrop-filter: blur(16px) saturate(145%);
            -webkit-backdrop-filter: blur(16px) saturate(145%);
            padding: 14px;
            opacity: 0;
            visibility: hidden;
            transform: translateY(12px) scale(0.96);
            transform-origin: bottom right;
            pointer-events: none;
            transition: opacity 0.2s ease, transform 0.24s ease, visibility 0.2s ease;
            z-index: 1005;
        }

        .student-notif-panel.is-open {
            opacity: 1;
            visibility: visible;
            pointer-events: auto;
            transform: translateY(0) scale(1);
        }

        .student-notif-head {
            display: flex;
            align-items: flex-start;
            justify-content: space-between;
            gap: 12px;
            margin-bottom: 10px;
        }

        .student-notif-title {
            margin: 0;
            font-size: 16px;
            font-weight: 800;
            color: #70131B;
            line-height: 1.2;
        }

        .student-notif-subtitle {
            margin: 4px 0 0;
            font-size: 12px;
            font-weight: 600;
            color: #7c2d36;
        }

        .student-notif-actions {
            display: flex;
            align-items: center;
            gap: 6px;
            position: relative;
            flex-shrink: 0;
        }

        .student-notif-back-btn,
        .student-notif-actions-toggle,
        .student-notif-close {
            border: 1px solid rgba(112, 19, 27, 0.2);
            background: rgba(255, 255, 255, 0.86);
            color: #70131B;
            border-radius: 10px;
            cursor: pointer;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            font-weight: 700;
            transition: all 0.18s ease;
        }

        .student-notif-actions-toggle,
        .student-notif-close {
            width: 34px;
            height: 34px;
            padding: 0;
        }

        .student-notif-back-btn {
            height: 34px;
            padding: 0 10px;
            gap: 4px;
            font-size: 12px;
            display: none;
        }

        .student-notif-back-btn.is-visible {
            display: inline-flex;
        }

        .student-notif-back-btn svg,
        .student-notif-actions-toggle svg,
        .student-notif-close svg {
            width: 16px;
            height: 16px;
            stroke-width: 2;
        }

        .student-notif-back-btn:hover,
        .student-notif-actions-toggle:hover,
        .student-notif-close:hover {
            background: #fff;
            border-color: rgba(250, 204, 21, 0.8);
            transform: translateY(-1px);
        }

        .student-notif-actions-menu {
            position: absolute;
            top: calc(100% + 8px);
            right: 0;
            min-width: 188px;
            padding: 8px;
            border-radius: 12px;
            border: 1px solid rgba(112, 19, 27, 0.2);
            background: rgba(255, 255, 255, 0.98);
            box-shadow: 0 14px 30px rgba(15, 23, 42, 0.2);
            opacity: 0;
            visibility: hidden;
            transform: translateY(4px) scale(0.98);
            transform-origin: top right;
            transition: opacity 0.18s ease, transform 0.18s ease, visibility 0.18s ease;
            z-index: 6;
        }

        .student-notif-actions-menu.is-open {
            opacity: 1;
            visibility: visible;
            transform: translateY(0) scale(1);
        }

        .student-notif-actions-menu form {
            margin: 0 0 6px;
        }

        .student-notif-actions-submit {
            width: 100%;
            border: 1px solid rgba(112, 19, 27, 0.18);
            background: #fff;
            border-radius: 10px;
            padding: 8px 10px;
            display: inline-flex;
            align-items: center;
            gap: 8px;
            font-size: 12px;
            font-weight: 700;
            color: #70131B;
            cursor: pointer;
            transition: all 0.18s ease;
        }

        .student-notif-actions-submit:hover {
            border-color: rgba(250, 204, 21, 0.84);
            background: #fffdf3;
        }

        .student-notif-actions-submit:disabled {
            opacity: 0.58;
            cursor: not-allowed;
        }

        .student-notif-actions-submit svg {
            width: 14px;
            height: 14px;
            stroke-width: 2;
        }

        .student-notif-section {
            display: block;
        }

        .student-notif-section.is-hidden {
            display: none;
        }

        .student-notif-list {
            display: flex;
            flex-direction: column;
            gap: 8px;
            max-height: 300px;
            overflow: auto;
            padding-right: 2px;
        }

        .student-notif-item {
            border: 1px solid #e2e8f0;
            border-radius: 12px;
            background: #ffffff;
        }

        .student-notif-item.is-unread {
            border-color: #f5d0d0;
            background: #fff7f7;
        }

        .student-notif-item-link {
            display: flex;
            gap: 10px;
            text-decoration: none;
            padding: 11px 12px;
            color: inherit;
        }

        .student-notif-dot {
            width: 10px;
            height: 10px;
            border-radius: 999px;
            background: #8B0000;
            margin-top: 6px;
            flex: 0 0 auto;
        }

        .student-notif-dot.is-read {
            background: #cbd5e1;
        }

        .student-notif-content {
            min-width: 0;
            flex: 1;
        }

        .student-notif-item-title {
            display: block;
            font-size: 13px;
            line-height: 1.45;
            font-weight: 700;
            color: #1f2937;
        }

        .student-notif-item-time {
            display: block;
            margin-top: 4px;
            font-size: 11px;
            color: #64748b;
        }

        .student-notif-chip {
            display: inline-flex;
            align-items: center;
            margin-left: 8px;
            padding: 2px 6px;
            border-radius: 999px;
            font-size: 10px;
            font-weight: 800;
            background: #e2e8f0;
            color: #475569;
        }

        .student-notif-empty {
            border: 1px dashed #cbd5e1;
            border-radius: 12px;
            padding: 16px 12px;
            text-align: center;
            background: #ffffff;
        }

        .student-notif-empty-title {
            margin: 0;
            font-size: 13px;
            font-weight: 800;
            color: #475569;
        }

        .student-notif-empty-copy {
            margin: 6px 0 0;
            font-size: 11px;
            color: #64748b;
        }

        html[data-theme="light"] .student-quick-actions-toggle,
        html[data-theme="light"] .student-quick-action-btn,
        html[data-theme="light"] .student-quick-action-link {
            background: linear-gradient(145deg, #9b111e, #6e1220 55%, #4f0b15);
            border-color: #facc15;
            color: #ffffff !important;
            box-shadow:
                0 0 0 3px rgba(250, 204, 21, 0.12),
                0 0 18px rgba(250, 204, 21, 0.32),
                0 12px 26px rgba(95, 0, 18, 0.34);
        }

        html[data-theme="light"] .student-quick-actions-toggle:hover,
        html[data-theme="light"] .student-quick-action-btn:hover,
        html[data-theme="light"] .student-quick-action-link:hover {
            background: linear-gradient(145deg, rgba(176, 24, 38, 0.82), rgba(127, 29, 45, 0.7) 55%, rgba(90, 15, 22, 0.8));
            border-color: #fde047;
            color: #ffffff !important;
        }

        html[data-theme="light"] .student-quick-actions-divider {
            background: rgba(128, 0, 0, 0.12);
        }

        html[data-theme="light"] .student-quick-actions-panel::after {
            border-bottom-color: #facc15;
            filter: drop-shadow(0 0 8px rgba(250, 204, 21, 0.75));
        }

        html[data-theme="light"] .student-quick-actions-panel::before {
            border-top-color: #facc15;
            filter: drop-shadow(0 0 8px rgba(250, 204, 21, 0.75));
        }

        html[data-theme="light"] .student-quick-actions-badge {
            border-color: rgba(255, 255, 255, 0.98);
        }

        html[data-theme="dark"] .student-quick-actions-toggle,
        html[data-theme="dark"] .student-quick-action-btn,
        html[data-theme="dark"] .student-quick-action-link {
            background: linear-gradient(145deg, #9b111e, #6e1220 55%, #4f0b15);
            border-color: #facc15;
            color: #ffffff !important;
            box-shadow:
                0 0 0 3px rgba(250, 204, 21, 0.12),
                0 0 18px rgba(250, 204, 21, 0.32),
                0 12px 26px rgba(95, 0, 18, 0.34);
        }

        html[data-theme="dark"] .student-quick-actions-toggle:hover,
        html[data-theme="dark"] .student-quick-action-btn:hover,
        html[data-theme="dark"] .student-quick-action-link:hover {
            background: linear-gradient(145deg, rgba(176, 24, 38, 0.82), rgba(127, 29, 45, 0.7) 55%, rgba(90, 15, 22, 0.8));
            border-color: #fde047;
            color: #ffffff !important;
            box-shadow:
                0 0 0 3px rgba(250, 204, 21, 0.14),
                0 0 20px rgba(250, 204, 21, 0.24),
                0 14px 26px rgba(15, 23, 42, 0.3);
        }

        html[data-theme="dark"] .student-quick-actions-panel::after {
            border-bottom-color: #facc15;
        }

        html[data-theme="dark"] .student-quick-actions-panel::before {
            border-top-color: #facc15;
        }

        html[data-theme="dark"] .student-quick-action-tooltip {
            background: rgba(17, 24, 39, 0.96);
            border-color: rgba(250, 204, 21, 0.72);
            color: #f8fafc;
            box-shadow: 0 14px 28px rgba(2, 6, 23, 0.34);
        }

        html[data-theme="dark"] .student-quick-action-tooltip::after {
            background: rgba(17, 24, 39, 0.96);
            border-right-color: rgba(250, 204, 21, 0.72);
            border-top-color: rgba(250, 204, 21, 0.72);
        }

        html[data-theme="dark"] .student-notif-panel {
            background: rgba(12, 18, 29, 0.8);
            border-color: rgba(250, 204, 21, 0.38);
            box-shadow: 0 22px 48px rgba(2, 6, 23, 0.44);
        }

        html[data-theme="dark"] .student-notif-title {
            color: #f8fafc;
        }

        html[data-theme="dark"] .student-notif-subtitle {
            color: #cbd5e1;
        }

        html[data-theme="dark"] .student-notif-back-btn,
        html[data-theme="dark"] .student-notif-actions-toggle,
        html[data-theme="dark"] .student-notif-close {
            background: rgba(17, 24, 39, 0.92);
            color: #f8fafc;
            border-color: rgba(148, 163, 184, 0.35);
        }

        html[data-theme="dark"] .student-notif-back-btn:hover,
        html[data-theme="dark"] .student-notif-actions-toggle:hover,
        html[data-theme="dark"] .student-notif-close:hover {
            border-color: rgba(250, 204, 21, 0.85);
            background: rgba(30, 41, 59, 0.96);
        }

        html[data-theme="dark"] .student-notif-actions-menu {
            background: rgba(2, 6, 23, 0.98);
            border-color: rgba(148, 163, 184, 0.36);
        }

        html[data-theme="dark"] .student-notif-actions-submit {
            background: rgba(15, 23, 42, 0.9);
            color: #f8fafc;
            border-color: rgba(148, 163, 184, 0.36);
        }

        html[data-theme="dark"] .student-notif-actions-submit:hover {
            border-color: rgba(250, 204, 21, 0.85);
            background: rgba(30, 41, 59, 0.95);
        }

        html[data-theme="dark"] .student-notif-item {
            background: #111a24;
            border-color: #3b4657;
        }

        html[data-theme="dark"] .student-notif-item.is-unread {
            background: rgba(139, 0, 0, 0.2);
            border-color: #7f1d2d;
        }

        html[data-theme="dark"] .student-notif-item-title {
            color: #e5ecf7;
        }

        html[data-theme="dark"] .student-notif-item-time {
            color: #9fb0c8;
        }

        html[data-theme="dark"] .student-notif-chip {
            background: rgba(148, 163, 184, 0.2);
            color: #e2e8f0;
        }

        html[data-theme="dark"] .student-notif-empty {
            background: #111a24;
            border-color: #3b4657;
        }

        html[data-theme="dark"] .student-notif-empty-title {
            color: #e5ecf7;
        }

        html[data-theme="dark"] .student-notif-empty-copy {
            color: #9fb0c8;
        }

        .student-toast-stack {
            position: fixed;
            top: calc(var(--header-height, 74px) + 16px);
            right: 26px;
            z-index: 499996;
            display: grid;
            gap: 12px;
            width: min(330px, calc(100vw - 32px));
            pointer-events: none;
        }

        .student-toast {
            position: relative;
            display: grid;
            grid-template-columns: 42px minmax(0, 1fr) 28px;
            align-items: center;
            gap: 12px;
            min-height: 78px;
            padding: 14px 12px 14px 22px;
            border-radius: 8px;
            background: rgba(255, 255, 255, 0.98);
            color: #334155;
            box-shadow: 0 18px 36px rgba(15, 23, 42, 0.18);
            opacity: 0;
            transform: translateX(18px) scale(0.98);
            animation: studentToastIn 0.32s cubic-bezier(.2, .8, .2, 1) forwards;
            pointer-events: auto;
            overflow: hidden;
        }

        .student-toast::before {
            content: "";
            position: absolute;
            inset: 0 auto 0 0;
            width: 8px;
            background:
                linear-gradient(135deg, transparent 0 45%, var(--toast-accent) 46% 100%) 0 0 / 8px 12px,
                linear-gradient(45deg, transparent 0 45%, var(--toast-accent-soft) 46% 100%) 0 6px / 8px 12px;
        }

        .student-toast.is-success {
            --toast-accent: #86efac;
            --toast-accent-soft: #bbf7d0;
        }

        .student-toast.is-error {
            --toast-accent: #fca5a5;
            --toast-accent-soft: #fecaca;
        }

        .student-toast.is-hiding {
            animation: studentToastOut 0.22s ease forwards;
        }

        .student-toast-icon {
            width: 38px;
            height: 38px;
            border-radius: 999px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            background: var(--toast-accent-soft);
            color: var(--toast-title);
        }

        .student-toast.is-success .student-toast-icon,
        .student-toast.is-success .student-toast-title {
            --toast-title: #16a34a;
        }

        .student-toast.is-error .student-toast-icon,
        .student-toast.is-error .student-toast-title {
            --toast-title: #dc2626;
        }

        .student-toast-icon svg {
            width: 20px;
            height: 20px;
            stroke-width: 2.2;
        }

        .student-toast-title,
        .student-toast-message {
            display: block;
            line-height: 1.25;
        }

        .student-toast-title {
            margin-bottom: 4px;
            color: var(--toast-title);
            font-size: 16px;
            font-weight: 900;
        }

        .student-toast-message {
            color: #475569;
            font-size: 14px;
            font-weight: 500;
        }

        .student-toast-close {
            width: 28px;
            height: 28px;
            border: 0;
            border-radius: 999px;
            background: transparent;
            color: #475569;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            cursor: pointer;
            transition: background .18s ease, color .18s ease, transform .18s ease;
        }

        .student-toast-close:hover,
        .student-toast-close:focus-visible {
            background: rgba(15, 23, 42, 0.08);
            color: #111827;
            transform: scale(1.04);
            outline: none;
        }

        .student-toast-close svg {
            width: 17px;
            height: 17px;
            stroke-width: 2;
        }

        @keyframes studentToastIn {
            to {
                opacity: 1;
                transform: translateX(0) scale(1);
            }
        }

        @keyframes studentToastOut {
            to {
                opacity: 0;
                transform: translateX(18px) scale(0.98);
            }
        }

        html[data-theme="dark"] .student-toast {
            background: rgba(15, 23, 42, 0.98);
            color: #f8fafc;
            box-shadow: 0 18px 38px rgba(2, 6, 23, 0.38);
        }

        html[data-theme="dark"] .student-toast-message,
        html[data-theme="dark"] .student-toast-close {
            color: #cbd5e1;
        }

        @media (max-width: 768px) {
            .student-quick-actions-fab-wrap {
                right: 18px;
                bottom: 16px;
            }

            .student-quick-actions-toggle,
            .student-quick-action-btn,
            .student-quick-action-logo {
                width: 60px;
                height: 60px;
            }

            .student-accessibility-launch svg {
                width: 28px;
                height: 28px;
            }

            .student-quick-action-tooltip {
                display: none;
            }

            .student-notif-panel {
                right: 0;
                left: auto;
                bottom: calc(100% + 12px);
                width: min(320px, calc(100vw - 22px));
                transform-origin: bottom right;
            }

            .student-notif-panel.is-open {
                transform: translateY(0) scale(1);
            }

            .student-toast-stack {
                top: calc(var(--header-height, 74px) + 10px);
                right: 12px;
                width: min(310px, calc(100vw - 24px));
            }
        }

        .nav-dropdown-toggle:focus-visible {
            outline: 2px solid #ffc107;
            outline-offset: 3px;
            border-radius: 6px;
        }

        .nav-dropdown-caret {
            width: 9px;
            height: 9px;
            border-right: 2px solid currentColor;
            border-bottom: 2px solid currentColor;
            transform: rotate(45deg) translateY(-1px);
            transition: transform 0.2s ease;
        }

        .nav-dropdown.is-open .nav-dropdown-caret {
            transform: rotate(225deg) translateY(-1px);
        }

        .nav-dropdown-menu {
            position: absolute;
            top: calc(100% + 14px);
            right: 0;
            min-width: 220px;
            padding: 10px;
            margin: 0;
            list-style: none;
            background: #ffffff;
            border-radius: 14px;
            border: 1px solid rgba(139, 0, 0, 0.12);
            box-shadow: 0 18px 36px rgba(15, 23, 42, 0.16);
            display: block;
            z-index: 40;
            opacity: 0;
            visibility: hidden;
            pointer-events: none;
            transform: translateY(-8px) scale(.97);
            transform-origin: top right;
            transition: opacity .22s ease, transform .26s cubic-bezier(.2, .8, .2, 1), visibility .22s ease;
        }

        .nav-dropdown.is-open .nav-dropdown-menu {
            opacity: 1;
            visibility: visible;
            pointer-events: auto;
            transform: translateY(0) scale(1);
        }

        .nav-dropdown-menu li {
            width: 100%;
            opacity: 0;
            transform: translateY(-4px);
            transition: opacity .2s ease, transform .22s ease;
        }

        .nav-dropdown.is-open .nav-dropdown-menu li {
            opacity: 1;
            transform: translateY(0);
        }

        .nav-dropdown.is-open .nav-dropdown-menu li:nth-child(2) { transition-delay: .03s; }
        .nav-dropdown.is-open .nav-dropdown-menu li:nth-child(3) { transition-delay: .06s; }
        .nav-dropdown.is-open .nav-dropdown-menu li:nth-child(4) { transition-delay: .09s; }
        .nav-dropdown.is-open .nav-dropdown-menu li:nth-child(5) { transition-delay: .12s; }
        .nav-dropdown.is-open .nav-dropdown-menu li:nth-child(6) { transition-delay: .15s; }
        .nav-dropdown.is-open .nav-dropdown-menu li:nth-child(7) { transition-delay: .18s; }

        .nav-dropdown-menu li + li {
            margin-top: 4px;
        }

        .nav-dropdown-menu a {
            width: 100%;
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 12px;
            padding: 10px 12px !important;
            border-radius: 10px;
            color: #1f2937 !important;
            font-size: 14px !important;
            font-weight: 600 !important;
        }

        .nav-dropdown-link-accessory {
            display: inline-flex;
            align-items: center;
            flex: 0 0 auto;
            font-size: 12px;
            font-weight: 800;
            color: #8b0000;
        }

        .nav-dropdown-menu a::after {
            display: none !important;
        }

        .nav-dropdown-menu a:hover,
        .nav-dropdown-menu a.active {
            background: #fff7ed;
            color: #8b0000 !important;
        }

        .nav-dropdown-menu .nav-dropdown-disabled {
            width: 100%;
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 12px;
            padding: 10px 12px;
            border: 0;
            border-radius: 10px;
            background: transparent;
            color: #94a3b8;
            font-family: inherit;
            font-size: 14px;
            font-weight: 600;
            text-align: left;
            cursor: not-allowed;
            opacity: .8;
        }

        .nav-dropdown-schedule {
            display: block;
            margin-top: 2px;
            font-size: 10px;
            font-weight: 700;
            line-height: 1.25;
        }

        .notif-toggle-btn {
            width: 38px;
            height: 38px;
            min-width: 38px;
            border-radius: 999px;
            border: 1px solid rgba(255, 255, 255, 0.42);
            background: rgba(255, 255, 255, 0.14);
            color: #fff;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            cursor: pointer;
            position: relative;
            flex: 0 0 auto;
            overflow: visible;
            transition: all 0.2s ease;
        }

        .notif-toggle-btn:hover,
        .notif-toggle-btn[aria-expanded="true"] {
            background: rgba(255, 255, 255, 0.24);
            border-color: rgba(255, 255, 255, 0.65);
        }

        .notif-toggle-btn svg {
            width: 18px;
            height: 18px;
            display: block;
            flex: 0 0 auto;
            stroke: currentColor;
            fill: none;
            stroke-width: 2;
            stroke-linecap: round;
            stroke-linejoin: round;
        }

        .notif-badge {
            position: absolute;
            top: -4px;
            right: -4px;
            min-width: 18px;
            height: 18px;
            padding: 0 5px;
            border-radius: 999px;
            background: #f59e0b;
            color: #fff;
            font-size: 10px;
            font-weight: 800;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            box-shadow: 0 4px 10px rgba(0, 0, 0, 0.16);
        }

        .notif-badge.is-hidden {
            display: none;
        }

        .notif-dropdown-menu {
            min-width: 320px;
            padding: 0;
            overflow: hidden;
        }

        .notif-fab-wrap {
            position: fixed;
            right: 24px;
            bottom: 84px;
            z-index: 1100;
        }

        .notif-fab {
            width: 54px;
            height: 54px;
            border-radius: 999px;
            border: 2px solid rgba(255, 255, 255, 0.92);
            background: linear-gradient(135deg, #8b0000 0%, #6b0011 100%);
            color: #fff;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            cursor: pointer;
            position: relative;
            box-shadow: 0 16px 28px rgba(107, 0, 17, 0.34);
            transition: transform 0.2s ease, box-shadow 0.2s ease, background 0.2s ease;
        }

        .notif-fab:hover,
        .notif-fab[aria-expanded="true"] {
            transform: translateY(-2px);
            box-shadow: 0 18px 30px rgba(107, 0, 17, 0.4);
            background: linear-gradient(135deg, #9f0712 0%, #7f0014 100%);
        }

        .notif-fab svg {
            width: 22px;
            height: 22px;
            display: block;
            stroke: currentColor;
            fill: none;
            stroke-width: 2;
            stroke-linecap: round;
            stroke-linejoin: round;
            transform-origin: top center;
        }

        .notif-fab:hover svg,
        .notif-fab:focus-visible svg {
            animation: notifBellRing 0.6s ease-in-out;
        }

        .notif-fab .notif-badge {
            top: -6px;
            right: -4px;
        }

        @keyframes notifBellRing {
            0% { transform: rotate(0deg); }
            15% { transform: rotate(14deg); }
            30% { transform: rotate(-12deg); }
            45% { transform: rotate(10deg); }
            60% { transform: rotate(-8deg); }
            75% { transform: rotate(5deg); }
            100% { transform: rotate(0deg); }
        }

        .notif-dropdown-header {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 12px;
            padding: 12px 14px;
            border-bottom: 1px solid #eef2f7;
            background: #fffaf5;
        }

        .notif-dropdown-title {
            font-size: 14px;
            font-weight: 800;
            color: #7c2d12;
        }

        .notif-read-all {
            background: none;
            border: none;
            color: #8b0000;
            font-size: 12px;
            font-weight: 700;
            cursor: pointer;
            padding: 0;
        }

        .notif-read-all:hover {
            text-decoration: underline;
        }

        .notif-dropdown-list {
            max-height: 360px;
            overflow-y: auto;
            background: #fff;
            list-style: none;
            margin: 0;
            padding: 0;
        }

        .notif-dropdown-item {
            display: flex !important;
            gap: 10px;
            align-items: flex-start !important;
            justify-content: flex-start !important;
            padding: 12px 14px !important;
            border-radius: 0 !important;
            border-bottom: 1px solid #f1f5f9;
            position: relative;
        }

        .notif-dropdown-item:last-child {
            border-bottom: none;
        }

        .notif-dropdown-item.unread {
            background: #fffaf0;
        }

        .notif-item-dot {
            width: 8px;
            height: 8px;
            border-radius: 999px;
            background: #8b0000;
            margin-top: 6px;
            flex: 0 0 auto;
        }

        .notif-item-content {
            flex: 1;
            min-width: 0;
        }

        .notif-item-message {
            font-size: 13px;
            line-height: 1.45;
            color: #1f2937;
        }

        .notif-dropdown-item.unread .notif-item-message {
            font-weight: 800;
        }

        .notif-item-time {
            display: block;
            margin-top: 4px;
            font-size: 11px;
            color: #94a3b8;
        }

        /* Hover Effect: Turn Gold */
        .nav-list li a:not(.logout-btn):hover {
            color: #ffc107; /* PUP Gold */
        }

        /* Underline Animation */
        .nav-list li a:not(.logout-btn)::after {
            content: '';
            position: absolute;
            width: 0;
            height: 2px;
            bottom: 0;
            left: 0;
            background-color: #ffc107;
            transition: width 0.3s ease;
        }

        .nav-list li a:not(.logout-btn):hover::after {
            width: 100%;
        }

        /* Active State */
        .nav-list li a.active {
            color: #fff;
            font-weight: 700;
        }

        .nav-list li .theme-toggle-btn,
        .nav-list li .accessibility-toggle-btn,
        .nav-list li .logout-btn {
            margin-left: 12px;
            padding: 8px 16px;
            background: rgba(255, 255, 255, 0.15);
            border: 1px solid rgba(255, 255, 255, 0.45);
            color: #fff;
            border-radius: 20px;
            font-weight: 600;
            font-size: 13px;
            line-height: 1;
            text-decoration: none;
            cursor: pointer;
            transition: all 0.2s ease;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            min-height: 34px;
        }

        .nav-list li .theme-toggle-btn,
        .nav-list li .accessibility-toggle-btn {
            font-family: inherit;
            width: 36px;
            height: 36px;
            min-height: 36px;
            padding: 0;
            border-radius: 50%;
            font-size: 0;
            line-height: 0;
        }

        .nav-list li .theme-toggle-btn svg,
        .nav-list li .accessibility-toggle-btn svg {
            width: 18px;
            height: 18px;
            display: block;
            stroke: currentColor;
            fill: none;
            stroke-width: 2;
            stroke-linecap: round;
            stroke-linejoin: round;
        }

        .nav-list li .logout-btn {
            min-height: 36px;
            padding: 8px 18px;
            min-width: 96px;
            white-space: nowrap;
            opacity: 1;
            gap: 8px;
        }

        .nav-list li .logout-btn svg {
            width: 18px;
            height: 18px;
            flex: 0 0 auto;
            stroke-width: 1.8;
        }

        .nav-list li .theme-toggle-btn:hover,
        .nav-list li .accessibility-toggle-btn:hover,
        .nav-list li .logout-btn:hover {
            background: rgba(255, 255, 255, 0.24);
            border-color: rgba(255, 255, 255, 0.65);
            color: #fff;
            transform: translateY(-1px);
            text-decoration: none;
            opacity: 1;
        }

        .nav-list li .theme-toggle-btn:focus-visible,
        .nav-list li .accessibility-toggle-btn:focus-visible,
        .nav-list li .logout-btn:focus-visible {
            outline: 2px solid #ffc107;
            outline-offset: 2px;
        }

        /* --- 3. MOBILE MENU --- */
        @media (max-width: 768px) {
            .nav-toggle {
                margin-left: auto;
                width: 42px;
                height: 42px;
                padding: 0;
                display: inline-flex;
                flex-direction: column;
                align-items: center;
                justify-content: center;
                gap: 5px;
                border: 0;
                background: transparent;
                color: #ffffff;
                cursor: pointer;
                position: relative;
                z-index: 40;
                transition: color .22s ease, transform .22s ease;
            }

            .nav-toggle:hover,
            .nav-toggle:focus-visible,
            .nav-toggle.is-open {
                color: #facc15;
                outline: none;
            }

            .nav-toggle:hover {
                transform: translateY(-1px);
            }

            .nav-toggle-line {
                width: 20px;
                height: 2px;
                border-radius: 999px;
                background: currentColor;
                transform-origin: center;
                transition: transform .28s ease, opacity .2s ease, width .28s ease;
            }

            .nav-toggle.is-open .nav-toggle-line:nth-child(1) {
                transform: translateY(7px) rotate(45deg);
            }

            .nav-toggle.is-open .nav-toggle-line:nth-child(2) {
                opacity: 0;
                transform: scaleX(.2);
            }

            .nav-toggle.is-open .nav-toggle-line:nth-child(3) {
                transform: translateY(-7px) rotate(-45deg);
            }

            .main-nav {
                width: auto;
            }

            .nav-list {
                display: flex;
                position: absolute;
                top: var(--header-height);
                left: auto;
                right: 12px;
                flex-direction: column;
                align-items: stretch;
                gap: 8px;
                width: min(260px, calc(100vw - 28px));
                padding: 12px;
                background: rgba(255, 255, 255, .97);
                border: 1px solid rgba(127, 29, 45, .16);
                border-radius: 0 0 18px 18px;
                box-shadow: 0 18px 38px rgba(0, 0, 0, 0.18);
                max-height: calc(100vh - var(--header-height) - 10px);
                overflow-y: auto;
                opacity: 0;
                visibility: hidden;
                pointer-events: none;
                transform: translateY(-8px) scale(.98);
                transform-origin: top right;
                transition: opacity .24s ease, transform .28s ease, visibility .24s ease;
            }

            .nav-list.show {
                opacity: 1;
                visibility: visible;
                pointer-events: auto;
                transform: translateY(0) scale(1);
            }

            .nav-list li {
                width: 100%;
            }

            .nav-list li a:not(.logout-btn) {
                color: #1f2937;
                width: 100%;
                display: flex;
                align-items: center;
                justify-content: flex-start;
                padding: 9px 10px;
                border-radius: 10px;
            }

            .nav-list-divider {
                width: 100%;
                height: 1px;
                margin: 4px 0;
                background: #e5e7eb;
            }

            .nav-dropdown-toggle {
                color: #1f2937;
                width: 100%;
                display: flex;
                align-items: center;
                justify-content: space-between;
                padding: 9px 10px;
                border-radius: 10px;
            }

            .nav-dropdown-menu {
                position: static;
                min-width: 100%;
                margin-top: 8px;
                box-shadow: none;
                border-radius: 12px;
                border: 1px solid #e5e7eb;
                background: #f8fafc;
            }

            .nav-dropdown-menu a {
                padding: 10px 12px !important;
            }

            .notif-dropdown-menu {
                min-width: 100%;
            }

            .nav-list li a:not(.logout-btn)::after {
                display: none;
            }

            .nav-list li .theme-toggle-btn,
            .nav-list li .accessibility-toggle-btn {
                margin-left: 0;
                width: 40px;
                height: 40px;
                min-height: 40px;
            }

            .nav-list li .logout-btn {
                margin-left: 0;
                width: 100%;
                justify-content: center;
                background: #7f1d2d;
                border-color: #6b1324;
                color: #ffffff;
            }

            .nav-list li .logout-btn:hover,
            .nav-list li .logout-btn:focus-visible {
                background: #6b1324;
                border-color: #5a0f1d;
                color: #ffffff;
            }

            .nav-list li .logout-btn svg {
                color: #ffffff;
                stroke: currentColor;
            }

            .standalone-logout-item {
                display: none;
                opacity: 0;
                transform: translateY(-6px);
                transition: opacity .2s ease, transform .24s ease;
            }

            .nav-dropdown.is-open + .standalone-logout-item {
                display: flex;
                opacity: 1;
                transform: translateY(0);
            }

            main table {
                display: block;
                width: 100%;
                overflow-x: auto;
                white-space: nowrap;
                -webkit-overflow-scrolling: touch;
            }
        }

        @media (max-width: 1024px) {
            .site-header .header-inner {
                padding-left: 22px;
                padding-right: 22px;
            }
        }

        @media (max-width: 520px) {
            .site-header .header-inner {
                padding-left: 14px;
                padding-right: 14px;
            }
        }
    </style>

    @stack('styles')
    <style>
        html[data-theme="dark"] {
            color-scheme: dark;
            --primary: #a31b1b;
            --header-bg: #3f0b15;
            --bg-color: #0f131a;
            --card-bg: #171d27;
            --text-main: #e5eaf3;
            --text-light: #a9b4c4;
            --border: #2f3847;
            --focus-ring: rgba(163, 27, 27, 0.32);
        }

        html[data-theme="dark"] body {
            background: var(--bg-color);
            color: var(--text-main);
        }

        html[data-theme="dark"] .site-header {
            border-bottom-color: rgba(255, 255, 255, 0.1);
            box-shadow: 0 8px 20px rgba(0, 0, 0, 0.35);
        }

        html[data-theme="dark"] .nav-list li a:not(.logout-btn) {
            color: rgba(243, 246, 252, 0.92);
        }

        html[data-theme="dark"] .nav-list li a:not(.logout-btn):hover {
            color: #ffd166;
        }

        html[data-theme="dark"] .nav-list li .theme-toggle-btn,
        html[data-theme="dark"] .nav-list li .accessibility-toggle-btn,
        html[data-theme="dark"] .nav-list li .logout-btn {
            background: rgba(255, 255, 255, 0.12);
            border-color: rgba(255, 255, 255, 0.4);
            color: #f8fafc;
        }

        html[data-theme="dark"] .nav-list li .theme-toggle-btn:hover,
        html[data-theme="dark"] .nav-list li .accessibility-toggle-btn:hover,
        html[data-theme="dark"] .nav-list li .logout-btn:hover {
            background: rgba(255, 255, 255, 0.22);
        }

        .nav-list li .student-logout-btn {
            box-shadow:
                0 3px 2px rgba(72, 0, 14, 0.22),
                0 6px 10px rgba(45, 0, 10, 0.18),
                inset 0 1px 0 rgba(255, 255, 255, 0.24),
                inset 0 -2px 3px rgba(56, 0, 12, 0.16);
            transition:
                background 0.2s ease,
                border-color 0.2s ease,
                color 0.2s ease,
                transform 0.2s ease,
                box-shadow 0.2s ease;
        }

        .nav-list li .student-logout-btn:hover {
            box-shadow:
                0 4px 3px rgba(72, 0, 14, 0.26),
                0 7px 12px rgba(45, 0, 10, 0.24),
                0 -2px 4px rgba(255, 255, 255, 0.14),
                inset 0 1px 2px rgba(255, 255, 255, 0.28),
                inset 0 -3px 4px rgba(56, 0, 12, 0.2);
        }

        .nav-list li .student-logout-btn:active {
            transform: translateY(1px);
            box-shadow:
                0 2px 3px rgba(45, 0, 10, 0.2),
                inset 0 2px 5px rgba(40, 0, 10, 0.42),
                inset 0 0 14px rgba(76, 0, 18, 0.28);
        }

        .nav-list li .student-logout-btn:focus-visible {
            box-shadow:
                0 4px 3px rgba(72, 0, 14, 0.24),
                0 7px 12px rgba(45, 0, 10, 0.22),
                inset 0 1px 2px rgba(255, 255, 255, 0.26),
                inset 0 -3px 4px rgba(56, 0, 12, 0.18);
        }

        .nav-dropdown-menu .desktop-account-logout {
            position: relative;
            overflow: hidden;
            margin-top: 8px;
            background: #7f1d2d !important;
            border: 1px solid rgba(127, 29, 45, .88);
            color: #ffffff !important;
            box-shadow:
                0 8px 16px rgba(45, 0, 10, .18),
                inset 0 1px 0 rgba(255, 255, 255, .18);
        }

        .nav-dropdown-menu .desktop-account-logout::before {
            content: "";
            position: absolute;
            inset: -45% auto -45% -70%;
            width: 56%;
            transform: skewX(-18deg);
            background: linear-gradient(90deg, transparent, rgba(255, 243, 166, .7), transparent);
            opacity: 0;
            pointer-events: none;
        }

        .nav-dropdown-menu .desktop-account-logout:hover,
        .nav-dropdown-menu .desktop-account-logout:focus-visible {
            background: #facc15 !important;
            border-color: rgba(250, 204, 21, .95);
            color: #7f1d2d !important;
            box-shadow:
                0 10px 20px rgba(127, 29, 45, .18),
                0 0 18px rgba(250, 204, 21, .26);
        }

        .nav-dropdown-menu .desktop-account-logout:hover::before,
        .nav-dropdown-menu .desktop-account-logout:focus-visible::before {
            opacity: 1;
            animation: accountLogoutSweep .78s ease forwards;
        }

        .nav-dropdown-menu .desktop-account-logout svg {
            width: 17px;
            height: 17px;
            stroke-width: 1.9;
        }

        .desktop-logout-item {
            display: none;
        }

        @keyframes accountLogoutSweep {
            from { transform: translateX(0) skewX(-18deg); }
            to { transform: translateX(360%) skewX(-18deg); }
        }

        @media (min-width: 769px) {
            .desktop-logout-item {
                display: block;
            }

            .standalone-logout-item {
                display: none;
            }
        }

        html[data-theme="dark"] .nav-list-divider {
            background: rgba(255, 255, 255, 0.16);
        }

        html[data-theme="dark"] .nav-dropdown-menu {
            background: #171d27;
            border-color: #2f3847;
            box-shadow: 0 18px 36px rgba(0, 0, 0, 0.38);
        }

        html[data-theme="dark"] .nav-dropdown-menu a {
            color: #e5eaf3 !important;
        }

        html[data-theme="dark"] .nav-dropdown-menu a:hover,
        html[data-theme="dark"] .nav-dropdown-menu a.active {
            background: rgba(139, 0, 0, 0.22);
            color: #ffd166 !important;
        }

        html[data-theme="dark"] .nav-dropdown-menu .desktop-account-logout {
            background: #7f1d2d !important;
            border-color: rgba(250, 204, 21, 0.22);
            color: #ffffff !important;
        }

        html[data-theme="dark"] .nav-dropdown-menu .desktop-account-logout:hover,
        html[data-theme="dark"] .nav-dropdown-menu .desktop-account-logout:focus-visible {
            background: #facc15 !important;
            border-color: #facc15;
            color: #7f1d2d !important;
        }

        html[data-theme="dark"] .nav-dropdown-menu .nav-dropdown-disabled {
            color: #8290a5;
        }

        html[data-theme="dark"] .notif-toggle-btn {
            background: rgba(255, 255, 255, 0.12);
            border-color: rgba(255, 255, 255, 0.4);
            color: #f8fafc;
        }

        html[data-theme="dark"] .notif-dropdown-header {
            background: rgba(139, 0, 0, 0.18);
            border-bottom-color: #2f3847;
        }

        html[data-theme="dark"] .notif-dropdown-title,
        html[data-theme="dark"] .notif-read-all {
            color: #ffd7b5;
        }

        html[data-theme="dark"] .notif-dropdown-list {
            background: #171d27;
        }

        html[data-theme="dark"] .notif-dropdown-item {
            border-bottom-color: #253041;
        }

        html[data-theme="dark"] .notif-dropdown-item.unread {
            background: rgba(139, 0, 0, 0.14);
        }

        html[data-theme="dark"] .notif-item-message {
            color: #e5eaf3;
        }

        html[data-theme="dark"] h1,
        html[data-theme="dark"] h2,
        html[data-theme="dark"] h3,
        html[data-theme="dark"] h4,
        html[data-theme="dark"] h5,
        html[data-theme="dark"] h6,
        html[data-theme="dark"] .page-title,
        html[data-theme="dark"] .form-section-title,
        html[data-theme="dark"] .info-title,
        html[data-theme="dark"] .section-title,
        html[data-theme="dark"] .widget-title,
        html[data-theme="dark"] .category-title,
        html[data-theme="dark"] .apt-service,
        html[data-theme="dark"] .appt-service,
        html[data-theme="dark"] .comment-body h4,
        html[data-theme="dark"] #about h2 {
            color: #f2f6fd !important;
        }

        html[data-theme="dark"] .page-subtitle,
        html[data-theme="dark"] .small,
        html[data-theme="dark"] .input-label,
        html[data-theme="dark"] .apt-details,
        html[data-theme="dark"] .apt-time,
        html[data-theme="dark"] .appt-time,
        html[data-theme="dark"] .faq-answer,
        html[data-theme="dark"] .comment-meta,
        html[data-theme="dark"] .comment-body p,
        html[data-theme="dark"] .notif-text,
        html[data-theme="dark"] .notif-time,
        html[data-theme="dark"] .scan-helper,
        html[data-theme="dark"] #about p {
            color: var(--text-light) !important;
        }

        html[data-theme="dark"] .card,
        html[data-theme="dark"] .booking-card,
        html[data-theme="dark"] .booking-info-section,
        html[data-theme="dark"] .info-card,
        html[data-theme="dark"] .appt-item,
        html[data-theme="dark"] .card-history,
        html[data-theme="dark"] .apt-card,
        html[data-theme="dark"] .appt-card,
        html[data-theme="dark"] .widget-card,
        html[data-theme="dark"] .barcode-status-card,
        html[data-theme="dark"] .student-info-box,
        html[data-theme="dark"] .barcode-card,
        html[data-theme="dark"] .category-card,
        html[data-theme="dark"] .sidebar-widget,
        html[data-theme="dark"] .comment-card,
        html[data-theme="dark"] details[open] {
            background: var(--card-bg) !important;
            border-color: var(--border) !important;
            color: var(--text-main) !important;
            box-shadow: 0 12px 28px rgba(0, 0, 0, 0.25);
        }

        html[data-theme="dark"] .welcome {
            background: #111822 !important;
        }

        html[data-theme="dark"] .comments-section {
            background: #0d141c !important;
        }

        html[data-theme="dark"] .comment-chip {
            background: rgba(203, 213, 225, 0.15) !important;
            color: #dbe5f4 !important;
        }

        html[data-theme="dark"] .booking-form-section,
        html[data-theme="dark"] .category-header,
        html[data-theme="dark"] .notif-item,
        html[data-theme="dark"] .stat-row,
        html[data-theme="dark"] .info-title {
            border-color: var(--border) !important;
        }

        html[data-theme="dark"] .apt-notes,
        html[data-theme="dark"] .appt-notes,
        html[data-theme="dark"] .note-widget {
            background: #111a24 !important;
            border-color: #3b4657 !important;
            color: #d2dbe8 !important;
        }

        html[data-theme="dark"] .note-header {
            color: #f0be6a !important;
        }

        html[data-theme="dark"] .barcode-label,
        html[data-theme="dark"] .stat-label {
            color: #9aa6ba !important;
        }

        html[data-theme="dark"] .barcode-value,
        html[data-theme="dark"] .stat-val {
            color: #f3f7ff !important;
        }

        html[data-theme="dark"] .hero-search-input,
        html[data-theme="dark"] input,
        html[data-theme="dark"] select,
        html[data-theme="dark"] textarea,
        html[data-theme="dark"] .form-control,
        html[data-theme="dark"] .barcode-input {
            background: #0f161f !important;
            color: #e9eef8 !important;
            border-color: #3a4556 !important;
        }

        html[data-theme="dark"] input::placeholder,
        html[data-theme="dark"] textarea::placeholder {
            color: #8e9aaf !important;
        }

        html[data-theme="dark"] .form-control[readonly],
        html[data-theme="dark"] .form-control:disabled {
            background: #1a2432 !important;
            color: #9ba7ba !important;
            border-color: #334053 !important;
        }

        html[data-theme="dark"] .btn.ghost,
        html[data-theme="dark"] .btn-outline {
            color: #f6d3d3 !important;
            border-color: #b45858 !important;
            background: transparent !important;
        }

        html[data-theme="dark"] .btn-outline:hover,
        html[data-theme="dark"] .btn.ghost:hover {
            background: rgba(139, 0, 0, 0.22) !important;
        }

        html[data-theme="dark"] details {
            border-color: var(--border) !important;
        }

        html[data-theme="dark"] summary {
            color: #d7e1ee !important;
        }

        html[data-theme="dark"] details[open] summary {
            color: #ffd166 !important;
        }

        html[data-theme="dark"] .empty-state {
            color: #93a2b6 !important;
        }

        html[data-theme="dark"] #about a {
            color: #ffd8d8 !important;
            border-color: #bb5959 !important;
            background: rgba(139, 0, 0, 0.18) !important;
        }

        html[data-theme="dark"] #about a:hover {
            background: rgba(139, 0, 0, 0.28) !important;
        }

        html[data-theme="dark"] #reader {
            border-color: #4a5568 !important;
        }

        html[data-theme="dark"] #barcodeModal > div {
            background: #181f2a !important;
            color: #e5ecf7 !important;
            border: 1px solid #2f3a4a;
        }

        html[data-theme="dark"] #barcodeModal h2 {
            color: #f5f8ff !important;
        }

        html[data-theme="dark"] #barcodeModal p {
            color: #b5c1d3 !important;
        }

        html[data-theme="dark"] #barcodeModal > div > div:first-child {
            background: rgba(139, 0, 0, 0.2) !important;
        }

        html[data-theme="dark"] #barcodeModal button[onclick="closeBarcodeModal()"] {
            background: #101723 !important;
            color: #d4deec !important;
        }

        .clinic-availability-banner {
            position: relative;
            z-index: 40;
            background: #7f1d2d;
            color: #ffffff;
            border-bottom: 3px solid #facc15;
            box-shadow: 0 10px 24px rgba(70, 8, 20, 0.18);
        }

        .clinic-availability-banner[hidden] { display: none; }

        .clinic-availability-banner-inner {
            width: min(1180px, calc(100% - 32px));
            min-height: 64px;
            margin: 0 auto;
            display: flex;
            align-items: center;
            gap: 14px;
            padding: 10px 0;
        }

        .clinic-availability-banner-icon {
            width: 38px;
            height: 38px;
            flex: 0 0 38px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            border-radius: 10px;
            background: #facc15;
            color: #64101d;
            font-weight: 900;
        }

        .clinic-availability-banner-copy {
            min-width: 0;
            flex: 1;
        }

        .clinic-availability-banner-copy strong {
            display: block;
            margin-bottom: 2px;
            color: #ffffff;
            font-size: 14px;
        }

        .clinic-availability-banner-copy span {
            color: rgba(255,255,255,0.88);
            font-size: 12px;
            line-height: 1.45;
        }

        .clinic-availability-banner-dismiss {
            width: 34px;
            height: 34px;
            flex: 0 0 34px;
            border: 1px solid rgba(250,204,21,0.7);
            border-radius: 50%;
            background: transparent;
            color: #ffffff;
            cursor: pointer;
            font-size: 20px;
            line-height: 1;
            transition: background .18s ease, color .18s ease;
        }

        .clinic-availability-banner-dismiss:hover {
            background: #facc15;
            color: #64101d;
        }

        @media (max-width: 768px) {
            .notif-fab-wrap {
                right: 18px;
                bottom: 76px;
            }

            .notif-fab {
                width: 50px;
                height: 50px;
            }

            html[data-theme="dark"] .nav-list {
                background: #1a1018 !important;
                border: 1px solid rgba(255, 255, 255, 0.12);
                box-shadow: 0 14px 28px rgba(0, 0, 0, 0.5);
            }

            html[data-theme="dark"] .nav-list li a:not(.logout-btn) {
                color: #f3f4f6 !important;
            }

            html[data-theme="dark"] .nav-dropdown-toggle {
                color: #f3f4f6 !important;
            }

            .nav-list {
                height: max-content;
                min-height: 0;
            }

            .nav-dropdown-menu {
                width: 100%;
                max-height: 0;
                margin-top: 0;
                padding: 0 10px;
                overflow: hidden;
                border-width: 0;
                box-sizing: border-box;
                opacity: 0;
                visibility: hidden;
                pointer-events: none;
                transform: none;
                transition:
                    max-height .3s cubic-bezier(.2, .8, .2, 1),
                    margin-top .3s ease,
                    padding .3s ease,
                    border-width .18s ease,
                    opacity .18s ease,
                    visibility 0s linear .3s;
            }

            .nav-dropdown.is-open .nav-dropdown-menu {
                max-height: 430px;
                margin-top: 8px;
                padding: 10px;
                border-width: 1px;
                opacity: 1;
                visibility: visible;
                pointer-events: auto;
                transform: none;
                transition-delay: 0s;
            }

            .standalone-logout-item,
            .nav-dropdown.is-open + .standalone-logout-item {
                display: flex;
                margin-top: 0;
                opacity: 1;
                transform: none;
            }

            .nav-list li .student-logout-btn,
            html[data-theme="dark"] .nav-list li .student-logout-btn {
                min-height: 42px;
                border-radius: 8px;
                background: #7f1d2d !important;
                border-color: #681424 !important;
                color: #ffffff !important;
            }

            .nav-list li .student-logout-btn:hover,
            .nav-list li .student-logout-btn:focus-visible,
            html[data-theme="dark"] .nav-list li .student-logout-btn:hover,
            html[data-theme="dark"] .nav-list li .student-logout-btn:focus-visible {
                background: #681424 !important;
                border-color: #560e1c !important;
                color: #ffffff !important;
            }

            .nav-list li a:not(.logout-btn):hover,
            .nav-list li a:not(.logout-btn):focus-visible,
            .nav-dropdown-toggle:hover,
            .nav-dropdown-toggle:focus-visible {
                background: transparent !important;
                color: #111827 !important;
                outline: none;
            }

            html[data-theme="dark"] .nav-list li a:not(.logout-btn):hover,
            html[data-theme="dark"] .nav-list li a:not(.logout-btn):focus-visible,
            html[data-theme="dark"] .nav-dropdown-toggle:hover,
            html[data-theme="dark"] .nav-dropdown-toggle:focus-visible {
                background: transparent !important;
                color: #f8fafc !important;
                outline: none;
            }

            .nav-list li a:not(.logout-btn):hover .nav-link-content > span:last-child,
            .nav-list li a:not(.logout-btn):focus-visible .nav-link-content > span:last-child,
            .nav-dropdown-toggle:hover .nav-link-content > span:last-child,
            .nav-dropdown-toggle:focus-visible .nav-link-content > span:last-child,
            .nav-dropdown-menu a:hover .nav-dropdown-link-content > span:last-child,
            .nav-dropdown-menu a:focus-visible .nav-dropdown-link-content > span:last-child {
                text-decoration-line: underline;
                text-decoration-color: #facc15;
                text-decoration-thickness: 2px;
                text-underline-offset: 4px;
            }

            .nav-list li a:not(.logout-btn).active,
            .nav-list .nav-dropdown > .nav-dropdown-toggle.active,
            .nav-list .nav-dropdown > .nav-dropdown-toggle[aria-expanded="true"],
            .nav-list .nav-dropdown.is-open > .nav-dropdown-toggle {
                background: transparent !important;
                color: #111827 !important;
                font-weight: 800 !important;
            }

            .nav-list .nav-dropdown > .nav-dropdown-toggle.active .nav-link-content,
            .nav-list .nav-dropdown > .nav-dropdown-toggle[aria-expanded="true"] .nav-link-content,
            .nav-list .nav-dropdown.is-open > .nav-dropdown-toggle .nav-link-content,
            .nav-list .nav-dropdown > .nav-dropdown-toggle.active .nav-link-icon,
            .nav-list .nav-dropdown > .nav-dropdown-toggle[aria-expanded="true"] .nav-link-icon,
            .nav-list .nav-dropdown.is-open > .nav-dropdown-toggle .nav-link-icon,
            .nav-list .nav-dropdown > .nav-dropdown-toggle.active .nav-dropdown-caret,
            .nav-list .nav-dropdown > .nav-dropdown-toggle[aria-expanded="true"] .nav-dropdown-caret,
            .nav-list .nav-dropdown.is-open > .nav-dropdown-toggle .nav-dropdown-caret {
                color: inherit !important;
            }

            .nav-list .nav-dropdown > .nav-dropdown-toggle.active .nav-link-content > span:last-child,
            .nav-list .nav-dropdown > .nav-dropdown-toggle[aria-expanded="true"] .nav-link-content > span:last-child,
            .nav-list .nav-dropdown.is-open > .nav-dropdown-toggle .nav-link-content > span:last-child {
                font-weight: 800;
                text-decoration: none !important;
            }

            html[data-theme="dark"] .nav-list li a:not(.logout-btn).active,
            html[data-theme="dark"] .nav-list .nav-dropdown > .nav-dropdown-toggle.active,
            html[data-theme="dark"] .nav-list .nav-dropdown > .nav-dropdown-toggle[aria-expanded="true"],
            html[data-theme="dark"] .nav-list .nav-dropdown.is-open > .nav-dropdown-toggle {
                background: transparent !important;
                color: #f8fafc !important;
            }
        }
    </style>
</head>
<body>
    <header class="site-header">
        <div class="container header-inner">
            <div class="header-left">
                <a class="brand-link" href="{{ url('/student/home') }}">
                    <span class="brand-badges">
                        <img src="{{ asset('images/pup_logo.png') }}" alt="PUP Logo" class="brand-img">
                        <img src="{{ asset('images/clinic_logo_transparent.png') }}?v={{ filemtime(public_path('images/clinic_logo_transparent.png')) }}" alt="Clinic Logo" class="brand-img brand-img--clinic">
                    </span>
                    <span class="brand-text">
                        <span class="brand-title">PUP TAGUIG</span>
                        <span class="brand-subtitle">MEDICAL CLINIC</span>
                    </span>
                </a>
            </div>

            <button class="nav-toggle" type="button" aria-label="Open menu" aria-expanded="false">
                <span class="nav-toggle-line" aria-hidden="true"></span>
                <span class="nav-toggle-line" aria-hidden="true"></span>
                <span class="nav-toggle-line" aria-hidden="true"></span>
            </button>

            <nav id="main-menu" class="main-nav">
                @php
                    $studentLayoutUser = Auth::guard('student')->user();
                    $isStudentAssistantPortalUser = $studentLayoutUser?->isStudentAssistant() ?? false;
                    $isMyAccountSection = Request::is('student/account') || Request::is('student/history') || Request::is('student/barcode-register');
                    $studentAllNotifications = collect($notifications ?? [])->values();
                    $studentUnreadNotifications = $studentAllNotifications
                        ->filter(fn ($notification) => !empty($notification['is_unread']))
                        ->values();
                    $notificationCount = $studentUnreadNotifications->count();
                    $studentNotificationHistory = $studentAllNotifications->take(20)->values();
                @endphp
                <ul class="nav-list">
                    <li>
                        <a href="{{ url('/student/home') }}" class="{{ Request::is('student/home') ? 'active' : '' }}" data-student-nav="home">
                            <span class="nav-link-content">
                                <x-outline-icon name="home" class="nav-link-icon" />
                                <span>Home</span>
                            </span>
                        </a>
                    </li>
                    <li>
                        <a href="{{ url('/student/booking') }}" class="{{ Request::is('student/booking') ? 'active' : '' }}">
                            <span class="nav-link-content">
                                <x-outline-icon name="calendar-days" class="nav-link-icon" />
                                <span>Appointments</span>
                            </span>
                        </a>
                    </li>
                    <li>
                        <a href="{{ url('/student/home') }}#about" data-student-nav="about">
                            <span class="nav-link-content">
                                <x-outline-icon name="information-circle" class="nav-link-icon" />
                                <span>About Us</span>
                            </span>
                        </a>
                    </li>
                    <li>
                        <a href="{{ url('/student/faq') }}" class="{{ Request::is('student/faq') ? 'active' : '' }}">
                            <span class="nav-link-content">
                                <x-outline-icon name="question-mark-circle" class="nav-link-icon" />
                                <span>FAQs</span>
                            </span>
                        </a>
                    </li>
                    <li class="nav-list-divider" aria-hidden="true"></li>
                    @auth('student')
                    <li class="nav-dropdown" data-nav-dropdown>
                        <button
                            type="button"
                            class="nav-dropdown-toggle {{ $isMyAccountSection ? 'active' : '' }}"
                            aria-expanded="false"
                            aria-haspopup="true"
                        >
                            <span class="nav-link-content">
                                <x-outline-icon name="user-circle" class="nav-link-icon" />
                                <span>My Account</span>
                            </span>
                            <span class="nav-dropdown-caret" aria-hidden="true"></span>
                        </button>
                        <ul class="nav-dropdown-menu">
                            <li>
                                <a href="{{ url('/student/account?view=profile') }}" class="{{ Request::is('student/account') && request('view', 'profile') === 'profile' ? 'active' : '' }}">
                                    <span class="nav-dropdown-link-content">
                                        <x-outline-icon name="user-circle" class="nav-dropdown-link-icon" />
                                        <span>Profile</span>
                                    </span>
                                </a>
                            </li>
                            <li>
                                <a href="{{ url('/student/account?view=notifications') }}" class="{{ Request::is('student/account') && request('view') === 'notifications' ? 'active' : '' }}">
                                    <span class="nav-dropdown-link-content">
                                        <x-outline-icon name="bell" class="nav-dropdown-link-icon" />
                                        <span>Notifications</span>
                                    </span>
                                    @if($notificationCount > 0)
                                        <span class="nav-dropdown-link-accessory">({{ $notificationCount }})</span>
                                    @endif
                                </a>
                            </li>
                            <li>
                                <a href="{{ url('/student/history') }}" class="{{ Request::is('student/history') ? 'active' : '' }}">
                                    <span class="nav-dropdown-link-content">
                                        <x-outline-icon name="clock" class="nav-dropdown-link-icon" />
                                        <span>Appointment History</span>
                                    </span>
                                </a>
                            </li>
                            <li>
                                <a href="{{ url('/student/account?view=health-record') }}" class="{{ Request::is('student/account') && request('view') === 'health-record' ? 'active' : '' }}">
                                    <span class="nav-dropdown-link-content">
                                        <x-outline-icon name="document-text" class="nav-dropdown-link-icon" />
                                        <span>Health Record</span>
                                    </span>
                                </a>
                            </li>
                            @if($isStudentAssistantPortalUser)
                                <li>
                                    @if($studentAssistantAdminAvailable)
                                        <a href="{{ route('assistant.enter-admin') }}">
                                            <span class="nav-dropdown-link-content">
                                                <x-outline-icon name="arrows-right-left" class="nav-dropdown-link-icon" />
                                                <span>Switch to Admin Workspace</span>
                                            </span>
                                        </a>
                                    @else
                                        <button type="button" class="nav-dropdown-disabled" disabled aria-disabled="true">
                                            <span class="nav-dropdown-link-content">
                                                <x-outline-icon name="arrows-right-left" class="nav-dropdown-link-icon" />
                                                <span>
                                                    Switch to Admin Workspace
                                                    <small class="nav-dropdown-schedule">Available {{ $studentAssistantHoursLabel ?? 'Mon-Fri, 8:00 AM - 5:00 PM' }}</small>
                                                </span>
                                            </span>
                                        </button>
                                    @endif
                                </li>
                            @endif
                            <li class="desktop-logout-item">
                                <a href="#" class="desktop-account-logout"
                                onclick="event.preventDefault(); document.getElementById('logout-form').submit();">
                                    <span class="nav-dropdown-link-content">
                                        <x-outline-icon name="arrow-left-on-rectangle" class="nav-dropdown-link-icon" />
                                        <span>Logout</span>
                                    </span>
                                </a>
                            </li>
                        </ul>
                    </li>
                    <li class="standalone-logout-item">
                        <a href="#" class="logout-btn student-logout-btn"
                        onclick="event.preventDefault(); document.getElementById('logout-form').submit();">
                            <x-outline-icon name="arrow-left-on-rectangle" />
                            <span>Logout</span>
                        </a>

                        <form id="logout-form" action="{{ route('logout') }}" method="POST" style="display: none;">
                            @csrf
                            <input type="hidden" name="portal_guard" value="student">
                        </form>
                    </li>
                    @else
                    <li>
                        <a href="{{ route('login.portal') }}" class="logout-btn">
                            <svg viewBox="0 0 24 24" fill="none" aria-hidden="true">
                                <path d="M10 17l5-5-5-5" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round"/>
                                <path d="M15 12H4" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round"/>
                                <path d="M20 4v16" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round"/>
                            </svg>
                            <span>Log In via One Portal</span>
                        </a>
                    </li>
                    @endauth
                    
                </ul>
            </nav>
        </div>
    </header>

    @if(!empty($clinicClosure))
        @php
            $closureBannerKey = 'clinic-closure-' . sha1(
                ($clinicClosure['reason'] ?? '')
                . '|'
                . optional($clinicClosure['updated_at'] ?? null)->timestamp
            );
        @endphp
        <aside
            class="clinic-availability-banner"
            id="clinicAvailabilityBanner"
            data-dismiss-key="{{ $closureBannerKey }}"
            aria-label="Clinic availability advisory"
        >
            <div class="clinic-availability-banner-inner">
                <span class="clinic-availability-banner-icon" aria-hidden="true">!</span>
                <div class="clinic-availability-banner-copy">
                    <strong>{{ $clinicClosure['reason'] }}</strong>
                    <span>
                        {{ $clinicClosure['message'] }}
                        @if(!empty($clinicClosure['ends_at']))
                            Expected reopening: {{ $clinicClosure['ends_at']->format('M d, Y g:i A') }}.
                        @endif
                    </span>
                </div>
                <button type="button" class="clinic-availability-banner-dismiss" id="clinicAvailabilityBannerDismiss" aria-label="Dismiss advisory">&times;</button>
            </div>
        </aside>
    @endif

    @php
        $studentToastMessage = Request::is('student/*')
            ? (session('error') ?: session('success') ?: ($errors->any() ? $errors->first() : null))
            : null;
        $studentToastType = (session('error') || $errors->any()) ? 'error' : 'success';
        $studentToastTitle = $studentToastType === 'error' ? 'Error message' : 'Success message';
    @endphp
    <div class="student-toast-stack" data-student-toast-stack aria-live="polite" aria-atomic="true">
        @if($studentToastMessage)
            <div class="student-toast is-{{ $studentToastType }}" role="{{ $studentToastType === 'error' ? 'alert' : 'status' }}" data-student-toast>
                <span class="student-toast-icon" aria-hidden="true">
                    @if($studentToastType === 'error')
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v4m0 4h.01M10.29 3.86 1.82 18a2 2 0 0 0 1.71 3h16.94a2 2 0 0 0 1.71-3L13.71 3.86a2 2 0 0 0-3.42 0Z" />
                        </svg>
                    @else
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75 11.25 15 15 9.75M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z" />
                        </svg>
                    @endif
                </span>
                <span>
                    <strong class="student-toast-title">{{ $studentToastTitle }}</strong>
                    <span class="student-toast-message">{{ $studentToastMessage }}</span>
                </span>
                <button type="button" class="student-toast-close" data-student-toast-close aria-label="Dismiss message">
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M6 18 18 6M6 6l12 12" />
                    </svg>
                </button>
            </div>
        @endif
    </div>

    <main>
        @yield('content')
    </main>

    @include('partials.post_login_terms_gate')
    @include('partials.system_footer')

    <div class="student-quick-actions-wrap student-quick-actions-fab-wrap" data-nav-dropdown>
        <button
            type="button"
            class="student-quick-actions-toggle"
            aria-expanded="false"
            aria-haspopup="true"
            aria-label="Open quick actions"
            title="Quick actions"
        >
            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" aria-hidden="true" focusable="false">
                <path stroke-linecap="round" stroke-linejoin="round" d="M12 5.25v13.5M5.25 12h13.5" />
            </svg>
            @if($notificationCount > 0)
                <span class="student-quick-actions-badge">{{ $notificationCount }}</span>
            @endif
        </button>

        <div class="student-quick-actions-panel">
            <div class="student-quick-action-item is-accessibility">
                <button type="button" id="studentAccessibilityLaunch" class="student-quick-action-btn student-accessibility-launch" aria-label="Accessibility menu" title="Accessibility">
                    <x-outline-icon name="accessibility-person" />
                </button>
                <span class="student-quick-action-tooltip">Accessibility</span>
            </div>

            <div class="student-quick-action-item is-theme">
                <button type="button" id="themeToggleBtn" class="student-quick-action-btn" aria-pressed="false" aria-label="Theme mode" title="Theme mode">
                    <x-outline-icon name="sun" />
                </button>
                <span class="student-quick-action-tooltip">Theme Mode</span>
            </div>

            <div class="student-quick-action-item is-notifications">
                <button type="button" id="studentNotifToggleBtn" class="student-quick-action-btn student-quick-action-bell" aria-label="Notifications" aria-expanded="false">
                    <x-outline-icon name="bell" />
                    @if($notificationCount > 0)
                        <span class="student-quick-actions-badge">{{ $notificationCount }}</span>
                    @endif
                </button>
                <span class="student-quick-action-tooltip">Notifications</span>
            </div>
        </div>

        <section class="student-notif-panel" id="studentNotifPanel" aria-live="polite">
            <div class="student-notif-head">
                <div>
                    <p class="student-notif-title">Notifications</p>
                    <p class="student-notif-subtitle">Appointment and health updates.</p>
                </div>
                <div class="student-notif-actions">
                    <button type="button" class="student-notif-back-btn" id="studentNotifBackBtn" aria-label="Back to new notifications">
                        <x-outline-icon name="chevron-right" style="transform: rotate(180deg);" />
                        Back
                    </button>
                    <button type="button" class="student-notif-actions-toggle" id="studentNotifActionsToggle" aria-label="Notification actions" aria-expanded="false">
                        <x-outline-icon name="bars-3" />
                    </button>
                    <div class="student-notif-actions-menu" id="studentNotifActionsMenu">
                        <form method="POST" action="{{ route('student.notifications.read_all') }}">
                            @csrf
                            <button type="submit" class="student-notif-actions-submit" {{ $studentUnreadNotifications->isEmpty() ? 'disabled' : '' }}>
                                <x-outline-icon name="check" />
                                Mark all as read
                            </button>
                        </form>
                        <button type="button" class="student-notif-actions-submit" id="studentNotifHistoryBtn">
                            <x-outline-icon name="clock" />
                            Notification history
                        </button>
                    </div>
                    <button type="button" class="student-notif-close" id="studentNotifCloseBtn" aria-label="Close notifications">
                        <x-outline-icon name="x-mark" />
                    </button>
                </div>
            </div>

            <div class="student-notif-section" id="studentNotifUnreadSection">
                <div class="student-notif-list">
                    @forelse($studentUnreadNotifications as $notification)
                        <article class="student-notif-item is-unread">
                            <a href="{{ route('student.notifications.open', ['notificationId' => $notification['id']]) }}" class="student-notif-item-link">
                                <span class="student-notif-dot" aria-hidden="true"></span>
                                <span class="student-notif-content">
                                    <span class="student-notif-item-title">
                                        {{ !empty($notification['announcement_id'])
                                            ? ($notification['title'] ?? 'Clinic Announcement')
                                            : ($notification['message'] ?? 'Notification available.') }}
                                    </span>
                                    <span class="student-notif-item-time">{{ $notification['time'] ?? 'Just now' }}</span>
                                </span>
                            </a>
                        </article>
                    @empty
                        <div class="student-notif-empty">
                            <p class="student-notif-empty-title">No new notifications</p>
                            <p class="student-notif-empty-copy">You're all caught up for now.</p>
                        </div>
                    @endforelse
                </div>
            </div>

            <div class="student-notif-section is-hidden" id="studentNotifHistorySection">
                <div class="student-notif-list">
                    @forelse($studentNotificationHistory as $notification)
                        <article class="student-notif-item {{ !empty($notification['is_unread']) ? 'is-unread' : '' }}">
                            <a href="{{ route('student.notifications.open', ['notificationId' => $notification['id']]) }}" class="student-notif-item-link">
                                <span class="student-notif-dot {{ !empty($notification['is_unread']) ? '' : 'is-read' }}" aria-hidden="true"></span>
                                <span class="student-notif-content">
                                    <span class="student-notif-item-title">
                                        {{ !empty($notification['announcement_id'])
                                            ? ($notification['title'] ?? 'Clinic Announcement')
                                            : ($notification['message'] ?? 'Notification available.') }}
                                        @if(empty($notification['is_unread']))
                                            <span class="student-notif-chip">Read</span>
                                        @endif
                                    </span>
                                    <span class="student-notif-item-time">{{ $notification['time'] ?? 'Just now' }}</span>
                                </span>
                            </a>
                        </article>
                    @empty
                        <div class="student-notif-empty">
                            <p class="student-notif-empty-title">No notification history yet</p>
                            <p class="student-notif-empty-copy">As notifications come in, they will appear here.</p>
                        </div>
                    @endforelse
                </div>
            </div>
        </section>
    </div>

    @if(
        Request::is('student/account') ||
        Request::is('student/booking') ||
        Request::is('student/faq') ||
        Request::is('student/health-form')
    )
        @include('partials.student_voice_input_support')
    @endif

    @stack('scripts')
    
    <script>
        (function() {
            const navToggle = document.querySelector('.nav-toggle');
            const navList = document.querySelector('.nav-list');
            const navDropdowns = document.querySelectorAll('[data-nav-dropdown]');
            const studentQuickActionsWrap = document.querySelector('.student-quick-actions-fab-wrap');
            const themeToggleBtn = document.getElementById('themeToggleBtn');
            const studentAccessibilityLaunch = document.getElementById('studentAccessibilityLaunch');
            const studentNotifPanel = document.getElementById('studentNotifPanel');
            const studentNotifToggleBtn = document.getElementById('studentNotifToggleBtn');
            const studentNotifCloseBtn = document.getElementById('studentNotifCloseBtn');
            const studentNotifActionsToggle = document.getElementById('studentNotifActionsToggle');
            const studentNotifActionsMenu = document.getElementById('studentNotifActionsMenu');
            const studentNotifHistoryBtn = document.getElementById('studentNotifHistoryBtn');
            const studentNotifBackBtn = document.getElementById('studentNotifBackBtn');
            const studentNotifUnreadSection = document.getElementById('studentNotifUnreadSection');
            const studentNotifHistorySection = document.getElementById('studentNotifHistorySection');
            const storageKey = 'student_theme';

            document.querySelectorAll('[data-student-toast]').forEach((toast) => {
                const closeButton = toast.querySelector('[data-student-toast-close]');
                const dismissToast = () => {
                    toast.classList.add('is-hiding');
                    window.setTimeout(() => toast.remove(), 240);
                };

                closeButton?.addEventListener('click', dismissToast);
                window.setTimeout(dismissToast, 5200);
            });

            function forceAccessibilityButtonTheme() {
                document.querySelectorAll('.asw-menu-btn').forEach((button) => {
                    button.style.setProperty('background', '#800000', 'important');
                    button.style.setProperty('background-image', 'none', 'important');
                    button.style.setProperty('border', '2px solid #5f0012', 'important');
                    button.style.setProperty('outline', 'none', 'important');
                    button.style.setProperty('box-shadow', '0 10px 24px rgba(128, 0, 0, 0.28)', 'important');
                    button.querySelectorAll('svg').forEach((icon) => {
                        icon.style.setProperty('fill', '#ffffff', 'important');
                        icon.style.setProperty('stroke', 'none', 'important');
                        icon.style.setProperty('background', 'transparent', 'important');
                    });
                    button.querySelectorAll('svg path:not([fill="none"])').forEach((path) => {
                        path.style.setProperty('fill', '#ffffff', 'important');
                        path.style.setProperty('stroke', 'none', 'important');
                    });
                    button.querySelectorAll('svg path[fill="none"]').forEach((path) => {
                        path.style.setProperty('stroke', 'none', 'important');
                    });
                });
            }

            function closeStudentNotifActionsMenu() {
                if (!studentNotifActionsToggle || !studentNotifActionsMenu) {
                    return;
                }

                studentNotifActionsToggle.setAttribute('aria-expanded', 'false');
                studentNotifActionsMenu.classList.remove('is-open');
            }

            function showStudentUnreadNotifications() {
                if (!studentNotifUnreadSection || !studentNotifHistorySection || !studentNotifBackBtn) {
                    return;
                }

                studentNotifUnreadSection.classList.remove('is-hidden');
                studentNotifHistorySection.classList.add('is-hidden');
                studentNotifBackBtn.classList.remove('is-visible');
            }

            function showStudentNotificationHistory() {
                if (!studentNotifUnreadSection || !studentNotifHistorySection || !studentNotifBackBtn) {
                    return;
                }

                studentNotifUnreadSection.classList.add('is-hidden');
                studentNotifHistorySection.classList.remove('is-hidden');
                studentNotifBackBtn.classList.add('is-visible');
            }

            function closeStudentNotifPanel() {
                if (!studentNotifPanel || !studentNotifToggleBtn) {
                    return;
                }

                studentNotifPanel.classList.remove('is-open');
                studentNotifToggleBtn.setAttribute('aria-expanded', 'false');
                closeStudentNotifActionsMenu();
                showStudentUnreadNotifications();
            }

            function openStudentNotifPanel() {
                if (!studentNotifPanel || !studentNotifToggleBtn) {
                    return;
                }

                studentNotifPanel.classList.add('is-open');
                studentNotifToggleBtn.setAttribute('aria-expanded', 'true');
                closeStudentNotifActionsMenu();
                showStudentUnreadNotifications();
            }

            if (navToggle && navList) {
                const setMobileMenuOpen = (isOpen) => {
                    navList.classList.toggle('show', isOpen);
                    navToggle.classList.toggle('is-open', isOpen);
                    navToggle.setAttribute('aria-expanded', isOpen.toString());
                    navToggle.setAttribute('aria-label', isOpen ? 'Close menu' : 'Open menu');
                };
                const closeMobileMenu = () => setMobileMenuOpen(false);
                const closeDropdowns = (exceptDropdown = null) => {
                    navDropdowns.forEach((dropdown) => {
                        if (dropdown === exceptDropdown) {
                            return;
                        }

                        dropdown.classList.remove('is-open');
                        const toggle = dropdown.querySelector('.student-quick-actions-toggle, .nav-dropdown-toggle, .notif-toggle-btn');
                        if (toggle) {
                            toggle.setAttribute('aria-expanded', 'false');
                        }
                    });

                    if (!exceptDropdown || exceptDropdown !== studentQuickActionsWrap) {
                        closeStudentNotifPanel();
                    }
                };

                navToggle.addEventListener('click', () => {
                    setMobileMenuOpen(!navList.classList.contains('show'));
                });

                navDropdowns.forEach((dropdown) => {
                    const toggle = dropdown.querySelector('.student-quick-actions-toggle, .nav-dropdown-toggle, .notif-toggle-btn');
                    if (!toggle) {
                        return;
                    }

                    toggle.addEventListener('click', (event) => {
                        event.preventDefault();
                        const isOpen = dropdown.classList.contains('is-open');
                        closeDropdowns(isOpen ? null : dropdown);
                        dropdown.classList.toggle('is-open', !isOpen);
                        toggle.setAttribute('aria-expanded', (!isOpen).toString());
                        if (isOpen) {
                            closeStudentNotifPanel();
                        }
                    });
                });

                navList.addEventListener('click', (event) => {
                    const target = event.target.closest('a, button');
                    if (!target) {
                        return;
                    }

                    if (target.classList.contains('student-quick-actions-toggle') || target.classList.contains('nav-dropdown-toggle') || target.classList.contains('notif-toggle-btn')) {
                        return;
                    }

                    closeDropdowns();
                    closeMobileMenu();
                });

                document.addEventListener('click', (event) => {
                    const clickedInsideMenu = navList.contains(event.target) || (studentQuickActionsWrap && studentQuickActionsWrap.contains(event.target));
                    const clickedToggle = navToggle.contains(event.target);
                    if (!clickedInsideMenu && !clickedToggle) {
                        closeDropdowns();
                        closeMobileMenu();
                    }
                });

                document.addEventListener('keydown', (event) => {
                    if (event.key === 'Escape') {
                        closeDropdowns();
                        closeMobileMenu();
                    }
                });

                window.addEventListener('resize', () => {
                    if (window.innerWidth > 768) {
                        closeMobileMenu();
                    }
                });
            }

            if (studentNotifToggleBtn) {
                studentNotifToggleBtn.addEventListener('click', (event) => {
                    event.preventDefault();
                    event.stopPropagation();

                    if (studentQuickActionsWrap) {
                        studentQuickActionsWrap.classList.add('is-open');
                    }

                    if (studentNotifPanel && studentNotifPanel.classList.contains('is-open')) {
                        closeStudentNotifPanel();
                        return;
                    }

                    openStudentNotifPanel();
                });
            }

            if (studentNotifCloseBtn) {
                studentNotifCloseBtn.addEventListener('click', (event) => {
                    event.preventDefault();
                    closeStudentNotifPanel();
                });
            }

            if (studentNotifActionsToggle && studentNotifActionsMenu) {
                studentNotifActionsToggle.addEventListener('click', (event) => {
                    event.preventDefault();
                    event.stopPropagation();
                    const willOpen = !studentNotifActionsMenu.classList.contains('is-open');
                    studentNotifActionsMenu.classList.toggle('is-open', willOpen);
                    studentNotifActionsToggle.setAttribute('aria-expanded', willOpen ? 'true' : 'false');
                });
            }

            if (studentNotifHistoryBtn) {
                studentNotifHistoryBtn.addEventListener('click', (event) => {
                    event.preventDefault();
                    showStudentNotificationHistory();
                    closeStudentNotifActionsMenu();
                });
            }

            if (studentNotifBackBtn) {
                studentNotifBackBtn.addEventListener('click', (event) => {
                    event.preventDefault();
                    showStudentUnreadNotifications();
                });
            }

            document.addEventListener('click', (event) => {
                if (!studentNotifPanel || !studentNotifActionsMenu || !studentNotifActionsToggle) {
                    return;
                }

                const clickedInsideActions = studentNotifActionsMenu.contains(event.target) || studentNotifActionsToggle.contains(event.target);
                if (!clickedInsideActions) {
                    closeStudentNotifActionsMenu();
                }
            });

            function setTheme(theme) {
                const normalizedTheme = theme === 'dark' ? 'dark' : 'light';
                document.documentElement.setAttribute('data-theme', normalizedTheme);

                if (themeToggleBtn) {
                    const isDark = normalizedTheme === 'dark';
                    const moonIcon = '<svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M21.752 15.002A9.718 9.718 0 0 1 18 15.75c-5.385 0-9.75-4.365-9.75-9.75 0-1.33.266-2.597.748-3.752A9.753 9.753 0 0 0 3 11.25C3 16.635 7.365 21 12.75 21a9.753 9.753 0 0 0 9.002-5.998Z"></path></svg>';
                    const sunIcon = '<svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M12 3v2.25m6.364.386-1.591 1.591M21 12h-2.25m-.386 6.364-1.591-1.591M12 18.75V21m-4.773-4.227-1.591 1.591M5.25 12H3m4.227-4.773L5.636 5.636M15.75 12a3.75 3.75 0 1 1-7.5 0 3.75 3.75 0 0 1 7.5 0Z"></path></svg>';
                    themeToggleBtn.innerHTML = isDark ? moonIcon : sunIcon;
                    themeToggleBtn.setAttribute('aria-label', isDark ? 'Dark mode enabled' : 'Light mode enabled');
                    themeToggleBtn.setAttribute('title', isDark ? 'Dark mode' : 'Light mode');
                    themeToggleBtn.setAttribute('aria-pressed', isDark ? 'true' : 'false');
                }
            }

            const currentTheme = document.documentElement.getAttribute('data-theme') || 'light';
            setTheme(currentTheme);

            function initAccessibilityLaunch() {
                function isStudentAccessibilityLauncher(element) {
                    return !!element && (element.id === 'studentAccessibilityLaunch' || element.classList.contains('student-accessibility-launch'));
                }

                function findSiennaTrigger() {
                    const selectorMatches = [
                        '.asw-menu-btn',
                        '#sienna-accessibility-button',
                        '.sienna-accessibility-button',
                        '.sienna-accessibility-trigger',
                        '[data-sienna-accessibility-trigger]',
                        'button[aria-label*="accessibility" i]:not(#studentAccessibilityLaunch)',
                        'button[title*="accessibility" i]:not(#studentAccessibilityLaunch)',
                        '[role="button"][aria-label*="accessibility" i]:not(#studentAccessibilityLaunch)'
                    ];

                    for (const selector of selectorMatches) {
                        const candidate = document.querySelector(selector);
                        if (candidate && !isStudentAccessibilityLauncher(candidate)) {
                            return candidate;
                        }
                    }

                    const fallbackCandidates = Array.from(document.querySelectorAll('button, [role="button"], div'))
                        .filter((element) => {
                            const label = [
                                element.getAttribute('aria-label'),
                                element.getAttribute('title'),
                                element.textContent
                            ].join(' ').toLowerCase();

                            const style = window.getComputedStyle(element);
                            const looksFloating = style.position === 'fixed' || style.position === 'sticky';

                            return looksFloating && label.includes('access') && !isStudentAccessibilityLauncher(element);
                        });

                    return fallbackCandidates[0] || null;
                }

                function showSiennaTrigger() {
                    const trigger = findSiennaTrigger();
                    if (!trigger) {
                        return;
                    }

                    trigger.style.removeProperty('opacity');
                    trigger.style.removeProperty('pointer-events');
                    trigger.removeAttribute('aria-hidden');
                    trigger.style.position = 'fixed';
                    trigger.style.left = 'auto';
                    trigger.style.right = '24px';
                    trigger.style.top = 'auto';
                    trigger.style.bottom = '12px';
                }

                function themeSiennaMenu() {
                    const candidates = document.querySelectorAll('[class*="sienna"], [id*="sienna"]');
                    candidates.forEach((element) => {
                        const style = window.getComputedStyle(element);
                        const role = (element.getAttribute('role') || '').toLowerCase();
                        const isTrigger = element === findSiennaTrigger();
                        const looksPanel =
                            !isTrigger &&
                            (
                                role === 'dialog' ||
                                role === 'menu' ||
                                ((style.position === 'fixed' || style.position === 'absolute') && element.clientWidth >= 220 && element.clientHeight >= 180)
                            );

                        if (!looksPanel) {
                            return;
                        }

                        element.style.background = 'linear-gradient(180deg, #7f1d2d 0%, #4b5563 100%)';
                        element.style.border = '1px solid rgba(255,255,255,0.18)';
                        element.style.color = '#f8fafc';
                        element.style.boxShadow = '0 18px 38px rgba(15, 23, 42, 0.35)';

                        const header = element.querySelector('header, [class*="header"], [class*="title"], [class*="top"]');
                        if (header) {
                            header.style.background = 'linear-gradient(135deg, #8b0000 0%, #6b7280 100%)';
                            header.style.color = '#ffffff';
                            header.style.borderBottom = '1px solid rgba(255,255,255,0.16)';
                        }

                        element.querySelectorAll('button, [role="button"], input, select').forEach((control) => {
                            control.style.background = 'rgba(255,255,255,0.12)';
                            control.style.borderColor = 'rgba(255,255,255,0.22)';
                            control.style.color = '#f8fafc';
                        });
                    });
                }

                function openAccessibilityMenu() {
                    const trigger = findSiennaTrigger();
                    if (!trigger) {
                        return;
                    }

                    showSiennaTrigger();
                    themeSiennaMenu();
                    trigger.click();
                }

                if (studentAccessibilityLaunch) {
                    studentAccessibilityLaunch.addEventListener('click', () => {
                        openAccessibilityMenu();
                    });
                }

                function injectSiennaShadowStyles() {
                    const hosts = Array.from(document.querySelectorAll('body *')).filter((element) => element.shadowRoot);

                    hosts.forEach((host) => {
                        const shadowRoot = host.shadowRoot;
                        if (!shadowRoot || shadowRoot.getElementById('customSiennaTheme')) {
                            return;
                        }

                        const text = shadowRoot.textContent || '';
                        const html = shadowRoot.innerHTML || '';
                        const combined = (text + ' ' + html).toLowerCase();
                        if (!combined.includes('access') && !combined.includes('sienna')) {
                            return;
                        }

                        const style = document.createElement('style');
                        style.id = 'customSiennaTheme';
                        style.textContent = `
                            :host, * {
                                --sienna-primary: #7f1d2d !important;
                                --sienna-secondary: #4b5563 !important;
                            }
                            header,
                            [class*="header"],
                            [class*="title"],
                            [class*="top"] {
                                background: linear-gradient(135deg, #8b0000 0%, #6b7280 100%) !important;
                                color: #ffffff !important;
                                border-bottom: 1px solid rgba(255,255,255,0.16) !important;
                            }
                            [role="dialog"],
                            [role="menu"],
                            .menu,
                            .panel,
                            .popover,
                            .container {
                                background: linear-gradient(180deg, #7f1d2d 0%, #4b5563 100%) !important;
                                color: #f8fafc !important;
                                border-color: rgba(255,255,255,0.18) !important;
                            }
                            button,
                            [role="button"],
                            input,
                            select {
                                background: rgba(255,255,255,0.12) !important;
                                color: #f8fafc !important;
                                border-color: rgba(255,255,255,0.22) !important;
                            }
                        `;

                        shadowRoot.appendChild(style);
                    });
                }

                showSiennaTrigger();
                themeSiennaMenu();
                injectSiennaShadowStyles();
                forceAccessibilityButtonTheme();

                const observer = new MutationObserver(() => {
                    showSiennaTrigger();
                    themeSiennaMenu();
                    injectSiennaShadowStyles();
                    forceAccessibilityButtonTheme();
                });

                observer.observe(document.body, {
                    childList: true,
                    subtree: true
                });
            }

            initAccessibilityLaunch();

            if (themeToggleBtn) {
                themeToggleBtn.addEventListener('click', () => {
                    const activeTheme = document.documentElement.getAttribute('data-theme');
                    const nextTheme = activeTheme === 'dark' ? 'light' : 'dark';
                    setTheme(nextTheme);

                    try {
                        localStorage.setItem(storageKey, nextTheme);
                    } catch (error) {
                        console.warn('Theme preference was not saved.', error);
                    }
                });
            }
        })();
    </script>

    {{-- HEALTH PROFILE PROMPT MODAL --}}
    @auth('student')
    @php
        $studentUser = Auth::guard('student')->user();
        $studentUser?->loadMissing(...array_values(array_filter([
            'healthProfile',
            'employeeHealthProfile',
            \Illuminate\Support\Facades\Schema::hasTable('dependents_profiles') ? 'dependentProfile' : null,
            'adminProfile',
            'adminHubProfile',
        ])));
        $studentHealthFormMarkers = strtolower(trim(implode(' ', array_filter([
            (string) data_get($studentUser, 'user_type', ''),
            (string) data_get($studentUser, 'user_role', ''),
            (string) data_get($studentUser, 'idp_role', ''),
            (string) data_get($studentUser, 'adminHubProfile.role', ''),
            (string) data_get($studentUser, 'adminProfile.access_level', ''),
        ]))));
        $studentDependentMarkers = strtolower(trim(implode(' ', array_filter([
            (string) data_get($studentUser, 'user_type', ''),
            (string) data_get($studentUser, 'idp_role', ''),
        ]))));
        $studentUsesDependentProfile = false;
        if ($studentDependentMarkers !== '') {
            $studentUsesDependentProfile = (
                str_contains($studentDependentMarkers, 'dependent')
                || str_contains($studentDependentMarkers, 'guest')
            ) && !collect(['student', 'applicant', 'faculty', 'admin', 'staff', 'employee', 'designee', 'non-teaching', 'non teaching'])
                ->contains(fn ($needle) => str_contains($studentDependentMarkers, $needle));
        }
        $studentUsesEmployeeHealthForm = false;
        foreach (['faculty', 'admin', 'staff', 'employee', 'designee', 'non-teaching', 'non teaching'] as $studentHealthFormNeedle) {
            if (str_contains($studentHealthFormMarkers, $studentHealthFormNeedle)) {
                $studentUsesEmployeeHealthForm = true;
                break;
            }
        }
        $studentHealthFormStartRoute = $studentUsesEmployeeHealthForm
            ? route('health.form.employee')
            : ($studentUsesDependentProfile ? route('dependent.profile.form') : route('health.form'));
        $studentApplicantHealthFormRoute = route('health.form');
        $studentCurrentHealthFormRoute = route('health.form.student');
        $studentHealthFormTitle = $studentUsesEmployeeHealthForm
            ? 'Health Examination Record'
            : ($studentUsesDependentProfile ? 'Dependent Information Form' : 'Health Information Form');
        $showHealthFormModal = $studentUser
            && !(bool) ($studentUser->is_health_profile_completed ?? false)
            && ($studentUsesEmployeeHealthForm
                ? !$studentUser->employeeHealthProfile
                : ($studentUsesDependentProfile ? !$studentUser->dependentProfile : !$studentUser->healthProfile));
        $studentActiveCorrectionRequest = $studentUser?->healthProfile
            ? \App\Models\HealthProfileCorrectionRequest::query()
                ->where('health_profile_id', $studentUser->healthProfile->id)
                ->active()
                ->latest('requested_at')
                ->latest('id')
                ->first()
            : null;
        $studentResubmissionDocuments = collect($studentActiveCorrectionRequest?->required_documents ?? optional($studentUser?->healthProfile)->resubmission_required_documents ?? [])
            ->filter()
            ->intersect(['student_photo', 'health_declaration', 'medical_certificate', 'chest_xray_result', 'pwd_id_proof'])
            ->values();
        $studentResubmissionNote = trim((string) ($studentActiveCorrectionRequest?->admin_note ?: optional($studentUser?->healthProfile)->pending_reason));
        $studentResubmissionNote = trim(preg_replace('/^Document\s+Resubmission:\s*/i', '', $studentResubmissionNote));
        $studentPendingHealthFormRequest = $studentUser
            ? \App\Models\HealthFormSubmission::query()
                ->where('user_id', $studentUser->id)
                ->where('status', \App\Models\HealthFormSubmission::STATUS_REQUESTED)
                ->latest('requested_at')
                ->latest('id')
                ->first()
            : null;
        $studentPendingReasonSearch = strtolower($studentResubmissionNote);
        $studentNeedsHealthFormCorrection = $studentUser?->healthProfile
            && (
                $studentActiveCorrectionRequest?->type === \App\Models\HealthProfileCorrectionRequest::TYPE_HEALTH_FORM_CORRECTION
                || str_contains($studentPendingReasonSearch, 'health form correction')
                || (
                    (str_contains(strtolower((string) $studentUser->healthProfile->clearance_status), 'pending')
                        || str_contains(strtolower((string) $studentUser->healthProfile->clearance_status), 'conditional'))
                    && collect([
                        'health information form',
                        'health form',
                        'correct address',
                        'home address',
                        'correct information',
                        'correct details',
                    ])->contains(fn ($needle) => str_contains($studentPendingReasonSearch, $needle))
                )
            );
        $studentCorrectionRequestedAt = $studentActiveCorrectionRequest?->requested_at ?: optional($studentUser?->healthProfile)->resubmission_requested_at;
        $studentNewFormRequestedAt = optional($studentPendingHealthFormRequest)->requested_at;
        $studentHealthActionMode = null;
        if ($studentNeedsHealthFormCorrection && $studentPendingHealthFormRequest) {
            $studentHealthActionMode = $studentNewFormRequestedAt
                && (!$studentCorrectionRequestedAt || $studentNewFormRequestedAt->greaterThanOrEqualTo($studentCorrectionRequestedAt))
                    ? 'new'
                    : 'correction';
        } elseif ($studentNeedsHealthFormCorrection) {
            $studentHealthActionMode = 'correction';
        } elseif ($studentPendingHealthFormRequest) {
            $studentHealthActionMode = 'new';
        }
        $showHealthFormActionModal = !$showHealthFormModal
            && session('show_health_form_action_prompt')
            && $studentHealthActionMode !== null;
        $studentHealthActionNote = $studentHealthActionMode === 'correction'
            ? trim((string) preg_replace('/(?:^|\R)\s*Health Form Correction\s*(?=\R|$)/i', '', $studentResubmissionNote))
            : trim((string) optional($studentPendingHealthFormRequest)->remarks);
        $showGlobalResubmissionModal = !$showHealthFormModal
            && !$showHealthFormActionModal
            && $studentUser
            && $studentUser->healthProfile
            && $studentResubmissionDocuments->isNotEmpty();
        $studentResubmissionLabels = [
            'student_photo' => '2x2 Student Photo',
            'health_declaration' => 'Health Declaration',
            'medical_certificate' => 'Medical Certificate',
            'chest_xray_result' => 'Chest X-ray Result',
            'pwd_id_proof' => 'PWD ID Proof',
        ];
        $studentResubmissionMeta = [
            'student_photo' => ['accept' => '.jpg,.jpeg,.png,image/jpeg,image/png', 'hint' => 'JPG or PNG, up to 1 MB'],
            'health_declaration' => ['accept' => '.pdf,.jpg,.jpeg,.png,application/pdf,image/jpeg,image/png', 'hint' => 'PDF, JPG, or PNG, up to 1 MB'],
            'medical_certificate' => ['accept' => '.pdf,.jpg,.jpeg,.png,application/pdf,image/jpeg,image/png', 'hint' => 'PDF, JPG, or PNG, up to 2 MB'],
            'chest_xray_result' => ['accept' => '.pdf,.jpg,.jpeg,.png,application/pdf,image/jpeg,image/png', 'hint' => 'PDF, JPG, or PNG, up to 2 MB'],
            'pwd_id_proof' => ['accept' => '.pdf,application/pdf', 'hint' => 'PDF only, up to 2 MB'],
        ];
    @endphp

    @if($showHealthFormModal)
    <div id="healthFormModal" style="position: fixed !important; top: 0 !important; left: 0 !important; right: 0 !important; bottom: 0 !important; width: 100% !important; height: 100% !important; display: flex !important; align-items: center !important; justify-content: center !important; z-index: 999999 !important;">
        <div class="health-profile-prompt-card" style="background: #fff; border-radius: 24px; padding: 40px; max-width: 580px; width: 92%; text-align: center; box-shadow: 0 25px 80px rgba(0,0,0,0.4); margin: auto; position: relative; border-top: 2px solid #ffc107; border-bottom: 2px solid #ffc107;">
            <div class="health-profile-orb" aria-hidden="true">
                <div class="health-profile-orb-inner">
                    <svg xmlns="http://www.w3.org/2000/svg" width="48" height="48" viewBox="0 0 24 24" fill="none" stroke="#ffffff" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2"></path>
                        <circle cx="9" cy="7" r="4"></circle>
                        <line x1="19" y1="8" x2="19" y2="14"></line>
                        <line x1="22" y1="11" x2="16" y2="11"></line>
                    </svg>
                </div>
            </div>
            <div class="health-profile-kicker">
                <span aria-hidden="true">▣</span>
                {{ $studentHealthFormTitle }}
            </div>
            <h2 class="health-profile-prompt-title" style="color: #1f2937; font-size: 24px; font-weight: 800; margin: 0 0 16px;">
                {{ $studentUsesDependentProfile ? 'Complete Your Dependent Information' : 'Complete Your Health Profile' }}
                <span class="health-profile-required">Required</span>
            </h2>
            <p class="health-profile-prompt-copy" style="color: #4b5563; font-size: 13px; line-height: 1.45; margin: -8px 0 12px;">
                Welcome, <strong>{{ $studentUser->first_name ?? 'Student' }}!</strong> 👋<br>
                Let’s complete your <strong>{{ $studentHealthFormTitle }}</strong><br>
                to complete your clinic record.
            </p>
            <div class="health-profile-prepare">
                <strong>Please prepare:</strong>
                <div class="health-profile-prepare-grid">
                    @if($studentUsesDependentProfile)
                    <div>
                        <span>
                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" aria-hidden="true">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 6a3.75 3.75 0 1 1-7.5 0 3.75 3.75 0 0 1 7.5 0ZM4.501 20.118a7.5 7.5 0 0 1 14.998 0A17.933 17.933 0 0 1 12 21.75c-2.676 0-5.216-.584-7.499-1.632Z" />
                            </svg>
                        </span>
                        <small>Personal<br>Information</small>
                    </div>
                    <div>
                        <span>
                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" aria-hidden="true">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M2.25 6.75c0 8.284 6.716 15 15 15h2.25a2.25 2.25 0 0 0 2.25-2.25v-1.372c0-.516-.351-.966-.852-1.091l-4.423-1.106a1.125 1.125 0 0 0-1.173.417l-.97 1.293a1.125 1.125 0 0 1-1.21.38 12.035 12.035 0 0 1-7.143-7.143 1.125 1.125 0 0 1 .38-1.21l1.293-.97a1.125 1.125 0 0 0 .417-1.173L6.963 3.102A1.125 1.125 0 0 0 5.872 2.25H4.5A2.25 2.25 0 0 0 2.25 4.5v2.25Z" />
                            </svg>
                        </span>
                        <small>Contact<br>Details</small>
                    </div>
                    @endif
                    @unless($studentUsesDependentProfile)
                    <div>
                        <span>
                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" aria-hidden="true">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 6a3.75 3.75 0 1 1-7.5 0 3.75 3.75 0 0 1 7.5 0ZM4.501 20.118a7.5 7.5 0 0 1 14.998 0A17.933 17.933 0 0 1 12 21.75c-2.676 0-5.216-.584-7.499-1.632Z" />
                            </svg>
                        </span>
                        <small>Personal<br>Information</small>
                    </div>
                    <div>
                        <span>
                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" aria-hidden="true">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M21 8.25c0-2.485-2.099-4.5-4.688-4.5-1.935 0-3.597 1.126-4.312 2.733-.715-1.607-2.377-2.733-4.313-2.733C5.1 3.75 3 5.765 3 8.25c0 7.22 9 12 9 12s9-4.78 9-12Z" />
                            </svg>
                        </span>
                        <small>Medical<br>History</small>
                    </div>
                    <div>
                        <span>
                            <img width="50" height="50" src="https://img.icons8.com/ios/50/syringe--v1.png" alt="syringe--v1">
                        </span>
                        <small>Vaccination<br>Details</small>
                    </div>
                    <div>
                        <span>
                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" aria-hidden="true">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 14.25v-2.625a3.375 3.375 0 0 0-3.375-3.375h-1.5A1.125 1.125 0 0 1 13.5 7.125v-1.5a3.375 3.375 0 0 0-3.375-3.375H8.25m2.25 0H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 0 0-9-9Z" />
                            </svg>
                        </span>
                        <small>Required<br>Documents</small>
                    </div>
                    @endunless
                </div>
            </div>
            <div class="health-profile-time">Estimated time: <strong>{{ $studentUsesDependentProfile ? '2-3 minutes' : '5-8 minutes' }}</strong></div>
            <div class="health-profile-benefits">
                <div>
                    <span>
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" aria-hidden="true">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M16.5 10.5V6.75a4.5 4.5 0 1 0-9 0v3.75m-.75 11.25h10.5a2.25 2.25 0 0 0 2.25-2.25v-6.75a2.25 2.25 0 0 0-2.25-2.25H6.75a2.25 2.25 0 0 0-2.25 2.25v6.75a2.25 2.25 0 0 0 2.25 2.25Z" />
                        </svg>
                    </span>
                    <strong>Secure</strong>
                    <small>Your information is protected</small>
                </div>
                <div>
                    <span>
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" aria-hidden="true">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75 11.25 15 15 9.75M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z" />
                        </svg>
                    </span>
                    <strong>Complete</strong>
                    <small>Submit all required information</small>
                </div>
                <div>
                    <span>
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" aria-hidden="true">
                            <path stroke-linecap="round" stroke-linejoin="round" d="m3.75 13.5 10.5-11.25L12 10.5h8.25L9.75 21.75 12 13.5H3.75Z" />
                        </svg>
                    </span>
                    <strong>Fast</strong>
                    <small>Takes only a few minutes to finish</small>
                </div>
            </div>
            <div class="health-profile-prompt-actions" style="display: flex; gap: 10px; justify-content: center; align-items: center; flex-wrap: wrap;">
                <a class="health-profile-fill-button" href="{{ $studentHealthFormStartRoute }}" data-health-role-selector-trigger>
                    <span>Get Started</span>
                    <svg viewBox="0 0 24 24" fill="none" aria-hidden="true">
                        <path d="M5 12h14" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round"/>
                        <path d="m13 6 6 6-6 6" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round"/>
                    </svg>
                </a>
                @env('local')
                    <button type="button" onclick="document.getElementById('healthFormModal').style.display='none'" style="display: inline-block; background: #e5e7eb; color: #1f2937; border: none; padding: 14px 30px; border-radius: 12px; font-size: 16px; font-weight: 700; cursor: pointer;">
                        Skip for Now
                    </button>
                @endenv
            </div>
        </div>
    </div>
    <style>
        #healthFormModal {
            background: rgba(0, 0, 0, 0.75) !important;
            backdrop-filter: blur(8px);
        }
        #healthFormModal .health-profile-prompt-card {
                max-height: calc(100vh - 28px);
                overflow-x: hidden;
                overflow-y: auto;
                background:
                    linear-gradient(rgba(255, 255, 255, .24), rgba(255, 255, 255, .38)),
                    url('{{ asset('images/hif_bg.png') }}') center / cover no-repeat,
                    linear-gradient(180deg, #fffdf8 0%, #fff8e7 100%) !important;
            color: #1f2937 !important;
            border: 1px solid rgba(250, 204, 21, .8) !important;
            border-bottom: 3px solid #facc15 !important;
        }
        #healthFormModal .health-profile-prompt-title {
            color: #1f2937 !important;
        }
        #healthFormModal .health-profile-prompt-copy {
            color: #4b5563 !important;
        }
        #healthFormModal .health-profile-prompt-instruction {
            color: #6b7280 !important;
        }
        #healthFormModal .health-profile-orb {
            position: relative;
            width: 86px;
            height: 86px;
            margin: -14px auto 18px;
            display: grid;
            place-items: center;
            border-radius: 50%;
            background: radial-gradient(circle, rgba(250, 204, 21, .34), rgba(250, 204, 21, 0) 68%);
        }
        #healthFormModal .health-profile-orb-inner {
            width: 64px;
            height: 64px;
            display: grid;
            place-items: center;
            border-radius: 50%;
            background: linear-gradient(135deg, #8b0000 0%, #6b0000 100%);
            animation: floatIcon 3s ease-in-out infinite;
            box-shadow: 0 10px 24px rgba(139, 0, 0, .28), 0 0 0 8px rgba(250, 204, 21, .18);
        }
        #healthFormModal .health-profile-orb-inner svg {
            width: 34px;
            height: 34px;
        }
        #healthFormModal .health-profile-kicker {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            margin-bottom: 10px;
            padding: 6px 16px;
            border: 1px solid rgba(250, 204, 21, .85);
            border-radius: 999px;
            background: rgba(255, 248, 225, .88);
            color: #70131b;
            font-size: 12px;
            font-weight: 900;
            text-transform: uppercase;
            letter-spacing: .03em;
        }
        #healthFormModal .health-profile-required {
            display: inline-flex;
            vertical-align: middle;
            margin-left: 8px;
            padding: 5px 10px;
            border-radius: 999px;
            background: #ffe4e6;
            color: #9f1239;
            font-size: 11px;
            font-weight: 900;
        }
        #healthFormModal .health-profile-prepare {
            margin: -6px auto 8px;
            max-width: 396px;
            padding: 12px 14px 14px;
            border: 1px solid rgba(250, 204, 21, .42);
            border-radius: 12px;
            background: rgba(255, 251, 235, .68);
            text-align: left;
        }
        #healthFormModal .health-profile-prepare > strong {
            display: block;
            margin-bottom: 12px;
            color: #70131b;
            font-size: 12px;
            font-weight: 900;
        }
        #healthFormModal .health-profile-prepare-grid {
            display: grid;
            grid-template-columns: repeat(4, 1fr);
            gap: 0;
            text-align: center;
        }
        #healthFormModal .health-profile-prepare-grid div {
            display: grid;
            justify-items: center;
            gap: 6px;
            padding: 0 8px;
            border-right: 1px solid rgba(127, 29, 45, .14);
        }
        #healthFormModal .health-profile-prepare-grid div:last-child {
            border-right: 0;
        }
        #healthFormModal .health-profile-prepare-grid span,
        #healthFormModal .health-profile-benefits span {
            width: 32px;
            height: 32px;
            display: grid;
            place-items: center;
            border-radius: 50%;
            background: #fff7ed;
            color: #70131b;
            border: 1px solid rgba(127, 29, 45, .12);
            font-size: 13px;
            font-weight: 900;
        }
        #healthFormModal .health-profile-prepare-grid span svg,
        #healthFormModal .health-profile-prepare-grid span img,
        #healthFormModal .health-profile-benefits span svg {
            width: 18px;
            height: 18px;
            object-fit: contain;
        }
        #healthFormModal .health-profile-prepare-grid span img {
            filter: sepia(1) saturate(4) hue-rotate(315deg) brightness(.55);
        }
        #healthFormModal .health-profile-prepare-grid small {
            color: #3f1d1d;
            font-size: 10px;
            font-weight: 900;
            line-height: 1.22;
        }
        #healthFormModal .health-profile-time {
            margin: 0 0 10px;
            color: #475569;
            font-size: 12px;
            font-weight: 800;
        }
        #healthFormModal .health-profile-time::before {
            content: "◷";
            color: #f59e0b;
            margin-right: 6px;
        }
        #healthFormModal .health-profile-time strong {
            color: #70131b;
        }
        #healthFormModal .health-profile-benefits {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 18px;
            margin: 0 auto 18px;
            max-width: 450px;
            text-align: left;
        }
        #healthFormModal .health-profile-benefits div {
            display: grid;
            grid-template-columns: 34px 1fr;
            column-gap: 8px;
            align-items: center;
        }
        #healthFormModal .health-profile-benefits span {
            grid-row: span 2;
        }
        #healthFormModal .health-profile-benefits strong {
            color: #70131b;
            font-size: 11px;
            font-weight: 900;
        }
        #healthFormModal .health-profile-benefits small {
            color: #334155;
            font-size: 9px;
            font-weight: 800;
            line-height: 1.2;
        }
        #healthFormModal .health-profile-prompt-actions {
            margin-top: 8px;
        }
        #healthFormModal .health-profile-fill-button {
            position: relative;
            isolation: isolate;
            display: inline-flex;
            min-width: 210px;
            min-height: 52px;
            overflow: hidden;
            align-items: center;
            justify-content: center;
            gap: 10px;
            padding: 12px 28px;
            border: 1px solid #8b0000;
            border-radius: 12px;
            background: #8b0000;
            color: #ffffff !important;
            font-size: 16px;
            font-weight: 800;
            text-decoration: none;
            box-shadow: 0 8px 24px rgba(139, 0, 0, .3);
            transition: color .25s ease, border-color .25s ease, box-shadow .25s ease, transform .25s ease;
        }
        #healthFormModal .health-profile-fill-button::before {
            position: absolute;
            z-index: -1;
            inset: 0;
            background: #facc15;
            content: "";
            transform: translateX(-102%);
            transition: transform .32s ease;
        }
        #healthFormModal .health-profile-fill-button svg {
            width: 20px;
            height: 20px;
            flex: 0 0 auto;
            stroke-width: 2;
        }
        #healthFormModal .health-profile-fill-button:hover,
        #healthFormModal .health-profile-fill-button:focus-visible {
            border-color: #facc15;
            color: #70131b !important;
            transform: translateY(-2px);
            box-shadow: 0 12px 32px rgba(139, 0, 0, .4);
            outline: none;
        }
        #healthFormModal .health-profile-fill-button:hover::before,
        #healthFormModal .health-profile-fill-button:focus-visible::before {
            transform: translateX(0);
        }
        @keyframes floatIcon {
            0%, 100% {
                transform: translateY(0);
            }
            50% {
                transform: translateY(-8px);
            }
        }
        @media (max-width: 620px) {
            #healthFormModal {
                align-items: flex-start !important;
                overflow-y: auto !important;
                padding: 12px 0 !important;
            }
            #healthFormModal .health-profile-prompt-card {
                max-height: none;
                padding: 28px 18px !important;
            }
            #healthFormModal .health-profile-prepare-grid {
                grid-template-columns: repeat(2, 1fr);
                row-gap: 12px;
            }
            #healthFormModal .health-profile-prepare-grid div:nth-child(2) {
                border-right: 0;
            }
            #healthFormModal .health-profile-benefits {
                grid-template-columns: 1fr;
                max-width: 280px;
            }
        }
    </style>
    @endif

    @if($showHealthFormModal && !$studentUsesEmployeeHealthForm)
    <div id="healthRoleSelectorModal" class="health-role-selector-modal" role="dialog" aria-modal="true" aria-labelledby="healthRoleSelectorTitle" hidden>
        <section class="health-role-selector-card">
            <button type="button" class="health-role-selector-close" data-health-role-selector-close aria-label="Close role selector">
                <x-outline-icon name="x-mark" />
            </button>
            <div class="health-role-selector-heading">
                <div>
                    <span class="health-role-selector-kicker">Access Type</span>
                    <h2 id="healthRoleSelectorTitle">How would you like to continue?</h2>
                    <p>Select the account type that applies to you.</p>
                </div>
            </div>
            <div class="health-role-options" role="radiogroup" aria-label="Choose your role">
                <label class="health-role-option" data-health-role-option>
                    <input type="radio" name="health_form_role" value="applicant" data-health-role-route="{{ $studentApplicantHealthFormRoute }}">
                    <span class="health-role-radio" aria-hidden="true"></span>
                    <span class="health-role-icon" aria-hidden="true"><x-outline-icon name="academic-cap" /></span>
                    <span class="health-role-copy">
                        <strong>Applicants</strong>
                        <small>Use your application reference number.</small>
                    </span>
                    <span class="health-role-check" aria-hidden="true"><x-outline-icon name="check" /></span>
                </label>
                <label class="health-role-option" data-health-role-option>
                    <input type="radio" name="health_form_role" value="student" data-health-role-route="{{ $studentCurrentHealthFormRoute }}">
                    <span class="health-role-radio" aria-hidden="true"></span>
                    <span class="health-role-icon" aria-hidden="true"><x-outline-icon name="identification" /></span>
                    <span class="health-role-copy">
                        <strong>Current Student / OJT</strong>
                        <small>Use your Student ID.</small>
                    </span>
                    <span class="health-role-check" aria-hidden="true"><x-outline-icon name="check" /></span>
                </label>
            </div>
            <div class="health-role-selector-actions">
                <a class="health-role-continue" id="healthRoleSelectorContinue" href="#">Continue</a>
            </div>
        </section>
    </div>
    <style>
        .health-role-selector-modal[hidden] { display: none !important; }
        .health-role-selector-modal {
            position: fixed;
            inset: 0;
            z-index: 1000001;
            display: grid;
            place-items: center;
            padding: 20px;
            background: rgba(15, 23, 42, .62);
            backdrop-filter: blur(8px);
        }
        .health-role-selector-card {
            position: relative;
            width: min(610px, 100%);
            padding: 24px 26px 22px;
            border: 1px solid rgba(127, 29, 45, .14);
            border-radius: 14px;
            background: #fff;
            box-shadow: 0 28px 80px rgba(15, 23, 42, .32);
        }
        .health-role-selector-close {
            position: absolute;
            top: 22px;
            right: 24px;
            display: grid;
            place-items: center;
            width: 38px;
            height: 38px;
            border: 1px solid #d1d5db;
            border-radius: 50%;
            background: #fff;
            color: #7f1d2d;
            cursor: pointer;
            isolation: isolate;
            overflow: hidden;
            transition: background-color .2s ease, color .2s ease, border-color .2s ease, transform .2s ease;
        }
        .health-role-selector-close::after,
        .health-role-continue::after {
            position: absolute;
            top: -35%;
            bottom: -35%;
            left: -80%;
            width: 34%;
            background: linear-gradient(105deg, transparent, rgba(255, 249, 190, .18) 28%, rgba(255, 249, 190, .9) 50%, rgba(255, 249, 190, .18) 72%, transparent);
            content: "";
            transform: skewX(-18deg);
            opacity: 0;
            pointer-events: none;
        }
        .health-role-selector-close:hover::after,
        .health-role-selector-close:focus-visible::after,
        .health-role-continue:hover::after,
        .health-role-continue:focus-visible::after {
            animation: health-role-light-sweep .7s ease-out;
        }
        @keyframes health-role-light-sweep {
            0% { left: -80%; opacity: 0; }
            18% { opacity: .9; }
            82% { opacity: .9; }
            100% { left: 135%; opacity: 0; }
        }
        .health-role-selector-close:hover,
        .health-role-selector-close:focus-visible {
            border-color: #facc15;
            background: #facc15;
            color: #000;
            transform: translateY(-1px);
            outline: none;
        }
        .health-role-selector-close svg { position: relative; z-index: 1; width: 20px; height: 20px; }
        .health-role-selector-heading { display: flex; align-items: flex-start; padding: 4px 46px 20px 8px; }
        .health-role-selector-kicker { display: block; margin-bottom: 8px; color: #7f1d2d; font-size: 11px; font-weight: 900; letter-spacing: .16em; text-transform: uppercase; }
        .health-role-selector-heading h2,
        #healthRoleSelectorTitle { margin: 0; color: #000000 !important; font-size: 24px; line-height: 1.2; }
        html[data-theme="dark"] .health-role-selector-heading h2,
        html[data-theme="dark"] #healthRoleSelectorTitle { color: #000000 !important; }
        .health-role-selector-heading p { margin: 8px 0 0; color: #4b5563; font-size: 16px; }
        .health-role-options { display: grid; grid-template-columns: repeat(2, minmax(0, 1fr)); gap: 14px; }
        .health-role-option { position: relative; display: grid; grid-template-columns: 24px 54px minmax(0, 1fr) 24px; align-items: center; gap: 10px; min-height: 108px; padding: 14px; border: 1px solid #d1d5db; border-radius: 12px; cursor: pointer; transition: border-color .2s ease, background .2s ease, box-shadow .2s ease, transform .2s ease; }
        .health-role-option:hover,
        .health-role-option:focus-within {
            border-color: rgba(127, 29, 45, .62);
            background: #fffdf8;
            box-shadow: 0 10px 22px rgba(127, 29, 45, .14);
            transform: translateY(-3px);
        }
        .health-role-option.is-selected { border-color: #9f1239; background: #fffaf0; box-shadow: 0 8px 18px rgba(127, 29, 45, .1); }
        .health-role-option input { position: absolute; opacity: 0; pointer-events: none; }
        .health-role-radio { width: 23px; height: 23px; border: 2px solid #b8b8b8; border-radius: 50%; }
        .health-role-option.is-selected .health-role-radio { border-color: #7f1d2d; box-shadow: inset 0 0 0 5px #fff; background: #7f1d2d; }
        .health-role-icon { display: grid; place-items: center; width: 52px; height: 52px; border-radius: 50%; background: #fff1d6; color: #7f1d2d; }
        .health-role-icon svg { width: 30px; height: 30px; }
        .health-role-copy { display: grid; gap: 8px; }
        .health-role-copy strong { color: #64111d; font-size: 16px; line-height: 1.2; }
        .health-role-copy small { color: #4b5563; font-size: 13px; }
        .health-role-check { display: none; place-items: center; width: 24px; height: 24px; border-radius: 50%; background: #7f1d2d; color: #fff; }
        .health-role-option.is-selected .health-role-check { display: grid; }
        .health-role-check svg { width: 15px; height: 15px; }
        .health-role-selector-actions { display: flex; justify-content: center; margin-top: 24px; }
        .health-role-continue { position: relative; isolation: isolate; overflow: hidden; display: none; align-items: center; justify-content: center; width: 100%; padding: 10px 24px; border: 1px solid #9f1239; border-radius: 6px; background: #7f1d2d; color: #fff; font-size: 16px; font-weight: 700; text-align: center; text-decoration: none; cursor: pointer; transition: background-color .2s ease, color .2s ease, border-color .2s ease, box-shadow .2s ease, transform .2s ease; }
        .health-role-continue.is-visible { display: inline-flex; }
        .health-role-continue:hover,
        .health-role-continue:focus-visible {
            border-color: #facc15;
            background: #facc15;
            color: #000;
            box-shadow: 0 10px 22px rgba(127, 29, 45, .16);
            transform: translateY(-2px);
            outline: none;
        }
        @media (max-width: 620px) {
            .health-role-selector-card { padding: 28px 18px 24px; }
            .health-role-selector-heading h2 { font-size: 20px; }
            .health-role-options { grid-template-columns: 1fr; gap: 12px; }
            .health-role-option { min-height: 100px; padding: 14px; }
            .health-role-selector-actions { margin-top: 24px; }
        }
    </style>
    <script>
        (function () {
            const promptModal = document.getElementById('healthFormModal');
            const roleModal = document.getElementById('healthRoleSelectorModal');
            const trigger = promptModal?.querySelector('[data-health-role-selector-trigger]');
            const continueLink = document.getElementById('healthRoleSelectorContinue');
            const options = Array.from(roleModal?.querySelectorAll('[data-health-role-option]') || []);
            if (!promptModal || !roleModal || !trigger || !continueLink) return;

            function closeRoleSelector() {
                roleModal.hidden = true;
                promptModal.style.display = 'flex';
                document.body.style.overflow = '';
            }

            trigger.addEventListener('click', function (event) {
                event.preventDefault();
                promptModal.style.display = 'none';
                roleModal.hidden = false;
                document.body.style.overflow = 'hidden';
                options[0]?.querySelector('input')?.focus();
            });

            options.forEach(function (option) {
                option.addEventListener('click', function () {
                    const input = option.querySelector('input');
                    if (!input) return;
                    input.checked = true;
                    options.forEach((item) => item.classList.toggle('is-selected', item === option));
                    continueLink.href = input.dataset.healthRoleRoute || '#';
                    continueLink.textContent = input.value === 'applicant'
                        ? 'Continue as Applicant'
                        : 'Continue as Student / OJT';
                    continueLink.classList.add('is-visible');
                });
            });

            roleModal.querySelectorAll('[data-health-role-selector-close]').forEach((button) => button.addEventListener('click', closeRoleSelector));
            roleModal.addEventListener('click', (event) => {
                if (event.target === roleModal) closeRoleSelector();
            });
            document.addEventListener('keydown', (event) => {
                if (event.key === 'Escape' && !roleModal.hidden) closeRoleSelector();
            });
        })();
    </script>
    @endif

    @if($showHealthFormActionModal)
    <div
        id="healthFormActionModal"
        class="health-form-action-modal"
        role="dialog"
        aria-modal="true"
        aria-labelledby="healthFormActionTitle"
        hidden
    >
        <section class="health-form-action-card">
            <header class="health-form-action-head">
                <span class="health-form-action-head-icon" aria-hidden="true">
                    @if($studentHealthActionMode === 'correction')
                        <x-outline-icon name="pencil-square" />
                    @else
                        <x-outline-icon name="document-text" />
                    @endif
                </span>
                <div>
                    <span class="health-form-action-kicker">
                        {{ $studentHealthActionMode === 'correction' ? 'Health Form Correction' : 'New Health Form Request' }}
                    </span>
                    <h2 id="healthFormActionTitle">
                        {{ $studentHealthActionMode === 'correction' ? 'Your health form is ready for editing' : 'A new health form is ready' }}
                    </h2>
                </div>
                <button type="button" class="health-form-action-close" data-health-form-action-close aria-label="Close health form notification">
                    <x-outline-icon name="x-mark" />
                </button>
            </header>

            <div class="health-form-action-body">
                <div class="health-form-action-status" aria-hidden="true">
                    <span><x-outline-icon name="bell" /></span>
                    <strong>Action requested by the Medical Clinic</strong>
                </div>

                @if($studentHealthActionMode === 'correction')
                    <p>
                        The Medical Clinic requested updates to your submitted Health Information Form.
                        Review the clinic note, correct the requested details, and submit the form again.
                    </p>
                @else
                    <p>
                        The Medical Clinic requested a new Health Information Form
                        @if(filled(optional($studentPendingHealthFormRequest)->category))
                            for <strong>{{ $studentPendingHealthFormRequest->category }}</strong>
                        @endif.
                        You can now fill out and submit a fresh form.
                    </p>
                @endif

                <div class="health-form-action-note">
                    <span class="health-form-action-note-icon" aria-hidden="true">
                        <x-outline-icon name="information-circle" />
                    </span>
                    <div>
                        <strong>{{ $studentHealthActionMode === 'correction' ? 'Clinic Note' : 'Request Details' }}</strong>
                        <span>
                            {{ $studentHealthActionNote !== ''
                                ? $studentHealthActionNote
                                : ($studentHealthActionMode === 'correction'
                                    ? 'Please review and update the information identified by the Medical Clinic.'
                                    : 'Please complete the new form using your current health information.') }}
                        </span>
                    </div>
                </div>
            </div>

            <footer class="health-form-action-actions">
                <button type="button" class="health-form-action-later" data-health-form-action-close>Review Later</button>
                <a href="{{ $studentHealthFormStartRoute }}" class="health-form-action-primary" data-health-form-action-primary>
                    @if($studentHealthActionMode === 'correction')
                        <x-outline-icon name="pencil-square" />
                        <span>Edit Health Form</span>
                    @else
                        <x-outline-icon name="document-text" />
                        <span>Fill Up New Health Form</span>
                    @endif
                    <x-outline-icon name="arrow-long-right" />
                </a>
            </footer>
        </section>
    </div>

    <style>
        .health-form-action-modal[hidden] {
            display: none !important;
        }
        .health-form-action-modal {
            position: fixed;
            inset: 0;
            z-index: 1000000;
            display: grid;
            place-items: center;
            padding: 20px;
            background: rgba(15, 23, 42, .72);
            backdrop-filter: blur(8px);
        }
        .health-form-action-card {
            width: min(590px, 100%);
            overflow: hidden;
            border: 1px solid rgba(250, 204, 21, .72);
            border-radius: 12px;
            background: #ffffff;
            color: #182033;
            box-shadow: 0 28px 80px rgba(15, 23, 42, .42), 0 0 0 1px rgba(127, 29, 45, .08);
        }
        .health-form-action-head {
            position: relative;
            display: grid;
            grid-template-columns: 52px minmax(0, 1fr) 42px;
            gap: 14px;
            align-items: center;
            padding: 20px 22px;
            background: linear-gradient(135deg, #8b1823 0%, #b91c1c 100%);
            color: #ffffff;
        }
        .health-form-action-head-icon,
        .health-form-action-close {
            width: 44px;
            height: 44px;
            display: grid;
            place-items: center;
            border-radius: 9px;
        }
        .health-form-action-head-icon {
            border: 1px solid rgba(255, 255, 255, .28);
            background: rgba(255, 255, 255, .13);
        }
        .health-form-action-head-icon svg,
        .health-form-action-close svg {
            width: 23px;
            height: 23px;
        }
        .health-form-action-kicker {
            display: block;
            margin-bottom: 4px;
            color: #fde68a;
            font-size: 11px;
            font-weight: 900;
            text-transform: uppercase;
        }
        .health-form-action-head h2 {
            margin: 0;
            color: #ffffff;
            font-size: 22px;
            line-height: 1.2;
            letter-spacing: 0;
        }
        .health-form-action-close {
            position: relative;
            overflow: hidden;
            width: 36px;
            height: 36px;
            border-radius: 50%;
            border: 1px solid transparent;
            background: rgba(74, 10, 18, .32);
            color: #ffffff;
            cursor: pointer;
            outline: none;
            transition: background .2s ease, color .2s ease, transform .2s ease;
        }
        .health-form-action-close::after {
            content: "";
            position: absolute;
            top: -35%;
            left: -78%;
            width: 42%;
            height: 170%;
            background: linear-gradient(
                115deg,
                transparent 0%,
                rgba(255, 255, 255, .18) 30%,
                rgba(255, 255, 255, .82) 50%,
                rgba(255, 255, 255, .18) 70%,
                transparent 100%
            );
            transform: skewX(-18deg);
            transition: left .48s ease;
            pointer-events: none;
        }
        .health-form-action-close svg {
            position: relative;
            z-index: 1;
            width: 17px;
            height: 17px;
        }
        .health-form-action-close:hover,
        .health-form-action-close:focus-visible {
            border-color: transparent;
            background: #facc15;
            color: #70131b;
            outline: none;
            transform: translateY(-1px);
        }
        .health-form-action-close:hover::after,
        .health-form-action-close:focus-visible::after {
            left: 136%;
        }
        .health-form-action-body {
            padding: 24px;
        }
        .health-form-action-status {
            display: flex;
            align-items: center;
            gap: 10px;
            margin-bottom: 16px;
            color: #7f1d1d;
            font-size: 12px;
        }
        .health-form-action-status > span {
            width: 34px;
            height: 34px;
            display: grid;
            place-items: center;
            border-radius: 50%;
            background: #fff1f2;
        }
        .health-form-action-status svg,
        .health-form-action-note-icon svg,
        .health-form-action-primary svg {
            width: 18px;
            height: 18px;
        }
        .health-form-action-body > p {
            margin: 0;
            color: #475569;
            font-size: 14px;
            line-height: 1.65;
        }
        .health-form-action-note {
            display: grid;
            grid-template-columns: 38px minmax(0, 1fr);
            gap: 11px;
            margin-top: 20px;
            padding: 15px;
            border: 1px solid rgba(245, 158, 11, .32);
            border-radius: 8px;
            background: #fffbeb;
        }
        .health-form-action-note-icon {
            width: 36px;
            height: 36px;
            display: grid;
            place-items: center;
            border-radius: 8px;
            background: #fef3c7;
            color: #991b1b;
        }
        .health-form-action-note strong,
        .health-form-action-note span {
            display: block;
        }
        .health-form-action-note strong {
            margin-bottom: 3px;
            color: #7f1d1d;
            font-size: 12px;
        }
        .health-form-action-note span {
            color: #475569;
            font-size: 12px;
            line-height: 1.5;
            white-space: pre-line;
        }
        .health-form-action-actions {
            display: flex;
            justify-content: flex-end;
            gap: 10px;
            padding: 16px 24px 20px;
            border-top: 1px solid #e5e7eb;
            background: #f8fafc;
        }
        .health-form-action-later,
        .health-form-action-primary {
            min-height: 44px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
            padding: 10px 18px;
            border-radius: 8px;
            font: inherit;
            font-size: 13px;
            font-weight: 850;
            text-decoration: none;
            cursor: pointer;
        }
        .health-form-action-later {
            border: 1px solid #cbd5e1;
            background: #ffffff;
            color: #334155;
        }
        .health-form-action-primary {
            position: relative;
            overflow: hidden;
            border: 1px solid #8b0000;
            background: #8b0000;
            color: #ffffff !important;
            box-shadow: 0 9px 22px rgba(127, 29, 29, .24);
        }
        .health-form-action-primary:hover,
        .health-form-action-primary:focus-visible {
            border-color: #eab308;
            background: #facc15;
            color: #70131b !important;
            outline: none;
            transform: translateY(-1px);
        }
        html[data-theme="dark"] .health-form-action-card {
            border-color: rgba(250, 204, 21, .42);
            background: #101827;
            color: #f8fafc;
        }
        html[data-theme="dark"] .health-form-action-body > p,
        html[data-theme="dark"] .health-form-action-note span {
            color: #d7deea;
        }
        html[data-theme="dark"] .health-form-action-status {
            color: #fde68a;
        }
        html[data-theme="dark"] .health-form-action-status > span,
        html[data-theme="dark"] .health-form-action-note-icon {
            background: rgba(127, 29, 29, .34);
            color: #fde68a;
        }
        html[data-theme="dark"] .health-form-action-note {
            border-color: rgba(250, 204, 21, .25);
            background: #171f2f;
        }
        html[data-theme="dark"] .health-form-action-note strong {
            color: #fde68a;
        }
        html[data-theme="dark"] .health-form-action-actions {
            border-color: #293548;
            background: #0d1420;
        }
        html[data-theme="dark"] .health-form-action-later {
            border-color: #415069;
            background: transparent;
            color: #f8fafc;
        }
        @media (max-width: 600px) {
            .health-form-action-modal {
                align-items: end;
                padding: 12px;
            }
            .health-form-action-head {
                grid-template-columns: 44px minmax(0, 1fr) 38px;
                gap: 10px;
                padding: 17px 16px;
            }
            .health-form-action-head-icon,
            .health-form-action-close {
                width: 38px;
                height: 38px;
            }
            .health-form-action-close {
                width: 34px;
                height: 34px;
            }
            .health-form-action-head h2 {
                font-size: 18px;
            }
            .health-form-action-body {
                padding: 18px;
            }
            .health-form-action-actions {
                flex-direction: column-reverse;
                padding: 14px 18px 18px;
            }
            .health-form-action-later,
            .health-form-action-primary {
                width: 100%;
            }
        }
    </style>

    <script>
        (function () {
            const modal = document.getElementById('healthFormActionModal');
            if (!modal) {
                return;
            }

            const closeButtons = modal.querySelectorAll('[data-health-form-action-close]');
            const primaryAction = modal.querySelector('[data-health-form-action-primary]');

            function openHealthFormActionModal() {
                modal.hidden = false;
                document.body.style.overflow = 'hidden';
                modal.querySelector('[data-health-form-action-primary]')?.focus();
            }

            function closeHealthFormActionModal() {
                modal.hidden = true;
                document.body.style.overflow = '';
            }

            function openAfterTermsGate() {
                const termsGate = document.getElementById('termsGateOverlay');
                if (!termsGate) {
                    openHealthFormActionModal();
                    return;
                }

                const observer = new MutationObserver(function () {
                    if (!document.getElementById('termsGateOverlay')) {
                        observer.disconnect();
                        openHealthFormActionModal();
                    }
                });
                observer.observe(document.body, { childList: true, subtree: true });
            }

            closeButtons.forEach(function (button) {
                button.addEventListener('click', closeHealthFormActionModal);
            });
            primaryAction?.addEventListener('click', function () {
                document.body.style.overflow = '';
            });
            modal.addEventListener('click', function (event) {
                if (event.target === modal) {
                    closeHealthFormActionModal();
                }
            });
            document.addEventListener('keydown', function (event) {
                if (event.key === 'Escape' && !modal.hidden) {
                    closeHealthFormActionModal();
                }
            });

            openAfterTermsGate();
        })();
    </script>
    @endif

    @if($showGlobalResubmissionModal)
    <div id="globalResubmissionModal" class="global-resubmission-modal" role="dialog" aria-modal="true" aria-labelledby="globalResubmissionTitle">
        <div class="global-resubmission-card">
            <button type="button" class="global-resubmission-close" aria-label="Close upload required files modal" onclick="closeGlobalResubmissionModal()">
                <x-outline-icon name="x-mark" />
            </button>
            <div class="global-resubmission-head">
                <span class="global-resubmission-head-icon" aria-hidden="true">
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75 11.25 15 15 9.75M12 3.75 4.5 7.5v5.25c0 4.125 2.55 7.8 6.405 9.255.705.267 1.485.267 2.19 0C16.95 20.55 19.5 16.875 19.5 12.75V7.5L12 3.75Z" />
                    </svg>
                </span>
                <div>
                    <h2 id="globalResubmissionTitle">Document Resubmission</h2>
                    <p>Replace only the requested documents. Previously approved files will remain unchanged.</p>
                </div>
            </div>
            <form action="{{ route('student.health_record.resubmit') }}" method="POST" enctype="multipart/form-data" class="global-resubmission-form">
                @csrf
                <div class="global-resubmission-progress-card">
                    <span class="global-resubmission-progress-icon" aria-hidden="true">
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75 11.25 15 15 9.75M6.75 3.75h10.5c.621 0 1.125.504 1.125 1.125v14.25c0 .621-.504 1.125-1.125 1.125H6.75a1.125 1.125 0 0 1-1.125-1.125V4.875c0-.621.504-1.125 1.125-1.125Z" />
                        </svg>
                    </span>
                    <div>
                        <span class="global-resubmission-progress-label">Upload Progress</span>
                        <span class="global-resubmission-progress-count"><span data-global-resubmission-selected>0</span> of <span data-global-resubmission-total>{{ $studentResubmissionDocuments->count() }}</span> files uploaded</span>
                    </div>
                    <div class="global-resubmission-progress-track" aria-hidden="true">
                        <span class="global-resubmission-progress-fill" data-global-resubmission-progress-fill></span>
                    </div>
                    <span class="global-resubmission-progress-percent" data-global-resubmission-percent>0%</span>
                </div>
                <div class="global-resubmission-note">
                    <span class="global-resubmission-note-icon" aria-hidden="true">
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M8.625 12h.008v.008h-.008V12Zm3.375 0h.008v.008H12V12Zm3.375 0h.008v.008h-.008V12ZM21 12c0 4.142-4.03 7.5-9 7.5a10.4 10.4 0 0 1-3.355-.54L3 20.25l1.505-4.012C3.55 15.024 3 13.568 3 12c0-4.142 4.03-7.5 9-7.5s9 3.358 9 7.5Z" />
                        </svg>
                    </span>
                    <div>
                        <strong>Clinic Note</strong>
                        <p>
                            {{ $studentResubmissionNote !== '' ? $studentResubmissionNote : 'Please review the requested document replacement.' }}
                        </p>
                    </div>
                </div>
                <div class="global-resubmission-docs">
                    @foreach($studentResubmissionDocuments as $documentKey)
                        @continue(!isset($studentResubmissionLabels[$documentKey]))
                        @php($documentMeta = $studentResubmissionMeta[$documentKey] ?? ['accept' => '', 'hint' => 'Upload the requested replacement file.'])
                        <div class="global-resubmission-doc" data-global-resubmission-card>
                            <div class="global-resubmission-doc-info">
                                <span class="global-resubmission-doc-icon" aria-hidden="true">
                                    @if($documentKey === 'student_photo')
                                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 6a3.75 3.75 0 1 1-7.5 0 3.75 3.75 0 0 1 7.5 0ZM4.5 20.25a7.5 7.5 0 0 1 15 0" />
                                        </svg>
                                    @elseif($documentKey === 'medical_certificate')
                                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M9 3.75h6m-6 0A2.25 2.25 0 0 0 6.75 6v12A2.25 2.25 0 0 0 9 20.25h6A2.25 2.25 0 0 0 17.25 18V6A2.25 2.25 0 0 0 15 3.75m-6 0v2.25h6V3.75M12 9v6m3-3H9" />
                                        </svg>
                                    @elseif($documentKey === 'chest_xray_result')
                                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16M8.25 5.25c-2.1 1.2-3.6 3.15-4.05 5.4-.45 2.25.3 4.8 2.25 7.65M15.75 5.25c2.1 1.2 3.6 3.15 4.05 5.4.45 2.25-.3 4.8-2.25 7.65M12 7.5c-2.7.15-4.95.9-6.75 2.25M12 10.5c-3.15.15-5.7 1.05-7.65 2.7M12 13.5c-2.85.15-5.1.9-6.75 2.25M12 16.5c-1.95.15-3.45.6-4.5 1.35M12 7.5c2.7.15 4.95.9 6.75 2.25M12 10.5c3.15.15 5.7 1.05 7.65 2.7M12 13.5c2.85.15 5.1.9 6.75 2.25M12 16.5c1.95.15 3.45.6 4.5 1.35" />
                                        </svg>
                                    @elseif($documentKey === 'pwd_id_proof')
                                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 7.5A3.75 3.75 0 1 1 12 3.75 3.75 3.75 0 0 1 15.75 7.5ZM4.5 20.25a7.5 7.5 0 0 1 15 0M18 14.25h2.25l-1.5 2.25H21" />
                                        </svg>
                                    @else
                                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 14.25v-2.625a3.375 3.375 0 0 0-3.375-3.375h-1.5A1.125 1.125 0 0 1 13.5 7.125v-1.5A3.375 3.375 0 0 0 10.125 2.25h-4.5C5.004 2.25 4.5 2.754 4.5 3.375v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V14.25Z" />
                                        </svg>
                                    @endif
                                </span>
                                <div>
                                    <span class="global-resubmission-doc-title">{{ $studentResubmissionLabels[$documentKey] }}</span>
                                    <span class="global-resubmission-needed">Needs Replacement</span>
                                    <span class="global-resubmission-doc-reason">Reason: {{ $studentResubmissionNote !== '' ? $studentResubmissionNote : 'Requested by the Medical Clinic.' }}</span>
                                    <span class="global-resubmission-doc-hint">Accepted: {{ $documentMeta['hint'] }}</span>
                                </div>
                            </div>
                            <div>
                                <label class="global-resubmission-upload-zone">
                                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" aria-hidden="true">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 16.5V9.75m0 0 2.25 2.25M12 9.75 9.75 12M4.5 15.75a4.5 4.5 0 0 1 4.5-4.5h.75A5.25 5.25 0 0 1 20.25 12 3.75 3.75 0 0 1 16.5 15.75H4.5Z" />
                                    </svg>
                                    <span class="global-resubmission-upload-copy">Drag & drop file here</span>
                                    <span class="global-resubmission-upload-or">or</span>
                                    <span class="global-resubmission-choose">Choose File</span>
                                    <span class="global-resubmission-preview" data-global-resubmission-preview>
                                        <span class="global-resubmission-thumb" data-global-resubmission-thumb></span>
                                        <span class="global-resubmission-file-name" data-global-resubmission-file-name></span>
                                        <span class="global-resubmission-ready">Ready</span>
                                        <button type="button" class="global-resubmission-remove" aria-label="Remove selected file" data-global-resubmission-remove>
                                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" d="m14.74 9-.346 9m-4.788 0L9.26 9m9.968-3.21c.342.052.682.107 1.022.166M4.772 5.79c.34-.059.68-.114 1.022-.165m0 0a48.11 48.11 0 0 1 3.478-.397m7.5 0v-.916c0-1.18-.91-2.164-2.09-2.201a51.964 51.964 0 0 0-3.32 0c-1.18.037-2.09 1.022-2.09 2.201v.916m7.5 0a48.667 48.667 0 0 0-7.5 0M6 5.79l1.068 13.883A2.25 2.25 0 0 0 9.312 21h5.376a2.25 2.25 0 0 0 2.244-2.077L18 5.79" />
                                            </svg>
                                        </button>
                                    </span>
                                    <input type="file" name="{{ $documentKey }}" accept="{{ $documentMeta['accept'] }}" required data-global-resubmission-input>
                                </label>
                            </div>
                            @error($documentKey)
                                <span class="global-resubmission-error">{{ $message }}</span>
                            @enderror
                        </div>
                    @endforeach
                </div>
                <div class="global-resubmission-footer">
                    <div class="global-resubmission-summary">
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" aria-hidden="true">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 14.25v-2.625a3.375 3.375 0 0 0-3.375-3.375h-1.5A1.125 1.125 0 0 1 13.5 7.125v-1.5A3.375 3.375 0 0 0 10.125 2.25h-4.5C5.004 2.25 4.5 2.754 4.5 3.375v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V14.25Z" />
                        </svg>
                        <div>
                            <strong><span data-global-resubmission-summary>0 of {{ $studentResubmissionDocuments->count() }}</span> files selected for replacement</strong>
                            <span>Please review your files before submitting.</span>
                        </div>
                    </div>
                    <div class="global-resubmission-actions">
                        <button type="button" class="global-resubmission-cancel" onclick="closeGlobalResubmissionModal()">Cancel</button>
                        <button type="submit" class="global-resubmission-submit" data-global-resubmission-submit disabled>Submit Replacement Files</button>
                    </div>
                </div>
                <p class="global-resubmission-secure">
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M16.5 10.5V6.75a4.5 4.5 0 1 0-9 0v3.75m-.75 11.25h10.5A2.25 2.25 0 0 0 19.5 19.5v-6.75a2.25 2.25 0 0 0-2.25-2.25H6.75a2.25 2.25 0 0 0-2.25 2.25v6.75a2.25 2.25 0 0 0 2.25 2.25Z" />
                    </svg>
                    Your files are secure and will only be used for verification purposes.
                </p>
            </form>
        </div>
    </div>
    <style>
        .global-resubmission-modal {
            position: fixed !important;
            inset: 0 !important;
            z-index: 999998 !important;
            display: flex !important;
            align-items: center !important;
            justify-content: center !important;
            padding: 24px;
            background: rgba(15, 23, 42, .58);
            backdrop-filter: blur(7px);
        }
        .global-resubmission-modal.is-closed {
            display: none !important;
        }
        .global-resubmission-card {
            position: relative;
            width: min(920px, 96vw);
            max-height: 90vh;
            overflow: hidden;
            border-radius: 22px;
            background: #ffffff;
            border-bottom: 3px solid #facc15;
            box-shadow: 0 28px 80px rgba(15, 23, 42, .32);
            display: flex;
            flex-direction: column;
        }
        .global-resubmission-head {
            display: flex;
            align-items: center;
            gap: 16px;
            padding: 24px 76px 22px 32px;
            background: linear-gradient(135deg, #70131b 0%, #8f2230 100%);
            color: #ffffff;
            flex: 0 0 auto;
            position: relative;
            z-index: 3;
        }
        .global-resubmission-head-icon {
            width: 54px;
            height: 54px;
            border-radius: 18px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            flex: 0 0 auto;
            color: #ffffff;
            background: rgba(255, 255, 255, .12);
            border: 1px solid rgba(255, 255, 255, .18);
        }
        .global-resubmission-head-icon svg {
            width: 28px;
            height: 28px;
        }
        .global-resubmission-head h2 {
            margin: 0 0 8px;
            color: #ffffff;
            font-size: 23px;
            font-weight: 900;
        }
        .global-resubmission-head p {
            margin: 0;
            max-width: 720px;
            color: #ffffff;
            font-size: 14px;
            line-height: 1.55;
        }
        .global-resubmission-close {
            position: absolute;
            top: 18px;
            right: 18px;
            z-index: 5;
            width: 42px;
            height: 42px;
            border-radius: 999px;
            border: 1px solid rgba(255, 255, 255, .18);
            background: rgba(127, 29, 45, .5);
            color: #ffffff;
            cursor: pointer;
            overflow: hidden;
            isolation: isolate;
            transition: border-color .22s ease, color .22s ease, background .22s ease;
        }
        .global-resubmission-close svg {
            position: relative;
            z-index: 1;
            width: 19px;
            height: 19px;
            stroke-width: 2.2;
        }
        .global-resubmission-close::before {
            position: absolute;
            z-index: -1;
            inset: 0;
            background: #facc15;
            content: "";
            transform: translateX(-102%);
            transition: transform .26s ease;
        }
        .global-resubmission-close:hover,
        .global-resubmission-close:focus-visible {
            border-color: #facc15;
            background: rgba(127, 29, 45, .5);
            color: #7f1d2d;
            outline: none;
        }
        .global-resubmission-close:hover::before,
        .global-resubmission-close:focus-visible::before {
            transform: translateX(0);
        }
        .global-resubmission-form {
            padding: 20px 24px 24px;
            overflow-y: auto;
            overflow-x: hidden;
            flex: 1 1 auto;
            min-height: 0;
        }
        .global-resubmission-progress-card {
            display: grid;
            grid-template-columns: auto auto minmax(0, 1fr) auto;
            align-items: center;
            gap: 18px;
            padding: 14px 18px;
            margin-bottom: 18px;
            border-radius: 12px;
            border: 1px solid rgba(112, 19, 27, .12);
            background: #ffffff;
            box-shadow: 0 12px 26px rgba(15, 23, 42, .06);
        }
        .global-resubmission-progress-icon,
        .global-resubmission-note-icon {
            width: 42px;
            height: 42px;
            border-radius: 12px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            flex: 0 0 auto;
        }
        .global-resubmission-progress-icon {
            color: #be123c;
            background: #fff1f2;
        }
        .global-resubmission-progress-icon svg,
        .global-resubmission-note-icon svg,
        .global-resubmission-doc-icon svg,
        .global-resubmission-remove svg {
            width: 20px;
            height: 20px;
        }
        .global-resubmission-progress-label {
            display: block;
            color: #64748b;
            font-size: 11px;
            font-weight: 900;
        }
        .global-resubmission-progress-count,
        .global-resubmission-progress-percent {
            display: block;
            color: #70131b;
            font-size: 13px;
            font-weight: 950;
        }
        .global-resubmission-progress-percent {
            min-width: 44px;
            font-size: 15px;
            text-align: right;
        }
        .global-resubmission-progress-track {
            height: 8px;
            border-radius: 999px;
            background: #f9dce0;
            overflow: hidden;
        }
        .global-resubmission-progress-fill {
            display: block;
            height: 100%;
            width: 0%;
            border-radius: inherit;
            background: linear-gradient(90deg, #70131b, #a2162b);
            transition: width .24s ease;
        }
        .global-resubmission-note {
            position: relative;
            display: flex;
            gap: 14px;
            margin-bottom: 22px;
            padding: 16px 18px;
            border-radius: 12px;
            border: 1px solid rgba(250, 204, 21, .55);
            background: linear-gradient(135deg, #fff8dd, #ffffff);
            overflow: hidden;
        }
        .global-resubmission-note::after {
            content: "";
            position: absolute;
            right: 18px;
            bottom: -12px;
            width: 84px;
            height: 70px;
            border: 3px solid rgba(250, 204, 21, .18);
            border-radius: 18px;
        }
        .global-resubmission-note-icon {
            color: #f59e0b;
            background: #ffffff;
            border: 1px solid rgba(250, 204, 21, .45);
        }
        .global-resubmission-note strong {
            display: block;
            margin-bottom: 5px;
            color: #70131b;
            font-size: 13px;
            font-weight: 950;
        }
        .global-resubmission-note p {
            margin: 0;
            color: #4b5563;
            font-size: 12px;
            font-weight: 750;
            line-height: 1.55;
            max-width: 680px;
        }
        .global-resubmission-docs {
            display: grid;
            gap: 14px;
        }
        .global-resubmission-doc {
            display: grid;
            grid-template-columns: minmax(0, 1fr) minmax(250px, .9fr);
            align-items: stretch;
            gap: 20px;
            padding: 18px;
            border-radius: 12px;
            border: 1px solid rgba(112, 19, 27, .12);
            background: #ffffff;
            box-shadow: 0 12px 26px rgba(15, 23, 42, .06);
        }
        .global-resubmission-doc-info {
            display: grid;
            grid-template-columns: 58px minmax(0, 1fr);
            gap: 16px;
            align-items: start;
            min-width: 0;
            padding-right: 8px;
            border-right: 1px solid rgba(112, 19, 27, .12);
        }
        .global-resubmission-doc-icon {
            width: 54px;
            height: 54px;
            border-radius: 14px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            background: #fff1f2;
            color: #be123c;
            box-shadow: 0 10px 20px rgba(112, 19, 27, .08);
        }
        .global-resubmission-doc-title {
            display: block;
            margin-bottom: 6px;
            color: #7f1d2d;
            font-size: 14px;
            font-weight: 900;
        }
        .global-resubmission-needed {
            display: inline-flex;
            align-items: center;
            gap: 5px;
            width: fit-content;
            margin-bottom: 10px;
            padding: 4px 8px;
            border-radius: 999px;
            background: #ffe4e6;
            color: #be123c;
            font-size: 10px;
            font-weight: 950;
        }
        .global-resubmission-needed::before {
            content: "";
            width: 6px;
            height: 6px;
            border-radius: 999px;
            background: currentColor;
        }
        .global-resubmission-doc-reason,
        .global-resubmission-doc-hint {
            display: block;
            color: #64748b;
            font-size: 12px;
            font-weight: 750;
            line-height: 1.45;
        }
        .global-resubmission-doc-hint {
            margin-top: 10px;
            font-size: 11px;
            font-weight: 850;
        }
        .global-resubmission-upload-zone {
            position: relative;
            min-height: 112px;
            border: 1px dashed rgba(190, 18, 60, .36);
            border-radius: 10px;
            background: linear-gradient(180deg, #fffafa, #ffffff);
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            gap: 5px;
            color: #70131b;
            text-align: center;
            cursor: pointer;
            transition: border-color .18s ease, box-shadow .18s ease, background .18s ease;
        }
        .global-resubmission-upload-zone:hover,
        .global-resubmission-upload-zone:focus-within,
        .global-resubmission-doc.has-file .global-resubmission-upload-zone {
            border-color: #facc15;
            background: #fffdf2;
            box-shadow: 0 0 0 4px rgba(250, 204, 21, .12);
        }
        .global-resubmission-doc.has-file .global-resubmission-upload-zone > svg,
        .global-resubmission-doc.has-file .global-resubmission-upload-copy,
        .global-resubmission-doc.has-file .global-resubmission-upload-or,
        .global-resubmission-doc.has-file .global-resubmission-choose {
            display: none;
        }
        .global-resubmission-upload-zone svg {
            width: 24px;
            height: 24px;
        }
        .global-resubmission-upload-copy {
            color: #70131b;
            font-size: 11px;
            font-weight: 850;
        }
        .global-resubmission-upload-or {
            color: #64748b;
            font-size: 10px;
            font-weight: 850;
        }
        .global-resubmission-upload-zone input[type="file"] {
            position: absolute;
            inset: 0;
            opacity: 0;
            cursor: pointer;
        }
        .global-resubmission-choose {
            position: relative;
            overflow: hidden;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            min-width: 92px;
            min-height: 28px;
            padding: 7px 12px;
            border: 1px solid #7f1d2d;
            border-radius: 6px;
            background: #7f1d2d;
            color: #ffffff;
            font-size: 11px;
            font-weight: 900;
            transition: border-color .18s ease, background .18s ease, color .18s ease, transform .18s ease, box-shadow .18s ease;
        }
        .global-resubmission-choose::after {
            position: absolute;
            top: -45%;
            bottom: -45%;
            left: -48%;
            width: 34%;
            pointer-events: none;
            opacity: 0;
            transform: skewX(-20deg);
            background: linear-gradient(90deg, transparent, rgba(255, 255, 255, .75), transparent);
            content: '';
        }
        .global-resubmission-upload-zone:hover .global-resubmission-choose,
        .global-resubmission-upload-zone:focus-within .global-resubmission-choose {
            border-color: #facc15;
            color: #70131b;
            background: #facc15;
            box-shadow: 0 7px 16px rgba(77, 16, 29, .16);
            transform: translateY(-1px);
        }
        .global-resubmission-upload-zone:hover .global-resubmission-choose::after,
        .global-resubmission-upload-zone:focus-within .global-resubmission-choose::after {
            animation: globalResubmissionChooseSweep .68s ease-out;
        }
        @keyframes globalResubmissionChooseSweep {
            0% { left: -48%; opacity: 0; }
            18% { opacity: .9; }
            100% { left: 120%; opacity: 0; }
        }
        .global-resubmission-preview {
            display: none;
            width: 100%;
            height: 100%;
            min-height: 96px;
            grid-template-columns: 54px minmax(0, 1fr) auto auto;
            align-items: center;
            gap: 10px;
            padding: 10px;
            color: #475569;
            font-size: 11px;
            font-weight: 800;
            text-align: left;
            pointer-events: none;
        }
        .global-resubmission-preview.is-visible {
            display: grid;
        }
        .global-resubmission-thumb {
            width: 54px;
            height: 54px;
            border-radius: 10px;
            border: 1px solid #f0c9ce;
            background: #fff7ed;
            color: #7f1d2d;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            overflow: hidden;
            font-size: 11px;
            font-weight: 950;
            text-transform: uppercase;
        }
        .global-resubmission-thumb img {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }
        .global-resubmission-file-name {
            min-width: 0;
            overflow: hidden;
            text-overflow: ellipsis;
            white-space: nowrap;
            color: #0f172a;
            font-weight: 900;
        }
        .global-resubmission-ready {
            color: #16a34a;
            font-weight: 900;
            white-space: nowrap;
        }
        .global-resubmission-remove {
            width: 26px;
            height: 26px;
            border: 0;
            border-radius: 8px;
            background: #fff1f2;
            color: #be123c;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            cursor: pointer;
            pointer-events: auto;
            transition: background .18s ease, color .18s ease, transform .18s ease;
        }
        .global-resubmission-remove:hover {
            background: #be123c;
            color: #ffffff;
            transform: translateY(-1px);
        }
        .global-resubmission-error {
            color: #dc2626;
            font-size: 12px;
            font-weight: 800;
        }
        .global-resubmission-footer {
            display: grid;
            grid-template-columns: minmax(0, 1fr) auto;
            gap: 18px;
            align-items: center;
            margin-top: 20px;
            padding-top: 18px;
            border-top: 1px solid rgba(112, 19, 27, .12);
        }
        .global-resubmission-summary {
            display: flex;
            align-items: center;
            gap: 12px;
            padding: 14px 16px;
            border-radius: 12px;
            border: 1px solid rgba(112, 19, 27, .12);
            background: #fff7f7;
        }
        .global-resubmission-summary svg {
            width: 24px;
            height: 24px;
            color: #be123c;
            flex: 0 0 auto;
        }
        .global-resubmission-summary strong,
        .global-resubmission-summary span {
            display: block;
        }
        .global-resubmission-summary strong {
            color: #70131b;
            font-size: 12px;
            font-weight: 950;
        }
        .global-resubmission-summary span {
            color: #64748b;
            font-size: 11px;
            font-weight: 750;
        }
        .global-resubmission-actions {
            display: flex;
            justify-content: flex-end;
            gap: 12px;
        }
        .global-resubmission-cancel,
        .global-resubmission-submit {
            min-height: 46px;
            padding: 0 22px;
            border-radius: 6px;
            font-size: 14px;
            font-weight: 900;
            cursor: pointer;
        }
        .global-resubmission-cancel {
            border: 1px solid rgba(112, 19, 27, .24);
            background: #ffffff;
            color: #70131b;
        }
        .global-resubmission-submit {
            position: relative;
            isolation: isolate;
            overflow: hidden;
            border: 1px solid #7f1d2d;
            background: #7f1d2d;
            color: #ffffff;
            transition: color .22s ease, border-color .22s ease;
        }
        .global-resubmission-submit:disabled {
            cursor: not-allowed;
            opacity: .58;
        }
        .global-resubmission-cancel:hover,
        .global-resubmission-cancel:focus-visible {
            border-color: #facc15;
            background: #facc15;
            color: #70131b;
            outline: none;
        }
        .global-resubmission-submit::before {
            position: absolute;
            z-index: -1;
            inset: 0;
            background: #facc15;
            content: "";
            transform: translateX(-102%);
            transition: transform .28s ease;
        }
        .global-resubmission-submit:hover,
        .global-resubmission-submit:focus-visible {
            border-color: #facc15;
            color: #7f1d2d;
            outline: none;
        }
        .global-resubmission-submit:disabled:hover::before,
        .global-resubmission-submit:disabled:focus-visible::before {
            transform: translateX(-102%);
        }
        .global-resubmission-submit:hover::before,
        .global-resubmission-submit:focus-visible::before {
            transform: translateX(0);
        }
        .global-resubmission-secure {
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 7px;
            margin: 16px 0 0;
            color: #64748b;
            font-size: 11px;
            font-weight: 750;
        }
        .global-resubmission-secure svg {
            width: 14px;
            height: 14px;
        }
        @media (max-width: 640px) {
            .global-resubmission-modal {
                align-items: flex-start !important;
                padding: 14px;
            }
            .global-resubmission-head {
                padding: 22px 64px 20px 20px;
            }
            .global-resubmission-progress-card,
            .global-resubmission-doc,
            .global-resubmission-footer {
                grid-template-columns: 1fr;
            }
            .global-resubmission-progress-percent {
                text-align: left;
            }
            .global-resubmission-doc-info {
                border-right: 0;
                border-bottom: 1px solid rgba(112, 19, 27, .12);
                padding-right: 0;
                padding-bottom: 14px;
            }
            .global-resubmission-actions {
                flex-direction: column;
            }
            .global-resubmission-cancel,
            .global-resubmission-submit {
                width: 100%;
            }
        }
    </style>
    <script>
        function closeGlobalResubmissionModal() {
            const modal = document.getElementById('globalResubmissionModal');
            if (!modal) {
                return;
            }
            modal.classList.add('is-closed');
            modal.setAttribute('aria-hidden', 'true');
            modal.style.setProperty('display', 'none', 'important');
            document.body.style.overflow = '';
        }
        window.openGlobalResubmissionModal = function () {
            const modal = document.getElementById('globalResubmissionModal');
            if (!modal) {
                return;
            }
            modal.classList.remove('is-closed');
            modal.setAttribute('aria-hidden', 'false');
            modal.style.setProperty('display', 'flex', 'important');
            document.body.style.overflow = 'hidden';
        };

        document.addEventListener('DOMContentLoaded', function () {
            const modal = document.getElementById('globalResubmissionModal');
            if (!modal) {
                return;
            }
            document.body.style.overflow = 'hidden';
            modal.querySelector('.global-resubmission-close')?.addEventListener('click', closeGlobalResubmissionModal);

            function updateGlobalResubmissionProgress() {
                const cards = Array.from(modal.querySelectorAll('[data-global-resubmission-card]'));
                const selected = cards.filter(function (card) {
                    const input = card.querySelector('[data-global-resubmission-input]');
                    return input && input.files && input.files.length > 0;
                }).length;
                const total = cards.length;
                const percent = total > 0 ? Math.round((selected / total) * 100) : 0;

                modal.querySelectorAll('[data-global-resubmission-selected]').forEach(function (target) {
                    target.textContent = selected;
                });
                modal.querySelectorAll('[data-global-resubmission-total]').forEach(function (target) {
                    target.textContent = total;
                });
                modal.querySelectorAll('[data-global-resubmission-summary]').forEach(function (target) {
                    target.textContent = selected + ' of ' + total;
                });
                modal.querySelectorAll('[data-global-resubmission-percent]').forEach(function (target) {
                    target.textContent = percent + '%';
                });
                modal.querySelectorAll('[data-global-resubmission-progress-fill]').forEach(function (target) {
                    target.style.width = percent + '%';
                });
                modal.querySelectorAll('[data-global-resubmission-submit]').forEach(function (button) {
                    button.disabled = selected < total;
                });
            }

            modal.querySelectorAll('[data-global-resubmission-input]').forEach(function (input) {
                const card = input.closest('[data-global-resubmission-card]');
                const preview = card?.querySelector('[data-global-resubmission-preview]');
                const thumb = card?.querySelector('[data-global-resubmission-thumb]');
                const fileName = card?.querySelector('[data-global-resubmission-file-name]');
                const removeButton = card?.querySelector('[data-global-resubmission-remove]');

                function syncFilePreview() {
                    const file = input.files && input.files[0] ? input.files[0] : null;
                    if (!preview || !card || !thumb || !fileName) return;

                    preview.classList.remove('is-visible');
                    thumb.replaceChildren();
                    if (!file) {
                        fileName.textContent = '';
                        card.classList.remove('has-file');
                        updateGlobalResubmissionProgress();
                        return;
                    }

                    fileName.textContent = file.name;
                    if (file.type && file.type.startsWith('image/')) {
                        const image = document.createElement('img');
                        image.alt = '';
                        image.src = URL.createObjectURL(file);
                        image.onload = function () {
                            URL.revokeObjectURL(image.src);
                        };
                        thumb.appendChild(image);
                    } else {
                        thumb.textContent = (file.name.split('.').pop() || 'file').slice(0, 4);
                    }

                    preview.classList.add('is-visible');
                    card.classList.add('has-file');
                    updateGlobalResubmissionProgress();
                }

                input.addEventListener('change', syncFilePreview);
                removeButton?.addEventListener('click', function () {
                    input.value = '';
                    syncFilePreview();
                });

                syncFilePreview();
            });

            updateGlobalResubmissionProgress();
        });
    </script>
    @endif
    @endauth
</body>
</html>
