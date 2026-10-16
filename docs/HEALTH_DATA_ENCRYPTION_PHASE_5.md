# Health Data Encryption Phase 5

Status: Implemented and verified locally.

## Coverage

The Laravel encryption transition now covers the three health-profile tables and historical health-form snapshots:

- health_profiles for applicants and students
- health_profile_emp for employees, faculty, and clinic staff profiles
- dependents_profiles for dependent profiles
- health_form_submissions for frozen historical profile snapshots and submission remarks

Sensitive profile fields and consultation draft payloads are encrypted with the dedicated `HEALTH_DATA_ENCRYPTION_KEY`. Array, date, boolean, and scalar values are restored to their normal model types when read. Session encryption and unrelated Laravel encryption continue to use `APP_KEY`.

During key transition, `HEALTH_DATA_ENCRYPTION_LEGACY_APP_KEY_FALLBACK=true` permits reads of existing mirrors encrypted with `APP_KEY`. New writes always use the dedicated health-data key. After rotating existing mirrors and verifying them with the dedicated key, disable the fallback.

Identity and operational lookup fields remain plaintext by design so the admin global search, joins, filters, and workflow status queries continue to work. Names, email addresses, student or employee numbers, reference numbers, and status fields are not encrypted in this phase.

## Local rollout performed

    php artisan migrate
    php artisan health:encrypt-student-medical-history
    php artisan health:encrypt-student-medical-history --apply

The command name is retained for compatibility with the earlier student-only rollout. It now processes student, employee, dependent, medical-assessment, and historical-submission fields and remains dry-run by default. Use `--force` for a dry-run re-encryption of existing mirrors, then `--force --apply` to rewrite them under the dedicated key; each rewritten value is checked with that key.

## Per-environment key rollout

Generate a unique 32-byte key for each environment, for example with `php -r "echo 'base64:'.base64_encode(random_bytes(32)).PHP_EOL;"`. Store it only in that environment's secret `.env` as `HEALTH_DATA_ENCRYPTION_KEY`; never commit it or reuse the local key. Keep `HEALTH_DATA_ENCRYPTION_LEGACY_APP_KEY_FALLBACK=true` during migration.

After deployment and migrations, run the health backfill dry-run/apply, followed by `health:encrypt-student-medical-history --force` and `health:encrypt-student-medical-history --force --apply`. Run `consultation-drafts:encrypt --force` and `consultation-drafts:encrypt --force --apply` to migrate or rotate draft payloads. Verify both dry-runs, then set `HEALTH_DATA_ENCRYPTION_LEGACY_APP_KEY_FALLBACK=false`, rebuild Laravel's config cache, and rerun the force dry-runs before any plaintext purge. Keep the staging database backup until the rollout is verified.

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

Local `clinic_laravel` cleanup completed on 2026-10-16: 291 source values were cleared after verification, with 0 unverified fields. A post-check found 0 non-null plaintext values in mapped source columns, 345 decryptable mirrors, and 0 decryption errors. The local mirrors were then rotated from `APP_KEY` to a unique local `HEALTH_DATA_ENCRYPTION_KEY`; 23 populated profile/submission rows were rewritten and verified. With legacy fallback disabled, a fresh force dry-run still read all mapped health data. The local consultation-draft migration is applied and there were no drafts to rotate. Staging and production need their own per-environment key and rollout sequence.

Do not rotate `APP_KEY` while sessions or other application-encrypted values use it. Generate a unique `HEALTH_DATA_ENCRYPTION_KEY` per environment and keep it outside source control. Run database mutations only after confirming a backup for the target environment.
