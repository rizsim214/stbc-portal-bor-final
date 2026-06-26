#!/usr/bin/env sh

set -eu

php artisan optimize:clear --ansi
php artisan migrate:fresh --seed --ansi


if php artisan storage:link --ansi; then
  echo "Storage link is ready."
else
  echo "storage:link skipped; link may already exist."
fi

php artisan config:cache --ansi
php artisan route:cache --ansi
php artisan event:cache --ansi
php artisan view:cache --ansi

exec php artisan serve --host=0.0.0.0 --port="${PORT:-10000}"
