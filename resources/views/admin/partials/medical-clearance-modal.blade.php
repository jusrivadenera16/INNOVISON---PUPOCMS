@once('medical-clearance-styles')
    @push('styles')
        <link rel="stylesheet" href="{{ asset('css/medical-clearance.css') }}?v={{ filemtime(public_path('css/medical-clearance.css')) }}">
    @endpush
@endonce
<dialog class="medical-clearance-modal" id="medicalClearanceModal" aria-labelledby="medicalClearanceTitle" data-clearance-base="{{ url('/admin/medical-clearances') }}">
    <header class="medical-clearance-header">
        <span class="medical-clearance-icon" aria-hidden="true"><x-outline-icon name="document-text" /></span>
        <div><h2 id="medicalClearanceTitle">Issue Medical Clearance</h2><p>Review the patient details and select the purpose before issuing.</p></div>
        <button type="button" data-clearance-close aria-label="Close Medical Clearance">&times;</button>
    </header>
    <form data-clearance-form>
        <label>Patient <input data-clearance-patient readonly aria-readonly="true"></label>
        <p class="medical-clearance-hint">Name, age, and F/M are taken from the saved record. Age is calculated as of the selected date when a birthday is available.</p>
        <label>Date <input type="date" name="issued_on" required></label>
        <label>Request for / Purpose <select name="purpose" required><option value="">Select a purpose</option></select></label>
        <label data-clearance-other hidden>Other purpose <input name="other_purpose" maxlength="180" placeholder="Enter the purpose" disabled></label>
        <p class="medical-clearance-notice">Issue only after the clinic has confirmed the patient is physically fit for the selected purpose. Physician and signature lines remain blank for authorized signing.</p>
        <p data-clearance-status class="medical-clearance-status" role="status" aria-live="polite" hidden></p>
        <footer class="medical-clearance-actions">
            <button type="button" data-clearance-close>Cancel</button>
            <button type="submit" class="medical-clearance-primary">Generate &amp; Save</button>
        </footer>
    </form>
</dialog>
@push('scripts')
    <script src="{{ asset('js/medical-clearance.js') }}" defer></script>
@endpush
