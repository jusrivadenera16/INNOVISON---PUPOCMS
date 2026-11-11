@php
    $queueBadgeType = $resolveHealthRecordUserType($record);
    $queueBadgeLabel = $queueBadgeType;
    $queueBadgeClass = 'is-' . strtolower($queueBadgeType);
    $queueBadgeCategory = '';
    if (strtolower($queueBadgeType) === 'student') {
        $queueBadgeCategory = ($pendingHealthFormCategories ?? collect())->get($record->id) ?: ($record->health_form_category ?? '');
        $queueBadgePurpose = trim(preg_replace('/[^a-z0-9]+/', ' ', strtolower(trim((string) $queueBadgeCategory))));
        if (preg_match('/\b(?:ojt|on the job(?: training)?)\b/', $queueBadgePurpose)) {
            $queueBadgeLabel = 'Student · OJT';
            $queueBadgeClass .= ' is-purpose-ojt';
        } elseif (preg_match('/\bladderized\b/', $queueBadgePurpose)) {
            $queueBadgeLabel = 'Student · Ladderized';
            $queueBadgeClass .= ' is-purpose-ladderized';
        }
    }
@endphp
<span class="health-user-type-badge {{ $queueBadgeClass }}" @if($queueBadgeCategory !== '') title="Submitted form purpose: {{ $queueBadgeCategory }}" @endif>{{ $queueBadgeLabel }}</span>
