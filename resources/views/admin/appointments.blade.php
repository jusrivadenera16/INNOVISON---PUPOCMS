@extends('layouts.admin')

@section('title', 'Manage Appointments')

@push('styles')
<style>
    /* Table Styling */
    .card {
        background: #fff;
        border-radius: 12px;
        padding: 24px;
        box-shadow: 0 4px 12px rgba(0,0,0,0.05);
        box-shadow:
            0 4px 12px rgba(0,0,0,0.05),
            inset 0 1px 0 rgba(255,255,255,0.72);
    }

    .appointments-summary-card {
        position: relative;
        overflow: visible;
    }

    .appointments-summary-card::before {
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

    .appointments-summary-title {
        font-weight: 800;
    }
    
    table { width: 100%; border-collapse: collapse; margin-top: 15px; }
    
    h2,
    .card,
    .card *:not(.status):not(.type-badge):not(.btn-action):not(.dialog-btn):not(.btn-add-walkin):not(.appointment-action-menu-toggle):not(.appointment-action-menu-toggle *):not(.appointment-action-menu-item):not(.appointment-action-menu-state):not(.appointment-action-menu-item *):not(.appointment-action-menu-state *) {
        color: #111827;
    }

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
    .status { padding: 5px 10px; border-radius: 99px; font-size: 11px; font-weight: 700; text-transform: uppercase; }
    .status.pending { background: #fff7ed; color: #c2410c; }
    .status.approved { background: #dcfce7; color: #15803d; }
    .status.cancelled { background: #fee2e2; color: #b91c1c; }
    .status.completed { background: #e0f2fe; color: #0369a1; }
    .status.expired { background: #f3f4f6; color: #4b5563; }
    .status.missed { background: #ffedd5; color: #9a3412; }
    
    /* Type Badges */
    .type-badge { padding: 4px 8px; border-radius: 6px; font-size: 10px; font-weight: 800; text-transform: uppercase; border: 1px solid; }
    .type-online { background: #eff6ff; color: #1d4ed8; border-color: #dbeafe; }
    .type-walkin { background: #fdf2f8; color: #be185d; border-color: #fce7f3; }

    /* Buttons */
    .btn-action {
        padding: 6px 10px;
        border-radius: 6px;
        font-size: 12px;
        font-weight: 600;
        cursor: pointer;
        border: none;
        transition: 0.2s;
        text-decoration: none;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        margin-right: 4px;
    }
    .action-list {
        display: flex;
        align-items: center;
        gap: 6px;
        flex-wrap: wrap;
    }
    .appointment-action-menu-wrap {
        position: relative;
        display: inline-flex;
        justify-content: center;
        z-index: 5;
    }
    .appointment-action-menu-wrap.is-open {
        z-index: 60;
    }
    .appointment-action-menu-toggle {
        min-width: 108px;
        min-height: 38px;
        padding: 8px 14px;
        border-radius: 999px;
        border: 1px solid #8f2230;
        background: linear-gradient(135deg, #70131B, #8f2230);
        color: #ffffff;
        font-size: 12px;
        font-weight: 800;
        letter-spacing: 0.01em;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        gap: 8px;
        cursor: pointer;
        color: #ffffff !important;
        box-shadow:
            0 0 0 3px rgba(112, 19, 27, 0.10),
            0 10px 20px rgba(112, 19, 27, 0.18);
        transition: transform .18s ease, box-shadow .18s ease, border-color .18s ease;
    }
    .appointment-action-menu-toggle,
    .appointment-action-menu-toggle span,
    .appointment-action-menu-toggle svg {
        color: #ffffff !important;
    }
    .appointment-action-menu-toggle:hover,
    .appointment-action-menu-wrap.is-open .appointment-action-menu-toggle {
        transform: translateY(-1px);
        border-color: #facc15;
        box-shadow:
            0 0 0 3px rgba(250, 204, 21, 0.16),
            0 14px 24px rgba(112, 19, 27, 0.18);
    }
    .appointment-action-menu-toggle svg {
        width: 16px;
        height: 16px;
        flex: 0 0 auto;
        stroke-width: 2;
    }
    .appointment-action-menu {
        position: absolute;
        top: calc(100% + 10px);
        right: 0;
        min-width: 220px;
        padding: 8px;
        border-radius: 16px;
        background: #ffffff;
        border: 1px solid rgba(127, 29, 45, 0.12);
        box-shadow: 0 18px 32px rgba(15, 23, 42, 0.14);
        display: none;
        z-index: 30;
    }
    #apptTable,
    #apptTable tbody,
    #apptTable tr,
    #apptTable td {
        overflow: visible;
    }
    .appointment-action-menu-wrap.is-open .appointment-action-menu {
        display: grid;
        gap: 6px;
    }
    .appointment-action-menu-item,
    .appointment-action-menu-state {
        width: 100%;
        min-height: 40px;
        padding: 10px 12px;
        border-radius: 12px;
        font-size: 12px;
        font-weight: 800;
        text-decoration: none;
        display: inline-flex;
        align-items: center;
        justify-content: flex-start;
        gap: 10px;
        border: 1px solid transparent;
        background: #f8fafc;
        color: #334155;
        transition: transform .16s ease, border-color .16s ease, background .16s ease, box-shadow .16s ease;
        cursor: pointer;
    }
    .appointment-action-menu-item:hover {
        transform: translateY(-1px);
        text-decoration: none;
        box-shadow: 0 10px 18px rgba(15, 23, 42, 0.08);
    }
    .appointment-action-menu-item svg,
    .appointment-action-menu-state svg {
        width: 15px;
        height: 15px;
        flex: 0 0 auto;
        stroke-width: 2;
    }
    .appointment-action-menu-item.is-view {
        background: #fff3f5;
        color: #70131B !important;
        border-color: #f0d7dc;
    }
    .appointment-action-menu-item.is-view:hover {
        background: #fae9ed;
        border-color: #dfb7c0;
    }
    .appointment-action-menu-item.is-approve {
        background: #ecfdf5;
        color: #166534 !important;
        border-color: #bbf7d0;
    }
    .appointment-action-menu-item.is-approve:hover {
        background: #dcfce7;
        border-color: #86efac;
    }
    .appointment-action-menu-item.is-reschedule {
        background: #fffbeb;
        color: #92400e !important;
        border-color: #fde68a;
    }
    .appointment-action-menu-item.is-reschedule:hover {
        background: #fef3c7;
        border-color: #facc15;
    }
    .appointment-action-menu-item.is-consult {
        background: #eff6ff;
        color: #1d4ed8 !important;
        border-color: #bfdbfe;
    }
    .appointment-action-menu-item.is-consult:hover {
        background: #dbeafe;
        border-color: #93c5fd;
    }
    .appointment-action-menu-item.is-missed {
        background: #fff7ed;
        color: #9a3412 !important;
        border-color: #fed7aa;
    }
    .appointment-action-menu-item.is-missed:hover {
        background: #ffedd5;
        border-color: #fdba74;
    }
    .appointment-action-menu-item.is-reject {
        background: #fff1f2;
        color: #b91c1c !important;
        border-color: #fecdd3;
    }
    .appointment-action-menu-item.is-reject:hover {
        background: #ffe4e6;
        border-color: #fda4af;
    }
    .appointment-action-menu-state {
        background: #e2e8f0;
        color: #64748b !important;
        border-color: #cbd5e1;
        cursor: not-allowed;
    }
    .appointment-inline-pill {
        min-width: 122px;
        min-height: 38px;
        padding: 8px 14px;
        border-radius: 999px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        gap: 8px;
        font-size: 12px;
        font-weight: 800;
        box-shadow:
            0 0 0 3px rgba(250, 204, 21, 0.14),
            0 10px 20px rgba(146, 64, 14, 0.10);
    }
    .appointment-inline-pill.is-view {
        background: linear-gradient(135deg, #fff8d6, #ffefb5);
        color: #7c2d12;
        border: 1px solid #facc15;
    }
    .appointment-inline-pill.is-consult {
        background: linear-gradient(135deg, #f7e4e8, #f1cfd7);
        color: #7f1d2d;
        border: 1px solid #e7aebd;
        box-shadow:
            0 0 0 3px rgba(190, 24, 93, 0.10),
            0 10px 20px rgba(127, 29, 45, 0.10);
    }
    .appointment-inline-pill.is-disabled {
        background: linear-gradient(135deg, #e2e8f0, #cbd5e1);
        color: #64748b;
        border: 1px solid #cbd5e1;
        box-shadow:
            0 0 0 3px rgba(148, 163, 184, 0.10),
            0 10px 20px rgba(71, 85, 105, 0.08);
        cursor: not-allowed;
    }
    .appointment-inline-pill svg {
        width: 16px;
        height: 16px;
        flex: 0 0 auto;
        stroke-width: 2;
    }
    .btn-view { background: #fff3f5; color: #70131B; border: 1px solid #f0d7dc; }
    .btn-view:hover { background: #fae9ed; color: #5a0f16; }
    
    .btn-approve { background: #fbecef; color: #70131B; border: 1px solid #f3d7dd; }
    .btn-approve:hover { background: #f7e2e7; }

    .btn-reschedule { background: #f9eef0; color: #7a1b28; border: 1px solid #f0d6dc; }
    .btn-reschedule:hover { background: #f4dde3; }

    .btn-missed { background: #fff7ed; color: #9a3412; border: 1px solid #fed7aa; }
    .btn-missed:hover { background: #ffedd5; }

    .btn-reject,
    .btn-cancel { background: #fee2e2; color: #b91c1c; border: 1px solid #fecaca; }
    .btn-reject:hover,
    .btn-cancel:hover { background: #fecaca; }

    .btn-complete { background: #70131B; color: white; }
    .btn-complete:hover { background: #5a0f16; }

    /* Modal Overlay */
    .modal-overlay {
        display: none;
        position: fixed;
        top: 0; left: 0;
        width: 100%; height: 100%;
        background: rgba(15, 23, 42, 0.58);
        backdrop-filter: blur(10px);
        -webkit-backdrop-filter: blur(10px);
        z-index: 1000;
        justify-content: center;
        align-items: center;
        padding: clamp(14px, 2.2vw, 28px);
        animation: fadeIn 0.2s;
    }
    .modal-box {
        background: rgba(255, 255, 255, 0.98);
        backdrop-filter: none;
        -webkit-backdrop-filter: none;
        padding: 0;
        border-radius: 18px;
        width: min(100%, 620px);
        max-width: 100%;
        max-height: min(760px, calc(100dvh - clamp(24px, 4vw, 56px)));
        position: relative;
        box-shadow: 0 24px 60px rgba(0, 0, 0, 0.16);
        border-left: 1px solid rgba(112, 19, 27, 0.12);
        border-right: 1px solid rgba(112, 19, 27, 0.12);
        border-top: 4px solid #facc15;
        border-bottom: 4px solid #facc15;
        overflow-y: auto;
        display: flex;
        flex-direction: column;
        min-height: 0;
        scrollbar-width: none;
    }
    .modal-box::-webkit-scrollbar { display: none; }
    .main #infoModal .modal-box,
    .main #statusActionModal .modal-box,
    .main #rescheduleModal .modal-box {
        background: rgba(255, 255, 255, 0.98) !important;
        border-left: 1px solid rgba(112, 19, 27, 0.12) !important;
        border-right: 1px solid rgba(112, 19, 27, 0.12) !important;
        border-top: 4px solid #facc15 !important;
        border-bottom: 4px solid #facc15 !important;
        border-radius: 18px !important;
        backdrop-filter: none !important;
        -webkit-backdrop-filter: none !important;
    }
    #rescheduleModal .modal-box {
        width: min(100%, 680px);
    }
    .modal-header {
        display: flex;
        align-items: flex-start;
        justify-content: space-between;
        gap: 16px;
        padding: clamp(14px, 1.5vw, 18px) clamp(16px, 1.8vw, 24px);
        margin: 0;
        border-radius: 0;
        background: linear-gradient(135deg, #70131B, #8f2230);
        border-bottom: 1px solid rgba(255, 255, 255, 0.12);
        position: relative;
        overflow: hidden;
        flex: 0 0 auto;
        position: sticky;
        top: 0;
        z-index: 10;
    }
    .modal-header::after {
        content: "";
        position: absolute;
        right: 54px;
        top: 50%;
        width: 80px;
        height: 80px;
        transform: translateY(-50%);
        border-radius: 999px;
        background: rgba(255, 255, 255, 0.06);
        pointer-events: none;
    }
    .modal-header-main {
        min-width: 0;
        flex: 1 1 auto;
        position: relative;
        z-index: 1;
    }
    .modal-status-badge {
        flex: 0 0 auto;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        min-height: 34px;
        padding: 7px 12px;
        border-radius: 999px;
        font-size: 11px;
        font-weight: 800;
        text-transform: uppercase;
        letter-spacing: 0.04em;
        border: 1px solid transparent;
        white-space: nowrap;
    }
    .modal-status-badge.pending { background: #fff7ed; color: #c2410c; border-color: #fed7aa; }
    .modal-status-badge.approved { background: #dcfce7; color: #15803d; border-color: #bbf7d0; }
    .modal-status-badge.completed { background: #dbeafe; color: #1d4ed8; border-color: #bfdbfe; }
    .modal-status-badge.cancelled { background: #fee2e2; color: #b91c1c; border-color: #fecaca; }
    .modal-status-badge.expired { background: #f3f4f6; color: #4b5563; border-color: #d1d5db; }
    .modal-status-badge.missed { background: #ffedd5; color: #9a3412; border-color: #fdba74; }
    .modal-row {
        margin-bottom: 12px;
        display: grid;
        grid-template-columns: 150px minmax(0, 1fr);
        gap: 16px;
        align-items: start;
        padding: 12px 14px;
        border-radius: 16px;
        border: 1px solid rgba(112, 19, 27, 0.15);
        background: linear-gradient(180deg, #ffffff 0%, #fff8f6 100%);
        box-shadow:
            inset 0 1px 0 rgba(255,255,255,0.82),
            0 8px 18px rgba(112, 19, 27, 0.05);
    }
    .modal-box > .modal-row,
    .modal-box > .dialog-actions,
    .modal-box form > .modal-row,
    .modal-box form > .dialog-actions {
        margin-left: clamp(16px, 2vw, 24px);
        margin-right: clamp(16px, 2vw, 24px);
    }
    .modal-box > .modal-row:first-of-type,
    .modal-box form > .modal-row:first-of-type {
        margin-top: calc(clamp(20px, 2.4vw, 30px) + 1.5px);
    }
    .modal-box form {
        display: flex;
        flex-direction: column;
        min-height: 0;
    }
    .modal-label { font-size: 12px; font-weight: 700; color: #111827; text-transform: uppercase; }
    .modal-val { font-size: 15px; color: #111827; font-weight: 500; }
    .modal-title {
        margin-top: 0;
        border-bottom: 0;
        padding-bottom: 0;
        margin-bottom: 4px;
        color: #ffffff !important;
        display: inline-flex;
        align-items: center;
        padding: 0;
        border-radius: 0;
        border: 0;
        background: transparent;
        box-shadow: none;
    }
    .modal-header .modal-subtitle {
        margin: 6px 0 0;
        color: #ffffff !important;
        font-size: 13px;
        line-height: 1.5;
    }
    .modal-header p,
    .modal-header .modal-header-main p,
    #statusActionSubtitle {
        color: #ffffff !important;
    }
    .modal-subtitle {
        font-size: 14px;
        color: #111827;
        margin-bottom: 16px;
    }
    .modal-header-close {
        width: 40px;
        height: 40px;
        min-width: 40px;
        min-height: 40px;
        border: 1px solid #8f2230;
        background: linear-gradient(135deg, #70131B, #8f2230);
        color: #ffffff;
        border-radius: 999px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        cursor: pointer;
        overflow: hidden;
        position: relative;
        z-index: 2;
        box-shadow:
            0 0 0 3px rgba(112, 19, 27, 0.12),
            0 10px 22px rgba(112, 19, 27, 0.20);
        transition: color .08s linear, transform .18s ease, box-shadow .18s ease, border-color .18s ease;
    }
    .modal-header-close:hover {
        border-color: #facc15;
        transform: translateY(-1px);
        box-shadow:
            0 0 0 3px rgba(250, 204, 21, 0.18),
            0 14px 24px rgba(112, 19, 27, 0.16);
    }
    .modal-header-close svg {
        width: 18px;
        height: 18px;
        stroke-width: 2.2;
        position: relative;
        z-index: 1;
    }
    .modal-header-close::after {
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
        z-index: 0;
    }
    .modal-header-close:hover::after {
        transform: translateX(135%);
    }
    .modal-status-badge.action-approve { background: #dcfce7; color: #15803d; border-color: #bbf7d0; }
    .modal-status-badge.action-reject { background: #fee2e2; color: #b91c1c; border-color: #fecaca; }
    .modal-status-badge.action-missed { background: #ffedd5; color: #9a3412; border-color: #fdba74; }
    .modal-status-badge.action-reschedule { background: #fef3c7; color: #92400e; border-color: #fde68a; }
    .modal-notes {
        background: #fff4c7;
        padding: 10px;
        border-radius: 14px;
        font-size: 13px;
        color: #111827;
        min-height: 72px;
        border: 1px solid rgba(112, 19, 27, 0.28);
    }
    #infoModal .appointment-detail-modal {
        width: min(100%, 1180px);
        max-height: min(820px, calc(100dvh - 42px));
        border: 1px solid rgba(255, 255, 255, 0.78) !important;
        border-top: 1px solid rgba(255, 255, 255, 0.86) !important;
        border-bottom: 1px solid rgba(255, 255, 255, 0.86) !important;
        border-radius: 20px !important;
        box-shadow: 0 34px 78px rgba(15, 23, 42, 0.28);
        background: #ffffff !important;
    }
    #infoModal .appointment-detail-header {
        padding: 28px 38px;
        min-height: 120px;
        align-items: center;
        background:
            radial-gradient(circle at 10% 0%, rgba(255, 255, 255, 0.10), transparent 32%),
            linear-gradient(135deg, #681219 0%, #7c1722 52%, #5a0f16 100%);
    }
    #infoModal .appointment-detail-header::after {
        display: none;
    }
    .appointment-detail-header-main .modal-title {
        display: block;
        margin: 0 0 8px;
        font-size: clamp(1.8rem, 3vw, 2.45rem);
        line-height: 1.08;
        font-weight: 900;
        letter-spacing: 0;
    }
    .appointment-detail-id-line {
        display: flex;
        align-items: center;
        flex-wrap: wrap;
        gap: 8px;
        margin: 0;
        color: #ffffff;
        font-size: 1.08rem;
        font-weight: 800;
    }
    .appointment-detail-id-line strong {
        color: #ffffff;
    }
    .appointment-detail-copy {
        width: 30px;
        height: 30px;
        border: 0;
        border-radius: 9px;
        background: rgba(255, 255, 255, 0.16);
        color: currentColor;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        cursor: pointer;
        transition: background-color .18s ease, transform .18s ease;
    }
    .appointment-detail-copy:hover {
        background: rgba(250, 204, 21, 0.28);
        transform: translateY(-1px);
    }
    .appointment-detail-copy svg {
        width: 17px;
        height: 17px;
    }
    #infoModal .appointment-detail-status {
        min-width: 278px;
        min-height: 70px;
        display: grid;
        grid-template-columns: auto 1fr;
        grid-template-rows: auto auto;
        align-items: center;
        justify-content: start;
        column-gap: 12px;
        padding: 14px 24px;
        border-radius: 16px;
        background: linear-gradient(135deg, #fff7d8, #fffdf4);
        color: #92400e;
        border-color: rgba(250, 204, 21, 0.54);
        box-shadow: 0 18px 28px rgba(15, 23, 42, 0.16);
        text-align: left;
    }
    #infoModal .appointment-detail-status .appointment-status-dot {
        grid-row: 1 / span 2;
    }
    #mStatusText {
        font-size: 1.03rem;
        font-weight: 900;
        letter-spacing: .04em;
        text-transform: uppercase;
    }
    #mStatusHint {
        color: #1f2937;
        font-size: .9rem;
        font-weight: 600;
        text-transform: none;
        letter-spacing: 0;
    }
    .appointment-detail-body {
        display: grid;
        gap: 22px;
        padding: 28px 38px 24px;
    }
    .appointment-detail-grid {
        display: grid;
        grid-template-columns: minmax(0, 1fr) minmax(0, 1fr);
        gap: 22px;
    }
    .appointment-detail-card,
    .appointment-notes-panel,
    .appointment-timeline-panel {
        border: 1px solid rgba(148, 163, 184, 0.24);
        border-radius: 16px;
        background: #ffffff;
        box-shadow: 0 12px 28px rgba(15, 23, 42, 0.04);
    }
    .appointment-detail-card {
        padding: 22px;
    }
    .appointment-detail-card h4,
    .appointment-timeline-panel h4 {
        display: flex;
        align-items: center;
        gap: 12px;
        margin: 0 0 22px;
        color: #111827;
        font-size: .96rem;
        font-weight: 900;
        text-transform: uppercase;
        letter-spacing: .02em;
    }
    .appointment-detail-card h4 svg,
    .appointment-timeline-panel h4 svg,
    .appointment-notes-title svg,
    .appointment-info-list svg,
    .appointment-student-info svg,
    .appointment-detail-footer svg {
        width: 22px;
        height: 22px;
        color: #2563eb;
        flex: 0 0 auto;
    }
    .appointment-student-layout {
        display: grid;
        grid-template-columns: 132px minmax(0, 1fr);
        gap: 28px;
        align-items: center;
    }
    .appointment-avatar {
        width: 112px;
        height: 112px;
        border-radius: 999px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        background: linear-gradient(135deg, #e8e4ff, #d8d4f4);
        color: #111827;
        font-size: 2.15rem;
        font-weight: 900;
        box-shadow: inset 0 1px 0 rgba(255,255,255,.7);
        overflow: hidden;
    }
    .appointment-avatar img {
        width: 100%;
        height: 100%;
        object-fit: cover;
        display: block;
    }
    .appointment-student-info {
        display: grid;
        gap: 14px;
        min-width: 0;
    }
    .appointment-student-info > strong {
        color: #0f172a;
        font-size: clamp(1.15rem, 2vw, 1.55rem);
        font-weight: 900;
        line-height: 1.2;
    }
    .appointment-student-info p {
        display: flex;
        align-items: center;
        gap: 12px;
        margin: 0;
        color: #111827;
        font-size: 1rem;
        font-weight: 600;
        min-width: 0;
    }
    .appointment-student-info p span {
        min-width: 0;
        overflow-wrap: anywhere;
    }
    .appointment-info-list {
        display: grid;
        gap: 16px;
    }
    .appointment-info-list > div {
        display: grid;
        grid-template-columns: minmax(170px, .75fr) minmax(0, 1fr);
        gap: 18px;
        align-items: center;
    }
    .appointment-info-list span {
        display: inline-flex;
        align-items: center;
        gap: 12px;
        color: #111827;
        font-size: .98rem;
        font-weight: 600;
    }
    .appointment-info-list strong {
        color: #111827;
        font-size: 1rem;
        font-weight: 700;
    }
    .appointment-service-pill {
        width: fit-content;
        max-width: 100%;
        padding: 9px 18px;
        border-radius: 12px;
        background: #dbeafe;
        color: #111827;
        overflow-wrap: anywhere;
    }
    .appointment-inline-status {
        display: inline-flex;
        align-items: center;
        gap: 12px;
    }
    .appointment-status-dot {
        width: 16px;
        height: 16px;
        border-radius: 999px;
        background: #f6b51e;
        box-shadow: 0 0 0 5px rgba(246, 181, 30, 0.14);
        flex: 0 0 auto;
    }
    .appointment-notes-panel {
        padding: 22px 26px;
        background: linear-gradient(135deg, #fff9e7, #fffdf8);
        border-color: rgba(245, 158, 11, 0.28);
    }
    .appointment-notes-title {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 12px;
        margin-bottom: 14px;
        color: #111827;
        font-weight: 900;
        text-transform: uppercase;
        letter-spacing: .02em;
    }
    .appointment-notes-title > span {
        display: inline-flex;
        align-items: center;
        gap: 12px;
    }
    .appointment-notes-panel p {
        margin: 0;
        color: #111827;
        font-size: 1rem;
        line-height: 1.6;
        overflow-wrap: anywhere;
    }
    .appointment-timeline-panel {
        padding: 22px 26px 28px;
    }
    .appointment-timeline {
        display: grid;
        grid-template-columns: minmax(88px, 1fr) minmax(36px, .44fr) minmax(106px, 1fr) minmax(36px, .44fr) minmax(88px, 1fr) minmax(36px, .44fr) minmax(88px, 1fr) minmax(36px, .44fr) minmax(88px, 1fr);
        align-items: start;
        gap: 0;
        padding: 18px 18px 0;
    }
    .timeline-step {
        display: grid;
        justify-items: center;
        text-align: center;
        gap: 7px;
        color: #111827;
    }
    .timeline-dot {
        width: 38px;
        height: 38px;
        border-radius: 999px;
        border: 6px solid #edf0f6;
        background: #cbd5e1;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        color: #ffffff;
    }
    .timeline-step.is-done .timeline-dot {
        border-color: #c8e9c0;
        background: #73bc64;
    }
    .timeline-step.is-current .timeline-dot {
        border-color: #fde7a6;
        background: #d58a00;
        box-shadow: 0 0 0 7px rgba(250, 204, 21, 0.16);
    }
    .timeline-step.is-muted .timeline-dot {
        border-color: #edf0f6;
        background: #cbd5e1;
        box-shadow: none;
    }
    .timeline-step.is-rejected .timeline-dot {
        border-color: #fecaca;
        background: #dc2626;
        box-shadow: 0 0 0 7px rgba(220, 38, 38, 0.14);
    }
    .timeline-step.is-rejected strong {
        color: #b91c1c;
    }
    .timeline-dot svg {
        width: 20px;
        height: 20px;
    }
    .timeline-step strong {
        font-size: .98rem;
        font-weight: 900;
    }
    .timeline-step small {
        color: #334155;
        font-size: .9rem;
        font-weight: 600;
        white-space: pre-line;
    }
    .timeline-line {
        height: 3px;
        margin-top: 18px;
        background: repeating-linear-gradient(90deg, #cbd5e1 0 8px, transparent 8px 16px);
    }
    .timeline-line.is-done {
        background: #73bc64;
    }
    .appointment-detail-footer {
        display: grid;
        grid-template-columns: repeat(2, minmax(0, 1fr));
        gap: 18px;
        padding: 16px 24px;
        border-radius: 14px;
        background: #f5f7fb;
        color: #1f2937;
        font-size: .94rem;
        font-weight: 600;
    }
    .appointment-detail-footer span {
        display: inline-flex;
        align-items: center;
        gap: 10px;
        min-width: 0;
        overflow-wrap: anywhere;
    }
    .appointment-detail-copy.is-copied {
        background: rgba(34, 197, 94, 0.22);
        color: #15803d;
    }
    #statusActionModal .status-action-detail-modal {
        width: min(100%, 900px);
        max-height: min(640px, calc(100dvh - 42px));
        border: 1px solid rgba(255, 255, 255, 0.78) !important;
        border-top: 1px solid rgba(250, 204, 21, 0.92) !important;
        border-bottom: 3px solid #facc15 !important;
        border-radius: 20px !important;
        background: #ffffff !important;
        box-shadow: 0 34px 78px rgba(15, 23, 42, 0.28);
    }
    #statusActionModal .appointment-detail-header {
        min-height: 84px;
        padding: 18px 28px;
    }
    #statusActionModal .appointment-detail-status {
        min-width: 0;
        width: fit-content;
        min-height: 36px;
        grid-template-columns: auto auto;
        grid-template-rows: 1fr;
        padding: 8px 16px;
        border-radius: 999px;
        align-self: center;
    }
    #statusActionModal .appointment-detail-status small {
        display: none !important;
    }
    #statusActionModal #sStatusText {
        font-size: .76rem;
        line-height: 1;
    }
    .approval-details-panel {
        padding: 15px 18px 18px;
        border: 1px solid rgba(148, 163, 184, 0.24);
        border-radius: 16px;
        background: #ffffff;
        box-shadow: 0 12px 28px rgba(15, 23, 42, 0.04);
    }
    .approval-details-panel h4 {
        display: flex;
        align-items: center;
        gap: 8px;
        margin: 0 0 13px;
        color: #111827;
        font-size: .76rem;
        font-weight: 900;
        text-transform: uppercase;
        letter-spacing: .03em;
    }
    .approval-details-panel h4 svg {
        width: 16px;
        height: 16px;
        color: #70131B;
    }
    .approval-detail-grid {
        display: grid;
        grid-template-columns: repeat(2, minmax(0, 1fr));
        gap: 12px;
    }
    .approval-detail-card {
        min-height: 76px;
        padding: 13px 14px;
        border-radius: 14px;
        border: 1px solid rgba(112, 19, 27, 0.12);
        background: linear-gradient(180deg, #ffffff 0%, #fffafa 100%);
    }
    .approval-detail-card span {
        display: block;
        color: #6b7a90;
        font-size: .68rem;
        font-weight: 900;
        letter-spacing: .06em;
        text-transform: uppercase;
        margin-bottom: 5px;
    }
    .approval-detail-card strong,
    .approval-detail-card p {
        display: block;
        margin: 0;
        color: #111827;
        font-size: .84rem;
        font-weight: 800;
        line-height: 1.45;
        overflow-wrap: anywhere;
    }
    .approval-detail-card p {
        font-weight: 600;
        color: #334155;
    }
    .approval-reminder-list {
        display: grid;
        gap: 8px;
        margin: 0;
        padding: 0;
        list-style: none;
    }
    .approval-reminder-list li {
        display: flex;
        align-items: flex-start;
        gap: 8px;
        color: #111827;
        font-size: .82rem;
        font-weight: 700;
        line-height: 1.35;
    }
    .approval-reminder-list input {
        position: absolute;
        opacity: 0;
        pointer-events: none;
    }
    .approval-reminder-list label {
        display: flex;
        align-items: flex-start;
        gap: 8px;
        cursor: pointer;
        color: #111827;
        transition: color .16s ease, transform .16s ease;
    }
    .approval-reminder-list label::before {
        content: "";
        width: 18px;
        height: 18px;
        border-radius: 5px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        flex: 0 0 auto;
        background: #ffffff;
        color: #ffffff;
        border: 1px solid rgba(112, 19, 27, .24);
        font-size: .72rem;
        font-weight: 900;
        transition: background-color .16s ease, border-color .16s ease, box-shadow .16s ease;
    }
    .approval-reminder-list input:checked + label::before {
        content: "✓";
        background: #70131B;
        border-color: #70131B;
        box-shadow: 0 8px 14px rgba(112, 19, 27, .16);
    }
    .approval-reminder-list label:hover {
        color: #70131B;
        transform: translateX(1px);
    }
    .approval-message-field {
        margin-top: 12px;
    }
    .approval-message-field label {
        display: block;
        margin-bottom: 7px;
        color: #6b7a90;
        font-size: .68rem;
        font-weight: 900;
        letter-spacing: .06em;
        text-transform: uppercase;
    }
    .approval-message-field textarea {
        width: 100%;
        min-height: 76px;
        resize: vertical;
        border-radius: 12px;
        border: 1px solid rgba(112, 19, 27, 0.15);
        padding: 10px 12px;
        color: #111827;
        font-size: .82rem;
        font-weight: 600;
        line-height: 1.45;
        background: #ffffff;
    }
    .approval-message-field textarea:focus {
        outline: none;
        border-color: #70131B;
        box-shadow: 0 0 0 3px rgba(112, 19, 27, .08);
    }
    .approval-timeline-row {
        display: grid;
        grid-template-columns: minmax(0, 1fr) 260px;
        gap: 14px;
    }
    .approval-timeline-row .appointment-timeline-panel {
        padding: 15px 18px 18px;
    }
    .approval-notice-card {
        display: grid;
        align-content: start;
        gap: 9px;
        padding: 18px;
        border-radius: 16px;
        border: 1px solid rgba(225, 29, 72, 0.16);
        background: linear-gradient(135deg, #fff1f2, #fff8f8);
        color: #70131B;
    }
    .approval-notice-card h4 {
        display: flex;
        align-items: center;
        gap: 8px;
        margin: 0;
        font-size: .85rem;
        font-weight: 900;
    }
    .approval-notice-card svg {
        width: 17px;
        height: 17px;
    }
    .approval-notice-card p {
        margin: 0;
        color: #334155;
        font-size: .82rem;
        font-weight: 700;
        line-height: 1.5;
    }
    #sApprovalTimeline {
        grid-template-columns: minmax(70px, 1fr) minmax(34px, .44fr) minmax(88px, 1fr) minmax(34px, .44fr) minmax(70px, 1fr) minmax(34px, .44fr) minmax(70px, 1fr) minmax(34px, .44fr) minmax(70px, 1fr);
        padding: 8px 0 0;
    }
    #sApprovalTimeline .timeline-dot {
        width: 25px;
        height: 25px;
        border-width: 4px;
    }
    #sApprovalTimeline .timeline-dot svg {
        width: 14px;
        height: 14px;
    }
    #sApprovalTimeline .timeline-step strong {
        font-size: .72rem;
    }
    #sApprovalTimeline .timeline-step small {
        font-size: .64rem;
    }
    #sApprovalTimeline .timeline-line {
        margin-top: 12px;
    }
    .appointment-type-badge {
        width: fit-content;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        min-height: 28px;
        padding: 6px 12px;
        border-radius: 999px;
        border: 1px solid rgba(112, 19, 27, 0.18);
        background: #fbecef;
        color: #70131B;
        font-size: .76rem;
        font-weight: 900;
    }
    .appointment-type-badge.is-online {
        background: #eff6ff;
        color: #1d4ed8;
        border-color: #bfdbfe;
    }
    .appointment-type-badge.is-walkin {
        background: #fff7ed;
        color: #c2410c;
        border-color: #fed7aa;
    }
    #statusActionModal .dialog-actions {
        margin: 0;
        padding-top: 2px;
    }
    #statusActionModal .dialog-btn {
        min-height: 44px;
        border-radius: 14px;
    }
    #statusActionModal .dialog-btn-approve {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        gap: 8px;
        padding: 12px 18px;
        border: 1px solid #8f2230;
        background: linear-gradient(135deg, #70131B 0%, #8f2230 100%);
        color: #ffffff;
        box-shadow: 0 16px 30px rgba(112, 19, 27, .22);
        position: relative;
        overflow: hidden;
    }
    #statusActionModal .dialog-btn-approve::before {
        content: "";
        position: absolute;
        inset: 0;
        background: linear-gradient(120deg, transparent 0%, rgba(250, 204, 21, .38) 45%, transparent 78%);
        transform: translateX(-120%);
        transition: transform .52s ease;
    }
    #statusActionModal .dialog-btn-approve:hover,
    #statusActionModal .dialog-btn-approve:focus-visible {
        background: #facc15;
        border-color: #facc15;
        color: #111827;
        transform: translateY(-1px);
    }
    #statusActionModal .dialog-btn-approve:hover::before,
    #statusActionModal .dialog-btn-approve:focus-visible::before {
        transform: translateX(120%);
    }
    #statusActionModal .dialog-btn-approve svg,
    #statusActionModal .dialog-btn-approve span {
        position: relative;
        z-index: 1;
    }
    #statusActionModal .dialog-btn-approve svg {
        width: 17px;
        height: 17px;
    }
    #statusActionModal .dialog-btn-secondary {
        background: #ffffff;
        color: #70131B;
        border: 1px solid rgba(112, 19, 27, 0.22);
        box-shadow: 0 12px 22px rgba(15, 23, 42, 0.06);
    }
    #statusActionModal .dialog-btn-secondary:hover,
    #statusActionModal .dialog-btn-secondary:focus-visible {
        border-color: #facc15;
        transform: translateY(-1px);
    }
    @media (max-width: 900px) {
        .approval-timeline-row {
            grid-template-columns: 1fr;
        }
        .approval-detail-grid {
            grid-template-columns: 1fr;
        }
    }
    #infoModal .appointment-detail-modal {
        width: min(100%, 900px);
        max-height: min(640px, calc(100dvh - 42px));
    }
    #infoModal .appointment-detail-header {
        min-height: 84px;
        padding: 18px 28px;
    }
    .appointment-detail-header-main .modal-title {
        margin-bottom: 5px;
        font-size: clamp(1.15rem, 1.75vw, 1.45rem);
        line-height: 1.05;
    }
    .appointment-detail-id-line {
        font-size: .76rem;
        gap: 6px;
    }
    .appointment-detail-id-line strong {
        color: #ffffff !important;
    }
    .appointment-detail-copy {
        width: 25px;
        height: 25px;
        border-radius: 8px;
    }
    .appointment-detail-copy svg {
        width: 14px;
        height: 14px;
    }
    #infoModal .appointment-detail-status {
        min-width: 200px;
        min-height: 50px;
        padding: 9px 15px;
        column-gap: 10px;
    }
    #mStatusText {
        font-size: .8rem;
    }
    #mStatusHint {
        font-size: .72rem;
    }
    .appointment-detail-body {
        gap: 13px;
        padding: 18px 28px 18px;
    }
    .appointment-detail-grid {
        gap: 14px;
    }
    .appointment-detail-card {
        padding: 15px;
    }
    .appointment-detail-card h4,
    .appointment-timeline-panel h4 {
        gap: 8px;
        margin-bottom: 13px;
        font-size: .76rem;
        letter-spacing: .03em;
    }
    .appointment-detail-card h4 svg,
    .appointment-timeline-panel h4 svg,
    .appointment-notes-title svg,
    .appointment-info-list svg,
    .appointment-student-info svg,
    .appointment-detail-footer svg {
        width: 16px;
        height: 16px;
        color: #70131B;
    }
    .appointment-student-layout {
        grid-template-columns: 94px minmax(0, 1fr);
        gap: 16px;
    }
    .appointment-avatar {
        width: 78px;
        height: 78px;
        font-size: 1.52rem;
    }
    .appointment-student-info {
        gap: 8px;
    }
    .appointment-student-info > strong {
        font-size: clamp(.98rem, 1.45vw, 1.18rem);
    }
    .appointment-student-info p {
        gap: 8px;
        font-size: .82rem;
    }
    .appointment-info-list {
        gap: 9px;
    }
    .appointment-info-list > div {
        grid-template-columns: minmax(132px, .72fr) minmax(0, 1fr);
        gap: 10px;
    }
    .appointment-info-list span,
    .appointment-info-list strong {
        font-size: .82rem;
    }
    .appointment-service-pill {
        padding: 7px 12px;
        border-radius: 10px;
    }
    .appointment-status-dot {
        width: 13px;
        height: 13px;
        box-shadow: 0 0 0 4px rgba(246, 181, 30, 0.14);
    }
    .appointment-notes-panel {
        padding: 15px 18px;
    }
    .appointment-notes-title {
        margin-bottom: 8px;
        font-size: .8rem;
    }
    .appointment-notes-panel p {
        font-size: .82rem;
        line-height: 1.45;
    }
    .appointment-timeline-panel {
        padding: 15px 18px 18px;
    }
    .appointment-timeline {
        padding: 8px 8px 0;
    }
    .timeline-dot {
        width: 27px;
        height: 27px;
        border-width: 4px;
    }
    .timeline-dot svg {
        width: 15px;
        height: 15px;
    }
    .timeline-step strong {
        font-size: .78rem;
    }
    .timeline-step small {
        font-size: .7rem;
    }
    .timeline-line {
        margin-top: 13px;
    }
    .appointment-detail-footer {
        padding: 11px 16px;
        font-size: .74rem;
    }
    .dialog-actions {
        display: flex;
        gap: 10px;
        justify-content: flex-end;
        margin-top: 22px;
        margin-bottom: clamp(16px, 2vw, 24px);
        padding-top: 10px;
    }
    .dialog-btn {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        text-decoration: none;
        border: none;
        border-radius: 20px;
        padding: 10px 23px;
        font-size: 16px;
        font-weight: 700;
        cursor: pointer;
        transition: 0.2s;
        border-bottom: 1px solid yellow;
        border-bottom-radius: 6px;
    }
    .dialog-btn-confirm {
        background: linear-gradient(135deg, #70131B, #8f2230);
        color: #ffffff;
        border: 1px solid #8f2230;
        box-shadow: 0 12px 24px rgba(112, 19, 27, 0.20);
        gap: 8px;
    }
    .dialog-btn-confirm:hover,
    .dialog-btn-confirm:focus-visible {
        background: linear-gradient(135deg, #facc15, #fde68a);
        color: #70131B;
        border-color: #facc15;
        transform: translateY(-1px);
        outline: none;
    }
    .dialog-btn-confirm svg {
        width: 18px;
        height: 18px;
        stroke-width: 2.2;
        flex: 0 0 auto;
    }
    .dialog-btn-neutral {
        background: #eee;
        color: #333;
    }
    .dialog-btn-neutral:hover {
        background: #e5e7eb;
    }
    .dialog-btn-primary {
        background: #70131B;
        color: #fff;
    }
    .dialog-btn-primary:hover {
        background: #5a0f16;
    }
    .main .dialog-actions a.dialog-btn-primary,
    .main .dialog-actions a.dialog-btn-primary:visited,
    .main .dialog-actions a.dialog-btn-approve,
    .main .dialog-actions a.dialog-btn-approve:visited,
    .main .dialog-actions a.dialog-btn-reject,
    .main .dialog-actions a.dialog-btn-reject:visited,
    .main .dialog-actions a.dialog-btn-warning,
    .main .dialog-actions a.dialog-btn-warning:visited {
        color: #ffffff !important;
    }
    .main .dialog-actions a.dialog-btn-primary:hover,
    .main .dialog-actions a.dialog-btn-primary:focus-visible,
    .main .dialog-actions a.dialog-btn-approve:hover,
    .main .dialog-actions a.dialog-btn-approve:focus-visible,
    .main .dialog-actions a.dialog-btn-reject:hover,
    .main .dialog-actions a.dialog-btn-reject:focus-visible,
    .main .dialog-actions a.dialog-btn-warning:hover,
    .main .dialog-actions a.dialog-btn-warning:focus-visible {
        color: #ffffff !important;
        text-decoration: none;
    }
    .dialog-btn-approve {
        background: #70131B;
        color: #fff;
    }
    .dialog-btn-approve:hover {
        background: #5a0f16;
    }
    .dialog-btn-reject {
        background: #70131B;
        color: #fff;
    }
    .dialog-btn-reject:hover {
        background: #5a0f16;
    }
    .dialog-btn-warning {
        background: #b45309;
        color: #fff;
    }
    .dialog-btn-warning:hover {
        background: #92400e;
    }

    /* Form Inputs for Reschedule */
    .form-input {
        width: 100%;
        min-height: 42px;
        padding: 10px 12px;
        border: 1px solid #cbd5e1;
        border-radius: 12px;
        margin-top: 4px;
        background: rgba(255, 255, 255, 0.96);
        color: #111827;
        box-shadow: inset 0 1px 0 rgba(255,255,255,0.82);
    }
    .form-input:focus {
        outline: none;
        border-color: #8f2230;
        box-shadow:
            0 0 0 3px rgba(143, 34, 48, 0.12),
            inset 0 1px 0 rgba(255,255,255,0.82);
    }
    .modal-row.is-form {
        align-items: center;
    }

    .action-header { margin-bottom: 20px; }

    .appointments-page-title {
        margin: 0;
        color: #111827;
        display: inline-flex;
        align-items: center;
        padding: 10px 18px;
        border-radius: 0 0 14px 14px;
        border: 0;
        border-bottom: 2px solid rgba(112, 19, 27, 0.72);
        background: transparent;
        box-shadow: none;
    }

    .appointments-page-title svg {
        width: 18px;
        height: 18px;
        margin-right: 10px;
        flex: 0 0 auto;
    }

    .appointments-toolbar {
        display: flex;
        justify-content: space-between;
        align-items: center;
        gap: 16px;
        margin-bottom: 20px;
        padding: 16px 18px;
        border-radius: 0 0 20px 20px;
        border: 0;
        border-bottom: 2px solid rgba(234, 215, 160, 0.9);
        background: linear-gradient(135deg, rgba(255, 253, 246, 0.76) 0%, rgba(255, 249, 231, 0.58) 42%, rgba(255, 255, 255, 0.82) 100%);
        box-shadow:
            0 14px 26px rgba(112, 19, 27, 0.05);
    }

    .appointments-toolbar-actions {
        display: flex;
        align-items: center;
        justify-content: flex-end;
        gap: 12px;
        margin-left: auto;
        flex-wrap: wrap;
    }

    .appointments-search-shell {
        display: inline-flex;
        align-items: center;
        gap: 10px;
        justify-content: flex-end;
    }

    .appointments-filter-shell {
        position: relative;
        display: inline-flex;
        align-items: center;
    }

    .appointments-search-wrap {
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

    .appointments-search-shell.is-open .appointments-search-wrap {
        width: 340px;
        flex: 0 0 340px;
        opacity: 1;
        pointer-events: auto;
        transform: translateX(0) scaleX(1);
    }

    .appointments-search-wrap .voice-field-wrap {
        width: 100%;
        background: transparent !important;
        border: 0 !important;
        box-shadow: none !important;
        padding: 0 !important;
        border-radius: 0 !important;
    }

    .main .appointments-search-shell .appointments-search-input {
        width: 100%;
        min-height: 48px;
        padding: 12px 20px;
        height: 48px;
        border-radius: 0 0 14px 14px !important;
        -webkit-border-radius: 0 0 14px 14px !important;
        -moz-border-radius: 0 0 14px 14px !important;
        border: 0 !important;
        border-bottom: 3px solid #8f2230 !important;
        color: #111827;
        background: transparent !important;
        box-shadow: none !important;
        transition: border-color .18s ease, box-shadow .18s ease, transform .18s ease;
        appearance: none;
        -webkit-appearance: none;
    }

    .main .appointments-search-shell .appointments-search-input::placeholder {
        color: #7f1d2d;
        font-weight: 700;
    }

    .main .appointments-search-shell .appointments-search-input:focus {
        outline: none;
        border-bottom-color: #70131B;
        box-shadow: none !important;
        transform: translateY(-1px);
    }

    .appointments-search-toggle {
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

    .appointments-search-toggle svg {
        width: 28px !important;
        height: 28px !important;
        stroke-width: 2 !important;
        position: relative;
        z-index: 1;
        display: block;
    }

    .appointments-search-toggle:hover,
    .appointments-search-toggle:focus {
        background: #facc15 !important;
        color: #111827 !important;
        border-color: #facc15 !important;
        box-shadow:
            0 0 0 3px rgba(250, 204, 21, 0.18),
            0 14px 24px rgba(112, 19, 27, 0.16) !important;
        outline: none !important;
    }

    .appointments-search-toggle:hover svg,
    .appointments-search-toggle:focus svg {
        color: #111827 !important;
        stroke: currentColor !important;
    }

    .appointments-filter-toggle {
        min-height: 50px;
        min-width: 140px !important;
        padding: 0 16px !important;
        gap: 8px !important;
        width: auto !important;
        border-radius: 14px !important;
    }

    .appointments-filter-toggle svg {
        width: 18px !important;
        height: 18px !important;
        flex: 0 0 auto;
    }

    .appointments-filter-panel {
        position: absolute;
        right: 0;
        top: calc(100% + 10px);
        z-index: 40;
        width: min(290px, 92vw);
        padding: 14px;
        border-radius: 18px;
        border: 1px solid rgba(112, 19, 27, 0.12);
        background: rgba(255, 255, 255, 0.96);
        box-shadow: 0 24px 38px rgba(15, 23, 42, 0.12);
        display: none;
    }

    .appointments-filter-shell.is-open .appointments-filter-panel {
        display: block;
    }

    .appointments-filter-panel-title {
        margin: 0 0 10px;
        font-size: 12px;
        font-weight: 800;
        letter-spacing: 0.04em;
        text-transform: uppercase;
        color: #70131B;
    }

    .appointments-status-wrap {
        position: relative;
    }

    .appointments-status-select {
        position: absolute;
        width: 1px !important;
        height: 1px !important;
        opacity: 0;
        pointer-events: none;
        padding: 0 !important;
        border: 0 !important;
        margin: 0 !important;
    }

    .appointments-status-display {
        width: 100%;
        min-height: 46px;
        padding: 12px 50px 12px 16px;
        border-radius: 16px;
        border: 1px solid rgba(127, 29, 29, 0.22);
        background:
            radial-gradient(circle at top right, rgba(250, 204, 21, 0.10), transparent 36%),
            linear-gradient(180deg, #ffffff 0%, #fff8f6 100%);
        color: #111827;
        box-shadow:
            0 12px 22px rgba(15, 23, 42, 0.08),
            inset 0 1px 0 rgba(255,255,255,0.86);
        text-align: left;
        font-weight: 700;
        outline: none;
        cursor: pointer;
        transition: all .2s ease;
        position: relative;
    }

    .appointments-status-display:hover {
        border-color: rgba(139, 0, 0, 0.34);
        box-shadow:
            0 14px 24px rgba(15, 23, 42, 0.10),
            0 8px 18px rgba(139, 0, 0, 0.05),
            inset 0 1px 0 rgba(255,255,255,0.90);
        transform: translateY(-1px);
    }

    .appointments-status-display:focus,
    .appointments-status-display.is-open {
        border-color: #8B0000;
        box-shadow:
            0 0 0 4px rgba(139, 0, 0, 0.06),
            0 14px 24px rgba(139, 0, 0, 0.10),
            inset 0 1px 0 rgba(255,255,255,0.88);
    }

    .appointments-status-wrap::after {
        content: "";
        position: absolute;
        top: 23px;
        right: 18px;
        width: 10px;
        height: 10px;
        border-right: 2px solid #8B0000;
        border-bottom: 2px solid #8B0000;
        transform: translateY(-65%) rotate(45deg);
        pointer-events: none;
        transition: transform .18s ease;
    }

    .appointments-status-wrap::before {
        content: "";
        position: absolute;
        top: 23px;
        right: 42px;
        transform: translateY(-50%);
        width: 1px;
        height: 24px;
        background: rgba(148, 163, 184, 0.24);
        pointer-events: none;
    }

    .appointments-status-wrap.is-open::after {
        transform: translateY(-20%) rotate(225deg);
    }

    .appointments-status-menu {
        position: absolute;
        top: calc(100% + 10px);
        left: 0;
        right: 0;
        display: none;
        gap: 10px;
        padding: 14px;
        border-radius: 18px;
        border: 1px solid rgba(139, 0, 0, 0.12);
        background: rgba(255, 255, 255, 0.98);
        box-shadow: 0 18px 34px rgba(15, 23, 42, 0.14);
        z-index: 80;
        backdrop-filter: blur(8px);
        -webkit-backdrop-filter: blur(8px);
        max-height: 260px;
        overflow: hidden;
    }

    .appointments-status-wrap.is-open .appointments-status-menu {
        display: grid;
    }

    .appointments-status-options {
        display: grid;
        gap: 10px;
        max-height: 228px;
        overflow-y: auto;
        padding-right: 2px;
    }

    .appointments-status-option {
        width: 100%;
        border: 1px solid rgba(148, 163, 184, 0.22);
        background: linear-gradient(180deg, #ffffff 0%, #f8fafc 100%);
        color: #1e293b;
        border-radius: 999px;
        padding: 12px 14px;
        font-size: 13px;
        font-weight: 800;
        text-align: left;
        cursor: pointer;
        transition: all .18s ease;
        box-shadow:
            0 12px 22px rgba(15, 23, 42, 0.08),
            inset 0 1px 0 rgba(255,255,255,0.82);
    }

    .appointments-status-option:hover,
    .appointments-status-option.is-selected {
        transform: translateY(-1px);
        border-color: #8B0000;
        background: linear-gradient(135deg, #8B0000, #70131B);
        color: #facc15;
        box-shadow: 0 12px 20px rgba(139, 0, 0, 0.16);
    }

    .appointments-filter-actions {
        display: flex;
        gap: 8px;
        margin-top: 12px;
    }

    .appointments-filter-reset,
    .appointments-filter-close {
        flex: 1 1 0;
        min-height: 42px;
        border-radius: 12px;
        border: 1px solid rgba(112, 19, 27, 0.12);
        background: #f8fafc;
        color: #475569;
        font-weight: 800;
        cursor: pointer;
    }

    .appointments-filter-reset:hover,
    .appointments-filter-close:hover {
        background: #fff3f5;
        color: #70131B;
    }

    html[data-theme="dark"] .appointments-filter-panel {
        background: rgba(15, 23, 42, 0.96);
        border-color: rgba(250, 204, 21, 0.14);
        box-shadow: 0 24px 38px rgba(0, 0, 0, 0.28);
    }

    html[data-theme="dark"] .appointments-filter-panel-title {
        color: #facc15;
    }

    html[data-theme="dark"] .appointments-status-display {
        background: rgba(15, 23, 42, 0.92);
        color: #e5eefb;
        border-color: rgba(148, 163, 184, 0.22);
    }

    html[data-theme="dark"] .appointments-status-menu {
        background: rgba(15, 23, 42, 0.98);
        border-color: rgba(250, 204, 21, 0.14);
        box-shadow: 0 24px 38px rgba(0, 0, 0, 0.28);
    }

    html[data-theme="dark"] .appointments-status-option {
        background: rgba(30, 41, 59, 0.92);
        color: #e2e8f0;
        border-color: rgba(148, 163, 184, 0.18);
    }

    html[data-theme="dark"] .appointments-status-option:hover,
    html[data-theme="dark"] .appointments-status-option.is-selected {
        background: linear-gradient(135deg, #8B0000, #70131B);
        color: #facc15;
        border-color: #facc15;
    }

    html[data-theme="dark"] .appointments-filter-reset,
    html[data-theme="dark"] .appointments-filter-close {
        background: rgba(30, 41, 59, 0.92);
        color: #e2e8f0;
        border-color: rgba(148, 163, 184, 0.18);
    }

    .appointments-search-toggle:focus-visible {
        outline: none !important;
    }

    .btn-add-walkin {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        gap: 10px;
        position: relative;
        overflow: hidden;
        background: linear-gradient(135deg, #70131B, #8f2230);
        color: #ffffff !important; 
        padding: 11px 18px;
        border-radius: 999px;
        text-decoration: none;
        font-weight: 800;
        font-size: 14px;
        white-space: nowrap;
        box-shadow:
            0 0 0 3px rgba(112, 19, 27, 0.12),
            0 10px 22px rgba(112, 19, 27, 0.20);
        transition: color .08s linear, transform .18s ease, box-shadow .18s ease, border-color .18s ease;
        border: 1px solid #8f2230;
        z-index: 0;
    }

    .btn-add-walkin::after {
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

    .btn-icon {
        width: 28px;
        height: 28px;
        border-radius: 999px;
        background: #ffefb5;
        color: #70131B;
        font-size: 15px;
        line-height: 1;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        position: relative;
        z-index: 1;
        margin-right: 0;
    }

    .btn-text {
        position: relative;
        z-index: 1;
    }

    .btn-add-walkin:hover {
        transform: translateY(-2px);
        background: #facc15;
        color: #111827 !important;
        border-color: #facc15;
        box-shadow:
            0 0 0 3px rgba(250, 204, 21, 0.18),
            0 14px 24px rgba(112, 19, 27, 0.16);
        text-decoration: none;
    }

    .btn-add-walkin:hover .btn-icon {
        background: #111827;
        color: #facc15;
    }
    .btn-add-walkin:hover::after {
        transform: translateX(135%);
    }

    .appointment-highlight-row {
        position: relative;
        background: linear-gradient(180deg, rgba(255, 248, 208, 0.98), rgba(255, 243, 191, 0.98));
        box-shadow: inset 4px 0 0 #f59e0b;
        transition: background 0.3s ease, box-shadow 0.3s ease;
    }

    .appointment-highlight-row td {
        background: transparent;
    }

    .appointment-row-clickable {
        cursor: pointer;
    }

    .appointment-row-clickable td {
        transition: background 0.16s ease;
    }

    .appointment-row-clickable:hover td {
        background: rgba(219, 234, 254, 0.52);
    }

    html[data-theme="dark"] .appointment-highlight-row {
        background: linear-gradient(180deg, rgba(120, 53, 15, 0.34), rgba(146, 64, 14, 0.28));
        box-shadow: inset 4px 0 0 #fbbf24;
    }

    html[data-theme="dark"] .appointment-row-clickable:hover td {
        background: rgba(30, 64, 175, 0.28);
    }

    @keyframes appointmentHighlightPulse {
        0%, 100% {
            box-shadow: inset 4px 0 0 #f59e0b, 0 0 0 rgba(245, 158, 11, 0);
        }
        50% {
            box-shadow: inset 4px 0 0 #f59e0b, 0 0 0 6px rgba(245, 158, 11, 0.14);
        }
    }

    html[data-theme="dark"] .appointments-page-title {
        color: #ffffff;
        border-bottom-color: rgba(143, 34, 48, 0.70);
        background: transparent;
        box-shadow: none;
    }

    html[data-theme="dark"] .appointments-toolbar {
        border-bottom-color: rgba(250, 204, 21, 0.28);
        background: linear-gradient(135deg, rgba(112, 19, 27, 0.68) 0%, rgba(86, 16, 26, 0.64) 48%, rgba(44, 14, 18, 0.72) 100%);
        box-shadow:
            0 16px 28px rgba(0, 0, 0, 0.22);
    }

    html[data-theme="dark"] .appointments-summary-card::before {
        background: #facc15;
    }

    html[data-theme="dark"] .card {
        box-shadow:
            0 14px 28px rgba(0, 0, 0, 0.22),
            inset 0 1px 0 rgba(255,255,255,0.04);
    }

    html[data-theme="dark"] .appointments-summary-title,
    html[data-theme="dark"] .student-name,
    html[data-theme="dark"] #apptTable td,
    html[data-theme="dark"] #apptTable td div,
    html[data-theme="dark"] #apptTable td span:not(.status):not(.type-badge),
    html[data-theme="dark"] #apptTable td[style],
    html[data-theme="dark"] #apptTable td div[style] {
        color: #ffffff !important;
    }

    html[data-theme="dark"] .main .appointments-search-shell .appointments-search-input {
        background: transparent !important;
        color: #ffffff;
        border-bottom-color: rgba(143, 34, 48, 0.92) !important;
        box-shadow: none !important;
    }

    html[data-theme="dark"] .main .appointments-search-shell .appointments-search-input::placeholder {
        color: #fecdd3;
    }

    html[data-theme="dark"] .appointments-search-toggle {
        background: linear-gradient(135deg, #70131B, #8f2230) !important;
        border-color: rgba(250, 204, 21, 0.28) !important;
        box-shadow:
            0 0 0 3px rgba(112, 19, 27, 0.16),
            0 12px 22px rgba(0, 0, 0, 0.24) !important;
    }
    html[data-theme="dark"] .appointment-action-menu {
        background: rgba(17, 24, 39, 0.98);
        border-color: rgba(250, 204, 21, 0.12);
        box-shadow: 0 20px 34px rgba(0, 0, 0, 0.32);
    }
    html[data-theme="dark"] .appointment-action-menu-item,
    html[data-theme="dark"] .appointment-action-menu-state {
        color: #f8fafc;
    }
    html[data-theme="dark"] .appointment-action-menu-item.is-view {
        background: rgba(127, 29, 45, 0.26);
        border-color: rgba(250, 204, 21, 0.14);
    }
    html[data-theme="dark"] .appointment-action-menu-item.is-approve {
        background: rgba(20, 83, 45, 0.88);
        border-color: rgba(74, 222, 128, 0.22);
    }
    html[data-theme="dark"] .appointment-action-menu-item.is-reschedule {
        background: rgba(146, 64, 14, 0.86);
        border-color: rgba(250, 204, 21, 0.22);
    }
    html[data-theme="dark"] .appointment-action-menu-item.is-consult {
        background: rgba(30, 64, 175, 0.88);
        border-color: rgba(147, 197, 253, 0.24);
    }
    html[data-theme="dark"] .appointment-action-menu-item.is-missed {
        background: rgba(154, 52, 18, 0.88);
        border-color: rgba(253, 186, 116, 0.22);
    }
    html[data-theme="dark"] .appointment-action-menu-item.is-reject {
        background: rgba(127, 29, 29, 0.88);
        border-color: rgba(248, 113, 113, 0.22);
    }
    html[data-theme="dark"] .appointment-action-menu-item.is-view,
    html[data-theme="dark"] .appointment-action-menu-item.is-approve,
    html[data-theme="dark"] .appointment-action-menu-item.is-reschedule,
    html[data-theme="dark"] .appointment-action-menu-item.is-reject,
    html[data-theme="dark"] .appointment-action-menu-item.is-view span,
    html[data-theme="dark"] .appointment-action-menu-item.is-approve span,
    html[data-theme="dark"] .appointment-action-menu-item.is-reschedule span,
    html[data-theme="dark"] .appointment-action-menu-item.is-reject span {
        color: #ffffff !important;
    }
    html[data-theme="dark"] .appointment-action-menu-item.is-view svg,
    html[data-theme="dark"] .appointment-action-menu-item.is-approve svg,
    html[data-theme="dark"] .appointment-action-menu-item.is-reschedule svg,
    html[data-theme="dark"] .appointment-action-menu-item.is-reject svg,
    html[data-theme="dark"] .appointment-action-menu-item.is-view svg *,
    html[data-theme="dark"] .appointment-action-menu-item.is-approve svg *,
    html[data-theme="dark"] .appointment-action-menu-item.is-reschedule svg *,
    html[data-theme="dark"] .appointment-action-menu-item.is-reject svg * {
        color: #ffffff !important;
        stroke: #ffffff !important;
    }
    html[data-theme="dark"] .btn-view,
    html[data-theme="dark"] .btn-approve,
    html[data-theme="dark"] .btn-reschedule,
    html[data-theme="dark"] .btn-reject,
    html[data-theme="dark"] .btn-cancel,
    html[data-theme="dark"] .btn-view span,
    html[data-theme="dark"] .btn-approve span,
    html[data-theme="dark"] .btn-reschedule span,
    html[data-theme="dark"] .btn-reject span,
    html[data-theme="dark"] .btn-cancel span {
        color: #ffffff !important;
    }
    html[data-theme="dark"] .btn-view svg,
    html[data-theme="dark"] .btn-approve svg,
    html[data-theme="dark"] .btn-reschedule svg,
    html[data-theme="dark"] .btn-reject svg,
    html[data-theme="dark"] .btn-cancel svg,
    html[data-theme="dark"] .btn-view svg *,
    html[data-theme="dark"] .btn-approve svg *,
    html[data-theme="dark"] .btn-reschedule svg *,
    html[data-theme="dark"] .btn-reject svg *,
    html[data-theme="dark"] .btn-cancel svg * {
        color: #ffffff !important;
        stroke: #ffffff !important;
    }
    html[data-theme="dark"] .appointment-action-menu-state {
        background: rgba(71, 85, 105, 0.86);
        border-color: rgba(148, 163, 184, 0.22);
        color: #cbd5e1;
    }
    html[data-theme="dark"] .appointment-inline-pill.is-view {
        background: linear-gradient(135deg, rgba(112, 19, 27, 0.96), rgba(143, 34, 48, 0.92));
        border-color: rgba(244, 114, 182, 0.22);
        color: #ffffff !important;
        box-shadow:
            0 0 0 3px rgba(244, 114, 182, 0.10),
            0 12px 22px rgba(0, 0, 0, 0.24);
    }
    html[data-theme="dark"] .appointment-inline-pill.is-view,
    html[data-theme="dark"] .appointment-inline-pill.is-view span,
    html[data-theme="dark"] .appointment-inline-pill.is-view svg {
        color: #ffffff !important;
    }
    html[data-theme="dark"] .appointment-inline-pill.is-view svg,
    html[data-theme="dark"] .appointment-inline-pill.is-view svg * {
        stroke: #ffffff !important;
        color: #ffffff !important;
    }
    html[data-theme="dark"] .appointment-inline-pill.is-consult {
        background: linear-gradient(135deg, rgba(112, 19, 27, 0.94), rgba(143, 34, 48, 0.90));
        border-color: rgba(244, 114, 182, 0.24);
        color: #fff1f2;
        box-shadow:
            0 0 0 3px rgba(244, 114, 182, 0.10),
            0 12px 22px rgba(0, 0, 0, 0.24);
    }
    html[data-theme="dark"] .appointment-inline-pill.is-disabled {
        background: linear-gradient(135deg, rgba(71, 85, 105, 0.92), rgba(51, 65, 85, 0.92));
        border-color: rgba(148, 163, 184, 0.26);
        color: #e2e8f0;
        box-shadow:
            0 0 0 3px rgba(148, 163, 184, 0.10),
            0 12px 22px rgba(0, 0, 0, 0.22);
    }

    html[data-theme="dark"] .modal-box {
        background: rgba(15, 23, 42, 0.98);
        border-left: 1px solid rgba(143, 34, 48, 0.36);
        border-right: 1px solid rgba(143, 34, 48, 0.36);
        border-top: 4px solid #facc15;
        border-bottom: 4px solid #facc15;
        box-shadow:
            0 22px 38px rgba(0, 0, 0, 0.42),
            0 0 0 1px rgba(250, 204, 21, 0.06);
    }
    html[data-theme="dark"] .main #infoModal .modal-box,
    html[data-theme="dark"] .main #statusActionModal .modal-box,
    html[data-theme="dark"] .main #rescheduleModal .modal-box {
        background: rgba(15, 23, 42, 0.98) !important;
        border-left: 1px solid rgba(143, 34, 48, 0.36) !important;
        border-right: 1px solid rgba(143, 34, 48, 0.36) !important;
        border-top: 4px solid #facc15 !important;
        border-bottom: 4px solid #facc15 !important;
        border-radius: 18px !important;
    }

    html[data-theme="dark"] .modal-title,
    html[data-theme="dark"] .modal-label,
    html[data-theme="dark"] .modal-val,
    html[data-theme="dark"] .modal-subtitle {
        color: #ffffff !important;
    }

    html[data-theme="dark"] .modal-title {
        border-bottom-color: rgba(250, 204, 21, 0.82);
    }

    html[data-theme="dark"] .modal-header {
        border-bottom-color: rgba(255, 255, 255, 0.12);
    }
    html[data-theme="dark"] .modal-header .modal-subtitle {
        color: rgba(255, 255, 255, 0.76);
    }
    html[data-theme="dark"] .modal-header-close {
        background: linear-gradient(135deg, #70131B, #8f2230);
        border-color: rgba(250, 204, 21, 0.22);
        color: #ffffff;
    }
    html[data-theme="dark"] .modal-header-close:hover {
        background: linear-gradient(135deg, #70131B, #8f2230);
        border-color: rgba(250, 204, 21, 0.34);
    }

    html[data-theme="dark"] .modal-row {
        background: rgba(25, 25, 28, 0.96);
        border-color: rgba(250, 204, 21, 0.14);
        box-shadow:
            inset 0 1px 0 rgba(255, 255, 255, 0.04),
            0 8px 18px rgba(0, 0, 0, 0.18);
    }

    html[data-theme="dark"] .modal-notes {
        background: rgba(255, 255, 255, 0.06);
        color: #ffffff;
        border: 1px solid rgba(250, 204, 21, 0.12);
    }

    html[data-theme="dark"] .modal-status-badge.pending { background: rgba(194, 65, 12, 0.22); color: #fdba74; border-color: rgba(251, 146, 60, 0.30); }
    html[data-theme="dark"] .modal-status-badge.approved { background: rgba(21, 128, 61, 0.24); color: #bbf7d0; border-color: rgba(74, 222, 128, 0.28); }
    html[data-theme="dark"] .modal-status-badge.completed { background: rgba(29, 78, 216, 0.24); color: #bfdbfe; border-color: rgba(96, 165, 250, 0.30); }
    html[data-theme="dark"] .modal-status-badge.cancelled { background: rgba(127, 29, 29, 0.24); color: #fecaca; border-color: rgba(248, 113, 113, 0.28); }
    html[data-theme="dark"] .modal-status-badge.expired { background: rgba(71, 85, 105, 0.26); color: #e5e7eb; border-color: rgba(148, 163, 184, 0.30); }
    html[data-theme="dark"] .modal-status-badge.missed { background: rgba(154, 52, 18, 0.24); color: #fdba74; border-color: rgba(251, 146, 60, 0.28); }
    html[data-theme="dark"] .modal-status-badge.action-approve { background: rgba(21, 128, 61, 0.24); color: #bbf7d0; border-color: rgba(74, 222, 128, 0.28); }
    html[data-theme="dark"] .modal-status-badge.action-reject { background: rgba(127, 29, 29, 0.24); color: #fecaca; border-color: rgba(248, 113, 113, 0.28); }
    html[data-theme="dark"] .modal-status-badge.action-missed { background: rgba(154, 52, 18, 0.24); color: #fdba74; border-color: rgba(251, 146, 60, 0.28); }
    html[data-theme="dark"] .modal-status-badge.action-reschedule { background: rgba(146, 64, 14, 0.24); color: #fde68a; border-color: rgba(250, 204, 21, 0.3); }

    html[data-theme="dark"] .form-input {
        background: rgba(255, 255, 255, 0.06);
        color: #ffffff;
        border-color: rgba(250, 204, 21, 0.18);
    }
    html[data-theme="dark"] .form-input:focus {
        border-color: rgba(250, 204, 21, 0.36);
        box-shadow:
            0 0 0 3px rgba(250, 204, 21, 0.10),
            inset 0 1px 0 rgba(255,255,255,0.04);
    }

    html[data-theme="dark"] .form-input::placeholder {
        color: rgba(255, 255, 255, 0.62);
    }

    @media (max-width: 920px) {
        .appointments-toolbar {
            flex-direction: column;
            align-items: stretch;
            border-radius: 0 0 18px 18px;
        }

        .appointments-toolbar-actions {
            width: 100%;
            justify-content: stretch;
            margin-left: 0;
        }

        .appointments-search-shell {
            width: 100%;
        }

        .appointments-search-wrap,
        .appointments-search-shell.is-open .appointments-search-wrap {
            width: 100%;
            flex: 1 1 100%;
        }

        .appointments-search-shell:not(.is-open) .appointments-search-wrap {
            width: 0;
            flex-basis: 0;
        }

        .btn-add-walkin {
            width: 100%;
        }

        .modal-box {
            width: min(92vw, 560px);
        }

        .modal-header {
            flex-direction: column;
            align-items: stretch;
        }

        .modal-row {
            grid-template-columns: 1fr;
            gap: 6px;
        }

        #infoModal .appointment-detail-modal {
            width: min(96vw, 760px);
        }

        #infoModal .appointment-detail-header {
            align-items: stretch;
            flex-direction: column;
            padding: 22px;
        }

        #infoModal .appointment-detail-status {
            min-width: 0;
            width: 100%;
        }

        .appointment-detail-body {
            padding: 18px;
        }

        .appointment-detail-grid,
        .appointment-detail-footer {
            grid-template-columns: 1fr;
        }

        .appointment-student-layout {
            grid-template-columns: 1fr;
            justify-items: center;
            text-align: center;
        }

        .appointment-student-info p {
            justify-content: center;
        }

        .appointment-info-list > div {
            grid-template-columns: 1fr;
            gap: 6px;
        }

        .appointment-timeline {
            grid-template-columns: 1fr;
            gap: 16px;
            padding: 8px 0 0;
        }

        .timeline-line {
            display: none;
        }

        /* MOBILE: Filter Modal */
        .appointments-filter-panel {
            position: fixed !important;
            right: 16px !important;
            left: 16px !important;
            top: auto !important;
            bottom: auto !important;
            width: auto !important;
            max-height: 70vh !important;
            overflow-y: auto !important;
        }

        .appointments-filter-shell.is-open .appointments-filter-panel {
            display: block !important;
        }

        /* MOBILE: Appointment Summary Table - Horizontal Scroll */
        .card table,
        .appointments-table,
        table {
            display: block !important;
            overflow-x: auto !important;
            -webkit-overflow-scrolling: touch;
            width: 100% !important;
            max-width: 100% !important;
        }

        .card table,
        .appointments-table {
            min-width: 1200px !important;
        }

        .card table thead,
        .card table tbody,
        .card table tr {
            display: table !important;
            width: 100% !important;
        }

        /* MOBILE: Modal Responsive */
        .modal-box {
            max-height: 90vh !important;
            width: 95vw !important;
            max-width: 95vw !important;
            overflow-y: auto !important;
        }

        .modal-content {
            max-height: 80vh !important;
            overflow-y: auto !important;
        }

        .modal-form-grid {
            grid-template-columns: 1fr !important;
        }

        .modal-actions {
            flex-wrap: wrap !important;
            gap: 8px !important;
        }

        .modal-actions button {
            flex: 1 1 45% !important;
            min-width: 100px !important;
        }
    }

    /* DARK MODE FIXES */
    html[data-theme="dark"] .appointments-filter-panel {
        background: rgba(35, 17, 25, 0.96) !important;
        border-color: rgba(255, 255, 255, 0.12) !important;
    }

    html[data-theme="dark"] .appointments-filter-panel-title {
        color: #f3d6da !important;
    }

    html[data-theme="dark"] .appointments-status-display {
        background: rgba(18, 18, 18, 0.55) !important;
        color: #f8fafc !important;
        border-color: rgba(255, 255, 255, 0.08) !important;
    }

    html[data-theme="dark"] .modal-box {
        background: rgba(35, 17, 25, 0.96) !important;
    }

    html[data-theme="dark"] .modal-header {
        border-color: rgba(255, 255, 255, 0.12) !important;
    }

    html[data-theme="dark"] .modal-form-label {
        color: #f8fafc !important;
    }

    html[data-theme="dark"] .modal-form-input,
    html[data-theme="dark"] .modal-form-select {
        background: rgba(18, 18, 18, 0.55) !important;
        color: #f8fafc !important;
        border-color: rgba(255, 255, 255, 0.08) !important;
    }

    html[data-theme="dark"] .appointments-table {
        background: rgba(18, 18, 18, 0.4) !important;
        color: #f8fafc !important;
    }

    html[data-theme="dark"] .appointments-table th {
        background: rgba(18, 18, 18, 0.55) !important;
        color: #f8fafc !important;
        border-color: rgba(255, 255, 255, 0.08) !important;
    }

    html[data-theme="dark"] .appointments-table td {
        border-color: rgba(255, 255, 255, 0.08) !important;
        color: #f8fafc !important;
    }

    @keyframes fadeIn { from { opacity: 0; } to { opacity: 1; } }
</style>
@endpush

@section('content')
    @php
        $role = \App\Models\User::normalizeRole(optional(auth()->user())->user_role ?? '');
        $basePrefix = $role === \App\Models\User::ROLE_ADMIN ? '/assistant' : '/admin';
        $highlightAppointmentId = trim((string) request()->query('highlight_appointment', ''));
    @endphp

    <div class="appointments-toolbar">
        <h2 class="appointments-page-title"><x-outline-icon name="calendar-days" />Appointments</h2>
        <div class="appointments-toolbar-actions">
            <div class="appointments-search-shell" id="appointmentsSearchShell">
                <div class="appointments-search-wrap">
                    <input type="text" id="searchInput" class="appointments-search-input" placeholder="Search by name...">
                </div>
                <button type="button" class="btn-add-walkin appointments-search-toggle" id="appointmentsSearchToggle" aria-label="Open search" aria-expanded="false" aria-controls="searchInput">
                    <x-outline-icon name="magnifying-glass" />
                </button>
            </div>
            <div class="appointments-filter-shell" id="appointmentsFilterShell">
                <button type="button" class="btn-add-walkin appointments-filter-toggle" id="appointmentsFilterToggle" aria-label="Open status filter" aria-expanded="false" aria-controls="appointmentsFilterPanel">
                    <x-outline-icon name="funnel" />
                    <span class="btn-text">Filter</span>
                </button>
                <div class="appointments-filter-panel" id="appointmentsFilterPanel" aria-hidden="true">
                    <div class="appointments-filter-panel-title">Status Filter</div>
                    <div class="appointments-status-wrap" id="appointmentsStatusWrap">
                        <select id="appointmentStatusFilter" class="appointments-status-select" aria-hidden="true" tabindex="-1">
                            <option value="">All Statuses</option>
                            <option value="Pending">Pending</option>
                            <option value="Approved">Approved</option>
                            <option value="Completed">Completed</option>
                            <option value="Cancelled">Cancelled</option>
                            <option value="Expired">Expired</option>
                            <option value="Missed">Missed</option>
                        </select>
                        <button type="button" class="appointments-status-display" id="appointmentsStatusDisplay" aria-haspopup="listbox" aria-expanded="false">
                            All Statuses
                        </button>
                        <div class="appointments-status-menu" id="appointmentsStatusMenu" role="listbox" aria-label="Appointment status options">
                            <div class="appointments-status-options">
                                <button type="button" class="appointments-status-option is-selected" data-status-value="">All Statuses</button>
                                <button type="button" class="appointments-status-option" data-status-value="Pending">Pending</button>
                                <button type="button" class="appointments-status-option" data-status-value="Approved">Approved</button>
                                <button type="button" class="appointments-status-option" data-status-value="Completed">Completed</button>
                                <button type="button" class="appointments-status-option" data-status-value="Cancelled">Cancelled</button>
                                <button type="button" class="appointments-status-option" data-status-value="Expired">Expired</button>
                                <button type="button" class="appointments-status-option" data-status-value="Missed">Missed</button>
                            </div>
                        </div>
                    </div>
                    <div class="appointments-filter-actions">
                        <button type="button" class="appointments-filter-reset" id="appointmentsFilterReset">Reset</button>
                        <button type="button" class="appointments-filter-close" id="appointmentsFilterClose">Close</button>
                    </div>
                </div>
            </div>
        </div>
    </div>
<div class="card appointments-summary-card">
        <div class="appointments-summary-title">Appointments Summary</div>
        <table id="apptTable">
            <thead>
                <tr>
                    <th>Full Name</th>
                    <th>ID Number</th>
                    <th>Appointment Type</th> <th>Service</th>
                    <th>Date & Time</th>
                    <th>Status</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse($appointments as $appt)
                    @php
                        $appointmentPhotoPath = optional(optional($appt->user)->healthProfile)->student_photo;
                        $currentType = strtolower(trim((string) ($appt->type ?? '')));
                        if ($currentType === '') {
                            $legacyType = strtolower(trim((string) ($appt->user_type ?? '')));
                            if (in_array($legacyType, ['walkin', 'walk-in', 'online'], true)) {
                                $currentType = str_replace('-', '', $legacyType);
                            }
                        }
                    @endphp
                    <tr
                        data-appointment-row
                        data-appointment-id="{{ $appt->id }}"
                        data-view-apt-id="{{ $appt->apt_id ?: '' }}"
                        data-view-name="{{ $appt->name }}"
                        data-view-service="{{ $appt->service }}"
                        data-view-date="{{ $appt->date }}"
                        data-view-time="{{ $appt->time }}"
                        data-view-remarks="{{ $appt->remarks ?? 'No notes provided.' }}"
                        data-view-email="{{ $appt->email }}"
                        data-view-status="{{ $appt->status }}"
                        data-view-type="{{ $currentType === 'walkin' ? 'Walk-in' : 'Online' }}"
                        data-view-student-number="{{ $appt->student_number ?: optional(optional($appt->user)->healthProfile)->student_number ?: optional($appt->user)->student_number ?: '' }}"
                        data-view-contact="{{ optional($appt->user)->contact_no ?: optional(optional($appt->user)->healthProfile)->cellphone ?: '' }}"
                        data-view-program="{{ trim(implode(' ', array_filter([optional($appt->user)->course ?: optional(optional($appt->user)->healthProfile)->course_college, trim(implode('-', array_filter([optional($appt->user)->year, optional($appt->user)->section]))) ]))) }}"
                        data-view-photo-url="{{ $appointmentPhotoPath ? asset('storage/' . $appointmentPhotoPath) : '' }}"
                        data-view-created="{{ optional($appt->created_at)->format('M d, Y g:i A') }}"
                        data-view-updated="{{ optional($appt->updated_at)->format('M d, Y g:i A') }}"
                        title="{{ $appt->status === 'Completed' ? 'Click to view' : '' }}"
                        class="{{ implode(' ', array_filter([
                            $highlightAppointmentId !== '' && $highlightAppointmentId === (string) $appt->id ? 'appointment-highlight-row' : '',
                            $appt->status === 'Completed' ? 'appointment-row-clickable' : '',
                        ])) }}"
                    >
                        <td>
                            <div style="font-weight: 700;" class="student-name">{{ $appt->name }}</div>
                            <div style="font-size: 12px; color: #111827;">{{ $appt->student_number ?: optional(optional($appt->user)->healthProfile)->student_number ?: optional($appt->user)->student_number ?: 'N/A' }}</div>
                        </td>
                        <td>{{ $appt->student_number ?: optional(optional($appt->user)->healthProfile)->student_number ?: optional($appt->user)->student_number ?: 'N/A' }}</td>
                       <td>
    @if($currentType === 'walkin')
        <span class="type-badge type-walkin">Walk-in</span>
    @else
        <span class="type-badge type-online">Online</span>
    @endif
</td>
                        <td>{{ $appt->service }}</td>
                        <td>
                            <div>{{ \Carbon\Carbon::parse($appt->date)->format('M d, Y') }}</div>
                            <div style="font-size: 12px; color: #94a3b8;">{{ \Carbon\Carbon::parse($appt->time)->format('g:i A') }}</div>
                        </td>
                        <td>
                            <span class="status {{ strtolower($appt->status) }}">{{ $appt->status }}</span>
                        </td>
                        <td>
                            @php
                                $scheduledAt = $appt->status === 'Approved'
                                    ? \Carbon\Carbon::parse($appt->date . ' ' . $appt->time)
                                    : null;
                                $consultEligibleAt = $scheduledAt?->copy()->subMinutes(10);
                                $now = \Carbon\Carbon::now();
                                $consultLocked = $consultEligibleAt ? $now->lt($consultEligibleAt) : false;
                            @endphp

                            @if($appt->status === 'Approved')
                                @if($consultLocked)
                                    <span class="appointment-inline-pill is-disabled" title="Consult becomes available 10 minutes before the scheduled time on {{ \Carbon\Carbon::parse($appt->date)->format('M d, Y') }}">
                                        <x-outline-icon name="clipboard-document-list" />
                                        Consult
                                    </span>
                                @else
                                    <a href="{{ url($basePrefix . '/walkin/form/' . $appt->student_id) }}?source=online" class="appointment-inline-pill is-consult" title="Open consult form">
                                        <x-outline-icon name="clipboard-document-list" />
                                        Consult
                                    </a>
                                @endif
                            @elseif(in_array($appt->status, ['Completed', 'Cancelled', 'Expired', 'Missed'], true))
                                <button
                                    type="button"
                                    class="appointment-inline-pill is-view"
                                    title="Click to view"
                                    data-name="{{ $appt->name }}"
                                    data-service="{{ $appt->service }}"
                                    data-date="{{ $appt->date }}"
                                    data-time="{{ $appt->time }}"
                                    data-remarks="{{ $appt->remarks ?? 'No notes provided.' }}"
                                    data-email="{{ $appt->email }}"
                                    data-status="{{ $appt->status }}"
                                    data-appointment-id="{{ $appt->id }}"
                                    onclick="openInfoModal(this)">
                                    <x-outline-icon name="eye" />
                                    View
                                </button>
                            @else
                                <div class="appointment-action-menu-wrap" data-appointment-action-menu>
                                    <button type="button" class="appointment-action-menu-toggle" aria-expanded="false">
                                        <x-outline-icon name="bars-3" />
                                        Actions
                                    </button>
                                    <div class="appointment-action-menu">
                                        <button
                                            type="button"
                                            class="appointment-action-menu-item is-view"
                                            title="View Details"
                                            data-name="{{ $appt->name }}"
                                            data-service="{{ $appt->service }}"
                                            data-date="{{ $appt->date }}"
                                            data-time="{{ $appt->time }}"
                                            data-remarks="{{ $appt->remarks ?? 'No notes provided.' }}"
                                            data-email="{{ $appt->email }}"
                                            data-status="{{ $appt->status }}"
                                            data-appointment-id="{{ $appt->id }}"
                                            onclick="openInfoModal(this)">
                                            <x-outline-icon name="document-text" />
                                            View
                                        </button>

                                        @if($appt->status == 'Pending')
                                            <a href="{{ url($basePrefix . '/appointments/' . $appt->id . '/Approved') }}" class="appointment-action-menu-item is-approve btn-approve" title="Approve" data-id="{{ $appt->id }}" data-name="{{ $appt->name }}" data-service="{{ $appt->service }}" data-date="{{ $appt->date }}" data-time="{{ $appt->time }}" data-status-target="Approved">
                                                <x-outline-icon name="check" />
                                                Approve
                                            </a>
                                            <button type="button" class="appointment-action-menu-item is-reschedule btn-reschedule" title="Reschedule" data-id="{{ $appt->id }}" data-date="{{ $appt->date }}" data-time="{{ $appt->time }}" data-name="{{ $appt->name }}" data-service="{{ $appt->service }}">
                                                <x-outline-icon name="calendar-days" />
                                                Reschedule
                                            </button>
                                            <a href="{{ url($basePrefix . '/appointments/' . $appt->id . '/Cancelled') }}" class="appointment-action-menu-item is-reject btn-reject btn-cancel" title="Reject" data-id="{{ $appt->id }}" data-name="{{ $appt->name }}" data-service="{{ $appt->service }}" data-date="{{ $appt->date }}" data-time="{{ $appt->time }}" data-status-target="Cancelled">
                                                <x-outline-icon name="x-mark" />
                                                Reject
                                            </a>
                                        @endif
                                    </div>
                                </div>
                            @endif
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="7" style="text-align: center; padding: 40px; color: #94a3b8;">No appointments found.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div id="infoModal" class="modal-overlay">
        <div class="modal-box appointment-detail-modal">
            <div class="modal-header appointment-detail-header">
                <div class="modal-header-main appointment-detail-header-main">
                    <h3 class="modal-title">Appointment Details</h3>
                    <p class="appointment-detail-id-line">
                        <span>Appointment ID:</span>
                        <strong id="mAppointmentId">N/A</strong>
                        <button type="button" class="appointment-detail-copy" data-copy-target="mAppointmentId" aria-label="Copy appointment ID">
                            <x-outline-icon name="clipboard-document-list" />
                        </button>
                    </p>
                </div>
                <span class="modal-status-badge appointment-detail-status" id="mStatus">
                    <span class="appointment-status-dot"></span>
                    <span id="mStatusText">N/A</span>
                    <small id="mStatusHint">Appointment status</small>
                </span>
                <button type="button" class="modal-header-close" onclick="closeInfoModal()" aria-label="Close appointment details modal">
                    <x-outline-icon name="x-mark" />
                </button>
            </div>
            <div class="appointment-detail-body">
                <div class="appointment-detail-grid">
                    <section class="appointment-detail-card">
                        <h4><x-outline-icon name="users" /> Student Information</h4>
                        <div class="appointment-student-layout">
                            <div class="appointment-avatar" id="mAvatar">NA</div>
                            <div class="appointment-student-info">
                                <strong id="mName">N/A</strong>
                                <p>
                                    <x-outline-icon name="envelope" />
                                    <span id="mEmail">N/A</span>
                                    <button type="button" class="appointment-detail-copy" data-copy-target="mEmail" aria-label="Copy email">
                                        <x-outline-icon name="clipboard-document-list" />
                                    </button>
                                </p>
                                <p><x-outline-icon name="phone" /> <span id="mContact">N/A</span></p>
                                <p><x-outline-icon name="information-circle" /> <span id="mStudentId">ID Number: N/A</span></p>
                                <p><x-outline-icon name="academic-cap" /> <span id="mProgram">Program: N/A</span></p>
                            </div>
                        </div>
                    </section>

                    <section class="appointment-detail-card">
                        <h4><x-outline-icon name="calendar-days" /> Appointment Information</h4>
                        <div class="appointment-info-list">
                            <div>
                                <span><x-outline-icon name="clipboard-document-list" /> Service Request</span>
                                <strong class="appointment-service-pill" id="mService">N/A</strong>
                            </div>
                            <div>
                                <span><x-outline-icon name="calendar-days" /> Date</span>
                                <strong id="mDate">N/A</strong>
                            </div>
                            <div>
                                <span><x-outline-icon name="clock" /> Time</span>
                                <strong id="mTime">N/A</strong>
                            </div>
                            <div>
                                <span><x-outline-icon name="check" /> Status</span>
                                <strong class="appointment-inline-status"><span class="appointment-status-dot"></span><span id="mStatusInline">N/A</span></strong>
                            </div>
                            <div>
                                <span><x-outline-icon name="document-text" /> Created</span>
                                <strong id="mCreated">N/A</strong>
                            </div>
                            <div>
                                <span><x-outline-icon name="clock" /> Last Updated</span>
                                <strong id="mUpdated">N/A</strong>
                            </div>
                        </div>
                    </section>
                </div>

                <section class="appointment-notes-panel">
                    <div class="appointment-notes-title">
                        <span><x-outline-icon name="document-text" /> Notes</span>
                        <button type="button" class="appointment-detail-copy" data-copy-target="mNotes" aria-label="Copy notes">
                            <x-outline-icon name="clipboard-document-list" />
                        </button>
                    </div>
                    <p id="mNotes">N/A</p>
                </section>

                <section class="appointment-timeline-panel">
                    <h4><x-outline-icon name="clock" /> Appointment Timeline</h4>
                    <div class="appointment-timeline" id="mTimeline">
                        <div class="timeline-step" data-timeline-step="submitted">
                            <span class="timeline-dot"><x-outline-icon name="check" /></span>
                            <strong>Submitted</strong>
                            <small id="mTimelineSubmitted">N/A</small>
                        </div>
                        <div class="timeline-line" data-timeline-line="submitted"></div>
                        <div class="timeline-step" data-timeline-step="pending">
                            <span class="timeline-dot"></span>
                            <strong>Pending Review</strong>
                            <small id="mTimelinePending">N/A</small>
                        </div>
                        <div class="timeline-line" data-timeline-line="pending"></div>
                        <div class="timeline-step" data-timeline-step="approved">
                            <span class="timeline-dot"></span>
                            <strong id="mTimelineApprovedLabel">Approved</strong>
                            <small id="mTimelineApproved">N/A</small>
                        </div>
                        <div class="timeline-line" data-timeline-line="approved"></div>
                        <div class="timeline-step" data-timeline-step="scheduled">
                            <span class="timeline-dot"></span>
                            <strong>Scheduled</strong>
                            <small id="mTimelineScheduled">N/A</small>
                        </div>
                        <div class="timeline-line" data-timeline-line="scheduled"></div>
                        <div class="timeline-step" data-timeline-step="completed">
                            <span class="timeline-dot"><x-outline-icon name="check" /></span>
                            <strong>Completed</strong>
                            <small id="mTimelineCompleted">N/A</small>
                        </div>
                    </div>
                </section>

                <div class="appointment-detail-footer">
                    <span><x-outline-icon name="calendar-days" /> Date Created: <strong id="mFooterCreated">N/A</strong></span>
                    <span><x-outline-icon name="document-text" /> Last Updated: <strong id="mFooterUpdated">N/A</strong></span>
                </div>
            </div>
        </div>
    </div>

    <div id="statusActionModal" class="modal-overlay">
        <div class="modal-box appointment-detail-modal status-action-detail-modal">
            <div class="modal-header appointment-detail-header">
                <div class="modal-header-main appointment-detail-header-main">
                    <h3 id="statusActionTitle" class="modal-title">Appointment Action</h3>
                    <p class="appointment-detail-id-line">
                        <span>Appointment ID:</span>
                        <strong id="sAppointmentId">N/A</strong>
                    </p>
                </div>
                <span class="modal-status-badge appointment-detail-status" id="statusActionBadge">
                    <span class="appointment-status-dot"></span>
                    <span id="sStatusText">Pending</span>
                    <small id="statusActionSubtitle" hidden></small>
                </span>
                <button type="button" class="modal-header-close" onclick="closeStatusActionModal()" aria-label="Close action modal">
                    <x-outline-icon name="x-mark" />
                </button>
            </div>
            <div class="appointment-detail-body">
                <div class="appointment-detail-grid">
                    <section class="appointment-detail-card">
                        <h4><x-outline-icon name="users" /> Student Information</h4>
                        <div class="appointment-student-layout">
                            <div class="appointment-avatar" id="sAvatar">NA</div>
                            <div class="appointment-student-info">
                                <strong id="sName">N/A</strong>
                                <p><x-outline-icon name="envelope" /> <span id="sEmail">N/A</span></p>
                                <p><x-outline-icon name="phone" /> <span id="sContact">N/A</span></p>
                                <p><x-outline-icon name="information-circle" /> <span id="sStudentId">ID Number: N/A</span></p>
                                <p><x-outline-icon name="academic-cap" /> <span id="sProgram">Program: N/A</span></p>
                            </div>
                        </div>
                    </section>

                    <section class="appointment-detail-card">
                        <h4><x-outline-icon name="calendar-days" /> Appointment Information</h4>
                        <div class="appointment-info-list">
                            <div>
                                <span><x-outline-icon name="clipboard-document-list" /> Service Request</span>
                                <strong class="appointment-service-pill" id="sService">N/A</strong>
                            </div>
                            <div>
                                <span><x-outline-icon name="calendar-days" /> Date</span>
                                <strong id="sDate">N/A</strong>
                            </div>
                            <div>
                                <span><x-outline-icon name="clock" /> Time</span>
                                <strong id="sTime">N/A</strong>
                            </div>
                            <div>
                                <span><x-outline-icon name="document-text" /> Created</span>
                                <strong id="sCreated">N/A</strong>
                            </div>
                            <div>
                                <span><x-outline-icon name="check" /> Consultation Type</span>
                                <strong><span class="appointment-type-badge" id="sConsultationType">N/A</span></strong>
                            </div>
                            <div>
                                <span><x-outline-icon name="document-text" /> Notes</span>
                                <strong id="sNotes">N/A</strong>
                            </div>
                        </div>
                    </section>
                </div>

                <section class="approval-details-panel">
                    <h4><x-outline-icon name="check" /> Approval Details</h4>
                    <div class="approval-detail-grid">
                        <div class="approval-detail-card">
                            <span>Consultation Type</span>
                            <strong><span class="appointment-type-badge" id="sApprovalConsultationType">N/A</span></strong>
                            <div class="approval-message-field">
                                <label for="sApprovalMessage">Message (Optional)</label>
                                <textarea id="sApprovalMessage" placeholder="Your appointment has been approved. Please come to the clinic on your scheduled date and time."></textarea>
                            </div>
                        </div>
                        <div class="approval-detail-card">
                            <span>Reminder to Student</span>
                            <ul class="approval-reminder-list">
                                <li>
                                    <input type="checkbox" id="sReminderEarly" checked>
                                    <label for="sReminderEarly">Arrive 15 minutes early</label>
                                </li>
                                <li>
                                    <input type="checkbox" id="sReminderId" checked>
                                    <label for="sReminderId">Bring valid school ID</label>
                                </li>
                                <li>
                                    <input type="checkbox" id="sReminderMask" checked>
                                    <label for="sReminderMask">Wear face mask</label>
                                </li>
                                <li>
                                    <input type="checkbox" id="sReminderGuidelines" checked>
                                    <label for="sReminderGuidelines">Follow clinic guidelines</label>
                                </li>
                            </ul>
                        </div>
                    </div>
                </section>

                <div class="approval-timeline-row">
                    <section class="appointment-timeline-panel">
                        <h4><x-outline-icon name="clock" /> Appointment Timeline</h4>
                        <div class="appointment-timeline" id="sApprovalTimeline">
                            <div class="timeline-step is-done">
                                <span class="timeline-dot"><x-outline-icon name="check" /></span>
                                <strong>Submitted</strong>
                                <small id="sTimelineSubmitted">N/A</small>
                            </div>
                            <div class="timeline-line is-done"></div>
                            <div class="timeline-step is-done">
                                <span class="timeline-dot"><x-outline-icon name="check" /></span>
                                <strong>Pending Review</strong>
                                <small id="sTimelinePending">N/A</small>
                            </div>
                            <div class="timeline-line is-done"></div>
                            <div class="timeline-step is-current">
                                <span class="timeline-dot"></span>
                                <strong>Approved</strong>
                                <small>Current Step</small>
                            </div>
                            <div class="timeline-line"></div>
                            <div class="timeline-step is-muted">
                                <span class="timeline-dot"></span>
                                <strong>Scheduled</strong>
                                <small id="sTimelineScheduled">N/A</small>
                            </div>
                            <div class="timeline-line"></div>
                            <div class="timeline-step is-muted">
                                <span class="timeline-dot"></span>
                                <strong>Completed</strong>
                                <small>Upcoming</small>
                            </div>
                        </div>
                    </section>

                    <aside class="approval-notice-card">
                        <h4><x-outline-icon name="information-circle" /> After Approval</h4>
                        <p>The student will be notified via email that their appointment has been approved.</p>
                    </aside>
                </div>

                <div class="dialog-actions">
                    <button type="button" class="dialog-btn dialog-btn-secondary" onclick="closeStatusActionModal()">Cancel</button>
                    <a id="statusActionConfirm" href="#" class="dialog-btn dialog-btn-confirm">
                        <x-outline-icon name="check" />
                        <span>Confirm</span>
                    </a>
                </div>
            </div>
        </div>
    </div>

    <div id="rescheduleModal" class="modal-overlay">
        <div class="modal-box">
            <form id="rescheduleForm" method="POST" action="">
                @csrf
                <div class="modal-header">
                    <div class="modal-header-main">
                        <h3 class="modal-title">Reschedule Appointment</h3>
                        <p class="modal-subtitle">Select a new date and time for this appointment.</p>
                    </div>
                    <span class="modal-status-badge action-reschedule">Reschedule</span>
                    <button type="button" class="modal-header-close" onclick="closeRescheduleModal()" aria-label="Close reschedule modal">
                        <x-outline-icon name="x-mark" />
                    </button>
                </div>
                <div class="modal-row"><div class="modal-label">Student Name</div><div class="modal-val" id="rName"></div></div>
                <div class="modal-row"><div class="modal-label">Service Request</div><div class="modal-val" id="rService"></div></div>
                <div class="modal-row"><div class="modal-label">Current Schedule</div><div class="modal-val" id="rCurrentSchedule"></div></div>
                <div class="modal-row is-form"><label class="modal-label">New Date</label><input type="date" name="date" id="rDate" class="form-input" required></div>
                <div class="modal-row is-form"><label class="modal-label">New Time</label><input type="time" name="time" id="rTime" class="form-input" required></div>
                <div class="dialog-actions">
                    <button type="submit" class="dialog-btn dialog-btn-confirm">
                        <x-outline-icon name="check" />
                        <span>Confirm New Schedule</span>
                    </button>
                </div>
            </form>
        </div>
    </div>

@endsection

@push('scripts')
<script>
    const appointmentsBaseUrl = @json(url($basePrefix . '/appointments'));
    const highlightedAppointmentId = @json($highlightAppointmentId);

    function safeText(value) {
        return (value ?? '').toString().trim() || '-';
    }

    function formatSchedule(date, time) {
        const rawDate = (date || '').toString().trim();
        const rawTime = (time || '').toString().trim();

        if (!rawDate && !rawTime) {
            return '-';
        }

        const normalizedTime = rawTime && rawTime.length === 5 ? rawTime + ':00' : rawTime;
        const parsed = rawDate ? new Date(rawDate + 'T' + (normalizedTime || '00:00:00')) : null;

        if (parsed && !Number.isNaN(parsed.getTime())) {
            const datePart = parsed.toLocaleDateString(undefined, { month: 'short', day: '2-digit', year: 'numeric' });
            const timePart = parsed.toLocaleTimeString([], { hour: 'numeric', minute: '2-digit' });
            return datePart + ' at ' + timePart;
        }

        return rawTime ? rawDate + ' at ' + rawTime : rawDate;
    }

    function formatDateLong(date) {
        const rawDate = (date || '').toString().trim();
        if (!rawDate) return '-';

        const parsed = new Date(rawDate + 'T00:00:00');
        if (!Number.isNaN(parsed.getTime())) {
            return parsed.toLocaleDateString(undefined, {
                month: 'long',
                day: 'numeric',
                year: 'numeric',
                weekday: 'long'
            });
        }

        return rawDate;
    }

    function formatTime(time) {
        const rawTime = (time || '').toString().trim();
        if (!rawTime) return '-';

        const normalizedTime = rawTime.length === 5 ? rawTime + ':00' : rawTime;
        const parsed = new Date('1970-01-01T' + normalizedTime);
        if (!Number.isNaN(parsed.getTime())) {
            return parsed.toLocaleTimeString([], { hour: 'numeric', minute: '2-digit' });
        }

        return rawTime;
    }

    function getRowDataFromElement(element) {
        const row = element ? element.closest('tr') : null;
        if (!row) {
            return { name: '', service: '', date: '', time: '' };
        }

        const name = row.querySelector('.student-name')?.textContent?.trim() || '';
        const service = row.cells?.[3]?.textContent?.trim() || '';
        const dateNode = row.cells?.[4]?.querySelector('div');
        const timeNode = row.cells?.[4]?.querySelectorAll('div')?.[1];
        const date = dateNode?.textContent?.trim() || '';
        const time = timeNode?.textContent?.trim() || '';

        return { name, service, date, time };
    }

    function openInfoModal(triggerOrName, service, date, time, remarks, email) {
        let payload = {
            name: triggerOrName,
            service,
            date,
            time,
            remarks,
            email,
            status: '',
            id: '',
            aptId: '',
            studentNumber: '',
            contact: '',
            program: '',
            photoUrl: '',
            created: '',
            updated: ''
        };

        if (triggerOrName && typeof triggerOrName === 'object' && triggerOrName.dataset) {
            const row = triggerOrName.closest?.('[data-appointment-row]') || null;
            const rowData = row?.dataset || {};
            payload = {
                name: triggerOrName.dataset.name || rowData.viewName || '',
                service: triggerOrName.dataset.service || rowData.viewService || '',
                date: triggerOrName.dataset.date || rowData.viewDate || '',
                time: triggerOrName.dataset.time || rowData.viewTime || '',
                remarks: triggerOrName.dataset.remarks || rowData.viewRemarks || '',
                email: triggerOrName.dataset.email || rowData.viewEmail || '',
                status: triggerOrName.dataset.status || triggerOrName.dataset.viewStatus || rowData.viewStatus || '',
                id: triggerOrName.dataset.appointmentId || rowData.appointmentId || '',
                aptId: triggerOrName.dataset.aptId || rowData.viewAptId || '',
                studentNumber: triggerOrName.dataset.studentNumber || rowData.viewStudentNumber || '',
                contact: triggerOrName.dataset.contact || rowData.viewContact || '',
                program: triggerOrName.dataset.program || rowData.viewProgram || '',
                photoUrl: triggerOrName.dataset.photoUrl || rowData.viewPhotoUrl || '',
                created: triggerOrName.dataset.created || rowData.viewCreated || '',
                updated: triggerOrName.dataset.updated || rowData.viewUpdated || ''
            };
        }

        const safeOrNA = (value) => {
            const text = safeText(value);
            return text === '-' ? 'N/A' : text;
        };
        const scheduleText = formatSchedule(payload.date, payload.time);
        const dateText = safeOrNA(payload.date ? formatDateLong(payload.date) : '');
        const timeText = safeOrNA(payload.time ? formatTime(payload.time) : '');
        const appointmentId = safeOrNA(payload.aptId);
        const initials = (payload.name || 'NA')
            .split(/\s+/)
            .filter(Boolean)
            .slice(0, 2)
            .map((part) => part.charAt(0).toUpperCase())
            .join('') || 'NA';

        document.getElementById('mAppointmentId').innerText = appointmentId;
        const avatar = document.getElementById('mAvatar');
        if (avatar) {
            avatar.textContent = '';
            if (payload.photoUrl) {
                const avatarImg = document.createElement('img');
                avatarImg.src = payload.photoUrl;
                avatarImg.alt = safeOrNA(payload.name) + ' profile photo';
                avatar.appendChild(avatarImg);
            } else {
                avatar.textContent = initials;
            }
        }
        document.getElementById('mName').innerText = safeOrNA(payload.name);
        document.getElementById('mService').innerText = safeOrNA(payload.service);
        document.getElementById('mDate').innerText = dateText;
        document.getElementById('mTime').innerText = timeText;
        document.getElementById('mNotes').innerText = safeOrNA(payload.remarks);
        document.getElementById('mEmail').innerText = safeOrNA(payload.email);
        document.getElementById('mContact').innerText = safeOrNA(payload.contact);
        document.getElementById('mStudentId').innerText = 'ID Number: ' + safeOrNA(payload.studentNumber);
        document.getElementById('mProgram').innerText = 'Program: ' + safeOrNA(payload.program);
        document.getElementById('mCreated').innerText = safeOrNA(payload.created);
        document.getElementById('mUpdated').innerText = safeOrNA(payload.updated);
        document.getElementById('mFooterCreated').innerText = safeOrNA(payload.created);
        document.getElementById('mFooterUpdated').innerText = safeOrNA(payload.updated);
        const statusEl = document.getElementById('mStatus');
        const normalizedStatus = safeText(payload.status);
        const statusText = normalizedStatus === '-' ? 'N/A' : normalizedStatus;
        document.getElementById('mStatusText').innerText = statusText;
        document.getElementById('mStatusInline').innerText = statusText;
        statusEl.className = 'modal-status-badge ' + normalizedStatus.toLowerCase().replace(/\s+/g, '-');
        statusEl.classList.add('appointment-detail-status');

        const timelineDate = (value) => safeOrNA(value).replace(' ', '\n');
        const scheduledTimelineDate = scheduleText === '-' ? 'N/A' : scheduleText.replace(' at ', '\n');
        document.getElementById('mTimelineSubmitted').innerText = timelineDate(payload.created);
        document.getElementById('mTimelinePending').innerText = timelineDate(payload.updated || payload.created);
        document.getElementById('mTimelineApproved').innerText = timelineDate(payload.updated);
        document.getElementById('mTimelineScheduled').innerText = scheduledTimelineDate;
        document.getElementById('mTimelineCompleted').innerText = timelineDate(payload.updated);
        document.getElementById('mTimelineApprovedLabel').innerText = 'Approved';

        const timeline = document.getElementById('mTimeline');
        const timelineSteps = ['submitted', 'pending', 'approved', 'scheduled', 'completed'];
        const normalizedTimelineStatus = statusText.toLowerCase();
        const isRejectedTimeline = ['rejected', 'cancelled', 'canceled', 'declined'].includes(normalizedTimelineStatus);
        const currentStep = isRejectedTimeline
            ? 'approved'
            : (normalizedTimelineStatus === 'completed'
                ? 'completed'
                : (normalizedTimelineStatus === 'scheduled'
                    ? 'scheduled'
                    : (normalizedTimelineStatus === 'approved'
                        ? 'approved'
                        : 'pending')));
        const currentIndex = timelineSteps.indexOf(currentStep);

        timelineSteps.forEach((step, index) => {
            const stepEl = timeline?.querySelector(`[data-timeline-step="${step}"]`);
            const lineEl = timeline?.querySelector(`[data-timeline-line="${step}"]`);
            if (!stepEl) return;

            stepEl.style.display = '';
            stepEl.classList.remove('is-done', 'is-current', 'is-muted', 'is-rejected');

            if (lineEl) {
                lineEl.style.display = '';
                lineEl.classList.remove('is-done');
            }

            if (isRejectedTimeline) {
                if (step === 'approved') {
                    stepEl.classList.add('is-rejected');
                    document.getElementById('mTimelineApprovedLabel').innerText = 'Rejected';
                    document.getElementById('mTimelineApproved').innerText = timelineDate(payload.updated);
                } else if (index < currentIndex) {
                    stepEl.classList.add('is-done');
                } else if (index > currentIndex) {
                    stepEl.style.display = 'none';
                }

                if (lineEl) {
                    if (index < currentIndex) {
                        lineEl.classList.add('is-done');
                    } else {
                        lineEl.style.display = 'none';
                    }
                }

                return;
            }

            if (normalizedTimelineStatus === 'completed') {
                stepEl.classList.add('is-done');
            } else if (index < currentIndex) {
                stepEl.classList.add('is-done');
            } else if (index === currentIndex) {
                stepEl.classList.add('is-current');
            } else {
                stepEl.classList.add('is-muted');
            }

            if (lineEl && index < currentIndex) {
                lineEl.classList.add('is-done');
            }
        });

        document.getElementById('infoModal').style.display = 'flex';
    }

    function closeInfoModal() {
        document.getElementById('infoModal').style.display = 'none';
    }

    function openStatusActionModal(trigger) {
        const fallback = getRowDataFromElement(trigger);
        const row = trigger?.closest?.('[data-appointment-row]') || null;
        const rowData = row?.dataset || {};
        const href = trigger?.getAttribute?.('href') || '';
        const matches = href.match(/\/appointments\/(\d+)\/([^/?#]+)/i);
        const decodedStatus = matches ? decodeURIComponent(matches[2]) : '';
        const statusTarget = trigger?.dataset?.statusTarget || decodedStatus || (href.includes('/Approved') ? 'Approved' : 'Cancelled');
        const id = trigger?.dataset?.id || (matches ? matches[1] : '');
        const actionUrl = id ? (appointmentsBaseUrl + '/' + id + '/' + encodeURIComponent(statusTarget)) : href;

        const name = trigger?.dataset?.name || rowData.viewName || fallback.name;
        const service = trigger?.dataset?.service || rowData.viewService || fallback.service;
        const date = trigger?.dataset?.date || rowData.viewDate || fallback.date;
        const time = trigger?.dataset?.time || rowData.viewTime || fallback.time;
        const appointmentId = safeText(rowData.viewAptId) === '-' ? 'N/A' : rowData.viewAptId;
        const email = safeText(rowData.viewEmail) === '-' ? 'N/A' : rowData.viewEmail;
        const contact = safeText(rowData.viewContact) === '-' ? 'N/A' : rowData.viewContact;
        const studentNumber = safeText(rowData.viewStudentNumber) === '-' ? 'N/A' : rowData.viewStudentNumber;
        const program = safeText(rowData.viewProgram) === '-' ? 'N/A' : rowData.viewProgram;
        const created = safeText(rowData.viewCreated) === '-' ? 'N/A' : rowData.viewCreated;
        const notes = safeText(rowData.viewRemarks) === '-' ? 'N/A' : rowData.viewRemarks;
        const consultationType = safeText(rowData.viewType) === '-' ? 'Online' : rowData.viewType;
        const photoUrl = rowData.viewPhotoUrl || '';

        const isApprove = statusTarget === 'Approved';
        const isReject = statusTarget === 'Cancelled';
        const isMissed = statusTarget === 'Missed Scheduled';
        const statusBadge = document.getElementById('statusActionBadge');
        document.getElementById('statusActionTitle').innerText = isApprove
            ? 'Approve Appointment'
            : (isMissed ? 'Mark Appointment as Missed' : 'Reject Appointment');
        document.getElementById('statusActionSubtitle').innerText = isApprove
            ? 'This will mark the appointment as approved and notify the workflow.'
            : (isMissed
                ? 'Use this only when the appointment is still not consulted and at least 1 hour has passed after the scheduled time.'
                : 'This will reject the appointment request and mark it as cancelled.');
        document.getElementById('sAppointmentId').innerText = appointmentId || 'N/A';
        document.getElementById('sName').innerText = safeText(name);
        document.getElementById('sEmail').innerText = email;
        document.getElementById('sContact').innerText = contact;
        document.getElementById('sStudentId').innerText = 'ID Number: ' + studentNumber;
        document.getElementById('sProgram').innerText = 'Program: ' + program;
        document.getElementById('sService').innerText = safeText(service);
        document.getElementById('sDate').innerText = safeText(date ? formatDateLong(date) : '');
        document.getElementById('sTime').innerText = safeText(time ? formatTime(time) : '');
        document.getElementById('sCreated').innerText = created;
        document.getElementById('sNotes').innerText = notes;
        document.getElementById('sStatusText').innerText = isApprove ? 'Pending' : (isMissed ? 'Approved' : 'Pending');
        document.getElementById('sTimelineSubmitted').innerText = created.replace(' ', '\n');
        document.getElementById('sTimelinePending').innerText = safeText(rowData.viewUpdated).replace(' ', '\n');
        document.getElementById('sTimelineScheduled').innerText = formatSchedule(date, time).replace(' at ', '\n');

        [document.getElementById('sConsultationType'), document.getElementById('sApprovalConsultationType')]
            .filter(Boolean)
            .forEach((consultationTypeEl) => {
                consultationTypeEl.textContent = consultationType;
                consultationTypeEl.className = 'appointment-type-badge ' + (consultationType.toLowerCase().includes('walk') ? 'is-walkin' : 'is-online');
            });

        const avatar = document.getElementById('sAvatar');
        if (avatar) {
            const initials = (name || 'NA')
                .split(/\s+/)
                .filter(Boolean)
                .slice(0, 2)
                .map((part) => part.charAt(0).toUpperCase())
                .join('') || 'NA';
            avatar.textContent = '';
            if (photoUrl) {
                const avatarImg = document.createElement('img');
                avatarImg.src = photoUrl;
                avatarImg.alt = safeText(name) + ' profile photo';
                avatar.appendChild(avatarImg);
            } else {
                avatar.textContent = initials;
            }
        }

        if (statusBadge) {
            statusBadge.className = 'modal-status-badge appointment-detail-status ' + (isApprove ? 'pending' : (isMissed ? 'missed' : 'cancelled'));
        }

        const confirmBtn = document.getElementById('statusActionConfirm');
        confirmBtn.href = actionUrl;
        const confirmText = isApprove
            ? 'Confirm Approval'
            : (isMissed ? 'Confirm Missed Status' : 'Confirm Rejection');
        confirmBtn.innerHTML = '<svg viewBox="0 0 24 24" fill="none" aria-hidden="true"><path d="M20 6 9 17l-5-5" stroke="currentColor" stroke-width="2.4" stroke-linecap="round" stroke-linejoin="round"/></svg><span>' + confirmText + '</span>';
        confirmBtn.className = 'dialog-btn ' + (isApprove ? 'dialog-btn-approve' : (isMissed ? 'dialog-btn-warning' : 'dialog-btn-reject'));

        document.getElementById('statusActionModal').style.display = 'flex';
    }

    function closeStatusActionModal() {
        document.getElementById('statusActionModal').style.display = 'none';
    }

    function openRescheduleModal(triggerOrId, currentDate, currentTime) {
        const form = document.getElementById('rescheduleForm');
        let id = '';
        let date = currentDate || '';
        let time = currentTime || '';
        let name = '';
        let service = '';

        if (triggerOrId && typeof triggerOrId === 'object' && triggerOrId.dataset) {
            id = triggerOrId.dataset.id || '';
            name = triggerOrId.dataset.name || '';
            service = triggerOrId.dataset.service || '';
            date = triggerOrId.dataset.date || '';
            time = triggerOrId.dataset.time || '';

            if (!id) {
                const fallback = getRowDataFromElement(triggerOrId);
                name = fallback.name;
                service = fallback.service;
                date = date || fallback.date;
                time = time || fallback.time;

                const href = triggerOrId.closest('td')?.querySelector('a.btn-approve')?.getAttribute('href') || '';
                const matches = href.match(/\/appointments\/(\d+)\/Approved/i);
                id = matches ? matches[1] : '';
            }
        } else {
            id = (triggerOrId ?? '').toString();
            const lookupTrigger = document.querySelector('a.btn-approve[href$="/' + id + '/Approved"]');
            const fallback = getRowDataFromElement(lookupTrigger);
            name = fallback.name;
            service = fallback.service;
            date = date || fallback.date;
            time = time || fallback.time;
        }

        if (!id) {
            return;
        }

        form.action = appointmentsBaseUrl + '/' + id + '/reschedule';
        document.getElementById('rName').innerText = safeText(name);
        document.getElementById('rService').innerText = safeText(service);
        document.getElementById('rCurrentSchedule').innerText = formatSchedule(date, time);
        document.getElementById('rDate').value = date;
        document.getElementById('rTime').value = (time || '').toString().slice(0, 5);
        document.getElementById('rDate').setAttribute('min', new Date().toISOString().slice(0, 10));
        document.getElementById('rescheduleModal').style.display = 'flex';
    }

    function closeRescheduleModal() {
        document.getElementById('rescheduleModal').style.display = 'none';
    }

    document.addEventListener('click', function(event) {
        const infoModal = document.getElementById('infoModal');
        const statusModal = document.getElementById('statusActionModal');
        const rescheduleModal = document.getElementById('rescheduleModal');

        if (event.target === infoModal) {
            closeInfoModal();
        }
        if (event.target === statusModal) {
            closeStatusActionModal();
        }
        if (event.target === rescheduleModal) {
            closeRescheduleModal();
        }
    });

    document.addEventListener('keydown', function(event) {
        if (event.key === 'Escape') {
            closeInfoModal();
            closeStatusActionModal();
            closeRescheduleModal();
        }
    });

    document.addEventListener('DOMContentLoaded', function() {
        const liveFeedNode = document.getElementById('adminLiveAlertFeedUrl');
        let appointmentsLivePollTimer = null;

        const initAppointmentsSummaryLiveSync = function () {
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

            const isAppointmentNotification = function (notification) {
                const id = (notification && notification.id ? String(notification.id) : '').trim();
                return id.startsWith('appointment-pending:');
            };

            const hydrateKnownIds = function (payload) {
                const notifications = Array.isArray(payload && payload.notifications) ? payload.notifications : [];
                knownNotificationIds = new Set(
                    notifications
                        .filter(isAppointmentNotification)
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
                            throw new Error('Failed to fetch live appointment updates.');
                        }
                        return response.json();
                    })
                    .then(function (payload) {
                        const notifications = Array.isArray(payload && payload.notifications) ? payload.notifications : [];
                        const appointmentNotifications = notifications.filter(isAppointmentNotification);
                        const hasNewAppointment = appointmentNotifications.some(function (notification) {
                            return !knownNotificationIds.has(String(notification.id));
                        });

                        if (hasNewAppointment) {
                            window.location.reload();
                            return;
                        }

                        hydrateKnownIds(payload);
                    })
                    .catch(function () {
                        // Keep the page usable even if polling fails.
                    });
            };

            fetch(feedUrl, {
                headers: {
                    'X-Requested-With': 'XMLHttpRequest',
                    'Accept': 'application/json'
                },
                credentials: 'same-origin'
            })
                .then(function (response) {
                    if (!response.ok) {
                        throw new Error('Failed to initialize live appointment updates.');
                    }
                    return response.json();
                })
                .then(function (payload) {
                    hydrateKnownIds(payload);
                })
                .catch(function () {
                    knownNotificationIds = new Set();
                });

            appointmentsLivePollTimer = window.setInterval(pullFeed, 10000);
            window.addEventListener('beforeunload', function () {
                if (appointmentsLivePollTimer) {
                    window.clearInterval(appointmentsLivePollTimer);
                }
            }, { once: true });
        };

        initAppointmentsSummaryLiveSync();

        const clearHighlightQueryParam = function(paramName) {
            const url = new URL(window.location.href);
            if (!url.searchParams.has(paramName)) {
                return;
            }
            url.searchParams.delete(paramName);
            window.history.replaceState({}, document.title, url.toString());
        };

        if (highlightedAppointmentId) {
            const highlightedRow = document.querySelector('[data-appointment-row][data-appointment-id="' + highlightedAppointmentId + '"]');
            if (highlightedRow) {
                highlightedRow.scrollIntoView({ behavior: 'smooth', block: 'center' });
                window.setTimeout(function() {
                    highlightedRow.classList.remove('appointment-highlight-row');
                    clearHighlightQueryParam('highlight_appointment');
                }, 5000);
            }
        }

        const searchInput = document.getElementById('searchInput');
        const searchShell = document.getElementById('appointmentsSearchShell');
        const searchToggle = document.getElementById('appointmentsSearchToggle');
        const filterShell = document.getElementById('appointmentsFilterShell');
        const filterToggle = document.getElementById('appointmentsFilterToggle');
        const filterPanel = document.getElementById('appointmentsFilterPanel');
        const statusFilter = document.getElementById('appointmentStatusFilter');
        const statusWrap = document.getElementById('appointmentsStatusWrap');
        const statusDisplay = document.getElementById('appointmentsStatusDisplay');
        const statusMenu = document.getElementById('appointmentsStatusMenu');
        const statusOptions = Array.from(document.querySelectorAll('.appointments-status-option'));
        const filterReset = document.getElementById('appointmentsFilterReset');
        const filterClose = document.getElementById('appointmentsFilterClose');
        const appointmentRows = Array.from(document.querySelectorAll('[data-appointment-row]'));
        let currentSearchTerm = '';
        let currentStatusFilter = '';

        function setSearchOpenState(isOpen) {
            if (!searchShell || !searchToggle) {
                return;
            }

            searchShell.classList.toggle('is-open', isOpen);
            searchToggle.classList.toggle('is-open', isOpen);
            searchToggle.setAttribute('aria-expanded', isOpen ? 'true' : 'false');
        }

        function setFilterOpenState(isOpen) {
            if (!filterShell || !filterToggle || !filterPanel) {
                return;
            }

            filterShell.classList.toggle('is-open', isOpen);
            filterToggle.setAttribute('aria-expanded', isOpen ? 'true' : 'false');
            filterPanel.setAttribute('aria-hidden', isOpen ? 'false' : 'true');
        }

        function setStatusDropdownOpen(isOpen) {
            if (!statusWrap || !statusDisplay) {
                return;
            }

            statusWrap.classList.toggle('is-open', isOpen);
            statusDisplay.classList.toggle('is-open', isOpen);
            statusDisplay.setAttribute('aria-expanded', isOpen ? 'true' : 'false');

            if (statusMenu) {
                statusMenu.setAttribute('aria-hidden', isOpen ? 'false' : 'true');
            }
        }

        function syncStatusDisplay() {
            if (!statusFilter || !statusDisplay) {
                return;
            }

            const selectedOption = statusOptions.find(function(option) {
                return (option.dataset.statusValue || '') === statusFilter.value;
            });

            statusDisplay.textContent = selectedOption ? selectedOption.textContent : 'All Statuses';

            statusOptions.forEach(function(option) {
                option.classList.toggle('is-selected', (option.dataset.statusValue || '') === statusFilter.value);
            });
        }

        function applyAppointmentFilters() {
            appointmentRows.forEach(function(row) {
                const nameCell = row.getElementsByTagName('td')[0];
                const nameNode = nameCell ? nameCell.getElementsByClassName('student-name')[0] : null;
                const rowName = nameNode ? (nameNode.textContent || nameNode.innerText || '') : '';
                const rowStatus = (row.dataset.viewStatus || '').trim().toLowerCase();

                const matchesSearch = currentSearchTerm === '' || rowName.toLowerCase().indexOf(currentSearchTerm) > -1;
                const matchesStatus = currentStatusFilter === '' || rowStatus === currentStatusFilter.toLowerCase();

                row.style.display = matchesSearch && matchesStatus ? '' : 'none';
            });
        }

        if (searchInput && searchInput.value.trim() !== '') {
            setSearchOpenState(true);
        }

        if (searchToggle && searchInput) {
            searchToggle.addEventListener('click', function() {
                const isOpening = !searchShell.classList.contains('is-open');
                setSearchOpenState(isOpening);

                if (isOpening) {
                    setTimeout(function() {
                        searchInput.focus();
                    }, 120);
                } else if (searchInput.value.trim() === '') {
                    searchInput.blur();
                }
            });
        }

        if (searchInput) {
            searchInput.addEventListener('keyup', function() {
                currentSearchTerm = this.value.trim().toLowerCase();
                applyAppointmentFilters();
            });

            searchInput.addEventListener('focus', function() {
                setSearchOpenState(true);
            });
        }

        if (filterToggle) {
            filterToggle.addEventListener('click', function() {
                if (!filterShell) {
                    return;
                }
                setFilterOpenState(!filterShell.classList.contains('is-open'));
            });
        }

        if (statusFilter) {
            statusFilter.addEventListener('change', function() {
                currentStatusFilter = this.value;
                syncStatusDisplay();
                applyAppointmentFilters();
            });
        }

        if (statusDisplay) {
            statusDisplay.addEventListener('click', function(event) {
                event.stopPropagation();
                const isOpen = statusWrap ? statusWrap.classList.contains('is-open') : false;
                setStatusDropdownOpen(!isOpen);
            });
        }

        statusOptions.forEach(function(option) {
            option.addEventListener('click', function() {
                const nextValue = this.dataset.statusValue || '';
                if (statusFilter) {
                    statusFilter.value = nextValue;
                    statusFilter.dispatchEvent(new Event('change', { bubbles: true }));
                }
                setStatusDropdownOpen(false);
            });
        });

        if (filterReset) {
            filterReset.addEventListener('click', function() {
                currentStatusFilter = '';
                if (statusFilter) {
                    statusFilter.value = '';
                    syncStatusDisplay();
                }
                applyAppointmentFilters();
                setStatusDropdownOpen(false);
            });
        }

        if (filterClose) {
            filterClose.addEventListener('click', function() {
                setFilterOpenState(false);
                setStatusDropdownOpen(false);
            });
        }

        document.addEventListener('click', function(event) {
            if (filterShell && !filterShell.contains(event.target)) {
                setFilterOpenState(false);
            }

            if (statusWrap && !statusWrap.contains(event.target)) {
                setStatusDropdownOpen(false);
            }
        });

        syncStatusDisplay();

        const actionMenus = document.querySelectorAll('[data-appointment-action-menu]');
        const closeActionMenus = function(exceptMenu = null) {
            actionMenus.forEach(function(menu) {
                if (exceptMenu && menu === exceptMenu) {
                    return;
                }

                menu.classList.remove('is-open');
                const toggle = menu.querySelector('.appointment-action-menu-toggle');
                if (toggle) {
                    toggle.setAttribute('aria-expanded', 'false');
                }
            });
        };

        actionMenus.forEach(function(menu) {
            const toggle = menu.querySelector('.appointment-action-menu-toggle');
            if (!toggle) {
                return;
            }

            toggle.addEventListener('click', function(event) {
                event.preventDefault();
                event.stopPropagation();

                const shouldOpen = !menu.classList.contains('is-open');
                closeActionMenus(menu);
                menu.classList.toggle('is-open', shouldOpen);
                toggle.setAttribute('aria-expanded', shouldOpen ? 'true' : 'false');
            });

            menu.querySelectorAll('.appointment-action-menu-item, .appointment-action-menu-state').forEach(function(item) {
                item.addEventListener('click', function() {
                    closeActionMenus();
                });
            });
        });

        document.addEventListener('click', function(event) {
            if (!event.target.closest('[data-appointment-action-menu]')) {
                closeActionMenus();
            }
        });

        document.querySelectorAll('a.btn-approve, a.btn-cancel, a.btn-missed, button.btn-reject').forEach((el) => {
            el.removeAttribute('onclick');
            el.addEventListener('click', function(event) {
                event.preventDefault();
                openStatusActionModal(this);
            });
        });

        document.querySelectorAll('button.btn-reschedule').forEach((el) => {
            const inlineHandler = el.getAttribute('onclick') || '';
            if (!el.dataset.id) {
                const matches = inlineHandler.match(/openRescheduleModal\('([^']+)'\s*,\s*'([^']+)'\s*,\s*'([^']+)'\)/);
                if (matches) {
                    el.dataset.id = matches[1];
                    el.dataset.date = matches[2];
                    el.dataset.time = matches[3];
                }
            }

            if (!el.dataset.name || !el.dataset.service) {
                const fallback = getRowDataFromElement(el);
                el.dataset.name = el.dataset.name || fallback.name;
                el.dataset.service = el.dataset.service || fallback.service;
            }

            el.removeAttribute('onclick');
            el.addEventListener('click', function() {
                openRescheduleModal(this);
            });
        });

        document.querySelectorAll('[data-appointment-row].appointment-row-clickable').forEach((row) => {
            row.addEventListener('click', function(event) {
                if (event.target.closest('a, button, input, select, textarea, label')) {
                    return;
                }

                openInfoModal({
                    dataset: {
                        appointmentId: row.dataset.appointmentId || '',
                        aptId: row.dataset.viewAptId || '',
                        name: row.dataset.viewName || '',
                        service: row.dataset.viewService || '',
                        date: row.dataset.viewDate || '',
                        time: row.dataset.viewTime || '',
                        remarks: row.dataset.viewRemarks || '',
                        email: row.dataset.viewEmail || '',
                        status: row.dataset.viewStatus || '',
                        studentNumber: row.dataset.viewStudentNumber || '',
                        contact: row.dataset.viewContact || '',
                        program: row.dataset.viewProgram || '',
                        photoUrl: row.dataset.viewPhotoUrl || '',
                        created: row.dataset.viewCreated || '',
                        updated: row.dataset.viewUpdated || '',
                    }
                });
            });
        });

        document.querySelectorAll('#infoModal [data-copy-target]').forEach((button) => {
            button.addEventListener('click', function () {
                const target = document.getElementById(button.dataset.copyTarget || '');
                const text = (target?.innerText || '').trim();
                if (!text || text === 'N/A') {
                    return;
                }

                const markCopied = () => {
                    button.classList.add('is-copied');
                    window.setTimeout(() => button.classList.remove('is-copied'), 900);
                };

                if (navigator.clipboard && window.isSecureContext) {
                    navigator.clipboard.writeText(text).then(markCopied).catch(() => {});
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
                    if (document.execCommand('copy')) {
                        markCopied();
                    }
                } catch (error) {
                    // Copy feedback is non-critical.
                } finally {
                    document.body.removeChild(tempInput);
                }
            });
        });
    });
</script>
@endpush
