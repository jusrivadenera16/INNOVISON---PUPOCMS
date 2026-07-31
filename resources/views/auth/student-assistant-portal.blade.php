<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Choose Your Workspace | PUP Taguig Medical Clinic</title>
    <style>
        :root {
            --maroon: #70131b;
            --maroon-2: #8f2230;
            --maroon-3: #a51528;
            --dark: #1d070d;
            --gold: #facc15;
            --gold-soft: #f8d86a;
            --text: #ffffff;
            --muted: rgba(255, 255, 255, .72);
            --line: rgba(255, 255, 255, .38);
            --glass: rgba(255, 255, 255, .09);
        }

        @property --shell-glow-angle {
            syntax: "<angle>";
            inherits: false;
            initial-value: 0deg;
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
            min-height: 100vh;
            overflow-x: hidden;
            color: var(--text);
            font-family: "Segoe UI", Tahoma, Geneva, Verdana, sans-serif;
            background:
                linear-gradient(180deg, rgba(68, 12, 19, .58), rgba(30, 4, 9, .88)),
                linear-gradient(90deg, rgba(70, 8, 15, .74), rgba(112, 19, 27, .52), rgba(36, 5, 10, .82)),
                url('{{ asset('images/PUPBG.jpg') }}') center center / cover no-repeat fixed;
        }

        body::before,
        body::after {
            content: "";
            position: fixed;
            inset: 0;
            pointer-events: none;
        }

        body::before {
            z-index: 0;
            background:
                radial-gradient(circle at 50% 47%, rgba(250, 204, 21, .13), transparent 18%),
                radial-gradient(circle at 16% 12%, rgba(255, 255, 255, .08), transparent 22%),
                linear-gradient(180deg, rgba(255, 255, 255, .02), rgba(0, 0, 0, .22));
            backdrop-filter: blur(1.2px);
            -webkit-backdrop-filter: blur(1.2px);
        }

        body::after {
            z-index: 1;
            background:
                radial-gradient(circle, rgba(255, 255, 255, .28) 0 1px, transparent 1.5px) left bottom / 11px 11px no-repeat,
                radial-gradient(circle, rgba(250, 204, 21, .18) 0 1px, transparent 1.5px) right bottom / 11px 11px no-repeat;
            background-size: 132px 92px, 132px 92px;
            opacity: .5;
        }

        body.portal-light {
            color: #2b0b12;
            background:
                linear-gradient(180deg, rgba(255, 250, 242, .86), rgba(255, 244, 230, .92)),
                url('{{ asset('images/PUPBG.jpg') }}') center center / cover no-repeat fixed;
        }

        body.portal-light::before {
            background:
                radial-gradient(circle at 50% 47%, rgba(250, 204, 21, .16), transparent 18%),
                linear-gradient(180deg, rgba(255, 255, 255, .28), rgba(255, 255, 255, .48));
        }

        .portal-page {
            position: relative;
            z-index: 2;
            min-height: 100vh;
            display: flex;
            flex-direction: column;
            align-items: center;
            padding: 34px 18px 18px;
        }

        .theme-toggle {
            position: fixed;
            top: 17px;
            right: 17px;
            z-index: 10;
            width: 39px;
            height: 39px;
            border-radius: 999px;
            border: 1px solid rgba(250, 204, 21, .56);
            background: rgba(42, 10, 17, .58);
            color: #f7e7bc;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            cursor: pointer;
            box-shadow: 0 15px 34px rgba(0, 0, 0, .25);
            backdrop-filter: blur(16px);
            -webkit-backdrop-filter: blur(16px);
            transition: transform .18s ease, background .18s ease, color .18s ease;
        }

        .theme-toggle:hover,
        .theme-toggle:focus-visible {
            transform: translateY(-1px);
            background: #facc15;
            color: #70131b;
            outline: none;
        }

        .theme-toggle svg {
            width: 18px;
            height: 18px;
            stroke: currentColor;
            fill: none;
            stroke-width: 2;
        }

        .portal-brand {
            width: min(760px, 100%);
            text-align: center;
            display: grid;
            justify-items: center;
            gap: 11px;
        }

        .logo-row {
            position: relative;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 30px;
            margin-bottom: 2px;
        }

        .logo-row::after {
            content: "";
            position: absolute;
            left: 50%;
            top: 10px;
            bottom: 10px;
            width: 1px;
            background: rgba(255, 255, 255, .42);
            transform: translateX(-50%);
        }

        .brand-logo {
            width: 46px;
            height: 46px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
        }

        .brand-logo img {
            width: 46px;
            height: 46px;
            object-fit: contain;
            filter: drop-shadow(0 10px 14px rgba(0, 0, 0, .28));
        }

        .brand-logo:nth-child(2) img {
            width: 46px;
            height: 46px;
            transform: scale(1.42);
            transform-origin: center;
        }

        .portal-title {
            margin: 0;
            color: #fff8ed;
            font-size: clamp(2.12rem, 4.45vw, 3.45rem);
            line-height: 1;
            font-weight: 900;
            letter-spacing: -.035em;
            text-shadow: 0 7px 20px rgba(0, 0, 0, .24);
        }

        .portal-title span {
            color: var(--gold-soft);
        }

        .title-mark {
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 9px;
            margin-top: -2px;
        }

        .title-mark::before,
        .title-mark::after {
            content: "";
            width: 42px;
            height: 1px;
            background: rgba(250, 204, 21, .54);
        }

        .title-mark span {
            width: 6px;
            height: 6px;
            border-radius: 999px;
            background: var(--gold);
            box-shadow: 0 0 14px rgba(250, 204, 21, .7);
        }

        .portal-subtitle {
            margin: 3px 0 0;
            color: var(--muted);
            font-size: 15px;
            font-weight: 500;
        }

        .status-pill {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 10px;
            min-height: 31px;
            padding: 0 15px;
            border-radius: 999px;
            color: rgba(255, 255, 255, .9);
            background: rgba(255, 255, 255, .08);
            border: 1px solid rgba(255, 255, 255, .14);
            box-shadow: inset 0 1px 0 rgba(255, 255, 255, .08);
            backdrop-filter: blur(15px);
            -webkit-backdrop-filter: blur(15px);
            font-size: 12px;
            font-weight: 600;
        }

        .status-dot {
            width: 9px;
            height: 9px;
            border-radius: 999px;
            background: #37d75f;
            box-shadow: 0 0 12px rgba(55, 215, 95, .92);
        }

        .workspace-shell {
            position: relative;
            width: min(594px, calc(100% - 16px));
            margin-top: 19px;
            padding: 20px 24px 20px;
            border-radius: 17px;
            background:
                linear-gradient(135deg, rgba(255, 255, 255, .13), rgba(255, 255, 255, .045)),
                rgba(48, 14, 22, .42);
            border: 1px solid rgba(255, 255, 255, .46);
            box-shadow:
                0 0 0 1px rgba(250, 204, 21, .04),
                0 24px 52px rgba(20, 3, 8, .46),
                inset 0 1px 0 rgba(255, 255, 255, .17);
            backdrop-filter: blur(18px);
            -webkit-backdrop-filter: blur(18px);
        }

        .workspace-shell::before {
            content: "";
            position: absolute;
            inset: -2px;
            padding: 2px;
            border-radius: 19px;
            background:
                conic-gradient(
                    from var(--shell-glow-angle, 0deg),
                    transparent 0deg,
                    transparent 236deg,
                    rgba(250, 204, 21, .02) 248deg,
                    rgba(250, 204, 21, .18) 257deg,
                    rgba(255, 245, 180, .72) 266deg,
                    rgba(255, 255, 255, .96) 270deg,
                    rgba(255, 245, 180, .72) 274deg,
                    rgba(250, 204, 21, .18) 283deg,
                    rgba(250, 204, 21, .02) 292deg,
                    transparent 304deg,
                    transparent 360deg
                );
            opacity: .98;
            filter: drop-shadow(0 0 10px rgba(250, 204, 21, .72)) drop-shadow(0 0 22px rgba(250, 204, 21, .34));
            -webkit-mask:
                linear-gradient(#000 0 0) content-box,
                linear-gradient(#000 0 0);
            -webkit-mask-composite: xor;
            mask:
                linear-gradient(#000 0 0) content-box,
                linear-gradient(#000 0 0);
            mask-composite: exclude;
            pointer-events: none;
            animation: shellGlowTrace 6.5s linear infinite;
        }

        .workspace-shell::after {
            content: "";
            position: absolute;
            inset: -7px;
            padding: 7px;
            border-radius: 24px;
            background:
                conic-gradient(
                    from var(--shell-glow-angle, 0deg),
                    transparent 0deg,
                    transparent 240deg,
                    rgba(250, 204, 21, .03) 252deg,
                    rgba(250, 204, 21, .20) 264deg,
                    rgba(255, 250, 205, .55) 270deg,
                    rgba(250, 204, 21, .20) 276deg,
                    rgba(250, 204, 21, .03) 288deg,
                    transparent 300deg,
                    transparent 360deg
                );
            opacity: .66;
            filter: blur(7px);
            -webkit-mask:
                linear-gradient(#000 0 0) content-box,
                linear-gradient(#000 0 0);
            -webkit-mask-composite: xor;
            mask:
                linear-gradient(#000 0 0) content-box,
                linear-gradient(#000 0 0);
            mask-composite: exclude;
            pointer-events: none;
            animation: shellGlowTrace 6.5s linear infinite;
        }

        .workspace-grid {
            position: relative;
            z-index: 1;
            display: grid;
            grid-template-columns: minmax(0, 1fr) 38px minmax(0, 1fr);
            align-items: center;
            gap: 12px;
        }

        .workspace-card {
            position: relative;
            min-height: 221px;
            padding: 16px 24px 13px;
            border-radius: 16px;
            color: #fff6f6;
            text-align: center;
            text-decoration: none;
            overflow: hidden;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: flex-start;
            border: 1px solid rgba(255, 255, 255, .48);
            background: rgba(255, 255, 255, .075);
            box-shadow:
                inset 0 1px 0 rgba(255, 255, 255, .14),
                0 16px 28px rgba(0, 0, 0, .22);
            transition: transform .18s ease, border-color .18s ease, box-shadow .18s ease, background .18s ease;
        }

        .workspace-card::before {
            content: "";
            position: absolute;
            inset: 0;
            background:
                radial-gradient(circle at 48% 23%, rgba(250, 204, 21, .24), transparent 20%),
                linear-gradient(135deg, rgba(143, 10, 32, .78), rgba(81, 14, 24, .12));
            opacity: .75;
            pointer-events: none;
        }

        .workspace-card::after {
            content: "";
            position: absolute;
            top: 12px;
            right: 11px;
            width: 31px;
            height: 31px;
            background-image: radial-gradient(circle, rgba(255, 255, 255, .28) 1px, transparent 1.5px);
            background-size: 8px 8px;
            opacity: .74;
        }

        .workspace-card.is-admin::before {
            background:
                radial-gradient(circle at 48% 23%, rgba(250, 204, 21, .22), transparent 20%),
                linear-gradient(135deg, rgba(255, 255, 255, .07), rgba(72, 25, 33, .28));
        }

        .workspace-card:hover,
        .workspace-card:focus-visible {
            transform: translateY(-2px);
            border-color: rgba(250, 204, 21, .78);
            box-shadow:
                0 0 0 1px rgba(250, 204, 21, .18),
                0 22px 42px rgba(0, 0, 0, .35),
                inset 0 1px 0 rgba(255, 255, 255, .18);
            outline: none;
        }

        .workspace-card.is-disabled {
            cursor: not-allowed;
            opacity: .62;
        }

        .workspace-card > * {
            position: relative;
            z-index: 1;
        }

        .workspace-icon {
            width: 64px;
            height: 64px;
            border-radius: 999px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            color: #ffffff;
            border: 1px solid rgba(250, 204, 21, .58);
            background:
                radial-gradient(circle at 50% 36%, rgba(250, 204, 21, .18), transparent 54%),
                rgba(112, 19, 27, .32);
            box-shadow: 0 10px 26px rgba(250, 204, 21, .12);
        }

        .workspace-icon svg {
            width: 34px;
            height: 34px;
            stroke: currentColor;
            fill: none;
            stroke-width: 1.6;
        }

        .workspace-card h2 {
            margin: 22px 0 0;
            font-size: 18px;
            line-height: 1.1;
            font-weight: 900;
            color: #fff8ef;
        }

        .workspace-rule {
            width: 38px;
            height: 2px;
            margin: 11px auto 14px;
            background: var(--gold);
            border-radius: 999px;
        }

        .workspace-card p {
            margin: 0;
            max-width: 180px;
            color: rgba(255, 255, 255, .70);
            font-size: 12px;
            line-height: 1.5;
            font-weight: 500;
        }

        .workspace-button {
            position: relative;
            width: 153px;
            min-height: 34px;
            margin-top: auto;
            border-radius: 10px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 18px;
            font-size: 12px;
            font-weight: 800;
            border: 1px solid rgba(250, 204, 21, .70);
            background: rgba(112, 19, 27, .92);
            color: #ffffff;
            box-shadow: 0 10px 22px rgba(112, 19, 27, .22);
            overflow: hidden;
            isolation: isolate;
            transition: background .18s ease, color .18s ease, border-color .18s ease, box-shadow .18s ease;
        }

        .workspace-button::before {
            content: "";
            position: absolute;
            inset: 0;
            z-index: -2;
            background: #facc15;
            transform: scaleX(0);
            transform-origin: left center;
            transition: transform .28s ease;
        }

        .workspace-button::after {
            content: "";
            position: absolute;
            top: -50%;
            bottom: -50%;
            left: -140%;
            z-index: -1;
            width: 42%;
            background: linear-gradient(105deg, rgba(255, 255, 255, 0) 0%, rgba(255, 246, 184, .22) 42%, rgba(255, 246, 184, .74) 50%, rgba(255, 246, 184, .22) 58%, rgba(255, 255, 255, 0) 100%);
            transform: skewX(-18deg);
            opacity: 0;
        }

        .workspace-card.is-admin .workspace-button {
            background: rgba(255, 250, 235, .96);
            color: var(--maroon);
            border-color: rgba(250, 204, 21, .62);
        }

        .workspace-card:hover .workspace-button,
        .workspace-card:focus-visible .workspace-button {
            background: #facc15;
            color: var(--maroon);
            border-color: #facc15;
            box-shadow: 0 0 0 3px rgba(250, 204, 21, .12), 0 14px 24px rgba(112, 19, 27, .24);
        }

        .workspace-card:hover .workspace-button::before,
        .workspace-card:focus-visible .workspace-button::before {
            transform: scaleX(1);
        }

        .workspace-card:hover .workspace-button::after,
        .workspace-card:focus-visible .workspace-button::after {
            opacity: 1;
            animation: buttonSweep .85s ease both;
        }

        .workspace-button svg {
            width: 15px;
            height: 15px;
            stroke: currentColor;
            fill: none;
            stroke-width: 2;
        }

        @keyframes buttonSweep {
            from { left: -140%; }
            to { left: 140%; }
        }

        @keyframes shellGlowTrace {
            from { --shell-glow-angle: 0deg; }
            to { --shell-glow-angle: 360deg; }
        }

        .or-divider {
            position: relative;
            height: 100%;
            min-height: 205px;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .or-divider::before {
            content: "";
            position: absolute;
            top: 6px;
            bottom: 6px;
            left: 50%;
            width: 1px;
            transform: translateX(-50%);
            background: linear-gradient(180deg, transparent, rgba(255, 255, 255, .34), transparent);
        }

        .or-divider span {
            position: relative;
            z-index: 1;
            width: 34px;
            height: 34px;
            border-radius: 999px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            color: rgba(255, 255, 255, .86);
            background: rgba(55, 17, 24, .66);
            border: 1px solid rgba(255, 255, 255, .24);
            font-size: 11px;
            font-weight: 800;
        }

        .assistant-foot {
            margin-top: 19px;
            text-align: center;
            display: grid;
            gap: 8px;
            justify-items: center;
        }

        .greeting {
            display: inline-flex;
            align-items: center;
            gap: 10px;
            color: rgba(255, 255, 255, .73);
            font-size: 13px;
            font-weight: 500;
        }

        .greeting-icon {
            width: 28px;
            height: 28px;
            border-radius: 999px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            color: #facc15;
            border: 1px solid rgba(250, 204, 21, .52);
            background: rgba(112, 19, 27, .45);
        }

        .greeting-icon svg {
            width: 15px;
            height: 15px;
            stroke: currentColor;
            fill: none;
            stroke-width: 2;
        }

        .greeting strong {
            color: #fff5f5;
            font-weight: 900;
        }

        .sign-out {
            border: 0;
            background: transparent;
            color: rgba(255, 255, 255, .70);
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
            min-height: 26px;
            padding: 0 10px;
            font: inherit;
            font-size: 12px;
            font-weight: 500;
            cursor: pointer;
        }

        .sign-out:hover,
        .sign-out:focus-visible {
            color: #facc15;
            outline: none;
        }

        .sign-out svg {
            width: 14px;
            height: 14px;
            stroke: currentColor;
            fill: none;
            stroke-width: 2;
        }

        .portal-footer {
            margin-top: auto;
            padding-top: 18px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 28px;
            color: rgba(255, 255, 255, .48);
            font-size: 11px;
            font-weight: 500;
        }

        .portal-footer span + span {
            position: relative;
        }

        .portal-footer span + span::before {
            content: "";
            position: absolute;
            top: 50%;
            left: -15px;
            width: 1px;
            height: 12px;
            background: rgba(255, 255, 255, .32);
            transform: translateY(-50%);
        }

        body.portal-light .portal-title,
        body.portal-light .workspace-card h2,
        body.portal-light .greeting strong {
            color: #70131b;
            text-shadow: none;
        }

        body.portal-light .portal-subtitle,
        body.portal-light .greeting,
        body.portal-light .sign-out,
        body.portal-light .portal-footer {
            color: rgba(78, 28, 36, .70);
        }

        body.portal-light .workspace-shell {
            background: rgba(255, 255, 255, .62);
            border-color: rgba(112, 19, 27, .20);
        }

        body.portal-light .workspace-card {
            border-color: rgba(112, 19, 27, .22);
            background: rgba(255, 255, 255, .72);
            color: #70131b;
        }

        body.portal-light .workspace-card::before {
            opacity: .20;
        }

        body.portal-light .workspace-card p {
            color: rgba(78, 28, 36, .72);
        }

        body.portal-light .or-divider span {
            color: #70131b;
            background: rgba(255, 255, 255, .82);
            border-color: rgba(112, 19, 27, .20);
        }

        @media (max-width: 760px) {
            .portal-page {
                padding-top: 62px;
            }

            .workspace-shell {
                width: min(430px, 100%);
                padding: 18px;
            }

            .workspace-grid {
                grid-template-columns: 1fr;
                gap: 14px;
            }

            .or-divider {
                min-height: 34px;
                height: 34px;
            }

            .or-divider::before {
                top: 50%;
                bottom: auto;
                left: 20px;
                right: 20px;
                width: auto;
                height: 1px;
                transform: translateY(-50%);
                background: linear-gradient(90deg, transparent, rgba(255, 255, 255, .34), transparent);
            }

            .workspace-card {
                min-height: 210px;
            }
        }

        @media (max-width: 480px) {
            .portal-title {
                font-size: 2rem;
            }

            .logo-row {
                gap: 24px;
            }

            .brand-logo,
            .brand-logo img {
                width: 42px;
                height: 42px;
            }

            .workspace-shell {
                width: 100%;
                border-radius: 15px;
            }

            .portal-footer {
                flex-direction: column;
                gap: 6px;
            }

            .portal-footer span + span::before {
                content: none;
            }
        }
    </style>
</head>
<body>
@php
    $user = auth()->user();
    $normalizedUserRole = \App\Models\User::normalizeRole(optional($user)->user_role ?? '');
    $userType = strtolower(trim((string) (optional($user)->user_type ?? '')));
    $isStudentAssistant = method_exists($user, 'isStudentAssistant') ? $user->isStudentAssistant() : false;
    $showStudentWorkspace = true;
    $showAdminWorkspace = $isStudentAssistant
        || in_array($normalizedUserRole, [\App\Models\User::ROLE_ADMIN, \App\Models\User::ROLE_SUPERADMIN], true)
        || in_array($userType, ['assistant', 'student assistant', 'student_assistant'], true);
    $adminWorkspaceAvailable = (bool) ($adminWorkspaceAvailable ?? false);
    $hour = (int) now()->format('G');
    $greeting = $hour < 12 ? 'Good Morning' : ($hour < 18 ? 'Good Afternoon' : 'Good Evening');
    $displayName = trim((string) (optional($user)->name ?: 'Student Assistant'));
    $statusText = $showAdminWorkspace && !$adminWorkspaceAvailable
        ? 'Student services are available'
        : 'All clinic services are available';
@endphp

<main class="portal-page" aria-label="Student Assistant workspace selection">
    <button type="button" class="theme-toggle" id="themeToggle" aria-label="Switch theme" aria-pressed="false">
        <svg id="themeIcon" viewBox="0 0 24 24" aria-hidden="true">
            <path d="M21 12.8A9 9 0 1 1 11.2 3a7 7 0 0 0 9.8 9.8Z" stroke-linecap="round" stroke-linejoin="round"></path>
        </svg>
    </button>

    <section class="portal-brand">
        <div class="logo-row" aria-label="PUP and Medical Clinic logos">
            <span class="brand-logo">
                <img src="{{ asset('images/pup_logo.png') }}" alt="PUP Logo">
            </span>
            <span class="brand-logo">
                <img src="{{ asset('images/clinic_logo_transparent.png') }}" alt="Clinic Logo">
            </span>
        </div>

        <h1 class="portal-title">PUP Taguig <span>Medical Clinic</span></h1>
        <div class="title-mark" aria-hidden="true"><span></span></div>
        <p class="portal-subtitle">Choose the workspace you want to access</p>
        <div class="status-pill">
            <span class="status-dot" aria-hidden="true"></span>
            <span>{{ $statusText }}</span>
        </div>
    </section>

    @error('workspace')
        <p role="alert" style="margin:14px 0 0;color:#fecaca;font-size:13px;font-weight:700;">{{ $message }}</p>
    @enderror

    <section class="workspace-shell" aria-label="Workspace options">
        <div class="workspace-grid">
            @if($showStudentWorkspace)
                <a class="workspace-card is-student" href="{{ route('assistant.enter-student') }}">
                    <span class="workspace-icon" aria-hidden="true">
                        <svg viewBox="0 0 24 24">
                            <path d="M4 7.5 12 3l8 4.5-8 4.5L4 7.5Z" stroke-linejoin="round"></path>
                            <path d="M7 10.2v4.1c0 1.7 2.2 3.1 5 3.1s5-1.4 5-3.1v-4.1"></path>
                            <path d="M20 8v5"></path>
                            <path d="M6 21c.7-2.2 3-3.4 6-3.4s5.3 1.2 6 3.4"></path>
                        </svg>
                    </span>
                    <h2>Student Workspace</h2>
                    <span class="workspace-rule" aria-hidden="true"></span>
                    <p>Access appointments, health records, and student services.</p>
                    <span class="workspace-button">
                        <span>Continue</span>
                        <svg viewBox="0 0 24 24" aria-hidden="true">
                            <path d="M5 12h14"></path>
                            <path d="m13 6 6 6-6 6"></path>
                        </svg>
                    </span>
                </a>
            @endif

            <div class="or-divider" aria-hidden="true"><span>OR</span></div>

            @if($showAdminWorkspace && $adminWorkspaceAvailable)
                <a class="workspace-card is-admin" href="{{ route('assistant.enter-admin') }}">
                    <span class="workspace-icon" aria-hidden="true">
                        <svg viewBox="0 0 24 24">
                            <circle cx="12" cy="7" r="3.4"></circle>
                            <path d="M5.5 21v-1.4c0-3.1 2.9-5.2 6.5-5.2s6.5 2.1 6.5 5.2V21"></path>
                            <path d="M8.7 15.5 12 19l3.3-3.5"></path>
                            <path d="M9.2 5.1 12 3.7l2.8 1.4"></path>
                        </svg>
                    </span>
                    <h2>Admin Workspace</h2>
                    <span class="workspace-rule" aria-hidden="true"></span>
                    <p>Authorized clinic management and operational portal.</p>
                    <span class="workspace-button">
                        <span>Continue</span>
                        <svg viewBox="0 0 24 24" aria-hidden="true">
                            <path d="M5 12h14"></path>
                            <path d="m13 6 6 6-6 6"></path>
                        </svg>
                    </span>
                </a>
            @elseif($showAdminWorkspace)
                <button class="workspace-card is-admin is-disabled" type="button" disabled aria-disabled="true" title="Admin Workspace is available {{ $adminWorkspaceHoursLabel ?? 'during clinic assistant hours' }}">
                    <span class="workspace-icon" aria-hidden="true">
                        <svg viewBox="0 0 24 24">
                            <circle cx="12" cy="7" r="3.4"></circle>
                            <path d="M5.5 21v-1.4c0-3.1 2.9-5.2 6.5-5.2s6.5 2.1 6.5 5.2V21"></path>
                            <path d="M8.7 15.5 12 19l3.3-3.5"></path>
                            <path d="M9.2 5.1 12 3.7l2.8 1.4"></path>
                        </svg>
                    </span>
                    <h2>Admin Workspace</h2>
                    <span class="workspace-rule" aria-hidden="true"></span>
                    <p>Available {{ $adminWorkspaceHoursLabel ?? 'during clinic assistant hours' }}.</p>
                    <span class="workspace-button">
                        <span>Closed</span>
                        <svg viewBox="0 0 24 24" aria-hidden="true">
                            <path d="M12 8v4l2.5 1.5"></path>
                            <circle cx="12" cy="12" r="8"></circle>
                        </svg>
                    </span>
                </button>
            @endif
        </div>
    </section>

    <section class="assistant-foot" aria-label="Signed in account">
        <div class="greeting">
            <span class="greeting-icon" aria-hidden="true">
                <svg viewBox="0 0 24 24">
                    <circle cx="12" cy="8" r="3"></circle>
                    <path d="M5.5 20a6.5 6.5 0 0 1 13 0"></path>
                </svg>
            </span>
            <span>{{ $greeting }}, <strong>{{ $displayName }}</strong></span>
        </div>
        <form method="POST" action="{{ route('logout') }}">
            @csrf
            <input type="hidden" name="logout_all" value="1">
            <button type="submit" class="sign-out">
                <svg viewBox="0 0 24 24" aria-hidden="true">
                    <path d="M15 8V5.5A1.5 1.5 0 0 0 13.5 4h-7A1.5 1.5 0 0 0 5 5.5v13A1.5 1.5 0 0 0 6.5 20h7A1.5 1.5 0 0 0 15 18.5V16"></path>
                    <path d="M10 12h9"></path>
                    <path d="m16 9 3 3-3 3"></path>
                </svg>
                <span>Sign out</span>
            </button>
        </form>
    </section>

    <footer class="portal-footer">
        <span>&copy; {{ now()->year }} PUP Taguig Medical Clinic</span>
        <span>Assistant Portal</span>
    </footer>
</main>

<script>
    (function () {
        const body = document.body;
        const toggle = document.getElementById('themeToggle');
        const icon = document.getElementById('themeIcon');
        const storageKey = 'assistant-portal-theme';

        function setIcon(isLight) {
            if (!icon) return;
            icon.innerHTML = isLight
                ? '<path d="M12 3v2.2M12 18.8V21M4.9 4.9l1.6 1.6M17.5 17.5l1.6 1.6M3 12h2.2M18.8 12H21M4.9 19.1l1.6-1.6M17.5 6.5l1.6-1.6M12 7a5 5 0 1 1 0 10 5 5 0 0 1 0-10Z" stroke-linecap="round" stroke-linejoin="round"></path>'
                : '<path d="M21 12.8A9 9 0 1 1 11.2 3a7 7 0 0 0 9.8 9.8Z" stroke-linecap="round" stroke-linejoin="round"></path>';
        }

        function applyTheme(theme) {
            const isLight = theme === 'light';
            body.classList.toggle('portal-light', isLight);
            if (toggle) {
                toggle.setAttribute('aria-pressed', isLight ? 'true' : 'false');
            }
            setIcon(isLight);
        }

        let initialTheme = 'dark';
        try {
            initialTheme = localStorage.getItem(storageKey) || 'dark';
        } catch (error) {
            initialTheme = 'dark';
        }

        applyTheme(initialTheme);

        if (toggle) {
            toggle.addEventListener('click', function () {
                const next = body.classList.contains('portal-light') ? 'dark' : 'light';
                applyTheme(next);
                try {
                    localStorage.setItem(storageKey, next);
                } catch (error) {
                    // Theme storage is optional.
                }
            });
        }
    })();
</script>
</body>
</html>
