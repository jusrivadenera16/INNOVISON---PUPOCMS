<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Under Maintenance | PUP Taguig Medical Clinic</title>
    <style>
        :root {
            --maroon: #8f1827;
            --maroon-dark: #70131B;
            --gold: #facc15;
            --ink: #111827;
            --muted: #64748b;
            --line: rgba(112, 19, 27, 0.12);
        }

        * {
            box-sizing: border-box;
        }

        body {
            min-height: 100vh;
            margin: 0;
            display: grid;
            place-items: center;
            padding: 24px;
            background:
                linear-gradient(135deg, rgba(51, 8, 13, 0.82), rgba(112, 19, 27, 0.66)),
                url('{{ asset('images/PUPBG.jpg') }}') center center / cover no-repeat fixed;
            color: var(--ink);
            font-family: Inter, ui-sans-serif, system-ui, -apple-system, BlinkMacSystemFont, "Segoe UI", sans-serif;
        }

        body::before {
            content: "";
            position: fixed;
            inset: 0;
            background:
                radial-gradient(circle at 18% 16%, rgba(250, 204, 21, 0.14), transparent 26%),
                radial-gradient(circle at 82% 12%, rgba(255, 255, 255, 0.12), transparent 28%),
                linear-gradient(180deg, rgba(15, 23, 42, 0.08), rgba(15, 23, 42, 0.28));
            pointer-events: none;
        }

        .maintenance-shell {
            width: min(980px, 100%);
            min-height: min(720px, calc(100vh - 48px));
            display: grid;
            place-items: center;
            padding: clamp(24px, 5vw, 58px);
            border: 1px solid rgba(148, 163, 184, 0.16);
            border-radius: 20px;
            background: rgba(255, 255, 255, 0.92);
            box-shadow: 0 24px 70px rgba(15, 23, 42, 0.08);
        }

        .maintenance-content {
            width: min(640px, 100%);
            text-align: center;
        }

        .maintenance-visual {
            position: relative;
            width: min(360px, 100%);
            height: 210px;
            margin: 0 auto 28px;
        }

        .visual-orbit {
            position: absolute;
            inset: 18% 4% 6%;
            border-radius: 999px;
            background: linear-gradient(135deg, rgba(143, 24, 39, 0.06), rgba(250, 204, 21, 0.08));
        }

        .spark {
            position: absolute;
            color: rgba(143, 24, 39, 0.35);
            font-weight: 900;
            animation: sparkle 2.6s ease-in-out infinite;
        }

        .spark.one { left: 4%; top: 18%; }
        .spark.two { right: 2%; top: 30%; animation-delay: .5s; }
        .spark.three { left: 12%; bottom: 18%; animation-delay: 1s; }

        .bot-tile {
            position: absolute;
            left: 50%;
            top: 0;
            width: 150px;
            height: 150px;
            transform: translateX(-62%);
            border-radius: 34px;
            background: linear-gradient(145deg, #7b1020, #a5162d);
            box-shadow: 0 22px 46px rgba(112, 19, 27, 0.25);
            animation: floatBot 3.4s ease-in-out infinite;
        }

        .maintenance-robot-image {
            position: absolute;
            left: 50%;
            top: 4px;
            z-index: 1;
            width: 172px;
            height: 172px;
            transform: translateX(-50%);
            border-radius: 34px;
            object-fit: cover;
            box-shadow: 0 22px 46px rgba(112, 19, 27, 0.25);
            animation: floatBot 3.4s ease-in-out infinite;
        }

        .maintenance-visual .bot-tile {
            display: none;
        }

        .bot-head {
            position: absolute;
            left: 50%;
            top: 48px;
            width: 104px;
            height: 76px;
            transform: translateX(-50%);
            border-radius: 30px;
            background: #ffffff;
            box-shadow: inset 0 -8px 0 rgba(15, 23, 42, 0.08), 0 12px 22px rgba(15, 23, 42, 0.18);
        }

        .bot-face {
            position: absolute;
            left: 15px;
            right: 15px;
            top: 18px;
            height: 38px;
            border-radius: 18px;
            background: linear-gradient(135deg, #3b0713, #5c1020);
        }

        .bot-eye {
            position: absolute;
            top: 12px;
            width: 10px;
            height: 10px;
            border-radius: 50%;
            background: #ffffff;
        }

        .bot-eye.left { left: 18px; }
        .bot-eye.right { right: 18px; }

        .bot-smile {
            position: absolute;
            left: 50%;
            bottom: 8px;
            width: 22px;
            height: 10px;
            transform: translateX(-50%);
            border-bottom: 4px solid #ffffff;
            border-radius: 0 0 999px 999px;
        }

        .bot-antenna {
            position: absolute;
            left: 50%;
            top: 16px;
            width: 6px;
            height: 34px;
            transform: translateX(-50%);
            background: #ffffff;
            border-radius: 999px;
        }

        .bot-antenna::before {
            content: "";
            position: absolute;
            left: 50%;
            top: -10px;
            width: 18px;
            height: 18px;
            transform: translateX(-50%);
            border-radius: 50%;
            background: #ffffff;
            box-shadow: 0 4px 12px rgba(15, 23, 42, 0.22);
        }

        .bot-ear {
            position: absolute;
            top: 72px;
            width: 18px;
            height: 34px;
            border-radius: 999px;
            background: #ffffff;
        }

        .bot-ear.left { left: 16px; }
        .bot-ear.right { right: 16px; }

        .bot-body {
            position: absolute;
            left: 50%;
            bottom: 6px;
            width: 90px;
            height: 42px;
            transform: translateX(-50%);
            border-radius: 28px 28px 18px 18px;
            background: #ffffff;
        }

        .bot-steth {
            position: absolute;
            left: 36px;
            bottom: 10px;
            width: 24px;
            height: 24px;
            border: 5px solid var(--maroon);
            border-radius: 50%;
        }

        .bubble {
            position: absolute;
            left: auto;
            right: -42px;
            top: 4px;
            z-index: 2;
            max-width: 156px;
            padding: 15px 18px;
            border: 2px solid rgba(143, 24, 39, 0.78);
            border-radius: 22px;
            background: #ffffff;
            color: var(--maroon-dark);
            font-size: 16px;
            font-weight: 900;
            line-height: 1.25;
            box-shadow: 0 16px 30px rgba(112, 19, 27, 0.12);
            animation: bubblePop 2.6s cubic-bezier(.2, .9, .2, 1) infinite;
        }

        .bubble::before {
            content: "";
            position: absolute;
            left: -13px;
            right: auto;
            top: 42px;
            width: 24px;
            height: 24px;
            background: #ffffff;
            border-left: 2px solid rgba(143, 24, 39, 0.78);
            border-right: 0;
            border-bottom: 2px solid rgba(143, 24, 39, 0.78);
            transform: rotate(45deg);
        }

        .bubble small {
            display: block;
            margin-top: 7px;
            color: var(--maroon);
            font-size: 18px;
        }

        .status-pill {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            min-height: 34px;
            padding: 0 16px;
            border-radius: 999px;
            background: #fff0f2;
            color: var(--maroon);
            font-size: 12px;
            font-weight: 950;
            letter-spacing: .04em;
            text-transform: uppercase;
        }

        .status-pill svg {
            width: 15px;
            height: 15px;
        }

        h1 {
            margin: 14px 0 10px;
            color: var(--ink);
            font-size: clamp(30px, 4vw, 42px);
            font-weight: 950;
            letter-spacing: -0.02em;
        }

        h1 span {
            color: var(--maroon);
        }

        .lead {
            margin: 0 auto 24px;
            max-width: 560px;
            color: var(--muted);
            font-size: 16px;
            font-weight: 650;
            line-height: 1.65;
        }

        .info-card {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 0;
            margin: 0 auto 14px;
            overflow: hidden;
            border: 1px solid var(--line);
            border-radius: 12px;
            background: #fff8f9;
            text-align: left;
        }

        .info-item {
            display: grid;
            grid-template-columns: 46px minmax(0, 1fr);
            gap: 12px;
            align-items: center;
            padding: 18px 20px;
        }

        .info-item + .info-item {
            border-left: 1px solid var(--line);
        }

        .info-icon,
        .safe-icon {
            display: grid;
            place-items: center;
            width: 44px;
            height: 44px;
            border-radius: 999px;
            background: #fff0f2;
            color: var(--maroon);
        }

        .info-icon svg,
        .safe-icon svg {
            width: 22px;
            height: 22px;
        }

        .label {
            display: block;
            color: var(--maroon);
            font-size: 11px;
            font-weight: 950;
            letter-spacing: .05em;
            text-transform: uppercase;
        }

        .value {
            display: block;
            margin-top: 5px;
            color: var(--ink);
            font-size: 15px;
            font-weight: 900;
        }

        .note {
            display: block;
            margin-top: 4px;
            color: var(--muted);
            font-size: 13px;
            font-weight: 650;
        }

        .safe-card {
            display: grid;
            grid-template-columns: 46px minmax(0, 1fr);
            gap: 12px;
            align-items: center;
            margin: 0 auto 20px;
            padding: 16px 20px;
            border: 1px solid rgba(22, 163, 74, 0.10);
            border-radius: 12px;
            background: #f8fff9;
            text-align: left;
        }

        .safe-icon {
            background: #dcfce7;
            color: #16a34a;
        }

        .safe-card strong {
            display: block;
            color: #15803d;
            font-size: 14px;
            font-weight: 950;
        }

        .safe-card span {
            display: block;
            margin-top: 4px;
            color: var(--muted);
            font-size: 13px;
            font-weight: 650;
        }

        .refresh-btn {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 10px;
            min-height: 48px;
            padding: 0 26px;
            border: 0;
            border-radius: 8px;
            background: linear-gradient(135deg, var(--maroon-dark), var(--maroon));
            color: #ffffff;
            font-size: 15px;
            font-weight: 950;
            cursor: pointer;
            box-shadow: 0 14px 24px rgba(112, 19, 27, 0.18);
            transition: transform .18s ease, background .18s ease, color .18s ease;
        }

        .refresh-btn:hover {
            transform: translateY(-2px);
            background: var(--gold);
            color: var(--maroon-dark);
        }

        .refresh-btn svg {
            width: 18px;
            height: 18px;
        }

        @keyframes floatBot {
            0%, 100% { transform: translateX(-50%) translateY(0); }
            50% { transform: translateX(-50%) translateY(-8px); }
        }

        @keyframes bubblePop {
            0%, 100% { transform: translateY(0) scale(1); }
            12% { transform: translateY(-8px) scale(1.06); }
            24% { transform: translateY(0) scale(.98); }
            36% { transform: translateY(-3px) scale(1.02); }
            48% { transform: translateY(0) scale(1); }
        }

        @keyframes sparkle {
            0%, 100% { opacity: .3; transform: scale(.9); }
            50% { opacity: .9; transform: scale(1.15); }
        }

        @media (max-width: 680px) {
            body {
                padding: 12px;
            }

            .maintenance-shell {
                min-height: calc(100vh - 24px);
                padding: 22px;
            }

            .maintenance-visual {
                width: 300px;
                height: 184px;
            }

            .bot-tile {
                width: 130px;
                height: 130px;
            }

            .maintenance-robot-image {
                left: 50%;
                top: 4px;
                width: 148px;
                height: 148px;
                border-radius: 28px;
            }

            .bubble {
                left: auto;
                right: -26px;
                top: 0;
                max-width: 132px;
                padding: 11px 12px;
                font-size: 13px;
            }

            .bubble::before {
                top: 34px;
            }

            .info-card {
                grid-template-columns: 1fr;
            }

            .info-item + .info-item {
                border-left: 0;
                border-top: 1px solid var(--line);
            }
        }
    </style>
</head>
<body>
@php
    $estimated = null;
    $updated = null;

    try {
        $estimated = $estimatedCompletion ? \Carbon\Carbon::parse($estimatedCompletion) : null;
    } catch (\Throwable $exception) {
        $estimated = null;
    }

    try {
        $updated = $lastUpdated ? \Carbon\Carbon::parse($lastUpdated) : null;
    } catch (\Throwable $exception) {
        $updated = null;
    }
@endphp
<main class="maintenance-shell">
    <section class="maintenance-content" aria-labelledby="maintenance-title">
        <div class="maintenance-visual" aria-hidden="true">
            <div class="visual-orbit"></div>
            <span class="spark one">+</span>
            <span class="spark two">+</span>
            <span class="spark three">+</span>
            <img class="maintenance-robot-image" src="{{ asset('images/clinic-robot.png') }}" alt="">
            <div class="bot-tile">
                <div class="bot-antenna"></div>
                <div class="bot-ear left"></div>
                <div class="bot-ear right"></div>
                <div class="bot-head">
                    <div class="bot-face">
                        <span class="bot-eye left"></span>
                        <span class="bot-eye right"></span>
                        <span class="bot-smile"></span>
                    </div>
                </div>
                <div class="bot-body"></div>
                <div class="bot-steth"></div>
            </div>
            <div class="bubble">
                Thank you for patience!
                <small>♥</small>
            </div>
        </div>

        <div class="status-pill">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">
                <path d="M14.7 6.3a1 1 0 0 0 0 1.4l1.6 1.6a1 1 0 0 0 1.4 0l3.1-3.1a6 6 0 0 1-7.9 7.9l-5.6 5.6a2.1 2.1 0 0 1-3-3l5.6-5.6a6 6 0 0 1 7.9-7.9l-3.1 3.1Z"/>
            </svg>
            Under Maintenance
        </div>

        <h1 id="maintenance-title">This Page is <span>Under Maintenance</span></h1>
        <p class="lead">We're currently performing scheduled maintenance to improve your experience. Please check back soon.</p>

        <div class="info-card">
            <div class="info-item">
                <span class="info-icon">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M12 6v6l4 2"/>
                        <circle cx="12" cy="12" r="9"/>
                    </svg>
                </span>
                <div>
                    <span class="label">Estimated Completion</span>
                    <span class="value">{{ $estimated ? $estimated->format('M d, Y - h:i A') : 'Please check back soon' }}</span>
                    <span class="note">We'll be back shortly!</span>
                </div>
            </div>
            <div class="info-item">
                <span class="info-icon">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M8 2v4M16 2v4M3 10h18"/>
                        <rect x="3" y="4" width="18" height="18" rx="3"/>
                        <path d="M8 14h.01M12 14h.01M16 14h.01M8 18h.01M12 18h.01M16 18h.01"/>
                    </svg>
                </span>
                <div>
                    <span class="label">Last Updated</span>
                    <span class="value">{{ $updated ? $updated->format('M d, Y - h:i A') : now()->format('M d, Y - h:i A') }}</span>
                    <span class="note">Updates are in real-time.</span>
                </div>
            </div>
        </div>

        <div class="safe-card">
            <span class="safe-icon">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10Z"/>
                    <path d="m9 12 2 2 4-5"/>
                </svg>
            </span>
            <div>
                <strong>Your Data is Safe</strong>
                <span>All your data remains secure. There is no action required on your part.</span>
            </div>
        </div>

        <button type="button" class="refresh-btn" onclick="window.location.reload()">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.9" stroke-linecap="round" stroke-linejoin="round">
                <path d="M21 12a9 9 0 0 1-15.5 6.3"/>
                <path d="M3 12A9 9 0 0 1 18.5 5.7"/>
                <path d="M3 19v-5h5"/>
                <path d="M21 5v5h-5"/>
            </svg>
            Refresh Page
        </button>
    </section>
</main>
</body>
</html>
