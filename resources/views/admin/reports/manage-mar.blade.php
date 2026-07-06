@extends('layouts.admin')

@section('title', 'Manage MAR Conditions')

@push('styles')

<style>
    .card { background: #fff; border-radius: 12px; padding: 24px; box-shadow: 0 4px 12px rgba(0,0,0,0.05); border: 1px solid #f0f0f0; margin-bottom: 24px; }
    h2 { color: #8B0000; font-size: 22px; margin-bottom: 20px; border-bottom: 2px solid #8B0000; padding-bottom: 10px; }
    h3 { color: #334155; font-size: 18px; margin-top: 10px; }

    .mar-table { width: 100%; border-collapse: collapse; margin-top: 15px; }
    .mar-table th { background: #f8fafc; color: #64748b; text-align: left; padding: 12px; border-bottom: 2px solid #eee; font-size: 13px; }
    .mar-table td { padding: 12px; border-bottom: 1px solid #f1f5f9; font-size: 14px; color: #334155; }
    
    .manage-section { background: #fdfdfd; border: 1px dashed #cbd5e1; padding: 20px; border-radius: 10px; }

    .btn-save {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        gap: 8px;
        position: relative;
        overflow: hidden;
        min-height: 52px;
        background: linear-gradient(135deg, #70131B, #8f2230);
        color: #ffffff;
        border: 1px solid #8f2230;
        padding: 14px 18px;
        border-radius: 999px;
        cursor: pointer;
        font-weight: 800;
        transition: color .18s ease, background .18s ease, border-color .18s ease, transform .18s ease, box-shadow .18s ease;
        box-shadow:
            0 0 0 3px rgba(112, 19, 27, 0.10),
            0 10px 22px rgba(112, 19, 27, 0.16);
        z-index: 0;
        white-space: nowrap;
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

    .btn-save-icon {
        width: 20px;
        height: 20px;
        flex: 0 0 auto;
        stroke: currentColor;
        stroke-width: 2.2;
    }

    .form-control { padding: 8px; border: 1px solid #ddd; border-radius: 5px; width: 100%; }

    .manage-condition-input {
        width: 100%;
        min-height: 52px;
        padding: 14px 16px;
        border: 1px solid rgba(127, 29, 29, 0.22);
        border-radius: 18px;
        font-size: 14px;
        color: #111111;
        font-weight: 700;
        background:
            radial-gradient(circle at top right, rgba(250, 204, 21, 0.10), transparent 36%),
            linear-gradient(180deg, #ffffff 0%, #fff8f6 100%);
        box-shadow:
            0 12px 22px rgba(15, 23, 42, 0.08),
            inset 0 1px 0 rgba(255,255,255,0.86);
        transition: all 0.2s ease;
    }

    .manage-condition-input::placeholder {
        color: #6b7280;
        font-weight: 700;
    }

    .manage-condition-input:hover {
        border-color: rgba(139, 0, 0, 0.34);
        box-shadow:
            0 14px 24px rgba(15, 23, 42, 0.10),
            0 8px 18px rgba(139, 0, 0, 0.05),
            inset 0 1px 0 rgba(255,255,255,0.90);
        transform: translateY(-1px);
    }

    .manage-condition-input:focus {
        outline: none;
        border-color: #8B0000;
        background: #ffffff;
        box-shadow:
            0 0 0 4px rgba(139, 0, 0, 0.06),
            0 14px 24px rgba(139, 0, 0, 0.10),
            inset 0 1px 0 rgba(255,255,255,0.88);
        transform: translateY(-1px);
    }

    .manage-category-wrap {
        position: relative;
    }

    .manage-category-select {
        position: absolute;
        width: 1px !important;
        height: 1px !important;
        opacity: 0;
        pointer-events: none;
        padding: 0 !important;
        border: 0 !important;
        margin: 0 !important;
    }

    .manage-category-display {
        width: 100%;
        min-height: 52px;
        padding: 14px 52px 14px 16px;
        border: 1px solid rgba(127, 29, 29, 0.22);
        border-radius: 18px;
        font-size: 14px;
        color: #111111;
        background:
            radial-gradient(circle at top right, rgba(250, 204, 21, 0.10), transparent 36%),
            linear-gradient(180deg, #ffffff 0%, #fff8f6 100%);
        box-shadow:
            0 12px 22px rgba(15, 23, 42, 0.08),
            inset 0 1px 0 rgba(255,255,255,0.86);
        cursor: pointer;
        font-weight: 700;
        text-align: left;
        transition: all 0.2s ease;
    }

    .manage-category-display:hover,
    .manage-category-display.is-open,
    .manage-category-display:focus {
        outline: none;
        border-color: #8B0000;
        box-shadow:
            0 0 0 4px rgba(139, 0, 0, 0.06),
            0 14px 24px rgba(139, 0, 0, 0.10),
            inset 0 1px 0 rgba(255,255,255,0.88);
    }

    .manage-category-wrap::after {
        content: "";
        position: absolute;
        top: 26px;
        right: 18px;
        width: 10px;
        height: 10px;
        border-right: 2px solid #8B0000;
        border-bottom: 2px solid #8B0000;
        transform: translateY(-65%) rotate(45deg);
        pointer-events: none;
        transition: transform 0.18s ease;
    }

    .manage-category-wrap::before {
        content: "";
        position: absolute;
        top: 26px;
        right: 42px;
        transform: translateY(-50%);
        width: 1px;
        height: 24px;
        background: rgba(148, 163, 184, 0.24);
        pointer-events: none;
    }

    .manage-category-wrap.is-open::after {
        transform: translateY(-20%) rotate(225deg);
    }

    .manage-category-menu {
        position: absolute;
        top: calc(100% + 10px);
        left: 0;
        right: 0;
        display: none;
        gap: 10px;
        padding: 14px;
        border-radius: 18px;
        border: 1px solid rgba(139, 0, 0, 0.12);
        background: rgba(255, 255, 255, 0.98);
        box-shadow: 0 18px 34px rgba(15, 23, 42, 0.14);
        z-index: 80;
        backdrop-filter: blur(8px);
        -webkit-backdrop-filter: blur(8px);
        max-height: 280px;
        overflow-y: auto;
    }

    .manage-category-wrap.is-open .manage-category-menu {
        display: grid;
    }

    .manage-category-option {
        width: 100%;
        border: 1px solid rgba(148, 163, 184, 0.22);
        background: linear-gradient(180deg, #ffffff 0%, #f8fafc 100%);
        color: #1e293b;
        border-radius: 999px;
        padding: 12px 14px;
        font-size: 13px;
        font-weight: 800;
        text-align: left;
        cursor: pointer;
        transition: all 0.18s ease;
        box-shadow:
            0 12px 22px rgba(15, 23, 42, 0.08),
            inset 0 1px 0 rgba(255,255,255,0.82);
    }

    .manage-category-option:hover,
    .manage-category-option.is-selected {
        transform: translateY(-1px);
        border-color: #8B0000;
        background: linear-gradient(135deg, #8B0000, #70131B);
        color: #facc15;
        box-shadow: 0 12px 20px rgba(139, 0, 0, 0.16);
    }

    .manage-form {
        display: grid;
        grid-template-columns: minmax(280px, 1.35fr) minmax(220px, 1.65fr) auto;
        gap: 10px;
        margin-bottom: 20px;
        align-items: start;
    }

    .table-action-cell { text-align: center; white-space: nowrap; }
    .table-action-wrap {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        gap: 6px;
    }

    /* BUTTONS */
    .btn-change,
    .btn-remove,
    .btn-cancel {
        border-radius: 999px;
        padding: 8px 14px;
        font-size: 12px;
        font-weight: 800;
        cursor: pointer;
        transition: all 0.2s ease;
    }

    .btn-change {
        background-color: #cbd5e1;
        color: #334155;
        border: 1px solid #5bf846;
    }

    .btn-remove {
        background: #70131B;
        color: #fff;
        border: none;
    }

    .btn-cancel {
        background: #e5e7eb;
        border: 1px solid #cbd5e1;
    }

    .btn-change:hover,
    .btn-remove:hover,
    .btn-cancel:hover,
    .btn-save:hover {
        background: #facc15;
        background-image: none;
        color: #111111;
        border-color: #facc15;
        transform: translateY(-2px);
        box-shadow:
            0 0 0 3px rgba(250, 204, 21, 0.12),
            0 18px 30px rgba(139, 0, 0, 0.22);
    }

    .btn-save:hover::after {
        transform: translateX(135%);
    }

    .manage-form .btn-save:hover {
        background: #facc15 !important;
        background-image: none !important;
        color: #111111 !important;
        border-color: #facc15 !important;
    }

    /* MODAL */
    .modal-overlay { display: none; position: fixed; inset: 0; background: rgba(0,0,0,0.5); justify-content: center; align-items: center; z-index: 1000; }
    .modal-box { background: #fff; padding: 24px; border-radius: 12px; width: 100%; max-width: 500px; max-height: 90vh; overflow-y: auto; }

    /* MOBILE RESPONSIVE */
    @media (max-width: 768px) {
        .modal-box {
            width: 95vw !important;
            max-width: 95vw !important;
            padding: 16px !important;
            margin: 10px !important;
        }

        /* Category/Condition Controls - Mobile Stack */
        .category-controls,
        .condition-controls,
        .manage-controls {
            display: flex !important;
            flex-wrap: wrap !important;
            gap: 8px !important;
            align-items: center !important;
            margin: 10px 0 !important;
        }

        .category-controls button,
        .category-controls select,
        .category-controls input,
        .condition-controls button,
        .condition-controls select,
        .manage-controls button,
        .manage-controls select {
            flex: 1 1 auto !important;
            min-width: 100px !important;
            padding: 8px 12px !important;
            font-size: 12px !important;
            white-space: normal !important;
        }

        /* Add Button */
        .add-button,
        .btn-add-category,
        .btn-add-condition {
            flex: 1 1 100% !important;
            min-height: 40px !important;
        }

        /* Tables - Horizontal Scroll */
        .table-container,
        .table-wrapper {
            overflow-x: auto !important;
            -webkit-overflow-scrolling: touch;
            margin: 0 -8px !important;
            padding: 0 8px !important;
        }

        table {
            min-width: 100% !important;
        }
    }

    /* DARK MODE FIXES */
    html[data-theme="dark"] .modal-box {
        background: rgba(35, 17, 25, 0.96) !important;
        border: 1px solid rgba(255, 255, 255, 0.08) !important;
    }

    html[data-theme="dark"] .modal-box h3,
    html[data-theme="dark"] .modal-box label {
        color: #f8fafc !important;
    }

    /* MAR Date Field - Dark Mode (Primary Fix) */
    html[data-theme="dark"] input[type="date"],
    html[data-theme="dark"] input[type="datetime-local"],
    html[data-theme="dark"] .date-field {
        background: rgba(18, 18, 18, 0.7) !important;
        color: #000000 !important;
        border: 2px solid rgba(255, 255, 255, 0.16) !important;
        padding: 10px 12px !important;
        font-size: 14px !important;
    }

    html[data-theme="dark"] input[type="date"]:focus,
    html[data-theme="dark"] input[type="datetime-local"]:focus {
        border-color: #facc15 !important;
        box-shadow: 0 0 0 3px rgba(250, 204, 21, 0.1) !important;
        outline: none !important;
    }

    html[data-theme="dark"] input[type="date"]::-webkit-calendar-picker-indicator {
        filter: invert(1) brightness(1.3) !important;
        cursor: pointer !important;
        opacity: 0.8 !important;
    }

    html[data-theme="dark"] input[type="date"]::-webkit-calendar-picker-indicator:hover {
        opacity: 1 !important;
    }

    /* Form Fields - Dark Mode */
    html[data-theme="dark"] input[type="text"],
    html[data-theme="dark"] input[type="email"],
    html[data-theme="dark"] input[type="number"],
    html[data-theme="dark"] input[type="search"],
    html[data-theme="dark"] select,
    html[data-theme="dark"] textarea {
        background: rgba(18, 18, 18, 0.55) !important;
        color: #f8fafc !important;
        border-color: rgba(255, 255, 255, 0.12) !important;
    }

    html[data-theme="dark"] input::placeholder,
    html[data-theme="dark"] textarea::placeholder {
        color: rgba(248, 250, 252, 0.5) !important;
    }

    /* Buttons - Dark Mode */
    html[data-theme="dark"] .btn-add,
    html[data-theme="dark"] button[type="button"],
    html[data-theme="dark"] button[type="submit"] {
        background: rgba(112, 19, 27, 0.96) !important;
        color: #f8fafc !important;
        border-color: rgba(255, 255, 255, 0.12) !important;
    }

    html[data-theme="dark"] .btn-add:hover {
        background: #facc15 !important;
        color: #111827 !important;
    }

    /* Tables - Dark Mode */
    html[data-theme="dark"] table {
        background: rgba(18, 18, 18, 0.4) !important;
        color: #f8fafc !important;
    }

    html[data-theme="dark"] table th {
        background: rgba(18, 18, 18, 0.55) !important;
        color: #f8fafc !important;
        border-color: rgba(255, 255, 255, 0.08) !important;
    }

    html[data-theme="dark"] table td {
        border-color: rgba(255, 255, 255, 0.08) !important;
        color: #f8fafc !important;
    }

    html[data-theme="dark"] table tbody tr:hover {
        background: rgba(59, 24, 33, 0.5) !important;
    }

    /* MAR Categories Dark Mode - Fixed Background & No Hover */
    html[data-theme="dark"] .manage-category-option {
        background: rgba(35, 17, 25, 0.96) !important;
        color: #f8fafc !important;
        border-color: rgba(255, 255, 255, 0.12) !important;
        box-shadow: none !important;
    }

    html[data-theme="dark"] .manage-category-option:hover {
        transform: none !important;
        background: rgba(35, 17, 25, 0.96) !important;
        color: #f8fafc !important;
        border-color: rgba(255, 255, 255, 0.12) !important;
        box-shadow: none !important;
    }

    html[data-theme="dark"] .manage-category-option.is-selected {
        transform: none !important;
        background: rgba(35, 17, 25, 0.96) !important;
        color: #f8fafc !important;
        border-color: rgba(255, 255, 255, 0.12) !important;
        box-shadow: none !important;
    }

</style>

@endpush

@section('content')

<div class="card manage-section">
    <h3>Manage Medical Conditions (Sub-categories)</h3>


<form action="{{ route('conditions.store') }}" method="POST" class="manage-form">
    @csrf

    <div class="manage-category-wrap" id="manageCategoryWrap">
        <select name="category_id" id="manageCategorySelect" class="form-control manage-category-select" required>
            <option value="">Select Category</option>
            @foreach($categoryList as $c)
                <option value="{{ $c->id }}">
                    Category {{ $c->code }} - {{ $c->name }}
                </option>
            @endforeach
        </select>
        <button type="button" class="manage-category-display" id="manageCategoryDisplay" aria-haspopup="listbox" aria-expanded="false">
            Select Category
        </button>
        <div class="manage-category-menu" id="manageCategoryMenu" role="listbox" aria-label="Medical category options">
            @foreach($categoryList as $c)
                <button type="button" class="manage-category-option" data-category-value="{{ $c->id }}">
                    Category {{ $c->code }} - {{ $c->name }}
                </button>
            @endforeach
        </div>
    </div>

    <input type="text" name="name" class="form-control manage-condition-input" placeholder="Condition Name" required>

    <button type="submit" class="btn-save">
        <svg class="btn-save-icon" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" d="M12 5v14m7-7H5" />
        </svg>
        <span>Add New</span>
    </button>
</form>

<table class="mar-table">
    <thead>
        <tr>
            <th>Category</th>
            <th>Condition Name</th>
            <th style="text-align: center;">Action</th>
        </tr>
    </thead>

    <tbody>
    @forelse($allConditions as $cond)
    <tr>
        <td>
            <strong>Category {{ $cond->category->code }}</strong> -
            {{ $cond->category->name }}
        </td>

        <td>{{ $cond->name }}</td>

        <td class="table-action-cell">
            <div class="table-action-wrap">

                <!-- CHANGE -->
                <button type="button" class="btn-change"
                    onclick="openChangeModal('{{ $cond->id }}','{{ $cond->category_id }}','{{ $cond->name }}')">
                    ✏️ Change
                </button>

                <!-- REMOVE -->
                <form action="{{ route('conditions.destroy', $cond->id) }}"
                      method="POST"
                      onsubmit="return confirmDelete(event)">
                    @csrf
                    @method('DELETE')

                    <button type="submit" class="btn-remove">
                        🗑 Remove
                    </button>
                </form>

            </div>
        </td>
    </tr>
    @empty
    <tr>
        <td colspan="3" style="text-align:center;">No data found</td>
    </tr>
    @endforelse
    </tbody>
</table>


</div>

<!-- MODAL -->

<div id="changeModal" class="modal-overlay">
    <div class="modal-box">


    <h3>Change Category</h3>
    <p id="conditionDisplayName"></p>

    <form id="changeForm" method="POST">
        @csrf
        @method('PUT')

        <select name="category_id" id="modalCategoryId" class="form-control" required>
            @foreach($categoryList as $c)
                <option value="{{ $c->id }}">
                    Category {{ $c->code }} - {{ $c->name }}
                </option>
            @endforeach
        </select>

        <div style="margin-top:20px; text-align:right;">
            <button type="button" class="btn-cancel" onclick="closeChangeModal()">Cancel</button>
            <button type="submit" class="btn-save">Save</button>
        </div>
    </form>

</div>


</div>

@endsection

@push('scripts')

<script>
const manageCategorySelect = document.getElementById('manageCategorySelect');
const manageCategoryWrap = document.getElementById('manageCategoryWrap');
const manageCategoryDisplay = document.getElementById('manageCategoryDisplay');
const manageCategoryOptions = Array.from(document.querySelectorAll('.manage-category-option'));

function syncManageCategoryDisplay() {
    if (!manageCategorySelect || !manageCategoryDisplay) return;

    const selectedOption = manageCategorySelect.options[manageCategorySelect.selectedIndex];
    manageCategoryDisplay.textContent = selectedOption && selectedOption.value ? selectedOption.text.trim() : 'Select Category';

    manageCategoryOptions.forEach(function(option) {
        option.classList.toggle('is-selected', option.dataset.categoryValue === manageCategorySelect.value);
    });
}

function setManageCategoryOpenState(isOpen) {
    if (!manageCategoryWrap || !manageCategoryDisplay) return;

    manageCategoryWrap.classList.toggle('is-open', isOpen);
    manageCategoryDisplay.classList.toggle('is-open', isOpen);
    manageCategoryDisplay.setAttribute('aria-expanded', isOpen ? 'true' : 'false');
}

if (manageCategoryDisplay && manageCategorySelect) {
    manageCategoryDisplay.addEventListener('click', function(event) {
        event.preventDefault();
        setManageCategoryOpenState(!manageCategoryWrap.classList.contains('is-open'));
    });

    manageCategoryOptions.forEach(function(option) {
        option.addEventListener('click', function(event) {
            event.preventDefault();
            manageCategorySelect.value = option.dataset.categoryValue || '';
            manageCategorySelect.dispatchEvent(new Event('change', { bubbles: true }));
            syncManageCategoryDisplay();
            setManageCategoryOpenState(false);
        });
    });

    manageCategorySelect.addEventListener('change', syncManageCategoryDisplay);
    syncManageCategoryDisplay();
}

function openChangeModal(id, categoryId, name) {
    document.getElementById('conditionDisplayName').innerText = "Condition: " + name;
    document.getElementById('modalCategoryId').value = categoryId;

    let route = @json(route('conditions.update', ['id' => '__ID__']));
    document.getElementById('changeForm').action = route.replace('__ID__', id);

    document.getElementById('changeModal').style.display = 'flex';
}

function closeChangeModal() {
    document.getElementById('changeModal').style.display = 'none';
}

function confirmDelete(e) {
    if (!confirm("Are you sure you want to delete this condition?")) {
        e.preventDefault();
        return false;
    }
    return true;
}

window.onclick = function(e) {
    let modal = document.getElementById('changeModal');
    if (e.target === modal) closeChangeModal();
    if (manageCategoryWrap && !manageCategoryWrap.contains(e.target)) {
        setManageCategoryOpenState(false);
    }
};

document.addEventListener('keydown', function(event) {
    if (event.key === 'Escape') {
        setManageCategoryOpenState(false);
    }
});
</script>

@endpush
