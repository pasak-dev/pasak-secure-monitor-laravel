@extends('pages.pasak.layout', ['heading' => __('Audit Reports'), 'subheading' => __('Laporan keamanan & uji penetrasi server format Markdown (.md) dengan preview langsung')])

@section('page_support_key', 'reports')

@section('content')
@php
    $DV = \Pasak\SecureMonitor\Support\DashboardView::class;
    $today = now()->toDateString();
    $yesterday = now()->subDay()->toDateString();
    $last7Days = now()->subDays(6)->toDateString();
    $last30Days = now()->subDays(29)->toDateString();
    $thisMonth = now()->startOfMonth()->toDateString();
    $summary = $report['summary'] ?? $summary ?? [];
@endphp

<div class="sec-card" style="margin-bottom: 20px;">
    <div style="display: flex; justify-content: space-between; align-items: center; gap: 16px; flex-wrap: wrap;">
        <div>
            <h3 class="sec-card-title" style="display: flex; align-items: center; gap: 8px;">
                <span>📅</span>
                <span>{{ __('Pilih Periode & Rekap Audit') }}</span>
                <span class="sec-badge sec-badge-info" style="font-size: 11px; padding: 2px 8px;">{{ $formattedDate }}</span>
            </h3>
            <p class="sec-card-subtitle" style="margin-top: 4px;">
                {{ __('Pilih rentang tanggal (date range) untuk merekap seluruh aktivitas serangan, tren ancaman harian, dan hasil audit hardening server.') }}
            </p>
        </div>

        <form method="GET" action="{{ route('pasak.dashboard.reports') }}" style="display: flex; align-items: center; gap: 10px; flex-wrap: wrap;">
            <div style="display: flex; align-items: center; gap: 6px;">
                <label for="reportStartDate" style="font-size: 12px; font-weight: 600; color: var(--sec-text-muted);">{{ __('Dari:') }}</label>
                <input type="date" id="reportStartDate" name="start_date" value="{{ $startDate }}" class="sec-input" style="padding: 7px 12px; font-size: 13px; font-weight: 600;">
            </div>
            <div style="display: flex; align-items: center; gap: 6px;">
                <label for="reportEndDate" style="font-size: 12px; font-weight: 600; color: var(--sec-text-muted);">{{ __('Sampai:') }}</label>
                <input type="date" id="reportEndDate" name="end_date" value="{{ $endDate }}" class="sec-input" style="padding: 7px 12px; font-size: 13px; font-weight: 600;">
            </div>
            <button type="submit" class="sec-btn sec-btn-primary" style="padding: 7px 14px;">
                {{ __('Terapkan Rekap') }}
            </button>
        </form>
    </div>

    <div style="display: flex; justify-content: space-between; align-items: center; gap: 12px; margin-top: 14px; padding-top: 14px; border-top: 1px solid var(--sec-border); flex-wrap: wrap;">
        <div style="display: flex; align-items: center; gap: 6px; flex-wrap: wrap;">
            <span style="font-size: 11.5px; color: var(--sec-text-muted); font-weight: 600; margin-right: 4px;">{{ __('Preset Cepat:') }}</span>
            <a href="{{ route('pasak.dashboard.reports', ['start_date' => $today, 'end_date' => $today]) }}" class="sec-btn {{ ($startDate === $today && $endDate === $today) ? 'sec-btn-primary' : '' }}" style="font-size: 12px; padding: 5px 10px;">
                {{ __('Hari Ini') }}
            </a>
            <a href="{{ route('pasak.dashboard.reports', ['start_date' => $yesterday, 'end_date' => $yesterday]) }}" class="sec-btn {{ ($startDate === $yesterday && $endDate === $yesterday) ? 'sec-btn-primary' : '' }}" style="font-size: 12px; padding: 5px 10px;">
                {{ __('Kemarin') }}
            </a>
            <a href="{{ route('pasak.dashboard.reports', ['start_date' => $last7Days, 'end_date' => $today]) }}" class="sec-btn {{ ($startDate === $last7Days && $endDate === $today) ? 'sec-btn-primary' : '' }}" style="font-size: 12px; padding: 5px 10px;">
                {{ __('7 Hari Terakhir') }}
            </a>
            <a href="{{ route('pasak.dashboard.reports', ['start_date' => $last30Days, 'end_date' => $today]) }}" class="sec-btn {{ ($startDate === $last30Days && $endDate === $today) ? 'sec-btn-primary' : '' }}" style="font-size: 12px; padding: 5px 10px;">
                {{ __('30 Hari Terakhir') }}
            </a>
            <a href="{{ route('pasak.dashboard.reports', ['start_date' => $thisMonth, 'end_date' => $today]) }}" class="sec-btn {{ ($startDate === $thisMonth && $endDate === $today) ? 'sec-btn-primary' : '' }}" style="font-size: 12px; padding: 5px 10px;">
                {{ __('Bulan Ini') }}
            </a>
        </div>

        <div style="display: flex; align-items: center; gap: 8px; flex-wrap: wrap;">
            <a href="{{ $urls['export'] }}" class="sec-btn sec-btn-primary" style="display: inline-flex; align-items: center; gap: 6px; font-weight: 600; padding: 7px 14px;" target="_blank">
                <span>📥</span>
                <span>{{ __('Unduh Berkas (.md)') }}</span>
            </a>
            <button type="button" class="sec-btn" onclick="copyReportMarkdown()" style="display: inline-flex; align-items: center; gap: 6px; font-weight: 600; padding: 7px 14px;">
                <span id="btnCopyIcon">📋</span>
                <span id="btnCopyText">{{ __('Salin Markdown') }}</span>
            </button>
            <a href="{{ $urls['refresh'] }}" class="sec-btn" title="{{ __('Pindai ulang dan muat data segar') }}" style="padding: 7px 12px;">
                🔄 {{ __('Pindai Ulang') }}
            </a>
            <a href="{{ $urls['server'] }}" class="sec-btn" title="{{ __('Kembali ke audit server') }}" style="padding: 7px 12px;">
                🖥️ {{ __('Audit Server') }}
            </a>
        </div>
    </div>
</div>

{{-- METRIC SUMMARY BAR --}}
<div class="sec-grid-4" style="margin-bottom: 20px;">
    <div class="sec-stat-card">
        <div class="sec-stat-label">{{ __('Health score') }}</div>
        <div class="sec-stat-value">{{ $summary['score'] ?? 0 }}<span style="font-size: 14px; color: var(--sec-text-subtle);">/100</span></div>
    </div>
    <div class="sec-stat-card">
        <div class="sec-stat-label">{{ __('Critical') }}</div>
        <div class="sec-stat-value sec-text-danger">{{ $summary['critical'] ?? 0 }}</div>
    </div>
    <div class="sec-stat-card">
        <div class="sec-stat-label">{{ __('Warnings') }}</div>
        <div class="sec-stat-value" style="color: var(--sec-warning);">{{ $summary['warning'] ?? 0 }}</div>
    </div>
    <div class="sec-stat-card">
        <div class="sec-stat-label">{{ __('Passed checks') }}</div>
        <div class="sec-stat-value" style="color: var(--sec-success);">{{ $summary['ok'] ?? 0 }}</div>
    </div>
</div>

<div style="margin-bottom: 16px; display: flex; justify-content: space-between; align-items: center; border-bottom: 1px solid var(--sec-border); padding-bottom: 8px; flex-wrap: wrap; gap: 12px;">
    <div style="display: flex; gap: 8px;">
        <button type="button" id="tabBtnVisual" class="sec-btn sec-btn-primary" onclick="switchReportTab('visual')" style="display: inline-flex; align-items: center; gap: 6px;">
            <span>🖥️</span>
            <span>{{ __('Pratinjau Tampilan Rapi') }}</span>
        </button>
        <button type="button" id="tabBtnRaw" class="sec-btn" onclick="switchReportTab('raw')" style="display: inline-flex; align-items: center; gap: 6px;">
            <span>📝</span>
            <span>{{ __('Format Mentah Markdown (.md)') }}</span>
        </button>
    </div>
    <div style="font-size: 12px; color: var(--sec-text-muted);">
        <span>{{ __('Panjang Dokumen:') }} <strong>{{ number_format(strlen($markdown)) }} byte</strong></span>
    </div>
</div>

{{-- TAB 1: VISUAL FORMATTED PREVIEW --}}
<div id="tabContentVisual" class="sec-card sec-markdown-wrapper" style="padding: 28px 32px; overflow-x: auto;">
    <div class="sec-markdown-body">
        {!! $htmlPreview !!}
    </div>
</div>

{{-- TAB 2: RAW MARKDOWN PREVIEW --}}
<div id="tabContentRaw" class="sec-card" style="display: none; padding: 20px;">
    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 12px;">
        <div style="font-size: 13px; font-weight: 600; color: var(--sec-text);">
            {{ __('Berkas Sumber Markdown (GFM Specification):') }}
        </div>
        <div style="display: flex; gap: 8px;">
            <button type="button" class="sec-btn sec-btn-primary" onclick="copyReportMarkdown()" style="font-size: 12px; padding: 6px 12px;">
                📋 {{ __('Salin Seluruh Teks') }}
            </button>
            <a href="{{ $urls['export'] }}" class="sec-btn" style="font-size: 12px; padding: 6px 12px;" target="_blank">
                📥 {{ __('Unduh .md') }}
            </a>
        </div>
    </div>
    <textarea id="rawMarkdownTextarea" readonly style="width: 100%; height: 600px; font-family: var(--sec-font-mono, monospace); font-size: 12.5px; line-height: 1.6; background: var(--sec-bg-subtle, #0d1117); color: var(--sec-text, #c9d1d9); border: 1px solid var(--sec-border); border-radius: 8px; padding: 16px; box-sizing: border-box; resize: vertical;">{{ $markdown }}</textarea>
</div>

<style>
    /* Styling Pratinjau Markdown */
    .sec-markdown-body {
        color: var(--sec-text);
        font-size: 14px;
        line-height: 1.7;
    }
    .sec-markdown-body h1 {
        font-size: 20px;
        font-weight: 800;
        margin-top: 0;
        margin-bottom: 12px;
        color: var(--sec-text);
        padding-bottom: 10px;
        border-bottom: 2px solid var(--sec-border);
    }
    .sec-markdown-body h2 {
        font-size: 16.5px;
        font-weight: 700;
        margin-top: 28px;
        margin-bottom: 12px;
        color: var(--sec-text);
        padding-bottom: 6px;
        border-bottom: 1px solid var(--sec-border);
    }
    .sec-markdown-body h3 {
        font-size: 14.5px;
        font-weight: 700;
        margin-top: 20px;
        margin-bottom: 10px;
        color: var(--sec-text);
    }
    .sec-markdown-body hr {
        border: 0;
        height: 1px;
        background: var(--sec-border);
        margin: 24px 0;
    }
    .sec-markdown-body table {
        width: 100%;
        border-collapse: collapse;
        margin: 16px 0 24px;
        font-size: 13px;
        border: 1px solid var(--sec-border);
        border-radius: 8px;
        overflow: hidden;
    }
    .sec-markdown-body th {
        background: var(--sec-bg-subtle);
        color: var(--sec-text);
        font-weight: 700;
        text-align: left;
        padding: 10px 14px;
        border-bottom: 2px solid var(--sec-border);
        border-right: 1px solid var(--sec-border);
    }
    .sec-markdown-body th:last-child {
        border-right: none;
    }
    .sec-markdown-body td {
        padding: 10px 14px;
        border-bottom: 1px solid var(--sec-border);
        border-right: 1px solid var(--sec-border);
        vertical-align: top;
    }
    .sec-markdown-body td:last-child {
        border-right: none;
    }
    .sec-markdown-body tr:hover td {
        background: var(--sec-card-hover, rgba(255,255,255,0.03));
    }
    .sec-markdown-body code {
        font-family: var(--sec-font-mono, monospace);
        font-size: 12px;
        background: var(--sec-bg-subtle);
        padding: 2px 6px;
        border-radius: 4px;
        border: 1px solid var(--sec-border);
        color: var(--sec-primary);
    }
    .sec-markdown-body blockquote {
        margin: 16px 0;
        padding: 12px 18px;
        border-left: 4px solid var(--sec-primary);
        background: var(--sec-bg-subtle);
        border-radius: 0 8px 8px 0;
        color: var(--sec-text-muted);
        font-size: 13px;
    }
    .sec-markdown-body ul {
        padding-left: 20px;
        margin: 12px 0;
    }
    .sec-markdown-body li {
        margin-bottom: 6px;
    }
</style>

<script>
    function switchReportTab(tab) {
        var vContent = document.getElementById('tabContentVisual');
        var rContent = document.getElementById('tabContentRaw');
        var vBtn = document.getElementById('tabBtnVisual');
        var rBtn = document.getElementById('tabBtnRaw');

        if (tab === 'visual') {
            if (vContent) vContent.style.display = 'block';
            if (rContent) rContent.style.display = 'none';
            if (vBtn) {
                vBtn.classList.add('sec-btn-primary');
            }
            if (rBtn) {
                rBtn.classList.remove('sec-btn-primary');
            }
        } else {
            if (vContent) vContent.style.display = 'none';
            if (rContent) rContent.style.display = 'block';
            if (rBtn) {
                rBtn.classList.add('sec-btn-primary');
            }
            if (vBtn) {
                vBtn.classList.remove('sec-btn-primary');
            }
        }
    }

    function copyReportMarkdown() {
        var ta = document.getElementById('rawMarkdownTextarea');
        if (!ta) return;

        navigator.clipboard.writeText(ta.value).then(function() {
            var icon = document.getElementById('btnCopyIcon');
            var txt = document.getElementById('btnCopyText');
            if (icon) icon.innerText = '✅';
            if (txt) txt.innerText = '{{ __('Tersalin!') }}';

            if (typeof window.showToast === 'function') {
                window.showToast('{{ __('Laporan Markdown berhasil disalin ke clipboard.') }}', 'success');
            } else {
                alert('{{ __('Laporan Markdown berhasil disalin ke clipboard.') }}');
            }

            setTimeout(function() {
                if (icon) icon.innerText = '📋';
                if (txt) txt.innerText = '{{ __('Salin Markdown') }}';
            }, 2500);
        }).catch(function(err) {
            alert('{{ __('Gagal menyalin teks: ') }}' + err);
        });
    }
</script>

@endsection
