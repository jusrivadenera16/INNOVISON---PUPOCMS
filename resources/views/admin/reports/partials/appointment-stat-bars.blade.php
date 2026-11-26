@php
    $items = collect($items ?? []);
    $classes = $classes ?? [''];
    $maxValue = max(1, (int) $items->pluck('value')->filter(static function ($value) {
        return is_numeric($value) && is_finite((float) $value);
    })->max());
    $widthForValue = static function ($value) use ($maxValue): int {
        if (!is_numeric($value) || !is_finite((float) $value)) {
            return 0;
        }

        $value = (int) $value;

        return $value > 0 ? (int) min(100, max(4, round(($value / $maxValue) * 100))) : 0;
    };
    $barWidths = $items->map(static fn ($item) => $widthForValue($item['value'] ?? 0))->unique();
@endphp

<style nonce="{{ request()->attributes->get('csp_nonce') }}">
    @foreach($barWidths as $barWidth)
        :is(#reports-csp-priority#reports-csp-priority, .appointment-chart-width-{{ $barWidth }}) { width: {{ $barWidth }}%; }
    @endforeach
</style>

<div class="appointment-chart-bars">
    @forelse($items as $index => $item)
        @php
            $value = (int) ($item['value'] ?? 0);
            $width = $widthForValue($item['value'] ?? 0);
            $class = $classes[$index % max(1, count($classes))] ?? '';
            $rowClass = trim((string) ($item['row_class'] ?? ''));
        @endphp
        <div class="appointment-chart-row {{ $rowClass }}">
            <div class="appointment-chart-label">{{ $item['label'] ?? 'Unlabeled' }}</div>
            <div class="appointment-chart-track" aria-hidden="true">
                <span class="appointment-chart-fill {{ $class }} appointment-chart-width-{{ $width }}"></span>
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
