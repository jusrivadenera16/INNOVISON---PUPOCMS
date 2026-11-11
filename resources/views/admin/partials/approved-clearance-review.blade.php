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
    <form class="approved-clearance-doctor" data-approved-clearance-form hidden>
        <h3>Doctor Review</h3>
        <p class="medical-clearance-hint">Review for a new Medical Clearance only. The approved Health Form stays unchanged.</p>
        <div class="review-clearance-grid">
            <label class="review-clearance-full">Patient <input data-approved-clearance-patient readonly></label>
            <label>Health Form Category <input data-approved-clearance-selected readonly></label>
            <label>Date <input type="date" data-approved-clearance-date required></label>
            <label>Review Result <select data-approved-clearance-findings required><option value="No Findings / Normal">No Findings / Normal</option><option value="With Findings">With Findings</option></select></label>
            <label>Clearance Decision <select data-approved-clearance-decision required><option value="">Select a decision</option><option value="issue">Cleared / Issue</option><option value="pending">Not yet cleared</option></select></label>
            <label class="review-clearance-full">Request for / Purpose <select data-approved-clearance-purpose required></select></label>
            <label class="review-clearance-full" data-approved-clearance-other-label hidden>Other purpose <input data-approved-clearance-other maxlength="180" disabled></label>
            <label class="review-clearance-full">Remarks (Optional)<textarea data-approved-clearance-remarks maxlength="4000" rows="3"></textarea></label>
        </div>
        <p data-approved-clearance-status role="status" hidden></p>
        <div class="approved-clearance-actions"><button type="button" data-approved-clearance-back>Back</button><button type="submit" class="medical-clearance-primary" data-approved-clearance-issue>Issue Medical Clearance</button></div>
        <a data-approved-clearance-document target="_blank" rel="noopener" hidden>View / Reprint Medical Clearance</a>
    </form>
</section>
@endif
@push('styles')
    <link rel="stylesheet" href="{{ asset('css/temporary-student-form-category.css') }}">
@endpush
@include('admin.partials.category-bubble-select-assets')
@push('scripts')
    <script src="{{ asset('js/approved-clearance-review.js') }}" defer></script>
@endpush
