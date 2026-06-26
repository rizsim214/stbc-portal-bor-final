# Backend README (STBC)

This backend is a Laravel 13 API for:
- auth + token-based sessions (Sanctum)
- user/role management
- appointment booking
- schedule availability computation
- lab result upload/release/download flow (S3/MinIO signed URLs)

The goal of this README is to explain how the code is structured and where business rules live so future-you can re-orient quickly.

## 1. Tech Stack

- PHP `^8.3`
- Laravel `^13`
- Laravel Sanctum `^4.3`
- PostgreSQL (default in `.env.example`)
- S3-compatible object storage (MinIO in local Docker)

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
2. Frontend uploads directly to S3/MinIO using signed URL.
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
  - `AWS_*` for S3/MinIO

Lab-result-specific config is in `config/lab_results.php`.

## 9. Local Setup

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
- MinIO console: `http://localhost:9001`

MinIO notes for local development:
- create the bucket named `lab-results`
- set `AWS_ENDPOINT=http://localhost:9000` in `backend/.env`
- keep `AWS_USE_PATH_STYLE_ENDPOINT=true`
- the browser uploads directly to the signed MinIO URL, so `http://minio:9000` is not suitable for local browser use even though it is valid inside Docker
- configure MinIO bucket CORS to allow `http://localhost:5173` and `http://127.0.0.1:5173`

Recommended local MinIO CORS policy:

```json
[
  {
    "AllowedOrigins": [
      "http://localhost:5173",
      "http://127.0.0.1:5173"
    ],
    "AllowedMethods": [
      "GET",
      "PUT",
      "HEAD"
    ],
    "AllowedHeaders": [
      "*"
    ],
    "ExposeHeaders": [
      "ETag"
    ],
    "MaxAgeSeconds": 3600
  }
]
```

Staging/production env guidance:
- update `AWS_ACCESS_KEY_ID`, `AWS_SECRET_ACCESS_KEY`, `AWS_DEFAULT_REGION`, `AWS_BUCKET`, and `AWS_ENDPOINT`
- keep `LAB_RESULTS_STORAGE_DISK=s3`
- set `CORS_ALLOWED_ORIGINS` to the deployed frontend origins
- if using MinIO outside local Docker, the endpoint must still be browser-reachable because uploads and downloads use signed URLs directly from the client

Queue worker (Docker):
- A dedicated `queue-worker` service runs `php artisan queue:work` with recycling flags for long-lived process stability:
  - `--memory=${QUEUE_WORKER_MEMORY:-256}`
  - `--max-jobs=${QUEUE_WORKER_MAX_JOBS:-1000}`
  - `--max-time=${QUEUE_WORKER_MAX_TIME:-3600}`

Reverb (Docker):
- A dedicated `reverb` service runs `php artisan reverb:start` on port `8080`.
- Backend publishing uses `REVERB_HOST`, `REVERB_PORT`, and `BROADCAST_CONNECTION=reverb`.
- The browser websocket client uses `VITE_REVERB_APP_KEY`, `VITE_REVERB_HOST`, `VITE_REVERB_PORT`, and `VITE_REVERB_SCHEME`.

Render backend deploy:
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
  - `php artisan queue:restart`
- suggested Render commands:
  - Build command: `composer install --no-dev --optimize-autoloader`
  - Pre-deploy command: `composer run render:deploy`
  - Start command: `php artisan serve --host=0.0.0.0 --port=$PORT`

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
