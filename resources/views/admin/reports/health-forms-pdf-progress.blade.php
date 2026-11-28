@extends('layouts.admin')

@section('content')
<section class="health-pdf-progress" id="healthPdfProgress" data-start-url="{{ $startUrl }}" data-filters="{{ json_encode($filters) }}" data-export-id="{{ $exportId ?? '' }}">
    <div class="health-pdf-progress-icon"><x-outline-icon name="document-text" /></div>
    <h1>Health Forms PDF Export</h1>
    <p>{{ $filters['date_from'] }} to {{ $filters['date_to'] }}</p>
    <h2 id="healthPdfPhase">Preparing your export</h2>
    <p id="healthPdfMessage" role="status" aria-live="polite">Starting background processing. Your records will be combined into one PDF.</p>
    <progress id="healthPdfMeter" max="100" value="0" aria-label="PDF export progress"></progress>
    <p class="health-pdf-progress-note">You may leave this tab open or return to this same page later. Processing continues in the background. The PDF is available for 6 hours.</p>
    <div class="health-pdf-progress-actions">
        <a href="{{ $backUrl }}">Back to Health Forms Export</a>
        <button type="button" id="healthPdfRetry" hidden>Try again</button>
        <a id="healthPdfDownload" class="health-pdf-download" target="_blank" rel="noopener" hidden>Open / Download PDF</a>
    </div>
    <noscript>JavaScript is required to start this background PDF export and display its status.</noscript>
</section>
@endsection

@push('styles')
<style nonce="{{ request()->attributes->get('csp_nonce') }}">
    .health-pdf-progress{max-width:780px;margin:32px auto;padding:32px;border:1px solid #e6d3d5;border-top:5px solid #801623;border-radius:20px;background:#fff;color:#111827;box-shadow:0 10px 30px #0000000a}
    .health-pdf-progress-icon{width:52px;height:52px;padding:12px;border-radius:14px;background:#801623;color:#facc15}
    .health-pdf-progress-icon svg{width:28px;height:28px}.health-pdf-progress h1{font-size:26px;margin:18px 0 8px}.health-pdf-progress h2{font-size:19px;margin-top:28px}.health-pdf-progress p{line-height:1.6}
    .health-pdf-progress progress{width:100%;height:18px;accent-color:#801623;margin:16px 0}.health-pdf-progress-note{font-size:13px;color:#64748b}
    .health-pdf-progress-actions{display:flex;gap:12px;flex-wrap:wrap;margin-top:24px;align-items:center}.health-pdf-progress-actions a,.health-pdf-progress-actions button{border:1px solid #cbd5e1;border-radius:10px;padding:11px 16px;color:inherit;text-decoration:none;background:transparent;cursor:pointer;font:inherit}
    .health-pdf-progress-actions .health-pdf-download,.health-pdf-progress-actions button{background:#801623;color:#fff;border-color:#801623}.health-pdf-progress [hidden]{display:none!important}
    html[data-theme="dark"] .health-pdf-progress{background:#101827;border-color:#493b36;color:#f8fafc}html[data-theme="dark"] .health-pdf-progress-note{color:#cbd5e1}
</style>
@endpush

@push('scripts')
<script nonce="{{ request()->attributes->get('csp_nonce') }}" src="{{ asset('js/health-pdf-export-progress.js') }}" defer></script>
@endpush
