# AutoCare Hub

AutoCare Hub is a single-garage vehicle maintenance system built with a PHP MVC structure. The application entry point is `public/index.php`; the root `index.php` is a compatibility wrapper for Laragon.

## Phase 1 — Requirements, ERD, and database

Implemented in `database/schema.sql` and `database/ERD.md`:

- The 17 agreed tables, foreign keys, unique constraints, indexes, and MySQL 8 checks.
- Vehicle/customer ownership relationships, one active appointment per exact time slot, appointment price/duration snapshots, and one-to-one ticket/invoice constraints.
- Multiple payments per invoice and idempotent provider webhook event identifiers linked to internal payments.
- Zero-tax invoice constraints per the agreed rule.
- Vehicle-brand and service-catalog reference data only; no fake customer or transaction history and no seeded Admin credentials.

This SQL is the proposed clean-install schema, not the current `autocare_hub` schema. The live database uses different column names; its tables were empty when last inspected. **Do not import this SQL into the existing database**; current development uses the existing table/column names until a separately reviewed migration is approved.

## Phase 2 — MVC Core

Implemented:

- Namespace-based autoloading for `App\` classes.
- MVC source organization: controllers, models, middleware, core services, helpers, and views live under `app/`.
- Web routes live in `routes/web.php`; application settings live in `config/`; public static files live in `public/`.
- Public homepage images live in `public/assets/images/`; user-upload storage remains outside the public directory in `assets/uploads/`.
- A small GET/POST router with path parameters, middleware support, 404, and 405 responses.
- Request/response objects, a base controller, and view rendering.
- PDO MySQL connection configuration for `utf8mb4`, exception reporting, and native prepared statements.
- A transaction helper, validation primitives, environment loading, session cookie hardening, and a shared HTML-escaping helper.
- A public document root with Apache rewrite rules and a root-entry compatibility wrapper.
- `.env.example` and root `.gitignore`.

## Phase 3 — Registration, login, and authorization

Implemented against the existing `autocare_hub.users` schema, without database changes:

- Customer-only registration using `users.password`, `role`, and `status`.
- Password hashing and verification, session ID rotation, status checks, and role-based route middleware.
- CSRF-protected registration, login, and logout forms.
- Profile updates, password changes, and one-hour password-reset links with one-time token use.
- A one-time Admin CLI that prompts for a password and refuses to create another Admin.

Set `DB_NAME=autocare_hub` and the local MySQL credentials in `.env`. Create the first Admin interactively from the project root:

```powershell
& 'D:\laragon\bin\php\php-8.3.30-Win32-vs16-x64\php.exe' bin\create-admin.php
```

The command does not print or store a default password. Customer registration always assigns the `customer` role.

## Phase 4 — Homepage, customer vehicles, and customer administration

Implemented against the existing database schema:

- Responsive AutoCare Hub landing page with navigation, service/process/contact sections, and live service catalog data from MySQL.
- An explicit empty state when the database has no active services; no sample services or garage contact details are substituted.
- Garage phone/address/hours can be configured with `GARAGE_PHONE`, `GARAGE_ADDRESS`, and `GARAGE_HOURS` in `.env`.
- Customers can list, add, update, and remove their own vehicles. Removal is blocked when the vehicle has any appointment/history reference.
- Admins can manage vehicle brands and model catalog entries, view customers and registered vehicles, and lock/unlock customer accounts.
- Customer vehicle reads and writes are scoped to the signed-in account; customer/admin pages require their respective roles and POST forms require CSRF.
- Admins can maintain vehicle brands and model catalog entries. New vehicle registrations load only active, verified models; models created by an Admin are verified at creation, while imported NHTSA models wait for review. Vehicle-model records are protected from deletion once referenced by a customer vehicle.
- The additive catalog migration is `database/create_vehicle_catalog.sql`. Apply it once to the existing `autocare_hub` database before deploying this feature; it creates `vehicle_models` and adds a nullable `vehicles.vehicle_model_id` foreign key without replacing existing vehicle records. Do not import `database/schema.sql`.
- Admins can import vehicle makes/models from the NHTSA vPIC API at `/admin/vehicle-import`. Imports use bounded, resumable batches, record source IDs and verification status, and leave existing names, images, and statuses unchanged. The source includes non-passenger manufacturers; review the catalog before relying on it as a passenger-car-only list.
- Before applying `database/migrations/20261112_vehicle_catalog_import.sql`, make and verify a database backup. This additive, rerunnable migration adds provenance fields and import history without renaming existing columns or changing catalog foreign keys. `database/migrations/20261112_vehicle_catalog_import_rollback.sql` deliberately refuses rollback once source IDs or import history exist; retain data and use a forward migration instead of dropping populated metadata.
- The NHTSA review queue is available at `/admin/vehicle-import/review`. It supports searching, filtering, pagination, and individual or bulk verification decisions; only NHTSA records can be reviewed there. Before deploying, back up the live database and apply `database/migrations/20261113_vehicle_model_review.sql` to add reviewer/time audit fields and the review-queue index without changing existing model statuses. The guarded rollback refuses once review decisions or audit records exist.
- Customer model lookup remains local (`GET /vehicle-models?brand_id=...&q=...`); the browser does not call NHTSA directly.
- Customers can submit on-site or online-support appointment requests at `/appointments/create` and view their own appointment history. Requests use the existing `appointments` and `appointment_details` tables, snapshot selected service names/prices, and remain pending until the garage responds. The requested date/time is not represented as an available slot because the live database has no schedule/capacity rules. Back up `autocare_hub` and apply `database/migrations/20261112_appointment_booking_type.sql` before deploying; its guarded rollback refuses while online appointments exist.
- Admins can manage requests at `/admin/appointments`, with search, status/date filters, pagination, and guarded transitions from pending through confirmation, intake, completion, rejection, or cancellation. Rejection reasons use the existing `appointments.admin_note`; `updated_at` updates automatically. No schema change was needed. The appointments table has no Admin-actor audit field, so the acting Admin is not recorded.

Service-catalog administration and repair-ticket workflows have not been implemented yet. Admins can see the status of an existing linked repair ticket from appointment details, but this feature does not create or manage repair tickets. The live service catalog is currently empty, so booking pages show an honest no-services state rather than seeded or fabricated entries.

## Laragon setup

1. Start Apache and MySQL in Laragon.
2. The existing `autocare_hub` database is retained. Do not import `database/schema.sql` into it; it documents a different proposed schema.
3. Copy `.env.example` to `.env`, set `DB_NAME=autocare_hub`, and configure local MySQL credentials and the URL.
4. Run `database/create_password_resets.sql` against `autocare_hub` to add the password-reset token table. This is separate from the proposed `database/schema.sql`.
5. Apply `database/create_vehicle_catalog.sql` once to add the model catalog and nullable vehicle-model reference; it does not recreate or delete existing data.
6. Before enabling customer appointment requests, back up the live database and apply `database/migrations/20261112_appointment_booking_type.sql`; it adds only the booking-type field to the existing appointments table.
7. For deployed environments, set `APP_ENV=production`, configure a valid `MAIL_FROM`, and configure PHP's `mail()` transport. In `APP_ENV=local`, submitting an active account email redirects directly to the password reset form for development only.
8. Configure the web server document root to `DriveHub/public`. Alternatively, visit `http://localhost/DriveHub/public/` when using Laragon's default document root.
9. Ensure PHP can write to `storage/logs` and `public/assets/images/vehicle-catalog`.

The root `index.php` and root `.htaccess` support Laragon's folder-based local URL. For deployment, use `public/` as the document root; the root rules also deny direct requests to application source and configuration paths.

## Checks

From the project root, lint the PHP source with the PHP executable selected in Laragon:

```powershell
Get-ChildItem -Recurse -Filter *.php | ForEach-Object { & 'D:\laragon\bin\php\php-8.3.30-Win32-vs16-x64\php.exe' -l $_.FullName }
```

Open the public URL and confirm the AutoCare Hub page loads. Verify registration creates only a customer and login/logout enforce CSRF. Unknown paths should return 404, unsupported methods on a known route should return 405, unauthenticated protected routes should redirect to login, and a customer requesting `/admin` should receive 403.
