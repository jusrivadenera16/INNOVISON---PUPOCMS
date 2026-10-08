# Health File Encryption Phase 1

Status: Implemented and verified locally.

## Scope

Health files written through `HealthFileStorage` are encrypted before being saved to the configured private disk. The encrypted file keeps its original path and extension so existing database references continue to work.

The file envelope uses AES-256-GCM with a random nonce and authentication tag. The key defaults to `APP_KEY`; a dedicated `HEALTH_FILES_ENCRYPTION_KEY` may be configured later if key management is changed consistently across environments.

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

1. Back up the staging database and private storage directory.
2. Deploy the code and confirm the same `APP_KEY` used to encrypt the files is present.
3. Run `php artisan config:clear` or rebuild the production config cache.
4. Set `HEALTH_FILES_ENCRYPTION_ENABLED=true`.
5. Run `php artisan health-files:encrypt-private` and review the count.
6. Run `php artisan health-files:encrypt-private --apply`.
7. Confirm the command reports zero files still requiring encryption.
8. Test authorized preview/download, file replacement, deletion, PDF generation, and an unauthorized document URL.

Do not rotate `APP_KEY` while encrypted health files are in use. The Hostinger malware scanner may not inspect encrypted file contents, so malware scanning and file encryption should be treated as separate controls.
