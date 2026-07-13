@extends('layouts.admin')

@section('title', 'Developer Tools')
@section('disable_voice_inputs', 'true')

@push('styles')
<style>
    .dev-shell {
        max-width: 1120px;
        margin: 0 auto;
        padding: 22px 24px 42px;
        color: #111827;
    }

    .dev-hero {
        position: relative;
        overflow: hidden;
        border-radius: 0 0 22px 22px;
        padding: 22px 24px;
        margin-bottom: 24px;
        background: linear-gradient(135deg, rgba(255,255,255,.96), rgba(255,248,231,.86));
        border: 1px solid rgba(112, 19, 27, .10);
        border-bottom: 2px solid rgba(234, 215, 160, .9);
        box-shadow: 0 18px 34px rgba(112, 19, 27, .08);
    }

    .dev-hero::after {
        content: "";
        position: absolute;
        inset: -60% auto auto -35%;
        width: 42%;
        height: 220%;
        background: linear-gradient(115deg, transparent, rgba(250,204,21,.32), transparent);
        transform: rotate(18deg);
        animation: devSweep 7s ease-in-out infinite;
        pointer-events: none;
    }

    .dev-hero h1 {
        margin: 0;
        display: inline-flex;
        align-items: center;
        gap: 10px;
        font-size: 1.55rem;
        font-weight: 900;
        color: #111827;
        padding-bottom: 8px;
        border-bottom: 2px solid rgba(112, 19, 27, .72);
    }

    .dev-hero h1 svg {
        width: 20px;
        height: 20px;
    }

    .dev-hero p {
        margin: 8px 0 0;
        color: #64748b;
        max-width: 680px;
        line-height: 1.6;
    }

    .dev-grid {
        display: grid;
        grid-template-columns: repeat(2, minmax(280px, 380px));
        justify-content: center;
        gap: 24px;
    }

    .dev-card {
        position: relative;
        overflow: hidden;
        width: min(380px, 100%);
        min-height: 280px;
        padding: 26px 24px;
        border: 1px solid rgba(128, 0, 0, .14);
        border-radius: 28px;
        background: linear-gradient(145deg, #ffffff 0%, #f8fafc 52%, #e5e7eb 100%);
        color: inherit;
        text-align: left;
        text-decoration: none;
        box-shadow:
            0 0 0 1px rgba(112,19,27,.06),
            0 26px 44px rgba(112,19,27,.10),
            0 56px 78px -42px rgba(15,23,42,.28);
        transition: transform .22s ease, box-shadow .22s ease, border-color .22s ease;
    }

    button.dev-card {
        font: inherit;
        cursor: pointer;
    }

    .dev-card::before {
        content: "";
        position: absolute;
        inset: 0;
        background: linear-gradient(180deg, rgba(255,255,255,.9), rgba(255,255,255,.18));
        pointer-events: none;
    }

    .dev-card::after {
        content: "";
        position: absolute;
        top: -42%;
        left: -130%;
        width: 120%;
        height: 185%;
        background: linear-gradient(115deg, transparent, rgba(250,204,21,.50), transparent);
        transform: skewX(-20deg);
        opacity: 0;
        transition: left .8s ease, opacity .18s ease;
        pointer-events: none;
    }

    .dev-card:hover {
        transform: translateY(-4px);
        border-color: rgba(234,179,8,.62);
        box-shadow:
            0 0 0 1px rgba(250,204,21,.22),
            0 28px 46px rgba(234,179,8,.18),
            0 60px 84px -42px rgba(202,138,4,.38);
    }

    .dev-card:hover::after {
        opacity: 1;
        left: 125%;
    }

    .dev-card > * {
        position: relative;
        z-index: 1;
    }

    .dev-icon {
        width: 70px;
        height: 70px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        margin-bottom: 18px;
        border-radius: 22px;
        color: #fff;
        background: linear-gradient(145deg, #70131B, #8f2230);
        box-shadow: inset 0 1px 0 rgba(255,255,255,.18), 0 18px 30px rgba(112,19,27,.24);
        animation: devFloat 3.8s ease-in-out infinite;
        transition: background .22s ease, color .22s ease;
    }

    .dev-card:nth-child(2) .dev-icon {
        animation-delay: .45s;
    }

    .dev-card:hover .dev-icon {
        background: linear-gradient(145deg, #facc15, #f59e0b);
        color: #3f0b15;
    }

    .dev-icon svg {
        width: 31px;
        height: 31px;
        stroke: currentColor;
    }

    .dev-card h2 {
        margin: 0 0 10px;
        font-size: 1.28rem;
        font-weight: 900;
        color: #111827;
    }

    .dev-card p {
        margin: 0;
        color: #475569;
        line-height: 1.62;
    }

    .dev-note {
        margin-top: 18px;
        padding: 12px 14px;
        border-radius: 16px;
        background: rgba(250,204,21,.12);
        border: 1px solid rgba(250,204,21,.28);
        color: #713f12;
        font-size: .86rem;
        line-height: 1.5;
        font-weight: 800;
    }

    .dev-action {
        display: inline-flex;
        align-items: center;
        gap: 8px;
        margin-top: 18px;
        color: #70131b;
        font-weight: 900;
    }

    .dev-action::after {
        content: "->";
        transition: transform .2s ease;
    }

    .dev-card:hover .dev-action::after {
        transform: translateX(4px);
    }

    .dev-modal {
        position: fixed;
        inset: 0;
        display: none;
        align-items: center;
        justify-content: center;
        padding: clamp(14px, 3vw, 28px);
        background: rgba(15, 23, 42, .56);
        backdrop-filter: blur(10px);
        -webkit-backdrop-filter: blur(10px);
        z-index: 500050;
    }

    .dev-modal.is-open {
        display: flex;
    }

    .dev-panel {
        width: min(760px, 100%);
        max-height: min(860px, calc(100dvh - 32px));
        overflow: hidden;
        display: flex;
        flex-direction: column;
        border-radius: 26px;
        background: linear-gradient(145deg, #ffffff, #f8fafc 58%, #eef2f7);
        border: 1px solid rgba(128,0,0,.14);
        box-shadow: 0 30px 74px rgba(15,23,42,.26);
    }

    .dev-panel-head {
        display: flex;
        justify-content: space-between;
        gap: 16px;
        padding: 20px 22px;
        background: linear-gradient(135deg, #70131B, #8f2230);
        color: #fff;
        flex: 0 0 auto;
    }

    .dev-panel-head h2 {
        margin: 0;
        font-size: 1.22rem;
        font-weight: 900;
        color: #ffffff !important;
    }

    .dev-panel-head p {
        margin: 5px 0 0;
        color: #ffffff !important;
        line-height: 1.5;
    }

    .dev-close {
        width: 40px;
        height: 40px;
        position: relative;
        overflow: hidden;
        border: 1px solid #8f2230;
        border-radius: 999px;
        background: linear-gradient(135deg, #70131B, #8f2230);
        color: #ffffff;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        cursor: pointer;
        flex: 0 0 40px;
        box-shadow: 0 10px 22px rgba(112,19,27,.20);
        transition: color .08s linear, transform .18s ease, box-shadow .18s ease, border-color .18s ease, background .18s ease;
        z-index: 0;
    }

    .dev-close::after {
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

    .dev-close:hover {
        transform: translateY(-1px);
        background: #facc15;
        color: #111827;
        border-color: #facc15;
        box-shadow: 0 14px 26px rgba(112,19,27,.18);
    }

    .dev-close:hover::after {
        transform: translateX(135%);
    }

    .dev-close svg {
        width: 18px;
        height: 18px;
    }

    .dev-panel-body {
        display: grid;
        gap: 14px;
        padding: 22px;
        min-height: 0;
        overflow-y: auto;
        scrollbar-width: thin;
        scrollbar-color: rgba(112,19,27,.36) transparent;
    }

    .dev-panel-body::-webkit-scrollbar {
        width: 8px;
    }

    .dev-panel-body::-webkit-scrollbar-thumb {
        border-radius: 999px;
        background: rgba(112,19,27,.36);
    }

    .dev-options-grid {
        display: grid;
        grid-template-columns: repeat(2, minmax(0, 1fr));
        gap: 14px;
    }

    .dev-option-block {
        position: relative;
        overflow: hidden;
        padding: 16px;
        border-radius: 20px;
        background: rgba(255,255,255,.90);
        border: 1px solid rgba(112,19,27,.12);
        box-shadow: 0 14px 28px rgba(112,19,27,.08);
    }

    .dev-option-block::before {
        content: "";
        position: absolute;
        inset: 0 auto auto 0;
        width: 100%;
        height: 4px;
        background: linear-gradient(90deg, #70131B, #8f2230, #facc15);
    }

    .dev-option-kicker {
        margin: 0 0 8px;
        color: #70131b;
        font-size: .72rem;
        font-weight: 900;
        letter-spacing: .08em;
        text-transform: uppercase;
    }

    .dev-option-title {
        margin: 0;
        color: #111827;
        font-size: 1rem;
        font-weight: 900;
    }

    .dev-option-copy {
        margin: 6px 0 0;
        color: #64748b;
        font-size: .84rem;
        line-height: 1.55;
    }

    .dev-option-list {
        display: grid;
        gap: 8px;
        margin-top: 14px;
    }

    .dev-option-row {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 12px;
        padding: 9px 10px;
        border-radius: 13px;
        background: rgba(112,19,27,.05);
        color: #334155;
        font-size: .82rem;
        font-weight: 800;
    }

    .dev-option-pill {
        display: inline-flex;
        align-items: center;
        min-height: 26px;
        padding: 0 9px;
        border-radius: 999px;
        background: rgba(250,204,21,.18);
        color: #713f12;
        font-size: .72rem;
        font-weight: 900;
        white-space: nowrap;
    }

    .dev-command-grid {
        display: grid;
        grid-template-columns: repeat(2, minmax(0, 1fr));
        gap: 10px;
        margin-top: 12px;
    }

    .dev-command-btn {
        min-height: 44px;
        padding: 10px 14px;
        border-radius: 14px;
        border: 1px solid rgba(112,19,27,.16);
        background: linear-gradient(180deg, rgba(255,255,255,.98), rgba(250,244,246,.98));
        color: #70131b;
        font-size: .82rem;
        font-weight: 900;
        text-align: left;
        cursor: not-allowed;
        box-shadow: 0 10px 20px rgba(112,19,27,.08);
    }

    .dev-status-list {
        display: grid;
        gap: 8px;
        margin-top: 12px;
    }

    .dev-status-item {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 10px;
        padding: 9px 10px;
        border-radius: 13px;
        background: rgba(127,29,45,.05);
        color: #334155;
        font-size: .82rem;
        font-weight: 800;
    }

    .dev-password-box {
        display: grid;
        gap: 10px;
        margin-top: 14px;
        padding: 14px;
        border-radius: 18px;
        border: 1px solid rgba(112,19,27,.14);
        background: rgba(255,255,255,.82);
    }

    .dev-password-field {
        display: grid;
        gap: 6px;
    }

    .dev-password-field label {
        color: #70131b;
        font-size: .74rem;
        font-weight: 900;
        letter-spacing: .08em;
        text-transform: uppercase;
    }

    .dev-password-field input {
        width: 100%;
        min-height: 44px;
        padding: 11px 13px;
        border-radius: 14px;
        border: 1px solid rgba(112,19,27,.16);
        background: rgba(255,255,255,.95);
        color: #334155;
        font-size: .84rem;
        font-weight: 800;
        outline: none;
    }

    .dev-password-field input::placeholder {
        color: #94a3b8;
        font-weight: 700;
    }

    .dev-password-field input:disabled {
        cursor: not-allowed;
        background: rgba(248,250,252,.96);
    }

    .dev-password-note {
        color: #64748b;
        font-size: .78rem;
        line-height: 1.45;
    }

    .dev-mini-summary {
        display: grid;
        gap: 8px;
        margin-top: 12px;
    }

    .dev-mini-line {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 10px;
        padding: 9px 10px;
        border-radius: 13px;
        background: rgba(127,29,45,.05);
        color: #334155;
        font-size: .82rem;
        font-weight: 800;
    }

    .dev-logo-preview {
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 12px;
        margin-top: 14px;
    }

    .dev-logo-box {
        min-height: 154px;
        padding: 14px;
        border-radius: 18px;
        border: 1px solid rgba(112,19,27,.14);
        background: rgba(255,255,255,.82);
        display: grid;
        align-content: center;
        justify-items: center;
        gap: 10px;
        text-align: center;
    }

    .dev-logo-box img {
        width: 72px;
        height: 72px;
        object-fit: contain;
        padding: 8px;
        border-radius: 18px;
        background: #fff;
        border: 1px solid rgba(112,19,27,.12);
        box-shadow: 0 12px 20px rgba(112,19,27,.10);
    }

    .dev-logo-placeholder {
        width: 72px;
        height: 72px;
        border-radius: 18px;
        border: 1px dashed rgba(112,19,27,.35);
        background: rgba(250,204,21,.12);
        color: #70131b;
        display: inline-flex;
        align-items: center;
        justify-content: center;
    }

    .dev-logo-placeholder svg {
        width: 28px;
        height: 28px;
    }

    .dev-logo-label {
        color: #334155;
        font-size: .82rem;
        font-weight: 900;
    }

    .dev-logo-hint {
        color: #64748b;
        font-size: .76rem;
        line-height: 1.4;
    }

    .dev-static-action,
    .dev-static-toggle {
        width: 100%;
        min-height: 56px;
        padding: 14px 16px;
        border: 1px solid rgba(112,19,27,.16);
        background: rgba(255,255,255,.9);
        color: #70131b;
        box-shadow: 0 12px 24px rgba(112,19,27,.08);
        cursor: not-allowed;
    }

    .dev-static-action {
        border-radius: 999px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        gap: 10px;
        font-weight: 900;
        margin-top: 14px;
    }

    .dev-static-action svg {
        width: 19px;
        height: 19px;
    }

    .dev-static-toggle {
        border-radius: 18px;
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 16px;
        text-align: left;
    }

    .dev-static-toggle strong,
    .dev-static-toggle span {
        display: block;
    }

    .dev-static-toggle strong {
        font-size: .94rem;
        font-weight: 900;
    }

    .dev-static-toggle span span {
        margin-top: 4px;
        color: #64748b;
        font-size: .8rem;
        line-height: 1.45;
    }

    .dev-toggle-track {
        width: 52px;
        height: 30px;
        flex: 0 0 52px;
        padding: 3px;
        border-radius: 999px;
        background: rgba(100,116,139,.22);
        border: 1px solid rgba(100,116,139,.20);
    }

    .dev-toggle-knob {
        display: block;
        width: 24px;
        height: 24px;
        border-radius: 999px;
        background: #fff;
        box-shadow: 0 6px 12px rgba(15,23,42,.16);
    }

    .dev-pin-form {
        display: grid;
        gap: 10px;
        margin-top: 14px;
    }

    .dev-live-toggle {
        min-height: 58px;
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 14px;
        padding: 12px;
        border-radius: 15px;
        border: 1px solid rgba(112,19,27,.14);
        background: rgba(255,255,255,.86);
        cursor: pointer;
    }

    .dev-live-toggle > span:first-child,
    .dev-live-toggle strong,
    .dev-live-toggle span span {
        display: block;
    }

    .dev-live-toggle strong {
        color: #111827;
        font-size: .88rem;
        font-weight: 950;
    }

    .dev-live-toggle span span {
        margin-top: 3px;
        color: #64748b;
        font-size: .76rem;
        line-height: 1.35;
    }

    .dev-live-toggle input {
        position: absolute;
        opacity: 0;
        pointer-events: none;
    }

    .dev-live-toggle input:checked + .dev-toggle-track {
        background: #70131B;
        border-color: #70131B;
    }

    .dev-live-toggle input:checked + .dev-toggle-track .dev-toggle-knob {
        transform: translateX(22px);
        background: #facc15;
    }

    .dev-live-toggle input:disabled + .dev-toggle-track {
        opacity: .45;
    }

    .dev-live-toggle .dev-toggle-knob {
        transition: transform .18s ease, background .18s ease;
    }

    .dev-pin-fields {
        display: grid;
        grid-template-columns: repeat(2, minmax(0, 1fr));
        gap: 10px;
        padding: 12px;
        border-radius: 16px;
        border: 1px solid rgba(112,19,27,.14);
        background: rgba(255,255,255,.78);
    }

    .dev-pin-save,
    .dev-pin-reset {
        position: relative;
        overflow: hidden;
        min-height: 44px;
        border: 1px solid #70131B;
        border-radius: 10px;
        background: #70131B;
        color: #ffffff;
        font-weight: 950;
        cursor: pointer;
        transition: background .18s ease, color .18s ease, border-color .18s ease, transform .18s ease;
    }

    .dev-pin-reset {
        background: #ffffff;
        color: #70131B;
    }

    .dev-pin-save::after,
    .dev-pin-reset::after {
        content: "";
        position: absolute;
        top: -35%;
        left: -72%;
        width: 48%;
        height: 170%;
        background: linear-gradient(120deg, transparent 0%, rgba(255, 244, 180, .18) 34%, rgba(255, 244, 180, .58) 50%, rgba(255, 244, 180, .18) 66%, transparent 100%);
        transform: skewX(-18deg);
        transition: left .48s ease;
        pointer-events: none;
    }

    .dev-pin-save:hover,
    .dev-pin-save:focus-visible,
    .dev-pin-reset:hover,
    .dev-pin-reset:focus-visible {
        background: #facc15;
        border-color: #facc15;
        color: #70131B;
        outline: none;
        transform: translateY(-1px);
    }

    .dev-pin-save:hover::after,
    .dev-pin-save:focus-visible::after,
    .dev-pin-reset:hover::after,
    .dev-pin-reset:focus-visible::after {
        left: 128%;
    }

    .dev-pin-error {
        padding: 10px 12px;
        border-radius: 12px;
        background: #fee2e2;
        color: #991b1b;
        font-size: .78rem;
        font-weight: 850;
    }

    .dev-pin-controls {
        display: grid;
        gap: 9px;
    }

    .dev-reset-modal {
        position: fixed;
        inset: 0;
        z-index: 6000;
        display: none;
        align-items: center;
        justify-content: center;
        padding: 20px;
        background: rgba(15, 23, 42, .56);
        backdrop-filter: blur(8px);
    }

    .dev-reset-modal.is-open {
        display: flex;
    }

    .dev-reset-dialog {
        width: min(520px, 100%);
        overflow: hidden;
        border-radius: 20px;
        border: 1px solid rgba(255,255,255,.18);
        background: #ffffff;
        box-shadow: 0 24px 70px rgba(15, 23, 42, .26);
    }

    .dev-reset-head {
        display: flex;
        justify-content: space-between;
        align-items: flex-start;
        gap: 14px;
        padding: 20px;
        background: #8f1827;
        color: #ffffff;
    }

    .dev-reset-head h3 {
        margin: 0;
        color: #ffffff;
        font-size: 20px;
        font-weight: 950;
    }

    .dev-reset-head p {
        margin: 4px 0 0;
        color: rgba(255,255,255,.86);
        font-size: 13px;
        font-weight: 700;
    }

    .dev-reset-close {
        width: 42px;
        height: 42px;
        border-radius: 999px;
        border: 1px solid rgba(255,255,255,.22);
        background: rgba(255,255,255,.12);
        color: #ffffff;
        font-size: 24px;
        cursor: pointer;
    }

    .dev-reset-close:hover,
    .dev-reset-close:focus-visible {
        background: #facc15;
        border-color: #facc15;
        color: #70131B;
        outline: none;
    }

    .dev-reset-form {
        display: grid;
        gap: 12px;
        padding: 20px;
    }

    html[data-theme="dark"] .dev-hero {
        background: linear-gradient(135deg, rgba(35,17,25,.96), rgba(24,11,18,.94));
        border-color: rgba(250,204,21,.18);
    }

    html[data-theme="dark"] .dev-hero h1,
    html[data-theme="dark"] .dev-card h2 {
        color: #f8fafc;
    }

    html[data-theme="dark"] .dev-hero p,
    html[data-theme="dark"] .dev-card p {
        color: #cbd5e1;
    }

    html[data-theme="dark"] .dev-card,
    html[data-theme="dark"] .dev-panel {
        background: linear-gradient(145deg, #5f0012 0%, #7d0b17 45%, #5a0010 100%);
        border-color: rgba(250,204,21,.18);
    }

    html[data-theme="dark"] .dev-card::before {
        background: linear-gradient(180deg, rgba(193,138,16,.14), rgba(95,0,18,.12));
    }

    html[data-theme="dark"] .dev-note,
    html[data-theme="dark"] .dev-static-action,
    html[data-theme="dark"] .dev-static-toggle,
    html[data-theme="dark"] .dev-option-block {
        background: rgba(255,255,255,.08);
        border-color: rgba(250,204,21,.22);
        color: #f0d15a;
    }

    html[data-theme="dark"] .dev-action,
    html[data-theme="dark"] .dev-static-toggle span span,
    html[data-theme="dark"] .dev-option-kicker {
        color: #f0d15a;
    }

    html[data-theme="dark"] .dev-option-title {
        color: #f8fafc;
    }

    html[data-theme="dark"] .dev-option-copy {
        color: #cbd5e1;
    }

    html[data-theme="dark"] .dev-option-row {
        background: rgba(255,255,255,.07);
        color: #e5eefb;
    }

    html[data-theme="dark"] .dev-mini-line {
        background: rgba(255,255,255,.07);
        color: #e5eefb;
    }

    html[data-theme="dark"] .dev-command-btn {
        background: rgba(255,255,255,.08);
        color: #f0d15a;
        border-color: rgba(250,204,21,.22);
    }

    html[data-theme="dark"] .dev-status-item {
        background: rgba(255,255,255,.07);
        color: #e5eefb;
    }

    html[data-theme="dark"] .dev-password-box {
        background: rgba(255,255,255,.07);
        border-color: rgba(250,204,21,.20);
    }

    html[data-theme="dark"] .dev-password-field label {
        color: #f0d15a;
    }

    html[data-theme="dark"] .dev-password-field input {
        background: rgba(255,255,255,.08);
        border-color: rgba(250,204,21,.20);
        color: #f8fafc;
    }

    html[data-theme="dark"] .dev-password-field input::placeholder {
        color: #cbd5e1;
    }

    html[data-theme="dark"] .dev-password-note {
        color: #cbd5e1;
    }

    html[data-theme="dark"] .dev-live-toggle,
    html[data-theme="dark"] .dev-pin-fields {
        background: rgba(15, 23, 42, .42);
        border-color: rgba(250, 204, 21, .16);
    }

    html[data-theme="dark"] .dev-live-toggle strong {
        color: #ffffff;
    }

    html[data-theme="dark"] .dev-live-toggle span span {
        color: #cbd5e1;
    }

    html[data-theme="dark"] .dev-logo-box {
        background: rgba(255,255,255,.07);
        border-color: rgba(250,204,21,.20);
    }

    html[data-theme="dark"] .dev-logo-label {
        color: #f8fafc;
    }

    html[data-theme="dark"] .dev-logo-hint {
        color: #cbd5e1;
    }

    @keyframes devFloat {
        0%, 100% { transform: translateY(0); }
        50% { transform: translateY(-5px); }
    }

    @keyframes devSweep {
        0%, 55% { opacity: 0; transform: translateX(0) rotate(18deg); }
        70% { opacity: 1; }
        100% { opacity: 0; transform: translateX(340%) rotate(18deg); }
    }

    @media (max-width: 900px) {
        .dev-grid {
            grid-template-columns: 1fr;
        }

        .dev-options-grid {
            grid-template-columns: 1fr;
        }

        .dev-command-grid {
            grid-template-columns: 1fr;
        }

        .dev-logo-preview {
            grid-template-columns: 1fr;
        }
    }
    /* Settings-style Developer Tools hub */
    .dev-shell {
        width: min(100%, 980px);
        max-width: 980px;
        margin: 0 auto;
        padding: 16px 16px 18px;
        border-radius: 12px;
        border: 1px solid rgba(112, 19, 27, 0.10);
        background: rgba(255, 255, 255, 0.94);
        box-shadow: 0 18px 34px rgba(112, 19, 27, 0.08);
        overflow: hidden;
        position: relative;
    }

    .dev-shell::before {
        content: "";
        position: absolute;
        top: 0;
        left: 12px;
        right: 12px;
        height: 5px;
        border-radius: 999px;
        background: #70131B;
        z-index: 2;
    }

    .dev-shell > * {
        position: relative;
        z-index: 3;
    }

    .dev-hero {
        margin: 0 0 20px;
        padding: 0;
        border: 0;
        border-radius: 0;
        background: transparent;
        box-shadow: none;
    }

    .dev-hero::after {
        content: none;
    }

    .dev-hero h1 {
        display: block;
        margin: 0 0 8px;
        padding: 0;
        border: 0;
        color: #8b0000;
        font-size: 13px;
        font-weight: 900;
        letter-spacing: 1px;
        line-height: 1.2;
        text-transform: uppercase;
    }

    .dev-hero h1 svg {
        display: none;
    }

    .dev-hero p {
        max-width: 760px;
        margin: 0;
        color: #0f172a;
        font-size: 20px;
        font-weight: 900;
        line-height: 1.2;
    }

    .dev-grid {
        grid-template-columns: repeat(2, minmax(0, 1fr));
        justify-content: stretch;
        gap: 12px;
        margin-top: 20px;
    }

    .dev-card,
    button.dev-card {
        position: relative;
        width: 100%;
        min-height: 246px;
        display: block;
        padding: 16px;
        border-radius: 12px;
        border: 1px solid rgba(112, 19, 27, 0.46);
        background: linear-gradient(135deg, #70131B, #8f2230);
        color: #ffffff;
        box-shadow: inset 0 -3px 0 rgba(250, 204, 21, 0.72), 0 10px 24px rgba(112, 19, 27, 0.18);
        overflow: hidden;
        text-decoration: none;
        transition: transform .2s ease, box-shadow .2s ease, border-color .2s ease, color .2s ease, background .2s ease;
    }

    .dev-card::before {
        background: linear-gradient(180deg, rgba(255,255,255,0.08) 0%, rgba(255,255,255,0) 38%);
        z-index: 0;
    }

    .dev-card::after {
        top: -38%;
        bottom: -38%;
        left: -135%;
        width: 34%;
        height: auto;
        opacity: 0;
        background: linear-gradient(105deg, rgba(255, 255, 255, 0) 0%, rgba(255, 246, 184, 0.18) 42%, rgba(255, 246, 184, 0.54) 50%, rgba(255, 246, 184, 0.18) 58%, rgba(255, 255, 255, 0) 100%);
        transform: translateX(0) skewX(-18deg);
        transition: none;
    }

    .dev-card:hover,
    .dev-card:focus-visible {
        background: #facc15 !important;
        background-image: none !important;
        color: #70131B !important;
        transform: translateY(-8px);
        border-color: #facc15;
        box-shadow: 0 0 0 3px rgba(250, 204, 21, 0.12), 0 20px 30px rgba(139, 0, 0, 0.22);
        outline: none;
    }

    .dev-card:hover::after,
    .dev-card:focus-visible::after {
        opacity: 1;
        animation: devSettingsSweep .92s ease both;
    }

    @keyframes devSettingsSweep {
        0% { opacity: 0; transform: translateX(0) skewX(-18deg); }
        18% { opacity: .72; }
        72% { opacity: .72; }
        100% { opacity: 0; transform: translateX(820%) skewX(-18deg); }
    }

    .dev-icon {
        width: 48px;
        height: 48px;
        margin-bottom: 14px;
        border-radius: 13px;
        background: rgba(255, 248, 196, 0.12);
        color: #facc15;
        border: 1px solid rgba(255, 248, 196, 0.16);
        box-shadow: none;
    }

    .dev-card:hover .dev-icon,
    .dev-card:focus-visible .dev-icon {
        background: #70131B;
        color: #facc15;
        border-color: rgba(112, 19, 27, 0.62);
    }

    .dev-icon svg {
        width: 21px;
        height: 21px;
        stroke: currentColor;
    }

    .dev-card h2 {
        margin: 0 0 8px;
        color: #ffffff;
        font-size: 16px;
        font-weight: 900;
        line-height: 1.25;
    }

    .dev-card p,
    .dev-action {
        color: #ffffff;
        font-size: 14px;
        font-weight: 700;
        line-height: 1.42;
    }

    .dev-note {
        display: none;
    }

    .dev-action {
        margin-top: 16px;
    }

    .dev-card:hover h2,
    .dev-card:focus-visible h2,
    .dev-card:hover p,
    .dev-card:focus-visible p,
    .dev-card:hover .dev-action,
    .dev-card:focus-visible .dev-action {
        color: #70131B;
    }

    html[data-theme="dark"] .dev-shell {
        background: linear-gradient(180deg, rgba(70, 19, 27, 0.92), rgba(46, 13, 19, 0.96));
        border-color: rgba(255,255,255,.08);
        box-shadow: 0 20px 38px rgba(0,0,0,.24);
    }

    html[data-theme="dark"] .dev-shell::before {
        background: #facc15;
    }

    html[data-theme="dark"] .dev-hero h1,
    html[data-theme="dark"] .dev-hero p {
        color: #ffffff;
    }

    @media (max-width: 720px) {
        .dev-shell {
            width: 100%;
            max-width: 100%;
            padding: 18px;
        }

        .dev-grid {
            grid-template-columns: 1fr;
        }
    }
    @media (min-width: 901px) {
        .dev-grid {
            grid-template-columns: repeat(4, minmax(0, 1fr)) !important;
            align-items: stretch;
        }
    }
    .dev-card,
    button.dev-card {
        text-align: left;
    }
    .dev-card h2,
    .dev-card p,
    .dev-card .dev-action {
        color: #ffffff !important;
    }
    .dev-card:hover h2,
    .dev-card:focus-visible h2,
    .dev-card:hover p,
    .dev-card:focus-visible p,
    .dev-card:hover .dev-action,
    .dev-card:focus-visible .dev-action {
        color: #70131B !important;
    }

    /* Patient Intake sizing parity */
    .dev-grid {
        grid-template-columns: repeat(auto-fit, minmax(220px, 1fr)) !important;
        gap: 16px !important;
        margin-top: 24px !important;
    }
    .dev-card,
    button.dev-card {
        min-height: unset !important;
        height: 100% !important;
        padding: 20px !important;
        border-radius: 16px !important;
    }
    .dev-icon {
        width: 58px !important;
        height: 58px !important;
        border-radius: 16px !important;
        margin-bottom: 14px !important;
    }
    .dev-icon svg {
        width: 24px !important;
        height: 24px !important;
    }
    .dev-card h2 {
        margin: 0 0 8px !important;
        font-size: 18px !important;
        line-height: 1.25 !important;
    }
    .dev-card p,
    .dev-card .dev-action {
        font-size: 16px !important;
        line-height: 1.55 !important;
    }
    .dev-action {
        margin-top: 16px !important;
    }
    @media (min-width: 721px) {
        .dev-grid {
            grid-template-columns: repeat(2, minmax(220px, 224px)) !important;
            justify-content: center !important;
            align-items: stretch !important;
        }
    }
    @media (max-width: 720px) {
        .dev-grid {
            grid-template-columns: 1fr !important;
            justify-content: stretch !important;
        }
    }
    @media (min-width: 721px) {
        .dev-card,
        button.dev-card {
            min-height: 293px !important;
        }
    }
</style>
@endpush

@section('content')
@php
    $apiTestingRoute = request()->routeIs('assistant.*') ? route('assistant.api-testing') : route('admin.api-testing');
@endphp

<div class="dev-shell">
    <section class="dev-hero">
        <h1><x-outline-icon name="code-bracket-square" />Developer Tools</h1>
        <p>Protected utilities for integration testing and maintenance preparation.</p>
    </section>

    <div class="dev-grid">
        <a href="{{ $apiTestingRoute }}" class="dev-card">
            <span class="dev-icon" aria-hidden="true">
                <svg viewBox="0 0 24 24" fill="none" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M8 9 4 12l4 3"/>
                    <path d="m16 9 4 3-4 3"/>
                    <path d="m14 5-4 14"/>
                </svg>
            </span>
            <h2>API Dashboard</h2>
            <p>Validate connected systems and inspect integration responses.</p>
            <div class="dev-note">Use for endpoint checks and response review.</div>
            <div class="dev-action">Open integration tester</div>
        </a>

        <button type="button" class="dev-card" id="openDeveloperOptionsPanel">
            <span class="dev-icon" aria-hidden="true">
                <svg viewBox="0 0 24 24" fill="none" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">
                    <ellipse cx="12" cy="5" rx="8" ry="3"/>
                    <path d="M4 5v6c0 1.7 3.6 3 8 3s8-1.3 8-3V5"/>
                    <path d="M4 11v6c0 1.7 3.6 3 8 3s8-1.3 8-3v-6"/>
                </svg>
            </span>
            <h2>Developer Options</h2>
            <p>Open prepared maintenance controls for future developer use.</p>
            <div class="dev-note">Static controls only.</div>
            <div class="dev-action">Open options</div>
        </button>
    </div>
</div>

<div class="dev-modal" id="developerOptionsPanel" aria-hidden="true">
    <section class="dev-panel" role="dialog" aria-modal="true" aria-labelledby="developerOptionsTitle">
        <div class="dev-panel-head">
            <div>
                <h2 id="developerOptionsTitle">Developer Options</h2>
                <p>Prepared controls for future maintenance workflows.</p>
            </div>
            <button type="button" class="dev-close" id="closeDeveloperOptionsPanel" aria-label="Close Developer Options">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.1" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                    <path d="M18 6 6 18M6 6l12 12"/>
                </svg>
            </button>
        </div>
        <div class="dev-panel-body">
            <div class="dev-options-grid">
                <section class="dev-option-block">
                    <p class="dev-option-kicker">Access</p>
                    <h3 class="dev-option-title">Sign-In Controls</h3>
                    <p class="dev-option-copy">Future controls for One Portal status and emergency fallback access.</p>
                    <div class="dev-mini-summary">
                        <div class="dev-mini-line"><span>One Portal / IdP</span><span class="dev-option-pill">Static</span></div>
                        <div class="dev-mini-line"><span>Emergency Login</span><span class="dev-option-pill">Static</span></div>
                        <div class="dev-mini-line"><span>Allowed Role</span><span class="dev-option-pill">Static</span></div>
                    </div>
                    <div class="dev-password-box">
                        <div class="dev-password-field">
                            <label for="devTeamPassword">Team Password</label>
                            <input
                                type="password"
                                id="devTeamPassword"
                                value="••••••••••"
                                disabled
                                aria-label="Static team password placeholder"
                            >
                        </div>
                        <div class="dev-password-note">
                            Static placeholder for a future backup password update flow.
                        </div>
                    </div>
                </section>

                <section class="dev-option-block">
                    <p class="dev-option-kicker">Integrations</p>
                    <h3 class="dev-option-title">Integration PIN</h3>
                    <p class="dev-option-copy">Control access to Integration Tokens from API Testing and direct routes.</p>
                    <form method="POST" action="{{ route('admin.integration-pin.update') }}" class="dev-pin-form">
                        @csrf
                        @method('PUT')
                        @php
                            $devPinUser = auth()->user();
                            $pinEnabled = (bool) ($devPinUser->api_pin_enabled ?? false);
                            $hasPin = trim((string) ($devPinUser->api_pin ?? '')) !== '';
                        @endphp
                        <label class="dev-live-toggle">
                            <span>
                                <strong>Turn on PIN</strong>
                                <span>Require a 4-digit PIN before opening Integration Tokens.</span>
                            </span>
                            <input type="checkbox" name="api_pin_enabled" value="1" id="devApiPinEnabled" {{ $pinEnabled ? 'checked' : '' }}>
                            <span class="dev-toggle-track" aria-hidden="true"><span class="dev-toggle-knob"></span></span>
                        </label>
                        <div class="dev-pin-fields" id="devApiPinFields" {{ $pinEnabled ? '' : 'hidden' }}>
                            @if($hasPin)
                                <div class="dev-password-field">
                                    <label for="devCurrentApiPin">Current PIN</label>
                                    <input type="password" id="devCurrentApiPin" name="current_api_pin" inputmode="numeric" pattern="[0-9]{4}" maxlength="4" placeholder="{{ $pinEnabled ? 'Only needed when turning PIN back on' : 'Enter current 4-digit PIN' }}">
                                </div>
                            @else
                                <div class="dev-password-field">
                                    <label for="devApiPin">4-Digit PIN</label>
                                    <input type="password" id="devApiPin" name="api_pin" inputmode="numeric" pattern="[0-9]{4}" maxlength="4" placeholder="Enter 4-digit PIN">
                                </div>
                                <div class="dev-password-field">
                                    <label for="devApiPinConfirm">Confirm PIN</label>
                                    <input type="password" id="devApiPinConfirm" name="api_pin_confirmation" inputmode="numeric" pattern="[0-9]{4}" maxlength="4" placeholder="Confirm 4-digit PIN">
                                </div>
                            @endif
                        </div>
                        <div class="dev-pin-controls" id="devApiPinControls" {{ $pinEnabled ? '' : 'hidden' }}>
                            <div class="dev-password-note">
                            Status: {{ $pinEnabled ? ($hasPin ? 'PIN required' : 'PIN setup needed') : 'PIN not required' }}
                            </div>
                            @error('api_pin')
                                <div class="dev-pin-error">{{ $message }}</div>
                            @enderror
                            @error('current_api_pin')
                                <div class="dev-pin-error">{{ $message }}</div>
                            @enderror
                            <button type="submit" class="dev-pin-save">Save Integration PIN</button>
                            @if($hasPin)
                                <button type="button" class="dev-pin-reset" id="openResetIntegrationPin">Reset PIN / Forgot PIN</button>
                            @endif
                        </div>
                    </form>
                </section>

                <section class="dev-option-block">
                    <p class="dev-option-kicker">Branding</p>
                    <h3 class="dev-option-title">Clinic Logo</h3>
                    <p class="dev-option-copy">Static preview for a future logo update workflow.</p>
                    <div class="dev-logo-preview">
                        <div class="dev-logo-box">
                            <img src="{{ asset('images/clinic_logo_transparent.png') }}?v={{ filemtime(public_path('images/clinic_logo_transparent.png')) }}" alt="Current clinic logo">
                            <div>
                                <div class="dev-logo-label">Current Logo</div>
                                <div class="dev-logo-hint">Used in headers and navigation.</div>
                            </div>
                        </div>
                        <div class="dev-logo-box">
                            <span class="dev-logo-placeholder" aria-hidden="true">
                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">
                                    <path d="M12 5v14"/>
                                    <path d="M5 12h14"/>
                                </svg>
                            </span>
                            <div>
                                <div class="dev-logo-label">Add New Logo</div>
                                <div class="dev-logo-hint">Upload control will be connected later.</div>
                            </div>
                        </div>
                    </div>
                    <button type="button" class="dev-static-action" disabled>
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                            <path d="M15 8h.01"/>
                            <path d="M3 6a3 3 0 0 1 3-3h12a3 3 0 0 1 3 3v12a3 3 0 0 1-3 3H6a3 3 0 0 1-3-3V6z"/>
                            <path d="m3 16 5-5c.9-.9 2.1-.9 3 0l5 5"/>
                            <path d="m14 14 1-1c.9-.9 2.1-.9 3 0l3 3"/>
                        </svg>
                        Update Clinic Logo
                    </button>
                </section>

                <section class="dev-option-block">
                    <p class="dev-option-kicker">Policies</p>
                    <h3 class="dev-option-title">Student Side Notices</h3>
                    <p class="dev-option-copy">Static switches for future student-facing announcements.</p>
                    <button type="button" class="dev-static-toggle" disabled>
                        <span>
                            <strong>Maintenance Banner</strong>
                            <span>Display "Under Maintenance" on the student side.</span>
                        </span>
                        <span class="dev-toggle-track" aria-hidden="true">
                            <span class="dev-toggle-knob"></span>
                        </span>
                    </button>
                </section>
            </div>
        </div>
    </section>
</div>

<div class="dev-reset-modal" id="resetIntegrationPinModal" aria-hidden="true">
    <section class="dev-reset-dialog" role="dialog" aria-modal="true" aria-labelledby="resetIntegrationPinTitle">
        <header class="dev-reset-head">
            <div>
                <h3 id="resetIntegrationPinTitle">Reset Integration PIN</h3>
                <p>Enter the emergency login password, then set a new 4-digit PIN.</p>
            </div>
            <button type="button" class="dev-reset-close" id="closeResetIntegrationPin" aria-label="Close reset PIN modal">&times;</button>
        </header>
        <form method="POST" action="{{ route('admin.integration-pin.reset') }}" class="dev-reset-form">
            @csrf
            <div class="dev-password-field">
                <label for="resetEmergencyPassword">Emergency Login Password</label>
                <input type="password" id="resetEmergencyPassword" name="emergency_password" required placeholder="Enter emergency password">
            </div>
            <div class="dev-pin-fields">
                <div class="dev-password-field">
                    <label for="resetApiPin">New PIN</label>
                    <input type="password" id="resetApiPin" name="api_pin" inputmode="numeric" pattern="[0-9]{4}" maxlength="4" required placeholder="Enter 4-digit PIN">
                </div>
                <div class="dev-password-field">
                    <label for="resetApiPinConfirm">Confirm New PIN</label>
                    <input type="password" id="resetApiPinConfirm" name="api_pin_confirmation" inputmode="numeric" pattern="[0-9]{4}" maxlength="4" required placeholder="Confirm 4-digit PIN">
                </div>
            </div>
            @error('emergency_password')
                <div class="dev-pin-error">{{ $message }}</div>
            @enderror
            <button type="submit" class="dev-pin-save">Reset Integration PIN</button>
        </form>
    </section>
</div>
@endsection

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function () {
        const openButton = document.getElementById('openDeveloperOptionsPanel');
        const closeButton = document.getElementById('closeDeveloperOptionsPanel');
        const panel = document.getElementById('developerOptionsPanel');

        if (!openButton || !closeButton || !panel) {
            return;
        }

        const openPanel = () => {
            panel.classList.add('is-open');
            panel.setAttribute('aria-hidden', 'false');
        };

        const closePanel = () => {
            panel.classList.remove('is-open');
            panel.setAttribute('aria-hidden', 'true');
        };

        openButton.addEventListener('click', openPanel);
        closeButton.addEventListener('click', closePanel);
        panel.addEventListener('click', function (event) {
            if (event.target === panel) {
                closePanel();
            }
        });

        const pinEnabledToggle = document.getElementById('devApiPinEnabled');
        const pinFields = document.getElementById('devApiPinFields');
        const pinControls = document.getElementById('devApiPinControls');
        const pinForm = pinEnabledToggle?.closest('form');
        const resetPinButton = document.getElementById('openResetIntegrationPin');
        const resetPinModal = document.getElementById('resetIntegrationPinModal');
        const closeResetPinButton = document.getElementById('closeResetIntegrationPin');

        const syncPinControls = () => {
            if (!pinEnabledToggle || !pinFields || !pinControls) {
                return;
            }

            pinFields.hidden = !pinEnabledToggle.checked;
            pinControls.hidden = !pinEnabledToggle.checked;
        };

        pinEnabledToggle?.addEventListener('change', () => {
            syncPinControls();

            if (!pinEnabledToggle.checked) {
                pinForm?.requestSubmit();
            }
        });
        syncPinControls();

        const setResetPinModalOpen = (isOpen) => {
            if (!resetPinModal) {
                return;
            }
            resetPinModal.classList.toggle('is-open', isOpen);
            resetPinModal.setAttribute('aria-hidden', isOpen ? 'false' : 'true');
        };

        resetPinButton?.addEventListener('click', () => setResetPinModalOpen(true));
        closeResetPinButton?.addEventListener('click', () => setResetPinModalOpen(false));
        resetPinModal?.addEventListener('click', function (event) {
            if (event.target === resetPinModal) {
                setResetPinModalOpen(false);
            }
        });

        document.addEventListener('keydown', function (event) {
            if (event.key === 'Escape' && panel.classList.contains('is-open')) {
                closePanel();
            }
            if (event.key === 'Escape' && resetPinModal?.classList.contains('is-open')) {
                setResetPinModalOpen(false);
            }
        });
    });
</script>
@endpush
