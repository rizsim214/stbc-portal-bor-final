# Frontend README (STBC)

Frontend app is built with Vue 3 + TypeScript + Vite.

## Auth Flow

Authentication is login/logout only.

- Available guest auth route:
  - `/login`
- Removed/disabled:
  - `/register`
  - `/forgot-password`

Auth API usage in frontend:
- `POST /api/auth/login`
- `POST /api/auth/logout`

Session behavior:
- On successful login, token/user are stored in localStorage.
- Logout clears session and revokes the current token server-side.
