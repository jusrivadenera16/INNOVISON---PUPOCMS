@push('styles')
<style>
    .clinic-options-page {
        --clinic-maroon: #8f1024;
        --clinic-deep: #6d0718;
        --clinic-border: #ead4d7;
        --clinic-muted: #64748b;
        color: #201016;
    }

    .clinic-options-header,
    .clinic-options-title-wrap,
    .clinic-option-main,
    .clinic-option-actions,
    .clinic-option-status,
    .clinic-option-modal-title,
    .clinic-option-modal-actions {
        display: flex;
        align-items: center;
    }

    .clinic-options-header {
        justify-content: space-between;
        gap: 18px;
        margin-bottom: 26px;
    }

    .clinic-options-title-wrap {
        gap: 14px;
        min-width: 0;
    }

    .clinic-options-title-icon,
    .clinic-option-code,
    .clinic-option-modal-icon,
    .clinic-option-modal-close {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        flex: 0 0 auto;
    }

    .clinic-options-title-icon {
        width: 46px;
        height: 46px;
        border-radius: 10px;
        color: #facc15;
        background: linear-gradient(135deg, var(--clinic-maroon), var(--clinic-deep));
        box-shadow: 0 12px 24px rgba(143, 16, 36, .18);
    }

    .clinic-options-title-icon svg,
    .clinic-options-action svg,
    .clinic-options-field svg,
    .clinic-option-code svg,
    .clinic-option-button svg,
    .clinic-option-modal-icon svg,
    .clinic-option-modal-close svg {
        width: 18px;
        height: 18px;
        fill: none;
        stroke: currentColor;
        stroke-width: 2;
        stroke-linecap: round;
        stroke-linejoin: round;
    }

    .clinic-options-title-wrap h1 {
        margin: 0;
        color: var(--clinic-deep);
        font-size: 1.35rem;
        font-weight: 900;
        letter-spacing: 0;
    }

    .clinic-options-title-wrap p {
        margin: 4px 0 0;
        color: var(--clinic-muted);
        font-size: .88rem;
        font-weight: 600;
    }

    .clinic-options-action,
    .clinic-option-button,
    .clinic-option-modal-actions button {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        gap: 8px;
        position: relative;
        overflow: hidden;
        min-height: 40px;
        border: 1px solid rgba(250, 204, 21, .35);
        border-radius: 8px;
        padding: 0 15px;
        color: #fff;
        background: linear-gradient(135deg, var(--clinic-maroon), var(--clinic-deep));
        font-size: .8rem;
        font-weight: 900;
        cursor: pointer;
        text-decoration: none;
        transition: color .18s ease, background .18s ease, transform .18s ease, box-shadow .18s ease, border-color .18s ease;
    }

    .clinic-options-action {
        min-height: 46px;
        padding: 0 20px;
        white-space: nowrap;
    }

    .clinic-options-action::after,
    .clinic-option-button::after,
    .clinic-option-modal-actions button::after {
        content: "";
        position: absolute;
        inset: 0;
        background: linear-gradient(120deg, transparent 0%, rgba(255, 246, 179, .18) 25%, rgba(255, 246, 179, .72) 50%, rgba(255, 246, 179, .18) 75%, transparent 100%);
        transform: translateX(-135%);
        transition: transform .85s ease;
        pointer-events: none;
    }

    .clinic-options-action:hover,
    .clinic-option-button:hover,
    .clinic-option-modal-actions button:hover {
        transform: translateY(-1px);
        color: var(--clinic-maroon);
        border-color: rgba(250, 204, 21, .9);
        background: #facc15;
        box-shadow: 0 10px 20px rgba(143, 16, 36, .14);
    }

    .clinic-options-action:hover::after,
    .clinic-option-button:hover::after,
    .clinic-option-modal-actions button:hover::after {
        transform: translateX(135%);
    }

    .clinic-options-action > *,
    .clinic-option-button > *,
    .clinic-option-modal-actions button > * {
        position: relative;
        z-index: 1;
    }

    .clinic-options-toolbar {
        display: grid;
        grid-template-columns: minmax(240px, 1fr) auto;
        gap: 14px;
        align-items: center;
        margin-bottom: 14px;
    }

    .clinic-options-field {
        position: relative;
        display: flex;
        align-items: center;
        min-height: 48px;
        color: #64748b;
    }

    .clinic-options-field svg {
        position: absolute;
        left: 16px;
        pointer-events: none;
    }

    .clinic-options-field input,
    .clinic-option-modal-box input {
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

    .clinic-options-field input:focus,
    .clinic-option-modal-box input:focus {
        border-color: rgba(143, 16, 36, .42);
        box-shadow: 0 0 0 4px rgba(143, 16, 36, .08);
    }

    .clinic-options-count {
        margin: 8px 0 14px;
        color: #263241;
        font-size: .86rem;
        font-weight: 800;
    }

    .clinic-options-list {
        display: grid;
        gap: 10px;
        max-height: clamp(320px, calc(100vh - 350px), 620px);
        overflow-y: auto;
        padding: 2px 4px 4px 0;
        scrollbar-width: thin;
    }

    .clinic-option-row {
        display: grid;
        grid-template-columns: 44px minmax(0, 1fr) auto auto;
        align-items: center;
        gap: 14px;
        min-height: 70px;
        padding: 12px 14px;
        border: 1px solid var(--clinic-border);
        border-radius: 9px;
        background: #fff;
    }

    .clinic-option-row[hidden] {
        display: none;
    }

    .clinic-option-code {
        width: 38px;
        height: 38px;
        border-radius: 9px;
        color: var(--clinic-maroon);
        border: 1px solid #f0d8cc;
        background: #fff8f5;
    }

    .clinic-option-main {
        min-width: 0;
        gap: 12px;
    }

    .clinic-option-copy {
        min-width: 0;
    }

    .clinic-option-name {
        overflow: hidden;
        color: #2c1820;
        font-size: .92rem;
        font-weight: 900;
        text-overflow: ellipsis;
        white-space: nowrap;
    }

    .clinic-option-meta {
        margin-top: 3px;
        color: #64748b;
        font-size: .74rem;
        font-weight: 700;
    }

    .clinic-option-status {
        gap: 7px;
        min-height: 30px;
        border-radius: 999px;
        padding: 0 11px;
        color: #166534;
        background: #dcfce7;
        font-size: .72rem;
        font-weight: 900;
        white-space: nowrap;
    }

    .clinic-option-status.is-inactive {
        color: #92400e;
        background: #fef3c7;
    }

    .clinic-option-actions {
        justify-content: flex-end;
        gap: 8px;
    }

    .clinic-option-button--edit {
        color: var(--clinic-maroon);
        border-color: #f0d8cc;
        background: #fffdfb;
    }

    .clinic-option-button--delete {
        color: #fff;
        background: linear-gradient(135deg, var(--clinic-maroon), var(--clinic-deep));
    }

    .clinic-option-empty {
        padding: 26px;
        border: 1px dashed #e8cfd3;
        border-radius: 10px;
        color: #64748b;
        background: #fff9fa;
        text-align: center;
        font-weight: 800;
    }

    .clinic-option-feedback {
        margin-bottom: 14px;
        padding: 11px 14px;
        border: 1px solid #bbf7d0;
        border-radius: 8px;
        color: #166534;
        background: #f0fdf4;
        font-size: .82rem;
        font-weight: 800;
    }

    .clinic-option-errors {
        margin: 0 0 14px;
        padding: 11px 14px 11px 32px;
        border: 1px solid #fecaca;
        border-radius: 8px;
        color: #991b1b;
        background: #fef2f2;
        font-size: .82rem;
        font-weight: 700;
    }

    .clinic-option-modal-overlay {
        display: none;
        position: fixed;
        inset: 0;
        z-index: 1000;
        align-items: center;
        justify-content: center;
        padding: 18px;
        background: rgba(15, 23, 42, .52);
    }

    .clinic-option-modal-overlay.is-open {
        display: flex;
    }

    .clinic-option-modal-box {
        width: min(100%, 520px);
        max-height: 90vh;
        overflow-y: auto;
        border: 1px solid #f0d8cc;
        border-radius: 12px;
        background: #fff;
        box-shadow: 0 24px 60px rgba(15, 23, 42, .22);
    }

    .clinic-option-modal-header {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 18px;
        padding: 18px 22px;
        color: #fff;
        background: linear-gradient(135deg, #8f1024, #6d0718);
    }

    .clinic-option-modal-title {
        gap: 12px;
        min-width: 0;
    }

    .clinic-option-modal-icon {
        width: 40px;
        height: 40px;
        border: 1px solid rgba(250, 204, 21, .35);
        border-radius: 9px;
        color: #facc15;
        background: rgba(255, 255, 255, .1);
    }

    .clinic-option-modal-title h3 {
        margin: 0;
        color: #fff;
        font-size: 1.08rem;
        font-weight: 900;
    }

    .clinic-option-modal-title p {
        margin: 3px 0 0;
        color: rgba(255, 255, 255, .8);
        font-size: .78rem;
        font-weight: 600;
    }

    .clinic-option-modal-close {
        position: relative;
        width: 38px;
        height: 38px;
        border: 1px solid rgba(255, 255, 255, .3);
        border-radius: 50%;
        color: #fff;
        background: transparent;
        cursor: pointer;
    }

    .clinic-option-modal-close:hover {
        color: var(--clinic-maroon);
        border-color: #facc15;
        background: #facc15;
    }

    .clinic-option-modal-body {
        padding: 22px;
    }

    .clinic-option-modal-field {
        display: grid;
        gap: 7px;
        margin-bottom: 16px;
    }

    .clinic-option-modal-field label {
        color: #334155;
        font-size: .75rem;
        font-weight: 900;
        letter-spacing: .03em;
        text-transform: uppercase;
    }

    .clinic-option-modal-field input {
        padding: 0 13px;
    }

    .clinic-option-modal-field--check {
        display: flex;
        align-items: center;
        gap: 9px;
    }

    .clinic-option-modal-field--check input {
        width: 17px;
        min-height: 17px;
        accent-color: var(--clinic-maroon);
    }

    .clinic-option-modal-field--check label {
        text-transform: none;
        letter-spacing: 0;
    }

    .clinic-option-modal-actions {
        justify-content: flex-end;
        gap: 9px;
        margin-top: 22px;
    }

    .clinic-option-modal-actions .clinic-option-modal-cancel {
        color: var(--clinic-maroon);
        border-color: #e2c4c8;
        background: #fff8f5;
    }

    html[data-theme="dark"] .clinic-options-page {
        --clinic-border: rgba(250, 204, 21, .24);
        --clinic-muted: #cbd5e1;
        color: #f8fafc;
    }

    html[data-theme="dark"] .clinic-options-title-wrap h1,
    html[data-theme="dark"] .clinic-options-count,
    html[data-theme="dark"] .clinic-option-name,
    html[data-theme="dark"] .clinic-option-modal-field label {
        color: #f8fafc;
    }

    html[data-theme="dark"] .clinic-options-field input,
    html[data-theme="dark"] .clinic-option-modal-box input {
        border-color: #475569;
        color: #f8fafc;
        background: #1e293b;
    }

    html[data-theme="dark"] .clinic-option-row {
        border-color: rgba(250, 204, 21, .24);
        background: #111827;
    }

    html[data-theme="dark"] .clinic-option-code {
        color: #facc15;
        border-color: rgba(250, 204, 21, .35);
        background: #1e293b;
    }

    html[data-theme="dark"] .clinic-option-meta {
        color: #94a3b8;
    }

    html[data-theme="dark"] .clinic-option-empty {
        border-color: rgba(250, 204, 21, .25);
        color: #cbd5e1;
        background: #111827;
    }

    html[data-theme="dark"] .clinic-option-feedback {
        border-color: rgba(134, 239, 172, .35);
        color: #bbf7d0;
        background: rgba(20, 83, 45, .34);
    }

    html[data-theme="dark"] .clinic-option-errors {
        border-color: rgba(252, 165, 165, .35);
        color: #fecaca;
        background: rgba(127, 29, 29, .28);
    }

    html[data-theme="dark"] .clinic-option-modal-box {
        border-color: rgba(250, 204, 21, .3);
        background: #0f172a;
    }

    html[data-theme="dark"] .clinic-option-modal-field--check label {
        color: #e2e8f0;
    }

    html[data-theme="dark"] .clinic-option-modal-actions .clinic-option-modal-cancel {
        color: #f8fafc;
        border-color: #475569;
        background: #1e293b;
    }

    @media (max-width: 760px) {
        .clinic-options-header,
        .clinic-options-toolbar {
            grid-template-columns: 1fr;
            flex-direction: column;
            align-items: stretch;
        }

        .clinic-options-action {
            width: 100%;
        }

        .clinic-option-row {
            grid-template-columns: 38px minmax(0, 1fr) auto;
        }

        .clinic-option-status {
            grid-column: 2 / 3;
            justify-self: start;
        }

        .clinic-option-actions {
            grid-column: 3;
            grid-row: 1 / span 2;
        }
    }
</style>
@endpush

<div class="clinic-options-page">
    @include('admin.partials.report-breadcrumb', ['items' => [
        ['label' => 'Settings', 'url' => route('admin.settings')],
        ['label' => 'Medical Configuration', 'url' => route('admin.settings.medical')],
        ['label' => $pageTitle],
    ], 'class' => 'report-breadcrumb--settings-medical'])

    <div class="clinic-options-header">
        <div class="clinic-options-title-wrap">
            <span class="clinic-options-title-icon" aria-hidden="true">
                <x-outline-icon name="{{ $pageIcon }}" />
            </span>
            <div>
                <h1>{{ $pageTitle }}</h1>
                <p>{{ $pageDescription }}</p>
            </div>
        </div>
        <button type="button" class="clinic-options-action" id="clinicOptionAddButton">
            <x-outline-icon name="plus" />
            <span>{{ $addLabel }}</span>
        </button>
    </div>

    @if(session('status'))
        <div class="clinic-option-feedback">{{ session('status') }}</div>
    @endif
    @if($errors->any())
        <ul class="clinic-option-errors">
            @foreach($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
    @endif

    <div class="clinic-options-toolbar">
        <label class="clinic-options-field" for="clinicOptionSearch">
            <x-outline-icon name="magnifying-glass" />
            <input type="search" id="clinicOptionSearch" placeholder="Search {{ strtolower($groupLabel) }}..." autocomplete="off">
        </label>
    </div>

    <div class="clinic-options-count" id="clinicOptionCount">Showing {{ $options->count() }} {{ strtolower($groupLabel) }}</div>

    <div class="clinic-options-list" id="clinicOptionList">
        @forelse($options as $option)
            <div class="clinic-option-row" data-option-row data-search-value="{{ strtolower($option->name . ' ' . $option->code) }}">
                <span class="clinic-option-code" title="{{ $option->code }}">
                    <x-outline-icon name="{{ $optionGroup === 'online_consultation' ? 'globe-alt' : 'document-text' }}" />
                </span>
                <div class="clinic-option-copy">
                    <div class="clinic-option-name">{{ $option->name }}</div>
                    <div class="clinic-option-meta">
                        {{ $optionGroup === 'online_consultation' ? 'Appointment label: Consultation c/o ' . $option->name : 'Code: ' . $option->code }}
                    </div>
                </div>
                <span class="clinic-option-status {{ $option->is_active ? '' : 'is-inactive' }}">
                    {{ $option->is_active ? 'Active' : 'Archived' }}
                </span>
                <div class="clinic-option-actions">
                    <button type="button" class="clinic-option-button clinic-option-button--edit" data-edit-option data-option-id="{{ $option->id }}" data-option-name="{{ $option->name }}" data-option-order="{{ $option->sort_order }}" data-option-active="{{ $option->is_active ? '1' : '0' }}" aria-label="Edit {{ $option->name }}">
                        <x-outline-icon name="pencil-square" />
                        <span>Edit</span>
                    </button>
                    <form method="POST" action="{{ route('clinic-service-options.destroy', ['optionGroup' => $optionGroup, 'option' => $option]) }}" onsubmit="return confirm('Remove or archive this option?');">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="clinic-option-button clinic-option-button--delete" aria-label="Remove {{ $option->name }}">
                            <x-outline-icon name="trash" />
                        </button>
                    </form>
                </div>
            </div>
        @empty
            <div class="clinic-option-empty" data-empty-state>No {{ strtolower($groupLabel) }} have been configured yet.</div>
        @endforelse
        <div class="clinic-option-empty" id="clinicOptionNoResults" hidden>No matching {{ strtolower($groupLabel) }} found.</div>
    </div>
</div>

<div class="clinic-option-modal-overlay" id="clinicOptionModal" aria-hidden="true">
    <div class="clinic-option-modal-box" role="dialog" aria-modal="true" aria-labelledby="clinicOptionModalTitle">
        <div class="clinic-option-modal-header">
            <div class="clinic-option-modal-title">
                <span class="clinic-option-modal-icon" aria-hidden="true"><x-outline-icon name="{{ $pageIcon }}" /></span>
                <div>
                    <h3 id="clinicOptionModalTitle">Add {{ $groupLabel }}</h3>
                    <p>Keep this list focused on active clinic workflow choices.</p>
                </div>
            </div>
            <button type="button" class="clinic-option-modal-close" id="clinicOptionModalClose" aria-label="Close dialog">
                <x-outline-icon name="x-mark" />
            </button>
        </div>
        <form method="POST" id="clinicOptionForm" action="{{ route('clinic-service-options.store', ['optionGroup' => $optionGroup]) }}">
            @csrf
            <input type="hidden" name="_method" value="POST" id="clinicOptionMethod">
            <div class="clinic-option-modal-body">
                <div class="clinic-option-modal-field">
                    <label for="clinicOptionName">{{ $optionGroup === 'online_consultation' ? 'Doctor / Provider Name' : 'Name' }}</label>
                    <input type="text" id="clinicOptionName" name="name" maxlength="160" required placeholder="{{ $optionGroup === 'online_consultation' ? 'e.g. Dr. Juan Dela Cruz' : 'Enter a name' }}">
                </div>
                <div class="clinic-option-modal-field">
                    <label for="clinicOptionOrder">Display Order</label>
                    <input type="number" id="clinicOptionOrder" name="sort_order" min="0" max="9999" value="0">
                </div>
                <div class="clinic-option-modal-field clinic-option-modal-field--check">
                    <input type="hidden" name="is_active" value="0">
                    <input type="checkbox" id="clinicOptionActive" name="is_active" value="1" checked>
                    <label for="clinicOptionActive">Active and available in the workflow</label>
                </div>
                <div class="clinic-option-modal-actions">
                    <button type="button" class="clinic-option-modal-cancel" id="clinicOptionModalCancel">Close</button>
                    <button type="submit" class="clinic-option-modal-save"><x-outline-icon name="check" /><span>Save</span></button>
                </div>
            </div>
        </form>
    </div>
</div>
@push('scripts')
<script>
(() => {
    const modal = document.getElementById('clinicOptionModal');
    const form = document.getElementById('clinicOptionForm');
    const methodInput = document.getElementById('clinicOptionMethod');
    const modalTitle = document.getElementById('clinicOptionModalTitle');
    const nameInput = document.getElementById('clinicOptionName');
    const orderInput = document.getElementById('clinicOptionOrder');
    const activeInput = document.getElementById('clinicOptionActive');
    const searchInput = document.getElementById('clinicOptionSearch');
    const count = document.getElementById('clinicOptionCount');
    const noResults = document.getElementById('clinicOptionNoResults');
    const rows = Array.from(document.querySelectorAll('[data-option-row]'));
    const storeUrl = @json(route('clinic-service-options.store', ['optionGroup' => $optionGroup]));
    const updatePrefix = @json(url('/admin/clinic-service-options/' . $optionGroup));

    const closeModal = () => {
        modal.classList.remove('is-open');
        modal.setAttribute('aria-hidden', 'true');
    };

    const openModal = (option = null) => {
        const editing = Boolean(option);
        form.action = editing ? `${updatePrefix}/${option.id}` : storeUrl;
        methodInput.value = editing ? 'PUT' : 'POST';
        modalTitle.textContent = editing ? 'Edit {{ $groupLabel }}' : 'Add {{ $groupLabel }}';
        nameInput.value = editing ? option.name : '';
        orderInput.value = editing ? option.order : 0;
        activeInput.checked = editing ? option.active === '1' : true;
        modal.classList.add('is-open');
        modal.setAttribute('aria-hidden', 'false');
        window.setTimeout(() => nameInput.focus(), 30);
    };

    document.getElementById('clinicOptionAddButton')?.addEventListener('click', () => openModal());
    document.getElementById('clinicOptionModalClose')?.addEventListener('click', closeModal);
    document.getElementById('clinicOptionModalCancel')?.addEventListener('click', closeModal);
    modal?.addEventListener('click', (event) => {
        if (event.target === modal) closeModal();
    });
    document.addEventListener('keydown', (event) => {
        if (event.key === 'Escape' && modal?.classList.contains('is-open')) closeModal();
    });

    document.querySelectorAll('[data-edit-option]').forEach((button) => {
        button.addEventListener('click', () => openModal({
            id: button.dataset.optionId,
            name: button.dataset.optionName || '',
            order: button.dataset.optionOrder || 0,
            active: button.dataset.optionActive || '0',
        }));
    });

    searchInput?.addEventListener('input', () => {
        const query = searchInput.value.trim().toLowerCase();
        let visible = 0;
        rows.forEach((row) => {
            const matches = query === '' || (row.dataset.searchValue || '').includes(query);
            row.hidden = !matches;
            if (matches) visible++;
        });
        if (count) count.textContent = `Showing ${visible} {{ strtolower($groupLabel) }}`;
        if (noResults) noResults.hidden = visible !== 0 || rows.length === 0;
    });
})();
</script>
@endpush
