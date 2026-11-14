#!/bin/sh
# Läuft vor 50-laravel-automations (serversideup /etc/entrypoint.d, als www-data).
#
# Das Volume für storage/ kann leer und root-eigen sein, wenn Coolify es angelegt hat,
# bevor das Image es befüllen konnte. Dann scheitert "artisan optimize" mit einem
# kryptischen tempnam()-Fehler. Hier: fehlende Ordner anlegen oder klar abbrechen.

STORAGE=/var/www/html/storage

if [ ! -w "$STORAGE" ]; then
    echo "❌ $STORAGE is not writable for $(id -un) (uid $(id -u))." >&2
    echo "   Fix once on the server (replace <volume> with the app-storage volume):" >&2
    echo "   docker run --rm -u root -v <volume>:/s --entrypoint sh <image> -c 'chown -R $(id -u):$(id -g) /s'" >&2
    exit 1
fi

mkdir -p \
    "$STORAGE/app/public" \
    "$STORAGE/app/private" \
    "$STORAGE/framework/cache/data" \
    "$STORAGE/framework/sessions" \
    "$STORAGE/framework/views" \
    "$STORAGE/logs"
