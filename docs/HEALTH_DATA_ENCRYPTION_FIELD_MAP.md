# Health Data Encryption Field Map

Status: Phase 1 inventory only. No model casts, database migrations, or data changes are included in this phase.

## Current encryption baseline

- Laravel is configured with `AES-256-CBC` in `config/app.php`.
- `APP_KEY` is already configured locally and must remain stable during migration.
- `SystemSetting` already uses Laravel `Crypt`, but the health profile models do not yet use encrypted casts.
- Health documents are stored on the private health disk. This map covers database fields and document metadata; it does not change file storage.

## Classification

### Student health profiles: `health_profiles`

Recommended reversible encryption candidates:

- Personal and emergency data: `home_address`, `guardian_name`, `landline`, `cellphone`.
- Medical history: `medical_history`, `other_illness`, `disability_type`.
- Allergies and vaccination: `food_allergies`, `medicine_allergies`, `other_med_allergies`, `vaccine_history`.
- Clinical findings: `xray_findings`, `xray_findings_details`, `doctor_name`, `med_cert_findings`, `med_cert_findings_details`.
- Review notes: `assessment_remarks`, `med_assessment_remarks`, `medical_condition_remarks`, `encode_remarks`, `pending_reason`.
- Pullout notes: `pullout_reason`, `pullout_request_remarks`, `pullout_completion_remarks`.

Keep available for workflow, joins, and filtering during the first phase:

- `id`, `user_id`, `student_id`, `student_number`, `reference_number`.
- `clearance_status`, `documents_valid`, `has_illness`, `has_disability`, `no_allergies`.
- Review and sync status fields, approval user IDs, and timestamps.

The JSON fields `medical_history`, `medicine_allergies`, and `vaccine_history` cannot remain MySQL JSON columns after encryption. They need `TEXT`-sized storage or dedicated encrypted columns.

### Employee health profiles: `health_profile_emp`

Recommended reversible encryption candidates:

- Address and emergency data: `home_address`, `street`, `barangay`, `municipality`, `province`, `contact_no`, `emergency_contact_person`, `emergency_contact_no`.
- Medical history: `past_medical_history`, `past_medical_history_others`, `previous_hospitalization_details`, `operation_surgery_details`, `current_medications`, `allergies`, `family_history`, `family_history_others`.
- Examination findings: `disability_type`, `head_findings`, `eyes_findings`, `ears_findings`, `throat_findings`, `chest_lungs_findings`, `chest_xray_result`, `breast_findings`, `heart_murmur`, `heart_rhythm`, `abdomen_findings`, `extremities_findings`, `vertebral_column_findings`, `skin_findings`, `scars_findings`.
- Clinical assessment: `working_impression`, `referred_to`, `referred_to_others`, `follow_up_on`, `pending_reason`, and `draft_data`.

Keep available for joins and workflow filtering during the first phase:

- `id`, `user_id`, `employee_number`.
- `submission_status`, `clearance_status`, `fit_status`, `for_work_up`, `documents_valid`.
- Approval user IDs, status fields, and timestamps.

The array fields and `draft_data` should be stored as encrypted text, not JSON, when encrypted casts are introduced.

### Dependent profiles: `dependents_profiles`

Recommended reversible encryption candidates:

- `email`, `street`, `barangay`, `municipality`, `province`, `home_address`.
- `contact_no`, `landline`, `emergency_contact_name`, `emergency_contact_no`.

Keep available for relationships and operational lookup during the first phase:

- `id`, `user_id`, `idp_user_id`, `id_number`.
- `birthday`, `age`, `sex`, `civil_status`, and `submitted_at` until the dependent lookup requirements are confirmed.

### Submission snapshots: `health_form_submissions`

- `profile_snapshot` contains a historical copy of health information and should be treated as sensitive.
- Any draft or snapshot JSON that contains health information must be encrypted as a whole value or split into encrypted fields.
- `health_profile_id`, `employee_health_profile_id`, status fields, approval timestamps, and document paths remain operational fields.

## Query dependencies to resolve before encryption

- `HealthProfile::scopeWithMedicalCondition()` and `scopeWithoutMedicalCondition()` currently query medical fields directly in the database.
- Reports read many medical fields after loading the model, which is compatible with encrypted casts, but export/filter behavior must be regression-tested.
- A non-sensitive derived flag such as `has_medical_condition` may be needed for SQL filtering after the source details are encrypted.
- Names, email addresses, student numbers, and reference numbers should not be encrypted in the first wave because login, lookup, sync, and joins depend on them. If they need stronger protection later, use encrypted values plus a keyed lookup index.

## Phase 2 prerequisites

1. Confirm this field classification with the clinic workflow owner.
2. Create a verified database and private-file backup.
3. Decide between new encrypted columns and in-place column conversion. New columns are safer for rollback but temporarily duplicate data.
4. Add migration tests for `TEXT` capacity, null values, arrays, and existing records.
5. Add model casts only after the query dependencies above have an implementation path.

