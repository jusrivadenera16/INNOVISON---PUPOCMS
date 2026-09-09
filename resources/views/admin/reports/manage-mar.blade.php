@extends('layouts.admin')

@section('title', 'Manage Medical Conditions')

@push('styles')
<style>
    .conditions-page,
    .modal-overlay {
        --clinic-maroon: #8f1024;
        --clinic-deep: #6d0718;
        --clinic-border: #ead4d7;
        --clinic-muted: #64748b;
    }

    .conditions-page {
        color: #201016;
    }

    .conditions-header {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 18px;
        margin-bottom: 28px;
    }

    .conditions-title-wrap {
        display: flex;
        align-items: center;
        gap: 14px;
        min-width: 0;
    }

    .conditions-title-icon {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        width: 46px;
        height: 46px;
        border-radius: 10px;
        color: #facc15;
        background: linear-gradient(135deg, var(--clinic-maroon), var(--clinic-deep));
        box-shadow: 0 12px 24px rgba(143, 16, 36, .18);
    }

    .conditions-title-icon svg,
    .conditions-action svg,
    .conditions-field svg,
    .conditions-group-toggle svg,
    .condition-btn svg {
        width: 18px;
        height: 18px;
        fill: none;
        stroke: currentColor;
        stroke-width: 2;
        stroke-linecap: round;
        stroke-linejoin: round;
    }

    .conditions-title-wrap h1 {
        margin: 0;
        color: var(--clinic-deep);
        font-size: 1.35rem;
        font-weight: 900;
        letter-spacing: 0;
    }

    .conditions-title-wrap p {
        margin: 4px 0 0;
        color: var(--clinic-muted);
        font-size: .88rem;
        font-weight: 600;
    }

    .conditions-toolbar {
        display: grid;
        grid-template-columns: minmax(240px, 1fr) auto;
        gap: 14px;
        align-items: center;
        margin-bottom: 18px;
    }

    .conditions-field {
        position: relative;
        display: flex;
        align-items: center;
        min-height: 48px;
        color: #64748b;
    }

    .conditions-field svg {
        position: absolute;
        left: 16px;
        pointer-events: none;
    }

    .conditions-field input,
    .conditions-field select,
    .modal-box .form-control {
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

    .conditions-field select {
        appearance: none;
        cursor: pointer;
        padding-right: 42px;
    }

    .conditions-field::after {
        content: "";
        position: absolute;
        right: 17px;
        width: 8px;
        height: 8px;
        border-right: 2px solid #475569;
        border-bottom: 2px solid #475569;
        transform: translateY(-2px) rotate(45deg);
        pointer-events: none;
    }

    .conditions-field--search::after {
        display: none;
    }

    .conditions-field--custom-select::after {
        display: none;
    }

    .conditions-field--custom-select .clinic-select-wrap {
        width: 100%;
    }

    .conditions-field--custom-select .clinic-select-display {
        padding-left: 42px;
    }

    .conditions-field input:focus,
    .conditions-field select:focus,
    .modal-box .form-control:focus {
        border-color: rgba(143, 16, 36, .42);
        box-shadow: 0 0 0 4px rgba(143, 16, 36, .08);
    }

    .conditions-action {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        gap: 9px;
        position: relative;
        overflow: hidden;
        min-height: 48px;
        border-radius: 13px;
        border: 1px solid rgba(250, 204, 21, .32);
        padding: 0 22px;
        color: #fff;
        background: linear-gradient(135deg, var(--clinic-maroon), var(--clinic-deep));
        font-weight: 900;
        cursor: pointer;
        box-shadow: 0 12px 24px rgba(143, 16, 36, .18);
        transition: color .18s ease, background .18s ease, transform .18s ease, box-shadow .18s ease, border-color .18s ease;
        white-space: nowrap;
    }

    .conditions-action::after,
    .condition-btn::after,
    .btn-cancel::after,
    .btn-save::after {
        content: "";
        position: absolute;
        inset: 0;
        background: linear-gradient(120deg, transparent 0%, rgba(255, 246, 179, .18) 25%, rgba(255, 246, 179, .72) 50%, rgba(255, 246, 179, .18) 75%, transparent 100%);
        transform: translateX(-135%);
        transition: transform .85s ease;
        pointer-events: none;
    }

    .conditions-action:hover {
        transform: translateY(-1px);
        color: var(--clinic-maroon);
        border-color: rgba(250, 204, 21, .9);
        background: #facc15;
        box-shadow: 0 16px 30px rgba(143, 16, 36, .25);
    }

    .conditions-action:hover::after,
    .condition-btn:hover::after,
    .btn-cancel:hover::after,
    .btn-save:hover::after {
        transform: translateX(135%);
    }

    .conditions-meta-row {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 12px;
        margin: 8px 0 16px;
    }

    .conditions-count {
        color: #263241;
        font-size: .86rem;
        font-weight: 800;
    }

    .conditions-list {
        display: grid;
        gap: 10px;
        max-height: clamp(320px, calc(100vh - 340px), 600px);
        overflow-y: auto;
        padding-right: 4px;
        scrollbar-width: thin;
    }

    .conditions-group {
        overflow: hidden;
        border: 1px solid var(--clinic-border);
        border-radius: 9px;
        background: #fff;
    }

    .conditions-group.is-hidden,
    .condition-row.is-hidden {
        display: none;
    }

    .conditions-group-header {
        display: grid;
        grid-template-columns: 34px 32px minmax(0, 1fr) auto 28px;
        align-items: center;
        gap: 12px;
        width: 100%;
        min-height: 58px;
        border: 0;
        padding: 12px 18px;
        color: var(--clinic-deep);
        background: linear-gradient(180deg, #fff8f9, #fff2f4);
        cursor: pointer;
        text-align: left;
    }

    .conditions-group-toggle {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        width: 28px;
        height: 28px;
        border: 1px solid #efcfd4;
        border-radius: 6px;
        color: var(--clinic-maroon);
        background: #fff;
        transition: transform .18s ease;
    }

    .conditions-group.is-open .conditions-group-toggle {
        transform: rotate(90deg);
    }

    .conditions-category-code {
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

    .conditions-category-name {
        min-width: 0;
        overflow: hidden;
        text-overflow: ellipsis;
        white-space: nowrap;
        font-size: .95rem;
        font-weight: 900;
    }

    .conditions-pill {
        border-radius: 999px;
        padding: 7px 12px;
        color: var(--clinic-maroon);
        background: #f8e7ea;
        font-size: .75rem;
        font-weight: 900;
        white-space: nowrap;
    }

    .conditions-chevron {
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

    .conditions-group.is-open .conditions-chevron {
        transform: rotate(180deg);
    }

    .conditions-group-body {
        display: none;
        max-height: 300px;
        overflow-y: auto;
        scrollbar-width: thin;
    }

    .conditions-group.is-open .conditions-group-body {
        display: block;
    }

    .condition-row {
        display: grid;
        grid-template-columns: 1fr auto;
        align-items: center;
        gap: 16px;
        min-height: 54px;
        padding: 10px 14px 10px 34px;
        border-top: 1px solid #eef1f5;
    }

    .condition-name {
        display: flex;
        align-items: center;
        gap: 18px;
        min-width: 0;
        color: #2c1820;
        font-size: .9rem;
        font-weight: 800;
    }

    .condition-name::before {
        content: "";
        width: 7px;
        height: 7px;
        border-radius: 999px;
        background: var(--clinic-maroon);
        flex: 0 0 auto;
    }

    .condition-actions {
        display: inline-flex;
        align-items: center;
        gap: 10px;
    }

    .condition-btn,
    .btn-cancel,
    .btn-save {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        gap: 8px;
        position: relative;
        overflow: hidden;
        min-height: 38px;
        border-radius: 8px;
        padding: 0 14px;
        font-size: .8rem;
        font-weight: 900;
        cursor: pointer;
        transition: transform .16s ease, border-color .16s ease, box-shadow .16s ease, background .16s ease;
    }

    .condition-btn svg {
        width: 15px;
        height: 15px;
    }

    .condition-btn--edit,
    .btn-cancel {
        color: var(--clinic-maroon);
        border: 1px solid #f0d8cc;
        background: #fffdfb;
    }

    .condition-btn--delete {
        color: #fff;
        border: 1px solid rgba(250, 204, 21, .26);
        background: linear-gradient(135deg, var(--clinic-maroon), var(--clinic-deep));
    }

    .btn-save {
        color: #fff;
        border: 1px solid rgba(250, 204, 21, .32);
        background: linear-gradient(135deg, var(--clinic-maroon), var(--clinic-deep));
    }

    .condition-btn:hover,
    .btn-cancel:hover,
    .btn-save:hover {
        transform: translateY(-1px);
        color: var(--clinic-maroon);
        border-color: rgba(250, 204, 21, .9);
        background: #facc15;
        box-shadow: 0 10px 20px rgba(143, 16, 36, .14);
    }

    .condition-btn span,
    .condition-btn svg,
    .conditions-action span,
    .conditions-action svg,
    .btn-cancel,
    .btn-save {
        z-index: 1;
    }

    .clinic-select-wrap {
        position: relative;
    }

    .clinic-select-native {
        position: absolute;
        width: 1px !important;
        height: 1px !important;
        opacity: 0;
        pointer-events: none;
        padding: 0 !important;
        border: 0 !important;
    }

    .clinic-select-display {
        position: relative;
        width: 100%;
        min-height: 48px;
        border: 1px solid #d9e1ec;
        border-radius: 9px;
        padding: 0 44px 0 16px;
        color: #263241;
        background: #fff;
        font-size: .88rem;
        font-weight: 800;
        text-align: left;
        cursor: pointer;
        transition: border-color .18s ease, box-shadow .18s ease;
    }

    .clinic-select-display::after {
        content: "";
        position: absolute;
        top: 50%;
        right: 17px;
        width: 8px;
        height: 8px;
        border-right: 2px solid var(--clinic-maroon);
        border-bottom: 2px solid var(--clinic-maroon);
        transform: translateY(-65%) rotate(45deg);
        transition: transform .18s ease;
    }

    .clinic-select-display:hover,
    .clinic-select-display:focus,
    .clinic-select-display.is-open {
        outline: none;
        border-color: rgba(143, 16, 36, .42);
        box-shadow: 0 0 0 4px rgba(143, 16, 36, .08);
    }

    .clinic-select-display.is-open::after {
        transform: translateY(-25%) rotate(225deg);
    }

    .clinic-select-menu {
        position: absolute;
        top: calc(100% + 10px);
        left: 0;
        right: 0;
        z-index: 1010;
        display: none;
        flex-direction: column;
        gap: 8px;
        max-height: 240px;
        overflow-y: auto;
        padding: 12px;
        border: 1px solid rgba(139, 0, 0, .12);
        border-radius: 14px;
        background: rgba(255,255,255,.98);
        box-shadow: 0 18px 34px rgba(15, 23, 42, .14);
    }

    .clinic-select-wrap.is-open .clinic-select-menu {
        display: flex;
    }

    .clinic-select-option {
        width: 100%;
        min-height: 40px;
        position: relative;
        overflow: hidden;
        border: 1px solid #efcfd4;
        border-radius: 8px;
        padding: 9px 13px;
        color: var(--clinic-maroon);
        background: #fff;
        font-size: .82rem;
        font-weight: 800;
        text-align: left;
        cursor: pointer;
        transition: color .18s ease, background .18s ease, border-color .18s ease, transform .18s ease, box-shadow .18s ease;
    }

    .clinic-select-option::after {
        content: "";
        position: absolute;
        inset: 0;
        background: linear-gradient(120deg, transparent 0%, rgba(255, 246, 179, .18) 25%, rgba(255, 246, 179, .72) 50%, rgba(255, 246, 179, .18) 75%, transparent 100%);
        transform: translateX(-135%);
        transition: transform .85s ease;
        pointer-events: none;
    }

    .clinic-select-option.is-selected {
        color: #fff;
        border-color: var(--clinic-maroon);
        background: linear-gradient(135deg, var(--clinic-maroon), var(--clinic-deep));
    }

    .clinic-select-option:hover {
        color: var(--clinic-maroon);
        border-color: rgba(250, 204, 21, .9);
        background: #facc15;
        transform: translateY(-1px);
        box-shadow: 0 8px 18px rgba(143, 16, 36, .12);
    }

    .clinic-select-option:hover::after {
        transform: translateX(135%);
    }

    .clinic-select-option span {
        position: relative;
        z-index: 1;
    }

    .conditions-empty {
        border: 1px dashed #e8cfd3;
        border-radius: 10px;
        padding: 24px;
        color: #64748b;
        background: #fff9fa;
        text-align: center;
        font-weight: 800;
    }

    .modal-overlay {
        display: none;
        position: fixed;
        inset: 0;
        z-index: 1000;
        align-items: center;
        justify-content: center;
        padding: 18px;
        background: rgba(15, 23, 42, .45);
    }

    .modal-overlay.is-open {
        display: flex;
    }

    .modal-box {
        width: min(100%, 500px);
        max-height: 90vh;
        overflow-y: auto;
        border: 1px solid #f0d8cc;
        border-radius: 14px;
        padding: 0;
        background: #fff;
        box-shadow: 0 24px 60px rgba(15, 23, 42, .22);
    }

    #addConditionModal .modal-box,
    #changeModal .modal-box {
        overflow: visible;
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
        display: inline-flex;
        align-items: center;
        justify-content: center;
        width: 40px;
        height: 40px;
        border-radius: 10px;
        color: #facc15;
        border: 1px solid rgba(250, 204, 21, .28);
        background: rgba(255,255,255,.1);
        flex: 0 0 auto;
    }

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
        display: inline-flex;
        align-items: center;
        justify-content: center;
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
        flex: 0 0 auto;
    }

    .standard-modal-close::after {
        content: "";
        position: absolute;
        inset: 0;
        background: linear-gradient(120deg, transparent 0%, rgba(255, 246, 179, .18) 25%, rgba(255, 246, 179, .72) 50%, rgba(255, 246, 179, .18) 75%, transparent 100%);
        transform: translateX(-135%);
        transition: transform .85s ease;
        pointer-events: none;
    }

    .standard-modal-close:hover {
        color: var(--clinic-maroon);
        border-color: rgba(250, 204, 21, .9);
        background: #facc15;
        box-shadow: 0 12px 24px rgba(250, 204, 21, .22);
        transform: translateY(-1px);
    }

    .standard-modal-close:hover::after {
        transform: translateX(135%);
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
        gap: 14px;
    }

    .modal-actions {
        display: flex;
        justify-content: flex-end;
        gap: 10px;
        margin-top: 20px;
    }

    .sr-only {
        position: absolute;
        width: 1px;
        height: 1px;
        padding: 0;
        margin: -1px;
        overflow: hidden;
        clip: rect(0, 0, 0, 0);
        white-space: nowrap;
        border: 0;
    }

    html[data-theme="dark"] .conditions-page {
        --clinic-border: rgba(255,255,255,.12);
        color: #f8fafc;
    }

    html[data-theme="dark"] .conditions-title-wrap h1,
    html[data-theme="dark"] .conditions-count,
    html[data-theme="dark"] .condition-name,
    html[data-theme="dark"] .modal-box h3 {
        color: #f8fafc;
    }

    html[data-theme="dark"] .conditions-title-wrap p,
    html[data-theme="dark"] .standard-modal-note {
        color: #cbd5e1;
    }

    html[data-theme="dark"] .standard-modal-header {
        background: linear-gradient(135deg, #8f1024, #5f0715);
    }

    html[data-theme="dark"] .conditions-field input,
    html[data-theme="dark"] .conditions-field select,
    html[data-theme="dark"] .modal-box .form-control,
    html[data-theme="dark"] .clinic-select-display,
    html[data-theme="dark"] .clinic-select-menu,
    html[data-theme="dark"] .conditions-group,
    html[data-theme="dark"] .modal-box {
        color: #f8fafc;
        border-color: rgba(255,255,255,.14);
        background: rgba(35, 17, 25, .96);
    }

    html[data-theme="dark"] .conditions-group-header {
        color: #f8fafc;
        background: linear-gradient(180deg, rgba(112, 19, 27, .75), rgba(55, 20, 30, .86));
    }

    html[data-theme="dark"] .conditions-group-toggle {
        color: var(--clinic-maroon);
        border-color: rgba(250, 204, 21, .78);
        background: #facc15;
    }

    html[data-theme="dark"] .conditions-chevron {
        color: #facc15;
    }

    html[data-theme="dark"] .condition-row {
        border-top-color: rgba(255,255,255,.1);
    }

    html[data-theme="dark"] .conditions-empty,
    html[data-theme="dark"] .condition-btn--edit,
    html[data-theme="dark"] .btn-cancel {
        color: #f8fafc;
        border-color: rgba(255,255,255,.14);
        background: rgba(18, 18, 18, .35);
    }

    html[data-theme="dark"] .clinic-select-option {
        color: #f8fafc;
        border-color: rgba(255,255,255,.12);
        background: rgba(18, 18, 18, .35);
    }

    html[data-theme="dark"] .clinic-select-option.is-selected {
        color: #fff;
        border-color: rgba(250, 204, 21, .28);
        background: linear-gradient(135deg, var(--clinic-maroon), var(--clinic-deep));
    }

    html[data-theme="dark"] .clinic-select-option:hover {
        color: var(--clinic-maroon);
        border-color: rgba(250, 204, 21, .9);
        background: #facc15;
    }

    html[data-theme="dark"] .condition-btn:hover,
    html[data-theme="dark"] .conditions-action:hover,
    html[data-theme="dark"] .btn-cancel:hover,
    html[data-theme="dark"] .btn-save:hover {
        color: var(--clinic-maroon);
        border-color: rgba(250, 204, 21, .9);
        background: #facc15;
    }

    @media (max-width: 860px) {
        .conditions-header {
            align-items: flex-start;
            flex-direction: column;
        }

        .conditions-toolbar {
            grid-template-columns: 1fr;
        }

        .conditions-action,
        .conditions-field--search {
            width: 100%;
            max-width: none;
        }

        .conditions-meta-row {
            align-items: stretch;
            flex-direction: column;
        }
    }

    @media (max-width: 640px) {
        .conditions-group-header {
            grid-template-columns: 30px 30px minmax(0, 1fr) 24px;
        }

        .conditions-pill {
            grid-column: 3 / 4;
            width: fit-content;
            margin-top: 4px;
        }

        .conditions-chevron {
            grid-column: 4;
            grid-row: 1 / 3;
        }

        .condition-row {
            grid-template-columns: 1fr;
            padding-left: 18px;
        }

        .condition-actions,
        .condition-actions form,
        .condition-btn {
            width: 100%;
        }
    }
</style>
@endpush

@section('content')
@php
    $conditionsByCategory = $allConditions->groupBy('category_id');
    $totalConditions = $allConditions->count();
@endphp

<div class="conditions-page" id="medical-conditions">
    <div class="conditions-header">
        <div class="conditions-title-wrap">
            <span class="conditions-title-icon" aria-hidden="true">
                <svg viewBox="0 0 24 24"><path d="M21 8.25c0-2.485-2.099-4.5-4.688-4.5-1.935 0-3.597 1.126-4.312 2.733-.715-1.607-2.377-2.733-4.313-2.733C5.1 3.75 3 5.765 3 8.25c0 7.22 9 12 9 12s9-4.78 9-12Z"></path></svg>
            </span>
            <div>
                <h1>Manage Medical Conditions</h1>
                <p>Manage condition names under each MAR category.</p>
            </div>
        </div>
    </div>

    <div class="conditions-toolbar">
        <label class="conditions-field conditions-field--search">
            <svg viewBox="0 0 24 24" aria-hidden="true"><circle cx="11" cy="11" r="7"></circle><path d="m20 20-3.5-3.5"></path></svg>
            <input type="search" id="conditionSearch" placeholder="Search conditions..." autocomplete="off">
        </label>

        <button type="button" class="conditions-action" onclick="openAddConditionModal()">
            <svg viewBox="0 0 24 24" aria-hidden="true"><path d="M12 5v14"></path><path d="M5 12h14"></path></svg>
            <span>Add Condition</span>
        </button>
    </div>

    <div class="conditions-meta-row">
        <div class="conditions-count" id="conditionsCount">Showing {{ $totalConditions }} medical conditions</div>
    </div>

    <div class="conditions-list" id="conditionsList">
        @forelse($categoryList as $category)
            @php
                $categoryConditions = $conditionsByCategory->get($category->id, collect())->sortBy('name')->values();
                $categoryCode = strtoupper((string) ($category->code ?? substr((string) $category->name, 0, 1)));
            @endphp
            <section
                class="conditions-group"
                data-condition-group
                data-category-id="{{ $category->id }}"
                data-category-name="{{ strtolower($categoryCode . ' ' . $category->name) }}"
            >
                <button type="button" class="conditions-group-header" data-accordion-toggle aria-expanded="false">
                    <span class="conditions-group-toggle" aria-hidden="true">
                        <svg viewBox="0 0 24 24"><path d="m9 18 6-6-6-6"></path></svg>
                    </span>
                    <span class="conditions-category-code">{{ $categoryCode }}</span>
                    <span class="conditions-category-name">{{ $category->name }}</span>
                    <span class="conditions-pill" data-group-count>{{ $categoryConditions->count() }} {{ $categoryConditions->count() === 1 ? 'condition' : 'conditions' }}</span>
                    <svg class="conditions-chevron" viewBox="0 0 24 24" aria-hidden="true"><path d="m6 9 6 6 6-6"></path></svg>
                </button>
                <div class="conditions-group-body">
                    @forelse($categoryConditions as $cond)
                        <div class="condition-row" data-condition-row data-condition-name="{{ strtolower($cond->name) }}">
                            <div class="condition-name">{{ $cond->name }}</div>
                            <div class="condition-actions">
                                <button type="button" class="condition-btn condition-btn--edit" onclick="openChangeModal('{{ $cond->id }}', '{{ $cond->category_id }}', @js($cond->name))">
                                    <svg viewBox="0 0 24 24" aria-hidden="true"><path d="M12 20h9"></path><path d="M16.5 3.5a2.1 2.1 0 0 1 3 3L7 19l-4 1 1-4Z"></path></svg>
                                    <span>Edit</span>
                                </button>
                                <form action="{{ route('conditions.destroy', $cond->id) }}" method="POST" onsubmit="return confirmDelete(event)">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="condition-btn condition-btn--delete">
                                        <svg viewBox="0 0 24 24" aria-hidden="true"><path d="M3 6h18"></path><path d="M8 6V4h8v2"></path><path d="M19 6l-1 14H6L5 6"></path><path d="M10 11v5"></path><path d="M14 11v5"></path></svg>
                                        <span>Delete</span>
                                    </button>
                                </form>
                            </div>
                        </div>
                    @empty
                        <div class="condition-row" data-empty-row>
                            <div class="condition-name">No conditions yet</div>
                        </div>
                    @endforelse
                </div>
            </section>
        @empty
            <div class="conditions-empty">No categories found.</div>
        @endforelse
    </div>

    <div class="conditions-empty" id="conditionsEmpty" hidden>No medical conditions matched the current filters.</div>
</div>


<div id="addConditionModal" class="modal-overlay" aria-hidden="true">
    <div class="modal-box" role="dialog" aria-modal="true" aria-labelledby="addConditionTitle">
        <div class="standard-modal-header">
            <div class="standard-modal-title">
                <span class="standard-modal-icon" aria-hidden="true">
                    <svg viewBox="0 0 24 24"><path d="M9 3h6v5h5v13H4V8h5V3Z"></path><path d="M10 14h4"></path><path d="M12 12v4"></path></svg>
                </span>
                <div>
                    <h3 id="addConditionTitle">Add Condition</h3>
                    <p>Assign a new condition to a MAR category.</p>
                </div>
            </div>
            <button type="button" class="standard-modal-close" onclick="closeAddConditionModal()" aria-label="Close add condition modal">
                <svg viewBox="0 0 24 24" aria-hidden="true"><path d="M18 6 6 18"></path><path d="m6 6 12 12"></path></svg>
            </button>
        </div>

        <div class="standard-modal-body">
            <p class="standard-modal-note">Add a condition name and assign it to a MAR category.</p>

            <form action="{{ route('conditions.store') }}" method="POST" class="modal-stack">
                @csrf
                <label class="clinic-select-wrap" data-clinic-select data-select-placeholder="Select Category">
                    <span class="sr-only">Category</span>
                    <select name="category_id" class="form-control clinic-select-native" required>
                        <option value="">Select Category</option>
                        @foreach($categoryList as $c)
                            <option value="{{ $c->id }}">Category {{ $c->code }} - {{ $c->name }}</option>
                        @endforeach
                    </select>
                    <button type="button" class="clinic-select-display" aria-haspopup="listbox" aria-expanded="false">Select Category</button>
                    <div class="clinic-select-menu" role="listbox" aria-label="Category options">
                        @foreach($categoryList as $c)
                            <button type="button" class="clinic-select-option" data-select-value="{{ $c->id }}">Category {{ $c->code }} - {{ $c->name }}</button>
                        @endforeach
                    </div>
                </label>
                <label>
                    <span class="sr-only">Condition Name</span>
                    <input type="text" name="name" class="form-control" placeholder="Condition Name" required>
                </label>

                <div class="modal-actions">
                    <button type="button" class="btn-cancel" onclick="closeAddConditionModal()">Cancel</button>
                    <button type="submit" class="btn-save">Save Condition</button>
                </div>
            </form>
        </div>
    </div>
</div>

<div id="changeModal" class="modal-overlay" aria-hidden="true">
    <div class="modal-box" role="dialog" aria-modal="true" aria-labelledby="changeConditionTitle">
        <div class="standard-modal-header">
            <div class="standard-modal-title">
                <span class="standard-modal-icon" aria-hidden="true">
                    <svg viewBox="0 0 24 24"><path d="M12 20h9"></path><path d="M16.5 3.5a2.1 2.1 0 0 1 3 3L7 19l-4 1 1-4Z"></path></svg>
                </span>
                <div>
                    <h3 id="changeConditionTitle">Edit Condition</h3>
                    <p>Update the condition name or MAR category.</p>
                </div>
            </div>
            <button type="button" class="standard-modal-close" onclick="closeChangeModal()" aria-label="Close edit condition modal">
                <svg viewBox="0 0 24 24" aria-hidden="true"><path d="M18 6 6 18"></path><path d="m6 6 12 12"></path></svg>
            </button>
        </div>

        <div class="standard-modal-body">
            <p class="standard-modal-note" id="conditionDisplayName"></p>

            <form id="changeForm" method="POST" class="modal-stack">
                @csrf
                @method('PUT')

                <label>
                    <span class="sr-only">Condition Name</span>
                    <input type="text" name="name" id="modalConditionName" class="form-control" placeholder="Condition Name" required>
                </label>

                <div class="clinic-select-wrap" data-clinic-select data-select-placeholder="Select Category">
                    <select name="category_id" id="modalCategoryId" class="form-control clinic-select-native" required>
                        @foreach($categoryList as $c)
                            <option value="{{ $c->id }}">Category {{ $c->code }} - {{ $c->name }}</option>
                        @endforeach
                    </select>
                    <button type="button" class="clinic-select-display" aria-haspopup="listbox" aria-expanded="false">Select Category</button>
                    <div class="clinic-select-menu" role="listbox" aria-label="Category options">
                        @foreach($categoryList as $c)
                            <button type="button" class="clinic-select-option" data-select-value="{{ $c->id }}">Category {{ $c->code }} - {{ $c->name }}</button>
                        @endforeach
                    </div>
                </div>

                <div class="modal-actions">
                    <button type="button" class="btn-cancel" onclick="closeChangeModal()">Cancel</button>
                    <button type="submit" class="btn-save">Save Changes</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
const conditionSearch = document.getElementById('conditionSearch');
const conditionsList = document.getElementById('conditionsList');
const conditionsCount = document.getElementById('conditionsCount');
const conditionsEmpty = document.getElementById('conditionsEmpty');
const addConditionModal = document.getElementById('addConditionModal');
const changeModal = document.getElementById('changeModal');
const clinicSelects = Array.from(document.querySelectorAll('[data-clinic-select]'));

function pluralizeCondition(count) {
    return count === 1 ? 'condition' : 'conditions';
}

function updateConditionsView() {
    const searchValue = (conditionSearch?.value || '').trim().toLowerCase();
    const groups = Array.from(document.querySelectorAll('[data-condition-group]'));
    let visibleTotal = 0;

    groups.forEach(function(group) {
        const rows = Array.from(group.querySelectorAll('[data-condition-row]'));
        let visibleInGroup = 0;

        rows.forEach(function(row) {
            const conditionMatches = !searchValue || (row.dataset.conditionName || '').includes(searchValue);
            const isVisible = conditionMatches;
            row.classList.toggle('is-hidden', !isVisible);
            if (isVisible) {
                visibleInGroup += 1;
            }
        });

        group.classList.toggle('is-hidden', visibleInGroup === 0);
        const groupCount = group.querySelector('[data-group-count]');
        if (groupCount) {
            groupCount.textContent = `${visibleInGroup} ${pluralizeCondition(visibleInGroup)}`;
        }
        visibleTotal += visibleInGroup;
    });

    if (conditionsCount) {
        conditionsCount.textContent = `Showing ${visibleTotal} medical ${pluralizeCondition(visibleTotal)}`;
    }
    if (conditionsEmpty) {
        conditionsEmpty.hidden = visibleTotal !== 0;
    }
}

document.querySelectorAll('[data-accordion-toggle]').forEach(function(button) {
    button.addEventListener('click', function() {
        const group = button.closest('[data-condition-group]');
        const isOpen = !group.classList.contains('is-open');
        group.classList.toggle('is-open', isOpen);
        button.setAttribute('aria-expanded', isOpen ? 'true' : 'false');
    });
});

conditionSearch?.addEventListener('input', updateConditionsView);

function closeClinicSelect(wrap) {
    wrap?.classList.remove('is-open');
    const display = wrap?.querySelector('.clinic-select-display');
    display?.classList.remove('is-open');
    display?.setAttribute('aria-expanded', 'false');
}

function syncClinicSelect(wrap) {
    const select = wrap?.querySelector('.clinic-select-native');
    const display = wrap?.querySelector('.clinic-select-display');
    const options = Array.from(wrap?.querySelectorAll('.clinic-select-option') || []);
    if (!select || !display) return;

    const selectedOption = select.options[select.selectedIndex];
    display.textContent = selectedOption && selectedOption.value
        ? selectedOption.text.trim()
        : (wrap.dataset.selectPlaceholder || 'Select option');

    options.forEach(function(option) {
        option.classList.toggle('is-selected', option.dataset.selectValue === select.value);
    });
}

function initializeClinicSelect(wrap) {
    const select = wrap?.querySelector('.clinic-select-native');
    const display = wrap?.querySelector('.clinic-select-display');
    const options = Array.from(wrap?.querySelectorAll('.clinic-select-option') || []);
    if (!select || !display) return;

    display.addEventListener('click', function(event) {
        event.preventDefault();
        const isOpen = wrap.classList.contains('is-open');
        clinicSelects.forEach(function(otherWrap) {
            if (otherWrap !== wrap) closeClinicSelect(otherWrap);
        });
        wrap.classList.toggle('is-open', !isOpen);
        display.classList.toggle('is-open', !isOpen);
        display.setAttribute('aria-expanded', !isOpen ? 'true' : 'false');
    });

    options.forEach(function(option) {
        option.addEventListener('click', function(event) {
            event.preventDefault();
            select.value = option.dataset.selectValue || '';
            select.dispatchEvent(new Event('change', { bubbles: true }));
            syncClinicSelect(wrap);
            closeClinicSelect(wrap);
        });
    });

    select.addEventListener('change', function() {
        syncClinicSelect(wrap);
    });
    syncClinicSelect(wrap);
}

function openAddConditionModal() {
    addConditionModal?.classList.add('is-open');
    addConditionModal?.setAttribute('aria-hidden', 'false');
    clinicSelects.forEach(syncClinicSelect);
}

function closeAddConditionModal() {
    addConditionModal?.classList.remove('is-open');
    addConditionModal?.setAttribute('aria-hidden', 'true');
}

function openChangeModal(id, categoryId, name) {
    document.getElementById('conditionDisplayName').innerText = 'Condition: ' + name;
    document.getElementById('modalConditionName').value = name;
    document.getElementById('modalCategoryId').value = categoryId;
    clinicSelects.forEach(syncClinicSelect);

    const route = @json(route('conditions.update', ['id' => '__ID__']));
    document.getElementById('changeForm').action = route.replace('__ID__', id);

    changeModal?.classList.add('is-open');
    changeModal?.setAttribute('aria-hidden', 'false');
}

function closeChangeModal() {
    changeModal?.classList.remove('is-open');
    changeModal?.setAttribute('aria-hidden', 'true');
}

function confirmDelete(event) {
    if (!confirm('Are you sure you want to delete this condition?')) {
        event.preventDefault();
        return false;
    }
    return true;
}

window.addEventListener('click', function(event) {
    if (event.target === addConditionModal) {
        closeAddConditionModal();
    }
    if (event.target === changeModal) {
        closeChangeModal();
    }
    if (!event.target.closest('[data-clinic-select]')) {
        clinicSelects.forEach(closeClinicSelect);
    }
});

document.addEventListener('keydown', function(event) {
    if (event.key === 'Escape') {
        closeAddConditionModal();
        closeChangeModal();
        clinicSelects.forEach(closeClinicSelect);
    }
});

clinicSelects.forEach(initializeClinicSelect);
updateConditionsView();
</script>
@endpush
