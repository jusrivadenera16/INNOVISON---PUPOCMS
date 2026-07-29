<style>
    .post-login-loader {
        position: fixed;
        inset: 0;
        z-index: 1000002;
        display: flex;
        align-items: center;
        justify-content: center;
        background: rgba(15, 23, 42, 0.74);
        backdrop-filter: blur(4px);
        opacity: 1;
        visibility: visible;
        transition: opacity 0.25s ease, visibility 0.25s ease;
    }

    .post-login-loader.hidden {
        opacity: 0;
        visibility: hidden;
        pointer-events: none;
    }

    .post-login-loader-card {
        text-align: center;
        color: #ffffff;
        font-family: inherit;
    }

    .post-login-loader-logo {
        width: 120px;
        height: 120px;
        object-fit: cover;
        border-radius: 50%;
        border: 2px solid rgba(255, 255, 255, 0.45);
        background: #ffffff;
        padding: 2px;
        animation: postLoginBounce 0.85s ease-in-out infinite;
    }

    .post-login-loader-text {
        margin-top: 10px;
        font-size: 14px;
        font-weight: 700;
        letter-spacing: 0.04em;
    }

    @keyframes postLoginBounce {
        0%, 100% { transform: translateY(0); }
        50% { transform: translateY(-10px); }
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
    <div class="post-login-loader" id="postLoginLoader" aria-live="polite" aria-label="Loading">
        <div class="post-login-loader-card">
            <img src="{{ asset('images/clinic_logo_transparent.png') }}?v={{ filemtime(public_path('images/clinic_logo_transparent.png')) }}" alt="Loading" class="post-login-loader-logo">
            <div class="post-login-loader-text">Loading...</div>
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

            setTimeout(function () {
                if (loader) {
                    loader.classList.add('hidden');
                }
                overlay.classList.add('is-visible');
            }, 1000);

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
