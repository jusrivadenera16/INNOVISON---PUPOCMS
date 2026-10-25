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
        appearance: none;
        display: block;
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
        content: "\2192";
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
        display: inline-flex;
        align-items: center;
        gap: 12px;
        font-size: 1.22rem;
        font-weight: 900;
        color: #ffffff !important;
    }

    .dev-panel-title-icon {
        width: 44px;
        height: 44px;
        flex: 0 0 44px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        border-radius: 14px;
        background: rgba(255, 255, 255, .14);
        border: 1px solid rgba(255, 255, 255, .22);
        color: #ffffff;
    }

    .dev-panel-title-icon svg {
        width: 22px;
        height: 22px;
        stroke: currentColor;
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

    .dev-password-field input,
    .dev-password-select {
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

    .dev-password-input-wrap {
        position: relative;
    }

    .dev-password-input-wrap input {
        padding-right: 48px;
    }

    .dev-password-toggle {
        position: absolute;
        top: 50%;
        right: 9px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        width: 32px;
        height: 32px;
        padding: 0;
        border: 1px solid rgba(112, 19, 27, .16);
        border-radius: 10px;
        background: rgba(255, 255, 255, .86);
        color: #70131b;
        cursor: pointer;
        transform: translateY(-50%);
        transition: background .18s ease, color .18s ease, border-color .18s ease;
    }

    .dev-password-toggle:hover,
    .dev-password-toggle:focus-visible {
        background: #70131b;
        border-color: #70131b;
        color: #ffffff;
        outline: none;
    }

    .dev-password-toggle svg {
        width: 17px;
        height: 17px;
        stroke-width: 1.9;
    }

    .dev-password-select {
        appearance: none;
    }

    .dev-password-field input::placeholder {
        color: #94a3b8;
        font-weight: 700;
    }

    .dev-maintenance-datetime {
        display: grid;
        grid-template-columns: minmax(0, 1fr) minmax(120px, .55fr);
        gap: 10px;
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

    .dev-compact-settings {
        display: grid;
        gap: 10px;
        margin-top: 14px;
    }

    .dev-setting-row {
        display: grid;
        gap: 8px;
        padding: 12px;
        border-radius: 14px;
        border: 1px solid rgba(112,19,27,.14);
        background: rgba(255,255,255,.86);
    }

    .dev-setting-row-head {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 10px;
    }

    .dev-setting-actions {
        display: inline-flex;
        align-items: center;
        gap: 8px;
        flex-wrap: wrap;
        justify-content: flex-end;
    }

    .dev-setting-label {
        display: block;
        color: #70131b;
        font-size: .74rem;
        font-weight: 900;
        letter-spacing: .08em;
        text-transform: uppercase;
    }

    .dev-setting-value {
        min-width: 0;
        color: #111827;
        font-size: .88rem;
        font-weight: 900;
        overflow-wrap: anywhere;
    }

    .dev-setting-subtext {
        color: #64748b;
        font-size: .76rem;
        font-weight: 700;
        line-height: 1.4;
    }

    .dev-mini-action,
    .dev-icon-action {
        border: 1px solid rgba(112,19,27,.18);
        background: #fff;
        color: #70131b;
        cursor: pointer;
        font-weight: 900;
        transition: background .18s ease, color .18s ease, border-color .18s ease, transform .18s ease;
    }

    .dev-mini-action {
        min-height: 34px;
        padding: 7px 11px;
        border-radius: 10px;
        font-size: .72rem;
        text-transform: uppercase;
        letter-spacing: .05em;
    }

    .dev-icon-action {
        width: 36px;
        height: 36px;
        border-radius: 10px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        flex: 0 0 36px;
    }

    .dev-icon-action svg {
        width: 17px;
        height: 17px;
    }

    .dev-mini-action:hover,
    .dev-icon-action:hover {
        background: #facc15;
        border-color: #facc15;
        color: #70131b;
        transform: translateY(-1px);
    }

    .dev-collapsible-fields {
        display: grid;
        gap: 10px;
        padding-top: 4px;
    }

    .dev-collapsible-fields[hidden] {
        display: none;
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
        display: block;
        color: #334155;
        font-size: .82rem;
        font-weight: 900;
    }

    .dev-logo-hint {
        display: block;
        color: #64748b;
        font-size: .76rem;
        line-height: 1.4;
    }

    .dev-logo-select {
        color: inherit;
        font: inherit;
        cursor: pointer;
        transition: border-color .18s ease, background .18s ease, box-shadow .18s ease;
    }

    .dev-logo-select:hover,
    .dev-logo-select:focus-visible {
        border-color: #eab308;
        box-shadow: 0 0 0 2px rgba(250,204,21,.24);
        outline: none;
    }

    .dev-logo-file-input {
        position: absolute;
        width: 1px;
        height: 1px;
        padding: 0;
        margin: -1px;
        overflow: hidden;
        clip: rect(0, 0, 0, 0);
        white-space: nowrap;
        clip-path: inset(50%);
        border: 0;
    }

    .dev-logo-file-input:focus-visible + .dev-logo-preview .dev-logo-select {
        border-color: #eab308;
        box-shadow: 0 0 0 2px rgba(250,204,21,.24);
    }

    .dev-logo-feedback {
        margin-top: 12px;
        padding: 10px 12px;
        border: 1px solid #86efac;
        border-radius: 10px;
        background: #f0fdf4;
        color: #166534;
        font-size: .84rem;
        font-weight: 800;
    }

    .dev-logo-feedback.is-error {
        border-color: #fca5a5;
        background: #fef2f2;
        color: #991b1b;
    }

    .dev-logo-submit {
        width: 100%;
        min-height: 52px;
        margin-top: 14px;
        padding: 12px 16px;
        border: 1px solid rgba(112,19,27,.3);
        border-radius: 999px;
        background: #7f1725;
        color: #fff;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        gap: 10px;
        font: inherit;
        font-weight: 900;
        cursor: pointer;
        transition: color .18s ease, background .18s ease, border-color .18s ease, box-shadow .18s ease;
    }

    .dev-logo-submit svg {
        width: 19px;
        height: 19px;
    }

    .dev-logo-submit:hover:not(:disabled),
    .dev-logo-submit:focus-visible:not(:disabled) {
        background: #facc15;
        border-color: #facc15;
        color: #70131b;
        box-shadow: 0 10px 22px rgba(234,179,8,.22);
        outline: none;
    }

    .dev-logo-submit:disabled {
        opacity: .55;
        cursor: not-allowed;
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

    .dev-policy-form {
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

    .dev-maintenance-save {
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

    .dev-maintenance-save::after {
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

    .dev-maintenance-save:hover,
    .dev-maintenance-save:focus-visible {
        background: #facc15;
        border-color: #facc15;
        color: #70131B;
        outline: none;
        transform: translateY(-1px);
    }

    .dev-maintenance-save:hover::after,
    .dev-maintenance-save:focus-visible::after {
        left: 128%;
    }

    .dev-form-error {
        padding: 10px 12px;
        border-radius: 12px;
        background: #fee2e2;
        color: #991b1b;
        font-size: .78rem;
        font-weight: 850;
    }

    .dev-reset-modal {
        position: fixed;
        inset: 0;
        z-index: 500100;
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
        width: min(660px, 100%);
        overflow: hidden;
        border-radius: 18px;
        border: 1px solid rgba(255,255,255,.18);
        background: #ffffff;
        box-shadow: 0 24px 70px rgba(15, 23, 42, .26);
    }

    .dev-reset-head {
        display: flex;
        justify-content: space-between;
        align-items: flex-start;
        gap: 14px;
        padding: 20px 22px;
        background: linear-gradient(135deg, #9d1427 0%, #710012 100%);
        color: #ffffff;
    }

    .dev-reset-head-main {
        display: flex;
        align-items: flex-start;
        gap: 12px;
    }

    .dev-reset-icon {
        width: 44px;
        height: 44px;
        flex: 0 0 44px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        border-radius: 14px;
        background: rgba(255, 255, 255, .14);
        border: 1px solid rgba(255, 255, 255, .22);
        color: #ffffff;
    }

    .dev-reset-icon svg {
        width: 22px;
        height: 22px;
        stroke: currentColor;
    }

    .dev-reset-head h3 {
        margin: 0;
        color: #ffffff !important;
        font-size: 20px;
        font-weight: 950;
    }

    .dev-reset-head p {
        margin: 4px 0 0;
        color: rgba(255,255,255,.9) !important;
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
        gap: 18px;
        padding: 18px 20px 0;
    }

    .dev-reset-security {
        display: flex;
        align-items: center;
        gap: 14px;
        padding: 14px;
        border-radius: 10px;
        border: 1px solid #f3ccd3;
        background: linear-gradient(135deg, #fff8f9, #ffffff);
    }

    .dev-reset-security-icon,
    .dev-reset-step-number {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        flex: 0 0 auto;
    }

    .dev-reset-security-icon {
        width: 42px;
        height: 42px;
        border-radius: 999px;
        background: #ffe7eb;
        color: #8f1827;
    }

    .dev-reset-security-icon svg,
    .dev-reset-step-icon {
        width: 20px;
        height: 20px;
    }

    .dev-reset-security strong,
    .dev-reset-step-title {
        display: block;
        color: #111827;
        font-size: 15px;
        font-weight: 950;
    }

    .dev-reset-security span,
    .dev-reset-step-copy {
        color: #64748b;
        font-size: 13px;
        font-weight: 700;
    }

    .dev-reset-step {
        display: grid;
        grid-template-columns: 28px minmax(0, 1fr);
        gap: 12px;
    }

    .dev-reset-step-number {
        width: 24px;
        height: 24px;
        margin-top: 2px;
        border-radius: 999px;
        background: #8f1827;
        color: #ffffff;
        font-size: 13px;
        font-weight: 950;
    }

    .dev-reset-step-body {
        display: grid;
        gap: 8px;
    }

    .dev-reset-step[hidden] {
        display: none !important;
    }

    .dev-otp-controls {
        display: flex;
        align-items: center;
        flex-wrap: wrap;
        gap: 10px;
    }

    .dev-otp-controls input {
        width: min(220px, 100%);
        min-height: 44px;
        padding: 0 12px;
        border: 1px solid #cbd5e1;
        border-radius: 8px;
        background: #ffffff;
        color: #111827;
        font-size: 16px;
        font-weight: 800;
        letter-spacing: 2px;
    }

    .dev-otp-controls input:focus {
        border-color: #b91c1c;
        box-shadow: 0 0 0 3px rgba(185, 28, 28, .12);
        outline: none;
    }

    .dev-reset-password-wrap {
        position: relative;
    }

    .dev-reset-password-wrap input {
        width: 100%;
        min-height: 42px;
        padding: 0 46px 0 42px;
        border-radius: 8px;
        border: 1px solid #cbd5e1;
        background: #ffffff;
        color: #111827;
        font-weight: 800;
    }

    .dev-reset-input-icon,
    .dev-reset-eye {
        position: absolute;
        top: 50%;
        transform: translateY(-50%);
        color: #64748b;
    }

    .dev-reset-input-icon {
        left: 14px;
    }

    .dev-reset-eye {
        right: 10px;
        width: 32px;
        height: 32px;
        border: 0;
        background: transparent;
        cursor: pointer;
        display: inline-flex;
        align-items: center;
        justify-content: center;
    }

    .dev-inline-status {
        display: none;
        align-items: center;
        gap: 8px;
        min-height: 20px;
        margin-top: 8px;
        color: #64748b;
        font-size: 13px;
        font-weight: 850;
    }

    .dev-inline-status.is-visible {
        display: flex;
    }

    .dev-inline-status.is-valid {
        color: #15803d;
    }

    .dev-inline-status.is-invalid {
        color: #b91c1c;
    }

    .dev-reset-footer {
        display: flex;
        justify-content: flex-end;
        align-items: center;
        gap: 12px;
        margin: 2px -20px 0;
        padding: 14px 20px;
        border-top: 1px solid #e5e7eb;
        background: #ffffff;
    }

    .dev-reset-cancel,
    .dev-reset-submit {
        min-height: 44px;
        border-radius: 8px;
        padding: 0 26px;
        font-weight: 950;
        cursor: pointer;
    }

    .dev-reset-cancel {
        border: 1px solid #cbd5e1;
        background: #ffffff;
        color: #374151;
    }

    .dev-reset-submit {
        border: 0;
        background: #8f1827;
        color: #ffffff;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        gap: 8px;
    }

    .dev-reset-submit svg {
        width: 16px;
        height: 16px;
    }

    .dev-reset-submit:hover,
    .dev-reset-submit:focus-visible {
        background: #facc15;
        color: #70131B;
        outline: none;
    }

    .dev-reset-submit:disabled {
        opacity: .55;
        cursor: not-allowed;
    }

    @media (max-width: 640px) {
        .dev-reset-modal {
            padding: 12px;
        }

        .dev-reset-head {
            padding: 18px;
        }

        .dev-reset-footer {
            flex-direction: column-reverse;
            align-items: stretch;
        }

        .dev-reset-cancel,
        .dev-reset-submit {
            width: 100%;
        }
    }

    html[data-theme="dark"] .dev-hero {
        background: transparent !important;
        border-color: transparent !important;
        box-shadow: none !important;
    }

    html[data-theme="dark"] .dev-hero h1,
    html[data-theme="dark"] .dev-card h2 {
        color: #f8fafc;
    }

    html[data-theme="dark"] .dev-hero p,
    html[data-theme="dark"] .dev-card p {
        color: #cbd5e1;
    }

    html[data-theme="dark"] .dev-card {
        background: #050505 !important;
        border-color: rgba(250,204,21,.76) !important;
        box-shadow: 0 18px 34px rgba(0,0,0,.34), 0 0 0 1px rgba(250,204,21,.12);
    }

    html[data-theme="dark"] .dev-panel {
        background: #121a24;
        border-color: rgba(250,204,21,.18);
    }

    html[data-theme="dark"] .dev-panel-body {
        background: #121a24;
        color: #e5e7eb;
        scrollbar-color: rgba(250,204,21,.38) transparent;
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

    html[data-theme="dark"] .dev-password-toggle {
        background: rgba(15, 23, 42, .86);
        border-color: rgba(250,204,21,.22);
        color: #facc15;
    }

    html[data-theme="dark"] .dev-password-toggle:hover,
    html[data-theme="dark"] .dev-password-toggle:focus-visible {
        background: #facc15;
        border-color: #facc15;
        color: #70131b;
    }

    html[data-theme="dark"] .dev-password-field input::placeholder {
        color: #cbd5e1;
    }

    html[data-theme="dark"] .dev-password-note {
        color: #cbd5e1;
    }

    html[data-theme="dark"] .dev-live-toggle {
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

    html[data-theme="dark"] .dev-logo-submit {
        background: rgba(255,255,255,.08);
        border-color: rgba(250,204,21,.28);
        color: #f0d15a;
    }

    html[data-theme="dark"] .dev-logo-submit:hover:not(:disabled),
    html[data-theme="dark"] .dev-logo-submit:focus-visible:not(:disabled) {
        background: #facc15;
        border-color: #facc15;
        color: #70131b;
    }

    html[data-theme="dark"] .dev-logo-feedback {
        border-color: rgba(134,239,172,.45);
        background: rgba(20,83,45,.35);
        color: #bbf7d0;
    }

    html[data-theme="dark"] .dev-logo-feedback.is-error {
        border-color: rgba(252,165,165,.45);
        background: rgba(127,29,29,.35);
        color: #fecaca;
    }

    html[data-theme="dark"] .dev-option-block {
        background: #1b2633;
        border-color: rgba(250,204,21,.16);
        box-shadow: none;
        color: #e5e7eb;
    }

    html[data-theme="dark"] .dev-option-row,
    html[data-theme="dark"] .dev-mini-line,
    html[data-theme="dark"] .dev-status-item {
        background: #25313f;
        color: #e5e7eb;
    }

    html[data-theme="dark"] .dev-option-pill {
        background: rgba(250,204,21,.14);
        color: #facc15;
    }

    html[data-theme="dark"] .dev-setting-row,
    html[data-theme="dark"] .dev-password-box,
    html[data-theme="dark"] .dev-logo-box {
        background: #202b38;
        border-color: rgba(250,204,21,.16);
        color: #e5e7eb;
    }

    html[data-theme="dark"] .dev-setting-label,
    html[data-theme="dark"] .dev-password-field label {
        color: #facc15;
    }

    html[data-theme="dark"] .dev-setting-value,
    html[data-theme="dark"] .dev-logo-label {
        color: #f8fafc;
    }

    html[data-theme="dark"] .dev-setting-subtext,
    html[data-theme="dark"] .dev-password-note,
    html[data-theme="dark"] .dev-logo-hint {
        color: #cbd5e1;
    }

    html[data-theme="dark"] .dev-mini-action,
    html[data-theme="dark"] .dev-icon-action {
        background: #273342;
        border-color: rgba(250,204,21,.22);
        color: #facc15;
    }

    html[data-theme="dark"] .dev-password-select {
        background: #25313f;
        border-color: rgba(250,204,21,.2);
        color: #f8fafc;
    }

    html[data-theme="dark"] .dev-logo-placeholder {
        background: rgba(250,204,21,.1);
        border-color: rgba(250,204,21,.42);
        color: #facc15;
    }

    html[data-theme="dark"] .dev-logo-submit {
        background: #25313f;
        border-color: rgba(250,204,21,.24);
        color: #facc15;
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
        display: inline-flex;
        align-items: center;
        gap: 9px;
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
        display: block;
        width: 30px;
        height: 30px;
        flex: 0 0 auto;
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
        background: transparent;
        border-color: rgba(250, 204, 21, 0.20);
        box-shadow: 0 18px 34px rgba(0, 0, 0, 0.18);
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
        display: flex !important;
        flex-direction: column !important;
        align-items: flex-start !important;
        justify-content: flex-start !important;
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
            min-height: 314px !important;
        }
    }

    html[data-theme="dark"] .dev-card,
    html[data-theme="dark"] button.dev-card {
        background: transparent !important;
        background-image: none !important;
        border-color: transparent !important;
    }

    html[data-theme="dark"] .dev-card::before,
    html[data-theme="dark"] button.dev-card::before {
        background: transparent !important;
    }

    html[data-theme="dark"] .dev-card:hover,
    html[data-theme="dark"] .dev-card:focus-visible,
    html[data-theme="dark"] button.dev-card:hover,
    html[data-theme="dark"] button.dev-card:focus-visible {
        background: #facc15 !important;
        background-image: none !important;
        border-color: #facc15 !important;
        color: #70131B !important;
    }

    html[data-theme="dark"] .dev-card:hover h2,
    html[data-theme="dark"] .dev-card:focus-visible h2,
    html[data-theme="dark"] .dev-card:hover p,
    html[data-theme="dark"] .dev-card:focus-visible p,
    html[data-theme="dark"] .dev-card:hover .dev-action,
    html[data-theme="dark"] .dev-card:focus-visible .dev-action,
    html[data-theme="dark"] button.dev-card:hover h2,
    html[data-theme="dark"] button.dev-card:focus-visible h2,
    html[data-theme="dark"] button.dev-card:hover p,
    html[data-theme="dark"] button.dev-card:focus-visible p,
    html[data-theme="dark"] button.dev-card:hover .dev-action,
    html[data-theme="dark"] button.dev-card:focus-visible .dev-action {
        color: #70131B !important;
    }

    .dev-card::after,
    button.dev-card::after {
        content: "" !important;
        position: absolute !important;
        top: -38% !important;
        bottom: -38% !important;
        left: -135% !important;
        width: 34% !important;
        height: auto !important;
        opacity: 0;
        pointer-events: none;
        z-index: 0;
        background: linear-gradient(105deg, rgba(255, 255, 255, 0) 0%, rgba(255, 246, 184, 0.18) 42%, rgba(255, 246, 184, 0.6) 50%, rgba(255, 246, 184, 0.18) 58%, rgba(255, 255, 255, 0) 100%) !important;
        transform: translateX(0) skewX(-18deg);
        transition: none !important;
    }

    .dev-card:hover::after,
    .dev-card:focus-visible::after,
    button.dev-card:hover::after,
    button.dev-card:focus-visible::after {
        opacity: 1 !important;
        animation: devSettingsSweep .92s ease both !important;
    }

    html[data-theme="dark"] .dev-card::after,
    html[data-theme="dark"] button.dev-card::after {
        background: linear-gradient(105deg, rgba(255, 255, 255, 0) 0%, rgba(255, 255, 255, 0.12) 42%, rgba(255, 255, 255, 0.44) 50%, rgba(255, 255, 255, 0.12) 58%, rgba(255, 255, 255, 0) 100%) !important;
    }

    /* Final light-mode surface pass */
    html:not([data-theme="dark"]) .dev-shell {
        border: 1px solid rgba(250, 204, 21, 0.20) !important;
        box-shadow: 0 18px 34px rgba(112, 19, 27, 0.08) !important;
    }

    html:not([data-theme="dark"]) .dev-card,
    html:not([data-theme="dark"]) button.dev-card {
        border: 1px solid rgba(250, 204, 21, 0.22) !important;
        box-shadow: 0 14px 28px rgba(112, 19, 27, 0.12), 0 4px 12px rgba(15, 23, 42, 0.06) !important;
    }
</style>
@endpush

@section('content')
@php
    $apiTestingRoute = request()->routeIs('assistant.*') ? route('assistant.api-testing') : route('admin.api-testing');
@endphp

<div class="dev-shell">
    <section class="dev-hero">
        <h1>
            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" aria-hidden="true">
                <path stroke-linecap="round" stroke-linejoin="round" d="M14.25 9.75 16.5 12l-2.25 2.25m-4.5 0L7.5 12l2.25-2.25M6 20.25h12A2.25 2.25 0 0 0 20.25 18V6A2.25 2.25 0 0 0 18 3.75H6A2.25 2.25 0 0 0 3.75 6v12A2.25 2.25 0 0 0 6 20.25Z" />
            </svg>
            Developer Tools
        </h1>
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
            <div class="dev-action">Click to view</div>
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
            <p>Prepared maintenance controls for future developer use.</p>
            <div class="dev-note">Static controls only.</div>
            <div class="dev-action">Click to view</div>
        </button>
    </div>
</div>

<div class="dev-modal" id="developerOptionsPanel" aria-hidden="true">
    <section class="dev-panel" role="dialog" aria-modal="true" aria-labelledby="developerOptionsTitle">
        <div class="dev-panel-head">
            <div>
                <h2 id="developerOptionsTitle">
                    <span class="dev-panel-title-icon" aria-hidden="true">
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M14.25 9.75 16.5 12l-2.25 2.25m-4.5 0L7.5 12l2.25-2.25M6 20.25h12A2.25 2.25 0 0 0 20.25 18V6A2.25 2.25 0 0 0 18 3.75H6A2.25 2.25 0 0 0 3.75 6v12A2.25 2.25 0 0 0 6 20.25Z" />
                        </svg>
                    </span>
                    <span>Developer Options</span>
                </h2>
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
                    <p class="dev-option-copy">Manage emergency fallback access when One Portal is unavailable.</p>
                    @php
                        $configEmergencyEmail = (string) config('services.emergency.email', '');
                        $configEmergencyHash = trim((string) config('services.emergency.password_hash', ''));
                        $configEmergencyPassword = (string) config('services.emergency.password', '');
                        $configEmergencyRole = (string) config('services.emergency.role', 'admin');
                        $emergencyEnabled = (bool) config('services.emergency.enabled', false);
                        $emergencyEmail = $configEmergencyEmail;
                        $emergencyRole = $configEmergencyRole;
                        $emergencyConfigured = $configEmergencyHash !== '' || $configEmergencyPassword !== '';
                        $emergencySource = 'Environment';
                    @endphp
                    <div class="dev-mini-summary">
                        <div class="dev-mini-line"><span>One Portal / IdP</span><span class="dev-option-pill">Primary</span></div>
                        <div class="dev-mini-line"><span>Emergency Login</span><span class="dev-option-pill">{{ $emergencyEnabled ? 'Enabled' : 'Disabled' }}</span></div>
                        <div class="dev-mini-line"><span>Password</span><span class="dev-option-pill">{{ $emergencyConfigured ? 'Configured' : 'Missing' }}</span></div>
                        <div class="dev-mini-line"><span>Source</span><span class="dev-option-pill">{{ $emergencySource }}</span></div>
                    </div>
                    <form method="POST" action="{{ route('admin.emergency-credentials.update') }}" class="dev-compact-settings" id="devEmergencyCredentialsForm">
                        @csrf
                        @method('PUT')
                        <input type="hidden" name="emergency_action" id="devEmergencyAction" value="reset">

                        <div class="dev-setting-row">
                            <div class="dev-setting-row-head">
                                <div>
                                    <span class="dev-setting-label">Emergency Email</span>
                                    <div class="dev-setting-value">{{ $emergencyEmail !== '' ? $emergencyEmail : 'No emergency email set' }}</div>
                                </div>
                            </div>
                        </div>

                        <input type="hidden" name="emergency_role" value="{{ in_array($emergencyRole, ['superadmin', 'super_admin'], true) ? 'superadmin' : 'admin' }}">
                        <div class="dev-setting-row">
                            <div class="dev-setting-row-head">
                                <div>
                                    <span class="dev-setting-label">Reset Emergency Password</span>
                                    <div class="dev-setting-subtext">Create a new fallback password when the current one is unknown or rotated.</div>
                                </div>
                                <button type="button" class="dev-mini-action" id="openEmergencyPasswordReset">Reset</button>
                            </div>
                        </div>

                        @error('new_emergency_password')
                            <div class="dev-form-error">{{ $message }}</div>
                        @enderror
                    </form>
                </section>

                <section class="dev-option-block">
                    <p class="dev-option-kicker">Branding</p>
                    <h3 class="dev-option-title">Clinic Logo</h3>
                    <p class="dev-option-copy">Upload the logo shown across clinic pages and headers.</p>
                    <form method="POST" action="{{ route('admin.developer-tools.clinic-logo.update') }}" enctype="multipart/form-data" id="clinicLogoUploadForm">
                        @csrf
                        <input type="file" name="clinic_logo" id="clinicLogoInput" class="dev-logo-file-input" accept="image/png,image/jpeg,image/webp" aria-label="Choose a clinic logo">
                        <div class="dev-logo-preview">
                            <div class="dev-logo-box">
                                <img src="{{ \App\Support\ClinicBranding::logoUrl() }}" alt="Current clinic logo" data-clinic-logo>
                                <div>
                                    <div class="dev-logo-label">Current Logo</div>
                                    <div class="dev-logo-hint">Used in headers and navigation.</div>
                                </div>
                            </div>
                            <label class="dev-logo-box dev-logo-select" for="clinicLogoInput" tabindex="0" id="clinicLogoPicker">
                                <img src="" alt="Selected logo preview" id="clinicLogoSelectedPreview" hidden>
                                <span class="dev-logo-placeholder" aria-hidden="true" id="clinicLogoPlaceholder">
                                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">
                                        <path d="M12 5v14"/>
                                        <path d="M5 12h14"/>
                                    </svg>
                                </span>
                                <span>
                                    <span class="dev-logo-label" id="clinicLogoFileName">Choose New Logo</span>
                                    <span class="dev-logo-hint">PNG, JPG, or WebP. Max 4 MB.</span>
                                </span>
                            </label>
                        </div>
                        @error('clinic_logo')
                            <div class="dev-logo-feedback is-error" role="alert">{{ $message }}</div>
                        @enderror
                        <button type="submit" class="dev-logo-submit" id="clinicLogoSubmit" disabled>
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                                <path d="M19 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11l5 5v11a2 2 0 0 1-2 2Z"/>
                                <path d="M17 21v-8H7v8M7 3v5h8"/>
                            </svg>
                            <span>Update Clinic Logo</span>
                        </button>
                    </form>
                </section>

                <section class="dev-option-block">
                    <p class="dev-option-kicker">Policies</p>
                    <h3 class="dev-option-title">Student Side Notices</h3>
                    <p class="dev-option-copy">Control student-facing access during scheduled maintenance.</p>
                    @php
                        $maintenanceSettingsAvailable = \Illuminate\Support\Facades\Schema::hasTable('system_settings');
                        $maintenanceEnabled = $maintenanceSettingsAvailable
                            ? \App\Models\SystemSetting::booleanValue('maintenance_mode_enabled', false)
                            : false;
                        $maintenanceEstimate = $maintenanceSettingsAvailable
                            ? \App\Models\SystemSetting::getValue('maintenance_estimated_completion', null)
                            : null;
                        try {
                            $maintenanceEstimateDate = $maintenanceEstimate ? \Carbon\Carbon::parse($maintenanceEstimate)->format('Y-m-d') : '';
                            $maintenanceEstimateTime = $maintenanceEstimate ? \Carbon\Carbon::parse($maintenanceEstimate)->format('H:i') : '';
                        } catch (\Throwable $exception) {
                            $maintenanceEstimateDate = '';
                            $maintenanceEstimateTime = '';
                        }
                    @endphp
                    <form method="POST" action="{{ route('admin.maintenance-policy.update') }}" class="dev-policy-form">
                        @csrf
                        @method('PUT')
                        <label class="dev-live-toggle">
                            <span>
                                <strong>Maintenance Mode</strong>
                                <span>Redirect student home access to the maintenance page.</span>
                            </span>
                            <input type="checkbox" name="maintenance_mode_enabled" value="1" id="devMaintenanceModeToggle" {{ $maintenanceEnabled ? 'checked' : '' }}>
                            <span class="dev-toggle-track" aria-hidden="true"><span class="dev-toggle-knob"></span></span>
                        </label>
                        <div class="dev-password-field">
                            <label>Estimated Completion</label>
                            <div class="dev-maintenance-datetime">
                                <input type="date" id="devMaintenanceEstimatedDate" name="maintenance_estimated_date" value="{{ old('maintenance_estimated_date', $maintenanceEstimateDate) }}" aria-label="Estimated completion date">
                                <input type="time" id="devMaintenanceEstimatedTime" name="maintenance_estimated_time" value="{{ old('maintenance_estimated_time', $maintenanceEstimateTime) }}" step="60" aria-label="Estimated completion time">
                            </div>
                        </div>
                        @error('maintenance_estimated_date')
                            <div class="dev-form-error">{{ $message }}</div>
                        @enderror
                        @error('maintenance_estimated_time')
                            <div class="dev-form-error">{{ $message }}</div>
                        @enderror
                        <div class="dev-password-note">
                            When enabled, students opening the clinic workspace are sent to the maintenance page.
                        </div>
                        <button type="submit" class="dev-maintenance-save" id="devMaintenanceSaveButton" {{ $maintenanceEnabled ? '' : 'hidden' }}>Save Student Notice</button>
                    </form>
                </section>
            </div>
        </div>
    </section>
</div>

<div class="dev-reset-modal" id="resetEmergencyPasswordModal" aria-hidden="true">
    <section class="dev-reset-dialog" role="dialog" aria-modal="true" aria-labelledby="resetEmergencyPasswordTitle">
        <header class="dev-reset-head">
            <div class="dev-reset-head-main">
                <span class="dev-reset-icon" aria-hidden="true">
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 5.25a3 3 0 0 1 3 3m3 0a6 6 0 0 1-7.029 5.912c-.563-.097-1.159.026-1.563.43L10.5 17.25H8.25v2.25H6v2.25H2.25v-2.818c0-.597.237-1.17.659-1.591l6.499-6.5c.404-.403.527-1 .43-1.563A6 6 0 1 1 21.75 8.25Z" />
                    </svg>
                </span>
                <div>
                    <h3 id="resetEmergencyPasswordTitle">Reset Emergency Password</h3>
                    <p>Verify the emergency recovery email with a one-time code before changing the fallback password.</p>
                </div>
            </div>
            <button type="button" class="dev-reset-close" id="closeEmergencyPasswordReset" aria-label="Close emergency password reset modal">&times;</button>
        </header>
        @php
            $resetEmergencyEmail = (string) config('services.emergency.email', '');
            $resetEmergencyRole = (string) config('services.emergency.role', 'admin');
            $resetOtpVerification = session()->get('emergency_password_reset_otp_verified', []);
            $resetOtpUser = \Illuminate\Support\Facades\Auth::guard('admin')->user() ?? auth()->user();
            $resetOtpIsVerified = is_array($resetOtpVerification)
                && (int) ($resetOtpVerification['user_id'] ?? 0) === (int) ($resetOtpUser?->getAuthIdentifier() ?? 0)
                && (int) ($resetOtpVerification['expires_at'] ?? 0) >= now()->timestamp;
        @endphp
        <form method="POST" action="{{ route('admin.emergency-credentials.update') }}" class="dev-reset-form" id="emergencyPasswordResetForm" autocomplete="off" data-otp-verified="{{ $resetOtpIsVerified ? '1' : '0' }}">
            @csrf
            @method('PUT')
            <input type="hidden" name="emergency_action" value="reset">
            <input type="hidden" name="emergency_email" value="{{ $resetEmergencyEmail }}">
            <input type="hidden" name="emergency_role" value="{{ in_array($resetEmergencyRole, ['superadmin', 'super_admin'], true) ? 'superadmin' : 'admin' }}">

            <div class="dev-reset-security">
                <span class="dev-reset-security-icon" aria-hidden="true">
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75 11.25 15 15 9.75m-3-7.036A11.959 11.959 0 0 1 3.598 6 11.99 11.99 0 0 0 3 9.75c0 5.592 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.31-.21-2.571-.598-3.75A11.959 11.959 0 0 1 12 2.714Z" />
                    </svg>
                </span>
                <div>
                    <strong>Security Verification</strong>
                    <span>Only use this when rotating or recovering the emergency login password.</span>
                </div>
            </div>

            <div class="dev-reset-step" id="emergencyPasswordOtpStep" {{ $resetOtpIsVerified ? 'hidden' : '' }}>
                <span class="dev-reset-step-number">1</span>
                <div class="dev-reset-step-body">
                    <div>
                        <strong class="dev-reset-step-title">Verify Emergency Email</strong>
                        <span class="dev-reset-step-copy">Send a one-time code to the configured emergency recovery email.</span>
                    </div>
                    <div class="dev-otp-controls">
                        <button type="button" class="dev-reset-submit" id="sendEmergencyPasswordOtp">Send verification code</button>
                    </div>
                    <div class="dev-inline-status" id="emergencyPasswordOtpStatus" aria-live="polite"></div>
                </div>
            </div>

            <div class="dev-reset-step" id="emergencyPasswordOtpEntry" hidden>
                <span class="dev-reset-step-number">2</span>
                <div class="dev-reset-step-body">
                    <div>
                        <strong class="dev-reset-step-title">Enter Verification Code</strong>
                        <span class="dev-reset-step-copy">Enter the 6-digit code sent to <span id="emergencyPasswordOtpRecipient">the configured recovery email</span>.</span>
                    </div>
                    <div class="dev-otp-controls">
                        <input type="text" id="emergencyPasswordOtp" inputmode="numeric" autocomplete="one-time-code" maxlength="6" pattern="[0-9]{6}" aria-label="6-digit verification code">
                        <button type="button" class="dev-reset-submit" id="verifyEmergencyPasswordOtp">Verify code</button>
                    </div>
                    <div class="dev-inline-status" id="emergencyPasswordOtpVerifyStatus" aria-live="polite"></div>
                </div>
            </div>

            <div class="dev-reset-step" id="emergencyPasswordFields" hidden>
                <span class="dev-reset-step-number">3</span>
                <div class="dev-reset-step-body">
                    <div>
                        <strong class="dev-reset-step-title">Create New Emergency Password</strong>
                        <span class="dev-reset-step-copy">Use at least 8 characters with at least one letter and one number.</span>
                    </div>
                    <div class="dev-reset-password-wrap">
                        <span class="dev-reset-input-icon" aria-hidden="true">
                            <svg class="dev-reset-step-icon" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M16.5 10.5V6.75a4.5 4.5 0 0 0-9 0v3.75m-.75 0h10.5c.621 0 1.125.504 1.125 1.125v7.5c0 .621-.504 1.125-1.125 1.125H6.75a1.125 1.125 0 0 1-1.125-1.125v-7.5c0-.621.504-1.125 1.125-1.125Z" /></svg>
                        </span>
                        <input type="password" id="modalNewEmergencyPassword" name="new_emergency_password" required placeholder="Create emergency password" autocomplete="new-password">
                        <button type="button" class="dev-reset-eye" data-toggle-password="modalNewEmergencyPassword" aria-label="Show new emergency password">
                            <svg class="dev-reset-step-icon" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M2.036 12.322a1.012 1.012 0 0 1 0-.639C3.423 7.51 7.36 4.5 12 4.5c4.638 0 8.573 3.007 9.963 7.178.07.207.07.431 0 .639C20.577 16.49 16.64 19.5 12 19.5c-4.638 0-8.573-3.007-9.963-7.178Z" /><path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 1 1-6 0 3 3 0 0 1 6 0Z" /></svg>
                        </button>
                    </div>
                    <div class="dev-reset-password-wrap">
                        <span class="dev-reset-input-icon" aria-hidden="true">
                            <svg class="dev-reset-step-icon" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75 11.25 15 15 9.75" /><path stroke-linecap="round" stroke-linejoin="round" d="M16.5 10.5V6.75a4.5 4.5 0 0 0-9 0v3.75m-.75 0h10.5c.621 0 1.125.504 1.125 1.125v7.5c0 .621-.504 1.125-1.125 1.125H6.75a1.125 1.125 0 0 1-1.125-1.125v-7.5c0-.621.504-1.125 1.125-1.125Z" /></svg>
                        </span>
                        <input type="password" id="modalNewEmergencyPasswordConfirm" name="new_emergency_password_confirmation" required placeholder="Confirm emergency password" autocomplete="new-password">
                        <button type="button" class="dev-reset-eye" data-toggle-password="modalNewEmergencyPasswordConfirm" aria-label="Show confirmed emergency password">
                            <svg class="dev-reset-step-icon" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M2.036 12.322a1.012 1.012 0 0 1 0-.639C3.423 7.51 7.36 4.5 12 4.5c4.638 0 8.573 3.007 9.963 7.178.07.207.07.431 0 .639C20.577 16.49 16.64 19.5 12 19.5c-4.638 0-8.573-3.007-9.963-7.178Z" /><path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 1 1-6 0 3 3 0 0 1 6 0Z" /></svg>
                        </button>
                    </div>
                    @error('new_emergency_password')
                        <div class="dev-form-error">{{ $message }}</div>
                    @enderror
                </div>
            </div>
            <div class="dev-reset-footer">
                <button type="button" class="dev-reset-cancel" id="cancelEmergencyPasswordReset">Cancel</button>
                <button type="submit" class="dev-reset-submit" id="saveEmergencyPasswordReset" {{ $resetOtpIsVerified ? '' : 'disabled' }}>Save Password</button>
            </div>
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

        @if (session('open_developer_options') || $errors->has('clinic_logo'))
            openPanel();
        @endif

        const clinicLogoInput = document.getElementById('clinicLogoInput');
        const clinicLogoPicker = document.getElementById('clinicLogoPicker');
        const clinicLogoSelectedPreview = document.getElementById('clinicLogoSelectedPreview');
        const clinicLogoPlaceholder = document.getElementById('clinicLogoPlaceholder');
        const clinicLogoFileName = document.getElementById('clinicLogoFileName');
        const clinicLogoSubmit = document.getElementById('clinicLogoSubmit');
        let selectedClinicLogoUrl = null;

        clinicLogoPicker?.addEventListener('keydown', event => {
            if (event.key === 'Enter' || event.key === ' ') {
                event.preventDefault();
                clinicLogoInput?.click();
            }
        });

        clinicLogoInput?.addEventListener('change', () => {
            const file = clinicLogoInput.files?.[0];
            if (!file) return;

            if (selectedClinicLogoUrl) URL.revokeObjectURL(selectedClinicLogoUrl);
            selectedClinicLogoUrl = URL.createObjectURL(file);
            if (clinicLogoSelectedPreview) {
                clinicLogoSelectedPreview.src = selectedClinicLogoUrl;
                clinicLogoSelectedPreview.hidden = false;
            }
            if (clinicLogoPlaceholder) clinicLogoPlaceholder.hidden = true;
            if (clinicLogoFileName) clinicLogoFileName.textContent = file.name;
            if (clinicLogoSubmit) clinicLogoSubmit.disabled = false;
        });

        const emergencyResetButton = document.getElementById('openEmergencyPasswordReset');
        const emergencyPasswordResetModal = document.getElementById('resetEmergencyPasswordModal');
        const closeEmergencyPasswordResetButton = document.getElementById('closeEmergencyPasswordReset');
        const cancelEmergencyPasswordResetButton = document.getElementById('cancelEmergencyPasswordReset');
        const emergencyPasswordResetForm = document.getElementById('emergencyPasswordResetForm');
        const emergencyPasswordOtpStep = document.getElementById('emergencyPasswordOtpStep');
        const sendEmergencyPasswordOtpButton = document.getElementById('sendEmergencyPasswordOtp');
        const verifyEmergencyPasswordOtpButton = document.getElementById('verifyEmergencyPasswordOtp');
        const emergencyPasswordOtp = document.getElementById('emergencyPasswordOtp');
        const emergencyPasswordOtpEntry = document.getElementById('emergencyPasswordOtpEntry');
        const emergencyPasswordOtpRecipient = document.getElementById('emergencyPasswordOtpRecipient');
        const emergencyPasswordOtpStatus = document.getElementById('emergencyPasswordOtpStatus');
        const emergencyPasswordOtpVerifyStatus = document.getElementById('emergencyPasswordOtpVerifyStatus');
        const emergencyPasswordFields = document.getElementById('emergencyPasswordFields');
        const saveEmergencyPasswordResetButton = document.getElementById('saveEmergencyPasswordReset');
        const maintenanceToggle = document.getElementById('devMaintenanceModeToggle');
        const maintenanceSaveButton = document.getElementById('devMaintenanceSaveButton');
        const maintenanceForm = maintenanceToggle?.closest('form');

        maintenanceToggle?.addEventListener('change', () => {
            if (maintenanceSaveButton) {
                maintenanceSaveButton.hidden = !maintenanceToggle.checked;
            }
            if (!maintenanceToggle.checked) {
                maintenanceForm?.requestSubmit();
            }
        });

        const setEmergencyPasswordResetModalOpen = (isOpen) => {
            if (!emergencyPasswordResetModal) {
                return;
            }
            emergencyPasswordResetModal.classList.toggle('is-open', isOpen);
            emergencyPasswordResetModal.setAttribute('aria-hidden', isOpen ? 'false' : 'true');
        };

        emergencyResetButton?.addEventListener('click', () => {
            window.setTimeout(() => setEmergencyPasswordResetModalOpen(true), 80);
        });
        closeEmergencyPasswordResetButton?.addEventListener('click', () => setEmergencyPasswordResetModalOpen(false));
        cancelEmergencyPasswordResetButton?.addEventListener('click', () => setEmergencyPasswordResetModalOpen(false));
        emergencyPasswordResetModal?.addEventListener('click', function (event) {
            if (event.target === emergencyPasswordResetModal) {
                setEmergencyPasswordResetModalOpen(false);
            }
        });

        const setOtpStatus = (element, message, isError = false) => {
            if (!element) return;
            element.textContent = message;
            element.classList.toggle('is-visible', Boolean(message));
            element.classList.toggle('is-invalid', isError);
            element.classList.toggle('is-valid', Boolean(message) && !isError);
        };

        const csrfToken = emergencyPasswordResetForm?.querySelector('input[name="_token"]')?.value || '';
        const restoreVerifiedOtpState = () => {
            const verified = emergencyPasswordResetForm?.dataset.otpVerified === '1';
            if (emergencyPasswordFields) emergencyPasswordFields.hidden = !verified;
            if (saveEmergencyPasswordResetButton) saveEmergencyPasswordResetButton.disabled = !verified;
            if (emergencyPasswordOtpEntry) emergencyPasswordOtpEntry.hidden = verified;
            if (emergencyPasswordOtpStep) emergencyPasswordOtpStep.hidden = verified;
            if (verified) setOtpStatus(emergencyPasswordOtpVerifyStatus, 'Email verification complete. Set the new password.', false);
        };
        restoreVerifiedOtpState();

        sendEmergencyPasswordOtpButton?.addEventListener('click', async () => {
            sendEmergencyPasswordOtpButton.disabled = true;
            setOtpStatus(emergencyPasswordOtpStatus, 'Sending verification code...');
            try {
                const response = await fetch('{{ route('admin.emergency-credentials.otp.send') }}', {
                    method: 'POST',
                    headers: { 'Accept': 'application/json', 'X-CSRF-TOKEN': csrfToken },
                });
                const payload = await response.json().catch(() => ({}));
                if (!response.ok || !payload.success) throw new Error(payload.message || 'Unable to send the verification code.');
                if (emergencyPasswordOtpRecipient) emergencyPasswordOtpRecipient.textContent = payload.recipient || 'the configured recovery email';
                if (emergencyPasswordOtpEntry) emergencyPasswordOtpEntry.hidden = false;
                if (emergencyPasswordOtpStep) emergencyPasswordOtpStep.hidden = false;
                if (emergencyPasswordFields) emergencyPasswordFields.hidden = true;
                if (saveEmergencyPasswordResetButton) saveEmergencyPasswordResetButton.disabled = true;
                if (emergencyPasswordResetForm) emergencyPasswordResetForm.dataset.otpVerified = '0';
                if (emergencyPasswordOtp) emergencyPasswordOtp.value = '';
                setOtpStatus(emergencyPasswordOtpStatus, payload.message || 'Verification code sent.');
                setOtpStatus(emergencyPasswordOtpVerifyStatus, '');
                emergencyPasswordOtp?.focus();
            } catch (error) {
                setOtpStatus(emergencyPasswordOtpStatus, error.message || 'Unable to send the verification code.', true);
            } finally {
                sendEmergencyPasswordOtpButton.disabled = false;
            }
        });

        emergencyPasswordOtp?.addEventListener('input', () => {
            emergencyPasswordOtp.value = emergencyPasswordOtp.value.replace(/\D/g, '').slice(0, 6);
        });

        verifyEmergencyPasswordOtpButton?.addEventListener('click', async () => {
            const code = (emergencyPasswordOtp?.value || '').trim();
            if (!/^\d{6}$/.test(code)) {
                setOtpStatus(emergencyPasswordOtpVerifyStatus, 'Enter the 6-digit verification code.', true);
                return;
            }
            verifyEmergencyPasswordOtpButton.disabled = true;
            setOtpStatus(emergencyPasswordOtpVerifyStatus, 'Verifying code...');
            try {
                const response = await fetch('{{ route('admin.emergency-credentials.otp.verify') }}', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json', 'Accept': 'application/json', 'X-CSRF-TOKEN': csrfToken },
                    body: JSON.stringify({ code }),
                });
                const payload = await response.json().catch(() => ({}));
                if (!response.ok || !payload.success) throw new Error(payload.message || 'The code is invalid or expired.');
                if (emergencyPasswordResetForm) emergencyPasswordResetForm.dataset.otpVerified = '1';
                if (emergencyPasswordOtpEntry) emergencyPasswordOtpEntry.hidden = true;
                if (emergencyPasswordOtpStep) emergencyPasswordOtpStep.hidden = true;
                if (emergencyPasswordFields) emergencyPasswordFields.hidden = false;
                if (saveEmergencyPasswordResetButton) saveEmergencyPasswordResetButton.disabled = false;
                setOtpStatus(emergencyPasswordOtpVerifyStatus, 'Email verification complete. Set the new password.');
                document.getElementById('modalNewEmergencyPassword')?.focus();
            } catch (error) {
                setOtpStatus(emergencyPasswordOtpVerifyStatus, error.message || 'The code is invalid or expired.', true);
            } finally {
                verifyEmergencyPasswordOtpButton.disabled = false;
            }
        });

        document.querySelectorAll('[data-toggle-password]').forEach((button) => {
            button.addEventListener('click', () => {
                const input = document.getElementById(button.dataset.togglePassword || '');
                if (!input) {
                    return;
                }

                const isHidden = input.type === 'password';
                input.type = isHidden ? 'text' : 'password';
                const originalLabel = button.dataset.defaultLabel || button.getAttribute('aria-label') || 'Show password';
                button.dataset.defaultLabel = originalLabel;
                button.setAttribute('aria-label', isHidden ? originalLabel.replace(/^Show/i, 'Hide') : originalLabel);
            });
        });

        document.addEventListener('keydown', function (event) {
            if (event.key === 'Escape' && panel.classList.contains('is-open')) {
                closePanel();
            }
            if (event.key === 'Escape' && emergencyPasswordResetModal?.classList.contains('is-open')) {
                setEmergencyPasswordResetModalOpen(false);
            }
        });
    });
</script>
@endpush
