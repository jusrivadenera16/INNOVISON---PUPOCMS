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
    @if(($managementView ?? '') !== 'admin-hub')
        <section class="um-module-access-preview" id="moduleAccessPreview" hidden aria-labelledby="moduleAccessTitle">
            <div class="um-module-access-head">
                <div>
                    <h5 id="moduleAccessTitle">Module Access</h5>
                    <p id="moduleAccessRoleSummary">Suggested access for this clinic role.</p>
                </div>
                <span class="um-preview-badge">UI Preview</span>
            </div>

            <div class="um-module-access-toolbar">
                <div class="um-module-selection-summary">
                    <strong id="moduleAccessSelectionSummary">0 modules selected</strong>
                    <span>View access is included with each selected module.</span>
                </div>
                <button type="button" class="um-reset-module-defaults" id="resetModuleDefaultsButton">
                    Reset to role defaults
                </button>
            </div>

            <div class="um-module-access-grid">
                <div class="um-module-item" data-module-item>
                    <div class="um-module-row">
                        <label class="um-module-option">
                            <input type="checkbox" data-module-permission data-clinic-staff="1" data-student-assistant="1">
                            <span class="um-module-icon" aria-hidden="true"><x-outline-icon name="calendar-days" /></span>
                            <span class="um-module-copy">
                                <span class="um-module-title"><strong>Appointments</strong><span>View</span></span>
                                <small>Schedules and appointment status</small>
                            </span>
                            <span class="um-module-check" aria-hidden="true"><x-outline-icon name="check" /></span>
                        </label>
                        <button type="button" class="um-module-expand" data-module-expand aria-expanded="false" aria-controls="appointmentPermissions" aria-label="Show appointment permissions" title="Additional permissions">
                            <x-outline-icon name="chevron-down" />
                        </button>
                    </div>
                    <div class="um-module-actions" id="appointmentPermissions" data-module-actions hidden>
                        <div class="um-module-actions-head"><strong>Additional permissions</strong><span>View access is included</span></div>
                        <label class="um-action-permission">
                            <input type="checkbox" data-module-action>
                            <span class="um-action-check" aria-hidden="true"><x-outline-icon name="check" /></span>
                            <span><strong>Approve appointments</strong><small>Confirm pending appointment requests</small></span>
                        </label>
                        <label class="um-action-permission">
                            <input type="checkbox" data-module-action>
                            <span class="um-action-check" aria-hidden="true"><x-outline-icon name="check" /></span>
                            <span><strong>Reject appointments</strong><small>Decline appointment requests</small></span>
                        </label>
                        <label class="um-action-permission">
                            <input type="checkbox" data-module-action>
                            <span class="um-action-check" aria-hidden="true"><x-outline-icon name="check" /></span>
                            <span><strong>Reschedule</strong><small>Change appointment dates and times</small></span>
                        </label>
                    </div>
                </div>

                <div class="um-module-item" data-module-item>
                    <div class="um-module-row">
                        <label class="um-module-option">
                            <input type="checkbox" data-module-permission data-clinic-staff="1" data-student-assistant="1">
                            <span class="um-module-icon" aria-hidden="true"><x-outline-icon name="clipboard-document-list" /></span>
                            <span class="um-module-copy">
                                <span class="um-module-title"><strong>Walk-in &amp; Consultation</strong><span>View</span></span>
                                <small>Patient intake and consultation workflow</small>
                            </span>
                            <span class="um-module-check" aria-hidden="true"><x-outline-icon name="check" /></span>
                        </label>
                        <button type="button" class="um-module-expand" data-module-expand aria-expanded="false" aria-controls="walkinPermissions" aria-label="Show walk-in permissions" title="Additional permissions">
                            <x-outline-icon name="chevron-down" />
                        </button>
                    </div>
                    <div class="um-module-actions" id="walkinPermissions" data-module-actions hidden>
                        <div class="um-module-actions-head"><strong>Additional permissions</strong><span>View access is included</span></div>
                        <label class="um-action-permission">
                            <input type="checkbox" data-module-action>
                            <span class="um-action-check" aria-hidden="true"><x-outline-icon name="check" /></span>
                            <span><strong>Register patient</strong><small>Create walk-in clinic entries</small></span>
                        </label>
                        <label class="um-action-permission">
                            <input type="checkbox" data-module-action>
                            <span class="um-action-check" aria-hidden="true"><x-outline-icon name="check" /></span>
                            <span><strong>Encode assessment</strong><small>Enter intake and assessment information</small></span>
                        </label>
                        <label class="um-action-permission">
                            <input type="checkbox" data-module-action>
                            <span class="um-action-check" aria-hidden="true"><x-outline-icon name="check" /></span>
                            <span><strong>Review submission</strong><small>Review completed patient information</small></span>
                        </label>
                        <label class="um-action-permission">
                            <input type="checkbox" data-module-action>
                            <span class="um-action-check" aria-hidden="true"><x-outline-icon name="check" /></span>
                            <span><strong>Final review / approve patients</strong><small>Complete the final patient review and approval</small></span>
                        </label>
                    </div>
                </div>

                <div class="um-module-item" data-module-item>
                    <div class="um-module-row">
                        <label class="um-module-option">
                            <input type="checkbox" data-module-permission data-clinic-staff="1" data-student-assistant="1">
                            <span class="um-module-icon" aria-hidden="true"><x-outline-icon name="heart-pulse" /></span>
                            <span class="um-module-copy">
                                <span class="um-module-title"><strong>Health Records</strong><span>View</span></span>
                                <small>Health profiles and document review</small>
                            </span>
                            <span class="um-module-check" aria-hidden="true"><x-outline-icon name="check" /></span>
                        </label>
                        <button type="button" class="um-module-expand" data-module-expand aria-expanded="false" aria-controls="healthRecordPermissions" aria-label="Show health record permissions" title="Additional permissions">
                            <x-outline-icon name="chevron-down" />
                        </button>
                    </div>
                    <div class="um-module-actions" id="healthRecordPermissions" data-module-actions hidden>
                        <div class="um-module-actions-head"><strong>Additional permissions</strong><span>View access is included</span></div>
                        <label class="um-action-permission">
                            <input type="checkbox" data-module-action>
                            <span class="um-action-check" aria-hidden="true"><x-outline-icon name="check" /></span>
                            <span><strong>Review documents</strong><small>Review submitted health requirements</small></span>
                        </label>
                        <label class="um-action-permission">
                            <input type="checkbox" data-module-action>
                            <span class="um-action-check" aria-hidden="true"><x-outline-icon name="check" /></span>
                            <span><strong>Request resubmission</strong><small>Return incomplete or invalid documents</small></span>
                        </label>
                        <label class="um-action-permission">
                            <input type="checkbox" data-module-action>
                            <span class="um-action-check" aria-hidden="true"><x-outline-icon name="check" /></span>
                            <span><strong>Update assessment</strong><small>Edit nurse review and assessment details</small></span>
                        </label>
                        <div class="um-action-permission is-locked">
                            <span class="um-action-lock" aria-hidden="true"><x-outline-icon name="shield-check" /></span>
                            <span><strong>Final approval &amp; signing</strong><small>Restricted to Super Admin</small></span>
                            <span class="um-locked-badge">Locked</span>
                        </div>
                    </div>
                </div>

                <div class="um-module-item" data-module-item>
                    <div class="um-module-row">
                        <label class="um-module-option">
                            <input type="checkbox" data-module-permission data-clinic-staff="1" data-student-assistant="1">
                            <span class="um-module-icon" aria-hidden="true"><x-outline-icon name="cube" /></span>
                            <span class="um-module-copy">
                                <span class="um-module-title"><strong>Inventory</strong><span>View</span></span>
                                <small>Medicine stock and availability</small>
                            </span>
                            <span class="um-module-check" aria-hidden="true"><x-outline-icon name="check" /></span>
                        </label>
                        <button type="button" class="um-module-expand" data-module-expand aria-expanded="false" aria-controls="inventoryPermissions" aria-label="Show inventory permissions" title="Additional permissions">
                            <x-outline-icon name="chevron-down" />
                        </button>
                    </div>
                    <div class="um-module-actions" id="inventoryPermissions" data-module-actions hidden>
                        <div class="um-module-actions-head"><strong>Additional permissions</strong><span>View access is included</span></div>
                        <label class="um-action-permission">
                            <input type="checkbox" data-module-action>
                            <span class="um-action-check" aria-hidden="true"><x-outline-icon name="check" /></span>
                            <span><strong>Import inventory</strong><small>Upload inventory records from a file</small></span>
                        </label>
                        <label class="um-action-permission">
                            <input type="checkbox" data-module-action>
                            <span class="um-action-check" aria-hidden="true"><x-outline-icon name="check" /></span>
                            <span><strong>Add stock</strong><small>Add quantities to an inventory item</small></span>
                        </label>
                        <label class="um-action-permission">
                            <input type="checkbox" data-module-action>
                            <span class="um-action-check" aria-hidden="true"><x-outline-icon name="check" /></span>
                            <span><strong>Manage inventory</strong><small>Create, edit, or remove inventory items</small></span>
                        </label>
                    </div>
                </div>

                <div class="um-module-item" data-module-item>
                    <div class="um-module-row">
                        <label class="um-module-option">
                            <input type="checkbox" data-module-permission data-clinic-staff="0" data-student-assistant="0">
                            <span class="um-module-icon" aria-hidden="true"><x-outline-icon name="chart-bar" /></span>
                            <span class="um-module-copy">
                                <span class="um-module-title"><strong>Reports</strong><span>View</span></span>
                                <small>Operational summaries and reports</small>
                            </span>
                            <span class="um-module-check" aria-hidden="true"><x-outline-icon name="check" /></span>
                        </label>
                        <button type="button" class="um-module-expand" data-module-expand aria-expanded="false" aria-controls="reportPermissions" aria-label="Show report permissions" title="Additional permissions">
                            <x-outline-icon name="chevron-down" />
                        </button>
                    </div>
                    <div class="um-module-actions" id="reportPermissions" data-module-actions hidden>
                        <div class="um-module-actions-head"><strong>Available report cards</strong><span>Select only what this account can open</span></div>
                        <label class="um-action-permission">
                            <input type="checkbox" data-module-action>
                            <span class="um-action-check" aria-hidden="true"><x-outline-icon name="check" /></span>
                            <span><strong>MAR Reports</strong><small>Medical accomplishment records</small></span>
                        </label>
                        <label class="um-action-permission">
                            <input type="checkbox" data-module-action>
                            <span class="um-action-check" aria-hidden="true"><x-outline-icon name="check" /></span>
                            <span><strong>Inventory Summary</strong><small>Stock movement and inventory levels</small></span>
                        </label>
                        <label class="um-action-permission">
                            <input type="checkbox" data-module-action>
                            <span class="um-action-check" aria-hidden="true"><x-outline-icon name="check" /></span>
                            <span><strong>Health Forms</strong><small>Issued health form summaries</small></span>
                        </label>
                        <label class="um-action-permission">
                            <input type="checkbox" data-module-action>
                            <span class="um-action-check" aria-hidden="true"><x-outline-icon name="check" /></span>
                            <span><strong>Appointment Statistics</strong><small>Appointment trends and clinic flow</small></span>
                        </label>
                        <label class="um-action-permission">
                            <input type="checkbox" data-module-action>
                            <span class="um-action-check" aria-hidden="true"><x-outline-icon name="check" /></span>
                            <span><strong>Digital Logbook</strong><small>Treatments and clinic visit logs</small></span>
                        </label>
                        <label class="um-action-permission">
                            <input type="checkbox" data-module-action>
                            <span class="um-action-check" aria-hidden="true"><x-outline-icon name="check" /></span>
                            <span><strong>Feedbacks</strong><small>Patient ratings, comments, and feedback</small></span>
                        </label>
                        <label class="um-action-permission">
                            <input type="checkbox" data-module-action>
                            <span class="um-action-check" aria-hidden="true"><x-outline-icon name="check" /></span>
                            <span><strong>Export reports</strong><small>Download or print report data</small></span>
                        </label>
                        <div class="um-action-permission is-locked">
                            <span class="um-action-lock" aria-hidden="true"><x-outline-icon name="shield-check" /></span>
                            <span><strong>Audit Trail</strong><small>Restricted to Super Admin</small></span>
                            <span class="um-locked-badge">Locked</span>
                        </div>
                    </div>
                </div>

                <div class="um-module-item" data-module-item>
                    <div class="um-module-row">
                        <label class="um-module-option">
                            <input type="checkbox" data-module-permission data-clinic-staff="0" data-student-assistant="0">
                            <span class="um-module-icon" aria-hidden="true"><x-outline-icon name="megaphone" /></span>
                            <span class="um-module-copy">
                                <span class="um-module-title"><strong>Announcements</strong><span>View</span></span>
                                <small>Clinic notices and announcements</small>
                            </span>
                            <span class="um-module-check" aria-hidden="true"><x-outline-icon name="check" /></span>
                        </label>
                        <button type="button" class="um-module-expand" data-module-expand aria-expanded="false" aria-controls="announcementPermissions" aria-label="Show announcement permissions" title="Additional permissions">
                            <x-outline-icon name="chevron-down" />
                        </button>
                    </div>
                    <div class="um-module-actions" id="announcementPermissions" data-module-actions hidden>
                        <div class="um-module-actions-head"><strong>Additional permissions</strong><span>View access is included</span></div>
                        <label class="um-action-permission">
                            <input type="checkbox" data-module-action>
                            <span class="um-action-check" aria-hidden="true"><x-outline-icon name="check" /></span>
                            <span><strong>Publish announcements</strong><small>Create and update clinic notices</small></span>
                        </label>
                        <label class="um-action-permission">
                            <input type="checkbox" data-module-action>
                            <span class="um-action-check" aria-hidden="true"><x-outline-icon name="check" /></span>
                            <span><strong>Archive announcements</strong><small>Archive or remove existing notices</small></span>
                        </label>
                    </div>
                </div>

                <div class="um-module-item" data-module-item>
                    <div class="um-module-row">
                        <label class="um-module-option">
                            <input type="checkbox" data-module-permission data-clinic-staff="0" data-student-assistant="0">
                            <span class="um-module-icon" aria-hidden="true"><x-outline-icon name="cog-6-tooth" /></span>
                            <span class="um-module-copy">
                                <span class="um-module-title"><strong>Settings</strong><span>View</span></span>
                                <small>Clinic, workflow, medical, and FAQ settings</small>
                            </span>
                            <span class="um-module-check" aria-hidden="true"><x-outline-icon name="check" /></span>
                        </label>
                        <button type="button" class="um-module-expand" data-module-expand aria-expanded="false" aria-controls="settingsPermissions" aria-label="Show settings permissions" title="Additional permissions">
                            <x-outline-icon name="chevron-down" />
                        </button>
                    </div>
                    <div class="um-module-actions" id="settingsPermissions" data-module-actions hidden>
                        <div class="um-module-actions-head"><strong>Available settings cards</strong><span>Select only what this account can open</span></div>
                        <label class="um-action-permission">
                            <input type="checkbox" data-module-action>
                            <span class="um-action-check" aria-hidden="true"><x-outline-icon name="check" /></span>
                            <span><strong>Personal Information</strong><small>Profile details and account identity</small></span>
                        </label>
                        <label class="um-action-permission">
                            <input type="checkbox" data-module-action>
                            <span class="um-action-check" aria-hidden="true"><x-outline-icon name="check" /></span>
                            <span><strong>Clinic Information</strong><small>Clinic profile, contacts, and office hours</small></span>
                        </label>
                        <label class="um-action-permission">
                            <input type="checkbox" data-module-action>
                            <span class="um-action-check" aria-hidden="true"><x-outline-icon name="check" /></span>
                            <span><strong>System Preferences</strong><small>Workflow, reminders, and availability rules</small></span>
                        </label>
                        <label class="um-action-permission">
                            <input type="checkbox" data-module-action>
                            <span class="um-action-check" aria-hidden="true"><x-outline-icon name="check" /></span>
                            <span><strong>Medical Configuration</strong><small>Medical conditions and medicine setup</small></span>
                        </label>
                        <label class="um-action-permission">
                            <input type="checkbox" data-module-action>
                            <span class="um-action-check" aria-hidden="true"><x-outline-icon name="check" /></span>
                            <span><strong>FAQs</strong><small>Clinic FAQ content and categories</small></span>
                        </label>
                        <div class="um-action-permission is-locked">
                            <span class="um-action-lock" aria-hidden="true"><x-outline-icon name="shield-check" /></span>
                            <span><strong>Users Management</strong><small>Restricted to Super Admin</small></span>
                            <span class="um-locked-badge">Locked</span>
                        </div>
                    </div>
                </div>
            </div>

            <div class="um-superadmin-access-summary">
                <span class="um-superadmin-summary-icon" aria-hidden="true"><x-outline-icon name="shield-check" /></span>
                <div>
                    <strong>Restricted to Super Admin</strong>
                    <div class="um-superadmin-access-list">
                        <span>Final approval &amp; signing</span>
                        <span>User Management</span>
                        <span>Developer Tools</span>
                        <span>API Integrations</span>
                        <span>Audit Trail</span>
                    </div>
                </div>
            </div>

            <p class="um-module-preview-note">Preview only. Module choices are not saved yet.</p>
        </section>
    @endif
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
