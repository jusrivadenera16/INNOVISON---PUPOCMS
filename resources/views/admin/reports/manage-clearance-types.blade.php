@extends('layouts.admin')

@section('title', 'Manage Clearance / Certificate Types')

@push('styles')
<style>
    .clearance-page,
    .clearance-modal-overlay {
        --clinic-maroon: #8f1024;
        --clinic-deep: #6d0718;
        --clinic-border: #ead4d7;
        --clinic-muted: #64748b;
    }

    .clearance-page { color: #201016; }
    .clearance-header {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 18px;
        margin-bottom: 28px;
    }
    .clearance-title-wrap {
        display: flex;
        align-items: center;
        gap: 14px;
        min-width: 0;
    }
    .clearance-title-icon {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        width: 46px;
        height: 46px;
        border-radius: 10px;
        color: #facc15;
        background: linear-gradient(135deg, var(--clinic-maroon), var(--clinic-deep));
        box-shadow: 0 12px 24px rgba(143, 16, 36, .18);
        flex: 0 0 auto;
    }
    .clearance-title-icon svg,
    .clearance-action svg,
    .clearance-field svg,
    .clearance-btn svg,
    .clearance-modal-icon svg,
    .clearance-modal-close svg {
        width: 18px;
        height: 18px;
        fill: none;
        stroke: currentColor;
        stroke-width: 2;
        stroke-linecap: round;
        stroke-linejoin: round;
    }
    .clearance-title-wrap h1 {
        margin: 0;
        color: var(--clinic-deep);
        font-size: 1.35rem;
        font-weight: 900;
        letter-spacing: 0;
    }
    .clearance-title-wrap p {
        margin: 4px 0 0;
        color: var(--clinic-muted);
        font-size: .88rem;
        font-weight: 600;
    }

    .clearance-toolbar {
        display: grid;
        grid-template-columns: minmax(240px, 1fr) auto;
        gap: 14px;
        align-items: center;
        margin-bottom: 18px;
    }
    .clearance-field {
        position: relative;
        display: flex;
        align-items: center;
        min-height: 48px;
        color: #64748b;
    }
    .clearance-field svg {
        position: absolute;
        left: 16px;
        pointer-events: none;
    }
    .clearance-field input,
    .clearance-modal .form-control {
        box-sizing: border-box;
        width: 100%;
        min-height: 48px;
        border: 1px solid #d9e1ec;
        border-radius: 9px;
        padding: 0 16px;
        color: #263241;
        background: #fff;
        font-size: .88rem;
        font-weight: 700;
        outline: none;
        transition: border-color .18s ease, box-shadow .18s ease;
    }
    .clearance-field input { padding-left: 42px; }
    .clearance-field input:focus,
    .clearance-modal .form-control:focus {
        border-color: rgba(143, 16, 36, .42);
        box-shadow: 0 0 0 4px rgba(143, 16, 36, .08);
    }

    .clearance-action,
    .clearance-btn,
    .clearance-btn-cancel,
    .clearance-btn-save,
    .clearance-modal-close {
        position: relative;
        overflow: hidden;
    }
    .clearance-action {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        gap: 9px;
        min-height: 48px;
        border: 1px solid rgba(250, 204, 21, .32);
        border-radius: 13px;
        padding: 0 22px;
        color: #fff;
        background: linear-gradient(135deg, var(--clinic-maroon), var(--clinic-deep));
        font-weight: 900;
        cursor: pointer;
        box-shadow: 0 12px 24px rgba(143, 16, 36, .18);
        transition: color .18s ease, background .18s ease, transform .18s ease, box-shadow .18s ease, border-color .18s ease;
        white-space: nowrap;
    }
    .clearance-action::after,
    .clearance-btn::after,
    .clearance-btn-cancel::after,
    .clearance-btn-save::after,
    .clearance-modal-close::after {
        content: "";
        position: absolute;
        inset: 0;
        background: linear-gradient(120deg, transparent 0%, rgba(255, 246, 179, .18) 25%, rgba(255, 246, 179, .72) 50%, rgba(255, 246, 179, .18) 75%, transparent 100%);
        transform: translateX(-135%);
        transition: transform .85s ease;
        pointer-events: none;
    }
    .clearance-action:hover,
    .clearance-btn:hover,
    .clearance-btn-cancel:hover,
    .clearance-btn-save:hover,
    .clearance-modal-close:hover {
        transform: translateY(-1px);
        color: var(--clinic-maroon);
        border-color: rgba(250, 204, 21, .9);
        background: #facc15;
        box-shadow: 0 10px 20px rgba(143, 16, 36, .14);
    }
    .clearance-action:hover::after,
    .clearance-btn:hover::after,
    .clearance-btn-cancel:hover::after,
    .clearance-btn-save:hover::after,
    .clearance-modal-close:hover::after { transform: translateX(135%); }
    .clearance-action > *,
    .clearance-btn > *,
    .clearance-modal-close > * {
        position: relative;
        z-index: 1;
    }

    .clearance-meta-row {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 12px;
        margin: 8px 0 16px;
    }
    .clearance-count {
        color: #263241;
        font-size: .86rem;
        font-weight: 800;
    }
    .clearance-list {
        display: grid;
        gap: 10px;
        max-height: clamp(320px, calc(100vh - 340px), 600px);
        overflow-y: auto;
        padding-right: 4px;
        scrollbar-width: thin;
    }
    .clearance-type-group {
        overflow: hidden;
        border: 1px solid var(--clinic-border);
        border-radius: 9px;
        background: #fff;
    }
    .clearance-type-group[hidden] { display: none; }
    .clearance-type-header {
        display: grid;
        grid-template-columns: 32px 32px minmax(0, 1fr) auto 28px;
        align-items: center;
        gap: 12px;
        min-height: 60px;
        padding: 10px 14px;
        color: var(--clinic-deep);
        background: linear-gradient(180deg, #fff8f9, #fff2f4);
    }
    .clearance-toggle,
    .clearance-chevron-button {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        width: 28px;
        height: 28px;
        border: 1px solid #efcfd4;
        border-radius: 6px;
        color: var(--clinic-maroon);
        background: #fff;
        cursor: pointer;
    }
    .clearance-toggle svg,
    .clearance-chevron-button svg {
        width: 16px;
        height: 16px;
        fill: none;
        stroke: currentColor;
        stroke-width: 2;
        stroke-linecap: round;
        stroke-linejoin: round;
        transition: transform .18s ease;
    }
    .clearance-type-group.is-open .clearance-toggle svg { transform: rotate(90deg); }
    .clearance-type-group.is-open .clearance-chevron-button svg { transform: rotate(180deg); }
    .clearance-letter {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        width: 30px;
        height: 30px;
        border-radius: 7px;
        color: #fff;
        background: var(--clinic-maroon);
        font-size: .78rem;
        font-weight: 900;
    }
    .clearance-header-name {
        min-width: 0;
        border: 0;
        padding: 0;
        overflow: hidden;
        color: var(--clinic-deep);
        background: transparent;
        font-size: .95rem;
        font-weight: 900;
        text-align: left;
        text-overflow: ellipsis;
        white-space: nowrap;
        cursor: pointer;
    }
    .clearance-parent-actions,
    .clearance-subcategory-actions {
        display: inline-flex;
        align-items: center;
        gap: 8px;
    }
    .clearance-parent-actions form,
    .clearance-subcategory-actions form { margin: 0; }
    .clearance-type-body { display: none; }
    .clearance-type-group.is-open .clearance-type-body { display: block; }
    .clearance-subcategory-row {
        display: grid;
        grid-template-columns: minmax(0, 1fr) auto;
        align-items: center;
        gap: 14px;
        min-height: 54px;
        padding: 9px 14px 9px 72px;
        border-top: 1px solid #eef1f5;
    }
    .clearance-subcategory-name {
        display: flex;
        align-items: center;
        gap: 12px;
        min-width: 0;
        color: #2c1820;
        font-size: .86rem;
        font-weight: 800;
    }
    .clearance-subcategory-number {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        width: 24px;
        height: 24px;
        border-radius: 6px;
        color: var(--clinic-maroon);
        background: #f8e7ea;
        font-size: .72rem;
        font-weight: 900;
        flex: 0 0 auto;
    }
    .clearance-subcategory-empty {
        padding: 18px 24px 18px 72px;
        border-top: 1px solid #eef1f5;
        color: var(--clinic-muted);
        font-size: .8rem;
        font-weight: 700;
    }
    .clearance-btn,
    .clearance-btn-cancel,
    .clearance-btn-save {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        gap: 8px;
        min-height: 38px;
        border-radius: 8px;
        padding: 0 14px;
        font-size: .8rem;
        font-weight: 900;
        cursor: pointer;
        transition: transform .16s ease, border-color .16s ease, box-shadow .16s ease, background .16s ease, color .16s ease;
    }
    .clearance-btn svg {
        width: 15px;
        height: 15px;
    }
    .clearance-btn--edit,
    .clearance-btn-cancel {
        color: var(--clinic-maroon);
        border: 1px solid #f0d8cc;
        background: #fffdfb;
    }
    .clearance-btn--delete,
    .clearance-btn-save {
        color: #fff;
        border: 1px solid rgba(250, 204, 21, .26);
        background: linear-gradient(135deg, var(--clinic-maroon), var(--clinic-deep));
    }
    .clearance-empty {
        border: 1px dashed var(--clinic-border);
        border-radius: 9px;
        padding: 24px;
        color: var(--clinic-muted);
        text-align: center;
        font-size: .86rem;
        font-weight: 700;
    }

    .clearance-modal-overlay {
        display: none;
        align-items: center;
        justify-content: center;
        position: fixed;
        inset: 0;
        z-index: 1200;
        padding: 20px;
        background: rgba(10, 18, 31, .72);
        backdrop-filter: blur(6px);
    }
    .clearance-modal-overlay.is-open { display: flex; }
    .clearance-modal {
        width: min(520px, 100%);
        overflow: hidden;
        border: 1px solid rgba(255, 255, 255, .16);
        border-radius: 12px;
        background: #fff;
        box-shadow: 0 28px 70px rgba(12, 20, 32, .34);
    }
    .clearance-modal-header {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 16px;
        padding: 18px 20px;
        color: #fff;
        background: linear-gradient(135deg, var(--clinic-maroon), var(--clinic-deep));
    }
    .clearance-modal-title {
        display: flex;
        align-items: center;
        gap: 12px;
        min-width: 0;
    }
    .clearance-modal-icon {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        width: 38px;
        height: 38px;
        border: 1px solid rgba(250, 204, 21, .45);
        border-radius: 9px;
        color: #facc15;
        background: rgba(255, 255, 255, .08);
        flex: 0 0 auto;
    }
    .clearance-modal-header .clearance-modal-title h3 {
        margin: 0;
        color: #fff !important;
        font-size: 1rem;
        font-weight: 900;
    }
    .clearance-modal-header .clearance-modal-title p {
        margin: 3px 0 0;
        color: rgba(255, 255, 255, .86) !important;
        font-size: .75rem;
        font-weight: 600;
    }
    .clearance-modal-close {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        width: 38px;
        height: 38px;
        border: 1px solid rgba(255, 255, 255, .22);
        border-radius: 999px;
        color: #fff;
        background: rgba(255, 255, 255, .08);
        cursor: pointer;
        flex: 0 0 auto;
        transition: color .18s ease, background .18s ease, border-color .18s ease, transform .18s ease;
    }
    .clearance-modal-body { padding: 24px; }
    .clearance-modal-note {
        margin: 0 0 18px;
        color: var(--clinic-muted);
        font-size: .84rem;
        font-weight: 700;
    }
    .clearance-form-stack {
        display: grid;
        gap: 14px;
    }
    .clearance-form-label {
        display: grid;
        gap: 7px;
        color: var(--clinic-deep);
        font-size: .75rem;
        font-weight: 900;
        text-transform: uppercase;
    }
    .clearance-form-error {
        border: 1px solid #fecaca;
        border-radius: 8px;
        padding: 10px 12px;
        color: #991b1b;
        background: #fef2f2;
        font-size: .78rem;
        font-weight: 800;
    }
    .clearance-modal-actions {
        display: flex;
        justify-content: flex-end;
        gap: 10px;
        margin-top: 6px;
    }

    html[data-theme="dark"] .clearance-page {
        --clinic-border: rgba(255, 255, 255, .12);
        color: #f8fafc;
    }
    html[data-theme="dark"] .clearance-title-wrap h1,
    html[data-theme="dark"] .clearance-count,
    html[data-theme="dark"] .clearance-header-name,
    html[data-theme="dark"] .clearance-subcategory-name,
    html[data-theme="dark"] .clearance-form-label { color: #f8fafc; }
    html[data-theme="dark"] .clearance-title-wrap p,
    html[data-theme="dark"] .clearance-modal-note { color: #cbd5e1; }
    html[data-theme="dark"] .clearance-field input,
    html[data-theme="dark"] .clearance-modal .form-control,
    html[data-theme="dark"] .clearance-type-group,
    html[data-theme="dark"] .clearance-modal {
        color: #f8fafc;
        border-color: rgba(255, 255, 255, .14);
        background: rgba(35, 17, 25, .96);
    }
    html[data-theme="dark"] .clearance-type-header {
        background: linear-gradient(180deg, rgba(112, 19, 27, .75), rgba(55, 20, 30, .86));
    }
    html[data-theme="dark"] .clearance-toggle,
    html[data-theme="dark"] .clearance-chevron-button {
        color: var(--clinic-maroon);
        border-color: rgba(250, 204, 21, .78);
        background: #facc15;
    }
    html[data-theme="dark"] .clearance-subcategory-row,
    html[data-theme="dark"] .clearance-subcategory-empty { border-top-color: rgba(255, 255, 255, .1); }
    html[data-theme="dark"] .clearance-btn--edit,
    html[data-theme="dark"] .clearance-btn-cancel,
    html[data-theme="dark"] .clearance-empty {
        color: #f8fafc;
        border-color: rgba(255, 255, 255, .14);
        background: rgba(18, 18, 18, .35);
    }
    html[data-theme="dark"] .clearance-action:hover,
    html[data-theme="dark"] .clearance-btn:hover,
    html[data-theme="dark"] .clearance-btn-cancel:hover,
    html[data-theme="dark"] .clearance-btn-save:hover,
    html[data-theme="dark"] .clearance-modal-close:hover {
        color: var(--clinic-maroon);
        border-color: rgba(250, 204, 21, .9);
        background: #facc15;
    }

    @media (max-width: 860px) {
        .clearance-header {
            align-items: flex-start;
            flex-direction: column;
        }
        .clearance-toolbar { grid-template-columns: 1fr; }
        .clearance-action,
        .clearance-field { width: 100%; }
    }
    @media (max-width: 640px) {
        .clearance-type-header {
            grid-template-columns: 30px 30px minmax(0, 1fr) 26px;
        }
        .clearance-parent-actions {
            grid-column: 3 / 5;
            flex-wrap: wrap;
        }
        .clearance-chevron-button {
            grid-column: 4;
            grid-row: 1;
        }
        .clearance-subcategory-row {
            grid-template-columns: 1fr;
            padding-left: 18px;
        }
        .clearance-subcategory-actions,
        .clearance-subcategory-actions form,
        .clearance-btn { width: 100%; }
        .clearance-modal-actions {
            display: grid;
            grid-template-columns: 1fr 1fr;
        }
    }
</style>
@endpush

@section('content')
@php
    $totalClearanceTypes = $clearanceTypes->count();
    $clearanceAlpha = function (int $index) {
        $label = '';
        $value = $index + 1;
        while ($value > 0) {
            $value--;
            $label = chr(65 + ($value % 26)) . $label;
            $value = intdiv($value, 26);
        }
        return $label;
    };
@endphp

<div class="clearance-page" id="clearance-types">
    <div class="clearance-header">
        <div class="clearance-title-wrap">
            <span class="clearance-title-icon" aria-hidden="true">
                <svg viewBox="0 0 24 24"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8Z"></path><path d="M14 2v6h6"></path><path d="M8 13h8"></path><path d="M8 17h5"></path></svg>
            </span>
            <div>
                <h1>Manage Clearance / Certificate Types</h1>
                <p>Manage the entries shown in Part II of the Medical Accomplishment Report.</p>
            </div>
        </div>
    </div>

    <div class="clearance-toolbar">
        <label class="clearance-field">
            <svg viewBox="0 0 24 24" aria-hidden="true"><circle cx="11" cy="11" r="7"></circle><path d="m20 20-3.5-3.5"></path></svg>
            <input type="search" id="clearanceSearch" placeholder="Search clearance types..." autocomplete="off">
        </label>

        <button type="button" class="clearance-action" onclick="openAddClearanceModal()">
            <svg viewBox="0 0 24 24" aria-hidden="true"><path d="M12 5v14"></path><path d="M5 12h14"></path></svg>
            <span>Add Clearance Type</span>
        </button>
    </div>

    <div class="clearance-meta-row">
        <div class="clearance-count" id="clearanceCount">Showing {{ $totalClearanceTypes }} clearance {{ $totalClearanceTypes === 1 ? 'type' : 'types' }}</div>
    </div>

    <div class="clearance-list" id="clearanceTypesList">
        @forelse($clearanceTypes as $clearanceType)
            <section
                class="clearance-type-group"
                data-clearance-group
                data-clearance-name="{{ strtolower($clearanceType->name . ' ' . $clearanceType->subcategories->pluck('name')->implode(' ')) }}"
            >
                <div class="clearance-type-header">
                    <button type="button" class="clearance-toggle" data-clearance-toggle aria-expanded="false" aria-label="Toggle {{ $clearanceType->name }} subcategories">
                        <svg viewBox="0 0 24 24" aria-hidden="true"><path d="m9 18 6-6-6-6"></path></svg>
                    </button>
                    <span class="clearance-letter">{{ $clearanceAlpha($loop->index) }}</span>
                    <button type="button" class="clearance-header-name" data-clearance-toggle aria-expanded="false">{{ $clearanceType->name }}</button>

                    <div class="clearance-parent-actions">
                        <button type="button" class="clearance-btn clearance-btn--edit" onclick="openAddSubcategoryModal('{{ $clearanceType->id }}', @js($clearanceType->name))">
                            <svg viewBox="0 0 24 24" aria-hidden="true"><path d="M12 5v14"></path><path d="M5 12h14"></path></svg>
                            <span>Add</span>
                        </button>
                        <button type="button" class="clearance-btn clearance-btn--edit" onclick="openEditClearanceModal('{{ $clearanceType->id }}', @js($clearanceType->name))">
                            <svg viewBox="0 0 24 24" aria-hidden="true"><path d="M12 20h9"></path><path d="M16.5 3.5a2.1 2.1 0 0 1 3 3L7 19l-4 1 1-4Z"></path></svg>
                            <span>Edit</span>
                        </button>
                        <form action="{{ route('mar-clearance-types.destroy', $clearanceType) }}" method="POST" onsubmit="return confirmClearanceDelete(event)">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="clearance-btn clearance-btn--delete">
                                <svg viewBox="0 0 24 24" aria-hidden="true"><path d="M3 6h18"></path><path d="M8 6V4h8v2"></path><path d="M19 6l-1 14H6L5 6"></path><path d="M10 11v5"></path><path d="M14 11v5"></path></svg>
                                <span>Remove</span>
                            </button>
                        </form>
                    </div>

                    <button type="button" class="clearance-chevron-button" data-clearance-toggle aria-expanded="false" aria-label="Toggle {{ $clearanceType->name }} subcategories">
                        <svg viewBox="0 0 24 24" aria-hidden="true"><path d="m6 9 6 6 6-6"></path></svg>
                    </button>
                </div>

                <div class="clearance-type-body">
                    @forelse($clearanceType->subcategories as $subcategory)
                        <div class="clearance-subcategory-row">
                            <div class="clearance-subcategory-name">
                                <span class="clearance-subcategory-number">{{ $loop->iteration }}</span>
                                <span>{{ $subcategory->name }}</span>
                            </div>
                            <div class="clearance-subcategory-actions">
                                <button type="button" class="clearance-btn clearance-btn--edit" onclick="openEditSubcategoryModal('{{ $subcategory->id }}', @js($subcategory->name), @js($clearanceType->name))">
                                    <svg viewBox="0 0 24 24" aria-hidden="true"><path d="M12 20h9"></path><path d="M16.5 3.5a2.1 2.1 0 0 1 3 3L7 19l-4 1 1-4Z"></path></svg>
                                    <span>Edit</span>
                                </button>
                                <form action="{{ route('mar-clearance-subcategories.destroy', $subcategory) }}" method="POST" onsubmit="return confirmClearanceSubcategoryDelete(event)">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="clearance-btn clearance-btn--delete">
                                        <svg viewBox="0 0 24 24" aria-hidden="true"><path d="M3 6h18"></path><path d="M8 6V4h8v2"></path><path d="M19 6l-1 14H6L5 6"></path><path d="M10 11v5"></path><path d="M14 11v5"></path></svg>
                                        <span>Remove</span>
                                    </button>
                                </form>
                            </div>
                        </div>
                    @empty
                        <div class="clearance-subcategory-empty">No subcategories yet. Use Add to create the first one.</div>
                    @endforelse
                </div>
            </section>
        @empty
            <div class="clearance-empty">No clearance or certificate types configured.</div>
        @endforelse
    </div>

    <div class="clearance-empty" id="clearanceEmpty" hidden>No clearance types matched your search.</div>
</div>

<div id="addClearanceModal" class="clearance-modal-overlay" aria-hidden="true">
    <div class="clearance-modal" role="dialog" aria-modal="true" aria-labelledby="addClearanceTitle">
        <div class="clearance-modal-header">
            <div class="clearance-modal-title">
                <span class="clearance-modal-icon" aria-hidden="true">
                    <svg viewBox="0 0 24 24"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8Z"></path><path d="M14 2v6h6"></path><path d="M12 12v6"></path><path d="M9 15h6"></path></svg>
                </span>
                <div>
                    <h3 id="addClearanceTitle">Add Clearance Type</h3>
                    <p>Create a new entry for Part II of the MAR.</p>
                </div>
            </div>
            <button type="button" class="clearance-modal-close" onclick="closeAddClearanceModal()" aria-label="Close add clearance type modal">
                <svg viewBox="0 0 24 24" aria-hidden="true"><path d="M18 6 6 18"></path><path d="m6 6 12 12"></path></svg>
            </button>
        </div>

        <div class="clearance-modal-body">
            <p class="clearance-modal-note">Enter the clearance or certificate name that should appear in the report.</p>

            <form action="{{ route('mar-clearance-types.store') }}" method="POST" class="clearance-form-stack">
                @csrf
                <input type="hidden" name="_clearance_form" value="add">

                @if($errors->any() && old('_clearance_form') === 'add')
                    <div class="clearance-form-error">{{ $errors->first() }}</div>
                @endif

                <label class="clearance-form-label">
                    Clearance / Certificate Name
                    <input type="text" name="name" class="form-control" value="{{ old('_clearance_form') === 'add' ? old('name') : '' }}" maxlength="160" placeholder="Example: Medical Certificate" required>
                </label>

                <div class="clearance-modal-actions">
                    <button type="button" class="clearance-btn-cancel" onclick="closeAddClearanceModal()">Cancel</button>
                    <button type="submit" class="clearance-btn-save">Save Type</button>
                </div>
            </form>
        </div>
    </div>
</div>

<div id="editClearanceModal" class="clearance-modal-overlay" aria-hidden="true">
    <div class="clearance-modal" role="dialog" aria-modal="true" aria-labelledby="editClearanceTitle">
        <div class="clearance-modal-header">
            <div class="clearance-modal-title">
                <span class="clearance-modal-icon" aria-hidden="true">
                    <svg viewBox="0 0 24 24"><path d="M12 20h9"></path><path d="M16.5 3.5a2.1 2.1 0 0 1 3 3L7 19l-4 1 1-4Z"></path></svg>
                </span>
                <div>
                    <h3 id="editClearanceTitle">Edit Clearance Type</h3>
                    <p>Update the name shown in the report.</p>
                </div>
            </div>
            <button type="button" class="clearance-modal-close" onclick="closeEditClearanceModal()" aria-label="Close edit clearance type modal">
                <svg viewBox="0 0 24 24" aria-hidden="true"><path d="M18 6 6 18"></path><path d="m6 6 12 12"></path></svg>
            </button>
        </div>

        <div class="clearance-modal-body">
            <p class="clearance-modal-note" id="editClearanceName"></p>

            <form id="editClearanceForm" method="POST" class="clearance-form-stack">
                @csrf
                @method('PUT')
                <input type="hidden" name="_clearance_form" value="edit">
                <input type="hidden" name="_clearance_type_id" id="editClearanceId">

                @if($errors->any() && old('_clearance_form') === 'edit')
                    <div class="clearance-form-error">{{ $errors->first() }}</div>
                @endif

                <label class="clearance-form-label">
                    Clearance / Certificate Name
                    <input type="text" name="name" id="editClearanceInput" class="form-control" maxlength="160" required>
                </label>

                <div class="clearance-modal-actions">
                    <button type="button" class="clearance-btn-cancel" onclick="closeEditClearanceModal()">Cancel</button>
                    <button type="submit" class="clearance-btn-save">Save Changes</button>
                </div>
            </form>
        </div>
    </div>
</div>

<div id="addSubcategoryModal" class="clearance-modal-overlay" aria-hidden="true">
    <div class="clearance-modal" role="dialog" aria-modal="true" aria-labelledby="addSubcategoryTitle">
        <div class="clearance-modal-header">
            <div class="clearance-modal-title">
                <span class="clearance-modal-icon" aria-hidden="true">
                    <svg viewBox="0 0 24 24"><path d="M12 5v14"></path><path d="M5 12h14"></path></svg>
                </span>
                <div>
                    <h3 id="addSubcategoryTitle">Add Subcategory</h3>
                    <p>Create a numbered entry under the selected clearance type.</p>
                </div>
            </div>
            <button type="button" class="clearance-modal-close" onclick="closeAddSubcategoryModal()" aria-label="Close add subcategory modal">
                <svg viewBox="0 0 24 24" aria-hidden="true"><path d="M18 6 6 18"></path><path d="m6 6 12 12"></path></svg>
            </button>
        </div>

        <div class="clearance-modal-body">
            <p class="clearance-modal-note" id="addSubcategoryParent"></p>
            <form id="addSubcategoryForm" method="POST" class="clearance-form-stack">
                @csrf
                <input type="hidden" name="_clearance_form" value="add_subcategory">
                <input type="hidden" name="_clearance_type_id" id="addSubcategoryTypeId">
                <input type="hidden" name="_clearance_type_name" id="addSubcategoryTypeName">

                @if($errors->any() && old('_clearance_form') === 'add_subcategory')
                    <div class="clearance-form-error">{{ $errors->first() }}</div>
                @endif

                <label class="clearance-form-label">
                    Subcategory Name
                    <input type="text" name="name" id="addSubcategoryInput" class="form-control" maxlength="160" placeholder="Enter subcategory name" required>
                </label>

                <div class="clearance-modal-actions">
                    <button type="button" class="clearance-btn-cancel" onclick="closeAddSubcategoryModal()">Cancel</button>
                    <button type="submit" class="clearance-btn-save">Save Subcategory</button>
                </div>
            </form>
        </div>
    </div>
</div>

<div id="editSubcategoryModal" class="clearance-modal-overlay" aria-hidden="true">
    <div class="clearance-modal" role="dialog" aria-modal="true" aria-labelledby="editSubcategoryTitle">
        <div class="clearance-modal-header">
            <div class="clearance-modal-title">
                <span class="clearance-modal-icon" aria-hidden="true">
                    <svg viewBox="0 0 24 24"><path d="M12 20h9"></path><path d="M16.5 3.5a2.1 2.1 0 0 1 3 3L7 19l-4 1 1-4Z"></path></svg>
                </span>
                <div>
                    <h3 id="editSubcategoryTitle">Edit Subcategory</h3>
                    <p>Update the numbered entry shown under its clearance type.</p>
                </div>
            </div>
            <button type="button" class="clearance-modal-close" onclick="closeEditSubcategoryModal()" aria-label="Close edit subcategory modal">
                <svg viewBox="0 0 24 24" aria-hidden="true"><path d="M18 6 6 18"></path><path d="m6 6 12 12"></path></svg>
            </button>
        </div>

        <div class="clearance-modal-body">
            <p class="clearance-modal-note" id="editSubcategoryParent"></p>
            <form id="editSubcategoryForm" method="POST" class="clearance-form-stack">
                @csrf
                @method('PUT')
                <input type="hidden" name="_clearance_form" value="edit_subcategory">
                <input type="hidden" name="_clearance_subcategory_id" id="editSubcategoryId">
                <input type="hidden" name="_clearance_type_name" id="editSubcategoryTypeName">

                @if($errors->any() && old('_clearance_form') === 'edit_subcategory')
                    <div class="clearance-form-error">{{ $errors->first() }}</div>
                @endif

                <label class="clearance-form-label">
                    Subcategory Name
                    <input type="text" name="name" id="editSubcategoryInput" class="form-control" maxlength="160" required>
                </label>

                <div class="clearance-modal-actions">
                    <button type="button" class="clearance-btn-cancel" onclick="closeEditSubcategoryModal()">Cancel</button>
                    <button type="submit" class="clearance-btn-save">Save Changes</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
const clearanceSearch = document.getElementById('clearanceSearch');
const clearanceCount = document.getElementById('clearanceCount');
const clearanceEmpty = document.getElementById('clearanceEmpty');
const addClearanceModal = document.getElementById('addClearanceModal');
const editClearanceModal = document.getElementById('editClearanceModal');
const addSubcategoryModal = document.getElementById('addSubcategoryModal');
const editSubcategoryModal = document.getElementById('editSubcategoryModal');

function clearanceTypeLabel(count) {
    return count === 1 ? 'type' : 'types';
}

function updateClearanceView() {
    const query = (clearanceSearch?.value || '').trim().toLowerCase();
    const groups = Array.from(document.querySelectorAll('[data-clearance-group]'));
    let visibleCount = 0;

    groups.forEach(function(group) {
        const isVisible = !query || (group.dataset.clearanceName || '').includes(query);
        group.hidden = !isVisible;
        if (isVisible) visibleCount += 1;
        if (query && isVisible) {
            setClearanceGroupOpen(group, true);
        }
    });

    if (clearanceCount) {
        clearanceCount.textContent = `Showing ${visibleCount} clearance ${clearanceTypeLabel(visibleCount)}`;
    }
    if (clearanceEmpty) {
        clearanceEmpty.hidden = groups.length === 0 || visibleCount !== 0;
    }
}

clearanceSearch?.addEventListener('input', updateClearanceView);

function setClearanceGroupOpen(group, isOpen) {
    group?.classList.toggle('is-open', isOpen);
    group?.querySelectorAll('[data-clearance-toggle]').forEach(function(toggle) {
        toggle.setAttribute('aria-expanded', isOpen ? 'true' : 'false');
    });
}

document.querySelectorAll('[data-clearance-toggle]').forEach(function(button) {
    button.addEventListener('click', function() {
        const group = button.closest('[data-clearance-group]');
        setClearanceGroupOpen(group, !group.classList.contains('is-open'));
    });
});

function showClearanceModal(modal) {
    modal?.classList.add('is-open');
    modal?.setAttribute('aria-hidden', 'false');
    document.body.style.overflow = 'hidden';
}

function hideClearanceModal(modal) {
    modal?.classList.remove('is-open');
    modal?.setAttribute('aria-hidden', 'true');
    if (!document.querySelector('.clearance-modal-overlay.is-open')) {
        document.body.style.overflow = '';
    }
}

function openAddClearanceModal() {
    showClearanceModal(addClearanceModal);
    window.setTimeout(function() {
        addClearanceModal?.querySelector('input[name="name"]')?.focus();
    }, 50);
}

function closeAddClearanceModal() {
    hideClearanceModal(addClearanceModal);
}

function openEditClearanceModal(id, name) {
    const route = @json(route('mar-clearance-types.update', ['marClearanceType' => '__ID__']));
    document.getElementById('editClearanceForm').action = route.replace('__ID__', id);
    document.getElementById('editClearanceId').value = id;
    document.getElementById('editClearanceInput').value = name;
    document.getElementById('editClearanceName').textContent = 'Editing: ' + name;
    showClearanceModal(editClearanceModal);
    window.setTimeout(function() {
        document.getElementById('editClearanceInput')?.focus();
    }, 50);
}

function closeEditClearanceModal() {
    hideClearanceModal(editClearanceModal);
}

function openAddSubcategoryModal(clearanceTypeId, clearanceTypeName) {
    const route = @json(route('mar-clearance-subcategories.store', ['marClearanceType' => '__ID__']));
    document.getElementById('addSubcategoryForm').action = route.replace('__ID__', clearanceTypeId);
    document.getElementById('addSubcategoryTypeId').value = clearanceTypeId;
    document.getElementById('addSubcategoryTypeName').value = clearanceTypeName;
    document.getElementById('addSubcategoryParent').textContent = 'Clearance type: ' + clearanceTypeName;
    showClearanceModal(addSubcategoryModal);
    window.setTimeout(function() {
        document.getElementById('addSubcategoryInput')?.focus();
    }, 50);
}

function closeAddSubcategoryModal() {
    hideClearanceModal(addSubcategoryModal);
}

function openEditSubcategoryModal(subcategoryId, subcategoryName, clearanceTypeName) {
    const route = @json(route('mar-clearance-subcategories.update', ['marClearanceSubcategory' => '__ID__']));
    document.getElementById('editSubcategoryForm').action = route.replace('__ID__', subcategoryId);
    document.getElementById('editSubcategoryId').value = subcategoryId;
    document.getElementById('editSubcategoryTypeName').value = clearanceTypeName;
    document.getElementById('editSubcategoryInput').value = subcategoryName;
    document.getElementById('editSubcategoryParent').textContent = 'Clearance type: ' + clearanceTypeName;
    showClearanceModal(editSubcategoryModal);
    window.setTimeout(function() {
        document.getElementById('editSubcategoryInput')?.focus();
    }, 50);
}

function closeEditSubcategoryModal() {
    hideClearanceModal(editSubcategoryModal);
}

function confirmClearanceDelete(event) {
    if (!confirm('Remove this clearance or certificate type? Types with linked consultations will be archived instead.')) {
        event.preventDefault();
        return false;
    }
    return true;
}

function confirmClearanceSubcategoryDelete(event) {
    if (!confirm('Remove this clearance subcategory?')) {
        event.preventDefault();
        return false;
    }
    return true;
}

window.addEventListener('click', function(event) {
    if (event.target === addClearanceModal) closeAddClearanceModal();
    if (event.target === editClearanceModal) closeEditClearanceModal();
    if (event.target === addSubcategoryModal) closeAddSubcategoryModal();
    if (event.target === editSubcategoryModal) closeEditSubcategoryModal();
});

document.addEventListener('keydown', function(event) {
    if (event.key === 'Escape') {
        closeAddClearanceModal();
        closeEditClearanceModal();
        closeAddSubcategoryModal();
        closeEditSubcategoryModal();
    }
});

updateClearanceView();

@if($errors->any() && old('_clearance_form') === 'add')
    openAddClearanceModal();
@elseif($errors->any() && old('_clearance_form') === 'edit' && old('_clearance_type_id'))
    openEditClearanceModal(@json(old('_clearance_type_id')), @json(old('name')));
@elseif($errors->any() && old('_clearance_form') === 'add_subcategory' && old('_clearance_type_id'))
    openAddSubcategoryModal(@json(old('_clearance_type_id')), @json(old('_clearance_type_name')));
    document.getElementById('addSubcategoryInput').value = @json(old('name'));
@elseif($errors->any() && old('_clearance_form') === 'edit_subcategory' && old('_clearance_subcategory_id'))
    openEditSubcategoryModal(@json(old('_clearance_subcategory_id')), @json(old('name')), @json(old('_clearance_type_name')));
@endif
</script>
@endpush
