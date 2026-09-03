@extends('layouts.admin')

@section('title', 'Manage Health Form Categories')

@push('styles')
<style>
    .health-categories-page,
    .health-category-modal-overlay {
        --clinic-maroon: #8f1024;
        --clinic-deep: #6d0718;
        --clinic-border: #ead4d7;
        --clinic-muted: #64748b;
    }

    .health-categories-page {
        color: #201016;
    }

    .health-categories-header,
    .health-categories-title-wrap,
    .health-category-actions,
    .modal-actions {
        display: flex;
        align-items: center;
    }

    .health-categories-header {
        justify-content: space-between;
        gap: 18px;
        margin-bottom: 28px;
    }

    .health-categories-title-wrap {
        gap: 14px;
        min-width: 0;
    }

    .health-categories-title-icon,
    .health-category-toggle,
    .health-category-code,
    .standard-modal-icon,
    .standard-modal-close {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        flex: 0 0 auto;
    }

    .health-categories-title-icon {
        width: 46px;
        height: 46px;
        border-radius: 10px;
        color: #facc15;
        background: linear-gradient(135deg, var(--clinic-maroon), var(--clinic-deep));
        box-shadow: 0 12px 24px rgba(143, 16, 36, .18);
    }

    .health-categories-title-icon svg,
    .health-categories-action svg,
    .health-categories-field svg,
    .health-category-toggle svg,
    .health-category-btn svg,
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

    .health-categories-title-wrap h1 {
        margin: 0;
        color: var(--clinic-deep);
        font-size: 1.35rem;
        font-weight: 900;
        letter-spacing: 0;
    }

    .health-categories-title-wrap p {
        margin: 4px 0 0;
        color: var(--clinic-muted);
        font-size: .88rem;
        font-weight: 600;
    }

    .health-categories-toolbar {
        display: grid;
        grid-template-columns: minmax(240px, 1fr) auto;
        gap: 14px;
        align-items: center;
        margin-bottom: 18px;
    }

    .health-categories-field {
        position: relative;
        display: flex;
        align-items: center;
        min-height: 48px;
        color: #64748b;
    }

    .health-categories-field svg {
        position: absolute;
        left: 16px;
        pointer-events: none;
    }

    .health-categories-field input,
    .health-category-modal-box .form-control {
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

    .health-categories-field input:focus,
    .health-category-modal-box .form-control:focus {
        border-color: rgba(143, 16, 36, .42);
        box-shadow: 0 0 0 4px rgba(143, 16, 36, .08);
    }

    .health-categories-action,
    .health-category-btn,
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

    .health-categories-action {
        min-height: 48px;
        border-radius: 13px;
        border: 1px solid rgba(250, 204, 21, .32);
        padding: 0 22px;
        color: #fff;
        background: linear-gradient(135deg, var(--clinic-maroon), var(--clinic-deep));
        box-shadow: 0 12px 24px rgba(143, 16, 36, .18);
        white-space: nowrap;
    }

    .health-categories-action::after,
    .health-category-btn::after,
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

    .health-categories-action:hover,
    .health-category-btn:hover,
    .btn-cancel:hover,
    .btn-save:hover {
        transform: translateY(-1px);
        color: var(--clinic-maroon);
        border-color: rgba(250, 204, 21, .9);
        background: #facc15;
        box-shadow: 0 10px 20px rgba(143, 16, 36, .14);
    }

    .health-categories-action:hover::after,
    .health-category-btn:hover::after,
    .btn-cancel:hover::after,
    .btn-save:hover::after,
    .standard-modal-close:hover::after {
        transform: translateX(135%);
    }

    .health-categories-action span,
    .health-categories-action svg,
    .health-category-btn span,
    .health-category-btn svg,
    .btn-cancel span,
    .btn-save span {
        position: relative;
        z-index: 1;
    }

    .health-categories-meta-row {
        margin: 8px 0 16px;
    }

    .health-categories-count {
        color: #263241;
        font-size: .86rem;
        font-weight: 800;
    }

    .health-categories-list {
        display: grid;
        gap: 10px;
        max-height: clamp(320px, calc(100vh - 340px), 600px);
        overflow-y: auto;
        padding-right: 4px;
        scrollbar-width: thin;
    }

    .health-category-group {
        overflow: hidden;
        border: 1px solid var(--clinic-border);
        border-radius: 9px;
        background: #fff;
    }

    .health-category-group[hidden] {
        display: none;
    }

    .health-category-group-header {
        display: grid;
        grid-template-columns: 34px 34px minmax(0, 1fr) auto auto 42px 28px;
        align-items: center;
        gap: 12px;
        width: 100%;
        min-height: 58px;
        padding: 12px 18px;
        color: var(--clinic-deep);
        background: linear-gradient(180deg, #fff8f9, #fff2f4);
        text-align: left;
    }

    .health-category-toggle {
        width: 28px;
        height: 28px;
        border: 1px solid #efcfd4;
        border-radius: 6px;
        color: var(--clinic-maroon);
        background: #fff;
        cursor: pointer;
        transition: transform .18s ease;
    }

    .health-category-group.is-open .health-category-toggle {
        transform: rotate(90deg);
    }

    .health-category-code {
        width: 30px;
        height: 30px;
        border-radius: 7px;
        color: #fff;
        background: var(--clinic-maroon);
        font-size: .78rem;
        font-weight: 900;
    }

    .health-category-header-name {
        min-width: 0;
        border: 0;
        padding: 0;
        color: inherit;
        background: transparent;
        cursor: pointer;
        text-align: left;
    }

    .health-category-name {
        display: block;
        min-width: 0;
        overflow: hidden;
        text-overflow: ellipsis;
        white-space: nowrap;
        font-size: .95rem;
        font-weight: 900;
    }

    .health-category-pill {
        border-radius: 999px;
        padding: 7px 12px;
        color: var(--clinic-maroon);
        background: #f8e7ea;
        font-size: .75rem;
        font-weight: 900;
        white-space: nowrap;
    }

    .health-category-status {
        border-radius: 999px;
        padding: 7px 12px;
        font-size: .75rem;
        font-weight: 900;
        white-space: nowrap;
    }

    .health-category-status.is-active {
        color: #166534;
        background: #dcfce7;
    }

    .health-category-status.is-archived {
        color: #991b1b;
        background: #fee2e2;
    }

    .health-category-chevron-button {
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

    .health-category-chevron {
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

    .health-category-group.is-open .health-category-chevron {
        transform: rotate(180deg);
    }

    .health-category-body {
        display: none;
        max-height: 300px;
        overflow-y: auto;
        scrollbar-width: thin;
    }

    .health-category-group.is-open .health-category-body {
        display: block;
    }

    .health-category-detail-row {
        display: grid;
        grid-template-columns: 1fr auto;
        align-items: center;
        gap: 16px;
        min-height: 54px;
        padding: 10px 14px 10px 34px;
        border-top: 1px solid #eef1f5;
    }

    .health-category-detail {
        display: flex;
        align-items: center;
        gap: 18px;
        min-width: 0;
        color: #2c1820;
        font-size: .9rem;
        font-weight: 800;
    }

    .health-category-detail::before {
        content: "";
        width: 7px;
        height: 7px;
        border-radius: 999px;
        background: var(--clinic-maroon);
        flex: 0 0 auto;
    }

    .health-category-btn,
    .btn-cancel,
    .btn-save {
        min-height: 38px;
        border-radius: 8px;
        padding: 0 14px;
        font-size: .8rem;
    }

    .health-category-btn--delete,
    .btn-save {
        color: #fff;
        border: 1px solid rgba(250, 204, 21, .26);
        background: linear-gradient(135deg, var(--clinic-maroon), var(--clinic-deep));
    }

    .health-category-btn--icon {
        width: 42px;
        min-width: 42px;
        height: 38px;
        padding: 0;
    }

    .btn-cancel {
        color: var(--clinic-maroon);
        border: 1px solid #f0d8cc;
        background: #fffdfb;
    }

    .health-categories-empty {
        border: 1px dashed #e8cfd3;
        border-radius: 10px;
        padding: 24px;
        color: #64748b;
        background: #fff9fa;
        text-align: center;
        font-weight: 800;
    }

    .health-category-modal-overlay {
        display: none;
        position: fixed;
        inset: 0;
        z-index: 1000;
        align-items: center;
        justify-content: center;
        padding: 18px;
        background: rgba(15, 23, 42, .45);
    }

    .health-category-modal-overlay.is-open {
        display: flex;
    }

    .health-category-modal-box {
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

    html[data-theme="dark"] .health-categories-page {
        --clinic-border: rgba(255,255,255,.12);
        color: #f8fafc;
    }

    html[data-theme="dark"] .health-categories-title-wrap h1,
    html[data-theme="dark"] .health-categories-count,
    html[data-theme="dark"] .health-category-name,
    html[data-theme="dark"] .health-category-detail,
    body.dark-mode .health-categories-title-wrap h1,
    body.dark-mode .health-categories-count,
    body.dark-mode .health-category-name,
    body.dark-mode .health-category-detail {
        color: #f8fafc;
    }

    html[data-theme="dark"] .health-categories-title-wrap p,
    html[data-theme="dark"] .standard-modal-note,
    body.dark-mode .health-categories-title-wrap p,
    body.dark-mode .standard-modal-note {
        color: #cbd5e1;
    }

    html[data-theme="dark"] .health-categories-field input,
    html[data-theme="dark"] .health-category-group,
    html[data-theme="dark"] .health-category-modal-box,
    body.dark-mode .health-categories-field input,
    body.dark-mode .health-category-group,
    body.dark-mode .health-category-modal-box {
        color: #f8fafc;
        border-color: rgba(255,255,255,.14);
        background: rgba(35, 17, 25, .96);
    }

    html[data-theme="dark"] .health-category-group-header,
    body.dark-mode .health-category-group-header {
        color: #f8fafc;
        background: linear-gradient(180deg, rgba(112, 19, 27, .75), rgba(55, 20, 30, .86));
    }

    html[data-theme="dark"] .health-category-toggle,
    body.dark-mode .health-category-toggle {
        color: var(--clinic-maroon);
        border-color: rgba(250, 204, 21, .78);
        background: #facc15;
    }

    html[data-theme="dark"] .health-category-chevron,
    body.dark-mode .health-category-chevron {
        color: #facc15;
    }

    html[data-theme="dark"] .health-category-detail-row,
    body.dark-mode .health-category-detail-row {
        border-top-color: rgba(255,255,255,.1);
    }

    html[data-theme="dark"] .standard-modal-body,
    body.dark-mode .standard-modal-body {
        background: rgba(24, 7, 14, .92);
    }

    html[data-theme="dark"] .health-category-modal-box .form-control,
    body.dark-mode .health-category-modal-box .form-control {
        color: #fff;
        border-color: rgba(255,255,255,.16);
        background: rgba(255,255,255,.05);
    }

    @media (max-width: 860px) {
        .health-categories-header,
        .health-categories-toolbar {
            display: grid;
            grid-template-columns: 1fr;
        }

        .health-categories-action,
        .modal-actions,
        .btn-cancel,
        .btn-save {
            width: 100%;
        }

        .health-category-group-header {
            grid-template-columns: 30px 30px minmax(0, 1fr) 38px 24px;
        }

        .health-category-pill,
        .health-category-status {
            grid-column: 3 / 4;
            width: fit-content;
            margin-top: 4px;
        }

        .health-category-status {
            grid-row: 3;
        }

        .health-category-remove-form {
            grid-column: 4;
            grid-row: 1 / 3;
        }

        .health-category-chevron {
            grid-column: 5;
            grid-row: 1 / 3;
        }

        .health-category-detail-row {
            grid-template-columns: 1fr;
            padding-left: 18px;
        }

        .modal-actions {
            flex-direction: column-reverse;
        }
    }
</style>
@endpush

@section('content')
@php
    $totalCategories = $categories->count();
@endphp

<div class="health-categories-page">
    <div class="health-categories-header">
        <div class="health-categories-title-wrap">
            <span class="health-categories-title-icon" aria-hidden="true">
                <svg viewBox="0 0 24 24"><path d="M9 3h6v5h5v13H4V8h5V3Z"></path><path d="M10 14h4"></path><path d="M12 12v4"></path></svg>
            </span>
            <div>
                <h1>Manage Health Form Categories</h1>
                <p>Manage health form category labels used by submitted patient forms.</p>
            </div>
        </div>
    </div>

    <div class="health-categories-toolbar">
        <label class="health-categories-field">
            <svg viewBox="0 0 24 24" aria-hidden="true"><circle cx="11" cy="11" r="7"></circle><path d="m20 20-3.5-3.5"></path></svg>
            <input type="search" id="healthCategorySearch" placeholder="Search health form categories..." autocomplete="off">
        </label>

        <button type="button" class="health-categories-action" onclick="openAddHealthCategoryModal()">
            <svg viewBox="0 0 24 24" aria-hidden="true"><path d="M12 5v14"></path><path d="M5 12h14"></path></svg>
            <span>Add Category</span>
        </button>
    </div>

    <div class="health-categories-meta-row">
        <div class="health-categories-count" id="healthCategoriesCount">Showing {{ $totalCategories }} {{ $totalCategories === 1 ? 'health form category' : 'health form categories' }}</div>
    </div>

    <div class="health-categories-list" id="healthCategoriesList">
        @forelse($categories as $category)
            <section
                class="health-category-group"
                data-health-category-group
                data-health-category-name="{{ strtolower($category->name) }}"
            >
                <div class="health-category-group-header">
                    <button type="button" class="health-category-toggle" data-health-category-toggle aria-expanded="false">
                        <svg viewBox="0 0 24 24"><path d="m9 18 6-6-6-6"></path></svg>
                    </button>
                    <span class="health-category-code">{{ strtoupper(substr($category->name, 0, 1)) }}</span>
                    <button type="button" class="health-category-header-name" data-health-category-toggle aria-expanded="false">
                        <span class="health-category-name">{{ $category->name }}</span>
                    </button>
                    <span class="health-category-pill">{{ $category->submissions_count }} {{ $category->submissions_count === 1 ? 'linked form' : 'linked forms' }}</span>
                    <span class="health-category-status {{ $category->is_active ? 'is-active' : 'is-archived' }}">{{ $category->is_active ? 'Active' : 'Archived' }}</span>
                    <form class="health-category-remove-form" action="{{ route('health-form-categories.destroy', $category->id) }}" method="POST" onsubmit="return confirmHealthCategoryDelete(event)">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="health-category-btn health-category-btn--delete health-category-btn--icon" title="{{ $category->submissions_count > 0 ? 'Archive category' : 'Remove category' }}" aria-label="{{ $category->submissions_count > 0 ? 'Archive category' : 'Remove category' }}">
                            <svg viewBox="0 0 24 24" aria-hidden="true"><path d="M3 6h18"></path><path d="M8 6V4h8v2"></path><path d="M19 6l-1 14H6L5 6"></path><path d="M10 11v5"></path><path d="M14 11v5"></path></svg>
                        </button>
                    </form>
                    <button type="button" class="health-category-chevron-button" data-health-category-toggle aria-expanded="false">
                        <svg class="health-category-chevron" viewBox="0 0 24 24" aria-hidden="true"><path d="m6 9 6 6 6-6"></path></svg>
                    </button>
                </div>

                <div class="health-category-body">
                    <div class="health-category-detail-row">
                        <div class="health-category-detail">Linked Forms: {{ $category->submissions_count }}</div>
                    </div>
                    <div class="health-category-detail-row">
                        <div class="health-category-detail">Status: {{ $category->is_active ? 'Active' : 'Archived' }}</div>
                    </div>
                    <div class="health-category-detail-row">
                        <div class="health-category-detail">{{ $category->submissions_count > 0 ? 'Removing this category will archive it because forms are linked.' : 'This category can be removed because no forms are linked.' }}</div>
                    </div>
                </div>
            </section>
        @empty
            <div class="health-categories-empty">No health form categories found.</div>
        @endforelse
    </div>

    <div class="health-categories-empty" id="healthCategoriesEmpty" hidden>No health form categories matched your search.</div>
</div>

<div id="addHealthCategoryModal" class="health-category-modal-overlay" aria-hidden="true">
    <div class="health-category-modal-box" role="dialog" aria-modal="true" aria-labelledby="addHealthCategoryTitle">
        <div class="standard-modal-header">
            <div class="standard-modal-title">
                <span class="standard-modal-icon" aria-hidden="true">
                    <svg viewBox="0 0 24 24"><path d="M9 3h6v5h5v13H4V8h5V3Z"></path><path d="M10 14h4"></path><path d="M12 12v4"></path></svg>
                </span>
                <div>
                    <h3 id="addHealthCategoryTitle">Add Category</h3>
                    <p>Create a health form category.</p>
                </div>
            </div>
            <button type="button" class="standard-modal-close" onclick="closeAddHealthCategoryModal()" aria-label="Close add health form category modal">
                <svg viewBox="0 0 24 24" aria-hidden="true"><path d="M18 6 6 18"></path><path d="m6 6 12 12"></path></svg>
            </button>
        </div>

        <div class="standard-modal-body">
            <p class="standard-modal-note">Add a category used to classify submitted health forms.</p>

            <form action="{{ route('health-form-categories.store') }}" method="POST" class="modal-stack">
                @csrf
                <label>
                    <span class="sr-only">Health Form Category Name</span>
                    <input type="text" name="name" class="form-control" placeholder="Health Form Category Name (e.g. OJT, Annual, Medical Clearance)" required>
                </label>

                <div class="modal-actions">
                    <button type="button" class="btn-cancel" onclick="closeAddHealthCategoryModal()"><span>Cancel</span></button>
                    <button type="submit" class="btn-save"><span>Save Category</span></button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
const healthCategorySearch = document.getElementById('healthCategorySearch');
const healthCategoriesCount = document.getElementById('healthCategoriesCount');
const healthCategoriesEmpty = document.getElementById('healthCategoriesEmpty');
const addHealthCategoryModal = document.getElementById('addHealthCategoryModal');

function pluralizeHealthCategory(count) {
    return count === 1 ? 'health form category' : 'health form categories';
}

function updateHealthCategoriesView() {
    const searchValue = (healthCategorySearch?.value || '').trim().toLowerCase();
    const groups = Array.from(document.querySelectorAll('[data-health-category-group]'));
    let visibleTotal = 0;

    groups.forEach(function(group) {
        const isVisible = !searchValue || (group.dataset.healthCategoryName || '').includes(searchValue);
        group.hidden = !isVisible;
        if (isVisible) {
            visibleTotal += 1;
        }
        if (searchValue) {
            group.classList.toggle('is-open', isVisible);
            group.querySelectorAll('[data-health-category-toggle]').forEach(function(toggle) {
                toggle.setAttribute('aria-expanded', isVisible ? 'true' : 'false');
            });
        }
    });

    if (healthCategoriesCount) {
        healthCategoriesCount.textContent = `Showing ${visibleTotal} ${pluralizeHealthCategory(visibleTotal)}`;
    }
    if (healthCategoriesEmpty) {
        healthCategoriesEmpty.hidden = visibleTotal !== 0;
    }
}

document.querySelectorAll('[data-health-category-toggle]').forEach(function(button) {
    button.addEventListener('click', function() {
        const group = button.closest('[data-health-category-group]');
        const isOpen = !group.classList.contains('is-open');
        group.classList.toggle('is-open', isOpen);
        group.querySelectorAll('[data-health-category-toggle]').forEach(function(toggle) {
            toggle.setAttribute('aria-expanded', isOpen ? 'true' : 'false');
        });
    });
});

function openAddHealthCategoryModal() {
    addHealthCategoryModal?.classList.add('is-open');
    addHealthCategoryModal?.setAttribute('aria-hidden', 'false');
}

function closeAddHealthCategoryModal() {
    addHealthCategoryModal?.classList.remove('is-open');
    addHealthCategoryModal?.setAttribute('aria-hidden', 'true');
}

function confirmHealthCategoryDelete(event) {
    if (!confirm('Are you sure you want to remove or archive this Health Form category?')) {
        event.preventDefault();
        return false;
    }
    return true;
}

healthCategorySearch?.addEventListener('input', updateHealthCategoriesView);

window.addEventListener('click', function(event) {
    if (event.target === addHealthCategoryModal) {
        closeAddHealthCategoryModal();
    }
});

document.addEventListener('keydown', function(event) {
    if (event.key === 'Escape') {
        closeAddHealthCategoryModal();
    }
});

updateHealthCategoriesView();
</script>
@endpush
