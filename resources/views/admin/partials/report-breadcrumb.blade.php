@php
    $breadcrumbItems = $items ?? [];
@endphp

@once
    @push('styles')
        <style>
            .report-breadcrumb {
                display: flex;
                align-items: center;
                flex-wrap: wrap;
                gap: 8px;
                margin: 0 0 10px;
                padding: 0 2px;
                font-size: 12px;
                font-weight: 900;
                line-height: 1.35;
            }

            .report-breadcrumb--settings-medical {
                margin-bottom: 20px;
            }

            .report-breadcrumb a,
            .report-breadcrumb-current {
                transition: color .18s ease;
            }

            .report-breadcrumb.report-breadcrumb a {
                color: #94a3b8 !important;
                text-decoration: none;
            }

            .report-breadcrumb.report-breadcrumb a:hover,
            .report-breadcrumb.report-breadcrumb a:focus-visible {
                color: #70131B !important;
                text-decoration: underline;
                outline: none;
            }

            .report-breadcrumb.report-breadcrumb .report-breadcrumb-current {
                color: #70131B !important;
            }

            .report-breadcrumb-separator {
                color: #94a3b8;
                user-select: none;
            }

            html[data-theme="dark"] .report-breadcrumb.report-breadcrumb a {
                color: #ffffff !important;
            }

            html[data-theme="dark"] .report-breadcrumb.report-breadcrumb a:hover,
            html[data-theme="dark"] .report-breadcrumb.report-breadcrumb a:focus-visible,
            html[data-theme="dark"] .report-breadcrumb.report-breadcrumb .report-breadcrumb-current {
                color: #facc15 !important;
            }
        </style>
    @endpush
@endonce

@if(count($breadcrumbItems) > 0)
    <nav class="report-breadcrumb {{ $class ?? '' }}" aria-label="Breadcrumb">
        @foreach($breadcrumbItems as $item)
            @if(!$loop->first)
                <span class="report-breadcrumb-separator" aria-hidden="true">&rarr;</span>
            @endif

            @if(!$loop->last && !empty($item['url']))
                <a href="{{ $item['url'] }}">{{ $item['label'] }}</a>
            @else
                <span class="report-breadcrumb-current" aria-current="page">{{ $item['label'] }}</span>
            @endif
        @endforeach
    </nav>
@endif
