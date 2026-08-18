<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Emergency Backup Login | PUP Taguig Clinic</title>
    <style>
        :root {
            --accent: #8B0000;
            --accent-deep: #5e0000;
            --accent-gold: #facc15;
            --paper: rgba(255,255,255,0.96);
            --ink: #12202b;
            --muted: #667085;
            --line: rgba(139, 0, 0, 0.18);
            --danger-bg: #fff1f2;
            --danger-fg: #9f1239;
            --success-bg: #ecfdf5;
            --success-fg: #047857;
        }
        * { box-sizing: border-box; margin: 0; padding: 0; font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, "Helvetica Neue", Arial, sans-serif; }
        body {
            min-height: 100vh;
            display: flex;
            flex-direction: column;
            color: #fff;
            background:
                linear-gradient(rgba(9, 14, 19, 0.68), rgba(9, 14, 19, 0.82)),
                url('{{ asset("images/PUPBG.jpg") }}') center/cover fixed no-repeat;
        }
        .topbar {
            padding: 18px 24px;
            background: rgba(91, 0, 0, 0.92);
            border-bottom: 1px solid rgba(255,255,255,0.12);
            display: flex;
            justify-content: center;
            backdrop-filter: blur(10px);
            position: relative;
            overflow: hidden;
        }
        .topbar::after,
        .panel-head::after {
            content: "";
            position: absolute;
            inset: 0;
            background: linear-gradient(110deg, transparent 35%, rgba(250, 204, 21, 0.18) 50%, transparent 65%);
            transform: translateX(-140%);
            pointer-events: none;
            animation: headerSweep 4s ease-in-out infinite;
            z-index: 0;
        }
        .topbar > *,
        .panel-head > * {
            position: relative;
            z-index: 1;
        }
        .brand {
            display: inline-flex;
            align-items: center;
            gap: 12px;
            text-decoration: none;
            color: #fff;
        }
        .brand img {
            width: 46px;
            height: 46px;
            object-fit: cover;
            border-radius: 50%;
            border: 2px solid rgba(255,255,255,0.24);
        }
        .brand-title {
            font-size: 18px;
            font-weight: 800;
            line-height: 1.1;
        }
        .brand-subtitle {
            font-size: 11px;
            letter-spacing: 0.12em;
            text-transform: uppercase;
            opacity: 0.88;
        }
        .shell {
            flex: 1;
            display: grid;
            place-items: center;
            padding: 28px 16px;
        }
        .panel {
            width: min(100%, 520px);
            border-radius: 26px;
            background: linear-gradient(180deg, rgba(255,255,255,0.96), rgba(248,250,252,0.94));
            color: var(--ink);
            border: 1px solid rgba(255,255,255,0.5);
            box-shadow: 0 28px 70px rgba(0, 0, 0, 0.35);
            backdrop-filter: blur(14px);
            overflow: hidden;
        }
        .panel-head {
            position: relative;
            overflow: hidden;
            padding: 24px 26px 18px;
            background:
                linear-gradient(135deg, rgba(91,0,0,0.98), rgba(127,29,29,0.98) 55%, rgba(168,18,18,0.98));
            color: #fff;
            border-bottom: 1px solid rgba(255,255,255,0.12);
        }
        .head-row {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 14px;
            margin-bottom: 16px;
        }
        .mark {
            width: 52px;
            height: 52px;
            border-radius: 16px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            background: rgba(255,255,255,0.12);
            border: 1px solid rgba(255,255,255,0.16);
            flex: 0 0 auto;
        }
        .mark svg { width: 24px; height: 24px; stroke: #fff; stroke-width: 2; }
        .badge {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            padding: 7px 12px;
            border-radius: 999px;
            background: rgba(250, 204, 21, 0.14);
            border: 1px solid rgba(250, 204, 21, 0.3);
            color: #fff7cc;
            font-size: 11px;
            font-weight: 800;
            letter-spacing: 0.08em;
            text-transform: uppercase;
            white-space: nowrap;
        }
        .badge span {
            width: 7px;
            height: 7px;
            border-radius: 50%;
            background: var(--accent-gold);
            box-shadow: 0 0 0 4px rgba(250, 204, 21, 0.15);
        }
        .panel-title {
            font-size: 32px;
            line-height: 1.05;
            font-weight: 900;
            margin-bottom: 10px;
        }
        .panel-copy {
            font-size: 14px;
            line-height: 1.7;
            max-width: 44ch;
            color: rgba(255,255,255,0.88);
        }
        .status-row {
            display: flex;
            flex-wrap: wrap;
            gap: 8px;
            margin-top: 16px;
        }
        .status-chip {
            padding: 7px 10px;
            border-radius: 999px;
            background: rgba(255,255,255,0.12);
            border: 1px solid rgba(255,255,255,0.16);
            font-size: 11px;
            font-weight: 700;
            color: rgba(255,255,255,0.92);
        }
        .panel-body {
            padding: 24px 26px 26px;
        }
        .alert {
            padding: 12px 14px;
            border-radius: 14px;
            margin-bottom: 14px;
            font-size: 13px;
            line-height: 1.55;
            border: 1px solid transparent;
        }
        .alert-error { background: var(--danger-bg); color: var(--danger-fg); border-color: #fecdd3; }
        .alert-success { background: var(--success-bg); color: var(--success-fg); border-color: #a7f3d0; }
        form { display: grid; gap: 14px; }
        .field {
            display: grid;
            gap: 6px;
        }
        .field label {
            font-size: 11px;
            font-weight: 900;
            color: #7a1b1b;
            text-transform: uppercase;
            letter-spacing: 0.08em;
        }
        .input-wrap {
            position: relative;
        }
        .input-wrap svg {
            position: absolute;
            left: 14px;
            top: 50%;
            transform: translateY(-50%);
            width: 18px;
            height: 18px;
            stroke: #8b0000;
            stroke-width: 2;
            pointer-events: none;
        }
        .field input {
            width: 100%;
            min-height: 50px;
            padding: 12px 16px 12px 44px;
            border-radius: 14px;
            border: 1px solid var(--line);
            background: linear-gradient(180deg, #fff, #fff8f6);
            color: #111827;
            font-size: 15px;
            font-weight: 700;
            outline: none;
            transition: border-color .18s ease, box-shadow .18s ease, transform .18s ease;
        }
        .field input:focus {
            border-color: #8b0000;
            box-shadow: 0 0 0 4px rgba(139,0,0,0.09);
            transform: translateY(-1px);
        }
        .field input.has-password-toggle {
            padding-right: 52px;
        }
        .password-toggle {
            position: absolute;
            right: 8px;
            top: 50%;
            transform: translateY(-50%);
            width: 38px;
            height: 38px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            border: 0;
            border-radius: 8px;
            background: transparent;
            color: #6b7280;
            cursor: pointer;
        }
        .password-toggle:hover,
        .password-toggle:focus-visible {
            background: rgba(139, 0, 0, 0.08);
            color: #8b0000;
            outline: none;
        }
        .input-wrap .password-toggle svg {
            position: static;
            width: 20px;
            height: 20px;
            transform: none;
            stroke: currentColor;
            pointer-events: none;
        }
        .password-toggle [hidden] {
            display: none;
        }
        .submit {
            min-height: 52px;
            border: 0;
            border-radius: 14px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 10px;
            background: linear-gradient(135deg, #5e0000, #8b0000 60%, #a61b1b);
            color: #fff;
            font-size: 14px;
            font-weight: 900;
            cursor: pointer;
            box-shadow: 0 16px 26px rgba(91,0,0,0.26);
            transition: transform .18s ease, box-shadow .18s ease, filter .18s ease, background .18s ease, color .18s ease;
        }
        .submit svg {
            width: 18px;
            height: 18px;
            stroke: #fff;
            stroke-width: 2;
            flex: 0 0 auto;
        }
        .submit:hover {
            background: var(--accent-gold);
            color: var(--accent);
            transform: translateY(-1px);
            filter: brightness(1.03);
            box-shadow: 0 20px 32px rgba(91,0,0,0.3);
        }
        .submit:hover svg {
            stroke: var(--accent);
        }
        .submit.is-secondary {
            background: #fff;
            border: 1px solid rgba(139, 0, 0, 0.28);
            box-shadow: none;
            color: #7a1b1b;
        }
        .submit.is-secondary:hover {
            background: #fff8f6;
            color: #7a1b1b;
            transform: translateY(-1px);
        }
        .submit.is-secondary svg { stroke: currentColor; }
        .mfa-options {
            display: grid;
            gap: 12px;
        }
        .mfa-option {
            width: 100%;
            min-height: 88px;
            display: grid;
            grid-template-columns: 42px minmax(0, 1fr) 20px;
            align-items: center;
            gap: 12px;
            padding: 14px;
            border: 1px solid var(--line);
            border-radius: 10px;
            background: #fff;
            color: var(--ink);
            cursor: pointer;
            font: inherit;
            text-align: left;
            transition: border-color .18s ease, box-shadow .18s ease, transform .18s ease;
        }
        .mfa-option:hover,
        .mfa-option:focus-visible {
            border-color: #8b0000;
            box-shadow: 0 8px 18px rgba(91, 0, 0, .11);
            outline: none;
            transform: translateY(-1px);
        }
        .mfa-option-icon {
            width: 42px;
            height: 42px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            border-radius: 8px;
            background: #fff1f2;
            color: #8b0000;
        }
        .mfa-option-icon svg,
        .mfa-option-arrow svg { width: 20px; height: 20px; stroke: currentColor; stroke-width: 2; }
        .mfa-option-copy { display: grid; gap: 3px; }
        .mfa-option-copy strong { font-size: 14px; }
        .mfa-option-copy small { color: var(--muted); font-size: 12px; font-weight: 600; line-height: 1.45; }
        .mfa-option-arrow { color: #8b0000; }
        .enrollment-grid {
            display: grid;
            grid-template-columns: minmax(0, 1fr) 210px;
            gap: 20px;
            align-items: start;
        }
        .enrollment-grid h2 { margin: 0 0 8px; font-size: 16px; }
        .enrollment-grid ol { display: grid; gap: 8px; padding-left: 19px; color: var(--muted); font-size: 13px; line-height: 1.5; }
        .qr-wrap {
            display: grid;
            place-items: center;
            min-height: 210px;
            padding: 10px;
            border: 1px solid rgba(15, 23, 42, .12);
            border-radius: 8px;
            background: #fff;
        }
        .qr-wrap svg { display: block; width: 188px; height: 188px; max-width: 100%; }
        .inline-note { margin-top: 10px; color: var(--muted); font-size: 12px; line-height: 1.5; }
        .backup-codes {
            display: grid;
            grid-template-columns: repeat(2, minmax(0, 1fr));
            gap: 8px;
            margin: 16px 0;
            padding: 0;
            list-style: none;
        }
        .backup-code {
            display: flex;
            align-items: center;
            justify-content: center;
            min-height: 40px;
            border: 1px solid rgba(139, 0, 0, .2);
            border-radius: 8px;
            background: #fff8f6;
            color: #701010;
            font: 800 14px/1 ui-monospace, SFMono-Regular, Menlo, Monaco, Consolas, monospace;
            letter-spacing: .08em;
        }
        .copy-status { min-height: 18px; margin-top: -4px; color: var(--success-fg); font-size: 12px; font-weight: 700; }
        .verification-meta { margin: 0 0 16px; color: var(--muted); font-size: 13px; line-height: 1.55; }
        .resend-form { margin-top: 10px; }
        .resend-button { padding: 0; border: 0; background: transparent; color: #8b0000; cursor: pointer; font-size: 12px; font-weight: 800; text-decoration: underline; }
        .footnote {
            margin-top: 16px;
            padding-top: 16px;
            border-top: 1px solid rgba(15,23,42,0.08);
            color: var(--muted);
            font-size: 12px;
            line-height: 1.6;
        }
        .footnote strong {
            display: block;
            margin-bottom: 4px;
            color: #7a1b1b;
            font-size: 11px;
            font-weight: 900;
            text-transform: uppercase;
            letter-spacing: 0.08em;
        }
        .bottom-bar {
            padding: 14px 16px;
            text-align: center;
            background: rgba(11, 16, 22, 0.92);
            color: rgba(255,255,255,0.92);
            font-size: 13px;
            border-top: 1px solid rgba(255,255,255,0.1);
        }
        @keyframes headerSweep {
            0% { transform: translateX(-140%); }
            35% { transform: translateX(140%); }
            100% { transform: translateX(140%); }
        }
        @media (max-width: 640px) {
            .panel-head, .panel-body { padding-left: 18px; padding-right: 18px; }
            .panel-title { font-size: 27px; }
            .head-row { align-items: flex-start; }
            .badge { font-size: 10px; }
            .enrollment-grid { grid-template-columns: 1fr; }
            .qr-wrap { order: -1; }
        }

        /* Emergency access uses the same quiet clinic treatment across every MFA step. */
        body {
            min-height: 100svh;
            position: relative;
            isolation: isolate;
            overflow-x: hidden;
            background: #fffdfd;
            color: #202431;
        }
        body::before {
            content: "";
            position: fixed;
            inset: 0;
            z-index: -3;
            opacity: .035;
            background: url('{{ asset('images/PUPBG.jpg') }}') center/cover no-repeat;
            filter: saturate(.7);
            pointer-events: none;
        }
        body::after {
            content: "";
            position: fixed;
            z-index: -2;
            left: -18vw;
            right: -18vw;
            bottom: -3.5rem;
            height: min(31vh, 210px);
            background: linear-gradient(105deg, #940718, #79000d 49%, #97081b);
            border-top: 3px solid #f3b321;
            border-radius: 50% 50% 0 0 / 24% 24% 0 0;
            box-shadow: inset 0 22px 34px rgba(75, 0, 8, .2);
            pointer-events: none;
        }
        .topbar {
            min-height: auto;
            padding: 21px 16px 0;
            background: transparent;
            border: 0;
            backdrop-filter: none;
            overflow: visible;
        }
        .topbar::after,
        .panel-head::after { display: none; }
        .brand {
            flex-direction: column;
            gap: 5px;
            color: #831021;
        }
        .brand::after {
            content: "";
            width: 53px;
            height: 3px;
            margin-top: 3px;
            border-radius: 999px;
            background: #f5b51f;
        }
        .brand img {
            width: 55px;
            height: 42px;
            border: 0;
            border-radius: 0;
            object-fit: contain;
        }
        .brand > div { text-align: center; }
        .brand-title {
            font-size: 18px;
            font-weight: 900;
            line-height: 1;
            letter-spacing: .1em;
        }
        .brand-subtitle {
            margin-top: 5px;
            color: #515868;
            font-size: 11px;
            font-weight: 800;
            letter-spacing: .2em;
            opacity: 1;
        }
        .shell {
            display: block;
            flex: 1;
            padding: 10px 16px 18px;
        }
        .panel {
            width: min(100%, 420px);
            margin: 0 auto;
            border: 1px solid rgba(132, 13, 30, .08);
            border-radius: 13px;
            background: rgba(255, 255, 255, .96);
            color: #242734;
            box-shadow: 0 18px 38px rgba(72, 34, 43, .16);
            backdrop-filter: blur(10px);
            overflow: hidden;
        }
        .panel-head {
            padding: 28px 34px 12px;
            overflow: visible;
            text-align: center;
            background: transparent;
            color: #7f0c1d;
            border: 0;
        }
        .security-seal {
            width: 82px;
            height: 82px;
            display: grid;
            place-items: center;
            margin: 0 auto 14px;
            color: #8c0c1d;
            border-radius: 50%;
            background: radial-gradient(circle at 50% 45%, rgba(255,255,255,.94) 0 28%, rgba(143,11,31,.055) 29% 100%);
        }
        .security-seal svg {
            width: 52px;
            height: 52px;
            stroke: currentColor;
            stroke-width: 1.7;
        }
        .panel-title {
            margin: 0;
            color: #830d20;
            font-size: 26px;
            font-weight: 900;
            line-height: 1.1;
            letter-spacing: 0;
        }
        .panel-copy {
            max-width: 290px;
            margin: 8px auto 0;
            color: #5b6170;
            font-size: 14px;
            font-weight: 600;
            line-height: 1.5;
        }
        .security-divider {
            display: flex;
            align-items: center;
            gap: 10px;
            margin-top: 16px;
            color: #e9a91c;
        }
        .security-divider::before,
        .security-divider::after {
            content: "";
            height: 1px;
            flex: 1;
            background: #ececf0;
        }
        .security-divider svg {
            width: 16px;
            height: 16px;
            stroke: currentColor;
            stroke-width: 2;
        }
        .panel-body { padding: 11px 34px 25px; }
        .alert {
            margin-bottom: 12px;
            padding: 10px 12px;
            border-radius: 7px;
            font-size: 12px;
        }
        form { gap: 11px; }
        .field { gap: 6px; }
        .field label {
            color: #8a1726;
            font-size: 11px;
            font-weight: 900;
            letter-spacing: .09em;
        }
        .field input {
            min-height: 41px;
            padding: 8px 37px;
            border: 1px solid #dcdce2;
            border-radius: 7px;
            background: #fffefe;
            color: #2f3440;
            font-size: 15px;
            font-weight: 600;
            box-shadow: none;
        }
        .field input:focus {
            border-color: #a20b20;
            box-shadow: 0 0 0 3px rgba(139, 0, 0, .08);
            transform: none;
        }
        .input-wrap::before {
            content: "";
            position: absolute;
            z-index: 0;
            top: 1px;
            bottom: 1px;
            left: 1px;
            width: 33px;
            border-radius: 6px 0 0 6px;
            background: #fff6f6;
        }
        .input-wrap svg { z-index: 1; left: 10px; width: 15px; height: 15px; stroke: #9a1b2b; }
        .field input.has-password-toggle { padding-right: 43px; }
        .password-toggle {
            z-index: 2;
            right: 3px;
            width: 32px;
            height: 32px;
            border-radius: 5px;
        }
        .password-toggle:hover,
        .password-toggle:focus-visible { background: transparent; color: #9a1b2b; }
        .input-wrap .password-toggle svg { width: 16px; height: 16px; }
        .submit {
            min-height: 43px;
            border-radius: 7px;
            background: linear-gradient(100deg, #a0031a, #7d0010);
            color: #fff;
            font-size: 13px;
            font-weight: 900;
            letter-spacing: .05em;
            box-shadow: 0 7px 14px rgba(115, 0, 15, .18);
        }
        .submit:hover,
        .submit:focus-visible {
            background: #facc15;
            color: #7d0010;
            box-shadow: 0 9px 17px rgba(115, 0, 15, .2);
            filter: none;
            transform: translateY(-1px);
        }
        .submit:hover svg,
        .submit:focus-visible svg { stroke: #7d0010; }
        #copyBackupCodes { width: 100%; }
        .submit.is-secondary {
            color: #850d1e;
            border-color: #d8a1a9;
            background: #fff;
        }
        .submit.is-secondary:hover { background: #fff7f7; color: #850d1e; }
        .footnote {
            display: flex;
            align-items: center;
            gap: 8px;
            margin-top: 20px;
            padding-top: 0;
            border: 0;
            color: #737886;
            font-size: 12px;
            line-height: 1.45;
        }
        .footnote svg {
            width: 18px;
            height: 18px;
            flex: 0 0 auto;
            stroke: #8c1021;
            stroke-width: 1.5;
        }
        .footnote strong { display: none; }
        .bottom-bar,
        body > .system-footer { display: none; }
        .mfa-options { gap: 9px; }
        .mfa-option {
            min-height: 62px;
            grid-template-columns: 32px minmax(0, 1fr) 16px;
            gap: 9px;
            padding: 10px;
            border-color: #ead4d7;
            border-radius: 7px;
        }
        .mfa-option:hover,
        .mfa-option:focus-visible { border-color: #9c0d20; box-shadow: 0 5px 12px rgba(91,0,0,.09); }
        .mfa-option-icon { width: 32px; height: 32px; border-radius: 6px; }
        .mfa-option-icon svg,
        .mfa-option-arrow svg { width: 16px; height: 16px; }
        .mfa-option-copy strong { font-size: 13px; }
        .mfa-option-copy small { font-size: 11px; }
        .enrollment-grid { grid-template-columns: 1fr; gap: 14px; text-align: center; }
        .enrollment-grid h2 { font-size: 16px; color: #830d20; }
        .enrollment-grid ol { text-align: left; font-size: 13px; }
        .qr-wrap { min-height: 302px; padding: 16px; border-radius: 8px; }
        .qr-wrap svg {
            width: auto;
            max-width: 100%;
            height: auto;
            margin-inline: auto;
            justify-self: center;
            shape-rendering: crispEdges;
        }
        .backup-codes { gap: 6px; margin: 12px 0; }
        .backup-code { min-height: 34px; font-size: 12px; }
        .verification-meta { font-size: 13px; }
        .otp-field { display: grid; gap: 10px; }
        .otp-field-label {
            color: #8a1726;
            font-size: 11px;
            font-weight: 900;
            letter-spacing: .09em;
            text-transform: uppercase;
        }
        .otp-inputs {
            display: grid;
            grid-template-columns: repeat(6, minmax(0, 1fr));
            gap: 8px;
        }
        .otp-digit {
            width: 100%;
            min-width: 0;
            height: 56px;
            padding: 0;
            border: 1px solid #ead4d7;
            border-radius: 8px;
            background: #fffefe;
            color: #801020;
            font-size: 25px;
            font-weight: 800;
            line-height: 1;
            text-align: center;
            outline: 0;
            transition: border-color .16s ease, box-shadow .16s ease, background .16s ease;
        }
        .otp-digit::placeholder { color: #b4b7c0; opacity: .75; }
        .otp-digit:focus {
            border: 2px solid #a30c20;
            background: #fff;
            box-shadow: 0 0 0 3px rgba(139, 0, 0, .08);
        }
        .otp-expiry {
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
            margin: 4px 0 0;
            color: #6d7280;
            font-size: 13px;
            font-weight: 700;
        }
        .otp-expiry svg { width: 18px; height: 18px; stroke: #a30c20; stroke-width: 2; }
        .otp-expiry strong { color: #a30c20; }
        .otp-error { min-height: 16px; margin: -4px 0 0; color: #9f1239; font-size: 12px; font-weight: 700; }
        .verification-actions {
            display: flex;
            flex-wrap: wrap;
            justify-content: center;
            gap: 12px 18px;
            margin-top: 15px;
        }
        .verification-action {
            padding: 0;
            border: 0;
            background: transparent;
            color: #8c1021;
            font: inherit;
            font-size: 12px;
            font-weight: 800;
            text-decoration: none;
            cursor: pointer;
        }
        .verification-action:hover,
        .verification-action:focus-visible { color: #5f000e; text-decoration: underline; outline: 0; }
        .verification-actions form { display: block; }
        .email-otp-notice {
            display: flex;
            align-items: center;
            gap: 12px;
            margin: 0 0 17px;
            padding: 12px 14px;
            border: 1px solid #f1e4e5;
            border-radius: 10px;
            background: #fffafa;
            color: #5e6574;
            font-size: 12px;
            font-weight: 600;
            line-height: 1.5;
        }
        .email-otp-notice-icon {
            width: 34px;
            height: 34px;
            display: grid;
            place-items: center;
            flex: 0 0 auto;
            border: 1px solid #a50d22;
            border-radius: 50%;
            color: #a50d22;
        }
        .email-otp-notice-icon svg { width: 19px; height: 19px; stroke: currentColor; stroke-width: 1.8; }
        .email-otp-notice p { margin: 0; }
        .email-otp-notice strong { color: #424958; font-weight: 900; }
        .email-otp-notice .email-otp-separator { padding: 0 4px; color: #a2a6b0; }
        .email-otp-notice .email-otp-expiry { color: #9b1324; }
        .verification-or {
            display: flex;
            align-items: center;
            gap: 14px;
            margin: 22px 0 16px;
            color: #a2a6b0;
            font-size: 12px;
            font-weight: 900;
            letter-spacing: .08em;
        }
        .verification-or::before,
        .verification-or::after {
            content: "";
            height: 1px;
            flex: 1;
            background: #e9e8ec;
        }
        .method-switch-grid {
            display: grid;
            grid-template-columns: repeat(2, minmax(0, 1fr));
            gap: 12px;
        }
        .method-switch-grid form { display: block; }
        .method-switch-button {
            width: 100%;
            min-height: 50px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 7px;
            padding: 8px;
            border: 1px solid #dedee5;
            border-radius: 9px;
            background: #fff;
            color: #626978;
            font: inherit;
            font-size: 11px;
            font-weight: 800;
            text-decoration: none;
            white-space: nowrap;
            cursor: pointer;
            transition: border-color .16s ease, background .16s ease, color .16s ease, box-shadow .16s ease;
        }
        .method-switch-button svg { width: 17px; height: 17px; stroke: #a30c20; stroke-width: 1.7; flex: 0 0 auto; }
        .method-switch-button:hover,
        .method-switch-button:focus-visible {
            border-color: #a30c20;
            background: #fff7f7;
            color: #830d20;
            box-shadow: 0 5px 12px rgba(91, 0, 0, .08);
            outline: 0;
        }
        .resend-email-section {
            margin-top: 22px;
            padding-top: 19px;
            border-top: 1px solid #ececf0;
            text-align: center;
        }
        .resend-email-button {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
            padding: 0;
            border: 0;
            background: transparent;
            color: #a30c20;
            font: inherit;
            font-size: 13px;
            font-weight: 900;
            cursor: pointer;
        }
        .resend-email-button svg { width: 19px; height: 19px; stroke: currentColor; stroke-width: 2; }
        .resend-email-button:hover:not(:disabled),
        .resend-email-button:focus-visible:not(:disabled) { color: #72000f; outline: 0; }
        .resend-email-button:disabled { color: #a30c20; cursor: not-allowed; }
        .resend-email-section p { margin: 7px 0 0; color: #747b89; font-size: 12px; font-weight: 600; }
        .resend-email-section strong { color: #5d6473; }
        @media (max-width: 420px) {
            .shell { padding: 10px 14px 18px; }
            .panel-head { padding: 22px 24px 9px; }
            .panel-body { padding: 9px 24px 22px; }
            .brand-title { font-size: 15px; }
            .otp-inputs { gap: 6px; }
            .otp-digit { height: 51px; font-size: 22px; }
            .qr-wrap svg { max-width: 100%; height: auto; }
            .method-switch-grid { grid-template-columns: 1fr; }
        }
    </style>
</head>
<body>
    <header class="topbar">
        <a href="{{ url('/') }}" class="brand" aria-label="Clinic home">
            <img src="{{ asset('images/clinic_logo_transparent.png') }}" alt="PUP Taguig Clinic logo">
            <div>
                <div class="brand-title">PUP TAGUIG CLINIC</div>
                <div class="brand-subtitle">Emergency Backup Access</div>
            </div>
        </a>
    </header>

    @php
        $step = $step ?? 'credentials';
    @endphp

    <main class="shell">
        <section class="panel" aria-labelledby="emergency-login-title">
            <div class="panel-head">
                <div class="security-seal" aria-hidden="true">
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="m12 3 7 3.5v5.2c0 4.4-2.98 7.45-7 9.3-4.02-1.85-7-4.9-7-9.3V6.5L12 3Z" />
                        <rect x="9.2" y="10.2" width="5.6" height="5.2" rx=".7" />
                        <path stroke-linecap="round" d="M10.6 10.2V8.9a1.4 1.4 0 0 1 2.8 0v1.3" />
                    </svg>
                </div>

                @if($step === 'credentials')
                    <h1 class="panel-title" id="emergency-login-title">Emergency Access</h1>
                    <p class="panel-copy">Use this portal only when the One Portal or the IdP is unavailable.</p>
                @elseif($step === 'enroll')
                    <h1 class="panel-title" id="emergency-login-title">Set Up Authenticator</h1>
                    <p class="panel-copy">This account needs an authenticator app before it can access the clinic system.</p>
                @elseif($step === 'backup-codes')
                    <h1 class="panel-title" id="emergency-login-title">Save Backup Codes</h1>
                    <p class="panel-copy">These one-time codes can restore emergency access if your authenticator is unavailable.</p>
                @elseif($step === 'enroll-verify')
                    <h1 class="panel-title" id="emergency-login-title">Enter Authenticator Code</h1>
                    <p class="panel-copy">Open your authenticator app and enter the six-digit code for this account.</p>
                @elseif($step === 'method')
                    <h1 class="panel-title" id="emergency-login-title">Verify Identity</h1>
                    <p class="panel-copy">Choose the second factor for this emergency sign-in.</p>
                @elseif($step === 'verify')
                    <h1 class="panel-title" id="emergency-login-title">{{ $mfaMethod === 'backup' ? 'Enter Backup Code' : ($mfaMethod === 'email' ? 'Enter Email Code' : 'Enter Authenticator Code') }}</h1>
                    <p class="panel-copy">{{ $mfaMethod === 'backup' ? 'Enter one saved recovery code to continue.' : ($mfaMethod === 'email' ? 'Enter the six-digit code sent to your recovery email.' : 'Open your authenticator app and enter the six-digit code for this account.') }}</p>
                @endif

                <div class="security-divider" aria-hidden="true">
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="m12 3.75 5.25 2.5v4.25c0 3.7-2.4 6.27-5.25 7.75-2.85-1.48-5.25-4.05-5.25-7.75V6.25L12 3.75Z" /><path stroke-linecap="round" d="M12 8v3.25m0 3.25h.008" /></svg>
                </div>
            </div>

            <div class="panel-body">
                @if($errors->any())
                    <div class="alert alert-error">{{ $errors->first() }}</div>
                @endif

                @if(session('success'))
                    <div class="alert alert-success">{{ session('success') }}</div>
                @endif

                @if($step === 'credentials')
                <form method="POST" action="{{ route('system-admin.emergency-login.submit') }}" autocomplete="off" data-emergency-login-form>
                    @csrf
                    <div class="field">
                        <label for="email">Email address</label>
                        <div class="input-wrap">
                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M21 8.25v7.5A2.25 2.25 0 0 1 18.75 18H5.25A2.25 2.25 0 0 1 3 15.75v-7.5m18 0A2.25 2.25 0 0 0 18.75 6H5.25A2.25 2.25 0 0 0 3 8.25m18 0-7.47 4.662a2.25 2.25 0 0 1-2.42 0L3 8.25" />
                            </svg>
                            <input type="email" name="email" id="email" value="{{ old('email') }}" placeholder="Enter emergency email" required autofocus>
                        </div>
                    </div>

                    <div class="field">
                        <label for="password">Password</label>
                        <div class="input-wrap">
                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M16.5 10.5V7.875a4.5 4.5 0 1 0-9 0V10.5m9 0A2.25 2.25 0 0 1 18.75 12.75v4.5A2.25 2.25 0 0 1 16.5 19.5h-9A2.25 2.25 0 0 1 5.25 17.25v-4.5A2.25 2.25 0 0 1 7.5 10.5m9 0h-9" />
                            </svg>
                            <input type="password" name="password" id="password" class="has-password-toggle" placeholder="Enter emergency password" required>
                            <button type="button" class="password-toggle" id="passwordToggle" aria-label="Show password" title="Show password" aria-pressed="false">
                                <svg data-password-show xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" aria-hidden="true">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M2.25 12s3.75-6.75 9.75-6.75S21.75 12 21.75 12 18 18.75 12 18.75 2.25 12 2.25 12Z" />
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 1 1-6 0 3 3 0 0 1 6 0Z" />
                                </svg>
                                <svg data-password-hide xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" aria-hidden="true" hidden>
                                    <path stroke-linecap="round" stroke-linejoin="round" d="m3 3 18 18M10.6 5.45A9.7 9.7 0 0 1 12 5.25c6 0 9.75 6.75 9.75 6.75a17.7 17.7 0 0 1-2.15 2.92M6.2 6.2C3.67 8.02 2.25 12 2.25 12S6 18.75 12 18.75c1.45 0 2.76-.39 3.92-1.01M9.88 9.88A3 3 0 0 0 14.12 14.12" />
                                </svg>
                            </button>
                        </div>
                    </div>

                    <button type="submit" class="submit" data-emergency-login-submit>
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 9V5.25a3.75 3.75 0 0 0-7.5 0V9m10.5 0H5.25A1.5 1.5 0 0 0 3.75 10.5v7.5A1.5 1.5 0 0 0 5.25 19.5h13.5a1.5 1.5 0 0 0 1.5-1.5v-7.5A1.5 1.5 0 0 0 18.75 9Z" />
                        </svg>
                        Unlock System
                    </button>
                </form>
                @elseif($step === 'enroll')
                    <div class="enrollment-grid">
                        <div>
                            <h2>Scan the QR code</h2>
                            <ol>
                                <li>Open an authenticator app and add a new code.</li>
                                <li>Scan the QR code shown on this page.</li>
                                <li>Keep the app open for the next step.</li>
                            </ol>
                            <p class="inline-note">Make sure the QR code is scanned before you continue.</p>
                        </div>
                        <div class="qr-wrap" aria-label="Authenticator app setup QR code">{!! $qrCodeSvg !!}</div>
                    </div>

                    <form method="POST" action="{{ route('system-admin.emergency-login.enroll.continue') }}">
                        @csrf
                        <button type="submit" class="submit">Next</button>
                    </form>
                @elseif($step === 'backup-codes')
                    <p class="verification-meta">Copy these 10 backup codes now. Each code works once only and will not be shown again after enrollment.</p>
                    <ul class="backup-codes" id="backupCodes" aria-label="One-time backup codes">
                        @foreach($backupCodes as $backupCode)
                            <li class="backup-code">{{ $backupCode }}</li>
                        @endforeach
                    </ul>
                    <button type="button" class="submit" id="copyBackupCodes">
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" aria-hidden="true">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 17.25v3.375c0 .621-.504 1.125-1.125 1.125h-9.75a1.125 1.125 0 0 1-1.125-1.125V7.875c0-.621.504-1.125 1.125-1.125H6.75a9.06 9.06 0 0 1 1.5.124m7.5 10.376h3.375c.621 0 1.125-.504 1.125-1.125V11.25c0-4.46-3.243-8.161-7.5-8.876a9.06 9.06 0 0 0-1.5-.124H9.375c-.621 0-1.125.504-1.125 1.125v3.5m7.5 10.375H9.375a1.125 1.125 0 0 1-1.125-1.125v-9.25m12 6.625v-1.875a3.375 3.375 0 0 0-3.375-3.375h-1.5a1.125 1.125 0 0 1-1.125-1.125v-1.5a3.375 3.375 0 0 0-3.375-3.375H9.75" />
                        </svg>
                        Copy Backup Codes
                    </button>
                    <p class="copy-status" id="copyStatus" aria-live="polite"></p>
                    <form method="POST" action="{{ route('system-admin.emergency-login.enroll.backup-codes.confirm') }}" id="backupCodesContinue" hidden>
                        @csrf
                    </form>
                    <noscript>
                        <form method="POST" action="{{ route('system-admin.emergency-login.enroll.backup-codes.confirm') }}">
                            @csrf
                            <button type="submit" class="submit">Continue</button>
                        </form>
                    </noscript>
                @elseif($step === 'enroll-verify')
                    <form method="POST" action="{{ route('system-admin.emergency-login.enroll.confirm') }}" autocomplete="off" data-mfa-code-form>
                        @csrf
                        <div class="otp-field" data-otp-field data-target="totp_code">
                            <span class="otp-field-label">6-digit code</span>
                            <div class="otp-inputs" aria-label="Six-digit authenticator code">
                                @for($digit = 0; $digit < 6; $digit++)
                                    <input class="otp-digit" type="text" inputmode="numeric" pattern="[0-9]*" autocomplete="{{ $digit === 0 ? 'one-time-code' : 'off' }}" maxlength="1" placeholder="-" aria-label="Digit {{ $digit + 1 }}" {{ $digit === 0 ? 'autofocus' : '' }}>
                                @endfor
                            </div>
                            <input type="hidden" name="totp_code" id="totp_code">
                            <p class="otp-error" data-otp-error aria-live="polite"></p>
                        </div>
                        <p class="otp-expiry" data-totp-expiry><svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M12 6.75v5.25l3 1.5m4.5-1.5a7.5 7.5 0 1 1-15 0 7.5 7.5 0 0 1 15 0Z" /></svg>Code refreshes in <strong data-totp-seconds>30</strong> seconds</p>
                        <button type="submit" class="submit" data-verification-submit>Verify Code</button>
                    </form>
                @elseif($step === 'method')
                    <form method="POST" action="{{ route('system-admin.emergency-login.method.select') }}" class="mfa-options" autocomplete="off">
                        @csrf
                        <button type="submit" name="mfa_method" value="totp" class="mfa-option">
                            <span class="mfa-option-icon" aria-hidden="true"><svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 15.75h.008v.008H12v-.008Zm0-3.75a2.25 2.25 0 1 0-2.25-2.25m2.25 2.25v1.5m0-10.5a9 9 0 1 1 0 18 9 9 0 0 1 0-18Z" /></svg></span>
                            <span class="mfa-option-copy"><strong>Authenticator App</strong><small>Enter the current six-digit code from your authenticator app.</small></span>
                            <span class="mfa-option-arrow" aria-hidden="true"><svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="m9 18 6-6-6-6" /></svg></span>
                        </button>
                        <button type="submit" name="mfa_method" value="email" class="mfa-option">
                            <span class="mfa-option-icon" aria-hidden="true"><svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M21 8.25v7.5A2.25 2.25 0 0 1 18.75 18H5.25A2.25 2.25 0 0 1 3 15.75v-7.5m18 0A2.25 2.25 0 0 0 18.75 6H5.25A2.25 2.25 0 0 0 3 8.25m18 0-7.47 4.662a2.25 2.25 0 0 1-2.42 0L3 8.25" /></svg></span>
                            <span class="mfa-option-copy"><strong>Email OTP</strong><small>Send a one-time code to {{ $emailOtpRecipient }}.</small></span>
                            <span class="mfa-option-arrow" aria-hidden="true"><svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="m9 18 6-6-6-6" /></svg></span>
                        </button>
                        @if($hasBackupCodes)
                            <button type="submit" name="mfa_method" value="backup" class="mfa-option">
                                <span class="mfa-option-icon" aria-hidden="true"><svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 3.75 4.5 7.5v9L12 20.25l7.5-3.75v-9L12 3.75Zm-3 8.25 2 2 4-4" /></svg></span>
                                <span class="mfa-option-copy"><strong>Backup code</strong><small>Use one saved recovery code. It becomes invalid immediately after use.</small></span>
                                <span class="mfa-option-arrow" aria-hidden="true"><svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="m9 18 6-6-6-6" /></svg></span>
                            </button>
                        @endif
                    </form>
                @elseif($step === 'verify')
                    @if($mfaMethod === 'email')
                        <div class="email-otp-notice" role="status">
                            <span class="email-otp-notice-icon" aria-hidden="true"><svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M21 8.25v7.5A2.25 2.25 0 0 1 18.75 18H5.25A2.25 2.25 0 0 1 3 15.75v-7.5m18 0A2.25 2.25 0 0 0 18.75 6H5.25A2.25 2.25 0 0 0 3 8.25m18 0-7.47 4.662a2.25 2.25 0 0 1-2.42 0L3 8.25" /></svg></span>
                            <p>A six-digit code was sent to <strong>{{ $emailOtpRecipient }}</strong><span class="email-otp-separator">&bull;</span>Expires in <strong class="email-otp-expiry">5 minutes</strong></p>
                        </div>
                    @else
                        <p class="verification-meta">
                        @if($mfaMethod === 'backup')
                            Enter one unused backup code exactly as it was saved.
                        @else
                            Enter the current six-digit code from your authenticator app.
                        @endif
                        </p>
                    @endif
                    <form method="POST" action="{{ route('system-admin.emergency-login.verify.submit') }}" autocomplete="off" data-mfa-code-form>
                        @csrf
                        @if($mfaMethod === 'backup')
                            <div class="field">
                                <label for="verification_code">Backup code</label>
                                <div class="input-wrap">
                                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 15.75h.008v.008H12v-.008Zm0-3.75a2.25 2.25 0 1 0-2.25-2.25m2.25 2.25v1.5m0-10.5a9 9 0 1 1 0 18 9 9 0 0 1 0-18Z" /></svg>
                                    <input type="text" inputmode="text" pattern="[A-Za-z0-9-]{8,16}" autocomplete="one-time-code" name="verification_code" id="verification_code" placeholder="ABCD-EFGH" maxlength="16" required autofocus>
                                </div>
                            </div>
                        @else
                            <div class="otp-field" data-otp-field data-target="verification_code">
                                <span class="otp-field-label">6-digit code</span>
                                <div class="otp-inputs" aria-label="Six-digit verification code">
                                    @for($digit = 0; $digit < 6; $digit++)
                                        <input class="otp-digit" type="text" inputmode="numeric" pattern="[0-9]*" autocomplete="{{ $digit === 0 ? 'one-time-code' : 'off' }}" maxlength="1" placeholder="-" aria-label="Digit {{ $digit + 1 }}" {{ $digit === 0 ? 'autofocus' : '' }}>
                                    @endfor
                                </div>
                                <input type="hidden" name="verification_code" id="verification_code">
                                <p class="otp-error" data-otp-error aria-live="polite"></p>
                            </div>
                            @if($mfaMethod === 'totp')
                                <p class="otp-expiry" data-totp-expiry><svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M12 6.75v5.25l3 1.5m4.5-1.5a7.5 7.5 0 1 1-15 0 7.5 7.5 0 0 1 15 0Z" /></svg>Code refreshes in <strong data-totp-seconds>30</strong> seconds</p>
                            @endif
                        @endif
                        <button type="submit" class="submit" data-verification-submit>
                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="m9 12 2 2 4-4m4.5 2a7.5 7.5 0 1 1-15 0 7.5 7.5 0 0 1 15 0Z" /></svg>
                            Verify Code
                        </button>
                    </form>
                    @if($mfaMethod === 'email')
                        <div class="verification-or" aria-hidden="true">OR</div>
                        <div class="method-switch-grid">
                            <a href="{{ route('system-admin.emergency-login.method') }}" class="method-switch-button">
                                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M4.5 4.5h10.125A2.625 2.625 0 0 1 17.25 7.125v9.75a2.625 2.625 0 0 1-2.625 2.625H4.5m0-15v15m0-15H3.375A1.875 1.875 0 0 0 1.5 6.375v11.25A1.875 1.875 0 0 0 3.375 19.5H4.5m6-11.25h.008v.008H10.5V8.25Zm0 3.75h.008v.008H10.5V12Zm0 3.75h.008v.008H10.5V15.75Z" /></svg>
                                Use another method
                            </a>
                            @if($hasBackupCodes)
                                <form method="POST" action="{{ route('system-admin.emergency-login.method.select') }}">
                                    @csrf
                                    <input type="hidden" name="mfa_method" value="backup">
                                    <button type="submit" class="method-switch-button">
                                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M7.5 5.25h9m-9 4.5h9m-9 4.5h9m-12 0H4.5m0-4.5H4.5m0-4.5H4.5" /></svg>
                                        Use backup code
                                    </button>
                                </form>
                            @endif
                        </div>
                        @php
                            $resendMinutes = intdiv((int) $emailOtpResendSeconds, 60);
                            $resendSeconds = (int) $emailOtpResendSeconds % 60;
                        @endphp
                        <div class="resend-email-section" data-email-resend data-resend-seconds="{{ $emailOtpResendSeconds }}">
                            <form method="POST" action="{{ route('system-admin.emergency-login.method.select') }}">
                                @csrf
                                <input type="hidden" name="mfa_method" value="email">
                                <button type="submit" class="resend-email-button" data-email-resend-button {{ $emailOtpResendSeconds > 0 ? 'disabled' : '' }}>
                                    <svg width="100%" height="100%" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg" aria-hidden="true">
                                        <path d="M22 10C22 10 19.995 7.26822 18.3662 5.63824C16.7373 4.00827 14.4864 3 12 3C7.02944 3 3 7.02944 3 12C3 16.9706 7.02944 21 12 21C16.1031 21 19.5649 18.2543 20.6482 14.5M22 10V4M22 10H16" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                                    </svg>
                                    Resend email code
                                </button>
                            </form>
                            <p data-email-resend-status>You can request a new code after <strong data-email-resend-countdown>{{ sprintf('%02d:%02d', $resendMinutes, $resendSeconds) }}</strong></p>
                        </div>
                    @elseif($mfaMethod === 'totp')
                        <div class="verification-or" aria-hidden="true">OR</div>
                        <div class="method-switch-grid">
                            <a href="{{ route('system-admin.emergency-login.method') }}" class="method-switch-button">
                                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M4.5 4.5h10.125A2.625 2.625 0 0 1 17.25 7.125v9.75a2.625 2.625 0 0 1-2.625 2.625H4.5m0-15v15m0-15H3.375A1.875 1.875 0 0 0 1.5 6.375v11.25A1.875 1.875 0 0 0 3.375 19.5H4.5m6-11.25h.008v.008H10.5V8.25Zm0 3.75h.008v.008H10.5V12Zm0 3.75h.008v.008H10.5V15.75Z" /></svg>
                                Use another method
                            </a>
                            @if($hasBackupCodes)
                                <form method="POST" action="{{ route('system-admin.emergency-login.method.select') }}">
                                    @csrf
                                    <input type="hidden" name="mfa_method" value="backup">
                                    <button type="submit" class="method-switch-button">
                                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M7.5 5.25h9m-9 4.5h9m-9 4.5h9m-12 0H4.5m0-4.5H4.5m0-4.5H4.5" /></svg>
                                        Use backup code
                                    </button>
                                </form>
                            @endif
                        </div>
                    @endif
                @endif

                <div class="footnote">
                    <strong>Emergency use only</strong>
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m0-10.036A11.959 11.959 0 0 1 3.598 6 11.99 11.99 0 0 0 3 9.75c0 5.592 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.31-.21-2.57-.598-3.75h-.152c-3.196 0-6.1-1.25-8.25-3.286Zm0 13.036h.008v.008H12v-.008Z" />
                    </svg>
                    All emergency access attempts are securely logged.
                </div>
            </div>
        </section>
    </main>

    <footer class="bottom-bar">
        PUP Taguig Clinic Management System
    </footer>
    @include('partials.system_footer')
    <script>
        document.getElementById('passwordToggle')?.addEventListener('click', function () {
            const passwordInput = document.getElementById('password');
            const showIcon = this.querySelector('[data-password-show]');
            const hideIcon = this.querySelector('[data-password-hide]');
            const shouldShow = passwordInput?.type === 'password';

            if (!passwordInput) return;

            passwordInput.type = shouldShow ? 'text' : 'password';
            showIcon.hidden = shouldShow;
            hideIcon.hidden = !shouldShow;
            this.setAttribute('aria-label', shouldShow ? 'Hide password' : 'Show password');
            this.setAttribute('title', shouldShow ? 'Hide password' : 'Show password');
            this.setAttribute('aria-pressed', shouldShow ? 'true' : 'false');
            passwordInput.focus({ preventScroll: true });
        });

        document.querySelectorAll('[data-otp-field]').forEach((field) => {
            const digits = Array.from(field.querySelectorAll('.otp-digit'));
            const hiddenInput = document.getElementById(field.dataset.target);
            const error = field.querySelector('[data-otp-error]');
            const form = field.closest('form');

            if (!hiddenInput || !form || digits.length !== 6) return;

            const sync = () => {
                hiddenInput.value = digits.map((input) => input.value).join('');
                if (hiddenInput.value.length === 6) error.textContent = '';
            };

            const fillDigits = (startIndex, value) => {
                const incomingDigits = value.replace(/\D/g, '').slice(0, digits.length - startIndex);
                if (incomingDigits === '') {
                    digits[startIndex].value = '';
                    sync();
                    return;
                }

                Array.from(incomingDigits).forEach((digit, offset) => {
                    digits[startIndex + offset].value = digit;
                });
                sync();

                const nextIndex = Math.min(startIndex + incomingDigits.length, digits.length - 1);
                digits[nextIndex].focus();
            };

            digits.forEach((input, index) => {
                input.addEventListener('input', () => {
                    const value = input.value.replace(/\D/g, '');
                    if (value.length > 1) {
                        fillDigits(index, value);
                        return;
                    }

                    input.value = value;
                    sync();
                    if (value !== '' && index < digits.length - 1) {
                        digits[index + 1].focus();
                    }
                });

                input.addEventListener('paste', (event) => {
                    event.preventDefault();
                    fillDigits(index, event.clipboardData?.getData('text') ?? '');
                });

                input.addEventListener('keydown', (event) => {
                    if (event.key === 'Backspace' && input.value === '' && index > 0) {
                        event.preventDefault();
                        digits[index - 1].value = '';
                        digits[index - 1].focus();
                        sync();
                    }

                    if (event.key === 'ArrowLeft' && index > 0) {
                        event.preventDefault();
                        digits[index - 1].focus();
                    }

                    if (event.key === 'ArrowRight' && index < digits.length - 1) {
                        event.preventDefault();
                        digits[index + 1].focus();
                    }
                });
            });

            form.addEventListener('submit', (event) => {
                sync();
                if (hiddenInput.value.length === 6) return;

                event.preventDefault();
                error.textContent = 'Enter all six digits to continue.';
                digits.find((input) => input.value === '')?.focus();
            });
        });

        document.querySelectorAll('[data-totp-expiry]').forEach((expiry) => {
            const seconds = expiry.querySelector('[data-totp-seconds]');
            if (!seconds) return;

            const update = () => {
                seconds.textContent = String(30 - (Math.floor(Date.now() / 1000) % 30));
            };

            update();
            window.setInterval(update, 1000);
        });

        document.querySelectorAll('[data-mfa-code-form]').forEach((form) => {
            form.addEventListener('submit', (event) => {
                if (event.defaultPrevented || form.dataset.submitting === 'true') {
                    event.preventDefault();
                    return;
                }

                form.dataset.submitting = 'true';
                const submitButton = form.querySelector('[data-verification-submit]');
                if (submitButton) submitButton.disabled = true;
            });
        });

        document.querySelectorAll('[data-email-resend]').forEach((section) => {
            const resendButton = section.querySelector('[data-email-resend-button]');
            const status = section.querySelector('[data-email-resend-status]');
            const countdown = section.querySelector('[data-email-resend-countdown]');
            let secondsRemaining = Number(section.dataset.resendSeconds || 0);

            const formatDuration = (seconds) => {
                const minutes = Math.floor(seconds / 60);
                const remainingSeconds = seconds % 60;

                return `${String(minutes).padStart(2, '0')}:${String(remainingSeconds).padStart(2, '0')}`;
            };

            const update = () => {
                if (secondsRemaining <= 0) {
                    resendButton.disabled = false;
                    status.innerHTML = 'You can request a new code now.';
                    return true;
                }

                resendButton.disabled = true;
                countdown.textContent = formatDuration(secondsRemaining);
                secondsRemaining -= 1;

                return false;
            };

            if (!update()) {
                const interval = window.setInterval(() => {
                    if (update()) window.clearInterval(interval);
                }, 1000);
            }
        });

        document.querySelectorAll('[data-emergency-login-form]').forEach((form) => {
            form.addEventListener('submit', (event) => {
                if (form.dataset.submitting === 'true') {
                    event.preventDefault();
                    return;
                }

                form.dataset.submitting = 'true';
                form.querySelector('[data-emergency-login-submit]')?.setAttribute('disabled', 'disabled');
            });
        });

        document.getElementById('copyBackupCodes')?.addEventListener('click', async function () {
            const codes = Array.from(document.querySelectorAll('#backupCodes .backup-code'), (element) => element.textContent.trim()).join('\n');
            const status = document.getElementById('copyStatus');
            this.disabled = true;
            this.setAttribute('aria-busy', 'true');

            try {
                if (navigator.clipboard?.writeText) {
                    await navigator.clipboard.writeText(codes);
                } else {
                    const fallback = document.createElement('textarea');
                    fallback.value = codes;
                    fallback.style.position = 'fixed';
                    fallback.style.opacity = '0';
                    document.body.appendChild(fallback);
                    fallback.select();
                    document.execCommand('copy');
                    fallback.remove();
                }

                status.textContent = 'Backup codes copied. Continuing...';
                window.setTimeout(() => document.getElementById('backupCodesContinue')?.submit(), 400);
            } catch (error) {
                this.disabled = false;
                this.removeAttribute('aria-busy');
                status.textContent = 'Copy was blocked. Copy the codes manually, then use a browser that allows clipboard access.';
            }
        });
    </script>
</body>
</html>
