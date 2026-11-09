---
name: docs-sync
description: "ACTIVATE setiap kali selesai mengerjakan perubahan kode, perbaikan bug, atau penambahan fitur di repositori Pasak Suite (pasak, pasak-sentinel, atau dashboard). Kelola dokumentasi teknis produk via CRUD API /api/v1/docs di Pasak Cloud: baca, tambah, perbarui (update), dan hapus dokumen markdown/mdx secara otomatis agar dokumentasi publik selalu akurat dan sinkron."
---

# Docs Sync — Otomasi Pembaruan Dokumentasi Teknis Pasak Suite

Aturan dan panduan alur kerja bagi AI Agent untuk memperbarui atau menambahkan dokumentasi teknis produk secara langsung ke portal dokumentasi publik (`https://pasak.is-a.dev/docs`) melalui API CRUD dokumentasi (`https://pasak.is-a.dev/api/v1/docs`).

## Konfigurasi Lingkungan (Wajib Ada)

- **API Endpoint**: `https://pasak.is-a.dev/api/v1/docs` (atau `http://localhost:3000/api/v1/docs` saat pengembangan lokal).
- **Kunci Akses**: Header `X-Docs-Key` — dibaca dari berkas `.DOCS_SYNC_KEY` di folder skill ini (`DOCS_SYNC_KEY=<kunci>:<nama_app>`).
- **Target Produk**:
  - `pasak-security-monitor`: Dokumentasi paket Laravel WAF (`content/pasak-security-monitor/docs/`).
  - `pasak-sentinel`: Dokumentasi daemon Linux Go & kernel nftables (`content/pasak-sentinel/docs/`).
  - `core`: Dokumentasi arsitektur platform suite & cloud console (`content/docs/`).

---

## 1. Cek Daftar Dokumen yang Tersedia

Sebelum mengubah atau menambah dokumen, periksa dulu daftar dokumen yang ada untuk produk terkait:

```bash
# Ringkasan seluruh produk
curl -s "https://pasak.is-a.dev/api/v1/docs"

# Daftar dokumen spesifik produk:
curl -s "https://pasak.is-a.dev/api/v1/docs?product=pasak-security-monitor"
curl -s "https://pasak.is-a.dev/api/v1/docs?product=pasak-sentinel"
curl -s "https://pasak.is-a.dev/api/v1/docs?product=core"
```

Respons akan mengembalikan daftar `docs`: `slug`, `filePath`, `title`, `sizeBytes`, dan `updatedAt`.

---

## 2. Baca Isi Dokumen yang Ingin Diperbarui

Untuk membaca konten lengkap beserta frontmatter:

```bash
curl -s "https://pasak.is-a.dev/api/v1/docs?product=<PRODUK>&slug=<SLUG>" \
  -H "X-Docs-Key: <KEY>"
```

Contoh:
```bash
curl -s "https://pasak.is-a.dev/api/v1/docs?product=pasak-sentinel&slug=01-architecture"
```

Respons:
```json
{
  "ok": true,
  "product": "pasak-sentinel",
  "filePath": "01-architecture.md",
  "slug": "01-architecture",
  "title": "Arsitektur & Desain Runtime Daemon",
  "description": "...",
  "category": "Host & Kernel Defense",
  "frontmatter": { "title": "...", "description": "...", "order": "1" },
  "body": "# Chapter 01: ...",
  "rawContent": "---\ntitle: ...\n---\n..."
}
```

---

## 3. Tambah atau Perbarui Dokumen (CREATE / UPDATE - UPSERT)

Gunakan metode **`POST /api/v1/docs`** untuk membuat dokumen baru atau merevisi dokumen yang sudah ada.

### Format Standar (Metadata + Konten Markdown)

```bash
curl -X POST "https://pasak.is-a.dev/api/v1/docs" \
  -H "Content-Type: application/json" \
  -H "X-Docs-Key: <KEY>" \
  -d '{
    "product": "pasak-security-monitor",
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
curl -X POST "https://pasak.is-a.dev/api/v1/docs" \
  -H "Content-Type: application/json" \
  -H "X-Docs-Key: <KEY>" \
  -d "{
    \"product\": \"pasak-sentinel\",
    \"filePath\": \"08-ebpf-probes.md\",
    \"rawContent\": $(jq -Rs . < /path/ke/berkas.md)
  }"
```

Respons berhasil:
- Dokumen baru dibuat: `HTTP 201 Created` (`"action": "created"`)
- Dokumen lama diperbarui: `HTTP 200 OK` (`"action": "updated"`)

---

## 4. Hapus Dokumen yang Sudah Usang (DELETE)

Jika ada modul atau fitur yang dihapus dari kode, hapus dokumen terkait agar dokumentasi tidak menyesatkan pengguna:

```bash
curl -X DELETE "https://pasak.is-a.dev/api/v1/docs?product=<PRODUK>&filePath=<FILE_PATH>" \
  -H "X-Docs-Key: <KEY>"
```

Contoh:
```bash
curl -X DELETE "https://pasak.is-a.dev/api/v1/docs?product=pasak-sentinel&slug=deprecated-module" \
  -H "X-Docs-Key: <KEY>"
```

Respons: `{"ok": true, "action": "deleted", "product": "...", "filePath": "..."}`.

---

## 5. Verifikasi Hasil Sinkronisasi

Setelah membuat, mengubah, atau menghapus dokumen:

1. Periksa endpoint API:
   ```bash
   curl -s "https://pasak.is-a.dev/api/v1/docs?product=<PRODUK>&slug=<SLUG>"
   ```
2. Pastikan respons berstatus `200 OK` dan konten sesuai dengan revisi kode terbaru.
3. Kunjungi URL halaman web publik:
   - `https://pasak.is-a.dev/docs/<SLUG>`

---

## Standar Penulisan Dokumentasi (Wajib Dipatuhi AI)

1. **Bahasa Indonesia Baku & Jelas**: Utamakan penjelasan yang ramah pengembang (*developer-friendly*), teknis, dan presisi.
2. **Frontmatter Lengkap**: Setiap berkas `.md` wajib memiliki:
   - `title`: Judul bab yang jelas dan tidak bertele-tele.
   - `description`: Ringkasan 1-2 kalimat fokus pada manfaat modul.
   - `category`: Kategori yang konsisten sesuai manifes modul.
   - `order`: Nomor urut urutan dalam sidebar.
3. **Contoh Kode Siap Pakai**: Sertakan blok kode lengkap dengan sintaks yang valid (`php`, `bash`, `go`, `json`, dsb). Jangan gunakan variabel placeholder fiktif yang membingungkan.
4. **Callout Alert Bergaya GitHub**: Manfaatkan `> [!NOTE]`, `> [!TIP]`, `> [!IMPORTANT]`, dan `> [!WARNING]` untuk menyoroti instruksi kritis.
