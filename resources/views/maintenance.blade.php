<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="theme-color" content="#3b0711">
    <title>Under Maintenance | PUP Taguig Medical Clinic</title>
    <style>
        :root {
            --maroon: #8d1425;
            --maroon-deep: #31050d;
            --maroon-ink: #22030a;
            --gold: #f4c33a;
            --gold-soft: #f8d368;
            --white: #fffaf7;
            --muted: rgba(255, 250, 247, 0.76);
            --glass: rgba(66, 14, 24, 0.62);
            --glass-line: rgba(255, 244, 236, 0.46);
        }

        * {
            box-sizing: border-box;
        }

        html {
            min-width: 320px;
            background: var(--maroon-ink);
        }

        body {
            min-height: 100vh;
            margin: 0;
            overflow-x: hidden;
            color: var(--white);
            font-family: Inter, ui-sans-serif, system-ui, -apple-system, BlinkMacSystemFont, "Segoe UI", sans-serif;
            background:
                linear-gradient(105deg, rgba(43, 3, 11, 0.92) 0%, rgba(83, 13, 26, 0.79) 48%, rgba(47, 5, 13, 0.84) 100%),
                url('{{ asset('images/PUPBG.jpg') }}') center 47% / cover no-repeat fixed;
        }

        body::before {
            content: "";
            position: fixed;
            inset: 0;
            z-index: 0;
            pointer-events: none;
            background:
                radial-gradient(circle at 82% 8%, rgba(255, 157, 92, 0.34), transparent 20%),
                radial-gradient(circle at 50% 62%, rgba(145, 24, 40, 0.16), transparent 40%),
                linear-gradient(180deg, rgba(18, 2, 7, 0.08), rgba(28, 2, 8, 0.38));
        }

        body::after {
            content: "";
            position: fixed;
            inset: 0;
            z-index: 0;
            pointer-events: none;
            opacity: 0.22;
            background-image: radial-gradient(rgba(255, 255, 255, 0.12) 0.7px, transparent 0.7px);
            background-size: 4px 4px;
            mix-blend-mode: soft-light;
        }

        .maintenance-page {
            position: relative;
            z-index: 1;
            width: min(100%, 1180px);
            min-height: 100vh;
            margin: 0 auto;
            padding: clamp(28px, 4vh, 48px) clamp(20px, 4vw, 48px) 24px;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
        }

        .corner-dots {
            position: fixed;
            z-index: 1;
            width: 108px;
            height: 80px;
            pointer-events: none;
            opacity: 0.48;
            background-image: radial-gradient(circle, #a92c3d 1.7px, transparent 1.9px);
            background-size: 15px 15px;
        }

        .corner-dots.top-left {
            top: 42px;
            left: 26px;
        }

        .corner-dots.bottom-right {
            right: 26px;
            bottom: 24px;
        }

        .brand-lockup {
            display: flex;
            flex-direction: column;
            align-items: center;
            margin-bottom: 20px;
        }

        .brand-logos {
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .brand-logo {
            width: 68px;
            height: 68px;
            display: grid;
            place-items: center;
        }

        .brand-logo img {
            display: block;
            width: 100%;
            height: 100%;
            object-fit: contain;
        }

        .brand-name {
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 14px;
            margin: 17px 0 0;
            color: var(--gold-soft);
            font-size: clamp(13px, 1.4vw, 17px);
            font-weight: 500;
            letter-spacing: 0.12em;
            text-align: center;
            text-transform: uppercase;
        }

        .brand-name::before,
        .brand-name::after {
            content: "";
            width: 38px;
            height: 1px;
            background: var(--gold);
        }

        .maintenance-card {
            position: relative;
            width: min(940px, 100%);
            display: grid;
            grid-template-columns: 290px minmax(0, 1fr);
            overflow: hidden;
            border: 1px solid var(--glass-line);
            border-radius: 24px;
            background:
                linear-gradient(115deg, rgba(91, 19, 34, 0.46), rgba(33, 7, 14, 0.44)),
                rgba(74, 17, 29, 0.46);
            box-shadow:
                0 28px 70px rgba(12, 1, 4, 0.48),
                inset 0 1px 0 rgba(255, 255, 255, 0.1);
            backdrop-filter: blur(14px);
            -webkit-backdrop-filter: blur(14px);
        }

        .maintenance-card::before {
            content: "";
            position: absolute;
            top: -2px;
            right: 8%;
            width: 190px;
            height: 4px;
            border-radius: 999px;
            background: linear-gradient(90deg, transparent, #fff4c3 48%, transparent);
            filter: blur(1px);
            box-shadow: 0 0 15px rgba(248, 198, 73, 0.75);
            opacity: 0.8;
        }

        .maintenance-visual {
            position: relative;
            min-height: 300px;
            display: grid;
            place-items: center;
            margin: 36px 0 28px;
            border-right: 1px solid rgba(255, 255, 255, 0.18);
        }

        .maintenance-orbit {
            position: relative;
            width: 190px;
            height: 190px;
            display: grid;
            place-items: center;
            border: 1px solid rgba(248, 211, 104, 0.72);
            border-radius: 50%;
            background: radial-gradient(circle at center, rgba(127, 20, 38, 0.66), rgba(72, 10, 21, 0.5) 64%, transparent 65%);
            box-shadow:
                0 0 0 12px rgba(122, 20, 36, 0.16),
                0 0 12px rgba(248, 211, 104, 0.42),
                0 0 38px rgba(244, 195, 58, 0.25),
                inset 0 0 30px rgba(255, 183, 76, 0.08);
        }

        .maintenance-orbit::before,
        .maintenance-orbit::after {
            content: "";
            position: absolute;
            border-radius: 50%;
            pointer-events: none;
        }

        .maintenance-orbit::before {
            inset: -15px;
            border: 2px dotted rgba(200, 55, 70, 0.48);
            animation: orbitSpin 28s linear infinite;
        }

        .maintenance-orbit::after {
            left: 24%;
            right: 24%;
            bottom: -13px;
            height: 5px;
            background: linear-gradient(90deg, transparent, var(--gold), transparent);
            filter: blur(1px);
            box-shadow: 0 0 16px rgba(244, 195, 58, 0.8);
        }

        .maintenance-emblem {
            display: block;
            width: 154px;
            height: 154px;
            object-fit: contain;
            filter: drop-shadow(0 0 13px rgba(244, 195, 58, 0.28));
        }

        .maintenance-copy {
            align-self: center;
            padding: 42px 54px 34px 34px;
        }

        .maintenance-copy h1 {
            max-width: 520px;
            margin: 0;
            color: var(--white);
            font-size: clamp(40px, 5vw, 59px);
            font-weight: 800;
            letter-spacing: 0;
            line-height: 0.98;
        }

        .maintenance-copy h1 span {
            display: block;
            margin-top: 6px;
            color: var(--gold-soft);
        }

        .heading-rule {
            display: block;
            width: 43px;
            height: 2px;
            margin: 20px 0 17px;
            background: var(--gold);
        }

        .maintenance-copy > p {
            max-width: 510px;
            margin: 0;
            color: rgba(255, 250, 247, 0.9);
            font-size: 15px;
            font-weight: 450;
            line-height: 1.55;
        }

        .return-card {
            width: min(350px, 100%);
            min-height: 70px;
            margin-top: 18px;
            padding: 12px 17px;
            display: grid;
            grid-template-columns: 40px minmax(0, 1fr);
            gap: 12px;
            align-items: center;
            border: 1px solid rgba(255, 255, 255, 0.15);
            border-radius: 13px;
            background: rgba(91, 20, 35, 0.62);
            box-shadow: inset 0 1px 0 rgba(255, 255, 255, 0.04);
        }

        .return-icon,
        .support-icon {
            display: grid;
            place-items: center;
            flex: 0 0 auto;
            border: 1px solid rgba(244, 195, 58, 0.35);
            border-radius: 50%;
            color: var(--gold);
            background: rgba(98, 16, 32, 0.72);
        }

        .return-icon {
            width: 38px;
            height: 38px;
        }

        .return-icon svg {
            width: 23px;
            height: 23px;
        }

        .return-label {
            display: block;
            margin-bottom: 2px;
            color: var(--muted);
            font-size: 12px;
            font-weight: 500;
        }

        .return-value {
            display: block;
            color: var(--gold-soft);
            font-size: 16px;
            font-weight: 800;
            line-height: 1.3;
        }

        .support-row {
            grid-column: 1 / -1;
            margin: 0 34px;
            display: grid;
            grid-template-columns: 1.25fr 1fr 0.92fr;
            border-top: 1px solid rgba(255, 255, 255, 0.15);
        }

        .support-item {
            min-width: 0;
            min-height: 78px;
            display: flex;
            align-items: center;
            gap: 12px;
            padding: 14px 18px;
            color: var(--white);
            text-decoration: none;
        }

        .support-item + .support-item {
            border-left: 1px solid rgba(255, 255, 255, 0.12);
        }

        .support-icon {
            width: 39px;
            height: 39px;
        }

        .support-icon svg {
            width: 20px;
            height: 20px;
        }

        .support-text {
            min-width: 0;
            color: rgba(255, 250, 247, 0.86);
            font-size: 13px;
            line-height: 1.45;
        }

        .support-text strong {
            display: block;
            color: var(--white);
            font-size: 13px;
            font-weight: 700;
        }

        a.support-item .support-text {
            overflow-wrap: anywhere;
        }

        a.support-item:hover .support-icon,
        a.support-item:focus-visible .support-icon {
            border-color: var(--gold);
            background: rgba(117, 21, 39, 0.96);
            box-shadow: 0 0 18px rgba(244, 195, 58, 0.2);
        }

        .page-note {
            margin-top: 25px;
            display: flex;
            align-items: flex-start;
            justify-content: center;
            gap: 13px;
            color: rgba(255, 250, 247, 0.9);
            text-align: left;
        }

        .page-note svg {
            width: 27px;
            height: 27px;
            flex: 0 0 auto;
            color: var(--gold);
        }

        .page-note p {
            margin: 0;
            font-size: 15px;
            line-height: 1.45;
        }

        .page-note span {
            display: block;
            margin-top: 3px;
            color: var(--gold);
            text-align: center;
        }

        .medical-line {
            position: fixed;
            left: 0;
            bottom: 36px;
            z-index: 1;
            width: 170px;
            height: 42px;
            color: rgba(155, 22, 41, 0.46);
            pointer-events: none;
        }

        .decorative-cross {
            position: fixed;
            z-index: 1;
            width: 38px;
            height: 38px;
            color: rgba(163, 31, 49, 0.58);
            pointer-events: none;
        }

        .decorative-cross.left {
            left: 18px;
            top: 54%;
        }

        .decorative-cross.right {
            right: 46px;
            top: 58%;
        }

        @keyframes orbitSpin {
            to { transform: rotate(360deg); }
        }

        @media (max-width: 820px) {
            .maintenance-page {
                justify-content: flex-start;
                padding-top: 28px;
            }

            .maintenance-card {
                grid-template-columns: 220px minmax(0, 1fr);
            }

            .maintenance-visual {
                min-height: 280px;
            }

            .maintenance-orbit {
                width: 158px;
                height: 158px;
            }

            .maintenance-emblem {
                width: 128px;
                height: 128px;
            }

            .maintenance-copy {
                padding: 36px 30px 28px;
            }

            .support-row {
                grid-template-columns: 1fr 1fr;
            }

            .support-item:first-child {
                grid-column: 1 / -1;
                border-bottom: 1px solid rgba(255, 255, 255, 0.12);
            }

            .support-item:nth-child(2) {
                border-left: 0;
            }
        }

        @media (max-width: 620px) {
            body {
                background-attachment: scroll;
            }

            .maintenance-page {
                padding: 22px 14px 24px;
            }

            .corner-dots,
            .medical-line,
            .decorative-cross {
                display: none;
            }

            .brand-lockup {
                margin-bottom: 15px;
            }

            .brand-logo {
                width: 52px;
                height: 52px;
            }

            .brand-name {
                margin-top: 12px;
                font-size: 11px;
                letter-spacing: 0.09em;
            }

            .brand-name::before,
            .brand-name::after {
                width: 22px;
            }

            .maintenance-card {
                grid-template-columns: 1fr;
                border-radius: 18px;
            }

            .maintenance-visual {
                min-height: 180px;
                margin: 20px 24px 0;
                border-right: 0;
                border-bottom: 1px solid rgba(255, 255, 255, 0.14);
            }

            .maintenance-orbit {
                width: 130px;
                height: 130px;
            }

            .maintenance-emblem {
                width: 106px;
                height: 106px;
            }

            .maintenance-copy {
                padding: 28px 24px 24px;
                text-align: center;
            }

            .maintenance-copy h1 {
                font-size: clamp(34px, 11vw, 46px);
            }

            .heading-rule {
                margin-right: auto;
                margin-left: auto;
            }

            .return-card {
                margin-right: auto;
                margin-left: auto;
                text-align: left;
            }

            .support-row {
                margin: 0 20px;
                grid-template-columns: 1fr;
            }

            .support-item,
            .support-item:first-child,
            .support-item:nth-child(2) {
                grid-column: auto;
                border-left: 0;
                border-bottom: 1px solid rgba(255, 255, 255, 0.12);
            }

            .support-item:last-child {
                border-bottom: 0;
            }

            .page-note {
                margin-top: 18px;
            }
        }

        @media (prefers-reduced-motion: reduce) {
            .maintenance-orbit::before {
                animation: none;
            }
        }
    </style>
</head>
<body>
@php
    $estimated = null;

    try {
        $estimated = $estimatedCompletion ? \Carbon\Carbon::parse($estimatedCompletion) : null;
    } catch (\Throwable $exception) {
        $estimated = null;
    }
@endphp

<span class="corner-dots top-left" aria-hidden="true"></span>
<span class="corner-dots bottom-right" aria-hidden="true"></span>

<svg class="decorative-cross left" viewBox="0 0 40 40" aria-hidden="true">
    <path d="M15 2h10v13h13v10H25v13H15V25H2V15h13V2Z" fill="none" stroke="currentColor" stroke-width="2"/>
</svg>
<svg class="decorative-cross right" viewBox="0 0 40 40" aria-hidden="true">
    <path d="M15 2h10v13h13v10H25v13H15V25H2V15h13V2Z" fill="none" stroke="currentColor" stroke-width="2"/>
</svg>

<main class="maintenance-page">
    <header class="brand-lockup">
        <div class="brand-logos" aria-label="Polytechnic University of the Philippines logo">
            <span class="brand-logo">
                <img src="{{ asset('images/pup_logo.png') }}" alt="Polytechnic University of the Philippines logo">
            </span>
        </div>
        <p class="brand-name">PUP Taguig Medical Clinic</p>
    </header>

    <section class="maintenance-card" aria-labelledby="maintenance-title">
        <div class="maintenance-visual" aria-hidden="true">
            <div class="maintenance-orbit">
                <img class="maintenance-emblem" src="{{ asset('images/clinic_logo_transparent.png') }}" alt="">
            </div>
        </div>

        <div class="maintenance-copy">
            <h1 id="maintenance-title">We're Under <span>Maintenance</span></h1>
            <span class="heading-rule" aria-hidden="true"></span>
            <p>We are currently performing scheduled maintenance to improve your experience. The website will be back online soon.</p>

            <div class="return-card">
                <span class="return-icon" aria-hidden="true">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">
                        <circle cx="12" cy="12" r="9"></circle>
                        <path d="M12 7v5l3.5 2.2"></path>
                    </svg>
                </span>
                <div>
                    <span class="return-label">Expected to be back on</span>
                    <strong class="return-value">
                        {{ $estimated ? $estimated->format('F j, Y') . '  -  ' . $estimated->format('g:i A') : 'Please check back soon' }}
                    </strong>
                </div>
            </div>
        </div>

        <div class="support-row" aria-label="Clinic assistance channels">
            <div class="support-item">
                <span class="support-icon" aria-hidden="true">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M21 15a4 4 0 0 1-4 4H8l-5 3V7a4 4 0 0 1 4-4h10a4 4 0 0 1 4 4v8Z"></path>
                        <path d="M8 9h8M8 13h5"></path>
                    </svg>
                </span>
                <span class="support-text"><strong>Need assistance?</strong>Please contact us through our official channels.</span>
            </div>

            <a class="support-item" href="mailto:puptclinic@gmail.com">
                <span class="support-icon" aria-hidden="true">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">
                        <rect x="3" y="5" width="18" height="14" rx="2"></rect>
                        <path d="m3 7 9 6 9-6"></path>
                    </svg>
                </span>
                <span class="support-text">puptclinic@gmail.com</span>
            </a>

            <a class="support-item" href="tel:+63288375858">
                <span class="support-icon" aria-hidden="true">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M22 16.9v3a2 2 0 0 1-2.2 2 19.8 19.8 0 0 1-8.6-3.1 19.4 19.4 0 0 1-6-6A19.8 19.8 0 0 1 2.1 4.2 2 2 0 0 1 4.1 2h3a2 2 0 0 1 2 1.7c.1 1 .4 2 .7 2.8a2 2 0 0 1-.5 2.1L8.1 9.8a16 16 0 0 0 6 6l1.2-1.2a2 2 0 0 1 2.1-.5c.9.3 1.8.6 2.8.7a2 2 0 0 1 1.8 2.1Z"></path>
                    </svg>
                </span>
                <span class="support-text">(02) 8837-5858</span>
            </a>
        </div>
    </section>

    <footer class="page-note">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
            <path d="M20.8 4.6a5.5 5.5 0 0 0-7.8 0L12 5.6l-1-1a5.5 5.5 0 0 0-7.8 7.8l1 1L12 21l7.8-7.6 1-1a5.5 5.5 0 0 0 0-7.8Z"></path>
        </svg>
        <p>Thank you for your patience and understanding.<span>Your health, our priority.</span></p>
    </footer>
</main>

<svg class="medical-line" viewBox="0 0 180 45" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true">
    <path d="M0 27h35l6-8 6 18 9-29 10 25 7-11 7 5h20l6-7 8 7h66"></path>
</svg>
</body>
</html>
