@once
    @push('scripts')
        <script src="{{ asset('js/category-bubble-select.js') }}?v={{ filemtime(public_path('js/category-bubble-select.js')) }}" defer></script>
    @endpush
@endonce
