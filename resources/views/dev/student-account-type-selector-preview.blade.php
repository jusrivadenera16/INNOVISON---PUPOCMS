<!DOCTYPE html>
<html lang="en" data-theme="dark">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Student Account Type Selector Preview</title>
    <style>
        :root {
            --preview-bg: #0b1020;
            --preview-panel: #111827;
            --preview-panel-2: #182235;
            --preview-border: rgba(250, 204, 21, .28);
            --preview-text: #f8fafc;
            --preview-muted: #cbd5e1;
            --preview-subtle: #94a3b8;
            --preview-maroon: #7f1d2d;
            --preview-maroon-dark: #64111d;
            --preview-yellow: #facc15;
            --preview-error: #fca5a5;
        }
        html[data-theme="light"] {
            --preview-bg: #f5f7fb;
            --preview-panel: #ffffff;
            --preview-panel-2: #eef2f7;
            --preview-border: rgba(127, 29, 45, .18);
            --preview-text: #111827;
            --preview-muted: #475569;
            --preview-subtle: #64748b;
            --preview-error: #b91c1c;
        }
        * { box-sizing: border-box; }
        html, body { min-height: 100%; }
        body {
            margin: 0;
            background: var(--preview-bg);
            color: var(--preview-text);
            font-family: Inter, ui-sans-serif, system-ui, -apple-system, BlinkMacSystemFont, "Segoe UI", sans-serif;
        }
        button { font: inherit; }
        .preview-shell { width: min(1180px, calc(100% - 32px)); margin: 0 auto; padding: 24px 0 40px; }
        .preview-toolbar {
            display: flex;
            align-items: flex-start;
            justify-content: space-between;
            gap: 18px;
            margin-bottom: 18px;
        }
        .preview-heading h1 { margin: 0; font-size: clamp(22px, 3vw, 32px); line-height: 1.15; }
        .preview-heading p { margin: 7px 0 0; color: var(--preview-muted); font-size: 14px; }
        .preview-controls { display: flex; flex-wrap: wrap; justify-content: flex-end; gap: 8px; }
        .preview-control {
            min-height: 36px;
            padding: 8px 12px;
            border: 1px solid var(--preview-border);
            border-radius: 6px;
            background: var(--preview-panel);
            color: var(--preview-text);
            font-size: 12px;
            font-weight: 800;
            cursor: pointer;
            transition: background-color .18s ease, border-color .18s ease, color .18s ease, transform .18s ease;
        }
        .preview-control:hover,
        .preview-control:focus-visible { border-color: var(--preview-yellow); background: var(--preview-yellow); color: var(--preview-maroon-dark); transform: translateY(-1px); outline: none; }
        .preview-control.is-active { border-color: var(--preview-yellow); background: var(--preview-yellow); color: var(--preview-maroon-dark); }
        .preview-stage {
            position: relative;
            min-height: 700px;
            overflow: hidden;
            border: 1px solid var(--preview-border);
            border-radius: 10px;
            background:
                linear-gradient(rgba(15, 23, 42, .76), rgba(15, 23, 42, .82)),
                radial-gradient(circle at 15% 15%, rgba(127, 29, 45, .55), transparent 34%),
                var(--preview-panel);
            box-shadow: 0 24px 60px rgba(15, 23, 42, .22);
        }
        .preview-background { position: absolute; inset: 0; padding: 32px; opacity: .62; filter: blur(3px); }
        .mock-nav { width: 190px; height: 100%; border-right: 1px solid rgba(255, 255, 255, .12); }
        .mock-logo { width: 125px; height: 16px; margin: 4px 0 36px; border-radius: 3px; background: rgba(255, 255, 255, .7); }
        .mock-line { height: 12px; margin: 18px 0; border-radius: 3px; background: rgba(255, 255, 255, .25); }
        .mock-line.short { width: 62%; }
        .mock-content { position: absolute; inset: 32px 32px 32px 250px; }
        .mock-title { width: 44%; height: 24px; margin: 4px 0 28px; border-radius: 4px; background: rgba(255, 255, 255, .68); }
        .mock-card-grid { display: grid; grid-template-columns: repeat(3, 1fr); gap: 14px; }
        .mock-card { min-height: 150px; border: 1px solid rgba(255, 255, 255, .15); border-radius: 8px; background: rgba(255, 255, 255, .1); }
        .selector-modal {
            position: absolute;
            inset: 0;
            display: grid;
            place-items: center;
            padding: 28px;
            background: rgba(15, 23, 42, .68);
            backdrop-filter: blur(8px);
        }
        .selector-card {
            position: relative;
            width: min(660px, 100%);
            max-height: calc(100% - 12px);
            overflow-y: auto;
            padding: 24px 26px 22px;
            border: 1px solid rgba(127, 29, 45, .16);
            border-radius: 8px;
            background: #fff;
            color: #111827;
            box-shadow: 0 28px 80px rgba(15, 23, 42, .34);
        }
        .selector-close {
            position: absolute;
            top: 19px;
            right: 22px;
            display: grid;
            place-items: center;
            width: 34px;
            height: 34px;
            border: 1px solid #d1d5db;
            border-radius: 50%;
            background: #fff;
            color: #7f1d2d;
        }
        .selector-close svg { width: 18px; height: 18px; }
        .selector-heading { padding: 2px 44px 18px 4px; }
        .selector-kicker { display: block; margin-bottom: 6px; color: #7f1d2d; font-size: 10px; font-weight: 900; letter-spacing: .04em; text-transform: uppercase; }
        .selector-heading h2 { margin: 0; color: #000; font-size: 23px; line-height: 1.2; }
        .selector-heading p { margin: 7px 0 0; color: #4b5563; font-size: 14px; }
        .preview-state-notice,
        .preview-state-error { display: flex; align-items: center; gap: 9px; margin: 0 0 14px; padding: 10px 12px; border-radius: 6px; font-size: 12px; font-weight: 700; }
        .preview-state-notice { background: #fff8df; color: #64111d; }
        .preview-state-error { background: #fff1f2; color: #991b1b; }
        .preview-spinner { width: 14px; height: 14px; border: 2px solid rgba(127, 29, 45, .22); border-top-color: #7f1d2d; border-radius: 50%; animation: preview-spin .7s linear infinite; }
        @keyframes preview-spin { to { transform: rotate(360deg); } }
        .selector-options { display: grid; grid-template-columns: repeat(2, minmax(0, 1fr)); gap: 10px; }
        .selector-option {
            position: relative;
            display: grid;
            grid-template-columns: minmax(0, 1fr);
            min-height: 82px;
            padding: 0;
            border: 1px solid #d1d5db;
            border-radius: 8px;
            background: #fff;
            overflow: hidden;
            max-height: 160px;
            transform-origin: top center;
            will-change: max-height, opacity, transform;
            transition: border-color .18s ease, background-color .18s ease, box-shadow .18s ease, transform .55s ease-in-out, max-height .6s ease-in-out, opacity .55s ease-in-out;
        }
        .selector-option-label { display: grid; grid-template-columns: 24px 34px minmax(0, 1fr) 22px; align-items: center; gap: 8px; min-height: 82px; padding: 10px; cursor: pointer; }
        .selector-option.is-disabled { opacity: .48; cursor: not-allowed; }
        .selector-option.is-disabled .selector-option-label { cursor: not-allowed; }
        .selector-option:not(.is-disabled):not(.is-selected):hover,
        .selector-option:not(.is-disabled):not(.is-selected):focus-within { border-color: #facc15; background: #facc15; transform: translateY(-2px); box-shadow: 0 10px 18px rgba(127, 29, 45, .12); }
        .selector-option.is-selected { border-color: #7f1d2d; background: #7f1d2d; box-shadow: 0 9px 16px rgba(127, 29, 45, .14); }
        .selector-option.is-selected strong { color: #fff; }
        .selector-option.is-selected small { color: rgba(255, 255, 255, .82); }
        .selector-option input { position: absolute; opacity: 0; pointer-events: none; }
        .selector-radio { position: relative; display: grid; place-items: center; width: 22px; height: 22px; border: 2px solid #b8b8b8; border-radius: 50%; background: #fff; box-shadow: 0 0 0 0 rgba(250, 204, 21, 0); transition: border-color .18s ease, box-shadow .18s ease, transform .18s ease; }
        .selector-radio::after { width: 8px; height: 8px; border-radius: 50%; background: #7f1d2d; content: ""; opacity: 0; transform: scale(.2); transition: opacity .18s ease, transform .18s ease; }
        .selector-option:hover .selector-radio { border-color: #7f1d2d; }
        .selector-option.is-selected .selector-radio { border-color: #fff; box-shadow: 0 0 0 3px rgba(250, 204, 21, .28); background: #fff; transform: scale(1.04); }
        .selector-option.is-selected .selector-radio::after { background: #facc15; opacity: 1; transform: scale(1); }
        .selector-icon { display: grid; place-items: center; width: 32px; height: 32px; border-radius: 50%; background: #fff1d6; color: #7f1d2d; }
        .selector-icon svg { width: 19px; height: 19px; }
        .selector-copy { display: grid; gap: 4px; }
        .selector-copy strong { color: #64111d; font-size: 13px; line-height: 1.32; }
        .selector-copy small { color: #4b5563; font-size: 12px; font-weight: 500; line-height: 1.25; }
        .selector-check { display: grid; visibility: hidden; place-items: center; width: 20px; height: 20px; border-radius: 50%; background: #fff; color: #7f1d2d; }
        .selector-check svg { width: 15px; height: 15px; }
        .selector-option.is-selected .selector-check { visibility: visible; }
        .selector-options { transition: grid-template-columns .28s ease; }
        .selector-options.is-student-focus { grid-template-columns: 1fr; }
        .selector-options.is-student-focus .selector-option.is-student-focused { grid-column: 1; }
        .selector-options.is-student-focus .selector-option.is-student-focused { max-height: 390px; animation: student-shell-open .68s ease-in-out both; }
        .selector-options.is-student-focus .selector-option.is-student-closing { animation: student-shell-close .58s ease-in-out both; pointer-events: none; }
        @keyframes student-shell-open {
            0% { max-height: 82px; opacity: .76; transform: scaleY(.9) translateY(-5px); }
            55% { opacity: .95; transform: scaleY(.98) translateY(-1px); }
            100% { max-height: 390px; opacity: 1; transform: scaleY(1) translateY(0); }
        }
        @keyframes student-shell-close {
            0% { max-height: 390px; opacity: 1; transform: scaleY(1) translateY(0); }
            55% { opacity: .95; transform: scaleY(.98) translateY(-1px); }
            100% { max-height: 82px; opacity: .9; transform: scaleY(.9) translateY(-5px); }
        }
        .selector-options.is-student-focus .selector-option:not(.is-student-focused) {
            max-height: 0;
            min-height: 0;
            margin: 0;
            padding-block: 0;
            border-color: transparent;
            opacity: 0;
            pointer-events: none;
            transform: translateY(-8px) scale(.96);
        }
        .student-type-field { max-height: 250px; margin: 0 10px 12px; opacity: 1; animation: student-type-reveal .5s ease-in-out; transition: max-height .58s ease-in-out, opacity .4s ease-in-out, transform .58s ease-in-out, margin .58s ease-in-out; }
        .student-type-field[hidden] { display: block !important; max-height: 0; margin-top: 0; margin-bottom: 0; opacity: 0; pointer-events: none; transform: translateY(-5px); }
        .student-type-label { display: block; margin-bottom: 8px; color: #fff; font-size: 11px; font-weight: 900; letter-spacing: .03em; text-transform: uppercase; }
        .student-type-options { display: grid; grid-template-columns: 1fr; gap: 7px; }
        .student-type-choice { position: relative; display: flex; align-items: center; width: 100%; gap: 8px; min-height: 32px; padding: 6px 0; border: 0; border-bottom: 1px solid rgba(255, 255, 255, .36); border-radius: 0; background: transparent; color: #fff; font-size: 12px; font-weight: 800; cursor: pointer; transition: background-color .18s ease, border-color .18s ease, color .18s ease, transform .18s ease; }
        .student-type-choice:last-child { border-bottom: 0; }
        .student-type-choice:hover, .student-type-choice:has(input:focus-visible) { border-bottom-color: #facc15; background: rgba(250, 204, 21, .18); transform: translateY(-1px); outline: none; }
        .student-type-choice input { position: absolute; width: 1px; height: 1px; opacity: 0; pointer-events: none; }
        .student-type-radio { position: relative; display: grid; place-items: center; width: 15px; height: 15px; border: 2px solid rgba(255, 255, 255, .78); border-radius: 50%; }
        .student-type-radio::after { width: 5px; height: 5px; border-radius: 50%; background: #64111d; content: ""; opacity: 0; transform: scale(.2); transition: opacity .18s ease, transform .18s ease; }
        .student-type-choice input:checked + .student-type-radio { border-color: #64111d; background: #facc15; box-shadow: 0 0 0 2px rgba(250, 204, 21, .25); }
        .student-type-choice input:checked + .student-type-radio::after { opacity: 1; transform: scale(1); }
        .student-type-choice:has(input:checked) { border-color: #facc15; background: #facc15; color: #64111d; }
        @keyframes student-type-reveal { from { opacity: 0; transform: translateY(-5px); } to { opacity: 1; transform: translateY(0); } }
        .selector-actions { display: flex; justify-content: flex-end; margin-top: 18px; padding-top: 14px; border-top: 1px solid rgba(127, 29, 45, .14); }
        .selector-continue,
        .selector-retry { min-height: 40px; padding: 9px 18px; border: 1px solid #9f1239; border-radius: 6px; font-size: 13px; font-weight: 800; cursor: pointer; }
        .selector-continue { background: #7f1d2d; color: #fff; }
        .selector-continue:disabled { opacity: .52; cursor: wait; }
        .selector-continue:not(:disabled):hover,
        .selector-continue:not(:disabled):focus-visible,
        .selector-retry:hover,
        .selector-retry:focus-visible { border-color: #facc15; background: #facc15; color: #64111d; outline: none; }
        .selector-retry { margin-top: 2px; background: #fff; color: #7f1d2d; }
        .preview-caption { margin: 14px 2px 0; color: var(--preview-subtle); font-size: 12px; text-align: center; }
        [hidden] { display: none !important; }
        @media (max-width: 760px) {
            .preview-toolbar { flex-direction: column; }
            .preview-controls { justify-content: flex-start; }
            .preview-stage { min-height: 760px; }
            .preview-background { padding: 18px; }
            .mock-content { inset: 18px; }
            .mock-nav { display: none; }
            .selector-modal { padding: 14px; }
            .selector-card { padding: 22px 16px 18px; }
            .selector-options { grid-template-columns: 1fr; gap: 12px; }
        }
    </style>
</head>
<body>
    <main class="preview-shell">
        <header class="preview-toolbar">
            <div class="preview-heading">
                <h1>Student Account Type Selector</h1>
                <p>Local preview for loading, successful response, and retry states.</p>
            </div>
            <div class="preview-controls" aria-label="Preview controls">
                <button type="button" class="preview-control" data-state="loading">Loading</button>
                <button type="button" class="preview-control" data-state="loaded">Loaded</button>
                <button type="button" class="preview-control" data-state="error">Error</button>
                <button type="button" class="preview-control" id="previewThemeToggle">Light Mode</button>
            </div>
        </header>

        <section class="preview-stage" aria-label="Student account type selector preview">
            <div class="preview-background" aria-hidden="true">
                <div class="mock-nav">
                    <div class="mock-logo"></div>
                    <div class="mock-line"></div>
                    <div class="mock-line short"></div>
                    <div class="mock-line"></div>
                    <div class="mock-line short"></div>
                </div>
                <div class="mock-content">
                    <div class="mock-title"></div>
                    <div class="mock-card-grid">
                        <div class="mock-card"></div>
                        <div class="mock-card"></div>
                        <div class="mock-card"></div>
                    </div>
                </div>
            </div>

            <div class="selector-modal">
                <section class="selector-card" role="dialog" aria-modal="true" aria-labelledby="previewSelectorTitle">
                    <button type="button" class="selector-close" aria-label="Close preview">
                        <x-outline-icon name="x-mark" />
                    </button>
                    <div class="selector-heading">
                        <span class="selector-kicker">Access Type</span>
                        <h2 id="previewSelectorTitle">How would you like to continue?</h2>
                        <p>Select the account type that applies to you.</p>
                    </div>

                    <div class="preview-state-notice" id="previewLoadingState" role="status" aria-live="polite">
                        <span class="preview-spinner" aria-hidden="true"></span>
                        <span>Loading available account types...</span>
                    </div>
                    <div class="preview-state-error" id="previewErrorState" role="alert" hidden>
                        <span>Unable to load account types. Please try again.</span>
                    </div>

                    <div class="selector-options" role="radiogroup" aria-label="Choose your account type">
                        @php
                            $previewOptions = [
                                ['value' => 'applicant', 'label' => 'Applicant', 'description' => 'For incoming freshmen with an ARN (Admission Reference Number).', 'icon' => 'academic-cap'],
                                ['value' => 'student', 'label' => 'Student', 'description' => 'For Regular Students, Ladderized, Transferees, Shiftees, and OJT.', 'icon' => 'identification'],
                                ['value' => 'faculty', 'label' => 'Faculty', 'description' => 'For teaching personnel and academic staff.', 'icon' => 'user-circle'],
                                ['value' => 'admin', 'label' => 'Non-teaching Staff / Admins', 'description' => 'For administrative and support personnel.', 'icon' => 'briefcase'],
                                ['value' => 'dependent', 'label' => 'Guest', 'description' => 'For guest users needing clinic access.', 'icon' => 'users'],
                            ];
                        @endphp
                        @foreach($previewOptions as $previewOption)
                            <div class="selector-option is-disabled" data-preview-option data-value="{{ $previewOption['value'] }}" role="radio" tabindex="0" aria-checked="false" aria-disabled="true">
                                <label class="selector-option-label" for="previewAccountType{{ ucfirst($previewOption['value']) }}">
                                    <input id="previewAccountType{{ ucfirst($previewOption['value']) }}" type="radio" name="preview_account_type" value="{{ $previewOption['value'] }}" disabled>
                                    <span class="selector-radio" aria-hidden="true"></span>
                                    <span class="selector-icon" aria-hidden="true"><x-outline-icon name="{{ $previewOption['icon'] }}" /></span>
                                    <span class="selector-copy">
                                        <strong>{{ $previewOption['label'] }}</strong>
                                        <small>{{ $previewOption['description'] }}</small>
                                    </span>
                                    <span class="selector-check" aria-hidden="true"><x-outline-icon name="check" /></span>
                                </label>
                                @if($previewOption['value'] === 'student')
                                    <div class="student-type-field" id="previewStudentTypeField" hidden>
                                        <span class="student-type-label">Student Type</span>
                                        <div class="student-type-options" role="radiogroup" aria-label="Choose your student type">
                                            @foreach([
                                                ['value' => 'regular', 'label' => 'Regular'],
                                                ['value' => 'ladderized', 'label' => 'Ladderized'],
                                                ['value' => 'transferee', 'label' => 'Transferee'],
                                                ['value' => 'returnee', 'label' => 'Returnee'],
                                                ['value' => 'shiftee', 'label' => 'Shiftee'],
                                                ['value' => 'ojt', 'label' => 'OJT'],
                                            ] as $studentTypeOption)
                                                <label class="student-type-choice" for="previewStudentType{{ ucfirst($studentTypeOption['value']) }}">
                                                    <input id="previewStudentType{{ ucfirst($studentTypeOption['value']) }}" type="radio" name="preview_student_type" value="{{ $studentTypeOption['value'] }}" disabled>
                                                    <span class="student-type-radio" aria-hidden="true"></span>
                                                    <span>{{ $studentTypeOption['label'] }}</span>
                                                </label>
                                            @endforeach
                                        </div>
                                    </div>
                                @endif
                            </div>
                        @endforeach
                    </div>

                    <button type="button" class="selector-retry" id="previewRetry" hidden>Retry</button>
                    <div class="selector-actions">
                        <button type="button" class="selector-continue" id="previewContinue" disabled hidden>Continue</button>
                    </div>
                </section>
            </div>
        </section>
        <p class="preview-caption">Preview only. No account type is saved and no live API request is made.</p>
    </main>

    <script>
        (function () {
            const root = document.documentElement;
            const stateButtons = Array.from(document.querySelectorAll('[data-state]'));
            const options = Array.from(document.querySelectorAll('[data-preview-option]'));
            const loadingState = document.getElementById('previewLoadingState');
            const errorState = document.getElementById('previewErrorState');
            const retryButton = document.getElementById('previewRetry');
            const continueButton = document.getElementById('previewContinue');
            const optionsContainer = document.querySelector('.selector-options');
            const studentTypeField = document.getElementById('previewStudentTypeField');
            const studentTypeInputs = Array.from(document.querySelectorAll('input[name="preview_student_type"]'));
            const studentOption = options.find(option => option.dataset.value === 'student');
            const studentLabel = studentOption?.querySelector('.selector-copy strong');
            const defaultStudentLabel = studentLabel?.textContent || 'Student';
            const themeButton = document.getElementById('previewThemeToggle');
            let currentState = 'loading';
            let selectedStudentType = '';

            function selectedAccountType() {
                return options.find(option => option.classList.contains('is-selected'))?.dataset.value || '';
            }

            function updateContinueState() {
                const accountSelected = selectedAccountType() !== '';
                const studentTypeReady = selectedAccountType() !== 'student' || selectedStudentType !== '';
                const canContinue = currentState === 'loaded' && accountSelected && studentTypeReady;
                continueButton.disabled = !canContinue;
                continueButton.hidden = !canContinue;
            }

            function resetStudentType() {
                selectedStudentType = '';
                studentTypeInputs.forEach(input => {
                    input.checked = false;
                    input.disabled = true;
                });
                studentTypeField.hidden = true;
                optionsContainer.classList.remove('is-student-focus');
                options.forEach(option => option.classList.remove('is-student-focused'));
                options.forEach(option => option.setAttribute('aria-checked', 'false'));
                if (studentLabel) studentLabel.textContent = defaultStudentLabel;
            }

            function collapseStudentTypeSelection() {
                studentTypeField.hidden = true;
                studentTypeInputs.forEach(input => input.disabled = true);
                studentOption.classList.add('is-student-closing');
                window.setTimeout(() => {
                    studentOption.classList.remove('is-student-closing');
                    optionsContainer.classList.remove('is-student-focus');
                    options.forEach(option => option.classList.remove('is-student-focused'));
                }, 620);
            }

            function openStudentTypeSelection() {
                if (currentState !== 'loaded' || !studentOption) return;
                optionsContainer.classList.add('is-student-focus');
                options.forEach(option => option.classList.toggle('is-student-focused', option === studentOption));
                studentTypeField.hidden = false;
                studentTypeInputs.forEach(input => {
                    input.disabled = false;
                    input.checked = input.value === selectedStudentType;
                });
            }

            function leaveStudentTypeSelection(clearAccount = true) {
                resetStudentType();
                if (clearAccount) {
                    options.forEach(option => {
                        const input = option.querySelector('input');
                        input.checked = false;
                        option.classList.remove('is-selected');
                        option.setAttribute('aria-checked', 'false');
                    });
                }
                updateContinueState();
            }

            function studentTypeLabel(value) {
                return {
                    regular: 'Regular',
                    ladderized: 'Ladderized',
                    transferee: 'Transferee',
                    returnee: 'Returnee',
                    shiftee: 'Shiftee',
                    ojt: 'OJT',
                }[value] || value;
            }

            function setState(state) {
                currentState = state;
                const isLoaded = state === 'loaded';
                const isLoading = state === 'loading';
                loadingState.hidden = !isLoading;
                errorState.hidden = state !== 'error';
                retryButton.hidden = state !== 'error';

                options.forEach(option => {
                    const input = option.querySelector('input');
                    input.disabled = !isLoaded;
                    option.classList.toggle('is-disabled', !isLoaded);
                    option.setAttribute('aria-disabled', String(!isLoaded));
                    if (!isLoaded) {
                        input.checked = false;
                        option.classList.remove('is-selected');
                    }
                });
                if (!isLoaded) {
                    leaveStudentTypeSelection();
                }
                updateContinueState();
                stateButtons.forEach(button => button.classList.toggle('is-active', button.dataset.state === state));
            }

            options.forEach(option => option.addEventListener('change', function () {
                if (currentState !== 'loaded') return;
                options.forEach(item => item.classList.toggle('is-selected', item === option));
                options.forEach(item => item.setAttribute('aria-checked', String(item === option)));
                const isStudent = option.dataset.value === 'student';
                if (isStudent) {
                    openStudentTypeSelection();
                } else {
                    resetStudentType();
                    updateContinueState();
                }
            }));

            options.forEach(option => option.addEventListener('click', function (event) {
                if (currentState !== 'loaded' || option.querySelector('input')?.disabled) return;
                if (event.target.closest('.student-type-field')) return;
                if (option.dataset.value === 'student' && option.classList.contains('is-selected')) openStudentTypeSelection();
            }));

            studentTypeInputs.forEach(input => input.addEventListener('change', function () {
                if (!input.value || !studentLabel) return;
                selectedStudentType = input.value;
                const selectedLabel = `Student - ${studentTypeLabel(selectedStudentType)}`;
                window.setTimeout(() => {
                    collapseStudentTypeSelection();
                    studentLabel.textContent = selectedLabel;
                    updateContinueState();
                    continueButton.focus();
                }, 180);
            }));
            options.forEach(option => option.addEventListener('keydown', function (event) {
                if ((event.key === 'Enter' || event.key === ' ') && currentState === 'loaded') {
                    event.preventDefault();
                    option.querySelector('input')?.click();
                }
            }));

            stateButtons.forEach(button => button.addEventListener('click', () => setState(button.dataset.state)));
            retryButton.addEventListener('click', () => {
                setState('loading');
                window.setTimeout(() => setState('loaded'), 850);
            });
            themeButton.addEventListener('click', () => {
                const nextTheme = root.dataset.theme === 'dark' ? 'light' : 'dark';
                root.dataset.theme = nextTheme;
                themeButton.textContent = nextTheme === 'dark' ? 'Light Mode' : 'Dark Mode';
            });

            setState('loading');
        })();
    </script>
</body>
</html>
