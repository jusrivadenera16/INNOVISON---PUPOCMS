<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Choose Your Workspace | PUP Taguig Medical Clinic</title>
    <style>
        :root {
            --maroon: #70131b;
            --maroon-strong: #8f2230;
            --maroon-deep: #33080d;
            --gold: #facc15;
            --gold-soft: #fff1a8;
            --white: #ffffff;
            --ink: #1f2937;
            --muted: #64748b;
        }

        * {
            box-sizing: border-box;
        }

        html,
        body {
            min-height: 100%;
            margin: 0;
        }

        body {
            overflow-x: hidden;
            background:
                linear-gradient(135deg, rgba(51, 8, 13, 0.92), rgba(112, 19, 27, 0.78)),
                url('{{ asset('images/PUPBG.jpg') }}') center center / cover no-repeat fixed;
            color: var(--white);
            font-family: "Segoe UI", Tahoma, Geneva, Verdana, sans-serif;
            transition: background 0.45s ease, color 0.28s ease;
        }

        body::before {
            position: fixed;
            inset: 0;
            background:
                radial-gradient(circle at 20% 18%, rgba(250, 204, 21, 0.16), transparent 24%),
                radial-gradient(circle at 82% 12%, rgba(255, 255, 255, 0.12), transparent 22%),
                linear-gradient(180deg, rgba(255, 255, 255, 0.04), rgba(15, 23, 42, 0.22));
            content: "";
            pointer-events: none;
            transition: background 0.45s ease;
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
                linear-gradient(180deg, rgba(255, 255, 255, 0.18), rgba(255, 255, 255, 0.08));
        }

        .landing-shell {
            position: relative;
            z-index: 1;
            min-height: 100vh;
            display: flex;
            align-items: stretch;
            justify-content: center;
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
            width: 42px;
            height: 42px;
            padding: 0;
            border-radius: 999px;
            border: 1px solid rgba(250, 204, 21, 0.45);
            background: rgba(20, 16, 20, 0.52);
            color: #ffffff;
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
            width: 18px;
            height: 18px;
            stroke: currentColor;
            stroke-width: 2;
            fill: none;
        }

        body.landing-theme-light .landing-theme-toggle {
            background: rgba(255, 255, 255, 0.88);
            color: #70131b;
            border-color: rgba(112, 19, 27, 0.24);
        }

        .landing-panel {
            position: relative;
            width: min(1040px, 100%);
            min-height: min(650px, calc(100vh - 72px));
            display: block;
            padding: 0 0 14px;
            background: transparent;
        }

        .gateway-stage {
            position: relative;
            z-index: 2;
            min-height: 100%;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: flex-start;
            gap: 12px;
            text-align: center;
            transition: opacity .3s ease, transform .34s ease;
        }

        .gateway-top-content {
            width: 100%;
            display: flex;
            flex-direction: column;
            align-items: center;
            gap: 10px;
            margin-top: 3px;
            transition: opacity .26s ease, transform .32s ease;
        }

        .gateway-brand {
            width: min(760px, 100%);
            display: grid;
            justify-items: center;
            gap: 14px;
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
            color: #8fa4c8;
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
        body.landing-theme-light .system-foot {
            color: #64748b;
        }

        .workspace-entry.gateway-actions {
            width: min(500px, 100%);
            display: grid;
            gap: 10px;
            justify-items: stretch;
        }

        .portal-btn {
            position: relative;
            min-height: 52px;
            width: 100%;
            padding: 0 20px;
            overflow: hidden;
            border-radius: 999px;
            background: linear-gradient(135deg, rgba(112, 19, 27, 0.92), rgba(143, 34, 48, 0.94));
            border: 1px solid rgba(250, 204, 21, 0.26);
            box-shadow: 0 18px 34px rgba(112, 19, 27, 0.24);
            display: grid;
            grid-template-columns: auto 1fr auto;
            align-items: center;
            gap: 12px;
            color: #ffffff;
            text-decoration: none;
            font-size: 0.95rem;
            font-weight: 950;
            transition: color .12s ease, transform .18s ease, border-color .18s ease, box-shadow .18s ease, background .18s ease;
        }

        .portal-btn:hover,
        .portal-btn:focus-visible {
            background: linear-gradient(135deg, var(--gold), var(--gold-soft));
            border-color: var(--gold);
            color: var(--maroon);
            box-shadow: 0 22px 42px rgba(112, 19, 27, 0.26);
            transform: translateY(-1px);
            outline: none;
        }

        .portal-btn span,
        .portal-btn svg {
            position: relative;
            z-index: 1;
        }

        .portal-btn__label {
            text-align: center;
        }

        .portal-btn {
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

        .portal-btn__label {
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

        .portal-btn__label::before {
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

        .portal-btn__label::after {
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

        .portal-btn:hover .portal-btn__label::before,
        .portal-btn:focus-visible .portal-btn__label::before {
            transform: scaleX(1);
        }

        .portal-btn:hover .portal-btn__label::after,
        .portal-btn:focus-visible .portal-btn__label::after {
            transform: translateX(135%);
        }

        .portal-btn__label > span,
        .portal-btn__label > svg,
        .portal-btn__label > i,
        .portal-btn__label > strong,
        .portal-btn__label > em,
        .portal-btn__label > b {
            position: relative;
            z-index: 1;
        }

        .portal-btn__icon {
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

        .portal-btn__icon svg,
        .portal-btn__arrow svg {
            width: 20px;
            height: 20px;
            stroke: currentColor;
            stroke-width: 2;
            fill: none;
        }

        .portal-btn__arrow {
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

        .portal-btn:hover .portal-btn__arrow,
        .portal-btn:focus-visible .portal-btn__arrow {
            background: rgba(255, 255, 255, 0.22);
            border-color: rgba(255, 255, 255, 0.28);
            color: var(--maroon);
        }

        .portal-btn--idp:hover .portal-btn__arrow,
        .portal-btn--idp:focus-visible .portal-btn__arrow {
            background: linear-gradient(135deg, #fff4b8, #ffe693);
            border-color: #ffffff;
            color: var(--maroon);
        }

        .portal-btn:hover .portal-btn__icon,
        .portal-btn:focus-visible .portal-btn__icon {
            background: linear-gradient(135deg, var(--gold), var(--gold-soft));
            border-color: var(--gold);
            color: var(--maroon);
        }

        .portal-btn:hover .portal-btn__label,
        .portal-btn:focus-visible .portal-btn__label {
            border-color: var(--gold);
            color: #111111;
        }

        .workspace-schedule {
            display: block;
            margin: -2px 0 2px;
            color: rgba(255, 255, 255, 0.76);
            font-size: 12px;
            font-weight: 700;
            line-height: 1.45;
            text-align: center;
        }

        body.landing-theme-light .workspace-schedule {
            color: #64748b;
        }

        .portal-btn.is-disabled,
        .portal-btn.is-disabled:hover,
        .portal-btn.is-disabled:focus-visible {
            cursor: not-allowed;
            color: #64748b;
            box-shadow: none;
            transform: none;
        }

        .portal-btn.is-disabled::after {
            display: none;
        }

        .portal-btn.is-disabled .portal-btn__icon,
        .portal-btn.is-disabled .portal-btn__label,
        .portal-btn.is-disabled .portal-btn__arrow {
            background: #e5e7eb;
            border-color: #cbd5e1;
            color: #64748b;
            box-shadow: none;
        }

        .workspace-utility-actions.gateway-utility {
            justify-content: center;
            margin-top: 0;
            display: flex;
            align-items: center;
            gap: 12px;
            flex-wrap: wrap;
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
            cursor: pointer;
        }

        .workspace-utility-actions.gateway-utility .help-btn svg,
        .local-login-link svg {
            width: 17px;
            height: 17px;
            stroke: currentColor;
            stroke-width: 2;
            fill: none;
        }

        .workspace-utility-actions.gateway-utility .help-btn:hover,
        .workspace-utility-actions.gateway-utility .help-btn:focus-visible {
            color: var(--gold);
            background: transparent;
            outline: none;
        }

        body.landing-theme-light .workspace-utility-actions.gateway-utility .help-btn {
            color: var(--maroon);
        }

        body.landing-theme-light .workspace-utility-actions.gateway-utility .help-btn:hover,
        body.landing-theme-light .workspace-utility-actions.gateway-utility .help-btn:focus-visible {
            color: #8f2230;
        }

        .local-login-link {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
            color: rgba(255, 255, 255, 0.84);
            text-decoration: none;
            font-size: 0.94rem;
            font-weight: 800;
        }

        body.landing-theme-light .local-login-link {
            color: var(--maroon);
        }

        .gateway-feature-grid {
            width: 100%;
            max-width: 980px;
            display: grid;
            grid-template-columns: repeat(4, minmax(0, 1fr));
            gap: 12px;
            margin: 4px auto 0;
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

        .account-note {
            margin: 0 0 8px;
            color: rgba(255, 255, 255, 0.72);
            font-size: 12px;
            line-height: 1.55;
            text-align: center;
        }

        body.landing-theme-light .account-note {
            color: #7c8797;
        }

        .system-foot {
            margin: 10px 0 0;
            color: rgba(255, 255, 255, 0.66);
            font-size: 13px;
            text-align: center;
        }

        .landing-panel.is-help .gateway-top-content {
            opacity: 0;
            transform: translateY(-10px) scale(0.985);
            pointer-events: none;
        }

        .landing-panel.is-help .gateway-feature-grid,
        .landing-panel.is-help .system-foot {
            opacity: 0;
            visibility: hidden;
            pointer-events: none;
        }

        .help-panel {
            position: absolute;
            top: 50%;
            left: 50%;
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
            box-shadow: none;
            transition: opacity .26s ease, transform .34s ease;
            overflow: visible;
        }

        .landing-panel.is-help .help-panel {
            opacity: 1;
            transform: translate(-50%, -50%);
            pointer-events: auto;
        }

        .help-panel-head {
            background: transparent;
            color: #ffffff;
            margin: 0;
            padding: 0 8px 12px 54px;
            text-align: left;
            position: relative;
        }

        .help-panel-back {
            position: absolute;
            top: -2px;
            left: 8px;
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
            stroke: currentColor;
            stroke-width: 2.3;
            fill: none;
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
            color: rgba(255, 255, 255, 0.84);
            font-size: 13px;
            line-height: 1.65;
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
            display: grid;
            gap: 12px;
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
            stroke: currentColor;
            fill: none;
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
            stroke: currentColor;
            stroke-width: 2;
            fill: none;
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

        @media (max-width: 980px) {
            .landing-panel {
                min-height: auto;
                padding: 0 0 16px;
            }

            .gateway-title {
                white-space: normal;
            }

            .gateway-feature-grid {
                grid-template-columns: repeat(2, minmax(0, 1fr));
            }
        }

        @media (max-width: 640px) {
            .landing-shell {
                padding: 12px 14px 16px;
            }

            .landing-theme-toggle {
                top: 16px;
                right: 16px;
            }

            .landing-panel {
                padding: 0 0 12px;
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

            .help-issue-list {
                grid-template-columns: 1fr;
            }

            .help-accordion-body {
                padding-left: 13px;
            }
        }
    </style>
</head>
<body>
    @php
        $normalizedUserRole = \App\Models\User::normalizeRole($user->user_role ?? '');
        $rawUserRole = strtolower(trim((string) ($user->user_role ?? '')));
        $userType = strtolower(trim((string) ($user->user_type ?? '')));
        $isStudentAssistant = $normalizedUserRole === \App\Models\User::ROLE_ADMIN
            && (
                in_array($userType, ['assistant', 'student assistant', 'student_assistant'], true)
                || in_array($rawUserRole, ['student_assistant', 'studentassistant', 'assistant'], true)
            );
        $showStudentWorkspace = $isStudentAssistant || $normalizedUserRole === \App\Models\User::ROLE_STUDENT;
        $showAdminWorkspace = $isStudentAssistant
            || in_array($normalizedUserRole, [\App\Models\User::ROLE_ADMIN, \App\Models\User::ROLE_SUPERADMIN], true);
        $adminWorkspaceAvailable = (bool) ($adminWorkspaceAvailable ?? false);
    @endphp

    <main class="landing-shell">
        <button type="button" class="landing-theme-toggle" id="landingThemeToggle" aria-pressed="false" aria-label="Switch to light mode" title="Switch theme">
            <svg id="landingThemeToggleIcon" viewBox="0 0 24 24" aria-hidden="true">
                <path d="M21 12.8A9 9 0 1 1 11.2 3a7 7 0 0 0 9.8 9.8Z" stroke-linecap="round" stroke-linejoin="round"></path>
            </svg>
        </button>

        <section class="landing-panel" id="assistantLandingPanel" aria-label="Student Assistant workspace selection">
            <div class="gateway-stage">
                <div class="gateway-top-content">
                    <div class="gateway-brand">
                        <div class="gateway-logo-row" aria-label="PUP and clinic logos">
                            <span class="gateway-logo-card">
                                <img src="{{ asset('images/pup_logo.png') }}" alt="PUP Logo">
                            </span>
                            <span class="gateway-logo-card">
                                <img src="{{ asset('images/clinic_logo_transparent.png') }}" alt="Clinic Logo">
                            </span>
                        </div>

                        <div class="gateway-brand-copy">
                            <p class="gateway-kicker">Medical Services Department</p>
                            <h1 class="gateway-title">PUP Taguig Medical Clinic</h1>
                            <p class="gateway-copy">
                                Continue to the workspace you need. Your Student Assistant account can access student services and authorized clinic operations from one secure gateway.
                            </p>
                        </div>
                    </div>

                    <div class="workspace-entry gateway-actions">
                        @error('workspace')
                            <p class="account-note" role="alert" style="color:#fecaca; margin:0 0 4px;">{{ $message }}</p>
                        @enderror

                        <p class="account-note">
                            Signed in as {{ $user->name ?? 'Student Assistant' }}
                        </p>

                        @if($showStudentWorkspace)
                            <a class="portal-btn" href="{{ route('assistant.enter-student') }}">
                                <span class="portal-btn__icon" aria-hidden="true">
                                    <svg viewBox="0 0 24 24">
                                        <circle cx="12" cy="8" r="4"></circle>
                                        <path d="M5 20a7 7 0 0 1 14 0"></path>
                                    </svg>
                                </span>
                                <span class="portal-btn__label">Student Workspace</span>
                                <span class="portal-btn__arrow" aria-hidden="true">
                                    <svg viewBox="0 0 24 24">
                                        <path d="M5 12h14"></path>
                                        <path d="m13 6 6 6-6 6"></path>
                                    </svg>
                                </span>
                            </a>
                        @endif

                        @if($showAdminWorkspace)
                            @if($adminWorkspaceAvailable)
                                <a class="portal-btn" href="{{ route('assistant.enter-admin') }}">
                                    <span class="portal-btn__icon" aria-hidden="true">
                                        <svg viewBox="0 0 24 24">
                                            <rect x="3" y="4" width="18" height="12" rx="2"></rect>
                                            <path d="M8 20h8M12 16v4"></path>
                                        </svg>
                                    </span>
                                    <span class="portal-btn__label">Admin Workspace</span>
                                    <span class="portal-btn__arrow" aria-hidden="true">
                                        <svg viewBox="0 0 24 24">
                                            <path d="M5 12h14"></path>
                                            <path d="m13 6 6 6-6 6"></path>
                                        </svg>
                                    </span>
                                </a>
                            @else
                                <button class="portal-btn is-disabled" type="button" disabled aria-disabled="true">
                                    <span class="portal-btn__icon" aria-hidden="true">
                                        <svg viewBox="0 0 24 24">
                                            <rect x="3" y="4" width="18" height="12" rx="2"></rect>
                                            <path d="M8 20h8M12 16v4"></path>
                                        </svg>
                                    </span>
                                    <span class="portal-btn__label">Admin Workspace</span>
                                    <span class="portal-btn__arrow" aria-hidden="true">
                                        <svg viewBox="0 0 24 24">
                                            <path d="M5 12h14"></path>
                                            <path d="m13 6 6 6-6 6"></path>
                                        </svg>
                                    </span>
                                </button>
                            @endif
                            <small class="workspace-schedule">Admin Workspace: Available daily from {{ $adminWorkspaceHoursLabel ?? '8:00 AM–8:00 PM' }}</small>
                        @endif

                        <div class="workspace-utility-actions gateway-utility">
                            <button class="help-btn help-link" type="button" id="landingNeedHelpButton" aria-controls="landingHelpPanel" aria-expanded="false">
                                <svg viewBox="0 0 24 24" aria-hidden="true">
                                    <path d="M12 18h.01" stroke-linecap="round" stroke-linejoin="round"/>
                                    <path d="M9.5 9a2.5 2.5 0 1 1 4.1 1.9c-.9.7-1.6 1.2-1.6 2.6v.5" stroke-linecap="round" stroke-linejoin="round"/>
                                    <path d="M12 21a9 9 0 1 0 0-18 9 9 0 0 0 0 18z" stroke-linecap="round" stroke-linejoin="round"/>
                                </svg>
                                <span>Need Help?</span>
                            </button>
                        </div>
                    </div>
                </div>

                <div class="gateway-feature-grid" aria-label="Available workspace capabilities">
                    <article class="gateway-feature-card">
                        <h3 class="gateway-feature-title">Student Services</h3>
                        <p class="gateway-feature-copy">Appointments, health records, and student-side clinic actions.</p>
                    </article>

                    <article class="gateway-feature-card">
                        <h3 class="gateway-feature-title">Clinic Operations</h3>
                        <p class="gateway-feature-copy">Authorized clinic workflows and admin-side operational access.</p>
                    </article>

                    <article class="gateway-feature-card">
                        <h3 class="gateway-feature-title">Protected Access</h3>
                        <p class="gateway-feature-copy">Workspace entry still follows your clinic role and allowed access hours.</p>
                    </article>

                    <article class="gateway-feature-card">
                        <h3 class="gateway-feature-title">Unified Gateway</h3>
                        <p class="gateway-feature-copy">Move between student-facing and staff-facing flows from one shared portal.</p>
                    </article>
                </div>

                <p class="system-foot">PUP Taguig Clinic Management System</p>
            </div>

            <div class="help-panel" id="landingHelpPanel" aria-hidden="true">
                <div class="help-panel-head">
                    <button type="button" class="help-panel-back" id="landingHelpBackButton" aria-label="Back to clinic access">
                        <svg viewBox="0 0 24 24" aria-hidden="true">
                            <path d="M15 18l-6-6 6-6" stroke-linecap="round" stroke-linejoin="round"/>
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
                                <svg viewBox="0 0 24 24" aria-hidden="true">
                                    <circle cx="12" cy="7" r="4"></circle>
                                    <path d="M5 21v-2a7 7 0 0 1 14 0v2" stroke-linecap="round"/>
                                </svg>
                            </span>
                            <span class="help-accordion-heading">
                                <strong>For Students</strong>
                                <span>Student workspace and clinic record access</span>
                            </span>
                            <svg class="help-accordion-chevron" viewBox="0 0 24 24" aria-hidden="true">
                                <path d="m6 9 6 6 6-6" stroke-linecap="round" stroke-linejoin="round"/>
                            </svg>
                        </summary>
                        <div class="help-accordion-body">
                            <ul class="help-check-list">
                                <li>Open the Student Workspace if you need appointments, health records, or clinic profile access.</li>
                                <li>Use your official One Portal account before checking your clinic submission status.</li>
                                <li>Prepare your Admission System reference number when medical clearance is required.</li>
                            </ul>
                        </div>
                    </details>

                    <details class="help-accordion">
                        <summary>
                            <span class="help-accordion-icon">
                                <svg viewBox="0 0 24 24" aria-hidden="true">
                                    <rect x="4" y="5" width="16" height="14" rx="2"></rect>
                                    <path d="M8 9h8M8 13h8M8 17h5" stroke-linecap="round" stroke-linejoin="round"/>
                                </svg>
                            </span>
                            <span class="help-accordion-heading">
                                <strong>For Clinic Staff</strong>
                                <span>Admin-side workflows and staff assistance</span>
                            </span>
                            <svg class="help-accordion-chevron" viewBox="0 0 24 24" aria-hidden="true">
                                <path d="m6 9 6 6 6-6" stroke-linecap="round" stroke-linejoin="round"/>
                            </svg>
                        </summary>
                        <div class="help-accordion-body">
                            <ul class="help-check-list">
                                <li>Open the Admin Workspace only during your allowed Student Assistant access hours.</li>
                                <li>If admin access is unavailable, wait for the next active schedule or contact clinic staff.</li>
                                <li>Continue using the Student Workspace if you only need student-facing actions.</li>
                            </ul>
                        </div>
                    </details>

                    <details class="help-accordion">
                        <summary>
                            <span class="help-accordion-icon">
                                <svg viewBox="0 0 24 24" aria-hidden="true">
                                    <path d="M12 3 2.8 20h18.4L12 3z" stroke-linejoin="round"></path>
                                    <path d="M12 9v5M12 17h.01" stroke-linecap="round"></path>
                                </svg>
                            </span>
                            <span class="help-accordion-heading">
                                <strong>Common Issues</strong>
                                <span>Quick checks for login and workspace problems</span>
                            </span>
                            <svg class="help-accordion-chevron" viewBox="0 0 24 24" aria-hidden="true">
                                <path d="m6 9 6 6 6-6" stroke-linecap="round" stroke-linejoin="round"/>
                            </svg>
                        </summary>
                        <div class="help-accordion-body">
                            <ul class="help-issue-list">
                                <li>One Portal is unavailable</li>
                                <li>Wrong account used</li>
                                <li>Admin hours are closed</li>
                                <li>Clinic record is still under review</li>
                            </ul>
                        </div>
                    </details>

                    <details class="help-accordion">
                        <summary>
                            <span class="help-accordion-icon">
                                <svg viewBox="0 0 24 24" aria-hidden="true">
                                    <path d="M4 6h16v12H4z"></path>
                                    <path d="M7 10h10M7 14h6" stroke-linecap="round"></path>
                                </svg>
                            </span>
                            <span class="help-accordion-heading">
                                <strong>Contact</strong>
                                <span>Where to request further assistance</span>
                            </span>
                            <svg class="help-accordion-chevron" viewBox="0 0 24 24" aria-hidden="true">
                                <path d="m6 9 6 6 6-6" stroke-linecap="round" stroke-linejoin="round"/>
                            </svg>
                        </summary>
                        <div class="help-accordion-body">
                            <div class="help-contact-card">
                                <strong>PUP Taguig Medical Clinic</strong>
                                <span>Contact the clinic staff or the assigned system administrator when the workspace, hours, or record visibility needs verification.</span>
                            </div>
                        </div>
                    </details>
                </div>
            </div>
        </section>
    </main>

    @include('partials.system_footer')

    <script>
        (function () {
            const landingPanel = document.getElementById('assistantLandingPanel');
            const needHelpButton = document.getElementById('landingNeedHelpButton');
            const helpBackButton = document.getElementById('landingHelpBackButton');
            const helpPanel = document.getElementById('landingHelpPanel');
            const themeToggle = document.getElementById('landingThemeToggle');
            const themeIcon = document.getElementById('landingThemeToggleIcon');
            const themeStorageKey = 'landing-theme-preference';

            function setThemeIcon(theme) {
                if (!themeIcon) {
                    return;
                }

                if (theme === 'light') {
                    themeIcon.innerHTML = '<path d="M12 3v2.2M12 18.8V21M4.9 4.9l1.6 1.6M17.5 17.5l1.6 1.6M3 12h2.2M18.8 12H21M4.9 19.1l1.6-1.6M17.5 6.5l1.6-1.6M12 7a5 5 0 1 1 0 10 5 5 0 0 1 0-10Z" stroke-linecap="round" stroke-linejoin="round"></path>';
                } else {
                    themeIcon.innerHTML = '<path d="M21 12.8A9 9 0 1 1 11.2 3a7 7 0 0 0 9.8 9.8Z" stroke-linecap="round" stroke-linejoin="round"></path>';
                }
            }

            function applyTheme(theme) {
                document.body.classList.toggle('landing-theme-light', theme === 'light');

                if (themeToggle) {
                    const isLight = theme === 'light';
                    themeToggle.setAttribute('aria-pressed', isLight ? 'true' : 'false');
                    themeToggle.setAttribute('aria-label', isLight ? 'Switch to dark mode' : 'Switch to light mode');
                    themeToggle.setAttribute('title', isLight ? 'Switch theme' : 'Switch theme');
                }

                setThemeIcon(theme);
            }

            function getStoredTheme() {
                try {
                    return localStorage.getItem(themeStorageKey);
                } catch (error) {
                    return null;
                }
            }

            function storeTheme(theme) {
                try {
                    localStorage.setItem(themeStorageKey, theme);
                } catch (error) {
                    // Ignore storage failures.
                }
            }

            function openHelp() {
                if (!landingPanel || !helpPanel) {
                    return;
                }

                landingPanel.classList.add('is-help');
                helpPanel.setAttribute('aria-hidden', 'false');

                if (needHelpButton) {
                    needHelpButton.setAttribute('aria-expanded', 'true');
                }
            }

            function closeHelp() {
                if (!landingPanel || !helpPanel) {
                    return;
                }

                landingPanel.classList.remove('is-help');
                helpPanel.setAttribute('aria-hidden', 'true');

                if (needHelpButton) {
                    needHelpButton.setAttribute('aria-expanded', 'false');
                }
            }

            const initialTheme = getStoredTheme() || 'dark';
            applyTheme(initialTheme);

            if (themeToggle) {
                themeToggle.addEventListener('click', function () {
                    const nextTheme = document.body.classList.contains('landing-theme-light') ? 'dark' : 'light';
                    applyTheme(nextTheme);
                    storeTheme(nextTheme);
                });
            }

            if (needHelpButton) {
                needHelpButton.addEventListener('click', openHelp);
            }

            if (helpBackButton) {
                helpBackButton.addEventListener('click', closeHelp);
            }
        })();
    </script>
</body>
</html>
