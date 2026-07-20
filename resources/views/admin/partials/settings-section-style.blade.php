<style>
    :root {
        --stg-maroon: #7f0000;
        --stg-maroon-deep: #4f0000;
        --stg-yellow: #facc15;
        --stg-text: #111827;
        --stg-muted: #64748b;
        --stg-border: rgba(127, 0, 0, 0.12);
    }
    .settings-section-page {
        position: relative;
        isolation: isolate;
    }
    .settings-section-page::before {
        content: '';
        position: absolute;
        inset: 0;
        border-radius: 28px;
        background:
            radial-gradient(circle at top left, rgba(127, 0, 0, 0.08), transparent 24%),
            radial-gradient(circle at bottom right, rgba(250, 204, 21, 0.12), transparent 24%),
            linear-gradient(180deg, rgba(248, 250, 252, 0.96), rgba(255, 255, 255, 0.92));
        pointer-events: none;
        z-index: -1;
    }
    .settings-section-page > * {
        position: relative;
        z-index: 1;
    }
    .settings-section-hero {
        display: flex;
        align-items: flex-start;
        justify-content: space-between;
        gap: 18px;
        padding: 22px;
        margin-bottom: 22px;
        border-radius: 20px;
        border: 1px solid var(--stg-border);
        background: #ffffff;
        box-shadow: 0 14px 34px rgba(15, 23, 42, 0.08);
    }
    .settings-section-title {
        display: flex;
        align-items: center;
        gap: 12px;
        margin: 0;
        color: var(--stg-text);
        font-size: 26px;
        font-weight: 900;
        line-height: 1.1;
    }
    .settings-section-title > svg {
        width: 44px;
        height: 44px;
        padding: 10px;
        border-radius: 14px;
        color: var(--stg-maroon);
        background: linear-gradient(135deg, #fff7cc, #fee2e2);
    }
    .settings-section-hero p {
        max-width: 720px;
        margin: 10px 0 0;
        color: var(--stg-muted);
        font-size: 14px;
        line-height: 1.65;
    }
    .settings-back-link {
        min-height: 40px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        gap: 8px;
        padding: 0 14px;
        border-radius: 12px;
        border: 1px solid rgba(127, 0, 0, 0.16);
        background: #ffffff;
        color: var(--stg-maroon);
        font-size: 12px;
        font-weight: 900;
        text-decoration: none;
        white-space: nowrap;
    }
    .settings-back-link:hover,
    .settings-back-link:focus-visible {
        background: var(--stg-yellow);
        border-color: var(--stg-yellow);
        color: #111827;
        outline: none;
    }
    .settings-section-grid {
        display: grid;
        grid-template-columns: minmax(0, 1fr);
        gap: 22px;
    }
    .settings-section-grid.two {
        grid-template-columns: repeat(2, minmax(0, 1fr));
    }
    .settings-panel {
        border-radius: 20px;
        border: 1px solid var(--stg-border);
        background: #ffffff;
        box-shadow: 0 18px 44px rgba(15, 23, 42, 0.08);
        overflow: hidden;
    }
    .settings-panel-head {
        display: flex;
        align-items: flex-start;
        justify-content: space-between;
        gap: 14px;
        padding: 20px 22px;
        border-bottom: 1px solid rgba(127, 0, 0, 0.1);
        background: linear-gradient(180deg, rgba(127, 0, 0, 0.04), rgba(255, 255, 255, 0));
    }
    .settings-panel-head h3 {
        margin: 0;
        color: var(--stg-maroon);
        font-size: 17px;
        font-weight: 900;
    }
    .settings-panel-head p {
        margin: 5px 0 0;
        color: var(--stg-muted);
        font-size: 12px;
        line-height: 1.5;
    }
    .settings-panel-body {
        padding: 22px;
    }
    .settings-form-grid {
        display: grid;
        grid-template-columns: repeat(2, minmax(0, 1fr));
        gap: 16px;
    }
    .settings-field {
        display: grid;
        gap: 7px;
    }
    .settings-field.full {
        grid-column: 1 / -1;
    }
    .settings-field label,
    .settings-static-label {
        color: var(--stg-muted);
        font-size: 11px;
        font-weight: 900;
        letter-spacing: .06em;
        text-transform: uppercase;
    }
    .settings-field input,
    .settings-field select,
    .settings-field textarea {
        width: 100%;
        min-height: 48px;
        padding: 12px 14px;
        border-radius: 12px;
        border: 1px solid rgba(127, 0, 0, 0.13);
        background: #ffffff;
        color: var(--stg-text);
        font: inherit;
        font-weight: 700;
    }
    .settings-field input:disabled,
    .settings-field select:disabled,
    .settings-field textarea:disabled {
        color: #334155;
        background: #f8fafc;
        border-color: rgba(127, 0, 0, 0.08);
        cursor: default;
        opacity: 1;
    }
    .settings-field textarea {
        min-height: 96px;
        resize: vertical;
    }
    .settings-field input:focus,
    .settings-field select:focus,
    .settings-field textarea:focus {
        outline: none;
        border-color: var(--stg-maroon);
        box-shadow: 0 0 0 4px rgba(127, 0, 0, 0.08);
    }
    .settings-static-list {
        display: grid;
        gap: 12px;
    }
    .settings-static-row {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 16px;
        padding: 14px;
        border-radius: 14px;
        border: 1px solid rgba(127, 0, 0, 0.1);
        background: #ffffff;
    }
    .settings-static-value {
        color: var(--stg-text);
        font-size: 13px;
        font-weight: 800;
        text-align: right;
    }
    .settings-action-row {
        display: flex;
        justify-content: flex-end;
        gap: 10px;
        margin-top: 18px;
    }
    .settings-edit-btn,
    .settings-save-btn,
    .settings-cancel-btn {
        min-height: 42px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        gap: 8px;
        padding: 0 16px;
        border: 0;
        border-radius: 12px;
        font-size: 13px;
        font-weight: 900;
        cursor: pointer;
        position: relative;
        overflow: hidden;
        box-shadow: 0 12px 22px rgba(127, 0, 0, 0.14);
    }
    .settings-edit-btn svg,
    .settings-save-btn svg,
    .settings-cancel-btn svg {
        width: 17px;
        height: 17px;
        flex: 0 0 auto;
        stroke: currentColor;
    }
    .settings-edit-btn {
        background: #ffffff;
        color: #111827;
        border: 1px solid rgba(127, 0, 0, 0.12);
    }
    .settings-save-btn {
        background: var(--stg-maroon);
        color: #ffffff;
    }
    .settings-cancel-btn {
        background: #e5e7eb;
        color: #374151;
        box-shadow: 0 10px 18px rgba(15, 23, 42, 0.08);
    }
    .settings-edit-btn::before,
    .settings-save-btn::before,
    .settings-cancel-btn::before {
        content: "";
        position: absolute;
        inset: 0;
        background: var(--stg-yellow);
        transform: translateX(-102%);
        transition: transform .46s ease;
        z-index: 0;
    }
    .settings-edit-btn > *,
    .settings-save-btn > *,
    .settings-cancel-btn > * {
        position: relative;
        z-index: 1;
    }
    .settings-edit-btn:hover,
    .settings-save-btn:hover,
    .settings-cancel-btn:hover,
    .settings-edit-btn:focus-visible,
    .settings-save-btn:focus-visible,
    .settings-cancel-btn:focus-visible {
        color: var(--stg-maroon);
        outline: none;
    }
    .settings-edit-btn:hover::before,
    .settings-save-btn:hover::before,
    .settings-cancel-btn:hover::before,
    .settings-edit-btn:focus-visible::before,
    .settings-save-btn:focus-visible::before,
    .settings-cancel-btn:focus-visible::before {
        transform: translateX(0);
    }
    .settings-section-page .settings-edit-btn {
        background-color: #ffffff !important;
        color: #111827 !important;
    }
    .settings-section-page .settings-save-btn {
        background-color: var(--stg-maroon) !important;
        color: #ffffff !important;
    }
    .settings-section-page .settings-save-btn:hover,
    .settings-section-page .settings-save-btn:focus-visible {
        color: var(--stg-maroon) !important;
    }
    .settings-section-page .settings-cancel-btn {
        background-color: #e5e7eb !important;
        color: #111827 !important;
    }
    .settings-section-page .settings-cancel-btn:hover,
    .settings-section-page .settings-cancel-btn:focus-visible {
        color: var(--stg-maroon) !important;
    }
    .settings-edit-actions {
        display: none;
        gap: 10px;
    }
    .settings-editable-form.is-editing .settings-edit-actions {
        display: inline-flex;
    }
    .settings-editable-form.is-editing .settings-edit-btn {
        display: none;
    }
    .settings-option-card {
        display: flex;
        align-items: center;
        gap: 16px;
        min-height: 112px;
        padding: 20px;
        border-radius: 18px;
        border: 1px solid rgba(127, 0, 0, 0.13);
        background: #ffffff;
    }
    .settings-option-icon {
        width: 56px;
        height: 56px;
        display: grid;
        place-items: center;
        flex: 0 0 auto;
        border-radius: 16px;
        color: var(--stg-maroon);
        background: linear-gradient(135deg, #fff7cc, #fee2e2);
    }
    .settings-option-icon svg {
        width: 30px;
        height: 30px;
    }
    .settings-option-card h4 {
        margin: 0;
        color: var(--stg-text);
        font-size: 16px;
        font-weight: 900;
    }
    .settings-option-card p {
        margin: 5px 0 0;
        color: var(--stg-muted);
        font-size: 12px;
        line-height: 1.55;
    }
    .alert {
        margin-bottom: 16px;
        padding: 12px 14px;
        border-radius: 12px;
        font-size: 13px;
        font-weight: 800;
    }
    .alert-success {
        color: #166534;
        background: #dcfce7;
        border: 1px solid #bbf7d0;
    }
    .alert-error {
        color: #991b1b;
        background: #fee2e2;
        border: 1px solid #fecaca;
    }
    @media (max-width: 900px) {
        .settings-section-hero,
        .settings-panel-head,
        .settings-static-row {
            flex-direction: column;
            align-items: stretch;
        }
        .settings-section-grid.two,
        .settings-form-grid {
            grid-template-columns: 1fr;
        }
        .settings-static-value {
            text-align: left;
        }
    }
</style>
