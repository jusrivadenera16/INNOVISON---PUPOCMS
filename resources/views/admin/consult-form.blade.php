@extends('layouts.admin')

@section('title', 'Clinical Consultation')

@push('styles')
<style>
    .consultation-workspace {
        display: flex;
        flex-wrap: nowrap;
        gap: 20px;
        align-items: start;
        width: 100%;
        overflow-x: auto;
        padding-bottom: 8px;
    }
    .consultation-documents {
        display: none;
        flex: 0 0 28%;
        width: 28%;
        min-width: 260px;
        max-width: 360px;
        position: sticky;
        top: 20px;
        max-height: calc(100vh - 40px);
        overflow-y: auto;
        padding: 18px;
        background: #fff;
        border: 1px solid #e2e8f0;
        border-radius: 10px;
        box-shadow: 0 4px 12px rgba(15, 23, 42, .06);
        scrollbar-width: thin;
    }
    .consultation-workspace.documents-open .consultation-documents {
        display: block;
        animation: consultationDocumentsIn .24s ease;
    }
    @keyframes consultationDocumentsIn {
        from {
            opacity: 0;
            transform: translateX(-14px);
        }
        to {
            opacity: 1;
            transform: translateX(0);
        }
    }
    .documents-heading,
    .inventory-drawer-heading {
        display: flex;
        align-items: center;
        gap: 10px;
        margin-bottom: 16px;
    }
    .documents-heading svg,
    .inventory-drawer-heading svg {
        width: 22px;
        height: 22px;
        color: #800000;
        flex: 0 0 auto;
    }
    .documents-heading h2,
    .inventory-drawer-heading h2 {
        margin: 0;
        color: #111827;
        font-size: 17px;
    }
    .documents-count {
        margin-left: auto;
        padding: 3px 8px;
        border-radius: 999px;
        background: #facc15;
        color: #111827;
        font-size: 11px;
        font-weight: 800;
    }
    .documents-panel-close {
        display: grid;
        place-items: center;
        width: 30px;
        height: 30px;
        padding: 0;
        border: 1px solid #cbd5e1;
        border-radius: 6px;
        background: #fff;
        color: #111827;
        font-size: 21px;
        line-height: 1;
        cursor: pointer;
    }
    .documents-panel-close:hover {
        border-color: #800000;
        background: #800000;
        color: #fff;
    }
    .document-list {
        display: grid;
        gap: 12px;
    }
    .document-card {
        overflow: hidden;
        border: 1px solid #e2e8f0;
        border-radius: 8px;
        background: #f8fafc;
    }
    .document-preview {
        display: grid;
        place-items: center;
        width: 100%;
        height: 116px;
        background: #e5e7eb;
        color: #800000;
    }
    .document-preview img {
        width: 100%;
        height: 100%;
        object-fit: cover;
    }
    .document-preview svg {
        width: 38px;
        height: 38px;
    }
    .document-card-body {
        padding: 11px;
    }
    .document-card-title {
        display: block;
        margin-bottom: 9px;
        color: #111827;
        font-size: 12px;
        font-weight: 800;
    }
    .document-open {
        display: inline-flex;
        align-items: center;
        gap: 6px;
        color: #800000;
        font-size: 12px;
        font-weight: 800;
        text-decoration: none;
    }
    .document-open:hover {
        color: #a16207;
    }
    .document-open svg {
        width: 15px;
        height: 15px;
    }
    .documents-empty {
        padding: 24px 12px;
        border: 1px dashed #cbd5e1;
        border-radius: 8px;
        color: #64748b;
        font-size: 13px;
        line-height: 1.6;
        text-align: center;
    }
    .consultation-main {
        flex: 1 1 auto;
        width: 72%;
        min-width: 640px;
    }
    .consult-card {
        margin-bottom: 20px;
        padding: 22px;
        border: 1px solid #e2e8f0;
        border-radius: 10px;
        background: #fff;
        box-shadow: 0 4px 12px rgba(15, 23, 42, .05);
    }
    .consult-card h3 {
        margin: 0 0 18px;
        color: #111827;
        font-size: 18px;
    }
    .patient-header {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 18px;
        border-left: 4px solid #800000;
        background: #f8fafc;
    }
    .patient-header-actions {
        display: flex;
        align-items: center;
        gap: 12px;
    }
    .documents-panel-trigger {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        gap: 7px;
        min-height: 38px;
        padding: 8px 12px;
        border: 1px solid #800000;
        border-radius: 7px;
        background: #fff;
        color: #800000;
        font-size: 12px;
        font-weight: 800;
        cursor: pointer;
        white-space: nowrap;
        transition: background-color .18s ease, color .18s ease;
    }
    .documents-panel-trigger:hover,
    .documents-panel-trigger[aria-expanded="true"] {
        background: #800000;
        color: #fff;
    }
    .documents-panel-trigger svg {
        width: 18px;
        height: 18px;
    }
    .patient-name {
        margin: 0 0 8px;
        color: #111827;
        font-size: 20px;
    }
    .patient-badges {
        display: flex;
        flex-wrap: wrap;
        gap: 7px;
    }
    .patient-badge,
    .badge-source {
        display: inline-flex;
        align-items: center;
        padding: 4px 9px;
        border-radius: 6px;
        font-size: 10px;
        font-weight: 800;
        text-transform: uppercase;
    }
    .patient-badge {
        background: #e2e8f0;
        color: #334155;
    }
    .source-online {
        border: 1px solid #bfdbfe;
        background: #dbeafe;
        color: #1e40af;
    }
    .source-walkin {
        border: 1px solid #fde68a;
        background: #fef3c7;
        color: #92400e;
    }
    .consultation-date {
        flex: 0 0 auto;
        color: #334155;
        font-size: 13px;
        font-weight: 700;
        text-align: right;
    }
    .consultation-date span {
        display: block;
        margin-bottom: 3px;
        color: #64748b;
        font-size: 11px;
        font-weight: 600;
    }
    .form-grid-2 {
        display: grid;
        grid-template-columns: repeat(2, minmax(0, 1fr));
        gap: 16px;
    }
    .form-group {
        margin-bottom: 16px;
    }
    .form-group:last-child {
        margin-bottom: 0;
    }
    .form-group label {
        display: block;
        margin-bottom: 6px;
        color: #111827;
        font-size: 13px;
        font-weight: 800;
    }
    .form-control {
        width: 100%;
        padding: 10px 12px;
        border: 1px solid #94a3b8;
        border-radius: 6px;
        background: #fff;
        color: #111827;
        font-size: 14px;
    }
    .form-control:focus {
        border-color: #800000;
        outline: 3px solid rgba(128, 0, 0, .12);
    }
    .form-control::placeholder {
        color: #64748b;
    }
    .form-help {
        margin-top: 6px;
        color: #64748b;
        font-size: 11px;
    }
    .form-error {
        margin-top: 6px;
        color: #dc2626;
        font-size: 12px;
        font-weight: 500;
        display: none;
    }
    .form-error.show {
        display: block;
    }
    .form-success {
        margin-top: 6px;
        color: #16a34a;
        font-size: 12px;
        font-weight: 500;
        display: none;
    }
    .form-success.show {
        display: block;
    }
    .form-control.is-invalid {
        border-color: #dc2626;
        background-color: #fef2f2;
    }
    .form-control.is-invalid:focus {
        border-color: #dc2626;
        box-shadow: 0 0 0 3px rgba(220, 38, 38, 0.1);
    }
    .form-control.is-valid {
        border-color: #16a34a;
        background-color: #f0fdf4;
    }
    .form-control.is-valid:focus {
        border-color: #16a34a;
        box-shadow: 0 0 0 3px rgba(22, 163, 74, 0.1);
    }
    .mar-required {
        border-color: #fca5a5;
        background: #fff7f7;
    }
    .choice-grid {
        display: grid;
        grid-template-columns: repeat(2, minmax(0, 1fr));
        gap: 10px;
    }
    .choice-input {
        position: absolute;
        opacity: 0;
        pointer-events: none;
    }
    .choice-card {
        display: flex;
        align-items: center;
        justify-content: center;
        min-height: 44px;
        border: 1px solid #cbd5e1;
        border-radius: 7px;
        background: #fff;
        color: #111827;
        font-size: 13px;
        font-weight: 800;
        cursor: pointer;
        transition: background-color .18s ease, border-color .18s ease, color .18s ease;
    }
    .choice-input:checked + .choice-card {
        border-color: #800000;
        background: #800000;
        color: #fff;
    }
    .medicine-header {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 12px;
        margin-bottom: 18px;
    }
    .medicine-header h3 {
        margin: 0;
    }
    .medicine-selection-row {
        display: grid;
        grid-template-columns: minmax(0, 1fr) auto;
        gap: 10px;
        align-items: center;
    }
    .inventory-tally-trigger {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        gap: 8px;
        min-height: 38px;
        padding: 8px 12px;
        border: 1px solid #800000;
        border-radius: 7px;
        background: #fff;
        color: #800000;
        font-size: 12px;
        font-weight: 800;
        cursor: pointer;
        transition: background-color .18s ease, color .18s ease, transform .18s ease;
    }
    .inventory-tally-trigger:hover {
        background: #800000;
        color: #fff;
        transform: translateY(-1px);
    }
    .inventory-tally-trigger svg {
        width: 18px;
        height: 18px;
    }
    .selected-stock {
        display: none;
        align-items: center;
        gap: 7px;
        margin-top: 9px;
        padding: 8px 10px;
        border-radius: 6px;
        background: #ecfdf5;
        color: #166534;
        font-size: 12px;
        font-weight: 800;
    }
    .selected-stock.visible {
        display: flex;
    }
    .selected-stock.low {
        background: #fff7ed;
        color: #c2410c;
    }
    .medicine-quantity-group {
        display: none;
    }
    .medicine-quantity-group.is-visible {
        display: block;
    }
    .medicine-entries {
        display: grid;
        gap: 12px;
    }
    .medicine-entry {
        display: none;
        padding: 14px;
        border: 1px solid rgba(127, 29, 45, .16);
        border-radius: 10px;
        background: #fff;
        box-shadow: 0 5px 14px rgba(127, 29, 45, .05);
    }
    .medicine-entry.is-visible {
        display: block;
        animation: medicineEntryIn .2s ease;
    }
    .medicine-entry-head {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 10px;
        margin-bottom: 12px;
    }
    .medicine-entry-title {
        display: flex;
        align-items: center;
        gap: 8px;
        color: #7f1d2d;
        font-size: .8rem;
        font-weight: 800;
        letter-spacing: .02em;
        text-transform: uppercase;
    }
    .medicine-entry-number {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        width: 22px;
        height: 22px;
        border-radius: 6px;
        background: #7f1d2d;
        color: #fff;
        font-size: .72rem;
    }
    .medicine-remove-button {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        width: 30px;
        height: 30px;
        padding: 0;
        border: 1px solid rgba(127, 29, 45, .2);
        border-radius: 7px;
        background: #fff7f8;
        color: #991b1b;
        cursor: pointer;
        transition: background-color .18s ease, color .18s ease, border-color .18s ease, transform .18s ease;
    }
    .medicine-remove-button:hover,
    .medicine-remove-button:focus-visible {
        border-color: #7f1d2d;
        background: #7f1d2d;
        color: #fff;
        transform: translateY(-1px);
        outline: none;
    }
    .medicine-remove-button svg {
        width: 16px;
        height: 16px;
    }
    .medicine-entry .form-group {
        margin-bottom: 10px;
        padding: 0;
        border: 0;
        background: transparent;
        box-shadow: none;
    }
    .medicine-entry .form-group:last-child {
        margin-bottom: 0;
    }
    .medicine-entry .medicine-quantity-group {
        display: none;
    }
    .medicine-entry .medicine-quantity-group.is-visible {
        display: block;
    }
    .medicine-entry .form-control {
        min-height: 42px;
    }
    .medicine-add-row {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 12px;
        margin-top: 12px;
    }
    .medicine-add-button {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        gap: 7px;
        width: 100%;
        min-height: 40px;
        padding: 9px 14px;
        border: 1px dashed rgba(127, 29, 45, .42);
        border-radius: 8px;
        background: #fffafb;
        color: #7f1d2d;
        font-size: .8rem;
        font-weight: 800;
        cursor: pointer;
        transition: background-color .18s ease, border-color .18s ease, color .18s ease, transform .18s ease;
    }
    .medicine-add-button:hover,
    .medicine-add-button:focus-visible {
        border-color: #7f1d2d;
        background: #7f1d2d;
        color: #fff;
        transform: translateY(-1px);
        outline: none;
    }
    .medicine-add-button:disabled {
        cursor: not-allowed;
        opacity: .45;
        transform: none;
    }
    .medicine-add-button svg {
        width: 17px;
        height: 17px;
    }
    .medicine-selection-count {
        flex: 0 0 auto;
        color: #7f1d2d;
        font-size: .74rem;
        font-weight: 800;
        white-space: nowrap;
    }
    @keyframes medicineEntryIn {
        from { opacity: 0; transform: translateY(-5px); }
        to { opacity: 1; transform: translateY(0); }
    }
    .form-actions {
        display: flex;
        align-items: center;
        gap: 12px;
        margin-top: 12px;
    }
    .btn-save {
        flex: 1;
        min-height: 46px;
        padding: 12px 22px;
        border: 0;
        border-radius: 8px;
        background: #800000;
        color: #fff;
        font-weight: 800;
        cursor: pointer;
        transition: background-color .2s ease, color .2s ease;
    }
    .btn-save:hover {
        background: #facc15;
        color: #111827;
    }
    .btn-cancel {
        padding: 12px;
        color: #111827;
        font-size: 13px;
        font-weight: 800;
        text-decoration: none;
    }
    .inventory-tally-list {
        display: grid;
        gap: 9px;
    }
    .inventory-tally-item {
        padding: 12px;
        border: 1px solid #e2e8f0;
        border-radius: 8px;
        background: #f8fafc;
        cursor: pointer;
        transition: border-color .18s ease, background-color .18s ease, box-shadow .18s ease;
    }
    .inventory-tally-item:hover,
    .inventory-tally-item.selected {
        border-color: #800000;
        background: #fff7ed;
        box-shadow: 0 5px 14px rgba(128, 0, 0, .08);
    }
    .inventory-tally-row {
        display: flex;
        align-items: flex-start;
        justify-content: space-between;
        gap: 10px;
    }
    .inventory-tally-name {
        color: #111827;
        font-size: 13px;
        font-weight: 800;
    }
    .inventory-tally-meta {
        margin-top: 5px;
        color: #64748b;
        font-size: 11px;
    }
    .inventory-tally-actions {
        display: none;
        justify-content: flex-end;
        margin-top: 10px;
        padding-top: 9px;
        border-top: 1px solid #e2e8f0;
    }
    .inventory-tally-item.selected .inventory-tally-actions {
        display: flex;
    }
    .inventory-issue-button {
        min-height: 30px;
        padding: 6px 13px;
        border: 1px solid #800000;
        border-radius: 6px;
        background: #800000;
        color: #fff;
        font: inherit;
        font-size: 11px;
        font-weight: 900;
        cursor: pointer;
        transition: background-color .18s ease, border-color .18s ease, color .18s ease;
    }
    .inventory-issue-button:hover {
        border-color: #facc15;
        background: #facc15;
        color: #111827;
    }
    .stock-badge {
        flex: 0 0 auto;
        padding: 4px 7px;
        border-radius: 999px;
        background: #dcfce7;
        color: #166534;
        font-size: 10px;
        font-weight: 900;
    }
    .stock-badge.low {
        background: #ffedd5;
        color: #c2410c;
    }
    html[data-theme="dark"] .consultation-documents,
    html[data-theme="dark"] .consult-card {
        border-color: #374151;
        background: #111827;
    }
    html[data-theme="dark"] .patient-header,
    html[data-theme="dark"] .document-card,
    html[data-theme="dark"] .inventory-tally-item {
        border-color: #374151;
        background: #1f2937;
    }
    html[data-theme="dark"] .documents-heading h2,
    html[data-theme="dark"] .inventory-drawer-heading h2,
    html[data-theme="dark"] .consult-card h3,
    html[data-theme="dark"] .patient-name,
    html[data-theme="dark"] .form-group label,
    html[data-theme="dark"] .document-card-title,
    html[data-theme="dark"] .inventory-tally-name,
    html[data-theme="dark"] .btn-cancel {
        color: #f8fafc;
    }
    html[data-theme="dark"] .form-control,
    html[data-theme="dark"] .choice-card,
    html[data-theme="dark"] .inventory-tally-trigger,
    html[data-theme="dark"] .documents-panel-trigger,
    html[data-theme="dark"] .documents-panel-close {
        border-color: #4b5563;
        background: #0f172a;
        color: #f8fafc;
    }
    html[data-theme="dark"] .mar-required {
        background: #2b1720;
    }
    @media (max-width: 1180px) {
        .form-grid-2 {
            grid-template-columns: 1fr;
        }
    }
    @media (max-width: 820px) {
        .consultation-documents {
            flex-basis: 260px;
            width: 260px;
        }
        .document-list {
            grid-template-columns: 1fr;
        }
        .patient-header,
        .patient-header-actions {
            align-items: flex-start;
            flex-direction: column;
        }
        .consultation-date {
            text-align: left;
        }
    }
    @media (max-width: 520px) {
        .form-grid-2 {
            grid-template-columns: 1fr;
        }
        .consult-card,
        .consultation-documents {
            padding: 16px;
        }
        .btn-save {
            width: 100%;
        }
        .medicine-selection-row {
            grid-template-columns: minmax(360px, 1fr) auto;
        }
        .form-actions {
            align-items: stretch;
            flex-direction: column;
        }
        .btn-cancel {
            text-align: center;
        }
    }

    /* Right-side consultation utility ecosystem */
    .consultation-workspace {
        display: block;
        overflow: visible;
        padding-right: 144px;
    }
    .consultation-main {
        width: 100%;
        min-width: 0;
    }
    .medicine-selection-row {
        grid-template-columns: minmax(0, 1fr);
    }
    .consultation-utility-rail {
        position: fixed;
        z-index: 1049;
        top: 50%;
        right: 18px;
        display: grid;
        gap: 9px;
        width: 126px;
        transform: translateY(-50%);
        transition: right .3s ease, opacity .18s ease, transform .18s ease, visibility .18s ease;
    }
    .consultation-utility-rail.panel-open {
        right: 338px;
    }
    .consultation-utility-rail.quick-actions-active {
        visibility: hidden;
        opacity: 0;
        pointer-events: none;
        transform: translateY(-50%) translateX(28px);
    }
    .utility-rail-button {
        display: flex;
        align-items: center;
        gap: 9px;
        width: 100%;
        min-height: 48px;
        padding: 8px 10px;
        border: 1px solid #cbd5e1;
        border-radius: 7px;
        background: #fff;
        color: #334155;
        box-shadow: 0 5px 16px rgba(15, 23, 42, .12);
        font-size: 11px;
        font-weight: 800;
        line-height: 1.2;
        text-align: left;
        cursor: pointer;
        transition: background-color .18s ease, border-color .18s ease, color .18s ease;
    }
    .utility-rail-button:hover,
    .utility-rail-button.active {
        border-color: #800000;
        background: #800000;
        color: #fff;
    }
    .utility-rail-button svg {
        width: 21px;
        height: 21px;
        flex: 0 0 auto;
    }
    .utility-rail-count {
        display: inline-grid;
        place-items: center;
        min-width: 19px;
        height: 19px;
        margin-left: auto;
        padding: 0 5px;
        border-radius: 999px;
        background: #facc15;
        color: #111827;
        font-size: 9px;
    }
    #right-utility-panel {
        position: fixed;
        z-index: 1050;
        top: 0;
        right: -350px;
        width: 320px;
        height: 100vh;
        padding: 20px;
        overflow-y: auto;
        border-left: 1px solid #e2e8f0;
        background: #fff;
        color: #111827;
        box-shadow: -2px 0 8px rgba(0, 0, 0, .18);
        transition: right .3s ease, width .3s ease;
    }
    #right-utility-panel.open {
        right: 0;
    }
    #right-utility-panel.is-expanded {
        width: min(1100px, calc(100vw - 72px));
    }
    .consultation-utility-rail.panel-expanded {
        visibility: hidden;
        opacity: 0;
        pointer-events: none;
    }
    .utility-panel-header {
        display: flex;
        align-items: center;
        gap: 10px;
        margin: -20px -20px 18px;
        padding: 18px 20px;
        border-bottom: 1px solid rgba(255, 255, 255, .16);
        background: linear-gradient(135deg, #70131b 0%, #8f2230 100%);
    }
    .utility-panel-header > svg {
        width: 23px;
        height: 23px;
        color: #ffffff;
    }
    .utility-panel-title {
        margin: 0;
        color: #ffffff !important;
        font-size: 18px;
        font-weight: 900;
    }
    .utility-panel-header,
    .utility-panel-header * {
        color: #ffffff;
    }
    .utility-panel-header > svg {
        color: #ffffff !important;
        stroke: currentColor;
    }
    #close-utility-panel {
        position: relative;
        overflow: hidden;
        display: grid;
        place-items: center;
        width: 32px;
        height: 32px;
        flex: 0 0 32px;
        margin-left: auto;
        padding: 0;
        border: 1px solid #facc15;
        border-radius: 999px;
        background: linear-gradient(90deg, #facc15 0 50%, rgba(255, 255, 255, .12) 50% 100%);
        background-size: 205% 100%;
        background-position: 100% 0;
        color: #ffffff;
        font-size: 20px;
        line-height: 1;
        cursor: pointer;
        transition: background-position .32s ease, color .18s ease, transform .18s ease, box-shadow .18s ease;
    }
    #close-utility-panel:hover,
    #close-utility-panel:focus {
        border-color: #facc15;
        background-position: 0 0;
        color: #70131b !important;
        transform: rotate(90deg);
        box-shadow: 0 8px 18px rgba(15, 23, 42, .2);
        outline: none;
    }
    #expand-utility-panel {
        position: absolute;
        top: 50%;
        left: 8px;
        z-index: 2;
        display: none;
        place-items: center;
        width: 24px;
        height: 36px;
        padding: 0;
        border: 0;
        border-radius: 0;
        background: transparent;
        color: #70131b;
        font: inherit;
        font-size: 24px;
        font-weight: 900;
        line-height: 1;
        cursor: pointer;
        transform: translateY(-50%);
        transition: color .18s ease, transform .18s ease;
    }
    #expand-utility-panel.is-visible {
        display: grid;
    }
    #expand-utility-panel:hover,
    #expand-utility-panel:focus {
        background: transparent;
        color: #facc15;
        transform: translateY(-50%) scale(1.12);
        outline: none;
    }
    .utility-panel-pane {
        display: none;
    }
    .utility-panel-pane.active {
        display: block;
    }
    .utility-pane-note {
        margin: -6px 0 15px;
        color: #64748b;
        font-size: 12px;
        line-height: 1.5;
    }
    .inventory-panel-search {
        display: flex;
        align-items: center;
        justify-content: flex-end;
        gap: 8px;
        min-height: 38px;
        margin: -4px 0 14px;
    }
    .inventory-panel-search-wrap {
        width: 0;
        flex: 0 0 0;
        opacity: 0;
        overflow: hidden;
        pointer-events: none;
        transform: translateX(8px) scaleX(.96);
        transform-origin: right center;
        transition:
            width .28s cubic-bezier(.22, 1, .36, 1),
            flex-basis .28s cubic-bezier(.22, 1, .36, 1),
            opacity .2s ease,
            transform .24s cubic-bezier(.22, 1, .36, 1);
    }
    .inventory-panel-search.is-open .inventory-panel-search-wrap {
        width: calc(100% - 44px);
        flex: 1 1 auto;
        opacity: 1;
        pointer-events: auto;
        transform: translateX(0) scaleX(1);
    }
    .inventory-panel-search-input {
        width: 100%;
        height: 36px;
        padding: 7px 9px;
        border: 0;
        border-bottom: 2px solid #8f2230;
        border-radius: 0 0 9px 9px;
        background: transparent;
        color: #111827;
        font: inherit;
        font-size: 12px;
        font-weight: 700;
        outline: none;
        transition: border-color .18s ease, transform .18s ease;
    }
    .inventory-panel-search-input::placeholder {
        color: #7f1d2d;
        opacity: 1;
    }
    .inventory-panel-search-input:focus {
        border-bottom-color: #70131b;
        transform: translateY(-1px);
    }
    .inventory-panel-search-toggle {
        display: grid;
        place-items: center;
        width: 36px;
        height: 36px;
        min-width: 36px;
        padding: 0;
        border: 1px solid #8f2230;
        border-radius: 999px;
        background: linear-gradient(135deg, #70131b, #8f2230);
        color: #ffffff;
        box-shadow:
            0 0 0 2px rgba(112, 19, 27, .1),
            0 7px 15px rgba(112, 19, 27, .18);
        cursor: pointer;
        transition: background-color .18s ease, border-color .18s ease, color .18s ease, box-shadow .18s ease;
    }
    .inventory-panel-search-toggle svg {
        width: 19px;
        height: 19px;
        stroke-width: 2;
    }
    .inventory-panel-search-toggle:hover,
    .inventory-panel-search-toggle:focus,
    .inventory-panel-search-toggle.is-open {
        border-color: #facc15;
        background: #facc15;
        color: #111827;
        box-shadow:
            0 0 0 2px rgba(250, 204, 21, .16),
            0 9px 18px rgba(112, 19, 27, .16);
        outline: none;
    }
    .inventory-search-empty {
        display: none;
        padding: 22px 12px;
        border: 1px dashed #cbd5e1;
        border-radius: 8px;
        color: #64748b;
        font-size: 12px;
        line-height: 1.5;
        text-align: center;
    }
    .inventory-search-empty.is-visible {
        display: block;
    }
    .consultation-treatment-table-wrap {
        width: 100%;
        overflow-x: auto;
        border: 1px solid #cbd5e1;
        border-radius: 8px;
        background: #ffffff;
    }
    .consultation-treatment-table {
        width: 100%;
        min-width: 920px;
        border-collapse: collapse;
        table-layout: fixed;
    }
    .consultation-treatment-table th,
    .consultation-treatment-table td {
        padding: 10px 9px;
        border-right: 1px solid #cbd5e1;
        border-bottom: 1px solid #cbd5e1;
        vertical-align: top;
    }
    .consultation-treatment-table th:last-child,
    .consultation-treatment-table td:last-child {
        border-right: 0;
    }
    .consultation-treatment-table tbody tr:last-child td {
        border-bottom: 0;
    }
    .consultation-treatment-table th {
        background: #70131b;
        color: #ffffff;
        font-size: 10px;
        font-weight: 900;
        line-height: 1.35;
        text-align: center;
        text-transform: uppercase;
    }
    .consultation-treatment-table td {
        color: #1f2937;
        font-size: 11px;
        line-height: 1.45;
    }
    .consultation-treatment-table tbody tr:nth-child(even) {
        background: #f8fafc;
    }
    .consultation-treatment-table tbody tr:hover {
        background: #fffbea;
    }
    .consultation-treatment-table .treatment-date-col { width: 92px; }
    .consultation-treatment-table .treatment-time-col { width: 76px; }
    .consultation-treatment-table .treatment-service-col { width: 120px; }
    .consultation-treatment-table .treatment-complaint-col { width: 220px; }
    .consultation-treatment-table .treatment-medicine-col { width: 165px; }
    .consultation-treatment-table .treatment-quantity-col { width: 62px; text-align: center; }
    .consultation-treatment-table .treatment-staff-col { width: 145px; }
    .treatment-table-entry {
        display: block;
        margin-bottom: 6px;
    }
    .treatment-table-entry:last-child {
        margin-bottom: 0;
    }
    .treatment-table-entry-label {
        display: block;
        margin-bottom: 2px;
        color: #70131b;
        font-size: 8px;
        font-weight: 900;
        text-transform: uppercase;
    }
    .treatment-table-entry-value {
        display: block;
    }
    .consultation-treatment-empty {
        padding: 28px !important;
        color: #64748b !important;
        text-align: center;
    }
    html[data-theme="dark"] #right-utility-panel,
    html[data-theme="dark"] .utility-rail-button,
    html[data-theme="dark"] #close-utility-panel {
        border-color: #374151;
        background: #111827;
        color: #f8fafc;
    }
    html[data-theme="dark"] .utility-panel-title {
        color: #f8fafc;
    }
    html[data-theme="dark"] .utility-panel-header {
        border-bottom-color: rgba(255, 255, 255, .16);
        background: linear-gradient(135deg, #70131b 0%, #8f2230 100%);
    }
    html[data-theme="dark"] .utility-panel-header > svg,
    html[data-theme="dark"] .utility-panel-title {
        color: #ffffff;
    }
    html[data-theme="dark"] #close-utility-panel {
        border-color: #facc15;
        background: linear-gradient(90deg, #facc15 0 50%, rgba(255, 255, 255, .12) 50% 100%);
        background-size: 205% 100%;
        background-position: 100% 0;
        color: #ffffff;
    }
    html[data-theme="dark"] #close-utility-panel:hover,
    html[data-theme="dark"] #close-utility-panel:focus {
        border-color: #facc15;
        background-position: 0 0;
        color: #70131b;
    }
    html[data-theme="dark"] .consultation-treatment-table-wrap {
        border-color: #374151;
        background: #111827;
    }
    html[data-theme="dark"] .consultation-treatment-table th,
    html[data-theme="dark"] .consultation-treatment-table td {
        border-color: #374151;
    }
    html[data-theme="dark"] .consultation-treatment-table td {
        color: #e5e7eb;
    }
    html[data-theme="dark"] .treatment-table-entry-label {
        color: #facc15;
    }
    html[data-theme="dark"] .consultation-treatment-table tbody tr:nth-child(even) {
        background: #1f2937;
    }
    html[data-theme="dark"] .consultation-treatment-table tbody tr:hover {
        background: #332d19;
    }
    html[data-theme="dark"] .inventory-panel-search-input {
        border-bottom-color: #facc15;
        color: #ffffff;
    }
    html[data-theme="dark"] .inventory-panel-search-input::placeholder {
        color: #cbd5e1;
    }
    html[data-theme="dark"] .inventory-panel-search-toggle {
        border-color: #facc15;
        background: #800000;
        color: #ffffff;
    }
    html[data-theme="dark"] .inventory-panel-search-toggle:hover,
    html[data-theme="dark"] .inventory-panel-search-toggle:focus,
    html[data-theme="dark"] .inventory-panel-search-toggle.is-open {
        background: #facc15;
        color: #111827;
    }
    html[data-theme="dark"] .inventory-search-empty {
        border-color: #465267;
        color: #cbd5e1;
    }
    @media (max-width: 760px) {
        .consultation-workspace {
            padding-right: 54px;
        }
        .consultation-utility-rail {
            right: 7px;
            width: 44px;
        }
        .consultation-utility-rail.panel-open {
            right: 327px;
        }
        #right-utility-panel.is-expanded {
            width: 100vw;
        }
        .utility-rail-button {
            justify-content: center;
            min-height: 44px;
            padding: 8px;
        }
        .utility-rail-button span:not(.utility-rail-count) {
            display: none;
        }
        .utility-rail-count {
            position: absolute;
            margin: -27px 0 0 27px;
        }
    }

    /* Student Health Profile visual language for the consultation form */
    .consultation-main {
        --clinic-form-maroon: #7f1d2d;
        --clinic-form-maroon-dark: #5f0012;
        --clinic-form-yellow: #facc15;
        --clinic-form-field: #f8fafc;
        --clinic-form-border: #d1d5db;
        font-family: "Segoe UI", Arial, sans-serif;
        color: #111827;
    }
    .consultation-main .consult-card {
        padding: 22px 24px;
        border: 1px solid rgba(127, 29, 45, .16);
        border-radius: 16px;
        background: rgba(255, 255, 255, .98);
        box-shadow: 0 10px 24px rgba(15, 23, 42, .06);
    }
    .consultation-main .patient-header {
        position: relative;
        display: grid;
        grid-template-columns: minmax(0, 1fr) auto;
        align-items: center;
        min-height: 175px;
        padding: 24px 28px;
        overflow: hidden;
        border-left: 0;
        border-top: 5px solid var(--clinic-form-maroon);
        background:
            radial-gradient(circle at top left, rgba(250, 204, 21, .16), transparent 34%),
            linear-gradient(180deg, #fff 0%, #fffaf2 100%);
        box-shadow: 0 14px 30px rgba(127, 29, 45, .08);
    }
    .consultation-main .patient-header::after {
        content: "";
        position: absolute;
        right: 0;
        bottom: 0;
        left: 0;
        height: 3px;
        background: linear-gradient(90deg, transparent, #facc15, transparent);
        opacity: .72;
    }
    .patient-identity {
        position: relative;
        z-index: 1;
        display: flex;
        align-items: center;
        gap: 18px;
        min-width: 0;
        text-align: left;
    }
    .patient-identity-copy {
        min-width: 0;
    }
    .patient-avatar {
        display: grid;
        place-items: center;
        width: 92px;
        height: 92px;
        flex: 0 0 92px;
        overflow: hidden;
        border: 3px solid #ffffff;
        border-radius: 9px;
        background: linear-gradient(135deg, #70131b, #9f1d35);
        color: #facc15;
        box-shadow:
            0 0 0 2px rgba(112, 19, 27, .22),
            0 10px 24px rgba(112, 19, 27, .2);
        font-size: 25px;
        font-weight: 900;
    }
    .patient-avatar img {
        width: 100%;
        height: 100%;
        object-fit: cover;
    }
    .consultation-main .patient-name {
        max-width: 100%;
        margin: 0 0 9px;
        color: #70131b;
        font-size: clamp(1.65rem, 3vw, 2.25rem);
        font-weight: 900;
        line-height: 1.05;
        overflow-wrap: anywhere;
    }
    .consultation-main .patient-badges {
        justify-content: flex-start;
        margin-bottom: 8px;
    }
    .consultation-main .patient-badge {
        border: 1px solid rgba(127, 29, 45, .12);
        border-radius: 10px;
        background: #fff;
        color: #4b5563;
        font-size: .68rem;
        font-weight: 800;
    }
    .patient-meta {
        display: grid;
        gap: 4px;
        color: #4b5563;
        font-size: .75rem;
        font-weight: 650;
        line-height: 1.35;
    }
    .patient-meta-row {
        display: flex;
        align-items: baseline;
        gap: 7px;
        min-width: 0;
    }
    .patient-meta-label {
        flex: 0 0 auto;
        color: #8f2230;
        font-size: .64rem;
        font-weight: 900;
        text-transform: uppercase;
    }
    .patient-meta-value {
        min-width: 0;
        overflow-wrap: anywhere;
    }
    .consultation-main .consultation-date {
        display: flex;
        align-items: center;
        flex-direction: column;
        justify-self: end;
        min-width: 142px;
        padding: 13px 14px;
        border: 1px solid rgba(127, 29, 45, .14);
        border-radius: 12px;
        background: rgba(255, 255, 255, .72);
        color: #111827;
        font-size: .9rem;
        font-weight: 800;
        box-shadow: 0 8px 18px rgba(112, 19, 27, .06);
        text-align: center;
    }
    .consultation-source-badge {
        margin: 6px 0 8px;
    }
    .consultation-main .consultation-date .source-walkin {
        color: #111111;
    }
    .consultation-main .consultation-date span {
        color: #6b7280;
        font-size: .72rem;
        font-weight: 700;
        text-transform: uppercase;
    }
    .consultation-main .consult-card > h3,
    .consultation-main .medicine-header h3 {
        margin: 0;
        color: var(--clinic-form-maroon);
        font-size: 1.05rem;
        font-weight: 800;
    }
    .consult-section-heading {
        display: flex;
        align-items: center;
        gap: 11px;
        margin: -22px -24px 18px;
        padding: 14px 18px;
        border-bottom: 1px solid rgba(250, 204, 21, .24);
        border-radius: 15px 15px 0 0;
        background: linear-gradient(135deg, #70131b 0%, #8f2230 100%);
        box-shadow: 0 8px 18px rgba(112, 19, 27, .14);
    }
    .consult-section-icon {
        display: grid;
        place-items: center;
        width: 34px;
        height: 34px;
        flex: 0 0 34px;
        border: 1px solid rgba(250, 204, 21, .58);
        border-radius: 10px;
        background: rgba(255, 255, 255, .1);
        color: #facc15;
        box-shadow: inset 0 1px 0 rgba(255, 255, 255, .12);
    }
    .consult-section-icon svg {
        width: 20px;
        height: 20px;
    }
    .consult-section-copy {
        min-width: 0;
    }
    .consult-section-copy h3 {
        margin: 2px 0 2px !important;
        padding: 0 !important;
        border: 0 !important;
        color: #ffffff !important;
        font-size: 1.11rem !important;
        line-height: 1.2 !important;
    }
    .consult-section-copy p {
        margin: 2px 0 0 !important;
        color: #ffffff !important;
        font-size: .87rem !important;
        font-weight: 600;
        letter-spacing: 0;
        opacity: 1 !important;
    }
    .consultation-main .medicine-header {
        display: block;
        width: 100%;
        margin-bottom: 0;
    }
    .consultation-main .medicine-header .consult-section-heading {
        width: auto;
    }
    .consultation-main .form-grid-2 {
        gap: 12px;
    }
    .consultation-main .form-group {
        position: relative;
        margin-bottom: 12px;
        padding: 10px 12px;
        border: 1px solid rgba(127, 29, 45, .12);
        border-radius: 12px;
        background: #fff;
    }
    .consultation-main .form-group label {
        display: block;
        margin: 0 0 5px;
        color: #6b7280;
        font-size: .74rem;
        font-weight: 700;
        letter-spacing: .06em;
        text-transform: uppercase;
    }
    .consultation-main .form-control {
        min-height: 46px;
        padding: 10px 12px;
        border: 1.5px solid rgba(127, 29, 45, .42);
        border-radius: 12px;
        background: linear-gradient(180deg, #fffafb 0%, #fff6f7 100%);
        box-shadow:
            0 8px 18px rgba(127, 29, 45, .06),
            inset 0 1px 0 rgba(255, 255, 255, .82);
        color: #111827;
        font-family: "Segoe UI", Arial, sans-serif;
        font-size: .9rem;
        font-weight: 700;
        transition: border-color .2s ease, box-shadow .2s ease, background-color .2s ease;
    }
    .consultation-main textarea.form-control {
        min-height: 118px;
        line-height: 1.55;
        resize: vertical;
    }
    .consultation-main .form-control:focus {
        border-color: var(--clinic-form-maroon);
        outline: none;
        background: linear-gradient(180deg, #fffafb 0%, #fff6f7 100%);
        box-shadow:
            0 0 0 .18rem rgba(127, 29, 45, .12),
            0 10px 22px rgba(127, 29, 45, .10);
    }
    .consultation-main .form-control[readonly] {
        border-color: rgba(209, 213, 219, .9);
        background: #f4f1f1;
        color: #4b5563;
        box-shadow: none;
    }
    .consultation-main .form-control::placeholder {
        color: #9ca3af;
        font-weight: 600;
    }
    .consultation-main select.form-control {
        cursor: pointer;
    }
    .clinic-select-shell {
        position: relative;
        width: 100%;
    }
    .clinic-select-native {
        position: absolute !important;
        width: 1px !important;
        height: 1px !important;
        padding: 0 !important;
        opacity: 0;
        pointer-events: none;
    }
    .clinic-select-display {
        position: relative;
        width: 100%;
        min-height: 52px;
        padding: 13px 52px 13px 16px;
        border: 1px solid rgba(127, 29, 29, .28);
        border-radius: 16px;
        background:
            radial-gradient(circle at top right, rgba(250, 204, 21, .1), transparent 36%),
            linear-gradient(180deg, #fff 0%, #fff8f6 100%);
        box-shadow: 0 10px 20px rgba(15, 23, 42, .07), inset 0 1px 0 rgba(255, 255, 255, .86);
        color: #111827;
        font: inherit;
        font-size: .86rem;
        font-weight: 750;
        text-align: left;
        cursor: pointer;
        transition: border-color .18s ease, box-shadow .18s ease, transform .18s ease;
    }
    .clinic-select-display::before {
        content: "";
        position: absolute;
        top: 50%;
        right: 42px;
        width: 1px;
        height: 24px;
        background: rgba(148, 163, 184, .28);
        transform: translateY(-50%);
    }
    .clinic-select-display::after {
        content: "";
        position: absolute;
        top: 50%;
        right: 19px;
        width: 9px;
        height: 9px;
        border-right: 2px solid #800000;
        border-bottom: 2px solid #800000;
        transform: translateY(-70%) rotate(45deg);
        transition: transform .18s ease;
    }
    .clinic-select-display:hover,
    .clinic-select-display.is-open {
        border-color: #800000;
        box-shadow: 0 0 0 4px rgba(128, 0, 0, .06), 0 13px 24px rgba(128, 0, 0, .1);
        transform: translateY(-1px);
    }
    .clinic-select-display.is-open::after {
        transform: translateY(-20%) rotate(225deg);
    }
    .clinic-select-display:disabled {
        cursor: not-allowed;
        opacity: .72;
        transform: none;
    }
    .clinic-select-menu {
        position: absolute;
        z-index: 90;
        top: calc(100% + 9px);
        left: 0;
        right: 0;
        display: none;
        gap: 8px;
        max-height: 270px;
        padding: 12px;
        overflow-y: auto;
        border: 1px solid rgba(128, 0, 0, .14);
        border-radius: 16px;
        background: rgba(255, 255, 255, .98);
        box-shadow: 0 18px 34px rgba(15, 23, 42, .15);
        backdrop-filter: blur(8px);
    }
    .clinic-select-shell.is-open .clinic-select-menu {
        display: grid;
    }
    .clinic-select-option {
        width: 100%;
        padding: 11px 13px;
        border: 1px solid rgba(148, 163, 184, .24);
        border-radius: 999px;
        background: linear-gradient(180deg, #fff 0%, #f8fafc 100%);
        color: #1e293b;
        font: inherit;
        font-size: .8rem;
        font-weight: 800;
        text-align: left;
        cursor: pointer;
        transition: background-color .18s ease, border-color .18s ease, color .18s ease, transform .18s ease;
    }
    .clinic-select-option:hover,
    .clinic-select-option.is-selected {
        border-color: #800000;
        background: linear-gradient(135deg, #800000, #70131b);
        color: #facc15;
        transform: translateY(-1px);
    }
    .clinic-select-option:disabled {
        display: none;
    }
    html[data-theme="dark"] .clinic-select-display,
    html[data-theme="dark"] .clinic-select-menu,
    html[data-theme="dark"] .clinic-select-option {
        border-color: #4b5563;
        background: #0f172a;
        color: #f8fafc;
    }
    html[data-theme="dark"] .clinic-select-option:hover,
    html[data-theme="dark"] .clinic-select-option.is-selected {
        border-color: #facc15;
        background: #800000;
        color: #facc15;
    }
    .consultation-main .mar-required {
        border-color: rgba(127, 29, 45, .52);
        background: linear-gradient(180deg, #fffafb 0%, #fff6f7 100%);
    }
    .consultation-main .form-help {
        margin-top: 7px;
        color: #7f1d2d;
        font-size: .75rem;
        font-weight: 650;
        line-height: 1.45;
    }
    .consultation-main .choice-grid {
        gap: 10px;
    }
    .consultation-main .choice-card {
        min-height: 44px;
        padding: 10px 14px;
        border: 1px solid #cbd5e1;
        border-radius: 12px;
        background: #f8fafc;
        color: #334155;
        font-size: .84rem;
        font-weight: 700;
    }
    .consultation-main .choice-input:checked + .choice-card {
        border-color: transparent;
        background: linear-gradient(135deg, var(--clinic-form-maroon) 0%, var(--clinic-form-maroon-dark) 100%);
        color: #fff;
        box-shadow: 0 8px 16px rgba(127, 29, 45, .2);
    }
    .consultation-main .selected-stock {
        border: 1px solid rgba(22, 101, 52, .14);
        border-radius: 10px;
        font-size: .76rem;
    }
    .consultation-main .btn-save {
        position: relative;
        z-index: 0;
        overflow: hidden;
        border-radius: 12px;
        background: linear-gradient(135deg, var(--clinic-form-maroon) 0%, var(--clinic-form-maroon-dark) 100%);
        color: #ffffff;
        box-shadow: 0 10px 22px rgba(127, 29, 45, .28);
        font-family: "Segoe UI", Arial, sans-serif;
        font-size: .9rem;
        font-weight: 700;
        isolation: isolate;
    }
    .consultation-main .btn-save::before {
        content: "";
        position: absolute;
        z-index: 0;
        inset: 0;
        background: #facc15;
        transform: translateX(-105%);
        transition: transform .34s ease;
    }
    .consultation-main .btn-save span {
        position: relative;
        z-index: 1;
    }
    .consultation-main .btn-save:hover,
    .consultation-main .btn-save:focus {
        background: var(--clinic-form-maroon);
        color: var(--clinic-form-maroon);
        box-shadow: 0 13px 26px rgba(127, 29, 45, .24);
        outline: none;
    }
    .consultation-main .btn-save:hover::before,
    .consultation-main .btn-save:focus::before {
        transform: translateX(0);
    }
    .consultation-main .btn-save.is-finalizing {
        color: var(--clinic-form-maroon);
        pointer-events: none;
    }
    .consultation-main .btn-save.is-finalizing::before {
        transform: translateX(0);
    }
    .consultation-success-overlay {
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
    .consultation-success-overlay.is-open {
        display: flex;
    }
    .consultation-success-card {
        width: min(360px, calc(100vw - 30px));
        padding: 28px 22px;
        border: 1px solid rgba(250, 204, 21, .48);
        border-radius: 18px;
        background: #ffffff;
        box-shadow: 0 24px 54px rgba(15, 23, 42, .3);
        text-align: center;
        animation: consultationSuccessPop .38s cubic-bezier(.2, .9, .25, 1.2);
    }
    .consultation-success-check {
        width: 76px;
        height: 76px;
        display: grid;
        place-items: center;
        margin: 0 auto 14px;
        border-radius: 999px;
        background: #70131b;
        color: #facc15;
        box-shadow: 0 12px 26px rgba(112, 19, 27, .24);
    }
    .consultation-success-check svg {
        width: 39px;
        height: 39px;
        stroke-width: 2.8;
    }
    .consultation-success-card strong {
        display: block;
        color: #70131b;
        font-size: 20px;
        font-weight: 900;
    }
    @keyframes consultationSuccessPop {
        from { opacity: 0; transform: scale(.72) translateY(18px); }
        to { opacity: 1; transform: scale(1) translateY(0); }
    }
    .consultation-main .btn-cancel {
        position: relative;
        z-index: 0;
        overflow: hidden;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        gap: 8px;
        min-width: 122px;
        min-height: 46px;
        padding: 11px 18px;
        border: 1px solid #70131b;
        border-radius: 12px;
        background: #eef0f3;
        color: #70131b;
        box-shadow: 0 7px 16px rgba(15, 23, 42, .08);
        font-family: "Segoe UI", Arial, sans-serif;
        font-size: .86rem;
        font-weight: 800;
        text-decoration: none;
        isolation: isolate;
        transition: color .2s ease, border-color .2s ease, box-shadow .2s ease;
    }
    .consultation-main .btn-cancel::before {
        content: "";
        position: absolute;
        z-index: -1;
        inset: 0;
        background: #facc15;
        transform: translateX(-105%);
        transition: transform .32s ease;
    }
    .consultation-main .btn-cancel svg {
        width: 17px;
        height: 17px;
        flex: 0 0 17px;
    }
    .consultation-main .btn-cancel:hover,
    .consultation-main .btn-cancel:focus {
        border-color: #facc15;
        color: #70131b;
        box-shadow: 0 10px 20px rgba(112, 19, 27, .14);
        outline: none;
    }
    .consultation-main .btn-cancel:hover::before,
    .consultation-main .btn-cancel:focus::before {
        transform: translateX(0);
    }
    .consultation-form-alert {
        display: grid;
        gap: 5px;
        margin: 0 0 18px;
        padding: 13px 16px;
        border: 1px solid #fca5a5;
        border-left: 4px solid #b91c1c;
        border-radius: 10px;
        background: #fff1f2;
        color: #7f1d1d;
        font-size: .84rem;
        line-height: 1.45;
    }
    .consultation-form-alert strong {
        color: #991b1b;
        font-weight: 800;
    }
    .consultation-form-alert ul {
        margin: 3px 0 0 18px;
        padding: 0;
    }
    .consultation-main .form-group {
        padding: 0;
        border: 0;
        border-radius: 0;
        background: transparent;
        box-shadow: none;
    }
    .consultation-main .form-control {
        min-height: 44px;
        padding: 10px 0;
        border: 0;
        border-bottom: 2px solid var(--clinic-form-maroon);
        border-radius: 0;
        background: transparent;
        box-shadow: none;
    }
    .consultation-main textarea.form-control {
        min-height: 108px;
        padding: 10px 0;
        border-bottom-width: 2px;
    }
    .consultation-main .form-control:focus,
    .consultation-main .form-control:hover {
        border-color: var(--clinic-form-maroon);
        background: transparent;
        box-shadow: 0 2px 0 rgba(127, 29, 45, .12);
    }
    .consultation-main .mar-required {
        border: 0;
        border-bottom: 2px solid var(--clinic-form-maroon);
        border-radius: 0;
        background: transparent;
    }
    .consultation-main .clinic-select-display {
        min-height: 44px;
        padding: 10px 32px 10px 0;
        border: 0;
        border-bottom: 2px solid var(--clinic-form-maroon);
        border-radius: 0;
        background: transparent;
        box-shadow: none;
    }
    .consultation-main .clinic-select-display::before {
        display: none;
    }
    .consultation-main .clinic-select-display::after {
        right: 4px;
    }
    .consultation-main .clinic-select-menu {
        gap: 8px;
        padding: 10px;
        border-radius: 8px;
    }
    .consultation-main .clinic-select-option {
        position: relative;
        isolation: isolate;
        min-height: 38px;
        overflow: hidden;
        padding: 0 12px;
        border: 1px solid #ead3d7;
        border-radius: 8px;
        background: #ffffff;
        color: #70131B;
        font-size: 13px;
        font-weight: 900;
        transform: none;
    }
    .consultation-main .clinic-select-option::after {
        content: "";
        position: absolute;
        z-index: -1;
        top: -40%;
        left: -130%;
        width: 120%;
        height: 180%;
        background: linear-gradient(115deg, rgba(255, 247, 181, 0) 0%, rgba(255, 247, 181, .72) 45%, rgba(255, 247, 181, 0) 100%);
        transform: skewX(-20deg);
        transition: left 1.05s ease;
        pointer-events: none;
    }
    .consultation-main .clinic-select-option:hover,
    .consultation-main .clinic-select-option:focus-visible {
        border-color: #facc15;
        background: #facc15;
        color: #70131B;
        outline: none;
        box-shadow: 0 8px 18px rgba(250, 204, 21, .24);
    }
    .consultation-main .clinic-select-option:hover::after,
    .consultation-main .clinic-select-option:focus-visible::after {
        left: 125%;
    }
    .consultation-main .clinic-select-option.is-selected {
        border-color: #70131B;
        background: #70131B;
        color: #ffffff;
    }
    .referral-details-group {
        display: none;
        margin-top: 14px;
    }
    .referral-details-group.is-visible {
        display: block;
    }
    .referral-details-group .form-help {
        margin-top: 5px;
    }

    /* Dark mode overrides for the consultation form's health-profile styling. */
    html[data-theme="dark"] .consultation-main {
        --clinic-form-field: #172033;
        --clinic-form-border: #3b475b;
        color: #f8fafc;
    }
    html[data-theme="dark"] .consultation-main .consult-card {
        border-color: #354158;
        background: #101827;
        box-shadow: 0 12px 28px rgba(0, 0, 0, .24);
    }
    html[data-theme="dark"] .consultation-main .patient-header {
        border-top-color: #9f1d35;
        background:
            radial-gradient(circle at top left, rgba(250, 204, 21, .08), transparent 34%),
            linear-gradient(180deg, #202c3d 0%, #1b2637 100%);
    }
    html[data-theme="dark"] .consultation-main .patient-name,
    html[data-theme="dark"] .consultation-main .consultation-date,
    html[data-theme="dark"] .consultation-main .consult-card > h3,
    html[data-theme="dark"] .consultation-main .medicine-header h3 {
        color: #ffffff;
    }
    html[data-theme="dark"] .consultation-main .consult-card > h3,
    html[data-theme="dark"] .consultation-main .medicine-header h3 {
        border-bottom-color: rgba(248, 113, 113, .16);
    }
    html[data-theme="dark"] .consultation-main .consultation-date span,
    html[data-theme="dark"] .consultation-main .form-group label {
        color: #cbd5e1;
    }
    html[data-theme="dark"] .patient-avatar {
        border-color: #263348;
        box-shadow:
            0 0 0 2px rgba(250, 204, 21, .45),
            0 10px 24px rgba(0, 0, 0, .32);
    }
    html[data-theme="dark"] .patient-meta {
        color: #e2e8f0;
    }
    html[data-theme="dark"] .patient-meta-label {
        color: #facc15;
    }
    html[data-theme="dark"] .consultation-main .consultation-date {
        border-color: #465267;
        background: rgba(13, 22, 40, .72);
        color: #ffffff;
    }
    html[data-theme="dark"] .consultation-main .consultation-date .source-walkin {
        color: #111111;
    }
    html[data-theme="dark"] .consult-section-heading {
        border-bottom-color: rgba(250, 204, 21, .24);
        background: linear-gradient(135deg, #70131b 0%, #8f2230 100%);
    }
    html[data-theme="dark"] .consult-section-icon {
        border-color: rgba(250, 204, 21, .34);
    }
    html[data-theme="dark"] .consultation-main .patient-badge {
        border-color: #536076;
        background: #f8fafc;
        color: #1f2937;
    }
    html[data-theme="dark"] .consultation-main .form-group {
        border-color: #3b475b;
        background: #182235;
        box-shadow: inset 0 1px 0 rgba(255, 255, 255, .025);
    }
    html[data-theme="dark"] .consultation-main .form-control,
    html[data-theme="dark"] .consultation-main .mar-required {
        border-color: #536076;
        background: #0d1628;
        box-shadow: inset 0 1px 0 rgba(255, 255, 255, .035);
        color: #f8fafc;
        color-scheme: dark;
    }
    html[data-theme="dark"] .consultation-main .form-control:hover {
        border-color: #6b7890;
    }
    html[data-theme="dark"] .consultation-main .form-control:focus,
    html[data-theme="dark"] .consultation-main .mar-required:focus {
        border-color: #facc15;
        background: #111c30;
        box-shadow:
            0 0 0 .18rem rgba(250, 204, 21, .12),
            0 10px 22px rgba(0, 0, 0, .2);
    }
    html[data-theme="dark"] .consultation-main .form-control[readonly] {
        border-color: #465267;
        background: #202a3a;
        color: #dbe4f0;
    }
    html[data-theme="dark"] .consultation-main .form-control::placeholder {
        color: #8491a5;
        opacity: 1;
    }
    html[data-theme="dark"] .consultation-main .form-help {
        color: #fca5a5;
    }
    html[data-theme="dark"] .consultation-main .choice-card {
        border-color: #536076;
        background: #0d1628;
        color: #f8fafc;
    }
    html[data-theme="dark"] .consultation-main .choice-card:hover {
        border-color: #facc15;
        background: #17233a;
    }
    html[data-theme="dark"] .consultation-main .choice-input:checked + .choice-card {
        border-color: #9f1d35;
        background: linear-gradient(135deg, #991b32 0%, #700018 100%);
        color: #ffffff;
    }
    html[data-theme="dark"] .consultation-main .clinic-select-display {
        border-color: #536076;
        background: #0d1628;
        box-shadow: inset 0 1px 0 rgba(255, 255, 255, .035);
        color: #f8fafc;
    }
    html[data-theme="dark"] .consultation-main .clinic-select-display::before {
        background: #3b475b;
    }
    html[data-theme="dark"] .consultation-main .clinic-select-display::after {
        border-color: #facc15;
    }
    html[data-theme="dark"] .consultation-main .clinic-select-display:hover,
    html[data-theme="dark"] .consultation-main .clinic-select-display.is-open {
        border-color: #facc15;
        box-shadow: 0 0 0 4px rgba(250, 204, 21, .08);
    }
    html[data-theme="dark"] .consultation-main .clinic-select-menu {
        border-color: #465267;
        background: #111827;
        box-shadow: 0 18px 34px rgba(0, 0, 0, .35);
    }
    html[data-theme="dark"] .consultation-main .clinic-select-option {
        border-color: rgba(255, 255, 255, .16) !important;
        background: #223044 !important;
        color: #f8fafc !important;
    }
    html[data-theme="dark"] .consultation-main .clinic-select-option:hover,
    html[data-theme="dark"] .consultation-main .clinic-select-option.is-selected {
        border-color: #facc15 !important;
        background: #facc15 !important;
        color: #70131B !important;
    }
    html[data-theme="dark"] .consultation-main .btn-cancel {
        border-color: #facc15;
        background: #273145;
        color: #ffffff;
    }
    html[data-theme="dark"] .consultation-main .btn-cancel:hover {
        border-color: #facc15;
        color: #70131b;
    }
    html[data-theme="dark"] .consultation-main .medicine-entry {
        border-color: #3b475b;
        background: #111827;
        box-shadow: 0 10px 24px rgba(0, 0, 0, .24);
    }
    html[data-theme="dark"] .consultation-main .medicine-entry-title {
        color: #facc15;
    }
    html[data-theme="dark"] .consultation-main .medicine-entry-number {
        background: #8f2230;
        color: #facc15;
    }
    html[data-theme="dark"] .consultation-main .medicine-remove-button {
        border-color: #536076;
        background: #202a3a;
        color: #fca5a5;
    }
    html[data-theme="dark"] .consultation-main .medicine-remove-button:hover,
    html[data-theme="dark"] .consultation-main .medicine-remove-button:focus-visible {
        border-color: #facc15;
        background: #facc15;
        color: #70131b;
    }
    html[data-theme="dark"] .consultation-main .medicine-add-button {
        border-color: rgba(250, 204, 21, .46);
        background: #182235;
        color: #facc15;
    }
    html[data-theme="dark"] .consultation-main .medicine-add-button:hover,
    html[data-theme="dark"] .consultation-main .medicine-add-button:focus-visible {
        border-color: #facc15;
        background: #facc15;
        color: #70131b;
    }
    html[data-theme="dark"] .consultation-main .medicine-selection-count {
        color: #facc15;
    }
    html[data-theme="dark"] .consultation-main .selected-stock {
        border-color: rgba(250, 204, 21, .24);
        background: #2b2114;
        color: #facc15;
    }
    html[data-theme="dark"] .consultation-main .selected-stock.low {
        border-color: rgba(248, 113, 113, .3);
        background: #351b24;
        color: #fca5a5;
    }
    html[data-theme="dark"] .consultation-form-alert {
        border-color: rgba(248, 113, 113, .46);
        border-left-color: #f87171;
        background: #351b24;
        color: #fecaca;
    }
    html[data-theme="dark"] .consultation-form-alert strong {
        color: #fca5a5;
    }
    html[data-theme="dark"] .consultation-main .form-group {
        border: 0;
        background: transparent;
        box-shadow: none;
    }
    html[data-theme="dark"] .consultation-main .form-control,
    html[data-theme="dark"] .consultation-main .mar-required {
        border: 0;
        border-bottom: 2px solid #facc15;
        border-radius: 0;
        background: transparent;
        box-shadow: none;
        color: #f8fafc;
    }
    html[data-theme="dark"] .consultation-main .form-control:focus,
    html[data-theme="dark"] .consultation-main .form-control:hover {
        border-color: #facc15;
        background: transparent;
        box-shadow: 0 2px 0 rgba(250, 204, 21, .16);
    }
    html[data-theme="dark"] .consultation-main .clinic-select-display {
        border: 0;
        border-bottom: 2px solid #facc15;
        border-radius: 0;
        background: transparent;
        box-shadow: none;
        color: #f8fafc;
    }
    html[data-theme="dark"] .consultation-main .clinic-select-display:hover,
    html[data-theme="dark"] .consultation-main .clinic-select-display.is-open {
        border-color: #facc15;
        background: transparent;
        box-shadow: 0 2px 0 rgba(250, 204, 21, .16);
    }
    .consultation-main input.form-control[data-vital],
    .consultation-main #consultDob {
        border: 0 !important;
        border-bottom: 2px solid var(--clinic-form-maroon) !important;
        border-radius: 0 !important;
        background: transparent !important;
        box-shadow: none !important;
    }
    .consultation-main input.form-control[data-vital]:hover,
    .consultation-main input.form-control[data-vital]:focus,
    .consultation-main #consultDob:hover,
    .consultation-main #consultDob:focus,
    .consultation-main input.form-control[data-vital].is-valid,
    .consultation-main input.form-control[data-vital].is-invalid {
        border: 0 !important;
        border-bottom: 2px solid var(--clinic-form-maroon) !important;
        box-shadow: 0 2px 0 rgba(127, 29, 45, .12) !important;
    }
    html[data-theme="dark"] .consultation-main input.form-control[data-vital],
    html[data-theme="dark"] .consultation-main #consultDob,
    html[data-theme="dark"] .consultation-main input.form-control[data-vital]:hover,
    html[data-theme="dark"] .consultation-main input.form-control[data-vital]:focus,
    html[data-theme="dark"] .consultation-main input.form-control[data-vital].is-valid,
    html[data-theme="dark"] .consultation-main input.form-control[data-vital].is-invalid,
    html[data-theme="dark"] .consultation-main #consultDob:hover,
    html[data-theme="dark"] .consultation-main #consultDob:focus {
        border: 0 !important;
        border-bottom: 2px solid #facc15 !important;
        background: transparent !important;
        box-shadow: 0 2px 0 rgba(250, 204, 21, .16) !important;
    }

    /* Reference-style physical assessment layout. */
    .consultation-main .physical-assessment-card {
        overflow: hidden;
        padding: 0 24px 18px;
        border-top: 3px solid var(--clinic-form-maroon) !important;
    }
    .physical-assessment-card > .consult-section-heading {
        display: flex;
        align-items: center;
        gap: 12px;
        margin: 0 -24px 20px;
        padding: 16px 24px;
        border-bottom: 1px solid rgba(127, 29, 45, .14);
        border-radius: 13px 13px 0 0;
        background: linear-gradient(135deg, #7f1d2d 0%, #991b32 100%);
    }
    .physical-assessment-card > .consult-section-heading .consult-section-icon {
        display: grid;
        flex: 0 0 42px;
        width: 42px;
        height: 42px;
        place-items: center;
        border: 1px solid rgba(255, 255, 255, .35);
        border-radius: 50%;
        background: rgba(255, 255, 255, .12);
        color: var(--clinic-form-yellow);
    }
    .physical-assessment-card > .consult-section-heading .consult-section-icon svg {
        width: 23px;
        height: 23px;
    }
    .physical-assessment-card > .consult-section-heading h3 {
        margin: 2px 0 2px;
        color: #ffffff;
        font-size: 1.11rem;
        font-weight: 800;
        letter-spacing: 0;
    }
    .physical-assessment-card > .physical-dob-group {
        display: grid;
        grid-template-columns: minmax(0, 1.08fr) minmax(250px, .92fr);
        column-gap: 26px;
        row-gap: 8px;
        align-items: center;
        margin: 0 0 22px;
        padding: 0;
    }
    .physical-assessment-card > .physical-dob-group > label {
        grid-column: 1;
        grid-row: 1;
        margin: 0;
        color: #5b6473;
        font-size: .71rem;
        font-weight: 800;
        letter-spacing: .04em;
        text-transform: uppercase;
    }
    .physical-assessment-card > .physical-dob-group > #consultDob {
        grid-column: 1;
        grid-row: 2;
        min-height: 54px !important;
        padding: 13px 48px 13px 20px !important;
        border: 1px solid #e5e7eb !important;
        border-radius: 10px !important;
        background: linear-gradient(180deg, #ffffff 0%, #fafafa 100%) !important;
        box-shadow: 0 2px 5px rgba(15, 23, 42, .06) !important;
        color: #111827;
        font-size: .98rem;
        font-weight: 700;
    }
    .physical-assessment-card > .physical-dob-group > #consultDob:focus {
        border-color: var(--clinic-form-maroon) !important;
        box-shadow: 0 0 0 3px rgba(127, 29, 45, .1) !important;
    }
    .physical-assessment-card > .physical-dob-group > .form-help {
        grid-column: 2;
        grid-row: 2;
        align-self: center;
        display: flex;
        align-items: center;
        min-height: 44px;
        margin: 0;
        padding: 9px 14px;
        border: 1px solid #dbe6ff;
        border-radius: 8px;
        background: #f5f8ff;
        color: #4b5563;
        font-size: .8rem;
        line-height: 1.45;
    }
    .physical-assessment-card > .physical-dob-group > .form-help::before {
        content: 'i';
        display: inline-grid;
        flex: 0 0 19px;
        width: 19px;
        height: 19px;
        margin-right: 7px;
        place-items: center;
        border: 1px solid #2563eb;
        border-radius: 50%;
        color: #2563eb;
        font-weight: 800;
    }
    .physical-assessment-card > .physical-dob-group > #consultDob::after {
        content: '';
    }
    .physical-assessment-card > .form-grid-2 {
        display: grid;
        grid-template-columns: repeat(2, minmax(0, 1fr));
        gap: 14px 18px;
        margin: 0;
    }
    .physical-vitals-heading {
        display: block;
        margin: 4px 0 14px;
        padding-top: 4px;
    }
    .physical-vitals-heading .physical-assessment-icon {
        display: grid;
        flex: 0 0 42px;
        width: 42px;
        height: 42px;
        place-items: center;
        border: 1px solid rgba(127, 29, 45, .1);
        border-radius: 50%;
        background: #fff3f5;
        color: var(--clinic-form-maroon);
    }
    .physical-vitals-heading .physical-assessment-icon svg {
        width: 23px;
        height: 23px;
    }
    .physical-vitals-heading h3 {
        margin: 0;
        color: var(--clinic-form-maroon);
        font-size: 1.05rem;
        font-weight: 800;
        letter-spacing: 0;
    }
    .physical-assessment-card > .form-grid-2 > .form-group {
        display: grid;
        grid-template-columns: 1fr 68px;
        grid-template-rows: auto minmax(29px, auto);
        min-height: 60px;
        margin: 0;
        padding: 0;
        overflow: hidden;
        border: 1px solid #e5e7eb;
        border-radius: 10px;
        background: linear-gradient(180deg, #ffffff 0%, #fafafa 100%);
        box-shadow: 0 2px 5px rgba(15, 23, 42, .06);
    }
    .physical-assessment-card > .form-grid-2 > .form-group > label {
        grid-column: 1;
        grid-row: 1;
        align-self: end;
        margin: 0;
        padding: 8px 10px 0 18px;
        color: #667085;
        font-size: .68rem;
        font-weight: 800;
        letter-spacing: .04em;
        text-transform: uppercase;
    }
    .physical-assessment-card .physical-vital-unit {
        grid-column: 2;
        grid-row: 1 / span 2;
        display: grid;
        min-width: 68px;
        place-items: center;
        border-left: 1px solid #eceff2;
        background: rgba(127, 29, 45, .035);
        color: #374151;
        font-size: .8rem;
        font-weight: 800;
    }
    .physical-assessment-card > .form-grid-2 > .form-group > input.form-control {
        grid-column: 1;
        grid-row: 2;
        min-height: 29px !important;
        padding: 1px 10px 9px 18px !important;
        border: 0 !important;
        border-bottom: 1px solid rgba(127, 29, 45, .38) !important;
        border-radius: 0 !important;
        background: transparent !important;
        box-shadow: none !important;
        color: #111827;
        font-size: .95rem;
        font-weight: 700;
    }
    .physical-assessment-card > .form-grid-2 > .form-group > input.form-control::placeholder {
        color: #94a3b8;
        opacity: 1;
    }
    .physical-assessment-card > .form-grid-2 > .form-group > input.form-control:hover,
    .physical-assessment-card > .form-grid-2 > .form-group > input.form-control:focus,
    .physical-assessment-card > .form-grid-2 > .form-group > input.form-control.is-valid,
    .physical-assessment-card > .form-grid-2 > .form-group > input.form-control.is-invalid {
        border: 0 !important;
        border-bottom: 1px solid rgba(127, 29, 45, .5) !important;
        box-shadow: none !important;
    }
    .physical-assessment-card > .form-grid-2 > .form-group::after {
        grid-column: 2;
        grid-row: 1 / span 2;
        display: grid;
        min-width: 68px;
        place-items: center;
        border-left: 1px solid #eceff2;
        background: rgba(127, 29, 45, .035);
        color: #374151;
        font-size: .8rem;
        font-weight: 800;
    }
    .physical-assessment-card > .form-grid-2 > .form-group:nth-child(1)::after { content: 'ft'; }
    .physical-assessment-card > .form-grid-2 > .form-group:nth-child(2)::after { content: 'lbs'; }
    .physical-assessment-card > .form-grid-2 > .form-group:nth-child(3)::after { content: '\00b0C'; }
    .physical-assessment-card > .form-grid-2 > .form-group:nth-child(4)::after { content: 'mmHg'; }
    .physical-assessment-card > .form-grid-2 > .form-group:nth-child(5)::after { content: 'bpm'; }
    .physical-assessment-card > .form-grid-2 > .form-group:nth-child(6)::after { content: 'cpm'; }
    .physical-assessment-card > .form-grid-2 > .form-group > .form-error,
    .physical-assessment-card > .form-grid-2 > .form-group > .form-success {
        grid-column: 1 / -1;
        grid-row: 3;
        margin: 0 10px 6px 18px;
        font-size: .7rem;
    }
    .physical-assessment-card > .form-grid-2 > .form-group > .form-error[style*="display: block"] {
        grid-row: 4;
    }
    .physical-assessment-card > .physical-covid-group {
        display: flex;
        align-items: flex-start;
        flex-direction: column;
        gap: 9px;
        margin: 18px 0 0;
        padding: 0;
    }
    .physical-assessment-card > .physical-covid-group > label {
        margin: 0;
        color: #374151;
        font-size: .72rem;
        font-weight: 800;
        letter-spacing: .04em;
        text-transform: uppercase;
    }
    .physical-assessment-card > .physical-covid-group .choice-grid {
        display: flex;
        flex: 0 0 auto;
        gap: 8px;
        width: 250px;
        margin: 0;
    }
    .physical-assessment-card > .physical-covid-group .choice-grid > label {
        flex: 1 1 0;
        min-width: 0;
    }
    .physical-assessment-card > .physical-covid-group .choice-card {
        position: relative;
        overflow: hidden;
        min-height: 44px;
        padding: 10px 14px;
        border-radius: 10px;
        font-size: .84rem;
        isolation: isolate;
        transition: border-color .2s ease, background-color .2s ease, color .2s ease, box-shadow .2s ease, transform .2s ease;
    }
    .physical-assessment-card > .physical-covid-group .choice-card::before {
        content: "";
        position: absolute;
        z-index: -1;
        inset: 0;
        background: linear-gradient(110deg, transparent 0%, rgba(255, 245, 180, .9) 45%, transparent 72%);
        transform: translateX(-120%);
        transition: transform .45s ease;
    }
    .physical-assessment-card > .physical-covid-group .choice-card:hover {
        border-color: var(--clinic-form-yellow);
        background: var(--clinic-form-yellow);
        color: var(--clinic-form-maroon);
        box-shadow: 0 8px 18px rgba(250, 204, 21, .25);
        transform: translateY(-1px);
    }
    .physical-assessment-card > .physical-covid-group .choice-card:hover::before {
        transform: translateX(120%);
    }
    .physical-assessment-card > .physical-covid-group .choice-input:checked + .choice-card {
        border-color: transparent;
        background: linear-gradient(135deg, var(--clinic-form-maroon) 0%, var(--clinic-form-maroon-dark) 100%);
        color: #ffffff;
    }
    .physical-covid-date-group {
        display: none;
        width: min(100%, 360px);
        margin-top: 6px;
    }
    .physical-covid-date-group.is-visible {
        display: block;
    }
    .physical-covid-date-group > label {
        display: block;
        margin: 0 0 7px;
        color: #374151;
        font-size: .72rem;
        font-weight: 800;
        letter-spacing: .04em;
        text-transform: uppercase;
    }
    .physical-covid-date-group > .form-control {
        min-height: 44px;
        padding: 10px 0;
    }
    .physical-covid-date-group > .form-error {
        margin: 6px 0 0;
    }
    .consultation-main .visit-details-card {
        position: relative;
        z-index: 5;
        overflow: visible;
        padding: 0 24px 24px;
        border-top: 3px solid var(--clinic-form-maroon) !important;
    }
    .visit-details-card > .consult-section-heading {
        display: flex;
        align-items: center;
        gap: 12px;
        margin: 0 -24px 22px;
        padding: 16px 24px;
        border-bottom: 1px solid rgba(127, 29, 45, .14);
        border-radius: 13px 13px 0 0;
        background: linear-gradient(135deg, #7f1d2d 0%, #991b32 100%);
    }
    .visit-details-card > .consult-section-heading .consult-section-icon {
        display: grid;
        flex: 0 0 42px;
        width: 42px;
        height: 42px;
        place-items: center;
        border: 1px solid rgba(250, 204, 21, .58);
        border-radius: 10px;
        background: rgba(255, 255, 255, .1);
        color: var(--clinic-form-yellow);
        box-shadow: inset 0 1px 0 rgba(255, 255, 255, .12);
    }
    .visit-details-card > .consult-section-heading .consult-section-icon svg {
        width: 23px;
        height: 23px;
    }
    .visit-details-card > .consult-section-heading h3 {
        margin: 2px 0 2px;
        color: #ffffff;
        font-size: 1.11rem;
        font-weight: 850;
        letter-spacing: 0;
    }
    .visit-details-card > .form-group {
        margin-bottom: 18px;
        padding: 0;
        border: 0;
        border-radius: 0;
        background: transparent;
        box-shadow: none;
    }
    .visit-details-card > .form-group:last-child {
        margin-bottom: 0;
    }
    .visit-details-card > .form-group > label {
        margin-bottom: 7px;
        color: #374151;
        font-size: .72rem;
        font-weight: 800;
        letter-spacing: .04em;
    }
    .visit-subsection {
        position: relative;
        display: grid;
        gap: 16px;
        padding: 0 36px 22px;
    }
    .visit-subsection + .visit-subsection {
        margin: 0 -24px -24px;
        padding: 26px 60px 24px;
        border-top: 1px solid #eef1f5;
        background: linear-gradient(180deg, #ffffff 0%, #fbfdff 100%);
    }
    .visit-subsection-heading {
        display: flex;
        align-items: center;
        gap: 12px;
        width: fit-content;
        margin: 0 0 2px -36px;
        color: var(--clinic-form-maroon);
    }
    .visit-subsection-icon {
        display: grid;
        width: 38px;
        height: 38px;
        flex: 0 0 38px;
        place-items: center;
        border: 1px solid rgba(127, 29, 45, .12);
        border-radius: 50%;
        background: #fff3f5;
        color: var(--clinic-form-maroon);
    }
    .visit-subsection-icon svg {
        width: 20px;
        height: 20px;
    }
    .visit-subsection-heading h4 {
        margin: 0;
        color: var(--clinic-form-maroon);
        font-size: 1rem;
        font-weight: 850;
        letter-spacing: 0;
    }
    .visit-details-card .visit-field {
        margin: 0;
        padding: 0;
        border: 0;
        border-radius: 0;
        background: transparent;
        box-shadow: none;
    }
    .visit-details-card .visit-field > label {
        display: flex;
        align-items: center;
        gap: 8px;
        margin: 0 0 7px;
        color: #374151;
        font-size: .72rem;
        font-weight: 800;
        letter-spacing: .04em;
        text-transform: uppercase;
    }
    .visit-field-badge {
        display: inline-flex;
        align-items: center;
        min-height: 20px;
        padding: 3px 8px;
        border-radius: 999px;
        background: #f7e9ec;
        color: var(--clinic-form-maroon);
        font-size: .64rem;
        font-weight: 850;
        letter-spacing: 0;
        text-transform: none;
    }
    .visit-field-badge.is-muted {
        background: #eef0f3;
        color: #64748b;
    }
    .visit-details-card .visit-field > .form-control,
    .visit-details-card .visit-field .clinic-select-display {
        min-height: 44px;
        padding: 10px 44px 10px 14px;
        border: 1px solid #d9e1ec;
        border-radius: 8px;
        background: #ffffff;
        box-shadow: 0 1px 3px rgba(15, 23, 42, .06);
        color: #111827;
    }
    .visit-details-card .visit-field > textarea.form-control {
        min-height: 78px;
        padding: 12px 14px;
        resize: vertical;
    }
    .visit-details-card .visit-field > .form-control:hover,
    .visit-details-card .visit-field > .form-control:focus,
    .visit-details-card .visit-field .clinic-select-display:hover,
    .visit-details-card .visit-field .clinic-select-display.is-open {
        border-color: rgba(127, 29, 45, .42);
        box-shadow: 0 0 0 3px rgba(127, 29, 45, .08);
    }
    .visit-details-card .visit-field .clinic-select-display::after {
        right: 18px;
    }
    .visit-details-card .visit-field .clinic-select-menu {
        z-index: 120;
        border-radius: 8px;
    }
    .visit-details-card .visit-field .clinic-select-shell.is-open {
        z-index: 120;
    }
    .visit-details-card .visit-field .form-help {
        display: flex;
        align-items: center;
        min-height: 36px;
        margin-top: 8px;
        padding: 8px 12px;
        border: 1px solid #dbe6ff;
        border-radius: 6px;
        background: #f5f8ff;
        color: #4b5563;
        font-size: .78rem;
        font-weight: 650;
    }
    .visit-details-card .visit-field .form-help::before {
        content: 'i';
        display: inline-grid;
        width: 18px;
        height: 18px;
        flex: 0 0 18px;
        margin-right: 8px;
        place-items: center;
        border: 1px solid #2563eb;
        border-radius: 50%;
        color: #2563eb;
        font-weight: 850;
    }
    html[data-theme="dark"] .consultation-main .physical-assessment-card {
        border-top-color: #facc15 !important;
        background: #101827;
    }
    html[data-theme="dark"] .physical-assessment-card > .consult-section-heading {
        border-bottom-color: rgba(250, 204, 21, .18);
        background: linear-gradient(135deg, #70131b 0%, #8f2230 100%);
    }
    html[data-theme="dark"] .physical-assessment-card > .consult-section-heading .consult-section-icon {
        border-color: rgba(250, 204, 21, .35);
        background: rgba(127, 29, 45, .55);
        color: #facc15;
    }
    html[data-theme="dark"] .physical-assessment-card > .consult-section-heading h3,
    html[data-theme="dark"] .physical-vitals-heading h3 {
        color: #ffffff;
    }
    html[data-theme="dark"] .physical-vitals-heading .physical-assessment-icon {
        border-color: rgba(250, 204, 21, .35);
        background: rgba(127, 29, 45, .55);
        color: #facc15;
    }
    html[data-theme="dark"] .physical-assessment-card > .physical-dob-group > label,
    html[data-theme="dark"] .physical-assessment-card > .physical-covid-group > label,
    html[data-theme="dark"] .physical-assessment-card > .form-grid-2 > .form-group > label {
        color: #cbd5e1;
    }
    html[data-theme="dark"] .physical-assessment-card > .physical-dob-group > #consultDob,
    html[data-theme="dark"] .physical-assessment-card > .form-grid-2 > .form-group {
        border-color: #3b475b !important;
        background: #182235 !important;
        box-shadow: none !important;
    }
    html[data-theme="dark"] .physical-assessment-card > .physical-dob-group > #consultDob,
    html[data-theme="dark"] .physical-assessment-card > .form-grid-2 > .form-group > input.form-control {
        color: #ffffff;
    }
    .physical-assessment-card > .physical-dob-group > #consultDob:disabled {
        opacity: 1;
        cursor: default;
    }
    html[data-theme="dark"] .physical-assessment-card > .physical-dob-group > #consultDob:focus {
        border-color: #facc15 !important;
        box-shadow: 0 0 0 3px rgba(250, 204, 21, .12) !important;
    }
    html[data-theme="dark"] .physical-assessment-card > .physical-dob-group > .form-help {
        border-color: #3b475b;
        background: #111c30;
        color: #cbd5e1;
    }
    html[data-theme="dark"] .physical-assessment-card > .physical-dob-group > .form-help::before {
        border-color: #facc15;
        color: #facc15;
    }
    html[data-theme="dark"] .physical-assessment-card > .physical-covid-group .choice-card:hover {
        border-color: #facc15;
        background: #facc15;
        color: #70131b;
        box-shadow: 0 8px 18px rgba(250, 204, 21, .18);
    }
    html[data-theme="dark"] .physical-assessment-card > .physical-covid-group .choice-input:checked + .choice-card {
        border-color: #9f1d35;
        background: linear-gradient(135deg, #991b32 0%, #700018 100%);
        color: #ffffff;
    }
    html[data-theme="dark"] .physical-covid-date-group > label {
        color: #cbd5e1;
    }
    html[data-theme="dark"] .consultation-main .visit-details-card {
        border-top-color: #facc15 !important;
        background: #101827;
    }
    html[data-theme="dark"] .visit-details-card > .consult-section-heading {
        border-bottom-color: rgba(250, 204, 21, .18);
        background: linear-gradient(135deg, #70131b 0%, #8f2230 100%);
    }
    html[data-theme="dark"] .visit-details-card > .consult-section-heading .consult-section-icon {
        border-color: rgba(250, 204, 21, .35);
        background: rgba(127, 29, 45, .55);
        color: #facc15;
    }
    html[data-theme="dark"] .visit-details-card > .form-group > label {
        color: #cbd5e1;
    }
    html[data-theme="dark"] .visit-subsection + .visit-subsection {
        border-top-color: #263449;
        background: #101827;
    }
    html[data-theme="dark"] .visit-subsection-heading h4 {
        color: #ffffff;
    }
    html[data-theme="dark"] .visit-subsection-icon {
        border-color: rgba(250, 204, 21, .35);
        background: rgba(127, 29, 45, .55);
        color: #facc15;
    }
    html[data-theme="dark"] .visit-details-card .visit-field > label {
        color: #cbd5e1;
    }
    html[data-theme="dark"] .visit-details-card .visit-field > .form-control,
    html[data-theme="dark"] .visit-details-card .visit-field .clinic-select-display {
        border-color: #3b475b;
        background: #182235;
        color: #ffffff;
    }
    html[data-theme="dark"] .visit-details-card .visit-field .form-help {
        border-color: #3b475b;
        background: #111c30;
        color: #cbd5e1;
    }
    html[data-theme="dark"] .visit-details-card .visit-field .form-help::before {
        border-color: #facc15;
        color: #facc15;
    }
    html[data-theme="dark"] .consultation-main .clinic-select-option.is-selected {
        border-color: #9f1d2d !important;
        background: #9f1d2d !important;
        color: #ffffff !important;
    }
    .consultation-main .medicine-dispensing-card {
        position: relative;
        overflow: visible;
        padding: 0 24px 24px;
        border-top: 3px solid var(--clinic-form-maroon) !important;
    }
    .medicine-dispensing-card .medicine-header {
        display: block;
        width: auto;
        margin: 0 -24px 24px;
    }
    .medicine-dispensing-card .medicine-header .consult-section-heading {
        display: flex;
        align-items: center;
        gap: 12px;
        margin: 0;
        padding: 16px 24px;
        border-bottom: 1px solid rgba(127, 29, 45, .14);
        border-radius: 13px 13px 0 0;
        background: linear-gradient(135deg, #7f1d2d 0%, #991b32 100%);
        box-shadow: 0 8px 18px rgba(112, 19, 27, .14);
    }
    .medicine-dispensing-card .medicine-header .consult-section-icon {
        display: grid;
        width: 42px;
        height: 42px;
        flex: 0 0 42px;
        place-items: center;
        border: 1px solid rgba(250, 204, 21, .58);
        border-radius: 10px;
        background: rgba(255, 255, 255, .1);
        color: var(--clinic-form-yellow);
        box-shadow: inset 0 1px 0 rgba(255, 255, 255, .12);
    }
    .medicine-dispensing-card .medicine-header .consult-section-icon svg {
        width: 23px;
        height: 23px;
    }
    .medicine-dispensing-card .medicine-header .consult-section-copy h3 {
        margin: 2px 0 2px;
        color: #ffffff !important;
        font-size: 1.11rem !important;
        font-weight: 850;
        letter-spacing: 0;
    }
    .medicine-dispensing-card .medicine-entries {
        display: grid;
        grid-template-columns: 1fr;
        gap: 16px;
    }
    .medicine-dispensing-card .medicine-entries.is-multi {
        grid-template-columns: repeat(2, minmax(0, 1fr));
    }
    .medicine-dispensing-card .medicine-entries.is-multi.is-odd .medicine-entry.is-visible:last-child {
        grid-column: 1 / -1;
    }
    .medicine-dispensing-card .medicine-entry {
        min-width: 0;
        padding: 18px 20px;
        border: 1px solid rgba(127, 29, 45, .14);
        border-radius: 8px;
        background: #ffffff;
        box-shadow: 0 8px 18px rgba(15, 23, 42, .08);
    }
    .medicine-dispensing-card .medicine-entry-head {
        margin-bottom: 18px;
    }
    .medicine-dispensing-card .medicine-entry-title {
        gap: 10px;
        color: var(--clinic-form-maroon);
        font-size: .88rem;
        font-weight: 850;
        letter-spacing: .03em;
    }
    .medicine-dispensing-card .medicine-entry-number {
        width: 28px;
        height: 28px;
        border-radius: 6px;
        background: var(--clinic-form-maroon);
        color: #ffffff;
        font-size: .82rem;
        font-weight: 850;
    }
    .medicine-dispensing-card .medicine-remove-button {
        width: 36px;
        height: 36px;
        border-color: #efd2d7;
        border-radius: 8px;
        background: #fffafb;
    }
    .medicine-dispensing-card .medicine-remove-button:hover,
    .medicine-dispensing-card .medicine-remove-button:focus-visible {
        border-color: #facc15;
        background: #facc15;
        color: var(--clinic-form-maroon);
        box-shadow: 0 8px 18px rgba(250, 204, 21, .24);
        transform: translateY(-1px);
        outline: none;
    }
    .medicine-dispensing-card .medicine-entry .form-group {
        margin-bottom: 14px;
    }
    .medicine-dispensing-card .medicine-entry .form-group > label {
        margin-bottom: 8px;
        color: #4b5563;
        font-size: .74rem;
        font-weight: 850;
        letter-spacing: .04em;
    }
    .medicine-dispensing-card .medicine-entry .clinic-select-display,
    .medicine-dispensing-card .medicine-entry .form-control {
        min-height: 50px;
        border: 1px solid #d9e1ec;
        border-radius: 8px;
        background: #ffffff;
        box-shadow: 0 1px 3px rgba(15, 23, 42, .05);
    }
    .medicine-dispensing-card .medicine-entry .clinic-select-display {
        padding: 12px 56px 12px 14px;
    }
    .medicine-dispensing-card .medicine-entry .clinic-select-display::after {
        right: 22px;
    }
    .medicine-dispensing-card .medicine-add-row {
        display: grid;
        grid-template-columns: minmax(0, 1fr) auto;
        align-items: center;
        gap: 18px;
        margin-top: 18px;
    }
    .medicine-dispensing-card .medicine-add-button {
        min-height: 50px;
        border-radius: 8px;
        background: #fffdfd;
        color: var(--clinic-form-maroon);
        font-size: .86rem;
        font-weight: 850;
    }
    .medicine-dispensing-card .medicine-selection-count {
        color: var(--clinic-form-maroon);
        font-size: .86rem;
        font-weight: 850;
    }
    html[data-theme="dark"] .consultation-main .medicine-dispensing-card {
        border-top-color: #facc15 !important;
        background: #101827;
    }
    html[data-theme="dark"] .medicine-dispensing-card .medicine-header .consult-section-heading {
        border-bottom-color: rgba(250, 204, 21, .18);
        background: linear-gradient(135deg, #70131b 0%, #8f2230 100%);
    }
    html[data-theme="dark"] .medicine-dispensing-card .medicine-entry {
        border-color: #3b475b;
        background: #111827;
        box-shadow: 0 10px 24px rgba(0, 0, 0, .24);
    }
    html[data-theme="dark"] .medicine-dispensing-card .medicine-entry .clinic-select-display,
    html[data-theme="dark"] .medicine-dispensing-card .medicine-entry .form-control {
        border-color: #3b475b;
        background: #182235;
        color: #ffffff;
    }
    html[data-theme="dark"] .medicine-dispensing-card .medicine-entry .form-group > label {
        color: #cbd5e1;
    }
    .consultation-main .clinical-findings-card {
        position: relative;
        z-index: 3;
        overflow: visible;
        padding: 0 24px 24px;
        border-top: 3px solid var(--clinic-form-maroon) !important;
    }
    .clinical-findings-card > .consult-section-heading {
        display: flex;
        align-items: center;
        gap: 12px;
        margin: 0 -24px 18px;
        padding: 16px 24px;
        border-bottom: 1px solid rgba(127, 29, 45, .14);
        border-radius: 13px 13px 0 0;
        background: linear-gradient(135deg, #7f1d2d 0%, #991b32 100%);
        box-shadow: 0 8px 18px rgba(112, 19, 27, .14);
    }
    .clinical-findings-card > .consult-section-heading .consult-section-icon {
        display: grid;
        width: 42px;
        height: 42px;
        flex: 0 0 42px;
        place-items: center;
        border: 1px solid rgba(250, 204, 21, .58);
        border-radius: 10px;
        background: rgba(255, 255, 255, .1);
        color: var(--clinic-form-yellow);
        box-shadow: inset 0 1px 0 rgba(255, 255, 255, .12);
    }
    .clinical-findings-card > .consult-section-heading .consult-section-icon svg {
        width: 22px;
        height: 22px;
    }
    .clinical-findings-card > .form-group {
        margin-bottom: 14px;
        padding: 0;
        border: 0;
        border-radius: 0;
        background: transparent;
        box-shadow: none;
    }
    .clinical-findings-card > .form-group > label {
        margin: 0 0 7px;
        color: #374151;
        font-size: .72rem;
        font-weight: 850;
        letter-spacing: .04em;
        text-transform: uppercase;
    }
    .clinical-findings-card > .form-group > .form-control,
    .clinical-findings-card > .form-group .clinic-select-display {
        min-height: 44px;
        padding: 10px 44px 10px 14px;
        border: 1px solid #d9e1ec;
        border-radius: 8px;
        background: #ffffff;
        box-shadow: 0 1px 3px rgba(15, 23, 42, .06);
        color: #111827;
    }
    .clinical-findings-card > .form-group textarea.form-control {
        min-height: 78px;
        padding: 12px 14px;
        resize: vertical;
    }
    .clinical-findings-card > .form-group > .form-control:hover,
    .clinical-findings-card > .form-group > .form-control:focus,
    .clinical-findings-card > .form-group .clinic-select-display:hover,
    .clinical-findings-card > .form-group .clinic-select-display.is-open {
        border-color: rgba(127, 29, 45, .42);
        box-shadow: 0 0 0 3px rgba(127, 29, 45, .08);
    }
    .clinical-findings-card > .form-group .clinic-select-display::after {
        right: 18px;
    }
    .clinical-findings-card > .form-group .clinic-select-shell.is-open,
    .clinical-findings-card > .form-group .clinic-select-menu {
        z-index: 120;
    }
    .clinical-findings-card > .form-group > .form-help {
        display: flex;
        align-items: center;
        min-height: 34px;
        margin-top: 8px;
        padding: 8px 12px;
        border: 1px solid #f8ddb0;
        border-radius: 6px;
        background: #fff8e6;
        color: #9a3412;
        font-size: .76rem;
        font-weight: 650;
    }
    .clinical-findings-card > .form-group > .form-help::before {
        content: 'i';
        display: inline-grid;
        width: 18px;
        height: 18px;
        flex: 0 0 18px;
        margin-right: 8px;
        place-items: center;
        border: 1px solid #f59e0b;
        border-radius: 50%;
        color: #f59e0b;
        font-weight: 850;
    }
    .clinical-findings-card .referral-details-group {
        margin-top: 0;
    }
    html[data-theme="dark"] .consultation-main .clinical-findings-card {
        border-top-color: #facc15 !important;
        background: #101827;
    }
    html[data-theme="dark"] .clinical-findings-card > .consult-section-heading {
        border-bottom-color: rgba(250, 204, 21, .18);
        background: linear-gradient(135deg, #70131b 0%, #8f2230 100%);
    }
    html[data-theme="dark"] .clinical-findings-card > .consult-section-heading .consult-section-icon {
        border-color: rgba(250, 204, 21, .35);
        background: rgba(127, 29, 45, .55);
        color: #facc15;
    }
    html[data-theme="dark"] .clinical-findings-card > .form-group > label {
        color: #cbd5e1;
    }
    html[data-theme="dark"] .clinical-findings-card > .form-group > .form-control,
    html[data-theme="dark"] .clinical-findings-card > .form-group .clinic-select-display {
        border-color: #3b475b;
        background: #182235;
        color: #ffffff;
    }
    html[data-theme="dark"] .clinical-findings-card > .form-group > .form-help {
        border-color: rgba(250, 204, 21, .22);
        background: #1f2430;
        color: #fde68a;
    }
    html[data-theme="dark"] .clinical-findings-card > .form-group > .form-help::before {
        border-color: #facc15;
        color: #facc15;
    }
    @media (max-width: 980px) {
        .medicine-dispensing-card .medicine-entries.is-multi {
            grid-template-columns: 1fr;
        }
    }
    html[data-theme="dark"] .physical-assessment-card .physical-vital-unit {
        border-left-color: #3b475b;
        background: #202a3a;
        color: #facc15;
    }
    html[data-theme="dark"] .physical-assessment-card > .form-grid-2 > .form-group::after {
        border-left-color: #3b475b;
        background: #202a3a;
        color: #facc15;
    }
    html[data-theme="dark"] .physical-assessment-card > .form-grid-2 > .form-group > input.form-control,
    html[data-theme="dark"] .physical-assessment-card > .form-grid-2 > .form-group > input.form-control:hover,
    html[data-theme="dark"] .physical-assessment-card > .form-grid-2 > .form-group > input.form-control:focus,
    html[data-theme="dark"] .physical-assessment-card > .form-grid-2 > .form-group > input.form-control.is-valid,
    html[data-theme="dark"] .physical-assessment-card > .form-grid-2 > .form-group > input.form-control.is-invalid {
        border: 0 !important;
        border-bottom: 1px solid rgba(250, 204, 21, .58) !important;
        box-shadow: none !important;
    }
    @media (max-width: 820px) {
        .physical-assessment-card {
            padding-right: 16px;
            padding-left: 16px;
        }
        .physical-assessment-card > .consult-section-heading {
            margin-right: -16px;
            margin-left: -16px;
            padding-right: 16px;
            padding-left: 16px;
        }
        .physical-assessment-card > .physical-dob-group {
            grid-template-columns: 1fr;
            gap: 10px;
        }
        .physical-assessment-card > .physical-dob-group > label,
        .physical-assessment-card > .physical-dob-group > #consultDob,
        .physical-assessment-card > .physical-dob-group > .form-help {
            grid-column: 1;
        }
        .physical-assessment-card > .physical-dob-group > .form-help { grid-row: 3; }
        .physical-assessment-card > .form-grid-2 {
            grid-template-columns: 1fr;
        }
        .visit-subsection,
        .visit-subsection + .visit-subsection {
            margin-right: -16px;
            margin-left: -16px;
            padding-right: 16px;
            padding-left: 16px;
        }
    }
    @media (max-width: 520px) {
        .physical-assessment-card > .physical-covid-group .choice-grid {
            width: 100%;
            max-width: 250px;
        }
    }
    @media (max-width: 820px) {
        .consultation-main .patient-header {
            grid-template-columns: 1fr;
            gap: 16px;
            padding: 22px 18px;
        }
        .patient-identity {
            justify-content: center;
        }
        .patient-header-actions {
            justify-content: center;
        }
        .consultation-main .consultation-date {
            justify-self: center;
        }
    }

    /* Subtle blue border for prefilled or populated fields */
    .consultation-main .form-control.has-value,
    .consultation-main .clinic-select-display.has-value,
    .consultation-main input.form-control[data-vital].has-value,
    .consultation-main #consultDob.has-value {
        border-color: #3b82f6 !important;
        border-bottom-color: #3b82f6 !important;
        box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.12) !important;
    }
    .physical-assessment-card > .form-grid-2 > .form-group.has-value {
        border-color: #93c5fd !important;
        box-shadow: 0 0 0 2px rgba(59, 130, 246, 0.15) !important;
    }
    .physical-assessment-card > .form-grid-2 > .form-group > input.form-control.has-value {
        border-bottom-color: #3b82f6 !important;
    }

    /* Dark mode subtle blue border */
    html[data-theme="dark"] .consultation-main .form-control.has-value,
    html[data-theme="dark"] .consultation-main .clinic-select-display.has-value,
    html[data-theme="dark"] .consultation-main input.form-control[data-vital].has-value,
    html[data-theme="dark"] .consultation-main #consultDob.has-value {
        border-color: #60a5fa !important;
        border-bottom-color: #60a5fa !important;
        box-shadow: 0 0 0 3px rgba(96, 165, 250, 0.18) !important;
    }
    html[data-theme="dark"] .physical-assessment-card > .form-grid-2 > .form-group.has-value {
        border-color: #3b82f6 !important;
        box-shadow: 0 0 0 2px rgba(96, 165, 250, 0.2) !important;
    }
    html[data-theme="dark"] .physical-assessment-card > .form-grid-2 > .form-group > input.form-control.has-value {
        border-bottom-color: #60a5fa !important;
    }
</style>
@endpush

@section('content')
@php
    $isAssistantWorkspace = request()->is('assistant/*');
    $walkinStoreRoute = $isAssistantWorkspace ? 'assistant.walkin.store' : 'walkin.store';
    $walkinIndexRoute = $isAssistantWorkspace ? 'assistant.walkin.index' : 'walkin.index';
    $studentDisplayRole = \App\Models\Appointment::normalizeUserType($student->user_role ?? $student->user_type ?? 'Student');
    $isAssistedIntake = ($user_source ?? '') === 'assisted';
    $studentDocuments = $studentDocuments ?? [];
    $studentPhotoDocument = collect($studentDocuments)->firstWhere('key', 'student_photo');
    $studentCourse = trim((string) ($student->course ?: optional($student->healthProfile)->course_college));
    $studentInitials = collect(preg_split('/\s+/', trim((string) $student->name)) ?: [])
        ->filter()
        ->take(2)
        ->map(fn ($namePart) => \Illuminate\Support\Str::upper(\Illuminate\Support\Str::substr($namePart, 0, 1)))
        ->implode('');
@endphp

<div class="consultation-workspace">
    <div class="consultation-main">
        <header class="patient-header consult-card">
            <div class="patient-identity">
                <div class="patient-avatar" aria-label="{{ $student->name }} profile photo">
                    @if($studentPhotoDocument)
                        <img src="{{ $studentPhotoDocument['url'] }}" alt="{{ $student->name }} 2x2 photo">
                    @else
                        <span aria-hidden="true">{{ $studentInitials ?: 'ST' }}</span>
                    @endif
                </div>
                <div class="patient-identity-copy">
                    <h2 class="patient-name">{{ $student->name }}</h2>
                    <div class="patient-badges">
                        <span class="patient-badge">{{ $studentDisplayRole }}</span>
                        <span class="patient-badge">Student No. {{ $student->student_number ?: $student->student_id ?: 'N/A' }}</span>
                        <span class="patient-badge">Appointment No. {{ optional($latestAppointment)->apt_id ?: 'N/A' }}</span>
                    </div>
                    <div class="patient-meta">
                        <div class="patient-meta-row">
                            <span class="patient-meta-label">Email</span>
                            <span class="patient-meta-value">{{ $student->email ?: 'Not provided' }}</span>
                        </div>
                        <div class="patient-meta-row">
                            <span class="patient-meta-label">Course</span>
                            <span class="patient-meta-value">{{ $studentCourse ?: 'Not provided' }}</span>
                        </div>
                    </div>
                    @if(($user_source ?? '') === 'online' && $latestAppointment)
                        <div class="form-help">
                            Scheduled {{ \Carbon\Carbon::parse($latestAppointment->date)->format('M d, Y') }}
                            at {{ \Carbon\Carbon::parse($latestAppointment->time)->format('g:i A') }}
                        </div>
                    @endif
                </div>
            </div>
            <div class="patient-header-actions">
                <div class="consultation-date">
                    <span>Today's Consultation</span>
                    @if(($user_source ?? '') === 'online' && $latestAppointment)
                        <span class="badge-source source-online consultation-source-badge">Online Appointment</span>
                    @elseif($isAssistedIntake)
                        <span class="badge-source source-walkin consultation-source-badge">Assisted Intake</span>
                    @else
                        <span class="badge-source source-walkin consultation-source-badge">Walk-in Patient</span>
                    @endif
                    {{ now()->format('F d, Y') }}
                </div>
            </div>
        </header>

        @if(session('error') || $errors->any())
            <div class="consultation-form-alert" role="alert">
                <strong>Consultation was not saved.</strong>
                @if(session('error'))
                    <span>{{ session('error') }}</span>
                @endif
                @if($errors->any())
                    <ul>
                        @foreach($errors->all() as $validationError)
                            <li>{{ $validationError }}</li>
                        @endforeach
                    </ul>
                @endif
            </div>
        @endif

        <form action="{{ route($walkinStoreRoute) }}" method="POST" id="consultationForm">
            @csrf
            <input type="hidden" name="student_number" value="{{ $student->student_number ?: $student->student_id }}">
            <input type="hidden" name="user_role" value="{{ $studentDisplayRole }}">
            <input type="hidden" name="user_type" value="{{ $user_source ?? 'walkin' }}">
            <input type="hidden" name="consultation_started_at" value="{{ old('consultation_started_at', $consultationStartedAt ?? now()->format('H:i:s')) }}">

            <section class="consult-card physical-assessment-card">
                <div class="consult-section-heading">
                    <span class="consult-section-icon" aria-hidden="true"><x-outline-icon name="user-circle" /></span>
                    <div class="consult-section-copy">
                        <h3>Personal Information</h3>
                        <p>Review student vitals, measurements, and personal details.</p>
                    </div>
                </div>
                <div class="form-group physical-dob-group">
                    <label for="consultDob">Date of Birth</label>
                    @php
                        $lockedConsultationDob = $consultationDob ?? old('dob', '');
                    @endphp
                    <input type="hidden" name="dob" value="{{ $lockedConsultationDob }}">
                    <input type="date" id="consultDob" class="form-control" value="{{ $lockedConsultationDob }}" disabled aria-readonly="true">
                    <div class="form-help">Prefilled from saved student information when available.</div>
                </div>
                <div class="physical-vitals-heading">
                    <h3>Measurements and Vitals</h3>
                </div>
                <div class="form-grid-2">
                    <div class="form-group">
                        <label for="consultHeight">Height (ft)</label>
                        <input type="number" id="consultHeight" step="0.01" name="height" class="form-control @error('height') is-invalid @enderror" placeholder="5.6" value="{{ old('height', $consultationHeight ?? '') }}" data-vital="height" data-vital-min="1" data-vital-max="10" data-vital-name="Height">
                        <div class="form-error" id="heightError"></div>
                        <div class="form-success" id="heightSuccess">✓ Valid height</div>
                        @error('height')
                            <div class="form-error" style="display: block;">{{ $message }}</div>
                        @enderror
                    </div>
                    <div class="form-group">
                        <label for="consultWeight">Weight (lbs)</label>
                        <input type="number" id="consultWeight" step="0.01" name="weight" class="form-control @error('weight') is-invalid @enderror" placeholder="143" value="{{ old('weight', $consultationWeight ?? '') }}" data-vital="weight" data-vital-min="1" data-vital-max="1100" data-vital-name="Weight">
                        <div class="form-error" id="weightError"></div>
                        <div class="form-success" id="weightSuccess">✓ Valid weight</div>
                        @error('weight')
                            <div class="form-error" style="display: block;">{{ $message }}</div>
                        @enderror
                    </div>
                    <div class="form-group">
                        <label for="consultTemp">Temperature (C)</label>
                        <input type="number" id="consultTemp" step="0.1" name="temp" class="form-control @error('temp') is-invalid @enderror" placeholder="36.5" value="{{ old('temp') }}" data-vital="temp" data-vital-min="30" data-vital-max="45" data-vital-name="Temperature">
                        <div class="form-error" id="tempError"></div>
                        <div class="form-success" id="tempSuccess">✓ Valid temperature</div>
                        @error('temp')
                            <div class="form-error" style="display: block;">{{ $message }}</div>
                        @enderror
                    </div>
                    <div class="form-group">
                        <label for="consultBp">Blood Pressure</label>
                        <input type="text" id="consultBp" name="bp" class="form-control @error('bp') is-invalid @enderror" placeholder="120/80" value="{{ old('bp') }}" data-vital="bp" data-vital-name="Blood Pressure">
                        <div class="form-error" id="bpError"></div>
                        <div class="form-success" id="bpSuccess">✓ Valid blood pressure</div>
                        @error('bp')
                            <div class="form-error" style="display: block;">{{ $message }}</div>
                        @enderror
                    </div>
                    <div class="form-group">
                        <label for="consultPulse">Pulse Rate (bpm)</label>
                        <input type="number" id="consultPulse" name="pulse_rate" class="form-control @error('pulse_rate') is-invalid @enderror" placeholder="72" value="{{ old('pulse_rate') }}" data-vital="pulse_rate" data-vital-min="1" data-vital-max="300" data-vital-name="Pulse Rate">
                        <div class="form-error" id="pulseError"></div>
                        <div class="form-success" id="pulseSuccess">✓ Valid pulse rate</div>
                        @error('pulse_rate')
                            <div class="form-error" style="display: block;">{{ $message }}</div>
                        @enderror
                    </div>
                    <div class="form-group">
                        <label for="consultRespiratory">Respiratory Rate (cpm)</label>
                        <input type="number" id="consultRespiratory" name="respiratory_rate" class="form-control @error('respiratory_rate') is-invalid @enderror" placeholder="18" value="{{ old('respiratory_rate') }}" data-vital="respiratory_rate" data-vital-min="1" data-vital-max="120" data-vital-name="Respiratory Rate">
                        <div class="form-error" id="respiratoryError"></div>
                        <div class="form-success" id="respiratorySuccess">✓ Valid respiratory rate</div>
                        @error('respiratory_rate')
                            <div class="form-error" style="display: block;">{{ $message }}</div>
                        @enderror
                    </div>
                </div>
                <div class="form-group physical-covid-group">
                    <label>Covid Positive?</label>
                    <div class="choice-grid">
                        <label>
                            <input type="radio" name="covid_status" class="choice-input" value="Yes" {{ old('covid_status') === 'Yes' ? 'checked' : '' }}>
                            <span class="choice-card">Yes</span>
                        </label>
                        <label>
                            <input type="radio" name="covid_status" class="choice-input" value="No" {{ old('covid_status', 'No') === 'No' ? 'checked' : '' }}>
                            <span class="choice-card">No</span>
                        </label>
                    </div>
                    <div class="physical-covid-date-group {{ old('covid_status') === 'Yes' ? 'is-visible' : '' }}" id="covidPositiveDateGroup">
                        <label for="consultCovidPositiveDate">Date Tested Positive</label>
                        <input
                            type="date"
                            id="consultCovidPositiveDate"
                            name="covid_positive_date"
                            class="form-control @error('covid_positive_date') is-invalid @enderror"
                            value="{{ old('covid_positive_date') }}"
                            max="{{ now()->format('Y-m-d') }}"
                            {{ old('covid_status') === 'Yes' ? 'required' : 'disabled' }}
                        >
                        @error('covid_positive_date')
                            <div class="form-error" style="display: block;">{{ $message }}</div>
                        @enderror
                    </div>
                </div>
            </section>

            <section class="consult-card visit-details-card">
                <div class="consult-section-heading">
                    <span class="consult-section-icon" aria-hidden="true"><x-outline-icon name="clipboard-document-list" /></span>
                    <div class="consult-section-copy">
                        <h3>Visit Details</h3>
                        <p>Provide details about the student's clinic visit.</p>
                    </div>
                </div>
                <div class="visit-subsection">
                    <div class="visit-subsection-heading">
                        <h4>1. Visit Information</h4>
                    </div>
                    <div class="form-group visit-field">
                        <label for="consultReason">{{ ($user_source ?? '') === 'online' ? 'Appointment Remarks' : 'Reason for Visiting Clinic' }}</label>
                        <textarea id="consultReason" name="reason_for_visit" class="form-control" rows="3" maxlength="255" placeholder="Describe the student's concern or reason for visit..." {{ ($user_source ?? '') === 'online' ? 'readonly' : '' }}>{{ old('reason_for_visit', optional($latestAppointment)->remarks) }}</textarea>
                    </div>
                    <div class="form-group visit-field">
                        <label for="consultService">Purpose of Visit / Service</label>
                        <select id="consultService" class="form-control" data-clinic-select @if(($user_source ?? '') === 'online') disabled @else name="service" @endif required>
                            <option value="" disabled {{ !old('service', optional($latestAppointment)->service) ? 'selected' : '' }}>Select clinic service</option>
                            <option value="General Consultation" {{ old('service', optional($latestAppointment)->service) === 'General Consultation' ? 'selected' : '' }}>General Consultation</option>
                            <option value="BP Monitoring" {{ old('service', optional($latestAppointment)->service) === 'BP Monitoring' ? 'selected' : '' }}>BP Monitoring</option>
                        </select>
                        @if(($user_source ?? '') === 'online')
                            <input type="hidden" name="service" value="{{ old('service', optional($latestAppointment)->service) }}">
                        @endif
                    </div>
                </div>
                <div class="visit-subsection">
                    <div class="visit-subsection-heading">
                        <h4>2. Medical Classification</h4>
                    </div>
                    <div class="form-group visit-field">
                        <label for="consultCondition">
                            Medical Condition
                            <span class="visit-field-badge">MAR Classification</span>
                        </label>
                        <select name="condition_id" id="consultCondition" class="form-control mar-required" data-clinic-select required>
                            <option value="" disabled {{ old('condition_id') ? '' : 'selected' }}>Select diagnosis / classification</option>
                            @foreach($conditions as $condition)
                                <option value="{{ $condition->id }}" {{ (string) old('condition_id') === (string) $condition->id ? 'selected' : '' }}>
                                    Category {{ optional($condition->category)->code }}: {{ $condition->name }}
                                </option>
                            @endforeach
                        </select>
                        <div class="form-help">Required for MAR reporting.</div>
                    </div>
                    <div class="form-group visit-field">
                        <label for="consultCertificate">
                            Medical Certificate / Clearance
                            <span class="visit-field-badge is-muted">Optional</span>
                        </label>
                        <select name="certificate_type" id="consultCertificate" class="form-control" data-clinic-select>
                            <option value="none" {{ old('certificate_type', 'none') === 'none' ? 'selected' : '' }}>No certificate / clearance</option>
                            <option value="excused_letter" {{ old('certificate_type') === 'excused_letter' ? 'selected' : '' }}>Excused Letter</option>
                            <option value="coc_ijt" {{ old('certificate_type') === 'coc_ijt' ? 'selected' : '' }}>COC for IJT</option>
                            <option value="coc_ladderized" {{ old('certificate_type') === 'coc_ladderized' ? 'selected' : '' }}>COC for Ladderized</option>
                        </select>
                    </div>
                </div>
            </section>

            <section class="consult-card medicine-dispensing-card">
                <div class="medicine-header">
                    <div class="consult-section-heading">
                        <span class="consult-section-icon" aria-hidden="true">
                            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">
                                <rect x="5.2" y="3.8" width="6.6" height="14" rx="3.3" transform="rotate(-35 8.5 10.8)" />
                                <line x1="4.2" y1="9.8" x2="11.8" y2="4.4" />
                                <line x1="7.8" y1="4.8" x2="8.6" y2="3.6" stroke-width="1.4" />
                                <ellipse cx="16.5" cy="16.8" rx="5.2" ry="2.2" />
                                <path d="M 11.3 16.8 v 1.8 c 0 1.2 2.3 2.2 5.2 2.2 s 5.2 -1 5.2 -2.2 v -1.8" />
                                <circle cx="15.2" cy="19.7" r="0.4" fill="currentColor" stroke="none" />
                            </svg>
                        </span>
                        <div class="consult-section-copy">
                            <h3>Medicine Dispensing</h3>
                            <p>Select and manage medicines issued during the clinic visit.</p>
                        </div>
                    </div>
                </div>
                @php
                    $oldMedicineIds = old('item_id', []);
                    $oldMedicineQuantities = old('issued_quantity', []);
                    $oldMedicineIds = is_array($oldMedicineIds) ? $oldMedicineIds : [$oldMedicineIds];
                    $oldMedicineQuantities = is_array($oldMedicineQuantities) ? $oldMedicineQuantities : [$oldMedicineQuantities];
                @endphp
                <div class="medicine-entries" id="medicineEntries">
                    @for($medicineIndex = 0; $medicineIndex < 5; $medicineIndex++)
                        @php
                            $oldMedicineId = trim((string) ($oldMedicineIds[$medicineIndex] ?? ''));
                            $oldMedicineQuantity = $oldMedicineQuantities[$medicineIndex] ?? '';
                        @endphp
                        <div class="medicine-entry {{ $medicineIndex === 0 || $oldMedicineId !== '' ? 'is-visible' : '' }}" data-medicine-entry data-index="{{ $medicineIndex }}">
                            <div class="medicine-entry-head">
                                <div class="medicine-entry-title">
                                    <span class="medicine-entry-number">{{ $medicineIndex + 1 }}</span>
                                    <span>Medicine {{ str_pad((string) ($medicineIndex + 1), 2, '0', STR_PAD_LEFT) }}</span>
                                </div>
                                <button type="button" class="medicine-remove-button" data-remove-medicine aria-label="Remove medicine {{ $medicineIndex + 1 }}" title="Remove medicine">
                                    <x-outline-icon name="trash" />
                                </button>
                            </div>
                            <div class="form-group">
                                <label for="consultMedicineSelect{{ $medicineIndex }}">Select Medicine (Inventory)</label>
                                <div class="medicine-selection-row">
                                    <select name="item_id[]" id="consultMedicineSelect{{ $medicineIndex }}" class="form-control" data-medicine-select data-clinic-select>
                                        <option value="">-- No Medicine Issued --</option>
                                        @foreach($items as $item)
                                            @php
                                                $availableDispensingQuantity = $item->hasDispensingConversion()
                                                    ? $item->availableDispensingQuantity()
                                                    : (float) $item->quantity;
                                                $issueUnit = $item->hasDispensingConversion()
                                                    ? ($item->dispensing_unit ?: $item->unit)
                                                    : ($item->unit ?: 'pcs');
                                                $stockDisplay = rtrim(rtrim(number_format((float) $item->quantity, 2, '.', ''), '0'), '.');
                                                $availableDisplay = rtrim(rtrim(number_format($availableDispensingQuantity, 2, '.', ''), '0'), '.');
                                                $isLowStock = (float) $item->quantity <= (float) ($item->minimum_stock ?? 0);
                                            @endphp
                                            <option
                                                value="{{ $item->id }}"
                                                data-stock-unit="{{ $item->unit ?: 'pcs' }}"
                                                data-dispensing-unit="{{ $issueUnit }}"
                                                data-has-conversion="{{ $item->hasDispensingConversion() ? '1' : '0' }}"
                                                data-units-per-stock="{{ $item->units_per_stock_unit ?: 1 }}"
                                                data-available-dispensing="{{ $availableDispensingQuantity }}"
                                                data-low-stock="{{ $isLowStock ? '1' : '0' }}"
                                                {{ $oldMedicineId === (string) $item->id ? 'selected' : '' }}
                                            >
                                                {{ $item->name }} (Available: {{ $availableDisplay }} {{ $issueUnit }}@if($item->hasDispensingConversion()) | {{ $stockDisplay }} {{ $item->unit }}@endif)
                                            </option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="selected-stock" data-selected-stock aria-live="polite"></div>
                            </div>
                            <div class="form-group medicine-quantity-group" data-medicine-quantity-group>
                                <label data-quantity-label for="consultIssuedQuantityInput{{ $medicineIndex }}">Quantity to Issue</label>
                                <input type="number" name="issued_quantity[]" id="consultIssuedQuantityInput{{ $medicineIndex }}" class="form-control" data-medicine-quantity min="0" step="0.01" placeholder="Enter amount" value="{{ $oldMedicineQuantity }}">
                                <div class="form-help" data-quantity-help>Select a medicine to see the dispensing unit and available stock.</div>
                            </div>
                        </div>
                    @endfor
                </div>
                <div class="medicine-add-row">
                    <button type="button" class="medicine-add-button" id="addMedicineButton">
                        <x-outline-icon name="plus-circle" />
                        <span>Add Another Medicine</span>
                    </button>
                    <span class="medicine-selection-count" id="medicineSelectionCount">0 of 5 selected</span>
                </div>
            </section>

            <section class="consult-card clinical-findings-card">
                <div class="consult-section-heading">
                    <span class="consult-section-icon" aria-hidden="true"><x-outline-icon name="document-text" /></span>
                    <div class="consult-section-copy">
                        <h3>Assessment and Recommendations</h3>
                        <p>Document the patient's findings and assessments.</p>
                    </div>
                </div>
                <div class="form-group">
                    <label for="consultReferral">Referral</label>
                    <select name="referral_type" id="consultReferral" class="form-control" data-clinic-select>
                        <option value="none" {{ old('referral_type', 'none') === 'none' ? 'selected' : '' }}>No Referral</option>
                        <option value="hospital_without_nurse" {{ old('referral_type') === 'hospital_without_nurse' ? 'selected' : '' }}>Refer to Hospital (Without Nurse)</option>
                        <option value="hospital_with_nurse" {{ old('referral_type') === 'hospital_with_nurse' ? 'selected' : '' }}>Refer to Hospital (With Nurse)</option>
                        <option value="general" {{ old('referral_type') === 'general' ? 'selected' : '' }}>Referral (General)</option>
                        <option value="others" {{ old('referral_type') === 'others' ? 'selected' : '' }}>Others</option>
                    </select>
                    <div class="form-help">Use this when the patient needs care beyond the school clinic.</div>
                </div>
                <div class="form-group referral-details-group {{ old('referral_type') === 'others' ? 'is-visible' : '' }}" id="referralDetailsGroup">
                    <label for="consultReferralDetails">Referral Details</label>
                    <input type="text" name="referral_details" id="consultReferralDetails" class="form-control" value="{{ old('referral_details') }}" maxlength="500" placeholder="Specify the referral destination or reason">
                    @error('referral_details')
                        <div class="form-error" style="display: block;">{{ $message }}</div>
                    @enderror
                </div>
                <div class="form-group">
                    <label for="consultRemarks">Remarks / Assessment</label>
                    <textarea name="remarks" id="consultRemarks" class="form-control" rows="5" required placeholder="Describe symptoms or concerns...">{{ old('remarks') }}</textarea>
                </div>
                <div class="form-actions">
                    <button type="submit" class="btn-save" id="finalizeConsultationButton">
                        <span>Save &amp; Finalize Consultation</span>
                    </button>
                    <a href="{{ route($walkinIndexRoute) }}" class="btn-cancel">
                        <x-outline-icon name="x-mark" />
                        <span>Cancel</span>
                    </a>
                </div>
            </section>
        </form>
    </div>
</div>

<div class="consultation-success-overlay" id="consultationSuccessOverlay" aria-live="assertive" aria-hidden="true">
    <div class="consultation-success-card">
        <div class="consultation-success-check" aria-hidden="true">
            <x-outline-icon name="check" />
        </div>
        <strong>Done Consultation</strong>
    </div>
</div>

<nav class="consultation-utility-rail" id="consultationUtilityRail" aria-label="Consultation tools">
    <button type="button" class="utility-rail-button" data-utility-target="documents" title="Uploaded Documents">
        <x-outline-icon name="document-text" />
        <span>Documents</span>
        <span class="utility-rail-count">{{ count($studentDocuments) }}</span>
    </button>
    <button type="button" class="utility-rail-button" data-utility-target="inventory" title="Live Stock Tally">
        <x-outline-icon name="cube" />
        <span>Stock Tally</span>
        <span class="utility-rail-count">{{ $items->count() }}</span>
    </button>
    @if($student)
        @php
            $recentRecordsCount = \App\Models\Consultation::where('user_id', $student->id)->count();
        @endphp
        <button type="button" class="utility-rail-button" data-utility-target="recent_records" title="Recent Records">
            <x-outline-icon name="clock" />
            <span>Recent Records</span>
            <span class="utility-rail-count">{{ $recentRecordsCount }}</span>
        </button>
    @endif
    <button type="button" class="utility-rail-button" data-utility-target="treatments" title="Treatment Record">
        <x-outline-icon name="clipboard-document-list" />
        <span>Treatment Record</span>
        <span class="utility-rail-count">{{ $studentTreatments->count() }}</span>
    </button>
</nav>

<aside id="right-utility-panel" aria-hidden="true" aria-label="Consultation utility panel">
    <header class="utility-panel-header">
        <x-outline-icon name="document-text" id="utilityPanelIcon" />
        <h2 class="utility-panel-title" id="utilityPanelTitle">Uploaded Documents</h2>
        <button type="button" id="close-utility-panel" aria-label="Close utility panel">&times;</button>
    </header>
    <button type="button" id="expand-utility-panel" aria-label="Expand treatment record panel" title="Expand treatment record">&lt;</button>

    <section class="utility-panel-pane" data-utility-pane="documents">
        <p class="utility-pane-note">Submitted clinic files and the generated Health Information Form.</p>
        @if(count($studentDocuments))
            <div class="document-list">
                @foreach($studentDocuments as $document)
                    <article class="document-card">
                        <a class="document-preview" href="{{ $document['url'] }}" target="_blank" rel="noopener">
                            @if($document['type'] === 'image')
                                <img src="{{ $document['url'] }}" alt="{{ $document['label'] }} preview">
                            @else
                                <x-outline-icon name="document-text" />
                            @endif
                        </a>
                        <div class="document-card-body">
                            <span class="document-card-title">{{ $document['label'] }}</span>
                            <a class="document-open" href="{{ $document['url'] }}" target="_blank" rel="noopener">
                                <x-outline-icon name="eye" />
                                Open document
                            </a>
                        </div>
                    </article>
                @endforeach
            </div>
        @else
            <div class="documents-empty">No uploaded clinic documents are available for this student.</div>
        @endif
    </section>

    <section class="utility-panel-pane" data-utility-pane="inventory">
        <div class="inventory-panel-search" id="inventoryPanelSearch">
            <div class="inventory-panel-search-wrap">
                <input
                    type="search"
                    id="inventoryPanelSearchInput"
                    class="inventory-panel-search-input"
                    placeholder="Search medicine..."
                    autocomplete="off"
                    aria-label="Search medicine inventory"
                >
            </div>
            <button
                type="button"
                class="inventory-panel-search-toggle"
                id="inventoryPanelSearchToggle"
                aria-label="Open medicine search"
                aria-expanded="false"
                aria-controls="inventoryPanelSearchInput"
            >
                <x-outline-icon name="magnifying-glass" />
            </button>
        </div>
        <div class="inventory-tally-list">
            @forelse($items as $item)
                @php
                    $drawerAvailable = $item->hasDispensingConversion() ? $item->availableDispensingQuantity() : (float) $item->quantity;
                    $drawerUnit = $item->hasDispensingConversion() ? ($item->dispensing_unit ?: $item->unit) : ($item->unit ?: 'pcs');
                    $drawerAvailableDisplay = rtrim(rtrim(number_format($drawerAvailable, 2, '.', ''), '0'), '.');
                    $drawerStockDisplay = rtrim(rtrim(number_format((float) $item->quantity, 2, '.', ''), '0'), '.');
                    $drawerLowStock = (float) $item->quantity <= (float) ($item->minimum_stock ?? 0);
                @endphp
                <article class="inventory-tally-item" data-inventory-item="{{ $item->id }}">
                    <div class="inventory-tally-row">
                        <span class="inventory-tally-name">{{ $item->name }}</span>
                        <span class="stock-badge {{ $drawerLowStock ? 'low' : '' }}">{{ $drawerLowStock ? 'Low' : 'In Stock' }}</span>
                    </div>
                    <div class="inventory-tally-meta">
                        {{ $drawerAvailableDisplay }} {{ $drawerUnit }} available
                        @if($item->hasDispensingConversion())
                            | {{ $drawerStockDisplay }} {{ $item->unit }} in storage
                        @endif
                    </div>
                    <div class="inventory-tally-actions">
                        <button type="button" class="inventory-issue-button" data-issue-medicine="{{ $item->id }}">Issue</button>
                    </div>
                </article>
            @empty
                <div class="documents-empty">No medicines are currently available.</div>
            @endforelse
        </div>
        <div class="inventory-search-empty" id="inventorySearchEmpty">
            No medicine matches your search.
        </div>
    </section>

    <section class="utility-panel-pane" data-utility-pane="recent_records">
        <p class="utility-pane-note">All recent records for this student.</p>
        <div class="consultation-treatment-table-wrap">
            <table class="consultation-treatment-table">
                <thead>
                    <tr>
                        <th class="treatment-date-col">Date</th>
                        <th class="treatment-time-col">Time In</th>
                        <th class="treatment-time-col">Time Out</th>
                        <th class="treatment-service-col">Service</th>
                        <th class="treatment-complaint-col">Complaints / Impression</th>
                        <th class="treatment-medicine-col">Treatment / Medicines</th>
                        <th class="treatment-quantity-col">Qty</th>
                        <th class="treatment-staff-col">Attending Staff</th>
                    </tr>
                </thead>
                <tbody>
                    @php
                        $recentConsultations = $student ? \App\Models\Consultation::with(['medicineItem', 'medicines.item'])->where('user_id', $student->id)->orderByDesc('created_at')->get() : collect();
                    @endphp
                    @if($student && $recentConsultations->count())
                        @forelse($recentConsultations as $consultation)
                            @php
                                $consultationMedicineLines = $consultation->medicines->filter(fn ($line) => trim((string) ($line->medicine ?: optional($line->item)->name)) !== '');
                                $consultMedicine = $consultationMedicineLines->isNotEmpty()
                                    ? $consultationMedicineLines->map(fn ($line) => $line->medicine ?: optional($line->item)->name)->implode(', ')
                                    : trim((string) (optional($consultation->medicineItem)->name ?: $consultation->medicine));
                                $consultStaff = trim((string) ($consultation->attending_staff_name ?: optional($consultation->attendingStaff)->name));
                                $consultQuantity = $consultationMedicineLines->isNotEmpty()
                                    ? $consultationMedicineLines->map(fn ($line) => rtrim(rtrim(number_format((float) $line->quantity, 2, '.', ''), '0'), '.'))->implode(', ')
                                    : ((float) $consultation->medicine_quantity > 0 ? rtrim(rtrim(number_format((float) $consultation->medicine_quantity, 2, '.', ''), '0'), '.') : '');
                                $consultTimeIn = $consultation->time_in ?: optional($consultation->created_at)->format('H:i:s');
                                $consultTimeOut = $consultation->time_out ?: optional($consultation->updated_at)->format('H:i:s');
                                $consultComplaint = trim((string) $consultation->reason_for_visit);
                                $consultImpression = trim((string) $consultation->comments);
                                $consultReferralLabels = [
                                    'hospital_without_nurse' => 'Refer to Hospital (Without Nurse)',
                                    'hospital_with_nurse' => 'Refer to Hospital (With Nurse)',
                                    'general' => 'Referral (General)',
                                    'others' => 'Others',
                                ];
                                $consultReferralType = trim((string) ($consultation->referral_type ?? ''));
                                $consultReferral = $consultReferralType !== '' && $consultReferralType !== 'none'
                                    ? ($consultReferralLabels[$consultReferralType] ?? $consultReferralType)
                                    : '';
                                if ($consultReferral !== '' && trim((string) ($consultation->referral_details ?? '')) !== '') {
                                    $consultReferral .= ': ' . trim((string) $consultation->referral_details);
                                }
                            @endphp
                            <tr>
                                <td>{{ optional($consultation->consultation_date)->format('m/d/Y') ?: optional($consultation->created_at)->format('m/d/Y') ?: '-' }}</td>
                                <td>{{ $consultTimeIn ? \Carbon\Carbon::parse($consultTimeIn)->format('g:i A') : '-' }}</td>
                                <td>{{ $consultTimeOut ? \Carbon\Carbon::parse($consultTimeOut)->format('g:i A') : '-' }}</td>
                                <td>{{ $consultation->service ?: 'Consultation' }}</td>
                                <td>
                                    <span class="treatment-table-entry">
                                        <span class="treatment-table-entry-label">Complaint</span>
                                        <span class="treatment-table-entry-value">{{ $consultComplaint ?: 'No complaint recorded.' }}</span>
                                    </span>
                                    <span class="treatment-table-entry">
                                        <span class="treatment-table-entry-label">Impression</span>
                                        <span class="treatment-table-entry-value">{{ $consultImpression ?: 'No assessment recorded.' }}</span>
                                    </span>
                                    @if($consultReferral !== '')
                                        <span class="treatment-table-entry">
                                            <span class="treatment-table-entry-label">Referral</span>
                                            <span class="treatment-table-entry-value">{{ $consultReferral }}</span>
                                        </span>
                                    @endif
                                </td>
                                <td>{{ $consultMedicine !== '' && strtolower($consultMedicine) !== 'none' ? $consultMedicine : 'No medicine issued' }}</td>
                                <td class="treatment-quantity-col">{{ $consultQuantity !== '' ? $consultQuantity : '-' }}</td>
                                <td>{{ $consultStaff ?: 'Clinic Staff' }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="8" class="consultation-treatment-empty">No recent records are available for this student.</td>
                            </tr>
                        @endforelse
                    @else
                        <tr>
                            <td colspan="8" class="consultation-treatment-empty">No recent records are available for this student.</td>
                        </tr>
                    @endif
                </tbody>
            </table>
        </div>
    </section>

    <section class="utility-panel-pane" data-utility-pane="treatments">
        <p class="utility-pane-note">The 20 most recent finalized consultations for this patient.</p>
        <div class="consultation-treatment-table-wrap">
            <table class="consultation-treatment-table">
                <thead>
                    <tr>
                        <th class="treatment-date-col">Date</th>
                        <th class="treatment-time-col">Time In</th>
                        <th class="treatment-time-col">Time Out</th>
                        <th class="treatment-service-col">Service</th>
                        <th class="treatment-complaint-col">Complaints / Impression</th>
                        <th class="treatment-medicine-col">Treatment / Medicines</th>
                        <th class="treatment-quantity-col">Qty</th>
                        <th class="treatment-staff-col">Attending Staff</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($studentTreatments as $treatment)
                        @php
                            $treatmentMedicineLines = $treatment->medicines->filter(fn ($line) => trim((string) ($line->medicine ?: optional($line->item)->name)) !== '');
                            $treatmentMedicine = $treatmentMedicineLines->isNotEmpty()
                                ? $treatmentMedicineLines->map(fn ($line) => $line->medicine ?: optional($line->item)->name)->implode(', ')
                                : trim((string) (optional($treatment->medicineItem)->name ?: $treatment->medicine));
                            $treatmentStaff = trim((string) ($treatment->attending_staff_name ?: optional($treatment->attendingStaff)->name));
                            $treatmentQuantity = $treatmentMedicineLines->isNotEmpty()
                                ? $treatmentMedicineLines->map(fn ($line) => rtrim(rtrim(number_format((float) $line->quantity, 2, '.', ''), '0'), '.'))->implode(', ')
                                : ((float) $treatment->medicine_quantity > 0 ? rtrim(rtrim(number_format((float) $treatment->medicine_quantity, 2, '.', ''), '0'), '.') : '');
                            $treatmentTimeIn = $treatment->time_in ?: optional($treatment->created_at)->format('H:i:s');
                            $treatmentTimeOut = $treatment->time_out ?: optional($treatment->updated_at)->format('H:i:s');
                            $treatmentComplaint = trim((string) $treatment->reason_for_visit);
                            $treatmentImpression = trim((string) $treatment->comments);
                            $treatmentReferralLabels = [
                                'hospital_without_nurse' => 'Refer to Hospital (Without Nurse)',
                                'hospital_with_nurse' => 'Refer to Hospital (With Nurse)',
                                'general' => 'Referral (General)',
                                'others' => 'Others',
                            ];
                            $treatmentReferralType = trim((string) ($treatment->referral_type ?? ''));
                            $treatmentReferral = $treatmentReferralType !== '' && $treatmentReferralType !== 'none'
                                ? ($treatmentReferralLabels[$treatmentReferralType] ?? $treatmentReferralType)
                                : '';
                            if ($treatmentReferral !== '' && trim((string) ($treatment->referral_details ?? '')) !== '') {
                                $treatmentReferral .= ': ' . trim((string) $treatment->referral_details);
                            }
                        @endphp
                        <tr>
                            <td>{{ optional($treatment->consultation_date)->format('m/d/Y') ?: '-' }}</td>
                            <td>{{ $treatmentTimeIn ? \Carbon\Carbon::parse($treatmentTimeIn)->format('g:i A') : '-' }}</td>
                            <td>{{ $treatmentTimeOut ? \Carbon\Carbon::parse($treatmentTimeOut)->format('g:i A') : '-' }}</td>
                            <td>{{ $treatment->service ?: 'Consultation' }}</td>
                            <td>
                                <span class="treatment-table-entry">
                                    <span class="treatment-table-entry-label">Complaint</span>
                                    <span class="treatment-table-entry-value">{{ $treatmentComplaint ?: 'No complaint recorded.' }}</span>
                                </span>
                                <span class="treatment-table-entry">
                                    <span class="treatment-table-entry-label">Impression</span>
                                    <span class="treatment-table-entry-value">{{ $treatmentImpression ?: 'No assessment recorded.' }}</span>
                                </span>
                                @if($treatmentReferral !== '')
                                    <span class="treatment-table-entry">
                                        <span class="treatment-table-entry-label">Referral</span>
                                        <span class="treatment-table-entry-value">{{ $treatmentReferral }}</span>
                                    </span>
                                @endif
                            </td>
                            <td>{{ $treatmentMedicine !== '' && strtolower($treatmentMedicine) !== 'none' ? $treatmentMedicine : 'No medicine issued' }}</td>
                            <td class="treatment-quantity-col">{{ $treatmentQuantity !== '' ? $treatmentQuantity : '-' }}</td>
                            <td>{{ $treatmentStaff ?: 'Clinic Staff' }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="consultation-treatment-empty">No previous treatment records are available for this student.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </section>
</aside>

<script>
    (function () {
        const medicineEntries = Array.from(document.querySelectorAll('[data-medicine-entry]'));
        const addMedicineButton = document.getElementById('addMedicineButton');
        const medicineSelectionCount = document.getElementById('medicineSelectionCount');
        const utilityPanel = document.getElementById('right-utility-panel');
        const utilityRail = document.getElementById('consultationUtilityRail');
        const utilityButtons = Array.from(document.querySelectorAll('[data-utility-target]'));
        const utilityPanes = Array.from(document.querySelectorAll('[data-utility-pane]'));
        const utilityTitle = document.getElementById('utilityPanelTitle');
        const expandUtilityPanel = document.getElementById('expand-utility-panel');
        const closeUtilityPanel = document.getElementById('close-utility-panel');
        const headerQuickActions = document.getElementById('headerQuickActions');
        const inventoryCards = Array.from(document.querySelectorAll('[data-inventory-item]'));
        const issueButtons = Array.from(document.querySelectorAll('[data-issue-medicine]'));
        const inventorySearchShell = document.getElementById('inventoryPanelSearch');
        const inventorySearchInput = document.getElementById('inventoryPanelSearchInput');
        const inventorySearchToggle = document.getElementById('inventoryPanelSearchToggle');
        const inventorySearchEmpty = document.getElementById('inventorySearchEmpty');
        const consultationForm = document.getElementById('consultationForm');
        const finalizeButton = document.getElementById('finalizeConsultationButton');
        const consultationSuccessOverlay = document.getElementById('consultationSuccessOverlay');
        const bpInput = document.getElementById('consultBp');
        const referralSelect = document.getElementById('consultReferral');
        const referralDetailsGroup = document.getElementById('referralDetailsGroup');
        const referralDetailsInput = document.getElementById('consultReferralDetails');
        const covidStatusInputs = Array.from(document.querySelectorAll('input[name="covid_status"]'));
        const covidPositiveDateGroup = document.getElementById('covidPositiveDateGroup');
        const covidPositiveDateInput = document.getElementById('consultCovidPositiveDate');
        let isSubmittingConsultation = false;
        let activeUtility = '';

        const syncReferralDetails = function () {
            const showDetails = referralSelect?.value === 'others';
            referralDetailsGroup?.classList.toggle('is-visible', showDetails);
            if (referralDetailsInput) {
                referralDetailsInput.required = showDetails;
            }
        };

        referralSelect?.addEventListener('change', syncReferralDetails);

        const syncCovidPositiveDate = function () {
            const selectedStatus = covidStatusInputs.find(function (input) {
                return input.checked;
            })?.value || 'No';
            const showDate = selectedStatus === 'Yes';

            covidPositiveDateGroup?.classList.toggle('is-visible', showDate);
            if (covidPositiveDateInput) {
                covidPositiveDateInput.required = showDate;
                covidPositiveDateInput.disabled = !showDate;
                if (!showDate) {
                    covidPositiveDateInput.value = '';
                }
            }
        };

        covidStatusInputs.forEach(function (input) {
            input.addEventListener('change', syncCovidPositiveDate);
        });

        // Auto-format Blood Pressure with slash
        if (bpInput) {
            bpInput.addEventListener('input', function () {
                let value = this.value.replace(/[^\d]/g, '');
                if (value.length >= 3 && !this.value.includes('/')) {
                    value = value.slice(0, value.length - 2) + '/' + value.slice(-2);
                }
                this.value = value.slice(0, 8);
                validateVitalField(this);
            });
        }

        // Real-time vital validation
        function validateVitalField(input) {
            const vital = input.dataset.vital;
            const errorId = vital === 'height' ? 'heightError' :
                           vital === 'weight' ? 'weightError' :
                           vital === 'temp' ? 'tempError' :
                           vital === 'bp' ? 'bpError' :
                           vital === 'pulse_rate' ? 'pulseError' :
                           vital === 'respiratory_rate' ? 'respiratoryError' : null;

            const successId = vital === 'height' ? 'heightSuccess' :
                             vital === 'weight' ? 'weightSuccess' :
                             vital === 'temp' ? 'tempSuccess' :
                             vital === 'bp' ? 'bpSuccess' :
                             vital === 'pulse_rate' ? 'pulseSuccess' :
                             vital === 'respiratory_rate' ? 'respiratorySuccess' : null;

            const errorEl = document.getElementById(errorId);
            const successEl = document.getElementById(successId);
            const value = input.value.trim();

            if (!value) {
                input.classList.remove('is-valid', 'is-invalid');
                if (errorEl) errorEl.classList.remove('show');
                if (successEl) successEl.classList.remove('show');
                return;
            }

            let isValid = false;
            let errorMsg = '';

            if (vital === 'bp') {
                isValid = /^\d{2,3}\/\d{2,3}$/.test(value);
                errorMsg = 'Format: 120/80';
            } else if (vital === 'height') {
                const num = parseFloat(value);
                isValid = num >= 1 && num <= 10;
                errorMsg = 'Height must be 1-10 ft';
            } else if (vital === 'weight') {
                const num = parseFloat(value);
                isValid = num >= 1 && num <= 1100;
                errorMsg = 'Weight must be 1-1100 lbs';
            } else if (vital === 'temp') {
                const num = parseFloat(value);
                isValid = num >= 30 && num <= 45;
                errorMsg = 'Temperature must be 30-45°C';
            } else if (vital === 'pulse_rate') {
                const num = parseInt(value);
                isValid = num >= 1 && num <= 300;
                errorMsg = 'Pulse rate must be 1-300 bpm';
            } else if (vital === 'respiratory_rate') {
                const num = parseInt(value);
                isValid = num >= 1 && num <= 120;
                errorMsg = 'Respiratory rate must be 1-120 cpm';
            }

            if (isValid) {
                input.classList.remove('is-invalid');
                input.classList.add('is-valid');
                if (errorEl) errorEl.classList.remove('show');
                if (successEl) successEl.classList.add('show');
            } else {
                input.classList.remove('is-valid');
                input.classList.add('is-invalid');
                if (errorEl) {
                    errorEl.textContent = errorMsg;
                    errorEl.classList.add('show');
                }
                if (successEl) successEl.classList.remove('show');
            }
        }

        // Add validation listeners to all vital fields
        document.querySelectorAll('[data-vital]').forEach(function (field) {
            field.addEventListener('input', function () {
                validateVitalField(this);
            });
            field.addEventListener('change', function () {
                validateVitalField(this);
            });
        });

        const formatQty = function (value) {
            const numeric = Number(value || 0);
            if (Number.isNaN(numeric)) {
                return '0';
            }
            return Number.isInteger(numeric) ? String(numeric) : numeric.toFixed(2).replace(/\.?0+$/, '');
        };

        const utilityLabels = {
            documents: 'Uploaded Documents',
            inventory: 'Live Medicine Stock',
            recent_records: 'Recent Records',
            treatments: 'Treatment Record'
        };

        const setInventorySearchOpen = function (isOpen) {
            if (!inventorySearchShell || !inventorySearchToggle) return;
            inventorySearchShell.classList.toggle('is-open', isOpen);
            inventorySearchToggle.classList.toggle('is-open', isOpen);
            inventorySearchToggle.setAttribute('aria-expanded', isOpen ? 'true' : 'false');
            inventorySearchToggle.setAttribute('aria-label', isOpen ? 'Close medicine search' : 'Open medicine search');
        };

        const filterInventoryCards = function () {
            const searchTerm = (inventorySearchInput?.value || '').trim().toLowerCase();
            let visibleCount = 0;

            inventoryCards.forEach(function (card) {
                const matches = searchTerm === '' || card.textContent.toLowerCase().includes(searchTerm);
                card.style.display = matches ? '' : 'none';
                if (matches) visibleCount += 1;
            });

            inventorySearchEmpty?.classList.toggle('is-visible', inventoryCards.length > 0 && visibleCount === 0);
        };

        const closeUtility = function () {
            utilityPanel.classList.remove('open');
            utilityPanel.classList.remove('is-expanded');
            utilityRail.classList.remove('panel-open');
            utilityRail.classList.remove('panel-expanded');
            utilityPanel.setAttribute('aria-hidden', 'true');
            expandUtilityPanel?.classList.remove('is-visible');
            expandUtilityPanel?.setAttribute('aria-expanded', 'false');
            if (expandUtilityPanel) expandUtilityPanel.textContent = '<';
            utilityButtons.forEach(function (button) {
                button.classList.remove('active');
                button.setAttribute('aria-expanded', 'false');
            });
            activeUtility = '';
        };

        const openUtility = function (target) {
            if (activeUtility === target && utilityPanel.classList.contains('open')) {
                closeUtility();
                return;
            }

            activeUtility = target;
            utilityTitle.textContent = utilityLabels[target] || 'Consultation Tools';
            const isExpandablePanel = target === 'treatments' || target === 'recent_records';
            expandUtilityPanel?.classList.toggle('is-visible', isExpandablePanel);
            if (!isExpandablePanel) {
                utilityPanel.classList.remove('is-expanded');
                utilityRail.classList.remove('panel-expanded');
                expandUtilityPanel?.setAttribute('aria-expanded', 'false');
                if (expandUtilityPanel) expandUtilityPanel.textContent = '<';
            }
            utilityPanes.forEach(function (pane) {
                pane.classList.toggle('active', pane.dataset.utilityPane === target);
            });
            utilityButtons.forEach(function (button) {
                const isActive = button.dataset.utilityTarget === target;
                button.classList.toggle('active', isActive);
                button.setAttribute('aria-expanded', isActive ? 'true' : 'false');
            });
            utilityPanel.classList.add('open');
            utilityRail.classList.add('panel-open');
            utilityPanel.setAttribute('aria-hidden', 'false');
        };

        const toggleUtilityExpansion = function () {
            if (activeUtility !== 'treatments' && activeUtility !== 'recent_records') return;
            const isExpanded = utilityPanel.classList.toggle('is-expanded');
            utilityRail.classList.toggle('panel-expanded', isExpanded);
            expandUtilityPanel?.setAttribute('aria-expanded', isExpanded ? 'true' : 'false');

            const panelName = activeUtility === 'treatments' ? 'treatment record' : 'recent records';
            expandUtilityPanel?.setAttribute(
                'aria-label',
                isExpanded ? `Collapse ${panelName} panel` : `Expand ${panelName} panel`
            );
            expandUtilityPanel?.setAttribute(
                'title',
                isExpanded ? `Collapse ${panelName}` : `Expand ${panelName}`
            );
            if (expandUtilityPanel) {
                expandUtilityPanel.textContent = isExpanded ? '>' : '<';
            }
        };

        const syncQuickActionsState = function () {
            const quickActionsOpen = Boolean(headerQuickActions && headerQuickActions.classList.contains('is-open'));
            utilityRail.classList.toggle('quick-actions-active', quickActionsOpen);
            if (quickActionsOpen && utilityPanel.classList.contains('open')) {
                closeUtility();
                utilityRail.classList.add('quick-actions-active');
            }
        };

        const updateMedicineEntry = function (entry) {
            const select = entry.querySelector('[data-medicine-select]');
            const quantityGroup = entry.querySelector('[data-medicine-quantity-group]');
            const quantityInput = entry.querySelector('[data-medicine-quantity]');
            const quantityLabel = entry.querySelector('[data-quantity-label]');
            const quantityHelp = entry.querySelector('[data-quantity-help]');
            const selectedStock = entry.querySelector('[data-selected-stock]');
            const selected = select?.options[select.selectedIndex];

            if (!select || !quantityInput || !quantityGroup || !quantityLabel || !quantityHelp || !selectedStock) return;

            quantityInput.setCustomValidity('');
            if (!selected || !selected.value) {
                quantityGroup.classList.remove('is-visible');
                quantityLabel.textContent = 'Quantity to Issue';
                quantityHelp.textContent = 'Select a medicine to see the dispensing unit and available stock.';
                quantityInput.placeholder = 'Enter amount';
                quantityInput.value = '';
                quantityInput.removeAttribute('max');
                selectedStock.className = 'selected-stock';
                selectedStock.textContent = '';
                return;
            }

            const dispensingUnit = selected.dataset.dispensingUnit || selected.dataset.stockUnit || 'unit';
            const stockUnit = selected.dataset.stockUnit || 'pcs';
            const availableValue = Number(selected.dataset.availableDispensing || 0);
            const available = formatQty(availableValue);
            const hasConversion = selected.dataset.hasConversion === '1';
            const unitsPerStock = formatQty(selected.dataset.unitsPerStock || 1);
            const isLowStock = selected.dataset.lowStock === '1';

            quantityGroup.classList.add('is-visible');
            quantityLabel.textContent = 'Quantity to Issue (' + dispensingUnit + ')';
            quantityInput.placeholder = 'Enter ' + dispensingUnit + ' quantity';
            quantityInput.max = String(availableValue);
            quantityHelp.textContent = hasConversion
                ? 'Available: ' + available + ' ' + dispensingUnit + ' (' + unitsPerStock + ' ' + dispensingUnit + ' per ' + stockUnit + ').'
                : 'Available: ' + available + ' ' + stockUnit + '.';
            selectedStock.className = 'selected-stock visible' + (isLowStock ? ' low' : '');
            selectedStock.textContent = (isLowStock ? 'Low stock: ' : 'Available: ') + available + ' ' + dispensingUnit;
        };

        const updateMedicineSelections = function () {
            const selectedIds = medicineEntries
                .map(function (entry) { return entry.querySelector('[data-medicine-select]')?.value || ''; })
                .filter(Boolean);
            const visibleCount = medicineEntries.filter(function (entry) {
                return entry.classList.contains('is-visible');
            }).length;

            const medicineEntriesWrap = document.getElementById('medicineEntries');
            medicineEntriesWrap?.classList.toggle('is-multi', visibleCount > 1);
            medicineEntriesWrap?.classList.toggle('is-odd', visibleCount > 1 && visibleCount % 2 === 1);

            document.querySelectorAll('[data-inventory-item]').forEach(function (item) {
                item.classList.toggle('selected', selectedIds.includes(item.dataset.inventoryItem));
            });

            if (medicineSelectionCount) {
                medicineSelectionCount.textContent = selectedIds.length + ' of 5 selected';
            }
            if (addMedicineButton) {
                addMedicineButton.disabled = medicineEntries.every(function (entry) {
                    return entry.classList.contains('is-visible');
                });
            }
        };

        const showNextMedicineEntry = function () {
            const nextEntry = medicineEntries.find(function (entry) {
                return !entry.classList.contains('is-visible');
            });

            if (!nextEntry) return null;
            nextEntry.classList.add('is-visible');
            updateMedicineEntry(nextEntry);
            updateMedicineSelections();
            nextEntry.querySelector('[data-medicine-select]')?.focus();
            return nextEntry;
        };

        medicineEntries.forEach(function (entry) {
            const select = entry.querySelector('[data-medicine-select]');
            const quantityInput = entry.querySelector('[data-medicine-quantity]');
            const removeButton = entry.querySelector('[data-remove-medicine]');

            select?.addEventListener('change', function () {
                updateMedicineEntry(entry);
                updateMedicineSelections();
            });
            quantityInput?.addEventListener('input', function () {
                const selected = select?.options[select.selectedIndex];
                const available = selected && selected.value ? Number(selected.dataset.availableDispensing || 0) : 0;
                const requested = Number(quantityInput.value || 0);
                quantityInput.setCustomValidity(requested > available ? 'Quantity cannot exceed the available medicine stock.' : '');
            });
            removeButton?.addEventListener('click', function () {
                if (select) {
                    select.value = '';
                    select.dispatchEvent(new Event('change', { bubbles: true }));
                }
                if (quantityInput) quantityInput.value = '';

                if (entry.dataset.index !== '0') {
                    entry.classList.remove('is-visible');
                }
                updateMedicineEntry(entry);
                updateMedicineSelections();
            });
        });

        addMedicineButton?.addEventListener('click', showNextMedicineEntry);

        const closeClinicSelects = function (exceptShell) {
            document.querySelectorAll('.clinic-select-shell.is-open').forEach(function (shell) {
                if (shell === exceptShell) return;
                shell.classList.remove('is-open');
                const display = shell.querySelector('.clinic-select-display');
                if (display) {
                    display.classList.remove('is-open');
                    display.setAttribute('aria-expanded', 'false');
                }
            });
        };

        document.querySelectorAll('select[data-clinic-select]').forEach(function (select) {
            const shell = document.createElement('div');
            shell.className = 'clinic-select-shell';
            select.parentNode.insertBefore(shell, select);
            shell.appendChild(select);
            select.classList.add('clinic-select-native');

            const display = document.createElement('button');
            display.type = 'button';
            display.className = 'clinic-select-display';
            display.setAttribute('aria-haspopup', 'listbox');
            display.setAttribute('aria-expanded', 'false');
            display.disabled = select.disabled;

            const menu = document.createElement('div');
            menu.className = 'clinic-select-menu';
            menu.setAttribute('role', 'listbox');

            // Add search input for searchable dropdowns
            let searchInput = null;
            const isConditionSelect = select.id === 'consultCondition';
            const isMedicineSelect = select.matches('[data-medicine-select]');
            const isSearchable = isConditionSelect || isMedicineSelect;

            if (isSearchable) {
                searchInput = document.createElement('input');
                searchInput.type = 'text';
                searchInput.className = 'clinic-select-search';
                searchInput.placeholder = isConditionSelect ? 'Search condition...' : 'Search medicine...';
                searchInput.style.width = '100%';
                searchInput.style.padding = '8px 12px';
                searchInput.style.marginBottom = '8px';
                searchInput.style.border = '1px solid #d1d5db';
                searchInput.style.borderRadius = '4px';
                searchInput.style.fontSize = '14px';
            }

            const syncDisplay = function () {
                const selected = select.options[select.selectedIndex];
                display.textContent = selected ? selected.textContent.trim() : 'Select an option';
                menu.querySelectorAll('.clinic-select-option').forEach(function (optionButton) {
                    optionButton.classList.toggle('is-selected', optionButton.dataset.value === select.value);
                });
                syncPopulatedFieldState(select);
            };

            Array.from(select.options).forEach(function (option) {
                const optionButton = document.createElement('button');
                optionButton.type = 'button';
                optionButton.className = 'clinic-select-option';
                optionButton.dataset.value = option.value;
                optionButton.textContent = option.textContent.trim();
                optionButton.disabled = option.disabled;
                optionButton.addEventListener('click', function () {
                    select.value = option.value;
                    select.dispatchEvent(new Event('change', { bubbles: true }));
                    syncDisplay();
                    closeClinicSelects();
                });
                menu.appendChild(optionButton);
            });

            display.addEventListener('click', function () {
                if (display.disabled) return;
                const opening = !shell.classList.contains('is-open');
                closeClinicSelects(shell);
                shell.classList.toggle('is-open', opening);
                display.classList.toggle('is-open', opening);
                display.setAttribute('aria-expanded', opening ? 'true' : 'false');
            });

            select.addEventListener('change', syncDisplay);

            // Add search input to menu for condition select
            if (searchInput) {
                menu.insertBefore(searchInput, menu.firstChild);

                searchInput.addEventListener('input', function () {
                    const searchTerm = searchInput.value.toLowerCase();
                    const options = menu.querySelectorAll('.clinic-select-option');
                    options.forEach(function (option) {
                        const optionText = option.textContent.toLowerCase();
                        const matches = optionText.includes(searchTerm) || searchTerm === '';
                        option.style.display = matches ? '' : 'none';
                    });
                });

                searchInput.addEventListener('click', function (event) {
                    event.stopPropagation();
                });

                searchInput.addEventListener('keydown', function (event) {
                    if (event.key === 'Escape') {
                        closeClinicSelects();
                    }
                });
            }

            shell.append(display, menu);
            syncDisplay();
        });

        inventoryCards.forEach(function (card) {
            card.addEventListener('click', function (event) {
                if (event.target.closest('[data-issue-medicine]')) return;
                const shouldSelect = !card.classList.contains('selected');
                inventoryCards.forEach(function (item) {
                    item.classList.remove('selected');
                });
                card.classList.toggle('selected', shouldSelect);
            });
        });

        issueButtons.forEach(function (button) {
            button.addEventListener('click', function () {
                const itemId = button.dataset.issueMedicine || '';
                let targetEntry = medicineEntries.find(function (entry) {
                    const select = entry.querySelector('[data-medicine-select]');
                    return entry.classList.contains('is-visible') && select && !select.value;
                });

                if (!targetEntry) targetEntry = showNextMedicineEntry();
                const medicineSelect = targetEntry?.querySelector('[data-medicine-select]');
                const option = medicineSelect
                    ? Array.from(medicineSelect.options).find(function (medicineOption) {
                        return medicineOption.value === itemId;
                    })
                    : null;
                if (!medicineSelect || !option) return;

                medicineSelect.value = itemId;
                medicineSelect.dispatchEvent(new Event('change', { bubbles: true }));
                closeUtility();
                targetEntry.closest('.consult-card')?.scrollIntoView({
                    behavior: 'smooth',
                    block: 'center'
                });
                window.setTimeout(function () {
                    targetEntry.querySelector('[data-medicine-quantity]')?.focus();
                }, 350);
            });
        });

        inventorySearchToggle?.addEventListener('click', function () {
            const isOpening = !inventorySearchShell.classList.contains('is-open');
            setInventorySearchOpen(isOpening);

            if (isOpening) {
                window.setTimeout(function () {
                    inventorySearchInput?.focus();
                }, 120);
            } else if (inventorySearchInput && inventorySearchInput.value.trim() === '') {
                inventorySearchInput.blur();
            }
        });
        inventorySearchInput?.addEventListener('input', filterInventoryCards);
        inventorySearchInput?.addEventListener('focus', function () {
            setInventorySearchOpen(true);
        });

        utilityButtons.forEach(function (button) {
            button.setAttribute('aria-expanded', 'false');
            button.addEventListener('click', function () {
                openUtility(button.dataset.utilityTarget);
            });
        });
        closeUtilityPanel.addEventListener('click', closeUtility);
        expandUtilityPanel?.addEventListener('click', toggleUtilityExpansion);
        if (headerQuickActions) {
            new MutationObserver(syncQuickActionsState).observe(headerQuickActions, {
                attributes: true,
                attributeFilter: ['class']
            });
            syncQuickActionsState();
        }
        document.addEventListener('keydown', function (event) {
            if (event.key === 'Escape' && utilityPanel.classList.contains('open')) {
                closeUtility();
            }
            if (event.key === 'Escape') {
                closeClinicSelects();
            }
        });
        document.addEventListener('click', function (event) {
            if (!event.target.closest('.clinic-select-shell')) {
                closeClinicSelects();
            }
        });
        consultationForm?.addEventListener('submit', function (event) {
            if (!consultationForm.checkValidity()) {
                event.preventDefault();
                consultationForm.reportValidity();
                return;
            }

            if (!finalizeButton || isSubmittingConsultation) {
                event.preventDefault();
                return;
            }

            event.preventDefault();
            isSubmittingConsultation = true;
            finalizeButton.classList.add('is-finalizing');
            finalizeButton.setAttribute('aria-disabled', 'true');
            const label = finalizeButton.querySelector('span');
            if (label) label.textContent = 'Finalizing Consultation...';
            consultationSuccessOverlay?.classList.add('is-open');
            consultationSuccessOverlay?.setAttribute('aria-hidden', 'false');
            window.setTimeout(function () {
                consultationForm.submit();
            }, 850);
        });

        const syncPopulatedFieldState = function (field) {
            if (!field) return;
            const val = (field.value || '').trim();
            const hasVal = val !== '' && val !== 'none';

            field.classList.toggle('has-value', hasVal);

            const vitalGroup = field.closest('.physical-assessment-card .form-group');
            if (vitalGroup) {
                vitalGroup.classList.toggle('has-value', hasVal);
            }

            const shell = field.closest('.clinic-select-shell');
            if (shell) {
                const display = shell.querySelector('.clinic-select-display');
                if (display) {
                    display.classList.toggle('has-value', hasVal);
                }
            }
        };

        const syncAllPopulatedFields = function () {
            const form = document.getElementById('consultationForm');
            if (!form) return;
            form.querySelectorAll('input, select, textarea').forEach(syncPopulatedFieldState);
        };

        const consultFormEl = document.getElementById('consultationForm');
        if (consultFormEl) {
            consultFormEl.addEventListener('input', function (e) {
                syncPopulatedFieldState(e.target);
            });
            consultFormEl.addEventListener('change', function (e) {
                syncPopulatedFieldState(e.target);
            });
        }

        medicineEntries.forEach(updateMedicineEntry);
        updateMedicineSelections();
        syncReferralDetails();
        syncCovidPositiveDate();
        syncAllPopulatedFields();
    })();
</script>
@endsection
