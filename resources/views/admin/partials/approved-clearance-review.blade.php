@if(optional(auth()->user())->canAccessPermission('health_records.update_assessment'))
<section class="approved-clearance-review" data-approved-clearance data-base="{{ url('/admin/medical-clearances') }}" data-type-store="{{ route('mar-clearance-types.store') }}" data-category-store="{{ route('health-form-categories.store') }}" hidden>
    <p data-approved-clearance-prompt>Issue another Medical Certificate? <button type="button" class="approved-clearance-link" data-approved-clearance-open aria-expanded="false" aria-controls="approvedClearanceCategoryBubble">Click here</button></p>
    <div id="approvedClearanceCategoryBubble" class="temp-category-bubble" popover="manual" role="dialog" aria-label="Select Clearance Type" data-approved-clearance-bubble hidden>
        <header><button type="button" data-approved-clearance-close aria-label="Close">&times;</button></header>
        <label id="approvedClearanceTypeLabel" for="approvedClearanceType">Select Clearance Type</label>
        <select id="approvedClearanceType" data-approved-clearance-type></select>
        <button type="button" class="category-bubble-trigger" data-approved-clearance-type-trigger aria-labelledby="approvedClearanceTypeLabel approvedClearanceTypeValue" aria-haspopup="listbox" aria-controls="approvedClearanceTypeOptions" aria-expanded="false" hidden><span id="approvedClearanceTypeValue">Select Clearance Type</span></button>
        <div id="approvedClearanceTypeOptions" class="category-bubble-options" data-approved-clearance-type-menu role="listbox" aria-labelledby="approvedClearanceTypeLabel" hidden></div>
        @if(optional(auth()->user())->canAccessPermission('settings.medical'))
            <div class="clearance-quick-actions">
                <button type="button" data-approved-clearance-add-type>+ Add Clearance Type</button>
            </div>
            <div class="clearance-quick-editor" data-approved-clearance-editor hidden>
                <label for="clearanceQuickName">New Clearance Type</label>
                <input id="clearanceQuickName" data-approved-clearance-editor-name maxlength="120" autocomplete="off">
                <label id="clearanceQuickCategoryLabel" for="clearanceQuickCategory">Health Form Category</label>
                <select id="clearanceQuickCategory" data-approved-clearance-editor-category></select>
                <button type="button" class="category-bubble-trigger" data-approved-clearance-editor-category-trigger aria-labelledby="clearanceQuickCategoryLabel clearanceQuickCategoryValue" aria-haspopup="listbox" aria-controls="clearanceQuickCategoryOptions" aria-expanded="false" hidden><span id="clearanceQuickCategoryValue">Select Health Form Category</span></button>
                <div id="clearanceQuickCategoryOptions" class="category-bubble-options" data-approved-clearance-editor-category-menu role="listbox" aria-labelledby="clearanceQuickCategoryLabel" hidden></div>
                <div class="clearance-quick-actions">
                    <button type="button" data-approved-clearance-add-category>+ Add Health Form Category</button>
                </div>
                <div class="clearance-category-editor" data-approved-clearance-category-editor hidden>
                    <label for="clearanceQuickCategoryName">New Health Form Category</label>
                    <input id="clearanceQuickCategoryName" data-approved-clearance-category-name maxlength="120" autocomplete="off">
                    <div class="clearance-quick-actions">
                        <button type="button" data-approved-clearance-category-save>Save Category</button>
                        <button type="button" data-approved-clearance-category-cancel>Cancel</button>
                    </div>
                </div>
                <div class="clearance-quick-actions">
                    <button type="button" data-approved-clearance-editor-save>Save</button>
                    <button type="button" data-approved-clearance-editor-cancel>Cancel</button>
                </div>
            </div>
        @endif
        <p data-approved-clearance-bubble-status role="status" hidden></p>
        <button type="submit" data-approved-clearance-continue disabled>Continue</button>
    </div>
</section>
@endif
@push('styles')
    <link rel="stylesheet" href="{{ asset('css/temporary-student-form-category.css') }}?v={{ filemtime(public_path('css/temporary-student-form-category.css')) }}">
@endpush
@include('admin.partials.category-bubble-select-assets')
@push('scripts')
    <script src="{{ asset('js/approved-clearance-review.js') }}?v={{ filemtime(public_path('js/approved-clearance-review.js')) }}" defer></script>
@endpush
