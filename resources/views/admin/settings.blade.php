@extends('layouts.admin')

@section('title', 'Settings')

@push('styles')
<style>
    :root {
        --stg-maroon: #7f0000;
        --stg-maroon-deep: #4f0000;
        --stg-text: #111827;
        --stg-muted: #64748b;
        --stg-border: rgba(127, 0, 0, 0.12);
        --stg-surface: rgba(255, 255, 255, 0.94);
    }

    .settings-page {
        position: relative;
        isolation: isolate;
    }
    .settings-page::before {
        content: '';
        position: absolute;
        inset: 0;
        background:
            radial-gradient(circle at top left, rgba(127, 0, 0, 0.08), transparent 24%),
            radial-gradient(circle at bottom right, rgba(15, 23, 42, 0.06), transparent 24%),
            linear-gradient(180deg, rgba(248, 250, 252, 0.96), rgba(255, 255, 255, 0.92));
        pointer-events: none;
        z-index: -1;
        border-radius: 28px;
    }
    .settings-page > * {
        position: relative;
        z-index: 1;
    }

    .hero {
        background: #ffffff;
        color: var(--stg-text);
        border: 1px solid rgba(127, 0, 0, 0.12);
        border-radius: 20px;
        padding: 20px;
        margin-bottom: 22px;
        box-shadow: 0 14px 34px rgba(15,23,42,0.08);
    }
    .hero-top {
        display: flex;
        justify-content: space-between;
        gap: 18px;
        align-items: flex-start;
        flex-wrap: wrap;
    }
    .hero h1 {
        margin: 0;
        font-size: clamp(24px, 2.3vw, 30px);
        line-height: 1.05;
        font-weight: 900;
        letter-spacing: -0.03em;
        display: inline-flex;
        align-items: center;
        gap: 10px;
        padding: 0;
        border-radius: 0;
        border-bottom: 0;
        background: transparent;
    }
    .hero h1 svg {
        width: 42px;
        height: 42px;
        padding: 11px;
        border-radius: 12px;
        background: #fff1f2;
        color: #b91c1c;
        flex: 0 0 auto;
    }
    .hero p {
        margin: 12px 0 0;
        max-width: 760px;
        color: var(--stg-muted);
        line-height: 1.7;
        font-size: 14px;
    }
    .badges {
        display: flex;
        gap: 10px;
        flex-wrap: wrap;
        margin-top: 18px;
    }
    .badge {
        display: inline-flex;
        align-items: center;
        gap: 8px;
        padding: 9px 12px;
        border-radius: 999px;
        background: rgba(127,0,0,0.06);
        border: 1px solid rgba(127,0,0,0.12);
        color: var(--stg-maroon);
        font-size: 12px;
        font-weight: 800;
    }
    .badge span {
        width: 8px;
        height: 8px;
        border-radius: 50%;
        background: #f6c36a;
    }

    .settings-hub-grid {
        display: grid;
        grid-template-columns: repeat(3, minmax(0, 1fr));
        gap: 22px;
    }
    .settings-hub-card {
        min-height: 342px;
        display: flex;
        flex-direction: column;
        align-items: center;
        padding: 34px 28px 24px;
        border-radius: 18px;
        border: 1px solid rgba(127, 0, 0, 0.13);
        background: #ffffff;
        box-shadow: 0 18px 44px rgba(15, 23, 42, 0.08);
        text-decoration: none;
        color: var(--stg-text);
        transition: transform .2s ease, border-color .2s ease, box-shadow .2s ease;
    }
    .settings-hub-card:hover,
    .settings-hub-card:focus-visible {
        color: var(--stg-text);
        transform: translateY(-4px);
        border-color: rgba(250, 204, 21, 0.72);
        box-shadow: 0 24px 54px rgba(127, 0, 0, 0.13);
        outline: none;
    }
    .settings-hub-icon {
        position: relative;
        width: 96px;
        height: 96px;
        display: grid;
        place-items: center;
        border-radius: 50%;
        background: radial-gradient(circle at 30% 20%, #fff7cc, #fee2e2 58%, #f8fafc 100%);
        color: var(--stg-maroon);
        margin-bottom: 22px;
    }
    .settings-hub-icon svg {
        width: 48px;
        height: 48px;
        stroke-width: 1.8;
    }
    .settings-hub-badge {
        position: absolute;
        right: -2px;
        bottom: 8px;
        width: 30px;
        height: 30px;
        display: grid;
        place-items: center;
        border-radius: 50%;
        background: var(--stg-maroon);
        color: #facc15;
        border: 3px solid #ffffff;
        box-shadow: 0 10px 20px rgba(127, 0, 0, 0.2);
    }
    .settings-hub-badge svg {
        width: 15px;
        height: 15px;
        stroke-width: 2.2;
    }
    .settings-hub-card h3 {
        margin: 0;
        font-size: 18px;
        line-height: 1.2;
        font-weight: 900;
        text-align: center;
    }
    .settings-hub-card p {
        min-height: 46px;
        margin: 12px 0 20px;
        color: var(--stg-muted);
        font-size: 13px;
        line-height: 1.55;
        text-align: center;
    }
    .settings-hub-list {
        width: 100%;
        display: grid;
        gap: 11px;
        padding: 18px 0 0;
        margin: 0 0 24px;
        border-top: 1px solid rgba(127, 0, 0, 0.12);
        list-style: none;
    }
    .settings-hub-list li {
        display: flex;
        align-items: center;
        gap: 10px;
        color: #334155;
        font-size: 12px;
        font-weight: 750;
    }
    .settings-hub-list svg {
        width: 16px;
        height: 16px;
        color: var(--stg-maroon);
        flex: 0 0 auto;
    }
    .settings-hub-action {
        margin-top: auto;
        min-width: 132px;
        min-height: 38px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        gap: 9px;
        border-radius: 9px;
        border: 1px solid var(--stg-maroon);
        color: var(--stg-maroon);
        font-size: 12px;
        font-weight: 900;
        background: #ffffff;
        transition: all 0.3s ease;
        position: relative;
        overflow: hidden;
    }
    .settings-hub-action svg {
        width: 15px;
        height: 15px;
    }
    .settings-hub-card:hover .settings-hub-action,
    .settings-hub-card:focus-visible .settings-hub-action {
        animation: sweep-settings 0.6s ease-out forwards;
    }

    @keyframes sweep-settings {
        0% {
            background: #ffffff;
            color: var(--stg-maroon);
            border-color: var(--stg-maroon);
        }
        50% {
            background: linear-gradient(90deg, #facc15, #facc15);
        }
        100% {
            background: #facc15;
            color: #111827;
            border-color: #facc15;
        }
    }

    @media (min-width: 900px) {
        .settings-hub-card:nth-child(4) {
            grid-column: 1 / span 1;
        }
        .settings-hub-card:nth-child(5) {
            grid-column: 2 / span 1;
        }
    }
    @media (max-width: 1080px) {
        .settings-hub-grid {
            grid-template-columns: repeat(2, minmax(0, 1fr));
        }
    }
    @media (max-width: 720px) {
        .settings-hub-grid {
            grid-template-columns: 1fr;
        }
        .settings-hub-card {
            min-height: 0;
        }
    }

    .grid {
        display: grid;
        grid-template-columns: 1.05fr 1.25fr;
        gap: 26px;
    }

    .panel {
        position: relative;
        background: var(--stg-surface);
        border: 1px solid var(--stg-border);
        border-radius: 26px;
        box-shadow: 0 24px 70px rgba(15,23,42,0.10);
        overflow: hidden;
    }
    .panel::before {
        content: '';
        position: absolute;
        inset: 0 auto auto 0;
        width: 100%;
        height: 4px;
        background: linear-gradient(90deg, var(--stg-maroon), #ad2234 55%, #d4a373);
    }
    .panel-head {
        padding: 22px 24px 18px;
        border-bottom: 1px solid rgba(127,0,0,0.10);
        background: linear-gradient(180deg, rgba(127,0,0,0.03), rgba(255,255,255,0));
    }
    .panel-head-top {
        display: flex;
        align-items: flex-start;
        justify-content: space-between;
        gap: 14px;
    }
    .section-spot {
        display: inline-flex;
        align-items: center;
        gap: 8px;
        margin-bottom: 12px;
        padding: 7px 11px;
        border-radius: 999px;
        color: var(--stg-maroon);
        background: rgba(127, 0, 0, 0.08);
        border: 1px solid rgba(127, 0, 0, 0.12);
        font-size: 10px;
        font-weight: 900;
        letter-spacing: 0.12em;
        text-transform: uppercase;
        box-shadow: 0 8px 18px rgba(15,23,42,0.06);
    }
    .section-spot::before {
        content: '';
        width: 8px;
        height: 8px;
        border-radius: 50%;
        background: var(--stg-maroon);
        box-shadow: 0 0 0 4px rgba(127,0,0,0.10);
    }
    .panel-head h3 {
        margin: 0;
        font-size: 18px;
        color: var(--stg-maroon);
        font-weight: 900;
    }
    .panel-head p {
        margin: 6px 0 0;
        color: var(--stg-muted);
        line-height: 1.6;
        font-size: 13px;
    }
    .panel-body {
        padding: 28px;
    }
    .btn-edit-profile {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        border: 1px solid rgba(127,0,0,0.12);
        background: rgba(127,0,0,0.06);
        color: var(--stg-maroon);
        padding: 10px 14px;
        border-radius: 14px;
        font-weight: 800;
        cursor: pointer;
        white-space: nowrap;
        box-shadow: 0 10px 20px rgba(15,23,42,0.06);
    }
    .btn-edit-profile:hover {
        background: #facc15;
        border-color: #facc15;
        color: #111827;
        transform: translateY(-1px);
        box-shadow: 0 12px 24px rgba(15,23,42,0.12);
    }
    .mini-edit-btn {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        border: 1px solid rgba(127,0,0,0.12);
        background: rgba(127,0,0,0.06);
        color: var(--stg-maroon);
        padding: 9px 13px;
        border-radius: 13px;
        font-weight: 800;
        cursor: pointer;
        white-space: nowrap;
    }
    .mini-edit-btn:hover {
        background: #facc15;
        border-color: #facc15;
        color: #111827;
        transform: translateY(-1px);
        box-shadow: 0 12px 24px rgba(15,23,42,0.12);
    }

    .profile-top {
        display: flex;
        justify-content: space-between;
        gap: 12px;
        align-items: center;
        margin-bottom: 14px;
    }
    .profile-id {
        display: flex;
        align-items: center;
        gap: 14px;
        min-width: 0;
    }
    .profile-avatar {
        width: 60px;
        height: 60px;
        border-radius: 20px;
        flex: 0 0 auto;
        display: flex;
        align-items: center;
        justify-content: center;
        color: #facc15;
        font-size: 16px;
        font-weight: 900;
        letter-spacing: 0.02em;
        background: linear-gradient(135deg, var(--stg-maroon), var(--stg-maroon-deep));
        box-shadow: 0 16px 32px rgba(127,0,0,0.24);
    }
    .profile-name {
        margin: 0;
        font-size: 16px;
        font-weight: 900;
        color: var(--stg-text);
    }
    .profile-role {
        display: inline-flex;
        align-items: center;
        gap: 8px;
        padding: 8px 12px;
        border-radius: 999px;
        font-size: 12px;
        font-weight: 900;
    }
    .profile-role.active {
        color: #166534;
        background: linear-gradient(135deg, #dcfce7, #bbf7d0);
        border: 1px solid rgba(34, 197, 94, 0.24);
    }
    .profile-role.inactive {
        color: #b91c1c;
        background: linear-gradient(135deg, #fee2e2, #fecaca);
        border: 1px solid rgba(239, 68, 68, 0.24);
    }
    .profile-role svg {
        width: 14px;
        height: 14px;
        flex: 0 0 auto;
    }
    .profile-list {
        display: grid;
        gap: 12px;
    }
    .profile-row {
        display: flex;
        justify-content: space-between;
        gap: 12px;
        padding: 13px 14px;
        border-radius: 16px;
        background: rgba(255,255,255,0.95);
        border: 1px solid rgba(127,0,0,0.10);
    }
    .profile-row .key {
        color: var(--stg-muted);
        font-size: 12px;
        font-weight: 800;
        text-transform: uppercase;
        letter-spacing: 0.04em;
    }
    .profile-row .val {
        color: var(--stg-text);
        font-size: 13px;
        font-weight: 700;
        text-align: right;
    }
    .editable-list {
        display: grid;
        gap: 12px;
    }
    .editable-row {
        display: grid;
        grid-template-columns: minmax(120px, 160px) minmax(0, 1fr);
        align-items: center;
        gap: 16px;
        padding: 14px 16px;
        border-radius: 18px;
        background: rgba(255,255,255,0.95);
        border: 1px solid rgba(127,0,0,0.10);
    }
    .editable-key {
        color: var(--stg-muted);
        font-size: 12px;
        font-weight: 800;
        text-transform: uppercase;
        letter-spacing: 0.04em;
    }
    .editable-field input {
        width: 100%;
        min-height: 54px;
        padding: 14px 16px;
        border-radius: 16px;
        border: 1px solid rgba(127,0,0,0.10);
        background: linear-gradient(180deg, rgba(255,255,255,0.99), rgba(250,251,252,0.96));
        color: var(--stg-text);
        box-shadow: 0 12px 28px rgba(15,23,42,0.06), inset 0 1px 0 rgba(255,255,255,0.96);
        transition: 0.2s ease;
    }
    .editable-field input:hover {
        transform: translateY(-1px);
        box-shadow: 0 14px 32px rgba(15,23,42,0.08), inset 0 1px 0 rgba(255,255,255,0.98);
    }
    .editable-field input:focus {
        outline: none;
        border-color: var(--stg-maroon);
        background: #fff;
        box-shadow: 0 0 0 5px rgba(127,0,0,0.10), 0 18px 34px rgba(15,23,42,0.10);
        transform: translateY(-1px);
    }

    .field-grid {
        display: grid;
        gap: 16px;
    }
    .field-grid.two { grid-template-columns: repeat(2, minmax(0,1fr)); }
    .field-grid.three { grid-template-columns: repeat(3, minmax(0,1fr)); }

    .field {
        position: relative;
        padding: 14px 14px 12px;
        border-radius: 22px;
        background: linear-gradient(180deg, rgba(255,255,255,0.88), rgba(248,250,252,0.72));
        border: 1px solid rgba(127,0,0,0.08);
        box-shadow: 0 12px 28px rgba(15,23,42,0.06);
        backdrop-filter: blur(8px);
    }
    .field label {
        position: absolute;
        left: 14px;
        top: 0;
        transform: translateY(-10px);
        padding: 0 12px;
        min-height: 26px;
        border-radius: 999px;
        border: 1px solid rgba(127,0,0,0.10);
        background: linear-gradient(180deg, rgba(255,255,255,0.99), rgba(249,250,252,0.98));
        color: #5f6677;
        font-size: 10px;
        font-weight: 900;
        letter-spacing: 0.1em;
        text-transform: uppercase;
        pointer-events: none;
        box-shadow: 0 8px 18px rgba(15,23,42,0.08);
    }
    .field:focus-within label {
        color: var(--stg-maroon);
        border-color: rgba(127,0,0,0.20);
    }
    .field input,
    .field select {
        width: 100%;
        min-height: 60px;
        padding: 22px 16px 14px;
        border-radius: 18px;
        border: 1px solid rgba(127,0,0,0.10);
        background: linear-gradient(180deg, rgba(255,255,255,0.99), rgba(250,251,252,0.96));
        color: var(--stg-text);
        box-shadow: 0 14px 32px rgba(15,23,42,0.08), inset 0 1px 0 rgba(255,255,255,0.96);
        transition: 0.2s ease;
        appearance: none;
    }
    .field input:hover,
    .field select:hover {
        transform: translateY(-1px);
        box-shadow: 0 16px 34px rgba(15,23,42,0.10), inset 0 1px 0 rgba(255,255,255,0.98);
    }
    .field input:focus,
    .field select:focus {
        outline: none;
        border-color: var(--stg-maroon);
        background: #fff;
        box-shadow: 0 0 0 6px rgba(127,0,0,0.10), 0 20px 40px rgba(15,23,42,0.12), 0 0 28px rgba(127,0,0,0.10);
        transform: translateY(-2px);
    }
    .field input::placeholder { color: #94a3b8; }
    .field input:disabled {
        cursor: not-allowed;
        background: linear-gradient(180deg, #f8fafc, #eef2f7);
        color: #64748b;
    }
    .field select {
        padding-right: 56px;
        background-image:
            linear-gradient(to right, transparent calc(100% - 48px), rgba(127,0,0,0.12) calc(100% - 48px), rgba(127,0,0,0.12) calc(100% - 47px), transparent calc(100% - 47px)),
            linear-gradient(45deg, transparent 50%, #7f0000 50%),
            linear-gradient(135deg, #7f0000 50%, transparent 50%);
        background-position:
            right 0 top 0,
            calc(100% - 22px) calc(50% - 3px),
            calc(100% - 16px) calc(50% - 3px);
        background-size: 100% 100%, 6px 6px, 6px 6px;
        background-repeat: no-repeat;
        cursor: pointer;
    }
    .field-help {
        margin: 8px 0 0 6px;
        font-size: 12px;
        color: var(--stg-muted);
    }

    .switch-list {
        display: grid;
        gap: 14px;
    }
    .switch-item {
        display: flex;
        align-items: center;
        gap: 14px;
        padding: 16px 18px;
        border-radius: 20px;
        background: linear-gradient(180deg, rgba(255,255,255,0.98), rgba(248,250,252,0.92));
        border: 1px solid rgba(127,0,0,0.10);
        box-shadow: 0 12px 28px rgba(15,23,42,0.05);
    }
    .switch-item input {
        width: 18px;
        height: 18px;
        accent-color: var(--stg-maroon);
    }
    .switch-item label {
        position: static;
        transform: none;
        background: transparent;
        border: none;
        box-shadow: none;
        pointer-events: auto;
        padding: 0;
        min-height: auto;
        text-transform: none;
        letter-spacing: 0;
        font-size: 14px;
        color: var(--stg-text);
    }
    .workflow-settings {
        display: grid;
        gap: 16px;
    }
    .workflow-setting-group {
        padding: 18px;
        border: 1px solid rgba(127,0,0,0.10);
        border-radius: 20px;
        background: linear-gradient(180deg, rgba(255,255,255,0.98), rgba(248,250,252,0.92));
        box-shadow: 0 12px 28px rgba(15,23,42,0.05);
    }
    .workflow-setting-group h4 {
        margin: 0 0 5px;
        color: var(--stg-text);
        font-size: 15px;
        font-weight: 900;
    }
    .workflow-setting-group > p {
        margin: 0 0 15px;
        color: var(--stg-muted);
        font-size: 12px;
        line-height: 1.55;
    }
    .workflow-setting-group .field {
        box-shadow: none;
    }
    .workflow-setting-group textarea {
        width: 100%;
        min-height: 96px;
        resize: vertical;
        padding: 16px;
        border: 1px solid rgba(127,0,0,0.10);
        border-radius: 16px;
        background: #fff;
        color: var(--stg-text);
        font: inherit;
        line-height: 1.55;
        outline: none;
    }
    .workflow-setting-group textarea:focus {
        border-color: var(--stg-maroon);
        box-shadow: 0 0 0 4px rgba(127,0,0,0.08);
    }
    #clinicClosureMessage::placeholder {
        font-size: 11px;
    }
    .workflow-closure-fields {
        display: grid;
        gap: 16px;
        margin-top: 16px;
    }
    .workflow-status {
        display: inline-flex;
        align-items: center;
        gap: 8px;
        margin-top: 12px;
        padding: 7px 11px;
        border-radius: 999px;
        background: #eef2f7;
        color: #475569;
        font-size: 11px;
        font-weight: 900;
    }
    .workflow-status.is-active {
        background: #fff3cd;
        color: #7f1d1d;
    }
    .workflow-summary-list {
        display: grid;
        gap: 10px;
    }
    .workflow-summary-row {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 16px;
        padding: 13px 15px;
        border: 1px solid rgba(127,0,0,0.08);
        border-radius: 14px;
        background: #f8fafc;
    }
    .workflow-summary-row span {
        color: var(--stg-muted);
        font-size: 12px;
        font-weight: 800;
    }
    .workflow-summary-row strong {
        color: var(--stg-text);
        font-size: 12px;
        text-align: right;
    }
    .workflow-modal-box {
        width: min(920px, 96vw);
    }
    .workflow-duration-output {
        min-height: 48px;
        display: flex;
        align-items: center;
        padding: 12px 16px;
        border: 1px solid rgba(112, 19, 27, 0.15);
        border-radius: 12px;
        background: #f8fafc;
        color: #475569;
        font-size: 13px;
        font-weight: 900;
    }
    .workflow-message-label {
        display: block;
        margin: 0 0 7px;
        color: var(--stg-text);
        font-size: 12px;
        font-weight: 900;
    }
    .actions-row {
        display: flex;
        justify-content: flex-end;
        margin-top: 18px;
    }
    .btn-save {
        background: linear-gradient(135deg, var(--stg-maroon), #9a1010 48%, var(--stg-maroon-deep));
        color: #fff;
        padding: 13px 24px;
        border: 1px solid #8f2230;
        border-radius: 999px;
        font-weight: 900;
        position: relative;
        overflow: hidden;
        box-shadow:
            0 0 0 3px rgba(112, 19, 27, 0.12),
            0 10px 22px rgba(112, 19, 27, 0.20);
        cursor: pointer;
        transition: color .08s linear, transform .18s ease, box-shadow .18s ease, border-color .18s ease;
        z-index: 0;
    }
    .btn-save::after {
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
    .btn-save:hover {
        transform: translateY(-1px);
        border-color: #facc15;
        background: #facc15;
        color: #111827;
        box-shadow:
            0 0 0 3px rgba(250, 204, 21, 0.18),
            0 14px 24px rgba(112, 19, 27, 0.16);
    }
    .btn-save:hover::after {
        transform: translateX(135%);
    }

    .modal-body .field select,
    .workflow-setting-group textarea {
        border: 1px solid rgba(127, 0, 0, 0.18);
        background: linear-gradient(180deg, #ffffff, #fffdf8);
        box-shadow: 0 8px 18px rgba(15, 23, 42, 0.06), inset 0 1px 0 #ffffff;
        transition: border-color .18s ease, box-shadow .18s ease, transform .18s ease;
    }

    .modal-body .field select:hover,
    .workflow-setting-group textarea:hover {
        border-color: rgba(127, 0, 0, 0.38);
    }

    /* ── Settings Modals — MA-style design ─────────────────────── */
    .modal-overlay {
        display: none;
        position: fixed;
        inset: 0;
        padding: clamp(12px, 2vw, 28px);
        background: rgba(15, 23, 42, 0.52);
        backdrop-filter: blur(10px);
        -webkit-backdrop-filter: blur(10px);
        z-index: 500050 !important;
        justify-content: center;
        align-items: center;
    }
    body.settings-modal-open .admin-header,
    body.settings-modal-open .medicine-alert-fab,
    body.settings-modal-open .medicine-alert-panel,
    body.settings-modal-open .medicine-hover-hint,
    body.settings-modal-open .admin-live-alert {
        z-index: 1 !important;
        pointer-events: none !important;
    }
    body.settings-modal-open .main {
        position: relative;
        z-index: 1000 !important;
        isolation: isolate;
    }
    body.settings-modal-open .admin-header {
        filter: blur(6px) saturate(0.9);
    }
    .modal-box {
        width: min(680px, 100%);
        max-width: 100%;
        height: min(860px, calc(100dvh - clamp(24px, 4vw, 56px)));
        max-height: min(860px, calc(100dvh - clamp(24px, 4vw, 56px)));
        border-radius: 22px;
        overflow: hidden;
        display: flex;
        flex-direction: column;
        background: #ffffff;
        border-left: 1px solid rgba(112, 19, 27, 0.12);
        border-right: 1px solid rgba(112, 19, 27, 0.12);
        border-top: 4px solid #facc15;
        border-bottom: 4px solid #70131B;
        box-shadow: 0 26px 70px rgba(15, 23, 42, 0.22);
    }
    /* Fixed header */
    .modal-head {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 12px;
        padding: clamp(12px, 1.4vw, 18px) clamp(14px, 1.6vw, 22px);
        background: linear-gradient(135deg, #70131B, #8f2230);
        border-bottom: 1px solid rgba(255, 255, 255, 0.12);
        flex: 0 0 auto;
        position: sticky;
        top: 0;
        z-index: 10;
        text-align: left;
    }
    .modal-head-main {
        display: flex;
        align-items: center;
        gap: 14px;
        min-width: 0;
        flex: 1 1 auto;
    }
    .modal-head-badge {
        width: 44px;
        height: 44px;
        flex: 0 0 44px;
        border-radius: 14px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        background: rgba(255, 255, 255, 0.16);
        border: 1px solid rgba(255, 255, 255, 0.24);
        color: #ffffff;
        font-size: 12px;
        font-weight: 900;
        letter-spacing: 0.06em;
    }
    .modal-head-copy { min-width: 0; }
    .modal-head h3 {
        margin: 0;
        color: #ffffff !important;
        font-size: clamp(15px, 1.4vw, 18px);
        font-weight: 900;
        letter-spacing: -0.01em;
    }
    .modal-head p {
        margin: 3px 0 0;
        color: rgba(255, 255, 255, 0.82) !important;
        font-size: 12px;
        line-height: 1.5;
    }
    .modal-head .section-spot { display: none; }
    .modal-head-close {
        width: 40px;
        height: 40px;
        min-width: 40px;
        min-height: 40px;
        padding: 0;
        flex: 0 0 40px;
        margin-left: auto;
        border-radius: 999px;
        position: relative;
        overflow: hidden;
        border: 1px solid #8f2230;
        background: linear-gradient(135deg, #70131B, #8f2230);
        color: #ffffff;
        display: inline-flex; align-items: center; justify-content: center;
        cursor: pointer;
        box-shadow:
            0 0 0 3px rgba(112, 19, 27, 0.12),
            0 10px 22px rgba(112, 19, 27, 0.20);
        transition: color .08s linear, transform .18s ease, box-shadow .18s ease, border-color .18s ease;
        z-index: 0;
    }
    .modal-head-close::after {
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
    .modal-head-close:hover {
        transform: translateY(-1px);
        background: #facc15;
        color: #111827;
        border-color: #facc15;
        box-shadow:
            0 0 0 3px rgba(250, 204, 21, 0.18),
            0 14px 24px rgba(112, 19, 27, 0.16);
    }
    .modal-head-close:hover::after {
        transform: translateX(135%);
    }
    .modal-head-close svg { width: 18px; height: 18px; stroke-width: 2.2; }
    /* Scrollable body */
    .modal-body {
        flex: 1 1 auto;
        overflow-y: auto;
        padding: 22px 26px 10px;
        min-height: 0;
        scrollbar-width: none;
        -ms-overflow-style: none;
    }
    .modal-body::-webkit-scrollbar { display: none; }
    /* Sticky footer */
    .modal-actions {
        flex: 0 0 auto;
        display: flex;
        justify-content: flex-end;
        gap: 10px;
        padding: 14px 26px 20px;
        background: rgba(255, 255, 255, 0.96);
        border-top: 1px solid rgba(112, 19, 27, 0.10);
        backdrop-filter: blur(8px);
    }
    /* Form must also flex so body expands */
    .modal-box > form,
    .modal-box form {
        display: flex;
        flex-direction: column;
        flex: 1 1 auto;
        min-height: 0;
        overflow: hidden;
    }
    .btn-cancel {
        min-height: 44px;
        padding: 0 18px;
        border-radius: 999px;
        border: 1px solid rgba(112, 19, 27, 0.18);
        background: #f8fafc;
        color: #334155;
        font-weight: 800;
        font-size: 13px;
        cursor: pointer;
        transition: background .18s ease, transform .18s ease;
    }
    .btn-cancel:hover { background: #e2e8f0; transform: translateY(-1px); }
    /* Field styles with shadow */
    .modal-body .field-grid,
    .modal-body .field-grid.two,
    .modal-body .field-grid.three {
        grid-template-columns: 1fr;
        gap: 14px;
    }
    .modal-body .field {
        padding: 0;
        border-radius: 0;
        background: transparent;
        border: none;
        box-shadow: none;
        backdrop-filter: none;
    }
    .modal-body .field label {
        position: static;
        transform: none;
        display: block;
        min-height: 0;
        margin-bottom: 6px;
        padding: 0;
        border: none;
        border-radius: 0;
        background: transparent;
        box-shadow: none;
        color: #475569;
        font-size: 11px;
        font-weight: 900;
        text-transform: uppercase;
        letter-spacing: 0.06em;
    }
    .modal-body .field input,
    .modal-body .field select {
        width: 100%;
        min-height: 48px;
        padding: 12px 16px;
        border-radius: 12px;
        border: 1px solid rgba(112, 19, 27, 0.15);
        background: linear-gradient(180deg, #ffffff, #fff8f6);
        color: #111827;
        font-size: 13px;
        font-weight: 700;
        box-shadow:
            inset 0 1px 0 rgba(255,255,255,0.92),
            0 2px 4px rgba(112, 19, 27, 0.04),
            0 8px 16px rgba(112, 19, 27, 0.07);
        transition: border-color .18s ease, box-shadow .2s ease, transform .18s ease;
        outline: none;
    }
    .modal-body .field select {
        padding-right: 56px;
        appearance: none;
        -webkit-appearance: none;
        -moz-appearance: none;
        background-image:
            linear-gradient(to right, transparent calc(100% - 48px), rgba(112, 19, 27, 0.12) calc(100% - 48px), rgba(112, 19, 27, 0.12) calc(100% - 47px), transparent calc(100% - 47px)),
            linear-gradient(45deg, transparent 50%, #8b0000 50%),
            linear-gradient(135deg, #8b0000 50%, transparent 50%),
            linear-gradient(180deg, #ffffff, #fff8f6);
        background-position:
            right 0 top 0,
            calc(100% - 22px) calc(50% - 3px),
            calc(100% - 16px) calc(50% - 3px),
            left top;
        background-size: 100% 100%, 6px 6px, 6px 6px, 100% 100%;
        background-repeat: no-repeat;
        cursor: pointer;
    }
    .modal-body .field input:hover,
    .modal-body .field select:hover {
        border-color: rgba(112, 19, 27, 0.28);
    }
    .modal-body .field input:focus,
    .modal-body .field select:focus {
        border-color: #70131B;
        transform: translateY(-1px);
        box-shadow:
            inset 0 1px 0 rgba(255,255,255,0.92),
            0 0 0 3px rgba(112, 19, 27, 0.08),
            0 10px 22px rgba(112, 19, 27, 0.10);
    }
    .modal-body .field input:disabled {
        background: #f8fafc;
        color: #64748b;
        border-color: #e2e8f0;
        box-shadow: none;
        transform: none;
    }
    .modal-body .field-help {
        margin: 8px 0 0;
        padding-left: 2px;
    }
    .modal-section {
        padding: 18px;
        border-radius: 20px;
        border: 1px solid rgba(127,0,0,0.10);
        background: linear-gradient(180deg, rgba(255,255,255,0.98), rgba(248,250,252,0.94));
        box-shadow: 0 12px 26px rgba(15,23,42,0.06);
    }
    .modal-section + .modal-section {
        margin-top: 18px;
    }
    .modal-section-kicker {
        display: inline-flex;
        align-items: center;
        gap: 8px;
        margin-bottom: 12px;
        padding: 6px 12px;
        border-radius: 999px;
        background: rgba(127, 0, 0, 0.08);
        color: var(--stg-maroon);
        font-size: 10px;
        font-weight: 900;
        letter-spacing: 0.10em;
        text-transform: uppercase;
    }
    .modal-section-kicker::before {
        content: "";
        width: 8px;
        height: 8px;
        border-radius: 50%;
        background: var(--stg-maroon);
        box-shadow: 0 0 0 4px rgba(127,0,0,0.10);
    }
    .modal-section-title {
        margin: 0 0 6px;
        color: var(--stg-maroon);
        font-size: 18px;
        font-weight: 900;
    }
    .modal-section-copy {
        margin: 0 0 14px;
        color: var(--stg-muted);
        font-size: 13px;
        line-height: 1.6;
    }

    .alert {
        padding: 14px 16px;
        border-radius: 16px;
        margin-bottom: 20px;
        font-size: 14px;
        font-weight: 700;
        border: 1px solid transparent;
    }
    .alert-success { background: #dcfce7; color: #15803d; border-color: #bbf7d0; }
    .alert-error { background: #fee2e2; color: #b91c1c; border-color: #fecaca; }

    .profile-summary .panel-head,
    .field-grid + .field-grid {
        margin-top: 0;
    }
    .profile-stack {
        display: grid;
        gap: 16px;
    }
    .profile-stack .panel {
        margin-bottom: 0;
    }

    html[data-theme="dark"] .settings-page::before {
        background:
            radial-gradient(circle at top left, rgba(255, 184, 28, 0.06), transparent 22%),
            radial-gradient(circle at bottom right, rgba(127, 0, 0, 0.18), transparent 24%),
            linear-gradient(180deg, rgba(35, 11, 18, 0.98), rgba(24, 8, 14, 0.96));
    }
    html[data-theme="dark"] .hero {
        background: linear-gradient(180deg, rgba(57, 22, 31, 0.96), rgba(39, 14, 21, 0.98));
        color: #fff1f4;
        border-color: rgba(255, 255, 255, 0.08);
        box-shadow: 0 24px 60px rgba(0, 0, 0, 0.28);
    }
    html[data-theme="dark"] .hero h1 {
        color: #fff4f7;
    }
    html[data-theme="dark"] .hero p {
        color: #e8cad2;
    }
    html[data-theme="dark"] .badge {
        background: rgba(255, 255, 255, 0.08);
        border-color: rgba(255, 255, 255, 0.10);
        color: #ffd9e1;
    }
    html[data-theme="dark"] .panel {
        background: linear-gradient(180deg, rgba(58, 23, 32, 0.96), rgba(38, 14, 21, 0.94));
        border-color: rgba(255, 255, 255, 0.08);
        box-shadow: 0 24px 70px rgba(0, 0, 0, 0.26);
    }
    html[data-theme="dark"] .panel-head {
        border-bottom-color: rgba(255, 255, 255, 0.08);
        background: linear-gradient(180deg, rgba(255,255,255,0.03), rgba(255,255,255,0));
    }
    html[data-theme="dark"] .panel-head h3,
    html[data-theme="dark"] .section-spot,
    html[data-theme="dark"] .btn-edit-profile,
    html[data-theme="dark"] .mini-edit-btn {
        color: #ffd7df;
    }
    html[data-theme="dark"] .panel-head p,
    html[data-theme="dark"] .field-help,
    html[data-theme="dark"] .profile-row .key,
    html[data-theme="dark"] .section-subtitle {
        color: #d7b3bc !important;
    }
    html[data-theme="dark"] .section-spot,
    html[data-theme="dark"] .btn-edit-profile,
    html[data-theme="dark"] .mini-edit-btn {
        background: rgba(255, 255, 255, 0.08);
        border-color: rgba(255, 255, 255, 0.10);
        box-shadow: none;
    }
    html[data-theme="dark"] .section-spot::before {
        background: #ffb8c6;
        box-shadow: 0 0 0 4px rgba(255, 184, 198, 0.12);
    }
    html[data-theme="dark"] .profile-name,
    html[data-theme="dark"] .profile-row .val,
    html[data-theme="dark"] .switch-item label {
        color: #fff1f4;
    }
    html[data-theme="dark"] .profile-role.active {
        color: #bbf7d0;
        background: rgba(22, 101, 52, 0.22);
        border-color: rgba(34, 197, 94, 0.24);
    }
    html[data-theme="dark"] .profile-role.inactive {
        color: #fecaca;
        background: rgba(153, 27, 27, 0.22);
        border-color: rgba(248, 113, 113, 0.24);
    }
    html[data-theme="dark"] .profile-row,
    html[data-theme="dark"] .switch-item,
    html[data-theme="dark"] .editable-row {
        background: rgba(255, 255, 255, 0.05);
        border-color: rgba(255, 255, 255, 0.08);
    }
    html[data-theme="dark"] .editable-key {
        color: #d7b3bc;
    }
    html[data-theme="dark"] .editable-field input,
    html[data-theme="dark"] .field input,
    html[data-theme="dark"] .field select {
        background: rgba(20, 9, 13, 0.92);
        color: #fff1f4;
        border-color: rgba(255, 255, 255, 0.08);
        box-shadow: inset 0 1px 0 rgba(255,255,255,0.04);
    }
    html[data-theme="dark"] .editable-field input::placeholder,
    html[data-theme="dark"] .field input::placeholder {
        color: #b7929d;
    }
    html[data-theme="dark"] .field label {
        background: rgba(32, 12, 19, 0.96);
        border-color: rgba(255, 255, 255, 0.08);
        color: #e1c0c8;
        box-shadow: none;
    }
    html[data-theme="dark"] .field:focus-within label {
        color: #ffd7df;
        border-color: rgba(255, 184, 198, 0.16);
    }
    html[data-theme="dark"] .editable-field input:hover,
    html[data-theme="dark"] .field input:hover,
    html[data-theme="dark"] .field select:hover {
        box-shadow: 0 0 0 1px rgba(255,255,255,0.04);
    }
    html[data-theme="dark"] .editable-field input:focus,
    html[data-theme="dark"] .field input:focus,
    html[data-theme="dark"] .field select:focus {
        background: rgba(28, 11, 17, 0.98);
        border-color: #ffb8c6;
        box-shadow: 0 0 0 5px rgba(255, 184, 198, 0.10), 0 12px 30px rgba(0,0,0,0.24);
    }
    html[data-theme="dark"] .field input:disabled {
        background: rgba(53, 32, 39, 0.84);
        color: #c9aab3;
    }
    html[data-theme="dark"] .modal-overlay {
        background: rgba(5, 2, 4, 0.68);
    }
    html[data-theme="dark"] .modal-box {
        background: rgba(15, 23, 42, 0.98);
        border-top-color: #facc15;
        border-bottom-color: #facc15;
        border-left-color: rgba(143, 34, 48, 0.36);
        border-right-color: rgba(143, 34, 48, 0.36);
        box-shadow: 0 34px 90px rgba(0,0,0,0.42);
    }
    html[data-theme="dark"] .modal-head {
        background: #4d0d17;
        border-bottom-color: rgba(250, 204, 21, 0.2);
    }
    html[data-theme="dark"] .modal-actions {
        background: rgba(15, 23, 42, 0.96);
        border-top-color: rgba(250, 204, 21, 0.14);
    }
    html[data-theme="dark"] .modal-body .field input,
    html[data-theme="dark"] .modal-body .field select {
        background: rgba(17, 24, 39, 0.88);
        color: #f8fafc;
        border-color: rgba(148, 163, 184, 0.22);
        box-shadow: inset 0 1px 0 rgba(255,255,255,0.04), 0 2px 4px rgba(0,0,0,0.12), 0 8px 18px rgba(0,0,0,0.18);
    }
    html[data-theme="dark"] .modal-body .field input:focus,
    html[data-theme="dark"] .modal-body .field select:focus {
        border-color: #facc15;
        box-shadow: 0 0 0 3px rgba(250,204,21,0.12), 0 10px 24px rgba(0,0,0,0.22);
    }
    html[data-theme="dark"] .field select {
        background-image:
            linear-gradient(to right, transparent calc(100% - 48px), rgba(250,204,21,0.18) calc(100% - 48px), rgba(250,204,21,0.18) calc(100% - 47px), transparent calc(100% - 47px)),
            linear-gradient(45deg, transparent 50%, #facc15 50%),
            linear-gradient(135deg, #facc15 50%, transparent 50%);
    }
    html[data-theme="dark"] .modal-body .field label {
        color: #94a3b8;
    }
    html[data-theme="dark"] .btn-cancel {
        background: rgba(255,255,255,0.06);
        color: #fff1f4;
        border-color: rgba(255,255,255,0.10);
    }
    html[data-theme="dark"] .modal-body .field label {
        color: #d7b3bc;
    }
    html[data-theme="dark"] .modal-body .field input,
    html[data-theme="dark"] .modal-body .field select {
        background: rgba(20, 9, 13, 0.92);
        color: #fff1f4;
        border-color: rgba(255,255,255,0.10);
    }
    html[data-theme="dark"] .modal-body .field select {
        background-image:
            linear-gradient(to right, transparent calc(100% - 48px), rgba(250,204,21,0.16) calc(100% - 48px), rgba(250,204,21,0.16) calc(100% - 47px), transparent calc(100% - 47px)),
            linear-gradient(45deg, transparent 50%, #facc15 50%),
            linear-gradient(135deg, #facc15 50%, transparent 50%),
            linear-gradient(180deg, rgba(20, 9, 13, 0.92), rgba(20, 9, 13, 0.92));
    }
    html[data-theme="dark"] .modal-body .field input:hover,
    html[data-theme="dark"] .modal-body .field select:hover {
        border-color: rgba(255,255,255,0.18);
    }
    html[data-theme="dark"] .modal-body .field input:focus,
    html[data-theme="dark"] .modal-body .field select:focus {
        border-color: #ffb8c6;
        box-shadow: 0 0 0 4px rgba(255,184,198,0.10);
    }
    html[data-theme="dark"] .modal-section {
        background: rgba(255,255,255,0.05);
        border-color: rgba(255,255,255,0.08);
        box-shadow: 0 18px 34px rgba(0,0,0,0.18);
    }
    html[data-theme="dark"] .workflow-setting-group,
    html[data-theme="dark"] .workflow-summary-row {
        background: rgba(255,255,255,0.05);
        border-color: rgba(255,255,255,0.09);
        box-shadow: none;
    }
    html[data-theme="dark"] .workflow-setting-group h4,
    html[data-theme="dark"] .workflow-summary-row strong {
        color: #fff1f4;
    }
    html[data-theme="dark"] .workflow-setting-group > p,
    html[data-theme="dark"] .workflow-summary-row span {
        color: #d7b3bc;
    }
    html[data-theme="dark"] .workflow-setting-group textarea {
        background: rgba(20, 9, 13, 0.92);
        color: #fff1f4;
        border-color: rgba(255,255,255,0.10);
    }
    html[data-theme="dark"] .workflow-setting-group textarea::placeholder {
        color: #b7929d;
    }
    html[data-theme="dark"] .workflow-setting-group textarea:focus {
        border-color: #facc15;
        box-shadow: 0 0 0 4px rgba(250,204,21,0.10);
    }
    html[data-theme="dark"] .workflow-duration-output {
        background: rgba(20, 9, 13, 0.92);
        color: #fff1f4;
        border-color: rgba(255,255,255,0.10);
    }
    html[data-theme="dark"] .workflow-message-label {
        color: #fff1f4;
    }
    html[data-theme="dark"] .workflow-status {
        background: rgba(255,255,255,0.08);
        color: #e5e7eb;
    }
    html[data-theme="dark"] .workflow-status.is-active {
        background: rgba(250,204,21,0.14);
        color: #fde68a;
    }
    html[data-theme="dark"] .modal-section-kicker {
        background: rgba(255,255,255,0.08);
        color: #ffd7df;
    }
    html[data-theme="dark"] .modal-section-kicker::before {
        background: #ffb8c6;
        box-shadow: 0 0 0 4px rgba(255,184,198,0.10);
    }
    html[data-theme="dark"] .modal-section-title {
        color: #ffd7df;
    }
    html[data-theme="dark"] .modal-section-copy {
        color: #d7b3bc;
    }
    html[data-theme="dark"] .alert-success {
        background: rgba(22, 101, 52, 0.18);
        color: #b7f7cd;
        border-color: rgba(34, 197, 94, 0.24);
    }
    html[data-theme="dark"] .alert-error {
        background: rgba(153, 27, 27, 0.18);
        color: #fecaca;
        border-color: rgba(248, 113, 113, 0.24);
    }

    @media (max-width: 1080px) {
        .grid { grid-template-columns: 1fr; }
    }
    @media (max-width: 768px) {
        .hero { padding: 24px 20px; }
        .panel-body, .modal-body, .modal-head { padding-left: 18px; padding-right: 18px; }
        .field-grid.two, .field-grid.three { grid-template-columns: 1fr; }
        .editable-row { grid-template-columns: 1fr; gap: 10px; }
        .modal-overlay { padding: 12px; }
        .modal-actions { padding: 14px 18px 18px; flex-wrap: wrap; }
        .modal-actions button { width: 100%; }
    }

    /* Reports-style Settings hub */
    .settings-page {
        max-width: 1180px;
        margin: 0 auto;
        padding: 20px;
        border-radius: 22px;
        border: 1px solid rgba(112, 19, 27, 0.10);
        background: linear-gradient(180deg, rgba(255,255,255,0.92), rgba(250,244,246,0.96));
        box-shadow: 0 18px 34px rgba(112, 19, 27, 0.08);
        overflow: hidden;
    }
    .settings-page::before {
        inset: 0 14px auto;
        height: 5px;
        border-radius: 999px;
        background: #70131B;
        z-index: 2;
    }
    .settings-page > * {
        position: relative;
        z-index: 3;
    }
    .settings-page .hero {
        margin: 0 0 24px;
        padding: 0 0 24px;
        border: 0;
        border-bottom: 1px solid #e2e8f0;
        border-radius: 0;
        background: transparent;
        box-shadow: none;
    }
    .settings-page .hero h1 {
        display: block;
        margin: 0 0 12px;
        color: #111827;
        font-size: 22px;
        font-weight: 800;
        line-height: 1.25;
        letter-spacing: 0;
    }
    .settings-page .hero h1 svg {
        display: none;
    }
    .settings-page .hero p {
        max-width: 760px;
        margin: 0;
        color: #111827;
        font-size: 16px;
        font-weight: 600;
        line-height: 1.45;
    }
    .settings-page .badges {
        display: none;
    }
    .settings-hub-grid {
        grid-template-columns: repeat(3, minmax(0, 1fr));
        gap: 20px;
    }
    .settings-hub-card {
        min-height: 160px;
        align-items: stretch;
        justify-content: space-between;
        padding: 24px 20px;
        border-radius: 16px;
        border: 1px solid rgba(112, 19, 27, 0.22);
        background: #70131B;
        color: #ffffff;
        box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.10);
        overflow: hidden;
        position: relative;
        transition: all .3s ease;
    }
    .settings-hub-card:nth-child(2) {
        background: #facc15;
        color: #111111;
        border-color: rgba(250, 204, 21, .72);
    }
    .settings-hub-card > * {
        position: relative;
        z-index: 2;
    }
    .settings-hub-card::after {
        content: "";
        position: absolute;
        top: -45%;
        bottom: -45%;
        left: -85%;
        width: 55%;
        background: linear-gradient(120deg, transparent 0%, rgba(255,255,255,.16) 45%, rgba(255,255,255,.34) 50%, transparent 100%);
        transform: translateX(0) skewX(-18deg);
        transition: transform .65s ease;
        pointer-events: none;
        z-index: 1;
    }
    .settings-hub-card:hover,
    .settings-hub-card:focus-visible {
        background: #facc15 !important;
        color: #111111 !important;
        border-color: #facc15 !important;
        transform: translateY(-8px);
        box-shadow: 0 0 0 3px rgba(250, 204, 21, 0.12), 0 20px 30px rgba(139, 0, 0, 0.22);
        outline: none;
    }
    .settings-hub-card:hover::after,
    .settings-hub-card:focus-visible::after {
        transform: translateX(360%) skewX(-18deg);
    }
    .settings-hub-label {
        margin-bottom: 5px;
        color: #cbd5e1;
        font-size: 14px;
        font-weight: 500;
        letter-spacing: .5px;
        text-transform: uppercase;
    }
    .settings-hub-card:nth-child(2) .settings-hub-label {
        color: rgba(17, 17, 17, .74);
    }
    .settings-hub-card h3 {
        margin: 0;
        color: inherit;
        font-size: 22px;
        font-weight: 700;
        line-height: 1.2;
        text-align: left;
    }
    .settings-hub-footer {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 12px;
        margin-top: 18px;
    }
    .settings-hub-action {
        min-width: 0;
        min-height: 0;
        margin: 0;
        padding: 6px 12px;
        border: 0;
        border-radius: 8px;
        background: rgba(255,255,255,.10);
        color: #ffffff;
        font-size: 11px;
        font-weight: 600;
    }
    .settings-hub-card:nth-child(2) .settings-hub-action,
    .settings-hub-card:hover .settings-hub-action,
    .settings-hub-card:focus-visible .settings-hub-action {
        animation: none;
        background: rgba(17,17,17,.10) !important;
        color: #111111 !important;
        border: 0;
    }
    .settings-hub-action svg {
        display: none;
    }
    .settings-hub-card-icon {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        width: 42px;
        height: 42px;
        border-radius: 14px;
        color: #facc15;
        background: rgba(255,255,255,.12);
        border: 1px solid rgba(255,255,255,.18);
        transition: transform .3s ease, background .3s ease, color .3s ease;
    }
    .settings-hub-card:nth-child(2) .settings-hub-card-icon,
    .settings-hub-card:hover .settings-hub-card-icon,
    .settings-hub-card:focus-visible .settings-hub-card-icon {
        background: rgba(17,17,17,.10);
        border-color: rgba(17,17,17,.14);
        color: #111111;
        transform: translateX(3px) scale(1.04);
    }
    .settings-hub-card-icon svg {
        width: 22px;
        height: 22px;
        stroke-width: 1.8;
    }
    html[data-theme="dark"] .settings-page {
        background: linear-gradient(180deg, rgba(70, 19, 27, 0.92), rgba(46, 13, 19, 0.96));
        border-color: rgba(255,255,255,.08);
        box-shadow: 0 20px 38px rgba(0,0,0,.24);
    }
    html[data-theme="dark"] .settings-page::before {
        background: #facc15;
    }
    html[data-theme="dark"] .settings-page .hero {
        border-bottom-color: rgba(255,255,255,.12);
    }
    html[data-theme="dark"] .settings-page .hero h1,
    html[data-theme="dark"] .settings-page .hero p {
        color: #f8fafc;
    }
    html[data-theme="dark"] .settings-hub-card {
        background: rgba(112, 19, 27, .96);
        border-color: rgba(250, 204, 21, .12);
    }
    html[data-theme="dark"] .settings-hub-card:hover,
    html[data-theme="dark"] .settings-hub-card:focus-visible {
        background: #facc15 !important;
        border-color: #facc15 !important;
    }
    @media (max-width: 1024px) {
        .settings-hub-grid {
            grid-template-columns: repeat(2, minmax(0, 1fr));
            gap: 16px;
        }
    }
    @media (max-width: 768px) {
        .settings-page {
            padding: 20px 18px;
        }
        .settings-hub-grid {
            grid-template-columns: 1fr;
            gap: 14px;
        }
        .settings-hub-card {
            min-height: 140px;
            padding: 20px 18px;
            border-radius: 14px;
        }
        .settings-hub-label {
            font-size: 11px;
        }
        .settings-hub-card h3 {
            font-size: 18px;
        }
    }

    /* Walk-in-style Settings hub */
    .settings-page {
        max-width: 1400px;
        margin: 0 auto;
        padding: 24px 28px 28px;
        border-radius: 22px;
        border: 1px solid rgba(112, 19, 27, 0.10);
        background: rgba(255, 255, 255, 0.94);
        box-shadow: 0 18px 34px rgba(112, 19, 27, 0.08);
        overflow: hidden;
    }
    .settings-page::before {
        inset: 0 14px auto;
        height: 5px;
        border-radius: 999px;
        background: #70131B;
        z-index: 2;
    }
    .settings-page > * {
        position: relative;
        z-index: 3;
    }
    .settings-page .hero {
        margin: 0 0 24px;
        padding: 0;
        border: 0;
        border-radius: 0;
        background: transparent;
        box-shadow: none;
    }
    .settings-page .hero h1 {
        display: block;
        margin: 0 0 10px;
        color: #8b0000;
        font-size: 16px;
        font-weight: 800;
        letter-spacing: 1px;
        line-height: 1.2;
        text-transform: uppercase;
    }
    .settings-page .hero h1 svg {
        display: none;
    }
    .settings-page .hero p {
        max-width: 760px;
        margin: 0;
        color: #0f172a;
        font-size: 22px;
        font-weight: 800;
        line-height: 1.25;
    }
    .settings-page .badges {
        display: none;
    }
    .settings-hub-grid {
        display: grid;
        grid-template-columns: repeat(4, minmax(0, 1fr));
        gap: 16px;
        margin-top: 24px;
    }
    .settings-hub-card,
    .settings-hub-card:nth-child(2) {
        position: relative;
        min-height: 238px;
        height: 100%;
        display: block;
        padding: 18px;
        border-radius: 16px;
        border: 1px solid rgba(112, 19, 27, 0.46);
        background: linear-gradient(135deg, #70131B, #8f2230);
        color: #ffffff;
        box-shadow: inset 0 -3px 0 rgba(250, 204, 21, 0.72), 0 10px 24px rgba(112, 19, 27, 0.18);
        overflow: hidden;
        text-decoration: none;
        transition: transform .2s ease, box-shadow .2s ease, border-color .2s ease, color .2s ease;
    }
    .settings-hub-card::before {
        content: "";
        position: absolute;
        inset: 0;
        pointer-events: none;
        background: linear-gradient(180deg, rgba(255,255,255,0.08) 0%, rgba(255,255,255,0) 38%);
        z-index: 0;
    }
    .settings-hub-card::after {
        content: "";
        position: absolute;
        top: -38%;
        bottom: -38%;
        left: -135%;
        width: 34%;
        opacity: 0;
        background: linear-gradient(105deg, rgba(255, 255, 255, 0) 0%, rgba(255, 246, 184, 0.18) 42%, rgba(255, 246, 184, 0.54) 50%, rgba(255, 246, 184, 0.18) 58%, rgba(255, 255, 255, 0) 100%);
        transform: translateX(0) skewX(-18deg);
        pointer-events: none;
        z-index: 0;
    }
    .settings-hub-card:hover,
    .settings-hub-card:focus-visible {
        background: #facc15 !important;
        background-image: none !important;
        color: #111111 !important;
        transform: translateY(-8px);
        box-shadow: 0 0 0 3px rgba(250, 204, 21, 0.12), 0 20px 30px rgba(139, 0, 0, 0.22);
        border-color: #facc15 !important;
        outline: none;
    }
    .settings-hub-card:hover::after,
    .settings-hub-card:focus-visible::after {
        animation: settingsIntakeSweep .92s ease both;
    }
    @keyframes settingsIntakeSweep {
        0% { opacity: 0; transform: translateX(0) skewX(-18deg); }
        18% { opacity: .72; }
        72% { opacity: .72; }
        100% { opacity: 0; transform: translateX(820%) skewX(-18deg); }
    }
    .settings-hub-card > * {
        position: relative;
        z-index: 1;
    }
    .settings-hub-chip {
        position: absolute;
        top: 12px;
        right: 12px;
        width: 28px;
        height: 28px;
        border-radius: 999px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        z-index: 2;
        border: 1px solid rgba(255, 248, 196, 0.72);
        background: rgba(255, 248, 196, 0.12);
        color: #ffffff;
        box-shadow: 0 8px 14px rgba(15, 23, 42, 0.14);
        transition: background .2s ease, color .2s ease, border-color .2s ease;
    }
    .settings-hub-chip svg {
        width: 14px;
        height: 14px;
        stroke-width: 2.2;
    }
    .settings-hub-card-icon {
        position: relative;
        z-index: 1;
        width: 58px;
        height: 58px;
        border-radius: 16px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        margin-bottom: 20px;
        background: rgba(255, 248, 196, 0.12);
        color: #ffffff;
        border: 1px solid rgba(255, 248, 196, 0.16);
        animation: settingsIntakeFloat 3.8s ease-in-out infinite;
        transition: background .2s ease, color .2s ease, border-color .2s ease;
    }
    .settings-hub-card-icon::after {
        content: "";
        position: absolute;
        left: 10%;
        right: 10%;
        bottom: -10px;
        height: 14px;
        border-radius: 999px;
        filter: blur(8px);
        opacity: .6;
        z-index: -1;
        background: radial-gradient(circle, rgba(0, 0, 0, 0.44) 0%, rgba(0, 0, 0, 0.22) 48%, transparent 86%);
    }
    .settings-hub-card-icon svg {
        width: 24px;
        height: 24px;
        stroke-width: 2.1;
    }
    @keyframes settingsIntakeFloat {
        0%, 100% { transform: translateY(0); }
        50% { transform: translateY(-5px); }
    }
    .settings-hub-label {
        margin: 0 0 6px;
        color: rgba(255,255,255,.82) !important;
        font-size: 12px;
        font-weight: 800;
        letter-spacing: .04em;
        text-transform: uppercase;
    }
    .settings-hub-card h3 {
        margin: 0 0 10px;
        color: #ffffff !important;
        font-size: 18px;
        font-weight: 800;
        line-height: 1.25;
        text-align: left;
        transition: color .2s ease;
    }
    .settings-hub-copy {
        margin: 0;
        color: #ffffff !important;
        font-size: 16px;
        font-weight: 600;
        line-height: 1.42;
        text-align: left !important;
        transition: color .2s ease;
    }
    .settings-hub-footer {
        display: block;
        margin: 0;
    }
    .settings-hub-action {
        display: none;
    }
    .settings-hub-card:nth-child(2),
    .settings-hub-card:nth-child(2) .settings-hub-label,
    .settings-hub-card:nth-child(2) h3,
    .settings-hub-card:nth-child(2) .settings-hub-copy {
        color: #ffffff !important;
    }
    .settings-hub-card:hover .settings-hub-label,
    .settings-hub-card:focus-visible .settings-hub-label,
    .settings-hub-card:hover h3,
    .settings-hub-card:focus-visible h3,
    .settings-hub-card:hover .settings-hub-copy,
    .settings-hub-card:focus-visible .settings-hub-copy {
        color: #70131B !important;
    }
    .settings-hub-card:hover .settings-hub-chip,
    .settings-hub-card:focus-visible .settings-hub-chip,
    .settings-hub-card:hover .settings-hub-card-icon,
    .settings-hub-card:focus-visible .settings-hub-card-icon {
        background: #70131B;
        color: #ffffff;
        border-color: rgba(112, 19, 27, 0.62);
    }
    html[data-theme="dark"] .settings-page {
        background: linear-gradient(180deg, rgba(70, 19, 27, 0.92), rgba(46, 13, 19, 0.96));
        border-color: rgba(255,255,255,.08);
        box-shadow: 0 20px 38px rgba(0,0,0,.24);
    }
    html[data-theme="dark"] .settings-page::before {
        background: #facc15;
    }
    html[data-theme="dark"] .settings-page .hero h1,
    html[data-theme="dark"] .settings-page .hero p {
        color: #ffffff;
    }
    html[data-theme="dark"] .settings-hub-card,
    html[data-theme="dark"] .settings-hub-card:nth-child(2) {
        background: #70131B;
        border-color: rgba(250, 204, 21, 0.62);
        box-shadow: inset 0 -3px 0 rgba(250, 204, 21, 0.92), 0 14px 26px rgba(0,0,0,.22);
    }
    html[data-theme="dark"] .settings-hub-card::before {
        background: none;
    }
    html[data-theme="dark"] .settings-hub-card::after {
        background: linear-gradient(105deg, rgba(255,255,255,0) 0%, rgba(255,248,196,.52) 48%, rgba(255,255,255,0) 100%);
    }
    html[data-theme="dark"] .settings-hub-card:hover,
    html[data-theme="dark"] .settings-hub-card:focus-visible {
        background: #facc15 !important;
        background-image: none !important;
        border-color: #facc15 !important;
    }
    /* Final Settings hub layout guard: override older 3-column/manual placement rules. */
    .settings-page .settings-hub-grid {
        grid-template-columns: repeat(4, minmax(0, 1fr)) !important;
    }
    .settings-page .settings-hub-card,
    .settings-page .settings-hub-card:nth-child(2),
    .settings-page .settings-hub-card:nth-child(4),
    .settings-page .settings-hub-card:nth-child(5) {
        min-width: 0;
        grid-column: auto !important;
        background: linear-gradient(135deg, #70131B, #8f2230) !important;
        color: #ffffff !important;
    }
    .settings-page .settings-hub-card .settings-hub-card-icon,
    .settings-page .settings-hub-card:nth-child(2) .settings-hub-card-icon {
        background: rgba(255, 248, 196, 0.12) !important;
        border-color: rgba(255, 248, 196, 0.16) !important;
        color: #ffffff !important;
        transform: none;
    }
    .settings-page .settings-hub-chip svg {
        display: block;
        width: 13px;
        height: 13px;
        stroke-width: 2.6;
    }
    .settings-page .settings-hub-card:hover,
    .settings-page .settings-hub-card:focus-visible {
        background: #facc15 !important;
        color: #70131B !important;
    }
    .settings-page .settings-hub-card:hover .settings-hub-card-icon,
    .settings-page .settings-hub-card:focus-visible .settings-hub-card-icon,
    .settings-page .settings-hub-card:hover .settings-hub-chip,
    .settings-page .settings-hub-card:focus-visible .settings-hub-chip {
        background: #70131B !important;
        border-color: rgba(112, 19, 27, 0.62) !important;
        color: #ffffff !important;
    }
    @media (max-width: 980px) {
        .settings-hub-grid {
            grid-template-columns: repeat(2, minmax(0, 1fr)) !important;
        }
    }
    @media (max-width: 720px) {
        .settings-page {
            max-width: 100%;
            padding: 20px 18px;
        }
        .settings-hub-grid {
            grid-template-columns: 1fr !important;
        }
    }
    /* Patient Intake-style compact container behavior */
    .settings-page {
        width: min(100%, 980px) !important;
        max-width: 980px !important;
        margin: 0 auto !important;
        padding: 16px 16px 18px !important;
        border-radius: 12px !important;
    }
    .settings-page::before {
        left: 12px !important;
        right: 12px !important;
    }
    .settings-page .hero {
        margin: 0 0 18px !important;
        padding: 0 !important;
    }
    .settings-page .hero h1 {
        font-size: 13px !important;
        margin-bottom: 8px !important;
    }
    .settings-page .hero p {
        max-width: 760px !important;
        font-size: 20px !important;
        line-height: 1.2 !important;
    }
    .settings-page .settings-hub-grid {
        grid-template-columns: repeat(4, minmax(0, 1fr)) !important;
        gap: 12px !important;
        margin-top: 20px !important;
    }
    .settings-page .settings-hub-card,
    .settings-page .settings-hub-card:nth-child(2),
    .settings-page .settings-hub-card:nth-child(4),
    .settings-page .settings-hub-card:nth-child(5) {
        min-height: 246px !important;
        padding: 16px !important;
        border-radius: 12px !important;
    }
    .settings-page .settings-hub-card-icon {
        width: 48px !important;
        height: 48px !important;
        border-radius: 13px !important;
        margin-bottom: 14px !important;
    }
    .settings-page .settings-hub-card-icon svg {
        width: 21px !important;
        height: 21px !important;
    }
    .settings-page .settings-hub-card h3 {
        font-size: 16px !important;
        margin-bottom: 8px !important;
    }
    .settings-page .settings-hub-copy {
        font-size: 14px !important;
        line-height: 1.42 !important;
    }
    @media (max-width: 920px) {
        .settings-page {
            width: 100% !important;
            max-width: 100% !important;
        }
        .settings-page .settings-hub-grid {
            grid-template-columns: repeat(2, minmax(0, 1fr)) !important;
        }
    }
    @media (max-width: 560px) {
        .settings-page {
            width: 100% !important;
            max-width: 100% !important;
            padding: 18px !important;
        }
        .settings-page .settings-hub-grid {
            grid-template-columns: 1fr !important;
        }
    }
    .settings-page .settings-hub-card .settings-hub-card-icon,
    .settings-page .settings-hub-card:nth-child(2) .settings-hub-card-icon,
    .settings-page .settings-hub-card:hover .settings-hub-card-icon,
    .settings-page .settings-hub-card:focus-visible .settings-hub-card-icon {
        color: #facc15 !important;
    }
    .settings-page .settings-hub-card .settings-hub-card-icon svg,
    .settings-page .settings-hub-card:hover .settings-hub-card-icon svg,
    .settings-page .settings-hub-card:focus-visible .settings-hub-card-icon svg {
        stroke: currentColor !important;
    }
    @media (min-width: 921px) {
        .settings-page .settings-hub-grid {
            grid-template-columns: repeat(4, minmax(0, calc((100% - 52px) / 4))) !important;
            justify-content: start !important;
        }
    }

    /* Patient Intake sizing parity */
    .settings-page .settings-hub-grid {
        grid-template-columns: repeat(auto-fit, minmax(220px, 1fr)) !important;
        gap: 16px !important;
        margin-top: 24px !important;
    }
    .settings-page .settings-hub-card,
    .settings-page .settings-hub-card:nth-child(2),
    .settings-page .settings-hub-card:nth-child(4),
    .settings-page .settings-hub-card:nth-child(5) {
        min-height: 314px !important;
        height: 100% !important;
        padding: 20px !important;
        border-radius: 16px !important;
    }
    .settings-page .settings-hub-chip {
        top: 12px !important;
        right: 12px !important;
        width: 28px !important;
        height: 28px !important;
    }
    .settings-page .settings-hub-card-icon {
        width: 58px !important;
        height: 58px !important;
        border-radius: 16px !important;
        margin-bottom: 14px !important;
    }
    .settings-page .settings-hub-card-icon svg {
        width: 24px !important;
        height: 24px !important;
    }
    .settings-page .settings-hub-card h3 {
        margin: 0 0 8px !important;
        font-size: 18px !important;
        line-height: 1.25 !important;
    }
    .settings-page .settings-hub-copy {
        font-size: 16px !important;
        line-height: 1.55 !important;
    }
    @media (min-width: 1000px) {
        .settings-page .settings-hub-grid {
            grid-template-columns: repeat(4, minmax(220px, 1fr)) !important;
        }
    }
    .settings-page .hero p {
        max-width: none !important;
        width: 100% !important;
    }

    /* Final Settings hub surface pass: match Reports/Developer Tools */
    .settings-page {
        border-color: rgba(250, 204, 21, 0.20) !important;
    }

    .settings-page::before {
        background: #70131B !important;
    }

    .settings-page .settings-hub-card,
    .settings-page .settings-hub-card:nth-child(2),
    .settings-page .settings-hub-card:nth-child(4),
    .settings-page .settings-hub-card:nth-child(5) {
        background: #70131B !important;
        background-image: none !important;
        border: 1px solid rgba(250, 204, 21, 0.18) !important;
        box-shadow: 0 10px 24px rgba(112, 19, 27, 0.18) !important;
        color: #ffffff !important;
    }

    html[data-theme="dark"] .settings-page {
        background: transparent !important;
        background-image: none !important;
        border-color: rgba(250, 204, 21, 0.20) !important;
        box-shadow: 0 18px 34px rgba(0, 0, 0, 0.18) !important;
    }

    html[data-theme="dark"] .settings-page::before {
        background: #facc15 !important;
    }

    html[data-theme="dark"] .settings-page .settings-hub-card,
    html[data-theme="dark"] .settings-page .settings-hub-card:nth-child(2),
    html[data-theme="dark"] .settings-page .settings-hub-card:nth-child(4),
    html[data-theme="dark"] .settings-page .settings-hub-card:nth-child(5) {
        background: transparent !important;
        background-image: none !important;
        border: 1px solid rgba(250, 204, 21, 0.18) !important;
        box-shadow: 0 18px 34px rgba(0, 0, 0, 0.34), 0 4px 12px rgba(0, 0, 0, 0.22) !important;
        color: #ffffff !important;
    }

    .settings-page .settings-hub-card:hover,
    .settings-page .settings-hub-card:focus-visible,
    html[data-theme="dark"] .settings-page .settings-hub-card:hover,
    html[data-theme="dark"] .settings-page .settings-hub-card:focus-visible {
        background: #facc15 !important;
        background-image: none !important;
        border-color: #facc15 !important;
        box-shadow: 0 0 0 3px rgba(250, 204, 21, 0.12), 0 20px 30px rgba(139, 0, 0, 0.22) !important;
        color: #70131B !important;
    }

    /* Final light-mode surface pass */
    html:not([data-theme="dark"]) .settings-page {
        border: 1px solid rgba(250, 204, 21, 0.20) !important;
        box-shadow: 0 18px 34px rgba(112, 19, 27, 0.08) !important;
    }

    html:not([data-theme="dark"]) .settings-page .settings-hub-card,
    html:not([data-theme="dark"]) .settings-page .settings-hub-card:nth-child(2),
    html:not([data-theme="dark"]) .settings-page .settings-hub-card:nth-child(4),
    html:not([data-theme="dark"]) .settings-page .settings-hub-card:nth-child(5) {
        border: 1px solid rgba(250, 204, 21, 0.22) !important;
        box-shadow: 0 14px 28px rgba(112, 19, 27, 0.12), 0 4px 12px rgba(15, 23, 42, 0.06) !important;
    }

</style>
@endpush

@section('content')
<div class="settings-page">
    @php
        $reminderLabels = [0 => 'Disabled', 1 => '1 hour before', 3 => '3 hours before', 24 => '1 day before', 48 => '2 days before'];
        $closureIsCurrentlyActive = app(\App\Services\ClinicWorkflowService::class)->activeClosure() !== null;
        $canAccessSetting = fn (string $permission): bool => optional(auth()->user())->canAccessPermission($permission) ?? false;
        $isSuperAdmin = \App\Models\User::normalizeRole(optional(auth()->user())->user_role ?? '') === \App\Models\User::ROLE_SUPERADMIN;
    @endphp

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

    <section class="hero">
        <div class="hero-top">
            <div>
                <h1><x-outline-icon name="cog-6-tooth" />Settings</h1>
                <p>Manage your account, clinic details, medical setup, users, and system preferences.</p>
                <div class="badges">
                    @if($canAccessSetting('settings.personal'))<div class="badge"><span></span> Personal Information</div>@endif
                    @if($canAccessSetting('settings.clinic'))<div class="badge"><span></span> Clinic Information</div>@endif
                    @if($canAccessSetting('settings.preferences'))<div class="badge"><span></span> System Preferences</div>@endif
                    @if($canAccessSetting('settings.medical'))<div class="badge"><span></span> Medical Configuration</div>@endif
                    @if($isSuperAdmin)<div class="badge"><span></span> Users Management</div>@endif
                </div>
            </div>
        </div>
    </section>

    <div class="settings-hub-grid">
        @if($canAccessSetting('settings.personal'))
        <a href="{{ route('admin.settings.personal') }}" class="settings-hub-card">
            <span class="settings-hub-chip" aria-hidden="true"><x-outline-icon name="chevron-right" /></span>
            <span class="settings-hub-card-icon" aria-hidden="true"><x-outline-icon name="user-circle" /></span>
            <div>
                <h3>Personal Information</h3>
                <p class="settings-hub-copy">Update your profile details, email address, password, and account identity.</p>
            </div>
        </a>
        @endif

        @if($canAccessSetting('settings.clinic'))
        <a href="{{ route('admin.settings.clinic') }}" class="settings-hub-card">
            <span class="settings-hub-chip" aria-hidden="true"><x-outline-icon name="chevron-right" /></span>
            <span class="settings-hub-card-icon" aria-hidden="true"><x-outline-icon name="home" /></span>
            <div>
                <h3>Clinic Information</h3>
                <p class="settings-hub-copy">Manage clinic profile, contact information, service details, and office hours.</p>
            </div>
        </a>
        @endif

        @if($canAccessSetting('settings.preferences'))
        <a href="{{ route('admin.settings.preferences') }}" class="settings-hub-card">
            <span class="settings-hub-chip" aria-hidden="true"><x-outline-icon name="chevron-right" /></span>
            <span class="settings-hub-card-icon" aria-hidden="true"><x-outline-icon name="code-bracket-square" /></span>
            <div>
                <h3>System Preferences</h3>
                <p class="settings-hub-copy">Configure workflow behavior, notifications, reminders, and availability rules.</p>
            </div>
        </a>
        @endif

        @if($canAccessSetting('settings.medical'))
        <a href="{{ route('admin.settings.medical') }}" class="settings-hub-card">
            <span class="settings-hub-chip" aria-hidden="true"><x-outline-icon name="plus" /></span>
            <span class="settings-hub-card-icon" aria-hidden="true"><x-outline-icon name="clipboard-document-list" /></span>
            <div>
                <h3>Medical Configuration</h3>
                <p class="settings-hub-copy">Open medical conditions and medicine type setup used by clinic workflows.</p>
            </div>
        </a>
        @endif

        @if($isSuperAdmin)
        <a href="{{ route('admin.user-management') }}" class="settings-hub-card">
            <span class="settings-hub-chip" aria-hidden="true"><x-outline-icon name="plus" /></span>
            <span class="settings-hub-card-icon" aria-hidden="true"><x-outline-icon name="users" /></span>
            <div>
                <h3>Users Management</h3>
                <p class="settings-hub-copy">Open account access, admin hub profiles, roles, and user permissions.</p>
            </div>
        </a>
        @endif

        @if($canAccessSetting('settings.faqs'))
        <a href="{{ route('admin.settings.faqs') }}" class="settings-hub-card">
            <span class="settings-hub-chip" aria-hidden="true"><x-outline-icon name="plus" /></span>
            <span class="settings-hub-card-icon" aria-hidden="true"><x-outline-icon name="question-mark-circle" /></span>
            <div>
                <h3>FAQs</h3>
                <p class="settings-hub-copy">View frequently asked questions about clinic appointments, records, and services.</p>
            </div>
        </a>
        @endif
    </div>

    @if($canAccessSetting('settings.preferences'))
    <div id="workflowSettingsModal" class="modal-overlay">
        <div class="modal-box workflow-modal-box">
            <div class="modal-head">
                <div class="modal-head-main">
                    <div class="modal-head-badge">WF</div>
                    <div class="modal-head-copy">
                        <h3>Workflow Settings</h3>
                        <p>Manage admin alerts, appointment behavior, access hours, reminders, and temporary clinic availability.</p>
                    </div>
                </div>
                <button type="button" class="modal-head-close" onclick="closeSettingsModal('workflowSettingsModal')" aria-label="Close">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round"><path d="M18 6 6 18M6 6l12 12"/></svg>
                </button>
            </div>
            <form action="{{ url('/admin/settings/update') }}" method="POST">
                @csrf @method('PUT')
                <input type="hidden" name="preferences_form" value="1">
                <input type="hidden" name="email_notifications" value="{{ $settings->email_notifications !== false ? '1' : '0' }}">
                <input type="hidden" name="pending_compliance_reminder_days" value="{{ (int) ($settings->pending_compliance_reminder_days ?? 7) }}">
                <input type="hidden" name="pending_compliance_reminder_max_count" value="{{ (int) ($settings->pending_compliance_reminder_max_count ?? 3) }}">
                <input type="hidden" name="notification_quiet_hours_enabled" value="{{ $settings->notification_quiet_hours_enabled ? '1' : '0' }}">
                <input type="hidden" name="notification_quiet_hours_start" value="{{ substr((string) ($settings->notification_quiet_hours_start ?: '20:00'), 0, 5) }}">
                <input type="hidden" name="notification_quiet_hours_end" value="{{ substr((string) ($settings->notification_quiet_hours_end ?: '07:00'), 0, 5) }}">
                <div class="modal-body">
                    <div class="workflow-settings">
                        <section class="workflow-setting-group">
                            <h4>Admin Workflow</h4>
                            <p>These options affect the admin live notification alert and appointment approval only.</p>
                            <div class="switch-list">
                                <div class="switch-item">
                                    <input type="checkbox" name="admin_live_notifications" id="adminLiveNotifications" {{ $settings->admin_live_notifications !== false ? 'checked' : '' }}>
                                    <label for="adminLiveNotifications">Enable Admin Live Notifications</label>
                                </div>
                            </div>
                        </section>

                        <section class="workflow-setting-group">
                            <h4>Student Assistant Admin Access</h4>
                            <p>Admin Workspace access follows the clinic operating days and hours: <strong>{{ app(\App\Services\ClinicWorkflowService::class)->clinicScheduleLabel() }}</strong>.</p>
                            <a href="{{ route('admin.settings.clinic') }}" class="btn-secondary">Manage Clinic Schedule</a>
                        </section>

                        <section class="workflow-setting-group">
                            <h4>Appointment Reminder Timing</h4>
                            <p>Choose when the system should send a reminder to the student's Notifications before an approved appointment.</p>
                            <div class="field-grid">
                                <div class="field">
                                    <label for="appointmentReminderHours">Reminder Timing</label>
                                    <select id="appointmentReminderHours" name="appointment_reminder_hours">
                                        @foreach($reminderLabels as $hours => $label)
                                            <option value="{{ $hours }}" {{ (int) old('appointment_reminder_hours', $settings->appointment_reminder_hours ?? 24) === $hours ? 'selected' : '' }}>{{ $label }}</option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                        </section>

                        <section class="workflow-setting-group">
                            <h4>Temporary Clinic Closure / Availability Notice</h4>
                            <p>Students may continue browsing records. Saving an enabled closure cancels Pending and Approved appointments inside the selected period, notifies affected students to rebook, and blocks those time slots.</p>
                            <div class="switch-item">
                                <input type="checkbox" name="clinic_closure_enabled" id="clinicClosureEnabled" {{ old('clinic_closure_enabled', $settings->clinic_closure_enabled) ? 'checked' : '' }}>
                                <label for="clinicClosureEnabled">Temporarily close appointment booking</label>
                            </div>

                            <div class="workflow-closure-fields">
                                <div class="field-grid two">
                                    <div class="field">
                                        <label for="clinicClosureStartsAt">Closure Starts</label>
                                        <input type="datetime-local" id="clinicClosureStartsAt" name="clinic_closure_starts_at" value="{{ old('clinic_closure_starts_at', optional($settings->clinic_closure_starts_at)->format('Y-m-d\\TH:i')) }}">
                                    </div>
                                    <div class="field">
                                        <label for="clinicClosureEndsAt">Expected Reopening</label>
                                        <input type="datetime-local" id="clinicClosureEndsAt" name="clinic_closure_ends_at" value="{{ old('clinic_closure_ends_at', optional($settings->clinic_closure_ends_at)->format('Y-m-d\\TH:i')) }}">
                                    </div>
                                </div>
                                <div class="field-grid two">
                                    <div class="field">
                                        <label for="clinicClosureReason">Reason</label>
                                        <select id="clinicClosureReason" name="clinic_closure_reason">
                                            @foreach(['Staff Meeting', 'Official Clinic Activity', 'Emergency', 'Early Closure', 'Other'] as $reason)
                                                <option value="{{ $reason }}" {{ old('clinic_closure_reason', $settings->clinic_closure_reason) === $reason ? 'selected' : '' }}>{{ $reason }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                    <div class="field">
                                        <label>Closure Duration</label>
                                        <output class="workflow-duration-output" id="clinicClosureDuration">Select start and reopening times</output>
                                    </div>
                                </div>
                                <div>
                                    <label for="clinicClosureMessage" class="workflow-message-label">Public Notice Message</label>
                                    <textarea id="clinicClosureMessage" name="clinic_closure_message" maxlength="500" placeholder="Example: The clinic will close early today due to an official staff meeting. Affected appointments must be rebooked after reopening.">{{ old('clinic_closure_message', $settings->clinic_closure_message) }}</textarea>
                                </div>
                            </div>

                            <span class="workflow-status {{ $closureIsCurrentlyActive ? 'is-active' : '' }}">
                                {{ $closureIsCurrentlyActive ? 'Closure notice is currently active' : 'Clinic booking is currently available' }}
                            </span>
                        </section>
                    </div>
                </div>
                <div class="modal-actions">
                    <button type="button" class="btn-cancel" onclick="closeSettingsModal('workflowSettingsModal')">Cancel</button>
                    <button type="submit" class="btn-save">Save System Settings</button>
                </div>
            </form>
        </div>
    </div>
    @endif

    @if($canAccessSetting('settings.clinic'))
    <div id="clinicInfoModal" class="modal-overlay">
        <div class="modal-box">
            <div class="modal-head">
                <div class="modal-head-main">
                    <div class="modal-head-badge">CI</div>
                    <div class="modal-head-copy">
                        <h3>Edit Clinic Information</h3>
                        <p>Update the clinic identity details shown across the system.</p>
                    </div>
                </div>
                <button type="button" class="modal-head-close" onclick="closeSettingsModal('clinicInfoModal')" aria-label="Close">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round"><path d="M18 6 6 18M6 6l12 12"/></svg>
                </button>
            </div>
            <form action="{{ url('/admin/settings/update') }}" method="POST">
                @csrf @method('PUT')
                <div class="modal-body">
                    <div class="field-grid">
                        <div class="field">
                            <label>Clinic Name</label>
                            <input type="text" name="clinic_name" value="{{ $settings->clinic_name }}" placeholder="Clinic name">
                        </div>
                        <div class="field">
                            <label>Location</label>
                            <input type="text" name="clinic_location" value="{{ $settings->clinic_location }}" placeholder="Clinic location">
                        </div>
                    </div>
                </div>
                <div class="modal-actions">
                    <button type="button" class="btn-cancel" onclick="closeSettingsModal('clinicInfoModal')">Cancel</button>
                    <button type="submit" class="btn-save">Save Clinic Information</button>
                </div>
            </form>
        </div>
    </div>

    <div id="clinicHoursModal" class="modal-overlay">
        <div class="modal-box">
            <div class="modal-head">
                <div class="modal-head-main">
                    <div class="modal-head-badge">CH</div>
                    <div class="modal-head-copy">
                        <h3>Edit Clinic Hours</h3>
                        <p>Update the daily opening and closing schedule for the clinic.</p>
                    </div>
                </div>
                <button type="button" class="modal-head-close" onclick="closeSettingsModal('clinicHoursModal')" aria-label="Close">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round"><path d="M18 6 6 18M6 6l12 12"/></svg>
                </button>
            </div>
            <form action="{{ url('/admin/settings/update') }}" method="POST">
                @csrf @method('PUT')
                <div class="modal-body">
                    <div class="field-grid two">
                        <div class="field">
                            <label>Opening Time</label>
                            <input type="time" name="open_time" value="{{ $settings->open_time }}">
                        </div>
                        <div class="field">
                            <label>Closing Time</label>
                            <input type="time" name="close_time" value="{{ $settings->close_time }}">
                        </div>
                    </div>
                </div>
                <div class="modal-actions">
                    <button type="button" class="btn-cancel" onclick="closeSettingsModal('clinicHoursModal')">Cancel</button>
                    <button type="submit" class="btn-save">Save Clinic Hours</button>
                </div>
            </form>
        </div>
    </div>
    @endif

    @if($canAccessSetting('settings.personal'))
    <div id="profileModal" class="modal-overlay">
        <div class="modal-box">
            <div class="modal-head">
                <div class="modal-head-main">
                    <div class="modal-head-badge">PR</div>
                    <div class="modal-head-copy">
                        <h3>Edit Profile</h3>
                        <p>Keep your admin identity and clinic contact details aligned with the hub record.</p>
                    </div>
                </div>
                <button type="button" class="modal-head-close" onclick="closeProfileModal()" aria-label="Close">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round"><path d="M18 6 6 18M6 6l12 12"/></svg>
                </button>
            </div>

            <form action="{{ url('/admin/profile/update') }}" method="POST">
                @csrf @method('PUT')
                <div class="modal-body">
                    <section class="modal-section">
                        <div class="modal-section-kicker">Profile Details</div>
                        <h4 class="modal-section-title">Personal Information</h4>
                        <p class="modal-section-copy">Update the main identity details shown in the CMS Admin Profile card.</p>

                        <div class="field-grid three">
                            <div class="field">
                                <label>Admin ID</label>
                                <input type="text" value="{{ !empty($cmsProfile['admin_id']) ? str_pad((string) $cmsProfile['admin_id'], 3, '0', STR_PAD_LEFT) : 'N/A' }}" disabled>
                            </div>
                            <div class="field">
                                <label>First Name</label>
                                <input type="text" name="first_name" value="{{ old('first_name', $cmsProfile['first_name'] ?? '') }}" required>
                            </div>
                            <div class="field">
                                <label>Middle Name</label>
                                <input type="text" name="middle_name" value="{{ old('middle_name', $cmsProfile['middle_name'] ?? '') }}" placeholder="Middle name">
                            </div>
                            <div class="field">
                                <label>Last Name</label>
                                <input type="text" name="last_name" value="{{ old('last_name', $cmsProfile['last_name'] ?? '') }}" required>
                            </div>
                            <div class="field">
                                <label>Suffix Name</label>
                                <input type="text" name="suffix_name" value="{{ old('suffix_name', $cmsProfile['suffix_name'] ?? '') }}" placeholder="Jr., Sr., III">
                            </div>
                            <div class="field">
                                <label>Email</label>
                                <input type="email" name="email" value="{{ old('email', $cmsProfile['email'] ?? ($admin->email ?? '')) }}" required>
                            </div>
                        </div>

                        <div class="field-grid two" style="margin-top:16px;">
                            <div class="field">
                                <label>Birthday</label>
                                <input type="date" id="cmsBirthdayInput" name="birthday" value="{{ old('birthday', $cmsProfile['birthday'] ?? '') }}">
                            </div>
                            <div class="field">
                                <label>Age</label>
                                <input type="text" id="cmsAgeInput" value="{{ old('age', $cmsProfile['age'] ?? '') }}" readonly>
                                <p class="field-help">Auto-calculated from birthday.</p>
                            </div>
                        </div>

                        <div class="field-grid two">
                            <div class="field">
                                <label>Gender</label>
                                <input type="text" name="gender" value="{{ old('gender', $cmsProfile['gender'] ?? '') }}" placeholder="Gender">
                            </div>
                            <div class="field">
                                <label>Civil Status</label>
                                <input type="text" name="civil_status" value="{{ old('civil_status', $cmsProfile['civil_status'] ?? '') }}" placeholder="Civil status">
                            </div>
                        </div>
                    </section>

                    <section class="modal-section">
                        <div class="modal-section-kicker">Clinic Access</div>
                        <h4 class="modal-section-title">Contact, Office, and Security</h4>
                        <p class="modal-section-copy">Keep clinic-facing contact details and login-related settings in one place.</p>

                        <div class="field-grid two">
                            <div class="field">
                                <label>Address</label>
                                <input type="text" name="address" value="{{ old('address', $cmsProfile['address'] ?? '') }}" placeholder="Complete address">
                            </div>
                            <div class="field">
                                <label>Contact Number</label>
                                <input type="text" name="contact_number" value="{{ old('contact_number', $cmsProfile['contact_number'] ?? '') }}" placeholder="Contact number">
                            </div>
                        </div>

                        <div class="field-grid two">
                            <div class="field">
                                <label>Emergency Contact Person</label>
                                <input type="text" name="emergency_contact_person" value="{{ old('emergency_contact_person', $cmsProfile['emergency_contact_person'] ?? '') }}" placeholder="Emergency contact person">
                            </div>
                            <div class="field">
                                <label>Emergency Contact No.</label>
                                <input type="text" name="emergency_contact_no" value="{{ old('emergency_contact_no', $cmsProfile['emergency_contact_no'] ?? ($cmsProfile['contact_number'] ?? '')) }}" placeholder="Emergency contact number">
                            </div>
                        </div>

                        <div class="field-grid two">
                            <div class="field">
                                <label>Office</label>
                                <input type="text" name="office" value="{{ old('office', $cmsProfile['office'] ?? 'Admission Office') }}" placeholder="Office">
                            </div>
                            <div class="field">
                                <label>Status</label>
                                <select name="status">
                                    <option value="active" {{ old('status', $cmsProfile['status'] ?? 'active') === 'active' ? 'selected' : '' }}>Active</option>
                                    <option value="inactive" {{ old('status', $cmsProfile['status'] ?? 'active') === 'inactive' ? 'selected' : '' }}>Inactive</option>
                                </select>
                            </div>
                        </div>

                    </section>
                </div>

                <div class="modal-actions">
                    <button type="button" class="btn-cancel" onclick="closeProfileModal()">Cancel</button>
                    <button type="submit" class="btn-save">Save Profile</button>
                </div>
            </form>
        </div>
    </div>
    @endif
</div>
@endsection

@push('scripts')
<script>
    function openSettingsModal(id) {
        const modal = document.getElementById(id);
        if (modal) {
            modal.style.display = 'flex';
            document.body.classList.add('settings-modal-open');
        }
    }

    function closeSettingsModal(id) {
        const modal = document.getElementById(id);
        if (modal) {
            modal.style.display = 'none';
        }
        if (!document.querySelector('.modal-overlay[style*="display: flex"]')) {
            document.body.classList.remove('settings-modal-open');
        }
    }

    function openProfileModal() {
        document.getElementById('profileModal').style.display = 'flex';
        document.body.classList.add('settings-modal-open');
        syncCmsAge();
    }

    function closeProfileModal() {
        document.getElementById('profileModal').style.display = 'none';
        if (!document.querySelector('.modal-overlay[style*="display: flex"]')) {
            document.body.classList.remove('settings-modal-open');
        }
    }

    window.addEventListener('click', function (e) {
        if (e.target === document.getElementById('profileModal')) {
            closeProfileModal();
        }
        if (e.target === document.getElementById('clinicInfoModal')) {
            closeSettingsModal('clinicInfoModal');
        }
        if (e.target === document.getElementById('clinicHoursModal')) {
            closeSettingsModal('clinicHoursModal');
        }
        if (e.target === document.getElementById('workflowSettingsModal')) {
            closeSettingsModal('workflowSettingsModal');
        }
    });

    document.addEventListener('keydown', function (event) {
        if (event.key !== 'Escape') return;

        const openModal = Array.from(document.querySelectorAll('.modal-overlay'))
            .find(function (modal) {
                return modal.style.display === 'flex';
            });

        if (!openModal) return;
        if (openModal.id === 'profileModal') {
            closeProfileModal();
            return;
        }
        closeSettingsModal(openModal.id);
    });

    function syncClosureDuration() {
        const startsInput = document.getElementById('clinicClosureStartsAt');
        const endsInput = document.getElementById('clinicClosureEndsAt');
        const durationOutput = document.getElementById('clinicClosureDuration');

        if (!startsInput || !endsInput || !durationOutput) return;
        if (!startsInput.value || !endsInput.value) {
            durationOutput.textContent = 'Select start and reopening times';
            return;
        }

        const startsAt = new Date(startsInput.value);
        const endsAt = new Date(endsInput.value);
        const durationMinutes = Math.round((endsAt.getTime() - startsAt.getTime()) / 60000);

        if (!Number.isFinite(durationMinutes) || durationMinutes <= 0) {
            durationOutput.textContent = 'Reopening must be later than closure start';
            return;
        }

        const days = Math.floor(durationMinutes / 1440);
        const hours = Math.floor((durationMinutes % 1440) / 60);
        const minutes = durationMinutes % 60;
        const parts = [];

        if (days > 0) parts.push(days + (days === 1 ? ' day' : ' days'));
        if (hours > 0) parts.push(hours + (hours === 1 ? ' hour' : ' hours'));
        if (minutes > 0) parts.push(minutes + (minutes === 1 ? ' minute' : ' minutes'));

        durationOutput.textContent = parts.join(' ') || 'Less than 1 minute';
    }

    function syncClosureRequirements() {
        const closureToggle = document.getElementById('clinicClosureEnabled');
        const startsInput = document.getElementById('clinicClosureStartsAt');
        const endsInput = document.getElementById('clinicClosureEndsAt');

        if (!closureToggle || !startsInput || !endsInput) return;
        startsInput.required = closureToggle.checked;
        endsInput.required = closureToggle.checked;
    }

    function syncCmsAge() {
        const birthdayInput = document.getElementById('cmsBirthdayInput');
        const ageInput = document.getElementById('cmsAgeInput');

        if (!birthdayInput || !ageInput) return;

        if (!birthdayInput.value) {
            ageInput.value = '';
            return;
        }

        const birthday = new Date(birthdayInput.value + 'T00:00:00');
        if (Number.isNaN(birthday.getTime())) {
            ageInput.value = '';
            return;
        }

        const today = new Date();
        let age = today.getFullYear() - birthday.getFullYear();
        const monthDiff = today.getMonth() - birthday.getMonth();
        if (monthDiff < 0 || (monthDiff === 0 && today.getDate() < birthday.getDate())) {
            age -= 1;
        }
        ageInput.value = age >= 0 ? age : '';
    }

    document.addEventListener('DOMContentLoaded', function () {
        const birthdayInput = document.getElementById('cmsBirthdayInput');
        if (birthdayInput) {
            birthdayInput.addEventListener('change', syncCmsAge);
            syncCmsAge();
        }

        const closureStartsInput = document.getElementById('clinicClosureStartsAt');
        const closureEndsInput = document.getElementById('clinicClosureEndsAt');
        const closureToggle = document.getElementById('clinicClosureEnabled');
        if (closureStartsInput && closureEndsInput) {
            closureStartsInput.addEventListener('change', syncClosureDuration);
            closureEndsInput.addEventListener('change', syncClosureDuration);
            syncClosureDuration();
        }
        if (closureToggle) {
            closureToggle.addEventListener('change', syncClosureRequirements);
            syncClosureRequirements();
        }

        @if($errors->any() && old('preferences_form'))
            openSettingsModal('workflowSettingsModal');
        @endif
    });
</script>
@endpush
