@if(optional(auth()->user())->canAccessPermission('health_records.update_assessment'))
<section class="review-medical-clearance" data-review-clearance data-options-base="{{ url('/admin/medical-clearances') }}" hidden>
    <div data-review-clearance-fields>
        <p class="medical-clearance-status" data-review-clearance-status role="status" hidden></p>
        <div class="review-clearance-grid">
            <label class="review-clearance-full">Request for / Purpose <span aria-hidden="true">*</span><select data-review-clearance-purpose required><option value="">Select a purpose</option></select></label>
            <label class="review-clearance-full" data-review-clearance-other-label hidden>Other purpose <input type="text" data-review-clearance-other maxlength="180" placeholder="Enter the purpose"></label>
        </div>
    </div>
    <a data-review-clearance-document target="_blank" rel="noopener" hidden>View / Reprint Medical Clearance</a>
</section>
@endif
@push('scripts')
    <script src="{{ asset('js/review-medical-clearance.js') }}" defer></script>
@endpush
