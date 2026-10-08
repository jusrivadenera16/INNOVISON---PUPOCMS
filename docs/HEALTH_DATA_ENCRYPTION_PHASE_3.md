# Health Data Encryption Phase 3

Status: Reversible mirror-column and backfill preparation. Do not run on production yet.

## What changed

- Added nullable `TEXT` encrypted mirror columns to `health_profiles` for student medical-history fields.
- Added Laravel encrypted casts to `HealthProfile` for those mirror columns.
- Added `health:encrypt-student-medical-history` with a dry-run default.
- The command uses `withoutGlobalScopes()` so inactive or pulled-out student records are included in the inventory.
- Existing plaintext columns are still present and unchanged. Existing application reads and writes still use those source columns.

## Fields included

| Plaintext source | Encrypted mirror |
| --- | --- |
| `medical_history` | `medical_history_encrypted` |
| `other_illness` | `other_illness_encrypted` |
| `food_allergies` | `food_allergies_encrypted` |
| `medicine_allergies` | `medicine_allergies_encrypted` |
| `other_med_allergies` | `other_med_allergies_encrypted` |
| `vaccine_history` | `vaccine_history_encrypted` |

## Controlled rollout

Run these only against a verified local or staging database backup:

```text
php artisan migrate
php artisan health:encrypt-student-medical-history
php artisan health:encrypt-student-medical-history --apply
```

The first command creates the nullable mirror columns. The second command only reports how many records would be written. The third command writes encrypted values while leaving the source columns intact.

Use `--chunk=50` or another value if the database needs smaller batches. Use `--force --apply` only to refresh mirrors that already contain encrypted values.

## Rollback

Before a production rollout, verify the backup and application key. The migration's `down()` method removes only the six mirror columns. It does not remove or alter the original source columns. Do not rotate `APP_KEY` during this process; existing encrypted values become unreadable after a key rotation.

## Not finished yet

- The application still reads the plaintext source columns for reports, workflows, filtering, and form writes.
- The source columns must not be deleted after this phase.
- The next phase must add dual-write and encrypted-read handling, then replace SQL filters such as `scopeWithMedicalCondition()` with a non-sensitive derived flag before plaintext removal.
- The database-backed backfill test is currently blocked by the checkout's pre-existing Laravel 8 / Doctrine DBAL 4.4 dependency mismatch. The feature-flagged model transition tests pass, and PHP lint/static checks pass.
