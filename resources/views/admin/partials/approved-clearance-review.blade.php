@if(optional(auth()->user())->canAccessPermission('health_records.update_assessment'))
<section class="approved-clearance-review" data-approved-clearance data-base="{{ url('/admin/medical-clearances') }}" hidden>
    <p data-approved-clearance-prompt>Issue another Medical Clearance? <button type="button" class="approved-clearance-link" data-approved-clearance-open aria-expanded="false" aria-controls="approvedClearanceCategoryBubble">Click here</button></p>
    <div id="approvedClearanceCategoryBubble" class="temp-category-bubble" popover="manual" role="dialog" aria-label="Select Health Form Category" data-approved-clearance-bubble hidden>
        <header><button type="button" data-approved-clearance-close aria-label="Close">&times;</button></header>
        <label id="approvedClearanceCategoryLabel" for="approvedClearanceCategory">Select a category</label>
        <select id="approvedClearanceCategory" data-approved-clearance-category></select>
        <button type="button" class="category-bubble-trigger" data-approved-clearance-category-trigger aria-labelledby="approvedClearanceCategoryLabel approvedClearanceCategoryValue" aria-haspopup="listbox" aria-controls="approvedClearanceCategoryOptions" aria-expanded="false" hidden><span id="approvedClearanceCategoryValue">Select a category</span></button>
        <div id="approvedClearanceCategoryOptions" class="category-bubble-options" data-approved-clearance-category-menu role="listbox" aria-labelledby="approvedClearanceCategoryLabel" hidden></div>
        <p data-approved-clearance-bubble-status role="status" hidden></p>
        <button type="submit" data-approved-clearance-continue disabled>Continue</button>
    </div>
</section>
@endif
@push('styles')
    <link rel="stylesheet" href="{{ asset('css/temporary-student-form-category.css') }}">
@endpush
@include('admin.partials.category-bubble-select-assets')
@push('scripts')
    <script src="{{ asset('js/approved-clearance-review.js') }}" defer></script>
@endpush
