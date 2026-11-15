# 03. Konfigurasi Lengkap & Environment Variable

Seluruh perilaku **`pasax/secure-monitor`** dikontrol melalui berkas konfigurasi `config/pasax.php`. Konfigurasi ini dirancang agar dapat disesuaikan tanpa perlu mengubah kode sumber paket, cukup melalui berkas `.env`.

---

## 1. Variabel Inti Lingkungan (`.env`) & Pengaturan Database

Paket **`pasax/secure-monitor`** menerapkan prinsip pemisahan tanggung jawab yang jelas:

- **Variabel Inti Bootstrap (`.env`)**: Berkas `.env` hanya memuat sakelar bootstrap paling vital (sakelar WAF, IP whitelist awal, dan rute).
- **Pengaturan Operasional (`pasax_settings`)**: Seluruh perilaku keamanan dinamis (ambang blokir per-tingkat, ambang kombinasi, lingkup karantina `device|ip|auto`, durasi pemblokiran, brute force lockout, dan retensi log) diatur langsung dari **Dashboard Pasax → Pengaturan (`/pasax/settings`)** dan tersimpan di database.

### A. Blok Variabel Inti Bawaan (`stubs/env.stub`)

Berikut adalah blok variabel `.env` ringkas yang disematkan saat `php artisan pasax:install`:

```dotenv
# Sakelar utama WAF: Aktifkan (true) atau nonaktifkan (false) seluruh inspeksi keamanan
PASAX_MONITOR_ENABLED=true

# Daftar putih IP yang tidak akan pernah diblokir (pisahkan dengan koma jika lebih dari satu)
PASAX_IP_WHITELIST=127.0.0.1,::1

# Sakelar pendaftaran middleware WAF otomatis ke HTTP Kernel
PASAX_AUTO_REGISTER_MIDDLEWARE=false

# Rute REST API headless (/api/pasax/*)
PASAX_ROUTES_ENABLED=true

# Dashboard monitoring Blade murni (akses via /pasax, wajib login)
PASAX_DASHBOARD_ENABLED=false
PASAX_DASHBOARD_PREFIX=pasax
# URL acak sekali pakai: /pasax 302 ke /pasax-{token}/ milik sesi tiap user,
# token diputar setiap akses HTML sehingga reload selalu berubah
# (mis. /pasax-95856857476jdhjs5/logs lalu /pasax-634664873647jbdjshf/threat-map)
PASAX_DASHBOARD_ROTATING=true
PASAX_DASHBOARD_SECRET=
# Diisi otomatis oleh `pasax:install` (hanya ke .env). Setelah clone/deploy
# baru jalankan `php artisan pasax:secret` lalu `php artisan config:clear`.
PASAX_DASHBOARD_TOKEN_LENGTH=16
PASAX_DASHBOARD_SINGLE_USE=true
PASAX_DASHBOARD_GRACE_SECONDS=120
# URL tombol "Kembali ke Dashboard Utama" pada header & sidebar Pasax (bawaan: /)
PASAX_DASHBOARD_BACK_URL=/

# Alamat email dukungan yang ditampilkan pada respon blokir 403
PASAX_SUPPORT_EMAIL=security@example.com
```

> 🔀 **URL dashboard acak sekali pakai (moving-target)**: saat `PASAX_DASHBOARD_ROTATING=true` (bawaan aktif sejak install.), pintu stabil `/pasax` selalu mengalihkan (302) ke `/pasax-{token}/` milik sesi pengguna — misalnya `http://localhost:8000/pasax-95856857476jdhjs5/logs`. Token diputar setiap halaman HTML dimuat sehingga me-reload browser selalu menampilkan URL baru; URL lama hanya hidup selama `PASAX_DASHBOARD_GRACE_SECONDS` (bawaan 120 detik, untuk polling `threat-map/feed` & form) lalu dialihkan ke URL baru. URL yang tidak dikenal mengembalikan 404 (bukan 403) agar keberadaan dashboard tetap tersamar. Butuh middleware `web` (sesi); nonaktifkan (`false`) bila memakai prefix statis yang mudah di-bookmark. Host yang rute kustomnya lama dapat memperolehnya via `php artisan pasax:upgrade --sync-routes`.

### B. Pengaturan Dinamis Berbasis Database (`pasax_settings`)

Saat migrasi dijalankan (atau saat `pasax:install --migrate` / `pasax:upgrade`), seeder `PasaxDefaultSettingsSeeder` otomatis menanam konfigurasi bawaan ke dalam tabel `pasax_settings`:

| Kunci Pengaturan                   | Nilai Bawaan                                 | Keterangan & Pengaturan di Dashboard                                                                        |
| :--------------------------------- | :------------------------------------------- | :---------------------------------------------------------------------------------------------------------- |
| `auto_block.enabled`               | `false`                                      | Status aktifasi pemblokiran otomatis berbasis akumulasi ancaman.                                            |
| `auto_block.level_thresholds`      | `{"critical":1,"high":2,"medium":3,"low":5}` | Ambang batas kejadian per tingkat bahaya sebelum blokir dijatuhkan.                                         |
| `auto_block.combined_threshold`    | `3`                                          | Ambang akumulasi gabungan lintas tingkat bahaya (minimal 2 level berbeda).                                  |
| `auto_block.window_minutes`        | `10`                                         | Jendela evaluasi waktu mundur (menit).                                                                      |
| `auto_block.duration_hours`        | `24`                                         | Durasi blokir otomatis (jam; 0 = permanen).                                                                 |
| `auto_block.scope`                 | `auto`                                       | Lingkup karantina: `device` (perangkat), `ip` (seluruh IP), atau `auto` (adaptif, aman bagi Wi-Fi bersama). |
| `instant_block.enabled`            | `false`                                      | Blokir langsung 30 hari untuk signature zero-tolerance.                                                     |
| `login_throttle.lockout_threshold` | `3`                                          | Jumlah kegagalan otentikasi sebelum akun/IP dikunci sementara.                                              |
| `login_throttle.base_minutes`      | `1`                                          | Durasi lockout awal pada tingkatan pertama (menit).                                                         |
| `login_throttle.max_minutes`       | `30`                                         | Batas maksimum penalti penguncian berjenjang (menit).                                                       |
| `logs.retention_days`              | `90`                                         | Masa simpan riwayat log audit (hari) sebelum dibersihkan otomatis.                                          |
| `dashboard.back_url`               | `/`                                          | URL tujuan tombol "Kembali ke Dashboard Utama" pada header dan navigasi sidebar. Dapat diatur via `/pasax/settings` (misal `/admin`, `/app`, dll). |

> 💡 **Prioritas Konfigurasi**: Nilai yang tersimpan pada tabel `pasax_settings` diutamakan di atas nilai `.env`. Kunci `.env` lama tetap berlaku sebagai fallback senyap apabila entri pada database belum tersimpan.

### C. Daftar Lengkap Variabel Override & Tingkat Lanjut (`.env`)

| `PASAX_INSTANT_BLOCK_ENABLED` | bool | `false` | Opt-in blokir instan. Default monitoring saja; aktifkan bila siap karantina otomatis. |
| `PASAX_INSTANT_BLOCK_DURATION` | int | `720` | Durasi blokir instan (dalam jam; bawaan: 720 jam = 30 hari). Masukkan `0` untuk permanen. |
| `PASAX_MAX_INSPECT_LENGTH` | int | `4000` | Batas maksimum panjang karakter string payload yang diinspeksi untuk mencegah ReDoS pada payload raksasa. |
| `PASAX_BLOCK_SUSPICIOUS` | bool | `false` | Tolak langsung (HTTP 403) request yang memicu ancaman berlevel `critical` tanpa menunggu akumulasi. |
| `PASAX_LOG_RETENTION_DAYS` | int | `90` | Masa simpan riwayat log keamanan (hari) sebelum dibersihkan otomatis oleh cron scheduler. |
| `PASAX_ACCESS_LOG_PATHS` | string | `null` | Jalur absolut ke berkas access log web server Nginx/Apache (dipisahkan koma). |
| `PASAX_ACCESS_LOG_MAX_LINES` | int | `500000` | Batas maksimum baris log yang dianalisis dalam satu kali eksekusi streaming scanner. |
| `PASAX_ACCESS_LOG_AUTO_SCAN` | bool | `false` | Mengaktifkan tugas cron harian otomatis untuk memindai berkas log web server. |
| `PASAX_ACCESS_LOG_AUTO_SCAN_HOURS` | int | `24` | Rentang waktu mundur (jam) yang dipindai oleh pemindai otomatis terjadwal. |
| `PASAX_LOGIN_LOCKOUT_ENABLED` | bool | `true` | Mengaktifkan penguncian bertingkat (_stepped login lockout_) terhadap brute force. |
| `PASAX_LOGIN_LOCKOUT_THRESHOLD` | int | `3` | Jumlah kegagalan otentikasi berturut-turut sebelum akun/IP dikunci sementara. |
| `PASAX_LOGIN_LOCKOUT_BASE_MINUTES` | int | `1` | Durasi awal penguncian pada tier pertama (menit). |
| `PASAX_LOGIN_LOCKOUT_MAX_MINUTES` | int | `30` | Batas maksimum durasi penguncian berjenjang (menit). |
| `PASAX_LOGIN_LOCKOUT_DECAY_MINUTES` | int | `60` | Waktu tanpa percobaan gagal (menit) sebelum penghitung kegagalan direset ke nol. |
| `PASAX_SERVER_SCAN_CACHE_MINUTES` | int | `10` | Durasi cache hasil pemindaian integritas dan berkas server (menit). |
| `PASAX_SERVER_SCAN_MAX_FILES` | int | `20000` | Batas maksimum berkas yang diperiksa saat audit baseline integritas. |
| `PASAX_RECENT_CHANGES_DAYS` | int | `7` | Rentang hari berkas baru/dimodifikasi yang dimasukkan ke laporan audit server. |
| `PASAX_DISK_WARNING_PERCENT` | int | `20` | Ambang batas sisa kapasitas disk (%) untuk memicu peringatan (_warning_). |
| `PASAX_DISK_CRITICAL_PERCENT` | int | `10` | Ambang batas sisa kapasitas disk (%) untuk memicu status genting (_critical_). |
| `PASAX_MAX_ADMIN_ACCOUNTS` | int | `5` | Jumlah maksimum akun administrator yang wajar sebelum auditor memunculkan peringatan. |
| `PASAX_LOG_SIZE_WARNING_MB` | int | `100` | Peringatan audit jika berkas `storage/logs/laravel.log` melebihi ukuran ini (MB). |
| `PASAX_USER_MODEL` | string | `App\Models\User` | FQCN kelas Model Pengguna otentikasi di aplikasi host Anda. |
| `PASAX_USERS_TABLE` | string | `users` | Nama tabel database untuk data pengguna. |
| `PASAX_BLOCKED_IPS_TABLE` | string | `blocked_ips` | Nama tabel untuk karantina IP dan perangkat. |
| `PASAX_LOGS_TABLE` | string | `pasax_logs` | Nama tabel untuk riwayat audit log keamanan. |
| `PASAX_LOGIN_ATTEMPTS_TABLE` | string | `login_attempts` | Nama tabel untuk penghitung kegagalan login. |
| `PASAX_IP_UNBLOCK_REQUESTS_TABLE` | string | `ip_unblock_requests` | Nama tabel untuk permohonan tiket banding buka blokir. |
| `PASAX_USER_LOGINS_TABLE` | string | `user_logins` | Nama tabel untuk sesi dan riwayat login pengguna. |
| `PASAX_TRUSTED_IPS_TABLE` | string | `trusted_ips` | Nama tabel untuk daftar IP terpercaya milik pengguna. |
| `PASAX_SETTINGS_TABLE` | string | `pasax_settings` | Nama tabel untuk pengaturan dinamis paket (diubah via API admin). |
| `PASAX_ROUTES_ENABLED` | bool | `true` | Mengaktifkan rute headless REST API di bawah `/api/pasax/*`. |
| `PASAX_ROUTES_PREFIX` | string | `api/pasax` | Awalan prefix URI untuk seluruh rute API paket. |
| `PASAX_SCHEDULE_ENABLED` | bool | `true` | Mengaktifkan registrasi jadwal tugas cron otomatis (prune & heartbeat). |
| `PASAX_PRUNE_SCHEDULE` | string | `02:30` | Jam eksekusi harian pembersihan log usang (format `HH:mm`). |
| `PASAX_SUPPORT_EMAIL` | string | `security@example.com` | Alamat email dukungan yang ditampilkan pada layar respon blokir. |
| `PASAX_AUTO_REGISTER_MIDDLEWARE` | bool | `false` | Mendaftarkan middleware global secara otomatis saat boot aplikasi. |
| `PASAX_ANNOUNCEMENT_ENABLED` | bool | `true` | Banner + dialog pengumuman otomatis di halaman utama (`/`). |

---

## 2. Struktur Konfigurasi Inti (`config/pasax.php`)

### A. Whitelist Jaringan & IP Lokal

Konfigurasi `whitelist` memastikan IP internal, proxy terpercaya, dan subnet kantor tidak pernah diblokir meskipun melakukan pengujian pentest:

```php
'whitelist' => array_values(array_filter(array_map(
    'trim',
    explode(',', (string) env('PASAX_IP_WHITELIST', '127.0.0.1,::1'))
))),
```

### B. Signature Zero-Tolerance (`instant_block`)

Menentukan pola serangan mutlak yang langsung memblokir pelaku pada request pertama:

```php
'instant_block' => [
    'enabled' => (bool) env('PASAX_INSTANT_BLOCK_ENABLED', false),
    'duration_hours' => (int) env('PASAX_INSTANT_BLOCK_DURATION', 720), // 30 hari
    'skip_whitelisted' => true,
    'signatures' => [
        [
            'id' => 'webshell_upload',
            'label' => 'Percobaan upload webshell (null byte / double extension)',
            'target' => 'any',
            'patterns' => [
                '/\.php(?:[\x00\0]|%00)\.[a-z0-9_-]+/i',
                '/\.php\.(?:jpe?g|png|gif|webp|svg|bmp|ico|pdf|zip|rar|tar|gz|7z|docx?|xlsx?)/i',
            ],
        ],
        // ... signatures lainnya
    ],
],
```

### C. Pola Aturan WAF Reguler (`rules`)

Pola aturan untuk SQLi, XSS, Path Traversal, Command Injection, dan SSRF yang diakumulasikan ke dalam penilaian ancaman bersyarat:

```php
'rules' => [
    [
        'id' => 'sql_injection',
        'label' => 'Indikasi SQL Injection',
        'level' => 'critical',
        'patterns' => [
            '/\bunion\s+(?:all\s+)?select\b/i',
            '/\bselect\b.+\bfrom\b.+\bwhere\b/is',
            // ...
        ],
    ],
    // ...
],
```

---

## 3. Preset Konfigurasi Lingkungan

### 🛠 Preset Pengembangan Lokal (`.env.local`)

Pada lingkungan development lokal, Anda dapat melonggarkan blokir agar tidak mengganggu proses pengujian dan debugging:

```dotenv
PASAX_MONITOR_ENABLED=true
PASAX_BLOCK_ENFORCEMENT=false
PASAX_AUTO_BLOCK_ENABLED=false
PASAX_INSTANT_BLOCK_ENABLED=false
PASAX_BLOCK_SUSPICIOUS=false
PASAX_WHITELIST=127.0.0.1,::1,192.168.*.*
```

### 🚀 Preset Produksi (`.env.production`)

Di server produksi, seluruh perlindungan diaktifkan penuh dengan pembersihan log berkala dan toleransi ketat:

```dotenv
PASAX_MONITOR_ENABLED=true
PASAX_BLOCK_ENFORCEMENT=true
PASAX_INSTANT_BLOCK_ENABLED=false
PASAX_LOG_RETENTION_DAYS=90
PASAX_ACCESS_LOG_AUTO_SCAN=true
PASAX_ACCESS_LOG_PATHS=/var/log/nginx/access.log
```

> Setelah sebaris settings tersedia di `pasax_settings`, ambang, lingkup, durasi, throttle lockout, dan retensi log dapat disetel penuh dari dashboard (`/pasax/settings`) tanpa mengubah file `.env` lagi.
