# STBC-BOR-FINAL

Full-stack setup with:
- `frontend`: Vue 3 + Vite
- `backend`: Laravel 13 + PHP-FPM
- `docker`: Nginx + PHP + Postgres + MinIO orchestration

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
- MinIO API: `http://localhost:9000`
- MinIO Console: `http://localhost:9001`
