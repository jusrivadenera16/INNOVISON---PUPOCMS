@extends('layouts.admin')

@section('title', 'System Preferences')

@push('styles')
@include('admin.partials.settings-section-style')
<style>
    .settings-schedule-note {
        grid-column: 1 / -1;
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 16px;
        min-height: 54px;
        padding: 12px 14px;
        border: 1px solid rgba(250, 204, 21, .34);
        border-radius: 10px;
        background: rgba(127, 0, 0, .035);
        color: #334155;
    }
    .settings-schedule-note strong { color: #7f0000; }
    .settings-schedule-note a {
        flex: 0 0 auto;
        color: #970014;
        font-size: 12px;
        font-weight: 850;
        text-decoration: none;
    }
    html[data-theme="dark"] .settings-schedule-note {
        background: rgba(15, 23, 42, .7);
        color: #e2e8f0;
    }
    html[data-theme="dark"] .settings-schedule-note strong,
    html[data-theme="dark"] .settings-schedule-note a { color: #facc15; }
    @media (max-width: 640px) {
        .settings-schedule-note { align-items: flex-start; flex-direction: column; }
    }

    /* System preferences parity with the Personal Information surface. */
    .preferences-settings-page {
        --preferences-maroon: #7f0010;
        --preferences-text: #172033;
        --preferences-muted: #64748b;
        --preferences-line: rgba(148, 163, 184, .22);
        display: grid;
        gap: 12px;
    }
    .preferences-settings-page .settings-section-hero {
        align-items: center;
        margin-bottom: 0;
        padding: 14px 16px;
        border-radius: 12px;
        border-color: var(--preferences-line);
        box-shadow: 0 8px 22px rgba(15, 23, 42, .05);
    }
    .preferences-settings-page .settings-section-hero > div:first-child {
        display: grid;
        grid-template-columns: 54px minmax(0, 1fr);
        column-gap: 14px;
        align-items: center;
        min-width: 0;
    }
    .preferences-settings-page .settings-section-title { display: contents; }
    .preferences-settings-page .preferences-title-icon {
        grid-column: 1;
        grid-row: 1 / span 2;
        display: grid;
        place-items: center;
        width: 54px;
        height: 54px;
        border-radius: 14px;
        background: #fff1f2;
        color: #b91c1c;
    }
    .preferences-settings-page .preferences-title-icon svg {
        width: 28px;
        height: 28px;
        padding: 0;
        background: transparent;
        color: currentColor;
    }
    .preferences-settings-page .settings-section-title > span:not(.preferences-title-icon) {
        grid-column: 2;
        grid-row: 1;
        color: var(--preferences-text);
        font-size: 26px;
        font-weight: 900;
        line-height: 1.1;
    }
    .preferences-settings-page .settings-section-hero p {
        grid-column: 2;
        grid-row: 2;
        margin: 6px 0 0;
        color: var(--preferences-muted);
        font-size: 13px;
        font-weight: 700;
        line-height: 1.45;
    }
    .preferences-settings-page .settings-back-link {
        min-height: 34px;
        border-radius: 8px;
        padding: 0 12px;
    }
    .preferences-settings-page .settings-back-link svg { transform: rotate(180deg); }
    .preferences-settings-page .settings-panel {
        overflow: hidden;
        border-radius: 12px;
        border: 1px solid var(--preferences-line);
        background: rgba(255, 255, 255, .96);
        box-shadow: 0 8px 22px rgba(15, 23, 42, .045);
    }
    .preferences-settings-page .settings-panel-head {
        align-items: center;
        min-height: 54px;
        padding: 11px 12px;
        border-bottom: 1px solid var(--preferences-line);
        background: transparent;
    }
    .preferences-settings-page .settings-panel-head > div {
        display: flex;
        align-items: center;
        gap: 9px;
    }
    .preferences-settings-page .settings-panel-head h3 {
        display: flex;
        align-items: center;
        gap: 8px;
        margin: 0;
        color: var(--preferences-maroon);
        font-size: 14px;
        font-weight: 900;
    }
    .preferences-settings-page .settings-panel-head h3 svg {
        width: 24px;
        height: 24px;
        flex: 0 0 auto;
        padding: 5px;
        border-radius: 7px;
        background: #fff1f2;
        color: var(--preferences-maroon);
    }
    .preferences-settings-page .settings-panel-head p { display: none; }
    .preferences-settings-page .settings-edit-btn {
        width: 34px;
        height: 34px;
        min-height: 34px;
        padding: 0;
        border-radius: 7px;
        background: #fff !important;
        color: var(--preferences-maroon) !important;
        border: 1px solid rgba(127, 0, 16, .25);
    }
    .preferences-settings-page .settings-edit-btn span { display: none; }
    .preferences-settings-page .settings-edit-btn svg { width: 16px; height: 16px; }
    .preferences-settings-page .settings-panel-body { padding: 0; }
    .preferences-settings-page .settings-form-grid {
        display: grid;
        grid-template-columns: repeat(2, minmax(0, 1fr));
        gap: 0;
        padding: 0 12px;
    }
    .preferences-settings-page .settings-field,
    .preferences-settings-page .settings-schedule-note {
        position: relative;
        display: grid;
        align-content: center;
        gap: 4px;
        min-width: 0;
        min-height: 60px;
        padding: 7px 12px;
    }
    .preferences-settings-page .settings-field.full,
    .preferences-settings-page .settings-schedule-note { grid-column: 1 / -1; }
    .preferences-settings-page .settings-field:nth-child(1)::after,
    .preferences-settings-page .settings-field:nth-child(4)::after,
    .preferences-settings-page .settings-field:nth-child(6)::after {
        content: "";
        position: absolute;
        top: 14px;
        right: 0;
        bottom: 14px;
        width: 1px;
        background: var(--preferences-line);
    }
    .preferences-settings-page .settings-schedule-note,
    .preferences-settings-page .settings-field.full + .settings-field.full::before,
    .preferences-settings-page .settings-field:nth-child(4)::before,
    .preferences-settings-page .settings-field:nth-child(6)::before {
        border-top: 1px solid var(--preferences-line);
    }
    .preferences-settings-page .settings-field:nth-child(4)::before,
    .preferences-settings-page .settings-field:nth-child(6)::before {
        content: "";
        position: absolute;
        top: 0;
        left: 10px;
        right: calc(-100% + 10px);
    }
    .preferences-settings-page .settings-field.full + .settings-field.full::before {
        content: "";
        position: absolute;
        top: 0;
        left: 10px;
        right: 10px;
    }
    .preferences-settings-page .settings-schedule-note {
        display: flex;
        align-items: center;
        justify-content: space-between;
        border-right: 0;
        border-bottom: 0;
        border-left: 0;
        border-radius: 0;
        background: transparent;
        color: var(--preferences-muted);
    }
    .preferences-settings-page .settings-schedule-note strong { color: var(--preferences-maroon); }
    .preferences-settings-page .settings-schedule-note a {
        color: var(--preferences-maroon);
        font-size: 12px;
        font-weight: 900;
    }
    .preferences-settings-page .settings-field label {
        color: var(--preferences-text);
        font-size: 12px;
        font-weight: 900;
        letter-spacing: 0;
        text-transform: none;
    }
    .preferences-settings-page .settings-field input,
    .preferences-settings-page .settings-field select,
    .preferences-settings-page .settings-field textarea {
        width: 100%;
        min-height: 28px;
        padding: 0 !important;
        border: 0 !important;
        border-radius: 0 !important;
        background: transparent !important;
        color: var(--preferences-text);
        font-size: 14px;
        font-weight: 600;
        box-shadow: none !important;
    }
    .preferences-settings-page .settings-field textarea {
        min-height: 58px;
        resize: vertical;
    }
    .preferences-settings-page .settings-editable-form.is-editing .settings-field input,
    .preferences-settings-page .settings-editable-form.is-editing .settings-field select,
    .preferences-settings-page .settings-editable-form.is-editing .settings-field textarea {
        border-bottom: 1px solid rgba(127, 0, 16, .28) !important;
    }
    .preferences-settings-page .settings-editable-form.is-editing .settings-field input:focus,
    .preferences-settings-page .settings-editable-form.is-editing .settings-field select:focus,
    .preferences-settings-page .settings-editable-form.is-editing .settings-field textarea:focus {
        border-bottom-color: var(--preferences-maroon) !important;
        outline: none;
    }
    .preferences-settings-page .settings-action-row {
        margin: 0;
        padding: 10px 12px 12px;
        border-top: 1px solid var(--preferences-line);
    }
    .preferences-settings-page .settings-save-btn {
        background-color: var(--preferences-maroon) !important;
        color: #ffffff !important;
    }
    .preferences-settings-page .settings-save-btn:hover,
    .preferences-settings-page .settings-save-btn:focus-visible {
        background-color: #facc15 !important;
        color: var(--preferences-maroon) !important;
    }
    .preferences-settings-page .settings-cancel-btn:hover,
    .preferences-settings-page .settings-cancel-btn:focus-visible {
        background-color: #4b5563 !important;
        color: #ffffff !important;
    }
    html[data-theme="dark"] .preferences-settings-page .settings-section-title > span:not(.preferences-title-icon),
    html[data-theme="dark"] .preferences-settings-page .settings-panel-head h3,
    html[data-theme="dark"] .preferences-settings-page .settings-field label,
    html[data-theme="dark"] .preferences-settings-page .settings-field input,
    html[data-theme="dark"] .preferences-settings-page .settings-field select,
    html[data-theme="dark"] .preferences-settings-page .settings-field textarea { color: #f8fafc; }
    html[data-theme="dark"] .preferences-settings-page .preferences-title-icon,
    html[data-theme="dark"] .preferences-settings-page .settings-panel-head h3 svg {
        background: rgba(127, 0, 16, .35);
        color: #fecdd3;
    }
    html[data-theme="dark"] .preferences-settings-page .settings-section-hero p,
    html[data-theme="dark"] .preferences-settings-page .settings-schedule-note { color: #cbd5e1; }
    html[data-theme="dark"] .preferences-settings-page .settings-schedule-note strong,
    html[data-theme="dark"] .preferences-settings-page .settings-schedule-note a { color: #facc15; }
    html[data-theme="dark"] .preferences-settings-page .settings-panel {
        background: rgba(15, 23, 42, .94);
        border-color: rgba(148, 163, 184, .2);
        box-shadow: 0 12px 28px rgba(0, 0, 0, .2);
    }
    html[data-theme="dark"] .preferences-settings-page .settings-edit-btn {
        background: rgba(15, 23, 42, .7) !important;
        color: #fecdd3 !important;
        border-color: rgba(250, 204, 21, .45);
    }
    html[data-theme="dark"] .preferences-settings-page .settings-field,
    html[data-theme="dark"] .preferences-settings-page .settings-form-grid,
    html[data-theme="dark"] .preferences-settings-page .settings-action-row { border-color: rgba(148, 163, 184, .16); }
    @media (max-width: 640px) {
        .preferences-settings-page .settings-section-hero > div:first-child { grid-template-columns: 44px minmax(0, 1fr); }
        .preferences-settings-page .preferences-title-icon { width: 44px; height: 44px; }
        .preferences-settings-page .preferences-title-icon svg { width: 24px; height: 24px; }
        .preferences-settings-page .settings-section-title > span:not(.preferences-title-icon) { font-size: 22px; }
        .preferences-settings-page .settings-form-grid { grid-template-columns: 1fr; }
        .preferences-settings-page .settings-field::after { display: none; }
        .preferences-settings-page .settings-schedule-note { align-items: flex-start; flex-direction: column; }
    }

    .preferences-settings-page .preferences-workflow-content {
        padding: 0 18px;
    }
    .preferences-settings-page .preferences-subheading {
        display: flex;
        align-items: center;
        gap: 12px;
        padding: 16px 10px 9px;
        color: var(--preferences-maroon);
        font-size: 15px;
        font-weight: 900;
    }
    .preferences-settings-page .preferences-general-grid {
        grid-template-columns: repeat(4, minmax(0, 1fr));
        padding: 0;
        border-bottom: 1px solid var(--preferences-line);
    }
    .preferences-settings-page .preferences-general-grid .settings-field {
        min-height: 72px;
        padding: 9px 14px;
    }
    .preferences-settings-page .preferences-general-grid .settings-field:nth-child(1)::after,
    .preferences-settings-page .preferences-general-grid .settings-field:nth-child(2)::after,
    .preferences-settings-page .preferences-general-grid .settings-field:nth-child(3)::after {
        content: "";
        position: absolute;
        top: 14px;
        right: 0;
        bottom: 14px;
        width: 1px;
        background: var(--preferences-line);
    }
    .preferences-settings-page .preferences-switch-row {
        display: flex;
        align-items: center;
        gap: 8px;
        min-height: 28px;
    }
    .preferences-settings-page .settings-field .preferences-switch-label {
        color: var(--preferences-text);
        font-size: 14px;
        font-weight: 600;
    }
    .preferences-settings-page .switch {
        --button-width: 3.5em;
        --button-height: 2em;
        --toggle-diameter: 1.5em;
        --button-toggle-offset: calc((var(--button-height) - var(--toggle-diameter)) / 2);
        --toggle-shadow-offset: 10px;
        --toggle-wider: 3em;
        --color-grey: #cccccc;
        --color-green: #4296f4;
        display: inline-flex;
        align-items: center;
        cursor: pointer;
    }
    .preferences-settings-page .switch .slider {
        display: inline-block;
        width: var(--button-width);
        height: var(--button-height);
        background-color: var(--color-grey);
        border-radius: calc(var(--button-height) / 2);
        position: relative;
        transition: .3s all ease-in-out;
    }
    .preferences-settings-page .switch .slider::after {
        content: "";
        display: inline-block;
        width: var(--toggle-diameter);
        height: var(--toggle-diameter);
        background-color: #fff;
        border-radius: calc(var(--toggle-diameter) / 2);
        position: absolute;
        top: var(--button-toggle-offset);
        transform: translateX(var(--button-toggle-offset));
        box-shadow: var(--toggle-shadow-offset) 0 calc(var(--toggle-shadow-offset) * 4) rgba(0, 0, 0, .1);
        transition: .3s all ease-in-out;
    }
    .preferences-settings-page .switch input[type="checkbox"] { display: none; }
    .preferences-settings-page .switch input[type="checkbox"]:checked + .slider { background-color: var(--color-green); }
    .preferences-settings-page .switch input[type="checkbox"]:checked + .slider::after {
        transform: translateX(calc(var(--button-width) - var(--toggle-diameter) - var(--button-toggle-offset)));
        box-shadow: calc(var(--toggle-shadow-offset) * -1) 0 calc(var(--toggle-shadow-offset) * 4) rgba(0, 0, 0, .1);
    }
    .preferences-settings-page .switch input[type="checkbox"]:active + .slider::after { width: var(--toggle-wider); }
    .preferences-settings-page .switch input[type="checkbox"]:checked:active + .slider::after {
        transform: translateX(calc(var(--button-width) - var(--toggle-wider) - var(--button-toggle-offset)));
    }
    .preferences-settings-page .switch input[type="checkbox"]:not(:checked) ~ .preferences-switch-label.is-on,
    .preferences-settings-page .switch input[type="checkbox"]:checked ~ .preferences-switch-label.is-off { display: none; }
    .preferences-settings-page .preferences-general-grid .settings-field select {
        display: block;
        min-height: 28px;
        padding: 0 22px 0 0 !important;
        border-bottom: 1px solid var(--preferences-line) !important;
        appearance: auto;
    }
    .preferences-settings-page .preferences-autosave-status {
        min-height: 18px;
        margin-left: auto;
        color: #64748b;
        font-size: 11px;
        font-weight: 700;
        transition: color .2s ease;
    }
    .preferences-settings-page .preferences-autosave-status.is-saving { color: #a16207; }
    .preferences-settings-page .preferences-autosave-status.is-saved { color: #15803d; }
    .preferences-settings-page .preferences-autosave-status.is-error { color: #b91c1c; }
    .preferences-settings-page .preferences-static-value {
        display: flex;
        align-items: center;
        min-height: 28px;
        gap: 8px;
        color: var(--preferences-text);
        font-size: 14px;
        font-weight: 600;
    }
    .preferences-settings-page .preferences-static-value::before {
        content: "";
        width: 22px;
        height: 13px;
        flex: 0 0 auto;
        border-radius: 999px;
        background: #d9dee7;
        box-shadow: inset 0 0 0 1px rgba(15, 23, 42, .08);
    }
    .preferences-settings-page .preferences-static-value.is-enabled::before {
        background: #62b36e;
        box-shadow: inset 0 0 0 1px rgba(22, 101, 52, .15);
    }
    .preferences-settings-page .preferences-static-value.is-enabled::after {
        content: "✓";
        width: 14px;
        height: 14px;
        margin-left: -25px;
        display: grid;
        place-items: center;
        border-radius: 50%;
        background: #ffffff;
        color: #3f9650;
        font-size: 10px;
        font-weight: 900;
    }
    .preferences-settings-page .preferences-static-value.is-disabled::after {
        content: "–";
        width: 14px;
        height: 14px;
        margin-left: -25px;
        display: grid;
        place-items: center;
        border-radius: 50%;
        background: #f1f4f8;
        color: #94a3b8;
        font-size: 12px;
        font-weight: 900;
    }
    .preferences-settings-page .preferences-edit-control { display: none; }
    .preferences-settings-page .settings-editable-form.is-editing .preferences-static-value { display: none; }
    .preferences-settings-page .settings-editable-form.is-editing .preferences-edit-control { display: block; }
    .preferences-settings-page .preferences-schedule-row {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 14px;
        min-height: 66px;
        margin: 0 0 12px;
        padding: 9px 12px;
        border: 1px solid var(--preferences-line);
        border-radius: 10px;
        background: rgba(255, 250, 250, .55);
    }
    .preferences-settings-page .preferences-schedule-copy {
        display: grid;
        grid-template-columns: 38px minmax(0, 1fr);
        align-items: center;
        column-gap: 10px;
        color: var(--preferences-muted);
        font-size: 13px;
    }
    .preferences-settings-page .preferences-schedule-copy svg {
        grid-row: 1 / span 2;
        width: 38px;
        height: 38px;
        padding: 8px;
        border-radius: 50%;
        background: #fff1f2;
        color: var(--preferences-maroon);
    }
    .preferences-settings-page .preferences-schedule-copy strong {
        color: var(--preferences-text);
        font-size: 14px;
    }
    .preferences-settings-page .preferences-schedule-link,
    .preferences-settings-page .preferences-closure-open {
        min-height: 36px;
        display: inline-flex;
        align-items: center;
        gap: 6px;
        padding: 0 12px;
        border: 1px solid rgba(127, 0, 16, .2);
        border-radius: 8px;
        background: #ffffff;
        color: var(--preferences-maroon);
        font-size: 12px;
        font-weight: 900;
        text-decoration: none;
        cursor: pointer;
    }
    .preferences-settings-page .preferences-schedule-link svg,
    .preferences-settings-page .preferences-closure-open svg { width: 15px; height: 15px; }
    .preferences-settings-page .preferences-schedule-link:hover,
    .preferences-settings-page .preferences-closure-open:hover {
        border-color: #facc15;
        background: #facc15;
        color: var(--preferences-maroon);
    }
    .preferences-settings-page .preferences-closure-card { margin-top: 0; }
    .preferences-settings-page .preferences-closure-body {
        min-height: 155px;
        padding: 16px 18px;
    }
    .preferences-settings-page .preferences-closure-status {
        display: inline-flex;
        align-items: center;
        gap: 7px;
        min-height: 25px;
        padding: 0 10px;
        border-radius: 999px;
        background: #e8f7eb;
        color: #3d9150;
        font-size: 12px;
        font-weight: 900;
    }
    .preferences-settings-page .preferences-closure-status::before {
        content: "";
        width: 8px;
        height: 8px;
        border-radius: 50%;
        background: currentColor;
    }
    .preferences-settings-page .preferences-closure-status.is-closed {
        background: #fff4d6;
        color: #a56a00;
    }
    .preferences-settings-page .preferences-closure-message {
        display: flex;
        gap: 10px;
        margin: 12px 0 16px;
        color: var(--preferences-text);
        font-size: 13px;
        line-height: 1.45;
    }
    .preferences-settings-page .preferences-closure-message svg {
        width: 18px;
        height: 18px;
        flex: 0 0 auto;
        color: #3d9150;
    }
    .preferences-settings-page .preferences-closure-message.is-closed svg { color: #a56a00; }
    .preferences-settings-page .preferences-closure-open { margin-top: 8px; }

    .preferences-modal[hidden] { display: none; }
    .preferences-modal {
        --preferences-maroon: #7f0010;
        --preferences-text: #172033;
        --preferences-line: rgba(148, 163, 184, .22);
        position: fixed;
        inset: 0;
        z-index: 2147483200;
        display: grid;
        place-items: center;
        padding: 20px;
        background: rgba(15, 23, 42, .58);
        backdrop-filter: blur(4px);
    }
    .preferences-modal .preferences-modal-dialog {
        width: min(620px, 100%);
        max-height: min(720px, calc(100dvh - 40px));
        display: flex;
        flex-direction: column;
        overflow: hidden;
        border: 1px solid rgba(250, 204, 21, .42);
        border-radius: 14px;
        background: #ffffff;
        box-shadow: 0 24px 70px rgba(15, 23, 42, .3);
    }
    .preferences-modal .preferences-modal-header {
        display: flex;
        align-items: center;
        flex: 0 0 auto;
        gap: 12px;
        padding: 14px 16px;
        background: linear-gradient(110deg, #8d091a, #b91c1c);
        color: #ffffff;
    }
    .preferences-modal .preferences-modal-header > svg {
        width: 42px;
        height: 42px;
        padding: 9px;
        border: 1px solid rgba(255, 255, 255, .28);
        border-radius: 10px;
    }
    .preferences-modal .preferences-modal-header h3 { margin: 0; color: #ffffff; font-size: 18px; font-weight: 900; }
    .preferences-modal .preferences-modal-header p { margin: 3px 0 0; color: #ffffff; font-size: 12px; opacity: .9; }
    .preferences-modal .preferences-modal-close {
        width: 34px;
        height: 34px;
        display: grid;
        place-items: center;
        margin-left: auto;
        border: 0;
        border-radius: 50%;
        background: rgba(127, 0, 16, .65);
        color: #ffffff;
        cursor: pointer;
        background-image: linear-gradient(105deg, transparent 0%, transparent 42%, rgba(255, 249, 210, .82) 50%, transparent 58%, transparent 100%);
        background-size: 240% 100%;
        background-position: 100% 0;
        transition: background-color .2s ease, background-position .42s ease, color .2s ease, transform .2s ease, box-shadow .2s ease;
    }
    .preferences-modal .preferences-modal-close:hover,
    .preferences-modal .preferences-modal-close:focus-visible {
        outline: none;
        background-color: #facc15;
        background-position: 0 0;
        color: var(--preferences-maroon);
        transform: translateY(-1px);
        box-shadow: 0 5px 13px rgba(66, 20, 10, .2);
    }
    .preferences-modal .preferences-modal-close svg { width: 18px; height: 18px; }
    .preferences-modal .preferences-modal-body { min-height: 0; overflow-y: auto; padding: 18px; }
    .preferences-modal .preferences-modal-grid { display: grid; grid-template-columns: repeat(2, minmax(0, 1fr)); gap: 14px; }
    .preferences-modal .preferences-modal-field { display: grid; gap: 6px; }
    .preferences-modal .preferences-modal-field.full { grid-column: 1 / -1; }
    .preferences-modal .preferences-modal-field label { color: var(--preferences-text); font-size: 12px; font-weight: 900; }
    .preferences-modal .preferences-modal-field input,
    .preferences-modal .preferences-modal-field select,
    .preferences-modal .preferences-modal-field textarea {
        width: 100%;
        min-height: 42px;
        padding: 9px 11px;
        border: 1px solid rgba(127, 0, 16, .18);
        border-radius: 8px;
        background: #ffffff;
        color: var(--preferences-text);
        font: inherit;
        font-size: 13px;
    }
    .preferences-modal .preferences-modal-field textarea { min-height: 88px; resize: vertical; }
    .preferences-modal .preferences-modal-footer {
        display: flex;
        justify-content: flex-end;
        flex: 0 0 auto;
        gap: 10px;
        padding: 12px 18px;
        border-top: 1px solid var(--preferences-line);
    }
    .preferences-modal .preferences-modal-footer button { min-width: 108px; }
    .preferences-modal .preferences-modal-footer .settings-save-btn {
        background-color: var(--preferences-maroon) !important;
        background-image: linear-gradient(105deg, transparent 0%, transparent 42%, rgba(255, 249, 210, .82) 50%, transparent 58%, transparent 100%) !important;
        background-size: 240% 100% !important;
        background-position: 100% 0 !important;
        color: #ffffff !important;
        box-shadow: 0 8px 18px rgba(127, 0, 16, .2);
        transition: background-color .2s ease, background-position .42s ease, color .2s ease, transform .2s ease, box-shadow .2s ease;
    }
    .preferences-modal .preferences-modal-footer .settings-save-btn:hover,
    .preferences-modal .preferences-modal-footer .settings-save-btn:focus-visible {
        outline: none;
        background-color: #facc15 !important;
        background-position: 0 0 !important;
        color: var(--preferences-maroon) !important;
        transform: translateY(-1px);
        box-shadow: 0 10px 20px rgba(127, 0, 16, .2);
    }
    html[data-theme="dark"] .preferences-settings-page .preferences-schedule-row,
    html[data-theme="dark"] .preferences-settings-page .preferences-closure-card,
    html[data-theme="dark"] .preferences-modal .preferences-modal-dialog { background: rgba(15, 23, 42, .96); }
    html[data-theme="dark"] .preferences-settings-page .preferences-schedule-copy,
    html[data-theme="dark"] .preferences-settings-page .preferences-closure-message,
    html[data-theme="dark"] .preferences-settings-page .preferences-static-value,
    html[data-theme="dark"] .preferences-modal .preferences-modal-field label { color: #f8fafc; }
    html[data-theme="dark"] .preferences-settings-page .preferences-schedule-copy svg { background: rgba(127, 0, 16, .35); color: #fecdd3; }
    html[data-theme="dark"] .preferences-settings-page .preferences-schedule-link,
    html[data-theme="dark"] .preferences-settings-page .preferences-closure-open,
    html[data-theme="dark"] .preferences-modal .preferences-modal-field input,
    html[data-theme="dark"] .preferences-modal .preferences-modal-field select,
    html[data-theme="dark"] .preferences-modal .preferences-modal-field textarea { background: rgba(30, 41, 59, .78); color: #f8fafc; border-color: rgba(148, 163, 184, .25); }
    @media (max-width: 1080px) and (min-width: 641px) {
        .preferences-settings-page .preferences-general-grid { grid-template-columns: repeat(2, minmax(0, 1fr)); }
        .preferences-settings-page .preferences-general-grid .settings-field::after { display: none; }
        .preferences-settings-page .preferences-general-grid .settings-field:nth-child(odd)::after { display: block; }
        .preferences-settings-page .preferences-general-grid .settings-field:nth-child(n + 3)::before { content: ""; position: absolute; top: 0; left: 10px; right: 10px; border-top: 1px solid var(--preferences-line); }
    }
    @media (max-width: 640px) {
        .preferences-settings-page .preferences-content { padding: 0 10px; }
        .preferences-settings-page .preferences-general-grid { grid-template-columns: 1fr; }
        .preferences-settings-page .preferences-general-grid .settings-field::after { display: none; }
        .preferences-settings-page .preferences-general-grid .settings-field + .settings-field::before { content: ""; position: absolute; top: 0; left: 10px; right: 10px; border-top: 1px solid var(--preferences-line); }
        .preferences-settings-page .preferences-schedule-row { align-items: flex-start; flex-direction: column; }
        .preferences-settings-page .preferences-schedule-link { width: 100%; justify-content: center; }
        .preferences-modal { padding: 10px; }
        .preferences-modal .preferences-modal-grid { grid-template-columns: 1fr; }
        .preferences-modal .preferences-modal-field.full { grid-column: auto; }
        .preferences-modal .preferences-modal-footer { flex-direction: column-reverse; }
        .preferences-modal .preferences-modal-footer button { width: 100%; }
    }
</style>
@endpush

@section('content')
@php
    $reminderOptions = [0 => 'Disabled', 1 => '1 hour before', 3 => '3 hours before', 24 => '1 day before', 48 => '2 days before'];
    $complianceReminderOptions = [0 => 'Disabled', 1 => 'Every day', 3 => 'Every 3 days', 7 => 'Every 7 days', 14 => 'Every 14 days', 30 => 'Every 30 days'];
    $currentReminderHours = (int) ($settings->appointment_reminder_hours ?? 24);
    $currentComplianceReminderDays = (int) ($settings->pending_compliance_reminder_days ?? 7);
    $adminNotificationsEnabled = (string) old('admin_live_notifications', $settings->admin_live_notifications !== false ? '1' : '0') === '1';
    $emailNotificationsEnabled = (string) old('email_notifications', $settings->email_notifications !== false ? '1' : '0') === '1';
    $closureEnabled = (bool) $settings->clinic_closure_enabled;
    $closureStartsValue = optional($settings->clinic_closure_starts_at)->format('Y-m-d\\TH:i');
    $closureEndsValue = optional($settings->clinic_closure_ends_at)->format('Y-m-d\\TH:i');
@endphp
<div class="settings-section-page preferences-settings-page">
    @if(session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif

    @if ($errors->any())
        <div class="alert alert-error">
            <ul style="margin:0; padding-left:18px;">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <section class="settings-section-hero">
        <div>
            <h1 class="settings-section-title"><span class="preferences-title-icon"><x-outline-icon name="code-bracket-square" /></span><span>System Preferences</span></h1>
            <p>Configure live alerts, email delivery, reminders, and clinic closures.</p>
        </div>
        <a href="{{ route('admin.settings') }}" class="settings-back-link"><x-outline-icon name="chevron-right" /> Settings Hub</a>
    </section>

    <section class="settings-panel preferences-workflow-panel">
        <div class="settings-panel-head">
            <div>
                <h3><x-outline-icon name="cog-6-tooth" />Workflow Settings</h3>
                <p>These controls use the existing system settings update workflow.</p>
            </div>
        </div>
        <div class="settings-panel-body">
            <form id="systemPreferencesForm" action="{{ route('admin.settings.update') }}" method="POST" class="settings-editable-form">
                @csrf
                @method('PUT')
                <input type="hidden" name="preferences_form" value="1">
                <input type="hidden" name="clinic_closure_enabled" value="{{ $closureEnabled ? '1' : '0' }}">
                <input type="hidden" name="clinic_closure_starts_at" value="{{ $closureStartsValue }}">
                <input type="hidden" name="clinic_closure_ends_at" value="{{ $closureEndsValue }}">
                <input type="hidden" name="clinic_closure_reason" value="{{ $settings->clinic_closure_reason }}">
                <input type="hidden" name="clinic_closure_message" value="{{ $settings->clinic_closure_message }}">

                <div class="preferences-workflow-content">
                    <div class="preferences-subheading">
                        <span>General Preferences</span>
                        <span class="preferences-autosave-status" data-preferences-autosave-status aria-live="polite"></span>
                    </div>
                    <div class="settings-form-grid preferences-general-grid">
                        <div class="settings-field">
                            <label for="admin_live_notifications">Admin Live Alert</label>
                            <input type="hidden" name="admin_live_notifications" value="0">
                            <label class="switch preferences-switch-row" for="admin_live_notifications">
                                <input type="checkbox" id="admin_live_notifications" name="admin_live_notifications" value="1" {{ $adminNotificationsEnabled ? 'checked' : '' }}>
                                <span class="slider" aria-hidden="true"></span>
                                <span class="preferences-switch-label is-on">Enabled</span>
                                <span class="preferences-switch-label is-off">Disabled</span>
                            </label>
                        </div>
                        <div class="settings-field">
                            <label for="email_notifications">Email Notifications</label>
                            <input type="hidden" name="email_notifications" value="0">
                            <label class="switch preferences-switch-row" for="email_notifications">
                                <input type="checkbox" id="email_notifications" name="email_notifications" value="1" {{ $emailNotificationsEnabled ? 'checked' : '' }}>
                                <span class="slider" aria-hidden="true"></span>
                                <span class="preferences-switch-label is-on">Enabled</span>
                                <span class="preferences-switch-label is-off">Disabled</span>
                            </label>
                        </div>
                        <div class="settings-field">
                            <label for="appointment_reminder_hours">Appointment Reminder</label>
                            <select id="appointment_reminder_hours" name="appointment_reminder_hours" required>
                                @foreach($reminderOptions as $hours => $label)
                                    <option value="{{ $hours }}" {{ (int) old('appointment_reminder_hours', $currentReminderHours) === $hours ? 'selected' : '' }}>{{ $label }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="settings-field">
                            <label for="pending_compliance_reminder_days">Pending Compliance Reminder</label>
                            <select id="pending_compliance_reminder_days" name="pending_compliance_reminder_days" required>
                                @foreach($complianceReminderOptions as $days => $label)
                                    <option value="{{ $days }}" {{ (int) old('pending_compliance_reminder_days', $currentComplianceReminderDays) === $days ? 'selected' : '' }}>{{ $label }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>

                    <div class="preferences-subheading">Clinic Operating Schedule</div>
                    <div class="preferences-schedule-row">
                        <div class="preferences-schedule-copy">
                            <x-outline-icon name="clock" />
                            <span>Clinic operating hours:</span>
                            <strong>{{ app(\App\Services\ClinicWorkflowService::class)->clinicScheduleLabel() }}</strong>
                        </div>
                        <a class="preferences-schedule-link" href="{{ route('admin.settings.clinic') }}">Manage Schedule <x-outline-icon name="chevron-right" /></a>
                    </div>
                </div>
            </form>
        </div>
    </section>

    <section class="settings-panel preferences-closure-card">
        <div class="settings-panel-head">
            <div><h3><x-outline-icon name="calendar" />Temporary Clinic Closure</h3></div>
        </div>
        <div class="preferences-closure-body">
            <span class="preferences-closure-status {{ $closureEnabled ? 'is-closed' : '' }}">{{ $closureEnabled ? 'Temporarily Closed' : 'Available' }}</span>
            <div class="preferences-closure-message {{ $closureEnabled ? 'is-closed' : '' }}">
                <x-outline-icon name="check" />
                <span>{{ $closureEnabled ? ($settings->clinic_closure_message ?: 'A temporary clinic closure is scheduled.') : 'The clinic is currently operating normally. No temporary closure is scheduled.' }}</span>
            </div>
            <button type="button" class="preferences-closure-open" data-preferences-modal-open="#temporaryClosureModal">
                <x-outline-icon name="calendar" /> {{ $closureEnabled ? 'Edit Temporary Closure' : 'Set Temporary Closure' }}
            </button>
        </div>
    </section>

    <div class="preferences-modal" id="temporaryClosureModal" hidden>
        <section class="preferences-modal-dialog" role="dialog" aria-modal="true" aria-labelledby="temporaryClosureTitle">
            <div class="preferences-modal-header">
                <x-outline-icon name="calendar" />
                <div>
                    <h3 id="temporaryClosureTitle">Temporary Clinic Closure</h3>
                    <p>Set the clinic closure schedule and the message shown to users.</p>
                </div>
                <button type="button" class="preferences-modal-close" data-preferences-modal-close aria-label="Close temporary closure modal"><x-outline-icon name="x-mark" /></button>
            </div>
            <form id="temporaryClosureForm" action="{{ route('admin.settings.update') }}" method="POST">
                @csrf
                @method('PUT')
                <input type="hidden" name="preferences_form" value="1">
                <input type="hidden" name="admin_live_notifications" value="{{ $settings->admin_live_notifications !== false ? '1' : '0' }}">
                <input type="hidden" name="email_notifications" value="{{ $settings->email_notifications !== false ? '1' : '0' }}">
                <input type="hidden" name="appointment_reminder_hours" value="{{ $currentReminderHours }}">
                <input type="hidden" name="pending_compliance_reminder_days" value="{{ $currentComplianceReminderDays }}">
                <div class="preferences-modal-body">
                    <div class="preferences-modal-grid">
                        <div class="preferences-modal-field full">
                            <label for="modal_clinic_closure_enabled">Closure Status</label>
                            <select id="modal_clinic_closure_enabled" name="clinic_closure_enabled" required>
                                <option value="0" {{ !$closureEnabled ? 'selected' : '' }}>Available</option>
                                <option value="1" {{ $closureEnabled ? 'selected' : '' }}>Temporarily Closed</option>
                            </select>
                        </div>
                        <div class="preferences-modal-field">
                            <label for="modal_clinic_closure_starts_at">Closure Starts</label>
                            <input id="modal_clinic_closure_starts_at" name="clinic_closure_starts_at" type="datetime-local" value="{{ $closureStartsValue }}">
                        </div>
                        <div class="preferences-modal-field">
                            <label for="modal_clinic_closure_ends_at">Closure Ends</label>
                            <input id="modal_clinic_closure_ends_at" name="clinic_closure_ends_at" type="datetime-local" value="{{ $closureEndsValue }}">
                        </div>
                        <div class="preferences-modal-field full">
                            <label for="modal_clinic_closure_reason">Closure Reason</label>
                            <select id="modal_clinic_closure_reason" name="clinic_closure_reason">
                                @foreach(['Staff Meeting', 'Official Clinic Activity', 'Emergency', 'Early Closure', 'Other'] as $reason)
                                    <option value="{{ $reason }}" {{ $settings->clinic_closure_reason === $reason ? 'selected' : '' }}>{{ $reason }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="preferences-modal-field full">
                            <label for="modal_clinic_closure_message">Closure Message</label>
                            <textarea id="modal_clinic_closure_message" name="clinic_closure_message" maxlength="500">{{ $settings->clinic_closure_message }}</textarea>
                        </div>
                    </div>
                </div>
                <div class="preferences-modal-footer">
                    <button type="button" class="settings-cancel-btn" data-preferences-modal-cancel>Cancel</button>
                    <button type="submit" class="settings-save-btn"><x-outline-icon name="check" /> Save Closure</button>
                </div>
            </form>
        </section>
    </div>
</div>
@include('admin.partials.settings-edit-script')
<script>
document.addEventListener('DOMContentLoaded', function () {
    const generalPreferencesForm = document.querySelector('#systemPreferencesForm');
    const closurePreferencesForm = document.querySelector('#temporaryClosureForm');
    const autosaveStatus = document.querySelector('[data-preferences-autosave-status]');
    let autosaveTimer = null;
    let autosaveRunning = false;
    let autosaveQueued = false;
    let autosaveStatusTimer = null;

    document.querySelectorAll('.preferences-modal').forEach(function (modal) {
        document.body.appendChild(modal);
    });

    function preferenceValue(name) {
        const field = generalPreferencesForm?.querySelector(`[name="${name}"]:not([type="hidden"])`);
        if (!field) return '';
        return field.type === 'checkbox' ? (field.checked ? '1' : '0') : field.value;
    }

    function syncClosurePreferences() {
        if (!closurePreferencesForm) return;
        ['admin_live_notifications', 'email_notifications', 'appointment_reminder_hours', 'pending_compliance_reminder_days'].forEach(function (name) {
            const preservedField = closurePreferencesForm.querySelector(`[name="${name}"]`);
            if (preservedField) preservedField.value = preferenceValue(name);
        });
    }

    function showAutosaveStatus(message, state) {
        if (!autosaveStatus) return;
        window.clearTimeout(autosaveStatusTimer);
        autosaveStatus.textContent = message;
        autosaveStatus.className = `preferences-autosave-status ${state ? `is-${state}` : ''}`.trim();
        if (state === 'saved') {
            autosaveStatusTimer = window.setTimeout(function () {
                autosaveStatus.textContent = '';
                autosaveStatus.className = 'preferences-autosave-status';
            }, 1800);
        }
    }

    async function saveGeneralPreferences() {
        if (!generalPreferencesForm) return;
        if (autosaveRunning) {
            autosaveQueued = true;
            return;
        }

        autosaveRunning = true;
        showAutosaveStatus('Saving...', 'saving');

        do {
            autosaveQueued = false;
            syncClosurePreferences();

            try {
                const response = await fetch(generalPreferencesForm.action, {
                    method: 'POST',
                    body: new FormData(generalPreferencesForm),
                    headers: {
                        'Accept': 'application/json',
                        'X-Requested-With': 'XMLHttpRequest',
                    },
                    credentials: 'same-origin',
                });

                if (!response.ok) throw new Error('Unable to save preferences.');
                if (!autosaveQueued) showAutosaveStatus('Saved', 'saved');
            } catch (error) {
                autosaveQueued = false;
                showAutosaveStatus('Could not save', 'error');
            }
        } while (autosaveQueued);

        autosaveRunning = false;
    }

    generalPreferencesForm?.addEventListener('change', function (event) {
        if (!event.target.matches('input[type="checkbox"], select')) return;

        syncClosurePreferences();
        window.clearTimeout(autosaveTimer);
        autosaveTimer = window.setTimeout(saveGeneralPreferences, 180);
    });

    document.querySelectorAll('[data-preferences-modal-open]').forEach(function (button) {
        button.addEventListener('click', function () {
            const modal = document.querySelector(button.dataset.preferencesModalOpen);
            if (!modal) return;
            modal.hidden = false;
            document.body.classList.add('modal-open');
            modal.querySelector('select, input, textarea')?.focus();
        });
    });

    document.querySelectorAll('#temporaryClosureModal [data-preferences-modal-close], #temporaryClosureModal [data-preferences-modal-cancel]').forEach(function (button) {
        button.addEventListener('click', function () {
            const modal = button.closest('.preferences-modal');
            if (!modal) return;
            if (button.hasAttribute('data-preferences-modal-cancel')) modal.querySelector('form')?.reset();
            modal.hidden = true;
            document.body.classList.remove('modal-open');
        });
    });

    document.querySelector('#temporaryClosureModal')?.addEventListener('click', function (event) {
        if (event.target === this) {
            this.hidden = true;
            document.body.classList.remove('modal-open');
        }
    });
});
</script>
@endsection
