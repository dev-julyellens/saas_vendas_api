#!/bin/sh
set -e

# Volume Docker em storage/ inicia vazio e como root; PHP-FPM workers usam www.
if [ "$(id -u)" = "0" ]; then
    mkdir -p \
        storage/app/public \
        storage/app/private \
        storage/framework/cache/data \
        storage/framework/sessions \
        storage/framework/views \
        storage/framework/testing \
        storage/logs \
        bootstrap/cache

    chown -R www:www storage bootstrap/cache 2>/dev/null || true
    chmod -R 775 storage bootstrap/cache 2>/dev/null || true
fi

exec "$@"
