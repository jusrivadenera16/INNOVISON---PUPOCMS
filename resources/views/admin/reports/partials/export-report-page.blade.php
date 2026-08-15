@section('title', $title ?? 'Export Report')

@push('styles')
<style>
    .export-page-shell {
        width: min(1180px, calc(100% - 32px));
        margin: 24px auto;
        color: #0f172a;
    }

    .export-page-frame,
    .export-preview-panel {
        position: relative;
        overflow: hidden;
        border-radius: 18px;
        border: 1px solid rgba(127, 29, 45, .18);
        background: #ffffff;
        padding: 24px;
        box-shadow: 0 18px 42px rgba(15, 23, 42, .08);
    }

    .export-page-frame::before,
    .export-preview-panel::before {
        content: "";
        position: absolute;
        top: 0;
        left: 18px;
        right: 18px;
        height: 5px;
        border-radius: 0 0 999px 999px;
        background: #70131B;
    }

    .export-page-header {
        display: flex;
        justify-content: space-between;
        align-items: flex-start;
        gap: 18px;
        margin-bottom: 22px;
    }

    .export-page-kicker {
        margin: 0 0 8px;
        color: #70131B;
        font-size: 12px;
        font-weight: 950;
        letter-spacing: .08em;
        text-transform: uppercase;
    }

    .export-page-title {
        margin: 0;
        color: #0f172a;
        font-size: 26px;
        line-height: 1.1;
        font-weight: 950;
    }

    .export-page-copy {
        margin: 8px 0 0;
        max-width: 760px;
        color: #334155;
        font-size: 14px;
        font-weight: 650;
        line-height: 1.5;
    }

    .export-page-back {
        min-height: 42px;
        padding: 0 16px;
        border-radius: 10px;
        border: 1px solid #70131B;
        background: #70131B;
        color: #ffffff;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        font-size: 13px;
        font-weight: 900;
        text-decoration: none;
        white-space: nowrap;
        transition: background .18s ease, color .18s ease, border-color .18s ease, transform .18s ease;
    }

    .export-page-back:visited {
        color: #ffffff !important;
    }

    .export-page-back,
    .export-page-back:link,
    .export-page-back:active {
        color: #ffffff !important;
    }

    .export-page-back:hover,
    .export-page-back:focus-visible {
        background: #facc15;
        border-color: #facc15;
        color: #70131B !important;
        transform: translateY(-1px);
        outline: none;
    }

    .export-action-grid {
        display: grid;
        grid-template-columns: repeat(4, minmax(0, 1fr));
        gap: 12px;
    }

    .export-action-card {
        position: relative;
        min-height: 124px;
        display: grid;
        grid-template-columns: 44px minmax(0, 1fr);
        align-items: center;
        gap: 14px;
        overflow: hidden;
        padding: 16px 16px;
        border-radius: 12px;
        border: 1px solid #e5e7eb;
        background: #ffffff;
        color: #0f172a;
        text-decoration: none;
        box-shadow: 0 12px 26px rgba(15, 23, 42, .06);
        transition: transform .22s ease, border-color .22s ease, box-shadow .22s ease, background .22s ease, color .22s ease;
    }

    .export-action-card > * {
        position: relative;
        z-index: 1;
    }

    .export-action-card::after {
        content: "";
        position: absolute;
        top: -42%;
        bottom: -42%;
        left: -125%;
        width: 42%;
        opacity: 0;
        background: linear-gradient(105deg, rgba(255,255,255,0) 0%, rgba(255,248,196,.42) 48%, rgba(255,255,255,0) 100%);
        transform: translateX(0) skewX(-18deg);
        pointer-events: none;
    }

    .export-action-card:hover,
    .export-action-card:focus-within {
        transform: translateY(-3px);
        border-color: rgba(127, 29, 45, .30);
        box-shadow: 0 16px 30px rgba(112, 19, 27, .12);
    }

    .export-action-card:hover::after,
    .export-action-card:focus-within::after {
        animation: exportCardSweep .92s ease both;
    }

    @keyframes exportCardSweep {
        0% { opacity: 0; transform: translateX(0) skewX(-18deg); }
        18%, 72% { opacity: .72; }
        100% { opacity: 0; transform: translateX(720%) skewX(-18deg); }
    }

    .export-card-icon {
        width: 44px;
        height: 44px;
        display: grid;
        place-items: center;
        border-radius: 999px;
        color: #ef4444;
        background: #fee2e2;
        border: 1px solid rgba(127, 29, 45, .08);
    }

    .export-card-icon svg {
        width: 22px;
        height: 22px;
    }

    .export-action-card:hover .export-card-icon,
    .export-action-card:focus-within .export-card-icon {
        color: #70131B;
        background: rgba(112, 19, 27, .12);
        border-color: rgba(112, 19, 27, .24);
    }

    .export-card-label {
        display: block;
        margin: 0 0 7px;
        color: #0f172a;
        font-size: 12px;
        font-weight: 950;
        letter-spacing: .05em;
        text-transform: uppercase;
    }

    .export-card-value {
        display: block;
        margin: 0;
        color: #0f172a;
        font-size: 21px;
        font-weight: 950;
        line-height: 1.05;
    }

    .export-card-copy {
        display: none;
        margin: 0;
        color: #0f172a;
        font-size: 12px;
        font-weight: 700;
        line-height: 1.35;
    }

    .export-action-card.is-filter .export-card-copy,
    .export-action-card.is-export .export-card-copy {
        display: block;
        margin-top: 5px;
    }

    .export-action-card:hover .export-card-label,
    .export-action-card:hover .export-card-value,
    .export-action-card:hover .export-card-copy,
    .export-action-card:focus-within .export-card-label,
    .export-action-card:focus-within .export-card-value,
    .export-action-card:focus-within .export-card-copy {
        color: inherit;
    }

    .export-action-card.is-filter,
    .export-action-card.is-export {
        grid-template-columns: 44px minmax(0, 1fr) 44px;
        min-height: 124px;
        background: #8f1827;
        color: #ffffff;
        border-color: rgba(250, 204, 21, .72);
        box-shadow: 0 16px 30px rgba(112, 19, 27, .16);
        cursor: pointer;
        text-align: left;
        font: inherit;
        width: 100%;
    }

    .export-action-grid-mar .export-action-card,
    .export-action-grid-appointments .export-action-card,
    .export-action-grid-audit-trail .export-action-card,
    .export-action-grid-health-forms .export-action-card {
        min-height: 104px;
        padding-top: 14px;
        padding-bottom: 14px;
    }

    .export-action-card.is-filter .export-card-icon,
    .export-action-card.is-export .export-card-icon {
        color: #ffffff;
        background: rgba(255, 255, 255, .14);
        border-color: rgba(255, 255, 255, .14);
    }

    .export-action-card.is-filter .export-card-label,
    .export-action-card.is-filter .export-card-value,
    .export-action-card.is-filter .export-card-copy,
    .export-action-card.is-export .export-card-label,
    .export-action-card.is-export .export-card-value,
    .export-action-card.is-export .export-card-copy {
        color: #ffffff;
    }

    .export-action-card.is-filter:hover,
    .export-action-card.is-filter:focus-visible,
    .export-action-card.is-export:hover,
    .export-action-card.is-export:focus-within {
        background: #facc15;
        border-color: #facc15;
        color: #70131B;
        outline: none;
    }

    .export-action-card.is-filter:hover .export-card-icon,
    .export-action-card.is-filter:focus-visible .export-card-icon,
    .export-action-card.is-export:hover .export-card-icon,
    .export-action-card.is-export:focus-within .export-card-icon {
        color: #70131B;
        background: rgba(112, 19, 27, .14);
        border-color: rgba(112, 19, 27, .20);
    }

    .export-action-card.is-filter:hover .export-card-label,
    .export-action-card.is-filter:hover .export-card-value,
    .export-action-card.is-filter:hover .export-card-copy,
    .export-action-card.is-filter:focus-visible .export-card-label,
    .export-action-card.is-filter:focus-visible .export-card-value,
    .export-action-card.is-filter:focus-visible .export-card-copy,
    .export-action-card.is-export:hover .export-card-label,
    .export-action-card.is-export:hover .export-card-value,
    .export-action-card.is-export:hover .export-card-copy,
    .export-action-card.is-export:focus-within .export-card-label,
    .export-action-card.is-export:focus-within .export-card-value,
    .export-action-card.is-export:focus-within .export-card-copy {
        color: #70131B;
    }

    .export-card-arrow {
        width: 38px;
        height: 38px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        border-radius: 10px;
        border: 1px solid #facc15;
        background: rgba(255, 255, 255, .08);
        color: #ffffff;
        font-size: 18px;
        font-weight: 900;
    }

    .export-action-card.is-filter:hover .export-card-arrow,
    .export-action-card.is-filter:focus-visible .export-card-arrow,
    .export-action-card.is-export:hover .export-card-arrow,
    .export-action-card.is-export:focus-within .export-card-arrow {
        background: rgba(112, 19, 27, .10);
        border-color: #70131B;
        color: #70131B;
    }

    .export-card-link {
        min-height: 34px;
        width: auto;
        border-radius: 10px;
        border: 1px solid #facc15;
        background: rgba(255,255,255,.12);
        color: #ffffff;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        padding: 0 11px;
        font: inherit;
        font-size: 12px;
        font-weight: 950;
        text-decoration: none;
        cursor: pointer;
        transition: background .18s ease, color .18s ease, border-color .18s ease;
    }

    .export-action-card:hover .export-card-link,
    .export-action-card:focus-within .export-card-link {
        background: rgba(112, 19, 27, .10);
        border-color: #70131B;
        color: #70131B;
    }

    .export-link-stack {
        grid-column: 3;
        grid-row: 1 / span 2;
        display: flex;
        flex-direction: column;
        align-self: center;
        gap: 8px;
    }

    .health-forms-export-card {
        display: block !important;
        padding: 0 !important;
    }

    .health-forms-export-card > summary {
        min-height: 104px;
        padding: 14px 16px;
        display: grid;
        grid-template-columns: 44px minmax(0, 1fr) 44px;
        align-items: center;
        gap: 14px;
        cursor: pointer;
        list-style: none;
    }

    .health-forms-export-card > summary::-webkit-details-marker {
        display: none;
    }

    .health-forms-export-card[open] > summary {
        display: none;
    }

    .health-forms-export-options {
        min-height: 104px;
        padding: 0 18px;
        display: flex;
        flex-direction: column;
        justify-content: center;
    }

    .health-forms-export-option {
        min-height: 49px;
        display: flex;
        align-items: center;
        justify-content: center;
        border: 0;
        border-radius: 0;
        background: transparent;
        color: #ffffff !important;
        font-size: 14px;
        font-weight: 900;
        text-align: center;
        text-decoration: none;
        transition: color .18s ease, background .18s ease;
    }

    .health-forms-export-option + .health-forms-export-option {
        border-top: 1px solid rgba(255, 255, 255, .34);
    }

    .health-forms-export-option:hover,
    .health-forms-export-option:focus-visible {
        background: rgba(255, 255, 255, .06);
        color: #facc15 !important;
        outline: none;
    }

    .health-forms-export-card[open],
    .health-forms-export-card[open]:hover,
    .health-forms-export-card[open]:focus-within,
    html[data-theme="dark"] .health-forms-export-card[open],
    html[data-theme="dark"] .health-forms-export-card[open]:hover,
    html[data-theme="dark"] .health-forms-export-card[open]:focus-within {
        border-color: rgba(250, 204, 21, .72);
        background: #8f1827;
        color: #ffffff;
    }

    .health-forms-export-card[open]::after {
        display: none;
    }

    .export-filter-backdrop {
        position: fixed;
        inset: 0;
        z-index: 1000;
        display: none;
        align-items: center;
        justify-content: center;
        padding: 32px 18px;
        overflow-y: auto;
        background: rgba(15, 23, 42, .56);
        backdrop-filter: blur(7px);
    }

    .export-filter-backdrop.is-open {
        display: flex;
    }

    .export-filter-modal {
        width: min(760px, 96vw);
        position: relative;
        overflow: hidden;
        display: flex;
        flex-direction: column;
        max-height: calc(100dvh - 64px);
        margin: auto 0;
        border-radius: 22px;
        border: 1px solid rgba(112, 19, 27, .18);
        background: #ffffff;
        box-shadow: 0 24px 70px rgba(15, 23, 42, .24);
        animation: exportFilterIn .2s ease;
    }

    @keyframes exportFilterIn {
        from { opacity: 0; transform: translateY(12px) scale(.98); }
        to { opacity: 1; transform: translateY(0) scale(1); }
    }

    .export-filter-modal-head {
        display: flex;
        align-items: center;
        gap: 14px;
        padding: 24px;
        background: #8f1827;
        color: #ffffff;
    }

    .export-filter-modal-icon {
        width: 52px;
        height: 52px;
        display: grid;
        place-items: center;
        border-radius: 14px;
        background: rgba(255,255,255,.14);
        border: 1px solid rgba(255,255,255,.20);
        color: #facc15;
        flex: 0 0 auto;
    }

    .export-filter-modal-icon svg {
        width: 26px;
        height: 26px;
    }

    .export-filter-modal-title {
        margin: 0;
        color: #ffffff !important;
        font-size: 22px;
        font-weight: 950;
    }

    .export-filter-modal-copy {
        margin: 4px 0 0;
        color: rgba(255,255,255,.86) !important;
        font-size: 13px;
        font-weight: 700;
    }

    .export-filter-modal-close {
        position: absolute;
        top: 18px;
        right: 18px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        overflow: hidden;
        width: 42px;
        height: 42px;
        padding: 0;
        border-radius: 999px;
        border: 1px solid rgba(255,255,255,.20);
        background: rgba(255,255,255,.12);
        color: #ffffff;
        font-size: 24px;
        line-height: 1;
        cursor: pointer;
        transition: background .18s ease, color .18s ease, border-color .18s ease, transform .18s ease;
    }

    .export-filter-modal-close span {
        position: relative;
        z-index: 1;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        width: 100%;
        height: 100%;
        transform: translateY(-1px);
    }

    .export-filter-modal-close::after {
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

    .export-filter-modal-close:hover,
    .export-filter-modal-close:focus-visible {
        background: #facc15;
        border-color: #facc15;
        color: #70131B;
        outline: none;
        transform: translateY(-1px);
    }

    .export-filter-modal-close:hover::after,
    .export-filter-modal-close:focus-visible::after {
        left: 128%;
    }

    .export-filter-form {
        display: grid;
        grid-template-columns: repeat(2, minmax(0, 1fr));
        gap: 16px;
        padding: 24px;
        min-height: 0;
        overflow-y: auto;
    }

    .export-filter-field {
        display: grid;
        gap: 7px;
    }

    .export-filter-field label {
        color: #0f172a;
        font-size: 11px;
        font-weight: 900;
        letter-spacing: .04em;
        text-transform: uppercase;
    }

    .export-date-input,
    .export-filter-select {
        width: 100%;
        min-height: 46px;
        border-radius: 12px;
        border: 1px solid rgba(112, 19, 27, .22);
        background: #ffffff;
        color: #0f172a;
        padding: 0 13px;
        font: inherit;
        font-size: 13px;
        font-weight: 800;
    }

    .export-date-input:focus,
    .export-filter-select:focus {
        border-color: #70131B;
        box-shadow: 0 0 0 3px rgba(112, 19, 27, .10);
        outline: none;
    }

    .export-filter-select option {
        background: #ffffff;
        color: #0f172a;
        font-size: 13px;
        font-weight: 800;
    }

    .export-filter-select option:checked {
        background: #70131B;
        color: #facc15;
    }

    .export-filter-select option:hover {
        background: #70131B;
        color: #ffffff;
    }

    .export-filter-select-wrap {
        position: relative;
    }

    .export-filter-custom-source {
        position: absolute !important;
        width: 1px !important;
        height: 1px !important;
        opacity: 0 !important;
        pointer-events: none !important;
    }

    .export-filter-custom-trigger {
        width: 100%;
        min-height: 46px;
        border-radius: 12px;
        border: 1px solid rgba(112, 19, 27, .22);
        background: #fffdf8;
        color: #0f172a;
        padding: 0 44px 0 13px;
        font: inherit;
        font-size: 13px;
        font-weight: 800;
        text-align: left;
        position: relative;
        cursor: pointer;
        box-shadow: 0 8px 16px rgba(112, 19, 27, .05);
        transition: border-color .18s ease, box-shadow .18s ease, transform .18s ease;
    }

    .export-filter-custom-trigger::after {
        content: "";
        position: absolute;
        right: 16px;
        top: 50%;
        width: 10px;
        height: 10px;
        border-right: 2px solid #70131B;
        border-bottom: 2px solid #70131B;
        transform: translateY(-65%) rotate(45deg);
        transition: transform .18s ease;
        pointer-events: none;
    }

    .export-filter-select-wrap.is-open .export-filter-custom-trigger {
        border-color: #8f0012;
        box-shadow: 0 0 0 3px rgba(112, 19, 27, .08), 0 10px 22px rgba(112, 19, 27, .10);
        transform: translateY(-1px);
    }

    .export-filter-select-wrap.is-open .export-filter-custom-trigger::after {
        transform: translateY(-35%) rotate(225deg);
    }

    .export-filter-custom-menu {
        position: absolute;
        z-index: 30;
        top: calc(100% + 8px);
        left: 0;
        right: 0;
        display: none;
        padding: 8px;
        border: 1px solid #f3c7c7;
        border-radius: 14px;
        background: #ffffff;
        box-shadow: 0 18px 32px rgba(112, 19, 27, .14);
        max-height: 180px;
        overflow-y: auto;
    }

    .export-filter-select-wrap.is-open .export-filter-custom-menu {
        display: grid;
        gap: 6px;
    }

    .export-filter-custom-option {
        border: 0;
        border-radius: 999px;
        background: #ffffff;
        color: #111827;
        min-height: 34px;
        padding: 7px 14px;
        font: inherit;
        font-size: 12px;
        font-weight: 800;
        text-align: left;
        cursor: pointer;
        transition: background .18s ease, color .18s ease, transform .18s ease;
    }

    .export-filter-custom-option:hover,
    .export-filter-custom-option:focus {
        background: #8f0012;
        color: #ffffff;
        outline: 0;
    }

    .export-filter-custom-option.is-selected {
        background: #8f0012;
        color: #facc15;
    }

    .export-filter-field.is-wide {
        grid-column: 1 / -1;
    }

    .export-condition-keyword-head {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 10px;
    }

    .export-condition-suggest-toggle {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        min-height: 30px;
        padding: 0 12px;
        border-radius: 8px;
        border: 1px solid rgba(112, 19, 27, .18);
        background: #fff7ed;
        color: #70131B;
        font: inherit;
        font-size: 10.5px;
        font-weight: 900;
        letter-spacing: .03em;
        text-transform: uppercase;
        cursor: pointer;
        transition: background .18s ease, color .18s ease, border-color .18s ease, transform .18s ease;
    }

    .export-condition-suggest-toggle:hover,
    .export-condition-suggest-toggle:focus-visible {
        background: #facc15;
        border-color: #facc15;
        color: #70131B;
        outline: none;
        transform: translateY(-1px);
    }

    .export-condition-suggestions {
        display: none;
        flex-wrap: wrap;
        gap: 8px;
        margin-top: 3px;
        padding: 12px;
        border-radius: 12px;
        border: 1px solid rgba(112, 19, 27, .12);
        background: #fffdf8;
    }

    .export-condition-suggestions.is-open {
        display: flex;
    }

    .export-condition-suggestion {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        min-height: 32px;
        padding: 7px 12px;
        border-radius: 8px;
        border: 1px solid rgba(112, 19, 27, .16);
        background: #ffffff;
        color: #70131B;
        font: inherit;
        font-size: 12px;
        font-weight: 800;
        cursor: pointer;
        transition: background .18s ease, color .18s ease, border-color .18s ease, transform .18s ease;
    }

    .export-condition-suggestion:hover,
    .export-condition-suggestion:focus-visible,
    .export-condition-suggestion.is-selected {
        background: #70131B;
        border-color: #70131B;
        color: #ffffff;
        outline: none;
        transform: translateY(-1px);
    }

    .export-filter-checks {
        grid-column: 1 / -1;
        display: grid;
        grid-template-columns: repeat(3, minmax(0, 1fr));
        gap: 10px;
    }

    .export-filter-option-section {
        grid-column: 1 / -1;
        display: grid;
        gap: 8px;
    }

    .export-filter-option-title {
        margin: 0;
        color: #0f172a;
        font-size: 12px;
        font-weight: 900;
        letter-spacing: .04em;
        text-transform: uppercase;
    }

    .export-filter-checks.is-single {
        grid-template-columns: minmax(0, 1fr);
    }

    .export-filter-check {
        min-height: 44px;
        display: flex;
        align-items: center;
        gap: 9px;
        padding: 10px 12px;
        border-radius: 12px;
        border: 1px solid rgba(112, 19, 27, .14);
        background: #fff;
        color: #0f172a;
        font-size: 12px;
        font-weight: 900;
        text-transform: uppercase;
    }

    .export-filter-check input {
        width: 16px;
        height: 16px;
        accent-color: #70131B;
    }

    .export-filter-actions {
        grid-column: 1 / -1;
        display: flex;
        justify-content: flex-end;
        gap: 10px;
    }

    .export-filter-cancel,
    .export-filter-submit {
        position: relative;
        overflow: hidden;
        min-height: 44px;
        min-width: 130px;
        border-radius: 10px;
        padding: 0 18px;
        font: inherit;
        font-size: 13px;
        font-weight: 950;
        cursor: pointer;
        transition: background .18s ease, color .18s ease, border-color .18s ease;
    }

    .export-filter-cancel::after,
    .export-filter-submit::after {
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

    .export-filter-cancel:hover::after,
    .export-filter-cancel:focus-visible::after,
    .export-filter-submit:hover::after,
    .export-filter-submit:focus-visible::after {
        left: 128%;
    }

    .export-filter-cancel {
        border: 1px solid rgba(148, 163, 184, .45);
        background: #f8fafc;
        color: #475569;
    }

    .export-filter-cancel:hover,
    .export-filter-cancel:focus-visible {
        background: #facc15;
        border-color: #facc15;
        color: #70131B;
        outline: none;
    }

    .export-filter-submit {
        border: 1px solid #70131B;
        background: #70131B;
        color: #ffffff;
    }

    .export-filter-submit:hover,
    .export-filter-submit:focus-visible {
        background: #facc15;
        border-color: #facc15;
        color: #70131B;
        outline: none;
    }

    .export-preview-panel {
        margin-top: 18px;
    }

    .export-preview-title {
        margin: 0;
        color: #0f172a;
        font-size: 20px;
        font-weight: 950;
    }

    .export-preview-copy {
        margin: 6px 0 18px;
        color: #64748b;
        font-size: 13px;
        font-weight: 700;
    }

    .export-preview-table-wrap {
        overflow-x: auto;
        border-radius: 14px;
        border: 1px solid rgba(112, 19, 27, .12);
    }

    .export-preview-table {
        width: 100%;
        min-width: 720px;
        border-collapse: collapse;
    }

    .export-preview-table th {
        background: #70131B;
        color: #ffffff;
        padding: 13px 14px;
        text-align: left;
        font-size: 12px;
        font-weight: 950;
        letter-spacing: .04em;
        text-transform: uppercase;
    }

    .export-preview-table td {
        background: #fff1f2;
        color: #111827;
        padding: 12px 14px;
        border-top: 1px solid rgba(112, 19, 27, .10);
        font-size: 13px;
        font-weight: 700;
    }

    .export-preview-empty {
        padding: 28px;
        text-align: center;
        color: #64748b;
        font-weight: 800;
    }

    html[data-theme="dark"] .export-page-frame,
    html[data-theme="dark"] .export-preview-panel {
        background: rgba(15, 23, 42, .94);
        border-color: rgba(250, 204, 21, .18);
    }

    html[data-theme="dark"] .export-page-title,
    html[data-theme="dark"] .export-preview-title {
        color: #ffffff;
    }

    html[data-theme="dark"] .export-page-copy,
    html[data-theme="dark"] .export-preview-copy {
        color: #cbd5e1;
    }

    html[data-theme="dark"] .export-action-card {
        background: rgba(15, 23, 42, .92);
        border-color: rgba(250, 204, 21, .36);
    }

    html[data-theme="dark"] .export-action-card:not(.is-filter):not(.is-export) .export-card-label,
    html[data-theme="dark"] .export-action-card:not(.is-filter):not(.is-export) .export-card-value,
    html[data-theme="dark"] .export-action-card:not(.is-filter):not(.is-export) .export-card-copy {
        color: #ffffff;
    }

    html[data-theme="dark"] .export-card-icon {
        color: #facc15;
        background: rgba(250, 204, 21, .10);
        border-color: rgba(250, 204, 21, .22);
    }

    html[data-theme="dark"] .export-action-card.is-filter,
    html[data-theme="dark"] .export-action-card.is-export {
        background: #8f1827;
    }

    html[data-theme="dark"] .export-action-card.is-filter:hover,
    html[data-theme="dark"] .export-action-card.is-filter:focus-visible,
    html[data-theme="dark"] .export-action-card.is-export:hover,
    html[data-theme="dark"] .export-action-card.is-export:focus-within {
        background: #facc15;
    }

    html[data-theme="dark"] .health-forms-export-card[open],
    html[data-theme="dark"] .health-forms-export-card[open]:hover,
    html[data-theme="dark"] .health-forms-export-card[open]:focus-within {
        border-color: rgba(250, 204, 21, .72);
        background: #8f1827;
        color: #ffffff;
    }

    html[data-theme="dark"] .export-filter-modal {
        background: rgba(15, 23, 42, .96);
        border-color: rgba(250, 204, 21, .18);
    }

    html[data-theme="dark"] .export-filter-field label {
        color: #ffffff;
    }

    html[data-theme="dark"] .export-date-input,
    html[data-theme="dark"] .export-filter-select,
    html[data-theme="dark"] .export-filter-check {
        background: rgba(17, 24, 39, .94);
        border-color: rgba(250, 204, 21, .18);
        color: #ffffff;
    }

    html[data-theme="dark"] .export-filter-custom-trigger,
    html[data-theme="dark"] .export-filter-custom-menu,
    html[data-theme="dark"] .export-filter-custom-option {
        background: #111827;
        color: #ffffff;
        border-color: rgba(250, 204, 21, .18);
    }

    html[data-theme="dark"] .export-filter-custom-trigger::after {
        border-color: #facc15;
    }

    html[data-theme="dark"] .export-filter-custom-option:hover,
    html[data-theme="dark"] .export-filter-custom-option:focus {
        background: #8f0012;
        color: #ffffff;
    }

    html[data-theme="dark"] .export-filter-custom-option.is-selected {
        background: #8f0012;
        color: #facc15;
    }

    html[data-theme="dark"] .export-condition-suggest-toggle,
    html[data-theme="dark"] .export-condition-suggestions,
    html[data-theme="dark"] .export-condition-suggestion {
        background: #111827;
        color: #ffffff;
        border-color: rgba(250, 204, 21, .18);
    }

    html[data-theme="dark"] .export-condition-suggest-toggle:hover,
    html[data-theme="dark"] .export-condition-suggest-toggle:focus-visible {
        background: #facc15;
        color: #70131B;
        border-color: #facc15;
    }

    html[data-theme="dark"] .export-condition-suggestion:hover,
    html[data-theme="dark"] .export-condition-suggestion:focus-visible,
    html[data-theme="dark"] .export-condition-suggestion.is-selected {
        background: #8f0012;
        color: #ffffff;
        border-color: #8f0012;
    }

    html[data-theme="dark"] .export-filter-option-title {
        color: #ffffff;
    }

    html[data-theme="dark"] .export-preview-table td {
        background: rgba(112, 19, 27, .34);
        color: #ffffff;
    }

    @media (max-width: 980px) {
        .export-action-grid {
            grid-template-columns: repeat(2, minmax(0, 1fr));
        }
    }

    @media (max-width: 640px) {
        .export-page-header {
            flex-direction: column;
        }
        .export-page-back,
        .export-action-grid {
            width: 100%;
        }
        .export-action-grid {
            grid-template-columns: 1fr;
        }
    }
</style>
@endpush

@section('content')
<div class="export-page-shell">
    <section class="export-page-frame">
        <header class="export-page-header">
            <div>
                <p class="export-page-kicker">{{ $kicker }}</p>
                <h1 class="export-page-title">{{ $title }}</h1>
                <p class="export-page-copy">{{ $subtitle }}</p>
            </div>
            <a href="{{ $hubUrl }}" class="export-page-back">&larr; Export Hub</a>
        </header>

        <div class="export-action-grid export-action-grid-{{ $reportType }}">
            @if(($reportType ?? '') === 'inventory')
                <div class="export-action-card">
                    <span class="export-card-icon"><x-outline-icon name="cube" /></span>
                    <span>
                        <span class="export-card-label">Total Items</span>
                        <span class="export-card-value">{{ number_format($previewCount) }}</span>
                    </span>
                </div>

                <button type="button" class="export-action-card is-filter" onclick="openExportFilterModal()">
                    <span class="export-card-icon">
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" aria-hidden="true">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 3c2.755 0 5.455.232 8.083.678.533.09.917.556.917 1.096v1.044a2.25 2.25 0 0 1-.659 1.591l-5.432 5.432a2.25 2.25 0 0 0-.659 1.591v2.927a2.25 2.25 0 0 1-1.244 2.013L9.75 21v-6.568a2.25 2.25 0 0 0-.659-1.591L3.659 7.409A2.25 2.25 0 0 1 3 5.818V4.774c0-.54.384-1.006.917-1.096A48.32 48.32 0 0 1 12 3Z" />
                        </svg>
                    </span>
                    <span>
                        <span class="export-card-label">Report Filter</span>
                        <span class="export-card-value">Filter</span>
                        <span class="export-card-copy">Click to view</span>
                    </span>
                    <span class="export-card-arrow" aria-hidden="true">&rarr;</span>
                </button>

                @foreach($exportLinks as $link)
                    <a href="{{ $link['url'] }}" target="_blank" class="export-action-card is-export">
                        <span class="export-card-icon"><x-outline-icon name="document-text" /></span>
                        <span>
                            <span class="export-card-label">Export Inventory</span>
                            <span class="export-card-value">{{ $link['label'] === 'Inventory of Medicines' ? 'Medicines' : 'Supplies' }}</span>
                            <span class="export-card-copy">Click to view</span>
                        </span>
                        <span class="export-card-arrow" aria-hidden="true">&rarr;</span>
                    </a>
                @endforeach
            @else
            @if(($reportType ?? '') === 'appointments')
                <div class="export-action-card">
                    <span class="export-card-icon"><x-outline-icon name="calendar-days" /></span>
                    <span>
                        <span class="export-card-label">Total Appointments</span>
                        <span class="export-card-value">{{ number_format($previewMetrics['total_appointments'] ?? $previewCount) }}</span>
                    </span>
                </div>

                <div class="export-action-card">
                    <span class="export-card-icon"><x-outline-icon name="clipboard-document-list" /></span>
                    <span>
                        <span class="export-card-label">Common Service</span>
                        <span class="export-card-value">{{ \Illuminate\Support\Str::limit($previewMetrics['common_service'] ?? 'N/A', 18) }}</span>
                    </span>
                </div>
            @elseif(($reportType ?? '') === 'audit-trail')
                <div class="export-action-card">
                    <span class="export-card-icon"><x-outline-icon name="clipboard-document-list" /></span>
                    <span>
                        <span class="export-card-label">Total Activities</span>
                        <span class="export-card-value">{{ number_format($previewMetrics['total_activities'] ?? $previewCount) }}</span>
                    </span>
                </div>

                <div class="export-action-card">
                    <span class="export-card-icon"><x-outline-icon name="clock" /></span>
                    <span>
                        <span class="export-card-label">Last Export</span>
                        <span class="export-card-value">{{ $previewMetrics['last_audit_export'] ?? 'N/A' }}</span>
                    </span>
                </div>
            @elseif(($reportType ?? '') === 'health-forms')
                <div class="export-action-card">
                    <span class="export-card-icon"><x-outline-icon name="check" /></span>
                    <span>
                        <span class="export-card-label">Total Issued</span>
                        <span class="export-card-value">{{ number_format($previewMetrics['total_issued'] ?? $previewCount) }}</span>
                    </span>
                </div>

                <div class="export-action-card">
                    <span class="export-card-icon">
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" aria-hidden="true">
                            <path d="M20.8 5.8c-1.7-2-4.7-2.1-6.5-.3L12 7.8 9.7 5.5C7.9 3.7 4.9 3.8 3.2 5.8c-1.6 1.9-1.4 4.8.4 6.6L12 20.5l8.4-8.1c1.8-1.8 2-4.7.4-6.6Z" stroke-width="2" stroke-linejoin="round"/>
                        </svg>
                    </span>
                    <span>
                        <span class="export-card-label">With Condition</span>
                        <span class="export-card-value">{{ number_format($previewMetrics['with_condition'] ?? 0) }}</span>
                    </span>
                </div>
            @else
                <div class="export-action-card">
                    <span class="export-card-icon"><x-outline-icon name="calendar-days" /></span>
                    <span>
                        <span class="export-card-label">Selected Period</span>
                        <span class="export-card-value">{{ $dateFrom->format('M d') }} - {{ $dateTo->format($dateFrom->isSameYear($dateTo) ? 'M d, Y' : 'M d, Y') }}</span>
                        <span class="export-card-copy">{{ $dateFrom->diffInDays($dateTo) + 1 }} day{{ $dateFrom->diffInDays($dateTo) + 1 === 1 ? '' : 's' }} included</span>
                    </span>
                </div>

                <div class="export-action-card">
                    <span class="export-card-icon"><x-outline-icon name="clipboard-document-list" /></span>
                    <span>
                        <span class="export-card-label">Preview Records</span>
                        <span class="export-card-value">{{ number_format($previewCount) }}</span>
                        <span class="export-card-copy">Ready for export</span>
                    </span>
                </div>
            @endif

            <button type="button" class="export-action-card is-filter" onclick="openExportFilterModal()">
                <span class="export-card-icon">
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 3c2.755 0 5.455.232 8.083.678.533.09.917.556.917 1.096v1.044a2.25 2.25 0 0 1-.659 1.591l-5.432 5.432a2.25 2.25 0 0 0-.659 1.591v2.927a2.25 2.25 0 0 1-1.244 2.013L9.75 21v-6.568a2.25 2.25 0 0 0-.659-1.591L3.659 7.409A2.25 2.25 0 0 1 3 5.818V4.774c0-.54.384-1.006.917-1.096A48.32 48.32 0 0 1 12 3Z" />
                    </svg>
                </span>
                <span>
                    <span class="export-card-label">Report Filter</span>
                    <span class="export-card-value">Filter</span>
                    <span class="export-card-copy">Click to view</span>
                </span>
                <span class="export-card-arrow" aria-hidden="true">→</span>
            </button>

            @if(($reportType ?? '') === 'health-forms')
                <details class="export-action-card is-export health-forms-export-card">
                    <summary aria-label="Choose a Health Forms export format">
                        <span class="export-card-icon"><x-outline-icon name="document-text" /></span>
                        <span>
                            <span class="export-card-label">Export Report</span>
                            <span class="export-card-value">{{ $exportLabel }}</span>
                            <span class="export-card-copy">Click to view</span>
                        </span>
                        <span class="export-card-arrow" aria-hidden="true">&rarr;</span>
                    </summary>
                    <div class="health-forms-export-options">
                        @foreach($exportLinks as $link)
                            <a href="{{ $link['url'] }}" target="_blank" class="health-forms-export-option">{{ $link['label'] }}</a>
                        @endforeach
                    </div>
                </details>
            @else
                <a href="{{ $exportUrl }}" target="_blank" class="export-action-card is-export">
                    <span class="export-card-icon"><x-outline-icon name="document-text" /></span>
                    <span>
                        <span class="export-card-label">Export Report</span>
                        <span class="export-card-value">{{ $exportLabel }}</span>
                        <span class="export-card-copy">Click to view</span>
                    </span>
                    <span class="export-card-arrow" aria-hidden="true">&rarr;</span>
                </a>
            @endif
            @endif
        </div>
    </section>

    <section class="export-preview-panel">
        <h2 class="export-preview-title">Export Preview</h2>
        <p class="export-preview-copy">{{ number_format($previewCount) }} record{{ $previewCount === 1 ? '' : 's' }} matched. Showing up to 10 rows so you can confirm what information will be exported.</p>
        <div class="export-preview-table-wrap">
            <table class="export-preview-table">
                <thead>
                    <tr>
                        @foreach($previewHeaders as $header)
                            <th>{{ $header }}</th>
                        @endforeach
                    </tr>
                </thead>
                <tbody>
                    @forelse($previewRows as $row)
                        <tr>
                            @foreach($row as $cell)
                                <td>{{ $cell }}</td>
                            @endforeach
                        </tr>
                    @empty
                        <tr>
                            <td colspan="{{ count($previewHeaders) }}" class="export-preview-empty">No preview records found for this date range.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </section>
</div>

<div class="export-filter-backdrop" id="exportFilterModal" onclick="closeExportFilterModal(event)">
    <section class="export-filter-modal" role="dialog" aria-modal="true" aria-labelledby="exportFilterTitle">
        <header class="export-filter-modal-head">
            <span class="export-filter-modal-icon">
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" aria-hidden="true">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 3c2.755 0 5.455.232 8.083.678.533.09.917.556.917 1.096v1.044a2.25 2.25 0 0 1-.659 1.591l-5.432 5.432a2.25 2.25 0 0 0-.659 1.591v2.927a2.25 2.25 0 0 1-1.244 2.013L9.75 21v-6.568a2.25 2.25 0 0 0-.659-1.591L3.659 7.409A2.25 2.25 0 0 1 3 5.818V4.774c0-.54.384-1.006.917-1.096A48.32 48.32 0 0 1 12 3Z" />
                </svg>
            </span>
            <div>
                <h2 class="export-filter-modal-title" id="exportFilterTitle">Filter {{ $title }}</h2>
                <p class="export-filter-modal-copy">Set the date range for the preview and generated export.</p>
            </div>
            <button type="button" class="export-filter-modal-close" onclick="closeExportFilterModal()" aria-label="Close filter"><span aria-hidden="true">&times;</span></button>
        </header>
        <form method="GET" action="{{ $filterActionUrl }}" class="export-filter-form">
            <div class="export-filter-field">
                <label for="exportDateFrom">From Date</label>
                <input class="export-date-input" id="exportDateFrom" type="date" name="date_from" value="{{ $dateFrom->toDateString() }}">
            </div>
            <div class="export-filter-field">
                <label for="exportDateTo">To Date</label>
                <input class="export-date-input" id="exportDateTo" type="date" name="date_to" value="{{ $dateTo->toDateString() }}">
            </div>
            @if(($reportType ?? '') === 'health-forms')
                @php
                    $selectedBmiCategories = collect((array) request()->query('bmi_categories', []))
                        ->flatMap(fn ($value) => explode(',', (string) $value))
                        ->map(fn ($value) => strtolower(trim($value)))
                        ->filter()
                        ->values();
                @endphp
                <div class="export-filter-field">
                    <label for="exportHealthCourse">Course</label>
                    <div class="export-filter-select-wrap">
                        <select class="export-filter-select export-filter-custom-source" id="exportHealthCourse" name="course">
                            <option value="">All Courses</option>
                            @foreach(($healthFormCourses ?? collect()) as $course)
                                <option value="{{ $course }}" {{ request('course') === $course ? 'selected' : '' }}>{{ $course }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>
                <div class="export-filter-field">
                    <label for="exportHealthUserType">User Type</label>
                    <div class="export-filter-select-wrap">
                        <select class="export-filter-select export-filter-custom-source" id="exportHealthUserType" name="user_type">
                            <option value="">All User Types</option>
                            @foreach(['student' => 'Student', 'applicant' => 'Applicant', 'faculty' => 'Faculty', 'admin' => 'Admin'] as $value => $label)
                                <option value="{{ $value }}" {{ request('user_type') === $value ? 'selected' : '' }}>{{ $label }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>
                <div class="export-filter-field">
                    <label for="exportHealthGender">Gender</label>
                    <div class="export-filter-select-wrap">
                        <select class="export-filter-select export-filter-custom-source" id="exportHealthGender" name="gender">
                            <option value="">All Gender</option>
                            @foreach(['male' => 'Male', 'female' => 'Female'] as $value => $label)
                                <option value="{{ $value }}" {{ request('gender') === $value ? 'selected' : '' }}>{{ $label }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>
                <div class="export-filter-field">
                    <label for="exportHealthCondition">Medical Condition</label>
                    <div class="export-filter-select-wrap">
                        <select class="export-filter-select export-filter-custom-source" id="exportHealthCondition" name="condition">
                            <option value="">All Conditions</option>
                            <option value="yes" {{ request('condition') === 'yes' ? 'selected' : '' }}>With Condition</option>
                            <option value="no" {{ request('condition') === 'no' ? 'selected' : '' }}>No Condition</option>
                        </select>
                    </div>
                </div>
                <div class="export-filter-field">
                    <label for="exportHealthStatus">Status</label>
                    <div class="export-filter-select-wrap">
                        <select class="export-filter-select export-filter-custom-source" id="exportHealthStatus" name="status">
                            <option value="">All Status</option>
                            <option value="approved" {{ request('status') === 'approved' ? 'selected' : '' }}>Approved</option>
                            <option value="pending" {{ request('status') === 'pending' ? 'selected' : '' }}>Pending</option>
                            <option value="rejected" {{ request('status') === 'rejected' ? 'selected' : '' }}>Rejected</option>
                        </select>
                    </div>
                </div>
                <div class="export-filter-field is-wide">
                    <div class="export-condition-keyword-head">
                        <label for="exportHealthConditionKeyword">Condition Keyword</label>
                        @if(($healthConditionSuggestions ?? collect())->isNotEmpty())
                            <button type="button" class="export-condition-suggest-toggle" id="toggleConditionSuggestions" aria-expanded="false" aria-controls="conditionKeywordSuggestions">
                                Suggestions
                            </button>
                        @endif
                    </div>
                    <input class="export-date-input" id="exportHealthConditionKeyword" type="search" name="condition_keyword" value="{{ request('condition_keyword') }}" placeholder="Asthma, allergy, remarks">
                    @if(($healthConditionSuggestions ?? collect())->isNotEmpty())
                        <div class="export-condition-suggestions" id="conditionKeywordSuggestions">
                            @foreach($healthConditionSuggestions as $suggestion)
                                <button type="button" class="export-condition-suggestion" data-condition-suggestion="{{ $suggestion }}">
                                    {{ $suggestion }}
                                </button>
                            @endforeach
                        </div>
                    @endif
                    <input type="hidden" name="condition_source" value="{{ request('condition_source', 'all') }}">
                    <input type="hidden" name="condition_match" value="{{ request('condition_match', 'any') }}">
                </div>
                <div class="export-filter-option-section">
                    <div class="export-filter-checks is-single">
                        <label class="export-filter-check">
                            <input type="checkbox" name="course_sheets" value="1" {{ request()->boolean('course_sheets') ? 'checked' : '' }}>
                            Export Applicants by Course Sheets
                        </label>
                    </div>
                </div>
                <div class="export-filter-option-section">
                    <p class="export-filter-option-title">BMI Category</p>
                    <div class="export-filter-checks">
                        @foreach(['underweight' => 'Underweight', 'normal' => 'Normal', 'overweight' => 'Overweight', 'obese' => 'Obese', 'no_bmi' => 'No BMI Recorded'] as $value => $label)
                            <label class="export-filter-check">
                                <input type="checkbox" name="bmi_categories[]" value="{{ $value }}" {{ $selectedBmiCategories->contains($value) ? 'checked' : '' }}>
                                {{ $label }}
                            </label>
                        @endforeach
                    </div>
                </div>
            @endif
            <div class="export-filter-actions">
                <button type="button" class="export-filter-cancel" onclick="closeExportFilterModal()">Cancel</button>
                <button type="submit" class="export-filter-submit">Apply Filter</button>
            </div>
        </form>
    </section>
</div>

@push('scripts')
<script>
    function openExportFilterModal() {
        document.getElementById('exportFilterModal')?.classList.add('is-open');
    }

    function closeExportFilterModal(event) {
        if (event && event.target.id !== 'exportFilterModal') {
            return;
        }
        document.getElementById('exportFilterModal')?.classList.remove('is-open');
    }

    document.addEventListener('keydown', function(event) {
        if (event.key === 'Escape') {
            closeExportFilterModal();
        }
    });

    const conditionSuggestionToggle = document.getElementById('toggleConditionSuggestions');
    const conditionSuggestionPanel = document.getElementById('conditionKeywordSuggestions');
    const conditionKeywordInput = document.getElementById('exportHealthConditionKeyword');

    if (conditionSuggestionToggle && conditionSuggestionPanel) {
        conditionSuggestionToggle.addEventListener('click', function () {
            const willOpen = !conditionSuggestionPanel.classList.contains('is-open');
            conditionSuggestionPanel.classList.toggle('is-open', willOpen);
            conditionSuggestionToggle.setAttribute('aria-expanded', willOpen ? 'true' : 'false');
        });
    }

    const conditionSuggestionButtons = document.querySelectorAll('[data-condition-suggestion]');

    function selectedConditionKeywords() {
        if (!conditionKeywordInput) return [];

        return conditionKeywordInput.value
            .split(',')
            .map(function (value) { return value.trim().toLowerCase(); })
            .filter(Boolean);
    }

    function syncConditionSuggestionSelection() {
        const selectedValues = selectedConditionKeywords();

        conditionSuggestionButtons.forEach(function (button) {
            const suggestion = (button.dataset.conditionSuggestion || button.textContent.trim()).trim().toLowerCase();
            button.classList.toggle('is-selected', selectedValues.includes(suggestion));
        });
    }

    conditionSuggestionButtons.forEach(function (button) {
        button.addEventListener('click', function () {
            if (!conditionKeywordInput) return;
            const suggestion = (button.dataset.conditionSuggestion || button.textContent.trim()).trim();
            const values = conditionKeywordInput.value
                .split(',')
                .map(function (value) { return value.trim(); })
                .filter(Boolean);
            const existingIndex = values.findIndex(function (value) {
                return value.toLowerCase() === suggestion.toLowerCase();
            });

            if (existingIndex >= 0) {
                values.splice(existingIndex, 1);
                button.classList.remove('is-selected');
            } else {
                values.push(suggestion);
                button.classList.add('is-selected');
            }

            conditionKeywordInput.value = values.join(', ');
            conditionKeywordInput.focus();
            conditionKeywordInput.dispatchEvent(new Event('input', { bubbles: true }));
            syncConditionSuggestionSelection();
        });
    });

    if (conditionKeywordInput) {
        conditionKeywordInput.addEventListener('input', syncConditionSuggestionSelection);
        syncConditionSuggestionSelection();
    }

    document.querySelectorAll('.export-filter-custom-source').forEach(function (select) {
        const wrap = select.closest('.export-filter-select-wrap');
        if (!wrap || wrap.dataset.customReady === '1') return;

        wrap.dataset.customReady = '1';

        const trigger = document.createElement('button');
        trigger.type = 'button';
        trigger.className = 'export-filter-custom-trigger';
        trigger.setAttribute('aria-haspopup', 'listbox');
        trigger.setAttribute('aria-expanded', 'false');

        const menu = document.createElement('div');
        menu.className = 'export-filter-custom-menu';
        menu.setAttribute('role', 'listbox');

        function selectedOption() {
            return select.options[select.selectedIndex] || select.options[0];
        }

        function syncTrigger() {
            const option = selectedOption();
            trigger.textContent = option ? option.textContent.trim() : '';
            menu.querySelectorAll('.export-filter-custom-option').forEach(function (button) {
                const isSelected = button.dataset.value === select.value;
                button.classList.toggle('is-selected', isSelected);
                button.setAttribute('aria-selected', isSelected ? 'true' : 'false');
            });
        }

        Array.prototype.forEach.call(select.options, function (option) {
            const button = document.createElement('button');
            button.type = 'button';
            button.className = 'export-filter-custom-option';
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
            document.querySelectorAll('.export-filter-select-wrap.is-open').forEach(function (openWrap) {
                if (openWrap !== wrap) {
                    openWrap.classList.remove('is-open');
                    const openTrigger = openWrap.querySelector('.export-filter-custom-trigger');
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
        if (event.target.closest('.export-filter-select-wrap')) return;

        document.querySelectorAll('.export-filter-select-wrap.is-open').forEach(function (wrap) {
            wrap.classList.remove('is-open');
            const trigger = wrap.querySelector('.export-filter-custom-trigger');
            if (trigger) trigger.setAttribute('aria-expanded', 'false');
        });
    });
</script>
@endpush
@endsection
