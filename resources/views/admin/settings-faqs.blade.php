@extends('layouts.admin')

@section('title', 'FAQs')

@push('styles')
<style>
    .faq-manager { width: min(1180px, 100%); margin: 0 auto; }
    .faq-manager-head, .faq-manager-panel { background: #fff; border: 1px solid rgba(112,19,27,.12); box-shadow: 0 18px 42px rgba(15,23,42,.06); }
    .faq-manager-head { display:flex; align-items:center; justify-content:space-between; gap:18px; padding:24px 28px; border-radius:16px; margin-bottom:18px; }
    .faq-manager-head h2 { margin:0; color:#111827; font-size:24px; font-weight:900; }
    .faq-manager-head p { margin:5px 0 0; color:#64748b; font-size:13px; }
    .faq-add-btn, .faq-save-btn { border:1px solid #facc15; background:#70131b; color:#fff; border-radius:10px; padding:11px 16px; font:inherit; font-weight:800; cursor:pointer; transition:background .18s ease,color .18s ease,transform .18s ease; }
    .faq-add-btn:hover, .faq-save-btn:hover { background:#facc15; color:#70131b; transform:translateY(-1px); }
    .faq-manager-grid { display:grid; grid-template-columns:minmax(280px,.72fr) minmax(420px,1.28fr); gap:18px; align-items:start; }
    .faq-manager-panel { border-radius:14px; padding:22px; }
    .faq-panel-head { display:flex; justify-content:space-between; align-items:center; gap:12px; margin-bottom:16px; }
    .faq-panel-head h3 { margin:0; color:#111827; font-size:17px; font-weight:900; }
    .faq-count { color:#8f1827; font-size:12px; font-weight:800; }
    .faq-list { display:grid; gap:10px; max-height:620px; overflow:auto; }
    .faq-item { border:1px solid #e2e8f0; border-radius:10px; padding:14px; background:#fffafa; }
    .faq-item-top { display:flex; justify-content:space-between; align-items:flex-start; gap:10px; }
    .faq-item-question { margin:0; color:#111827; font-size:14px; font-weight:850; line-height:1.4; }
    .faq-item-answer { margin:8px 0 0; color:#64748b; font-size:13px; line-height:1.55; white-space:pre-line; }
    .faq-delete-btn { border:0; background:transparent; color:#991b1b; cursor:pointer; font-size:12px; font-weight:800; padding:2px 0; }
    .faq-delete-btn:hover { color:#d97706; }
    .faq-empty { padding:28px 12px; text-align:center; color:#64748b; font-size:13px; border:1px dashed #cbd5e1; border-radius:10px; }
    .faq-modal { position:fixed; inset:0; z-index:1200; display:none; place-items:center; padding:20px; background:rgba(15,23,42,.62); }
    .faq-modal.is-open { display:grid; }
    .faq-modal-card { width:min(560px,100%); background:#fff; border-radius:14px; box-shadow:0 24px 70px rgba(15,23,42,.3); overflow:hidden; }
    .faq-modal-head { display:flex; justify-content:space-between; align-items:center; gap:12px; padding:18px 20px; background:#8f1827; color:#fff; }
    .faq-modal-head h3 { margin:0; color:#fff !important; font-size:18px; font-weight:900; }
    .faq-modal-title { display:flex; align-items:center; gap:9px; }
    .faq-modal-title svg, .faq-manager-title svg { width:22px; height:22px; }
    .faq-manager-title { display:flex; align-items:center; gap:10px; }
    .faq-manager-title svg { color:#8f1827; }
    .faq-modal-close { width:34px; height:34px; display:grid; place-items:center; border:1px solid rgba(255,255,255,.4); border-radius:50%; background:transparent; color:#fff; cursor:pointer; font-size:22px; line-height:1; }
    .faq-modal-close:hover { background:#facc15; color:#70131b; border-color:#facc15; }
    .faq-modal-body { display:grid; gap:14px; padding:22px; }
    .faq-field { display:grid; gap:6px; }
    .faq-field label { color:#334155; font-size:12px; font-weight:850; }
    .faq-field input, .faq-field textarea, .faq-field select { width:100%; border:1px solid #cbd5e1; border-radius:9px; padding:11px 12px; color:#111827; background:#fff; font:inherit; font-size:14px; outline:none; }
    .faq-field textarea { min-height:150px; resize:vertical; }
    .faq-field input:focus, .faq-field textarea:focus, .faq-field select:focus { border-color:#8f1827; box-shadow:0 0 0 3px rgba(143,24,39,.1); }
    .faq-category-tag { display:inline-flex; margin:0 0 8px; padding:3px 8px; border-radius:999px; background:#fff0f2; color:#8f1827; font-size:10px; font-weight:850; }
    .faq-modal-actions { display:flex; justify-content:flex-end; gap:10px; padding-top:4px; }
    .faq-cancel-btn { border:1px solid #cbd5e1; background:#fff; color:#334155; border-radius:9px; padding:10px 16px; font:inherit; font-weight:800; cursor:pointer; }
    .faq-cancel-btn:hover { background:#f1f5f9; }
    .faq-alert { margin-bottom:14px; padding:11px 14px; border-radius:9px; background:#ecfdf5; border:1px solid #a7f3d0; color:#047857; font-size:13px; font-weight:700; }
    @media (max-width: 760px) {
        .faq-manager-head { align-items:flex-start; flex-direction:column; }
        .faq-add-btn { width:100%; }
        .faq-manager-grid { grid-template-columns:1fr; }
    }
</style>
@endpush

@section('content')
@php
    $faqCategories = $faqs->pluck('category')->filter()->unique()->sort()->values();
@endphp
<div class="faq-manager">
    <div class="faq-manager-head">
        <div class="faq-manager-title">
            <x-outline-icon name="question-mark-circle" />
            <div>
            <h2>FAQs</h2>
            <p>Manage the questions and answers shown in the student portal.</p>
            </div>
        </div>
        <button type="button" class="faq-add-btn" id="openFaqModal">+ Add FAQ</button>
    </div>

    @if(session('success'))
        <div class="faq-alert">{{ session('success') }}</div>
    @endif

    <div class="faq-manager-grid">
        <section class="faq-manager-panel">
            <div class="faq-panel-head">
                <h3>FAQ Manager</h3>
                <span class="faq-count">{{ $faqs->count() }} total</span>
            </div>
            <p style="margin:0;color:#64748b;font-size:13px;line-height:1.6;">Add clear answers for common clinic questions. Published FAQs are visible to students.</p>
        </section>

        <section class="faq-manager-panel">
            <div class="faq-panel-head">
                <h3>Added FAQs</h3>
                <span class="faq-count">Student portal</span>
            </div>
            <div class="faq-list">
                @forelse($faqs as $faq)
                    <article class="faq-item">
                        <div class="faq-item-top">
                            <div>
                                <span class="faq-category-tag">{{ $faq->category ?: 'General' }}</span>
                                <h4 class="faq-item-question">{{ $faq->question }}</h4>
                            </div>
                            <form method="POST" action="{{ route('admin.settings.faqs.destroy', $faq) }}" onsubmit="return confirm('Remove this FAQ?');">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="faq-delete-btn">Remove</button>
                            </form>
                        </div>
                        <p class="faq-item-answer">{{ $faq->answer }}</p>
                    </article>
                @empty
                    <div class="faq-empty">No FAQs have been added yet.</div>
                @endforelse
            </div>
        </section>
    </div>
</div>

<div class="faq-modal" id="faqModal" aria-hidden="true">
    <div class="faq-modal-card" role="dialog" aria-modal="true" aria-labelledby="faqModalTitle">
        <div class="faq-modal-head">
            <h3 id="faqModalTitle" class="faq-modal-title"><x-outline-icon name="question-mark-circle" /> Add FAQ</h3>
            <button type="button" class="faq-modal-close" id="closeFaqModal" aria-label="Close">&times;</button>
        </div>
        <form method="POST" action="{{ route('admin.settings.faqs.store') }}" class="faq-modal-body">
            @csrf
            <div class="faq-field">
                <label for="faqCategory">Category</label>
                <select id="faqCategory" name="category" required>
                    <option value="">Select a category</option>
                    @foreach($faqCategories as $category)
                        <option value="{{ $category }}" @selected(old('category') === $category)>{{ $category }}</option>
                    @endforeach
                    <option value="__new__" @selected(old('category') === '__new__')>New category...</option>
                </select>
            </div>
            <div class="faq-field" id="newFaqCategoryField" style="display:none;">
                <label for="faqCategoryNew">New Category Name</label>
                <input id="faqCategoryNew" name="category_new" type="text" maxlength="120" value="{{ old('category_new') }}" placeholder="e.g., Appointments & Requests">
            </div>
            <div class="faq-field">
                <label for="faqQuestion">Question</label>
                <input id="faqQuestion" name="question" type="text" maxlength="500" value="{{ old('question') }}" required>
            </div>
            <div class="faq-field">
                <label for="faqAnswer">Answer</label>
                <textarea id="faqAnswer" name="answer" maxlength="10000" required>{{ old('answer') }}</textarea>
            </div>
            <div class="faq-modal-actions">
                <button type="button" class="faq-cancel-btn" id="cancelFaqModal">Cancel</button>
                <button type="submit" class="faq-save-btn">Add FAQ</button>
            </div>
        </form>
    </div>
</div>
@endsection

@push('scripts')
<script>
    (() => {
        const modal = document.getElementById('faqModal');
        const open = document.getElementById('openFaqModal');
        const category = document.getElementById('faqCategory');
        const newCategoryField = document.getElementById('newFaqCategoryField');
        const syncCategoryField = () => {
            const isNew = category?.value === '__new__';
            if (newCategoryField) newCategoryField.style.display = isNew ? 'grid' : 'none';
            const newCategoryInput = document.getElementById('faqCategoryNew');
            if (newCategoryInput) newCategoryInput.required = isNew;
        };
        const close = () => {
            if (!modal) return;
            modal.classList.remove('is-open');
            modal.setAttribute('aria-hidden', 'true');
        };
        if (open && modal) {
            category?.addEventListener('change', syncCategoryField);
            syncCategoryField();
            open.addEventListener('click', () => {
                modal.classList.add('is-open');
                modal.setAttribute('aria-hidden', 'false');
                document.getElementById('faqQuestion')?.focus();
            });
            document.getElementById('closeFaqModal')?.addEventListener('click', close);
            document.getElementById('cancelFaqModal')?.addEventListener('click', close);
            modal.addEventListener('click', (event) => { if (event.target === modal) close(); });
            document.addEventListener('keydown', (event) => { if (event.key === 'Escape') close(); });
        }
        @if($errors->any())
            modal?.classList.add('is-open');
            modal?.setAttribute('aria-hidden', 'false');
        @endif
    })();
</script>
@endpush
