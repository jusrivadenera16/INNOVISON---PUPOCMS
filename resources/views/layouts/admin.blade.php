<!DOCTYPE html>
<html lang="en">
<head>
    @php
        $tabIcon = 'CL';
        $tabAccent = '#70131B';
        $tabTitlePrefix = '';

        $medicalSettingsRoute = request()->routeIs('admin.reports.manage-mar') || request()->routeIs('admin.reports.manage-medicine-types');

        if (request()->routeIs('admin.dashboard') || request()->routeIs('assistant.dashboard')) {
            $tabIcon = 'DB';
            $tabAccent = '#7C3AED';
            $tabTitlePrefix = '[Dashboard] ';
        } elseif (request()->routeIs('admin.appointments*') || request()->routeIs('assistant.appointments*')) {
            $tabIcon = 'AP';
            $tabAccent = '#2563EB';
            $tabTitlePrefix = '[Appointments] ';
        } elseif (request()->routeIs('admin.inventory*') || request()->routeIs('assistant.inventory*')) {
            $tabIcon = 'IN';
            $tabAccent = '#059669';
            $tabTitlePrefix = '[Inventory] ';
        } elseif ((request()->routeIs('admin.settings*') || request()->is('admin/settings*') || $medicalSettingsRoute)) {
            $tabIcon = 'ST';
            $tabAccent = '#475569';
            $tabTitlePrefix = '[Settings] ';
        } elseif ((request()->routeIs('admin.reports*') || request()->routeIs('assistant.reports*') || request()->is('admin/reports*') || request()->is('assistant/reports*')) && !$medicalSettingsRoute) {
            $tabIcon = 'RP';
            $tabAccent = '#DC2626';
            $tabTitlePrefix = '[Reports] ';
        } elseif (request()->is('admin/walkin*') || request()->is('assistant/walkin*')) {
            $tabIcon = 'WK';
            $tabAccent = '#EA580C';
            $tabTitlePrefix = '[Walk-In] ';
        } elseif (request()->routeIs('admin.health_records') || request()->is('health-records') || request()->is('health-profile/*')) {
            $tabIcon = 'HF';
            $tabAccent = '#0F766E';
            $tabTitlePrefix = '[Health Form] ';
        } elseif (request()->routeIs('admin.user-management*') || request()->is('admin/user-management*')) {
            $tabIcon = 'UM';
            $tabAccent = '#9333EA';
            $tabTitlePrefix = '[Users] ';
        } elseif (request()->routeIs('admin.logs') || request()->is('admin/activity-logs*')) {
            $tabIcon = 'AT';
            $tabAccent = '#B45309';
            $tabTitlePrefix = '[Audit Trail] ';
        } elseif (request()->routeIs('admin.announcements')) {
            $tabIcon = 'AN';
            $tabAccent = '#BE123C';
            $tabTitlePrefix = '[Announcements] ';
        }

        $tabIconSvg = "<svg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 64 64'>"
            . "<rect width='64' height='64' rx='18' fill='{$tabAccent}'/>"
            . "<rect x='5' y='5' width='54' height='54' rx='15' fill='none' stroke='#FACC15' stroke-width='3'/>"
            . "<text x='32' y='39' text-anchor='middle' font-family='Arial, sans-serif' font-size='20' font-weight='700' fill='#FFFFFF'>{$tabIcon}</text>"
            . "</svg>";
        $tabIconData = 'data:image/svg+xml;utf8,' . rawurlencode($tabIconSvg);
    @endphp
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ $tabTitlePrefix }}@yield('title') - PUPT Admin</title>
    <link rel="icon" type="image/svg+xml" href="{{ $tabIconData }}">
    <link rel="alternate icon" href="{{ asset('favicon.ico') }}">
    <script
        src="{{ asset('js/sienna-accessibility-custom.umd.js') }}?v={{ filemtime(public_path('js/sienna-accessibility-custom.umd.js')) }}"
        data-asw-position="bottom-right"
        data-asw-offset="24,12"
        defer
    ></script>
    <script>
        (function() {
            try {
                var savedTheme = localStorage.getItem('admin_theme');
                var theme = savedTheme === 'light' ? 'light' : 'dark';
                document.documentElement.setAttribute('data-theme', theme);
            } catch (error) {
                document.documentElement.setAttribute('data-theme', 'dark');
            }
        })();
    </script>

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Manrope:wght@400;500;600;700;800&family=Outfit:wght@600;700&display=swap" rel="stylesheet">

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
            left: -9999px !important;
            right: auto !important;
            bottom: auto !important;
            top: auto !important;
            opacity: 0 !important;
            visibility: hidden !important;
            pointer-events: none !important;
            transform: none !important;
            z-index: -1 !important;
            overflow: visible !important;
            background: #800000 !important;
            background-image: none !important;
            border: 2px solid #5f0012 !important;
            outline: none !important;
            box-shadow: 0 10px 24px rgba(128, 0, 0, 0.28) !important;
        }

        :where(.asw-menu-btn.admin-sienna-trigger-hidden),
        :where(.sienna-accessibility-button.admin-sienna-trigger-hidden),
        :where(.sienna-accessibility-trigger.admin-sienna-trigger-hidden),
        :where([data-sienna-accessibility-trigger].admin-sienna-trigger-hidden) {
            position: fixed !important;
            left: -9999px !important;
            right: auto !important;
            bottom: auto !important;
            top: auto !important;
            opacity: 0 !important;
            visibility: hidden !important;
            pointer-events: none !important;
            transform: none !important;
            z-index: -1 !important;
        }

        :where(.admin-sienna-trigger-hidden)::after {
            display: none !important;
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

        .medicine-alert-fab {
            position: fixed;
            right: 96px;
            bottom: 12px;
            z-index: 499999;
            width: 58px;
            height: 58px;
            border-radius: 999px;
            border: 2px solid #5f0012;
            background: linear-gradient(145deg, #8b0000, #5f0012);
            color: #ffffff;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            box-shadow: 0 10px 24px rgba(128, 0, 0, 0.24);
            cursor: pointer;
            transition: transform 0.18s ease, box-shadow 0.18s ease;
        }

        .medicine-alert-fab[data-surface-tone="dark"] {
            background: linear-gradient(145deg, #d1d5db, #9ca3af);
            border-color: #6b7280;
            color: #5f0012;
            box-shadow: 0 10px 24px rgba(100, 116, 139, 0.26);
        }

        .medicine-alert-fab[data-surface-tone="dark"]:hover {
            box-shadow: 0 14px 28px rgba(100, 116, 139, 0.3);
        }

        .medicine-alert-fab:hover {
            transform: translateY(-2px);
            box-shadow: 0 14px 28px rgba(128, 0, 0, 0.3);
        }

        .medicine-alert-fab svg {
            width: 28px;
            height: 28px;
            display: block;
            stroke: currentColor;
            fill: none;
            stroke-width: 1.8;
            stroke-linecap: round;
            stroke-linejoin: round;
            transition: transform 0.2s ease;
        }

        .medicine-alert-fab:hover svg {
            transform: scale(1.08);
        }

        .medicine-alert-badge {
            position: absolute;
            top: -5px;
            right: -3px;
            min-width: 22px;
            height: 22px;
            padding: 0 6px;
            border-radius: 999px;
            background: #ffb81c;
            color: #4a1500;
            font-size: 11px;
            font-weight: 800;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            border: 2px solid #fff4d4;
        }

        .medicine-alert-panel {
            position: fixed;
            right: 96px;
            bottom: 82px;
            z-index: 499998;
            width: min(360px, calc(100vw - 32px));
            max-width: calc(100vw - 32px);
            border-radius: 20px;
            background: rgba(255, 248, 249, 0.68);
            border: 1px solid rgba(250, 204, 21, 0.48);
            box-shadow: 0 22px 48px rgba(15, 23, 42, 0.18);
            padding: 18px;
            display: block;
            overflow: hidden;
            box-sizing: border-box;
            backdrop-filter: blur(18px) saturate(155%);
            -webkit-backdrop-filter: blur(18px) saturate(155%);
            opacity: 0;
            transform: translateY(16px) scale(0.96);
            transform-origin: bottom right;
            pointer-events: none;
            visibility: hidden;
            transition:
                opacity 0.22s ease,
                transform 0.24s ease,
                visibility 0s linear 0.24s;
        }

        .medicine-alert-panel.is-open {
            opacity: 1;
            transform: translateY(0) scale(1);
            pointer-events: auto;
            visibility: visible;
            transition:
                opacity 0.22s ease,
                transform 0.24s ease,
                visibility 0s linear 0s;
        }

        .medicine-alert-panel.is-closing {
            opacity: 0;
            transform: translateY(16px) scale(0.96);
            pointer-events: none;
            visibility: visible;
        }

        .medicine-alert-head {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            gap: 12px;
            margin-bottom: 14px;
            padding: 0 0 12px;
            border-bottom: 1px solid rgba(127, 29, 45, 0.12);
            position: relative;
            z-index: 1;
            background: #fffaf2;
            border-radius: 14px;
            padding: 12px 12px 14px;
        }

        .medicine-alert-close {
            flex: 0 0 auto;
            width: 34px;
            height: 34px;
            border-radius: 999px;
            border: 1px solid rgba(250, 204, 21, 0.34);
            background: #fff7e6;
            color: #7f1d2d;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            cursor: pointer;
            transition: transform 0.18s ease, background 0.18s ease, border-color 0.18s ease;
        }

        .medicine-alert-close:hover {
            transform: scale(1.05);
            background: #fff2cc;
            border-color: rgba(250, 204, 21, 0.52);
        }

        .medicine-alert-close svg {
            width: 18px;
            height: 18px;
            stroke: currentColor;
            stroke-width: 2;
        }

        .medicine-alert-title {
            margin: 0;
            font-size: 16px;
            font-weight: 800;
            color: #7f1d2d;
            line-height: 1.2;
        }

        .medicine-alert-subtitle {
            margin: 4px 0 0;
            font-size: 12px;
            color: #475569;
            line-height: 1.45;
        }

        .medicine-alert-list {
            display: grid;
            gap: 10px;
            max-height: 320px;
            overflow-y: auto;
        }

.medicine-alert-item {
     border-radius: 16px;
     padding: 12px 14px;
     background: #fffaf7;
      border: 1px solid rgba(127, 29, 45, 0.08);
      min-width: 0;
      max-width: 100%;
      overflow: hidden;
  }
  
.medicine-alert-item-link {
      display: block;
      color: inherit;
      text-decoration: none;
      min-width: 0;
      max-width: 100%;
      overflow: hidden;
      background: inherit;
      border-radius: inherit;
  }

  .medicine-alert-item-link:hover .medicine-alert-item-name {
      text-decoration: underline;
  }

  .medicine-alert-actions {
      position: relative;
      display: inline-flex;
      align-items: center;
      gap: 10px;
  }

  .medicine-alert-actions-toggle {
      display: inline-flex;
      align-items: center;
      justify-content: center;
      width: 38px;
      height: 38px;
      border-radius: 999px;
      border: 1px solid rgba(127, 29, 45, 0.14);
      background: rgba(255, 255, 255, 0.96);
      color: #7f1d2d;
      cursor: pointer;
      transition: transform 0.18s ease, box-shadow 0.18s ease, border-color 0.18s ease;
  }

  .medicine-alert-actions-toggle,
  .medicine-alert-close {
      color: #111111;
  }

  .medicine-alert-actions-toggle:hover {
      transform: translateY(-1px);
      box-shadow: 0 10px 20px rgba(15, 23, 42, 0.12);
      border-color: rgba(250, 204, 21, 0.55);
  }

  .medicine-alert-actions-toggle svg {
      width: 18px;
      height: 18px;
  }

  .medicine-alert-actions-menu {
      position: absolute;
      top: calc(100% + 8px);
      right: 0;
      min-width: 200px;
      padding: 8px;
      border-radius: 16px;
      background: rgba(255, 255, 255, 0.98);
      border: 1px solid rgba(127, 29, 45, 0.12);
      box-shadow: 0 18px 28px rgba(15, 23, 42, 0.16);
      display: none;
      z-index: 4;
  }

  .medicine-alert-actions-menu.is-open {
      display: block;
  }

  .medicine-alert-actions-menu form {
      margin: 0;
  }

  .medicine-alert-actions-link,
  .medicine-alert-actions-submit {
      width: 100%;
      display: flex;
      align-items: center;
      justify-content: flex-start;
      gap: 10px;
      padding: 11px 12px;
      border-radius: 12px;
      border: 0;
      background: transparent;
      color: #111827;
      text-decoration: none;
      font-size: 13px;
      font-weight: 700;
      cursor: pointer;
      transition: background 0.18s ease, color 0.18s ease;
      text-align: left;
  }

  .medicine-alert-actions-link svg,
  .medicine-alert-actions-submit svg {
      width: 16px;
      height: 16px;
      flex: 0 0 auto;
      stroke-width: 2;
      opacity: 0.9;
  }

  .medicine-alert-actions-link:hover,
  .medicine-alert-actions-submit:hover {
      background: #fff7ea;
      color: #7f1d2d;
  }

  .medicine-alert-actions-submit:disabled {
      opacity: 0.52;
      cursor: not-allowed;
  }

  .medicine-alert-empty {
      padding: 18px 16px;
      border-radius: 16px;
      border: 1px dashed rgba(127, 29, 45, 0.18);
      background: rgba(255, 255, 255, 0.98);
      text-align: center;
  }

  .medicine-alert-empty-title {
      margin: 0;
      font-size: 14px;
      font-weight: 800;
      color: #111827;
  }

  .medicine-alert-empty-copy {
      margin: 6px 0 0;
      font-size: 12px;
      line-height: 1.55;
      color: #64748b;
  }

.medicine-alert-item-link[data-hover-hint]:hover {
    cursor: pointer;
}

.medicine-hover-hint {
    position: fixed;
    z-index: 500001;
    pointer-events: none;
    transform: translate(14px, 14px);
    background: rgba(17, 24, 39, 0.92);
    color: #f8fafc;
    padding: 6px 10px;
    border-radius: 999px;
    font-size: 12px;
    font-weight: 800;
    letter-spacing: 0.02em;
    max-width: min(320px, calc(100vw - 24px));
    white-space: normal;
    line-height: 1.45;
    box-shadow: 0 10px 22px rgba(15, 23, 42, 0.22);
    opacity: 0;
    visibility: hidden;
    transition: opacity 0.15s ease;
}

.medicine-hover-hint.is-visible {
    opacity: 1;
    visibility: visible;
}

        .medicine-alert-item.is-near-expiry {
            background: #fef3c7;
            border-color: rgba(245, 158, 11, 0.26);
        }

        .medicine-alert-item.is-expired {
            background: #fee2e2;
            border-color: rgba(220, 38, 38, 0.22);
        }

        .medicine-alert-item-name {
            margin: 0;
            font-size: 14px;
            font-weight: 800;
            color: #111827;
        }

        .medicine-alert-item-meta {
            display: flex;
            flex-wrap: wrap;
            gap: 8px;
            margin-top: 8px;
        }

        .medicine-alert-chip {
            display: inline-flex;
            align-items: center;
            padding: 5px 9px;
            border-radius: 999px;
            background: #ffffff;
            border: 1px solid rgba(127, 29, 45, 0.12);
            color: #334155;
            font-size: 11px;
            font-weight: 700;
        }

        .medicine-alert-empty {
            margin: 0;
            color: #475569;
            font-size: 13px;
        }

        html[data-theme="dark"] .medicine-alert-panel {
            background: rgba(35, 17, 25, 0.72);
            border-color: rgba(250, 204, 21, 0.42);
        }

        html[data-theme="dark"] .medicine-alert-head {
            border-bottom-color: rgba(255, 255, 255, 0.1);
            background: #2f161d;
        }

        html[data-theme="dark"] .medicine-alert-close {
            background: #3a1a23;
            color: #f8fafc;
            border-color: rgba(250, 204, 21, 0.28);
        }

        html[data-theme="dark"] .medicine-alert-close:hover {
            background: #4a212c;
            border-color: rgba(250, 204, 21, 0.46);
        }

        html[data-theme="dark"] .medicine-alert-title {
            color: #f3d6da;
        }

        html[data-theme="dark"] .medicine-alert-subtitle,
        html[data-theme="dark"] .medicine-alert-empty {
            color: #cbd5e1;
        }

        html[data-theme="dark"] .medicine-alert-item {
            background: #241117;
            border-color: rgba(255, 255, 255, 0.08);
        }

        html[data-theme="dark"] .medicine-alert-item.is-near-expiry {
            background: #4a3412;
            border-color: rgba(253, 224, 71, 0.22);
        }

        html[data-theme="dark"] .medicine-alert-item.is-expired {
            background: #4a171b;
            border-color: rgba(252, 165, 165, 0.22);
        }

        html[data-theme="dark"] .medicine-alert-item-name {
            color: #f8fafc;
        }

        html[data-theme="dark"] .medicine-alert-chip {
            background: rgba(255, 255, 255, 0.08);
            border-color: rgba(255, 255, 255, 0.1);
            color: #f8fafc;
        }

        :root {
            --bg: #2a0e16;
            --bg-grad-1: #5b1a2a;
            --bg-grad-2: #3a111c;
            --surface: #5a1d2a;
            --surface-soft: #733242;
            --stroke: #8d4c5b;
            --stroke-strong: #a86373;
            --text: #fff0f4;
            --muted: #f0c5cf;
            --pup-maroon: #800000;
            --pup-maroon-dark: #5f0012;
            --pup-maroon-soft: #f1d9df;
            --pup-gold: #ffb81c;
            --danger: #ff9cb0;
            --shadow-soft: 0 14px 30px rgba(22, 5, 10, 0.32);
            --radius-xl: 22px;
            --radius-lg: 16px;
            --radius-md: 12px;
            --sidebar-collapsed-width: 86px;
            --sidebar-expanded-width: 272px;
            --admin-shell-bg: linear-gradient(180deg, rgba(112, 19, 35, 0.24) 0%, rgba(62, 7, 18, 0.34) 100%);
            --admin-shell-border: rgba(255, 255, 255, 0.14);
            --admin-card-bg: linear-gradient(165deg, rgba(111, 19, 35, 0.96) 0%, rgba(74, 11, 23, 0.98) 100%);
            --admin-card-border: rgba(255, 255, 255, 0.12);
            --admin-card-shadow: 0 16px 34px rgba(10, 2, 5, 0.28);
            --admin-card-text: #fff4f7;
            --admin-heading: #fff9fb;
            --admin-link: #ffd7df;
            --admin-link-hover: #fff0f4;
            --admin-table-head-border: rgba(255, 255, 255, 0.14);
            --admin-table-head-text: #ffd3dc;
            --admin-table-head-bg: rgba(255, 255, 255, 0.08);
            --admin-table-body-border: rgba(255, 255, 255, 0.1);
            --admin-table-body-text: #fff1f4;
            --admin-input-border: rgba(255, 255, 255, 0.16);
            --admin-input-bg: rgba(255, 255, 255, 0.08);
            --admin-input-text: #fff7fa;
            --admin-input-placeholder: rgba(255, 241, 244, 0.64);
            --admin-input-focus-shadow: 0 0 0 3px rgba(255, 184, 28, 0.18);
            --admin-primary-btn-bg: #8b0000;
            --admin-primary-btn-border: #8b0000;
            --admin-primary-btn-hover: #6f0015;
            --admin-secondary-btn-bg: rgba(255, 255, 255, 0.12);
            --admin-secondary-btn-text: #fff3f6;
            --admin-secondary-btn-border: rgba(255, 255, 255, 0.18);
            --admin-secondary-btn-hover: rgba(255, 255, 255, 0.18);
            --admin-danger-btn-bg: rgba(255, 216, 223, 0.12);
            --admin-danger-btn-text: #ffdbe3;
            --admin-danger-btn-border: rgba(255, 216, 223, 0.24);
            --admin-status-completed-bg: rgba(255, 255, 255, 0.12);
            --admin-status-completed-text: #fff8fb;
            --admin-sidebar-bg:
                linear-gradient(180deg, rgba(140, 72, 89, 0.18) 0%, rgba(98, 33, 47, 0.24) 100%),
                linear-gradient(180deg, #281217 0%, #160a0e 100%);
            --admin-sidebar-border: rgba(255, 255, 255, 0.08);
            --admin-sidebar-divider: rgba(255, 255, 255, 0.16);
            --admin-sidebar-title: #ffffff;
            --admin-sidebar-muted: rgba(255, 255, 255, 0.68);
            --admin-sidebar-text: rgba(255, 255, 255, 0.9);
            --admin-sidebar-hover-bg: rgba(255, 255, 255, 0.1);
            --admin-sidebar-hover-border: rgba(255, 255, 255, 0.2);
            --admin-sidebar-active-bg: rgba(255, 255, 255, 0.1);
            --admin-sidebar-active-border: rgba(255, 255, 255, 0.2);
            --admin-sidebar-short-bg: rgba(255, 255, 255, 0.08);
            --admin-sidebar-short-border: rgba(255, 255, 255, 0.2);
            --admin-sidebar-logout-bg: rgba(0, 0, 0, 0.18);
            --admin-sidebar-logout-border: rgba(255, 255, 255, 0.2);
            --admin-sidebar-indicator-bg: rgba(255, 255, 255, 0.08);
            --admin-sidebar-indicator-border: rgba(255, 255, 255, 0.14);
            --admin-brand-logo-bg: rgba(255, 255, 255, 0.96);
            --admin-brand-logo-border: rgba(255, 255, 255, 0.18);
        }

        * { box-sizing: border-box; }

        html,
        body {
            height: 100%;
        }

        body {
            margin: 0;
            font-family: "Manrope", "Segoe UI", "Inter", "Helvetica Neue", Arial, sans-serif;
            background:
                radial-gradient(circle at -10% -10%, rgba(255, 255, 255, 0.09) 0%, transparent 42%),
                radial-gradient(circle at 110% 120%, rgba(255, 184, 28, 0.08) 0%, transparent 36%),
                linear-gradient(180deg, var(--bg-grad-2) 0%, var(--bg) 80%);
            color: var(--text);
            height: 100vh;
            overflow: hidden;
            display: flex;
            flex-direction: column;
        }

        .admin-header {
            position: sticky;
            top: 0;
            background: linear-gradient(180deg, #231119 0%, #180b12 100%);
            backdrop-filter: none;
            border-bottom: 1px solid rgba(255, 255, 255, 0.12);
            box-shadow: inset 0 -14px 28px rgba(0, 0, 0, 0.18);
            padding: 14px clamp(16px, 3vw, 30px);
            display: flex;
            justify-content: space-between;
            align-items: center;
            gap: 16px;
            flex-shrink: 0;
            overflow: visible;
            z-index: 70;
        }

        .admin-header::after {
            content: "";
            position: absolute;
            top: 10%;
            left: -24%;
            width: 36%;
            height: 80%;
            border-radius: 999px;
            pointer-events: none;
            opacity: 0;
            background: linear-gradient(
                105deg,
                rgba(255, 255, 255, 0) 0%,
                rgba(145, 42, 68, 0.10) 18%,
                rgba(188, 70, 98, 0.38) 50%,
                rgba(145, 42, 68, 0.14) 80%,
                rgba(255, 255, 255, 0) 100%
            );
            filter: blur(1px);
            transform: translateX(-150%) skewX(-24deg);
            transform-origin: center;
            animation: adminHeaderReflection 9s ease-in-out infinite;
        }

        @keyframes adminHeaderReflection {
            0% {
                opacity: 0;
                transform: translateX(-150%) skewX(-24deg);
            }
            8% {
                opacity: 0.28;
            }
            56% {
                opacity: 0.48;
                transform: translateX(360%) skewX(-24deg);
            }
            62% {
                opacity: 0;
                transform: translateX(385%) skewX(-24deg);
            }
            100% {
                opacity: 0;
                transform: translateX(385%) skewX(-24deg);
            }
        }

        .header-left {
            min-width: 0;
            display: flex;
            align-items: center;
            gap: 12px;
        }

        .header-brand-lockup {
            display: flex;
            align-items: center;
            gap: 10px;
            flex-shrink: 0;
        }

        .header-brand-avatar {
            width: 52px;
            height: 52px;
            border-radius: 50%;
            object-fit: contain;
            background: var(--admin-brand-logo-bg);
            border: 1px solid var(--admin-brand-logo-border);
            padding: 5px;
            box-shadow: 0 10px 18px rgba(15, 23, 42, 0.16);
            flex-shrink: 0;
        }

        .header-brand-avatar--clinic {
            padding: 4px;
        }

        .header-copy {
            min-width: 0;
        }

        .header-kicker {
            margin: 0 0 4px;
            color: var(--muted);
            font-size: 11px;
            text-transform: uppercase;
            letter-spacing: 0.08em;
            font-weight: 700;
        }

        .header-title {
            margin: 0;
            font-family: "Outfit", "Manrope", sans-serif;
            font-size: clamp(18px, 2vw, 24px);
            line-height: 1.2;
            font-weight: 700;
            letter-spacing: -0.02em;
            color: var(--text);
        }

        .header-title span {
            color: var(--pup-gold);
        }

        .header-subtitle {
            margin: 4px 0 0;
            color: var(--muted);
            font-size: 13px;
            font-weight: 500;
        }

        .header-right {
            display: flex;
            align-items: center;
            gap: 12px;
            flex-shrink: 0;
            overflow: visible;
        }

        .sidebar-toggle {
            display: none;
            width: 40px;
            height: 40px;
            border-radius: 10px;
            border: 1px solid var(--stroke);
            background: var(--surface);
            color: var(--text);
            align-items: center;
            justify-content: center;
            cursor: pointer;
            transition: background 0.2s ease, border-color 0.2s ease, transform 0.2s ease;
        }

        .sidebar-toggle svg {
            width: 20px;
            height: 20px;
            stroke-width: 1.8;
            flex: 0 0 auto;
        }

        .profile-wrap {
            position: relative;
            overflow: visible;
            z-index: 80;
        }

        .quick-actions-wrap {
            position: fixed;
            right: 24px;
            bottom: 18px;
            display: flex;
            align-items: center;
            z-index: 499997;
        }

        .quick-actions-toggle,
        .quick-action-btn,
        .quick-action-logo {
            width: 66px;
            height: 66px;
            border-radius: 999px;
            border: 2px solid #facc15;
            background: linear-gradient(145deg, #9b111e, #6e1220 55%, #4f0b15);
            color: #ffffff;
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

        .quick-actions-toggle,
        .quick-action-btn {
            cursor: pointer;
        }

        .quick-action-btn,
        .quick-action-logo {
            width: 66px;
            height: 66px;
            border-radius: 999px;
            border: 2px solid #facc15;
            background: linear-gradient(145deg, #9b111e, #6e1220 55%, #4f0b15);
            box-shadow:
                0 0 0 3px rgba(250, 204, 21, 0.12),
                0 0 18px rgba(250, 204, 21, 0.26),
                0 10px 22px rgba(95, 0, 18, 0.28);
        }

        .quick-actions-toggle {
            width: 66px;
            height: 66px;
            border-radius: 999px;
            background: linear-gradient(145deg, #9b111e, #6e1220 55%, #4f0b15);
            border: 2px solid #facc15;
            box-shadow:
                0 0 0 3px rgba(250, 204, 21, 0.12),
                0 0 18px rgba(250, 204, 21, 0.32),
                0 12px 26px rgba(95, 0, 18, 0.34);
            animation: quickActionsGlow 2.2s ease-in-out infinite;
        }

        .quick-actions-toggle:hover,
        .quick-action-btn:hover {
            background: linear-gradient(145deg, #b01826, #7f1d2d 55%, #5a0f16);
            border-color: #fde047;
            transform: translateY(-2px) scale(1.02);
            box-shadow:
                0 0 0 3px rgba(250, 204, 21, 0.18),
                0 0 22px rgba(250, 204, 21, 0.34),
                0 14px 28px rgba(95, 0, 18, 0.36);
        }

        .quick-action-btn:hover {
            background: linear-gradient(145deg, #b01826, #7f1d2d 55%, #5a0f16);
            border-color: #fde047;
            box-shadow:
                0 0 0 3px rgba(250, 204, 21, 0.18),
                0 0 22px rgba(250, 204, 21, 0.34),
                0 14px 28px rgba(95, 0, 18, 0.36);
            transform: translateY(-1px) scale(1.04);
        }

        .quick-action-item:hover .quick-action-btn,
        .quick-action-item:hover .quick-action-logo {
            box-shadow:
                0 0 0 3px rgba(250, 204, 21, 0.18),
                0 0 10px rgba(255, 0, 102, 0.34),
                0 0 18px rgba(0, 200, 255, 0.32),
                0 0 26px rgba(255, 221, 0, 0.3),
                0 14px 28px rgba(95, 0, 18, 0.36) !important;
        }

        .quick-action-item:hover .quick-action-btn svg,
        .quick-action-item:hover .quick-action-logo img {
            animation: quickActionShake 0.42s ease-in-out;
            filter:
                drop-shadow(0 0 6px rgba(255, 0, 102, 0.45))
                drop-shadow(0 0 10px rgba(0, 200, 255, 0.38))
                drop-shadow(0 0 14px rgba(255, 221, 0, 0.42));
        }

        .quick-action-item:hover .accessibility-launch-admin svg {
            animation: quickActionShakeAccessibility 0.42s ease-in-out;
        }

        .quick-actions-toggle:hover {
            background: linear-gradient(145deg, #b01826, #7f1d2d 55%, #5a0f16);
            border-color: #fde047;
            box-shadow:
                0 0 0 3px rgba(250, 204, 21, 0.18),
                0 0 22px rgba(250, 204, 21, 0.4),
                0 16px 30px rgba(95, 0, 18, 0.42);
            transform: translateY(-2px) scale(1.02);
        }

        .quick-actions-toggle:focus-visible,
        .quick-action-btn:focus-visible {
            outline: 2px solid var(--pup-gold);
            outline-offset: 2px;
        }

        .quick-actions-toggle svg,
        .quick-action-btn svg {
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

        .accessibility-launch-admin svg {
            width: 45px;
            height: 45px;
            stroke-width: 2.4;
            transform: translateY(-1px) scale(1.18);
            transform-origin: center;
            overflow: visible;
        }

        .quick-action-bell svg {
            width: 20px;
            height: 20px;
        }

        .quick-action-scan {
            position: relative;
        }

        .quick-scan-menu {
            position: absolute;
            right: calc(100% + 14px);
            top: 50%;
            width: 190px;
            display: grid;
            gap: 8px;
            padding: 10px;
            border: 1px solid rgba(250, 204, 21, 0.45);
            border-radius: 12px;
            background: #70131b;
            box-shadow: 0 16px 32px rgba(15, 23, 42, 0.28);
            opacity: 0;
            visibility: hidden;
            transform: translate(8px, -50%) scale(0.96);
            transition: opacity 0.18s ease, visibility 0.18s ease, transform 0.18s ease;
            z-index: 1010;
        }

        .quick-action-scan.is-open .quick-scan-menu {
            opacity: 1;
            visibility: visible;
            transform: translate(0, -50%) scale(1);
        }

        .quick-scan-choice {
            width: 100%;
            min-height: 40px;
            display: flex;
            align-items: center;
            gap: 9px;
            padding: 9px 11px;
            border: 1px solid rgba(255, 255, 255, 0.18);
            border-radius: 8px;
            background: rgba(255, 255, 255, 0.09);
            color: #ffffff;
            font: inherit;
            font-size: 12px;
            font-weight: 800;
            text-align: left;
            cursor: default;
        }

        .quick-scan-choice svg {
            width: 17px;
            height: 17px;
            flex: 0 0 auto;
        }

        .quick-scan-choice:hover {
            background: #facc15;
            border-color: #facc15;
            color: #70131b;
        }

        .quick-actions-wrap.is-open .quick-actions-toggle svg {
            transform: rotate(135deg) scale(1.04);
        }

        .quick-actions-panel {
            position: absolute;
            left: 50%;
            bottom: calc(100% + 16px);
            display: flex;
            flex-direction: column;
            align-items: center;
            gap: 4px;
            padding: 6px 0;
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
        }

        .quick-actions-wrap.is-open .quick-actions-panel {
            opacity: 1;
            visibility: visible;
            transform: translateX(-50%) translateY(0) scale(1);
        }

        .quick-actions-panel::after {
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
        }

        .quick-actions-panel::before {
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
        }

        .quick-actions-wrap.is-open .quick-actions-panel::before,
        .quick-actions-wrap.is-open .quick-actions-panel::after {
            opacity: 1;
            visibility: visible;
        }

        .quick-action-logo {
            overflow: hidden;
            border-radius: 999px;
            background: #ffffff !important;
            border: 2px solid #7f1d2d;
            box-shadow:
                0 0 0 3px #ffffff,
                0 0 0 5px #facc15,
                0 10px 22px rgba(95, 0, 18, 0.28);
        }

        .quick-actions-divider {
            display: none;
        }

        .quick-action-item {
            position: relative;
            display: inline-flex;
            opacity: 0;
            transform: translateY(18px) scale(0.86);
            transition: opacity 0.22s ease, transform 0.28s cubic-bezier(0.22, 1, 0.36, 1);
        }

        .quick-actions-wrap.is-open .quick-action-item {
            opacity: 1;
            transform: translateY(0) scale(1);
        }

        .quick-actions-wrap.is-open .quick-action-item:nth-child(1) {
            transition-delay: 0.19s;
        }

        .quick-actions-wrap.is-open .quick-action-item:nth-child(3) {
            transition-delay: 0.14s;
        }

        .quick-actions-wrap.is-open .quick-action-item:nth-child(4) {
            transition-delay: 0.09s;
        }

        .quick-actions-wrap.is-open .quick-action-item:nth-child(5) {
            transition-delay: 0.04s;
        }

        .quick-actions-wrap.is-open .quick-action-item:nth-child(6),
        .quick-actions-wrap.is-open .quick-action-item:nth-child(7) {
            transition-delay: 0.02s;
        }


        .quick-action-logo img {
            width: 24px;
            height: 24px;
            object-fit: contain;
            border-radius: 999px;
            background: #ffffff;
        }

        .quick-action-bell {
            position: relative;
        }

        .quick-action-badge {
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

        .quick-actions-toggle > .quick-action-badge {
            top: 1px;
            right: 1px;
            border-color: #7f1d2d;
        }

        .quick-action-tooltip {
            position: absolute;
            top: 50%;
            right: calc(100% + 14px);
            transform: translateY(-50%) translateX(6px);
            padding: 8px 12px;
            border-radius: 12px;
            background: rgba(44, 14, 21, 0.96);
            border: 1px solid #facc15;
            color: #ffffff;
            box-shadow: 0 0 18px rgba(250, 204, 21, 0.32);
            font-size: 12px;
            font-weight: 800;
            letter-spacing: 0.02em;
            white-space: nowrap;
            opacity: 0;
            visibility: hidden;
            pointer-events: none;
            transition: opacity 0.18s ease, transform 0.18s ease, visibility 0.18s ease;
            z-index: 1003;
        }

        .quick-action-tooltip::after {
            content: "";
            position: absolute;
            top: 50%;
            left: calc(100% - 1px);
            width: 10px;
            height: 10px;
            background: #70131B;
            border-top: 1px solid #facc15;
            border-right: 1px solid #facc15;
            box-shadow: 6px -6px 12px rgba(250, 204, 21, 0.24);
            transform: translateY(-50%) rotate(45deg);
        }

        .quick-action-item:hover .quick-action-tooltip,
        .quick-action-item:focus-within .quick-action-tooltip {
            opacity: 1;
            visibility: visible;
            transform: translateY(-50%) translateX(0);
        }

        .quick-action-logo + .quick-action-tooltip {
            display: none;
        }

        .admin-user {
            border: 1px solid rgba(255, 255, 255, 0.24);
            background: rgba(255, 255, 255, 0.1);
            border-radius: 14px;
            width: 60px;
            min-width: 60px;
            min-height: 58px;
            padding: 9px 10px;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 0;
            overflow: hidden;
            cursor: pointer;
            user-select: none;
            transition: width 0.24s ease, min-width 0.24s ease, padding 0.24s ease, border-color 0.2s ease, box-shadow 0.2s ease, transform 0.2s ease, background 0.2s ease;
        }

        .profile-wrap:hover .admin-user,
        .profile-wrap.is-expanded .admin-user {
            width: 258px;
            min-width: 258px;
            padding: 8px 10px;
            justify-content: flex-end;
            gap: 10px;
        }

        .profile-wrap.is-click-collapsed:hover .admin-user {
            width: 60px;
            min-width: 60px;
            padding: 9px 10px;
            justify-content: center;
            gap: 0;
        }

        .admin-user:hover,
        .profile-wrap.is-expanded .admin-user {
            border-color: rgba(255, 255, 255, 0.4);
            box-shadow: var(--shadow-soft);
            transform: translateY(-1px);
        }

        .user-avatar {
            width: 38px;
            height: 38px;
            flex: 0 0 38px;
            border-radius: 12px;
            background: linear-gradient(145deg, var(--pup-maroon), var(--pup-maroon-dark));
            color: #fff;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: 800;
            font-size: 14px;
            letter-spacing: 0.02em;
        }

        .admin-user-meta {
            text-align: right;
            width: 0;
            max-width: 0;
            opacity: 0;
            overflow: hidden;
            white-space: nowrap;
            transform: translateX(8px);
            transition: opacity 0.18s ease, max-width 0.24s ease, width 0.24s ease, transform 0.24s ease;
        }

        .profile-wrap:hover .admin-user-meta,
        .profile-wrap.is-expanded .admin-user-meta {
            width: 168px;
            max-width: 168px;
            opacity: 1;
            transform: translateX(0);
        }

        .profile-wrap.is-click-collapsed:hover .admin-user-meta {
            width: 0;
            max-width: 0;
            opacity: 0;
            transform: translateX(8px);
        }

        .admin-user-name {
            font-size: 13px;
            font-weight: 700;
            line-height: 1.1;
            color: var(--text);
        }

        .admin-user-role {
            margin-top: 2px;
            font-size: 11px;
            font-weight: 600;
            color: var(--muted);
            text-transform: uppercase;
            letter-spacing: 0.04em;
        }

        .admin-user-chevron {
            width: 18px;
            height: 18px;
            flex: 0 0 auto;
            opacity: 0;
            max-width: 0;
            overflow: hidden;
            stroke-width: 1.8;
            transition: opacity 0.18s ease, max-width 0.24s ease, transform 0.2s ease;
        }

        .profile-wrap:hover .admin-user-chevron,
        .profile-wrap.is-expanded .admin-user-chevron {
            opacity: 0.72;
            max-width: 18px;
        }

        .profile-wrap.is-click-collapsed:hover .admin-user-chevron {
            opacity: 0;
            max-width: 0;
        }

        .profile-wrap.is-expanded .admin-user-chevron {
            transform: rotate(180deg);
        }

        .profile-dropdown {
            display: none;
            position: absolute;
            top: 54px;
            right: 0;
            background: linear-gradient(180deg, #4f1520 0%, #391019 100%);
            width: 190px;
            box-shadow: var(--shadow-soft);
            border-radius: 12px;
            border: 1px solid rgba(255, 255, 255, 0.12);
            overflow: hidden;
            z-index: 1000;
        }

        .profile-dropdown a {
            display: flex;
            align-items: center;
            gap: 10px;
            padding: 12px 16px;
            color: #fff2f6;
            text-decoration: none;
            font-size: 14px;
            font-weight: 600;
            transition: background 0.2s ease, color 0.2s ease;
            border-bottom: 1px solid rgba(255, 255, 255, 0.08);
        }

        .profile-dropdown a svg {
            width: 18px;
            height: 18px;
            flex: 0 0 auto;
            stroke-width: 1.8;
        }

        .profile-dropdown a:hover {
            background: rgba(255, 255, 255, 0.08);
            color: #ffffff;
        }

        .profile-dropdown a.switch-student-link {
            color: #fff7cf;
        }

        .profile-dropdown a.switch-student-link:hover {
            background: rgba(250, 204, 21, 0.14);
            color: #facc15;
        }

        .profile-dropdown a.logout-link {
            color: #ffd7df;
            border-bottom: none;
            justify-content: flex-start;
            line-height: 1.2;
            padding: 12px 14px;
        }

        .profile-dropdown a.logout-link:hover {
            background: rgba(255, 255, 255, 0.1);
        }

        .admin-layout {
            display: flex;
            flex: 1;
            min-height: 0;
            gap: 20px;
            padding: clamp(14px, 2.4vw, 24px);
            overflow: hidden;
        }

        .sidebar {
            width: var(--sidebar-expanded-width);
            background: var(--admin-sidebar-bg);
            border: 1px solid var(--admin-sidebar-border);
            border-radius: var(--radius-xl);
            box-shadow: var(--shadow-soft);
            padding: 20px 14px;
            display: flex;
            flex-direction: column;
            flex-shrink: 0;
            overflow-y: auto;
            overflow-x: hidden;
            transition:
                background 0.28s ease,
                border-color 0.28s ease,
                box-shadow 0.28s ease;
        }

        .sidebar-logo {
            display: flex;
            align-items: center;
            gap: 12px;
            margin-bottom: 20px;
            padding-bottom: 18px;
            border-bottom: 1px solid var(--admin-sidebar-divider);
            min-width: 210px;
        }

        .sidebar-logo-badges {
            display: flex;
            align-items: center;
            gap: 8px;
            flex-shrink: 0;
        }

        .sidebar-logo-badge {
            width: 44px;
            height: 44px;
            border-radius: 50%;
            background: var(--admin-brand-logo-bg);
            border: 1px solid var(--admin-brand-logo-border);
            object-fit: contain;
            padding: 4px;
            box-shadow: 0 10px 18px rgba(15, 23, 42, 0.14);
        }

        .sidebar-logo-badge--clinic {
            padding: 3px;
        }

        .sidebar-logo-title {
            margin: 0;
            font-family: "Outfit", "Manrope", sans-serif;
            font-size: 14px;
            letter-spacing: 0.02em;
            font-weight: 700;
            color: var(--admin-sidebar-title);
            line-height: 1.2;
        }

        .sidebar-logo-sub {
            margin: 3px 0 0;
            color: var(--admin-sidebar-muted);
            font-size: 10px;
            font-weight: 600;
            letter-spacing: 0.08em;
            text-transform: uppercase;
        }

        .sidebar-logo-text {
            opacity: 1;
            transform: translateX(0);
            max-width: 180px;
            overflow: hidden;
            white-space: nowrap;
            transition:
                max-width 0.34s cubic-bezier(0.22, 1, 0.36, 1),
                opacity 0.2s ease,
                transform 0.24s ease;
        }

        .sidebar h4 {
            color: var(--admin-sidebar-muted);
            margin: 0 0 12px;
            font-size: 10px;
            letter-spacing: 0.16em;
            text-transform: uppercase;
            font-weight: 800;
            white-space: nowrap;
            opacity: 1;
            transform: translateX(0);
            max-width: 180px;
            overflow: hidden;
            transition:
                max-width 0.34s cubic-bezier(0.22, 1, 0.36, 1),
                opacity 0.2s ease,
                transform 0.24s ease;
        }

        .sidebar-nav a {
            display: flex;
            align-items: center;
            gap: 12px;
            padding: 10px 12px;
            border-radius: var(--radius-md);
            border: 1px solid transparent;
            color: var(--admin-sidebar-text);
            text-decoration: none;
            margin-bottom: 8px;
            font-size: 13px;
            font-weight: 700;
            letter-spacing: 0.01em;
            transition:
                background 0.2s ease,
                border-color 0.2s ease,
                color 0.2s ease,
                transform 0.22s ease;
            min-width: 210px;
            white-space: nowrap;
            position: relative;
        }

        .sidebar-nav a:hover {
            background: var(--admin-sidebar-hover-bg);
            border-color: var(--admin-sidebar-hover-border);
            transform: translateX(1px);
        }

        .sidebar-nav a.active {
            background: var(--admin-sidebar-active-bg);
            border-color: var(--admin-sidebar-active-border);
            color: var(--admin-sidebar-title);
            box-shadow: inset 0 0 0 1px rgba(255, 255, 255, 0.04);
            margin-right: -14px;
            padding-right: 26px;
            border-top-right-radius: 0;
            border-bottom-right-radius: 0;
        }

        .sidebar-short {
            width: 34px;
            height: 34px;
            border-radius: 10px;
            border: 1px solid var(--admin-sidebar-short-border);
            background: var(--admin-sidebar-short-bg);
            display: inline-flex;
            align-items: center;
            justify-content: center;
            flex-shrink: 0;
            color: var(--admin-sidebar-title);
            font-size: 0;
            line-height: 0;
            position: relative;
            overflow: hidden;
        }

        .sidebar-short svg {
            width: 18px;
            height: 18px;
            stroke-width: 1.8;
            flex: 0 0 auto;
            transition: transform 0.22s ease, filter 0.22s ease;
        }

        .sidebar-nav a.active .sidebar-short {
            border-color: #111111;
            background: rgba(255, 255, 255, 0.16);
            color: #111111;
        }

        .sidebar-nav a.active .sidebar-short svg {
            filter: drop-shadow(0 2px 6px rgba(17, 17, 17, 0.12));
        }

        html[data-theme="dark"] .sidebar-nav a.active .sidebar-short {
            border-color: transparent;
            background: transparent;
            color: #ffffff;
            border-radius: 0;
            box-shadow: none;
        }

        html[data-theme="dark"] .sidebar-nav a.active .sidebar-short svg,
        html[data-theme="dark"] .sidebar-nav a.active .sidebar-short::before,
        html[data-theme="dark"] .sidebar-nav a.active .sidebar-short::after {
            color: #ffffff;
            stroke: #ffffff;
            border-color: rgba(255, 255, 255, 0.72);
            background-color: transparent;
        }

        .sidebar-nav a.nav-dashboard.active .sidebar-short svg {
            animation: navDashboardOrbit 1s linear 1;
            transform-origin: 50% 50%;
        }

        .sidebar-nav a.nav-appointments.active .sidebar-short svg {
            animation: navAppointmentsPageTurn 1s cubic-bezier(0.3, 0, 0.2, 1) 1;
            transform-origin: 54% 38%;
        }

        .sidebar-nav a.nav-inventory.active .sidebar-short svg path {
            stroke-dasharray: 64;
            stroke-dashoffset: 64;
            animation: navInventoryTrace 1s ease-in-out 1 forwards;
        }

        .sidebar-nav a.nav-walkin.active .sidebar-short svg {
            animation: navWalkinAdd 1s cubic-bezier(0.22, 1, 0.36, 1) 1;
            transform-origin: 58% 42%;
        }

        .sidebar-nav a.nav-reports.active .sidebar-short svg {
            animation: navReportsRise 1s ease-in-out 1;
            transform-origin: 50% 100%;
        }

        .sidebar-nav a.nav-health.active .sidebar-short svg {
            animation: navHealthWrite 1s cubic-bezier(0.37, 0, 0.63, 1) 1;
            transform-origin: 42% 60%;
        }

        .sidebar-nav a.nav-audit.active .sidebar-short svg {
            animation: navAuditPulse 1s ease-in-out 1;
            transform-origin: 50% 50%;
        }

        .sidebar-nav a.nav-settings.active .sidebar-short svg {
            animation: navSettingsOrbit 1s linear 1;
            transform-origin: 50% 50%;
        }

        .sidebar-nav a.nav-health.active .sidebar-short::after {
            content: "";
            position: absolute;
            left: 8px;
            bottom: 8px;
            width: 0;
            height: 2px;
            border-radius: 999px;
            background: rgba(17, 17, 17, 0.82);
            animation: navHealthInk 1s cubic-bezier(0.37, 0, 0.63, 1) 1;
        }

        .sidebar-nav a.nav-settings.active .sidebar-short::before {
            content: "";
            position: absolute;
            inset: 4px;
            border-radius: 999px;
            border: 1.5px dashed rgba(17, 17, 17, 0.5);
            animation: navSettingsRing 1s linear 1;
        }

        .sidebar-nav a.nav-audit.active .sidebar-short::after {
            content: "";
            position: absolute;
            left: 6px;
            right: 6px;
            top: 8px;
            height: 2px;
            border-radius: 999px;
            background: linear-gradient(90deg, rgba(17, 17, 17, 0), rgba(17, 17, 17, 0.72), rgba(17, 17, 17, 0));
            transform: translateY(0);
            opacity: 0;
            animation: navAuditScan 1s ease-in-out 1;
        }

        .sidebar-nav a.nav-appointments.active .sidebar-short::before,
        .sidebar-nav a.nav-appointments.active .sidebar-short::after {
            content: "";
            position: absolute;
            left: 9px;
            right: 9px;
            height: 1.5px;
            border-radius: 999px;
            background: rgba(17, 17, 17, 0.62);
            opacity: 0;
            animation: navAppointmentsPageLines 1s cubic-bezier(0.3, 0, 0.2, 1) 1;
        }

        .sidebar-nav a.nav-appointments.active .sidebar-short::before {
            top: 11px;
        }

        .sidebar-nav a.nav-appointments.active .sidebar-short::after {
            top: 16px;
            animation-delay: 0.12s;
        }

        .sidebar-nav a.nav-reports.active .sidebar-short svg path {
            transform-box: fill-box;
            transform-origin: 50% 100%;
        }

        .sidebar-nav a.nav-reports.active .sidebar-short svg path:nth-of-type(1) {
            animation: navReportsBarOne 1s ease-in-out 1;
        }

        .sidebar-nav a.nav-reports.active .sidebar-short svg path:nth-of-type(2) {
            animation: navReportsBarTwo 1s ease-in-out 1;
        }

        .sidebar-nav a.nav-reports.active .sidebar-short svg path:nth-of-type(3) {
            animation: navReportsBarThree 1s ease-in-out 1;
        }

        @keyframes navDashboardOrbit {
            0% {
                transform: rotate(0deg) scale(1);
            }
            45% {
                transform: rotate(180deg) scale(1.06);
            }
            100% {
                transform: rotate(360deg) scale(1);
            }
        }

        @keyframes navAppointmentsFlip {
            0% {
                transform: rotate(0deg) translateX(0) translateY(0) scale(1);
            }
            24% {
                transform: rotate(-8deg) translateX(-1px) translateY(-1px) scale(0.98);
            }
            52% {
                transform: rotate(7deg) translateX(1.5px) translateY(0) scale(1.02);
            }
            74% {
                transform: rotate(-4deg) translateX(0.5px) translateY(1px) scale(1);
            }
            100% {
                transform: rotate(0deg) translateX(0) translateY(0) scale(1);
            }
        }

        @keyframes navAppointmentsPageTurn {
            0% {
                transform: perspective(120px) rotateY(0deg) translateX(0) scale(1);
            }
            28% {
                transform: perspective(120px) rotateY(-24deg) translateX(-1px) scale(0.98);
            }
            55% {
                transform: perspective(120px) rotateY(18deg) translateX(1px) scale(1.01);
            }
            78% {
                transform: perspective(120px) rotateY(-8deg) translateX(0) scale(1);
            }
            100% {
                transform: perspective(120px) rotateY(0deg) translateX(0) scale(1);
            }
        }

        @keyframes navInventoryTrace {
            0% {
                stroke-dashoffset: 64;
                opacity: 0.7;
            }
            55% {
                opacity: 1;
            }
            100% {
                stroke-dashoffset: 0;
                opacity: 1;
            }
        }

        @keyframes navWalkinAdd {
            0%, 100% {
                transform: scale(1);
            }
            35% {
                transform: scale(1.14);
            }
            65% {
                transform: scale(0.96);
            }
        }

        @keyframes navReportsRise {
            0%, 100% {
                transform: translateY(0) scale(1);
            }
            50% {
                transform: translateY(-1px) scale(1.02);
            }
        }

        @keyframes navHealthWrite {
            0% {
                transform: translateX(-2px) translateY(1px) rotate(-14deg);
            }
            26% {
                transform: translateX(0) translateY(0) rotate(-4deg);
            }
            56% {
                transform: translateX(5px) translateY(-1px) rotate(7deg);
            }
            82% {
                transform: translateX(3px) translateY(0) rotate(-1deg);
            }
            100% {
                transform: translateX(0) translateY(0) rotate(0deg);
            }
        }

        @keyframes navAuditPulse {
            0%, 100% {
                transform: scale(1);
                filter: drop-shadow(0 2px 6px rgba(17, 17, 17, 0.1));
            }
            35% {
                transform: scale(1.03);
                filter: drop-shadow(0 3px 8px rgba(17, 17, 17, 0.14));
            }
            60% {
                transform: scale(1.08);
                filter: drop-shadow(0 4px 10px rgba(17, 17, 17, 0.18));
            }
        }

        @keyframes navSettingsOrbit {
            0% {
                transform: rotate(0deg);
            }
            100% {
                transform: rotate(360deg);
            }
        }

        @keyframes navHealthInk {
            0%, 14% {
                width: 0;
                opacity: 0;
            }
            24% {
                opacity: 0.9;
            }
            58% {
                width: 16px;
                opacity: 0.9;
            }
            82% {
                width: 16px;
                opacity: 0.9;
            }
            100% {
                width: 12px;
                opacity: 0;
            }
        }

        @keyframes navSettingsRing {
            0% {
                transform: rotate(0deg) scale(0.98);
                opacity: 0.5;
            }
            50% {
                transform: rotate(180deg) scale(1.02);
                opacity: 0.78;
            }
            100% {
                transform: rotate(360deg) scale(0.98);
                opacity: 0.5;
            }
        }

        @keyframes navAuditScan {
            0%, 18% {
                transform: translateY(-5px);
                opacity: 0;
            }
            32% {
                opacity: 0.9;
            }
            62% {
                transform: translateY(9px);
                opacity: 0.9;
            }
            100% {
                transform: translateY(9px);
                opacity: 0;
            }
        }

        @keyframes navAppointmentsPageLines {
            0%, 16% {
                transform: translateX(-5px) scaleX(0.76);
                opacity: 0;
            }
            30% {
                transform: translateX(-1px) scaleX(0.96);
                opacity: 0.78;
            }
            58% {
                transform: translateX(3px) scaleX(1);
                opacity: 0.78;
            }
            100% {
                transform: translateX(6px) scaleX(0.82);
                opacity: 0;
            }
        }

        @keyframes navReportsBarOne {
            0%, 12% {
                transform: scaleY(0);
                opacity: 0;
            }
            22%, 100% {
                transform: scaleY(1);
                opacity: 1;
            }
        }

        @keyframes navReportsBarTwo {
            0%, 34% {
                transform: scaleY(0);
                opacity: 0;
            }
            46%, 100% {
                transform: scaleY(1);
                opacity: 1;
            }
        }

        @keyframes navReportsBarThree {
            0%, 56% {
                transform: scaleY(0);
                opacity: 0;
            }
            70%, 100% {
                transform: scaleY(1);
                opacity: 1;
            }
        }

        .sidebar-label {
            opacity: 1;
            transform: translateX(0);
            max-width: 180px;
            overflow: hidden;
            white-space: nowrap;
            transition:
                max-width 0.34s cubic-bezier(0.22, 1, 0.36, 1),
                opacity 0.2s ease,
                transform 0.24s ease;
        }

        .sidebar-logout {
            margin-top: auto;
            padding-top: 16px;
            border-top: 1px solid var(--admin-sidebar-divider);
        }

        .sidebar-logout a {
            display: flex;
            align-items: center;
            gap: 12px;
            padding: 10px 12px;
            border-radius: var(--radius-md);
            border: 1px solid var(--admin-sidebar-logout-border);
            text-decoration: none;
            min-width: 210px;
            transition: background 0.2s ease, border-color 0.2s ease, transform 0.2s ease;
            margin-bottom: 0;
            color: var(--admin-sidebar-text);
            background: var(--admin-sidebar-logout-bg);
            line-height: 1.2;
        }

        .sidebar-logout a:hover {
            background: var(--admin-sidebar-hover-bg);
            border-color: var(--admin-sidebar-hover-border);
            transform: translateX(1px);
        }

        .sidebar-logout .sidebar-short {
            color: #ffffff;
            border-color: rgba(255, 255, 255, 0.26);
            background: rgba(255, 255, 255, 0.12);
        }

        .main {
            flex: 1;
            min-width: 0;
            position: relative;
            overflow-y: auto;
            overflow-x: auto;
            -webkit-overflow-scrolling: touch;
            -ms-overflow-style: none;
            scrollbar-width: none;
            padding: 10px 10px 4px;
            color: var(--admin-card-text);
            background: var(--admin-shell-bg);
            border: 1px solid var(--admin-shell-border);
            border-radius: var(--radius-xl);
        }

        .main table {
            width: 100%;
        }

        @keyframes scrollbarGlow {
            0%, 100% {
                box-shadow:
                    inset 0 0 0 1px rgba(255, 255, 255, 0.04),
                    0 0 0 0 rgba(128, 0, 0, 0.0);
            }
            50% {
                box-shadow:
                    inset 0 0 0 1px rgba(255, 255, 255, 0.08),
                    0 0 10px 2px rgba(128, 0, 0, 0.22);
            }
        }

        .main::-webkit-scrollbar {
            width: 0;
            height: 0;
        }

        .main::-webkit-scrollbar-track {
            background: transparent;
        }

        .sidebar {
            -ms-overflow-style: none;
            scrollbar-width: none;
        }

        .sidebar::-webkit-scrollbar {
            width: 0;
            height: 0;
            display: none;
        }

        .sidebar-scroll-indicator {
            position: sticky;
            bottom: 12px;
            margin: auto auto 0;
            width: 34px;
            height: 34px;
            border-radius: 999px;
            border: 1px solid var(--admin-sidebar-indicator-border);
            background: var(--admin-sidebar-indicator-bg);
            color: #ffffff;
            box-shadow: 0 10px 18px rgba(15, 23, 42, 0.16);
            backdrop-filter: blur(8px);
            display: none;
            align-items: center;
            justify-content: center;
            cursor: pointer;
            z-index: 2;
            transition: transform 0.2s ease, background 0.2s ease, opacity 0.2s ease, box-shadow 0.2s ease;
        }

        .sidebar-scroll-indicator.is-visible {
            display: inline-flex;
        }

        .sidebar:not(:hover):not(:focus-within) .sidebar-scroll-indicator.is-visible {
            display: none;
        }

        .sidebar-scroll-indicator:hover {
            transform: translateY(-2px);
            background: rgba(255, 255, 255, 0.14);
            box-shadow: 0 14px 24px rgba(15, 23, 42, 0.2);
        }

        .sidebar-scroll-indicator svg {
            width: 14px;
            height: 14px;
            display: block;
            stroke: currentColor;
            color: #ffffff;
        }

        .card {
            background: var(--surface);
            border: 1px solid var(--stroke);
            border-radius: var(--radius-lg);
            padding: 20px;
            box-shadow: 0 18px 34px rgba(15, 23, 42, 0.12);
            margin-bottom: 18px;
        }

        .btn {
            padding: 10px 14px;
            border-radius: 10px;
            border: 1px solid transparent;
            font-family: inherit;
            font-size: 13px;
            font-weight: 700;
            cursor: pointer;
            transition: filter 0.2s ease, transform 0.2s ease;
        }

        .btn:hover {
            filter: brightness(0.98);
            transform: translateY(-1px);
        }

        .btn-primary {
            background: var(--pup-maroon);
            color: #fff;
        }

        .btn-outline {
            background: var(--surface);
            border-color: var(--stroke-strong);
            color: var(--text);
        }

        @media (max-width: 1024px) {
            .admin-layout {
                gap: 16px;
                padding: 14px;
            }
        }

        @media (max-width: 860px) {
            .sidebar-toggle {
                display: inline-flex;
                align-items: center;
                justify-content: center;
            }

            .admin-layout {
                padding: 12px;
            }

            .sidebar {
                position: fixed;
                top: 76px;
                left: 12px;
                bottom: 12px;
                width: min(86vw, 300px);
                z-index: 60;
                transform: translateX(-115%);
                transition: transform 0.28s cubic-bezier(0.22, 1, 0.36, 1);
            }

            .sidebar:hover,
            .sidebar:focus-within {
                width: min(86vw, 300px);
            }

            body.sidebar-open .sidebar {
                transform: translateX(0);
            }

            body.sidebar-open::before {
                content: "";
                position: fixed;
                inset: 0;
                background: rgba(15, 23, 42, 0.28);
                z-index: 55;
            }

            .main {
                width: 100%;
            }

            .header-subtitle {
                display: none;
            }

            .header-brand-avatar {
                width: 44px;
                height: 44px;
            }

            .admin-user-meta {
                width: 0;
                max-width: 0;
                opacity: 0;
            }

            .sidebar-logo {
                min-width: 0;
            }

            .sidebar-nav a {
                min-width: 0;
            }

            .sidebar .sidebar-logo-text,
            .sidebar h4,
            .sidebar .sidebar-label {
                max-width: 180px;
                opacity: 1;
                transform: translateX(0);
            }

            .main table {
                display: block;
                overflow-x: auto;
                white-space: nowrap;
                -webkit-overflow-scrolling: touch;
            }
        }

        @media (max-width: 560px) {
            .header-kicker {
                display: none;
            }

            .header-title {
                font-size: 18px;
            }

            .medicine-alert-fab {
                width: 28px; 
                height: 28px;
                fill: #ffffff;
                stroke: none;  
            }

            .medicine-alert-panel {
                right: 12px;
                left: 12px;
                bottom: 72px;
                width: auto;
            }
        }

        @stack('styles')
    </style>

    <style>
        /* Global Admin Theme Overrides */
        .main :where(.card, .panel, .stat-card-mini, .modal-box) {
            background: var(--admin-card-bg) !important;
            border: 1px solid var(--admin-card-border) !important;
            border-radius: 14px !important;
            box-shadow: 0 18px 38px rgba(15, 23, 42, 0.12) !important;
            color: var(--admin-card-text) !important;
        }

        .main :where(h1, h2, h3, h4, h5) {
            color: var(--admin-heading);
            letter-spacing: -0.01em;
        }

        .main :where(a):not(.report-card) {
            color: var(--admin-link);
        }

        .main :where(a:hover):not(.report-card) {
            color: var(--admin-link-hover);
        }

        /* Keep Reports cards high-contrast and match the appointment-style yellow hover */
        .main .report-grid .report-card {
            background: #6f1422 !important;
            border: 1px solid #8f3444 !important;
            color: #ffffff !important;
            overflow: hidden !important;
            position: relative !important;
        }

        .main .report-grid .report-card:hover {
            background: #facc15 !important;
            background-image: none !important;
            color: #111111 !important;
            filter: none !important;
            border-color: #facc15 !important;
        }

        .main .report-grid .report-card.report-card-primary::before {
            display: none !important;
        }

        .main .report-grid .report-card .report-label {
            color: #f2d4dc !important;
        }

        .main .report-grid .report-card .report-main-title {
            color: #ffffff !important;
        }

        .main .report-grid .report-card .report-badge {
            background: rgba(255, 255, 255, 0.16) !important;
            color: #ffffff !important;
        }

        .main .report-grid .report-card .report-card-icon {
            color: #facc15 !important;
        }

        .main .report-grid .report-card:hover .report-label,
        .main .report-grid .report-card:hover .report-main-title,
        .main .report-grid .report-card:hover .report-badge,
        .main .report-grid .report-card:hover .report-card-icon {
            color: #111111 !important;
        }

        .main .report-grid .report-card:hover .report-badge {
            background: rgba(17, 17, 17, 0.10) !important;
        }

        .main :where(table th) {
            border-bottom-color: var(--admin-table-head-border) !important;
            color: var(--admin-table-head-text) !important;
            letter-spacing: 0.06em;
            background: var(--admin-table-head-bg) !important;
        }

        .main :where(table td) {
            border-bottom-color: var(--admin-table-body-border) !important;
            color: var(--admin-table-body-text) !important;
        }

        .main :where(.form-control, .form-input, .input-month, input, select, textarea) {
            border-color: var(--admin-input-border) !important;
            border-radius: 10px !important;
            background: var(--admin-input-bg) !important;
            color: var(--admin-input-text) !important;
        }

        .main :where(.form-control, .form-input, .input-month, input, select, textarea)::placeholder {
            color: var(--admin-input-placeholder) !important;
        }

        .main :where(.form-control, .form-input, .input-month, input, select, textarea):focus {
            outline: none;
            border-color: var(--admin-primary-btn-border) !important;
            box-shadow: var(--admin-input-focus-shadow);
        }

        .main :where(.btn-save, .btn-add, .btn-add-walkin, .btn-filter, .btn-change, .btn-generate, .btn-primary, .btn-complete) {
            background: var(--admin-primary-btn-bg) !important;
            color: #ffffff !important;
            border: 1px solid var(--admin-primary-btn-border) !important;
        }

        .main :where(.btn-save:hover, .btn-add:hover, .btn-add-walkin:hover, .btn-filter:hover, .btn-change:hover, .btn-generate:hover, .btn-primary:hover, .btn-complete:hover) {
            background: var(--admin-primary-btn-hover) !important;
            border-color: var(--admin-primary-btn-hover) !important;
            color: #ffffff !important;
        }

        .main .appointments-toolbar .btn-add-walkin:hover,
        .main .appointments-toolbar .btn-add-walkin:focus {
            background: #facc15 !important;
            border-color: #facc15 !important;
            color: #111827 !important;
        }

        .main .appointments-toolbar .btn-add-walkin:hover svg,
        .main .appointments-toolbar .btn-add-walkin:focus svg,
        .main .appointments-toolbar .btn-add-walkin:hover .btn-text,
        .main .appointments-toolbar .btn-add-walkin:focus .btn-text {
            color: #111827 !important;
            stroke: currentColor !important;
        }

        .main .inventory-toolbar-actions .btn-add:hover,
        .main .inventory-toolbar-actions .btn-add:focus {
            background: #facc15 !important;
            border-color: #facc15 !important;
            color: #111827 !important;
        }

        .main .inventory-toolbar-actions .btn-add:hover svg,
        .main .inventory-toolbar-actions .btn-add:focus svg {
            color: #111827 !important;
            stroke: currentColor !important;
        }

        .main .health-filter-shell .health-filter-toggle:hover,
        .main .health-filter-shell .health-filter-toggle:focus,
        .main .health-filter-shell .health-filter-toggle.is-open {
            background: #facc15 !important;
            border-color: #facc15 !important;
            color: #111827 !important;
        }

        .main .health-records-search-shell .health-records-search-toggle:hover,
        .main .health-records-search-shell .health-records-search-toggle:focus {
            background: #facc15 !important;
            border-color: #facc15 !important;
            color: #111827 !important;
        }

        .main .health-records-search-shell .health-records-search-toggle:hover svg,
        .main .health-records-search-shell .health-records-search-toggle:focus svg {
            color: #111827 !important;
            stroke: currentColor !important;
        }

        .main .export-hub-container .btn-generate:hover,
        .main .export-hub-container .btn-generate:focus {
            background: #facc15 !important;
            border-color: #facc15 !important;
            color: #111827 !important;
        }

        .main :where(.btn-edit, .btn-view, .btn-outline) {
            background: var(--admin-secondary-btn-bg) !important;
            color: var(--admin-secondary-btn-text) !important;
            border: 1px solid var(--admin-secondary-btn-border) !important;
        }

        .main :where(.btn-edit:hover, .btn-view:hover, .btn-outline:hover) {
            background: var(--admin-secondary-btn-hover) !important;
        }

        .main :where(.btn-delete, .btn-cancel) {
            background: var(--admin-danger-btn-bg) !important;
            color: var(--admin-danger-btn-text) !important;
            border: 1px solid var(--admin-danger-btn-border) !important;
        }

        .main :where(.status.completed) {
            background: var(--admin-status-completed-bg) !important;
            color: var(--admin-status-completed-text) !important;
        }

        .main :where(.switch-row input) {
            accent-color: #70131B !important;
        }

        .main :where(.notification-toast) {
            background: linear-gradient(145deg, #7f1d2d, #5a0f16) !important;
        }

        .main :where(.btn-toast-action) {
            background: rgba(255, 255, 255, 0.18) !important;
            border-color: rgba(255, 255, 255, 0.38) !important;
        }

        .admin-live-alert {
            position: fixed;
            left: 50%;
            top: 50%;
            width: min(620px, calc(100vw - 36px));
            z-index: 500001;
            opacity: 0;
            transform: translate(-50%, -56%) scale(0.98);
            pointer-events: none;
            transition: opacity 0.22s ease, transform 0.24s ease;
        }

        .admin-live-alert.is-open {
            opacity: 1;
            transform: translate(-50%, -50%) scale(1);
            pointer-events: auto;
        }

        .admin-live-alert-card {
            display: grid;
            grid-template-columns: auto minmax(0, 1fr) auto auto;
            gap: 12px;
            align-items: center;
            border-radius: 14px;
            border: 1px solid rgba(112, 19, 27, 0.22);
            background: rgba(255, 250, 244, 0.96);
            box-shadow: 0 18px 34px rgba(15, 23, 42, 0.2);
            padding: 16px 16px 14px;
            min-height: 176px;
            height: 176px;
            overflow: hidden;
            backdrop-filter: blur(10px);
            -webkit-backdrop-filter: blur(10px);
        }

        .admin-live-alert-content {
            min-width: 0;
            align-self: center;
            overflow: hidden;
            display: flex;
            flex-direction: column;
            justify-content: center;
        }

        .admin-live-alert-icon {
            width: 40px;
            height: 40px;
            border-radius: 10px;
            background: #fff3c4;
            color: #7f1d2d;
            border: 1px solid rgba(250, 204, 21, 0.45);
            display: inline-flex;
            align-items: center;
            justify-content: center;
            flex: 0 0 auto;
        }

        .admin-live-alert-icon svg {
            width: 20px;
            height: 20px;
            stroke-width: 2;
        }

        .admin-live-alert-icon.is-health {
            background: #dcfce7;
            color: #166534;
            border-color: rgba(34, 197, 94, 0.4);
        }

        .admin-live-alert-title {
            margin: 0;
            font-size: 16px;
            font-weight: 800;
            color: #70131B;
            line-height: 1.3;
            overflow: hidden;
            text-overflow: ellipsis;
            white-space: nowrap;
        }

        .admin-live-alert-copy {
            margin: 5px 0 0;
            font-size: 13px;
            font-weight: 600;
            color: #334155;
            line-height: 1.5;
            overflow: hidden;
            display: -webkit-box;
            -webkit-line-clamp: 4;
            -webkit-box-orient: vertical;
        }

        .admin-live-alert-open {
            align-self: center;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            min-height: 38px;
            padding: 0 16px;
            border-radius: 10px;
            border: 1px solid rgba(250, 204, 21, 0.52);
            background: #fff7d6;
            color: #70131B;
            font-size: 13px;
            font-weight: 800;
            text-decoration: none;
            white-space: nowrap;
        }

        .admin-live-alert-more {
            align-self: center;
            display: none;
            align-items: center;
            justify-content: center;
            min-height: 38px;
            padding: 0 16px;
            border-radius: 10px;
            border: 1px solid rgba(112, 19, 27, 0.3);
            background: rgba(255, 255, 255, 0.9);
            color: #70131B;
            font-size: 13px;
            font-weight: 800;
            white-space: nowrap;
            cursor: pointer;
            grid-column: 1 / -1;
            justify-self: center;
            margin-top: 2px;
        }

        .admin-live-alert-more.is-visible {
            display: inline-flex;
        }

        .admin-live-alert-close {
            width: 36px;
            height: 36px;
            border: 1px solid rgba(112, 19, 27, 0.22);
            background: #ffffff;
            color: #70131B;
            border-radius: 10px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            cursor: pointer;
            transition: background 0.2s ease, border-color 0.2s ease;
        }

        .admin-live-alert-close:hover {
            background: #fff4f4;
            border-color: rgba(112, 19, 27, 0.42);
        }

        .admin-live-alert-close svg {
            width: 18px;
            height: 18px;
            stroke-width: 2.2;
        }

        .admin-live-alert-progress {
            margin-top: 8px;
            width: 100%;
            height: 3px;
            border-radius: 999px;
            overflow: hidden;
            background: rgba(148, 163, 184, 0.28);
        }

        .admin-live-alert-progress-bar {
            width: 100%;
            height: 100%;
            background: linear-gradient(90deg, #facc15, #70131B);
            transform-origin: left center;
            transform: scaleX(1);
        }

        @media (max-width: 640px) {
            .admin-live-alert {
                width: min(100vw - 24px, 540px);
            }

            .admin-live-alert-card {
                grid-template-columns: auto minmax(0, 1fr) auto;
                min-height: 188px;
                height: 188px;
            }

            .admin-live-alert-open {
                grid-column: 2 / 3;
                justify-self: start;
            }

            .admin-live-alert-more {
                grid-column: 1 / -1;
                justify-self: center;
            }

            .admin-live-alert-close {
                grid-column: 3 / 4;
                grid-row: 1;
            }
        }

        html[data-theme="dark"] .admin-live-alert-card {
            border-color: rgba(148, 163, 184, 0.32);
            background: rgba(17, 24, 39, 0.94);
            box-shadow: 0 18px 34px rgba(2, 6, 23, 0.44);
        }

        html[data-theme="dark"] .admin-live-alert-title {
            color: #f8fafc;
        }

        html[data-theme="dark"] .admin-live-alert-copy {
            color: #cbd5e1;
        }

        html[data-theme="dark"] .admin-live-alert-open {
            border-color: rgba(250, 204, 21, 0.5);
            background: rgba(146, 64, 14, 0.22);
            color: #fde68a;
        }

        html[data-theme="dark"] .admin-live-alert-more {
            border-color: rgba(250, 204, 21, 0.46);
            background: rgba(30, 41, 59, 0.92);
            color: #fde68a;
        }

        html[data-theme="dark"] .admin-live-alert-close {
            border-color: rgba(148, 163, 184, 0.34);
            background: rgba(30, 41, 59, 0.9);
            color: #f8fafc;
        }

        .assistant-launch {
            border: 1px solid rgba(255, 255, 255, 0.28);
            background: rgba(255, 255, 255, 0.12);
            color: #ffffff;
            border-radius: 12px;
            padding: 9px 12px;
            font-size: 12px;
            font-weight: 800;
            cursor: pointer;
            letter-spacing: 0.03em;
            display: inline-flex;
            align-items: center;
            gap: 8px;
            transition: background 0.2s ease, border-color 0.2s ease, transform 0.2s ease;
        }

        .assistant-launch:hover {
            background: rgba(255, 255, 255, 0.2);
            border-color: rgba(255, 255, 255, 0.42);
            transform: translateY(-1px);
        }

        .assistant-launch svg {
            width: 18px;
            height: 18px;
            flex: 0 0 auto;
            stroke-width: 1.8;
        }

        .theme-toggle-admin {
            box-shadow: none;
        }

        .theme-toggle-admin:hover {
            box-shadow: none;
        }

        .theme-toggle-admin:focus-visible {
            outline: 2px solid var(--pup-gold);
            outline-offset: 2px;
        }

        .theme-toggle-admin svg {
            width: 18px;
            height: 18px;
            stroke: currentColor;
            fill: none;
            stroke-width: 2;
            stroke-linecap: round;
            stroke-linejoin: round;
            display: block;
        }

        .accessibility-launch-admin {
            box-shadow: none;
        }

        .accessibility-launch-admin:hover {
            box-shadow: none;
        }

        .accessibility-launch-admin:focus-visible {
            outline: 2px solid var(--pup-gold);
            outline-offset: 2px;
        }

        .accessibility-launch-admin svg {
            width: 18px;
            height: 18px;
            stroke: currentColor;
            fill: none;
            stroke-width: 2;
            stroke-linecap: round;
            stroke-linejoin: round;
            display: block;
        }

        html[data-theme="light"] {
            --admin-shell-bg: linear-gradient(180deg, #ffffff 0%, #fbfbfd 100%);
            --admin-shell-border: rgba(128, 0, 0, 0.12);
            --admin-card-bg: linear-gradient(180deg, #ffffff 0%, #fcfcfd 100%);
            --admin-card-border: rgba(128, 0, 0, 0.12);
            --admin-card-shadow: 0 12px 24px rgba(15, 23, 42, 0.08);
            --admin-card-text: #1f2937;
            --admin-heading: #111827;
            --admin-link: #7f1d2d;
            --admin-link-hover: #5a0f16;
            --admin-table-head-border: rgba(128, 0, 0, 0.12);
            --admin-table-head-text: #6b1321;
            --admin-table-head-bg: rgba(128, 0, 0, 0.05);
            --admin-table-body-border: #ece7ea;
            --admin-table-body-text: #1f2937;
            --admin-input-border: #d5dbe4;
            --admin-input-bg: #ffffff;
            --admin-input-text: #111827;
            --admin-input-placeholder: #94a3b8;
            --admin-input-focus-shadow: 0 0 0 3px rgba(128, 0, 0, 0.12);
            --admin-secondary-btn-bg: #fff7f8;
            --admin-secondary-btn-text: #65101d;
            --admin-secondary-btn-border: rgba(128, 0, 0, 0.16);
            --admin-secondary-btn-hover: #fdecee;
            --admin-danger-btn-bg: #fff4f5;
            --admin-danger-btn-text: #b42339;
            --admin-danger-btn-border: rgba(180, 35, 57, 0.18);
            --admin-status-completed-bg: rgba(128, 0, 0, 0.08);
            --admin-status-completed-text: #781826;
        }

        html[data-theme="light"] body {
            background:
                radial-gradient(circle at -10% -10%, rgba(128, 0, 0, 0.04) 0%, transparent 42%),
                radial-gradient(circle at 110% 120%, rgba(128, 0, 0, 0.05) 0%, transparent 36%),
                linear-gradient(180deg, #ffffff 0%, #f8fafc 86%);
            color: #1f2937;
        }

        html[data-theme="light"] .admin-header {
            background: linear-gradient(180deg, #ffffff 0%, #fbfbfc 100%);
            border-bottom-color: rgba(128, 0, 0, 0.12);
            box-shadow: inset 0 -12px 24px rgba(15, 23, 42, 0.06);
        }

        html[data-theme="light"] .header-kicker,
        html[data-theme="light"] .header-subtitle {
            color: #64748b;
        }

        html[data-theme="light"] .header-title {
            color: #111827;
        }

        html[data-theme="light"] .header-title span {
            color: #8b0000;
        }

        html[data-theme="light"] .assistant-launch,
        html[data-theme="light"] .accessibility-launch-admin,
        html[data-theme="light"] .theme-toggle-admin,
        html[data-theme="light"] .quick-action-btn,
        html[data-theme="light"] .quick-action-logo,
        html[data-theme="light"] .sidebar-toggle {
            background: rgba(128, 0, 0, 0.08);
            border-color: rgba(128, 0, 0, 0.24);
            color: #5f0012;
        }

        html[data-theme="light"] .assistant-launch:hover,
        html[data-theme="light"] .accessibility-launch-admin:hover,
        html[data-theme="light"] .theme-toggle-admin:hover,
        html[data-theme="light"] .quick-action-btn:hover,
        html[data-theme="light"] .sidebar-toggle:hover {
            background: rgba(128, 0, 0, 0.14);
            border-color: rgba(128, 0, 0, 0.34);
        }

        html[data-theme="light"] .quick-actions-toggle {
            background: linear-gradient(145deg, #9b111e, #6e1220 55%, #4f0b15);
            border-color: #facc15;
            color: #ffffff;
            box-shadow:
                0 0 0 3px rgba(250, 204, 21, 0.12),
                0 0 18px rgba(250, 204, 21, 0.32),
                0 12px 26px rgba(95, 0, 18, 0.34);
        }

        html[data-theme="light"] .quick-actions-toggle:hover {
            background: linear-gradient(145deg, rgba(176, 24, 38, 0.82), rgba(127, 29, 45, 0.7) 55%, rgba(90, 15, 22, 0.8));
            border-color: #fde047;
            color: #ffffff;
            box-shadow:
                0 0 0 3px rgba(250, 204, 21, 0.18),
                0 0 22px rgba(250, 204, 21, 0.4),
                0 16px 30px rgba(95, 0, 18, 0.42);
        }

        html[data-theme="light"] .quick-actions-panel {
            background: transparent;
            border-color: transparent;
            box-shadow: none;
        }

        html[data-theme="light"] .quick-actions-divider {
            background: rgba(128, 0, 0, 0.12);
        }

        html[data-theme="light"] .quick-action-badge {
            border-color: rgba(255, 255, 255, 0.98);
        }

        html[data-theme="light"] .quick-action-btn,
        html[data-theme="light"] .quick-action-logo {
            background: linear-gradient(145deg, #9b111e, #6e1220 55%, #4f0b15);
            border-color: #facc15;
            color: #ffffff;
            box-shadow:
                0 0 0 3px rgba(250, 204, 21, 0.12),
                0 0 18px rgba(250, 204, 21, 0.26),
                0 10px 22px rgba(95, 0, 18, 0.28);
        }

        html[data-theme="light"] .quick-action-logo,
        html[data-theme="dark"] .quick-action-logo {
            background: #ffffff !important;
            border-color: #7f1d2d !important;
            color: #7f1d2d;
            box-shadow:
                0 0 0 3px #ffffff,
                0 0 0 5px #facc15,
                0 10px 22px rgba(95, 0, 18, 0.28) !important;
        }

        html[data-theme="light"] .quick-action-btn:hover {
            background: linear-gradient(145deg, #b01826, #7f1d2d 55%, #5a0f16);
            border-color: #fde047;
            color: #ffffff;
            box-shadow:
                0 0 0 3px rgba(250, 204, 21, 0.18),
                0 0 22px rgba(250, 204, 21, 0.34),
                0 14px 28px rgba(95, 0, 18, 0.36);
        }

        html[data-theme="light"] .quick-action-item:hover .quick-action-btn,
        html[data-theme="light"] .quick-action-item:hover .quick-action-logo {
            box-shadow:
                0 0 0 3px rgba(250, 204, 21, 0.18),
                0 0 10px rgba(255, 0, 102, 0.34),
                0 0 18px rgba(0, 200, 255, 0.32),
                0 0 26px rgba(255, 221, 0, 0.3),
                0 14px 28px rgba(95, 0, 18, 0.36) !important;
        }

        html[data-theme="light"] .quick-action-tooltip {
            background: #70131B;
            border-color: #facc15;
            color: #ffffff;
            box-shadow: 0 0 18px rgba(250, 204, 21, 0.38);
        }

        html[data-theme="light"] .quick-action-tooltip::after {
            background: #70131B;
            border-top-color: #facc15;
            border-right-color: #facc15;
            box-shadow: 6px -6px 12px rgba(250, 204, 21, 0.28);
        }

        @keyframes quickActionsGlow {
            0%, 100% {
                box-shadow:
                    0 0 0 3px rgba(250, 204, 21, 0.12),
                    0 0 18px rgba(250, 204, 21, 0.28),
                    0 12px 26px rgba(95, 0, 18, 0.34);
            }
            50% {
                box-shadow:
                    0 0 0 4px rgba(250, 204, 21, 0.16),
                    0 0 24px rgba(250, 204, 21, 0.42),
                    0 14px 30px rgba(95, 0, 18, 0.4);
            }
        }

        @keyframes quickActionShake {
            0%, 100% {
                transform: translateX(0) rotate(0deg);
            }
            20% {
                transform: translateX(-1px) rotate(-3deg);
            }
            40% {
                transform: translateX(1.5px) rotate(3deg);
            }
            60% {
                transform: translateX(-1px) rotate(-2deg);
            }
            80% {
                transform: translateX(1px) rotate(2deg);
            }
        }

        @keyframes quickActionShakeAccessibility {
            0%, 100% {
                transform: translateY(-1px) scale(1.18) translateX(0) rotate(0deg);
            }
            20% {
                transform: translateY(-1px) scale(1.18) translateX(-1px) rotate(-3deg);
            }
            40% {
                transform: translateY(-1px) scale(1.18) translateX(1.5px) rotate(3deg);
            }
            60% {
                transform: translateY(-1px) scale(1.18) translateX(-1px) rotate(-2deg);
            }
            80% {
                transform: translateY(-1px) scale(1.18) translateX(1px) rotate(2deg);
            }
        }

        html[data-theme="light"] .admin-user {
            border-color: rgba(128, 0, 0, 0.24);
            background: rgba(255, 255, 255, 0.96);
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

        html[data-theme="light"] .admin-user-name {
            color: #111827;
        }

        html[data-theme="light"] .admin-user-role {
            color: #64748b;
        }

        html[data-theme="light"] .sidebar {
            background: linear-gradient(180deg, #ffffff 0%, #fbfbfc 100%);
            border-color: rgba(128, 0, 0, 0.12);
        }

        html[data-theme="light"] .sidebar-logo {
            border-bottom-color: rgba(128, 0, 0, 0.12);
        }

        html[data-theme="light"] .sidebar-logo-title {
            color: #5a1421;
        }

        html[data-theme="light"] .sidebar-logo-sub,
        html[data-theme="light"] .sidebar h4 {
            color: rgba(90, 20, 33, 0.72);
        }

        html[data-theme="light"] .sidebar-nav a {
            color: #5a1421;
        }

        html[data-theme="light"] .sidebar-nav a:hover {
            background: rgba(128, 0, 0, 0.06);
            border-color: rgba(128, 0, 0, 0.14);
        }

        html[data-theme="light"] .sidebar-nav a.active {
            background: rgba(128, 0, 0, 0.06);
            border-color: rgba(128, 0, 0, 0.14);
            color: #4a0f1a;
        }


        html[data-theme="light"] .sidebar-short {
            color: #5a1421;
            border-color: rgba(128, 0, 0, 0.24);
            background: rgba(128, 0, 0, 0.08);
        }

        html[data-theme="light"] .sidebar-logout {
            border-top-color: rgba(128, 0, 0, 0.12);
        }

        html[data-theme="light"] .sidebar-logout a {
            background: #fff7f8;
            border-color: rgba(128, 0, 0, 0.14);
            color: #561320;
        }

        html[data-theme="light"] .sidebar-logout a:hover {
            background: #fdecee;
            border-color: rgba(128, 0, 0, 0.2);
        }

        html[data-theme="light"] .sidebar-scroll-indicator {
            background: #ffffff !important;
            color: #7f1d2d !important;
            border-color: rgba(127, 29, 45, 0.14) !important;
            box-shadow: 0 10px 18px rgba(127, 29, 45, 0.12) !important;
        }

        html[data-theme="light"] .sidebar-scroll-indicator svg {
            color: #7f1d2d !important;
            stroke: #7f1d2d !important;
        }

        html[data-theme="light"] .sidebar-scroll-indicator:hover {
            background: #ffffff !important;
            box-shadow: 0 14px 24px rgba(127, 29, 45, 0.16) !important;
        }

        html[data-theme="dark"] .sidebar-scroll-indicator svg {
            color: #ffffff !important;
            stroke: #ffffff !important;
        }

        html[data-theme="dark"] .sidebar-scroll-indicator {
            background: rgba(255, 255, 255, 0.08) !important;
            color: #ffffff !important;
        }

        html[data-theme="light"] .main {
            color: #1f2937;
            background: linear-gradient(180deg, #ffffff 0%, #fbfbfd 100%);
            border-color: rgba(128, 0, 0, 0.12);
        }

        html[data-theme="light"] .main,
        html[data-theme="light"] .main h1,
        html[data-theme="light"] .main h2,
        html[data-theme="light"] .main h3,
        html[data-theme="light"] .main h4,
        html[data-theme="light"] .main h5,
        html[data-theme="light"] .main h6,
        html[data-theme="light"] .main p,
        html[data-theme="light"] .main label,
        html[data-theme="light"] .main small,
        html[data-theme="light"] .main strong,
        html[data-theme="light"] .main td,
        html[data-theme="light"] .main th,
        html[data-theme="light"] .main li,
        html[data-theme="light"] .main dt,
        html[data-theme="light"] .main dd,
        html[data-theme="light"] .main legend {
            color: #111827;
        }

        html[data-theme="light"] .main input,
        html[data-theme="light"] .main select,
        html[data-theme="light"] .main textarea {
            color: #111827;
        }

        html[data-theme="light"] .main input::placeholder,
        html[data-theme="light"] .main textarea::placeholder {
            color: #6b7280;
        }

        html[data-theme="light"] .profile-dropdown {
            background: #ffffff;
            border-color: #e4d8dc;
        }

        html[data-theme="light"] .profile-dropdown a {
            color: #5b1623;
            border-bottom-color: #f0e5e8;
        }

        html[data-theme="light"] .profile-dropdown a:hover {
            background: #f8f5f6;
            color: #56111d;
        }

        html[data-theme="light"] .profile-dropdown a.switch-student-link {
            color: #70131b;
        }

        html[data-theme="light"] .profile-dropdown a.switch-student-link:hover {
            background: #fff8d6;
            color: #70131b;
        }

        html[data-theme="light"] .profile-dropdown a.logout-link {
            color: #a2263f;
        }

        html[data-theme="light"] .assistant-panel {
            --assistant-bg: linear-gradient(180deg, rgba(255, 255, 255, 0.98) 0%, rgba(255, 248, 249, 0.98) 100%);
            --assistant-head-bg: linear-gradient(135deg, #8b1020 0%, #70131b 58%, #4f0b15 100%);
            --assistant-surface: #fff8fa;
            --assistant-surface-strong: #ffffff;
            --assistant-border: rgba(127, 29, 45, 0.18);
            --assistant-soft-border: rgba(127, 29, 45, 0.12);
            --assistant-shadow: 0 24px 58px rgba(112, 19, 27, 0.18);
            --assistant-text: #351018;
            --assistant-muted: #6f3a45;
            --assistant-user-bg: linear-gradient(135deg, #70131b 0%, #9f2337 100%);
            --assistant-user-text: #ffffff;
            --assistant-ai-bg: #ffffff;
            --assistant-ai-text: #3e0f18;
            --assistant-chip-bg: #fff1c8;
            --assistant-chip-text: #5b1420;
            --assistant-input-bg: #ffffff;
            --assistant-input-text: #351018;
            --assistant-note-bg: rgba(255, 247, 214, 0.68);
            --assistant-note-text: #6d3b12;
        }

        .assistant-panel {
            --assistant-bg: linear-gradient(180deg, rgba(73, 18, 29, 0.98) 0%, rgba(31, 11, 18, 0.98) 100%);
            --assistant-head-bg: linear-gradient(135deg, #8b1020 0%, #70131b 55%, #3b0b13 100%);
            --assistant-surface: rgba(255, 255, 255, 0.06);
            --assistant-surface-strong: rgba(255, 255, 255, 0.1);
            --assistant-border: rgba(250, 204, 21, 0.24);
            --assistant-soft-border: rgba(255, 255, 255, 0.12);
            --assistant-shadow: 0 28px 70px rgba(12, 2, 6, 0.44);
            --assistant-text: #fff7fa;
            --assistant-muted: #f4c8d1;
            --assistant-user-bg: linear-gradient(135deg, rgba(250, 204, 21, 0.95) 0%, rgba(234, 179, 8, 0.92) 100%);
            --assistant-user-text: #3b0b13;
            --assistant-ai-bg: rgba(255, 255, 255, 0.09);
            --assistant-ai-text: #fff7fa;
            --assistant-chip-bg: rgba(250, 204, 21, 0.12);
            --assistant-chip-text: #fde68a;
            --assistant-input-bg: rgba(255, 255, 255, 0.09);
            --assistant-input-text: #fff7fa;
            --assistant-note-bg: rgba(255, 255, 255, 0.06);
            --assistant-note-text: #f4c8d1;
            position: fixed;
            right: 20px;
            bottom: 20px;
            width: min(520px, calc(100vw - 40px));
            height: min(540px, calc(100vh - 40px));
            max-height: calc(100vh - 48px);
            background: var(--assistant-bg);
            border: 1px solid var(--assistant-border);
            border-radius: 22px;
            box-shadow: var(--assistant-shadow);
            z-index: 1200;
            display: none;
            overflow: hidden;
            color: var(--assistant-text);
            backdrop-filter: blur(18px);
        }

        .assistant-panel.open {
            display: flex;
            flex-direction: column;
            animation: assistantPanelIn 0.22s ease-out;
        }

        @keyframes assistantPanelIn {
            from {
                opacity: 0;
                transform: translateY(14px) scale(0.97);
            }
            to {
                opacity: 1;
                transform: translateY(0) scale(1);
            }
        }

        .assistant-head {
            position: relative;
            padding: 16px;
            background: var(--assistant-head-bg);
            color: #ffffff;
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 12px;
        }

        .assistant-head::after {
            content: "";
            position: absolute;
            left: 18px;
            right: 18px;
            bottom: 0;
            height: 1px;
            background: linear-gradient(90deg, transparent, rgba(250, 204, 21, 0.78), transparent);
        }

        .assistant-brand {
            display: flex;
            align-items: center;
            gap: 12px;
            min-width: 0;
        }

        .assistant-orb {
            position: relative;
            display: grid;
            place-items: center;
            width: 44px;
            height: 44px;
            flex: 0 0 44px;
            border: 1px solid rgba(250, 204, 21, 0.56);
            border-radius: 18px;
            background:
                linear-gradient(145deg, rgba(255, 255, 255, 0.2), rgba(255, 255, 255, 0.04)),
                rgba(250, 204, 21, 0.14);
            box-shadow:
                inset 0 1px 0 rgba(255, 255, 255, 0.2),
                0 12px 26px rgba(15, 23, 42, 0.22);
        }

        .assistant-orb::after {
            content: "";
            position: absolute;
            right: 7px;
            bottom: 7px;
            width: 8px;
            height: 8px;
            border: 2px solid #70131b;
            border-radius: 50%;
            background: #22c55e;
        }

        .assistant-orb svg {
            width: 24px;
            height: 24px;
            color: #fde68a;
        }

        .assistant-head-title {
            margin: 0;
            color: #ffffff;
            font-size: 16px;
            font-weight: 900;
            letter-spacing: 0;
        }

        .assistant-head-sub {
            margin: 2px 0 0;
            color: rgba(255, 255, 255, 0.8);
            font-size: 12px;
            line-height: 1.35;
            opacity: 0.86;
        }

        .assistant-head-actions {
            display: flex;
            align-items: center;
            gap: 8px;
            flex: 0 0 auto;
        }

        .assistant-status-pill {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            min-height: 28px;
            padding: 0 9px;
            border: 1px solid rgba(250, 204, 21, 0.36);
            border-radius: 999px;
            background: rgba(255, 255, 255, 0.11);
            color: #fde68a;
            font-size: 10px;
            font-weight: 900;
            text-transform: uppercase;
            letter-spacing: 0.06em;
        }

        .assistant-status-dot {
            width: 7px;
            height: 7px;
            border-radius: 50%;
            background: #22c55e;
            box-shadow: 0 0 0 4px rgba(34, 197, 94, 0.14);
        }

        .assistant-close {
            display: grid;
            place-items: center;
            border: 1px solid rgba(255, 255, 255, 0.32);
            background: rgba(255, 255, 255, 0.12);
            color: #ffffff;
            width: 32px;
            height: 32px;
            border-radius: 50%;
            cursor: pointer;
            transition: background 0.18s ease, border-color 0.18s ease, transform 0.18s ease;
        }

        .assistant-close:hover,
        .assistant-close:focus-visible {
            background: rgba(250, 204, 21, 0.18);
            border-color: rgba(250, 204, 21, 0.72);
            transform: translateY(-1px);
            outline: none;
        }

        .assistant-close svg {
            width: 17px;
            height: 17px;
        }

        .assistant-messages {
            flex: 1 1 auto;
            min-height: 260px;
            max-height: none;
            overflow-y: auto;
            padding: 16px;
            background:
                radial-gradient(circle at 12% 0%, rgba(250, 204, 21, 0.09) 0%, transparent 32%),
                var(--assistant-surface);
            display: flex;
            flex-direction: column;
            gap: 10px;
            scrollbar-width: thin;
        }

        .assistant-bubble {
            position: relative;
            padding: 11px 13px;
            border-radius: 16px;
            font-size: 13px;
            line-height: 1.5;
            border: 1px solid var(--assistant-soft-border);
            max-width: 88%;
            white-space: pre-wrap;
            box-shadow: 0 8px 20px rgba(15, 23, 42, 0.08);
        }

        .assistant-bubble.user {
            margin-left: auto;
            border-bottom-right-radius: 6px;
            background: var(--assistant-user-bg);
            border-color: rgba(250, 204, 21, 0.34);
            color: var(--assistant-user-text);
        }

        .assistant-bubble.assistant {
            margin-right: auto;
            border-bottom-left-radius: 6px;
            background: var(--assistant-ai-bg);
            border-color: var(--assistant-soft-border);
            color: var(--assistant-ai-text);
        }

        .assistant-bubble.assistant::before {
            content: "AI";
            display: inline-flex;
            align-items: center;
            justify-content: center;
            width: 24px;
            height: 24px;
            margin-right: 8px;
            border-radius: 8px;
            background: rgba(250, 204, 21, 0.16);
            color: #facc15;
            font-size: 10px;
            font-weight: 900;
            vertical-align: top;
        }

        .assistant-quick-prompts {
            display: flex;
            gap: 8px;
            padding: 12px 14px 0;
            overflow-x: auto;
            background: var(--assistant-surface);
            border-top: 1px solid var(--assistant-soft-border);
        }

        .assistant-prompt-chip {
            flex: 0 0 auto;
            border: 1px solid var(--assistant-border);
            border-radius: 999px;
            background: var(--assistant-chip-bg);
            color: var(--assistant-chip-text);
            padding: 9px 12px;
            font: inherit;
            font-size: 12px;
            font-weight: 900;
            cursor: pointer;
            transition: transform 0.18s ease, border-color 0.18s ease, background 0.18s ease;
        }

        .assistant-prompt-chip:hover,
        .assistant-prompt-chip:focus-visible {
            border-color: #facc15;
            transform: translateY(-1px);
            outline: none;
        }

        .assistant-controls {
            border-top: 1px solid var(--assistant-soft-border);
            padding: 12px 14px;
            display: flex;
            gap: 8px;
            align-items: center;
            background: var(--assistant-surface);
        }

        .assistant-input-shell {
            flex: 1;
            display: flex;
            align-items: center;
            min-width: 0;
            border: 1px solid var(--assistant-soft-border);
            border-radius: 14px;
            background: var(--assistant-input-bg);
            transition: border-color 0.18s ease, box-shadow 0.18s ease;
        }

        .assistant-input-shell:focus-within {
            border-color: #facc15;
            box-shadow: 0 0 0 3px rgba(250, 204, 21, 0.16);
        }

        .assistant-mic,
        .assistant-send {
            border: 1px solid rgba(250, 204, 21, 0.42);
            background: linear-gradient(135deg, #8b1020 0%, #6e1220 100%);
            color: #ffffff;
            padding: 11px 14px;
            border-radius: 14px;
            font-size: 13px;
            font-weight: 900;
            cursor: pointer;
            min-height: 42px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 7px;
            transition: transform 0.18s ease, box-shadow 0.18s ease, border-color 0.18s ease;
        }

        .assistant-send:hover,
        .assistant-send:focus-visible {
            border-color: #facc15;
            box-shadow: 0 10px 22px rgba(127, 29, 45, 0.22);
            transform: translateY(-1px);
            outline: none;
        }

        .assistant-send svg {
            width: 15px;
            height: 15px;
        }

        .assistant-mic.listening {
            background: #70131B;
            color: #ffffff;
            border-color: #70131B;
        }

        .assistant-mic:disabled {
            opacity: 0.55;
            cursor: not-allowed;
        }

        .assistant-input {
            width: 100%;
            min-width: 0;
            flex: 1;
            border: 0;
            border-radius: 14px;
            padding: 11px 12px;
            font-size: 13px;
            color: var(--assistant-input-text);
            background: transparent;
        }

        .assistant-input:focus {
            outline: none;
        }

        .assistant-input::placeholder {
            color: var(--assistant-muted);
            opacity: 0.72;
        }

        .assistant-note {
            margin: 0;
            padding: 10px 14px 13px;
            font-size: 11px;
            line-height: 1.45;
            color: var(--assistant-note-text);
            border-top: 1px solid var(--assistant-soft-border);
            background: var(--assistant-note-bg);
        }

        .assistant-note strong {
            color: inherit;
        }

        @media (max-width: 860px) {
            .assistant-panel {
                right: 12px;
                bottom: 10px;
                width: min(500px, calc(100vw - 24px));
                height: min(540px, calc(100vh - 20px));
                max-height: calc(100vh - 20px);
            }

            .assistant-launch {
                display: none;
            }
        }

        @media (max-width: 620px) {
            .assistant-panel {
                right: 8px;
                bottom: 8px;
                width: calc(100vw - 16px);
                height: calc(100vh - 16px);
                border-radius: 18px;
            }

            .assistant-head {
                padding: 16px;
            }

            .assistant-status-pill {
                display: none;
            }

            .assistant-messages {
                min-height: 260px;
                padding: 16px;
            }

            .assistant-bubble {
                max-width: 94%;
            }

            .assistant-controls {
                align-items: stretch;
            }
        }
        /* Container for the name and button */
.medicine-alert-name-row {
      display: flex;
      justify-content: space-between; /* Pushes name to left, button to right */
      align-items: center;
      width: 100%;
      min-width: 0;
      margin-bottom: 5px; /* Space between name and chips */
  }
  
  /* The name itself */
.medicine-alert-item-name {
      margin: 0;
      font-weight: bold;
      font-size: 1rem;
      min-width: 0;
      max-width: 100%;
      overflow-wrap: anywhere;
      word-break: break-word;
  }

.medicine-alert-content {
    width: 100%;
    min-width: 0;
    max-width: 100%;
    overflow: hidden;
    background: inherit;
}

/* Alert state indicators */
.is-near-expiry {
    border-right: 4px solid #f59e0b;
}

.is-expired {
    border-right: 4px solid #dc3545;
    background: #fee2e2;
}

.is-notification-appointment {
    border-right: 4px solid #2563eb;
    background: #dbeafe;
}

.is-notification-scheduled {
    border-right: 4px solid #059669;
    background: #d1fae5;
}

.is-notification-health {
    border-right: 4px solid #7c3aed;
    background: #ede9fe;
}
.medicine-alert-more-wrapper {
    padding: 12px;
    text-align: center;
    border-top: 1px solid rgba(0,0,0,0.1);
    background: rgba(255, 255, 255, 0.05);
}

.medicine-see-more-link {
    background: none;
    border: none;
    padding: 0;
    color: #800000; /* PUP Maroon */
    font-size: 0.85rem;
    font-weight: bold;
    text-decoration: none;
    transition: color 0.2s, text-decoration 0.2s;
    cursor: pointer;
}

.medicine-see-more-link:hover {
    color: #a71d2a;
    text-decoration: underline;
}

.medicine-alert-mode-btn {
    display: none;
    align-items: center;
    gap: 8px;
    padding: 8px 12px;
    border-radius: 999px;
    border: 1px solid rgba(127, 29, 45, 0.14);
    background: rgba(255, 255, 255, 0.96);
    color: #7f1d2d;
    font-size: 12px;
    font-weight: 800;
    cursor: pointer;
}

.medicine-alert-mode-btn.is-visible {
    display: inline-flex;
}

.medicine-alert-mode-btn svg {
    width: 14px;
    height: 14px;
    flex: 0 0 auto;
}

.medicine-alert-section.is-hidden {
    display: none;
}

.medicine-alert-item.is-hidden {
    display: none;
}

.medicine-alert-extra-list {
    display: grid;
    gap: 10px;
    margin-top: 12px;
    padding-top: 12px;
    border-top: 1px dashed rgba(127, 29, 45, 0.18);
}

  html[data-theme="dark"] .medicine-see-more-link {
      color: #ffffff;
  }

  html[data-theme="dark"] .medicine-alert-actions-toggle,
  html[data-theme="dark"] .medicine-alert-actions-menu,
  html[data-theme="dark"] .medicine-alert-empty,
  html[data-theme="dark"] .medicine-alert-mode-btn {
      background: rgba(17, 24, 39, 0.96);
      border-color: rgba(250, 204, 21, 0.16);
      color: #f8fafc;
  }

  html[data-theme="dark"] .medicine-alert-actions-toggle {
      box-shadow: 0 10px 22px rgba(0, 0, 0, 0.28);
  }

  html[data-theme="dark"] .medicine-alert-actions-toggle,
  html[data-theme="dark"] .medicine-alert-close {
      color: #ffffff;
  }

  html[data-theme="dark"] .medicine-alert-actions-link,
  html[data-theme="dark"] .medicine-alert-actions-submit,
  html[data-theme="dark"] .medicine-alert-empty-title {
      color: #f8fafc;
      background: rgba(255, 255, 255, 0.03);
  }

  html[data-theme="dark"] .medicine-alert-actions-link:hover,
  html[data-theme="dark"] .medicine-alert-actions-submit:hover {
      background: rgba(127, 29, 45, 0.32);
      color: #fde68a;
  }

  html[data-theme="dark"] .medicine-alert-empty-copy {
      color: #cbd5e1;
  }

html[data-theme="dark"] .medicine-see-more-link:hover {
    color: #f8fafc;
}

/* Ensure the list has a max height if there are many items */
.medicine-alert-list {
      max-height: 400px;
      overflow-y: auto;
      overflow-x: hidden;
  }

.medicine-alert-item-meta {
    min-width: 0;
    width: 100%;
    max-width: 100%;
    overflow: hidden;
    background: inherit;
}

.medicine-alert-chip {
    display: inline-flex;
    min-width: 0;
    max-width: 100%;
    background: #fff7ea;
    white-space: normal;
    overflow-wrap: anywhere;
    word-break: break-word;
}

        @media (max-width: 860px) {
            .medicine-alert-panel {
                right: 12px;
                bottom: 82px;
                width: min(360px, calc(100vw - 24px));
                max-width: calc(100vw - 24px);
            }
        }

        .treatment-record-modal {
            position: fixed;
            inset: 0;
            z-index: 499996;
            display: block;
            pointer-events: none;
        }

        .treatment-record-modal-dialog {
            position: absolute;
            right: 96px;
            bottom: 82px;
            width: min(820px, calc(100vw - 128px));
            height: min(680px, calc(100vh - 118px));
            overflow: hidden;
            display: flex;
            flex-direction: column;
            border: 1px solid rgba(250, 204, 21, 0.48);
            border-radius: 20px;
            background: rgba(255, 248, 249, 0.96);
            box-shadow: 0 22px 48px rgba(15, 23, 42, 0.22);
            opacity: 0;
            visibility: hidden;
            pointer-events: none;
            transform: translateY(16px) scale(0.96);
            transform-origin: bottom right;
            transition:
                width 0.28s ease,
                height 0.28s ease,
                opacity 0.22s ease,
                transform 0.24s ease,
                visibility 0s linear 0.24s;
        }

        .treatment-record-modal.is-open .treatment-record-modal-dialog {
            opacity: 1;
            visibility: visible;
            pointer-events: auto;
            transform: translateY(0) scale(1);
            transition:
                width 0.28s ease,
                height 0.28s ease,
                opacity 0.22s ease,
                transform 0.24s ease,
                visibility 0s linear 0s;
        }

        .treatment-record-modal.is-expanded .treatment-record-modal-dialog {
            right: 24px;
            bottom: 24px;
            width: calc(100vw - 48px);
            height: calc(100vh - 48px);
        }

        .treatment-record-modal-head {
            display: flex;
            align-items: center;
            gap: 11px;
            min-height: 64px;
            padding: 14px 18px;
            border-radius: 19px 19px 0 0;
            background: #70131b;
            color: #ffffff;
        }

        .treatment-record-modal-head > svg {
            width: 24px;
            height: 24px;
            flex: 0 0 auto;
            color: #ffffff;
        }

        .treatment-record-modal-copy {
            min-width: 0;
        }

        .treatment-record-modal-title {
            margin: 0;
            color: #ffffff;
            font-size: 17px;
            font-weight: 900;
        }

        .treatment-record-modal-subtitle {
            margin: 3px 0 0;
            color: rgba(255, 255, 255, 0.82);
            font-size: 11px;
            line-height: 1.4;
        }

        .treatment-record-modal-close {
            display: grid;
            place-items: center;
            width: 30px;
            height: 30px;
            flex: 0 0 30px;
            margin-left: auto;
            padding: 0;
            border: 1px solid #facc15;
            border-radius: 50%;
            background: linear-gradient(90deg, #8f2230 0 50%, #70131b 50% 100%);
            background-size: 205% 100%;
            background-position: 100% 0;
            color: #ffffff;
            font-size: 19px;
            line-height: 1;
            cursor: pointer;
            transition: background-position 0.32s ease, border-color 0.18s ease, box-shadow 0.18s ease;
        }

        .treatment-record-modal-close:hover,
        .treatment-record-modal-close:focus {
            border-color: #facc15;
            background-position: 0 0;
            color: #ffffff;
            box-shadow: 0 8px 18px rgba(15, 23, 42, 0.2);
            outline: none;
        }

        .treatment-record-modal-expand {
            position: absolute;
            top: 50%;
            left: -28px;
            z-index: 2;
            display: grid;
            place-items: center;
            width: 28px;
            height: 56px;
            padding: 0;
            overflow: hidden;
            border: 2px solid #facc15;
            border-right: 0;
            border-radius: 30px 0 0 30px;
            background: linear-gradient(90deg, #fff2f4 0 50%, rgba(255, 248, 249, 0.96) 50% 100%);
            background-size: 205% 100%;
            background-position: 100% 0;
            color: #70131b;
            font-size: 18px;
            font-weight: 900;
            line-height: 1;
            cursor: pointer;
            transform: translateY(-50%);
            box-shadow: -5px 5px 14px rgba(15, 23, 42, 0.2);
            transition: background-position 0.3s ease, border-color 0.18s ease, color 0.18s ease;
        }

        .treatment-record-modal-expand:hover,
        .treatment-record-modal-expand:focus {
            border-color: #facc15;
            background-position: 0 0;
            color: #70131b;
            outline: none;
        }

        .treatment-record-modal-frame {
            display: block;
            width: 100%;
            height: auto;
            min-height: 0;
            flex: 1 1 auto;
            border: 0;
            border-radius: 0 0 19px 19px;
            background: #f8fafc;
        }

        body.admin-embedded-view {
            background: #f8fafc;
        }

        body.admin-embedded-view .admin-header,
        body.admin-embedded-view .sidebar,
        body.admin-embedded-view .assistant-panel,
        body.admin-embedded-view .medicine-alert-panel,
        body.admin-embedded-view .admin-live-alert-stack,
        body.admin-embedded-view .quick-actions-wrap {
            display: none !important;
        }

        body.admin-embedded-view .admin-layout,
        body.admin-embedded-view .main {
            display: block;
            width: 100%;
            min-height: 100vh;
            margin: 0 !important;
            padding: 0 !important;
        }

        body.admin-embedded-view .treatment-record-shell {
            max-width: none;
            padding: 14px;
        }

        body.admin-embedded-view .treatment-record-header {
            display: none;
        }

        body.admin-embedded-view .form-b-title-row {
            align-items: center;
            flex-direction: row;
            justify-content: space-between;
        }

        body.admin-embedded-view .logbook-search {
            width: min(100%, 330px);
            flex: 0 1 330px;
        }

        @media (max-width: 700px) {
            .treatment-record-modal-dialog,
            .treatment-record-modal.is-expanded .treatment-record-modal-dialog {
                right: 12px;
                bottom: 82px;
                width: calc(100vw - 24px);
                height: calc(100vh - 106px);
            }

            .treatment-record-modal-expand {
                display: none;
            }

            .treatment-record-modal-subtitle {
                display: none;
            }

            body.admin-embedded-view .form-b-title-row {
                align-items: stretch;
                flex-direction: column;
            }

            body.admin-embedded-view .logbook-search {
                width: 100%;
                flex-basis: auto;
            }
        }
    </style>
</head>
<body class="{{ (request()->routeIs('admin.inventory*') || request()->routeIs('assistant.inventory*')) ? 'admin-inventory-page' : '' }} {{ request()->boolean('embed') ? 'admin-embedded-view' : '' }}">
@php
    $authUser = auth()->user();
    $currentRole = \App\Models\User::normalizeRole(optional($authUser)->user_role ?? '');
    $currentUserType = strtolower(trim((string) (optional($authUser)->user_type ?? '')));
    $isStudentAssistant = $currentRole === \App\Models\User::ROLE_ADMIN
        && in_array($currentUserType, ['assistant', 'student assistant', 'student_assistant'], true);
    $isAdminLike = $currentRole === \App\Models\User::ROLE_SUPERADMIN;
    $linkedAdminProfile = null;
    $adminTypeLabel = null;
    if ($authUser && $currentRole === \App\Models\User::ROLE_ADMIN) {
        $email = trim((string) ($authUser->email ?? ''));
        if (\App\Models\Admin::hasColumn('user_id')) {
            $linkedAdminProfile = \App\Models\Admin::query()
                ->where('user_id', $authUser->id)
                ->first();
        }
        if (!$linkedAdminProfile && $email !== '') {
            $linkedAdminProfile = \App\Models\Admin::query()
                ->where(function ($query) use ($email) {
                    $query->where('email', $email);
                    if (\App\Models\Admin::hasColumn('email_address')) {
                        $query->orWhere('email_address', $email);
                    }
                })
                ->first();

        }

        if ($isStudentAssistant) {
            $adminTypeLabel = 'Admin - Student Assistant';
        } else {
            $accessLevel = strtolower(trim((string) (
                $linkedAdminProfile?->access_level
                ?? $linkedAdminProfile?->admin_hub_role
                ?? ''
            )));
            if (in_array($accessLevel, ['designee', 'admin_designee'], true)) {
                $adminTypeLabel = 'Admin - Designee';
            } elseif (in_array($accessLevel, ['clinic_staff', 'clinic staff', 'staff'], true)) {
                $adminTypeLabel = 'Admin - Clinic Staff';
            }
        }
    }
    $dashboardUrl = $isStudentAssistant ? url('/assistant/dashboard') : url('/admin/dashboard');
    $appointmentsUrl = $isStudentAssistant ? url('/assistant/appointments') : url('/admin/appointments');
    $inventoryUrl = $isStudentAssistant ? url('/assistant/inventory') : url('/admin/inventory');
    $reportsUrl = $isStudentAssistant ? url('/assistant/reports') : url('/admin/reports');
    $dailyTreatmentRecordUrl = $isStudentAssistant
        ? url('/assistant/reports/daily-treatment-record')
        : url('/admin/reports/daily-treatment-record');
    $healthRecordsUrl = route('admin.health_records');
    $adminNotificationsFeedUrl = $isStudentAssistant
        ? route('assistant.notifications.feed')
        : route('admin.notifications.feed');
    $workflowSettings = app(\App\Services\ClinicWorkflowService::class)->settings();
    $adminLiveNotificationsEnabled = $workflowSettings->admin_live_notifications !== false;
    $apiTestingUrl = $isStudentAssistant ? url('/assistant/api-testing') : url('/admin/api-testing');
    $developerToolsUrl = $isStudentAssistant ? url('/assistant/developer-tools') : url('/admin/developer-tools');
    $canSeeDeveloperTools = strtolower(trim((string) optional($authUser)->email)) === 'pupocms2027@gmail.com';
    $settingsUrl = url('/admin/settings');
    $walkinUrl = $isStudentAssistant ? url('/assistant/walkin') : url('/admin/walkin');
    $assistantEndpoint = $isStudentAssistant ? route('assistant.intent') : route('admin.assistant.intent');
    $displayName = optional($authUser)->name ?? 'Clinic User';
    $welcomeFirstName = trim((string) (optional($authUser)->first_name ?? ''));
    $welcomeLastName = trim((string) (optional($authUser)->last_name ?? ''));
    if ($welcomeFirstName === '') {
        $welcomeNameParts = preg_split('/\s+/', trim((string) $displayName), -1, PREG_SPLIT_NO_EMPTY) ?: [];
        $welcomeFirstName = $welcomeNameParts[0] ?? 'Clinic User';
        $welcomeLastName = count($welcomeNameParts) > 1 ? (string) end($welcomeNameParts) : '';
    }
    $welcomeName = in_array($displayName, ['Admin Account', 'Super Admin Account'], true)
        ? 'Nurse J.'
        : trim($welcomeFirstName . ($welcomeLastName !== '' ? ' ' . strtoupper(substr($welcomeLastName, 0, 1)) . '.' : ''));
    $avatarInitial = strtoupper(substr($displayName, 0, 1));
    $brandLogo = asset('images/clinic_logo_transparent.png') . '?v=' . filemtime(public_path('images/clinic_logo_transparent.png'));
    $brandUniversityLogo = asset('images/pup_logo.png');
    \App\Models\Appointment::expireOverduePending();
    $roleLabelMap = [
        'superadmin' => 'Super Admin',
        'admin' => 'Admin',
        'super_admin' => 'Super Admin',
        'student_assistant' => 'Student Assistant',
    ];
    $displayRole = $adminTypeLabel ?: ($roleLabelMap[$currentRole] ?? ucfirst($currentRole ?: 'user'));
    $medicineAlertsQuery = \App\Models\Item::query()
        ->where('category', 'Medicine')
        ->whereNotNull('expiration_date')
        ->whereDate('expiration_date', '<=', now()->addDays(30)->toDateString())
        ->orderBy('expiration_date');
    $medicineAlertCount = (clone $medicineAlertsQuery)->count();
    $medicineAlerts = $medicineAlertsQuery
        ->limit(8)
        ->get();
    $adminNotificationReadMap = is_array(optional(auth()->user())->notification_read_map ?? null)
        ? (optional(auth()->user())->notification_read_map ?? [])
        : [];
    $adminMarkAllReadUrl = $isStudentAssistant
        ? route('assistant.notifications.read_all')
        : route('admin.notifications.read_all');
    $recentPendingAppointments = \App\Models\Appointment::query()
        ->where('status', 'Pending')
        ->orderByDesc('created_at')
        ->limit(4)
        ->get();
    $todayApprovedAppointments = \App\Models\Appointment::query()
        ->where('status', 'Approved')
        ->whereDate('date', now()->toDateString())
        ->orderBy('time')
        ->limit(3)
        ->get();
    $recentExpiredAppointments = \App\Models\Appointment::query()
        ->where('status', 'Expired')
        ->orderByDesc('updated_at')
        ->limit(4)
        ->get();
    $recentHealthFormSubmissions = $isStudentAssistant
        ? collect()
        : \App\Models\HealthProfile::query()
            ->with('user')
            ->where(function ($query) {
                $query->whereIn('clearance_status', ['Pending', 'For Verification'])
                    ->orWhereNull('clearance_status')
                    ->orWhere('clearance_status', '');
            })
            ->latest('created_at')
            ->limit(3)
            ->get();

    $adminNotifications = collect();

    foreach ($recentPendingAppointments as $appointment) {
        $adminNotifications->push([
            'id' => 'appointment-pending:' . $appointment->id . ':' . optional($appointment->updated_at)->timestamp,
            'type' => 'appointment',
            'title' => 'New appointment request',
            'link' => $appointmentsUrl . '?highlight_appointment=' . $appointment->id,
            'hover_hint' => implode(' | ', array_filter([
                'Name: ' . trim((string) ($appointment->name ?? 'Unknown patient')),
                'Date: ' . trim((string) ($appointment->date ?? 'N/A')),
                'Time: ' . trim((string) ($appointment->time ?? 'N/A')),
            ])),
            'state_class' => 'is-notification-appointment',
            'chips' => array_filter([
                'Pending',
            ]),
        ]);
    }

    foreach ($todayApprovedAppointments as $appointment) {
        $adminNotifications->push([
            'id' => 'appointment-approved:' . $appointment->id . ':' . optional($appointment->updated_at)->timestamp,
            'type' => 'appointment',
            'title' => 'Scheduled consultation today',
            'link' => $appointmentsUrl . '?highlight_appointment=' . $appointment->id,
            'hover_hint' => implode(' | ', array_filter([
                'Name: ' . trim((string) ($appointment->name ?? 'Unknown patient')),
                'Date: ' . trim((string) ($appointment->date ?? now()->toDateString())),
                'Time: ' . trim((string) ($appointment->time ?? 'N/A')),
            ])),
            'state_class' => 'is-notification-scheduled',
            'chips' => array_filter([
                'Status: Approved',
            ]),
        ]);
    }

    foreach ($recentExpiredAppointments as $appointment) {
        $adminNotifications->push([
            'id' => 'appointment-expired:' . $appointment->id . ':' . optional($appointment->updated_at)->timestamp,
            'type' => 'appointment',
            'title' => 'Appointment expired before approval',
            'link' => $appointmentsUrl . '?highlight_appointment=' . $appointment->id,
            'hover_hint' => implode(' | ', array_filter([
                'Name: ' . trim((string) ($appointment->name ?? 'Unknown patient')),
                'Service: ' . trim((string) ($appointment->service ?? 'General consultation')),
                'Date: ' . trim((string) ($appointment->date ?? 'N/A')),
                'Time: ' . trim((string) ($appointment->time ?? 'N/A')),
            ])),
            'state_class' => 'is-expired',
            'chips' => array_filter([
                'Expired',
                'Needs rebooking',
            ]),
        ]);
    }

    foreach ($recentHealthFormSubmissions as $healthProfile) {
        $profileUser = $healthProfile->user;
        $studentName = trim((string) ($profileUser->name ?? 'Unknown student'));
        $studentNumber = trim((string) ($profileUser->student_number ?? $profileUser->student_id ?? 'N/A'));

        $adminNotifications->push([
            'id' => 'health-form:' . $healthProfile->id . ':' . optional($healthProfile->created_at)->timestamp,
            'type' => 'health-form',
            'title' => 'New health form submission',
            'link' => $healthRecordsUrl . '?tab=pending_approval&highlight_health=' . $healthProfile->id,
            'hover_hint' => implode(' | ', array_filter([
                'Name: ' . $studentName,
                'Student No: ' . $studentNumber,
                'Submitted: ' . optional($healthProfile->created_at)->format('M d, Y g:i A'),
            ])),
            'state_class' => 'is-notification-health',
            'chips' => array_filter([
                'Pending review',
            ]),
        ]);
    }

    foreach ($medicineAlerts as $medicineAlert) {
        $isExpired = optional($medicineAlert->expiration_date)->isPast();
        $daysLeft = $medicineAlert->expiration_date ? now()->diffInDays($medicineAlert->expiration_date, false) : null;

        $adminNotifications->push([
            'id' => 'medicine-alert:' . $medicineAlert->id . ':' . optional($medicineAlert->updated_at)->timestamp . ':' . optional($medicineAlert->expiration_date)->timestamp,
            'type' => 'medicine',
            'title' => $isExpired ? 'Expired medicine alert' : 'Medicine expiry alert',
            'link' => $inventoryUrl . '?highlight_item=' . $medicineAlert->id,
            'hover_hint' => implode(' | ', array_filter([
                'Medicine: ' . trim((string) $medicineAlert->name),
                'Stock: ' . $medicineAlert->quantity . ' units',
                'Expiry: ' . (optional($medicineAlert->expiration_date)->format('M d, Y') ?? 'N/A'),
                $isExpired ? 'Status: Expired' : ($daysLeft !== null ? 'Status: ' . $daysLeft . ' day(s) left' : 'Status: Near expiry'),
            ])),
            'state_class' => $isExpired ? 'is-expired' : 'is-near-expiry',
            'chips' => array_filter([
                $isExpired ? 'Expired' : 'Near expiry',
            ]),
        ]);
    }

    $allAdminNotifications = $adminNotifications
        ->map(function (array $notification) use ($adminNotificationReadMap) {
            $notification['is_unread'] = !isset($adminNotificationReadMap[$notification['id']]);
            return $notification;
        })
        ->values();

    $adminNotifications = $allAdminNotifications
        ->filter(fn (array $notification) => $notification['is_unread'])
        ->take(10)
        ->values();
    $adminNotificationCount = $adminNotifications->count();
    $adminNotificationPreview = $adminNotifications->take(4);
    $adminNotificationOverflow = $adminNotifications->slice(4)->values();
    $adminNotificationHistory = $allAdminNotifications->take(20)->values();
    $adminLiveAlertItems = $adminNotifications
        ->filter(function (array $notification) {
            $id = (string) ($notification['id'] ?? '');
            return str_starts_with($id, 'appointment-pending:') || str_starts_with($id, 'health-form:');
        })
        ->take(2)
        ->map(function (array $notification) {
            $id = (string) ($notification['id'] ?? '');
            $isHealth = str_starts_with($id, 'health-form:');
            return [
                'id' => $id,
                'kind' => $isHealth ? 'health' : 'appointment',
                'title' => (string) ($notification['title'] ?? ($isHealth ? 'New health form submission' : 'New appointment request')),
                'message' => $isHealth
                    ? 'A student submitted a health record for verification.'
                    : 'A new appointment request is waiting for review.',
                'link' => (string) ($notification['link'] ?? ''),
            ];
        })
        ->values();
@endphp

<header class="admin-header">
    <div class="header-left">
        <div class="header-brand-lockup">
            <img src="{{ $brandUniversityLogo }}" alt="PUP Logo" class="header-brand-avatar">
            <img src="{{ $brandLogo }}" alt="Clinic Logo" class="header-brand-avatar header-brand-avatar--clinic">
        </div>
        <div class="header-copy">
            <p class="header-kicker">{{ $adminTypeLabel ? 'Clinic Administration' : ($isStudentAssistant ? 'Clinic Assistant Console' : 'Clinic Administration') }}</p>
            <h1 class="header-title">Welcome back, <span>{{ $welcomeName }}</span></h1>
            <p class="header-subtitle">Monitor operations and patient flow in one clear workspace.</p>
        </div>
    </div>

    <div class="header-right">
        <button type="button" class="sidebar-toggle" aria-label="Toggle sidebar" onclick="toggleSidebar()">
            <x-outline-icon name="bars-3" />
        </button>
        <button type="button" class="assistant-launch" id="assistantLaunchBtn" onclick="toggleAssistantPanel()">
            <x-outline-icon name="sparkles" />
            <span>AI Assistant</span>
        </button>
        <div class="quick-actions-wrap" id="headerQuickActions">
            <button type="button" class="quick-actions-toggle" aria-label="Open quick actions" aria-expanded="false" onclick="toggleHeaderQuickActions()">
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" aria-hidden="true" focusable="false">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 5.25v13.5M5.25 12h13.5" />
                </svg>
                @if($adminNotificationCount > 0)
                    <span class="quick-action-badge">{{ $adminNotificationCount }}</span>
                @endif
            </button>
            <div class="quick-actions-panel" id="headerQuickActionsPanel">
                <div class="quick-action-item">
                    <button type="button" class="quick-action-btn" id="openTreatmentRecordModal" aria-label="Daily Treatment Record">
                        <x-outline-icon name="clipboard-document-list" />
                    </button>
                    <span class="quick-action-tooltip">Daily Treatment Record</span>
                </div>
                <div class="quick-actions-divider" aria-hidden="true"></div>
                <div class="quick-action-item">
                    <a href="{{ $walkinUrl }}" class="quick-action-btn" aria-label="Open Walk-in">
                        <x-outline-icon name="qr-code" />
                    </a>
                    <span class="quick-action-tooltip">Walk-in</span>
                </div>
                <div class="quick-actions-divider" aria-hidden="true"></div>
                <div class="quick-action-item">
                    <button type="button" class="quick-action-btn quick-action-bell" data-medicine-alert-toggle aria-label="Notifications">
                        <x-outline-icon name="bell" />
                        @if($adminNotificationCount > 0)
                            <span class="quick-action-badge">{{ $adminNotificationCount }}</span>
                        @endif
                    </button>
                    <span class="quick-action-tooltip">Notifications</span>
                </div>
                <div class="quick-action-item">
                    <button type="button" class="theme-toggle-admin quick-action-btn" id="adminThemeToggle" aria-pressed="false" aria-label="Theme mode">
                        <x-outline-icon name="sun" />
                    </button>
                    <span class="quick-action-tooltip" id="themeModeTooltip">Dark Mode</span>
                </div>
                <div class="quick-action-item is-accessibility">
                    <button type="button" class="accessibility-launch-admin quick-action-btn" id="adminAccessibilityLaunch" aria-label="Accessibility menu">
                        <x-outline-icon name="accessibility-person" />
                    </button>
                    <span class="quick-action-tooltip">Accessibility</span>
                </div>
            </div>
        </div>

        <div class="profile-wrap" id="profileWrap">
            <button type="button" class="admin-user" onclick="toggleProfileMenu()" aria-expanded="false" aria-controls="profileDropdown" aria-label="Account menu">
                <div class="admin-user-meta">
                    <div class="admin-user-name">{{ $displayName }}</div>
                    <div class="admin-user-role">{{ $displayRole }}</div>
                </div>
                <div class="user-avatar">{{ $avatarInitial }}</div>
                <x-outline-icon name="chevron-down" class="admin-user-chevron" />
            </button>

            <div id="profileDropdown" class="profile-dropdown">
                @if($isAdminLike)
                    <a href="{{ $settingsUrl }}">
                        <x-outline-icon name="cog-6-tooth" />
                        <span>Settings</span>
                    </a>
                @endif
                @if($isStudentAssistant)
                    <a href="{{ route('assistant.enter-student') }}" class="switch-student-link">
                        <x-outline-icon name="user-plus" />
                        <span>Switch to Student Workspace</span>
                    </a>
                @endif
                <a href="#" class="logout-link" onclick="event.preventDefault(); document.getElementById('layoutLogoutForm').submit();">
                    <x-outline-icon name="arrow-left-on-rectangle" />
                    <span>Logout</span>
                </a>
            </div>
        </div>
    </div>
</header>

<div class="admin-layout">
  
  <aside class="sidebar" id="adminSidebar">
    <div class="sidebar-logo">
      <div class="sidebar-logo-badges">
        
      </div>
      <div class="sidebar-logo-text">
        <div class="sidebar-logo-title">PUP TAGUIG</div>
        <div class="sidebar-logo-sub">{{ $adminTypeLabel ? 'Clinic Admin' : ($isStudentAssistant ? 'Clinic Assistant' : 'Clinic Admin') }}</div>
      </div>
    </div>
    
    <h4>Main Menu</h4>
    <nav class="sidebar-nav">
      <a href="{{ $dashboardUrl }}" class="nav-dashboard {{ (request()->routeIs('admin.dashboard') || request()->routeIs('assistant.dashboard')) ? 'active' : '' }}">
        <span class="sidebar-short"><x-outline-icon name="squares-2x2" /></span><span class="sidebar-label">Dashboard</span>
      </a>
      <a href="{{ $appointmentsUrl }}" class="nav-appointments {{ (request()->routeIs('admin.appointments*') || request()->routeIs('assistant.appointments*')) ? 'active' : '' }}">
        <span class="sidebar-short"><x-outline-icon name="calendar-days" /></span><span class="sidebar-label">Appointments</span>
      </a>
      <a href="{{ $inventoryUrl }}" class="nav-inventory {{ (request()->routeIs('admin.inventory*') || request()->routeIs('assistant.inventory*')) ? 'active' : '' }}">
        <span class="sidebar-short"><x-outline-icon name="cube" /></span><span class="sidebar-label">Inventory</span>
      </a>
      <a href="{{ $reportsUrl }}" class="nav-reports {{ ((request()->routeIs('admin.reports*') || request()->routeIs('assistant.reports*') || Request::is('admin/reports*') || Request::is('assistant/reports*')) && !(request()->routeIs('admin.reports.manage-mar') || request()->routeIs('admin.reports.manage-medicine-types'))) ? 'active' : '' }}">
        <span class="sidebar-short"><x-outline-icon name="chart-bar" /></span><span class="sidebar-label">Reports</span>
      </a>
     
      <a href="{{ $walkinUrl }}" class="nav-walkin {{ (Request::is('admin/walkin*') || Request::is('assistant/walkin*')) ? 'active' : '' }}">
        <span class="sidebar-short"><x-outline-icon name="user-plus" /></span><span class="sidebar-label">Walk-in</span>
      </a>
      <a href="{{ route('admin.health_records') }}" class="nav-health {{ (request()->routeIs('admin.health_records') || Request::is('health-records') || Request::is('health-profile/*')) ? 'active' : '' }}">
    <span class="sidebar-short"><x-outline-icon name="document-text" /></span>
    <span class="sidebar-label">Health Records</span>
    </a>
      <a href="{{ route('admin.announcements') }}" class="nav-announcements {{ request()->routeIs('admin.announcements') ? 'active' : '' }}">
        <span class="sidebar-short">
          <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" aria-hidden="true">
            <path stroke-linecap="round" stroke-linejoin="round" d="M10.34 15.84c-.688-.06-1.386-.09-2.09-.09H7.5a4.5 4.5 0 1 1 0-9h.75c.704 0 1.402-.03 2.09-.09m0 9.18c.253.962.584 1.892.985 2.783.247.55.06 1.21-.463 1.511l-.657.38c-.551.318-1.26.117-1.527-.461a20.845 20.845 0 0 1-1.44-4.282m3.102.069a18.03 18.03 0 0 1-.59-4.59c0-1.586.205-3.124.59-4.59m0 9.18a23.848 23.848 0 0 1 8.835 2.535M10.34 6.66a23.847 23.847 0 0 0 8.835-2.535m0 0A23.74 23.74 0 0 0 18.795 3m.38 1.125a23.91 23.91 0 0 1 1.014 5.395m-1.014 8.855c-.118.38-.245.754-.38 1.125m.38-1.125a23.91 23.91 0 0 0 1.014-5.395m0-3.46c.495.413.811 1.035.811 1.73 0 .695-.316 1.317-.811 1.73m0-3.46a24.347 24.347 0 0 1 0 3.46" />
          </svg>
        </span><span class="sidebar-label">Announcements</span>
      </a>
      @if($isAdminLike)
          <a href="{{ route('admin.logs') }}" class="nav-audit {{ (request()->routeIs('admin.logs') || Request::is('admin/activity-logs*')) ? 'active' : '' }}">
            <span class="sidebar-short"><x-outline-icon name="clipboard-document-list" /></span><span class="sidebar-label">Audit Trail</span>
          </a>
          <a href="{{ $settingsUrl }}" class="nav-settings {{ (request()->routeIs('admin.settings*') || Request::is('admin/settings*') || request()->routeIs('admin.reports.manage-mar') || request()->routeIs('admin.reports.manage-medicine-types')) ? 'active' : '' }}">
            <span class="sidebar-short"><x-outline-icon name="cog-6-tooth" /></span><span class="sidebar-label">Settings</span>
          </a>
      @endif
      @if($canSeeDeveloperTools)
        <a href="{{ $developerToolsUrl }}" class="{{ (Request::is('admin/developer-tools*') || Request::is('assistant/developer-tools*') || Request::is('admin/api-testing*') || Request::is('assistant/api-testing*')) ? 'active' : '' }}">
          <span class="sidebar-short"><x-outline-icon name="code-bracket-square" /></span><span class="sidebar-label">Developer Tools</span>
        </a>
      @endif

    </nav>

    <button type="button" class="sidebar-scroll-indicator" id="sidebarScrollIndicator" aria-label="Scroll navigation">
      <x-outline-icon name="chevron-down" />
    </button>

    <div class="sidebar-logout">
      <a href="#" onclick="event.preventDefault(); document.getElementById('layoutLogoutForm').submit();">
        <span class="sidebar-short"><x-outline-icon name="arrow-left-on-rectangle" /></span><span class="sidebar-label">Logout</span>
      </a>
    </div>
  </aside>

    <main class="main">
        @yield('content')
    </main>

</div>

<form id="layoutLogoutForm" method="POST" action="{{ route('logout') }}" style="display:none;">
    @csrf
    <input type="hidden" name="portal_guard" value="admin">
    <input type="hidden" name="logout_all" value="1">
</form>

<div class="treatment-record-modal" id="treatmentRecordModal" aria-hidden="true">
    <section class="treatment-record-modal-dialog" role="dialog" aria-modal="true" aria-labelledby="treatmentRecordModalTitle">
        <button
            type="button"
            class="treatment-record-modal-expand"
            id="expandTreatmentRecordModal"
            aria-label="Expand Daily Treatment Record"
            aria-expanded="false"
            title="Expand Daily Treatment Record"
        >&lt;</button>
        <header class="treatment-record-modal-head">
            <x-outline-icon name="clipboard-document-list" />
            <div class="treatment-record-modal-copy">
                <h2 class="treatment-record-modal-title" id="treatmentRecordModalTitle">Daily Treatment Record</h2>
                <p class="treatment-record-modal-subtitle">View the clinic's current digital Form B treatment logbook.</p>
            </div>
            <button type="button" class="treatment-record-modal-close" id="closeTreatmentRecordModal" aria-label="Close Daily Treatment Record">&times;</button>
        </header>
        <iframe
            class="treatment-record-modal-frame"
            id="treatmentRecordModalFrame"
            title="Daily Treatment Record"
            data-src="{{ $dailyTreatmentRecordUrl }}?embed=1"
            scrolling="yes"
        ></iframe>
    </section>
</div>

<section class="medicine-alert-panel" id="medicineAlertPanel" aria-live="polite">
    <div class="medicine-alert-head">
        <div>
            <p class="medicine-alert-title">Notifications</p>
            <p class="medicine-alert-subtitle">New appointments, today's schedules, and medicine alerts.</p>
        </div>
        <div class="medicine-alert-actions">
            <button type="button" class="medicine-alert-mode-btn" id="medicineAlertBackBtn" aria-label="Back to new notifications">
                <x-outline-icon name="chevron-right" style="transform: rotate(180deg);" />
                Back
            </button>
            <button type="button" class="medicine-alert-actions-toggle" id="medicineAlertActionsToggle" aria-label="Notification actions" aria-expanded="false">
                <x-outline-icon name="bars-3" />
            </button>
            <div class="medicine-alert-actions-menu" id="medicineAlertActionsMenu">
                    <form method="POST" action="{{ $adminMarkAllReadUrl }}">
                        @csrf
                        @foreach($adminNotifications as $notification)
                            <input type="hidden" name="notification_ids[]" value="{{ $notification['id'] }}">
                        @endforeach
                        <button type="submit" class="medicine-alert-actions-submit" {{ $adminNotificationCount === 0 ? 'disabled' : '' }}>
                            <x-outline-icon name="check" />
                            Mark all as read
                        </button>
                    </form>
                    <button type="button" class="medicine-alert-actions-submit" id="medicineAlertHistoryBtn">
                        <x-outline-icon name="clock" />
                        Notification history
                    </button>
                </div>
            <button type="button" class="medicine-alert-close" id="medicineAlertCloseBtn" aria-label="Close notifications">
                <x-outline-icon name="x-mark" />
            </button>
        </div>
    </div>

    <div class="medicine-alert-section" id="medicineAlertUnreadSection">
    <div class="medicine-alert-list" id="medicineAlertList">
        @forelse($adminNotificationPreview as $notification)
        <article class="medicine-alert-item {{ $notification['state_class'] }}">
            <a
                href="{{ $notification['link'] }}"
                class="medicine-alert-item-link"
                data-admin-notification-link="true"
                data-admin-notification-target="{{ $notification['link'] }}"
                data-hover-hint="{{ $notification['hover_hint'] }}"
            >
            <div class="medicine-alert-content" style="width: 100%;">
                <div class="medicine-alert-name-row">
                    <p class="medicine-alert-item-name">{{ $notification['title'] }}</p>
                </div>
                
                <div class="medicine-alert-item-meta">
                    @foreach($notification['chips'] as $chip)
                        <span class="medicine-alert-chip">{{ $chip }}</span>
                    @endforeach
                </div>
                
            </div>
            </a>
        </article>
        @empty
        <div class="medicine-alert-empty">
            <p class="medicine-alert-empty-title">No new notifications</p>
            <p class="medicine-alert-empty-copy">You're all caught up for now. New appointment, health form, and medicine alerts will appear here.</p>
        </div>
        @endforelse
    </div>
    @if($adminNotificationOverflow->isNotEmpty())
    <div class="medicine-alert-extra-list" id="medicineAlertExtraList">
        @foreach($adminNotificationOverflow as $notification)
        <article class="medicine-alert-item is-hidden {{ $notification['state_class'] }}" data-medicine-alert-extra="true">
            <a
                href="{{ $notification['link'] }}"
                class="medicine-alert-item-link"
                data-admin-notification-link="true"
                data-admin-notification-target="{{ $notification['link'] }}"
                data-hover-hint="{{ $notification['hover_hint'] }}"
            >
            <div class="medicine-alert-content" style="width: 100%;">
                <div class="medicine-alert-name-row">
                    <p class="medicine-alert-item-name">{{ $notification['title'] }}</p>
                </div>

                <div class="medicine-alert-item-meta">
                    @foreach($notification['chips'] as $chip)
                        <span class="medicine-alert-chip">{{ $chip }}</span>
                    @endforeach
                </div>
            </div>
            </a>
        </article>
        @endforeach
    </div>
    @endif
    @if($adminNotificationCount > 4)
    <div class="medicine-alert-more-wrapper">
        <button type="button" class="medicine-see-more-link" id="medicineAlertMoreBtn">
            Show more ({{ $adminNotificationCount - 4 }})
        </button>
    </div>
    @endif
    </div>

    <div class="medicine-alert-section is-hidden" id="medicineAlertHistorySection">
        <div class="medicine-alert-list">
            @forelse($adminNotificationHistory as $notification)
                <article class="medicine-alert-item {{ $notification['state_class'] }}">
                    <a
                        href="{{ $notification['link'] }}"
                        class="medicine-alert-item-link"
                        data-admin-notification-link="true"
                        data-admin-notification-target="{{ $notification['link'] }}"
                        data-hover-hint="{{ $notification['hover_hint'] }}"
                    >
                    <div class="medicine-alert-content" style="width: 100%;">
                        <div class="medicine-alert-name-row">
                            <p class="medicine-alert-item-name">
                                {{ $notification['title'] }}
                                @if(!($notification['is_unread'] ?? false))
                                    <span class="medicine-alert-chip" style="margin-left:8px;">Read</span>
                                @endif
                            </p>
                        </div>

                        <div class="medicine-alert-item-meta">
                            @foreach($notification['chips'] as $chip)
                                <span class="medicine-alert-chip">{{ $chip }}</span>
                            @endforeach
                        </div>
                    </div>
                    </a>
                </article>
            @empty
                <div class="medicine-alert-empty">
                    <p class="medicine-alert-empty-title">No notification history yet</p>
                    <p class="medicine-alert-empty-copy">As notifications come in, this history view will keep them in one place.</p>
                </div>
            @endforelse
        </div>
    </div>
</section>

<form method="POST" action="{{ $adminMarkAllReadUrl }}" id="adminNotificationOpenForm" style="display:none;">
    @csrf
    @foreach($adminNotifications as $notification)
        <input type="hidden" name="notification_ids[]" value="{{ $notification['id'] }}">
    @endforeach
    <input type="hidden" name="redirect_to" id="adminNotificationRedirectTo" value="">
</form>

<div id="medicineHoverHint" class="medicine-hover-hint" aria-hidden="true">Press to enter</div>

<div id="adminLiveAlertModal" class="admin-live-alert" aria-live="assertive" aria-atomic="true">
    <div class="admin-live-alert-card">
        <div id="adminLiveAlertIcon" class="admin-live-alert-icon" aria-hidden="true">
            <x-outline-icon name="calendar-days" />
        </div>
        <div class="admin-live-alert-content">
            <p id="adminLiveAlertTitle" class="admin-live-alert-title">New notification</p>
            <p id="adminLiveAlertCopy" class="admin-live-alert-copy">A new clinic activity requires your attention.</p>
        </div>
        <a id="adminLiveAlertOpen" class="admin-live-alert-open" href="{{ $appointmentsUrl }}">Open</a>
        <button type="button" id="adminLiveAlertMore" class="admin-live-alert-more" aria-label="See more notifications">
            See more
        </button>
        <button type="button" class="admin-live-alert-close" id="adminLiveAlertClose" aria-label="Close alert">
            <x-outline-icon name="x-mark" />
        </button>
    </div>
    <div class="admin-live-alert-progress" aria-hidden="true">
        <div id="adminLiveAlertProgressBar" class="admin-live-alert-progress-bar"></div>
    </div>
</div>
<script id="adminLiveAlertData" type="application/json">@json($adminLiveAlertItems)</script>
<script id="adminLiveAlertFeedUrl" type="application/json">@json($adminNotificationsFeedUrl)</script>

<section id="assistantPanel" class="assistant-panel" aria-live="polite" role="dialog" aria-label="Clinic AI Assistant">
    <div class="assistant-head">
        <div class="assistant-brand">
            <div class="assistant-orb" aria-hidden="true">
                <x-outline-icon name="sparkles" />
            </div>
            <div>
                <p class="assistant-head-title">Clinic AI Assistant</p>
                <p class="assistant-head-sub">Operational shortcuts and clinical triage support</p>
            </div>
        </div>
        <div class="assistant-head-actions">
            <span class="assistant-status-pill"><span class="assistant-status-dot" aria-hidden="true"></span>Ready</span>
            <button type="button" class="assistant-close" aria-label="Close assistant" onclick="closeAssistantPanel()">
                <x-outline-icon name="x-mark" />
            </button>
        </div>
    </div>

    <div id="assistantMessages" class="assistant-messages">
        <div class="assistant-bubble assistant">Try a shortcut below, ask a symptom question, or type a clinic workflow request.</div>
    </div>

    <div class="assistant-quick-prompts" aria-label="Assistant shortcuts">
        <button type="button" class="assistant-prompt-chip" data-assistant-prompt="open appointments">Open appointments</button>
        <button type="button" class="assistant-prompt-chip" data-assistant-prompt="generate MAR">Generate MAR</button>
        <button type="button" class="assistant-prompt-chip" data-assistant-prompt="summarize clinic alerts">Clinic alerts</button>
    </div>

    <div class="assistant-controls">
        <div class="assistant-input-shell">
            <input type="text" id="assistantInput" class="assistant-input" placeholder="Type a command or triage question..." maxlength="500">
        </div>
        <button type="button" id="assistantSendBtn" class="assistant-send">
            <span>Send</span>
            <x-outline-icon name="arrow-long-right" />
        </button>
    </div>

    <p class="assistant-note"><strong>Clinical safety:</strong> responses support initial triage only, not a confirmed diagnosis. For emergencies, call local emergency services immediately.</p>
</section>

@include('partials.post_login_terms_gate')
@hasSection('disable_voice_inputs')
@else
@include('partials.student_voice_input_support')
@endif

@stack('scripts')

<script>
    const assistantEndpoint = @json($assistantEndpoint);
    const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '';

    function toggleSidebar() {
        document.body.classList.toggle('sidebar-open');
    }

    function applyAdminTheme(theme) {
        const normalizedTheme = theme === 'light' ? 'light' : 'dark';
        const toggle = document.getElementById('adminThemeToggle');
        const tooltip = document.getElementById('themeModeTooltip');
        const moonIcon = '<svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M21.752 15.002A9.718 9.718 0 0 1 18 15.75c-5.385 0-9.75-4.365-9.75-9.75 0-1.33.266-2.597.748-3.752A9.753 9.753 0 0 0 3 11.25C3 16.635 7.365 21 12.75 21a9.753 9.753 0 0 0 9.002-5.998Z"></path></svg>';
        const sunIcon = '<svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M12 3v2.25m6.364.386-1.591 1.591M21 12h-2.25m-.386 6.364-1.591-1.591M12 18.75V21m-4.773-4.227-1.591 1.591M5.25 12H3m4.227-4.773L5.636 5.636M15.75 12a3.75 3.75 0 1 1-7.5 0 3.75 3.75 0 0 1 7.5 0Z"></path></svg>';

        document.documentElement.setAttribute('data-theme', normalizedTheme);

        if (!toggle) {
            return;
        }

        const isDark = normalizedTheme === 'dark';
        toggle.innerHTML = isDark ? moonIcon : sunIcon;
        toggle.setAttribute('aria-label', isDark ? 'Dark mode' : 'Light mode');
        toggle.setAttribute('aria-pressed', isDark ? 'true' : 'false');

        if (tooltip) {
            tooltip.textContent = isDark ? 'Dark Mode' : 'Light Mode';
        }
    }

    function initThemeToggle() {
        const toggle = document.getElementById('adminThemeToggle');
        const storageKey = 'admin_theme';
        const currentTheme = document.documentElement.getAttribute('data-theme') || 'dark';

        applyAdminTheme(currentTheme);

        if (!toggle) {
            return;
        }

        toggle.addEventListener('click', function () {
            const activeTheme = document.documentElement.getAttribute('data-theme');
            const nextTheme = activeTheme === 'dark' ? 'light' : 'dark';
            applyAdminTheme(nextTheme);
            try {
                localStorage.setItem(storageKey, nextTheme);
            } catch (error) {
                console.warn('Theme preference was not saved.', error);
            }
        });
    }

    function initSidebarScrollIndicator() {
        const sidebar = document.getElementById('adminSidebar');
        const indicator = document.getElementById('sidebarScrollIndicator');
        const activeLink = sidebar ? sidebar.querySelector('.sidebar-nav a.active') : null;

        if (!sidebar || !indicator) {
            return;
        }

        const revealActiveLink = () => {
            if (!activeLink) {
                return;
            }

            const sidebarRect = sidebar.getBoundingClientRect();
            const linkRect = activeLink.getBoundingClientRect();
            const topOverflow = linkRect.top < sidebarRect.top + 84;
            const bottomOverflow = linkRect.bottom > sidebarRect.bottom - 76;

            if (topOverflow || bottomOverflow) {
                activeLink.scrollIntoView({
                    block: 'center',
                    behavior: 'auto'
                });
            }
        };

        const updateIndicator = () => {
            const canScroll = sidebar.scrollHeight - sidebar.clientHeight > 6;
            const hasMoreBelow = sidebar.scrollTop + sidebar.clientHeight < sidebar.scrollHeight - 6;
            const isExpanded = window.innerWidth > 860 || document.body.classList.contains('sidebar-open');
            indicator.classList.toggle('is-visible', canScroll && hasMoreBelow && isExpanded);
        };

        indicator.addEventListener('click', () => {
            sidebar.scrollBy({
                top: Math.max(180, Math.floor(sidebar.clientHeight * 0.45)),
                behavior: 'smooth'
            });
        });

        sidebar.addEventListener('scroll', updateIndicator, { passive: true });
        sidebar.addEventListener('mouseenter', updateIndicator);
        sidebar.addEventListener('mouseleave', updateIndicator);
        window.addEventListener('resize', () => {
            revealActiveLink();
            updateIndicator();
        }, { passive: true });
        revealActiveLink();
        updateIndicator();
        window.setTimeout(revealActiveLink, 80);
        window.setTimeout(updateIndicator, 150);
    }

    function initMedicineAlerts() {
        const toggles = Array.from(document.querySelectorAll('[data-medicine-alert-toggle]'));
        const panel = document.getElementById('medicineAlertPanel');
        const closeButton = document.getElementById('medicineAlertCloseBtn');
        const actionsToggle = document.getElementById('medicineAlertActionsToggle');
        const actionsMenu = document.getElementById('medicineAlertActionsMenu');
        const historyButton = document.getElementById('medicineAlertHistoryBtn');
        const backButton = document.getElementById('medicineAlertBackBtn');
        const unreadSection = document.getElementById('medicineAlertUnreadSection');
        const historySection = document.getElementById('medicineAlertHistorySection');
        const moreButton = document.getElementById('medicineAlertMoreBtn');
        const openForm = document.getElementById('adminNotificationOpenForm');
        const redirectInput = document.getElementById('adminNotificationRedirectTo');
        const hiddenItems = Array.from(document.querySelectorAll('[data-medicine-alert-extra="true"]'));
        const hoverHint = document.getElementById('medicineHoverHint');
        const hintTargets = Array.from(document.querySelectorAll('.medicine-alert-item-link[data-hover-hint]'));
        const notificationLinks = Array.from(document.querySelectorAll('.medicine-alert-item-link[data-admin-notification-link="true"]'));
        let closeTimer = null;

        if (!toggles.length || !panel) {
            return;
        }

        const closeActionsMenu = function () {
            if (!actionsToggle || !actionsMenu) {
                return;
            }

            actionsMenu.classList.remove('is-open');
            actionsToggle.setAttribute('aria-expanded', 'false');
        };

        const openActionsMenu = function () {
            if (!actionsToggle || !actionsMenu) {
                return;
            }

            actionsMenu.classList.add('is-open');
            actionsToggle.setAttribute('aria-expanded', 'true');
        };

        const openPanel = function () {
            if (closeTimer) {
                window.clearTimeout(closeTimer);
                closeTimer = null;
            }

            panel.classList.remove('is-closing');
            window.requestAnimationFrame(function () {
                panel.classList.add('is-open');
                toggleHeaderQuickActions(true);
            });
        };

        const showUnreadSection = function () {
            unreadSection?.classList.remove('is-hidden');
            historySection?.classList.add('is-hidden');
            backButton?.classList.remove('is-visible');
        };

        const showHistorySection = function () {
            unreadSection?.classList.add('is-hidden');
            historySection?.classList.remove('is-hidden');
            backButton?.classList.add('is-visible');
            closeActionsMenu();
        };

        const closePanel = function () {
            if (!panel.classList.contains('is-open')) {
                return;
            }

            showUnreadSection();
            closeActionsMenu();
            panel.classList.remove('is-open');
            panel.classList.add('is-closing');
            releaseHeaderQuickActionsWhenIdle();

            if (closeTimer) {
                window.clearTimeout(closeTimer);
            }

            closeTimer = window.setTimeout(function () {
                panel.classList.remove('is-closing');
                closeTimer = null;
            }, 240);
        };

        document.addEventListener('admin:close-quick-action-popups', closePanel);

        toggles.forEach(function (toggle) {
            toggle.addEventListener('click', function (event) {
                event.stopPropagation();
                if (panel.classList.contains('is-open')) {
                    closePanel();
                } else {
                    openPanel();
                }
            });
        });

        if (closeButton) {
            closeButton.addEventListener('click', function (event) {
                event.stopPropagation();
                closePanel();
            });
        }

        if (actionsToggle && actionsMenu) {
            actionsToggle.addEventListener('click', function (event) {
                event.preventDefault();
                event.stopPropagation();

                if (actionsMenu.classList.contains('is-open')) {
                    closeActionsMenu();
                } else {
                    openActionsMenu();
                }
            });

            actionsMenu.addEventListener('click', function (event) {
                event.stopPropagation();
            });
        }

        if (historyButton) {
            historyButton.addEventListener('click', function (event) {
                event.preventDefault();
                event.stopPropagation();
                showHistorySection();
            });
        }

        if (backButton) {
            backButton.addEventListener('click', function (event) {
                event.preventDefault();
                event.stopPropagation();
                showUnreadSection();
            });
        }

        document.addEventListener('click', function (event) {
            if (actionsMenu && actionsMenu.classList.contains('is-open')) {
                const clickedActionsToggle = actionsToggle ? actionsToggle.contains(event.target) : false;
                if (!clickedActionsToggle && !actionsMenu.contains(event.target)) {
                    closeActionsMenu();
                }
            }

            if (!panel.classList.contains('is-open')) {
                return;
            }

            const clickedToggle = toggles.some(function (toggle) {
                return toggle.contains(event.target);
            });

            if (!panel.contains(event.target) && !clickedToggle) {
                closePanel();
            }
        });

        if (moreButton && hiddenItems.length > 0) {
            moreButton.addEventListener('click', function (event) {
                event.preventDefault();
                event.stopPropagation();

                const nextItems = hiddenItems.filter(function (item) {
                    return item.classList.contains('is-hidden');
                }).slice(0, 2);

                nextItems.forEach(function (item) {
                    item.classList.remove('is-hidden');
                });

                const remaining = hiddenItems.filter(function (item) {
                    return item.classList.contains('is-hidden');
                }).length;

                if (remaining > 0) {
                    moreButton.textContent = 'Show more (' + remaining + ')';
                } else {
                    moreButton.closest('.medicine-alert-more-wrapper')?.remove();
                }
            });
        }

        if (hoverHint && hintTargets.length > 0) {
            const moveHint = function (event) {
                hoverHint.style.left = event.clientX + 'px';
                hoverHint.style.top = event.clientY + 'px';
            };

            hintTargets.forEach(function (target) {
                target.addEventListener('mouseenter', function (event) {
                    hoverHint.textContent = target.dataset.hoverHint || 'Press to enter';
                    hoverHint.classList.add('is-visible');
                    moveHint(event);
                });

                target.addEventListener('mousemove', moveHint);

                target.addEventListener('mouseleave', function () {
                    hoverHint.classList.remove('is-visible');
                });
            });
        }

        if (openForm && redirectInput && notificationLinks.length > 0) {
            notificationLinks.forEach(function (link) {
                link.addEventListener('click', function (event) {
                    const targetUrl = link.dataset.adminNotificationTarget || link.getAttribute('href') || '';
                    if (targetUrl.trim() === '') {
                        return;
                    }

                    event.preventDefault();
                    redirectInput.value = targetUrl;
                    openForm.submit();
                });
            });
        }

    }

    function initAdminLiveAlerts() {
        const liveAlertsEnabled = @json($adminLiveNotificationsEnabled);
        if (!liveAlertsEnabled) {
            return;
        }

        const modal = document.getElementById('adminLiveAlertModal');
        const dataNode = document.getElementById('adminLiveAlertData');
        const feedUrlNode = document.getElementById('adminLiveAlertFeedUrl');
        const titleNode = document.getElementById('adminLiveAlertTitle');
        const copyNode = document.getElementById('adminLiveAlertCopy');
        const iconNode = document.getElementById('adminLiveAlertIcon');
        const openNode = document.getElementById('adminLiveAlertOpen');
        const moreNode = document.getElementById('adminLiveAlertMore');
        const closeNode = document.getElementById('adminLiveAlertClose');
        const progressNode = document.getElementById('adminLiveAlertProgressBar');
        const badgeNodes = Array.from(document.querySelectorAll('.quick-action-badge, .medicine-alert-badge'));
        const alertPanel = document.getElementById('medicineAlertPanel');
        const notificationToggle = document.querySelector('[data-medicine-alert-toggle]');

        if (!modal || !dataNode || !titleNode || !copyNode || !iconNode || !openNode || !moreNode || !closeNode || !progressNode || !feedUrlNode) {
            return;
        }

        let alerts = [];
        try {
            alerts = JSON.parse(dataNode.textContent || '[]');
        } catch (error) {
            alerts = [];
        }
        let unreadCount = {{ (int) $adminNotificationCount }};

        const feedUrl = (() => {
            try {
                return JSON.parse(feedUrlNode.textContent || '""') || '';
            } catch (error) {
                return '';
            }
        })();

        const storageKey = 'admin_live_alert_seen_' + String({{ (int) (optional(auth()->user())->id ?? 0) }});
        let seenMap = {};
        try {
            const rawSeen = localStorage.getItem(storageKey);
            seenMap = rawSeen ? JSON.parse(rawSeen) : {};
        } catch (error) {
            seenMap = {};
        }

        const queue = [];
        const queuedIds = new Set();

        const appointmentIconSvg = '<svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 0 0 2-2V7a2 2 0 0 0-2-2H5a2 2 0 0 0-2 2v12a2 2 0 0 0 2 2Z"/></svg>';
        const healthIconSvg = '<svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M12 6.75h.008v.008H12V6.75Zm0 3.75h.008v.008H12V10.5Zm0 3.75h.008v.008H12v-.008Zm-6 6h12A2.25 2.25 0 0 0 20.25 18V6A2.25 2.25 0 0 0 18 3.75H6A2.25 2.25 0 0 0 3.75 6v12A2.25 2.25 0 0 0 6 20.25Z"/></svg>';

        let currentIndex = -1;
        let hideTimer = null;
        let isShowing = false;
        let pollTimer = null;
        let currentAlert = null;

        const persistSeen = function () {
            try {
                localStorage.setItem(storageKey, JSON.stringify(seenMap));
            } catch (error) {
                // no-op
            }
        };

        const markSeen = function (notificationId) {
            if (!notificationId) {
                return;
            }
            seenMap[notificationId] = new Date().toISOString();
            persistSeen();
        };

        const syncSeeMore = function () {
            moreNode.classList.toggle('is-visible', unreadCount >= 4);
        };

        const updateBadgeCount = function (count) {
            badgeNodes.forEach(function (badge) {
                if (!badge) {
                    return;
                }

                if (count > 0) {
                    badge.textContent = String(count);
                    badge.style.display = 'inline-flex';
                } else {
                    badge.textContent = '';
                    badge.style.display = 'none';
                }
            });
        };

        const closeCurrent = function () {
            modal.classList.remove('is-open');
            if (hideTimer) {
                window.clearTimeout(hideTimer);
                hideTimer = null;
            }
            isShowing = false;
            currentAlert = null;
            window.setTimeout(function () {
                if (queue.length > 0) {
                    showNext();
                }
            }, 180);
        };

        const resetProgress = function () {
            progressNode.style.transition = 'none';
            progressNode.style.transform = 'scaleX(1)';
            window.requestAnimationFrame(function () {
                window.requestAnimationFrame(function () {
                    progressNode.style.transition = 'transform 10s linear';
                    progressNode.style.transform = 'scaleX(0)';
                });
            });
        };

        const showNext = function () {
            if (isShowing || queue.length === 0) {
                return;
            }

            const item = queue.shift();
            if (!item) {
                return;
            }

            currentIndex += 1;
            isShowing = true;
            currentAlert = item;
            titleNode.textContent = item.title || 'New notification';
            copyNode.textContent = item.message || 'A new clinic activity requires your attention.';
            openNode.setAttribute('href', item.link || '{{ $appointmentsUrl }}');
            iconNode.classList.toggle('is-health', item.kind === 'health');
            iconNode.innerHTML = item.kind === 'health' ? healthIconSvg : appointmentIconSvg;

            modal.classList.add('is-open');
            resetProgress();

            hideTimer = window.setTimeout(function () {
                markSeen(item.id);
                closeCurrent();
            }, 10000);
        };

        closeNode.addEventListener('click', function () {
            if (isShowing && currentAlert && currentAlert.id) {
                markSeen(currentAlert.id);
            }
            closeCurrent();
        });

        openNode.addEventListener('click', function () {
            if (isShowing && currentAlert && currentAlert.id) {
                markSeen(currentAlert.id);
            }
        });

        moreNode.addEventListener('click', function () {
            if (alertPanel && !alertPanel.classList.contains('is-open') && notificationToggle) {
                notificationToggle.click();
            }
            if (isShowing && currentAlert && currentAlert.id) {
                markSeen(currentAlert.id);
            }
            closeCurrent();
        });

        const enqueueAlerts = function (items) {
            if (!Array.isArray(items)) {
                return;
            }

            items.forEach(function (alertItem) {
                if (!alertItem || !alertItem.id || seenMap[alertItem.id] || queuedIds.has(alertItem.id)) {
                    return;
                }

                queuedIds.add(alertItem.id);
                queue.push(alertItem);
            });

            showNext();
        };

        const pullFeed = function () {
            if (!feedUrl) {
                return;
            }

            fetch(feedUrl, {
                method: 'GET',
                headers: {
                    'X-Requested-With': 'XMLHttpRequest',
                    'Accept': 'application/json',
                },
                credentials: 'same-origin',
                cache: 'no-store',
            })
                .then(function (response) {
                    if (!response.ok) {
                        throw new Error('Notifications feed request failed');
                    }
                    return response.json();
                })
                .then(function (payload) {
                    const items = Array.isArray(payload.notifications) ? payload.notifications : [];
                    unreadCount = Number(payload.count || items.length || 0);
                    updateBadgeCount(unreadCount);
                    syncSeeMore();

                    const filtered = items
                        .filter(function (item) {
                            return item && item.id && (item.kind === 'appointment' || item.kind === 'health');
                        })
                        .slice(0, 3);

                    alerts = filtered;
                    enqueueAlerts(filtered);
                })
                .catch(function () {
                    // keep silent to avoid noisy UX
                });
        };

        enqueueAlerts(Array.isArray(alerts) ? alerts : []);
        syncSeeMore();
        pullFeed();
        pollTimer = window.setInterval(pullFeed, 10000);
        window.addEventListener('beforeunload', function () {
            if (pollTimer) {
                window.clearInterval(pollTimer);
            }
        });
    }

    function initAccessibilityLaunch() {
        const launchButton = document.getElementById('adminAccessibilityLaunch');
        forceAccessibilityButtonTheme();
        if (!launchButton) {
            return;
        }

        function findSiennaTrigger() {
            const selectorMatches = [
                '.asw-menu-btn',
                '#sienna-accessibility-button',
                '.sienna-accessibility-button',
                '.sienna-accessibility-trigger',
                '[data-sienna-accessibility-trigger]',
                'button[aria-label*="accessibility" i]:not(#adminAccessibilityLaunch)',
                'button[title*="accessibility" i]:not(#adminAccessibilityLaunch)',
                '[role="button"][aria-label*="accessibility" i]'
            ];

            for (const selector of selectorMatches) {
                const candidate = document.querySelector(selector);
                if (candidate) {
                    return candidate;
                }
            }

            const fallbackCandidates = Array.from(document.querySelectorAll('button, [role="button"], div'))
                .filter((element) => {
                    if (element.id === 'adminAccessibilityLaunch') {
                        return false;
                    }

                    const label = [
                        element.getAttribute('aria-label'),
                        element.getAttribute('title'),
                        element.textContent
                    ].join(' ').toLowerCase();

                    const style = window.getComputedStyle(element);
                    const looksFloating = style.position === 'fixed' || style.position === 'sticky';

                    return looksFloating && label.includes('access');
                });

            return fallbackCandidates[0] || null;
        }

        function hideSiennaTrigger() {
            const trigger = findSiennaTrigger();
            if (!trigger) {
                return;
            }

            const alreadyHidden =
                trigger.classList.contains('admin-sienna-trigger-hidden') &&
                trigger.style.getPropertyValue('left') === '-9999px' &&
                trigger.style.getPropertyValue('visibility') === 'hidden';

            if (alreadyHidden) {
                trigger.setAttribute('aria-hidden', 'true');
                return;
            }

            trigger.classList.add('admin-sienna-trigger-hidden');
            trigger.style.setProperty('position', 'fixed', 'important');
            trigger.style.setProperty('left', '-9999px', 'important');
            trigger.style.setProperty('right', 'auto', 'important');
            trigger.style.setProperty('bottom', 'auto', 'important');
            trigger.style.setProperty('top', 'auto', 'important');
            trigger.style.setProperty('opacity', '0', 'important');
            trigger.style.setProperty('visibility', 'hidden', 'important');
            trigger.style.setProperty('pointer-events', 'none', 'important');
            trigger.style.setProperty('transform', 'none', 'important');
            trigger.style.setProperty('z-index', '-1', 'important');
            trigger.setAttribute('aria-hidden', 'true');
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

        launchButton.addEventListener('click', function () {
            const trigger = findSiennaTrigger();
            if (!trigger) {
                console.warn('Accessibility widget trigger not found yet.');
                return;
            }

            trigger.click();
        });

        hideSiennaTrigger();
        themeSiennaMenu();
        injectSiennaShadowStyles();
        forceAccessibilityButtonTheme();

        const observer = new MutationObserver(function () {
            hideSiennaTrigger();
            themeSiennaMenu();
            injectSiennaShadowStyles();
            forceAccessibilityButtonTheme();
        });

        observer.observe(document.body, {
            childList: true,
            subtree: true
        });

        window.addEventListener('focus', hideSiennaTrigger);
        window.addEventListener('pageshow', hideSiennaTrigger);
        window.setTimeout(hideSiennaTrigger, 150);
        window.setTimeout(hideSiennaTrigger, 500);
        window.setTimeout(hideSiennaTrigger, 1200);
    }

    function toggleProfileMenu() {
        const menu = document.getElementById('profileDropdown');
        const wrap = document.getElementById('profileWrap');
        const button = wrap ? wrap.querySelector('.admin-user') : null;
        if (!menu || !wrap) {
            return;
        }

        const shouldOpen = !wrap.classList.contains('is-expanded');
        wrap.classList.toggle('is-click-collapsed', !shouldOpen);
        wrap.classList.toggle('is-expanded', shouldOpen);
        menu.style.display = shouldOpen ? 'block' : 'none';
        button?.setAttribute('aria-expanded', shouldOpen ? 'true' : 'false');
    }

    document.getElementById('profileWrap')?.addEventListener('mouseleave', function () {
        this.classList.remove('is-click-collapsed');
    });

    function toggleHeaderQuickActions(forceOpen) {
        const wrap = document.getElementById('headerQuickActions');
        const toggle = wrap ? wrap.querySelector('.quick-actions-toggle') : null;
        if (!wrap || !toggle) {
            return;
        }

        const isDirectToggleClick = typeof forceOpen !== 'boolean';
        if (isDirectToggleClick && wrap.classList.contains('is-open') && hasOpenQuickActionPopup()) {
            document.dispatchEvent(new CustomEvent('admin:close-quick-action-popups'));
            wrap.classList.remove('is-open');
            toggle.setAttribute('aria-expanded', 'false');

            const scan = document.getElementById('headerQuickScan');
            const scanToggle = scan ? scan.querySelector('.quick-action-btn') : null;
            scan?.classList.remove('is-open');
            scanToggle?.setAttribute('aria-expanded', 'false');
            return;
        }

        let shouldOpen = typeof forceOpen === 'boolean' ? forceOpen : !wrap.classList.contains('is-open');
        if (!shouldOpen && hasOpenQuickActionPopup()) {
            shouldOpen = true;
        }
        wrap.classList.toggle('is-open', shouldOpen);
        toggle.setAttribute('aria-expanded', shouldOpen ? 'true' : 'false');
        if (!shouldOpen) {
            const scan = document.getElementById('headerQuickScan');
            const scanToggle = scan ? scan.querySelector('.quick-action-btn') : null;
            scan?.classList.remove('is-open');
            scanToggle?.setAttribute('aria-expanded', 'false');
        }
    }

    function hasOpenQuickActionPopup() {
        return Boolean(
            document.getElementById('medicineAlertPanel')?.classList.contains('is-open')
            || document.getElementById('treatmentRecordModal')?.classList.contains('is-open')
        );
    }

    function releaseHeaderQuickActionsWhenIdle() {
        if (!hasOpenQuickActionPopup()) {
            toggleHeaderQuickActions(false);
        }
    }

    function initTreatmentRecordModal() {
        const openButton = document.getElementById('openTreatmentRecordModal');
        const modal = document.getElementById('treatmentRecordModal');
        const dialog = modal ? modal.querySelector('.treatment-record-modal-dialog') : null;
        const closeButton = document.getElementById('closeTreatmentRecordModal');
        const expandButton = document.getElementById('expandTreatmentRecordModal');
        const frame = document.getElementById('treatmentRecordModalFrame');

        if (!openButton || !modal || !dialog || !closeButton || !expandButton || !frame) {
            return;
        }

        const setExpanded = function (expanded) {
            modal.classList.toggle('is-expanded', expanded);
            expandButton.textContent = expanded ? '>' : '<';
            expandButton.setAttribute('aria-expanded', expanded ? 'true' : 'false');
            expandButton.setAttribute(
                'aria-label',
                expanded ? 'Restore Daily Treatment Record modal' : 'Expand Daily Treatment Record modal'
            );
            expandButton.setAttribute(
                'title',
                expanded ? 'Restore Daily Treatment Record' : 'Expand Daily Treatment Record'
            );
        };

        const closeModal = function () {
            if (!modal.classList.contains('is-open')) {
                return;
            }

            modal.classList.remove('is-open');
            modal.setAttribute('aria-hidden', 'true');
            setExpanded(false);
            releaseHeaderQuickActionsWhenIdle();
            openButton.focus();
        };

        const openModal = function () {
            if (!frame.getAttribute('src')) {
                frame.setAttribute('src', frame.dataset.src);
            }

            modal.classList.add('is-open');
            modal.setAttribute('aria-hidden', 'false');
            toggleHeaderQuickActions(true);
        };

        openButton.addEventListener('click', openModal);
        closeButton.addEventListener('click', closeModal);
        document.addEventListener('admin:close-quick-action-popups', closeModal);
        expandButton.addEventListener('click', function () {
            setExpanded(!modal.classList.contains('is-expanded'));
        });
        document.addEventListener('keydown', function (event) {
            if (event.key === 'Escape' && modal.classList.contains('is-open')) {
                closeModal();
            }
        });
    }

    function toggleQuickScanMenu(event) {
        event?.preventDefault();
        event?.stopPropagation();

        const scan = document.getElementById('headerQuickScan');
        const toggle = scan ? scan.querySelector('.quick-action-btn') : null;
        if (!scan || !toggle) {
            return;
        }

        const shouldOpen = !scan.classList.contains('is-open');
        scan.classList.toggle('is-open', shouldOpen);
        toggle.setAttribute('aria-expanded', shouldOpen ? 'true' : 'false');
    }

    function toggleAssistantPanel() {
        const panel = document.getElementById('assistantPanel');
        panel.classList.toggle('open');
    }

    function closeAssistantPanel() {
        const panel = document.getElementById('assistantPanel');
        panel.classList.remove('open');
    }

    function appendAssistantMessage(role, text) {
        const messages = document.getElementById('assistantMessages');
        if (!messages) return;
        const bubble = document.createElement('div');
        bubble.className = 'assistant-bubble ' + (role === 'user' ? 'user' : 'assistant');
        bubble.textContent = text;
        messages.appendChild(bubble);
        messages.scrollTop = messages.scrollHeight;
        return bubble;
    }

    function setAssistantStatus(label) {
        const status = document.querySelector('#assistantPanel .assistant-status-pill');
        if (!status) return;
        const dot = status.querySelector('.assistant-status-dot');
        status.textContent = '';
        if (dot) {
            status.appendChild(dot);
        }
        status.appendChild(document.createTextNode(label || 'Ready'));
    }

    async function sendAssistantQuery(rawText) {
        const text = (rawText || '').trim();
        if (!text) return;

        appendAssistantMessage('user', text);
        setAssistantStatus('Thinking');
        const pendingBubble = appendAssistantMessage('assistant', 'Reviewing your request...');

        try {
            const response = await fetch(assistantEndpoint, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': csrfToken,
                    'X-Requested-With': 'XMLHttpRequest'
                },
                body: JSON.stringify({ text })
            });

            if (!response.ok) {
                throw new Error('Assistant request failed');
            }

            const payload = await response.json();
            if (payload.message) {
                if (pendingBubble) {
                    pendingBubble.textContent = payload.message;
                } else {
                    appendAssistantMessage('assistant', payload.message);
                }
            }

            if (payload.type === 'action' && payload.action?.kind === 'redirect' && payload.action?.url) {
                if (pendingBubble && !payload.message) {
                    pendingBubble.textContent = 'Opening the requested workspace...';
                }
                setAssistantStatus('Opening');
                setTimeout(function () {
                    window.location.href = payload.action.url;
                }, 650);
            } else {
                if (pendingBubble && !payload.message) {
                    pendingBubble.textContent = 'Done. What would you like to do next?';
                }
                setAssistantStatus('Ready');
            }
        } catch (error) {
            if (pendingBubble) {
                pendingBubble.textContent = 'Unable to process right now. Please try again.';
            } else {
                appendAssistantMessage('assistant', 'Unable to process right now. Please try again.');
            }
            setAssistantStatus('Ready');
        }
    }

    function initAssistantUi() {
        const panel = document.getElementById('assistantPanel');
        const sendBtn = document.getElementById('assistantSendBtn');
        const input = document.getElementById('assistantInput');
        const promptButtons = document.querySelectorAll('[data-assistant-prompt]');

        if (!panel || !sendBtn || !input) return;

        sendBtn.addEventListener('click', function () {
            const value = input.value;
            input.value = '';
            sendAssistantQuery(value);
        });

        input.addEventListener('keydown', function (event) {
            if (event.key === 'Enter') {
                event.preventDefault();
                const value = input.value;
                input.value = '';
                sendAssistantQuery(value);
            }
        });

        promptButtons.forEach(function (button) {
            button.addEventListener('click', function () {
                const prompt = button.dataset.assistantPrompt || button.textContent || '';
                input.value = prompt;
                input.focus({ preventScroll: true });
                sendAssistantQuery(prompt);
                input.value = '';
            });
        });

    }

    function forceAccessibilityButtonTheme() {
        document.querySelectorAll('.asw-menu-btn').forEach(function (button) {
            button.style.setProperty('background', '#800000', 'important');
            button.style.setProperty('background-image', 'none', 'important');
            button.style.setProperty('border', '2px solid #5f0012', 'important');
            button.style.setProperty('outline', 'none', 'important');
            button.style.setProperty('box-shadow', '0 10px 24px rgba(128, 0, 0, 0.28)', 'important');
            button.querySelectorAll('svg').forEach(function (icon) {
                icon.style.setProperty('fill', '#ffffff', 'important');
                icon.style.setProperty('stroke', 'none', 'important');
                icon.style.setProperty('background', 'transparent', 'important');
            });
            button.querySelectorAll('svg path:not([fill="none"])').forEach(function (path) {
                path.style.setProperty('fill', '#ffffff', 'important');
                path.style.setProperty('stroke', 'none', 'important');
            });
            button.querySelectorAll('svg path[fill="none"]').forEach(function (path) {
                path.style.setProperty('stroke', 'none', 'important');
            });
        });
    }

    document.addEventListener('click', function (event) {
        const menu = document.getElementById('profileDropdown');
        const trigger = document.querySelector('.admin-user');
        const profileWrap = document.getElementById('profileWrap');
        const sidebar = document.getElementById('adminSidebar');
        const sidebarToggle = document.querySelector('.sidebar-toggle');
        const panel = document.getElementById('assistantPanel');
        const assistantLaunch = document.getElementById('assistantLaunchBtn');
        const quickActions = document.getElementById('headerQuickActions');
        const quickScan = document.getElementById('headerQuickScan');

        if (menu.style.display === 'block' && trigger && !menu.contains(event.target) && !trigger.contains(event.target)) {
            menu.style.display = 'none';
            profileWrap?.classList.remove('is-expanded');
            trigger?.setAttribute('aria-expanded', 'false');
        }

        if (
            quickActions
            && quickActions.classList.contains('is-open')
            && !quickActions.contains(event.target)
            && !hasOpenQuickActionPopup()
        ) {
            toggleHeaderQuickActions(false);
        }

        if (quickScan && quickScan.classList.contains('is-open') && !quickScan.contains(event.target)) {
            quickScan.classList.remove('is-open');
            quickScan.querySelector('.quick-action-btn')?.setAttribute('aria-expanded', 'false');
        }

        if (
            document.body.classList.contains('sidebar-open') &&
            sidebar &&
            sidebarToggle &&
            !sidebar.contains(event.target) &&
            !sidebarToggle.contains(event.target)
        ) {
            document.body.classList.remove('sidebar-open');
        }

        if (
            panel &&
            panel.classList.contains('open') &&
            assistantLaunch &&
            !panel.contains(event.target) &&
            !assistantLaunch.contains(event.target)
        ) {
            panel.classList.remove('open');
        }
    });

    window.addEventListener('resize', function () {
        if (window.innerWidth > 860) {
            document.body.classList.remove('sidebar-open');
        }
        if (hasOpenQuickActionPopup()) {
            toggleHeaderQuickActions(true);
        } else {
            toggleHeaderQuickActions(false);
        }
    });

    document.addEventListener('DOMContentLoaded', function () {
        initAssistantUi();
        initThemeToggle();
        initSidebarScrollIndicator();
        initMedicineAlerts();
        initTreatmentRecordModal();
        initAdminLiveAlerts();
        initAccessibilityLaunch();
    });
</script>

@include('partials.system_footer')
</body>
</html>
