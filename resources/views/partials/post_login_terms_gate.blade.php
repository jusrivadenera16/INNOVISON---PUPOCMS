<style>
    .post-login-loader {
        position: fixed;
        inset: 0;
        z-index: 1000002;
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

    .post-login-loader-bg-icons {
        position: absolute;
        inset: 0;
        overflow: hidden;
        pointer-events: none;
    }

    .post-login-loader-bg-icon {
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

    .post-login-loader-bg-icon svg {
        width: 20px;
        height: 20px;
        stroke: currentColor;
        stroke-width: 1.8;
        fill: none;
        stroke-linecap: round;
        stroke-linejoin: round;
    }

    .post-login-loader-bg-icon.is-one {
        top: 18%;
        left: 18%;
        --icon-tilt: -10deg;
    }

    .post-login-loader-bg-icon.is-two {
        top: 22%;
        right: 20%;
        --icon-tilt: 12deg;
    }

    .post-login-loader-bg-icon.is-three {
        bottom: 20%;
        left: 24%;
        --icon-tilt: 8deg;
    }

    .post-login-loader-bg-icon.is-four {
        right: 22%;
        bottom: 18%;
        --icon-tilt: -8deg;
    }

    .post-login-loader-card {
        position: absolute;
        inset: 0;
        z-index: 1;
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

    .post-login-ripple-loader {
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

    .post-login-ripple-loader .box {
        position: absolute;
        inset: var(--inset);
        z-index: calc(var(--i) * -1);
        border-radius: 50%;
        background: var(--ring-bg);
        border: 1px solid rgba(255, 203, 211, 0.13);
        box-shadow:
            rgba(0, 0, 0, 0.38) 0 12px 24px 0,
            inset rgba(255, 215, 221, 0.24) 0 5px 10px -7px;
        animation: postLoginRipplePulse var(--duration) infinite ease-in-out;
        animation-delay: calc(var(--i) * 0.15s);
        transition: filter 0.3s ease;
    }

    .post-login-ripple-loader .box:last-child {
        filter: blur(24px);
    }

    .post-login-ripple-loader .logo {
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

    .post-login-loader-logo {
        width: 100%;
        aspect-ratio: 1;
        object-fit: contain;
        border-radius: 0;
        border: 0;
        background: transparent;
        padding: 0;
        animation: postLoginLogoGlow var(--duration) infinite ease-in-out;
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
        animation: postLoginLetterReveal 1.65s infinite ease-in-out;
        animation-delay: calc(var(--letter-index) * 0.08s);
    }

    @keyframes postLoginRipplePulse {
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

    @keyframes postLoginLogoGlow {
        0%, 100% {
            transform: scale(1);
            filter: saturate(0.95) drop-shadow(0 14px 26px rgba(0, 0, 0, 0.26));
        }
        50% {
            transform: scale(1.05);
            filter: saturate(1.1) brightness(1.08) drop-shadow(0 18px 34px rgba(0, 0, 0, 0.32));
        }
    }

    @keyframes postLoginLetterReveal {
        0%, 100% {
            opacity: 0.18;
            transform: translateY(5px);
        }
        22%, 58% {
            opacity: 1;
            transform: translateY(0);
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

    .terms-gate-overlay {
        position: fixed;
        inset: 0;
        z-index: 1000001;
        background: rgba(15, 23, 42, 0.62);
        display: flex;
        align-items: center;
        justify-content: center;
        padding: 16px;
        opacity: 0;
        visibility: hidden;
        pointer-events: none;
        transition: opacity 0.24s ease, visibility 0.24s ease;
    }

    .terms-gate-overlay.is-visible {
        opacity: 1;
        visibility: visible;
        pointer-events: auto;
    }

    .terms-gate-modal {
        width: min(560px, 100%);
        background: #ffffff;
        border-radius: 10px;
        overflow: hidden;
        box-shadow: 0 16px 34px rgba(0, 0, 0, 0.26);
        color: #1f2937;
        font-family: inherit;
    }

    .terms-gate-head {
        background: #8B0000;
        color: #ffffff;
        padding: 12px 16px;
    }

    .terms-gate-head h3 {
        margin: 0;
        font-size: 24px;
        line-height: 1.2;
        font-weight: 800;
    }

    .terms-gate-body {
        padding: 16px;
        border-bottom: 1px solid #e5e7eb;
    }

    .terms-gate-body p {
        margin: 0 0 12px;
        line-height: 1.55;
        font-size: 15px;
        color: #1f2937;
    }

    .terms-gate-body p:last-child {
        margin-bottom: 0;
    }

    .terms-gate-body a {
        color: #8B0000;
        font-weight: 700;
        text-decoration: underline;
    }

    .terms-gate-checkbox {
        margin-top: 14px;
        display: flex;
        align-items: flex-start;
        gap: 8px;
        color: #1f2937;
        font-size: 14px;
    }

    .terms-gate-checkbox input[type="checkbox"] {
        margin-top: 2px;
        width: 16px;
        height: 16px;
        accent-color: #8B0000;
        flex-shrink: 0;
    }

    .terms-gate-actions {
        display: flex;
        justify-content: flex-end;
        gap: 10px;
        padding: 12px 16px;
        background: #f8fafc;
    }

    .terms-gate-btn {
        border: 1px solid transparent;
        border-radius: 7px;
        padding: 8px 14px;
        font-size: 13px;
        font-weight: 700;
        cursor: pointer;
        min-width: 96px;
        font-family: inherit;
    }

    .terms-gate-btn-cancel {
        background: #6b7280;
        border-color: #6b7280;
        color: #ffffff;
    }

    .terms-gate-btn-continue {
        background: #8B0000;
        border-color: #8B0000;
        color: #ffffff;
    }

    .terms-gate-btn-continue:disabled {
        opacity: 0.55;
        cursor: not-allowed;
    }

    body.terms-gate-lock {
        overflow: hidden;
    }

    @media (max-width: 640px) {
        .terms-gate-head h3 {
            font-size: 20px;
        }

        .terms-gate-body p {
            font-size: 14px;
        }

        .terms-gate-checkbox {
            font-size: 13px;
        }

        .terms-gate-actions {
            flex-direction: column;
        }

        .terms-gate-btn {
            width: 100%;
        }
    }
</style>

@if (session('show_terms_modal'))
    <div class="post-login-loader" id="postLoginLoader" aria-live="polite" aria-label="Logging in">
        <div class="post-login-loader-bg-icons" aria-hidden="true">
            <span class="post-login-loader-bg-icon is-one">
                <svg viewBox="0 0 24 24">
                    <path d="M12 5v14"></path>
                    <path d="M5 12h14"></path>
                </svg>
            </span>
            <span class="post-login-loader-bg-icon is-two">
                <svg viewBox="0 0 24 24">
                    <path d="M8 4h8"></path>
                    <path d="M9 2h6v4H9z"></path>
                    <path d="M7 5h10a2 2 0 0 1 2 2v13H5V7a2 2 0 0 1 2-2z"></path>
                    <path d="M9 12h6"></path>
                    <path d="M9 16h4"></path>
                </svg>
            </span>
            <span class="post-login-loader-bg-icon is-three">
                <svg viewBox="0 0 24 24">
                    <path d="M6 5v5a6 6 0 0 0 12 0V5"></path>
                    <path d="M9 5H5"></path>
                    <path d="M19 5h-4"></path>
                    <path d="M12 16v2a3 3 0 0 0 6 0v-1"></path>
                    <circle cx="19" cy="15" r="1.8"></circle>
                </svg>
            </span>
            <span class="post-login-loader-bg-icon is-four">
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
                <div class="post-login-loader-text" aria-label="Logging in">
                    <span style="--letter-index: 0;">L</span>
                    <span style="--letter-index: 1;">o</span>
                    <span style="--letter-index: 2;">g</span>
                    <span style="--letter-index: 3;">g</span>
                    <span style="--letter-index: 4;">i</span>
                    <span style="--letter-index: 5;">n</span>
                    <span style="--letter-index: 6;">g</span>
                    <span style="--letter-index: 7;">&nbsp;</span>
                    <span style="--letter-index: 8;">i</span>
                    <span style="--letter-index: 9;">n</span>
                    <span style="--letter-index: 10;">.</span>
                    <span style="--letter-index: 11;">.</span>
                    <span style="--letter-index: 12;">.</span>
                </div>
            </div>
        </div>
    </div>

    <div class="terms-gate-overlay" id="termsGateOverlay" role="dialog" aria-modal="true" aria-labelledby="termsGateTitle">
        <div class="terms-gate-modal">
            <div class="terms-gate-head">
                <h3 id="termsGateTitle">Terms and Conditions</h3>
            </div>
            <div class="terms-gate-body">
                <p>
                    By clicking <strong>I Agree</strong>, you consent to the collection, use, and processing of your personal data for legitimate purposes related to this service.
                </p>
                <p>
                    Your information will be handled in accordance with our
                    <a href="https://www.pup.edu.ph/privacy/" target="_blank" rel="noopener noreferrer">Privacy Policy</a>
                    and in compliance with the Data Privacy Act of 2012.
                </p>
                <p>
                    Please also review our
                    <a href="https://www.pup.edu.ph/terms/" target="_blank" rel="noopener noreferrer">Terms of Use</a>.
                </p>

                <label class="terms-gate-checkbox" for="termsGateAgree">
                    <input type="checkbox" id="termsGateAgree">
                    <span>I Agree and acknowledge the Terms and Conditions</span>
                </label>
            </div>
            <div class="terms-gate-actions">
                <button type="button" class="terms-gate-btn terms-gate-btn-cancel" id="termsGateCancelBtn">Cancel</button>
                <button type="button" class="terms-gate-btn terms-gate-btn-continue" id="termsGateContinueBtn" disabled>Continue</button>
            </div>
        </div>
    </div>

    <form id="termsGateLogoutForm" method="POST" action="{{ route('logout') }}" style="display:none;">
        @csrf
        <input type="hidden" name="portal_guard" value="{{ request()->is('admin/*') || request()->is('assistant/*') || request()->is('health-records') || request()->is('health-profile/*') ? 'admin' : 'student' }}">
    </form>

    <script>
        (function () {
            const loader = document.getElementById('postLoginLoader');
            const overlay = document.getElementById('termsGateOverlay');
            const agreeInput = document.getElementById('termsGateAgree');
            const continueBtn = document.getElementById('termsGateContinueBtn');
            const cancelBtn = document.getElementById('termsGateCancelBtn');
            const logoutForm = document.getElementById('termsGateLogoutForm');

            if (!overlay || !agreeInput || !continueBtn || !cancelBtn || !logoutForm) {
                return;
            }

            function syncContinueState() {
                continueBtn.disabled = !agreeInput.checked;
            }

            syncContinueState();
            document.body.classList.add('terms-gate-lock');

            const minimumLoaderMs = 3000;
            const fallbackLoaderMs = 12000;
            let minimumTimeElapsed = false;
            let pageIsReady = document.readyState === 'complete';
            let termsShown = false;

            function showTermsGate() {
                if (termsShown || !minimumTimeElapsed || !pageIsReady) {
                    return;
                }
                termsShown = true;
                if (loader) {
                    loader.classList.add('hidden');
                }
                overlay.classList.add('is-visible');
            }

            setTimeout(function () {
                minimumTimeElapsed = true;
                showTermsGate();
            }, minimumLoaderMs);

            if (pageIsReady) {
                showTermsGate();
            } else {
                window.addEventListener('load', function () {
                    pageIsReady = true;
                    showTermsGate();
                }, { once: true });
            }

            setTimeout(function () {
                pageIsReady = true;
                showTermsGate();
            }, fallbackLoaderMs);

            agreeInput.addEventListener('change', syncContinueState);

            continueBtn.addEventListener('click', function () {
                if (!agreeInput.checked) {
                    return;
                }
                fetch('{{ route('post-login-terms.acknowledge') }}', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': '{{ csrf_token() }}',
                        'Accept': 'application/json'
                    },
                    body: JSON.stringify({})
                }).catch(function () {});
                overlay.remove();
                document.body.classList.remove('terms-gate-lock');
            });

            cancelBtn.addEventListener('click', function () {
                logoutForm.submit();
            });
        })();
    </script>
@endif
