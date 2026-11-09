# Changelog

All notable changes to `robyajo/pasak` will be documented in this file.

## [1.7.2] - 2026-11-09

### Added

- **Peta Intelijen Siber Interaktif & Dual-Engine Fallback pada Threat Map** (`stubs/blade/pages/pasak/threat-map.blade.php`):
  - Visualisasi intelijen serangan siber real-time dengan HUD status taktis (ThreatsEye SOC Style), koordinat node yang dilindungi, dan status firewall WAF (Blocking / Monitoring).
  - Pilihan proyeksi peta fleksibel: **3D Globe** (rotasi interaktif) dan **2D Tactical Flat Map** (Mercator / Equirectangular).
  - **Dual-Engine & Offline Resilience**: Mendukung failover multi-CDN (`jsdelivr` & `cdnjs`), data benua offline bawaan (`FALLBACK_WORLD_GEOJSON`), dan engine **Radar Cyber 2D Mandiri (Pure Canvas Standalone Engine)** yang berjalan 100% tanpa dependensi eksternal bila CDN diblokir atau offline.
  - Perbaikan styling modal toast offline (`.threat-map-offline[hidden] { display: none !important; }`) sehingga mudah ditutup tanpa menghalangi visualisasi radar.

- **Pembaruan Menyeluruh & Pembersihan Berkas Usang via `php artisan pasak:upgrade --force` (dan Alias `pasak:upgrae`)**:
  - Menambahkan alias resmi `pasak:upgrae` pada perintah konsol sehingga salah ketik tetap berjalan mulus.
  - Opsi `--force` kini memperbarui dan menyinkronkan seluruh aset dari instalasi lama ke standar versi terbaru:
    - **Deteksi & Laporan Migrasi Database**: Menampilkan deteksi jumlah migrasi baru yang belum diterapkan (`⚠ Terdeteksi X migrasi database baru...`) dan laporan status pembaruan struktur database yang informatif.
    - **Pembaruan Konfigurasi (`config/pasak.php`)**: Otomatis menyisipkan konfigurasi baru yang hilang (`sentinel` IPC socket daemon, `threat_map` globe visualizer, URL rotasi dinamis, dan migrasi nama tabel ke prefix `pasak_`) tanpa menimpa nilai kustomisasi pengguna.
    - **Sinkronisasi Tampilan (`resources/views/pages/pasak`) & Rute**: Secara otomatis menyinkronkan tampilan Blade dashboard dan rute ke versi terbaru, dengan opsi `--clean-backups` dan `--no-backup`.
    - **Pembersihan Berkas & Direktori Usang**: Otomatis mendeteksi dan menghapus berkas serta direktori peninggalan versi lama yang sudah tidak digunakan (`routes/security.php`, `routes/security-api.php`, `config/security.php`, `resources/views/pages/security`, `resources/js/pages/security`, `resources/js/components/security`, `web-docs/`, `finis.sh`, `install-dash.sh`), serta memangkas arsip cadangan berlebih (`.backup-*`) dengan mempertahankan 2 cadangan paling mutakhir.

- **Aksi Unduh Berkas & Modal Konfirmasi Verifikasi Berkas Aman pada Server Audit** (`stubs/blade/pages/pasak/server.blade.php`, `PasakServerSecurityService.php`, `PasakServerController.php`):
  - Menambahkan tombol unduh aman (`⬇️ Unduh`) pada tabel berkas mencurigakan & modal detail inspeksi yang memungkinkan admin mengunduh berkas berbahaya/mencurigakan secara aman dengan header `application/octet-stream` dan `X-Content-Type-Options: nosniff` untuk analisis offline.
  - Menambahkan aksi `🛡️ Verifikasi` dan modal konfirmasi `🛡️ Verifikasi Berkas Aman` yang memungkinkan admin menandai false positive sebagai berkas aman/terverifikasi disertai catatan investigasi opsional.
  - Berkas terverifikasi dicatat secara permanen di storage (`app/security/verified_files.json`) lengkap dengan hash SHA-256, ukuran, identitas verifikator, dan waktu verifikasi, serta otomatis dikecualikan dari pemindaian mencurigakan masa depan selama hash berkas tidak dimanipulasi.
- **Tombol Aksi Cepat & Modal Dialog Karantina IP Interaktif pada Log Keamanan** (`stubs/blade/pages/pasak/logs.blade.php`, `PasakBlockedIpController.php`):
  - Menambahkan tombol aksi cepat `🚫` di sebelah alamat IP dan tombol `🚫 Blokir` pada setiap baris riwayat log keamanan.
  - Menghadirkan modal dialog karantina terpadu `🚫 Blokir Alamat IP` yang memungkinkan admin memblokir penyerang secara instan langsung dari halaman log.
  - Mendukung pemilihan cakupan pemblokiran berbasis kartu responsif: **Hanya Perangkat** (`device` - isolasi fingerprint peramban tanpa mengorbankan pengguna lain di jaringan NAT/Wi-Fi yang sama) dan **Seluruh IP Router** (`ip` - blokir penuh seluruh lalu lintas dari IP publik tersebut).
  - Pilihan durasi fleksibel (1 jam, 6 jam, 12 jam, 24 jam, 3 hari, 7 hari, 30 hari, atau Permanen) dengan auto-fill alasan ancaman dan catatan investigasi internal opsional.
  - Terintegrasi langsung dengan `PasakSentinelBridge` untuk langsung meneruskan pemblokiran router IP ke kernel firewall Linux (`nftables`/`iptables`).
- **Chart Visual Tren Serangan (Line Chart) pada Halaman Security Logs** (`stubs/blade/pages/pasak/logs.blade.php`, `PasakLogController.php`):
  - Menambahkan kartu visualisasi grafik garis (_line chart_) interaktif berbasis vektor SVG murni (zero-dependency) pada halaman audit log keamanan.
  - Mendukung pergantian rentang waktu dinamis dengan tombol filter **Minggu** (7 hari), **Bulan** (30 hari), dan **Tahun** (12 bulan) dengan tetap mempertahankan filter pencarian, event, level keparahan, rentang tanggal, dan jumlah paginasi tabel.
  - Dilengkapi ringkasan telemetri eksekutif (total ancaman pada periode, tanggal & volume serangan puncak, rata-rata harian/bulanan), titik data interaktif (_nodes_) dengan tooltip rincian keparahan (Critical, High, Medium, Low), garis grid horizontal berlabel sumbu Y, serta gradien area visual (_smooth area fill_).

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
