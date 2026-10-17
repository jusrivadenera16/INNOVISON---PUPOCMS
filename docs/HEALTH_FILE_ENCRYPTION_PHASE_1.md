# Health File Encryption Phase 1

Status: Implemented and verified locally.

## Scope

Health files written through `HealthFileStorage` are encrypted before being saved to the configured private disk. The encrypted file keeps its original path and extension so existing database references continue to work.

The file envelope uses AES-256-GCM with a random nonce and authentication tag. New file writes and normal reads require the dedicated `HEALTH_FILES_ENCRYPTION_KEY`; `APP_KEY` is never used as an automatic fallback. If an explicit migration from files encrypted with the old `APP_KEY` is needed, use the one-time `health-files:rotate-key --from-app-key` command and verify its results before normal reads.

## Application behavior

- New uploads and generated Health Form PDFs are encrypted before storage.
- Reads, inline previews, image data URIs, and downloads decrypt only while serving the authorized response.
- Legacy plaintext private files remain readable during transition until the backfill command encrypts them.
- Public legacy fallback remains disabled by the existing environment settings.
- Controller responses no longer expose an encrypted file path directly; they use the storage service response helper.

## Local rollout

```text
HEALTH_FILES_ENCRYPTION_ENABLED=true
php artisan health-files:encrypt-private
php artisan health-files:encrypt-private --apply
```

The dry run must be reviewed before `--apply`. The local rollout encrypted and verified 150 private files, with zero remaining files reported for encryption.

## Staging rollout order

1. Back up the private storage directory and generate a unique dedicated key for staging.
2. Set `HEALTH_FILES_ENCRYPTION_ENABLED=true` and `HEALTH_FILES_ENCRYPTION_KEY`.
3. Run `php artisan config:clear` or rebuild the production config cache.
4. Run `php artisan health-files:rotate-key --from-app-key` and review the dry-run count.
5. Run `php artisan health-files:rotate-key --from-app-key --apply` and verify every file.
6. Test authorized preview/download, file replacement, deletion, PDF generation, and an unauthorized document URL.

Do not rotate `APP_KEY` while encrypted health files are in use. The Hostinger malware scanner may not inspect encrypted file contents, so malware scanning and file encryption should be treated as separate controls.
