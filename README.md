# STBC-BOR-FINAL

Full-stack setup with:
- `frontend`: Vue 3 + Vite
- `backend`: Laravel 13 + PHP-FPM
- `docker`: Nginx + PHP + Postgres + Redis + Reverb orchestration

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
