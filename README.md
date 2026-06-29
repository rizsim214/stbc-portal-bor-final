# STBC-BOR-FINAL

Full-stack setup with:
- `frontend`: Vue 3 + Vite
- `backend`: Laravel 13 + PHP-FPM
- `docker`: Nginx + PHP + Postgres + Redis + Reverb orchestration

## Dockerfiles

The backend now uses two separate Dockerfiles:
- `backend/Dockerfile.local`: local Docker Compose development with PHP-FPM behind Nginx
- `backend/Dockerfile.render`: Render deployment using `php artisan serve`

Local `docker compose` uses `backend/Dockerfile.local`.
Render should be configured to use `backend/Dockerfile.render`.

## Auth Model

- Authentication is `login/logout` only.
- Public registration is disabled.
- Forgot-password flow is disabled.
- Main auth endpoints:
  - `POST /api/auth/login`
  - `POST /api/auth/logout`

## Quick Start

1. Copy root env:
   - `cp .env.example .env` (or create `.env` manually on Windows)
2. Copy backend env:
   - `cp backend/.env.example backend/.env`
3. Generate Laravel key:
   - `docker compose run --rm app php artisan key:generate`
4. Run migrations:
   - `docker compose run --rm app php artisan migrate`
5. Start stack:
   - `docker compose up -d`

## Render Environments

Use the existing `.env.example` files for local Docker development.

Use these Render-specific examples when deploying hosted services:
- `backend/.env.render.staging.example`
- `backend/.env.render.production.example`
- `frontend/.env.render.staging.example`
- `frontend/.env.render.production.example`

Recommended mapping on Render:
- Backend web service: copy values from the matching `backend/.env.render.*.example`
- Reverb service: reuse the same backend env file for the same environment
- Frontend static site or web service: copy values from the matching `frontend/.env.render.*.example`

Switching environments should only require swapping the staging file set for the production file set and then replacing the placeholder hosts, passwords, keys, and domains with the real Render values.

## URLs

- Frontend: `http://localhost:5173`
- Backend (Nginx/Laravel): `http://localhost:8000`
- Backend health: `http://localhost:8000/up`
- API health: `http://localhost:8000/api/health`

## Lab Result Storage

Lab-result files now use Supabase Storage over its S3-compatible endpoint instead of the local MinIO container.

Set the following values in `backend/.env`:
- `SUPABASE_STORAGE_ACCESS_KEY_ID`
- `SUPABASE_STORAGE_SECRET_ACCESS_KEY`
- `SUPABASE_STORAGE_REGION`
- `SUPABASE_STORAGE_BUCKET`
- `SUPABASE_STORAGE_ENDPOINT`
- `LAB_RESULTS_STORAGE_DISK=supabase`

Use the direct storage hostname for signed browser uploads, for example:
- `SUPABASE_STORAGE_ENDPOINT=https://<project-ref>.storage.supabase.co/storage/v1/s3`
