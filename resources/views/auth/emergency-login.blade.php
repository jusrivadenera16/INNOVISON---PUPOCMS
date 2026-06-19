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
        }
    </style>
</head>
<body>
    <header class="topbar">
        <a href="{{ url('/') }}" class="brand" aria-label="Clinic home">
            <img src="{{ asset('images/pup_logo.png') }}" alt="PUP Logo">
            <div>
                <div class="brand-title">PUP Taguig Clinic Management System</div>
                <div class="brand-subtitle">Emergency Backup Access</div>
            </div>
        </a>
    </header>

    <main class="shell">
        <section class="panel" aria-labelledby="emergency-login-title">
            <div class="panel-head">
                <div class="head-row">
                    <div class="mark" aria-hidden="true">
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 3.75 3.75 8.25v7.5L12 20.25l8.25-4.5v-7.5L12 3.75Z" />
                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 8.25v7.5m-3-2.25 3 2.25 3-2.25" />
                        </svg>
                    </div>
                    <div class="badge"><span></span> Emergency Access</div>
                </div>

                <h1 class="panel-title" id="emergency-login-title">Emergency Login</h1>
                <p class="panel-copy">Use this only when the IdP or One Portal is unavailable. This opens the clinic side through the configured backup admin or nurse account.</p>

                <div class="status-row" aria-label="Emergency access status">
                    <span class="status-chip">Fallback mode</span>
                    <span class="status-chip">Config-driven</span>
                    <span class="status-chip">Hidden route</span>
                </div>
            </div>

            <div class="panel-body">
                @if($errors->any())
                    <div class="alert alert-error">{{ $errors->first() }}</div>
                @endif

                @if(session('success'))
                    <div class="alert alert-success">{{ session('success') }}</div>
                @endif

                <form method="POST" action="{{ route('system-admin.emergency-login.submit') }}" autocomplete="off">
                    @csrf
                    <div class="field">
                        <label for="email">Email address</label>
                        <div class="input-wrap">
                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M21 8.25v7.5A2.25 2.25 0 0 1 18.75 18H5.25A2.25 2.25 0 0 1 3 15.75v-7.5m18 0A2.25 2.25 0 0 0 18.75 6H5.25A2.25 2.25 0 0 0 3 8.25m18 0-7.47 4.662a2.25 2.25 0 0 1-2.42 0L3 8.25" />
                            </svg>
                            <input type="email" name="email" id="email" value="{{ old('email') }}" placeholder="backup-admin@clinic.local" required autofocus>
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

                    <button type="submit" class="submit">
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 9V5.25a3.75 3.75 0 0 0-7.5 0V9m10.5 0H5.25A1.5 1.5 0 0 0 3.75 10.5v7.5A1.5 1.5 0 0 0 5.25 19.5h13.5a1.5 1.5 0 0 0 1.5-1.5v-7.5A1.5 1.5 0 0 0 18.75 9Z" />
                        </svg>
                        Sign In
                    </button>
                </form>

                <div class="footnote">
                    <strong>Emergency use only</strong>
                    Keep this page unlinked from normal navigation and restrict the configured credentials to trusted clinic staff.
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
    </script>
</body>
</html>
