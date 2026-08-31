<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>PUP Taguig - Online Clinic</title>
    <style>
        /* --- 1. GLOBAL RESET & VARIABLES --- */
        :root {
            --accent: #8B0000;      /* PUP Maroon */
            --accent-dark: #600000;
            --accent-gold: #facc15;
            --white: #ffffff;
            --glass-bg: rgba(255, 255, 255, 0.96);
            --glass-border: rgba(255, 255, 255, 0.42);
            --text-dark: #12202b;
            --text-light: #667085;
            --error-bg: #fee2e2;
            --error-text: #b91c1c;
        }
        * { box-sizing: border-box; margin: 0; padding: 0; font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, "Helvetica Neue", Arial, sans-serif; }
        
        body {
            background-color: #740018;
            background-image:
                linear-gradient(rgba(91, 0, 22, 0.38), rgba(91, 0, 22, 0.50)),
                url('{{ asset("images/PUPBG.jpg") }}');
            background-repeat: no-repeat;
            background-position: center center;
            background-attachment: fixed;
            background-size: cover;
            background-blend-mode: normal, soft-light;
            color: var(--white);
            display: flex;
            flex-direction: column;
            min-height: 100vh;
            overflow-x: hidden;
            position: relative;
            isolation: isolate;
            animation: loginTheme 21s linear infinite;
        }

        body::before,
        body::after {
            content: "";
            display: block;
            position: fixed;
            z-index: 0;
            top: 0;
            width: 100vw;
            height: 100vh;
            width: 100vmax;
            height: 100vmax;
            background: rgba(255, 255, 255, 0.05);
            pointer-events: none;
            transform-origin: center;
            animation: loginBackgroundRotate 90s linear infinite;
        }

        body::after {
            left: 15vw;
        }

        body::before {
            right: 15vw;
            animation-delay: -30s;
            animation-direction: reverse;
        }

        /* --- 2. CENTERED PAGE BRAND --- */
        .login-page-brand {
            display: flex;
            flex-direction: column;
            align-items: center;
            gap: 5px;
            margin-bottom: 18px;
            position: relative;
            z-index: 1;
            color: #ffffff;
        }
        .login-page-brand__logo {
            width: 44px;
            height: 44px;
            border-radius: 50%;
            object-fit: contain;
            filter: drop-shadow(0 5px 10px rgba(91, 0, 0, 0.14));
        }
        .login-page-brand__title {
            margin-top: 2px;
            font-size: 20px;
            line-height: 1.05;
            font-weight: 900;
            letter-spacing: 0.02em;
        }
        .login-page-brand__subtitle {
            color: #ffffff;
            font-size: 12px;
            line-height: 1.2;
            font-weight: 500;
            letter-spacing: 0.04em;
        }
        .login-page-brand__accent {
            width: 38px;
            height: 2px;
            margin-top: 4px;
            border-radius: 999px;
            background: var(--accent-gold);
        }
        /* --- 3. MAIN CONTAINER --- */
        .lp-container {
            flex: 1;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            padding: 28px 16px;
            text-align: center;
            width: 100%;
            position: relative;
            z-index: 1;
        }

        .login-box {
            background: linear-gradient(145deg, rgba(82, 0, 20, 0.82), rgba(121, 10, 36, 0.68));
            padding: 30px 32px 28px;
            border-radius: 18px;
            width: 100%;
            max-width: 430px;
            color: #ffffff;
            border: 1px solid rgba(250, 204, 21, 0.36);
            box-shadow: 0 24px 58px rgba(45, 0, 12, 0.32), 0 4px 14px rgba(15, 23, 42, 0.12);
            backdrop-filter: blur(18px);
            animation: slideUp 0.7s ease-out;
            overflow: hidden;
        }

        .login-hero {
            position: relative;
            padding: 0;
            margin: 0;
            background: transparent;
            color: var(--text-dark);
            border: 0;
            text-align: center;
        }
        .login-security-icon {
            width: 72px;
            height: 72px;
            margin: 0 auto 17px;
            display: grid;
            place-items: center;
            border-radius: 50%;
            background: rgba(255, 255, 255, 0.08);
            color: #ffe4e8;
            border: 1px solid rgba(250, 204, 21, 0.28);
        }
        .login-security-icon svg {
            width: 42px;
            height: 42px;
            fill: none;
            stroke: currentColor;
            stroke-width: 1.8;
            stroke-linecap: round;
            stroke-linejoin: round;
        }
        .login-box h2 {
            color: #ffffff;
            font-weight: 900;
            font-size: 29px;
            line-height: 1.1;
            margin-bottom: 9px;
        }
        .login-box p {
            color: rgba(255, 255, 255, 0.78);
            font-size: 14px;
            line-height: 1.55;
            margin-bottom: 0;
            max-width: 44ch;
        }
        .login-box p.login-hero-copy {
            max-width: 33ch;
            text-align: center;
            margin-left: auto;
            margin-right: auto;
        }
        .login-divider {
            width: 100%;
            margin: 21px 0;
            display: flex;
            align-items: center;
            gap: 13px;
        }
        .login-divider::before,
        .login-divider::after {
            content: "";
            flex: 1;
            height: 1px;
            background: rgba(250, 204, 21, 0.24);
        }
        .login-divider svg {
            width: 18px;
            height: 18px;
            fill: none;
            stroke: #e5a900;
            stroke-width: 1.8;
            stroke-linecap: round;
            stroke-linejoin: round;
            flex: 0 0 auto;
        }

        /* Error Alert Styling */
        .alert-error {
            background-color: var(--error-bg);
            color: var(--error-text);
            padding: 12px;
            border-radius: 10px;
            margin-bottom: 20px;
            font-size: 13px;
            text-align: left;
            border: 1px solid #fecaca;
        }
        .alert-error ul { list-style: none; margin: 0; padding: 0; }

        /* Form Elements */
        .form-group { margin-bottom: 15px; text-align: left; }
        .form-group label {
            display: block;
            font-size: 11px;
            font-weight: 900;
            color: #fff3f3;
            margin-bottom: 6px;
            letter-spacing: 0.08em;
            text-transform: uppercase;
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
        .form-group input, .form-group select {
            width: 100%;
            padding: 13px 15px;
            border: 1px solid rgba(139, 0, 0, 0.18);
            border-radius: 14px;
            font-size: 14px;
            background: linear-gradient(180deg, #ffffff, #fff8f6);
            transition: border-color 0.2s ease, box-shadow 0.2s ease, transform 0.2s ease;
        }
        .input-wrap .form-control,
        .input-wrap input {
            padding-left: 44px;
        }
        .form-group input:focus, .form-group select:focus {
            outline: none;
            border-color: var(--accent);
            box-shadow: 0 0 0 4px rgba(139, 0, 0, 0.08);
            transform: translateY(-1px);
        }
        .form-row { display: grid; grid-template-columns: 1fr 1fr; gap: 12px; }
        .mini-form-row { display: grid; grid-template-columns: 1fr 1fr; gap: 12px; }

        .login-box form .btn-submit,
        .idp-login-wrap .btn-submit {
            width: 100%;
            min-height: 52px;
            padding: 14px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 11px;
            background: linear-gradient(135deg, #5e0000, #8b0000 60%, #a61b1b);
            color: white;
            border: 1px solid rgba(250, 204, 21, 0.38);
            border-radius: 8px;
            text-decoration: none;
            text-align: center;
            font-size: 14px;
            font-weight: 900;
            cursor: pointer;
            margin-top: 10px;
            box-shadow: 0 14px 28px rgba(41, 0, 11, 0.38), 0 0 0 1px rgba(250, 204, 21, 0.06);
            transition: transform 0.18s ease, box-shadow 0.18s ease, filter 0.18s ease, background 0.18s ease, color 0.18s ease;
        }
        .idp-login-wrap .btn-submit {
            margin-top: 0;
        }
        .btn-submit svg {
            width: 20px;
            height: 20px;
            fill: none;
            stroke: currentColor;
            stroke-width: 1.9;
            stroke-linecap: round;
            stroke-linejoin: round;
            flex: 0 0 auto;
        }
        .login-box form .btn-submit:hover,
        .login-box form .btn-submit:focus,
        .idp-login-wrap .btn-submit:hover,
        .idp-login-wrap .btn-submit:focus {
            background: var(--accent-gold);
            color: var(--accent);
            transform: translateY(-1px);
            box-shadow: 0 20px 32px rgba(91,0,0,0.28);
            filter: brightness(1.02);
        }
        .login-box form .btn-submit:hover svg,
        .login-box form .btn-submit:focus svg,
        .idp-login-wrap .btn-submit:hover svg,
        .idp-login-wrap .btn-submit:focus svg {
            stroke: var(--accent);
        }

        .idp-login-wrap {
            display: flex;
            flex-direction: column;
            align-items: center;
            gap: 20px;
            margin-top: 0;
        }
        .idp-login-note {
            display: flex;
            align-items: center;
            gap: 12px;
            font-size: 13px;
            color: rgba(255, 255, 255, 0.76);
            max-width: 330px;
            margin: 0 auto;
            text-align: left;
        }
        .idp-login-note svg {
            width: 25px;
            height: 25px;
            fill: none;
            stroke: #facc15;
            stroke-width: 1.8;
            stroke-linecap: round;
            stroke-linejoin: round;
            flex: 0 0 auto;
        }

        .idp-fallback-card {
            margin: 0;
            padding: 6px 4px 2px;
            border-radius: 0;
            background: transparent;
            border: 0;
            box-shadow: none;
            color: #ffffff;
        }
        .idp-fallback-icon {
            width: 92px;
            height: 92px;
            margin: 0 auto 18px;
            display: grid;
            place-items: center;
            border-radius: 50%;
            background: rgba(255, 255, 255, 0.08);
            color: #ffe4e8;
            border: 1px solid rgba(250, 204, 21, 0.28);
        }
        .idp-fallback-icon svg {
            width: 52px;
            height: 52px;
            stroke: currentColor;
            stroke-width: 1.8;
            fill: none;
        }
        .idp-fallback-card h3 {
            margin: 0 auto 14px;
            max-width: 360px;
            color: #ffffff;
            font-size: 25px;
            font-weight: 950;
            line-height: 1.16;
        }
        .idp-fallback-divider {
            width: 100%;
            height: 1px;
            margin: 18px 0;
            background: linear-gradient(90deg, transparent, rgba(250, 204, 21, 0.34), transparent);
        }
        .idp-fallback-card p {
            max-width: 360px;
            margin: 0 auto;
            color: rgba(255, 255, 255, 0.76);
            font-size: 14px;
            line-height: 1.7;
        }
        .idp-fallback-action {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 10px;
            width: 100%;
            min-height: 52px;
            margin-top: 22px;
            padding: 14px 18px;
            border-radius: 14px;
            border: 1px solid rgba(250, 204, 21, 0.38);
            background: linear-gradient(135deg, #5e0000, #8b0000 60%, #a61b1b);
            color: #ffffff;
            text-decoration: none;
            font-size: 15px;
            font-weight: 950;
            box-shadow: 0 14px 28px rgba(41, 0, 11, 0.38), 0 0 0 1px rgba(250, 204, 21, 0.06);
            transition: transform 0.18s ease, box-shadow 0.18s ease, background 0.18s ease, color 0.18s ease;
        }
        .idp-fallback-action:hover,
        .idp-fallback-action:focus {
            background: var(--accent-gold);
            color: var(--accent);
            transform: translateY(-1px);
            box-shadow: 0 20px 32px rgba(91,0,0,0.28);
            outline: none;
        }
        .idp-fallback-action svg {
            width: 19px;
            height: 19px;
            stroke: currentColor;
            stroke-width: 2.4;
            fill: none;
        }
        .dev-login-grid {
            display: grid;
            grid-template-columns: repeat(2, minmax(0, 1fr));
            gap: 14px;
            margin-top: 26px;
        }
        .dev-login-card {
            background: linear-gradient(180deg, rgba(255,255,255,0.98), rgba(248,250,252,0.96));
            border-radius: 24px;
            padding: 22px;
            text-align: left;
            border: 1px solid rgba(139, 0, 0, 0.12);
            box-shadow: 0 18px 36px rgba(15, 23, 42, 0.08);
            color: #111827;
            display: flex;
            flex-direction: column;
            justify-content: space-between;
            min-height: 220px;
        }
        .dev-login-chip {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            padding: 8px 12px;
            border-radius: 999px;
            background: rgba(139, 0, 0, 0.08);
            color: #8B0000;
            font-size: 11px;
            font-weight: 800;
            text-transform: uppercase;
            letter-spacing: 0.04em;
            margin-bottom: 18px;
        }
        .dev-login-icon {
            width: 52px;
            height: 52px;
            border-radius: 18px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            background: linear-gradient(135deg, #5e0000, #8B0000);
            color: #ffffff;
            margin-bottom: 18px;
        }
        .dev-login-title {
            margin: 0 0 14px;
            font-size: 22px;
            font-weight: 800;
            line-height: 1.1;
        }
        .dev-login-copy {
            color: #475569;
            font-size: 14px;
            line-height: 1.7;
            margin-bottom: 18px;
        }
        .dev-login-cta {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            width: 100%;
            padding: 14px 0;
            border-radius: 14px;
            background: #f8fafc;
            color: #111827;
            font-weight: 700;
            border: 1px solid rgba(139, 0, 0, 0.14);
            cursor: not-allowed;
            opacity: 0.75;
            text-decoration: none;
        }
        .dev-login-cta span {
            display: block;
            width: 100%;
        }

        /* --- 4. MODAL STYLES --- */
        .modal-overlay {
            position: fixed;
            top: 0; left: 0; width: 100%; height: 100%;
            background: rgba(0,0,0,0.8);
            display: none;
            justify-content: center;
            align-items: center;
            z-index: 1000;
            backdrop-filter: blur(8px);
        }
        .modal-content {
            background: linear-gradient(180deg, rgba(255,255,255,0.98), rgba(248,250,252,0.98));
            color: var(--text-dark);
            width: 95%;
            max-width: 600px;
            padding: 40px;
            border-radius: 26px;
            position: relative;
            max-height: none;
            overflow-y: visible;
            border: 1px solid rgba(139, 0, 0, 0.12);
            box-shadow: 0 28px 70px rgba(0,0,0,0.28);
        }
        .modal-close { position: absolute; top: 20px; right: 20px; cursor: pointer; font-size: 28px; color: var(--text-light); }
        .register-hero {
            display: grid;
            gap: 10px;
            padding: 18px 18px 16px;
            margin: -40px -40px 22px;
            background: linear-gradient(135deg, rgba(91,0,0,0.98), rgba(127,29,29,0.98) 55%, rgba(168,18,18,0.98));
            color: #ffffff;
            border-bottom: 1px solid rgba(255,255,255,0.12);
        }
        .register-hero-top {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 12px;
            flex-wrap: wrap;
        }
        .register-kicker {
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
        }
        .register-hero h2 {
            margin: 0;
            color: #ffffff;
            font-weight: 900;
            font-size: 28px;
            line-height: 1.1;
        }
        .register-hero p {
            margin: 0;
            color: rgba(255,255,255,0.88);
            font-size: 14px;
            line-height: 1.7;
            max-width: 50ch;
            text-align: left;
        }
        .register-grid {
            display: grid;
            gap: 14px;
        }
        .register-submit {
            width: 100%;
            min-height: 54px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 10px;
            margin-top: 12px;
            padding: 14px 18px;
            border: 1px solid rgba(250, 204, 21, 0.38);
            border-radius: 16px;
            background: linear-gradient(135deg, #5e0000, #8b0000 60%, #a61b1b);
            color: #ffffff;
            font-size: 15px;
            font-weight: 900;
            letter-spacing: 0.02em;
            cursor: pointer;
            box-shadow: 0 14px 28px rgba(41, 0, 11, 0.38), 0 0 0 1px rgba(250, 204, 21, 0.06);
            transition: transform 0.18s ease, box-shadow 0.18s ease, background 0.18s ease, color 0.18s ease;
        }
        .register-submit:hover,
        .register-submit:focus-visible {
            transform: translateY(-1px);
            background: linear-gradient(135deg, #fff2a8, #facc15);
            color: #7b1113;
            box-shadow: 0 22px 36px rgba(91,0,0,0.26);
            outline: none;
        }
        .register-submit svg {
            width: 18px;
            height: 18px;
            stroke: currentColor;
            stroke-width: 2;
            flex: 0 0 auto;
        }

        .switch-form { margin-top: 20px; font-size: 14px; color: rgba(255, 255, 255, 0.74); }
        .switch-form span { color: #facc15; cursor: pointer; font-weight: 700; text-decoration: underline; }

        .lp-foot {
            background: rgba(17, 24, 39, 0.92);
            border-top: 2px solid #8B0000;
            text-align: center;
            padding: 14px 16px;
            font-size: 13px;
            color: #f8fafc;
            display: flex;
            justify-content: center;
            align-items: center;
            gap: 12px;
            flex-wrap: wrap;
        }
        .lp-foot a {
            color: #ffffff;
            text-decoration: none;
            font-weight: 600;
        }
        .lp-foot a:hover {
            text-decoration: underline;
        }
        .lp-foot .sep {
            color: rgba(255, 255, 255, 0.5);
        }

        .login-loading-overlay {
            position: fixed;
            inset: 0;
            z-index: 2200;
            display: none;
            align-items: center;
            justify-content: center;
            background: rgba(15, 23, 42, 0.68);
            backdrop-filter: blur(4px);
        }
        .login-loading-overlay.show {
            display: flex;
        }
        .login-loading-card {
            text-align: center;
            color: #ffffff;
            background: rgba(9, 14, 19, 0.72);
            border: 1px solid rgba(255, 255, 255, 0.08);
            border-radius: 22px;
            padding: 24px 28px;
            min-width: 180px;
            box-shadow: 0 20px 50px rgba(0,0,0,0.3);
        }
        .login-loading-logo {
            width: 72px;
            height: 72px;
            object-fit: cover;
            border-radius: 50%;
            border: 2px solid rgba(255, 255, 255, 0.45);
            background: #ffffff;
            padding: 2px;
            animation: loginLogoBounce 0.85s ease-in-out infinite;
            margin-bottom: 8px;
        }
        .login-loading-text {
            font-size: 14px;
            font-weight: 700;
            letter-spacing: 0.03em;
        }

        @media (max-width: 768px) {
            body {
                background-attachment: scroll;
            }

            .login-page-brand {
                margin-bottom: 14px;
            }

            .lp-container {
                padding: 14px;
            }

            .login-box {
                padding: 28px 18px;
                border-radius: 20px;
                max-width: 100%;
            }

            .form-row,
            .mini-form-row {
                grid-template-columns: 1fr;
                gap: 10px;
            }

            .modal-overlay {
                align-items: flex-end;
            }

            .modal-content {
                width: 100%;
                max-width: 100%;
                padding: 24px 16px 20px;
                border-radius: 18px 18px 0 0;
                max-height: none;
                overflow-y: visible;
            }

            .modal-close {
                top: 10px;
                right: 14px;
            }
        }

        @media (max-width: 420px) {
            .login-page-brand__title {
                font-size: 18px;
            }

            .btn-submit {
                padding: 12px;
            }
        }

        @keyframes loginLogoBounce {
            0%, 100% { transform: translateY(0); }
            50% { transform: translateY(-10px); }
        }

        @keyframes loginTheme {
            0% { background-color: #740018; }
            16% { background-color: #8f1730; }
            33% { background-color: #aa2342; }
            50% { background-color: #7b001c; }
            66% { background-color: #bd3655; }
            83% { background-color: #961d34; }
            100% { background-color: #740018; }
        }

        @keyframes loginBackgroundRotate {
            0% { transform: rotate(0deg); }
            100% { transform: rotate(360deg); }
        }

        @media (prefers-reduced-motion: reduce) {
            body {
                animation: none;
                background-color: #7b001c;
            }

            body::before,
            body::after {
                animation: none;
            }
        }

        @keyframes slideUp { from { opacity: 0; transform: translateY(30px); } to { opacity: 1; transform: translateY(0); } }
    </style>
</head>
<body>

  <main class="lp-container">
    <div class="login-page-brand" aria-label="PUP Taguig Clinic Centralized Access">
        <img src="{{ asset('images/clinic_logo_transparent.png') }}?v={{ filemtime(public_path('images/clinic_logo_transparent.png')) }}" alt="PUP Taguig Clinic logo" class="login-page-brand__logo">
        <div class="login-page-brand__title">PUP TAGUIG CLINIC</div>
        <div class="login-page-brand__subtitle">CENTRALIZED ACCESS</div>
        <span class="login-page-brand__accent" aria-hidden="true"></span>
    </div>

    <div class="login-box">
        @php
            $portalLoginUrl = route('login.portal');
            $localLoginEnabled = $localLoginEnabled ?? false;
            $idpUnavailable = ($idpUnavailable ?? false) || request()->boolean('idp_error') || $errors->has('idp');
        @endphp

        @unless($idpUnavailable)
            <div class="login-hero">
                <div class="login-security-icon" aria-hidden="true">
                    <svg viewBox="0 0 48 48">
                        <path d="M24 5.5 38 11v10.8c0 9.2-5.7 16.5-14 20.7-8.3-4.2-14-11.5-14-20.7V11L24 5.5Z" />
                        <rect x="18" y="21" width="12" height="10" rx="2" />
                        <path d="M20.5 21v-3a3.5 3.5 0 0 1 7 0v3M24 25v2.5" />
                    </svg>
                </div>
                <h2>Clinic Portal</h2>
                <p class="login-hero-copy">Access the PUP Taguig Clinic using your campus account.</p>
            </div>

            <div class="login-divider" aria-hidden="true">
                <svg viewBox="0 0 24 24">
                    <path d="M12 3 19 6v5.4c0 4.6-2.8 8.2-7 10.3-4.2-2.1-7-5.7-7-10.3V6l7-3Z" />
                    <path d="m9.3 12 1.8 1.8 3.8-4" />
                </svg>
            </div>
        @endunless

        @if ($errors->any() && ! $idpUnavailable)
            <div class="alert-error">
                <ul>
                    @foreach ($errors->all() as $error)
                        <li>⚠️ {{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        @if($idpUnavailable)
            <section class="idp-fallback-card" aria-labelledby="idpFallbackTitle">
                <div class="idp-fallback-icon" aria-hidden="true">
                    <svg viewBox="0 0 24 24">
                        <path d="M17.5 17H8.25a4.75 4.75 0 0 1-.58-9.46 6 6 0 0 1 10.88 2.35A3.75 3.75 0 0 1 17.5 17Z" />
                        <path d="m4 4 16 16" />
                        <path d="M12 8v4" />
                        <path d="M12 15h.01" />
                    </svg>
                </div>
                <h3 id="idpFallbackTitle">Identity Provider Temporarily Unavailable</h3>
                <div class="idp-fallback-divider"></div>
                <p>
                    We’re having trouble connecting to the IdP sign-in service. Please try again in a few minutes.
                </p>
                <a href="{{ $portalLoginUrl }}" class="idp-fallback-action">
                    <svg viewBox="0 0 24 24" aria-hidden="true">
                        <path d="M20 12a8 8 0 1 1-2.34-5.66" />
                        <path d="M20 4v6h-6" />
                    </svg>
                    Try Again
                </a>
            </section>
        @elseif(config('services.idp.enabled'))
            <div class="idp-login-wrap">
                <a href="{{ $portalLoginUrl }}" class="btn-submit">
                    <svg viewBox="0 0 24 24" aria-hidden="true">
                        <path d="M14 8V5a2 2 0 0 0-2-2H5a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h7a2 2 0 0 0 2-2v-3" />
                        <path d="M10 12h11M18 9l3 3-3 3" />
                    </svg>
                    <span>Continue with One Portal</span>
                </a>
                <p class="idp-login-note">
                    <svg viewBox="0 0 24 24" aria-hidden="true">
                        <path d="M12 3 19 6v5.4c0 4.6-2.8 8.2-7 10.3-4.2-2.1-7-5.7-7-10.3V6l7-3Z" />
                        <path d="m9.3 12 1.8 1.8 3.8-4" />
                    </svg>
                    <span>You'll be redirected to the campus Identity Provider to sign in securely.</span>
                </p>
            </div>

            @env('local')
                <section class="dev-login-grid" aria-label="Static developer login options">
                    <div class="dev-login-card">
                        <div>
                            <div class="dev-login-chip">Dev Login</div>
                            <div class="dev-login-icon">S</div>
                            <h3 class="dev-login-title">Student</h3>
                            <p class="dev-login-copy">Static student login placeholder. This option is for local preview only and does not perform authentication yet.</p>
                        </div>
                        <a href="#" class="dev-login-cta" onclick="event.preventDefault();">
                            Student Login
                        </a>
                    </div>

                    <div class="dev-login-card">
                        <div>
                            <div class="dev-login-chip">Dev Login</div>
                            <div class="dev-login-icon">A</div>
                            <h3 class="dev-login-title">Admin</h3>
                            <p class="dev-login-copy">Static admin login placeholder. This option is for local preview only and does not perform authentication yet.</p>
                        </div>
                        <a href="#" class="dev-login-cta" onclick="event.preventDefault();">
                            Admin Login
                        </a>
                    </div>
                </section>
            @endenv
        @elseif($localLoginEnabled)
            <form id="loginForm" action="{{ url('/login-action') }}" method="POST">
                @csrf
                <div class="form-group">
                    <label>EMAIL ADDRESS</label>
                    <div class="input-wrap">
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M21 8.25v7.5A2.25 2.25 0 0 1 18.75 18H5.25A2.25 2.25 0 0 1 3 15.75v-7.5m18 0A2.25 2.25 0 0 0 18.75 6H5.25A2.25 2.25 0 0 0 3 8.25m18 0-7.47 4.662a2.25 2.25 0 0 1-2.42 0L3 8.25" />
                        </svg>
                        <input type="email" name="email" value="{{ old('email') }}" placeholder="Enter your email address" required>
                    </div>
                </div>

                <div class="form-group">
                    <label>PASSWORD</label>
                    <div class="input-wrap">
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M16.5 10.5V7.875a4.5 4.5 0 0 0-9 0V10.5m-.75 0A2.25 2.25 0 0 0 4.5 12.75v5.25A2.25 2.25 0 0 0 6.75 20.25h10.5a2.25 2.25 0 0 0 2.25-2.25v-5.25A2.25 2.25 0 0 0 17.25 10.5h-10.5Z" />
                        </svg>
                        <input type="password" name="password" placeholder="Enter your password" required>
                    </div>
                </div>

                <button id="loginSubmitBtn" type="submit" class="btn-submit">Login to Portal</button>
            </form>

            <div class="switch-form">
                Don't have an account? <span onclick="openModal('registerModal')">Create Account</span>
            </div>
        @else
            <div class="idp-login-wrap">
                <p class="idp-login-note">
                    Local login is only available on the staging clinic site.
                </p>
            </div>
        @endif
    </div>
  </main>

  @if(! config('services.idp.enabled') && $localLoginEnabled)
      <div id="registerModal" class="modal-overlay">
          <div class="modal-content">
              <span class="modal-close" onclick="closeModal('registerModal')">&times;</span>
              <div class="register-hero">
                  <div class="register-hero-top">
                      <div class="register-kicker">Local Registration</div>
                  </div>
                  <h2>Create Account</h2>
                  <p>Complete the quick local registration form to create your clinic account.</p>
              </div>
              <form action="{{ url('/register-action') }}" method="POST">
                  @csrf
                  <div class="form-row">
                      <div class="form-group">
                          <label>FIRST NAME</label>
                          <input type="text" name="first_name" required>
                      </div>
                      <div class="form-group">
                          <label>LAST NAME</label>
                          <input type="text" name="last_name" required>
                      </div>
                  </div>

                  <div class="register-grid">
                      <div class="form-group">
                          <label>EMAIL ADDRESS</label>
                          <input type="email" name="email" required>
                      </div>

                      <div class="form-group">
                          <label>CLINIC ROLE</label>
                          <select name="clinic_role" required>
                              <option value="" disabled {{ old('clinic_role') ? '' : 'selected' }}>Select clinic role</option>
                              <option value="student" {{ old('clinic_role') === 'student' ? 'selected' : '' }}>
                                  Student
                              </option>
                              <option value="faculty" {{ old('clinic_role') === 'faculty' ? 'selected' : '' }}>
                                  Faculty
                              </option>
                              <option value="guest" {{ old('clinic_role') === 'guest' ? 'selected' : '' }}>
                                  Guest
                              </option>
                              <option value="admin_clinic_staff" {{ old('clinic_role') === 'admin_clinic_staff' ? 'selected' : '' }}>
                                  Admin - Clinic Staff
                              </option>
                              <option value="admin_designee" {{ old('clinic_role') === 'admin_designee' ? 'selected' : '' }}>
                                  Admin - Designee
                              </option>
                              <option value="student_assistant" {{ old('clinic_role') === 'student_assistant' ? 'selected' : '' }}>
                                  Admin - Student Assistant
                              </option>
                              <option value="super_admin" {{ old('clinic_role') === 'super_admin' ? 'selected' : '' }}>
                                  Superadmin
                              </option>
                          </select>
                      </div>

                      <div class="form-row">
                          <div class="form-group">
                              <label>PASSWORD</label>
                              <input type="password" name="password" required>
                          </div>
                          <div class="form-group">
                              <label>CONFIRM PASSWORD</label>
                              <input type="password" name="password_confirmation" required>
                          </div>
                      </div>
                  </div>

                  <button type="submit" class="register-submit">
                      <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" aria-hidden="true">
                          <path stroke-linecap="round" stroke-linejoin="round" d="M10 17l5-5-5-5" />
                          <path stroke-linecap="round" stroke-linejoin="round" d="M15 12H4" />
                      </svg>
                      Register Account
                  </button>
              </form>
          </div>
      </div>
  @endif

  <div id="loginLoadingOverlay" class="login-loading-overlay" aria-hidden="true">
      <div class="login-loading-card">
          <img src="{{ asset('images/clinic_logo_transparent.png') }}?v={{ filemtime(public_path('images/clinic_logo_transparent.png')) }}" alt="Loading" class="login-loading-logo">
          <div class="login-loading-text">Signing in...</div>
      </div>
  </div>

  <script>
      function openModal(id) { document.getElementById(id).style.display = 'flex'; }
      function closeModal(id) { document.getElementById(id).style.display = 'none'; }

      window.onclick = function(event) {
          if (event.target.className === 'modal-overlay') {
              event.target.style.display = 'none';
          }
      }

      (function () {
          const loginForm = document.getElementById('loginForm');
          const loginSubmitBtn = document.getElementById('loginSubmitBtn');
          const loadingOverlay = document.getElementById('loginLoadingOverlay');
          let isSubmitting = false;

          if (!loginForm || !loginSubmitBtn || !loadingOverlay) {
              return;
          }

          function showLoadingAndSubmit(event) {
              if (event) {
                  event.preventDefault();
              }
              if (isSubmitting) {
                  return;
              }
              if (typeof loginForm.checkValidity === 'function' && !loginForm.checkValidity()) {
                  loginForm.reportValidity();
                  return;
              }

              isSubmitting = true;
              loadingOverlay.classList.add('show');
              loadingOverlay.setAttribute('aria-hidden', 'false');
              loginSubmitBtn.disabled = true;
              loginSubmitBtn.textContent = 'Signing in...';

              requestAnimationFrame(function () {
                  setTimeout(function () {
                      HTMLFormElement.prototype.submit.call(loginForm);
                  }, 260);
              });
          }

          loginSubmitBtn.addEventListener('click', showLoadingAndSubmit);
          loginForm.addEventListener('submit', showLoadingAndSubmit);
      })();
  </script>

</body>
</html>
