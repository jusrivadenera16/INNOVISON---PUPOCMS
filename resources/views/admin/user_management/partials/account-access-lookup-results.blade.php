<tbody id="lookupResultsBody">
    @forelse($lookupRecords as $record)
        @php
            $lookupMeta = (array) ($record['meta'] ?? []);
            $lookupSource = strtolower(trim((string) ($record['source'] ?? '')));
            $lookupUserType = strtolower(trim((string) ($lookupMeta['user_type'] ?? '')));
            $lookupRole = strtolower(trim((string) ($record['raw_role'] ?? '')));
            $isStudentLookup = in_array($lookupSource, ['student', 'student_assistant'], true)
                || str_contains($lookupUserType, 'student')
                || $lookupRole === 'student';
            $lookupIdentifier = $isStudentLookup
                ? trim((string) ($lookupMeta['student_number'] ?? ''))
                : trim((string) ($lookupMeta['employee_number'] ?? $lookupMeta['faculty_identifier'] ?? $lookupMeta['employee_id'] ?? ''));

            if (preg_match('/emergency[-_\\s]?(admin|login)/i', $lookupIdentifier)) {
                $lookupIdentifier = '';
            }
        @endphp
        <tr
            data-user-card
            data-lookup-result-row
            data-update-url="{{ $record['can_edit'] ? route('admin.user-management.update', $record['id']) : '' }}"
            data-delete-url="{{ $record['can_edit'] ? route('admin.user-management.destroy', $record['id']) : '' }}"
            data-delete-account-url="{{ $record['can_edit'] ? route('admin.user-management.delete-account', $record['id']) : '' }}"
            data-create-url="{{ !$record['can_edit'] && !empty($record['can_onboard']) ? route('admin.user-management.store-from-lookup') : '' }}"
            data-can-edit="{{ $record['can_edit'] ? '1' : '0' }}"
            data-can-onboard="{{ !empty($record['can_onboard']) ? '1' : '0' }}"
            data-id="{{ $record['record_id'] }}"
            data-name="{{ $record['name'] }}"
            data-first-name="{{ $record['first_name'] }}"
            data-middle-name="{{ $record['middle_name'] ?? '' }}"
            data-last-name="{{ $record['last_name'] }}"
            data-email="{{ $record['email'] }}"
            data-role="{{ $record['raw_role'] }}"
            data-role-label="{{ $record['role'] }}"
            data-status="{{ $record['status'] }}"
            data-source="{{ $record['source'] }}"
            data-source-label="{{ $record['source_label'] }}"
            data-student-id="{{ $record['student_id'] }}"
            data-avatar-url="{{ $record['avatar_url'] ?? '' }}"
            data-avatar-letter="{{ $record['avatar_letter'] }}"
            data-updated="{{ $record['meta']['updated_at'] ?? '' }}"
            data-meta='@json($record["meta"])'
        >
            <td>
                <div class="um-user">
                    <div class="um-avatar">
                        {{ $record['avatar_letter'] }}
                    </div>
                    <div>
                        <div class="um-name">{{ $record['name'] }}</div>
                        <div class="um-sub">{{ $lookupIdentifier ?: 'ID not available' }}</div>
                    </div>
                </div>
            </td>
            <td>{{ $record['email'] ?: 'N/A' }}</td>
            <td>{{ $record['role'] }}</td>
            <td><span class="um-badge {{ $record['status'] === 'inactive' ? 'inactive' : 'active' }}">{{ ucfirst($record['status']) }}</span></td>
        </tr>
    @empty
        <tr>
            <td colspan="4"><div class="um-empty">No users matched the current search.</div></td>
        </tr>
    @endforelse
</tbody>
