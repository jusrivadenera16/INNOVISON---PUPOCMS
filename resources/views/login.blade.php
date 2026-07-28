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
            background: linear-gradient(rgba(15, 27, 38, 0.75), rgba(15, 27, 38, 0.85)), 
                        url('{{ asset("images/PUPBG.jpg") }}') no-repeat center center fixed;
            background-size: cover;
            color: var(--white);
            display: flex;
            flex-direction: column;
            min-height: 100vh;
        }

        /* --- 2. LOGO HEADER --- */
        .logo-header {
            background: rgba(91, 0, 0, 0.92);
            padding: 18px 24px;
            box-shadow: 0 4px 16px rgba(0,0,0,0.22);
            display: flex;
            justify-content: center;
            position: relative;
            z-index: 10;
            backdrop-filter: blur(10px);
            overflow: hidden;
        }
        .logo-header::after,
        .login-hero::after {
            content: "";
            position: absolute;
            inset: 0;
            background: linear-gradient(110deg, transparent 35%, rgba(250, 204, 21, 0.18) 50%, transparent 65%);
            transform: translateX(-140%);
            pointer-events: none;
        }
        .logo-header::after { animation: headerSweep 4s ease-in-out infinite; }
        .login-hero::after { animation: headerSweep 4s ease-in-out infinite; }
        .logo-header::after,
        .login-hero::after {
            z-index: 0;
        }
        .logo-header > *,
        .login-hero > * {
            position: relative;
            z-index: 1;
        }
        .logo-brand { display: flex; align-items: center; gap: 12px; text-decoration: none; }
        .logo-icon { width: 48px; height: 48px; border-radius: 50%; border: 2px solid rgba(255,255,255,0.2); object-fit: cover; }
        .logo-text { color: var(--white); line-height: 1.1; }
        .logo-title { font-weight: 800; font-size: 18px; letter-spacing: 0.5px; }
        .logo-subtitle { font-size: 11px; text-transform: uppercase; letter-spacing: 1px; opacity: 0.9; }

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
        }

        .login-box {
            background: linear-gradient(180deg, rgba(255,255,255,0.97), rgba(248,250,252,0.95));
            padding: 34px;
            border-radius: 26px;
            width: 100%;
            max-width: 520px;
            color: var(--text-dark);
            border: 1px solid var(--glass-border);
            box-shadow: 0 28px 70px rgba(0,0,0,0.35);
            animation: slideUp 0.7s ease-out;
            overflow: hidden;
        }

        .login-hero {
            position: relative;
            overflow: hidden;
            padding: 22px 22px 20px;
            margin: -34px -34px 24px;
            background: linear-gradient(135deg, rgba(91,0,0,0.98), rgba(127,29,29,0.98) 55%, rgba(168,18,18,0.98));
            color: #fff;
            border-bottom: 1px solid rgba(255,255,255,0.12);
        }
        .login-hero-top {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 12px;
            margin-bottom: 14px;
        }
        .login-hero-badge {
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
        .login-hero-badge span {
            width: 7px;
            height: 7px;
            border-radius: 50%;
            background: var(--accent-gold);
            box-shadow: 0 0 0 4px rgba(250, 204, 21, 0.15);
        }
        .login-box h2 {
            color: #fff;
            font-weight: 900;
            font-size: 32px;
            line-height: 1.05;
            margin-bottom: 10px;
        }
        .login-box p {
            color: rgba(255,255,255,0.88);
            font-size: 14px;
            line-height: 1.7;
            margin-bottom: 0;
            max-width: 44ch;
        }
        .login-box p.login-hero-copy {
            max-width: 100%;
            text-align: center;
            margin-left: auto;
            margin-right: auto;
        }
        .login-subline {
            margin-top: 14px;
            display: flex;
            gap: 8px;
            flex-wrap: wrap;
        }
        .login-chip {
            padding: 7px 10px;
            border-radius: 999px;
            background: rgba(255,255,255,0.12);
            border: 1px solid rgba(255,255,255,0.16);
            color: rgba(255,255,255,0.9);
            font-size: 11px;
            font-weight: 700;
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
            color: #7a1b1b;
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
            background: linear-gradient(135deg, #5e0000, #8b0000 60%, #a61b1b);
            color: white;
            border: none;
            border-radius: 14px;
            font-weight: 900;
            cursor: pointer;
            margin-top: 10px;
            box-shadow: 0 16px 26px rgba(91,0,0,0.24);
            transition: transform 0.18s ease, box-shadow 0.18s ease, filter 0.18s ease, background 0.18s ease, color 0.18s ease;
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
            gap: 10px;
            margin-top: 18px;
        }
        .idp-login-note {
            font-size: 13px;
            color: var(--text-light);
            max-width: 360px;
            margin: 0 auto;
        }

        .idp-fallback-card {
            margin-top: 18px;
            padding: 26px 24px;
            border-radius: 24px;
            background:
                radial-gradient(circle at 50% 0%, rgba(139, 0, 0, 0.08), transparent 42%),
                linear-gradient(180deg, #ffffff, #fbfbfc);
            border: 1px solid rgba(139, 0, 0, 0.12);
            box-shadow: 0 18px 42px rgba(15, 23, 42, 0.08);
            color: var(--text-dark);
        }
        .idp-fallback-icon {
            width: 92px;
            height: 92px;
            margin: 0 auto 18px;
            display: grid;
            place-items: center;
            border-radius: 50%;
            background: #fff0f0;
            color: #bd3030;
            border: 1px solid rgba(189, 48, 48, 0.12);
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
            color: #2b0f14;
            font-size: 25px;
            font-weight: 950;
            line-height: 1.16;
        }
        .idp-fallback-divider {
            width: 100%;
            height: 1px;
            margin: 18px 0;
            background: linear-gradient(90deg, transparent, rgba(148, 163, 184, 0.42), transparent);
        }
        .idp-fallback-card p {
            max-width: 360px;
            margin: 0 auto;
            color: #5f6877;
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
            background: linear-gradient(135deg, #5e0000, #8b0000 60%, #a61b1b);
            color: #ffffff;
            text-decoration: none;
            font-size: 15px;
            font-weight: 950;
            box-shadow: 0 16px 26px rgba(91,0,0,0.24);
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
            border: 0;
            border-radius: 16px;
            background: linear-gradient(135deg, #5e0000, #8b0000 60%, #a61b1b);
            color: #ffffff;
            font-size: 15px;
            font-weight: 900;
            letter-spacing: 0.02em;
            cursor: pointer;
            box-shadow: 0 18px 30px rgba(91,0,0,0.24);
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

        .switch-form { margin-top: 20px; font-size: 14px; color: var(--text-light); }
        .switch-form span { color: var(--accent); cursor: pointer; font-weight: 700; text-decoration: underline; }

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

            .logo-header {
                padding: 12px 14px;
            }

            .logo-title {
                font-size: 16px;
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
            .logo-subtitle {
                font-size: 10px;
            }

            .btn-submit {
                padding: 12px;
            }
        }

        @keyframes loginLogoBounce {
            0%, 100% { transform: translateY(0); }
            50% { transform: translateY(-10px); }
        }
        @keyframes slideUp { from { opacity: 0; transform: translateY(30px); } to { opacity: 1; transform: translateY(0); } }
        @keyframes headerSweep {
            0% { transform: translateX(-140%); }
            35% { transform: translateX(140%); }
            100% { transform: translateX(140%); }
        }
    </style>
</head>
<body>

  <div class="logo-header">
    <div class="logo-brand">
      <img src="{{ asset('images/clinic_logo_transparent.png') }}?v={{ filemtime(public_path('images/clinic_logo_transparent.png')) }}" alt="Clinic Logo" class="logo-icon">
      <div class="logo-text">
        <div class="logo-title">PUP TAGUIG</div>
        <div class="logo-subtitle">ONLINE CLINIC</div>
      </div>
    </div>
  </div>

  <main class="lp-container">
    <div class="login-box">
        @php
            $portalLoginUrl = route('login.portal');
            $localLoginEnabled = $localLoginEnabled ?? false;
            $idpUnavailable = ($idpUnavailable ?? false) || request()->boolean('idp_error') || $errors->has('idp');
        @endphp

        @unless($idpUnavailable)
            <div class="login-hero">
                <div class="login-hero-top">
                    <div class="login-hero-badge"><span></span> Clinic Access</div>
                    <div class="login-chip">
                        {{ config('services.idp.enabled') ? 'Centralized Sign In' : ($localLoginEnabled ? 'Local Sign In' : 'Restricted Sign In') }}
                    </div>
                </div>
                <h2>Clinic Portal</h2>
                <p class="login-hero-copy">Login to your account to continue. The same system keeps student and clinic access in one place.</p>
                <div class="login-subline">
                    <span class="login-chip">IdP Ready</span>
                    @if($localLoginEnabled)
                        <span class="login-chip">Local Fallback</span>
                    @endif
                </div>
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
                <a href="{{ $portalLoginUrl }}" class="btn-submit" style="display:block; text-decoration:none; text-align:center;">
                    Login through Identity Provider
                </a>
                <p class="idp-login-note">
                    Centralized sign-in is enabled for this system. Use the button above to authenticate through the campus identity provider.
                </p>
            </div>

            @if($localLoginEnabled)
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
            @endif
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

  @include('partials.system_footer')

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
