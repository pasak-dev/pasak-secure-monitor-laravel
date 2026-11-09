@extends('pages.pasak.layout', ['heading' => __('Security Logs'), 'subheading' => __('Jejak audit ancaman, pola terdeteksi, dan aksi mitigasi')])

@section('content')
@php
    $DV = \Internal\Pasak\Support\DashboardView::class;

    $trendRange = $trendRange ?? request()->query('trend_range', request()->query('range', 'week'));
    if (! in_array($trendRange, ['week', 'month', 'year'], true)) {
        $trendRange = 'week';
    }

    if (! isset($trend) || ! is_array($trend)) {
        try {
            $trend = app(\Internal\Pasak\Services\PasakService::class)->attackTrend($trendRange);
        } catch (\Throwable) {
            $trend = [
                'range' => $trendRange,
                'granularity' => $trendRange === 'year' ? 'month' : 'day',
                'total' => 0,
                'peak' => ['label' => '-', 'total' => 0],
                'points' => [],
            ];
        }
    }

    $trendPoints = $trend['points'] ?? [];
    $numPoints = count($trendPoints);
    $maxTrendValue = max(1, collect($trendPoints)->max('total') ?? 1);
    $yCeil = max(4, (int) ceil($maxTrendValue * 1.18));

    // Akumulasi total keparahan ancaman pada rentang terpilih
    $totalCritical = 0;
    $totalHigh = 0;
    $totalMedium = 0;
    $totalLow = 0;
    foreach ($trendPoints as $tp) {
        $totalCritical += (int) ($tp['levels']['critical'] ?? 0);
        $totalHigh += (int) ($tp['levels']['high'] ?? 0);
        $totalMedium += (int) ($tp['levels']['medium'] ?? 0);
        $totalLow += (int) ($tp['levels']['low'] ?? 0);
    }

    $avgTrend = $numPoints > 0 ? round(($trend['total'] ?? 0) / $numPoints, 1) : 0;

    // Dimensi SVG Line Chart Responsif
    $svgW = 840;
    $svgH = 210;
    $padL = 45;
    $padR = 25;
    $padT = 20;
    $padB = 35;
    $plotW = $svgW - $padL - $padR;
    $plotH = $svgH - $padT - $padB;
    $baselineY = $padT + $plotH;

    $coords = [];
    foreach ($trendPoints as $idx => $pt) {
        $tot = (int) ($pt['total'] ?? 0);
        if ($numPoints <= 1) {
            $cx = $padL + ($plotW / 2);
        } else {
            $cx = $padL + ($idx * ($plotW / ($numPoints - 1)));
        }
        $cy = $baselineY - (($tot / $yCeil) * $plotH);
        $coords[] = [
            'x' => round($cx, 1),
            'y' => round($cy, 1),
            'pt' => $pt,
            'idx' => $idx,
        ];
    }

    $linePointsStr = '';
    $areaPathStr = '';
    if (! empty($coords)) {
        $linePointsStr = implode(' ', array_map(fn($c) => "{$c['x']},{$c['y']}", $coords));
        $first = $coords[0];
        $last = $coords[count($coords) - 1];
        $areaPathStr = "M {$first['x']},{$baselineY} ";
        foreach ($coords as $c) {
            $areaPathStr .= "L {$c['x']},{$c['y']} ";
        }
        $areaPathStr .= "L {$last['x']},{$baselineY} Z";
    }
@endphp

{{-- Kartu Visual: Chart Tren Serangan (Line Chart) --}}
<div class="sec-card" style="margin-bottom: 20px;">
    <div style="display: flex; justify-content: space-between; align-items: flex-start; margin-bottom: 14px; flex-wrap: wrap; gap: 12px;">
        <div>
            <div style="display: flex; align-items: center; gap: 8px;">
                <h3 class="sec-card-title" style="margin: 0;">{{ __('Attack trend') }}</h3>
                <span class="sec-badge sec-badge-critical" style="font-size: 11px; padding: 2px 7px;">
                    {{ $trendRange === 'week' ? __('Minggu (7 Hari)') : ($trendRange === 'month' ? __('Bulan (30 Hari)') : __('Tahun (12 Bulan)')) }}
                </span>
            </div>
            <p class="sec-card-subtitle" style="margin-top: 4px; font-size: 12px;">
                {{ __('Total :total ancaman · Puncak :peak (:count) · Rata-rata :avg / :gran', [
                    'total' => number_format($trend['total'] ?? 0),
                    'peak' => ($trend['peak'] ?? [])['label'] ?? '-',
                    'count' => number_format(($trend['peak'] ?? [])['total'] ?? 0),
                    'avg' => $avgTrend,
                    'gran' => $trend['granularity'] === 'month' ? __('bulan') : __('hari')
                ]) }}
            </p>
        </div>

        {{-- Filter Rentang Waktu: Minggu, Bulan, Tahun --}}
        <div style="display: flex; align-items: center; gap: 6px; flex-wrap: wrap;">
            @foreach (['week' => __('Minggu'), 'month' => __('Bulan'), 'year' => __('Tahun')] as $key => $label)
                <a href="{{ request()->fullUrlWithQuery(['trend_range' => $key]) }}"
                   class="sec-btn sec-btn-sm {{ $trendRange === $key ? 'sec-btn-primary' : 'sec-btn-secondary' }}"
                   style="font-size: 12px; padding: 5px 12px;">
                   {{ $label }}
                </a>
            @endforeach
        </div>
    </div>

    {{-- Indikator Keparahan --}}
    <div style="display: flex; align-items: center; gap: 14px; margin-bottom: 12px; font-size: 11px; color: var(--sec-text-muted); flex-wrap: wrap;">
        <span style="font-weight: 600; color: var(--sec-text);">{{ __('Rincian:') }}</span>
        <span style="display: inline-flex; align-items: center; gap: 4px;">
            <span style="display: inline-block; width: 8px; height: 8px; border-radius: 9999px; background: var(--sec-danger);"></span>
            {{ __('Critical') }}: <strong style="color: var(--sec-text);">{{ number_format($totalCritical) }}</strong>
        </span>
        <span style="display: inline-flex; align-items: center; gap: 4px;">
            <span style="display: inline-block; width: 8px; height: 8px; border-radius: 9999px; background: var(--sec-warning);"></span>
            {{ __('High') }}: <strong style="color: var(--sec-text);">{{ number_format($totalHigh) }}</strong>
        </span>
        <span style="display: inline-flex; align-items: center; gap: 4px;">
            <span style="display: inline-block; width: 8px; height: 8px; border-radius: 9999px; background: #d97706;"></span>
            {{ __('Medium') }}: <strong style="color: var(--sec-text);">{{ number_format($totalMedium) }}</strong>
        </span>
        <span style="display: inline-flex; align-items: center; gap: 4px;">
            <span style="display: inline-block; width: 8px; height: 8px; border-radius: 9999px; background: var(--sec-text-subtle);"></span>
            {{ __('Low') }}: <strong style="color: var(--sec-text);">{{ number_format($totalLow) }}</strong>
        </span>
    </div>

    {{-- Kanvas Line Chart Berbasis Vektor SVG (Zero-Dependency) --}}
    <div style="position: relative; width: 100%; overflow-x: auto;">
        <svg viewBox="0 0 {{ $svgW }} {{ $svgH }}" style="width: 100%; height: auto; min-width: 580px; display: block; overflow: visible;">
            <defs>
                {{-- Gradien Area di Bawah Garis Tren --}}
                <linearGradient id="pasakLineGrad" x1="0" y1="0" x2="0" y2="1">
                    <stop offset="0%" stop-color="var(--sec-danger)" stop-opacity="0.32" />
                    <stop offset="75%" stop-color="var(--sec-danger)" stop-opacity="0.06" />
                    <stop offset="100%" stop-color="var(--sec-danger)" stop-opacity="0.0" />
                </linearGradient>
                {{-- Gradien Garis Stroke --}}
                <linearGradient id="pasakStrokeGrad" x1="0" y1="0" x2="1" y2="0">
                    <stop offset="0%" stop-color="#ef4444" />
                    <stop offset="60%" stop-color="#f59e0b" />
                    <stop offset="100%" stop-color="#dc2626" />
                </linearGradient>
            </defs>

            {{-- Garis Grid Horizontal & Label Sumbu Y --}}
            @php
                $yLevels = [
                    ['val' => $yCeil, 'y' => $padT],
                    ['val' => (int) round($yCeil * 0.66), 'y' => $padT + ($plotH * 0.33)],
                    ['val' => (int) round($yCeil * 0.33), 'y' => $padT + ($plotH * 0.66)],
                    ['val' => 0, 'y' => $baselineY],
                ];
            @endphp
            @foreach ($yLevels as $gl)
                <line x1="{{ $padL }}" y1="{{ $gl['y'] }}" x2="{{ $svgW - $padR }}" y2="{{ $gl['y'] }}"
                      stroke="var(--sec-border-light)" stroke-dasharray="{{ $gl['val'] === 0 ? 'none' : '4 4' }}" stroke-width="1" />
                <text x="{{ $padL - 8 }}" y="{{ $gl['y'] + 4 }}" fill="var(--sec-text-subtle)" font-size="10" font-family="var(--sec-font-mono)" text-anchor="end">
                    {{ $gl['val'] }}
                </text>
            @endforeach

            @if (! empty($coords))
                {{-- Area Berisi Gradien --}}
                <path d="{{ $areaPathStr }}" fill="url(#pasakLineGrad)" />

                {{-- Garis Utama Tren Serangan (Line Chart) --}}
                <polyline points="{{ $linePointsStr }}" fill="none" stroke="url(#pasakStrokeGrad)" stroke-width="3" stroke-linecap="round" stroke-linejoin="round" />

                {{-- Titik Data Interaktif (Nodes) & Label Sumbu X --}}
                @foreach ($coords as $c)
                    @php
                        $pt = $c['pt'];
                        $isPeak = ($pt['total'] ?? 0) > 0 && ($pt['total'] ?? 0) === (($trend['peak'] ?? [])['total'] ?? -1);
                        // Tentukan apakah label tanggal sumbu X perlu ditampilkan agar tidak bertumpukan
                        $showLabel = true;
                        if ($trendRange === 'month') {
                            $showLabel = ($c['idx'] % 4 === 0) || ($c['idx'] === $numPoints - 1);
                        }
                    @endphp

                    {{-- Lingkaran Titik Interaktif --}}
                    <circle cx="{{ $c['x'] }}" cy="{{ $c['y'] }}" r="{{ $isPeak ? '5.5' : '4' }}"
                            fill="var(--sec-card)" stroke="{{ $isPeak ? '#ef4444' : 'var(--sec-danger)' }}" stroke-width="{{ $isPeak ? '3' : '2.5' }}"
                            style="transition: all 0.2s; cursor: pointer;"
                            class="sec-chart-node">
                        <title>{{ $pt['tooltip'] ?? $pt['label'] }}: {{ $pt['total'] }} {{ __('serangan') }} (Critical: {{ $pt['levels']['critical'] ?? 0 }}, High: {{ $pt['levels']['high'] ?? 0 }}, Med: {{ $pt['levels']['medium'] ?? 0 }}, Low: {{ $pt['levels']['low'] ?? 0 }})</title>
                    </circle>

                    {{-- Label Tanggal / Bulan di Bawah Baseline --}}
                    @if ($showLabel)
                        <text x="{{ $c['x'] }}" y="{{ $baselineY + 18 }}" fill="var(--sec-text-subtle)" font-size="10" font-family="var(--sec-font)" text-anchor="middle">
                            {{ $pt['label'] ?? '' }}
                        </text>
                    @endif
                @endforeach
            @else
                {{-- Status Kosong --}}
                <text x="{{ $svgW / 2 }}" y="{{ $padT + ($plotH / 2) }}" fill="var(--sec-text-subtle)" font-size="12" font-family="var(--sec-font)" text-anchor="middle">
                    {{ __('Belum ada aktivitas ancaman pada rentang ini.') }}
                </text>
            @endif
        </svg>
    </div>
</div>

<div class="sec-card">
    <form method="GET" action="{{ route('pasak.dashboard.logs') }}" class="sec-toolbar" style="margin-bottom: 0;">
        {{-- Pertahankan pilihan rentang tren serangan saat filter log diterapkan --}}
        <input type="hidden" name="trend_range" value="{{ $trendRange }}">

        <div class="sec-toolbar-group" style="flex: 1;">
            <input type="text" name="search" value="{{ $filters['search'] ?? '' }}" placeholder="{{ __('Search path, evidence, IP...') }}" class="sec-input" style="min-width: 200px; flex: 1;">
            <select name="level" class="sec-select">
                <option value="">{{ __('All levels') }}</option>
                @foreach (($levels ?? []) as $lvl)
                    <option value="{{ $lvl }}" @selected(($filters['level'] ?? '') === $lvl)>{{ $DV::levelLabel($lvl) }}</option>
                @endforeach
            </select>
            <select name="event_type" class="sec-select">
                <option value="">{{ __('All events') }}</option>
                @foreach (($eventTypes ?? []) as $type)
                    <option value="{{ $type }}" @selected(($filters['event_type'] ?? '') === $type)>{{ $type }}</option>
                @endforeach
            </select>
            <div style="display: inline-flex; align-items: center; gap: 6px;">
                <label for="filter_date_from" style="font-size: 12px; color: var(--sec-text-muted); font-weight: 500;">{{ __('From') }}:</label>
                <input id="filter_date_from" type="date" name="date_from" value="{{ $filters['date_from'] ?? '' }}" class="sec-input" style="font-size: 13px;">
            </div>
            <div style="display: inline-flex; align-items: center; gap: 6px;">
                <label for="filter_date_to" style="font-size: 12px; color: var(--sec-text-muted); font-weight: 500;">{{ __('To') }}:</label>
                <input id="filter_date_to" type="date" name="date_to" value="{{ $filters['date_to'] ?? '' }}" class="sec-input" style="font-size: 13px;">
            </div>
            <div style="display: inline-flex; align-items: center; gap: 6px;">
                <label for="filter_per_page" style="font-size: 12px; color: var(--sec-text-muted); font-weight: 500;">{{ __('Show') }}:</label>
                <select id="filter_per_page" name="per_page" class="sec-select" onchange="this.form.submit()" style="font-size: 13px;">
                    @foreach ([10, 25, 50, 100] as $n)
                        <option value="{{ $n }}" @selected(($filters['per_page'] ?? 25) == $n)>{{ $n }} / {{ __('page') }}</option>
                    @endforeach
                </select>
            </div>
            <button type="submit" class="sec-btn sec-btn-primary">{{ __('Filter') }}</button>
            <a href="{{ route('pasak.dashboard.logs', ['trend_range' => $trendRange]) }}" class="sec-btn sec-btn-secondary">{{ __('Reset') }}</a>
        </div>
    </form>
</div>

<div class="sec-card sec-card-flush">
    <div class="sec-card-header">
        <h3 class="sec-card-title">{{ __('Entries') }} ({{ number_format($logs->total()) }})</h3>
        <form method="POST" action="{{ route('pasak.dashboard.logs.clear') }}" data-sec-confirm="{{ __('Kosongkan seluruh riwayat log keamanan? Tindakan ini tidak dapat dibatalkan.') }}" data-confirm-title="{{ __('Konfirmasi Bersihkan Log') }}" data-confirm-type="danger" data-confirm-btn="{{ __('Ya, Kosongkan Semua') }}">
            @csrf
            @method('DELETE')
            <button type="submit" class="sec-btn sec-btn-danger sec-btn-sm">{{ __('Clear all') }}</button>
        </form>
    </div>
    <div class="sec-table-wrap">
        <table class="sec-table">
            <thead>
                <tr>
                    <th>{{ __('Time') }}</th>
                    <th>{{ __('IP address') }}</th>
                    <th>{{ __('Event') }}</th>
                    <th>{{ __('Level') }}</th>
                    <th>{{ __('Path') }}</th>
                    <th>{{ __('Blocked') }}</th>
                    <th style="text-align: right;">{{ __('Action') }}</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($logs as $log)
                    <tr>
                        <td class="sec-font-mono" style="font-size: 12px; white-space: nowrap;">{{ $log->created_at }}</td>
                        <td class="sec-font-mono" style="font-weight: 600;">{{ $log->ip_address }}</td>
                        <td>{{ $log->event_type }}</td>
                        <td><span class="sec-badge {{ $DV::levelClass($log->threat_level) }}">{{ $DV::levelLabel($log->threat_level) }}</span></td>
                        <td class="sec-font-mono" style="font-size: 12px; max-width: 260px; overflow: hidden; text-overflow: ellipsis; white-space: nowrap;" title="{{ $log->path }}">{{ $log->path }}</td>
                        <td>{{ $log->was_blocked ? __('Yes') : __('No') }}</td>
                        <td style="text-align: right; white-space: nowrap;">
                            <a href="{{ route('pasak.dashboard.logs.show', $log->id) }}" class="sec-btn sec-btn-ghost sec-btn-sm">{{ __('Detail') }}</a>
                            <form method="POST" action="{{ route('pasak.dashboard.logs.destroy', $log->id) }}" style="display: inline;" data-sec-confirm="{{ __('Hapus entri log ancaman ini secara permanen?') }}" data-confirm-title="{{ __('Konfirmasi Hapus Log') }}" data-confirm-type="danger" data-confirm-btn="{{ __('Ya, Hapus') }}">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="sec-btn sec-btn-ghost sec-btn-sm">🗑️</button>
                            </form>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="7" style="text-align: center; color: var(--sec-text-muted); padding: 32px;">{{ __('No logs found.') }}</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
    <div class="sec-datatable-footer">
        <div>
            @if ($logs->total() > 0)
                {{ __('Showing') }} <span style="font-weight: 600; color: var(--sec-text);">{{ $logs->firstItem() }}</span>
                {{ __('to') }} <span style="font-weight: 600; color: var(--sec-text);">{{ $logs->lastItem() }}</span>
                {{ __('of') }} <span style="font-weight: 600; color: var(--sec-text);">{{ number_format($logs->total()) }}</span> {{ __('entries') }}
            @else
                {{ __('Showing 0 entries') }}
            @endif
        </div>
        <div>
            {{ $logs->links('pages.pasak.pagination') }}
        </div>
    </div>
</div>
@endsection
