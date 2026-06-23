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
        width: min(900px, 95%);
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
        align-items: flex-start;
        justify-content: space-between;
        gap: 14px;
        padding-bottom: 12px;
        border-bottom: 1px solid rgba(112, 19, 27, 0.1);
    }
    .readonly-record-meta {
        display: flex;
        align-items: flex-start;
        justify-content: flex-end;
        gap: 10px;
        flex-wrap: wrap;
        text-align: right;
    }
    .readonly-record-pill {
        display: grid;
        gap: 3px;
        min-width: 128px;
        padding: 7px 10px;
        border-radius: 12px;
        background: #ffffff;
        border: 1px solid rgba(112, 19, 27, 0.1);
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
    }
    .readonly-reference-value {
        display: inline-flex;
        align-items: center;
        justify-content: flex-end;
        gap: 6px;
    }
    .readonly-copy-btn {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        width: 22px;
        height: 22px;
        flex: 0 0 22px;
        border: 1px solid rgba(112, 19, 27, 0.18);
        border-radius: 7px;
        background: #ffffff;
        color: #70131B;
        cursor: pointer;
        transition: background-color .18s ease, border-color .18s ease, transform .18s ease;
    }
    .readonly-copy-btn svg {
        width: 13px;
        height: 13px;
        stroke-width: 2.4;
    }
    .readonly-copy-btn:hover {
        background: #facc15;
        border-color: #facc15;
        transform: translateY(-1px);
    }
    .readonly-copy-btn.is-copied {
        background: #dcfce7;
        border-color: #86efac;
        color: #166534;
    }
    .readonly-record-pill.status-flag-pill {
        background: #fef3c7;
        border-color: #facc15;
        box-shadow: 0 8px 18px rgba(250, 204, 21, 0.18);
    }
    .readonly-record-pill.status-flag-pill span {
        color: #70131B;
    }
    .readonly-record-pill.status-flag-pill strong {
        color: #70131B;
    }
    .readonly-expand-btn {
        position: relative;
        overflow: hidden;
        align-self: stretch;
        min-width: 76px;
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
        background: #facc15 !important;
        border: 1px solid #facc15 !important;
        border-left: 5px solid #70131B !important;
        color: #70131B !important;
        box-shadow: 0 4px 12px rgba(112, 19, 27, 0.16) !important;
    }
    .health-summary-metric-card * {
        color: #70131B !important;
    }
    .health-summary-metric-label {
        display: block;
        font-size: 9px;
        font-weight: 800;
        line-height: 1.18;
        letter-spacing: 0.8px;
        text-transform: uppercase;
    }
    .health-summary-metric-count {
        margin: 3px 0 0 0;
        font-size: 22px;
        font-weight: 800;
        line-height: 1;
        color: #70131B;
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
        justify-content: space-between;
        gap: 16px;
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

    .verification-doc-toggle {
        border: 1px solid rgba(112, 19, 27, 0.16);
        border-radius: 999px;
        background: #70131B;
        color: #ffffff;
        padding: 10px 14px;
        font-size: 12px;
        font-weight: 900;
        cursor: pointer;
    }
    .verification-doc-grid {
        display: none;
        grid-template-columns: repeat(2, minmax(0, 1fr));
        gap: 10px;
    }
    .verification-doc-grid.is-open {
        display: grid;
    }
    .verification-doc-card {
        border: 1px solid #e2e8f0;
        border-radius: 14px;
        background: #ffffff;
        padding: 12px;
    }
    .verification-doc-card strong {
        display: block;
        color: #70131B;
        font-size: 13px;
        margin-bottom: 6px;
    }
    .verification-doc-card a {
        display: inline-flex;
        margin-bottom: 8px;
        color: #0369a1;
        font-size: 12px;
        font-weight: 900;
        text-decoration: none;
    }
    .verification-doc-card dl {
        margin: 0;
        display: grid;
        gap: 4px;
        font-size: 12px;
    }
    .verification-doc-card dt {
        color: #64748b;
        font-weight: 800;
    }
    .verification-doc-card dd {
        margin: 0 0 4px;
        color: #111827;
        font-weight: 700;
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
        width: min(760px, 100%);
        border-radius: 18px;
        background: #ffffff;
        border: 1px solid #e2e8f0;
        box-shadow: 0 24px 60px rgba(15, 23, 42, 0.22);
        overflow: hidden;
    }

    .verify-approval-modal-head {
        display: flex;
        align-items: flex-start;
        justify-content: space-between;
        gap: 12px;
        padding: 16px 18px;
        border-bottom: 1px solid #e2e8f0;
        background: #f8fafc;
    }

    .verify-approval-modal-title {
        margin: 0;
        font-size: 17px;
        font-weight: 900;
        color: #70131B;
    }

    .verify-approval-modal-copy {
        margin: 4px 0 0;
        font-size: 12px;
        color: #64748b;
    }

    .verify-approval-modal-close {
        width: 36px;
        height: 36px;
        border-radius: 999px;
        border: 1px solid #cbd5e1;
        background: #ffffff;
        color: #111827;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        cursor: pointer;
    }

    .verify-approval-modal-close svg {
        width: 16px;
        height: 16px;
    }

    .verify-approval-body {
        padding: 16px 18px 18px;
        display: grid;
        gap: 14px;
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

    .verify-approval-doc-title {
        margin: 0 0 8px;
        font-size: 13px;
        font-weight: 800;
        color: #1e293b;
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
    }

    .verify-approval-btn {
        border-radius: 999px;
        padding: 9px 16px;
        font-size: 12px;
        font-weight: 800;
        border: 1px solid transparent;
        cursor: pointer;
    }

    .verify-approval-btn-cancel {
        background: #e2e8f0;
        color: #334155;
        border-color: #cbd5e1;
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

    html[data-theme="dark"] .verify-approval-modal-card {
        background: #0f172a;
        border-color: #334155;
    }

    html[data-theme="dark"] .verify-approval-modal-head {
        background: #111827;
        border-color: #334155;
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
            $summaryIsConditional = in_array($summaryStatus, ['Pending/Conditional', 'Rejected'], true)
                || trim((string) ($summaryRecord->pending_reason ?? '')) !== ''
                || trim((string) ($summaryRecord->medical_condition_remarks ?? '')) !== '';

            if (
                $summaryRecord->has_disability === 'Yes'
                || $summaryRecord->has_illness === 'Yes'
                || trim((string) ($summaryRecord->medical_condition_remarks ?? '')) !== ''
            ) {
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
    @endphp

    {{-- Summary Action Cards --}}
    <div class="summary-container">
        <div class="summary-item">
            <div class="card p-3 health-summary-action-card health-summary-metric-card" style="padding: 15px 24px !important;">
                <div class="health-summary-row">
                    <small class="health-summary-metric-label"><span>Total</span><span>Submissions</span></small>
                    <h3 class="health-summary-metric-count">{{ $healthSummaryStats['total'] }}</h3>
                </div>
            </div>
        </div>
        <div class="summary-item">
            <div class="card p-3 health-summary-action-card health-summary-metric-card" style="padding: 15px 24px !important;">
                <div class="health-summary-row">
                    <small class="health-summary-metric-label"><span>With Medical</span><span>Conditions</span></small>
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
        <div class="health-table-title">Health Profile Summary</div>
    </div>
    <table id="healthTable">
        <thead>
            <tr>
                <th>ID Number</th>
                <th>Full Name</th>
                <th>Course / Yr / Sec</th>
                <th>Medical Condition</th>
                <th>Clearance Status</th>
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
                    $recordPayload = [
                        'id' => $record->id,
                        'name' => optional($record->user)->name ?: '-',
                        'email' => optional($record->user)->email ?: '-',
                        'reference_number' => $record->reference_number ?: $record->student_number ?: optional($record->user)->student_number ?: '-',
                        'student_id' => $record->student_id ?: optional($record->user)->student_id ?: '-',
                        'student_number' => optional($record->user)->student_number ?: optional($record->user)->student_id ?: '-',
                        'course' => trim((string) (($record->course_college ?: optional($record->user)->course) . ' ' . optional($record->user)->year . '-' . optional($record->user)->section)),
                        'status' => $recordStatus ?: 'For Verification',
                        'pending_reason' => $record->pending_reason ?: '',
                        'medical_condition_remarks' => $record->medical_condition_remarks ?: '',
                        'physical_assessment_status' => $record->physical_assessment_status ?: 'Not Yet Conducted',
                        'documents_valid' => (bool) $record->documents_valid,
                        'approve_url' => route('admin.update_clearance', $record->id),
                        'documents' => [
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
                                'title' => '2x2 Photo',
                                'url' => $record->student_photo ? route('walkin.document', [
                                    'healthProfile' => $record->id,
                                    'document' => 'student_photo',
                                ]) : '',
                                'meta' => [
                                    'Guideline' => 'Formal white-background photo.',
                                ],
                            ],
                        ],
                    ];
                @endphp
                <tr
                    data-health-row
                    data-health-id="{{ $record->id }}"
                    data-health-tab="{{ $healthTabState }}"
                    data-health-condition="{{ $record->has_disability == 'Yes' ? 'with_conditions' : 'none' }}"
                    data-record-payload="{{ e(json_encode($recordPayload)) }}"
                    data-view-url="{{ in_array($record->clearance_status, ['Issued', 'Fully Cleared'], true) ? route('admin.show_health', $record->id) : '' }}"
                    title="{{ in_array($record->clearance_status, ['Issued', 'Fully Cleared'], true) ? 'Click to view' : '' }}"
                    class="{{ implode(' ', array_filter([
                        $highlightHealthId !== '' && $highlightHealthId === (string) $record->id ? 'health-highlight-row' : '',
                        in_array($record->clearance_status, ['Issued', 'Fully Cleared'], true) ? 'health-row-clickable' : '',
                    ])) }}"
                >
                    <td class="fw-bold">{{ $record->user->student_number ?: $record->user->student_id }}</td>
                    <td>
                        <div class="student-name" style="font-weight: 700;">{{ $record->user->name }}</div>
                    </td>
                    <td>{{ $record->course_college ?: $record->user->course }} {{ $record->user->year }}-{{ $record->user->section }}</td>
                    
                    {{-- Column 1: Medical Condition Status --}}
                    <td>
                        @if(
                            $record->has_disability === 'Yes'
                            || $record->has_illness === 'Yes'
                            || trim((string) ($record->medical_condition_remarks ?? '')) !== ''
                        )
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
    <div class="awaiting-links-modal-shell" style="max-width: 760px;">
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
            <div class="pending-approval-list" id="pendingApprovalRecordsList">
                @forelse($records->whereIn('id', $pendingApprovalRecordIds) as $readonlyRecord)
                    @php
                        $readonlyDocs = [
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
                        ];

                        if ($readonlyRecord->pwd_id_proof) {
                            $readonlyDocs['PWD ID Proof'] = [
                                'key' => 'pwd_id_proof',
                                'path' => $readonlyRecord->pwd_id_proof,
                                'url' => null,
                            ];
                        }
                    @endphp
                    <article class="readonly-record-card">
                        <div class="readonly-record-head">
                            <div>
                                <h4 class="readonly-record-name">{{ optional($readonlyRecord->user)->name ?: 'Unnamed Student' }}</h4>
                                <p class="readonly-record-sub">{{ optional($readonlyRecord->user)->email ?: '-' }}</p>
                            </div>
                            <div class="readonly-record-meta">
                                <div class="readonly-record-pill">
                                    <span>Reference Number</span>
                                    @php($readonlyReference = $readonlyRecord->reference_number ?: $readonlyRecord->student_number ?: optional($readonlyRecord->user)->student_number ?: '-')
                                    <strong class="readonly-reference-value">
                                        <span>{{ $readonlyReference }}</span>
                                        @if($readonlyReference !== '-')
                                            <button type="button" class="readonly-copy-btn" data-copy-reference="{{ $readonlyReference }}" aria-label="Copy reference number">
                                                <x-outline-icon name="clipboard-document-list" />
                                            </button>
                                        @endif
                                    </strong>
                                </div>
                                <div class="readonly-record-pill status-flag-pill">
                                    <span>Status Flag</span>
                                    <strong>Pending Verification</strong>
                                </div>
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
                    </article>
                @empty
                    <div class="pending-approval-empty">No students are currently waiting for approval.</div>
                @endforelse
            </div>
        </div>
    </div>
</div>

<div id="pendingConditionalInfoModal" style="display: none; position: fixed; inset: 0; background: rgba(0,0,0,0.5); z-index: 1000; align-items: center; justify-content: center; padding: 20px;" onclick="if(event.target.id==='pendingConditionalInfoModal') document.getElementById('pendingConditionalInfoModal').style.display='none';">
    <div class="awaiting-links-modal-shell" style="max-width: 760px;">
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
            <div class="pending-approval-list">
                @forelse($records->whereIn('id', $pendingConditionalRecordIds) as $readonlyRecord)
                    <article class="readonly-record-card">
                        <div class="readonly-record-head">
                            <div>
                                <h4 class="readonly-record-name">{{ optional($readonlyRecord->user)->name ?: 'Unnamed Student' }}</h4>
                                <p class="readonly-record-sub">{{ optional($readonlyRecord->user)->email ?: '-' }}</p>
                            </div>
                            <div class="readonly-record-meta">
                                <div class="readonly-record-pill">
                                    <span>Reference Number</span>
                                    @php($readonlyReference = $readonlyRecord->reference_number ?: $readonlyRecord->student_number ?: optional($readonlyRecord->user)->student_number ?: '-')
                                    <strong class="readonly-reference-value">
                                        <span>{{ $readonlyReference }}</span>
                                        @if($readonlyReference !== '-')
                                            <button type="button" class="readonly-copy-btn" data-copy-reference="{{ $readonlyReference }}" aria-label="Copy reference number">
                                                <x-outline-icon name="clipboard-document-list" />
                                            </button>
                                        @endif
                                    </strong>
                                </div>
                                <div class="readonly-record-pill status-flag-pill">
                                    <span>Status Flag</span>
                                    <strong>Conditional / Flagged</strong>
                                </div>
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
                        <div class="readonly-record-details">
                            <div class="readonly-record-grid">
                                <div class="readonly-field"><span>Status Flag</span><strong>Conditional / Flagged</strong></div>
                                <div class="readonly-field"><span>Previous Nurse Disapproval Notes</span><strong>{{ $readonlyRecord->pending_reason ?: '-' }}</strong></div>
                                <div class="readonly-field"><span>Student Full Name</span><strong>{{ optional($readonlyRecord->user)->name ?: 'Unnamed Student' }}</strong></div>
                                <div class="readonly-field"><span>Student ID Number</span><strong>{{ $readonlyRecord->student_id ?: optional($readonlyRecord->user)->student_id ?: optional($readonlyRecord->user)->student_number ?: '-' }}</strong></div>
                                <div class="readonly-field"><span>Submission Reference Number</span><strong>{{ $readonlyRecord->reference_number ?: $readonlyRecord->student_number ?: optional($readonlyRecord->user)->student_number ?: '-' }}</strong></div>
                                <div class="readonly-field"><span>Last Updated Nurse Tracking Remarks</span><strong>{{ $readonlyRecord->medical_condition_remarks ?: $readonlyRecord->pending_reason ?: '-' }}</strong></div>
                            </div>
                        </div>
                    </article>
                @empty
                    <div class="pending-approval-empty">No students are currently pending compliance.</div>
                @endforelse
            </div>
        </div>
    </div>
</div>

<div class="verify-approval-modal" id="verifyApprovalModal" aria-hidden="true">
    <div class="verify-approval-modal-card">
        <div class="verify-approval-modal-head">
            <div>
                <h3 class="verify-approval-modal-title">Verify Clinic Requirements</h3>
                <p class="verify-approval-modal-copy">Review uploaded requirements, record nurse screening notes, then set conditional status or issue medical clearance.</p>
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
                    <div class="verify-approval-meta-k">Reference / Student ID</div>
                    <div class="verify-approval-meta-v" id="verifyApprovalStudentCourse">-</div>
                </div>
            </div>

            <div class="verify-approval-doc-wrap">
                <button type="button" class="verification-doc-toggle" id="verificationDocsToggle">View Uploaded Requirements</button>
                <div class="verification-doc-grid" id="verificationDocsGrid"></div>
            </div>

            <form id="verifyApprovalForm" method="POST" action="">
                @csrf
                @method('PUT')
                <input type="hidden" name="clearance_status" id="verifyClearanceStatus" value="Fully Cleared">
                <div class="verify-screening-grid">
                    <div class="verify-screening-field">
                        <label for="verifyMedicalRemarks">Medical Condition / Nurse Remarks</label>
                        <textarea id="verifyMedicalRemarks" name="medical_condition_remarks" class="verify-screening-control" rows="3" placeholder="Document medical condition, document issues, or screening notes."></textarea>
                    </div>
                    <div class="verify-screening-field">
                        <label for="verifyPendingReason">Pending / Conditional Reason</label>
                        <textarea id="verifyPendingReason" name="pending_reason" class="verify-screening-control" rows="3" placeholder="Required when setting this record as pending or conditional."></textarea>
                    </div>
                    <div class="verify-screening-field">
                        <label for="verifyAssessmentStatus">Physical Examination / Assessment Status</label>
                        <select id="verifyAssessmentStatus" name="physical_assessment_status" class="verify-screening-control" required>
                            <option value="Not Yet Conducted">Not Yet Conducted</option>
                            <option value="Completed / Passed">Completed / Passed</option>
                        </select>
                    </div>
                    <label class="verify-check-row" for="verifyDocumentsValid">
                        <input type="checkbox" id="verifyDocumentsValid" name="documents_valid" value="1">
                        Documents are complete, readable, and valid for medical clearance.
                    </label>
                </div>
                <div class="verify-approval-actions">
                    <button type="button" class="verify-approval-btn verify-approval-btn-cancel" id="verifyApprovalCancelBtn">Cancel</button>
                    <button type="submit" class="verify-approval-btn verify-approval-btn-pending" id="verifyPendingBtn">Set as Pending/Conditional</button>
                    <button type="submit" class="verify-approval-btn verify-approval-btn-approve" id="verifyApprovalApproveBtn">Approve & Issue Medical Clearance</button>
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
    const verifyApprovalForm = document.getElementById('verifyApprovalForm');
    const verifyApprovalApproveBtn = document.getElementById('verifyApprovalApproveBtn');
    const verifyPendingBtn = document.getElementById('verifyPendingBtn');
    const verifyClearanceStatus = document.getElementById('verifyClearanceStatus');
    const verifyMedicalRemarks = document.getElementById('verifyMedicalRemarks');
    const verifyPendingReason = document.getElementById('verifyPendingReason');
    const verifyAssessmentStatus = document.getElementById('verifyAssessmentStatus');
    const verifyDocumentsValid = document.getElementById('verifyDocumentsValid');
    const verificationDocsToggle = document.getElementById('verificationDocsToggle');
    const verificationDocsGrid = document.getElementById('verificationDocsGrid');

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
    };

    if (verifyApprovalCloseBtn) {
        verifyApprovalCloseBtn.addEventListener('click', function () {
            setVerifyApprovalModalOpenState(false);
        });
    }

    if (verifyApprovalCancelBtn) {
        verifyApprovalCancelBtn.addEventListener('click', function () {
            setVerifyApprovalModalOpenState(false);
        });
    }

    if (verifyApprovalModal) {
        verifyApprovalModal.addEventListener('click', function (event) {
            if (event.target === verifyApprovalModal) {
                setVerifyApprovalModalOpenState(false);
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
                const originalLabel = button?.getAttribute('aria-label') || 'Copy reference number';
                button?.setAttribute('aria-label', 'Copied');
                button?.classList.add('is-copied');
                window.setTimeout(function () {
                    button?.setAttribute('aria-label', originalLabel);
                    button?.classList.remove('is-copied');
                }, 1200);
            };

            if (navigator.clipboard && window.isSecureContext) {
                navigator.clipboard.writeText(text).then(markCopied).catch(function () {});
                return;
            }

            const tempInput = document.createElement('textarea');
            tempInput.value = text;
            tempInput.setAttribute('readonly', 'readonly');
            tempInput.style.position = 'fixed';
            tempInput.style.left = '-9999px';
            document.body.appendChild(tempInput);
            tempInput.select();
            try {
                document.execCommand('copy');
                markCopied();
            } catch (error) {
                // Browser denied copy; leave the UI unchanged.
            }
            document.body.removeChild(tempInput);
        };

        document.querySelectorAll('[data-copy-reference]').forEach(function (button) {
            button.addEventListener('click', function (event) {
                event.preventDefault();
                event.stopPropagation();
                copyReferenceValue(button.getAttribute('data-copy-reference'), button);
            });
        });

        if (pendingApprovalInfoBtn && pendingApprovalInfoModal) {
            pendingApprovalInfoBtn.addEventListener('click', function () {
                pendingApprovalInfoModal.style.display = 'flex';
            });
        }

        if (closePendingApprovalInfoModal && pendingApprovalInfoModal) {
            closePendingApprovalInfoModal.addEventListener('click', function () {
                pendingApprovalInfoModal.style.display = 'none';
            });
        }

        if (pendingConditionalInfoBtn && pendingConditionalInfoModal) {
            pendingConditionalInfoBtn.addEventListener('click', function () {
                pendingConditionalInfoModal.style.display = 'flex';
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
            const meta = doc && doc.meta ? doc.meta : {};
            const metaMarkup = Object.keys(meta).map(function (key) {
                return `<dt>${escapeHtml(key)}</dt><dd>${escapeHtml(meta[key] || '-')}</dd>`;
            }).join('');
            const linkMarkup = doc.url
                ? `<a href="${escapeHtml(doc.url)}" target="_blank" rel="noopener noreferrer">Open file</a>`
                : '<span class="verify-approval-doc-missing">Missing upload</span>';
            return `
                <div class="verification-doc-card">
                    <strong>${escapeHtml(doc.title || 'Requirement')}</strong>
                    ${linkMarkup}
                    <dl>${metaMarkup}</dl>
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

    function getRecordPayloadFromRow(row) {
        let payload = {};
        try {
            payload = JSON.parse(row?.dataset.recordPayload || '{}');
        } catch (error) {
            payload = {};
        }

        return payload;
    }

    function openVerificationModalFromRow(row) {
        if (!row) return;
        const payload = getRecordPayloadFromRow(row);

        if (verifyApprovalStudentName) {
            verifyApprovalStudentName.textContent = payload.name || '-';
        }
        if (verifyApprovalStudentNumber) {
            verifyApprovalStudentNumber.textContent = payload.email || '-';
        }
        if (verifyApprovalStudentCourse) {
            verifyApprovalStudentCourse.textContent = `${payload.reference_number || '-'} / ${payload.student_id || '-'}`;
        }
        if (verifyApprovalForm) {
            verifyApprovalForm.setAttribute('action', payload.approve_url || '');
        }
        if (verifyMedicalRemarks) {
            verifyMedicalRemarks.value = payload.medical_condition_remarks || '';
        }
        if (verifyPendingReason) {
            verifyPendingReason.value = payload.pending_reason || '';
        }
        if (verifyAssessmentStatus) {
            verifyAssessmentStatus.value = payload.physical_assessment_status || 'Not Yet Conducted';
        }
        if (verifyDocumentsValid) {
            verifyDocumentsValid.checked = Boolean(payload.documents_valid);
        }
        if (verificationDocsGrid) {
            verificationDocsGrid.classList.remove('is-open');
        }
        renderVerificationDocuments(payload.documents || []);
        syncApprovalButtonState();

        setVerifyApprovalModalOpenState(true);
    }

    document.querySelectorAll('.js-open-verify-modal').forEach(function (button) {
        button.addEventListener('click', function () {
            const row = button.closest('[data-health-row]');
            openVerificationModalFromRow(row);
        });
    });

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
            if (verifyPendingReason && verifyPendingReason.value.trim() === '') {
                event.preventDefault();
                verifyPendingReason.setCustomValidity('Nurse remarks are required for pending or conditional records.');
                verifyPendingReason.reportValidity();
                verifyPendingReason.setCustomValidity('');
            }
        });
    }

    if (verifyApprovalApproveBtn && verifyClearanceStatus) {
        verifyApprovalApproveBtn.addEventListener('click', function (event) {
            verifyClearanceStatus.value = 'Fully Cleared';
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

</script>
@endpush
