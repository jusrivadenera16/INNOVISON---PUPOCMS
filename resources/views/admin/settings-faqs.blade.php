@extends('layouts.admin')

@section('title', 'FAQs')

@push('styles')
<style>
    .faq-admin {
        width: min(1480px, 100%);
        margin: 0 auto;
        color: #111827;
    }
    .faq-hero,
    .faq-stat,
    .faq-side,
    .faq-board,
    .faq-modal-card {
        background: rgba(255, 255, 255, 0.98);
        border: 1px solid rgba(112, 19, 27, 0.12);
        box-shadow: 0 18px 42px rgba(15, 23, 42, 0.06);
    }
    .faq-hero {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 18px;
        padding: 22px 28px;
        border-radius: 18px;
        margin-bottom: 16px;
    }
    .faq-title-wrap {
        display: flex;
        align-items: center;
        gap: 14px;
        min-width: 0;
    }
    .faq-title-icon,
    .faq-stat-icon,
    .faq-category-icon,
    .faq-action-icon {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        flex: 0 0 auto;
    }
    .faq-title-icon {
        width: 54px;
        height: 54px;
        border-radius: 999px;
        background: #fff0f2;
        color: #8f1827;
    }
    .faq-title-icon svg { width: 28px; height: 28px; }
    .faq-hero h2 {
        margin: 0;
        color: #111827;
        font-size: 30px;
        font-weight: 950;
        line-height: 1;
    }
    .faq-hero p {
        margin: 7px 0 0;
        color: #64748b;
        font-size: 14px;
        font-weight: 650;
    }
    .faq-add-btn,
    .faq-save-btn {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        gap: 8px;
        border: 1px solid #facc15;
        background: #82000c;
        color: #ffffff;
        border-radius: 10px;
        padding: 13px 20px;
        font: inherit;
        font-size: 15px;
        font-weight: 900;
        cursor: pointer;
        box-shadow: 0 12px 22px rgba(130, 0, 12, 0.18);
        transition: background 0.18s ease, color 0.18s ease, transform 0.18s ease;
    }
    .faq-add-btn svg,
    .faq-save-btn svg { width: 18px; height: 18px; }
    .faq-add-btn:hover,
    .faq-save-btn:hover {
        background: #facc15;
        color: #70131b;
        transform: translateY(-1px);
    }
    .faq-stats {
        display: grid;
        grid-template-columns: repeat(4, minmax(0, 1fr));
        gap: 18px;
        margin-bottom: 18px;
    }
    .faq-stat {
        display: flex;
        align-items: center;
        gap: 18px;
        min-width: 0;
        padding: 24px 28px;
        border-radius: 14px;
    }
    .faq-stat-icon {
        width: 54px;
        height: 54px;
        border-radius: 999px;
    }
    .faq-stat-icon svg { width: 28px; height: 28px; }
    .faq-stat-icon.is-total { background: #fff0f2; color: #a2162b; }
    .faq-stat-icon.is-categories { background: #fff7df; color: #c9872d; }
    .faq-stat-icon.is-published { background: #e9f8e8; color: #16803c; }
    .faq-stat-icon.is-updated { background: #e9f4ff; color: #256a9b; }
    .faq-stat-label {
        display: block;
        color: #64748b;
        font-size: 14px;
        font-weight: 750;
    }
    .faq-stat-number {
        display: block;
        margin-top: 3px;
        color: #111827;
        font-size: 30px;
        line-height: 1;
        font-weight: 950;
    }
    .faq-stat-help {
        display: block;
        margin-top: 5px;
        color: #64748b;
        font-size: 12px;
        font-weight: 700;
    }
    .faq-grid {
        display: grid;
        grid-template-columns: 380px minmax(0, 1fr);
        gap: 18px;
        align-items: stretch;
    }
    .faq-side,
    .faq-board {
        border-radius: 16px;
    }
    .faq-side {
        padding: 22px;
    }
    .faq-side-head,
    .faq-board-tools,
    .faq-modal-head {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 12px;
    }
    .faq-side h3,
    .faq-board h3 {
        margin: 0;
        color: #111827;
        font-size: 18px;
        font-weight: 950;
    }
    .faq-side-count {
        color: #a2162b;
        font-size: 12px;
        font-weight: 900;
    }
    .faq-side p {
        margin: 10px 0 18px;
        color: #64748b;
        font-size: 13px;
        line-height: 1.55;
        font-weight: 650;
    }
    .faq-category-list,
    .faq-quick-list {
        display: grid;
        gap: 10px;
        margin: 0;
        padding: 0;
        list-style: none;
    }
    .faq-category-btn,
    .faq-quick-btn {
        width: 100%;
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 10px;
        padding: 8px 10px;
        min-height: 40px;
        border: 1px solid rgba(226, 232, 240, 0.95);
        border-radius: 10px;
        background: #fffafa;
        color: #1f2937;
        font: inherit;
        font-size: 13px;
        font-weight: 850;
        cursor: pointer;
        text-align: left;
        transition: border-color 0.18s ease, background 0.18s ease, transform 0.18s ease;
    }
    .faq-category-btn:hover,
    .faq-category-btn.is-active,
    .faq-quick-btn:hover {
        border-color: rgba(162, 22, 43, 0.28);
        background: #fff4f5;
        transform: translateY(-1px);
    }
    .faq-category-main,
    .faq-quick-main {
        display: inline-flex;
        align-items: center;
        gap: 8px;
        min-width: 0;
    }
    .faq-category-icon,
    .faq-action-icon {
        width: 24px;
        height: 24px;
        border-radius: 8px;
    }
    .faq-category-icon.is-red,
    .faq-action-icon.is-red { background: #fff0f2; color: #a2162b; }
    .faq-category-icon.is-gold,
    .faq-action-icon.is-gold { background: #fff7df; color: #b47513; }
    .faq-category-icon.is-green { background: #ecfdf3; color: #16803c; }
    .faq-category-icon.is-blue { background: #edf4ff; color: #3456b8; }
    .faq-category-icon.is-sky { background: #e9f4ff; color: #256a9b; }
    .faq-category-icon svg,
    .faq-action-icon svg { width: 14px; height: 14px; }
    .faq-quick-btn > svg {
        width: 16px;
        height: 16px;
        flex: 0 0 16px;
        stroke-width: 2;
    }
    .faq-category-name {
        min-width: 0;
        overflow: hidden;
        text-overflow: ellipsis;
        white-space: nowrap;
    }
    .faq-category-badge {
        flex: 0 0 auto;
        padding: 4px 8px;
        border-radius: 999px;
        background: #fff0f2;
        color: #a2162b;
        font-size: 11px;
        font-weight: 900;
    }
    .faq-quick {
        margin-top: 22px;
        padding: 14px;
        border: 1px solid rgba(226, 232, 240, 0.95);
        border-radius: 12px;
        background: rgba(255, 255, 255, 0.74);
    }
    .faq-quick h4 {
        margin: 0 0 10px;
        color: #111827;
        font-size: 15px;
        font-weight: 950;
    }
    .faq-board {
        min-width: 0;
        padding: 22px;
    }
    .faq-board-tools {
        margin-bottom: 16px;
    }
    .faq-search {
        position: relative;
        flex: 1 1 360px;
        max-width: 430px;
    }
    .faq-search svg {
        position: absolute;
        left: 16px;
        top: 50%;
        width: 18px;
        height: 18px;
        color: #475569;
        transform: translateY(-50%);
        pointer-events: none;
    }
    .faq-search input,
    .faq-select {
        width: 100%;
        height: 44px;
        border: 1px solid #dbe3ee;
        border-radius: 12px;
        background: #ffffff;
        color: #111827;
        font: inherit;
        font-size: 13px;
        font-weight: 750;
        outline: none;
    }
    .faq-search input {
        padding: 0 16px 0 44px;
    }
    .faq-search input:focus,
    .faq-select:focus {
        border-color: #a2162b;
        box-shadow: 0 0 0 3px rgba(162, 22, 43, 0.1);
    }
    .faq-board-selects {
        display: flex;
        gap: 12px;
        flex: 0 0 auto;
    }
    .faq-select {
        min-width: 190px;
        padding: 0 14px;
        cursor: pointer;
    }
    .faq-filter-row {
        display: flex;
        align-items: center;
        gap: 10px;
        padding-bottom: 14px;
        margin-bottom: 14px;
        overflow-x: auto;
    }
    .faq-filter-pill {
        flex: 0 0 auto;
        border: 1px solid #e2e8f0;
        border-radius: 999px;
        background: #ffffff;
        color: #1f2937;
        padding: 9px 14px;
        font: inherit;
        font-size: 12px;
        font-weight: 850;
        cursor: pointer;
        transition: background 0.18s ease, color 0.18s ease, border-color 0.18s ease;
    }
    .faq-filter-pill:hover,
    .faq-filter-pill.is-active {
        background: #940515;
        color: #ffffff;
        border-color: #940515;
    }
    .faq-list {
        display: grid;
        gap: 12px;
    }
    .faq-item {
        display: grid;
        grid-template-columns: 54px minmax(0, 1fr) auto;
        gap: 14px;
        align-items: center;
        padding: 18px;
        border: 1px solid #e2e8f0;
        border-radius: 14px;
        background: linear-gradient(90deg, rgba(255, 247, 247, 0.96), #ffffff 42%);
    }
    .faq-item-number {
        color: #9b111e;
        font-size: 14px;
        font-weight: 950;
    }
    .faq-item-body {
        min-width: 0;
    }
    .faq-category-tag {
        display: inline-flex;
        align-items: center;
        gap: 6px;
        width: fit-content;
        max-width: min(100%, 260px);
        margin-bottom: 8px;
        padding: 4px 8px;
        border-radius: 8px;
        background: #fff0f2;
        color: #a2162b;
        font-size: 10px;
        line-height: 1.2;
        font-weight: 900;
        white-space: normal;
    }
    .faq-category-tag svg {
        width: 14px;
        height: 14px;
        flex: 0 0 auto;
    }
    .faq-category-tag.is-gold { background: #fff7df; color: #9a5e05; }
    .faq-item-question {
        margin: 0;
        color: #111827;
        font-size: 16px;
        font-weight: 950;
        line-height: 1.35;
    }
    .faq-item-answer {
        margin: 7px 0 0;
        color: #475569;
        font-size: 13px;
        line-height: 1.45;
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
    }
    .faq-meta {
        display: flex;
        align-items: center;
        flex-wrap: wrap;
        gap: 8px;
        margin-top: 10px;
        color: #64748b;
        font-size: 12px;
        font-weight: 750;
    }
    .faq-status {
        color: #16803c;
        font-weight: 900;
    }
    .faq-status.is-hidden {
        color: #b45309;
    }
    .faq-dot { color: #cbd5e1; }
    .faq-item-actions {
        display: flex;
        align-items: center;
        gap: 10px;
    }
    .faq-row-btn,
    .faq-delete-btn {
        display: inline-flex;
        align-items: center;
        gap: 8px;
        min-height: 40px;
        border: 1px solid #e2e8f0;
        border-radius: 9px;
        background: #ffffff;
        color: #1f2937;
        padding: 0 14px;
        font: inherit;
        font-size: 12px;
        font-weight: 850;
        cursor: pointer;
        text-decoration: none;
        transition: border-color 0.18s ease, background 0.18s ease, color 0.18s ease;
    }
    .faq-row-btn svg,
    .faq-delete-btn svg { width: 16px; height: 16px; }
    .faq-row-btn:hover {
        border-color: #a2162b;
        background: #fff7f7;
        color: #a2162b;
    }
    .faq-delete-btn {
        border-color: #fecdd3;
        color: #be123c;
    }
    .faq-delete-btn:hover {
        background: #fff1f2;
        border-color: #fb7185;
    }
    .faq-empty {
        padding: 44px 18px;
        border: 1px dashed #cbd5e1;
        border-radius: 14px;
        color: #64748b;
        text-align: center;
        font-size: 14px;
        font-weight: 700;
    }
    .faq-pagination {
        display: flex;
        justify-content: space-between;
        align-items: center;
        gap: 16px;
        margin-top: 18px;
    }
    .faq-pages {
        display: flex;
        justify-content: center;
        gap: 8px;
        flex: 1;
    }
    .faq-page-btn {
        width: 38px;
        height: 38px;
        border: 1px solid #e2e8f0;
        border-radius: 10px;
        background: #ffffff;
        color: #1f2937;
        font: inherit;
        font-size: 13px;
        font-weight: 900;
        cursor: pointer;
    }
    .faq-page-btn.is-active {
        background: #940515;
        border-color: #940515;
        color: #ffffff;
    }
    .faq-per-page {
        width: 140px;
    }
    .faq-alert {
        margin-bottom: 14px;
        padding: 12px 16px;
        border-radius: 10px;
        background: #ecfdf5;
        border: 1px solid #a7f3d0;
        color: #047857;
        font-size: 13px;
        font-weight: 800;
    }
    .faq-alert.is-error {
        background: #fff1f2;
        border-color: #fecdd3;
        color: #be123c;
    }
    .faq-modal {
        position: fixed;
        inset: 0;
        z-index: 1200;
        display: none;
        place-items: center;
        padding: 20px;
        background: rgba(15, 23, 42, 0.62);
    }
    .faq-modal.is-open { display: grid; }
    .faq-modal-card {
        width: min(620px, 100%);
        max-height: min(760px, 92vh);
        display: flex;
        flex-direction: column;
        overflow: hidden;
        border-radius: 16px;
    }
    .faq-modal-head {
        padding: 18px 20px;
        background: #8f1827;
        color: #ffffff;
    }
    .faq-modal-head h3 {
        display: flex;
        align-items: center;
        gap: 10px;
        margin: 0;
        color: #ffffff !important;
        font-size: 19px;
        font-weight: 950;
    }
    .faq-modal-head svg { width: 21px; height: 21px; }
    .faq-modal-close {
        width: 36px;
        height: 36px;
        display: grid;
        place-items: center;
        border: 1px solid rgba(255, 255, 255, 0.45);
        border-radius: 999px;
        background: transparent;
        color: #ffffff;
        cursor: pointer;
    }
    .faq-modal-close svg { width: 18px; height: 18px; }
    .faq-modal-close:hover {
        background: #facc15;
        color: #70131b;
        border-color: #facc15;
    }
    .faq-modal-body {
        display: grid;
        gap: 14px;
        padding: 22px;
        overflow-y: auto;
    }
    .faq-field { display: grid; gap: 6px; }
    .faq-field label {
        color: #334155;
        font-size: 12px;
        font-weight: 900;
    }
    .faq-field input,
    .faq-field textarea,
    .faq-field select {
        width: 100%;
        border: 1px solid #cbd5e1;
        border-radius: 10px;
        padding: 12px;
        color: #111827;
        background: #ffffff;
        font: inherit;
        font-size: 14px;
        outline: none;
    }
    .faq-field textarea {
        min-height: 160px;
        resize: vertical;
        line-height: 1.55;
    }
    .faq-field input:focus,
    .faq-field textarea:focus,
    .faq-field select:focus {
        border-color: #8f1827;
        box-shadow: 0 0 0 3px rgba(143, 24, 39, 0.1);
    }
    .faq-toggle-row {
        display: flex;
        align-items: center;
        gap: 10px;
        color: #334155;
        font-size: 13px;
        font-weight: 850;
    }
    .faq-toggle-row input {
        width: 18px;
        height: 18px;
        accent-color: #8f1827;
    }
    .faq-modal-actions {
        display: flex;
        justify-content: flex-end;
        gap: 10px;
        padding-top: 4px;
    }
    .faq-cancel-btn {
        border: 1px solid #cbd5e1;
        background: #ffffff;
        color: #334155;
        border-radius: 10px;
        padding: 11px 16px;
        font: inherit;
        font-weight: 850;
        cursor: pointer;
    }
    .faq-cancel-btn:hover { background: #f1f5f9; }
    .faq-preview-title {
        margin: 0;
        color: #111827;
        font-size: 20px;
        font-weight: 950;
    }
    .faq-preview-answer {
        margin: 10px 0 0;
        color: #475569;
        font-size: 14px;
        line-height: 1.7;
        white-space: pre-line;
    }
    .faq-category-manage-list {
        display: grid;
        gap: 12px;
    }
    .faq-category-manage-item {
        display: grid;
        grid-template-columns: minmax(0, 1fr) auto;
        gap: 10px;
        align-items: end;
        padding: 14px;
        border: 1px solid #e2e8f0;
        border-radius: 12px;
        background: #fffafa;
    }
    .faq-category-manage-name {
        margin: 0 0 8px;
        color: #64748b;
        font-size: 12px;
        font-weight: 850;
    }
    .faq-category-manage-empty {
        padding: 22px;
        border: 1px dashed #cbd5e1;
        border-radius: 12px;
        color: #64748b;
        text-align: center;
        font-size: 13px;
        font-weight: 750;
    }
    @media (max-width: 1180px) {
        .faq-stats { grid-template-columns: repeat(2, minmax(0, 1fr)); }
        .faq-grid { grid-template-columns: 1fr; }
    }
    @media (max-width: 760px) {
        .faq-hero,
        .faq-board-tools,
        .faq-pagination {
            align-items: stretch;
            flex-direction: column;
        }
        .faq-add-btn,
        .faq-board-selects,
        .faq-select,
        .faq-per-page { width: 100%; }
        .faq-stats { grid-template-columns: 1fr; }
        .faq-item {
            grid-template-columns: 1fr;
        }
        .faq-item-actions {
            flex-wrap: wrap;
        }
        .faq-row-btn,
        .faq-delete-btn {
            flex: 1 1 140px;
            justify-content: center;
        }
    }
</style>
@endpush

@section('content')
@php
    $faqCategories = $faqs->pluck('category')->filter()->unique()->sort()->values();
    $faqCategoryCounts = $faqs
        ->groupBy(fn ($faq) => $faq->category ?: 'General')
        ->map(fn ($categoryFaqs) => $categoryFaqs->count())
        ->sortKeys();
    $publishedCount = $faqs->where('is_active', true)->count();
    $lastUpdated = $faqs->max('updated_at');
    $categoryThemes = ['is-red', 'is-gold', 'is-green', 'is-blue', 'is-sky'];
    $faqData = $faqs->mapWithKeys(fn ($faq) => [
        $faq->id => [
            'id' => $faq->id,
            'category' => $faq->category ?: 'General',
            'question' => $faq->question,
            'answer' => $faq->answer,
            'is_active' => (bool) $faq->is_active,
            'update_url' => route('admin.settings.faqs.update', $faq),
        ],
    ]);
@endphp

<div class="faq-admin" id="faqAdmin">
    <section class="faq-hero">
        <div class="faq-title-wrap">
            <span class="faq-title-icon"><x-outline-icon name="question-mark-circle" /></span>
            <div>
                <h2>FAQs</h2>
                <p>Create, organize, and publish frequently asked questions for students.</p>
            </div>
        </div>
        <button type="button" class="faq-add-btn" data-open-faq-modal="create">
            <x-outline-icon name="plus-circle" />
            <span>Add FAQ</span>
        </button>
    </section>

    @if(session('success'))
        <div class="faq-alert">{{ session('success') }}</div>
    @endif
    @if($errors->any())
        <div class="faq-alert is-error">{{ $errors->first() }}</div>
    @endif

    <section class="faq-stats" aria-label="FAQ summary">
        <article class="faq-stat">
            <span class="faq-stat-icon is-total"><x-outline-icon name="clipboard-document-list" /></span>
            <span>
                <span class="faq-stat-label">Total FAQs</span>
                <strong class="faq-stat-number">{{ $faqs->count() }}</strong>
                <span class="faq-stat-help">All questions created</span>
            </span>
        </article>
        <article class="faq-stat">
            <span class="faq-stat-icon is-categories"><x-outline-icon name="briefcase" /></span>
            <span>
                <span class="faq-stat-label">Categories</span>
                <strong class="faq-stat-number">{{ $faqCategoryCounts->count() }}</strong>
                <span class="faq-stat-help">FAQ categories</span>
            </span>
        </article>
        <article class="faq-stat">
            <span class="faq-stat-icon is-published"><x-outline-icon name="eye" /></span>
            <span>
                <span class="faq-stat-label">Published</span>
                <strong class="faq-stat-number">{{ $publishedCount }}</strong>
                <span class="faq-stat-help">Visible in student portal</span>
            </span>
        </article>
        <article class="faq-stat">
            <span class="faq-stat-icon is-updated"><x-outline-icon name="calendar-days" /></span>
            <span>
                <span class="faq-stat-label">Last Updated</span>
                <strong class="faq-stat-number" style="font-size:22px;">{{ $lastUpdated ? $lastUpdated->format('F j, Y') : 'No FAQs' }}</strong>
                <span class="faq-stat-help">Latest content update</span>
            </span>
        </article>
    </section>

    <div class="faq-grid">
        <aside class="faq-side">
            <div class="faq-side-head">
                <h3>Category Manager</h3>
                <span class="faq-side-count">{{ $faqCategoryCounts->count() }} {{ \Illuminate\Support\Str::plural('Category', $faqCategoryCounts->count()) }}</span>
            </div>
            <p>Organize FAQs by category so students can easily find what they need.</p>

            @if($faqCategoryCounts->isNotEmpty())
                <ul class="faq-category-list">
                    @foreach($faqCategoryCounts as $category => $count)
                        @php
                            $theme = $categoryThemes[$loop->index % count($categoryThemes)];
                        @endphp
                        <li>
                            <button type="button" class="faq-category-btn" data-category-filter="{{ $category }}">
                                <span class="faq-category-main">
                                    <span class="faq-category-icon {{ $theme }}"><x-outline-icon name="clipboard-document-list" /></span>
                                    <span class="faq-category-name">{{ $category }}</span>
                                </span>
                                <span class="faq-category-badge">{{ $count }} {{ \Illuminate\Support\Str::plural('FAQ', $count) }}</span>
                            </button>
                        </li>
                    @endforeach
                </ul>
            @else
                <div class="faq-empty">No categories yet. Add your first FAQ to create one.</div>
            @endif

            <div class="faq-quick">
                <h4>Quick Actions</h4>
                <div class="faq-quick-list">
                    <button type="button" class="faq-quick-btn" data-open-faq-modal="create">
                        <span class="faq-quick-main">
                            <span class="faq-action-icon is-red"><x-outline-icon name="plus-circle" /></span>
                            <span>Add New FAQ</span>
                        </span>
                        <x-outline-icon name="chevron-right" />
                    </button>
                    <button type="button" class="faq-quick-btn" id="openCategoryModal">
                        <span class="faq-quick-main">
                            <span class="faq-action-icon is-gold"><x-outline-icon name="briefcase" /></span>
                            <span>Manage Categories</span>
                        </span>
                        <x-outline-icon name="chevron-right" />
                    </button>
                </div>
            </div>
        </aside>

        <section class="faq-board">
            <div class="faq-board-tools">
                <label class="faq-search" for="faqAdminSearch">
                    <x-outline-icon name="magnifying-glass" />
                    <input id="faqAdminSearch" type="search" placeholder="Search FAQs..." autocomplete="off">
                </label>
                <div class="faq-board-selects">
                    <select class="faq-select" id="faqCategorySelect" aria-label="Filter by category">
                        <option value="">All Categories</option>
                        @foreach($faqCategoryCounts as $category => $count)
                            <option value="{{ $category }}">{{ $category }}</option>
                        @endforeach
                    </select>
                    <select class="faq-select" id="faqSortSelect" aria-label="Sort FAQs">
                        <option value="newest">Newest First</option>
                        <option value="oldest">Oldest First</option>
                        <option value="az">Question A-Z</option>
                        <option value="za">Question Z-A</option>
                    </select>
                </div>
            </div>

            <div class="faq-filter-row" aria-label="FAQ category filters">
                <button type="button" class="faq-filter-pill is-active" data-category-filter="">All ({{ $faqs->count() }})</button>
                @foreach($faqCategoryCounts as $category => $count)
                    <button type="button" class="faq-filter-pill" data-category-filter="{{ $category }}">{{ $category }} ({{ $count }})</button>
                @endforeach
            </div>

            <div class="faq-list" id="faqAdminList">
                @forelse($faqs as $faq)
                    @php
                        $category = $faq->category ?: 'General';
                    @endphp
                    <article
                        class="faq-item"
                        data-faq-item
                        data-category="{{ $category }}"
                        data-question="{{ \Illuminate\Support\Str::lower($faq->question) }}"
                        data-answer="{{ \Illuminate\Support\Str::lower($faq->answer) }}"
                        data-updated="{{ optional($faq->updated_at)->timestamp ?? 0 }}"
                    >
                        <span class="faq-item-number">#{{ str_pad((string) $loop->iteration, 2, '0', STR_PAD_LEFT) }}</span>
                        <div class="faq-item-body">
                            <span class="faq-category-tag {{ $loop->odd ? '' : 'is-gold' }}">
                                <x-outline-icon name="clipboard-document-list" />
                                {{ $category }}
                            </span>
                            <h4 class="faq-item-question">{{ $faq->question }}</h4>
                            <p class="faq-item-answer">{{ $faq->answer }}</p>
                            <div class="faq-meta">
                                <span class="faq-status {{ $faq->is_active ? '' : 'is-hidden' }}">{{ $faq->is_active ? 'Visible in Student Portal' : 'Hidden from Student Portal' }}</span>
                                <span class="faq-dot">.</span>
                                <span>Updated: {{ optional($faq->updated_at)->format('F j, Y') ?? 'Not yet' }}</span>
                            </div>
                        </div>
                        <div class="faq-item-actions">
                            <button type="button" class="faq-row-btn" data-preview-faq-id="{{ $faq->id }}">
                                <x-outline-icon name="eye" />
                                <span>Preview</span>
                            </button>
                            <button type="button" class="faq-row-btn" data-edit-faq-id="{{ $faq->id }}">
                                <x-outline-icon name="pencil-square" />
                                <span>Edit</span>
                            </button>
                            <form method="POST" action="{{ route('admin.settings.faqs.destroy', $faq) }}" onsubmit="return confirm('Delete this FAQ?');">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="faq-delete-btn">
                                    <x-outline-icon name="x-mark" />
                                    <span>Delete</span>
                                </button>
                            </form>
                        </div>
                    </article>
                @empty
                    <div class="faq-empty" data-faq-empty>No FAQs have been added yet.</div>
                @endforelse
            </div>

            @if($faqs->isNotEmpty())
                <div class="faq-empty" id="faqNoResults" style="display:none;">No FAQs match the current search or filter.</div>
                <div class="faq-pagination">
                    <span></span>
                    <div class="faq-pages" id="faqPages"></div>
                    <select class="faq-select faq-per-page" id="faqPerPage" aria-label="FAQs per page">
                        <option value="10">10 per page</option>
                        <option value="5">5 per page</option>
                        <option value="20">20 per page</option>
                        <option value="all">Show all</option>
                    </select>
                </div>
            @endif
        </section>
    </div>
</div>

<div class="faq-modal" id="faqModal" aria-hidden="true">
    <div class="faq-modal-card" role="dialog" aria-modal="true" aria-labelledby="faqModalTitle">
        <div class="faq-modal-head">
            <h3 id="faqModalTitle"><x-outline-icon name="question-mark-circle" /> <span id="faqModalTitleText">Add FAQ</span></h3>
            <button type="button" class="faq-modal-close" data-close-modal aria-label="Close"><x-outline-icon name="x-mark" /></button>
        </div>
        <form method="POST" action="{{ route('admin.settings.faqs.store') }}" class="faq-modal-body" id="faqForm">
            @csrf
            <input type="hidden" name="_method" value="POST" id="faqFormMethod" disabled>
            <div class="faq-field">
                <label for="faqCategory">Category</label>
                <select id="faqCategory" name="category" required>
                    <option value="">Select a category</option>
                    @foreach($faqCategories as $category)
                        <option value="{{ $category }}" {{ old('category') === $category ? 'selected' : '' }}>{{ $category }}</option>
                    @endforeach
                    <option value="__new__" {{ old('category') === '__new__' ? 'selected' : '' }}>New category...</option>
                </select>
            </div>
            <div class="faq-field" id="newFaqCategoryField" style="display:none;">
                <label for="faqCategoryNew">New Category Name</label>
                <input id="faqCategoryNew" name="category_new" type="text" maxlength="120" value="{{ old('category_new') }}" placeholder="e.g., Appointments and Request">
            </div>
            <div class="faq-field">
                <label for="faqQuestion">Question</label>
                <input id="faqQuestion" name="question" type="text" maxlength="500" value="{{ old('question') }}" required>
            </div>
            <div class="faq-field">
                <label for="faqAnswer">Answer</label>
                <textarea id="faqAnswer" name="answer" maxlength="10000" required>{{ old('answer') }}</textarea>
            </div>
            <label class="faq-toggle-row">
                <input id="faqIsActive" name="is_active" type="checkbox" value="1" checked>
                <span>Publish to student portal</span>
            </label>
            <div class="faq-modal-actions">
                <button type="button" class="faq-cancel-btn" data-close-modal>Cancel</button>
                <button type="submit" class="faq-save-btn"><x-outline-icon name="plus-circle" /> <span id="faqSaveText">Add FAQ</span></button>
            </div>
        </form>
    </div>
</div>

<div class="faq-modal" id="faqPreviewModal" aria-hidden="true">
    <div class="faq-modal-card" role="dialog" aria-modal="true" aria-labelledby="faqPreviewTitle">
        <div class="faq-modal-head">
            <h3><x-outline-icon name="eye" /> Preview FAQ</h3>
            <button type="button" class="faq-modal-close" data-close-modal aria-label="Close"><x-outline-icon name="x-mark" /></button>
        </div>
        <div class="faq-modal-body">
            <span class="faq-category-tag" id="faqPreviewCategory"></span>
            <h3 class="faq-preview-title" id="faqPreviewTitle"></h3>
            <p class="faq-preview-answer" id="faqPreviewAnswer"></p>
        </div>
    </div>
</div>

<div class="faq-modal" id="faqCategoryModal" aria-hidden="true">
    <div class="faq-modal-card" role="dialog" aria-modal="true" aria-labelledby="faqCategoryModalTitle">
        <div class="faq-modal-head">
            <h3 id="faqCategoryModalTitle"><x-outline-icon name="briefcase" /> Manage Categories</h3>
            <button type="button" class="faq-modal-close" data-close-modal aria-label="Close"><x-outline-icon name="x-mark" /></button>
        </div>
        <div class="faq-modal-body">
            @if($faqCategoryCounts->isNotEmpty())
                <div class="faq-category-manage-list">
                    @foreach($faqCategoryCounts as $category => $count)
                        <form method="POST" action="{{ route('admin.settings.faqs.category.rename') }}" class="faq-category-manage-item">
                            @csrf
                            <input type="hidden" name="current_category" value="{{ $category }}">
                            <div class="faq-field">
                                <p class="faq-category-manage-name">{{ $count }} {{ \Illuminate\Support\Str::plural('FAQ', $count) }}</p>
                                <input name="category_name" type="text" maxlength="120" value="{{ $category }}" required>
                            </div>
                            <button type="submit" class="faq-save-btn">Save</button>
                        </form>
                    @endforeach
                </div>
            @else
                <div class="faq-category-manage-empty">No categories to manage yet.</div>
            @endif
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
    (() => {
        const createUrl = @json(route('admin.settings.faqs.store'));
        const faqData = @json($faqData);
        const faqModal = document.getElementById('faqModal');
        const previewModal = document.getElementById('faqPreviewModal');
        const categoryModal = document.getElementById('faqCategoryModal');
        const faqForm = document.getElementById('faqForm');
        const methodInput = document.getElementById('faqFormMethod');
        const modalTitleText = document.getElementById('faqModalTitleText');
        const saveText = document.getElementById('faqSaveText');
        const categorySelect = document.getElementById('faqCategory');
        const categoryNewField = document.getElementById('newFaqCategoryField');
        const categoryNewInput = document.getElementById('faqCategoryNew');
        const questionInput = document.getElementById('faqQuestion');
        const answerInput = document.getElementById('faqAnswer');
        const isActiveInput = document.getElementById('faqIsActive');

        const searchInput = document.getElementById('faqAdminSearch');
        const categoryFilterSelect = document.getElementById('faqCategorySelect');
        const sortSelect = document.getElementById('faqSortSelect');
        const perPageSelect = document.getElementById('faqPerPage');
        const pagesContainer = document.getElementById('faqPages');
        const noResults = document.getElementById('faqNoResults');
        const faqList = document.getElementById('faqAdminList');
        const items = Array.from(document.querySelectorAll('[data-faq-item]'));
        const categoryButtons = Array.from(document.querySelectorAll('[data-category-filter]'));
        let currentPage = 1;

        const openModal = (modal) => {
            if (!modal) return;
            modal.classList.add('is-open');
            modal.setAttribute('aria-hidden', 'false');
        };

        const closeModal = (modal) => {
            if (!modal) return;
            modal.classList.remove('is-open');
            modal.setAttribute('aria-hidden', 'true');
        };

        const syncNewCategoryField = () => {
            const isNew = categorySelect?.value === '__new__';
            if (categoryNewField) categoryNewField.style.display = isNew ? 'grid' : 'none';
            if (categoryNewInput) categoryNewInput.required = isNew;
        };

        const resetFaqForm = () => {
            if (!faqForm) return;
            faqForm.action = createUrl;
            if (methodInput) {
                methodInput.disabled = true;
                methodInput.value = 'POST';
            }
            if (modalTitleText) modalTitleText.textContent = 'Add FAQ';
            if (saveText) saveText.textContent = 'Add FAQ';
            faqForm.reset();
            if (isActiveInput) isActiveInput.checked = true;
            syncNewCategoryField();
        };

        const openCreate = () => {
            resetFaqForm();
            openModal(faqModal);
            questionInput?.focus();
        };

        const openEdit = (payload) => {
            if (!faqForm) return;
            faqForm.action = payload.update_url;
            if (methodInput) {
                methodInput.disabled = false;
                methodInput.value = 'PUT';
            }
            if (modalTitleText) modalTitleText.textContent = 'Edit FAQ';
            if (saveText) saveText.textContent = 'Save Changes';
            if (categorySelect) categorySelect.value = payload.category;
            if (questionInput) questionInput.value = payload.question || '';
            if (answerInput) answerInput.value = payload.answer || '';
            if (isActiveInput) isActiveInput.checked = Boolean(payload.is_active);
            syncNewCategoryField();
            openModal(faqModal);
            questionInput?.focus();
        };

        const openPreview = (payload) => {
            document.getElementById('faqPreviewCategory').textContent = payload.category || 'General';
            document.getElementById('faqPreviewTitle').textContent = payload.question || '';
            document.getElementById('faqPreviewAnswer').textContent = payload.answer || '';
            openModal(previewModal);
        };

        const setCategoryFilter = (category) => {
            if (categoryFilterSelect) categoryFilterSelect.value = category;
            categoryButtons.forEach((button) => {
                button.classList.toggle('is-active', (button.dataset.categoryFilter || '') === category);
            });
            currentPage = 1;
            applyFilters();
        };

        const applyFilters = () => {
            const query = (searchInput?.value || '').trim().toLowerCase();
            const category = categoryFilterSelect?.value || '';
            const sort = sortSelect?.value || 'newest';
            const perPageValue = perPageSelect?.value || '10';
            const perPage = perPageValue === 'all' ? Infinity : Number(perPageValue);

            let visible = items.filter((item) => {
                const matchesCategory = !category || item.dataset.category === category;
                const haystack = `${item.dataset.question || ''} ${item.dataset.answer || ''} ${(item.dataset.category || '').toLowerCase()}`;
                return matchesCategory && (!query || haystack.includes(query));
            });

            visible.sort((a, b) => {
                if (sort === 'oldest') return Number(a.dataset.updated || 0) - Number(b.dataset.updated || 0);
                if (sort === 'az') return (a.dataset.question || '').localeCompare(b.dataset.question || '');
                if (sort === 'za') return (b.dataset.question || '').localeCompare(a.dataset.question || '');
                return Number(b.dataset.updated || 0) - Number(a.dataset.updated || 0);
            });

            visible.forEach((item) => faqList?.appendChild(item));

            const pageCount = perPage === Infinity ? 1 : Math.max(1, Math.ceil(visible.length / perPage));
            currentPage = Math.min(currentPage, pageCount);
            const start = perPage === Infinity ? 0 : (currentPage - 1) * perPage;
            const end = perPage === Infinity ? visible.length : start + perPage;
            const pageItems = new Set(visible.slice(start, end));

            items.forEach((item) => {
                item.style.display = pageItems.has(item) ? '' : 'none';
            });

            if (noResults) noResults.style.display = visible.length ? 'none' : 'block';
            if (pagesContainer) {
                pagesContainer.innerHTML = '';
                if (visible.length && pageCount > 1) {
                    for (let page = 1; page <= pageCount; page += 1) {
                        const button = document.createElement('button');
                        button.type = 'button';
                        button.className = `faq-page-btn${page === currentPage ? ' is-active' : ''}`;
                        button.textContent = page;
                        button.addEventListener('click', () => {
                            currentPage = page;
                            applyFilters();
                        });
                        pagesContainer.appendChild(button);
                    }
                }
            }
        };

        document.querySelectorAll('[data-open-faq-modal="create"]').forEach((button) => {
            button.addEventListener('click', openCreate);
        });

        document.querySelectorAll('[data-edit-faq-id]').forEach((button) => {
            button.addEventListener('click', () => openEdit(faqData[button.dataset.editFaqId] || {}));
        });

        document.querySelectorAll('[data-preview-faq-id]').forEach((button) => {
            button.addEventListener('click', () => openPreview(faqData[button.dataset.previewFaqId] || {}));
        });

        document.getElementById('openCategoryModal')?.addEventListener('click', () => openModal(categoryModal));

        document.querySelectorAll('[data-close-modal]').forEach((button) => {
            button.addEventListener('click', () => {
                closeModal(faqModal);
                closeModal(previewModal);
                closeModal(categoryModal);
            });
        });

        [faqModal, previewModal, categoryModal].forEach((modal) => {
            modal?.addEventListener('click', (event) => {
                if (event.target === modal) closeModal(modal);
            });
        });

        document.addEventListener('keydown', (event) => {
            if (event.key === 'Escape') {
                closeModal(faqModal);
                closeModal(previewModal);
                closeModal(categoryModal);
            }
        });

        categorySelect?.addEventListener('change', syncNewCategoryField);
        searchInput?.addEventListener('input', () => {
            currentPage = 1;
            applyFilters();
        });
        categoryFilterSelect?.addEventListener('change', () => setCategoryFilter(categoryFilterSelect.value));
        sortSelect?.addEventListener('change', applyFilters);
        perPageSelect?.addEventListener('change', () => {
            currentPage = 1;
            applyFilters();
        });
        categoryButtons.forEach((button) => {
            button.addEventListener('click', () => setCategoryFilter(button.dataset.categoryFilter || ''));
        });

        syncNewCategoryField();
        applyFilters();

        @if($errors->any())
            openModal(faqModal);
        @endif
    })();
</script>
@endpush
