<section class="medical-clearance-panel" data-clearance-panel
    @if($clearanceDocumentsOnly ?? false) data-clearance-documents-only="true" @endif
    @isset($clearanceRecordUrl) data-clearance-url="{{ $clearanceRecordUrl }}" @endisset
    @empty($clearanceRecordUrl) hidden @endempty>
    <button type="button" class="medical-clearance-primary" data-clearance-open hidden>Issue Medical Clearance</button>
    <p class="medical-clearance-status" data-clearance-panel-status role="status" hidden></p>
    <div data-clearance-documents hidden>
        <h3>Medical Certificate History</h3>
        <p class="medical-clearance-hint">Saved Medical Certificates for this record. View or reprint the original PDF.</p>
        <p class="medical-clearance-hint" data-clearance-empty hidden>No Medical Certificates have been issued yet.</p>
        <ul data-clearance-list></ul>
    </div>
</section>
