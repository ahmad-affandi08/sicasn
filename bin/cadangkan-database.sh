#!/bin/sh
set -e
umask 077

cd "$(dirname "$0")/.."

TUJUAN="${1:-$PWD/cadangan}"
SIMPAN_HARI="${2:-14}"

ambil() {
    grep -E "^$1=" .env | head -n 1 | cut -d '=' -f 2- | sed -e 's/^"//' -e 's/"$//'
}

HOST="$(ambil DB_HOST)"
PORT="$(ambil DB_PORT)"
BASIS="$(ambil DB_DATABASE)"
PENGGUNA="$(ambil DB_USERNAME)"
SANDI="$(ambil DB_PASSWORD)"

mkdir -p "$TUJUAN"
BERKAS="$TUJUAN/sicasn-$(date +%Y%m%d-%H%M%S).sql.gz"

MYSQL_PWD="$SANDI" mysqldump --single-transaction --no-tablespaces --default-character-set=utf8mb4 -h "$HOST" -P "${PORT:-3306}" -u "$PENGGUNA" "$BASIS" | gzip > "$BERKAS"
chmod 600 "$BERKAS"

find "$TUJUAN" -name 'sicasn-*.sql.gz' -mtime "+$SIMPAN_HARI" -delete

echo "Cadangan tersimpan: $BERKAS"
