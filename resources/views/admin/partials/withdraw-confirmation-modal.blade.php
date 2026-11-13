@include('admin.partials.health-record-confirmation-styles')
<dialog class="record-confirmation-modal" id="withdrawHealthRecordDialog" aria-labelledby="withdrawHealthRecordTitle">
    <div class="record-confirmation-card">
        <span class="record-confirmation-icon" aria-hidden="true"><x-outline-icon name="exclamation-triangle" /></span>
    <h3 id="withdrawHealthRecordTitle">Mark as Withdrawn</h3>
    <p>Mark this health record as withdrawn? The saved health information, documents, and user account will be kept.</p>
    <div class="record-confirmation-actions">
        <button type="button" data-withdraw-cancel>Cancel</button>
        <button type="button" data-withdraw-confirm>Mark as Withdrawn</button>
    </div>
    </div>
</dialog>
