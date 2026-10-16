# Health Data Encryption Phase 5

Status: Implemented and verified locally.

## Coverage

The Laravel encryption transition now covers the three health-profile tables and historical health-form snapshots:

- health_profiles for applicants and students
- health_profile_emp for employees, faculty, and clinic staff profiles
- dependents_profiles for dependent profiles
- health_form_submissions for frozen historical profile snapshots and submission remarks

Sensitive profile fields are written to nullable TEXT encrypted mirrors using Laravel's AES encryption through the application key. Array, date, boolean, and scalar values are restored to their normal model types when read.

Identity and operational lookup fields remain plaintext by design so the admin global search, joins, filters, and workflow status queries continue to work. Names, email addresses, student or employee numbers, reference numbers, and status fields are not encrypted in this phase.

## Local rollout performed

    php artisan migrate
    php artisan health:encrypt-student-medical-history
    php artisan health:encrypt-student-medical-history --apply

The command name is retained for compatibility with the earlier student-only rollout. It now processes student, employee, and dependent profiles and remains dry-run by default.

## Admin compatibility

- Student health records continue to open through the existing admin health-profile route.
- Employee health records continue to open through the existing employee health-profile route.
- The admin global search keeps using searchable identity fields and dependent identifiers.
- Employee search results now open the matching employee health profile directly.
- Model reads used by admin details, reports, review screens, and health-condition checks return decrypted values.

## Local verification

- AllHealthProfileEncryptionTest: 1 passed
- HealthProfileEncryptionTransitionTest: 3 passed
- EncryptedStudentMedicalHistoryBackfillTest: 2 passed
- Raw database checks confirmed encrypted mirror values for student, employee, and historical submission records.

## Plaintext source cleanup

The source columns remain in the schema for compatibility, but application reads and writes use the encrypted mirrors when health-data encryption is enabled. SQL filters that depended on plaintext health values have been removed or redirected to encrypted mirrors.

Local `clinic_laravel` cleanup completed on 2026-10-16: 291 source values were cleared after verification, with 0 unverified fields. A post-check found 0 non-null plaintext values in mapped source columns, 345 decryptable mirrors, and 0 decryption errors. This is local-only; staging and production databases need their own migration, backfill, dry-run, backup, and purge sequence.

Do not rotate APP_KEY while encrypted values are in use. Run the migration and backfill only after confirming a database backup for the target environment.
