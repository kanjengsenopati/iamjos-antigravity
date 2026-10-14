#!/bin/sh
set -e

# Ensure writable directories
mkdir -p storage/framework/cache/data storage/framework/sessions storage/framework/views storage/logs bootstrap/cache
chmod -R 775 storage bootstrap/cache 2>/dev/null || true

# Auto-link storage if missing
if [ ! -L public/storage ] && [ ! -d public/storage ]; then
    php artisan storage:link || true
fi

# Auto-create SQLite database file if configured and missing
if [ "${DB_CONNECTION}" = "sqlite" ] && [ -n "${DB_DATABASE}" ] && [ "${DB_DATABASE}" != ":memory:" ]; then
    mkdir -p "$(dirname "${DB_DATABASE}")"
    if [ ! -f "${DB_DATABASE}" ]; then
        touch "${DB_DATABASE}"
        chmod 664 "${DB_DATABASE}" 2>/dev/null || true
    fi
fi

# Run database migrations if RUN_MIGRATIONS=true
if [ "${RUN_MIGRATIONS}" = "true" ]; then
    echo "Running database migrations..."
    php artisan migrate --force
fi

# Lock installer if AUTO_LOCK_INSTALLER=true
if [ "${AUTO_LOCK_INSTALLER}" = "true" ] && [ ! -f storage/installed ]; then
    echo "Auto-locking installer..."
    date > storage/installed
fi

# Execute CMD
exec "$@"
