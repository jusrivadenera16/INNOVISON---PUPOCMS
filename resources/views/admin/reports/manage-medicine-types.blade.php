@extends('layouts.admin')

@section('title', 'Manage Medicine Types')

@push('styles')
<style>
    .medicine-types-page,
    .medicine-modal-overlay {
        --clinic-maroon: #8f1024;
        --clinic-deep: #6d0718;
        --clinic-border: #ead4d7;
        --clinic-muted: #64748b;
    }

    .medicine-types-page {
        color: #201016;
    }

    .medicine-types-header,
    .medicine-types-title-wrap,
    .medicine-type-main,
    .linked-medicine-actions,
    .modal-actions {
        display: flex;
        align-items: center;
    }

    .medicine-types-header {
        justify-content: space-between;
        gap: 18px;
        margin-bottom: 28px;
    }

    .medicine-types-title-wrap {
        gap: 14px;
        min-width: 0;
    }

    .medicine-types-title-icon,
    .medicine-type-toggle,
    .medicine-type-code,
    .standard-modal-icon,
    .standard-modal-close {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        flex: 0 0 auto;
    }

    .medicine-types-title-icon {
        width: 46px;
        height: 46px;
        border-radius: 10px;
        color: #facc15;
        background: linear-gradient(135deg, var(--clinic-maroon), var(--clinic-deep));
        box-shadow: 0 12px 24px rgba(143, 16, 36, .18);
    }

    .medicine-types-title-icon svg,
    .medicine-types-action svg,
    .medicine-types-field svg,
    .medicine-type-toggle svg,
    .medicine-type-btn svg,
    .standard-modal-icon svg,
    .standard-modal-close svg {
        width: 18px;
        height: 18px;
        fill: none;
        stroke: currentColor;
        stroke-width: 2;
        stroke-linecap: round;
        stroke-linejoin: round;
    }

    .medicine-types-title-wrap h1 {
        margin: 0;
        color: var(--clinic-deep);
        font-size: 1.35rem;
        font-weight: 900;
        letter-spacing: 0;
    }

    .medicine-types-title-wrap p {
        margin: 4px 0 0;
        color: var(--clinic-muted);
        font-size: .88rem;
        font-weight: 600;
    }

    .medicine-types-toolbar {
        display: grid;
        grid-template-columns: minmax(240px, 1fr) auto;
        gap: 14px;
        align-items: center;
        margin-bottom: 18px;
    }

    .medicine-types-field {
        position: relative;
        display: flex;
        align-items: center;
        min-height: 48px;
        color: #64748b;
    }

    .medicine-types-field svg {
        position: absolute;
        left: 16px;
        pointer-events: none;
    }

    .medicine-types-field input,
    .medicine-modal-box .form-control {
        width: 100%;
        min-height: 48px;
        border: 1px solid #d9e1ec;
        border-radius: 9px;
        padding: 0 42px;
        color: #263241;
        background: #fff;
        font-size: .88rem;
        font-weight: 700;
        outline: none;
        transition: border-color .18s ease, box-shadow .18s ease;
    }

    .medicine-types-field input:focus,
    .medicine-modal-box .form-control:focus {
        border-color: rgba(143, 16, 36, .42);
        box-shadow: 0 0 0 4px rgba(143, 16, 36, .08);
    }

    .medicine-types-action,
    .medicine-type-btn,
    .btn-cancel,
    .btn-save {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        gap: 8px;
        position: relative;
        overflow: hidden;
        border-radius: 8px;
        font-weight: 900;
        cursor: pointer;
        text-decoration: none;
        transition: color .18s ease, background .18s ease, transform .18s ease, box-shadow .18s ease, border-color .18s ease;
    }

    .medicine-types-action {
        min-height: 48px;
        border-radius: 13px;
        border: 1px solid rgba(250, 204, 21, .32);
        padding: 0 22px;
        color: #fff;
        background: linear-gradient(135deg, var(--clinic-maroon), var(--clinic-deep));
        box-shadow: 0 12px 24px rgba(143, 16, 36, .18);
        white-space: nowrap;
    }

    .medicine-types-action::after,
    .medicine-type-btn::after,
    .btn-cancel::after,
    .btn-save::after,
    .standard-modal-close::after {
        content: "";
        position: absolute;
        inset: 0;
        background: linear-gradient(120deg, transparent 0%, rgba(255, 246, 179, .18) 25%, rgba(255, 246, 179, .72) 50%, rgba(255, 246, 179, .18) 75%, transparent 100%);
        transform: translateX(-135%);
        transition: transform .85s ease;
        pointer-events: none;
    }

    .medicine-types-action:hover,
    .medicine-type-btn:hover,
    .btn-cancel:hover,
    .btn-save:hover {
        transform: translateY(-1px);
        color: var(--clinic-maroon);
        border-color: rgba(250, 204, 21, .9);
        background: #facc15;
        box-shadow: 0 10px 20px rgba(143, 16, 36, .14);
    }

    .medicine-types-action:hover::after,
    .medicine-type-btn:hover::after,
    .btn-cancel:hover::after,
    .btn-save:hover::after,
    .standard-modal-close:hover::after {
        transform: translateX(135%);
    }

    .medicine-types-action span,
    .medicine-types-action svg,
    .medicine-type-btn span,
    .medicine-type-btn svg,
    .btn-cancel span,
    .btn-save span {
        position: relative;
        z-index: 1;
    }

    .medicine-types-meta-row {
        margin: 8px 0 16px;
    }

    .medicine-types-count {
        color: #263241;
        font-size: .86rem;
        font-weight: 800;
    }

    .medicine-types-list {
        display: grid;
        gap: 10px;
        max-height: clamp(320px, calc(100vh - 340px), 600px);
        overflow-y: auto;
        padding-right: 4px;
        scrollbar-width: thin;
    }

    .medicine-type-group {
        overflow: hidden;
        border: 1px solid var(--clinic-border);
        border-radius: 9px;
        background: #fff;
    }

    .medicine-type-group[hidden] {
        display: none;
    }

    .medicine-type-group-header {
        display: grid;
        grid-template-columns: 34px 34px minmax(0, 1fr) auto 42px 28px;
        align-items: center;
        gap: 12px;
        width: 100%;
        min-height: 58px;
        padding: 12px 18px;
        color: var(--clinic-deep);
        background: linear-gradient(180deg, #fff8f9, #fff2f4);
        text-align: left;
    }

    .medicine-type-header-name {
        min-width: 0;
        border: 0;
        padding: 0;
        color: inherit;
        background: transparent;
        cursor: pointer;
        text-align: left;
    }

    .medicine-type-chevron-button {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        width: 28px;
        height: 28px;
        border: 0;
        padding: 0;
        color: inherit;
        background: transparent;
        cursor: pointer;
    }

    .medicine-type-toggle {
        width: 28px;
        height: 28px;
        border: 1px solid #efcfd4;
        border-radius: 6px;
        color: var(--clinic-maroon);
        background: #fff;
        transition: transform .18s ease;
    }

    .medicine-type-group.is-open .medicine-type-toggle {
        transform: rotate(90deg);
    }

    .medicine-type-code {
        width: 30px;
        height: 30px;
        border-radius: 7px;
        color: #fff;
        background: var(--clinic-maroon);
        font-size: .78rem;
        font-weight: 900;
    }

    .medicine-type-name {
        min-width: 0;
        overflow: hidden;
        text-overflow: ellipsis;
        white-space: nowrap;
        font-size: .95rem;
        font-weight: 900;
    }

    .medicine-type-count {
        border-radius: 999px;
        padding: 7px 12px;
        color: var(--clinic-maroon);
        background: #f8e7ea;
        font-size: .75rem;
        font-weight: 900;
        white-space: nowrap;
    }

    .medicine-type-chevron {
        width: 18px;
        height: 18px;
        color: var(--clinic-maroon);
        fill: none;
        stroke: currentColor;
        stroke-width: 2;
        stroke-linecap: round;
        stroke-linejoin: round;
        transition: transform .18s ease;
    }

    .medicine-type-group.is-open .medicine-type-chevron {
        transform: rotate(180deg);
    }

    .medicine-type-body {
        display: none;
        max-height: 300px;
        overflow-y: auto;
        scrollbar-width: thin;
    }

    .medicine-type-group.is-open .medicine-type-body {
        display: block;
    }

    .linked-medicine-row {
        display: grid;
        grid-template-columns: 1fr auto;
        align-items: center;
        gap: 16px;
        min-height: 54px;
        padding: 10px 14px 10px 34px;
        border-top: 1px solid #eef1f5;
    }

    .linked-medicine-name {
        display: flex;
        align-items: center;
        gap: 18px;
        min-width: 0;
        color: #2c1820;
        font-size: .9rem;
        font-weight: 800;
    }

    .linked-medicine-name::before {
        content: "";
        width: 7px;
        height: 7px;
        border-radius: 999px;
        background: var(--clinic-maroon);
        flex: 0 0 auto;
    }

    .linked-medicine-actions {
        justify-content: flex-end;
        gap: 10px;
    }

    .medicine-type-actions {
        display: flex;
        justify-content: flex-end;
        gap: 10px;
        border-top: 1px solid #eef1f5;
        padding: 12px 14px;
    }

    .medicine-type-btn,
    .btn-cancel,
    .btn-save {
        min-height: 38px;
        border-radius: 8px;
        padding: 0 14px;
        font-size: .8rem;
    }

    .medicine-type-btn--edit,
    .btn-cancel {
        color: var(--clinic-maroon);
        border: 1px solid #f0d8cc;
        background: #fffdfb;
    }

    .medicine-type-btn--delete,
    .btn-save {
        color: #fff;
        border: 1px solid rgba(250, 204, 21, .26);
        background: linear-gradient(135deg, var(--clinic-maroon), var(--clinic-deep));
    }

    .medicine-type-btn--icon {
        width: 42px;
        min-width: 42px;
        height: 38px;
        padding: 0;
    }

    .medicine-type-btn--icon svg {
        width: 16px;
        height: 16px;
    }

    .medicine-types-empty {
        border: 1px dashed #e8cfd3;
        border-radius: 10px;
        padding: 24px;
        color: #64748b;
        background: #fff9fa;
        text-align: center;
        font-weight: 800;
    }

    .medicine-modal-overlay {
        display: none;
        position: fixed;
        inset: 0;
        z-index: 1000;
        align-items: center;
        justify-content: center;
        padding: 18px;
        background: rgba(15, 23, 42, .45);
    }

    .medicine-modal-overlay.is-open {
        display: flex;
    }

    .medicine-modal-box {
        width: min(100%, 520px);
        max-height: 90vh;
        overflow-y: auto;
        border: 1px solid #f0d8cc;
        border-radius: 14px;
        padding: 0;
        background: #fff;
        box-shadow: 0 24px 60px rgba(15, 23, 42, .22);
    }

    .standard-modal-header {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 18px;
        padding: 20px 24px;
        color: #fff !important;
        background: linear-gradient(135deg, #8f1024, #6d0718) !important;
        border-radius: 14px 14px 0 0;
    }

    .standard-modal-title {
        display: flex;
        align-items: center;
        gap: 12px;
        min-width: 0;
    }

    .standard-modal-icon {
        width: 40px;
        height: 40px;
        border-radius: 10px;
        color: #facc15;
        border: 1px solid rgba(250, 204, 21, .28);
        background: rgba(255,255,255,.1);
    }

    .standard-modal-title h3 {
        margin: 0;
        color: #fff !important;
        font-size: 1.2rem;
        font-weight: 900;
    }

    .standard-modal-title p {
        margin: 3px 0 0;
        color: rgba(255,255,255,.78) !important;
        font-size: .84rem;
        font-weight: 700;
    }

    .standard-modal-close {
        position: relative;
        overflow: hidden;
        width: 42px;
        height: 42px;
        border: 1px solid rgba(255,255,255,.22);
        border-radius: 999px;
        color: #fff;
        background: rgba(255,255,255,.08);
        cursor: pointer;
        transition: color .18s ease, background .18s ease, border-color .18s ease, box-shadow .18s ease, transform .18s ease;
    }

    .standard-modal-close:hover {
        color: var(--clinic-maroon);
        border-color: rgba(250, 204, 21, .9);
        background: #facc15;
        box-shadow: 0 12px 24px rgba(250, 204, 21, .22);
        transform: translateY(-1px);
    }

    .standard-modal-close svg {
        position: relative;
        z-index: 1;
    }

    .standard-modal-body {
        padding: 24px;
    }

    .standard-modal-note {
        margin: 0 0 18px;
        color: #64748b;
        font-weight: 700;
    }

    .modal-stack {
        display: grid;
        gap: 16px;
    }

    .modal-actions {
        justify-content: flex-end;
        gap: 10px;
        margin-top: 12px;
    }

    html[data-theme="dark"] .medicine-types-page {
        --clinic-border: rgba(255,255,255,.12);
        color: #f8fafc;
    }

    html[data-theme="dark"] .medicine-types-title-wrap h1,
    html[data-theme="dark"] .medicine-types-count,
    html[data-theme="dark"] .medicine-type-name,
    html[data-theme="dark"] .linked-medicine-name,
    body.dark-mode .medicine-types-title-wrap h1,
    body.dark-mode .medicine-types-count,
    body.dark-mode .medicine-type-name,
    body.dark-mode .linked-medicine-name {
        color: #f8fafc;
    }

    html[data-theme="dark"] .medicine-types-title-wrap p,
    html[data-theme="dark"] .standard-modal-note,
    body.dark-mode .medicine-types-title-wrap p,
    body.dark-mode .standard-modal-note {
        color: #cbd5e1;
    }

    html[data-theme="dark"] .medicine-types-field input,
    html[data-theme="dark"] .medicine-type-group,
    html[data-theme="dark"] .medicine-modal-box,
    body.dark-mode .medicine-types-field input,
    body.dark-mode .medicine-type-group,
    body.dark-mode .medicine-modal-box {
        color: #f8fafc;
        border-color: rgba(255,255,255,.14);
        background: rgba(35, 17, 25, .96);
    }

    html[data-theme="dark"] .medicine-type-group-header,
    body.dark-mode .medicine-type-group-header {
        color: #f8fafc;
        background: linear-gradient(180deg, rgba(112, 19, 27, .75), rgba(55, 20, 30, .86));
    }

    html[data-theme="dark"] .medicine-type-toggle,
    body.dark-mode .medicine-type-toggle {
        color: var(--clinic-maroon);
        border-color: rgba(250, 204, 21, .78);
        background: #facc15;
    }

    html[data-theme="dark"] .medicine-type-chevron,
    body.dark-mode .medicine-type-chevron {
        color: #facc15;
    }

    html[data-theme="dark"] .linked-medicine-row,
    html[data-theme="dark"] .linked-medicine-actions,
    html[data-theme="dark"] .medicine-type-actions,
    body.dark-mode .linked-medicine-row,
    body.dark-mode .linked-medicine-actions,
    body.dark-mode .medicine-type-actions {
        border-top-color: rgba(255,255,255,.1);
    }

    html[data-theme="dark"] .medicine-type-count,
    body.dark-mode .medicine-type-count {
        color: var(--clinic-maroon);
        background: #fff4f5;
    }

    html[data-theme="dark"] .medicine-type-btn--edit,
    html[data-theme="dark"] .btn-cancel,
    body.dark-mode .medicine-type-btn--edit,
    body.dark-mode .btn-cancel {
        color: #f8fafc;
        border-color: rgba(255,255,255,.14);
        background: rgba(18, 18, 18, .35);
    }

    html[data-theme="dark"] .medicine-type-btn:hover,
    html[data-theme="dark"] .btn-cancel:hover,
    html[data-theme="dark"] .btn-save:hover,
    body.dark-mode .medicine-type-btn:hover,
    body.dark-mode .btn-cancel:hover,
    body.dark-mode .btn-save:hover {
        color: var(--clinic-maroon);
        border-color: rgba(250, 204, 21, .9);
        background: #facc15;
    }

    html[data-theme="dark"] .standard-modal-body,
    body.dark-mode .standard-modal-body {
        background: rgba(24, 7, 14, .92);
    }

    html[data-theme="dark"] .medicine-modal-box .form-control,
    body.dark-mode .medicine-modal-box .form-control {
        color: #fff;
        border-color: rgba(255,255,255,.16);
        background: rgba(255,255,255,.05);
    }

    @media (max-width: 720px) {
        .medicine-types-header,
        .medicine-types-toolbar {
            display: grid;
            grid-template-columns: 1fr;
        }

        .medicine-types-action,
        .linked-medicine-actions,
        .linked-medicine-actions form,
        .medicine-type-actions,
        .medicine-type-actions form,
        .medicine-type-btn,
        .modal-actions,
        .btn-cancel,
        .btn-save {
            width: 100%;
        }

        .medicine-type-group-header {
            grid-template-columns: 30px 30px minmax(0, 1fr) 38px 24px;
        }

        .medicine-type-count {
            grid-column: 3 / 4;
            width: fit-content;
            margin-top: 4px;
        }

        .medicine-type-chevron {
            grid-column: 5;
            grid-row: 1 / 3;
        }

        .medicine-type-remove-form {
            grid-column: 4;
            grid-row: 1 / 3;
        }

        .linked-medicine-row {
            grid-template-columns: 1fr;
            padding-left: 18px;
        }

        .linked-medicine-actions,
        .medicine-type-actions,
        .modal-actions {
            flex-direction: column-reverse;
        }
    }
</style>
@endpush

@section('content')
@php
    $totalMedicineTypes = $medicineTypes->count();
@endphp

<div class="medicine-types-page">
    <div class="medicine-types-header">
        <div class="medicine-types-title-wrap">
            <span class="medicine-types-title-icon" aria-hidden="true">
                <svg viewBox="0 0 24 24"><path d="m10.5 20.25 9.75-9.75a5.303 5.303 0 0 0-7.5-7.5L3 12.75a5.303 5.303 0 1 0 7.5 7.5Z"></path><path d="m8.625 7.125 8.25 8.25"></path></svg>
            </span>
            <div>
                <h1>Manage Medicine Types</h1>
                <p>Manage medicine type references used by inventory and MAR reports.</p>
            </div>
        </div>
    </div>

    <div class="medicine-types-toolbar">
        <label class="medicine-types-field">
            <svg viewBox="0 0 24 24" aria-hidden="true"><circle cx="11" cy="11" r="7"></circle><path d="m20 20-3.5-3.5"></path></svg>
            <input type="search" id="medicineTypeSearch" placeholder="Search medicine types..." autocomplete="off">
        </label>

        <button type="button" class="medicine-types-action" onclick="openAddMedicineTypeModal()">
            <svg viewBox="0 0 24 24" aria-hidden="true"><path d="M12 5v14"></path><path d="M5 12h14"></path></svg>
            <span>Add Medicine Type</span>
        </button>
    </div>

    <div class="medicine-types-meta-row">
        <div class="medicine-types-count" id="medicineTypesCount">Showing {{ $totalMedicineTypes }} {{ $totalMedicineTypes === 1 ? 'medicine type' : 'medicine types' }}</div>
    </div>

    <div class="medicine-types-list" id="medicineTypesList">
        @forelse($medicineTypes as $medicineType)
            <section
                class="medicine-type-group"
                data-medicine-type-group
                data-medicine-type-name="{{ strtolower($medicineType->name) }}"
            >
                <div class="medicine-type-group-header">
                    <button type="button" class="medicine-type-toggle" data-medicine-toggle aria-expanded="false">
                        <svg viewBox="0 0 24 24"><path d="m9 18 6-6-6-6"></path></svg>
                    </button>
                    <span class="medicine-type-code">{{ strtoupper(substr($medicineType->name, 0, 1)) }}</span>
                    <button type="button" class="medicine-type-header-name" data-medicine-toggle aria-expanded="false">
                        <span class="medicine-type-name">{{ $medicineType->name }}</span>
                    </button>
                    <span class="medicine-type-count">{{ $medicineType->items_count }} {{ $medicineType->items_count === 1 ? 'linked medicine' : 'linked medicines' }}</span>
                    <form class="medicine-type-remove-form" action="{{ route('medicine-types.destroy', $medicineType->id) }}" method="POST" onsubmit="return confirmMedicineTypeDelete(event)">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="medicine-type-btn medicine-type-btn--delete medicine-type-btn--icon" title="Remove medicine type" aria-label="Remove medicine type">
                            <svg viewBox="0 0 24 24" aria-hidden="true"><path d="M3 6h18"></path><path d="M8 6V4h8v2"></path><path d="M19 6l-1 14H6L5 6"></path><path d="M10 11v5"></path><path d="M14 11v5"></path></svg>
                        </button>
                    </form>
                    <button type="button" class="medicine-type-chevron-button" data-medicine-toggle aria-expanded="false">
                        <svg class="medicine-type-chevron" viewBox="0 0 24 24" aria-hidden="true"><path d="m6 9 6 6 6-6"></path></svg>
                    </button>
                </div>

                <div class="medicine-type-body">
                    @forelse($medicineType->items as $item)
                        <div class="linked-medicine-row">
                            <div class="linked-medicine-name">{{ $item->name }}</div>
                            <div class="linked-medicine-actions">
                                <button
                                    type="button"
                                    class="medicine-type-btn medicine-type-btn--edit"
                                    data-edit-medicine
                                    data-id="{{ $item->id }}"
                                    data-name="{{ e($item->name) }}"
                                    data-category="{{ e($item->category ?: 'Medicine') }}"
                                    data-stock-number="{{ e($item->stock_number) }}"
                                    data-starting-stock="{{ $item->starting_stock }}"
                                    data-consumed="{{ $item->consumed ?? 0 }}"
                                    data-quantity="{{ $item->quantity }}"
                                    data-unit="{{ e($item->unit ?: 'pcs') }}"
                                    data-minimum-stock="{{ $item->minimum_stock ?? 0 }}"
                                    data-dispensing-unit="{{ e($item->dispensing_unit) }}"
                                    data-units-per-stock-unit="{{ $item->units_per_stock_unit }}"
                                    data-date-added="{{ optional($item->date_added)->format('Y-m-d') ?: now()->toDateString() }}"
                                    data-medicine-type-id="{{ $medicineType->id }}"
                                    data-expiration-date="{{ optional($item->expiration_date)->format('Y-m-d') }}"
                                >
                                    <svg viewBox="0 0 24 24" aria-hidden="true"><path d="M12 20h9"></path><path d="M16.5 3.5a2.1 2.1 0 0 1 3 3L7 19l-4 1 1-4Z"></path></svg>
                                    <span>Edit</span>
                                </button>
                                <form action="{{ route('admin.inventory.delete', $item->id) }}" method="POST" onsubmit="return confirmItemDelete(event)">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="medicine-type-btn medicine-type-btn--delete">
                                        <svg viewBox="0 0 24 24" aria-hidden="true"><path d="M3 6h18"></path><path d="M8 6V4h8v2"></path><path d="M19 6l-1 14H6L5 6"></path><path d="M10 11v5"></path><path d="M14 11v5"></path></svg>
                                        <span>Delete</span>
                                    </button>
                                </form>
                            </div>
                        </div>
                    @empty
                        <div class="linked-medicine-row">
                            <div class="linked-medicine-name">No medicines are linked to this medicine type yet.</div>
                        </div>
                    @endforelse
                </div>
            </section>
        @empty
            <div class="medicine-types-empty">No medicine types found.</div>
        @endforelse
    </div>

    <div class="medicine-types-empty" id="medicineTypesEmpty" hidden>No medicine types matched your search.</div>
</div>

<div id="addMedicineTypeModal" class="medicine-modal-overlay" aria-hidden="true">
    <div class="medicine-modal-box" role="dialog" aria-modal="true" aria-labelledby="addMedicineTypeTitle">
        <div class="standard-modal-header">
            <div class="standard-modal-title">
                <span class="standard-modal-icon" aria-hidden="true">
                    <svg viewBox="0 0 24 24"><path d="m10.5 20.25 9.75-9.75a5.303 5.303 0 0 0-7.5-7.5L3 12.75a5.303 5.303 0 1 0 7.5 7.5Z"></path><path d="m8.625 7.125 8.25 8.25"></path></svg>
                </span>
                <div>
                    <h3 id="addMedicineTypeTitle">Add Medicine Type</h3>
                    <p>Create a new medicine type reference.</p>
                </div>
            </div>
            <button type="button" class="standard-modal-close" onclick="closeAddMedicineTypeModal()" aria-label="Close add medicine type modal">
                <svg viewBox="0 0 24 24" aria-hidden="true"><path d="M18 6 6 18"></path><path d="m6 6 12 12"></path></svg>
            </button>
        </div>

        <div class="standard-modal-body">
            <p class="standard-modal-note">Add a medicine type name used by inventory records and medicine reports.</p>

            <form action="{{ route('medicine-types.store') }}" method="POST" class="modal-stack">
                @csrf
                <label>
                    <span class="sr-only">Medicine Type Name</span>
                    <input type="text" name="name" class="form-control" placeholder="Medicine Type Name (e.g. Analgesic, Antibiotic)" required>
                </label>

                <div class="modal-actions">
                    <button type="button" class="btn-cancel" onclick="closeAddMedicineTypeModal()"><span>Cancel</span></button>
                    <button type="submit" class="btn-save"><span>Save Medicine Type</span></button>
                </div>
            </form>
        </div>
    </div>
</div>

<div id="editMedicineModal" class="medicine-modal-overlay" aria-hidden="true">
    <div class="medicine-modal-box" role="dialog" aria-modal="true" aria-labelledby="editMedicineTitle">
        <div class="standard-modal-header">
            <div class="standard-modal-title">
                <span class="standard-modal-icon" aria-hidden="true">
                    <svg viewBox="0 0 24 24"><path d="M12 20h9"></path><path d="M16.5 3.5a2.1 2.1 0 0 1 3 3L7 19l-4 1 1-4Z"></path></svg>
                </span>
                <div>
                    <h3 id="editMedicineTitle">Edit Medicine</h3>
                    <p>Update the linked medicine name.</p>
                </div>
            </div>
            <button type="button" class="standard-modal-close" onclick="closeEditMedicineModal()" aria-label="Close edit medicine modal">
                <svg viewBox="0 0 24 24" aria-hidden="true"><path d="M18 6 6 18"></path><path d="m6 6 12 12"></path></svg>
            </button>
        </div>

        <div class="standard-modal-body">
            <p class="standard-modal-note" id="editMedicineDisplayName"></p>

            <form id="editMedicineForm" method="POST" class="modal-stack">
                @csrf
                @method('PUT')

                <label>
                    <span class="sr-only">Medicine Name</span>
                    <input type="text" name="name" id="editMedicineName" class="form-control" placeholder="Medicine Name" required>
                </label>

                <input type="hidden" name="category" id="editMedicineCategory">
                <input type="hidden" name="stock_number" id="editMedicineStockNumber">
                <input type="hidden" name="starting_stock" id="editMedicineStartingStock">
                <input type="hidden" name="consumed" id="editMedicineConsumed">
                <input type="hidden" name="quantity" id="editMedicineQuantity">
                <input type="hidden" name="unit" id="editMedicineUnit">
                <input type="hidden" name="minimum_stock" id="editMedicineMinimumStock">
                <input type="hidden" name="dispensing_unit" id="editMedicineDispensingUnit">
                <input type="hidden" name="units_per_stock_unit" id="editMedicineUnitsPerStockUnit">
                <input type="hidden" name="date_added" id="editMedicineDateAdded">
                <input type="hidden" name="medicine_type_id" id="editMedicineTypeId">
                <input type="hidden" name="expiration_date" id="editMedicineExpirationDate">

                <div class="modal-actions">
                    <button type="button" class="btn-cancel" onclick="closeEditMedicineModal()"><span>Cancel</span></button>
                    <button type="submit" class="btn-save"><span>Save Changes</span></button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
const medicineTypeSearch = document.getElementById('medicineTypeSearch');
const medicineTypesCount = document.getElementById('medicineTypesCount');
const medicineTypesEmpty = document.getElementById('medicineTypesEmpty');
const addMedicineTypeModal = document.getElementById('addMedicineTypeModal');
const editMedicineModal = document.getElementById('editMedicineModal');

function pluralizeMedicineType(count) {
    return count === 1 ? 'medicine type' : 'medicine types';
}

function updateMedicineTypesView() {
    const searchValue = (medicineTypeSearch?.value || '').trim().toLowerCase();
    const groups = Array.from(document.querySelectorAll('[data-medicine-type-group]'));
    let visibleTotal = 0;

    groups.forEach(function(group) {
        const isVisible = !searchValue || (group.dataset.medicineTypeName || '').includes(searchValue);
        group.hidden = !isVisible;
        if (isVisible) {
            visibleTotal += 1;
        }
        if (searchValue) {
            group.classList.toggle('is-open', isVisible);
            group.querySelectorAll('[data-medicine-toggle]').forEach(function(toggle) {
                toggle.setAttribute('aria-expanded', isVisible ? 'true' : 'false');
            });
        }
    });

    if (medicineTypesCount) {
        medicineTypesCount.textContent = `Showing ${visibleTotal} ${pluralizeMedicineType(visibleTotal)}`;
    }
    if (medicineTypesEmpty) {
        medicineTypesEmpty.hidden = visibleTotal !== 0;
    }
}

document.querySelectorAll('[data-medicine-toggle]').forEach(function(button) {
    button.addEventListener('click', function() {
        const group = button.closest('[data-medicine-type-group]');
        const isOpen = !group.classList.contains('is-open');
        group.classList.toggle('is-open', isOpen);
        group.querySelectorAll('[data-medicine-toggle]').forEach(function(toggle) {
            toggle.setAttribute('aria-expanded', isOpen ? 'true' : 'false');
        });
    });
});

document.querySelectorAll('[data-edit-medicine]').forEach(function(button) {
    button.addEventListener('click', function() {
        openEditMedicineModal(button.dataset);
    });
});

function openAddMedicineTypeModal() {
    addMedicineTypeModal?.classList.add('is-open');
    addMedicineTypeModal?.setAttribute('aria-hidden', 'false');
}

function closeAddMedicineTypeModal() {
    addMedicineTypeModal?.classList.remove('is-open');
    addMedicineTypeModal?.setAttribute('aria-hidden', 'true');
}

function setEditField(id, value) {
    const field = document.getElementById(id);
    if (field) {
        field.value = value ?? '';
    }
}

function openEditMedicineModal(item) {
    const route = @json(route('admin.inventory.update', ['id' => '__ID__']));
    document.getElementById('editMedicineForm').action = route.replace('__ID__', item.id);
    document.getElementById('editMedicineDisplayName').innerText = 'Medicine: ' + (item.name || '');
    setEditField('editMedicineName', item.name);
    setEditField('editMedicineCategory', item.category || 'Medicine');
    setEditField('editMedicineStockNumber', item.stock_number);
    setEditField('editMedicineStartingStock', item.starting_stock || 0);
    setEditField('editMedicineConsumed', item.consumed || 0);
    setEditField('editMedicineQuantity', item.quantity || 0);
    setEditField('editMedicineUnit', item.unit || 'pcs');
    setEditField('editMedicineMinimumStock', item.minimum_stock || 0);
    setEditField('editMedicineDispensingUnit', item.dispensing_unit);
    setEditField('editMedicineUnitsPerStockUnit', item.units_per_stock_unit);
    setEditField('editMedicineDateAdded', item.date_added);
    setEditField('editMedicineTypeId', item.medicine_type_id);
    setEditField('editMedicineExpirationDate', item.expiration_date);
    editMedicineModal?.classList.add('is-open');
    editMedicineModal?.setAttribute('aria-hidden', 'false');
}

function closeEditMedicineModal() {
    editMedicineModal?.classList.remove('is-open');
    editMedicineModal?.setAttribute('aria-hidden', 'true');
}

function confirmMedicineTypeDelete(event) {
    if (!confirm('Are you sure you want to delete this medicine type? Linked medicines will be unset.')) {
        event.preventDefault();
        return false;
    }
    return true;
}

function confirmItemDelete(event) {
    if (!confirm('Are you sure you want to delete this medicine?')) {
        event.preventDefault();
        return false;
    }
    return true;
}

medicineTypeSearch?.addEventListener('input', updateMedicineTypesView);

window.addEventListener('click', function(event) {
    if (event.target === addMedicineTypeModal) {
        closeAddMedicineTypeModal();
    }
    if (event.target === editMedicineModal) {
        closeEditMedicineModal();
    }
});

document.addEventListener('keydown', function(event) {
    if (event.key === 'Escape') {
        closeAddMedicineTypeModal();
        closeEditMedicineModal();
    }
});

updateMedicineTypesView();
</script>
@endpush
