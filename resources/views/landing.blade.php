<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>PUP Taguig Medical Clinic</title>
    <style>
        :root {
            --maroon: #70131B;
            --maroon-strong: #8f2230;
            --maroon-deep: #33080d;
            --gold: #facc15;
            --gold-soft: #fff1a8;
            --white: #ffffff;
            --ink: #1f2937;
            --muted: rgba(255, 255, 255, 0.78);
            --line: rgba(255, 255, 255, 0.18);
        }

        * {
            box-sizing: border-box;
        }

        html,
        body {
            margin: 0;
            min-height: 100%;
        }

        body {
            font-family: "Segoe UI", Tahoma, Geneva, Verdana, sans-serif;
            color: var(--white);
            background:
                linear-gradient(135deg, rgba(51, 8, 13, 0.92), rgba(112, 19, 27, 0.78)),
                url('{{ asset('images/PUPBG.jpg') }}') center center / cover no-repeat fixed;
            overflow-x: hidden;
        }

        body::before {
            content: "";
            position: fixed;
            inset: 0;
            background:
                radial-gradient(circle at 18% 18%, rgba(250, 204, 21, 0.20), transparent 28%),
                radial-gradient(circle at 88% 12%, rgba(255, 255, 255, 0.12), transparent 24%),
                linear-gradient(180deg, rgba(15, 23, 42, 0.08), rgba(15, 23, 42, 0.32));
            pointer-events: none;
        }

        a {
            color: inherit;
        }

        .landing-shell {
            position: relative;
            z-index: 1;
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: clamp(20px, 4vw, 48px) 18px;
        }

        .landing-topbar {
            position: fixed;
            top: 18px;
            left: 18px;
            right: 18px;
            z-index: 20;
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 18px;
            width: min(1040px, calc(100% - 36px));
            margin: 0 auto;
            padding: 12px 14px;
            border: 1px solid var(--line);
            border-radius: 22px;
            background: rgba(255, 255, 255, 0.10);
            backdrop-filter: blur(18px);
            -webkit-backdrop-filter: blur(18px);
            box-shadow: 0 18px 42px rgba(15, 23, 42, 0.22);
        }

        .landing-topbar-brand {
            display: inline-flex;
            align-items: center;
            gap: 10px;
            min-width: 0;
            color: #ffffff;
            text-decoration: none;
            font-weight: 950;
        }

        .landing-topbar-brand img {
            width: 38px;
            height: 38px;
            border-radius: 12px;
            background: #ffffff;
            padding: 4px;
            object-fit: contain;
        }

        .landing-topbar-brand span {
            overflow: hidden;
            text-overflow: ellipsis;
            white-space: nowrap;
        }

        .landing-topbar-actions {
            display: inline-flex;
            align-items: center;
            gap: 10px;
            flex: 0 0 auto;
        }

        .topbar-btn {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
            min-height: 42px;
            padding: 0 16px;
            border-radius: 999px;
            border: 1px solid rgba(250, 204, 21, 0.32);
            background: rgba(255, 255, 255, 0.12);
            color: #ffffff;
            font-family: inherit;
            font-size: 13px;
            font-weight: 900;
            line-height: 1;
            text-decoration: none;
            cursor: pointer;
            transition: transform .18s ease, background .18s ease, color .18s ease, border-color .18s ease, box-shadow .18s ease;
        }

        .topbar-btn:hover,
        .topbar-btn:focus-visible {
            transform: translateY(-1px);
            background: linear-gradient(135deg, var(--gold), var(--gold-soft));
            border-color: var(--gold);
            color: var(--maroon);
            box-shadow: 0 12px 26px rgba(15, 23, 42, 0.20);
            outline: none;
        }

        .topbar-btn svg {
            width: 17px;
            height: 17px;
            flex: 0 0 auto;
            stroke-width: 2.2;
        }

        .topbar-btn-adaptive > span,
        .topbar-btn-adaptive > svg {
            color: #ffffff;
            mix-blend-mode: difference;
        }

        .topbar-btn-adaptive:hover > span,
        .topbar-btn-adaptive:hover > svg,
        .topbar-btn-adaptive:focus-visible > span,
        .topbar-btn-adaptive:focus-visible > svg {
            color: var(--maroon);
            mix-blend-mode: normal;
        }

        .topbar-btn-local {
            border-color: rgba(255, 255, 255, 0.42);
            background: rgba(15, 23, 42, 0.24);
        }

        .topbar-btn-local:hover,
        .topbar-btn-local:focus-visible {
            border-color: var(--gold);
            background: linear-gradient(135deg, var(--gold), var(--gold-soft));
            color: var(--maroon);
        }

        .landing-panel {
            width: min(1040px, 100%);
            min-height: min(620px, calc(100vh - 72px));
            display: grid;
            grid-template-columns: minmax(0, 1.06fr) minmax(320px, 0.94fr);
            border: 1px solid var(--line);
            border-radius: 26px;
            overflow: hidden;
            background: rgba(255, 255, 255, 0.08);
            backdrop-filter: blur(18px);
            -webkit-backdrop-filter: blur(18px);
            box-shadow: 0 28px 68px rgba(15, 23, 42, 0.34);
        }

        .landing-panel,
        .info-column,
        .login-column,
        .brand-kicker,
        .brand-name,
        .hero-copy h1,
        .hero-copy p,
        .trust-title,
        .trust-copy,
        .trust-item,
        .trust-icon {
            transition:
                background .52s cubic-bezier(.22, 1, .36, 1),
                border-color .52s cubic-bezier(.22, 1, .36, 1),
                color .34s ease,
                box-shadow .52s cubic-bezier(.22, 1, .36, 1);
        }

        .info-column {
            position: relative;
            display: grid;
            padding: clamp(24px, 4.4vw, 48px);
            background:
                linear-gradient(145deg, rgba(112, 19, 27, 0.68), rgba(77, 13, 23, 0.52)),
                rgba(112, 19, 27, 0.34);
            border-right: 1px solid rgba(250, 204, 21, 0.14);
            backdrop-filter: blur(22px);
            -webkit-backdrop-filter: blur(22px);
        }

        .info-column::after {
            content: "";
            position: absolute;
            right: 0;
            top: 34px;
            bottom: 34px;
            width: 1px;
            background: linear-gradient(180deg, transparent, rgba(250, 204, 21, 0.40), transparent);
        }

        .info-default,
        .info-login-swap {
            grid-column: 1;
            grid-row: 1;
            transition:
                opacity .32s ease,
                transform .42s cubic-bezier(.22, 1, .36, 1),
                visibility .32s ease;
        }

        .info-default {
            display: flex;
            flex-direction: column;
            justify-content: space-between;
            gap: 26px;
            opacity: 1;
            transform: translateX(0);
            visibility: visible;
        }

        .info-login-swap {
            width: min(360px, 100%);
            align-self: center;
            justify-self: center;
            display: grid;
            gap: 18px;
            opacity: 0;
            transform: translateX(22px);
            visibility: hidden;
            pointer-events: none;
        }

        .info-login-swap > .portal-btn {
            height: 54px !important;
            padding: 0 24px !important;
            font-size: 15px !important;
            gap: 10px !important;
            display: inline-flex !important;
            line-height: 1 !important;
        }

        .info-login-swap .portal-btn svg {
            width: 18px !important;
            height: 18px !important;
        }

        .landing-panel.is-help .info-default {
            opacity: 0;
            transform: translateX(-22px);
            visibility: hidden;
            pointer-events: none;
        }

        .landing-panel.is-help .info-login-swap {
            opacity: 1;
            transform: translateX(0);
            visibility: visible;
            pointer-events: auto;
        }

        .landing-panel.is-help .info-column {
            background:
                linear-gradient(180deg, rgba(255, 255, 255, 0.98), rgba(255, 248, 230, 0.94));
            border-right-color: rgba(112, 19, 27, 0.12);
            color: var(--ink);
        }

        .landing-panel.is-help .info-column::after {
            background: linear-gradient(180deg, transparent, rgba(112, 19, 27, 0.22), transparent);
        }

        .landing-panel.is-help .brand-badge {
            background: #ffffff;
            border-color: rgba(112, 19, 27, 0.10);
            box-shadow: 0 12px 24px rgba(112, 19, 27, 0.08);
        }

        .landing-panel.is-help .brand-kicker,
        .landing-panel.is-help .hero-copy h1 {
            color: var(--maroon);
        }

        .landing-panel.is-help .brand-name,
        .landing-panel.is-help .hero-copy p {
            color: #64748b;
        }

        .landing-panel.is-help .trust-item {
            background: rgba(112, 19, 27, 0.045);
            border-color: rgba(112, 19, 27, 0.10);
        }

        .landing-panel.is-help .trust-icon {
            color: #ffffff;
            background: linear-gradient(135deg, var(--maroon), var(--maroon-strong));
        }

        .landing-panel.is-help .trust-title {
            color: var(--maroon);
        }

        .landing-panel.is-help .trust-copy {
            color: #64748b;
        }

        .brand-row {
            display: flex;
            align-items: center;
            gap: 14px;
        }

        .brand-badge {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            width: 58px;
            height: 58px;
            border-radius: 18px;
            border: 1px solid rgba(250, 204, 21, 0.24);
            background: rgba(255, 255, 255, 0.10);
            box-shadow: inset 0 1px 0 rgba(255, 255, 255, 0.14);
        }

        .brand-badge img {
            width: 45px;
            height: 45px;
            object-fit: contain;
        }

        .brand-meta {
            display: grid;
            gap: 3px;
        }

        .brand-kicker {
            margin: 0;
            color: var(--gold);
            font-size: 11px;
            font-weight: 900;
            letter-spacing: 0.18em;
            text-transform: uppercase;
        }

        .brand-name {
            margin: 0;
            font-size: 15px;
            font-weight: 800;
            color: rgba(255, 255, 255, 0.92);
        }

        .hero-copy {
            max-width: 650px;
        }

        .hero-copy h1 {
            margin: 0;
            max-width: 620px;
            font-size: clamp(36px, 5.2vw, 58px);
            line-height: 0.98;
            font-weight: 950;
            letter-spacing: 0;
            color: #ffffff;
        }

        .hero-copy p {
            margin: 18px 0 0;
            max-width: 560px;
            color: var(--muted);
            font-size: 16px;
            line-height: 1.75;
        }

        .trust-grid {
            display: grid;
            grid-template-columns: repeat(2, minmax(0, 1fr));
            gap: 10px;
        }

        .trust-item {
            display: flex;
            align-items: center;
            gap: 10px;
            min-height: 54px;
            padding: 11px 13px;
            border-radius: 14px;
            border: 1px solid rgba(250, 204, 21, 0.16);
            background: rgba(15, 23, 42, 0.14);
        }

        .trust-icon {
            width: 34px;
            height: 34px;
            border-radius: 12px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            color: var(--maroon);
            background: linear-gradient(135deg, var(--gold), var(--gold-soft));
            flex: 0 0 auto;
        }

        .trust-icon svg {
            width: 17px;
            height: 17px;
            stroke-width: 2.3;
        }

        .trust-text {
            display: grid;
            gap: 2px;
            min-width: 0;
        }

        .trust-title {
            font-size: 13px;
            font-weight: 900;
            color: #ffffff;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }

        .trust-copy {
            font-size: 11px;
            font-weight: 700;
            color: rgba(255, 255, 255, 0.62);
        }

        .login-column {
            position: relative;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: clamp(20px, 3.4vw, 36px);
            background:
                linear-gradient(180deg, rgba(255, 255, 255, 0.98), rgba(255, 248, 230, 0.94));
            color: var(--ink);
        }

        .landing-panel.is-help .login-column {
            background:
                linear-gradient(145deg, rgba(112, 19, 27, 0.84), rgba(77, 13, 23, 0.74)),
                rgba(112, 19, 27, 0.52);
            color: #ffffff;
        }

        .login-card {
            width: min(360px, 100%);
            display: grid;
            gap: 18px;
        }

        .login-primary,
        .help-panel {
            grid-column: 1;
            grid-row: 1;
            transition:
                opacity .32s ease,
                transform .42s cubic-bezier(.22, 1, .36, 1),
                visibility .32s ease;
        }

        .login-primary {
            display: grid;
            gap: 18px;
            opacity: 1;
            transform: translateX(0);
            visibility: visible;
        }

        .help-panel {
            display: grid;
            gap: 16px;
            opacity: 0;
            transform: translateX(22px);
            visibility: hidden;
            color: #ffffff;
            max-height: min(540px, calc(100vh - 132px));
            overflow-y: auto;
            padding-right: 4px;
            scrollbar-width: thin;
            scrollbar-color: rgba(250, 204, 21, 0.6) rgba(255, 255, 255, 0.08);
        }

        .help-panel::-webkit-scrollbar {
            width: 10px;
        }

        .help-panel::-webkit-scrollbar-track {
            background: rgba(255, 255, 255, 0.08);
            border-radius: 999px;
        }

        .help-panel::-webkit-scrollbar-thumb {
            background: rgba(250, 204, 21, 0.6);
            border-radius: 999px;
            border: 2px solid rgba(255, 255, 255, 0.08);
        }

        .landing-panel.is-help .login-primary {
            opacity: 0;
            transform: translateX(-22px);
            visibility: hidden;
            pointer-events: none;
        }

        .landing-panel.is-help .help-panel {
            opacity: 1;
            transform: translateX(0);
            visibility: visible;
        }

        .logo-stack {
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 14px;
        }

        .logo-frame {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            width: 108px;
            height: 108px;
            border-radius: 22px;
            background: #ffffff;
            border: 1px solid rgba(112, 19, 27, 0.10);
            box-shadow: 0 16px 32px rgba(112, 19, 27, 0.10);
        }

        .logo-frame img {
            width: 84px;
            height: 84px;
            object-fit: contain;
        }

        .logo-frame--clinic {
            width: 108px;
            height: 108px;
            border-color: rgba(250, 204, 21, 0.34);
        }

        .logo-frame--clinic img {
            width: 84px;
            height: 84px;
        }

        .login-copy {
            display: grid;
            gap: 10px;
            text-align: center;
        }

        .login-copy h2 {
            margin: 0;
            color: var(--maroon);
            font-size: 26px;
            line-height: 1.08;
            font-weight: 950;
            letter-spacing: 0;
        }

        .login-copy p {
            margin: 0;
            color: #64748b;
            font-size: 14px;
            line-height: 1.7;
        }

        .workspace-entry {
            display: grid;
            gap: 12px;
        }

        .workspace-utility-actions {
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 14px;
            flex-wrap: wrap;
        }

        .help-btn.help-link,
        .local-login-link {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 7px;
            min-height: 32px;
            width: auto;
            padding: 4px 8px;
            border: 0;
            border-radius: 6px;
            background: transparent;
            color: #ffffff;
            box-shadow: none;
            font-family: inherit;
            font-size: 13px;
            font-weight: 800;
            line-height: 1.2;
            text-decoration: none;
            cursor: pointer;
        }

        .help-btn.help-link:hover,
        .help-btn.help-link:focus-visible,
        .local-login-link:hover,
        .local-login-link:focus-visible,
        .landing-panel.is-help .help-btn.help-link {
            transform: none;
            border: 0;
            background: transparent;
            color: #ffffff;
            box-shadow: none;
            outline: none;
        }

        .help-btn.help-link svg,
        .local-login-link svg {
            width: 17px;
            height: 17px;
            flex: 0 0 auto;
            color: var(--maroon);
            stroke-width: 2.2;
            transition: transform .2s ease;
        }

        .login-primary .system-foot,
        .info-login-swap .system-foot {
            color: #111827;
        }

        .help-btn.help-link:hover svg,
        .help-btn.help-link:focus-visible svg,
        .local-login-link:hover svg,
        .local-login-link:focus-visible svg {
            transform: translateY(-1px) scale(1.06);
        }

        .portal-btn {
            position: relative;
            overflow: hidden;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 10px;
            min-height: 54px;
            width: 100%;
            padding: 0 24px;
            border-radius: 999px;
            border: 1px solid var(--maroon);
            background: linear-gradient(135deg, var(--maroon), var(--maroon-strong));
            color: #ffffff;
            font-size: 15px;
            font-weight: 950;
            letter-spacing: 0.01em;
            text-decoration: none;
            box-shadow:
                0 0 0 4px rgba(112, 19, 27, 0.10),
                0 18px 34px rgba(112, 19, 27, 0.22);
            transition: color .12s ease, transform .18s ease, border-color .18s ease, box-shadow .18s ease, background .18s ease;
        }

        .portal-btn:hover,
        .portal-btn:focus-visible {
            transform: translateY(-1px);
            background: linear-gradient(135deg, var(--gold), var(--gold-soft));
            border-color: var(--gold);
            color: var(--maroon);
            box-shadow:
                0 0 0 4px rgba(250, 204, 21, 0.18),
                0 22px 42px rgba(112, 19, 27, 0.20);
            outline: none;
        }

        .help-btn {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 10px;
            min-height: 54px;
            width: 100%;
            padding: 0 24px;
            border-radius: 999px;
            border: 1px solid var(--maroon);
            background: linear-gradient(135deg, var(--maroon), var(--maroon-strong));
            color: #ffffff;
            font-family: inherit;
            font-size: 15px;
            font-weight: 900;
            text-decoration: none;
            cursor: pointer;
            box-sizing: border-box;
            line-height: 1;
            appearance: none;
            -webkit-appearance: none;
            box-shadow: 0 12px 24px rgba(112, 19, 27, 0.08);
            transition: transform .18s ease, border-color .18s ease, background .18s ease, color .18s ease, box-shadow .18s ease;
        }

        .help-btn:hover,
        .help-btn:focus-visible,
        .landing-panel.is-help .help-btn {
            transform: translateY(-1px);
            background: linear-gradient(135deg, var(--gold), var(--gold-soft));
            border-color: var(--gold);
            color: var(--maroon);
            box-shadow:
                0 0 0 4px rgba(112, 19, 27, 0.14),
                0 16px 30px rgba(112, 19, 27, 0.18);
            outline: none;
        }

        .help-btn svg {
            width: 18px;
            height: 18px;
            flex: 0 0 auto;
            stroke-width: 2.2;
        }

        .portal-btn svg,
        .portal-btn span {
            position: relative;
            z-index: 1;
        }

        .portal-btn svg {
            width: 19px;
            height: 19px;
            flex: 0 0 auto;
            stroke-width: 2.2;
        }

        .notice {
            padding: 12px 14px;
            border-radius: 16px;
            border: 1px solid rgba(220, 38, 38, 0.16);
            background: #fff1f2;
            color: #be123c;
            font-size: 13px;
            line-height: 1.55;
            text-align: center;
            font-weight: 700;
        }

        .system-foot {
            margin: 0;
            text-align: center;
            color: #94a3b8;
            font-size: 12px;
            line-height: 1.6;
        }

        .help-panel-head {
            position: relative;
            position: sticky;
            top: 0;
            z-index: 3;
            display: grid;
            gap: 8px;
            margin: -2px -4px 2px 0;
            padding: 2px 4px 14px 42px;
            background: linear-gradient(180deg, #7b151f 0%, #7b151f 82%, rgba(123, 21, 31, 0) 100%);
            text-align: left;
        }

        .help-panel-back {
            position: absolute;
            top: 0;
            left: 0;
            display: inline-flex;
            width: 32px;
            height: 32px;
            align-items: center;
            justify-content: center;
            padding: 0;
            border: 1px solid rgba(250, 204, 21, .5);
            border-radius: 50%;
            background: rgba(255, 255, 255, .08);
            color: #ffffff;
            cursor: pointer;
            transition: background .2s ease, border-color .2s ease, color .2s ease, transform .2s ease;
        }

        .help-panel-back:hover,
        .help-panel-back:focus-visible {
            border-color: var(--gold);
            background: var(--gold);
            color: var(--maroon);
            transform: translateX(-2px);
            outline: none;
        }

        .help-panel-back svg {
            width: 17px;
            height: 17px;
            stroke-width: 2.3;
        }

        .help-panel-kicker {
            margin: 0;
            color: var(--gold);
            font-size: 11px;
            font-weight: 950;
            letter-spacing: 0.18em;
            text-transform: uppercase;
        }

        .help-panel-title {
            margin: 0;
            color: #ffffff;
            font-size: 28px;
            line-height: 1.08;
            font-weight: 950;
        }

        .help-panel-copy {
            margin: 0;
            color: rgba(255, 255, 255, 0.76);
            font-size: 13px;
            line-height: 1.65;
        }

        .help-guide {
            display: grid;
            gap: 12px;
            padding: 18px 20px 20px;
        }

        .help-guide-legacy {
            display: none;
        }

        .help-accordion {
            overflow: hidden;
            border: 1px solid rgba(250, 204, 21, .18);
            border-radius: 16px;
            background: linear-gradient(180deg, rgba(53, 15, 22, .94), rgba(39, 10, 17, .96));
            transition: border-color .2s ease, background .2s ease, box-shadow .2s ease;
        }

        .help-accordion[open] {
            border-color: rgba(250, 204, 21, .42);
            box-shadow: 0 14px 28px rgba(31, 4, 9, .18);
        }

        .help-accordion summary {
            display: flex;
            min-height: 58px;
            align-items: center;
            gap: 12px;
            padding: 12px 14px;
            color: #ffffff;
            cursor: pointer;
            list-style: none;
            user-select: none;
        }

        .help-accordion summary::-webkit-details-marker {
            display: none;
        }

        .help-accordion summary:focus-visible {
            outline: 2px solid var(--gold);
            outline-offset: -2px;
        }

        .help-accordion-icon {
            width: 36px;
            height: 36px;
            border-radius: 10px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            flex: 0 0 36px;
            background: linear-gradient(135deg, var(--gold), var(--gold-soft));
            color: var(--maroon);
            box-shadow: 0 8px 18px rgba(250, 204, 21, .12);
        }

        .help-accordion-icon svg {
            width: 18px;
            height: 18px;
            stroke-width: 2;
        }

        .help-accordion-heading {
            display: grid;
            flex: 1 1 auto;
            gap: 1px;
            min-width: 0;
        }

        .help-accordion-heading strong {
            color: #ffffff;
            font-size: 14px;
            line-height: 1.3;
        }

        .help-accordion-heading span {
            overflow: hidden;
            color: rgba(255, 255, 255, .66);
            font-size: 11px;
            line-height: 1.35;
            text-overflow: ellipsis;
            white-space: nowrap;
        }

        .help-accordion-chevron {
            width: 17px;
            height: 17px;
            flex: 0 0 auto;
            color: var(--gold);
            transition: transform .22s ease;
        }

        .help-accordion[open] .help-accordion-chevron {
            transform: rotate(180deg);
        }

        .help-accordion-body {
            padding: 0 15px 15px 63px;
        }

        .help-check-list,
        .help-issue-list {
            display: grid;
            gap: 7px;
            margin: 0;
            padding: 0;
            list-style: none;
        }

        .help-check-list li,
        .help-issue-list li {
            position: relative;
            padding-left: 18px;
            color: rgba(255, 255, 255, .78);
            font-size: 12px;
            line-height: 1.55;
        }

        .help-check-list li::before {
            position: absolute;
            top: 1px;
            left: 0;
            color: var(--gold);
            font-weight: 950;
            content: "✓";
        }

        .help-issue-list {
            grid-template-columns: repeat(2, minmax(0, 1fr));
        }

        .help-issue-list li {
            padding: 8px 10px 8px 25px;
            border: 1px solid rgba(255, 255, 255, .08);
            border-radius: 10px;
            background: rgba(255, 255, 255, .05);
        }

        .help-issue-list li::before {
            position: absolute;
            top: 7px;
            left: 8px;
            color: var(--gold);
            font-weight: 950;
            content: "!";
        }

        .help-contact-card {
            display: grid;
            gap: 5px;
            padding: 11px 12px;
            border-left: 3px solid var(--gold);
            border-radius: 0 6px 6px 0;
            background: rgba(250, 204, 21, .08);
        }

        .help-contact-card strong {
            color: #ffffff;
            font-size: 12px;
        }

        .help-contact-card span {
            color: rgba(255, 255, 255, .72);
            font-size: 11px;
            line-height: 1.5;
        }

        @media (max-width: 480px) {
            .help-issue-list {
                grid-template-columns: 1fr;
            }

            .help-accordion-body {
                padding-left: 13px;
            }
        }

        @media (max-width: 920px) {
            .landing-shell {
                align-items: flex-start;
                padding: 12px 14px 16px;
            }

            .landing-topbar {
                top: 12px;
                left: 12px;
                right: 12px;
                width: calc(100% - 24px);
                align-items: stretch;
                flex-direction: column;
                gap: 10px;
                padding: 10px;
            }

            .landing-topbar-brand {
                justify-content: center;
            }

            .landing-topbar-actions {
                width: 100%;
                justify-content: center;
                flex-wrap: wrap;
            }

            .topbar-btn {
                flex: 1 1 150px;
            }

            .landing-panel {
                min-height: 0;
                grid-template-columns: 1fr;
                border-radius: 24px;
            }

            .info-column::after {
                top: auto;
                right: 24px;
                left: 24px;
                bottom: 0;
                width: auto;
                height: 1px;
                background: linear-gradient(90deg, transparent, rgba(250, 204, 21, 0.34), transparent);
            }

            .hero-copy h1 {
                font-size: clamp(36px, 9vw, 54px);
            }

            .login-column {
                padding: 28px 20px 34px;
            }

            .landing-panel.is-help .info-column::after {
                background: linear-gradient(90deg, transparent, rgba(112, 19, 27, 0.22), transparent);
            }
        }

        @media (max-width: 640px) {
            .info-column {
                padding: 24px 18px;
            }

            .trust-grid {
                grid-template-columns: 1fr;
            }

            .logo-frame {
                width: 72px;
                height: 72px;
                border-radius: 18px;
            }

            .logo-frame img {
                width: 58px;
                height: 58px;
            }

            .logo-frame--clinic {
                width: 72px;
                height: 72px;
            }

            .logo-frame--clinic img {
                width: 58px;
                height: 58px;
            }
        }

        /* Preloader Styles */
        #preloader {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: linear-gradient(135deg, rgba(51, 8, 13, 0.98), rgba(112, 19, 27, 0.95));
            display: flex;
            align-items: center;
            justify-content: center;
            z-index: 9999;
            opacity: 1;
            transition: opacity 0.6s ease, visibility 0.6s ease;
            visibility: visible;
        }

        #preloader.hidden {
            opacity: 0;
            visibility: hidden;
            pointer-events: none;
        }

        .preloader-logo {
            width: 120px;
            height: 120px;
            animation: pulseLogo 2.5s ease-in-out infinite;
        }

        .preloader-logo img {
            width: 100%;
            height: 100%;
            object-fit: contain;
        }

        @keyframes pulseLogo {
            0%, 100% {
                opacity: 0.6;
                transform: scale(1);
            }
            50% {
                opacity: 1;
                transform: scale(1.08);
            }
        }

        /* SA Workspace Selection Styles */
        .sa-workspace-selector {
            display: none;
            grid-template-columns: 1fr;
            gap: 14px;
            animation: fadeIn 0.4s ease;
        }

        .sa-workspace-selector.visible {
            display: grid;
        }

        @keyframes fadeIn {
            from {
                opacity: 0;
                transform: translateY(10px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .workspace-btn {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 10px;
            min-height: 54px;
            width: 100%;
            padding: 0 24px;
            border-radius: 999px;
            border: 1px solid var(--maroon);
            background: linear-gradient(135deg, var(--maroon), var(--maroon-strong));
            color: #ffffff;
            font-size: 15px;
            font-weight: 950;
            text-decoration: none;
            cursor: pointer;
            box-shadow: 0 12px 24px rgba(112, 19, 27, 0.08);
            transition: transform .18s ease, border-color .18s ease, background .18s ease, color .18s ease, box-shadow .18s ease;
            text-align: center;
            font-family: inherit;
        }

        .workspace-btn:hover,
        .workspace-btn:focus-visible {
            transform: translateY(-1px);
            background: linear-gradient(135deg, var(--gold), var(--gold-soft));
            border-color: var(--gold);
            color: var(--maroon);
            box-shadow: 0 0 0 4px rgba(112, 19, 27, 0.14), 0 16px 30px rgba(112, 19, 27, 0.18);
            outline: none;
        }

        .workspace-badge {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            width: 20px;
            height: 20px;
            border-radius: 50%;
            background: rgba(255, 255, 255, 0.3);
            font-size: 12px;
            font-weight: 800;
        }

      
        body {
            transition: background 0.45s ease, color 0.28s ease;
        }

        body.landing-theme-light {
            color: #4b1520;
            background:
                linear-gradient(135deg, rgba(255, 248, 238, 0.94), rgba(255, 255, 255, 0.90)),
                url('{{ asset('images/PUPBG.jpg') }}') center center / cover no-repeat fixed;
        }

        body.landing-theme-light::before {
            background:
                radial-gradient(circle at 20% 18%, rgba(112, 19, 27, 0.14), transparent 24%),
                radial-gradient(circle at 82% 12%, rgba(250, 204, 21, 0.18), transparent 22%),
                linear-gradient(180deg, rgba(255, 255, 255, 0.22), rgba(255, 255, 255, 0.10));
        }

        .landing-shell {
            align-items: stretch;
            padding: clamp(2px, 1vw, 10px) 18px 18px;
        }

        .landing-theme-toggle {
            position: fixed;
            top: 18px;
            right: 18px;
            z-index: 40;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 0;
            min-height: 56px;
            width: 56px;
            height: 56px;
            padding: 0;
            border-radius: 999px;
            border: 1px solid rgba(250, 204, 21, 0.45);
            background: rgba(20, 16, 20, 0.52);
            color: #ffffff;
            font-family: inherit;
            font-size: 12px;
            font-weight: 900;
            cursor: pointer;
            box-shadow: 0 18px 32px rgba(15, 23, 42, 0.22);
            backdrop-filter: blur(18px);
            -webkit-backdrop-filter: blur(18px);
            transition: transform .18s ease, background .18s ease, color .18s ease, border-color .18s ease;
        }

        .landing-theme-toggle:hover,
        .landing-theme-toggle:focus-visible {
            transform: translateY(-1px);
            background: linear-gradient(135deg, var(--gold), var(--gold-soft));
            color: var(--maroon);
            border-color: var(--gold);
            outline: none;
        }

        .landing-theme-toggle svg {
            width: 26px;
            height: 26px;
            stroke: currentColor;
            stroke-width: 2;
            fill: none;
            flex: 0 0 auto;
        }

        body.landing-theme-light .landing-theme-toggle {
            background: rgba(255, 255, 255, 0.88);
            color: #70131b;
            border-color: rgba(112, 19, 27, 0.24);
        }

        .landing-announcement-btn {
            position: fixed;
            top: 86px;
            right: 18px;
            z-index: 40;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 0;
            min-height: 56px;
            width: 56px;
            height: 56px;
            padding: 0;
            border-radius: 999px;
            border: 1px solid rgba(250, 204, 21, 0.45);
            background: rgba(20, 16, 20, 0.52);
            color: #ffffff;
            font-family: inherit;
            font-size: 12px;
            font-weight: 900;
            cursor: pointer;
            box-shadow: 0 18px 32px rgba(15, 23, 42, 0.22);
            backdrop-filter: blur(18px);
            -webkit-backdrop-filter: blur(18px);
            transition: transform .18s ease, background .18s ease, color .18s ease, border-color .18s ease;
        }

        .landing-announcement-btn:hover,
        .landing-announcement-btn:focus-visible {
            transform: translateY(-1px);
            background: linear-gradient(135deg, var(--gold), var(--gold-soft));
            color: var(--maroon);
            border-color: var(--gold);
            outline: none;
        }

        .landing-announcement-btn svg {
            width: 32px;
            height: 32px;
            flex: 0 0 auto;
            stroke: currentColor;
            stroke-width: 1.5;
            fill: none;
        }

        body.landing-theme-light .landing-announcement-btn {
            background: rgba(255, 255, 255, 0.88);
            color: #70131b;
            border-color: rgba(112, 19, 27, 0.24);
        }

        body.landing-theme-light .landing-announcement-btn:hover,
        body.landing-theme-light .landing-announcement-btn:focus-visible {
            background: linear-gradient(135deg, #facc15, #fef3c7);
            color: #70131b;
            border-color: #facc15;
        }

        .announcement-badge {
            position: absolute;
            top: -8px;
            right: -8px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            min-width: 24px;
            height: 24px;
            padding: 0 6px;
            border-radius: 999px;
            background: linear-gradient(135deg, #facc15, #fcd34d);
            color: #70131b;
            font-size: 11px;
            font-weight: 900;
            border: 2px solid rgba(20, 16, 20, 0.96);
            box-shadow: 0 2px 8px rgba(250, 204, 21, 0.4);
            animation: badge-pulse 2s ease-in-out infinite;
        }

        @keyframes badge-pulse {
            0%, 100% {
                transform: scale(1);
                box-shadow: 0 2px 8px rgba(250, 204, 21, 0.4);
            }
            50% {
                transform: scale(1.1);
                box-shadow: 0 4px 12px rgba(250, 204, 21, 0.6);
            }
        }

        body.landing-theme-light .announcement-badge {
            border-color: rgba(255, 255, 255, 0.96);
        }

        .landing-assistant-btn {
            position: fixed;
            top: 154px;
            right: 18px;
            z-index: 40;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 0;
            min-height: 56px;
            width: 56px;
            height: 56px;
            padding: 0;
            border-radius: 16px;
            border: 1px solid rgba(255, 255, 255, 0.3);
            background: #8f1024;
            color: #ffffff;
            font-family: inherit;
            font-size: 12px;
            font-weight: 900;
            cursor: pointer;
            overflow: hidden;
            box-shadow: 0 18px 32px rgba(112, 19, 27, 0.28);
            backdrop-filter: blur(18px);
            -webkit-backdrop-filter: blur(18px);
            transition: transform .18s ease, background .18s ease, color .18s ease, border-color .18s ease;
        }

        .landing-assistant-btn:hover,
        .landing-assistant-btn:focus-visible {
            transform: translateY(-1px);
            background: #70131b;
            color: #ffffff;
            border-color: rgba(250, 204, 21, 0.75);
            outline: none;
        }

        .landing-assistant-btn svg {
            width: 100%;
            height: 100%;
            stroke: none;
            stroke-width: 0;
            fill: none;
            flex: 0 0 auto;
        }

        .landing-assistant-btn .assistant-icon-shadow {
            filter: drop-shadow(0 5px 8px rgba(55, 6, 18, 0.22));
        }

        body.landing-theme-light .landing-assistant-btn {
            background: #8f1024;
            color: #ffffff;
            border-color: rgba(112, 19, 27, 0.16);
        }

        body.landing-theme-light .landing-assistant-btn:hover,
        body.landing-theme-light .landing-assistant-btn:focus-visible {
            background: #70131b;
            color: #ffffff;
            border-color: rgba(250, 204, 21, 0.75);
        }

        /* AI Chatbot Modal Styles */
        .assistant-modal-overlay {
            position: fixed;
            inset: 0;
            background: rgba(0, 0, 0, 0.4);
            backdrop-filter: blur(4px);
            z-index: 999;
            display: none;
            align-items: center;
            justify-content: flex-end;
            padding: 24px;
        }

        .assistant-modal-overlay.is-open {
            display: flex;
        }

        .assistant-modal {
            position: relative;
            width: min(520px, calc(100vw - 48px));
            min-height: 320px;
            max-height: min(560px, calc(100vh - 48px));
            background: rgba(20, 16, 20, 0.96);
            border: 1px solid rgba(250, 204, 21, 0.2);
            border-radius: 22px;
            backdrop-filter: blur(18px);
            -webkit-backdrop-filter: blur(18px);
            box-shadow: 0 24px 70px rgba(15, 23, 42, 0.36);
            z-index: 1000;
            display: flex;
            flex-direction: column;
            transform: translateX(18px) scale(0.96);
            opacity: 0;
            transition: transform 0.22s ease, opacity 0.22s ease;
            overflow: hidden;
        }

        .assistant-modal.is-open {
            transform: translateX(0) scale(1);
            opacity: 1;
        }

        .assistant-modal-header {
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 24px;
            background: linear-gradient(135deg, #70131b, #8f1827 58%, #a11d31);
            border-bottom: 1px solid rgba(250, 204, 21, 0.22);
            flex-shrink: 0;
            box-shadow: 0 14px 28px rgba(15, 23, 42, 0.18);
        }

        .assistant-modal-title,
        .announcement-modal-title {
            display: inline-flex;
            align-items: center;
            gap: 12px;
            margin: 0;
            font-size: 1.2rem;
            font-weight: 900;
            color: #ffffff;
        }

        .modal-title-icon {
            width: 34px;
            height: 34px;
            flex: 0 0 34px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            border-radius: 12px;
            background: rgba(255, 255, 255, 0.14);
            border: 1px solid rgba(255, 255, 255, 0.18);
            color: #ffffff;
            box-shadow: 0 10px 20px rgba(15, 23, 42, 0.16);
        }

        .modal-title-icon svg {
            width: 22px;
            height: 22px;
            flex: 0 0 auto;
            stroke: currentColor;
            stroke-width: 1.6;
            fill: none;
        }

        .modal-title-icon.is-assistant {
            overflow: hidden;
            padding: 0;
            border-radius: 10px;
            background: #8f1024;
        }

        .modal-title-icon.is-assistant svg {
            width: 34px;
            height: 34px;
            stroke: none;
        }

        .assistant-modal-close {
            position: relative;
            overflow: hidden;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            width: 40px;
            height: 40px;
            border-radius: 999px;
            border: 1px solid rgba(250, 204, 21, 0.24);
            background: rgba(18, 12, 15, 0.32);
            color: #ffffff;
            cursor: pointer;
            box-shadow: 0 10px 20px rgba(15, 23, 42, 0.22);
            transition: transform .18s ease, color .18s ease, background .18s ease, border-color .18s ease, box-shadow .18s ease;
            z-index: 0;
        }

        .assistant-modal-close::after {
            content: "";
            position: absolute;
            top: -35%;
            left: -78%;
            width: 58%;
            height: 170%;
            background: linear-gradient(120deg, transparent 0%, rgba(255, 244, 180, .16) 34%, rgba(255, 244, 180, .62) 50%, rgba(255, 244, 180, .16) 66%, transparent 100%);
            transform: skewX(-18deg);
            transition: left .52s ease;
            pointer-events: none;
            z-index: -1;
        }

        .assistant-modal-close:hover,
        .assistant-modal-close:focus-visible {
            transform: translateY(-1px);
            background: #facc15;
            color: #70131b;
            border-color: #facc15;
            box-shadow: 0 0 0 3px rgba(250, 204, 21, 0.18), 0 14px 24px rgba(15, 23, 42, 0.2);
            outline: none;
        }

        .assistant-modal-close:hover::after,
        .assistant-modal-close:focus-visible::after {
            left: 128%;
        }

        .assistant-modal-close svg {
            position: relative;
            z-index: 1;
            width: 20px;
            height: 20px;
            stroke: currentColor;
            stroke-width: 2;
            fill: none;
        }

        .assistant-modal-content {
            flex: 1;
            min-height: 0;
            overflow-y: auto;
            padding: 24px;
            color: #cbd5e1;
            font-size: 0.95rem;
            line-height: 1.6;
        }

        .assistant-modal-content p {
            margin: 0 0 16px;
        }

        .assistant-modal-content p:last-child {
            margin-bottom: 0;
        }

        body.landing-theme-light .assistant-modal {
            background: rgba(255, 255, 255, 0.96);
            border-color: rgba(112, 19, 27, 0.1);
            box-shadow: 0 24px 70px rgba(112, 19, 27, 0.16);
        }

        body.landing-theme-light .assistant-modal-header {
            border-bottom-color: rgba(250, 204, 21, 0.22);
        }

        body.landing-theme-light .assistant-modal-title {
            color: #ffffff;
        }

        body.landing-theme-light .assistant-modal-close {
            background: rgba(18, 12, 15, 0.32);
            color: #ffffff;
            border-color: rgba(250, 204, 21, 0.24);
        }

        body.landing-theme-light .assistant-modal-close:hover,
        body.landing-theme-light .assistant-modal-close:focus-visible {
            background: #facc15;
            color: #70131b;
            border-color: #facc15;
        }

        body.landing-theme-light .assistant-modal-content {
            color: #64748b;
        }

        .landing-panel {
            position: relative;
            width: min(1040px, 100%);
            min-height: min(650px, calc(100vh - 72px));
            display: block;
            padding: 0 0 14px;
            border-radius: 0;
            overflow: visible;
            background: transparent;
            border: 0;
            backdrop-filter: none;
            -webkit-backdrop-filter: none;
            box-shadow: none;
        }

        body.landing-theme-light .landing-panel {
            background: transparent;
            border-color: transparent;
            box-shadow: none;
        }

        .landing-panel::before,
        .landing-panel::after {
            content: none;
        }

        .landing-panel::before {
            top: -160px;
            left: -120px;
            width: 360px;
            height: 360px;
            background: radial-gradient(circle, rgba(250, 204, 21, 0.22), transparent 68%);
        }

        .landing-panel::after {
            right: -140px;
            bottom: -180px;
            width: 420px;
            height: 420px;
            background: radial-gradient(circle, rgba(255, 255, 255, 0.12), transparent 68%);
        }

        body.landing-theme-light .landing-panel::before {
            background: radial-gradient(circle, rgba(250, 204, 21, 0.18), transparent 68%);
        }

        body.landing-theme-light .landing-panel::after {
            background: radial-gradient(circle, rgba(112, 19, 27, 0.10), transparent 68%);
        }

        .gateway-stage {
            position: relative;
            z-index: 2;
            min-height: 100%;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: flex-start;
            gap: 18px;
            text-align: center;
            transition: opacity .3s ease, transform .34s ease;
        }

        .gateway-top-content {
            width: 100%;
            display: flex;
            flex-direction: column;
            align-items: center;
            gap: 10px;
            transition: opacity .26s ease, transform .32s ease;
        }

        .gateway-brand {
            width: min(760px, 100%);
            display: grid;
            justify-items: center;
            gap: 18px;
        }

        .gateway-logo-row {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 16px;
            flex-wrap: wrap;
        }

        .gateway-logo-card {
            width: 72px;
            height: 72px;
            border-radius: 18px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            background: rgba(255, 255, 255, 0.94);
            border: 1px solid rgba(250, 204, 21, 0.36);
            box-shadow: 0 18px 34px rgba(15, 23, 42, 0.16);
        }

        .gateway-logo-card img {
            width: 48px;
            height: 48px;
            object-fit: contain;
        }

        .gateway-kicker {
            margin: 0;
            font-size: 10px;
            font-weight: 950;
            letter-spacing: 0.2em;
            text-transform: uppercase;
            color: #facc15;
        }

        .gateway-title {
            margin: 0;
            max-width: 820px;
            color: #ffffff;
            font-size: clamp(2rem, 4vw, 3.7rem);
            line-height: 1.02;
            font-weight: 950;
            letter-spacing: -0.03em;
            text-wrap: balance;
        }

        @media (min-width: 900px) {
            .gateway-title {
                max-width: 100%;
                white-space: nowrap;
            }
        }

        body.landing-theme-light .gateway-title {
            color: #70131b;
        }

        .gateway-copy {
            margin: 0;
            max-width: 760px;
            color: rgba(255, 255, 255, 0.82);
            font-size: clamp(0.88rem, 1.08vw, 0.96rem);
            line-height: 1.58;
            font-weight: 500;
            text-wrap: balance;
        }

        body.landing-theme-light .gateway-copy,
        body.landing-theme-light .system-foot,
        body.landing-theme-light .workspace-utility-actions .local-login-link,
        body.landing-theme-light .workspace-utility-actions .help-btn {
            color: #6b7280;
        }

        .gateway-notice {
            width: min(620px, 100%);
        }

        .workspace-entry.gateway-actions {
            width: min(500px, 100%);
            display: grid;
            gap: 12px;
            justify-items: stretch;
        }

        .gateway-actions .portal-btn {
            min-height: 64px;
            width: 100%;
            padding: 0;
            border-radius: 0;
            background: transparent;
            border: 0;
            box-shadow: none;
            display: grid;
            grid-template-columns: 96px minmax(0, 1fr);
            align-items: center;
            gap: 12px;
            font-size: 1.02rem;
            font-weight: 950;
            position: relative;
        }

        .gateway-actions .portal-btn .portal-btn__label {
            position: relative;
            min-height: 64px;
            padding: 0 70px 0 24px;
            display: flex;
            align-items: center;
            justify-content: flex-start;
            text-align: left;
            background: linear-gradient(135deg, rgba(112, 19, 27, 0.92), rgba(143, 34, 48, 0.94));
            border: 1px solid rgba(250, 204, 21, 0.50);
            box-shadow: 0 18px 34px rgba(112, 19, 27, 0.24);
            border-radius: 0 18px 18px 0;
            overflow: hidden;
            z-index: 1;
            isolation: isolate;
        }

        .gateway-actions .portal-btn .portal-btn__label::before {
            content: "";
            position: absolute;
            inset: 0;
            background: linear-gradient(135deg, var(--gold), var(--gold-soft));
            transform: scaleX(0);
            transform-origin: left center;
            transition: transform 0.32s ease;
            pointer-events: none;
            z-index: -2;
        }

        .gateway-actions .portal-btn .portal-btn__label::after {
            content: "";
            position: absolute;
            inset: 0;
            background:
                linear-gradient(120deg,
                    rgba(255, 248, 196, 0) 0%,
                    rgba(255, 239, 181, 0.14) 20%,
                    rgba(255, 239, 181, 0.46) 48%,
                    rgba(255, 239, 181, 0.14) 76%,
                    rgba(255, 248, 196, 0) 100%);
            transform: translateX(-135%);
            transition: transform 0.9s ease;
            pointer-events: none;
            z-index: -1;
        }

        .gateway-actions .portal-btn:hover .portal-btn__label::before,
        .gateway-actions .portal-btn:focus-visible .portal-btn__label::before {
            transform: scaleX(1);
        }

        .gateway-actions .portal-btn:hover .portal-btn__label::after,
        .gateway-actions .portal-btn:focus-visible .portal-btn__label::after {
            transform: translateX(135%);
        }

        .gateway-actions .portal-btn .portal-btn__label > span,
        .gateway-actions .portal-btn .portal-btn__label > svg,
        .gateway-actions .portal-btn .portal-btn__label > i,
        .gateway-actions .portal-btn .portal-btn__label > strong,
        .gateway-actions .portal-btn .portal-btn__label > em,
        .gateway-actions .portal-btn .portal-btn__label > b {
            position: relative;
            z-index: 1;
        }

        .gateway-actions .portal-btn .portal-btn__icon {
            width: 96px;
            min-height: 64px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            background: linear-gradient(135deg, rgba(112, 19, 27, 0.92), rgba(143, 34, 48, 0.94));
            border: 1px solid rgba(250, 204, 21, 0.50);
            color: currentColor;
            box-shadow: 0 18px 34px rgba(112, 19, 27, 0.24);
            border-radius: 18px 0 0 18px;
        }

        .gateway-actions .portal-btn .portal-btn__icon svg,
        .gateway-actions .portal-btn .portal-btn__arrow svg {
            width: 20px;
            height: 20px;
            stroke: currentColor;
            stroke-width: 2;
            fill: none;
        }

        .gateway-actions .portal-btn .portal-btn__arrow {
            position: absolute;
            right: 18px;
            top: 50%;
            transform: translateY(-50%);
            width: 34px;
            height: 34px;
            min-height: 34px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            background: rgba(255, 255, 255, 0.12);
            border: 1px solid rgba(255, 255, 255, 0.18);
            color: currentColor;
            box-shadow: none;
            z-index: 2;
        }

        .gateway-actions .portal-btn:hover .portal-btn__arrow,
        .gateway-actions .portal-btn:focus-visible .portal-btn__arrow {
            background: rgba(255, 255, 255, 0.22);
            border-color: rgba(255, 255, 255, 0.28);
            color: var(--maroon);
        }

        .gateway-actions .portal-btn:hover,
        .gateway-actions .portal-btn:focus-visible {
            color: var(--maroon);
        }

        .gateway-actions .portal-btn:hover .portal-btn__icon,
        .gateway-actions .portal-btn:focus-visible .portal-btn__icon {
            background: linear-gradient(135deg, var(--gold), var(--gold-soft));
            border-color: var(--gold);
            color: var(--maroon);
        }

        .gateway-actions .portal-btn:hover .portal-btn__label,
        .gateway-actions .portal-btn:focus-visible .portal-btn__label {
            border-color: var(--gold);
            color: #111111;
        }

        .gateway-actions .portal-btn--idp {
            color: #111111;
        }

        .gateway-actions .portal-btn--idp .portal-btn__label,
        .gateway-actions .portal-btn--idp .portal-btn__icon,
        .gateway-actions .portal-btn--idp .portal-btn__arrow {
            background: #ffffff;
            border-color: rgba(255, 255, 255, 0.96);
            color: #111111;
        }

        .gateway-actions .portal-btn--idp:hover,
        .gateway-actions .portal-btn--idp:focus-visible {
            color: var(--maroon);
        }

        .gateway-actions .portal-btn--idp:hover .portal-btn__icon,
        .gateway-actions .portal-btn--idp:focus-visible .portal-btn__icon,
        .gateway-actions .portal-btn--idp:hover .portal-btn__label,
        .gateway-actions .portal-btn--idp:focus-visible .portal-btn__label {
            background: linear-gradient(135deg, var(--gold), var(--gold-soft));
            border-color: var(--gold);
            color: var(--maroon);
        }

        .gateway-actions .portal-btn--idp:hover .portal-btn__arrow,
        .gateway-actions .portal-btn--idp:focus-visible .portal-btn__arrow {
            background: linear-gradient(135deg, #fff4b8, #ffe693);
            border-color: #ffffff;
            color: var(--maroon);
        }

        .workspace-utility-actions.gateway-utility {
            justify-content: center;
            margin-top: 0;
        }

        .workspace-utility-actions.gateway-utility .help-btn {
            color: rgba(255, 255, 255, 0.9);
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
            min-height: 34px;
            padding: 0 12px;
            border-radius: 999px;
            border: 0;
            background: transparent;
            box-shadow: none;
            font-size: 0.96rem;
            font-weight: 800;
        }

        .workspace-utility-actions.gateway-utility .help-btn svg {
            color: inherit;
        }

        .workspace-utility-actions.gateway-utility .help-btn:hover,
        .workspace-utility-actions.gateway-utility .help-btn:focus-visible {
            color: var(--gold);
            border-color: transparent;
            background: transparent;
        }

        body.landing-theme-light .workspace-utility-actions.gateway-utility .help-btn {
            color: var(--maroon);
        }

        body.landing-theme-light .workspace-utility-actions.gateway-utility .help-btn:hover,
        body.landing-theme-light .workspace-utility-actions.gateway-utility .help-btn:focus-visible {
            color: #8f2230;
            background: transparent;
        }

        .gateway-sa-selector {
            width: min(520px, 100%);
            margin-top: 8px;
            background: rgba(255, 255, 255, 0.06);
            border: 1px solid rgba(250, 204, 21, 0.18);
            border-radius: 24px;
            padding: 22px;
            backdrop-filter: blur(10px);
            -webkit-backdrop-filter: blur(10px);
        }

        body.landing-theme-light .gateway-sa-selector {
            background: rgba(255, 255, 255, 0.7);
            border-color: rgba(112, 19, 27, 0.10);
        }

        .gateway-feature-grid {
            width: 100%;
            display: grid;
            grid-template-columns: repeat(4, minmax(0, 1fr));
            gap: 12px;
            margin-top: 4px;
            position: relative;
            z-index: 3;
        }

        .gateway-feature-card {
            position: relative;
            overflow: hidden;
            border-radius: 18px;
            padding: 14px 14px 12px;
            background: rgba(255, 255, 255, 0.08);
            border: 1px solid rgba(255, 255, 255, 0.10);
            text-align: left;
            box-shadow: inset 0 1px 0 rgba(255, 255, 255, 0.08);
        }

        body.landing-theme-light .gateway-feature-card {
            background: rgba(255, 255, 255, 0.78);
            border-color: rgba(112, 19, 27, 0.10);
        }

        .gateway-feature-card::before,
        .gateway-feature-card::after {
            content: "";
            position: absolute;
            top: 12px;
            width: 18px;
            height: 18px;
            border-radius: 50%;
            background: linear-gradient(135deg, #facc15, #ffe693);
            border: 1px solid rgba(250, 204, 21, 0.92);
            box-shadow: inset 0 1px 2px rgba(255, 255, 255, 0.24);
        }

        .gateway-feature-card::before {
            left: 12px;
        }

        .gateway-feature-card::after {
            right: 12px;
        }

        .gateway-feature-title {
            margin: 26px 0 4px;
            color: #ffffff;
            font-size: 0.98rem;
            font-weight: 900;
        }

        body.landing-theme-light .gateway-feature-title {
            color: #70131b;
        }

        .gateway-feature-copy {
            margin: 0;
            color: rgba(255, 255, 255, 0.72);
            font-size: 0.84rem;
            line-height: 1.55;
            font-weight: 600;
        }

        body.landing-theme-light .gateway-feature-copy {
            color: #64748b;
        }

        .help-panel {
            position: absolute;
            top: 50%;
            left: 50%;
            right: auto;
            bottom: auto;
            z-index: 5;
            width: min(760px, calc(100% - 44px));
            max-width: 760px;
            max-height: min(560px, calc(100vh - 96px));
            margin: 0 auto;
            opacity: 0;
            transform: translate(-50%, calc(-50% + 18px));
            pointer-events: none;
            background: transparent;
            border: 0;
            border-radius: 0;
            box-shadow: none;
            color: #1f2937;
            transition: opacity .26s ease, transform .34s ease;
            overflow: visible;
        }

        .landing-panel.is-help .help-panel {
            opacity: 1;
            transform: translate(-50%, -50%);
            pointer-events: auto;
        }

        .landing-panel.is-help .gateway-top-content {
            opacity: 0;
            transform: translateY(-10px) scale(0.985);
            pointer-events: none;
        }

        .help-panel-head {
            background: transparent;
            color: #ffffff;
            border-radius: 0;
            margin: 0;
            padding: 0 8px 12px 54px;
            text-align: left;
        }

        .help-contact-card {
            display: grid;
            gap: 6px;
            border-radius: 14px;
            border: 1px solid rgba(250, 204, 21, 0.18);
            background: rgba(255, 255, 255, 0.05);
            padding: 14px 15px;
            color: rgba(255, 255, 255, 0.82);
            font-size: 12px;
            line-height: 1.6;
        }

        .help-contact-card strong {
            color: #ffffff;
            font-size: 13px;
        }

        .help-panel-kicker {
            color: #facc15;
        }

        .help-panel-copy {
            color: rgba(255, 255, 255, 0.84);
        }

        body.landing-theme-light .help-panel-title {
            color: #111111;
        }

        body.landing-theme-light .help-panel-copy {
            color: #1f2937;
        }

        .help-guide {
            width: min(740px, 100%);
            margin: 0 auto;
            padding: 0;
        }

        .help-panel-back {
            top: -2px;
            left: 8px;
            transform: none;
        }

        .help-panel-back:hover,
        .help-panel-back:focus-visible {
            transform: translateX(-2px);
        }

        .help-panel-back {
            background: rgba(255, 255, 255, 0.10);
            color: #ffffff;
            border-color: rgba(250, 204, 21, 0.44);
        }

        .help-panel-back:hover,
        .help-panel-back:focus-visible {
            background: linear-gradient(135deg, var(--gold), var(--gold-soft));
            color: var(--maroon);
        }

        .info-column {
            display: none !important;
        }

        .landing-topbar {
            display: none !important;
        }

        .login-column {
            width: 100% !important;
            display: block !important;
            background: transparent !important;
            border: 0 !important;
        }

        .login-card {
            width: 100% !important;
            max-width: none !important;
            display: block !important;
            background: transparent !important;
            box-shadow: none !important;
            min-height: auto !important;
            padding: 0 !important;
        }

        .login-primary.gateway-stage {
            width: min(980px, 100%);
            margin: 0 auto;
            min-height: min(560px, calc(100vh - 130px));
            padding: 0 !important;
            background: transparent !important;
            box-shadow: none !important;
        }

        .gateway-brand-copy {
            width: min(960px, 100%);
            display: grid;
            justify-items: center;
            gap: 16px;
            margin-bottom: 6px;
        }

        .gateway-brand-copy h1,
        .gateway-brand-copy p {
            margin: 0;
        }

        .gateway-brand-copy .gateway-copy {
            max-width: 860px;
        }

        .login-copy.gateway-brand-copy {
            margin-bottom: 0;
        }

        .gateway-stage .workspace-entry {
            width: min(560px, 100%);
        }

        .gateway-stage .workspace-entry .portal-btn {
            width: 100%;
        }

        .gateway-feature-grid {
            max-width: 980px;
            margin-left: auto;
            margin-right: auto;
        }

        @media (max-width: 980px) {
            .landing-panel {
                min-height: auto;
                padding: 0 0 16px;
            }

            .login-primary.gateway-stage {
                width: 100%;
            }

            .gateway-title {
                white-space: normal;
            }

            .gateway-feature-grid {
                grid-template-columns: repeat(2, minmax(0, 1fr));
            }
        }

        @media (max-width: 640px) {
            .landing-theme-toggle,
            .landing-announcement-btn,
            .landing-assistant-btn {
                --landing-floating-button-size: clamp(42px, 10.5vw, 50px);
                right: clamp(10px, 3.2vw, 16px);
                min-height: var(--landing-floating-button-size);
                width: var(--landing-floating-button-size);
                height: var(--landing-floating-button-size);
                padding: 0;
                box-shadow: 0 12px 24px rgba(15, 23, 42, 0.22);
            }

            .landing-theme-toggle {
                top: 16px;
            }

            .landing-announcement-btn {
                top: calc(16px + var(--landing-floating-button-size) + 14px);
            }

            .landing-assistant-btn {
                top: calc(16px + (var(--landing-floating-button-size) * 2) + 28px);
                border-radius: clamp(12px, 3vw, 15px);
            }

            .landing-theme-toggle svg {
                width: clamp(20px, 5.4vw, 24px);
                height: clamp(20px, 5.4vw, 24px);
            }

            .landing-announcement-btn svg {
                width: clamp(24px, 6.2vw, 30px);
                height: clamp(24px, 6.2vw, 30px);
            }

            .announcement-badge {
                top: -6px;
                right: -6px;
                min-width: 20px;
                height: 20px;
                padding: 0 5px;
                font-size: 10px;
            }

            .landing-panel {
                padding: 0 0 12px;
                border-radius: 0;
            }

            .gateway-logo-card {
                width: 74px;
                height: 74px;
                border-radius: 20px;
            }

            .gateway-logo-card img {
                width: 52px;
                height: 52px;
            }

            .gateway-feature-grid {
                grid-template-columns: 1fr;
            }

            .help-panel {
                width: calc(100% - 20px);
                max-height: min(520px, calc(100vh - 72px));
            }

            .workspace-entry.gateway-actions {
                width: 100%;
            }
        }

        @media (max-width: 380px) {
            .landing-theme-toggle,
            .landing-announcement-btn,
            .landing-assistant-btn {
                --landing-floating-button-size: 40px;
                right: 8px;
            }

            .landing-theme-toggle {
                top: 12px;
            }

            .landing-announcement-btn {
                top: 64px;
            }

            .landing-assistant-btn {
                top: 116px;
                border-radius: 12px;
            }
        }

        /* Announcement Modal Styles */
        .announcement-modal-overlay {
            position: fixed;
            inset: 0;
            background: rgba(0, 0, 0, 0.4);
            backdrop-filter: blur(4px);
            z-index: 999;
            display: none;
            align-items: center;
            justify-content: flex-end;
        }

        .announcement-modal-overlay.is-open {
            display: flex;
        }

        .announcement-modal {
            position: fixed;
            right: 0;
            top: 0;
            bottom: 0;
            width: min(420px, 100%);
            background: rgba(20, 16, 20, 0.96);
            border-left: 1px solid rgba(250, 204, 21, 0.2);
            backdrop-filter: blur(18px);
            -webkit-backdrop-filter: blur(18px);
            box-shadow: -10px 0 40px rgba(15, 23, 42, 0.3);
            z-index: 1000;
            display: flex;
            flex-direction: column;
            transform: translateX(100%);
            transition: transform 0.3s ease;
            overflow: hidden;
        }

        .announcement-modal.is-open {
            transform: translateX(0);
        }

        .announcement-modal-header {
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 24px;
            background: linear-gradient(135deg, #70131b, #8f1827 58%, #a11d31);
            border-bottom: 1px solid rgba(250, 204, 21, 0.22);
            flex-shrink: 0;
            box-shadow: 0 14px 28px rgba(15, 23, 42, 0.18);
        }

        .announcement-modal-title {
            margin: 0;
            font-size: 1.2rem;
            font-weight: 900;
            color: #ffffff;
        }

        .announcement-modal-close {
            position: relative;
            overflow: hidden;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            width: 40px;
            height: 40px;
            border-radius: 999px;
            border: 1px solid rgba(250, 204, 21, 0.24);
            background: rgba(18, 12, 15, 0.32);
            color: #ffffff;
            cursor: pointer;
            box-shadow: 0 10px 20px rgba(15, 23, 42, 0.22);
            transition: transform .18s ease, color .18s ease, background .18s ease, border-color .18s ease, box-shadow .18s ease;
            z-index: 0;
        }

        .announcement-modal-close::after {
            content: "";
            position: absolute;
            top: -35%;
            left: -78%;
            width: 58%;
            height: 170%;
            background: linear-gradient(120deg, transparent 0%, rgba(255, 244, 180, .16) 34%, rgba(255, 244, 180, .62) 50%, rgba(255, 244, 180, .16) 66%, transparent 100%);
            transform: skewX(-18deg);
            transition: left .52s ease;
            pointer-events: none;
            z-index: -1;
        }

        .announcement-modal-close:hover,
        .announcement-modal-close:focus-visible {
            transform: translateY(-1px);
            background: #facc15;
            color: #70131b;
            border-color: #facc15;
            box-shadow: 0 0 0 3px rgba(250, 204, 21, 0.18), 0 14px 24px rgba(15, 23, 42, 0.2);
            outline: none;
        }

        .announcement-modal-close:hover::after,
        .announcement-modal-close:focus-visible::after {
            left: 128%;
        }

        .announcement-modal-close svg {
            position: relative;
            z-index: 1;
            width: 20px;
            height: 20px;
            stroke: currentColor;
            stroke-width: 2;
            fill: none;
        }

        .announcement-modal-content {
            flex: 1;
            min-height: 0;
            overflow-y: auto;
            padding: 24px;
            color: #cbd5e1;
            font-size: 0.95rem;
            line-height: 1.6;
            scrollbar-width: thin;
            scrollbar-color: rgba(250, 204, 21, 0.62) rgba(255, 255, 255, 0.08);
        }

        .announcement-modal-content::-webkit-scrollbar {
            width: 8px;
        }

        .announcement-modal-content::-webkit-scrollbar-track {
            background: rgba(255, 255, 255, 0.08);
        }

        .announcement-modal-content::-webkit-scrollbar-thumb {
            border-radius: 999px;
            background: rgba(250, 204, 21, 0.62);
        }

        .announcement-modal-content p {
            margin: 0 0 16px;
        }

        .announcement-modal-content p:last-child {
            margin-bottom: 0;
        }

        .landing-announcement-list {
            display: grid;
            gap: 14px;
        }

        .landing-announcement-card {
            position: relative;
            display: grid;
            gap: 9px;
            overflow: hidden;
            border-radius: 12px;
            border: 1px solid color-mix(in srgb, var(--landing-announcement-priority, #2563eb) 36%, rgba(255, 255, 255, 0.16));
            background: rgba(255, 255, 255, 0.06);
            padding: 14px 14px 14px 16px;
        }

        .landing-announcement-card::before {
            content: "";
            position: absolute;
            inset: 0 auto 0 0;
            width: 2px;
            background: var(--landing-announcement-priority, #2563eb);
        }

        .landing-announcement-meta,
        .landing-announcement-foot {
            display: flex;
            align-items: center;
            flex-wrap: wrap;
            gap: 8px;
            font-size: 11px;
            font-weight: 850;
        }

        .landing-announcement-meta {
            color: #cbd5e1;
        }

        .landing-announcement-priority {
            display: inline-flex;
            align-items: center;
            gap: 7px;
            color: var(--landing-announcement-priority, #2563eb);
            font-weight: 950;
            letter-spacing: .04em;
            text-transform: uppercase;
        }

        .landing-announcement-priority::before {
            content: "";
            width: 8px;
            height: 8px;
            border-radius: 999px;
            background: currentColor;
        }

        .landing-announcement-title {
            margin: 0;
            color: #ffffff;
            font-size: 15px;
            font-weight: 950;
            line-height: 1.3;
        }

        .landing-announcement-message {
            margin: 0;
            color: #cbd5e1;
            font-size: 13px;
            font-weight: 700;
            line-height: 1.55;
        }

        .landing-announcement-foot {
            color: #94a3b8;
        }

        .landing-announcement-foot strong {
            color: #facc15;
            text-transform: uppercase;
        }

        .landing-announcement-empty {
            border-radius: 12px;
            border: 1px dashed rgba(250, 204, 21, 0.24);
            padding: 18px;
            color: #cbd5e1;
        }

        .landing-announcement-card.priority-urgent { --landing-announcement-priority: #fb7185; }
        .landing-announcement-card.priority-info { --landing-announcement-priority: #60a5fa; }
        .landing-announcement-card.priority-warning { --landing-announcement-priority: #f59e0b; }

        body.landing-theme-light .announcement-modal {
            background: rgba(255, 255, 255, 0.96);
            border-left-color: rgba(112, 19, 27, 0.1);
            box-shadow: -10px 0 40px rgba(112, 19, 27, 0.15);
        }

        body.landing-theme-light .announcement-modal-header {
            border-bottom-color: rgba(250, 204, 21, 0.22);
        }

        body.landing-theme-light .announcement-modal-title {
            color: #ffffff;
        }

        body.landing-theme-light .announcement-modal-close {
            background: rgba(18, 12, 15, 0.32);
            color: #ffffff;
            border-color: rgba(250, 204, 21, 0.24);
        }

        body.landing-theme-light .announcement-modal-close:hover,
        body.landing-theme-light .announcement-modal-close:focus-visible {
            background: #facc15;
            color: #70131b;
            border-color: #facc15;
        }

        body.landing-theme-light .announcement-modal-content {
            color: #64748b;
        }

        body.landing-theme-light .landing-announcement-card {
            background: rgba(255, 255, 255, 0.9);
            border-color: rgba(112, 19, 27, 0.12);
        }

        body.landing-theme-light .landing-announcement-title {
            color: #111827;
        }

        body.landing-theme-light .landing-announcement-message,
        body.landing-theme-light .landing-announcement-meta {
            color: #475569;
        }

        body.landing-theme-light .landing-announcement-foot {
            color: #64748b;
        }

        body.landing-theme-light .landing-announcement-foot strong {
            color: #70131b;
        }

        body.landing-theme-light .landing-announcement-empty {
            color: #475569;
            border-color: rgba(112, 19, 27, 0.18);
        }

        .announcement-modal-overlay {
            background: rgba(4, 4, 8, 0.56);
            backdrop-filter: blur(7px);
            -webkit-backdrop-filter: blur(7px);
        }

        .announcement-modal {
            right: 18px;
            top: 14px;
            bottom: 14px;
            width: min(392px, calc(100vw - 28px));
            border-radius: 14px;
            background:
                radial-gradient(circle at 16% 0%, rgba(157, 20, 39, .28), transparent 34%),
                linear-gradient(180deg, rgba(17, 18, 26, .98), rgba(12, 14, 22, .98));
            border: 1px solid rgba(255, 255, 255, .12);
            box-shadow: -18px 24px 55px rgba(0, 0, 0, .45);
        }

        .announcement-modal-header {
            align-items: center;
            padding: 14px 14px 12px;
            background: linear-gradient(135deg, rgba(112, 19, 27, .98), rgba(61, 10, 20, .92));
            border-bottom: 1px solid rgba(255, 255, 255, .08);
            box-shadow: none;
        }

        .announcement-title-wrap {
            display: flex;
            align-items: center;
            gap: 10px;
            min-width: 0;
        }

        .announcement-title-copy {
            display: grid;
            gap: 2px;
        }

        .announcement-title-copy strong {
            color: #ffffff;
            font-size: 16px;
            line-height: 1.15;
            font-weight: 950;
        }

        .announcement-modal-title {
            font-size: 16px;
            line-height: 1.15;
        }

        .announcement-title-sub {
            color: rgba(255,255,255,.78);
            font-size: 10px;
            font-weight: 700;
        }

        .announcement-modal-title .modal-title-icon {
            width: 34px;
            height: 34px;
            border-radius: 9px;
            background: rgba(255,255,255,.13);
            border: 1px solid rgba(255,255,255,.16);
            color: #ffffff;
        }

        .announcement-modal-close {
            width: 31px;
            height: 31px;
            border-color: rgba(255,255,255,.2);
            background: rgba(255,255,255,.06);
        }

        .announcement-modal-close svg {
            width: 16px;
            height: 16px;
        }

        .announcement-modal-content {
            padding: 12px;
            background: rgba(10, 12, 18, .72);
        }

        .announcement-overview-grid {
            display: grid;
            grid-template-columns: repeat(3, minmax(0, 1fr));
            gap: 8px;
            margin-bottom: 10px;
        }

        .announcement-overview-card {
            min-height: 54px;
            display: grid;
            grid-template-columns: 26px minmax(0, 1fr);
            gap: 8px;
            align-items: center;
            padding: 9px;
            border-radius: 7px;
            background: rgba(255,255,255,.035);
            border: 1px solid rgba(255,255,255,.08);
        }

        .announcement-overview-icon,
        .landing-announcement-icon {
            display: inline-flex;
            align-items: center;
            justify-content: center;
        }

        .announcement-overview-icon {
            width: 25px;
            height: 25px;
            border-radius: 7px;
            color: var(--announcement-accent, #60a5fa);
            background: color-mix(in srgb, var(--announcement-accent, #60a5fa) 18%, transparent);
        }

        .announcement-overview-icon svg,
        .landing-announcement-icon svg {
            width: 15px;
            height: 15px;
        }

        .announcement-overview-copy span {
            display: block;
            color: #cbd5e1;
            font-size: 9px;
            font-weight: 850;
            line-height: 1;
        }

        .announcement-overview-copy strong {
            display: block;
            margin-top: 1px;
            color: #ffffff;
            font-size: 17px;
            line-height: 1;
            font-weight: 950;
        }

        .announcement-overview-copy small {
            display: block;
            margin-top: 2px;
            color: #94a3b8;
            font-size: 8px;
            font-weight: 800;
        }

        .announcement-tools {
            display: block;
            margin-bottom: 10px;
        }

        .announcement-search {
            position: relative;
        }

        .announcement-search svg {
            position: absolute;
            left: 10px;
            top: 50%;
            width: 14px;
            height: 14px;
            transform: translateY(-50%);
            color: #64748b;
        }

        .announcement-search input {
            width: 100%;
            height: 34px;
            padding: 0 10px 0 31px;
            border-radius: 7px;
            border: 1px solid rgba(255,255,255,.09);
            background: rgba(255,255,255,.035);
            color: #e5e7eb;
            font-size: 11px;
            font-weight: 750;
            outline: none;
        }

        .announcement-search input::placeholder {
            color: #64748b;
        }

        .announcement-section-head {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin: 0 2px 8px;
            color: #94a3b8;
            font-size: 10px;
            font-weight: 850;
        }

        .landing-announcement-list {
            gap: 9px;
        }

        .landing-announcement-card {
            grid-template-columns: 35px minmax(0, 1fr);
            gap: 10px;
            padding: 10px;
            border-radius: 9px;
            background: linear-gradient(180deg, rgba(255,255,255,.045), rgba(255,255,255,.025));
            border: 1px solid rgba(255,255,255,.09);
            box-shadow: inset 3px 0 0 var(--landing-announcement-priority, #60a5fa);
        }

        .landing-announcement-card::before {
            content: none;
        }

        .landing-announcement-icon {
            width: 34px;
            height: 34px;
            margin-top: 20px;
            border-radius: 8px;
            color: var(--landing-announcement-priority, #60a5fa);
            background: color-mix(in srgb, var(--landing-announcement-priority, #60a5fa) 18%, transparent);
            border: 1px solid color-mix(in srgb, var(--landing-announcement-priority, #60a5fa) 28%, transparent);
        }

        .landing-announcement-body {
            min-width: 0;
            display: grid;
            gap: 9px;
        }

        .landing-announcement-meta {
            justify-content: space-between;
            gap: 6px;
            color: #94a3b8;
            font-size: 9px;
        }

        .landing-announcement-meta-left,
        .landing-announcement-meta-right {
            display: inline-flex;
            align-items: center;
            min-width: 0;
            gap: 6px;
        }

        .landing-announcement-priority {
            padding: 2px 5px;
            border-radius: 4px;
            background: color-mix(in srgb, var(--landing-announcement-priority, #60a5fa) 18%, transparent);
            font-size: 9px;
        }

        .landing-announcement-priority::before {
            content: none;
        }

        .landing-announcement-title {
            font-size: 14px;
        }

        .landing-announcement-message {
            display: -webkit-box;
            -webkit-line-clamp: 3;
            -webkit-box-orient: vertical;
            overflow: hidden;
            color: #cbd5e1;
            font-size: 12px;
            line-height: 1.55;
        }

        .landing-announcement-foot {
            justify-content: space-between;
            margin-top: 8px;
            font-size: 10px;
        }

        .landing-announcement-read {
            min-height: 23px;
            padding: 0 9px;
            border-radius: 5px;
            border: 1px solid rgba(239, 68, 68, .36);
            background: rgba(127, 29, 45, .16);
            color: #fda4af;
            font-size: 10px;
            font-weight: 900;
        }

        .landing-announcement-card.priority-urgent { --landing-announcement-priority: #ef4444; }
        .landing-announcement-card.priority-info { --landing-announcement-priority: #3b82f6; }
        .landing-announcement-card.priority-warning { --landing-announcement-priority: #eab308; }
        .landing-announcement-card.priority-health { --landing-announcement-priority: #22c55e; }
        .landing-announcement-card.priority-event { --landing-announcement-priority: #a855f7; }

        .announcement-view-all {
            width: 100%;
            margin-top: 12px;
            border: 0;
            background: transparent;
            color: #f87171;
            font-size: 11px;
            font-weight: 900;
        }

        body.landing-theme-light .announcement-modal {
            background:
                radial-gradient(circle at 16% 0%, rgba(157, 20, 39, .12), transparent 34%),
                linear-gradient(180deg, rgba(255,255,255,.98), rgba(248,250,252,.98));
        }

        .announcement-modal-content {
            display: flex;
            flex-direction: column;
        }

        body.landing-theme-light .announcement-modal-content {
            color: #475569;
            background: transparent;
        }

        body.landing-theme-light .announcement-overview-card {
            background: rgba(255, 255, 255, .92);
            border-color: rgba(112, 19, 27, .12);
        }

        body.landing-theme-light .announcement-overview-copy span,
        body.landing-theme-light .announcement-section-head {
            color: #64748b;
        }

        body.landing-theme-light .announcement-overview-copy strong {
            color: #111827;
        }

        body.landing-theme-light .announcement-overview-copy small {
            color: #64748b;
        }

        body.landing-theme-light .announcement-search input {
            background: rgba(255, 255, 255, .92);
            border-color: rgba(112, 19, 27, .14);
            color: #334155;
        }

        body.landing-theme-light .announcement-search input::placeholder {
            color: #64748b;
        }

        .announcement-title-copy strong {
            font-size: 18px;
        }

        .announcement-title-sub {
            font-size: 11px;
        }

        .announcement-overview-copy span,
        .announcement-section-head {
            font-size: 11px;
        }

        .announcement-overview-copy strong {
            font-size: 20px;
        }

        .announcement-overview-copy small,
        .landing-announcement-meta,
        .landing-announcement-foot {
            font-size: 10px;
        }

        .announcement-search input {
            font-size: 12px;
        }

        .landing-announcement-list {
            flex: 1 1 auto;
            min-height: 0;
            overflow-y: auto;
            padding-right: 2px;
            align-content: start;
        }

        .landing-announcement-card {
            flex-shrink: 0;
            min-height: 124px;
            padding: 10px;
            gap: 10px;
        }

        body.landing-theme-light .landing-announcement-card {
            background: rgba(255, 255, 255, .96);
            border-color: rgba(112, 19, 27, .12);
        }

        .landing-announcement-card.is-expanded .landing-announcement-message {
            display: block;
            -webkit-line-clamp: unset;
            overflow: visible;
        }

        .landing-announcement-card.is-hidden-by-limit {
            display: none;
        }

        .landing-announcement-empty.is-search-empty {
            grid-column: 1 / -1;
            margin-top: 0;
        }

        .landing-announcement-title {
            font-size: 14px;
        }

        .landing-announcement-message {
            font-size: 12px;
            -webkit-line-clamp: 2;
        }

        body.landing-theme-light .landing-announcement-title {
            color: #111827;
        }

        body.landing-theme-light .landing-announcement-message {
            color: #475569;
        }

        body.landing-theme-light .landing-announcement-meta,
        body.landing-theme-light .landing-announcement-foot {
            color: #64748b;
        }

        .landing-announcement-read {
            font-size: 10px;
        }

        .announcement-view-all {
            position: sticky;
            bottom: 0;
            flex: 0 0 auto;
            min-height: 42px;
            margin: 10px -12px -12px;
            background: linear-gradient(180deg, rgba(12,14,22,.72), rgba(12,14,22,.98));
            border-top: 1px solid rgba(255,255,255,.08);
            z-index: 3;
        }

        body.landing-theme-light .announcement-view-all {
            background: linear-gradient(180deg, rgba(255,255,255,.76), rgba(255,255,255,.98));
        }

        .announcement-modal.is-view-all {
            width: min(790px, calc(100vw - 28px));
        }

        .announcement-modal.is-view-all .landing-announcement-list {
            grid-template-columns: repeat(2, minmax(0, 1fr));
            gap: 12px;
        }

        .announcement-modal.is-view-all .landing-announcement-card {
            min-height: 140px;
        }

        @media (max-width: 720px) {
            .announcement-modal.is-view-all .landing-announcement-list {
                grid-template-columns: 1fr;
            }
        }

        .assistant-modal-overlay {
            justify-content: center;
            padding: 18px;
            background: rgba(5, 7, 12, .58);
            backdrop-filter: blur(9px);
            -webkit-backdrop-filter: blur(9px);
        }

        .assistant-modal {
            width: min(660px, calc(100vw - 34px));
            max-height: min(570px, calc(100vh - 34px));
            min-height: 0;
            border-radius: 14px;
            background: #ffffff;
            border: 1px solid rgba(255,255,255,.28);
            box-shadow: 0 26px 75px rgba(0,0,0,.38);
        }

        .assistant-modal-header {
            min-height: 86px;
            padding: 18px 22px;
            background:
                radial-gradient(circle at 28% 10%, rgba(255,255,255,.12), transparent 34%),
                linear-gradient(135deg, #8f1024, #a1142b 52%, #70131b);
            border-bottom: 1px solid rgba(112, 19, 27, .12);
            box-shadow: none;
        }

        .assistant-title-wrap {
            display: flex;
            align-items: center;
            gap: 14px;
        }

        .assistant-title-copy {
            display: grid;
            gap: 3px;
        }

        .assistant-title-copy strong {
            color: #ffffff;
            font-size: 23px;
            line-height: 1;
            font-weight: 950;
        }

        .assistant-title-copy span {
            color: #facc15;
            font-size: 12px;
            font-weight: 900;
        }

        .assistant-modal-title {
            font-size: 0;
            gap: 0;
        }

        .modal-title-icon.is-assistant {
            width: 52px;
            height: 52px;
            flex-basis: 52px;
            border-radius: 14px;
            border: 1px solid rgba(255,255,255,.18);
            box-shadow: inset 0 1px 0 rgba(255,255,255,.18), 0 16px 26px rgba(49,4,14,.2);
        }

        .modal-title-icon.is-assistant svg {
            width: 52px;
            height: 52px;
        }

        .assistant-modal-close {
            width: 44px;
            height: 44px;
            background: rgba(112, 19, 27, .34);
            border-color: rgba(250,204,21,.46);
        }

        .assistant-modal-content {
            padding: 28px 26px 18px;
            overflow-y: auto;
            color: #1f2937;
            background:
                radial-gradient(circle at 15% 28%, rgba(252, 231, 235, .86), transparent 26%),
                linear-gradient(180deg, #ffffff, #fffafa);
        }

        .assistant-coming-grid {
            display: grid;
            grid-template-columns: minmax(220px, .9fr) minmax(240px, 1fr);
            gap: 26px;
            align-items: center;
        }

        .assistant-robot-scene {
            position: relative;
            min-height: 210px;
            display: flex;
            align-items: end;
            justify-content: center;
        }

        .assistant-robot-halo {
            position: absolute;
            width: 260px;
            height: 166px;
            border-radius: 50%;
            background: #fae7eb;
            opacity: .82;
            bottom: 8px;
            left: 50%;
            transform: translateX(-50%);
        }

        .assistant-robot-art {
            position: relative;
            width: 205px;
            height: 210px;
            z-index: 1;
        }

        .assistant-robot-head {
            position: absolute;
            left: 40px;
            top: 63px;
            width: 126px;
            height: 86px;
            border-radius: 42px;
            background: linear-gradient(145deg, #ffffff, #f3f4f6);
            box-shadow: 0 18px 26px rgba(112,19,27,.18);
        }

        .assistant-robot-face {
            position: absolute;
            left: 18px;
            right: 18px;
            top: 24px;
            height: 40px;
            border-radius: 20px;
            background: linear-gradient(145deg, #2a0612, #5a1022);
        }

        .assistant-robot-face::before,
        .assistant-robot-face::after {
            content: "";
            position: absolute;
            top: 15px;
            width: 13px;
            height: 10px;
            border-radius: 0 0 999px 999px;
            border-bottom: 5px solid #ffffff;
        }

        .assistant-robot-face::before { left: 22px; }
        .assistant-robot-face::after { right: 22px; }

        .assistant-robot-smile {
            position: absolute;
            left: 50%;
            top: 48px;
            width: 24px;
            height: 12px;
            transform: translateX(-50%);
            border-bottom: 5px solid #ffffff;
            border-radius: 0 0 999px 999px;
        }

        .assistant-robot-ear {
            position: absolute;
            top: 93px;
            width: 20px;
            height: 43px;
            border-radius: 14px;
            background: #ffffff;
            box-shadow: 0 10px 18px rgba(112,19,27,.12);
        }

        .assistant-robot-ear.left { left: 23px; }
        .assistant-robot-ear.right { right: 23px; }

        .assistant-robot-body {
            position: absolute;
            left: 57px;
            bottom: 9px;
            width: 94px;
            height: 74px;
            border-radius: 35px 35px 20px 20px;
            background: #ffffff;
            box-shadow: 0 16px 22px rgba(112,19,27,.13);
        }

        .assistant-robot-antenna {
            position: absolute;
            left: 101px;
            top: 22px;
            width: 7px;
            height: 44px;
            background: #d1d5db;
            border-radius: 99px;
        }

        .assistant-robot-antenna::before {
            content: "";
            position: absolute;
            left: 50%;
            top: -12px;
            width: 22px;
            height: 22px;
            transform: translateX(-50%);
            border-radius: 999px;
            background: linear-gradient(145deg, #ffffff, #d9dee7);
            box-shadow: 0 8px 13px rgba(0,0,0,.12);
        }

        .assistant-robot-wave {
            position: absolute;
            right: 19px;
            bottom: 62px;
            width: 31px;
            height: 62px;
            border-radius: 99px;
            border-right: 12px solid #ffffff;
            transform: rotate(-27deg);
        }

        .assistant-stethoscope {
            position: absolute;
            left: 72px;
            bottom: 22px;
            width: 66px;
            height: 44px;
            border: 5px solid #7f1023;
            border-top: 0;
            border-radius: 0 0 28px 28px;
        }

        .assistant-stethoscope::before,
        .assistant-stethoscope::after {
            content: "";
            position: absolute;
            bottom: -9px;
            width: 16px;
            height: 16px;
            border-radius: 999px;
            border: 5px solid #7f1023;
            background: #ffffff;
        }

        .assistant-stethoscope::before { left: -10px; }
        .assistant-stethoscope::after { right: -10px; }

        .assistant-speech-bubble {
            position: absolute;
            right: 6px;
            top: 28px;
            width: 112px;
            height: 112px;
            display: grid;
            place-items: center;
            padding: 14px;
            border-radius: 999px;
            background: #ffffff;
            border: 2px solid #8f1024;
            color: #4b1020;
            font-size: 12px;
            font-weight: 900;
            text-align: center;
            line-height: 1.25;
            z-index: 2;
            transform-origin: 18px 54px;
            transition: opacity .28s ease, transform .28s cubic-bezier(.2, .9, .2, 1);
        }

        .assistant-speech-bubble.is-changing {
            opacity: 0;
            transform: translateY(8px) scale(.9);
        }

        .assistant-speech-bubble::after {
            content: "";
            position: absolute;
            left: -12px;
            top: 42px;
            width: 22px;
            height: 22px;
            background: #ffffff;
            border-left: 2px solid #8f1024;
            border-bottom: 2px solid #8f1024;
            transform: rotate(45deg);
        }

        .assistant-speech-bubble-text {
            position: relative;
            z-index: 1;
        }

        .assistant-speech-bubble span {
            display: block;
            color: #8f1024;
            font-size: 17px;
            line-height: 1;
            margin-top: 4px;
        }

        .assistant-plus-mark {
            position: absolute;
            color: #e6a8b3;
            font-size: 24px;
            font-weight: 700;
        }

        .assistant-plus-mark.one { left: 8px; top: 62px; }
        .assistant-plus-mark.two { right: 16px; bottom: 24px; }

        .assistant-reference-icon {
            position: relative;
            z-index: 1;
            width: min(185px, 66%);
            height: auto;
            object-fit: contain;
            border-radius: 0;
            filter: drop-shadow(0 18px 26px rgba(112, 19, 27, .2));
            animation: assistantIconFloat 3.4s ease-in-out infinite;
        }

        .assistant-robot-scene .assistant-robot-art {
            display: none;
        }

        .assistant-reference-icon .chat-bubble {
            transform-origin: 96px 38px;
            animation: assistantBubblePop 2.8s cubic-bezier(.2, .9, .2, 1) infinite;
        }

        @keyframes assistantIconFloat {
            0%, 100% { transform: translateY(0); }
            50% { transform: translateY(-8px); }
        }

        @keyframes assistantBubblePop {
            0%, 100% { transform: scale(1); }
            45% { transform: scale(1.06); }
        }

        .assistant-coming-copy h3 {
            margin: 0;
            color: #7f1023;
            font-size: 30px;
            line-height: 1.05;
            font-weight: 950;
        }

        .assistant-coming-label {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            margin: 10px 0 16px;
            color: #d08a18;
            font-size: 13px;
            font-weight: 950;
        }

        .assistant-coming-copy p {
            margin: 0 0 14px;
            color: #374151;
            font-size: 14px;
            line-height: 1.55;
            font-weight: 700;
        }

        .assistant-patience {
            display: flex;
            align-items: flex-start;
            gap: 10px;
            color: #4b5563;
            font-size: 14px;
            font-weight: 750;
        }

        .assistant-patience span {
            color: #e5a2ad;
            font-size: 18px;
        }

        .assistant-features {
            margin-top: 18px;
            padding: 14px 14px 16px;
            border-radius: 10px;
            border: 1px solid #f4d6dc;
            background: rgba(255, 247, 249, .92);
        }

        .assistant-features-title {
            display: flex;
            align-items: center;
            gap: 12px;
            margin-bottom: 13px;
            color: #7f1023;
            font-size: 12px;
            font-weight: 950;
            text-align: center;
            text-transform: uppercase;
        }

        .assistant-features-title::before,
        .assistant-features-title::after {
            content: "";
            height: 1px;
            flex: 1;
            background: #efd3d9;
        }

        .assistant-feature-grid {
            display: grid;
            grid-template-columns: repeat(4, minmax(0, 1fr));
            gap: 12px;
        }

        .assistant-feature {
            display: grid;
            justify-items: center;
            gap: 6px;
            text-align: center;
            color: #374151;
            font-size: 10px;
            line-height: 1.3;
            font-weight: 700;
        }

        .assistant-feature-icon {
            width: 40px;
            height: 40px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            border-radius: 9px;
            background: #f8e5e9;
            color: #8f1024;
        }

        .assistant-feature-icon svg {
            width: 21px;
            height: 21px;
        }

        .assistant-feature strong {
            display: block;
            color: #111827;
            font-size: 11px;
            font-weight: 950;
        }

        .assistant-notify {
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 18px;
            margin-top: 10px;
            padding: 12px;
            border-radius: 10px;
            background: #fff0f3;
            color: #8f1024;
            font-size: 13px;
            font-weight: 850;
        }

        @media (max-width: 720px) {
            .assistant-coming-grid {
                grid-template-columns: 1fr;
                gap: 12px;
            }

            .assistant-feature-grid {
                grid-template-columns: repeat(2, minmax(0, 1fr));
            }

            .assistant-modal {
                max-height: calc(100vh - 24px);
            }
        }

        .announcement-title-copy strong {
            font-size: 20px;
        }

        .announcement-title-sub {
            font-size: 13px;
        }

        .announcement-overview-copy span,
        .announcement-section-head {
            font-size: 13px;
        }

        .announcement-overview-copy strong {
            font-size: 24px;
        }

        .announcement-overview-copy small,
        .landing-announcement-meta,
        .landing-announcement-foot {
            font-size: 12px;
        }

        .announcement-search input {
            font-size: 14px;
        }

        .landing-announcement-title {
            font-size: 16px;
            line-height: 1.25;
        }

        .landing-announcement-message {
            font-size: 14px;
            line-height: 1.5;
        }

        .landing-announcement-read,
        .announcement-view-all {
            font-size: 12px;
        }

        .assistant-modal-overlay {
            justify-content: flex-end;
            align-items: stretch;
            padding: 14px 14px 14px 0;
        }

        .assistant-modal {
            width: min(420px, calc(100vw - 28px));
            height: calc(100vh - 28px);
            max-height: none;
            transform: translateX(112%);
            opacity: 1;
            transition: transform .28s ease;
        }

        .assistant-modal.is-open {
            transform: translateX(0);
            opacity: 1;
        }

        .assistant-modal-header {
            min-height: 74px;
            padding: 14px 16px;
        }

        .assistant-title-wrap {
            gap: 10px;
        }

        .assistant-title-copy strong {
            font-size: 18px;
        }

        .assistant-title-copy span {
            font-size: 11px;
        }

        .modal-title-icon.is-assistant {
            width: 42px;
            height: 42px;
            flex-basis: 42px;
            border-radius: 11px;
        }

        .modal-title-icon.is-assistant svg {
            width: 42px;
            height: 42px;
        }

        .assistant-modal-close {
            width: 34px;
            height: 34px;
        }

        .assistant-modal-content {
            padding: 14px;
        }

        .assistant-coming-grid {
            grid-template-columns: 1fr;
            gap: 10px;
        }

        .assistant-robot-scene {
            min-height: 150px;
            transform: scale(.78);
            transform-origin: center bottom;
            margin: -28px 0 -16px;
        }

        .assistant-coming-copy h3 {
            font-size: 24px;
        }

        .assistant-coming-label {
            margin: 7px 0 10px;
            font-size: 11px;
        }

        .assistant-coming-copy p,
        .assistant-patience {
            font-size: 12px;
        }

        .assistant-features {
            margin-top: 12px;
            padding: 11px;
        }

        .assistant-feature-grid {
            grid-template-columns: repeat(2, minmax(0, 1fr));
            gap: 10px;
        }

        .assistant-feature-icon {
            width: 36px;
            height: 36px;
        }

        .assistant-notify {
            gap: 10px;
            padding: 10px;
            font-size: 11px;
            flex-wrap: wrap;
        }
    </style>
</head>
<body>
    @php
        $landingAdminUser = Auth::guard('admin')->user();
        $landingStudentUser = Auth::guard('student')->user();
        $landingStudentUserType = strtolower(trim((string) ($landingStudentUser->user_type ?? '')));
        $landingStudentRawRole = strtolower(trim((string) ($landingStudentUser->user_role ?? '')));
        $landingStudentNormalizedRole = \App\Models\User::normalizeRole($landingStudentRawRole);
        $landingIsStudentAssistant = $landingStudentUser
            && $landingStudentNormalizedRole === \App\Models\User::ROLE_ADMIN
            && (
                in_array($landingStudentUserType, ['assistant', 'student assistant', 'student_assistant'], true)
                || in_array($landingStudentRawRole, ['student_assistant', 'studentassistant', 'assistant'], true)
            );
        $landingWorkflow = app(\App\Services\ClinicWorkflowService::class);
        $saAdminWorkspaceAvailable = $landingWorkflow->studentAssistantWorkspaceAvailable();
        $saAdminWorkspaceHoursLabel = $landingWorkflow->studentAssistantHoursLabel();

        if ($landingAdminUser) {
            $workspaceHref = url('/admin/dashboard');
        } elseif ($landingIsStudentAssistant) {
            $workspaceHref = url('/student/home?workspace=sa');
        } else {
            $workspaceHref = url('/student/home');
        }
    @endphp

    <!-- Full-Screen Preloader -->
    <div id="preloader">
        <div class="preloader-logo">
            <img src="{{ asset('images/clinic_logo_transparent.png') }}" alt="Clinic Logo">
        </div>
    </div>
    <main class="landing-shell">
        <button type="button" class="landing-theme-toggle" id="landingThemeToggle" aria-pressed="false" aria-label="Switch to light mode" title="Switch theme">
            <svg id="landingThemeToggleIcon" viewBox="0 0 24 24" aria-hidden="true">
                <path d="M21 12.8A9 9 0 1 1 11.2 3a7 7 0 0 0 9.8 9.8Z" stroke-linecap="round" stroke-linejoin="round"></path>
            </svg>
        </button>
        <button type="button" class="landing-announcement-btn" id="announcementBtn" aria-label="Announcements" title="Announcements">
            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" aria-hidden="true">
                <path stroke-linecap="round" stroke-linejoin="round" d="M10.34 15.84c-.688-.06-1.386-.09-2.09-.09H7.5a4.5 4.5 0 1 1 0-9h.75c.704 0 1.402-.03 2.09-.09m0 9.18c.253.962.584 1.892.985 2.783.247.55.06 1.21-.463 1.511l-.657.38c-.551.318-1.26.117-1.527-.461a20.845 20.845 0 0 1-1.44-4.282m3.102.069a18.03 18.03 0 0 1-.59-4.59c0-1.586.205-3.124.59-4.59m0 9.18a23.848 23.848 0 0 1 8.835 2.535M10.34 6.66a23.847 23.847 0 0 0 8.835-2.535m0 0A23.74 23.74 0 0 0 18.795 3m.38 1.125a23.91 23.91 0 0 1 1.014 5.395m-1.014 8.855c-.118.38-.245.754-.38 1.125m.38-1.125a23.91 23.91 0 0 0 1.014-5.395m0-3.46c.495.413.811 1.035.811 1.73 0 .695-.316 1.317-.811 1.73m0-3.46a24.347 24.347 0 0 1 0 3.46" />
            </svg>
            <span class="announcement-badge" id="announcementBadge" style="display: none;">1</span>
        </button>

        <button type="button" class="landing-assistant-btn" id="assistantBtn" aria-label="AI Chatbot" title="AI Chatbot">
            <svg viewBox="0 0 128 128" aria-hidden="true">
                <defs>
                    <linearGradient id="assistantIconBg" x1="22" y1="12" x2="105" y2="118" gradientUnits="userSpaceOnUse">
                        <stop offset="0" stop-color="#a5162c" />
                        <stop offset="1" stop-color="#6f0f1d" />
                    </linearGradient>
                    <linearGradient id="assistantIconFace" x1="36" y1="54" x2="85" y2="81" gradientUnits="userSpaceOnUse">
                        <stop offset="0" stop-color="#4a1020" />
                        <stop offset="1" stop-color="#220812" />
                    </linearGradient>
                    <linearGradient id="assistantIconWhite" x1="26" y1="24" x2="95" y2="111" gradientUnits="userSpaceOnUse">
                        <stop offset="0" stop-color="#ffffff" />
                        <stop offset="1" stop-color="#f5eff0" />
                    </linearGradient>
                </defs>
                <rect width="128" height="128" rx="30" fill="url(#assistantIconBg)" />
                <g class="assistant-icon-shadow">
                    <path d="M38 92c0-13 11-24 25-24h2c14 0 25 11 25 24v12c0 6-5 11-11 11H49c-6 0-11-5-11-11V92Z" fill="url(#assistantIconWhite)" />
                    <path d="M40 63h-6c-6 0-11 5-11 11v10c0 6 5 11 11 11h6V63Z" fill="url(#assistantIconWhite)" />
                    <path d="M88 63h6c6 0 11 5 11 11v10c0 6-5 11-11 11h-6V63Z" fill="url(#assistantIconWhite)" />
                    <path d="M63 35h4v17h-4V35Z" fill="url(#assistantIconWhite)" />
                    <circle cx="65" cy="31" r="10" fill="url(#assistantIconWhite)" />
                    <rect x="30" y="42" width="70" height="52" rx="23" fill="url(#assistantIconWhite)" />
                    <rect x="38" y="55" width="54" height="28" rx="14" fill="url(#assistantIconFace)" />
                    <ellipse cx="51" cy="69" rx="5.2" ry="7.2" fill="#ffffff" />
                    <ellipse cx="79" cy="69" rx="5.2" ry="7.2" fill="#ffffff" />
                    <path d="M58 77c4 4 10 4 14 0" stroke="#ffffff" stroke-width="4" stroke-linecap="round" />
                    <path d="M47 91c-5 5-7 12-5 20" stroke="#7b1020" stroke-width="4" stroke-linecap="round" fill="none" />
                    <path d="M83 91c5 5 7 12 5 20" stroke="#7b1020" stroke-width="4" stroke-linecap="round" fill="none" />
                    <circle cx="51" cy="105" r="8" fill="none" stroke="#7b1020" stroke-width="4" />
                    <path d="M87 105c3-9 10-13 17-10 8 3 10 11 7 20" fill="none" stroke="#7b1020" stroke-width="5" stroke-linecap="round" />
                    <circle cx="106" cy="115" r="3" fill="#7b1020" />
                    <circle cx="88" cy="115" r="3" fill="#7b1020" />
                    <path d="M83 21h25c8 0 14 6 14 14v10c0 8-6 14-14 14H96l-10 10v-10h-3c-8 0-14-6-14-14V35c0-8 6-14 14-14Z" fill="#ffffff" />
                    <circle cx="89" cy="40" r="4" fill="#871224" />
                    <circle cx="98" cy="40" r="4" fill="#871224" />
                    <circle cx="107" cy="40" r="4" fill="#871224" />
                </g>
            </svg>
        </button>

        <!-- Announcement Modal -->
        <div class="announcement-modal-overlay" id="announcementModalOverlay">
            <div class="announcement-modal" id="announcementModal">
                <div class="announcement-modal-header">
                    <div class="announcement-title-wrap">
                        <h2 class="announcement-modal-title">
                            <span class="modal-title-icon" aria-hidden="true">
                                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M10.34 15.84c-.688-.06-1.386-.09-2.09-.09H7.5a4.5 4.5 0 1 1 0-9h.75c.704 0 1.402-.03 2.09-.09m0 9.18c.253.962.584 1.892.985 2.783.247.55.06 1.21-.463 1.511l-.657.38c-.551.318-1.26.117-1.527-.461a20.845 20.845 0 0 1-1.44-4.282m3.102.069a18.03 18.03 0 0 1-.59-4.59c0-1.586.205-3.124.59-4.59m0 9.18a23.848 23.848 0 0 1 8.835 2.535M10.34 6.66a23.847 23.847 0 0 0 8.835-2.535m0 0A23.74 23.74 0 0 0 18.795 3m.38 1.125a23.91 23.91 0 0 1 1.014 5.395m-1.014 8.855c-.118.38-.245.754-.38 1.125m.38-1.125a23.91 23.91 0 0 0 1.014-5.395m0-3.46c.495.413.811 1.035.811 1.73 0 .695-.316 1.317-.811 1.73m0-3.46a24.347 24.347 0 0 1 0 3.46" />
                                </svg>
                            </span>
                        </h2>
                        <div class="announcement-title-copy">
                            <strong>Announcements</strong>
                            <span class="announcement-title-sub">Stay updated with the latest clinic updates.</span>
                        </div>
                    </div>
                    <button type="button" class="announcement-modal-close" id="announcementModalClose" aria-label="Close announcements">
                        <svg viewBox="0 0 24 24" aria-hidden="true">
                            <path d="M18 6L6 18M6 6l12 12" stroke-linecap="round" stroke-linejoin="round"></path>
                        </svg>
                    </button>
                </div>
                <div class="announcement-modal-content">
                    @php
                        $landingAnnouncementItems = ($landingAnnouncements ?? collect())
                            ->sortByDesc(fn ($announcement) => $announcement->created_at ?? $announcement->id)
                            ->values();
                        $landingPriorityLabels = [
                            'urgent' => 'Urgent',
                            'info' => 'Info',
                            'warning' => 'Notice',
                            'health' => 'Health',
                            'event' => 'Event',
                        ];
                        $landingTotalAnnouncements = $landingAnnouncementItems->count();
                        $landingLatestCount = $landingAnnouncementItems
                            ->filter(fn ($announcement) => $announcement->created_at && $announcement->created_at->gte(now()->subDays(3)))
                            ->count();
                        $landingEventCount = $landingAnnouncementItems->filter(fn ($announcement) => ($announcement->priority ?: 'info') === 'event')->count();
                    @endphp

                    <div class="announcement-overview-grid">
                        <div class="announcement-overview-card" style="--announcement-accent:#3b82f6">
                            <span class="announcement-overview-icon" aria-hidden="true">
                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path d="M12 16v-4"/><path d="M12 8h.01"/><path d="M12 21a9 9 0 1 0 0-18 9 9 0 0 0 0 18Z"/></svg>
                            </span>
                            <span class="announcement-overview-copy"><span>Latest</span><strong>{{ $landingLatestCount }}</strong><small>new posts</small></span>
                        </div>
                        <div class="announcement-overview-card" style="--announcement-accent:#f59e0b">
                            <span class="announcement-overview-icon" aria-hidden="true">
                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path d="M4 7h16v10H4z"/><path d="m4 8 8 5 8-5"/></svg>
                            </span>
                            <span class="announcement-overview-copy"><span>All</span><strong>{{ $landingTotalAnnouncements }}</strong><small>updates</small></span>
                        </div>
                        <div class="announcement-overview-card" style="--announcement-accent:#a855f7">
                            <span class="announcement-overview-icon" aria-hidden="true">
                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path d="M7 3v3M17 3v3M4 9h16M6 5h12a2 2 0 0 1 2 2v13H4V7a2 2 0 0 1 2-2z"/><path d="M8 13h3M8 17h6"/></svg>
                            </span>
                            <span class="announcement-overview-copy"><span>Events</span><strong>{{ $landingEventCount }}</strong><small>updates</small></span>
                        </div>
                    </div>

                    <div class="announcement-tools">
                        <label class="announcement-search">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true"><path d="m21 21-4.3-4.3"/><path d="M10.8 18a7.2 7.2 0 1 1 0-14.4 7.2 7.2 0 0 1 0 14.4Z"/></svg>
                            <input type="search" id="announcementSearchInput" placeholder="Search announcements...">
                        </label>
                    </div>

                    @if($landingAnnouncementItems->isNotEmpty())
                        <div class="announcement-section-head">
                            <span data-announcement-section-title>Latest Announcements</span>
                            <span>{{ $landingTotalAnnouncements }} of {{ $landingTotalAnnouncements }}</span>
                        </div>
                        <div class="landing-announcement-list">
                            @foreach($landingAnnouncementItems as $announcementIndex => $announcement)
                                @php
                                    $priority = $announcement->priority ?: 'info';
                                    $priorityClass = in_array($priority, ['urgent', 'info', 'warning', 'health', 'event'], true) ? $priority : 'info';
                                @endphp
                                <article class="landing-announcement-card priority-{{ $priorityClass }}" data-announcement-card data-announcement-index="{{ $announcementIndex }}" data-priority="{{ $priorityClass }}" data-search="{{ \Illuminate\Support\Str::lower($announcement->title . ' ' . $announcement->message) }}">
                                    <span class="landing-announcement-icon" aria-hidden="true">
                                        @if($priorityClass === 'urgent')
                                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path d="m12 3 9 16H3L12 3Z"/><path d="M12 9v4"/><path d="M12 17h.01"/></svg>
                                        @elseif($priorityClass === 'warning')
                                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path d="M12 9v4"/><path d="M12 17h.01"/><path d="M10.3 4.3 2.5 18a2 2 0 0 0 1.7 3h15.6a2 2 0 0 0 1.7-3L13.7 4.3a2 2 0 0 0-3.4 0Z"/></svg>
                                        @else
                                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path d="M12 16v-4"/><path d="M12 8h.01"/><path d="M12 21a9 9 0 1 0 0-18 9 9 0 0 0 0 18Z"/></svg>
                                        @endif
                                    </span>
                                    <div class="landing-announcement-body">
                                        <div class="landing-announcement-meta">
                                            <span class="landing-announcement-meta-left">
                                                <span class="landing-announcement-priority">{{ $landingPriorityLabels[$priorityClass] ?? ucfirst($priorityClass) }}</span>
                                            </span>
                                            <span class="landing-announcement-meta-right">{{ $announcement->created_at?->format('M j, Y · g:i A') ?? 'Just now' }}</span>
                                        </div>
                                        <h3 class="landing-announcement-title">{{ $announcement->title }}</h3>
                                        <p class="landing-announcement-message">{{ $announcement->message }}</p>
                                        <div class="landing-announcement-foot">
                                            <span>Target: {{ strtoupper($announcement->target_audience ?? 'All Users') }}</span>
                                            <button type="button" class="landing-announcement-read" data-announcement-read>Read More →</button>
                                        </div>
                                    </div>
                                </article>
                            @endforeach
                            <div class="landing-announcement-empty is-search-empty" data-announcement-empty hidden>
                                <p><strong>No announcement posted...</strong></p>
                                <p>No clinic announcements matched your search.</p>
                            </div>
                        </div>
                        <button type="button" class="announcement-view-all">View All Announcements →</button>
                    @else
                    <div class="landing-announcement-empty">
                        <p><strong>No announcements at the moment.</strong></p>
                        <p>Please check back later for clinic advisories, schedule updates, and system notices.</p>
                    </div>
                    @endif
                </div>
            </div>
        </div>

        <!-- AI Chatbot Modal -->
        <div class="assistant-modal-overlay" id="assistantModalOverlay">
            <div class="assistant-modal" id="assistantModal">
                <div class="assistant-modal-header">
                    <div class="assistant-title-wrap">
                        <h2 class="assistant-modal-title">
                            <span class="modal-title-icon is-assistant" aria-hidden="true">
                            <svg viewBox="0 0 128 128">
                                <defs>
                                    <linearGradient id="assistantHeaderIconBg" x1="22" y1="12" x2="105" y2="118" gradientUnits="userSpaceOnUse">
                                        <stop offset="0" stop-color="#a5162c" />
                                        <stop offset="1" stop-color="#6f0f1d" />
                                    </linearGradient>
                                    <linearGradient id="assistantHeaderIconFace" x1="36" y1="54" x2="85" y2="81" gradientUnits="userSpaceOnUse">
                                        <stop offset="0" stop-color="#4a1020" />
                                        <stop offset="1" stop-color="#220812" />
                                    </linearGradient>
                                    <linearGradient id="assistantHeaderIconWhite" x1="26" y1="24" x2="95" y2="111" gradientUnits="userSpaceOnUse">
                                        <stop offset="0" stop-color="#ffffff" />
                                        <stop offset="1" stop-color="#f5eff0" />
                                    </linearGradient>
                                </defs>
                                <rect width="128" height="128" rx="30" fill="url(#assistantHeaderIconBg)" />
                                <path d="M38 92c0-13 11-24 25-24h2c14 0 25 11 25 24v12c0 6-5 11-11 11H49c-6 0-11-5-11-11V92Z" fill="url(#assistantHeaderIconWhite)" />
                                <path d="M40 63h-6c-6 0-11 5-11 11v10c0 6 5 11 11 11h6V63Z" fill="url(#assistantHeaderIconWhite)" />
                                <path d="M88 63h6c6 0 11 5 11 11v10c0 6-5 11-11 11h-6V63Z" fill="url(#assistantHeaderIconWhite)" />
                                <path d="M63 35h4v17h-4V35Z" fill="url(#assistantHeaderIconWhite)" />
                                <circle cx="65" cy="31" r="10" fill="url(#assistantHeaderIconWhite)" />
                                <rect x="30" y="42" width="70" height="52" rx="23" fill="url(#assistantHeaderIconWhite)" />
                                <rect x="38" y="55" width="54" height="28" rx="14" fill="url(#assistantHeaderIconFace)" />
                                <ellipse cx="51" cy="69" rx="5.2" ry="7.2" fill="#ffffff" />
                                <ellipse cx="79" cy="69" rx="5.2" ry="7.2" fill="#ffffff" />
                                <path d="M58 77c4 4 10 4 14 0" stroke="#ffffff" stroke-width="4" stroke-linecap="round" fill="none" />
                                <path d="M47 91c-5 5-7 12-5 20" stroke="#7b1020" stroke-width="4" stroke-linecap="round" fill="none" />
                                <path d="M83 91c5 5 7 12 5 20" stroke="#7b1020" stroke-width="4" stroke-linecap="round" fill="none" />
                                <circle cx="51" cy="105" r="8" fill="none" stroke="#7b1020" stroke-width="4" />
                                <path d="M87 105c3-9 10-13 17-10 8 3 10 11 7 20" fill="none" stroke="#7b1020" stroke-width="5" stroke-linecap="round" />
                                <circle cx="106" cy="115" r="3" fill="#7b1020" />
                                <circle cx="88" cy="115" r="3" fill="#7b1020" />
                                <path d="M83 21h25c8 0 14 6 14 14v10c0 8-6 14-14 14H96l-10 10v-10h-3c-8 0-14-6-14-14V35c0-8 6-14 14-14Z" fill="#ffffff" />
                                <circle cx="89" cy="40" r="4" fill="#871224" />
                                <circle cx="98" cy="40" r="4" fill="#871224" />
                                <circle cx="107" cy="40" r="4" fill="#871224" />
                            </svg>
                            </span>
                        </h2>
                        <div class="assistant-title-copy">
                            <strong>Clinic Chatbot</strong>
                            <span>✨ Coming Soon ✨</span>
                        </div>
                    </div>
                    <button type="button" class="assistant-modal-close" id="assistantModalClose" aria-label="Close AI Chatbot">
                        <svg viewBox="0 0 24 24" aria-hidden="true">
                            <path d="M18 6L6 18M6 6l12 12" stroke-linecap="round" stroke-linejoin="round"></path>
                        </svg>
                    </button>
                </div>
                <div class="assistant-modal-content">
                    <div class="assistant-coming-grid">
                        <div class="assistant-robot-scene" aria-hidden="true">
                            <span class="assistant-robot-halo"></span>
                            <span class="assistant-plus-mark one">+</span>
                            <span class="assistant-plus-mark two">+</span>
                            <img class="assistant-reference-icon" src="{{ asset('images/clinic-robot-nobg.png') }}" onerror="this.onerror=null;this.src='{{ asset('images/clinic-robot.png') }}';" alt="">
                            <div class="assistant-speech-bubble" data-assistant-bubble>
                                <span class="assistant-speech-bubble-text" data-assistant-bubble-text>We'll get<br>back soon<span>♥</span></span>
                            </div>
                            <div class="assistant-robot-art">
                                <span class="assistant-robot-antenna"></span>
                                <span class="assistant-robot-ear left"></span>
                                <span class="assistant-robot-ear right"></span>
                                <span class="assistant-robot-head">
                                    <span class="assistant-robot-face">
                                        <span class="assistant-robot-smile"></span>
                                    </span>
                                </span>
                                <span class="assistant-robot-body"></span>
                                <span class="assistant-robot-wave"></span>
                                <span class="assistant-stethoscope"></span>
                            </div>
                        </div>
                        <div class="assistant-coming-copy">
                            <h3>AI Chatbot Assistant</h3>
                            <div class="assistant-coming-label">— ✨ Coming Soon ✨ —</div>
                            <p>The AI Clninc Chatbot Assistant is currently under development and will soon be available to help answer clinic-related questions, provide guidance, and assist with common inquiries.</p>
                            <div class="assistant-patience"><span>♥</span><strong>Thank you for your patience while we build this feature.</strong></div>
                        </div>
                    </div>

                    <div class="assistant-features">
                        <div class="assistant-features-title">✨ Coming Features</div>
                        <div class="assistant-feature-grid">
                            <div class="assistant-feature">
                                <span class="assistant-feature-icon"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path d="M4 5h16v12H8l-4 4V5z"/><path d="M8 9h8M8 13h5"/></svg></span>
                                <strong>Answer Clinic FAQs</strong>
                                <span>Get quick answers to common questions.</span>
                            </div>
                            <div class="assistant-feature">
                                <span class="assistant-feature-icon"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path d="M7 3v3M17 3v3M4 9h16M6 5h12a2 2 0 0 1 2 2v13H4V7a2 2 0 0 1 2-2z"/><path d="M8 13h3M8 17h6"/></svg></span>
                                <strong>Appointment Guidance</strong>
                                <span>Help with schedules, appointments, and more.</span>
                            </div>
                            <div class="assistant-feature">
                                <span class="assistant-feature-icon"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path d="M12 21s7-4.4 7-11a4 4 0 0 0-7-2.6A4 4 0 0 0 5 10c0 6.6 7 11 7 11z"/><path d="M8 13h3l1-2 2 4 1-2h3"/></svg></span>
                                <strong>Health Information</strong>
                                <span>Access trusted health advice and reminders.</span>
                            </div>
                            <div class="assistant-feature">
                                <span class="assistant-feature-icon"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path d="m21 21-4.3-4.3"/><path d="M10.8 18a7.2 7.2 0 1 1 0-14.4 7.2 7.2 0 0 1 0 14.4Z"/></svg></span>
                                <strong>Smart Search</strong>
                                <span>Find the information you need, faster.</span>
                            </div>
                        </div>
                    </div>

                    <div class="assistant-notify">
                        <span>🔔</span>
                        <span>We'll notify you once this feature becomes available.</span>
                       
                        <span>See you soon! ♥</span>
                    </div>
                </div>
            </div>
        </div>
        <section class="landing-panel" aria-label="PUP medical clinic access">
            <div class="info-column">
                <div class="info-default">
                    <div class="brand-row">
                        <div class="brand-badge">
                            <img src="{{ asset('images/pup_logo.png') }}" alt="PUP Logo">
                        </div>
                        <div class="brand-meta">
                            <p class="brand-kicker">Medical Clinic</p>
                            <p class="brand-name">Polytechnic University of the Philippines Taguig</p>
                        </div>
                    </div>

                    <div class="hero-copy">
                        <h1>PUP Taguig Medical Clinic</h1>
                        <p>
                           A centralized web-based clinic management platform designed to digitize PUP Taguig Clinic operations, integrating student health profiles, appointment scheduling, verification processes, and staff workflows into a unified portal.
                        </p>
                    </div>

                    <div class="trust-grid" aria-label="Clinic system capabilities">
                        <div class="trust-item">
                            <span class="trust-icon">
                                <svg viewBox="0 0 24 24" fill="none" aria-hidden="true">
                                    <path d="M12 3l7 4v5c0 4.5-2.9 7.4-7 9-4.1-1.6-7-4.5-7-9V7l7-4z" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round"/>
                                    <path d="M9 12l2 2 4-5" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round"/>
                                </svg>
                            </span>
                            <span class="trust-text">
                                <span class="trust-title">One Portal Integrated</span>
                                <span class="trust-copy">Secure centralized IdP access</span>
                            </span>
                        </div>
                        <div class="trust-item">
                            <span class="trust-icon">
                                <svg viewBox="0 0 24 24" fill="none" aria-hidden="true">
                                    <path d="M7 3v3M17 3v3M4 9h16M6 5h12a2 2 0 0 1 2 2v14H4V7a2 2 0 0 1 2-2z" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round"/>
                                    <path d="M8 14h3M8 18h6" stroke="currentColor" stroke-linecap="round"/>
                                </svg>
                            </span>
                            <span class="trust-text">
                                <span class="trust-title">Appointments</span>
                                <span class="trust-copy">Student scheduling and visit tracking</span>
                            </span>
                        </div>
                        <div class="trust-item">
                            <span class="trust-icon">
                                <svg viewBox="0 0 24 24" fill="none" aria-hidden="true">
                                    <path d="M12 21s7-4.4 7-11a4 4 0 0 0-7-2.6A4 4 0 0 0 5 10c0 6.6 7 11 7 11z" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round"/>
                                    <path d="M9 13h2l1-2 2 4 1-2h2" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round"/>
                                </svg>
                            </span>
                            <span class="trust-text">
                                <span class="trust-title">Health Records</span>
                                <span class="trust-copy">Organized profiles and clinic verification</span>
                            </span>
                        </div>
                        <div class="trust-item">
                            <span class="trust-icon">
                                <svg viewBox="0 0 24 24" fill="none" aria-hidden="true">
                                    <path d="M12 3l1.2 3.8L17 8l-3.8 1.2L12 13l-1.2-3.8L7 8l3.8-1.2L12 3zM18 14l.8 2.2L21 17l-2.2.8L18 20l-.8-2.2L15 17l2.2-.8L18 14zM6 13l.7 1.8L8.5 15.5l-1.8.7L6 18l-.7-1.8-1.8-.7 1.8-.7L6 13z" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round"/>
                                </svg>
                            </span>
                            <span class="trust-text">
                                <span class="trust-title">AI Integrated</span>
                                <span class="trust-copy">Assisted intake and clinic workflows</span>
                            </span>
                        </div>
                    </div>
                </div>

                <div class="info-login-swap" aria-hidden="true">
                    <div class="logo-stack" aria-label="PUP and clinic logos">
                        <span class="logo-frame">
                            <img src="{{ asset('images/pup_logo.png') }}" alt="PUP Logo">
                        </span>
                        <span class="logo-frame logo-frame--clinic">
                            <img src="{{ asset('images/clinic_logo_transparent.png') }}?v={{ filemtime(public_path('images/clinic_logo_transparent.png')) }}" alt="Clinic Logo">
                        </span>
                    </div>

                    <div class="login-copy">
                        <h2>Clinic Access</h2>
                        <p>Use your One Portal account to continue browsing other systems or clinic workspace.</p>
                    </div>

                    <a class="portal-btn" href="{{ $workspaceHref }}">
                        <svg viewBox="0 0 24 24" fill="none" aria-hidden="true">
                            <path d="M10 17l5-5-5-5" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round"/>
                            <path d="M15 12H4" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round"/>
                            <path d="M20 4v16" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round"/>
                        </svg>
                        <span>View Clinic Workspace</span>
                    </a>

                    @if($landingStudentUser)
                        <a class="portal-btn" href="{{ url('/student/account?view=profile') }}">
                            <svg viewBox="0 0 24 24" fill="none" aria-hidden="true">
                                <circle cx="12" cy="8" r="4" stroke="currentColor"/>
                                <path d="M5 21a7 7 0 0 1 14 0" stroke="currentColor" stroke-linecap="round"/>
                            </svg>
                            <span>My Account</span>
                        </a>
                    @elseif($landingAdminUser)
                        <a class="portal-btn" href="{{ url('/admin/dashboard') }}">
                            <svg viewBox="0 0 24 24" fill="none" aria-hidden="true">
                                <path d="M12 3l7 4v5c0 4.5-2.9 7.4-7 9-4.1-1.6-7-4.5-7-9V7l7-4z" stroke="currentColor" stroke-linejoin="round"/>
                                <path d="M9 12l2 2 4-5" stroke="currentColor" stroke-linecap="round"/>
                            </svg>
                            <span>My Account</span>
                        </a>
                    @else
                        <a class="portal-btn" href="https://one-portal.isaxbsit2027.com/portal">
                            <svg viewBox="0 0 24 24" fill="none" aria-hidden="true">
                                <path d="M10 17l5-5-5-5" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round"/>
                                <path d="M15 12H4" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round"/>
                                <path d="M20 4v16" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round"/>
                            </svg>
                            <span>Log In via One Portal</span>
                        </a>
                    @endif

                    <div class="workspace-utility-actions">
                        <button class="help-btn help-link" type="button" aria-controls="landingHelpPanel" aria-expanded="true">
                            <svg viewBox="0 0 24 24" fill="none" aria-hidden="true">
                                <path d="M12 18h.01" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round"/>
                                <path d="M9.5 9a2.5 2.5 0 1 1 4.1 1.9c-.9.7-1.6 1.2-1.6 2.6v.5" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round"/>
                                <path d="M12 21a9 9 0 1 0 0-18 9 9 0 0 0 0 18z" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round"/>
                            </svg>
                            <span>Need Help?</span>
                        </button>

                        @guest('student')
                            @guest('admin')
                                @env('local')
                                    <a class="local-login-link" href="{{ route('login') }}">
                                        <svg viewBox="0 0 24 24" fill="none" aria-hidden="true">
                                            <rect x="4" y="3" width="16" height="18" rx="2" stroke="currentColor"/>
                                            <path d="M8 8h8M8 12h5M8 16h3" stroke="currentColor" stroke-linecap="round"/>
                                        </svg>
                                        <span>Local Login</span>
                                    </a>
                                @endenv
                            @endguest
                        @endguest
                    </div>

                    <p class="system-foot">PUP Taguig Clinic Management System</p>
                </div>
            </div>

            <div class="login-column">
                <div class="login-card">
                    <div class="login-primary gateway-stage" id="landingLoginPrimary">
                        <div class="gateway-top-content">
                        <div class="logo-stack gateway-logo-row" aria-label="PUP and clinic logos">
                            <span class="logo-frame gateway-logo-card">
                                <img src="{{ asset('images/pup_logo.png') }}" alt="PUP Logo">
                            </span>
                            <span class="logo-frame logo-frame--clinic gateway-logo-card">
                                <img src="{{ asset('images/clinic_logo_transparent.png') }}?v={{ filemtime(public_path('images/clinic_logo_transparent.png')) }}" alt="Clinic Logo">
                            </span>
                        </div>

                        <div class="login-copy gateway-brand-copy">
                            <p class="gateway-kicker">Medical Services Department</p>
                            <h1 class="gateway-title">PUP Taguig Medical Clinic</h1>
                            <p class="gateway-copy">A centralized web-based clinic management platform designed to digitize PUP Taguig Clinic operations, integrating student health profiles, appointment scheduling, verification processes, and staff workflows into one unified access point.</p>
                        </div>

                        @if($errors->has('idp'))
                            <div class="notice">{{ $errors->first('idp') }}</div>
                        @endif

                        <div class="workspace-entry gateway-actions">
                            <a href="{{ $workspaceHref }}" class="portal-btn portal-btn--workspace" id="viewClinicWorkspaceBtn">
                                <span class="portal-btn__icon" aria-hidden="true">
                                    <svg viewBox="0 0 24 24">
                                        <rect x="3" y="4" width="18" height="12" rx="2"></rect>
                                        <path d="M8 20h8M12 16v4"></path>
                                    </svg>
                                </span>
                                <span class="portal-btn__label">View Clinic Workspace</span>
                                <span class="portal-btn__arrow" aria-hidden="true">
                                    <svg viewBox="0 0 24 24">
                                        <path d="M5 12h14"></path>
                                        <path d="m13 6 6 6-6 6"></path>
                                    </svg>
                                </span>
                            </a>

                            @if($landingStudentUser)
                                <a class="portal-btn" href="{{ url('/student/account?view=profile') }}">
                                    <svg viewBox="0 0 24 24" fill="none" aria-hidden="true">
                                        <circle cx="12" cy="8" r="4" stroke="currentColor"/>
                                        <path d="M5 21a7 7 0 0 1 14 0" stroke="currentColor" stroke-linecap="round"/>
                                    </svg>
                                    <span>My Account</span>
                                </a>
                            @elseif($landingAdminUser)
                                <a class="portal-btn" href="{{ url('/admin/dashboard') }}">
                                    <svg viewBox="0 0 24 24" fill="none" aria-hidden="true">
                                        <path d="M12 3l7 4v5c0 4.5-2.9 7.4-7 9-4.1-1.6-7-4.5-7-9V7l7-4z" stroke="currentColor" stroke-linejoin="round"/>
                                        <path d="M9 12l2 2 4-5" stroke="currentColor" stroke-linecap="round"/>
                                    </svg>
                                    <span>My Account</span>
                                </a>
                            @else
                                <a class="portal-btn portal-btn--idp" href="https://one-portal.isaxbsit2027.com/portal">
                                    <span class="portal-btn__icon" aria-hidden="true">
                                        <svg viewBox="0 0 24 24">
                                            <circle cx="12" cy="8" r="4"></circle>
                                            <path d="M5 20a7 7 0 0 1 14 0"></path>
                                        </svg>
                                    </span>
                                    <span class="portal-btn__label">Log In via One Portal</span>
                                    <span class="portal-btn__arrow" aria-hidden="true">
                                        <svg viewBox="0 0 24 24">
                                            <path d="M5 12h14"></path>
                                            <path d="m13 6 6 6-6 6"></path>
                                        </svg>
                                    </span>
                                </a>
                            @endif

                            <div class="workspace-utility-actions gateway-utility">
                                <button class="help-btn help-link" type="button" id="landingNeedHelpButton" aria-controls="landingHelpPanel" aria-expanded="false">
                                    <svg viewBox="0 0 24 24" fill="none" aria-hidden="true">
                                        <path d="M12 18h.01" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round"/>
                                        <path d="M9.5 9a2.5 2.5 0 1 1 4.1 1.9c-.9.7-1.6 1.2-1.6 2.6v.5" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round"/>
                                        <path d="M12 21a9 9 0 1 0 0-18 9 9 0 0 0 0 18z" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round"/>
                                    </svg>
                                    <span>Need Help?</span>
                                </button>

                                @guest('student')
                                    @guest('admin')
                                        @env('local')
                                            <a class="local-login-link" href="{{ route('login') }}">
                                                <svg viewBox="0 0 24 24" fill="none" aria-hidden="true">
                                                    <rect x="4" y="3" width="16" height="18" rx="2" stroke="currentColor"/>
                                                    <path d="M8 8h8M8 12h5M8 16h3" stroke="currentColor" stroke-linecap="round"/>
                                                </svg>
                                                <span>Local Login</span>
                                            </a>
                                        @endenv
                                    @endguest
                                @endguest
                            </div>
                        </div>

                       
                        <div id="saWorkspaceSelector" class="sa-workspace-selector">
                            <div class="login-copy" style="margin-bottom: 8px;">
                                <h2>Choose Your Workspace</h2>
                                <p>Select where you want to continue today.</p>
                            </div>
                            <a class="workspace-btn" href="{{ route('assistant.enter-student') }}">
                                <span class="workspace-badge">👤</span>
                                <span>Go to Student Side</span>
                            </a>
                            <a
                                class="workspace-btn"
                                href="{{ $saAdminWorkspaceAvailable ? route('assistant.enter-admin') : '#' }}"
                                @unless($saAdminWorkspaceAvailable)
                                    aria-disabled="true"
                                    onclick="event.preventDefault();"
                                    style="cursor: not-allowed; opacity: .58;"
                                @endunless
                            >
                                <span class="workspace-badge">⚙️</span>
                                <span>
                                    {{ $saAdminWorkspaceAvailable
                                        ? 'Go to Admin/SA Side'
                                        : 'Admin Side: Available ' . $saAdminWorkspaceHoursLabel }}
                                </span>
                            </a>
                        </div>

                        </div>

                        <div class="gateway-feature-grid" aria-label="Clinic system capabilities">
                            <article class="gateway-feature-card">
                                <h3 class="gateway-feature-title">Clinic Access</h3>
                                <p class="gateway-feature-copy">Secure workspace entry through One Portal and role-aware portal routing.</p>
                            </article>

                            <article class="gateway-feature-card">
                                <h3 class="gateway-feature-title">Health Records</h3>
                                <p class="gateway-feature-copy">Student profiles, digital clearances, and clinic-ready submission workflows.</p>
                            </article>

                            <article class="gateway-feature-card">
                                <h3 class="gateway-feature-title">Appointments</h3>
                                <p class="gateway-feature-copy">Book clinic services, monitor schedules, and follow your visit status.</p>
                            </article>

                            <article class="gateway-feature-card">
                                <h3 class="gateway-feature-title">AI Integrated</h3>
                                <p class="gateway-feature-copy">Assisted intake tools and smarter clinic-side workflow support.</p>
                            </article>
                        </div>

                        <p class="system-foot">PUP Taguig Clinic Management System</p>
                    </div>

                    <div class="help-panel" id="landingHelpPanel" aria-hidden="true">
                        <div class="help-panel-head">
                            <button type="button" class="help-panel-back" id="landingHelpBackButton" aria-label="Back to clinic access">
                                <svg viewBox="0 0 24 24" fill="none" aria-hidden="true">
                                    <path d="M15 18l-6-6 6-6" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round"/>
                                </svg>
                            </button>
                            <p class="help-panel-kicker">Help Center</p>
                            <h2 class="help-panel-title">Before You Continue</h2>
                            <p class="help-panel-copy">Use this guide if you cannot continue through One Portal or you are unsure what to do next.</p>
                        </div>

                        <div class="help-guide" aria-label="Help Center guide">
                            <details class="help-accordion" open>
                                <summary>
                                    <span class="help-accordion-icon">
                                        <svg viewBox="0 0 24 24" fill="none" aria-hidden="true">
                                            <circle cx="12" cy="7" r="4" stroke="currentColor"/>
                                            <path d="M5 21v-2a7 7 0 0 1 14 0v2" stroke="currentColor" stroke-linecap="round"/>
                                        </svg>
                                    </span>
                                    <span class="help-accordion-heading">
                                        <strong>For Students</strong>
                                        <span>Login, clinic records, and medical clearance</span>
                                    </span>
                                    <svg class="help-accordion-chevron" viewBox="0 0 24 24" fill="none" aria-hidden="true">
                                        <path d="m6 9 6 6 6-6" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round"/>
                                    </svg>
                                </summary>
                                <div class="help-accordion-body">
                                    <ul class="help-check-list">
                                        <li>Use your official One Portal account.</li>
                                        <li>If One Portal does not open, refresh the page and try again.</li>
                                        <li>Contact clinic staff if your clinic record is not visible after login.</li>
                                        <li>Prepare your Admission System reference number for medical clearance.</li>
                                        <li>Visit the clinic when instructed to proceed with medical assessment.</li>
                                    </ul>
                                </div>
                            </details>

                            <details class="help-accordion">
                                <summary>
                                    <span class="help-accordion-icon">
                                        <svg viewBox="0 0 24 24" fill="none" aria-hidden="true">
                                            <path d="M4 5h16v14H4zM8 9h8M8 13h8M8 17h5" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round"/>
                                        </svg>
                                    </span>
                                    <span class="help-accordion-heading">
                                        <strong>For Clinic Staff</strong>
                                        <span>Staff access and system assistance</span>
                                    </span>
                                    <svg class="help-accordion-chevron" viewBox="0 0 24 24" fill="none" aria-hidden="true">
                                        <path d="m6 9 6 6 6-6" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round"/>
                                    </svg>
                                </summary>
                                <div class="help-accordion-body">
                                    <ul class="help-check-list">
                                        <li>Use One Portal for the normal clinic login process.</li>
                                        <li>Contact the system administrator when One Portal is unavailable.</li>
                                    </ul>
                                </div>
                            </details>

                            <details class="help-accordion">
                                <summary>
                                    <span class="help-accordion-icon">
                                        <svg viewBox="0 0 24 24" fill="none" aria-hidden="true">
                                            <path d="M12 3 2.8 20h18.4L12 3z" stroke="currentColor" stroke-linejoin="round"/>
                                            <path d="M12 9v5M12 17h.01" stroke="currentColor" stroke-linecap="round"/>
                                        </svg>
                                    </span>
                                    <span class="help-accordion-heading">
                                        <strong>Common Issues</strong>
                                        <span>Quick checks for access and record problems</span>
                                    </span>
                                    <svg class="help-accordion-chevron" viewBox="0 0 24 24" fill="none" aria-hidden="true">
                                        <path d="m6 9 6 6 6-6" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round"/>
                                    </svg>
                                </summary>
                                <div class="help-accordion-body">
                                    <ul class="help-issue-list">
                                        <li>One Portal unavailable</li>
                                        <li>Wrong account used</li>
                                        <li>Missing reference number</li>
                                        <li>Profile not submitted</li>
                                        <li>Status not approved</li>
                                        <li>Record under review</li>
                                    </ul>
                                </div>
                            </details>

                            <details class="help-accordion">
                                <summary>
                                    <span class="help-accordion-icon">
                                        <svg viewBox="0 0 24 24" fill="none" aria-hidden="true">
                                            <path d="M4 5h16v12H8l-4 4V5z" stroke="currentColor" stroke-linejoin="round"/>
                                            <path d="M8 9h8M8 13h5" stroke="currentColor" stroke-linecap="round"/>
                                        </svg>
                                    </span>
                                    <span class="help-accordion-heading">
                                        <strong>Contact</strong>
                                        <span>Where to request further assistance</span>
                                    </span>
                                    <svg class="help-accordion-chevron" viewBox="0 0 24 24" fill="none" aria-hidden="true">
                                        <path d="m6 9 6 6 6-6" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round"/>
                                    </svg>
                                </summary>
                                <div class="help-accordion-body">
                                    <div class="help-contact-card">
                                        <strong>PUP Taguig Medical Clinic</strong>
                                        <span>For clinic record, clearance, or assessment concerns, contact the clinic staff. For login or technical issues, contact the system administrator.</span>
                                    </div>
                                </div>
                            </details>
                        </div>

                        <div class="help-guide help-guide-legacy" aria-hidden="true">
                            <div class="help-guide-item">
                                <span class="help-guide-number">1</span>
                                <span class="help-guide-text">
                                    <strong>For Students</strong>
                                    <span>&middot; Make sure you are using your official One Portal account.</span>
                                    <span>&middot; If One Portal does not open, refresh the page and try again.</span>
                                    <span>&middot; If you can log in but cannot see your clinic record, contact the clinic staff.</span>
                                    <span>&middot; For medical clearance, prepare your Admission System reference number.</span>
                                    <span>&middot; Visit the clinic if you are instructed to proceed with medical assessment.</span>
                                </span>
                            </div>
                            
                            
                            <div class="help-guide-item">
                                <span class="help-guide-number">2</span>
                                <span class="help-guide-text">
                                    <strong>For Clinic Staff</strong>
                                    <span>&middot; Use One Portal for normal login.</span>
                                    <span>&middot;If One Portal is unavailable, contact the system administrator.</span>
                                </span>
                                </span>
                            </div>
                           
                            <div class="help-guide-item">
                                <span class="help-guide-number">3</span>
                                <span class="help-guide-text">
                                    <strong>Common Issues</strong>
                                    <span>&middot; One Portal is unavailable</span>
                                    <span>&middot; Wrong account used</span>
                                    <span>&middot; Missing applicant reference number</span>
                                    <span>&middot; Health profile not yet submitted</span>
                                    <span>&middot; Medical status not yet approved</span>
                                    <span>&middot; Clinic record is still under review</span>
                                </span>
                            </div>
                           
                                   
                            <div class="help-guide-item">
                                <span class="help-guide-number">4</span>
                                <span class="help-guide-text">
                                    <strong>Contact</strong>
                                    <span>&middot; For assistance, contact the PUP Taguig Medical Clinic or the system administrator.</span>
                                </span>
                            </div>
                        </div>

                    </div>
                </div>
            </div>
        </section>
    </main>
    @include('partials.system_footer')
    <script>
      
        const preloader = document.getElementById('preloader');
        const saSelector = document.getElementById('saWorkspaceSelector');
        const landingPanel = document.querySelector('.landing-panel');
        const helpPanel = document.getElementById('landingHelpPanel');
        const infoLoginSwap = document.querySelector('.info-login-swap');
        const helpButtons = Array.from(document.querySelectorAll('.help-btn'));
        const helpBackButton = document.getElementById('landingHelpBackButton');
        const helpAccordions = Array.from(document.querySelectorAll('.help-accordion'));
        const landingThemeToggle = document.getElementById('landingThemeToggle');
        const landingThemeToggleIcon = document.getElementById('landingThemeToggleIcon');
        let isHelpMode = false;
        const landingThemeStorageKey = 'landing-theme-preference';

        function setLandingThemeIcon(theme) {
            if (!landingThemeToggleIcon) {
                return;
            }

            if (theme === 'light') {
                landingThemeToggleIcon.innerHTML = '<path d="M12 3v2.2M12 18.8V21M4.9 4.9l1.5 1.5M17.6 17.6l1.5 1.5M3 12h2.2M18.8 12H21M4.9 19.1l1.5-1.5M17.6 6.4l1.5-1.5M12 7a5 5 0 1 1 0 10 5 5 0 0 1 0-10Z" stroke-linecap="round" stroke-linejoin="round"></path>';
            } else {
                landingThemeToggleIcon.innerHTML = '<path d="M21 12.8A9 9 0 1 1 11.2 3a7 7 0 0 0 9.8 9.8Z" stroke-linecap="round" stroke-linejoin="round"></path>';
            }
        }

        function applyLandingTheme(theme) {
            const normalizedTheme = theme === 'light' ? 'light' : 'dark';
            document.body.classList.toggle('landing-theme-light', normalizedTheme === 'light');

            if (landingThemeToggle) {
                landingThemeToggle.setAttribute('aria-pressed', normalizedTheme === 'light' ? 'true' : 'false');
                landingThemeToggle.setAttribute('aria-label', normalizedTheme === 'light' ? 'Switch to dark mode' : 'Switch to light mode');
                landingThemeToggle.setAttribute('title', normalizedTheme === 'light' ? 'Dark mode' : 'Light mode');
            }

            setLandingThemeIcon(normalizedTheme);
            localStorage.setItem(landingThemeStorageKey, normalizedTheme);
        }

        function initializeLandingTheme() {
            const savedTheme = localStorage.getItem(landingThemeStorageKey);
            applyLandingTheme(savedTheme === 'light' ? 'light' : 'dark');

            landingThemeToggle?.addEventListener('click', function () {
                const nextTheme = document.body.classList.contains('landing-theme-light') ? 'dark' : 'light';
                applyLandingTheme(nextTheme);
            });
        }

     
        function initializeLanding() {
            if (preloader) {
                preloader.classList.remove('hidden');
            }

            initializeLandingTheme();


            checkGatewayParameters();
            hidePreloader();
        }

    
        function checkGatewayParameters() {
            console.log('[LANDING] Checking gateway parameters...');
            const urlParams = new URLSearchParams(window.location.search);

            const workspaceParam = urlParams.get('workspace');
            const authErrorParam = urlParams.get('auth_error');

            console.log('[LANDING] URL Params - workspace:', workspaceParam, 'auth_error:', authErrorParam);

          
            if (authErrorParam === 'true') {
                console.log('[LANDING] Gateway returned auth_error - keeping public workspace UI visible');
                updateUIForGuest();
                return;
            }

 
            if (workspaceParam === 'sa') {
                console.log('[LANDING] Gateway returned workspace=sa - showing SA workspace selector');
                showStudentAssistantSelector();
                return;
            }

         
            if (workspaceParam === 'student') {
                console.log('[LANDING] Gateway returned workspace=student - showing public workspace UI');
                updateUIForGuest();
                return;
            }

            updateUIForGuest();
        }

        function updateUIForGuest() {
            if (saSelector) saSelector.classList.remove('visible');
        }

        function updateUIForAuthenticated() {
            if (saSelector) saSelector.classList.remove('visible');
        }

        function hidePreloader() {
            if (preloader) {
                setTimeout(() => {
                    preloader.classList.add('hidden');
                }, 200);
            }
        }

       
        function showStudentAssistantSelector() {
            console.log('[LANDING] Creating Student Assistant workspace selector');
            const adminWorkspaceAvailable = @json($saAdminWorkspaceAvailable);
            const adminWorkspaceHoursLabel = @json($saAdminWorkspaceHoursLabel);

            // Create modal overlay
            const modal = document.createElement('div');
            modal.id = 'saWorkspaceSelectorModal';
            modal.style.cssText = `
                position: fixed;
                top: 0;
                left: 0;
                width: 100%;
                height: 100%;
                background: rgba(15, 23, 42, 0.6);
                backdrop-filter: blur(4px);
                -webkit-backdrop-filter: blur(4px);
                display: flex;
                align-items: center;
                justify-content: center;
                z-index: 10000;
                animation: fadeInOverlay 0.3s ease;
            `;

            const modalBox = document.createElement('div');
            modalBox.style.cssText = `
                background: linear-gradient(135deg, rgba(255, 255, 255, 0.98), rgba(255, 248, 230, 0.95));
                border-radius: 24px;
                padding: 48px 40px;
                max-width: 480px;
                width: 90%;
                box-shadow: 0 28px 68px rgba(15, 23, 42, 0.34), inset 0 1px 0 rgba(255, 255, 255, 0.6);
                border: 1px solid rgba(112, 19, 27, 0.10);
                text-align: center;
                font-family: "Segoe UI", Tahoma, Geneva, Verdana, sans-serif;
                animation: slideInUp 0.4s cubic-bezier(0.22, 1, 0.36, 1);
            `;

            const headingHTML = `
                <div style="margin-bottom: 28px;">
                    <p style="margin: 0 0 8px 0; color: #70131b; font-size: 12px; font-weight: 950; letter-spacing: 0.18em; text-transform: uppercase;">Choose Your Portal</p>
                    <h2 style="margin: 0 0 12px 0; color: #70131b; font-size: 32px; line-height: 1.08; font-weight: 950;">Workspace Selection</h2>
                    <p style="margin: 0; color: #64748b; font-size: 15px; line-height: 1.6;">Select which side you want to access today.</p>
                </div>
            `;

            const studentsideButton = document.createElement('a');
            studentsideButton.href = '/student/home';
            studentsideButton.style.cssText = `
                display: flex;
                align-items: center;
                justify-content: center;
                gap: 14px;
                width: 100%;
                padding: 18px 24px;
                margin-bottom: 14px;
                border-radius: 18px;
                border: 1.5px solid #70131b;
                background: linear-gradient(135deg, #70131b, #8f2230);
                color: white;
                font-size: 16px;
                font-weight: 950;
                text-decoration: none;
                cursor: pointer;
                transition: all 0.18s ease;
                box-shadow: 0 12px 28px rgba(112, 19, 27, 0.18);
            `;
            studentsideButton.innerHTML = `
                <svg viewBox="0 0 24 24" fill="none" style="width: 22px; height: 22px; flex: 0 0 auto; stroke: currentColor; stroke-linecap: round; stroke-linejoin: round; stroke-width: 2;">
                    <path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"/>
                    <circle cx="12" cy="7" r="4"/>
                </svg>
                <span>Go to Student Side</span>
            `;
            studentsideButton.addEventListener('mouseover', function() {
                this.style.background = 'linear-gradient(135deg, #facc15, #fff1a8)';
                this.style.color = '#70131b';
                this.style.borderColor = '#facc15';
                this.style.boxShadow = '0 16px 36px rgba(112, 19, 27, 0.22)';
            });
            studentsideButton.addEventListener('mouseout', function() {
                this.style.background = 'linear-gradient(135deg, #70131b, #8f2230)';
                this.style.color = 'white';
                this.style.borderColor = '#70131b';
                this.style.boxShadow = '0 12px 28px rgba(112, 19, 27, 0.18)';
            });

            const adminButton = document.createElement(adminWorkspaceAvailable ? 'a' : 'button');
            if (adminWorkspaceAvailable) {
                adminButton.href = '{{ route('assistant.enter-admin') }}';
            } else {
                adminButton.type = 'button';
                adminButton.disabled = true;
                adminButton.setAttribute('aria-disabled', 'true');
            }
            adminButton.style.cssText = `
                display: flex;
                align-items: center;
                justify-content: center;
                gap: 14px;
                width: 100%;
                padding: 18px 24px;
                margin-bottom: 24px;
                border-radius: 18px;
                border: 1.5px solid #70131b;
                background: linear-gradient(135deg, #70131b, #8f2230);
                color: white;
                font-size: 16px;
                font-weight: 950;
                text-decoration: none;
                cursor: ${adminWorkspaceAvailable ? 'pointer' : 'not-allowed'};
                transition: all 0.18s ease;
                box-shadow: 0 12px 28px rgba(112, 19, 27, 0.18);
                opacity: ${adminWorkspaceAvailable ? '1' : '.58'};
            `;
            adminButton.innerHTML = `
                <svg viewBox="0 0 24 24" fill="none" style="width: 22px; height: 22px; flex: 0 0 auto; stroke: currentColor; stroke-linecap: round; stroke-linejoin: round; stroke-width: 2;">
                    <path d="M12 2L2 7v10a8 8 0 0 0 8 8 8 8 0 0 0 8-8V7l-10-5z"/>
                    <path d="M12 11v5M9 14h6"/>
                </svg>
                <span>${adminWorkspaceAvailable ? 'Go to Admin/SA Side' : `Admin Side: Available ${adminWorkspaceHoursLabel}`}</span>
            `;
            if (adminWorkspaceAvailable) {
                adminButton.addEventListener('mouseover', function() {
                    this.style.background = 'linear-gradient(135deg, #facc15, #fff1a8)';
                    this.style.color = '#70131b';
                    this.style.borderColor = '#facc15';
                    this.style.boxShadow = '0 16px 36px rgba(112, 19, 27, 0.22)';
                });
                adminButton.addEventListener('mouseout', function() {
                    this.style.background = 'linear-gradient(135deg, #70131b, #8f2230)';
                    this.style.color = 'white';
                    this.style.borderColor = '#70131b';
                    this.style.boxShadow = '0 12px 28px rgba(112, 19, 27, 0.18)';
                });
            }

            const footerHTML = `
                <p style="margin: 0; color: #94a3b8; font-size: 12px; line-height: 1.6;">PUP Taguig Clinic Management System</p>
            `;

            modalBox.innerHTML = headingHTML;
            modalBox.appendChild(studentsideButton);
            modalBox.appendChild(adminButton);
            modalBox.innerHTML += footerHTML;

            modal.appendChild(modalBox);
            document.body.appendChild(modal);

     
            const style = document.createElement('style');
            style.textContent = `
                @keyframes fadeInOverlay {
                    from { opacity: 0; }
                    to { opacity: 1; }
                }
                @keyframes slideInUp {
                    from {
                        opacity: 0;
                        transform: translateY(20px);
                    }
                    to {
                        opacity: 1;
                        transform: translateY(0);
                    }
                }
                @keyframes spin {
                    to { transform: rotate(360deg); }
                }
            `;
            if (!document.head.querySelector('style[data-landing-animations]')) {
                style.setAttribute('data-landing-animations', 'true');
                document.head.appendChild(style);
            }

            // Close modal on overlay click (but not on the box)
            modal.addEventListener('click', function(e) {
                if (e.target === modal) {
                    console.log('[LANDING] Closing SA workspace selector');
                    modal.remove();
                }
            });
        }

     
        function handleViewHomepage(event) {
            event.preventDefault();

            fetch('/api/get-redirect-path', {
                method: 'GET',
                headers: {
                    'Accept': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest'
                },
                credentials: 'include'
            })
            .then(response => response.json())
            .then(data => {
                if (data.redirectPath) {
                    window.location.href = data.redirectPath;
                } else {
                    console.error('No redirect path provided');
                }
            })
            .catch(error => {
                console.error('Error getting redirect path:', error);
            });
        }

       
        function setLandingHelpMode(nextState) {
            if (!landingPanel || !helpPanel) {
                return;
            }

            isHelpMode = !!nextState;
            landingPanel.classList.toggle('is-help', isHelpMode);
            helpPanel.setAttribute('aria-hidden', isHelpMode ? 'false' : 'true');
            infoLoginSwap?.setAttribute('aria-hidden', isHelpMode ? 'false' : 'true');
            helpButtons.forEach(function (button) {
                button.setAttribute('aria-expanded', isHelpMode ? 'true' : 'false');
            });
        }

        helpButtons.forEach(function (button) {
            button.addEventListener('click', function () {
                setLandingHelpMode(!isHelpMode);
            });
        });

        helpBackButton?.addEventListener('click', function () {
            setLandingHelpMode(false);
        });

        helpAccordions.forEach(function (accordion) {
            accordion.addEventListener('toggle', function () {
                if (!accordion.open) {
                    return;
                }

                helpAccordions.forEach(function (otherAccordion) {
                    if (otherAccordion !== accordion) {
                        otherAccordion.open = false;
                    }
                });
            });
        });

        // Announcement Modal Handler
        const announcementBtn = document.getElementById('announcementBtn');
        const announcementModal = document.getElementById('announcementModal');
        const announcementModalOverlay = document.getElementById('announcementModalOverlay');
        const announcementModalClose = document.getElementById('announcementModalClose');
        const announcementBadge = document.getElementById('announcementBadge');
        const announcementSearchInput = document.getElementById('announcementSearchInput');
        const announcementCards = Array.from(document.querySelectorAll('[data-announcement-card]'));
        const announcementSectionTitle = document.querySelector('[data-announcement-section-title]');
        const announcementSectionCount = document.querySelector('.announcement-section-head span:last-child');
        const announcementViewAllButton = document.querySelector('.announcement-view-all');
        const announcementEmptyState = document.querySelector('[data-announcement-empty]');
        let announcementViewAll = false;

        function openAnnouncementModal() {
            announcementModalOverlay.classList.add('is-open');
            announcementModal.classList.add('is-open');
            window.setTimeout(syncAnnouncementReadButtons, 80);
        }

        function closeAnnouncementModal() {
            announcementModalOverlay.classList.remove('is-open');
            announcementModal.classList.remove('is-open');
        }

        function updateAnnouncementBadge(count) {
            if (count > 0) {
                announcementBadge.textContent = count > 99 ? '99+' : count;
                announcementBadge.style.display = 'flex';
            } else {
                announcementBadge.style.display = 'none';
            }
        }

        announcementBtn?.addEventListener('click', openAnnouncementModal);
        announcementModalClose?.addEventListener('click', closeAnnouncementModal);
        announcementModalOverlay?.addEventListener('click', function (e) {
            if (e.target === announcementModalOverlay) {
                closeAnnouncementModal();
            }
        });

        function filterAnnouncements() {
            const query = (announcementSearchInput?.value || '').trim().toLowerCase();
            let visibleCount = 0;
            let shownCount = 0;
            let totalMatches = 0;

            announcementCards.forEach(function (card) {
                const searchable = card.dataset.search || '';
                const searchMatches = query === '' || searchable.includes(query);
                if (searchMatches) {
                    totalMatches += 1;
                }
                const shouldShow = searchMatches && (announcementViewAll || shownCount < 5);
                card.hidden = !shouldShow;
                card.classList.toggle('is-hidden-by-limit', !shouldShow && searchMatches && !announcementViewAll);
                if (shouldShow) {
                    visibleCount += 1;
                    shownCount += 1;
                } else if (searchMatches) {
                    shownCount += 1;
                }
            });

            if (announcementSectionTitle) {
                announcementSectionTitle.textContent = query ? 'Search Results' : 'Latest Announcements';
            }

            if (announcementSectionCount) {
                announcementSectionCount.textContent = `${visibleCount} of ${totalMatches}`;
            }

            if (announcementEmptyState) {
                announcementEmptyState.hidden = totalMatches > 0;
            }

            if (announcementViewAllButton) {
                announcementViewAllButton.hidden = announcementViewAll || totalMatches <= 5;
            }
        }

        announcementSearchInput?.addEventListener('input', filterAnnouncements);

        function syncAnnouncementReadButtons() {
            announcementCards.forEach(function (card) {
                const message = card.querySelector('.landing-announcement-message');
                const button = card.querySelector('[data-announcement-read]');
                if (!message || !button) {
                    return;
                }

                const wasExpanded = card.classList.contains('is-expanded');
                card.classList.remove('is-expanded');
                const isOverflowing = message.scrollHeight > message.clientHeight + 2;
                card.classList.toggle('is-expanded', wasExpanded);
                button.hidden = !isOverflowing && !wasExpanded;
                button.textContent = wasExpanded ? 'Read Less ↑' : 'Read More →';
            });
        }

        announcementCards.forEach(function (card) {
            const button = card.querySelector('[data-announcement-read]');
            button?.addEventListener('click', function () {
                card.classList.toggle('is-expanded');
                syncAnnouncementReadButtons();
            });
        });

        announcementViewAllButton?.addEventListener('click', function () {
            announcementViewAll = true;
            announcementModal?.classList.add('is-view-all');
            filterAnnouncements();
            syncAnnouncementReadButtons();
        });

        updateAnnouncementBadge({{ ($landingAnnouncements ?? collect())->count() }});
        filterAnnouncements();
        window.setTimeout(syncAnnouncementReadButtons, 50);
        window.addEventListener('resize', syncAnnouncementReadButtons);

        // AI Chatbot Modal Handler
        const assistantBtn = document.getElementById('assistantBtn');
        const assistantModal = document.getElementById('assistantModal');
        const assistantModalOverlay = document.getElementById('assistantModalOverlay');
        const assistantModalClose = document.getElementById('assistantModalClose');
        const assistantBubble = document.querySelector('[data-assistant-bubble]');
        const assistantBubbleText = document.querySelector('[data-assistant-bubble-text]');
        const assistantBubbleMessages = [
            "We'll get<br>back soon<span>♥</span>",
            "See ya!<span>♥</span>",
        ];
        let assistantBubbleIndex = 0;

        if (assistantBubble && assistantBubbleText) {
            window.setInterval(function () {
                assistantBubble.classList.add('is-changing');
                window.setTimeout(function () {
                    assistantBubbleIndex = (assistantBubbleIndex + 1) % assistantBubbleMessages.length;
                    assistantBubbleText.innerHTML = assistantBubbleMessages[assistantBubbleIndex];
                    assistantBubble.classList.remove('is-changing');
                }, 280);
            }, 5000);
        }

        function openAssistantModal() {
            assistantModalOverlay.classList.add('is-open');
            assistantModal.classList.add('is-open');
        }

        function closeAssistantModal() {
            assistantModalOverlay.classList.remove('is-open');
            assistantModal.classList.remove('is-open');
        }

        assistantBtn?.addEventListener('click', openAssistantModal);
        assistantModalClose?.addEventListener('click', closeAssistantModal);
        assistantModalOverlay?.addEventListener('click', function (e) {
            if (e.target === assistantModalOverlay) {
                closeAssistantModal();
            }
        });


        if (document.readyState === 'loading') {
            document.addEventListener('DOMContentLoaded', initializeLanding);
        } else {
            initializeLanding();
        }
    </script>
</body>
</html>

