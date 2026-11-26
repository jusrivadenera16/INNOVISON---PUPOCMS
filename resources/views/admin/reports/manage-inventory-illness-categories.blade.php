@extends('layouts.admin')

@section('title', 'Manage Inventory Illness Categories')

@push('styles')
<style nonce="{{ request()->attributes->get('csp_nonce') }}">
    .card { background: #fff; border-radius: 12px; padding: 24px; box-shadow: 0 4px 12px rgba(0,0,0,0.05); border: 1px solid #f0f0f0; margin-bottom: 24px; }
    h3 { color: #334155; font-size: 18px; margin-top: 10px; }
    .mar-table { width: 100%; border-collapse: collapse; margin-top: 15px; }
    .mar-table th { background: #f8fafc; color: #64748b; text-align: left; padding: 12px; border-bottom: 2px solid #eee; font-size: 13px; }
    .mar-table td { padding: 12px; border-bottom: 1px solid #f1f5f9; font-size: 14px; color: #334155; }
    .manage-section { background: #fdfdfd; border: 1px dashed #cbd5e1; padding: 20px; border-radius: 10px; }
    .btn-save { background: linear-gradient(135deg, #70131B, #8f2230); color: #ffffff; border: 1px solid #8f2230; padding: 10px 16px; border-radius: 999px; cursor: pointer; font-weight: 800; }
    .form-control { padding: 8px; border: 1px solid #ddd; border-radius: 5px; width: 100%; }
    .manage-form { display: grid; grid-template-columns: minmax(320px, 1fr) auto; gap: 10px; margin-bottom: 20px; align-items: start; }
    .table-action-cell { text-align: center; white-space: nowrap; }
    .table-action-wrap { display: inline-flex; align-items: center; justify-content: center; gap: 6px; }
    .btn-view, .btn-remove, .btn-cancel { border-radius: 999px; padding: 8px 14px; font-size: 12px; font-weight: 800; cursor: pointer; transition: all 0.2s ease; }
    .btn-view { background-color: #cbd5e1; color: #334155; border: 1px solid #5bf846; }
    .btn-remove { background: #70131B; color: #fff; border: none; }
    .btn-cancel { background: #e5e7eb; border: 1px solid #cbd5e1; }
    .btn-view:hover, .btn-remove:hover, .btn-cancel:hover { transform: translateY(-1px); }
    .modal-overlay { display: none; position: fixed; inset: 0; background: rgba(0,0,0,0.5); justify-content: center; align-items: center; }
    .modal-box { background: #fff; padding: 24px; border-radius: 12px; width: 100%; max-width: 500px; }
    .linked-medicine-list { margin: 16px 0 0; padding-left: 18px; color: #334155; }
    .linked-medicine-list li + li { margin-top: 8px; }
    .linked-medicine-empty { margin-top: 14px; color: #64748b; font-size: 14px; }

    /* Outrank page ID rules without blocking inline CSSOM updates. */
    :is(#reports-csp-priority#reports-csp-priority, .illness-categories-back) { margin-bottom: 20px; }
    :is(#reports-csp-priority#reports-csp-priority, .illness-categories-back-link) { text-decoration: none; color: #64748b; font-size: 14px; }
    :is(#reports-csp-priority#reports-csp-priority, .illness-categories-centered) { text-align: center; }
    :is(#reports-csp-priority#reports-csp-priority, .illness-categories-empty-hidden) { display: none; }
    :is(#reports-csp-priority#reports-csp-priority, .illness-categories-modal-actions) { margin-top:20px; text-align:right; }
</style>
@endpush

@section('content')
@php
    $role = \App\Models\User::normalizeRole(optional(auth()->user())->user_role ?? '');
    $backUrl = $role === \App\Models\User::ROLE_ADMIN
        ? url('/assistant/reports/inventory-summary?month=' . $month)
        : url('/admin/reports/inventory-summary?month=' . $month);
@endphp

<div class="illness-categories-back">
    <a class="illness-categories-back-link" href="{{ $backUrl }}">
        &larr; Back to Inventory Summary
    </a>
</div>

<div class="card manage-section">
    <h3>Manage Inventory Illness Categories</h3>

    <form action="{{ route('inventory-illness-categories.store') }}" method="POST" class="manage-form">
        @csrf
        <input type="text" name="name" class="form-control" placeholder="Illness Category Name (e.g. Pain Reliever, Asthma)" required>
        <button type="submit" class="btn-save">Add New</button>
    </form>

    <table class="mar-table">
        <thead>
            <tr>
                <th>Illness Category</th>
                <th>Linked Medicines</th>
                <th class="illness-categories-centered">Action</th>
            </tr>
        </thead>
        <tbody>
        @forelse($categories as $category)
            <tr>
                <td><strong>{{ $category->name }}</strong></td>
                <td>{{ $category->items_count }}</td>
                <td class="table-action-cell">
                    <div class="table-action-wrap">
                        <button type="button" class="btn-view" data-view-illness-category data-category-name="{{ $category->name }}" data-category-items="{{ json_encode($category->items->pluck('name')->values()) }}">
                            View
                        </button>
                        <form action="{{ route('inventory-illness-categories.destroy', $category->id) }}" method="POST" data-delete-illness-category>
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="btn-remove">Remove</button>
                        </form>
                    </div>
                </td>
            </tr>
        @empty
            <tr>
                <td class="illness-categories-centered" colspan="3">No illness categories found</td>
            </tr>
        @endforelse
        </tbody>
    </table>
</div>

<div id="viewModal" class="modal-overlay">
    <div class="modal-box">
        <h3>Linked Medicines</h3>
        <p id="categoryDisplayName"></p>
        <ul id="linkedMedicineList" class="linked-medicine-list"></ul>
        <p id="linkedMedicineEmpty" class="linked-medicine-empty illness-categories-empty-hidden">No medicines are linked to this illness category yet.</p>

        <div class="illness-categories-modal-actions">
            <button type="button" class="btn-cancel" data-close-illness-category>Close</button>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script nonce="{{ request()->attributes->get('csp_nonce') }}">
document.querySelectorAll('[data-view-illness-category]').forEach(function(button) {
    button.addEventListener('click', function() {
        openViewModal({ name: button.dataset.categoryName, items: JSON.parse(button.dataset.categoryItems || '[]') });
    });
});
document.querySelector('[data-close-illness-category]')?.addEventListener('click', closeViewModal);
document.querySelectorAll('[data-delete-illness-category]').forEach(function(form) {
    form.addEventListener('submit', confirmDelete);
});

function openViewModal(category) {
    const listNode = document.getElementById('linkedMedicineList');
    const emptyNode = document.getElementById('linkedMedicineEmpty');
    document.getElementById('categoryDisplayName').innerText = 'Category: ' + (category.name || '');

    listNode.innerHTML = '';

    const items = Array.isArray(category.items) ? category.items : [];
    if (items.length === 0) {
        emptyNode.style.display = 'block';
        listNode.style.display = 'none';
    } else {
        emptyNode.style.display = 'none';
        listNode.style.display = 'block';

        items.forEach(function (name) {
            const li = document.createElement('li');
            li.textContent = name;
            listNode.appendChild(li);
        });
    }

    document.getElementById('viewModal').style.display = 'flex';
}

function closeViewModal() {
    document.getElementById('viewModal').style.display = 'none';
}

function confirmDelete(e) {
    if (!confirm('Are you sure you want to delete this illness category? Linked medicines will be unset.')) {
        e.preventDefault();
        return false;
    }
    return true;
}

window.addEventListener('click', function(e) {
    let modal = document.getElementById('viewModal');
    if (e.target === modal) closeViewModal();
});
</script>
@endpush
