#!/usr/bin/env sh
set -e
cd /var/www/html

# CONTAINER_ROLE: app | queue. Only "app" bootstraps the database; the queue
# worker waits until migrations exist, then starts processing jobs.
ROLE="${CONTAINER_ROLE:-app}"

wait_for_mysql() {
    echo "Waiting for MySQL at ${DB_HOST}:${DB_PORT}..."
    until php -r "try { new PDO('mysql:host='.getenv('DB_HOST').';port='.getenv('DB_PORT'), getenv('DB_USERNAME'), getenv('DB_PASSWORD')); } catch (Throwable \$e) { exit(1); }" 2>/dev/null; do
        sleep 2
    done
}

wait_for_migrations() {
    echo "Waiting for migrations to complete..."
    until php artisan migrate:status >/dev/null 2>&1; do
        sleep 2
    done
}

wait_for_mysql

if [ "$ROLE" = "app" ]; then
    if ! grep -q "^APP_KEY=base64:" .env 2>/dev/null; then
        php artisan key:generate --force
    fi

    php artisan config:clear
    php artisan migrate --force
    php artisan db:seed --force
else
    wait_for_migrations
fi

exec "$@"
