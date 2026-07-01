# Backend README (STBC)

This backend is a Laravel 13 API for:
- auth + token-based sessions (Sanctum)
- user/role management
- appointment booking
- schedule availability computation
- lab result upload/release/download flow (Supabase Storage signed URLs)

The goal of this README is to explain how the code is structured and where business rules live so future-you can re-orient quickly.

## 1. Tech Stack

- PHP `^8.3`
- Laravel `^13`
- Laravel Sanctum `^4.3`
- PostgreSQL (default in `.env.example`)
- Supabase Storage via its S3-compatible endpoint

Main dependencies are in `composer.json`.

## 2. High-Level Architecture

This codebase follows a modular pattern under `app/Modules/*`:

- `Requests/`: validation + mapping request data into DTOs
- `DTOs/`: typed input objects for actions/services
- `Actions/`: business use-cases (one class per operation)
- `Controllers/`: thin transport layer
- `Services/`: reusable domain services (scheduling, file URL signing)
- `routes.php`: per-module route definitions

Global API routes are loaded in `routes/api.php` and module routes are auto-discovered with:

`glob(app_path('Modules/*/routes.php'))`

## 3. Request Lifecycle (How a feature usually works)

1. Route points to a controller method.
2. FormRequest validates input.
3. FormRequest converts input to a DTO.
4. Controller calls an Action with that DTO.
5. Action runs domain logic and returns model/data.
6. Controller returns JSON response.
7. Exceptions are normalized in `bootstrap/app.php`.

This keeps controllers simple and business rules testable.

## 4. Modules Overview

### Auth (`app/Modules/Auth`)

Endpoints:
- `POST /api/auth/login`
- `POST /api/auth/logout` (auth required)

Key rules:
- Public self-registration is disabled.
- Login uses email/password and returns Sanctum token.
- Logout deletes current access token only.

### Users + Roles (`app/Modules/Users`)

Endpoints (auth required):
- `GET /api/roles` (`can:manage-roles`)
- `POST /api/users` (`can:manage-users`)
- `PATCH /api/users/{user}/role` (`can:manage-users`)

Key rules:
- `AdminRegisterUserAction` and request validation enforce a simple role assignment flow.
- `AssignRoleAction` updates the target user's role directly.

### Scheduling (`app/Modules/Scheduling`)

Endpoint:
- `GET /api/scheduling/availability`

Core logic is in `SchedulingService`:
- intersects schedules across all requested resources
- subtracts schedule exceptions (resource-level and global)
- splits intervals into 30-minute slots
- filters out slots that conflict with existing appointments

Important: availability is for the intersection of all selected resources, not union.

### Appointments (`app/Modules/Appointments`)

Endpoint:
- `POST /api/appointments`

`CreateAppointment`:
- verifies slot availability through `SchedulingService::isAvailable`
- writes appointment + pivot links to resources in a transaction
- throws `422 APPOINTMENT_SLOT_UNAVAILABLE` when slot is not bookable

### Lab Results (`app/Modules/LabResults`)

Endpoints (auth required):
- `GET /api/lab-results`
- `POST /api/lab-results/upload-url` (`can:upload-lab-results`)
- `POST /api/lab-results` (`can:upload-lab-results`)
- `GET /api/lab-results/{labResult}` (`can:view-lab-result,labResult`)
- `GET /api/lab-results/{labResult}/file-url` (`can:view-lab-result,labResult`)
- `PATCH /api/lab-results/{labResult}/release` (`can:release-lab-results`)

Flow:
1. Request signed upload URL with file metadata.
2. Frontend uploads directly to Supabase Storage using the signed URL.
3. Frontend stores lab result record with returned `file_key`.
4. Authorized users generate temporary download URL when needed.

Key rules:
- exactly one lab result per appointment (enforced in action logic)
- users only see released results tied to their own appointments
- `file_path` is kept as DB column, but API now prefers input field name `file_key` (backward compatible)

## 5. Authorization Model

Authorization is gate-based in `app/Providers/AppServiceProvider.php`.

Defined gates:
- `manage-users`
- `manage-roles`
- `upload-lab-results`
- `release-lab-results`
- `view-lab-result`

Role checks are string-based (`$user->role?->name`), so role seed data matters.

## 6. Data Model (Core Tables)

Primary tables:
- `users` (+ `role_id`)
- `roles` (unique `name`)
- `appointments`
- `appointment_types`
- `resources`
- `resource_schedules`
- `schedule_exceptions`
- `appointment_resources` (pivot)
- `lab_results`
- `personal_access_tokens` (Sanctum)

Relationship highlights:
- user has many appointments
- appointment belongs to user + appointment type
- appointment belongs to many resources
- appointment has one lab result
- resource has many schedules
- schedule exceptions can be global (`resource_id = null`) or per-resource

## 7. Error Response Shape

Custom API exceptions inherit `ApiException` and are rendered in `bootstrap/app.php` as:

```json
{
  "message": "...",
  "error_code": "...",
  "context": {}
}
```

Other normalized responses include:
- validation errors (`VALIDATION_ERROR`, with `errors`)
- auth (`UNAUTHENTICATED`)
- authorization (`FORBIDDEN`)
- not found (`RESOURCE_NOT_FOUND`)
- fallback 500 (`SERVER_ERROR`)

## 8. Environment and Config Notes

Important env/config:
- DB: `DB_*` (default pgsql)
- Auth: `AUTH_GUARD=sanctum`
- CORS: `CORS_ALLOWED_ORIGINS`
- Sanitized Sanctum SPA settings:
  - `SANCTUM_STATEFUL_DOMAINS`
  - `SANCTUM_ROUTES=false` for API-only mode
- Initial admin seed:
  - `INITIAL_ADMIN_NAME`
  - `INITIAL_ADMIN_EMAIL`
  - `INITIAL_ADMIN_PASSWORD`
- Lab result storage:
  - `LAB_RESULTS_STORAGE_DISK`
  - `LAB_RESULTS_UPLOAD_URL_TTL_MINUTES`
  - `LAB_RESULTS_DOWNLOAD_URL_TTL_MINUTES`
  - `LAB_RESULTS_MAX_FILE_SIZE_BYTES`
  - `SUPABASE_STORAGE_*` for Supabase Storage

Lab-result-specific config is in `config/lab_results.php`.

## 9. Local Setup

Dockerfile split:
- `Dockerfile.local`: local Docker Compose image with PHP-FPM on port `9000`
- `Dockerfile.render`: Render image using `nginx + php-fpm + reverb` behind Render's public port

From repo root (Docker path, recommended):

1. Copy env files
   - `.env.example` -> `.env`
   - `backend/.env.example` -> `backend/.env`
2. Generate app key
   - `docker compose run --rm app php artisan key:generate`
3. Run migrations/seeds
   - `docker compose run --rm app php artisan migrate --seed`
4. Start services
   - `docker compose up -d`

Useful URLs:
- API health: `http://localhost:8000/api/health`
- Laravel health: `http://localhost:8000/up`
- Reverb websocket: `ws://localhost:8080`

Supabase Storage notes:
- create the `lab-results` bucket in Supabase Storage
- generate S3 access keys in Supabase Storage settings
- use the direct storage hostname for better browser upload performance:
  `https://<project-ref>.storage.supabase.co/storage/v1/s3`
- keep path-style addressing enabled
- ensure your bucket/object access policies allow the intended server-side signing flow

Staging/production env guidance:
- update `SUPABASE_STORAGE_ACCESS_KEY_ID`, `SUPABASE_STORAGE_SECRET_ACCESS_KEY`, `SUPABASE_STORAGE_REGION`, `SUPABASE_STORAGE_BUCKET`, and `SUPABASE_STORAGE_ENDPOINT`
- keep `LAB_RESULTS_STORAGE_DISK=supabase`
- set `CORS_ALLOWED_ORIGINS` to the deployed frontend origins
- the storage endpoint must remain browser-reachable because uploads and downloads use signed URLs directly from the client

Reverb (Docker):
- A dedicated `reverb` service runs `php artisan reverb:start` on port `8080`.
- Backend publishing uses `REVERB_HOST`, `REVERB_PORT`, and `BROADCAST_CONNECTION=reverb`.
- The browser websocket client uses `VITE_REVERB_APP_KEY`, `VITE_REVERB_HOST`, `VITE_REVERB_PORT`, and `VITE_REVERB_SCHEME`.

Guest first-appointment email:
- The only live account email flow currently wired in code is the guest first-appointment account email.
- It is sent from `CreateGuestBookAppointment` using the `GuestAppointmentAccountCreated` mailable.
- The guest appointment API response currently also includes the generated `temporary_password` for hobby-project convenience.
- Admin-created user accounts do not send temporary-password emails yet.
- For real delivery with Gmail, set:
  - `MAIL_MAILER=smtp`
  - `MAIL_SCHEME=tls`
  - `MAIL_HOST=smtp.gmail.com`
  - `MAIL_PORT=587`
  - `MAIL_USERNAME=<your-gmail-address@gmail.com>`
  - `MAIL_PASSWORD=<your-google-app-password>`
  - `MAIL_FROM_ADDRESS=<your-gmail-address@gmail.com>`
- Gmail requires 2-Step Verification plus a Google App Password. Regular Gmail account passwords will not work.

Render backend deploy:
- Dockerfile path: `backend/Dockerfile.render`
- deployment script: `scripts/render-deploy.sh`
- Composer alias: `composer run render:deploy`
- tasks included:
  - `php artisan optimize:clear`
  - `php artisan storage:link`
  - `php artisan migrate --force`
  - `php artisan config:cache`
  - `php artisan route:cache`
  - `php artisan event:cache`
  - `php artisan view:cache`
- Render web service settings:
  - Environment: `Docker`
  - Root directory: `backend`
  - Dockerfile path: `Dockerfile.render`
  - Docker build context: `backend`
  - Pre-deploy command: none
  - Start command: handled by `Dockerfile.render`

Single-service Reverb on Render:
- Render exposes only one public port for the web service.
- `nginx` listens on Render's `PORT`.
- `php-fpm` handles Laravel requests internally on `127.0.0.1:9000`.
- Reverb runs internally on `127.0.0.1:8080`.
- `nginx` proxies websocket paths `/app` and `/apps` to the internal Reverb process.

Recommended backend env values on Render:
- `APP_ENV=production`
- `APP_DEBUG=false`
- `APP_URL=https://<your-backend-service>.onrender.com`
- `LOG_CHANNEL=stack`
- `LOG_STACK=stderr`
- `BROADCAST_CONNECTION=reverb`
- `QUEUE_CONNECTION=sync`
- `REVERB_APP_ID=<shared-app-id>`
- `REVERB_APP_KEY=<shared-app-key>`
- `REVERB_APP_SECRET=<shared-app-secret>`
- `REVERB_SERVER_HOST=0.0.0.0`
- `REVERB_SERVER_PORT=8080`
- `REVERB_HOST=<your-backend-service>.onrender.com`
- `REVERB_PORT=443`
- `REVERB_SCHEME=https`
- `REVERB_ALLOWED_ORIGINS=https://<your-frontend-site>`
- `SANCTUM_STATEFUL_DOMAINS=<your-frontend-site-host-without-https>`
- `CORS_ALLOWED_ORIGINS=https://<your-frontend-site>`

Recommended frontend env values on Render:
- `VITE_API_BASE_URL=https://<your-backend-service>.onrender.com/api`
- `VITE_REVERB_APP_KEY=<same-shared-app-key>`
- `VITE_REVERB_HOST=<your-backend-service>.onrender.com`
- `VITE_REVERB_PORT=443`
- `VITE_REVERB_SCHEME=https`

Render deployment checklist:
1. Deploy the backend web service from `backend/Dockerfile.render`.
2. Set all backend env vars from `backend/.env.render.production.example`.
3. Deploy the frontend static site and set env vars from `frontend/.env.render.production.example`.
4. Replace the placeholder backend domain in both backend and frontend env values.
5. Keep the backend Reverb host equal to the backend public Render domain, not a separate websocket host.
6. Use the same `REVERB_APP_KEY` value on backend and frontend.

## 10. Testing

Run tests:

```bash
php artisan test
```

Tests include module-focused feature/unit coverage under `tests/Feature/Modules` and `tests/Unit/Modules`.

## 11. Known Gotchas

1. Role naming consistency is critical.
Current backend access control now treats `admin` as the management role and `user` as the account used for booking and viewing results.
Lab result management is admin-only, while users can view only their own released results.

2. Lab result uniqueness is enforced in application logic, not DB unique index.
Concurrent requests could still race in edge cases; DB-level unique on `lab_results.appointment_id` would make this stricter.

3. `appointment_type_id` is required in availability requests but currently not used in scheduling calculations.
If appointment-type-specific rules are needed, this is an extension point.

## 12. Where to Change Things Quickly

- Add endpoint in existing domain: edit that module's `routes.php` + new `Request/DTO/Action/Controller`.
- Change permissions: update gates in `AppServiceProvider`.
- Change slot duration/algorithm: `SchedulingService`.
- Change lab file URL behavior/storage disk: `LabResultFileUrlService` + `config/lab_results.php`.
- Change seeded defaults: `database/seeders/*`.
