<div class="um-section-block account-access" id="accountAccessSection">
    <h4 class="um-section-title">{{ ($managementView ?? '') === 'admin-hub' ? 'Admin Hub Access' : 'Account Access' }}</h4>
    <p class="um-section-copy">
        {{ ($managementView ?? '') === 'admin-hub'
            ? 'Classify this shared directory profile, remove designee access, or deactivate resigned accounts.'
            : 'Assign clinic staff, Student Assistant, or super administrator access to this account.' }}
    </p>
    <div class="um-field">
        <label>{{ ($managementView ?? '') === 'admin-hub' ? 'Admin Hub Role' : 'Clinic Role' }}</label>
        <select name="user_role" id="detailRole">
            @if(($managementView ?? '') === 'admin-hub')
                <option value="admin_designee">Admin - Designee</option>
                <option value="super_admin">Super Admin</option>
            @else
                <option value="admin_clinic_staff">Admin - Clinic Staff</option>
                <option value="student_assistant">Admin - Student Assistant</option>
                <option value="super_admin">Super Admin</option>
            @endif
        </select>
    </div>
    @if(($managementView ?? '') === 'admin-hub')
        <div class="um-field" id="adminOfficeWrap">
            <label for="detailOffice">Department / Office</label>
            <input
                type="text"
                name="office"
                id="detailOffice"
                placeholder="Enter department or office"
                maxlength="255"
            >
        </div>
    @endif
    <input type="hidden" name="email" id="detailEditEmail">
    <div class="um-field">
        <label>Status</label>
        <select name="status" id="detailStatus">
            <option value="active">Active</option>
            <option value="inactive">Inactive</option>
        </select>
    </div>
</div>
