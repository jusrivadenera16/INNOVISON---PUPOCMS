@foreach($issuedClearances as $issuedClearance)
    @php($issuedClearanceUrl = route('admin.medical_clearances.show', [$issuedClearance->record_type, $issuedClearance->profile_id, $issuedClearance]))
    <div class="doc-file">
        <h4>Medical Clearance (Issued)</h4>
        <p>{{ $issuedClearance->purpose }} · Issued {{ $issuedClearance->issued_on->format('M d, Y') }}</p>
        <div class="doc-actions">
            <a class="doc-link" href="{{ $issuedClearanceUrl }}" target="_blank" rel="noopener"><x-outline-icon name="eye" /> View / Print</a>
        </div>
        <div class="doc-preview"><iframe src="{{ $issuedClearanceUrl }}" title="Issued Medical Clearance" loading="lazy"></iframe></div>
    </div>
@endforeach
