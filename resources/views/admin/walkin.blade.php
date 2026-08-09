@extends('layouts.admin')
@section('title', 'Patient Intake')

@push('styles')
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
<style>
    .notification-toast {
        position: fixed; top: 25px; right: 25px;
        background: linear-gradient(135deg, #15803d, #166534); color: #ffffff; padding: 15px 20px;
        border-radius: 12px; box-shadow: 0 10px 25px rgba(0,0,0,0.15);
        z-index: 10000; display: flex; align-items: center;
        justify-content: space-between; min-width: 380px;
        gap: 16px;
        animation: slideInRight 0.4s cubic-bezier(0.175, 0.885, 0.32, 1.275);
    }
    .toast-copy {
        display: flex;
        align-items: center;
        gap: 12px;
        color: #ffffff;
    }
    .toast-badge {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        min-width: 48px;
        height: 28px;
        padding: 0 10px;
        border-radius: 999px;
        background: rgba(255,255,255,0.18);
        border: 1px solid rgba(255,255,255,0.28);
        color: #ffffff;
        font-size: 11px;
        font-weight: 900;
        letter-spacing: 0.08em;
        text-transform: uppercase;
        flex-shrink: 0;
    }
    .toast-title {
        display: block;
        font-size: 14px;
        font-weight: 800;
        color: #ffffff;
        line-height: 1.2;
    }
    .toast-subtitle {
        display: block;
        font-size: 11px;
        opacity: 0.95;
        color: rgba(255,255,255,0.92);
        margin-top: 2px;
    }
    .btn-toast-action {
        background: rgba(255,255,255,0.25); border: 1px solid rgba(255,255,255,0.4);
        color: #ffffff; padding: 6px 14px; border-radius: 6px; font-size: 12px; font-weight: 700; cursor: pointer;
        flex-shrink: 0;
    }

    .mode-header {
        padding: 22px 24px; color: white; display: flex; align-items: center;
        justify-content: center; gap: 12px; border-radius: 12px 12px 0 0;
        margin: -25px -25px 25px -25px;
        transition: background 0.4s ease;
    }
    .bg-scan { background: linear-gradient(135deg, #7f1d1d, #991b1b 55%, #b91c1c); }
    .bg-register { background: linear-gradient(135deg, #1e293b, #334155 58%, #475569); }

    .mode-header-badge {
        width: 46px;
        height: 46px;
        border-radius: 14px;
        background: rgba(255,255,255,0.16);
        border: 1px solid rgba(255,255,255,0.2);
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 12px;
        font-weight: 900;
        letter-spacing: 0.08em;
    }

    .mode-header-copy {
        text-align: left;
    }

    .mode-header-copy h3 {
        color: #ffffff;
    }

    .mode-header-copy p {
        margin: 4px 0 0;
        font-size: 12px;
        color: rgba(255, 255, 255, 0.96);
        opacity: 1;
        line-height: 1.45;
        letter-spacing: 0.01em;
    }

    #dynamicHeader #headerTitle,
    #dynamicHeader #headerSubtitle {
        color: #ffffff;
    }

    .scanner-box {
        width: 100% !important; max-width: 480px; aspect-ratio: 16 / 9;
        margin: 0 auto; background: radial-gradient(circle at top, #1f2937 0%, #0f172a 58%, #020617 100%);
        border: 2px solid #cbd5e1;
        border-radius: 16px; overflow: hidden; position: relative;
        box-shadow: inset 0 0 0 1px rgba(255,255,255,0.04), 0 20px 40px rgba(15, 23, 42, 0.18);
    }
    .scanner-box video { object-fit: cover !important; }
    .scanner-box::before {
        content: '';
        position: absolute;
        inset: 16px;
        border: 1px dashed rgba(255,255,255,0.18);
        border-radius: 12px;
        pointer-events: none;
        z-index: 2;
    }

    .scan-stage {
        transform-style: preserve-3d;
        transform-origin: center;
        transition: transform 0.55s cubic-bezier(0.22, 1, 0.36, 1), opacity 0.25s ease;
    }

    .scan-stage.is-flipping {
        transform: rotateY(180deg) scale(0.98);
        opacity: 0.82;
    }

    .scan-line-overlay {
        position: absolute;
        left: 8%;
        width: 84%;
        height: 4px;
        border-radius: 999px;
        background: linear-gradient(90deg, rgba(16,185,129,0) 0%, rgba(52,211,153,0.95) 20%, rgba(167,243,208,1) 50%, rgba(52,211,153,0.95) 80%, rgba(16,185,129,0) 100%);
        z-index: 10;
        box-shadow: 0 0 14px rgba(110, 231, 183, 0.95), 0 0 28px rgba(16, 185, 129, 0.45);
        animation: scan-animation 2.1s cubic-bezier(0.4, 0, 0.2, 1) infinite;
    }
    .scan-line-overlay::after {
        content: '';
        position: absolute;
        left: 0;
        right: 0;
        top: -22px;
        height: 48px;
        background: linear-gradient(180deg, rgba(16,185,129,0) 0%, rgba(52,211,153,0.16) 45%, rgba(16,185,129,0) 100%);
        filter: blur(4px);
        pointer-events: none;
    }

    .form-control { display: block; width: 100%; padding: 12px; border: 1px solid #cbd5e1; border-radius: 8px; margin-bottom: 10px; }
    
    /* Password Toggle Styling Fix */
    .password-wrapper { position: relative; width: 100%; margin-bottom: 10px; }
    .password-wrapper .form-control { margin-bottom: 0; padding-right: 45px; }
    .password-toggle {
        position: absolute; right: 15px; top: 50%;
        transform: translateY(-50%);
        color: #64748b; cursor: pointer; z-index: 10;
        font-size: 1.1rem;
    }

    #scan-loading {
        display: none; position: absolute; inset: 0;
        background: rgba(255, 255, 255, 0.9); z-index: 20;
        flex-direction: column; justify-content: center; align-items: center;
        border-radius: 12px;
    }
    .spinner {
        width: 40px; height: 40px; border: 4px solid #f3f3f3;
        border-top: 4px solid #8B0000; border-radius: 50%;
        animation: spin 1s linear infinite;
    }

    .scan-method-bar {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 12px;
        margin-bottom: 18px;
        padding: 14px 16px;
        border: 1px solid #e5e7eb;
        border-radius: 12px;
        background: linear-gradient(135deg, #fffaf9, #f8fafc);
        box-shadow: 0 10px 24px rgba(15, 23, 42, 0.04);
    }

    .scan-method-title {
        margin: 0;
        font-size: 15px;
        font-weight: 800;
        color: #0f172a;
    }

    .scan-method-note {
        margin: 4px 0 0;
        font-size: 12px;
        color: #64748b;
        line-height: 1.5;
    }

    .btn-scan-switch {
        border: 1px solid #cbd5e1;
        background: #ffffff;
        color: #334155;
        border-radius: 999px;
        padding: 10px 14px;
        font-size: 12px;
        font-weight: 800;
        cursor: pointer;
        white-space: nowrap;
        box-shadow: 0 6px 14px rgba(15, 23, 42, 0.06);
    }

    .walkin-strip-card {
        position: relative;
        overflow: hidden;
    }

    .patient-intake-entry-shell {
        margin-top: 8px !important;
    }

    .walkin-strip-card::before {
        content: "";
        position: absolute;
        top: 0;
        left: 14px;
        right: 14px;
        height: 5px;
        background: #70131B;
        border-radius: 999px;
        pointer-events: none;
        z-index: 2;
    }

    .scan-method-badge {
        display: inline-flex;
        align-items: center;
        gap: 6px;
        margin-top: 10px;
        padding: 6px 10px;
        border-radius: 999px;
        background: #fff7ed;
        border: 1px solid #fed7aa;
        color: #9a3412;
        font-size: 11px;
        font-weight: 800;
        text-transform: uppercase;
        letter-spacing: 0.04em;
    }

    .intake-heading-kicker {
        margin: 0 0 8px;
        font-size: 16px;
        font-weight: 800;
        letter-spacing: 1px;
        color: #8b0000;
        text-transform: uppercase;
    }

    .intake-heading-title {
        margin: 0;
        font-size: 22px;
        font-weight: 800;
        color: #0f172a;
    }

    .intake-heading-copy {
        margin: 10px 0 0;
        color: #475569;
        max-width: 680px;
    }

    .intake-options-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(220px, 1fr));
        gap: 16px;
        margin-top: 24px;
    }

    .intake-option-link {
        text-decoration: none;
        color: inherit;
    }

    .intake-option-card {
        position: relative;
        height: 100%;
        min-height: 314px;
        padding: 20px;
        border-radius: 16px;
        border: 1px solid rgba(112, 19, 27, 0.46);
        background: linear-gradient(135deg, #70131B, #8f2230);
        color: #ffffff;
        box-shadow:
            inset 0 -3px 0 rgba(250, 204, 21, 0.72),
            0 10px 24px rgba(112, 19, 27, 0.18);
        overflow: hidden;
        transition: transform .2s ease, box-shadow .2s ease, border-color .2s ease, color .2s ease;
    }

    .intake-option-card:hover {
        background: #facc15 !important;
        background-image: none !important;
        color: #111111 !important;
        transform: translateY(-8px);
        box-shadow:
            0 0 0 3px rgba(250, 204, 21, 0.12),
            0 20px 30px rgba(139, 0, 0, 0.22);
        border-color: #facc15;
    }

    .intake-option-card:hover .intake-option-chip svg,
    .intake-option-card:hover .intake-option-icon-wrap svg {
        stroke: #ffffff;
        color: #ffffff;
    }

    .intake-option-card::before {
        content: "";
        position: absolute;
        inset: 0;
        pointer-events: none;
        background: linear-gradient(180deg, rgba(255,255,255,0.08) 0%, rgba(255,255,255,0) 38%);
        z-index: 0;
    }

    .intake-option-card::after {
        content: "";
        position: absolute;
        top: -38%;
        bottom: -38%;
        left: -135%;
        width: 34%;
        opacity: 0;
        background: linear-gradient(105deg, rgba(255, 255, 255, 0) 0%, rgba(255, 246, 184, 0.18) 42%, rgba(255, 246, 184, 0.54) 50%, rgba(255, 246, 184, 0.18) 58%, rgba(255, 255, 255, 0) 100%);
        transform: translateX(0) skewX(-18deg);
        pointer-events: none;
        z-index: 0;
    }

    .intake-option-card:hover::after {
        animation: intakeSweep .92s ease both;
    }

    @keyframes intakeSweep {
        0% {
            opacity: 0;
            transform: translateX(0) skewX(-18deg);
        }
        18% {
            opacity: .72;
        }
        72% {
            opacity: .72;
        }
        100% {
            opacity: 0;
            transform: translateX(820%) skewX(-18deg);
        }
    }

    .intake-option-card:hover .intake-option-title,
    .intake-option-card:hover .intake-option-copy {
        color: #70131B !important;
    }

    .intake-option-card:hover .intake-option-chip,
    .intake-option-card:hover .intake-option-icon-wrap {
        background: #70131B;
        color: #ffffff;
        border-color: rgba(112, 19, 27, 0.62);
    }

    .intake-option-chip {
        position: absolute;
        top: 12px;
        right: 12px;
        width: 28px;
        height: 28px;
        border-radius: 999px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        z-index: 2;
        border: 1px solid rgba(255, 248, 196, 0.72);
        box-shadow: 0 8px 14px rgba(15, 23, 42, 0.14);
        transition: background .2s ease, color .2s ease, border-color .2s ease;
    }

    .intake-option-chip svg {
        width: 14px;
        height: 14px;
        stroke: currentColor;
        stroke-width: 2.2;
        fill: none;
    }

    .intake-option-icon-wrap {
        width: 58px;
        height: 58px;
        border-radius: 16px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        margin-bottom: 14px;
        position: relative;
        z-index: 1;
        animation: intakeFloat 3.8s ease-in-out infinite;
        transition: background .2s ease, color .2s ease, border-color .2s ease;
        background: rgba(255, 248, 196, 0.12);
        color: #ffffff;
        border: 1px solid rgba(255, 248, 196, 0.16);
    }

    .intake-option-icon-wrap::after {
        content: "";
        position: absolute;
        left: 10%;
        right: 10%;
        bottom: -10px;
        height: 14px;
        border-radius: 999px;
        filter: blur(8px);
        opacity: .6;
        z-index: -1;
        background: radial-gradient(circle, rgba(0, 0, 0, 0.44) 0%, rgba(0, 0, 0, 0.22) 48%, transparent 86%);
    }

    .intake-option-icon-wrap svg {
        width: 24px;
        height: 24px;
        stroke: currentColor;
        stroke-width: 2.1;
        fill: none;
    }

    .intake-option-title {
        margin: 0 0 8px;
        font-size: 18px;
        font-weight: 800;
        color: #111827;
        position: relative;
        z-index: 1;
        transition: color .2s ease;
    }

    .intake-option-copy {
        margin: 0;
        color: #475569;
        line-height: 1.55;
        position: relative;
        z-index: 1;
        transition: color .2s ease;
    }

    .intake-option-card .intake-option-title,
    .intake-option-card .intake-option-copy {
        color: #ffffff !important;
    }

    .intake-option-registration {
        background: linear-gradient(135deg, #70131B, #8f2230);
    }

    .intake-option-registration.is-active {
        border: 2px solid #facc15;
    }

    .intake-option-registration .intake-option-icon-wrap {
        background: rgba(255, 248, 196, 0.12);
        color: #ffffff;
    }

    .intake-option-registration .intake-option-chip {
        background: rgba(255, 248, 196, 0.12);
        color: #ffffff;
    }

    .intake-option-scan {
        background: linear-gradient(135deg, #70131B, #8f2230);
    }

    .intake-option-scan.is-active {
        border: 2px solid #facc15;
    }

    .intake-option-scan .intake-option-icon-wrap {
        background: rgba(255, 248, 196, 0.12);
        color: #ffffff;
    }

    .intake-option-scan .intake-option-chip {
        background: rgba(255, 248, 196, 0.12);
        color: #ffffff;
    }

    .intake-option-assisted {
        background: linear-gradient(135deg, #70131B, #8f2230);
    }

    .intake-option-assisted.is-active {
        border: 2px solid #facc15;
    }

    .intake-option-assisted .intake-option-icon-wrap {
        background: rgba(255, 248, 196, 0.12);
        color: #ffffff;
    }

    .intake-option-assisted .intake-option-chip {
        background: rgba(255, 248, 196, 0.12);
        color: #ffffff;
    }

    .intake-option-applicant {
        background: linear-gradient(135deg, #70131B, #8f2230);
    }

    .intake-option-applicant.is-active {
        border: 2px solid #facc15;
    }

    .intake-option-applicant .intake-option-icon-wrap {
        background: rgba(255, 248, 196, 0.12);
        color: #ffffff;
    }

    .intake-option-applicant .intake-option-chip {
        background: rgba(255, 248, 196, 0.12);
        color: #ffffff;
    }

    html[data-theme="dark"] .intake-heading-title,
    html[data-theme="dark"] .intake-heading-copy {
        color: #ffffff;
    }

    html[data-theme="dark"] .intake-heading-kicker {
        color: #ffffff;
    }

    html[data-theme="dark"] .scan-method-title,
    html[data-theme="dark"] .scan-method-note {
        color: #ffffff;
    }

    html[data-theme="dark"] .intake-option-card {
        border-color: rgba(250, 204, 21, 0.62);
        box-shadow:
            inset 0 -3px 0 rgba(250, 204, 21, 0.92),
            0 14px 26px rgba(0, 0, 0, 0.22);
        background: #70131B;
    }

    html[data-theme="dark"] .intake-option-card::after {
        background: linear-gradient(105deg, rgba(255, 255, 255, 0) 0%, rgba(255, 248, 196, 0.52) 48%, rgba(255, 255, 255, 0) 100%);
    }

    html[data-theme="dark"] .intake-option-card::before {
        background: none;
    }

    html[data-theme="dark"] .intake-option-title,
    html[data-theme="dark"] .intake-option-copy {
        color: #ffffff !important;
    }

    html[data-theme="dark"] .intake-option-registration,
    html[data-theme="dark"] .intake-option-scan,
    html[data-theme="dark"] .intake-option-assisted,
    html[data-theme="dark"] .intake-option-applicant {
        background: #70131B;
    }

    html[data-theme="dark"] .intake-option-registration.is-active,
    html[data-theme="dark"] .intake-option-scan.is-active,
    html[data-theme="dark"] .intake-option-assisted.is-active,
    html[data-theme="dark"] .intake-option-applicant.is-active {
        background: #70131B;
    }

    html[data-theme="dark"] .intake-option-registration.is-active,
    html[data-theme="dark"] .intake-option-scan.is-active,
    html[data-theme="dark"] .intake-option-assisted.is-active,
    html[data-theme="dark"] .intake-option-applicant.is-active {
        border-color: #facc15;
    }

    html[data-theme="dark"] .intake-option-card:hover .intake-option-title,
    html[data-theme="dark"] .intake-option-card:hover .intake-option-copy {
        color: #70131B !important;
    }

    html[data-theme="dark"] .intake-option-card:hover {
        background: #facc15 !important;
        background-image: none !important;
        border-color: #facc15;
    }

    html[data-theme="dark"] .intake-option-card:hover .intake-option-chip,
    html[data-theme="dark"] .intake-option-card:hover .intake-option-icon-wrap {
        background: #70131B;
        color: #ffffff;
        border-color: rgba(112, 19, 27, 0.62);
    }

    .scan-surface {
        padding: 16px;
        border-radius: 16px;
        background: linear-gradient(180deg, #ffffff, #f8fafc);
        border: 1px solid #e2e8f0;
        box-shadow: inset 0 1px 0 rgba(255,255,255,0.9), 0 12px 30px rgba(15, 23, 42, 0.05);
    }

    .scan-inline-note {
        margin: 0 0 14px;
        padding: 10px 12px;
        border-radius: 10px;
        background: #fff7ed;
        border: 1px solid #fed7aa;
        color: #9a3412;
        font-size: 12px;
        line-height: 1.5;
    }

    html[data-theme="dark"] .applicant-modal-shell .scan-surface {
        background: #111827 !important;
        border-color: rgba(250, 204, 21, 0.18) !important;
        box-shadow: inset 0 1px 0 rgba(255,255,255,0.04), 0 16px 34px rgba(0, 0, 0, 0.26) !important;
        color: #f8fafc !important;
    }

    html[data-theme="dark"] .applicant-modal-shell .scan-inline-note {
        background: rgba(112, 19, 27, 0.34) !important;
        border-color: rgba(250, 204, 21, 0.24) !important;
        color: #fde68a !important;
    }

    html[data-theme="dark"] .applicant-modal-shell .ocr-camera-shell,
    html[data-theme="dark"] .applicant-modal-shell .scanner-box {
        background: #0f172a !important;
        border-color: rgba(250, 204, 21, 0.14) !important;
    }

    html[data-theme="dark"] .applicant-modal-shell .ocr-camera-idle {
        background: #0f172a !important;
        color: #f8fafc !important;
    }

    html[data-theme="dark"] .applicant-modal-shell .ocr-result-panel,
    html[data-theme="dark"] .applicant-modal-shell .ocr-meta,
    html[data-theme="dark"] .applicant-modal-shell .ocr-lock-badge {
        background: #111827 !important;
        border-color: rgba(250, 204, 21, 0.18) !important;
        color: #f8fafc !important;
    }

    html[data-theme="dark"] .applicant-modal-shell .ocr-result-label,
    html[data-theme="dark"] .applicant-modal-shell .ocr-result-help {
        color: #cbd5e1 !important;
    }

    html[data-theme="dark"] .applicant-modal-shell .ocr-status.info {
        background: rgba(59, 130, 246, 0.16) !important;
        border-color: rgba(96, 165, 250, 0.28) !important;
        color: #bfdbfe !important;
    }

    html[data-theme="dark"] .applicant-modal-shell .ocr-status.success,
    html[data-theme="dark"] .applicant-modal-shell .ocr-status.approved {
        background: rgba(16, 185, 129, 0.14) !important;
        border-color: rgba(52, 211, 153, 0.30) !important;
        color: #bbf7d0 !important;
    }

    html[data-theme="dark"] .applicant-modal-shell .ocr-status.error {
        background: rgba(127, 29, 29, 0.34) !important;
        border-color: rgba(248, 113, 113, 0.34) !important;
        color: #fecaca !important;
    }

    .ocr-guide {
        position: absolute;
        inset: 14px;
        border: 2px solid rgba(255,255,255,0.7);
        border-radius: 18px;
        z-index: 12;
        pointer-events: none;
        box-shadow: inset 0 0 0 1px rgba(255,255,255,0.12);
    }

    .ocr-guide::before,
    .ocr-guide::after {
        content: '';
        position: absolute;
        width: 52px;
        height: 52px;
        border: 3px solid #f8fafc;
        border-radius: 12px;
    }

    .ocr-guide::before {
        top: -2px;
        left: -2px;
        border-right: 0;
        border-bottom: 0;
    }

    .ocr-guide::after {
        right: -2px;
        bottom: -2px;
        border-left: 0;
        border-top: 0;
    }

    .ocr-guide-label {
        position: absolute;
        left: 50%;
        top: 18px;
        transform: translateX(-50%);
        z-index: 13;
        padding: 6px 12px;
        border-radius: 999px;
        background: rgba(15, 23, 42, 0.72);
        color: #ffffff;
        font-size: 11px;
        font-weight: 800;
        letter-spacing: 0.05em;
        text-transform: uppercase;
        white-space: nowrap;
        pointer-events: none;
    }

    .ocr-actions {
        display: flex;
        gap: 10px;
        flex-wrap: wrap;
        margin-top: 16px;
    }

    .btn-ocr {
        border: none;
        border-radius: 12px;
        padding: 12px 16px;
        font-size: 12px;
        font-weight: 800;
        letter-spacing: 0.03em;
        cursor: pointer;
        transition: transform 0.18s ease, box-shadow 0.18s ease, filter 0.18s ease;
    }

    .btn-ocr:hover,
    .btn-ocr:focus {
        transform: translateY(-1px);
        filter: brightness(1.02);
        outline: none;
    }

    .btn-ocr-primary {
        background: linear-gradient(135deg, #0f172a, #1e293b 58%, #334155);
        color: #ffffff;
        box-shadow: 0 12px 24px rgba(15, 23, 42, 0.16);
    }

    .btn-ocr-secondary {
        background: linear-gradient(135deg, #7f1d1d, #991b1b 55%, #b91c1c);
        color: #ffffff;
        box-shadow: 0 12px 24px rgba(127, 29, 29, 0.22);
    }

    .btn-ocr:disabled,
    .manual-find-btn:disabled {
        opacity: 0.65;
        cursor: not-allowed;
        transform: none;
        filter: none;
    }

    .ocr-camera-controls {
        display: flex;
        gap: 10px;
        margin: 14px 0;
    }

    .ocr-camera-controls .btn-ocr {
        flex: 1 1 180px;
    }

    .ocr-camera-idle {
        position: absolute;
        inset: 0;
        z-index: 5;
        display: flex;
        align-items: center;
        justify-content: center;
        padding: 24px;
        background: #0f172a;
        color: #e2e8f0;
        text-align: center;
        font-size: 13px;
        font-weight: 700;
        line-height: 1.6;
    }

    .ocr-camera-shell {
        position: relative;
        width: 100%;
        max-width: 480px;
        margin: 0 auto;
    }

    .ocr-camera-idle.is-hidden {
        display: none;
    }

    .ocr-result-panel {
        margin-top: 18px;
        padding: 16px;
        border-radius: 14px;
        border: 1px solid #e2e8f0;
        background: linear-gradient(180deg, #ffffff, #f8fafc);
    }

    .ocr-result-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
        gap: 12px;
    }

    .ocr-result-label {
        margin: 0 0 6px;
        font-size: 11px;
        font-weight: 800;
        letter-spacing: 0.06em;
        text-transform: uppercase;
        color: #475569;
    }

    .ocr-result-help {
        margin: 0 0 12px;
        font-size: 12px;
        color: #64748b;
        line-height: 1.55;
    }

    .ocr-status {
        margin-top: 12px;
        padding: 12px 14px;
        border-radius: 12px;
        font-size: 12px;
        line-height: 1.55;
        display: none;
    }

    .ocr-status.info {
        display: block;
        background: #eff6ff;
        border: 1px solid #bfdbfe;
        color: #1d4ed8;
    }

    .ocr-status.success {
        display: block;
        background: #ecfdf5;
        border: 1px solid #a7f3d0;
        color: #047857;
    }

    .ocr-status.error {
        display: block;
        background: #fff1f2;
        border: 1px solid #fecdd3;
        color: #be123c;
    }

    .manual-lookup-status {
        display: none;
        align-items: flex-start;
        gap: 10px;
        margin-top: 12px;
        padding: 12px 14px;
        border: 1px solid transparent;
        border-radius: 10px;
        font-size: 12px;
        font-weight: 800;
        line-height: 1.5;
    }
    .manual-lookup-status.is-visible { display: flex; }
    .manual-lookup-status.is-loading { background: #fffbeb; border-color: #fde68a; color: #92400e; }
    .manual-lookup-status.is-success { background: #ecfdf5; border-color: #a7f3d0; color: #047857; }
    .manual-lookup-status.is-error { background: #fff1f2; border-color: #fecdd3; color: #9f1239; }
    .manual-lookup-status .spinner {
        width: 18px;
        height: 18px;
        flex: 0 0 18px;
        border-width: 2px;
    }

    .ocr-status.approved {
        display: block;
        padding: 18px 20px;
        border: 1px solid rgba(16, 185, 129, 0.35);
        border-left: 5px solid #059669;
        border-radius: 10px;
        background: linear-gradient(135deg, #ecfdf5, #ffffff);
        color: #065f46;
        font-size: 15px;
        font-weight: 900;
        line-height: 1.5;
        box-shadow: 0 14px 32px rgba(5, 150, 105, 0.14);
    }

    html[data-theme="dark"] .walkin-strip-card::before {
        background: #facc15;
    }

    .ocr-meta {
        display: inline-flex;
        align-items: center;
        gap: 6px;
        margin-top: 8px;
        padding: 6px 10px;
        border-radius: 999px;
        background: #f8fafc;
        border: 1px solid #e2e8f0;
        color: #334155;
        font-size: 11px;
        font-weight: 700;
    }

    .ocr-lock-badge {
        display: inline-flex;
        align-items: center;
        gap: 6px;
        margin-left: 8px;
        padding: 6px 10px;
        border-radius: 999px;
        background: #ecfdf5;
        border: 1px solid #a7f3d0;
        color: #047857;
        font-size: 11px;
        font-weight: 800;
    }

    .manual-find-btn {
        min-width: 180px;
        min-height: 48px;
        padding: 0 26px;
        border: none;
        border-radius: 12px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        background: linear-gradient(135deg, #7f1d1d, #991b1b 55%, #b91c1c);
        color: #ffffff;
        font-weight: 800;
        font-size: 0.92rem;
        letter-spacing: 0.03em;
        box-shadow: 0 12px 24px rgba(127, 29, 29, 0.24);
        transition: transform 0.18s ease, box-shadow 0.18s ease, filter 0.18s ease;
    }

    .manual-find-btn:hover,
    .manual-find-btn:focus {
        transform: translateY(-1px);
        box-shadow: 0 16px 28px rgba(127, 29, 29, 0.3);
        filter: brightness(1.03);
        outline: none;
    }

    .manual-find-btn:active {
        transform: translateY(0);
        box-shadow: 0 8px 18px rgba(127, 29, 29, 0.22);
    }

    .applicant-modal-backdrop {
        position: fixed;
        inset: 0;
        z-index: 1300;
        display: none;
        align-items: center;
        justify-content: center;
        padding: 26px 18px;
        background: rgba(15, 23, 42, 0.52);
        backdrop-filter: blur(10px);
    }

    .applicant-modal-backdrop.show {
        display: flex;
    }

    .applicant-modal-shell {
        width: min(1180px, 100%);
        max-height: calc(100vh - 40px);
        overflow: hidden;
        border-radius: 24px;
        background: linear-gradient(180deg, rgba(255,255,255,0.96), rgba(248,250,252,0.98));
        box-shadow: 0 26px 60px rgba(15, 23, 42, 0.24);
        border: 1px solid rgba(255,255,255,0.62);
        border-bottom: 4px solid #70131B;
    }

    .applicant-modal-head {
        position: relative;
        display: flex;
        align-items: flex-start;
        justify-content: space-between;
        gap: 16px;
        padding: 18px 20px 14px;
        background: linear-gradient(135deg, #7f1d1d, #991b1b 55%, #b91c1c);
        color: #ffffff;
    }

    .applicant-modal-head-main {
        display: flex;
        align-items: flex-start;
        gap: 14px;
        min-width: 0;
    }

    .applicant-modal-head-actions {
        position: absolute;
        left: 50%;
        top: 50%;
        transform: translate(-50%, -50%);
        display: flex;
        align-items: center;
        justify-content: center;
        gap: 10px;
        min-width: max-content;
        z-index: 1;
    }

    .applicant-modal-head-badge {
        width: 46px;
        height: 46px;
        flex: 0 0 auto;
        border-radius: 14px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        background: rgba(255, 255, 255, 0.16);
        border: 1px solid rgba(255, 255, 255, 0.24);
        color: #ffffff;
        font-size: 12px;
        font-weight: 900;
        letter-spacing: 0.08em;
        box-shadow: 0 12px 24px rgba(15, 23, 42, 0.16);
    }

    .applicant-modal-head-copy {
        min-width: 0;
    }

    .applicant-modal-head h3 {
        margin: 0;
        font-size: 1rem;
        font-weight: 800;
        text-transform: uppercase;
        letter-spacing: 0.08em;
        color: #ffffff !important;
    }

    .applicant-modal-head p {
        margin: 6px 0 0;
        color: rgba(255, 255, 255, 0.92) !important;
        font-size: 12px;
        line-height: 1.55;
        max-width: 760px;
    }

    .applicant-modal-head-copy,
    .applicant-modal-head-copy h3,
    .applicant-modal-head-copy p {
        color: #ffffff !important;
    }

    .applicant-modal-head-copy .scan-method-badge {
        margin-top: 8px;
        margin-bottom: 0;
    }

    .applicant-modal-head-actions .btn-scan-switch {
        position: relative;
        overflow: hidden;
        background: rgba(255, 255, 255, 0.96);
        color: #70131B;
        border-color: rgba(255, 255, 255, 0.86);
        box-shadow: 0 8px 18px rgba(15, 23, 42, 0.12);
        min-height: 44px;
        padding: 11px 18px;
        font-size: 13px;
        gap: 8px;
        transition: color .08s linear, transform .18s ease, box-shadow .18s ease, border-color .18s ease, background .18s ease;
    }

    .applicant-modal-head-actions .btn-scan-switch svg {
        width: 15px;
        height: 15px;
        flex: 0 0 auto;
        stroke-width: 2.2;
    }

    .applicant-modal-head-actions .btn-scan-switch::after {
        content: "";
        position: absolute;
        inset: 0;
        background:
            linear-gradient(120deg,
                rgba(255, 248, 196, 0) 0%,
                rgba(255, 239, 181, 0.14) 22%,
                rgba(255, 239, 181, 0.52) 48%,
                rgba(255, 239, 181, 0.14) 72%,
                rgba(255, 248, 196, 0) 100%);
        transform: translateX(-135%);
        transition: transform 1.2s ease;
        pointer-events: none;
    }

    .applicant-modal-head-actions .btn-scan-switch:hover,
    .applicant-modal-head-actions .btn-scan-switch:focus {
        transform: translateY(-1px);
        border-color: #facc15;
        background: #fff8e1;
        box-shadow:
            0 0 0 3px rgba(250, 204, 21, 0.18),
            0 14px 24px rgba(112, 19, 27, 0.16);
        outline: none;
    }

    .applicant-modal-head-actions .btn-scan-switch:hover::after,
    .applicant-modal-head-actions .btn-scan-switch:focus::after {
        transform: translateX(135%);
    }

    .applicant-modal-head-actions .scan-method-badge {
        margin-top: 0;
        background: rgba(255, 244, 214, 0.96);
        border-color: rgba(254, 215, 170, 0.94);
        color: #9a3412;
    }

    .applicant-modal-close {
        width: 40px;
        height: 40px;
        min-width: 40px;
        min-height: 40px;
        padding: 0;
        flex: 0 0 40px;
        border-radius: 999px;
        position: relative;
        overflow: hidden;
        border: 1px solid #8f2230;
        background: linear-gradient(135deg, #70131B, #8f2230);
        color: #ffffff;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        box-shadow:
            0 0 0 3px rgba(112, 19, 27, 0.12),
            0 10px 22px rgba(112, 19, 27, 0.20);
        cursor: pointer;
        transition: color .08s linear, transform .18s ease, box-shadow .18s ease, border-color .18s ease;
        z-index: 0;
    }

    .applicant-modal-close::after {
        content: "";
        position: absolute;
        inset: 0;
        background:
            linear-gradient(120deg,
                rgba(255, 248, 196, 0) 0%,
                rgba(255, 239, 181, 0.14) 22%,
                rgba(255, 239, 181, 0.52) 48%,
                rgba(255, 239, 181, 0.14) 72%,
                rgba(255, 248, 196, 0) 100%);
        transform: translateX(-135%);
        transition: transform 1.5s ease;
        z-index: -1;
    }

    .applicant-modal-close svg {
        width: 18px;
        height: 18px;
        stroke-width: 2.2;
        flex: 0 0 auto;
    }

    .applicant-modal-close:hover,
    .applicant-modal-close:focus {
        transform: translateY(-1px);
        border-color: #facc15;
        background: #facc15;
        color: #70131B;
        box-shadow:
            0 0 0 3px rgba(250, 204, 21, 0.18),
            0 14px 24px rgba(112, 19, 27, 0.16);
        outline: none;
    }

    .applicant-modal-close:hover::after,
    .applicant-modal-close:focus::after {
        transform: translateX(135%);
    }

    .applicant-ref-head-actions {
        display: flex;
        align-items: center;
        justify-content: flex-end;
        gap: 18px;
        margin-left: auto;
        flex: 0 0 auto;
    }

    .applicant-final-review-total-badge {
        display: none;
        align-items: center;
        gap: 8px;
        min-height: 38px;
        padding: 8px 14px;
        border-radius: 999px;
        border: 1px solid rgba(250, 204, 21, 0.62);
        background: rgba(255, 247, 214, 0.16);
        color: #ffffff;
        font-size: 11px;
        font-weight: 900;
        letter-spacing: 0.06em;
        text-transform: uppercase;
        white-space: nowrap;
        box-shadow: 0 10px 22px rgba(15, 23, 42, 0.14);
    }

    #applicantRefModal .applicant-modal-shell.is-final-review-workflow .applicant-final-review-total-badge {
        display: inline-flex;
    }

    .applicant-final-review-total-badge span {
        color: #ffffff !important;
    }

    .applicant-final-review-total-badge * {
        color: #ffffff !important;
    }

    .applicant-final-review-total-badge strong {
        color: #ffffff !important;
        font-size: 15px;
        line-height: 1;
    }

    .applicant-modal-body {
        padding: 18px;
        overflow-y: auto;
        overflow-x: hidden;
        max-height: calc(100vh - 140px);
        scroll-behavior: smooth;
    }

    .applicant-modal-body::-webkit-scrollbar {
        width: 6px;
    }

    .applicant-modal-body::-webkit-scrollbar-track {
        background: rgba(0, 0, 0, 0.06);
        border-radius: 3px;
    }

    .applicant-modal-body::-webkit-scrollbar-thumb {
        background: rgba(112, 19, 27, 0.4);
        border-radius: 3px;
    }

    .applicant-modal-body::-webkit-scrollbar-thumb:hover {
        background: rgba(112, 19, 27, 0.6);
    }

    html[data-theme="dark"] .applicant-modal-body::-webkit-scrollbar-track {
        background: rgba(255, 255, 255, 0.08);
    }

    html[data-theme="dark"] .applicant-modal-body::-webkit-scrollbar-thumb {
        background: rgba(250, 204, 21, 0.3);
    }

    html[data-theme="dark"] .applicant-modal-body::-webkit-scrollbar-thumb:hover {
        background: rgba(250, 204, 21, 0.5);
    }

    .applicant-modal-grid {
        display: grid;
        grid-template-columns: minmax(0, 1.15fr) minmax(320px, 0.85fr);
        gap: 18px;
        align-items: start;
    }

    .applicant-modal-panel {
        border-radius: 18px;
        border: 1px solid rgba(148, 163, 184, 0.16);
        background: rgba(255,255,255,0.88);
        box-shadow: 0 14px 28px rgba(15, 23, 42, 0.06);
        padding: 16px;
    }

    .applicant-modal-panel .scan-method-bar {
        margin-bottom: 14px;
    }

    .applicant-modal-panel .scan-method-title,
    .applicant-modal-panel .scan-method-note {
        color: #000000 !important;
    }

    .applicant-modal-panel .ocr-actions {
        margin-top: 14px;
    }

    .applicant-modal-panel .ocr-result-panel {
        display: block;
        margin-top: 0;
        background: transparent;
        border: none;
        padding: 0;
        box-shadow: none;
    }

    .applicant-modal-panel .manual-input-stack {
        margin-top: 16px;
        padding: 18px;
        border: 1px solid rgba(112, 19, 27, 0.15);
        border-radius: 14px;
        background: linear-gradient(180deg, #ffffff, #fff8f6);
        box-shadow: 0 10px 24px rgba(15, 23, 42, 0.06);
    }

    .applicant-modal-panel .manual-input-stack .manual-find-btn {
        width: 100%;
    }

    .applicant-modal-panel .manual-toggle-label {
        margin: 0 0 5px;
        font-size: 11px;
        font-weight: 900;
        letter-spacing: 0.07em;
        text-transform: uppercase;
        color: #8b0000;
    }

    .manual-lookup-title {
        margin: 0;
        color: #111827;
        font-size: 18px;
        font-weight: 900;
    }

    .manual-lookup-copy {
        margin: 7px 0 14px;
        color: #64748b;
        font-size: 13px;
        line-height: 1.55;
    }

    .manual-lookup-form {
        display: grid;
        gap: 10px;
    }

    .manual-lookup-form .form-control {
        width: 100%;
        min-height: 50px;
        margin: 0 !important;
        padding: 13px 15px;
        border: 1px solid rgba(112, 19, 27, 0.22);
        border-radius: 10px;
        background: #ffffff;
        color: #111827;
        font-size: 14px;
        font-weight: 700;
    }

    .manual-lookup-form .manual-find-btn {
        display: inline-flex !important;
        min-height: 50px;
        border-radius: 10px;
        background: #800000;
    }

    .manual-lookup-form .manual-find-btn:hover {
        background: #facc15;
        color: #70131b;
    }

    html[data-theme="dark"] .applicant-modal-shell {
        background: linear-gradient(180deg, rgba(15, 23, 42, 0.98), rgba(17, 24, 39, 0.96));
        border-color: rgba(148, 163, 184, 0.16);
    }

    html[data-theme="dark"] .applicant-modal-panel {
        background: rgba(15, 23, 42, 0.88);
        border-color: rgba(148, 163, 184, 0.16);
        box-shadow: 0 18px 34px rgba(0, 0, 0, 0.24);
    }

    html[data-theme="dark"] .applicant-modal-panel .scan-method-bar,
    html[data-theme="dark"] .applicant-modal-panel .scan-surface {
        background: linear-gradient(180deg, rgba(17, 24, 39, 0.96), rgba(15, 23, 42, 0.94));
        border-color: rgba(148, 163, 184, 0.18);
        box-shadow: inset 0 1px 0 rgba(255,255,255,0.04), 0 12px 30px rgba(0, 0, 0, 0.20);
    }

    html[data-theme="dark"] .applicant-modal-panel .scan-method-title,
    html[data-theme="dark"] .applicant-modal-panel .scan-method-note,
    html[data-theme="dark"] .applicant-modal-panel .scan-inline-note {
        color: #f8fafc !important;
    }

    html[data-theme="dark"] .applicant-modal-panel .scan-inline-note {
        background: rgba(112, 19, 27, 0.32);
        border-color: rgba(250, 204, 21, 0.22);
    }

    html[data-theme="dark"] .applicant-modal-panel .btn-scan-switch {
        background: rgba(15, 23, 42, 0.86);
        color: #f8fafc;
        border-color: rgba(250, 204, 21, 0.28);
        box-shadow: 0 8px 18px rgba(0, 0, 0, 0.22);
    }

    html[data-theme="dark"] .applicant-modal-panel .scan-method-badge {
        background: rgba(250, 204, 21, 0.16);
        border-color: rgba(250, 204, 21, 0.28);
        color: #fde68a;
    }

    html[data-theme="dark"] .applicant-modal-head-actions .btn-scan-switch {
        background: rgba(15, 23, 42, 0.86);
        color: #f8fafc;
        border-color: rgba(250, 204, 21, 0.28);
        box-shadow: 0 8px 18px rgba(0, 0, 0, 0.22);
    }

    html[data-theme="dark"] .applicant-modal-head-actions .btn-scan-switch:hover,
    html[data-theme="dark"] .applicant-modal-head-actions .btn-scan-switch:focus {
        background: rgba(112, 19, 27, 0.92);
        color: #fef3c7;
        border-color: #facc15;
        box-shadow:
            0 0 0 3px rgba(250, 204, 21, 0.18),
            0 14px 24px rgba(0, 0, 0, 0.28);
    }

    html[data-theme="dark"] .applicant-modal-head-actions .scan-method-badge {
        background: rgba(250, 204, 21, 0.16);
        border-color: rgba(250, 204, 21, 0.28);
        color: #fde68a;
    }

    html[data-theme="dark"] .applicant-modal-panel .manual-input-stack {
        border-color: rgba(250, 204, 21, 0.16);
        background: #111827;
    }

    html[data-theme="dark"] .applicant-modal-panel .manual-toggle-label {
        color: #facc15;
    }

    html[data-theme="dark"] .manual-lookup-title,
    html[data-theme="dark"] .manual-lookup-copy {
        color: #f8fafc;
    }

    .registration-hub {
        max-width: 980px;
    }

    .registration-head {
        margin-bottom: 10px;
    }

    .registration-head-main {
        display: flex;
        align-items: flex-start;
        justify-content: space-between;
        gap: 14px;
        flex-wrap: wrap;
    }

    .registration-head-copy {
        flex: 1 1 420px;
        min-width: 280px;
    }

    .registration-kicker {
        margin: 0 0 8px;
        font-size: 12px;
        font-weight: 900;
        text-transform: uppercase;
        letter-spacing: 0.08em;
        color: #8b0000;
    }

    .registration-head h3 {
        margin: 0;
        font-size: 1.75rem;
        font-weight: 900;
        color: #111827;
    }

    .registration-head p {
        margin: 8px 0 0;
        color: #475569;
    }

    .registration-mode-picker {
        display: flex;
        justify-content: center;
        gap: 18px;
        margin: 18px 0 20px;
        flex-wrap: wrap;
    }

    .registration-mode-btn {
        width: min(360px, 100%);
        min-height: 280px;
        border: 1px solid rgba(234, 179, 8, 0.42);
        border-radius: 28px;
        padding: 26px 24px 30px;
        background: linear-gradient(145deg, #ffffff 0%, #f8fafc 48%, #e5e7eb 100%);
        box-shadow:
            0 0 0 1px rgba(250, 204, 21, 0.12),
            0 22px 36px rgba(234, 179, 8, 0.18),
            0 48px 60px -36px rgba(202, 138, 4, 0.36);
        text-align: center;
        text-decoration: none;
        color: inherit;
        cursor: pointer;
        position: relative;
        overflow: hidden;
        transition: transform .22s ease, box-shadow .22s ease, border-color .22s ease, color .22s ease;
    }

    .registration-mode-btn::before {
        content: "";
        position: absolute;
        inset: 0;
        background: linear-gradient(180deg, rgba(255,255,255,0.92), rgba(255,255,255,0.28) 42%, rgba(255,255,255,0.10));
        pointer-events: none;
        z-index: 0;
    }

    .registration-mode-btn::after {
        content: "";
        position: absolute;
        top: -42%;
        left: -130%;
        width: 120%;
        height: 185%;
        background: linear-gradient(115deg, rgba(250, 204, 21, 0) 0%, rgba(250, 204, 21, 0.5) 45%, rgba(250, 204, 21, 0) 100%);
        transform: skewX(-20deg);
        opacity: 0;
        pointer-events: none;
        transition: left .8s ease, opacity .18s ease;
        z-index: 0;
    }

    .registration-mode-btn:hover {
        transform: translateY(-4px);
        border-color: rgba(234, 179, 8, 0.62);
        box-shadow:
            0 0 0 1px rgba(250, 204, 21, 0.22),
            0 26px 42px rgba(234, 179, 8, 0.22),
            0 54px 76px -38px rgba(202, 138, 4, 0.42);
        text-decoration: none;
        color: inherit;
    }

    .registration-mode-btn:hover::after {
        opacity: 1;
        left: 125%;
    }

    .registration-mode-btn .um-mode-icon {
        width: 68px;
        height: 68px;
        margin: 12px auto 10px;
        border-radius: 22px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        background: linear-gradient(145deg, rgba(254, 240, 138, 0.98), rgba(250, 204, 21, 0.9));
        border: 1px solid rgba(234, 179, 8, 0.34);
        color: #111827;
        box-shadow: inset 0 1px 0 rgba(255,255,255,0.96), 0 18px 28px rgba(234, 179, 8, 0.18);
        position: relative;
        z-index: 1;
        animation: umModeFloat 3.8s ease-in-out infinite;
        transition: background .22s ease, color .22s ease, border-color .22s ease, transform .22s ease;
    }

    .registration-mode-btn .um-mode-icon::after {
        content: "";
        position: absolute;
        left: 12%;
        right: 12%;
        bottom: -13px;
        height: 14px;
        border-radius: 999px;
        background: radial-gradient(circle, rgba(0, 0, 0, 0.42) 0%, rgba(0, 0, 0, 0.2) 48%, transparent 86%);
        filter: blur(7px);
        z-index: -1;
        pointer-events: none;
    }

    .registration-mode-btn .um-mode-icon svg {
        width: 30px;
        height: 30px;
        stroke: currentColor;
        stroke-width: 2;
        fill: none;
    }

    .registration-mode-btn .um-mode-icon img {
        width: 46px;
        height: 46px;
        object-fit: contain;
        display: block;
    }

    .registration-mode-btn h3 {
        margin: 14px 0 8px;
        font-size: 1.24rem;
        font-weight: 900;
        color: #0f172a;
        position: relative;
        z-index: 1;
        transition: color .22s ease;
    }

    .registration-mode-btn p {
        margin: 0;
        color: #64748b;
        line-height: 1.6;
        font-size: .95rem;
        position: relative;
        z-index: 1;
        transition: color .22s ease;
    }

    .registration-mode-btn:hover .um-mode-icon {
        background: linear-gradient(145deg, #facc15, #fde68a);
        color: #111827;
        border-color: rgba(250, 204, 21, 0.62);
        transform: translateY(-2px) scale(1.04);
    }

    .registration-actions {
        display: flex;
        gap: 12px;
        flex-wrap: wrap;
        justify-content: flex-end;
        margin-top: 8px;
    }

    .registration-actions .btn {
        min-width: 132px;
        width: auto !important;
        flex: 0 0 auto;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        text-align: center;
        position: relative;
        overflow: hidden;
        border-radius: 999px;
        border: 1px solid rgba(112, 19, 27, 0.3);
        background: rgba(255, 255, 255, 0.96);
        color: #70131B;
        font-weight: 800;
        padding: 10px 16px;
        white-space: nowrap;
        box-shadow: 0 0 0 2px rgba(112, 19, 27, 0.09), 0 10px 20px rgba(15, 23, 42, 0.08);
    }

    .registration-actions .btn::after {
        content: "";
        position: absolute;
        top: -40%;
        left: -130%;
        width: 120%;
        height: 180%;
        background: linear-gradient(115deg, rgba(250, 204, 21, 0) 0%, rgba(250, 204, 21, 0.46) 45%, rgba(250, 204, 21, 0) 100%);
        transform: skewX(-20deg);
        transition: left 1.5s ease;
        pointer-events: none;
    }

    .registration-actions .btn:hover::after {
        left: 125%;
    }

    .registration-actions .btn:hover,
    .registration-actions .btn:focus {
        color: #70131B;
        border-color: rgba(112, 19, 27, 0.48);
        background: #ffffff;
    }

    .assisted-intake-shell {
        display: grid;
        grid-template-columns: minmax(0, 1fr);
        gap: 22px;
        align-items: start;
    }

    .assisted-panel {
        position: relative;
        border-radius: 24px;
        border: 1px solid rgba(148, 163, 184, 0.22);
        background: linear-gradient(180deg, rgba(255,255,255,0.98), rgba(248,250,252,0.94));
        box-shadow: 0 20px 44px rgba(15, 23, 42, 0.08), inset 0 1px 0 rgba(255,255,255,0.9);
        overflow: hidden;
    }

    .assisted-panel::before {
        content: "";
        position: absolute;
        top: 0;
        left: 26px;
        right: 26px;
        height: 4px;
        border-radius: 999px;
        background: linear-gradient(90deg, #70131B 0%, #a11d33 54%, #facc15 100%);
    }

    .assisted-panel-body {
        padding: 22px;
        display: grid;
        grid-template-columns: repeat(2, minmax(0, 1fr));
        gap: 14px;
    }

    .assisted-hero {
        display: flex;
        align-items: flex-start;
        gap: 14px;
        margin-bottom: 18px;
        grid-column: 1 / -1;
    }

    .assisted-hero-badge {
        width: 58px;
        height: 58px;
        border-radius: 18px;
        background: linear-gradient(145deg, #70131B, #8f2230);
        color: #ffffff;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        box-shadow: 0 16px 30px rgba(112, 19, 27, 0.22);
        flex-shrink: 0;
    }

    .assisted-hero-badge svg {
        width: 28px;
        height: 28px;
        stroke: currentColor;
        stroke-width: 2;
        fill: none;
    }

    .assisted-hero-copy h3 {
        margin: 0;
        font-size: 1.45rem;
        font-weight: 900;
        color: #000000;
        letter-spacing: -0.02em;
    }

    .assisted-hero-copy p {
        margin: 8px 0 0;
        color: #000000;
        line-height: 1.6;
        font-size: .95rem;
    }

    .assisted-status-row {
        display: flex;
        flex-wrap: wrap;
        gap: 10px;
        margin-top: 14px;
    }

    .assisted-status-chip {
        display: inline-flex;
        align-items: center;
        gap: 8px;
        padding: 8px 12px;
        border-radius: 999px;
        font-size: 11px;
        font-weight: 900;
        text-transform: uppercase;
        letter-spacing: 0.05em;
    }

    .assisted-status-chip.pending {
        background: #fff7ed;
        border: 1px solid #fdba74;
        color: #000000;
    }

    .assisted-status-chip.ready {
        background: #ecfdf5;
        border: 1px solid #a7f3d0;
        color: #000000;
    }

    .assisted-grid {
        display: grid;
        grid-template-columns: repeat(2, minmax(0, 1fr));
        gap: 14px;
    }

    .assisted-section-divider {
        display: flex;
        align-items: center;
        gap: 12px;
        margin: 6px 0 12px;
    }

    .assisted-section-divider::before,
    .assisted-section-divider::after {
        content: "";
        flex: 1 1 auto;
        height: 1px;
        background: linear-gradient(90deg, rgba(148, 163, 184, 0), rgba(148, 163, 184, 0.42), rgba(148, 163, 184, 0));
    }

    .assisted-section-divider span {
        display: inline-flex;
        align-items: center;
        gap: 8px;
        padding: 7px 12px;
        border-radius: 999px;
        border: 1px solid rgba(148, 163, 184, 0.18);
        background: rgba(255, 255, 255, 0.82);
        color: #000000;
        font-size: 11px;
        font-weight: 900;
        text-transform: uppercase;
        letter-spacing: 0.08em;
        box-shadow: 0 8px 18px rgba(15, 23, 42, 0.05);
        white-space: nowrap;
    }

    .assisted-section-divider svg {
        width: 14px;
        height: 14px;
        stroke: currentColor;
        fill: none;
        stroke-width: 1.9;
    }

    .assisted-panel-body > .mb-3,
    .assisted-panel-body > .mb-2,
    .assisted-panel-body > .assisted-pair-row,
    .assisted-panel-body > .assisted-field-card,
    .assisted-panel-body > input.form-control,
    .assisted-panel-body > .assisted-callout,
    .assisted-panel-body > div[style*="background:#fff7ed"] {
        margin-bottom: 14px !important;
        padding: 14px;
        border-radius: 18px;
        border: 1px solid rgba(127, 29, 29, 0.16);
        background:
            radial-gradient(circle at top right, rgba(250, 204, 21, 0.10), transparent 34%),
            linear-gradient(180deg, #fff7f7 0%, #fef2f2 100%);
        box-shadow:
            inset 0 1px 0 rgba(255,255,255,0.94),
            0 10px 20px rgba(127, 29, 29, 0.04);
    }

    .assisted-highlight-card {
        border: 1px solid rgba(127, 29, 29, 0.12) !important;
        background:
            radial-gradient(circle at top right, rgba(250, 204, 21, 0.12), transparent 34%),
            linear-gradient(180deg, #fff4f4 0%, #fef2f2 100%) !important;
        box-shadow:
            inset 0 1px 0 rgba(255,255,255,0.94),
            0 10px 20px rgba(127, 29, 29, 0.05);
    }

    .assisted-highlight-card label {
        color: #000000 !important;
    }

    .assisted-panel-body > .mb-3,
    .assisted-panel-body > .assisted-pair-row:first-of-type {
        grid-column: 1 / -1;
    }

    .assisted-panel-body > .mb-3 label,
    .assisted-panel-body > .mb-2 label,
    .assisted-field-label {
        display: block;
        margin: 0 0 9px;
        font-size: 13px !important;
        font-weight: 700 !important;
        text-transform: uppercase;
        letter-spacing: 0.5px;
        color: #000000 !important;
    }

    .assisted-panel-body > .mb-3 .form-control,
    .assisted-panel-body > .mb-2 .form-control,
    .assisted-panel-body > .assisted-pair-row .form-control,
    .assisted-panel-body > .assisted-field-card .form-control,
    .assisted-panel-body > input.form-control {
        width: 100%;
        min-height: 56px;
        padding: 16px 18px;
        border: 1px solid rgba(127, 29, 29, 0.22);
        border-radius: 18px;
        font-size: 14px;
        color: #111111;
        font-weight: 700;
        margin-bottom: 0 !important;
        background:
            radial-gradient(circle at top right, rgba(250, 204, 21, 0.10), transparent 36%),
            linear-gradient(180deg, #ffffff 0%, #fff8f6 100%);
        box-shadow:
            0 12px 22px rgba(15, 23, 42, 0.08),
            inset 0 1px 0 rgba(255,255,255,0.86);
        transition: all 0.2s ease;
    }

    .assisted-pair-row {
        display: grid !important;
        grid-template-columns: repeat(2, minmax(0, 1fr));
        gap: 16px !important;
        align-items: stretch;
    }

    .assisted-field-card {
        padding: 14px;
        border-radius: 18px;
        border: 1px solid rgba(127, 29, 29, 0.16);
        background:
            radial-gradient(circle at top right, rgba(250, 204, 21, 0.10), transparent 34%),
            linear-gradient(180deg, #fff7f7 0%, #fef2f2 100%);
        box-shadow:
            inset 0 1px 0 rgba(255,255,255,0.94),
            0 10px 20px rgba(127, 29, 29, 0.04);
        min-width: 0;
    }

    .assisted-panel-body > input.form-control {
        width: 100%;
    }

    .assisted-panel-body .form-control::placeholder {
        color: #000000;
        font-weight: 600;
        opacity: 0.72;
    }

    .assisted-intake-shell .assisted-role-display,
    .assisted-intake-shell .assisted-gender-display,
    .assisted-intake-shell .assisted-role-option,
    .assisted-intake-shell .assisted-gender-option,
    .assisted-intake-shell > .assisted-panel > .assisted-panel-body > div[style*="background:#fff7ed"] strong,
    .assisted-intake-shell > .assisted-panel > .assisted-panel-body > div[style*="background:#fff7ed"] p,
    #registerForm .text-center.mt-3 a {
        color: #000000 !important;
    }

    .assisted-submit-btn {
        width: 100%;
        border: none;
        padding: 14px 28px;
        border-radius: 14px;
        font-weight: 700;
        font-size: 16px;
        cursor: pointer;
        display: flex;
        align-items: center;
        justify-content: center;
        gap: 10px;
        color: #ffffff;
        background: linear-gradient(135deg, #8B0000, #70131B);
        box-shadow:
            0 0 0 3px rgba(139, 0, 0, 0.10),
            0 16px 28px rgba(112, 19, 27, 0.20);
        transition: transform 0.2s ease, box-shadow 0.2s ease, background 0.2s ease, color 0.2s ease;
    }

    .assisted-submit-btn:hover {
        background: #facc15;
        color: #8B0000;
        transform: translateY(-2px);
        box-shadow:
            0 0 0 3px rgba(250, 204, 21, 0.12),
            0 18px 30px rgba(139, 0, 0, 0.22);
    }

    .assisted-submit-btn:disabled {
        cursor: not-allowed;
        opacity: 0.85;
        transform: none;
    }

    html[data-theme="dark"] .assisted-panel {
        background: linear-gradient(180deg, rgba(18, 18, 18, 0.98), rgba(28, 18, 18, 0.98));
        border-color: rgba(250, 204, 21, 0.14);
        box-shadow:
            0 22px 46px rgba(0, 0, 0, 0.34),
            0 0 0 1px rgba(139, 0, 0, 0.18);
    }

    html[data-theme="dark"] .assisted-hero-copy h3,
    html[data-theme="dark"] .assisted-hero-copy p,
    html[data-theme="dark"] .assisted-panel-body > .mb-3 label,
    html[data-theme="dark"] .assisted-panel-body > .mb-2 label,
    html[data-theme="dark"] .assisted-field-label,
    html[data-theme="dark"] .assisted-section-divider span,
    html[data-theme="dark"] .assisted-intake-shell .assisted-role-display,
    html[data-theme="dark"] .assisted-intake-shell .assisted-gender-display,
    html[data-theme="dark"] .assisted-intake-shell .assisted-role-option,
    html[data-theme="dark"] .assisted-intake-shell .assisted-gender-option,
    html[data-theme="dark"] .assisted-intake-shell > .assisted-panel > .assisted-panel-body > div[style*="background:#fff7ed"] strong,
    html[data-theme="dark"] .assisted-intake-shell > .assisted-panel > .assisted-panel-body > div[style*="background:#fff7ed"] p,
    html[data-theme="dark"] #registerForm .text-center.mt-3 a {
        color: #f8fafc !important;
    }

    html[data-theme="dark"] .assisted-status-chip.pending,
    html[data-theme="dark"] .assisted-status-chip.ready {
        color: #f8fafc;
        border-color: rgba(250, 204, 21, 0.24);
        background: rgba(255, 255, 255, 0.08);
    }

    html[data-theme="dark"] .assisted-panel-body > .mb-3,
    html[data-theme="dark"] .assisted-panel-body > .mb-2,
    html[data-theme="dark"] .assisted-panel-body > .assisted-pair-row,
    html[data-theme="dark"] .assisted-panel-body > .assisted-field-card,
    html[data-theme="dark"] .assisted-panel-body > input.form-control,
    html[data-theme="dark"] .assisted-panel-body > .assisted-callout,
    html[data-theme="dark"] .assisted-panel-body > div[style*="background:#fff7ed"] {
        border-color: rgba(250, 204, 21, 0.14);
        background:
            radial-gradient(circle at top right, rgba(250, 204, 21, 0.08), transparent 34%),
            linear-gradient(180deg, rgba(47, 24, 24, 0.92) 0%, rgba(30, 18, 18, 0.98) 100%);
        box-shadow:
            inset 0 1px 0 rgba(255,255,255,0.04),
            0 12px 24px rgba(0, 0, 0, 0.18);
    }

    html[data-theme="dark"] .assisted-highlight-card {
        border-color: rgba(250, 204, 21, 0.18) !important;
        background:
            radial-gradient(circle at top right, rgba(250, 204, 21, 0.14), transparent 34%),
            linear-gradient(180deg, rgba(74, 24, 31, 0.96) 0%, rgba(52, 18, 23, 0.98) 100%) !important;
        box-shadow:
            inset 0 1px 0 rgba(255,255,255,0.05),
            0 12px 24px rgba(0, 0, 0, 0.20);
    }

    html[data-theme="dark"] .assisted-panel-body > .mb-3 .form-control,
    html[data-theme="dark"] .assisted-panel-body > .mb-2 .form-control,
    html[data-theme="dark"] .assisted-panel-body > .assisted-pair-row .form-control,
    html[data-theme="dark"] .assisted-panel-body > .assisted-field-card .form-control,
    html[data-theme="dark"] .assisted-panel-body > input.form-control,
    html[data-theme="dark"] .assisted-intake-shell .assisted-role-display,
    html[data-theme="dark"] .assisted-intake-shell .assisted-gender-display {
        color: #f8fafc !important;
        border-color: rgba(250, 204, 21, 0.16);
        background:
            radial-gradient(circle at top right, rgba(250, 204, 21, 0.08), transparent 36%),
            linear-gradient(180deg, rgba(40, 26, 26, 0.98) 0%, rgba(23, 23, 23, 0.98) 100%);
        box-shadow:
            0 12px 22px rgba(0, 0, 0, 0.22),
            inset 0 1px 0 rgba(255,255,255,0.05);
    }

    html[data-theme="dark"] .assisted-panel-body .form-control::placeholder {
        color: rgba(248, 250, 252, 0.62);
    }

    html[data-theme="dark"] .assisted-panel-body .form-control:focus,
    html[data-theme="dark"] .assisted-panel-body select.form-control:focus,
    html[data-theme="dark"] .assisted-intake-shell .assisted-role-display:focus,
    html[data-theme="dark"] .assisted-intake-shell .assisted-gender-display:focus,
    html[data-theme="dark"] .assisted-intake-shell .assisted-role-display.is-open,
    html[data-theme="dark"] .assisted-intake-shell .assisted-gender-display.is-open {
        border-color: #facc15;
        box-shadow:
            0 0 0 4px rgba(250, 204, 21, 0.14),
            0 14px 24px rgba(0, 0, 0, 0.26),
            inset 0 1px 0 rgba(255,255,255,0.06);
        background:
            radial-gradient(circle at top right, rgba(250, 204, 21, 0.10), transparent 36%),
            linear-gradient(180deg, rgba(54, 34, 34, 0.98) 0%, rgba(28, 20, 20, 0.98) 100%);
    }

    html[data-theme="dark"] .assisted-role-wrap::after,
    html[data-theme="dark"] .assisted-gender-wrap::after {
        border-right-color: #facc15;
        border-bottom-color: #facc15;
    }

    html[data-theme="dark"] .assisted-role-wrap::before,
    html[data-theme="dark"] .assisted-gender-wrap::before {
        background: rgba(250, 204, 21, 0.18);
    }

    html[data-theme="dark"] .assisted-role-menu,
    html[data-theme="dark"] .assisted-gender-menu {
        background: rgba(18, 18, 18, 0.96);
        border-color: rgba(250, 204, 21, 0.14);
        box-shadow: 0 18px 34px rgba(0, 0, 0, 0.34);
    }

    html[data-theme="dark"] .assisted-role-option,
    html[data-theme="dark"] .assisted-gender-option {
        color: #f8fafc !important;
        border-color: rgba(250, 204, 21, 0.14);
        background: linear-gradient(180deg, rgba(40, 26, 26, 0.98) 0%, rgba(23, 23, 23, 0.98) 100%);
        box-shadow: 0 12px 22px rgba(0, 0, 0, 0.22), inset 0 1px 0 rgba(255,255,255,0.04);
    }

    html[data-theme="dark"] .assisted-role-option:hover,
    html[data-theme="dark"] .assisted-role-option.is-selected,
    html[data-theme="dark"] .assisted-gender-option:hover,
    html[data-theme="dark"] .assisted-gender-option.is-selected {
        background: linear-gradient(135deg, #8B0000, #70131B);
        color: #facc15 !important;
        border-color: rgba(250, 204, 21, 0.28);
    }

    html[data-theme="dark"] .assisted-submit-btn {
        background: linear-gradient(135deg, #8B0000, #70131B);
        color: #ffffff;
        box-shadow:
            0 0 0 3px rgba(139, 0, 0, 0.16),
            0 16px 28px rgba(0, 0, 0, 0.30);
    }

    html[data-theme="dark"] .assisted-submit-btn:hover {
        background: #facc15;
        color: #111111;
        box-shadow:
            0 0 0 3px rgba(250, 204, 21, 0.16),
            0 18px 30px rgba(0, 0, 0, 0.32);
    }

    .assisted-panel-body .form-control:hover {
        border-color: rgba(139, 0, 0, 0.34);
        box-shadow:
            0 14px 24px rgba(15, 23, 42, 0.10),
            0 8px 18px rgba(139, 0, 0, 0.05),
            inset 0 1px 0 rgba(255,255,255,0.90);
        transform: translateY(-1px);
    }

    .assisted-panel-body .form-control:focus,
    .assisted-panel-body select.form-control:focus {
        border-color: #8B0000;
        box-shadow:
            0 0 0 4px rgba(139, 0, 0, 0.06),
            0 14px 24px rgba(139, 0, 0, 0.10),
            inset 0 1px 0 rgba(255,255,255,0.88);
        background: #ffffff;
        outline: none;
        transform: translateY(-1px);
    }

    .assisted-panel-body select.form-control {
        appearance: none;
        -webkit-appearance: none;
        -moz-appearance: none;
        padding-right: 44px;
        background-image:
            linear-gradient(45deg, transparent 50%, #70131B 50%),
            linear-gradient(135deg, #70131B 50%, transparent 50%);
        background-position:
            calc(100% - 20px) calc(50% - 3px),
            calc(100% - 14px) calc(50% - 3px);
        background-size: 6px 6px, 6px 6px;
        background-repeat: no-repeat;
    }

    .assisted-panel-body input[type="date"].form-control {
        letter-spacing: 0.01em;
        color: #1e293b;
    }

    .assisted-panel-body input[type="date"].form-control::-webkit-calendar-picker-indicator {
        opacity: 0.7;
        cursor: pointer;
        filter: sepia(1) saturate(6) hue-rotate(330deg);
    }

    .assisted-field-card .assisted-gender-wrap {
        width: 100%;
    }

    .assisted-role-wrap {
        position: relative;
    }

    .assisted-role-select {
        position: absolute;
        opacity: 0;
        pointer-events: none;
        width: 0;
        height: 0;
        padding: 0;
        border: 0;
        margin: 0;
    }

    .assisted-role-display {
        width: 100%;
        min-height: 52px;
        padding: 14px 52px 14px 16px;
        border: 1px solid rgba(148, 163, 184, 0.20);
        border-radius: 18px;
        font-size: 14px;
        color: #111111;
        background: linear-gradient(180deg, #ffffff 0%, #fff8f6 100%);
        box-shadow: 0 12px 22px rgba(15, 23, 42, 0.08), inset 0 1px 0 rgba(255,255,255,0.86);
        cursor: pointer;
        font-weight: 600;
        text-align: left;
        transition: all 0.2s ease;
    }

    .assisted-role-display:hover {
        border-color: rgba(139, 0, 0, 0.28);
        box-shadow: 0 10px 18px rgba(139, 0, 0, 0.05), inset 0 1px 0 rgba(255,255,255,0.86);
    }

    .assisted-role-display.is-open,
    .assisted-role-display:focus {
        outline: none;
        border-color: #8B0000;
        box-shadow: 0 0 0 4px rgba(139, 0, 0, 0.06), 0 10px 18px rgba(139, 0, 0, 0.08);
    }

    .assisted-role-wrap::after {
        content: "";
        position: absolute;
        top: 50%;
        right: 18px;
        width: 10px;
        height: 10px;
        border-right: 2px solid #8B0000;
        border-bottom: 2px solid #8B0000;
        transform: translateY(-65%) rotate(45deg);
        pointer-events: none;
        transition: transform 0.18s ease;
    }

    .assisted-role-wrap::before {
        content: "";
        position: absolute;
        top: 50%;
        right: 42px;
        transform: translateY(-50%);
        width: 1px;
        height: 24px;
        background: rgba(148, 163, 184, 0.24);
        pointer-events: none;
    }

    .assisted-role-wrap.is-open::after {
        transform: translateY(-20%) rotate(225deg);
    }

    .assisted-role-menu {
        position: absolute;
        top: calc(100% + 10px);
        left: 0;
        right: 0;
        display: none;
        gap: 10px;
        padding: 14px;
        border-radius: 18px;
        border: 1px solid rgba(139, 0, 0, 0.12);
        background: rgba(255, 255, 255, 0.98);
        box-shadow: 0 18px 34px rgba(15, 23, 42, 0.14);
        z-index: 80;
        backdrop-filter: blur(8px);
        -webkit-backdrop-filter: blur(8px);
    }

    .assisted-role-wrap.is-open .assisted-role-menu {
        display: grid;
    }

    .assisted-role-option {
        width: 100%;
        border: 1px solid rgba(148, 163, 184, 0.22);
        background: linear-gradient(180deg, #ffffff 0%, #f8fafc 100%);
        color: #1e293b;
        border-radius: 999px;
        padding: 12px 14px;
        font-size: 13px;
        font-weight: 800;
        text-align: left;
        cursor: pointer;
        transition: all 0.18s ease;
        box-shadow: 0 12px 22px rgba(15, 23, 42, 0.08), 0 1px 0 rgba(255,255,255,0.82) inset;
    }

    .assisted-role-option:hover {
        transform: translateY(-1px);
        border-color: #8B0000;
        background: linear-gradient(135deg, #8B0000, #70131B);
        color: #facc15;
        box-shadow: 0 12px 20px rgba(139, 0, 0, 0.16);
    }

    .assisted-role-option.is-selected {
        background: linear-gradient(135deg, #8B0000, #70131B);
        color: #ffffff;
        border-color: #8B0000;
        box-shadow: 0 14px 24px rgba(139, 0, 0, 0.18);
    }

    .assisted-gender-wrap {
        position: relative;
    }

    .assisted-gender-select {
        position: absolute;
        opacity: 0;
        pointer-events: none;
        width: 0;
        height: 0;
        padding: 0;
        border: 0;
        margin: 0;
    }

    .assisted-gender-display {
        width: 100%;
        min-height: 52px;
        padding: 14px 52px 14px 16px;
        border: 1px solid rgba(148, 163, 184, 0.20);
        border-radius: 18px;
        font-size: 14px;
        color: #111111;
        background: linear-gradient(180deg, #ffffff 0%, #fff8f6 100%);
        box-shadow: 0 12px 22px rgba(15, 23, 42, 0.08), inset 0 1px 0 rgba(255,255,255,0.86);
        cursor: pointer;
        font-weight: 600;
        text-align: left;
        transition: all 0.2s ease;
    }

    .assisted-gender-display:hover {
        border-color: rgba(139, 0, 0, 0.28);
        box-shadow: 0 10px 18px rgba(139, 0, 0, 0.05), inset 0 1px 0 rgba(255,255,255,0.86);
    }

    .assisted-gender-display.is-open,
    .assisted-gender-display:focus {
        outline: none;
        border-color: #8B0000;
        box-shadow: 0 0 0 4px rgba(139, 0, 0, 0.06), 0 10px 18px rgba(139, 0, 0, 0.08);
    }

    .assisted-gender-wrap::after {
        content: "";
        position: absolute;
        top: 50%;
        right: 18px;
        width: 10px;
        height: 10px;
        border-right: 2px solid #8B0000;
        border-bottom: 2px solid #8B0000;
        transform: translateY(-65%) rotate(45deg);
        pointer-events: none;
        transition: transform 0.18s ease;
    }

    .assisted-gender-wrap::before {
        content: "";
        position: absolute;
        top: 50%;
        right: 42px;
        transform: translateY(-50%);
        width: 1px;
        height: 24px;
        background: rgba(148, 163, 184, 0.24);
        pointer-events: none;
    }

    .assisted-gender-wrap.is-open::after {
        transform: translateY(-20%) rotate(225deg);
    }

    @media (max-width: 767.98px) {
        .assisted-pair-row {
            grid-template-columns: 1fr;
        }
    }

    .assisted-gender-menu {
        position: absolute;
        top: calc(100% + 10px);
        left: 0;
        right: 0;
        display: none;
        gap: 10px;
        padding: 14px;
        border-radius: 18px;
        border: 1px solid rgba(139, 0, 0, 0.12);
        background: rgba(255, 255, 255, 0.98);
        box-shadow: 0 18px 34px rgba(15, 23, 42, 0.14);
        z-index: 80;
        backdrop-filter: blur(8px);
        -webkit-backdrop-filter: blur(8px);
    }

    .assisted-gender-wrap.is-open .assisted-gender-menu {
        display: grid;
    }

    .assisted-gender-option {
        width: 100%;
        border: 1px solid rgba(148, 163, 184, 0.22);
        background: linear-gradient(180deg, #ffffff 0%, #f8fafc 100%);
        color: #1e293b;
        border-radius: 999px;
        padding: 12px 14px;
        font-size: 13px;
        font-weight: 800;
        text-align: left;
        cursor: pointer;
        transition: all 0.18s ease;
        box-shadow: 0 12px 22px rgba(15, 23, 42, 0.08), 0 1px 0 rgba(255,255,255,0.82) inset;
    }

    .assisted-gender-option:hover {
        transform: translateY(-1px);
        border-color: #8B0000;
        background: linear-gradient(135deg, #8B0000, #70131B);
        color: #facc15;
        box-shadow: 0 12px 20px rgba(139, 0, 0, 0.16);
    }

    .assisted-gender-option.is-selected {
        background: linear-gradient(135deg, #8B0000, #70131B);
        color: #ffffff;
        border-color: #8B0000;
        box-shadow: 0 14px 24px rgba(139, 0, 0, 0.18);
    }

    .assisted-field,
    .assisted-field-full {
        padding: 14px;
        border-radius: 18px;
        border: 1px solid #e2e8f0;
        background: linear-gradient(180deg, #ffffff, #f8fafc);
        box-shadow: inset 0 1px 0 rgba(255,255,255,0.92);
    }

    .assisted-field-full {
        grid-column: 1 / -1;
    }

    .assisted-field-label {
        display: block;
        margin: 0 0 9px;
        font-size: 11px;
        font-weight: 900;
        text-transform: uppercase;
        letter-spacing: 0.07em;
        color: #64748b;
    }

    .assisted-field .form-control,
    .assisted-field select,
    .assisted-field-full .form-control,
    .assisted-field-full select {
        margin-bottom: 0;
        border-radius: 14px;
        border: 1px solid #cbd5e1;
        background: #ffffff;
        min-height: 48px;
        box-shadow: inset 0 1px 0 rgba(255,255,255,0.95);
    }

    .assisted-inline-identity {
        display: grid;
        grid-template-columns: 1.2fr 0.8fr;
        gap: 14px;
    }

    .assisted-summary-card {
        display: grid;
        gap: 14px;
        max-width: 360px;
        width: 100%;
        justify-self: end;
    }

    .assisted-summary-card .assisted-panel-body {
        display: block;
    }

    .assisted-intake-shell > .assisted-panel > .assisted-panel-body {
        display: block;
        padding: 28px 30px;
        max-width: 880px;
        margin: 0 auto;
        width: 100%;
    }

    .assisted-intake-shell > .assisted-panel > .assisted-panel-body > .mb-3,
    .assisted-intake-shell > .assisted-panel > .assisted-panel-body > .mb-2,
    .assisted-intake-shell > .assisted-panel > .assisted-panel-body > .d-flex.gap-2,
    .assisted-intake-shell > .assisted-panel > .assisted-panel-body > input.form-control,
    .assisted-intake-shell > .assisted-panel > .assisted-panel-body > div[style*="background:#fff7ed"] {
        margin-bottom: 16px !important;
    }

    .assisted-intake-shell > .assisted-panel > .assisted-panel-body > .mb-3,
    .assisted-intake-shell > .assisted-panel > .assisted-panel-body > .mb-2,
    .assisted-intake-shell > .assisted-panel > .assisted-panel-body > .d-flex.gap-2,
    .assisted-intake-shell > .assisted-panel > .assisted-panel-body > input.form-control,
    .assisted-intake-shell > .assisted-panel > .assisted-panel-body > div[style*="background:#fff7ed"] {
        padding: 16px;
    }

    .assisted-intake-shell > .assisted-panel > .assisted-panel-body > .mb-3 label,
    .assisted-intake-shell > .assisted-panel > .assisted-panel-body > .mb-2 label {
        font-size: 12px !important;
    }

    html[data-theme="dark"] .assisted-intake-shell > .assisted-panel > .assisted-panel-body > .mb-3,
    html[data-theme="dark"] .assisted-intake-shell > .assisted-panel > .assisted-panel-body > .mb-2,
    html[data-theme="dark"] .assisted-intake-shell > .assisted-panel > .assisted-panel-body > .assisted-pair-row,
    html[data-theme="dark"] .assisted-intake-shell > .assisted-panel > .assisted-panel-body > .assisted-field-card,
    html[data-theme="dark"] .assisted-intake-shell > .assisted-panel > .assisted-panel-body > input.form-control,
    html[data-theme="dark"] .assisted-intake-shell > .assisted-panel > .assisted-panel-body > div[style*="background:#fff7ed"] {
        border-color: rgba(250, 204, 21, 0.14) !important;
        background:
            radial-gradient(circle at top right, rgba(250, 204, 21, 0.08), transparent 34%),
            linear-gradient(180deg, rgba(47, 24, 24, 0.92) 0%, rgba(30, 18, 18, 0.98) 100%) !important;
        box-shadow:
            inset 0 1px 0 rgba(255,255,255,0.04),
            0 12px 24px rgba(0, 0, 0, 0.18) !important;
    }

    html[data-theme="dark"] .assisted-intake-shell > .assisted-panel > .assisted-panel-body > .mb-3.assisted-highlight-card {
        border-color: rgba(250, 204, 21, 0.18) !important;
        background:
            radial-gradient(circle at top right, rgba(250, 204, 21, 0.14), transparent 34%),
            linear-gradient(180deg, rgba(74, 24, 31, 0.96) 0%, rgba(52, 18, 23, 0.98) 100%) !important;
    }

    html[data-theme="dark"] .assisted-intake-shell > .assisted-panel > .assisted-panel-body > .assisted-pair-row .assisted-field-card,
    html[data-theme="dark"] .assisted-intake-shell > .assisted-panel > .assisted-panel-body > .assisted-field-card {
        border-color: rgba(250, 204, 21, 0.14) !important;
        background:
            radial-gradient(circle at top right, rgba(250, 204, 21, 0.08), transparent 34%),
            linear-gradient(180deg, rgba(47, 24, 24, 0.92) 0%, rgba(30, 18, 18, 0.98) 100%) !important;
    }

    html[data-theme="dark"] .assisted-intake-shell .assisted-section-divider span {
        background: rgba(20, 20, 20, 0.9) !important;
        border-color: rgba(250, 204, 21, 0.18) !important;
        color: #f8fafc !important;
    }

    html[data-theme="dark"] .assisted-intake-shell > .assisted-panel > .assisted-panel-body .form-control,
    html[data-theme="dark"] .assisted-intake-shell .assisted-role-display,
    html[data-theme="dark"] .assisted-intake-shell .assisted-gender-display {
        background:
            radial-gradient(circle at top right, rgba(250, 204, 21, 0.08), transparent 36%),
            linear-gradient(180deg, rgba(40, 26, 26, 0.98) 0%, rgba(23, 23, 23, 0.98) 100%) !important;
        color: #f8fafc !important;
        border-color: rgba(250, 204, 21, 0.16) !important;
    }

    .assisted-summary-block {
        padding: 18px;
        border-radius: 20px;
        border: 1px solid rgba(112, 19, 27, 0.10);
        background:
            radial-gradient(circle at top right, rgba(250, 204, 21, 0.16), transparent 34%),
            linear-gradient(180deg, #fffdf8 0%, #fff9fb 48%, #ffffff 100%);
        box-shadow: 0 18px 32px rgba(15, 23, 42, 0.06);
    }

    .assisted-summary-kicker {
        margin: 0 0 8px;
        font-size: 11px;
        font-weight: 900;
        text-transform: uppercase;
        letter-spacing: 0.08em;
        color: #8b0000;
    }

    .assisted-summary-title {
        margin: 0;
        font-size: 1.25rem;
        font-weight: 900;
        color: #111827;
    }

    .assisted-summary-copy {
        margin: 8px 0 0;
        color: #64748b;
        line-height: 1.6;
        font-size: .93rem;
    }

    .assisted-preview-list {
        display: grid;
        gap: 10px;
        margin-top: 14px;
    }

    .assisted-preview-item {
        display: grid;
        grid-template-columns: 110px minmax(0, 1fr);
        gap: 12px;
        align-items: center;
        padding: 11px 12px;
        border-radius: 14px;
        background: rgba(255,255,255,0.9);
        border: 1px solid rgba(203, 213, 225, 0.7);
    }

    .assisted-preview-item small {
        font-size: 11px;
        font-weight: 900;
        text-transform: uppercase;
        letter-spacing: 0.06em;
        color: #64748b;
    }

    .assisted-preview-item strong {
        color: #111827;
        font-size: 14px;
    }

    .assisted-callout {
        padding: 16px 18px;
        border-radius: 18px;
        background: linear-gradient(180deg, #fff7ed, #fffaf3);
        border: 1px dashed #fdba74;
    }

    .assisted-callout strong {
        display: block;
        margin-bottom: 6px;
        font-size: 12px;
        color: #9a3412;
    }

    .assisted-callout p {
        margin: 0;
        font-size: 12px;
        color: #7c2d12;
        line-height: 1.6;
    }

    .assisted-footer {
        margin-top: 16px;
        padding-top: 16px;
        border-top: 1px dashed #cbd5e1;
    }

    .assisted-save-btn {
        width: 100%;
        min-height: 56px;
        border: none;
        border-radius: 16px;
        background: linear-gradient(135deg, #15803d, #166534 54%, #14532d);
        color: #ffffff;
        font-weight: 900;
        letter-spacing: 0.04em;
        box-shadow: 0 18px 28px rgba(22, 101, 52, 0.24);
    }

    .assisted-footer-link {
        margin-top: 12px;
        text-align: center;
    }

    .assisted-footer-link a {
        font-size: 12px;
        color: #64748b;
        text-decoration: none;
    }

    @media (max-width: 900px) {
        .assisted-intake-shell,
        .assisted-inline-identity {
            grid-template-columns: 1fr;
        }

        .assisted-summary-card {
            max-width: none;
            justify-self: stretch;
        }
    }

    @media (max-width: 640px) {
        .assisted-grid {
            grid-template-columns: 1fr;
        }

        .assisted-preview-item {
            grid-template-columns: 1fr;
            gap: 6px;
        }
    }

    .status-chip {
        display: inline-flex;
        align-items: center;
        gap: 7px;
        padding: 7px 12px;
        border-radius: 999px;
        font-size: 12px;
        font-weight: 800;
    }

    .status-chip.pending {
        background: #fff7ed;
        border: 1px solid #fdba74;
        color: #9a3412;
    }

    .status-note {
        color: #64748b;
        font-size: 13px;
    }

    html[data-theme="dark"] .registration-kicker {
        color: #fde68a;
    }

    html[data-theme="dark"] .registration-head h3,
    html[data-theme="dark"] .registration-head p,
    html[data-theme="dark"] .status-note {
        color: #ffffff;
    }

    html[data-theme="dark"] .registration-mode-btn {
        background: #70131B;
        border-color: rgba(255, 214, 102, 0.5);
        box-shadow: 0 0 0 1px rgba(255, 214, 102, 0.16), 0 24px 38px rgba(95, 0, 18, 0.34), 0 52px 72px -38px rgba(193, 138, 16, 0.56);
    }

    html[data-theme="dark"] .registration-mode-btn::before {
        background: none;
    }

    html[data-theme="dark"] .registration-mode-btn::after {
        background: linear-gradient(115deg, rgba(250, 204, 21, 0) 0%, rgba(250, 204, 21, 0.46) 45%, rgba(250, 204, 21, 0) 100%);
    }

    html[data-theme="dark"] .registration-mode-btn .eyebrow {
        background: rgba(193, 138, 16, 0.22);
        color: #ffd86b;
    }

    html[data-theme="dark"] .registration-mode-btn h3,
    html[data-theme="dark"] .registration-mode-btn p {
        color: #ffffff;
    }

    html[data-theme="dark"] .status-chip.pending {
        background: rgba(234, 179, 8, 0.16);
        border-color: rgba(250, 204, 21, 0.5);
        color: #fde68a;
    }

    html[data-theme="dark"] .registration-actions .btn {
        background: rgba(255, 255, 255, 0.96);
        color: #70131B;
        border-color: rgba(250, 204, 21, 0.42);
    }

    @media (max-width: 860px) {
        .registration-actions {
            justify-content: flex-start;
        }

        .registration-head-main {
            flex-direction: column;
        }
    }

    @keyframes spin { 0% { transform: rotate(0deg); } 100% { transform: rotate(360deg); } }
    @keyframes umModeFloat {
        0%, 100% { transform: translateY(0); }
        50% { transform: translateY(-4px); }
    }
    @keyframes intakeFloat {
        0%, 100% { transform: translateY(0); }
        50% { transform: translateY(-5px); }
    }
    @keyframes fingerPulse {
        0%, 100% { transform: scale(1); opacity: 0.95; }
        50% { transform: scale(1.05); opacity: 1; }
    }
    @keyframes scan-animation {
        0% { top: 16%; opacity: 0.9; }
        50% { top: 78%; opacity: 1; }
        100% { top: 16%; opacity: 0.9; }
    }
    @keyframes slideInRight { from { transform: translateX(120%); opacity: 0; } to { transform: translateX(0); opacity: 1; } }
    /* --- Applicant Reference Panel --- */
    #applicantRefModal .applicant-modal-shell {
        width: min(680px, 100%);
        transition: width 0.3s ease;
    }

    #applicantRefModal .applicant-modal-shell.has-lookup-result {
        width: min(1040px, 100%);
    }

    #applicantRefModal .applicant-modal-shell.has-lookup-result.is-encode-workflow {
        width: min(1080px, 100%);
    }

    #applicantRefModal .applicant-modal-shell.has-lookup-result.is-encode-workflow .applicant-modal-body {
        padding: 18px 18px 14px;
    }

    #applicantRefModal .applicant-modal-shell.has-lookup-result.is-encode-workflow .applicant-ref-panel {
        max-width: 100%;
        gap: 10px;
    }

    #applicantRefModal .applicant-modal-shell.has-lookup-result.is-encode-workflow .applicant-medical-condition-section.show {
        grid-template-columns: 1fr;
        gap: 10px;
    }

    #applicantRefModal .applicant-modal-shell.has-lookup-result.is-encode-workflow .applicant-medical-condition-section.show .applicant-screening-panel {
        width: 100%;
    }

    #applicantRefModal .applicant-modal-shell.has-lookup-result.is-final-review-workflow .applicant-vitals-grid {
        grid-template-columns: 1fr;
    }

    #applicantRefModal .applicant-modal-shell.is-final-review-workflow {
        width: min(1120px, 100%);
    }

    #applicantRefModal .applicant-modal-shell.has-lookup-result.is-final-review-workflow .applicant-modal-body {
        padding: 18px 18px 14px;
    }

    #applicantRefModal .applicant-modal-shell.has-lookup-result.is-final-review-workflow .applicant-ref-panel {
        max-width: 100%;
        gap: 10px;
    }

    #applicantRefModal .applicant-modal-shell.has-lookup-result.is-employee-lookup {
        width: min(1040px, 100%);
    }

    #applicantRefModal .applicant-modal-shell.has-lookup-result.is-employee-lookup .applicant-modal-body {
        padding: 18px 18px 14px;
    }

    #applicantRefModal .applicant-modal-shell.has-lookup-result.is-employee-lookup .applicant-ref-panel {
        max-width: 100%;
        gap: 12px;
    }

    .applicant-ref-grid {
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 20px;
        align-items: stretch;
    }
    .applicant-ref-col {
        display: flex;
        flex-direction: column;
        gap: 14px;
        padding: 20px;
        border-radius: 16px;
        border: 1px solid rgba(112, 19, 27, 0.13);
        background: linear-gradient(180deg, #ffffff, #f8fafc);
        box-shadow: inset 0 1px 0 rgba(255,255,255,0.9), 0 10px 24px rgba(15, 23, 42, 0.05);
    }
    .applicant-ref-kicker {
        margin: 0 0 3px;
        font-size: 11px;
        font-weight: 900;
        text-transform: uppercase;
        letter-spacing: 0.08em;
        color: #8b0000;
    }
    .applicant-ref-title {
        margin: 0;
        font-size: 16px;
        font-weight: 800;
        color: #0f172a;
    }
    .applicant-ref-copy {
        margin: 4px 0 0;
        font-size: 13px;
        color: #64748b;
        line-height: 1.5;
    }
    .applicant-ref-input {
        width: 100%;
        min-height: 52px;
        padding: 14px 16px;
        border: 1px solid rgba(112, 19, 27, 0.18);
        border-radius: 14px;
        font-size: 14px;
        font-weight: 700;
        color: #111827;
        background: linear-gradient(180deg, #ffffff, #fff8f6);
        box-shadow: 0 8px 18px rgba(15, 23, 42, 0.06), inset 0 1px 0 rgba(255,255,255,0.9);
        transition: border-color .2s ease, box-shadow .2s ease;
        margin-bottom: 0;
        outline: none;
    }
    .applicant-ref-input:focus {
        border-color: rgba(112, 19, 27, 0.42);
        box-shadow: 0 0 0 3px rgba(112, 19, 27, 0.08), 0 8px 18px rgba(15, 23, 42, 0.08);
    }
    .applicant-ref-status {
        padding: 10px 12px;
        border-radius: 10px;
        font-size: 12px;
        font-weight: 700;
        line-height: 1.5;
        display: none;
    }
    .applicant-ref-status.info    { display:block; background:#eff6ff; border:1px solid #bfdbfe; color:#1d4ed8; }
    .applicant-ref-status.success { display:block; background:#ecfdf5; border:1px solid #a7f3d0; color:#047857; }
    .applicant-ref-status.error   { display:block; background:#fff1f2; border:1px solid #fecdd3; color:#be123c; }
    .applicant-ref-mode {
        width: 100%;
        display: flex;
        flex-direction: column;
        gap: 14px;
        align-items: center;
    }

    .applicant-workflow-grid {
        width: min(760px, 100%);
        display: grid;
        grid-template-columns: repeat(2, minmax(0, 1fr));
        gap: 14px;
    }

    .applicant-workflow-card {
        position: relative;
        overflow: hidden;
        min-height: 58px;
        border: 1px solid #facc15;
        border-radius: 14px;
        background: #8b1722;
        color: #ffffff !important;
        text-align: center;
        padding: 0 20px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        gap: 10px;
        box-shadow: 0 18px 30px rgba(112, 19, 27, 0.24);
        transition: transform .18s ease, border-color .18s ease, box-shadow .18s ease, background .18s ease, color .18s ease;
    }

    .applicant-workflow-card::before {
        content: "";
        position: absolute;
        inset: 0;
        background:
            linear-gradient(120deg,
                rgba(255, 248, 196, 0) 0%,
                rgba(255, 239, 181, 0.14) 22%,
                rgba(255, 239, 181, 0.52) 48%,
                rgba(255, 239, 181, 0.14) 72%,
                rgba(255, 248, 196, 0) 100%);
        transform: translateX(-135%);
        transition: transform 1.2s ease;
        z-index: 0;
        pointer-events: none;
    }

    .applicant-workflow-card:hover,
    .applicant-workflow-card:focus-visible {
        transform: translateY(-2px);
        border-color: #facc15;
        background: #facc15;
        color: #70131B !important;
        box-shadow: 0 22px 38px rgba(112, 19, 27, 0.28);
        outline: none;
    }

    .applicant-workflow-card:hover::before,
    .applicant-workflow-card:focus-visible::before {
        transform: translateX(135%);
    }

    .applicant-workflow-card svg {
        width: 18px;
        height: 18px;
        stroke-width: 2.2;
        flex: 0 0 auto;
        color: inherit !important;
        stroke: currentColor !important;
        position: relative;
        z-index: 1;
    }

    .applicant-workflow-card strong {
        display: inline;
        color: inherit !important;
        font-size: 14px;
        font-weight: 900;
        margin: 0;
        position: relative;
        z-index: 1;
    }

    .applicant-workflow-card span {
        display: none;
    }

    .applicant-final-review-list {
        width: min(920px, 100%);
        display: none;
        flex-direction: column;
        gap: 16px;
        margin-top: -8px;
    }

    #applicantRefModal .applicant-modal-shell.is-final-review-workflow .applicant-modal-body {
        justify-content: flex-start !important;
        min-height: 0 !important;
        padding-top: 26px !important;
    }

    .applicant-final-review-list.is-visible {
        display: flex;
    }

    .applicant-final-review-toolbar {
        display: flex;
        gap: 10px;
        align-items: center;
        width: 100%;
    }

    .applicant-final-review-search {
        width: 100%;
        min-height: 50px;
        border: 1px solid rgba(112, 19, 27, 0.18);
        border-radius: 14px;
        padding: 0 54px 0 60px;
        font-weight: 800;
        color: #1f2937;
        outline: none;
        background: linear-gradient(180deg, #ffffff, #fffefe);
        box-shadow: 0 12px 26px rgba(112, 19, 27, 0.05);
        box-sizing: border-box;
    }

    .applicant-final-review-search::placeholder {
        color: #94a3b8;
        opacity: 1;
    }

    .applicant-final-review-search:focus {
        border-color: rgba(112, 19, 27, 0.32);
        box-shadow: 0 0 0 3px rgba(112, 19, 27, 0.06), 0 12px 26px rgba(112, 19, 27, 0.07);
    }

    .applicant-final-review-search-wrap {
        position: relative;
        flex: 1 1 0;
        min-width: 0;
        display: block;
    }

    .applicant-final-review-search-wrap > svg {
        position: absolute;
        left: 18px;
        top: 50%;
        transform: translateY(-50%);
        width: 17px;
        height: 17px;
        color: #8b1722;
        pointer-events: none;
        z-index: 3;
    }

    .applicant-final-review-search-wrap::after {
        content: '';
        position: absolute;
        left: 46px;
        top: 50%;
        width: 1px;
        height: 24px;
        background: rgba(112, 19, 27, 0.18);
        transform: translateY(-50%);
        z-index: 3;
        pointer-events: none;
    }

    .applicant-final-review-search-wrap .voice-field-wrap {
        width: 100%;
        display: block;
        position: relative;
    }

    .applicant-final-review-search-wrap .voice-field-wrap .applicant-final-review-search {
        padding-left: 60px !important;
        padding-right: 58px !important;
    }

    .applicant-final-review-search-wrap .voice-field-inline-mic {
        right: 12px;
        top: 50%;
        width: 28px;
        height: 28px;
        min-width: 28px;
        min-height: 28px;
        transform: translateY(-50%);
        border: 1px solid rgba(112, 19, 27, 0.08);
        background: #e8bec1;
        color: #8b1722;
        box-shadow: none;
    }

    .applicant-final-review-search-wrap .voice-field-inline-mic:hover,
    .applicant-final-review-search-wrap .voice-field-inline-mic:focus-visible {
        background: #facc15;
        color: #70131B;
        border-color: #facc15;
    }

    .applicant-final-review-card {
        display: grid;
        grid-template-columns: minmax(250px, 1fr) minmax(210px, 0.8fr) auto;
        gap: 12px;
        align-items: center;
        padding: 18px;
        border: 1px solid rgba(112, 19, 27, 0.15);
        border-radius: 18px;
        background: rgba(255, 250, 250, 0.92);
        margin-bottom: 4px;
        transition: transform .18s ease, background .18s ease, border-color .18s ease, box-shadow .18s ease;
    }

    .applicant-final-review-card:hover,
    .applicant-final-review-card:focus-within {
        transform: translateY(-2px);
        border-color: rgba(112, 19, 27, 0.32);
        background: linear-gradient(180deg, #fff7ed 0%, #fff1f2 100%);
        box-shadow: 0 16px 28px rgba(112, 19, 27, 0.14);
    }

    .applicant-final-review-person {
        display: grid;
        grid-template-columns: 48px minmax(0, 1fr);
        gap: 12px;
        align-items: center;
    }

    .applicant-final-review-avatar {
        width: 48px;
        height: 48px;
        border-radius: 999px;
        display: grid;
        place-items: center;
        overflow: hidden;
        border: 2px solid rgba(250, 204, 21, .72);
        background: linear-gradient(135deg, #fff1f2, #fee2e2);
        color: #70131B;
        font-size: 15px;
        font-weight: 900;
        box-shadow: 0 10px 20px rgba(112, 19, 27, .12);
    }

    .applicant-final-review-avatar img {
        width: 100%;
        height: 100%;
        object-fit: cover;
        display: block;
    }

    .applicant-final-review-reference-badge {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        width: fit-content;
        min-height: 34px;
        padding: 7px 12px;
        border-radius: 999px;
        border: 1px solid rgba(112, 19, 27, 0.16);
        background: #fff7ed;
        color: #70131b !important;
        font-size: 13px;
        font-weight: 900;
        letter-spacing: 0.01em;
    }

    .applicant-final-review-card small,
    .applicant-final-review-card span {
        display: block;
    }

    .applicant-final-review-card small {
        color: #64748b;
        font-size: 10px;
        font-weight: 900;
        letter-spacing: .08em;
        text-transform: uppercase;
    }

    .applicant-final-review-card strong,
    .applicant-final-review-card span {
        color: #111827;
        font-weight: 900;
        font-size: 13px;
    }

    .applicant-final-review-btn,
    .applicant-final-review-back {
        position: relative;
        overflow: hidden;
        min-height: 42px;
        border-radius: 14px;
        border: 1px solid #70131B;
        background: #70131B;
        color: #facc15 !important;
        font-weight: 900;
        padding: 0 16px;
        transition: transform .18s ease, background .18s ease, color .18s ease, border-color .18s ease;
    }

    .applicant-final-review-btn::after {
        content: "";
        position: absolute;
        inset: 0;
        background:
            linear-gradient(120deg,
                rgba(255, 248, 196, 0) 0%,
                rgba(255, 239, 181, 0.14) 22%,
                rgba(255, 239, 181, 0.52) 48%,
                rgba(255, 239, 181, 0.14) 72%,
                rgba(255, 248, 196, 0) 100%);
        transform: translateX(-135%);
        transition: transform 1.2s ease;
        pointer-events: none;
    }

    .applicant-final-review-btn > *,
    .applicant-final-review-btn {
        isolation: isolate;
    }

    .applicant-final-review-btn > * {
        position: relative;
        z-index: 1;
    }

    .applicant-final-review-btn span {
        color: inherit !important;
    }

    .applicant-final-review-toolbar .applicant-final-review-btn {
        flex: 0 0 210px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        gap: 8px;
        min-height: 50px;
        white-space: nowrap;
    }

    .applicant-final-review-toolbar .applicant-final-review-refresh-btn {
        flex: 0 0 56px;
        width: 56px;
        padding: 0;
    }

    .applicant-final-review-toolbar .applicant-final-review-refresh-btn svg {
        width: 18px;
        height: 18px;
        fill: currentColor;
        stroke: none;
    }

    .applicant-final-review-toolbar .applicant-final-review-btn span {
        display: inline;
        white-space: nowrap;
    }

    .applicant-final-review-toolbar .applicant-final-review-btn svg {
        width: 17px;
        height: 17px;
    }

    .applicant-final-review-btn:hover,
    .applicant-final-review-back:hover {
        transform: translateY(-1px);
        background: #facc15;
        color: #70131B !important;
        border-color: #facc15;
    }

    .applicant-final-review-btn:hover::after,
    .applicant-final-review-btn:focus-visible::after {
        transform: translateX(135%);
    }

    .applicant-final-review-back {
        display: none;
        align-self: flex-start;
        background: #ffffff;
        color: #70131b !important;
        border-color: rgba(112, 19, 27, 0.18);
        box-shadow: 0 8px 18px rgba(15, 23, 42, 0.06);
    }

    .applicant-final-review-back.is-visible {
        display: inline-flex;
        align-items: center;
        justify-content: center;
    }

    .applicant-final-review-action-row {
        display: none;
        align-items: center;
        justify-content: flex-start;
        gap: 10px;
        width: 100%;
        margin: -4px 0 6px;
    }

    #applicantRefModal .applicant-modal-shell.is-final-review-workflow .applicant-final-review-action-row {
        position: sticky;
        top: -18px;
        z-index: 95;
        display: flex;
        flex-wrap: wrap;
        padding: 10px 0;
        background: rgba(248, 250, 252, 0.92);
        backdrop-filter: blur(14px);
        -webkit-backdrop-filter: blur(14px);
    }

    .applicant-final-review-action-separator {
        display: none;
        width: 1px;
        height: 34px;
        background: rgba(112, 19, 27, 0.18);
    }

    #applicantRefModal .applicant-modal-shell.is-final-review-workflow .applicant-final-review-action-row:has(.applicant-documents-trigger.is-visible) .applicant-final-review-action-separator {
        display: inline-block;
    }

    #applicantFinalReviewRows {
        min-height: 96px;
    }

    .applicant-final-review-empty {
        display: none;
        min-height: 92px;
        align-items: center;
        justify-content: center;
        padding: 18px;
        border: 1px dashed rgba(112, 19, 27, 0.22);
        border-radius: 18px;
        background: rgba(255, 250, 250, 0.72);
        color: #64748b;
        font-size: 13px;
        font-weight: 800;
        text-align: center;
    }

    .applicant-final-review-empty.is-visible {
        display: flex;
    }

    .applicant-final-review-pagination {
        display: none;
        align-items: center;
        justify-content: space-between;
        gap: 16px;
        margin-top: 12px;
        padding: 14px 18px;
        border: 1px solid #edf2f7;
        border-radius: 12px;
        background: #ffffff;
        box-shadow: 0 10px 24px rgba(15, 23, 42, 0.04);
    }

    .applicant-final-review-pagination.is-visible {
        display: flex;
    }

    .applicant-final-review-page-btn {
        min-width: 38px;
        min-height: 38px;
        border-radius: 8px;
        border: 1px solid #e2e8f0;
        background: #ffffff;
        color: #334155;
        font-size: 12px;
        font-weight: 900;
        padding: 0 12px;
        transition: transform .18s ease, background .18s ease, color .18s ease, border-color .18s ease, box-shadow .18s ease;
    }

    .applicant-final-review-page-btn:hover:not(:disabled) {
        transform: translateY(-1px);
        background: #fff7ed;
        border-color: #f8cfd4;
        color: #70131B;
        box-shadow: 0 10px 20px rgba(112, 19, 27, .10);
    }

    .applicant-final-review-page-btn:disabled {
        opacity: .45;
        cursor: not-allowed;
    }

    .applicant-final-review-page-label {
        min-width: 38px;
        min-height: 38px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        padding: 0 12px;
        border-radius: 8px;
        background: #7f0010;
        color: #ffffff;
        font-size: 12px;
        font-weight: 900;
    }

    .applicant-final-review-pagination-summary,
    .applicant-final-review-per-page {
        color: #64748b;
        font-size: 12px;
        font-weight: 900;
        white-space: nowrap;
    }

    .applicant-final-review-per-page {
        min-height: 38px;
        border-radius: 8px;
        border: 1px solid #e2e8f0;
        background: #ffffff;
        color: #334155;
        padding: 0 34px 0 12px;
        appearance: none;
        cursor: pointer;
        background-image:
            linear-gradient(45deg, transparent 50%, #70131B 50%),
            linear-gradient(135deg, #70131B 50%, transparent 50%);
        background-position:
            calc(100% - 17px) 50%,
            calc(100% - 11px) 50%;
        background-size: 6px 6px, 6px 6px;
        background-repeat: no-repeat;
        transition: transform .18s ease, border-color .18s ease, box-shadow .18s ease;
    }

    .applicant-final-review-per-page:hover,
    .applicant-final-review-per-page:focus {
        outline: none;
        transform: translateY(-1px);
        border-color: rgba(112, 19, 27, .32);
        box-shadow: 0 10px 20px rgba(112, 19, 27, .10);
    }
    .premium-select-native {
        position: absolute !important;
        width: 1px !important;
        height: 1px !important;
        opacity: 0 !important;
        pointer-events: none !important;
    }
    .premium-select-shell {
        position: relative;
        display: inline-flex;
        min-width: 126px;
        z-index: 30;
    }
    .premium-select-button {
        width: 100%;
        min-height: 38px;
        display: inline-flex;
        align-items: center;
        justify-content: space-between;
        gap: 10px;
        padding: 0 12px;
        border-radius: 9px;
        border: 1px solid rgba(112, 19, 27, .24);
        background: #ffffff;
        color: #111827;
        font-size: 12px;
        font-weight: 900;
        cursor: pointer;
        box-shadow: 0 10px 20px rgba(15, 23, 42, .06);
    }
    .premium-select-button::after {
        content: "";
        width: 8px;
        height: 8px;
        border-right: 2px solid #70131B;
        border-bottom: 2px solid #70131B;
        transform: rotate(45deg) translateY(-2px);
    }
    .premium-select-shell.is-open .premium-select-button::after {
        transform: rotate(225deg) translateY(-2px);
    }
    .premium-select-menu {
        position: absolute;
        top: calc(100% + 8px);
        left: 0;
        right: 0;
        display: none;
        flex-direction: column;
        gap: 6px;
        padding: 8px;
        border-radius: 14px;
        border: 1px solid rgba(112, 19, 27, .16);
        background: #ffffff;
        box-shadow: 0 18px 36px rgba(15, 23, 42, .16);
    }
    .premium-select-shell.is-open .premium-select-menu { display: flex; }
    .premium-select-option {
        min-height: 34px;
        border: 1px solid rgba(226, 232, 240, .9);
        border-radius: 999px;
        background: #ffffff;
        color: #111827;
        font-size: 12px;
        font-weight: 900;
        text-align: left;
        padding: 0 12px;
        cursor: pointer;
    }
    .premium-select-option:hover,
    .premium-select-option.is-selected {
        background: #7f0010;
        color: #facc15;
        border-color: #7f0010;
    }

    .applicant-final-review-pagination-controls {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        gap: 10px;
        color: #64748b;
        font-size: 12px;
        font-weight: 900;
    }

    .applicant-ref-status.encoded {
        display: block;
        background: #fff7ed;
        border: 1px solid #fed7aa;
        color: #9a3412;
    }

    @media (max-width: 760px) {
        .applicant-workflow-grid {
            grid-template-columns: 1fr;
        }

        .applicant-final-review-toolbar {
            flex-direction: column;
            align-items: stretch;
        }

        .applicant-final-review-toolbar .applicant-final-review-btn {
            width: auto;
        }

        .applicant-final-review-toolbar > .applicant-final-review-btn {
            flex: 0 0 52px;
            min-height: 52px;
        }

        .applicant-final-review-toolbar {
            display: grid;
            grid-template-columns: 52px minmax(0, 1fr);
            gap: 10px;
        }

        .applicant-final-review-search-wrap {
            grid-column: 1 / -1;
        }

        .applicant-final-review-toolbar .applicant-final-review-refresh-btn {
            width: 52px;
            padding: 0;
        }

        .applicant-final-review-toolbar #btnFinalReviewManualLookup {
            flex-basis: auto;
            width: 100%;
            padding: 0 14px;
        }

        .applicant-final-review-card {
            grid-template-columns: 1fr;
        }

        .applicant-final-review-card .applicant-final-review-btn,
        .applicant-final-review-back {
            width: 100%;
        }
    }

    .applicant-ref-copy {
        width: 100%;
        max-width: 460px;
        text-align: center;
    }

    .applicant-ref-copy .applicant-ref-kicker {
        margin: 0 0 6px;
        font-size: 11px;
        font-weight: 900;
        text-transform: uppercase;
        letter-spacing: 0.08em;
        color: #8b0000;
    }

    .applicant-ref-copy h4 {
        margin: 0 0 8px;
        font-size: 20px;
        font-weight: 900;
        color: #111827;
    }

    .applicant-ref-copy p {
        margin: 0;
        font-size: 13px;
        line-height: 1.55;
        color: #64748b;
    }

    .applicant-ref-lookup-row {
        display: flex;
        flex-direction: column;
        gap: 12px;
        width: 100%;
    }

    .applicant-ref-instruction {
        position: relative;
        min-height: 88px;
        padding: 14px 15px;
        border-radius: 16px;
        background: #fff7ed;
        border: 1px solid #fed7aa;
        color: #9a3412;
        font-size: 0;
        line-height: 1.5;
        box-shadow: 0 8px 18px rgba(180, 83, 9, 0.10);
    }

    .applicant-ref-panel { position: relative; }

    .applicant-ref-instruction strong {
        display: block;
        margin-bottom: 4px;
        font-size: 11px;
        font-weight: 900;
        letter-spacing: 0.06em;
        text-transform: uppercase;
    }

    .applicant-ref-instruction > strong:not(.applicant-ref-help-title) {
        display: none;
    }

    .applicant-ref-help-title {
        display: block;
        margin-bottom: 5px;
        color: #9a3412;
        font-size: 11px;
        font-weight: 900;
        letter-spacing: 0.06em;
        text-transform: uppercase;
    }

    .applicant-ref-help-copy {
        display: block;
        color: #9a3412;
        font-size: 12px;
        line-height: 1.5;
    }

    .applicant-ref-help-copy strong {
        display: inline;
        margin: 0;
        font-size: inherit;
        letter-spacing: 0;
        text-transform: none;
    }

    .applicant-ref-toggle-btn,
    .applicant-ref-action-btn {
        width: 100%;
        min-height: 58px;
        border-radius: 14px;
        border: 1px solid transparent;
        font-size: 14px;
        font-weight: 900;
        cursor: pointer;
        transition: transform .18s ease, box-shadow .18s ease, background .18s ease, color .18s ease, border-color .18s ease, filter .18s ease;
    }

    .applicant-ref-toggle-btn {
        position: relative;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        gap: 10px;
        padding: 0 20px;
        overflow: hidden;
        background: linear-gradient(135deg, #70131B, #8f2230);
        color: #facc15;
        border-color: rgba(112, 19, 27, 0.46);
        box-shadow: 0 12px 24px rgba(112, 19, 27, 0.22);
    }

    .applicant-ref-toggle-btn:hover,
    .applicant-ref-toggle-btn:focus {
        transform: translateY(-1px);
        background: linear-gradient(135deg, #facc15, #fde68a);
        color: #70131B;
        border-color: #facc15;
        box-shadow: 0 14px 24px rgba(112, 19, 27, 0.18);
        outline: none;
    }

    .applicant-ref-toggle-btn::after {
        content: "";
        position: absolute;
        inset: 0;
        background: linear-gradient(120deg, rgba(255, 255, 255, 0) 0%, rgba(255, 248, 196, 0.45) 50%, rgba(255, 255, 255, 0) 100%);
        transform: translateX(-140%);
        transition: transform .95s ease;
        pointer-events: none;
    }

    .applicant-ref-toggle-btn:hover::after,
    .applicant-ref-toggle-btn:focus::after {
        transform: translateX(140%);
    }

    .applicant-ref-toggle-btn svg,
    .applicant-ref-toggle-btn span {
        position: relative;
        z-index: 1;
    }

    .applicant-ref-toggle-btn svg {
        width: 18px;
        height: 18px;
        flex: 0 0 auto;
    }

    .applicant-ref-panel {
        width: 100%;
        display: none;
        flex-direction: column;
        gap: 12px;
        align-items: stretch;
        max-width: 760px;
    }

    .applicant-ref-panel.is-visible {
        display: flex;
    }

    #applicantRefModal .has-lookup-result .applicant-ref-panel {
        max-width: 960px;
    }

    .applicant-ref-field label {
        display: block;
        margin: 0 0 6px;
        font-size: 11px;
        font-weight: 900;
        text-transform: uppercase;
        letter-spacing: 0.06em;
        color: #64748b;
    }

    .applicant-ref-input {
        width: 100%;
        min-height: 48px;
        padding: 12px 14px;
        border: 1px solid rgba(112, 19, 27, 0.15);
        border-radius: 12px;
        background: linear-gradient(180deg, #ffffff, #fff8f6);
        color: #111827;
        font-size: 14px;
        font-weight: 700;
        box-shadow: 0 8px 18px rgba(15, 23, 42, 0.06), inset 0 1px 0 rgba(255,255,255,0.9);
        transition: border-color .2s ease, box-shadow .2s ease;
        outline: none;
    }
    .applicant-ref-input:focus {
        border-color: rgba(112, 19, 27, 0.42);
        box-shadow: 0 0 0 3px rgba(112, 19, 27, 0.08), 0 8px 18px rgba(15, 23, 42, 0.08);
    }

    .applicant-ref-actions {
        display: grid;
        grid-template-columns: repeat(2, minmax(0, 1fr));
        gap: 10px;
    }

    .applicant-ref-actions.has-draft-action {
        grid-template-columns: repeat(4, minmax(0, 1fr));
    }

    .applicant-ref-cancel-btn {
        background: #f1f5f9;
        color: #334155;
        border-color: #cbd5e1;
    }

    .applicant-ref-cancel-btn:hover,
    .applicant-ref-cancel-btn:focus {
        background: #e2e8f0;
        border-color: #b8c2d3;
        outline: none;
    }

    .applicant-ref-find-btn {
        position: relative;
        overflow: hidden;
        background: #70131b;
        color: #facc15;
        border-color: #facc15;
        box-shadow: 0 12px 24px rgba(127, 29, 29, 0.24);
        isolation: isolate;
        transition: background .18s ease, color .18s ease, transform .18s ease, box-shadow .18s ease, border-color .18s ease;
    }

    .applicant-ref-find-btn::after {
        content: "";
        position: absolute;
        inset: 0;
        background:
            linear-gradient(120deg,
                rgba(255, 248, 196, 0) 0%,
                rgba(255, 239, 181, 0.14) 22%,
                rgba(255, 239, 181, 0.52) 48%,
                rgba(255, 239, 181, 0.14) 72%,
                rgba(255, 248, 196, 0) 100%);
        transform: translateX(-135%);
        transition: transform 1.2s ease;
        pointer-events: none;
    }

    .applicant-ref-find-btn:hover,
    .applicant-ref-find-btn:focus {
        background: #facc15;
        transform: translateY(-1px);
        box-shadow: 0 16px 28px rgba(127, 29, 29, 0.3);
        color: #70131b;
        outline: none;
    }

    .applicant-ref-find-btn:hover::after,
    .applicant-ref-find-btn:focus::after {
        transform: translateX(135%);
    }

    .applicant-ref-draft-btn {
        display: none;
        position: relative;
        overflow: hidden;
        align-items: center;
        justify-content: center;
        gap: 6px;
        padding: 0 16px;
        text-align: center;
        line-height: 1;
        background: linear-gradient(180deg, #fff7ed 0%, #ffffff 100%);
        color: #70131b;
        border-color: rgba(112, 19, 27, 0.36);
        box-shadow: 0 10px 22px rgba(112, 19, 27, 0.08);
        isolation: isolate;
    }

    .applicant-ref-draft-btn:hover,
    .applicant-ref-draft-btn:focus {
        background: #facc15;
        color: #70131b;
        border-color: #facc15;
        box-shadow: none;
        transform: none;
        outline: none;
    }

    .applicant-ref-draft-btn svg {
        display: block;
        width: 17px;
        height: 17px;
        flex: 0 0 auto;
        padding: 0;
        background: transparent;
    }

    .applicant-ref-draft-btn span {
        display: block;
        line-height: 1;
    }

    #applicantRefModal .applicant-modal-shell.has-lookup-result.is-encode-workflow .applicant-ref-actions {
        display: grid;
        grid-template-columns: repeat(4, minmax(0, 1fr));
        align-items: center;
        margin-top: 0;
        padding-top: 0;
        gap: 12px;
    }

    #applicantRefModal .applicant-modal-shell.has-lookup-result.is-encode-workflow .applicant-ref-action-btn {
        width: 100%;
        min-width: 0;
        min-height: 46px;
        border-radius: 8px;
        font-size: 14px;
    }

    #applicantRefModal .applicant-modal-shell.has-lookup-result.is-final-review-workflow .applicant-ref-actions {
        display: grid;
        grid-template-columns: repeat(4, minmax(0, 1fr));
        align-items: center;
        gap: 12px;
    }

    #applicantRefModal .applicant-modal-shell.has-lookup-result.is-final-review-workflow .applicant-ref-action-btn {
        width: 100%;
        min-width: 0;
    }

    #applicantRefModal .applicant-modal-shell.has-lookup-result.is-encode-workflow .applicant-ref-cancel-btn {
        background: #ffffff;
        color: #334155;
        border-color: #dbe3ef;
        box-shadow: none;
    }

    #applicantRefModal .applicant-modal-shell.has-lookup-result.is-encode-workflow .applicant-ref-find-btn {
        background: #facc15;
        color: #70131b;
        border-color: #facc15;
        box-shadow: 0 10px 20px rgba(202, 138, 4, 0.18);
    }

    #applicantRefModal .applicant-modal-shell.has-lookup-result.is-encode-workflow .applicant-ref-find-btn:hover,
    #applicantRefModal .applicant-modal-shell.has-lookup-result.is-encode-workflow .applicant-ref-find-btn:focus {
        background: #70131b;
        color: #facc15;
        border-color: #facc15;
    }

    #applicantRefModal .applicant-modal-shell.has-lookup-result.is-final-review-workflow .applicant-ref-result {
        position: relative;
        min-height: 86px;
        padding: 48px 24px 6px 112px;
        border-radius: 10px 10px 0 0;
        border: 1px solid #e5e7eb;
        border-bottom: 0;
        background: #ffffff;
        box-shadow: 0 8px 20px rgba(15, 23, 42, 0.05);
        color: #111827;
        align-items: flex-start;
    }

    #applicantRefModal .applicant-modal-shell.has-lookup-result.is-final-review-workflow .applicant-ref-result::before {
        content: "Applicant Information";
        position: absolute;
        top: 18px;
        left: 52px;
        color: #111827;
        font-size: 17px;
        font-weight: 900;
    }

    #applicantRefModal .applicant-modal-shell.has-lookup-result.is-final-review-workflow .applicant-ref-result::after {
        content: attr(data-initials);
        position: absolute;
        left: 26px;
        top: 66px;
        width: 58px;
        height: 58px;
        display: flex;
        align-items: center;
        justify-content: center;
        border-radius: 50%;
        background:
            radial-gradient(circle at 50% 42%, transparent 0 8px, #fef2f2 9px 100%),
            linear-gradient(135deg, #fee2e2, #fff7ed);
        border: 1px solid #fecaca;
        color: #991b1b;
        font-size: 20px;
        font-weight: 900;
        letter-spacing: 0.03em;
    }

    #applicantRefModal .applicant-modal-shell.has-lookup-result.is-final-review-workflow .applicant-ref-result strong {
        margin-top: 5px;
        color: #111827;
        font-size: 16px;
        letter-spacing: 0;
        text-transform: none;
    }

    #applicantRefModal .applicant-modal-shell.has-lookup-result.is-final-review-workflow .applicant-found-copy {
        color: transparent;
        line-height: 1.35;
    }

    #applicantRefModal .applicant-modal-shell.has-lookup-result.is-final-review-workflow .applicant-condition-badge {
        min-height: 26px;
        padding: 5px 9px;
        border-radius: 999px;
        font-size: 9px;
    }

    #applicantRefModal .applicant-modal-shell.has-lookup-result.is-final-review-workflow .applicant-lookup-details {
        margin-top: -10px;
        padding: 0 24px 10px 112px;
        border-radius: 0;
        border-color: #e5e7eb;
        border-top: 0;
        border-bottom: 0;
        background: #ffffff;
        box-shadow: none;
    }

    #applicantRefModal .applicant-modal-shell.has-lookup-result.is-final-review-workflow .applicant-lookup-grid {
        grid-template-columns: repeat(4, minmax(0, 1fr));
        gap: 16px;
    }

    #applicantRefModal .applicant-modal-shell.has-lookup-result.is-final-review-workflow .applicant-lookup-card {
        align-items: center;
        gap: 8px;
        padding: 9px 8px;
        border: 0;
        background: transparent;
        box-shadow: none;
    }

    #applicantRefModal .applicant-modal-shell.has-lookup-result.is-final-review-workflow .applicant-lookup-card:hover {
        background: transparent;
        border-color: transparent;
        box-shadow: none;
    }

    #applicantRefModal .applicant-modal-shell.has-lookup-result.is-final-review-workflow .applicant-lookup-icon {
        display: none;
    }

    #applicantRefModal .applicant-modal-shell.has-lookup-result.is-final-review-workflow .applicant-lookup-label {
        margin-bottom: 3px;
        color: #4b5563;
        font-size: 12px;
        letter-spacing: 0.02em;
        text-transform: none;
    }

    #applicantRefModal .applicant-modal-shell.has-lookup-result.is-final-review-workflow .applicant-lookup-value {
        color: #111827;
        font-size: 14px;
        font-weight: 800;
    }

    #applicantRefModal .applicant-modal-shell.has-lookup-result.is-final-review-workflow .applicant-file-actions {
        display: flex;
        justify-content: flex-start;
        align-items: center;
        flex-wrap: wrap;
        gap: 8px;
        margin-top: -10px;
        padding: 8px 24px 16px;
        border: 1px solid #e5e7eb;
        border-top: 0;
        border-radius: 0 0 10px 10px;
        background: #ffffff;
        box-shadow: 0 10px 20px rgba(15, 23, 42, 0.05);
    }

    #applicantRefModal .applicant-modal-shell.has-lookup-result.is-final-review-workflow .applicant-file-action {
        width: auto;
        min-height: 34px;
        padding: 7px 12px;
        border-radius: 8px;
        background: #ffffff;
        border-color: rgba(112, 19, 27, 0.22);
        color: #70131b;
        box-shadow: none;
        font-size: 12px;
    }

    #applicantRefModal .applicant-modal-shell.has-lookup-result.is-final-review-workflow .applicant-file-action svg {
        width: 15px;
        height: 15px;
        color: #70131b;
    }

    #applicantRefModal .applicant-modal-shell.has-lookup-result.is-employee-lookup .applicant-ref-result {
        position: relative;
        min-height: 86px;
        padding: 48px 24px 6px 112px;
        border-radius: 10px 10px 0 0;
        border: 1px solid #e5e7eb;
        border-bottom: 0;
        background: #ffffff;
        box-shadow: 0 8px 20px rgba(15, 23, 42, 0.05);
        color: #111827;
        align-items: flex-start;
    }

    #applicantRefModal .applicant-modal-shell.has-lookup-result.is-employee-lookup .applicant-ref-result::before {
        content: "Employee Information";
        position: absolute;
        top: 18px;
        left: 52px;
        color: #111827;
        font-size: 17px;
        font-weight: 900;
    }

    #applicantRefModal .applicant-modal-shell.has-lookup-result.is-employee-lookup .applicant-ref-result::after {
        content: attr(data-initials);
        position: absolute;
        left: 26px;
        top: 66px;
        width: 58px;
        height: 58px;
        display: flex;
        align-items: center;
        justify-content: center;
        border-radius: 50%;
        background:
            radial-gradient(circle at 50% 42%, transparent 0 8px, #fef2f2 9px 100%),
            linear-gradient(135deg, #fee2e2, #fff7ed);
        border: 1px solid #fecaca;
        color: #991b1b;
        font-size: 20px;
        font-weight: 900;
        letter-spacing: 0.03em;
    }

    #applicantRefModal .applicant-modal-shell.has-lookup-result.is-employee-lookup .applicant-ref-result strong {
        margin-top: 5px;
        color: #111827;
        font-size: 16px;
        letter-spacing: 0;
        text-transform: none;
    }

    #applicantRefModal .applicant-modal-shell.has-lookup-result.is-employee-lookup .applicant-found-copy {
        color: transparent;
        line-height: 1.35;
    }

    #applicantRefModal .applicant-modal-shell.has-lookup-result.is-employee-lookup .applicant-condition-badge {
        min-height: 26px;
        padding: 5px 9px;
        border-radius: 999px;
        font-size: 9px;
    }

    #applicantRefModal .applicant-modal-shell.has-lookup-result.is-employee-lookup .applicant-lookup-details {
        margin-top: -10px;
        padding: 0 24px 12px 112px;
        border-radius: 0 0 10px 10px;
        border-color: #e5e7eb;
        border-top: 0;
        background: #ffffff;
        box-shadow: 0 10px 20px rgba(15, 23, 42, 0.05);
    }

    #applicantRefModal .applicant-modal-shell.has-lookup-result.is-employee-lookup .applicant-lookup-grid {
        grid-template-columns: repeat(4, minmax(0, 1fr));
        gap: 16px;
    }

    #applicantRefModal .applicant-modal-shell.has-lookup-result.is-employee-lookup .applicant-lookup-card {
        align-items: center;
        gap: 8px;
        padding: 9px 8px;
        border: 0;
        background: transparent;
        box-shadow: none;
    }

    #applicantRefModal .applicant-modal-shell.has-lookup-result.is-employee-lookup .applicant-lookup-card:hover {
        background: transparent;
        border-color: transparent;
        box-shadow: none;
    }

    #applicantRefModal .applicant-modal-shell.has-lookup-result.is-employee-lookup .applicant-lookup-icon {
        display: none;
    }

    #applicantRefModal .applicant-modal-shell.has-lookup-result.is-employee-lookup .applicant-lookup-label {
        margin-bottom: 3px;
        color: #4b5563;
        font-size: 12px;
        letter-spacing: 0.02em;
        text-transform: none;
    }

    #applicantRefModal .applicant-modal-shell.has-lookup-result.is-employee-lookup .applicant-lookup-value {
        color: #111827;
        font-size: 14px;
        font-weight: 800;
    }

    .applicant-ref-result {
        display: none;
        padding: 20px 18px;
        border-radius: 12px;
        background: #ecfdf5;
        border: 1px solid #a7f3d0;
        color: #047857;
        font-size: 14px;
        font-weight: 700;
        line-height: 1.6;
        align-items: center;
        justify-content: space-between;
        gap: 14px;
    }

    .applicant-ref-result strong {
        display: block;
        margin-top: 8px;
        color: #064e3b;
        font-size: 18px;
        font-weight: 800;
        text-transform: uppercase;
        letter-spacing: 0.5px;
    }

    .applicant-found-copy {
        min-width: 0;
    }

    .applicant-condition-badge {
        flex: 0 0 auto;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        min-height: 34px;
        padding: 8px 12px;
        border-radius: 999px;
        border: 1px solid #bfdbfe;
        background: #eff6ff;
        color: #1d4ed8;
        font-size: 11px;
        font-weight: 900;
        letter-spacing: .03em;
        text-transform: uppercase;
        white-space: nowrap;
    }

    .applicant-condition-badge.has-condition {
        border-color: #fecaca;
        background: #fef2f2;
        color: #991b1b;
    }

    #applicantRefModal .applicant-modal-shell.has-lookup-result.is-encode-workflow .applicant-ref-result {
        position: relative;
        min-height: 94px;
        padding: 54px 28px 8px 128px;
        border-radius: 10px 10px 0 0;
        border: 1px solid #e5e7eb;
        border-bottom: 0;
        background: #ffffff;
        box-shadow: 0 8px 20px rgba(15, 23, 42, 0.05);
        color: #111827;
        align-items: flex-start;
    }

    #applicantRefModal .applicant-modal-shell.has-lookup-result.is-encode-workflow .applicant-ref-result::before {
        content: "Applicant Information";
        position: absolute;
        top: 20px;
        left: 54px;
        color: #70131b;
        font-size: 18px;
        font-weight: 900;
    }

    #applicantRefModal .applicant-modal-shell.has-lookup-result.is-encode-workflow .applicant-ref-result::after {
        content: attr(data-initials);
        position: absolute;
        left: 28px;
        top: 72px;
        width: 70px;
        height: 70px;
        display: flex;
        align-items: center;
        justify-content: center;
        border-radius: 50%;
        background:
            radial-gradient(circle at 50% 42%, transparent 0 8px, #fef2f2 9px 100%),
            linear-gradient(135deg, #fee2e2, #fff7ed);
        border: 1px solid #fecaca;
        color: #991b1b;
        font-size: 22px;
        font-weight: 900;
        letter-spacing: 0.03em;
    }

    #applicantRefModal .applicant-modal-shell.has-lookup-result.is-encode-workflow .applicant-ref-result strong {
        margin-top: 5px;
        color: #111827;
        font-size: 18px;
        letter-spacing: 0;
        text-transform: none;
    }

    #applicantRefModal .applicant-modal-shell.has-lookup-result.is-encode-workflow .applicant-found-copy {
        color: transparent;
        line-height: 1.35;
    }

    #applicantRefModal .applicant-modal-shell.has-lookup-result.is-encode-workflow .applicant-condition-badge {
        min-height: 26px;
        padding: 5px 9px;
        border-radius: 999px;
        font-size: 9px;
    }

    .applicant-lookup-details {
        display: none;
        width: 100%;
        margin-top: 12px;
        padding: 14px;
        border-radius: 14px;
        border: 1px solid rgba(112, 19, 27, 0.14);
        background: linear-gradient(180deg, #ffffff, #fff8f6);
        box-shadow: 0 10px 22px rgba(15, 23, 42, 0.06);
    }

    .applicant-lookup-details.is-summary-visible {
        display: block;
    }

    #applicantRefModal .applicant-modal-shell.has-lookup-result.is-encode-workflow .applicant-lookup-details {
        margin-top: -10px;
        padding: 0 28px 10px 128px;
        border-radius: 0;
        border-color: #e5e7eb;
        border-top: 0;
        border-bottom: 0;
        background: #ffffff;
        box-shadow: none;
    }

    #applicantRefModal .applicant-modal-shell.has-lookup-result.is-encode-workflow .applicant-lookup-grid {
        grid-template-columns: repeat(4, minmax(0, 1fr));
        gap: 18px;
    }

    #applicantRefModal .applicant-modal-shell.has-lookup-result.is-encode-workflow .applicant-lookup-card {
        align-items: center;
        gap: 8px;
        padding: 10px 8px;
        border: 0;
        background: transparent;
        box-shadow: none;
    }

    #applicantRefModal .applicant-modal-shell.has-lookup-result.is-encode-workflow .applicant-lookup-card:hover {
        background: transparent;
        border-color: transparent;
        box-shadow: none;
    }

    #applicantRefModal .applicant-modal-shell.has-lookup-result.is-encode-workflow .applicant-lookup-icon {
        display: none;
    }

    #applicantRefModal .applicant-modal-shell.has-lookup-result.is-encode-workflow .applicant-lookup-label {
        margin-bottom: 3px;
        color: #4b5563;
        font-size: 13px;
        text-transform: none;
        letter-spacing: 0.02em;
    }

    #applicantRefModal .applicant-modal-shell.has-lookup-result.is-encode-workflow .applicant-lookup-value {
        color: #111827;
        font-size: 16px;
        font-weight: 800;
    }

    #applicantRefModal .applicant-modal-shell.has-lookup-result.is-encode-workflow .applicant-reference-copy {
        width: 28px;
        height: 28px;
        flex-basis: 28px;
    }

    .applicant-information-details {
        display: none;
        width: 100%;
        margin-top: 12px;
        padding: 14px;
        border-radius: 14px;
        border: 1px solid rgba(112, 19, 27, 0.14);
        background: linear-gradient(180deg, #fffdf8, #ffffff);
        box-shadow: 0 10px 22px rgba(15, 23, 42, 0.05);
    }

    .applicant-information-details.is-visible {
        display: block;
    }

    .applicant-health-info-modal {
        width: min(1040px, calc(100vw - 48px));
    }

    .applicant-health-info-modal-body {
        padding: 18px;
        max-height: calc(100vh - 140px);
    }

    .applicant-health-info-modal .applicant-information-details {
        margin: 0;
        padding: 0;
        border: 0;
        border-radius: 0;
        background: transparent;
        box-shadow: none;
    }

    .applicant-medical-condition-modal {
        width: min(760px, calc(100vw - 48px));
    }

    .applicant-medical-condition-modal-body {
        padding: 18px;
        max-height: calc(100vh - 140px);
    }

    .applicant-medical-condition-modal .applicant-medical-condition-details {
        margin: 0;
    }

    .applicant-medical-condition-details {
        display: none;
        width: 100%;
        margin-top: 12px;
        padding: 14px;
        border-radius: 14px;
        border: 1px solid rgba(112, 19, 27, 0.14);
        background: linear-gradient(180deg, #fffdf8, #ffffff);
        box-shadow: 0 10px 22px rgba(15, 23, 42, 0.05);
    }

    .applicant-medical-condition-details.is-visible {
        display: block;
    }

    .applicant-review-source-grid {
        margin: 12px 0 16px;
    }

    .applicant-lookup-grid {
        display: grid;
        grid-template-columns: repeat(2, minmax(0, 1fr));
        gap: 16px;
    }

    .applicant-lookup-item {
        min-width: 0;
    }

    .applicant-lookup-card {
        display: flex;
        align-items: flex-start;
        gap: 12px;
        padding: 16px;
        border-radius: 12px;
        background: linear-gradient(135deg, #f8f9fa 0%, #ffffff 100%);
        border: 1px solid #e2e8f0;
        transition: all .2s ease;
    }

    .applicant-lookup-card:hover {
        border-color: #facc15;
        box-shadow: 0 4px 12px rgba(250, 204, 21, 0.12);
        background: linear-gradient(135deg, #fffbeb 0%, #fefce8 100%);
    }

    .applicant-lookup-icon {
        display: flex;
        align-items: center;
        justify-content: center;
        width: 40px;
        height: 40px;
        min-width: 40px;
        border-radius: 8px;
        background: linear-gradient(135deg, #facc15, #fde68a);
        color: #701315;
        font-size: 12px;
        font-weight: 900;
        letter-spacing: 0;
    }

    .applicant-lookup-icon.is-maroon {
        background: linear-gradient(135deg, #7f1d1d, #a11d2a);
        color: #ffffff;
        box-shadow: 0 8px 16px rgba(127, 29, 29, 0.18);
    }

    .applicant-lookup-content {
        flex: 1;
        min-width: 0;
    }

    .applicant-lookup-label {
        margin: 0 0 6px;
        font-size: 11px;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: 0.05em;
        color: #64748b;
    }

    .applicant-lookup-value {
        margin: 0;
        font-size: 14px;
        font-weight: 600;
        color: #1e293b;
        word-break: break-word;
        line-height: 1.4;
    }

    .applicant-reference-value-row {
        display: flex;
        align-items: center;
        gap: 8px;
        min-width: 0;
    }

    .applicant-reference-value-row .applicant-lookup-value {
        flex: 1;
        min-width: 0;
    }

    .applicant-reference-copy {
        width: 34px;
        height: 34px;
        flex: 0 0 34px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        border: 1px solid #d6a900;
        border-radius: 8px;
        background: #facc15;
        color: #111827;
        cursor: pointer;
    }

    .applicant-reference-copy:hover,
    .applicant-reference-copy:focus-visible {
        background: #fde047;
        outline: 2px solid rgba(112, 19, 27, 0.2);
        outline-offset: 2px;
    }

    .applicant-reference-copy svg {
        width: 18px;
        height: 18px;
        stroke: currentColor;
        fill: none;
        stroke-width: 2;
    }

    .applicant-upload-wrap {
        display: none;
        width: 100%;
        gap: 12px;
        flex-direction: column;
    }

    .applicant-file-actions {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(180px, 1fr));
        gap: 12px;
        align-items: start;
        width: 100%;
    }

    #applicantRefModal .applicant-modal-shell.is-employee-lookup .applicant-file-actions {
        position: sticky;
        top: -16px;
        z-index: 70;
        grid-template-columns: repeat(4, minmax(150px, 1fr));
        justify-content: start;
        margin: -12px 0 6px;
        padding: 8px;
        border: 1px solid rgba(255, 255, 255, 0.48);
        border-radius: 16px;
        background: rgba(255, 255, 255, 0.34);
        backdrop-filter: blur(16px);
        -webkit-backdrop-filter: blur(16px);
        box-shadow: 0 14px 32px rgba(15, 23, 42, 0.08);
    }

    #applicantRefModal .applicant-modal-shell.is-employee-lookup .applicant-file-actions .applicant-file-action {
        min-height: 42px;
        padding: 9px 12px;
        border-color: rgba(112, 19, 27, 0.28);
        background: rgba(112, 19, 27, 0.92);
        font-size: 12px;
        line-height: 1.2;
        box-shadow: 0 12px 24px rgba(112, 19, 27, 0.16);
    }

    #applicantRefModal .applicant-modal-shell.is-employee-lookup .applicant-file-actions .applicant-file-action svg {
        width: 15px;
        height: 15px;
    }

    #applicantRefModal .applicant-modal-shell.is-employee-lookup .applicant-file-actions .applicant-documents-count {
        min-width: 18px;
        height: 18px;
        padding: 2px 6px;
        font-size: 10px;
    }

    #applicantRefModal .applicant-modal-shell.is-employee-lookup #btnViewSavedAssessment {
        display: none !important;
    }

    #applicantRefModal .applicant-modal-shell.has-lookup-result.is-final-review-workflow .applicant-file-actions {
        position: sticky;
        top: -1px;
        z-index: 80;
        align-self: stretch;
        width: calc(100% - 32px);
        margin: -1px auto 10px !important;
        padding: 6px 10px !important;
        border: 1px solid rgba(127, 29, 45, 0.16);
        border-radius: 14px;
        background: rgba(255, 241, 242, .86) !important;
        backdrop-filter: blur(14px);
        -webkit-backdrop-filter: blur(14px);
        box-shadow: 0 10px 22px rgba(127, 29, 45, 0.10);
        transition: width .18s ease, margin .18s ease, border-radius .18s ease, padding .18s ease, box-shadow .18s ease;
    }

    #applicantRefModal .applicant-modal-shell.has-lookup-result.is-final-review-workflow.is-final-review-toolbar-stuck .applicant-file-actions {
        top: -10px;
        width: calc(100% + 48px);
        margin: -30px -24px 8px !important;
        padding: 5px 24px 6px !important;
        border-top: 0;
        border-left: 0;
        border-right: 0;
        border-radius: 0 0 12px 12px;
        background: rgba(255, 241, 242, .76) !important;
        box-shadow: 0 14px 26px rgba(127, 29, 45, 0.14);
    }

    #applicantRefModal .applicant-modal-shell.has-lookup-result.is-final-review-workflow.is-final-review-toolbar-stuck .applicant-file-actions::before {
        content: "";
        position: absolute;
        left: 0;
        right: 0;
        top: -34px;
        height: 34px;
        background: rgba(255, 241, 242, .72);
        backdrop-filter: blur(14px);
        -webkit-backdrop-filter: blur(14px);
        pointer-events: none;
    }

    #applicantRefModal .applicant-modal-shell.has-lookup-result.is-final-review-workflow .applicant-final-review-action-row .applicant-file-actions {
        position: static;
        top: auto;
        z-index: auto;
        display: flex;
        flex: 1 1 auto;
        align-items: center;
        justify-content: flex-start;
        flex-wrap: wrap;
        width: auto;
        margin: 0 !important;
        padding: 0 !important;
        gap: 8px;
        border: 0;
        border-radius: 0;
        background: transparent !important;
        box-shadow: none;
        backdrop-filter: none;
        -webkit-backdrop-filter: none;
    }

    #applicantRefModal .applicant-modal-shell.has-lookup-result.is-final-review-workflow .applicant-final-review-action-row .applicant-file-actions::before {
        display: none;
    }

    #applicantRefModal .applicant-modal-shell.has-lookup-result.is-final-review-workflow .applicant-final-review-action-row .applicant-file-action {
        width: auto;
        min-height: 42px;
        padding: 9px 12px;
        border-radius: 8px;
        border-color: #70131b;
        background: #70131b;
        color: #facc15 !important;
        box-shadow: 0 10px 20px rgba(112, 19, 27, 0.12);
        font-size: 12px;
        line-height: 1.2;
    }

    #applicantRefModal .applicant-modal-shell.has-lookup-result.is-final-review-workflow .applicant-final-review-action-row .applicant-file-action svg {
        width: 15px;
        height: 15px;
        color: currentColor;
    }

    #applicantRefModal .applicant-modal-shell.has-lookup-result.is-final-review-workflow .applicant-final-review-action-row .applicant-documents-count {
        min-width: 18px;
        height: 18px;
        padding: 2px 6px;
        font-size: 10px;
    }

    #applicantRefModal .applicant-modal-shell.has-lookup-result.is-final-review-workflow .applicant-final-review-action-row .applicant-file-action:hover,
    #applicantRefModal .applicant-modal-shell.has-lookup-result.is-final-review-workflow .applicant-final-review-action-row .applicant-file-action:focus {
        background: #8a101d;
        color: #facc15;
        border-color: #facc15;
    }

    .applicant-pending-history-wrap {
        position: relative;
    }

    .applicant-pending-history-bubble {
        display: none;
        position: absolute;
        right: 0;
        top: calc(100% + 10px);
        z-index: 20;
        width: min(320px, 80vw);
        padding: 14px;
        border: 1px solid rgba(112, 19, 27, 0.18);
        border-radius: 14px;
        background: #fff7ed;
        color: #1f2937;
        box-shadow: 0 18px 36px rgba(15, 23, 42, 0.18);
    }

    .applicant-pending-history-wrap.is-open .applicant-pending-history-bubble {
        display: grid;
        gap: 10px;
    }

    .applicant-pending-history-bubble span {
        display: block;
        font-size: 11px;
        font-weight: 900;
        letter-spacing: .04em;
        text-transform: uppercase;
        color: #7f1d2d;
    }

    .applicant-pending-history-bubble strong {
        display: block;
        margin-top: 3px;
        color: #111827;
        font-size: 13px;
        line-height: 1.4;
        word-break: break-word;
    }

    .health-info-editor {
        display: grid;
        grid-template-columns: 220px minmax(0, 1fr);
        border: 1px solid rgba(148, 163, 184, 0.28);
        border-radius: 14px;
        overflow: hidden;
        background: #ffffff;
        box-shadow: 0 12px 30px rgba(15, 23, 42, 0.06);
    }

    .health-info-tabs {
        display: flex;
        flex-direction: column;
        padding: 12px;
        gap: 6px;
        background: linear-gradient(180deg, #fffaf0 0%, #ffffff 72%);
        border-right: 1px solid rgba(148, 163, 184, 0.22);
    }

    .health-info-tab {
        display: flex;
        align-items: center;
        gap: 10px;
        width: 100%;
        min-height: 42px;
        padding: 10px 12px;
        border: 1px solid transparent;
        border-radius: 8px;
        background: transparent;
        color: #334155;
        font-size: 12px;
        font-weight: 850;
        text-align: left;
        cursor: pointer;
    }

    .health-info-tab.is-active {
        border-color: rgba(250, 204, 21, 0.85);
        background: #fffbeb;
        color: #70131b;
        box-shadow: inset 3px 0 0 #facc15;
    }

    .health-info-tab svg {
        width: 16px;
        height: 16px;
        color: currentColor;
    }

    .health-info-content {
        min-width: 0;
        padding: 18px;
    }

    .health-info-header {
        display: flex;
        align-items: flex-start;
        justify-content: space-between;
        gap: 16px;
        padding-bottom: 12px;
        border-bottom: 1px solid rgba(148, 163, 184, 0.22);
    }

    .health-info-kicker {
        margin: 0 0 3px;
        color: #64748b;
        font-size: 10px;
        font-weight: 900;
        letter-spacing: .08em;
        text-transform: uppercase;
    }

    .health-info-header h4 {
        margin: 0;
        color: #111827;
        font-size: 15px;
        font-weight: 900;
    }

    .health-info-actions {
        display: flex;
        align-items: center;
        gap: 8px;
        flex-wrap: wrap;
        justify-content: flex-end;
    }

    .health-info-edit-btn,
    .health-info-cancel-btn,
    .health-info-save-btn {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        gap: 7px;
        min-height: 34px;
        padding: 8px 13px;
        border-radius: 8px;
        font-size: 12px;
        font-weight: 900;
        cursor: pointer;
    }

    .health-info-edit-btn,
    .health-info-cancel-btn {
        border: 1px solid rgba(112, 19, 27, 0.18);
        background: #ffffff;
        color: #70131b;
    }

    .health-info-save-btn {
        border: 1px solid #facc15;
        background: #facc15;
        color: #70131b;
        box-shadow: 0 10px 18px rgba(202, 138, 4, .18);
    }

    .health-info-edit-btn svg {
        width: 15px;
        height: 15px;
    }

    .health-info-fields {
        display: grid;
        grid-template-columns: repeat(2, minmax(0, 1fr));
        gap: 0 22px;
        padding-top: 8px;
    }

    .health-info-field {
        min-width: 0;
        padding: 12px 0;
        border-bottom: 1px solid rgba(148, 163, 184, 0.18);
    }

    .health-info-field label {
        display: block;
        margin-bottom: 5px;
        color: #64748b;
        font-size: 10px;
        font-weight: 900;
        letter-spacing: .04em;
        text-transform: uppercase;
    }

    .health-info-value {
        min-height: 28px;
        color: #111827;
        font-size: 13px;
        font-weight: 800;
        line-height: 1.45;
        word-break: break-word;
        white-space: pre-line;
    }

    .health-info-input {
        width: 100%;
        min-height: 38px;
        padding: 9px 11px;
        border: 1px solid rgba(148, 163, 184, 0.45);
        border-radius: 8px;
        background: #ffffff;
        color: #111827;
        font-size: 13px;
        font-weight: 700;
    }

    .health-info-field.is-hidden {
        display: none;
    }

    .health-info-checkbox-grid {
        display: grid;
        grid-template-columns: repeat(2, minmax(0, 1fr));
        gap: 8px;
    }

    .health-info-check-option {
        display: flex;
        align-items: center;
        gap: 8px;
        min-height: 36px;
        padding: 8px 10px;
        border: 1px solid rgba(148, 163, 184, 0.35);
        border-radius: 9px;
        background: #fff;
        color: #111827;
        font-size: 12px;
        font-weight: 800;
        line-height: 1.25;
    }

    .health-info-check-option input {
        width: 16px;
        height: 16px;
        accent-color: #70131b;
    }

    .health-info-vaccine-grid {
        grid-column: 1 / -1;
        display: grid;
        grid-template-columns: repeat(2, minmax(0, 1fr));
        gap: 10px 14px;
        padding: 12px;
        border: 1px solid rgba(148, 163, 184, 0.24);
        border-radius: 12px;
        background: #f8fafc;
    }

    .health-info-vaccine-dose {
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 8px;
    }

    .health-info-vaccine-dose strong {
        grid-column: 1 / -1;
        color: #70131b;
        font-size: 11px;
        font-weight: 900;
        text-transform: uppercase;
        letter-spacing: .04em;
    }

    textarea.health-info-input {
        min-height: 78px;
        resize: vertical;
    }

    @media (max-width: 760px) {
        .health-info-editor {
            grid-template-columns: 1fr;
        }

        .health-info-tabs {
            border-right: 0;
            border-bottom: 1px solid rgba(148, 163, 184, 0.22);
        }

        .health-info-fields {
            grid-template-columns: 1fr;
        }

        .health-info-checkbox-grid,
        .health-info-vaccine-grid,
        .health-info-vaccine-dose {
            grid-template-columns: 1fr;
        }
    }

    #applicantRefModal .applicant-modal-shell.has-lookup-result.is-encode-workflow .applicant-file-actions {
        display: flex;
        justify-content: flex-start;
        align-items: center;
        flex-wrap: wrap;
        gap: 8px;
        margin-top: -10px;
        padding: 8px 28px 18px 28px;
        border: 1px solid #e5e7eb;
        border-top: 0;
        border-radius: 0 0 10px 10px;
        background: #ffffff;
        box-shadow: 0 10px 20px rgba(15, 23, 42, 0.05);
    }

    #applicantRefModal .applicant-modal-shell.has-lookup-result.is-encode-workflow .applicant-file-action {
        width: auto;
        min-height: 34px;
        padding: 7px 12px;
        border-radius: 8px;
        background: #ffffff;
        border-color: rgba(112, 19, 27, 0.22);
        color: #70131b;
        box-shadow: none;
        font-size: 12px;
    }

    #applicantRefModal .applicant-modal-shell.has-lookup-result.is-encode-workflow .applicant-file-action:hover {
        background: #fff7ed;
        border-color: #facc15;
        color: #70131b;
    }

    #applicantRefModal .applicant-modal-shell.has-lookup-result.is-encode-workflow .applicant-file-action svg {
        width: 15px;
        height: 15px;
        color: #70131b;
    }

    .saved-review-modal {
        width: min(94vw, 760px);
    }

    .saved-review-body {
        padding: 20px 24px 24px;
    }

    .saved-review-grid {
        display: grid;
        grid-template-columns: repeat(2, minmax(0, 1fr));
        gap: 10px;
    }

    .saved-review-card {
        min-width: 0;
        padding: 12px 14px;
        border: 1px solid rgba(112, 19, 27, 0.14);
        border-radius: 8px;
        background: #fffdfa;
    }

    .saved-review-card span {
        display: block;
        margin-bottom: 5px;
        color: #64748b;
        font-size: 10px;
        font-weight: 800;
        text-transform: uppercase;
    }

    .saved-review-card strong {
        display: block;
        color: #1f2937;
        font-size: 13px;
        line-height: 1.45;
        overflow-wrap: anywhere;
    }

    html[data-theme="dark"] .saved-review-card {
        border-color: rgba(250, 204, 21, 0.16);
        background: rgba(15, 23, 42, 0.8);
    }

    html[data-theme="dark"] .saved-review-card strong {
        color: #f8fafc;
    }

    .applicant-file-actions .applicant-upload-wrap {
        min-width: 0;
    }

    .applicant-file-action {
        position: relative;
        z-index: 0;
        overflow: hidden;
        min-height: 50px;
        border: 1px solid #70131b !important;
        border-radius: 10px !important;
        background: #70131b !important;
        color: #facc15 !important;
        box-shadow: 0 8px 18px rgba(112, 19, 27, .16);
        isolation: isolate;
        transition: background .18s ease, color .18s ease, transform .18s ease, box-shadow .18s ease, border-color .18s ease !important;
    }

    .applicant-file-action::after {
        content: "";
        position: absolute;
        inset: 0;
        background:
            linear-gradient(120deg,
                rgba(255, 248, 196, 0) 0%,
                rgba(255, 239, 181, 0.14) 22%,
                rgba(255, 239, 181, 0.52) 48%,
                rgba(255, 239, 181, 0.14) 72%,
                rgba(255, 248, 196, 0) 100%);
        transform: translateX(-135%);
        transition: transform 1.2s ease;
        pointer-events: none;
    }

    .applicant-file-action > * {
        position: relative;
        z-index: 1;
    }

    .applicant-file-action:hover,
    .applicant-file-action:focus {
        background: #facc15 !important;
        color: #70131b !important;
        border-color: #facc15 !important;
        transform: translateY(-1px);
        box-shadow: 0 12px 22px rgba(202, 138, 4, .24);
        outline: none;
    }

    .applicant-file-action:hover::after,
    .applicant-file-action:focus::after {
        transform: translateX(135%);
    }

    #applicantRefModal .applicant-modal-shell.has-lookup-result.is-encode-workflow .applicant-file-action,
    #applicantRefModal .applicant-modal-shell.has-lookup-result.is-final-review-workflow .applicant-file-action {
        width: auto !important;
        min-height: 30px !important;
        padding: 6px 11px !important;
        border: 1px solid rgba(112, 19, 27, 0.18) !important;
        border-radius: 8px !important;
        background: rgba(255, 255, 255, 0.78) !important;
        color: #70131b !important;
        box-shadow: 0 8px 18px rgba(127, 29, 45, 0.05) !important;
        font-size: 11.5px !important;
    }

    #applicantRefModal .applicant-modal-shell.has-lookup-result.is-encode-workflow .applicant-file-action svg,
    #applicantRefModal .applicant-modal-shell.has-lookup-result.is-final-review-workflow .applicant-file-action svg {
        width: 15px !important;
        height: 15px !important;
        color: #70131b !important;
    }

    #applicantRefModal .applicant-modal-shell.has-lookup-result.is-encode-workflow .applicant-file-action:hover,
    #applicantRefModal .applicant-modal-shell.has-lookup-result.is-encode-workflow .applicant-file-action:focus,
    #applicantRefModal .applicant-modal-shell.has-lookup-result.is-final-review-workflow .applicant-file-action:hover,
    #applicantRefModal .applicant-modal-shell.has-lookup-result.is-final-review-workflow .applicant-file-action:focus {
        background: #fff7ed !important;
        border-color: #facc15 !important;
        color: #70131b !important;
        box-shadow: 0 10px 20px rgba(127, 29, 45, 0.08) !important;
    }

    #applicantRefModal .applicant-modal-shell.has-lookup-result.is-final-review-workflow .applicant-final-review-action-row .applicant-file-action {
        min-height: 42px !important;
        padding: 9px 12px !important;
        border-color: #70131b !important;
        background: #70131b !important;
        color: #facc15 !important;
        font-size: 12px !important;
        line-height: 1.2 !important;
        box-shadow: 0 10px 20px rgba(112, 19, 27, 0.12) !important;
    }

    #applicantRefModal .applicant-modal-shell.has-lookup-result.is-final-review-workflow .applicant-final-review-action-row .applicant-file-action svg {
        width: 15px !important;
        height: 15px !important;
        color: currentColor !important;
    }

    #applicantRefModal .applicant-modal-shell.has-lookup-result.is-final-review-workflow .applicant-final-review-action-row .applicant-file-action:hover,
    #applicantRefModal .applicant-modal-shell.has-lookup-result.is-final-review-workflow .applicant-final-review-action-row .applicant-file-action:focus {
        background: #8a101d !important;
        color: #facc15 !important;
        border-color: #facc15 !important;
    }

    .applicant-upload-preview-area {
        width: 100%;
    }

    .applicant-upload-preview-container {
        position: relative;
        width: 100%;
        padding-top: 100%;
        border-radius: 14px;
        overflow: hidden;
        background: linear-gradient(135deg, #f8f9fa 0%, #ffffff 100%);
        border: 2px solid #facc15;
        box-shadow: 0 8px 24px rgba(250, 204, 21, 0.16);
    }

    .applicant-upload-preview-image {
        position: absolute;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        object-fit: cover;
        object-position: center;
    }

    .applicant-upload-preview-overlay {
        position: absolute;
        inset: 0;
        display: flex;
        align-items: center;
        justify-content: center;
        gap: 12px;
        background: rgba(0, 0, 0, 0.4);
        opacity: 0;
        transition: opacity 0.2s ease;
        backdrop-filter: blur(2px);
    }

    .applicant-upload-preview-container:hover .applicant-upload-preview-overlay {
        opacity: 1;
    }

    .applicant-preview-btn {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        gap: 6px;
        padding: 8px 16px;
        border-radius: 8px;
        border: none;
        font-size: 12px;
        font-weight: 700;
        cursor: pointer;
        transition: all 0.18s ease;
    }

    .applicant-preview-replace-btn {
        background: #facc15;
        color: #701315;
    }

    .applicant-preview-replace-btn:hover {
        background: #fde68a;
        transform: translateY(-1px);
        box-shadow: 0 4px 12px rgba(250, 204, 21, 0.3);
    }

    .applicant-preview-remove-btn {
        background: rgba(239, 68, 68, 0.8);
        color: #ffffff;
        border: 1px solid rgba(239, 68, 68, 0.6);
    }

    .applicant-preview-remove-btn:hover {
        background: rgba(220, 38, 38, 0.9);
        border-color: rgba(220, 38, 38, 0.8);
        transform: translateY(-1px);
        box-shadow: 0 4px 12px rgba(220, 38, 38, 0.3);
    }

    .applicant-preview-btn svg {
        width: 16px;
        height: 16px;
    }

    .applicant-upload-btn {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        gap: 8px;
        width: 100%;
        min-height: 46px;
        padding: 0 16px;
        border-radius: 14px;
        border: 1px solid rgba(112, 19, 27, 0.42);
        background: linear-gradient(135deg, #70131B, #8f2230);
        color: #facc15;
        font-size: 13px;
        font-weight: 900;
        cursor: pointer;
        transition: transform .18s ease, background .18s ease, color .18s ease, box-shadow .18s ease;
        box-shadow: 0 10px 22px rgba(112, 19, 27, 0.18);
    }

    .applicant-upload-btn:hover {
        background: linear-gradient(135deg, #facc15, #fde68a);
        color: #70131B;
        transform: translateY(-1px);
    }

    .applicant-upload-note {
        font-size: 11px;
        line-height: 1.45;
        color: #64748b;
    }

    html[data-theme="dark"] .applicant-lookup-details {
        background: rgba(15, 23, 42, 0.94);
        border-color: rgba(250, 204, 21, 0.14);
    }

    html[data-theme="dark"] .applicant-information-details {
        background: rgba(15, 23, 42, 0.94);
        border-color: rgba(250, 204, 21, 0.14);
    }

    html[data-theme="dark"] .applicant-lookup-card {
        background: linear-gradient(135deg, rgba(30, 41, 59, 0.8) 0%, rgba(15, 23, 42, 0.8) 100%);
        border-color: rgba(71, 85, 105, 0.4);
    }

    html[data-theme="dark"] .applicant-lookup-card:hover {
        border-color: rgba(250, 204, 21, 0.3);
        box-shadow: 0 4px 12px rgba(250, 204, 21, 0.08);
        background: linear-gradient(135deg, rgba(63, 57, 18, 0.8) 0%, rgba(41, 37, 12, 0.8) 100%);
    }

    html[data-theme="dark"] .applicant-lookup-label {
        color: #94a3b8;
    }

    html[data-theme="dark"] .applicant-lookup-value {
        color: #f1f5f9;
    }

    html[data-theme="dark"] .applicant-upload-note {
        color: #cbd5e1;
    }

    html[data-theme="dark"] .applicant-upload-preview-container {
        background: linear-gradient(135deg, rgba(30, 41, 59, 0.8) 0%, rgba(15, 23, 42, 0.8) 100%);
        border-color: rgba(250, 204, 21, 0.3);
        box-shadow: 0 8px 24px rgba(250, 204, 21, 0.08);
    }

    html[data-theme="dark"] .applicant-preview-replace-btn {
        background: #facc15;
        color: #701315;
    }

    html[data-theme="dark"] .applicant-preview-replace-btn:hover {
        background: #fde68a;
        box-shadow: 0 4px 12px rgba(250, 204, 21, 0.2);
    }

    html[data-theme="dark"] .applicant-preview-remove-btn {
        background: rgba(220, 38, 38, 0.7);
        border-color: rgba(220, 38, 38, 0.5);
        color: #f1f5f9;
    }

    html[data-theme="dark"] .applicant-preview-remove-btn:hover {
        background: rgba(220, 38, 38, 0.85);
        border-color: rgba(220, 38, 38, 0.7);
        box-shadow: 0 4px 12px rgba(220, 38, 38, 0.2);
    }

    @media (max-width: 640px) {
        .applicant-file-actions,
        .saved-review-grid {
            grid-template-columns: 1fr;
        }

        .applicant-ref-actions {
            grid-template-columns: 1fr;
        }
    }
    html[data-theme="dark"] .applicant-ref-col {
        background: linear-gradient(180deg, rgba(18,18,18,0.98), rgba(28,18,18,0.98));
        border-color: rgba(250, 204, 21, 0.14);
    }
    html[data-theme="dark"] .applicant-ref-title { color: #f8fafc; }
    html[data-theme="dark"] .applicant-ref-copy  { color: #94a3b8; }
    html[data-theme="dark"] .applicant-ref-input {
        background: rgba(30, 41, 59, 0.9);
        border-color: rgba(148, 163, 184, 0.24);
        color: #f1f5f9;
    }
    @media (max-width: 640px) {
        .applicant-ref-grid { grid-template-columns: 1fr; }
    }

    /* Medical Condition Section */
    .applicant-medical-condition-section {
        display: none;
        width: 100%;
        grid-template-columns: minmax(0, 1.35fr) minmax(280px, 0.65fr);
        gap: 14px;
    }

    .applicant-medical-condition-section.show {
        display: grid;
    }

    #applicantRefModal .applicant-modal-shell.has-lookup-result.is-final-review-workflow .applicant-medical-condition-section.show {
        grid-template-columns: minmax(0, 1.25fr) minmax(320px, 0.75fr);
        gap: 10px;
    }

    #applicantRefModal .applicant-modal-shell.has-lookup-result.is-final-review-workflow #applicantEncodeRemarksField {
        display: none !important;
    }

    .applicant-screening-panel {
        min-width: 0;
        padding: 18px 20px;
        background: linear-gradient(135deg, #f0f9ff 0%, #e0f2fe 100%);
        border: 1px solid #0284c7;
        border-radius: 12px;
    }

    #applicantRefModal .applicant-modal-shell.has-lookup-result.is-employee-lookup .applicant-medical-condition-section.show {
        grid-template-columns: minmax(0, 1.05fr) minmax(360px, 0.95fr);
        gap: 14px;
        align-items: stretch;
    }

    #applicantRefModal .applicant-modal-shell.has-lookup-result.is-employee-lookup .applicant-review-source-grid {
        grid-template-columns: repeat(2, minmax(0, 1fr));
        gap: 14px;
        margin: 12px 0 16px;
    }

    #applicantRefModal .applicant-modal-shell.has-lookup-result.is-employee-lookup .applicant-review-source-grid .applicant-lookup-card {
        min-height: 112px;
        align-items: flex-start;
        gap: 12px;
        padding: 18px;
        border: 0;
        border-radius: 10px;
        background: #ffffff;
        box-shadow: 0 10px 22px rgba(15, 23, 42, 0.05);
    }

    #applicantRefModal .applicant-modal-shell.has-lookup-result.is-employee-lookup .applicant-review-source-grid .applicant-lookup-icon {
        display: inline-flex;
        width: 52px;
        height: 52px;
        min-width: 52px;
        border-radius: 8px;
        background: linear-gradient(135deg, #8b0000, #b91c1c);
        color: #ffffff;
        font-size: 18px;
        box-shadow: 0 10px 18px rgba(127, 29, 29, 0.18);
    }

    #applicantRefModal .applicant-modal-shell.has-lookup-result.is-employee-lookup .applicant-review-source-grid .applicant-lookup-label {
        color: #111827;
        font-size: 12px;
        font-weight: 900;
        text-transform: none;
        letter-spacing: 0;
    }

    #applicantRefModal .applicant-modal-shell.has-lookup-result.is-employee-lookup .applicant-review-source-grid .applicant-lookup-value {
        color: #111827;
        font-size: 14px;
        font-weight: 900;
    }

    #applicantRefModal .applicant-modal-shell.has-lookup-result.is-employee-lookup .employee-physical-assessment-panel {
        align-self: stretch;
        height: auto;
        max-height: min(760px, calc(100vh - 190px));
        overflow-y: auto;
        padding: 0;
        border-color: rgba(112, 19, 27, 0.16);
        background:
            linear-gradient(180deg, rgba(255, 255, 255, 0.96), rgba(248, 250, 252, 0.92)),
            radial-gradient(circle at 12% 0%, rgba(250, 204, 21, 0.14), transparent 30%);
        box-shadow: 0 18px 40px rgba(15, 23, 42, 0.10);
        scrollbar-color: rgba(112, 19, 27, 0.36) rgba(226, 232, 240, 0.65);
        scrollbar-width: thin;
    }

    #applicantRefModal .applicant-modal-shell.has-lookup-result.is-employee-lookup .employee-physical-assessment-panel .applicant-screening-panel-title {
        position: sticky;
        top: 0;
        z-index: 2;
        margin: 0;
        padding: 16px 20px 14px;
        border-bottom: 1px solid rgba(112, 19, 27, 0.12);
        background: rgba(255, 255, 255, 0.78);
        backdrop-filter: blur(14px);
        color: #70131b;
        font-size: 14px;
        letter-spacing: 0.02em;
    }

    .employee-physical-exam-template {
        display: none;
    }

    #applicantRefModal .applicant-modal-shell.is-employee-lookup .employee-physical-exam-template {
        display: block;
        padding: 18px 20px 20px;
        color: #111827;
        font-size: 13px;
        font-weight: 650;
        line-height: 1.55;
    }

    #applicantRefModal .applicant-modal-shell.is-employee-lookup .employee-physical-exam-template h5 {
        display: inline-flex;
        align-items: center;
        min-height: 30px;
        margin: 0 0 16px;
        padding: 6px 11px;
        border: 1px solid rgba(112, 19, 27, 0.14);
        border-radius: 999px;
        background: rgba(112, 19, 27, 0.06);
        color: #70131b;
        font-size: 12px;
        font-weight: 900;
        letter-spacing: 0.06em;
    }

    #applicantRefModal .applicant-modal-shell.is-employee-lookup .employee-physical-exam-template p {
        margin: 0 0 10px;
    }

    #applicantRefModal .applicant-modal-shell.is-employee-lookup .employee-exam-section {
        display: grid;
        gap: 8px;
        margin-bottom: 12px;
        padding: 12px;
        border: 1px solid rgba(226, 232, 240, 0.95);
        border-radius: 10px;
        background: rgba(255, 255, 255, 0.88);
        box-shadow: inset 0 1px 0 rgba(255, 255, 255, 0.95);
    }

    #applicantRefModal .applicant-modal-shell.is-employee-lookup .employee-exam-section > strong {
        color: #70131b;
        font-size: 12px;
        font-weight: 900;
        letter-spacing: 0.03em;
        text-transform: uppercase;
    }

    #applicantRefModal .applicant-modal-shell.is-employee-lookup .employee-exam-options {
        display: flex;
        flex-wrap: wrap;
        align-items: center;
        gap: 8px 16px;
    }

    #applicantRefModal .applicant-modal-shell.is-employee-lookup .employee-exam-options label,
    #applicantRefModal .applicant-modal-shell.is-employee-lookup .employee-exam-line {
        display: inline-flex;
        align-items: center;
        gap: 7px;
        margin: 0;
    }

    #applicantRefModal .applicant-modal-shell.is-employee-lookup .employee-exam-options label {
        min-height: 34px;
        padding: 7px 11px;
        border: 1px solid rgba(203, 213, 225, 0.92);
        border-radius: 999px;
        background: #ffffff;
        color: #334155;
        font-size: 12px;
        font-weight: 800;
        cursor: pointer;
        transition: border-color 0.18s ease, background 0.18s ease, color 0.18s ease, box-shadow 0.18s ease;
    }

    #applicantRefModal .applicant-modal-shell.is-employee-lookup .employee-exam-options label:has(input[type="checkbox"]:checked) {
        border-color: rgba(112, 19, 27, 0.38);
        background: rgba(112, 19, 27, 0.08);
        color: #70131b;
        box-shadow: 0 6px 14px rgba(112, 19, 27, 0.08);
    }

    #applicantRefModal .applicant-modal-shell.is-employee-lookup .employee-exam-options label:has(input[type="radio"]:checked) {
        border-color: rgba(112, 19, 27, 0.38);
        background: rgba(112, 19, 27, 0.08);
        color: #70131b;
        box-shadow: 0 6px 14px rgba(112, 19, 27, 0.08);
    }

    #applicantRefModal .applicant-modal-shell.is-employee-lookup .employee-exam-options input[type="checkbox"],
    #applicantRefModal .applicant-modal-shell.is-employee-lookup .employee-exam-options input[type="radio"] {
        width: 16px;
        height: 16px;
        accent-color: #70131b;
    }

    #applicantRefModal .applicant-modal-shell.is-employee-lookup .employee-physical-exam-template input[type="text"],
    #applicantRefModal .applicant-modal-shell.is-employee-lookup .employee-physical-exam-template input[type="date"] {
        min-width: 110px;
        width: 150px;
        height: 32px;
        padding: 5px 9px;
        border: 1px solid rgba(148, 163, 184, 0.72);
        border-radius: 8px;
        background: #ffffff;
        color: #111827;
        font-size: 13px;
        font-weight: 700;
        outline: none;
        transition: border-color 0.18s ease, box-shadow 0.18s ease;
    }

    #applicantRefModal .applicant-modal-shell.is-employee-lookup .employee-physical-exam-template input[type="text"]:focus,
    #applicantRefModal .applicant-modal-shell.is-employee-lookup .employee-physical-exam-template input[type="date"]:focus {
        border-color: rgba(112, 19, 27, 0.55);
        box-shadow: 0 0 0 3px rgba(112, 19, 27, 0.10);
    }

    #applicantRefModal .applicant-modal-shell.is-employee-lookup .employee-exam-two-col {
        display: grid;
        grid-template-columns: 1fr;
        gap: 10px 12px;
        margin: 0 0 12px;
        padding: 12px;
        border: 1px solid rgba(226, 232, 240, 0.95);
        border-radius: 10px;
        background: rgba(255, 255, 255, 0.88);
    }

    #applicantRefModal .applicant-modal-shell.is-employee-lookup .employee-exam-two-col label {
        display: grid;
        grid-template-columns: 70px minmax(0, 1fr) auto;
        align-items: center;
        gap: 7px;
        margin: 0;
    }

    #applicantRefModal .applicant-modal-shell.is-employee-lookup .employee-exam-two-col input[type="text"] {
        width: 100%;
    }

    #applicantRefModal .applicant-modal-shell.is-employee-lookup .employee-physical-assessment-panel .applicant-screening-panel-copy,
    #applicantRefModal .applicant-modal-shell.is-employee-lookup .employee-physical-assessment-panel .applicant-vitals-grid,
    #applicantRefModal .applicant-modal-shell.is-employee-lookup .employee-physical-assessment-panel .bmi-gauge-card {
        display: none !important;
    }

    #applicantRefModal .applicant-modal-shell.is-employee-lookup .employee-exam-two-col {
        gap: 9px 10px;
    }

    #applicantRefModal .applicant-modal-shell.is-employee-lookup .employee-exam-two-col label {
        grid-template-columns: minmax(64px, auto) minmax(0, 128px) auto minmax(48px, auto);
        gap: 6px;
    }

    #applicantRefModal .applicant-modal-shell.is-employee-lookup .employee-exam-two-col input[type="text"],
    #applicantRefModal .applicant-modal-shell.is-employee-lookup .employee-exam-two-col input[type="date"],
    #applicantRefModal .applicant-modal-shell.is-employee-lookup .employee-exam-two-col select {
        width: 128px;
        min-width: 0;
    }

    #applicantRefModal .applicant-modal-shell.is-employee-lookup .employee-exam-two-col input[readonly] {
        background: #f1f5f9;
        color: #475569;
        cursor: not-allowed;
    }

    #applicantRefModal .applicant-modal-shell.is-employee-lookup .employee-exam-validation,
    #applicantRefModal .applicant-modal-shell.is-employee-lookup .employee-bmi-category {
        min-width: 48px;
        color: #64748b;
        font-size: 10px;
        font-weight: 850;
        white-space: nowrap;
    }

    #applicantRefModal .applicant-modal-shell.is-employee-lookup .employee-exam-validation.is-valid,
    #applicantRefModal .applicant-modal-shell.is-employee-lookup .employee-bmi-category.is-normal {
        color: #15803d;
    }

    #applicantRefModal .applicant-modal-shell.is-employee-lookup .employee-exam-validation.is-invalid,
    #applicantRefModal .applicant-modal-shell.is-employee-lookup .employee-bmi-category.is-underweight,
    #applicantRefModal .applicant-modal-shell.is-employee-lookup .employee-bmi-category.is-overweight,
    #applicantRefModal .applicant-modal-shell.is-employee-lookup .employee-bmi-category.is-obese {
        color: #b91c1c;
    }

    #applicantRefModal .applicant-modal-shell.is-employee-lookup .employee-exam-options > span {
        font-size: 12px;
        font-weight: 800;
    }

    #applicantRefModal .applicant-modal-shell.is-employee-lookup .employee-exam-options select,
    #applicantRefModal .applicant-modal-shell.is-employee-lookup .employee-exam-line select {
        width: 180px;
        min-width: 0;
        height: 32px;
        padding: 5px 9px;
        border: 1px solid rgba(148, 163, 184, 0.72);
        border-radius: 8px;
        background: #ffffff;
        color: #111827;
        font-size: 12px;
        font-weight: 700;
    }

    /* Keep the vital-sign rows on one shared alignment grid. */
    #applicantRefModal .applicant-modal-shell.is-employee-lookup .employee-exam-two-col label {
        grid-template-columns: 125px minmax(90px, 128px) auto 60px;
        font-size: 12px;
        line-height: 1.35;
    }

    #applicantRefModal .applicant-modal-shell.is-employee-lookup .employee-exam-two-col input[type="text"],
    #applicantRefModal .applicant-modal-shell.is-employee-lookup .employee-exam-two-col input[type="date"],
    #applicantRefModal .applicant-modal-shell.is-employee-lookup .employee-exam-two-col select {
        width: 100%;
        max-width: 100%;
        font-size: 12px;
    }

    #applicantRefModal .applicant-modal-shell.is-employee-lookup .employee-exam-validation,
    #applicantRefModal .applicant-modal-shell.is-employee-lookup .employee-bmi-category {
        min-width: 0;
        font-size: 12px !important;
        line-height: 1.2;
    }

    #applicantRefModal .applicant-modal-shell.is-employee-lookup .employee-exam-two-col label > span {
        font-size: 12px !important;
    }

    #applicantRefModal .applicant-modal-shell.is-employee-lookup .employee-physical-assessment-panel {
        box-shadow: 0 12px 28px rgba(15, 23, 42, 0.12);
    }

    #applicantRefModal .applicant-modal-shell.is-employee-lookup .employee-exam-section,
    #applicantRefModal .applicant-modal-shell.is-employee-lookup .employee-exam-two-col,
    #applicantRefModal .applicant-modal-shell.is-employee-lookup .bmi-gauge-card {
        box-shadow: 0 6px 16px rgba(15, 23, 42, 0.08), inset 0 1px 0 rgba(255, 255, 255, 0.72);
    }

    #applicantRefModal .applicant-modal-shell.has-lookup-result.is-encode-workflow .applicant-screening-panel {
        padding: 0;
        border-radius: 10px;
        border-color: transparent;
        background: transparent;
        box-shadow: none;
    }

    #applicantRefModal .applicant-modal-shell.has-lookup-result.is-final-review-workflow .applicant-screening-panel {
        padding: 18px;
        border-radius: 10px;
        border-color: #e5e7eb;
        background: #ffffff;
        box-shadow: 0 8px 20px rgba(15, 23, 42, 0.05);
    }

    #applicantRefModal .applicant-modal-shell.has-lookup-result.is-final-review-workflow .applicant-review-panel {
        background: linear-gradient(180deg, #f0f9ff, #ffffff);
        border-color: #bae6fd;
    }

    #applicantRefModal .applicant-modal-shell.has-lookup-result.is-final-review-workflow .applicant-review-source-grid {
        grid-template-columns: repeat(2, minmax(0, 1fr));
        gap: 14px;
        margin: 10px 0 16px;
    }

    #applicantRefModal .applicant-modal-shell.has-lookup-result.is-final-review-workflow .applicant-review-source-grid .applicant-lookup-card {
        min-height: 112px;
        padding: 18px;
        align-items: flex-start;
        background: #ffffff;
        border-color: #dbe3ef;
        border-radius: 10px;
        box-shadow: 0 6px 16px rgba(15, 23, 42, 0.04);
    }

    #applicantRefModal .applicant-modal-shell.has-lookup-result.is-final-review-workflow .applicant-review-source-grid .applicant-lookup-icon {
        display: inline-flex;
        width: 52px;
        height: 52px;
        min-width: 52px;
        border-radius: 8px;
        background: linear-gradient(135deg, #8b0000, #b91c1c);
        color: #ffffff;
        font-size: 18px;
        box-shadow: 0 10px 18px rgba(127, 29, 29, 0.18);
    }

    #applicantRefModal .applicant-modal-shell.has-lookup-result.is-final-review-workflow .applicant-review-source-grid .applicant-lookup-label {
        color: #111827;
        font-size: 14px;
        font-weight: 900;
        text-transform: none;
        letter-spacing: 0;
    }

    .applicant-review-result-badge {
        display: inline-flex;
        align-items: center;
        min-height: 26px;
        margin: 5px 0 9px;
        padding: 4px 12px;
        border-radius: 9px;
        border: 1px solid #bbf7d0;
        background: #ecfdf3;
        color: #15803d;
        font-size: 12px;
        font-weight: 900;
    }

    .applicant-review-result-badge.needs-review {
        border-color: #fde68a;
        background: #fff7ed;
        color: #d97706;
    }

    .applicant-review-result-detail {
        display: block;
        color: #475569;
        font-size: 13px;
        font-weight: 700;
    }

    .applicant-screening-panel-title {
        margin: 0 0 13px;
        color: #0f172a;
        font-size: 15px;
        font-weight: 800;
    }

    #applicantRefModal .applicant-modal-shell.has-lookup-result.is-encode-workflow .applicant-screening-panel-title {
        margin: 0;
        padding: 16px 18px 0;
        color: #70131b;
        font-size: 16px;
    }

    #applicantRefModal .applicant-modal-shell.has-lookup-result.is-final-review-workflow .applicant-screening-panel-title {
        margin-bottom: 10px;
        color: #111827;
        font-size: 15px;
    }

    .applicant-screening-panel-copy {
        margin: -7px 0 15px;
        color: #64748b;
        font-size: 12px;
        line-height: 1.5;
    }

    #applicantRefModal .applicant-modal-shell.has-lookup-result.is-encode-workflow .applicant-screening-panel-copy {
        margin: 0;
        padding: 4px 18px 0;
        font-size: 11px;
    }

    #applicantRefModal .applicant-modal-shell.has-lookup-result.is-final-review-workflow .applicant-screening-panel-copy {
        margin: -6px 0 10px;
        font-size: 11px;
    }

    .applicant-review-panel {
        display: flex;
        flex-direction: column;
        gap: 14px;
    }

    .applicant-vitals-grid {
        display: grid;
        grid-template-columns: repeat(2, minmax(0, 1fr));
        gap: 12px;
    }

    #applicantRefModal .applicant-modal-shell.has-lookup-result.is-final-review-workflow .applicant-vitals-grid {
        grid-template-columns: 1fr;
        gap: 0;
        border: 1px solid #e5e7eb;
        border-radius: 10px;
        overflow: hidden;
        background: #ffffff;
    }

    #applicantRefModal .applicant-modal-shell.has-lookup-result.is-final-review-workflow .applicant-vitals-grid .applicant-condition-field {
        display: grid;
        grid-template-columns: minmax(105px, 1fr) minmax(54px, 0.45fr) auto;
        align-items: center;
        gap: 10px;
        padding: 9px 12px;
        border-bottom: 1px solid #eef2f7;
    }

    #applicantRefModal .applicant-modal-shell.has-lookup-result.is-final-review-workflow .applicant-vitals-grid .applicant-condition-field::after {
        content: "✓ Normal";
        display: inline-flex;
        align-items: center;
        justify-content: center;
        min-height: 28px;
        padding: 4px 12px;
        border-radius: 999px;
        border: 1px solid #bbf7d0;
        background: #ecfdf3;
        color: #15803d;
        font-size: 12px;
        font-weight: 900;
        white-space: nowrap;
    }

    #applicantRefModal .applicant-modal-shell.has-lookup-result.is-final-review-workflow .applicant-vitals-grid .applicant-condition-field:nth-child(7)::after {
        content: "✓ Negative";
    }

    #applicantRefModal .applicant-modal-shell.has-lookup-result.is-final-review-workflow .applicant-vitals-grid .applicant-condition-field:nth-child(8),
    #applicantRefModal .applicant-modal-shell.has-lookup-result.is-final-review-workflow .applicant-vitals-grid .applicant-condition-field:nth-child(9) {
        display: none !important;
    }

    #applicantRefModal .applicant-modal-shell.has-lookup-result.is-final-review-workflow .applicant-vitals-grid .applicant-condition-field:nth-child(7) {
        border-bottom: 0;
    }

    #applicantRefModal .applicant-modal-shell.has-lookup-result.is-final-review-workflow .applicant-vitals-grid .applicant-condition-field label {
        color: #475569;
        font-size: 12px;
        font-weight: 800;
    }

    #applicantRefModal .applicant-modal-shell.has-lookup-result.is-final-review-workflow .applicant-vitals-grid .applicant-condition-input,
    #applicantRefModal .applicant-modal-shell.has-lookup-result.is-final-review-workflow .applicant-vitals-grid .applicant-condition-textarea {
        min-height: 30px;
        padding: 4px 0;
        border: 0;
        background: transparent;
        color: #111827;
        font-size: 13px;
        font-weight: 900;
        box-shadow: none;
    }

    #applicantRefModal .applicant-modal-shell.has-lookup-result.is-final-review-workflow .applicant-vitals-grid .applicant-condition-input:not([readonly]),
    #applicantRefModal .applicant-modal-shell.has-lookup-result.is-final-review-workflow .applicant-vitals-grid .applicant-condition-textarea:not([readonly]) {
        min-height: 40px;
        padding: 8px 10px;
        border: 1px solid #facc15;
        border-radius: 8px;
        background: #fffbea;
    }

    #applicantRefModal .applicant-modal-shell.has-lookup-result.is-final-review-workflow .applicant-vitals-grid .applicant-condition-field::after {
        content: none !important;
        display: none !important;
    }

    #applicantRefModal .applicant-modal-shell.has-lookup-result.is-final-review-workflow .applicant-vitals-grid .applicant-condition-field small {
        grid-column: 2 / -1;
    }

    #applicantRefModal .applicant-modal-shell.has-lookup-result.is-final-review-workflow .applicant-vitals-grid .vital-status {
        align-self: center;
        justify-self: end;
        min-width: 76px;
        font-size: 11px;
        text-align: center;
        white-space: nowrap;
    }

    #applicantRefModal .applicant-modal-shell.has-lookup-result.is-final-review-workflow .applicant-vitals-grid .voice-field-inline-mic {
        display: none !important;
    }

    #applicantRefModal .applicant-modal-shell.has-lookup-result.is-final-review-workflow .applicant-screening-panel:not(.is-readonly-review) .applicant-vitals-grid .voice-field-inline-mic {
        display: inline-flex !important;
    }

    #applicantRefModal .applicant-modal-shell.has-lookup-result.is-final-review-workflow .applicant-vitals-grid .applicant-findings-options {
        display: flex;
        gap: 6px;
        justify-content: flex-end;
    }

    #applicantRefModal .applicant-modal-shell.has-lookup-result.is-final-review-workflow .applicant-vitals-grid .applicant-findings-option span {
        min-height: 30px;
        padding: 4px 12px;
        border-radius: 999px;
        font-size: 11px;
    }

    #applicantRefModal .applicant-modal-shell.has-lookup-result.is-encode-workflow .applicant-vitals-grid {
        position: relative;
        grid-template-columns: repeat(2, minmax(0, 1fr)) 340px;
        align-items: start;
        gap: 14px 22px;
        padding-top: 14px;
    }

    #applicantRefModal .applicant-modal-shell.has-lookup-result.is-encode-workflow .applicant-vitals-grid::before {
        content: "";
        position: absolute;
        inset: 0 362px 0 0;
        border: 1px solid #e5e7eb;
        border-radius: 10px;
        background: #ffffff;
        box-shadow: 0 8px 20px rgba(15, 23, 42, 0.05);
        z-index: 0;
    }

    #applicantRefModal .applicant-modal-shell.has-lookup-result.is-encode-workflow .applicant-vitals-grid .applicant-condition-field {
        gap: 5px;
    }

    #applicantRefModal .applicant-modal-shell.has-lookup-result.is-encode-workflow .applicant-vitals-grid .applicant-condition-field {
        position: relative;
        z-index: 1;
    }

    #applicantRefModal .applicant-modal-shell.has-lookup-result.is-encode-workflow .applicant-vitals-grid .applicant-condition-field:nth-child(-n+6) {
        padding: 0 18px;
    }

    #applicantRefModal .applicant-modal-shell.has-lookup-result.is-encode-workflow .applicant-vitals-grid .applicant-condition-field:nth-child(1) {
        grid-column: 1;
        grid-row: 1;
    }

    #applicantRefModal .applicant-modal-shell.has-lookup-result.is-encode-workflow .applicant-vitals-grid .applicant-condition-field:nth-child(2) {
        grid-column: 2;
        grid-row: 1;
    }

    #applicantRefModal .applicant-modal-shell.has-lookup-result.is-encode-workflow .applicant-vitals-grid .applicant-condition-field:nth-child(3) {
        grid-column: 1;
        grid-row: 2;
    }

    #applicantRefModal .applicant-modal-shell.has-lookup-result.is-encode-workflow .applicant-vitals-grid .applicant-condition-field:nth-child(4) {
        grid-column: 2;
        grid-row: 2;
    }

    #applicantRefModal .applicant-modal-shell.has-lookup-result.is-encode-workflow .applicant-vitals-grid .applicant-condition-field:nth-child(5) {
        grid-column: 1;
        grid-row: 3;
    }

    #applicantRefModal .applicant-modal-shell.has-lookup-result.is-encode-workflow .applicant-vitals-grid .applicant-condition-field:nth-child(6) {
        grid-column: 2;
        grid-row: 3;
    }

    #applicantRefModal .applicant-modal-shell.has-lookup-result.is-encode-workflow .applicant-vitals-grid .applicant-condition-field:nth-child(7) {
        grid-column: 3;
        grid-row: 1;
        padding: 16px 18px 10px;
        transform: translateY(-14px);
        border: 1px solid #e5e7eb;
        border-bottom: 0;
        border-radius: 10px 10px 0 0;
        background: #ffffff;
        box-shadow: 0 8px 20px rgba(15, 23, 42, 0.05);
    }

    #applicantRefModal .applicant-modal-shell.has-lookup-result.is-encode-workflow .applicant-vitals-grid .applicant-condition-field:nth-child(8) {
        grid-column: 3;
        grid-row: 2;
        padding: 0 18px 10px;
        transform: translateY(-14px);
        border-inline: 1px solid #e5e7eb;
        background: #ffffff;
    }

    #applicantRefModal .applicant-modal-shell.has-lookup-result.is-encode-workflow .applicant-vitals-grid .applicant-condition-field:nth-child(9) {
        grid-column: 3;
        grid-row: 3 / span 3;
        padding: 8px 18px 16px;
        transform: translateY(-14px);
        border: 1px solid #e5e7eb;
        border-top: 0;
        border-radius: 0 0 10px 10px;
        background: #ffffff;
        box-shadow: 0 8px 20px rgba(15, 23, 42, 0.05);
    }

    #applicantRefModal .applicant-modal-shell.has-lookup-result.is-encode-workflow .applicant-vitals-grid .applicant-condition-field.is-full {
        grid-column: auto;
    }

    #applicantRefModal .applicant-modal-shell.has-lookup-result.is-encode-workflow .applicant-vitals-grid:has(#applicantCovidDateField[style*="display:none"]) .applicant-condition-field:nth-child(9),
    #applicantRefModal .applicant-modal-shell.has-lookup-result.is-encode-workflow .applicant-vitals-grid:has(#applicantCovidDateField[style*="display: none"]) .applicant-condition-field:nth-child(9) {
        grid-row: 2 / span 4;
    }

    @media (max-width: 980px) {
        #applicantRefModal .applicant-modal-shell.has-lookup-result.is-encode-workflow .applicant-lookup-details {
            padding-left: 14px;
        }

        #applicantRefModal .applicant-modal-shell.has-lookup-result.is-encode-workflow .applicant-lookup-grid {
            grid-template-columns: repeat(2, minmax(0, 1fr));
        }

        #applicantRefModal .applicant-modal-shell.has-lookup-result.is-encode-workflow .applicant-vitals-grid {
            grid-template-columns: repeat(2, minmax(0, 1fr));
        }

        #applicantRefModal .applicant-modal-shell.has-lookup-result.is-encode-workflow .applicant-vitals-grid::before {
            display: none;
        }

        #applicantRefModal .applicant-modal-shell.has-lookup-result.is-encode-workflow .applicant-vitals-grid .applicant-condition-field:nth-child(-n+6) {
            padding: 0;
        }

        #applicantRefModal .applicant-modal-shell.has-lookup-result.is-encode-workflow .applicant-vitals-grid .applicant-condition-field:nth-child(7),
        #applicantRefModal .applicant-modal-shell.has-lookup-result.is-encode-workflow .applicant-vitals-grid .applicant-condition-field:nth-child(8),
        #applicantRefModal .applicant-modal-shell.has-lookup-result.is-encode-workflow .applicant-vitals-grid .applicant-condition-field:nth-child(9) {
            grid-column: 1 / -1;
            grid-row: auto;
            transform: none;
        }

        #applicantRefModal .applicant-modal-shell.has-lookup-result.is-encode-workflow .applicant-file-actions {
            grid-template-columns: 1fr;
        }
    }

    @media (max-width: 640px) {
        #applicantRefModal .applicant-modal-shell.has-lookup-result.is-encode-workflow .applicant-ref-result {
            padding-left: 78px;
        }

        #applicantRefModal .applicant-modal-shell.has-lookup-result.is-encode-workflow .applicant-ref-result::after {
            width: 42px;
            height: 42px;
        }

        #applicantRefModal .applicant-modal-shell.has-lookup-result.is-encode-workflow .applicant-lookup-grid,
        #applicantRefModal .applicant-modal-shell.has-lookup-result.is-encode-workflow .applicant-vitals-grid,
        #applicantRefModal .applicant-modal-shell.has-lookup-result.is-encode-workflow .applicant-ref-actions {
            grid-template-columns: 1fr;
        }
    }

    .applicant-vitals-grid .applicant-condition-field.is-full {
        grid-column: 1 / -1;
    }

    .applicant-condition-header {
        display: flex;
        align-items: center;
    }

    .applicant-findings-review {
        display: grid;
        gap: 8px;
        padding-bottom: 13px;
        border-bottom: 1px solid rgba(3, 105, 161, 0.18);
    }

    .applicant-findings-label {
        color: #334155;
        font-size: 13px;
        font-weight: 700;
    }

    .applicant-findings-options {
        display: grid;
        grid-template-columns: repeat(2, minmax(0, 1fr));
        gap: 8px;
    }

    .applicant-findings-option {
        position: relative;
        cursor: pointer;
    }

    .applicant-findings-option input {
        position: absolute;
        opacity: 0;
        pointer-events: none;
    }

    .applicant-findings-option span {
        position: relative;
        overflow: hidden;
        min-height: 42px;
        display: flex;
        align-items: center;
        justify-content: center;
        padding: 9px 12px;
        border: 1px solid rgba(3, 105, 161, 0.28);
        border-radius: 8px;
        background: #ffffff;
        color: #334155;
        font-size: 13px;
        font-weight: 750;
        text-align: center;
        isolation: isolate;
        transition: background-color .18s ease, border-color .18s ease, color .18s ease, transform .18s ease, box-shadow .18s ease;
    }

    .applicant-findings-option span::after {
        content: "";
        position: absolute;
        inset: 0;
        background:
            linear-gradient(120deg,
                rgba(255, 248, 196, 0) 0%,
                rgba(255, 239, 181, 0.14) 22%,
                rgba(255, 239, 181, 0.52) 48%,
                rgba(255, 239, 181, 0.14) 72%,
                rgba(255, 248, 196, 0) 100%);
        transform: translateX(-135%);
        transition: transform 1.2s ease;
        pointer-events: none;
    }

    .applicant-findings-option input:checked + span {
        border-color: #70131b;
        background: #70131b;
        color: #facc15;
    }

    .applicant-findings-option:hover span,
    .applicant-findings-option input:focus-visible + span {
        border-color: #facc15;
        background: #facc15;
        color: #70131b;
        transform: translateY(-1px);
        box-shadow: 0 10px 18px rgba(202, 138, 4, .18);
    }

    .applicant-findings-option:hover span::after,
    .applicant-findings-option input:focus-visible + span::after {
        transform: translateX(135%);
    }

    .applicant-condition-toggle {
        display: flex;
        align-items: center;
        gap: 10px;
        cursor: pointer;
        user-select: none;
    }

    .applicant-condition-checkbox {
        width: 18px;
        height: 18px;
        cursor: pointer;
        accent-color: #0369a1;
    }

    .applicant-condition-toggle-text {
        font-weight: 600;
        color: #0369a1;
        font-size: 14px;
    }

    .applicant-pending-reasons {
        display: grid;
        gap: 8px;
        padding: 2px 0 4px;
    }

    .applicant-pending-reason-option {
        display: flex;
        align-items: center;
        gap: 10px;
        padding: 10px 12px;
        border: 1px solid rgba(3, 105, 161, 0.24);
        border-radius: 8px;
        background: #ffffff;
        color: #334155;
        font-size: 13px;
        font-weight: 700;
        cursor: pointer;
    }

    .applicant-pending-reason-option input {
        width: 18px;
        height: 18px;
        margin: 0;
        accent-color: #70131b;
        cursor: pointer;
    }

    .applicant-pending-reason-option:has(input:checked) {
        border-color: rgba(112, 19, 27, 0.5);
        background: #fff7ed;
        color: #70131b;
    }

    .applicant-condition-fields {
        display: flex;
        flex-direction: column;
        gap: 12px;
        width: 100%;
    }

    .applicant-condition-field {
        display: flex;
        flex-direction: column;
        gap: 6px;
        width: 100%;
    }

    .applicant-condition-field label {
        font-size: 13px;
        font-weight: 600;
        color: #334155;
    }

    .applicant-condition-input,
    .applicant-condition-textarea {
        width: 100%;
        box-sizing: border-box;
        padding: 10px 12px;
        border: 1px solid #0284c7;
        border-radius: 8px;
        font-size: 14px;
        font-family: inherit;
        background: #ffffff;
        color: #1e293b;
        transition: all 0.2s ease;
    }

    .applicant-condition-input:focus,
    .applicant-condition-textarea:focus {
        outline: none;
        border-color: #0369a1;
        box-shadow: 0 0 0 3px rgba(3, 105, 161, 0.1);
    }

    .vital-status {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        align-self: flex-start;
        min-height: 28px;
        max-width: 100%;
        padding: 4px 10px;
        border-radius: 999px;
        border: 1px solid #cbd5e1;
        background: #f8fafc;
        color: #475569;
        font-size: 11px;
        font-weight: 800;
        line-height: 1.25;
        white-space: normal;
    }

    .vital-status.is-normal {
        border-color: #bbf7d0;
        background: #ecfdf3;
        color: #15803d;
    }

    .vital-status.is-warning {
        border-color: #fde68a;
        background: #fffbeb;
        color: #92400e;
    }

    .vital-status.is-danger {
        border-color: #fecaca;
        background: #fef2f2;
        color: #b91c1c;
    }

    .vital-status.is-muted {
        border-color: #e2e8f0;
        background: #f8fafc;
        color: #64748b;
    }

    .bmi-gauge-card {
        margin-top: 14px;
        padding: 16px;
        border: 1px solid #e2e8f0;
        border-radius: 10px;
        background: #ffffff;
        box-shadow: 0 14px 30px rgba(15, 23, 42, 0.08);
    }

    .bmi-gauge-summary {
        display: flex;
        align-items: baseline;
        justify-content: center;
        gap: 10px;
        margin-bottom: 10px;
        text-align: center;
        flex-wrap: wrap;
    }

    .bmi-gauge-summary strong {
        color: #020617;
        font-size: 20px;
        font-weight: 900;
    }

    .bmi-gauge-summary span {
        color: #64748b;
        font-size: 15px;
        font-weight: 800;
    }

    .bmi-gauge-card.is-underweight .bmi-gauge-summary span,
    .bmi-gauge-card.is-overweight .bmi-gauge-summary span {
        color: #ca8a04;
    }

    .bmi-gauge-card.is-normal .bmi-gauge-summary span {
        color: #15803d;
    }

    .bmi-gauge-card.is-obese .bmi-gauge-summary span {
        color: #b91c1c;
    }

    .bmi-gauge-layout {
        display: grid;
        grid-template-columns: minmax(240px, 360px) minmax(240px, 1fr);
        align-items: center;
        justify-content: center;
        gap: 20px;
        max-width: 820px;
        margin: 0 auto;
    }

    .bmi-gauge-meter {
        position: relative;
        width: min(360px, 100%);
        aspect-ratio: 420 / 245;
        margin: 0 auto;
        overflow: visible;
    }

    .bmi-gauge-svg {
        position: absolute;
        inset: 0;
        width: 100%;
        height: 100%;
        overflow: visible;
    }

    .bmi-gauge-segment {
        stroke: none;
    }

    .bmi-gauge-segment.is-low {
        fill: #dc2626;
    }

    .bmi-gauge-segment.is-under {
        fill: #facc15;
    }

    .bmi-gauge-segment.is-normal {
        fill: #059669;
    }

    .bmi-gauge-segment.is-over {
        fill: #fde047;
    }

    .bmi-gauge-segment.is-obese-start {
        fill: #d88487;
    }

    .bmi-gauge-segment.is-obese {
        fill: #b91c1c;
    }

    .bmi-gauge-needle {
        position: absolute;
        left: 50%;
        bottom: 16.3%;
        width: 4px;
        height: 43%;
        border-radius: 999px;
        background: #525252;
        transform: translateX(-50%) rotate(-90deg);
        transform-origin: 50% 100%;
        transition: transform 520ms cubic-bezier(.22, .9, .28, 1.12);
        z-index: 3;
    }

    .bmi-gauge-needle::before {
        content: "";
        position: absolute;
        top: -10px;
        left: 50%;
        width: 0;
        height: 0;
        border-left: 9px solid transparent;
        border-right: 9px solid transparent;
        border-bottom: 18px solid #111827;
        transform: translateX(-50%);
    }

    .bmi-gauge-pivot {
        position: absolute;
        left: 50%;
        bottom: calc(16.3% - 6.5px);
        width: 13px;
        height: 13px;
        border-radius: 999px;
        background: #737373;
        transform: translateX(-50%);
        z-index: 6;
    }

    .bmi-gauge-value {
        position: absolute;
        left: 50%;
        bottom: 25%;
        transform: translateX(-50%);
        color: #020617;
        font-size: clamp(22px, 6vw, 38px);
        font-weight: 900;
        line-height: 1;
        white-space: nowrap;
        z-index: 5;
    }

    .bmi-gauge-svg-label {
        color: #0f172a;
        fill: currentColor;
        font-size: 14px;
        font-weight: 800;
        pointer-events: none;
        text-anchor: middle;
    }

    .bmi-gauge-details {
        margin: 0;
        padding-left: 20px;
        color: #0f172a;
        font-size: 13px;
        line-height: 1.45;
    }

    .bmi-gauge-details li + li {
        margin-top: 4px;
    }

    #applicantRefModal .applicant-modal-shell.has-lookup-result.is-final-review-workflow .bmi-gauge-layout {
        grid-template-columns: 1fr;
        gap: 10px;
    }

    #applicantRefModal .applicant-modal-shell.has-lookup-result.is-final-review-workflow .bmi-gauge-card {
        margin-top: 12px;
        padding: 14px;
        box-shadow: none;
    }

    #applicantRefModal .applicant-modal-shell.has-lookup-result.is-final-review-workflow .bmi-gauge-meter {
        width: min(300px, 100%);
    }

    #applicantRefModal .applicant-modal-shell.has-lookup-result.is-final-review-workflow .bmi-gauge-summary {
        margin-bottom: 6px;
    }

    #applicantRefModal .applicant-modal-shell.has-lookup-result.is-final-review-workflow .bmi-gauge-summary strong {
        font-size: 19px;
    }

    #applicantRefModal .applicant-modal-shell.has-lookup-result.is-final-review-workflow .bmi-gauge-summary span {
        font-size: 13px;
    }

    #applicantRefModal .applicant-modal-shell.has-lookup-result.is-final-review-workflow .bmi-gauge-svg-label {
        font-size: 13px;
    }

    #applicantRefModal .applicant-modal-shell.has-lookup-result.is-final-review-workflow .bmi-gauge-value {
        font-size: clamp(22px, 5vw, 30px);
    }

    @media (max-width: 760px) {
        .bmi-gauge-layout {
            grid-template-columns: 1fr;
        }

        .bmi-gauge-card {
            padding: 12px;
        }

        .bmi-gauge-summary strong {
            font-size: 18px;
        }

        .bmi-gauge-details {
            font-size: 12px;
        }
    }

    .applicant-condition-textarea {
        resize: vertical;
        min-height: 120px;
    }

    .applicant-condition-textarea[readonly] {
        background: #f8fafc;
        color: #334155;
        cursor: default;
    }

    #applicantRefModal .has-lookup-result .applicant-condition-input {
        min-height: 48px;
        padding: 12px 14px;
    }

    #applicantRefModal .applicant-modal-shell.has-lookup-result.is-encode-workflow .applicant-condition-field label {
        color: #111827;
        font-size: 12px;
        font-weight: 800;
    }

    #applicantRefModal .applicant-modal-shell.has-lookup-result.is-encode-workflow .applicant-condition-input,
    #applicantRefModal .applicant-modal-shell.has-lookup-result.is-encode-workflow .applicant-condition-textarea {
        min-height: 42px;
        padding: 9px 13px;
        border-color: #dbe3ef;
        border-radius: 8px;
        font-size: 14px;
    }

    #applicantRefModal .applicant-modal-shell.has-lookup-result.is-encode-workflow .applicant-condition-textarea {
        min-height: 116px;
    }

    #applicantRefModal .applicant-modal-shell.has-lookup-result.is-encode-workflow .applicant-findings-options {
        gap: 8px;
    }

    #applicantRefModal .applicant-modal-shell.has-lookup-result.is-encode-workflow .applicant-findings-option span {
        min-height: 42px;
        border-color: #dbe3ef;
        font-size: 13px;
    }

    .applicant-documents-trigger {
        display: none;
        width: 100%;
        min-height: 50px;
        align-items: center;
        justify-content: center;
        gap: 9px;
        padding: 12px 16px;
        border: 1px solid #70131b;
        border-radius: 10px;
        background: #70131b;
        color: #ffffff;
        font: inherit;
        font-size: 14px;
        font-weight: 800;
        cursor: pointer;
        transition: background-color 0.2s ease, color 0.2s ease, transform 0.2s ease;
    }

    .applicant-documents-trigger.is-visible {
        display: inline-flex;
    }

    .applicant-documents-trigger:hover {
        background: #facc15;
        color: #70131b;
        transform: translateY(-1px);
    }

    .applicant-documents-trigger svg {
        width: 20px;
        height: 20px;
    }

    .applicant-documents-count {
        min-width: 24px;
        padding: 3px 7px;
        border-radius: 999px;
        background: rgba(112, 19, 27, 0.12);
        color: inherit;
        font-size: 11px;
    }

    .applicant-documents-modal {
        width: min(1180px, 96vw);
    }

    .applicant-documents-workspace {
        display: grid;
        grid-template-columns: 250px minmax(0, 1fr);
        gap: 16px;
        height: min(74vh, 760px);
        min-height: 520px;
    }

    .applicant-documents-sidebar {
        min-width: 0;
        padding-right: 12px;
        border-right: 1px solid #e2e8f0;
        overflow-y: auto;
    }

    .applicant-documents-grid {
        display: grid;
        grid-template-columns: 1fr;
        gap: 9px;
    }

    .applicant-document-card {
        display: grid;
        grid-template-columns: 36px minmax(0, 1fr);
        align-items: center;
        gap: 9px;
        min-width: 0;
        padding: 10px;
        border: 1px solid #e2e8f0;
        border-radius: 9px;
        background: #ffffff;
        transition: border-color .18s ease, background-color .18s ease, box-shadow .18s ease;
    }

    .applicant-document-card.is-active {
        border-color: #800000;
        background: #fff7ed;
        box-shadow: 0 0 0 2px rgba(128, 0, 0, 0.08);
    }

    .applicant-document-card.is-missing {
        border-color: #fecaca;
        background: #fff1f2;
    }

    .applicant-document-icon {
        width: 36px;
        height: 36px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        border-radius: 8px;
        background: #fff7ed;
        color: #800000;
        font-size: 9px;
        font-weight: 900;
    }

    .applicant-document-icon svg {
        width: 18px;
        height: 18px;
    }

    .applicant-document-copy {
        min-width: 0;
        flex: 1;
    }

    .applicant-document-copy strong,
    .applicant-document-copy span {
        display: block;
    }

    .applicant-document-copy strong {
        color: #111827;
        font-size: 12px;
        line-height: 1.35;
    }

    .applicant-document-copy span {
        margin-top: 2px;
        color: #64748b;
        font-size: 10px;
    }

    .applicant-document-actions {
        display: flex;
        grid-column: 1 / -1;
        flex: 0 0 auto;
        align-items: center;
        gap: 7px;
    }

    .applicant-document-view {
        flex: 1;
        padding: 7px 8px;
        border: 1px solid #800000;
        border-radius: 7px;
        background: #ffffff;
        color: #800000;
        font-size: 10px;
        font-weight: 800;
        text-decoration: none;
        text-align: center;
        transition: background-color 0.2s ease, color 0.2s ease;
        cursor: pointer;
    }

    .applicant-document-view:hover {
        background: #800000;
        color: #facc15;
    }

    .applicant-document-view.is-disabled,
    .applicant-document-view.is-disabled:hover {
        border-color: #fecaca;
        background: #fee2e2;
        color: #991b1b;
        cursor: default;
    }

    .applicant-documents-empty {
        grid-column: 1 / -1;
        padding: 24px;
        border: 1px dashed #cbd5e1;
        border-radius: 12px;
        color: #64748b;
        text-align: center;
        font-size: 14px;
        font-weight: 700;
    }

    .applicant-document-preview-panel {
        display: flex;
        min-width: 0;
        min-height: 0;
        flex-direction: column;
        border: 1px solid #e2e8f0;
        border-radius: 12px;
        overflow: hidden;
        background: #f8fafc;
    }

    .applicant-document-preview-panel.is-visible {
        display: flex;
    }

    .applicant-document-preview-head {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 12px;
        padding: 12px 14px;
        border-bottom: 1px solid #e2e8f0;
        background: #ffffff;
    }

    .applicant-document-preview-head strong {
        color: #111827;
        font-size: 14px;
    }

    .applicant-document-preview-close {
        min-height: 34px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        padding: 7px 11px;
        border: 1px solid #cbd5e1;
        border-radius: 8px;
        background: #ffffff;
        color: #800000;
        font-size: 11px;
        font-weight: 800;
        text-decoration: none;
        cursor: pointer;
    }

    .applicant-document-preview-frame,
    .applicant-document-preview-image {
        width: 100%;
        height: 100%;
        min-height: 0;
        flex: 1;
        border: 0;
        background: #ffffff;
    }

    .applicant-document-preview-image {
        display: none;
        object-fit: contain;
        padding: 14px;
    }

    .applicant-document-preview-empty {
        flex: 1;
        display: flex;
        align-items: center;
        justify-content: center;
        padding: 30px;
        color: #64748b;
        font-size: 13px;
        font-weight: 700;
        text-align: center;
    }

    html[data-theme="dark"] .applicant-document-card {
        background: #111827;
        border-color: rgba(250, 204, 21, 0.16);
    }

    html[data-theme="dark"] .applicant-document-copy strong {
        color: #f8fafc;
    }

    html[data-theme="dark"] .applicant-document-copy span,
    html[data-theme="dark"] .applicant-documents-empty {
        color: #cbd5e1;
    }

    html[data-theme="dark"] .applicant-document-icon,
    html[data-theme="dark"] .applicant-document-view {
        background: #1f2937;
        border-color: rgba(250, 204, 21, 0.24);
        color: #fde68a;
    }

    html[data-theme="dark"] .applicant-document-preview-panel,
    html[data-theme="dark"] .applicant-document-preview-head {
        background: #111827;
        border-color: rgba(250, 204, 21, 0.16);
    }

    html[data-theme="dark"] .applicant-document-preview-head strong {
        color: #f8fafc;
    }

    @media (max-width: 760px) {
        #applicantRefModal .applicant-modal-shell,
        #applicantRefModal .applicant-modal-shell.has-lookup-result {
            width: 100%;
        }

        .applicant-medical-condition-section.show {
            grid-template-columns: 1fr;
        }

        #applicantRefModal .applicant-modal-shell.has-lookup-result.is-employee-lookup .employee-physical-assessment-panel {
            height: auto;
            max-height: min(70vh, 560px);
        }

        .applicant-file-actions {
            grid-template-columns: 1fr;
        }

        .applicant-documents-workspace {
            grid-template-columns: 1fr;
            height: auto;
            min-height: 0;
        }

        .applicant-documents-sidebar {
            max-height: 230px;
            padding-right: 0;
            padding-bottom: 12px;
            border-right: 0;
            border-bottom: 1px solid #e2e8f0;
        }

        .applicant-document-card {
            align-items: center;
        }

        .applicant-document-preview-panel {
            min-height: 520px;
        }
    }

    html[data-theme="dark"] .applicant-screening-panel {
        background: linear-gradient(135deg, rgba(3, 105, 161, 0.15) 0%, rgba(2, 132, 199, 0.15) 100%);
        border-color: rgba(3, 105, 161, 0.3);
    }

    html[data-theme="dark"] .applicant-screening-panel-title {
        color: #f8fafc;
    }

    html[data-theme="dark"] .applicant-screening-panel-copy {
        color: #cbd5e1;
    }

    html[data-theme="dark"] .bmi-gauge-card {
        border-color: rgba(148, 163, 184, 0.26);
        background: rgba(15, 23, 42, 0.82);
        box-shadow: none;
    }

    html[data-theme="dark"] .bmi-gauge-summary strong,
    html[data-theme="dark"] .bmi-gauge-value,
    html[data-theme="dark"] .bmi-gauge-svg-label,
    html[data-theme="dark"] .bmi-gauge-details {
        color: #f8fafc;
    }

    html[data-theme="dark"] .bmi-gauge-summary span {
        color: #cbd5e1;
    }

    html[data-theme="dark"] .applicant-documents-sidebar {
        border-color: rgba(250, 204, 21, 0.16);
    }

    html[data-theme="dark"] .applicant-document-card.is-active {
        border-color: #facc15;
        background: rgba(250, 204, 21, 0.1);
    }

    .clinic-success-overlay {
        position: fixed;
        z-index: 9999;
        inset: 0;
        display: none;
        align-items: center;
        justify-content: center;
        padding: 20px;
        background: rgba(15, 23, 42, .58);
        backdrop-filter: blur(5px);
    }

    .clinic-success-overlay.is-open {
        display: flex;
    }

    .clinic-success-card {
        position: relative;
        width: min(420px, calc(100vw - 28px));
        padding: 38px 32px 28px;
        border: 1px solid rgba(112, 19, 27, .10);
        border-radius: 16px;
        background: #ffffff;
        box-shadow: 0 24px 54px rgba(15, 23, 42, .3);
        text-align: center;
        animation: clinicSuccessPop .38s cubic-bezier(.2, .9, .25, 1.2);
        overflow: hidden;
    }

    .clinic-success-check {
        position: relative;
        width: 88px;
        height: 88px;
        display: grid;
        place-items: center;
        margin: 6px auto 22px;
        border-radius: 999px;
        background: radial-gradient(circle at 35% 24%, #9f2435 0%, #70131b 62%, #4f1017 100%);
        color: #ffffff;
        box-shadow: 0 12px 26px rgba(112, 19, 27, .24);
        animation: clinicCheckPop .48s .12s cubic-bezier(.2, .9, .25, 1.25) both;
    }

    .clinic-success-ring,
    .clinic-success-check::before,
    .clinic-success-check::after {
        content: "";
        position: absolute;
        inset: -22px;
        border-radius: inherit;
        border: 2px solid rgba(148, 163, 184, .42);
        pointer-events: none;
        animation: clinicSuccessPulse 2.15s ease-out infinite;
    }

    .clinic-success-ring {
        inset: -38px;
        animation-delay: .34s;
        border-color: rgba(148, 163, 184, .32);
    }

    .clinic-success-check::after {
        inset: -54px;
        animation-delay: .68s;
        border-color: rgba(148, 163, 184, .22);
    }

    .clinic-success-check svg {
        width: 44px;
        height: 44px;
        stroke-width: 3.2;
        position: relative;
        z-index: 1;
        animation: clinicCheckIconIn .42s .25s cubic-bezier(.2, .9, .25, 1.35) both;
    }

    .clinic-success-card strong {
        display: block;
        color: #70131b;
        font-size: clamp(22px, 3vw, 30px);
        font-weight: 900;
        line-height: 1.15;
        margin-bottom: 10px;
    }

    .clinic-success-card p {
        margin: 0 auto 24px;
        max-width: 310px;
        color: #4b5563;
        font-size: 14px;
        font-weight: 700;
        line-height: 1.6;
    }

    .clinic-success-card hr {
        border: 0;
        border-top: 1px solid #f1e3e5;
        margin: 0 0 22px;
    }

    .clinic-success-close {
        position: absolute;
        top: 16px;
        right: 16px;
        width: 38px;
        height: 38px;
        border-radius: 999px;
        border: 0;
        background: #f2dfe3;
        color: #70131B;
        display: inline-grid;
        place-items: center;
        cursor: pointer;
    }

    .clinic-success-close svg {
        width: 19px;
        height: 19px;
        stroke-width: 2.4;
    }

    .clinic-success-continue {
        width: 100%;
        min-height: 50px;
        border: 0;
        border-radius: 9px;
        background: linear-gradient(135deg, #70131B, #9a1b2c);
        color: #ffffff;
        font-weight: 900;
        font-size: 15px;
        cursor: pointer;
        box-shadow: 0 16px 34px rgba(112, 19, 27, .20);
    }

    .clinic-success-confetti {
        display: none;
    }

    @keyframes clinicSuccessPop {
        from { opacity: 0; transform: scale(.72) translateY(18px); }
        to { opacity: 1; transform: scale(1) translateY(0); }
    }

    @keyframes clinicSuccessPulse {
        0% { opacity: .86; transform: scale(.78); }
        72% { opacity: .22; }
        100% { opacity: 0; transform: scale(1.18); }
    }

    @keyframes clinicCheckPop {
        from { opacity: 0; transform: scale(.72); }
        to { opacity: 1; transform: scale(1); }
    }

    @keyframes clinicCheckIconIn {
        from { opacity: 0; transform: scale(.5) rotate(-8deg); }
        to { opacity: 1; transform: scale(1) rotate(0); }
    }

    @keyframes clinicConfettiDrift {
        from { transform: translateY(0); opacity: .55; }
        to { transform: translateY(-8px); opacity: .95; }
    }

    html[data-theme="dark"] .applicant-condition-toggle-text {
        color: #38bdf8;
    }

    html[data-theme="dark"] .applicant-findings-label {
        color: #e2e8f0;
    }

    html[data-theme="dark"] .applicant-findings-option span {
        background: #1e293b;
        border-color: rgba(56, 189, 248, 0.3);
        color: #f1f5f9;
    }

    html[data-theme="dark"] .applicant-findings-option input:checked + span {
        background: #facc15;
        border-color: #facc15;
        color: #111827;
    }

    html[data-theme="dark"] .applicant-condition-field label {
        color: #e2e8f0;
    }

    html[data-theme="dark"] .applicant-condition-input,
    html[data-theme="dark"] .applicant-condition-textarea {
        background: #1e293b;
        border-color: rgba(3, 105, 161, 0.4);
        color: #f1f5f9;
    }

    html[data-theme="dark"] .applicant-condition-input:focus,
    html[data-theme="dark"] .applicant-condition-textarea:focus {
        border-color: #0284c7;
        box-shadow: 0 0 0 3px rgba(3, 105, 161, 0.2);
    }

    /* === MOBILE RESPONSIVE FIXES FOR WALK-IN MODAL === */
    @media (max-width: 768px) {
        /* Modal container adjustments */
        .applicant-modal-backdrop {
            padding: 16px 12px;
        }

        .applicant-modal-shell {
            border-radius: 16px;
            max-height: calc(100vh - 32px);
        }

        /* Header layout for mobile */
        .applicant-modal-head {
            position: relative;
            padding: 14px 16px 12px;
            gap: 10px;
        }

        .applicant-modal-head-main {
            width: 100%;
            gap: 12px;
        }

        .applicant-final-review-total-badge {
            position: absolute;
            top: 15px;
            right: 64px;
            min-height: 34px;
            padding: 7px 10px;
            font-size: 10px;
            letter-spacing: 0.04em;
        }

        .applicant-final-review-total-badge strong {
            font-size: 13px;
        }

        .applicant-modal-close {
            position: absolute !important;
            top: 14px !important;
            right: 16px !important;
            width: 36px !important;
            height: 36px !important;
            min-width: 36px !important;
            min-height: 36px !important;
            flex: none !important;
        }

        .applicant-modal-head-badge {
            width: 40px;
            height: 40px;
            font-size: 11px;
        }

        .applicant-modal-head-copy {
            flex: 1;
            min-width: 0;
        }

        .applicant-modal-head h3 {
            font-size: 0.95rem;
        }

        .applicant-modal-head p {
            font-size: 11px;
            max-width: 100%;
        }

        .applicant-modal-close {
            width: 36px;
            height: 36px;
            min-width: 36px;
            min-height: 36px;
            flex: 0 0 36px;
        }

        /* Modal body padding adjustment */
        .applicant-modal-body {
            padding: 14px;
            max-height: calc(100vh - 110px);
        }

        /* Modal grid layout for mobile */
        .applicant-modal-grid {
            grid-template-columns: 1fr;
            gap: 12px;
        }

        /* Applicant lookup grid - single column */
        .applicant-lookup-grid {
            grid-template-columns: 1fr;
            gap: 12px;
        }

        .applicant-lookup-card {
            padding: 12px;
            gap: 10px;
        }

        .applicant-lookup-icon {
            width: 36px;
            height: 36px;
            min-width: 36px;
            font-size: 11px;
        }

        .applicant-lookup-label {
            font-size: 10px;
        }

        .applicant-lookup-value {
            font-size: 13px;
        }

        /* Medical condition section - single column */
        .applicant-medical-condition-section {
            grid-template-columns: 1fr;
            gap: 12px;
        }

        .applicant-screening-panel {
            padding: 14px 16px;
        }

        .applicant-screening-panel-title {
            font-size: 14px;
            margin-bottom: 11px;
        }

        .applicant-screening-panel-copy {
            font-size: 11px;
            margin-bottom: 13px;
        }

        /* Vitals grid responsive */
        .applicant-vitals-grid {
            grid-template-columns: 1fr;
            gap: 10px;
        }

        /* Findings options on mobile */
        .applicant-findings-options {
            grid-template-columns: 1fr;
        }

        .applicant-findings-option span {
            min-height: 38px;
            padding: 8px 10px;
            font-size: 12px;
        }

        /* Form field adjustments */
        .applicant-condition-field {
            gap: 5px;
        }

        .applicant-condition-field label {
            font-size: 12px;
        }

        .applicant-condition-input,
        .applicant-condition-textarea {
            padding: 9px 11px;
            font-size: 13px;
        }

        .applicant-condition-toggle-text {
            font-size: 13px;
        }

        /* File actions - single column */
        .applicant-file-actions {
            grid-template-columns: 1fr;
            gap: 10px;
        }

        /* Reference actions - stacked */
        .applicant-ref-actions {
            grid-template-columns: 1fr;
            gap: 9px;
        }

        .applicant-ref-action-btn {
            width: 100%;
            padding: 11px 14px;
            font-size: 13px;
            min-height: 42px;
        }

        /* Upload button full width */
        .applicant-upload-btn,
        .applicant-ref-toggle-btn,
        .applicant-documents-trigger {
            width: 100%;
        }

        .applicant-upload-btn {
            min-height: 44px;
            padding: 11px 14px;
            font-size: 13px;
        }

        .applicant-documents-trigger {
            min-height: 44px;
            font-size: 13px;
            padding: 11px 14px;
        }

        .applicant-upload-note {
            font-size: 11px;
        }

        /* Reference lookup section */
        .applicant-ref-mode {
            gap: 12px;
        }

        .applicant-ref-copy h4 {
            font-size: 14px;
        }

        .applicant-ref-copy p {
            font-size: 12px;
        }

        .applicant-ref-instruction {
            padding: 10px;
            font-size: 12px;
        }

        .applicant-ref-field label {
            font-size: 12px;
        }

        .applicant-ref-input {
            padding: 9px 11px;
            font-size: 13px;
        }

        /* OCR result panel adjustments */
        .applicant-ref-result {
            padding: 12px;
            font-size: 13px;
        }

        .applicant-ref-result strong {
            font-size: 14px;
        }

        /* Status message sizing */
        .ocr-status {
            padding: 10px 12px;
            font-size: 12px;
            border-radius: 8px;
        }

        /* Documents workspace for mobile */
        .applicant-documents-workspace {
            grid-template-columns: 1fr;
            height: auto;
            min-height: auto;
            gap: 12px;
        }

        .applicant-documents-sidebar {
            max-height: 300px;
            overflow-y: auto;
        }

        .applicant-documents-grid {
            grid-template-columns: repeat(2, minmax(0, 1fr));
            gap: 10px;
        }

        .applicant-document-card {
            padding: 10px;
        }

        /* Upload preview adjustments */
        .applicant-upload-preview-container {
            max-height: 300px;
        }

        /* Preview buttons sizing */
        .applicant-preview-btn {
            padding: 7px 11px;
            font-size: 11px;
            gap: 5px;
        }

        .applicant-preview-btn svg {
            width: 14px;
            height: 14px;
        }
    }

    @media (max-width: 600px) {
        /* Extra tight mobile devices */
        .applicant-modal-backdrop {
            padding: 12px 10px;
        }

        .applicant-modal-shell {
            border-radius: 14px;
            max-height: calc(100vh - 24px);
        }

        .applicant-modal-head {
            position: relative;
            padding: 12px 14px 10px;
            padding-right: 50px;
        }

        .applicant-modal-head-badge {
            width: 38px;
            height: 38px;
        }

        .applicant-modal-close {
            position: absolute !important;
            top: 12px !important;
            right: 14px !important;
            width: 32px !important;
            height: 32px !important;
            min-width: 32px !important;
            min-height: 32px !important;
            flex: none !important;
        }

        .applicant-modal-body {
            padding: 12px;
            max-height: calc(100vh - 100px);
        }

        .applicant-lookup-card {
            padding: 10px;
        }

        .applicant-screening-panel {
            padding: 12px 14px;
        }

        .applicant-condition-input,
        .applicant-condition-textarea {
            padding: 8px 10px;
            font-size: 12px;
        }

        .applicant-ref-action-btn {
            padding: 10px 12px;
            font-size: 12px;
            min-height: 40px;
        }

        .applicant-upload-btn,
        .applicant-documents-trigger {
            min-height: 40px;
            padding: 10px 12px;
            font-size: 12px;
        }

        .applicant-documents-sidebar {
            max-height: 250px;
        }

        .applicant-documents-grid {
            grid-template-columns: 1fr;
            gap: 8px;
        }
    }

    /* Dark mode mobile adjustments */
    @media (max-width: 768px) {
        html[data-theme="dark"] .applicant-modal-shell {
            background: linear-gradient(180deg, rgba(17,24,39,0.98), rgba(20,23,30,0.98));
        }

        html[data-theme="dark"] .applicant-lookup-card {
            background: linear-gradient(135deg, rgba(30,41,59,0.8) 0%, rgba(15,23,42,0.8) 100%);
            border-color: rgba(148, 163, 184, 0.2);
        }

        html[data-theme="dark"] .applicant-lookup-card:hover {
            border-color: rgba(250, 204, 21, 0.3);
            background: linear-gradient(135deg, rgba(40,60,80,0.9) 0%, rgba(25,35,55,0.9) 100%);
        }

        html[data-theme="dark"] .applicant-condition-input,
        html[data-theme="dark"] .applicant-condition-textarea {
            background: rgba(30, 41, 59, 0.85);
            border-color: rgba(148, 163, 184, 0.2);
            color: #e2e8f0;
        }

        html[data-theme="dark"] .applicant-ref-input {
            background: rgba(30, 41, 59, 0.85);
            border-color: rgba(148, 163, 184, 0.2);
            color: #e2e8f0;
        }
    }
    .intake-option-card .intake-option-icon-wrap,
    .intake-option-card:hover .intake-option-icon-wrap,
    .intake-option-card:focus-within .intake-option-icon-wrap {
        color: #facc15 !important;
    }
    .intake-option-card .intake-option-icon-wrap svg,
    .intake-option-card:hover .intake-option-icon-wrap svg,
    .intake-option-card:focus-within .intake-option-icon-wrap svg {
        color: #facc15 !important;
        stroke: currentColor !important;
    }

    /* Employee lookup modal dark-theme surfaces */
    html[data-theme="dark"] #applicantRefModal .applicant-modal-shell.is-employee-lookup .applicant-modal-body,
    html[data-theme="dark"] #applicantRefModal .applicant-modal-shell.is-employee-lookup .applicant-ref-panel {
        background: #0f172a;
        color: #f8fafc;
    }
    html[data-theme="dark"] #applicantRefModal .applicant-modal-shell.is-employee-lookup .applicant-file-actions {
        background: rgba(30, 41, 59, .94);
        border-color: rgba(226, 232, 240, .24);
    }
    html[data-theme="dark"] #applicantRefModal .applicant-modal-shell.is-employee-lookup .applicant-ref-result,
    html[data-theme="dark"] #applicantRefModal .applicant-modal-shell.is-employee-lookup .applicant-lookup-card {
        background: #111827;
        border-color: rgba(148, 163, 184, .25);
        color: #f8fafc;
        box-shadow: 0 12px 28px rgba(0, 0, 0, .22);
    }
    html[data-theme="dark"] #applicantRefModal .applicant-modal-shell.is-employee-lookup .applicant-found-copy,
    html[data-theme="dark"] #applicantRefModal .applicant-modal-shell.is-employee-lookup .applicant-found-copy strong,
    html[data-theme="dark"] #applicantRefModal .applicant-modal-shell.is-employee-lookup .applicant-lookup-value,
    html[data-theme="dark"] #applicantRefModal .applicant-modal-shell.is-employee-lookup .applicant-lookup-label {
        color: #f8fafc;
    }
    html[data-theme="dark"] #applicantRefModal .applicant-modal-shell.is-employee-lookup .applicant-lookup-label {
        color: #cbd5e1;
    }
    html[data-theme="dark"] #applicantRefModal .applicant-modal-shell.is-employee-lookup .applicant-lookup-card:hover {
        background: #1e293b;
        border-color: rgba(250, 204, 21, .36);
    }
    html[data-theme="dark"] #applicantRefModal .applicant-modal-shell.is-employee-lookup .applicant-screening-panel {
        background: linear-gradient(135deg, #102a43, #0f2438);
        border-color: #0284c7;
        color: #f8fafc;
    }
    html[data-theme="dark"] #applicantRefModal .applicant-modal-shell.is-employee-lookup .applicant-screening-panel-title {
        color: #f8fafc;
    }
    html[data-theme="dark"] #applicantRefModal .applicant-modal-shell.is-employee-lookup .employee-physical-assessment-panel {
        background: linear-gradient(180deg, #111827, #0f172a);
        border-color: rgba(250, 204, 21, .28);
        scrollbar-color: rgba(250, 204, 21, .42) rgba(30, 41, 59, .8);
    }
    html[data-theme="dark"] #applicantRefModal .applicant-modal-shell.is-employee-lookup .employee-physical-assessment-panel .applicant-screening-panel-title {
        background: rgba(17, 24, 39, .88);
        border-bottom-color: rgba(250, 204, 21, .22);
        color: #facc15;
    }
    html[data-theme="dark"] #applicantRefModal .applicant-modal-shell.is-employee-lookup .employee-physical-exam-template {
        color: #e2e8f0;
    }
    html[data-theme="dark"] #applicantRefModal .applicant-modal-shell.is-employee-lookup .employee-physical-exam-template h5 {
        background: rgba(250, 204, 21, .12);
        border-color: rgba(250, 204, 21, .34);
        color: #facc15;
    }
    html[data-theme="dark"] #applicantRefModal .applicant-modal-shell.is-employee-lookup .employee-exam-section,
    html[data-theme="dark"] #applicantRefModal .applicant-modal-shell.is-employee-lookup .employee-exam-two-col {
        background: rgba(30, 41, 59, .86);
        border-color: rgba(148, 163, 184, .25);
        box-shadow: inset 0 1px 0 rgba(255, 255, 255, .04);
    }
    html[data-theme="dark"] #applicantRefModal .applicant-modal-shell.is-employee-lookup .employee-physical-assessment-panel {
        box-shadow: 0 12px 28px rgba(0, 0, 0, .28);
    }
    html[data-theme="dark"] #applicantRefModal .applicant-modal-shell.is-employee-lookup .employee-exam-section,
    html[data-theme="dark"] #applicantRefModal .applicant-modal-shell.is-employee-lookup .employee-exam-two-col,
    html[data-theme="dark"] #applicantRefModal .applicant-modal-shell.is-employee-lookup .bmi-gauge-card {
        box-shadow: 0 6px 16px rgba(0, 0, 0, .24), inset 0 1px 0 rgba(255, 255, 255, .04);
    }
    html[data-theme="dark"] #applicantRefModal .applicant-modal-shell.is-employee-lookup .employee-exam-section > strong {
        color: #facc15;
    }
    html[data-theme="dark"] #applicantRefModal .applicant-modal-shell.is-employee-lookup .employee-exam-options label,
    html[data-theme="dark"] #applicantRefModal .applicant-modal-shell.is-employee-lookup .employee-exam-line {
        color: #e2e8f0;
    }
    html[data-theme="dark"] #applicantRefModal .applicant-modal-shell.is-employee-lookup .employee-exam-options label {
        background: #0f172a;
        border-color: rgba(148, 163, 184, .34);
    }
    html[data-theme="dark"] #applicantRefModal .applicant-modal-shell.is-employee-lookup .employee-exam-options label:has(input:checked) {
        background: rgba(250, 204, 21, .14);
        border-color: rgba(250, 204, 21, .55);
        color: #fde68a;
    }
    html[data-theme="dark"] #applicantRefModal .applicant-modal-shell.is-employee-lookup .employee-physical-exam-template input[type="text"],
    html[data-theme="dark"] #applicantRefModal .applicant-modal-shell.is-employee-lookup .employee-physical-exam-template input[type="date"] {
        background: #0f172a;
        border-color: rgba(148, 163, 184, .38);
        color: #f8fafc;
    }
    html[data-theme="dark"] #applicantRefModal .applicant-modal-shell.is-employee-lookup .employee-physical-exam-template input::placeholder {
        color: #94a3b8;
    }
    html[data-theme="dark"] #applicantRefModal .applicant-modal-shell.is-employee-lookup .employee-exam-two-col input[readonly],
    html[data-theme="dark"] #applicantRefModal .applicant-modal-shell.is-employee-lookup .employee-exam-options select,
    html[data-theme="dark"] #applicantRefModal .applicant-modal-shell.is-employee-lookup .employee-exam-line select {
        background: #1e293b;
        color: #f8fafc;
        border-color: rgba(148, 163, 184, .38);
    }

    #applicantRefModal .applicant-modal-shell.is-employee-lookup .employee-physical-assessment-panel .bmi-gauge-card {
        display: block !important;
        margin-top: 12px;
    }

    /* Do not reserve space for an empty lookup status under the employee ID field. */
    #applicantRefModal .applicant-modal-shell.is-employee-lookup #applicantRefStatus:empty {
        display: none !important;
        min-height: 0;
        margin: 0;
        padding: 0;
        border: 0;
    }

    html[data-theme="dark"] #applicantRefModal .applicant-modal-shell.is-employee-lookup,
    html[data-theme="dark"] #applicantRefModal .applicant-modal-shell.is-employee-lookup .applicant-modal-body,
    html[data-theme="dark"] #applicantRefModal .applicant-modal-shell.is-employee-lookup .applicant-ref-panel {
        background: #0f172a !important;
        color: #f8fafc !important;
    }
    html[data-theme="dark"] #applicantRefModal .applicant-modal-shell.is-employee-lookup .applicant-ref-help,
    html[data-theme="dark"] #applicantRefModal .applicant-modal-shell.is-employee-lookup .applicant-ref-label,
    html[data-theme="dark"] #applicantRefModal .applicant-modal-shell.is-employee-lookup .applicant-ref-copy,
    html[data-theme="dark"] #applicantRefModal .applicant-modal-shell.is-employee-lookup label,
    html[data-theme="dark"] #applicantRefModal .applicant-modal-shell.is-employee-lookup p {
        color: #cbd5e1 !important;
    }
    html[data-theme="dark"] #applicantRefModal .applicant-modal-shell.is-employee-lookup h3,
    html[data-theme="dark"] #applicantRefModal .applicant-modal-shell.is-employee-lookup h4,
    html[data-theme="dark"] #applicantRefModal .applicant-modal-shell.is-employee-lookup strong {
        color: #f8fafc !important;
    }
    html[data-theme="dark"] #applicantRefModal .applicant-modal-shell.is-employee-lookup .applicant-ref-input,
    html[data-theme="dark"] #applicantRefModal .applicant-modal-shell.is-employee-lookup input[type="text"],
    html[data-theme="dark"] #applicantRefModal .applicant-modal-shell.is-employee-lookup input[type="search"] {
        background: #111827 !important;
        border-color: rgba(250, 204, 21, .22) !important;
        color: #f8fafc !important;
        -webkit-text-fill-color: #f8fafc !important;
        box-shadow: inset 0 1px 0 rgba(255, 255, 255, .04) !important;
    }
    html[data-theme="dark"] #applicantRefModal .applicant-modal-shell.is-employee-lookup .applicant-ref-input::placeholder,
    html[data-theme="dark"] #applicantRefModal .applicant-modal-shell.is-employee-lookup input::placeholder {
        color: #94a3b8 !important;
        -webkit-text-fill-color: #94a3b8 !important;
    }
    html[data-theme="dark"] #applicantRefModal .applicant-modal-shell.is-employee-lookup .applicant-ref-notice,
    html[data-theme="dark"] #applicantRefModal .applicant-modal-shell.is-employee-lookup .applicant-ref-result,
    html[data-theme="dark"] #applicantRefModal .applicant-modal-shell.is-employee-lookup .applicant-lookup-card,
    html[data-theme="dark"] #applicantRefModal .applicant-modal-shell.is-employee-lookup #applicantRefStatus:not(:empty) {
        background: #111827 !important;
        border-color: rgba(250, 204, 21, .18) !important;
        color: #f8fafc !important;
    }
    html[data-theme="dark"] #applicantRefModal .applicant-modal-shell.is-employee-lookup #applicantRefStatus.is-error,
    html[data-theme="dark"] #applicantRefModal .applicant-modal-shell.is-employee-lookup .manual-lookup-status.is-error {
        background: rgba(127, 29, 29, .34) !important;
        border-color: rgba(248, 113, 113, .34) !important;
        color: #fecaca !important;
    }
    html[data-theme="dark"] #applicantRefModal .applicant-modal-shell.is-employee-lookup #applicantRefStatus.is-loading,
    html[data-theme="dark"] #applicantRefModal .applicant-modal-shell.is-employee-lookup .manual-lookup-status.is-loading {
        background: rgba(250, 204, 21, .12) !important;
        border-color: rgba(250, 204, 21, .34) !important;
        color: #fde68a !important;
    }
    html[data-theme="dark"] #applicantRefModal .applicant-modal-shell.is-employee-lookup .applicant-ref-cancel-btn {
        background: #e2e8f0 !important;
        color: #0f172a !important;
    }

    #applicantRefModal .applicant-ref-actions.has-draft-action {
        display: grid !important;
        grid-template-columns: repeat(4, minmax(0, 1fr)) !important;
        align-items: center;
    }

    #applicantRefModal .applicant-ref-actions.has-draft-action .applicant-ref-cancel-btn {
        grid-column: 2;
    }

    #applicantRefModal .applicant-ref-actions.has-draft-action .applicant-ref-draft-btn {
        grid-column: 3;
    }

    #applicantRefModal .applicant-ref-actions.has-draft-action .applicant-ref-find-btn {
        grid-column: 4;
    }

    #applicantRefModal .applicant-ref-actions.has-draft-action .applicant-ref-action-btn {
        width: 100%;
        min-width: 0;
    }

    /* Applicant workflow and employee/student lookup dark-mode polish */
    html[data-theme="dark"] #applicantRefModal .applicant-ref-copy .applicant-ref-kicker,
    html[data-theme="dark"] #applicantRefModal .applicant-ref-copy h4 {
        color: #ffffff !important;
    }

    html[data-theme="dark"] #applicantRefModal .applicant-workflow-card {
        background: transparent !important;
        background-image: none !important;
        border: 1px solid rgba(250, 204, 21, 0.18) !important;
        box-shadow: 0 18px 34px rgba(0, 0, 0, 0.34), 0 4px 12px rgba(0, 0, 0, 0.22) !important;
        color: #ffffff !important;
    }

    html[data-theme="dark"] #applicantRefModal .applicant-workflow-card:hover,
    html[data-theme="dark"] #applicantRefModal .applicant-workflow-card:focus-visible {
        background: #facc15 !important;
        background-image: none !important;
        border-color: #facc15 !important;
        box-shadow: 0 0 0 3px rgba(250, 204, 21, 0.12), 0 20px 30px rgba(139, 0, 0, 0.22) !important;
        color: #70131B !important;
    }

    html[data-theme="dark"] #applicantRefModal .applicant-modal-shell.is-employee-lookup .applicant-ref-instruction {
        background: #050505 !important;
        border: 1px solid rgba(250, 204, 21, 0.18) !important;
        box-shadow: 0 18px 34px rgba(0, 0, 0, 0.34), 0 4px 12px rgba(0, 0, 0, 0.22) !important;
        color: #ffffff !important;
    }

    html[data-theme="dark"] #applicantRefModal .applicant-modal-shell.is-employee-lookup .applicant-ref-help-copy,
    html[data-theme="dark"] #applicantRefModal .applicant-modal-shell.is-employee-lookup .applicant-ref-help-copy strong {
        color: #ffffff !important;
    }

    #applicantRefModal .applicant-modal-shell.is-employee-lookup #applicantRefStatus:not(.info):not(.success):not(.error):not(.encoded) {
        display: none !important;
        min-height: 0 !important;
        margin: 0 !important;
        padding: 0 !important;
        border: 0 !important;
        box-shadow: none !important;
        background: transparent !important;
    }

    html[data-theme="dark"] #applicantRefModal .applicant-modal-shell.is-employee-lookup .applicant-ref-input {
        background: #111827 !important;
        background-image: none !important;
        border: 1px solid rgba(250, 204, 21, 0.18) !important;
        box-shadow: 0 18px 34px rgba(0, 0, 0, 0.26), inset 0 1px 0 rgba(255, 255, 255, 0.04) !important;
        color: #ffffff !important;
        -webkit-text-fill-color: #ffffff !important;
    }

    html[data-theme="dark"] #applicantRefModal .applicant-modal-shell.is-employee-lookup .applicant-ref-input:focus {
        border-color: rgba(250, 204, 21, 0.46) !important;
        box-shadow: 0 0 0 3px rgba(250, 204, 21, 0.10), 0 18px 34px rgba(0, 0, 0, 0.30) !important;
    }

    html[data-theme="dark"] #applicantRefModal .applicant-modal-shell.is-employee-lookup .applicant-ref-cancel-btn,
    html[data-theme="dark"] #applicantRefModal .applicant-modal-shell.is-employee-lookup .applicant-ref-find-btn {
        border: 1px solid rgba(250, 204, 21, 0.18) !important;
        box-shadow: 0 18px 34px rgba(0, 0, 0, 0.34), 0 4px 12px rgba(0, 0, 0, 0.22) !important;
    }

    html[data-theme="dark"] #applicantRefModal .applicant-modal-shell.is-employee-lookup .applicant-ref-cancel-btn {
        background: #111827 !important;
        color: #ffffff !important;
    }

    html[data-theme="dark"] #applicantRefModal .applicant-modal-shell.is-employee-lookup .applicant-ref-find-btn {
        background: #70131B !important;
        color: #facc15 !important;
        border-color: #facc15 !important;
    }

    html[data-theme="dark"] #applicantRefModal .applicant-modal-shell.is-employee-lookup .applicant-ref-cancel-btn:hover,
    html[data-theme="dark"] #applicantRefModal .applicant-modal-shell.is-employee-lookup .applicant-ref-cancel-btn:focus,
    html[data-theme="dark"] #applicantRefModal .applicant-modal-shell.is-employee-lookup .applicant-ref-find-btn:hover,
    html[data-theme="dark"] #applicantRefModal .applicant-modal-shell.is-employee-lookup .applicant-ref-find-btn:focus {
        background: #facc15 !important;
        border-color: #facc15 !important;
        color: #70131B !important;
        box-shadow: 0 0 0 3px rgba(250, 204, 21, 0.12), 0 20px 30px rgba(139, 0, 0, 0.22) !important;
    }

    #applicantRefModal .applicant-modal-shell.is-employee-lookup .applicant-ref-toggle-btn {
        background: #70131B !important;
        background-image: none !important;
        border: 1px solid rgba(250, 204, 21, 0.18) !important;
        box-shadow: 0 14px 28px rgba(112, 19, 27, 0.12), 0 4px 12px rgba(15, 23, 42, 0.06) !important;
        color: #ffffff !important;
    }

    html[data-theme="dark"] #applicantRefModal .applicant-modal-shell.is-employee-lookup .applicant-ref-toggle-btn {
        background: transparent !important;
        background-image: none !important;
        box-shadow: 0 18px 34px rgba(0, 0, 0, 0.34), 0 4px 12px rgba(0, 0, 0, 0.22) !important;
    }

    #applicantRefModal .applicant-modal-shell.is-employee-lookup .applicant-ref-toggle-btn svg,
    #applicantRefModal .applicant-modal-shell.is-employee-lookup .applicant-ref-toggle-btn span {
        color: #ffffff !important;
        stroke: currentColor !important;
    }

    #applicantRefModal .applicant-modal-shell.is-employee-lookup .applicant-ref-toggle-btn:hover,
    #applicantRefModal .applicant-modal-shell.is-employee-lookup .applicant-ref-toggle-btn:focus {
        background: #facc15 !important;
        background-image: none !important;
        border-color: #facc15 !important;
        color: #70131B !important;
        box-shadow: 0 0 0 3px rgba(250, 204, 21, 0.12), 0 20px 30px rgba(139, 0, 0, 0.22) !important;
    }

    #applicantRefModal .applicant-modal-shell.is-employee-lookup .applicant-ref-toggle-btn:hover svg,
    #applicantRefModal .applicant-modal-shell.is-employee-lookup .applicant-ref-toggle-btn:focus svg,
    #applicantRefModal .applicant-modal-shell.is-employee-lookup .applicant-ref-toggle-btn:hover span,
    #applicantRefModal .applicant-modal-shell.is-employee-lookup .applicant-ref-toggle-btn:focus span {
        color: #70131B !important;
    }

    /* Final Walk-in intake surface pass: match Reports/Developer Tools */
    .patient-intake-entry-shell > .walkin-strip-card {
        border: 1px solid rgba(250, 204, 21, 0.20) !important;
        box-shadow: 0 18px 34px rgba(112, 19, 27, 0.08) !important;
    }

    .patient-intake-entry-shell > .walkin-strip-card::before {
        background: #70131B !important;
    }

    .patient-intake-entry-shell .intake-option-card,
    .patient-intake-entry-shell .intake-option-registration,
    .patient-intake-entry-shell .intake-option-scan,
    .patient-intake-entry-shell .intake-option-assisted,
    .patient-intake-entry-shell .intake-option-applicant {
        background: #70131B !important;
        background-image: none !important;
        border: 1px solid rgba(250, 204, 21, 0.18) !important;
        box-shadow: 0 10px 24px rgba(112, 19, 27, 0.18) !important;
        color: #ffffff !important;
    }

    html[data-theme="dark"] .patient-intake-entry-shell > .walkin-strip-card {
        background: transparent !important;
        background-image: none !important;
        border-color: rgba(250, 204, 21, 0.20) !important;
        box-shadow: 0 18px 34px rgba(0, 0, 0, 0.18) !important;
    }

    html[data-theme="dark"] .patient-intake-entry-shell > .walkin-strip-card::before {
        background: #facc15 !important;
    }

    html[data-theme="dark"] .patient-intake-entry-shell .intake-option-card,
    html[data-theme="dark"] .patient-intake-entry-shell .intake-option-registration,
    html[data-theme="dark"] .patient-intake-entry-shell .intake-option-scan,
    html[data-theme="dark"] .patient-intake-entry-shell .intake-option-assisted,
    html[data-theme="dark"] .patient-intake-entry-shell .intake-option-applicant {
        background: transparent !important;
        background-image: none !important;
        border: 1px solid rgba(250, 204, 21, 0.18) !important;
        box-shadow: 0 18px 34px rgba(0, 0, 0, 0.34), 0 4px 12px rgba(0, 0, 0, 0.22) !important;
        color: #ffffff !important;
    }

    .patient-intake-entry-shell .intake-option-card:hover,
    .patient-intake-entry-shell .intake-option-card:focus-within,
    html[data-theme="dark"] .patient-intake-entry-shell .intake-option-card:hover,
    html[data-theme="dark"] .patient-intake-entry-shell .intake-option-card:focus-within {
        background: #facc15 !important;
        background-image: none !important;
        border-color: #facc15 !important;
        box-shadow: 0 0 0 3px rgba(250, 204, 21, 0.12), 0 20px 30px rgba(139, 0, 0, 0.22) !important;
        color: #70131B !important;
    }

    /* OCR modal control surfaces */
    html[data-theme="dark"] #applicantScanModal .btn-ocr,
    html[data-theme="dark"] #applicantScanModal .manual-find-btn {
        border: 1px solid rgba(250, 204, 21, 0.18) !important;
        box-shadow: 0 18px 34px rgba(0, 0, 0, 0.34), 0 4px 12px rgba(0, 0, 0, 0.22) !important;
    }

    html[data-theme="dark"] #applicantScanModal .btn-ocr-primary {
        background: #111827 !important;
        background-image: none !important;
        color: #ffffff !important;
    }

    html[data-theme="dark"] #applicantScanModal .btn-ocr-secondary,
    html[data-theme="dark"] #applicantScanModal .manual-find-btn {
        background: #70131B !important;
        background-image: none !important;
        color: #ffffff !important;
    }

    html[data-theme="dark"] #applicantScanModal .btn-ocr:hover:not(:disabled),
    html[data-theme="dark"] #applicantScanModal .btn-ocr:focus:not(:disabled),
    html[data-theme="dark"] #applicantScanModal .manual-find-btn:hover:not(:disabled),
    html[data-theme="dark"] #applicantScanModal .manual-find-btn:focus:not(:disabled) {
        background: #facc15 !important;
        background-image: none !important;
        border-color: #facc15 !important;
        color: #70131B !important;
        box-shadow: 0 0 0 3px rgba(250, 204, 21, 0.12), 0 20px 30px rgba(139, 0, 0, 0.22) !important;
    }

    /* Final light-mode surface pass */
    html:not([data-theme="dark"]) .patient-intake-entry-shell > .walkin-strip-card,
    html:not([data-theme="dark"]) .patient-intake-entry-shell .intake-option-card,
    html:not([data-theme="dark"]) .patient-intake-entry-shell .intake-option-scan,
    html:not([data-theme="dark"]) .patient-intake-entry-shell .intake-option-assisted,
    html:not([data-theme="dark"]) .patient-intake-entry-shell .intake-option-applicant {
        border: 1px solid rgba(250, 204, 21, 0.22) !important;
        box-shadow: 0 14px 28px rgba(112, 19, 27, 0.12), 0 4px 12px rgba(15, 23, 42, 0.06) !important;
    }

    /* Standard modal chrome: OCR/walk-in and applicant/employee workflows */
    #applicantScanModal .applicant-modal-shell,
    #applicantRefModal .applicant-modal-shell,
    html[data-theme="dark"] #applicantScanModal .applicant-modal-shell,
    html[data-theme="dark"] #applicantRefModal .applicant-modal-shell {
        border: 1px solid rgba(250, 204, 21, .34) !important;
    }

    #applicantScanModal .applicant-modal-head,
    #applicantRefModal .applicant-modal-head {
        background: linear-gradient(135deg, #70131B, #8f2230) !important;
    }

    #applicantScanModal .applicant-modal-close,
    #applicantRefModal .applicant-modal-close {
        width: 40px !important;
        height: 40px !important;
        min-width: 40px !important;
        min-height: 40px !important;
        padding: 0 !important;
        border: 1px solid #8f2230 !important;
        border-radius: 999px !important;
        background: linear-gradient(135deg, #70131B, #8f2230) !important;
        color: #ffffff !important;
        overflow: hidden !important;
        box-shadow: 0 0 0 3px rgba(112, 19, 27, .12), 0 10px 22px rgba(112, 19, 27, .20) !important;
    }

    #applicantScanModal .applicant-modal-close::after,
    #applicantRefModal .applicant-modal-close::after {
        z-index: 0 !important;
        pointer-events: none;
    }

    #applicantScanModal .applicant-modal-close svg,
    #applicantRefModal .applicant-modal-close svg {
        position: relative;
        z-index: 1;
    }

    #applicantScanModal .applicant-modal-close:hover,
    #applicantScanModal .applicant-modal-close:focus-visible,
    #applicantRefModal .applicant-modal-close:hover,
    #applicantRefModal .applicant-modal-close:focus-visible {
        border-color: #facc15 !important;
        background: #facc15 !important;
        color: #70131B !important;
        transform: translateY(-1px) !important;
        outline: none;
        box-shadow: 0 0 0 3px rgba(250, 204, 21, .18), 0 14px 24px rgba(112, 19, 27, .16) !important;
    }

</style>
@endpush

@section('content')
@php
    $role = \App\Models\User::normalizeRole(optional(auth()->user())->user_role ?? '');
    $isStudentAssistant = optional(auth()->user())->isStudentAssistant();
    $basePrefix = $role === \App\Models\User::ROLE_ADMIN ? '/assistant' : '/admin';
    $currentMode = in_array($mode ?? '', ['scan', 'assisted', 'registration', 'applicant'], true) ? $mode : '';
    $idpBaseUrl = rtrim((string) config('services.idp.base_url', ''), '/');
    $idpClientId = trim((string) config('services.idp.client_id', ''));
    $portalRegisterUrl = ($idpBaseUrl !== '' && $idpClientId !== '')
        ? $idpBaseUrl . '/login?' . http_build_query(['client_id' => $idpClientId])
        : route('login');
    $idpRegistrationLink = 'https://identity-provider.isaxbsit2027.com/register?client_id=7112646b-c785-4306-b00f-87d29ad54fb2';
@endphp

<div class="patient-intake-entry-shell" style="max-width: 980px; margin: 20px auto;">
    @if($currentMode === '')
    <div class="card p-4 shadow-sm walkin-strip-card" style="border-radius: 18px; border: none; margin-bottom: 20px;">
        <div style="display:flex; justify-content:space-between; align-items:flex-start; gap:18px; flex-wrap:wrap;">
            <div>
                <p class="intake-heading-kicker">Patient Intake</p>
                <h2 class="intake-heading-title">Choose how you want to begin the clinic intake flow</h2>
            </div>
        </div>

        <div class="intake-options-grid">
            <a href="#" class="intake-option-link" id="openScanLookupModal">
                <div class="intake-option-card intake-option-scan {{ $currentMode === 'scan' ? 'is-active' : '' }}">
                    <span class="intake-option-chip" aria-hidden="true">
                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M4.5 7.5V6A1.5 1.5 0 0 1 6 4.5h1.5m10.5 3V6A1.5 1.5 0 0 0 16.5 4.5H15m3 12V18a1.5 1.5 0 0 1-1.5 1.5H15m-9-3V18A1.5 1.5 0 0 0 7.5 19.5H9" />
                        </svg>
                    </span>
                    <span class="intake-option-icon-wrap" aria-hidden="true">
                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M3 7.5A1.5 1.5 0 0 1 4.5 6h15A1.5 1.5 0 0 1 21 7.5v9A1.5 1.5 0 0 1 19.5 18h-15A1.5 1.5 0 0 1 3 16.5v-9Zm3 3h12m-12 3h7.5" />
                        </svg>
                    </span>
                    <h3 class="intake-option-title">Walk-in Patient</h3>
                    <p class="intake-option-copy">Look up an existing patient record using ID scanning or manual entry to continue directly to consultation.</p>
                </div>
            </a>

            <a href="{{ url()->current() }}?mode=assisted" class="intake-option-link">
                <div class="intake-option-card intake-option-assisted {{ $currentMode === 'assisted' ? 'is-active' : '' }}">
                    <span class="intake-option-chip" aria-hidden="true">
                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 6v6m0 0v6m0-6h6m-6 0H6" />
                        </svg>
                    </span>
                    <span class="intake-option-icon-wrap" aria-hidden="true">
                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M15 19.128a9.38 9.38 0 0 0 2.625.372 9.337 9.337 0 0 0 4.121-.983 4.125 4.125 0 0 0-7.533-2.493M15 19.128v-.003c0-1.113-.285-2.16-.786-3.072M15 19.128v.106A12.318 12.318 0 0 1 8.624 21c-2.331 0-4.552-.645-6.46-1.766l-.084-.049a6.375 6.375 0 0 1 11.964-3.07M12 6.375a3.375 3.375 0 1 1-6.75 0 3.375 3.375 0 0 1 6.75 0Zm8.25 2.25a2.625 2.625 0 1 1-5.25 0 2.625 2.625 0 0 1 5.25 0Z" />
                        </svg>
                    </span>
                    <h3 class="intake-option-title">Assisted Intake</h3>
                    <p class="intake-option-copy">Let clinic staff capture the patient record on their behalf when illness or urgency makes self-registration impractical.</p>
                </div>
            </a>

            @unless($isStudentAssistant)
                {{-- Applicants card — opens the reference number modal --}}
                <a href="#" class="intake-option-link" id="openApplicantRefModal">
                    <div class="intake-option-card intake-option-applicant {{ $currentMode === 'applicant' ? 'is-active' : '' }}">
                        <span class="intake-option-chip" aria-hidden="true">
                            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M12 6v12m6-6H6" />
                            </svg>
                        </span>
                        <span class="intake-option-icon-wrap" aria-hidden="true">
                            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M9 12h3.75M9 15h3.75M9 18h3.75m3 .75H18a2.25 2.25 0 0 0 2.25-2.25V6.108c0-1.135-.845-2.098-1.976-2.192a48.424 48.424 0 0 0-1.123-.08m-5.801 0c-.065.21-.1.433-.1.664 0 .414.336.75.75.75h4.5a.75.75 0 0 0 .75-.75 2.25 2.25 0 0 0-.1-.664m-5.8 0A2.251 2.251 0 0 1 13.5 2.25H15c1.012 0 1.867.668 2.15 1.586m-5.8 0c-.376.023-.75.05-1.124.08C9.095 4.01 8.25 4.973 8.25 6.108V8.25m0 0H4.875c-.621 0-1.125.504-1.125 1.125v11.25c0 .621.504 1.125 1.125 1.125h9.75c.621 0 1.125-.504 1.125-1.125V9.375c0-.621-.504-1.125-1.125-1.125H8.25ZM6.75 12h.008v.008H6.75V12Zm0 3h.008v.008H6.75V15Zm0 3h.008v.008H6.75V18Z" />
                            </svg>
                        </span>
                        <h3 class="intake-option-title">Applicants</h3>
                        <p class="intake-option-copy">Enter a reference number to look up the applicant record.</p>
                    </div>
                </a>
            @endunless

            <a href="#" class="intake-option-link" id="openClinicRefModal">
                <div class="intake-option-card intake-option-applicant">
                    <span class="intake-option-chip" aria-hidden="true">
                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 6v12m6-6H6" />
                        </svg>
                    </span>
                    <span class="intake-option-icon-wrap" aria-hidden="true">
                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M4.5 6.75h15m-15 5.25h15m-15 5.25h9" />
                        </svg>
                    </span>
                    <h3 class="intake-option-title">Employee's / Student</h3>
                    <p class="intake-option-copy">Enter Employee's / Student ID to lookup clinic records to manage clinic profiles.</p>
                </div>
            </a>

        </div>
    </div>

    {{-- Applicants Modal: Reference Number Lookup --}}
    <div class="applicant-modal-backdrop" id="applicantRefModal">
        <div class="applicant-modal-shell">
            <div class="applicant-modal-head">
                <div class="applicant-modal-head-main">
                    <div class="applicant-modal-head-badge" id="lookupModalBadge">AP</div>
                    <div class="applicant-modal-head-copy">
                        <h3 id="lookupModalTitle">Applicants</h3>
                        <p id="lookupModalSubtitle">Enter the applicant's reference number to look up the record.</p>
                    </div>
                </div>
                <div class="applicant-ref-head-actions">
                    <div class="applicant-final-review-total-badge" id="applicantFinalReviewTotalBadge">
                        <span>Total Applicants</span>
                        <strong id="applicantFinalReviewTotalCount">{{ $finalReviewApplicants->count() }}</strong>
                    </div>
                    <button type="button" class="applicant-modal-close" id="closeApplicantRefModal" aria-label="Close modal">
                        <x-outline-icon name="x-mark" />
                    </button>
                </div>
            </div>

            <div class="applicant-modal-body" style="display:flex; flex-direction:column; align-items:center; justify-content:center; min-height:220px; gap:18px;">
                <div class="applicant-ref-mode" id="applicantRefDefault">
                    <div class="applicant-ref-copy">
                        <p class="applicant-ref-kicker">Proceed</p>
                        <h4 id="lookupModalEntryTitle">Applicant Workflow</h4>
                        <p id="lookupModalEntrySubtitle">Choose encoding for the first station or final review for approval.</p>
                    </div>

                    <div class="applicant-workflow-grid" id="applicantWorkflowChoices">
                        <button type="button" class="applicant-workflow-card" id="btnStartApplicantEncoding">
                            <x-outline-icon name="clipboard-document-list" />
                            <strong>Encode Assessment</strong>
                        </button>
                        <button type="button" class="applicant-workflow-card" id="btnStartApplicantFinalReview">
                            <x-outline-icon name="document-text" />
                            <strong>Final Review</strong>
                        </button>
                    </div>

                    <button type="button" id="btnShowApplicantRefInput" class="applicant-ref-toggle-btn" style="max-width:360px; display:none;">
                        <x-outline-icon name="magnifying-glass" />
                        <span id="lookupModalEntryButtonText">Input Reference Number</span>
                    </button>

                    <div class="applicant-final-review-list" id="applicantFinalReviewList">
                        <div class="applicant-final-review-toolbar">
                            <label class="applicant-final-review-search-wrap" for="applicantFinalReviewSearch">
                                <x-outline-icon name="magnifying-glass" />
                                <input type="search" class="applicant-final-review-search" id="applicantFinalReviewSearch" placeholder="Search by name, email, or reference number">
                            </label>
                            <button type="button" class="applicant-final-review-btn applicant-final-review-refresh-btn" id="btnRefreshFinalReviewList" aria-label="Refresh final review list" title="Refresh final review list">
                                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 32 32" aria-hidden="true" focusable="false">
                                    <path d="M16 4C10.886719 4 6.617188 7.160156 4.875 11.625L6.71875 12.375C8.175781 8.640625 11.710938 6 16 6C19.242188 6 22.132813 7.589844 23.9375 10H20V12H27V5H25V8.09375C22.808594 5.582031 19.570313 4 16 4ZM25.28125 19.625C23.824219 23.359375 20.289063 26 16 26C12.722656 26 9.84375 24.386719 8.03125 22H12V20H5V27H7V23.90625C9.1875 26.386719 12.394531 28 16 28C21.113281 28 25.382813 24.839844 27.125 20.375Z"></path>
                                </svg>
                            </button>
                            <button type="button" class="applicant-final-review-btn" id="btnFinalReviewManualLookup">
                                <x-outline-icon name="magnifying-glass" />
                                <span>Reference Lookup</span>
                            </button>
                        </div>
                        <div id="applicantFinalReviewRows">
                            @forelse($finalReviewApplicants as $reviewApplicant)
                                @php
                                    $reviewUser = $reviewApplicant->user;
                                    $reviewName = $reviewUser?->name
                                        ?: trim(implode(' ', array_filter([
                                            $reviewUser?->first_name,
                                            $reviewUser?->middle_name,
                                            $reviewUser?->last_name,
                                        ])))
                                        ?: 'Applicant';
                                    $reviewRef = $reviewApplicant->reference_number ?: 'N/A';
                                    $reviewPhotoUrl = filled($reviewApplicant->student_photo)
                                        ? route('walkin.document', ['healthProfile' => $reviewApplicant->id, 'document' => 'student_photo'])
                                        : '';
                                    $reviewNameParts = collect(preg_split('/\s+/', trim($reviewName)) ?: [])->filter()->values();
                                    $reviewInitials = strtoupper(
                                        ($reviewNameParts->first() ? mb_substr($reviewNameParts->first(), 0, 1) : 'A')
                                        . ($reviewNameParts->count() > 1 ? mb_substr($reviewNameParts->last(), 0, 1) : 'P')
                                    );
                                @endphp
                                <article class="applicant-final-review-card" data-final-review-row data-search="{{ strtolower($reviewName . ' ' . ($reviewUser?->email ?? '') . ' ' . $reviewRef) }}">
                                    <div class="applicant-final-review-person">
                                        <span class="applicant-final-review-avatar">
                                            @if($reviewPhotoUrl)
                                                <img src="{{ $reviewPhotoUrl }}" alt="{{ $reviewName }} 2x2 photo">
                                            @else
                                                {{ $reviewInitials }}
                                            @endif
                                        </span>
                                        <div>
                                            <small>Applicant</small>
                                            <strong>{{ $reviewName }}</strong>
                                            <span>{{ $reviewUser?->email ?? 'N/A' }}</span>
                                        </div>
                                    </div>
                                    <div>
                                        <small>Reference Number</small>
                                        <strong class="applicant-final-review-reference-badge">{{ $reviewRef }}</strong>
                                    </div>
                                    <button type="button" class="applicant-final-review-btn" data-final-review-reference="{{ $reviewRef }}"><span>Review</span></button>
                                </article>
                            @empty
                                <div class="applicant-documents-empty">No encoded applicants are ready for final review.</div>
                            @endforelse
                        </div>
                        <div class="applicant-final-review-empty" id="applicantFinalReviewEmpty">No encoded applicant matches your search.</div>
                        <div class="applicant-final-review-pagination" id="applicantFinalReviewPagination">
                            <span class="applicant-final-review-pagination-summary">Encoded applicants</span>
                            <span class="applicant-final-review-pagination-controls">
                                <button type="button" class="applicant-final-review-page-btn" id="applicantFinalReviewPrev" aria-label="Previous page">&larr;</button>
                                <span class="applicant-final-review-page-label" id="applicantFinalReviewPageLabel">1</span>
                                <button type="button" class="applicant-final-review-page-btn" id="applicantFinalReviewNext" aria-label="Next page">&rarr;</button>
                            </span>
                            <select class="applicant-final-review-per-page" id="applicantFinalReviewPerPage" aria-label="Final review applicants per page">
                                <option value="5">5 per page</option>
                                <option value="10">10 per page</option>
                                <option value="15">15 per page</option>
                                <option value="20">20 per page</option>
                                <option value="all">Show all</option>
                            </select>
                        </div>
                    </div>
                </div>

                <div class="applicant-ref-panel" id="applicantRefEntry">
                    <div class="applicant-final-review-action-row" id="applicantFinalReviewActionRow">
                        <button type="button" class="applicant-final-review-back" id="btnBackToFinalReviewList">&larr; Back</button>
                        <span class="applicant-final-review-action-separator" aria-hidden="true"></span>
                    </div>
                    <div class="applicant-ref-lookup-row">
                    <div class="applicant-ref-instruction">
                        <span class="applicant-ref-help-copy" id="lookupModalHelpCopy">Find the reference number in the <strong>Admission System</strong> under the applicant's profile or registration form.</span>
                    </div>
                    <div class="applicant-ref-field">
                        <label for="applicantRefInput" id="lookupModalFieldLabel">Reference Number</label>
                        <input type="text" id="applicantRefInput" class="applicant-ref-input" placeholder="Enter reference number">
                    </div>
                    </div>

                    <div class="applicant-file-actions" id="applicantFileActions">
                        <button type="button" id="btnViewApplicantInformation" class="applicant-documents-trigger applicant-file-action">
                            <x-outline-icon name="user-circle" />
                            <span data-information-button-label>Health Information Form</span>
                        </button>
                        <button type="button" id="btnViewMedicalCondition" class="applicant-documents-trigger applicant-file-action">
                            <x-outline-icon name="clipboard-document-list" />
                            <span data-condition-button-label>Medical Condition</span>
                        </button>
                        <button type="button" id="btnViewApplicantDocuments" class="applicant-documents-trigger applicant-file-action">
                            <x-outline-icon name="document-text" />
                            <span>Uploaded Documents</span>
                            <span class="applicant-documents-count" id="applicantDocumentsCount">0</span>
                        </button>
                        <div class="applicant-pending-history-wrap" id="applicantPendingHistoryWrap">
                            <button type="button" id="btnViewPendingHistory" class="applicant-documents-trigger applicant-file-action">
                                <x-outline-icon name="clipboard-document-list" />
                                <span>Pending History</span>
                            </button>
                            <div class="applicant-pending-history-bubble" id="applicantPendingHistoryBubble" role="status">
                                <div>
                                    <span>Pending Reason</span>
                                    <strong id="applicantPendingHistoryReason">-</strong>
                                </div>
                                <div>
                                    <span>Other Pending Reason</span>
                                    <strong id="applicantPendingHistoryOther">-</strong>
                                </div>
                            </div>
                        </div>
                        <button type="button" id="btnViewSavedAssessment" class="applicant-documents-trigger applicant-file-action">
                            <x-outline-icon name="clipboard-document-list" />
                            <span>View Saved Review</span>
                        </button>
                    </div>

                    <div id="applicantRefStatus" class="ocr-status"></div>

                    <div id="applicantFoundCard" class="applicant-ref-result" data-initials="AP">
                        <div class="applicant-found-copy">
                            Full Name
                            <strong id="applicantFoundName"></strong>
                        </div>
                        <span id="applicantConditionBadge" class="applicant-condition-badge">Condition: No Medical Condition</span>
                    </div>

                    <div id="applicantLookupDetails" class="applicant-lookup-details">
                        <div class="applicant-lookup-grid">
                            <div class="applicant-lookup-item">
                                <div class="applicant-lookup-card">
                                    <div class="applicant-lookup-icon">
                                        RN
                                    </div>
                                    <div class="applicant-lookup-content">
                                        <p class="applicant-lookup-label">Reference Number</p>
                                        <div class="applicant-reference-value-row">
                                            <p class="applicant-lookup-value" id="applicantLookupRef">-</p>
                                            <button type="button" class="applicant-reference-copy" id="copyApplicantReference" aria-label="Copy reference number" title="Copy reference number">
                                                <svg viewBox="0 0 24 24" aria-hidden="true">
                                                    <rect x="9" y="9" width="11" height="11" rx="2"></rect>
                                                    <path d="M15 9V6a2 2 0 0 0-2-2H6a2 2 0 0 0-2 2v7a2 2 0 0 0 2 2h3"></path>
                                                </svg>
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="applicant-lookup-item">
                                <div class="applicant-lookup-card">
                                    <div class="applicant-lookup-icon">
                                        ST
                                    </div>
                                    <div class="applicant-lookup-content">
                                        <p class="applicant-lookup-label">Status</p>
                                        <p class="applicant-lookup-value" id="applicantLookupStatus">-</p>
                                    </div>
                                </div>
                            </div>

                            <div class="applicant-lookup-item">
                                <div class="applicant-lookup-card">
                                    <div class="applicant-lookup-icon">
                                        DOB
                                    </div>
                                    <div class="applicant-lookup-content">
                                        <p class="applicant-lookup-label">Date of Birth</p>
                                        <p class="applicant-lookup-value" id="applicantLookupDob">-</p>
                                    </div>
                                </div>
                            </div>
                            <div class="applicant-lookup-item">
                                <div class="applicant-lookup-card">
                                    <div class="applicant-lookup-icon">
                                        E
                                    </div>
                                    <div class="applicant-lookup-content">
                                        <p class="applicant-lookup-label">Email</p>
                                        <p class="applicant-lookup-value" id="applicantLookupEmail">-</p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    {{-- Medical Condition Section --}}
                    <div class="applicant-medical-condition-section">
                        <section class="applicant-screening-panel applicant-review-panel" id="applicantNurseReviewPanel">
                            <h4 class="applicant-screening-panel-title">Nurse Findings Review</h4>
                            <div class="applicant-lookup-grid applicant-review-source-grid">
                                <div class="applicant-lookup-item">
                                    <div class="applicant-lookup-card">
                                        <div class="applicant-lookup-icon is-maroon">MC</div>
                                        <div class="applicant-lookup-content">
                                            <p class="applicant-lookup-label">Medical Certificate Result</p>
                                            <p class="applicant-lookup-value" id="applicantLookupMedCertResult">N/A</p>
                                        </div>
                                    </div>
                                </div>
                                <div class="applicant-lookup-item">
                                    <div class="applicant-lookup-card">
                                        <div class="applicant-lookup-icon is-maroon">FD</div>
                                        <div class="applicant-lookup-content">
                                            <p class="applicant-lookup-label">Student Declared Findings</p>
                                            <p class="applicant-lookup-value" id="applicantLookupMedCertDetails">N/A</p>
                                        </div>
                                    </div>
                                </div>
                                <div class="applicant-lookup-item">
                                    <div class="applicant-lookup-card">
                                        <div class="applicant-lookup-icon is-maroon">XR</div>
                                        <div class="applicant-lookup-content">
                                            <p class="applicant-lookup-label">Chest X-ray Result</p>
                                            <p class="applicant-lookup-value" id="applicantLookupXrayResult">N/A</p>
                                        </div>
                                    </div>
                                </div>
                                <div class="applicant-lookup-item">
                                    <div class="applicant-lookup-card">
                                        <div class="applicant-lookup-icon is-maroon">XF</div>
                                        <div class="applicant-lookup-content">
                                            <p class="applicant-lookup-label">Student Declared X-ray Findings</p>
                                            <p class="applicant-lookup-value" id="applicantLookupXrayDetails">N/A</p>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="applicant-findings-review">
                                <div class="applicant-findings-label">Review Result <span style="color:#dc2626;">*</span></div>
                                <div class="applicant-findings-options">
                                    <label class="applicant-findings-option">
                                        <input type="radio" name="applicant_findings_status" value="No Findings / Normal">
                                        <span>No Findings / Normal</span>
                                    </label>
                                    <label class="applicant-findings-option">
                                        <input type="radio" name="applicant_findings_status" value="With Findings">
                                        <span>With Findings</span>
                                    </label>
                                </div>
                            </div>
                            <div id="applicantClearanceDecisionFields" class="applicant-findings-review" style="display: none;">
                                <div class="applicant-findings-label">Clearance Decision <span style="color:#dc2626;">*</span></div>
                                <div class="applicant-findings-options">
                                    <label class="applicant-findings-option">
                                        <input type="radio" name="applicant_clearance_decision" value="approve">
                                        <span>Approve / Issue</span>
                                    </label>
                                    <label class="applicant-findings-option">
                                        <input type="radio" name="applicant_clearance_decision" value="pending">
                                        <span>Pending</span>
                                    </label>
                                </div>
                            </div>
                            <div id="applicantNormalRemarksFields" class="applicant-condition-fields" style="display: none;">
                                <div class="applicant-condition-field">
                                    <label for="applicantNormalRemarks">Remarks <span style="color: #94a3b8;">(Optional)</span></label>
                                    <textarea id="applicantNormalRemarks" name="normal_remarks" placeholder="Optional notes for a normal review..." class="applicant-condition-textarea" rows="3"></textarea>
                                </div>
                            </div>
                            <div id="applicantConditionFields" class="applicant-condition-fields" style="display: none;">
                                <div class="applicant-condition-field">
                                    <label>Finding Details</label>
                                    <div class="applicant-pending-reasons">
                                        <label class="applicant-pending-reason-option">
                                            <input type="checkbox" id="applicantHasMedicalCondition" name="has_medical_condition" value="1">
                                            <span>With Medical Condition</span>
                                        </label>
                                    </div>
                                </div>
                                <div id="applicantMedicalConditionField" class="applicant-condition-field" style="display: none;">
                                    <label for="applicantMedicalCondition">Medical Condition <span style="color: #dc2626;">*</span></label>
                                    <input type="text" id="applicantMedicalCondition" name="medical_condition" placeholder="e.g., Asthma, Diabetes, Hypertension..." class="applicant-condition-input">
                                </div>
                                <div id="applicantFindingRemarksField" class="applicant-condition-field">
                                    <label for="applicantFindingRemarks">Remarks <span style="color: #94a3b8;">(Optional)</span></label>
                                    <textarea id="applicantFindingRemarks" name="med_assessment_remarks" placeholder="Additional notes about the findings..." class="applicant-condition-textarea" rows="3"></textarea>
                                </div>
                            </div>
                            <div id="applicantPendingDecisionFields" class="applicant-condition-fields" style="display: none;">
                                <div class="applicant-condition-field">
                                    <label>Pending Reason <span style="color: #dc2626;">*</span></label>
                                    <div class="applicant-pending-reasons">
                                        <label class="applicant-pending-reason-option">
                                            <input type="checkbox" id="applicantIncompleteRequirements" name="incomplete_requirements" value="1">
                                            <span>Request Document Resubmission</span>
                                        </label>
                                        <label class="applicant-pending-reason-option">
                                            <input type="checkbox" id="applicantNeedsPhysicianEvaluation" name="needs_physician_evaluation" value="1">
                                            <span>For Physician Evaluation</span>
                                        </label>
                                        <label class="applicant-pending-reason-option">
                                            <input type="checkbox" id="applicantNeedsFurtherEvaluation" name="needs_further_evaluation" value="1">
                                            <span>For Further Evaluation</span>
                                        </label>
                                        <label class="applicant-pending-reason-option">
                                            <input type="checkbox" id="applicantNeedsHealthFormCorrection" name="needs_health_form_correction" value="1">
                                            <span>Health Form Correction</span>
                                        </label>
                                        <label class="applicant-pending-reason-option">
                                            <input type="checkbox" id="applicantOtherPendingReason" name="other_pending_reason_selected" value="1">
                                            <span>Others</span>
                                        </label>
                                    </div>
                                </div>
                                <div id="applicantOtherPendingReasonField" class="applicant-condition-field" style="display: none;">
                                    <label for="applicantOtherPendingReasonText">Other Pending Reason <span style="color: #dc2626;">*</span></label>
                                    <input type="text" id="applicantOtherPendingReasonText" name="other_pending_reason" placeholder="Enter other pending reason..." class="applicant-condition-input">
                                </div>
                                <div class="applicant-condition-field">
                                    <label for="applicantConditionRemarks">Remarks <span style="color: #94a3b8;">(Optional)</span></label>
                                    <textarea id="applicantConditionRemarks" name="condition_remarks" placeholder="Additional notes about the pending decision..." class="applicant-condition-textarea" rows="3"></textarea>
                                </div>
                                <div id="applicantResubmissionDocsField" class="applicant-condition-field" style="display: none;">
                                    <label>Documents to Resubmit <span style="color: #dc2626;">*</span></label>
                                    <div class="applicant-pending-reasons">
                                        <label class="applicant-pending-reason-option">
                                            <input type="checkbox" name="resubmission_required_documents[]" value="student_photo">
                                            <span>2x2 Photo</span>
                                        </label>
                                        <label class="applicant-pending-reason-option">
                                            <input type="checkbox" name="resubmission_required_documents[]" value="health_declaration">
                                            <span>Health Declaration</span>
                                        </label>
                                        <label class="applicant-pending-reason-option">
                                            <input type="checkbox" name="resubmission_required_documents[]" value="medical_certificate">
                                            <span>Medical Certificate</span>
                                        </label>
                                        <label class="applicant-pending-reason-option">
                                            <input type="checkbox" name="resubmission_required_documents[]" value="chest_xray_result">
                                            <span>Chest X-ray Result</span>
                                        </label>
                                        <label class="applicant-pending-reason-option">
                                            <input type="checkbox" name="resubmission_required_documents[]" value="pwd_id_proof">
                                            <span>PWD ID Proof</span>
                                        </label>
                                    </div>
                                </div>
                            </div>
                        </section>

                        <section class="applicant-screening-panel employee-physical-assessment-panel">
                            <h4 class="applicant-screening-panel-title">PHYSICAL EXAMINATION</h4>
                            <div class="employee-physical-exam-template" aria-label="Physical examination">
                                <div class="employee-exam-section">
                                    <strong>Vital Signs:</strong>
                                    <div class="employee-exam-options">
                                        <label><input type="radio" name="employee_exam_distress" value="not_in_distress"> Not in Distress</label>
                                        <label><input type="radio" name="employee_exam_distress" value="in_distress"> In Distress</label>
                                    </div>
                                    <div class="employee-exam-two-col">
                                        <label><span>Height</span><input type="text" name="employee_exam_height" placeholder="e.g., 5'6&quot;"><span>ft.</span><span class="employee-exam-validation" id="employeeHeightValidation">Pending</span></label>
                                        <label><span>Weight</span><input type="text" name="employee_exam_weight" placeholder="e.g., 143"><span>lbs.</span><span class="employee-exam-validation" id="employeeWeightValidation">Pending</span></label>
                                        <label><span>BMI</span><input type="text" name="employee_exam_bmi" readonly aria-readonly="true"><span class="employee-bmi-category" id="employeeBmiCategory">Pending</span></label>
                                        <label><span>Blood Pressure</span><input type="text" name="employee_exam_bp" placeholder="e.g., 120/80"><span class="employee-exam-validation" id="employeeBpValidation">Pending</span></label>
                                        <label><span>Heart Rate</span><input type="text" name="employee_exam_hr" placeholder="e.g., 72"><span>/min</span><span class="employee-exam-validation" id="employeeHrValidation">Pending</span></label>
                                        <label><span>Respiratory Rate</span><input type="text" name="employee_exam_rr" placeholder="e.g., 18"><span>/min</span><span class="employee-exam-validation" id="employeeRrValidation">Pending</span></label>
                                        <label><span>Temperature</span><input type="text" name="employee_exam_temperature" placeholder="e.g., 36.5"><span>°C</span><span class="employee-exam-validation" id="employeeTemperatureValidation">Pending</span></label>
                                    </div>
                                </div>
                                <div class="employee-exam-section">
                                    <strong>Head:</strong>
                                    <div class="employee-exam-options">
                                        <label><input type="checkbox" name="employee_exam_head[]" value="wound"> Wound</label>
                                        <label><input type="checkbox" name="employee_exam_head[]" value="mass"> Mass</label>
                                        <label><input type="checkbox" name="employee_exam_head[]" value="alopecia"> Alopecia</label>
                                    </div>
                                </div>
                                <div class="employee-exam-section">
                                    <strong>Eyes:</strong>
                                    <div class="employee-exam-options">
                                        <label><input type="checkbox" name="employee_exam_eyes[]" value="without_glasses"> w/o Glasses</label>
                                        <label><input type="checkbox" name="employee_exam_eyes[]" value="with_glasses"> w/ Glasses</label>
                                        <label><input type="checkbox" name="employee_exam_eyes[]" value="anicteric_sclera"> Anicteric Sclera</label>
                                        <label><input type="checkbox" name="employee_exam_eyes[]" value="pink_palpebral_conjunctiva"> Pink Palpebral Conjunctiva</label>
                                    </div>
                                </div>
                                <div class="employee-exam-section">
                                    <strong>Ears:</strong>
                                    <div class="employee-exam-options">
                                        <label><input type="checkbox" name="employee_exam_ears[]" value="no_gross_deformity"> No Gross Deformity</label>
                                        <label><input type="checkbox" name="employee_exam_ears[]" value="no_discharge"> No Discharge</label>
                                    </div>
                                </div>
                                <div class="employee-exam-section">
                                    <strong>Throat:</strong>
                                    <div class="employee-exam-options">
                                        <label><input type="checkbox" name="employee_exam_throat[]" value="no_tpc"> No TPC</label>
                                        <label><input type="checkbox" name="employee_exam_throat[]" value="no_mass"> No Mass</label>
                                        <label><input type="checkbox" name="employee_exam_throat[]" value="no_lymphadenopathy"> No lymphadenopathy</label>
                                    </div>
                                </div>
                                <div class="employee-exam-section">
                                    <strong>Chest/Lungs:</strong>
                                    <div class="employee-exam-options">
                                        <label><input type="radio" name="employee_exam_chest_lungs" value="normal"> Normal</label>
                                        <label><input type="radio" name="employee_exam_chest_lungs" value="wheeze"> Wheeze</label>
                                        <label><input type="radio" name="employee_exam_chest_lungs" value="rales"> Rales</label>
                                    </div>
                                </div>
                                <div class="employee-exam-section">
                                    <strong>Chest X-Ray Result:</strong>
                                    <div class="employee-exam-options">
                                        <label><input type="radio" name="employee_exam_chest_xray" value="normal"> Normal</label>
                                        <label><input type="radio" name="employee_exam_chest_xray" value="with_findings"> With findings</label>
                                        <label><input type="radio" name="employee_exam_chest_xray" value="na"> N/A</label>
                                    </div>
                                </div>
                                <div class="employee-exam-section">
                                    <strong>Breast:</strong>
                                    <div class="employee-exam-options">
                                        <label><input type="checkbox" name="employee_exam_breast[]" value="normal"> Normal</label>
                                    </div>
                                </div>
                                <div class="employee-exam-section">
                                    <strong>Heart:</strong>
                                    <div class="employee-exam-options">
                                        <span>Murmur:</span>
                                        <label><input type="radio" name="employee_exam_heart_murmur" value="present"> Present</label>
                                        <label><input type="radio" name="employee_exam_heart_murmur" value="absent"> Absent</label>
                                        <label><input type="radio" name="employee_exam_heart_murmur" value="na"> N/A</label>
                                        <span>Rhythm:</span>
                                        <label><input type="radio" name="employee_exam_heart_rhythm" value="regular"> Regular</label>
                                        <label><input type="radio" name="employee_exam_heart_rhythm" value="irregular"> Irregular</label>
                                        <label><input type="radio" name="employee_exam_heart_rhythm" value="na"> N/A</label>
                                    </div>
                                </div>
                                <div class="employee-exam-section">
                                    <strong>Abdomen:</strong>
                                    <div class="employee-exam-options">
                                        <label><input type="checkbox" name="employee_exam_abdomen[]" value="normal"> Normal</label>
                                    </div>
                                </div>
                                <div class="employee-exam-section">
                                    <strong>Genito-Urinary:</strong>
                                    <label class="employee-exam-line">1st day of last Menstruation <input type="date" name="employee_exam_last_menstruation"></label>
                                </div>
                                <div class="employee-exam-section">
                                    <strong>Extremities:</strong>
                                    <div class="employee-exam-options">
                                        <label><input type="checkbox" name="employee_exam_extremities[]" value="no_deformities"> No Deformities</label>
                                    </div>
                                </div>
                                <div class="employee-exam-section">
                                    <strong>Vertebral Column:</strong>
                                    <div class="employee-exam-options">
                                        <label><input type="radio" name="employee_exam_vertebral_column" value="normal"> Normal</label>
                                        <label><input type="radio" name="employee_exam_vertebral_column" value="with_deformity"> With Deformity</label>
                                    </div>
                                </div>
                                <div class="employee-exam-section">
                                    <strong>Skin:</strong>
                                    <div class="employee-exam-options">
                                        <label><input type="checkbox" name="employee_exam_skin[]" value="pallor"> Pallor</label>
                                        <label><input type="checkbox" name="employee_exam_skin[]" value="rashes"> Rashes</label>
                                        <label><input type="checkbox" name="employee_exam_skin[]" value="lesions"> Lesions</label>
                                    <strong>Scars:</strong>
                                        <label><input type="radio" name="employee_exam_scars" value="absent"> Absent</label>
                                        <label><input type="radio" name="employee_exam_scars" value="present"> Present</label>
                                    </div>
                                </div>
                                <div class="employee-exam-section">
                                    <strong>WORKING IMPRESSION:</strong>
                                    <label class="employee-exam-line"><strong>Working Impression:</strong> <input type="text" name="employee_exam_working_impression"></label>
                                    <label class="employee-exam-line"><strong>Fit:</strong>
                                        <select name="employee_exam_fit" id="employeeExamFit">
                                            <option value="">Select fit status</option>
                                            <option value="Fit for Work">Fit for Work</option>
                                            <option value="Fit for School">Fit for School</option>
                                            <option value="Fit for Duty">Fit for Duty</option>
                                            <option value="Fit for Internship">Fit for Internship</option>
                                            <option value="Fit for Clinic Duty">Fit for Clinic Duty</option>
                                            <option value="Others">Others</option>
                                        </select>
                                    </label>
                                    <label class="employee-exam-line" id="employeeExamFitOtherWrap" style="display:none;">Other Fit Status: <input type="text" name="employee_exam_fit_other" id="employeeExamFitOther"></label>
                                    <label class="employee-exam-line"><strong>For Work-Up:</strong> <input type="text" name="employee_exam_for_work_up"></label>
                                    <strong>Referred to:</strong>
                                    <div class="employee-exam-options">
                                        <label><input type="checkbox" name="employee_exam_referred_to[]" value="cardio"> Cardio</label>
                                        <label><input type="checkbox" name="employee_exam_referred_to[]" value="derma"> Derma</label>
                                        <label><input type="checkbox" name="employee_exam_referred_to[]" value="ent"> ENT</label>
                                        <label><input type="checkbox" name="employee_exam_referred_to[]" value="optha"> Optha</label>
                                        <label><input type="checkbox" name="employee_exam_referred_to[]" value="pulmo"> Pulmo</label>
                                        <label class="employee-exam-line">Others: <input type="text" name="employee_exam_referred_to_others"></label>
                                    </div>
                                    <label class="employee-exam-line"><strong>Follow up on:</strong> <input type="date" name="employee_exam_follow_up_on"></label>
                                </div>
                            </div>
                            <div class="applicant-vitals-grid">
                                <div class="applicant-condition-field">
                                    <label for="applicantHeight">Height <span style="color:#dc2626;">*</span></label>
                                    <input type="text" id="applicantHeight" name="height" class="applicant-condition-input vital-input" placeholder="e.g., 5'6&quot;" inputmode="text" required>
                                    <span class="vital-status is-muted" id="heightStatus">Pending</span>
                                    <small style="color:#dc2626; display:none;" id="heightError">Height must use feet and inches, e.g., 5'6&quot;. Valid range: 1'0&quot;-10'0&quot;.</small>
                                </div>
                                <div class="applicant-condition-field">
                                    <label for="applicantWeight">Weight (lbs) <span style="color:#dc2626;">*</span></label>
                                    <input type="number" id="applicantWeight" name="weight" class="applicant-condition-input vital-input" placeholder="e.g., 143" step="0.01" min="1" max="1100" required>
                                    <span class="vital-status is-muted" id="weightStatus">BMI pending</span>
                                    <small style="color:#dc2626; display:none;" id="weightError">Weight is required. Valid range: 1-1100 lbs.</small>
                                </div>
                                <div class="applicant-condition-field">
                                    <label for="applicantBloodPressure">Blood Pressure <span style="color:#dc2626;">*</span></label>
                                    <input type="text" id="applicantBloodPressure" name="blood_pressure" class="applicant-condition-input vital-input" placeholder="e.g., 120/80" inputmode="numeric" required>
                                    <span class="vital-status is-muted" id="bpStatus">Pending</span>
                                    <small style="color:#dc2626; display:none;" id="bpError">Blood pressure must be in format: ###/## (e.g., 120/80). Normal range: 90-120 systolic, 60-80 diastolic.</small>
                                </div>
                                <div class="applicant-condition-field">
                                    <label for="applicantPulseRate">Pulse Rate (bpm) <span style="color:#dc2626;">*</span></label>
                                    <input type="number" id="applicantPulseRate" name="pulse_rate" class="applicant-condition-input vital-input" placeholder="e.g., 72" min="1" max="300" required>
                                    <span class="vital-status is-muted" id="pulseStatus">Pending</span>
                                    <small style="color:#dc2626; display:none;" id="prError">Pulse rate is required. Normal range: 60-100 bpm.</small>
                                </div>
                                <div class="applicant-condition-field">
                                    <label for="applicantRespiratoryRate">Respiratory Rate (cpm) <span style="color:#dc2626;">*</span></label>
                                    <input type="number" id="applicantRespiratoryRate" name="respiratory_rate" class="applicant-condition-input vital-input" placeholder="e.g., 18" min="1" max="120" required>
                                    <span class="vital-status is-muted" id="respiratoryStatus">Pending</span>
                                    <small style="color:#dc2626; display:none;" id="rrError">Respiratory rate is required. Normal range: 12-20 cpm.</small>
                                </div>
                                <div class="applicant-condition-field">
                                    <label for="applicantTemperature">Temperature (&deg;C) <span style="color:#dc2626;">*</span></label>
                                    <input type="number" id="applicantTemperature" name="temperature" class="applicant-condition-input vital-input" placeholder="e.g., 36.5" min="30" max="45" step="0.1" required>
                                    <span class="vital-status is-muted" id="temperatureStatus">Pending</span>
                                    <small style="color:#dc2626; display:none;" id="tempError">Temperature is required. Normal range: 36.5-37.5°C. High fever if > 38.5°C.</small>
                                </div>
                                <div class="applicant-condition-field">
                                    <label>COVID Positive? <span style="color:#dc2626;">*</span></label>
                                    <div class="applicant-findings-options">
                                        <label class="applicant-findings-option">
                                            <input type="radio" name="applicant_covid_positive" value="No" required>
                                            <span>No</span>
                                        </label>
                                        <label class="applicant-findings-option">
                                            <input type="radio" name="applicant_covid_positive" value="Yes" required>
                                            <span>Yes</span>
                                        </label>
                                    </div>
                                    <span class="vital-status is-muted" id="covidStatus">Pending</span>
                                </div>
                                <div class="applicant-condition-field" id="applicantCovidDateField" style="display:none;">
                                    <label for="applicantCovidPositiveDate">COVID Positive Date <span style="color:#dc2626;">*</span></label>
                                    <input type="date" id="applicantCovidPositiveDate" name="covid_positive_date" class="applicant-condition-input">
                                    <small style="color:#dc2626; display:none;" id="covidDateError">Date cannot be in the future. Please select today or an earlier date.</small>
                                </div>
                                <div class="applicant-condition-field is-full" id="applicantEncodeRemarksField">
                                    <label for="applicantEncodeRemarks">Assessment Remarks <span style="color:#94a3b8;">(Optional)</span></label>
                                    <textarea id="applicantEncodeRemarks" name="encode_remarks" placeholder="Optional assessment notes from the encoding station..." class="applicant-condition-textarea" rows="3"></textarea>
                                </div>
                            </div>
                            <div class="bmi-gauge-card" id="bmiGaugeCard" aria-live="polite">
                                <div class="bmi-gauge-summary">
                                    <strong id="bmiGaugeTitle">BMI pending</strong>
                                    <span id="bmiGaugeCategory">Enter height and weight</span>
                                </div>
                                <div class="bmi-gauge-layout">
                                    <div class="bmi-gauge-meter" aria-hidden="true">
                                        <svg class="bmi-gauge-svg" viewBox="0 0 420 245" role="img" aria-label="BMI category gauge">
                                            <path class="bmi-gauge-segment is-low" d="M 60 205 A 150 150 0 0 1 70.7 149.3 L 121.8 169.7 A 95 95 0 0 0 115 205 Z" />
                                            <path class="bmi-gauge-segment is-under" d="M 70.7 149.3 A 150 150 0 0 1 87.8 118 L 132.6 149.9 A 95 95 0 0 0 121.8 169.7 Z" />
                                            <path class="bmi-gauge-segment is-normal" d="M 87.8 118 A 150 150 0 0 1 160.9 63.2 L 178.9 115.2 A 95 95 0 0 0 132.6 149.9 Z" />
                                            <path class="bmi-gauge-segment is-over" d="M 160.9 63.2 A 150 150 0 0 1 231.3 56.5 L 223.5 111 A 95 95 0 0 0 178.9 115.2 Z" />
                                            <path class="bmi-gauge-segment is-obese-start" d="M 231.3 56.5 A 150 150 0 0 1 297 82.8 L 265.1 127.6 A 95 95 0 0 0 223.5 111 Z" />
                                            <path class="bmi-gauge-segment is-obese" d="M 297 82.8 A 150 150 0 0 1 360 205 L 305 205 A 95 95 0 0 0 265.1 127.6 Z" />
                                            <text class="bmi-gauge-svg-label" x="82" y="166" transform="rotate(-70 82 166)">Underweight</text>
                                            <text class="bmi-gauge-svg-label" x="129" y="118" transform="rotate(-48 129 118)">Normal</text>
                                            <text class="bmi-gauge-svg-label" x="210" y="76" transform="rotate(-8 210 76)">Overweight</text>
                                            <text class="bmi-gauge-svg-label" x="326" y="132" transform="rotate(48 326 132)">Obese</text>
                                        </svg>
                                        <div class="bmi-gauge-needle" id="bmiGaugeNeedle"></div>
                                        <div class="bmi-gauge-pivot"></div>
                                        <div class="bmi-gauge-value" id="bmiGaugeValue">BMI --</div>
                                    </div>
                                    <ul class="bmi-gauge-details">
                                        <li id="bmiHealthyRange">Healthy BMI range: 18.5 kg/m2 - 25 kg/m2</li>
                                        <li id="bmiHealthyWeight">Healthy weight for this height: --</li>
                                        <li id="bmiWeightGoal">Enter height and weight to calculate BMI.</li>
                                        <li id="bmiPrime">BMI Prime: --</li>
                                        <li id="bmiPonderal">Ponderal Index: --</li>
                                    </ul>
                                </div>
                            </div>
                        </section>
                    </div>

                    <div class="applicant-ref-actions">
                        <button type="button" id="btnCancelApplicantRef" class="applicant-ref-action-btn applicant-ref-cancel-btn">Cancel</button>
                        <button type="button" id="btnSaveReviewDraft" class="applicant-ref-action-btn applicant-ref-draft-btn" aria-label="Save review draft">
                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" aria-hidden="true">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 14.25v-2.625a3.375 3.375 0 0 0-3.375-3.375h-1.5A1.125 1.125 0 0 1 13.5 7.125v-1.5a3.375 3.375 0 0 0-3.375-3.375H8.25m2.25 0H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 0 0-9-9Z" />
                            </svg>
                            <span>Draft</span>
                        </button>
                        <button type="button" id="btnFindApplicant" class="applicant-ref-action-btn applicant-ref-find-btn">Find</button>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="applicant-modal-backdrop" id="applicantHealthInfoModal" aria-hidden="true">
        <div class="applicant-modal-shell applicant-health-info-modal">
            <div class="applicant-modal-head">
                <div class="applicant-modal-head-main">
                    <div class="applicant-modal-head-badge">HF</div>
                    <div class="applicant-modal-head-copy">
                        <h3>Health Information Form</h3>
                        <p>Complete health form information for the selected record.</p>
                    </div>
                </div>
                <button type="button" class="applicant-modal-close" id="closeApplicantHealthInfoModal" aria-label="Close health information form">
                    <x-outline-icon name="x-mark" />
                </button>
            </div>
            <div class="applicant-modal-body applicant-health-info-modal-body">
                <div id="applicantInformationDetails" class="applicant-information-details is-visible" aria-hidden="false">
                    <div class="health-info-editor" id="healthInfoEditor">
                        <aside class="health-info-tabs" id="healthInfoTabs"></aside>
                        <section class="health-info-content">
                            <div class="health-info-header">
                                <div>
                                    <p class="health-info-kicker">Health Form</p>
                                    <h4 id="healthInfoSectionTitle">Health Information Form</h4>
                                </div>
                                <div class="health-info-actions">
                                    <button type="button" id="healthInfoEditBtn" class="health-info-edit-btn">
                                        <x-outline-icon name="pencil-square" />
                                        <span>Edit</span>
                                    </button>
                                    <button type="button" id="healthInfoCancelBtn" class="health-info-cancel-btn" style="display: none;">Cancel</button>
                                    <button type="button" id="healthInfoSaveBtn" class="health-info-save-btn" style="display: none;">Save</button>
                                </div>
                            </div>
                            <div class="health-info-fields" id="healthInfoFields"></div>
                        </section>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="applicant-modal-backdrop" id="applicantMedicalConditionModal" aria-hidden="true">
        <div class="applicant-modal-shell applicant-medical-condition-modal">
            <div class="applicant-modal-head">
                <div class="applicant-modal-head-main">
                    <div class="applicant-modal-head-badge">MC</div>
                    <div class="applicant-modal-head-copy">
                        <h3>Medical Condition</h3>
                        <p>Condition summary, pending reasons, and remarks for the selected record.</p>
                    </div>
                </div>
                <button type="button" class="applicant-modal-close" id="closeApplicantMedicalConditionModal" aria-label="Close medical condition">
                    <x-outline-icon name="x-mark" />
                </button>
            </div>
            <div class="applicant-modal-body applicant-medical-condition-modal-body">
                <div id="applicantMedicalConditionDetails" class="applicant-medical-condition-details is-visible" aria-hidden="false">
                    <div class="applicant-lookup-grid">
                        <div class="applicant-lookup-item">
                            <div class="applicant-lookup-card">
                                <div class="applicant-lookup-icon is-maroon">MC</div>
                                <div class="applicant-lookup-content">
                                    <p class="applicant-lookup-label">Condition</p>
                                    <p class="applicant-lookup-value" id="applicantViewConditionStatus">No Medical Condition</p>
                                </div>
                            </div>
                        </div>
                        <div class="applicant-lookup-item">
                            <div class="applicant-lookup-card">
                                <div class="applicant-lookup-icon is-maroon">DX</div>
                                <div class="applicant-lookup-content">
                                    <p class="applicant-lookup-label">Medical Condition</p>
                                    <p class="applicant-lookup-value" id="applicantViewMedicalCondition">N/A</p>
                                </div>
                            </div>
                        </div>
                        <div class="applicant-lookup-item">
                            <div class="applicant-lookup-card">
                                <div class="applicant-lookup-icon is-maroon">PR</div>
                                <div class="applicant-lookup-content">
                                    <p class="applicant-lookup-label">Pending Reasons</p>
                                    <p class="applicant-lookup-value" id="applicantViewConditionReasons">N/A</p>
                                </div>
                            </div>
                        </div>
                        <div class="applicant-lookup-item">
                            <div class="applicant-lookup-card">
                                <div class="applicant-lookup-icon is-maroon">RM</div>
                                <div class="applicant-lookup-content">
                                    <p class="applicant-lookup-label">Remarks</p>
                                    <p class="applicant-lookup-value" id="applicantViewConditionRemarks">N/A</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="applicant-modal-backdrop" id="savedAssessmentModal" aria-hidden="true">
        <div class="applicant-modal-shell saved-review-modal">
            <div class="applicant-modal-head">
                <div class="applicant-modal-head-main">
                    <div class="applicant-modal-head-badge">RV</div>
                    <div class="applicant-modal-head-copy">
                        <h3>Saved Nurse Review</h3>
                        <p>Previously recorded findings and physical assessment.</p>
                    </div>
                </div>
                <button type="button" class="applicant-modal-close" id="closeSavedAssessmentModal" aria-label="Close saved nurse review">
                    <x-outline-icon name="x-mark" />
                </button>
            </div>
            <div class="saved-review-body">
                <div class="saved-review-grid">
                    <div class="saved-review-card"><span>Review Result</span><strong id="savedReviewResult">-</strong></div>
                    <div class="saved-review-card"><span>Pending Reasons</span><strong id="savedReviewReasons">-</strong></div>
                    <div class="saved-review-card"><span>Medical Condition</span><strong id="savedReviewCondition">-</strong></div>
                    <div class="saved-review-card"><span>Remarks</span><strong id="savedReviewRemarks">-</strong></div>
                    <div class="saved-review-card"><span>Height</span><strong id="savedReviewHeight">-</strong></div>
                    <div class="saved-review-card"><span>Weight</span><strong id="savedReviewWeight">-</strong></div>
                    <div class="saved-review-card"><span>Blood Pressure</span><strong id="savedReviewBloodPressure">-</strong></div>
                    <div class="saved-review-card"><span>Pulse Rate</span><strong id="savedReviewPulseRate">-</strong></div>
                    <div class="saved-review-card"><span>Respiratory Rate</span><strong id="savedReviewRespiratoryRate">-</strong></div>
                    <div class="saved-review-card"><span>Temperature</span><strong id="savedReviewTemperature">-</strong></div>
                    <div class="saved-review-card"><span>COVID Positive</span><strong id="savedReviewCovidPositive">-</strong></div>
                    <div class="saved-review-card"><span>COVID Positive Date</span><strong id="savedReviewCovidPositiveDate">-</strong></div>
                    <div class="saved-review-card"><span>Reference Number</span><strong id="savedReviewReference">-</strong></div>
                </div>
            </div>
        </div>
    </div>

    <div class="clinic-success-overlay" id="applicantApprovalOverlay" aria-live="assertive" aria-hidden="true">
        <div class="clinic-success-card">
            <button type="button" class="clinic-success-close" data-success-close aria-label="Close success message">
                <x-outline-icon name="x-mark" />
            </button>
            <span class="clinic-success-confetti" aria-hidden="true"></span>
            <div class="clinic-success-check" aria-hidden="true">
                <span class="clinic-success-ring"></span>
                <x-outline-icon name="check" />
            </div>
            <strong id="applicantApprovalOverlayTitle">Approved!</strong>
            <p id="applicantApprovalOverlayMessage">The applicant decision has been saved successfully.</p>
            <hr>
            <button type="button" class="clinic-success-continue" data-success-close>Continue</button>
        </div>
    </div>

    <div class="clinic-success-overlay" id="applicantEncodeOverlay" aria-live="assertive" aria-hidden="true">
        <div class="clinic-success-card">
            <button type="button" class="clinic-success-close" data-success-close aria-label="Close success message">
                <x-outline-icon name="x-mark" />
            </button>
            <span class="clinic-success-confetti" aria-hidden="true"></span>
            <div class="clinic-success-check" aria-hidden="true">
                <span class="clinic-success-ring"></span>
                <x-outline-icon name="check" />
            </div>
            <strong>Done Encoding</strong>
            <p>The data has been successfully encoded and saved in the system.</p>
            <hr>
            <button type="button" class="clinic-success-continue" data-success-close>OK, Continue</button>
        </div>
    </div>

    <div class="applicant-modal-backdrop" id="applicantDocumentsModal">
        <div class="applicant-modal-shell applicant-documents-modal">
            <div class="applicant-modal-head">
                <div class="applicant-modal-head-main">
                    <div class="applicant-modal-head-badge">FILE</div>
                    <div class="applicant-modal-head-copy">
                        <h3>Uploaded Documents</h3>
                        <p id="applicantDocumentsModalSubtitle">View the applicant's submitted clinic requirements and Health Information Form.</p>
                    </div>
                </div>
                <button type="button" class="applicant-modal-close" id="closeApplicantDocumentsModal" aria-label="Close uploaded documents">
                    <x-outline-icon name="x-mark" />
                </button>
            </div>
            <div class="applicant-modal-body">
                <div class="applicant-documents-workspace">
                    <aside class="applicant-documents-sidebar" aria-label="Uploaded document list">
                        <div class="applicant-documents-grid" id="applicantDocumentsGrid">
                            <div class="applicant-documents-empty" id="applicantDocumentsInitialEmpty">No uploaded documents are available for this applicant.</div>
                        </div>
                    </aside>
                    <section class="applicant-document-preview-panel" id="applicantDocumentPreviewPanel" aria-live="polite">
                        <div class="applicant-document-preview-head">
                            <strong id="applicantDocumentPreviewTitle">Document Preview</strong>
                            <a class="applicant-document-preview-close" id="applicantDocumentPreviewOpen" href="#" target="_blank" rel="noopener noreferrer" style="display:none;">Open in New Tab</a>
                        </div>
                        <div class="applicant-document-preview-empty" id="applicantDocumentPreviewEmpty">Select a file from the left to display its full-page preview.</div>
                        <iframe class="applicant-document-preview-frame" id="applicantDocumentPreviewFrame" title="Clinic document preview" style="display:none;"></iframe>
                        <img class="applicant-document-preview-image" id="applicantDocumentPreviewImage" src="" alt="">
                    </section>
                </div>
            </div>
        </div>
    </div>

    {{-- OCR / Manual ID shared modal (used by OCR/Manual ID card and legacy assessment flow) --}}
    <div class="applicant-modal-backdrop" id="applicantScanModal">
        <div class="applicant-modal-shell">
            <div class="applicant-modal-head">
                <div class="applicant-modal-head-main">
                    <div id="headerIcon" class="applicant-modal-head-badge">AP</div>
                    <div class="applicant-modal-head-copy">
                        <h3 id="headerTitle">OCR Ready</h3>
                        <span id="scanMethodBadge" class="scan-method-badge">OCR Active</span>
                        <p id="headerSubtitle">Start with OCR ID scanning, or use manual student number entry when the card cannot be captured clearly.</p>
                    </div>
                </div>
                <div class="applicant-modal-head-actions" style="display:none;">
                    <button type="button" id="btnSwitchScanMode" class="btn-scan-switch">
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" aria-hidden="true">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M7.5 21 3 16.5m0 0L7.5 12M3 16.5h13.5m0-13.5L21 7.5m0 0L16.5 12M21 7.5H7.5" />
                        </svg>
                        <span>OCR Scan Active</span>
                    </button>
                </div>
                <button type="button" class="applicant-modal-close" id="closeApplicantScanModal" aria-label="Close applicant scan modal">
                    <x-outline-icon name="x-mark" />
                </button>
            </div>
            <div class="applicant-modal-body">
                <div class="applicant-modal-grid">
                    {{-- Left Column: Scanner (No Frame) --}}
                    <div id="scanForm" style="display: contents;">
                        <div id="scanStage" class="scan-stage">
                            <div id="scanner-container-scan" class="scan-surface" style="position:relative;">
                                <p id="scanInlineNote" class="scan-inline-note">OCR mode is active. Align the physical ID inside the frame and continue once the ID number and name are matched locally.</p>
                                <div id="barcodeScanPanel">
                                    <div id="scan-loading">
                                        <div class="spinner"></div>
                                        <p style="margin-top:10px;color:#8B0000;font-weight:bold;font-size:12px;">Verifying...</p>
                                    </div>
                                    <div class="ocr-camera-shell">
                                        <div id="readerScan" class="scanner-box">
                                            <div class="scan-line-overlay"></div>
                                            <div class="ocr-guide"></div>
                                            <div class="ocr-guide-label">Align Student Number and Name</div>
                                        </div>
                                        <div id="ocrCameraIdle" class="ocr-camera-idle">Camera is Closed. Select Start Camera when you are ready to scan the student ID.</div>
                                    </div>
                                    <div class="ocr-camera-controls">
                                        <button type="button" id="btnStartOcrCamera" class="btn-ocr btn-ocr-primary">Start Camera</button>
                                        <button type="button" id="btnCloseOcrCamera" class="btn-ocr btn-ocr-secondary" disabled>Close Camera</button>
                                    </div>
                                    <div class="ocr-actions" id="ocrScanActions" style="display:none;">
                                        <button type="button" id="btnRunAiOcr" class="btn-ocr btn-ocr-primary" style="background:linear-gradient(135deg,#1d4ed8,#2563eb 55%,#3b82f6);box-shadow:0 12px 24px rgba(37,99,235,0.22);">Reading ID Number</button>
                                        <button type="button" id="btnRetryOcr" class="btn-ocr btn-ocr-secondary">Clear OCR Result</button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    {{-- Right Column: Detected Data & Manual Entry --}}
                    <div class="applicant-modal-panel">
                        <div id="applicantOcrReviewPanel">
                            <div id="applicantOcrDetectedContent" style="display:none;">
                            <div style="display:flex;align-items:flex-start;justify-content:space-between;gap:14px;margin-bottom:16px;padding-bottom:12px;border-bottom:1px solid #e2e8f0;">
                                <div style="display:flex;align-items:flex-start;gap:12px;">
                                    <span style="width:42px;height:42px;border-radius:14px;display:inline-flex;align-items:center;justify-content:center;background:linear-gradient(135deg,#7f1d1d,#991b1b 58%,#b91c1c);color:#ffffff;box-shadow:0 12px 24px rgba(127,29,29,0.18);flex-shrink:0;">
                                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" style="width:20px;height:20px;stroke:currentColor;stroke-width:1.9;">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M3.75 7.5h16.5m-16.5 4.5h10.5m-10.5 4.5h7.5M17.25 5.25h2.25A1.5 1.5 0 0 1 21 6.75v10.5a1.5 1.5 0 0 1-1.5 1.5h-15A1.5 1.5 0 0 1 3 17.25V6.75a1.5 1.5 0 0 1 1.5-1.5h2.25"/>
                                        </svg>
                                    </span>
                                    <div>
                                        <p style="margin:0;font-size:11px;font-weight:900;letter-spacing:0.08em;text-transform:uppercase;color:#8b0000;">Detected Data</p>
                                        <p style="margin:4px 0 0;font-size:12px;color:#64748b;line-height:1.5;">Captured from OCR scan and arranged for final review.</p>
                                    </div>
                                </div>
                                <span style="display:inline-flex;align-items:center;padding:7px 10px;border-radius:999px;background:#ecfdf5;border:1px solid #a7f3d0;color:#047857;font-size:11px;font-weight:800;letter-spacing:0.04em;text-transform:uppercase;white-space:nowrap;">OCR Result</span>
                            </div>
                            <div style="display:grid;gap:12px;">
                                <div style="display:grid;grid-template-columns:120px minmax(0,1fr);gap:12px;align-items:center;padding:12px 14px;border-radius:14px;background:linear-gradient(180deg,#fdfefe,#f8fafc);border:1px solid #e2e8f0;">
                                    <p class="ocr-result-label" style="margin:0;color:#334155;">Full Name</p>
                                    <input type="text" id="ocr_student_name" class="form-control" readonly style="margin-bottom:0;background:#ffffff;color:#0f172a;border:1px solid #cbd5e1;box-shadow:inset 0 1px 0 rgba(255,255,255,0.95);font-weight:700;cursor:default;">
                                </div>
                                <div style="display:grid;grid-template-columns:120px minmax(0,1fr);gap:12px;align-items:center;padding:12px 14px;border-radius:14px;background:linear-gradient(180deg,#fdfefe,#f8fafc);border:1px solid #e2e8f0;">
                                    <p class="ocr-result-label" style="margin:0;color:#334155;">ID Number</p>
                                    <input type="text" id="ocr_student_number" class="form-control" readonly style="margin-bottom:0;background:#ffffff;color:#0f172a;border:1px solid #cbd5e1;box-shadow:inset 0 1px 0 rgba(255,255,255,0.95);font-weight:700;cursor:default;">
                                </div>
                            </div>
                            <div id="ocrStatus" class="ocr-status info" style="display:block;">AI verification could not finish right now.</div>
                            <div style="display:flex;align-items:center;gap:8px;flex-wrap:wrap;">
                                <div id="ocrConfidenceText" class="ocr-meta">ID number confidence: 10%</div>
                                <div id="ocrLockBadge" class="ocr-lock-badge" style="display:none;">Locked on ID</div>
                            </div>
                            <div class="ocr-actions" style="margin-top:14px;">
                                <button type="button" id="btnConfirmOcr" class="btn-ocr btn-ocr-secondary" disabled>Confirm &amp; Continue</button>
                            </div>
                            </div>
                            <div class="manual-input-stack">
                                <p class="manual-toggle-label">Alternative Lookup</p>
                                <h4 class="manual-lookup-title">Employee's / Student Number Lookup</h4>
                                <p class="manual-lookup-copy">Use the employee number or student number saved in local clinic records when OCR cannot read the physical ID clearly.</p>
                                <form id="walkinFormManual" class="manual-lookup-form">
                                    <input type="text" id="student_id_manual" placeholder="Enter employee or student number" class="form-control" required>
                                    <button type="submit" id="manualFindBtn" class="manual-find-btn" disabled>Find Record</button>
                                </form>
                                <div id="manualLookupStatus" class="manual-lookup-status" role="status" aria-live="polite"></div>
                            </div>
                        </div>
                        <canvas id="ocrCanvas" style="display:none;"></canvas>
                    </div>
                </div>
            </div>
        </div>
    </div>
    @endif

@if($currentMode === 'registration')
<div class="card p-4 shadow-sm walkin-strip-card registration-hub" style="border-radius: 16px; border: none; margin: 20px auto;">
    <div class="registration-head">
        <div class="registration-head-main">
            <div class="registration-head-copy">
                <p class="registration-kicker">Patient Intake</p>
                <h3>Registration Options</h3>
                <p>Choose an onboarding path for applicant registration and identity setup.</p>
            </div>
            <div class="registration-actions">
                <a href="{{ url($basePrefix . '/walkin') }}" class="btn">Back to Intake Options</a>
                <a href="{{ url($basePrefix . '/appointments') }}" class="btn">Back to Appointments</a>
            </div>
        </div>
    </div>

    <div class="um-mode-picker registration-mode-picker">
        <a href="{{ $idpRegistrationLink }}" target="_blank" rel="noopener noreferrer" class="um-mode-btn registration-mode-btn">
            <span class="um-mode-icon" aria-hidden="true">
                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" aria-hidden="true">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M15 3h6m0 0v6m0-6-7.5 7.5M9 6H6.75A2.25 2.25 0 0 0 4.5 8.25v9A2.25 2.25 0 0 0 6.75 19.5h9A2.25 2.25 0 0 0 18 17.25V15" />
                </svg>
            </span>
            <h3>Register via IDP</h3>
            <p>Open the official Identity Provider registration form and complete applicant account enrollment.</p>
        </a>

        <a href="{{ url()->current() }}?mode=assisted" class="um-mode-btn registration-mode-btn">
            <span class="um-mode-icon" aria-hidden="true">
                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" aria-hidden="true">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 6.75h3A2.25 2.25 0 0 1 21 9v7.5A2.25 2.25 0 0 1 18.75 18.75H5.25A2.25 2.25 0 0 1 3 16.5V9a2.25 2.25 0 0 1 2.25-2.25h3m1.5 0a2.25 2.25 0 0 1 4.5 0m-4.5 0h4.5m-6 5.25h6m-6 3h3.75" />
                </svg>
            </span>
            <h3>Manual Registration</h3>
            <p>Capture the patient record on their behalf when illness or urgency makes self-registration impractical.</p>
        </a>
    </div>
</div>
@endif



@if(in_array($currentMode, ['scan', 'assisted', 'applicant'], true))
<div class="{{ $currentMode === 'assisted' ? '' : 'card p-4 shadow-sm walkin-strip-card' }}" style="{{ $currentMode === 'assisted' ? 'max-width: 1180px; margin: 20px auto; padding: 0; background: transparent; box-shadow: none; border: none;' : 'border-radius: 15px; border: none; max-width: 550px; margin: 20px auto;' }}">
    
    @if($currentMode !== 'assisted')
    <div id="dynamicHeader" class="mode-header bg-scan">
        <div id="headerIcon" class="mode-header-badge">{{ $currentMode === 'applicant' ? 'AP' : 'SB' }}</div>
        <div class="mode-header-copy">
            <h3 id="headerTitle" style="margin: 0; font-weight: 800; text-transform: uppercase; font-size: 1rem; letter-spacing: 1px;">
                {{ $currentMode === 'applicant' ? 'Applicant Scan Ready' : 'OCR Ready' }}
            </h3>
            <p id="headerSubtitle">
                {{ $currentMode === 'applicant' ? 'Choose OCR scanning or manual ID entry to identify the applicant record.' : 'Choose OCR scanning or manual ID entry to identify the patient.' }}
            </p>
        </div>
    </div>
    @endif

    <div id="scanForm" style="{{ $currentMode === 'assisted' ? 'display:none;' : '' }}">
        <div id="scanStage" class="scan-stage">
            <div class="scan-method-bar">
                <div>
                <p id="scanMethodTitle" class="scan-method-title">OCR ID Scan</p>
                <p id="scanMethodNote" class="scan-method-note">Use the camera to extract the student number from the physical ID card, or enter it manually.</p>
                <span id="scanMethodBadge" class="scan-method-badge">OCR Active</span>
                </div>
                <button type="button" id="btnSwitchScanMode" class="btn-scan-switch" style="display:none;">OCR Scan Active</button>
            </div>

            <div id="scanner-container-scan" class="scan-surface" style="position: relative;">
                <p id="scanInlineNote" class="scan-inline-note">OCR mode is active. Align the physical ID inside the frame, or type the student number manually.</p>
                <div id="barcodeScanPanel">
                    <div id="scan-loading">
                        <div class="spinner"></div>
                        <p style="margin-top:10px; color:#8B0000; font-weight:bold; font-size: 12px;">Verifying...</p>
                    </div>
                    <div id="readerScan" class="scanner-box">
                        <div class="scan-line-overlay"></div>
                        <div class="ocr-guide"></div>
                        <div class="ocr-guide-label">Align Student Number and Name</div>
                    </div>

                    <div class="ocr-actions">
                        <button type="button" id="btnRunAiOcr" class="btn-ocr btn-ocr-primary" style="background:linear-gradient(135deg, #1d4ed8, #2563eb 55%, #3b82f6); box-shadow:0 12px 24px rgba(37,99,235,0.22);">Reading ID Number</button>
                        <button type="button" id="btnRetryOcr" class="btn-ocr btn-ocr-secondary">Clear OCR Result</button>
                    </div>

                    <div id="ocrResultPanel" class="ocr-result-panel" style="display:none;">
                        <div style="display:grid; gap:12px;">
                            <div style="display:grid; grid-template-columns: 120px minmax(0, 1fr); gap:12px; align-items:center;">
                                <p class="ocr-result-label" style="margin:0;">Full Name</p>
                                <input type="text" id="ocr_student_name" class="form-control" placeholder="Enter full name" style="margin-bottom:0;">
                            </div>
                            <div style="display:grid; grid-template-columns: 120px minmax(0, 1fr); gap:12px; align-items:center;">
                                <p class="ocr-result-label" style="margin:0;">ID Number</p>
                                <input type="text" id="ocr_student_number" class="form-control" placeholder="Enter ID number" style="margin-bottom:0;">
                            </div>
                        </div>

                        <div id="ocrStatus" class="ocr-status info" style="display:block;">Live OCR is ready. Hold the ID steady inside the frame so we can detect the student number and fill the saved name from records.</div>
                        <div style="display:flex; align-items:center; gap:8px; flex-wrap:wrap;">
                            <div id="ocrConfidenceText" class="ocr-meta">OCR confidence will appear here after analysis.</div>
                            <div id="ocrLockBadge" class="ocr-lock-badge" style="display:none;">Locked on ID</div>
                        </div>

                        <div class="ocr-actions" style="margin-top:14px;">
                            <button type="button" id="btnConfirmOcr" class="btn-ocr btn-ocr-secondary" disabled>Confirm & Continue</button>
                        </div>
                    </div>

                    <canvas id="ocrCanvas" style="display:none;"></canvas>
                </div>

            </div>
        
            <div class="text-center mt-3">
                <button type="button" id="btnShowManual" style="background:none; border:none; color:#8B0000; text-decoration:underline; cursor:pointer; font-weight:600; font-size: 0.85rem;">
                    Type Student Number Manually
                </button>
            </div>

            <div id="manualInputArea" style="display:none;" class="mt-3">
                <form id="walkinFormManual" class="d-flex gap-2">
                    <input type="text" id="student_id_manual" placeholder="Enter employee or student number" class="form-control" style="margin-bottom:0;" required>
                    <button type="submit" id="manualFindBtn" class="manual-find-btn" disabled>Find Record</button>
                </form>
                <div id="manualLookupStatus" class="manual-lookup-status" role="status" aria-live="polite"></div>
            </div>

            <div class="mt-4 pt-3" style="border-top: 1px dashed #cbd5e1;">
                <div style="display:flex; gap:10px; flex-wrap:wrap;">
                <a href="{{ url($basePrefix . '/walkin') }}" class="btn w-100 py-2" style="flex:1 1 180px; background: #ffffff; border: 1px solid #cbd5e1; color: #475569; font-weight: 700; font-size: 0.8rem; border-radius: 8px; display: flex; align-items: center; justify-content: center; gap: 8px; text-decoration: none;">
                     BACK TO INTAKE OPTIONS
                </a>
                <a href="{{ url($basePrefix . '/appointments') }}" class="btn w-100 py-2" style="flex:1 1 180px; background: #f8fafc; border: 1px solid #cbd5e1; color: #475569; font-weight: 600; font-size: 0.8rem; border-radius: 8px; display: flex; align-items: center; justify-content: center; gap: 8px; text-decoration: none;">
                 BACK TO APPOINTMENTS LIST
                </a>
                </div>
            </div>
        </div>
    </div>

    <div id="registerForm" style="{{ $currentMode === 'assisted' ? '' : 'display:none;' }}">
        <form id="formRegisterStudent">
            @csrf

            <div class="assisted-intake-shell">
                <section class="assisted-panel">
                    <div class="assisted-panel-body">
                        <div class="assisted-hero">
                            <div class="assisted-hero-badge" aria-hidden="true">
                                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 6.75V4.875c0-1.036-.84-1.875-1.875-1.875h-3.75c-1.035 0-1.875.84-1.875 1.875V6.75m7.5 0h1.125c1.035 0 1.875.84 1.875 1.875v9.75c0 1.035-.84 1.875-1.875 1.875H7.125c-1.035 0-1.875-.84-1.875-1.875v-9.75c0-1.035.84-1.875 1.875-1.875H8.25m7.5 0h-7.5m3.75 3v3.75m-1.875-1.875h3.75" />
                                </svg>
                            </div>
                            <div class="assisted-hero-copy">
                                <h3>Clinic Patient Registration</h3>
                                <div class="assisted-status-row" style="margin-bottom:10px;">
                                    <span class="assisted-status-chip pending">Temporary Record</span>
                                    <span class="assisted-status-chip ready">Ready for Consultation</span>
                                </div>
                                <p>Capture the patient identity details here, then continue to the consultation form for clinical notes and assessment.</p>
                            </div>
                        </div>

            <div class="mb-3 assisted-highlight-card">
                <label style="font-size: 11px; font-weight: 700; color: #475569; text-transform: uppercase;">Student Number / Reference ID</label>
                <input type="text" id="reg_student_id" class="form-control mb-0" placeholder="Enter student number or reference ID" required>
                <input type="hidden" id="reg_barcode">
            </div>
            
            <div class="mb-2">
                <label style="font-size: 11px; font-weight: 700; color: #475569;">PATIENT ROLE</label>
                <div class="assisted-role-wrap" id="assistedRoleWrap">
                    <select id="reg_user_type" class="form-control assisted-role-select" required>
                        <option value="" disabled selected>-- Choose Patient Role --</option>
                        <option value="Guest">Guest</option>
                        <option value="Dependent">Dependent</option>
                        <option value="Student">Student</option>
                        <option value="Faculty">Faculty</option>
                        <option value="Admin">Admin</option>
                    </select>
                    <button type="button" class="assisted-role-display" id="assistedRoleDisplay" aria-haspopup="listbox" aria-expanded="false">
                        Select patient role
                    </button>
                    <div class="assisted-role-menu" id="assistedRoleMenu" role="listbox" aria-label="Patient Role options">
                        <button type="button" class="assisted-role-option" data-role-value="Guest">Guest</button>
                        <button type="button" class="assisted-role-option" data-role-value="Dependent">Dependent</button>
                        <button type="button" class="assisted-role-option" data-role-value="Student">Student</button>
                        <button type="button" class="assisted-role-option" data-role-value="Faculty">Faculty</button>
                        <button type="button" class="assisted-role-option" data-role-value="Admin">Admin</button>
                    </div>
                </div>
            </div>

            <div class="assisted-section-divider" aria-hidden="true">
                <span>
                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M15 19.125A7.125 7.125 0 1 0 8.25 7.5a5.625 5.625 0 1 1 6.75 11.625Zm0 0h3.375m-3.375 0v-3.375" />
                    </svg>
                    Identity Details
                </span>
            </div>
            
            <div class="assisted-pair-row">
                <div class="assisted-field-card">
                    <label class="assisted-field-label" for="reg_first_name">First Name</label>
                    <input type="text" id="reg_first_name" placeholder="Enter first name" class="form-control" required>
                </div>
                <div class="assisted-field-card">
                    <label class="assisted-field-label" for="reg_last_name">Last Name</label>
                    <input type="text" id="reg_last_name" placeholder="Enter last name" class="form-control" required>
                </div>
            </div>

            <div class="assisted-pair-row">
                <div class="assisted-field-card">
                    <label class="assisted-field-label" for="reg_dob">Birthday</label>
                    <input type="date" id="reg_dob" class="form-control" aria-label="Birthday">
                </div>
                <div class="assisted-field-card">
                    <label class="assisted-field-label" for="reg_gender">Sex / Gender</label>
                    <div class="assisted-gender-wrap" id="assistedGenderWrap">
                    <select id="reg_gender" class="form-control assisted-gender-select">
                        <option value="">Sex / Gender</option>
                        <option value="Male">Male</option>
                        <option value="Female">Female</option>
                        <option value="Other">Other</option>
                    </select>
                    <button type="button" class="assisted-gender-display" id="assistedGenderDisplay" aria-haspopup="listbox" aria-expanded="false">
                        Select sex / gender
                    </button>
                    <div class="assisted-gender-menu" id="assistedGenderMenu" role="listbox" aria-label="Sex / Gender options">
                        <button type="button" class="assisted-gender-option" data-gender-value="Male">Male</button>
                        <button type="button" class="assisted-gender-option" data-gender-value="Female">Female</button>
                        <button type="button" class="assisted-gender-option" data-gender-value="Other">Other</button>
                    </div>
                </div>
                </div>
            </div>

            <div class="assisted-section-divider" aria-hidden="true">
                <span>
                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M21.75 8.25v7.5a2.25 2.25 0 0 1-2.25 2.25H4.5a2.25 2.25 0 0 1-2.25-2.25v-7.5m19.5 0-8.69 5.216a2.25 2.25 0 0 1-2.122 0L2.25 8.25m19.5 0A2.25 2.25 0 0 0 19.5 6H4.5a2.25 2.25 0 0 0-2.25 2.25" />
                    </svg>
                    Contact Details
                </span>
            </div>

            <div class="assisted-field-card">
                <label class="assisted-field-label" for="reg_contact_no">Contact Number</label>
                <input type="text" id="reg_contact_no" placeholder="Enter contact number" class="form-control">
            </div>

            <div class="assisted-field-card">
                <label class="assisted-field-label" for="reg_email">Email Address</label>
                <input type="email" id="reg_email" placeholder="Enter email address" class="form-control" required>
            </div>

            <div style="background:#fff7ed; border:1px dashed #fdba74; border-radius:10px; padding:12px 14px; margin-bottom:10px;">
                <strong style="display:block; font-size:12px; color:#9a3412; margin-bottom:4px;">No password needed for assisted intake</strong>
                <p style="margin:0; font-size:12px; color:#7c2d12; line-height:1.5;">A valid email address is required for assisted intake before proceeding to consultation.</p>
            </div>
            
                    </div>
                </section>

            <div id="notification" style="margin: 10px 0;"></div>
            
            <button type="button" id="confirmBtn" class="assisted-submit-btn mt-2">
                SAVE ASSISTED INTAKE
            </button>
            
            <div class="text-center mt-3">
                <a href="{{ url($basePrefix . '/walkin') }}" style="font-size: 12px; color: #64748b; text-decoration: none;">Back to intake options</a>
            </div>
            </div>
        </form>
    </div>
</div>
@endif
</div>
@endsection

@push('scripts')
<script src="https://unpkg.com/html5-qrcode" type="text/javascript"></script>
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/tesseract.js@5/dist/tesseract.min.js"></script>
<script type="text/javascript">
    let mainScanner;
    let ocrWorkerPromise = null;
    let currentVideoTrack = null;
    let liveOcrInterval = null;
    let ocrInFlight = false;
    let lastOcrSignature = '';
    let ocrLockCount = 0;
    let lastStudentNumberCandidate = '';
    let studentNumberStableCount = 0;
    let lastStudentNameCandidate = '';
    let studentNameStableCount = 0;
    let manualStudentNumberEdited = false;
    let manualStudentNameEdited = false;
    let lastPreviewedStudentNumber = '';
    let lastPreviewedStudentName = '';
    let ocrNameLocked = false;
    let aiAssistCooldown = false;
    let autoProceedInFlight = false;
    let lastAutoProceedKey = '';
    const initialMode = @json($currentMode);
    let intakeTarget = 'consultation';
    let scanMethod = 'ocr';
    const liveOcrIntervalMs = 900;
    const ocrCanvasScale = 1;
    const supportedFormats = window.Html5QrcodeSupportedFormats ? [
        Html5QrcodeSupportedFormats.CODE_128,
        Html5QrcodeSupportedFormats.CODE_39,
        Html5QrcodeSupportedFormats.CODE_93,
        Html5QrcodeSupportedFormats.EAN_13,
        Html5QrcodeSupportedFormats.EAN_8,
        Html5QrcodeSupportedFormats.UPC_A,
        Html5QrcodeSupportedFormats.UPC_E,
        Html5QrcodeSupportedFormats.ITF,
        Html5QrcodeSupportedFormats.CODABAR,
        Html5QrcodeSupportedFormats.QR_CODE
    ] : [];
    const scannerConfig = {
        fps: 20,
        qrbox: { width: 400, height: 160 },
        aspectRatio: 1.777778
    };
    const ocrSkipWords = ['POLYTECHNIC', 'UNIVERSITY', 'OF', 'THE', 'PHILIPPINES', 'TAGUIG', 'CAMPUS', 'STUDENT', 'IDENTIFICATION', 'CARD', 'ID', 'NO', 'NUMBER'];

    if (supportedFormats.length) {
        scannerConfig.formatsToSupport = supportedFormats;
    }

    function createScanner(targetId) {
        if (supportedFormats.length) {
            return new Html5Qrcode(targetId, { formatsToSupport: supportedFormats });
        }

        return new Html5Qrcode(targetId);
    }

    $(document).ready(function() {
        const applicantScanModal = document.getElementById('applicantScanModal');
        const openScanLookupModalBtn = document.getElementById('openScanLookupModal');
        const openApplicantScanModalBtn = document.getElementById('openApplicantScanModal');
        const closeApplicantScanModalBtn = document.getElementById('closeApplicantScanModal');
        const startOcrCameraBtn = document.getElementById('btnStartOcrCamera');
        const closeOcrCameraBtn = document.getElementById('btnCloseOcrCamera');
        const ocrCameraIdle = document.getElementById('ocrCameraIdle');
        const ocrScanActions = document.getElementById('ocrScanActions');
        const applicantOcrDetectedContent = document.getElementById('applicantOcrDetectedContent');
        const assistedRoleSelect = document.getElementById('reg_user_type');
        const assistedRoleDisplay = document.getElementById('assistedRoleDisplay');
        const assistedRoleMenu = document.getElementById('assistedRoleMenu');
        const assistedRoleOptions = Array.from(document.querySelectorAll('.assisted-role-option'));
        const assistedRoleWrap = assistedRoleDisplay ? assistedRoleDisplay.closest('.assisted-role-wrap') : null;
        const assistedGenderSelect = document.getElementById('reg_gender');
        const assistedGenderDisplay = document.getElementById('assistedGenderDisplay');
        const assistedGenderMenu = document.getElementById('assistedGenderMenu');
        const assistedGenderOptions = Array.from(document.querySelectorAll('.assisted-gender-option'));
        const assistedGenderWrap = assistedGenderDisplay ? assistedGenderDisplay.closest('.assisted-gender-wrap') : null;

        updateScanModeUI();

        function getDestinationLabel() {
            return intakeTarget === 'assessment' ? 'applicant record' : 'consultation form';
        }

        function syncAssistedRoleDisplay() {
            if (!assistedRoleSelect || !assistedRoleDisplay) return;

            const selectedValue = assistedRoleSelect.value || '';
            const selectedText = selectedValue
                ? (assistedRoleSelect.options[assistedRoleSelect.selectedIndex]?.text || selectedValue)
                : 'Select patient role';

            assistedRoleDisplay.textContent = selectedText;

            assistedRoleOptions.forEach(function (option) {
                option.classList.toggle('is-selected', option.dataset.roleValue === selectedValue);
            });
        }

        function setAssistedRoleOpenState(isOpen) {
            if (!assistedRoleWrap || !assistedRoleDisplay) return;

            assistedRoleWrap.classList.toggle('is-open', isOpen);
            assistedRoleDisplay.classList.toggle('is-open', isOpen);
            assistedRoleDisplay.setAttribute('aria-expanded', isOpen ? 'true' : 'false');
        }

        function syncAssistedGenderDisplay() {
            if (!assistedGenderSelect || !assistedGenderDisplay) return;

            const selectedValue = assistedGenderSelect.value || '';
            const selectedText = selectedValue
                ? (assistedGenderSelect.options[assistedGenderSelect.selectedIndex]?.text || selectedValue)
                : 'Select sex / gender';

            assistedGenderDisplay.textContent = selectedText;

            assistedGenderOptions.forEach(function (option) {
                option.classList.toggle('is-selected', option.dataset.genderValue === selectedValue);
            });
        }

        function setAssistedGenderOpenState(isOpen) {
            if (!assistedGenderWrap || !assistedGenderDisplay) return;

            assistedGenderWrap.classList.toggle('is-open', isOpen);
            assistedGenderDisplay.classList.toggle('is-open', isOpen);
            assistedGenderDisplay.setAttribute('aria-expanded', isOpen ? 'true' : 'false');
        }

        function getScannerVideoElement() {
            return document.querySelector('#readerScan video');
        }

        function isOcrMode() {
            return true;
        }

        function attachVideoTrack() {
            const video = getScannerVideoElement();
            if (!video || !video.srcObject) {
                return;
            }

            const tracks = video.srcObject.getVideoTracks ? video.srcObject.getVideoTracks() : [];
            currentVideoTrack = tracks.length ? tracks[0] : null;
        }

        function stopLiveOcr() {
            if (liveOcrInterval) {
                window.clearInterval(liveOcrInterval);
                liveOcrInterval = null;
            }
        }

        function startLiveOcr() {
            if (!isOcrMode() || liveOcrInterval) {
                return;
            }

            liveOcrInterval = window.setInterval(function () {
                const video = getScannerVideoElement();

                if (!isOcrMode() || ocrInFlight || !video || video.readyState < 2) {
                    return;
                }

                captureAndAnalyzeId(true);
            }, liveOcrIntervalMs);
        }

        function setOcrCameraState(isActive, isStarting = false) {
            if (startOcrCameraBtn) {
                startOcrCameraBtn.disabled = isActive || isStarting;
                startOcrCameraBtn.textContent = isStarting ? 'Opening Camera...' : 'Start Camera';
            }
            if (closeOcrCameraBtn) closeOcrCameraBtn.disabled = !isActive;
            if (ocrCameraIdle) ocrCameraIdle.classList.toggle('is-hidden', isActive);
            if (ocrCameraIdle && !isActive && !isStarting) {
                ocrCameraIdle.textContent = 'Camera is Closed. Select Start Camera when you are ready to scan the student ID.';
            }
            if (ocrScanActions) ocrScanActions.style.display = isActive ? 'flex' : 'none';
            if (applicantOcrDetectedContent) applicantOcrDetectedContent.style.display = isActive ? 'block' : 'none';
        }

        async function startMainScanner() {
            if (mainScanner) {
                return;
            }

            setOcrCameraState(false, true);
            mainScanner = createScanner("readerScan");

            try {
                await mainScanner.start(
                    { facingMode: "environment" },
                    scannerConfig,
                    (decodedText) => {
                        if (isOcrMode()) {
                            return;
                        }

                        verifyUser(decodedText);
                    }
                );
                attachVideoTrack();
                setOcrCameraState(true);
                buildStatus('Live OCR is scanning. Hold the student ID steady inside the frame.', 'info');
                $('#ocrConfidenceText').text('Student number confidence will appear after scanning.');
                startLiveOcr();
            } catch (error) {
                mainScanner = null;
                currentVideoTrack = null;
                setOcrCameraState(false);
                if (ocrCameraIdle) {
                    ocrCameraIdle.textContent = 'The camera could not be opened. Check browser camera permission, then try again.';
                }
                console.warn(error);
            }
        }

        function stopMainScanner() {
            stopLiveOcr();
            if (mainScanner) {
                if (startOcrCameraBtn) {
                    startOcrCameraBtn.disabled = true;
                    startOcrCameraBtn.textContent = 'Closing Camera...';
                }
                if (closeOcrCameraBtn) closeOcrCameraBtn.disabled = true;
                if (ocrScanActions) ocrScanActions.style.display = 'none';
                if (applicantOcrDetectedContent) applicantOcrDetectedContent.style.display = 'none';
                mainScanner.stop().catch(() => {}).finally(() => {
                    mainScanner = null;
                    currentVideoTrack = null;
                    setOcrCameraState(false);
                });
            } else {
                currentVideoTrack = null;
                setOcrCameraState(false);
            }
        }

        function buildStatus(message, type = 'info', extra = '') {
            const $status = $('#ocrStatus');
            $status.removeClass('info success error').addClass(type).html(`${message}${extra ? `<div class="ocr-meta">${extra}</div>` : ''}`);
        }

        function normalizeSpaces(value) {
            return (value || '').replace(/\s+/g, ' ').trim();
        }

        function cleanOcrLine(value) {
            return normalizeSpaces((value || '').replace(/[^\w\s-]/g, ' '));
        }

        function extractStudentNumber(text, focusedText = '') {
            const normalized = `${focusedText} ${text}`.replace(/\s+/g, ' ').toUpperCase();
            const patterns = [
                /\b20\d{2}\s*-\s*\d{5}\s*-\s*[A-Z]{2}\s*-\s*\d\b/,
                /\b20\d{2}-\d{3}-\d{3}\b/,
                /\b\d{4}-\d{3}-\d{3}\b/,
                /\b\d{4}\s*-\s*\d{3}\s*-\s*\d{3}\b/,
                /\b20\d{2}\d{6}\b/,
                /\b20\d{2}\d{5}[A-Z]{2}\d\b/
            ];

            for (const pattern of patterns) {
                const match = normalized.match(pattern);
                if (match) {
                    const compact = match[0].replace(/\s+/g, '').toUpperCase();

                    if (/^20\d{2}\d{5}[A-Z]{2}\d$/.test(compact)) {
                        return `${compact.slice(0, 4)}-${compact.slice(4, 9)}-${compact.slice(9, 11)}-${compact.slice(11, 12)}`;
                    }

                    if (/^20\d{10}$/.test(compact)) {
                        return `${compact.slice(0, 4)}-${compact.slice(4, 7)}-${compact.slice(7, 10)}`;
                    }

                    return compact;
                }
            }

            return '';
        }

        function isLikelyNameLine(line, allowSingleToken = false) {
            const cleaned = line.replace(/[^A-Za-z\s]/g, '').trim();
            const upper = cleaned.toUpperCase();
            const tokens = upper.split(' ').filter(Boolean);

            if (!tokens.length) {
                return false;
            }

            if (!allowSingleToken && tokens.length < 2) {
                return false;
            }

            if (tokens.length > 5) {
                return false;
            }

            if (tokens.some(token => ocrSkipWords.includes(token))) {
                return false;
            }

            if (tokens.some(token => token.length < 2 && !['R', 'J'].includes(token))) {
                return false;
            }

            if (!tokens.every(token => /^[A-Z]+$/.test(token))) {
                return false;
            }

            return true;
        }

        function extractStudentName(text, focusedText = '') {
            const lines = `${focusedText}\n${text}`
                .split(/\r?\n/)
                .map(line => cleanOcrLine(line))
                .filter(Boolean);
            const uniqueLines = [];

            lines.forEach((line) => {
                const upper = line.toUpperCase();
                if (!uniqueLines.includes(upper) && !/\d/.test(upper)) {
                    uniqueLines.push(upper);
                }
            });

            for (let i = 0; i < uniqueLines.length - 1; i += 1) {
                const topLine = uniqueLines[i];
                const nextLine = uniqueLines[i + 1];

                if (isLikelyNameLine(topLine, false) && isLikelyNameLine(nextLine, true)) {
                    return `${topLine} ${nextLine}`.replace(/\s+/g, ' ').trim();
                }
            }

            const singleLineCandidate = uniqueLines.find(line => isLikelyNameLine(line, false));
            if (singleLineCandidate) {
                return singleLineCandidate;
            }

            const twoSingleTokenLines = uniqueLines.filter(line => isLikelyNameLine(line, true)).slice(0, 2);
            if (twoSingleTokenLines.length === 2) {
                return twoSingleTokenLines.join(' ');
            }

            return '';
        }

        function updateDetectedFields(studentNumber, studentName, confidence, isLocked = false, allowNameAutofill = false) {
            if (studentNumber && (!manualStudentNumberEdited || $('#ocr_student_number').val().trim() === '')) {
                $('#ocr_student_number').val(studentNumber);
            }

            if (allowNameAutofill && studentName && !ocrNameLocked && (!manualStudentNameEdited || $('#ocr_student_name').val().trim() === '')) {
                $('#ocr_student_name').val(studentName);
                ocrNameLocked = true;
            }

            $('#ocrConfidenceText').text(confidence ? `ID number confidence: ${confidence}%` : 'ID number confidence will appear here after analysis.');
            $('#ocrResultPanel').show();
            $('#btnConfirmOcr').prop('disabled', !($('#ocr_student_number').val().trim() && $('#ocr_student_name').val().trim()));
            $('#ocrLockBadge').toggle(isLocked);
        }

        function requestMatchedNamePreview(studentNumber, extractedName = '', onApplied = null) {
            const normalizedStudentNumber = (studentNumber || '').trim();
            if (!normalizedStudentNumber || lastPreviewedStudentNumber === normalizedStudentNumber) {
                return;
            }

            lastPreviewedStudentNumber = normalizedStudentNumber;

            $.get("{{ url($basePrefix . '/walkin/get-student') }}", {
                student_id: normalizedStudentNumber,
                student_name: extractedName,
                preview_only: 1,
                intake_target: intakeTarget
            }, function(res) {
                if (res.status !== 'preview' || !res.student_name) {
                    return;
                }

                $('#ocr_student_name').val(res.student_name);
                $('#btnConfirmOcr').prop('disabled', !($('#ocr_student_number').val().trim() && $('#ocr_student_name').val().trim()));
                $('#ocrLockBadge').show().text('Matched in records');

                if (typeof onApplied === 'function') {
                    onApplied(res);
                }
            });
        }

        function attemptAutoProceed(studentNumber, studentName) {
            const normalizedStudentNumber = (studentNumber || '').trim();
            const normalizedStudentName = (studentName || '').trim();
            const autoProceedKey = `${normalizedStudentNumber}|${normalizedStudentName}`;

            if (!normalizedStudentNumber || !normalizedStudentName || autoProceedInFlight || lastAutoProceedKey === autoProceedKey) {
                return;
            }

            autoProceedInFlight = true;
            lastAutoProceedKey = autoProceedKey;
            buildStatus(`Student number and name matched. Opening the ${getDestinationLabel()} now.`, 'success', 'Auto proceed');
            verifyUser(normalizedStudentNumber, normalizedStudentName, true);
        }

        function requestStudentNumberPreviewByName(studentName, onApplied = null) {
            const normalizedStudentName = (studentName || '').trim();
            if (!normalizedStudentName || lastPreviewedStudentName === normalizedStudentName) {
                return;
            }

            lastPreviewedStudentName = normalizedStudentName;

            $.get("{{ url($basePrefix . '/walkin/get-student') }}", {
                student_id: '',
                student_name: normalizedStudentName,
                preview_only: 1,
                intake_target: intakeTarget
            }, function(res) {
                if (res.status !== 'preview' || !res.student_number) {
                    return;
                }

                $('#ocr_student_number').val(res.student_number);
                if (res.student_name && (!manualStudentNameEdited || $('#ocr_student_name').val().trim() === '')) {
                    $('#ocr_student_name').val(res.student_name);
                }
                $('#btnConfirmOcr').prop('disabled', !($('#ocr_student_number').val().trim() && $('#ocr_student_name').val().trim()));
                $('#ocrLockBadge').show().text('Matched by name');

                if (typeof onApplied === 'function') {
                    onApplied(res);
                }
            });
        }

        async function getOcrWorker() {
            if (!ocrWorkerPromise) {
                ocrWorkerPromise = (async () => {
                    const worker = await Tesseract.createWorker('eng');

                    if (typeof worker.setParameters === 'function') {
                        await worker.setParameters({
                            preserve_interword_spaces: '1',
                        });
                    }

                    return worker;
                })();
            }

            return ocrWorkerPromise;
        }

        function capturePreparedIdCanvas(preprocess = true) {
            const video = getScannerVideoElement();
            if (!video || video.readyState < 2) {
                return null;
            }

            const canvas = document.getElementById('ocrCanvas');
            const context = canvas.getContext('2d', { willReadFrequently: true });
            const width = video.videoWidth || 1280;
            const height = video.videoHeight || 720;
            const outputWidth = Math.max(720, Math.floor(width * ocrCanvasScale));
            const outputHeight = Math.max(405, Math.floor(height * ocrCanvasScale));

            canvas.width = outputWidth;
            canvas.height = outputHeight;
            context.drawImage(video, 0, 0, width, height, 0, 0, outputWidth, outputHeight);

            if (!preprocess) {
                return canvas;
            }

            const imageData = context.getImageData(0, 0, outputWidth, outputHeight);
            const data = imageData.data;

            for (let i = 0; i < data.length; i += 4) {
                const grayscale = (data[i] * 0.299) + (data[i + 1] * 0.587) + (data[i + 2] * 0.114);
                const softened = Math.max(0, Math.min(255, ((grayscale - 128) * 1.28) + 128 + 10));
                data[i] = softened;
                data[i + 1] = softened;
                data[i + 2] = softened;
            }

            context.putImageData(imageData, 0, 0);

            return canvas;
        }

        function captureStudentNumberCanvas() {
            const sourceCanvas = capturePreparedIdCanvas(true);
            if (!sourceCanvas) {
                return null;
            }

            const width = sourceCanvas.width;
            const height = sourceCanvas.height;
            const numberZoneCanvas = document.createElement('canvas');
            const zoneWidth = Math.floor(width * 0.72);
            const zoneHeight = Math.floor(height * 0.18);
            const zoneX = Math.floor((width - zoneWidth) / 2);
            const zoneY = Math.floor(height * 0.48);

            numberZoneCanvas.width = zoneWidth;
            numberZoneCanvas.height = zoneHeight;
            numberZoneCanvas.getContext('2d', { willReadFrequently: true }).drawImage(
                sourceCanvas,
                zoneX,
                zoneY,
                zoneWidth,
                zoneHeight,
                0,
                0,
                zoneWidth,
                zoneHeight
            );

            return numberZoneCanvas;
        }

        async function captureAndAnalyzeId(isAutoPass = false) {
            const canvas = capturePreparedIdCanvas(true);
            const studentNumberCanvas = captureStudentNumberCanvas();
            if (!canvas || !studentNumberCanvas) {
                if (!isAutoPass) {
                    buildStatus('Camera preview is not ready yet. Please wait a moment, then try again.', 'error');
                }
                return;
            }
            ocrInFlight = true;
            $('#btnRunOcr').prop('disabled', true).text(isAutoPass ? 'Live OCR Running...' : 'Analyzing ID...');

            if (!isAutoPass) {
                buildStatus('Analyzing the camera image and extracting the student data now.', 'info');
            }

            try {
                const worker = await getOcrWorker();
                const [fullResult, numberResult] = await Promise.all([
                    worker.recognize(canvas),
                    worker.recognize(studentNumberCanvas),
                ]);
                const fullText = fullResult.data.text || '';
                const numberText = numberResult.data.text || '';
                const studentNumber = extractStudentNumber(`${numberText} ${fullText}`, numberText);
                const studentName = extractStudentName(fullText, fullText);
                const confidence = Math.round(((numberResult.data.confidence || 0) * 0.8) + ((fullResult.data.confidence || 0) * 0.2));
                const hasNumberCandidate = studentNumber !== '';
                const hasNameCandidate = studentName !== '';

                if (hasNumberCandidate && studentNumber === lastStudentNumberCandidate) {
                    studentNumberStableCount += 1;
                } else if (hasNumberCandidate) {
                    lastStudentNumberCandidate = studentNumber;
                    studentNumberStableCount = 1;
                } else {
                    lastStudentNumberCandidate = '';
                    studentNumberStableCount = 0;
                }

                if (hasNameCandidate && studentName === lastStudentNameCandidate) {
                    studentNameStableCount += 1;
                } else if (hasNameCandidate) {
                    lastStudentNameCandidate = studentName;
                    studentNameStableCount = 1;
                } else {
                    lastStudentNameCandidate = '';
                    studentNameStableCount = 0;
                }

                const allowNameAutofill = hasNameCandidate && studentNameStableCount >= 2;
                const stableStudentNumber = hasNumberCandidate && studentNumberStableCount >= 2 ? studentNumber : '';
                const stableStudentName = allowNameAutofill ? studentName : '';
                const signature = `${stableStudentNumber}|${stableStudentName}|${confidence}`;
                const isStableCandidate = stableStudentNumber !== '' || stableStudentName !== '';

                if (isStableCandidate && signature === lastOcrSignature) {
                    ocrLockCount += 1;
                } else if (isStableCandidate) {
                    ocrLockCount = 1;
                } else {
                    ocrLockCount = 0;
                }

                const isLocked = ocrLockCount >= 2 && isStableCandidate;

                updateDetectedFields(stableStudentNumber || studentNumber, stableStudentName, confidence, isLocked, allowNameAutofill);

                if (stableStudentNumber) {
                    requestMatchedNamePreview(stableStudentNumber, stableStudentName || studentName, function(preview) {
                        if (preview.name_matches === false) {
                            buildStatus('Student number matched an official record, but the scanned name still looks different. Please review the card before continuing.', 'info', 'Record name applied');
                        } else {
                            buildStatus('Student number matched an official record and the system applied the saved name automatically. Please review before continuing.', 'success', 'Record name applied');
                            attemptAutoProceed(
                                stableStudentNumber,
                                ($('#ocr_student_name').val() || preview.student_name || stableStudentName || studentName)
                            );
                        }
                    });

                    if (signature !== lastOcrSignature || !isAutoPass) {
                        buildStatus(
                            isLocked
                                ? 'Live OCR locked onto the card. Please review the extracted ID number and name before continuing.'
                                : allowNameAutofill
                                    ? 'Live OCR found a stable student number and a usable name guess. Please review the extracted fields below.'
                                    : 'Live OCR found a stable student number. The system is matching the saved name now.',
                            'success',
                            `ID number confidence ${confidence}%`
                        );
                        lastOcrSignature = signature;
                    }
                } else if (stableStudentName) {
                    if (signature !== lastOcrSignature) {
                        buildStatus(
                            'The name is stable now. Keep the ID steady and we will keep reading the student number.',
                            'info',
                            `ID number confidence ${confidence}%`
                        );
                        lastOcrSignature = signature;
                    }
                } else if (isAutoPass && !aiAssistCooldown) {
                    aiAssistCooldown = true;
                    window.setTimeout(function() {
                        aiAssistCooldown = false;
                    }, 7000);
                    verifyWithAi(true);
                } else if (!isAutoPass) {
                    buildStatus('OCR could not confidently read the ID number yet. You can keep the card steady, use AI ID-number reading, or type it manually.', 'error', `ID number confidence ${confidence}%`);
                }
            } catch (error) {
                if (!isAutoPass) {
                    buildStatus('OCR analysis failed on this capture. Please try again or use manual entry.', 'error');
                }
            } finally {
                ocrInFlight = false;
                $('#btnRunOcr').prop('disabled', false).text('Capture & Analyze ID');
            }
        }

        function verifyWithAi(isAutoAssist = false) {
            const canvas = capturePreparedIdCanvas(false);
            if (!canvas) {
                buildStatus('Camera preview is not ready yet. Please wait a moment, then try AI student-number reading again.', 'error');
                return;
            }

            $('#btnRunAiOcr').prop('disabled', true).text('Reading ID...');
            buildStatus(
                isAutoAssist
                    ? 'Live OCR needs help, so we are sending the current camera image to AI to extract the student number.'
                    : 'Sending the current camera image to AI to extract the student number.',
                'info'
            );

            $.ajax({
                url: "{{ url($basePrefix . '/walkin/verify-id-ai') }}",
                method: 'POST',
                data: {
                    _token: "{{ csrf_token() }}",
                    image_data: canvas.toDataURL('image/jpeg', 0.84),
                },
                success: function(res) {
                    const studentNumber = (res.student_number || '').trim();
                    const studentName = (res.student_name || '').trim();
                    const note = (res.confidence_note || 'AI student-number reading completed.').trim();

                    if (studentNumber) {
                        $('#ocr_student_number').val(studentNumber);
                    }

                    $('#ocrResultPanel').show();
                    $('#btnConfirmOcr').prop('disabled', !(studentNumber && $('#ocr_student_name').val().trim()));
                    $('#ocrLockBadge').show().text('AI Read');

                    if (studentNumber) {
                        requestMatchedNamePreview(studentNumber, '', function(preview) {
                            if (preview && preview.student_name) {
                                buildStatus('AI read the student number and the system filled the saved name from records. Please review before continuing.', 'success', 'AI + records');
                                attemptAutoProceed(studentNumber, preview.student_name);
                                return;
                            }

                            if (studentName) {
                                $('#ocr_student_name').val(studentName);
                            }

                            $('#btnConfirmOcr').prop('disabled', !(studentNumber && $('#ocr_student_name').val().trim()));
                            buildStatus(note, 'success', 'AI assist');
                            attemptAutoProceed(studentNumber, $('#ocr_student_name').val().trim());
                        });
                    } else if (studentName) {
                        $('#ocr_student_name').val(studentName);
                        $('#btnConfirmOcr').prop('disabled', !(studentNumber && $('#ocr_student_name').val().trim()));
                        buildStatus(note, 'info', 'AI assist');
                    } else {
                        buildStatus(note, 'error', 'AI assist');
                    }
                },
                error: function(xhr) {
                    const response = xhr.responseJSON || {};
                    buildStatus(response.message || 'AI student-number reading could not complete right now. Please keep using OCR or manual review.', 'error');
                },
                complete: function() {
                    $('#btnRunAiOcr').prop('disabled', false).text('Reading ID Number');
                }
            });
        }

        function setManualLookupStatus(type, message) {
            const status = document.getElementById('manualLookupStatus');
            if (!status) return;

            status.className = 'manual-lookup-status is-visible is-' + type;
            status.replaceChildren();

            if (type === 'loading') {
                const spinner = document.createElement('span');
                spinner.className = 'spinner';
                spinner.setAttribute('aria-hidden', 'true');
                status.appendChild(spinner);
            }

            const copy = document.createElement('span');
            copy.textContent = message;
            status.appendChild(copy);
        }

        function isSupportedClinicIdNumber(value) {
            const normalized = String(value || '').trim().toUpperCase();
            if (!normalized) return false;

            const studentNumberPattern = /^\d{4}-\d{5}-[A-Z]{2}-\d+$/;
            const employeeNumberPattern = /^[A-Z0-9-]{3,40}$/;

            return studentNumberPattern.test(normalized) || employeeNumberPattern.test(normalized);
        }

        function verifyUser(id, studentName = '', autoProceed = false, verificationSource = 'ocr') {
            const isManualLookup = verificationSource === 'manual';
            const normalizedId = String(id || '').trim();

            if (!isSupportedClinicIdNumber(normalizedId)) {
                const invalidMessage = 'Enter a valid employee number or student number from local clinic records.';
                if (isManualLookup) {
                    setManualLookupStatus('error', invalidMessage);
                    $('#manualFindBtn').prop('disabled', false).text('Find Record');
                } else {
                    buildStatus(invalidMessage, 'error');
                }
                return;
            }

            if (isManualLookup) {
                setManualLookupStatus('loading', 'Checking local clinic records...');
                $('#manualFindBtn').prop('disabled', true).text('Verifying...');
            } else {
                $('#scan-loading').css('display', 'flex');
            }
            $('#notification').html('');
            $.get("{{ url($basePrefix . '/walkin/get-student') }}", {
                student_id: normalizedId,
                student_name: studentName,
                intake_target: intakeTarget,
                lookup_scope: 'clinic_local'
            }, function(res) {
                if (!isManualLookup) $('#scan-loading').hide();
                autoProceedInFlight = false;
                if (res.status === 'found') {
                    if (isManualLookup) setManualLookupStatus('success', 'Record found. Opening the local clinic record...');
                    window.location.href = res.redirect_url;
                } else if (res.status === 'name_mismatch') {
                    const candidateName = res.candidate && res.candidate.name ? res.candidate.name : 'Saved patient name';
                    const candidateNumber = res.candidate && res.candidate.student_number ? res.candidate.student_number : normalizedId;
                    if (isManualLookup) {
                        setManualLookupStatus('error', `Record ${candidateNumber} was found, but the supplied name does not match ${candidateName}.`);
                    } else {
                        buildStatus(`We found ${candidateNumber}, but the extracted name did not match the saved record (${candidateName}). Please review the OCR result before continuing.`, 'error');
                        $('#ocrResultPanel').show();
                    }
                } else {
                    const failureMessage = res.message
                        ? res.message
                        : `No local employee or student record found for ID number ${normalizedId}. Please check the number and try again.`;

                    if (isManualLookup) {
                        setManualLookupStatus('error', failureMessage);
                        return;
                    }

                    if (autoProceed) {
                        buildStatus(`Auto proceed stopped: ${failureMessage}`, 'info');
                        return;
                    }

                    $('#notification').html(`<p style="color:#991b1b; font-size:12px; font-weight:700; background:#fff1f2; padding:10px 12px; border-radius:10px; border:1px solid #fecdd3; margin-bottom:12px;">${failureMessage}</p>`);

                    if(mainScanner) {
                        mainScanner.stop().then(() => {
                            mainScanner = null;
                            currentVideoTrack = null;
                            stopLiveOcr();
                            if (confirm(`${failureMessage}\n\nOpen Assisted Intake instead?`)) {
                                showRegisterUI(id);
                            } else { window.location.reload(); }
                        });
                    }
                }
            }).fail(() => {
                if (isManualLookup) {
                    setManualLookupStatus('error', 'Unable to check local clinic records right now. Please try again.');
                } else {
                    $('#scan-loading').hide();
                }
                autoProceedInFlight = false;
            }).always(() => {
                if (isManualLookup) {
                    $('#manualFindBtn').prop('disabled', normalizedId === '').text('Find Record');
                }
            });
        }

        function showRegisterUI(scannedId = '') {
            $('#scanForm').hide();
            $('#registerForm').show();
            $('#headerTitle').text('Assisted Intake Ready');
            $('#headerIcon').text('ASSIST');
            if(scannedId) {
                $('#reg_barcode').val(scannedId);
                $('#reg_student_id').val(scannedId);
            }
        }

        function updateScanModeUI() {
            scanMethod = 'ocr';
            const isApplicantFlow = intakeTarget === 'assessment';
            $('#scanMethodTitle').text('OCR ID Scan');
            $('#scanMethodNote').text(
                isApplicantFlow
                        ? 'Use the live camera feed to extract the printed ID number from the physical ID card, then review the saved local record.'
                        : 'Use the live camera feed to extract the printed ID number from the physical ID card, then fill the saved name from records.'
            );
            $('#scanMethodBadge').text('OCR Active');
            $('#btnSwitchScanMode').hide();
            $('#btnSwitchScanMode span').text('OCR Scan Active');
            $('#headerTitle').text('OCR Ready');
            $('#headerSubtitle').text(
                isApplicantFlow
                        ? 'Choose OCR ID scanning or manual ID entry to identify the saved local clinic record.'
                        : ''
            );
            $('#headerIcon').text(isApplicantFlow ? 'AP' : 'SB');
            $('#scanInlineNote').text(
                isApplicantFlow
                        ? 'OCR mode is active. Align the physical ID inside the frame and continue once the ID number and name are matched locally.'
                        : 'OCR mode is active. Align the physical ID inside the frame and the system will keep reading the ID number live, then match the saved name automatically.'
            );
            $('#barcodeScanPanel').show();
            $('#btnShowManual').show();
            $('#manualInputArea').toggle($('#manualInputArea').is(':visible'));
            $('#applicantOcrReviewPanel').show();
        }

        function openIntakeScanModal(target = 'consultation') {
            intakeTarget = target;
            scanMethod = 'ocr';
            manualStudentNumberEdited = false;
            manualStudentNameEdited = false;
            $('#student_id_manual').val('');
            $('#manualFindBtn').prop('disabled', true);
            $('#ocr_student_number').val('');
            $('#ocr_student_name').val('');
            $('#ocrLockBadge').hide();
            $('#btnConfirmOcr').prop('disabled', true);
            $('#manualInputArea').show();
            $('#ocrStatus').removeClass('success error').addClass('info').text('');
            $('#ocrConfidenceText').text('');
            setOcrCameraState(false);
            updateScanModeUI();
            syncAssistedRoleDisplay();
            syncAssistedGenderDisplay();
            if (applicantScanModal) {
                applicantScanModal.classList.add('show');
            }
        }

        function closeApplicantScanModal() {
            if (applicantScanModal) {
                applicantScanModal.classList.remove('show');
            }
            stopMainScanner();
        }

        if (openScanLookupModalBtn) {
            openScanLookupModalBtn.addEventListener('click', function (event) {
                event.preventDefault();
                openIntakeScanModal('consultation');
            });
        }

        if (openApplicantScanModalBtn) {
            openApplicantScanModalBtn.addEventListener('click', function (event) {
                event.preventDefault();
                openIntakeScanModal('assessment');
            });
        }

        if (closeApplicantScanModalBtn) {
            closeApplicantScanModalBtn.addEventListener('click', closeApplicantScanModal);
        }

        if (startOcrCameraBtn) {
            startOcrCameraBtn.addEventListener('click', startMainScanner);
        }

        if (closeOcrCameraBtn) {
            closeOcrCameraBtn.addEventListener('click', stopMainScanner);
        }

        if (applicantScanModal) {
            applicantScanModal.addEventListener('click', function (event) {
                if (event.target === applicantScanModal) {
                    closeApplicantScanModal();
                }
            });
        }

        if (assistedRoleSelect && assistedRoleDisplay && assistedRoleWrap) {
            assistedRoleDisplay.addEventListener('click', function () {
                const shouldOpen = !assistedRoleWrap.classList.contains('is-open');
                setAssistedRoleOpenState(shouldOpen);
            });

            assistedRoleOptions.forEach(function (option) {
                option.addEventListener('click', function () {
                    const value = option.dataset.roleValue || '';
                    assistedRoleSelect.value = value;
                    syncAssistedRoleDisplay();
                    setAssistedRoleOpenState(false);
                });
            });

            document.addEventListener('click', function (event) {
                if (!assistedRoleWrap.contains(event.target)) {
                    setAssistedRoleOpenState(false);
                }
            });
        }

        if (assistedGenderSelect && assistedGenderDisplay && assistedGenderWrap) {
            assistedGenderDisplay.addEventListener('click', function () {
                const shouldOpen = !assistedGenderWrap.classList.contains('is-open');
                setAssistedGenderOpenState(shouldOpen);
            });

            assistedGenderOptions.forEach(function (option) {
                option.addEventListener('click', function () {
                    const value = option.dataset.genderValue || '';
                    assistedGenderSelect.value = value;
                    syncAssistedGenderDisplay();
                    setAssistedGenderOpenState(false);
                });
            });

            document.addEventListener('click', function (event) {
                if (!assistedGenderWrap.contains(event.target)) {
                    setAssistedGenderOpenState(false);
                }
            });
        }

        $('#btnShowManual').on('click', function() {
            $('#manualInputArea').toggle();
        });

        $('#student_id_manual').on('input', function() {
            const value = $(this).val().trim();
            $('#manualFindBtn').prop('disabled', value === '');
            const manualStatus = document.getElementById('manualLookupStatus');
            if (manualStatus) {
                manualStatus.className = 'manual-lookup-status';
                manualStatus.replaceChildren();
            }
        });

        $('#btnSwitchScanMode').on('click', function() {
            scanMethod = 'ocr';
            updateScanModeUI();
        });

        $('#btnRunAiOcr').on('click', function() {
            verifyWithAi();
        });

        $('#btnConfirmOcr').on('click', function() {
            const studentNumber = $('#ocr_student_number').val().trim();
            const studentName = $('#ocr_student_name').val().trim();

            if (!studentNumber || !studentName) {
                buildStatus('Please review both extracted fields first. We need both the student number and the student name for confirmation.', 'error');
                return;
            }

            verifyUser(studentNumber, studentName);
        });

        $('#btnRetryOcr').on('click', function() {
            $('#ocr_student_number').val('');
            $('#ocr_student_name').val('');
            $('#btnConfirmOcr').prop('disabled', true);
            $('#ocrConfidenceText').text('Student number confidence will appear here after analysis.');
            lastOcrSignature = '';
            ocrLockCount = 0;
            lastStudentNumberCandidate = '';
            studentNumberStableCount = 0;
            lastStudentNameCandidate = '';
            studentNameStableCount = 0;
            manualStudentNumberEdited = false;
            manualStudentNameEdited = false;
            lastPreviewedStudentNumber = '';
            lastPreviewedStudentName = '';
            ocrNameLocked = false;
            autoProceedInFlight = false;
            lastAutoProceedKey = '';
            $('#ocrLockBadge').hide();
            buildStatus('We cleared the last OCR result. Capture the ID again when you are ready.', 'info');
        });

        $('#ocr_student_number').on('input', function() {
            manualStudentNumberEdited = true;
            lastPreviewedStudentNumber = '';
            lastAutoProceedKey = '';
            const hasBoth = $('#ocr_student_number').val().trim() !== '' && $('#ocr_student_name').val().trim() !== '';
            $('#btnConfirmOcr').prop('disabled', !hasBoth);
        });

        $('#ocr_student_name').on('input', function() {
            manualStudentNameEdited = true;
            lastPreviewedStudentName = '';
            ocrNameLocked = true;
            lastAutoProceedKey = '';
            const hasBoth = $('#ocr_student_number').val().trim() !== '' && $('#ocr_student_name').val().trim() !== '';
            $('#btnConfirmOcr').prop('disabled', !hasBoth);
        });

        $('#walkinFormManual').on('submit', function(e) {
            e.preventDefault();
            verifyUser($('#student_id_manual').val(), '', false, 'manual');
        });

        $('#confirmBtn').on('click', function() {
            const form = document.getElementById('formRegisterStudent');
            const role = $('#reg_user_type').val();
            const email = $('#reg_email').val().trim();

            if(!role) { alert("Please select a User Role!"); return; }

            if (!email) {
                $('#reg_email')[0].reportValidity();
                return;
            }

            if (form && !form.checkValidity()) {
                form.reportValidity();
                return;
            }

            $(this).prop('disabled', true).text('PROCESSING...');
            
            const formData = {
                _token: "{{ csrf_token() }}",
                role: role,
                user_role: role,
                user_type: role,
                student_number: $('#reg_student_id').val(),
                first_name: $('#reg_first_name').val(),
                last_name: $('#reg_last_name').val(),
                email: email,
                dob: $('#reg_dob').val(),
                gender: $('#reg_gender').val(),
                contact_no: $('#reg_contact_no').val(),
                barcode: $('#reg_barcode').val() || $('#reg_student_id').val()
            };

            $.ajax({
                url: "{{ url($basePrefix . '/walkin/register') }}",
                method: 'POST',
                data: formData,
                headers: {
                    Accept: 'application/json'
                }
            }).done(function(res) {
                if(res.redirect_url) window.location.href = res.redirect_url;
                else window.location.reload();
            }).fail(function(xhr) {
                $('#confirmBtn').prop('disabled', false).text('SAVE ASSISTED INTAKE');
                let errorMsg = "Assisted intake failed.";
                if(xhr.responseJSON && xhr.responseJSON.errors) {
                    errorMsg = Object.values(xhr.responseJSON.errors)[0][0];
                } else if (xhr.responseJSON && xhr.responseJSON.message) {
                    errorMsg = xhr.responseJSON.message;
                } else if (xhr.status === 419) {
                    errorMsg = "Your session expired. Please refresh the page and try again.";
                } else if (xhr.status === 409) {
                    errorMsg = "This account already exists.";
                }
                $('#notification').html(`<p style="color:#991b1b; font-size:12px; font-weight:bold; background:#fee2e2; padding:10px; border-radius:8px; border:1px solid #fecaca;">${$('<div>').text(errorMsg).html()}</p>`);
            });
        });
    });

    // --- Applicants Modal: Reference Number Lookup ---
    (function () {
        const backdrop        = document.getElementById('applicantRefModal');
        const modalShell      = backdrop?.querySelector('.applicant-modal-shell');
        const applicantModalBody = backdrop?.querySelector('.applicant-modal-body');
        const applicantFileActions = document.getElementById('applicantFileActions');
        const openBtn         = document.getElementById('openApplicantRefModal');
        const openClinicBtn   = document.getElementById('openClinicRefModal');
        const closeBtn        = document.getElementById('closeApplicantRefModal');
        const defaultPane     = document.getElementById('applicantRefDefault');
        const entryPane       = document.getElementById('applicantRefEntry');
        const showEntryBtn    = document.getElementById('btnShowApplicantRefInput');
        const workflowChoices = document.getElementById('applicantWorkflowChoices');
        const startEncodingBtn = document.getElementById('btnStartApplicantEncoding');
        const startFinalReviewBtn = document.getElementById('btnStartApplicantFinalReview');
        const finalReviewList = document.getElementById('applicantFinalReviewList');
        const finalReviewRows = document.getElementById('applicantFinalReviewRows');
        const finalReviewSearch = document.getElementById('applicantFinalReviewSearch');
        const finalReviewRefresh = document.getElementById('btnRefreshFinalReviewList');
        const finalReviewManualLookup = document.getElementById('btnFinalReviewManualLookup');
        const finalReviewPagination = document.getElementById('applicantFinalReviewPagination');
        const finalReviewPrev = document.getElementById('applicantFinalReviewPrev');
        const finalReviewNext = document.getElementById('applicantFinalReviewNext');
        const finalReviewPageLabel = document.getElementById('applicantFinalReviewPageLabel');
        const finalReviewPerPage = document.getElementById('applicantFinalReviewPerPage');
        const finalReviewEmpty = document.getElementById('applicantFinalReviewEmpty');
        const finalReviewTotalCount = document.getElementById('applicantFinalReviewTotalCount');
        const finalReviewActionRow = document.getElementById('applicantFinalReviewActionRow');
        const backToFinalReviewList = document.getElementById('btnBackToFinalReviewList');
        const cancelEntryBtn  = document.getElementById('btnCancelApplicantRef');
        const refInput        = document.getElementById('applicantRefInput');
        const refStatus       = document.getElementById('applicantRefStatus');
        const findBtn         = document.getElementById('btnFindApplicant');
        const reviewDraftBtn = document.getElementById('btnSaveReviewDraft');
        const applicantRefActions = reviewDraftBtn?.closest('.applicant-ref-actions');
        const foundCard       = document.getElementById('applicantFoundCard');
        const foundName       = document.getElementById('applicantFoundName');
        const lookupDetails   = document.getElementById('applicantLookupDetails');
        const lookupRef       = document.getElementById('applicantLookupRef');
        const lookupStatus    = document.getElementById('applicantLookupStatus');
        const lookupCourse    = document.getElementById('applicantLookupCourse');
        const lookupYearSec   = document.getElementById('applicantLookupYearSection');
        const lookupDob       = document.getElementById('applicantLookupDob');
        const lookupEmail     = document.getElementById('applicantLookupEmail');
        const healthInfoModal = document.getElementById('applicantHealthInfoModal');
        const closeHealthInfoModalButton = document.getElementById('closeApplicantHealthInfoModal');
        const informationDetails = document.getElementById('applicantInformationDetails');
        const lookupHeight    = document.getElementById('applicantLookupHeight');
        const lookupWeight    = document.getElementById('applicantLookupWeight');
        const lookupCivilStatus = document.getElementById('applicantLookupCivilStatus');
        const lookupAge       = document.getElementById('applicantLookupAge');
        const lookupGender    = document.getElementById('applicantLookupGender');
        const lookupContact   = document.getElementById('applicantLookupContact');
        const conditionBadge  = document.getElementById('applicantConditionBadge');
        const lookupMedCertResult = document.getElementById('applicantLookupMedCertResult');
        const lookupMedCertDetails = document.getElementById('applicantLookupMedCertDetails');
        const lookupXrayResult = document.getElementById('applicantLookupXrayResult');
        const lookupXrayDetails = document.getElementById('applicantLookupXrayDetails');
        const nurseReviewPanel = document.getElementById('applicantNurseReviewPanel');
        const informationButton = document.getElementById('btnViewApplicantInformation');
        const medicalConditionButton = document.getElementById('btnViewMedicalCondition');
        const medicalConditionModal = document.getElementById('applicantMedicalConditionModal');
        const closeMedicalConditionModalButton = document.getElementById('closeApplicantMedicalConditionModal');
        const medicalConditionDetails = document.getElementById('applicantMedicalConditionDetails');
        const medicalConditionSection = document.querySelector('#applicantRefModal .applicant-medical-condition-section');
        const viewConditionStatus = document.getElementById('applicantViewConditionStatus');
        const viewMedicalCondition = document.getElementById('applicantViewMedicalCondition');
        const viewConditionReasons = document.getElementById('applicantViewConditionReasons');
        const viewConditionRemarks = document.getElementById('applicantViewConditionRemarks');
        const documentsButton = document.getElementById('btnViewApplicantDocuments');
        const pendingHistoryWrap = document.getElementById('applicantPendingHistoryWrap');
        const pendingHistoryButton = document.getElementById('btnViewPendingHistory');
        const pendingHistoryReason = document.getElementById('applicantPendingHistoryReason');
        const pendingHistoryOther = document.getElementById('applicantPendingHistoryOther');
        const savedAssessmentButton = document.getElementById('btnViewSavedAssessment');
        const copyReferenceButton = document.getElementById('copyApplicantReference');
        const documentsCount  = document.getElementById('applicantDocumentsCount');
        const documentsModal  = document.getElementById('applicantDocumentsModal');
        const documentsModalSubtitle = document.getElementById('applicantDocumentsModalSubtitle');
        const documentsInitialEmpty = document.getElementById('applicantDocumentsInitialEmpty');
        const documentsGrid   = document.getElementById('applicantDocumentsGrid');
        const closeDocuments  = document.getElementById('closeApplicantDocumentsModal');
        const savedAssessmentModal = document.getElementById('savedAssessmentModal');
        const closeSavedAssessment = document.getElementById('closeSavedAssessmentModal');
        const lookupModalBadge = document.getElementById('lookupModalBadge');
        const lookupModalTitle = document.getElementById('lookupModalTitle');
        const lookupModalSubtitle = document.getElementById('lookupModalSubtitle');
        const lookupModalEntryTitle = document.getElementById('lookupModalEntryTitle');
        const lookupModalEntrySubtitle = document.getElementById('lookupModalEntrySubtitle');
        const lookupModalEntryButtonText = document.getElementById('lookupModalEntryButtonText');
        const lookupModalHelpCopy = document.getElementById('lookupModalHelpCopy');
        const lookupModalFieldLabel = document.getElementById('lookupModalFieldLabel');
        const previewPanel    = document.getElementById('applicantDocumentPreviewPanel');
        const previewTitle    = document.getElementById('applicantDocumentPreviewTitle');
        const previewFrame    = document.getElementById('applicantDocumentPreviewFrame');
        const previewImage    = document.getElementById('applicantDocumentPreviewImage');
        const previewEmpty    = document.getElementById('applicantDocumentPreviewEmpty');

        const getApplicantInitials = (name) => {
            const parts = String(name || '').trim().split(/\s+/).filter(Boolean);

            if (parts.length === 0) {
                return 'AP';
            }

            const firstInitial = parts[0]?.charAt(0) || '';
            const lastInitial = parts.length > 1 ? (parts[parts.length - 1]?.charAt(0) || '') : '';

            return (firstInitial + lastInitial).toUpperCase() || 'AP';
        };
        const previewOpen     = document.getElementById('applicantDocumentPreviewOpen');
        const approvalOverlay = document.getElementById('applicantApprovalOverlay');
        const approvalOverlayTitle = document.getElementById('applicantApprovalOverlayTitle');
        const approvalOverlayMessage = document.getElementById('applicantApprovalOverlayMessage');

        function setReviewDraftButtonVisible(visible) {
            if (reviewDraftBtn) {
                reviewDraftBtn.style.display = visible ? 'inline-flex' : 'none';
            }

            applicantRefActions?.classList.toggle('has-draft-action', Boolean(visible));
        }
        const encodeOverlay   = document.getElementById('applicantEncodeOverlay');
        let activeSuccessAction = null;
        let activeSuccessTimer = null;
        let currentLookupRef  = '';
        let currentDocuments  = [];
        let currentLookupMode = 'applicant';
        let currentApplicantWorkflow = 'select';
        let finalReviewPage = 1;
        let currentLookupRedirect = '';
        let currentAssessmentReview = {};
        const getStudentUrl   = '{{ url($basePrefix . '/walkin/get-student') }}';
        const finalReviewApplicantsUrl = '{{ url($basePrefix . '/walkin/final-review-applicants') }}';
        const finalReviewTimeInUrl = '{{ url($basePrefix . '/walkin/final-review/time-in') }}';
        const saveEncodingUrl = '{{ url($basePrefix . '/walkin/applicant-encoding') }}';
        const applicantFinalReviewDraftUrl = '{{ url($basePrefix . '/walkin/applicant-final-review-draft') }}';
        const healthInfoUpdateBaseUrl = '{{ url($basePrefix . '/walkin/health-profile-information') }}';
        const healthInfoTabs = document.getElementById('healthInfoTabs');
        const healthInfoFields = document.getElementById('healthInfoFields');
        const healthInfoSectionTitle = document.getElementById('healthInfoSectionTitle');
        const healthInfoEditBtn = document.getElementById('healthInfoEditBtn');
        const healthInfoCancelBtn = document.getElementById('healthInfoCancelBtn');
        const healthInfoSaveBtn = document.getElementById('healthInfoSaveBtn');
        let currentHealthInfoData = {};
        let currentHealthInfoSection = 'personal_information';
        let isHealthInfoEditing = false;

        function isClinicLookupMode() {
            return currentLookupMode === 'clinic';
        }

        function isEncodeWorkflow() {
            return currentApplicantWorkflow === 'encode' && !isClinicLookupMode();
        }

        function isFinalReviewWorkflow() {
            return currentApplicantWorkflow === 'final_review' && !isClinicLookupMode();
        }

        function positionApplicantFileActions() {
            if (!applicantFileActions || !entryPane) return;

            if (isClinicLookupMode()) {
                if (refStatus && applicantFileActions.nextElementSibling !== refStatus) {
                    entryPane.insertBefore(applicantFileActions, refStatus);
                }
                return;
            }

            if (isFinalReviewWorkflow() && finalReviewActionRow) {
                if (applicantFileActions.parentElement !== finalReviewActionRow) {
                    finalReviewActionRow.appendChild(applicantFileActions);
                }
                return;
            }

            if (medicalConditionSection && applicantFileActions.nextElementSibling !== medicalConditionSection) {
                entryPane.insertBefore(applicantFileActions, medicalConditionSection);
            }
        }

        function syncDocumentsModalCopy() {
            const emptyCopy = isClinicLookupMode()
                ? "No uploaded documents are available for this employee."
                : "No uploaded documents are available for this applicant.";

            if (documentsModalSubtitle) {
                documentsModalSubtitle.textContent = isClinicLookupMode()
                    ? "View the employee's submitted clinic requirements and Health Information Form."
                    : "View the applicant's submitted clinic requirements and Health Information Form.";
            }
            if (documentsInitialEmpty) {
                documentsInitialEmpty.textContent = emptyCopy;
            }
            return emptyCopy;
        }

        function closeHealthInfoModal() {
            if (!healthInfoModal) return;
            healthInfoModal.classList.remove('show');
            healthInfoModal.setAttribute('aria-hidden', 'true');
            if (informationButton) {
                informationButton.setAttribute('aria-expanded', 'false');
                const label = informationButton.querySelector('[data-information-button-label]');
                if (label) label.textContent = 'Health Information Form';
            }
        }

        function closeMedicalConditionModal() {
            if (!medicalConditionModal) return;
            medicalConditionModal.classList.remove('show');
            medicalConditionModal.setAttribute('aria-hidden', 'true');
            if (medicalConditionButton) {
                medicalConditionButton.setAttribute('aria-expanded', 'false');
                const label = medicalConditionButton.querySelector('[data-condition-button-label]');
                if (label) label.textContent = 'Medical Condition';
            }
        }

        function closeClinicSuccessOverlay(overlay, shouldRunAction = true) {
            if (!overlay) return;
            overlay.classList.remove('is-open');
            overlay.setAttribute('aria-hidden', 'true');
            if (activeSuccessTimer) {
                clearTimeout(activeSuccessTimer);
                activeSuccessTimer = null;
            }
            const nextAction = activeSuccessAction;
            activeSuccessAction = null;
            if (shouldRunAction && typeof nextAction === 'function') {
                nextAction();
            }
        }

        function showClinicSuccessOverlay(overlay, options = {}) {
            if (!overlay) return;
            if (approvalOverlayTitle && options.title && overlay === approvalOverlay) {
                approvalOverlayTitle.textContent = options.title;
            }
            if (approvalOverlayMessage && options.message && overlay === approvalOverlay) {
                approvalOverlayMessage.textContent = options.message;
            }
            activeSuccessAction = typeof options.onDone === 'function' ? options.onDone : null;
            overlay.classList.add('is-open');
            overlay.setAttribute('aria-hidden', 'false');
            if (activeSuccessTimer) clearTimeout(activeSuccessTimer);
            activeSuccessTimer = setTimeout(() => closeClinicSuccessOverlay(overlay, true), options.duration || 3000);
        }

        document.querySelectorAll('[data-success-close]').forEach(function (button) {
            button.addEventListener('click', function () {
                closeClinicSuccessOverlay(button.closest('.clinic-success-overlay'), true);
            });
        });

        function applyLookupMode(mode) {
            currentLookupMode = mode === 'clinic' ? 'clinic' : 'applicant';
            if (isClinicLookupMode()) {
                currentApplicantWorkflow = 'review';
            }

            if (modalShell) {
                modalShell.classList.toggle('is-employee-lookup', isClinicLookupMode());
                modalShell.classList.toggle('is-applicant-lookup', !isClinicLookupMode());
                if (isClinicLookupMode()) {
                    modalShell.classList.remove('is-encode-workflow', 'is-final-review-workflow', 'is-final-review-toolbar-stuck');
                }
            }
            positionApplicantFileActions();
            syncDocumentsModalCopy();

            if (lookupModalBadge) lookupModalBadge.textContent = isClinicLookupMode() ? 'ID' : 'AP';
            if (lookupModalTitle) lookupModalTitle.textContent = isClinicLookupMode() ? "Employee's / Students" : 'Applicants';
            if (lookupModalSubtitle) lookupModalSubtitle.textContent = isClinicLookupMode()
                ? 'Enter an employee number or student number to look up local employee records.'
                : "Enter the applicant's reference number to look up the record.";
            if (lookupModalEntryTitle) lookupModalEntryTitle.textContent = isClinicLookupMode() ? "Employee's / Student ID Lookup" : 'Reference Lookup';
            if (lookupModalEntrySubtitle) lookupModalEntrySubtitle.textContent = isClinicLookupMode()
                ? 'Use the employee number or student number to open the saved local employee record.'
                : 'Choose encoding for the first station or final review for approval.';
            if (lookupModalEntryButtonText) lookupModalEntryButtonText.textContent = isClinicLookupMode() ? 'Input ID Number' : 'Input Reference Number';
            if (lookupModalHelpCopy) {
                lookupModalHelpCopy.innerHTML = isClinicLookupMode()
                    ? 'Enter the <strong>employee number</strong> or <strong>student number</strong> saved in the employee record.'
                    : "Find the reference number in the <strong>Admission System</strong> under the applicant's profile or registration form.";
            }
            if (lookupModalFieldLabel) lookupModalFieldLabel.textContent = isClinicLookupMode() ? 'ID Number' : 'Reference Number';
            if (refInput) refInput.placeholder = isClinicLookupMode() ? 'Enter employee number or student number' : 'Enter reference number';

            if (workflowChoices) workflowChoices.style.display = isClinicLookupMode() ? 'none' : 'grid';
            if (showEntryBtn) showEntryBtn.style.display = isClinicLookupMode() ? 'inline-flex' : 'none';
            if (finalReviewList) finalReviewList.classList.remove('is-visible');
            if (backToFinalReviewList) backToFinalReviewList.classList.remove('is-visible');
        }

        function setApplicantWorkflow(workflow) {
            currentApplicantWorkflow = workflow;
            const isEncode = workflow === 'encode';
            const isFinalReview = workflow === 'final_review';

            if (modalShell) {
                modalShell.classList.toggle('is-encode-workflow', isEncode);
                modalShell.classList.toggle('is-final-review-workflow', isFinalReview);
            }
            if (lookupModalTitle) lookupModalTitle.textContent = isEncode ? 'Encode Assessment' : (isFinalReview ? 'Final Review' : 'Applicants');
            if (lookupModalSubtitle) lookupModalSubtitle.textContent = isEncode
                ? "Record the applicant's vital signs during the nurse review."
                : (isFinalReview ? 'Review encoded applicants and approve or mark pending compliance.' : "Enter the applicant's reference number to look up the record.");
            if (lookupModalEntryTitle) lookupModalEntryTitle.textContent = isEncode ? 'Assessment Encoding' : (isFinalReview ? 'Encoded Applicants' : 'Applicant Workflow');
            if (lookupModalEntrySubtitle) lookupModalEntrySubtitle.textContent = isEncode
                ? 'Use the applicant reference number to open the record for physical assessment.'
                : (isFinalReview ? 'Select a ready applicant below or use reference lookup.' : 'Choose encoding for the first station or final review for approval.');
            if (lookupModalEntryButtonText) lookupModalEntryButtonText.textContent = isEncode ? 'Input Reference Number' : 'Reference Lookup';
            if (lookupModalHelpCopy) lookupModalHelpCopy.innerHTML = isEncode
                ? 'Encode only the physical assessment here. Final approval and PUPTAS sync remain in <strong>Final Review</strong>.'
                : "Find the reference number in the <strong>Admission System</strong> under the applicant's profile or registration form.";
            positionApplicantFileActions();
        }

        function resetLookupButtonToFind() {
            if (!findBtn) return;

            setReviewDraftButtonVisible(false);

            findBtn.textContent = 'Find';
            findBtn.disabled = false;
            findBtn.style.opacity = '';
            findBtn.style.cursor = 'pointer';
            findBtn.style.background = '';
            findBtn.style.color = '';
            findBtn.style.fontWeight = '';
            findBtn.style.fontSize = '';
            findBtn.style.letterSpacing = '';
            findBtn.style.boxShadow = '';
            findBtn.style.border = '';
            findBtn.onclick = null;
            findBtn.removeEventListener('click', doLookup);
            findBtn.removeEventListener('click', doApprove);
            findBtn.removeEventListener('click', saveApplicantEncoding);
            findBtn.removeEventListener('click', enterSavedReviewEditMode);
            findBtn.addEventListener('click', doLookup);
        }

        function closeDocumentPreview() {
            if (previewFrame) {
                previewFrame.removeAttribute('src');
                previewFrame.style.display = 'none';
            }
            if (previewImage) {
                previewImage.removeAttribute('src');
                previewImage.style.display = 'none';
            }
            if (previewTitle) previewTitle.textContent = 'Document Preview';
            if (previewEmpty) previewEmpty.style.display = 'flex';
            if (previewOpen) {
                previewOpen.removeAttribute('href');
                previewOpen.style.display = 'none';
            }
            if (documentsGrid) {
                documentsGrid.querySelectorAll('.applicant-document-card').forEach(function (card) {
                    card.classList.remove('is-active');
                });
            }
        }

        function previewDocument(documentItem, activeCard) {
            if (!documentItem || !documentItem.url || !previewPanel) return;

            closeDocumentPreview();
            if (previewTitle) previewTitle.textContent = documentItem.label || 'Document Preview';
            if (previewEmpty) previewEmpty.style.display = 'none';
            if (previewOpen) {
                previewOpen.href = documentItem.url;
                previewOpen.style.display = 'inline-flex';
            }
            if (activeCard) activeCard.classList.add('is-active');

            if (documentItem.type === 'image' && previewImage) {
                previewImage.src = documentItem.url;
                previewImage.alt = (documentItem.label || 'Applicant document') + ' preview';
                previewImage.style.display = 'block';
            } else if (previewFrame) {
                previewFrame.src = documentItem.url;
                previewFrame.style.display = 'block';
            }

            previewPanel.classList.add('is-visible');
        }

        function closeDocumentsModal() {
            if (documentsModal) documentsModal.classList.remove('show');
            closeDocumentPreview();
        }

        function renderDocuments(documents) {
            currentDocuments = Array.isArray(documents) ? documents : [];

            if (documentsCount) documentsCount.textContent = String(currentDocuments.length);
            if (!documentsGrid) return;

            documentsGrid.replaceChildren();
            closeDocumentPreview();

            if (!currentDocuments.length) {
                const empty = document.createElement('div');
                empty.className = 'applicant-documents-empty';
                empty.textContent = syncDocumentsModalCopy();
                documentsGrid.appendChild(empty);
                return;
            }

            currentDocuments.forEach(function (documentItem) {
                const card = document.createElement('article');
                card.className = 'applicant-document-card';

                const icon = document.createElement('span');
                icon.className = 'applicant-document-icon';
                icon.textContent = documentItem.type === 'image' ? 'IMG' : (documentItem.type === 'form' ? 'FORM' : 'FILE');

                const copy = document.createElement('div');
                copy.className = 'applicant-document-copy';
                const title = document.createElement('strong');
                title.textContent = documentItem.label || 'Clinic Document';
                const type = document.createElement('span');
                type.textContent = documentItem.missing
                    ? 'Missing upload'
                    : (documentItem.type === 'form'
                    ? 'Official health form layout'
                    : (documentItem.type === 'image' ? 'Uploaded image' : 'Uploaded document'));
                copy.append(title, type);

                const actions = document.createElement('div');
                actions.className = 'applicant-document-actions';

                if (documentItem.missing || !documentItem.url) {
                    const missingBadge = document.createElement('span');
                    missingBadge.className = 'applicant-document-view is-disabled';
                    missingBadge.textContent = 'Missing';
                    actions.append(missingBadge);
                    card.classList.add('is-missing');
                } else {
                    const previewButton = document.createElement('button');
                    previewButton.type = 'button';
                    previewButton.className = 'applicant-document-view';
                    previewButton.textContent = 'Preview';
                    previewButton.addEventListener('click', function () {
                        previewDocument(documentItem, card);
                    });

                    const openLink = document.createElement('a');
                    openLink.className = 'applicant-document-view';
                    openLink.href = documentItem.url || '#';
                    openLink.target = '_blank';
                    openLink.rel = 'noopener noreferrer';
                    openLink.textContent = 'Open in New Tab';

                    actions.append(previewButton, openLink);
                }
                card.append(icon, copy, actions);
                documentsGrid.appendChild(card);
            });

            const firstPreviewableIndex = currentDocuments.findIndex((documentItem) => documentItem.url && !documentItem.missing);
            const firstCard = documentsGrid.querySelectorAll('.applicant-document-card')[firstPreviewableIndex];
            if (firstCard && currentDocuments[firstPreviewableIndex]) previewDocument(currentDocuments[firstPreviewableIndex], firstCard);
        }

        function setEntryMode(isActive) {
            if (defaultPane) defaultPane.style.display = isActive ? 'none' : 'flex';
            if (entryPane) entryPane.classList.toggle('is-visible', isActive);
            if (backToFinalReviewList) {
                backToFinalReviewList.classList.toggle('is-visible', isActive && currentApplicantWorkflow === 'final_review' && !isClinicLookupMode());
            }
            if (!isActive) resetLookupState();
            if (isActive && refInput) {
                setTimeout(() => refInput.focus(), 0);
            }
        }

        function resetLookupState() {
            if (modalShell) modalShell.classList.remove('has-lookup-result');
            if (modalShell && !isClinicLookupMode()) {
                modalShell.classList.remove('is-employee-lookup');
                modalShell.classList.add('is-applicant-lookup');
            }
            positionApplicantFileActions();
            renderDocuments([]);
            closeHealthInfoModal();
            closeMedicalConditionModal();
            closeDocumentsModal();
            if (refStatus) { refStatus.className = 'ocr-status'; refStatus.textContent = ''; }
            if (foundCard) {
                foundCard.style.display = 'none';
                foundCard.dataset.initials = 'AP';
            }
            if (foundName) foundName.textContent = '';
            if (lookupDetails) lookupDetails.style.display = 'none';
            lookupDetails?.classList.remove('is-summary-visible');
            if (informationDetails) {
                informationDetails.classList.remove('is-visible');
                informationDetails.setAttribute('aria-hidden', 'true');
            }
            if (medicalConditionDetails) {
                medicalConditionDetails.classList.remove('is-visible');
                medicalConditionDetails.setAttribute('aria-hidden', 'true');
            }
            if (informationButton) {
                informationButton.classList.remove('is-visible');
                const label = informationButton.querySelector('[data-information-button-label]');
                if (label) label.textContent = 'Health Information Form';
                informationButton.setAttribute('aria-expanded', 'false');
            }
            if (medicalConditionButton) {
                medicalConditionButton.classList.remove('is-visible');
                const label = medicalConditionButton.querySelector('[data-condition-button-label]');
                if (label) label.textContent = 'Medical Condition';
                medicalConditionButton.setAttribute('aria-expanded', 'false');
            }
            if (conditionBadge) {
                conditionBadge.classList.remove('has-condition');
                conditionBadge.textContent = 'Condition: No Medical Condition';
            }
            if (documentsButton) documentsButton.classList.remove('is-visible');
            if (pendingHistoryWrap) pendingHistoryWrap.classList.remove('is-open');
            if (pendingHistoryButton) {
                pendingHistoryButton.classList.remove('is-visible');
                pendingHistoryButton.setAttribute('aria-expanded', 'false');
            }
            if (pendingHistoryReason) pendingHistoryReason.textContent = '-';
            if (pendingHistoryOther) pendingHistoryOther.textContent = '-';
            currentHealthInfoData = {};
            currentHealthInfoSection = 'personal_information';
            setHealthInfoEditing(false);
            if (savedAssessmentButton) savedAssessmentButton.classList.remove('is-visible');
            if (savedAssessmentModal) {
                savedAssessmentModal.classList.remove('show');
                savedAssessmentModal.setAttribute('aria-hidden', 'true');
            }
            currentLookupRef = '';
            currentLookupRedirect = '';
            currentAssessmentReview = {};
            if (!isClinicLookupMode()) currentApplicantWorkflow = 'select';
            if (defaultPane) defaultPane.style.display = 'flex';
            const introCopy = defaultPane?.querySelector('.applicant-ref-copy');
            if (introCopy) introCopy.style.display = '';
            if (entryPane) entryPane.classList.remove('is-visible');
            if (workflowChoices) workflowChoices.style.display = isClinicLookupMode() ? 'none' : 'grid';
            if (finalReviewList) finalReviewList.classList.remove('is-visible');
            if (showEntryBtn) showEntryBtn.style.display = isClinicLookupMode() ? 'inline-flex' : 'none';
            if (backToFinalReviewList) backToFinalReviewList.classList.remove('is-visible');
            if (medicalConditionSection) {
                medicalConditionSection.classList.remove('show');
                medicalConditionSection.style.removeProperty('display');
            }
            document.querySelectorAll('input[name="applicant_findings_status"]').forEach(function (input) {
                input.checked = false;
            });
            document.querySelectorAll('input[name="applicant_clearance_decision"]').forEach(function (input) {
                input.checked = false;
            });
            const conditionFields = document.getElementById('applicantConditionFields');
            const clearanceDecisionFields = document.getElementById('applicantClearanceDecisionFields');
            const pendingDecisionFields = document.getElementById('applicantPendingDecisionFields');
            const normalRemarksFields = document.getElementById('applicantNormalRemarksFields');
            const findingRemarksField = document.getElementById('applicantFindingRemarksField');
            if (conditionFields) conditionFields.style.display = 'none';
            if (clearanceDecisionFields) clearanceDecisionFields.style.display = 'none';
            if (pendingDecisionFields) pendingDecisionFields.style.display = 'none';
            if (normalRemarksFields) normalRemarksFields.style.display = 'none';
            if (findingRemarksField) findingRemarksField.style.display = 'none';
            ['applicantMedicalCondition', 'applicantFindingRemarks', 'applicantConditionRemarks', 'applicantNormalRemarks', 'applicantHeight', 'applicantWeight', 'applicantBloodPressure', 'applicantPulseRate', 'applicantRespiratoryRate', 'applicantTemperature', 'applicantCovidPositiveDate', 'applicantOtherPendingReasonText', 'applicantEncodeRemarks'].forEach(function (id) {
                const field = document.getElementById(id);
                if (field) field.value = '';
            });
            const encodeRemarksInput = document.getElementById('applicantEncodeRemarks');
            if (encodeRemarksInput) {
                encodeRemarksInput.readOnly = false;
                encodeRemarksInput.placeholder = 'Optional assessment notes from the encoding station...';
            }
            setFinalReviewPhysicalReadonly(false);
            document.querySelectorAll('input[name="applicant_covid_positive"]').forEach(function (input) {
                input.checked = false;
                input.disabled = false;
            });
            document.querySelectorAll('input[name="resubmission_required_documents[]"]').forEach(function (input) {
                input.checked = false;
            });
            ['applicantHasMedicalCondition', 'applicantIncompleteRequirements', 'applicantNeedsPhysicianEvaluation', 'applicantNeedsFurtherEvaluation', 'applicantNeedsHealthFormCorrection', 'applicantOtherPendingReason'].forEach(function (id) {
                const field = document.getElementById(id);
                if (field) field.checked = false;
            });
            if (typeof syncFindingsReviewFields === 'function') syncFindingsReviewFields();
            if (typeof syncCovidPositiveFields === 'function') syncCovidPositiveFields();

            // Show input form elements again
            const lookupRow = document.querySelector('.applicant-ref-lookup-row');
            if (lookupRow) lookupRow.style.display = 'flex';

            // Reset button text and events back to Find mode
            resetLookupButtonToFind();

            if (modalShell) {
                modalShell.classList.remove('is-final-review-toolbar-stuck');
            }
            if (applicantFileActions) {
                delete applicantFileActions.dataset.stickyOriginTop;
            }
            if (applicantModalBody) {
                applicantModalBody.style.alignItems = 'center';
                applicantModalBody.style.justifyContent = 'center';
                applicantModalBody.style.minHeight = '220px';
                applicantModalBody.scrollTop = 0;
            }
        }

        function syncFinalReviewToolbarState() {
            if (!modalShell || !applicantModalBody || !applicantFileActions) return;
            const canStick = modalShell.classList.contains('has-lookup-result')
                && modalShell.classList.contains('is-final-review-workflow');

            if (!canStick) {
                modalShell.classList.remove('is-final-review-toolbar-stuck');
                delete applicantFileActions.dataset.stickyOriginTop;
                return;
            }

            if (!applicantFileActions.dataset.stickyOriginTop || !modalShell.classList.contains('is-final-review-toolbar-stuck')) {
                applicantFileActions.dataset.stickyOriginTop = String(Math.max(0, applicantFileActions.offsetTop));
            }

            const stickyOriginTop = parseFloat(applicantFileActions.dataset.stickyOriginTop || '0');
            const shouldStick = applicantModalBody.scrollTop >= Math.max(1, stickyOriginTop - 2);
            modalShell.classList.toggle('is-final-review-toolbar-stuck', shouldStick);
        }

        function openApplicantsModal() {
            if (!backdrop) return;
            applyLookupMode('applicant');
            setApplicantWorkflow('select');
            backdrop.classList.add('show');
            setEntryMode(false);
            if (refInput) refInput.value = '';
        }

        function openClinicLookupModal() {
            if (!backdrop) return;
            applyLookupMode('clinic');
            currentApplicantWorkflow = 'review';
            backdrop.classList.add('show');
            setEntryMode(false);
            if (refInput) refInput.value = '';
        }

        function closeApplicantsModal() {
            if (backdrop) backdrop.classList.remove('show');
            closeHealthInfoModal();
            closeMedicalConditionModal();
            setEntryMode(false);
            if (refInput) refInput.value = '';
        }

        function setStatus(type, msg) {
            if (!refStatus) return;
            refStatus.className = 'ocr-status ' + type;
            refStatus.textContent = msg;
        }

        function setFinalReviewPhysicalReadonly(locked) {
            const physicalFieldIds = [
                'applicantHeight',
                'applicantWeight',
                'applicantBloodPressure',
                'applicantPulseRate',
                'applicantRespiratoryRate',
                'applicantTemperature',
                'applicantCovidPositiveDate'
            ];

            physicalFieldIds.forEach(function (id) {
                const field = document.getElementById(id);
                if (field) field.readOnly = Boolean(locked);
            });

            document.querySelectorAll('input[name="applicant_covid_positive"]').forEach(function (input) {
                input.disabled = Boolean(locked);
            });

            const physicalPanel = document.querySelector('#applicantRefModal .applicant-medical-condition-section > section:not(.applicant-review-panel)');
            if (physicalPanel) {
                physicalPanel.classList.toggle('is-readonly-review', Boolean(locked));
            }
        }

        function populateAssessmentReview(review) {
            const savedReview = review && typeof review === 'object' ? review : {};
            const isChecked = (value) => value === true || value === 1 || value === '1';

            document.querySelectorAll('input[name="applicant_findings_status"]').forEach(function (input) {
                input.checked = input.value === (savedReview.findings_status || '');
            });
            document.querySelectorAll('input[name="applicant_clearance_decision"]').forEach(function (input) {
                input.checked = input.value === (savedReview.clearance_decision || 'approve');
            });

            const fieldValues = {
                applicantMedicalCondition: savedReview.medical_condition || '',
                applicantFindingRemarks: savedReview.med_assessment_remarks || savedReview.approval_remarks || savedReview.condition_remarks || savedReview.pending_remarks || '',
                applicantConditionRemarks: savedReview.condition_remarks || savedReview.pending_remarks || savedReview.med_assessment_remarks || savedReview.normal_remarks || '',
                applicantNormalRemarks: savedReview.normal_remarks || savedReview.approval_remarks || savedReview.med_assessment_remarks || savedReview.condition_remarks || savedReview.pending_remarks || '',
                applicantHeight: savedReview.height !== null && savedReview.height !== undefined && savedReview.height !== '' ? formatHeightFeet(savedReview.height) : '',
                applicantWeight: savedReview.weight ?? '',
                applicantBloodPressure: savedReview.blood_pressure || '',
                applicantPulseRate: savedReview.pulse_rate ?? '',
                applicantRespiratoryRate: savedReview.respiratory_rate ?? '',
                applicantTemperature: savedReview.temperature ?? '',
                applicantCovidPositiveDate: savedReview.covid_positive_date || '',
                applicantOtherPendingReasonText: savedReview.other_pending_reason || '',
                applicantEncodeRemarks: savedReview.encode_remarks || ''
            };
            Object.entries(fieldValues).forEach(function ([id, value]) {
                const field = document.getElementById(id);
                if (field) field.value = value;
            });

            const checkboxValues = {
                applicantHasMedicalCondition: savedReview.has_medical_condition,
                applicantIncompleteRequirements: savedReview.incomplete_requirements,
                applicantNeedsPhysicianEvaluation: savedReview.needs_physician_evaluation,
                applicantNeedsFurtherEvaluation: savedReview.needs_further_evaluation,
                applicantNeedsHealthFormCorrection: savedReview.needs_health_form_correction,
                applicantOtherPendingReason: Boolean(savedReview.other_pending_reason)
            };
            Object.entries(checkboxValues).forEach(function ([id, value]) {
                const field = document.getElementById(id);
                if (field) field.checked = isChecked(value) || value === true;
            });
            const savedResubmissionDocuments = Array.isArray(savedReview.resubmission_required_documents)
                ? savedReview.resubmission_required_documents
                : [];
            document.querySelectorAll('input[name="resubmission_required_documents[]"]').forEach(function (input) {
                input.checked = savedResubmissionDocuments.includes(input.value);
            });

            document.querySelectorAll('input[name="applicant_covid_positive"]').forEach(function (input) {
                input.checked = input.value === (savedReview.covid_positive || '');
            });

            if (typeof syncFindingsReviewFields === 'function') {
                syncFindingsReviewFields();
            }
            if (typeof syncCovidPositiveFields === 'function') {
                syncCovidPositiveFields();
            }
            if (typeof validateVitals === 'function') {
                validateVitals();
            }
        }

        function employeeDraftStorageKey(reference) {
            return 'employee-health-draft:' + String(reference || '').trim().toUpperCase();
        }

        function readLocalEmployeeDraft(reference) {
            try {
                const saved = localStorage.getItem(employeeDraftStorageKey(reference));
                return saved ? JSON.parse(saved) : {};
            } catch (error) {
                return {};
            }
        }

        function applicantFinalReviewDraftStorageKey(reference) {
            return 'applicant-final-review-draft:' + String(reference || '').trim().toUpperCase();
        }

        function readLocalApplicantFinalReviewDraft(reference) {
            try {
                const saved = localStorage.getItem(applicantFinalReviewDraftStorageKey(reference));
                return saved ? JSON.parse(saved) : {};
            } catch (error) {
                return {};
            }
        }

        function clearLocalApplicantFinalReviewDraft(reference) {
            try {
                localStorage.removeItem(applicantFinalReviewDraftStorageKey(reference));
            } catch (error) {
                // The server draft is already cleared when the decision is finalized.
            }
        }

        function applyReviewDraftFields(savedDraft) {
            if (!Object.keys(savedDraft).length) return;

            document.querySelectorAll('#applicantRefModal [name]').forEach(function (field) {
                const baseName = field.name.endsWith('[]') ? field.name.slice(0, -2) : field.name;
                if (!Object.prototype.hasOwnProperty.call(savedDraft, baseName)) return;

                const savedValue = savedDraft[baseName];
                if (field.type === 'checkbox' || field.type === 'radio') {
                    const values = Array.isArray(savedValue) ? savedValue.map(String) : [String(savedValue ?? '')];
                    field.checked = values.includes(String(field.value));
                } else if (field.tagName === 'SELECT' || field.type !== 'file') {
                    field.value = Array.isArray(savedValue) ? (savedValue[0] ?? '') : (savedValue ?? '');
                }
            });

            syncFindingsReviewFields();
            syncCovidPositiveFields();
            validateVitals();
        }

        function populateEmployeeDraft(draft) {
            if (!isClinicLookupMode()) return;
            const serverDraft = draft && typeof draft === 'object' ? draft : {};
            const savedDraft = Object.keys(serverDraft).length ? serverDraft : readLocalEmployeeDraft(currentLookupRef);
            applyReviewDraftFields(savedDraft);

            document.getElementById('employeeExamFit')?.dispatchEvent(new Event('change', { bubbles: true }));
            updateEmployeeBmi();
            updateEmployeeExamValidation();
        }

        function populateApplicantFinalReviewDraft(draft) {
            if (!isFinalReviewWorkflow()) return;
            const serverDraft = draft && typeof draft === 'object' ? draft : {};
            const savedDraft = Object.keys(serverDraft).length
                ? serverDraft
                : readLocalApplicantFinalReviewDraft(currentLookupRef);
            applyReviewDraftFields(savedDraft);
            setFinalReviewPhysicalReadonly(true);
        }

        function renderPendingHistory(review) {
            const savedReview = review && typeof review === 'object' ? review : {};
            const pendingReasons = [];

            if (savedReview.incomplete_requirements) pendingReasons.push('Document Resubmission');
            if (savedReview.needs_physician_evaluation) pendingReasons.push('For Physician Evaluation');
            if (savedReview.needs_further_evaluation) pendingReasons.push('For Further Evaluation');
            if (savedReview.needs_health_form_correction) pendingReasons.push('Health Form Correction');
            if (savedReview.other_pending_reason) pendingReasons.push('Others');

            const remarks = String(savedReview.condition_remarks || savedReview.pending_remarks || savedReview.med_assessment_remarks || savedReview.normal_remarks || '').trim();
            const otherReason = String(savedReview.other_pending_reason || '').trim();
            const hasHistory = pendingReasons.length > 0 || remarks !== '' || otherReason !== '';

            if (pendingHistoryReason) pendingHistoryReason.textContent = pendingReasons.length ? pendingReasons.join(', ') : '-';
            if (pendingHistoryOther) {
                const otherText = [otherReason, remarks].filter(Boolean).join(otherReason && remarks ? ' - ' : '');
                pendingHistoryOther.textContent = otherText || '-';
            }
            if (pendingHistoryWrap) pendingHistoryWrap.classList.remove('is-open');
            if (pendingHistoryButton) {
                pendingHistoryButton.classList.toggle('is-visible', hasHistory && (isFinalReviewWorkflow() || isClinicLookupMode()));
                pendingHistoryButton.setAttribute('aria-expanded', 'false');
            }
        }

        function hasSavedAssessmentReview(review) {
            if (!review || typeof review !== 'object') return false;
            const status = String(review.clearance_status || '').trim();
            return Boolean(review.findings_status && [
                'Pending/Conditional',
                'Pending Resubmission'
            ].includes(status));
        }

        function isEncodedForFinalReview(review, data) {
            const status = String(review?.clearance_status || data?.clearance_status || data?.clinic_status || '').trim();
            const physicalStatus = String(review?.physical_assessment_status || data?.physical_assessment_status || '').trim();

            return status === 'For Final Review'
                || physicalStatus === 'Encoded / For Final Review';
        }

        function numericValue(value) {
            const parsed = parseFloat(String(value ?? '').replace(/[^0-9.]/g, ''));
            return Number.isFinite(parsed) ? parsed : null;
        }

        function parseHeightFeet(value) {
            const rawValue = String(value ?? '').trim();
            if (!rawValue) return null;

            const feetInchesMatch = rawValue.match(/^(\d+)\s*(?:'|ft|feet)\s*(\d{1,2})?\s*(?:"|in|inch|inches)?\s*$/i);
            if (feetInchesMatch) {
                const feetPart = Number(feetInchesMatch[1]);
                const inchesPart = feetInchesMatch[2] === undefined ? 0 : Number(feetInchesMatch[2]);
                if (!Number.isFinite(feetPart) || !Number.isFinite(inchesPart) || inchesPart < 0 || inchesPart > 11) {
                    return null;
                }
                return feetPart + (inchesPart / 12);
            }

            const decimalFeetMatch = rawValue.match(/^\d+(?:\.\d+)?(?:\s*ft)?$/i);
            if (decimalFeetMatch) {
                return numericValue(rawValue);
            }

            return null;
        }

        function formatHeightFeet(value) {
            const rawValue = String(value ?? '').trim();
            if (!rawValue) return 'N/A';
            const parsedFeet = parseHeightFeet(rawValue);
            if (parsedFeet !== null) {
                const totalInches = Math.round(parsedFeet * 12);
                const feetPart = Math.floor(totalInches / 12);
                const inchesPart = totalInches % 12;
                return feetPart + "'" + inchesPart + '"';
            }
            if (/ft|in/i.test(rawValue)) return rawValue;
            let feet = numericValue(rawValue);
            if (/cm/i.test(rawValue) || feet > 10) {
                feet = feet / 30.48;
            }
            return feet ? Number(feet.toFixed(2)) + ' ft' : 'N/A';
        }

        function formatWeightPounds(value) {
            const rawValue = String(value ?? '').trim();
            if (!rawValue) return 'N/A';
            if (/lbs?|pounds?/i.test(rawValue)) return rawValue;
            let pounds = numericValue(rawValue);
            if (/kg/i.test(rawValue)) {
                pounds = pounds * 2.20462;
            }
            return pounds ? Number(pounds.toFixed(2)) + ' lbs' : 'N/A';
        }

        function setVitalStatus(id, text, tone = 'muted') {
            const status = document.getElementById(id);
            if (!status) return;
            status.textContent = text;
            status.className = 'vital-status is-' + tone;
        }

        function classifyBmi(heightFeet, weightPounds) {
            if (!heightFeet || !weightPounds || heightFeet <= 0 || weightPounds <= 0) {
                return null;
            }

            const heightInches = heightFeet * 12;
            const bmi = (weightPounds * 703) / (heightInches * heightInches);
            const rounded = Number(bmi.toFixed(1));

            if (rounded < 18.5) {
                return { text: 'Underweight', tone: 'warning', bmi: rounded, category: 'Underweight', categoryKey: 'underweight' };
            }
            if (rounded < 25) {
                return { text: 'Normal', tone: 'normal', bmi: rounded, category: 'Normal', categoryKey: 'normal' };
            }
            if (rounded < 30) {
                return { text: 'Overweight', tone: 'warning', bmi: rounded, category: 'Overweight', categoryKey: 'overweight' };
            }

            return { text: 'Obese', tone: 'danger', bmi: rounded, category: 'Obese', categoryKey: 'obese' };
        }

        function formatPounds(value) {
            return Number(value.toFixed(1)) + ' lbs';
        }

        function setBmiGaugeClass(card, categoryKey) {
            if (!card) return;
            card.classList.remove('is-underweight', 'is-normal', 'is-overweight', 'is-obese');
            if (categoryKey) {
                card.classList.add('is-' + categoryKey);
            }
        }

        function bmiNeedleDegrees(bmi) {
            const minBmi = 12;
            const maxBmi = 45;
            const clamped = Math.min(maxBmi, Math.max(minBmi, bmi));
            return -90 + ((clamped - minBmi) / (maxBmi - minBmi)) * 180;
        }

        function updateBmiGauge(heightFeet, weightPounds, bmiStatus) {
            const card = document.getElementById('bmiGaugeCard');
            const title = document.getElementById('bmiGaugeTitle');
            const category = document.getElementById('bmiGaugeCategory');
            const value = document.getElementById('bmiGaugeValue');
            const needle = document.getElementById('bmiGaugeNeedle');
            const healthyWeight = document.getElementById('bmiHealthyWeight');
            const weightGoal = document.getElementById('bmiWeightGoal');
            const bmiPrime = document.getElementById('bmiPrime');
            const ponderal = document.getElementById('bmiPonderal');

            if (!card || !title || !category || !value || !needle) return;

            if (!bmiStatus || !heightFeet || !weightPounds || heightFeet <= 0 || weightPounds <= 0) {
                setBmiGaugeClass(card, '');
                title.textContent = 'BMI pending';
                category.textContent = 'Enter height and weight';
                value.textContent = 'BMI --';
                needle.style.transform = 'translateX(-50%) rotate(-90deg)';
                if (healthyWeight) healthyWeight.textContent = 'Healthy weight for this height: --';
                if (weightGoal) weightGoal.textContent = 'Enter height and weight to calculate BMI.';
                if (bmiPrime) bmiPrime.textContent = 'BMI Prime: --';
                if (ponderal) ponderal.textContent = 'Ponderal Index: --';
                return;
            }

            const heightInches = heightFeet * 12;
            const healthyMin = (18.5 * heightInches * heightInches) / 703;
            const healthyMax = (25 * heightInches * heightInches) / 703;
            const heightMeters = heightFeet * 0.3048;
            const weightKg = weightPounds * 0.45359237;
            const ponderalIndex = weightKg / Math.pow(heightMeters, 3);
            const bmi = bmiStatus.bmi;

            setBmiGaugeClass(card, bmiStatus.categoryKey);
            title.textContent = 'BMI = ' + bmi + ' kg/m2';
            category.textContent = '(' + bmiStatus.category + ')';
            value.textContent = 'BMI = ' + bmi;
            needle.style.transform = 'translateX(-50%) rotate(' + bmiNeedleDegrees(bmi).toFixed(1) + 'deg)';
            if (healthyWeight) {
                healthyWeight.textContent = 'Healthy weight for this height: ' + formatPounds(healthyMin) + ' - ' + formatPounds(healthyMax);
            }
            if (weightGoal) {
                if (bmi < 18.5) {
                    weightGoal.textContent = 'Gain ' + formatPounds(healthyMin - weightPounds) + ' to reach a BMI of 18.5.';
                } else if (bmi >= 25) {
                    weightGoal.textContent = 'Lose ' + formatPounds(weightPounds - healthyMax) + ' to reach a BMI of 25.';
                } else {
                    weightGoal.textContent = 'Weight is within the healthy BMI range.';
                }
            }
            if (bmiPrime) bmiPrime.textContent = 'BMI Prime: ' + Number((bmi / 25).toFixed(2));
            if (ponderal) ponderal.textContent = 'Ponderal Index: ' + Number(ponderalIndex.toFixed(1)) + ' kg/m3';
        }

        function classifyBloodPressure(value) {
            const match = String(value ?? '').trim().match(/^(\d{2,3})\s*\/\s*(\d{2,3})$/);
            if (!match) return null;

            const systolic = Number(match[1]);
            const diastolic = Number(match[2]);
            if (!Number.isFinite(systolic) || !Number.isFinite(diastolic)) return null;
            if (systolic < 90 || diastolic < 60) return { text: 'Low', tone: 'warning' };
            if (systolic > 120 || diastolic > 80) return { text: 'High', tone: 'warning' };

            return { text: 'Normal', tone: 'normal' };
        }

        function classifyNumberRange(value, normalMin, normalMax, invalidMin, invalidMax, lowText = 'Low', highText = 'High') {
            const number = Number(value);
            if (!Number.isFinite(number)) return null;
            if (number < invalidMin || number > invalidMax) return { text: 'Invalid', tone: 'danger' };
            if (number < normalMin) return { text: lowText, tone: 'warning' };
            if (number > normalMax) return { text: highText, tone: 'warning' };

            return { text: 'Normal', tone: 'normal' };
        }

        function updateVitalIndicators() {
            const heightInput = document.getElementById('applicantHeight');
            const weightInput = document.getElementById('applicantWeight');
            const bpInput = document.getElementById('applicantBloodPressure');
            const prInput = document.getElementById('applicantPulseRate');
            const rrInput = document.getElementById('applicantRespiratoryRate');
            const tempInput = document.getElementById('applicantTemperature');
            const covidValue = document.querySelector('input[name="applicant_covid_positive"]:checked')?.value || '';

            const heightFeet = parseHeightFeet(heightInput?.value);
            const weightPounds = Number(weightInput?.value);
            let bmiStatus = null;

            if (!heightInput?.value) {
                setVitalStatus('heightStatus', 'Pending');
            } else if (heightFeet === null || heightFeet < 1 || heightFeet > 10) {
                setVitalStatus('heightStatus', 'Invalid', 'danger');
            } else {
                setVitalStatus('heightStatus', 'Valid', 'normal');
            }

            if (!weightInput?.value) {
                setVitalStatus('weightStatus', 'BMI pending');
            } else if (!Number.isFinite(weightPounds) || weightPounds < 1 || weightPounds > 1100) {
                setVitalStatus('weightStatus', 'Invalid', 'danger');
            } else {
                bmiStatus = classifyBmi(heightFeet, weightPounds);
                if (bmiStatus) {
                    setVitalStatus('weightStatus', bmiStatus.text, bmiStatus.tone);
                } else {
                    setVitalStatus('weightStatus', 'BMI needs height', 'muted');
                }
            }

            updateBmiGauge(heightFeet, weightInput?.value ? weightPounds : null, bmiStatus);

            const bpStatus = bpInput?.value ? classifyBloodPressure(bpInput.value) : null;
            setVitalStatus('bpStatus', bpInput?.value ? (bpStatus?.text || 'Invalid') : 'Pending', bpStatus?.tone || (bpInput?.value ? 'danger' : 'muted'));

            const pulseStatus = prInput?.value ? classifyNumberRange(prInput.value, 60, 100, 1, 300) : null;
            setVitalStatus('pulseStatus', prInput?.value ? (pulseStatus?.text || 'Invalid') : 'Pending', pulseStatus?.tone || (prInput?.value ? 'danger' : 'muted'));

            const respiratoryStatus = rrInput?.value ? classifyNumberRange(rrInput.value, 12, 20, 1, 120) : null;
            setVitalStatus('respiratoryStatus', rrInput?.value ? (respiratoryStatus?.text || 'Invalid') : 'Pending', respiratoryStatus?.tone || (rrInput?.value ? 'danger' : 'muted'));

            const temperatureStatus = tempInput?.value ? classifyNumberRange(tempInput.value, 36.5, 37.5, 30, 45, 'Low', Number(tempInput?.value) > 38.5 ? 'High fever' : 'High') : null;
            setVitalStatus('temperatureStatus', tempInput?.value ? (temperatureStatus?.text || 'Invalid') : 'Pending', temperatureStatus?.tone || (tempInput?.value ? 'danger' : 'muted'));

            if (!covidValue) {
                setVitalStatus('covidStatus', 'Pending');
            } else if (covidValue === 'No') {
                setVitalStatus('covidStatus', 'Negative', 'normal');
            } else {
                setVitalStatus('covidStatus', 'Positive', 'danger');
            }
        }

        function updateEmployeeBmi() {
            const heightInput = document.querySelector('[name="employee_exam_height"]');
            const weightInput = document.querySelector('[name="employee_exam_weight"]');
            const bmiInput = document.querySelector('[name="employee_exam_bmi"]');
            if (!heightInput || !weightInput || !bmiInput) return;

            const heightFeet = parseHeightFeet(heightInput.value);
            const weightPounds = Number(weightInput.value);
            const bmiStatus = classifyBmi(heightFeet, weightPounds);

            bmiInput.value = bmiStatus ? bmiStatus.bmi : '';
            const bmiCategory = document.getElementById('employeeBmiCategory');
            if (bmiCategory) {
                bmiCategory.textContent = bmiStatus ? bmiStatus.category : 'Pending';
                bmiCategory.className = 'employee-bmi-category' + (bmiStatus ? ' is-' + bmiStatus.categoryKey : '');
            }
            updateBmiGauge(heightFeet, weightPounds, bmiStatus);
            updateEmployeeExamValidation();
        }

        function setEmployeeExamValidation(id, text, isValid) {
            const status = document.getElementById(id);
            if (!status) return;
            status.textContent = text;
            status.className = 'employee-exam-validation ' + (isValid === null ? '' : (isValid ? 'is-valid' : 'is-invalid'));
        }

        function updateEmployeeExamValidation() {
            const value = (name) => document.querySelector('[name="' + name + '"]')?.value?.trim() || '';
            const height = value('employee_exam_height');
            const heightFeet = parseHeightFeet(height);
            setEmployeeExamValidation('employeeHeightValidation', !height ? 'Pending' : (heightFeet !== null && heightFeet >= 1 && heightFeet <= 10 ? 'Valid' : 'Invalid'), !height ? null : (heightFeet !== null && heightFeet >= 1 && heightFeet <= 10));

            const weight = Number(value('employee_exam_weight'));
            setEmployeeExamValidation('employeeWeightValidation', !value('employee_exam_weight') ? 'Pending' : (Number.isFinite(weight) && weight > 0 && weight <= 1100 ? 'Valid' : 'Invalid'), !value('employee_exam_weight') ? null : (Number.isFinite(weight) && weight > 0 && weight <= 1100));

            const bp = value('employee_exam_bp');
            const bpStatus = bp ? classifyBloodPressure(bp) : null;
            setEmployeeExamValidation('employeeBpValidation', !bp ? 'Pending' : (bpStatus ? 'Valid' : 'Invalid'), !bp ? null : Boolean(bpStatus));

            const hr = Number(value('employee_exam_hr'));
            setEmployeeExamValidation('employeeHrValidation', !value('employee_exam_hr') ? 'Pending' : (Number.isFinite(hr) && hr >= 1 && hr <= 300 ? 'Valid' : 'Invalid'), !value('employee_exam_hr') ? null : (Number.isFinite(hr) && hr >= 1 && hr <= 300));

            const rr = Number(value('employee_exam_rr'));
            setEmployeeExamValidation('employeeRrValidation', !value('employee_exam_rr') ? 'Pending' : (Number.isFinite(rr) && rr >= 1 && rr <= 120 ? 'Valid' : 'Invalid'), !value('employee_exam_rr') ? null : (Number.isFinite(rr) && rr >= 1 && rr <= 120));

            const temperature = Number(value('employee_exam_temperature'));
            setEmployeeExamValidation('employeeTemperatureValidation', !value('employee_exam_temperature') ? 'Pending' : (Number.isFinite(temperature) && temperature >= 30 && temperature <= 45 ? 'Valid' : 'Invalid'), !value('employee_exam_temperature') ? null : (Number.isFinite(temperature) && temperature >= 30 && temperature <= 45));
        }

        function getConditionSummary(review, lookupData) {
            const savedReview = review && typeof review === 'object' ? review : {};
            const conditionItems = Array.isArray(lookupData?.medical_condition_summary)
                ? lookupData.medical_condition_summary.filter(function (item) {
                    return item && String(item.value || '').trim() !== '';
                })
                : [];
            const hasProfileCondition = lookupData?.has_medical_condition === true
                || lookupData?.has_medical_condition === 1
                || lookupData?.has_medical_condition === '1'
                || conditionItems.length > 0;
            const hasReviewCondition = savedReview.has_medical_condition === true
                || savedReview.has_medical_condition === 1
                || savedReview.has_medical_condition === '1'
                || String(savedReview.medical_condition || '').trim() !== '';
            const hasCondition = hasProfileCondition || hasReviewCondition;
            const pendingReasons = [];
            if (savedReview.incomplete_requirements) pendingReasons.push('Document Resubmission');
            if (savedReview.needs_physician_evaluation) pendingReasons.push('For Physician Evaluation');
            if (savedReview.needs_further_evaluation) pendingReasons.push('For Further Evaluation');
            if (savedReview.needs_health_form_correction) pendingReasons.push('Health Form Correction');
            if (savedReview.other_pending_reason) pendingReasons.push('Others: ' + savedReview.other_pending_reason);
            const profileConditionText = conditionItems.map(function (item) {
                return (item.label ? item.label + ': ' : '') + item.value;
            }).join('; ');

            return {
                hasCondition,
                status: hasCondition ? 'With Medical Condition' : 'No Medical Condition',
                condition: savedReview.medical_condition || profileConditionText || 'N/A',
                reasons: pendingReasons.join(', ') || 'N/A',
                remarks: savedReview.med_assessment_remarks || savedReview.condition_remarks || savedReview.normal_remarks || 'N/A'
            };
        }

        function renderMedicalConditionView(review, lookupData) {
            const summary = getConditionSummary(review, lookupData || {});

            if (conditionBadge) {
                conditionBadge.classList.toggle('has-condition', summary.hasCondition);
                conditionBadge.textContent = 'Condition: ' + summary.status;
            }
            if (viewConditionStatus) viewConditionStatus.textContent = summary.status;
            if (viewMedicalCondition) viewMedicalCondition.textContent = summary.condition;
            if (viewConditionReasons) viewConditionReasons.textContent = summary.reasons;
            if (viewConditionRemarks) viewConditionRemarks.textContent = summary.remarks;

            return summary;
        }

        function renderSavedAssessmentReview(review, referenceNumber) {
            const savedReview = review && typeof review === 'object' ? review : {};
            const pendingReasons = [];
            if (savedReview.incomplete_requirements) pendingReasons.push('Document Resubmission');
            if (savedReview.needs_physician_evaluation) pendingReasons.push('For Physician Evaluation');
            if (savedReview.needs_further_evaluation) pendingReasons.push('For Further Evaluation');
            if (savedReview.needs_health_form_correction) pendingReasons.push('Health Form Correction');
            if (savedReview.other_pending_reason) pendingReasons.push('Others: ' + savedReview.other_pending_reason);

            const values = {
                savedReviewResult: savedReview.findings_status || '-',
                savedReviewReasons: pendingReasons.join(', ') || '-',
                savedReviewCondition: savedReview.medical_condition || '-',
                savedReviewRemarks: savedReview.med_assessment_remarks || savedReview.condition_remarks || savedReview.normal_remarks || '-',
                savedReviewHeight: savedReview.height !== null && savedReview.height !== undefined && savedReview.height !== '' ? formatHeightFeet(savedReview.height) : '-',
                savedReviewWeight: savedReview.weight !== null && savedReview.weight !== undefined && savedReview.weight !== '' ? formatWeightPounds(savedReview.weight) : '-',
                savedReviewBloodPressure: savedReview.blood_pressure || '-',
                savedReviewPulseRate: savedReview.pulse_rate !== null && savedReview.pulse_rate !== undefined && savedReview.pulse_rate !== '' ? savedReview.pulse_rate + ' bpm' : '-',
                savedReviewRespiratoryRate: savedReview.respiratory_rate !== null && savedReview.respiratory_rate !== undefined && savedReview.respiratory_rate !== '' ? savedReview.respiratory_rate + ' cpm' : '-',
                savedReviewTemperature: savedReview.temperature !== null && savedReview.temperature !== undefined && savedReview.temperature !== '' ? savedReview.temperature + ' °C' : '-',
                savedReviewCovidPositive: savedReview.covid_positive || '-',
                savedReviewCovidPositiveDate: savedReview.covid_positive_date || '-',
                savedReviewReference: referenceNumber || '-'
            };

            Object.entries(values).forEach(function ([id, value]) {
                const field = document.getElementById(id);
                if (field) field.textContent = value;
            });
        }

        function enterSavedReviewEditMode() {
            const medicalConditionSection = document.querySelector('.applicant-medical-condition-section');
            if (medicalConditionSection) {
                medicalConditionSection.classList.add('show');
                medicalConditionSection.style.display = 'grid';
                medicalConditionSection.scrollIntoView({ behavior: 'smooth', block: 'start' });
            }

            if (findBtn) {
                findBtn.removeEventListener('click', enterSavedReviewEditMode);
                findBtn.removeEventListener('click', doApprove);
                findBtn.addEventListener('click', doApprove);
            }
            syncFindingsReviewFields();
        }

        function escapeApplicantHtml(value) {
            return String(value ?? '').replace(/[&<>"']/g, function (character) {
                return {
                    '&': '&amp;',
                    '<': '&lt;',
                    '>': '&gt;',
                    '"': '&quot;',
                    "'": '&#039;'
                }[character];
            });
        }

        const healthInfoCourseOptions = [
            { code: 'BSBA-HRM', name: 'Bachelor of Science in Business Administration major in Human Resource Management' },
            { code: 'BSBA-MM', name: 'Bachelor of Science in Business Administration major in Marketing Management' },
            { code: 'BSECE', name: 'Bachelor of Science in Electronics Engineering' },
            { code: 'BSIT', name: 'Bachelor of Science in Information Technology' },
            { code: 'BSME', name: 'Bachelor of Science in Mechanical Engineering' },
            { code: 'BSOA', name: 'Bachelor of Science in Office Administration' },
            { code: 'BSPSY', name: 'Bachelor of Science in Psychology' },
            { code: 'BSED-ENGLISH', name: 'Bachelor of Secondary Education major in English' },
            { code: 'BSED-MATH', name: 'Bachelor of Secondary Education major in Mathematics' },
            { code: 'DIT', name: 'Diploma in Information Technology' },
            { code: 'DOMT', name: 'Diploma in Office Management Technology' },
        ];

        const healthInfoYesNoOptions = ['Yes', 'No'];
        const healthInfoIllnessOptions = [
            'Asthma',
            'Loss of Consciousness',
            'Eye Disease / Defect',
            'Accident Injuries',
            'Diabetes',
            'Heart Disease',
            'Kidney Disease',
            'Tuberculosis',
            'Convulsion / Epilepsy',
            'Migraine',
            'Hyperventilation',
            'High Blood Pressure',
            'Hemophilia',
            'Primary Complex',
            'Others',
        ];
        const healthInfoVaccineDoses = [
            ['first_dose', '1st Dose'],
            ['second_dose', '2nd Dose'],
            ['booster_1', 'Booster 1'],
            ['booster_2', 'Booster 2'],
        ];

        const healthInfoSections = [
            {
                key: 'personal_information',
                title: 'Personal Information',
                icon: 'PI',
                fields: [
                    ['full_name', 'Full Name', 'readonly', 'root'],
                    ['birthday', 'Date of Birth', 'date'],
                    ['age', 'Age', 'number'],
                    ['sex', 'Gender', 'select', null, { options: ['Male', 'Female'] }],
                    ['civil_status', 'Civil Status', 'select', null, { options: ['Single', 'Married', 'Widowed', 'Separated'] }],
                    ['blood_type', 'Blood Type', 'select', null, { options: ['Unknown', 'A+', 'A-', 'B+', 'B-', 'AB+', 'AB-', 'O+', 'O-'] }],
                    ['course_college', 'Course / Program', 'course'],
                    ['course_code', 'Course Code', 'readonly'],
                    ['school_year', 'School Year']
                ]
            },
            {
                key: 'contact_information',
                title: 'Contact Information',
                icon: 'CN',
                fields: [
                    ['contact_no', 'Contact Number'],
                    ['guardian_name', 'Emergency Contact Person'],
                    ['cellphone', 'Emergency Contact Person Number'],
                    ['landline', 'Landline']
                ]
            },
            {
                key: 'address_information',
                title: 'Address Information',
                icon: 'AD',
                fields: [
                    ['home_address', 'Home Address', 'textarea'],
                    ['zipcode', 'Zip Code']
                ]
            },
            {
                key: 'medical_history',
                title: 'Medical History',
                icon: 'MH',
                fields: [
                    ['has_illness', 'Known Medical Illness', 'select', null, { options: healthInfoYesNoOptions }],
                    ['medical_history', 'Medical History / Conditions', 'checkbox_group', null, { options: healthInfoIllnessOptions, dependsOn: ['has_illness', 'Yes'] }],
                    ['other_illness', 'Other Illness', 'textarea', null, { dependsOnCheckedValue: ['medical_history', 'Others'] }],
                    ['has_disability', 'Has Disability', 'select', null, { options: healthInfoYesNoOptions }],
                    ['disability_type', 'Disability Type', null, null, { dependsOn: ['has_disability', 'Yes'] }],
                    ['no_allergies', 'No Known Allergies', 'checkbox'],
                    ['has_food_allergies', 'Has Food Allergy', 'virtual_select', null, { options: healthInfoYesNoOptions }],
                    ['food_allergies', 'Food Allergies', 'textarea', null, { dependsOn: ['has_food_allergies', 'Yes'] }],
                    ['has_medicine_allergies', 'Has Medicine Allergy', 'virtual_select', null, { options: healthInfoYesNoOptions }],
                    ['medicine_allergies', 'Medicine Allergies', 'textarea', null, { dependsOn: ['has_medicine_allergies', 'Yes'] }],
                    ['other_med_allergies', 'Other Medicine Allergies', 'textarea', null, { dependsOn: ['has_medicine_allergies', 'Yes'] }]
                ]
            },
            {
                key: 'personal_social_history',
                title: 'Personal Social History',
                icon: 'SH',
                fields: [
                    ['is_smoker', 'Cigarette Smoking', 'select', null, { options: healthInfoYesNoOptions }],
                    ['is_drinker', 'Alcohol Drinking', 'select', null, { options: healthInfoYesNoOptions }],
                    ['covid_vaccinated', 'COVID Vaccinated', 'select', null, { options: healthInfoYesNoOptions }],
                    ['vaccine_history', 'COVID Vaccination Details', 'vaccine_history', null, { dependsOn: ['covid_vaccinated', 'Yes'] }]
                ]
            },
            {
                key: 'clinic_requirements',
                title: 'Clinic Requirements',
                icon: 'CR',
                fields: [
                    ['med_cert_findings', 'Medical Certificate Result'],
                    ['med_cert_findings_details', 'Medical Certificate Findings', 'textarea'],
                    ['doctor_name', 'Doctor Name'],
                    ['med_cert_date', 'Medical Certificate Date', 'date'],
                    ['xray_findings', 'Chest X-ray Result'],
                    ['xray_findings_details', 'Chest X-ray Findings', 'textarea'],
                    ['xray_date', 'Chest X-ray Date', 'date']
                ]
            }
        ];

        function activeHealthInfoSections() {
            return healthInfoSections;
        }

        function getHealthInfoFieldValue(sectionKey, fieldKey, sourceType) {
            if (sourceType === 'root') {
                return currentHealthInfoData?.[fieldKey] ?? '';
            }

            if (sectionKey === 'medical_history' && fieldKey === 'has_food_allergies') {
                return normalizeHealthInfoValue(currentHealthInfoData?.medical_history?.no_allergies) === 'Yes'
                    ? 'No'
                    : (normalizeHealthInfoValue(currentHealthInfoData?.medical_history?.food_allergies) !== '' ? 'Yes' : 'No');
            }

            if (sectionKey === 'medical_history' && fieldKey === 'has_medicine_allergies') {
                return normalizeHealthInfoValue(currentHealthInfoData?.medical_history?.no_allergies) === 'Yes'
                    ? 'No'
                    : (
                        normalizeHealthInfoValue(currentHealthInfoData?.medical_history?.medicine_allergies) !== '' ||
                        normalizeHealthInfoValue(currentHealthInfoData?.medical_history?.other_med_allergies) !== ''
                            ? 'Yes'
                            : 'No'
                    );
            }

            return currentHealthInfoData?.[sectionKey]?.[fieldKey] ?? '';
        }

        function normalizeHealthInfoValue(value) {
            if (value === true) return 'Yes';
            if (value === false) return 'No';
            return String(value ?? '').trim();
        }

        function healthInfoFieldId(fieldKey) {
            return 'healthInfo_' + String(fieldKey).replace(/[^A-Za-z0-9_-]/g, '_');
        }

        function getHealthInfoCurrentValue(sectionKey, fieldKey, sourceType) {
            const field = healthInfoFields?.querySelector(`[data-health-field="${fieldKey}"]`);
            if (field) {
                if (field.type === 'checkbox') {
                    return field.checked ? 'Yes' : 'No';
                }
                return field.value;
            }

            if (fieldKey === 'medical_history') {
                const checkedValues = Array.from(healthInfoFields?.querySelectorAll('[data-health-checkbox-group="medical_history"]:checked') || [])
                    .map(item => item.value);
                if (checkedValues.length) return checkedValues.join(', ');
            }

            return getHealthInfoFieldValue(sectionKey, fieldKey, sourceType);
        }

        function isHealthInfoFieldVisible(section, fieldConfig) {
            const [fieldKey, , , sourceType, config = {}] = fieldConfig;

            if (config.dependsOn) {
                const [dependencyKey, expectedValue] = config.dependsOn;
                const actual = normalizeHealthInfoValue(getHealthInfoCurrentValue(section.key, dependencyKey, sourceType));
                if (actual !== expectedValue) return false;
            }

            if (config.dependsOnAnyChecked) {
                const [groupKey] = config.dependsOnAnyChecked;
                const checkedValues = Array.from(healthInfoFields?.querySelectorAll(`[data-health-checkbox-group="${groupKey}"]:checked`) || []);
                if (!checkedValues.length) return false;
            }

            if (config.dependsOnCheckedValue) {
                const [groupKey, expectedValue] = config.dependsOnCheckedValue;
                const checkedValues = Array.from(healthInfoFields?.querySelectorAll(`[data-health-checkbox-group="${groupKey}"]:checked`) || [])
                    .map(item => item.value);
                if (!checkedValues.includes(expectedValue)) return false;
            }

            if (['has_food_allergies', 'food_allergies', 'has_medicine_allergies', 'medicine_allergies', 'other_med_allergies'].includes(fieldKey)) {
                const noAllergies = healthInfoFields?.querySelector('[data-health-field="no_allergies"]');
                if (noAllergies?.checked) return false;
            }

            return true;
        }

        function healthInfoOptionsMarkup(options, selectedValue, placeholder) {
            const normalizedSelected = normalizeHealthInfoValue(selectedValue);
            return [
                `<option value="">${escapeApplicantHtml(placeholder || 'Select')}</option>`,
                ...options.map(function (option) {
                    const value = typeof option === 'string' ? option : option.value;
                    const label = typeof option === 'string' ? option : option.label;
                    return `<option value="${escapeApplicantHtml(value)}" ${normalizeHealthInfoValue(value) === normalizedSelected ? 'selected' : ''}>${escapeApplicantHtml(label)}</option>`;
                })
            ].join('');
        }

        function renderHealthInfoInput(section, fieldKey, label, type, sourceType, config = {}) {
            const rawValue = getHealthInfoFieldValue(section.key, fieldKey, sourceType);
            const value = rawValue === true ? 'Yes' : (rawValue === false ? 'No' : String(rawValue ?? ''));
            const escapedValue = escapeApplicantHtml(value);
            const fieldId = healthInfoFieldId(fieldKey);
            const hiddenClass = isHealthInfoFieldVisible(section, [fieldKey, label, type, sourceType, config]) ? '' : ' is-hidden';
            const wrapperStart = `<div class="health-info-field${hiddenClass}" data-health-field-wrap="${escapeApplicantHtml(fieldKey)}">`;

            if (type === 'textarea') {
                return `
                    ${wrapperStart}
                        <label for="${fieldId}">${escapeApplicantHtml(label)}</label>
                        <textarea class="health-info-input" id="${fieldId}" data-health-field="${escapeApplicantHtml(fieldKey)}">${escapedValue}</textarea>
                    </div>
                `;
            }

            if (type === 'checkbox') {
                return `
                    ${wrapperStart}
                        <label>${escapeApplicantHtml(label)}</label>
                        <label class="health-info-check-option">
                            <input class="health-info-input" type="checkbox" data-health-field="${escapeApplicantHtml(fieldKey)}" ${rawValue ? 'checked' : ''}>
                            <span>${escapeApplicantHtml(label)}</span>
                        </label>
                    </div>
                `;
            }

            if (type === 'select' || type === 'virtual_select') {
                return `
                    ${wrapperStart}
                        <label for="${fieldId}">${escapeApplicantHtml(label)}</label>
                        <select class="health-info-input" id="${fieldId}" data-health-field="${escapeApplicantHtml(fieldKey)}">
                            ${healthInfoOptionsMarkup(config.options || [], value, 'Select ' + label.toLowerCase())}
                        </select>
                    </div>
                `;
            }

            if (type === 'course') {
                const selectedCode = normalizeHealthInfoValue(getHealthInfoFieldValue(section.key, 'course_code'));
                const selectedCourse = healthInfoCourseOptions.find(course => course.code === selectedCode)
                    || healthInfoCourseOptions.find(course => course.name === value);
                return `
                    ${wrapperStart}
                        <label for="${fieldId}">${escapeApplicantHtml(label)}</label>
                        <select class="health-info-input" id="${fieldId}" data-health-field="course_code" data-health-course-select>
                            ${healthInfoOptionsMarkup(healthInfoCourseOptions.map(course => ({
                                value: course.code,
                                label: course.code + ' - ' + course.name
                            })), selectedCourse?.code || selectedCode, 'Select course')}
                        </select>
                    </div>
                `;
            }

            if (type === 'checkbox_group') {
                const selectedItems = Array.isArray(rawValue)
                    ? rawValue.map(item => String(item))
                    : String(rawValue || '').split(',').map(item => item.trim()).filter(Boolean);
                if (
                    fieldKey === 'medical_history' &&
                    normalizeHealthInfoValue(getHealthInfoFieldValue(section.key, 'other_illness')) !== '' &&
                    !selectedItems.includes('Others')
                ) {
                    selectedItems.push('Others');
                }
                return `
                    ${wrapperStart}
                        <label>${escapeApplicantHtml(label)}</label>
                        <div class="health-info-checkbox-grid">
                            ${(config.options || []).map(function (option) {
                                const checked = selectedItems.includes(option);
                                return `
                                    <label class="health-info-check-option">
                                        <input type="checkbox" data-health-checkbox-group="${escapeApplicantHtml(fieldKey)}" value="${escapeApplicantHtml(option)}" ${checked ? 'checked' : ''}>
                                        <span>${escapeApplicantHtml(option)}</span>
                                    </label>
                                `;
                            }).join('')}
                        </div>
                    </div>
                `;
            }

            if (type === 'vaccine_history') {
                const history = rawValue && typeof rawValue === 'object' ? rawValue : {};
                return `
                    ${wrapperStart}
                        <label>${escapeApplicantHtml(label)}</label>
                        <div class="health-info-vaccine-grid">
                            ${healthInfoVaccineDoses.map(function ([doseKey, doseLabel]) {
                                const dose = history[doseKey] || {};
                                return `
                                    <div class="health-info-vaccine-dose">
                                        <strong>${escapeApplicantHtml(doseLabel)}</strong>
                                        <input class="health-info-input" type="date" data-health-vaccine-dose="${escapeApplicantHtml(doseKey)}" data-health-vaccine-field="date" value="${escapeApplicantHtml(dose.date || '')}">
                                        <input class="health-info-input" type="text" data-health-vaccine-dose="${escapeApplicantHtml(doseKey)}" data-health-vaccine-field="brand" value="${escapeApplicantHtml(dose.brand || '')}" placeholder="Brand">
                                    </div>
                                `;
                            }).join('')}
                        </div>
                    </div>
                `;
            }

            return `
                ${wrapperStart}
                    <label for="${fieldId}">${escapeApplicantHtml(label)}</label>
                    <input class="health-info-input" id="${fieldId}" type="${type === 'date' ? 'date' : (type === 'number' ? 'number' : 'text')}" data-health-field="${escapeApplicantHtml(fieldKey)}" value="${escapedValue}">
                </div>
            `;
        }

        function refreshHealthInfoConditionalVisibility() {
            if (!healthInfoFields) return;
            const sections = activeHealthInfoSections();
            const section = sections.find(item => item.key === currentHealthInfoSection) || sections[0];
            section.fields.forEach(function (fieldConfig) {
                const [fieldKey] = fieldConfig;
                const wrapper = healthInfoFields.querySelector(`[data-health-field-wrap="${fieldKey}"]`);
                if (!wrapper) return;
                wrapper.classList.toggle('is-hidden', !isHealthInfoFieldVisible(section, fieldConfig));
            });
        }

        function renderHealthInfoTabs() {
            if (!healthInfoTabs) return;
            const sections = activeHealthInfoSections();

            healthInfoTabs.style.display = '';

            healthInfoTabs.innerHTML = sections.map(function (section) {
                const activeClass = section.key === currentHealthInfoSection ? ' is-active' : '';
                return `
                    <button type="button" class="health-info-tab${activeClass}" data-health-info-section="${escapeApplicantHtml(section.key)}">
                        <span>${escapeApplicantHtml(section.icon)}</span>
                        <strong>${escapeApplicantHtml(section.title)}</strong>
                    </button>
                `;
            }).join('');
        }

        function renderHealthInfoFields() {
            if (!healthInfoFields) return;

            const sections = activeHealthInfoSections();
            const section = sections.find(item => item.key === currentHealthInfoSection) || sections[0];
            if (healthInfoSectionTitle) healthInfoSectionTitle.textContent = section.title;

            healthInfoFields.innerHTML = section.fields.map(function ([fieldKey, label, type, sourceType, config]) {
                const rawValue = getHealthInfoFieldValue(section.key, fieldKey, sourceType);
                let value = rawValue === true ? 'Yes' : (rawValue === false ? 'No' : String(rawValue ?? ''));
                if (type === 'vaccine_history' && rawValue && typeof rawValue === 'object') {
                    value = healthInfoVaccineDoses
                        .map(function ([doseKey, doseLabel]) {
                            const dose = rawValue[doseKey] || {};
                            const parts = [dose.date || '', dose.brand || ''].filter(Boolean);
                            return parts.length ? `${doseLabel}: ${parts.join(' - ')}` : '';
                        })
                        .filter(Boolean)
                        .join('\n');
                }
                const escapedValue = escapeApplicantHtml(value);
                const isReadonly = type === 'readonly';

                if (isHealthInfoEditing && !isReadonly) {
                    return renderHealthInfoInput(section, fieldKey, label, type, sourceType, config || {});
                }

                return `
                    <div class="health-info-field">
                        <label>${escapeApplicantHtml(label)}</label>
                        <div class="health-info-value">${escapedValue || 'N/A'}</div>
                    </div>
                `;
            }).join('');

            refreshHealthInfoConditionalVisibility();
            syncHealthInfoCourseFields();
        }

        function syncHealthInfoCourseFields() {
            if (!healthInfoFields) return;

            const courseSelect = healthInfoFields.querySelector('[data-health-course-select]');
            if (!courseSelect) return;

            const selectedCourse = healthInfoCourseOptions.find(course => course.code === courseSelect.value);
            const courseCodeValue = healthInfoFields.querySelector('[data-health-field-wrap="course_code"] .health-info-value');

            if (courseCodeValue) {
                courseCodeValue.textContent = selectedCourse?.code || 'N/A';
            }
        }

        function renderHealthInfoEditor(data) {
            currentHealthInfoData = data && typeof data === 'object' ? data : {};
            const sections = activeHealthInfoSections();
            if (!sections.some(section => section.key === currentHealthInfoSection)) {
                currentHealthInfoSection = 'personal_information';
            }
            renderHealthInfoTabs();
            renderHealthInfoFields();
            syncHealthInfoActions();
        }

        function syncHealthInfoActions() {
            const hideActions = false;
            if (healthInfoEditBtn) healthInfoEditBtn.style.display = hideActions || isHealthInfoEditing ? 'none' : 'inline-flex';
            if (healthInfoCancelBtn) healthInfoCancelBtn.style.display = !hideActions && isHealthInfoEditing ? 'inline-flex' : 'none';
            if (healthInfoSaveBtn) healthInfoSaveBtn.style.display = !hideActions && isHealthInfoEditing ? 'inline-flex' : 'none';
        }

        function setHealthInfoEditing(editing) {
            isHealthInfoEditing = Boolean(editing);
            syncHealthInfoActions();
            renderHealthInfoFields();
        }

        function collectHealthInfoFields() {
            const payload = {};

            healthInfoSections.forEach(function (section) {
                section.fields.forEach(function ([fieldKey, , type, sourceType]) {
                    if (type === 'readonly' || sourceType === 'root') return;

                    if (type === 'virtual_select') {
                        return;
                    }

                    if (type === 'course') {
                        const field = healthInfoFields?.querySelector('[data-health-course-select]');
                        if (!field) {
                            payload.course_code = currentHealthInfoData?.personal_information?.course_code || '';
                            payload.course_college = currentHealthInfoData?.personal_information?.course_college || '';
                            return;
                        }
                        const selectedCode = field ? field.value : '';
                        const selectedCourse = healthInfoCourseOptions.find(course => course.code === selectedCode);
                        payload.course_code = selectedCode;
                        payload.course_college = selectedCourse?.name || '';
                        return;
                    }

                    if (type === 'checkbox_group') {
                        const groupInputs = Array.from(healthInfoFields?.querySelectorAll(`[data-health-checkbox-group="${fieldKey}"]`) || []);
                        if (!groupInputs.length) {
                            payload[fieldKey] = currentHealthInfoData?.[section.key]?.[fieldKey] || '';
                            return;
                        }
                        const checkedValues = Array.from(healthInfoFields?.querySelectorAll(`[data-health-checkbox-group="${fieldKey}"]:checked`) || [])
                            .map(item => item.value);
                        payload[fieldKey] = checkedValues.join(', ');
                        return;
                    }

                    if (type === 'vaccine_history') {
                        const doseInputs = Array.from(healthInfoFields?.querySelectorAll('[data-health-vaccine-dose]') || []);
                        if (!doseInputs.length) {
                            payload.vaccine_history = currentHealthInfoData?.personal_social_history?.vaccine_history || {};
                            return;
                        }
                        const vaccineHistory = {};
                        healthInfoVaccineDoses.forEach(function ([doseKey]) {
                            const date = healthInfoFields?.querySelector(`[data-health-vaccine-dose="${doseKey}"][data-health-vaccine-field="date"]`)?.value || '';
                            const brand = healthInfoFields?.querySelector(`[data-health-vaccine-dose="${doseKey}"][data-health-vaccine-field="brand"]`)?.value || '';
                            if (date || brand) {
                                vaccineHistory[doseKey] = { date, brand };
                            }
                        });
                        payload.vaccine_history = vaccineHistory;
                        return;
                    }

                    const field = healthInfoFields?.querySelector(`[data-health-field="${fieldKey}"]`);
                    if (!field) {
                        const existing = currentHealthInfoData?.[section.key]?.[fieldKey];
                        payload[fieldKey] = existing === undefined ? '' : existing;
                        return;
                    }
                    payload[fieldKey] = field.type === 'checkbox' ? field.checked : field.value;
                });
            });

            if (payload.has_illness !== 'Yes') {
                payload.medical_history = '';
                payload.other_illness = '';
            }
            if (payload.has_disability !== 'Yes') {
                payload.disability_type = '';
            }
            if (payload.no_allergies) {
                payload.food_allergies = '';
                payload.medicine_allergies = '';
                payload.other_med_allergies = '';
            } else {
                const hasFoodAllergies = healthInfoFields?.querySelector('[data-health-field="has_food_allergies"]')?.value;
                const hasMedicineAllergies = healthInfoFields?.querySelector('[data-health-field="has_medicine_allergies"]')?.value;

                if (hasFoodAllergies === 'No') {
                    payload.food_allergies = '';
                }
                if (hasMedicineAllergies === 'No') {
                    payload.medicine_allergies = '';
                    payload.other_med_allergies = '';
                }
            }
            if (payload.covid_vaccinated !== 'Yes') {
                payload.vaccine_history = {};
            }

            return payload;
        }

        function saveHealthInfoEditor() {
            const profileId = currentHealthInfoData?.profile_id;
            if (!profileId) {
                setStatus('error', 'No health profile is loaded for editing.');
                return;
            }

            if (healthInfoSaveBtn) healthInfoSaveBtn.disabled = true;
            setStatus('info', 'Saving health form information...');

            fetch(`${healthInfoUpdateBaseUrl}/${profileId}`, {
                method: 'POST',
                headers: {
                    'X-Requested-With': 'XMLHttpRequest',
                    'Accept': 'application/json',
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                },
                body: JSON.stringify(collectHealthInfoFields())
            })
            .then(response => response.json())
            .then(data => {
                if (!data.success) {
                    throw new Error(data.message || 'Unable to update health form information.');
                }
                currentHealthInfoData = data.health_form_information || currentHealthInfoData;
                setHealthInfoEditing(false);
                setStatus('success', data.message || 'Health form information updated.');
            })
            .catch(error => {
                setStatus('error', error.message || 'Unable to update health form information.');
            })
            .finally(() => {
                if (healthInfoSaveBtn) healthInfoSaveBtn.disabled = false;
            });
        }

        function renderApplicantReviewSource(element, value, fallbackText) {
            if (!element) return;

            const normalizedValue = String(value || '').trim();
            element.textContent = normalizedValue && normalizedValue !== '-'
                ? normalizedValue
                : (fallbackText || 'N/A');
        }

        function showLookupDetails(data, fallbackRef) {
            console.log('showLookupDetails called with data:', data);

            if (!lookupDetails) {
                console.log('ERROR: lookupDetails element not found!');
                return false;
            }

            const referenceNumber = data.reference_number || fallbackRef || '-';
            const studentNumber = data.student_number || '';
            const yearSection = [data.year || '', data.section || ''].filter(Boolean).join(' / ') || 'N/A';

            console.log('Setting lookup values:', {
                lookupRef: referenceNumber,
                lookupStatus: data.clinic_status,
                lookupDob: data.dob,
                lookupEmail: data.email
            });

            if (lookupRef) lookupRef.textContent = referenceNumber;
            if (lookupStatus) lookupStatus.textContent = data.clinic_status || 'Awaiting Uploads';
            if (lookupCourse) lookupCourse.textContent = data.course || 'N/A';
            if (lookupYearSec) lookupYearSec.textContent = yearSection;
            if (lookupDob) lookupDob.textContent = data.dob || 'N/A';
            if (lookupEmail) lookupEmail.textContent = data.email || 'N/A';
            if (lookupCivilStatus) lookupCivilStatus.textContent = data.civil_status || 'N/A';
            if (lookupAge) lookupAge.textContent = data.age !== null && data.age !== undefined && data.age !== '' ? String(data.age) : 'N/A';
            if (lookupGender) lookupGender.textContent = data.sex || 'N/A';
            if (lookupContact) lookupContact.textContent = data.contact_number || 'N/A';
            renderApplicantReviewSource(
                lookupMedCertResult,
                data.medical_certificate_result || 'N/A',
                'For Clinic Review'
            );
            renderApplicantReviewSource(
                lookupMedCertDetails,
                data.medical_certificate_findings_details || '',
                'No findings declared'
            );
            renderApplicantReviewSource(
                lookupXrayResult,
                data.xray_result || 'N/A',
                'For Clinic Review'
            );
            renderApplicantReviewSource(
                lookupXrayDetails,
                data.xray_findings_details || '',
                'No findings declared'
            );

            console.log('Setting display styles...');
            lookupDetails.style.display = 'block';
            lookupDetails.classList.add('is-summary-visible');
            if (informationDetails) {
                informationDetails.classList.remove('is-visible');
                informationDetails.setAttribute('aria-hidden', 'true');
            }
            if (modalShell) modalShell.classList.add('has-lookup-result');
            renderDocuments(data.documents);
            if (informationButton) {
                informationButton.classList.add('is-visible');
                informationButton.setAttribute('aria-expanded', 'false');
                const label = informationButton.querySelector('[data-information-button-label]');
                if (label) label.textContent = 'Health Information Form';
            }
            renderHealthInfoEditor(data.health_form_information || {});
            if (medicalConditionButton) {
                medicalConditionButton.classList.remove('is-visible');
                medicalConditionButton.setAttribute('aria-expanded', 'false');
                const label = medicalConditionButton.querySelector('[data-condition-button-label]');
                if (label) label.textContent = 'Medical Condition';
            }
            if (documentsButton) documentsButton.classList.add('is-visible');
            if (foundCard) foundCard.style.display = 'flex';

            if (applicantModalBody) {
                console.log('Updating modal body layout...');
                applicantModalBody.style.alignItems = 'stretch';
                applicantModalBody.style.justifyContent = 'flex-start';
                applicantModalBody.style.minHeight = 'auto';
                applicantModalBody.scrollTop = 0;
            }
            if (applicantFileActions) {
                delete applicantFileActions.dataset.stickyOriginTop;
            }
            syncFinalReviewToolbarState();

            currentAssessmentReview = data.assessment_review && typeof data.assessment_review === 'object'
                ? data.assessment_review
                : {};
            const preferredHeight = currentAssessmentReview.height || data.height || '';
            const preferredWeight = currentAssessmentReview.weight || data.weight || '';
            if (lookupHeight) lookupHeight.textContent = formatHeightFeet(preferredHeight);
            if (lookupWeight) lookupWeight.textContent = formatWeightPounds(preferredWeight);
            const hasSavedReview = hasSavedAssessmentReview(currentAssessmentReview);
            renderSavedAssessmentReview(currentAssessmentReview, referenceNumber);
            const conditionSummary = renderMedicalConditionView(currentAssessmentReview, data);
            if (medicalConditionButton) {
                medicalConditionButton.classList.toggle('is-visible', Boolean(conditionSummary?.hasCondition));
            }
            if (savedAssessmentButton) {
                savedAssessmentButton.classList.toggle('is-visible', hasSavedReview);
            }
            renderPendingHistory(currentAssessmentReview);

            if (medicalConditionSection) {
                const shouldShowAssessment = isEncodeWorkflow() || isFinalReviewWorkflow() || !hasSavedReview;
                medicalConditionSection.classList.toggle('show', shouldShowAssessment);
                medicalConditionSection.style.display = shouldShowAssessment ? 'grid' : 'none';
            }

            if (nurseReviewPanel) {
                nurseReviewPanel.style.display = isEncodeWorkflow() ? 'none' : '';
            }

            populateAssessmentReview(currentAssessmentReview);
            const encodeRemarksInput = document.getElementById('applicantEncodeRemarks');
            if (encodeRemarksInput) {
                encodeRemarksInput.readOnly = isFinalReviewWorkflow();
                encodeRemarksInput.placeholder = isFinalReviewWorkflow()
                    ? 'No encode remarks recorded.'
                    : 'Optional notes from the encoding station...';
            }
            setFinalReviewPhysicalReadonly(isFinalReviewWorkflow());

            console.log('showLookupDetails completed');
            return hasSavedReview;
        }

        let isApprovalMode = false;

        function formatApplicantReferenceInput(value) {
            const compact = String(value || '').toUpperCase().replace(/[^A-Z0-9]/g, '');

            if (isClinicLookupMode()) {
                if (compact === '') return '';
                if (!compact.startsWith('CLN')) return compact.slice(0, 20);

                const suffix = compact.slice(3, 16);
                if (suffix === '') return 'CLN';
                const firstGroupLength = suffix.startsWith('20') ? 4 : 6;
                const firstGroup = suffix.slice(0, firstGroupLength);
                const remaining = suffix.slice(firstGroupLength);

                return 'CLN-' + firstGroup + (remaining ? '-' + remaining : '');
            }

            const admissionReference = compact.slice(0, 12);
            if (admissionReference.length <= 4) {
                return admissionReference;
            }
            if (admissionReference.length <= 8) {
                return admissionReference.slice(0, 4) + '-' + admissionReference.slice(4);
            }

            return admissionReference.slice(0, 4) + '-' + admissionReference.slice(4, 8) + '-' + admissionReference.slice(8);
        }

        function doLookup() {
            const ref = (refInput ? refInput.value : '').trim();
            if (!ref) {
                setStatus('error', isClinicLookupMode() ? 'Please enter an ID number first.' : 'Please enter a reference number first.');
                return;
            }

            if (!isClinicLookupMode() && ref.toUpperCase().startsWith('CLN-')) {
                setStatus('error', 'This is an Employee Reference, not an Applicant Reference. Please use the Employee lookup.');
                return;
            }

            setStatus('info', isClinicLookupMode() ? 'Looking up employee record...' : 'Looking up applicant...');
            if (foundCard) foundCard.style.display = 'none';
            if (documentsButton) documentsButton.classList.remove('is-visible');
            if (savedAssessmentButton) savedAssessmentButton.classList.remove('is-visible');

            const lookupUrl = new URL(getStudentUrl, window.location.origin);
            lookupUrl.searchParams.set('student_id', ref);
            lookupUrl.searchParams.set('preview_only', 'true');
            if (isClinicLookupMode()) {
                lookupUrl.searchParams.set('lookup_scope', 'employee_local');
            }

            fetch(lookupUrl.toString(), {
                headers: { 'X-Requested-With': 'XMLHttpRequest', 'Accept': 'application/json' }
            })
            .then(r => r.json())
            .then(data => {
                if (data.status === 'preview' || data.status === 'found') {
                    // Try multiple field name variations for applicant name
                    let applicantName = data.student_name || data.name || data.full_name || '';
                    if (!applicantName && data.first_name) {
                        const lastName = data.last_name ? ' ' + data.last_name : '';
                        applicantName = data.first_name + lastName;
                    }

                    currentLookupRef = data.reference_number || ref;
                    currentLookupRedirect = data.redirect_url || '';

                    // Check if applicant is already approved
                    const isAlreadyApproved = data.is_health_profile_completed === true
                        || data.is_health_profile_completed === 1
                        || data.medical_status === 'cleared'
                        || data.clinic_status === 'Fully Cleared'
                        || data.clearance_status === 'Fully Cleared'
                        || data.clearance_status === 'Issued'
                        || data.approved === true
                        || data.approved === 1;

                    const isLocalHealthProfile = data.lookup_source === 'local_health_profile';
                    const isLocalEmployeeReference = ['local_employee_reference', 'local_clinic_reference'].includes(data.lookup_source);
                    const isLocalEmployeeId = ['local_employee_id', 'local_clinic_id'].includes(data.lookup_source);
                    const isLocalOnlyLookup = isLocalHealthProfile || isLocalEmployeeReference || isLocalEmployeeId;
                    const lookupFoundMessage = isLocalHealthProfile
                        ? (data.sync_warning || 'Local health profile found. PUPTAS sync will still depend on a valid Admission reference.')
                        : (isClinicLookupMode()
                            ? (applicantName ? "Employee's record found: " + applicantName + '.' : "Employee's record found.")
                            : (applicantName ? 'Applicant found: ' + applicantName + '.' : 'Applicant found.'));

                    if (isAlreadyApproved) {
                        setStatus('approved', isClinicLookupMode()
                            ? "Employee's record is already cleared."
                            : 'Applicant Already Approved. This health profile has already been cleared by the clinic.');
                        if (foundCard && foundName) {
                            foundName.textContent = (applicantName || ref) + ' ✓';
                            foundCard.dataset.initials = getApplicantInitials(applicantName || ref);
                            foundCard.style.display = 'flex';
                        }
                        showLookupDetails(data, ref);
                        populateEmployeeDraft(data.employee_draft_data || {});
                        populateApplicantFinalReviewDraft(data.applicant_final_review_draft_data || {});
                        if (foundCard) {
                            foundCard.style.display = 'none';
                            foundCard.dataset.initials = 'AP';
                        }
                        if (foundName) foundName.textContent = '';
                        if (lookupDetails) lookupDetails.style.display = 'none';
                        lookupDetails?.classList.remove('is-summary-visible');
                        if (informationDetails) {
                            informationDetails.classList.remove('is-visible');
                            informationDetails.setAttribute('aria-hidden', 'true');
                        }
                        if (informationButton) {
                            informationButton.classList.remove('is-visible');
                            informationButton.setAttribute('aria-expanded', 'false');
                        }
                        if (documentsButton) documentsButton.classList.remove('is-visible');
                        renderDocuments([]);
                        const approvedMedicalConditionSection = document.querySelector('.applicant-medical-condition-section');
                        if (approvedMedicalConditionSection) {
                            approvedMedicalConditionSection.classList.remove('show');
                            approvedMedicalConditionSection.style.removeProperty('display');
                        }

                        // Hide input sections and show only results
                        if (defaultPane) defaultPane.style.display = 'none';

                        // Hide only the input form row, keep action buttons visible
                        const lookupRow = document.querySelector('.applicant-ref-lookup-row');
                        if (lookupRow) lookupRow.style.display = 'none';

                        isApprovalMode = false;
                        setReviewDraftButtonVisible(false);
                        if (findBtn) {
                            findBtn.removeEventListener('click', doLookup);
                            findBtn.removeEventListener('click', doApprove);
                            findBtn.removeEventListener('click', saveApplicantEncoding);
                            findBtn.removeEventListener('click', enterSavedReviewEditMode);

                            findBtn.textContent = '✓ Already Approved';
                            findBtn.disabled = true;
                            findBtn.style.background = 'linear-gradient(135deg, #10b981, #059669)';
                            findBtn.style.color = '#000000';
                            findBtn.style.opacity = '1';
                            findBtn.style.cursor = 'not-allowed';
                            findBtn.style.fontWeight = '700';
                            findBtn.style.fontSize = '14px';
                            findBtn.style.letterSpacing = '0.5px';
                            findBtn.style.boxShadow = '0 8px 16px rgba(16, 185, 129, 0.25)';
                            findBtn.style.border = '2px solid #059669';
                            findBtn.onclick = null;
                        }
                    } else {
                        setStatus(isLocalOnlyLookup ? 'info' : 'success', lookupFoundMessage);
                        if (foundCard && foundName) {
                            foundName.textContent = applicantName || ref;
                            foundCard.dataset.initials = getApplicantInitials(applicantName || ref);
                            foundCard.style.display = 'flex';
                        }
                        const hasSavedReview = showLookupDetails(data, ref);
                        populateEmployeeDraft(data.employee_draft_data || {});
                        populateApplicantFinalReviewDraft(data.applicant_final_review_draft_data || {});
                        const alreadyEncodedForReview = isEncodeWorkflow() && isEncodedForFinalReview(currentAssessmentReview, data);
                        if (alreadyEncodedForReview) {
                            setStatus('encoded', 'This applicant is already encoded and is ready for Final Review / Approval.');
                        }
                        // Hide input sections and show only results
                        if (defaultPane) defaultPane.style.display = 'none';

                        // Hide only the input form row, keep action buttons visible
                        const lookupRow = document.querySelector('.applicant-ref-lookup-row');
                        if (lookupRow) lookupRow.style.display = 'none';

                        // Change button mode based on the selected applicant workflow.
                        isApprovalMode = !isEncodeWorkflow();
                        if (findBtn) {
                            findBtn.textContent = alreadyEncodedForReview
                                ? 'Already Encoded'
                                : (isEncodeWorkflow()
                                ? 'Save Assessment'
                                : (isFinalReviewWorkflow() ? 'Approve' : (hasSavedReview ? 'Edit Review' : 'Approve')));
                            findBtn.disabled = alreadyEncodedForReview;
                            findBtn.style.opacity = alreadyEncodedForReview ? '0.72' : '1';
                            findBtn.style.cursor = alreadyEncodedForReview ? 'not-allowed' : 'pointer';
                            findBtn.removeEventListener('click', doLookup);
                            findBtn.removeEventListener('click', doApprove);
                            findBtn.removeEventListener('click', saveApplicantEncoding);
                            findBtn.removeEventListener('click', enterSavedReviewEditMode);
                            findBtn.onclick = null;
                            if (alreadyEncodedForReview) {
                                // Encoded applicants must proceed through Final Review so the webhook approval flow stays separate.
                            } else if (isEncodeWorkflow()) {
                                findBtn.addEventListener('click', saveApplicantEncoding);
                            } else if (isFinalReviewWorkflow()) {
                                findBtn.addEventListener('click', doApprove);
                            } else {
                                findBtn.addEventListener('click', hasSavedReview ? enterSavedReviewEditMode : doApprove);
                            }
                        }
                        setReviewDraftButtonVisible(isClinicLookupMode() || isFinalReviewWorkflow());
                        if (!hasSavedReview) syncFindingsReviewFields();
                    }
                } else {
                    setStatus('error', data.message || (isClinicLookupMode()
                        ? "No employee's record found with that ID number."
                        : 'No applicant found with that reference number.'));
                }
            })
            .catch(() => setStatus('error', 'Unable to look up right now. Please try again.'));
        }

        refInput?.addEventListener('input', () => {
            if (!isClinicLookupMode()) {
                refInput.value = formatApplicantReferenceInput(refInput.value);
            }
        });

        refInput?.addEventListener('blur', () => {
            if (!isClinicLookupMode()) {
                refInput.value = formatApplicantReferenceInput(refInput.value);
            }
        });

        function saveApplicantEncoding() {
            if (!currentLookupRef) {
                setStatus('error', 'No applicant to encode.');
                return;
            }

            const heightInput = document.getElementById('applicantHeight');
            const weightInput = document.getElementById('applicantWeight');
            const bloodPressureInput = document.getElementById('applicantBloodPressure');
            const pulseRateInput = document.getElementById('applicantPulseRate');
            const respiratoryRateInput = document.getElementById('applicantRespiratoryRate');
            const temperatureInput = document.getElementById('applicantTemperature');
            const covidPositiveInput = document.querySelector('input[name="applicant_covid_positive"]:checked');
            const covidPositiveDateInput = document.getElementById('applicantCovidPositiveDate');
            const encodeRemarksInput = document.getElementById('applicantEncodeRemarks');

            if (!heightInput?.value || !weightInput?.value || !bloodPressureInput?.value.trim() || !pulseRateInput?.value || !respiratoryRateInput?.value || !temperatureInput?.value) {
                setStatus('error', 'Please complete the height, weight, blood pressure, pulse rate, respiratory rate, and temperature fields.');
                return;
            }

            const heightValue = parseHeightFeet(heightInput.value);
            if (heightValue === null || heightValue < 1 || heightValue > 10) {
                setStatus('error', 'Height must use feet and inches, e.g., 5\'6".');
                return;
            }

            if (!covidPositiveInput) {
                setStatus('error', 'Please select if the student is COVID Positive.');
                return;
            }

            if (covidPositiveInput.value === 'Yes' && !covidPositiveDateInput?.value) {
                setStatus('error', 'Please enter the COVID Positive date.');
                return;
            }

            setStatus('info', 'Saving assessment for final review...');

            fetch(saveEncodingUrl, {
                method: 'POST',
                headers: {
                    'X-Requested-With': 'XMLHttpRequest',
                    'Accept': 'application/json',
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                },
                body: JSON.stringify({
                    reference_number: currentLookupRef,
                    height: heightInput.value,
                    weight: weightInput.value,
                    blood_pressure: bloodPressureInput.value.trim(),
                    pulse_rate: pulseRateInput.value,
                    respiratory_rate: respiratoryRateInput.value,
                    temperature: temperatureInput.value,
                    covid_positive: covidPositiveInput.value,
                    covid_positive_date: covidPositiveInput.value === 'Yes' ? covidPositiveDateInput.value : '',
                    encode_remarks: encodeRemarksInput?.value.trim() || ''
                })
            })
            .then(r => r.json())
            .then(data => {
                if (data.success) {
                    setStatus('success', data.message || 'Assessment saved for final review.');
                    showClinicSuccessOverlay(encodeOverlay, {
                        duration: 3000,
                        onDone: () => {
                            refreshFinalReviewApplicants()
                                .finally(showFinalReviewList);
                        }
                    });
                } else {
                    setStatus('error', data.message || 'Failed to save assessment.');
                }
            })
            .catch(() => setStatus('error', 'Unable to save assessment right now. Please try again.'));
        }

        function doApprove() {
            if (!currentLookupRef) {
                setStatus('error', 'No applicant to approve.');
                return;
            }

            const medicalConditionInput = document.getElementById('applicantMedicalCondition');
            const findingRemarksInput = document.getElementById('applicantFindingRemarks');
            const conditionRemarksInput = document.getElementById('applicantConditionRemarks');
            const normalRemarksInput = document.getElementById('applicantNormalRemarks');
            const findingsStatusInput = document.querySelector('input[name="applicant_findings_status"]:checked');
            const clearanceDecisionInput = document.querySelector('input[name="applicant_clearance_decision"]:checked');
            const medicalConditionCheckbox = document.getElementById('applicantHasMedicalCondition');
            const incompleteRequirementsCheckbox = document.getElementById('applicantIncompleteRequirements');
            const physicianEvaluationCheckbox = document.getElementById('applicantNeedsPhysicianEvaluation');
            const furtherEvaluationCheckbox = document.getElementById('applicantNeedsFurtherEvaluation');
            const healthFormCorrectionCheckbox = document.getElementById('applicantNeedsHealthFormCorrection');
            const otherPendingReasonCheckbox = document.getElementById('applicantOtherPendingReason');
            const otherPendingReasonInput = document.getElementById('applicantOtherPendingReasonText');
            const heightInput = document.getElementById('applicantHeight');
            const weightInput = document.getElementById('applicantWeight');
            const bloodPressureInput = document.getElementById('applicantBloodPressure');
            const pulseRateInput = document.getElementById('applicantPulseRate');
            const respiratoryRateInput = document.getElementById('applicantRespiratoryRate');
            const temperatureInput = document.getElementById('applicantTemperature');
            const covidPositiveInput = document.querySelector('input[name="applicant_covid_positive"]:checked');
            const covidPositiveDateInput = document.getElementById('applicantCovidPositiveDate');

            if (!findingsStatusInput) {
                setStatus('error', 'Please select the nurse findings review result.');
                return;
            }
            if (!clearanceDecisionInput) {
                setStatus('error', 'Please select the clearance decision.');
                return;
            }

            const hasFindings = findingsStatusInput.value === 'With Findings';
            const isPendingDecision = clearanceDecisionInput.value === 'pending';
            const hasMedicalCondition = Boolean(medicalConditionCheckbox?.checked);
            const hasIncompleteRequirements = Boolean(incompleteRequirementsCheckbox?.checked);
            const needsPhysicianEvaluation = Boolean(physicianEvaluationCheckbox?.checked);
            const needsFurtherEvaluation = Boolean(furtherEvaluationCheckbox?.checked);
            const needsHealthFormCorrection = Boolean(healthFormCorrectionCheckbox?.checked);
            const hasOtherPendingReason = Boolean(otherPendingReasonCheckbox?.checked);
            const selectedResubmissionDocs = Array.from(document.querySelectorAll('input[name="resubmission_required_documents[]"]:checked'))
                .map(function (input) { return input.value; });

            if (isPendingDecision && !hasIncompleteRequirements && !needsPhysicianEvaluation && !needsFurtherEvaluation && !needsHealthFormCorrection && !hasOtherPendingReason) {
                setStatus('error', 'Please select at least one pending reason.');
                return;
            }

            if (hasFindings && hasMedicalCondition && (!medicalConditionInput?.value.trim())) {
                setStatus('error', 'Please enter the medical condition.');
                return;
            }

            if (isPendingDecision && hasIncompleteRequirements && selectedResubmissionDocs.length === 0) {
                setStatus('error', 'Please select at least one document for resubmission.');
                return;
            }

            if (isPendingDecision && hasOtherPendingReason && (!otherPendingReasonInput?.value.trim())) {
                setStatus('error', 'Please enter the other pending reason.');
                return;
            }

            if (!heightInput?.value || !weightInput?.value || !bloodPressureInput?.value.trim() || !pulseRateInput?.value || !respiratoryRateInput?.value || !temperatureInput?.value) {
                setStatus('error', 'Please complete the height, weight, blood pressure, pulse rate, respiratory rate, and temperature fields.');
                return;
            }

            const heightValue = parseHeightFeet(heightInput.value);
            if (heightValue === null || heightValue < 1 || heightValue > 10) {
                setStatus('error', 'Height must use feet and inches, e.g., 5\'6".');
                return;
            }

            if (!covidPositiveInput) {
                setStatus('error', 'Please select if the student is COVID Positive.');
                return;
            }

            if (covidPositiveInput.value === 'Yes' && !covidPositiveDateInput?.value) {
                setStatus('error', 'Please enter the COVID Positive date.');
                return;
            }

            setStatus('info', isPendingDecision ? 'Saving pending compliance...' : 'Approving applicant...');

            const approvalData = {
                reference_number: currentLookupRef,
                lookup_scope: isClinicLookupMode() ? 'employee_local' : 'default',
                findings_status: findingsStatusInput.value,
                clearance_decision: clearanceDecisionInput.value,
                height: heightInput.value,
                weight: weightInput.value,
                blood_pressure: bloodPressureInput.value.trim(),
                pulse_rate: pulseRateInput.value,
                respiratory_rate: respiratoryRateInput.value,
                temperature: temperatureInput.value,
                covid_positive: covidPositiveInput.value,
                covid_positive_date: covidPositiveInput.value === 'Yes' ? covidPositiveDateInput.value : '',
                med_assessment_remarks: hasFindings
                    ? (findingRemarksInput?.value.trim() || '')
                    : (normalRemarksInput?.value.trim() || '')
            };

            if (isClinicLookupMode()) {
                const checkedValues = (name) => Array.from(document.querySelectorAll(`input[name="${name}"]:checked`))
                    .map(input => input.value);
                const checkedValue = (name) => document.querySelector(`input[name="${name}"]:checked`)?.value || '';
                const fieldValue = (name) => document.querySelector(`[name="${name}"]`)?.value?.trim() || '';

                Object.assign(approvalData, {
                    employee_exam_distress: checkedValue('employee_exam_distress'),
                    employee_exam_height: fieldValue('employee_exam_height') || heightInput.value,
                    employee_exam_weight: fieldValue('employee_exam_weight') || weightInput.value,
                    employee_exam_bmi: fieldValue('employee_exam_bmi'),
                    employee_exam_bp: fieldValue('employee_exam_bp') || bloodPressureInput.value.trim(),
                    employee_exam_hr: fieldValue('employee_exam_hr') || pulseRateInput.value,
                    employee_exam_rr: fieldValue('employee_exam_rr') || respiratoryRateInput.value,
                    employee_exam_temperature: fieldValue('employee_exam_temperature') || temperatureInput.value,
                    employee_exam_head: checkedValues('employee_exam_head[]'),
                    employee_exam_eyes: checkedValues('employee_exam_eyes[]'),
                    employee_exam_ears: checkedValues('employee_exam_ears[]'),
                    employee_exam_throat: checkedValues('employee_exam_throat[]'),
                    employee_exam_chest_lungs: checkedValue('employee_exam_chest_lungs'),
                    employee_exam_chest_xray: checkedValue('employee_exam_chest_xray'),
                    employee_exam_breast: checkedValues('employee_exam_breast[]'),
                    employee_exam_heart_murmur: checkedValue('employee_exam_heart_murmur'),
                    employee_exam_heart_rhythm: checkedValue('employee_exam_heart_rhythm'),
                    employee_exam_abdomen: checkedValues('employee_exam_abdomen[]'),
                    employee_exam_last_menstruation: fieldValue('employee_exam_last_menstruation'),
                    employee_exam_extremities: checkedValues('employee_exam_extremities[]'),
                    employee_exam_vertebral_column: checkedValue('employee_exam_vertebral_column'),
                    employee_exam_skin: checkedValues('employee_exam_skin[]'),
                    employee_exam_scars: checkedValue('employee_exam_scars'),
                    employee_exam_working_impression: fieldValue('employee_exam_working_impression'),
                    employee_exam_fit: fieldValue('employee_exam_fit'),
                    employee_exam_fit_other: fieldValue('employee_exam_fit_other'),
                    employee_exam_for_work_up: fieldValue('employee_exam_for_work_up'),
                    employee_exam_referred_to: checkedValues('employee_exam_referred_to[]'),
                    employee_exam_referred_to_others: fieldValue('employee_exam_referred_to_others'),
                    employee_exam_follow_up_on: fieldValue('employee_exam_follow_up_on')
                });
            }

            if (hasFindings || isPendingDecision) {
                approvalData.has_medical_condition = hasMedicalCondition;
                approvalData.incomplete_requirements = isPendingDecision && hasIncompleteRequirements;
                approvalData.resubmission_required_documents = isPendingDecision && hasIncompleteRequirements ? selectedResubmissionDocs : [];
                approvalData.needs_physician_evaluation = isPendingDecision && needsPhysicianEvaluation;
                approvalData.needs_further_evaluation = isPendingDecision && needsFurtherEvaluation;
                approvalData.needs_health_form_correction = isPendingDecision && needsHealthFormCorrection;
                approvalData.other_pending_reason = isPendingDecision && hasOtherPendingReason ? otherPendingReasonInput.value.trim() : '';
                approvalData.medical_condition = hasMedicalCondition ? medicalConditionInput.value.trim() : '';
                approvalData.condition_remarks = isPendingDecision ? (conditionRemarksInput?.value.trim() || '') : '';
            } else {
                approvalData.condition_remarks = '';
            }

            fetch("{{ route('admin.walkin.approve_applicant') }}", {
                method: 'POST',
                headers: {
                    'X-Requested-With': 'XMLHttpRequest',
                    'Accept': 'application/json',
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                },
                body: JSON.stringify(approvalData)
            })
            .then(r => r.json())
            .then(data => {
                if (data.success) {
                    if (isFinalReviewWorkflow()) {
                        clearLocalApplicantFinalReviewDraft(currentLookupRef);
                    }
                    setStatus('success', data.message || 'Applicant decision saved successfully.');
                    showClinicSuccessOverlay(approvalOverlay, {
                        title: isPendingDecision ? 'Marked Pending' : 'Medical Clearance Issued',
                        message: isPendingDecision
                            ? 'The applicant has been moved to pending compliance for follow-up.'
                            : 'The applicant has been approved successfully.',
                        duration: 3000,
                        onDone: () => {
                        if (isFinalReviewWorkflow()) {
                            refreshFinalReviewApplicants()
                                .finally(showFinalReviewList);
                            return;
                        }
                        if (data.redirect_url) {
                            window.location.href = data.redirect_url;
                            return;
                        }
                        closeApplicantsModal();
                        isApprovalMode = false;
                        resetLookupButtonToFind();
                        resetLookupState();
                        if (refInput) refInput.value = '';
                        }
                    });
                } else {
                    setStatus('error', data.message || 'Failed to save applicant decision.');
                }
            })
            .catch(() => setStatus('error', 'Unable to save the applicant decision right now. Please try again.'));
        }

        function collectReviewDraftData(lookupScope) {
            const draft = {
                reference_number: currentLookupRef,
                lookup_scope: lookupScope
            };

            document.querySelectorAll('#applicantRefModal [name]').forEach(function (field) {
                if (field.type === 'file') return;
                const name = field.name;
                const key = name.endsWith('[]') ? name.slice(0, -2) : name;
                if (field.type === 'checkbox' || field.type === 'radio') {
                    if (name.endsWith('[]')) {
                        if (!Array.isArray(draft[key])) draft[key] = [];
                        if (field.checked) draft[key].push(field.value);
                    } else if (field.type === 'checkbox') {
                        if (!Object.prototype.hasOwnProperty.call(draft, key)) draft[key] = false;
                        if (field.checked) draft[key] = field.value;
                    } else {
                        if (!Object.prototype.hasOwnProperty.call(draft, key)) draft[key] = '';
                        if (field.checked) draft[key] = field.value;
                    }
                    return;
                }
                draft[key] = field.value;
            });

            return draft;
        }

        function collectEmployeeDraftData() {
            return collectReviewDraftData('employee_local');
        }

        function collectApplicantFinalReviewDraftData() {
            return collectReviewDraftData('final_review');
        }

        function cacheCurrentEmployeeDraft() {
            if (!isClinicLookupMode() || !currentLookupRef) return;
            try {
                localStorage.setItem(employeeDraftStorageKey(currentLookupRef), JSON.stringify(collectEmployeeDraftData()));
            } catch (error) {
                // Local backup is best effort; the server Draft action remains available.
            }
        }

        function cacheCurrentApplicantFinalReviewDraft() {
            if (!isFinalReviewWorkflow() || !currentLookupRef) return;
            try {
                localStorage.setItem(
                    applicantFinalReviewDraftStorageKey(currentLookupRef),
                    JSON.stringify(collectApplicantFinalReviewDraftData())
                );
            } catch (error) {
                // Local backup is best effort; the server Draft action remains available.
            }
        }

        function saveEmployeeDraft() {
            if (!currentLookupRef || !isClinicLookupMode()) {
                setStatus('error', 'Open an employee record before saving a draft.');
                return;
            }

            setStatus('info', 'Saving employee draft...');
            if (reviewDraftBtn) reviewDraftBtn.disabled = true;
            cacheCurrentEmployeeDraft();

            fetch("{{ route('admin.walkin.employee_draft') }}", {
                method: 'POST',
                headers: {
                    'X-Requested-With': 'XMLHttpRequest',
                    'Accept': 'application/json',
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                },
                body: JSON.stringify(collectEmployeeDraftData())
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    setStatus('success', data.message || 'Employee draft saved successfully.');
                    closeApplicantsModal();
                    return;
                }

                setStatus('error', data.message || 'Unable to save employee draft.');
            })
            .catch(() => setStatus('error', 'Unable to save employee draft right now.'))
            .finally(() => { if (reviewDraftBtn) reviewDraftBtn.disabled = false; });
        }

        function saveApplicantFinalReviewDraft() {
            if (!currentLookupRef || !isFinalReviewWorkflow()) {
                setStatus('error', 'Open an applicant in Final Review before saving a draft.');
                return;
            }

            setStatus('info', 'Saving applicant final review draft...');
            if (reviewDraftBtn) reviewDraftBtn.disabled = true;
            cacheCurrentApplicantFinalReviewDraft();

            fetch(applicantFinalReviewDraftUrl, {
                method: 'POST',
                headers: {
                    'X-Requested-With': 'XMLHttpRequest',
                    'Accept': 'application/json',
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                },
                body: JSON.stringify(collectApplicantFinalReviewDraftData())
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    setStatus('success', data.message || 'Applicant final review draft saved successfully.');
                    closeApplicantsModal();
                    return;
                }

                setStatus('error', data.message || 'Unable to save applicant final review draft.');
            })
            .catch(() => setStatus('error', 'Unable to save applicant final review draft right now.'))
            .finally(() => { if (reviewDraftBtn) reviewDraftBtn.disabled = false; });
        }

        reviewDraftBtn?.addEventListener('click', function () {
            if (isFinalReviewWorkflow()) {
                saveApplicantFinalReviewDraft();
                return;
            }

            saveEmployeeDraft();
        });

        document.querySelectorAll('#applicantRefModal [name]').forEach(function (field) {
            field.addEventListener('input', function () {
                cacheCurrentEmployeeDraft();
                cacheCurrentApplicantFinalReviewDraft();
            });
            field.addEventListener('change', function () {
                cacheCurrentEmployeeDraft();
                cacheCurrentApplicantFinalReviewDraft();
            });
        });

        const findingsInputs = document.querySelectorAll('input[name="applicant_findings_status"]');
        const clearanceDecisionInputs = document.querySelectorAll('input[name="applicant_clearance_decision"]');
        const pendingReasonInputs = document.querySelectorAll('#applicantHasMedicalCondition, #applicantIncompleteRequirements, #applicantNeedsPhysicianEvaluation, #applicantNeedsFurtherEvaluation, #applicantNeedsHealthFormCorrection, #applicantOtherPendingReason');
        const medicalConditionFields = document.getElementById('applicantConditionFields');
            const clearanceDecisionFields = document.getElementById('applicantClearanceDecisionFields');
            const pendingDecisionFields = document.getElementById('applicantPendingDecisionFields');
            const normalRemarksFields = document.getElementById('applicantNormalRemarksFields');
            const findingRemarksField = document.getElementById('applicantFindingRemarksField');
            const medicalConditionField = document.getElementById('applicantMedicalConditionField');
            const otherPendingReasonField = document.getElementById('applicantOtherPendingReasonField');
            const resubmissionDocsField = document.getElementById('applicantResubmissionDocsField');
            const covidPositiveInputs = document.querySelectorAll('input[name="applicant_covid_positive"]');
        const covidPositiveDateField = document.getElementById('applicantCovidDateField');
        const covidPositiveDateInput = document.getElementById('applicantCovidPositiveDate');

        function syncCovidPositiveFields() {
            const selectedCovidValue = document.querySelector('input[name="applicant_covid_positive"]:checked')?.value || '';
            const isPositive = selectedCovidValue === 'Yes';

            if (covidPositiveDateField) {
                covidPositiveDateField.style.display = isPositive ? 'flex' : 'none';
            }
            if (covidPositiveDateInput) {
                covidPositiveDateInput.required = isPositive;
                covidPositiveDateInput.disabled = !isPositive;
                if (!isPositive) {
                    covidPositiveDateInput.value = '';
                }
            }

            // Sync COVID positive selection to consultation form
            if (selectedCovidValue) {
                const consultationCovidField = document.querySelector('input[name="covid_status"][value="' + selectedCovidValue + '"]');
                if (consultationCovidField) {
                    consultationCovidField.checked = true;
                    consultationCovidField.dispatchEvent(new Event('change', { bubbles: true }));
                }
            }

            validateCovidDate();
            updateVitalIndicators();
        }

        function validateVitals() {
            const heightInput = document.getElementById('applicantHeight');
            const weightInput = document.getElementById('applicantWeight');
            const bpInput = document.getElementById('applicantBloodPressure');
            const prInput = document.getElementById('applicantPulseRate');
            const rrInput = document.getElementById('applicantRespiratoryRate');
            const tempInput = document.getElementById('applicantTemperature');
            const heightError = document.getElementById('heightError');
            const weightError = document.getElementById('weightError');
            const bpError = document.getElementById('bpError');
            const prError = document.getElementById('prError');
            const rrError = document.getElementById('rrError');
            const tempError = document.getElementById('tempError');

            // Validate Height
            if (heightInput) {
                const heightValue = parseHeightFeet(heightInput.value);
                if (heightInput.value && (heightValue === null || heightValue < 1 || heightValue > 10)) {
                    if (heightError) heightError.style.display = 'block';
                } else {
                    if (heightError) heightError.style.display = 'none';
                }
            }

            // Validate Weight
            if (weightInput) {
                const weightValue = parseFloat(weightInput.value);
                if (weightInput.value && (weightValue < 1 || weightValue > 1100)) {
                    if (weightError) weightError.style.display = 'block';
                } else {
                    if (weightError) weightError.style.display = 'none';
                }
            }

            // Validate Blood Pressure
            if (bpInput) {
                const bpValue = bpInput.value.trim();
                const bpRegex = /^\d{2,3}\s*\/\s*\d{2,3}$/;
                if (bpValue && !bpRegex.test(bpValue)) {
                    if (bpError) bpError.style.display = 'block';
                } else {
                    if (bpError) bpError.style.display = 'none';
                }
            }

            // Validate Pulse Rate
            if (prInput) {
                const prValue = prInput.value;
                if (prValue && (prValue < 1 || prValue > 300)) {
                    if (prError) prError.style.display = 'block';
                } else {
                    if (prError) prError.style.display = 'none';
                }
            }

            // Validate Respiratory Rate
            if (rrInput) {
                const rrValue = rrInput.value;
                if (rrValue && (rrValue < 1 || rrValue > 120)) {
                    if (rrError) rrError.style.display = 'block';
                } else {
                    if (rrError) rrError.style.display = 'none';
                }
            }

            // Validate Temperature
            if (tempInput) {
                const tempValue = tempInput.value;
                if (tempValue && (tempValue < 30 || tempValue > 45)) {
                    if (tempError) tempError.style.display = 'block';
                } else {
                    if (tempError) tempError.style.display = 'none';
                }
            }

            updateVitalIndicators();
        }

        function validateCovidDate() {
            const covidDateInput = document.getElementById('applicantCovidPositiveDate');
            const covidDateError = document.getElementById('covidDateError');

            if (covidDateInput && covidDateInput.value) {
                const selectedDate = new Date(covidDateInput.value);
                const today = new Date();
                today.setHours(0, 0, 0, 0);

                if (selectedDate > today) {
                    if (covidDateError) covidDateError.style.display = 'block';
                    covidDateInput.value = '';
                } else {
                    if (covidDateError) covidDateError.style.display = 'none';
                }
            }
        }

        function restrictCovidDateInput() {
            const covidDateInput = document.getElementById('applicantCovidPositiveDate');
            if (covidDateInput) {
                const today = new Date().toISOString().split('T')[0];
                covidDateInput.max = today;
            }
        }

        function mirrorAssessmentRemarks(sourceId) {
            const source = document.getElementById(sourceId);
            if (!source) return;

            const value = String(source.value || '');

            ['applicantNormalRemarks', 'applicantFindingRemarks', 'applicantConditionRemarks'].forEach(function (id) {
                const field = document.getElementById(id);
                if (!field || field === source) return;
                field.value = value;
            });
        }

        function carryAssessmentRemarksForCurrentChoice() {
            const selectedFinding = document.querySelector('input[name="applicant_findings_status"]:checked')?.value || '';
            const selectedDecision = document.querySelector('input[name="applicant_clearance_decision"]:checked')?.value || '';
            const normalRemarks = document.getElementById('applicantNormalRemarks');
            const findingRemarks = document.getElementById('applicantFindingRemarks');
            const pendingRemarks = document.getElementById('applicantConditionRemarks');

            if (selectedFinding === 'With Findings' && findingRemarks && String(findingRemarks.value || '').trim() === '') {
                findingRemarks.value = normalRemarks?.value || pendingRemarks?.value || '';
            }
            if (selectedFinding === 'No Findings / Normal' && normalRemarks && String(normalRemarks.value || '').trim() === '') {
                normalRemarks.value = findingRemarks?.value || pendingRemarks?.value || '';
            }
            if (selectedDecision === 'pending' && pendingRemarks && String(pendingRemarks.value || '').trim() === '') {
                pendingRemarks.value = findingRemarks?.value || normalRemarks?.value || '';
            }
        }

        function syncFindingsReviewFields() {
            const selectedFinding = document.querySelector('input[name="applicant_findings_status"]:checked')?.value || '';
            let selectedDecision = document.querySelector('input[name="applicant_clearance_decision"]:checked')?.value || '';
            const hasFindings = selectedFinding === 'With Findings';
            const hasNormalResult = selectedFinding === 'No Findings / Normal';
            const hasReviewResult = hasFindings || hasNormalResult;
            if (hasReviewResult && !selectedDecision) {
                const approveDecision = document.querySelector('input[name="applicant_clearance_decision"][value="approve"]');
                if (approveDecision) {
                    approveDecision.checked = true;
                    selectedDecision = 'approve';
                }
            }
            const isPendingDecision = selectedDecision === 'pending';
            const hasMedicalCondition = Boolean(document.getElementById('applicantHasMedicalCondition')?.checked);
            const hasIncompleteRequirements = Boolean(document.getElementById('applicantIncompleteRequirements')?.checked);
            const hasOtherPendingReason = Boolean(document.getElementById('applicantOtherPendingReason')?.checked);
            carryAssessmentRemarksForCurrentChoice();

            if (clearanceDecisionFields) {
                clearanceDecisionFields.style.display = hasReviewResult ? 'block' : 'none';
            }
            if (medicalConditionFields) {
                medicalConditionFields.style.display = hasFindings ? 'flex' : 'none';
            }
            if (pendingDecisionFields) {
                pendingDecisionFields.style.display = isPendingDecision ? 'flex' : 'none';
            }
            if (normalRemarksFields) {
                normalRemarksFields.style.display = hasNormalResult && !isPendingDecision ? 'flex' : 'none';
            }
            if (findingRemarksField) {
                findingRemarksField.style.display = hasFindings && !isPendingDecision ? 'flex' : 'none';
            }
            if (medicalConditionField) {
                medicalConditionField.style.display = hasFindings && hasMedicalCondition ? 'flex' : 'none';
            }
            if (otherPendingReasonField) {
                otherPendingReasonField.style.display = isPendingDecision && hasOtherPendingReason ? 'flex' : 'none';
            }
            if (resubmissionDocsField) {
                resubmissionDocsField.style.display = isPendingDecision && hasIncompleteRequirements ? 'flex' : 'none';
            }

            if (!hasFindings) {
                const conditionInput = document.getElementById('applicantMedicalCondition');
                if (conditionInput) conditionInput.value = '';
                const conditionToggle = document.getElementById('applicantHasMedicalCondition');
                if (conditionToggle) conditionToggle.checked = false;
            } else {
                if (!hasMedicalCondition) {
                    const conditionInput = document.getElementById('applicantMedicalCondition');
                    if (conditionInput) conditionInput.value = '';
                }
            }
            if (!isPendingDecision) {
                ['applicantIncompleteRequirements', 'applicantNeedsPhysicianEvaluation', 'applicantNeedsFurtherEvaluation', 'applicantNeedsHealthFormCorrection', 'applicantOtherPendingReason'].forEach(function (id) {
                    const input = document.getElementById(id);
                    if (input) input.checked = false;
                });
                const otherPendingReason = document.getElementById('applicantOtherPendingReasonText');
                if (otherPendingReason) otherPendingReason.value = '';
                document.querySelectorAll('input[name="resubmission_required_documents[]"]').forEach(function (input) {
                    input.checked = false;
                });
            } else {
                if (!hasOtherPendingReason) {
                    const otherPendingReason = document.getElementById('applicantOtherPendingReasonText');
                    if (otherPendingReason) otherPendingReason.value = '';
                }
                if (!hasIncompleteRequirements) {
                    document.querySelectorAll('input[name="resubmission_required_documents[]"]').forEach(function (input) {
                        input.checked = false;
                    });
                }
            }

            if (findBtn && isApprovalMode) {
                findBtn.textContent = isPendingDecision ? 'Pending' : 'Approve';
            }
        }

        findingsInputs.forEach(function (input) {
            input.addEventListener('change', syncFindingsReviewFields);
        });
        clearanceDecisionInputs.forEach(function (input) {
            input.addEventListener('change', syncFindingsReviewFields);
        });
        pendingReasonInputs.forEach(function (input) {
            input.addEventListener('change', syncFindingsReviewFields);
        });
        ['applicantNormalRemarks', 'applicantFindingRemarks', 'applicantConditionRemarks'].forEach(function (id) {
            const field = document.getElementById(id);
            if (field) {
                field.addEventListener('input', function () {
                    mirrorAssessmentRemarks(id);
                });
            }
        });
        covidPositiveInputs.forEach(function (input) {
            input.addEventListener('change', syncCovidPositiveFields);
        });

        const finalReviewPhysicalPanel = document.querySelector('#applicantRefModal .applicant-medical-condition-section > section:not(.applicant-review-panel)');
        if (finalReviewPhysicalPanel) {
            finalReviewPhysicalPanel.addEventListener('dblclick', function () {
                if (!isFinalReviewWorkflow()) return;
                setFinalReviewPhysicalReadonly(false);
                setStatus('info', 'Physical assessment fields are now editable.');
            });
        }

        // Add event listeners for vital signs validation
        const vitalInputs = document.querySelectorAll('.vital-input');
        vitalInputs.forEach(function (input) {
            input.addEventListener('input', validateVitals);
            input.addEventListener('blur', validateVitals);
        });

        document.querySelectorAll('[name="employee_exam_height"], [name="employee_exam_weight"]').forEach(function (input) {
            input.addEventListener('input', updateEmployeeBmi);
            input.addEventListener('blur', updateEmployeeBmi);
        });

        document.querySelectorAll('[name="employee_exam_bp"], [name="employee_exam_hr"], [name="employee_exam_rr"], [name="employee_exam_temperature"]').forEach(function (input) {
            input.addEventListener('input', updateEmployeeExamValidation);
            input.addEventListener('blur', updateEmployeeExamValidation);
        });

        const employeeExamFit = document.getElementById('employeeExamFit');
        const employeeExamFitOtherWrap = document.getElementById('employeeExamFitOtherWrap');
        const syncEmployeeExamFitOther = function () {
            const showOther = employeeExamFit?.value === 'Others';
            if (employeeExamFitOtherWrap) employeeExamFitOtherWrap.style.display = showOther ? 'inline-flex' : 'none';
        };
        employeeExamFit?.addEventListener('change', syncEmployeeExamFitOther);
        syncEmployeeExamFitOther();

        // Add event listener for COVID positive date validation
        const covidDateInput = document.getElementById('applicantCovidPositiveDate');
        if (covidDateInput) {
            covidDateInput.addEventListener('change', validateCovidDate);
            covidDateInput.addEventListener('blur', validateCovidDate);
        }

        syncCovidPositiveFields();
        updateEmployeeBmi();
        restrictCovidDateInput();
        if (applicantModalBody) {
            applicantModalBody.addEventListener('scroll', syncFinalReviewToolbarState, { passive: true });
        }

        function showApplicantReferenceEntry(workflow) {
            resetLookupState();
            setApplicantWorkflow(workflow);
            if (workflowChoices) workflowChoices.style.display = 'none';
            if (finalReviewList) finalReviewList.classList.remove('is-visible');
            if (showEntryBtn) showEntryBtn.style.display = 'none';
            if (refInput) refInput.value = '';
            setEntryMode(true);
        }

        function showFinalReviewList() {
            setApplicantWorkflow('final_review');
            if (modalShell) modalShell.classList.remove('is-final-review-toolbar-stuck');
            if (applicantFileActions) delete applicantFileActions.dataset.stickyOriginTop;
            if (applicantModalBody) applicantModalBody.scrollTop = 0;
            if (defaultPane) defaultPane.style.display = 'flex';
            if (entryPane) entryPane.classList.remove('is-visible');
            if (workflowChoices) workflowChoices.style.display = 'none';
            if (showEntryBtn) showEntryBtn.style.display = 'none';
            const introCopy = defaultPane?.querySelector('.applicant-ref-copy');
            if (introCopy) introCopy.style.display = 'none';
            if (finalReviewList) finalReviewList.classList.add('is-visible');
            if (backToFinalReviewList) backToFinalReviewList.classList.remove('is-visible');
            updateFinalReviewPagination(1);
            if (finalReviewSearch) finalReviewSearch.focus();
        }

        function openFinalReviewReference(referenceNumber) {
            showApplicantReferenceEntry('final_review');
            if (refInput) {
                refInput.value = referenceNumber || '';
            }
            doLookup();
        }

        function markFinalReviewTimeIn(referenceNumber) {
            if (!referenceNumber || !finalReviewTimeInUrl) {
                return Promise.resolve();
            }

            return fetch(finalReviewTimeInUrl, {
                method: 'POST',
                headers: {
                    'Accept': 'application/json',
                    'Content-Type': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                },
                body: JSON.stringify({ reference_number: referenceNumber })
            }).catch(function (error) {
                console.warn('Unable to mark final review time-in.', error);
            });
        }

        function buildFinalReviewCard(applicant) {
            const article = document.createElement('article');
            article.className = 'applicant-final-review-card';
            article.setAttribute('data-final-review-row', '');
            article.setAttribute('data-search', applicant.search || '');

            const applicantBlock = document.createElement('div');
            applicantBlock.className = 'applicant-final-review-person';
            const avatar = document.createElement('span');
            avatar.className = 'applicant-final-review-avatar';
            if (applicant.photo_url) {
                const avatarImg = document.createElement('img');
                avatarImg.src = applicant.photo_url;
                avatarImg.alt = `${applicant.name || 'Applicant'} 2x2 photo`;
                avatarImg.addEventListener('error', function () {
                    avatar.textContent = getApplicantInitials(applicant.name || 'Applicant');
                }, { once: true });
                avatar.appendChild(avatarImg);
            } else {
                avatar.textContent = getApplicantInitials(applicant.name || 'Applicant');
            }
            const applicantText = document.createElement('div');
            const applicantLabel = document.createElement('small');
            applicantLabel.textContent = 'Applicant';
            const applicantName = document.createElement('strong');
            applicantName.textContent = applicant.name || 'Applicant';
            const applicantEmail = document.createElement('span');
            applicantEmail.textContent = applicant.email || 'N/A';
            applicantText.append(applicantLabel, applicantName, applicantEmail);
            applicantBlock.append(avatar, applicantText);

            const referenceBlock = document.createElement('div');
            const referenceLabel = document.createElement('small');
            referenceLabel.textContent = 'Reference Number';
            const referenceValue = document.createElement('strong');
            referenceValue.className = 'applicant-final-review-reference-badge';
            referenceValue.textContent = applicant.reference_number || 'N/A';
            referenceBlock.append(referenceLabel, referenceValue);

            const reviewButton = document.createElement('button');
            reviewButton.type = 'button';
            reviewButton.className = 'applicant-final-review-btn';
            reviewButton.dataset.finalReviewReference = applicant.reference_number || '';
            const reviewButtonLabel = document.createElement('span');
            reviewButtonLabel.textContent = 'Review';
            reviewButton.appendChild(reviewButtonLabel);

            article.append(applicantBlock, referenceBlock, reviewButton);

            return article;
        }

        function renderFinalReviewApplicants(applicants) {
            if (!finalReviewRows) return;

            finalReviewRows.replaceChildren();

            if (!Array.isArray(applicants) || applicants.length === 0) {
                const emptyState = document.createElement('div');
                emptyState.className = 'applicant-documents-empty';
                emptyState.textContent = 'No encoded applicants are ready for final review.';
                finalReviewRows.appendChild(emptyState);
                if (finalReviewEmpty) finalReviewEmpty.classList.remove('is-visible');
                updateFinalReviewPagination(1);
                return;
            }

            applicants.forEach(function (applicant) {
                finalReviewRows.appendChild(buildFinalReviewCard(applicant));
            });

            if (finalReviewEmpty) finalReviewEmpty.textContent = 'No encoded applicant matches your search.';
            updateFinalReviewPagination(1);
        }

        function refreshFinalReviewApplicants() {
            if (!finalReviewRefresh) return Promise.resolve();

            const label = finalReviewRefresh.querySelector('span');
            const originalLabel = label ? label.textContent : '';
            finalReviewRefresh.disabled = true;
            finalReviewRefresh.style.opacity = '0.72';
            if (label) label.textContent = 'Refreshing';

            return fetch(finalReviewApplicantsUrl, {
                method: 'GET',
                headers: {
                    'Accept': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest'
                }
            })
                .then(response => response.json())
                .then(data => {
                    if (!data.success) {
                        throw new Error(data.message || 'Unable to refresh the final review list.');
                    }

                    renderFinalReviewApplicants(data.applicants || []);
                })
                .catch(error => {
                    console.error(error);
                    if (finalReviewEmpty) {
                        finalReviewEmpty.textContent = 'Unable to refresh the list. Please try again.';
                        finalReviewEmpty.classList.add('is-visible');
                    }
                })
                .finally(() => {
                    finalReviewRefresh.disabled = false;
                    finalReviewRefresh.style.opacity = '';
                    if (label) label.textContent = originalLabel || 'Refresh';
                });
        }

        function updateFinalReviewPagination(targetPage) {
            const allRows = Array.from((finalReviewRows || document).querySelectorAll('[data-final-review-row]'));
            const query = finalReviewSearch ? finalReviewSearch.value.trim().toLowerCase() : '';
            const matchingRows = allRows.filter(function (row) {
                const haystack = row.getAttribute('data-search') || '';
                return !query || haystack.includes(query);
            });
            if (finalReviewTotalCount) finalReviewTotalCount.textContent = allRows.length.toString();
            const rawPageSize = finalReviewPerPage ? finalReviewPerPage.value : '5';
            const pageSize = rawPageSize === 'all'
                ? Math.max(1, matchingRows.length)
                : Math.max(1, parseInt(rawPageSize, 10) || 5);
            const totalPages = Math.max(1, Math.ceil(matchingRows.length / pageSize));
            finalReviewPage = Math.min(Math.max(targetPage || finalReviewPage || 1, 1), totalPages);
            const start = (finalReviewPage - 1) * pageSize;
            const end = start + pageSize;

            allRows.forEach(function (row) {
                row.style.display = 'none';
            });
            matchingRows.slice(start, end).forEach(function (row) {
                row.style.display = '';
            });

            if (finalReviewEmpty) {
                finalReviewEmpty.classList.toggle('is-visible', allRows.length > 0 && matchingRows.length === 0);
            }
            if (finalReviewPagination) {
                finalReviewPagination.classList.toggle('is-visible', matchingRows.length > 0);
            }
            if (finalReviewPrev) finalReviewPrev.disabled = finalReviewPage <= 1;
            if (finalReviewNext) finalReviewNext.disabled = finalReviewPage >= totalPages;
            if (finalReviewPageLabel) finalReviewPageLabel.textContent = String(finalReviewPage);
        }

        if (startEncodingBtn) {
            startEncodingBtn.addEventListener('click', function () {
                showApplicantReferenceEntry('encode');
            });
        }

        if (startFinalReviewBtn) {
            startFinalReviewBtn.addEventListener('click', showFinalReviewList);
        }

        if (finalReviewManualLookup) {
            finalReviewManualLookup.addEventListener('click', function () {
                showApplicantReferenceEntry('final_review');
            });
        }

        if (backToFinalReviewList) {
            backToFinalReviewList.addEventListener('click', function () {
                resetLookupState();
                showFinalReviewList();
            });
        }

        if (finalReviewRows) {
            finalReviewRows.addEventListener('click', function (event) {
                const button = event.target.closest('[data-final-review-reference]');
                if (!button) return;

                const referenceNumber = button.getAttribute('data-final-review-reference') || '';
                markFinalReviewTimeIn(referenceNumber).finally(function () {
                    openFinalReviewReference(referenceNumber);
                });
            });
        }

        if (finalReviewRefresh) {
            finalReviewRefresh.addEventListener('click', refreshFinalReviewApplicants);
        }

        if (finalReviewSearch) {
            finalReviewSearch.addEventListener('input', function () {
                updateFinalReviewPagination(1);
            });
        }

        if (finalReviewPrev) {
            finalReviewPrev.addEventListener('click', function () {
                updateFinalReviewPagination(finalReviewPage - 1);
            });
        }

        if (finalReviewNext) {
            finalReviewNext.addEventListener('click', function () {
                updateFinalReviewPagination(finalReviewPage + 1);
            });
        }

        if (finalReviewPerPage) {
            finalReviewPerPage.addEventListener('change', function () {
                updateFinalReviewPagination(1);
            });
        }

        if (openBtn) openBtn.addEventListener('click', function (e) { e.preventDefault(); openApplicantsModal(); });
        if (openClinicBtn) openClinicBtn.addEventListener('click', function (e) { e.preventDefault(); openClinicLookupModal(); });
        if (closeBtn) closeBtn.addEventListener('click', closeApplicantsModal);
        if (backdrop) backdrop.addEventListener('click', function (e) { if (e.target === backdrop) closeApplicantsModal(); });
        if (closeHealthInfoModalButton) closeHealthInfoModalButton.addEventListener('click', closeHealthInfoModal);
        if (healthInfoModal) healthInfoModal.addEventListener('click', function (event) {
            if (event.target === healthInfoModal) closeHealthInfoModal();
        });
        if (closeMedicalConditionModalButton) closeMedicalConditionModalButton.addEventListener('click', closeMedicalConditionModal);
        if (medicalConditionModal) medicalConditionModal.addEventListener('click', function (event) {
            if (event.target === medicalConditionModal) closeMedicalConditionModal();
        });
        if (informationButton) informationButton.addEventListener('click', function () {
            if (!healthInfoModal || !informationDetails) return;

            informationDetails.classList.add('is-visible');
            informationDetails.setAttribute('aria-hidden', 'false');
            renderHealthInfoEditor(currentHealthInfoData);
            healthInfoModal.classList.add('show');
            healthInfoModal.setAttribute('aria-hidden', 'false');
            informationButton.setAttribute('aria-expanded', 'true');
        });
        if (medicalConditionButton) medicalConditionButton.addEventListener('click', function () {
            if (!medicalConditionModal || !medicalConditionDetails) return;

            medicalConditionDetails.classList.add('is-visible');
            medicalConditionDetails.setAttribute('aria-hidden', 'false');
            medicalConditionModal.classList.add('show');
            medicalConditionModal.setAttribute('aria-hidden', 'false');
            medicalConditionButton.setAttribute('aria-expanded', 'true');
        });
        if (documentsButton) documentsButton.addEventListener('click', function () {
            if (documentsModal) documentsModal.classList.add('show');
        });
        if (healthInfoTabs) {
            healthInfoTabs.addEventListener('click', function (event) {
                const tab = event.target.closest('[data-health-info-section]');
                if (!tab) return;
                currentHealthInfoSection = tab.dataset.healthInfoSection || 'personal_information';
                renderHealthInfoTabs();
                renderHealthInfoFields();
            });
        }
        if (healthInfoEditBtn) {
            healthInfoEditBtn.addEventListener('click', function () {
                setHealthInfoEditing(true);
            });
        }
        if (healthInfoCancelBtn) {
            healthInfoCancelBtn.addEventListener('click', function () {
                setHealthInfoEditing(false);
            });
        }
        if (healthInfoSaveBtn) {
            healthInfoSaveBtn.addEventListener('click', saveHealthInfoEditor);
        }
        if (healthInfoFields) {
            healthInfoFields.addEventListener('change', function (event) {
                if (event.target.matches('[data-health-course-select]')) {
                    syncHealthInfoCourseFields();
                }

                if (
                    event.target.matches('[data-health-field]') ||
                    event.target.matches('[data-health-checkbox-group]') ||
                    event.target.matches('[data-health-vaccine-dose]')
                ) {
                    refreshHealthInfoConditionalVisibility();
                }
            });
        }
        if (pendingHistoryButton && pendingHistoryWrap) {
            pendingHistoryButton.addEventListener('click', function (event) {
                event.preventDefault();
                event.stopPropagation();
                const willShow = !pendingHistoryWrap.classList.contains('is-open');
                pendingHistoryWrap.classList.toggle('is-open', willShow);
                pendingHistoryButton.setAttribute('aria-expanded', willShow ? 'true' : 'false');
            });
            document.addEventListener('click', function (event) {
                if (!pendingHistoryWrap.classList.contains('is-open')) return;
                if (pendingHistoryWrap.contains(event.target)) return;
                pendingHistoryWrap.classList.remove('is-open');
                pendingHistoryButton.setAttribute('aria-expanded', 'false');
            });
        }
        if (savedAssessmentButton) savedAssessmentButton.addEventListener('click', function () {
            if (!hasSavedAssessmentReview(currentAssessmentReview) || !savedAssessmentModal) return;
            savedAssessmentModal.classList.add('show');
            savedAssessmentModal.setAttribute('aria-hidden', 'false');
        });
        if (closeSavedAssessment) closeSavedAssessment.addEventListener('click', function () {
            if (!savedAssessmentModal) return;
            savedAssessmentModal.classList.remove('show');
            savedAssessmentModal.setAttribute('aria-hidden', 'true');
        });
        if (savedAssessmentModal) savedAssessmentModal.addEventListener('click', function (event) {
            if (event.target !== savedAssessmentModal) return;
            savedAssessmentModal.classList.remove('show');
            savedAssessmentModal.setAttribute('aria-hidden', 'true');
        });
        if (copyReferenceButton) copyReferenceButton.addEventListener('click', async function () {
            const referenceNumber = (lookupRef?.textContent || currentLookupRef || '').trim();
            if (referenceNumber === '' || referenceNumber === '-') return;

            try {
                await navigator.clipboard.writeText(referenceNumber);
            } catch (error) {
                const temporaryInput = document.createElement('textarea');
                temporaryInput.value = referenceNumber;
                temporaryInput.style.position = 'fixed';
                temporaryInput.style.opacity = '0';
                document.body.appendChild(temporaryInput);
                temporaryInput.select();
                document.execCommand('copy');
                temporaryInput.remove();
            }

            setStatus('success', 'Reference number copied.');
        });
        if (closeDocuments) closeDocuments.addEventListener('click', closeDocumentsModal);
        if (documentsModal) documentsModal.addEventListener('click', function (event) {
            if (event.target === documentsModal) closeDocumentsModal();
        });
        if (showEntryBtn) showEntryBtn.addEventListener('click', function () { setEntryMode(true); });
        if (cancelEntryBtn) cancelEntryBtn.addEventListener('click', function () {
            if (refInput) refInput.value = '';
            setEntryMode(false);
        });
        if (findBtn) findBtn.addEventListener('click', doLookup);
        if (refInput) {
            refInput.addEventListener('keydown', function (e) {
                if (e.key === 'Enter') {
                    e.preventDefault();
                    doLookup();
                }
            });
        }

    })();

    (function initApplicantPremiumSelects() {
        function enhance(select) {
            if (!select || select.dataset.premiumEnhanced === 'true') return;
            select.dataset.premiumEnhanced = 'true';
            select.classList.add('premium-select-native');
            const shell = document.createElement('div');
            shell.className = 'premium-select-shell';
            const button = document.createElement('button');
            button.type = 'button';
            button.className = 'premium-select-button';
            const menu = document.createElement('div');
            menu.className = 'premium-select-menu';
            function rebuild() {
                const selected = select.options[select.selectedIndex];
                button.textContent = selected ? selected.textContent.trim() : 'Select';
                menu.innerHTML = '';
                Array.from(select.options).forEach(function(option) {
                    const item = document.createElement('button');
                    item.type = 'button';
                    item.className = 'premium-select-option';
                    item.textContent = option.textContent.trim();
                    item.classList.toggle('is-selected', option.selected);
                    item.addEventListener('click', function() {
                        select.value = option.value;
                        shell.classList.remove('is-open');
                        rebuild();
                        select.dispatchEvent(new Event('change', { bubbles: true }));
                    });
                    menu.appendChild(item);
                });
            }
            select.parentNode.insertBefore(shell, select.nextSibling);
            shell.appendChild(select);
            shell.appendChild(button);
            shell.appendChild(menu);
            button.addEventListener('click', function(event) {
                event.stopPropagation();
                document.querySelectorAll('.premium-select-shell.is-open').forEach(function(openShell) {
                    if (openShell !== shell) openShell.classList.remove('is-open');
                });
                shell.classList.toggle('is-open');
            });
            rebuild();
        }
        document.querySelectorAll('.applicant-final-review-per-page').forEach(enhance);
        document.addEventListener('click', function() {
            document.querySelectorAll('.premium-select-shell.is-open').forEach(function(shell) {
                shell.classList.remove('is-open');
            });
        });
    })();

</script>
@endpush
