@extends('layouts.admin')

@section('title', 'Announcement')

@push('styles')
<style>
    .announcement-page {
        width: min(1180px, 100%);
        margin: 0 auto;
        display: grid;
        gap: 24px;
    }

    .announcement-hero,
    .announcement-card {
        border: 1px solid rgba(112, 19, 27, 0.12);
        background: #ffffff;
        box-shadow: 0 18px 42px rgba(15, 23, 42, 0.05);
    }

    .announcement-hero {
        display: grid;
        gap: 22px;
        padding: 24px 28px;
        border-radius: 18px;
    }

    .announcement-hero-head {
        display: flex;
        align-items: flex-start;
        justify-content: space-between;
        gap: 18px;
    }

    .announcement-title-block {
        display: flex;
        align-items: center;
        gap: 18px;
    }

    .announcement-hero-icon,
    .announcement-form-icon {
        display: grid;
        place-items: center;
        border-radius: 12px;
        background: #fff0f2;
        color: #8f1827;
        border: 1px solid rgba(143, 24, 39, 0.08);
    }

    .announcement-hero-icon {
        width: 58px;
        height: 58px;
        flex: 0 0 auto;
    }

    .announcement-hero-icon svg {
        width: 30px;
        height: 30px;
    }

    .announcement-hero h2 {
        margin: 0;
        color: #111827;
        font-size: 24px;
        font-weight: 950;
        line-height: 1.15;
    }

    .announcement-hero p {
        margin: 5px 0 0;
        color: #64748b;
        font-size: 13px;
        font-weight: 750;
    }

    .announcement-last-updated {
        min-width: 184px;
        display: flex;
        align-items: center;
        justify-content: flex-start;
        gap: 10px;
        color: #111827;
        text-align: left;
        font-size: 13px;
        font-weight: 950;
        text-transform: uppercase;
    }

    .announcement-last-updated span {
        display: block;
        color: #64748b;
        font-size: 11px;
        letter-spacing: .04em;
    }

    .announcement-last-updated-icon {
        position: relative;
        width: 42px;
        height: 42px;
        display: block;
        flex: 0 0 42px;
        border-radius: 11px;
        background: #fff0f2;
        color: #64748b;
        line-height: 0;
    }

    .announcement-last-updated-icon svg {
        position: absolute;
        left: 50%;
        top: 50%;
        display: block;
        width: 18px;
        height: 18px;
        transform: translate(-50%, -50%);
    }

    .announcement-stat-grid {
        display: grid;
        grid-template-columns: repeat(4, minmax(0, 1fr));
        gap: 14px;
    }

    .announcement-stat-card {
        min-height: 112px;
        display: grid;
        grid-template-columns: 52px minmax(0, 1fr);
        align-items: center;
        gap: 14px;
        border-radius: 16px;
        border: 1px solid rgba(112, 19, 27, 0.14);
        background: #ffffff;
        padding: 16px;
        transition: transform .18s ease, border-color .18s ease, box-shadow .18s ease;
    }

    .announcement-stat-card:hover,
    .announcement-stat-card:focus-visible {
        transform: translateY(-6px) scale(1.025);
        border-color: rgba(250, 204, 21, 0.78);
        box-shadow: 0 20px 36px rgba(112, 19, 27, 0.16);
        outline: none;
    }

    .announcement-stat-card.is-primary {
        background: #951426;
        color: #ffffff;
        border-color: rgba(250, 204, 21, 0.42);
    }

    .announcement-stat-button {
        width: 100%;
        appearance: none;
        font: inherit;
        text-align: left;
        cursor: pointer;
    }

    .announcement-stat-icon {
        width: 52px;
        height: 52px;
        display: grid;
        place-items: center;
        border-radius: 999px;
        background: #dcfce7;
        color: #16a34a;
    }

    .announcement-stat-card.is-primary .announcement-stat-icon {
        background: rgba(255, 255, 255, .14);
        color: #ffffff;
    }

    .announcement-stat-card.is-urgent .announcement-stat-icon {
        background: #fee2e2;
        color: #e11d48;
    }

    .announcement-stat-card.is-warning .announcement-stat-icon {
        background: #fef3c7;
        color: #d97706;
    }

    .announcement-stat-card.is-archived .announcement-stat-icon {
        background: #f1f5f9;
        color: #64748b;
    }

    .announcement-stat-icon svg {
        width: 22px;
        height: 22px;
    }

    .announcement-stat-label,
    .announcement-stat-note {
        display: block;
        font-size: 12px;
        font-weight: 950;
        line-height: 1.25;
        text-transform: uppercase;
    }

    .announcement-stat-value {
        display: block;
        margin-top: 4px;
        color: #0f172a;
        font-size: 28px;
        font-weight: 950;
        line-height: 1;
    }

    .announcement-stat-note {
        margin-top: 8px;
        color: #475569;
        text-transform: none;
    }

    .announcement-stat-card.is-primary .announcement-stat-value,
    .announcement-stat-card.is-primary .announcement-stat-note {
        color: #ffffff;
    }

    .announcement-grid {
        display: grid;
        grid-template-columns: minmax(300px, 0.9fr) minmax(360px, 1.15fr);
        gap: 18px;
        align-items: start;
        margin-top: 4px;
    }

    .announcement-card {
        position: relative;
        overflow: hidden;
        border-radius: 12px;
        padding: 24px;
    }

    .announcement-card::before {
        content: "";
        position: absolute;
        top: 0;
        left: 18px;
        right: 18px;
        height: 5px;
        border-radius: 0 0 999px 999px;
        background: #70131B;
    }

    .announcement-card-head {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 12px;
        margin-bottom: 22px;
    }

    .announcement-card-title {
        display: flex;
        align-items: center;
        gap: 10px;
        margin: 0;
        color: #70131B;
        font-size: 16px;
        font-weight: 950;
    }

    .announcement-form-icon {
        width: 34px;
        height: 34px;
    }

    .announcement-form-icon svg {
        width: 18px;
        height: 18px;
    }

    .announcement-form {
        display: grid;
        gap: 16px;
    }

    .announcement-field {
        display: grid;
        gap: 8px;
    }

    .announcement-label {
        color: #111827;
        font-size: 12px;
        font-weight: 950;
        letter-spacing: 0.02em;
        text-transform: uppercase;
    }

    .announcement-input,
    .announcement-select,
    .announcement-textarea {
        width: 100%;
        border-radius: 8px;
        border: 1px solid rgba(148, 163, 184, 0.42);
        background: #ffffff;
        color: #0f172a;
        font: inherit;
        font-size: 14px;
        font-weight: 700;
        outline: none;
        transition: border-color 0.18s ease, box-shadow 0.18s ease;
    }

    .announcement-input,
    .announcement-select {
        min-height: 46px;
        padding: 0 14px;
    }

    .announcement-textarea {
        min-height: 132px;
        resize: vertical;
        padding: 14px;
        line-height: 1.55;
    }

    .announcement-input:focus,
    .announcement-select:focus,
    .announcement-textarea:focus {
        border-color: rgba(112, 19, 27, 0.58);
        box-shadow: 0 0 0 4px rgba(112, 19, 27, 0.08);
    }

    .announcement-editor {
        overflow: hidden;
        border-radius: 8px;
        border: 1px solid rgba(148, 163, 184, 0.42);
        background: #ffffff;
    }

    .announcement-toolbar {
        min-height: 42px;
        display: flex;
        align-items: center;
        gap: 6px;
        padding: 0 10px;
        border-bottom: 1px solid rgba(148, 163, 184, 0.2);
        color: #334155;
    }

    .announcement-tool {
        width: 30px;
        height: 30px;
        display: grid;
        place-items: center;
        border: 1px solid transparent;
        border-radius: 6px;
        background: transparent;
        color: inherit;
        font-size: 12px;
        font-weight: 950;
        cursor: pointer;
    }

    .announcement-tool:hover,
    .announcement-tool:focus-visible {
        border-color: rgba(112, 19, 27, 0.18);
        background: #fff0f2;
        color: #70131B;
        outline: none;
    }

    .announcement-editor .announcement-textarea {
        border: 0;
        border-radius: 0;
        box-shadow: none;
    }

    .announcement-counter {
        margin-top: -6px;
        color: #94a3b8;
        font-size: 11px;
        font-weight: 800;
        text-align: right;
    }

    .announcement-submit {
        position: relative;
        overflow: hidden;
        min-height: 50px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        gap: 10px;
        border: 0;
        border-radius: 8px;
        background: linear-gradient(135deg, #8f1024, #70131B);
        color: #ffffff;
        font-weight: 950;
        cursor: pointer;
        box-shadow: 0 14px 24px rgba(112, 19, 27, 0.18);
        transition: transform 0.18s ease, box-shadow 0.18s ease, background .18s ease, color .18s ease;
    }

    .announcement-submit::after {
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

    .announcement-submit > * {
        position: relative;
        z-index: 1;
    }

    .announcement-submit:hover,
    .announcement-submit:focus-visible {
        transform: translateY(-1px);
        background: #facc15;
        color: #70131B;
        box-shadow: 0 18px 30px rgba(112, 19, 27, 0.22);
        outline: none;
    }

    .announcement-submit:hover::after,
    .announcement-submit:focus-visible::after {
        left: 128%;
    }

    .announcement-submit svg {
        width: 18px;
        height: 18px;
    }

    .announcement-alert,
    .announcement-error {
        border-radius: 10px;
        padding: 12px 14px;
        font-size: 13px;
        font-weight: 850;
    }

    .announcement-alert {
        background: #dcfce7;
        color: #166534;
    }

    .announcement-error {
        background: #fee2e2;
        color: #991b1b;
    }

    .announcement-list {
        display: grid;
        gap: 14px;
    }

    .announcement-item {
        position: relative;
        display: grid;
        gap: 12px;
        padding: 18px 16px 16px 20px;
        border-radius: 10px;
        border: 1px solid rgba(148, 163, 184, 0.26);
        background: #ffffff;
        overflow: hidden;
    }

    .announcement-item::before {
        content: "";
        position: absolute;
        inset: 0 auto 0 0;
        width: 4px;
        background: var(--announcement-priority-color, #2563eb);
    }

    .announcement-item-top,
    .announcement-meta-row,
    .announcement-actions {
        display: flex;
        align-items: center;
        gap: 10px;
    }

    .announcement-item-top {
        justify-content: space-between;
        gap: 14px;
    }

    .announcement-priority {
        display: inline-flex;
        align-items: center;
        gap: 8px;
        color: var(--announcement-priority-color, #2563eb);
        font-size: 11px;
        font-weight: 950;
        letter-spacing: 0.04em;
        text-transform: uppercase;
    }

    .announcement-priority-dot {
        width: 10px;
        height: 10px;
        border-radius: 999px;
        background: currentColor;
    }

    .announcement-time {
        color: #64748b;
        font-size: 12px;
        font-weight: 800;
    }

    .announcement-status {
        min-height: 30px;
        display: inline-flex;
        align-items: center;
        border-radius: 8px;
        background: #dcfce7;
        color: #15803d;
        padding: 0 12px;
        font-size: 12px;
        font-weight: 950;
    }

    .announcement-status.is-archived,
    .announcement-status.is-expired {
        background: #f1f5f9;
        color: #64748b;
    }

    .announcement-delete,
    .announcement-archive {
        width: 34px;
        height: 34px;
        display: grid;
        place-items: center;
        border-radius: 8px;
        cursor: pointer;
        transition: background 0.18s ease, color 0.18s ease;
    }

    .announcement-delete {
        border: 1px solid rgba(190, 18, 60, 0.22);
        background: #fff1f2;
        color: #be123c;
    }

    .announcement-archive {
        border: 1px solid rgba(100, 116, 139, 0.24);
        background: #f8fafc;
        color: #475569;
    }

    .announcement-delete:hover,
    .announcement-delete:focus-visible,
    .announcement-archive:hover,
    .announcement-archive:focus-visible {
        background: #70131B;
        color: #ffffff;
        outline: none;
    }

    .announcement-delete svg,
    .announcement-archive svg {
        width: 17px;
        height: 17px;
    }

    .announcement-item h3 {
        margin: 0;
        color: #111827;
        font-size: 17px;
        font-weight: 950;
        line-height: 1.25;
    }

    .announcement-message {
        margin: 0;
        color: #475569;
        font-size: 13px;
        font-weight: 700;
        line-height: 1.55;
    }

    .announcement-message p,
    .announcement-message ul {
        margin: 0 0 8px;
    }

    .announcement-message p:last-child,
    .announcement-message ul:last-child {
        margin-bottom: 0;
    }

    .announcement-message ul {
        padding-left: 18px;
    }

    .announcement-message strong {
        color: #111827;
        font-weight: 950;
    }

    .announcement-meta-row {
        flex-wrap: wrap;
        color: #64748b;
        font-size: 12px;
        font-weight: 800;
    }

    .announcement-meta-row strong {
        color: #70131B;
        font-weight: 950;
        text-transform: uppercase;
    }

    .announcement-divider {
        width: 1px;
        height: 16px;
        background: rgba(148, 163, 184, 0.32);
    }

    .announcement-empty-state {
        min-height: 260px;
        display: grid;
        place-items: center;
        text-align: center;
        border-radius: 12px;
        border: 1px dashed rgba(112, 19, 27, 0.2);
        color: #64748b;
        padding: 28px;
    }

    .announcement-empty-state h3 {
        margin: 0 0 8px;
        color: #111827;
        font-size: 18px;
        font-weight: 950;
    }

    .announcement-empty-state p {
        margin: 0;
        font-size: 13px;
        font-weight: 750;
    }

    .priority-urgent { --announcement-priority-color: #e11d48; }
    .priority-info { --announcement-priority-color: #2563eb; }
    .priority-warning { --announcement-priority-color: #f59e0b; }
    .priority-health { --announcement-priority-color: #16a34a; }
    .priority-event { --announcement-priority-color: #9333ea; }

    @media (max-width: 980px) {
        .announcement-grid {
            grid-template-columns: 1fr;
        }
    }

    @media (max-width: 640px) {
        .announcement-hero,
        .announcement-card {
            padding: 18px;
        }

        .announcement-hero-head {
            align-items: flex-start;
            flex-direction: column;
        }

        .announcement-last-updated {
            min-width: 0;
            text-align: left;
            justify-content: flex-start;
        }

        .announcement-stat-grid {
            grid-template-columns: repeat(2, minmax(0, 1fr));
        }

        .announcement-item-top {
            align-items: flex-start;
            flex-direction: column;
        }

        .announcement-actions {
            width: 100%;
            justify-content: flex-start;
        }
    }

    html[data-theme="dark"] .announcement-hero,
    html[data-theme="dark"] .announcement-card,
    html[data-theme="dark"] .announcement-stat-card,
    html[data-theme="dark"] .announcement-item,
    html[data-theme="dark"] .announcement-input,
    html[data-theme="dark"] .announcement-select,
    html[data-theme="dark"] .announcement-editor,
    html[data-theme="dark"] .announcement-textarea {
        background: rgba(15, 23, 42, 0.94);
        border-color: rgba(250, 204, 21, 0.16);
        color: #ffffff;
    }

    html[data-theme="dark"] .announcement-hero h2,
    html[data-theme="dark"] .announcement-last-updated strong,
    html[data-theme="dark"] .announcement-label,
    html[data-theme="dark"] .announcement-item h3,
    html[data-theme="dark"] .announcement-empty-state h3 {
        color: #ffffff;
    }

    html[data-theme="dark"] .announcement-hero p,
    html[data-theme="dark"] .announcement-last-updated span,
    html[data-theme="dark"] .announcement-message,
    html[data-theme="dark"] .announcement-time,
    html[data-theme="dark"] .announcement-meta-row,
    html[data-theme="dark"] .announcement-empty-state p {
        color: #cbd5e1;
    }

    html[data-theme="dark"] .announcement-toolbar {
        border-bottom-color: rgba(250, 204, 21, 0.12);
        color: #f8fafc;
    }

    html[data-theme="dark"] .announcement-stat-value {
        color: #ffffff;
    }

    html[data-theme="dark"] .announcement-stat-note {
        color: #cbd5e1;
    }

    .announcement-archive-modal {
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

    .announcement-archive-modal.is-open {
        display: flex;
    }

    .announcement-archive-shell {
        width: min(920px, 100%);
        max-height: calc(100vh - 40px);
        overflow: hidden;
        border-radius: 24px;
        background: linear-gradient(180deg, rgba(255,255,255,0.96), rgba(248,250,252,0.98));
        box-shadow: 0 26px 60px rgba(15, 23, 42, 0.24);
        border: 1px solid rgba(255,255,255,0.62);
        border-bottom: 4px solid #70131B;
    }

    .announcement-archive-head {
        display: flex;
        align-items: flex-start;
        justify-content: space-between;
        gap: 16px;
        padding: 18px 20px 14px;
        background: linear-gradient(135deg, #7f1d1d, #991b1b 55%, #b91c1c);
        color: #ffffff;
    }

    .announcement-archive-title {
        display: flex;
        align-items: flex-start;
        gap: 14px;
        min-width: 0;
    }

    .announcement-archive-badge {
        width: 46px;
        height: 46px;
        flex: 0 0 auto;
        border-radius: 14px;
        display: grid;
        place-items: center;
        background: rgba(255, 255, 255, 0.16);
        border: 1px solid rgba(255, 255, 255, 0.24);
        color: #ffffff;
        box-shadow: 0 12px 24px rgba(15, 23, 42, 0.16);
    }

    .announcement-archive-badge svg {
        width: 22px;
        height: 22px;
    }

    .announcement-archive-head h3 {
        margin: 0;
        font-size: 1rem;
        font-weight: 900;
        text-transform: uppercase;
        letter-spacing: 0.08em;
        color: #ffffff !important;
    }

    .announcement-archive-head p {
        margin: 6px 0 0;
        color: rgba(255, 255, 255, 0.92) !important;
        font-size: 12px;
        line-height: 1.55;
    }

    .announcement-archive-title,
    .announcement-archive-title h3,
    .announcement-archive-title p {
        color: #ffffff !important;
    }

    .announcement-archive-close {
        width: 40px;
        height: 40px;
        flex: 0 0 40px;
        border-radius: 999px;
        border: 1px solid #8f2230;
        background: linear-gradient(135deg, #70131B, #8f2230);
        color: #ffffff;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        cursor: pointer;
        transition: transform .18s ease, box-shadow .18s ease, border-color .18s ease;
    }

    .announcement-archive-close:hover,
    .announcement-archive-close:focus-visible {
        transform: translateY(-1px);
        border-color: #facc15;
        box-shadow: 0 0 0 3px rgba(250, 204, 21, 0.18), 0 14px 24px rgba(112, 19, 27, 0.16);
        outline: none;
    }

    .announcement-archive-close svg {
        width: 18px;
        height: 18px;
    }

    .announcement-archive-body {
        max-height: calc(100vh - 142px);
        overflow-y: auto;
        padding: 18px;
    }

    .announcement-archive-summary {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 14px;
        margin-bottom: 14px;
        padding: 14px 16px;
        border-radius: 16px;
        border: 1px solid rgba(112, 19, 27, 0.13);
        background: linear-gradient(180deg, #ffffff, #fff8f6);
        color: #475569;
        font-size: 13px;
        font-weight: 800;
    }

    .announcement-archive-count {
        display: inline-flex;
        align-items: center;
        min-height: 30px;
        padding: 6px 12px;
        border-radius: 999px;
        background: #fff1f2;
        color: #8b0000;
        font-size: 12px;
        font-weight: 950;
        white-space: nowrap;
    }

    .announcement-archive-list {
        display: grid;
        gap: 12px;
    }

    .announcement-archive-item {
        border-radius: 16px;
        border: 1px solid rgba(148, 163, 184, 0.18);
        background: rgba(255,255,255,0.92);
        padding: 16px;
        box-shadow: 0 12px 24px rgba(15, 23, 42, 0.05);
    }

    .announcement-archive-item h4 {
        margin: 10px 0 7px;
        color: #0f172a;
        font-size: 15px;
        font-weight: 900;
    }

    .announcement-archive-item p {
        margin: 0;
        color: #475569;
        font-size: 13px;
        line-height: 1.6;
    }

    .announcement-archive-meta {
        display: flex;
        flex-wrap: wrap;
        gap: 10px;
        margin-top: 12px;
        color: #64748b;
        font-size: 12px;
        font-weight: 800;
    }

    .announcement-archive-empty {
        min-height: 180px;
        display: grid;
        place-items: center;
        text-align: center;
        border-radius: 16px;
        border: 1px dashed rgba(112, 19, 27, 0.22);
        background: #fffafa;
        color: #475569;
        padding: 22px;
    }

    .announcement-archive-empty h4 {
        margin: 0 0 6px;
        color: #0f172a;
        font-size: 16px;
        font-weight: 900;
    }

    .announcement-archive-empty p {
        margin: 0;
        font-size: 13px;
        line-height: 1.55;
    }

    @media (max-width: 640px) {
        .announcement-archive-head {
            align-items: flex-start;
        }

        .announcement-archive-summary {
            align-items: flex-start;
            flex-direction: column;
        }
    }

    /* Final light-mode surface pass */
    html:not([data-theme="dark"]) .announcement-stat-card,
    html:not([data-theme="dark"]) .announcement-card,
    html:not([data-theme="dark"]) .announcement-archive-shell,
    html:not([data-theme="dark"]) .announcement-archive-item {
        border: 1px solid rgba(250, 204, 21, 0.22) !important;
        box-shadow: 0 14px 28px rgba(112, 19, 27, 0.12), 0 4px 12px rgba(15, 23, 42, 0.06) !important;
    }

    html:not([data-theme="dark"]) .announcement-delete,
    html:not([data-theme="dark"]) .announcement-archive,
    html:not([data-theme="dark"]) .announcement-archive-close,
    html:not([data-theme="dark"]) .announcement-submit,
    html:not([data-theme="dark"]) .announcement-form-submit,
    html:not([data-theme="dark"]) .announcement-publish,
    html:not([data-theme="dark"]) .announcement-notification,
    html:not([data-theme="dark"]) .send-notification {
        border: 1px solid rgba(250, 204, 21, 0.22) !important;
        box-shadow: 0 12px 24px rgba(112, 19, 27, 0.10), 0 3px 10px rgba(15, 23, 42, 0.05) !important;
    }
</style>
@endpush

@section('content')
@php
    $priorityLabels = [
        'urgent' => 'Urgent',
        'info' => 'Info',
        'warning' => 'Warning',
        'health' => 'Health',
        'event' => 'Events',
    ];

    $lastUpdated = $lastUpdatedAnnouncement
        ? \Carbon\Carbon::parse($lastUpdatedAnnouncement)
        : now();

    $renderAnnouncementMessage = function ($message) {
        $formatInline = function ($line) {
            $line = e($line);
            $line = preg_replace('/\*\*(.+?)\*\*/s', '<strong>$1</strong>', $line);
            $line = preg_replace('/(?<!\*)\*(?!\*)(.+?)(?<!\*)\*(?!\*)/s', '<em>$1</em>', $line);

            return $line;
        };
        $lines = preg_split('/\r\n|\r|\n/', trim((string) $message));
        $html = '';
        $paragraph = [];
        $inList = false;
        $flushParagraph = function () use (&$html, &$paragraph, $formatInline) {
            if ($paragraph === []) {
                return;
            }

            $html .= '<p>' . implode('<br>', array_map($formatInline, $paragraph)) . '</p>';
            $paragraph = [];
        };

        foreach ($lines as $line) {
            if (trim($line) === '') {
                $flushParagraph();
                if ($inList) {
                    $html .= '</ul>';
                    $inList = false;
                }
                continue;
            }

            if (preg_match('/^\s*[-*]\s+(.+)$/', $line, $matches)) {
                $flushParagraph();
                if (! $inList) {
                    $html .= '<ul>';
                    $inList = true;
                }
                $html .= '<li>' . $formatInline($matches[1]) . '</li>';
                continue;
            }

            if ($inList) {
                $html .= '</ul>';
                $inList = false;
            }
            $paragraph[] = $line;
        }

        $flushParagraph();
        if ($inList) {
            $html .= '</ul>';
        }

        return $html;
    };

    $archivedAnnouncements = $announcements->where('status', \App\Models\Announcement::STATUS_ARCHIVED);
    $activeBulletins = $announcements->reject(fn ($announcement) => $announcement->status === \App\Models\Announcement::STATUS_ARCHIVED);
@endphp

<div class="announcement-page">
    <section class="announcement-hero">
        <div class="announcement-hero-head">
            <div class="announcement-title-block">
                <span class="announcement-hero-icon" aria-hidden="true">
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.7" d="M10.34 15.84c-.688-.06-1.386-.09-2.09-.09H7.5a4.5 4.5 0 1 1 0-9h.75c.704 0 1.402-.03 2.09-.09m0 9.18c.253.962.584 1.892.985 2.783.247.55.06 1.21-.463 1.511l-.657.38c-.551.318-1.26.117-1.527-.461a20.845 20.845 0 0 1-1.44-4.282m3.102.069a18.03 18.03 0 0 1-.59-4.59c0-1.586.205-3.124.59-4.59m0 9.18a23.848 23.848 0 0 1 8.835 2.535M10.34 6.66a23.847 23.847 0 0 0 8.835-2.535m0 0A23.74 23.74 0 0 0 18.795 3m.38 1.125a23.91 23.91 0 0 1 1.014 5.395m-1.014 8.855c-.118.38-.245.754-.38 1.125m.38-1.125a23.91 23.91 0 0 0 1.014-5.395m0-3.46c.495.413.811 1.035.811 1.73 0 .695-.316 1.317-.811 1.73m0-3.46a24.347 24.347 0 0 1 0 3.46" />
                    </svg>
                </span>
                <div>
                    <h2>Announcements & Bulletin Manager</h2>
                    <p>Create and publish health advisories, schedule updates, and system notices.</p>
                </div>
            </div>
            <div class="announcement-last-updated">
                <span class="announcement-last-updated-icon"><x-outline-icon name="clock" /></span>
                <div>
                    <span>Last Updated</span>
                    <strong>{{ $lastUpdated->format('M j, Y') }}<br>{{ $lastUpdated->format('g:i A') }}</strong>
                </div>
            </div>
        </div>

        <div class="announcement-stat-grid">
            <article class="announcement-stat-card">
                <span class="announcement-stat-icon"><x-outline-icon name="check" /></span>
                <span>
                    <span class="announcement-stat-label">Total Active</span>
                    <span class="announcement-stat-value">{{ number_format($announcementStats['active'] ?? 0) }}</span>
                    <span class="announcement-stat-note">Published bulletins</span>
                </span>
            </article>
            <article class="announcement-stat-card is-urgent">
                <span class="announcement-stat-icon"><x-outline-icon name="exclamation-triangle" /></span>
                <span>
                    <span class="announcement-stat-label">Urgent</span>
                    <span class="announcement-stat-value">{{ number_format($announcementStats['urgent'] ?? 0) }}</span>
                    <span class="announcement-stat-note">Needs attention</span>
                </span>
            </article>
            <article class="announcement-stat-card is-warning">
                <span class="announcement-stat-icon"><x-outline-icon name="calendar-days" /></span>
                <span>
                    <span class="announcement-stat-label">Scheduled</span>
                    <span class="announcement-stat-value">{{ number_format($announcementStats['scheduled'] ?? 0) }}</span>
                    <span class="announcement-stat-note">With expiry dates</span>
                </span>
            </article>
            <button type="button" class="announcement-stat-card announcement-stat-button is-archived" id="openArchivedAnnouncements" aria-haspopup="dialog" aria-controls="archivedAnnouncementsModal">
                <span class="announcement-stat-icon">
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="m20.25 7.5-.625 10.632a2.25 2.25 0 0 1-2.247 2.118H6.622a2.25 2.25 0 0 1-2.247-2.118L3.75 7.5M10 11.25h4M3.375 7.5h17.25c.621 0 1.125-.504 1.125-1.125v-1.5c0-.621-.504-1.125-1.125-1.125H3.375c-.621 0-1.125.504-1.125 1.125v1.5c0 .621.504 1.125 1.125 1.125Z" />
                    </svg>
                </span>
                <span>
                    <span class="announcement-stat-label">Archived</span>
                    <span class="announcement-stat-value">{{ number_format($announcementStats['archived'] ?? 0) }}</span>
                    <span class="announcement-stat-note">Past bulletins</span>
                </span>
            </button>
        </div>
    </section>

    @if(session('success'))
        <div class="announcement-alert">{{ session('success') }}</div>
    @endif

    @if($errors->any())
        <div class="announcement-error">{{ $errors->first() }}</div>
    @endif

    <div class="announcement-grid">
        <section class="announcement-card">
            <div class="announcement-card-head">
                <h3 class="announcement-card-title">
                    <span class="announcement-form-icon" aria-hidden="true">
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="m16.862 4.487 1.687-1.688a1.875 1.875 0 1 1 2.652 2.652L10.582 16.07a4.5 4.5 0 0 1-1.897 1.13L6 18l.8-2.685a4.5 4.5 0 0 1 1.13-1.897l8.932-8.931Z" />
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M19.5 7.125 16.875 4.5M18 14v4.75A2.25 2.25 0 0 1 15.75 21h-10.5A2.25 2.25 0 0 1 3 18.75v-10.5A2.25 2.25 0 0 1 5.25 6H10" />
                        </svg>
                    </span>
                    Create New Announcement
                </h3>
            </div>

            <form method="POST" action="{{ route('admin.announcements.store') }}" class="announcement-form">
                @csrf

                <label class="announcement-field">
                    <span class="announcement-label">Title</span>
                    <input class="announcement-input" type="text" name="title" value="{{ old('title') }}" maxlength="140" placeholder="e.g., Extended Clinic Hours" required>
                </label>

                <label class="announcement-field">
                    <span class="announcement-label">Priority Level</span>
                    <select class="announcement-select" name="priority" required>
                        @foreach($priorityLabels as $value => $label)
                            <option value="{{ $value }}" @selected(old('priority', 'urgent') === $value)>{{ $label }}</option>
                        @endforeach
                    </select>
                </label>

                <label class="announcement-field">
                    <span class="announcement-label">Message Content</span>
                    <div class="announcement-editor">
                        <div class="announcement-toolbar" aria-label="Message formatting tools">
                            <button type="button" class="announcement-tool" data-wrap-before="**" data-wrap-after="**" title="Bold">B</button>
                            <button type="button" class="announcement-tool" data-wrap-before="*" data-wrap-after="*" title="Italic"><em>I</em></button>
                            <button type="button" class="announcement-tool" data-prefix="- " title="Bulleted line">&bull;</button>
                        </div>
                        <textarea class="announcement-textarea" name="message" maxlength="2000" placeholder="Write the details of the update here..." required data-announcement-message>{{ old('message') }}</textarea>
                    </div>
                    <span class="announcement-counter" data-announcement-counter>0 / 2000</span>
                </label>

                <label class="announcement-field">
                    <span class="announcement-label">Expiration Date (Optional)</span>
                    <input class="announcement-input" type="date" name="expires_at" value="{{ old('expires_at') }}">
                </label>

                <button type="submit" class="announcement-submit">
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="m6 12 13.5-8.25L15.75 21 12 14.25 6 12Z" />
                    </svg>
                    Publish & Send Notification
                </button>
            </form>
        </section>

        <section class="announcement-card">
            <div class="announcement-card-head">
                <h3 class="announcement-card-title">Active Bulletins</h3>
            </div>

            <div class="announcement-list">
                @forelse($activeBulletins as $announcement)
                    @php
                        $priority = $announcement->priority ?: 'info';
                        $priorityLabel = $priorityLabels[$priority] ?? ucfirst($priority);
                        $isArchived = $announcement->status === \App\Models\Announcement::STATUS_ARCHIVED;
                        $isExpired = $announcement->is_expired;
                        $statusLabel = $isArchived ? 'Archived' : ($isExpired ? 'Expired' : 'Active');
                        $statusClass = $isArchived ? 'is-archived' : ($isExpired ? 'is-expired' : '');
                    @endphp
                    <article class="announcement-item priority-{{ $priority }}">
                        <div class="announcement-item-top">
                            <div class="announcement-meta-row">
                                <span class="announcement-priority">
                                    <span class="announcement-priority-dot"></span>
                                    {{ $priorityLabel }}
                                </span>
                                <span class="announcement-time">&middot; {{ $announcement->created_at?->diffForHumans() ?? 'Just now' }}</span>
                            </div>
                            <div class="announcement-actions">
                                <span class="announcement-status {{ $statusClass }}">{{ $statusLabel }}</span>
                                @if(! $isArchived)
                                    <form method="POST" action="{{ route('admin.announcements.archive', $announcement) }}">
                                        @csrf
                                        @method('PATCH')
                                        <button class="announcement-archive" type="submit" aria-label="Archive announcement">
                                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="m20.25 7.5-.625 10.632a2.25 2.25 0 0 1-2.247 2.118H6.622a2.25 2.25 0 0 1-2.247-2.118L3.75 7.5M10 11.25h4M3.375 7.5h17.25c.621 0 1.125-.504 1.125-1.125v-1.5c0-.621-.504-1.125-1.125-1.125H3.375c-.621 0-1.125.504-1.125 1.125v1.5c0 .621.504 1.125 1.125 1.125Z" />
                                            </svg>
                                        </button>
                                    </form>
                                @endif
                                <form method="POST" action="{{ route('admin.announcements.destroy', $announcement) }}" onsubmit="return confirm('Delete this announcement?');">
                                    @csrf
                                    @method('DELETE')
                                    <button class="announcement-delete" type="submit" aria-label="Delete announcement">
                                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="m14.74 9-.346 9m-4.788 0L9.26 9m9.968-3.21c.342.052.682.107 1.022.166M19.228 5.79 18.16 19.673A2.25 2.25 0 0 1 15.916 21H8.084a2.25 2.25 0 0 1-2.244-2.077L4.772 5.79m14.456 0a48.108 48.108 0 0 0-3.478-.397m-12 .563c.34-.059.68-.114 1.022-.165m0 0a48.11 48.11 0 0 1 3.478-.397m7.5 0v-.916c0-1.18-.91-2.164-2.09-2.201a51.964 51.964 0 0 0-3.32 0c-1.18.037-2.09 1.022-2.09 2.201v.916m7.5 0a48.667 48.667 0 0 0-7.5 0" />
                                        </svg>
                                    </button>
                                </form>
                            </div>
                        </div>

                        <h3>{{ $announcement->title }}</h3>
                        <div class="announcement-message">{!! $renderAnnouncementMessage(\Illuminate\Support\Str::limit($announcement->message, 145)) !!}</div>

                        <div class="announcement-meta-row">
                            <span>Expires: {{ $announcement->expires_at ? $announcement->expires_at->format('M j, Y') : 'Never' }}</span>
                        </div>
                    </article>
                @empty
                    <div class="announcement-empty-state">
                        <div>
                            <h3>No bulletins yet</h3>
                            <p>Create the first clinic announcement using the form.</p>
                        </div>
                    </div>
                @endforelse
            </div>
        </section>
    </div>

    <div class="announcement-archive-modal" id="archivedAnnouncementsModal" role="dialog" aria-modal="true" aria-labelledby="archivedAnnouncementsTitle" aria-hidden="true">
        <div class="announcement-archive-shell" role="document">
            <div class="announcement-archive-head">
                <div class="announcement-archive-title">
                    <span class="announcement-archive-badge" aria-hidden="true">
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="m20.25 7.5-.625 10.632a2.25 2.25 0 0 1-2.247 2.118H6.622a2.25 2.25 0 0 1-2.247-2.118L3.75 7.5M10 11.25h4M3.375 7.5h17.25c.621 0 1.125-.504 1.125-1.125v-1.5c0-.621-.504-1.125-1.125-1.125H3.375c-.621 0-1.125.504-1.125 1.125v1.5c0 .621.504 1.125 1.125 1.125Z" />
                        </svg>
                    </span>
                    <div>
                        <h3 id="archivedAnnouncementsTitle">Archived Announcements</h3>
                        <p>Review past clinic bulletins, advisories, and system notices that are no longer active.</p>
                    </div>
                </div>
                <button type="button" class="announcement-archive-close" id="closeArchivedAnnouncements" aria-label="Close archived announcements">
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18 18 6M6 6l12 12" />
                    </svg>
                </button>
            </div>

            <div class="announcement-archive-body">
                <div class="announcement-archive-summary">
                    <span>Archived bulletins are kept here for clinic record review.</span>
                    <span class="announcement-archive-count">{{ number_format($archivedAnnouncements->count()) }} archived</span>
                </div>

                @if($archivedAnnouncements->isNotEmpty())
                    <div class="announcement-archive-list">
                        @foreach($archivedAnnouncements as $announcement)
                            @php
                                $priority = $announcement->priority ?: 'info';
                                $priorityLabel = $priorityLabels[$priority] ?? ucfirst($priority);
                            @endphp
                            <article class="announcement-archive-item">
                                <div class="announcement-item-top">
                                    <div class="announcement-meta-row">
                                        <span class="announcement-priority">
                                            <span class="announcement-priority-dot"></span>
                                            {{ $priorityLabel }}
                                        </span>
                                        <span class="announcement-time">&middot; Archived {{ $announcement->updated_at?->diffForHumans() ?? 'recently' }}</span>
                                    </div>
                                    <span class="announcement-status is-archived">Archived</span>
                                </div>
                                <h4>{{ $announcement->title }}</h4>
                                <div class="announcement-message">{!! $renderAnnouncementMessage(\Illuminate\Support\Str::limit($announcement->message, 190)) !!}</div>
                                <div class="announcement-archive-meta">
                                    <span>Published: {{ $announcement->created_at ? $announcement->created_at->format('M j, Y') : 'N/A' }}</span>
                                    <span>Expires: {{ $announcement->expires_at ? $announcement->expires_at->format('M j, Y') : 'Never' }}</span>
                                </div>
                            </article>
                        @endforeach
                    </div>
                @else
                    <div class="announcement-archive-empty">
                        <div>
                            <h4>No archived announcements yet</h4>
                            <p>Archived clinic bulletins will appear here once an active announcement is moved out of circulation.</p>
                        </div>
                    </div>
                @endif
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function () {
        const message = document.querySelector('[data-announcement-message]');
        const counter = document.querySelector('[data-announcement-counter]');
        const tools = document.querySelectorAll('.announcement-tool');
        const archiveButton = document.getElementById('openArchivedAnnouncements');
        const archiveModal = document.getElementById('archivedAnnouncementsModal');
        const archiveClose = document.getElementById('closeArchivedAnnouncements');

        const openArchiveModal = () => {
            if (!archiveModal) {
                return;
            }

            archiveModal.classList.add('is-open');
            archiveModal.setAttribute('aria-hidden', 'false');
            document.body.style.overflow = 'hidden';
            archiveClose?.focus();
        };

        const closeArchiveModal = () => {
            if (!archiveModal) {
                return;
            }

            archiveModal.classList.remove('is-open');
            archiveModal.setAttribute('aria-hidden', 'true');
            document.body.style.overflow = '';
            archiveButton?.focus();
        };

        archiveButton?.addEventListener('click', openArchiveModal);
        archiveClose?.addEventListener('click', closeArchiveModal);
        archiveModal?.addEventListener('click', function (event) {
            if (event.target === archiveModal) {
                closeArchiveModal();
            }
        });

        document.addEventListener('keydown', function (event) {
            if (event.key === 'Escape' && archiveModal?.classList.contains('is-open')) {
                closeArchiveModal();
            }
        });

        if (!message || !counter) {
            return;
        }

        const syncCounter = () => {
            counter.textContent = `${message.value.length} / ${message.maxLength || 2000}`;
        };

        message.addEventListener('input', syncCounter);
        syncCounter();

        const applyFormat = (tool) => {
            const start = message.selectionStart ?? 0;
            const end = message.selectionEnd ?? start;
            const value = message.value;
            const selected = value.slice(start, end);
            const prefix = tool.dataset.prefix;
            const before = tool.dataset.wrapBefore || '';
            const after = tool.dataset.wrapAfter || '';
            let nextValue;
            let nextStart;
            let nextEnd;

            if (prefix) {
                const lineStart = value.lastIndexOf('\n', Math.max(0, start - 1)) + 1;
                const selectionEnd = end > start && value.charAt(end - 1) === '\n' ? end - 1 : end;
                const lineEnd = value.indexOf('\n', selectionEnd);
                const blockEnd = lineEnd === -1 ? value.length : lineEnd;
                const block = value.slice(lineStart, blockEnd);
                const formattedBlock = block
                    .split('\n')
                    .map((line) => line.trim() === '' || line.startsWith(prefix) ? line : `${prefix}${line}`)
                    .join('\n');
                nextValue = value.slice(0, lineStart) + formattedBlock + value.slice(blockEnd);
                const addedLength = formattedBlock.length - block.length;
                nextStart = start + (start === lineStart ? prefix.length : 0);
                nextEnd = end + addedLength;
            } else {
                nextValue = value.slice(0, start) + before + selected + after + value.slice(end);
                nextStart = start + before.length;
                nextEnd = nextStart + selected.length;
            }

            message.value = nextValue.slice(0, Number(message.maxLength || 2000));
            message.focus();
            message.setSelectionRange(nextStart, nextEnd);
            syncCounter();
        };

        tools.forEach((tool) => {
            tool.addEventListener('click', () => applyFormat(tool));
        });
    });
</script>
@endpush

