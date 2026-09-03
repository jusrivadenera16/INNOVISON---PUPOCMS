<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Fill Up - Health Profile</title>
    <script
        src="{{ asset('js/sienna-accessibility-custom.umd.js') }}?v={{ filemtime(public_path('js/sienna-accessibility-custom.umd.js')) }}"
        data-asw-position="bottom-right"
        data-asw-offset="24,12"
        data-asw-size="small"
        defer
    ></script>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        :root {
            --clinic-maroon: #7f1d2d;
            --clinic-maroon-dark: #5f0012;
            --clinic-yellow: #facc15;
            --panel: #ffffff;
            --field: #f8fafc;
            --border: #d1d5db;
        }

        body {
            margin: 0;
            font-family: 'Segoe UI', sans-serif;
            background:
                linear-gradient(rgba(39, 14, 17, 0.82), rgba(22, 8, 8, 0.84)),
                url('{{ asset('images/PUPBG.jpg') }}') center center / cover no-repeat fixed;
            padding: 28px 12px 120px;
        }

        body.health-form-page .system-footer {
            position: fixed;
            left: 0;
            right: 0;
            bottom: 0;
            z-index: 100;
            box-shadow: 0 -12px 30px rgba(15, 23, 42, 0.18);
        }

        .health-shell {
            max-width: 980px;
            margin: 0 auto;
            background:
                linear-gradient(rgba(255, 255, 255, .24), rgba(255, 255, 255, .38)),
                url('{{ asset('images/hif_bg.png') }}') center / cover no-repeat,
                rgba(255, 255, 255, 0.97);
            border: 1px solid rgba(127, 29, 45, 0.16);
            border-radius: 24px;
            box-shadow: 0 20px 40px rgba(15, 23, 42, 0.18);
            overflow: hidden;
        }

        .health-header {
            height: 12px;
            background: linear-gradient(135deg, var(--clinic-maroon) 0%, var(--clinic-maroon-dark) 100%);
            border-bottom: 2px solid var(--clinic-yellow);
        }

        .form-intro {
            margin-bottom: 18px;
        }

        .form-intro h1 {
            display: flex;
            align-items: center;
            gap: 10px;
            margin: 0;
            font-size: 1.6rem;
            font-weight: 800;
            color: #70131b;
        }

        .form-intro h1::before,
        .section-title.step-page-title::before {
            content: attr(data-title-letter);
            display: inline-grid;
            place-items: center;
            width: 34px;
            height: 34px;
            border: 1.5px solid rgba(127, 29, 45, 0.5);
            border-radius: 50%;
            background: #fff7f8;
            color: var(--clinic-maroon);
            font-size: 0.86rem;
            font-weight: 900;
            line-height: 1;
            flex: 0 0 auto;
        }

        .form-intro p {
            margin: 8px 0 0;
            font-size: 0.95rem;
            color: #4b5563;
        }

        .section-body {
            padding: 24px 28px 28px;
        }

        .section-title {
            margin: 0 0 16px;
            font-size: 1.05rem;
            font-weight: 800;
            color: var(--clinic-maroon);
            border-bottom: 2px solid rgba(127, 29, 45, 0.12);
            padding-bottom: 8px;
        }

        .section-title.step-page-title {
            display: flex;
            align-items: center;
            gap: 10px;
            font-size: 1.6rem;
            font-weight: 800;
        }


        .stepper-shell {
            position: fixed;
            left: 0;
            right: 0;
            top: 0;
            width: 100%;
            box-sizing: border-box;
            z-index: 50;
            color: #111827;
            pointer-events: none;
        }

        .step-progress-top {
            display: grid;
            grid-template-columns: 260px minmax(0, 1fr) 136px;
            align-items: center;
            gap: 16px;
            min-height: 64px;
            padding: 8px 24px;
            background: linear-gradient(135deg, var(--clinic-maroon) 0%, var(--clinic-maroon-dark) 100%);
            border-bottom: 3px solid var(--clinic-yellow);
            box-shadow: 0 12px 30px rgba(15, 23, 42, 0.18);
            pointer-events: auto;
        }

        .step-brand {
            display: flex;
            align-items: center;
            gap: 12px;
            min-width: 0;
        }

        .step-brand img {
            width: 44px;
            height: 44px;
            object-fit: contain;
            border-radius: 50%;
            background: rgba(255, 255, 255, 0.95);
            box-shadow: 0 8px 20px rgba(74, 15, 26, 0.12);
        }

        .step-brand-text {
            color: #ffffff;
            font-size: 0.8rem;
            font-weight: 900;
            line-height: 1.1;
            letter-spacing: 0.02em;
            text-transform: uppercase;
        }

        .step-progress-center {
            display: grid;
            grid-template-columns: 132px minmax(110px, 1fr) 104px;
            align-items: center;
            gap: 12px;
        }

        .step-progress-copy strong,
        .step-progress-percent {
            color: var(--clinic-yellow);
            font-weight: 900;
        }

        .step-progress-copy strong {
            display: block;
            font-size: 0.92rem;
        }

        .step-progress-copy span {
            display: block;
            margin-top: 3px;
            color: #ffffff;
            font-size: 0.82rem;
            font-weight: 700;
        }

        .step-progress-track {
            position: relative;
            height: 9px;
            border-radius: 999px;
            background: rgba(190, 91, 105, 0.58);
            overflow: hidden;
        }

        .step-progress-fill {
            position: absolute;
            inset: 0 auto 0 0;
            width: 20%;
            border-radius: inherit;
            background: linear-gradient(90deg, var(--clinic-yellow) 0%, #f59e0b 100%);
            transition: width 0.24s ease;
        }

        .step-progress-percent {
            font-size: 0.84rem;
            white-space: nowrap;
        }

        .step-form-title-card {
            display: flex;
            align-items: center;
            justify-content: center;
            min-width: 136px;
            padding: 8px 12px;
            border: 1px solid rgba(127, 29, 45, 0.12);
            border-radius: 12px;
            background: rgba(255, 255, 255, 0.95);
            box-shadow: 0 10px 24px rgba(15, 23, 42, 0.12);
        }

        .step-number svg path {
            stroke: currentColor;
            stroke-linecap: round;
            stroke-linejoin: round;
        }

        .step-form-title-card strong {
            display: block;
            color: var(--clinic-maroon);
            font-size: 0.78rem;
            font-weight: 900;
            line-height: 1.15;
            text-align: center;
        }

        .step-list-card {
            display: none;
        }

        @keyframes activeStepGlow {
            0%, 100% {
                box-shadow:
                    0 0 0 5px rgba(250, 204, 21, 0.16),
                    0 0 22px rgba(250, 204, 21, 0.58),
                    0 12px 26px rgba(127, 29, 45, 0.28);
            }
            50% {
                box-shadow:
                    0 0 0 8px rgba(250, 204, 21, 0.2),
                    0 0 34px rgba(250, 204, 21, 0.82),
                    0 16px 32px rgba(127, 29, 45, 0.34);
            }
        }

        :where(.asw-menu-btn),
        :where(#studentQuickActionsFab),
        :where(.student-quick-actions-fab-wrap),
        :where(.student-quick-actions-toggle),
        :where(.student-quick-action-btn),
        :where(#studentAccessibilityLaunch),
        :where(#sienna-accessibility-button),
        :where(.sienna-accessibility-button),
        :where(.sienna-accessibility-trigger),
        :where([data-sienna-accessibility-trigger]) {
            position: fixed !important;
            right: 22px !important;
            bottom: 14px !important;
            z-index: 2147483000 !important;
        }

        :where(.asw-menu-btn),
        :where(.asw-menu-btn *),
        :where(#studentQuickActionsFab),
        :where(#studentQuickActionsFab *),
        :where(.student-quick-actions-fab-wrap),
        :where(.student-quick-actions-fab-wrap *),
        :where(#studentAccessibilityLaunch),
        :where(#studentAccessibilityLaunch *),
        :where(#sienna-accessibility-button),
        :where(#sienna-accessibility-button *),
        :where(.sienna-accessibility-button),
        :where(.sienna-accessibility-button *),
        :where(.sienna-accessibility-trigger),
        :where(.sienna-accessibility-trigger *),
        :where([data-sienna-accessibility-trigger]),
        :where([data-sienna-accessibility-trigger] *) {
            pointer-events: auto !important;
        }

        .step-chip {
            position: relative;
            display: grid;
            place-items: center;
            gap: 0;
            min-width: 0;
            min-height: 36px;
            text-align: center;
        }

        .step-chip:not(:last-child)::after {
            content: "";
            position: absolute;
            top: 39px;
            bottom: -24px;
            left: 20px;
            width: 2px;
            background: rgba(17, 24, 39, 0.14);
        }

        .step-number {
            position: relative;
            z-index: 1;
            display: inline-grid;
            place-items: center;
            width: 36px;
            height: 36px;
            border: 2px solid #f59e0b;
            border-radius: 50%;
            background: #ffffff;
            color: #70131b;
            font-size: 0.86rem;
            font-weight: 900;
            box-shadow: 0 8px 18px rgba(127, 29, 45, 0.08);
        }

        .step-number svg {
            width: 21px;
            height: 21px;
            stroke-width: 3;
        }

        .step-label {
            display: none;
            color: #4b5563;
            font-size: 0.8rem;
            font-weight: 800;
            line-height: 1.15;
        }

        .step-chip.is-active .step-number {
            border-color: transparent;
            background: linear-gradient(135deg, var(--clinic-maroon) 0%, var(--clinic-maroon-dark) 100%);
            color: #ffffff;
            animation: activeStepGlow 1.45s ease-in-out infinite;
        }

        .step-chip.is-active .step-label {
            color: #70131b;
        }

        .step-chip.is-complete .step-number {
            border-color: transparent;
            background: linear-gradient(135deg, var(--clinic-maroon) 0%, var(--clinic-maroon-dark) 100%);
            color: #ffffff;
        }

        .stepper-spacer {
            height: 82px;
        }

        .profile-readonly-grid {
            display: grid;
            grid-template-columns: repeat(2, minmax(0, 1fr));
            gap: 10px;
            margin-bottom: 16px;
        }

        .readonly-item {
            border: 1px solid rgba(127, 29, 45, 0.12);
            background: #fff;
            border-radius: 12px;
            padding: 10px 12px;
        }

        .readonly-item small {
            display: block;
            color: #6b7280;
            font-size: 0.74rem;
            text-transform: uppercase;
            letter-spacing: 0.06em;
            font-weight: 700;
            margin-bottom: 3px;
        }

        .readonly-item strong {
            color: #111827;
            font-size: 0.93rem;
        }

        .identity-overview {
            margin-bottom: 18px;
            border: 1px solid rgba(127, 29, 45, 0.14);
            border-radius: 20px;
            background:
                radial-gradient(circle at top left, rgba(250, 204, 21, 0.18), transparent 32%),
                linear-gradient(180deg, #ffffff 0%, #fffaf2 100%);
            box-shadow: 0 14px 30px rgba(127, 29, 45, 0.08);
            overflow: hidden;
        }

        .identity-name-grid {
            display: grid;
            grid-template-columns: repeat(3, minmax(0, 1fr));
            gap: 10px;
            padding: 16px;
        }

        .identity-field {
            border: 1px solid rgba(127, 29, 45, 0.12);
            background: rgba(255, 255, 255, 0.86);
            border-radius: 14px;
            padding: 12px 14px;
            min-height: 72px;
        }

        .identity-field small,
        .reference-panel small {
            display: block;
            color: #6b7280;
            font-size: 0.72rem;
            text-transform: uppercase;
            letter-spacing: 0.08em;
            font-weight: 800;
            margin-bottom: 5px;
        }

        .identity-field strong {
            color: #111827;
            font-size: 1rem;
            font-weight: 800;
            word-break: break-word;
        }

        .reference-panel {
            position: relative;
            text-align: center;
            padding: 20px 18px 22px;
            border-top: 1px solid rgba(127, 29, 45, 0.12);
            background: linear-gradient(135deg, rgba(127, 29, 45, 0.98), rgba(95, 0, 18, 0.98));
        }

        .reference-panel small {
            color: rgba(255, 255, 255, 0.78);
            margin-bottom: 6px;
        }

        .reference-panel strong {
            display: block;
            color: #facc15;
            font-size: clamp(1.8rem, 5vw, 3rem);
            line-height: 1;
            font-weight: 950;
            letter-spacing: 0.05em;
            word-break: break-word;
        }

        .reference-display {
            min-height: 74px;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 0 58px;
        }

        .reference-edit-btn {
            position: absolute;
            top: 50%;
            right: 18px;
            width: 44px;
            height: 44px;
            transform: translateY(-50%);
            display: inline-flex;
            align-items: center;
            justify-content: center;
            border: 2px solid rgba(250, 204, 21, 0.8);
            border-radius: 50%;
            background: rgba(255, 255, 255, 0.1);
            color: #facc15;
            cursor: pointer;
            overflow: hidden;
            transition: color 0.2s ease, transform 0.2s ease;
        }

        .reference-edit-btn::before {
            content: "";
            position: absolute;
            inset: 0;
            background: #facc15;
            transform: translateX(-105%);
            transition: transform 0.28s ease;
        }

        .reference-edit-btn:hover {
            color: #70131b;
            transform: translateY(-50%) scale(1.04);
        }

        .reference-edit-btn:hover::before {
            transform: translateX(0);
        }

        .reference-edit-btn svg {
            position: relative;
            z-index: 1;
            width: 22px;
            height: 22px;
            fill: none;
            stroke: currentColor;
            stroke-width: 2;
            stroke-linecap: round;
            stroke-linejoin: round;
        }

        .reference-verify-wrap {
            display: none;
            min-height: 74px;
            align-items: center;
            justify-content: center;
            padding: 0 58px;
        }

        .reference-panel.is-editing .reference-display {
            display: none;
        }

        .reference-panel.is-editing .reference-verify-wrap {
            display: flex;
        }

        .reference-verify-row {
            width: min(620px, 100%);
        }

        .reference-verify-input {
            width: 100%;
            min-height: 62px;
            border: 2px solid #facc15;
            border-radius: 12px;
            background: transparent;
            color: #facc15;
            padding: 0 18px;
            font: inherit;
            font-size: clamp(1.8rem, 5vw, 3rem);
            line-height: 1;
            font-weight: 950;
            letter-spacing: 0.05em;
            text-align: center;
            text-transform: uppercase;
            outline: none;
            transition: border-color 0.2s ease, box-shadow 0.2s ease;
        }

        .reference-verify-input::placeholder {
            color: rgba(250, 204, 21, 0.55);
            opacity: 1;
        }

        .reference-verify-input:focus {
            border-color: #facc15;
            box-shadow: 0 0 0 4px rgba(250, 204, 21, 0.2);
        }

        .reference-verify-status {
            margin: 10px auto 0;
            max-width: 760px;
            color: #fde68a;
            font-size: 0.82rem;
            font-weight: 800;
            line-height: 1.45;
        }

        .reference-verify-status.is-success {
            color: #bbf7d0;
        }

        .reference-verify-status.is-error {
            color: #fecaca;
        }

        .reference-icon-check {
            display: none;
        }

        .reference-panel.is-editing .reference-icon-edit {
            display: none;
        }

        .reference-panel.is-editing .reference-icon-check {
            display: block;
        }

        .reference-field-error {
            margin: 8px 0 0;
            font-size: 0.78rem;
            font-weight: 800;
        }

        .reference-panel.is-missing .reference-display strong {
            font-size: clamp(1.9rem, 5vw, 3.5rem);
        }

        .reference-mode-selector {
            display: grid;
            grid-template-columns: repeat(2, minmax(0, 1fr));
            gap: 12px;
            margin-bottom: 14px;
        }

        .reference-mode-card {
            position: relative;
            display: flex;
            gap: 10px;
            align-items: flex-start;
            min-height: 88px;
            padding: 14px;
            border-radius: 16px;
            border: 1px solid rgba(250, 204, 21, 0.34);
            background: rgba(255, 255, 255, 0.08);
            color: #111827;
            cursor: pointer;
            transition: border-color 0.2s ease, background 0.2s ease, transform 0.2s ease;
        }

        .reference-mode-card:hover {
            border-color: rgba(250, 204, 21, 0.72);
            background: rgba(250, 204, 21, 0.12);
            transform: translateY(-1px);
        }

        .reference-mode-radio {
            position: absolute;
            opacity: 0;
            pointer-events: none;
        }

        .reference-mode-dot {
            width: 18px;
            height: 18px;
            flex: 0 0 auto;
            border-radius: 999px;
            border: 2px solid rgba(250, 204, 21, 0.7);
            margin-top: 2px;
            box-shadow: inset 0 0 0 4px transparent;
        }

        .reference-mode-copy {
            display: grid;
            gap: 4px;
            min-width: 0;
        }

        .reference-mode-copy strong {
            color: #111827;
            font-size: 0.94rem;
            line-height: 1.2;
        }

        .reference-mode-copy span {
            color: #4b5563;
            font-size: 0.78rem;
            font-weight: 750;
            line-height: 1.35;
        }

        .reference-mode-radio:checked + .reference-mode-card {
            border-color: #facc15;
            background: rgba(250, 204, 21, 0.18);
            box-shadow: 0 0 0 3px rgba(250, 204, 21, 0.12);
        }

        .reference-mode-radio:checked + .reference-mode-card .reference-mode-dot {
            background: #facc15;
            box-shadow: inset 0 0 0 4px #70131b;
        }

        .reference-mode-radio:disabled + .reference-mode-card {
            border-color: rgba(148, 163, 184, 0.42);
            background: rgba(226, 232, 240, 0.34);
            cursor: not-allowed;
            opacity: 0.72;
            transform: none;
        }

        .reference-mode-radio:disabled + .reference-mode-card:hover {
            border-color: rgba(148, 163, 184, 0.42);
            background: rgba(226, 232, 240, 0.34);
            transform: none;
        }

        .upload-instruction-card {
            margin-bottom: 18px;
            padding: 15px 16px;
            border-radius: 16px;
            border: 1px solid rgba(250, 204, 21, 0.42);
            background: linear-gradient(135deg, #fff8d6 0%, #fffef4 100%);
            color: #4b2e05;
        }

        .upload-instruction-card strong {
            display: block;
            color: #70131b;
            font-size: 0.95rem;
            margin-bottom: 5px;
        }

        .upload-instruction-card p {
            margin: 0;
            font-size: 0.86rem;
            line-height: 1.55;
            font-weight: 600;
        }

        .upload-instruction-card ol {
            margin: 0;
            padding-left: 20px;
            color: #4b2e05;
            font-size: 0.86rem;
            line-height: 1.6;
            font-weight: 650;
        }

        .upload-instruction-card li + li {
            margin-top: 4px;
        }

        .form-label {
            font-weight: 700;
            color: #111827;
        }

        .form-control,
        .form-select {
            border-radius: 12px;
            border: 1px solid var(--border);
            background: var(--field);
            min-height: 46px;
        }

        .form-control:focus,
        .form-select:focus {
            border-color: rgba(127, 29, 45, 0.5);
            box-shadow: 0 0 0 0.18rem rgba(127, 29, 45, 0.12);
        }

        .upload-card {
            border: 1px dashed rgba(127, 29, 45, 0.35);
            background: linear-gradient(180deg, #fffef6 0%, #fff8dc 100%);
            border-radius: 14px;
            padding: 14px;
            height: 100%;
        }

        .upload-card strong {
            display: block;
            color: #70131b;
            margin-bottom: 6px;
        }

        .upload-card small {
            color: #5b6470;
            display: block;
            margin-top: 6px;
        }

        .requirement-grid {
            display: grid;
            grid-template-columns: repeat(2, minmax(0, 1fr));
            gap: 14px;
        }

        .requirement-card {
            border: 1px solid rgba(127, 29, 45, 0.18);
            background: #ffffff;
            border-radius: 16px;
            padding: 14px;
            min-height: 100%;
            box-shadow: 0 10px 24px rgba(15, 23, 42, 0.06);
            transition: border-color 0.2s ease, box-shadow 0.2s ease, transform 0.2s ease;
        }

        .requirement-card.file-selected {
            border-color: rgba(127, 29, 45, 0.48);
            box-shadow: 0 14px 30px rgba(127, 29, 45, 0.12);
            transform: translateY(-1px);
        }

        .requirement-card-header {
            display: flex;
            align-items: flex-start;
            justify-content: space-between;
            gap: 12px;
            margin-bottom: 10px;
        }

        .requirement-card-header strong {
            display: block;
            color: #70131b;
            font-size: 0.98rem;
            line-height: 1.35;
        }

        .requirement-badge {
            flex: 0 0 auto;
            border-radius: 999px;
            background: #fef3c7;
            color: #7c2d12;
            border: 1px solid rgba(250, 204, 21, 0.45);
            padding: 4px 8px;
            font-size: 0.68rem;
            font-weight: 800;
            text-transform: uppercase;
            letter-spacing: 0.05em;
        }

        .requirement-guideline {
            margin: 8px 0 12px;
            border-left: 4px solid var(--clinic-yellow);
            border-radius: 12px;
            background: #fff8db;
            color: #51340b;
            padding: 10px 12px;
            font-size: 0.82rem;
            line-height: 1.45;
            font-weight: 650;
        }

        .upload-example-grid {
            display: grid;
            grid-template-columns: repeat(2, minmax(0, 1fr));
            gap: 10px;
            margin: 12px 0;
        }

        .requirement-card.has-upload-preview .upload-example-grid,
        .requirement-card.has-upload-preview .requirement-guideline {
            display: none;
        }

        .upload-example {
            overflow: hidden;
            border: 1px solid #d8dee7;
            border-radius: 8px;
            background: #ffffff;
        }

        .upload-example.is-wrong {
            border-color: #efb3b3;
        }

        .upload-example.is-correct {
            border-color: #9bcdae;
        }

        .upload-example-status {
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 6px;
            min-height: 34px;
            padding: 7px 8px;
            color: #ffffff;
            font-size: 0.72rem;
            font-weight: 800;
            text-align: center;
        }

        .upload-example.is-wrong .upload-example-status {
            background: #9f2531;
        }

        .upload-example.is-correct .upload-example-status {
            background: #267447;
        }

        .upload-example-status span:first-child {
            font-size: 1rem;
            line-height: 1;
        }

        .upload-example img {
            display: block;
            width: 100%;
            height: 148px;
            object-fit: cover;
            background: #eef1f4;
        }


        .upload-example-caption {
            min-height: 48px;
            margin: 0;
            padding: 8px 9px;
            color: #4b5563;
            font-size: 0.72rem;
            font-weight: 700;
            line-height: 1.35;
            text-align: center;
        }

        .requirement-extra {
            display: none;
            grid-template-columns: repeat(2, minmax(0, 1fr));
            gap: 10px;
            margin-top: 12px;
            padding-top: 12px;
            border-top: 1px solid rgba(127, 29, 45, 0.12);
        }

        .requirement-card.file-selected .requirement-extra,
        .requirement-card.has-old-data .requirement-extra,
        .requirement-extra.always-visible {
            display: grid;
        }

        .requirement-extra .form-field {
            padding: 9px 10px;
        }

        .requirement-extra .form-field.span-2 {
            grid-column: span 2;
        }

        .certify-row {
            display: flex;
            gap: 10px;
            align-items: flex-start;
            margin-top: 12px;
            padding: 12px;
            border-radius: 12px;
            border: 1px solid rgba(127, 29, 45, 0.18);
            background: #fffaf0;
        }

        .certify-row input {
            width: 18px;
            height: 18px;
            margin-top: 2px;
            accent-color: var(--clinic-maroon);
        }

        .certify-row label {
            color: #374151;
            font-size: 0.84rem;
            line-height: 1.45;
            font-weight: 700;
        }

        .final-certification {
            width: min(680px, 100%);
            margin: 22px auto 0;
            align-items: center;
            justify-content: center;
            text-align: center;
            padding: 16px 18px;
        }

        .final-certification input {
            flex: 0 0 auto;
            margin-top: 0;
        }

        .final-certification label {
            max-width: 590px;
        }

        .esign-grid {
            display: grid;
            grid-template-columns: minmax(0, 1.2fr) minmax(260px, 0.8fr);
            gap: 18px;
            align-items: stretch;
            max-width: 680px;
            margin-left: auto;
            margin-right: auto;
        }

        .esign-card {
            border: 1px solid rgba(127, 29, 45, 0.14);
            border-radius: 16px;
            background: #ffffff;
            padding: 16px;
            box-shadow: 0 8px 22px rgba(15, 23, 42, 0.06);
        }

        .esign-card h3 {
            margin: 0 0 6px;
            color: var(--clinic-maroon);
            font-size: 1rem;
            font-weight: 900;
        }

        .esign-card p {
            margin: 0 0 12px;
            color: #64748b;
            font-size: 0.84rem;
            font-weight: 700;
            line-height: 1.4;
        }

        .esign-upload-instructions {
            margin: 12px 0 0;
            padding-left: 20px;
            color: #64748b;
            font-size: 0.84rem;
            font-weight: 700;
            line-height: 1.5;
        }

        .esign-upload-instructions li + li {
            margin-top: 4px;
        }

        .signature-pad-wrap {
            border: 1.5px dashed rgba(127, 29, 45, 0.35);
            border-radius: 14px;
            background:
                linear-gradient(rgba(255, 255, 255, 0.82), rgba(255, 255, 255, 0.82)),
                repeating-linear-gradient(0deg, transparent 0, transparent 35px, rgba(127, 29, 45, 0.08) 36px);
            overflow: hidden;
        }

        #digitalSignaturePad,
        #staffSignaturePad,
        #guardianSignaturePad {
            display: block;
            width: 100%;
            height: 220px;
            background: #fff;
            touch-action: none;
            cursor: crosshair;
        }

        .esign-method-grid {
            display: grid;
            grid-template-columns: repeat(2, minmax(0, 1fr));
            gap: 12px;
            width: min(920px, 100%);
            margin: 0 auto 16px;
        }

        .esign-method-card {
            position: relative;
            display: flex;
            align-items: center;
            gap: 12px;
            min-height: 74px;
            padding: 14px 16px;
            border: 1px solid rgba(127, 29, 45, 0.16);
            border-radius: 12px;
            background: rgba(255, 255, 255, 0.92);
            color: #334155;
            cursor: pointer;
            box-shadow: 0 8px 18px rgba(15, 23, 42, 0.05);
            transition: all 0.18s ease;
        }

        .esign-method-radio {
            position: absolute;
            opacity: 0;
            pointer-events: none;
        }

        .esign-method-dot {
            width: 14px;
            height: 14px;
            border-radius: 50%;
            border: 2px solid #cbd5e1;
            box-shadow: inset 0 0 0 3px #fff;
            flex: 0 0 auto;
        }

        .esign-method-icon {
            display: grid;
            place-items: center;
            width: 42px;
            height: 42px;
            border-radius: 12px;
            background: #fff7f8;
            color: var(--clinic-maroon);
        }

        .esign-method-icon svg {
            width: 26px;
            height: 26px;
            fill: none;
            stroke: currentColor;
            stroke-width: 1.8;
            stroke-linecap: round;
            stroke-linejoin: round;
        }

        .esign-method-copy strong,
        .esign-method-copy span {
            display: block;
        }

        .esign-method-copy strong {
            color: var(--clinic-maroon);
            font-size: 0.84rem;
            font-weight: 900;
        }

        .esign-method-copy span {
            margin-top: 3px;
            color: #64748b;
            font-size: 0.72rem;
            font-weight: 800;
            line-height: 1.25;
        }

        .esign-method-badge {
            display: inline-flex;
            margin-top: 5px;
            border-radius: 999px;
            background: #ffe6e8;
            color: #b91c1c;
            padding: 3px 7px;
            font-size: 0.64rem;
            font-weight: 900;
        }

        .esign-method-radio:checked + .esign-method-card {
            border-color: rgba(127, 29, 45, 0.72);
            background: linear-gradient(180deg, #fffafb 0%, #fff5f6 100%);
            box-shadow: 0 10px 24px rgba(127, 29, 45, 0.12);
        }

        .esign-method-radio:checked + .esign-method-card .esign-method-dot {
            border-color: var(--clinic-maroon);
            background: var(--clinic-maroon);
        }

        .esign-mode-panel.is-hidden {
            display: none;
        }

        .esign-mode-panel {
            grid-template-columns: minmax(0, 1fr);
        }

        .esign-actions {
            display: flex;
            flex-wrap: wrap;
            gap: 10px;
            margin-top: 12px;
        }

        .esign-secondary-btn {
            display: inline-flex;
            align-items: center;
            gap: 7px;
            border: 1px solid rgba(127, 29, 45, 0.2);
            border-radius: 999px;
            background: #fff;
            color: var(--clinic-maroon);
            padding: 9px 14px;
            font-weight: 900;
            cursor: pointer;
        }

        .esign-secondary-btn svg {
            width: 16px;
            height: 16px;
            fill: currentColor;
        }

        .esign-upload-input {
            width: 100%;
            border: 1px solid rgba(127, 29, 45, 0.18);
            border-radius: 12px;
            background: #fff7f8;
            color: #111827;
            padding: 10px;
            font-weight: 800;
        }

        .esign-status {
            margin-top: 10px;
            color: #64748b;
            font-size: 0.8rem;
            font-weight: 800;
        }

        .upload-preview-card {
            display: none;
            align-items: center;
            gap: 12px;
            margin-top: 12px;
            padding: 10px;
            border-radius: 14px;
            border: 1px solid rgba(127, 29, 45, 0.16);
            background: #ffffff;
            box-shadow: 0 8px 18px rgba(15, 23, 42, 0.06);
        }

        .upload-preview-card.is-visible {
            display: flex;
        }

        .correction-mode-note {
            margin-bottom: 16px;
            padding: 14px 16px;
            border: 1px solid #facc15;
            border-radius: 14px;
            background: #fffbeb;
            color: #78350f;
            font-size: 0.86rem;
            font-weight: 700;
            line-height: 1.55;
        }

        .existing-upload-preview {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 12px;
            margin: 12px 0;
            padding: 12px;
            border: 1px solid #dbeafe;
            border-radius: 14px;
            background: #eff6ff;
            color: #1e3a8a;
            font-size: 0.82rem;
            font-weight: 800;
        }

        .existing-upload-preview a {
            color: #7f1d2d;
            text-decoration: none;
            font-weight: 900;
            white-space: nowrap;
        }

        .file-locked-note {
            margin: 8px 0 0;
            padding: 10px 12px;
            border: 1px solid #e5e7eb;
            border-radius: 12px;
            background: #f8fafc;
            color: #64748b;
            font-size: 0.78rem;
            font-weight: 700;
        }

        .requirement-card.has-upload-preview > input[type="file"],
        .upload-card.has-upload-preview > input[type="file"] {
            display: none;
        }

        .upload-preview-thumb {
            width: 52px;
            height: 52px;
            border-radius: 12px;
            border: 1px solid rgba(127, 29, 45, 0.16);
            background:
                linear-gradient(135deg, rgba(127, 29, 45, 0.10), rgba(250, 204, 21, 0.18)),
                #ffffff;
            display: flex;
            align-items: center;
            justify-content: center;
            overflow: hidden;
            color: var(--clinic-maroon);
            flex: 0 0 auto;
        }

        .upload-preview-thumb img {
            width: 100%;
            height: 100%;
            object-fit: cover;
            display: block;
        }

        .upload-preview-thumb svg {
            width: 24px;
            height: 24px;
            stroke: currentColor;
            stroke-width: 2;
            fill: none;
        }

        .upload-preview-body {
            min-width: 0;
            flex: 1 1 auto;
        }

        .upload-preview-name {
            display: block;
            color: #111827;
            font-size: 0.84rem;
            font-weight: 800;
            line-height: 1.3;
            overflow: hidden;
            text-overflow: ellipsis;
            white-space: nowrap;
        }

        .upload-preview-meta {
            display: block;
            margin-top: 2px;
            color: #6b7280;
            font-size: 0.72rem;
            font-weight: 700;
        }

        .upload-preview-actions {
            display: flex;
            flex-wrap: wrap;
            gap: 7px;
            margin-top: 8px;
        }

        .upload-preview-btn {
            border: 1px solid rgba(127, 29, 45, 0.18);
            border-radius: 999px;
            background: #ffffff;
            color: var(--clinic-maroon);
            padding: 7px 11px;
            font-size: 0.72rem;
            line-height: 1;
            font-weight: 850;
            text-decoration: none;
            cursor: pointer;
            transition: all 0.18s ease;
        }

        .upload-preview-btn:hover {
            background: linear-gradient(135deg, var(--clinic-maroon) 0%, var(--clinic-maroon-dark) 100%);
            border-color: transparent;
            color: var(--clinic-yellow);
            text-decoration: none;
            transform: translateY(-1px);
        }

        .upload-preview-error {
            display: flex;
            flex-direction: column;
            gap: 12px;
            padding: 12px;
            background-color: #fde2e2;
            border: 1px solid #f5a5a5;
            border-radius: 6px;
        }

        .upload-preview-error-header {
            display: flex;
            align-items: flex-start;
            gap: 10px;
        }

        .upload-preview-error-icon {
            font-size: 1.2rem;
            flex-shrink: 0;
            margin-top: 2px;
        }

        .upload-preview-error-text {
            color: #991b1b;
            font-size: 0.84rem;
            line-height: 1.4;
            flex: 1;
        }

        .upload-preview-error-actions {
            display: flex;
            gap: 8px;
        }

        .health-error-modal {
            position: fixed;
            inset: 0;
            z-index: 2147482500;
            display: none;
            align-items: center;
            justify-content: center;
            padding: 18px;
            background: rgba(17, 24, 39, 0.38);
        }

        .health-error-modal.is-visible {
            display: flex;
        }

        .health-error-card {
            width: min(320px, 100%);
            padding: 24px 20px 18px;
            border-radius: 12px;
            border: 1px solid rgba(250, 204, 21, 0.24);
            background: #ffffff;
            box-shadow: 0 18px 45px rgba(15, 23, 42, 0.25);
            text-align: center;
        }

        .health-error-card h3 {
            margin: 0;
            color: var(--clinic-maroon);
            font-size: 1.18rem;
            font-weight: 900;
        }

        .health-error-card p {
            margin: 10px 0 18px;
            color: #5b677a;
            font-size: 0.92rem;
            line-height: 1.35;
        }

        .health-error-card button {
            width: 100%;
            min-height: 42px;
            border: 0;
            border-radius: 999px;
            background: linear-gradient(135deg, var(--clinic-maroon) 0%, var(--clinic-maroon-dark) 100%);
            color: #ffffff;
            font-weight: 900;
            cursor: pointer;
            box-shadow: 0 10px 20px rgba(127, 29, 45, 0.22);
            transition: transform 0.18s ease, box-shadow 0.18s ease;
        }

        .health-error-card button:hover,
        .health-error-card button:focus {
            transform: translateY(-1px);
            box-shadow: 0 12px 24px rgba(127, 29, 45, 0.3);
        }

        .student-toast-stack {
            position: fixed;
            top: 18px;
            right: 24px;
            z-index: 2147482600;
            width: min(330px, calc(100vw - 32px));
            pointer-events: none;
        }

        .student-toast {
            position: relative;
            display: grid;
            grid-template-columns: 42px minmax(0, 1fr) 28px;
            align-items: center;
            gap: 12px;
            min-height: 78px;
            padding: 14px 12px 14px 22px;
            border-radius: 8px;
            background: rgba(255, 255, 255, 0.98);
            color: #334155;
            box-shadow: 0 18px 36px rgba(15, 23, 42, 0.18);
            animation: studentToastIn 0.32s cubic-bezier(.2, .8, .2, 1) forwards;
            pointer-events: auto;
            overflow: hidden;
        }

        .student-toast::before {
            content: "";
            position: absolute;
            inset: 0 auto 0 0;
            width: 8px;
            background:
                linear-gradient(135deg, transparent 0 45%, var(--toast-accent) 46% 100%) 0 0 / 8px 12px,
                linear-gradient(45deg, transparent 0 45%, var(--toast-accent-soft) 46% 100%) 0 6px / 8px 12px;
        }

        .student-toast.is-success {
            --toast-accent: #86efac;
            --toast-accent-soft: #bbf7d0;
            --toast-title: #16a34a;
        }

        .student-toast.is-error {
            --toast-accent: #fca5a5;
            --toast-accent-soft: #fecaca;
            --toast-title: #dc2626;
        }

        .student-toast-icon {
            width: 38px;
            height: 38px;
            border-radius: 999px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            background: var(--toast-accent-soft);
            color: var(--toast-title);
        }

        .student-toast-title,
        .student-toast-message {
            display: block;
            line-height: 1.25;
        }

        .student-toast-title {
            margin-bottom: 4px;
            color: var(--toast-title);
            font-size: 16px;
            font-weight: 900;
        }

        .student-toast-message {
            color: #475569;
            font-size: 14px;
            font-weight: 500;
        }

        .student-toast-close {
            width: 28px;
            height: 28px;
            border: 0;
            border-radius: 999px;
            background: transparent;
            color: #475569;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            cursor: pointer;
        }

        .student-toast.is-hiding {
            animation: studentToastOut 0.22s ease forwards;
        }

        @keyframes studentToastIn {
            from { opacity: 0; transform: translateX(18px) scale(0.98); }
            to { opacity: 1; transform: translateX(0) scale(1); }
        }

        @keyframes studentToastOut {
            to { opacity: 0; transform: translateX(18px) scale(0.98); }
        }

        .clinic-select-wrap {
            position: relative;
        }

        .clinic-select-native {
            position: absolute;
            opacity: 0;
            pointer-events: none;
            width: 0;
            height: 0;
            padding: 0;
            border: 0;
            margin: 0;
        }

        .clinic-select-display {
            width: 100%;
            min-height: 46px;
            padding: 11px 48px 11px 14px;
            border: 1px solid rgba(148, 163, 184, 0.20);
            border-radius: 18px;
            font-size: 0.88rem;
            color: #111111;
            background: linear-gradient(180deg, #ffffff 0%, #fff8f6 100%);
            box-shadow:
                0 12px 22px rgba(15, 23, 42, 0.08),
                inset 0 1px 0 rgba(255,255,255,0.86);
            cursor: pointer;
            font-weight: 650;
            text-align: left;
            transition: all 0.2s ease;
        }

        .clinic-select-display:hover {
            border-color: rgba(139, 0, 0, 0.28);
            box-shadow:
                0 10px 18px rgba(139, 0, 0, 0.05),
                inset 0 1px 0 rgba(255,255,255,0.86);
        }

        .clinic-select-display.is-open,
        .clinic-select-display:focus {
            outline: none;
            border-color: var(--clinic-maroon);
            box-shadow:
                0 0 0 4px rgba(139, 0, 0, 0.06),
                0 10px 18px rgba(139, 0, 0, 0.08);
        }

        .clinic-select-wrap::after {
            content: "";
            position: absolute;
            top: 23px;
            right: 17px;
            width: 10px;
            height: 10px;
            border-right: 2px solid var(--clinic-maroon);
            border-bottom: 2px solid var(--clinic-maroon);
            transform: translateY(-65%) rotate(45deg);
            pointer-events: none;
            transition: transform 0.18s ease;
            z-index: 2;
        }

        .clinic-select-wrap::before {
            content: "";
            position: absolute;
            top: 23px;
            right: 40px;
            transform: translateY(-50%);
            width: 1px;
            height: 22px;
            background: rgba(148, 163, 184, 0.24);
            pointer-events: none;
            z-index: 2;
        }

        .clinic-select-wrap.is-open::after {
            transform: translateY(-25%) rotate(225deg);
        }

        .clinic-select-menu {
            position: absolute;
            top: calc(100% + 10px);
            left: 0;
            right: 0;
            display: none;
            gap: 10px;
            padding: 12px;
            border-radius: 18px;
            border: 1px solid rgba(139, 0, 0, 0.12);
            background: rgba(255, 255, 255, 0.98);
            box-shadow: 0 18px 34px rgba(15, 23, 42, 0.14);
            z-index: 90;
            backdrop-filter: blur(8px);
            -webkit-backdrop-filter: blur(8px);
            max-height: 260px;
            overflow-y: auto;
            scrollbar-width: thin;
            scrollbar-color: rgba(127, 29, 45, 0.45) transparent;
        }

        .clinic-select-wrap.is-open .clinic-select-menu {
            display: grid;
        }

        .clinic-select-option {
            width: 100%;
            border: 1px solid rgba(148, 163, 184, 0.22);
            background: linear-gradient(180deg, #ffffff 0%, #f8fafc 100%);
            color: #1e293b;
            border-radius: 999px;
            padding: 11px 14px;
            font-size: 0.82rem;
            font-weight: 800;
            letter-spacing: 0.01em;
            text-align: left;
            cursor: pointer;
            transition: all 0.18s ease;
            box-shadow:
                0 12px 22px rgba(15, 23, 42, 0.08),
                0 1px 0 rgba(255,255,255,0.82) inset;
        }

        .clinic-select-option:hover {
            border-color: transparent;
            background: linear-gradient(135deg, var(--clinic-maroon) 0%, var(--clinic-maroon-dark) 100%);
            color: var(--clinic-yellow);
            transform: translateY(-1px);
            box-shadow: 0 14px 26px rgba(127, 29, 45, 0.22);
        }

        .clinic-select-option.is-selected {
            border-color: transparent;
            background: linear-gradient(135deg, var(--clinic-maroon) 0%, var(--clinic-maroon-dark) 100%);
            color: #ffffff;
            box-shadow: 0 14px 26px rgba(127, 29, 45, 0.24);
        }

        .course-select-wrap .clinic-select-display {
            min-height: 52px;
            white-space: normal;
            line-height: 1.25;
        }

        .course-select-wrap .clinic-select-option {
            border-radius: 14px;
            white-space: normal;
            line-height: 1.25;
        }

        .btn-row {
            margin-top: 22px;
            display: flex;
            justify-content: flex-end;
            gap: 10px;
            flex-wrap: wrap;
        }

        .btn-health {
            border: none;
            border-radius: 12px;
            padding: 12px 18px;
            font-weight: 700;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
        }

        .btn-health-back,
        .btn-health-next,
        .btn-health-submit {
            isolation: isolate;
        }

        .btn-health-back > span,
        .btn-health-back > svg,
        .btn-health-next > span,
        .btn-health-next > svg,
        .btn-health-submit > span,
        .btn-health-submit > svg {
            position: relative;
            z-index: 2;
        }

        .btn-health-back::after,
        .btn-health-next::after,
        .btn-health-submit::after {
            content: "";
            position: absolute;
            top: -45%;
            bottom: -45%;
            left: -55%;
            width: 34%;
            background: linear-gradient(
                105deg,
                transparent 0%,
                rgba(255, 255, 255, 0.12) 30%,
                rgba(255, 255, 255, 0.72) 50%,
                rgba(255, 255, 255, 0.12) 70%,
                transparent 100%
            );
            transform: translateX(0) skewX(-18deg);
            opacity: 0;
            pointer-events: none;
            z-index: 3;
        }

        .btn-health-next:hover::after,
        .btn-health-next:focus::after,
        .btn-health-submit:hover::after,
        .btn-health-submit:focus::after {
            animation: health-button-reflection 0.65s ease-out;
        }

        .btn-health-back:hover::after,
        .btn-health-back:focus::after {
            animation: health-button-reflection 0.65s ease-out reverse;
        }

        @keyframes health-button-reflection {
            0% {
                left: -55%;
                opacity: 0;
            }
            18% {
                opacity: 0.85;
            }
            82% {
                opacity: 0.85;
            }
            100% {
                left: 125%;
                opacity: 0;
            }
        }

        .btn-health svg {
            width: 18px;
            height: 18px;
            flex: 0 0 auto;
            stroke: currentColor;
            stroke-width: 2.4;
            fill: none;
        }

        .btn-health-back {
            position: relative;
            overflow: hidden;
            background: #f8fafc;
            color: var(--clinic-maroon);
            border: 1px solid rgba(127, 29, 45, 0.42);
            box-shadow: 0 7px 16px rgba(127, 29, 45, 0.08);
            transition: background-color 0.22s ease, color 0.22s ease, border-color 0.22s ease, transform 0.22s ease, box-shadow 0.22s ease;
            z-index: 0;
        }

        .btn-health-back::before {
            content: "";
            position: absolute;
            inset: 0;
            background: var(--clinic-maroon);
            transform: translateX(105%);
            transition: transform 0.34s ease;
            z-index: 1;
        }

        .btn-health-back:hover,
        .btn-health-back:focus {
            color: #ffffff;
            border-color: var(--clinic-maroon);
            transform: translateY(-1px);
            box-shadow: 0 10px 20px rgba(127, 29, 45, 0.2);
        }

        .btn-health-back:hover::before,
        .btn-health-back:focus::before {
            transform: translateX(0);
        }

        .btn-health-back:hover svg,
        .btn-health-back:focus svg {
            transform: translateX(-2px);
        }

        .btn-health-back svg {
            transition: transform 0.22s ease;
        }

        .btn-testing-skip {
            background: #e5e7eb;
            color: #111827;
        }

        .btn-health-submit,
        .btn-health-next {
            position: relative;
            overflow: hidden;
            background: linear-gradient(135deg, var(--clinic-maroon) 0%, var(--clinic-maroon-dark) 100%);
            color: #fff;
            box-shadow: 0 10px 22px rgba(127, 29, 45, 0.28);
            transition: color 0.22s ease, border-color 0.22s ease, transform 0.22s ease, box-shadow 0.22s ease;
            z-index: 0;
        }

        .btn-health-submit::before,
        .btn-health-next::before {
            content: "";
            position: absolute;
            inset: 0;
            background: var(--clinic-yellow);
            transform: translateX(-105%);
            transition: transform 0.34s ease;
            z-index: 1;
        }

        .btn-health-submit:hover,
        .btn-health-submit:focus,
        .btn-health-next:hover,
        .btn-health-next:focus {
            color: var(--clinic-maroon);
            transform: translateY(-1px);
            box-shadow: 0 12px 24px rgba(250, 204, 21, 0.24);
        }

        .btn-health-submit:hover::before,
        .btn-health-submit:focus::before,
        .btn-health-next:hover::before,
        .btn-health-next:focus::before {
            transform: translateX(0);
        }

        .btn-health-next span {
            order: 1;
        }

        .btn-health-next svg {
            order: 2;
            transition: transform 0.22s ease;
        }

        .btn-health-next:hover svg,
        .btn-health-next:focus svg {
            transform: translateX(2px);
        }

        .required {
            color: #b91c1c;
        }

        .pwd-toggle {
            display: flex;
            gap: 10px;
            margin-top: 4px;
        }

        .pwd-radio {
            position: absolute;
            opacity: 0;
            pointer-events: none;
        }

        .pwd-option {
            min-width: 92px;
            text-align: center;
            padding: 10px 14px;
            border-radius: 12px;
            border: 1px solid #cbd5e1;
            background: #f8fafc;
            color: #334155;
            font-weight: 700;
            cursor: pointer;
            transition: all 0.18s ease;
        }

        .pwd-radio:checked + .pwd-option {
            background: linear-gradient(135deg, var(--clinic-maroon) 0%, var(--clinic-maroon-dark) 100%);
            border-color: transparent;
            color: #fff;
            box-shadow: 0 8px 16px rgba(127, 29, 45, 0.2);
        }

        #pwdUploadWrap {
            transition: opacity 0.2s ease, transform 0.2s ease;
        }

        #disabilityTypeWrap.is-hidden,
        #pwdUploadWrap.is-hidden,
        #medCertFindingsDetailsWrap.is-hidden,
        #xrayFindingsDetailsWrap.is-hidden {
            display: none;
        }

        .step-panel.is-hidden {
            display: none;
        }

        .student-consent-modal[hidden] { display: none !important; }
        .student-consent-modal { position: fixed; inset: 0; z-index: 1000; display: grid; place-items: center; padding: 18px; background: rgba(15, 23, 42, .68); backdrop-filter: blur(7px); }
        .student-consent-card { position: relative; width: min(680px, 100%); max-height: calc(100vh - 36px); overflow-y: auto; padding: 28px 30px 24px; border: 1px solid rgba(127, 29, 45, .2); border-radius: 14px; background: #fffdf8; color: #1f2937; box-shadow: 0 25px 70px rgba(15, 23, 42, .32); }
        .student-consent-close { position: absolute; top: 16px; right: 18px; width: 34px; height: 34px; border: 1px solid #d1d5db; border-radius: 50%; background: #fff; color: #7f1d2d; font-size: 22px; line-height: 1; cursor: pointer; }
        .student-consent-kicker { margin: 0 42px 8px 0; color: #7f1d2d; font-size: 11px; font-weight: 900; letter-spacing: .12em; text-transform: uppercase; }
        .student-consent-card h2 { margin: 0 42px 18px 0; color: #64111d; font-size: 21px; line-height: 1.2; }
        .student-consent-copy p { margin: 0 0 12px; font-size: 13px; line-height: 1.45; text-align: justify; }
        .student-consent-guardian-note { margin: 14px 0; padding: 10px 12px; border-left: 3px solid #facc15; background: #fff7d6; color: #64111d; font-size: 12px; font-weight: 700; line-height: 1.4; }
        .student-consent-agreement { display: flex; align-items: flex-start; gap: 9px; margin-top: 18px; color: #64111d; font-size: 13px; font-weight: 800; line-height: 1.35; cursor: pointer; }
        .student-consent-agreement input { width: 17px; height: 17px; margin-top: 1px; accent-color: #7f1d2d; flex: 0 0 auto; }
        .student-consent-continue { display: block; width: 100%; margin-top: 20px; padding: 11px 18px; border: 1px solid #7f1d2d; border-radius: 8px; background: #7f1d2d; color: #fff; font-weight: 800; cursor: pointer; }
        .student-consent-continue:hover, .student-consent-continue:focus-visible { border-color: #facc15; background: #facc15; color: #000; outline: none; }
        @media (max-width: 560px) { .student-consent-card { padding: 24px 18px 20px; } .student-consent-card h2 { font-size: 18px; } }

        .step-one-grid {
            display: grid;
            grid-template-columns: repeat(2, minmax(0, 1fr));
            gap: 12px;
        }

        .choice-grid {
            display: grid;
            grid-template-columns: repeat(3, minmax(0, 1fr));
            gap: 9px;
        }

        .personal-identity-grid {
            display: grid;
            grid-template-columns: repeat(3, minmax(0, 1fr));
            gap: 12px;
            margin-bottom: 12px;
        }

        .personal-email-field {
            margin-bottom: 18px;
        }

        .identity-readonly {
            background: #f4f1f1;
            color: #4b5563;
            cursor: default;
        }

        .choice-card {
            display: flex;
            align-items: center;
            gap: 9px;
            min-height: 44px;
            padding: 10px 12px;
            border: 1px solid rgba(127, 29, 45, 0.14);
            border-radius: 12px;
            background: #fffaf2;
            color: #334155;
            font-size: 0.84rem;
            font-weight: 700;
            cursor: pointer;
        }

        .choice-card span,
        .checkbox-card span {
            color: #70131b;
            font-size: 1rem;
            font-weight: 600;
            line-height: 1.25;
        }

        .choice-card input {
            width: 17px;
            height: 17px;
            flex: 0 0 auto;
            accent-color: var(--clinic-maroon);
        }

        .dose-grid {
            display: grid;
            grid-template-columns: 150px minmax(0, 1fr) minmax(0, 1fr);
            gap: 10px;
            align-items: center;
        }

        .dose-row {
            display: contents;
        }

        .dose-label {
            color: #70131b;
            font-size: 0.84rem;
            font-weight: 800;
        }

        .conditional-section.is-hidden {
            display: none;
        }

        .form-field {
            display: flex;
            flex-direction: column;
            position: relative;
            border: 1px solid rgba(127, 29, 45, 0.12);
            background: #fff;
            border-radius: 12px;
            padding: 10px 12px;
        }

        .validation-anchor {
            position: relative;
        }

        .validation-bubble {
            position: absolute;
            left: 12px;
            bottom: calc(100% + 9px);
            z-index: 30;
            width: max-content;
            max-width: min(250px, calc(100vw - 48px));
            padding: 9px 12px;
            border: 1px solid #f1c40f;
            border-radius: 8px;
            background: #fff4b8;
            box-shadow: 0 8px 20px rgba(76, 15, 25, 0.2);
            color: #57111c;
            font-size: 0.78rem;
            font-weight: 800;
            line-height: 1.3;
            animation: validationBubbleIn 0.18s ease-out;
        }

        .validation-bubble::after {
            content: "";
            position: absolute;
            top: 100%;
            left: 18px;
            border: 7px solid transparent;
            border-top-color: #fff4b8;
        }

        @keyframes validationBubbleIn {
            from {
                opacity: 0;
                transform: translateY(5px) scale(0.98);
            }
            to {
                opacity: 1;
                transform: translateY(0) scale(1);
            }
        }

        .form-field.span-2 {
            grid-column: span 2;
        }

        .address-split-grid {
            display: grid;
            grid-template-columns: repeat(2, minmax(0, 1fr));
            gap: 12px;
        }

        .address-split-grid .form-field {
            padding: 0;
            border: 0;
            background: transparent;
            border-radius: 0;
        }

        .home-address-label {
            color: #111827 !important;
        }

        .form-field .form-label {
            display: block;
            color: #6b7280;
            font-size: 0.74rem;
            text-transform: uppercase;
            letter-spacing: 0.06em;
            font-weight: 700;
            margin-bottom: 4px;
        }

        .form-field .form-control,
        .form-field .form-select {
            border: 0;
            background: transparent;
            box-shadow: none;
            border-radius: 0;
            min-height: 24px;
            padding: 0;
            color: #111827;
            font-weight: 700;
        }
        .form-field .form-control.field-maroon,
        .form-field .form-select.field-maroon {
            border: 1.5px solid rgba(127, 29, 45, 0.52);
            background: linear-gradient(180deg, #fffafb 0%, #fff6f7 100%);
            box-shadow:
                0 8px 18px rgba(127, 29, 45, 0.06),
                inset 0 1px 0 rgba(255,255,255,0.82);
            border-radius: 12px;
            min-height: 46px;
            padding: 10px 12px;
        }
        .form-field .form-control.field-maroon.is-filled,
        .form-field .form-select.field-maroon.is-filled {
            border: 1px solid rgba(209, 213, 219, 0.9);
            background: #ffffff !important;
            background-color: #ffffff !important;
            box-shadow:
                0 6px 14px rgba(15, 23, 42, 0.04),
                inset 0 1px 0 rgba(255,255,255,0.82);
            border-radius: 12px;
            min-height: 46px;
            padding: 10px 12px;
        }
        .form-field .form-control.field-maroon.is-filled:focus,
        .form-field .form-select.field-maroon.is-filled:focus {
            background: #ffffff !important;
            background-color: #ffffff !important;
        }
        .form-field input[type="number"].field-maroon,
        .form-field input[type="number"].field-maroon.is-filled {
            appearance: textfield;
            -moz-appearance: textfield;
        }
        .form-field input[type="number"].field-maroon::-webkit-outer-spin-button,
        .form-field input[type="number"].field-maroon::-webkit-inner-spin-button,
        .form-field input[type="number"].field-maroon.is-filled::-webkit-outer-spin-button,
        .form-field input[type="number"].field-maroon.is-filled::-webkit-inner-spin-button {
            -webkit-appearance: none;
            margin: 0;
        }

        .form-field .form-control:focus,
        .form-field .form-select:focus {
            border: 0;
            box-shadow: none;
            background: transparent;
        }
        .form-field .form-control.field-maroon:focus,
        .form-field .form-select.field-maroon:focus {
            border: 1.5px solid var(--clinic-maroon);
            background: linear-gradient(180deg, #fffafb 0%, #fff6f7 100%);
            box-shadow:
                0 0 0 0.18rem rgba(127, 29, 45, 0.12),
                0 10px 22px rgba(127, 29, 45, 0.10);
        }

        .step-fill-note {
            margin: 0 0 12px;
            color: #7f1d2d;
            font-size: 0.84rem;
            font-weight: 700;
        }

        .field-helper {
            margin-top: 6px;
            font-size: 0.75rem;
            color: #6b7280;
            font-weight: 600;
        }

        .form-field .form-control.field-maroon::placeholder {
            color: #a8b0bd;
            font-weight: 500;
            opacity: 1;
        }

        .privacy-note {
            margin: 18px 0 0;
            padding-top: 14px;
            border-top: 1px solid rgba(127, 29, 45, 0.22);
            text-align: center;
            font-size: 0.78rem;
            color: #000000;
            line-height: 1.5;
        }
        .privacy-noteD {
            font-weight: 700;
        }

        @media (max-width: 768px) {
            body {
                padding-bottom: 132px;
            }

            .stepper-shell,
            .requirement-grid,
            .profile-readonly-grid,
            .identity-name-grid,
            .step-one-grid,
            .choice-grid,
            .dose-grid,
            .personal-identity-grid {
                grid-template-columns: 1fr;
            }

            .requirement-extra {
                grid-template-columns: 1fr;
            }

            .requirement-extra .form-field.span-2 {
                grid-column: span 1;
            }

            .form-field.span-2 {
                grid-column: span 1;
            }

            .address-split-grid {
                grid-template-columns: 1fr;
            }

            .stepper-shell {
                top: 0;
                width: 100%;
                min-height: auto;
            }

            .step-progress-top {
                grid-template-columns: 42px minmax(0, 1fr) 86px;
                gap: 10px;
                min-height: 74px;
                padding: 8px 16px;
            }

            .step-brand {
                justify-content: flex-start;
            }

            .step-brand img {
                width: 40px;
                height: 40px;
            }

            .step-brand-text {
                display: none;
            }

            .step-progress-center {
                grid-template-columns: 82px minmax(60px, 1fr) 76px;
                gap: 8px;
                text-align: left;
            }

            .step-progress-copy strong {
                font-size: 0.82rem;
            }

            .step-progress-copy span {
                font-size: 0.72rem;
                margin-top: 1px;
            }

            .step-progress-track {
                height: 7px;
            }

            .step-progress-percent {
                font-size: 0.7rem;
            }

            .step-form-title-card {
                display: flex;
                min-width: 0;
                padding: 8px 8px;
                border-radius: 10px;
            }

            .step-form-title-card strong {
                font-size: 0.66rem;
                line-height: 1.08;
            }

            .step-list-card {
                overflow-x: auto;
                position: static;
                grid-template-columns: repeat(5, 54px);
                width: calc(100vw - 24px);
                margin: 8px auto 0;
                min-height: 58px;
                padding: 12px 14px;
                border-radius: 14px;
            }

            .step-chip {
                display: grid;
                justify-items: center;
                align-content: start;
                gap: 8px;
                min-height: 0;
                text-align: center;
            }

            .step-chip:not(:last-child)::after {
                left: calc(50% + 31px);
                right: calc(-50% + 31px);
                top: 16px;
                bottom: auto;
                width: auto;
                height: 2px;
            }

            .step-number {
                width: 32px;
                height: 32px;
                font-size: 0.82rem;
            }

            .step-label {
                display: none;
            }

            .stepper-spacer {
                height: 86px;
            }

            .esign-grid {
                grid-template-columns: 1fr;
            }

            #digitalSignaturePad,
            #guardianSignaturePad {
                height: 160px;
            }

            .reference-mode-selector {
                grid-template-columns: 1fr;
            }
        }

        @media (max-width: 430px) {
            .upload-example-grid {
                grid-template-columns: 1fr;
            }

            .upload-example img {
                height: 180px;
            }
        }
    </style>
</head>
<body class="health-form-page">
    @php
        $prefill = $healthFormPrefill ?? [];
    @endphp

    <div class="health-shell">
        <div class="health-header"></div>

        <div class="section-body">
            @php
                $selectedPwd = old('has_disability', $prefill['has_disability'] ?? 'No');
                $personalErrorFields = ['school_year', 'course_code', 'course_college', 'home_address', 'zipcode', 'birthday', 'age', 'sex', 'civil_status', 'blood_type', 'contact_no', 'guardian_name', 'landline', 'cellphone'];
                $medicalErrorFields = ['has_illness', 'medical_history', 'other_illness', 'has_disability', 'disability_type', 'food_allergies', 'no_allergies', 'medicine_allergies', 'other_med_allergies', 'is_smoker', 'is_drinker'];
                $covidErrorFields = ['covid_vaccinated', 'vaccine_history'];
                $uploadErrorFields = ['medical_certificate', 'doctor_name', 'med_cert_date', 'med_cert_findings', 'med_cert_findings_details', 'chest_xray_result', 'xray_date', 'xray_findings', 'xray_findings_details', 'pwd_id_proof', 'student_photo', 'health_declaration'];
                $esignErrorFields = ['digital_signature_data', 'digital_signature_upload', 'digital_signature_existing', 'health_profile_certified'];
                $startStep = collect($esignErrorFields)->contains(fn ($field) => $errors->has($field)) ? 6
                    : (collect($uploadErrorFields)->contains(fn ($field) => $errors->has($field)) ? 5
                    : (collect($covidErrorFields)->contains(fn ($field) => $errors->has($field)) ? 4
                    : (collect($medicalErrorFields)->contains(fn ($field) => $errors->has($field)) ? 3
                    : (collect($personalErrorFields)->contains(fn ($field) => $errors->has($field)) ? 2 : 1))));
                $selectedMedicalHistory = old('medical_history', $prefill['medical_history'] ?? []);
                $selectedMedicineAllergies = old('medicine_allergies', $prefill['medicine_allergies'] ?? []);
                $selectedHasIllness = old('has_illness', $prefill['has_illness'] ?? 'No');
                $isHealthFormCorrectionMode = (bool) ($prefill['health_form_correction_mode'] ?? false);
                $healthFormCorrectionNotes = trim((string) ($prefill['health_form_correction_notes'] ?? ''));
                $requestedCorrectionDocuments = collect($prefill['resubmission_required_documents'] ?? [])->filter()->values()->all();
                $existingCorrectionDocuments = $prefill['existing_documents'] ?? [];
                $isDocumentRequested = fn ($documentKey) => in_array($documentKey, $requestedCorrectionDocuments, true);
                $hasExistingDocument = fn ($documentKey) => trim((string) ($existingCorrectionDocuments[$documentKey] ?? '')) !== '';
                $documentOpenUrl = fn ($documentKey) => route('student.health_record.document', ['document' => $documentKey]);
                $displayFirstName = trim((string) ($prefill['first_name'] ?? ''));
                $displayMiddleName = trim((string) ($prefill['middle_name'] ?? ''));
                $displayLastName = trim((string) ($prefill['last_name'] ?? ''));
                $displaySuffixName = trim((string) ($prefill['suffix_name'] ?? ''));

                $displayReferenceNumber = '';
                $referenceMode = 'student_number';
                $manualStudentNumberAllowed = true;
                $selectedReferenceMode = 'student_number';
                $manualStudentModeSelected = true;
                $referenceRequiresValidation = true;
                $referenceVerificationUnavailable = false;
                $stepOneTitle = 'Student ID';
                $stepOneDescription = 'Enter your Student ID, then complete your health information.';
                $referenceLabel = 'Student ID / Student Number';
                $referenceDisplayFallback = 'Enter Student ID';
                $referenceStatusDefault = 'Enter your Student ID, then click the check icon.';
                $courseOptions = $prefill['course_options'] ?? [];
                $courseApplicable = (bool) ($prefill['course_applicable'] ?? false);
                $selectedCourseCode = old('course_code', $prefill['course_code'] ?? '');
                $selectedCourseName = old('course_college', $prefill['course_college'] ?? '');
                $prefillOrOld = function (string $field, $fallback = '') use ($prefill) {
                    $oldValue = old($field);
                    return trim((string) $oldValue) !== '' ? $oldValue : ($prefill[$field] ?? $fallback);
                };

                if ($displayReferenceNumber === '' && ($referenceRequiresValidation || $referenceVerificationUnavailable || $manualStudentModeSelected)) {
                    $startStep = 1;
                }

                $healthFormSteps = [
                    1 => $stepOneTitle,
                    2 => 'Personal Information',
                    3 => 'Medical History',
                    4 => 'COVID-19',
                    5 => 'Clinic Requirements',
                    6 => 'E-Signature',
                ];
            @endphp

            <div class="stepper-shell">
                <div class="step-progress-top">
                    <div class="step-brand">
                        <img src="{{ asset('images/pup_logo.png') }}" alt="PUP Logo">
                        <div class="step-brand-text">PUP Taguig<br>Medical Clinic</div>
                    </div>
                    <div class="step-progress-center">
                        <div class="step-progress-copy">
                            <strong id="currentStepLabel">Step {{ $startStep }} of {{ count($healthFormSteps) }}</strong>
                            <span id="currentStepName">{{ $healthFormSteps[$startStep] ?? 'Clinic Reference' }}</span>
                        </div>
                        <div class="step-progress-track" aria-hidden="true">
                            <div class="step-progress-fill" id="stepProgressFill" style="width: {{ round(($startStep / count($healthFormSteps)) * 100) }}%;"></div>
                        </div>
                        <div class="step-progress-percent" id="stepProgressPercent">{{ round(($startStep / count($healthFormSteps)) * 100) }}% Complete</div>
                    </div>
                    <div class="step-form-title-card" aria-label="Current form">
                        <strong>Health Information Form</strong>
                    </div>
                </div>
            </div>
            <div class="stepper-spacer"></div>

            <form action="{{ route('store.health.form.student') }}" method="POST" enctype="multipart/form-data">
                @csrf
                <input type="hidden" name="reference_mode_selected" value="student_number">
                <input type="hidden" id="course_college" name="course_college" value="{{ $courseApplicable ? $selectedCourseName : '' }}">
                <input type="hidden" name="health_form_category" value="{{ optional($pendingHealthFormRequest ?? null)->category ?: 'General' }}">
                <input type="hidden" name="health_form_request_remarks" value="{{ optional($pendingHealthFormRequest ?? null)->remarks ?: '' }}">
                <input type="hidden" name="consent_acknowledged" id="consentAcknowledged" value="">

                <div class="step-panel {{ $startStep === 1 ? '' : 'is-hidden' }}" id="stepPanel1">
                    <div class="form-intro">
                        <h1 data-title-letter="C">{{ $stepOneTitle }}</h1>
                        <p>{{ $stepOneDescription }}</p>
                        @if(!empty($pendingHealthFormRequest))
                            <p style="margin-top:10px;color:#7f1d2d;font-weight:900;">
                                New Health Form Request: {{ $pendingHealthFormRequest->category }}{{ $pendingHealthFormRequest->remarks ? ' - ' . $pendingHealthFormRequest->remarks : '' }}
                            </p>
                        @endif
                    </div>

                    <div class="identity-overview">
                        <div class="identity-name-grid">
                            <div class="identity-field">
                                <small>First Name</small>
                                <strong>{{ $displayFirstName !== '' ? $displayFirstName : 'N/A' }}</strong>
                            </div>
                            <div class="identity-field">
                                <small>Middle Name</small>
                                <strong>{{ $displayMiddleName !== '' ? $displayMiddleName : 'N/A' }}</strong>
                            </div>
                            <div class="identity-field">
                                <small>Last Name</small>
                                <strong>{{ $displayLastName !== '' ? $displayLastName : 'N/A' }}</strong>
                            </div>
                        </div>
                        <div
                            class="reference-panel {{ $displayReferenceNumber === '' ? 'is-missing' : '' }}"
                            id="referencePanel"
                            data-reference-locked="{{ $displayReferenceNumber !== '' ? 'true' : 'false' }}"
                            data-reference-mode="{{ $referenceMode }}"
                            data-reference-requires-validation="{{ $referenceRequiresValidation ? 'true' : 'false' }}"
                            data-manual-student-mode-allowed="{{ $manualStudentNumberAllowed ? 'true' : 'false' }}"
                        >
                            <small>{{ $referenceLabel }}</small>
                            <div class="reference-display">
                                <strong id="referenceDisplayValue">{{ $displayReferenceNumber !== '' ? $displayReferenceNumber : $referenceDisplayFallback }}</strong>
                            </div>
                            @if($referenceRequiresValidation || $manualStudentModeSelected || $referenceVerificationUnavailable)
                                <button
                                    type="button"
                                    class="reference-edit-btn"
                                    id="editReferenceBtn"
                                    aria-label="Add student ID"
                                    aria-expanded="false"
                                    title="{{ $displayReferenceNumber !== '' ? 'Reference already verified' : 'Add reference number' }}"
                                >
                                    <svg class="reference-icon-edit" viewBox="0 0 24 24" aria-hidden="true">
                                        <path d="M12 20h9"></path>
                                        <path d="M16.5 3.5a2.12 2.12 0 0 1 3 3L8 18l-4 1 1-4Z"></path>
                                    </svg>
                                    <svg class="reference-icon-check" viewBox="0 0 24 24" aria-hidden="true">
                                        <path d="m5 12 4 4L19 6"></path>
                                    </svg>
                                </button>
                            @endif
                            <input type="hidden" name="reference_number" id="reference_number" value="{{ $displayReferenceNumber }}">
                            @if($referenceRequiresValidation || $manualStudentModeSelected || $referenceVerificationUnavailable)
                                <div class="reference-verify-wrap" id="referenceVerifyWrap">
                                    <div class="reference-verify-row">
                                        <input
                                            type="text"
                                            id="reference_editor"
                                            class="reference-verify-input"
                                            value="{{ $displayReferenceNumber }}"
                                            placeholder="{{ $manualStudentModeSelected ? '2020-00000-TG-0' : '0000-0000-0000' }}"
                                            maxlength="20"
                                            autocomplete="off"
                                            aria-describedby="referenceVerifyStatus"
                                        >
                                    </div>
                                </div>
                            @endif
                            <p class="reference-verify-status {{ $displayReferenceNumber === '' ? '' : 'is-success' }}" id="referenceVerifyStatus" aria-live="polite">
                                {{ $referenceStatusDefault }}
                            </p>
                            @error('reference_number')
                                <p class="reference-field-error">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>

                    <div class="upload-instruction-card">
                        <strong>Instructions for Completing Your Health Profile</strong>
                        <ol>
                            <li>Review your Student ID and name before proceeding.</li>
                            <li>Complete every required field in Personal Information using accurate and current details.</li>
                            <li>Answer the Medical History, allergy, disability, smoking, and alcohol questions truthfully.</li>
                            <li>Provide your COVID-19 vaccination status and dose details, when applicable.</li>
                            <li>Upload your formal 2x2 photo as JPG or PNG. This is the only document required for this form.</li>
                            <li>Medical certificate, health declaration, chest X-ray, and PWD documents are optional and may be submitted later through My Account or when requested by the clinic.</li>
                            <li>Complete the required e-signature for the Declaration of Medical Information and Data Subject Consent Form.</li>
                        </ol>
                    </div>

                    <div class="btn-row">
                        @env('local')
                            <a href="{{ url('/student/account') }}" class="btn btn-health btn-health-back">
                                <svg viewBox="0 0 24 24" aria-hidden="true">
                                    <path d="m15 18-6-6 6-6"></path>
                                </svg>
                                <span>Back</span>
                            </a>
                            <button
                                type="submit"
                                class="btn btn-health btn-testing-skip"
                                formaction="{{ route('student.health_form.testing_skip') }}"
                                formmethod="POST"
                                formnovalidate
                                data-testing-skip
                            >
                                Skip to Print (Testing)
                            </button>
                            <button type="button" class="btn btn-health" data-step-next="2" style="background-color: #ffc107; color: #000;">
                                <span>Skip Step 1</span>
                            </button>
                        @endenv
                        <button type="button" class="btn btn-health btn-health-next" id="nextToStep2" {{ $referenceVerificationUnavailable ? 'disabled' : '' }}>
                            <span>Next</span>
                            <svg viewBox="0 0 24 24" aria-hidden="true">
                                <path d="m9 18 6-6-6-6"></path>
                            </svg>
                        </button>
                        @if($referenceVerificationUnavailable)
                            <button type="button" class="btn btn-health btn-health-next" onclick="window.location.reload()">
                                <svg viewBox="0 0 24 24" aria-hidden="true">
                                    <path d="M20 11a8 8 0 1 0-2.34 5.66"></path>
                                    <path d="M20 4v7h-7"></path>
                                </svg>
                                <span>Retry Verification</span>
                            </button>
                        @endif
                    </div>
                </div>

                <div class="step-panel {{ $startStep === 2 ? '' : 'is-hidden' }}" id="stepPanel2">
                    <h2 class="section-title step-page-title" data-title-letter="P">Personal Information</h2>
                    <p class="step-fill-note">Complete the student and emergency contact details from the official PUP Health Information Form.</p>
                    <div class="personal-identity-grid">
                        <div class="form-field">
                            <label class="form-label" for="profile_first_name">First Name</label>
                            <input id="profile_first_name" class="form-control identity-readonly" value="{{ $displayFirstName !== '' ? $displayFirstName : 'N/A' }}" readonly>
                        </div>
                        <div class="form-field">
                            <label class="form-label" for="profile_middle_name">Middle Name</label>
                            <input id="profile_middle_name" class="form-control identity-readonly" value="{{ $displayMiddleName !== '' ? $displayMiddleName : 'N/A' }}" readonly>
                        </div>
                        <div class="form-field">
                            <label class="form-label" for="profile_last_name">Last Name</label>
                            <input id="profile_last_name" class="form-control identity-readonly" value="{{ $displayLastName !== '' ? $displayLastName : 'N/A' }}" readonly>
                        </div>
                        <div class="form-field">
                            <label class="form-label" for="profile_suffix_name">Suffix Name</label>
                            <input id="profile_suffix_name" class="form-control identity-readonly" value="{{ $displaySuffixName }}" readonly>
                            <input type="hidden" name="suffix_name" value="{{ $displaySuffixName }}">
                        </div>
                    </div>
                    <div class="form-field personal-email-field">
                        <label class="form-label" for="profile_email">Email Address</label>
                        <input id="profile_email" type="email" class="form-control identity-readonly" value="{{ $user->email }}" readonly>
                    </div>
                    @if($courseApplicable)
                        <div class="form-field personal-email-field">
                            <label class="form-label" for="course_code">Course / Program <span class="required">*</span></label>
                            <div class="clinic-select-wrap course-select-wrap" data-clinic-select data-select-placeholder="Select course">
                                <select id="course_code" class="form-select clinic-select-native field-maroon" name="course_code" required>
                                    <option value="">Select course</option>
                                    @foreach($courseOptions as $courseOption)
                                        <option
                                            value="{{ $courseOption['code'] }}"
                                            data-course-name="{{ $courseOption['name'] }}"
                                            {{ $selectedCourseCode === $courseOption['code'] ? 'selected' : '' }}
                                        >
                                            {{ $courseOption['label'] }}
                                        </option>
                                    @endforeach
                                </select>
                                <button type="button" class="clinic-select-display" aria-haspopup="listbox" aria-expanded="false">Select course</button>
                                <div class="clinic-select-menu" role="listbox" aria-label="Course options">
                                    @foreach($courseOptions as $courseOption)
                                        <button
                                            type="button"
                                            class="clinic-select-option"
                                            data-select-value="{{ $courseOption['code'] }}"
                                        >
                                            {{ $courseOption['label'] }}
                                        </button>
                                    @endforeach
                                </div>
                            </div>
                        </div>
                    @endif
                    <div class="step-one-grid">
                        <div class="form-field">
                            <label class="form-label" for="school_year">School Year <span class="required">*</span></label>
                            <input
                                id="school_year"
                                class="form-control field-maroon"
                                name="school_year"
                                value="{{ old('school_year', $prefill['school_year'] ?? '') }}"
                                placeholder="YYYY-YYYY"
                                pattern="\d{4}-\d{4}"
                                inputmode="numeric"
                                required
                            >
                        </div>
                        @if(!empty($prefill['year_level']))
                            <div class="form-field">
                                <label class="form-label" for="year_level_display">Year Level</label>
                                <input
                                    id="year_level_display"
                                    class="form-control field-maroon identity-readonly"
                                    value="{{ $prefill['year_level'] }}"
                                    readonly
                                >
                            </div>
                        @endif
                        @if(!empty($prefill['section']))
                            <div class="form-field">
                                <label class="form-label" for="section_display">Section</label>
                                <input
                                    id="section_display"
                                    class="form-control field-maroon identity-readonly"
                                    value="{{ $prefill['section'] }}"
                                    readonly
                                >
                            </div>
                        @endif
                        <div class="form-field">
                            <label class="form-label" for="birthday">Birthday <span class="required">*</span></label>
                            <input id="birthday" type="date" class="form-control field-maroon" name="birthday" value="{{ $prefillOrOld('birthday') }}" required>
                        </div>
                        <div class="form-field">
                            <label class="form-label" for="age">Age <span class="required">*</span></label>
                            <input id="age" type="number" class="form-control field-maroon" name="age" value="{{ $prefillOrOld('age') }}" min="15" max="100" required readonly>
                        </div>
                        <div class="form-field">
                            <label class="form-label" for="sex">Sex <span class="required">*</span></label>
                            <div class="clinic-select-wrap" data-clinic-select data-select-placeholder="Select sex">
                                <select id="sex" class="form-select clinic-select-native field-maroon" name="sex" required>
                                    <option value="">Select sex</option>
                                    @foreach(['Male', 'Female'] as $option)
                                        <option value="{{ $option }}" {{ $prefillOrOld('sex') === $option ? 'selected' : '' }}>{{ $option }}</option>
                                    @endforeach
                                </select>
                                <button type="button" class="clinic-select-display" aria-haspopup="listbox" aria-expanded="false">Select sex</button>
                                <div class="clinic-select-menu" role="listbox" aria-label="Sex options">
                                    <button type="button" class="clinic-select-option" data-select-value="Male">Male</button>
                                    <button type="button" class="clinic-select-option" data-select-value="Female">Female</button>
                                </div>
                            </div>
                        </div>
                        <div class="form-field">
                            <label class="form-label" for="civil_status">Civil Status <span class="required">*</span></label>
                            <div class="clinic-select-wrap" data-clinic-select data-select-placeholder="Select civil status">
                                <select id="civil_status" class="form-select clinic-select-native field-maroon" name="civil_status" required>
                                    @foreach(['Single', 'Married', 'Widowed', 'Separated'] as $option)
                                        <option value="{{ $option }}" {{ old('civil_status', $prefill['civil_status'] ?? 'Single') === $option ? 'selected' : '' }}>{{ $option }}</option>
                                    @endforeach
                                </select>
                                <button type="button" class="clinic-select-display" aria-haspopup="listbox" aria-expanded="false">Select civil status</button>
                                <div class="clinic-select-menu" role="listbox" aria-label="Civil status options">
                                    @foreach(['Single', 'Married', 'Widowed', 'Separated'] as $option)
                                        <button type="button" class="clinic-select-option" data-select-value="{{ $option }}">{{ $option }}</button>
                                    @endforeach
                                </div>
                            </div>
                        </div>
                        <div class="form-field">
                            <label class="form-label" for="blood_type">Blood Type <span class="required">*</span></label>
                            <div class="clinic-select-wrap" data-clinic-select data-select-placeholder="Select blood type">
                                <select id="blood_type" class="form-select clinic-select-native field-maroon" name="blood_type" required>
                                    @foreach(['Unknown', 'A+', 'A-', 'B+', 'B-', 'AB+', 'AB-', 'O+', 'O-'] as $option)
                                        <option value="{{ $option }}" {{ old('blood_type', $prefill['blood_type'] ?? 'Unknown') === $option ? 'selected' : '' }}>{{ $option }}</option>
                                    @endforeach
                                </select>
                                <button type="button" class="clinic-select-display" aria-haspopup="listbox" aria-expanded="false">Select blood type</button>
                                <div class="clinic-select-menu" role="listbox" aria-label="Blood type options">
                                    @foreach(['Unknown', 'A+', 'A-', 'B+', 'B-', 'AB+', 'AB-', 'O+', 'O-'] as $option)
                                        <button type="button" class="clinic-select-option" data-select-value="{{ $option }}">{{ $option }}</button>
                                    @endforeach
                                </div>
                            </div>
                        </div>
                        @php
                            $combinedHomeAddress = $prefillOrOld('home_address');
                            $oldAddressStreet = old('home_address_street');
                            $oldAddressBarangay = old('home_address_barangay');
                            $oldAddressCity = old('home_address_city_municipality');
                            $oldAddressProvince = old('home_address_province');
                            $hasSplitAddressOldInput = $oldAddressStreet !== null
                                || $oldAddressBarangay !== null
                                || $oldAddressCity !== null
                                || $oldAddressProvince !== null;
                            $addressStreet = '';
                            $addressBarangay = '';
                            $addressCity = '';
                            $addressProvince = '';

                            if ($hasSplitAddressOldInput) {
                                $addressStreet = trim((string) $oldAddressStreet) !== ''
                                    ? $oldAddressStreet
                                    : ($prefill['home_address_street'] ?? '');
                                $addressBarangay = trim((string) $oldAddressBarangay) !== ''
                                    ? $oldAddressBarangay
                                    : ($prefill['home_address_barangay'] ?? '');
                                $addressCity = trim((string) $oldAddressCity) !== ''
                                    ? $oldAddressCity
                                    : ($prefill['home_address_city_municipality'] ?? '');
                                $addressProvince = trim((string) $oldAddressProvince) !== ''
                                    ? $oldAddressProvince
                                    : ($prefill['home_address_province'] ?? '');
                            } elseif (
                                !empty($prefill['home_address_street'])
                                || !empty($prefill['home_address_barangay'])
                                || !empty($prefill['home_address_city_municipality'])
                                || !empty($prefill['home_address_province'])
                            ) {
                                $addressStreet = $prefill['home_address_street'] ?? '';
                                $addressBarangay = $prefill['home_address_barangay'] ?? '';
                                $addressCity = $prefill['home_address_city_municipality'] ?? '';
                                $addressProvince = $prefill['home_address_province'] ?? '';
                            } else {
                                $addressParts = array_values(array_filter(array_map('trim', explode(',', (string) $combinedHomeAddress)), fn ($part) => $part !== ''));

                                if (count($addressParts) >= 4) {
                                    $addressStreet = $addressParts[0];
                                    $addressBarangay = $addressParts[1];
                                    $addressCity = $addressParts[2];
                                    $addressProvince = implode(', ', array_slice($addressParts, 3));
                                } else {
                                    $addressStreet = $combinedHomeAddress;
                                }
                            }
                        @endphp
                        <div class="form-field span-2">
                            <label class="form-label home-address-label" for="home_address_street">Home Address <span class="required">*</span></label>
                            <input id="home_address" type="hidden" name="home_address" value="{{ $combinedHomeAddress }}" required>
                            <div class="address-split-grid">
                                <div class="form-field">
                                    <label class="form-label" for="home_address_street">House No. / Street <span class="required">*</span></label>
                                    <input
                                        id="home_address_street"
                                        class="form-control field-maroon"
                                        name="home_address_street"
                                        value="{{ $addressStreet }}"
                                        placeholder="e.g., 123 Mabini St."
                                        data-home-address-part
                                        required
                                    >
                                </div>
                                <div class="form-field">
                                    <label class="form-label" for="home_address_barangay">Barangay <span class="required">*</span></label>
                                    <input
                                        id="home_address_barangay"
                                        class="form-control field-maroon"
                                        name="home_address_barangay"
                                        value="{{ $addressBarangay }}"
                                        placeholder="e.g., Brgy. Central"
                                        data-home-address-part
                                        required
                                    >
                                </div>
                                <div class="form-field">
                                    <label class="form-label" for="home_address_city_municipality">City / Municipality <span class="required">*</span></label>
                                    <input
                                        id="home_address_city_municipality"
                                        class="form-control field-maroon"
                                        name="home_address_city_municipality"
                                        value="{{ $addressCity }}"
                                        placeholder="e.g., Taguig City"
                                        data-home-address-part
                                        required
                                    >
                                </div>
                                <div class="form-field">
                                    <label class="form-label" for="home_address_province">Province <span class="required">*</span></label>
                                    <input
                                        id="home_address_province"
                                        class="form-control field-maroon"
                                        name="home_address_province"
                                        value="{{ $addressProvince }}"
                                        placeholder="e.g., Metro Manila"
                                        data-home-address-part
                                        required
                                    >
                                </div>
                            </div>
                            <div class="field-helper"></div>
                        </div>
                        <div class="form-field">
                            <label class="form-label" for="zipcode">ZIP Code <span class="required">*</span></label>
                            <input id="zipcode" class="form-control field-maroon" name="zipcode" value="{{ $prefillOrOld('zipcode') }}" required>
                        </div>
                        <div class="form-field">
                            <label class="form-label" for="contact_no">Student Contact Number <span class="required">*</span></label>
                            <input
                                id="contact_no"
                                class="form-control field-maroon"
                                name="contact_no"
                                value="{{ old('contact_no', $prefill['contact_number'] ?? $user->contact_no ?? '') }}"
                                placeholder="Enter 11-digit mobile number"
                                inputmode="numeric"
                                pattern="[0-9]{11,20}"
                                minlength="11"
                                maxlength="20"
                                data-numeric-contact
                                data-validation-message="Enter numbers only, at least 11 digits."
                                required
                            >
                            <div class="field-helper">Example: 09123456789</div>
                        </div>
                        <div class="form-field">
                            <label class="form-label" for="guardian_name">Parent / Guardian Name <span class="required">*</span></label>
                            <input id="guardian_name" class="form-control field-maroon" name="guardian_name" value="{{ old('guardian_name', $prefill['guardian_name'] ?? '') }}" required>
                        </div>
                        <div class="form-field">
                            <label class="form-label" for="cellphone">Parent / Guardian Contact Number <span class="required">*</span></label>
                            <input
                                id="cellphone"
                                class="form-control field-maroon"
                                name="cellphone"
                                value="{{ old('cellphone', $prefill['cellphone'] ?? '') }}"
                                placeholder="Enter 11-digit mobile number"
                                inputmode="numeric"
                                pattern="[0-9]{11,20}"
                                minlength="11"
                                maxlength="20"
                                data-numeric-contact
                                data-validation-message="Enter numbers only, at least 11 digits."
                                required
                            >
                            <div class="field-helper">Example: 09123456789</div>
                        </div>
                        <div class="form-field">
                            <label class="form-label" for="landline">Landline</label>
                            <input
                                id="landline"
                                class="form-control field-maroon"
                                name="landline"
                                value="{{ old('landline', $prefill['landline'] ?? '') }}"
                                placeholder="Enter landline or NA / None"
                                required
                            >
                            <div class="field-helper">Put NA or None if not applicable.</div>
                        </div>
                    </div>
                    <div class="btn-row">
                        <button type="button" class="btn btn-health btn-health-back" data-step-back="1">
                            <svg viewBox="0 0 24 24" aria-hidden="true">
                                <path d="m15 18-6-6 6-6"></path>
                            </svg>
                            <span>Back</span>
                        </button>
                        <button type="button" class="btn btn-health btn-health-next" data-step-next="3">
                            <span>Next</span>
                            <svg viewBox="0 0 24 24" aria-hidden="true">
                                <path d="m9 18 6-6-6-6"></path>
                            </svg>
                        </button>
                    </div>
                </div>

                <div class="student-consent-modal" id="studentConsentModal" role="dialog" aria-modal="true" aria-labelledby="studentConsentTitle" hidden>
                    <section class="student-consent-card">
                        <button type="button" class="student-consent-close" id="studentConsentClose" aria-label="Close consent form">&times;</button>
                        <p class="student-consent-kicker">Data Privacy Consent</p>
                        <h2 id="studentConsentTitle">Declaration of Medical Information and Data Subject Consent Form</h2>
                        
                        @php
                            $pendingCategory = optional($pendingHealthFormRequest)->category;
                            $isOjtCategory = $pendingCategory && (stripos($pendingCategory, 'ojt') !== false || stripos($pendingCategory, 'on-the-job') !== false);
                            $isStudentCategory = $pendingCategory && (stripos($pendingCategory, 'student') !== false || stripos($pendingCategory, 'enrolment') !== false || stripos($pendingCategory, 'general') !== false);
                            $initialCategory = $isOjtCategory ? 'On-the-Job Training (OJT)' : ($isStudentCategory ? 'Student' : '');
                        @endphp

                        <div class="student-consent-purpose-wrap" style="margin: 0 0 16px;">
                            <label for="studentConsentPurpose" style="display: block; margin-bottom: 6px; font-size: 12px; font-weight: 800; color: #7f1d2d; text-transform: uppercase; letter-spacing: .05em;">
                                Purpose of Medical Clearance <span style="color: #dc2626;">*</span>
                            </label>
                            <select id="studentConsentPurpose" name="health_form_category" class="form-select" style="width: 100%; padding: 8px 12px; border: 1.5px solid #7f1d2d; border-radius: 8px; font-size: 13.5px; font-weight: 700; background-color: #ffffff; color: #1f2937;" required>
                                @if(!$pendingCategory)
                                    <option value="" disabled {{ old('health_form_category', $initialCategory) === '' ? 'selected' : '' }}>-- Select Purpose of Medical Clearance --</option>
                                    <option value="Student" {{ old('health_form_category', $initialCategory) === 'Student' ? 'selected' : '' }}>Student (Enrolment / Annual Medical Clearance)</option>
                                    <option value="On-the-Job Training (OJT)" {{ old('health_form_category', $initialCategory) === 'On-the-Job Training (OJT)' ? 'selected' : '' }}>On-the-Job Training (OJT)</option>
                                @elseif($isOjtCategory)
                                    <option value="On-the-Job Training (OJT)" selected>On-the-Job Training (OJT)</option>
                                    <option value="Student">Student (Enrolment / Annual Medical Clearance)</option>
                                @else
                                    <option value="Student" selected>Student (Enrolment / Annual Medical Clearance)</option>
                                    <option value="On-the-Job Training (OJT)">On-the-Job Training (OJT)</option>
                                @endif
                            </select>
                        </div>

                        <div class="student-consent-copy">
                            <p id="studentConsentCertParagraph">
                                I hereby certify that the medical health information given to the physician and nurses of Polytechnic University of the Philippines (PUP) during my on-site consultation for the issuance of medical clearance for <u id="consentDynamicPurpose" style="font-weight: 700;">{{ $initialCategory === 'On-the-Job Training (OJT)' ? 'On-the-Job Training (OJT)' : ($initialCategory === 'Student' ? 'enrolled student' : '[Select Purpose]') }}</u> are true, correct and complete to the best of my knowledge. I have fully disclosed all the medical condition that may affect in the assessment to endorse my <u id="consentDynamicEndorsement" style="font-weight: 700;">{{ $initialCategory === 'On-the-Job Training (OJT)' ? 'On-the-Job Training (OJT)' : ($initialCategory === 'Student' ? 'enrolment as a student' : '[Select Purpose]') }}</u> of PUP Taguig Campus
                            </p>
                            <p>I also understand that the PUP Medical Services and University will not be liable for any untoward incident that may arise due to my failure to disclose accurate information or intentionally providing false and deceptive information.</p>
                            <p>In compliance with the Data Privacy Act of 2012 and its implementing Rules and Regulations, I voluntarily consent to the collection, processing and storage of my personal and health information for the purpose/s of health assessment, treatment/ or research (following research ethics guidelines) for the improvement of healthcare services.</p>
                        </div>
                        <p class="student-consent-guardian-note" id="studentConsentGuardianNote" hidden>Because you are 17 years old or below, a parent or guardian must also sign the printed consent form.</p>
                        <label class="student-consent-agreement">
                            <input type="checkbox" id="studentConsentCheckbox" required>
                            <span>I have read and agree to this declaration and consent.</span>
                        </label>
                        <button type="button" class="student-consent-continue" id="studentConsentContinue">Continue</button>
                    </section>
                </div>

                <div class="step-panel {{ $startStep === 3 ? '' : 'is-hidden' }}" id="stepPanel3">
                    <h2 class="section-title step-page-title" data-title-letter="M">Medical History</h2>
                    <div class="form-field mb-3">
                        <label class="form-label">Do you need medical attention or have a known medical illness? <span class="required">*</span></label>
                        <div class="pwd-toggle">
                            @foreach(['No', 'Yes'] as $option)
                                <input class="pwd-radio" type="radio" name="has_illness" id="illness_{{ strtolower($option) }}" value="{{ $option }}" required {{ $selectedHasIllness === $option ? 'checked' : '' }}>
                                <label class="pwd-option" for="illness_{{ strtolower($option) }}">{{ $option }}</label>
                            @endforeach
                        </div>
                    </div>
                    <div id="medicalHistoryDetails" class="conditional-section">
                        <h2 class="section-title">Known Conditions</h2>
                        <div class="choice-grid">
                            @foreach(['Asthma', 'Loss of Consciousness', 'Eye Disease / Defect', 'Accident Injuries', 'Diabetes', 'Heart Disease', 'Kidney Disease', 'Tuberculosis / Primary Complex', 'Convulsion / Epilepsy', 'Migraine', 'Hyperventilation', 'High Blood Pressure', 'Hemophilia'] as $condition)
                                <label class="choice-card">
                                    <input type="checkbox" name="medical_history[]" value="{{ $condition }}" {{ in_array($condition, $selectedMedicalHistory, true) ? 'checked' : '' }}>
                                    <span>{{ $condition }}</span>
                                </label>
                            @endforeach
                        </div>
                        <div class="form-field mt-3">
                            <label class="form-label" for="other_illness">Other Illness / Medical Notes</label>
                            <textarea id="other_illness" name="other_illness" class="form-control field-maroon" rows="3">{{ old('other_illness', $prefill['other_illness'] ?? '') }}</textarea>
                        </div>
                    </div>

                    <h2 class="section-title mt-4">Disability Information</h2>
                    <div class="step-one-grid">
                        <div class="form-field">
                            <label class="form-label">Do you have a disability? <span class="required">*</span></label>
                            <div class="pwd-toggle" id="pwdToggle">
                                <input class="pwd-radio" type="radio" name="has_disability" id="pwd_no" value="No" required {{ $selectedPwd !== 'Yes' ? 'checked' : '' }}>
                                <label class="pwd-option" for="pwd_no">No</label>
                                <input class="pwd-radio" type="radio" name="has_disability" id="pwd_yes" value="Yes" {{ $selectedPwd === 'Yes' ? 'checked' : '' }}>
                                <label class="pwd-option" for="pwd_yes">Yes</label>
                            </div>
                        </div>
                        <div class="form-field" id="disabilityTypeWrap">
                            <label class="form-label" for="disability_type">Disability Type <span class="required">*</span></label>
                            <input id="disability_type" name="disability_type" class="form-control field-maroon" value="{{ old('disability_type', $prefill['disability_type'] ?? '') }}" list="disabilityTypeSuggestions" autocomplete="off" placeholder="Type or select disability type">
                            <datalist id="disabilityTypeSuggestions">
                                <option value="Physical Disability">
                                <option value="Visual Disability">
                                <option value="Hearing Disability">
                                <option value="Speech and Language Impairment">
                                <option value="Intellectual Disability">
                                <option value="Learning Disability">
                                <option value="Psychosocial Disability">
                                <option value="Autism Spectrum Disorder">
                                <option value="Disability Due to Chronic Illness">
                                <option value="Orthopedic Disability">
                                <option value="Multiple Disability">
                                <option value="Other Disability">
                            </datalist>
                        </div>
                    </div>

                    <h2 class="section-title mt-4">Allergies</h2>
                    <label class="choice-card mb-3">
                        <input id="no_allergies" type="checkbox" name="no_allergies" value="1" {{ old('no_allergies', $prefill['no_allergies'] ?? false) ? 'checked' : '' }}>
                        <span>No Known Allergies</span>
                    </label>
                    <div id="allergyDetails" class="conditional-section">
                        <div class="form-field mb-3">
                            <label class="form-label" for="food_allergies">Food Allergies</label>
                            <input id="food_allergies" name="food_allergies" class="form-control field-maroon" value="{{ old('food_allergies', $prefill['food_allergies'] ?? '') }}" placeholder="Specify food allergies">
                        </div>
                        <div class="choice-grid">
                            @foreach(['Aspirin', 'Ibuprofen', 'Amoxicillin', 'Mefenamic Acid', 'Penicillin'] as $medicine)
                                <label class="choice-card">
                                    <input type="checkbox" name="medicine_allergies[]" value="{{ $medicine }}" {{ in_array($medicine, $selectedMedicineAllergies, true) ? 'checked' : '' }}>
                                    <span>{{ $medicine }}</span>
                                </label>
                            @endforeach
                        </div>
                        <div class="form-field mt-3">
                            <label class="form-label" for="other_med_allergies">Other Medicine Allergies</label>
                            <input id="other_med_allergies" name="other_med_allergies" class="form-control field-maroon" value="{{ old('other_med_allergies', $prefill['other_med_allergies'] ?? '') }}">
                        </div>
                    </div>

                    <h2 class="section-title mt-4">Personal Social History</h2>
                    <div class="step-one-grid">
                        @foreach(['is_smoker' => 'Cigarette Smoking', 'is_drinker' => 'Alcohol Drinking'] as $field => $label)
                            <div class="form-field">
                                <label class="form-label">{{ $label }} <span class="required">*</span></label>
                                <div class="pwd-toggle">
                                    @foreach(['No', 'Yes'] as $option)
                                        <input class="pwd-radio" type="radio" name="{{ $field }}" id="{{ $field }}_{{ strtolower($option) }}" value="{{ $option }}" required {{ old($field, $prefill[$field] ?? 'No') === $option ? 'checked' : '' }}>
                                        <label class="pwd-option" for="{{ $field }}_{{ strtolower($option) }}">{{ $option }}</label>
                                    @endforeach
                                </div>
                            </div>
                        @endforeach
                    </div>
                    <div class="btn-row">
                        <button type="button" class="btn btn-health btn-health-back" data-step-back="2">
                            <svg viewBox="0 0 24 24" aria-hidden="true">
                                <path d="m15 18-6-6 6-6"></path>
                            </svg>
                            <span>Back</span>
                        </button>
                        <button type="button" class="btn btn-health btn-health-next" data-step-next="4">
                            <span>Next</span>
                            <svg viewBox="0 0 24 24" aria-hidden="true">
                                <path d="m9 18 6-6-6-6"></path>
                            </svg>
                        </button>
                    </div>
                </div>

                <div class="step-panel {{ $startStep === 4 ? '' : 'is-hidden' }}" id="stepPanel4">
                    <h2 class="section-title step-page-title" data-title-letter="C">COVID-19</h2>
                    <p class="step-fill-note">Select your vaccination status. If vaccinated, provide dose details if available.</p>
                    @php
                        $selectedCovidVaccinated = old('covid_vaccinated', $prefill['covid_vaccinated'] ?? '');
                    @endphp
                    <div class="form-field mb-3">
                        <label class="form-label">Have you received a COVID-19 vaccine? <span class="required">*</span></label>
                        <div class="pwd-toggle" id="covidVaccinatedToggle">
                            <input class="pwd-radio" type="radio" name="covid_vaccinated" id="covid_vaccinated_no" value="No" required {{ $selectedCovidVaccinated === 'No' ? 'checked' : '' }}>
                            <label class="pwd-option" for="covid_vaccinated_no">No</label>
                            <input class="pwd-radio" type="radio" name="covid_vaccinated" id="covid_vaccinated_yes" value="Yes" required {{ $selectedCovidVaccinated === 'Yes' ? 'checked' : '' }}>
                            <label class="pwd-option" for="covid_vaccinated_yes">Yes</label>
                        </div>
                    </div>
                    <div id="vaccineHistoryDetails" class="conditional-section {{ $selectedCovidVaccinated === 'Yes' ? '' : 'is-hidden' }}">
                        <div class="dose-grid">
                            @foreach(['first_dose' => '1st Dose', 'second_dose' => '2nd Dose', 'booster_1' => 'Booster 1', 'booster_2' => 'Booster 2'] as $doseKey => $doseLabel)
                                @php $doseRequired = false; @endphp
                                <div class="dose-row">
                                    <div class="dose-label">{{ $doseLabel }} (Optional)</div>
                                    <div class="form-field">
                                        <label class="form-label" for="{{ $doseKey }}_date">Date Received @if($doseRequired)<span class="required">*</span>@endif</label>
                                        <input id="{{ $doseKey }}_date" type="date" name="vaccine_history[{{ $doseKey }}][date]" class="form-control field-maroon" value="{{ old("vaccine_history.$doseKey.date", $prefill['vaccine_history'][$doseKey]['date'] ?? '') }}" min="2021-03-01" max="{{ now()->toDateString() }}" data-vaccine-field data-dose-required="{{ $doseRequired ? 'true' : 'false' }}">
                                    </div>
                                    <div class="form-field">
                                        <label class="form-label" for="{{ $doseKey }}_brand">Vaccine Brand @if($doseRequired)<span class="required">*</span>@endif</label>
                                        <input id="{{ $doseKey }}_brand" name="vaccine_history[{{ $doseKey }}][brand]" class="form-control field-maroon" value="{{ old("vaccine_history.$doseKey.brand", $prefill['vaccine_history'][$doseKey]['brand'] ?? '') }}" placeholder="e.g. Pfizer, Moderna" data-vaccine-field data-dose-required="{{ $doseRequired ? 'true' : 'false' }}">
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>
                    <div class="btn-row">
                        <button type="button" class="btn btn-health btn-health-back" data-step-back="3">
                            <svg viewBox="0 0 24 24" aria-hidden="true">
                                <path d="m15 18-6-6 6-6"></path>
                            </svg>
                            <span>Back</span>
                        </button>
                        <button type="button" class="btn btn-health btn-health-next" data-step-next="5">
                            <span>Next</span>
                            <svg viewBox="0 0 24 24" aria-hidden="true">
                                <path d="m9 18 6-6-6-6"></path>
                            </svg>
                        </button>
                    </div>
                </div>

                <div class="step-panel {{ $startStep === 5 ? '' : 'is-hidden' }}" id="stepPanel5">
                    <h2 class="section-title step-page-title" data-title-letter="R">Clinic Requirements</h2>
                    @if($isHealthFormCorrectionMode)
                        <div class="correction-mode-note">
                            Clinic note: {{ $healthFormCorrectionNotes !== '' ? $healthFormCorrectionNotes : 'Please review and correct your Health Information Form details.' }}
                            Existing documents are locked unless the clinic requested a replacement for that specific file.
                        </div>
                    @endif
                    <div class="requirement-grid">
                        @php
                            $pwdRequested = $isDocumentRequested('pwd_id_proof');
                            $pwdUploaded = $hasExistingDocument('pwd_id_proof');
                            $pwdLocked = $isHealthFormCorrectionMode && !$pwdRequested && $pwdUploaded;
                        @endphp
                        <div class="requirement-card" id="pwdUploadWrap" data-requirement-card>
                            <div class="requirement-card-header">
                                <strong>PWD ID <span class="required">*</span></strong>
                                <span class="requirement-badge">PDF</span>
                            </div>
                            <p class="requirement-guideline">Please upload a clear and readable scanned copy of the front of your valid PWD ID.</p>
                            @if($isHealthFormCorrectionMode && $pwdUploaded)
                                <div class="existing-upload-preview">
                                    <span>PWD ID Proof already uploaded.</span>
                                    <a href="{{ $documentOpenUrl('pwd_id_proof') }}" target="_blank" rel="noopener">Open</a>
                                </div>
                            @endif
                            <input id="pwd_id_proof" type="file" name="pwd_id_proof" class="form-control" accept=".pdf,application/pdf" data-requirement-file data-upload-input data-preview-kind="pdf" data-correction-locked="{{ $pwdLocked ? 'true' : 'false' }}" {{ $pwdLocked ? 'disabled' : '' }}>
                            <div class="upload-preview-card" data-upload-preview aria-live="polite"></div>
                            @if($pwdLocked)
                                <div class="file-locked-note">Preview only. The clinic did not request replacement for this file.</div>
                            @elseif($isHealthFormCorrectionMode && $pwdRequested)
                                <div class="file-locked-note">Replacement requested by the clinic. Upload a new PWD ID Proof.</div>
                            @endif
                            <small>Required when PWD is selected. Allowed: PDF only, max 1MB.</small>
                        </div>
                        @php
                            $photoRequested = $isDocumentRequested('student_photo');
                            $photoUploaded = $hasExistingDocument('student_photo');
                            $photoLocked = $isHealthFormCorrectionMode && !$photoRequested && $photoUploaded;
                        @endphp
                        @php
                            $declarationRequested = $isDocumentRequested('health_declaration');
                            $declarationUploaded = $hasExistingDocument('health_declaration');
                            $declarationLocked = $isHealthFormCorrectionMode && !$declarationRequested && $declarationUploaded;
                        @endphp
                        <div class="requirement-card">
                            <div class="requirement-card-header">
                                <strong>2x2 Photo (Image) <span class="required">*</span></strong>
                                <span class="requirement-badge">JPG/PNG</span>
                            </div>
                            <p class="requirement-guideline">Must be a formal photo on a plain white background, taken within the last 6 months.</p>
                            <div class="upload-example-grid" aria-label="2x2 photo upload examples">
                                <div class="upload-example is-wrong">
                                    <div class="upload-example-status"><span aria-hidden="true">&times;</span> Do Not Upload</div>
                                    <img src="{{ asset('images/upload-guides/photo-casual-do-not-upload.jpg') }}" alt="Casual outdoor selfie that should not be uploaded">
                                    <p class="upload-example-caption">No selfies, casual poses, scenery, filters, or distracting backgrounds.</p>
                                </div>
                                <div class="upload-example is-correct">
                                    <div class="upload-example-status"><span aria-hidden="true">&#10003;</span> Upload This</div>
                                    <img src="{{ asset('images/upload-guides/photo-formal-upload.jpg') }}" alt="Formal front-facing ID photo on a plain white background">
                                    <p class="upload-example-caption">Formal, front-facing photo with even lighting and a plain white background.</p>
                                </div>
                            </div>
                            @if($isHealthFormCorrectionMode && $photoUploaded)
                                <div class="existing-upload-preview">
                                    <span>2x2 Student Photo already uploaded.</span>
                                    <a href="{{ $documentOpenUrl('student_photo') }}" target="_blank" rel="noopener">Open</a>
                                </div>
                            @endif
                            <input type="file" name="student_photo" class="form-control" accept=".jpg,.jpeg,.png,image/jpeg,image/png" {{ (!$isHealthFormCorrectionMode || $photoRequested) ? 'required' : '' }} {{ $photoLocked ? 'disabled' : '' }} data-upload-input data-preview-kind="image">
                            <div class="upload-preview-card" data-upload-preview aria-live="polite"></div>
                            @if($photoLocked)
                                <div class="file-locked-note">Preview only. The clinic did not request replacement for this file.</div>
                            @elseif($isHealthFormCorrectionMode && $photoRequested)
                                <div class="file-locked-note">Replacement requested by the clinic. Upload a new 2x2 photo.</div>
                            @endif
                            <small>Allowed: JPG/PNG only, max 1MB. Compress the image if needed to meet the size requirement.</small>
                        </div>
                        @if($isHealthFormCorrectionMode && $declarationRequested)
                        <div class="requirement-card">
                            <div class="requirement-card-header">
                                <strong>Declaration of Medical Information and Data Subject Consent Form</strong>
                                <span class="requirement-badge">PDF/IMG</span>
                            </div>
                            <p class="requirement-guideline">Upload the signed, clear, and readable Declaration of Medical Information and Data Subject Consent Form.</p>
                            <div class="upload-example-grid" aria-label="Declaration of Medical Information and Data Subject Consent Form upload examples">
                                <div class="upload-example is-wrong">
                                    <div class="upload-example-status"><span aria-hidden="true">&times;</span> Do Not Upload</div>
                                    <img src="{{ asset('images/upload-guides/health-declaration-do-not-upload.png') }}" alt="Unsigned, blurry, or unreadable Declaration of Medical Information and Data Subject Consent Form that should not be uploaded">
                                    <p class="upload-example-caption">Do not upload without sign, blurry, or unreadable.</p>
                                </div>
                                <div class="upload-example is-correct">
                                    <div class="upload-example-status"><span aria-hidden="true">&#10003;</span> Upload This</div>
                                    <img src="{{ asset('images/upload-guides/health-declaration-upload-this.png') }}" alt="Signed and readable Declaration of Medical Information and Data Subject Consent Form that should be uploaded">
                                    <p class="upload-example-caption">Upload the signed, clear, and readable consent form.</p>
                                </div>
                            </div>
                            @if($isHealthFormCorrectionMode && $declarationUploaded)
                                <div class="existing-upload-preview">
                                    <span>Declaration form already uploaded.</span>
                                    <a href="{{ $documentOpenUrl('health_declaration') }}" target="_blank" rel="noopener">Open</a>
                                </div>
                            @endif
                            <input type="file" name="health_declaration" class="form-control" accept=".pdf,.jpg,.jpeg,.png,application/pdf,image/jpeg,image/png" {{ ($isHealthFormCorrectionMode && $declarationRequested) ? 'required' : '' }} {{ $declarationLocked ? 'disabled' : '' }} data-requirement-file data-upload-input>
                            <div class="upload-preview-card" data-upload-preview aria-live="polite"></div>
                            @if($declarationLocked)
                                <div class="file-locked-note">Preview only. The clinic did not request replacement for this file.</div>
                            @elseif($isHealthFormCorrectionMode && $declarationRequested)
                                <div class="file-locked-note">Replacement requested by the clinic. Upload a new declaration form.</div>
                            @endif
                            <small>Optional during initial submission. Allowed: PDF, JPG, JPEG, or PNG, max 1MB.</small>
                        </div>
                        @endif
                        @php
                            $medicalCertificateRequested = $isDocumentRequested('medical_certificate');
                            $medicalCertificateUploaded = $hasExistingDocument('medical_certificate');
                            $medicalCertificateLocked = $isHealthFormCorrectionMode && !$medicalCertificateRequested && $medicalCertificateUploaded;
                            $selectedMedCertFindings = old('med_cert_findings', $prefill['med_cert_findings'] ?? '');
                        @endphp
                        @if($isHealthFormCorrectionMode && $medicalCertificateRequested)
                        <div class="requirement-card {{ old('doctor_name', $prefill['doctor_name'] ?? '') || old('med_cert_date', $prefill['med_cert_date'] ?? '') || $selectedMedCertFindings || old('med_cert_findings_details', $prefill['med_cert_findings_details'] ?? '') ? 'has-old-data' : '' }}" data-requirement-card>
                            <div class="requirement-card-header">
                                <strong>Medical Certificate</strong>
                                <span class="requirement-badge">PDF/IMG</span>
                            </div>
                            <p class="requirement-guideline">Please ensure the doctor's signature and PRC License Number are clearly visible.</p>
                            <div class="upload-example-grid" aria-label="Medical certificate upload examples">
                                <div class="upload-example is-wrong">
                                    <div class="upload-example-status"><span aria-hidden="true">&times;</span> Do Not Upload</div>
                                    <img src="{{ asset('images/upload-guides/medical-certificate-incomplete.jpg') }}" alt="Incomplete medical certificate without a physician signature or license number">
                                    <p class="upload-example-caption">Incomplete certificate without visible signature and PRC License Number.</p>
                                </div>
                                <div class="upload-example is-correct">
                                    <div class="upload-example-status"><span aria-hidden="true">&#10003;</span> Upload This</div>
                                    <img src="{{ asset('images/upload-guides/medical-certificate-complete.jpg') }}" alt="Complete medical certificate with physician signature and license number">
                                    <p class="upload-example-caption">Complete certificate with the doctor's signature and PRC License Number.</p>
                                </div>
                            </div>
                            @if($isHealthFormCorrectionMode && $medicalCertificateUploaded)
                                <div class="existing-upload-preview">
                                    <span>Medical Certificate already uploaded.</span>
                                    <a href="{{ $documentOpenUrl('medical_certificate') }}" target="_blank" rel="noopener">Open</a>
                                </div>
                            @endif
                            <input type="file" name="medical_certificate" class="form-control" accept=".pdf,.jpg,.jpeg,.png,application/pdf,image/jpeg,image/png" {{ ($isHealthFormCorrectionMode && $medicalCertificateRequested) ? 'required' : '' }} {{ $medicalCertificateLocked ? 'disabled' : '' }} data-requirement-file data-upload-input>
                            <div class="upload-preview-card" data-upload-preview aria-live="polite"></div>
                            @if($medicalCertificateLocked)
                                <div class="file-locked-note">Preview only. The clinic did not request replacement for this file.</div>
                            @elseif($isHealthFormCorrectionMode && $medicalCertificateRequested)
                                <div class="file-locked-note">Replacement requested by the clinic. Upload a new medical certificate.</div>
                            @endif
                            <small>Optional during initial submission. Allowed: PDF, JPG, JPEG, or PNG, max 1MB.</small>
                            <div class="requirement-extra">
                                <div class="form-field span-2">
                                    <label class="form-label" for="doctor_name">Doctor's Full Name</label>
                                    <input id="doctor_name" type="text" name="doctor_name" class="form-control" value="{{ old('doctor_name', $prefill['doctor_name'] ?? '') }}" maxlength="255" data-requirement-extra-field>
                                </div>
                                <div class="form-field">
                                    <label class="form-label" for="med_cert_date">Date of Certificate</label>
                                    <input id="med_cert_date" type="date" name="med_cert_date" class="form-control" value="{{ old('med_cert_date', $prefill['med_cert_date'] ?? '') }}" data-requirement-extra-field>
                                </div>
                                <div class="form-field">
                                    <label class="form-label" for="med_cert_findings">Findings</label>
                                    <div class="clinic-select-wrap" data-clinic-select>
                                        <select id="med_cert_findings" name="med_cert_findings" class="form-select clinic-select-native" data-requirement-extra-field>
                                            <option value="">Select findings</option>
                                            <option value="No Findings / Normal" {{ $selectedMedCertFindings === 'No Findings / Normal' ? 'selected' : '' }}>No Findings / Normal</option>
                                            <option value="With Findings" {{ $selectedMedCertFindings === 'With Findings' ? 'selected' : '' }}>With Findings</option>
                                            <option value="Not Sure / For Clinic Review" {{ $selectedMedCertFindings === 'Not Sure / For Clinic Review' ? 'selected' : '' }}>Not Sure / For Clinic Review</option>
                                        </select>
                                        <button type="button" class="clinic-select-display" aria-haspopup="listbox" aria-expanded="false">Select findings</button>
                                        <div class="clinic-select-menu" role="listbox" aria-label="Medical certificate findings options">
                                            <button type="button" class="clinic-select-option" data-select-value="No Findings / Normal">No Findings / Normal</button>
                                            <button type="button" class="clinic-select-option" data-select-value="With Findings">With Findings</button>
                                            <button type="button" class="clinic-select-option" data-select-value="Not Sure / For Clinic Review">Not Sure / For Clinic Review</button>
                                        </div>
                                    </div>
                                </div>
                                <div class="form-field span-2 {{ $selectedMedCertFindings === 'With Findings' ? '' : 'is-hidden' }}" id="medCertFindingsDetailsWrap">
                                    <label class="form-label" for="med_cert_findings_details">Medical Certificate Findings <span class="required">*</span></label>
                                    <textarea
                                        id="med_cert_findings_details"
                                        name="med_cert_findings_details"
                                        class="form-control"
                                        rows="3"
                                        maxlength="1000"
                                        placeholder="Type the findings written in your medical certificate."
                                        data-requirement-extra-field
                                    >{{ old('med_cert_findings_details', $prefill['med_cert_findings_details'] ?? '') }}</textarea>
                                </div>
                            </div>
                        </div>
                        @endif
                        @php
                            $xrayRequested = $isDocumentRequested('chest_xray_result');
                            $xrayUploaded = $hasExistingDocument('chest_xray_result');
                            $xrayLocked = $isHealthFormCorrectionMode && !$xrayRequested && $xrayUploaded;
                            $selectedXrayFindings = old('xray_findings', $prefill['xray_findings'] ?? '');
                        @endphp
                        @if($isHealthFormCorrectionMode && $xrayRequested)
                        <div class="requirement-card {{ old('xray_date', $prefill['xray_date'] ?? '') || $selectedXrayFindings || old('xray_findings_details', $prefill['xray_findings_details'] ?? '') ? 'has-old-data' : '' }}" data-requirement-card>
                            <div class="requirement-card-header">
                                <strong>Chest X-ray Result</strong>
                                <span class="requirement-badge">PDF/IMG</span>
                            </div>
                            <p class="requirement-guideline">Please upload the official radiologist's written report, not the actual film scanning image.</p>
                            <div class="upload-example-grid" aria-label="Chest X-ray upload examples">
                                <div class="upload-example is-wrong">
                                    <div class="upload-example-status"><span aria-hidden="true">&times;</span> Do Not Upload</div>
                                    <img src="{{ asset('images/upload-guides/xray-film-do-not-upload.jpg') }}" alt="Physical chest X-ray film that should not be uploaded">
                                    <p class="upload-example-caption">Do not upload a scan or photograph of the X-ray film.</p>
                                </div>
                                <div class="upload-example is-correct">
                                    <div class="upload-example-status"><span aria-hidden="true">&#10003;</span> Upload This</div>
                                    <img src="{{ asset('images/upload-guides/xray-written-report-upload.jpg') }}" alt="Official written radiologist report that should be uploaded">
                                    <p class="upload-example-caption">Upload the official written report containing findings and impression.</p>
                                </div>
                            </div>
                            @if($isHealthFormCorrectionMode && $xrayUploaded)
                                <div class="existing-upload-preview">
                                    <span>Chest X-ray Result already uploaded.</span>
                                    <a href="{{ $documentOpenUrl('chest_xray_result') }}" target="_blank" rel="noopener">Open</a>
                                </div>
                            @endif
                            <input type="file" name="chest_xray_result" class="form-control" accept=".pdf,.jpg,.jpeg,.png,application/pdf,image/jpeg,image/png" {{ ($isHealthFormCorrectionMode && $xrayRequested) ? 'required' : '' }} {{ $xrayLocked ? 'disabled' : '' }} data-requirement-file data-upload-input>
                            <div class="upload-preview-card" data-upload-preview aria-live="polite"></div>
                            @if($xrayLocked)
                                <div class="file-locked-note">Preview only. The clinic did not request replacement for this file.</div>
                            @elseif($isHealthFormCorrectionMode && $xrayRequested)
                                <div class="file-locked-note">Replacement requested by the clinic. Upload a new Chest X-ray Result.</div>
                            @endif
                            <small>Optional during initial submission. Allowed: PDF, JPG, JPEG, or PNG, max 1MB.</small>
                            <div class="requirement-extra">
                                <div class="form-field">
                                    <label class="form-label" for="xray_date">Date of Examination</label>
                                    <input id="xray_date" type="date" name="xray_date" class="form-control" value="{{ old('xray_date', $prefill['xray_date'] ?? '') }}" data-requirement-extra-field>
                                </div>
                                <div class="form-field">
                                    <label class="form-label" for="xray_findings">Findings</label>
                                    <div class="clinic-select-wrap" data-clinic-select>
                                        <select id="xray_findings" name="xray_findings" class="form-select clinic-select-native" data-requirement-extra-field>
                                            <option value="">Select findings</option>
                                            <option value="Normal" {{ $selectedXrayFindings === 'Normal' ? 'selected' : '' }}>Normal</option>
                                            <option value="With Findings" {{ $selectedXrayFindings === 'With Findings' ? 'selected' : '' }}>With Findings</option>
                                            <option value="Not Sure / For Clinic Review" {{ $selectedXrayFindings === 'Not Sure / For Clinic Review' ? 'selected' : '' }}>Not Sure / For Clinic Review</option>
                                        </select>
                                        <button type="button" class="clinic-select-display" aria-haspopup="listbox" aria-expanded="false">Select findings</button>
                                        <div class="clinic-select-menu" role="listbox" aria-label="Chest X-ray findings options">
                                            <button type="button" class="clinic-select-option" data-select-value="Normal">Normal</button>
                                            <button type="button" class="clinic-select-option" data-select-value="With Findings">With Findings</button>
                                            <button type="button" class="clinic-select-option" data-select-value="Not Sure / For Clinic Review">Not Sure / For Clinic Review</button>
                                        </div>
                                    </div>
                                </div>
                                <div class="form-field span-2 {{ $selectedXrayFindings === 'With Findings' ? '' : 'is-hidden' }}" id="xrayFindingsDetailsWrap">
                                    <label class="form-label" for="xray_findings_details">Chest X-ray Findings <span class="required">*</span></label>
                                    <textarea
                                        id="xray_findings_details"
                                        name="xray_findings_details"
                                        class="form-control"
                                        rows="3"
                                        maxlength="1000"
                                        placeholder="Type the findings written in your chest X-ray report."
                                        data-requirement-extra-field
                                    >{{ old('xray_findings_details', $prefill['xray_findings_details'] ?? '') }}</textarea>
                                </div>
                            </div>
                        </div>
                        @endif
                    </div>
                    <div class="btn-row">
                        <button type="button" class="btn btn-health btn-health-back" data-step-back="4">
                            <svg viewBox="0 0 24 24" aria-hidden="true">
                                <path d="m15 18-6-6 6-6"></path>
                            </svg>
                            <span>Back</span>
                        </button>
                        <button type="button" class="btn btn-health btn-health-next" data-step-next="6">
                            <span>Next</span>
                            <svg viewBox="0 0 24 24" aria-hidden="true">
                                <path d="m9 18 6-6-6-6"></path>
                            </svg>
                        </button>
                    </div>
                </div>

                <div class="step-panel {{ $startStep === 6 ? '' : 'is-hidden' }}" id="stepPanel6">
                    <h2 class="section-title step-page-title" data-title-letter="E">E-Signature</h2>
                    <p class="step-fill-note">Draw your signature or upload a clear signature image for the Declaration of Medical Information and Data Subject Consent Form.</p>
                    <input type="text" id="digital_signature_data" name="digital_signature_data" value="{{ old('digital_signature_data') }}" class="visually-hidden" data-signature-field>
                    <input type="hidden" id="digital_signature_existing" name="digital_signature_existing" value="{{ $prefill['digital_signature'] ?? '' }}">
                    <div class="esign-method-grid" aria-label="Choose your signature method">
                        <input class="esign-method-radio" type="radio" name="signature_method" id="signature_method_draw" value="draw" checked>
                        <label class="esign-method-card" for="signature_method_draw">
                            <span class="esign-method-dot" aria-hidden="true"></span>
                            <span class="esign-method-icon" aria-hidden="true">
                                <svg viewBox="0 0 24 24"><path d="M4 20c3.5-7.5 5.5-11.5 7.5-12.5 1.5-.8 3 .4 2.4 2-.8 2.2-3.6 4.1-3.2 5.2.3.8 1.7.7 3.4-.2 1.4-.8 2.4-.3 2.6.7.2.8.8 1.2 1.7.8l1.6-.7"></path><path d="m14.5 4.5 2-2 2.5 2.5-2 2"></path></svg>
                            </span>
                            <span class="esign-method-copy">
                                <strong>Draw Signature</strong>
                                <span>Draw your signature here</span>
                                <span class="esign-method-badge">Recommended</span>
                            </span>
                        </label>
                        <input class="esign-method-radio" type="radio" name="signature_method" id="signature_method_upload" value="upload">
                        <label class="esign-method-card" for="signature_method_upload">
                            <span class="esign-method-dot" aria-hidden="true"></span>
                            <span class="esign-method-icon" aria-hidden="true">
                                <svg viewBox="0 0 24 24"><path d="M12 16V4"></path><path d="m7 9 5-5 5 5"></path><path d="M20 16.5a4.5 4.5 0 0 1-4.5 4.5h-7A4.5 4.5 0 0 1 4 16.5"></path></svg>
                            </span>
                            <span class="esign-method-copy">
                                <strong>Upload Signature</strong>
                                <span>Upload an image file of your signature</span>
                            </span>
                        </label>
                    </div>
                    <div class="esign-grid esign-mode-panel" id="signatureDrawPanel">
                        <div class="esign-card">
                            <h3>Draw Signature</h3>
                            <p>Use your mouse, touchpad, or finger. You can clear and redraw anytime before saving.</p>
                            <div class="signature-pad-wrap">
                                <canvas id="digitalSignaturePad" aria-label="Draw your signature"></canvas>
                            </div>
                            <div class="esign-actions">
                                <button type="button" class="esign-secondary-btn" id="clearSignatureBtn">
                                    <svg viewBox="0 0 16 16" aria-hidden="true"><path d="M3.4 2.2 2.2 3.4 6.8 8l-4.6 4.6 1.2 1.2L8 9.2l4.6 4.6 1.2-1.2L9.2 8l4.6-4.6-1.2-1.2L8 6.8 3.4 2.2Z"></path></svg>
                                    <span>Clear</span>
                                </button>
                            </div>
                            <div class="esign-status" id="signatureDrawStatus">No drawn signature yet.</div>
                        </div>
                    </div>
                    <div class="esign-grid esign-mode-panel is-hidden" id="signatureUploadPanel">
                        <div class="esign-card">
                            <h3>Upload Signature</h3>
                            <input id="digital_signature_upload" type="file" name="digital_signature_upload" class="esign-upload-input" accept=".png,.jpg,.jpeg,image/png,image/jpeg" data-upload-input data-preview-kind="image">
                            <ol class="esign-upload-instructions">
                                <li>Upload a black PNG signature only.</li>
                                <li>Use a transparent/no-background signature file.</li>
                                <li>If the background is not removed yet, use remove.bg first, then upload the PNG file.</li>
                                <li>Maximum file size is 1MB.</li>
                            </ol>
                            <div class="upload-preview-card" data-upload-preview aria-live="polite"></div>
                            @if(!empty($prefill['digital_signature']))
                                <div class="esign-status">Existing signature is on file. Drawing or uploading a new one will replace it.</div>
                            @endif
                        </div>
                    </div>

                    <div class="guardian-signature-section" id="guardianSignatureSection" style="margin-top: 24px; padding-top: 20px; border-top: 1px dashed #d1d5db;" hidden>
                        <h3 style="font-size: 15px; font-weight: 800; color: #7f1d2d; margin-bottom: 4px;">Parent / Guardian's Signature <span class="required" style="color: #dc2626;">*</span></h3>
                        <p class="step-fill-note" style="margin-bottom: 12px;">Because you are 17 years old or below, your parent or legal guardian must also affix their signature for the Declaration and Consent Form.</p>
                        <input type="text" id="guardian_signature_data" name="guardian_signature_data" value="{{ old('guardian_signature_data') }}" class="visually-hidden" data-guardian-signature-field>
                        <div class="esign-method-grid" aria-label="Choose guardian signature method" style="margin-bottom: 14px;">
                            <input class="esign-method-radio" type="radio" name="guardian_signature_method" id="guardian_signature_method_draw" value="draw" checked>
                            <label class="esign-method-card" for="guardian_signature_method_draw">
                                <span class="esign-method-dot" aria-hidden="true"></span>
                                <span class="esign-method-icon" aria-hidden="true">
                                    <svg viewBox="0 0 24 24"><path d="M4 20c3.5-7.5 5.5-11.5 7.5-12.5 1.5-.8 3 .4 2.4 2-.8 2.2-3.6 4.1-3.2 5.2.3.8 1.7.7 3.4-.2 1.4-.8 2.4-.3 2.6.7.2.8.8 1.2 1.7.8l1.6-.7"></path><path d="m14.5 4.5 2-2 2.5 2.5-2 2"></path></svg>
                                </span>
                                <span class="esign-method-copy">
                                    <strong>Draw Guardian Signature</strong>
                                    <span>Draw signature here</span>
                                    <span class="esign-method-badge">Recommended</span>
                                </span>
                            </label>
                            <input class="esign-method-radio" type="radio" name="guardian_signature_method" id="guardian_signature_method_upload" value="upload">
                            <label class="esign-method-card" for="guardian_signature_method_upload">
                                <span class="esign-method-dot" aria-hidden="true"></span>
                                <span class="esign-method-icon" aria-hidden="true">
                                    <svg viewBox="0 0 24 24"><path d="M12 16V4"></path><path d="m7 9 5-5 5 5"></path><path d="M20 16.5a4.5 4.5 0 0 1-4.5 4.5h-7A4.5 4.5 0 0 1 4 16.5"></path></svg>
                                </span>
                                <span class="esign-method-copy">
                                    <strong>Upload Guardian Signature</strong>
                                    <span>Upload an image file (PNG/JPG) of signature</span>
                                </span>
                            </label>
                        </div>
                        <div class="esign-grid esign-mode-panel" id="guardianSignatureDrawPanel">
                            <div class="esign-card">
                                <h3>Draw Guardian Signature</h3>
                                <p>Use your mouse, touchpad, or finger. You can clear and redraw anytime before saving.</p>
                                <div class="signature-pad-wrap">
                                    <canvas id="guardianSignaturePad" aria-label="Draw guardian signature"></canvas>
                                </div>
                                <div class="esign-actions">
                                    <button type="button" class="esign-secondary-btn" id="clearGuardianSignatureBtn">
                                        <svg viewBox="0 0 16 16" aria-hidden="true"><path d="M3.4 2.2 2.2 3.4 6.8 8l-4.6 4.6 1.2 1.2L8 9.2l4.6 4.6 1.2-1.2L9.2 8l4.6-4.6-1.2-1.2L8 6.8 3.4 2.2Z"></path></svg>
                                        <span>Clear</span>
                                    </button>
                                </div>
                                <div class="esign-status" id="guardianSignatureDrawStatus">No guardian signature drawn yet.</div>
                            </div>
                        </div>
                        <div class="esign-grid esign-mode-panel is-hidden" id="guardianSignatureUploadPanel">
                            <div class="esign-card">
                                <h3>Upload Guardian Signature</h3>
                                <input id="guardian_signature_upload" type="file" name="guardian_signature_upload" class="esign-upload-input" accept=".png,.jpg,.jpeg,image/png,image/jpeg" data-upload-input data-preview-kind="image">
                                <ol class="esign-upload-instructions">
                                    <li>Upload a clear PNG or JPG image of guardian's signature.</li>
                                    <li>Maximum file size is 1MB.</li>
                                </ol>
                                <div class="upload-preview-card" data-upload-preview aria-live="polite"></div>
                            </div>
                        </div>
                    </div>

                    <div class="certify-row final-certification">
                        <input id="health_profile_certified" type="checkbox" name="health_profile_certified" value="1" required {{ old('health_profile_certified') ? 'checked' : '' }}>
                        <label for="health_profile_certified">
                            I certify that I have completely filled out all required sections of the official PUP Health Profile and that all information I provided is true and correct.
                        </label>
                    </div>
                    <div class="btn-row">
                        <button type="button" class="btn btn-health btn-health-back" data-step-back="5">
                            <svg viewBox="0 0 24 24" aria-hidden="true">
                                <path d="m15 18-6-6 6-6"></path>
                            </svg>
                            <span>Back</span>
                        </button>
                        <button type="submit" class="btn btn-health btn-health-submit">
                            <svg viewBox="0 0 24 24" aria-hidden="true">
                                <path d="M19 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11l5 5v11a2 2 0 0 1-2 2Z"></path>
                                <path d="M17 21v-8H7v8"></path>
                                <path d="M7 3v5h8"></path>
                            </svg>
                            <span data-submit-label>Save Health Profile</span>
                        </button>
                    </div>
                </div>

                <p class="privacy-note">
                    <span class="privacy-noteD">Data Privacy Notice:</span>
                    The information you provide is collected for school clinic documentation and health clearance processing only, in compliance with school data privacy requirements.
                </p>
            </form>
        </div>
    </div>

    @php
        $studentToastMessage = session('error') ?: session('success') ?: ($errors->any() ? $errors->first() : null);
        $studentToastType = (session('error') || $errors->any()) ? 'error' : 'success';
    @endphp
    <div class="student-toast-stack" aria-live="polite" aria-atomic="true">
        @if($studentToastMessage)
            <div class="student-toast is-{{ $studentToastType }}" role="{{ $studentToastType === 'error' ? 'alert' : 'status' }}" data-student-toast>
                <span class="student-toast-icon" aria-hidden="true">!</span>
                <span>
                    <strong class="student-toast-title">{{ $studentToastType === 'error' ? 'Error message' : 'Success message' }}</strong>
                    <span class="student-toast-message">{{ $studentToastMessage }}</span>
                </span>
                <button type="button" class="student-toast-close" data-student-toast-close aria-label="Dismiss message">&times;</button>
            </div>
        @endif
    </div>

    <div class="health-error-modal" id="healthErrorModal" aria-hidden="true" data-initial-message="">
        <div class="health-error-card" role="alertdialog" aria-modal="true" aria-labelledby="healthErrorTitle" aria-describedby="healthErrorMessage">
            <h3 id="healthErrorTitle">Error</h3>
            <p id="healthErrorMessage">Please complete the required field.</p>
            <button type="button" id="healthErrorContinue">Continue</button>
        </div>
    </div>

    <script>
        (function () {
            document.querySelectorAll('[data-student-toast]').forEach((toast) => {
                const closeButton = toast.querySelector('[data-student-toast-close]');
                const dismissToast = () => {
                    toast.classList.add('is-hiding');
                    window.setTimeout(() => toast.remove(), 240);
                };

                closeButton?.addEventListener('click', dismissToast);
                window.setTimeout(dismissToast, 5200);
            });

            const form = document.querySelector('form[action="{{ route('store.health.form.student') }}"]');
            const totalSteps = 6;
            const stepNames = @json($healthFormSteps);
            const stepPanels = Array.from({ length: totalSteps }, (_, index) => document.getElementById(`stepPanel${index + 1}`));
            const stepChips = [];
            const currentStepLabel = document.getElementById('currentStepLabel');
            const currentStepName = document.getElementById('currentStepName');
            const stepProgressFill = document.getElementById('stepProgressFill');
            const stepProgressPercent = document.getElementById('stepProgressPercent');
            const healthErrorModal = document.getElementById('healthErrorModal');
            const healthErrorMessage = document.getElementById('healthErrorMessage');
            const healthErrorContinue = document.getElementById('healthErrorContinue');
            const studentConsentModal = document.getElementById('studentConsentModal');
            const studentConsentPurpose = document.getElementById('studentConsentPurpose');
            const consentDynamicPurpose = document.getElementById('consentDynamicPurpose');
            const consentDynamicEndorsement = document.getElementById('consentDynamicEndorsement');
            const studentConsentCheckbox = document.getElementById('studentConsentCheckbox');
            const studentConsentAcknowledged = document.getElementById('consentAcknowledged');
            const studentConsentContinue = document.getElementById('studentConsentContinue');
            const studentConsentClose = document.getElementById('studentConsentClose');
            const studentConsentGuardianNote = document.getElementById('studentConsentGuardianNote');
            const nextToStep2Btn = document.getElementById('nextToStep2');
            const referenceInput = document.getElementById('reference_number');
            const referenceEditorInput = document.getElementById('reference_editor');
            const referencePanel = document.getElementById('referencePanel');
            const editReferenceBtn = document.getElementById('editReferenceBtn');
            const referenceDisplayValue = document.getElementById('referenceDisplayValue');
            const referenceVerifyStatus = document.getElementById('referenceVerifyStatus');
            const referenceModeRadios = Array.from(document.querySelectorAll('input[name="reference_mode_selected"]'));
            const stepNavigationButtons = Array.from(document.querySelectorAll('[data-step-next], [data-step-back]'));
            const birthdayInput = document.getElementById('birthday');
            const ageInput = document.getElementById('age');
            const illnessRadios = document.querySelectorAll('input[name="has_illness"]');
            const medicalHistoryDetails = document.getElementById('medicalHistoryDetails');
            const disabilityRadios = document.querySelectorAll('input[name="has_disability"]');
            const disabilityTypeInput = document.getElementById('disability_type');
            const disabilityTypeWrap = document.getElementById('disabilityTypeWrap');
            const pwdProofInput = document.getElementById('pwd_id_proof');
            const pwdUploadWrap = document.getElementById('pwdUploadWrap');
            const noAllergiesInput = document.getElementById('no_allergies');
            const allergyDetails = document.getElementById('allergyDetails');
            const covidVaccinatedRadios = document.querySelectorAll('input[name="covid_vaccinated"]');
            const vaccineHistoryDetails = document.getElementById('vaccineHistoryDetails');
            const vaccineFields = Array.from(document.querySelectorAll('[data-vaccine-field]'));
            const vaccineDateFields = [
                document.getElementById('first_dose_date'),
                document.getElementById('second_dose_date'),
                document.getElementById('booster_1_date'),
                document.getElementById('booster_2_date'),
            ].filter(Boolean);
            const requirementFiles = document.querySelectorAll('[data-requirement-file]');
            const clinicSelects = Array.from(document.querySelectorAll('[data-clinic-select]'));
            const courseCodeSelect = document.getElementById('course_code');
            const courseCollegeInput = document.getElementById('course_college');
            const medCertFindingsSelect = document.getElementById('med_cert_findings');
            const medCertFindingsDetailsWrap = document.getElementById('medCertFindingsDetailsWrap');
            const medCertFindingsDetails = document.getElementById('med_cert_findings_details');
            const xrayFindingsSelect = document.getElementById('xray_findings');
            const xrayFindingsDetailsWrap = document.getElementById('xrayFindingsDetailsWrap');
            const xrayFindingsDetails = document.getElementById('xray_findings_details');
            const uploadInputs = Array.from(document.querySelectorAll('[data-upload-input]'));
            const numericContactInputs = Array.from(document.querySelectorAll('[data-numeric-contact]'));
            const homeAddressInput = document.getElementById('home_address');
            const homeAddressPartInputs = Array.from(document.querySelectorAll('[data-home-address-part]'));
            const signatureCanvas = document.getElementById('digitalSignaturePad');
            const signatureDataInput = document.getElementById('digital_signature_data');
            const signatureUploadInput = document.getElementById('digital_signature_upload');
            const signatureExistingInput = document.getElementById('digital_signature_existing');
            const signatureDrawStatus = document.getElementById('signatureDrawStatus');
            const clearSignatureBtn = document.getElementById('clearSignatureBtn');
            const signatureMethodRadios = Array.from(document.querySelectorAll('input[name="signature_method"]'));
            const signatureDrawPanel = document.getElementById('signatureDrawPanel');
            const signatureUploadPanel = document.getElementById('signatureUploadPanel');
            const guardianSignatureSection = document.getElementById('guardianSignatureSection');
            const guardianSignatureCanvas = document.getElementById('guardianSignaturePad');
            const guardianSignatureDataInput = document.getElementById('guardian_signature_data');
            const guardianSignatureUploadInput = document.getElementById('guardian_signature_upload');
            const guardianSignatureDrawStatus = document.getElementById('guardianSignatureDrawStatus');
            const clearGuardianSignatureBtn = document.getElementById('clearGuardianSignatureBtn');
            const guardianSignatureMethodRadios = Array.from(document.querySelectorAll('input[name="guardian_signature_method"]'));
            const guardianSignatureDrawPanel = document.getElementById('guardianSignatureDrawPanel');
            const guardianSignatureUploadPanel = document.getElementById('guardianSignatureUploadPanel');
            const healthFormStoreUrl = @json(route('store.health.form.student'));
            let currentStep = {{ $startStep }};
            let maxVisitedStep = {{ $startStep }};
            let isSubmitting = false;
            let isReferenceValidating = false;
            let resizeSignatureCanvas = () => {};
            let resizeGuardianSignatureCanvas = () => {};
            const referenceRequiresValidation = referencePanel?.dataset.referenceRequiresValidation === 'true';
            const manualStudentNumberAllowed = referencePanel?.dataset.manualStudentModeAllowed === 'true';
            const referenceVerificationUnavailable = @json($referenceVerificationUnavailable);

            function selectedReferenceMode() {
                return 'student_number';
            }

            function syncReferenceModeUi() {
                const mode = selectedReferenceMode();
                const isStudentMode = mode === 'student_number';

                if (referenceEditorInput) {
                    referenceEditorInput.placeholder = isStudentMode ? '2020-00000-TG-0' : '0000-0000-0000';
                    referenceEditorInput.setCustomValidity('');
                }

                if (nextToStep2Btn) {
                    nextToStep2Btn.disabled = referenceVerificationUnavailable && !isStudentMode;
                }

                if (!isReferenceLocked()) {
                    if (referenceDisplayValue) {
                        referenceDisplayValue.textContent = isStudentMode ? 'Enter Student ID' : 'No Reference Received';
                    }
                    referencePanel?.classList.add('is-missing');
                    setReferenceStatus('Enter your Student ID, then click the check icon.', '');
                }
            }

            function syncHomeAddressValue() {
                if (!homeAddressInput || homeAddressPartInputs.length === 0) return;
                const addressParts = homeAddressPartInputs
                    .map((input) => input.value.trim())
                    .filter((value) => value !== '');

                homeAddressInput.value = addressParts.join(', ');
            }

            homeAddressPartInputs.forEach((input) => {
                input.addEventListener('input', syncHomeAddressValue);
                input.addEventListener('change', syncHomeAddressValue);
            });
            syncHomeAddressValue();

            function hasSignatureValue() {
                const selectedMethod = signatureMethodRadios.find((radio) => radio.checked)?.value || 'draw';
                if (selectedMethod === 'upload') {
                    return Boolean(signatureUploadInput?.files && signatureUploadInput.files.length > 0);
                }
                return Boolean((signatureDataInput?.value || '').trim());
            }

            function syncSignatureValidity() {
                if (!signatureDataInput) return;
                const selectedMethod = signatureMethodRadios.find((radio) => radio.checked)?.value || 'draw';
                const message = hasSignatureValue()
                    ? ''
                    : (selectedMethod === 'upload' ? 'Please upload your e-signature file.' : 'Please draw your e-signature.');
                signatureDataInput.setCustomValidity(message);
                if (message) {
                    signatureDataInput.dataset.validationMessage = message;
                } else {
                    delete signatureDataInput.dataset.validationMessage;
                }
            }

            function syncSignatureMethod() {
                const selectedMethod = signatureMethodRadios.find((radio) => radio.checked)?.value || 'draw';
                const isUpload = selectedMethod === 'upload';
                signatureDrawPanel?.classList.toggle('is-hidden', isUpload);
                signatureUploadPanel?.classList.toggle('is-hidden', !isUpload);

                if (isUpload) {
                    if (signatureDataInput) signatureDataInput.value = '';
                    const context = signatureCanvas?.getContext('2d');
                    context?.clearRect(0, 0, signatureCanvas.width, signatureCanvas.height);
                    if (signatureDrawStatus) signatureDrawStatus.textContent = 'No drawn signature yet.';
                } else {
                    if (signatureUploadInput) {
                        signatureUploadInput.value = '';
                        renderUploadPreview(signatureUploadInput);
                    }
                    window.requestAnimationFrame(() => resizeSignatureCanvas());
                }

                syncSignatureValidity();
            }

            function setupSignaturePad() {
                if (!signatureCanvas || !signatureDataInput) {
                    syncSignatureValidity();
                    return;
                }

                const context = signatureCanvas.getContext('2d');
                let drawing = false;
                let hasDrawing = Boolean(signatureDataInput.value);

                function applySignatureStroke() {
                    context.lineCap = 'round';
                    context.lineJoin = 'round';
                    context.lineWidth = 3.5;
                    context.strokeStyle = '#000000';
                    context.fillStyle = '#000000';
                }

                function signatureDataUrl() {
                    const exportCanvas = document.createElement('canvas');
                    exportCanvas.width = signatureCanvas.width;
                    exportCanvas.height = signatureCanvas.height;
                    const exportContext = exportCanvas.getContext('2d');
                    exportContext.drawImage(signatureCanvas, 0, 0);

                    return exportCanvas.toDataURL('image/png');
                }

                function resizeCanvas() {
                    const ratio = Math.max(window.devicePixelRatio || 1, 1);
                    const rect = signatureCanvas.getBoundingClientRect();
                    const previousData = (hasDrawing ? signatureDataUrl() : '') || (signatureDataInput.value || '');
                    signatureCanvas.width = Math.max(1, Math.floor(rect.width * ratio));
                    signatureCanvas.height = Math.max(1, Math.floor(rect.height * ratio));
                    context.setTransform(ratio, 0, 0, ratio, 0, 0);
                    applySignatureStroke();

                    if (previousData) {
                        hasDrawing = true;
                        const image = new Image();
                        image.onload = () => {
                            context.drawImage(image, 0, 0, rect.width, rect.height);
                            if (signatureDrawStatus) signatureDrawStatus.textContent = 'Drawn signature ready.';
                        };
                        image.src = previousData;
                    }
                }

                function positionFromEvent(event) {
                    const rect = signatureCanvas.getBoundingClientRect();
                    return {
                        x: event.clientX - rect.left,
                        y: event.clientY - rect.top,
                    };
                }

                function startDrawing(event) {
                    event.preventDefault();
                    signatureCanvas.setPointerCapture?.(event.pointerId);
                    drawing = true;
                    const point = positionFromEvent(event);
                    applySignatureStroke();
                    context.beginPath();
                    context.arc(point.x, point.y, 1.8, 0, Math.PI * 2);
                    context.fill();
                    context.beginPath();
                    context.moveTo(point.x, point.y);
                    hasDrawing = true;
                    signatureDataInput.value = signatureDataUrl();
                    if (signatureDrawStatus) signatureDrawStatus.textContent = 'Drawn signature ready.';
                    if (signatureUploadInput) signatureUploadInput.value = '';
                    syncSignatureValidity();
                }

                function draw(event) {
                    if (!drawing) return;
                    event.preventDefault();
                    const point = positionFromEvent(event);
                    context.lineTo(point.x, point.y);
                    context.stroke();
                    hasDrawing = true;
                    signatureDataInput.value = signatureDataUrl();
                    if (signatureDrawStatus) signatureDrawStatus.textContent = 'Drawn signature ready.';
                    if (signatureUploadInput) signatureUploadInput.value = '';
                    syncSignatureValidity();
                }

                function stopDrawing(event) {
                    if (!drawing) return;
                    drawing = false;
                    signatureCanvas.releasePointerCapture?.(event.pointerId);
                    context.closePath();
                }

                function clearSignature(clearUpload = true) {
                    context.save();
                    context.setTransform(1, 0, 0, 1, 0, 0);
                    context.clearRect(0, 0, signatureCanvas.width, signatureCanvas.height);
                    context.restore();
                    applySignatureStroke();
                    hasDrawing = false;
                    signatureDataInput.value = '';
                    if (clearUpload && signatureUploadInput) {
                        signatureUploadInput.value = '';
                        renderUploadPreview(signatureUploadInput);
                    }
                    if (signatureDrawStatus) signatureDrawStatus.textContent = 'No drawn signature yet.';
                    syncSignatureValidity();
                }

                resizeSignatureCanvas = resizeCanvas;
                resizeCanvas();
                window.addEventListener('resize', resizeCanvas);
                signatureCanvas.addEventListener('pointerdown', startDrawing);
                signatureCanvas.addEventListener('pointermove', draw);
                signatureCanvas.addEventListener('pointerup', stopDrawing);
                signatureCanvas.addEventListener('pointercancel', stopDrawing);
                signatureCanvas.addEventListener('pointerleave', stopDrawing);
                clearSignatureBtn?.addEventListener('click', clearSignature);
                signatureUploadInput?.addEventListener('change', () => {
                    if (signatureUploadInput.files && signatureUploadInput.files.length > 0) {
                        clearSignature(false);
                        if (signatureDrawStatus) signatureDrawStatus.textContent = 'Uploaded signature will be used.';
                    }
                    syncSignatureValidity();
                });
                syncSignatureValidity();
            }

            signatureMethodRadios.forEach((radio) => {
                radio.addEventListener('change', syncSignatureMethod);
            });

            function syncGuardianSignatureMethod() {
                const selectedMethod = guardianSignatureMethodRadios.find((radio) => radio.checked)?.value || 'draw';
                const isUpload = selectedMethod === 'upload';
                guardianSignatureDrawPanel?.classList.toggle('is-hidden', isUpload);
                guardianSignatureUploadPanel?.classList.toggle('is-hidden', !isUpload);

                if (isUpload) {
                    if (guardianSignatureDataInput) guardianSignatureDataInput.value = '';
                    const ctx = guardianSignatureCanvas?.getContext('2d');
                    ctx?.clearRect(0, 0, guardianSignatureCanvas.width, guardianSignatureCanvas.height);
                    if (guardianSignatureDrawStatus) guardianSignatureDrawStatus.textContent = 'No guardian signature drawn yet.';
                } else {
                    if (guardianSignatureUploadInput) {
                        guardianSignatureUploadInput.value = '';
                        renderUploadPreview(guardianSignatureUploadInput);
                    }
                    window.requestAnimationFrame(() => resizeGuardianSignatureCanvas());
                }

                syncGuardianSignatureValidity();
            }

            guardianSignatureMethodRadios.forEach((radio) => {
                radio.addEventListener('change', syncGuardianSignatureMethod);
            });

            function hasGuardianSignatureValue() {
                const selectedMethod = guardianSignatureMethodRadios.find((radio) => radio.checked)?.value || 'draw';
                if (selectedMethod === 'upload') {
                    return Boolean(guardianSignatureUploadInput?.files && guardianSignatureUploadInput.files.length > 0);
                }
                return Boolean((guardianSignatureDataInput?.value || '').trim());
            }

            function syncGuardianSignatureValidity() {
                if (!guardianSignatureDataInput) return;
                if (!isMinorStudent()) {
                    guardianSignatureDataInput.setCustomValidity('');
                    return;
                }
                const selectedMethod = guardianSignatureMethodRadios.find((radio) => radio.checked)?.value || 'draw';
                const message = hasGuardianSignatureValue()
                    ? ''
                    : (selectedMethod === 'upload' ? 'Please upload the guardian signature file.' : 'Please draw the guardian signature.');
                guardianSignatureDataInput.setCustomValidity(message);
            }

            function toggleGuardianSignatureSection() {
                const showGuardian = isMinorStudent();
                if (guardianSignatureSection) {
                    guardianSignatureSection.hidden = !showGuardian;
                }
                if (guardianSignatureDataInput) {
                    guardianSignatureDataInput.disabled = !showGuardian;
                }
                if (guardianSignatureUploadInput) {
                    guardianSignatureUploadInput.disabled = !showGuardian;
                }
                syncGuardianSignatureValidity();
                if (showGuardian) {
                    window.requestAnimationFrame(() => resizeGuardianSignatureCanvas());
                }
            }

            function setupGuardianSignaturePad() {
                if (!guardianSignatureCanvas || !guardianSignatureDataInput) {
                    syncGuardianSignatureValidity();
                    return;
                }

                const ctx = guardianSignatureCanvas.getContext('2d');
                let drawing = false;
                let hasDrawing = Boolean(guardianSignatureDataInput.value);

                function applyStroke() {
                    ctx.lineCap = 'round';
                    ctx.lineJoin = 'round';
                    ctx.lineWidth = 3.5;
                    ctx.strokeStyle = '#000000';
                    ctx.fillStyle = '#000000';
                }

                function guardianDataUrl() {
                    const ec = document.createElement('canvas');
                    ec.width = guardianSignatureCanvas.width;
                    ec.height = guardianSignatureCanvas.height;
                    const ectx = ec.getContext('2d');
                    ectx.drawImage(guardianSignatureCanvas, 0, 0);

                    return ec.toDataURL('image/png');
                }

                function resizeCanvas() {
                    const ratio = Math.max(window.devicePixelRatio || 1, 1);
                    const rect = guardianSignatureCanvas.getBoundingClientRect();
                    const previousData = (hasDrawing ? guardianDataUrl() : '') || (guardianSignatureDataInput.value || '');
                    guardianSignatureCanvas.width = Math.max(1, Math.floor(rect.width * ratio));
                    guardianSignatureCanvas.height = Math.max(1, Math.floor(rect.height * ratio));
                    ctx.setTransform(ratio, 0, 0, ratio, 0, 0);
                    applyStroke();

                    if (previousData) {
                        hasDrawing = true;
                        const image = new Image();
                        image.onload = () => {
                            ctx.drawImage(image, 0, 0, rect.width, rect.height);
                            if (guardianSignatureDrawStatus) guardianSignatureDrawStatus.textContent = 'Guardian signature ready.';
                        };
                        image.src = previousData;
                    }
                }

                function posFromEvent(event) {
                    const rect = guardianSignatureCanvas.getBoundingClientRect();
                    return { x: event.clientX - rect.left, y: event.clientY - rect.top };
                }

                function startDrawing(event) {
                    event.preventDefault();
                    guardianSignatureCanvas.setPointerCapture?.(event.pointerId);
                    drawing = true;
                    const pt = posFromEvent(event);
                    applyStroke();
                    ctx.beginPath();
                    ctx.arc(pt.x, pt.y, 1.8, 0, Math.PI * 2);
                    ctx.fill();
                    ctx.beginPath();
                    ctx.moveTo(pt.x, pt.y);
                    hasDrawing = true;
                    guardianSignatureDataInput.value = guardianDataUrl();
                    if (guardianSignatureDrawStatus) guardianSignatureDrawStatus.textContent = 'Guardian signature ready.';
                    if (guardianSignatureUploadInput) guardianSignatureUploadInput.value = '';
                    syncGuardianSignatureValidity();
                }

                function draw(event) {
                    if (!drawing) return;
                    event.preventDefault();
                    const pt = posFromEvent(event);
                    ctx.lineTo(pt.x, pt.y);
                    ctx.stroke();
                    hasDrawing = true;
                    guardianSignatureDataInput.value = guardianDataUrl();
                    if (guardianSignatureDrawStatus) guardianSignatureDrawStatus.textContent = 'Guardian signature ready.';
                    if (guardianSignatureUploadInput) guardianSignatureUploadInput.value = '';
                    syncGuardianSignatureValidity();
                }

                function stopDrawing(event) {
                    if (!drawing) return;
                    drawing = false;
                    guardianSignatureCanvas.releasePointerCapture?.(event.pointerId);
                    ctx.closePath();
                }

                function clearGuardianSignature(clearUpload = true) {
                    ctx.save();
                    ctx.setTransform(1, 0, 0, 1, 0, 0);
                    ctx.clearRect(0, 0, guardianSignatureCanvas.width, guardianSignatureCanvas.height);
                    ctx.restore();
                    applyStroke();
                    hasDrawing = false;
                    guardianSignatureDataInput.value = '';
                    if (clearUpload && guardianSignatureUploadInput) {
                        guardianSignatureUploadInput.value = '';
                        renderUploadPreview(guardianSignatureUploadInput);
                    }
                    if (guardianSignatureDrawStatus) guardianSignatureDrawStatus.textContent = 'No guardian signature drawn yet.';
                    syncGuardianSignatureValidity();
                }

                resizeGuardianSignatureCanvas = resizeCanvas;
                resizeCanvas();
                window.addEventListener('resize', resizeCanvas);
                guardianSignatureCanvas.addEventListener('pointerdown', startDrawing);
                guardianSignatureCanvas.addEventListener('pointermove', draw);
                guardianSignatureCanvas.addEventListener('pointerup', stopDrawing);
                guardianSignatureCanvas.addEventListener('pointercancel', stopDrawing);
                guardianSignatureCanvas.addEventListener('pointerleave', stopDrawing);
                clearGuardianSignatureBtn?.addEventListener('click', clearGuardianSignature);
                guardianSignatureUploadInput?.addEventListener('change', () => {
                    if (guardianSignatureUploadInput.files && guardianSignatureUploadInput.files.length > 0) {
                        clearGuardianSignature(false);
                        if (guardianSignatureDrawStatus) guardianSignatureDrawStatus.textContent = 'Uploaded guardian signature will be used.';
                    }
                    syncGuardianSignatureValidity();
                });
                syncGuardianSignatureValidity();
            }

            function isReferenceLocked() {
                return referencePanel?.dataset.referenceLocked === 'true';
            }

            function setReferenceLocked(locked) {
                if (!referencePanel) return;
                referencePanel.dataset.referenceLocked = locked ? 'true' : 'false';
            }

            function setReferenceStatus(message, statusClass = '') {
                if (!referenceVerifyStatus) return;
                referenceVerifyStatus.textContent = message;
                referenceVerifyStatus.classList.remove('is-success', 'is-error');
                if (statusClass) {
                    referenceVerifyStatus.classList.add(statusClass);
                }
            }

            function setReferenceEditor(open) {
                if (!referencePanel) return;
                referencePanel.classList.toggle('is-editing', open);
                editReferenceBtn?.setAttribute('aria-expanded', open ? 'true' : 'false');
                editReferenceBtn?.setAttribute(
                    'aria-label',
                    open ? 'Validate reference number' : (isReferenceLocked() ? 'Reference already verified' : 'Add reference number')
                );
                editReferenceBtn?.setAttribute(
                    'title',
                    open ? 'Validate reference number' : (isReferenceLocked() ? 'Reference already verified' : 'Add reference number')
                );

                if (open) {
                    window.setTimeout(() => {
                        referenceEditorInput?.focus();
                        referenceEditorInput?.select();
                    }, 40);
                }
            }

            async function validateReferenceNumber() {
                if (!referenceEditorInput || !referenceInput || isReferenceValidating) {
                    return;
                }

                const isStudentMode = selectedReferenceMode() === 'student_number';
                const normalizedReference = referenceEditorInput.value.toUpperCase().replace(isStudentMode ? /[^A-Z0-9\-_]/g : /[^A-Z0-9-]/g, '').slice(0, 20);
                referenceEditorInput.value = normalizedReference;
                referenceEditorInput.setCustomValidity('');

                const isValidReference = isStudentMode
                    ? /^\d{4}-\d{5}-[A-Z]{2}-\d+$/.test(normalizedReference)
                    : /^[A-Z0-9]+(?:-[A-Z0-9]+)+$/.test(normalizedReference);

                if (!normalizedReference || !isValidReference) {
                    const message = isStudentMode ? 'Enter a valid Student ID in the format YYYY-#####-TG-#.' : 'Enter a valid reference number.';
                    referenceEditorInput.setCustomValidity(message);
                    setReferenceStatus(message, 'is-error');
                    showValidationBubble(referenceEditorInput);
                    referenceEditorInput.focus();
                    return;
                }

                isReferenceValidating = true;
                editReferenceBtn?.setAttribute('disabled', 'disabled');
                setReferenceStatus('Checking your Student ID...');

                try {
                    const endpoint = new URL('{{ route('student.health_form.reference.validate') }}', window.location.origin);
                    endpoint.searchParams.set('reference_number', normalizedReference);
                    endpoint.searchParams.set('reference_mode_selected', selectedReferenceMode());
                    endpoint.searchParams.set('form_mode', 'student');

                    const response = await fetch(endpoint.toString(), {
                        method: 'GET',
                        headers: {
                            'Accept': 'application/json',
                            'X-Requested-With': 'XMLHttpRequest',
                        },
                        credentials: 'same-origin',
                    });

                    const payload = await response.json().catch(() => ({}));
                    if (!response.ok || !payload.success) {
                        const message = payload.message || 'Reference number could not be verified right now.';
                        if (payload.rate_limited) {
                            editReferenceBtn?.setAttribute('disabled', 'disabled');
                            referenceEditorInput.setAttribute('disabled', 'disabled');
                        }
                        referenceEditorInput.setCustomValidity(message);
                        setReferenceStatus(message, 'is-error');
                        if (!payload.rate_limited) {
                            showValidationBubble(referenceEditorInput);
                            referenceEditorInput.focus();
                        }
                        return;
                    }

                    referenceInput.value = payload.reference_number || normalizedReference;
                    referenceEditorInput.value = referenceInput.value;
                    referenceDisplayValue.textContent = referenceInput.value;
                    referencePanel?.classList.remove('is-missing');
                    setReferenceLocked(true);
                    setReferenceStatus(payload.message || (isStudentMode ? 'Student ID accepted.' : 'Reference number verified successfully.'), 'is-success');
                    referenceEditorInput.setCustomValidity('');
                    setReferenceEditor(false);
                } catch (error) {
                    referenceEditorInput.setCustomValidity('Reference number could not be verified right now.');
                    setReferenceStatus('Reference number could not be verified right now.', 'is-error');
                    showValidationBubble(referenceEditorInput);
                } finally {
                    isReferenceValidating = false;
                    editReferenceBtn?.removeAttribute('disabled');
                }
            }

            referenceModeRadios.forEach((radio) => {
                radio.addEventListener('change', () => {
                    if (!radio.checked) return;
                    setReferenceLocked(false);
                    if (referenceInput) referenceInput.value = '';
                    if (referenceEditorInput) referenceEditorInput.value = '';
                    syncReferenceModeUi();
                    setReferenceEditor(true);
                });
            });
            syncReferenceModeUi();

            function setStep(step) {
                const normalizedStep = Math.min(totalSteps, Math.max(1, Number(step) || 1));
                currentStep = normalizedStep;
                maxVisitedStep = Math.max(maxVisitedStep, normalizedStep);
                stepPanels.forEach((panel, index) => {
                    panel?.classList.toggle('is-hidden', index + 1 !== normalizedStep);
                });
                if (normalizedStep === 6) {
                    window.requestAnimationFrame(() => {
                        resizeSignatureCanvas();
                        resizeGuardianSignatureCanvas();
                    });
                }

                const progressPercent = Math.round((maxVisitedStep / totalSteps) * 100);
                const activeStepName = stepNames[normalizedStep] || '';
                if (currentStepLabel) currentStepLabel.textContent = `Step ${normalizedStep} of ${totalSteps}`;
                if (currentStepName) currentStepName.textContent = activeStepName;
                if (stepProgressFill) stepProgressFill.style.width = `${progressPercent}%`;
                if (stepProgressPercent) stepProgressPercent.textContent = `${progressPercent}% Complete`;
            }

            function validateStep(step) {
                const panel = stepPanels[step - 1];
                if (!panel) return true;
                clearValidationBubble();
                if (step === 6) {
                    syncSignatureValidity();
                    syncGuardianSignatureValidity();
                }
                const fields = Array.from(panel.querySelectorAll('input, select, textarea'))
                    .filter((field) => !field.disabled);
                const firstInvalid = fields.find((field) => !field.checkValidity());

                if (!firstInvalid) {
                    return true;
                }

                showValidationBubble(firstInvalid);
                const visibleValidationControl = firstInvalid.classList.contains('clinic-select-native')
                    ? firstInvalid.closest('[data-clinic-select]')?.querySelector('.clinic-select-display')
                    : firstInvalid;
                visibleValidationControl?.focus({ preventScroll: true });
                firstInvalid.scrollIntoView({ behavior: 'smooth', block: 'center' });
                return false;
            }

            function validateWholeForm() {
                syncHomeAddressValue();
                syncSignatureValidity();
                syncGuardianSignatureValidity();
                const fields = Array.from(form?.querySelectorAll('input, select, textarea') || [])
                    .filter((field) => !field.disabled);
                const firstInvalid = fields.find((field) => !field.checkValidity());
                if (!firstInvalid) {
                    return true;
                }

                const panel = firstInvalid.closest('.step-panel');
                const panelIndex = stepPanels.findIndex((stepPanel) => stepPanel === panel);
                if (panelIndex >= 0) {
                    setStep(panelIndex + 1);
                }
                showValidationBubble(firstInvalid);
                firstInvalid.scrollIntoView({ behavior: 'smooth', block: 'center' });
                return false;
            }

            function clearValidationBubble() {
                document.querySelectorAll('.validation-bubble').forEach((bubble) => bubble.remove());
                document.querySelectorAll('.validation-anchor').forEach((anchor) => {
                    anchor.classList.remove('validation-anchor');
                });
            }

            function validationMessageForField(field) {
                if (field.validationMessage) {
                    return field.validationMessage;
                }
                if (field.type === 'file' && field.required && field.validity?.valueMissing) {
                    const requirementName = field.closest('.requirement-card, .upload-card')
                        ?.querySelector('.requirement-card-header strong, .form-label, h3')
                        ?.textContent
                        ?.replace(/\*/g, '')
                        ?.replace(/\s+/g, ' ')
                        ?.trim();

                    return requirementName
                        ? `Please upload the required ${requirementName}.`
                        : 'Please upload the required file.';
                }
                if (field.validity?.rangeUnderflow) {
                    return field.dataset.validationMessage
                        || 'Date must be January 1, 2020 or later.';
                }
                if (field.validity?.rangeOverflow) {
                    return 'Date must not be later than December 31, 2025.';
                }
                return 'Please complete the required field.';
            }

            function showErrorModal(message) {
                if (!healthErrorModal || !healthErrorMessage) return;
                healthErrorMessage.textContent = message || 'Please complete the required field.';
                healthErrorModal.classList.add('is-visible');
                healthErrorModal.setAttribute('aria-hidden', 'false');
                healthErrorContinue?.focus({ preventScroll: true });
            }

            function hideErrorModal() {
                healthErrorModal?.classList.remove('is-visible');
                healthErrorModal?.setAttribute('aria-hidden', 'true');
            }

            healthErrorContinue?.addEventListener('click', hideErrorModal);
            healthErrorModal?.addEventListener('click', (event) => {
                if (event.target === healthErrorModal) {
                    hideErrorModal();
                }
            });
            if (healthErrorModal?.dataset.initialMessage) {
                showErrorModal(healthErrorModal.dataset.initialMessage);
            }

            function showValidationBubble(field) {
                const anchor = field.closest('.form-field, .requirement-card, .certify-row, .upload-card')
                    || field.parentElement;
                if (!anchor) return;

                anchor.classList.add('validation-anchor');
                showErrorModal(validationMessageForField(field));
            }

            function updateAgeFromBirthday() {
                if (!birthdayInput || !ageInput || !birthdayInput.value) return;
                const birthday = new Date(birthdayInput.value);
                if (Number.isNaN(birthday.getTime())) return;

                const today = new Date();
                let age = today.getFullYear() - birthday.getFullYear();
                const monthDiff = today.getMonth() - birthday.getMonth();

                if (monthDiff < 0 || (monthDiff === 0 && today.getDate() < birthday.getDate())) {
                    age--;
                }

                if (age >= 0) {
                    ageInput.value = age;
                }
            }

            function togglePwdRequirements() {
                if (!disabilityTypeInput || !pwdProofInput) return;
                const selected = document.querySelector('input[name="has_disability"]:checked');
                const isPwd = selected?.value === 'Yes';
                const isPwdFileLocked = pwdProofInput.dataset.correctionLocked === 'true';

                disabilityTypeInput.required = isPwd;
                disabilityTypeInput.disabled = !isPwd;
                pwdProofInput.required = isPwd && !isPwdFileLocked;
                pwdProofInput.disabled = !isPwd || isPwdFileLocked;
                disabilityTypeWrap?.classList.toggle('is-hidden', !isPwd);
                pwdUploadWrap?.classList.toggle('is-hidden', !isPwd);

                if (!isPwd) {
                    disabilityTypeInput.value = '';
                    pwdProofInput.value = '';
                }
            }

            function toggleIllnessDetails() {
                const hasIllness = document.querySelector('input[name="has_illness"]:checked')?.value === 'Yes';
                medicalHistoryDetails?.classList.toggle('is-hidden', !hasIllness);
                medicalHistoryDetails?.querySelectorAll('input, textarea').forEach((field) => {
                    field.disabled = !hasIllness;
                    if (!hasIllness) {
                        if (field.type === 'checkbox') {
                            field.checked = false;
                        } else {
                            field.value = '';
                        }
                    }
                });
            }

            function toggleAllergyDetails() {
                const hasNoKnownAllergies = Boolean(noAllergiesInput?.checked);
                allergyDetails?.classList.toggle('is-hidden', hasNoKnownAllergies);
                allergyDetails?.querySelectorAll('input, textarea').forEach((field) => {
                    field.disabled = hasNoKnownAllergies;
                    if (hasNoKnownAllergies) {
                        if (field.type === 'checkbox') {
                            field.checked = false;
                        } else {
                            field.value = '';
                        }
                    }
                });
            }

            function toggleVaccineHistory() {
                const isVaccinated = document.querySelector('input[name="covid_vaccinated"]:checked')?.value === 'Yes';
                vaccineHistoryDetails?.classList.toggle('is-hidden', !isVaccinated);

                vaccineFields.forEach((field) => {
                    field.disabled = !isVaccinated;
                    field.required = false;

                    if (!isVaccinated) {
                        field.value = '';
                        field.setCustomValidity('');
                        delete field.dataset.validationMessage;
                    }
                });

                syncVaccineDateRules();
            }

            function addDaysToIsoDate(dateValue, days) {
                if (!dateValue) return '';
                const date = new Date(`${dateValue}T00:00:00`);
                if (Number.isNaN(date.getTime())) return '';
                date.setDate(date.getDate() + days);
                const year = date.getFullYear();
                const month = String(date.getMonth() + 1).padStart(2, '0');
                const day = String(date.getDate()).padStart(2, '0');
                return `${year}-${month}-${day}`;
            }

            function syncVaccineDateRules() {
                const firstDoseDate = document.getElementById('first_dose_date');
                const secondDoseDate = document.getElementById('second_dose_date');
                const booster1Date = document.getElementById('booster_1_date');
                const booster2Date = document.getElementById('booster_2_date');

                const minimumSecondDoseDate = addDaysToIsoDate(firstDoseDate?.value, 14);
                const minimumBooster1Date = addDaysToIsoDate(secondDoseDate?.value, 14);
                const minimumBooster2Date = addDaysToIsoDate(booster1Date?.value, 14);

                if (secondDoseDate) {
                    secondDoseDate.min = minimumSecondDoseDate || '2021-03-01';
                    if (
                        minimumSecondDoseDate
                        && secondDoseDate.value
                        && secondDoseDate.value < minimumSecondDoseDate
                    ) {
                        secondDoseDate.dataset.validationMessage = 'The 2nd Dose must be at least 2 weeks after the 1st Dose.';
                    } else {
                        delete secondDoseDate.dataset.validationMessage;
                    }
                }

                if (booster1Date) {
                    booster1Date.min = minimumBooster1Date || '2021-03-01';
                    if (
                        minimumBooster1Date
                        && booster1Date.value
                        && booster1Date.value < minimumBooster1Date
                    ) {
                        booster1Date.dataset.validationMessage = 'Booster 1 must be at least 2 weeks after the 2nd Dose.';
                    } else {
                        delete booster1Date.dataset.validationMessage;
                    }
                }

                if (booster2Date) {
                    booster2Date.min = minimumBooster2Date || '2021-03-01';
                    if (
                        minimumBooster2Date
                        && booster2Date.value
                        && booster2Date.value < minimumBooster2Date
                    ) {
                        booster2Date.dataset.validationMessage = 'Booster 2 must be at least 2 weeks after Booster 1.';
                    } else {
                        delete booster2Date.dataset.validationMessage;
                    }
                }

                const dateUsage = new Map();
                vaccineDateFields.forEach((field) => {
                    field.setCustomValidity('');
                    if ((field !== secondDoseDate && field !== booster1Date && field !== booster2Date) || !field.dataset.validationMessage) {
                        delete field.dataset.validationMessage;
                    }

                    if (!field.disabled && field.value) {
                        if (dateUsage.has(field.value)) {
                            const message = 'Each COVID-19 dose must have a different date.';
                            field.dataset.validationMessage = message;
                            field.setCustomValidity(message);
                            const firstField = dateUsage.get(field.value);
                            firstField.dataset.validationMessage = message;
                            firstField.setCustomValidity(message);
                        } else {
                            dateUsage.set(field.value, field);
                        }
                    }
                });
            }

            function syncRequirementCard(fileInput) {
                const card = fileInput?.closest('[data-requirement-card]');
                if (!card) return;
                const hasFile = Boolean(fileInput.files && fileInput.files.length > 0);
                const hasOldData = card.classList.contains('has-old-data');
                const shouldEnableExtraFields = hasFile || hasOldData;

                card.classList.toggle('file-selected', hasFile);
                card.querySelectorAll('[data-requirement-extra-field]').forEach((field) => {
                    field.disabled = !shouldEnableExtraFields;
                });
            }

            function closeClinicSelect(wrap) {
                const display = wrap?.querySelector('.clinic-select-display');
                wrap?.classList.remove('is-open');
                display?.classList.remove('is-open');
                display?.setAttribute('aria-expanded', 'false');
            }

            function syncClinicSelect(wrap) {
                const select = wrap?.querySelector('select');
                const display = wrap?.querySelector('.clinic-select-display');
                const options = Array.from(wrap?.querySelectorAll('.clinic-select-option') || []);
                if (!select || !display) return;

                const selectedValue = select.value || '';
                const placeholder = wrap.dataset.selectPlaceholder
                    || select.options[0]?.text
                    || 'Select option';
                const selectedText = selectedValue
                    ? (select.options[select.selectedIndex]?.text || selectedValue)
                    : placeholder;

                display.textContent = selectedText;
                options.forEach((option) => {
                    option.classList.toggle('is-selected', option.dataset.selectValue === selectedValue);
                });
            }

            function syncCourseCollegeValue() {
                if (!courseCodeSelect || !courseCollegeInput) return;
                const selectedOption = courseCodeSelect.options[courseCodeSelect.selectedIndex];
                courseCollegeInput.value = selectedOption?.dataset.courseName || '';
            }

            function syncMedCertFindingsDetails() {
                if (!medCertFindingsSelect || !medCertFindingsDetails || !medCertFindingsDetailsWrap) return;
                const shouldShow = medCertFindingsSelect.value === 'With Findings';
                medCertFindingsDetailsWrap.classList.toggle('is-hidden', !shouldShow);
                medCertFindingsDetails.required = shouldShow && !medCertFindingsSelect.disabled;
                medCertFindingsDetails.disabled = !shouldShow || medCertFindingsSelect.disabled;
                if (!shouldShow) {
                    medCertFindingsDetails.value = '';
                    medCertFindingsDetails.setCustomValidity('');
                }
            }

            function syncXrayFindingsDetails() {
                if (!xrayFindingsSelect || !xrayFindingsDetails || !xrayFindingsDetailsWrap) return;
                const shouldShow = xrayFindingsSelect.value === 'With Findings';
                xrayFindingsDetailsWrap.classList.toggle('is-hidden', !shouldShow);
                xrayFindingsDetails.required = shouldShow && !xrayFindingsSelect.disabled;
                xrayFindingsDetails.disabled = !shouldShow || xrayFindingsSelect.disabled;
                if (!shouldShow) {
                    xrayFindingsDetails.value = '';
                    xrayFindingsDetails.setCustomValidity('');
                }
            }

            function initializeClinicSelect(wrap) {
                const select = wrap?.querySelector('select');
                const display = wrap?.querySelector('.clinic-select-display');
                const options = Array.from(wrap?.querySelectorAll('.clinic-select-option') || []);
                if (!select || !display) return;

                syncClinicSelect(wrap);

                display.addEventListener('click', () => {
                    const shouldOpen = !wrap.classList.contains('is-open');
                    clinicSelects.forEach((otherWrap) => {
                        if (otherWrap !== wrap) {
                            closeClinicSelect(otherWrap);
                        }
                    });
                    wrap.classList.toggle('is-open', shouldOpen);
                    display.classList.toggle('is-open', shouldOpen);
                    display.setAttribute('aria-expanded', shouldOpen ? 'true' : 'false');
                });

                options.forEach((option) => {
                    option.addEventListener('click', () => {
                        select.value = option.dataset.selectValue || '';
                        select.dispatchEvent(new Event('change', { bubbles: true }));
                        syncClinicSelect(wrap);
                        closeClinicSelect(wrap);
                    });
                });

                select.addEventListener('change', () => syncClinicSelect(wrap));
            }

            courseCodeSelect?.addEventListener('change', syncCourseCollegeValue);
            syncCourseCollegeValue();
            medCertFindingsSelect?.addEventListener('change', syncMedCertFindingsDetails);
            syncMedCertFindingsDetails();
            xrayFindingsSelect?.addEventListener('change', syncXrayFindingsDetails);
            syncXrayFindingsDetails();

            function formatFileSize(bytes) {
                if (!Number.isFinite(bytes) || bytes <= 0) {
                    return 'Selected file';
                }

                if (bytes < 1024 * 1024) {
                    return Math.max(1, Math.round(bytes / 1024)) + ' KB';
                }

                return (bytes / (1024 * 1024)).toFixed(1) + ' MB';
            }

            function renderUploadPreview(input) {
                const previewScope = input.closest('.upload-card, .requirement-card');
                const preview = previewScope?.querySelector('[data-upload-preview]');
                if (!preview) return;

                if (preview.dataset.objectUrl) {
                    URL.revokeObjectURL(preview.dataset.objectUrl);
                    preview.dataset.objectUrl = '';
                }

                const file = input.files && input.files[0] ? input.files[0] : null;
                if (!file) {
                    previewScope?.classList.remove('has-upload-preview');
                    preview.classList.remove('is-visible');
                    preview.innerHTML = '';
                    return;
                }

                const maxSize = 1 * 1024 * 1024;
                const maxSizeLabel = '1MB';

                if (file.size > maxSize) {
                    preview.innerHTML = `
                        <div class="upload-preview-error">
                            <div class="upload-preview-error-header">
                                <span class="upload-preview-error-icon">⚠️</span>
                                <span class="upload-preview-error-text">File is too large (${formatFileSize(file.size)}). Maximum size is ${maxSizeLabel}.</span>
                            </div>
                            <div class="upload-preview-error-actions">
                                <button type="button" class="upload-preview-btn" data-upload-replace>Choose Different File</button>
                            </div>
                        </div>
                    `;
                    input.setCustomValidity(`File size exceeds ${maxSizeLabel} limit`);
                    previewScope?.classList.remove('has-upload-preview');
                    preview.classList.remove('is-visible');
                    showErrorModal(`File is too large (${formatFileSize(file.size)}). Maximum size is ${maxSizeLabel}.`);

                    preview.querySelector('[data-upload-replace]')?.addEventListener('click', () => {
                        input.click();
                    });
                    return;
                }

                input.setCustomValidity('');

                const objectUrl = URL.createObjectURL(file);
                preview.dataset.objectUrl = objectUrl;
                const isImage = input.dataset.previewKind === 'image' || file.type.startsWith('image/');
                const safeName = file.name.replace(/[&<>"']/g, (char) => ({
                    '&': '&amp;',
                    '<': '&lt;',
                    '>': '&gt;',
                    '"': '&quot;',
                    "'": '&#039;',
                })[char]);

                const thumbMarkup = isImage
                    ? `<img src="${objectUrl}" alt="">`
                    : `<svg viewBox="0 0 24 24" aria-hidden="true"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"></path><path d="M14 2v6h6"></path><path d="M8 13h8"></path><path d="M8 17h5"></path></svg>`;

                preview.innerHTML = `
                    <div class="upload-preview-thumb">${thumbMarkup}</div>
                    <div class="upload-preview-body">
                        <span class="upload-preview-name">${safeName}</span>
                        <span class="upload-preview-meta">${isImage ? 'Image preview' : 'PDF document'} - ${formatFileSize(file.size)}</span>
                        <div class="upload-preview-actions">
                            <a class="upload-preview-btn" href="${objectUrl}" target="_blank" rel="noopener noreferrer">View</a>
                            <button type="button" class="upload-preview-btn" data-upload-replace>Replace</button>
                        </div>
                    </div>
                `;
                previewScope?.classList.add('has-upload-preview');
                preview.classList.add('is-visible');

                preview.querySelector('[data-upload-replace]')?.addEventListener('click', () => {
                    input.click();
                });
            }

            birthdayInput?.addEventListener('change', updateAgeFromBirthday);
            birthdayInput?.addEventListener('change', toggleGuardianSignatureSection);
            setupSignaturePad();
            setupGuardianSignaturePad();
            syncSignatureMethod();
            syncGuardianSignatureMethod();
            toggleGuardianSignatureSection();
            form?.addEventListener('input', clearValidationBubble);
            form?.addEventListener('change', clearValidationBubble);
            numericContactInputs.forEach((input) => {
                input.addEventListener('input', () => {
                    input.value = input.value.replace(/\D/g, '');
                });
            });
            illnessRadios.forEach((radio) => {
                radio.addEventListener('change', toggleIllnessDetails);
            });
            disabilityRadios.forEach((radio) => {
                radio.addEventListener('change', togglePwdRequirements);
            });
            noAllergiesInput?.addEventListener('change', toggleAllergyDetails);
            covidVaccinatedRadios.forEach((radio) => {
                radio.addEventListener('change', toggleVaccineHistory);
            });
            vaccineDateFields.forEach((field) => {
                field.addEventListener('change', syncVaccineDateRules);
                field.addEventListener('input', syncVaccineDateRules);
            });
            requirementFiles.forEach((fileInput) => {
                syncRequirementCard(fileInput);
                fileInput.addEventListener('change', () => {
                    syncRequirementCard(fileInput);
                    syncMedCertFindingsDetails();
                    syncXrayFindingsDetails();
                });
            });
            clinicSelects.forEach(initializeClinicSelect);
            uploadInputs.forEach((input) => {
                renderUploadPreview(input);
                input.addEventListener('change', () => renderUploadPreview(input));
            });
            editReferenceBtn?.addEventListener('click', () => {
                if (!referenceRequiresValidation) {
                    return;
                }
                if (isReferenceLocked()) {
                    setReferenceStatus('Student ID is ready for use inside the clinic system.', 'is-success');
                    return;
                }

                if (referencePanel?.classList.contains('is-editing')) {
                    validateReferenceNumber();
                    return;
                }

                clearValidationBubble();
                setReferenceStatus('Enter your Student ID, then click the check icon.');
                setReferenceEditor(true);
            });
            referenceEditorInput?.addEventListener('input', () => {
                referenceEditorInput.value = referenceEditorInput.value.toUpperCase().replace(/[^A-Z0-9-]/g, '').slice(0, 20);
                referenceEditorInput.setCustomValidity('');
                if (!isReferenceLocked()) {
                    setReferenceStatus('Enter your Student ID, then click the check icon.');
                }
            });
            document.addEventListener('click', (event) => {
                clinicSelects.forEach((wrap) => {
                    if (!wrap.contains(event.target)) {
                        closeClinicSelect(wrap);
                    }
                });
            });
            togglePwdRequirements();
            toggleVaccineHistory();

            const maroonFields = Array.from(document.querySelectorAll('.field-maroon'));

            function syncMaroonFieldState(field) {
                if (!field) return;
                const value = typeof field.value === 'string' ? field.value.trim() : '';
                field.classList.toggle('is-filled', value !== '');
            }

            maroonFields.forEach((field) => {
                syncMaroonFieldState(field);
                field.addEventListener('input', () => syncMaroonFieldState(field));
                field.addEventListener('change', () => syncMaroonFieldState(field));
            });

            function isMinorStudent() {
                const birthday = birthdayInput?.value ? new Date(`${birthdayInput.value}T00:00:00`) : null;
                if (!birthday || Number.isNaN(birthday.getTime())) return false;
                const today = new Date();
                let age = today.getFullYear() - birthday.getFullYear();
                if (today.getMonth() < birthday.getMonth() || (today.getMonth() === birthday.getMonth() && today.getDate() < birthday.getDate())) age -= 1;
                return age < 18;
            }

            function closeStudentConsent() {
                if (!studentConsentModal) return;
                studentConsentModal.hidden = true;
                document.body.style.overflow = '';
            }

            function resetStudentConsent() {
                if (studentConsentAcknowledged) studentConsentAcknowledged.value = '';
                if (studentConsentCheckbox) studentConsentCheckbox.checked = false;
            }

            function updateConsentDynamicText() {
                const selected = studentConsentPurpose?.value || '';
                if (selected === 'On-the-Job Training (OJT)') {
                    if (consentDynamicPurpose) consentDynamicPurpose.textContent = 'On-the-Job Training (OJT)';
                    if (consentDynamicEndorsement) consentDynamicEndorsement.textContent = 'On-the-Job Training (OJT)';
                } else if (selected === 'Student') {
                    if (consentDynamicPurpose) consentDynamicPurpose.textContent = 'enrolled student';
                    if (consentDynamicEndorsement) consentDynamicEndorsement.textContent = 'enrolment as a student';
                } else {
                    if (consentDynamicPurpose) consentDynamicPurpose.textContent = '[Select Purpose]';
                    if (consentDynamicEndorsement) consentDynamicEndorsement.textContent = '[Select Purpose]';
                }
            }

            studentConsentPurpose?.addEventListener('change', updateConsentDynamicText);

            function openStudentConsent() {
                if (!studentConsentModal) return;
                studentConsentGuardianNote?.toggleAttribute('hidden', !isMinorStudent());
                updateConsentDynamicText();
                studentConsentModal.hidden = false;
                document.body.style.overflow = 'hidden';
                if (!studentConsentPurpose?.value) {
                    studentConsentPurpose?.focus();
                } else {
                    studentConsentCheckbox?.focus();
                }
            }

            studentConsentClose?.addEventListener('click', closeStudentConsent);
            studentConsentContinue?.addEventListener('click', () => {
                if (!studentConsentPurpose?.value) {
                    showError('Please select the Purpose of your Medical Clearance before proceeding.');
                    studentConsentPurpose?.focus();
                    return;
                }
                if (!studentConsentCheckbox?.checked) {
                    showError('Please confirm that you have read and agree to the consent form.');
                    return;
                }
                if (studentConsentAcknowledged) studentConsentAcknowledged.value = '1';
                closeStudentConsent();
                setStep(3);
                stepPanels[2]?.scrollIntoView({ behavior: 'smooth', block: 'start' });
            });

            nextToStep2Btn?.addEventListener('click', () => {
                const normalizedReference = (referenceInput?.value || '').trim();

                if (referenceInput) {
                    referenceInput.value = normalizedReference;
                    referenceInput.setCustomValidity('');
                }

                if (normalizedReference === '') {
                    if (referenceInput) {
                        const isStudentMode = selectedReferenceMode() === 'student_number';
                        referenceInput.setCustomValidity('Student ID is required before continuing.');
                        setReferenceStatus('Enter and accept your Student ID before continuing.', 'is-error');
                        showValidationBubble(referenceInput);
                    }
                    return;
                }

                if (!validateStep(1)) {
                    return;
                }
                setStep(2);
                stepPanels[1]?.scrollIntoView({ behavior: 'smooth', block: 'start' });
            });
            stepNavigationButtons.forEach((button) => {
                button.addEventListener('click', () => {
                    const nextStep = button.dataset.stepNext ? Number(button.dataset.stepNext) : null;
                    const backStep = button.dataset.stepBack ? Number(button.dataset.stepBack) : null;

                    if (nextStep && !validateStep(currentStep)) {
                        return;
                    }

                    const targetStep = nextStep || backStep;
                    if (!targetStep) return;
                    if (backStep === 2 && currentStep === 3) {
                        resetStudentConsent();
                    }
                    if (currentStep === 2 && targetStep === 3 && !studentConsentAcknowledged?.value) {
                        openStudentConsent();
                        return;
                    }
                    setStep(targetStep);
                    stepPanels[targetStep - 1]?.scrollIntoView({ behavior: 'smooth', block: 'start' });
                });
            });
            form?.addEventListener('submit', (event) => {
                syncHomeAddressValue();

                if (event.submitter?.hasAttribute('data-testing-skip')) {
                    return;
                }

                if (isSubmitting) {
                    return;
                }

                if (currentStep < 5) {
                    event.preventDefault();
                    if (!validateStep(currentStep)) {
                        return;
                    }
                    setStep(currentStep + 1);
                    stepPanels[currentStep - 1]?.scrollIntoView({ behavior: 'smooth', block: 'start' });
                    return;
                }

                if (currentStep < totalSteps) {
                    event.preventDefault();
                    if (!validateStep(currentStep)) {
                        return;
                    }
                    setStep(currentStep + 1);
                    stepPanels[currentStep - 1]?.scrollIntoView({ behavior: 'smooth', block: 'start' });
                    return;
                }

                if (!validateWholeForm()) {
                    event.preventDefault();
                    return;
                }

                event.preventDefault();
                isSubmitting = true;
                const submitButtons = form.querySelectorAll('button[type="submit"], button[type="button"], a.btn');
                submitButtons.forEach((btn) => {
                    btn.setAttribute('aria-disabled', 'true');
                    btn.style.pointerEvents = 'none';
                    btn.style.opacity = '0.72';
                });
                event.submitter?.querySelector('[data-submit-label]')?.replaceChildren('Saving...');

                form.action = healthFormStoreUrl;
                form.method = 'POST';
                form.enctype = 'multipart/form-data';
                form.submit();
            });

            updateAgeFromBirthday();
            toggleIllnessDetails();
            togglePwdRequirements();
            toggleAllergyDetails();
            setStep(currentStep);
        })();
    </script>

    @include('partials.student_voice_input_support')
    @include('partials.system_footer')
</body>
</html>
