#!/bin/sh
set -e

cd "$(dirname "$0")/.."
ASAL="$PWD"

NAMA="sicasn-rilis-$(date +%Y%m%d-%H%M%S)"
HASIL="${1:-$ASAL/$NAMA.zip}"
TEMP="$(mktemp -d)"
RILIS="$TEMP/sicasn"

trap 'rm -rf "$TEMP"' EXIT

echo "Membangun aset frontend"
npm ci --ignore-scripts
npm run build

echo "Menyusun salinan bersih"
mkdir -p "$RILIS"
for berkas in app bootstrap config database public resources routes src artisan composer.json composer.lock .env.example; do
    cp -R "$ASAL/$berkas" "$RILIS/"
done
rm -rf "$RILIS/public/hot" "$RILIS/bootstrap/cache"
mkdir -p "$RILIS/bootstrap/cache" "$RILIS/storage/logs" "$RILIS/storage/framework/cache" "$RILIS/storage/framework/sessions" "$RILIS/storage/framework/views" "$RILIS/storage/app/private"

echo "Memasang pustaka PHP untuk produksi di salinan"
(cd "$RILIS" && composer install --no-dev --optimize-autoloader --classmap-authoritative --no-interaction)

echo "Mengemas"
(cd "$TEMP" && zip -qr "$HASIL" sicasn)

echo "Paket rilis: $HASIL"
