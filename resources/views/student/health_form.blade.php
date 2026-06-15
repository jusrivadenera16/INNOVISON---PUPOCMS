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
            background: rgba(255, 255, 255, 0.97);
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
            margin: 0;
            font-size: 1.6rem;
            font-weight: 800;
            color: #70131b;
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
            font-size: 1.6rem;
            font-weight: 800;
        }

        .stepper-shell {
            display: grid;
            grid-template-columns: repeat(5, minmax(0, 1fr));
            gap: 10px;
            min-height: 91px;
            position: fixed;
            left: 50%;
            top: 14px;
            transform: translateX(-50%);
            width: min(972px, calc(100vw - 10px));
            box-sizing: border-box;
            z-index: 50;
            background: rgba(255, 255, 255, 0.32);
            padding: 10px;
            border-radius: 16px;
            border: 1px solid rgba(255, 255, 255, 0.62);
            box-shadow:
                0 10px 30px rgba(74, 15, 26, 0.14),
                inset 0 1px 0 rgba(255, 255, 255, 0.72);
            -webkit-backdrop-filter: blur(18px) saturate(145%);
            backdrop-filter: blur(18px) saturate(145%);
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

        .stepper-spacer {
            height: 109px;
        }

        .step-chip {
            border: 1px solid rgba(127, 29, 45, 0.2);
            border-radius: 14px;
            padding: 12px 14px;
            background: #fff7d6;
            opacity: 0.78;
            transition: all 0.2s ease;
        }

        .step-chip small {
            display: block;
            color: #6b7280;
            text-transform: uppercase;
            letter-spacing: 0.06em;
            font-size: 0.72rem;
            margin-bottom: 2px;
            font-weight: 700;
        }

        .step-chip strong {
            color: #70131b;
            font-size: 0.95rem;
        }

        .step-chip.is-active {
            background: linear-gradient(135deg, var(--clinic-maroon) 0%, var(--clinic-maroon-dark) 100%);
            border-color: transparent;
            opacity: 1;
            box-shadow: 0 8px 20px rgba(127, 29, 45, 0.24);
        }

        .step-chip.is-active small,
        .step-chip.is-active strong {
            color: #fff;
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
            position: absolute;
            width: 1px;
            height: 1px;
            padding: 0;
            margin: -1px;
            overflow: hidden;
            clip: rect(0, 0, 0, 0);
            white-space: nowrap;
            border: 0;
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
        #pwdUploadWrap.is-hidden {
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

        .submit-overlay {
            position: fixed;
            inset: 0;
            background: rgba(15, 23, 42, 0.55);
            display: none;
            align-items: center;
            justify-content: center;
            z-index: 9999;
            backdrop-filter: blur(4px);
        }

        .submit-overlay.is-open {
            display: flex;
        }

        .submit-card {
            width: min(360px, calc(100vw - 26px));
            border-radius: 18px;
            background: #ffffff;
            border: 1px solid rgba(16, 185, 129, 0.25);
            box-shadow: 0 20px 40px rgba(15, 23, 42, 0.28);
            padding: 26px 20px;
            text-align: center;
        }

        .submit-check {
            width: 72px;
            height: 72px;
            border-radius: 999px;
            margin: 0 auto 12px;
            background: linear-gradient(135deg, #16a34a 0%, #22c55e 100%);
            display: flex;
            align-items: center;
            justify-content: center;
            animation: popIn 0.35s ease;
        }

        .submit-check svg {
            width: 36px;
            height: 36px;
            stroke: #fff;
            stroke-width: 2.6;
            fill: none;
        }

        .submit-card strong {
            display: block;
            color: #111827;
            font-size: 1rem;
        }

        @keyframes popIn {
            0% { transform: scale(0.7); opacity: 0; }
            100% { transform: scale(1); opacity: 1; }
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

            .stepper-shell {
                top: 8px;
                width: calc(100vw - 16px);
                min-height: auto;
                padding: 8px;
            }

            .stepper-spacer {
                height: 74px;
            }

            .step-chip {
                display: none;
                padding: 10px 12px;
                border-radius: 12px;
            }

            .step-chip.is-active {
                display: flex;
                align-items: center;
                justify-content: space-between;
                gap: 12px;
                min-height: 48px;
            }

            .step-chip.is-active small {
                margin: 0;
                font-size: 0.68rem;
                white-space: nowrap;
            }

            .step-chip.is-active small::after {
                content: " of 5";
            }

            .step-chip.is-active strong {
                font-size: 0.9rem;
                text-align: right;
                line-height: 1.2;
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
            @if(session('error'))
                <div class="alert alert-danger">{{ session('error') }}</div>
            @endif
            @if($errors->any())
                <div class="alert alert-danger">{{ $errors->first() }}</div>
            @endif
            @php
                $selectedPwd = old('has_disability', $prefill['has_disability'] ?? 'No');
                $personalErrorFields = ['school_year', 'home_address', 'zipcode', 'birthday', 'age', 'sex', 'civil_status', 'height', 'weight', 'blood_type', 'contact_no', 'guardian_name', 'landline', 'cellphone'];
                $medicalErrorFields = ['has_illness', 'medical_history', 'other_illness', 'has_disability', 'disability_type', 'food_allergies', 'no_allergies', 'medicine_allergies', 'other_med_allergies', 'is_smoker', 'is_drinker'];
                $covidErrorFields = ['covid_vaccinated', 'vaccine_history'];
                $uploadErrorFields = ['medical_certificate', 'doctor_name', 'med_cert_date', 'med_cert_findings', 'chest_xray_result', 'xray_date', 'xray_findings', 'pwd_id_proof', 'student_photo', 'health_profile_certified'];
                $startStep = collect($uploadErrorFields)->contains(fn ($field) => $errors->has($field)) ? 5
                    : (collect($covidErrorFields)->contains(fn ($field) => $errors->has($field)) ? 4
                    : (collect($medicalErrorFields)->contains(fn ($field) => $errors->has($field)) ? 3
                    : (collect($personalErrorFields)->contains(fn ($field) => $errors->has($field)) ? 2 : 1)));
                $selectedMedicalHistory = old('medical_history', []);
                $selectedMedicineAllergies = old('medicine_allergies', []);
                $selectedHasIllness = old('has_illness', 'No');
                $displayFirstName = trim((string) (
                    ($prefill['puptas_first_name'] ?? '')
                    ?: ($prefill['first_name'] ?? '')
                ));
                $displayMiddleName = trim((string) (
                    ($prefill['puptas_middle_name'] ?? '')
                    ?: ($prefill['middle_name'] ?? '')
                ));
                $displayLastName = trim((string) (
                    ($prefill['puptas_last_name'] ?? '')
                    ?: ($prefill['last_name'] ?? '')
                ));

                $displayReferenceNumber = trim((string) ($prefill['reference_number'] ?? ''));
                $referenceNumberDraft = trim((string) old('reference_number', ''));
            @endphp

            <div class="stepper-shell">
                <div class="step-chip {{ $startStep === 1 ? 'is-active' : '' }}" id="chipStep1">
                    <small>Step 1</small>
                    <strong>Admission Reference</strong>
                </div>
                <div class="step-chip {{ $startStep === 2 ? 'is-active' : '' }}" id="chipStep2">
                    <small>Step 2</small>
                    <strong>Personal Information</strong>
                </div>
                <div class="step-chip {{ $startStep === 3 ? 'is-active' : '' }}" id="chipStep3">
                    <small>Step 3</small>
                    <strong>Medical History</strong>
                </div>
                <div class="step-chip {{ $startStep === 4 ? 'is-active' : '' }}" id="chipStep4">
                    <small>Step 4</small>
                    <strong>COVID-19</strong>
                </div>
                <div class="step-chip {{ $startStep === 5 ? 'is-active' : '' }}" id="chipStep5">
                    <small>Step 5</small>
                    <strong>Clinic Requirements</strong>
                </div>
            </div>
            <div class="stepper-spacer"></div>

            <form action="{{ route('store.health.form') }}" method="POST" enctype="multipart/form-data">
                @csrf
                <input type="hidden" name="course_college" value="{{ old('course_college', $prefill['course_college'] ?? $user->course) }}">

                <div class="step-panel {{ $startStep === 1 ? '' : 'is-hidden' }}" id="stepPanel1">
                    <div class="form-intro">
                        <h1>Admission Reference</h1>
                        <p>Confirm your admission reference, complete your health information, then upload the required clinic documents.</p>
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
                        <div class="reference-panel" id="referencePanel">
                            <small>Reference Number</small>
                            <div class="reference-display">
                                <strong id="referenceDisplayValue">{{ $displayReferenceNumber !== '' ? $displayReferenceNumber : 'No Reference Found' }}</strong>
                            </div>
                            <button
                                type="button"
                                class="reference-edit-btn"
                                id="editReferenceBtn"
                                aria-label="{{ $displayReferenceNumber !== '' ? 'Edit admission reference' : 'Add admission reference' }}"
                                aria-expanded="false"
                                title="{{ $displayReferenceNumber !== '' ? 'Edit reference number' : 'Add reference number' }}"
                            >
                                <svg class="reference-icon-edit" viewBox="0 0 24 24" aria-hidden="true">
                                    <path d="M12 20h9"></path>
                                    <path d="M16.5 3.5a2.12 2.12 0 0 1 3 3L8 18l-4 1 1-4Z"></path>
                                </svg>
                                <svg class="reference-icon-check" viewBox="0 0 24 24" aria-hidden="true">
                                    <path d="m5 12 4 4L19 6"></path>
                                </svg>
                            </button>
                            <div class="reference-verify-wrap" id="referenceVerifyWrap">
                                <div class="reference-verify-row">
                                    <input
                                        type="text"
                                        name="reference_number"
                                        id="reference_number"
                                        class="reference-verify-input"
                                        value="{{ $displayReferenceNumber !== '' ? $displayReferenceNumber : $referenceNumberDraft }}"
                                        placeholder="0000-0000-0000"
                                        maxlength="14"
                                        pattern="\d{4}-\d{4}-\d{4}"
                                        inputmode="numeric"
                                        autocomplete="off"
                                        required
                                        aria-describedby="referenceVerifyStatus"
                                    >
                                </div>
                                <p class="reference-verify-status" id="referenceVerifyStatus" aria-live="polite">
                                    Enter the reference number exactly as shown in your Admission System record.
                                </p>
                                @error('reference_number')
                                    <p class="reference-field-error">{{ $message }}</p>
                                @enderror
                            </div>
                        </div>
                    </div>

                    <div class="upload-instruction-card">
                        <strong>Instructions for Completing Your Health Profile</strong>
                        <ol>
                            <li>Review your admission reference and name before proceeding.</li>
                            <li>Complete every required field in Personal Information using accurate and current details.</li>
                            <li>Answer the Medical History, allergy, disability, smoking, and alcohol questions truthfully.</li>
                            <li>Provide your COVID-19 vaccination status and dose details, when applicable.</li>
                            <li>Prepare clear PDF or image copies of your medical certificate and official chest X-ray report.</li>
                            <li>If you are a PWD, upload your PWD ID in Step 5. Upload your formal 2x2 photo as JPG or PNG.</li>
                            <li>Upload your formal 2x2 photo as JPG or PNG.</li>
                            <li>Download and print your Health Information Form before proceeding to Clinic for Assessment.</li>
                            <li>Don't forget to bring your hard copy of requirements and printed 2 by 2 photo to the clinic.</li>
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
                        @endenv
                        <button type="button" class="btn btn-health btn-health-next" id="nextToStep2">
                            <span>Next</span>
                            <svg viewBox="0 0 24 24" aria-hidden="true">
                                <path d="m9 18 6-6-6-6"></path>
                            </svg>
                        </button>
                    </div>
                </div>

                <div class="step-panel {{ $startStep === 2 ? '' : 'is-hidden' }}" id="stepPanel2">
                    <h2 class="section-title step-page-title">Personal Information</h2>
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
                    </div>
                    <div class="form-field personal-email-field">
                        <label class="form-label" for="profile_email">Email Address</label>
                        <input id="profile_email" type="email" class="form-control identity-readonly" value="{{ $user->email }}" readonly>
                    </div>
                    <div class="step-one-grid">
                        <div class="form-field">
                            <label class="form-label" for="school_year">School Year <span class="required">*</span></label>
                            <input
                                id="school_year"
                                class="form-control field-maroon{{ !empty($prefill['school_year_from_puptas']) ? ' identity-readonly' : '' }}"
                                name="school_year"
                                value="{{ old('school_year', $prefill['school_year'] ?? '') }}"
                                placeholder="YYYY-YYYY"
                                pattern="\d{4}-\d{4}"
                                inputmode="numeric"
                                required
                                {{ !empty($prefill['school_year_from_puptas']) ? 'readonly' : '' }}
                            >
                        </div>
                        <div class="form-field">
                            <label class="form-label" for="birthday">Birthday <span class="required">*</span></label>
                            <input id="birthday" type="date" class="form-control field-maroon" name="birthday" value="{{ old('birthday', $prefill['birthday'] ?? '') }}" required>
                        </div>
                        <div class="form-field">
                            <label class="form-label" for="age">Age <span class="required">*</span></label>
                            <input id="age" type="number" class="form-control field-maroon" name="age" value="{{ old('age', $prefill['age'] ?? '') }}" min="15" max="100" required readonly>
                        </div>
                        <div class="form-field">
                            <label class="form-label" for="sex">Sex <span class="required">*</span></label>
                            <div class="clinic-select-wrap" data-clinic-select data-select-placeholder="Select sex">
                                <select id="sex" class="form-select clinic-select-native field-maroon" name="sex" required>
                                    <option value="">Select sex</option>
                                    @foreach(['Male', 'Female'] as $option)
                                        <option value="{{ $option }}" {{ old('sex', $prefill['sex'] ?? '') === $option ? 'selected' : '' }}>{{ $option }}</option>
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
                        <div class="form-field">
                            <label class="form-label" for="height">Height (cm) <span class="required">*</span></label>
                            <input id="height" type="number" step="0.01" class="form-control field-maroon" name="height" value="{{ old('height', $prefill['height'] ?? '') }}" min="1" required>
                        </div>
                        <div class="form-field">
                            <label class="form-label" for="weight">Weight (kg) <span class="required">*</span></label>
                            <input id="weight" type="number" step="0.01" class="form-control field-maroon" name="weight" value="{{ old('weight', $prefill['weight'] ?? '') }}" min="1" required>
                        </div>
                        <div class="form-field span-2">
                            <label class="form-label" for="home_address">Home Address <span class="required">*</span></label>
                            <input id="home_address" class="form-control field-maroon" name="home_address" value="{{ old('home_address', $prefill['home_address'] ?? '') }}" required>
                        </div>
                        <div class="form-field">
                            <label class="form-label" for="zipcode">ZIP Code <span class="required">*</span></label>
                            <input id="zipcode" class="form-control field-maroon" name="zipcode" value="{{ old('zipcode', $prefill['zipcode'] ?? '') }}" required>
                        </div>
                        <div class="form-field">
                            <label class="form-label" for="contact_no">Student Contact Number <span class="required">*</span></label>
                            <input id="contact_no" class="form-control field-maroon" name="contact_no" value="{{ old('contact_no', $prefill['contact_number'] ?? $user->contact_no ?? '') }}" required>
                        </div>
                        <div class="form-field">
                            <label class="form-label" for="guardian_name">Parent / Guardian Name <span class="required">*</span></label>
                            <input id="guardian_name" class="form-control field-maroon" name="guardian_name" value="{{ old('guardian_name', $prefill['guardian_name'] ?? '') }}" required>
                        </div>
                        <div class="form-field">
                            <label class="form-label" for="cellphone">Parent / Guardian Cellphone <span class="required">*</span></label>
                            <input id="cellphone" class="form-control field-maroon" name="cellphone" value="{{ old('cellphone', $prefill['cellphone'] ?? '') }}" required>
                        </div>
                        <div class="form-field">
                            <label class="form-label" for="landline">Landline</label>
                            <input id="landline" class="form-control field-maroon" name="landline" value="{{ old('landline', $prefill['landline'] ?? '') }}">
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

                <div class="step-panel {{ $startStep === 3 ? '' : 'is-hidden' }}" id="stepPanel3">
                    <h2 class="section-title step-page-title">Medical History</h2>
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
                            <textarea id="other_illness" name="other_illness" class="form-control field-maroon" rows="3">{{ old('other_illness') }}</textarea>
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
                            <input id="disability_type" name="disability_type" class="form-control field-maroon" value="{{ old('disability_type', $prefill['disability_type'] ?? '') }}">
                        </div>
                    </div>

                    <h2 class="section-title mt-4">Allergies</h2>
                    <label class="choice-card mb-3">
                        <input id="no_allergies" type="checkbox" name="no_allergies" value="1" {{ old('no_allergies') ? 'checked' : '' }}>
                        <span>No Known Allergies</span>
                    </label>
                    <div id="allergyDetails" class="conditional-section">
                        <div class="form-field mb-3">
                            <label class="form-label" for="food_allergies">Food Allergies</label>
                            <input id="food_allergies" name="food_allergies" class="form-control field-maroon" value="{{ old('food_allergies') }}" placeholder="Specify food allergies">
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
                            <input id="other_med_allergies" name="other_med_allergies" class="form-control field-maroon" value="{{ old('other_med_allergies') }}">
                        </div>
                    </div>

                    <h2 class="section-title mt-4">Personal Social History</h2>
                    <div class="step-one-grid">
                        @foreach(['is_smoker' => 'Cigarette Smoking', 'is_drinker' => 'Alcohol Drinking'] as $field => $label)
                            <div class="form-field">
                                <label class="form-label">{{ $label }} <span class="required">*</span></label>
                                <div class="pwd-toggle">
                                    @foreach(['No', 'Yes'] as $option)
                                        <input class="pwd-radio" type="radio" name="{{ $field }}" id="{{ $field }}_{{ strtolower($option) }}" value="{{ $option }}" required {{ old($field, 'No') === $option ? 'checked' : '' }}>
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
                    <h2 class="section-title step-page-title">COVID-19</h2>
                    <p class="step-fill-note">Select your vaccination status. If vaccinated, provide at least your first and second dose details.</p>
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
                                @php $doseRequired = in_array($doseKey, ['first_dose', 'second_dose'], true); @endphp
                                <div class="dose-row">
                                    <div class="dose-label">{{ $doseLabel }}{{ $doseRequired ? '' : ' (Optional)' }}</div>
                                    <div class="form-field">
                                        <label class="form-label" for="{{ $doseKey }}_date">Date Received @if($doseRequired)<span class="required">*</span>@endif</label>
                                        <input id="{{ $doseKey }}_date" type="date" name="vaccine_history[{{ $doseKey }}][date]" class="form-control field-maroon" value="{{ old("vaccine_history.$doseKey.date") }}" min="2021-03-01" max="2025-12-31" data-vaccine-field data-dose-required="{{ $doseRequired ? 'true' : 'false' }}">
                                    </div>
                                    <div class="form-field">
                                        <label class="form-label" for="{{ $doseKey }}_brand">Vaccine Brand @if($doseRequired)<span class="required">*</span>@endif</label>
                                        <input id="{{ $doseKey }}_brand" name="vaccine_history[{{ $doseKey }}][brand]" class="form-control field-maroon" value="{{ old("vaccine_history.$doseKey.brand") }}" placeholder="e.g. Pfizer, Moderna" data-vaccine-field data-dose-required="{{ $doseRequired ? 'true' : 'false' }}">
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
                    <h2 class="section-title step-page-title">Clinic Requirements</h2>
                    <div class="requirement-grid">
                        <div class="requirement-card" id="pwdUploadWrap" data-requirement-card>
                            <div class="requirement-card-header">
                                <strong>PWD ID <span class="required">*</span></strong>
                                <span class="requirement-badge">PDF</span>
                            </div>
                            <p class="requirement-guideline">Please upload a clear and readable scanned copy of the front of your valid PWD ID.</p>
                            <input id="pwd_id_proof" type="file" name="pwd_id_proof" class="form-control" accept=".pdf,application/pdf" data-requirement-file data-upload-input data-preview-kind="pdf">
                            <div class="upload-preview-card" data-upload-preview aria-live="polite"></div>
                            <small>Required only when PWD. Allowed: PDF only, max 4MB.</small>
                        </div>
                        <div class="requirement-card {{ old('doctor_name') || old('med_cert_date') || old('med_cert_findings') ? 'has-old-data' : '' }}" data-requirement-card>
                            <div class="requirement-card-header">
                                <strong>Medical Certificate <span class="required">*</span></strong>
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
                            <input type="file" name="medical_certificate" class="form-control" accept=".pdf,.jpg,.jpeg,.png,application/pdf,image/jpeg,image/png" required data-requirement-file data-upload-input>
                            <div class="upload-preview-card" data-upload-preview aria-live="polite"></div>
                            <small>Allowed: PDF, JPG, JPEG, or PNG, max 4MB.</small>
                            <div class="requirement-extra">
                                <div class="form-field span-2">
                                    <label class="form-label" for="doctor_name">Doctor's Full Name <span class="required">*</span></label>
                                    <input id="doctor_name" type="text" name="doctor_name" class="form-control" value="{{ old('doctor_name') }}" maxlength="255" required data-requirement-extra-field>
                                </div>
                                <div class="form-field">
                                    <label class="form-label" for="med_cert_date">Date of Certificate <span class="required">*</span></label>
                                    <input id="med_cert_date" type="date" name="med_cert_date" class="form-control" value="{{ old('med_cert_date') }}" required data-requirement-extra-field>
                                </div>
                                <div class="form-field">
                                    <label class="form-label" for="med_cert_findings">Findings <span class="required">*</span></label>
                                    <div class="clinic-select-wrap" data-clinic-select>
                                        <select id="med_cert_findings" name="med_cert_findings" class="form-select clinic-select-native" required data-requirement-extra-field>
                                            <option value="">Select findings</option>
                                            <option value="No Findings / Normal" {{ old('med_cert_findings') === 'No Findings / Normal' ? 'selected' : '' }}>No Findings / Normal</option>
                                            <option value="With Findings" {{ old('med_cert_findings') === 'With Findings' ? 'selected' : '' }}>With Findings</option>
                                            <option value="Not Sure / For Clinic Review" {{ old('med_cert_findings') === 'Not Sure / For Clinic Review' ? 'selected' : '' }}>Not Sure / For Clinic Review</option>
                                        </select>
                                        <button type="button" class="clinic-select-display" aria-haspopup="listbox" aria-expanded="false">Select findings</button>
                                        <div class="clinic-select-menu" role="listbox" aria-label="Medical certificate findings options">
                                            <button type="button" class="clinic-select-option" data-select-value="No Findings / Normal">No Findings / Normal</button>
                                            <button type="button" class="clinic-select-option" data-select-value="With Findings">With Findings</button>
                                            <button type="button" class="clinic-select-option" data-select-value="Not Sure / For Clinic Review">Not Sure / For Clinic Review</button>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="requirement-card {{ old('xray_date') || old('xray_findings') ? 'has-old-data' : '' }}" data-requirement-card>
                            <div class="requirement-card-header">
                                <strong>Chest X-ray Result <span class="required">*</span></strong>
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
                            <input type="file" name="chest_xray_result" class="form-control" accept=".pdf,.jpg,.jpeg,.png,application/pdf,image/jpeg,image/png" required data-requirement-file data-upload-input>
                            <div class="upload-preview-card" data-upload-preview aria-live="polite"></div>
                            <small>Allowed: PDF, JPG, JPEG, or PNG, max 4MB.</small>
                            <div class="requirement-extra">
                                <div class="form-field">
                                    <label class="form-label" for="xray_date">Date of Examination <span class="required">*</span></label>
                                    <input id="xray_date" type="date" name="xray_date" class="form-control" value="{{ old('xray_date') }}" required data-requirement-extra-field>
                                </div>
                                <div class="form-field">
                                    <label class="form-label" for="xray_findings">Findings <span class="required">*</span></label>
                                    <div class="clinic-select-wrap" data-clinic-select>
                                        <select id="xray_findings" name="xray_findings" class="form-select clinic-select-native" required data-requirement-extra-field>
                                            <option value="">Select findings</option>
                                            <option value="Normal" {{ old('xray_findings') === 'Normal' ? 'selected' : '' }}>Normal</option>
                                            <option value="With Findings" {{ old('xray_findings') === 'With Findings' ? 'selected' : '' }}>With Findings</option>
                                            <option value="Not Sure / For Clinic Review" {{ old('xray_findings') === 'Not Sure / For Clinic Review' ? 'selected' : '' }}>Not Sure / For Clinic Review</option>
                                        </select>
                                        <button type="button" class="clinic-select-display" aria-haspopup="listbox" aria-expanded="false">Select findings</button>
                                        <div class="clinic-select-menu" role="listbox" aria-label="Chest X-ray findings options">
                                            <button type="button" class="clinic-select-option" data-select-value="Normal">Normal</button>
                                            <button type="button" class="clinic-select-option" data-select-value="With Findings">With Findings</button>
                                            <button type="button" class="clinic-select-option" data-select-value="Not Sure / For Clinic Review">Not Sure / For Clinic Review</button>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
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
                            <input type="file" name="student_photo" class="form-control" accept=".jpg,.jpeg,.png,image/jpeg,image/png" required data-upload-input data-preview-kind="image">
                            <div class="upload-preview-card" data-upload-preview aria-live="polite"></div>
                            <small>Allowed: JPG/PNG only, max 2MB.</small>
                        </div>
                    </div>
                    <div class="certify-row final-certification">
                        <input id="health_profile_certified" type="checkbox" name="health_profile_certified" value="1" required {{ old('health_profile_certified') ? 'checked' : '' }}>
                        <label for="health_profile_certified">
                            I certify that I have completely filled out all required sections of the official PUP Health Profile and that all information I provided is true and correct.
                        </label>
                    </div>
                    <div class="btn-row">
                        <button type="button" class="btn btn-health btn-health-back" data-step-back="4">
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
                            <span>Save Health Profile</span>
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

    <div class="submit-overlay" id="submitOverlay" aria-hidden="true">
        <div class="submit-card">
            <div class="submit-check" aria-hidden="true">
                <svg viewBox="0 0 24 24">
                    <path d="M20 6L9 17l-5-5"></path>
                </svg>
            </div>
            <strong>Thank you for submission.</strong>
        </div>
    </div>

    <script>
        (function () {
            const form = document.querySelector('form[action="{{ route('store.health.form') }}"]');
            const stepPanels = Array.from({ length: 5 }, (_, index) => document.getElementById(`stepPanel${index + 1}`));
            const stepChips = Array.from({ length: 5 }, (_, index) => document.getElementById(`chipStep${index + 1}`));
            const nextToStep2Btn = document.getElementById('nextToStep2');
            const referenceInput = document.getElementById('reference_number');
            const referencePanel = document.getElementById('referencePanel');
            const editReferenceBtn = document.getElementById('editReferenceBtn');
            const referenceDisplayValue = document.getElementById('referenceDisplayValue');
            const referenceVerifyStatus = document.getElementById('referenceVerifyStatus');
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
            const submitOverlay = document.getElementById('submitOverlay');
            const requirementFiles = document.querySelectorAll('[data-requirement-file]');
            const clinicSelects = Array.from(document.querySelectorAll('[data-clinic-select]'));
            const uploadInputs = Array.from(document.querySelectorAll('[data-upload-input]'));
            let currentStep = {{ $startStep }};
            let isSubmitting = false;

            function setReferenceEditor(open) {
                referencePanel?.classList.toggle('is-editing', open);
                editReferenceBtn?.setAttribute('aria-expanded', open ? 'true' : 'false');
                editReferenceBtn?.setAttribute(
                    'aria-label',
                    open ? 'Confirm admission reference' : 'Edit admission reference'
                );
                editReferenceBtn?.setAttribute(
                    'title',
                    open ? 'Confirm reference number' : 'Edit reference number'
                );

                if (open) {
                    window.setTimeout(() => {
                        referenceInput?.focus();
                        referenceInput?.select();
                    }, 40);
                }
            }

            editReferenceBtn?.addEventListener('click', () => {
                const isEditing = referencePanel?.classList.contains('is-editing');
                if (isEditing && referenceInput) {
                    const digits = referenceInput.value.replace(/\D/g, '').slice(0, 12);
                    referenceInput.value = digits.match(/.{1,4}/g)?.join('-') || '';
                    referenceInput.setCustomValidity('');

                    if (!referenceInput.checkValidity()) {
                        showValidationBubble(referenceInput);
                        referenceInput.focus();
                        return;
                    }

                    if (referenceDisplayValue) {
                        referenceDisplayValue.textContent = referenceInput.value;
                    }
                }

                setReferenceEditor(!isEditing);
            });

            function setStep(step) {
                const normalizedStep = Math.min(5, Math.max(1, Number(step) || 1));
                currentStep = normalizedStep;
                stepPanels.forEach((panel, index) => {
                    panel?.classList.toggle('is-hidden', index + 1 !== normalizedStep);
                });
                stepChips.forEach((chip, index) => {
                    chip?.classList.toggle('is-active', index + 1 === normalizedStep);
                });
            }

            function validateStep(step) {
                const panel = stepPanels[step - 1];
                if (!panel) return true;
                clearValidationBubble();
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

            function clearValidationBubble() {
                document.querySelectorAll('.validation-bubble').forEach((bubble) => bubble.remove());
                document.querySelectorAll('.validation-anchor').forEach((anchor) => {
                    anchor.classList.remove('validation-anchor');
                });
            }

            function showValidationBubble(field) {
                const anchor = field.closest('.form-field, .requirement-card, .certify-row, .upload-card')
                    || field.parentElement;
                if (!anchor) return;

                anchor.classList.add('validation-anchor');
                const bubble = document.createElement('div');
                bubble.className = 'validation-bubble';
                bubble.setAttribute('role', 'alert');
                if (field.validity?.rangeUnderflow) {
                    bubble.textContent = field.dataset.validationMessage
                        || 'Date must be January 1, 2020 or later.';
                } else if (field.validity?.rangeOverflow) {
                    bubble.textContent = 'Date must not be later than December 31, 2025.';
                } else if (field.validationMessage && field.dataset.validationMessage) {
                    bubble.textContent = field.dataset.validationMessage;
                } else {
                    bubble.textContent = 'Please fill this field.';
                }
                anchor.appendChild(bubble);
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

                disabilityTypeInput.required = isPwd;
                disabilityTypeInput.disabled = !isPwd;
                pwdProofInput.required = isPwd;
                pwdProofInput.disabled = !isPwd;
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
                    field.required = isVaccinated && field.dataset.doseRequired === 'true';

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
                const minimumSecondDoseDate = addDaysToIsoDate(firstDoseDate?.value, 28);

                if (secondDoseDate) {
                    secondDoseDate.min = minimumSecondDoseDate || '2021-03-01';
                    if (
                        minimumSecondDoseDate
                        && secondDoseDate.value
                        && secondDoseDate.value < minimumSecondDoseDate
                    ) {
                        secondDoseDate.dataset.validationMessage = 'The 2nd Dose must be at least 4 weeks after the 1st Dose.';
                    } else {
                        delete secondDoseDate.dataset.validationMessage;
                    }
                }

                const dateUsage = new Map();
                vaccineDateFields.forEach((field) => {
                    field.setCustomValidity('');
                    if (field !== secondDoseDate || !field.dataset.validationMessage) {
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
            form?.addEventListener('input', clearValidationBubble);
            form?.addEventListener('change', clearValidationBubble);
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
                fileInput.addEventListener('change', () => syncRequirementCard(fileInput));
            });
            clinicSelects.forEach(initializeClinicSelect);
            uploadInputs.forEach((input) => {
                renderUploadPreview(input);
                input.addEventListener('change', () => renderUploadPreview(input));
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

            nextToStep2Btn?.addEventListener('click', () => {
                if (referenceInput) {
                    const digits = referenceInput.value.replace(/\D/g, '').slice(0, 12);
                    referenceInput.value = digits.match(/.{1,4}/g)?.join('-') || '';
                    referenceInput.setCustomValidity('');
                }

                if (!referenceInput?.checkValidity()) {
                    setReferenceEditor(true);
                    showValidationBubble(referenceInput);
                    referenceInput?.focus();
                    return;
                }

                if (referenceDisplayValue) {
                    referenceDisplayValue.textContent = referenceInput.value;
                }
                setReferenceEditor(false);

                if (!validateStep(1)) {
                    return;
                }
                setStep(2);
                stepPanels[1]?.scrollIntoView({ behavior: 'smooth', block: 'start' });
            });

            referenceInput?.addEventListener('input', () => {
                const digits = referenceInput.value.replace(/\D/g, '').slice(0, 12);
                referenceInput.value = digits.match(/.{1,4}/g)?.join('-') || '';
                referenceInput.setCustomValidity('');
                referenceVerifyStatus?.classList.remove('is-success', 'is-error');

                if (referenceDisplayValue) {
                    referenceDisplayValue.textContent = referenceInput.value || 'No Reference Found';
                }

                if (referenceVerifyStatus) {
                    referenceVerifyStatus.textContent =
                        'Enter the reference number exactly as shown in your Admission System record.';
                }
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
                    setStep(targetStep);
                    stepPanels[targetStep - 1]?.scrollIntoView({ behavior: 'smooth', block: 'start' });
                });
            });
            form?.addEventListener('submit', (event) => {
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

                if (!form.checkValidity()) {
                    return;
                }

                event.preventDefault();
                isSubmitting = true;
                submitOverlay?.classList.add('is-open');
                const submitButtons = form.querySelectorAll('button[type="submit"], button[type="button"], a.btn');
                submitButtons.forEach((btn) => {
                    btn.setAttribute('aria-disabled', 'true');
                    btn.style.pointerEvents = 'none';
                    btn.style.opacity = '0.72';
                });

                window.setTimeout(() => {
                    form.submit();
                }, 850);
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
