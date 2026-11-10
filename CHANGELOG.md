# Changelog

All notable changes to `robyajo/pasak` will be documented in this file.

## [1.7.4] - 2026-11-10

### Added

- **Favicon Resmi Pasak untuk Dashboard & Halaman Error Terblokir** (`stubs/blade/pages/pasak/layout.blade.php`, `stubs/blocked.blade.php`):
  - Mengintegrasikan favicon default resolusi tinggi menggunakan logo/ikon resmi Pasak (berbasis SVG/PNG base64 yang selaras dengan ikon aplikasi di bagian bawah sidebar navigasi).
  - Melengkapi tag `<link rel="icon">`, `<link rel="shortcut icon">`, dan `<link rel="apple-touch-icon">` untuk memastikan favicon tampil konsisten di semua peramban desktop maupun mobile.
  - Tetap mendukung kustomisasi penuh melalui konfigurasi `config('pasak.dashboard.favicon')` atau penimpaan langsung via Blade `@section('favicon')`.

- **Konfigurasi URL Kembali Dashboard Dinamis (`pasak.dashboard.back_url`)** (`config/pasak.php`, `stubs/blade/pages/pasak/settings.blade.php`, `PasakSettingController.php`, `PasakSecuritySettingApiController.php`):
  - Memungkinkan admin mengonfigurasi tautan tombol "Kembali ke Aplikasi Utama" di sidebar dashboard secara fleksibel melalui antarmuka Pengaturan Keamanan maupun file konfigurasi.

## [1.7.3] - 2026-11-10

### Added

- **Pembersihan Cache & Optimasi Sistem Sekali Klik (`php artisan optimize:clear`) via Tombol Refresh Header** (`stubs/blade/pages/pasak/layout.blade.php`, `PasakServerController.php`, `PasakServerSecurityApiController.php`):
  - Tombol Refresh di sudut kanan atas header dashboard kini memicu endpoint `POST {pasakSlug}/optimize-clear` dan API `POST api/pasak/server/optimize-clear`.
  - Secara otomatis mengeksekusi `Artisan::call('optimize:clear')` yang membersihkan seluruh cache aplikasi, compiled views, konfigurasi, rute, dan event cache.
  - Dilengkapi animasi putar halus (_spinning icon_), pengiriman pesan aman via session flash, serta penayangan notifikasi floating toast hijau (_5 detik_) saat halaman termuat ulang dalam kondisi segar.

- **Branding Resmi Pasak Adaptif pada Header & Sidebar Dashboard** (`stubs/blade/pages/pasak/layout.blade.php`):
  - Menghadirkan logo resmi horizontal **PASAK** pada sisi kiri header (`.sec-header-brand`) lengkap dengan pemisah vertikal elegan dan tautan cepat ke Ringkasan Dashboard.
  - Varian logo dwitema adaptif otomatis: varian warna navy untuk Mode Terang (_Light Mode_) dan varian warna putih tajam (_crisp white_) untuk Mode Gelap (_Dark Mode_).
  - Menggantikan ikon perisai generik lama pada footer sidebar versi dengan ikon resmi Pasak (_squircle biru P berlatar jaringan node dan pasak oranye_) beresolusi tinggi (Retina-ready) tanpa dependensi aset eksternal.

- **Lencana Informasi & Fitur Salin Cepat Alamat IP Pengakses di Header** (`stubs/blade/pages/pasak/layout.blade.php`):
  - Menambahkan informasi alamat IP publik/lokal yang sedang mengakses dashboard secara langsung di samping tombol Refresh (`.sec-client-ip-badge`).
  - Dilengkapi indikator status aktif (_live green beacon_), label teks responsif, serta fitur interaktif satu klik (_click-to-copy_) ke clipboard dengan feedback notifikasi toast.

### Fixed

- **Pencegahan Alert Dialog Ganda pada Hapus Berkas Server Audit** (`stubs/blade/pages/pasak/server.blade.php`):
  - Menghilangkan konflik konfirmasi ganda bawaan peramban (`onsubmit="return confirm(...)"`) sehingga aksi hapus berkas mencurigakan konsisten hanya memicu 1 modal konfirmasi alert-dialog terpadu Pasak.
- **Perbaikan Kebocoran Teks Skrip pada Visualisasi Threat Map** (`stubs/blade/pages/pasak/threat-map.blade.php`):
  - Mengubah payload simulasi XSS menjadi format URL-encoded (`%3Cscript%3Ealert(1)%3C%2Fscript%3E`) guna mencegah penutupan prematur blok `<script>` oleh parser HTML browser yang sebelumnya menyebabkan teks kode tumpah ke bagian bawah layar.
- **Pencegahan Toast Duplikat & Konsistensi Aliran Radar Serangan**:
  - Menambahkan _deduplication guard_ pada sistem notifikasi toast untuk memblokir penayangan pesan identik secara simultan.
  - Mengalirkan data serangan database satu per satu (_playback queue_) secara real-time pada Threat Map sehingga statistik radar 100% konsisten dengan log riil tanpa menghasilkan _phantom counters_.

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
