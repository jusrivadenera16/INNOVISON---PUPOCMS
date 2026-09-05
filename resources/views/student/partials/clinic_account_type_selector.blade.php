<div id="healthRoleSelectorModal" class="health-role-selector-modal" role="dialog" aria-modal="true" aria-labelledby="healthRoleSelectorTitle" hidden>
    <section class="health-role-selector-card">
        <button type="button" class="health-role-selector-close" data-health-role-selector-close aria-label="Close role selector">
            <x-outline-icon name="x-mark" />
        </button>
        <div class="health-role-selector-heading">
            <div>
                <span class="health-role-selector-kicker">Access Type</span>
                <h2 id="healthRoleSelectorTitle">How would you like to continue?</h2>
                <p>Select the account type that applies to you.</p>
            </div>
        </div>
        <form id="healthRoleSelectorForm" action="{{ route('student.account_type.store') }}" data-options-url="{{ route('student.account_type.options') }}" method="POST">
        @csrf
        <div class="health-role-options" role="radiogroup" aria-label="Choose your account type">
            @foreach(\App\Models\User::CLINIC_ACCOUNT_TYPES as $accountType => $accountTypeLabel)
            @php
                $accountTypeIcon = match ($accountType) {
                    'applicant' => 'academic-cap',
                    'student' => 'identification',
                    'faculty' => 'user-circle',
                    'non_teaching_staff' => 'briefcase',
                    default => 'users',
                };
                $accountTypeDisabled = true;
            @endphp
            <label class="health-role-option{{ $accountTypeDisabled ? ' is-disabled' : '' }}" data-health-role-option>
                <input type="radio" name="clinic_account_type" value="{{ $accountType }}" required {{ $accountTypeDisabled ? 'disabled' : '' }}>
                <span class="health-role-radio" aria-hidden="true"></span>
                <span class="health-role-icon" aria-hidden="true"><x-outline-icon :name="$accountTypeIcon" /></span>
                <span class="health-role-copy">
                    <strong>{{ $accountTypeLabel }}</strong>
                </span>
                <span class="health-role-check" aria-hidden="true"><x-outline-icon name="check" /></span>
            </label>
            @endforeach
        </div>
        @if($studentPendingAdmission)
            <p class="health-role-notice">Your admission reference is awaiting clearance. Please continue as an applicant.</p>
        @endif
        <p id="healthRoleSelectorError" class="health-role-error" role="alert" hidden></p>
        <p id="healthRoleSelectorStatus" class="health-role-notice" role="status" hidden>Loading account types...</p>
        <button type="button" id="healthRoleSelectorRetry" class="health-role-retry" hidden>Retry</button>
        <div class="health-role-selector-actions">
            <button class="health-role-continue" id="healthRoleSelectorContinue" type="submit" disabled>Continue</button>
        </div>
        </form>
    </section>
</div>
<style>
    .health-role-selector-modal[hidden] { display: none !important; }
    .health-role-selector-modal {
        position: fixed;
        inset: 0;
        z-index: 1000001;
        display: grid;
        place-items: center;
        padding: 20px;
        background: rgba(15, 23, 42, .62);
        backdrop-filter: blur(8px);
    }
    .health-role-selector-card {
        position: relative;
        width: min(720px, 100%);
        max-height: calc(100dvh - 40px);
        overflow-y: auto;
        padding: 24px 26px 22px;
        border: 1px solid rgba(127, 29, 45, .14);
        border-radius: 8px;
        background: #fff;
        box-shadow: 0 28px 80px rgba(15, 23, 42, .32);
    }
    .health-role-selector-close {
        position: absolute;
        top: 22px;
        right: 24px;
        display: grid;
        place-items: center;
        width: 38px;
        height: 38px;
        border: 1px solid #d1d5db;
        border-radius: 50%;
        background: #fff;
        color: #7f1d2d;
        cursor: pointer;
        isolation: isolate;
        overflow: hidden;
        transition: background-color .2s ease, color .2s ease, border-color .2s ease, transform .2s ease;
    }
    .health-role-selector-close::after,
    .health-role-continue::after {
        position: absolute;
        top: -35%;
        bottom: -35%;
        left: -80%;
        width: 34%;
        background: linear-gradient(105deg, transparent, rgba(255, 249, 190, .18) 28%, rgba(255, 249, 190, .9) 50%, rgba(255, 249, 190, .18) 72%, transparent);
        content: "";
        transform: skewX(-18deg);
        opacity: 0;
        pointer-events: none;
    }
    .health-role-selector-close:hover::after,
    .health-role-selector-close:focus-visible::after,
    .health-role-continue:hover::after,
    .health-role-continue:focus-visible::after {
        animation: health-role-light-sweep .7s ease-out;
    }
    @keyframes health-role-light-sweep {
        0% { left: -80%; opacity: 0; }
        18% { opacity: .9; }
        82% { opacity: .9; }
        100% { left: 135%; opacity: 0; }
    }
    .health-role-selector-close:hover,
    .health-role-selector-close:focus-visible {
        border-color: #facc15;
        background: #facc15;
        color: #000;
        transform: translateY(-1px);
        outline: none;
    }
    .health-role-selector-close svg { position: relative; z-index: 1; width: 20px; height: 20px; }
    .health-role-selector-heading { display: flex; align-items: flex-start; padding: 4px 46px 20px 8px; }
    .health-role-selector-kicker { display: block; margin-bottom: 8px; color: #7f1d2d; font-size: 11px; font-weight: 900; letter-spacing: 0; text-transform: uppercase; }
    .health-role-selector-heading h2,
    #healthRoleSelectorTitle { margin: 0; color: #000000 !important; font-size: 24px; line-height: 1.2; }
    html[data-theme="dark"] .health-role-selector-heading h2,
    html[data-theme="dark"] #healthRoleSelectorTitle { color: #000000 !important; }
    .health-role-selector-heading p { margin: 8px 0 0; color: #4b5563; font-size: 16px; }
    .health-role-options { display: grid; grid-template-columns: repeat(2, minmax(0, 1fr)); gap: 14px; }
    .health-role-option { position: relative; isolation: isolate; overflow: hidden; display: grid; grid-template-columns: 22px 42px minmax(0, 1fr) 20px; align-items: center; gap: 8px; min-height: 94px; padding: 12px; border: 1px solid #d1d5db; border-radius: 8px; cursor: pointer; transition: border-color .2s ease, background-color .2s ease; }
    .health-role-option:not(.is-disabled):hover,
    .health-role-option:not(.is-disabled):focus-within {
        border-color: #facc15;
        background: #facc15;
    }
    .health-role-option::after { content: ''; position: absolute; inset-block: -35%; left: -80%; width: 34%; background: rgba(255, 249, 190, .75); transform: skewX(-18deg); opacity: 0; pointer-events: none; }
    .health-role-option:not(.is-disabled):hover::after { animation: health-role-light-sweep .85s ease-out; }
    .health-role-option > span { position: relative; z-index: 1; }
    .health-role-option.is-selected { border-color: #7f1d2d; background: #7f1d2d; }
    .health-role-option.is-selected .health-role-copy strong { color: #fff; }
    .health-role-option:not(.is-disabled):hover .health-role-copy strong,
    .health-role-option:not(.is-disabled):focus-within .health-role-copy strong { color: #64111d; }
    .health-role-option.is-disabled { opacity: .5; cursor: not-allowed; }
    .health-role-option input { position: absolute; opacity: 0; pointer-events: none; }
    .health-role-radio { width: 23px; height: 23px; border: 2px solid #b8b8b8; border-radius: 50%; }
    .health-role-option.is-selected .health-role-radio { border-color: #7f1d2d; box-shadow: inset 0 0 0 5px #fff; background: #7f1d2d; }
    .health-role-icon { display: grid; place-items: center; width: 40px; height: 40px; border-radius: 50%; background: #fff1d6; color: #7f1d2d; }
    .health-role-icon svg { width: 24px; height: 24px; }
    .health-role-copy { display: grid; gap: 8px; }
    .health-role-copy strong { color: #64111d; font-size: 14px; line-height: 1.4; overflow-wrap: break-word; }
    .health-role-copy small { color: #4b5563; font-size: 13px; }
    .health-role-check { display: grid; visibility: hidden; place-items: center; width: 20px; height: 20px; border-radius: 50%; background: #7f1d2d; color: #fff; }
    .health-role-option.is-selected .health-role-check { visibility: visible; }
    .health-role-notice, .health-role-error { margin: 14px 0 0; font-size: 13px; line-height: 1.5; color: #7f1d2d; }
    .health-role-continue:disabled { cursor: wait; opacity: .65; }
    .health-role-retry { margin-top: 12px; padding: 8px 12px; border: 1px solid #7f1d2d; border-radius: 6px; background: #fff; color: #7f1d2d; cursor: pointer; }
    .health-role-retry:hover { background: #facc15; }
    .health-role-check svg { width: 15px; height: 15px; }
    .health-role-selector-actions { display: flex; justify-content: center; margin-top: 24px; }
    .health-role-continue { position: relative; isolation: isolate; overflow: hidden; display: none; align-items: center; justify-content: center; width: 100%; padding: 10px 24px; border: 1px solid #9f1239; border-radius: 6px; background: #7f1d2d; color: #fff; font-size: 16px; font-weight: 700; text-align: center; text-decoration: none; cursor: pointer; transition: background-color .2s ease, color .2s ease, border-color .2s ease, box-shadow .2s ease, transform .2s ease; }
    .health-role-continue.is-visible { display: inline-flex; }
    .health-role-continue:hover,
    .health-role-continue:focus-visible {
        border-color: #facc15;
        background: #facc15;
        color: #000;
        box-shadow: 0 10px 22px rgba(127, 29, 45, .16);
        transform: translateY(-2px);
        outline: none;
    }
    @media (max-width: 620px) {
        .health-role-selector-card { padding: 28px 18px 24px; }
        .health-role-selector-heading h2 { font-size: 20px; }
        .health-role-options { grid-template-columns: 1fr; gap: 12px; }
        .health-role-option { min-height: 100px; padding: 14px; }
        .health-role-selector-actions { margin-top: 24px; }
    }
</style>
<script>
    (function () {
        const promptModal = document.getElementById('healthFormModal');
        const roleModal = document.getElementById('healthRoleSelectorModal');
        const trigger = promptModal?.querySelector('[data-health-role-selector-trigger]');
        const continueLink = document.getElementById('healthRoleSelectorContinue');
        const form = document.getElementById('healthRoleSelectorForm');
        const errorText = document.getElementById('healthRoleSelectorError');
        const statusText = document.getElementById('healthRoleSelectorStatus');
        const retryButton = document.getElementById('healthRoleSelectorRetry');
        const options = Array.from(roleModal?.querySelectorAll('[data-health-role-option]') || []);
        if (!promptModal || !roleModal || !trigger || !continueLink || !form) return;
        let saving = false;
        let loading = false;
        let previousFocus;

        async function loadOptions() {
            if (loading || saving) return;
            loading = true;
            errorText.hidden = true;
            retryButton.hidden = true;
            statusText.hidden = false;
            continueLink.disabled = true;
            continueLink.classList.remove('is-visible');
            options.forEach(option => {
                const input = option.querySelector('input');
                input.disabled = true;
                input.checked = false;
                option.classList.add('is-disabled');
                option.classList.remove('is-selected');
            });
            try {
                const response = await fetch(form.dataset.optionsUrl, {
                    credentials: 'same-origin', headers: { Accept: 'application/json' }
                });
                const result = await response.json().catch(() => ({}));
                if (!response.ok || !Array.isArray(result.allowed_types) || !result.allowed_types.length) {
                    throw new Error(result.errors?.clinic_account_type?.[0] || result.message || 'Unable to load account types. Please retry.');
                }
                options.forEach(option => {
                    const input = option.querySelector('input');
                    input.disabled = !result.allowed_types.includes(input.value);
                    option.classList.toggle('is-disabled', input.disabled);
                });
                if (!roleModal.hidden) roleModal.querySelector('input:not(:disabled)')?.focus();
            } catch (error) {
                errorText.textContent = error.message;
                errorText.hidden = false;
                retryButton.hidden = false;
            } finally {
                loading = false;
                statusText.hidden = true;
            }
        }
        retryButton.addEventListener('click', loadOptions);

        function closeRoleSelector() {
            if (saving) return;
            roleModal.hidden = true;
            promptModal.style.display = 'flex';
            document.body.style.overflow = '';
            previousFocus?.focus();
        }

        trigger.addEventListener('click', function (event) {
            event.preventDefault();
            previousFocus = document.activeElement;
            promptModal.style.display = 'none';
            roleModal.hidden = false;
            document.body.style.overflow = 'hidden';
            roleModal.querySelector('[data-health-role-selector-close]')?.focus();
            loadOptions();
        });

        options.forEach(function (option) {
            option.addEventListener('change', function () {
                const input = option.querySelector('input');
                if (!input || input.disabled || saving) return;
                options.forEach((item) => item.classList.toggle('is-selected', item === option));
                continueLink.disabled = false;
                errorText.hidden = true;
                continueLink.classList.add('is-visible');
            });
        });

        form.addEventListener('submit', async function (event) {
            event.preventDefault();
            if (loading || saving || !form.querySelector('input[type="radio"]:checked:not(:disabled)') || !form.reportValidity()) return;
            const payload = new FormData(form);
            saving = true;
            continueLink.disabled = true;
            continueLink.textContent = 'Saving...';
            form.setAttribute('aria-busy', 'true');
            errorText.hidden = true;
            const enabledInputs = Array.from(form.querySelectorAll('input[type="radio"]:not(:disabled)'));
            enabledInputs.forEach(input => input.disabled = true);
            try {
                const response = await fetch(form.action, {
                    method: 'POST', body: payload, credentials: 'same-origin',
                    headers: { Accept: 'application/json', 'X-Requested-With': 'XMLHttpRequest' }
                });
                const result = await response.json().catch(() => ({}));
                if (!response.ok || !result.redirect) {
                    throw new Error(result.errors?.clinic_account_type?.[0] || result.message || 'Unable to save your account type. Please try again.');
                }
                const destination = new URL(result.redirect, window.location.origin);
                if (destination.origin !== window.location.origin) throw new Error('Unable to open your form.');
                window.location.assign(destination.href);
            } catch (error) {
                errorText.textContent = error.message || 'Unable to save your account type. Please try again.';
                errorText.hidden = false;
                saving = false;
                form.removeAttribute('aria-busy');
                enabledInputs.forEach(input => input.disabled = false);
                continueLink.disabled = false;
                continueLink.textContent = 'Continue';
            }
        });

        roleModal.querySelectorAll('[data-health-role-selector-close]').forEach((button) => button.addEventListener('click', closeRoleSelector));
        roleModal.addEventListener('click', (event) => {
            if (event.target === roleModal) closeRoleSelector();
        });
        document.addEventListener('keydown', (event) => {
            if (event.key === 'Escape' && !roleModal.hidden) closeRoleSelector();
            if (event.key === 'Tab' && !roleModal.hidden) {
                const focusable = Array.from(roleModal.querySelectorAll('button:not(:disabled), input:not(:disabled)')).filter(el => el.getClientRects().length);
                const first = focusable[0];
                const last = focusable[focusable.length - 1];
                if (event.shiftKey && document.activeElement === first) { event.preventDefault(); last?.focus(); }
                if (!event.shiftKey && document.activeElement === last) { event.preventDefault(); first?.focus(); }
            }
        });
    })();
</script>
