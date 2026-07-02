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
        gap: 12px;
        margin-top: 16px;
        padding: 12px;
        border: 1px solid rgba(112, 19, 27, 0.12);
        border-radius: 16px;
        background: #fffaf7;
    }
    .readonly-modal-pagination.is-visible {
        display: flex;
    }
    .readonly-pagination-summary {
        color: #64748b;
        font-size: 12px;
        font-weight: 800;
        text-transform: uppercase;
        letter-spacing: 0.04em;
    }
    .readonly-pagination-actions {
        display: flex;
        align-items: center;
        gap: 8px;
    }
    .readonly-pagination-btn {
        min-width: 92px;
        min-height: 40px;
        border: 1px solid rgba(112, 19, 27, 0.35);
        border-radius: 999px;
        background: #fff;
        color: #70131B;
        font-size: 12px;
        font-weight: 900;
        cursor: pointer;
        transition: background-color 0.2s ease, border-color 0.2s ease, color 0.2s ease, transform 0.2s ease;
    }
    .readonly-pagination-btn:hover:not(:disabled) {
        background: #ffcc00;
        border-color: #ffcc00;
        color: #111827;
        transform: translateY(-1px);
    }
    .readonly-pagination-btn:disabled {
        cursor: not-allowed;
        opacity: 0.45;
    }
    .readonly-record-card {
        display: grid;
        gap: 14px;
        padding: 16px;
        border: 1px solid rgba(112, 19, 27, 0.12);
        border-radius: 16px;
        background: #fffaf7;
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
        display: flex;
        align-items: flex-end;
        justify-content: flex-start;
        gap: 12px;
        flex-wrap: wrap;
        margin-top: 16px;
    }

    .health-filter-field {
        display: flex;
        flex-direction: column;
        gap: 6px;
    }

    .health-filter-actions {
        display: flex;
        align-items: center;
        gap: 10px;
        margin-left: auto;
        flex-wrap: nowrap;
    }

    .health-filter-field label {
        font-size: 11px;
        font-weight: 800;
        text-transform: uppercase;
        letter-spacing: 0.05em;
        color: #64748b;
    }

    .health-records-search,
    .health-filter-select {
        min-height: 48px;
        height: 48px;
        padding: 12px 18px;
        border-radius: 0 0 14px 14px;
        border: 0 !important;
        border-bottom: 3px solid #8f2230 !important;
        min-width: 180px;
        color: #111827;
        background: transparent !important;
        box-shadow: none !important;
        transition: border-color .18s ease, box-shadow .18s ease, transform .18s ease;
        appearance: none;
        -webkit-appearance: none;
    }

    .health-records-search {
        width: 280px;
    }
    .health-records-search::placeholder {
        color: #7f1d2d;
        font-weight: 700;
    }

    .health-records-search:focus,
    .health-filter-select:focus {
        outline: none;
        border-bottom-color: #70131B;
        box-shadow: none !important;
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
        padding: 0 18px;
        border-radius: 999px;
        border: 1px solid #8f2230;
        background: linear-gradient(135deg, #70131B, #8f2230);
        color: #ffffff;
        font-size: 13px;
        font-weight: 800;
        text-decoration: none;
        cursor: pointer;
        box-shadow:
            0 0 0 3px rgba(112, 19, 27, 0.12),
            0 10px 22px rgba(112, 19, 27, 0.20);
        transition: color .08s linear, transform .18s ease, box-shadow .18s ease, border-color .18s ease;
        z-index: 0;
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
        transition: transform 1.5s ease;
        z-index: -1;
    }

    .health-filter-btn:hover {
        transform: translateY(-1px);
        border-color: #facc15;
        box-shadow:
            0 0 0 3px rgba(250, 204, 21, 0.18),
            0 14px 24px rgba(112, 19, 27, 0.16);
    }

    .health-filter-btn:hover::after {
        transform: translateX(135%);
    }

    .health-filter-btn-reset {
        background: linear-gradient(135deg, #64748b, #475569);
        border-color: #475569;
        box-shadow:
            0 0 0 3px rgba(100, 116, 139, 0.12),
            0 10px 22px rgba(71, 85, 105, 0.20);
    }

    .health-filter-modal {
        position: fixed;
        inset: 0;
        display: none;
        align-items: center;
        justify-content: center;
        padding: 24px;
        background: rgba(15, 23, 42, 0.42);
        backdrop-filter: blur(6px);
        z-index: 1200;
    }

    .health-filter-modal.is-open {
        display: flex;
    }

    .health-filter-modal-card {
        width: min(760px, 100%);
        border-radius: 22px;
        background: #ffffff;
        border: 1px solid rgba(127, 29, 45, 0.12);
        box-shadow: 0 24px 60px rgba(15, 23, 42, 0.18);
        padding: 22px;
    }

    .health-filter-modal-head {
        display: flex;
        align-items: flex-start;
        justify-content: space-between;
        gap: 16px;
        margin-bottom: 12px;
    }

    .health-filter-modal-title {
        margin: 0;
        font-size: 20px;
        font-weight: 900;
        color: #70131B;
    }

    .health-filter-modal-copy {
        margin: 6px 0 0;
        color: #64748b;
        font-size: 13px;
        line-height: 1.6;
    }

    .health-filter-modal-close {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        width: 40px;
        height: 40px;
        border: 1px solid rgba(127, 29, 45, 0.12);
        border-radius: 999px;
        background: #ffffff;
        color: #111827;
        cursor: pointer;
    }

    .health-filter-modal-close svg {
        width: 18px;
        height: 18px;
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
        align-items: flex-start;
        justify-content: space-between;
        gap: 16px;
        margin-bottom: 12px;
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
        box-shadow:
            0 0 0 2px rgba(250, 204, 21, 0.06),
            0 10px 20px rgba(0, 0, 0, 0.20);
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
        border-color: rgba(148, 163, 184, 0.14);
        box-shadow: 0 18px 32px rgba(0, 0, 0, 0.24);
    }

    html[data-theme="dark"] .health-filter-modal-title,
    html[data-theme="dark"] .health-filter-modal-close {
        color: #ffffff;
    }

    html[data-theme="dark"] .health-filter-modal-copy {
        color: #cbd5e1;
    }

    html[data-theme="dark"] .health-filter-modal-close {
        background: rgba(15, 23, 42, 0.92);
        border-color: rgba(148, 163, 184, 0.24);
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

</style>
@endpush

@section('content')
    @php
        $role = \App\Models\User::normalizeRole(optional(auth()->user())->user_role ?? '');
        $basePrefix = $role === \App\Models\User::ROLE_ADMIN ? '/assistant' : '/admin';
        $canSignHealth = $role === \App\Models\User::ROLE_SUPERADMIN;
        $highlightHealthId = trim((string) request()->query('highlight_health', ''));
    @endphp

    {{-- Header with Search / Filters --}}
    <div class="health-records-toolbar">
        <h2 class="health-records-title"><x-outline-icon name="document-text" />Health Records</h2>
        <div class="health-records-toolbar-actions">
            <div class="health-records-search-shell" id="healthRecordsSearchShell">
                <div class="health-records-search-wrap">
                    <input
                        type="text"
                        id="recordSearch"
                        name="q"
                        value="{{ $search ?? '' }}"
                        class="health-records-search"
                        placeholder="Search by student name or ID..."
                    >
                </div>
                <button type="button" class="health-records-search-toggle" id="healthRecordsSearchToggle" aria-label="Open search" aria-expanded="false" aria-controls="recordSearch" onclick="document.getElementById('healthRecordsSearchShell').classList.toggle('is-open'); document.getElementById('recordSearch').focus();">
                    <x-outline-icon name="magnifying-glass" />
                </button>
            </div>
        </div>
    </div>

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
            $summaryHasRequirements = filled($summaryRecord->medical_certificate)
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
                $pendingApprovalRecordIds[] = $summaryRecord->id;
            }

            if ($summaryIsConditional) {
                $healthSummaryStats['pending_conditional']++;
                $pendingConditionalRecordIds[] = $summaryRecord->id;
            }
        }

        $healthProfileSummaryRecords = $records
            ->filter(fn ($record) => in_array($record->clearance_status, ['Issued', 'Fully Cleared'], true))
            ->values();
        $healthSummaryStats['total_approved'] = $healthProfileSummaryRecords->count();
    @endphp

    {{-- Summary Action Cards --}}
    <div class="summary-container">
        <div class="summary-item">
            <div class="card p-3 health-summary-action-card health-summary-metric-card is-approved" style="padding: 15px 24px !important;">
                <div class="health-summary-row">
                    <small class="health-summary-metric-label"><span>Total Approved</span></small>
                    <h3 class="health-summary-metric-count">{{ $healthSummaryStats['total_approved'] }}</h3>
                </div>
            </div>
        </div>
        <div class="summary-item">
            <div class="card p-3 health-summary-action-card health-summary-metric-card is-condition" style="padding: 15px 24px !important;">
                <div class="health-summary-row">
                    <small class="health-summary-metric-label"><span>With Medical Conditions</span></small>
                    <h3 class="health-summary-metric-count">{{ $healthSummaryStats['with_conditions'] }}</h3>
                </div>
            </div>
        </div>
        <div class="summary-item">
            <button type="button" class="card p-3 awaiting-links-btn health-summary-info-btn" id="pendingApprovalInfoBtn" style="padding: 8px 18px 8px 12px !important; border-left: 5px solid #70131B; width: 100%; display: flex; align-items: center; justify-content: flex-start; gap: 12px; min-height: 90px;" onclick="document.getElementById('pendingApprovalInfoModal').style.display='flex';">
                <div class="workflow-card-icon">
                    <svg viewBox="0 0 24 24" fill="none" aria-hidden="true">
                        <path d="M7 4h7l4 4v12H7V4z" stroke="currentColor" stroke-linejoin="round"/>
                        <path d="M14 4v4h4" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round"/>
                        <path d="M9.5 14l2 2 4-5" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round"/>
                    </svg>
                </div>
                <span class="workflow-card-divider" aria-hidden="true"></span>
                <div class="workflow-card-text">
                    <small class="workflow-card-label">Pending Approval</small>
                    <h3 class="workflow-card-count">{{ $healthSummaryStats['pending_approval'] }}</h3>
                </div>
            </button>
        </div>
        <div class="summary-item">
            <button type="button" class="card p-3 awaiting-links-btn health-summary-info-btn" id="pendingConditionalInfoBtn" style="padding: 8px 18px 8px 12px !important; border-left: 5px solid #70131B; width: 100%; display: flex; align-items: center; justify-content: flex-start; gap: 12px; min-height: 90px;" onclick="document.getElementById('pendingConditionalInfoModal').style.display='flex';">
                <div class="workflow-card-icon">
                    <x-outline-icon name="exclamation-triangle" />
                </div>
                <span class="workflow-card-divider" aria-hidden="true"></span>
                <div class="workflow-card-text">
                    <small class="workflow-card-label">Pending Compliance</small>
                    <h3 class="workflow-card-count">{{ $healthSummaryStats['pending_conditional'] }}</h3>
                </div>
            </button>
        </div>
    </div>

    {{-- Main Table Card --}}
<div class="card health-summary-card">
    <div class="health-table-head">
        <div class="health-table-title">Approved Health Records</div>
    </div>
    <table id="healthTable">
        <thead>
            <tr>
                <th>Full Name</th>
                <th>Course / Yr / Sec</th>
                <th>Medical Condition</th>
                <th>Clearance Status</th>
                <th>PUPTAS Status</th>
                <th>Submitted At</th>
                <th style="text-align: center;">Actions</th>
            </tr>
        </thead>
        <tbody>
            @forelse($healthProfileSummaryRecords as $record)
                @php
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
                    $recordYearSection = trim((string) implode('-', array_filter([
                        trim((string) optional($record->user)->year),
                        trim((string) optional($record->user)->section),
                    ])));
                    $recordCourseDisplay = trim($recordCourseName . ($recordYearSection !== '' ? ' ' . $recordYearSection : ''));
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
                        'approve_url' => route('admin.update_clearance', $record->id),
                        'resubmission_url' => route('admin.health_profile.request_resubmission', $record->id),
                        'documents' => [
                            [
                                'title' => '2x2 Photo',
                                'url' => $record->student_photo ? route('walkin.document', [
                                    'healthProfile' => $record->id,
                                    'document' => 'student_photo',
                                ]) : '',
                                'meta' => [
                                    'Guideline' => 'Formal white-background photo.',
                                ],
                            ],
                            [
                                'title' => 'Health Information Form',
                                'url' => route('walkin.healthForm', [
                                    'healthProfile' => $record->id,
                                ]),
                                'meta' => [
                                    'Type' => 'Official health form layout',
                                ],
                            ],
                            [
                                'title' => 'Medical Certificate',
                                'url' => $record->medical_certificate ? route('walkin.document', [
                                    'healthProfile' => $record->id,
                                    'document' => 'medical_certificate',
                                ]) : '',
                                'meta' => [
                                    'Doctor' => $record->doctor_name ?: '-',
                                    'Certificate Date' => optional($record->med_cert_date)->format('M d, Y') ?: '-',
                                    'Findings' => $record->med_cert_findings ?: '-',
                                ],
                            ],
                            [
                                'title' => 'Chest X-ray Result',
                                'url' => $record->chest_xray_result ? route('walkin.document', [
                                    'healthProfile' => $record->id,
                                    'document' => 'chest_xray_result',
                                ]) : '',
                                'meta' => [
                                    'Exam Date' => optional($record->xray_date)->format('M d, Y') ?: '-',
                                    'Findings' => $record->xray_findings ?: '-',
                                ],
                            ],
                            [
                                'title' => 'Health Declaration',
                                'url' => $record->health_declaration ? route('walkin.document', [
                                    'healthProfile' => $record->id,
                                    'document' => 'health_declaration',
                                ]) : '',
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
                    data-view-url="{{ in_array($record->clearance_status, ['Issued', 'Fully Cleared'], true) ? route('admin.show_health', $record->id) : '' }}"
                    title="{{ in_array($record->clearance_status, ['Issued', 'Fully Cleared'], true) ? 'Click to view' : '' }}"
                    class="{{ implode(' ', array_filter([
                        $highlightHealthId !== '' && $highlightHealthId === (string) $record->id ? 'health-highlight-row' : '',
                        in_array($record->clearance_status, ['Issued', 'Fully Cleared'], true) ? 'health-row-clickable' : '',
                    ])) }}"
                >
                    <td>
                        <div class="student-name" style="font-weight: 700;">{{ $record->user->name }}</div>
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
                            <span class="status issued"><i class="fas fa-check-circle me-1"></i> Issued</span>
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

                    <td>
                        <span class="status {{ $puptasStatusClass }}">{{ $puptasStatusLabel }}</span>
                    </td>

                    <td style="color: #94a3b8; font-size: 12px;">
                        {{ $record->created_at->format('M d, Y') }}
                    </td>

                    <td style="text-align: center;">
                        <div class="d-flex justify-content-center">
                            <a href="{{ route('admin.show_health', $record->id) }}" class="btn-action btn-view">
                                <x-outline-icon name="eye" />
                                View
                            </a>
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
                @forelse($records->whereIn('id', $pendingApprovalRecordIds) as $readonlyRecord)
                    @php
                        $readonlyDocs = [
                            '2x2 Photo' => [
                                'key' => 'student_photo',
                                'path' => $readonlyRecord->student_photo,
                                'url' => null,
                            ],
                            'Health Information Form' => [
                                'key' => 'health_form',
                                'path' => true,
                                'url' => route('walkin.healthForm', [
                                    'healthProfile' => $readonlyRecord->id,
                                ]),
                            ],
                            'Medical Certificate' => [
                                'key' => 'medical_certificate',
                                'path' => $readonlyRecord->medical_certificate,
                                'url' => null,
                            ],
                            'Chest X-ray Result' => [
                                'key' => 'chest_xray_result',
                                'path' => $readonlyRecord->chest_xray_result,
                                'url' => null,
                            ],
                            'Health Declaration' => [
                                'key' => 'health_declaration',
                                'path' => $readonlyRecord->health_declaration,
                                'url' => null,
                            ],
                        ];

                        if ($readonlyRecord->pwd_id_proof) {
                            $readonlyDocs['PWD ID Proof'] = [
                                'key' => 'pwd_id_proof',
                                'path' => $readonlyRecord->pwd_id_proof,
                                'url' => null,
                            ];
                        }

                        $readonlyUploadDocs = [
                            '2x2 Student Photo' => [
                                'key' => 'student_photo',
                                'path' => $readonlyRecord->student_photo,
                            ],
                            'Health Information Form' => [
                                'key' => 'health_form',
                                'path' => true,
                                'url' => route('walkin.healthForm', [
                                    'healthProfile' => $readonlyRecord->id,
                                ]),
                            ],
                            'Medical Certificate' => [
                                'key' => 'medical_certificate',
                                'path' => $readonlyRecord->medical_certificate,
                            ],
                            'Chest X-ray Result' => [
                                'key' => 'chest_xray_result',
                                'path' => $readonlyRecord->chest_xray_result,
                            ],
                            'Health Declaration' => [
                                'key' => 'health_declaration',
                                'path' => $readonlyRecord->health_declaration,
                            ],
                        ];

                        if ($readonlyRecord->pwd_id_proof) {
                            $readonlyUploadDocs['PWD ID Proof'] = [
                                'key' => 'pwd_id_proof',
                                'path' => $readonlyRecord->pwd_id_proof,
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
                            'approve_url' => route('admin.update_clearance', $readonlyRecord->id),
                            'resubmission_url' => route('admin.health_profile.request_resubmission', $readonlyRecord->id),
                            'documents' => [
                            [
                                'title' => '2x2 Photo',
                                'url' => $readonlyRecord->student_photo ? route('walkin.document', [
                                    'healthProfile' => $readonlyRecord->id,
                                    'document' => 'student_photo',
                                    ]) : '',
                                    'meta' => [
                                    'Guideline' => 'Formal white-background photo.',
                                ],
                            ],
                            [
                                'title' => 'Health Information Form',
                                'url' => route('walkin.healthForm', [
                                    'healthProfile' => $readonlyRecord->id,
                                ]),
                                'meta' => [
                                    'Type' => 'Official health form layout',
                                ],
                            ],
                            [
                                'title' => 'Medical Certificate',
                                'url' => $readonlyRecord->medical_certificate ? route('walkin.document', [
                                    'healthProfile' => $readonlyRecord->id,
                                    'document' => 'medical_certificate',
                                ]) : '',
                                'meta' => [
                                    'Doctor' => $readonlyRecord->doctor_name ?: '-',
                                    'Certificate Date' => optional($readonlyRecord->med_cert_date)->format('M d, Y') ?: '-',
                                    'Findings' => $readonlyRecord->med_cert_findings ?: '-',
                                ],
                            ],
                            [
                                'title' => 'Chest X-ray Result',
                                'url' => $readonlyRecord->chest_xray_result ? route('walkin.document', [
                                    'healthProfile' => $readonlyRecord->id,
                                    'document' => 'chest_xray_result',
                                ]) : '',
                                'meta' => [
                                    'Exam Date' => optional($readonlyRecord->xray_date)->format('M d, Y') ?: '-',
                                    'Findings' => $readonlyRecord->xray_findings ?: '-',
                                ],
                            ],
                            [
                                'title' => 'Health Declaration',
                                'url' => $readonlyRecord->health_declaration ? route('walkin.document', [
                                        'healthProfile' => $readonlyRecord->id,
                                        'document' => 'health_declaration',
                                    ]) : '',
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
                                        data-review-approve-url="{{ route('admin.update_clearance', $readonlyRecord->id) }}"
                                        data-review-resubmission-url="{{ route('admin.health_profile.request_resubmission', $readonlyRecord->id) }}"
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
                                                href="{{ $document['url'] ?: route('walkin.document', [
                                                        'healthProfile' => $readonlyRecord->id,
                                                        'document' => $document['key'],
                                                    ]) }}"
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
                                        $documentUrl = $document['url'] ?? route('walkin.document', [
                                            'healthProfile' => $readonlyRecord->id,
                                            'document' => $document['key'],
                                        ]);
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
                    <button type="button" class="readonly-pagination-btn" data-pagination-prev>Previous</button>
                    <button type="button" class="readonly-pagination-btn" data-pagination-next>Next</button>
                </div>
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
                @forelse($records->whereIn('id', $pendingConditionalRecordIds) as $readonlyRecord)
                    @php
                        $readonlyHasCondition = $readonlyRecord->hasMedicalCondition();
                        $readonlyReference = $readonlyRecord->reference_number ?: $readonlyRecord->student_number ?: optional($readonlyRecord->user)->student_number ?: '-';
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
                                <div class="readonly-field"><span>Student ID Number</span><strong>{{ $readonlyRecord->student_id ?: optional($readonlyRecord->user)->student_id ?: optional($readonlyRecord->user)->student_number ?: '-' }}</strong></div>
                                <div class="readonly-field"><span>Submission Reference Number</span><strong>{{ $readonlyRecord->reference_number ?: $readonlyRecord->student_number ?: optional($readonlyRecord->user)->student_number ?: '-' }}</strong></div>
                                <div class="readonly-field">
                                    <span>Health Declaration</span>
                                    <strong>
                                        @if($readonlyRecord->health_declaration)
                                            <a href="{{ route('walkin.document', ['healthProfile' => $readonlyRecord->id, 'document' => 'health_declaration']) }}" target="_blank" rel="noopener noreferrer">Open uploaded file</a>
                                        @else
                                            Missing / Not yet uploaded
                                        @endif
                                    </strong>
                                </div>
                                <div class="readonly-field readonly-final-review-field">
                                    <span>Final Review</span>
                                    <form method="POST" action="{{ route('admin.health_profile.for_final_review', $readonlyRecord->id) }}">
                                        @csrf
                                        <button type="submit" class="readonly-final-review-btn">
                                            <x-outline-icon name="check" />
                                            For Final Review
                                        </button>
                                    </form>
                                </div>
                                <div class="readonly-field"><span>Last Updated Nurse Tracking Remarks</span><strong>{{ $readonlyRecord->medical_condition_remarks ?: $readonlyRecord->pending_reason ?: '-' }}</strong></div>
                                <div class="readonly-field readonly-resubmission-field">
                                    <span>Document Resubmission</span>
                                    <form
                                        method="POST"
                                        action="{{ route('admin.health_profile.request_resubmission', $readonlyRecord->id) }}"
                                        class="readonly-resubmission-form"
                                        onsubmit="if (!this.querySelector('input[name=&quot;resubmission_required_documents[]&quot;]:checked')) { alert('Select at least one document for resubmission.'); return false; } return confirm('Request resubmission and remove the selected uploaded document references from this record?');"
                                    >
                                        @csrf
                                        <input type="hidden" name="pending_reason" value="{{ $readonlyRecord->pending_reason ?: 'Document Resubmission' }}">
                                        <input type="hidden" name="clear_uploaded_documents" value="1">
                                        <input type="hidden" name="return_to" value="health_records">
                                        <details class="readonly-resubmission-box">
                                            <summary class="readonly-resubmission-summary">
                                                <x-outline-icon name="clipboard-document-list" />
                                                Resubmission
                                            </summary>
                                            <div class="readonly-resubmission-panel">
                                                <p class="readonly-resubmission-help">Select the file/s that the student must upload again. Confirming will clear the selected document reference from the database and mark it for resubmission.</p>
                                                <div class="readonly-resubmission-options">
                                                    <label class="readonly-resubmission-option">
                                                        <input type="checkbox" name="resubmission_required_documents[]" value="student_photo">
                                                        <span>2x2 Photo</span>
                                                    </label>
                                                    <label class="readonly-resubmission-option">
                                                        <input type="checkbox" name="resubmission_required_documents[]" value="health_declaration">
                                                        <span>Health Declaration</span>
                                                    </label>
                                                    <label class="readonly-resubmission-option">
                                                        <input type="checkbox" name="resubmission_required_documents[]" value="medical_certificate">
                                                        <span>Medical Certificate</span>
                                                    </label>
                                                    <label class="readonly-resubmission-option">
                                                        <input type="checkbox" name="resubmission_required_documents[]" value="chest_xray_result">
                                                        <span>Chest X-ray Result</span>
                                                    </label>
                                                    <label class="readonly-resubmission-option">
                                                        <input type="checkbox" name="resubmission_required_documents[]" value="pwd_id_proof">
                                                        <span>PWD ID Proof</span>
                                                    </label>
                                                </div>
                                                <button type="submit" class="readonly-resubmission-submit">Confirm Resubmission</button>
                                            </div>
                                        </details>
                                    </form>
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
                    <button type="button" class="readonly-pagination-btn" data-pagination-prev>Previous</button>
                    <button type="button" class="readonly-pagination-btn" data-pagination-next>Next</button>
                </div>
            </div>
        </div>
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

                <label class="verify-check-row verify-resubmission-toggle-row">
                    <input type="checkbox" id="verifyNeedsDocumentResubmission" value="1">
                    <span>
                        <strong>Needs Document Resubmission</strong>
                        <small>Use this only when uploaded files are blurred, unreadable, unsigned, incorrect, or need replacement.</small>
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
                    </div>
                    <label class="verify-textarea-field">
                        <span>Remarks <small>(Optional)</small></span>
                        <textarea id="verifyDocumentResubmissionRemarks" rows="3" placeholder="Optional note for the student or clinic tracking."></textarea>
                    </label>
                </div>

                <div class="verify-approval-actions verify-resubmission-only-actions">
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
            <div>
                <h3 class="health-filter-modal-title">Filter Health Forms</h3>
                <p class="health-filter-modal-copy">Narrow the student health form list by course, month, or year level.</p>
            </div>
            <button type="button" class="health-filter-modal-close" id="healthFilterCloseBtn" aria-label="Close filter popup">
                <x-outline-icon name="x-mark" />
            </button>
        </div>

        <form method="GET" class="health-filter-form" id="healthFilterForm">
            <div class="health-filter-field">
                <label for="courseFilter">Course</label>
                <select id="courseFilter" name="course" class="health-filter-select">
                    <option value="">All Courses</option>
                    @foreach(($courseOptions ?? collect()) as $courseOption)
                        <option value="{{ $courseOption }}" {{ ($courseFilter ?? '') === $courseOption ? 'selected' : '' }}>{{ $courseOption }}</option>
                    @endforeach
                </select>
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
            <div class="health-filter-field">
                <label for="yearFilter">Year Level</label>
                <select id="yearFilter" name="year" class="health-filter-select">
                    <option value="">All Year Levels</option>
                    @foreach(($yearOptions ?? collect()) as $yearOption)
                        <option value="{{ $yearOption }}" {{ (string) ($yearFilter ?? '') === (string) $yearOption ? 'selected' : '' }}>{{ $yearOption }}</option>
                    @endforeach
                </select>
            </div>
            <div class="health-filter-actions">
                <button type="submit" class="health-filter-btn">Apply</button>
                <a href="{{ route('admin.health_records') }}" class="health-filter-btn health-filter-btn-reset">Reset</a>
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
    const verifyDocumentResubmissionRemarks = document.getElementById('verifyDocumentResubmissionRemarks');
    const verifyDocumentResubmissionReason = document.getElementById('verifyDocumentResubmissionReason');
    const verifyDocumentResubmissionInputs = Array.from(document.querySelectorAll('#verifyDocumentResubmissionForm input[name="resubmission_required_documents[]"]'));

    function syncDocumentResubmissionReason() {
        if (!verifyDocumentResubmissionReason) {
            return;
        }

        const remarks = verifyDocumentResubmissionRemarks ? verifyDocumentResubmissionRemarks.value.trim() : '';
        verifyDocumentResubmissionReason.value = remarks !== ''
            ? 'Document Resubmission: ' + remarks
            : 'Document Resubmission';
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

        if (verifyDocumentResubmissionRemarks) {
            verifyDocumentResubmissionRemarks.value = '';
        }

        verifyDocumentResubmissionInputs.forEach(function (input) {
            input.checked = false;
        });

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

            if (!hasSelectedDocument) {
                event.preventDefault();
                const firstInput = verifyDocumentResubmissionInputs[0];
                if (firstInput) {
                    firstInput.setCustomValidity('Select at least one document to resubmit.');
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
            const pageSize = Math.max(1, parseInt(pagination?.getAttribute('data-page-size') || '5', 10));
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

            const shouldShowPagination = visibleCards.length > pageSize;
            pagination.classList.toggle('is-visible', shouldShowPagination);

            const summary = pagination.querySelector('[data-pagination-summary]');
            if (summary) {
                const showingStart = visibleCards.length === 0 ? 0 : startIndex + 1;
                const showingEnd = Math.min(endIndex, visibleCards.length);
                summary.textContent = `Showing ${showingStart}-${showingEnd} of ${visibleCards.length}`;
            }

            const previousBtn = pagination.querySelector('[data-pagination-prev]');
            const nextBtn = pagination.querySelector('[data-pagination-next]');
            if (previousBtn) previousBtn.disabled = currentPage <= 1;
            if (nextBtn) nextBtn.disabled = currentPage >= totalPages;
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
</script>
@endpush
