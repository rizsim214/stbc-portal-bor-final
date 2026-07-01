#!/usr/bin/env sh

set -eu

export PORT="${PORT:-10000}"

php artisan optimize:clear --ansi
php artisan migrate --force --ansi
php artisan db:seed --force --ansi

if php artisan storage:link --ansi; then
  echo "Storage link is ready."
else
  echo "storage:link skipped; link may already exist."
fi

php artisan config:cache --ansi
php artisan route:cache --ansi
php artisan event:cache --ansi
php artisan view:cache --ansi

envsubst '${PORT}' < /etc/nginx/templates/render.conf.template > /etc/nginx/sites-available/default

exec /usr/bin/supervisord -c /etc/supervisor/conf.d/supervisord.conf
