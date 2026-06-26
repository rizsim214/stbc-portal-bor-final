#!/usr/bin/env bash

set -euo pipefail

SCRIPT_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")" && pwd)"
APP_DIR="$(cd "${SCRIPT_DIR}/.." && pwd)"

cd "${APP_DIR}"

echo "Starting Laravel deploy tasks for Render..."

if [[ ! -f artisan ]]; then
  echo "artisan not found in ${APP_DIR}" >&2
  exit 1
fi

php artisan optimize:clear --ansi

if php artisan storage:link --ansi; then
  echo "Storage link is ready."
else
  echo "storage:link skipped; link may already exist."
fi

php artisan migrate --force --ansi
php artisan config:cache --ansi
php artisan route:cache --ansi
php artisan event:cache --ansi
php artisan view:cache --ansi

if php artisan queue:restart --ansi; then
  echo "Queue restart signal sent."
else
  echo "queue:restart skipped; cache backend may not be ready yet."
fi

echo "Render deploy tasks completed."
