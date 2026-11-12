<div class="clinic-success-overlay" id="medicalClearancePrintPrompt" role="dialog" aria-modal="true" aria-labelledby="medicalClearancePrintTitle" aria-describedby="medicalClearancePrintMessage" aria-hidden="true">
    <div class="clinic-success-card clinic-confirm-card">
        <div class="clinic-confirm-icon" aria-hidden="true"><x-outline-icon name="document-text" /></div>
        <strong id="medicalClearancePrintTitle">Medical Clearance Ready</strong>
        <p id="medicalClearancePrintMessage">The clearance is saved in Documents. Would you like to view or print it now?</p>
        <div class="clinic-confirm-actions">
            <button type="button" class="clinic-confirm-cancel" data-clearance-print-later><span>Not Now</span></button>
            <button type="button" class="clinic-confirm-approve" data-clearance-print-view><span>View / Print</span></button>
        </div>
    </div>
</div>
@push('scripts')
    <script src="{{ asset('js/medical-clearance-print-prompt.js') }}" defer></script>
@endpush
