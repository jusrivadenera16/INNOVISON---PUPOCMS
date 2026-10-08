# Health Data Encryption Phase 4

Status: Feature-flagged dual-read/dual-write transition for student medical-history fields.

## What changed

- Added `HEALTH_DATA_ENCRYPTION_ENABLED`, disabled by default.
- When enabled, `HealthProfile` writes the six selected source fields to their encrypted mirror columns through normal model assignment, `fill()`, and `create()` flows.
- When enabled, reads prefer the decrypted mirror value and fall back to the plaintext source if the mirror is still `NULL`.
- When disabled, the model keeps the existing plaintext behavior and does not write encrypted mirrors.

## Rollout order

1. Apply the mirror-column migration.
2. Run the dry-run backfill and review the count.
3. Run the backfill with `--apply`.
4. Set `HEALTH_DATA_ENCRYPTION_ENABLED=true` only after the backfill completes.
5. Test health-form edits, nurse assessment, reports, walk-in records, sync, and approval flows.

The source columns remain in place during this phase. This is intentional so a missing mirror can fall back to the existing value during the transition.

## Important limitation

Direct query-builder updates bypass Eloquent model hooks. Current student health-form writes use the model, but any future `DB::table('health_profiles')->update(...)` for these fields must also write the encrypted mirror explicitly or be replaced with model-based writes.

SQL scopes that filter medical details still use the plaintext source columns. They must be replaced with a non-sensitive derived flag before the source columns can be removed.

## Rollback

Set `HEALTH_DATA_ENCRYPTION_ENABLED=false` and clear the cached configuration. The application will return to plaintext reads/writes while the mirror columns remain available. Do not rotate `APP_KEY`.
