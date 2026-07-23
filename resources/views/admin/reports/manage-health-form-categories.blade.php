@extends('layouts.admin')

@section('title', 'Manage Health Form Categories')

@push('styles')
<style>
    .card { background: #fff; border-radius: 12px; padding: 24px; box-shadow: 0 4px 12px rgba(0,0,0,0.05); border: 1px solid #f0f0f0; margin-bottom: 24px; }
    h3 { color: #111827; font-size: 18px; margin: 10px 0 18px; font-weight: 900; }
    .manage-section { background: #fdfdfd; border: 1px dashed #cbd5e1; padding: 20px; border-radius: 10px; }
    .manage-form { display: grid; grid-template-columns: minmax(320px, 1fr) auto; gap: 10px; margin-bottom: 20px; align-items: start; }
    .form-control { padding: 10px 12px; border: 1px solid #cbd5e1; border-radius: 8px; width: 100%; font-weight: 700; }
    .btn-save { background: linear-gradient(135deg, #70131B, #8f2230); color: #ffffff; border: 1px solid #8f2230; padding: 10px 18px; border-radius: 999px; cursor: pointer; font-weight: 900; }
    .mar-table { width: 100%; border-collapse: collapse; margin-top: 15px; }
    .mar-table th { background: #f8eeee; color: #70131B; text-align: left; padding: 12px; border-bottom: 2px solid #ead0d0; font-size: 13px; font-weight: 900; letter-spacing: .04em; }
    .mar-table td { padding: 12px; border-bottom: 1px solid #f1f5f9; font-size: 14px; color: #111827; }
    .table-action-cell { text-align: center; white-space: nowrap; }
    .table-action-wrap { display: inline-flex; align-items: center; justify-content: center; gap: 6px; }
    .btn-view, .btn-remove, .btn-cancel { border-radius: 999px; padding: 8px 14px; font-size: 12px; font-weight: 800; cursor: pointer; transition: all 0.2s ease; }
    .btn-view { background: #fff7ed; color: #70131B; border: 1px solid #fecaca; }
    .btn-remove { background: #70131B; color: #fff; border: none; }
    .btn-cancel { background: #e5e7eb; border: 1px solid #cbd5e1; }
    .btn-view:hover, .btn-remove:hover, .btn-cancel:hover, .btn-save:hover { transform: translateY(-1px); }
    .status-pill { display:inline-flex; align-items:center; border-radius:999px; padding:4px 10px; font-size:11px; font-weight:900; }
    .status-pill.active { background:#dcfce7; color:#166534; }
    .status-pill.archived { background:#fee2e2; color:#991b1b; }
    .modal-overlay { display: none; position: fixed; inset: 0; background: rgba(0,0,0,0.5); justify-content: center; align-items: center; z-index: 1000; }
    .modal-box { background: #fff; padding: 24px; border-radius: 12px; width: 100%; max-width: 500px; }
    .linked-list { margin: 16px 0 0; padding-left: 18px; color: #334155; }
    @media (max-width: 720px) { .manage-form { grid-template-columns: 1fr; } }
</style>
@endpush

@section('content')
<div class="card manage-section">
    <h3>Manage Health Form Categories</h3>

    <form action="{{ route('health-form-categories.store') }}" method="POST" class="manage-form">
        @csrf
        <input type="text" name="name" class="form-control" placeholder="Health Form Category Name (e.g. OJT, Annual, Medical Clearance)" required>
        <button type="submit" class="btn-save">Add New</button>
    </form>

    <table class="mar-table">
        <thead>
            <tr>
                <th>Health Form Category</th>
                <th>Linked Forms</th>
                <th>Status</th>
                <th style="text-align: center;">Action</th>
            </tr>
        </thead>
        <tbody>
        @forelse($categories as $category)
            <tr>
                <td><strong>{{ $category->name }}</strong></td>
                <td>{{ $category->submissions_count }}</td>
                <td><span class="status-pill {{ $category->is_active ? 'active' : 'archived' }}">{{ $category->is_active ? 'Active' : 'Archived' }}</span></td>
                <td class="table-action-cell">
                    <div class="table-action-wrap">
                        <button type="button" class="btn-view" onclick='openViewModal(@json([
                            "name" => $category->name,
                            "count" => $category->submissions_count,
                            "status" => $category->is_active ? "Active" : "Archived",
                        ]))'>View</button>
                        <form action="{{ route('health-form-categories.destroy', $category->id) }}" method="POST" onsubmit="return confirmDelete(event)">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="btn-remove">{{ $category->submissions_count > 0 ? 'Archive' : 'Remove' }}</button>
                        </form>
                    </div>
                </td>
            </tr>
        @empty
            <tr>
                <td colspan="4" style="text-align:center;">No health form categories found</td>
            </tr>
        @endforelse
        </tbody>
    </table>
</div>

<div id="viewModal" class="modal-overlay">
    <div class="modal-box">
        <h3>Category Details</h3>
        <ul id="categoryDetails" class="linked-list"></ul>
        <div style="margin-top:20px; text-align:right;">
            <button type="button" class="btn-cancel" onclick="closeViewModal()">Close</button>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
function openViewModal(category) {
    const details = document.getElementById('categoryDetails');
    details.innerHTML = '';
    ['Name: ' + category.name, 'Linked Forms: ' + category.count, 'Status: ' + category.status].forEach(function (line) {
        const li = document.createElement('li');
        li.textContent = line;
        details.appendChild(li);
    });
    document.getElementById('viewModal').style.display = 'flex';
}
function closeViewModal() {
    document.getElementById('viewModal').style.display = 'none';
}
function confirmDelete(e) {
    if (!confirm('Are you sure you want to remove or archive this Health Form category?')) {
        e.preventDefault();
        return false;
    }
    return true;
}
window.onclick = function(e) {
    const modal = document.getElementById('viewModal');
    if (e.target === modal) closeViewModal();
};
</script>
@endpush
