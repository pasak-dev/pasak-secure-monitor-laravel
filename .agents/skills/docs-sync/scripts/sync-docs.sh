#!/usr/bin/env bash
set -e

# ==============================================================================
# sync-docs.sh — Pasax Suite Docs Sync Helper
# Portal: https://pasax.is-a.dev/docs/pasax-secure-monitor
# API:    https://pasax-service.vercel.app/api/v1/docs
# ==============================================================================

SCRIPT_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")/.." && pwd)"
API_URL="${DOCS_API_URL:-https://pasax-service.vercel.app/api/v1/docs}"
KEY_FILE="$SCRIPT_DIR/.DOCS_SYNC_KEY"

# Baca API Key
if [ -f "$KEY_FILE" ]; then
    DOCS_KEY=$(grep -E '^DOCS_SYNC_KEY=' "$KEY_FILE" | cut -d '=' -f2- | tr -d '\r\n')
fi
DOCS_KEY="${DOCS_KEY:-$DOCS_SYNC_KEY}"

usage() {
    echo "Penggunaan: $(basename "$0") <perintah> [argumen]"
    echo ""
    echo "Perintah:"
    echo "  list [product]              Daftar dokumen (default: pasax-secure-monitor)"
    echo "  get <slug> [product]        Baca konten dokumen dari Neon DB"
    echo "  push <file.md> <slug> [prod] [title] [cat] [order]"
    echo "                              Kirim/perbarui dokumen ke Neon DB"
    echo "  raw-push <file.md> <slug> [product]"
    echo "                              Kirim berkas markdown mentah beserta frontmatter"
    echo "  delete <slug> [product]     Hapus dokumen dari Neon DB"
    echo ""
    echo "Target Produk:"
    echo "  - pasax-secure-monitor (default) -> https://pasax.is-a.dev/docs/pasax-secure-monitor"
    echo "  - pasax-sentinel                   -> https://pasax.is-a.dev/docs/pasax-sentinel"
    echo "  - core                             -> https://pasax.is-a.dev/docs/core"
    exit 1
}

CMD="$1"
shift || true

case "$CMD" in
    list)
        PROD="${1:-pasax-secure-monitor}"
        echo "==> Mengambil daftar dokumen untuk: $PROD"
        curl -s "$API_URL?product=$PROD" | jq .
        ;;

    get)
        SLUG="$1"
        PROD="${2:-pasax-secure-monitor}"
        if [ -z "$SLUG" ]; then
            echo "Error: slug wajib disertakan."
            usage
        fi
        echo "==> Membaca: $PROD / $SLUG"
        curl -s "$API_URL/$PROD/$SLUG" | jq .
        ;;

    push)
        FILE="$1"
        SLUG="$2"
        PROD="${3:-pasax-secure-monitor}"
        TITLE="${4:-Dokumentasi Pasax}"
        CATEGORY="${5:-Panduan}"
        ORDER="${6:-10}"

        if [ -z "$FILE" ] || [ -z "$SLUG" ] || [ ! -f "$FILE" ]; then
            echo "Error: berkas file.md dan slug wajib disertakan."
            usage
        fi

        if [ -z "$DOCS_KEY" ]; then
            echo "Error: Kunci Docs API tidak ditemukan di $KEY_FILE"
            exit 1
        fi

        CONTENT=$(cat "$FILE")
        PAYLOAD=$(jq -n \
            --arg product "$PROD" \
            --arg slug "$SLUG" \
            --arg filePath "$SLUG.md" \
            --arg title "$TITLE" \
            --arg category "$CATEGORY" \
            --argjson order "$ORDER" \
            --arg content "$CONTENT" \
            '{product: $product, slug: $slug, filePath: $filePath, title: $title, category: $category, order: $order, content: $content}')

        echo "==> Mengirim dokumen ke Neon PostgreSQL..."
        curl -s -X POST "$API_URL" \
            -H "Content-Type: application/json" \
            -H "X-Docs-Key: $DOCS_KEY" \
            -d "$PAYLOAD" | jq .

        echo "==> Verifikasi di portal publik:"
        echo "    https://pasax.is-a.dev/docs/$PROD/$SLUG"
        ;;

    raw-push)
        FILE="$1"
        SLUG="$2"
        PROD="${3:-pasax-secure-monitor}"

        if [ -z "$FILE" ] || [ -z "$SLUG" ] || [ ! -f "$FILE" ]; then
            echo "Error: berkas file.md dan slug wajib disertakan."
            usage
        fi

        if [ -z "$DOCS_KEY" ]; then
            echo "Error: Kunci Docs API tidak ditemukan di $KEY_FILE"
            exit 1
        fi

        RAW_CONTENT=$(cat "$FILE")
        PAYLOAD=$(jq -n \
            --arg product "$PROD" \
            --arg slug "$SLUG" \
            --arg rawContent "$RAW_CONTENT" \
            '{product: $product, slug: $slug, rawContent: $rawContent}')

        echo "==> Mengirim raw markdown ke Neon PostgreSQL..."
        curl -s -X POST "$API_URL" \
            -H "Content-Type: application/json" \
            -H "X-Docs-Key: $DOCS_KEY" \
            -d "$PAYLOAD" | jq .

        echo "==> Verifikasi di portal publik:"
        echo "    https://pasax.is-a.dev/docs/$PROD/$SLUG"
        ;;

    delete)
        SLUG="$1"
        PROD="${2:-pasax-secure-monitor}"
        if [ -z "$SLUG" ]; then
            echo "Error: slug wajib disertakan."
            usage
        fi

        if [ -z "$DOCS_KEY" ]; then
            echo "Error: Kunci Docs API tidak ditemukan di $KEY_FILE"
            exit 1
        fi

        echo "==> Menghapus dokumen $PROD / $SLUG dari Neon DB..."
        curl -s -X DELETE "$API_URL?product=$PROD&slug=$SLUG" \
            -H "X-Docs-Key: $DOCS_KEY" | jq .
        ;;

    *)
        usage
        ;;
esac
