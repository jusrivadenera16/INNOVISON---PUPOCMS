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

    .announcement-rich-editor {
        overflow-y: auto;
        resize: vertical;
        white-space: pre-wrap;
        word-break: break-word;
        font-weight: 400;
    }

    .announcement-rich-editor:empty::before {
        content: attr(data-placeholder);
        color: #94a3b8;
        pointer-events: none;
    }

    .announcement-rich-editor p,
    .announcement-rich-editor ul {
        margin: 0;
    }

    .announcement-rich-editor ul {
        padding-left: 20px;
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

    .announcement-tool.is-active,
    .announcement-tool[aria-pressed="true"] {
        border-color: #70131B;
        background: #70131B;
        color: #ffffff;
        box-shadow: 0 4px 10px rgba(112, 19, 27, 0.18);
    }

    .announcement-tool svg {
        width: 16px;
        height: 16px;
        stroke-width: 2;
    }

    .announcement-tool[data-announcement-link-tool] {
        width: 25px;
        height: 25px;
        margin-left: -2px;
    }

    .announcement-tool[data-announcement-link-tool] svg {
        width: 13px;
        height: 13px;
    }

    .announcement-link-popover {
        display: grid;
        grid-template-columns: minmax(0, 1fr) auto auto;
        gap: 7px;
        padding: 8px 10px;
        border-bottom: 1px solid rgba(148, 163, 184, 0.2);
        background: #fff8f8;
    }

    .announcement-link-popover[hidden] { display: none; }

    .announcement-link-popover input {
        min-width: 0;
        height: 34px;
        padding: 0 10px;
        border: 1px solid rgba(112, 19, 27, 0.22);
        border-radius: 6px;
        color: #1f2937;
        background: #ffffff;
        font: inherit;
        font-size: 12px;
        font-weight: 700;
    }

    .announcement-link-apply,
    .announcement-link-cancel {
        min-height: 34px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        border-radius: 6px;
        cursor: pointer;
        font: inherit;
        font-size: 12px;
        font-weight: 900;
        transition: background .18s ease, color .18s ease, border-color .18s ease;
    }

    .announcement-link-apply {
        padding: 0 12px;
        border: 1px solid #70131B;
        color: #ffffff;
        background: #70131B;
    }

    .announcement-link-cancel {
        width: 34px;
        padding: 0;
        border: 1px solid rgba(112, 19, 27, 0.16);
        color: #70131B;
        background: #ffffff;
    }

    .announcement-link-cancel svg {
        width: 15px;
        height: 15px;
    }

    .announcement-link-apply:hover,
    .announcement-link-apply:focus-visible,
    .announcement-link-cancel:hover,
    .announcement-link-cancel:focus-visible {
        border-color: #f1bd00;
        color: #690014;
        background: #ffd21f;
        outline: none;
    }

    .announcement-link-feedback {
        grid-column: 1 / -1;
        margin: 0;
        color: #9b1225;
        font-size: 11px;
        font-weight: 800;
    }

    .announcement-link-feedback[hidden] { display: none; }

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

    .announcement-image-input {
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

    .announcement-image-controls {
        min-height: 52px;
        display: flex;
        align-items: center;
        gap: 8px;
        padding: 8px 10px;
        border: 1px dashed rgba(112, 19, 27, 0.34);
        border-radius: 8px;
        background: #fff8f8;
    }

    .announcement-image-add,
    .announcement-image-clear {
        min-height: 34px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        gap: 7px;
        border-radius: 6px;
        font: inherit;
        font-size: 12px;
        font-weight: 900;
        cursor: pointer;
        transition: background .18s ease, color .18s ease, border-color .18s ease, transform .18s ease;
    }

    .announcement-image-add {
        padding: 7px 11px;
        border: 1px solid transparent;
        color: #70131B;
        background: #fde9ed;
    }

    .announcement-image-add svg,
    .announcement-image-clear svg {
        width: 15px;
        height: 15px;
        stroke-width: 2;
    }

    .announcement-image-add:hover,
    .announcement-image-add:focus-visible,
    .announcement-image-clear:hover,
    .announcement-image-clear:focus-visible {
        border-color: #f1bd00;
        background: #ffd21f;
        color: #690014;
        outline: none;
        transform: translateY(-1px);
    }

    .announcement-image-clear {
        width: 34px;
        padding: 0;
        margin-left: auto;
        border: 1px solid rgba(112, 19, 27, 0.16);
        color: #9b1225;
        background: #ffffff;
    }

    .announcement-image-clear[hidden] { display: none; }

    .announcement-image-note {
        color: #64748b;
        font-size: 11px;
        font-weight: 700;
    }

    .announcement-image-feedback {
        min-height: 16px;
        color: #9b1225;
        font-size: 11px;
        font-weight: 800;
    }

    .announcement-image-feedback[hidden] { display: none; }

    .announcement-visibility-field {
        display: grid;
        gap: 10px;
        padding: 12px 14px;
        border: 1px solid rgba(112, 19, 27, 0.16);
        border-radius: 8px;
        background: #fffafa;
    }

    .announcement-visibility-title {
        color: #70131B;
        font-size: 13px;
        font-weight: 950;
    }

    .announcement-visibility-options {
        display: flex;
        flex-wrap: wrap;
        gap: 18px;
    }

    .announcement-visibility-toggle {
        display: inline-flex;
        align-items: center;
        gap: 8px;
        flex: 0 0 auto;
        color: #334155;
        font-size: 12px;
        font-weight: 900;
        cursor: pointer;
    }

    .announcement-visibility-toggle input {
        width: 17px;
        height: 17px;
        margin: 0;
        accent-color: #70131B;
    }

    .announcement-image-preview {
        display: none;
        grid-template-columns: repeat(3, minmax(0, 1fr));
        gap: 8px;
        border: 1px solid rgba(112, 19, 27, 0.14);
        border-radius: 8px;
        background: #f8fafc;
        padding: 8px;
    }

    .announcement-image-preview.is-visible { display: grid; }
    .announcement-image-preview-card {
        position: relative;
        min-width: 0;
    }

    .announcement-image-preview img {
        display: block;
        width: 100%;
        height: 96px;
        border-radius: 6px;
        object-fit: cover;
    }

    .announcement-image-remove {
        position: absolute;
        top: 5px;
        right: 5px;
        width: 26px;
        height: 26px;
        display: grid;
        place-items: center;
        padding: 0;
        border: 1px solid rgba(255, 255, 255, 0.72);
        border-radius: 50%;
        color: #ffffff;
        background: rgba(112, 19, 27, 0.92);
        cursor: pointer;
        box-shadow: 0 3px 8px rgba(36, 8, 12, 0.22);
        transition: background .18s ease, color .18s ease, transform .18s ease;
    }

    .announcement-image-remove svg {
        width: 14px;
        height: 14px;
        stroke-width: 2.4;
    }

    .announcement-image-remove:hover,
    .announcement-image-remove:focus-visible {
        background: #ffd21f;
        color: #690014;
        outline: none;
        transform: scale(1.06);
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

    [data-active-bulletins-panel] {
        transition: opacity .18s ease;
    }

    [data-active-bulletins-panel].is-loading {
        pointer-events: none;
        opacity: .54;
    }

    .announcement-pagination {
        display: grid;
        grid-template-columns: minmax(0, 1fr) auto minmax(0, 1fr);
        align-items: center;
        gap: 16px;
        margin-top: 16px;
        padding: 14px 18px;
        border: 1px solid #edf2f7;
        border-radius: 12px;
        background: #ffffff;
        box-shadow: 0 10px 24px rgba(15, 23, 42, 0.04);
    }

    .announcement-pagination-summary {
        color: #64748b;
        font-size: 12px;
        font-weight: 900;
    }

    .announcement-pagination-actions {
        display: flex;
        align-items: center;
        justify-content: center;
        gap: 8px;
    }

    .announcement-pagination-control {
        min-width: 34px;
        min-height: 34px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        padding: 0 10px;
        border: 1px solid #e2e8f0;
        border-radius: 8px;
        color: #334155;
        background: #ffffff;
        font-size: 12px;
        font-weight: 900;
        line-height: 1;
        text-decoration: none;
        transition: background-color .2s ease, border-color .2s ease, color .2s ease, transform .2s ease, box-shadow .2s ease;
    }

    .announcement-pagination-control:not(.is-disabled):hover,
    .announcement-pagination-control:not(.is-disabled):focus-visible {
        border-color: #f8cfd4;
        background: #fff7ed;
        color: #70131B;
        outline: none;
        transform: translateY(-2px) scale(1.04);
        box-shadow: 0 10px 20px rgba(112, 19, 27, .10);
    }

    .announcement-pagination-control.is-active,
    .announcement-pagination-control.is-active:hover {
        border-color: #7f0010;
        color: #ffffff;
        background: #7f0010;
        box-shadow: 0 12px 24px rgba(127, 0, 16, 0.18);
        transform: translateY(-1px);
    }

    .announcement-pagination-control.is-disabled {
        color: #94a3b8;
        background: #f8fafc;
        cursor: not-allowed;
        opacity: .45;
    }

    .announcement-pagination-spacer {
        min-height: 1px;
    }

    @media (max-width: 640px) {
        .announcement-pagination {
            grid-template-columns: 1fr;
            justify-items: center;
        }

        .announcement-pagination-summary {
            text-align: center;
        }

        .announcement-pagination-spacer {
            display: none;
        }
    }

    .announcement-item {
        position: relative;
        display: grid;
        grid-template-rows: auto auto minmax(0, 1fr) auto;
        gap: 12px;
        height: 224px;
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
    .announcement-archive,
    .announcement-view,
    .announcement-edit,
    .announcement-restore {
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

    .announcement-edit {
        border: 1px solid rgba(100, 116, 139, 0.24);
        background: #f8fafc;
        color: #475569;
        box-shadow: 0 12px 24px rgba(112, 19, 27, 0.10), 0 3px 10px rgba(15, 23, 42, 0.05);
    }

    .announcement-view {
        border: 1px solid rgba(37, 99, 235, 0.2);
        background: #eff6ff;
        color: #1d4ed8;
    }

    .announcement-restore {
        border: 1px solid rgba(100, 116, 139, 0.24);
        background: #f8fafc;
        color: #475569;
    }

    .announcement-delete:hover,
    .announcement-delete:focus-visible,
    .announcement-archive:hover,
    .announcement-archive:focus-visible,
    .announcement-view:hover,
    .announcement-view:focus-visible,
    .announcement-edit:hover,
    .announcement-edit:focus-visible,
    .announcement-restore:hover,
    .announcement-restore:focus-visible {
        background: #70131B;
        color: #ffffff;
        outline: none;
    }

    .announcement-delete svg,
    .announcement-archive svg,
    .announcement-view svg,
    .announcement-edit svg,
    .announcement-restore svg {
        width: 17px;
        height: 17px;
    }

    .announcement-item h3 {
        margin: 0;
        color: #111827;
        font-size: 17px;
        font-weight: 950;
        line-height: 1.25;
        display: -webkit-box;
        -webkit-box-orient: vertical;
        -webkit-line-clamp: 2;
        overflow: hidden;
    }

    .announcement-preview {
        overflow: hidden;
        color: #475569;
        font-size: 13px;
        font-weight: 700;
        line-height: 1.55;
        display: -webkit-box;
        -webkit-box-orient: vertical;
        -webkit-line-clamp: 3;
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

    .announcement-item-image {
        display: block;
        width: 100%;
        max-height: 200px;
        border: 1px solid rgba(112, 19, 27, 0.12);
        border-radius: 8px;
        object-fit: cover;
    }

    .announcement-item-image-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(120px, 1fr));
        gap: 8px;
    }

    .announcement-detail-meta {
        display: flex;
        flex-wrap: wrap;
        gap: 8px 14px;
        margin-bottom: 18px;
        color: #64748b;
        font-size: 12px;
        font-weight: 800;
    }

    .announcement-detail-body h4 {
        margin: 0 0 12px;
        color: #111827;
        font-size: 20px;
        font-weight: 950;
        line-height: 1.3;
    }

    .announcement-detail-message {
        color: #334155;
        font-size: 14px;
        font-weight: 650;
        line-height: 1.65;
    }

    .announcement-detail-message p,
    .announcement-detail-message ul {
        margin: 0 0 12px;
    }

    .announcement-detail-message p:last-child,
    .announcement-detail-message ul:last-child { margin-bottom: 0; }
    .announcement-detail-message ul { padding-left: 22px; }

    .announcement-detail-images {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(180px, 1fr));
        gap: 10px;
        margin-top: 20px;
    }

    .announcement-detail-images[hidden] { display: none; }

    .announcement-detail-image-card {
        position: relative;
        overflow: hidden;
        border-radius: 8px;
    }

    .announcement-detail-image-button {
        display: block;
        width: 100%;
        padding: 0;
        border: 0;
        background: transparent;
        cursor: pointer;
    }

    .announcement-detail-image-button img {
        display: block;
        width: 100%;
        max-height: 300px;
        border: 1px solid rgba(112, 19, 27, 0.14);
        border-radius: 8px;
        object-fit: contain;
        background: #f8fafc;
    }

    .announcement-detail-image-open {
        position: absolute;
        inset: 0;
        display: grid;
        place-items: center;
        opacity: 0;
        pointer-events: none;
        background: rgba(15, 23, 42, 0.58);
        color: #690014;
        text-decoration: none;
        transition: opacity .18s ease;
    }

    .announcement-detail-image-open span {
        min-height: 34px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        padding: 0 14px;
        border-radius: 6px;
        background: #ffd21f;
        font-size: 12px;
        font-weight: 950;
    }

    .announcement-detail-image-card.is-open .announcement-detail-image-open {
        opacity: 1;
        pointer-events: auto;
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

    html[data-theme="dark"] .announcement-image-controls,
    html[data-theme="dark"] .announcement-image-preview {
        border-color: rgba(250, 204, 21, .22);
        color: #cbd5e1;
        background: #172033;
    }

    html[data-theme="dark"] .announcement-image-clear {
        border-color: rgba(250, 204, 21, .22);
        color: #fecaca;
        background: #1e293b;
    }

    html[data-theme="dark"] .announcement-link-popover,
    html[data-theme="dark"] .announcement-visibility-field {
        border-color: rgba(250, 204, 21, .2);
        background: #172033;
    }

    html[data-theme="dark"] .announcement-link-popover input,
    html[data-theme="dark"] .announcement-visibility-toggle {
        color: #e5e7eb;
        background: #1e293b;
    }

    html[data-theme="dark"] .announcement-visibility-title { color: #facc15; }

    html[data-theme="dark"] .announcement-rich-editor:empty::before {
        color: #94a3b8;
    }

    html[data-theme="dark"] .announcement-pagination {
        border-color: rgba(250, 204, 21, .18);
        background: rgba(17, 24, 39, .96);
        box-shadow: 0 12px 28px rgba(0, 0, 0, .26);
    }

    html[data-theme="dark"] .announcement-pagination-summary {
        color: #ffffff;
    }

    html[data-theme="dark"] .announcement-pagination-control {
        border-color: rgba(250, 204, 21, .18);
        color: #ffffff;
        background: rgba(15, 23, 42, .96);
    }

    html[data-theme="dark"] .announcement-pagination-control.is-active,
    html[data-theme="dark"] .announcement-pagination-control.is-active:hover {
        border-color: #facc15;
        color: #ffffff;
        background: #7f0010;
    }

    html[data-theme="dark"] .announcement-pagination-control:not(.is-disabled):hover {
        border-color: #facc15;
        color: #70131B;
        background: #facc15;
    }

    html[data-theme="dark"] .announcement-stat-value {
        color: #ffffff;
    }

    html[data-theme="dark"] .announcement-stat-note {
        color: #cbd5e1;
    }

    html[data-theme="dark"] .announcement-archive-shell,
    html[data-theme="dark"] .announcement-archive-item {
        background: #0f172a;
        border-color: rgba(250, 204, 21, 0.18);
        color: #e2e8f0;
    }

    html[data-theme="dark"] .announcement-archive-summary {
        background: #172033;
        border-color: rgba(250, 204, 21, 0.18);
        color: #e2e8f0;
    }

    html[data-theme="dark"] .announcement-archive-item h4,
    html[data-theme="dark"] .announcement-archive-empty h4 {
        color: #f8fafc;
    }

    html[data-theme="dark"] .announcement-archive-item p,
    html[data-theme="dark"] .announcement-archive-meta,
    html[data-theme="dark"] .announcement-archive-empty {
        color: #cbd5e1;
    }

    html[data-theme="dark"] .announcement-archive-empty {
        background: #172033;
        border-color: rgba(250, 204, 21, 0.22);
    }

    html[data-theme="dark"] .announcement-restore {
        border-color: rgba(250, 204, 21, 0.2);
        background: #1e293b;
        color: #cbd5e1;
    }

    html[data-theme="dark"] .announcement-edit {
        border-color: rgba(250, 204, 21, 0.2);
        background: #1e293b;
        color: #cbd5e1;
    }

    html[data-theme="dark"] .announcement-detail-meta {
        color: #cbd5e1;
    }

    html[data-theme="dark"] .announcement-detail-body h4,
    html[data-theme="dark"] .announcement-detail-message {
        color: #f8fafc;
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

    #announcementDetailModal {
        z-index: 1400;
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

    .announcement-edit-shell {
        width: min(720px, 100%);
    }

    .announcement-edit-form {
        max-height: calc(100vh - 150px);
        overflow-y: auto;
        padding: 20px;
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
    html:not([data-theme="dark"]) .announcement-view,
    html:not([data-theme="dark"]) .announcement-edit,
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
    $canPublishAnnouncements = optional(auth()->user())->canAccessPermission('announcements.publish') ?? false;
    $canArchiveAnnouncements = optional(auth()->user())->canAccessPermission('announcements.archive') ?? false;
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
        @if($canPublishAnnouncements)
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

            <form method="POST" action="{{ route('admin.announcements.store') }}" class="announcement-form" enctype="multipart/form-data">
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

                <div class="announcement-field">
                    <span class="announcement-label">Message Content</span>
                    <div class="announcement-editor">
                        <div class="announcement-toolbar" aria-label="Message formatting tools">
                            <button type="button" class="announcement-tool" data-command="bold" title="Bold" aria-pressed="false">B</button>
                            <button type="button" class="announcement-tool" data-command="italic" title="Italic" aria-pressed="false"><em>I</em></button>
                            <button type="button" class="announcement-tool" data-command="insertUnorderedList" title="Bulleted list" aria-pressed="false">&bull;</button>
                            <button type="button" class="announcement-tool" data-announcement-link-tool title="Add link" aria-label="Add link">
                                <x-outline-icon name="link" />
                            </button>
                        </div>
                        <div class="announcement-link-popover" data-announcement-link-popover hidden>
                            <input type="url" data-announcement-link-input placeholder="Paste https:// link" aria-label="Announcement link">
                            <button type="button" class="announcement-link-apply" data-announcement-link-apply>Apply</button>
                            <button type="button" class="announcement-link-cancel" data-announcement-link-cancel aria-label="Cancel link"><x-outline-icon name="x-mark" /></button>
                            <p class="announcement-link-feedback" data-announcement-link-feedback role="status" hidden></p>
                        </div>
                        <div
                            class="announcement-textarea announcement-rich-editor"
                            contenteditable="true"
                            role="textbox"
                            aria-multiline="true"
                            aria-label="Announcement message"
                            data-placeholder="Write the details of the update here..."
                            data-announcement-message
                        >{!! \App\Services\AnnouncementContent::toHtml(old('message')) !!}</div>
                        <textarea name="message" data-announcement-message-input hidden required>{{ old('message') }}</textarea>
                    </div>
                    <span class="announcement-counter" data-announcement-counter>0 / 2000</span>
                </div>

                <div class="announcement-field">
                    <span class="announcement-label">Announcement Image <small>(Optional)</small></span>
                    <div class="announcement-image-controls">
                        <input id="announcementImages" class="announcement-image-input" type="file" name="images[]" accept="image/jpeg,image/png,image/webp,image/gif" multiple data-announcement-image>
                        <label class="announcement-image-add" for="announcementImages">
                            <x-outline-icon name="plus-circle" />
                            <span>Add image</span>
                        </label>
                        <button type="button" class="announcement-image-clear" data-announcement-image-clear aria-label="Remove all selected announcement images" title="Remove all images" hidden>
                            <x-outline-icon name="trash" />
                        </button>
                    </div>
                    <span class="announcement-image-note">Up to 5 JPG, PNG, WebP, or GIF images. Maximum file size: 500 KB per image.</span>
                    <span class="announcement-image-feedback" data-announcement-image-feedback role="status" hidden></span>
                    <span class="announcement-image-preview" data-announcement-image-preview hidden></span>
                    <template data-announcement-image-remove-template>
                        <button type="button" class="announcement-image-remove" aria-label="Remove image" title="Remove image">
                            <x-outline-icon name="x-mark" />
                        </button>
                    </template>
                </div>

                <div class="announcement-visibility-field">
                    <strong class="announcement-visibility-title">Visibility</strong>
                    <div class="announcement-visibility-options">
                        <input type="hidden" name="show_on_landing" value="0">
                        <label class="announcement-visibility-toggle">
                            <input type="checkbox" name="show_on_landing" value="1" data-announcement-visibility @checked(old('show_on_landing', true))>
                            <span>Show in General</span>
                        </label>
                        <input type="hidden" name="show_in_portal" value="0">
                        <label class="announcement-visibility-toggle">
                            <input type="checkbox" name="show_in_portal" value="1" data-announcement-visibility @checked(old('show_in_portal', true))>
                            <span>Show in User Portal</span>
                        </label>
                    </div>
                </div>

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
        @endif

        <section class="announcement-card">
            <div class="announcement-card-head">
                <h3 class="announcement-card-title">Active Bulletins</h3>
            </div>

            <div data-active-bulletins-panel aria-live="polite">
            <div class="announcement-list">
                @forelse($activeBulletins as $announcement)
                    @php
                        $priority = $announcement->priority ?: 'info';
                        $priorityLabel = $priorityLabels[$priority] ?? ucfirst($priority);
                        $isArchived = $announcement->status === \App\Models\Announcement::STATUS_ARCHIVED;
                        $isExpired = $announcement->is_expired;
                        $statusLabel = $isArchived ? 'Archived' : ($isExpired ? 'Expired' : 'Active');
                        $statusClass = $isArchived ? 'is-archived' : ($isExpired ? 'is-expired' : '');
                        $announcementPreview = \Illuminate\Support\Str::limit(\App\Services\AnnouncementContent::toPlainText($announcement->message), 190);
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
                                @if($canPublishAnnouncements && ! $isArchived)
                                    <button type="button" class="announcement-edit" data-announcement-edit data-update-url="{{ route('admin.announcements.update', $announcement) }}" data-title="{{ $announcement->title }}" data-message="{{ base64_encode($announcement->message) }}" data-priority="{{ $priority }}" data-show-on-landing="{{ $announcement->show_on_landing ? '1' : '0' }}" data-show-in-portal="{{ $announcement->show_in_portal ? '1' : '0' }}" data-expires="{{ $announcement->expires_at?->format('Y-m-d') ?? '' }}" data-image-urls='@json($announcement->image_urls)' aria-label="Edit announcement" title="Edit announcement">
                                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" aria-hidden="true">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="m16.862 4.487 1.687-1.688a1.875 1.875 0 1 1 2.652 2.652L10.582 16.07a4.5 4.5 0 0 1-1.897 1.13L6 18l.8-2.685a4.5 4.5 0 0 1 1.13-1.897l8.932-8.931Zm0 0L19.5 7.125M18 14v4.75A2.25 2.25 0 0 1 15.75 21H5.25A2.25 2.25 0 0 1 3 18.75V8.25A2.25 2.25 0 0 1 5.25 6H10" />
                                        </svg>
                                    </button>
                                @else
                                    <span class="announcement-status {{ $statusClass }}">{{ $statusLabel }}</span>
                                @endif
                                <button
                                    type="button"
                                    class="announcement-view"
                                    data-announcement-view
                                    data-title="{{ $announcement->title }}"
                                    data-priority="{{ $priorityLabel }}"
                                    data-status="{{ $statusLabel }}"
                                    data-published="{{ $announcement->created_at?->format('M j, Y g:i A') ?? 'N/A' }}"
                                    data-expires="{{ $announcement->expires_at?->format('M j, Y') ?? 'Never' }}"
                                    data-message-html="{!! e(\App\Services\AnnouncementContent::toHtml($announcement->message)) !!}"
                                    data-image-urls='@json($announcement->image_urls)'
                                    aria-label="View full announcement"
                                    title="View full announcement"
                                >
                                    <x-outline-icon name="eye" />
                                </button>
                                @if($canArchiveAnnouncements && ! $isArchived)
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
                                @if($canArchiveAnnouncements)
                                <form method="POST" action="{{ route('admin.announcements.destroy', $announcement) }}" onsubmit="return confirm('Delete this announcement?');">
                                    @csrf
                                    @method('DELETE')
                                    <button class="announcement-delete" type="submit" aria-label="Delete announcement">
                                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="m14.74 9-.346 9m-4.788 0L9.26 9m9.968-3.21c.342.052.682.107 1.022.166M19.228 5.79 18.16 19.673A2.25 2.25 0 0 1 15.916 21H8.084a2.25 2.25 0 0 1-2.244-2.077L4.772 5.79m14.456 0a48.108 48.108 0 0 0-3.478-.397m-12 .563c.34-.059.68-.114 1.022-.165m0 0a48.11 48.11 0 0 1 3.478-.397m7.5 0v-.916c0-1.18-.91-2.164-2.09-2.201a51.964 51.964 0 0 0-3.32 0c-1.18.037-2.09 1.022-2.09 2.201v.916m7.5 0a48.667 48.667 0 0 0-7.5 0" />
                                        </svg>
                                    </button>
                                </form>
                                @endif
                            </div>
                        </div>

                        <h3>{{ $announcement->title }}</h3>
                        <div class="announcement-preview">{{ $announcementPreview }}</div>

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
            @if($activeBulletins->total() > 0)
                @php
                    $bulletinCurrentPage = $activeBulletins->currentPage();
                    $bulletinLastPage = $activeBulletins->lastPage();
                    $bulletinPageWindow = 5;
                    $bulletinPageStart = max(1, min(
                        $bulletinCurrentPage - intdiv($bulletinPageWindow, 2),
                        max(1, $bulletinLastPage - $bulletinPageWindow + 1)
                    ));
                    $bulletinPageEnd = min($bulletinLastPage, $bulletinPageStart + $bulletinPageWindow - 1);
                @endphp
                <div class="announcement-pagination">
                    <span class="announcement-pagination-summary">
                        Showing {{ $activeBulletins->firstItem() }} to {{ $activeBulletins->lastItem() }} of {{ $activeBulletins->total() }} record{{ $activeBulletins->total() === 1 ? '' : 's' }}
                    </span>
                    <div class="announcement-pagination-actions" aria-label="Active bulletins pagination">
                        @if($activeBulletins->onFirstPage())
                            <span class="announcement-pagination-control is-disabled" aria-disabled="true" aria-label="Previous page">&larr;</span>
                        @else
                            <a class="announcement-pagination-control" href="{{ $activeBulletins->previousPageUrl() }}" aria-label="Previous page">&larr;</a>
                        @endif

                        @for($bulletinPage = $bulletinPageStart; $bulletinPage <= $bulletinPageEnd; $bulletinPage++)
                            @if($bulletinPage === $bulletinCurrentPage)
                                <span class="announcement-pagination-control is-active" aria-current="page">{{ $bulletinPage }}</span>
                            @else
                                <a class="announcement-pagination-control" href="{{ $activeBulletins->url($bulletinPage) }}" aria-label="Page {{ $bulletinPage }}">{{ $bulletinPage }}</a>
                            @endif
                        @endfor

                        @if($activeBulletins->hasMorePages())
                            <a class="announcement-pagination-control" href="{{ $activeBulletins->nextPageUrl() }}" aria-label="Next page">&rarr;</a>
                        @else
                            <span class="announcement-pagination-control is-disabled" aria-disabled="true" aria-label="Next page">&rarr;</span>
                        @endif
                    </div>
                    <span class="announcement-pagination-spacer" aria-hidden="true"></span>
                </div>
            @endif
            </div>
        </section>
    </div>

    <div class="announcement-archive-modal" id="announcementDetailModal" role="dialog" aria-modal="true" aria-labelledby="announcementDetailModalTitle" aria-hidden="true">
        <div class="announcement-archive-shell" role="document">
            <div class="announcement-archive-head">
                <div class="announcement-archive-title">
                    <span class="announcement-archive-badge" aria-hidden="true"><x-outline-icon name="eye" /></span>
                    <div>
                        <h3 id="announcementDetailModalTitle">Announcement Details</h3>
                        <p>Complete bulletin information and attached images.</p>
                    </div>
                </div>
                <button type="button" class="announcement-archive-close" id="closeAnnouncementDetailModal" aria-label="Close announcement details">
                    <x-outline-icon name="x-mark" />
                </button>
            </div>
            <div class="announcement-archive-body announcement-detail-body">
                <div class="announcement-detail-meta" id="announcementDetailMeta"></div>
                <h4 id="announcementDetailTitle"></h4>
                <div class="announcement-detail-message" id="announcementDetailMessage"></div>
                <div class="announcement-detail-images" id="announcementDetailImages" hidden></div>
            </div>
        </div>
    </div>

    <div class="announcement-archive-modal" id="announcementEditModal" role="dialog" aria-modal="true" aria-labelledby="announcementEditModalTitle" aria-hidden="true">
        <div class="announcement-archive-shell announcement-edit-shell" role="document">
            <div class="announcement-archive-head">
                <div class="announcement-archive-title">
                    <span class="announcement-archive-badge" aria-hidden="true">
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="m16.862 4.487 1.687-1.688a1.875 1.875 0 1 1 2.652 2.652L10.582 16.07a4.5 4.5 0 0 1-1.897 1.13L6 18l.8-2.685a4.5 4.5 0 0 1 1.13-1.897l8.932-8.931Zm0 0L19.5 7.125M18 14v4.75A2.25 2.25 0 0 1 15.75 21H5.25A2.25 2.25 0 0 1 3 18.75V8.25A2.25 2.25 0 0 1 5.25 6H10" />
                        </svg>
                    </span>
                    <div>
                        <h3 id="announcementEditModalTitle">Edit Announcement</h3>
                        <p>Update the announcement details and visibility.</p>
                    </div>
                </div>
                <button type="button" class="announcement-archive-close" id="closeAnnouncementEditModal" aria-label="Close edit announcement">
                    <x-outline-icon name="x-mark" />
                </button>
            </div>
            <form method="POST" action="" class="announcement-form announcement-edit-form" id="announcementEditForm" enctype="multipart/form-data">
                @csrf
                @method('PATCH')
                <label class="announcement-field">
                    <span class="announcement-label">Title</span>
                    <input class="announcement-input" type="text" name="title" id="announcementEditTitle" maxlength="140" required>
                </label>
                <label class="announcement-field">
                    <span class="announcement-label">Priority Level</span>
                    <select class="announcement-select" name="priority" id="announcementEditPriority" required>
                        @foreach($priorityLabels as $value => $label)
                            <option value="{{ $value }}">{{ $label }}</option>
                        @endforeach
                    </select>
                </label>
                <div class="announcement-field">
                    <span class="announcement-label">Message Content</span>
                    <div class="announcement-editor">
                        <div class="announcement-textarea announcement-rich-editor" contenteditable="true" role="textbox" aria-multiline="true" data-announcement-edit-message></div>
                        <textarea name="message" id="announcementEditMessage" hidden required></textarea>
                    </div>
                </div>
                <div class="announcement-field">
                    <span class="announcement-label">Announcement Image <small>(Optional)</small></span>
                    <div class="announcement-image-preview" id="announcementEditExistingImages" hidden></div>
                    <div id="announcementEditRemovedImages"></div>
                    <div class="announcement-image-controls">
                        <input id="announcementEditImages" class="announcement-image-input" type="file" name="images[]" accept="image/jpeg,image/png,image/webp,image/gif" multiple>
                        <label class="announcement-image-add" for="announcementEditImages">
                            <x-outline-icon name="plus-circle" />
                            <span>Add image</span>
                        </label>
                    </div>
                    <span class="announcement-image-note">Remove an existing image with X, or add replacement images. Up to 5 images total, 500 KB each.</span>
                    <span class="announcement-image-feedback" id="announcementEditImageFeedback" role="status" hidden></span>
                    <div class="announcement-image-preview" id="announcementEditNewImages" hidden></div>
                </div>
                <div class="announcement-visibility-field">
                    <strong class="announcement-visibility-title">Visibility</strong>
                    <div class="announcement-visibility-options">
                        <input type="hidden" name="show_on_landing" value="0">
                        <label class="announcement-visibility-toggle">
                            <input type="checkbox" name="show_on_landing" value="1" id="announcementEditLanding">
                            <span>Show in General</span>
                        </label>
                        <input type="hidden" name="show_in_portal" value="0">
                        <label class="announcement-visibility-toggle">
                            <input type="checkbox" name="show_in_portal" value="1" id="announcementEditPortal">
                            <span>Show in User Portal</span>
                        </label>
                    </div>
                </div>
                <label class="announcement-field">
                    <span class="announcement-label">Expiration Date (Optional)</span>
                    <input class="announcement-input" type="date" name="expires_at" id="announcementEditExpires">
                </label>
                <button type="submit" class="announcement-submit">
                    <x-outline-icon name="check" />
                    Save Changes
                </button>
            </form>
        </div>
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
                                    <div class="announcement-actions">
                                        <button
                                            type="button"
                                            class="announcement-view"
                                            data-announcement-view
                                            data-title="{{ $announcement->title }}"
                                            data-priority="{{ $priorityLabel }}"
                                            data-status="Archived"
                                            data-published="{{ $announcement->created_at?->format('M j, Y g:i A') ?? 'N/A' }}"
                                            data-expires="{{ $announcement->expires_at?->format('M j, Y') ?? 'Never' }}"
                                            data-message-html="{!! e(\App\Services\AnnouncementContent::toHtml($announcement->message)) !!}"
                                            data-image-urls='@json($announcement->image_urls)'
                                            aria-label="View archived announcement"
                                            title="View archived announcement"
                                        >
                                            <x-outline-icon name="eye" />
                                        </button>
                                        @if($canArchiveAnnouncements)
                                            <form method="POST" action="{{ route('admin.announcements.restore', $announcement) }}" onsubmit="return confirm('Restore this announcement?');">
                                                @csrf
                                                @method('PATCH')
                                                <button class="announcement-restore" type="submit" aria-label="Restore announcement" title="Restore announcement">
                                                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" aria-hidden="true">
                                                        <path stroke-linecap="round" stroke-linejoin="round" d="m7.49 12-3.75 3.75m0 0 3.75 3.75m-3.75-3.75h16.5V4.499" />
                                                    </svg>
                                                </button>
                                            </form>
                                        @endif
                                    </div>
                                </div>
                                <h4>{{ $announcement->title }}</h4>
                                @if($announcement->image_urls !== [])
                                    <div class="announcement-item-image-grid">
                                        @foreach($announcement->image_urls as $imageIndex => $imageUrl)
                                            <img class="announcement-item-image" src="{{ $imageUrl }}" alt="Announcement image {{ $imageIndex + 1 }} for {{ $announcement->title }}">
                                        @endforeach
                                    </div>
                                @endif
                                <div class="announcement-message">{!! \App\Services\AnnouncementContent::toHtml(\Illuminate\Support\Str::limit($announcement->message, 190)) !!}</div>
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
        const messageInput = document.querySelector('[data-announcement-message-input]');
        const counter = document.querySelector('[data-announcement-counter]');
        const tools = document.querySelectorAll('[data-command]');
        const linkTool = document.querySelector('[data-announcement-link-tool]');
        const linkPopover = document.querySelector('[data-announcement-link-popover]');
        const linkInput = document.querySelector('[data-announcement-link-input]');
        const linkApply = document.querySelector('[data-announcement-link-apply]');
        const linkCancel = document.querySelector('[data-announcement-link-cancel]');
        const linkFeedback = document.querySelector('[data-announcement-link-feedback]');
        const imageInput = document.querySelector('[data-announcement-image]');
        const imagePreview = document.querySelector('[data-announcement-image-preview]');
        const imageClear = document.querySelector('[data-announcement-image-clear]');
        const imageFeedback = document.querySelector('[data-announcement-image-feedback]');
        const imageRemoveTemplate = document.querySelector('[data-announcement-image-remove-template]');
        const announcementForm = imageInput?.closest('form');
        const visibilityInputs = Array.from(document.querySelectorAll('[data-announcement-visibility]'));
        const archiveButton = document.getElementById('openArchivedAnnouncements');
        const archiveModal = document.getElementById('archivedAnnouncementsModal');
        const archiveClose = document.getElementById('closeArchivedAnnouncements');
        const activeBulletinsPanel = document.querySelector('[data-active-bulletins-panel]');
        const detailModal = document.getElementById('announcementDetailModal');
        const detailClose = document.getElementById('closeAnnouncementDetailModal');
        const detailTitle = document.getElementById('announcementDetailTitle');
        const detailMeta = document.getElementById('announcementDetailMeta');
        const detailMessage = document.getElementById('announcementDetailMessage');
        const detailImages = document.getElementById('announcementDetailImages');
        const editModal = document.getElementById('announcementEditModal');
        const editClose = document.getElementById('closeAnnouncementEditModal');
        const editForm = document.getElementById('announcementEditForm');
        const editTitle = document.getElementById('announcementEditTitle');
        const editPriority = document.getElementById('announcementEditPriority');
        const editMessage = document.querySelector('[data-announcement-edit-message]');
        const editMessageInput = document.getElementById('announcementEditMessage');
        const editLanding = document.getElementById('announcementEditLanding');
        const editPortal = document.getElementById('announcementEditPortal');
        const editExpires = document.getElementById('announcementEditExpires');
        const editImageInput = document.getElementById('announcementEditImages');
        const editExistingImages = document.getElementById('announcementEditExistingImages');
        const editNewImages = document.getElementById('announcementEditNewImages');
        const editRemovedImages = document.getElementById('announcementEditRemovedImages');
        const editImageFeedback = document.getElementById('announcementEditImageFeedback');
        let editImageUrls = [];
        let removedEditImageIndexes = new Set();
        let selectedEditImages = [];
        let editTrigger = null;
        let detailTrigger = null;

        const decodeAnnouncementMessage = (value) => {
            try {
                return decodeURIComponent(escape(window.atob(value || '')));
            } catch (error) {
                return '';
            }
        };

        const syncEditImageInput = () => {
            if (!editImageInput) return;
            const transfer = new DataTransfer();
            selectedEditImages.forEach((file) => transfer.items.add(file));
            editImageInput.files = transfer.files;
        };

        const syncRemovedEditImages = () => {
            if (!editRemovedImages) return;
            editRemovedImages.replaceChildren(...Array.from(removedEditImageIndexes).map((index) => {
                const input = document.createElement('input');
                input.type = 'hidden';
                input.name = 'remove_image_indexes[]';
                input.value = String(index);
                return input;
            }));
        };

        const createEditImageCard = (source, alt, onRemove) => {
            const card = document.createElement('span');
            card.className = 'announcement-image-preview-card';

            const image = document.createElement('img');
            image.src = source;
            image.alt = alt;

            const removeButton = document.createElement('button');
            removeButton.type = 'button';
            removeButton.className = 'announcement-image-remove';
            removeButton.setAttribute('aria-label', `Remove ${alt}`);
            removeButton.title = 'Remove image';
            removeButton.innerHTML = '<svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18 18 6M6 6l12 12" /></svg>';
            removeButton.addEventListener('click', onRemove);

            card.append(image, removeButton);
            return card;
        };

        const renderEditImages = () => {
            if (editExistingImages) {
                const existingCards = editImageUrls.flatMap((url, index) => {
                    if (removedEditImageIndexes.has(index)) return [];
                    return [createEditImageCard(url, `existing announcement image ${index + 1}`, () => {
                        removedEditImageIndexes.add(index);
                        syncRemovedEditImages();
                        renderEditImages();
                    })];
                });
                editExistingImages.replaceChildren(...existingCards);
                editExistingImages.classList.toggle('is-visible', existingCards.length > 0);
                editExistingImages.toggleAttribute('hidden', existingCards.length === 0);
            }

            if (editNewImages) {
                const newCards = selectedEditImages.map((file, index) => createEditImageCard(
                    URL.createObjectURL(file),
                    `new announcement image ${index + 1}`,
                    () => {
                        selectedEditImages.splice(index, 1);
                        syncEditImageInput();
                        renderEditImages();
                    }
                ));
                editNewImages.replaceChildren(...newCards);
                editNewImages.classList.toggle('is-visible', newCards.length > 0);
                editNewImages.toggleAttribute('hidden', newCards.length === 0);
            }
        };

        const openEditModal = (trigger) => {
            if (!editModal || !editForm || !editMessage) return;
            editTrigger = trigger;
            editForm.action = trigger.dataset.updateUrl || '';
            editTitle.value = trigger.dataset.title || '';
            editPriority.value = trigger.dataset.priority || 'info';
            editMessage.innerHTML = decodeAnnouncementMessage(trigger.dataset.message);
            editMessageInput.value = editMessage.innerHTML;
            editLanding.checked = trigger.dataset.showOnLanding === '1';
            editPortal.checked = trigger.dataset.showInPortal === '1';
            editExpires.value = trigger.dataset.expires || '';
            try {
                editImageUrls = JSON.parse(trigger.dataset.imageUrls || '[]');
            } catch (error) {
                editImageUrls = [];
            }
            removedEditImageIndexes = new Set();
            selectedEditImages = [];
            if (editImageInput) editImageInput.value = '';
            if (editImageFeedback) {
                editImageFeedback.textContent = '';
                editImageFeedback.hidden = true;
            }
            syncRemovedEditImages();
            renderEditImages();
            editModal.classList.add('is-open');
            editModal.setAttribute('aria-hidden', 'false');
            document.body.style.overflow = 'hidden';
            editTitle.focus();
        };

        const closeEditModal = () => {
            if (!editModal) return;
            editModal.classList.remove('is-open');
            editModal.setAttribute('aria-hidden', 'true');
            document.body.style.overflow = '';
            editTrigger?.focus();
            editTrigger = null;
        };

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

        const openDetailModal = (trigger) => {
            if (!detailModal) return;

            detailTrigger = trigger;
            detailTitle.textContent = trigger.dataset.title || 'Announcement';
            detailMessage.innerHTML = trigger.dataset.messageHtml || '';
            detailMeta.replaceChildren(...[
                `Priority: ${trigger.dataset.priority || 'Info'}`,
                `Status: ${trigger.dataset.status || 'Active'}`,
                `Published: ${trigger.dataset.published || 'N/A'}`,
                `Expires: ${trigger.dataset.expires || 'Never'}`,
            ].map((value) => {
                const item = document.createElement('span');
                item.textContent = value;
                return item;
            }));

            let imageUrls = [];
            try {
                imageUrls = JSON.parse(trigger.dataset.imageUrls || '[]');
            } catch (error) {
                imageUrls = [];
            }

            detailImages.replaceChildren(...imageUrls.map((imageUrl, index) => {
                const card = document.createElement('div');
                card.className = 'announcement-detail-image-card';

                const button = document.createElement('button');
                button.type = 'button';
                button.className = 'announcement-detail-image-button';
                button.setAttribute('aria-label', `Show open option for announcement image ${index + 1}`);

                const image = document.createElement('img');
                image.src = imageUrl;
                image.alt = `Announcement image ${index + 1} for ${trigger.dataset.title || 'announcement'}`;
                button.append(image);
                button.addEventListener('click', () => card.classList.toggle('is-open'));

                const openLink = document.createElement('a');
                openLink.className = 'announcement-detail-image-open';
                openLink.href = imageUrl;
                openLink.target = '_blank';
                openLink.rel = 'noopener noreferrer';
                openLink.innerHTML = '<span>Open</span>';

                card.append(button, openLink);
                return card;
            }));
            detailImages.toggleAttribute('hidden', imageUrls.length === 0);

            detailModal.classList.add('is-open');
            detailModal.setAttribute('aria-hidden', 'false');
            document.body.style.overflow = 'hidden';
            detailClose?.focus();
        };

        const closeDetailModal = () => {
            if (!detailModal) return;

            detailModal.classList.remove('is-open');
            detailModal.setAttribute('aria-hidden', 'true');
            document.body.style.overflow = '';
            detailTrigger?.focus();
            detailTrigger = null;
        };

        archiveButton?.addEventListener('click', openArchiveModal);
        archiveClose?.addEventListener('click', closeArchiveModal);
        archiveModal?.addEventListener('click', function (event) {
            if (event.target === archiveModal) {
                closeArchiveModal();
            }
        });
        document.addEventListener('click', function (event) {
            const editButton = event.target.closest('[data-announcement-edit]');
            if (editButton) {
                event.preventDefault();
                openEditModal(editButton);
                return;
            }
            const viewButton = event.target.closest('[data-announcement-view]');
            if (viewButton) {
                event.preventDefault();
                openDetailModal(viewButton);
            }
        });
        detailClose?.addEventListener('click', closeDetailModal);
        detailModal?.addEventListener('click', function (event) {
            if (event.target === detailModal) {
                closeDetailModal();
            }
        });
        editClose?.addEventListener('click', closeEditModal);
        editModal?.addEventListener('click', function (event) {
            if (event.target === editModal) closeEditModal();
        });
        editMessage?.addEventListener('input', function () {
            editMessageInput.value = editMessage.innerHTML;
        });
        editImageInput?.addEventListener('change', function () {
            const incomingFiles = Array.from(editImageInput.files || []);
            const remainingExistingIndexes = editImageUrls
                .map((_, index) => index)
                .filter((index) => !removedEditImageIndexes.has(index));
            const retainedExistingCount = remainingExistingIndexes.length;
            const currentlySelectedCount = selectedEditImages.length;
            const availableSlots = Math.max(0, 5 - retainedExistingCount - currentlySelectedCount);
            const validFiles = incomingFiles.filter((file) => file.size <= 500 * 1024);
            const acceptedFiles = validFiles.slice(0, availableSlots);

            if (acceptedFiles.length > 0 && retainedExistingCount > 0) {
                const replacementCount = Math.min(acceptedFiles.length, retainedExistingCount);
                remainingExistingIndexes.slice(0, replacementCount).forEach((index) => {
                    removedEditImageIndexes.add(index);
                });
            }

            selectedEditImages.push(...acceptedFiles);
            syncEditImageInput();
            syncRemovedEditImages();
            renderEditImages();

            const messages = [];
            if (validFiles.length !== incomingFiles.length) messages.push('Each image must be 500 KB or smaller.');
            if (validFiles.length > availableSlots) messages.push('An announcement can have up to 5 images.');
            if (editImageFeedback) {
                editImageFeedback.textContent = messages.join(' ');
                editImageFeedback.toggleAttribute('hidden', messages.length === 0);
            }
        });
        editForm?.addEventListener('submit', function () {
            editMessageInput.value = editMessage.innerHTML;
            syncEditImageInput();
            syncRemovedEditImages();
        });

        const loadActiveBulletinPage = async (url) => {
            if (!activeBulletinsPanel || activeBulletinsPanel.classList.contains('is-loading')) {
                return;
            }

            activeBulletinsPanel.classList.add('is-loading');
            activeBulletinsPanel.setAttribute('aria-busy', 'true');

            try {
                const response = await fetch(url, {
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest',
                        'Accept': 'text/html',
                    },
                });

                if (!response.ok) {
                    throw new Error('Unable to load the next bulletin page.');
                }

                const documentFragment = new DOMParser().parseFromString(await response.text(), 'text/html');
                const nextPanel = documentFragment.querySelector('[data-active-bulletins-panel]');
                if (!nextPanel) {
                    throw new Error('The bulletin list could not be found.');
                }

                activeBulletinsPanel.innerHTML = nextPanel.innerHTML;
                window.history.replaceState({}, '', url);
            } catch (error) {
                window.location.assign(url);
            } finally {
                activeBulletinsPanel.classList.remove('is-loading');
                activeBulletinsPanel.removeAttribute('aria-busy');
            }
        };

        activeBulletinsPanel?.addEventListener('click', function (event) {
            const pageLink = event.target.closest('.announcement-pagination-control[href]');
            if (!pageLink || !activeBulletinsPanel.contains(pageLink)) {
                return;
            }

            event.preventDefault();
            loadActiveBulletinPage(pageLink.href);
        });

        document.addEventListener('keydown', function (event) {
            if (event.key === 'Escape' && archiveModal?.classList.contains('is-open')) {
                closeArchiveModal();
            }
            if (event.key === 'Escape' && detailModal?.classList.contains('is-open')) {
                closeDetailModal();
            }
        });

        if (!message || !messageInput || !counter) {
            return;
        }

        const syncCounter = () => {
            const plainText = (message.innerText || '').replace(/\u00a0/g, ' ').trimEnd();
            messageInput.value = message.innerHTML;
            counter.textContent = `${plainText.length} / 2000`;
        };

        const syncVisibilityValidity = () => {
            const hasSelection = visibilityInputs.some((input) => input.checked);
            visibilityInputs.forEach((input) => {
                input.setCustomValidity(hasSelection ? '' : 'Select at least one visibility option.');
            });
        };

        visibilityInputs.forEach((input) => input.addEventListener('change', syncVisibilityValidity));
        syncVisibilityValidity();

        const syncToolbarState = () => {
            if (document.activeElement !== message) return;

            tools.forEach((tool) => {
                const isActive = document.queryCommandState(tool.dataset.command);
                tool.classList.toggle('is-active', isActive);
                tool.setAttribute('aria-pressed', isActive ? 'true' : 'false');
            });
        };

        let savedSelection = null;

        const rememberEditorSelection = () => {
            const selection = window.getSelection();
            if (!selection || selection.rangeCount === 0) return;

            const range = selection.getRangeAt(0);
            if (message.contains(range.commonAncestorContainer)) {
                savedSelection = range.cloneRange();
            }
        };

        const setLinkFeedback = (feedbackMessage = '') => {
            if (!linkFeedback) return;
            linkFeedback.textContent = feedbackMessage;
            linkFeedback.toggleAttribute('hidden', !feedbackMessage);
        };

        const closeLinkPopover = () => {
            linkPopover?.setAttribute('hidden', 'hidden');
            linkTool?.setAttribute('aria-expanded', 'false');
            setLinkFeedback();
        };

        const openLinkPopover = () => {
            rememberEditorSelection();
            linkPopover?.removeAttribute('hidden');
            linkTool?.setAttribute('aria-expanded', 'true');
            setLinkFeedback();
            window.setTimeout(() => linkInput?.focus(), 0);
        };

        message.addEventListener('input', () => {
            syncCounter();
            syncToolbarState();
        });
        message.addEventListener('keyup', syncToolbarState);
        message.addEventListener('mouseup', syncToolbarState);
        message.addEventListener('keyup', rememberEditorSelection);
        message.addEventListener('mouseup', rememberEditorSelection);
        message.addEventListener('focus', () => {
            rememberEditorSelection();
            syncToolbarState();
        });
        message.addEventListener('paste', () => window.setTimeout(() => {
            syncCounter();
            syncToolbarState();
            rememberEditorSelection();
        }, 0));
        message.addEventListener('click', (event) => {
            if (event.target.closest('a')) event.preventDefault();
        });
        syncCounter();

        const applyFormat = (tool) => {
            message.focus();
            document.execCommand(tool.dataset.command, false, null);
            syncCounter();
            syncToolbarState();
        };

        tools.forEach((tool) => {
            tool.addEventListener('mousedown', (event) => event.preventDefault());
            tool.addEventListener('click', () => applyFormat(tool));
        });

        linkTool?.addEventListener('mousedown', (event) => event.preventDefault());
        linkTool?.addEventListener('click', openLinkPopover);
        linkCancel?.addEventListener('click', closeLinkPopover);
        linkApply?.addEventListener('mousedown', (event) => event.preventDefault());
        linkApply?.addEventListener('click', () => {
            const rawUrl = linkInput?.value.trim() || '';
            let url;

            try {
                url = new URL(rawUrl);
            } catch (error) {
                setLinkFeedback('Paste a valid http or https link.');
                return;
            }

            if (!['http:', 'https:'].includes(url.protocol)) {
                setLinkFeedback('Only http and https links are allowed.');
                return;
            }

            message.focus();
            const selection = window.getSelection();
            selection?.removeAllRanges();
            if (savedSelection && message.contains(savedSelection.commonAncestorContainer)) {
                selection?.addRange(savedSelection);
            } else {
                const range = document.createRange();
                range.selectNodeContents(message);
                range.collapse(false);
                selection?.addRange(range);
            }

            const activeRange = selection?.rangeCount ? selection.getRangeAt(0) : null;
            if (!activeRange) {
                setLinkFeedback('Place the cursor in the message before adding a link.');
                return;
            }

            if (activeRange.collapsed) {
                const anchor = document.createElement('a');
                anchor.href = url.href;
                anchor.textContent = url.href;
                activeRange.insertNode(anchor);
                activeRange.setStartAfter(anchor);
                activeRange.collapse(true);
                selection?.removeAllRanges();
                selection?.addRange(activeRange);
            } else {
                document.execCommand('createLink', false, url.href);
            }

            rememberEditorSelection();
            linkInput.value = '';
            closeLinkPopover();
            syncCounter();
            syncToolbarState();
        });

        announcementForm?.addEventListener('submit', () => {
            syncCounter();
            syncVisibilityValidity();
        });

        const maxAnnouncementImages = 5;
        const maxAnnouncementImageBytes = 500 * 1024;
        let selectedAnnouncementImages = [];

        const imageKey = (file) => [file.name, file.size, file.lastModified].join(':');

        const setImageFeedback = (feedbackMessage = '') => {
            if (!imageFeedback) return;
            imageFeedback.textContent = feedbackMessage;
            imageFeedback.toggleAttribute('hidden', !feedbackMessage);
        };

        const syncSelectedAnnouncementImages = () => {
            if (!imageInput) return;

            if (!imagePreview || selectedAnnouncementImages.length === 0) {
                imagePreview?.replaceChildren();
                imagePreview?.classList.remove('is-visible');
                imagePreview?.setAttribute('hidden', 'hidden');
                imageClear?.setAttribute('hidden', 'hidden');
                return;
            }

            imagePreview.replaceChildren(...selectedAnnouncementImages.map((file, index) => {
                const card = document.createElement('span');
                card.className = 'announcement-image-preview-card';

                const image = document.createElement('img');
                image.src = URL.createObjectURL(file);
                image.alt = `Selected announcement image ${index + 1}`;
                card.append(image);

                const removeButton = imageRemoveTemplate?.content.firstElementChild?.cloneNode(true);
                if (removeButton) {
                    removeButton.addEventListener('click', () => {
                        selectedAnnouncementImages.splice(index, 1);
                        imageInput.value = '';
                        setImageFeedback();
                        syncSelectedAnnouncementImages();
                    });
                    card.append(removeButton);
                }

                return card;
            }));
            imagePreview.classList.add('is-visible');
            imagePreview.removeAttribute('hidden');
            imageClear?.removeAttribute('hidden');
        };

        imageInput?.addEventListener('change', function () {
            const incomingFiles = Array.from(imageInput.files || []);
            const currentKeys = new Set(selectedAnnouncementImages.map(imageKey));
            const tooLarge = incomingFiles.filter((file) => file.size > maxAnnouncementImageBytes);
            const validFiles = incomingFiles.filter((file) => file.size <= maxAnnouncementImageBytes && !currentKeys.has(imageKey(file)));
            const availableSlots = Math.max(0, maxAnnouncementImages - selectedAnnouncementImages.length);
            const acceptedFiles = validFiles.slice(0, availableSlots);

            selectedAnnouncementImages.push(...acceptedFiles);

            const messages = [];
            if (tooLarge.length > 0) messages.push('Each image must be 500 KB or smaller.');
            if (validFiles.length > availableSlots) messages.push(`Only ${maxAnnouncementImages} images can be attached.`);
            if (incomingFiles.length > 0 && acceptedFiles.length === 0 && messages.length === 0) messages.push('That image is already selected.');

            setImageFeedback(messages.join(' '));
            imageInput.value = '';
            syncSelectedAnnouncementImages();
        });

        imageClear?.addEventListener('click', () => {
            selectedAnnouncementImages = [];
            imageInput.value = '';
            setImageFeedback();
            syncSelectedAnnouncementImages();
        });

        announcementForm?.addEventListener('submit', () => {
            const transfer = new DataTransfer();
            selectedAnnouncementImages.forEach((file) => transfer.items.add(file));
            imageInput.files = transfer.files;
        });
    });
</script>
@endpush

