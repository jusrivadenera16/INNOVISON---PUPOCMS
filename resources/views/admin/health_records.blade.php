@extends('layouts.admin')

@section('title', 'Health Records')

@push('styles')
<style>
    /* Table & Card Styling */
    .card {
        background: #fff;
        border-radius: 12px;
        padding: 24px;
        box-shadow: 0 4px 12px rgba(0,0,0,0.05);
        border: 1px solid #f0f0f0;
        height: 100%; /* Para pantay ang taas nila */
    }
    .card.awaiting-links-btn {
        background: linear-gradient(135deg, #7f1d1d 0%, #991b1b 55%, #b91c1c 100%) !important;
        border: 1px solid #991b1b !important;
        color: #ffffff !important;
        cursor: pointer;
        transition: all 0.3s ease;
        box-shadow: 0 4px 12px rgba(112, 19, 27, 0.2) !important;
        position: relative;
        overflow: hidden;
    }
    .card.awaiting-links-btn * {
        background-color: transparent !important;
        color: #ffffff !important;
    }
    .card.awaiting-links-btn .health-summary-label {
        color: #ffffff !important;
    }
    .card.awaiting-links-btn .health-summary-label span {
        color: #ffffff !important;
    }
    .card.awaiting-links-btn h3 {
        color: #ffffff !important;
    }
    .card.awaiting-links-btn svg {
        stroke: #ffffff !important;
        color: #ffffff !important;
    }
    .workflow-card-icon {
        display: flex;
        align-items: center;
        justify-content: center;
        color: #ffffff;
        flex: 0 0 34px;
        width: 34px;
        height: 34px;
    }
    .workflow-card-icon svg {
        width: 30px !important;
        height: 30px !important;
        stroke-width: 2.1;
    }
    .workflow-card-divider {
        width: 2px;
        height: 54px;
        align-self: center;
        background: rgba(255, 255, 255, 0.72);
        border-radius: 999px;
        box-shadow: 0 0 0 1px rgba(255, 255, 255, 0.12);
        flex: 0 0 auto;
        position: relative;
        z-index: 2;
    }
    .workflow-card-text {
        flex: 1;
        min-width: 0;
        padding-right: 10px;
    }
    .workflow-card-text small {
        overflow-wrap: anywhere;
    }
    .workflow-card-label {
        display: block;
        font-size: 9px;
        font-weight: 800;
        line-height: 1.18;
        letter-spacing: 0.8px;
        text-transform: uppercase;
    }
    .workflow-card-count {
        margin: 3px 0 0 0;
        font-size: 22px;
        font-weight: 800;
        line-height: 1;
        color: #ffffff;
    }
    .card.awaiting-links-btn:hover .workflow-card-divider {
        background: rgba(112, 19, 27, 0.72);
        box-shadow: 0 0 0 1px rgba(112, 19, 27, 0.12);
    }
    .awaiting-links-btn::before {
        content: '';
        position: absolute;
        top: 0;
        left: -100%;
        width: 100%;
        height: 100%;
        background: linear-gradient(90deg,
            rgba(255, 248, 196, 0) 0%,
            rgba(255, 239, 181, 0.14) 22%,
            rgba(255, 239, 181, 0.52) 48%,
            rgba(255, 239, 181, 0.14) 72%,
            rgba(255, 248, 196, 0) 100%);
        transform: translateX(-135%);
        transition: transform 1.5s ease;
        z-index: -1;
    }
    .awaiting-links-btn:hover {
        transform: translateY(-1px);
        background: #facc15 !important;
        color: #70131B !important;
        border-color: #facc15 !important;
        box-shadow: 0 14px 24px rgba(112, 19, 27, 0.2);
    }
    .card.awaiting-links-btn:hover * {
        color: #70131B !important;
    }
    .card.awaiting-links-btn:hover h3 {
        color: #70131B !important;
    }
    .card.awaiting-links-btn:hover small {
        color: #70131B !important;
    }
    .card.awaiting-links-btn:hover svg {
        stroke: #70131B !important;
        color: #70131B !important;
    }
    .awaiting-links-btn:hover::before {
        transform: translateX(135%);
    }
    .awaiting-links-btn:active {
        transform: translateY(0);
    }

    /* Read-only workflow modal styling */
    .awaiting-links-modal-shell {
        width: min(980px, 96%);
        max-height: 85vh;
        overflow: hidden;
        border-radius: 18px;
        background: #ffffff;
        box-shadow: 0 26px 60px rgba(15, 23, 42, 0.24);
        border: 1px solid rgba(255,255,255,0.5);
        display: flex;
        flex-direction: column;
    }
    .awaiting-links-modal-head {
        position: relative;
        display: flex;
        align-items: flex-start;
        justify-content: space-between;
        gap: 16px;
        padding: 24px;
        background: linear-gradient(135deg, #7f1d1d, #991b1b 55%, #b91c1c);
        color: #ffffff;
        border-bottom: 1px solid rgba(255,255,255,0.1);
    }
    .awaiting-links-modal-head-main {
        display: flex;
        align-items: flex-start;
        gap: 16px;
        min-width: 0;
    }
    .awaiting-links-modal-badge {
        width: 48px;
        height: 48px;
        flex: 0 0 auto;
        display: flex;
        align-items: center;
        justify-content: center;
        background: rgba(255,255,255,0.2);
        border-radius: 12px;
        font-weight: 700;
        font-size: 16px;
        color: #ffffff;
        border: 1px solid rgba(255,255,255,0.3);
    }
    .awaiting-links-modal-copy h3 {
        margin: 0 0 4px 0;
        font-size: 18px;
        font-weight: 700;
        color: #ffffff !important;
    }
    .awaiting-links-modal-copy p {
        margin: 0;
        font-size: 13px;
        color: #ffffff !important;
    }
    .awaiting-links-modal-close {
        flex: 0 0 auto;
        background: rgba(255,255,255,0.15);
        border: 1px solid rgba(255,255,255,0.2);
        border-radius: 50%;
        width: 44px;
        height: 44px;
        display: flex;
        align-items: center;
        justify-content: center;
        color: #ffffff;
        cursor: pointer;
        transition: all 0.3s ease;
        position: relative;
        overflow: hidden;
    }
    .awaiting-links-modal-close::before {
        content: '';
        position: absolute;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        background: linear-gradient(90deg,
            rgba(255, 248, 196, 0) 0%,
            rgba(255, 239, 181, 0.14) 22%,
            rgba(255, 239, 181, 0.52) 48%,
            rgba(255, 239, 181, 0.14) 72%,
            rgba(255, 248, 196, 0) 100%);
        transform: translateX(-135%);
        transition: transform 1.5s ease;
        border-radius: 50%;
        z-index: 0;
    }
    .awaiting-links-modal-close:hover {
        color: #70131B;
        background: #facc15;
        border-color: #facc15;
    }
    .awaiting-links-modal-close:hover::before {
        transform: translateX(135%);
    }
    .awaiting-links-modal-close svg {
        width: 20px;
        height: 20px;
        stroke-width: 2.5;
        stroke: currentColor;
        fill: none;
        position: relative;
        z-index: 1;
    }
    .awaiting-links-modal-body {
        flex: 1;
        overflow-y: auto;
        padding: 24px;
    }
    .awaiting-links-modal-body table {
        width: 100%;
        border-collapse: collapse;
    }
    .awaiting-links-modal-body thead tr {
        border-bottom: 2px solid #e2e8f0;
    }
    .awaiting-links-modal-body th {
        text-align: left;
        padding: 14px 16px;
        font-size: 12px;
        font-weight: 800;
        color: #475569;
        text-transform: uppercase;
        letter-spacing: 0.5px;
    }
    .awaiting-links-modal-body tbody tr {
        border-bottom: 1px solid #f1f5f9;
        transition: background-color 0.2s ease;
    }
    .awaiting-links-modal-body tbody tr:hover {
        background-color: #f8fafc;
    }
    .awaiting-links-modal-body td {
        padding: 16px;
        color: #334155;
        font-size: 14px;
    }
    .awaiting-info-row {
        display: grid;
        gap: 4px;
        padding: 12px 14px;
        border-radius: 12px;
        border: 1px solid rgba(112, 19, 27, 0.12);
        background: #fffaf7;
    }
    .awaiting-info-row span {
        color: #64748b;
        font-size: 11px;
        font-weight: 800;
        text-transform: uppercase;
        letter-spacing: .08em;
    }
    .awaiting-info-row strong {
        color: #111827;
        font-size: 14px;
        font-weight: 900;
        word-break: break-word;
    }
    .readonly-modal-search {
        position: sticky;
        top: -24px;
        z-index: 4;
        display: flex;
        align-items: center;
        gap: 10px;
        margin: -4px 0 18px;
        padding: 12px;
        border: 1px solid rgba(112, 19, 27, 0.12);
        border-radius: 16px;
        background: rgba(255, 255, 255, 0.96);
        box-shadow: 0 14px 28px rgba(15, 23, 42, 0.08);
    }

    .readonly-modal-search:focus-within {
        border-color: rgba(112, 19, 27, 0.12);
        box-shadow: 0 14px 28px rgba(15, 23, 42, 0.08);
    }
    .readonly-modal-search svg {
        width: 18px;
        height: 18px;
        color: #70131B;
        flex: 0 0 auto;
    }
    .readonly-modal-search input {
        width: 100%;
        border: 0;
        outline: none !important;
        background: transparent;
        color: #111827;
        font-size: 14px;
        font-weight: 800;
        box-shadow: none !important;
        appearance: none;
        -webkit-appearance: none;
    }
    .readonly-modal-search input:focus {
        outline: none !important;
        border-color: transparent !important;
        box-shadow: none !important;
        background: transparent;
    }
    .readonly-modal-search input::placeholder {
        color: #94a3b8;
        font-weight: 700;
    }
    .readonly-search-empty {
        display: none;
        padding: 18px;
        border: 1px dashed rgba(112, 19, 27, 0.24);
        border-radius: 14px;
        background: #fffaf7;
        color: #64748b;
        font-size: 13px;
        font-weight: 800;
        text-align: center;
    }
    .readonly-search-empty.is-visible {
        display: block;
    }
    .pending-approval-list {
        display: grid;
        gap: 12px;
    }
    .pending-approval-card {
        display: grid;
        grid-template-columns: minmax(0, 1fr) auto;
        align-items: center;
        gap: 14px;
        padding: 14px 16px;
        border: 1px solid rgba(112, 19, 27, 0.12);
        border-radius: 14px;
        background: #fffaf7;
        transition: background-color 0.2s ease, border-color 0.2s ease, transform 0.2s ease;
    }
    .pending-approval-card:hover {
        background: #fff7df;
        border-color: rgba(112, 19, 27, 0.24);
        transform: translateY(-1px);
    }
    .pending-approval-name {
        margin: 0;
        color: #111827;
        font-size: 14px;
        font-weight: 900;
    }
    .pending-approval-meta {
        display: flex;
        flex-wrap: wrap;
        gap: 6px 12px;
        margin-top: 5px;
        color: #64748b;
        font-size: 12px;
        font-weight: 700;
    }
    .pending-approval-empty {
        padding: 28px;
        border: 1px dashed rgba(112, 19, 27, 0.18);
        border-radius: 16px;
        color: #64748b;
        text-align: center;
        font-size: 14px;
        font-weight: 700;
    }
    .readonly-modal-pagination {
        display: none;
        align-items: center;
        justify-content: space-between;
        gap: 16px;
        margin-top: 16px;
        padding: 14px 18px;
        border: 1px solid #edf2f7;
        border-radius: 12px;
        background: #ffffff;
        box-shadow: 0 10px 24px rgba(15, 23, 42, 0.04);
    }
    .readonly-modal-pagination.is-visible {
        display: flex;
    }
    .readonly-pagination-summary {
        color: #64748b;
        font-size: 12px;
        font-weight: 900;
    }
    .readonly-pagination-actions {
        display: flex;
        align-items: center;
        gap: 10px;
        justify-content: center;
        flex-wrap: wrap;
    }
    .readonly-pagination-btn {
        min-width: 38px;
        min-height: 38px;
        border: 1px solid #e2e8f0;
        border-radius: 8px;
        background: #fff;
        color: #334155;
        font-size: 12px;
        font-weight: 900;
        cursor: pointer;
        padding: 0 12px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        text-decoration: none;
        transition: background-color 0.2s ease, border-color 0.2s ease, color 0.2s ease, transform 0.2s ease, box-shadow .2s ease;
    }
    .readonly-pagination-btn:hover:not(:disabled) {
        background: #fff7ed;
        border-color: #f8cfd4;
        color: #70131B;
        transform: translateY(-2px) scale(1.04);
        box-shadow: 0 10px 20px rgba(112, 19, 27, .10);
    }
    .readonly-pagination-btn.is-active,
    .readonly-pagination-btn.is-active:hover:not(:disabled) {
        background: #7f0010;
        border-color: #7f0010;
        color: #ffffff !important;
        box-shadow: 0 12px 24px rgba(127, 0, 16, 0.18);
        transform: translateY(-1px);
    }
    .readonly-pagination-btn:disabled {
        cursor: not-allowed;
        opacity: 0.45;
    }
    .readonly-pagination-btn.is-active:disabled {
        opacity: 1;
    }
    .readonly-pagination-per-page {
        min-height: 38px;
        border-radius: 8px;
        border: 1px solid #e2e8f0;
        background: #ffffff;
        color: #334155;
        padding: 0 12px;
        font-size: 12px;
        font-weight: 900;
    }
    .readonly-pagination-per-page-form {
        margin: 0;
    }
    .readonly-pagination-per-page-select {
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
            linear-gradient(135deg, transparent 50%, #70131B 50%),
            linear-gradient(45deg, #70131B 50%, transparent 50%);
        background-position:
            calc(100% - 17px) 50%,
            calc(100% - 11px) 50%;
        background-size: 6px 6px, 6px 6px;
        background-repeat: no-repeat;
        transition: border-color .2s ease, box-shadow .2s ease, transform .2s ease;
    }
    .readonly-pagination-per-page-select:hover,
    .readonly-pagination-per-page-select:focus {
        outline: none;
        border-color: rgba(112, 19, 27, .32);
        box-shadow: 0 10px 20px rgba(112, 19, 27, .10);
        transform: translateY(-1px);
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
        min-width: 132px;
        z-index: 20;
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
        transition: transform .18s ease;
    }
    .premium-select-shell.is-open .premium-select-button::after {
        transform: rotate(225deg) translateY(-2px);
    }
    .premium-select-menu {
        position: absolute;
        left: 0;
        right: 0;
        bottom: calc(100% + 8px);
        top: auto;
        display: none;
        gap: 6px;
        flex-direction: column;
        padding: 8px;
        border-radius: 14px;
        border: 1px solid rgba(112, 19, 27, .16);
        background: #ffffff;
        box-shadow: 0 18px 36px rgba(15, 23, 42, .16);
        z-index: 80;
    }
    .premium-select-shell.is-open .premium-select-menu {
        display: flex;
    }
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
    .premium-select-option:hover {
        background: #7f0010;
        color: #facc15;
        border-color: #7f0010;
    }
    .premium-select-option.is-selected {
        background: #7f0010;
        color: #ffffff;
        border-color: #7f0010;
    }
    .readonly-record-card {
        display: grid;
        gap: 14px;
        padding: 16px;
        border: 1px solid rgba(112, 19, 27, 0.12);
        border-radius: 16px;
        background: #fffaf7;
        transition: transform .18s ease, background .18s ease, border-color .18s ease, box-shadow .18s ease;
    }
    .readonly-record-card:hover,
    .readonly-record-card:focus-within {
        transform: translateY(-2px);
        border-color: rgba(112, 19, 27, 0.3);
        background: linear-gradient(180deg, #fff7ed 0%, #fff1f2 100%);
        box-shadow: 0 14px 26px rgba(112, 19, 27, 0.12);
    }
    .readonly-record-head {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 14px;
        padding-bottom: 12px;
        border-bottom: 1px solid rgba(112, 19, 27, 0.1);
    }
    .readonly-record-meta {
        display: flex;
        align-items: center;
        justify-content: flex-end;
        gap: 10px;
        flex-wrap: nowrap;
        text-align: right;
        flex: 0 0 auto;
        max-width: none;
    }
    .readonly-record-actions {
        display: flex;
        align-items: stretch;
        width: 126px;
        flex: 0 0 126px;
    }
    .readonly-record-pill {
        display: flex;
        flex-direction: column;
        align-items: center;
        justify-content: center;
        gap: 4px;
        min-width: 142px;
        height: 52px;
        padding: 6px 12px;
        border-radius: 12px;
        background: #ffffff;
        border: 1px solid rgba(112, 19, 27, 0.1);
    }
    .readonly-record-pill.reference-pill {
        min-width: 258px;
        height: 52px;
        align-items: center;
        flex-direction: column;
        justify-content: center;
        text-align: center;
    }
    .readonly-record-pill.reference-pill > span {
        white-space: nowrap;
    }
    .readonly-record-pill span {
        color: #64748b;
        font-size: 9px;
        font-weight: 900;
        letter-spacing: .07em;
        text-transform: uppercase;
    }
    .readonly-record-pill strong {
        color: #111827;
        font-size: 11px;
        font-weight: 900;
        word-break: break-word;
        line-height: 1.15;
    }
    .readonly-reference-value {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        gap: 6px;
        color: #000000 !important;
        font-weight: 900;
        white-space: nowrap;
    }
    .readonly-reference-value span {
        color: #000000 !important;
    }
    .readonly-copy-btn {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        width: 28px;
        height: 28px;
        flex: 0 0 28px;
        border: 1.5px solid #7f1d2d;
        border-radius: 7px;
        background: #ffffff;
        color: #7f1d2d;
        cursor: pointer;
        transition: all .18s ease;
        padding: 4px;
    }
    .readonly-copy-btn svg {
        width: 16px;
        height: 16px;
        stroke-width: 2;
    }
    .readonly-copy-btn:hover {
        background: #7f1d2d;
        border-color: #7f1d2d;
        color: #ffffff;
        transform: translateY(-2px);
        box-shadow: 0 4px 12px rgba(127, 29, 45, 0.2);
    }
    .readonly-copy-btn.is-copied {
        background: #dcfce7;
        border-color: #86efac;
        color: #166534;
    }
    .readonly-copy-btn.is-copied:hover {
        background: #dcfce7;
        box-shadow: 0 4px 12px rgba(22, 101, 52, 0.2);
    }
    .readonly-record-pill.condition-pill {
        background: #eff6ff;
        border-color: #bfdbfe;
        box-shadow: 0 8px 18px rgba(59, 130, 246, 0.1);
    }
    .readonly-record-pill.condition-pill.has-condition {
        background: #fef2f2;
        border-color: #fecaca;
        box-shadow: 0 8px 18px rgba(239, 68, 68, 0.1);
    }
    .readonly-record-pill.condition-pill span,
    .readonly-record-pill.condition-pill strong {
        color: #1d4ed8;
    }
    .readonly-record-pill.condition-pill.has-condition span,
    .readonly-record-pill.condition-pill.has-condition strong {
        color: #991b1b;
    }
    .readonly-expand-btn {
        position: relative;
        overflow: hidden;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        width: 100%;
        min-width: 0;
        min-height: 46px;
        border: 1px solid #70131B;
        border-radius: 12px;
        background: #70131B;
        color: #ffffff;
        font-size: 11px;
        font-weight: 900;
        cursor: pointer;
        transition: color .25s ease, background-color .25s ease, transform .25s ease;
    }
    .readonly-expand-btn::before {
        content: '';
        position: absolute;
        inset: 0;
        background: linear-gradient(90deg,
            rgba(255, 248, 196, 0) 0%,
            rgba(255, 239, 181, 0.18) 22%,
            rgba(255, 239, 181, 0.62) 48%,
            rgba(255, 239, 181, 0.18) 72%,
            rgba(255, 248, 196, 0) 100%);
        transform: translateX(-135%);
        transition: transform 1.2s ease;
    }
    .readonly-expand-btn span {
        position: relative;
        z-index: 1;
    }
    .readonly-expand-btn:hover {
        background: #facc15;
        color: #70131B;
        transform: translateY(-1px);
    }
    .readonly-expand-btn:hover::before {
        transform: translateX(135%);
    }
    .readonly-review-btn {
        border-color: rgba(112, 19, 27, 0.22);
        background: #70131B;
        color: #ffffff;
    }
    .readonly-review-btn:hover {
        background: #facc15;
        color: #111827;
        border-color: #facc15;
    }
    .readonly-record-details {
        display: none;
        gap: 14px;
    }
    .readonly-record-card.is-expanded .readonly-record-details {
        display: grid;
    }
    .readonly-record-name {
        margin: 0;
        color: #111827;
        font-size: 15px;
        font-weight: 900;
    }
    .readonly-record-sub {
        margin: 4px 0 0;
        color: #64748b;
        font-size: 12px;
        font-weight: 700;
    }
    .readonly-record-status {
        flex: 0 0 auto;
        border-radius: 999px;
        padding: 6px 10px;
        background: #fef3c7;
        color: #70131B;
        font-size: 10px;
        font-weight: 900;
        text-transform: uppercase;
        letter-spacing: .05em;
    }
    .readonly-record-grid {
        display: grid;
        grid-template-columns: repeat(2, minmax(0, 1fr));
        gap: 10px;
    }
    .readonly-field {
        padding: 10px 12px;
        border-radius: 12px;
        background: #ffffff;
        border: 1px solid rgba(112, 19, 27, 0.08);
    }
    .readonly-field span {
        display: block;
        color: #64748b;
        font-size: 10px;
        font-weight: 900;
        letter-spacing: .07em;
        text-transform: uppercase;
    }
    .readonly-field strong {
        display: block;
        margin-top: 4px;
        color: #111827;
        font-size: 13px;
        font-weight: 900;
        word-break: break-word;
    }
    .readonly-docs-title {
        margin: 2px 0 0;
        color: #70131B;
        font-size: 12px;
        font-weight: 900;
        text-transform: uppercase;
        letter-spacing: .06em;
    }
    .readonly-doc-list {
        display: grid;
        grid-template-columns: repeat(2, minmax(0, 1fr));
        gap: 8px;
        margin: 0;
        padding: 0;
        list-style: none;
    }
    .readonly-doc-link,
    .readonly-doc-missing {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 8px;
        min-height: 40px;
        padding: 9px 11px;
        border-radius: 12px;
        font-size: 12px;
        font-weight: 900;
        text-decoration: none;
    }
    .readonly-doc-link {
        border: 1px solid rgba(112, 19, 27, 0.18);
        background: #ffffff;
        color: #70131B;
        transition: background-color .2s ease, color .2s ease, transform .2s ease;
    }
    .readonly-doc-link:hover {
        background: #facc15;
        color: #70131B;
        border-color: #facc15;
        transform: translateY(-1px);
    }
    .readonly-doc-missing {
        border: 1px dashed rgba(100, 116, 139, 0.25);
        background: #f8fafc;
        color: #94a3b8;
        transition: background-color .2s ease, color .2s ease, border-color .2s ease;
    }
    .readonly-doc-missing:hover {
        background: #facc15;
        border-color: #facc15;
        color: #70131B;
    }
    @media (max-width: 860px) {
        .readonly-record-head {
            flex-direction: column;
            align-items: stretch;
        }
        .readonly-record-meta {
            justify-content: stretch;
            flex-wrap: wrap;
            max-width: none;
        }
        .readonly-record-pill {
            flex: 1 1 180px;
        }
        .readonly-record-pill.reference-pill {
            min-width: 0;
            flex: 1 1 100%;
        }
        .readonly-record-actions {
            width: 100%;
            flex: 1 1 100%;
        }
    }
    html[data-theme="dark"] .awaiting-info-row {
        background: rgba(17, 24, 39, .92);
        border-color: rgba(250, 204, 21, .18);
    }
    html[data-theme="dark"] .awaiting-links-modal-shell {
        background: rgba(15, 23, 42, .98);
        border-color: rgba(250, 204, 21, .18);
        box-shadow: 0 28px 72px rgba(0, 0, 0, .42);
    }
    html[data-theme="dark"] .awaiting-links-modal-body {
        background: rgba(15, 23, 42, .98);
        color: #ffffff;
    }
    html[data-theme="dark"] .awaiting-links-modal-body th {
        color: #e5e7eb;
        border-color: rgba(250, 204, 21, .14);
    }
    html[data-theme="dark"] .awaiting-links-modal-body td,
    html[data-theme="dark"] .awaiting-links-modal-body td * {
        color: #ffffff;
    }
    html[data-theme="dark"] .awaiting-links-modal-body tbody tr {
        border-color: rgba(250, 204, 21, .10);
    }
    html[data-theme="dark"] .awaiting-links-modal-body tbody tr:hover {
        background: rgba(250, 204, 21, .06);
    }
    html[data-theme="dark"] .readonly-modal-search {
        background: rgba(17, 24, 39, .96);
        border-color: rgba(250, 204, 21, .18);
        box-shadow: 0 14px 28px rgba(0, 0, 0, .28);
    }
    html[data-theme="dark"] .readonly-modal-search svg {
        color: #facc15;
    }
    html[data-theme="dark"] .readonly-modal-search input {
        color: #ffffff;
    }
    html[data-theme="dark"] .readonly-modal-search input::placeholder {
        color: #cbd5e1;
    }
    html[data-theme="dark"] .readonly-search-empty {
        background: rgba(17, 24, 39, .92);
        border-color: rgba(250, 204, 21, .18);
        color: #ffffff;
    }
    html[data-theme="dark"] .awaiting-info-row span {
        color: #cbd5e1;
    }
    html[data-theme="dark"] .awaiting-info-row strong {
        color: #ffffff;
    }
    html[data-theme="dark"] .pending-approval-card,
    html[data-theme="dark"] .pending-approval-empty,
    html[data-theme="dark"] .readonly-record-card {
        background: rgba(17, 24, 39, .92);
        border-color: rgba(250, 204, 21, .18);
    }
    html[data-theme="dark"] .pending-approval-card:hover {
        background: rgba(250, 204, 21, .08);
        border-color: rgba(250, 204, 21, .32);
    }
    html[data-theme="dark"] .pending-approval-name,
    html[data-theme="dark"] .readonly-record-name,
    html[data-theme="dark"] .readonly-field strong,
    html[data-theme="dark"] .readonly-record-pill strong {
        color: #ffffff;
    }
    html[data-theme="dark"] .pending-approval-meta,
    html[data-theme="dark"] .pending-approval-empty,
    html[data-theme="dark"] .readonly-record-sub,
    html[data-theme="dark"] .readonly-field span,
    html[data-theme="dark"] .readonly-record-pill span {
        color: #cbd5e1;
    }
    html[data-theme="dark"] .readonly-field,
    html[data-theme="dark"] .readonly-record-pill,
    html[data-theme="dark"] .readonly-doc-link {
        background: rgba(15, 23, 42, .9);
        border-color: rgba(250, 204, 21, .14);
    }
    html[data-theme="dark"] .readonly-doc-link {
        color: #facc15;
    }
    html[data-theme="dark"] .readonly-doc-missing {
        background: rgba(15, 23, 42, .72);
        color: #94a3b8;
    }
    html[data-theme="dark"] .readonly-resubmission-summary {
        background: rgba(250, 204, 21, .1);
        border-color: rgba(250, 204, 21, .24);
        color: #facc15;
    }
    html[data-theme="dark"] .readonly-resubmission-help {
        color: #cbd5e1;
    }
    html[data-theme="dark"] .readonly-resubmission-option {
        background: rgba(15, 23, 42, .86);
        border-color: rgba(250, 204, 21, .14);
        color: #f8fafc;
    }
    html[data-theme="dark"] .readonly-doc-preview-btn,
    html[data-theme="dark"] .readonly-doc-preview-empty {
        background: rgba(15, 23, 42, .86);
        border-color: rgba(250, 204, 21, .14);
        color: #f8fafc;
    }
    html[data-theme="dark"] .readonly-doc-preview-btn:hover {
        background: rgba(250, 204, 21, .1);
        border-color: rgba(250, 204, 21, .28);
        color: #facc15;
    }
    html[data-theme="dark"] .resubmission-progress-card {
        background: #111827;
        border-color: rgba(250, 204, 21, .28);
    }
    html[data-theme="dark"] .resubmission-progress-card strong {
        color: #facc15;
    }
    html[data-theme="dark"] .resubmission-progress-card span {
        color: #cbd5e1;
    }
    html[data-theme="dark"] .readonly-modal-pagination {
        background: rgba(17, 24, 39, .96);
        border-color: rgba(250, 204, 21, .18);
        box-shadow: 0 12px 28px rgba(0, 0, 0, .26);
    }
    html[data-theme="dark"] .readonly-pagination-summary {
        color: #ffffff;
    }
    html[data-theme="dark"] .readonly-pagination-btn,
    html[data-theme="dark"] .readonly-pagination-per-page-select {
        background: rgba(15, 23, 42, .96);
        border-color: rgba(250, 204, 21, .18);
        color: #ffffff;
    }
    html[data-theme="dark"] .readonly-pagination-btn.is-active {
        background: #7f0010;
        border-color: #facc15;
        color: #ffffff;
    }
    .health-summary-card {
        position: relative;
        overflow: hidden;
    }
    .health-summary-card::before {
        content: "";
        position: absolute;
        top: 0;
        left: 14px;
        right: 14px;
        height: 5px;
        background: #70131B;
        border-radius: 999px;
        pointer-events: none;
        z-index: 1;
    }
    .card,
    .card *:not(.status):not(.btn-action):not(.btn-sign) {
        color: #111827;
    }
    
    table { width: 100%; border-collapse: collapse; margin-top: 15px; }
    
    th {
        text-align: left;
        font-size: 12px;
        font-weight: 800;
        color: #111827;
        text-transform: uppercase;
        padding: 12px 16px;
        border-bottom: 2px solid #f1f5f9;
    }
    
    td {
        padding: 16px;
        border-bottom: 1px solid #f8fafc;
        font-size: 14px;
        color: #111827;
        vertical-align: middle;
    }

    /* Status Badges */
    .status { padding: 5px 12px; border-radius: 99px; font-size: 11px; font-weight: 700; text-transform: uppercase; }
    .status.pending { background: #fff7ed; color: #c2410c; border: 1px solid #ffedd5; }
    .status.issued { background: #dcfce7; color: #15803d; }
    .status.review { background: #fee2e2; color: #b91c1c; }
    .status.submitted { background: #e0f2fe; color: #0369a1; }

    html[data-theme="dark"] .status.pending {
        background: #fff7ed;
        color: #c2410c !important;
        border-color: #fed7aa;
    }

    html[data-theme="dark"] .status.issued {
        background: #dcfce7;
        color: #15803d !important;
        border-color: #86efac;
    }

    html[data-theme="dark"] .status.review {
        background: #fee2e2;
        color: #b91c1c !important;
        border-color: #fecaca;
    }

    html[data-theme="dark"] .status.submitted {
        background: #e0f2fe;
        color: #0369a1 !important;
        border-color: #bae6fd;
    }

    /* Buttons */
    .btn-action {
        min-width: 92px;
        min-height: 38px;
        padding: 8px 16px;
        border-radius: 999px;
        font-size: 12px;
        font-weight: 800;
        letter-spacing: 0.01em;
        cursor: pointer;
        border: 1px solid transparent;
        transition: transform 0.18s ease, box-shadow 0.18s ease, border-color 0.18s ease, background 0.18s ease, color 0.18s ease;
        text-decoration: none;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        box-shadow: 0 10px 20px rgba(15, 23, 42, 0.08);
    }

    .btn-action svg {
        width: 14px;
        height: 14px;
        margin-right: 6px;
        flex: 0 0 auto;
        stroke-width: 2;
    }
    .btn-action:hover {
        transform: translateY(-1px);
        text-decoration: none;
    }

    .btn-view {
        background: linear-gradient(135deg, #ffffff, #fff3f5);
        color: #70131B;
        border-color: #f0d7dc;
    }

    .btn-view:hover {
        background: linear-gradient(135deg, #fff7f8, #ffe7ed);
        border-color: #d9a9b4;
        box-shadow: 0 14px 24px rgba(112, 19, 27, 0.12);
    }

    .btn-sign {
        background: linear-gradient(135deg, #70131B, #8f2230);
        color: #ffffff;
        border-color: #8f2230;
        box-shadow:
            0 0 0 3px rgba(112, 19, 27, 0.10),
            0 12px 24px rgba(112, 19, 27, 0.18);
    }

    .btn-sign:hover {
        color: #ffffff;
        border-color: #facc15;
        box-shadow:
            0 0 0 3px rgba(250, 204, 21, 0.16),
            0 14px 26px rgba(112, 19, 27, 0.20);
    }

    .btn-signed,
    .btn-readonly {
        background: linear-gradient(135deg, #e2e8f0, #cbd5e1);
        color: #475569;
        border-color: #cbd5e1;
        cursor: not-allowed;
        box-shadow: none;
    }

    .btn-signed::before {
        content: "✓";
        margin-right: 6px;
        font-weight: 900;
    }

    .health-issued-badge {
        min-width: 118px;
        min-height: 38px;
        padding: 8px 14px;
        border-radius: 999px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        gap: 8px;
        background: linear-gradient(135deg, #dcfce7, #bbf7d0);
        color: #166534;
        border: 1px solid #86efac;
        font-size: 12px;
        font-weight: 800;
        letter-spacing: 0.01em;
        box-shadow:
            0 0 0 3px rgba(34, 197, 94, 0.10),
            0 10px 20px rgba(22, 101, 52, 0.10);
    }

    .health-issued-badge svg {
        width: 16px;
        height: 16px;
        flex: 0 0 auto;
        stroke-width: 2;
        margin-right: 0;
    }

    /* Custom Flex Grid para sa Summary Cards */
    .summary-container {
        display: grid;
        grid-template-columns: repeat(4, minmax(0, 1fr));
        gap: 20px;
        width: 100%;
        margin-bottom: 25px;
        align-items: stretch;
    }
    .summary-item {
        min-width: 0;
    }
    .health-summary-action-card {
        width: 100%;
        min-height: 90px;
        display: flex;
        align-items: center;
        justify-content: flex-start;
        text-align: left;
        cursor: default;
        transition: transform .18s ease, border-color .18s ease, box-shadow .18s ease, background .18s ease;
    }
    .health-summary-metric-card {
        position: relative;
        overflow: hidden;
        min-height: 90px;
        align-items: center;
        justify-content: center;
        text-align: center;
        color: #111827 !important;
        box-shadow: none !important;
    }
    .health-summary-metric-card.is-approved {
        background: #eff6ff !important;
        border: 1.5px solid #93c5fd !important;
    }
    .health-summary-metric-card.is-condition {
        background: #fff1f2 !important;
        border: 1.5px solid #fda4af !important;
    }
    .health-summary-metric-card * {
        color: inherit !important;
    }
    .health-summary-metric-label {
        display: block;
        padding: 0;
        border-radius: 0;
        font-size: 11px;
        font-weight: 900;
        line-height: 1.1;
        letter-spacing: 0.8px;
        text-transform: uppercase;
        white-space: nowrap;
    }
    .health-summary-metric-card.is-approved .health-summary-metric-label {
        color: #1d4ed8 !important;
    }
    .health-summary-metric-card.is-condition .health-summary-metric-label {
        color: #dc2626 !important;
    }
    .health-summary-metric-count {
        display: block;
        margin: 10px 0 0;
        font-size: 28px;
        font-weight: 900;
        line-height: 1;
    }
    .health-summary-metric-card.is-approved .health-summary-metric-count {
        color: #1e3a8a !important;
    }
    .health-summary-metric-card.is-condition .health-summary-metric-count {
        color: #881337 !important;
    }
    .health-summary-info-btn {
        cursor: pointer;
    }
    .health-summary-info-btn:hover {
        transform: translateY(-2px);
        border-color: rgba(112, 19, 27, .42) !important;
        box-shadow: 0 14px 28px rgba(112, 19, 27, .14);
        background: #fffaf0;
    }
    .health-records-overview {
        background: #ffffff;
        border: 1px solid #e5e7eb;
        border-radius: 16px;
        box-shadow: 0 14px 34px rgba(15, 23, 42, .06);
        overflow: hidden;
    }
    .health-records-overview-head {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 18px;
        padding: 18px 20px;
        border-bottom: 1px solid #edf2f7;
    }
    .health-records-title-block {
        display: flex;
        align-items: center;
        gap: 14px;
        min-width: 0;
    }
    .health-records-title-icon {
        width: 54px;
        height: 54px;
        border-radius: 14px;
        display: grid;
        place-items: center;
        background: #fff1f2;
        color: #b91c1c;
        flex: 0 0 auto;
    }
    .health-records-title-icon svg {
        width: 28px;
        height: 28px;
    }
    .health-records-title-copy h2 {
        margin: 0;
        color: #0f172a;
        font-size: 26px;
        font-weight: 900;
        line-height: 1.08;
    }
    .health-records-title-copy p {
        margin: 6px 0 0;
        color: #64748b;
        font-size: 13px;
        font-weight: 700;
    }
    .health-records-last-updated {
        display: grid;
        grid-template-columns: 42px auto;
        gap: 10px;
        align-items: center;
        color: #0f172a;
        flex: 0 0 auto;
    }
    .health-records-last-updated-icon {
        width: 42px;
        height: 42px;
        border-radius: 12px;
        display: flex !important;
        align-items: center !important;
        justify-content: center !important;
        background: #fff1f2;
        color: #b91c1c;
        line-height: 0;
    }
    .health-records-last-updated-icon svg {
        width: 20px !important;
        height: 20px !important;
        display: block !important;
        margin: auto !important;
        transform: none !important;
        position: static !important;
        inset: auto !important;
        flex: 0 0 20px !important;
    }
    .health-records-last-updated span {
        display: block;
        color: #64748b;
        font-size: 11px;
        font-weight: 900;
        text-transform: uppercase;
        letter-spacing: .04em;
    }
    .health-records-last-updated strong {
        display: block;
        margin-top: 2px;
        font-size: 13px;
        font-weight: 900;
        line-height: 1.25;
    }
    .health-records-last-updated > .health-records-last-updated-icon {
        display: flex !important;
        align-items: center !important;
        justify-content: center !important;
        place-items: center !important;
        padding: 0 !important;
        text-align: center !important;
    }
    .health-records-last-updated > .health-records-last-updated-icon > svg {
        display: block !important;
        width: 20px !important;
        height: 20px !important;
        margin: 0 !important;
        transform: translate(0, 0) !important;
    }
    .health-records-overview-search {
        display: grid;
        grid-template-columns: minmax(0, 1fr) auto;
        gap: 12px;
        padding: 18px 20px 8px;
    }
    .health-table-tools {
        width: min(100%, 620px);
        display: grid;
        grid-template-columns: minmax(220px, 1fr) auto;
        align-items: end;
        gap: 12px;
        margin-left: auto;
    }
    .health-table-tools .health-records-search-shell,
    .health-table-tools .health-records-search-wrap {
        width: 100%;
        opacity: 1;
        pointer-events: auto;
    }
    .health-table-tools .health-records-search-wrap {
        position: relative;
        display: flex;
        align-items: center;
        min-height: 42px;
        border: 0;
        border-radius: 0;
        background: transparent;
        border-bottom: 3px solid #8f2230;
        box-shadow: none;
    }
    .health-table-tools .health-records-search-wrap::before {
        content: "";
        width: 18px;
        height: 18px;
        margin: 0 12px 0 2px;
        background: currentColor;
        color: #9f1239;
        flex: 0 0 auto;
        mask: url("data:image/svg+xml,%3Csvg viewBox='0 0 24 24' fill='none' xmlns='http://www.w3.org/2000/svg'%3E%3Cpath d='m21 21-4.35-4.35M10.5 18a7.5 7.5 0 1 1 0-15 7.5 7.5 0 0 1 0 15Z' stroke='black' stroke-width='2' stroke-linecap='round'/%3E%3C/svg%3E") center / contain no-repeat;
    }
    .health-table-tools .health-records-search {
        width: 100% !important;
        min-height: 40px;
        height: 40px;
        padding: 8px 0 !important;
        border: 0 !important;
        border-radius: 0 !important;
        background: transparent !important;
        box-shadow: none !important;
        color: #0f172a;
        font-weight: 800;
    }
    .health-table-tools .health-records-search:focus {
        outline: none;
    }
    .health-table-tools .health-records-search::placeholder {
        color: #94a3b8;
        font-weight: 800;
    }
    .health-records-overview-search .health-records-search-shell,
    .health-records-overview-search .health-records-search-wrap {
        width: 100%;
        flex: 1 1 auto;
        opacity: 1;
        pointer-events: auto;
    }
    .health-records-overview-search .health-records-search-wrap {
        border: 1px solid #e2e8f0;
        border-radius: 12px;
        background: #fff;
        display: flex;
        align-items: center;
        min-height: 48px;
    }
    .health-records-overview-search .health-records-search-wrap::before {
        content: "";
        width: 20px;
        height: 20px;
        margin-left: 18px;
        background: currentColor;
        color: #9f1239;
        mask: url("data:image/svg+xml,%3Csvg viewBox='0 0 24 24' fill='none' xmlns='http://www.w3.org/2000/svg'%3E%3Cpath d='m21 21-4.35-4.35M10.5 18a7.5 7.5 0 1 1 0-15 7.5 7.5 0 0 1 0 15Z' stroke='black' stroke-width='2' stroke-linecap='round'/%3E%3C/svg%3E") center / contain no-repeat;
    }
    .health-records-overview-search .health-records-search {
        width: 100% !important;
        min-height: 46px;
        height: 46px;
        border-radius: 0 !important;
        border: 0 !important;
        background: transparent !important;
        color: #0f172a;
        font-weight: 800;
    }
    .health-records-overview-search .health-records-search::placeholder {
        color: #94a3b8;
        font-weight: 800;
    }
    .health-records-search-submit {
        min-width: 116px;
        min-height: 48px;
        border: 1px solid #7f1d2d;
        border-radius: 12px;
        background: #7f1d2d;
        color: #ffffff;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        gap: 8px;
        font-size: 13px;
        font-weight: 900;
        cursor: pointer;
        box-shadow: 0 10px 20px rgba(112, 19, 27, .08);
        overflow: hidden;
        position: relative;
        transition: color .18s ease, background .18s ease, border-color .18s ease, transform .18s ease, box-shadow .18s ease;
    }
    .health-records-search-submit::after {
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
    .health-records-search-submit > * {
        position: relative;
        z-index: 1;
    }
    .health-records-search-submit svg {
        width: 18px;
        height: 18px;
    }
    .health-records-search-submit:hover,
    .health-records-search-submit:focus-visible {
        background: #facc15;
        border-color: #facc15;
        color: #7f1d2d;
        transform: translateY(-1px);
        outline: none;
    }
    .health-records-search-submit:hover::after,
    .health-records-search-submit:focus-visible::after {
        left: 125%;
    }
    .health-table-tools .health-records-search-submit {
        min-height: 42px;
        min-width: 106px;
        box-shadow: none;
    }
    .health-summary-modern-container {
        display: grid;
        grid-template-columns: repeat(4, minmax(0, 1fr));
        gap: 12px;
        padding: 14px 20px 18px;
    }
    .health-summary-modern-card {
        position: relative;
        min-height: 100px;
        border-radius: 14px;
        border: 1px solid #e5e7eb;
        background: #ffffff;
        padding: 14px 14px;
        display: grid;
        grid-template-columns: 44px minmax(0, 1fr) auto;
        align-items: center;
        gap: 14px;
        overflow: hidden;
        text-align: left;
        text-decoration: none;
        font: inherit;
        width: 100%;
        transition: transform .18s ease, border-color .18s ease, box-shadow .18s ease, background .18s ease;
    }
    .health-summary-modern-card::before {
        content: "";
        position: absolute;
        top: -40%;
        bottom: -40%;
        left: -70%;
        width: 34%;
        opacity: 0;
        background: linear-gradient(105deg, rgba(255, 255, 255, 0) 0%, rgba(255, 248, 196, .25) 42%, rgba(255, 248, 196, .7) 50%, rgba(255, 248, 196, .25) 58%, rgba(255, 255, 255, 0) 100%);
        transform: translateX(0) skewX(-18deg);
        transition: transform .42s ease, opacity .08s ease;
        pointer-events: none;
        z-index: 1;
    }
    .health-summary-modern-card.is-clickable:hover::before {
        opacity: 1;
        transform: translateX(510%) skewX(-18deg);
    }
    .health-summary-modern-card::after {
        content: "";
        position: absolute;
        left: 0;
        right: auto;
        bottom: 0;
        width: 100%;
        height: 74%;
        opacity: .24;
        background: #ffd700;
        display: none;
        border: 0;
        border-radius: 0;
        transform: none;
        pointer-events: none;
    }
    .health-summary-modern-card.is-approved {
        border-color: #bbf7d0;
        color: #16a34a;
    }
    .health-summary-modern-card.is-approved::after,
    .health-summary-modern-card.is-condition::after {
        background: #70131B;
        opacity: .16;
    }
    .health-summary-modern-card.is-pending::after,
    .health-summary-modern-card.is-compliance::after {
        background: #ffd700;
        opacity: .28;
    }
    .health-summary-modern-card.is-approved .health-summary-modern-icon-wrap {
        background: #dcfce7;
        color: #16a34a;
    }
    .health-summary-modern-card.is-condition {
        border-color: #fecaca;
        color: #dc2626;
    }
    .health-summary-modern-card.is-condition .health-summary-modern-icon-wrap {
        background: #fee2e2;
        color: #dc2626;
    }
    .health-summary-modern-card.is-pending {
        border-color: rgba(250, 204, 21, .62);
        background: linear-gradient(135deg, #70131B, #8f1727);
        color: #ffffff;
    }
    .health-summary-modern-card.is-pending .health-summary-modern-icon-wrap {
        background: rgba(255, 255, 255, .14);
        color: #ffffff;
    }
    .health-summary-modern-card.is-compliance {
        border-color: rgba(250, 204, 21, .62);
        background: linear-gradient(135deg, #70131B, #8f1727);
        color: #ffffff;
    }
    .health-summary-modern-card.is-compliance .health-summary-modern-icon-wrap {
        background: rgba(255, 255, 255, .14);
        color: #ffffff;
    }
    .health-summary-modern-card.is-clickable {
        cursor: pointer;
    }
    .health-summary-modern-card > * {
        position: relative;
        z-index: 2;
    }
    .health-summary-modern-card.is-clickable:hover {
        transform: translateY(-3px);
        box-shadow: 0 18px 34px rgba(112, 19, 27, .14);
        background: #fffaf0;
    }
    .health-summary-modern-card.is-pending.is-clickable:hover,
    .health-summary-modern-card.is-compliance.is-clickable:hover {
        background: #facc15;
        color: #70131B;
        border-color: #facc15;
    }
    .health-summary-modern-card.is-pending.is-clickable:hover::after,
    .health-summary-modern-card.is-compliance.is-clickable:hover::after {
        background: #7f1d2d;
        opacity: .22;
    }
    .health-summary-modern-card.is-pending.is-clickable:hover .health-summary-modern-copy,
    .health-summary-modern-card.is-pending.is-clickable:hover .health-summary-modern-label,
    .health-summary-modern-card.is-pending.is-clickable:hover .health-summary-modern-count,
    .health-summary-modern-card.is-pending.is-clickable:hover .health-summary-modern-note,
    .health-summary-modern-card.is-pending.is-clickable:hover .health-summary-modern-arrow,
    .health-summary-modern-card.is-compliance.is-clickable:hover .health-summary-modern-copy,
    .health-summary-modern-card.is-compliance.is-clickable:hover .health-summary-modern-label,
    .health-summary-modern-card.is-compliance.is-clickable:hover .health-summary-modern-count,
    .health-summary-modern-card.is-compliance.is-clickable:hover .health-summary-modern-note,
    .health-summary-modern-card.is-compliance.is-clickable:hover .health-summary-modern-arrow {
        color: #70131B;
    }
    .health-summary-modern-card.is-pending.is-clickable:hover .health-summary-modern-icon-wrap,
    .health-summary-modern-card.is-compliance.is-clickable:hover .health-summary-modern-icon-wrap {
        background: rgba(112, 19, 27, .14);
        color: #70131B;
    }
    .health-summary-modern-icon {
        width: 58px;
        height: 58px;
        border-radius: 999px;
        display: grid;
        place-items: center;
        background: currentColor;
        color: inherit;
        opacity: .12;
    }
    .health-summary-modern-icon svg {
        width: 30px;
        height: 30px;
        opacity: 7;
        stroke: currentColor;
    }
    .health-summary-modern-icon-wrap {
        width: 44px;
        height: 44px;
        border-radius: 999px;
        display: grid;
        place-items: center;
        color: inherit;
        z-index: 1;
    }
    .health-summary-modern-icon-wrap svg {
        width: 25px;
        height: 25px;
    }
    .health-summary-modern-copy {
        position: relative;
        z-index: 1;
        color: #0f172a;
        min-width: 0;
    }
    .health-summary-modern-label {
        display: block;
        color: #111827;
        font-size: 11px;
        font-weight: 900;
        text-transform: uppercase;
        letter-spacing: .04em;
    }
    .health-summary-modern-count {
        display: block;
        margin-top: 6px;
        color: #0f172a;
        font-size: 24px;
        font-weight: 900;
        line-height: 1;
        transition: color .22s ease, transform .22s ease;
    }
    .health-summary-modern-count.is-live-updated {
        animation: healthStatPulse .62s ease;
    }
    @keyframes healthStatPulse {
        0% { transform: scale(1); }
        45% { transform: scale(1.13); color: #9f1239; }
        100% { transform: scale(1); }
    }
    .health-summary-modern-note {
        display: block;
        margin-top: 7px;
        color: currentColor;
        font-size: 12px;
        font-weight: 900;
    }
    .health-summary-modern-card.is-pending .health-summary-modern-copy,
    .health-summary-modern-card.is-pending .health-summary-modern-label,
    .health-summary-modern-card.is-pending .health-summary-modern-count,
    .health-summary-modern-card.is-pending .health-summary-modern-note,
    .health-summary-modern-card.is-compliance .health-summary-modern-copy,
    .health-summary-modern-card.is-compliance .health-summary-modern-label,
    .health-summary-modern-card.is-compliance .health-summary-modern-count,
    .health-summary-modern-card.is-compliance .health-summary-modern-note {
        color: #ffffff;
    }
    .health-summary-modern-arrow {
        position: relative;
        z-index: 1;
        width: 38px;
        height: 38px;
        border-radius: 12px;
        display: grid;
        place-items: center;
        border: 1px solid #fecaca;
        background: #fff;
        color: #b91c1c;
        font-size: 26px;
        font-weight: 900;
    }
    .health-summary-modern-card.is-pending .health-summary-modern-arrow,
    .health-summary-modern-card.is-compliance .health-summary-modern-arrow {
        border-color: rgba(250, 204, 21, .52);
        background: rgba(255, 255, 255, .10);
        color: #ffffff;
    }
    @media (max-width: 1180px) {
        .health-summary-modern-container {
            grid-template-columns: repeat(2, minmax(0, 1fr));
        }
    }
    @media (max-width: 768px) {
        .health-records-overview-head {
            align-items: flex-start;
            flex-direction: column;
        }
        .health-records-overview-search {
            grid-template-columns: 1fr;
            gap: 10px;
        }
        .health-records-overview-search .health-records-search-wrap {
            border-right: 1px solid #e2e8f0;
            border-radius: 12px;
        }
        .health-records-search-submit {
            width: 100%;
            border-radius: 12px;
        }
        .health-summary-modern-container {
            grid-template-columns: 1fr;
        }
        .health-summary-modern-card {
            min-height: 118px;
        }
    }
    .health-records-title {
        margin: 0;
        color: #111827;
        display: inline-flex;
        align-items: center;
        padding: 10px 18px;
        border-radius: 0 0 14px 14px;
        border: 0;
        border-bottom: 2px solid rgba(234, 215, 160, 0.9);
        background: transparent;
        box-shadow: none;
    }

    .health-records-title svg {
        width: 18px;
        height: 18px;
        margin-right: 10px;
        flex: 0 0 auto;
    }

    .health-records-toolbar {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 18px;
        width: 100%;
        margin-bottom: 20px;
        padding: 16px 18px;
        border-radius: 0 0 20px 20px;
        border: 0;
        border-bottom: 2px solid rgba(112, 19, 27, 0.72);
        background: linear-gradient(135deg, rgba(255, 253, 246, 0.76) 0%, rgba(255, 249, 231, 0.58) 42%, rgba(255, 255, 255, 0.82) 100%);
        box-shadow: 0 14px 26px rgba(112, 19, 27, 0.05);
    }

    .health-records-toolbar-actions {
        display: flex;
        align-items: center;
        justify-content: flex-end;
        gap: 12px;
        flex-wrap: wrap;
        margin-left: auto;
    }

    .health-medical-launch-btn {
        display: inline-flex;
        align-items: center;
        gap: 10px;
        min-height: 50px;
        padding: 0 18px;
        border-radius: 999px;
        border: 1px solid #8f2230;
        background: linear-gradient(135deg, #70131B, #8f2230);
        color: #ffffff;
        font-size: 13px;
        font-weight: 900;
        letter-spacing: 0.01em;
        cursor: pointer;
        position: relative;
        overflow: hidden;
        box-shadow:
            0 0 0 3px rgba(112, 19, 27, 0.12),
            0 10px 22px rgba(112, 19, 27, 0.20);
        transition: color .08s linear, transform .18s ease, box-shadow .18s ease, border-color .18s ease;
        white-space: nowrap;
    }

    .health-medical-launch-btn::after {
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
        pointer-events: none;
    }

    .health-medical-launch-btn:hover,
    .health-medical-launch-btn:focus {
        transform: translateY(-1px);
        background: #facc15;
        color: #111827;
        border-color: #facc15;
        box-shadow:
            0 0 0 3px rgba(250, 204, 21, 0.18),
            0 14px 24px rgba(112, 19, 27, 0.16);
        outline: none;
    }

    .health-medical-launch-btn:hover::after,
    .health-medical-launch-btn:focus::after {
        transform: translateX(135%);
    }

    .health-medical-launch-icon {
        width: 28px;
        height: 28px;
        border-radius: 999px;
        background: rgba(255,255,255,0.16);
        border: 1px solid rgba(255,255,255,0.20);
        display: inline-flex;
        align-items: center;
        justify-content: center;
        flex: 0 0 28px;
        position: relative;
        z-index: 1;
    }

    .health-medical-launch-btn svg {
        width: 16px;
        height: 16px;
        stroke-width: 2;
        flex: 0 0 auto;
        position: relative;
        z-index: 1;
    }

    .health-medical-launch-text {
        position: relative;
        z-index: 1;
    }
    .health-records-toolbar-actions {
        display: flex;
        align-items: center;
        gap: 12px;
        justify-content: flex-end;
    }

    .health-records-search-shell {
        display: inline-flex;
        align-items: center;
        gap: 0;
        justify-content: flex-end;
    }
    .health-records-search-wrap {
        width: 0;
        max-width: 100%;
        flex: 0 0 0;
        opacity: 0;
        overflow: hidden;
        pointer-events: none;
        transform: translateX(12px) scaleX(0.96);
        transform-origin: right center;
        transition:
            width .32s cubic-bezier(.22, 1, .36, 1),
            flex-basis .32s cubic-bezier(.22, 1, .36, 1),
            opacity .24s ease,
            transform .28s cubic-bezier(.22, 1, .36, 1);
        background: transparent !important;
        border: 0 !important;
        box-shadow: none !important;
        padding: 0 !important;
        border-radius: 0 !important;
    }
    .health-records-search-shell.is-open .health-records-search-wrap {
        width: 320px;
        flex: 0 0 320px;
        opacity: 1;
        pointer-events: auto;
        transform: translateX(0) scaleX(1);
        background: #ffffff !important;
        border: 1px solid rgba(112, 19, 27, 0.18) !important;
        border-radius: 14px !important;
        box-shadow: 0 12px 28px rgba(15, 23, 42, 0.08) !important;
        padding: 0 8px !important;
    }

    .health-filter-shell {
        display: flex;
        align-items: center;
    }

    .health-filter-toggle {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        min-height: 50px;
        min-width: 140px !important;
        padding: 0 16px !important;
        gap: 8px !important;
        width: auto !important;
        border-radius: 14px !important;
        border: 1px solid #e2e8f0;
        background: #ffffff;
        color: #70131B;
        cursor: pointer;
        font-weight: 700;
        font-size: 13px;
        transition: all .18s ease;
        white-space: nowrap;
    }

    .health-filter-toggle svg {
        width: 18px !important;
        height: 18px !important;
        flex: 0 0 auto;
        stroke: currentColor;
        fill: none;
        stroke-width: 2;
    }

    .health-filter-toggle:hover,
    .health-filter-toggle.is-open {
        background: #fef3c7;
        border-color: #facc15;
        color: #111827 !important;
        box-shadow: 0 0 0 3px rgba(250, 204, 21, 0.18);
    }

    .health-records-search-toggle {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        min-height: 50px;
        min-width: 50px;
        padding: 0 14px;
        border-radius: 14px;
        border: none;
        background: linear-gradient(135deg, #0369a1 0%, #0284c7 100%);
        color: #ffffff;
        cursor: pointer;
        transition: all .18s ease;
        z-index: 1;
        position: relative;
        overflow: hidden;
        box-shadow: 0 4px 12px rgba(3, 105, 161, 0.2);
    }

    .health-records-search-toggle:hover,
    .health-records-search-toggle:focus {
        background: linear-gradient(135deg, #0284c7 0%, #0ea5e9 100%);
        color: #ffffff;
        outline: none;
        box-shadow: 0 8px 20px rgba(3, 105, 161, 0.3);
        transform: translateY(-2px);
    }

    .health-records-search-toggle:active {
        transform: translateY(0);
        box-shadow: 0 4px 12px rgba(3, 105, 161, 0.2);
    }

    .health-records-search-toggle svg {
        width: 20px;
        height: 20px;
        stroke-width: 2.5;
        stroke: currentColor;
        fill: none;
    }

    .health-filter-form {
        display: grid;
        grid-template-columns: 1fr;
        align-items: end;
        gap: 16px;
        padding: 20px;
    }

    .health-filter-field {
        display: flex;
        flex-direction: column;
        gap: 9px;
        min-width: 0;
    }

    .health-filter-actions {
        display: flex;
        align-items: center;
        gap: 10px;
        justify-content: flex-end;
        grid-column: 1 / -1;
        flex-wrap: nowrap;
    }

    .health-filter-field label {
        font-size: 11px;
        font-weight: 900;
        text-transform: uppercase;
        letter-spacing: 0.05em;
        color: #111827;
    }

    .health-records-search,
    .health-filter-select {
        min-height: 48px;
        height: 48px;
        padding: 12px 42px 12px 14px;
        border-radius: 12px;
        border: 1px solid #f3c7c7 !important;
        border-bottom: 3px solid #70131B !important;
        min-width: 180px;
        color: #111827;
        background: #ffffff !important;
        box-shadow: 0 10px 18px rgba(15, 23, 42, .04) !important;
        transition: border-color .18s ease, box-shadow .18s ease, transform .18s ease;
        appearance: none;
        -webkit-appearance: none;
    }

    .health-filter-select-wrap {
        position: relative;
    }

    .health-filter-select-wrap:not(.has-custom-dropdown)::after {
        content: "";
        position: absolute;
        right: 15px;
        top: 50%;
        width: 10px;
        height: 10px;
        border-right: 2px solid #70131B;
        border-bottom: 2px solid #70131B;
        transform: translateY(-65%) rotate(45deg);
        pointer-events: none;
    }

    .health-filter-custom-source {
        position: absolute !important;
        width: 1px !important;
        height: 1px !important;
        opacity: 0 !important;
        pointer-events: none !important;
    }

    .health-filter-custom-trigger {
        width: 100%;
        min-height: 48px;
        border-radius: 14px;
        border: 1px solid #efc7c7;
        background: #fffdf8;
        color: #111827;
        padding: 12px 42px 12px 14px;
        font-size: 14px;
        font-weight: 800;
        text-align: left;
        box-shadow: 0 8px 16px rgba(112, 19, 27, .05);
        position: relative;
        cursor: pointer;
        transition: border-color .18s ease, box-shadow .18s ease, transform .18s ease;
    }

    .health-filter-custom-trigger::after {
        content: "";
        position: absolute;
        right: 15px;
        top: 50%;
        width: 10px;
        height: 10px;
        border-right: 2px solid #70131B;
        border-bottom: 2px solid #70131B;
        transform: translateY(-65%) rotate(45deg);
        transition: transform .18s ease;
        pointer-events: none;
    }

    .health-filter-select-wrap.is-open .health-filter-custom-trigger {
        border-color: #8f0012;
        box-shadow: 0 0 0 3px rgba(112, 19, 27, .08), 0 10px 22px rgba(112, 19, 27, .10);
        transform: translateY(-1px);
    }

    .health-filter-select-wrap.is-open .health-filter-custom-trigger::after {
        transform: translateY(-35%) rotate(225deg);
    }

    .health-filter-custom-menu {
        position: absolute;
        z-index: 20;
        top: calc(100% + 8px);
        left: 0;
        right: 0;
        display: none;
        padding: 8px;
        border: 1px solid #f3c7c7;
        border-radius: 14px;
        background: #ffffff;
        box-shadow: 0 18px 32px rgba(112, 19, 27, 0.14);
        max-height: 180px;
        overflow-y: auto;
    }

    .health-filter-select-wrap.is-open .health-filter-custom-menu {
        display: grid;
        gap: 6px;
    }

    .health-filter-select-wrap.is-year-level .health-filter-custom-menu {
        grid-template-columns: repeat(2, minmax(0, 1fr));
    }

    .health-filter-select-wrap.is-year-level .health-filter-custom-option:first-child {
        grid-column: 1 / -1;
    }

    .health-filter-select-wrap.is-year-level .health-filter-custom-option {
        text-align: center;
    }

    .health-filter-custom-option {
        border: 0;
        border-radius: 999px;
        background: #ffffff;
        color: #111827;
        min-height: 34px;
        padding: 7px 14px;
        font-size: 13px;
        font-weight: 900;
        text-align: left;
        cursor: pointer;
        transition: background .18s ease, color .18s ease, transform .18s ease;
    }

    .health-filter-custom-option:hover,
    .health-filter-custom-option:focus {
        background: #8f0012;
        color: #ffffff;
        outline: 0;
    }

    .health-filter-custom-option.is-selected {
        background: #8f0012;
        color: #facc15;
    }

    .health-records-search {
        width: 280px;
        background: #ffffff !important;
        border: 0 !important;
        border-bottom: 0 !important;
        border-radius: 12px !important;
    }
    .health-records-search::placeholder {
        color: #7f1d2d;
        font-weight: 700;
    }

    .health-records-search:focus,
    .health-filter-select:focus {
        outline: none;
        border-bottom-color: #70131B !important;
        box-shadow: 0 10px 22px rgba(112, 19, 27, .10) !important;
        transform: translateY(-1px);
    }
    .health-records-search-toggle {
        width: 50px !important;
        height: 50px !important;
        min-width: 50px !important;
        min-height: 50px !important;
        flex: 0 0 50px !important;
        padding: 0 !important;
        gap: 0 !important;
        border-radius: 999px !important;
        appearance: none !important;
        -webkit-appearance: none !important;
        background: linear-gradient(135deg, #70131B, #8f2230) !important;
        border: 1px solid #8f2230 !important;
        box-shadow:
            0 0 0 3px rgba(112, 19, 27, 0.12),
            0 10px 22px rgba(112, 19, 27, 0.20) !important;
        outline: none !important;
    }
    .health-records-search-toggle svg {
        width: 28px !important;
        height: 28px !important;
        stroke-width: 2 !important;
        display: block;
    }
    .health-records-search-toggle:hover,
    .health-records-search-toggle:focus {
        background: #facc15 !important;
        color: #111827 !important;
        border-color: #facc15 !important;
        box-shadow:
            0 0 0 3px rgba(250, 204, 21, 0.18),
            0 14px 24px rgba(112, 19, 27, 0.16) !important;
        outline: none !important;
    }

    .health-records-search-toggle:hover svg,
    .health-records-search-toggle:focus svg {
        color: #111827 !important;
        stroke: currentColor !important;
    }

    .health-filter-btn {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        position: relative;
        overflow: hidden;
        min-height: 44px;
        padding: 0 22px;
        border-radius: 10px;
        border: 1px solid #7f1d2d !important;
        background: #7f1d2d !important;
        color: #ffffff !important;
        fill: #ffffff !important;
        stroke: #ffffff !important;
        font-size: 13px;
        font-weight: 800;
        text-decoration: none;
        cursor: pointer;
        box-shadow:
            0 0 0 3px rgba(127, 29, 45, 0.12),
            0 10px 22px rgba(127, 29, 45, 0.08) !important;
        transition: color .08s linear, transform .18s ease, box-shadow .18s ease, border-color .18s ease, background .18s ease;
        z-index: 0;
    }
    .health-filter-btn,
    .health-filter-btn * {
        color: #ffffff !important;
        fill: #ffffff !important;
    }

    .health-filter-actions .health-filter-btn {
        background: #7f1d2d !important;
        border-color: #7f1d2d !important;
        color: #ffffff !important;
    }

    .health-filter-actions .health-filter-btn-reset {
        background: #ffffff !important;
        border-color: #cbd5e1 !important;
        color: #111827 !important;
        -webkit-text-fill-color: #111827 !important;
    }
    .health-filter-actions .health-filter-btn-reset,
    .health-filter-actions .health-filter-btn-reset * {
        color: #111827 !important;
        -webkit-text-fill-color: #111827 !important;
    }

    .health-filter-actions button.health-filter-btn:not(.health-filter-btn-reset) {
        background: #7f1d2d !important;
        border-color: #7f1d2d !important;
        color: #ffffff !important;
        -webkit-text-fill-color: #ffffff !important;
    }

    .health-filter-actions button.health-filter-btn:not(.health-filter-btn-reset):hover,
    .health-filter-actions a.health-filter-btn-reset:hover {
        background: #facc15 !important;
        border-color: #facc15 !important;
        color: #7f1d2d !important;
        -webkit-text-fill-color: #7f1d2d !important;
    }

    .health-filter-actions .health-filter-btn,
    .health-filter-actions .health-filter-btn *,
    button.health-filter-btn,
    .health-filter-form .health-filter-actions button.health-filter-btn,
    div.health-filter-actions button[type="submit"].health-filter-btn {
        color: white !important;
        -webkit-text-fill-color: white !important;
        fill: white !important;
        stroke: white !important;
    }

    button.health-filter-btn,
    button.health-filter-btn * {
        color: white !important;
        -webkit-text-fill-color: white !important;
    }

    .health-filter-form .health-filter-actions a.health-filter-btn-reset,
    .health-filter-form .health-filter-actions a.health-filter-btn-reset:visited,
    .health-filter-form .health-filter-actions a.health-filter-btn-reset *,
    div.health-filter-actions a.health-filter-btn-reset {
        color: #111827 !important;
        -webkit-text-fill-color: #111827 !important;
        fill: #111827 !important;
        stroke: #111827 !important;
    }

    .health-filter-form .health-filter-actions a.health-filter-btn-reset:hover,
    .health-filter-form .health-filter-actions a.health-filter-btn-reset:hover * {
        color: #70131B !important;
        -webkit-text-fill-color: #70131B !important;
    }

    .health-filter-btn::after {
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
        transition: transform .42s ease;
        z-index: -1;
    }

    .health-filter-btn:hover {
        transform: translateY(-1px);
        background: #facc15 !important;
        border-color: #facc15 !important;
        color: #7f1d2d !important;
        -webkit-text-fill-color: #7f1d2d !important;
        box-shadow:
            0 0 0 3px rgba(250, 204, 21, 0.18),
            0 14px 24px rgba(127, 29, 45, 0.16);
    }

    .health-filter-btn:hover * {
        color: #7f1d2d !important;
    }

    .health-filter-btn:hover::after {
        transform: translateX(135%);
    }

    .health-filter-btn-reset {
        background: #ffffff;
        border-color: #cbd5e1;
        color: #111827 !important;
        box-shadow:
            0 0 0 3px rgba(100, 116, 139, 0.08),
            0 10px 22px rgba(71, 85, 105, 0.12);
    }

    .health-filter-modal {
        position: fixed;
        inset: 0;
        display: none;
        align-items: center;
        justify-content: center;
        padding: 24px;
        background: rgba(15, 23, 42, 0.62);
        backdrop-filter: blur(6px);
        z-index: 1200;
    }

    .health-filter-modal.is-open {
        display: flex;
    }

    .health-filter-modal-card {
        width: min(560px, 100%);
        border-radius: 18px;
        background: #ffffff;
        border: 1px solid #fecaca;
        border-bottom: 4px solid #70131B;
        box-shadow: 0 24px 60px rgba(15, 23, 42, 0.28);
        overflow: visible;
        padding: 0;
    }

    .health-filter-modal-head {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 16px;
        padding: 18px 20px;
        background: linear-gradient(135deg, #7f1d1d, #b91c1c);
        color: #ffffff;
        border-radius: 18px 18px 0 0;
    }

    .health-filter-modal-head-main {
        display: flex;
        align-items: center;
        gap: 12px;
        min-width: 0;
    }

    .health-filter-modal-badge {
        width: 48px;
        height: 48px;
        border-radius: 10px;
        border: 1px solid rgba(255, 255, 255, 0.26);
        background: rgba(255, 255, 255, 0.10);
        color: #facc15;
        display: grid;
        place-items: center;
        flex: 0 0 auto;
    }

    .health-filter-modal-badge svg {
        width: 22px;
        height: 22px;
    }

    .health-filter-modal-title {
        margin: 0;
        font-size: 20px;
        font-weight: 900;
        color: #ffffff !important;
    }

    .health-filter-modal-copy {
        margin: 6px 0 0;
        color: rgba(255, 255, 255, 0.88) !important;
        font-size: 13px;
        line-height: 1.6;
    }

    .health-filter-modal-close {
        position: relative;
        overflow: hidden;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        width: 44px;
        min-width: 44px;
        height: 44px;
        min-height: 44px;
        flex: 0 0 44px;
        border: 1px solid rgba(255,255,255,0.24);
        border-radius: 999px;
        background: rgba(112, 19, 27, 0.45);
        color: #ffffff;
        cursor: pointer;
        transition: transform .18s ease, background .18s ease, color .18s ease, border-color .18s ease;
    }

    .health-filter-modal-close::after {
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

    .health-filter-modal-close:hover,
    .health-filter-modal-close:focus {
        background: #facc15;
        border-color: #facc15;
        color: #70131B;
        transform: translateY(-1px);
    }

    .health-filter-modal-close:hover::after,
    .health-filter-modal-close:focus::after {
        left: 128%;
    }

    .health-filter-modal-close svg {
        width: 18px;
        height: 18px;
        position: relative;
        z-index: 1;
    }

    .health-summary-label {
        font-size: 17px;
        letter-spacing: 0.5px;
        display: inline-flex;
        flex-direction: column;
        line-height: 1.15;
    }

    .health-summary-value {
        color: #70131B;
        font-size: 17px;
    }

    .health-summary-row {
        display: flex;
        align-items: center;
        justify-content: center;
        flex-direction: column;
        gap: 0;
        width: 100%;
    }

    .health-table-head {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 16px;
        margin-bottom: 12px;
        flex-wrap: wrap;
    }

    .health-table-title {
        margin: 0;
        font-size: 18px;
        font-weight: 900;
        color: #70131B;
        letter-spacing: 0.01em;
    }

    .health-highlight-row {
        position: relative;
        background: linear-gradient(180deg, rgba(243, 232, 255, 0.98), rgba(237, 233, 254, 0.98));
        box-shadow: inset 4px 0 0 #7c3aed;
        transition: background 0.3s ease, box-shadow 0.3s ease;
    }

    .health-row-clickable {
        cursor: pointer;
    }

    .health-row-clickable:hover td {
        background: rgba(220, 252, 231, 0.58);
    }

    .health-row-clickable td {
        transition: background 0.16s ease;
    }

    .health-highlight-row td {
        background: transparent;
    }

    .verification-doc-grid {
        display: grid;
        grid-template-columns: repeat(4, minmax(0, 1fr));
        gap: 10px;
    }
    .verification-doc-card {
        min-height: 252px;
        border: 1px solid rgba(112, 19, 27, 0.14);
        border-radius: 14px;
        background: linear-gradient(180deg, #ffffff 0%, #fff7f7 100%);
        padding: 10px;
        text-decoration: none;
        display: flex;
        flex-direction: column;
        justify-content: space-between;
        gap: 8px;
        transition: transform 0.18s ease, border-color 0.18s ease, box-shadow 0.18s ease;
    }
    .verification-doc-card:hover {
        transform: translateY(-2px);
        border-color: rgba(250, 204, 21, 0.75);
        box-shadow: 0 14px 24px rgba(112, 19, 27, 0.12);
    }
    .verification-doc-card strong {
        display: block;
        color: #70131B;
        font-size: 12px;
        margin-top: 8px;
    }
    .verification-doc-card span {
        color: #475569;
        font-size: 12px;
        font-weight: 900;
    }
    .verification-doc-card svg {
        width: 22px;
        height: 22px;
        color: #70131B;
        margin-bottom: 10px;
    }
    .verification-doc-card.health-form-doc-card {
        position: relative;
        overflow: hidden;
        border-color: rgba(250, 204, 21, 0.82);
        background: linear-gradient(180deg, #ffffff 0%, #fffafa 100%);
    }
    .health-form-doc-preview {
        position: relative;
        flex: 1 1 auto;
        min-height: 190px;
        border-radius: 12px;
        overflow: hidden;
        display: flex;
        align-items: center;
        justify-content: center;
        text-align: center;
        isolation: isolate;
    }
    .health-form-doc-preview::before {
        content: "";
        position: absolute;
        inset: 8px;
        background: url('{{ asset('images/pup_logo_print.jpg') }}') center / contain no-repeat;
        opacity: 0.12;
        filter: blur(1.2px);
        z-index: -1;
    }
    .health-form-doc-title {
        display: flex;
        flex-direction: column;
        align-items: center;
        justify-content: center;
        gap: 4px;
        color: #70131B;
        font-size: 16px;
        font-weight: 900;
        line-height: 1.02;
        text-transform: uppercase;
        letter-spacing: 0.04em;
    }
    .verification-doc-card.health-form-doc-card strong {
        margin-top: 8px;
        text-align: left;
    }
    .verification-doc-card.health-form-doc-card > span:last-child {
        text-align: left;
    }
    .verification-doc-preview {
        width: 100%;
        height: 190px;
        border-radius: 10px;
        border: 1px solid rgba(112, 19, 27, 0.12);
        background: #ffffff;
        overflow: hidden;
        display: flex;
        align-items: center;
        justify-content: center;
    }
    .verification-doc-preview img,
    .verification-doc-preview iframe {
        width: 100%;
        height: 100%;
        border: 0;
        object-fit: cover;
        background: #ffffff;
    }
    .verification-doc-card:not(a) {
        justify-content: center;
    }
    .verification-doc-card:not(a) strong,
    .verification-doc-card:not(a) span {
        text-align: center;
    }
    .verify-screening-grid {
        display: grid;
        gap: 12px;
    }
    .verify-screening-field label {
        display: block;
        margin-bottom: 5px;
        color: #64748b;
        font-size: 11px;
        font-weight: 900;
        letter-spacing: 0.06em;
        text-transform: uppercase;
    }
    .verify-screening-control {
        width: 100%;
        min-height: 42px;
        border: 1px solid #cbd5e1;
        border-radius: 12px;
        background: #ffffff;
        color: #111827;
        padding: 10px 12px;
        font-weight: 700;
    }

    select.verify-screening-control {
        appearance: none;
        background-image:
            linear-gradient(45deg, transparent 50%, #70131B 50%),
            linear-gradient(135deg, #70131B 50%, transparent 50%);
        background-position:
            calc(100% - 18px) 50%,
            calc(100% - 12px) 50%;
        background-size: 6px 6px, 6px 6px;
        background-repeat: no-repeat;
        padding-right: 38px;
    }

    .verify-screening-control:focus {
        outline: none;
        border-color: #facc15;
        box-shadow: 0 0 0 4px rgba(250, 204, 21, 0.18);
    }

    .verify-other-reason-field {
        display: none;
        margin-top: 10px;
    }

    .verify-other-reason-field.is-open {
        display: block;
    }
    .verify-check-row {
        display: flex;
        align-items: flex-start;
        gap: 9px;
        padding: 10px 12px;
        border: 1px solid #e2e8f0;
        border-radius: 12px;
        background: #f8fafc;
        color: #111827;
        font-size: 13px;
        font-weight: 800;
    }
    .verify-check-row input {
        margin-top: 2px;
        accent-color: #70131B;
    }
    .verify-approval-btn-pending {
        background: #fff7ed;
        border-color: #fdba74;
        color: #9a3412;
    }

    @keyframes healthHighlightPulse {
        0%, 100% {
            box-shadow: inset 4px 0 0 #7c3aed, 0 0 0 rgba(124, 58, 237, 0);
        }
        50% {
            box-shadow: inset 4px 0 0 #7c3aed, 0 0 0 6px rgba(124, 58, 237, 0.12);
        }
    }

    html[data-theme="dark"] .health-records-overview {
        background: rgba(15, 23, 42, 0.96);
        border-color: rgba(250, 204, 21, 0.18);
        box-shadow: 0 18px 38px rgba(0, 0, 0, .28);
    }

    html[data-theme="dark"] .health-records-overview-head {
        border-bottom-color: rgba(250, 204, 21, 0.12);
        background: linear-gradient(135deg, rgba(112, 19, 27, .34), rgba(15, 23, 42, .88));
    }

    html[data-theme="dark"] .health-records-title-icon,
    html[data-theme="dark"] .health-records-last-updated-icon {
        background: rgba(250, 204, 21, .12);
        color: #facc15;
    }

    html[data-theme="dark"] .health-records-title-copy h2,
    html[data-theme="dark"] .health-records-last-updated strong {
        color: #f8fafc;
    }

    html[data-theme="dark"] .health-records-title-copy p,
    html[data-theme="dark"] .health-records-last-updated span {
        color: #cbd5e1;
    }

    html[data-theme="dark"] .health-records-overview-search {
        background: rgba(15, 23, 42, .92);
    }

    html[data-theme="dark"] .health-records-overview-search .health-records-search-wrap {
        background: rgba(17, 24, 39, .94);
        border-color: rgba(250, 204, 21, .20);
    }

    html[data-theme="dark"] .health-records-overview-search .health-records-search {
        color: #f8fafc !important;
    }

    html[data-theme="dark"] .health-records-overview-search .health-records-search::placeholder {
        color: #fecdd3 !important;
    }

    html[data-theme="dark"] .health-records-search-submit {
        background: rgba(127, 29, 45, .96);
        border-color: rgba(250, 204, 21, .22);
        color: #ffffff;
        box-shadow: 0 12px 24px rgba(0, 0, 0, .22);
    }

    html[data-theme="dark"] .health-records-search-submit:hover,
    html[data-theme="dark"] .health-records-search-submit:focus-visible {
        background: #facc15;
        border-color: #facc15;
        color: #7f1d2d;
    }

    html[data-theme="dark"] .health-summary-modern-card {
        background: rgba(17, 24, 39, .94);
        border-color: rgba(148, 163, 184, .18);
    }

    html[data-theme="dark"] .health-summary-modern-card.is-approved {
        background: rgba(20, 83, 45, .18);
        border-color: rgba(74, 222, 128, .42);
    }

    html[data-theme="dark"] .health-summary-modern-card.is-condition {
        background: rgba(127, 29, 29, .20);
        border-color: rgba(248, 113, 113, .42);
    }

    html[data-theme="dark"] .health-summary-modern-card.is-pending,
    html[data-theme="dark"] .health-summary-modern-card.is-compliance {
        background: linear-gradient(135deg, #70131B, #8f1727);
        border-color: rgba(250, 204, 21, .62);
    }

    html[data-theme="dark"] .health-summary-modern-card:not(.is-pending):not(.is-compliance) .health-summary-modern-copy,
    html[data-theme="dark"] .health-summary-modern-card:not(.is-pending):not(.is-compliance) .health-summary-modern-label,
    html[data-theme="dark"] .health-summary-modern-card:not(.is-pending):not(.is-compliance) .health-summary-modern-count {
        color: #f8fafc;
    }

    html[data-theme="dark"] .health-summary-modern-card:not(.is-pending):not(.is-compliance) .health-summary-modern-note {
        color: #cbd5e1;
    }

    html[data-theme="dark"] .health-summary-card {
        background: rgba(15, 23, 42, .96);
        border-color: rgba(250, 204, 21, .16);
        box-shadow: 0 18px 34px rgba(0, 0, 0, .24);
    }

    html[data-theme="dark"] .health-table-head {
        border-bottom-color: rgba(250, 204, 21, .18);
    }

    html[data-theme="dark"] #healthTable {
        background: rgba(15, 23, 42, .96);
        color: #f8fafc;
    }

    html[data-theme="dark"] #healthTable thead th {
        background: rgba(112, 19, 27, .58);
        color: #f8fafc !important;
        border-bottom-color: rgba(250, 204, 21, .22);
    }

    html[data-theme="dark"] #healthTable tbody tr {
        background: rgba(15, 23, 42, .92);
        border-bottom-color: rgba(148, 163, 184, .14);
    }

    html[data-theme="dark"] #healthTable tbody tr:nth-child(even) {
        background: rgba(17, 24, 39, .88);
    }

    html[data-theme="dark"] #healthTable tbody tr.health-row-clickable:hover td,
    html[data-theme="dark"] #healthTable tbody tr:hover td {
        background: rgba(250, 204, 21, .08) !important;
    }

    html[data-theme="dark"] #healthTable td {
        color: #f8fafc !important;
        border-bottom-color: rgba(148, 163, 184, .14);
    }

    html[data-theme="dark"] .readonly-modal-pagination {
        background: rgba(15, 23, 42, .96);
        border-color: rgba(148, 163, 184, .18);
        box-shadow: 0 12px 26px rgba(0, 0, 0, .24);
    }

    html[data-theme="dark"] .readonly-pagination-summary {
        color: #cbd5e1;
    }

    html[data-theme="dark"] .readonly-pagination-btn,
    html[data-theme="dark"] .readonly-pagination-per-page-select {
        background-color: rgba(17, 24, 39, .94);
        border-color: rgba(148, 163, 184, .24);
        color: #e2e8f0;
    }

    html[data-theme="dark"] .readonly-pagination-btn.is-active {
        background: #7f0010;
        border-color: #facc15;
        color: #ffffff !important;
    }

    html[data-theme="dark"] .readonly-pagination-btn:hover:not(:disabled) {
        background: #facc15;
        border-color: #facc15;
        color: #70131B;
    }

    html[data-theme="dark"] .readonly-pagination-per-page-select {
        background-image:
            linear-gradient(135deg, transparent 50%, #facc15 50%),
            linear-gradient(45deg, #facc15 50%, transparent 50%);
    }

    html[data-theme="dark"] .health-records-title,
    html[data-theme="dark"] .health-table-title,
    html[data-theme="dark"] .text-muted.health-summary-label,
    html[data-theme="dark"] .summary-item .health-summary-label span,
    html[data-theme="dark"] .health-filter-field label,
    html[data-theme="dark"] .health-records-search,
    html[data-theme="dark"] .health-records-search::placeholder,
    html[data-theme="dark"] .health-filter-select,
    html[data-theme="dark"] .health-summary-label,
    html[data-theme="dark"] .health-summary-value,
    html[data-theme="dark"] .summary-item h3,
    html[data-theme="dark"] .summary-item .text-danger {
        color: #ffffff !important;
    }

    html[data-theme="dark"] .health-summary-metric-card .health-summary-metric-label,
    html[data-theme="dark"] .health-summary-metric-card .health-summary-metric-label span,
    html[data-theme="dark"] .health-summary-metric-card .health-summary-metric-count {
        color: #ffffff !important;
    }

    html[data-theme="dark"] .health-summary-metric-card.is-approved {
        background: rgba(37, 99, 235, 0.16) !important;
        border: 1.5px solid #60a5fa !important;
    }

    html[data-theme="dark"] .health-summary-metric-card.is-condition {
        background: rgba(190, 18, 60, 0.16) !important;
        border: 1.5px solid #fb7185 !important;
    }

    html[data-theme="dark"] .health-summary-metric-card.is-approved .health-summary-metric-label,
    html[data-theme="dark"] .health-summary-metric-card.is-approved .health-summary-metric-label span {
        color: #bfdbfe !important;
    }

    html[data-theme="dark"] .health-summary-metric-card.is-condition .health-summary-metric-label,
    html[data-theme="dark"] .health-summary-metric-card.is-condition .health-summary-metric-label span {
        color: #fecdd3 !important;
    }

    html[data-theme="dark"] .health-summary-metric-card.is-approved .health-summary-metric-count {
        color: #dbeafe !important;
    }

    html[data-theme="dark"] .health-summary-metric-card.is-condition .health-summary-metric-count {
        color: #ffe4e6 !important;
    }

    html[data-theme="dark"] .health-records-search-toggle {
        background: rgba(17, 24, 39, 0.96);
        border-color: rgba(250, 204, 21, 0.16);
        color: #facc15;
    }

    html[data-theme="dark"] .health-records-search-toggle:hover,
    html[data-theme="dark"] .health-records-search-toggle:focus {
        background: rgba(250, 204, 21, 0.18);
        border-color: #facc15;
        color: #111827;
    }

    html[data-theme="dark"] .health-filter-toggle {
        background: rgba(17, 24, 39, 0.96);
        border-color: rgba(250, 204, 21, 0.16);
        color: #facc15;
    }

    html[data-theme="dark"] .health-filter-toggle:hover,
    html[data-theme="dark"] .health-filter-toggle.is-open {
        background: rgba(250, 204, 21, 0.18);
        border-color: #facc15;
        color: #111827 !important;
    }
    html[data-theme="dark"] .card .text-muted,
    html[data-theme="dark"] .card th,
    html[data-theme="dark"] .card td,
    html[data-theme="dark"] .card td *,
    html[data-theme="dark"] .student-name {
        color: #ffffff !important;
    }

    html[data-theme="dark"] .health-records-search::placeholder {
        color: #fecdd3 !important;
    }

    html[data-theme="dark"] .health-records-title {
        border-color: rgba(250, 204, 21, 0.30);
        background: transparent;
        box-shadow: none;
    }

    html[data-theme="dark"] .health-records-search,
    html[data-theme="dark"] .health-filter-select {
        background: rgba(18, 8, 12, 0.86);
        border-color: rgba(250, 204, 21, 0.28);
        border-bottom-color: #70131B !important;
        box-shadow:
            0 0 0 2px rgba(250, 204, 21, 0.06),
            0 10px 20px rgba(0, 0, 0, 0.20);
    }

    html[data-theme="dark"] .health-filter-select-wrap::after {
        border-color: #facc15;
    }

    html[data-theme="dark"] .health-filter-custom-trigger {
        background: rgba(18, 8, 12, 0.86);
        border-color: rgba(250, 204, 21, 0.28);
        color: #ffffff;
        box-shadow:
            0 0 0 2px rgba(250, 204, 21, 0.06),
            0 10px 20px rgba(0, 0, 0, 0.20);
    }

    html[data-theme="dark"] .health-filter-custom-trigger::after {
        border-color: #facc15;
    }

    html[data-theme="dark"] .health-filter-custom-menu {
        background: #111827;
        border-color: rgba(250, 204, 21, 0.18);
        box-shadow: 0 18px 32px rgba(0, 0, 0, 0.34);
    }

    html[data-theme="dark"] .health-filter-custom-option {
        background: #111827;
        color: #ffffff;
    }

    html[data-theme="dark"] .health-filter-custom-option:hover,
    html[data-theme="dark"] .health-filter-custom-option:focus {
        background: #8f0012;
        color: #ffffff;
    }

    html[data-theme="dark"] .health-filter-custom-option.is-selected {
        background: #8f0012;
        color: #facc15;
    }

    html[data-theme="dark"] .health-records-search-toggle {
        background: linear-gradient(135deg, #70131B, #8f2230) !important;
        border-color: rgba(250, 204, 21, 0.28) !important;
        box-shadow:
            0 0 0 3px rgba(112, 19, 27, 0.16),
            0 12px 22px rgba(0, 0, 0, 0.24) !important;
    }

    html[data-theme="dark"] .health-records-toolbar {
        border-color: rgba(250, 204, 21, 0.24);
        background: linear-gradient(135deg, rgba(112, 19, 27, 0.68) 0%, rgba(86, 16, 26, 0.64) 48%, rgba(44, 14, 18, 0.72) 100%);
        box-shadow:
            0 0 0 2px rgba(250, 204, 21, 0.07),
            0 16px 28px rgba(0, 0, 0, 0.22);
    }

    html[data-theme="dark"] .health-medical-launch-btn {
        box-shadow:
            0 0 0 3px rgba(112, 19, 27, 0.16),
            0 12px 22px rgba(0, 0, 0, 0.24);
    }

    html[data-theme="dark"] .health-medical-launch-btn:hover,
    html[data-theme="dark"] .health-medical-launch-btn:focus {
        box-shadow:
            0 0 0 3px rgba(250, 204, 21, 0.18),
            0 14px 24px rgba(0, 0, 0, 0.22);
    }

    html[data-theme="dark"] .btn-view {
        background: linear-gradient(135deg, rgba(127, 29, 45, 0.22), rgba(148, 28, 57, 0.18));
        border-color: rgba(250, 204, 21, 0.18);
        color: #ffffff;
    }

    html[data-theme="dark"] .btn-view:hover {
        border-color: rgba(250, 204, 21, 0.4);
        box-shadow: 0 14px 24px rgba(0, 0, 0, 0.22);
    }

    html[data-theme="dark"] .btn-sign {
        color: #ffffff;
    }

    html[data-theme="dark"] .btn-signed,
    html[data-theme="dark"] .btn-readonly {
        background: linear-gradient(135deg, rgba(71, 85, 105, 0.78), rgba(51, 65, 85, 0.92));
        border-color: rgba(148, 163, 184, 0.28);
        color: #e2e8f0;
    }

    html[data-theme="dark"] .health-issued-badge {
        background: linear-gradient(135deg, rgba(20, 83, 45, 0.96), rgba(21, 128, 61, 0.84));
        border-color: rgba(74, 222, 128, 0.30);
        color: #ecfdf5;
        box-shadow:
            0 0 0 3px rgba(34, 197, 94, 0.10),
            0 12px 22px rgba(0, 0, 0, 0.24);
    }

    html[data-theme="dark"] .health-filter-modal-card {
        background: rgba(15, 23, 42, 0.98);
        border-color: rgba(248, 113, 113, 0.45);
        border-bottom-color: #70131B;
        box-shadow: 0 18px 32px rgba(0, 0, 0, 0.24);
    }

    html[data-theme="dark"] .health-filter-modal-head {
        background: linear-gradient(135deg, #7f1d1d, #b91c1c);
    }

    html[data-theme="dark"] .health-filter-modal-title,
    html[data-theme="dark"] .health-filter-modal-copy {
        color: #ffffff !important;
    }

    html[data-theme="dark"] .health-filter-modal-title,
    html[data-theme="dark"] .health-filter-modal-close {
        color: #ffffff;
    }

    html[data-theme="dark"] .health-filter-modal-copy {
        color: #cbd5e1;
    }

    html[data-theme="dark"] .health-filter-modal-close {
        background: rgba(112, 19, 27, 0.45);
        border-color: rgba(255, 255, 255, 0.24);
    }

    html[data-theme="dark"] .health-filter-modal-close:hover,
    html[data-theme="dark"] .health-filter-modal-close:focus {
        background: #facc15;
        border-color: #facc15;
        color: #70131B;
    }

    html[data-theme="dark"] .health-summary-card::before {
        background: #facc15;
    }

    html[data-theme="dark"] .summary-item .card {
        background: rgba(15, 23, 42, 0.96);
        border-color: rgba(148, 163, 184, 0.14);
    }

    html[data-theme="dark"] .health-highlight-row {
        background: linear-gradient(180deg, rgba(76, 29, 149, 0.34), rgba(91, 33, 182, 0.28));
        box-shadow: inset 4px 0 0 #a855f7;
    }

    html[data-theme="dark"] .health-row-clickable:hover td {
        background: rgba(20, 83, 45, 0.34);
    }
    .verify-approval-modal {
        position: fixed;
        inset: 0;
        display: none;
        align-items: center;
        justify-content: center;
        padding: 16px;
        background: rgba(15, 23, 42, 0.55);
        backdrop-filter: blur(6px);
        z-index: 1300;
    }

    .verify-approval-modal.is-open {
        display: flex;
    }

    .verify-approval-modal-card {
        width: min(980px, 100%);
        max-height: min(88vh, 760px);
        display: flex;
        flex-direction: column;
        border-radius: 20px;
        background: #ffffff;
        border: 1px solid rgba(112, 19, 27, 0.24);
        box-shadow: 0 28px 80px rgba(15, 23, 42, 0.30);
        overflow: hidden;
    }

    .verify-approval-modal-head {
        display: flex;
        align-items: flex-start;
        justify-content: space-between;
        gap: 12px;
        padding: 22px 24px;
        border-bottom: 1px solid rgba(255, 255, 255, 0.16);
        background: linear-gradient(135deg, #9f1d24 0%, #b91c1c 100%);
        color: #ffffff;
    }

    .verify-approval-modal-head-main {
        display: flex;
        align-items: center;
        gap: 16px;
        min-width: 0;
    }

    .verify-approval-modal-badge {
        width: 48px;
        height: 48px;
        flex: 0 0 48px;
        border-radius: 12px;
        border: 1px solid rgba(255, 255, 255, 0.30);
        background: rgba(255, 255, 255, 0.16);
        color: #ffffff;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        font-size: 13px;
        font-weight: 900;
        letter-spacing: 0.04em;
    }

    .verify-approval-modal-title {
        margin: 0;
        font-size: 20px;
        font-weight: 900;
        color: #ffffff !important;
    }

    .verify-approval-modal-copy {
        margin: 4px 0 0;
        font-size: 13px;
        color: #ffffff !important;
    }

    .verify-approval-modal-close {
        width: 44px;
        height: 44px;
        border-radius: 999px;
        border: 1px solid rgba(255, 255, 255, 0.24);
        background: rgba(255, 255, 255, 0.14);
        color: #ffffff;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        cursor: pointer;
        position: relative;
        overflow: hidden;
        transition: all 0.3s ease;
    }

    .verify-approval-modal-close::before {
        content: '';
        position: absolute;
        inset: 0;
        background: linear-gradient(90deg,
            rgba(255, 248, 196, 0) 0%,
            rgba(255, 239, 181, 0.14) 22%,
            rgba(255, 239, 181, 0.52) 48%,
            rgba(255, 239, 181, 0.14) 72%,
            rgba(255, 248, 196, 0) 100%);
        transform: translateX(-135%);
        transition: transform 1.5s ease;
        border-radius: 999px;
        z-index: 0;
    }

    .verify-approval-modal-close:hover {
        color: #70131B;
        background: #facc15;
        border-color: #facc15;
    }

    .verify-approval-modal-close:hover::before {
        transform: translateX(135%);
    }

    .verify-approval-modal-close svg {
        width: 20px;
        height: 20px;
        stroke-width: 2.5;
        position: relative;
        z-index: 1;
    }

    .verify-approval-body {
        padding: 22px 24px 24px;
        display: grid;
        gap: 14px;
        overflow-y: auto;
        background:
            radial-gradient(circle at top right, rgba(250, 204, 21, 0.10), transparent 28%),
            #ffffff;
    }

    .verify-approval-student {
        display: grid;
        grid-template-columns: repeat(3, minmax(0, 1fr));
        gap: 10px;
    }

    .verify-approval-meta {
        border: 1px solid #e2e8f0;
        background: #f8fafc;
        border-radius: 10px;
        padding: 10px 12px;
    }

    .verify-approval-meta-k {
        font-size: 10px;
        text-transform: uppercase;
        letter-spacing: 0.06em;
        color: #64748b;
        font-weight: 700;
        margin-bottom: 4px;
    }

    .verify-approval-meta-v {
        font-size: 14px;
        font-weight: 700;
        color: #0f172a;
        word-break: break-word;
    }

    .verify-approval-doc-wrap {
        border: 1px solid #e2e8f0;
        border-radius: 12px;
        padding: 12px;
        background: #ffffff;
    }

    .verify-approval-doc-title,
    .verify-condition-title {
        margin: 0 0 8px;
        font-size: 13px;
        font-weight: 800;
        color: #1e293b;
    }

    .verify-condition-wrap {
        border: 1px solid #e2e8f0;
        border-radius: 12px;
        padding: 12px;
        background: #ffffff;
    }

    .verify-condition-grid {
        display: grid;
        grid-template-columns: repeat(2, minmax(0, 1fr));
        gap: 10px;
    }

    .verify-condition-item {
        min-height: 58px;
        border: 1px solid rgba(112, 19, 27, 0.10);
        border-radius: 12px;
        background: #fffaf7;
        padding: 10px 12px;
    }

    .verify-condition-item span {
        display: block;
        color: #64748b;
        font-size: 10px;
        font-weight: 900;
        letter-spacing: .06em;
        text-transform: uppercase;
        margin-bottom: 4px;
    }

    .verify-condition-item strong {
        display: block;
        color: #111827;
        font-size: 13px;
        font-weight: 900;
        word-break: break-word;
    }

    .verify-approval-doc-frame {
        height: 340px;
        border: 1px solid #e2e8f0;
        border-radius: 10px;
        overflow: hidden;
        background: #f8fafc;
    }

    .verify-approval-doc-frame iframe {
        width: 100%;
        height: 100%;
        border: 0;
        background: #ffffff;
    }

    .verify-approval-doc-missing {
        border: 1px dashed #cbd5e1;
        background: #f8fafc;
        color: #64748b;
        border-radius: 10px;
        padding: 12px;
        font-size: 12px;
        font-weight: 600;
    }

    .verify-approval-actions {
        display: flex;
        justify-content: flex-end;
        gap: 10px;
        margin-top: 14px;
        padding-top: 14px;
        border-top: 1px solid rgba(112, 19, 27, 0.10);
    }

    .verify-approval-actions.is-hidden {
        display: none;
    }

    .verify-approval-review-form {
        margin-top: 18px;
        padding: 16px;
        border: 1px solid rgba(112, 19, 27, 0.14);
        border-radius: 18px;
        background: linear-gradient(180deg, #ffffff 0%, #fffafa 100%);
    }

    .verify-review-grid,
    .verify-pending-reason-row {
        display: grid;
        grid-template-columns: repeat(2, minmax(0, 1fr));
        gap: 12px;
    }

    .verify-select-field,
    .verify-text-field,
    .verify-textarea-field {
        display: flex;
        flex-direction: column;
        gap: 7px;
        font-size: 12px;
        font-weight: 800;
        color: #1f2937;
    }

    .verify-select-field select,
    .verify-text-field input,
    .verify-textarea-field textarea {
        width: 100%;
        border: 1px solid rgba(148, 163, 184, 0.55);
        border-radius: 12px;
        padding: 10px 12px;
        background: #ffffff;
        color: #111827;
        font-size: 13px;
        font-weight: 700;
        outline: none;
    }

    .verify-textarea-field {
        margin-top: 12px;
    }

    .verify-textarea-field textarea {
        min-height: 84px;
        resize: vertical;
    }

    .verify-pending-reason-row {
        margin-top: 12px;
    }

    .verify-text-field {
        display: none;
    }

    .verify-text-field.is-open {
        display: flex;
    }

    .verify-approval-btn svg {
        width: 16px;
        height: 16px;
        flex: 0 0 auto;
    }

    .readonly-review-routing-actions {
        display: grid;
        grid-template-columns: repeat(2, minmax(0, 1fr));
        gap: 8px;
        margin-top: 8px;
    }

    .readonly-final-review-field {
        grid-column: 1 / -1;
        order: 50;
    }

    .readonly-final-review-field form {
        margin: 0;
    }

    .readonly-final-review-btn {
        width: 100%;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        gap: 8px;
        border: 1px solid rgba(112, 19, 27, 0.28);
        border-radius: 12px;
        background: #70131B;
        color: #facc15;
        font-size: 12px;
        font-weight: 900;
        padding: 11px 12px;
        cursor: pointer;
        transition: transform 0.18s ease, background 0.18s ease, color 0.18s ease, border-color 0.18s ease;
    }

    .readonly-final-review-btn svg {
        width: 16px;
        height: 16px;
    }

    .readonly-final-review-btn:hover {
        transform: translateY(-1px);
        background: #facc15;
        color: #70131B;
        border-color: #f59e0b;
    }

    @media (max-width: 640px) {
        .readonly-review-routing-actions {
            grid-template-columns: 1fr;
        }
    }

    .readonly-resubmission-field {
        grid-column: 1 / -1;
    }

    .readonly-resubmission-form {
        margin: 0;
    }

    .readonly-resubmission-box {
        display: block;
    }

    .readonly-resubmission-summary {
        width: 100%;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        gap: 8px;
        padding: 11px 12px;
        border: 1px solid rgba(202, 138, 4, 0.34);
        border-radius: 12px;
        background: #fffbeb;
        color: #92400e;
        font-size: 12px;
        font-weight: 900;
        cursor: pointer;
        list-style: none;
    }

    .readonly-resubmission-summary::-webkit-details-marker {
        display: none;
    }

    .readonly-resubmission-summary svg {
        width: 16px;
        height: 16px;
    }

    .readonly-resubmission-panel {
        margin-top: 12px;
        display: grid;
        gap: 10px;
    }

    .readonly-resubmission-help {
        margin: 0;
        color: #64748b;
        font-size: 12px;
        line-height: 1.45;
    }

    .readonly-resubmission-options {
        display: grid;
        grid-template-columns: repeat(2, minmax(0, 1fr));
        gap: 8px;
    }

    .readonly-resubmission-option {
        display: flex;
        align-items: center;
        gap: 8px;
        padding: 10px;
        border: 1px solid #e2e8f0;
        border-radius: 10px;
        background: #ffffff;
        color: #334155;
        font-size: 12px;
        font-weight: 800;
    }

    .readonly-resubmission-submit {
        justify-self: start;
        border: 0;
        border-radius: 999px;
        background: #70131B;
        color: #facc15;
        padding: 10px 16px;
        font-size: 12px;
        font-weight: 900;
        cursor: pointer;
        transition: transform 0.18s ease, background 0.18s ease, color 0.18s ease;
    }

    .readonly-resubmission-submit:hover {
        transform: translateY(-1px);
        background: #facc15;
        color: #70131B;
    }

    .readonly-doc-preview-field {
        grid-column: 1 / -1;
    }

    .readonly-doc-preview-shell {
        margin-top: 8px;
    }

    .readonly-doc-preview-list {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(150px, 1fr));
        gap: 10px;
    }

    .readonly-doc-preview-btn {
        width: 100%;
        min-height: 174px;
        display: grid;
        grid-template-rows: 108px auto auto;
        gap: 7px;
        padding: 10px;
        border: 1px solid rgba(112, 19, 27, 0.14);
        border-radius: 12px;
        background: #fffafa;
        color: #334155;
        font-size: 12px;
        font-weight: 900;
        text-align: left;
        cursor: pointer;
        text-decoration: none;
        transition: border-color 0.18s ease, background 0.18s ease, color 0.18s ease, transform 0.18s ease;
    }

    .readonly-doc-preview-btn:hover {
        transform: translateY(-1px);
        border-color: rgba(112, 19, 27, 0.36);
        background: #fff7ed;
        color: #70131B;
    }

    .readonly-doc-preview-thumb {
        width: 100%;
        height: 108px;
        display: grid;
        place-items: center;
        border-radius: 9px;
        overflow: hidden;
        background:
            radial-gradient(circle at 50% 42%, rgba(112, 19, 27, .08), transparent 44%),
            #fff1f2;
    }

    .readonly-doc-preview-thumb img {
        width: 100%;
        height: 100%;
        object-fit: cover;
        display: block;
    }

    .readonly-doc-preview-thumb svg {
        width: 34px;
        height: 34px;
        color: #8A1020;
    }

    .readonly-doc-preview-btn span,
    .readonly-doc-preview-btn small {
        display: block;
    }

    .readonly-doc-preview-btn span {
        color: #70131B;
        line-height: 1.25;
    }

    .readonly-doc-preview-btn small {
        color: #475569;
        font-size: 11px;
        font-weight: 800;
    }

    .readonly-doc-preview-empty {
        min-height: 52px;
        display: grid;
        place-items: center;
        padding: 16px;
        border: 1px dashed #cbd5e1;
        border-radius: 10px;
        color: #64748b;
        font-size: 12px;
        font-weight: 800;
        text-align: center;
    }

    .resubmission-progress-overlay {
        position: fixed;
        z-index: 1300;
        inset: 0;
        display: none;
        align-items: center;
        justify-content: center;
        padding: 20px;
        background: rgba(15, 23, 42, 0.58);
        backdrop-filter: blur(5px);
    }

    .resubmission-progress-overlay.is-open {
        display: flex;
    }

    .resubmission-progress-card {
        width: min(360px, calc(100vw - 32px));
        padding: 24px 20px;
        border: 1px solid rgba(250, 204, 21, 0.38);
        border-radius: 18px;
        background: #ffffff;
        text-align: center;
        box-shadow: 0 24px 54px rgba(15, 23, 42, 0.3);
        animation: resubmissionPop 0.34s cubic-bezier(.2, .9, .25, 1.2);
    }

    .resubmission-progress-mark {
        width: 68px;
        height: 68px;
        margin: 0 auto 12px;
        border-radius: 999px;
        display: grid;
        place-items: center;
        background: #70131B;
        color: #facc15;
        position: relative;
    }

    .resubmission-progress-mark::before {
        content: "";
        position: absolute;
        inset: 8px;
        border-radius: inherit;
        border: 3px solid rgba(250, 204, 21, 0.28);
        border-top-color: #facc15;
        animation: resubmissionSpin 0.82s linear infinite;
    }

    .resubmission-progress-mark svg {
        width: 28px;
        height: 28px;
        position: relative;
        z-index: 1;
    }

    .resubmission-progress-card strong {
        display: block;
        color: #70131B;
        font-size: 18px;
        font-weight: 900;
    }

    .resubmission-progress-card span {
        display: block;
        margin-top: 6px;
        color: #64748b;
        font-size: 12px;
        font-weight: 700;
        line-height: 1.45;
    }

    @keyframes resubmissionPop {
        from { opacity: 0; transform: scale(.82) translateY(12px); }
        to { opacity: 1; transform: scale(1) translateY(0); }
    }

    @keyframes resubmissionSpin {
        to { transform: rotate(360deg); }
    }

    @media (max-width: 760px) {
        .readonly-doc-preview-list {
            grid-template-columns: 1fr;
        }

        .readonly-resubmission-options {
            grid-template-columns: 1fr;
        }
    }

    .verify-approval-btn {
        border-radius: 999px;
        padding: 9px 16px;
        font-size: 12px;
        font-weight: 800;
        border: 1px solid transparent;
        cursor: pointer;
        position: relative;
        overflow: hidden;
        transition: transform 0.18s ease, box-shadow 0.18s ease, background 0.18s ease, border-color 0.18s ease;
    }

    .verify-approval-btn::before {
        content: '';
        position: absolute;
        inset: 0;
        width: 42%;
        transform: translateX(-140%) skewX(-18deg);
        background: linear-gradient(90deg, transparent, rgba(255, 255, 255, 0.45), transparent);
        transition: transform 0.42s ease;
    }

    .verify-approval-btn:hover::before {
        transform: translateX(260%) skewX(-18deg);
    }

    .verify-approval-btn:hover {
        transform: translateY(-1px);
    }

    .verify-approval-btn-cancel {
        background: linear-gradient(135deg, #f8fafc, #e2e8f0);
        color: #334155;
        border-color: #cbd5e1;
        box-shadow: inset 0 1px 0 rgba(255, 255, 255, 0.70);
    }

    .verify-approval-btn-cancel:hover {
        border-color: #94a3b8;
        box-shadow: 0 12px 22px rgba(15, 23, 42, 0.12);
    }

    .verify-approval-btn-approve {
        background: #70131B;
        color: #ffffff;
        border-color: #8f2230;
    }

    .verify-approval-btn-approve:disabled {
        background: #cbd5e1;
        color: #64748b;
        border-color: #cbd5e1;
        cursor: not-allowed;
    }

    .verify-approval-btn-resubmit {
        background: linear-gradient(135deg, #70131B 0%, #9f1d24 100%);
        color: #ffffff;
        border-color: rgba(250, 204, 21, 0.40);
        box-shadow:
            0 14px 26px rgba(112, 19, 27, 0.28),
            inset 0 1px 0 rgba(255, 255, 255, 0.16);
    }

    .verify-approval-btn-resubmit:hover {
        background: linear-gradient(135deg, #facc15 0%, #f59e0b 100%);
        color: #111827;
        border-color: #fde047;
        box-shadow:
            0 18px 30px rgba(245, 158, 11, 0.34),
            inset 0 1px 0 rgba(255, 255, 255, 0.55);
    }

    .verify-resubmission-panel {
        display: none;
        gap: 12px;
        padding: 14px;
        border: 1px solid #fecaca;
        border-radius: 14px;
        background: #fff7f7;
    }

    .verify-resubmission-panel.is-open {
        display: grid;
    }

    .verify-resubmission-help-note {
        margin: 0 0 8px;
        color: #64748b;
        font-size: 11px;
        font-weight: 700;
        line-height: 1.45;
    }

    .verify-resubmission-title {
        margin: 0;
        color: #7f1d2d;
        font-size: 12px;
        font-weight: 900;
        text-transform: uppercase;
        letter-spacing: .05em;
    }

    .verify-resubmission-options {
        display: grid;
        grid-template-columns: repeat(2, minmax(0, 1fr));
        gap: 10px;
    }

    .verify-resubmission-option {
        display: flex;
        align-items: center;
        gap: 9px;
        min-height: 42px;
        padding: 9px 11px;
        border: 1px solid #f3d7dd;
        border-radius: 12px;
        background: #ffffff;
        color: #111827;
        font-size: 12px;
        font-weight: 800;
    }

    html[data-theme="dark"] .verify-approval-modal-card {
        background: #0f172a;
        border-color: #334155;
    }

    html[data-theme="dark"] .verify-approval-modal-head {
        background: linear-gradient(135deg, #8f1d24 0%, #b91c1c 100%);
        border-color: rgba(255, 255, 255, 0.16);
    }

    html[data-theme="dark"] .verify-approval-body,
    html[data-theme="dark"] .verify-approval-body * {
        color: #e5e7eb !important;
    }

    html[data-theme="dark"] .verify-approval-modal-title,
    html[data-theme="dark"] .verify-approval-meta-v,
    html[data-theme="dark"] .verify-approval-doc-title {
        color: #f8fafc !important;
    }

    html[data-theme="dark"] .verify-approval-modal-copy,
    html[data-theme="dark"] .verify-approval-meta-k,
    html[data-theme="dark"] .verify-approval-doc-missing {
        color: #cbd5e1 !important;
    }

    html[data-theme="dark"] .verify-approval-modal-head .verify-approval-modal-title,
    html[data-theme="dark"] .verify-approval-modal-head .verify-approval-modal-copy {
        color: #ffffff !important;
    }

    html[data-theme="dark"] .verify-approval-modal-close {
        background: #1e293b;
        border-color: #475569;
        color: #f8fafc;
    }

    html[data-theme="dark"] .verify-approval-meta {
        background: #111827;
        border-color: #334155;
    }

    html[data-theme="dark"] .verify-approval-doc-wrap {
        background: #0f172a;
        border-color: #334155;
    }

    html[data-theme="dark"] .verify-condition-wrap {
        background: #0f172a;
        border-color: #334155;
    }

    html[data-theme="dark"] .verify-condition-item {
        background: #111827;
        border-color: rgba(250, 204, 21, 0.18);
    }

    html[data-theme="dark"] .verify-condition-item span {
        color: #cbd5e1 !important;
    }

    html[data-theme="dark"] .verify-condition-item strong {
        color: #ffffff !important;
    }

    html[data-theme="dark"] .verification-doc-card {
        background: linear-gradient(180deg, #111827 0%, #1f2937 100%);
        border-color: rgba(250, 204, 21, 0.18);
    }

    html[data-theme="dark"] .verification-doc-card strong,
    html[data-theme="dark"] .verification-doc-card svg {
        color: #facc15 !important;
    }

    html[data-theme="dark"] .verification-doc-card span {
        color: #cbd5e1 !important;
    }

    html[data-theme="dark"] .verify-approval-doc-frame {
        border-color: #334155;
        background: #111827;
    }

    html[data-theme="dark"] .verify-approval-doc-missing {
        border-color: #475569;
        background: #111827;
    }

    html[data-theme="dark"] .verify-approval-btn-cancel {
        background: #1e293b;
        border-color: #475569;
        color: #f8fafc !important;
    }

    html[data-theme="dark"] .verify-approval-btn-approve {
        background: #70131B;
        border-color: #8f2230;
        color: #ffffff !important;
    }

    html[data-theme="dark"] .verify-approval-btn-approve:disabled {
        background: #334155;
        border-color: #475569;
        color: #cbd5e1 !important;
    }

    @media (max-width: 980px) {
        .health-records-toolbar {
            flex-direction: column;
            align-items: stretch;
            border-radius: 24px;
        }

        .verification-doc-grid {
            grid-template-columns: repeat(2, minmax(0, 1fr));
        }

        .health-records-toolbar-actions,
        .health-filter-shell {
            justify-content: flex-start;
            margin-left: 0;
            align-items: stretch;
        }

        .health-records-toolbar-actions {
            width: 100%;
        }

        .health-medical-launch-btn {
            width: 100%;
            justify-content: center;
        }

        .health-records-search-shell {
            width: 100%;
        }

        .health-records-search-wrap,
        .health-records-search-shell.is-open .health-records-search-wrap {
            width: 100%;
            flex: 1 1 100%;
        }

        .health-records-search-shell:not(.is-open) .health-records-search-wrap {
            width: 0;
            flex-basis: 0;
        }

        .health-records-search {
            width: 100%;
        }

        .verify-approval-student {
            grid-template-columns: 1fr;
        }
        .health-filter-form {
            grid-template-columns: 1fr;
        }
        .health-filter-actions {
            justify-content: stretch;
        }
        .health-filter-actions .health-filter-btn {
            flex: 1 1 0;
        }
        .verification-doc-grid {
            grid-template-columns: 1fr;
        }

        .summary-container {
            grid-template-columns: 1fr;
        }

    }

    .hr-modal-backdrop {
        display: none;
        position: fixed;
        inset: 0;
        z-index: 1300;
        align-items: center;
        justify-content: center;
        padding: clamp(12px, 2vw, 28px);
        background: rgba(15, 23, 42, 0.52);
        backdrop-filter: blur(10px);
        -webkit-backdrop-filter: blur(10px);
    }
    .hr-modal-backdrop.show { display: flex; }
    .hr-modal-shell {
        width: min(520px, 100%);
        max-height: calc(100dvh - clamp(24px, 4vw, 56px));
        border-radius: 24px;
        overflow: hidden;
        display: flex;
        flex-direction: column;
        background: rgba(255,255,255,0.96);
        border-left: 1px solid rgba(112,19,27,0.12);
        border-right: 1px solid rgba(112,19,27,0.12);
        border-top: 4px solid #facc15;
        border-bottom: 4px solid #70131B;
        box-shadow: 0 26px 60px rgba(15,23,42,0.22);
    }
    .hr-modal-shell.hr-ma-shell { width: min(880px, 100%); }
    .hr-modal-head {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 12px;
        padding: clamp(12px,1.4vw,18px) clamp(14px,1.6vw,22px);
        background: linear-gradient(135deg, #70131B, #8f2230);
        border-bottom: 1px solid rgba(255,255,255,0.12);
        flex: 0 0 auto;
    }
    .hr-modal-head-main { display:flex; align-items:center; gap:14px; min-width:0; flex:1 1 auto; }
    .hr-modal-head-badge {
        width:44px; height:44px; flex:0 0 44px; border-radius:14px;
        display:inline-flex; align-items:center; justify-content:center;
        background:rgba(255,255,255,0.16); border:1px solid rgba(255,255,255,0.24);
        color:#ffffff; font-size:12px; font-weight:900; letter-spacing:.06em;
    }
    .hr-modal-head h3 { margin:0; color:#ffffff !important; font-size:clamp(15px,1.4vw,18px); font-weight:900; }
    .hr-modal-head p  { margin:3px 0 0; color:rgba(255,255,255,0.82) !important; font-size:12px; line-height:1.5; }
    .hr-modal-close {
        width:38px; height:38px; flex:0 0 38px; border-radius:999px;
        border:1px solid rgba(255,255,255,0.22); background:rgba(255,255,255,0.12);
        color:#ffffff; display:inline-flex; align-items:center; justify-content:center;
        cursor:pointer; transition:background .18s ease, transform .18s ease;
    }
    .hr-modal-close:hover { background:rgba(255,255,255,0.26); transform:translateY(-1px); }
    .hr-modal-close svg { width:16px; height:16px; stroke-width:2.2; }
    .hr-modal-body {
        flex:1 1 auto; overflow-y:auto; padding:24px;
        min-height:0; scrollbar-width:none; -ms-overflow-style:none;
    }
    .hr-modal-body::-webkit-scrollbar { display:none; }
    /* Default pane */
    .hr-ref-default {
        display:flex; flex-direction:column;
        align-items:center; text-align:center; gap:16px; padding:8px 0;
    }
    .hr-ref-default h4 { margin:0; font-size:20px; font-weight:900; color:#111827; }
    .hr-ref-default p  { margin:0; font-size:13px; color:#64748b; line-height:1.55; max-width:360px; }
    /* Entry pane */
    .hr-ref-entry {
        display: none;
        position: relative;
        gap: 12px;
    }
    .hr-ref-entry.is-visible { display: grid; }
    .hr-ref-tip {
        position: relative;
        max-width: 100%;
        padding:10px 12px;
        border-radius:14px; background:#fff7ed;
        border:1px solid #fed7aa; color:#9a3412;
        font-size:12px; line-height:1.5;
        box-shadow:0 8px 18px rgba(180,83,9,0.10);
    }
    .hr-ref-tip strong { display:block; margin-bottom:3px; font-size:10px; font-weight:900; text-transform:uppercase; letter-spacing:.06em; }
    .hr-ref-tip::before,
    .hr-ref-tip::after {
        display: none;
    }
    .hr-ref-label { font-size:11px; font-weight:900; text-transform:uppercase; letter-spacing:.06em; color:#475569; margin-bottom:6px; display:block; }
    .hr-ref-input {
        width:100%; min-height:52px; padding:14px 16px;
        border:1px solid rgba(112,19,27,0.18); border-radius:14px;
        background:linear-gradient(180deg,#ffffff,#fff8f6); color:#111827;
        font-size:14px; font-weight:700; outline:none;
        box-shadow:0 8px 18px rgba(15,23,42,0.06), inset 0 1px 0 rgba(255,255,255,0.9);
        transition:border-color .18s ease, box-shadow .18s ease;
        margin-bottom:10px;
    }
    .hr-ref-input:focus { border-color:#70131B; box-shadow:0 0 0 3px rgba(112,19,27,0.08), 0 8px 18px rgba(15,23,42,0.08); }
    .hr-ref-status { margin:8px 0; padding:10px 12px; border-radius:10px; font-size:12px; font-weight:700; display:none; }
    .hr-ref-status.info    { display:block; background:#eff6ff; border:1px solid #bfdbfe; color:#1d4ed8; }
    .hr-ref-status.success { display:block; background:#ecfdf5; border:1px solid #a7f3d0; color:#047857; }
    .hr-ref-status.error   { display:block; background:#fff1f2; border:1px solid #fecdd3; color:#be123c; }
    .hr-ref-actions { display:flex; gap:10px; margin-top:12px; }
    .hr-btn {
        flex:1; min-height:46px; border-radius:999px; padding:0 18px;
        font-size:13px; font-weight:900; cursor:pointer;
        border:1px solid transparent; display:inline-flex;
        align-items:center; justify-content:center; gap:8px;
        transition:transform .18s ease, background .18s ease, color .18s ease, box-shadow .18s ease;
    }
    .hr-btn:hover { transform:translateY(-1px); }
    .hr-btn-cancel { background:#f1f5f9; color:#334155; border-color:#cbd5e1; }
    .hr-btn-cancel:hover { background:#e2e8f0; }
    .hr-btn-primary {
        background:linear-gradient(135deg,#70131B,#8f2230);
        color:#ffffff; border-color:#8f2230;
        box-shadow:0 10px 22px rgba(112,19,27,0.22);
    }
    .hr-btn-primary:hover { background:#facc15; color:#111827; border-color:#facc15; }
    .hr-btn-toggle {
        background:#ffffff; color:#70131B; border-color:rgba(112,19,27,0.18);
        box-shadow:0 6px 14px rgba(15,23,42,0.06);
    }
    .hr-btn-toggle:hover { border-color:rgba(112,19,27,0.32); }
    /* MA form inside modal */
    .hr-ma-section {
        margin-bottom:14px; padding:16px 18px; border-radius:16px;
        border:1px solid rgba(112,19,27,0.12);
        background:linear-gradient(180deg,#ffffff,#f8fafc);
        box-shadow:inset 0 1px 0 rgba(255,255,255,0.9), 0 8px 20px rgba(15,23,42,0.05);
    }
    .hr-ma-section-title {
        margin:0 0 14px; font-size:12px; font-weight:900;
        text-transform:uppercase; letter-spacing:.08em; color:#70131B;
        display:flex; align-items:center; gap:8px;
    }
    .hr-ma-section-num {
        width:26px; height:26px; border-radius:999px;
        background:#fff1f2; border:1px solid #fecdd3;
        display:inline-flex; align-items:center; justify-content:center;
        font-size:11px; font-weight:900; color:#70131B; flex:0 0 auto;
    }
    .hr-ma-grid   { display:grid; grid-template-columns:repeat(2,minmax(0,1fr)); gap:12px; }
    .hr-ma-grid-3 { display:grid; grid-template-columns:repeat(3,minmax(0,1fr)); gap:12px; }
    .hr-ma-grid-4 { display:grid; grid-template-columns:repeat(4,minmax(0,1fr)); gap:12px; }
    .hr-ma-grid-1 { display:grid; grid-template-columns:1fr; gap:12px; }
    .hr-ma-field  { display:flex; flex-direction:column; gap:5px; }
    .hr-ma-label  { font-size:10px; font-weight:900; text-transform:uppercase; letter-spacing:.06em; color:#64748b; }
    .hr-ma-control {
        width:100%; min-height:44px; padding:10px 14px;
        border:1px solid rgba(112,19,27,0.15); border-radius:12px;
        background:linear-gradient(180deg,#ffffff,#fff8f6); color:#111827;
        font-size:13px; font-weight:700; outline:none;
        box-shadow:inset 0 1px 0 rgba(255,255,255,0.92), 0 2px 4px rgba(112,19,27,0.04), 0 8px 16px rgba(112,19,27,0.07);
        transition:border-color .18s ease, box-shadow .2s ease, transform .18s ease;
    }
    .hr-ma-control:focus { border-color:#70131B; transform:translateY(-1px); box-shadow:inset 0 1px 0 rgba(255,255,255,0.92), 0 0 0 3px rgba(112,19,27,0.08), 0 10px 24px rgba(112,19,27,0.12); }
    .hr-ma-control[readonly] { background:#f8fafc; color:#64748b; border-color:#e2e8f0; }
    textarea.hr-ma-control { min-height:96px; resize:vertical; line-height:1.55; }
    .hr-ma-radio-group { display:flex; gap:8px; flex-wrap:wrap; }
    .hr-ma-radio {
        display:inline-flex; align-items:center; gap:6px; min-height:40px;
        padding:0 14px; border-radius:999px; background:#f8fafc;
        border:1px solid #e2e8f0; color:#334155; font-size:12px; font-weight:900; cursor:pointer;
    }
    .hr-ma-radio input { accent-color:#70131B; }
    .hr-ma-required { display:inline-flex; align-items:center; padding:2px 7px; border-radius:999px; background:#fff1f2; border:1px solid #fecdd3; color:#be123c; font-size:9px; font-weight:900; text-transform:uppercase; letter-spacing:.06em; margin-left:5px; }
    .hr-ma-actions { display:flex; justify-content:flex-end; gap:10px; padding:14px 0 4px; border-top:1px solid rgba(112,19,27,0.10); margin-top:6px; }
    @media (max-width:640px) {
        .hr-ma-grid, .hr-ma-grid-3, .hr-ma-grid-4 { grid-template-columns:1fr; }
    }
    html[data-theme="dark"] .hr-modal-shell { background:rgba(15,23,42,0.98); border-top-color:#facc15; border-bottom-color:#facc15; }
    html[data-theme="dark"] .hr-modal-head { background:#4d0d17; }
    html[data-theme="dark"] .hr-ma-section { background:linear-gradient(180deg,rgba(17,24,39,0.96),rgba(15,23,42,0.94)); border-color:rgba(250,204,21,0.14); }
    html[data-theme="dark"] .hr-ma-control { background:rgba(17,24,39,0.88); color:#f8fafc; border-color:rgba(148,163,184,0.22); }
    html[data-theme="dark"] .hr-ma-label { color:#94a3b8; }
    html[data-theme="dark"] .hr-ref-input { background:rgba(30,41,59,0.9); color:#f1f5f9; border-color:rgba(148,163,184,0.24); }
    html[data-theme="dark"] .hr-ref-default h4, html[data-theme="dark"] .hr-ref-default p { color:#f1f5f9; }
    html[data-theme="dark"] .hr-ma-radio { background:rgba(17,24,39,0.86); color:#f8fafc; border-color:rgba(148,163,184,0.18); }

    .health-table-tools .health-records-search-wrap,
    .health-records-overview .health-records-search-wrap {
        border: 0 !important;
        border-bottom: 3px solid #8f2230 !important;
        border-radius: 0 !important;
        background: transparent !important;
        box-shadow: none !important;
    }

    .health-table-tools .health-records-search-wrap::before,
    .health-records-overview .health-records-search-wrap::before {
        content: "";
        width: 18px;
        height: 18px;
        margin: 0 12px 0 2px;
        flex: 0 0 auto;
        background: #9f1239;
        -webkit-mask: url("data:image/svg+xml,%3Csvg viewBox='0 0 24 24' fill='none' xmlns='http://www.w3.org/2000/svg'%3E%3Cpath d='m21 21-4.35-4.35M10.5 18a7.5 7.5 0 1 1 0-15 7.5 7.5 0 0 1 0 15Z' stroke='black' stroke-width='2' stroke-linecap='round'/%3E%3C/svg%3E") center / contain no-repeat;
        mask: url("data:image/svg+xml,%3Csvg viewBox='0 0 24 24' fill='none' xmlns='http://www.w3.org/2000/svg'%3E%3Cpath d='m21 21-4.35-4.35M10.5 18a7.5 7.5 0 1 1 0-15 7.5 7.5 0 0 1 0 15Z' stroke='black' stroke-width='2' stroke-linecap='round'/%3E%3C/svg%3E") center / contain no-repeat;
    }

    .health-table-tools .health-records-search,
    .health-records-overview .health-records-search {
        min-height: 42px !important;
        height: 42px !important;
        padding: 8px 0 !important;
        border: 0 !important;
        border-radius: 0 !important;
        background: transparent !important;
        box-shadow: none !important;
        color: #0f172a !important;
    }

    .health-table-tools .health-records-search::placeholder,
    .health-records-overview .health-records-search::placeholder {
        color: #94a3b8 !important;
        font-weight: 800;
    }

    html[data-theme="dark"] .health-table-tools .health-records-search-wrap,
    html[data-theme="dark"] .health-records-overview .health-records-search-wrap {
        border-bottom-color: #facc15 !important;
        background: transparent !important;
    }

    html[data-theme="dark"] .health-table-tools .health-records-search-wrap::before,
    html[data-theme="dark"] .health-records-overview .health-records-search-wrap::before {
        background: #facc15;
    }

    html[data-theme="dark"] .health-table-tools .health-records-search,
    html[data-theme="dark"] .health-records-overview .health-records-search {
        color: #ffffff !important;
        background: transparent !important;
    }

    html[data-theme="dark"] .health-table-tools .health-records-search::placeholder,
    html[data-theme="dark"] .health-records-overview .health-records-search::placeholder {
        color: #cbd5e1 !important;
    }

    html[data-theme="dark"] .health-records-search-submit {
        background: #7f1d2d !important;
        border-color: #7f1d2d !important;
        color: #ffffff !important;
    }

    html[data-theme="dark"] .health-records-search-submit:hover,
    html[data-theme="dark"] .health-records-search-submit:focus-visible {
        background: #facc15 !important;
        border-color: #facc15 !important;
        color: #7f1d2d !important;
    }

    .health-records-overview .health-records-search-shell,
    .health-records-overview .health-records-search-shell.is-open,
    .health-records-overview .health-records-search-wrap,
    .health-records-overview .health-records-search-shell.is-open .health-records-search-wrap {
        width: 100% !important;
        flex: 1 1 auto !important;
        opacity: 1 !important;
        pointer-events: auto !important;
    }
    .health-records-overview .health-records-search {
        width: 100% !important;
    }

    .health-summary-modern-card.is-approved:hover,
    .health-summary-modern-card.is-condition:hover {
        transform: translateY(-4px) scale(1.015);
        box-shadow: 0 20px 36px rgba(112, 19, 27, .16);
        border-color: rgba(112, 19, 27, .24);
        background: #fffaf0;
    }
    .health-summary-modern-card.is-approved:hover::before,
    .health-summary-modern-card.is-condition:hover::before {
        opacity: 1;
        transform: translateX(510%) skewX(-18deg);
    }
    html[data-theme="dark"] .health-summary-modern-card.is-approved:hover,
    html[data-theme="dark"] .health-summary-modern-card.is-condition:hover {
        background: rgba(112, 19, 27, .92);
        border-color: rgba(250, 204, 21, .52);
        box-shadow: 0 20px 36px rgba(0, 0, 0, .32);
    }

    .premium-select-option:hover,
    .premium-select-option:focus-visible {
        background: #7f0010 !important;
        color: #ffffff !important;
        border-color: #7f0010 !important;
        outline: none;
    }
    .premium-select-option.is-selected {
        background: #7f0010 !important;
        color: #facc15 !important;
        border-color: #7f0010 !important;
    }
    .premium-select-option.is-selected:hover,
    .premium-select-option.is-selected:focus-visible {
        color: #ffffff !important;
    }
    html[data-theme="dark"] .premium-select-option:hover,
    html[data-theme="dark"] .premium-select-option:focus-visible {
        background: #7f0010 !important;
        color: #ffffff !important;
        border-color: #7f0010 !important;
    }
    html[data-theme="dark"] .premium-select-option.is-selected {
        background: #7f0010 !important;
        color: #facc15 !important;
        border-color: #facc15 !important;
    }
    html[data-theme="dark"] .premium-select-option.is-selected:hover,
    html[data-theme="dark"] .premium-select-option.is-selected:focus-visible {
        color: #ffffff !important;
    }

    .health-table-tools .health-records-search-submit,
    .health-table-tools .health-records-search-submit *,
    button#healthRecordsOverviewFilterBtn.health-records-search-submit {
        color: #ffffff !important;
        -webkit-text-fill-color: #ffffff !important;
        fill: #ffffff !important;
        stroke: #ffffff !important;
    }
    .health-table-tools .health-records-search-submit:hover,
    .health-table-tools .health-records-search-submit:focus-visible,
    button#healthRecordsOverviewFilterBtn.health-records-search-submit:hover,
    button#healthRecordsOverviewFilterBtn.health-records-search-submit:focus-visible {
        color: #7f1d2d !important;
        -webkit-text-fill-color: #7f1d2d !important;
    }
    .health-summary-card {
        margin-top: 16px !important;
    }
    .health-summary-modern-container .health-summary-modern-card {
        min-height: 98px !important;
        padding-top: 13px !important;
        padding-bottom: 13px !important;
    }

</style>
@endpush

@section('content')
    @php
        $role = \App\Models\User::normalizeRole(optional(auth()->user())->user_role ?? '');
        $basePrefix = $role === \App\Models\User::ROLE_ADMIN ? '/assistant' : '/admin';
        $canSignHealth = $role === \App\Models\User::ROLE_SUPERADMIN;
        $highlightHealthId = trim((string) request()->query('highlight_health', ''));
    @endphp

    @php
        $healthSummaryStats = [
            'total' => $records->count(),
            'with_conditions' => 0,
            'pending_approval' => 0,
            'pending_conditional' => 0,
        ];
        $pendingApprovalRecordIds = [];
        $pendingConditionalRecordIds = [];

        foreach ($records as $summaryRecord) {
            $summaryRecordSource = (string) ($summaryRecord->record_source ?? 'health');
            $summaryRecordKey = (string) ($summaryRecord->record_key ?? ($summaryRecordSource . ':' . $summaryRecord->id));
            $summaryHasRequirements = in_array($summaryRecordSource, ['employee', 'staff'], true) || filled($summaryRecord->medical_certificate)
                && filled($summaryRecord->chest_xray_result)
                && filled($summaryRecord->student_photo);
            $summaryStatus = trim((string) ($summaryRecord->clearance_status ?? ''));
            $summaryIsApproved = in_array($summaryStatus, ['Issued', 'Fully Cleared'], true);
            $summaryIsConditional = !$summaryIsApproved && (
                in_array($summaryStatus, ['Pending/Conditional', 'Pending Resubmission', 'Rejected'], true)
                || trim((string) ($summaryRecord->pending_reason ?? '')) !== ''
                || trim((string) ($summaryRecord->medical_condition_remarks ?? '')) !== ''
            );

            if ($summaryIsApproved && $summaryRecord->hasMedicalCondition()) {
                $healthSummaryStats['with_conditions']++;
            }

            if ($summaryHasRequirements && !$summaryIsConditional && in_array($summaryStatus, ['Pending', 'For Verification', ''], true)) {
                $healthSummaryStats['pending_approval']++;
                $pendingApprovalRecordIds[] = $summaryRecordKey;
            }

            if ($summaryIsConditional) {
                $healthSummaryStats['pending_conditional']++;
                $pendingConditionalRecordIds[] = $summaryRecordKey;
            }
        }

        $healthSummaryStats['total_approved'] = $healthProfileSummaryRecords->total();
        $latestApprovedAt = $records
            ->filter(function ($summaryRecord) {
                return in_array(trim((string) ($summaryRecord->clearance_status ?? '')), ['Issued', 'Fully Cleared'], true)
                    && filled($summaryRecord->verified_at);
            })
            ->max('verified_at');
        $latestApprovedAt = $latestApprovedAt ? \Carbon\Carbon::parse($latestApprovedAt) : null;
        $formatHealthCourseCode = function ($courseName, $courseCode = '') {
            $courseCode = strtoupper(trim((string) $courseCode));
            if ($courseCode !== '') {
                return $courseCode;
            }

            $courseName = trim((string) $courseName);
            if ($courseName === '') {
                return '';
            }

            $words = preg_split('/\s+/', preg_replace('/[^A-Za-z0-9\s]/', ' ', $courseName)) ?: [];
            $stopWords = ['of', 'in', 'and', 'the', 'major'];
            $code = collect($words)
                ->map(fn ($word) => strtolower(trim((string) $word)))
                ->filter(fn ($word) => $word !== '' && !in_array($word, $stopWords, true))
                ->map(fn ($word) => \Illuminate\Support\Str::upper(\Illuminate\Support\Str::substr($word, 0, 1)))
                ->implode('');

            return $code !== '' ? $code : $courseName;
        };
        $formatHealthYear = function ($year) {
            $year = trim((string) $year);
            if ($year === '') {
                return '';
            }

            if (preg_match('/[1-4]/', $year, $matches)) {
                return $matches[0];
            }

            return $year;
        };
        $formatHealthCourseYearSection = function ($courseName, $courseCode = '', $year = '', $section = '') use ($formatHealthCourseCode, $formatHealthYear) {
            $code = $formatHealthCourseCode($courseName, $courseCode);
            $year = $formatHealthYear($year);
            $section = trim((string) $section);
            $suffix = trim($year . ($section !== '' ? ' - ' . $section : ''));
            $display = trim($code . ($suffix !== '' ? ' ' . $suffix : ''));

            return $display !== '' ? $display : trim((string) $courseName);
        };
        $resolveHealthRecordUserType = function ($record) {
            $user = optional($record->user);
            $rawType = strtolower(trim((string) ($user->user_type ?: $user->user_role ?: '')));

            if (str_contains($rawType, 'faculty')) {
                return 'Faculty';
            }
            if (str_contains($rawType, 'dependent')) {
                return 'Dependent';
            }
            if (str_contains($rawType, 'admin') || str_contains($rawType, 'nurse')) {
                return 'Admin';
            }
            if (str_contains($rawType, 'applicant')) {
                return 'Applicant';
            }

            $studentNumber = strtoupper(trim((string) ($record->student_number ?: $user->student_number)));
            return $studentNumber !== ''
                && !\Illuminate\Support\Str::startsWith($studentNumber, ['CLN-', 'LOC-', 'TEST-LOCAL'])
                    ? 'Student'
                    : 'Applicant';
        };
    @endphp

    <section class="health-records-overview">
        <div class="health-records-overview-head">
            <div class="health-records-title-block">
                <span class="health-records-title-icon"><x-outline-icon name="document-text" /></span>
                <div class="health-records-title-copy">
                    <h2>Health Records</h2>
                    <p>View approved medical clearances and student health profiles.</p>
                </div>
            </div>
            <div class="health-records-last-updated">
                <span class="health-records-last-updated-icon"><x-outline-icon name="clock" /></span>
                <div>
                    <span>Last Updated</span>
                    <strong>
                        @if($latestApprovedAt)
                            {{ $latestApprovedAt->format('M d, Y') }}<br>{{ $latestApprovedAt->format('g:i A') }}
                        @else
                            N/A
                        @endif
                    </strong>
                </div>
            </div>
        </div>

        <div class="health-summary-modern-container">
            <div class="health-summary-modern-card is-approved">
                <span class="health-summary-modern-icon-wrap"><x-outline-icon name="check" /></span>
                <div class="health-summary-modern-copy">
                    <span class="health-summary-modern-label">Total Approved</span>
                    <span class="health-summary-modern-count" data-health-record-stat="total_approved">{{ $healthSummaryStats['total_approved'] }}</span>
                    <span class="health-summary-modern-note">Issued clearances</span>
                </div>
            </div>
            <div class="health-summary-modern-card is-condition">
                <span class="health-summary-modern-icon-wrap">
                    <svg viewBox="0 0 24 24" fill="none" aria-hidden="true">
                        <path d="M20.8 5.8c-1.7-2-4.7-2.1-6.5-.3L12 7.8 9.7 5.5C7.9 3.7 4.9 3.8 3.2 5.8c-1.6 1.9-1.4 4.8.4 6.6L12 20.5l8.4-8.1c1.8-1.8 2-4.7.4-6.6Z" stroke="currentColor" stroke-width="2" stroke-linejoin="round"/>
                    </svg>
                </span>
                <div class="health-summary-modern-copy">
                    <span class="health-summary-modern-label">With Medical Conditions</span>
                    <span class="health-summary-modern-count" data-health-record-stat="with_conditions">{{ $healthSummaryStats['with_conditions'] }}</span>
                    <span class="health-summary-modern-note">Approved records only</span>
                </div>
            </div>
            <button type="button" class="health-summary-modern-card health-summary-info-btn is-pending is-clickable" id="pendingApprovalInfoBtn" onclick="document.getElementById('pendingApprovalInfoModal').style.display='flex';">
                <span class="health-summary-modern-icon-wrap">
                    <svg viewBox="0 0 24 24" fill="none" aria-hidden="true">
                        <path d="M7 4h7l4 4v12H7V4z" stroke="currentColor" stroke-width="2" stroke-linejoin="round"/>
                        <path d="M14 4v4h4" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                        <path d="M9.5 14l2 2 4-5" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                    </svg>
                </span>
                <div class="health-summary-modern-copy">
                    <span class="health-summary-modern-label">Pending Approval</span>
                    <span class="health-summary-modern-count" data-health-record-stat="pending_approval">{{ $healthSummaryStats['pending_approval'] }}</span>
                    <span class="health-summary-modern-note">Click to view</span>
                </div>
                <span class="health-summary-modern-arrow">&rarr;</span>
            </button>
            <button type="button" class="health-summary-modern-card health-summary-info-btn is-compliance is-clickable" id="pendingConditionalInfoBtn" onclick="document.getElementById('pendingConditionalInfoModal').style.display='flex';">
                <span class="health-summary-modern-icon-wrap"><x-outline-icon name="exclamation-triangle" /></span>
                <div class="health-summary-modern-copy">
                    <span class="health-summary-modern-label">Pending Compliance</span>
                    <span class="health-summary-modern-count" data-health-record-stat="pending_conditional">{{ $healthSummaryStats['pending_conditional'] }}</span>
                    <span class="health-summary-modern-note">Click to view</span>
                </div>
                <span class="health-summary-modern-arrow">&rarr;</span>
            </button>
        </div>
    </section>

    {{-- Main Table Card --}}
<div class="card health-summary-card">
    <div class="health-table-head">
        <div class="health-table-title">Issued Medical Clearance</div>
        <div class="health-table-tools">
            <form method="GET" action="{{ url()->current() }}" class="health-records-search-shell is-open" id="healthRecordsSearchShell">
                <div class="health-records-search-wrap">
                    <input
                        type="text"
                        id="recordSearch"
                        value=""
                        class="health-records-search"
                        placeholder="Search records..."
                        autocomplete="off"
                    >
                </div>
            </form>
            <button type="button" class="health-records-search-submit" id="healthRecordsOverviewFilterBtn">
                <x-outline-icon name="funnel" />
                Filter
            </button>
        </div>
    </div>
    <table id="healthTable">
        <thead>
            <tr>
                <th>Full Name</th>
                <th>User Type</th>
                <th>Course / Yr / Sec</th>
                <th>Medical Condition</th>
                <th>Clearance Status</th>
                <th>Approved At</th>
                <th style="text-align: center;">Actions</th>
            </tr>
        </thead>
        <tbody>
            @forelse($healthProfileSummaryRecords as $record)
                @php
                    $recordSource = (string) ($record->record_source ?? 'health');
                    $recordIsEmployee = in_array($recordSource, ['employee', 'staff'], true);
                    $hasClinicRequirements = filled($record->medical_certificate)
                        && filled($record->chest_xray_result)
                        && filled($record->student_photo);
                    $recordStatus = trim((string) ($record->clearance_status ?? ''));
                    $recordStatusNormalized = strtolower($recordStatus);
                    $isConditional = in_array($recordStatus, ['Pending/Conditional', 'Rejected'], true)
                        || trim((string) ($record->pending_reason ?? '')) !== ''
                        || trim((string) ($record->medical_condition_remarks ?? '')) !== '';
                    $healthTabState = $isConditional
                        ? 'pending_conditional'
                        : (in_array($recordStatus, ['Pending', 'For Verification', ''], true) ? 'pending_approval' : 'cleared');
                    $recordCourseName = trim((string) ($record->course_college ?: optional($record->user)->course ?: ''));
                    $recordCourseDisplay = $formatHealthCourseYearSection(
                        $recordCourseName,
                        $record->course_code ?? '',
                        optional($record->user)->year,
                        optional($record->user)->section
                    );
                    $recordUserType = $resolveHealthRecordUserType($record);
                    $puptasStatusRaw = strtolower(trim((string) ($record->puptas_sync_status ?? '')));
                    $puptasReference = strtoupper(trim((string) ($record->reference_number ?: $record->student_number ?: optional($record->user)->student_number)));
                    $isLocalPuptasReference = $puptasReference === ''
                        || \Illuminate\Support\Str::startsWith($puptasReference, ['CLN-', 'LOC-', 'TEST-LOCAL']);
                    if ($puptasStatusRaw === '' && $isLocalPuptasReference) {
                        $puptasStatusRaw = 'not_applicable';
                    }
                    $puptasStatusLabel = match ($puptasStatusRaw) {
                        'synced' => 'Synced',
                        'failed' => 'Failed',
                        'syncing' => 'Syncing',
                        'pending' => 'Pending',
                        'not_applicable' => 'N/A',
                        'missing_reference_number' => 'Missing Ref',
                        default => 'Not Synced',
                    };
                    $puptasStatusClass = match ($puptasStatusRaw) {
                        'synced' => 'issued',
                        'failed', 'missing_reference_number' => 'review',
                        'syncing', 'pending' => 'pending',
                        'not_applicable' => 'submitted',
                        default => 'pending',
                    };
                    $recordPayload = [
                        'id' => $record->id,
                        'name' => optional($record->user)->name ?: '-',
                        'email' => optional($record->user)->email ?: '-',
                        'reference_number' => $record->reference_number ?: $record->student_number ?: optional($record->user)->student_number ?: '-',
                        'student_id' => $record->student_id ?: optional($record->user)->student_id ?: '-',
                        'student_number' => optional($record->user)->student_number ?: optional($record->user)->student_id ?: '-',
                        'course' => $recordCourseDisplay !== '' ? $recordCourseDisplay : '-',
                        'status' => $recordStatus ?: 'For Verification',
                        'pending_reason' => $record->pending_reason ?: '',
                        'medical_condition_remarks' => $record->medical_condition_remarks ?: '',
                        'physical_assessment_status' => $record->physical_assessment_status ?: 'Not Yet Conducted',
                        'documents_valid' => (bool) $record->documents_valid,
                        'approve_url' => $recordIsEmployee ? '' : route('admin.update_clearance', $record->id),
                        'resubmission_url' => $recordIsEmployee ? '' : route('admin.health_profile.request_resubmission', $record->id),
                        'documents' => [
                            [
                                'title' => '2x2 Photo',
                                'url' => $record->student_photo ? ($recordIsEmployee ? route('walkin.employeeDocument', [
                                    'employeeProfile' => $record->id,
                                    'document' => 'student_photo',
                                ]) : route('walkin.document', [
                                    'healthProfile' => $record->id,
                                    'document' => 'student_photo',
                                ])) : '',
                                'meta' => [
                                    'Guideline' => 'Formal white-background photo.',
                                ],
                            ],
                            [
                                'title' => 'Health Information Form',
                                'url' => $recordIsEmployee ? route('walkin.employeeHealthForm', [
                                    'employeeProfile' => $record->id,
                                    'fresh' => 1,
                                ]) : route('walkin.healthForm', [
                                    'healthProfile' => $record->id,
                                ]),
                                'meta' => [
                                    'Type' => 'Official health form layout',
                                ],
                            ],
                            [
                                'title' => 'Medical Certificate',
                                'url' => $record->medical_certificate ? ($recordIsEmployee ? route('walkin.employeeDocument', [
                                    'employeeProfile' => $record->id,
                                    'document' => 'medical_certificate',
                                ]) : route('walkin.document', [
                                    'healthProfile' => $record->id,
                                    'document' => 'medical_certificate',
                                ])) : '',
                                'meta' => [
                                    'Doctor' => $record->doctor_name ?: '-',
                                    'Certificate Date' => optional($record->med_cert_date)->format('M d, Y') ?: '-',
                                    'Findings' => $record->med_cert_findings ?: '-',
                                ],
                            ],
                            [
                                'title' => 'Chest X-ray Result',
                                'url' => $record->chest_xray_result ? ($recordIsEmployee ? route('walkin.employeeDocument', [
                                    'employeeProfile' => $record->id,
                                    'document' => 'chest_xray_result',
                                ]) : route('walkin.document', [
                                    'healthProfile' => $record->id,
                                    'document' => 'chest_xray_result',
                                ])) : '',
                                'meta' => [
                                    'Exam Date' => optional($record->xray_date)->format('M d, Y') ?: '-',
                                    'Findings' => $record->xray_findings ?: '-',
                                ],
                            ],
                            [
                                'title' => 'Health Declaration',
                                'url' => $record->health_declaration ? ($recordIsEmployee ? route('walkin.employeeDocument', [
                                    'employeeProfile' => $record->id,
                                    'document' => 'health_declaration',
                                ]) : route('walkin.document', [
                                    'healthProfile' => $record->id,
                                    'document' => 'health_declaration',
                                ])) : '',
                                'meta' => [
                                    'Status' => $record->health_declaration ? 'Uploaded' : 'Missing / Not yet uploaded',
                                ],
                            ],
                        ],
                    ];
                @endphp
                <tr
                    data-health-row
                    data-health-id="{{ $record->id }}"
                    data-health-tab="{{ $healthTabState }}"
                    data-health-condition="{{ $record->hasMedicalCondition() ? 'with_conditions' : 'none' }}"
                    data-record-payload="{{ e(json_encode($recordPayload)) }}"
                    data-view-url="{{ (!$recordIsEmployee && in_array($record->clearance_status, ['Issued', 'Fully Cleared'], true)) ? route('admin.show_health', $record->id) : '' }}"
                    title="{{ (!$recordIsEmployee && in_array($record->clearance_status, ['Issued', 'Fully Cleared'], true)) ? 'Click to view' : '' }}"
                    class="{{ implode(' ', array_filter([
                        $highlightHealthId !== '' && $highlightHealthId === (string) $record->id ? 'health-highlight-row' : '',
                        (!$recordIsEmployee && in_array($record->clearance_status, ['Issued', 'Fully Cleared'], true)) ? 'health-row-clickable' : '',
                    ])) }}"
                >
                    <td>
                        <div class="student-name" style="font-weight: 700;">{{ $record->user->name }}</div>
                    </td>
                    <td>
                        <span class="status {{ $recordUserType === 'Student' ? 'issued' : 'submitted' }}">{{ $recordUserType }}</span>
                    </td>
                    <td>{{ $recordCourseDisplay !== '' ? $recordCourseDisplay : '-' }}</td>
                    
                    {{-- Column 1: Medical Condition Status --}}
                    <td>
                        @if($record->hasMedicalCondition())
                            <span class="status review">With Condition</span>
                        @else
                            <span class="status submitted">No Condition</span>
                        @endif
                    </td>

                    {{-- Column 2: Clearance Issuance Status --}}
                    <td>
                        @if(in_array($record->clearance_status, ['Issued', 'Fully Cleared'], true))
                            @if($puptasStatusRaw === 'synced')
                                <span class="status issued"><i class="fas fa-check-circle me-1"></i> Issued</span>
                            @else
                                <span class="status pending">Not Sync</span>
                            @endif
                        @elseif($record->clearance_status == 'Pending/Conditional')
                            <span class="status review">Pending/Conditional</span>
                        @elseif($record->clearance_status == 'Rejected')
                            <span class="status review">Rejected</span>
                        @elseif(in_array($record->clearance_status, ['Pending', 'For Verification'], true))
                            <span class="status pending">For Verification</span>
                        @else
                            <span class="status submitted">Not Processed</span>
                        @endif
                    </td>

                    <td style="color: #94a3b8; font-size: 12px;">
                        {{ $record->verified_at ? \Carbon\Carbon::parse($record->verified_at)->format('M d, Y g:i A') : '-' }}
                    </td>

                    <td style="text-align: center;">
                        <div class="d-flex justify-content-center">
                            @if($recordIsEmployee)
                                <a href="{{ route('walkin.employeeHealthForm', ['employeeProfile' => $record->id, 'fresh' => 1]) }}" class="btn-action btn-view" target="_blank" rel="noopener noreferrer">
                                    <x-outline-icon name="eye" />
                                    View
                                </a>
                            @else
                                <a href="{{ route('admin.show_health', $record->id) }}" class="btn-action btn-view">
                                    <x-outline-icon name="eye" />
                                    View
                                </a>
                            @endif
                        </div>
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="7" style="text-align: center; padding: 40px; color: #94a3b8;">No issued health profiles found.</td>
                </tr>
            @endforelse
        </tbody>
    </table>
    @if($healthProfileSummaryRecords->total() > 0)
        @php
            $issuedCurrentPage = $healthProfileSummaryRecords->currentPage();
            $issuedLastPage = $healthProfileSummaryRecords->lastPage();
            $issuedPages = collect([1, $issuedCurrentPage - 1, $issuedCurrentPage, $issuedCurrentPage + 1, $issuedLastPage])
                ->filter(fn ($page) => $page >= 1 && $page <= $issuedLastPage)
                ->unique()
                ->values();
            $issuedPerPageOptions = [
                '20' => '20 per page',
                '40' => '40 per page',
                '80' => '80 per page',
                '100' => '100 per page',
                'all' => 'Show all',
            ];
        @endphp
        <div class="readonly-modal-pagination is-visible" aria-label="Issued medical clearance pagination">
            <span class="readonly-pagination-summary">
                Showing {{ $healthProfileSummaryRecords->firstItem() }} to {{ $healthProfileSummaryRecords->lastItem() }} of {{ $healthProfileSummaryRecords->total() }} records
            </span>
            <div class="readonly-pagination-actions">
                @if($healthProfileSummaryRecords->onFirstPage())
                    <button type="button" class="readonly-pagination-btn" disabled aria-label="Previous page">&larr;</button>
                @else
                    <a class="readonly-pagination-btn" href="{{ $healthProfileSummaryRecords->previousPageUrl() }}" style="text-decoration:none;display:inline-flex;align-items:center;justify-content:center;" aria-label="Previous page">&larr;</a>
                @endif

                @foreach($issuedPages as $page)
                    @if($loop->index > 0 && $page - $issuedPages[$loop->index - 1] > 1)
                        <span class="readonly-pagination-btn" aria-hidden="true">...</span>
                    @endif
                    @if($page === $issuedCurrentPage)
                        <button type="button" class="readonly-pagination-btn is-active" disabled>{{ $page }}</button>
                    @else
                        <a class="readonly-pagination-btn" href="{{ $healthProfileSummaryRecords->url($page) }}" style="text-decoration:none;display:inline-flex;align-items:center;justify-content:center;">{{ $page }}</a>
                    @endif
                @endforeach

                @if($healthProfileSummaryRecords->hasMorePages())
                    <a class="readonly-pagination-btn" href="{{ $healthProfileSummaryRecords->nextPageUrl() }}" aria-label="Next page">&rarr;</a>
                @else
                    <button type="button" class="readonly-pagination-btn" disabled aria-label="Next page">&rarr;</button>
                @endif
            </div>
            <form method="GET" action="{{ url()->current() }}" class="readonly-pagination-per-page-form">
                @foreach(request()->except(['issued_page', 'per_page']) as $queryKey => $queryValue)
                    @if(is_array($queryValue))
                        @foreach($queryValue as $nestedValue)
                            <input type="hidden" name="{{ $queryKey }}[]" value="{{ $nestedValue }}">
                        @endforeach
                    @else
                        <input type="hidden" name="{{ $queryKey }}" value="{{ $queryValue }}">
                    @endif
                @endforeach
                <select name="per_page" class="readonly-pagination-per-page-select" onchange="this.form.submit()" aria-label="Issued medical clearance records per page">
                    @foreach($issuedPerPageOptions as $optionValue => $optionLabel)
                        <option value="{{ $optionValue }}" @selected(($issuedPerPage ?? '20') === $optionValue)>{{ $optionLabel }}</option>
                    @endforeach
                </select>
            </form>
        </div>
    @endif
</div>

<div id="pendingApprovalInfoModal" style="display: none; position: fixed; inset: 0; background: rgba(0,0,0,0.5); z-index: 1000; align-items: center; justify-content: center; padding: 20px;" onclick="if(event.target.id==='pendingApprovalInfoModal') document.getElementById('pendingApprovalInfoModal').style.display='none';">
    <div class="awaiting-links-modal-shell" style="max-width: 980px;">
        <div class="awaiting-links-modal-head">
            <div class="awaiting-links-modal-head-main">
                <div class="awaiting-links-modal-badge">PA</div>
                <div class="awaiting-links-modal-copy">
                    <h3>Pending Approval</h3>
                    <p>Students in this state have uploaded clinic requirements and are waiting for nurse review.</p>
                </div>
            </div>
            <button type="button" class="awaiting-links-modal-close" id="closePendingApprovalInfoModal" aria-label="Close pending approval modal" onclick="document.getElementById('pendingApprovalInfoModal').style.display='none';">
                <x-outline-icon name="x-mark" />
            </button>
        </div>
        <div class="awaiting-links-modal-body">
            <label class="readonly-modal-search" for="pendingApprovalSearchInput">
                <x-outline-icon name="magnifying-glass" />
                <input type="search" id="pendingApprovalSearchInput" data-readonly-modal-search="pendingApprovalRecordsList" placeholder="Search by name, email, or reference number">
            </label>
            <div class="pending-approval-list" id="pendingApprovalRecordsList">
                @forelse($records->filter(fn ($record) => in_array((string) ($record->record_key ?? (($record->record_source ?? 'health') . ':' . $record->id)), $pendingApprovalRecordIds, true)) as $readonlyRecord)
                    @php
                        $readonlySource = (string) ($readonlyRecord->record_source ?? 'health');
                        $readonlyIsEmployee = in_array($readonlySource, ['employee', 'staff'], true);
                        $readonlyHealthFormUrl = $readonlyIsEmployee
                            ? route('walkin.employeeHealthForm', ['employeeProfile' => $readonlyRecord->id, 'fresh' => 1])
                            : route('walkin.healthForm', ['healthProfile' => $readonlyRecord->id]);
                        $readonlyDocumentUrl = function ($documentKey) use ($readonlyIsEmployee, $readonlyRecord) {
                            return $readonlyIsEmployee
                                ? route('walkin.employeeDocument', ['employeeProfile' => $readonlyRecord->id, 'document' => $documentKey])
                                : route('walkin.document', ['healthProfile' => $readonlyRecord->id, 'document' => $documentKey]);
                        };
                        $readonlyDocs = [
                            '2x2 Photo' => [
                                'key' => 'student_photo',
                                'path' => $readonlyRecord->student_photo,
                                'url' => $readonlyRecord->student_photo ? $readonlyDocumentUrl('student_photo') : null,
                            ],
                            'Health Information Form' => [
                                'key' => 'health_form',
                                'path' => true,
                                'url' => $readonlyHealthFormUrl,
                            ],
                            'Medical Certificate' => [
                                'key' => 'medical_certificate',
                                'path' => $readonlyRecord->medical_certificate,
                                'url' => $readonlyRecord->medical_certificate ? $readonlyDocumentUrl('medical_certificate') : null,
                            ],
                            'Chest X-ray Result' => [
                                'key' => 'chest_xray_result',
                                'path' => $readonlyRecord->chest_xray_result,
                                'url' => $readonlyRecord->chest_xray_result ? $readonlyDocumentUrl('chest_xray_result') : null,
                            ],
                            'Health Declaration' => [
                                'key' => 'health_declaration',
                                'path' => $readonlyRecord->health_declaration,
                                'url' => $readonlyRecord->health_declaration ? $readonlyDocumentUrl('health_declaration') : null,
                            ],
                        ];

                        if ($readonlyRecord->pwd_id_proof) {
                            $readonlyDocs['PWD ID Proof'] = [
                                'key' => 'pwd_id_proof',
                                'path' => $readonlyRecord->pwd_id_proof,
                                'url' => $readonlyDocumentUrl('pwd_id_proof'),
                            ];
                        }

                        $readonlyUploadDocs = [
                            '2x2 Student Photo' => [
                                'key' => 'student_photo',
                                'path' => $readonlyRecord->student_photo,
                                'url' => $readonlyRecord->student_photo ? $readonlyDocumentUrl('student_photo') : null,
                            ],
                            'Health Information Form' => [
                                'key' => 'health_form',
                                'path' => true,
                                'url' => $readonlyHealthFormUrl,
                            ],
                            'Medical Certificate' => [
                                'key' => 'medical_certificate',
                                'path' => $readonlyRecord->medical_certificate,
                                'url' => $readonlyRecord->medical_certificate ? $readonlyDocumentUrl('medical_certificate') : null,
                            ],
                            'Chest X-ray Result' => [
                                'key' => 'chest_xray_result',
                                'path' => $readonlyRecord->chest_xray_result,
                                'url' => $readonlyRecord->chest_xray_result ? $readonlyDocumentUrl('chest_xray_result') : null,
                            ],
                            'Health Declaration' => [
                                'key' => 'health_declaration',
                                'path' => $readonlyRecord->health_declaration,
                                'url' => $readonlyRecord->health_declaration ? $readonlyDocumentUrl('health_declaration') : null,
                            ],
                        ];

                        if ($readonlyRecord->pwd_id_proof) {
                            $readonlyUploadDocs['PWD ID Proof'] = [
                                'key' => 'pwd_id_proof',
                                'path' => $readonlyRecord->pwd_id_proof,
                                'url' => $readonlyDocumentUrl('pwd_id_proof'),
                            ];
                        }

                        $readonlyHasCondition = $readonlyRecord->hasMedicalCondition();
                        $readonlyMedicalHistory = is_array($readonlyRecord->medical_history)
                            ? implode(', ', array_filter($readonlyRecord->medical_history))
                            : trim((string) $readonlyRecord->medical_history);
                        $readonlyMedicineAllergies = is_array($readonlyRecord->medicine_allergies)
                            ? implode(', ', array_filter($readonlyRecord->medicine_allergies))
                            : trim((string) $readonlyRecord->medicine_allergies);
                        $readonlyConditionItems = [];
                        if ($readonlyHasCondition) {
                            if (trim((string) $readonlyRecord->medical_condition_remarks) !== '') {
                                $readonlyConditionItems['Remarks'] = $readonlyRecord->medical_condition_remarks;
                            }
                            if (trim((string) $readonlyRecord->has_illness) !== '' && strcasecmp((string) $readonlyRecord->has_illness, 'No') !== 0) {
                                $readonlyConditionItems['Known Medical Illness'] = $readonlyRecord->has_illness;
                            }
                            if ($readonlyMedicalHistory !== '') {
                                $readonlyConditionItems['Medical History'] = $readonlyMedicalHistory;
                            }
                            if (trim((string) $readonlyRecord->other_illness) !== '') {
                                $readonlyConditionItems['Other Illness'] = $readonlyRecord->other_illness;
                            }
                            if (trim((string) $readonlyRecord->has_disability) !== '' && strcasecmp((string) $readonlyRecord->has_disability, 'No') !== 0) {
                                $readonlyConditionItems['Disability'] = $readonlyRecord->disability_type ?: $readonlyRecord->has_disability;
                            }
                            if (trim((string) $readonlyRecord->food_allergies) !== '') {
                                $readonlyConditionItems['Food Allergies'] = $readonlyRecord->food_allergies;
                            }
                            if ($readonlyMedicineAllergies !== '') {
                                $readonlyConditionItems['Medicine Allergies'] = $readonlyMedicineAllergies;
                            }
                            if (trim((string) $readonlyRecord->other_med_allergies) !== '') {
                                $readonlyConditionItems['Other Medicine Allergies'] = $readonlyRecord->other_med_allergies;
                            }
                            if (empty($readonlyConditionItems)) {
                                $readonlyConditionItems['Condition'] = 'With Medical Condition';
                            }
                        } else {
                            $readonlyConditionItems['Condition'] = 'No Medical Condition';
                        }
                        $readonlyReference = $readonlyRecord->reference_number ?: $readonlyRecord->student_number ?: optional($readonlyRecord->user)->student_number ?: '-';
                        $readonlyCourseName = trim((string) ($readonlyRecord->course_college ?: optional($readonlyRecord->user)->course ?: ''));
                        $readonlyYearSection = trim((string) implode('-', array_filter([
                            trim((string) optional($readonlyRecord->user)->year),
                            trim((string) optional($readonlyRecord->user)->section),
                        ])));
                        $readonlyCourseDisplay = trim($readonlyCourseName . ($readonlyYearSection !== '' ? ' ' . $readonlyYearSection : ''));
                        $readonlyRecordPayload = [
                            'id' => $readonlyRecord->id,
                            'name' => optional($readonlyRecord->user)->name ?: '-',
                            'email' => optional($readonlyRecord->user)->email ?: '-',
                            'reference_number' => $readonlyReference,
                            'student_id' => $readonlyRecord->student_id ?: optional($readonlyRecord->user)->student_id ?: '-',
                            'student_number' => optional($readonlyRecord->user)->student_number ?: optional($readonlyRecord->user)->student_id ?: '-',
                            'course' => $readonlyCourseDisplay !== '' ? $readonlyCourseDisplay : '-',
                            'status' => $readonlyRecord->clearance_status ?: 'For Verification',
                            'pending_reason' => $readonlyRecord->pending_reason ?: '',
                            'medical_condition_remarks' => $readonlyRecord->medical_condition_remarks ?: '',
                            'physical_assessment_status' => $readonlyRecord->physical_assessment_status ?: 'Not Yet Conducted',
                            'documents_valid' => (bool) $readonlyRecord->documents_valid,
                            'approve_url' => $readonlyIsEmployee ? '' : route('admin.update_clearance', $readonlyRecord->id),
                            'resubmission_url' => $readonlyIsEmployee ? '' : route('admin.health_profile.request_resubmission', $readonlyRecord->id),
                            'documents' => [
                            [
                                'title' => '2x2 Photo',
                                'url' => $readonlyRecord->student_photo ? $readonlyDocumentUrl('student_photo') : '',
                                    'meta' => [
                                    'Guideline' => 'Formal white-background photo.',
                                ],
                            ],
                            [
                                'title' => 'Health Information Form',
                                'url' => $readonlyHealthFormUrl,
                                'meta' => [
                                    'Type' => 'Official health form layout',
                                ],
                            ],
                            [
                                'title' => 'Medical Certificate',
                                'url' => $readonlyRecord->medical_certificate ? $readonlyDocumentUrl('medical_certificate') : '',
                                'meta' => [
                                    'Doctor' => $readonlyRecord->doctor_name ?: '-',
                                    'Certificate Date' => optional($readonlyRecord->med_cert_date)->format('M d, Y') ?: '-',
                                    'Findings' => $readonlyRecord->med_cert_findings ?: '-',
                                ],
                            ],
                            [
                                'title' => 'Chest X-ray Result',
                                'url' => $readonlyRecord->chest_xray_result ? $readonlyDocumentUrl('chest_xray_result') : '',
                                'meta' => [
                                    'Exam Date' => optional($readonlyRecord->xray_date)->format('M d, Y') ?: '-',
                                    'Findings' => $readonlyRecord->xray_findings ?: '-',
                                ],
                            ],
                            [
                                'title' => 'Health Declaration',
                                'url' => $readonlyRecord->health_declaration ? $readonlyDocumentUrl('health_declaration') : '',
                                    'meta' => [
                                        'Status' => $readonlyRecord->health_declaration ? 'Uploaded' : 'Missing / Not yet uploaded',
                                    ],
                                ],
                            ],
                        ];
                    @endphp
                    <article
                        class="readonly-record-card"
                        data-health-row
                        data-health-id="{{ $readonlyRecord->id }}"
                        data-record-payload="{{ e(json_encode($readonlyRecordPayload)) }}"
                        data-search-text="{{ strtolower(trim((string) (optional($readonlyRecord->user)->name . ' ' . optional($readonlyRecord->user)->email . ' ' . $readonlyReference))) }}"
                    >
                        <div class="readonly-record-head">
                            <div>
                                <h4 class="readonly-record-name">{{ optional($readonlyRecord->user)->name ?: 'Unnamed Student' }}</h4>
                                <p class="readonly-record-sub">{{ optional($readonlyRecord->user)->email ?: '-' }}</p>
                            </div>
                            <div class="readonly-record-meta">
                                <div class="readonly-record-pill reference-pill">
                                    <span>Reference Number</span>
                                    <strong class="readonly-reference-value">
                                        <span>{{ $readonlyReference }}</span>
                                        @if($readonlyReference !== '-')
                                            <button type="button" class="readonly-copy-btn" data-copy-reference="{{ $readonlyReference }}" aria-label="Copy reference number">
                                                <x-outline-icon name="clipboard-document-list" />
                                            </button>
                                        @endif
                                    </strong>
                                </div>
                                <div class="readonly-record-pill condition-pill {{ $readonlyHasCondition ? 'has-condition' : '' }}">
                                    <span>Condition</span>
                                    <strong>{{ $readonlyHasCondition ? 'With Condition' : 'No Condition' }}</strong>
                                </div>
                                <div class="readonly-record-actions">
                                    <button
                                        type="button"
                                        class="readonly-expand-btn readonly-review-btn js-open-verify-modal"
                                        data-review-name="{{ optional($readonlyRecord->user)->name ?: 'Unnamed Student' }}"
                                        data-review-email="{{ optional($readonlyRecord->user)->email ?: '-' }}"
                                        data-review-reference="{{ $readonlyReference }}"
                                        data-review-course="{{ $readonlyCourseDisplay !== '' ? $readonlyCourseDisplay : '-' }}"
                                        data-review-student-id="{{ $readonlyRecord->student_id ?: optional($readonlyRecord->user)->student_id ?: optional($readonlyRecord->user)->student_number ?: '-' }}"
                                        data-review-approve-url="{{ $readonlyIsEmployee ? '' : route('admin.update_clearance', $readonlyRecord->id) }}"
                                        data-review-resubmission-url="{{ $readonlyIsEmployee ? '' : route('admin.health_profile.request_resubmission', $readonlyRecord->id) }}"
                                    >
                                        <span>View Info</span>
                                    </button>
                                </div>
                            </div>
                        </div>
                        <div class="readonly-record-details">
                            <h5 class="readonly-docs-title">Uploaded Files Checklist</h5>
                            <ul class="readonly-doc-list">
                                @foreach($readonlyDocs as $docLabel => $document)
                                    <li>
                                        @if($document['path'])
                                            <a
                                                class="readonly-doc-link"
                                                href="{{ $document['url'] }}"
                                                target="_blank"
                                                rel="noopener noreferrer"
                                            >
                                                <span>{{ $docLabel }}</span>
                                                <span>Open</span>
                                            </a>
                                        @else
                                            <span class="readonly-doc-missing">
                                                <span>{{ $docLabel }}</span>
                                                <span>No document uploaded</span>
                                            </span>
                                        @endif
                                    </li>
                                @endforeach
                            </ul>
                        </div>
                        <template data-review-documents-template>
                            @foreach($readonlyUploadDocs as $docLabel => $document)
                                @if($document['path'])
                                    @php
                                        $documentUrl = $document['url'];
                                        $documentExtension = strtolower(pathinfo((string) $document['path'], PATHINFO_EXTENSION));
                                        $isPreviewImage = in_array($documentExtension, ['jpg', 'jpeg', 'png', 'gif', 'webp'], true);
                                    @endphp
                                    <a
                                        class="verification-doc-card {{ ($document['key'] ?? '') === 'health_form' ? 'health-form-doc-card' : '' }}"
                                        href="{{ $documentUrl }}"
                                        target="_blank"
                                        rel="noopener noreferrer"
                                    >
                                        @if(($document['key'] ?? '') === 'health_form')
                                            <span class="health-form-doc-preview" aria-hidden="true">
                                                <span class="health-form-doc-title">
                                                    <span>Health</span>
                                                    <span>Information</span>
                                                    <span>Form</span>
                                                </span>
                                            </span>
                                        @else
                                            <span class="verification-doc-preview">
                                                @if($isPreviewImage)
                                                <img src="{{ $documentUrl }}" alt="{{ $docLabel }} preview" loading="lazy">
                                                @else
                                                <iframe src="{{ $documentUrl }}" title="{{ $docLabel }} preview" loading="lazy"></iframe>
                                                @endif
                                            </span>
                                        @endif
                                        <strong>{{ $docLabel }}</strong>
                                        <span>Open in new tab</span>
                                    </a>
                                @else
                                    <div class="verification-doc-card">
                                        <x-outline-icon name="x-mark" />
                                        <strong>{{ $docLabel }}</strong>
                                        <span>Missing upload</span>
                                    </div>
                                @endif
                            @endforeach
                        </template>
                        <template data-review-condition-template>
                            @foreach($readonlyConditionItems as $conditionLabel => $conditionValue)
                                <div class="verify-condition-item">
                                    <span>{{ $conditionLabel }}</span>
                                    <strong>{{ $conditionValue }}</strong>
                                </div>
                            @endforeach
                        </template>
                    </article>
                @empty
                    <div class="pending-approval-empty">No students are currently waiting for approval.</div>
                @endforelse
                <div class="readonly-search-empty" data-readonly-search-empty>No matching pending approval records found.</div>
            </div>
            <div
                class="readonly-modal-pagination"
                data-readonly-pagination="pendingApprovalRecordsList"
                data-page-size="5"
                aria-label="Pending approval pagination"
            >
                <span class="readonly-pagination-summary" data-pagination-summary>Showing 0-0 of 0</span>
                <div class="readonly-pagination-actions">
                    <button type="button" class="readonly-pagination-btn" data-pagination-prev aria-label="Previous page">&larr;</button>
                    <button type="button" class="readonly-pagination-btn is-active" data-pagination-current>1</button>
                    <button type="button" class="readonly-pagination-btn" data-pagination-next aria-label="Next page">&rarr;</button>
                </div>
                <select class="readonly-pagination-per-page-select" data-pagination-page-size aria-label="Pending approval records per page">
                    <option value="5">5 per page</option>
                    <option value="10">10 per page</option>
                    <option value="15">15 per page</option>
                    <option value="20">20 per page</option>
                    <option value="all">Show all</option>
                </select>
            </div>
        </div>
    </div>
</div>

<div id="pendingConditionalInfoModal" style="display: none; position: fixed; inset: 0; background: rgba(0,0,0,0.5); z-index: 1000; align-items: center; justify-content: center; padding: 20px;" onclick="if(event.target.id==='pendingConditionalInfoModal') document.getElementById('pendingConditionalInfoModal').style.display='none';">
    <div class="awaiting-links-modal-shell" style="max-width: 980px;">
        <div class="awaiting-links-modal-head">
            <div class="awaiting-links-modal-head-main">
                <div class="awaiting-links-modal-badge">PC</div>
                <div class="awaiting-links-modal-copy">
                    <h3>Pending Compliance / Conditional</h3>
                    <p>Read-only view of students needing correction, compliance, or further medical assessment.</p>
                </div>
            </div>
            <button type="button" class="awaiting-links-modal-close" id="closePendingConditionalInfoModal" aria-label="Close pending compliance modal" onclick="document.getElementById('pendingConditionalInfoModal').style.display='none';">
                <x-outline-icon name="x-mark" />
            </button>
        </div>
        <div class="awaiting-links-modal-body">
            <label class="readonly-modal-search" for="pendingComplianceSearchInput">
                <x-outline-icon name="magnifying-glass" />
                <input type="search" id="pendingComplianceSearchInput" data-readonly-modal-search="pendingComplianceRecordsList" placeholder="Search by name, email, or reference number">
            </label>
            <div class="pending-approval-list" id="pendingComplianceRecordsList">
                @forelse($records->filter(fn ($record) => in_array((string) ($record->record_key ?? (($record->record_source ?? 'health') . ':' . $record->id)), $pendingConditionalRecordIds, true)) as $readonlyRecord)
                    @php
                        $readonlySource = (string) ($readonlyRecord->record_source ?? 'health');
                        $readonlyIsEmployee = in_array($readonlySource, ['employee', 'staff'], true);
                        $pendingComplianceDocumentUrl = function ($documentKey) use ($readonlyIsEmployee, $readonlyRecord) {
                            return $readonlyIsEmployee
                                ? route('walkin.employeeDocument', ['employeeProfile' => $readonlyRecord->id, 'document' => $documentKey])
                                : route('walkin.document', ['healthProfile' => $readonlyRecord->id, 'document' => $documentKey]);
                        };
                        $readonlyHasCondition = $readonlyRecord->hasMedicalCondition();
                        $readonlyReference = $readonlyRecord->reference_number ?: $readonlyRecord->student_number ?: optional($readonlyRecord->user)->student_number ?: '-';
                        $pendingComplianceDocs = [
                            '2x2 Student Photo' => ['key' => 'student_photo', 'path' => $readonlyRecord->student_photo],
                            'Health Declaration' => ['key' => 'health_declaration', 'path' => $readonlyRecord->health_declaration],
                            'Medical Certificate' => ['key' => 'medical_certificate', 'path' => $readonlyRecord->medical_certificate],
                            'Chest X-ray Result' => ['key' => 'chest_xray_result', 'path' => $readonlyRecord->chest_xray_result],
                            'PWD ID Proof' => ['key' => 'pwd_id_proof', 'path' => $readonlyRecord->pwd_id_proof],
                            'Medical Assessment Copy' => ['key' => 'medical_assessment_upload', 'path' => $readonlyRecord->medical_assessment_upload],
                        ];
                        $pendingComplianceUploadedDocs = collect($pendingComplianceDocs)
                            ->filter(fn ($document) => filled($document['path']))
                            ->all();
                        $imagePreviewExtensions = ['jpg', 'jpeg', 'png', 'gif', 'webp'];
                    @endphp
                    <article class="readonly-record-card" data-search-text="{{ strtolower(trim((string) (optional($readonlyRecord->user)->name . ' ' . optional($readonlyRecord->user)->email . ' ' . $readonlyReference))) }}">
                        <div class="readonly-record-head">
                            <div>
                                <h4 class="readonly-record-name">{{ optional($readonlyRecord->user)->name ?: 'Unnamed Student' }}</h4>
                                <p class="readonly-record-sub">{{ optional($readonlyRecord->user)->email ?: '-' }}</p>
                            </div>
                            <div class="readonly-record-meta">
                                <div class="readonly-record-pill reference-pill">
                                    <span>Reference Number</span>
                                    <strong class="readonly-reference-value">
                                        <span>{{ $readonlyReference }}</span>
                                        @if($readonlyReference !== '-')
                                            <button type="button" class="readonly-copy-btn" data-copy-reference="{{ $readonlyReference }}" aria-label="Copy reference number">
                                                <x-outline-icon name="clipboard-document-list" />
                                            </button>
                                        @endif
                                    </strong>
                                </div>
                                <div class="readonly-record-pill condition-pill {{ $readonlyHasCondition ? 'has-condition' : '' }}">
                                    <span>Condition</span>
                                    <strong>{{ $readonlyHasCondition ? 'With Condition' : 'No Condition' }}</strong>
                                </div>
                                <div class="readonly-record-actions">
                                    <button
                                        type="button"
                                        class="readonly-expand-btn"
                                        aria-expanded="false"
                                        onclick="event.stopPropagation(); const card = this.closest('.readonly-record-card'); if (card) { const expanded = !card.classList.contains('is-expanded'); card.classList.toggle('is-expanded', expanded); this.setAttribute('aria-expanded', expanded ? 'true' : 'false'); const label = this.querySelector('span'); if (label) label.textContent = expanded ? 'Hide' : 'View'; }"
                                    >
                                        <span>View</span>
                                    </button>
                                </div>
                            </div>
                        </div>
                        <div class="readonly-record-details">
                            <div class="readonly-record-grid">
                                <div class="readonly-field"><span>Status Flag</span><strong>Conditional / Flagged</strong></div>
                                <div class="readonly-field"><span>Previous Nurse Disapproval Notes</span><strong>{{ $readonlyRecord->pending_reason ?: '-' }}</strong></div>
                                <div class="readonly-field"><span>Student Full Name</span><strong>{{ optional($readonlyRecord->user)->name ?: 'Unnamed Student' }}</strong></div>
                                <div class="readonly-field"><span>Submission Reference Number</span><strong>{{ $readonlyRecord->reference_number ?: $readonlyRecord->student_number ?: optional($readonlyRecord->user)->student_number ?: '-' }}</strong></div>
                                <div class="readonly-field"><span>Last Updated Nurse Tracking Remarks</span><strong>{{ $readonlyRecord->medical_condition_remarks ?: $readonlyRecord->pending_reason ?: '-' }}</strong></div>
                                <div class="readonly-field readonly-doc-preview-field">
                                    <span>Uploaded Documents</span>
                                    <div class="readonly-doc-preview-shell">
                                        <div class="readonly-doc-preview-list">
                                            @forelse($pendingComplianceUploadedDocs as $docLabel => $document)
                                                @php
                                                    $documentUrl = $pendingComplianceDocumentUrl($document['key']);
                                                    $documentExtension = strtolower(pathinfo((string) $document['path'], PATHINFO_EXTENSION));
                                                    $isImagePreview = in_array($documentExtension, $imagePreviewExtensions, true);
                                                @endphp
                                                <a
                                                    href="{{ $documentUrl }}"
                                                    target="_blank"
                                                    rel="noopener noreferrer"
                                                    class="readonly-doc-preview-btn"
                                                >
                                                    <span class="readonly-doc-preview-thumb">
                                                        @if($isImagePreview)
                                                            <img src="{{ $documentUrl }}" alt="{{ $docLabel }} preview">
                                                        @else
                                                            <x-outline-icon name="document-text" />
                                                        @endif
                                                    </span>
                                                    <span>{{ $docLabel }}</span>
                                                    <small>Open in new tab</small>
                                                </a>
                                            @empty
                                                <div class="readonly-doc-preview-empty">No uploaded documents are available.</div>
                                            @endforelse
                                        </div>
                                    </div>
                                </div>
                                <div class="readonly-field readonly-final-review-field">
                                    <span>Move Record</span>
                                    <div class="readonly-review-routing-actions">
                                        <form method="POST" action="{{ route('admin.health_profile.for_final_review', $readonlyRecord->id) }}">
                                            @csrf
                                            <button type="submit" class="readonly-final-review-btn">
                                                <x-outline-icon name="check" />
                                                For Final Review
                                            </button>
                                        </form>
                                        <form method="POST" action="{{ route('admin.health_profile.for_approval', $readonlyRecord->id) }}">
                                            @csrf
                                            <button type="submit" class="readonly-final-review-btn">
                                                <x-outline-icon name="clipboard-document-list" />
                                                For Approval
                                            </button>
                                        </form>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </article>
                @empty
                    <div class="pending-approval-empty">No students are currently pending compliance.</div>
                @endforelse
                <div class="readonly-search-empty" data-readonly-search-empty>No matching pending compliance records found.</div>
            </div>
            <div
                class="readonly-modal-pagination"
                data-readonly-pagination="pendingComplianceRecordsList"
                data-page-size="5"
                aria-label="Pending compliance pagination"
            >
                <span class="readonly-pagination-summary" data-pagination-summary>Showing 0-0 of 0</span>
                <div class="readonly-pagination-actions">
                    <button type="button" class="readonly-pagination-btn" data-pagination-prev aria-label="Previous page">&larr;</button>
                    <button type="button" class="readonly-pagination-btn is-active" data-pagination-current>1</button>
                    <button type="button" class="readonly-pagination-btn" data-pagination-next aria-label="Next page">&rarr;</button>
                </div>
                <select class="readonly-pagination-per-page-select" data-pagination-page-size aria-label="Pending compliance records per page">
                    <option value="5">5 per page</option>
                    <option value="10">10 per page</option>
                    <option value="15">15 per page</option>
                    <option value="20">20 per page</option>
                    <option value="all">Show all</option>
                </select>
            </div>
        </div>
    </div>
</div>

<div class="resubmission-progress-overlay" id="readonlyResubmissionProgress" aria-hidden="true" aria-live="assertive">
    <div class="resubmission-progress-card">
        <div class="resubmission-progress-mark" aria-hidden="true">
            <x-outline-icon name="clipboard-document-list" />
        </div>
        <strong>Preparing Resubmission</strong>
        <span>Clearing selected document references and notifying the student to upload replacements.</span>
    </div>
</div>

<div class="verify-approval-modal" id="verifyApprovalModal" aria-hidden="true">
    <div class="verify-approval-modal-card">
        <div class="verify-approval-modal-head">
            <div class="verify-approval-modal-head-main">
                <div class="verify-approval-modal-badge">VI</div>
                <div>
                    <h3 class="verify-approval-modal-title">View Submitted Requirements</h3>
                    <p class="verify-approval-modal-copy">Review the student information and uploaded clinic requirement files.</p>
                </div>
            </div>
            <button type="button" class="verify-approval-modal-close" id="verifyApprovalCloseBtn" aria-label="Close verification popup">
                <x-outline-icon name="x-mark" />
            </button>
        </div>
        <div class="verify-approval-body">
            <div class="verify-approval-student">
                <div class="verify-approval-meta">
                    <div class="verify-approval-meta-k">Student Name</div>
                    <div class="verify-approval-meta-v" id="verifyApprovalStudentName">-</div>
                </div>
                <div class="verify-approval-meta">
                    <div class="verify-approval-meta-k">Email</div>
                    <div class="verify-approval-meta-v" id="verifyApprovalStudentNumber">-</div>
                </div>
                <div class="verify-approval-meta">
                    <div class="verify-approval-meta-k">Course</div>
                    <div class="verify-approval-meta-v" id="verifyApprovalStudentCourse">-</div>
                </div>
                <div class="verify-approval-meta">
                    <div class="verify-approval-meta-k">Reference Number</div>
                    <div class="verify-approval-meta-v" id="verifyApprovalReferenceNumber">-</div>
                </div>
            </div>

            <div class="verify-approval-doc-wrap">
                <p class="verify-approval-doc-title">Uploaded Documents</p>
                <div class="verification-doc-grid" id="verificationDocsGrid"></div>
            </div>

            <div class="verify-condition-wrap">
                <p class="verify-condition-title">Medical Conditions</p>
                <div class="verify-condition-grid" id="verificationConditionGrid"></div>
            </div>

            <form method="POST" class="verify-approval-review-form verify-resubmission-only-form" id="verifyDocumentResubmissionForm">
                @csrf
                <input type="hidden" name="pending_reason" id="verifyDocumentResubmissionReason" value="Document Resubmission">
                <input type="hidden" name="return_to" value="health_records">

                <p class="verify-resubmission-help-note">Use this only when uploaded files are blurred, unreadable, unsigned, incorrect, or need replacement.</p>
                <label class="verify-check-row verify-resubmission-toggle-row">
                    <input type="checkbox" id="verifyNeedsDocumentResubmission" value="1">
                    <span>
                        <strong>Document Resubmission</strong>
                    </span>
                </label>

                <div class="verify-resubmission-panel" id="verifyDocumentResubmissionPanel">
                    <p class="verify-resubmission-title">Documents to Resubmit <span class="required">*</span></p>
                    <div class="verify-resubmission-options">
                        <label class="verify-resubmission-option">
                            <input type="checkbox" name="resubmission_required_documents[]" value="student_photo">
                            <span>2x2 Photo</span>
                        </label>
                        <label class="verify-resubmission-option">
                            <input type="checkbox" name="resubmission_required_documents[]" value="health_declaration">
                            <span>Health Declaration</span>
                        </label>
                        <label class="verify-resubmission-option">
                            <input type="checkbox" name="resubmission_required_documents[]" value="medical_certificate">
                            <span>Medical Certificate</span>
                        </label>
                        <label class="verify-resubmission-option">
                            <input type="checkbox" name="resubmission_required_documents[]" value="chest_xray_result">
                            <span>Chest X-ray Result</span>
                        </label>
                        <label class="verify-resubmission-option">
                            <input type="checkbox" name="resubmission_required_documents[]" value="pwd_id_proof">
                            <span>PWD ID Proof</span>
                        </label>
                        <label class="verify-resubmission-option">
                            <input type="checkbox" id="verifyNeedsHealthFormCorrection" name="needs_health_form_correction" value="1">
                            <span>Health Form Correction</span>
                        </label>
                    </div>
                    <label class="verify-textarea-field">
                        <span>Remarks <small>(Optional)</small></span>
                        <textarea id="verifyDocumentResubmissionRemarks" rows="3" placeholder="Optional note for the student or clinic tracking."></textarea>
                    </label>
                </div>

                <div class="verify-approval-actions verify-resubmission-only-actions is-hidden">
                    <button type="submit" class="verify-approval-btn verify-approval-btn-resubmit" id="verifyDocumentResubmissionSubmit" disabled>
                        Request Resubmission
                    </button>
                </div>
            </form>

        </div>
    </div>
</div>

<div class="health-filter-modal" id="healthFilterModal" aria-hidden="true">
    <div class="health-filter-modal-card">
        <div class="health-filter-modal-head">
            <div class="health-filter-modal-head-main">
                <span class="health-filter-modal-badge" aria-hidden="true">
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 3c2.755 0 5.455.232 8.083.678.533.09.917.556.917 1.096v1.044a2.25 2.25 0 0 1-.659 1.591l-5.432 5.432a2.25 2.25 0 0 0-.659 1.591v2.927a2.25 2.25 0 0 1-1.244 2.013L9.75 21v-6.568a2.25 2.25 0 0 0-.659-1.591L3.659 7.409A2.25 2.25 0 0 1 3 5.818V4.774c0-.54.384-1.006.917-1.096A48.32 48.32 0 0 1 12 3Z" />
                    </svg>
                </span>
                <div>
                    <h3 class="health-filter-modal-title">Filter Health Records</h3>
                    <p class="health-filter-modal-copy">Narrow the issued medical clearance list by user type, course, month, or year level.</p>
                </div>
            </div>
            <button type="button" class="health-filter-modal-close" id="healthFilterCloseBtn" aria-label="Close filter popup">
                <x-outline-icon name="x-mark" />
            </button>
        </div>

        <form method="GET" class="health-filter-form" id="healthFilterForm">
            <div class="health-filter-field">
                <label for="userTypeFilter">User Type</label>
                <div class="health-filter-select-wrap">
                    <select id="userTypeFilter" name="user_type" class="health-filter-select health-filter-custom-source">
                        <option value="">All User Types</option>
                        @foreach(($userTypeOptions ?? collect()) as $userTypeValue => $userTypeLabel)
                            <option value="{{ $userTypeValue }}" {{ ($userTypeFilter ?? '') === $userTypeValue ? 'selected' : '' }}>{{ $userTypeLabel }}</option>
                        @endforeach
                    </select>
                </div>
            </div>
            <div class="health-filter-field">
                <label for="courseFilter">Course</label>
                <div class="health-filter-select-wrap">
                    <select id="courseFilter" name="course" class="health-filter-select health-filter-custom-source">
                        <option value="">All Courses</option>
                        @foreach(($courseOptions ?? collect()) as $courseOption)
                            @php($courseOptionCode = $formatHealthCourseCode($courseOption))
                            <option value="{{ $courseOption }}" {{ ($courseFilter ?? '') === $courseOption ? 'selected' : '' }}>
                                {{ $courseOptionCode !== '' && $courseOptionCode !== $courseOption ? $courseOptionCode . ' - ' . $courseOption : $courseOption }}
                            </option>
                        @endforeach
                    </select>
                </div>
            </div>
            <div class="health-filter-field">
                <label for="yearFilter">Year Level</label>
                <div class="health-filter-select-wrap is-year-level">
                    <select id="yearFilter" name="year" class="health-filter-select health-filter-custom-source">
                        <option value="">All Year Levels</option>
                        @foreach(($yearOptions ?? collect()) as $yearOption)
                            <option value="{{ $yearOption }}" {{ (string) ($yearFilter ?? '') === (string) $yearOption ? 'selected' : '' }}>{{ $yearOption }}</option>
                        @endforeach
                    </select>
                </div>
            </div>
            <div class="health-filter-field">
                <label for="monthFilter">Time</label>
                <input
                    type="month"
                    id="monthFilter"
                    name="month"
                    value="{{ $monthFilter ?? '' }}"
                    class="health-filter-select"
                >
            </div>
            <div class="health-filter-actions">
                <a href="{{ route('admin.health_records') }}" class="health-filter-btn health-filter-btn-reset">Reset</a>
                <button type="submit" class="health-filter-btn" style="color: #ffffff !important;">Apply</button>
            </div>
        </form>
    </div>
</div>
@endsection

@push('scripts')
<script>
    (function () {
        function closestMatch(element, selector) {
            while (element && element !== document) {
                if (element.matches && element.matches(selector)) {
                    return element;
                }
                element = element.parentNode;
            }
            return null;
        }

        function getNode(id) {
            return document.getElementById(id);
        }

        function setText(id, value) {
            var node = getNode(id);
            if (node) {
                node.textContent = value || '-';
            }
        }

        function setValue(id, value) {
            var node = getNode(id);
            if (node) {
                node.value = value || '';
            }
        }

        function escapeValue(value) {
            return String(value == null ? '' : value).replace(/[&<>"']/g, function (character) {
                return {
                    '&': '&amp;',
                    '<': '&lt;',
                    '>': '&gt;',
                    '"': '&quot;',
                    "'": '&#039;'
                }[character];
            });
        }

        function filledValue(primary, fallback) {
            var value = String(primary == null ? '' : primary).trim();
            if (value === '' || value === '-') {
                value = String(fallback == null ? '' : fallback).trim();
            }
            return value || '-';
        }

        function markCopied(button) {
            var originalLabel = button.getAttribute('aria-label') || 'Copy reference number';
            button.classList.add('is-copied');
            button.setAttribute('aria-label', 'Copied');
            window.setTimeout(function () {
                button.classList.remove('is-copied');
                button.setAttribute('aria-label', originalLabel);
            }, 1200);
        }

        function fallbackCopy(text, button) {
            var tempInput = document.createElement('textarea');
            tempInput.value = text;
            tempInput.setAttribute('readonly', 'readonly');
            tempInput.style.position = 'fixed';
            tempInput.style.left = '-9999px';
            tempInput.style.top = '0';
            document.body.appendChild(tempInput);
            tempInput.focus();
            tempInput.select();

            try {
                document.execCommand('copy');
                markCopied(button);
            } finally {
                document.body.removeChild(tempInput);
            }
        }

        window.copyHealthReferenceFromButton = function (button) {
            var text = (button && button.getAttribute('data-copy-reference') || '').trim();
            if (!text) {
                return false;
            }

            if (navigator.clipboard && window.isSecureContext) {
                navigator.clipboard.writeText(text).then(function () {
                    markCopied(button);
                }).catch(function () {
                    fallbackCopy(text, button);
                });
            } else {
                fallbackCopy(text, button);
            }

            return false;
        };

        window.openHealthReviewFromButton = function (button) {
            var row = closestMatch(button, '[data-health-row]');
            var payload = {};
            var approvalModal = getNode('pendingApprovalInfoModal');
            var conditionalModal = getNode('pendingConditionalInfoModal');
            var docsGrid = getNode('verificationDocsGrid');
            var conditionGrid = getNode('verificationConditionGrid');
            var docsValid = getNode('verifyDocumentsValid');
            var approveBtn = getNode('verifyApprovalApproveBtn');
            var assessmentStatus = getNode('verifyAssessmentStatus');
            var verifyModal = getNode('verifyApprovalModal');
            var form = getNode('verifyApprovalForm');
            var resubmissionPanel = getNode('verifyResubmissionPanel');
            var rawPayload = row ? row.getAttribute('data-record-payload') : '';

            if (!row || !verifyModal) {
                return false;
            }

            try {
                payload = JSON.parse(rawPayload || '{}');
            } catch (error) {
                payload = {};
            }

            payload.name = filledValue(payload.name, button.getAttribute('data-review-name'));
            payload.email = filledValue(payload.email, button.getAttribute('data-review-email'));
            payload.reference_number = filledValue(payload.reference_number, button.getAttribute('data-review-reference'));
            payload.course = filledValue(payload.course, button.getAttribute('data-review-course'));
            payload.student_id = filledValue(payload.student_id, button.getAttribute('data-review-student-id'));
            payload.approve_url = filledValue(payload.approve_url, button.getAttribute('data-review-approve-url'));
            payload.resubmission_url = filledValue(payload.resubmission_url, button.getAttribute('data-review-resubmission-url'));

            setText('verifyApprovalStudentName', payload.name || '-');
            setText('verifyApprovalStudentNumber', payload.email || '-');
            setText('verifyApprovalStudentCourse', payload.course || '-');
            setText('verifyApprovalReferenceNumber', payload.reference_number || '-');

            if (form) {
                form.setAttribute('action', payload.approve_url || '');
            }

            var documentResubmissionForm = getNode('verifyDocumentResubmissionForm');
            var documentResubmissionToggle = getNode('verifyNeedsDocumentResubmission');
            var documentResubmissionPanel = getNode('verifyDocumentResubmissionPanel');
            var documentResubmissionSubmit = getNode('verifyDocumentResubmissionSubmit');
            var documentResubmissionRemarks = getNode('verifyDocumentResubmissionRemarks');
            var documentResubmissionReason = getNode('verifyDocumentResubmissionReason');

            if (documentResubmissionForm) {
                documentResubmissionForm.setAttribute('action', payload.resubmission_url || '');
            }
            if (documentResubmissionToggle) {
                documentResubmissionToggle.checked = false;
            }
            if (documentResubmissionPanel) {
                documentResubmissionPanel.classList.remove('is-open');
            }
            if (documentResubmissionSubmit) {
                documentResubmissionSubmit.disabled = true;
            }
            if (documentResubmissionRemarks) {
                documentResubmissionRemarks.value = '';
            }
            if (documentResubmissionReason) {
                documentResubmissionReason.value = 'Document Resubmission';
            }
            Array.prototype.forEach.call(document.querySelectorAll('#verifyDocumentResubmissionForm input[name="resubmission_required_documents[]"]'), function (input) {
                input.checked = false;
            });
            var healthFormCorrectionInput = getNode('verifyNeedsHealthFormCorrection');
            if (healthFormCorrectionInput) {
                healthFormCorrectionInput.checked = false;
            }

            setValue('verifyMedicalRemarks', payload.medical_condition_remarks || '');
            setValue('verifyPendingReason', '');
            setValue('verifyPendingReasonSelect', '');
            setValue('verifyPendingReasonOther', '');
            setValue('verifyAssessmentStatus', payload.physical_assessment_status || 'Not Yet Conducted');
            setValue('verifyClearanceStatus', 'Fully Cleared');

            if (docsValid) {
                docsValid.checked = Boolean(payload.documents_valid);
            }

            if (resubmissionPanel) {
                resubmissionPanel.classList.remove('is-open');
            }

            Array.prototype.forEach.call(document.querySelectorAll('input[name="resubmission_required_documents[]"]'), function (input) {
                input.checked = false;
            });

            if (docsGrid) {
                var documentsTemplate = row.querySelector('[data-review-documents-template]');
                if (documentsTemplate) {
                    docsGrid.innerHTML = documentsTemplate.innerHTML.trim();
                } else {
                    var docs = Array.isArray(payload.documents) ? payload.documents : [];
                    docsGrid.innerHTML = docs.map(function (doc) {
                        if (doc && doc.url) {
                            var docTitle = doc.title || 'Requirement';
                            var isHealthForm = String(docTitle).toLowerCase() === 'health information form';
                            if (isHealthForm) {
                                return '<a class="verification-doc-card health-form-doc-card" href="' + escapeValue(doc.url) + '" target="_blank" rel="noopener noreferrer"><span class="health-form-doc-preview" aria-hidden="true"><span class="health-form-doc-title"><span>Health</span><span>Information</span><span>Form</span></span></span><strong>' + escapeValue(docTitle) + '</strong><span>Open in new tab</span></a>';
                            }

                            return '<a class="verification-doc-card" href="' + escapeValue(doc.url) + '" target="_blank" rel="noopener noreferrer"><svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M19.5 14.25v-2.625a3.375 3.375 0 0 0-3.375-3.375h-1.5A1.125 1.125 0 0 1 13.5 7.125v-1.5A3.375 3.375 0 0 0 10.125 2.25H8.25m2.25 0H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 0 0-9-9Z" /></svg><strong>' + escapeValue(doc.title || 'Requirement') + '</strong><span>Open in new tab</span></a>';
                        }

                        return '<div class="verification-doc-card"><svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M18.364 18.364A9 9 0 0 0 5.636 5.636m12.728 12.728A9 9 0 0 1 5.636 5.636m12.728 12.728L5.636 5.636" /></svg><strong>' + escapeValue(doc && doc.title ? doc.title : 'Requirement') + '</strong><span>Missing upload</span></div>';
                    }).join('');
                }
            }

            if (conditionGrid) {
                var conditionTemplate = row.querySelector('[data-review-condition-template]');
                conditionGrid.innerHTML = conditionTemplate
                    ? conditionTemplate.innerHTML.trim()
                    : '<div class="verify-condition-item"><span>Condition</span><strong>No Medical Condition</strong></div>';
            }

            if (approveBtn && assessmentStatus && docsValid) {
                approveBtn.disabled = !(docsValid.checked && assessmentStatus.value === 'Completed / Passed');
            }

            verifyModal.classList.add('is-open');
            verifyModal.setAttribute('aria-hidden', 'false');
            verifyModal.style.display = 'flex';

            return false;
        };

        document.addEventListener('click', function (event) {
            var copyButton = closestMatch(event.target, '[data-copy-reference]');
            if (copyButton) {
                event.preventDefault();
                event.stopImmediatePropagation();
                window.copyHealthReferenceFromButton(copyButton);
                return;
            }

            var reviewButton = closestMatch(event.target, '.js-open-verify-modal');
            if (reviewButton) {
                event.preventDefault();
                event.stopImmediatePropagation();
                window.openHealthReviewFromButton(reviewButton);
            }
        }, true);

        document.addEventListener('submit', function (event) {
            var form = closestMatch(event.target, '[data-readonly-resubmission-form]');
            if (!form || form.dataset.submitting === '1') {
                return;
            }

            event.preventDefault();
            if (!form.querySelector('input[name="resubmission_required_documents[]"]:checked')) {
                alert('Select at least one document for resubmission.');
                return;
            }
            if (!confirm('Request resubmission and remove the selected uploaded document references from this record?')) {
                return;
            }

            var overlay = getNode('readonlyResubmissionProgress');
            var submitButton = form.querySelector('button[type="submit"]');
            form.dataset.submitting = '1';
            if (submitButton) {
                submitButton.disabled = true;
                submitButton.textContent = 'Submitting...';
            }
            if (overlay) {
                overlay.classList.add('is-open');
                overlay.setAttribute('aria-hidden', 'false');
            }

            window.setTimeout(function () {
                form.submit();
            }, 700);
        });
    })();

    // Simple search toggle function
    function toggleHealthSearch() {
        const shell = document.getElementById('healthRecordsSearchShell');
        const toggle = document.getElementById('healthRecordsSearchToggle');
        const input = document.getElementById('recordSearch');

        if (shell && toggle) {
            const isOpen = shell.classList.contains('is-open');
            shell.classList.toggle('is-open', !isOpen);
            toggle.setAttribute('aria-expanded', !isOpen ? 'true' : 'false');

            if (!isOpen && input) {
                setTimeout(() => input.focus(), 100);
            }
        }
    }

    const healthFilterToggle = document.getElementById('healthFilterToggle');
    const healthRecordsOverviewFilterBtn = document.getElementById('healthRecordsOverviewFilterBtn');
    const healthFilterModal = document.getElementById('healthFilterModal');
    const healthFilterCloseBtn = document.getElementById('healthFilterCloseBtn');
    const healthFilterForm = document.getElementById('healthFilterForm');
    const highlightedHealthId = @json($highlightHealthId);
    const healthRecordsSearchInput = document.getElementById('recordSearch');
    const healthRecordsSearchShell = document.getElementById('healthRecordsSearchShell');
    const healthRecordsSearchToggle = document.getElementById('healthRecordsSearchToggle');
    const healthRows = Array.from(document.querySelectorAll('#healthTable tbody tr[data-health-row]'));
    const verifyApprovalModal = document.getElementById('verifyApprovalModal');
    const verifyApprovalCloseBtn = document.getElementById('verifyApprovalCloseBtn');
    const verifyApprovalCancelBtn = document.getElementById('verifyApprovalCancelBtn');
    const verifyApprovalStudentName = document.getElementById('verifyApprovalStudentName');
    const verifyApprovalStudentNumber = document.getElementById('verifyApprovalStudentNumber');
    const verifyApprovalStudentCourse = document.getElementById('verifyApprovalStudentCourse');
    const verifyApprovalReferenceNumber = document.getElementById('verifyApprovalReferenceNumber');
    const verificationConditionGrid = document.getElementById('verificationConditionGrid');
    const verifyApprovalForm = document.getElementById('verifyApprovalForm');
    const verifyApprovalApproveBtn = document.getElementById('verifyApprovalApproveBtn');
    const verifyPendingBtn = document.getElementById('verifyPendingBtn');
    const verifyResubmissionBtn = document.getElementById('verifyResubmissionBtn');
    const verifyResubmissionPanel = document.getElementById('verifyResubmissionPanel');
    const verifyClearanceStatus = document.getElementById('verifyClearanceStatus');
    const verifyMedicalRemarks = document.getElementById('verifyMedicalRemarks');
    const verifyPendingReason = document.getElementById('verifyPendingReason');
    const verifyPendingReasonSelect = document.getElementById('verifyPendingReasonSelect');
    const verifyPendingReasonOther = document.getElementById('verifyPendingReasonOther');
    const verifyOtherReasonField = document.getElementById('verifyOtherReasonField');
    const verifyAssessmentStatus = document.getElementById('verifyAssessmentStatus');
    const verifyDocumentsValid = document.getElementById('verifyDocumentsValid');
    const verificationDocsToggle = document.getElementById('verificationDocsToggle');
    const verificationDocsGrid = document.getElementById('verificationDocsGrid');
    const verifyResubmissionInputs = Array.from(document.querySelectorAll('input[name="resubmission_required_documents[]"]'));
    const verifyDocumentResubmissionForm = document.getElementById('verifyDocumentResubmissionForm');
    const verifyNeedsDocumentResubmission = document.getElementById('verifyNeedsDocumentResubmission');
    const verifyDocumentResubmissionPanel = document.getElementById('verifyDocumentResubmissionPanel');
    const verifyDocumentResubmissionSubmit = document.getElementById('verifyDocumentResubmissionSubmit');
    const verifyDocumentResubmissionActions = document.querySelector('.verify-resubmission-only-actions');
    const verifyDocumentResubmissionRemarks = document.getElementById('verifyDocumentResubmissionRemarks');
    const verifyDocumentResubmissionReason = document.getElementById('verifyDocumentResubmissionReason');
    const verifyNeedsHealthFormCorrection = document.getElementById('verifyNeedsHealthFormCorrection');
    const verifyDocumentResubmissionInputs = Array.from(document.querySelectorAll('#verifyDocumentResubmissionForm input[name="resubmission_required_documents[]"]'));

    healthRecordsSearchShell?.addEventListener('submit', function (event) {
        event.preventDefault();
    });

    function setHealthFilterModalOpen(isOpen) {
        if (!healthFilterModal) return;
        healthFilterModal.classList.toggle('is-open', isOpen);
        healthFilterModal.setAttribute('aria-hidden', isOpen ? 'false' : 'true');
        healthFilterToggle?.classList.toggle('is-open', isOpen);
        if (!isOpen) {
            document.querySelectorAll('.health-filter-select-wrap.is-open').forEach(function (wrap) {
                wrap.classList.remove('is-open');
            });
        }
    }

    document.querySelectorAll('.health-filter-custom-source').forEach(function (select) {
        const wrap = select.closest('.health-filter-select-wrap');
        if (!wrap || wrap.dataset.customReady === '1') return;

        wrap.dataset.customReady = '1';
        wrap.classList.add('has-custom-dropdown');

        const trigger = document.createElement('button');
        trigger.type = 'button';
        trigger.className = 'health-filter-custom-trigger';
        trigger.setAttribute('aria-haspopup', 'listbox');
        trigger.setAttribute('aria-expanded', 'false');

        const menu = document.createElement('div');
        menu.className = 'health-filter-custom-menu';
        menu.setAttribute('role', 'listbox');

        function selectedOption() {
            return select.options[select.selectedIndex] || select.options[0];
        }

        function syncTrigger() {
            const option = selectedOption();
            trigger.textContent = option ? option.textContent.trim() : '';
            menu.querySelectorAll('.health-filter-custom-option').forEach(function (button) {
                button.classList.toggle('is-selected', button.dataset.value === select.value);
                button.setAttribute('aria-selected', button.dataset.value === select.value ? 'true' : 'false');
            });
        }

        Array.prototype.forEach.call(select.options, function (option) {
            const button = document.createElement('button');
            button.type = 'button';
            button.className = 'health-filter-custom-option';
            button.dataset.value = option.value;
            button.textContent = option.textContent.trim();
            button.setAttribute('role', 'option');
            button.addEventListener('click', function () {
                select.value = option.value;
                select.dispatchEvent(new Event('change', { bubbles: true }));
                syncTrigger();
                wrap.classList.remove('is-open');
                trigger.setAttribute('aria-expanded', 'false');
            });
            menu.appendChild(button);
        });

        trigger.addEventListener('click', function () {
            const willOpen = !wrap.classList.contains('is-open');
            document.querySelectorAll('.health-filter-select-wrap.is-open').forEach(function (openWrap) {
                if (openWrap !== wrap) {
                    openWrap.classList.remove('is-open');
                    const openTrigger = openWrap.querySelector('.health-filter-custom-trigger');
                    if (openTrigger) openTrigger.setAttribute('aria-expanded', 'false');
                }
            });
            wrap.classList.toggle('is-open', willOpen);
            trigger.setAttribute('aria-expanded', willOpen ? 'true' : 'false');
        });

        select.addEventListener('change', syncTrigger);
        wrap.appendChild(trigger);
        wrap.appendChild(menu);
        syncTrigger();
    });

    document.addEventListener('click', function (event) {
        if (event.target.closest('.health-filter-select-wrap')) return;

        document.querySelectorAll('.health-filter-select-wrap.is-open').forEach(function (wrap) {
            wrap.classList.remove('is-open');
            const trigger = wrap.querySelector('.health-filter-custom-trigger');
            if (trigger) trigger.setAttribute('aria-expanded', 'false');
        });
    });

    healthRecordsOverviewFilterBtn?.addEventListener('click', function () {
        setHealthFilterModalOpen(true);
    });

    healthFilterToggle?.addEventListener('click', function () {
        setHealthFilterModalOpen(true);
    });

    healthFilterCloseBtn?.addEventListener('click', function () {
        setHealthFilterModalOpen(false);
    });

    healthFilterModal?.addEventListener('click', function (event) {
        if (event.target === healthFilterModal) {
            setHealthFilterModalOpen(false);
        }
    });

    function syncDocumentResubmissionReason() {
        if (!verifyDocumentResubmissionReason) {
            return;
        }

        const remarks = verifyDocumentResubmissionRemarks ? verifyDocumentResubmissionRemarks.value.trim() : '';
        const needsHealthFormCorrection = Boolean(verifyNeedsHealthFormCorrection && verifyNeedsHealthFormCorrection.checked);
        let reason = remarks !== ''
            ? 'Document Resubmission: ' + remarks
            : 'Document Resubmission';

        if (needsHealthFormCorrection && reason.toLowerCase().indexOf('health form correction') === -1) {
            reason += '\nHealth Form Correction';
        }

        verifyDocumentResubmissionReason.value = reason;
    }

    function resetDocumentResubmissionForm(actionUrl) {
        if (verifyDocumentResubmissionForm) {
            verifyDocumentResubmissionForm.setAttribute('action', actionUrl || '');
        }

        if (verifyNeedsDocumentResubmission) {
            verifyNeedsDocumentResubmission.checked = false;
        }

        if (verifyDocumentResubmissionPanel) {
            verifyDocumentResubmissionPanel.classList.remove('is-open');
        }

        if (verifyDocumentResubmissionSubmit) {
            verifyDocumentResubmissionSubmit.disabled = true;
        }

        if (verifyDocumentResubmissionActions) {
            verifyDocumentResubmissionActions.classList.add('is-hidden');
        }

        if (verifyDocumentResubmissionRemarks) {
            verifyDocumentResubmissionRemarks.value = '';
        }

        verifyDocumentResubmissionInputs.forEach(function (input) {
            input.checked = false;
        });
        if (verifyNeedsHealthFormCorrection) {
            verifyNeedsHealthFormCorrection.checked = false;
        }

        syncDocumentResubmissionReason();
    }

    function setHealthFilterOpenState(isOpen) {
        if (!healthFilterToggle || !healthFilterModal) {
            return;
        }

        healthFilterToggle.classList.toggle('is-open', isOpen);
        healthFilterToggle.setAttribute('aria-expanded', isOpen ? 'true' : 'false');
        healthFilterModal.classList.toggle('is-open', isOpen);
        healthFilterModal.setAttribute('aria-hidden', isOpen ? 'false' : 'true');
    }

    if (healthFilterToggle && healthFilterModal) {
        healthFilterToggle.addEventListener('click', function () {
            setHealthFilterOpenState(true);
        });
    }

    if (healthFilterCloseBtn) {
        healthFilterCloseBtn.addEventListener('click', function () {
            setHealthFilterOpenState(false);
        });
    }

    if (healthFilterModal) {
        healthFilterModal.addEventListener('click', function (event) {
            if (event.target === healthFilterModal) {
                setHealthFilterOpenState(false);
            }
        });
    }

    if (healthFilterForm) {
        healthFilterForm.addEventListener('submit', function () {
            setHealthFilterOpenState(false);
        });
    }

    if (highlightedHealthId) {
        window.addEventListener('DOMContentLoaded', function () {
            const highlightedRow = document.querySelector('[data-health-row][data-health-id="' + highlightedHealthId + '"]');
            if (highlightedRow) {
                highlightedRow.scrollIntoView({ behavior: 'smooth', block: 'center' });
                window.setTimeout(function () {
                    highlightedRow.classList.remove('health-highlight-row');

                    const url = new URL(window.location.href);
                    if (url.searchParams.has('highlight_health')) {
                        url.searchParams.delete('highlight_health');
                        window.history.replaceState({}, document.title, url.toString());
                    }
                }, 5000);
            }
        });
    }

    if (healthRecordsSearchInput) {
        healthRecordsSearchInput.addEventListener('input', function () {
            const searchTerm = this.value.trim().toLowerCase();

            healthRows.forEach(function (row) {
                const rowText = row.innerText.toLowerCase();
                row.style.display = rowText.includes(searchTerm) ? '' : 'none';
            });
        });
    }

    window.addEventListener('DOMContentLoaded', function () {
        const params = new URLSearchParams(window.location.search);
        const requestedTab = params.get('tab');
        if (requestedTab === 'pending_approval') {
            const pendingApprovalInfoModal = document.getElementById('pendingApprovalInfoModal');
            if (pendingApprovalInfoModal) pendingApprovalInfoModal.style.display = 'flex';
        } else if (requestedTab === 'pending_conditional') {
            const pendingConditionalInfoModal = document.getElementById('pendingConditionalInfoModal');
            if (pendingConditionalInfoModal) pendingConditionalInfoModal.style.display = 'flex';
        }
    });

    document.addEventListener('DOMContentLoaded', function () {
        const healthRecordsSearchShell = document.getElementById('healthRecordsSearchShell');
        const healthRecordsSearchInput = document.getElementById('recordSearch');
        const healthRecordsSearchToggle = document.getElementById('healthRecordsSearchToggle');

        if (healthRecordsSearchShell && healthRecordsSearchInput && healthRecordsSearchToggle) {
            const setHealthRecordsSearchOpenState = function (isOpen) {
                healthRecordsSearchShell.classList.toggle('is-open', isOpen);
                healthRecordsSearchToggle.setAttribute('aria-expanded', isOpen ? 'true' : 'false');
            };

            setHealthRecordsSearchOpenState(healthRecordsSearchInput.value.trim() !== '');

            healthRecordsSearchToggle.addEventListener('click', function () {
                const shouldOpen = !healthRecordsSearchShell.classList.contains('is-open');
                setHealthRecordsSearchOpenState(shouldOpen);

                if (shouldOpen) {
                    window.requestAnimationFrame(function () {
                        healthRecordsSearchInput.focus();
                    });
                }
            });
        }
    });

    window.addEventListener('DOMContentLoaded', function () {
        document.querySelectorAll('[data-health-row][data-view-url]').forEach(function (row) {
            const viewUrl = row.getAttribute('data-view-url') || '';
            if (viewUrl.trim() === '') {
                return;
            }

            row.addEventListener('click', function (event) {
                if (event.target.closest('a, button, input, select, textarea, label')) {
                    return;
                }

                window.location.href = viewUrl;
            });
        });
    });

    const setVerifyApprovalModalOpenState = function (isOpen) {
        if (!verifyApprovalModal) {
            return;
        }

        verifyApprovalModal.classList.toggle('is-open', isOpen);
        verifyApprovalModal.setAttribute('aria-hidden', isOpen ? 'false' : 'true');
        verifyApprovalModal.style.display = isOpen ? 'flex' : 'none';
    };

    if (verifyApprovalCloseBtn) {
        verifyApprovalCloseBtn.addEventListener('click', function (event) {
            event.preventDefault();
            event.stopPropagation();
            setVerifyApprovalModalOpenState(false);
        });
    }

    if (verifyApprovalCancelBtn) {
        verifyApprovalCancelBtn.addEventListener('click', function (event) {
            event.preventDefault();
            event.stopPropagation();
            setVerifyApprovalModalOpenState(false);
        });
    }

    if (verifyApprovalModal) {
        verifyApprovalModal.addEventListener('click', function (event) {
            event.stopPropagation();
            if (event.target === verifyApprovalModal) {
                setVerifyApprovalModalOpenState(false);
            }
        });
    }

    if (verifyNeedsDocumentResubmission) {
        verifyNeedsDocumentResubmission.addEventListener('change', function () {
            const isChecked = verifyNeedsDocumentResubmission.checked;

            if (verifyDocumentResubmissionPanel) {
                verifyDocumentResubmissionPanel.classList.toggle('is-open', isChecked);
            }

            if (verifyDocumentResubmissionSubmit) {
                verifyDocumentResubmissionSubmit.disabled = !isChecked;
            }

            if (verifyDocumentResubmissionActions) {
                verifyDocumentResubmissionActions.classList.toggle('is-hidden', !isChecked);
            }

            if (!isChecked) {
                verifyDocumentResubmissionInputs.forEach(function (input) {
                    input.checked = false;
                });
            }

            syncDocumentResubmissionReason();
        });
    }

    if (verifyDocumentResubmissionRemarks) {
        verifyDocumentResubmissionRemarks.addEventListener('input', syncDocumentResubmissionReason);
    }
    if (verifyNeedsHealthFormCorrection) {
        verifyNeedsHealthFormCorrection.addEventListener('change', syncDocumentResubmissionReason);
    }

    if (verifyDocumentResubmissionForm) {
        verifyDocumentResubmissionForm.addEventListener('submit', function (event) {
            syncDocumentResubmissionReason();

            if (!verifyNeedsDocumentResubmission || !verifyNeedsDocumentResubmission.checked) {
                event.preventDefault();
                if (verifyNeedsDocumentResubmission) {
                    verifyNeedsDocumentResubmission.setCustomValidity('Check Needs Document Resubmission first.');
                    verifyNeedsDocumentResubmission.reportValidity();
                    verifyNeedsDocumentResubmission.setCustomValidity('');
                }
                return;
            }

            const hasSelectedDocument = verifyDocumentResubmissionInputs.some(function (input) {
                return input.checked;
            });
            const hasHealthFormCorrection = Boolean(verifyNeedsHealthFormCorrection && verifyNeedsHealthFormCorrection.checked);

            if (!hasSelectedDocument && !hasHealthFormCorrection) {
                event.preventDefault();
                const firstInput = verifyDocumentResubmissionInputs[0] || verifyNeedsHealthFormCorrection;
                if (firstInput) {
                    firstInput.setCustomValidity('Select at least one document or Health Form Correction.');
                    firstInput.reportValidity();
                    firstInput.setCustomValidity('');
                }
                return;
            }

            if (!verifyDocumentResubmissionForm.getAttribute('action')) {
                event.preventDefault();
            }
        });
    }

    // Read-only clinic status modals - wrapped in DOMContentLoaded to ensure elements exist
    document.addEventListener('DOMContentLoaded', function() {
        const pendingApprovalInfoBtn = document.getElementById('pendingApprovalInfoBtn');
        const pendingApprovalInfoModal = document.getElementById('pendingApprovalInfoModal');
        const closePendingApprovalInfoModal = document.getElementById('closePendingApprovalInfoModal');
        const pendingConditionalInfoBtn = document.getElementById('pendingConditionalInfoBtn');
        const pendingConditionalInfoModal = document.getElementById('pendingConditionalInfoModal');
        const closePendingConditionalInfoModal = document.getElementById('closePendingConditionalInfoModal');
        const copyReferenceValue = function (value, button) {
            const text = (value || '').trim();
            if (!text) return;

            const markCopied = function () {
                const originalLabel = button ? (button.getAttribute('aria-label') || 'Copy reference number') : 'Copy reference number';
                if (button) {
                    button.setAttribute('aria-label', 'Copied');
                    button.classList.add('is-copied');
                }
                window.setTimeout(function () {
                    if (button) {
                        button.setAttribute('aria-label', originalLabel);
                        button.classList.remove('is-copied');
                    }
                }, 1200);
            };

            const fallbackCopy = function () {
                const tempInput = document.createElement('textarea');
                tempInput.value = text;
                tempInput.setAttribute('readonly', 'readonly');
                tempInput.style.position = 'fixed';
                tempInput.style.top = '0';
                tempInput.style.left = '-9999px';
                tempInput.style.opacity = '0';
                tempInput.style.pointerEvents = 'none';
                document.body.appendChild(tempInput);
                tempInput.focus();
                tempInput.select();
                tempInput.setSelectionRange(0, tempInput.value.length);

                try {
                    const copied = document.execCommand('copy');
                    if (!copied && navigator.clipboard) {
                        navigator.clipboard.writeText(text).then(markCopied);
                        return;
                    }
                    if (copied) {
                        markCopied();
                    }
                } catch (error) {
                    if (navigator.clipboard) {
                        navigator.clipboard.writeText(text).then(markCopied).catch(function () {});
                    }
                } finally {
                    document.body.removeChild(tempInput);
                }
            };

            if (navigator.clipboard && window.isSecureContext) {
                navigator.clipboard.writeText(text).then(markCopied).catch(fallbackCopy);
                return;
            }

            fallbackCopy();
        };

        const getReadonlyPagination = function (list) {
            if (!list?.id) return null;
            return document.querySelector(`[data-readonly-pagination="${list.id}"]`);
        };

        const paginateReadonlyList = function (list) {
            if (!list) return;

            const pagination = getReadonlyPagination(list);
            const cards = Array.from(list.querySelectorAll('.readonly-record-card'));
            const visibleCards = cards.filter(function (card) {
                return card.dataset.searchVisible !== '0';
            });
            const pageSizeControl = pagination?.querySelector('[data-pagination-page-size]');
            const rawPageSize = pageSizeControl?.value || pagination?.getAttribute('data-page-size') || '5';
            const pageSize = rawPageSize === 'all'
                ? Math.max(1, visibleCards.length)
                : Math.max(1, parseInt(rawPageSize, 10) || 5);
            const totalPages = Math.max(1, Math.ceil(visibleCards.length / pageSize));
            let currentPage = parseInt(list.dataset.currentPage || '1', 10);
            if (!Number.isFinite(currentPage) || currentPage < 1) currentPage = 1;
            if (currentPage > totalPages) currentPage = totalPages;
            list.dataset.currentPage = String(currentPage);

            cards.forEach(function (card) {
                card.style.display = 'none';
            });

            const startIndex = (currentPage - 1) * pageSize;
            const endIndex = startIndex + pageSize;
            visibleCards.slice(startIndex, endIndex).forEach(function (card) {
                card.style.display = '';
            });

            if (!pagination) return;

            const shouldShowPagination = visibleCards.length > 0;
            pagination.classList.toggle('is-visible', shouldShowPagination);

            const summary = pagination.querySelector('[data-pagination-summary]');
            if (summary) {
                const showingStart = visibleCards.length === 0 ? 0 : startIndex + 1;
                const showingEnd = Math.min(endIndex, visibleCards.length);
                summary.textContent = `Showing ${showingStart}-${showingEnd} of ${visibleCards.length}`;
            }

            const previousBtn = pagination.querySelector('[data-pagination-prev]');
            const nextBtn = pagination.querySelector('[data-pagination-next]');
            const currentBtn = pagination.querySelector('[data-pagination-current]');
            if (previousBtn) previousBtn.disabled = currentPage <= 1;
            if (nextBtn) nextBtn.disabled = currentPage >= totalPages;
            if (currentBtn) currentBtn.textContent = String(totalPages === 0 ? 1 : currentPage);
        };

        const filterReadonlyCards = function (input) {
            const listId = input?.getAttribute('data-readonly-modal-search') || '';
            const list = listId ? document.getElementById(listId) : null;
            if (!list) return;

            const query = (input.value || '').trim().toLowerCase();
            const cards = Array.from(list.querySelectorAll('.readonly-record-card'));
            let visibleCount = 0;

            cards.forEach(function (card) {
                const searchText = (card.getAttribute('data-search-text') || card.innerText || '').toLowerCase();
                const isVisible = query === '' || searchText.includes(query);
                card.dataset.searchVisible = isVisible ? '1' : '0';
                if (isVisible) {
                    visibleCount += 1;
                }
            });

            list.dataset.currentPage = '1';
            paginateReadonlyList(list);

            const emptyState = list.querySelector('[data-readonly-search-empty]');
            if (emptyState) {
                emptyState.classList.toggle('is-visible', query !== '' && visibleCount === 0);
            }
        };

        document.querySelectorAll('[data-readonly-modal-search]').forEach(function (input) {
            input.addEventListener('input', function () {
                filterReadonlyCards(input);
            });
        });

        document.querySelectorAll('[data-readonly-pagination]').forEach(function (pagination) {
            const listId = pagination.getAttribute('data-readonly-pagination') || '';
            const list = listId ? document.getElementById(listId) : null;
            if (!list) return;

            pagination.querySelector('[data-pagination-prev]')?.addEventListener('click', function () {
                list.dataset.currentPage = String(Math.max(1, parseInt(list.dataset.currentPage || '1', 10) - 1));
                paginateReadonlyList(list);
            });

            pagination.querySelector('[data-pagination-next]')?.addEventListener('click', function () {
                list.dataset.currentPage = String(parseInt(list.dataset.currentPage || '1', 10) + 1);
                paginateReadonlyList(list);
            });

            pagination.querySelector('[data-pagination-page-size]')?.addEventListener('change', function () {
                list.dataset.currentPage = '1';
                pagination.setAttribute('data-page-size', this.value || '5');
                paginateReadonlyList(list);
            });

            list.querySelectorAll('.readonly-record-card').forEach(function (card) {
                card.dataset.searchVisible = '1';
            });
            paginateReadonlyList(list);
        });

        const resetReadonlyModalSearch = function (modal) {
            modal?.querySelectorAll('[data-readonly-modal-search]').forEach(function (input) {
                input.value = '';
                filterReadonlyCards(input);
            });
        };

        if (pendingApprovalInfoBtn && pendingApprovalInfoModal) {
            pendingApprovalInfoBtn.addEventListener('click', function () {
                resetReadonlyModalSearch(pendingApprovalInfoModal);
                pendingApprovalInfoModal.style.display = 'flex';
                pendingApprovalInfoModal.querySelector('[data-readonly-modal-search]')?.focus();
            });
        }

        if (closePendingApprovalInfoModal && pendingApprovalInfoModal) {
            closePendingApprovalInfoModal.addEventListener('click', function () {
                pendingApprovalInfoModal.style.display = 'none';
            });
        }

        if (pendingConditionalInfoBtn && pendingConditionalInfoModal) {
            pendingConditionalInfoBtn.addEventListener('click', function () {
                resetReadonlyModalSearch(pendingConditionalInfoModal);
                pendingConditionalInfoModal.style.display = 'flex';
                pendingConditionalInfoModal.querySelector('[data-readonly-modal-search]')?.focus();
            });
        }

        if (closePendingConditionalInfoModal && pendingConditionalInfoModal) {
            closePendingConditionalInfoModal.addEventListener('click', function () {
                pendingConditionalInfoModal.style.display = 'none';
            });
        }

    });

    function escapeHtml(value) {
        return String(value ?? '').replace(/[&<>"']/g, function (char) {
            return {
                '&': '&amp;',
                '<': '&lt;',
                '>': '&gt;',
                '"': '&quot;',
                "'": '&#039;'
            }[char];
        });
    }

    function renderVerificationDocuments(documents) {
        if (!verificationDocsGrid) return;
        const docs = Array.isArray(documents) ? documents : [];
        verificationDocsGrid.innerHTML = docs.map(function (doc) {
            if (doc && doc.url) {
                const docTitle = doc.title || 'Requirement';
                const isHealthForm = String(docTitle).toLowerCase() === 'health information form';
                if (isHealthForm) {
                    return `
                    <a class="verification-doc-card health-form-doc-card" href="${escapeHtml(doc.url)}" target="_blank" rel="noopener noreferrer">
                        <span class="health-form-doc-preview" aria-hidden="true">
                            <span class="health-form-doc-title">
                                <span>Health</span>
                                <span>Information</span>
                                <span>Form</span>
                            </span>
                        </span>
                        <strong>${escapeHtml(docTitle)}</strong>
                        <span>Open in new tab</span>
                    </a>
                `;
                }

                return `
                    <a class="verification-doc-card" href="${escapeHtml(doc.url)}" target="_blank" rel="noopener noreferrer">
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M19.5 14.25v-2.625a3.375 3.375 0 0 0-3.375-3.375h-1.5A1.125 1.125 0 0 1 13.5 7.125v-1.5A3.375 3.375 0 0 0 10.125 2.25H8.25m2.25 0H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 0 0-9-9Z" /></svg>
                        <strong>${escapeHtml(doc.title || 'Requirement')}</strong>
                        <span>Open in new tab</span>
                    </a>
                `;
            }

            return `
                <div class="verification-doc-card">
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M18.364 18.364A9 9 0 0 0 5.636 5.636m12.728 12.728A9 9 0 0 1 5.636 5.636m12.728 12.728L5.636 5.636" /></svg>
                    <strong>${escapeHtml(doc && doc.title ? doc.title : 'Requirement')}</strong>
                    <span>Missing upload</span>
                </div>
            `;
        }).join('');
    }

    function syncApprovalButtonState() {
        if (!verifyApprovalApproveBtn) return;
        const canApprove = Boolean(verifyDocumentsValid && verifyDocumentsValid.checked)
            && verifyAssessmentStatus
            && verifyAssessmentStatus.value === 'Completed / Passed';
        verifyApprovalApproveBtn.disabled = !canApprove;
    }

    function setResubmissionPanelState(isOpen) {
        if (verifyResubmissionPanel) {
            verifyResubmissionPanel.classList.toggle('is-open', Boolean(isOpen));
        }

        verifyResubmissionInputs.forEach(function (input) {
            if (!isOpen) {
                input.checked = false;
            }
        });
    }

    function syncResubmissionReason() {
        if (!verifyPendingReason || !verifyPendingReasonSelect) {
            return '';
        }

        const selectedValue = verifyPendingReasonSelect.value;
        const isOther = selectedValue === 'others';
        const otherValue = verifyPendingReasonOther ? verifyPendingReasonOther.value.trim() : '';
        const reason = isOther ? otherValue : selectedValue;

        if (verifyOtherReasonField) {
            verifyOtherReasonField.classList.toggle('is-open', isOther);
        }

        if (verifyPendingReasonOther) {
            verifyPendingReasonOther.required = isOther;
        }

        verifyPendingReason.value = reason;
        return reason;
    }

    function getRecordPayloadFromRow(row) {
        let payload = {};
        try {
            payload = JSON.parse(row?.dataset.recordPayload || '{}');
        } catch (error) {
            payload = {};
        }

        return payload;
    }

    function reviewFallbackValue(primary, fallback) {
        let value = String(primary ?? '').trim();
        if (value === '' || value === '-') {
            value = String(fallback ?? '').trim();
        }
        return value || '-';
    }

    function openVerificationModalFromRow(row, button = null) {
        if (!row) return;
        const payload = getRecordPayloadFromRow(row);
        if (button) {
            payload.name = reviewFallbackValue(payload.name, button.getAttribute('data-review-name'));
            payload.email = reviewFallbackValue(payload.email, button.getAttribute('data-review-email'));
            payload.reference_number = reviewFallbackValue(payload.reference_number, button.getAttribute('data-review-reference'));
            payload.course = reviewFallbackValue(payload.course, button.getAttribute('data-review-course'));
            payload.student_id = reviewFallbackValue(payload.student_id, button.getAttribute('data-review-student-id'));
            payload.approve_url = reviewFallbackValue(payload.approve_url, button.getAttribute('data-review-approve-url'));
            payload.resubmission_url = reviewFallbackValue(payload.resubmission_url, button.getAttribute('data-review-resubmission-url'));
        }

        if (verifyApprovalStudentName) {
            verifyApprovalStudentName.textContent = payload.name || '-';
        }
        if (verifyApprovalStudentNumber) {
            verifyApprovalStudentNumber.textContent = payload.email || '-';
        }
        if (verifyApprovalStudentCourse) {
            verifyApprovalStudentCourse.textContent = payload.course || '-';
        }
        if (verifyApprovalReferenceNumber) {
            verifyApprovalReferenceNumber.textContent = payload.reference_number || '-';
        }
        if (verifyApprovalForm) {
            verifyApprovalForm.setAttribute('action', payload.approve_url || '');
        }
        resetDocumentResubmissionForm(payload.resubmission_url || '');
        if (verifyMedicalRemarks) {
            verifyMedicalRemarks.value = payload.medical_condition_remarks || '';
        }
        if (verifyPendingReason) {
            verifyPendingReason.value = '';
        }
        if (verifyPendingReasonSelect) {
            verifyPendingReasonSelect.value = '';
        }
        if (verifyPendingReasonOther) {
            verifyPendingReasonOther.value = '';
        }
        syncResubmissionReason();
        if (verifyAssessmentStatus) {
            verifyAssessmentStatus.value = payload.physical_assessment_status || 'Not Yet Conducted';
        }
        if (verifyDocumentsValid) {
            verifyDocumentsValid.checked = Boolean(payload.documents_valid);
        }
        if (verifyClearanceStatus) {
            verifyClearanceStatus.value = 'Pending Resubmission';
        }
        verifyResubmissionInputs.forEach(function (input) {
            input.checked = false;
        });
        setResubmissionPanelState(true);
        if (verificationDocsGrid) {
            const documentsTemplate = row.querySelector('[data-review-documents-template]');
            if (documentsTemplate) {
                verificationDocsGrid.innerHTML = documentsTemplate.innerHTML.trim();
            }
        }
        if (verificationConditionGrid) {
            const conditionTemplate = row.querySelector('[data-review-condition-template]');
            verificationConditionGrid.innerHTML = conditionTemplate
                ? conditionTemplate.innerHTML.trim()
                : '<div class="verify-condition-item"><span>Condition</span><strong>No Medical Condition</strong></div>';
        }
        if (!row.querySelector('[data-review-documents-template]')) {
            renderVerificationDocuments(payload.documents || []);
        }
        setVerifyApprovalModalOpenState(true);
    }

    function openHealthReviewFromButtonEnhanced(button) {
        if (!button) return;
        const row = button.closest('[data-health-row]');

        openVerificationModalFromRow(row, button);
    }

    if (verificationDocsToggle && verificationDocsGrid) {
        verificationDocsToggle.addEventListener('click', function () {
            verificationDocsGrid.classList.toggle('is-open');
        });
    }

    [verifyDocumentsValid, verifyAssessmentStatus].forEach(function (input) {
        if (input) {
            input.addEventListener('change', syncApprovalButtonState);
        }
    });

    if (verifyPendingBtn && verifyClearanceStatus && verifyApprovalForm) {
        verifyPendingBtn.addEventListener('click', function (event) {
            verifyClearanceStatus.value = 'Pending/Conditional';
            setResubmissionPanelState(false);
            if (verifyPendingReason && verifyPendingReason.value.trim() === '') {
                event.preventDefault();
                verifyPendingReason.setCustomValidity('Nurse remarks are required for pending or conditional records.');
                verifyPendingReason.reportValidity();
                verifyPendingReason.setCustomValidity('');
            }
        });
    }

    if (verifyResubmissionBtn && verifyClearanceStatus && verifyApprovalForm) {
        verifyResubmissionBtn.addEventListener('click', function (event) {
            verifyClearanceStatus.value = 'Pending Resubmission';
            setResubmissionPanelState(true);
            const resubmissionReason = syncResubmissionReason();

            const hasSelectedDocument = verifyResubmissionInputs.some(function (input) {
                return input.checked;
            });

            if (!hasSelectedDocument) {
                event.preventDefault();
                const firstInput = verifyResubmissionInputs[0];
                if (firstInput) {
                    firstInput.setCustomValidity('Select at least one document for resubmission.');
                    firstInput.reportValidity();
                    firstInput.setCustomValidity('');
                }
                return;
            }

            if (resubmissionReason === '') {
                event.preventDefault();
                if (verifyPendingReasonSelect && verifyPendingReasonSelect.value === 'others' && verifyPendingReasonOther) {
                    verifyPendingReasonOther.setCustomValidity('Type the resubmission reason.');
                    verifyPendingReasonOther.reportValidity();
                    verifyPendingReasonOther.setCustomValidity('');
                    return;
                }
                if (verifyPendingReasonSelect) {
                    verifyPendingReasonSelect.setCustomValidity('Select a resubmission reason.');
                    verifyPendingReasonSelect.reportValidity();
                    verifyPendingReasonSelect.setCustomValidity('');
                }
            }
        });
    }

    if (verifyPendingReasonSelect) {
        verifyPendingReasonSelect.addEventListener('change', syncResubmissionReason);
    }

    if (verifyPendingReasonOther) {
        verifyPendingReasonOther.addEventListener('input', syncResubmissionReason);
    }

    if (verifyApprovalApproveBtn && verifyClearanceStatus) {
        verifyApprovalApproveBtn.addEventListener('click', function (event) {
            verifyClearanceStatus.value = 'Fully Cleared';
            setResubmissionPanelState(false);
            syncApprovalButtonState();
            if (verifyApprovalApproveBtn.disabled) {
                event.preventDefault();
            }
        });
    }

    (function initHealthRecordStatsLiveSync() {
        const statEndpoint = @json(route('admin.health_records.stats'));
        const statNodes = document.querySelectorAll('[data-health-record-stat]');

        if (!statEndpoint || statNodes.length === 0) {
            return;
        }

        const formatter = new Intl.NumberFormat();
        let healthRecordStatsTimer = null;

        function currentQueryString() {
            const params = new URLSearchParams(window.location.search);
            params.delete('issued_page');
            return params.toString();
        }

        function renderStat(key, rawValue) {
            const numericValue = Number(rawValue || 0);
            const nextValue = formatter.format(Number.isFinite(numericValue) ? numericValue : 0);

            document.querySelectorAll(`[data-health-record-stat="${key}"]`).forEach(function (node) {
                if ((node.textContent || '').trim() === nextValue) {
                    return;
                }

                node.textContent = nextValue;
                node.classList.remove('is-live-updated');
                void node.offsetWidth;
                node.classList.add('is-live-updated');
            });
        }

        function pullStats() {
            if (document.hidden) {
                return;
            }

            const query = currentQueryString();
            const url = query ? `${statEndpoint}?${query}` : statEndpoint;

            fetch(url, {
                headers: {
                    'Accept': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest'
                },
                credentials: 'same-origin'
            })
                .then(function (response) {
                    if (!response.ok) {
                        throw new Error('Unable to load health record stats.');
                    }
                    return response.json();
                })
                .then(function (payload) {
                    const stats = payload && payload.stats ? payload.stats : {};
                    Object.keys(stats).forEach(function (key) {
                        renderStat(key, stats[key]);
                    });
                })
                .catch(function () {
                    // Stats are convenience-only. Keep the current page usable if polling fails.
                });
        }

        pullStats();
        healthRecordStatsTimer = window.setInterval(pullStats, 12000);

        window.addEventListener('beforeunload', function () {
            if (healthRecordStatsTimer) {
                window.clearInterval(healthRecordStatsTimer);
            }
        });
    })();

    (function initHealthSummaryLiveSync() {
        const liveFeedNode = document.getElementById('adminLiveAlertFeedUrl');
        if (!liveFeedNode) {
            return;
        }

        let feedUrl = '';
        try {
            feedUrl = JSON.parse(liveFeedNode.textContent || '""') || '';
        } catch (error) {
            feedUrl = '';
        }

        if (!feedUrl) {
            return;
        }

        let knownNotificationIds = new Set();
        let healthLivePollTimer = null;
        let hasHydratedLiveFeed = false;

        const isHealthNotification = function (notification) {
            const id = (notification && notification.id ? String(notification.id) : '').trim();
            return id.startsWith('health-form:');
        };

        const hydrateKnownIds = function (payload) {
            const notifications = Array.isArray(payload && payload.notifications) ? payload.notifications : [];
            knownNotificationIds = new Set(
                notifications
                    .filter(isHealthNotification)
                    .map(function (notification) {
                        return String(notification.id);
                    })
            );
        };

        const pullFeed = function () {
            if (document.hidden) {
                return;
            }

            fetch(feedUrl, {
                headers: {
                    'X-Requested-With': 'XMLHttpRequest',
                    'Accept': 'application/json'
                },
                credentials: 'same-origin'
            })
                .then(function (response) {
                    if (!response.ok) {
                        throw new Error('Failed to fetch live health updates.');
                    }
                    return response.json();
                })
                .then(function (payload) {
                    const notifications = Array.isArray(payload && payload.notifications) ? payload.notifications : [];
                    const healthNotifications = notifications.filter(isHealthNotification);

                    if (!hasHydratedLiveFeed) {
                        hydrateKnownIds(payload);
                        hasHydratedLiveFeed = true;
                        return;
                    }

                    const hasNewHealthSubmission = healthNotifications.some(function (notification) {
                        return !knownNotificationIds.has(String(notification.id));
                    });

                    if (hasNewHealthSubmission) {
                        window.location.reload();
                        return;
                    }

                    hydrateKnownIds(payload);
                })
                .catch(function () {
                    // Keep the page usable if the live notification feed is temporarily unavailable.
                });
        };

        pullFeed();
        healthLivePollTimer = window.setInterval(pullFeed, 15000);

        window.addEventListener('beforeunload', function () {
            if (healthLivePollTimer) {
                window.clearInterval(healthLivePollTimer);
            }
        });
    })();

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
            menu.setAttribute('role', 'listbox');

            function selectedText() {
                const option = select.options[select.selectedIndex];
                return option ? option.textContent.trim() : 'Select';
            }

            function rebuild() {
                button.textContent = selectedText();
                menu.innerHTML = '';
                Array.from(select.options).forEach(function(option) {
                    const item = document.createElement('button');
                    item.type = 'button';
                    item.className = 'premium-select-option';
                    item.textContent = option.textContent.trim();
                    item.dataset.value = option.value;
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

            button.addEventListener('click', function(event) {
                event.stopPropagation();
                document.querySelectorAll('.premium-select-shell.is-open').forEach(function(openShell) {
                    if (openShell !== shell) openShell.classList.remove('is-open');
                });
                shell.classList.toggle('is-open');
            });

            select.parentNode.insertBefore(shell, select.nextSibling);
            shell.appendChild(select);
            shell.appendChild(button);
            shell.appendChild(menu);
            rebuild();
            select.addEventListener('change', rebuild);
        }

        document.querySelectorAll('.readonly-pagination-per-page-select').forEach(enhance);
        document.addEventListener('click', function() {
            document.querySelectorAll('.premium-select-shell.is-open').forEach(function(shell) {
                shell.classList.remove('is-open');
            });
        });
    })();
</script>
@endpush
