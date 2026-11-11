@once
    @push('styles')
        <style>
            .profile-version-choice-copy .health-version-date { display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 4px 8px; }
            .health-version-latest { display: inline-block; margin-left: auto; padding: 1px 6px; border: 1px solid #e8c94c; border-radius: 5px; background: #fff7d4; color: #754600; font-size: 9px; font-weight: 800; line-height: 1.4; }
            .profile-version-choice.is-active .health-version-latest { background: #facc151a; color: #fde047; border-color: #facc1580; }
            html[data-theme="dark"] .health-version-latest { background: #facc151a; color: #fde047; border-color: #facc1580; }
        </style>
    @endpush
@endonce
<strong>{{ app(\App\Services\HealthRecordCategoryFilter::class)->label($versionCategory ?? null) }}</strong>
<small class="health-version-date"><span>{{ $versionDateLabel }}</span>@if($versionLatest ?? false)<span class="health-version-latest">Latest</span>@endif</small>
