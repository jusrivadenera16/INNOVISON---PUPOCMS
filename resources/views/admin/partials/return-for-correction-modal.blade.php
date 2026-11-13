@include('admin.partials.health-record-confirmation-styles')
<dialog class="record-confirmation-modal" id="returnForCorrectionDialog" aria-labelledby="returnForCorrectionTitle">
    <div class="record-confirmation-card">
        <span class="record-confirmation-icon" aria-hidden="true"><x-outline-icon name="exclamation-triangle" /></span>
    <h3 id="returnForCorrectionTitle">Return for Correction</h3>
    <p>Return this Health Form for correction? The record owner can edit and resubmit the same form. Saved documents and earlier form versions will be kept.</p>
    <form method="POST">
        @csrf
        <input type="hidden" name="submission_id" value="">
        <div class="record-confirmation-actions">
            <button type="button" data-return-correction-cancel>Cancel</button>
            <button type="submit">Return for Correction</button>
        </div>
    </form>
    </div>
</dialog>
@push('scripts')
<script src="{{ asset('js/health-record-correction.js') }}?v={{ filemtime(public_path('js/health-record-correction.js')) }}"></script>
@endpush
