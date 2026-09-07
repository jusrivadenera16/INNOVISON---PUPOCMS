<!DOCTYPE html>
<html lang="en" data-theme="dark">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Post Login Loader Preview</title>
    <link rel="alternate icon" href="{{ asset('favicon.ico') }}">
    <style>
        :root {
            color-scheme: dark;
            --preview-bg: #160911;
            --preview-panel: rgba(15, 23, 42, 0.72);
            --preview-panel-border: rgba(250, 204, 21, 0.24);
            --preview-text: #ffffff;
            --preview-muted: #cbd5e1;
            --preview-maroon: #8b1020;
            --preview-yellow: #facc15;
        }

        html[data-theme="light"] {
            color-scheme: light;
            --preview-bg: #fff7ed;
            --preview-panel: rgba(255, 255, 255, 0.9);
            --preview-panel-border: rgba(139, 16, 32, 0.18);
            --preview-text: #1f2937;
            --preview-muted: #64748b;
        }

        * {
            box-sizing: border-box;
        }

        body {
            min-height: 100vh;
            margin: 0;
            font-family: Inter, system-ui, -apple-system, BlinkMacSystemFont, "Segoe UI", sans-serif;
            color: var(--preview-text);
            background:
                linear-gradient(135deg, rgba(51, 8, 13, 0.92), rgba(15, 23, 42, 0.82)),
                url('{{ asset('images/PUPBG.jpg') }}') center / cover fixed;
        }

        html[data-theme="light"] body {
            background:
                linear-gradient(135deg, rgba(255, 255, 255, 0.9), rgba(255, 247, 237, 0.76)),
                url('{{ asset('images/PUPBG.jpg') }}') center / cover fixed;
        }

        .preview-shell {
            width: min(980px, calc(100% - 32px));
            margin: 0 auto;
            padding: 32px 0;
        }

        .preview-header {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 16px;
            margin-bottom: 20px;
        }

        .preview-brand {
            display: flex;
            align-items: center;
            gap: 12px;
            min-width: 0;
        }

        .preview-brand img {
            width: 54px;
            height: 54px;
            border-radius: 50%;
            object-fit: cover;
            background: #ffffff;
            padding: 2px;
            border: 1px solid rgba(255, 255, 255, 0.4);
        }

        .preview-brand h1 {
            margin: 0;
            font-size: 20px;
            line-height: 1.1;
            letter-spacing: 0;
        }

        .preview-brand p {
            margin: 4px 0 0;
            color: var(--preview-muted);
            font-size: 13px;
        }

        .preview-actions {
            display: flex;
            flex-wrap: wrap;
            justify-content: flex-end;
            gap: 8px;
        }

        .preview-btn {
            min-height: 42px;
            border: 1px solid rgba(250, 204, 21, 0.55);
            border-radius: 8px;
            padding: 0 14px;
            background: #8b1020;
            color: #ffffff;
            font-weight: 800;
            cursor: pointer;
        }

        .preview-btn.is-secondary {
            background: var(--preview-panel);
            color: var(--preview-text);
        }

        .preview-stage {
            position: relative;
            min-height: 560px;
            overflow: hidden;
            border: 1px solid var(--preview-panel-border);
            border-radius: 8px;
            background:
                linear-gradient(180deg, rgba(255, 255, 255, 0.08), transparent 42%),
                var(--preview-panel);
            box-shadow: 0 22px 60px rgba(0, 0, 0, 0.24);
        }

        .mock-page {
            display: grid;
            min-height: 560px;
            grid-template-rows: 76px 1fr;
            opacity: 0.28;
            filter: blur(1px);
        }

        .mock-topbar {
            display: flex;
            align-items: center;
            gap: 12px;
            padding: 0 24px;
            background: #8b1020;
            border-bottom: 3px solid #facc15;
        }

        .mock-logo {
            width: 42px;
            height: 42px;
            border-radius: 50%;
            background: #ffffff;
        }

        .mock-lines {
            display: grid;
            gap: 8px;
        }

        .mock-line {
            height: 10px;
            border-radius: 999px;
            background: rgba(255, 255, 255, 0.45);
        }

        .mock-line.is-short {
            width: 120px;
        }

        .mock-line.is-long {
            width: 190px;
        }

        .mock-body {
            padding: 26px;
        }

        .mock-card {
            height: 118px;
            margin-bottom: 16px;
            border: 1px solid rgba(250, 204, 21, 0.18);
            border-radius: 8px;
            background: rgba(15, 23, 42, 0.7);
        }

        html[data-theme="light"] .mock-card {
            background: rgba(255, 255, 255, 0.82);
            border-color: rgba(139, 16, 32, 0.12);
        }

        .post-login-loader {
            position: absolute;
            inset: 0;
            z-index: 20;
            display: flex;
            align-items: center;
            justify-content: center;
            background:
                radial-gradient(ellipse 42% 58% at 50% 50%, rgba(255, 246, 241, 0.78) 0%, rgba(244, 194, 197, 0.62) 20%, rgba(156, 36, 55, 0.52) 42%, transparent 70%),
                radial-gradient(ellipse 88% 72% at 12% 50%, rgba(235, 118, 130, 0.38) 0%, rgba(139, 16, 32, 0.18) 42%, transparent 70%),
                radial-gradient(ellipse 88% 72% at 88% 50%, rgba(235, 118, 130, 0.38) 0%, rgba(139, 16, 32, 0.18) 42%, transparent 70%),
                linear-gradient(90deg, #4a0b18 0%, #8f1b2d 42%, #7a1425 50%, #8f1b2d 58%, #4a0b18 100%);
            backdrop-filter: blur(4px);
            -webkit-backdrop-filter: blur(4px);
            opacity: 1;
            visibility: visible;
            transition: opacity 0.25s ease, visibility 0.25s ease;
        }

        .post-login-loader::before {
            content: "";
            position: absolute;
            inset: 0;
            background-image:
                radial-gradient(circle, rgba(255, 255, 255, 0.22) 1px, transparent 1.5px),
                radial-gradient(circle, rgba(250, 204, 21, 0.16) 1px, transparent 1.5px);
            background-size: 54px 54px, 86px 86px;
            background-position: 10px 12px, 36px 42px;
            opacity: 0.55;
            pointer-events: none;
        }

        .post-login-loader.hidden {
            opacity: 0;
            visibility: hidden;
            pointer-events: none;
        }

        .loader-bg-icons {
            position: absolute;
            inset: 0;
            overflow: hidden;
            pointer-events: none;
        }

        .loader-bg-icon {
            position: absolute;
            display: inline-flex;
            width: 38px;
            height: 38px;
            align-items: center;
            justify-content: center;
            color: rgba(255, 255, 255, 0.18);
            border: 1px solid rgba(255, 255, 255, 0.11);
            border-radius: 10px;
            background: rgba(255, 255, 255, 0.035);
            transform: rotate(var(--icon-tilt, 0deg));
        }

        .loader-bg-icon svg {
            width: 20px;
            height: 20px;
            stroke: currentColor;
            stroke-width: 1.8;
            fill: none;
            stroke-linecap: round;
            stroke-linejoin: round;
        }

        .loader-bg-icon.is-one {
            top: 18%;
            left: 18%;
            --icon-tilt: -10deg;
        }

        .loader-bg-icon.is-two {
            top: 22%;
            right: 20%;
            --icon-tilt: 12deg;
        }

        .loader-bg-icon.is-three {
            bottom: 20%;
            left: 24%;
            --icon-tilt: 8deg;
        }

        .loader-bg-icon.is-four {
            right: 22%;
            bottom: 18%;
            --icon-tilt: -8deg;
        }

        .post-login-loader-card {
            position: absolute;
            inset: 0;
            display: grid;
            grid-template-rows: 1fr auto;
            align-items: center;
            justify-items: center;
            gap: 22px;
            padding: clamp(28px, 5vw, 52px);
            text-align: center;
            color: #ffffff;
            font-family: inherit;
        }

        .capsule-loader-content {
            width: min(360px, 58vmin);
            height: min(360px, 58vmin);
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .capsule-loader {
            width: min(106px, 15vmin);
            height: min(282px, 40vmin);
            display: flex;
            align-items: center;
            justify-content: center;
            flex-direction: column;
            transform: rotate(180deg);
            animation: capsuleSpin 6.5s linear infinite;
        }

        .capsule-loader .side {
            position: relative;
            overflow: hidden;
            width: min(78px, 11vmin);
            height: min(106px, 15vmin);
            border-radius: min(43px, 6vmin) min(43px, 6vmin) 0 0;
            background: linear-gradient(145deg, #ffe082 0%, #f7c340 46%, #c99112 100%);
            box-shadow: 0 18px 36px rgba(0, 0, 0, 0.22);
        }

        .capsule-loader .side + .side {
            border-top: min(7px, 1vmin) solid #58121a;
            border-radius: 0 0 min(43px, 6vmin) min(43px, 6vmin);
            background: linear-gradient(145deg, #b63446 0%, #8b1020 48%, #4a0b18 100%);
            animation: capsuleOpen 2s ease-in-out infinite;
        }

        .capsule-loader .side::before {
            content: "";
            position: absolute;
            right: min(11px, 1.5vmin);
            bottom: 0;
            width: min(14px, 2vmin);
            height: min(70px, 10vmin);
            border-radius: min(7px, 1vmin) min(7px, 1vmin) 0 0;
            background: rgba(255, 255, 255, 0.2);
            animation: capsuleShine 1s ease-out -1s infinite alternate-reverse;
        }

        .capsule-loader .side + .side::before {
            top: 0;
            bottom: auto;
            border-radius: 0 0 min(7px, 1vmin) min(7px, 1vmin);
        }

        .capsule-loader .side::after {
            content: "";
            position: absolute;
            left: 0;
            bottom: 0;
            width: 100%;
            height: 100%;
            border: min(12px, 1.75vmin) solid rgba(0, 0, 0, 0.16);
            border-top-width: min(7px, 1vmin);
            border-bottom-color: transparent;
            border-bottom-width: 0;
            border-radius: min(43px, 6vmin) min(43px, 6vmin) 0 0;
            animation: capsuleShadow 1s ease -1s infinite alternate-reverse;
        }

        .capsule-loader .side + .side::after {
            top: 0;
            bottom: auto;
            border-top-color: transparent;
            border-top-width: 0;
            border-bottom-width: min(7px, 1vmin);
            border-radius: 0 0 min(43px, 6vmin) min(43px, 6vmin);
        }

        .capsule-medicine {
            position: absolute;
            width: calc(100% - min(42px, 6vmin));
            height: calc(100% - min(84px, 12vmin));
            display: flex;
            align-items: center;
            justify-content: center;
            border-radius: min(35px, 5vmin);
        }

        .capsule-medicine i {
            position: absolute;
            width: min(7px, 1vmin);
            height: min(7px, 1vmin);
            border-radius: 999px;
            background: #fdf2f8;
            box-shadow: 0 0 14px rgba(255, 255, 255, 0.5);
            animation: capsuleMedicineDust 1.75s ease infinite alternate;
        }

        .capsule-medicine i:nth-child(2n + 2) {
            width: min(11px, 1.5vmin);
            height: min(11px, 1.5vmin);
            margin-top: min(-35px, -5vmin);
            margin-right: min(-35px, -5vmin);
            animation-delay: -0.2s;
        }

        .capsule-medicine i:nth-child(3n + 3) {
            width: min(14px, 2vmin);
            height: min(14px, 2vmin);
            margin-top: min(28px, 4vmin);
            margin-right: min(21px, 3vmin);
            animation-delay: -0.33s;
        }

        .capsule-medicine i:nth-child(4) { margin-top: min(-35px, -5vmin); margin-right: min(28px, 4vmin); animation-delay: -0.4s; }
        .capsule-medicine i:nth-child(5) { margin-top: min(35px, 5vmin); margin-right: min(-28px, -4vmin); animation-delay: -0.5s; }
        .capsule-medicine i:nth-child(6) { margin-top: 0; margin-right: min(-25px, -3.5vmin); animation-delay: -0.66s; }
        .capsule-medicine i:nth-child(7) { margin-top: min(-7px, -1vmin); margin-right: min(49px, 7vmin); animation-delay: -0.7s; }
        .capsule-medicine i:nth-child(8) { margin-top: min(42px, 6vmin); margin-right: min(-7px, -1vmin); animation-delay: -0.8s; }
        .capsule-medicine i:nth-child(9) { margin-top: min(28px, 4vmin); margin-right: min(-49px, -7vmin); animation-delay: -0.99s; }
        .capsule-medicine i:nth-child(10) { margin-top: min(-42px, -6vmin); margin-right: 0; animation-delay: -1.11s; }
        .capsule-medicine i:nth-child(1n + 10) { width: min(5px, .6vmin); height: min(5px, .6vmin); }
        .capsule-medicine i:nth-child(11) { margin-top: min(42px, 6vmin); margin-right: min(42px, 6vmin); animation-delay: -1.125s; }
        .capsule-medicine i:nth-child(12) { margin-top: min(-49px, -7vmin); margin-right: min(-49px, -7vmin); animation-delay: -1.275s; }
        .capsule-medicine i:nth-child(13) { margin-top: min(-7px, -1vmin); margin-right: min(21px, 3vmin); animation-delay: -1.33s; }
        .capsule-medicine i:nth-child(14) { margin-top: min(-21px, -3vmin); margin-right: min(-7px, -1vmin); animation-delay: -1.4s; }
        .capsule-medicine i:nth-child(15) { margin-top: min(-7px, -1vmin); margin-right: min(-49px, -7vmin); animation-delay: -1.55s; }

        .loader-bottom-brand {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 12px;
            margin-bottom: clamp(38px, 7vh, 76px);
        }

        .loader-bottom-logo {
            width: clamp(56px, 7vw, 74px);
            aspect-ratio: 1;
            object-fit: contain;
            transform-origin: center;
            animation: bottomLogoPopup 1.8s ease-in-out infinite;
            filter: drop-shadow(0 12px 24px rgba(0, 0, 0, 0.34));
        }

        .loader-bottom-brand .post-login-loader-text {
            margin-top: 0;
        }

        .ripple-loader {
            --size: min(460px, 86vw);
            --duration: 2.5s;
            --ring-bg:
                radial-gradient(circle at 34% 24%, rgba(255, 217, 222, 0.38), transparent 28%),
                linear-gradient(145deg, rgba(220, 92, 108, 0.36) 0%, rgba(139, 16, 32, 0.36) 46%, rgba(52, 7, 18, 0.58) 100%);
            position: relative;
            width: var(--size);
            aspect-ratio: 1;
            pointer-events: none;
        }

        .ripple-loader .box {
            position: absolute;
            inset: var(--inset);
            z-index: calc(var(--i) * -1);
            border-radius: 50%;
            background: var(--ring-bg);
            border: 1px solid rgba(255, 203, 211, 0.13);
            box-shadow:
                rgba(0, 0, 0, 0.38) 0 12px 24px 0,
                inset rgba(255, 215, 221, 0.24) 0 5px 10px -7px;
            animation: ripplePulse var(--duration) infinite ease-in-out;
            animation-delay: calc(var(--i) * 0.15s);
            transition: filter 0.3s ease;
        }

        .ripple-loader .box:last-child {
            filter: blur(24px);
        }

        .ripple-loader .box:not(:last-child):hover {
            filter: brightness(1.45) blur(2px);
        }

        .ripple-loader .logo {
            position: absolute;
            top: 50%;
            left: 50%;
            display: grid;
            place-items: center;
            width: clamp(88px, 14vw, 116px);
            aspect-ratio: 1;
            padding: 0;
            transform: translate(-50%, -50%);
        }

        .ripple-loader .logo-mark {
            width: 100%;
            aspect-ratio: 1;
            object-fit: contain;
            border-radius: 0;
            border: 0;
            background: transparent;
            padding: 0;
            animation: logoGlow var(--duration) infinite ease-in-out;
            filter: drop-shadow(0 14px 26px rgba(0, 0, 0, 0.26));
        }

        .post-login-loader-text {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            min-height: 20px;
            margin-top: 10px;
            font-size: 14px;
            font-weight: 700;
            letter-spacing: 0.04em;
        }

        .post-login-loader-text span {
            display: inline-block;
            opacity: 0.18;
            transform: translateY(5px);
            animation: loadingLetterReveal 1.65s infinite ease-in-out;
            animation-delay: calc(var(--letter-index) * 0.08s);
        }

        .terms-preview {
            position: absolute;
            left: 50%;
            top: 50%;
            z-index: 10;
            width: min(520px, calc(100% - 40px));
            padding: 20px;
            border: 1px solid rgba(250, 204, 21, 0.24);
            border-radius: 8px;
            background: rgba(255, 255, 255, 0.95);
            color: #1f2937;
            box-shadow: 0 24px 50px rgba(0, 0, 0, 0.28);
            transform: translate(-50%, -50%);
        }

        .terms-preview h2 {
            margin: 0 0 8px;
            color: #8b1020;
            font-size: 20px;
        }

        .terms-preview p {
            margin: 0;
            color: #475569;
            line-height: 1.55;
        }

        @keyframes ripplePulse {
            0%, 100% {
                transform: scale(1);
                box-shadow:
                    rgba(0, 0, 0, 0.38) 0 12px 24px 0,
                    inset rgba(255, 255, 255, 0.22) 0 5px 10px -7px;
            }
            65% {
                transform: scale(1.4);
                box-shadow: rgba(0, 0, 0, 0) 0 0 0 0;
            }
        }

        @keyframes capsuleSpin {
            100% {
                transform: rotate(-540deg);
            }
        }

        @keyframes capsuleOpen {
            0%, 20%, 80%, 100% {
                margin-top: 0;
            }
            30%, 70% {
                margin-top: min(70px, 10vmin);
            }
        }

        @keyframes capsuleShine {
            0%, 46% {
                right: min(11px, 1.5vmin);
            }
            54%, 100% {
                right: min(53px, 7.5vmin);
            }
        }

        @keyframes capsuleShadow {
            0%, 49.999% {
                transform: rotateY(0deg);
                left: 0;
            }
            50%, 100% {
                transform: rotateY(180deg);
                left: min(-21px, -3vmin);
            }
        }

        @keyframes capsuleMedicineDust {
            0%, 100% {
                transform: translate3d(0, 0, 0);
            }
            25% {
                transform: translate3d(min(2px, .25vmin), min(35px, 5vmin), 0);
            }
            75% {
                transform: translate3d(min(-1px, -.1vmin), min(-28px, -4vmin), 0);
            }
        }

        @keyframes bottomLogoPopup {
            0%, 100% {
                transform: scale(1);
                filter: saturate(0.95) drop-shadow(0 12px 24px rgba(0, 0, 0, 0.34));
            }
            12% {
                transform: scale(1.18);
                filter: saturate(1.1) brightness(1.08) drop-shadow(0 18px 30px rgba(0, 0, 0, 0.38));
            }
            24% {
                transform: scale(0.96);
            }
            36% {
                transform: scale(1.06);
            }
            48% {
                transform: scale(1);
            }
        }

        @keyframes logoGlow {
            0%, 100% {
                transform: scale(1);
                filter: saturate(0.95) drop-shadow(0 14px 26px rgba(0, 0, 0, 0.26));
            }
            50% {
                transform: scale(1.05);
                filter: saturate(1.1) brightness(1.08) drop-shadow(0 18px 34px rgba(0, 0, 0, 0.32));
            }
        }

        @keyframes loadingLetterReveal {
            0%, 100% {
                opacity: 0.18;
                transform: translateY(5px);
            }
            22%, 58% {
                opacity: 1;
                transform: translateY(0);
            }
        }

        @media (max-width: 720px) {
            .preview-header {
                align-items: flex-start;
                flex-direction: column;
            }

            .preview-actions {
                justify-content: flex-start;
            }

            .preview-stage,
            .mock-page {
                min-height: 500px;
            }
        }
    </style>
</head>
<body>
    <main class="preview-shell">
        <header class="preview-header">
            <div class="preview-brand">
                <img src="{{ asset('images/clinic_logo_transparent.png') }}?v={{ filemtime(public_path('images/clinic_logo_transparent.png')) }}" alt="Clinic Logo">
                <div>
                    <h1>Post Login Loader Preview</h1>
                    <p>Same loader before the Terms and Conditions modal appears.</p>
                </div>
            </div>
            <div class="preview-actions">
                <button type="button" class="preview-btn" id="replayLoader">Replay 1s Flow</button>
                <button type="button" class="preview-btn is-secondary" id="holdLoader">Hold Loader</button>
                <button type="button" class="preview-btn is-secondary" id="toggleTheme">Light Mode</button>
            </div>
        </header>

        <section class="preview-stage" aria-label="Loader preview">
            <div class="mock-page" aria-hidden="true">
                <div class="mock-topbar">
                    <span class="mock-logo"></span>
                    <span class="mock-lines">
                        <span class="mock-line is-long"></span>
                        <span class="mock-line is-short"></span>
                    </span>
                </div>
                <div class="mock-body">
                    <div class="mock-card"></div>
                    <div class="mock-card"></div>
                    <div class="mock-card"></div>
                </div>
            </div>

            <div class="terms-preview" id="termsPreview" hidden>
                <h2>Terms and Conditions</h2>
                <p>This is only a placeholder so you can see where the loader hands off after login.</p>
            </div>

            <div class="post-login-loader" id="postLoginLoaderPreview" aria-live="polite" aria-label="Loading">
                <div class="loader-bg-icons" aria-hidden="true">
                    <span class="loader-bg-icon is-one">
                        <svg viewBox="0 0 24 24">
                            <path d="M12 5v14"></path>
                            <path d="M5 12h14"></path>
                        </svg>
                    </span>
                    <span class="loader-bg-icon is-two">
                        <svg viewBox="0 0 24 24">
                            <path d="M8 4h8"></path>
                            <path d="M9 2h6v4H9z"></path>
                            <path d="M7 5h10a2 2 0 0 1 2 2v13H5V7a2 2 0 0 1 2-2z"></path>
                            <path d="M9 12h6"></path>
                            <path d="M9 16h4"></path>
                        </svg>
                    </span>
                    <span class="loader-bg-icon is-three">
                        <svg viewBox="0 0 24 24">
                            <path d="M6 5v5a6 6 0 0 0 12 0V5"></path>
                            <path d="M9 5H5"></path>
                            <path d="M19 5h-4"></path>
                            <path d="M12 16v2a3 3 0 0 0 6 0v-1"></path>
                            <circle cx="19" cy="15" r="1.8"></circle>
                        </svg>
                    </span>
                    <span class="loader-bg-icon is-four">
                        <svg viewBox="0 0 24 24">
                            <path d="M8 3h8"></path>
                            <path d="M10 3v5l-4 7a4 4 0 0 0 3.5 6h5a4 4 0 0 0 3.5-6l-4-7V3"></path>
                            <path d="M8 15h8"></path>
                        </svg>
                    </span>
                </div>
                <div class="post-login-loader-card">
                    <div class="capsule-loader-content" aria-hidden="true">
                        <div class="capsule-loader">
                            <div class="capsule-medicine">
                                <i></i><i></i><i></i><i></i><i></i>
                                <i></i><i></i><i></i><i></i><i></i>
                                <i></i><i></i><i></i><i></i><i></i>
                                <i></i><i></i><i></i><i></i><i></i>
                            </div>
                            <div class="side"></div>
                            <div class="side"></div>
                        </div>
                    </div>
                    <div class="loader-bottom-brand">
                        <img src="{{ asset('images/clinic_logo_transparent.png') }}?v={{ filemtime(public_path('images/clinic_logo_transparent.png')) }}" alt="Clinic Logo" class="loader-bottom-logo">
                        <div class="post-login-loader-text" aria-label="Loading">
                            <span style="--letter-index: 0;">L</span>
                            <span style="--letter-index: 1;">o</span>
                            <span style="--letter-index: 2;">a</span>
                            <span style="--letter-index: 3;">d</span>
                            <span style="--letter-index: 4;">i</span>
                            <span style="--letter-index: 5;">n</span>
                            <span style="--letter-index: 6;">g</span>
                            <span style="--letter-index: 7;">.</span>
                            <span style="--letter-index: 8;">.</span>
                            <span style="--letter-index: 9;">.</span>
                        </div>
                    </div>
                </div>
            </div>
        </section>
    </main>

    <script>
        (function () {
            const loader = document.getElementById('postLoginLoaderPreview');
            const terms = document.getElementById('termsPreview');
            const replay = document.getElementById('replayLoader');
            const hold = document.getElementById('holdLoader');
            const toggle = document.getElementById('toggleTheme');
            let timer = null;
            let isHolding = true;

            function showLoader() {
                window.clearTimeout(timer);
                loader.classList.remove('hidden');
                terms.hidden = true;
            }

            function showTerms() {
                loader.classList.add('hidden');
                terms.hidden = false;
            }

            function replayFlow() {
                isHolding = false;
                hold.textContent = 'Hold Loader';
                showLoader();
                timer = window.setTimeout(showTerms, 1000);
            }

            replay.addEventListener('click', replayFlow);

            hold.addEventListener('click', function () {
                isHolding = !isHolding;
                if (isHolding) {
                    showLoader();
                    hold.textContent = 'Show Terms';
                    return;
                }
                showTerms();
                hold.textContent = 'Hold Loader';
            });

            toggle.addEventListener('click', function () {
                const root = document.documentElement;
                const nextTheme = root.dataset.theme === 'dark' ? 'light' : 'dark';
                root.dataset.theme = nextTheme;
                toggle.textContent = nextTheme === 'dark' ? 'Light Mode' : 'Dark Mode';
            });
        })();
    </script>
</body>
</html>
