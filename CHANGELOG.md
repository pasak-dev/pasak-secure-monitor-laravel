# Changelog

All notable changes to `robyajo/pasak` will be documented in this file.

## [1.7.1] - 2026-11-09

### Removed

- **Pembersihan Direktori Dokumentasi Standalone Lama (`web-docs/` & `finis.sh`)**:
  - Menghapus folder `web-docs/` (Astro Starlight) seutuhnya (~462 MB) dan berkas pembungkus `finis.sh`.
  - Seluruh 29 modul dokumentasi teknis `pasak-security-monitor` telah dialihkan permanen ke **Pasak Cloud SOC** (`https://pasak.is-a.dev/docs`) bertenaga **Neon Serverless PostgreSQL Database**.
  - Membersihkan referensi `web-docs` dan `finis.sh` dari `composer.json`, `.gitattributes`, `push.sh`, dan `AGENTS.md`.

## [1.7.0] - 2026-11-09

### Added

- **Jembatan IPC Unix Domain Socket ke Pasak Sentinel Host Daemon (`PasakSentinelBridge`)** — menambahkan jembatan komunikasi performa tinggi (< 0.2ms) antara aplikasi Laravel dan daemon `pasak-sentinel` melalui socket Unix (`/run/pasak/agent.sock` atau `/tmp/pasak/agent.sock`).
- **Delegasi Pemblokiran IP Otomatis ke Kernel Firewall Linux (`nftables` / `iptables`)** — setiap pemblokiran IP yang dipicu oleh honeypot, tripwire, atau deteksi anomali di `PasakService::blockIp()` kini otomatis diteruskan ke kernel firewall host melalui Sentinel untuk melakukan drop koneksi seketika sebelum paket menyentuh PHP-FPM atau thread web server.
- **Sinkronisasi Pembukaan Blokir Kernel Firewall** — `PasakService::unblock()` dan `PasakService::unblockIp()` secara otomatis menginstruksikan daemon Sentinel untuk mencabut aturan drop IP dari kernel firewall saat blokir dicabut oleh admin.
- **Perintah Diagnostik CLI `php artisan pasak:sentinel`** — menyediakan perintah artisan terpadu dengan opsi `--ping`, `--status`, `--block=IP`, dan `--unblock=IP` untuk menguji dan memverifikasi koneksi socket Unix dengan daemon Sentinel secara langsung dari terminal.
- **Konfigurasi Lingkungan Terpusat (`config/pasak.php`)** — penambahan blok konfigurasi `sentinel` dengan opsi `enabled`, `socket`, dan `timeout` yang dapat disesuaikan lewat environment variable `PASAK_SENTINEL_SOCKET`.

## [1.6.9] - 2026-11-07

### Added

- **Modal Dialog Detail & Bukti Cuplikan Kode untuk Berkas Mencurigakan** (`stubs/blade/pages/pasak/server.blade.php`) — menambahkan tombol `🔍 Detail` dan tautan interaktif pada setiap baris temuan di tabel _Suspicious files & webshells_. Modal menyajikan nama berkas, jalur lengkap, kategori ancaman, ukuran, waktu modifikasi presisi, alasan lengkap, serta blok bukti cuplikan kode (`$file['snippet']`) yang terdeteksi dengan tombol hapus langsung dari dalam modal.
- **Kolom Tanggal Modifikasi Presisi pada Tabel Audit Server** — menyajikan tanggal & jam berkas hingga satuan detik (`d M Y, H:i:s`) dengan timestamp relatif (`diffForHumans`) dan tooltip ISO 8601.

### Fixed

- **Perlindungan Ganda Konfirmasi Penghapusan Berkas (Native Fallback)** — menambahkan `onsubmit="return confirm(...)"` pada form penghapusan berkas mencurigakan untuk menjamin dialog konfirmasi peringatan selalu muncul di peramban pengguna bahkan jika JavaScript kustom terhambat atau file view di VPS belum disinkronkan.

## [1.6.8] - 2026-11-07

### Fixed

- **Eliminasi False Positive Injeksi PHP Polyglot pada Berkas Media Biner (JPEG/PNG/Media)** — memperketat pola regex deteksi short tag `<?` pada `PasakServerSecurityService` dan `PasakSafeImageFile`. Sebelumnya, pola `<\?\s*\$[a-zA-Z_\x7f-\xff]` mengizinkan sembarang byte tinggi (`\x7f-\xff`) sehingga tabrakan byte biner terkompresi acak seperti `3C 3F 24 56` (`<?$V`) dan `3C 3F 24` diikuti _high-byte_ (`<?$?`) pada foto profil pengguna asli keliru dideteksi sebagai injeksi PHP polyglot kritis. Pola kini secara ketat mewajibkan token PHP valid, fungsi eksekusi berbahaya (`eval`, `system`, `passthru`, `exec`, `shell_exec`, `assert`, `popen`, `proc_open`), atau assignment variabel PHP nyata (`$_GET`, `$_POST`, `$cmd = ...`), menghilangkan false positive pada foto pengguna tanpa mengorbankan proteksi terhadap webshell nyata.
