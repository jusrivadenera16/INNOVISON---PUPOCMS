@extends('layouts.admin')

@section('title', 'Health Forms Applicants List')

@push('styles')
<style>
    .logbook-shell {
        max-width: 1480px;
        margin: 0 auto;
        padding: 22px;
    }
    .logbook-head {
        display: flex;
        justify-content: space-between;
        align-items: flex-start;
        gap: 18px;
        margin-bottom: 24px;
    }
    .logbook-title {
        margin: 0;
        font-size: 30px;
        font-weight: 900;
        color: #ffffff;
        letter-spacing: -0.03em;
    }
    .logbook-copy {
        margin: 8px 0 0;
        color: rgba(255,255,255,0.78);
        font-size: 14px;
        line-height: 1.6;
    }
    .logbook-back {
        min-width: 132px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        gap: 7px;
        padding: 10px 16px;
        border: 1px solid #7f1d2d;
        border-radius: 14px;
        background: #7f1d2d;
        color: #ffffff !important;
        font-size: 13px;
        font-weight: 800;
        text-decoration: none;
        position: relative;
        overflow: hidden;
        transition: color .18s ease, background .18s ease, border-color .18s ease, transform .18s ease;
    }
    .logbook-back::after,
    .filter-btn-open::after,
    .filter-btn::after {
        content: "";
        position: absolute;
        top: -40%;
        left: -130%;
        width: 120%;
        height: 180%;
        background: linear-gradient(115deg, rgba(250, 204, 21, 0) 0%, rgba(255, 247, 181, .58) 45%, rgba(250, 204, 21, 0) 100%);
        transform: skewX(-20deg);
        transition: left 1.5s ease;
        pointer-events: none;
    }
    .logbook-back:hover::after,
    .filter-btn-open:hover::after,
    .filter-btn:hover::after {
        left: 125%;
    }
    .logbook-back:hover,
    .logbook-back:focus-visible {
        background: #facc15;
        border-color: #facc15;
        color: #70131B;
        transform: translateY(-1px);
        outline: none;
    }
    .logbook-toolbar {
        display: flex;
        gap: 12px;
        align-items: center;
        margin-bottom: 24px;
    }
    .logbook-search-wrap {
        position: relative;
        flex: 1;
        min-width: 260px;
    }
    .logbook-search-wrap .voice-field-wrap {
        width: 100%;
    }
    .logbook-search-input {
        width: 100%;
        height: 44px;
        border: 1px solid #cbd5e1;
        border-radius: 12px;
        padding: 0 46px 0 14px;
        font-size: 14px;
        color: #111827;
        background: #ffffff;
        transition: border-color .18s ease, box-shadow .18s ease;
    }
    .logbook-search-input:focus {
        outline: none;
        border-color: #7f1d2d;
        box-shadow: 0 0 0 3px rgba(127, 29, 45, 0.1);
    }
    .logbook-search-wrap .voice-field-inline-mic {
        right: 10px;
    }
    .logbook-toolbar-actions {
        display: inline-flex;
        align-items: center;
        justify-content: flex-end;
        gap: 10px;
        flex: 0 0 auto;
    }
    .logbook-total-card {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        min-height: 44px;
        padding: 0 18px;
        border: 1px solid rgba(127, 29, 45, 0.18);
        border-radius: 12px;
        background: #ffffff;
        color: #111827;
        font-size: 13px;
        font-weight: 900;
        white-space: nowrap;
        box-shadow: 0 12px 24px rgba(15, 23, 42, 0.06);
    }
    .logbook-total-card span {
        color: #7f1d2d;
        margin-left: 6px;
    }
    .logbook-export-btn {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        gap: 8px;
        min-height: 44px;
        padding: 0 20px;
        border: 1px solid rgba(250, 204, 21, 0.95);
        border-radius: 12px;
        background: #facc15;
        color: #111827;
        font-size: 13px;
        font-weight: 900;
        text-transform: uppercase;
        letter-spacing: 0.04em;
        text-decoration: none;
        box-shadow: 0 14px 28px rgba(250, 204, 21, 0.22);
        transition: all .18s ease;
        white-space: nowrap;
    }
    .logbook-export-btn:hover {
        background: #eab308;
        border-color: #eab308;
        color: #111827;
        transform: translateY(-1px);
        box-shadow: 0 16px 30px rgba(234, 179, 8, 0.28);
    }
    .filter-btn-open {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        gap: 8px;
        padding: 12px 20px;
        border: 1px solid #7f1d2d;
        border-radius: 12px;
        background: #7f1d2d;
        color: #ffffff;
        font-size: 13px;
        font-weight: 800;
        text-transform: uppercase;
        letter-spacing: 0.04em;
        cursor: pointer;
        position: relative;
        overflow: hidden;
        transition: color .18s ease, background .18s ease, border-color .18s ease, transform .18s ease;
        white-space: nowrap;
    }
    .filter-btn-open:hover,
    .filter-btn-open:focus-visible {
        background: #facc15;
        border-color: #facc15;
        color: #70131B;
        transform: translateY(-1px);
        outline: none;
    }
    .filter-btn-open svg {
        width: 18px;
        height: 18px;
        flex: 0 0 auto;
    }
    .modal-overlay {
        display: none;
        position: fixed;
        top: 0;
        left: 0;
        right: 0;
        bottom: 0;
        background: rgba(15, 23, 42, 0.6);
        z-index: 999;
    }
    .modal-overlay.active {
        display: flex;
        align-items: center;
        justify-content: center;
    }
    .modal-content {
        position: relative;
        background: #ffffff;
        border-radius: 20px;
        padding: 0;
        max-width: 620px;
        width: 90%;
        max-height: 90vh;
        overflow: visible;
        box-shadow: 0 20px 60px rgba(15, 23, 42, 0.2);
        animation: slideUp 0.3s ease;
    }
    @keyframes slideUp {
        from {
            transform: translateY(20px);
            opacity: 0;
        }
        to {
            transform: translateY(0);
            opacity: 1;
        }
    }
    .modal-header {
        display: flex;
        align-items: center;
        gap: 14px;
        min-height: 112px;
        margin: 0;
        padding: 24px 72px 24px 26px;
        border-radius: 20px 20px 0 0;
        background: linear-gradient(135deg, #7f1d2d 0%, #a5121f 100%);
        color: #ffffff;
    }
    .modal-title-icon {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        width: 50px;
        height: 50px;
        border-radius: 12px;
        border: 1px solid rgba(255, 255, 255, .24);
        background: rgba(255, 255, 255, .12);
        color: #facc15;
        flex: 0 0 auto;
    }
    .modal-title-icon svg {
        width: 24px;
        height: 24px;
        stroke: currentColor;
        stroke-width: 2;
        fill: none;
    }
    .modal-header h2 {
        margin: 0;
        font-size: 24px;
        font-weight: 900;
        color: #ffffff !important;
    }
    .modal-header p {
        margin: 4px 0 0;
        color: #ffffff !important;
        font-size: 13px;
        font-weight: 700;
        line-height: 1.4;
    }
    .modal-close {
        position: absolute;
        top: 24px;
        right: 24px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        width: 44px;
        height: 44px;
        border-radius: 50%;
        border: 1px solid rgba(255, 255, 255, .24);
        background: rgba(255, 255, 255, .12);
        font-size: 26px;
        color: #ffffff;
        cursor: pointer;
        line-height: 1;
        overflow: hidden;
        transition: color .18s ease, background .18s ease, border-color .18s ease, transform .18s ease;
    }
    .modal-close::after {
        content: "";
        position: absolute;
        top: -40%;
        left: -130%;
        width: 120%;
        height: 180%;
        background: linear-gradient(115deg, rgba(250, 204, 21, 0) 0%, rgba(255, 247, 181, .65) 45%, rgba(250, 204, 21, 0) 100%);
        transform: skewX(-20deg);
        transition: left 1.35s ease;
        pointer-events: none;
    }
    .modal-close:hover,
    .modal-close:focus-visible {
        background: #facc15;
        border-color: #facc15;
        color: #70131B;
        transform: translateY(-1px);
        outline: none;
    }
    .modal-close:hover::after,
    .modal-close:focus-visible::after {
        left: 125%;
    }
    .filters-grid {
        display: grid;
        grid-template-columns: 1fr;
        gap: 16px;
        margin-bottom: 24px;
    }
    .filters-form {
        padding: 26px;
        max-height: calc(90vh - 112px);
        overflow-y: auto;
        scrollbar-width: none;
        -ms-overflow-style: none;
    }
    .filters-form::-webkit-scrollbar {
        display: none;
    }
    .filter-group {
        position: relative;
    }
    .filter-group:has(.premium-select-shell.is-open) {
        z-index: 80;
    }
    .filter-group.is-select-open {
        z-index: 80;
    }
    .filter-group label {
        display: block;
        margin-bottom: 8px;
        font-size: 12px;
        font-weight: 800;
        text-transform: uppercase;
        color: #475569;
        letter-spacing: 0.04em;
    }
    .filter-group input,
    .filter-group select {
        width: 100%;
        height: 44px;
        border: 1px solid #cbd5e1;
        border-radius: 12px;
        padding: 0 14px;
        font-size: 14px;
        color: #111827;
    }
    .filter-group input:focus,
    .filter-group select:focus {
        outline: none;
        border-color: #7f1d2d;
        box-shadow: 0 0 0 3px rgba(127, 29, 45, 0.1);
    }
    .filter-actions {
        display: flex;
        gap: 10px;
    }
    .filter-btn {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        min-height: 44px;
        padding: 0 18px;
        border-radius: 12px;
        font-weight: 800;
        cursor: pointer;
        border: 1px solid #7f1d2d;
        font-size: 13px;
        text-transform: uppercase;
        letter-spacing: 0.04em;
        transition: color .18s ease, background .18s ease, border-color .18s ease, transform .18s ease;
        flex: 1;
        position: relative;
        overflow: hidden;
        text-decoration: none;
    }
    .filter-btn,
    .filter-btn.primary,
    .filter-btn.secondary {
        background: #7f1d2d;
        color: #ffffff;
    }
    .filter-btn.secondary {
        background: #ffffff;
        border-color: #e5e7eb;
        color: #111827 !important;
    }
    .filter-btn:hover,
    .filter-btn:focus-visible {
        background: #facc15;
        border-color: #facc15;
        color: #70131B;
        transform: translateY(-1px);
        outline: none;
    }
    .logbook-table-wrap {
        background: #ffffff;
        border-radius: 20px;
        padding: 12px;
        box-shadow: 0 14px 30px rgba(15, 23, 42, 0.12);
        overflow-x: auto;
    }
    .logbook-table {
        width: 100%;
        border-collapse: collapse;
    }
    .logbook-table th,
    .logbook-table td {
        padding: 14px 12px;
        border-bottom: 1px solid #e2e8f0;
        font-size: 13px;
        text-align: left;
        color: #111827;
        vertical-align: middle;
    }
    .logbook-table th {
        font-size: 11px;
        font-weight: 900;
        text-transform: uppercase;
        letter-spacing: 0.05em;
        color: #64748b;
        background: #f8fafc;
    }
    .logbook-table tbody tr[data-logbook-row] {
        cursor: pointer;
        transition: background .18s ease, transform .18s ease, box-shadow .18s ease;
    }
    .logbook-table tbody tr[data-logbook-row]:hover,
    .logbook-table tbody tr[data-logbook-row]:focus-within {
        background: #fff7ed;
        box-shadow: inset 4px 0 0 #facc15;
    }
    .logbook-record-link {
        border: 0;
        background: transparent;
        padding: 0;
        color: #70131B;
        font-weight: 900;
        text-decoration: none;
        cursor: pointer;
        font: inherit;
        text-align: left;
    }
    .logbook-record-link:hover,
    .logbook-record-link:focus-visible {
        color: #9f1239;
        text-decoration: underline;
        outline: none;
    }
    .logbook-view-link {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        min-height: 34px;
        padding: 8px 12px;
        border-radius: 10px;
        border: 1px solid #7f1d2d;
        background: #7f1d2d;
        color: #ffffff !important;
        font-size: 12px;
        font-weight: 900;
        text-decoration: none;
        white-space: nowrap;
        transition: background .18s ease, border-color .18s ease, color .18s ease, transform .18s ease;
    }
    .logbook-view-link:hover,
    .logbook-view-link:focus-visible {
        background: #facc15;
        border-color: #facc15;
        color: #70131B !important;
        transform: translateY(-1px);
        outline: none;
    }
    .logbook-report-modal {
        position: fixed;
        inset: 0;
        z-index: 2400;
        display: none;
        align-items: center;
        justify-content: center;
        padding: 20px;
        background: rgba(15, 23, 42, 0.62);
        backdrop-filter: blur(6px);
    }
    .logbook-report-modal.is-open {
        display: flex;
    }
    .logbook-report-card {
        width: min(860px, 100%);
        max-height: min(760px, calc(100dvh - 36px));
        overflow: hidden;
        display: flex;
        flex-direction: column;
        border: 1px solid rgba(250, 204, 21, 0.32);
        border-radius: 18px;
        background: #ffffff;
        box-shadow: 0 28px 74px rgba(15, 23, 42, 0.30);
    }
    .logbook-report-head {
        display: flex;
        align-items: flex-start;
        justify-content: space-between;
        gap: 16px;
        padding: 22px 24px;
        background: linear-gradient(135deg, #70131B, #9f1d2d);
        color: #ffffff;
    }
    .logbook-report-head-main {
        min-width: 0;
        display: flex;
        align-items: flex-start;
        gap: 14px;
    }
    .logbook-report-icon {
        width: 46px;
        height: 46px;
        flex: 0 0 46px;
        display: grid;
        place-items: center;
        border-radius: 14px;
        border: 1px solid rgba(250, 204, 21, 0.36);
        background: rgba(255, 255, 255, 0.11);
        color: #facc15;
    }
    .logbook-report-icon svg {
        width: 23px;
        height: 23px;
    }
    .logbook-report-title {
        margin: 0;
        color: #ffffff;
        font-size: 22px;
        font-weight: 900;
        letter-spacing: -0.02em;
    }
    .logbook-report-copy {
        margin: 5px 0 0;
        color: rgba(255,255,255,.86);
        font-size: 13px;
        font-weight: 700;
        line-height: 1.5;
    }
    .logbook-report-close {
        width: 42px;
        height: 42px;
        flex: 0 0 42px;
        display: grid;
        place-items: center;
        border: 1px solid rgba(250, 204, 21, .44);
        border-radius: 999px;
        background: rgba(112, 19, 27, .28);
        color: #ffffff;
        cursor: pointer;
        transition: background .18s ease, border-color .18s ease, color .18s ease, transform .18s ease;
    }
    .logbook-report-close:hover,
    .logbook-report-close:focus-visible {
        border-color: #facc15;
        background: #facc15;
        color: #70131B;
        transform: translateY(-1px);
        outline: none;
    }
    .logbook-report-close svg {
        width: 20px;
        height: 20px;
    }
    .logbook-report-body {
        overflow-y: auto;
        padding: 22px 24px 24px;
    }
    .logbook-report-grid {
        display: grid;
        grid-template-columns: repeat(2, minmax(0, 1fr));
        gap: 14px;
    }
    .logbook-report-field,
    .logbook-report-conditions {
        padding: 15px 16px;
        border: 1px solid #ead8dc;
        border-radius: 14px;
        background: linear-gradient(180deg, #ffffff 0%, #fffafa 100%);
        box-shadow: 0 10px 22px rgba(112, 19, 27, .05);
    }
    .logbook-report-field span,
    .logbook-report-conditions span {
        display: block;
        color: #64748b;
        font-size: 11px;
        font-weight: 900;
        text-transform: uppercase;
        letter-spacing: .04em;
    }
    .logbook-report-field strong,
    .logbook-report-conditions strong {
        display: block;
        margin-top: 6px;
        color: #111827;
        font-size: 14px;
        font-weight: 900;
        line-height: 1.35;
        overflow-wrap: anywhere;
    }
    .logbook-report-conditions {
        grid-column: 1 / -1;
    }
    .logbook-report-condition-list {
        display: grid;
        gap: 8px;
        margin: 12px 0 0;
        padding: 0;
        list-style: none;
    }
    .logbook-report-condition-list li {
        padding: 10px 12px;
        border-radius: 10px;
        background: #fff7ed;
        color: #334155;
        font-size: 13px;
        font-weight: 700;
        line-height: 1.45;
    }
    .logbook-report-condition-list b {
        color: #70131B;
        font-weight: 900;
    }
    .status-badge {
        display: inline-flex;
        align-items: center;
        padding: 6px 12px;
        border-radius: 999px;
        font-size: 11px;
        font-weight: 800;
        text-transform: uppercase;
    }
    .status-approved {
        background: #dcfce7;
        color: #166534;
    }
    .status-pending {
        background: #fef3c7;
        color: #92400e;
    }
    .status-rejected {
        background: #fee2e2;
        color: #991b1b;
    }
    .condition-yes {
        background: #fef2f2;
        color: #991b1b;
    }
    .condition-no {
        background: #eff6ff;
        color: #1d4ed8;
    }
    .condition-tooltip-wrap {
        position: relative;
        display: inline-flex;
        align-items: center;
    }
    .condition-tooltip-wrap .condition-yes {
        cursor: help;
        box-shadow: 0 8px 18px rgba(153, 27, 27, 0.08);
    }
    .condition-tooltip-bubble {
        position: absolute;
        right: 0;
        bottom: calc(100% + 10px);
        z-index: 30;
        width: min(320px, 78vw);
        padding: 14px 15px;
        border: 1px solid rgba(112, 19, 27, 0.16);
        border-radius: 14px;
        background: #ffffff;
        color: #111827;
        box-shadow: 0 20px 40px rgba(15, 23, 42, 0.18);
        opacity: 0;
        visibility: hidden;
        transform: translateY(6px);
        pointer-events: none;
        transition: opacity .18s ease, visibility .18s ease, transform .18s ease;
    }
    .condition-tooltip-bubble::after {
        content: '';
        position: absolute;
        right: 22px;
        bottom: -7px;
        width: 12px;
        height: 12px;
        background: #ffffff;
        border-right: 1px solid rgba(112, 19, 27, 0.16);
        border-bottom: 1px solid rgba(112, 19, 27, 0.16);
        transform: rotate(45deg);
    }
    .condition-tooltip-wrap:hover .condition-tooltip-bubble,
    .condition-tooltip-wrap:focus-within .condition-tooltip-bubble {
        opacity: 1;
        visibility: visible;
        transform: translateY(0);
    }
    .condition-tooltip-title {
        margin: 0 0 8px;
        color: #70131B;
        font-size: 12px;
        font-weight: 900;
        text-transform: uppercase;
        letter-spacing: .04em;
    }
    .condition-tooltip-list {
        display: grid;
        gap: 7px;
        margin: 0;
        padding: 0;
        list-style: none;
    }
    .condition-tooltip-list li {
        color: #334155;
        font-size: 12px;
        font-weight: 700;
        line-height: 1.45;
    }
    .condition-tooltip-list strong {
        color: #111827;
        font-weight: 900;
    }
    .logbook-empty {
        padding: 44px 24px;
        text-align: center;
        color: #64748b;
        font-weight: 700;
    }
    .logbook-pagination {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 16px;
        margin-top: 20px;
        padding: 14px 18px;
        border: 1px solid #edf2f7;
        border-radius: 12px;
        background: #ffffff;
        box-shadow: 0 10px 24px rgba(15, 23, 42, 0.04);
    }
    .logbook-pagination-pages {
        display: flex;
        align-items: center;
        justify-content: center;
        gap: 10px;
        flex-wrap: wrap;
        margin: 0;
        padding: 0;
        list-style: none;
    }
    .logbook-page-link,
    .logbook-page-ellipsis {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        min-width: 38px;
        height: 38px;
        padding: 0 14px;
        border-radius: 8px;
        border: 1px solid #e2e8f0;
        background: #ffffff;
        color: #334155;
        font-size: 12px;
        font-weight: 900;
        line-height: 1;
        text-decoration: none;
        box-shadow: none;
        transition: transform .18s ease, border-color .18s ease, background-color .18s ease, color .18s ease, box-shadow .18s ease;
    }
    .logbook-page-link:hover {
        background: #fff7ed;
        border-color: #f8cfd4;
        color: #70131B;
        transform: translateY(-1px);
        box-shadow: 0 10px 20px rgba(112, 19, 27, .10);
    }
    .logbook-page-link.is-active {
        background: #7f0010;
        border-color: #7f0010;
        color: #ffffff !important;
        box-shadow: 0 12px 24px rgba(127, 29, 45, 0.22);
    }
    .logbook-page-link.is-disabled,
    .logbook-page-ellipsis {
        background: #f8fafc;
        color: #94a3b8;
        border-color: #e2e8f0;
        box-shadow: none;
        cursor: not-allowed;
    }
    .logbook-page-link.is-disabled {
        pointer-events: none;
    }
    .logbook-pagination-meta {
        color: #64748b;
        font-size: 12px;
        font-weight: 900;
        white-space: nowrap;
    }
    .logbook-pagination-side {
        display: flex;
        align-items: center;
        gap: 12px;
        flex-wrap: wrap;
        justify-content: flex-end;
    }
    .logbook-per-page-form {
        margin: 0;
    }
    .logbook-per-page-select {
        min-height: 38px;
        border-radius: 8px;
        border: 1px solid #e2e8f0;
        background: #ffffff;
        color: #334155;
        padding: 0 34px 0 12px;
        font-size: 12px;
        font-weight: 900;
        cursor: pointer;
        appearance: none;
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
    .logbook-per-page-select:hover,
    .logbook-per-page-select:focus {
        outline: none;
        border-color: rgba(112, 19, 27, .34);
        box-shadow: 0 10px 20px rgba(112, 19, 27, .10);
        transform: translateY(-2px);
    }
    .premium-select-native { position: absolute !important; width: 1px !important; height: 1px !important; opacity: 0 !important; pointer-events: none !important; }
    .premium-select-shell { position: relative; display: inline-flex; min-width: 132px; z-index: 30; }
    .premium-select-shell.is-open { z-index: 120; }
    .filter-group .premium-select-shell { width: 100%; display: flex; }
    .filter-group .premium-select-button { min-height: 46px; border-radius: 16px; padding: 12px 50px 12px 16px; font-size: 14px; }
    .premium-select-button { width: 100%; min-height: 46px; display: inline-flex; align-items: center; justify-content: flex-start; gap: 10px; padding: 12px 50px 12px 16px; border-radius: 16px; border: 1px solid rgba(127, 29, 29, 0.22); background: radial-gradient(circle at top right, rgba(250, 204, 21, 0.10), transparent 36%), linear-gradient(180deg, #ffffff 0%, #fff8f6 100%); color: #111827; font-size: 13px; font-weight: 800; text-align: left; cursor: pointer; box-shadow: 0 12px 22px rgba(15, 23, 42, 0.08), inset 0 1px 0 rgba(255,255,255,0.86); transition: all .2s ease; }
    .premium-select-button:hover { border-color: rgba(139, 0, 0, 0.34); box-shadow: 0 14px 24px rgba(15, 23, 42, 0.10), 0 8px 18px rgba(139, 0, 0, 0.05), inset 0 1px 0 rgba(255,255,255,0.90); transform: translateY(-1px); }
    .premium-select-button:focus,
    .premium-select-shell.is-open .premium-select-button { border-color: #8B0000; box-shadow: 0 0 0 4px rgba(139, 0, 0, 0.06), 0 14px 24px rgba(139, 0, 0, 0.10), inset 0 1px 0 rgba(255,255,255,0.88); outline: none; }
    .premium-select-shell::after { content: ""; position: absolute; top: 23px; right: 18px; width: 10px; height: 10px; border-right: 2px solid #8B0000; border-bottom: 2px solid #8B0000; transform: translateY(-65%) rotate(45deg); pointer-events: none; transition: transform .18s ease; }
    .premium-select-shell::before { content: ""; position: absolute; top: 23px; right: 42px; transform: translateY(-50%); width: 1px; height: 24px; background: rgba(148, 163, 184, 0.24); pointer-events: none; }
    .premium-select-shell.is-open::after { transform: translateY(-20%) rotate(225deg); }
    .premium-select-menu { position: absolute; top: calc(100% + 10px); left: 0; right: 0; display: none; flex-direction: column; gap: 10px; padding: 14px; border-radius: 18px; border: 1px solid rgba(139, 0, 0, 0.12); background: rgba(255, 255, 255, 0.98); box-shadow: 0 18px 34px rgba(15, 23, 42, 0.14); z-index: 130; max-height: 260px; overflow-y: auto; scrollbar-width: none; -ms-overflow-style: none; backdrop-filter: blur(8px); -webkit-backdrop-filter: blur(8px); }
    .premium-select-menu::-webkit-scrollbar { display: none; }
    .premium-select-shell.is-open .premium-select-menu { display: flex; }
    .premium-select-option { width: 100%; min-height: 42px; border: 1px solid rgba(148, 163, 184, 0.22); border-radius: 999px; background: linear-gradient(180deg, #ffffff 0%, #f8fafc 100%); color: #1e293b; font-size: 13px; font-weight: 800; text-align: left; padding: 10px 14px; cursor: pointer; transition: all .18s ease; box-shadow: 0 12px 22px rgba(15, 23, 42, 0.08), inset 0 1px 0 rgba(255,255,255,0.82); }
    .premium-select-option:hover,
    .premium-select-option.is-selected { transform: translateY(-1px); border-color: #8B0000; background: linear-gradient(135deg, #8B0000, #70131B); color: #facc15; box-shadow: 0 12px 20px rgba(139, 0, 0, 0.16); }
    html[data-theme="dark"] .logbook-shell {
        color: #f8fafc;
    }
    html[data-theme="dark"] .logbook-title,
    html[data-theme="dark"] .logbook-copy {
        color: #ffffff;
    }
    html[data-theme="dark"] .logbook-search-input,
    html[data-theme="dark"] .logbook-total-card,
    html[data-theme="dark"] .modal-content,
    html[data-theme="dark"] .logbook-table-wrap,
    html[data-theme="dark"] .logbook-pagination,
    html[data-theme="dark"] .logbook-per-page-select,
    html[data-theme="dark"] .premium-select-button,
    html[data-theme="dark"] .premium-select-menu,
    html[data-theme="dark"] .premium-select-option,
    html[data-theme="dark"] .filter-group input,
    html[data-theme="dark"] .filter-group select {
        background: rgba(15, 23, 42, .96) !important;
        border-color: rgba(250, 204, 21, .18) !important;
        color: #ffffff !important;
    }
    html[data-theme="dark"] .logbook-table th {
        background: rgba(17, 24, 39, .92);
        color: #e5e7eb;
    }
    html[data-theme="dark"] .logbook-table td,
    html[data-theme="dark"] .modal-header h2,
    html[data-theme="dark"] .condition-tooltip-list strong {
        color: #ffffff;
    }
    html[data-theme="dark"] .logbook-table th,
    html[data-theme="dark"] .logbook-table td {
        border-bottom-color: rgba(250, 204, 21, .12);
    }
    html[data-theme="dark"] .logbook-table tbody tr[data-logbook-row]:hover,
    html[data-theme="dark"] .logbook-table tbody tr[data-logbook-row]:focus-within {
        background: rgba(250, 204, 21, .08);
        box-shadow: inset 4px 0 0 #facc15;
    }
    html[data-theme="dark"] .logbook-record-link {
        color: #facc15;
    }
    html[data-theme="dark"] .logbook-report-card,
    html[data-theme="dark"] .logbook-report-body,
    html[data-theme="dark"] .logbook-report-field,
    html[data-theme="dark"] .logbook-report-conditions {
        background: #0f172a;
        border-color: rgba(250, 204, 21, .18);
        color: #ffffff;
    }
    html[data-theme="dark"] .logbook-report-field,
    html[data-theme="dark"] .logbook-report-conditions {
        box-shadow: 0 18px 34px rgba(0, 0, 0, .24);
    }
    html[data-theme="dark"] .logbook-report-field span,
    html[data-theme="dark"] .logbook-report-conditions span {
        color: #cbd5e1;
    }
    html[data-theme="dark"] .logbook-report-field strong,
    html[data-theme="dark"] .logbook-report-conditions strong {
        color: #ffffff;
    }
    html[data-theme="dark"] .logbook-report-condition-list li {
        background: rgba(250, 204, 21, .08);
        color: #f8fafc;
    }
    html[data-theme="dark"] .logbook-report-condition-list b {
        color: #facc15;
    }
    html[data-theme="dark"] .filter-group label,
    html[data-theme="dark"] .logbook-pagination-meta,
    html[data-theme="dark"] .logbook-empty,
    html[data-theme="dark"] .condition-tooltip-list li {
        color: #cbd5e1;
    }
    html[data-theme="dark"] .condition-tooltip-bubble {
        background: #111827;
        border-color: rgba(250, 204, 21, .18);
        color: #ffffff;
    }
    html[data-theme="dark"] .condition-tooltip-bubble::after {
        background: #111827;
        border-color: rgba(250, 204, 21, .18);
    }
    html[data-theme="dark"] .logbook-page-link,
    html[data-theme="dark"] .logbook-page-ellipsis {
        background: rgba(17, 24, 39, .94);
        border-color: rgba(250, 204, 21, .18);
        color: #ffffff;
    }
    html[data-theme="dark"] .logbook-page-link.is-active,
    html[data-theme="dark"] .premium-select-option:hover {
        background: #7f0010 !important;
        border-color: #facc15 !important;
        color: #ffffff !important;
    }
    html[data-theme="dark"] .premium-select-shell::before {
        background: rgba(250, 204, 21, .18);
    }
    html[data-theme="dark"] .premium-select-shell::after {
        border-color: #facc15;
    }
    html[data-theme="dark"] .premium-select-button:hover,
    html[data-theme="dark"] .premium-select-button:focus,
    html[data-theme="dark"] .premium-select-shell.is-open .premium-select-button {
        border-color: #facc15 !important;
        box-shadow: 0 0 0 4px rgba(250, 204, 21, .08), 0 14px 24px rgba(0, 0, 0, .30) !important;
    }
    html[data-theme="dark"] .premium-select-option.is-selected {
        background: #7f0010 !important;
        border-color: #facc15 !important;
        color: #facc15 !important;
    }

    /* Shared compact table pagination. */
    .logbook-pagination {
        width: 100%;
        max-width: 100%;
        min-width: 0;
        display: grid;
        grid-template-columns: minmax(0, 1fr) auto minmax(0, 1fr);
        align-items: center;
        gap: 10px;
        margin-top: 10px;
        padding: 8px 10px;
        box-sizing: border-box;
        border-color: rgba(250, 204, 21, .28);
        border-radius: 10px;
        box-shadow: 0 12px 24px rgba(112, 19, 27, .10), 0 3px 10px rgba(15, 23, 42, .05);
    }

    .logbook-pagination-meta {
        justify-self: start;
        font-size: 11px;
        color: #334155;
    }

    .logbook-pagination-pages {
        justify-self: center;
        gap: 6px;
    }

    .logbook-page-link {
        position: relative;
        isolation: isolate;
        overflow: hidden;
        width: 34px;
        min-width: 34px;
        height: 34px;
        min-height: 34px;
        padding: 0 8px;
        border-color: #ead8dc;
        border-radius: 7px;
        color: #70131B;
        font-size: 11px;
    }

    .logbook-page-link::before,
    .logbook-pagination .premium-select-button::before,
    .logbook-pagination .premium-select-option::before {
        content: "";
        position: absolute;
        top: -45%;
        left: -135%;
        width: 72%;
        height: 190%;
        transform: skewX(-20deg);
        background: rgba(255, 247, 178, .72);
        transition: left .72s ease;
        pointer-events: none;
        z-index: 0;
    }

    .logbook-page-link:hover:not(.is-disabled)::before,
    .logbook-page-link:focus-visible:not(.is-disabled)::before,
    .logbook-pagination .premium-select-button:hover::before,
    .logbook-pagination .premium-select-button:focus-visible::before,
    .logbook-pagination .premium-select-option:hover::before,
    .logbook-pagination .premium-select-option:focus-visible::before {
        left: 135%;
    }

    .logbook-page-link:hover:not(.is-disabled),
    .logbook-page-link:focus-visible:not(.is-disabled),
    .logbook-pagination .premium-select-button:hover,
    .logbook-pagination .premium-select-button:focus-visible,
    .logbook-pagination .premium-select-option:hover,
    .logbook-pagination .premium-select-option:focus-visible {
        border-color: #facc15 !important;
        background: #facc15 !important;
        color: #70131B !important;
        transform: translateY(-1px);
        outline: none;
        box-shadow: 0 10px 20px rgba(250, 204, 21, .22);
    }

    .logbook-page-link.is-active,
    .logbook-pagination .premium-select-option.is-selected,
    html[data-theme="dark"] .logbook-page-link.is-active,
    html[data-theme="dark"] .logbook-pagination .premium-select-option.is-selected {
        border-color: #8f0015 !important;
        background: #8f0015 !important;
        color: #ffffff !important;
        -webkit-text-fill-color: #ffffff !important;
    }

    .logbook-pagination .premium-select-option.is-selected:hover,
    .logbook-pagination .premium-select-option.is-selected:focus-visible,
    html[data-theme="dark"] .logbook-pagination .premium-select-option.is-selected:hover,
    html[data-theme="dark"] .logbook-pagination .premium-select-option.is-selected:focus-visible {
        border-color: #facc15 !important;
        background: #facc15 !important;
        color: #70131B !important;
        -webkit-text-fill-color: #70131B !important;
    }

    .logbook-page-link.is-disabled {
        opacity: .45;
    }

    .logbook-per-page-form {
        justify-self: end;
        width: 132px;
    }

    .logbook-per-page-form .premium-select-shell,
    .logbook-per-page-form .premium-select-button {
        width: 132px;
    }

    .logbook-per-page-form .premium-select-button {
        position: relative;
        isolation: isolate;
        overflow: hidden;
        min-height: 36px;
        height: 36px;
        border-radius: 10px;
        font-size: 11px;
        text-align: left;
    }

    .logbook-per-page-form .premium-select-menu {
        top: auto;
        right: 0;
        bottom: calc(100% + 8px);
        left: auto;
        width: 170px;
    }

    .logbook-per-page-form .premium-select-option {
        position: relative;
        isolation: isolate;
        overflow: hidden;
        min-height: 38px;
        border-radius: 8px;
        font-size: 12px;
    }

    html[data-theme="dark"] .logbook-pagination {
        border-color: rgba(250, 204, 21, .18);
        background: #111827 !important;
        box-shadow: 0 16px 32px rgba(0, 0, 0, .30);
    }

    html[data-theme="dark"] .logbook-pagination-meta {
        color: #f8fafc;
    }

    @media (max-width: 720px) {
        .logbook-pagination {
            grid-template-columns: 1fr;
            justify-items: center;
            gap: 8px;
            padding: 8px;
        }

        .logbook-pagination-meta,
        .logbook-per-page-form {
            justify-self: center;
        }
    }

    @media (max-width: 720px) {
        .logbook-pagination {
            align-items: center;
        }
        .logbook-pagination-side {
            justify-content: flex-start;
        }
    }
    @media (max-width: 720px) {
        .logbook-head,
        .logbook-toolbar {
            flex-direction: column;
            align-items: stretch;
        }
        .logbook-toolbar-actions,
        .logbook-back,
        .filter-btn-open,
        .logbook-total-card,
        .logbook-export-btn {
            width: 100%;
        }
    }
</style>
@endpush

@section('content')
@php
    $role = \App\Models\User::normalizeRole(optional(auth()->user())->user_role ?? '');
    $reportsUrl = $role === \App\Models\User::ROLE_ADMIN ? url('/assistant/reports/health-forms') : url('/admin/reports/health-forms');
    $logbookRouteName = request()->routeIs('assistant.*') ? 'assistant.reports.health-forms.applicants-list' : 'reports.health-forms.applicants-list';
@endphp

<div class="logbook-shell">
    <div class="logbook-head">
        <div>
            <h1 class="logbook-title">Health Forms</h1>
            <p class="logbook-copy">List of applicants, students, and staff who submitted health forms.</p>
        </div>
        <div style="display: flex; gap: 10px;">
            <button type="button" class="filter-btn-open" onclick="openFilterModal()">
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" aria-hidden="true">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 3c2.755 0 5.455.232 8.083.678.533.09.917.556.917 1.096v1.044a2.25 2.25 0 0 1-.659 1.591l-5.432 5.432a2.25 2.25 0 0 0-.659 1.591v2.927a2.25 2.25 0 0 1-1.244 2.013L9.75 21v-6.568a2.25 2.25 0 0 0-.659-1.591L3.659 7.409A2.25 2.25 0 0 1 3 5.818V4.774c0-.54.384-1.006.917-1.096A48.32 48.32 0 0 1 12 3Z" />
                </svg>
                <span>Filter</span>
            </button>
            <a href="{{ $reportsUrl }}" class="logbook-back">&larr; Back</a>
        </div>
    </div>

    <div class="logbook-toolbar">
        <div class="logbook-search-wrap">
            <input type="search" id="searchInput" class="logbook-search-input" placeholder="Search by applicant or student name..." value="{{ $search }}" autocomplete="off" enterkeyhint="search">
        </div>
        <div class="logbook-toolbar-actions">
            <div class="logbook-total-card">Total:<span>{{ number_format($logbookRecords->total()) }}</span></div>
        </div>
    </div>

    <div class="modal-overlay" id="filterModal" onclick="closeFilterModal(event)">
        <div class="modal-content" onclick="event.stopPropagation()">
            <button type="button" class="modal-close" onclick="closeFilterModal()" aria-label="Close filter">&times;</button>
            <div class="modal-header">
                <span class="modal-title-icon" aria-hidden="true">
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 3c2.755 0 5.455.232 8.083.678.533.09.917.556.917 1.096v1.044a2.25 2.25 0 0 1-.659 1.591l-5.432 5.432a2.25 2.25 0 0 0-.659 1.591v2.927a2.25 2.25 0 0 1-1.244 2.013L9.75 21v-6.568a2.25 2.25 0 0 0-.659-1.591L3.659 7.409A2.25 2.25 0 0 1 3 5.818V4.774c0-.54.384-1.006.917-1.096A48.32 48.32 0 0 1 12 3Z" />
                    </svg>
                </span>
                <div>
                    <h2>Filter Records</h2>
                    <p>Choose which health form records to review.</p>
                </div>
            </div>
            <form method="GET" class="filters-form" id="filterForm">
                <div class="filters-grid">
                    <div class="filter-group">
                        <label>Course</label>
                        <select name="course">
                            <option value="">All Courses</option>
                            @foreach($courses as $course)
                                <option value="{{ $course['name'] }}" data-premium-label="{{ $course['code'] }}" {{ $courseFilter === $course['name'] ? 'selected' : '' }}>
                                    {{ $course['name'] }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="filter-group">
                        <label>User Type</label>
                        <select name="type">
                            <option value="">All Types</option>
                            <option value="Applicant" {{ $userTypeFilter === 'Applicant' ? 'selected' : '' }}>Applicant</option>
                            <option value="Student" {{ $userTypeFilter === 'Student' ? 'selected' : '' }}>Student</option>
                            <option value="Faculty" {{ $userTypeFilter === 'Faculty' ? 'selected' : '' }}>Faculty</option>
                            <option value="Admin" {{ $userTypeFilter === 'Admin' ? 'selected' : '' }}>Admin</option>
                        </select>
                    </div>
                    <div class="filter-group">
                        <label>Gender</label>
                        <select name="gender">
                            <option value="">All Genders</option>
                            <option value="Male" {{ $genderFilter === 'Male' ? 'selected' : '' }}>Male</option>
                            <option value="Female" {{ $genderFilter === 'Female' ? 'selected' : '' }}>Female</option>
                            <option value="Non-binary" {{ $genderFilter === 'Non-binary' ? 'selected' : '' }}>Non-binary</option>
                        </select>
                    </div>
                    <div class="filter-group">
                        <label>Condition</label>
                        <select name="condition">
                            <option value="">All Records</option>
                            <option value="yes" {{ $conditionFilter === 'yes' ? 'selected' : '' }}>With Condition</option>
                            <option value="no" {{ $conditionFilter === 'no' ? 'selected' : '' }}>No Condition</option>
                        </select>
                    </div>
                    <div class="filter-group">
                        <label>Status</label>
                        <select name="status">
                            <option value="">All Status</option>
                            <option value="approved" {{ $statusFilter === 'approved' ? 'selected' : '' }}>Approved</option>
                            <option value="pending" {{ $statusFilter === 'pending' ? 'selected' : '' }}>Pending</option>
                            <option value="rejected" {{ $statusFilter === 'rejected' ? 'selected' : '' }}>Rejected</option>
                        </select>
                    </div>
                </div>
                <div class="filter-actions">
                    <a href="{{ route($logbookRouteName) }}" class="filter-btn secondary">Reset</a>
                    <button type="submit" class="filter-btn primary">Apply Filters</button>
                </div>
            </form>
        </div>
    </div>

    <div class="logbook-table-wrap">
        <table class="logbook-table">
            <thead>
                <tr>
                    <th>Name</th>
                    <th>Gender</th>
                    <th>Course</th>
                    <th>Type</th>
                    <th>Submitted</th>
                    <th>Reviewed By</th>
                    <th>Approved By</th>
                    <th>Status</th>
                    <th>Condition</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse($logbookRecords as $record)
                    @php
                        $user = $record->user;
                        $approver = $record->approvedBy;
                        $reviewer = $record->reviewStartedBy;
                        $isApproved = in_array($record->clearance_status, ['Issued', 'Fully Cleared'], true);
                        $isRejected = $record->clearance_status === 'Rejected';
                        $statusLabel = $isApproved ? 'Approved' : ($isRejected ? 'Rejected' : 'Pending');
                        $hasCondition = $record->hasMedicalCondition();
                        $conditionDetails = collect();
                        $formatList = static function ($value): string {
                            if (is_array($value)) {
                                return collect($value)
                                    ->filter(fn ($item) => trim((string) $item) !== '')
                                    ->implode(', ');
                            }

                            return trim((string) $value);
                        };

                        if ($record->has_disability === 'Yes') {
                            $conditionDetails->push([
                                'label' => 'Disability',
                                'value' => trim((string) $record->disability_type) !== '' ? $record->disability_type : 'Yes',
                            ]);
                        }

                        if ($record->has_illness === 'Yes' || $formatList($record->medical_history) !== '') {
                            $conditionDetails->push([
                                'label' => 'Medical History',
                                'value' => $formatList($record->medical_history) !== '' ? $formatList($record->medical_history) : 'Yes',
                            ]);
                        }

                        foreach ([
                            'Other Illness' => $record->other_illness,
                            'Food Allergies' => $record->food_allergies,
                            'Medicine Allergies' => $record->medicine_allergies,
                            'Other Medicine Allergies' => $record->other_med_allergies,
                            'Nurse Remarks' => $record->medical_condition_remarks,
                        ] as $label => $value) {
                            $formattedValue = $formatList($value);
                            if ($formattedValue !== '' && $formattedValue !== '[]') {
                                $conditionDetails->push([
                                    'label' => $label,
                                    'value' => $formattedValue,
                                ]);
                            }
                        }
                        $patientName = $record->formatted_patient_name ?? $user->name ?? 'N/A';
                        $submittedAt = $record->created_at ? \Carbon\Carbon::parse($record->created_at)->format('M d, Y g:i A') : 'N/A';
                        $reviewedAt = $record->review_started_at ? \Carbon\Carbon::parse($record->review_started_at)->format('M d, Y g:i A') : 'N/A';
                        $approvedAt = $isApproved && $record->verified_at ? \Carbon\Carbon::parse($record->verified_at)->format('M d, Y g:i A') : 'N/A';
                        $conditionPayload = $conditionDetails
                            ->map(fn ($detail) => $detail['label'] . '::' . $detail['value'])
                            ->implode('||');
                    @endphp
                    <tr
                        data-logbook-row
                        data-report-name="{{ e($patientName) }}"
                        data-report-email="{{ e($user->email ?? 'N/A') }}"
                        data-report-gender="{{ e($record->sex ?: ($user->gender ?? 'N/A')) }}"
                        data-report-course="{{ e($record->course_college ?? $user->course ?? 'N/A') }}"
                        data-report-type="{{ e($user->user_type ?? 'N/A') }}"
                        data-report-submitted="{{ e($submittedAt) }}"
                        data-report-reviewed-by="{{ e($reviewer?->name ?? 'N/A') }}"
                        data-report-reviewed-at="{{ e($reviewedAt) }}"
                        data-report-approved-by="{{ e(($isApproved && $approver) ? $approver->name : ($isApproved ? 'Not recorded' : 'N/A')) }}"
                        data-report-approved-at="{{ e($approvedAt) }}"
                        data-report-status="{{ e($statusLabel) }}"
                        data-report-condition="{{ e($hasCondition ? 'Yes' : 'No') }}"
                        data-report-conditions="{{ e($conditionPayload) }}"
                        data-search="{{ strtolower($patientName . ' ' . ($user->name ?? '') . ' ' . ($user->email ?? '') . ' ' . ($record->sex ?: ($user->gender ?? 'N/A')) . ' ' . ($record->course_college ?? $user->course ?? 'N/A') . ' ' . ($user->user_type ?? 'N/A') . ' ' . $statusLabel . ' ' . ($hasCondition ? 'yes with condition medical condition' : 'no condition')) }}"
                        tabindex="0"
                        aria-label="View report details for {{ $patientName }}"
                    >
                        <td>
                            <button type="button" class="logbook-record-link" data-open-report-details>{{ $patientName }}</button>
                        </td>
                        <td>{{ $record->sex ?: ($user->gender ?? 'N/A') }}</td>
                        <td>{{ $record->course_college ?? $user->course ?? 'N/A' }}</td>
                        <td>{{ $user->user_type ?? 'N/A' }}</td>
                        <td>{{ $submittedAt }}</td>
                        <td>
                            @if($reviewer)
                                <strong>{{ $reviewer->name }}</strong>
                            @else
                                <span style="color: #94a3b8;">&mdash;</span>
                            @endif
                        </td>
                        <td>
                            @if($isApproved && $approver)
                                <strong>{{ $approver->name }}</strong>
                            @elseif($isApproved)
                                <span style="color: #64748b;">Not recorded</span>
                            @else
                                <span style="color: #94a3b8;">&mdash;</span>
                            @endif
                        </td>
                        <td>
                            <span class="status-badge {{ $isApproved ? 'status-approved' : ($isRejected ? 'status-rejected' : 'status-pending') }}">
                                {{ $statusLabel }}
                            </span>
                        </td>
                        <td>
                            @if($hasCondition)
                                <span class="condition-tooltip-wrap">
                                    <span class="status-badge condition-yes" tabindex="0">Yes</span>
                                    <span class="condition-tooltip-bubble" role="tooltip">
                                        <span class="condition-tooltip-title">Medical Condition Details</span>
                                        <ul class="condition-tooltip-list">
                                            @forelse($conditionDetails as $detail)
                                                <li><strong>{{ $detail['label'] }}:</strong> {{ $detail['value'] }}</li>
                                            @empty
                                                <li>Medical condition was flagged, but no specific details were provided.</li>
                                            @endforelse
                                        </ul>
                                    </span>
                                </span>
                            @else
                                <span class="status-badge condition-no">No</span>
                            @endif
                        </td>
                        <td>
                            <button type="button" class="logbook-view-link" data-open-report-details>View</button>
                        </td>
                    </tr>
                @empty
                    <tr data-logbook-empty-row>
                        <td colspan="10" class="logbook-empty">No health form records found matching your filters.</td>
                    </tr>
                @endforelse
                @if($logbookRecords->count() > 0)
                    <tr data-logbook-empty-row style="display:none;">
                        <td colspan="10" class="logbook-empty">No health form records found matching your search.</td>
                    </tr>
                @endif
            </tbody>
        </table>
    </div>

    @if($logbookRecords->total() > 0)
        @php
            $currentPage = $logbookRecords->currentPage();
            $lastPage = $logbookRecords->lastPage();
            $pageStart = max(1, min($currentPage - 2, $lastPage - 4));
            $pageEnd = min($lastPage, $pageStart + 4);
            $visiblePages = range($pageStart, $pageEnd);
        @endphp
        <div class="logbook-pagination">
            <span class="logbook-pagination-meta">
                Showing {{ $logbookRecords->firstItem() }} to {{ $logbookRecords->lastItem() }} of {{ $logbookRecords->total() }} records
            </span>
            <nav class="logbook-pagination-pages" aria-label="Applicants list pagination">
                @if($logbookRecords->onFirstPage())
                    <span class="logbook-page-link is-disabled" aria-disabled="true">&larr;</span>
                @else
                    <a class="logbook-page-link" href="{{ $logbookRecords->previousPageUrl() }}" rel="prev">&larr;</a>
                @endif

                @foreach($visiblePages as $page)
                    @if($page === $currentPage)
                        <span class="logbook-page-link is-active" aria-current="page">{{ $page }}</span>
                    @else
                        <a class="logbook-page-link" href="{{ $logbookRecords->url($page) }}">{{ $page }}</a>
                    @endif
                @endforeach

                @if($logbookRecords->hasMorePages())
                    <a class="logbook-page-link" href="{{ $logbookRecords->nextPageUrl() }}" rel="next">&rarr;</a>
                @else
                    <span class="logbook-page-link is-disabled" aria-disabled="true">&rarr;</span>
                @endif
            </nav>
            <form method="GET" class="logbook-per-page-form">
                @foreach(request()->except(['page', 'per_page']) as $queryKey => $queryValue)
                    @if(is_array($queryValue))
                        @foreach($queryValue as $nestedValue)
                            <input type="hidden" name="{{ $queryKey }}[]" value="{{ $nestedValue }}">
                        @endforeach
                    @else
                        <input type="hidden" name="{{ $queryKey }}" value="{{ $queryValue }}">
                    @endif
                @endforeach
                <select name="per_page" class="logbook-per-page-select" onchange="this.form.submit()" aria-label="Applicants list records per page">
                    @foreach(['20' => '20 per page', '40' => '40 per page', '80' => '80 per page', '100' => '100 per page', 'all' => 'Show all'] as $optionValue => $optionLabel)
                        <option value="{{ $optionValue }}" @selected(($perPage ?? '20') === $optionValue)>{{ $optionLabel }}</option>
                    @endforeach
                </select>
            </form>
        </div>
    @endif
</div>

<div class="logbook-report-modal" id="logbookReportModal" aria-hidden="true">
    <div class="logbook-report-card" role="dialog" aria-modal="true" aria-labelledby="logbookReportTitle">
        <header class="logbook-report-head">
            <div class="logbook-report-head-main">
                <span class="logbook-report-icon" aria-hidden="true"><x-outline-icon name="clipboard-document-list" /></span>
                <div>
                    <h2 class="logbook-report-title" id="logbookReportTitle">Health Form Report Details</h2>
                    <p class="logbook-report-copy">Read-only report information from the Health Forms applicants list.</p>
                </div>
            </div>
            <button type="button" class="logbook-report-close" id="logbookReportClose" aria-label="Close report details">
                <x-outline-icon name="x-mark" />
            </button>
        </header>
        <div class="logbook-report-body">
            <div class="logbook-report-grid">
                <div class="logbook-report-field">
                    <span>Patient Name</span>
                    <strong id="reportDetailName">N/A</strong>
                </div>
                <div class="logbook-report-field">
                    <span>Email</span>
                    <strong id="reportDetailEmail">N/A</strong>
                </div>
                <div class="logbook-report-field">
                    <span>Gender</span>
                    <strong id="reportDetailGender">N/A</strong>
                </div>
                <div class="logbook-report-field">
                    <span>Course</span>
                    <strong id="reportDetailCourse">N/A</strong>
                </div>
                <div class="logbook-report-field">
                    <span>User Type</span>
                    <strong id="reportDetailType">N/A</strong>
                </div>
                <div class="logbook-report-field">
                    <span>Status</span>
                    <strong id="reportDetailStatus">N/A</strong>
                </div>
                <div class="logbook-report-field">
                    <span>Submitted At</span>
                    <strong id="reportDetailSubmitted">N/A</strong>
                </div>
                <div class="logbook-report-field">
                    <span>Reviewed By</span>
                    <strong id="reportDetailReviewedBy">N/A</strong>
                </div>
                <div class="logbook-report-field">
                    <span>Reviewed At</span>
                    <strong id="reportDetailReviewedAt">N/A</strong>
                </div>
                <div class="logbook-report-field">
                    <span>Approved By</span>
                    <strong id="reportDetailApprovedBy">N/A</strong>
                </div>
                <div class="logbook-report-field">
                    <span>Approved At</span>
                    <strong id="reportDetailApprovedAt">N/A</strong>
                </div>
                <div class="logbook-report-field">
                    <span>Medical Condition</span>
                    <strong id="reportDetailCondition">N/A</strong>
                </div>
                <div class="logbook-report-conditions">
                    <span>Condition Details</span>
                    <strong id="reportDetailConditionSummary">No condition details recorded.</strong>
                    <ul class="logbook-report-condition-list" id="reportDetailConditionList"></ul>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
function openFilterModal() {
    document.getElementById('filterModal').classList.add('active');
}

function closeFilterModal(event) {
    if (event && event.target.id !== 'filterModal') return;
    document.getElementById('filterModal').classList.remove('active');
}

const logbookSearchInput = document.getElementById('searchInput');
const logbookRows = Array.from(document.querySelectorAll('[data-logbook-row]'));
const logbookEmptyRow = document.querySelector('[data-logbook-empty-row]');
const logbookTotalCardValue = document.querySelector('.logbook-total-card span');
const logbookReportModal = document.getElementById('logbookReportModal');
const logbookReportClose = document.getElementById('logbookReportClose');

function setReportText(id, value) {
    const target = document.getElementById(id);
    if (target) target.textContent = value && value.trim() ? value : 'N/A';
}

function closeLogbookReportModal() {
    logbookReportModal?.classList.remove('is-open');
    logbookReportModal?.setAttribute('aria-hidden', 'true');
}

function openLogbookReportModal(row) {
    if (!row || !logbookReportModal) return;

    setReportText('reportDetailName', row.dataset.reportName);
    setReportText('reportDetailEmail', row.dataset.reportEmail);
    setReportText('reportDetailGender', row.dataset.reportGender);
    setReportText('reportDetailCourse', row.dataset.reportCourse);
    setReportText('reportDetailType', row.dataset.reportType);
    setReportText('reportDetailStatus', row.dataset.reportStatus);
    setReportText('reportDetailSubmitted', row.dataset.reportSubmitted);
    setReportText('reportDetailReviewedBy', row.dataset.reportReviewedBy);
    setReportText('reportDetailReviewedAt', row.dataset.reportReviewedAt);
    setReportText('reportDetailApprovedBy', row.dataset.reportApprovedBy);
    setReportText('reportDetailApprovedAt', row.dataset.reportApprovedAt);
    setReportText('reportDetailCondition', row.dataset.reportCondition);

    const conditionList = document.getElementById('reportDetailConditionList');
    const conditionSummary = document.getElementById('reportDetailConditionSummary');
    const details = (row.dataset.reportConditions || '')
        .split('||')
        .map((item) => item.trim())
        .filter(Boolean)
        .map((item) => {
            const parts = item.split('::');
            return {
                label: (parts.shift() || '').trim(),
                value: parts.join('::').trim()
            };
        })
        .filter((item) => item.label || item.value);

    if (conditionList) {
        conditionList.innerHTML = '';
        details.forEach((detail) => {
            const item = document.createElement('li');
            const label = document.createElement('b');
            label.textContent = detail.label ? detail.label + ': ' : '';
            item.appendChild(label);
            item.append(document.createTextNode(detail.value || 'N/A'));
            conditionList.appendChild(item);
        });
    }

    if (conditionSummary) {
        conditionSummary.textContent = details.length
            ? `${details.length} condition detail${details.length === 1 ? '' : 's'} recorded.`
            : 'No condition details recorded.';
    }

    logbookReportModal.classList.add('is-open');
    logbookReportModal.setAttribute('aria-hidden', 'false');
    logbookReportClose?.focus();
}

logbookRows.forEach(function (row) {
    row.querySelectorAll('[data-open-report-details]').forEach(function (trigger) {
        trigger.addEventListener('click', function (event) {
            event.preventDefault();
            event.stopPropagation();
            openLogbookReportModal(row);
        });
    });

    row.addEventListener('click', function (event) {
        if (event.target.closest('a, button, input, select, textarea, [tabindex]:not([data-logbook-row])')) {
            return;
        }

        openLogbookReportModal(row);
    });

    row.addEventListener('keydown', function (event) {
        if (!['Enter', ' '].includes(event.key)) {
            return;
        }

        if (event.target.closest('a, button, input, select, textarea, [tabindex]:not([data-logbook-row])')) {
            return;
        }

        event.preventDefault();
        openLogbookReportModal(row);
    });
});

logbookReportClose?.addEventListener('click', closeLogbookReportModal);
logbookReportModal?.addEventListener('click', function (event) {
    if (event.target === logbookReportModal) {
        closeLogbookReportModal();
    }
});

function filterVisibleLogbookRows() {
    const query = (logbookSearchInput?.value || '').trim().toLowerCase();
    let visibleCount = 0;

    logbookRows.forEach(function (row) {
        const haystack = row.getAttribute('data-search') || row.textContent.toLowerCase();
        const isVisible = !query || haystack.includes(query);
        row.style.display = isVisible ? '' : 'none';
        if (isVisible) visibleCount += 1;
    });

    if (logbookEmptyRow) {
        logbookEmptyRow.style.display = visibleCount === 0 ? '' : 'none';
    }

    if (logbookTotalCardValue) {
        logbookTotalCardValue.textContent = visibleCount.toString();
    }
}

logbookSearchInput?.addEventListener('input', function(event) {
    event.stopPropagation();
    filterVisibleLogbookRows();
});

logbookSearchInput?.addEventListener('keydown', function (event) {
    event.stopPropagation();
    if (event.key === 'Enter') {
        event.preventDefault();
        filterVisibleLogbookRows();
    }
});

document.getElementById('filterForm').addEventListener('submit', function(e) {
    e.preventDefault();
    const searchValue = document.getElementById('searchInput').value;

    let params = new URLSearchParams();
    params.append('q', searchValue);
    params.append('course', this.querySelector('[name="course"]').value);
    params.append('type', this.querySelector('[name="type"]').value);
    params.append('gender', this.querySelector('[name="gender"]').value);
    params.append('condition', this.querySelector('[name="condition"]').value);
    params.append('status', this.querySelector('[name="status"]').value);

    window.location.href = '{{ route($logbookRouteName) }}?' + params.toString();
});

document.addEventListener('keydown', function(e) {
    if (e.key === 'Escape') {
        closeFilterModal();
        closeLogbookReportModal();
    }
});

(function initPremiumSelects() {
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
            button.textContent = selected ? (selected.dataset.premiumLabel || selected.textContent).trim() : 'Select';
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
                    shell.closest('.filter-group')?.classList.remove('is-select-open');
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
                if (openShell !== shell) openShell.closest('.filter-group')?.classList.remove('is-select-open');
            });
            shell.classList.toggle('is-open');
            shell.closest('.filter-group')?.classList.toggle('is-select-open', shell.classList.contains('is-open'));
        });
        rebuild();
    }
    document.querySelectorAll('.logbook-per-page-select, .filters-form select').forEach(enhance);
    document.addEventListener('click', function() {
        document.querySelectorAll('.premium-select-shell.is-open').forEach(function(shell) {
            shell.classList.remove('is-open');
            shell.closest('.filter-group')?.classList.remove('is-select-open');
        });
    });
})();
</script>

@endsection

