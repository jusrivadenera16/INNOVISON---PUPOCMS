# Health Data Encryption Phase 2

Status: Local prototype only.

The prototype uses an isolated SQLite table and Laravel encrypted casts to verify the storage behavior before changing the real health profile tables.

## Prototype scope

- `medical_history` uses `encrypted:array`.
- `emergency_contact` uses `encrypted`.
- `medical_remarks` uses `encrypted`.
- All prototype columns use `TEXT` storage.
- The prototype verifies that raw database values do not contain the plaintext and that the Eloquent model returns the original values.

## Not included yet

- No production model casts.
- No production database migration.
- No existing-record backfill.
- No changes to `APP_KEY`.
- No changes to the student, employee, dependent, report, sync, or document workflows.

## Gate for the next phase

The real migration should only be designed after this prototype passes and the dependent query changes are mapped. The next phase will add a reversible migration/backfill strategy for one approved field group, with a backup and rollback path.
