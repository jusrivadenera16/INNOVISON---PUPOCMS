@if(optional(auth()->user())->canAccessPermission('health_records.update_assessment'))
<section class="review-medical-clearance" data-review-clearance data-options-base="{{ url('/admin/medical-clearances') }}" hidden>
    <label class="review-clearance-toggle"><input type="checkbox" data-review-clearance-enable> <span>Issue Medical Clearance with this approval <small>Optional - for students and employees only.</small></span></label>
    <div data-review-clearance-fields hidden>
        <p class="medical-clearance-hint">Review the patient details and purpose here. The PDF will be generated and saved to Documents only when you approve. Physician/signature lines remain blank for authorized signing.</p>
        <p class="medical-clearance-status" data-review-clearance-status role="status" hidden></p>
        <div class="review-clearance-grid">
            <label class="review-clearance-full">Patient <input data-review-clearance-patient readonly></label>
            <label>Date <input type="date" data-review-clearance-date></label>
            <label>Request for / Purpose <select data-review-clearance-purpose><option value="">Select a purpose</option></select></label>
            <label class="review-clearance-full" data-review-clearance-other-label hidden>Other purpose <input type="text" data-review-clearance-other maxlength="180" placeholder="Enter the purpose"></label>
        </div>
    </div>
</section>
@endif
@push('scripts')
    <script src="{{ asset('js/review-medical-clearance.js') }}" defer></script>
@endpush
