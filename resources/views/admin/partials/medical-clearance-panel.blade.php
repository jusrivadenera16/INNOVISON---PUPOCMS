<section class="medical-clearance-panel" data-clearance-panel
    @if($clearanceDocumentsOnly ?? false) data-clearance-documents-only="true" @endif
    @isset($clearanceRecordUrl) data-clearance-url="{{ $clearanceRecordUrl }}" @endisset
    @empty($clearanceRecordUrl) hidden @endempty>
    <button type="button" class="medical-clearance-primary" data-clearance-open hidden>Issue Medical Clearance</button>
    <p class="medical-clearance-status" data-clearance-panel-status role="status" hidden></p>
    <div data-clearance-documents hidden>
        <h3>Issued Documents</h3>
        <p class="medical-clearance-hint">Saved Medical Clearances for this record. View the original PDF to reprint.</p>
        <ul data-clearance-list></ul>
    </div>
</section>
