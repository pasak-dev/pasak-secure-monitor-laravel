# 03. Panduan Upgrade & Uninstall

Panduan menaikkan versi paket pada aplikasi yang sudah terpasang, pembaruan menyeluruh tanpa perlu konfigurasi manual, serta cara mencopot bersih bila sudah tidak dibutuhkan.

---

## 1. Upgrade Otomatis & Menyeluruh (Direkomendasikan)

Secara default, `pasax:upgrade` otomatis mengeksekusi `composer update pasax/secure-monitor` di latar belakang lalu menyinkronkan seluruh aset. Anda cukup menjalankan:

```bash
php artisan pasax:upgrade --force
# Atau gunakan alias resmi bila salah ketik:
php artisan pasax:upgrae --force
```

### Opsi Manual untuk Server Produksi / VPS:

Di beberapa server hosting / VPS di mana fungsi `proc_open` dinonaktifkan atau jika Anda lebih memilih menjalankan pembaruan Composer secara manual:

```bash
# 1. Unduh kode paket versi terbaru via Composer
composer update pasax/secure-monitor

# 2. Sinkronkan migrasi, rute, template Blade, dan segarkan cache
php artisan pasax:upgrade --force --no-composer
```

---

### Alur Eksekusi Otomatis `--force`:

1. **Pengecekan Versi Rilis Terbaru**: Memeriksa ketersediaan versi baru di katalog Packagist / GitHub.
2. **Pembaruan Dependensi Composer**: Menjalankan `composer update pasax/secure-monitor` untuk mengunduh kode paket terkini.
3. **Deteksi & Migrasi Database Baru**: Mendeteksi jika terdapat migrasi database tabel `pasax_*` baru yang belum diterapkan, lalu mengeksekusi `php artisan migrate --force` secara aman.
4. **Pembaruan Konfigurasi (`config/pasax.php`)**: Otomatis menyisipkan opsi konfigurasi baru yang belum ada (seperti socket daemon `sentinel`, visualizer `threat_map`, dan rotasi URL dashboard) tanpa menimpa konfigurasi kustom yang telah diubah pengguna.
5. **Sinkronisasi Tampilan Blade & Rute**: Memperbarui berkas tampilan dashboard (`resources/views/pages/pasax`) dan rute (`routes/pasax.php`) ke versi terbaru secara otomatis.
6. **Pembersihan Berkas Usang**: Otomatis mendeteksi dan menghapus berkas warisan lama era `security.*` yang sudah usang (`routes/security.php`, `config/security.php`, direktori dokumentasi lama `web-docs/`, skrip usang, dll.).
7. **Pembersihan Arsip Cadangan**: Memangkas berkas cadangan lama (`*.backup-*`) dengan mempertahankan hanya 2 arsip terbaru agar direktori proyek tetap bersih.
8. **Penyegaran Cache Framework**: Mengeksekusi `php artisan optimize:clear` agar konfigurasi, rute, dan tampilan Blade baru langsung aktif.

---

## 2. Varian Perintah `pasax:upgrade`

Jika Anda ingin mengontrol jalannya pembaruan secara terperinci atau menjalankan di lingkungan khusus (seperti container CI/CD atau dev offline):

```bash
# Hanya cek ketersediaan versi baru tanpa mengubah berkas apa pun:
php artisan pasax:upgrade --check

# Sinkronisasi aset lokal tanpa mengunduh dari Composer (misal repository symlink lokal atau setelah composer update manual):
php artisan pasax:upgrade --force --no-composer

# Pembaruan tanpa menjalankan migrasi database:
php artisan pasax:upgrade --force --no-migrate

# Pembaruan tanpa membuat berkas cadangan *.backup-* (ganti baru langsung):
php artisan pasax:upgrade --force --no-backup

# Pembaruan dengan pembersihan menyeluruh seluruh arsip cadangan lama:
php artisan pasax:upgrade --force --clean-backups

# Sinkronisasi rute atau views secara spesifik:
php artisan pasax:upgrade --sync-routes
php artisan pasax:upgrade --sync-views
```

---

## 3. Verifikasi Setelah Upgrade

Setelah proses upgrade selesai, pastikan integrasi berjalan sempurna:

```bash
# 1. Periksa status integritas dan versi aktif
php artisan pasax:baseline

# 2. Pastikan rute Pasax terdaftar dengan benar
php artisan route:list --name=pasax

# 3. Uji respons WAF terhadap canary probe
curl -I 'https://domain-anda.test/?q=%7B%7B7*7%7D%7D'
```

---

## 4. Panduan Deployment VPS & CI/CD (Migrasi, Seeder, dan Zero Double Data)

Saat melakukan deployment pertama kali ke VPS, server baru, atau setelah melakukan `git pull` dalam alur rilis produksi/CI/CD, Anda tidak perlu menjalankan serangkaian perintah migrasi, seeder, dan clear cache secara manual satu per satu. Pasax menyediakan perintah deployment tunggal yang dirancang khusus:

```bash
php artisan pasax:deploy --force
```

### Mengapa Aman & Menjamin Zero Double Data?

1. **Idempotent Seeder**: Seeder `PasaxDefaultSettingsSeeder` menggunakan metode `firstOrCreate` / `updateOrCreate` pada tabel `pasax_settings`. Dijalankan 100 kali pun **tidak akan pernah menduplikasi baris konfigurasi**.
2. **Isolasi Penuh dari Tabel Host**: Perintah ini hanya beroperasi pada tabel khusus paket Pasax (`pasax_*`). **Sama sekali tidak menyentuh atau merusak tabel bisnis utama** seperti `users`, `orders`, `products`, dsb.
3. **Otomasi Secret Key**: Memastikan `PASAX_DASHBOARD_SECRET` terisi di berkas `.env` server produksi tanpa merusak nilai yang sudah ada, sehingga sesi login pengguna tidak akan terputus.
4. **Pembersihan Cache Produksi**: Otomatis menjalankan `config:clear`, `route:clear`, dan `view:clear` agar konfigurasi dan rute termutakhir langsung aktif di lingkungan web server.

### Opsi Perintah Deployment:

```bash
# Deployment standar otomatis di VPS / CI-CD:
php artisan pasax:deploy --force

# Reset konfigurasi keamanan pasax_settings ke bawaan pabrik (tanpa hapus log/blokir):
php artisan pasax:deploy --reset-settings --force

# Reset status bersih tabel Pasax untuk audit awal (tabel host users tetap 100% aman):
php artisan pasax:deploy --reset-tables --force
```

---

## 5. Uninstall Bersih (Pencopotan Penuh)

Bila paket sudah tidak dibutuhkan, Pasax menyediakan perintah pencopotan bersih yang menghapus seluruh jejak tanpa meninggalkan residu atau merusak aplikasi host:

```bash
php artisan pasax:uninstall --force
```

### Yang Dikerjakan `pasax:uninstall`:

- Menghapus 8 tabel database khusus Pasax beserta catatan riwayat migrasinya di tabel `migrations`.
- Mencabut middleware Pasax dari `bootstrap/app.php` (Laravel 11+) atau `app/Http/Kernel.php` (Laravel 10).
- Mengembalikan konfigurasi `.htaccess` dari berkas cadangan asli sebelum instalasi.
- Menghapus rute publikasi (`routes/pasax.php`), tampilan Blade, dan konfigurasi `config/pasax.php`.
- Menghapus kunci environment `PASAX_*` dari `.env`.
- Menjalankan `composer remove pasax/secure-monitor`.

> [!TIP]
> Gunakan flag `--keep-tables` atau `--keep-env` bila Anda ingin mencopot paket namun tetap menyimpan data audit log dan riwayat pemblokiran di database.
