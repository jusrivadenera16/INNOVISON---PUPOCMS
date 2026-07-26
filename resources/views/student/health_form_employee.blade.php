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
        #employeeSignaturePad {
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
        #employeePastMedicalOthersWrap.is-hidden,
        #employeeHospitalizationDetailsWrap.is-hidden,
        #employeeSurgeryDetailsWrap.is-hidden,
        #employeeDisabilityTypeWrap.is-hidden,
        #employeePwdRequirementCard.is-hidden,
        #medCertFindingsDetailsWrap.is-hidden,
        #xrayFindingsDetailsWrap.is-hidden {
            display: none;
        }

        .step-panel.is-hidden {
            display: none;
        }

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

        .employee-personal-stack {
            display: grid;
            gap: 12px;
        }

        .employee-personal-row {
            display: grid;
            grid-template-columns: repeat(2, minmax(0, 1fr));
            gap: 12px;
        }

        .employee-personal-row.three {
            grid-template-columns: repeat(3, minmax(0, 1fr));
        }

        .employee-personal-row.four {
            grid-template-columns: repeat(4, minmax(0, 1fr));
        }

        .employee-personal-row.single {
            grid-template-columns: minmax(0, 1fr);
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

        .employee-personal-stack .form-field .form-control:not([readonly]),
        .employee-personal-stack .form-field .form-select {
            border: 1.5px solid rgba(127, 29, 45, 0.52);
            background: linear-gradient(180deg, #fffafb 0%, #fff6f7 100%);
            box-shadow:
                0 8px 18px rgba(127, 29, 45, 0.06),
                inset 0 1px 0 rgba(255,255,255,0.82);
            border-radius: 12px;
            min-height: 46px;
            padding: 10px 12px;
        }

        .employee-personal-stack .form-field .form-control:not([readonly]):focus,
        .employee-personal-stack .form-field .form-select:focus {
            border: 1.5px solid var(--clinic-maroon);
            background: linear-gradient(180deg, #fffafb 0%, #fff6f7 100%);
            box-shadow:
                0 0 0 0.18rem rgba(127, 29, 45, 0.12),
                0 10px 22px rgba(127, 29, 45, 0.10);
        }

        .employee-personal-stack .clinic-select-wrap .clinic-select-display {
            border: 1.5px solid rgba(127, 29, 45, 0.52);
            border-radius: 18px;
            min-height: 50px;
            background: linear-gradient(180deg, #fffafb 0%, #fff6f7 100%);
        }

        .employee-personal-stack .clinic-select-wrap.is-open .clinic-select-display,
        .employee-personal-stack .clinic-select-wrap .clinic-select-display:focus {
            border-color: var(--clinic-maroon);
            box-shadow:
                0 0 0 0.18rem rgba(127, 29, 45, 0.12),
                0 10px 22px rgba(127, 29, 45, 0.10);
        }

        .employee-personal-stack .clinic-select-option.is-selected {
            color: #ffffff;
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

        .form-field .form-control::placeholder {
            color: rgba(100, 116, 139, 0.35);
            font-weight: 600;
            opacity: 1;
        }

        .field-hint {
            margin-top: 5px;
            color: rgba(100, 116, 139, 0.68);
            font-size: 0.72rem;
            font-weight: 700;
            line-height: 1.25;
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
            .personal-identity-grid,
            .employee-personal-row,
            .employee-personal-row.three,
            .employee-personal-row.four,
            .employee-personal-row.single {
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

            #digitalSignaturePad {
                height: 160px;
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

        .checkbox-grid {
            display: grid;
            grid-template-columns: repeat(4, minmax(0, 1fr));
            gap: 10px;
            margin-bottom: 16px;
        }

        .checkbox-card {
            display: flex;
            align-items: center;
            gap: 10px;
            min-height: 48px;
            padding: 11px 12px;
            border: 1px solid rgba(127, 29, 45, 0.18);
            border-radius: 12px;
            background: rgba(255, 255, 255, 0.88);
            color: #70131b;
            font-weight: 850;
            cursor: pointer;
        }

        .checkbox-card input {
            width: 18px;
            height: 18px;
            accent-color: var(--clinic-maroon);
            flex: 0 0 auto;
        }

        .checkbox-card:hover {
            border-color: rgba(250, 204, 21, 0.84);
            box-shadow: 0 10px 24px rgba(250, 204, 21, 0.14);
        }

        .checkbox-card span {
            color: #70131b;
            font-size: 1rem;
            font-weight: 600;
            line-height: 1.25;
        }

        @media (max-width: 860px) {
            .checkbox-grid {
                grid-template-columns: repeat(2, minmax(0, 1fr));
            }
        }

        @media (max-width: 560px) {
            .checkbox-grid {
                grid-template-columns: 1fr;
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
                $employeeValue = fn (string $field, $fallback = '') => old($field, data_get($employeeProfile ?? null, $field, data_get($employeePrefill ?? [], $field, $fallback)));
                $employeeCheckedValues = fn (string $field) => collect(old($field, data_get($employeeProfile ?? null, $field, [])))->filter()->values()->all();
                $employeeName = trim((string) data_get($employeePrefill ?? [], 'first_name') . ' ' . (string) data_get($employeePrefill ?? [], 'middle_name') . ' ' . (string) data_get($employeePrefill ?? [], 'last_name'));
                $employeeName = trim(preg_replace('/\s+/', ' ', $employeeName));
                if ($employeeName === '') {
                    $employeeName = trim((string) ($displayName ?? ''));
                }
                if ($employeeName === '') {
                    $employeeName = trim(implode(' ', array_filter([
                        $user->first_name ?? '',
                        $user->middle_name ?? '',
                        $user->last_name ?? '',
                    ])));
                }
                $employeeErrorGroups = [
                    1 => ['first_name', 'last_name', 'street_address', 'barangay', 'city_municipality', 'province', 'contact_no', 'emergency_contact_person', 'emergency_contact_no', 'form_date', 'office', 'age', 'sex', 'civil_status', 'birthday'],
                    2 => ['past_medical_history', 'past_medical_history_others', 'previous_hospitalization', 'previous_hospitalization_details', 'operation_surgery', 'operation_surgery_details', 'allergies'],
                    3 => ['family_history', 'family_history_others'],
                    4 => ['cigarette_smoking', 'alcohol_drinking', 'traveled_abroad', 'has_disability', 'disability_type'],
                    5 => ['working_impression', 'fit_status'],
                    6 => ['employee_signature', 'uploaded_signature', 'employee_health_profile_certified'],
                ];
                $startStep = collect($employeeErrorGroups)
                    ->search(fn ($fields) => collect($fields)->contains(fn ($field) => $errors->has($field)));
                $startStep = $startStep ?: 1;
                $healthFormSteps = [
                    1 => 'Personal Information',
                    2 => 'Past Medical History',
                    3 => 'Family History',
                    4 => 'Personal History',
                    5 => 'Requirements',
                    6 => 'E-Signature',
                ];
                $selectedEmployeeCourse = old('course_college', $employeeValue('course_college', $user->course ?? ''));
                $selectedPastMedicalHistory = $employeeCheckedValues('past_medical_history');
                $selectedPreviousHospitalization = old('previous_hospitalization', data_get($employeeProfile ?? null, 'previous_hospitalization') ? '1' : '0');
                $selectedOperationSurgery = old('operation_surgery', data_get($employeeProfile ?? null, 'operation_surgery') ? '1' : '0');
                $selectedCigaretteSmoking = old('cigarette_smoking', data_get($employeeProfile ?? null, 'cigarette_smoking') ? '1' : '0');
                $selectedAlcoholDrinking = old('alcohol_drinking', data_get($employeeProfile ?? null, 'alcohol_drinking') ? '1' : '0');
                $selectedTraveledAbroad = old('traveled_abroad', data_get($employeeProfile ?? null, 'traveled_abroad') ? '1' : '0');
                $selectedEmployeePwd = old('has_disability', data_get($employeeProfile ?? null, 'has_disability') ? '1' : '0');
                $employeeCourseOptions = collect($employeeCourseOptions ?? [])
                    ->filter(fn ($courseOption) => is_array($courseOption))
                    ->values()
                    ->all();
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
                            <span id="currentStepName">{{ $healthFormSteps[$startStep] ?? 'Personal Information' }}</span>
                        </div>
                        <div class="step-progress-track" aria-hidden="true">
                            <div class="step-progress-fill" id="stepProgressFill" style="width: {{ round(($startStep / count($healthFormSteps)) * 100) }}%;"></div>
                        </div>
                        <div class="step-progress-percent" id="stepProgressPercent">{{ round(($startStep / count($healthFormSteps)) * 100) }}% Complete</div>
                    </div>
                    <div class="step-form-title-card" aria-label="Current form">
                        <strong>Health Examination Record</strong>
                    </div>
                </div>
            </div>
            <div class="stepper-spacer"></div>

            <form action="{{ route('store.health.form.employee') }}" method="POST" enctype="multipart/form-data" id="employeeHealthForm">
                @csrf

                @php
                    $streetFallback = $employeeValue('street_address');
                    $barangayFallback = $employeeValue('barangay');
                    $cityFallback = $employeeValue('city_municipality');
                    $provinceFallback = $employeeValue('province');
                @endphp
                <div class="step-panel {{ $startStep === 1 ? '' : 'is-hidden' }}" id="stepPanel1">
                    <div style="display:flex;align-items:flex-start;justify-content:space-between;gap:14px;flex-wrap:wrap;">
                        <h2 class="section-title step-page-title" data-title-letter="P" style="margin-bottom:0;">Personal Information</h2>
                        <div class="readonly-item" style="min-width:160px;text-align:right;">
                            <small>Date</small>
                            <strong>{{ now()->format('M d, Y') }}</strong>
                        </div>
                    </div>
                    <p class="step-fill-note">Complete your identity, department, and emergency contact details.</p>
                    <input id="form_date" type="hidden" name="form_date" value="{{ now()->toDateString() }}">
                    <div class="employee-personal-stack">
                        <div class="employee-personal-row three">
                            <div class="form-field">
                                <label class="form-label" for="first_name">First Name <span class="required">*</span></label>
                                <input id="first_name" type="text" name="first_name" class="form-control" value="{{ $employeeValue('first_name', $user->first_name ?? '') }}" readonly required>
                            </div>
                            <div class="form-field">
                                <label class="form-label" for="middle_name">Middle Name</label>
                                <input id="middle_name" type="text" name="middle_name" class="form-control" value="{{ $employeeValue('middle_name', $user->middle_name ?? '') }}" placeholder="N/A if not applicable" readonly>
                                <small class="field-hint"></small>
                            </div>
                            <div class="form-field">
                                <label class="form-label" for="last_name">Last Name <span class="required">*</span></label>
                                <input id="last_name" type="text" name="last_name" class="form-control" value="{{ $employeeValue('last_name', $user->last_name ?? '') }}" readonly required>
                            </div>
                        </div>
                        <div class="employee-personal-row single">
                            <div class="form-field">
                                <label class="form-label" for="employee_email">Email Address</label>
                                <input id="employee_email" type="email" class="form-control" value="{{ old('employee_email', data_get($employeePrefill ?? [], 'email', $user->email ?? '')) }}" readonly>
                            </div>
                        </div>
                        <div class="employee-personal-row">
                            <div class="form-field">
                                <label class="form-label" for="employee_number">Employee Number</label>
                                <input id="employee_number" type="text" name="employee_number" class="form-control" value="{{ $employeeValue('employee_number', $user->employee_number ?? '') }}">
                                <small class="field-hint">Example: FA001TG2026</small>
                            </div>
                            <div class="form-field">
                                <label class="form-label" for="office">College / Department <span class="required">*</span></label>
                                <input id="office" type="text" name="office" class="form-control" value="{{ $employeeValue('office') }}" required>
                                <small class="field-hint">Example: Medical Services Department</small>
                            </div>
                        </div>
                        <div class="employee-personal-row">
                            <div class="form-field">
                                <label class="form-label" for="birthday">Date of Birth <span class="required">*</span></label>
                                <input id="birthday" type="date" name="birthday" class="form-control" value="{{ $employeeValue('birthday', $user->DOB ?? '') }}" required>
                            </div>
                            <div class="form-field">
                                <label class="form-label" for="age">Age <span class="required">*</span></label>
                                <input id="age" type="number" name="age" class="form-control" value="{{ $employeeValue('age') }}" min="15" max="100" readonly required>
                                <small class="field-hint">Automatically calculated from Date of Birth.</small>
                            </div>
                        </div>
                        <div class="employee-personal-row">
                            <div class="form-field">
                                <label class="form-label" for="civil_status">Civil Status <span class="required">*</span></label>
                                <div class="clinic-select-wrap" data-clinic-select data-select-placeholder="Select civil status">
                                    <select id="civil_status" name="civil_status" class="form-select clinic-select-native" required>
                                        <option value="" {{ $employeeValue('civil_status') === '' ? 'selected' : '' }} disabled>Select civil status</option>
                                        @foreach(['Single', 'Married', 'Widowed', 'Separated'] as $status)
                                            <option value="{{ $status }}" {{ $employeeValue('civil_status') === $status ? 'selected' : '' }}>{{ $status }}</option>
                                        @endforeach
                                    </select>
                                    <button type="button" class="clinic-select-display" aria-haspopup="listbox" aria-expanded="false">Select civil status</button>
                                    <div class="clinic-select-menu" role="listbox" aria-label="Civil status options">
                                        @foreach(['Single', 'Married', 'Widowed', 'Separated'] as $status)
                                            <button type="button" class="clinic-select-option" data-select-value="{{ $status }}">{{ $status }}</button>
                                        @endforeach
                                    </div>
                                </div>
                            </div>
                            <div class="form-field">
                                <label class="form-label" for="sex">Sex <span class="required">*</span></label>
                                <div class="clinic-select-wrap" data-clinic-select data-select-placeholder="Select sex">
                                    <select id="sex" name="sex" class="form-select clinic-select-native" required>
                                        <option value="" {{ $employeeValue('sex', $user->gender ?? '') === '' ? 'selected' : '' }} disabled>Select sex</option>
                                        @foreach(['Male', 'Female'] as $sexOption)
                                            <option value="{{ $sexOption }}" {{ $employeeValue('sex', $user->gender ?? '') === $sexOption ? 'selected' : '' }}>{{ $sexOption }}</option>
                                        @endforeach
                                    </select>
                                    <button type="button" class="clinic-select-display" aria-haspopup="listbox" aria-expanded="false">Select sex</button>
                                    <div class="clinic-select-menu" role="listbox" aria-label="Sex options">
                                        @foreach(['Male', 'Female'] as $sexOption)
                                            <button type="button" class="clinic-select-option" data-select-value="{{ $sexOption }}">{{ $sexOption }}</button>
                                        @endforeach
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="employee-personal-row single">
                            <div class="form-field">
                                <label class="form-label" for="course_college">Course / College</label>
                                <div class="clinic-select-wrap course-select-wrap" data-clinic-select data-select-placeholder="Select course or Not Applicable">
                                    <select id="course_college" name="course_college" class="form-select clinic-select-native">
                                        <option value="">Select course or Not Applicable</option>
                                        @foreach($employeeCourseOptions as $courseOption)
                                            @php($courseValue = $courseOption['name'] ?? $courseOption['label'] ?? $courseOption['code'] ?? '')
                                            <option value="{{ $courseValue }}" {{ $selectedEmployeeCourse === $courseValue ? 'selected' : '' }}>{{ $courseOption['label'] ?? $courseValue }}</option>
                                        @endforeach
                                    </select>
                                    <button type="button" class="clinic-select-display" aria-haspopup="listbox" aria-expanded="false">Select course or Not Applicable</button>
                                    <div class="clinic-select-menu" role="listbox" aria-label="Course or college options">
                                        @foreach($employeeCourseOptions as $courseOption)
                                            @php($courseValue = $courseOption['name'] ?? $courseOption['label'] ?? $courseOption['code'] ?? '')
                                            <button type="button" class="clinic-select-option" data-select-value="{{ $courseValue }}">{{ $courseOption['label'] ?? $courseValue }}</button>
                                        @endforeach
                                    </div>
                                </div>
                                <small class="field-hint">Example: Bachelor of Science in Information Technology</small>
                            </div>
                        </div>
                        <div class="employee-personal-row">
                            <div class="form-field">
                                <label class="form-label" for="contact_no">Contact Number <span class="required">*</span></label>
                                <input id="contact_no" type="text" name="contact_no" class="form-control" value="{{ $employeeValue('contact_no', $user->contact_no ?? '') }}" maxlength="20" required>
                                <small class="field-hint">Example: 09123456789</small>
                            </div>
                            <div class="form-field">
                                <label class="form-label" for="school_year">School Year</label>
                                <input id="school_year" type="text" name="school_year" class="form-control" value="{{ old('school_year', $employeeValue('school_year', $user->year ?? '')) }}">
                                <small class="field-hint">Example: 2026-2027</small>
                            </div>
                        </div>
                        <div class="employee-personal-row four">
                            <div class="form-field">
                                <label class="form-label" for="street_address">Street / House No. <span class="required">*</span></label>
                                <input id="street_address" type="text" name="street_address" class="form-control" value="{{ old('street_address', $streetFallback) }}" required>
                                <small class="field-hint">Example: 123 Mabini St.</small>
                            </div>
                            <div class="form-field">
                                <label class="form-label" for="barangay">Barangay <span class="required">*</span></label>
                                <input id="barangay" type="text" name="barangay" class="form-control" value="{{ old('barangay', $barangayFallback) }}" required>
                                <small class="field-hint">Example: Brgy. Central</small>
                            </div>
                            <div class="form-field">
                                <label class="form-label" for="city_municipality">City / Municipality <span class="required">*</span></label>
                                <input id="city_municipality" type="text" name="city_municipality" class="form-control" value="{{ old('city_municipality', $cityFallback) }}" required>
                                <small class="field-hint">Example: Taguig City</small>
                            </div>
                            <div class="form-field">
                                <label class="form-label" for="province">Province <span class="required">*</span></label>
                                <input id="province" type="text" name="province" class="form-control" value="{{ old('province', $provinceFallback) }}" required>
                                <small class="field-hint">Example: Metro Manila</small>
                            </div>
                        </div>
                        <div class="employee-personal-row">
                            <div class="form-field">
                                <label class="form-label" for="emergency_contact_person">Contact Person in Case of Emergency <span class="required">*</span></label>
                                <input id="emergency_contact_person" type="text" name="emergency_contact_person" class="form-control" value="{{ $employeeValue('emergency_contact_person') }}" required>
                                <small class="field-hint">Example: Maria Dela Cruz</small>
                            </div>
                            <div class="form-field">
                                <label class="form-label" for="emergency_contact_no">Contact Person Contact Number <span class="required">*</span></label>
                                <input id="emergency_contact_no" type="text" name="emergency_contact_no" class="form-control" value="{{ $employeeValue('emergency_contact_no') }}" maxlength="20" required>
                                <small class="field-hint">Example: 09123456789</small>
                            </div>
                        </div>
                    </div>
                    <div class="btn-row">
                        <button type="button" class="btn btn-health btn-health-next" data-step-next="2">
                            <span>Next</span>
                            <svg viewBox="0 0 24 24" aria-hidden="true"><path d="m9 18 6-6-6-6"></path></svg>
                        </button>
                    </div>
                </div>

                <div class="step-panel {{ $startStep === 2 ? '' : 'is-hidden' }}" id="stepPanel2">
                    <h2 class="section-title step-page-title" data-title-letter="M">Past Medical History</h2>
                    <p class="step-fill-note">Select all conditions that apply and provide details where needed.</p>
                    <h3 class="section-title" style="font-size:1rem;margin-bottom:10px;">Childhood Illness</h3>
                    @php($pastMedicalItems = ['Asthma', 'Heart Disease', 'Seizure Disorder', 'Others', 'Chicken Pox', 'Measles', 'Hypertension'])
                    <div class="checkbox-grid">
                        @foreach($pastMedicalItems as $item)
                            <label class="checkbox-card">
                                <input type="checkbox" name="past_medical_history[]" value="{{ $item }}" {{ in_array($item, $selectedPastMedicalHistory, true) ? 'checked' : '' }} @if($item === 'Others') id="employeePastMedicalOthersToggle" @endif>
                                <span>{{ $item }}</span>
                            </label>
                        @endforeach
                    </div>
                    <div class="form-field mt-3 {{ in_array('Others', $selectedPastMedicalHistory, true) ? '' : 'is-hidden' }}" id="employeePastMedicalOthersWrap">
                        <label class="form-label" for="past_medical_history_others">Other Childhood Illness <span class="required">*</span></label>
                        <input id="past_medical_history_others" type="text" name="past_medical_history_others" class="form-control" value="{{ $employeeValue('past_medical_history_others') }}">
                    </div>

                    <div class="form-grid personal-form-grid mt-3">
                        <div class="form-field">
                            <label class="form-label">Previous Hospitalization <span class="required">*</span></label>
                            <div class="pwd-toggle">
                                <input class="pwd-radio" type="radio" name="previous_hospitalization" id="employee_hospitalization_no" value="0" required {{ (string) $selectedPreviousHospitalization !== '1' ? 'checked' : '' }}>
                                <label class="pwd-option" for="employee_hospitalization_no">No</label>
                                <input class="pwd-radio" type="radio" name="previous_hospitalization" id="employee_hospitalization_yes" value="1" {{ (string) $selectedPreviousHospitalization === '1' ? 'checked' : '' }}>
                                <label class="pwd-option" for="employee_hospitalization_yes">Yes</label>
                            </div>
                            <div class="form-field mt-3 {{ (string) $selectedPreviousHospitalization === '1' ? '' : 'is-hidden' }}" id="employeeHospitalizationDetailsWrap">
                                <label class="form-label" for="previous_hospitalization_details">Hospitalization Details <span class="required">*</span></label>
                                <textarea id="previous_hospitalization_details" name="previous_hospitalization_details" class="form-control" rows="3">{{ $employeeValue('previous_hospitalization_details') }}</textarea>
                            </div>
                        </div>
                        <div class="form-field">
                            <label class="form-label">Operation / Surgery <span class="required">*</span></label>
                            <div class="pwd-toggle">
                                <input class="pwd-radio" type="radio" name="operation_surgery" id="employee_surgery_no" value="0" required {{ (string) $selectedOperationSurgery !== '1' ? 'checked' : '' }}>
                                <label class="pwd-option" for="employee_surgery_no">No</label>
                                <input class="pwd-radio" type="radio" name="operation_surgery" id="employee_surgery_yes" value="1" {{ (string) $selectedOperationSurgery === '1' ? 'checked' : '' }}>
                                <label class="pwd-option" for="employee_surgery_yes">Yes</label>
                            </div>
                            <div class="form-field mt-3 {{ (string) $selectedOperationSurgery === '1' ? '' : 'is-hidden' }}" id="employeeSurgeryDetailsWrap">
                                <label class="form-label" for="operation_surgery_details">Operation / Surgery Details <span class="required">*</span></label>
                                <textarea id="operation_surgery_details" name="operation_surgery_details" class="form-control" rows="3">{{ $employeeValue('operation_surgery_details') }}</textarea>
                            </div>
                        </div>
                    </div>

                    <h3 class="section-title mt-4" style="font-size:1rem;margin-bottom:10px;">Current Medications</h3>
                    <div class="form-grid personal-form-grid">
                        <div class="form-field">
                            <label class="form-label" for="allergies">Allergies</label>
                            <textarea id="allergies" name="allergies" class="form-control" rows="3">{{ $employeeValue('allergies') }}</textarea>
                        </div>
                    </div>
                    <div class="btn-row">
                        <button type="button" class="btn btn-health btn-health-back" data-step-back="1"><span>Back</span></button>
                        <button type="button" class="btn btn-health btn-health-next" data-step-next="3"><span>Next</span></button>
                    </div>
                </div>

                <div class="step-panel {{ $startStep === 3 ? '' : 'is-hidden' }}" id="stepPanel3">
                    <h2 class="section-title step-page-title" data-title-letter="F">Family History</h2>
                    <p class="step-fill-note">Select all family medical history that applies.</p>
                    @php($familyHistoryItems = ['Diabetes', 'PTB', 'Hypertension', 'Cancer'])
                    <div class="checkbox-grid">
                        @foreach($familyHistoryItems as $item)
                            <label class="checkbox-card">
                                <input type="checkbox" name="family_history[]" value="{{ $item }}" {{ in_array($item, $employeeCheckedValues('family_history'), true) ? 'checked' : '' }}>
                                <span>{{ $item }}</span>
                            </label>
                        @endforeach
                    </div>
                    <div class="form-field">
                        <label class="form-label" for="family_history_others">Others</label>
                        <input id="family_history_others" type="text" name="family_history_others" class="form-control" value="{{ $employeeValue('family_history_others') }}">
                    </div>
                    <div class="btn-row">
                        <button type="button" class="btn btn-health btn-health-back" data-step-back="2"><span>Back</span></button>
                        <button type="button" class="btn btn-health btn-health-next" data-step-next="4"><span>Next</span></button>
                    </div>
                </div>

                <div class="step-panel {{ $startStep === 4 ? '' : 'is-hidden' }}" id="stepPanel4">
                    <h2 class="section-title step-page-title" data-title-letter="H">Personal History</h2>
                    <p class="step-fill-note">Answer the personal history portion of the employee health examination record.</p>
                    <div class="form-grid personal-form-grid">
                        <div class="form-field">
                            <label class="form-label">Cigarette Smoking <span class="required">*</span></label>
                            <div class="pwd-toggle">
                                <input class="pwd-radio" type="radio" name="cigarette_smoking" id="employee_smoking_no" value="0" required {{ (string) $selectedCigaretteSmoking !== '1' ? 'checked' : '' }}>
                                <label class="pwd-option" for="employee_smoking_no">No</label>
                                <input class="pwd-radio" type="radio" name="cigarette_smoking" id="employee_smoking_yes" value="1" {{ (string) $selectedCigaretteSmoking === '1' ? 'checked' : '' }}>
                                <label class="pwd-option" for="employee_smoking_yes">Yes</label>
                            </div>
                        </div>
                        <div class="form-field">
                            <label class="form-label">Alcohol Drinking <span class="required">*</span></label>
                            <div class="pwd-toggle">
                                <input class="pwd-radio" type="radio" name="alcohol_drinking" id="employee_alcohol_no" value="0" required {{ (string) $selectedAlcoholDrinking !== '1' ? 'checked' : '' }}>
                                <label class="pwd-option" for="employee_alcohol_no">No</label>
                                <input class="pwd-radio" type="radio" name="alcohol_drinking" id="employee_alcohol_yes" value="1" {{ (string) $selectedAlcoholDrinking === '1' ? 'checked' : '' }}>
                                <label class="pwd-option" for="employee_alcohol_yes">Yes</label>
                            </div>
                        </div>
                        <div class="form-field">
                            <label class="form-label">Traveled Abroad <span class="required">*</span></label>
                            <div class="pwd-toggle">
                                <input class="pwd-radio" type="radio" name="traveled_abroad" id="employee_traveled_no" value="0" required {{ (string) $selectedTraveledAbroad !== '1' ? 'checked' : '' }}>
                                <label class="pwd-option" for="employee_traveled_no">No</label>
                                <input class="pwd-radio" type="radio" name="traveled_abroad" id="employee_traveled_yes" value="1" {{ (string) $selectedTraveledAbroad === '1' ? 'checked' : '' }}>
                                <label class="pwd-option" for="employee_traveled_yes">Yes</label>
                            </div>
                        </div>
                    </div>
                    <h3 class="section-title mt-4" style="font-size:1rem;margin-bottom:10px;">PWD</h3>
                    <div class="form-grid personal-form-grid">
                        <div class="form-field">
                            <label class="form-label">Do you have a disability? <span class="required">*</span></label>
                            <div class="pwd-toggle">
                                <input class="pwd-radio" type="radio" name="has_disability" id="employee_pwd_no" value="0" required {{ (string) $selectedEmployeePwd !== '1' ? 'checked' : '' }}>
                                <label class="pwd-option" for="employee_pwd_no">No</label>
                                <input class="pwd-radio" type="radio" name="has_disability" id="employee_pwd_yes" value="1" {{ (string) $selectedEmployeePwd === '1' ? 'checked' : '' }}>
                                <label class="pwd-option" for="employee_pwd_yes">Yes</label>
                            </div>
                        </div>
                        <div class="form-field {{ (string) $selectedEmployeePwd === '1' ? '' : 'is-hidden' }}" id="employeeDisabilityTypeWrap">
                            <label class="form-label" for="disability_type">Type of Disability <span class="required">*</span></label>
                            <input id="disability_type" type="text" name="disability_type" class="form-control" value="{{ $employeeValue('disability_type') }}">
                        </div>
                    </div>
                    <div class="btn-row">
                        <button type="button" class="btn btn-health btn-health-back" data-step-back="3"><span>Back</span></button>
                        <button type="button" class="btn btn-health btn-health-next" data-step-next="5"><span>Next</span></button>
                    </div>
                </div>

                <div class="step-panel {{ $startStep === 5 ? '' : 'is-hidden' }}" id="stepPanel5">
                    <h2 class="section-title step-page-title" data-title-letter="R">Clinic Requirements</h2>
                    <p class="step-fill-note">Upload available clinic documents. These files are optional and may also be completed during clinic assessment.</p>
                    <div class="requirement-grid">
                        <div class="requirement-card">
                            <div class="requirement-card-header">
                                <strong>2x2 Photo (Image)</strong>
                                <span class="requirement-badge">JPG/PNG</span>
                            </div>
                            <p class="requirement-guideline">Upload a formal front-facing photo on a plain white background if available.</p>
                            <input type="file" name="student_photo" class="form-control" accept=".jpg,.jpeg,.png,image/jpeg,image/png" data-upload-input data-preview-kind="image">
                            <div class="upload-preview-card" data-upload-preview aria-live="polite"></div>
                            <small>Allowed: JPG/PNG only, max 1MB.</small>
                        </div>
                        <div class="requirement-card">
                            <div class="requirement-card-header">
                                <strong>Declaration of Medical Information and Data Subject Consent Form</strong>
                                <span class="requirement-badge">PDF/IMG</span>
                            </div>
                            <p class="requirement-guideline">Upload the signed, clear, and readable declaration form if available.</p>
                            <input type="file" name="health_declaration" class="form-control" accept=".pdf,.jpg,.jpeg,.png,application/pdf,image/jpeg,image/png" data-upload-input>
                            <div class="upload-preview-card" data-upload-preview aria-live="polite"></div>
                            <small>Allowed: PDF, JPG, JPEG, or PNG, max 1MB.</small>
                        </div>
                        <div class="requirement-card">
                            <div class="requirement-card-header">
                                <strong>Medical Certificate</strong>
                                <span class="requirement-badge">PDF/IMG</span>
                            </div>
                            <p class="requirement-guideline">Upload a clear medical certificate if already available.</p>
                            <input type="file" name="medical_certificate" class="form-control" accept=".pdf,.jpg,.jpeg,.png,application/pdf,image/jpeg,image/png" data-upload-input>
                            <div class="upload-preview-card" data-upload-preview aria-live="polite"></div>
                            <small>Allowed: PDF, JPG, JPEG, or PNG, max 1MB.</small>
                        </div>
                        <div class="requirement-card">
                            <div class="requirement-card-header">
                                <strong>Chest X-ray Result</strong>
                                <span class="requirement-badge">PDF/IMG</span>
                            </div>
                            <p class="requirement-guideline">Upload the official radiologist's written report if available.</p>
                            <input type="file" name="chest_xray_document" class="form-control" accept=".pdf,.jpg,.jpeg,.png,application/pdf,image/jpeg,image/png" data-upload-input>
                            <div class="upload-preview-card" data-upload-preview aria-live="polite"></div>
                            <small>Allowed: PDF, JPG, JPEG, or PNG, max 1MB.</small>
                        </div>
                        <div class="requirement-card {{ (string) $selectedEmployeePwd === '1' ? '' : 'is-hidden' }}" id="employeePwdRequirementCard">
                            <div class="requirement-card-header">
                                <strong>PWD ID Proof</strong>
                                <span class="requirement-badge">PDF/IMG</span>
                            </div>
                            <p class="requirement-guideline">Upload a clear copy of your valid PWD ID if available.</p>
                            <input type="file" name="pwd_id_proof" class="form-control" accept=".pdf,.jpg,.jpeg,.png,application/pdf,image/jpeg,image/png" data-upload-input>
                            <div class="upload-preview-card" data-upload-preview aria-live="polite"></div>
                            <small>Allowed: PDF, JPG, JPEG, or PNG, max 1MB.</small>
                        </div>
                    </div>
                    <div class="btn-row">
                        <button type="button" class="btn btn-health btn-health-back" data-step-back="4"><span>Back</span></button>
                        <button type="button" class="btn btn-health btn-health-next" data-step-next="6"><span>Next</span></button>
                    </div>
                </div>

                <div class="step-panel {{ $startStep === 6 ? '' : 'is-hidden' }}" id="stepPanel6">
                    <h2 class="section-title step-page-title" data-title-letter="E">E-Signature</h2>
                    <p class="step-fill-note">Draw your signature or upload a clear signature image to certify your Health Examination Record.</p>
                    <input type="hidden" id="employee_signature" name="employee_signature" value="{{ old('employee_signature') }}">
                    <div class="esign-method-grid" aria-label="Choose your signature method">
                        <input class="esign-method-radio" type="radio" name="employee_signature_method" id="employee_signature_method_draw" value="draw" checked>
                        <label class="esign-method-card" for="employee_signature_method_draw">
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
                        <input class="esign-method-radio" type="radio" name="employee_signature_method" id="employee_signature_method_upload" value="upload">
                        <label class="esign-method-card" for="employee_signature_method_upload">
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
                    <div class="esign-grid esign-mode-panel" id="employeeSignatureDrawPanel">
                        <div class="esign-card">
                            <h3>Draw Signature</h3>
                            <p>Use your mouse, touchpad, or finger. You can clear and redraw before saving.</p>
                            <div class="signature-pad-wrap">
                                <canvas id="employeeSignaturePad" aria-label="Draw your signature"></canvas>
                            </div>
                            <div class="esign-actions">
                                <button type="button" class="esign-secondary-btn" id="clearEmployeeSignatureBtn">
                                    <svg viewBox="0 0 16 16" aria-hidden="true"><path d="M3.4 2.2 2.2 3.4 6.8 8l-4.6 4.6 1.2 1.2L8 9.2l4.6 4.6 1.2-1.2L9.2 8l4.6-4.6-1.2-1.2L8 6.8 3.4 2.2Z"></path></svg>
                                    <span>Clear</span>
                                </button>
                            </div>
                            <div class="esign-status" id="employeeSignatureStatus">No drawn signature yet.</div>
                        </div>
                    </div>
                    <div class="esign-grid esign-mode-panel is-hidden" id="employeeSignatureUploadPanel">
                        <div class="esign-card">
                            <h3>Upload Signature</h3>
                            <input id="uploaded_signature" type="file" name="uploaded_signature" class="esign-upload-input" accept=".png,.jpg,.jpeg,image/png,image/jpeg" data-upload-input data-preview-kind="image">
                            <ol class="esign-upload-instructions">
                                <li>Upload a black signature image if you prefer file upload.</li>
                                <li>PNG with transparent background is recommended.</li>
                                <li>If the background is not removed yet, use remove.bg first.</li>
                                <li>Maximum file size is 1MB.</li>
                            </ol>
                            <div class="upload-preview-card" data-upload-preview aria-live="polite"></div>
                        </div>
                    </div>
                    <div class="certify-row final-certification">
                        <input id="employee_health_profile_certified" type="checkbox" name="employee_health_profile_certified" value="1" required {{ old('employee_health_profile_certified') ? 'checked' : '' }}>
                        <label for="employee_health_profile_certified">
                            By affixing my signature, I am agreeing to the PUP Data Privacy Policy and giving my consent in the collection and processing of my Personal Information in accordance thereto.
                        </label>
                    </div>
                    <div class="btn-row">
                        <button type="button" class="btn btn-health btn-health-back" data-step-back="5"><span>Back</span></button>
                        <button type="submit" class="btn btn-health btn-health-submit"><span>Save Health Record</span></button>
                    </div>
                </div>


                <p class="privacy-note">
                    <span class="privacy-noteD">Data Privacy Notice:</span>
                    The information you provide is collected for school clinic documentation and health clearance processing only, in compliance with school data privacy requirements.
                </p>
            </form>
        </div>
    </div>

    <div class="health-error-modal" id="healthErrorModal" aria-hidden="true" data-initial-message="{{ session('error') ? e(session('error')) : ($errors->any() ? e($errors->first()) : '') }}">
        <div class="health-error-card" role="alertdialog" aria-modal="true" aria-labelledby="healthErrorTitle" aria-describedby="healthErrorMessage">
            <h3 id="healthErrorTitle">Error</h3>
            <p id="healthErrorMessage">Please complete the required field.</p>
            <button type="button" id="healthErrorContinue">Continue</button>
        </div>
    </div>

    <script>
        (function () {
            const form = document.getElementById('employeeHealthForm');
            if (!form) return;

            const totalSteps = 6;
            const stepNames = @json($healthFormSteps);
            const panels = Array.from({ length: totalSteps }, (_, index) => document.getElementById(`stepPanel${index + 1}`));
            const currentStepLabel = document.getElementById('currentStepLabel');
            const currentStepName = document.getElementById('currentStepName');
            const progressFill = document.getElementById('stepProgressFill');
            const progressPercent = document.getElementById('stepProgressPercent');
            const errorModal = document.getElementById('healthErrorModal');
            const errorMessage = document.getElementById('healthErrorMessage');
            const errorContinue = document.getElementById('healthErrorContinue');
            const signatureCanvas = document.getElementById('employeeSignaturePad');
            const signatureInput = document.getElementById('employee_signature');
            const signatureStatus = document.getElementById('employeeSignatureStatus');
            const signatureUpload = document.getElementById('uploaded_signature');
            const signatureMethodRadios = Array.from(document.querySelectorAll('input[name="employee_signature_method"]'));
            const signatureDrawPanel = document.getElementById('employeeSignatureDrawPanel');
            const signatureUploadPanel = document.getElementById('employeeSignatureUploadPanel');
            const birthdayInput = document.getElementById('birthday');
            const ageInput = document.getElementById('age');
            const clinicSelects = Array.from(document.querySelectorAll('[data-clinic-select]'));
            const pastMedicalOthersToggle = document.getElementById('employeePastMedicalOthersToggle');
            const pastMedicalOthersWrap = document.getElementById('employeePastMedicalOthersWrap');
            const pastMedicalOthersInput = document.getElementById('past_medical_history_others');
            const hospitalizationRadios = Array.from(document.querySelectorAll('input[name="previous_hospitalization"]'));
            const hospitalizationDetailsWrap = document.getElementById('employeeHospitalizationDetailsWrap');
            const hospitalizationDetailsInput = document.getElementById('previous_hospitalization_details');
            const surgeryRadios = Array.from(document.querySelectorAll('input[name="operation_surgery"]'));
            const surgeryDetailsWrap = document.getElementById('employeeSurgeryDetailsWrap');
            const surgeryDetailsInput = document.getElementById('operation_surgery_details');
            const employeePwdRadios = Array.from(document.querySelectorAll('input[name="has_disability"]'));
            const employeeDisabilityTypeWrap = document.getElementById('employeeDisabilityTypeWrap');
            const employeeDisabilityTypeInput = document.getElementById('disability_type');
            const employeePwdRequirementCard = document.getElementById('employeePwdRequirementCard');
            const uploadInputs = Array.from(document.querySelectorAll('[data-upload-input]'));
            let currentStep = {{ (int) $startStep }};
            let maxVisitedStep = currentStep;
            let resizeSignatureCanvas = () => {};

            function closeClinicSelect(wrap) {
                wrap?.classList.remove('is-open');
                const display = wrap?.querySelector('.clinic-select-display');
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

            function initializeClinicSelect(wrap) {
                const select = wrap?.querySelector('select');
                const display = wrap?.querySelector('.clinic-select-display');
                const options = Array.from(wrap?.querySelectorAll('.clinic-select-option') || []);
                if (!select || !display) return;

                syncClinicSelect(wrap);

                display.addEventListener('click', () => {
                    const shouldOpen = !wrap.classList.contains('is-open');
                    clinicSelects.forEach((otherWrap) => {
                        if (otherWrap !== wrap) closeClinicSelect(otherWrap);
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

            function showError(message) {
                if (!errorModal || !errorMessage) {
                    alert(message);
                    return;
                }
                errorMessage.textContent = message || 'Please complete the required field.';
                errorModal.classList.add('is-visible');
                errorModal.setAttribute('aria-hidden', 'false');
            }

            function hideError() {
                errorModal?.classList.remove('is-visible');
                errorModal?.setAttribute('aria-hidden', 'true');
            }

            function setStep(step) {
                currentStep = Math.min(totalSteps, Math.max(1, Number(step) || 1));
                maxVisitedStep = Math.max(maxVisitedStep, currentStep);
                panels.forEach((panel, index) => panel?.classList.toggle('is-hidden', index + 1 !== currentStep));
                const progress = Math.round((maxVisitedStep / totalSteps) * 100);
                if (currentStepLabel) currentStepLabel.textContent = `Step ${currentStep} of ${totalSteps}`;
                if (currentStepName) currentStepName.textContent = stepNames[currentStep] || '';
                if (progressFill) progressFill.style.width = `${progress}%`;
                if (progressPercent) progressPercent.textContent = `${progress}% Complete`;
                if (currentStep === 6) {
                    window.requestAnimationFrame(() => resizeSignatureCanvas());
                }
            }

            function validateStep(step) {
                const panel = panels[step - 1];
                const fields = Array.from(panel?.querySelectorAll('input, select, textarea') || []);
                const invalid = fields.find((field) => !field.disabled && !field.checkValidity());
                if (invalid) {
                    invalid.reportValidity();
                    showError(invalid.validationMessage || 'Please complete the required field.');
                    invalid.focus({ preventScroll: false });
                    return false;
                }
                return true;
            }

            document.querySelectorAll('[data-step-next], [data-step-back]').forEach((button) => {
                button.addEventListener('click', () => {
                    const next = button.dataset.stepNext ? Number(button.dataset.stepNext) : null;
                    const back = button.dataset.stepBack ? Number(button.dataset.stepBack) : null;
                    if (next && !validateStep(currentStep)) return;
                    setStep(next || back || currentStep);
                    panels[currentStep - 1]?.scrollIntoView({ behavior: 'smooth', block: 'start' });
                });
            });

            function calculateAgeFromBirthday() {
                if (!birthdayInput || !ageInput || !birthdayInput.value) return;
                const birthday = new Date(`${birthdayInput.value}T00:00:00`);
                if (Number.isNaN(birthday.getTime())) return;
                const today = new Date();
                let age = today.getFullYear() - birthday.getFullYear();
                const monthDiff = today.getMonth() - birthday.getMonth();
                if (monthDiff < 0 || (monthDiff === 0 && today.getDate() < birthday.getDate())) {
                    age -= 1;
                }
                if (age > 0) ageInput.value = String(age);
            }

            birthdayInput?.addEventListener('change', calculateAgeFromBirthday);
            birthdayInput?.addEventListener('input', calculateAgeFromBirthday);

            function syncPastMedicalOthers() {
                const show = !!pastMedicalOthersToggle?.checked;
                pastMedicalOthersWrap?.classList.toggle('is-hidden', !show);
                if (pastMedicalOthersInput) {
                    pastMedicalOthersInput.required = show;
                    pastMedicalOthersInput.disabled = !show;
                    if (!show) {
                        pastMedicalOthersInput.value = '';
                        pastMedicalOthersInput.setCustomValidity('');
                    }
                }
            }

            function syncEmployeeConditionalDetails(radios, wrap, input) {
                const selected = radios.find((radio) => radio.checked);
                const show = selected?.value === '1';
                wrap?.classList.toggle('is-hidden', !show);
                if (input) {
                    input.required = show;
                    input.disabled = !show;
                    if (!show) {
                        input.value = '';
                        input.setCustomValidity('');
                    }
                }
            }

            pastMedicalOthersToggle?.addEventListener('change', syncPastMedicalOthers);
            hospitalizationRadios.forEach((radio) => {
                radio.addEventListener('change', () => syncEmployeeConditionalDetails(hospitalizationRadios, hospitalizationDetailsWrap, hospitalizationDetailsInput));
            });
            surgeryRadios.forEach((radio) => {
                radio.addEventListener('change', () => syncEmployeeConditionalDetails(surgeryRadios, surgeryDetailsWrap, surgeryDetailsInput));
            });

            function syncEmployeePwd() {
                const selected = employeePwdRadios.find((radio) => radio.checked);
                const isPwd = selected?.value === '1';
                employeeDisabilityTypeWrap?.classList.toggle('is-hidden', !isPwd);
                employeePwdRequirementCard?.classList.toggle('is-hidden', !isPwd);
                if (employeeDisabilityTypeInput) {
                    employeeDisabilityTypeInput.required = isPwd;
                    employeeDisabilityTypeInput.disabled = !isPwd;
                    if (!isPwd) {
                        employeeDisabilityTypeInput.value = '';
                        employeeDisabilityTypeInput.setCustomValidity('');
                    }
                }
            }

            employeePwdRadios.forEach((radio) => {
                radio.addEventListener('change', syncEmployeePwd);
            });

            function formatFileSize(bytes) {
                if (!Number.isFinite(bytes) || bytes <= 0) return 'Selected file';
                if (bytes < 1024 * 1024) return Math.max(1, Math.round(bytes / 1024)) + ' KB';
                return (bytes / (1024 * 1024)).toFixed(1) + ' MB';
            }

            function renderUploadPreview(input) {
                const card = input.closest('.requirement-card');
                const previewScope = card || input.closest('.esign-card');
                const preview = card?.querySelector('[data-upload-preview]');
                const targetPreview = preview || previewScope?.querySelector('[data-upload-preview]');
                if (!previewScope || !targetPreview) return;

                if (targetPreview.dataset.objectUrl) {
                    URL.revokeObjectURL(targetPreview.dataset.objectUrl);
                    targetPreview.dataset.objectUrl = '';
                }

                const file = input.files && input.files[0] ? input.files[0] : null;
                card?.classList.toggle('file-selected', !!file);

                if (!file) {
                    targetPreview.classList.remove('is-visible');
                    targetPreview.innerHTML = '';
                    return;
                }

                const maxSize = 1 * 1024 * 1024;
                if (file.size > maxSize) {
                    targetPreview.innerHTML = `
                        <div class="upload-preview-error">
                            <div class="upload-preview-error-header">
                                <span class="upload-preview-error-icon">!</span>
                                <span class="upload-preview-error-text">File is too large (${formatFileSize(file.size)}). Maximum size is 1MB.</span>
                            </div>
                        </div>
                    `;
                    input.setCustomValidity('File size exceeds 1MB limit');
                    targetPreview.classList.add('is-visible');
                    return;
                }

                input.setCustomValidity('');

                const isImage = file.type.startsWith('image/');
                const fileMeta = `${file.name} • ${formatFileSize(file.size)}`;
                if (isImage) {
                    const objectUrl = URL.createObjectURL(file);
                    targetPreview.dataset.objectUrl = objectUrl;
                    targetPreview.innerHTML = `
                        <div class="upload-preview-thumb">
                            <img src="${objectUrl}" alt="Selected file preview">
                        </div>
                        <div class="upload-preview-body">
                            <span class="upload-preview-name">${file.name}</span>
                            <span class="upload-preview-meta">${formatFileSize(file.size)}</span>
                        </div>
                    `;
                } else {
                    targetPreview.innerHTML = `
                        <div class="upload-preview-thumb">
                            <svg viewBox="0 0 24 24" aria-hidden="true"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"></path><path d="M14 2v6h6"></path></svg>
                        </div>
                        <div class="upload-preview-body">
                            <span class="upload-preview-name">${file.name}</span>
                            <span class="upload-preview-meta">${fileMeta}</span>
                        </div>
                    `;
                }
                targetPreview.classList.add('is-visible');
            }

            uploadInputs.forEach((input) => {
                input.addEventListener('change', () => renderUploadPreview(input));
            });

            function syncSignatureMethod() {
                const selectedMethod = signatureMethodRadios.find((radio) => radio.checked)?.value || 'draw';
                const isUpload = selectedMethod === 'upload';
                signatureDrawPanel?.classList.toggle('is-hidden', isUpload);
                signatureUploadPanel?.classList.toggle('is-hidden', !isUpload);
                if (isUpload) {
                    if (signatureInput) signatureInput.value = '';
                    const ctx = signatureCanvas?.getContext('2d');
                    ctx?.clearRect(0, 0, signatureCanvas.width, signatureCanvas.height);
                    if (signatureStatus) signatureStatus.textContent = 'No drawn signature yet.';
                } else {
                    if (signatureUpload) signatureUpload.value = '';
                    const uploadPreview = signatureUploadPanel?.querySelector('[data-upload-preview]');
                    uploadPreview?.classList.remove('is-visible');
                    if (uploadPreview) uploadPreview.innerHTML = '';
                    window.requestAnimationFrame(() => resizeSignatureCanvas());
                }
            }

            function validateSignatureChoice() {
                const selectedMethod = signatureMethodRadios.find((radio) => radio.checked)?.value || 'draw';
                if (selectedMethod === 'draw' && !signatureInput?.value) {
                    setStep(6);
                    showError('Please draw your signature before submitting.');
                    return false;
                }
                if (selectedMethod === 'upload' && (!signatureUpload || signatureUpload.files.length === 0)) {
                    setStep(6);
                    showError('Please upload your signature file before submitting.');
                    return false;
                }
                return true;
            }

            signatureMethodRadios.forEach((radio) => {
                radio.addEventListener('change', syncSignatureMethod);
            });

            function setupEmployeeSignaturePad() {
                if (!signatureCanvas || !signatureInput) return;

                const context = signatureCanvas.getContext('2d');
                let drawing = false;
                let hasDrawing = Boolean(signatureInput.value);

                function applySignatureStroke() {
                    context.lineCap = 'round';
                    context.lineJoin = 'round';
                    context.lineWidth = 3.5;
                    context.strokeStyle = '#000000';
                    context.fillStyle = '#000000';
                }

                function resizeCanvas() {
                const ratio = Math.max(window.devicePixelRatio || 1, 1);
                    const rect = signatureCanvas.getBoundingClientRect();
                    const previousData = hasDrawing ? (signatureInput.value || signatureCanvas.toDataURL('image/png')) : '';
                    signatureCanvas.width = Math.max(1, Math.floor(rect.width * ratio));
                    signatureCanvas.height = Math.max(1, Math.floor(rect.height * ratio));
                    context.setTransform(ratio, 0, 0, ratio, 0, 0);
                    applySignatureStroke();

                    if (previousData) {
                    const image = new Image();
                        image.onload = () => context.drawImage(image, 0, 0, rect.width, rect.height);
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
                    signatureInput.value = signatureCanvas.toDataURL('image/png');
                    if (signatureStatus) signatureStatus.textContent = 'Drawn signature ready.';
                    if (signatureUpload) {
                        signatureUpload.value = '';
                        renderUploadPreview(signatureUpload);
                    }
            }

                function draw(event) {
                    if (!drawing) return;
                    event.preventDefault();
                    const point = positionFromEvent(event);
                    context.lineTo(point.x, point.y);
                    context.stroke();
                    hasDrawing = true;
                    signatureInput.value = signatureCanvas.toDataURL('image/png');
                if (signatureStatus) signatureStatus.textContent = 'Drawn signature ready.';
                    if (signatureUpload) signatureUpload.value = '';
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
                    signatureInput.value = '';
                    if (clearUpload && signatureUpload) {
                        signatureUpload.value = '';
                        renderUploadPreview(signatureUpload);
                    }
                if (signatureStatus) signatureStatus.textContent = 'No drawn signature yet.';
                }

                resizeSignatureCanvas = resizeCanvas;
                resizeCanvas();
                window.addEventListener('resize', resizeCanvas);
                signatureCanvas.addEventListener('pointerdown', startDrawing);
                signatureCanvas.addEventListener('pointermove', draw);
                signatureCanvas.addEventListener('pointerup', stopDrawing);
                signatureCanvas.addEventListener('pointercancel', stopDrawing);
                signatureCanvas.addEventListener('pointerleave', stopDrawing);
                document.getElementById('clearEmployeeSignatureBtn')?.addEventListener('click', () => clearSignature());
                signatureUpload?.addEventListener('change', () => {
                    if (signatureUpload.files && signatureUpload.files.length > 0) {
                        clearSignature(false);
                        if (signatureStatus) signatureStatus.textContent = 'Uploaded signature will be used.';
                    }
                });
            }

            errorContinue?.addEventListener('click', hideError);
            errorModal?.addEventListener('click', (event) => {
                if (event.target === errorModal) hideError();
            });
            document.addEventListener('click', (event) => {
                clinicSelects.forEach((wrap) => {
                    if (!wrap.contains(event.target)) closeClinicSelect(wrap);
                });
            });

            form.addEventListener('submit', (event) => {
                if (!validateStep(currentStep)) {
                    event.preventDefault();
                    return;
                }

                for (let step = 1; step <= totalSteps; step += 1) {
                    if (!validateStep(step)) {
                        event.preventDefault();
                        setStep(step);
                        return;
                    }
                }

                if (!validateSignatureChoice()) {
                    event.preventDefault();
                }
            });

            const initialMessage = errorModal?.dataset.initialMessage || '';
            if (initialMessage) showError(initialMessage);
            clinicSelects.forEach(initializeClinicSelect);
            syncPastMedicalOthers();
            syncEmployeeConditionalDetails(hospitalizationRadios, hospitalizationDetailsWrap, hospitalizationDetailsInput);
            syncEmployeeConditionalDetails(surgeryRadios, surgeryDetailsWrap, surgeryDetailsInput);
            syncEmployeePwd();
            setupEmployeeSignaturePad();
            syncSignatureMethod();
            calculateAgeFromBirthday();
            resizeSignatureCanvas();
            setStep(currentStep);
        })();
    </script>


    @include('partials.student_voice_input_support')
    @include('partials.system_footer')
</body>
</html>

