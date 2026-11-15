#!/usr/bin/env bash
#
# uninstall-package.sh — copot TOTAL pasax/secure-monitor dari app uji ini untuk pengujian.
#
# Menghapus: tabel DB + record migrasi Pasax, rute, middleware, trait User,
# variabel PASAX_* (.env), .htaccess Pasax, file publish (config, view,
# migrasi), file *.backup-*, lalu melepas paket via Composer.
#
# Cara pakai:
#   ./uninstall-package.sh                 # copot total + migrasi + composer install
#   ./uninstall-package.sh --keep-tables   # pertahankan tabel DB (diteruskan)
#   ./uninstall-package.sh --keep-env      # pertahankan variabel .env (diteruskan)
#
# Pasangan: ./install-package.sh (pasang ulang) — siklus uji:
#   ./uninstall-package.sh && ./install-package.sh

set -euo pipefail

cd "$(dirname "$0")"

echo "== [1/5] Menjalankan pasax:uninstall (dilewati bila paket belum terpasang) =="
if php artisan list --raw 2>/dev/null | grep -q "^pasax:uninstall"; then
    php artisan pasax:uninstall --force --no-interaction "$@"
else
    echo "-- perintah pasax:uninstall tidak ada, paket belum terpasang."
fi

echo "== [2/5] Sanity check bootstrap/app.php =="
php -l bootstrap/app.php

echo "== [3/5] Migrasi database =="
php artisan migrate --force

echo "== [4/5] Memastikan paket lepas + dependensi utuh =="
if composer show pasax/secure-monitor >/dev/null 2>&1; then
    composer remove pasax/secure-monitor --no-interaction || true
fi
composer install --no-interaction

echo "== [5/5] Membersihkan cache framework =="
php artisan optimize:clear

echo "OK: Pasax tercopot total. Sisa yang diharapkan NOL:"
grep -rc "^PASAX_" .env .env.example 2>/dev/null || true
