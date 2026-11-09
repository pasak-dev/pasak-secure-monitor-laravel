---
name: docs-sync
description: "ACTIVATE setiap kali selesai mengerjakan perubahan kode, perbaikan bug, atau penambahan fitur di repositori Pasak Suite (pasak, pasak-sentinel, dashboard, pasak-api). Kelola dokumentasi teknis produk via CRUD API /api/v1/docs di Pasak API (Neon Serverless PostgreSQL): baca, tambah, perbarui (update), dan hapus dokumen markdown/mdx secara otomatis agar dokumentasi publik di pasak.is-a.dev/docs selalu akurat dan sinkron."
---

# Docs Sync — Otomasi Pembaruan Dokumentasi Teknis Pasak Suite (Neon PostgreSQL)

Aturan dan panduan alur kerja bagi AI Agent untuk memperbarui atau menambahkan dokumentasi teknis produk secara langsung ke database **Neon PostgreSQL** melalui REST API CRUD di **Pasak Cloud API** (`https://pasak-api.vercel.app/api/v1/docs`). Dokumentasi yang disimpan di database langsung ditampilkan di portal web publik (`https://pasak.is-a.dev/docs`).

## Konfigurasi Lingkungan (Wajib Ada)

- **API Endpoint**: `https://pasak-api.vercel.app/api/v1/docs` (atau `http://localhost:3000/api/v1/docs` saat pengembangan lokal `pasak-api`).
- **Kunci Akses**: Header `X-Docs-Key` — dibaca dari berkas `.DOCS_SYNC_KEY` di folder skill ini (`DOCS_SYNC_KEY=psk_docs_live_sync_key_9f8e7d:...`).
- **Database Backend**: Neon Serverless PostgreSQL (`docs` table di branch `production`).
- **Target Produk**:
  - `pasak-security-monitor`: Dokumentasi paket Laravel WAF (`content/pasak-security-monitor/docs/`).
  - `pasak-sentinel`: Dokumentasi daemon Linux Go & kernel nftables (`content/pasak-sentinel/docs/`).
  - `core`: Dokumentasi arsitektur platform suite & cloud console (`content/docs/`).

---

## 1. Cek Daftar Dokumen yang Tersedia

Sebelum mengubah atau menambah dokumen, periksa dulu daftar dokumen yang tersimpan di Neon PostgreSQL:

```bash
# Ringkasan seluruh produk
curl -s "https://pasak-api.vercel.app/api/v1/docs"

# Daftar dokumen spesifik produk:
curl -s "https://pasak-api.vercel.app/api/v1/docs?product=pasak-security-monitor"
curl -s "https://pasak-api.vercel.app/api/v1/docs?product=pasak-sentinel"
curl -s "https://pasak-api.vercel.app/api/v1/docs?product=core"
```

Respons mengembalikan daftar `docs`: `id`, `product`, `slug`, `title`, `description`, `category`, `order`, dan `updatedAt`.

---

## 2. Baca Isi Dokumen yang Ingin Diperbarui

Untuk membaca konten lengkap dokumen dari Neon DB:

```bash
curl -s "https://pasak-api.vercel.app/api/v1/docs/<PRODUK>/<SLUG>"
# atau
curl -s "https://pasak-api.vercel.app/api/v1/docs?product=<PRODUK>&slug=<SLUG>"
```

Contoh:

```bash
curl -s "https://pasak-api.vercel.app/api/v1/docs/pasak-security-monitor/getting-started/installation"
```

Respons:

```json
{
  "ok": true,
  "doc": {
    "id": "...",
    "product": "pasak-security-monitor",
    "slug": "getting-started/installation",
    "filePath": "getting-started/installation.md",
    "title": "Panduan Instalasi & Migrasi",
    "description": "...",
    "category": "Modul Keamanan",
    "content": "# Panduan Instalasi...",
    "rawContent": "---\ntitle: ...\n---\n...",
    "frontmatter": { "title": "...", "description": "...", "order": "2" },
    "updatedAt": "2026-11-09T..."
  }
}
```

---

## 3. Tambah atau Perbarui Dokumen (CREATE / UPDATE - UPSERT)

Gunakan metode **`POST /api/v1/docs`** untuk membuat dokumen baru atau merevisi dokumen di database Neon PostgreSQL.

### Format Standar (Metadata + Konten Markdown)

```bash
curl -X POST "https://pasak-api.vercel.app/api/v1/docs" \
  -H "Content-Type: application/json" \
  -H "X-Docs-Key: psk_docs_live_sync_key_9f8e7d" \
  -d '{
    "product": "pasak-security-monitor",
    "slug": "security-modules/custom-tripwire",
    "filePath": "security-modules/custom-tripwire.md",
    "title": "Tripwire & Honeypot Detection",
    "description": "Mekanisme jebakan URL virtual untuk menangkap bot pemindai celah otomatis.",
    "category": "Modul Keamanan",
    "order": 6,
    "content": "## Panduan Penggunaan Tripwire\n\nPenjelasan implementasi fitur..."
  }'
```

### Format Raw (Termasuk Frontmatter Lengkap)

Jika Anda sudah memiliki berkas Markdown utuh:

```bash
curl -X POST "https://pasak-api.vercel.app/api/v1/docs" \
  -H "Content-Type: application/json" \
  -H "X-Docs-Key: psk_docs_live_sync_key_9f8e7d" \
  -d "{
    \"product\": \"pasak-security-monitor\",
    \"slug\": \"integration-guides/laravel-filament\",
    \"rawContent\": $(jq -Rs . < /path/ke/berkas.md)
  }"
```

Respons berhasil:

- Dokumen baru dibuat: `HTTP 201 Created` (`"action": "created"`)
- Dokumen lama diperbarui: `HTTP 200 OK` (`"action": "updated"`)

---

## 4. Hapus Dokumen yang Sudah Usang (DELETE)

Jika ada modul atau fitur yang dihapus, hapus dokumen terkait dari database Neon PostgreSQL:

```bash
curl -X DELETE "https://pasak-api.vercel.app/api/v1/docs?product=<PRODUK>&slug=<SLUG>" \
  -H "X-Docs-Key: psk_docs_live_sync_key_9f8e7d"
```

Contoh:

```bash
curl -X DELETE "https://pasak-api.vercel.app/api/v1/docs?product=pasak-security-monitor&slug=deprecated-module" \
  -H "X-Docs-Key: psk_docs_live_sync_key_9f8e7d"
```

Respons: `{"ok": true, "action": "deleted", "message": "Document removed from Neon PostgreSQL database"}`.

---

## 5. Verifikasi Hasil Sinkronisasi

Setelah membuat, mengubah, atau menghapus dokumen:

1. Periksa endpoint API:
   ```bash
   curl -s "https://pasak-api.vercel.app/api/v1/docs/<PRODUK>/<SLUG>"
   ```
2. Pastikan respons berstatus `200 OK` dan data tersimpan di Neon PostgreSQL.
3. Kunjungi URL halaman web publik:
   - `https://pasak.is-a.dev/docs/<SLUG>`

---

## Standar Penulisan Dokumentasi (Wajib Dipatuhi AI)

1. **Bahasa Indonesia Baku & Jelas**: Utamakan penjelasan yang ramah pengembang (_developer-friendly_), teknis, dan presisi.
2. **Frontmatter Lengkap**: Setiap dokumen wajib memiliki:
   - `title`: Judul bab yang jelas dan tidak bertele-tele.
   - `description`: Ringkasan 1-2 kalimat fokus pada manfaat modul.
   - `category`: Kategori yang konsisten sesuai manifes modul.
   - `order`: Nomor urut urutan dalam sidebar.
3. **Contoh Kode Siap Pakai**: Sertakan blok kode lengkap dengan sintaks yang valid (`php`, `bash`, `go`, `json`, dsb). Jangan gunakan variabel placeholder fiktif yang membingungkan.
4. **Callout Alert Bergaya GitHub**: Manfaatkan `> [!NOTE]`, `> [!TIP]`, `> [!IMPORTANT]`, dan `> [!WARNING]` untuk menyoroti instruksi kritis.
