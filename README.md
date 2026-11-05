# PUP Taguig Medical Clinic Management System

**Project:** INNOVISON PUPOCMS

**Purpose:** Manage clinic appointments, health records, consultations, inventory, and clinic administration for PUP Taguig.

**Manual scope:** This is the single general system and operations manual. It is based on the repository source and configuration inspected on 2026-11-05. Production settings can differ from the local project.

> Never put passwords, API keys, access tokens, encryption keys, real patient data, or unredacted screenshots in this file or in source control.

## 1. System Overview

PUPOCMS is a Laravel web application with separate student-facing and clinic staff workflows. It supports health-form intake and review, appointments, walk-in consultations, health records, clinic inventory, operational reports, account permissions, and external system integrations.

The application is organized around Laravel routes, controllers, services, Eloquent models, Blade templates, and database migrations. The browser UI is Blade, HTML, CSS, and JavaScript built with Vite; the current npm manifest does not list Angular or TypeScript.

## 2. Users and Access

Access is determined by the authenticated account guard, role, account status, and, for many clinic modules, module permissions. Being able to sign in does not automatically grant access to every module.

| Account/work group | Typical use |
| --- | --- |
| Students and applicants, including transferees | Submit applicable health forms and documents, manage their clinic account, request appointments, and view their own clinic information. |
| Employees and clinic staff | Use the employee/staff health-profile workflows available to their account. |
| Clinic administrators and nurses | Review health records and documents, manage appointments, conduct walk-in and consultation workflows, and use assigned clinic modules. |
| Student assistants | Use only the clinic or assistant modules and actions assigned to their account. |
| Super Admin | Manage high-privilege clinic settings, user access, developer tools, integration tokens, and restricted record workflows. |

Super Admin-only routes include User Management, Developer Tools, integration-token administration, and certain pull-out/restore actions. Module access can be assigned separately from a user's source identity. Follow the access shown in User Management rather than assuming every admin account has identical permissions.

## 3. Main System Areas

### Student and applicant portal

- Account sign-in and portal entry.
- Student, transferee, employee, and staff health-form paths where applicable.
- Health-form and document submission, e-signature, and health-record views.
- Appointment booking, appointment history, cancellation, and notifications.
- Health-record correction or document workflows exposed to the account.

The exact form and required documents depend on the account category and current clinic configuration. Applicants should use the current admission/clinic instructions shown in the portal.

### Clinic dashboard and appointments

- Dashboard summaries and clinic availability controls.
- Appointment review, approval, rejection, and rescheduling.
- Walk-in intake and consultation workflows.
- Consultation records, assessment details, vitals, and dispensed medicine or supplies where recorded.

Clinic availability affects whether booking or consultation actions can proceed. The appointment record and its workflow status are distinct from a completed consultation record.

### Health Records

- Review submitted health forms and uploaded documents.
- Review, approve, or return submissions through the available workflow.
- View student and employee health profiles, documents, and submission history.
- Process health-information updates, document correction/resubmission requests, and new-form requests.
- Manage clearance status and supported pull-out/restoration actions.

Pulling out a record is a workflow/status action, not proof that the underlying health record was deleted. A restoration workflow exists for records that were pulled out. Do not describe this action as data erasure.

### Inventory

- Manage clinic medicines and supplies, current stock, minimum stock, and expiry information.
- Record stock-in/restock, issue/consumption, and adjustments.
- Review item stock-movement history and inventory reports.
- Use expiry monitoring to identify items approaching expiry; follow the configured item category and expiry rules.

Stock movement history is the transaction record for changes to inventory. Use it to review who recorded a movement, when it occurred, the quantity, and the resulting stock values where available.

### Reports and administration

Reports include inventory summaries, appointment statistics/history, health-form reports, clinic operational reports, and service-evaluation reports, subject to permissions.

Settings cover clinic information, medical configuration, health-form categories, medicine types, MAR configuration, FAQ content, and other clinic options. User Management controls clinic access, roles, and module permissions. Developer Tools contains restricted integration, system, and API diagnostics.

## 4. Typical Workflows

### Health-form review

1. The student/applicant submits the applicable health form, required documents, and declaration/signature.
2. Clinic staff reviews the submission and documents in Health Records or the applicable review workflow.
3. Staff records the review outcome and any required corrections or resubmissions.
4. Approved health information is available through the user's health record and any separately configured integration.

Submission versions and review states should be treated as health-record history. A new request or correction is not automatically an approval.

### Appointment and consultation

1. The user selects an available clinic service and submits a booking request.
2. Clinic staff reviews the appointment and changes its status or schedule as needed.
3. For a completed consultation, staff records the applicable clinical details, vitals, findings, and dispensed items in the consultation workflow.
4. Appointment details and completed consultation details are related but are not the same record.

### Inventory movement

1. Select the item and record the appropriate action: receive/restock, issue/consume, or adjustment.
2. Enter the quantity and relevant remarks or expiry/batch information when applicable.
3. Confirm the transaction and review the item's stock history for the recorded movement.

Avoid changing stock by editing a quantity without checking the associated movement history and audit information.

## 5. Application Entry Points

The local host, production domain, and route availability depend on deployment configuration. Common route prefixes include:

| Area | Route examples |
| --- | --- |
| Public landing and sign-in | `/`, `/login`, `/login/portal`, `/auth/callback` |
| Student portal | `/student/home`, `/student/booking`, `/student/health-form`, `/student/account`, `/student/history` |
| Clinic dashboard and operations | `/admin/dashboard`, `/admin/appointments`, `/admin/walkin`, `/admin/inventory`, `/admin/reports` |
| Health Records | `/health-records`, `/health-profile/{id}`, `/employee-health-profile/{id}` |
| User Management and Developer Tools | `/admin/user-management`, `/admin/user-management/account-access`, `/admin/developer-tools`, `/admin/integration-tokens` |
| External APIs | `/api/external/...` |

Routes are protected by authentication, role, account-status, session, and module-permission middleware as applicable. Do not bypass these controls by exposing controller URLs directly.

## 6. Technology and Dependencies

### Core runtime

- PHP requirement: `^8.2` in `composer.json` (PHP 8.2 or later, below 9.0).
- Laravel Framework: `^12.69`.
- Database: Laravel's default connection is `mysql`, using the PHP PDO MySQL driver. The actual database server is selected by environment configuration; local XAMPP installations commonly provide MariaDB.
- ORM and query access: Laravel Eloquent, Laravel Query Builder, and the `DB` facade/raw SQL in selected operations.
- Frontend: Blade templates, HTML/CSS, JavaScript, and Vite `^7.1`.

### Key Composer packages

| Package | Role in the system |
| --- | --- |
| `laravel/sanctum` | API personal-access tokens used by supported integrations. |
| `guzzlehttp/guzzle` | HTTP transport used by Laravel's HTTP client for external requests. |
| `barryvdh/laravel-dompdf` | PDF generation for forms, reports, and printable records. |
| `pragmarx/google2fa` | Time-based authenticator-code support for emergency-login verification. |
| `laravel/tinker` | Interactive Laravel command-line shell for development/maintenance. |

`doctrine/dbal`, `simplesoftwareio/simple-qrcode`, and `paragonie/constant_time_encoding` are declared in Composer, but direct use was not found in the application source checked for this manual. Do not present them as active application features without confirming their use. Eloquent is the project's ORM; Doctrine DBAL is not a second ORM.

### Frontend and development packages

The npm manifest lists `vite`, `axios`, and `lodash` under `devDependencies`, and `matter-js` under `dependencies`. Axios and Lodash are imported by the frontend bootstrap; a direct Matter.js reference was not found in the application source checked for this manual.

Testing/development Composer packages include PHPUnit, Mockery, Faker, Laravel Sail, Collision, and Laravel Ignition. They support tests, local tooling, or development diagnostics rather than clinic workflows.

See `composer.json`, `composer.lock`, `package.json`, and `package-lock.json` for the exact declared and locked package versions.

## 7. Integrations and APIs

External integrations are separate services; they are not the same thing as Composer packages or the Admin Hub API.

### Admin Hub and external admin-profile API

PUPOCMS provides a custom Laravel RESTful API for external administrator-profile integration. Relevant routes include:

- `GET /api/external/admins`
- `GET /api/external/admins/options`
- `GET /api/external/admins/{admin_uuid}`
- `GET /api/external/admin-profile`
- `PUT /api/external/admins/{admin_uuid}`

The preferred integration authentication is a generated Laravel Sanctum bearer token with scoped abilities such as `external-admin:read` and `external-admin:update`. Generate or rotate tokens from the Integration Tokens area, assign only the necessary abilities, and share the token through a secure channel. The token is not intended to be retrieved later from the UI; issue a replacement if it is lost.

Example read request (replace placeholders locally; never commit a real token):

```bash
curl -H "Accept: application/json" \
  -H "Authorization: Bearer <INTEGRATION_TOKEN>" \
  -H "X-External-System: <SYSTEM_KEY>" \
  "https://<CLINIC_HOST>/api/external/admins?limit=25"
```

`X-External-System` is optional for Sanctum bearer authentication, but if sent it must match the integration client's system key. The legacy static-key path may instead use its configured external API header and system identifier.

The current middleware also retains a legacy static-key fallback using the `EXTERNAL_ADMIN_PROFILE_API_KEY` / `EXTERNAL_ADMIN_PROFILE_SYSTEM_KEYS` configuration. Keep this compatibility in mind when changing credentials; do not remove the fallback until all clients using it have migrated and been verified.

### Medical status and PUPTAS

The medical-status endpoint is separate from the Admin Hub administrator-profile API:

- `GET /api/external/students/{student_id}/medical-status` requires the `medical-status:read` ability.

PUPTAS applicant lookups and medical-clearance synchronization are implemented separately in `PuptasWebhookService`. PUPTAS uses its own endpoint/client configuration and webhook signing settings. Do not use an Admin Hub integration token as a substitute for PUPTAS credentials, or vice versa.

### Identity, student/faculty data, and AI

- **One Portal IdP:** configurable authorization-code login with PKCE support. Actual enablement and endpoints are environment-specific.
- **GUISIS:** configured external student-system API integration.
- **PUPT FLSS:** configured faculty-profile integration.
- **OpenAI and Gemini:** configured AI providers used by assistant-related workflows. Models and provider selection are environment-configurable; avoid sending sensitive health information to external AI services unless explicitly authorized by policy and configuration.
- **Email:** Laravel Mail is used for application email. The mail transport/provider comes from environment configuration; the repository does not establish which production email account is currently configured.

Configuration is in `config/services.php` and `config/mail.php`. Secret values belong in the hosting environment or protected `.env`, never in this manual.

## 8. Privacy and Security Operations

### Health information and files

- Health fields use a dedicated `HEALTH_DATA_ENCRYPTION_KEY`. The default configured cipher is `AES-256-CBC`, configurable through `HEALTH_DATA_ENCRYPTION_CIPHER`. The dedicated health-data key must be different from `APP_KEY`.
- Private health files use `AES-256-GCM` when health-file encryption is enabled, with a separate `HEALTH_FILES_ENCRYPTION_KEY`.
- The default `health_private` filesystem disk stores private files under `storage/app/private`. File access should go through the application authorization and private-file service, not a public URL.
- Legacy disk fallback/mirroring options exist. Verify the production flags and existing file locations before claiming every historical file is encrypted or private.
- Keep the database, private health-file storage, and their encryption keys backed up securely. Losing a required key can make encrypted data unreadable. Never rotate keys without a tested re-encryption and recovery plan.

### Access, audit, and records

- Use individual accounts and least-privilege module access.
- Review available activity/audit and integration logs when investigating an action. Logging coverage can differ by workflow; do not assume every event is recorded.
- Pull-out changes a record's workflow state and supports restoration. It is not a delete operation.
- Keep health details out of email, toast, and other general notifications unless the workflow requires them.
- Redact personal identifiers, health details, and tokens from screenshots and support reports.

## 9. Local Development Setup

### Requirements

- PHP compatible with `^8.2`, Composer, Node.js/npm compatible with the project's Vite version, and MySQL/MariaDB with PDO MySQL enabled.
- On Windows, XAMPP can provide Apache, PHP, and MariaDB/MySQL-compatible services. Confirm the PHP executable used by the terminal, Apache, and Composer; they can differ.

### Initial setup

Run commands from the project root:

```powershell
composer install
if (-not (Test-Path .env)) { Copy-Item .env.example .env }
php artisan key:generate
npm ci
```

Run `php artisan key:generate` only for a new local environment with no existing `APP_KEY`; never replace the key on an environment containing encrypted data. Set the local database connection and required non-secret settings in `.env`. If health-data or health-file encryption is enabled, configure separate dedicated keys for those systems; do not reuse `APP_KEY`.

Create/select the intended local database, then run:

```powershell
php artisan migrate
php artisan storage:link
npm run dev
php artisan serve
```

Alternatively, configure Apache to serve the Laravel `public/` directory. Never use the repository root as the web document root. Do not run destructive commands such as `migrate:fresh` against a database containing records you need to keep.

## 10. Production Hosting Checklist

AWS/Spaceship instructions are intentionally not part of this manual. The code requires PHP `^8.2`; verify the **website runtime** in the hosting control panel's PHP Info. An SSH/Composer `php -v` result can refer to a different CLI PHP version.

If Hostinger is the production host, verify the correct website/subdomain before applying these steps. The production host cannot be confirmed from repository files alone:

1. Set the website document root to the Laravel `public/` directory, and keep `.env` outside public web access.
2. Create a production database and configure `DB_*` values in the protected environment.
3. Configure the separate health-data and health-file encryption keys, identity provider, PUPTAS, mail, and AI settings that the enabled workflows require. Do not copy local credentials into production.
4. Ensure `storage/` and `bootstrap/cache/` are writable by the PHP process. Keep private health files outside the public disk.
5. Back up the production database, private file storage, and protected encryption keys before deployment or migrations.
6. Build production dependencies/assets using the deployment environment's supported PHP and Node versions.
7. Run reviewed database migrations only after backup and confirmation of the target database.
8. Verify sign-in, student health-form access, appointment workflow, staff review, private document access, inventory, email, and each enabled external integration.

The live hosting PHP version, database host, enabled integrations, and production mail provider are not verifiable from repository files alone. Record those values in a restricted operations record, not in this public-facing manual.

## 11. Routine Maintenance

- Back up the database and private health-file storage on a schedule approved by the clinic/system owner; periodically test restoration.
- Store encryption keys separately from application files and database backups, with access limited to designated maintainers.
- Review failed API requests and integration activity; rotate/revoke exposed or obsolete tokens and confirm connected clients before rotation.
- Apply dependency updates in a test environment first. Review Laravel/PHP compatibility and run automated tests before production deployment.
- Keep logs and exported reports access-controlled; remove sensitive data from debugging output and screenshots.
- Confirm the current data-retention, record correction, and pull-out/restoration procedures with the clinic before changing them. This README does not define a legal retention schedule.

## 12. Tests and Troubleshooting

### Checks

```powershell
php artisan test
npm run production
```

These commands are provided for verification; this manual does not claim that the current test suite has passed on every environment.

### Common checks

| Symptom | Check |
| --- | --- |
| HTTP 500 or database error | Check `storage/logs/laravel.log`, PHP version/extensions, database service and `DB_*` configuration. Redact health data before sharing logs. |
| Encrypted data/file cannot be read | Confirm the matching dedicated key and cipher settings. Do not generate replacement keys over existing encrypted data. |
| API returns 401 | Confirm the client is active and the bearer token is complete and current. |
| API returns 403 | Check token ability, system identity, role, and module permissions. |
| External service unavailable | Check that service is enabled/configured, endpoint is current, and its activity/error log; do not paste secrets into tickets. |
| CSS or JavaScript appears stale | Rebuild assets with `npm run production` and clear only the relevant Laravel caches after checking the deployment environment. |
| Private document access fails | Check the private disk, file-encryption setting/key, file permissions, and whether legacy-file fallback is expected. |

## 13. Important Source Locations

| Concern | Source |
| --- | --- |
| Browser and API routes | `routes/web.php`, `routes/api.php` |
| Business logic | `app/Http/Controllers/`, `app/Services/` |
| Database models | `app/Models/` |
| Schema changes | `database/migrations/` |
| Application configuration | `config/` |
| Student and clinic UI | `resources/views/`, `resources/js/`, `resources/css/` |
| Composer and npm package manifests | `composer.json`, `composer.lock`, `package.json`, `package-lock.json` |

Focused encryption and security-header implementation evidence remains under `docs/`; it is supporting technical evidence, not a second general system manual.
