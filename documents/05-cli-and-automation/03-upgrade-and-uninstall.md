# 03. Panduan Upgrade & Uninstall

Panduan menaikkan versi paket pada aplikasi yang sudah terpasang, pembaruan menyeluruh tanpa perlu konfigurasi manual, serta cara mencopot bersih bila sudah tidak dibutuhkan.

---

## 1. Upgrade Otomatis & Menyeluruh (Direkomendasikan)

Mulai versi terbaru, Anda cukup menjalankan perintah dengan opsi `--force`. Perintah ini akan mengeksekusi seluruh siklus pembaruan secara otomatis tanpa harus menjalankan flag `--sync-views` secara terpisah:

```bash
php artisan pasak:upgrade --force
# Atau gunakan alias resmi bila salah ketik:
php artisan pasak:upgrae --force
```

### Alur Eksekusi Otomatis `--force`:

1. **Pengecekan Versi Rilis Terbaru**: Memeriksa ketersediaan versi baru di katalog Packagist / GitHub.
2. **Pembaruan Dependensi Composer**: Menjalankan `composer update robyajo/pasak` untuk mengunduh kode paket terkini.
3. **Deteksi & Migrasi Database Baru**: Mendeteksi jika terdapat migrasi database tabel `pasak_*` baru yang belum diterapkan, lalu mengeksekusi `php artisan migrate --force` secara aman.
4. **Pembaruan Konfigurasi (`config/pasak.php`)**: Otomatis menyisipkan opsi konfigurasi baru yang belum ada (seperti socket daemon `sentinel`, visualizer `threat_map`, dan rotasi URL dashboard) tanpa menimpa konfigurasi kustom yang telah diubah pengguna.
5. **Sinkronisasi Tampilan Blade & Rute**: Memperbarui berkas tampilan dashboard (`resources/views/pages/pasak`) dan rute (`routes/pasak.php`) ke versi terbaru secara otomatis.
6. **Pembersihan Berkas Usang**: Otomatis mendeteksi dan menghapus berkas warisan lama era `security.*` yang sudah usang (`routes/security.php`, `config/security.php`, direktori dokumentasi lama `web-docs/`, skrip usang, dll.).
7. **Pembersihan Arsip Cadangan**: Memangkas berkas cadangan lama (`*.backup-*`) dengan mempertahankan hanya 2 arsip terbaru agar direktori proyek tetap bersih.
8. **Penyegaran Cache Framework**: Mengeksekusi `php artisan optimize:clear` agar konfigurasi, rute, dan tampilan Blade baru langsung aktif.

---

## 2. Varian Perintah `pasak:upgrade`

Jika Anda ingin mengontrol jalannya pembaruan secara terperinci atau menjalankan di lingkungan khusus (seperti container CI/CD atau dev offline):

```bash
# Hanya cek ketersediaan versi baru tanpa mengubah berkas apa pun:
php artisan pasak:upgrade --check

# Sinkronisasi aset lokal tanpa mengunduh dari Composer (misal repository symlink lokal):
php artisan pasak:upgrade --force --no-composer

# Pembaruan tanpa menjalankan migrasi database:
php artisan pasak:upgrade --force --no-migrate

# Pembaruan tanpa membuat berkas cadangan *.backup-* (ganti baru langsung):
php artisan pasak:upgrade --force --no-backup

# Pembaruan dengan pembersihan menyeluruh seluruh arsip cadangan lama:
php artisan pasak:upgrade --force --clean-backups

# Sinkronisasi rute atau views secara spesifik:
php artisan pasak:upgrade --sync-routes
php artisan pasak:upgrade --sync-views
```

---

## 3. Verifikasi Setelah Upgrade

Setelah proses upgrade selesai, pastikan integrasi berjalan sempurna:

```bash
# 1. Periksa status integritas dan versi aktif
php artisan pasak:baseline

# 2. Pastikan rute Pasak terdaftar dengan benar
php artisan route:list --name=pasak

# 3. Uji respons WAF terhadap canary probe
curl -I 'https://domain-anda.test/?q=%7B%7B7*7%7D%7D'
```

---

## 4. Uninstall Bersih (Pencopotan Penuh)

Bila paket sudah tidak dibutuhkan, Pasak menyediakan perintah pencopotan bersih yang menghapus seluruh jejak tanpa meninggalkan residu atau merusak aplikasi host:

```bash
php artisan pasak:uninstall --force
```

### Yang Dikerjakan `pasak:uninstall`:

- Menghapus 8 tabel database khusus Pasak beserta catatan riwayat migrasinya di tabel `migrations`.
- Mencabut middleware Pasak dari `bootstrap/app.php` (Laravel 11+) atau `app/Http/Kernel.php` (Laravel 10).
- Mengembalikan konfigurasi `.htaccess` dari berkas cadangan asli sebelum instalasi.
- Menghapus rute publikasi (`routes/pasak.php`), tampilan Blade, dan konfigurasi `config/pasak.php`.
- Menghapus kunci environment `PASAK_*` dari `.env`.
- Menjalankan `composer remove robyajo/pasak`.

> [!TIP]
> Gunakan flag `--keep-tables` atau `--keep-env` bila Anda ingin mencopot paket namun tetap menyimpan data audit log dan riwayat pemblokiran di database.
