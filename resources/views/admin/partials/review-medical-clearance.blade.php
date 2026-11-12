@if(optional(auth()->user())->canAccessPermission('health_records.update_assessment'))
<section class="review-medical-clearance" data-review-clearance data-options-base="{{ url('/admin/medical-clearances') }}" hidden>
    <div data-review-clearance-fields>
        <p class="medical-clearance-status" data-review-clearance-status role="status" hidden></p>
        <div class="review-clearance-grid">
            <div class="review-clearance-full review-purpose-select">
                <label id="reviewClearancePurposeLabel" for="reviewClearancePurpose">Request for / Purpose <span aria-hidden="true">*</span></label>
                <select data-review-clearance-purpose required id="reviewClearancePurpose"><option value="">Select a purpose</option></select>
                <button type="button" class="review-purpose-trigger" data-review-clearance-purpose-trigger aria-labelledby="reviewClearancePurposeLabel reviewClearancePurposeValue" aria-haspopup="listbox" aria-expanded="false" aria-controls="reviewClearancePurposeMenu" hidden><span id="reviewClearancePurposeValue">Select a purpose</span><svg aria-hidden="true" viewBox="0 0 20 20" fill="none"><path d="m5 7.5 5 5 5-5" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"/></svg></button>
                <div id="reviewClearancePurposeMenu" class="review-purpose-menu" data-review-clearance-purpose-menu role="listbox" aria-labelledby="reviewClearancePurposeLabel" hidden></div>
            </div>
            <label class="review-clearance-full" data-review-clearance-other-label hidden>Other purpose <input type="text" data-review-clearance-other maxlength="180" placeholder="Enter the purpose"></label>
        </div>
    </div>
    <a data-review-clearance-document target="_blank" rel="noopener" hidden>View / Reprint Medical Clearance</a>
</section>
@endif
@include('admin.partials.category-bubble-select-assets')
@push('scripts')
    <script src="{{ asset('js/review-medical-clearance.js') }}" defer></script>
@endpush
