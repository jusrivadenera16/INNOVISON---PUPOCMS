@php
    $items = collect($items ?? []);
    $classes = $classes ?? [''];
    $maxValue = max(1, (int) $items->max('value'));
@endphp

<div class="appointment-chart-bars">
    @forelse($items as $index => $item)
        @php
            $value = (int) ($item['value'] ?? 0);
            $width = $value > 0 ? max(4, round(($value / $maxValue) * 100)) : 0;
            $class = $classes[$index % max(1, count($classes))] ?? '';
            $rowClass = trim((string) ($item['row_class'] ?? ''));
        @endphp
        <div class="appointment-chart-row {{ $rowClass }}">
            <div class="appointment-chart-label">{{ $item['label'] ?? 'Unlabeled' }}</div>
            <div class="appointment-chart-track" aria-hidden="true">
                <span class="appointment-chart-fill {{ $class }}" style="width: {{ $width }}%;"></span>
            </div>
            <div class="appointment-chart-value">{{ number_format($value) }}</div>
        </div>
    @empty
        <div class="appointment-chart-row">
            <div class="appointment-chart-label">No data</div>
            <div class="appointment-chart-track" aria-hidden="true"></div>
            <div class="appointment-chart-value">0</div>
        </div>
    @endforelse
</div>
