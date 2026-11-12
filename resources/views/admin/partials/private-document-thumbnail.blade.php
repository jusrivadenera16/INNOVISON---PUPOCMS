@once
    @push('styles')
        <link rel="stylesheet" href="{{ asset('css/private-document-thumbnail.css') }}">
    @endpush
@endonce
<div class="doc-preview private-document-thumbnail">
    <div class="private-document-thumbnail-copy">
        <span class="private-document-thumbnail-title">{{ $documentLabel }}</span>
        @if(!empty($documentSubtitle))
            <small class="private-document-thumbnail-subtitle">{{ $documentSubtitle }}</small>
        @endif
    </div>
</div>
