<tbody id="lookupResultsBody">
    @forelse($lookupRecords as $record)
        @php
            $canUpdateLocalUser = !empty($record['is_local_user']) && !empty($record['can_edit']);
            $canOnboardLookupRecord = !$canUpdateLocalUser && !empty($record['can_onboard']);
        @endphp
        <tr
            data-user-card
            data-lookup-result-row
            data-update-url="{{ $canUpdateLocalUser ? route('admin.user-management.update', $record['id']) : '' }}"
            data-delete-url="{{ $canUpdateLocalUser ? route('admin.user-management.destroy', $record['id']) : '' }}"
            data-delete-admin-hub-url="{{ $record['delete_admin_hub_url'] ?? '' }}"
            data-create-url="{{ $canOnboardLookupRecord ? route('admin.user-management.store-from-lookup') : '' }}"
            data-can-edit="{{ $canUpdateLocalUser ? '1' : '0' }}"
            data-can-onboard="{{ $canOnboardLookupRecord ? '1' : '0' }}"
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
                        @php($employeeNumber = trim((string) ($record['meta']['employee_number'] ?? $record['meta']['faculty_identifier'] ?? '')))
                        <div class="um-sub">{{ $employeeNumber !== '' ? $employeeNumber : 'Employee number not available' }}</div>
                    </div>
                </div>
            </td>
            <td>{{ $record['email'] ?: 'N/A' }}</td>
            <td>{{ $record['role'] }}</td>
            <td><span class="um-badge {{ $record['status'] === 'inactive' ? 'inactive' : 'active' }}">{{ ucfirst($record['status']) }}</span></td>
            <td><span class="um-badge source">{{ $record['source_label'] }}</span></td>
        </tr>
    @empty
        <tr>
            <td colspan="5"><div class="um-empty">No users matched the current search.</div></td>
        </tr>
    @endforelse
</tbody>
